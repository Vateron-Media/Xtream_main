<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xui') {
    if ($argc) {
        require str_replace('\\', '/', dirname($argv[0])) . '/../../www/init.php';
        shell_exec('kill $(ps aux | grep watchdog | grep -v grep | grep -v ' . getmypid() . " | awk '{print \$2}')");
        $rInterval = (intval(ipTV_lib::$settings['online_capacity_interval']) ?: 10);
        $rLastRequests = $rLastRequestsTime = $rPrevStat = $rLastCheck = null;
        $rMD5 = md5_file(__FILE__);
        $rWatchdog = json_decode(ipTV_lib::$StreamingServers[SERVER_ID]['watchdog_data'], true);
        $rCPUAverage = ($rWatchdog['cpu_average_array'] ?: array());
        while (true && $db->ping()) {
            if ($rLastCheck && $rInterval > time() - $rLastCheck) {
                $rNginx = explode("\n", file_get_contents('http://127.0.0.1:' . ipTV_lib::$StreamingServers[SERVER_ID]['http_broadcast_port'] . '/nginx_status'));
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
                exec("ps -u xui | grep php-fpm | awk {'print \$1'}", $rPHPPIDs);
                $rConnections = $rUsers = 0;
                $db->query('SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `hls_end` = 0 AND `server_id` = \'%s\';', SERVER_ID);
                $rConnections = $db->get_row()['count'];
                $db->query('SELECT `activity_id` FROM `lines_live` WHERE `hls_end` = 0 AND `server_id` = \'%s\' GROUP BY `user_id`;', SERVER_ID);
                $rUsers = $db->num_rows();
                $rResult = $db->query('UPDATE `servers` SET `watchdog_data` = \'%s\', `last_check_ago` = UNIX_TIMESTAMP(), `requests_per_second` = \'%s\', `php_pids` = \'%s\', `connections` = \'%s\', `users` = \'%s\' WHERE `id` = \'%s\';', json_encode($rStats, JSON_PARTIAL_OUTPUT_ON_ERROR), $rRequestsPerSecond, json_encode($rPHPPIDs), $rConnections, $rUsers, SERVER_ID);

                if ($rResult) {
                    if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
                        $db->query('SELECT `activity_id` FROM `lines_live` WHERE `hls_end` = 0 GROUP BY `user_id`;');
                        $rTotalUsers = $db->num_rows();
                        $db->query('UPDATE `settings` SET `total_users` = \'%s\';', $rTotalUsers);
                    }
                    sleep(2);
                } else {
                    echo 'Failed, break.' . "\n";
                }
                break;
            }
            if (md5_file(__FILE__) == $rMD5) {
                ipTV_lib::$StreamingServers = ipTV_lib::getServers(true);
                ipTV_lib::$settings = ipTV_lib::getSettings(true);
                ipTV_streaming::getCapacity();
                $rLastCheck = time();
            } else {
                echo 'File changed! Break.' . "\n";
            }
        }
        if (is_object($db)) {
            $db->close_mysql();
        }
        shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
    } else {
        exit(0);
    }
} else {
    exit('Please run as XUI!' . "\n");
}
