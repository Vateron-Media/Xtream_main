<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    set_time_limit(0);
    ini_set('memory_limit', -1);
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XC_VM[Users Parser]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::checkCron($unique_id);
        $rSync = null;
        if (count($argv) == 2 && ipTV_lib::$Servers[SERVER_ID]['is_main']) {
            ipTV_lib::connectRedis();
            if (is_object(ipTV_lib::$redis)) {
                $rSync = intval($argv[1]);
                if ($rSync == 1) {
                    $rDeSync = $rRedisUsers = $rRedisUpdate = $rRedisSet = array();
                    $ipTV_db->query('SELECT * FROM `lines_live` WHERE `hls_end` = 0;');
                    $rRows = $ipTV_db->get_rows();
                    if (count($rRows) > 0) {
                        $rStreamIDs = array();
                        foreach ($rRows as $rRow) {
                            if (!in_array($rRow['stream_id'], $rStreamIDs) || $rRow['stream_id'] > 0) {
                                $rStreamIDs[] = intval($rRow['stream_id']);
                            }
                        }
                        $rOnDemand = array();
                        if (count($rStreamIDs) > 0) {
                            $ipTV_db->query('SELECT `stream_id`, `server_id`, `on_demand` FROM `streams_servers` WHERE `stream_id` IN (' . implode(',', $rStreamIDs) . ');');
                            foreach ($ipTV_db->get_rows() as $rRow) {
                                $rOnDemand[$rRow['stream_id']][$rRow['server_id']] = intval($rRow['on_demand']);
                            }
                        }
                        $rRedis = ipTV_lib::$redis->multi();
                        foreach ($rRows as $rRow) {
                            echo 'Resynchronising UUID: ' . $rRow['uuid'] . "\n";
                            if (empty($rRow['hmac_id'])) {
                                $rRow['identity'] = $rRow['user_id'];
                            } else {
                                $rRow['identity'] = $rRow['hmac_id'] . '_' . $rRow['hmac_identifier'];
                            }
                            $rRow['on_demand'] = ($rOnDemand[$rRow['stream_id']][$rRow['server_id']] ?: 0);
                            $rRedis->zAdd('LINE#' . $rRow['identity'], $rRow['date_start'], $rRow['uuid']);
                            $rRedis->zAdd('LINE_ALL#' . $rRow['identity'], $rRow['date_start'], $rRow['uuid']);
                            $rRedis->zAdd('STREAM#' . $rRow['stream_id'], $rRow['date_start'], $rRow['uuid']);
                            $rRedis->zAdd('SERVER#' . $rRow['server_id'], $rRow['date_start'], $rRow['uuid']);
                            if ($rRow['user_id']) {
                                $rRedis->zAdd('SERVER_LINES#' . $rRow['server_id'], $rRow['user_id'], $rRow['uuid']);
                            }
                            $rRedis->zAdd('CONNECTIONS', $rRow['date_start'], $rRow['uuid']);
                            $rRedis->zAdd('LIVE', $rRow['date_start'], $rRow['uuid']);
                            $rRedis->set($rRow['uuid'], igbinary_serialize($rRow));
                            $rDeSync[] = $rRow['uuid'];
                        }
                        $rRedis->exec();
                        if (count($rDeSync) > 0) {
                            $ipTV_db->query("DELETE FROM `lines_live` WHERE `uuid` IN ('" . implode("','", $rDeSync) . "');");
                        }
                    }
                }
            } else {
                exit("Couldn't connect to Redis." . "\n");
            }
        }
        if (ipTV_lib::$settings['redis_handler'] && ipTV_lib::$Servers[SERVER_ID]['is_main']) {
            ipTV_lib::$Servers = ipTV_lib::getServers(true);
            $rPHPPIDs = array();
            foreach (ipTV_lib::$Servers as $rServer) {
                $rPHPPIDs[$rServer['id']] = (array_map('intval', json_decode($rServer['php_pids'], true)) ?: array());
            }
        }
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
function processDeletions($rDelete, $rDelStream = array()) {
    global $ipTV_db;
    $rTime = time();
    if (ipTV_lib::$settings['redis_handler']) {
        if ($rDelete['count'] > 0) {
            $rRedis = ipTV_lib::$redis->multi();
            foreach ($rDelete['line'] as $rUserID => $rUUIDs) {
                $rRedis->zRem('LINE#' . $rUserID, ...$rUUIDs);
                $rRedis->zRem('LINE_ALL#' . $rUserID, ...$rUUIDs);
            }
            foreach ($rDelete['stream'] as $rStreamID => $rUUIDs) {
                $rRedis->zRem('STREAM#' . $rStreamID, ...$rUUIDs);
            }
            foreach ($rDelete['server'] as $rServerID => $rUUIDs) {
                $rRedis->zRem('SERVER#' . $rServerID, ...$rUUIDs);
                $rRedis->zRem('SERVER_LINES#' . $rServerID, ...$rUUIDs);
            }
            if (count($rDelete['uuid']) > 0) {
                $rRedis->zRem('CONNECTIONS', ...$rDelete['uuid']);
                $rRedis->zRem('LIVE', ...$rDelete['uuid']);
                $rRedis->sRem('ENDED', ...$rDelete['uuid']);
                $rRedis->del(...$rDelete['uuid']);
            }
            $rRedis->exec();
        }
    } else {
        foreach ($rDelete as $rServerID => $rConnections) {
            if (count($rConnections) > 0) {
                $ipTV_db->query("DELETE FROM `lines_live` WHERE `uuid` IN ('" . implode("','", $rConnections) . "')");
            }
        }
    }
    foreach ((ipTV_lib::$settings['redis_handler'] ? $rDelete['server'] : $rDelete) as $rServerID => $rConnections) {
        if ($rServerID != SERVER_ID) {
            $rQuery = '';
            foreach ($rConnections as $rConnection) {
                $rQuery .= '(' . $rServerID . ',1,' . $rTime . ',' . $ipTV_db->escape(json_encode(array('type' => 'delete_con', 'uuid' => $rConnection))) . '),';
            }
            $rQuery = rtrim($rQuery, ',');
            if (!empty($rQuery)) {
                $ipTV_db->query('INSERT INTO `signals`(`server_id`, `cache`, `time`, `custom_data`) VALUES ' . $rQuery . ';');
            }
        }
    }
    foreach ($rDelStream as $rStreamID => $rConnections) {
        foreach ($rConnections as $rConnection) {
            ipTV_lib::unlinkFile(CONS_TMP_PATH . $rStreamID . '/' . $rConnection);
        }
    }
    if (ipTV_lib::$settings['redis_handler']) {
        return array('line' => array(), 'server' => array(), 'server_lines' => array(), 'stream' => array(), 'uuid' => array(), 'count' => 0);
    }
    return array();
}
function loadCron() {
    global $ipTV_db;
    global $rPHPPIDs;
    if (ipTV_lib::$settings['redis_handler']) {
        ipTV_lib::connectRedis();
    }
    $rStartTime = time();
    if (!ipTV_lib::$settings['redis_handler'] && ipTV_lib::$Servers[SERVER_ID]['is_main']) {
        $rAutoKick = ipTV_lib::$settings['user_auto_kick_hours'] * 3600;
        $rLiveKeys = $rDelete = $rDeleteStream = array();
        if (ipTV_lib::$settings['redis_handler']) {
            $rRedisDelete = array('line' => array(), 'server' => array(), 'server_lines' => array(), 'stream' => array(), 'uuid' => array(), 'count' => 0);
            $rUsers = array();
            list($rKeys, $rConnections) = ipTV_streaming::getConnections();
            $i = 0;
            for ($rSize = count($rConnections); $i < $rSize; $i++) {
                $rConnection = $rConnections[$i];
                if (is_array($rConnection)) {
                    $rUsers[$rConnection['identity']][] = $rConnection;
                    $rLiveKeys[] = $rConnection['uuid'];
                } else {
                    $rRedisDelete['count']++;
                    $rRedisDelete['uuid'][] = $rKeys[$i];
                }
            }
            unset($rConnections);
        } else {
            $rUsers = ipTV_streaming::getConnections((ipTV_lib::$Servers[SERVER_ID]['is_main'] ? null : SERVER_ID));
        }
        $rRestreamerArray = $rMaxConnectionsArray = array();
        $rUserIDs = ipTV_lib::confirmIDs(array_keys($rUsers));
        if (count($rUserIDs) > 0) {
            $ipTV_db->query('SELECT `id`, `max_connections`, `is_restreamer` FROM `lines` WHERE `id` IN (' . implode(',', $rUserIDs) . ');');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rMaxConnectionsArray[$rRow['id']] = $rRow['max_connections'];
                $rRestreamerArray[$rRow['id']] = $rRow['is_restreamer'];
            }
        }
        if (ipTV_lib::$settings['redis_handler'] && ipTV_lib::$Servers[SERVER_ID]['is_main']) {
            foreach (ipTV_streaming::getEnded() as $rConnection) {
                if (is_array($rConnection)) {
                    if (!in_array($rConnection['container'], array('ts', 'hls', 'rtmp')) && time() - $rConnection['hls_last_read'] < 300) {
                        $rClose = false;
                    } else {
                        $rClose = true;
                    }
                    if ($rClose) {
                        echo 'Close connection 1: ' . $rConnection['uuid'] . "\n";
                        ipTV_streaming::closeConnection($rConnection, false, false);
                        $rRedisDelete['count']++;
                        $rRedisDelete['line'][$rConnection['identity']][] = $rConnection['uuid'];
                        $rRedisDelete['stream'][$rConnection['stream_id']][] = $rConnection['uuid'];
                        $rRedisDelete['server'][$rConnection['server_id']][] = $rConnection['uuid'];
                        $rRedisDelete['uuid'][] = $rConnection['uuid'];
                    }
                }
            }
            if ($rRedisDelete['count'] > 1000) {
                $rRedisDelete = processDeletions($rRedisDelete, $rRedisDelete['stream']);
            }
        }
        foreach ($rUsers as $rUserID => $rConnections) {
            $rActiveCount = 0;
            $rMaxConnections = $rMaxConnectionsArray[$rUserID];
            $rIsRestreamer = ($rRestreamerArray[$rUserID] ?: false);
            foreach ($rConnections as $rKey => $rConnection) {
                if ($rConnection['server_id'] == SERVER_ID || ipTV_lib::$settings['redis_handler']) {
                    if (is_null($rConnection['exp_date']) || $rConnection['exp_date'] >= $rStartTime) {
                        $rTotalTime = $rStartTime - $rConnection['date_start'];
                        if (!($rAutoKick != 0 && $rAutoKick <= $rTotalTime) || $rIsRestreamer) {
                            if ($rConnection['container'] == 'hls') {
                                if (30 <= $rStartTime - $rConnection['hls_last_read'] || $rConnection['hls_end'] == 1) {
                                    echo 'Close connection 2: ' . $rConnection['uuid'] . "\n";
                                    ipTV_streaming::closeConnection($rConnection, false, false);
                                    if (ipTV_lib::$settings['redis_handler']) {
                                        $rRedisDelete['count']++;
                                        $rRedisDelete['line'][$rConnection['identity']][] = $rConnection['uuid'];
                                        $rRedisDelete['stream'][$rConnection['stream_id']][] = $rConnection['uuid'];
                                        $rRedisDelete['server'][$rConnection['server_id']][] = $rConnection['uuid'];
                                        $rRedisDelete['uuid'][] = $rConnection['uuid'];
                                        if ($rConnection['user_id']) {
                                            $rRedisDelete['server_lines'][$rConnection['server_id']][] = $rConnection['uuid'];
                                        }
                                    } else {
                                        $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                                        $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                                    }
                                }
                            } else if ($rConnection['container'] == 'ts') {
                                if ($rConnection['server_id'] == SERVER_ID) {
                                    $rIsRunning = ipTV_streaming::isProcessRunning($rConnection['pid'], 'php-fpm');
                                } else {
                                    if ($rConnection['date_start'] <= ipTV_lib::$Servers[$rConnection['server_id']]['last_check_ago'] - 1 && 0 < count($rPHPPIDs[$rConnection['server_id']])) {
                                        $rIsRunning = in_array(intval($rConnection['pid']), $rPHPPIDs[$rConnection['server_id']]);
                                    } else {
                                        $rIsRunning = true;
                                    }
                                }
                                if (($rConnection['hls_end'] == 1 && 300 <= $rStartTime - $rConnection['hls_last_read']) || !$rIsRunning) {
                                    echo 'Close connection 3: ' . $rConnection['uuid'] . "\n";
                                    ipTV_streaming::closeConnection($rConnection, false, false);
                                    if (ipTV_lib::$settings['redis_handler']) {
                                        $rRedisDelete['count']++;
                                        $rRedisDelete['line'][$rConnection['identity']][] = $rConnection['uuid'];
                                        $rRedisDelete['stream'][$rConnection['stream_id']][] = $rConnection['uuid'];
                                        $rRedisDelete['server'][$rConnection['server_id']][] = $rConnection['uuid'];
                                        $rRedisDelete['uuid'][] = $rConnection['uuid'];
                                        if ($rConnection['user_id']) {
                                            $rRedisDelete['server_lines'][$rConnection['server_id']][] = $rConnection['uuid'];
                                        }
                                    } else {
                                        $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                                        $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                                    }
                                }
                            }
                        } else {
                            echo 'Close connection 4: ' . $rConnection['uuid'] . "\n";
                            ipTV_streaming::closeConnection($rConnection, false, false);
                            if (ipTV_lib::$settings['redis_handler']) {
                                $rRedisDelete['count']++;
                                $rRedisDelete['line'][$rConnection['identity']][] = $rConnection['uuid'];
                                $rRedisDelete['stream'][$rConnection['stream_id']][] = $rConnection['uuid'];
                                $rRedisDelete['server'][$rConnection['server_id']][] = $rConnection['uuid'];
                                $rRedisDelete['uuid'][] = $rConnection['uuid'];
                                if ($rConnection['user_id']) {
                                    $rRedisDelete['server_lines'][$rConnection['server_id']][] = $rConnection['uuid'];
                                }
                            } else {
                                $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                                $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                            }
                        }
                    } else {
                        echo 'Close connection 5: ' . $rConnection['uuid'] . "\n";
                        ipTV_streaming::closeConnection($rConnection, false, false);
                        if (ipTV_lib::$settings['redis_handler']) {
                            $rRedisDelete['count']++;
                            $rRedisDelete['line'][$rConnection['identity']][] = $rConnection['uuid'];
                            $rRedisDelete['stream'][$rConnection['stream_id']][] = $rConnection['uuid'];
                            $rRedisDelete['server'][$rConnection['server_id']][] = $rConnection['uuid'];
                            $rRedisDelete['uuid'][] = $rConnection['uuid'];
                            if ($rConnection['user_id']) {
                                $rRedisDelete['server_lines'][$rConnection['server_id']][] = $rConnection['uuid'];
                            }
                        } else {
                            $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                            $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                        }
                    }
                }
                if (!$rConnection['hls_end']) {
                    $rActiveCount++;
                }
            }
            if (ipTV_lib::$Servers[SERVER_ID]['is_main'] && 0 < $rMaxConnections && $rMaxConnections < $rActiveCount) {
                foreach ($rConnections as $rKey => $rConnection) {
                    if (!$rConnection['hls_end']) {
                        echo 'Close connection 6: ' . $rConnection['uuid'] . "\n";
                        ipTV_streaming::closeConnection($rConnection, false, false);
                        if (ipTV_lib::$settings['redis_handler']) {
                            $rRedisDelete['count']++;
                            $rRedisDelete['line'][$rConnection['identity']][] = $rConnection['uuid'];
                            $rRedisDelete['stream'][$rConnection['stream_id']][] = $rConnection['uuid'];
                            $rRedisDelete['server'][$rConnection['server_id']][] = $rConnection['uuid'];
                            $rRedisDelete['uuid'][] = $rConnection['uuid'];
                            if ($rConnection['user_id']) {
                                $rRedisDelete['server_lines'][$rConnection['server_id']][] = $rConnection['uuid'];
                            }
                        } else {
                            $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                            $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                        }
                        $rActiveCount--;
                    }
                    if ($rActiveCount > $rMaxConnections) {
                    } else {
                        break;
                    }
                }
            }
            if (ipTV_lib::$settings['redis_handler'] && 1000 <= $rRedisDelete['count']) {
                $rRedisDelete = processDeletions($rRedisDelete, $rRedisDelete['stream']);
            } elseif (!ipTV_lib::$settings['redis_handler'] || count($rDelete) > 1000) {
                $rDelete = processDeletions($rDelete, $rDeleteStream);
            }
        }
        if (ipTV_lib::$settings['redis_handler'] && 0 < $rRedisDelete['count']) {
            processDeletions($rRedisDelete, $rRedisDelete['stream']);
        } elseif (!ipTV_lib::$settings['redis_handler'] || count($rDelete) > 0) {
            processDeletions($rDelete, $rDeleteStream);
        }
    }
    $rConnectionSpeeds = glob(DIVERGENCE_TMP_PATH . '*');
    if (count($rConnectionSpeeds) > 0) {
        if (ipTV_lib::$settings['redis_handler']) {
            $rStreamMap = $rBitrates = array();
            $ipTV_db->query('SELECT `stream_id`, `bitrate` FROM `streams_servers` WHERE `server_id` = ? AND `bitrate` IS NOT NULL;', SERVER_ID);
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rStreamMap[intval($rRow['stream_id'])] = intval($rRow['bitrate'] / 8 * 0.92);
            }
            $rUUIDs = array();
            foreach ($rConnectionSpeeds as $rConnectionSpeed) {
                if (!empty($rConnectionSpeed)) {
                    $rUUIDs[] = basename($rConnectionSpeed);
                }
            }
            if (count($rUUIDs) > 0) {
                $rConnections = array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rUUIDs));
                foreach ($rConnections as $rConnection) {
                    if (is_array($rConnection)) {
                        $rBitrates[$rConnection['uuid']] = $rStreamMap[intval($rConnection['stream_id'])];
                    }
                }
            }
            unset($rStreamMap);
        } else {
            $rBitrates = array();
            $ipTV_db->query('SELECT `lines_live`.`uuid`, `streams_servers`.`bitrate` FROM `lines_live` LEFT JOIN `streams_servers` ON `lines_live`.`stream_id` = `streams_servers`.`stream_id` AND `lines_live`.`server_id` = `streams_servers`.`server_id` WHERE `lines_live`.`server_id` = ?;', SERVER_ID);
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rBitrates[$rRow['uuid']] = intval($rRow['bitrate'] / 8 * 0.92);
            }
        }
        if (!ipTV_lib::$settings['redis_handler']) {
            $rUUIDMap = array();
            $ipTV_db->query('SELECT `uuid`, `activity_id` FROM `lines_live`;');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rUUIDMap[$rRow['uuid']] = $rRow['activity_id'];
            }
        }
        $rLiveQuery = $rDivergenceUpdate = array();
        foreach ($rConnectionSpeeds as $rConnectionSpeed) {
            if (!empty($rConnectionSpeed)) {
                $rUUID = basename($rConnectionSpeed);
                $rAverageSpeed = intval(file_get_contents($rConnectionSpeed));
                $rDivergence = intval(($rAverageSpeed - $rBitrates[$rUUID]) / $rBitrates[$rUUID] * 100);
                if ($rDivergence > 0) {
                    $rDivergence = 0;
                }
                $rDivergenceUpdate[] = "('" . $rUUID . "', " . abs($rDivergence) . ')';
                if (!ipTV_lib::$settings['redis_handler'] && isset($rUUIDMap[$rUUID])) {
                    $rLiveQuery[] = '(' . $rUUIDMap[$rUUID] . ', ' . abs($rDivergence) . ')';
                }
            }
        }
        if (count($rDivergenceUpdate) > 0) {
            $rUpdateQuery = implode(',', $rDivergenceUpdate);
            $ipTV_db->query('INSERT INTO `lines_divergence`(`uuid`,`divergence`) VALUES ' . $rUpdateQuery . ' ON DUPLICATE KEY UPDATE `divergence`=VALUES(`divergence`);');
        }
        if (!ipTV_lib::$settings['redis_handler'] && count($rLiveQuery) > 0) {
            $rLiveQuery = implode(',', $rLiveQuery);
            $ipTV_db->query('INSERT INTO `lines_live`(`activity_id`,`divergence`) VALUES ' . $rLiveQuery . ' ON DUPLICATE KEY UPDATE `divergence`=VALUES(`divergence`);');
        }
        shell_exec('rm -f ' . DIVERGENCE_TMP_PATH . '*');
    }
    if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
        if (ipTV_lib::$settings['redis_handler']) {
            $ipTV_db->query('DELETE FROM `lines_divergence` WHERE `uuid` NOT IN (SELECT `uuid` FROM `lines_live`);');
        } else {
            $ipTV_db->query("DELETE FROM `lines_divergence` WHERE `uuid` NOT IN ('" . implode("','", $rLiveKeys) . "');");
        }
    }
    if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
        $ipTV_db->query('DELETE FROM `lines_live` WHERE `uuid` IS NULL;');
    }
}
function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
