<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XtreamCodes[Servers]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::check_cron($unique_id);
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as XtreamCodes!' . "\n");
}
function loadCron() {
    global $ipTV_db;
    if (ipTV_lib::isRunning()) {
        $rServers = ipTV_lib::getServers(true);

        #create all network stats
        getNetworkStats();

        $rSignals = intval(trim(shell_exec('ps aux | grep \'Signal Receiver\' | grep -v grep | wc -l')));
        if ($rSignals == 0) {
            shell_exec(PHP_BIN . ' ' . TOOLS_PATH . 'signals.php > /dev/null 2>/dev/null &');
        }
        $rWatchdog = intval(trim(shell_exec('ps aux | grep \'Server WatchDog\' | grep -v grep | wc -l')));
        if ($rWatchdog == 0) {
            shell_exec(PHP_BIN . ' ' . TOOLS_PATH . 'watchdog.php > /dev/null 2>/dev/null &');
        }
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
                $rPing = pingserver($rServers[$rMainID]['server_ip'], $rServers[$rMainID]['http_broadcast_port']);
            }
        }
        $rAddresses = array_values(array_unique(array_map('trim', explode("\n", shell_exec("ip -4 addr | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'")))));
        $ipTV_db->query('UPDATE `streaming_servers` SET `remote_status` = \'%s\', `script_version` = \'%s\', `server_hardware` = \'%s\',`whitelist_ips` = \'%s\', `interfaces` = \'%s\', `time_offset` = ' . intval(time()) . ' - UNIX_TIMESTAMP(), `ping` = \'%s\' WHERE `id` = \'%s\'', $rRemoteStatus, SCRIPT_VERSION, json_encode($rHardware, JSON_UNESCAPED_UNICODE), json_encode($rAddresses, JSON_UNESCAPED_UNICODE), json_encode($rStats['interfaces'], JSON_UNESCAPED_UNICODE), $rPing, SERVER_ID);
        if ($rServers[SERVER_ID]['is_main']) {
            foreach ($rServers as $rServerID => $rServerArray) {
                if ($rServerArray['server_online'] != $rServerArray['last_status']) {
                    $ipTV_db->query('UPDATE `streaming_servers` SET `last_status` = \'%s\' WHERE `id` = \'%s\';', $rServerArray['server_online'], $rServerID);
                }
            }
            $ipTV_db->query('DELETE FROM `signals` WHERE `time` <= \'%s\';', time() - 86400);
        }
    } else {
        echo 'XtreamCodes not running...' . "\n";
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
