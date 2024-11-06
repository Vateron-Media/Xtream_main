<?php
if ($argc) {
    require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
    cli_set_process_title('XtreamCodes[Server WatchDog]');
    shell_exec('kill $(ps aux | grep \'Server WatchDog\' | grep -v grep | grep -v ' . getmypid() . " | awk '{print \$2}')");
    $rInterval = (intval(ipTV_lib::$settings['online_capacity_interval']) ?: 10);
    $rPrevStat = $rLastCheck = null;
    $rMD5 = md5_file(__FILE__);
    $rWatchdog = json_decode(ipTV_lib::$Servers[SERVER_ID]['watchdog_data'], true);
    $rCPUAverage = ($rWatchdog['cpu_average_array'] ?: array());
    while (true) {
        if ($rLastCheck && $rInterval > time() - $rLastCheck) {
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

            exec("ps -u xtreamcodes | grep php-fpm | awk {'print \$1'}", $rPHPPIDs);
            $rResult = $ipTV_db->query('UPDATE `streaming_servers` SET `watchdog_data` = \'%s\', `last_check_ago` = UNIX_TIMESTAMP(), `php_pids` = \'%s\' WHERE `id` = \'%s\';', json_encode($rStats, JSON_PARTIAL_OUTPUT_ON_ERROR), json_encode($rPHPPIDs), SERVER_ID);

            if ($rResult) {
                sleep(2);
            } else {
                echo 'Failed, break.' . "\n";
            }
            break;
        }
        if (md5_file(__FILE__) == $rMD5) {
            ipTV_lib::$Servers = ipTV_lib::getServers(true);
            ipTV_lib::$settings = ipTV_lib::getSettings(true);
            ipTV_streaming::getCapacity();
            $rLastCheck = time();
        } else {
            echo 'File changed! Break.' . "\n";
        }
        if (is_object($ipTV_db)) {
            $ipTV_db->close_mysql();
        }
    }
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
} else {
    exit(0);
}
