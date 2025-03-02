<?php

if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XC_VMrs]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::checkCron($unique_id);
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}

function loadCron() {
    global $ipTV_db;
    ipTV_lib::$settings = ipTV_lib::getSettings(true);
    if (ipTV_lib::isRunning()) {
        $rServers = ipTV_lib::getServers(true);
        if ($rServers[SERVER_ID]['is_main'] && ipTV_lib::$settings['redis_handler']) {
            exec('pgrep -u xc_vm redis-server', $rRedis);
            if (count($rRedis) == 0) {
                echo 'Restarting Redis!' . "\n";
                shell_exec(MAIN_DIR . 'bin/redis/redis-server ' . MAIN_DIR . '/bin/redis/redis.conf > /dev/null 2>/dev/null &');
            }
        }
        #create all network stats
        getNetworkStats();
        $rSignals = intval(trim(shell_exec('pgrep -U xc_vm | xargs ps -f -p | grep signals | grep -v grep | grep -v pgrep | wc -l')));
        if ($rSignals == 0) {
            shell_exec(PHP_BIN . ' ' . CLI_PATH . 'signals.php > /dev/null 2>/dev/null &');
        }
        if ($rServers[SERVER_ID]['is_main']) {
            $rCache = intval(trim(shell_exec('pgrep -U xc_vm | xargs ps -f -p | grep cache_handler | grep -v grep | grep -v pgrep | wc -l')));
            if (ipTV_lib::$settings['enable_cache'] && $rCache == 0) {
                shell_exec(PHP_BIN . ' ' . CLI_PATH . 'cache_handler.php > /dev/null 2>/dev/null &');
            } else {
                if (!ipTV_lib::$settings['enable_cache'] || $rCache > 0) {
                    echo 'Killing Cache Handler' . "\n";
                    exec("pgrep -U xc_vm | xargs ps | grep cache_handler | awk '{print \$1}'", $rPIDs);
                    foreach ($rPIDs as $rPID) {
                        if (intval($rPID) > 0) {
                            shell_exec('kill -9 ' . intval($rPID));
                        }
                    }
                }
            }
        }
        $rWatchdog = intval(trim(shell_exec('pgrep -U xc_vm | xargs ps -f -p | grep watchdog | grep -v grep | grep -v pgrep | wc -l')));
        if ($rWatchdog == 0) {
            shell_exec(PHP_BIN . ' ' . CLI_PATH . 'watchdog.php > /dev/null 2>/dev/null &');
        }
        $rQueue = intval(trim(shell_exec('pgrep -U xc_vm | xargs ps -f -p | grep queue | grep -v grep | grep -v pgrep | wc -l')));
        if ($rQueue == 0) {
            shell_exec(PHP_BIN . ' ' . CLI_PATH . 'queue.php > /dev/null 2>/dev/null &');
        }
        $rOnDemand = intval(trim(shell_exec('pgrep -U xc_vm | xargs ps -f -p | grep ondemand | grep -v grep | grep -v pgrep | wc -l')));
        if (ipTV_lib::$settings['on_demand_instant_off'] && $rOnDemand == 0) {
            shell_exec(PHP_BIN . ' ' . CLI_PATH . 'ondemand.php > /dev/null 2>/dev/null &');
        } else {
            if (!ipTV_lib::$settings['on_demand_instant_off'] || $rOnDemand > 0) {
                echo 'Killing On-Demand Instant-Off' . "\n";
                exec("pgrep -U xc_vm | xargs ps | grep ondemand | awk '{print \$1}'", $rPIDs);
                foreach ($rPIDs as $rPID) {
                    if (intval($rPID) > 0) {
                        shell_exec('kill -9 ' . intval($rPID));
                    }
                }
            }
        }
        // $rScanner = intval(trim(shell_exec('pgrep -U xc_vm | xargs ps -f -p | grep scanner | grep -v grep | grep -v pgrep | wc -l')));
        // if (ipTV_lib::$settings['on_demand_checker'] && $rScanner == 0) {
        //     shell_exec(PHP_BIN . ' ' . CLI_PATH . 'scanner.php > /dev/null 2>/dev/null &');
        // } else {
        //     if (!ipTV_lib::$settings['on_demand_checker'] || $rScanner > 0) {
        //         echo 'Killing On-Demand Scanner' . "\n";
        //         exec("pgrep -U xc_vm | xargs ps | grep scanner | awk '{print \$1}'", $rPIDs);
        //         foreach ($rPIDs as $rPID) {
        //             if (intval($rPID) > 0) {
        //                 shell_exec('kill -9 ' . intval($rPID));
        //             }
        //         }
        //     }
        // }
        $rStats = getStats();
        $rWatchdog = json_decode($rServers[SERVER_ID]['watchdog_data'], true);
        $rCPUAverage = ($rWatchdog['cpu_average_array'] ?: array());
        if (count($rCPUAverage) > 0) {
            $rStats['cpu'] = round(array_sum($rCPUAverage) / count($rCPUAverage), 2);
        }
        $rHardware = array('total_ram' => $rStats['total_mem'], 'total_used' => $rStats['total_mem_used'], 'cores' => $rStats['cpu_cores'], 'threads' => $rStats['cpu_cores'], 'kernel' => $rStats['kernel'], 'total_running_streams' => $rStats['total_running_streams'], 'cpu_name' => $rStats['cpu_name'], 'cpu_usage' => $rStats['cpu'], 'network_speed' => $rStats['network_speed'], 'bytes_sent' => $rStats['bytes_sent'], 'bytes_received' => $rStats['bytes_received']);
        if (fsockopen($rServers[SERVER_ID]['server_ip'], $rServers[SERVER_ID]['http_broadcast_port'], $rErrNo, $rErrStr, 3) || fsockopen($rServers[SERVER_ID]['server_ip'], $rServers[SERVER_ID]['https_broadcast_port'], $rErrNo, $rErrStr, 3)) {
            $rRemoteStatus = true;
        } else {
            $rRemoteStatus = false;
        }
        if (ipTV_lib::$settings['redis_handler']) {
            $rConnections = $rServers[SERVER_ID]['connections'];
            $rUsers = $rServers[SERVER_ID]['users'];
            $rAllUsers = 0;
            foreach (array_keys($rServers) as $rServerID) {
                if ($rServers[$rServerID]['server_online']) {
                    $rAllUsers += $rServers[$rServerID]['users'];
                }
            }
        } else {
            $ipTV_db->query('SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `server_id` = ? AND `hls_end` = 0;', SERVER_ID);
            $rConnections = intval($ipTV_db->get_row()['count']);
            $ipTV_db->query('SELECT `activity_id` FROM `lines_live` WHERE `server_id` = ? AND `hls_end` = 0 GROUP BY `user_id`;', SERVER_ID);
            $rUsers = intval($ipTV_db->num_rows());
            $ipTV_db->query('SELECT `activity_id` FROM `lines_live` WHERE `hls_end` = 0 GROUP BY `user_id`;');
            $rAllUsers = intval($ipTV_db->num_rows());
        }
        $ipTV_db->query('SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = ? AND `pid` > 0 AND `type` = 1;', SERVER_ID);
        $rStreams = intval($ipTV_db->get_row()['count']);
        $rPing = 0;
        if (!$rServers[SERVER_ID]['is_main']) {
            $rMainID = null;
            foreach ($rServers as $rServerID => $rServerArray) {
                if ($rServerArray['is_main']) {
                    $rMainID = $rServerID;
                    break;
                }
            }
            if ($rMainID) {
                $rPing = pingServer($rServers[$rMainID]['server_ip'], $rServers[$rMainID]['http_broadcast_port']);
            }
        }
        $rSysCtl = file_get_contents('/etc/sysctl.conf');
        $rAddresses = array_values(array_unique(array_map('trim', explode("\n", shell_exec("ip -4 addr | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'")))));
        // $ipTV_db->query('INSERT INTO `servers_stats`(`server_id`, `connections`, `total_users`, `users`, `streams`, `cpu`, `cpu_cores`, `cpu_avg`, `total_mem`, `total_mem_free`, `total_mem_used`, `total_mem_used_percent`, `total_disk_space`, `uptime`, `total_running_streams`, `bytes_sent`, `bytes_received`, `bytes_sent_total`, `bytes_received_total`, `cpu_load_average`, `gpu_info`, `iostat_info`, `time`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP());', SERVER_ID, $rConnections, $rAllUsers, $rUsers, $rStreams, $rStats['cpu'], $rStats['cpu_cores'], $rStats['cpu_avg'], $rStats['total_mem'], $rStats['total_mem_free'], $rStats['total_mem_used'], $rStats['total_mem_used_percent'], $rStats['total_disk_space'], $rStats['uptime'], $rStats['total_running_streams'], $rStats['bytes_sent'], $rStats['bytes_received'], $rStats['bytes_sent_total'], $rStats['bytes_received_total'], $rStats['cpu_load_average'], json_encode($rStats['gpu_info'], JSON_UNESCAPED_UNICODE), json_encode($rStats['iostat_info'], JSON_UNESCAPED_UNICODE));
        $ipTV_db->query('UPDATE `servers` SET `remote_status` = ?, `script_version` = ?, `server_hardware` = ?,`whitelist_ips` = ?, `sysctl` = ?, `video_devices` = ?, `audio_devices` = ?, `gpu_info` = ?, `interfaces` = ?, `time_offset` = ' . intval(time()) . ' - UNIX_TIMESTAMP(), `ping` = ? WHERE `id` = ?', $rRemoteStatus, SCRIPT_VERSION, json_encode($rHardware, JSON_UNESCAPED_UNICODE), json_encode($rAddresses, JSON_UNESCAPED_UNICODE), $rSysCtl, json_encode($rStats['video_devices'], JSON_UNESCAPED_UNICODE), json_encode($rStats['audio_devices'], JSON_UNESCAPED_UNICODE), json_encode($rStats['gpu_info'], JSON_UNESCAPED_UNICODE), json_encode($rStats['interfaces'], JSON_UNESCAPED_UNICODE), $rPing, SERVER_ID);
        if ($rServers[SERVER_ID]['is_main']) {
            foreach ($rServers as $rServerID => $rServerArray) {
                if ($rServerArray['server_online'] != $rServerArray['last_status']) {
                    $ipTV_db->query('UPDATE `servers` SET `last_status` = ? WHERE `id` = ?;', $rServerArray['server_online'], $rServerID);
                }
            }
            $ipTV_db->query('DELETE FROM `signals` WHERE `time` <= ?;', time() - 86400);
        }
    } else {
        echo 'XC_VM not running...' . "\n";
    }
}

