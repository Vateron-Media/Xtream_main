<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    set_time_limit(0);
    ini_set('memory_limit', -1);
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XtreamCodes[Users Parser]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::check_cron($unique_id);
        $rSync = null;
        if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
            ipTV_lib::$StreamingServers = ipTV_lib::getServers(true);
            $rPHPPIDs = array();
            foreach (ipTV_lib::$StreamingServers as $rServer) {
                $rPHPPIDs[$rServer['id']] = (array_map('intval', json_decode($rServer['php_pids'], true)) ?: array());
            }
        }
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as XtreamCodes!' . "\n");
}
function processDeletions($rDelete, $rDelStream = array()) {
    global $ipTV_db;
    $rTime = time();

    foreach ($rDelete as $rServerID => $rConnections) {
        if (0 >= count($rConnections)) {
        } else {
            $ipTV_db->query("DELETE FROM `lines_live` WHERE `uuid` IN ('" . implode("','", $rConnections) . "')");
        }
    }

    foreach ($rDelete as $rServerID => $rConnections) {
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
            ipTV_lib::unlink_file(CONS_TMP_PATH . $rStreamID . '/' . $rConnection);
        }
    }
    return array();
}
function loadCron() {
    global $ipTV_db;
    global $rPHPPIDs;
    $rStartTime = time();
    if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
        $rAutoKick = ipTV_lib::$settings['user_auto_kick_hours'] * 3600;
        $rLiveKeys = $rDelete = $rDeleteStream = array();

        $rUsers = ipTV_streaming::getConnections((ipTV_lib::$StreamingServers[SERVER_ID]['is_main'] ? null : SERVER_ID));

        $rRestreamerArray = $rMaxConnectionsArray = array();
        $rUserIDs = ipTV_lib::confirmIDs(array_keys($rUsers));
        if (0 >= count($rUserIDs)) {
        } else {
            $ipTV_db->query('SELECT `id`, `max_connections`, `is_restreamer` FROM `users` WHERE `id` IN (' . implode(',', $rUserIDs) . ');');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rMaxConnectionsArray[$rRow['id']] = $rRow['max_connections'];
                $rRestreamerArray[$rRow['id']] = $rRow['is_restreamer'];
            }
        }
        foreach ($rUsers as $rUserID => $rConnections) {
            $rActiveCount = 0;
            $rMaxConnections = $rMaxConnectionsArray[$rUserID];
            $rIsRestreamer = ($rRestreamerArray[$rUserID] ?: false);
            foreach ($rConnections as $rKey => $rConnection) {
                if ($rConnection['server_id'] == SERVER_ID) {
                    if (is_null($rConnection['exp_date']) || $rConnection['exp_date'] >= $rStartTime) {
                        $rTotalTime = $rStartTime - $rConnection['date_start'];
                        if (!($rAutoKick != 0 && $rAutoKick <= $rTotalTime) || $rIsRestreamer) {
                            if ($rConnection['container'] == 'hls') {
                                if (30 <= $rStartTime - $rConnection['hls_last_read'] || $rConnection['hls_end'] == 1) {
                                    echo 'Close connection: ' . $rConnection['uuid'] . "\n";
                                    ipTV_streaming::closeConnection($rConnection, false, false);

                                    $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                                    $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                                }
                            } else {
                                if ($rConnection['container'] != 'rtmp') {
                                    if ($rConnection['server_id'] == SERVER_ID) {
                                        $rIsRunning = ipTV_streaming::isProcessRunning($rConnection['pid'], 'php-fpm');
                                    } else {
                                        if ($rConnection['date_start'] <= ipTV_lib::$StreamingServers[$rConnection['server_id']]['last_check_ago'] - 1 && 0 < count($rPHPPIDs[$rConnection['server_id']])) {
                                            $rIsRunning = in_array(intval($rConnection['pid']), $rPHPPIDs[$rConnection['server_id']]);
                                        } else {
                                            $rIsRunning = true;
                                        }
                                    }
                                    if ($rConnection['hls_end'] == 1 && 300 <= $rStartTime - $rConnection['hls_last_read'] && !$rIsRunning) {
                                        echo 'Close connection: ' . $rConnection['uuid'] . "\n";
                                        ipTV_streaming::closeConnection($rConnection, false, false);

                                        $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                                        $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                                    }
                                }
                            }
                        } else {
                            echo 'Close connection: ' . $rConnection['uuid'] . "\n";
                            ipTV_streaming::closeConnection($rConnection, false, false);

                            $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                            $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                        }
                    } else {
                        echo 'Close connection: ' . $rConnection['uuid'] . "\n";
                        ipTV_streaming::closeConnection($rConnection, false, false);

                        $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                        $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];
                    }
                }
                if ($rConnection['hls_end']) {
                } else {
                    $rActiveCount++;
                }
            }
            if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main'] && 0 < $rMaxConnections && $rMaxConnections < $rActiveCount) {
                foreach ($rConnections as $rKey => $rConnection) {
                    if ($rConnection['hls_end']) {
                    } else {
                        echo 'Close connection: ' . $rConnection['uuid'] . "\n";
                        ipTV_streaming::closeConnection($rConnection, false, false);

                        $rDelete[$rConnection['server_id']][] = $rConnection['uuid'];
                        $rDeleteStream[$rConnection['stream_id']] = $rDelete[$rConnection['server_id']];

                        $rActiveCount--;
                    }
                    if ($rActiveCount > $rMaxConnections) {
                    } else {
                        break;
                    }
                }
            }
            if (1000 < count($rDelete)) {
                $rDelete = processDeletions($rDelete, $rDeleteStream);
            }
        }
        if (count($rDelete) > 0) {
            processDeletions($rDelete, $rDeleteStream);
        }
    }
    $rConnectionSpeeds = glob(DIVERGENCE_TMP_PATH . '*');
    if (count($rConnectionSpeeds) > 0) {
        $rBitrates = array();
        $ipTV_db->query('SELECT `lines_live`.`uuid`, `streams_servers`.`bitrate` FROM `lines_live` LEFT JOIN `streams_servers` ON `lines_live`.`stream_id` = `streams_servers`.`stream_id` AND `lines_live`.`server_id` = `streams_servers`.`server_id` WHERE `lines_live`.`server_id` = \'%s\';', SERVER_ID);
        foreach ($ipTV_db->get_rows() as $rRow) {
            $rBitrates[$rRow['uuid']] = intval($rRow['bitrate'] / 8 * 0.92);
        }

        $rUUIDMap = array();
        $ipTV_db->query('SELECT `uuid`, `activity_id` FROM `lines_live`;');
        foreach ($ipTV_db->get_rows() as $rRow) {
            $rUUIDMap[$rRow['uuid']] = $rRow['activity_id'];
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
                if (isset($rUUIDMap[$rUUID])) {
                    $rLiveQuery[] = '(' . $rUUIDMap[$rUUID] . ', ' . abs($rDivergence) . ')';
                }
            }
        }
        if (count($rDivergenceUpdate) > 0) {
            $rUpdateQuery = implode(',', $rDivergenceUpdate);
            $ipTV_db->query('INSERT INTO `lines_divergence`(`uuid`,`divergence`) VALUES ' . $rUpdateQuery . ' ON DUPLICATE KEY UPDATE `divergence`=VALUES(`divergence`);');
        }
        if (count($rLiveQuery) > 0) {
            $rLiveQuery = implode(',', $rLiveQuery);
            $ipTV_db->query('INSERT INTO `lines_live`(`activity_id`,`divergence`) VALUES ' . $rLiveQuery . ' ON DUPLICATE KEY UPDATE `divergence`=VALUES(`divergence`);');
        }
        shell_exec('rm -f ' . DIVERGENCE_TMP_PATH . '*');
    }
    if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
        $ipTV_db->query('DELETE FROM `lines_divergence` WHERE `uuid` NOT IN (SELECT `uuid` FROM `lines_live`);');
    }
    if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
        $ipTV_db->query('DELETE FROM `lines_live` WHERE `uuid` IS NULL;');
    }
}
function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (!is_object($ipTV_db)) {
    } else {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
