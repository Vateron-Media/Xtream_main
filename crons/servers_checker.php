<?php

if ($argc) {
    register_shutdown_function('shutdown');
    require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
    cli_set_process_title('XtreamCodes[Server Checker]');
    $unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
    ipTV_lib::check_cron($unique_id);
    loadCron();
} else {
    exit(0);
}
function loadCron() {
    global $ipTV_db;
    $rServers = ipTV_lib::getServers(true);
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
    $rAddresses = array_values(array_unique(array_map('trim', explode("\n", shell_exec("ip -4 addr | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'")))));
    $ipTV_db->query('UPDATE `streaming_servers` SET `remote_status` = \'%s\', `server_hardware` = \'%s\', `server_hardware` = \'%s\',`whitelist_ips` = \'%s\', `time_offset` = ' . intval(time()) . ' - UNIX_TIMESTAMP(), `script_version` = \'%s\' WHERE `id` = \'%s\'', $rRemoteStatus, json_encode($rHardware, JSON_UNESCAPED_UNICODE), json_encode($rHardware, JSON_UNESCAPED_UNICODE), json_encode($rAddresses, JSON_UNESCAPED_UNICODE), SCRIPT_VERSION, SERVER_ID);
}
function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
