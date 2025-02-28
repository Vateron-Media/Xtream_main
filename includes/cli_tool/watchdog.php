<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
        shell_exec('kill $(ps aux | grep watchdog | grep -v grep | grep -v ' . getmypid() . " | awk '{print \$2}')");
        $rInterval = (intval(ipTV_lib::$settings['online_capacity_interval']) ?: 10);
        $rLastRequests = $rLastRequestsTime = $rPrevStat = $rLastCheck = null;
        $rMD5 = md5_file(__FILE__);
        if (ipTV_lib::$settings['redis_handler']) {
            ipTV_lib::connectRedis();
        }
        $rWatchdog = json_decode(ipTV_lib::$Servers[SERVER_ID]['watchdog_data'], true);
        $rCPUAverage = ($rWatchdog['cpu_average_array'] ?: array());
        while (true && $ipTV_db->ping() && !(ipTV_lib::$settings['redis_handler'] && (!ipTV_lib::$redis || !ipTV_lib::$redis->ping()))) {
            if ($rLastCheck && $rInterval > time() - $rLastCheck) {
                $rNginx = explode("\n", file_get_contents('http://127.0.0.1:' . ipTV_lib::$Servers[SERVER_ID]['http_broadcast_port'] . '/nginx_status'));
                list($rAccepted, $rHandled, $rRequests) = explode(' ', trim($rNginx[2]));
                $rRequestsPerSecond = ($rLastRequests ? intval(floatval($rRequests - $rLastRequests) / (time() - $rLastRequestsTime)) : 0);
                $rLastRequests = $rRequests;
                $rLastRequestsTime = time();
                $rStats = getStats();
                if (!$rPrevStat) {
                    $rPrevStat = file('/proc/stat');
                    sleep(2);
                }
                $rStat = file('/proc/stat');
                $rInfoA = explode(' ', preg_replace('!cpu +!', '', $rPrevStat[0]));
                $rInfoB = explode(' ', preg_replace('!cpu +!', '', $rStat[0]));
                $rPrevStat = $rStat;
                $rDiff = array();
                $rDiff['user'] = $rInfoB[0] - $rInfoA[0];
                $rDiff['nice'] = $rInfoB[1] - $rInfoA[1];
                $rDiff['sys'] = $rInfoB[2] - $rInfoA[2];
                $rDiff['idle'] = $rInfoB[3] - $rInfoA[3];
                $rTotal = array_sum($rDiff);
                $rCPU = array();
                foreach ($rDiff as $x => $y) {
                    $rCPU[$x] = round($y / $rTotal * 100, 2);
                }
                $rStats['cpu'] = $rCPU['user'] + $rCPU['sys'];
                $rCPUAverage[] = $rStats['cpu'];
                if (count($rCPUAverage) > 30) {
                    $rCPUAverage = array_slice($rCPUAverage, count($rCPUAverage) - 30, 30);
                }
                $rStats['cpu_average_array'] = $rCPUAverage;
                $rPHPPIDs = array();
                exec("ps -u xc_vm | grep php-fpm | awk {'print \$1'}", $rPHPPIDs);
                $rConnections = $rUsers = 0;
                if (!ipTV_lib::$settings['redis_handler']) {
                    $ipTV_db->query('SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `hls_end` = 0 AND `server_id` = ?;', SERVER_ID);
                    $rConnections = $ipTV_db->get_row()['count'];
                    $ipTV_db->query('SELECT `activity_id` FROM `lines_live` WHERE `hls_end` = 0 AND `server_id` = ? GROUP BY `user_id`;', SERVER_ID);
                    $rUsers = $ipTV_db->num_rows();
                    $rResult = $ipTV_db->query('UPDATE `servers` SET `watchdog_data` = ?, `last_check_ago` = UNIX_TIMESTAMP(), `requests_per_second` = ?, `php_pids` = ?, `connections` = ?, `users` = ? WHERE `id` = ?;', json_encode($rStats, JSON_PARTIAL_OUTPUT_ON_ERROR), $rRequestsPerSecond, json_encode($rPHPPIDs), $rConnections, $rUsers, SERVER_ID);
                } else {
                    $rResult = $ipTV_db->query('UPDATE `servers` SET `watchdog_data` = ?, `last_check_ago` = UNIX_TIMESTAMP(), `requests_per_second` = ?, `php_pids` = ? WHERE `id` = ?;', json_encode($rStats, JSON_PARTIAL_OUTPUT_ON_ERROR), $rRequestsPerSecond, json_encode($rPHPPIDs), SERVER_ID);
                }
                if ($rResult) {
                    if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
                        if (ipTV_lib::$settings['redis_handler']) {
                            $rMulti = ipTV_lib::$redis->multi();
                            foreach (array_keys(ipTV_lib::$Servers) as $rServerID) {
                                if (ipTV_lib::$Servers[$rServerID]['server_online']) {
                                    $rMulti->zCard('SERVER#' . $rServerID);
                                    $rMulti->zRangeByScore('SERVER_LINES#' . $rServerID, '-inf', '+inf', array('withscores' => true));
                                }
                            }
                            $rResults = $rMulti->exec();
                            $rTotalUsers = array();
                            $i = 0;
                            foreach (array_keys(ipTV_lib::$Servers) as $rServerID) {
                                if (ipTV_lib::$Servers[$rServerID]['server_online']) {
                                    $ipTV_db->query('UPDATE `servers` SET `connections` = ?, `users` = ? WHERE `id` = ?;', $rResults[$i * 2], count(array_unique(array_values($rResults[$i * 2 + 1]))), $rServerID);
                                    $rTotalUsers = array_merge(array_values($rResults[$i * 2 + 1]), $rTotalUsers);
                                    $i++;
                                }
                            }
                            ipTV_lib::setSettings(["total_users" => count(array_unique($rTotalUsers))]);
                        } else {
                            $ipTV_db->query('SELECT `activity_id` FROM `lines_live` WHERE `hls_end` = 0 GROUP BY `user_id`;');
                            $rTotalUsers = $ipTV_db->num_rows();
                            ipTV_lib::setSettings(["total_users" => $rTotalUsers]);
                        }
                    }
                    sleep(2);
                } else {
                    echo 'Failed, break.' . "\n";
                }
                break;
            }
            if (ipTV_lib::isRunning()) {
                if (md5_file(__FILE__) == $rMD5) {
                    ipTV_lib::$Servers = ipTV_lib::getServers(true);
                    ipTV_lib::$settings = ipTV_lib::getSettings(true);
                    ipTV_streaming::getCapacity();
                    $rLastCheck = time();
                } else {
                    echo 'File changed! Break.' . "\n";
                }
            } else {
                echo 'Not running! Break.' . "\n";
            }
        }
        if (is_object($ipTV_db)) {
            $ipTV_db->close_mysql();
        }
        shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
