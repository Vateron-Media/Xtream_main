<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    if ($argc) {
        set_time_limit(0);
        $rPID = getmypid();
        require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
        shell_exec('kill -9 $(ps aux | grep cache_handler | grep -v grep | grep -v ' . $rPID . " | awk '{print \$2}')");
        $rLastCheck = null;
        $rInterval = 60;
        $rMD5 = md5_file(__FILE__);
        ipTV_lib::$settings = ipTV_lib::getSettings(true);
        if (ipTV_lib::$settings['enable_cache']) {
            while (true) {
                if (!$ipTV_db->ping()) {
                    break;
                }
                if ($rLastCheck && $rInterval > time() - $rLastCheck) {
                } else {
                    ipTV_lib::$settings = ipTV_lib::getSettings(true);
                    ipTV_lib::$Servers = ipTV_lib::getServers(true);
                    if (ipTV_lib::$settings['enable_cache']) {
                        if (md5_file(__FILE__) == $rMD5) {
                            $rLastCheck = time();
                        } else {
                            echo 'File changed! Break.' . "\n";
                        }
                    } else {
                        echo 'Cache disabled! Break.' . "\n";
                    }
                }
                try {
                    $rUpdatedLines = array();
                    foreach (glob(SIGNALS_TMP_PATH . 'cache_*') as $rFileMD5) {
                        list($rKey, $rData) = json_decode(file_get_contents($rFileMD5), true);
                        list($rHeader) = explode('/', $rKey);
                        switch ($rHeader) {
                            case 'restream_block_user':
                                list($rBlank, $rUserID, $rStreamID, $rIP) = explode('/', $rKey);
                                $ipTV_db->query('UPDATE `lines` SET `admin_enabled` = 0 WHERE `id` = ?;', $rUserID);
                                $ipTV_db->query('INSERT INTO `detect_restream_logs`(`user_id`, `stream_id`, `ip`, `time`) VALUES(?, ?, ?, ?);', $rUserID, $rStreamID, $rIP, time());
                                $rUpdatedLines[] = $rUserID;
                                break;
                            case 'forced_country':
                                $rUserID = intval(explode('/', $rKey)[1]);
                                $ipTV_db->query('UPDATE `lines` SET `forced_country` = ? WHERE `id` = ?', $rData, $rUserID);
                                $rUpdatedLines[] = $rUserID;
                                break;
                            case 'isp':
                                $rUserID = intval(explode('/', $rKey)[1]);
                                $rISPInfo = json_decode($rData, true);
                                $ipTV_db->query('UPDATE `lines` SET `isp_desc` = ?, `as_number` = ? WHERE `id` = ?', $rISPInfo[0], $rISPInfo[1], $rUserID);
                                $rUpdatedLines[] = $rUserID;
                                break;
                            case 'expiring':
                                $rUserID = intval(explode('/', $rKey)[1]);
                                $ipTV_db->query('UPDATE `lines` SET `last_expiration_video` = ? WHERE `id` = ?;', time(), $rUserID);
                                $rUpdatedLines[] = $rUserID;
                                break;
                            case 'flood_attack':
                                list($rBlank, $rIP) = explode('/', $rKey);
                                $ipTV_db->query('INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES(?,?,?)', $rIP, 'FLOOD ATTACK', time());
                                touch(FLOOD_TMP_PATH . 'block_' . $rIP);
                                break;
                            case 'bruteforce_attack':
                                list($rBlank, $rIP) = explode('/', $rKey);
                                $ipTV_db->query('INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES(?,?,?)', $rIP, 'BRUTEFORCE ATTACK', time());
                                touch(FLOOD_TMP_PATH . 'block_' . $rIP);
                                break;
                        }
                        unlink($rFileMD5);
                    }
                    $rUpdatedLines = array_unique($rUpdatedLines);
                    foreach ($rUpdatedLines as $rUserID) {
                        ipTV_lib::updateLine($rUserID);
                    }
                    sleep(1);
                } catch (Exception $e) {
                    echo 'Error!' . "\n";
                }
            }
            if (is_object($ipTV_db)) {
                $ipTV_db->close_mysql();
            }
            shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
        } else {
            echo 'Cache disabled.' . "\n";
            exit();
        }
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
