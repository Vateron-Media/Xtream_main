<?php
//Cleanup

if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    define("MAIN_DIR", "/home/xc_vm/");
    define('CONFIG_PATH', MAIN_DIR . 'config/');

    function xor_parse($data, $key) {
        $i = 0;
        $output = '';
        foreach (str_split($data) as $char) {
            $output .= chr(ord($char) ^ ord($key[$i++ % strlen($key)]));
        }
        return $output;
    }

    $_INFO = parse_ini_file(CONFIG_PATH . 'config.ini');
    if (!$db = new mysqli($_INFO['hostname'], $_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['port'])) {
        exit("No MySQL connection!");
    }

    // Collect live PID's
    $rLive = explode("\n", trim(shell_exec("pgrep ffmpeg")));
    echo count($rLive) . " live processes found\n";
    // Collect required PID's
    $rPIDs = array();
    $result = $db->query("SELECT DISTINCT(`pid`) AS `pid` FROM `streams_servers` WHERE `pid` > 0 AND `pid` IS NOT NULL AND `server_id` = " . intval($_INFO["server_id"]) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rPIDs[] = $row["pid"];
        }
    }
    $result = $db->query("SELECT DISTINCT(`monitor_pid`) AS `pid` FROM `streams_servers` WHERE `monitor_pid` > 0 AND `monitor_pid` IS NOT NULL AND `server_id` = " . intval($_INFO["server_id"]) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rPIDs[] = $row["pid"];
        }
    }
    $result = $db->query("SELECT DISTINCT(`delay_pid`) AS `pid` FROM `streams_servers` WHERE `delay_pid` > 0 AND `delay_pid` IS NOT NULL AND `server_id` = " . intval($_INFO["server_id"]) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rPIDs[] = $row["pid"];
        }
    }
    $result = $db->query("SELECT DISTINCT(`tv_archive_pid`) AS `pid` FROM `streams` WHERE `tv_archive_pid` > 0 AND `tv_archive_pid` IS NOT NULL AND `tv_archive_server_id` = " . intval($_INFO["server_id"]) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rPIDs[] = $row["pid"];
        }
    }
    $result = $db->query("SELECT DISTINCT(`pid`) AS `pid` FROM `lines_live` WHERE `pid` > 0 AND `pid` IS NOT NULL AND `server_id` = " . intval($_INFO["server_id"]) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rPIDs[] = $row["pid"];
        }
    }
    echo count($rPIDs) . " required processes found\n";
    // Kill redundant PID's.
    foreach ($rLive as $rPID) {
        if (!in_array($rPID, $rPIDs)) {
            echo "Killed redundant process " . $rPID . "\n";
            shell_exec("kill -9 " . $rPID);
        }
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