function getNetworkStats() {
    $interfaces_dir = '/sys/class/net/';
    $interfaces = array_diff(scandir($interfaces_dir), array('..', '.'));

    $network_stats = [];

    foreach ($interfaces as $interface) {
        $stats = [];
        $stats['status'] = trim(file_get_contents($interfaces_dir . $interface . '/operstate'));
        $stats['rx_packets'] = trim(file_get_contents($interfaces_dir . $interface . '/statistics/rx_packets'));
        $stats['tx_packets'] = trim(file_get_contents($interfaces_dir . $interface . '/statistics/tx_packets'));
        $bytesSentOld = trim(file_get_contents($interfaces_dir . $interface . '/statistics/tx_bytes'));
        $bytesReceivedOld = trim(file_get_contents($interfaces_dir . $interface . '/statistics/rx_bytes'));
        sleep(1);
        $bytesSent = trim(file_get_contents($interfaces_dir . $interface . "/statistics/tx_bytes"));
        $bytesReceived = trim(file_get_contents($interfaces_dir . $interface . "/statistics/rx_bytes"));
        $total_bytes_sent = round(($bytesSent - $bytesSentOld) / 1024 * 0.0078125, 2);
        $total_bytes_received = round(($bytesReceived - $bytesReceivedOld) / 1024 * 0.0078125, 2);
        $stats['bytes_sent'] = $total_bytes_sent;
        $stats['bytes_received'] = $total_bytes_received;

        $network_stats[$interface] = $stats;
    }

    # write to file network
    file_put_contents(LOGS_TMP_PATH . "network", json_encode($network_stats), LOCK_EX);
}

function pingServer($rIP, $rPort) {
    $rStartTime = microtime(true);
    $rSocket = fsockopen($rIP, $rPort, $rErrNo, $rErrStr, 3);
    $rStopTime = microtime(true);
    if (!$rSocket) {
        $rStatus = -1;
    } else {
        fclose($rSocket);
        $rStatus = floor(($rStopTime - $rStartTime) * 1000);
    }
    return $rStatus;
}

function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
