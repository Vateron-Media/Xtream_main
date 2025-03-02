<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    set_time_limit(0);
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XC_VM[Activity]');
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
    $rLogFile = LOGS_TMP_PATH . 'activity';
    $rUpdateQuery = $rQuery = '';
    $rUpdates = array();
    $rCount = 0;
    if (file_exists($rLogFile)) {
        list($rQuery, $rUpdates, $rCount) = parseLog($rLogFile);
        unlink($rLogFile);
    }
    if ($rCount > 0) {
        $rQuery = rtrim($rQuery, ',');
        if (!empty($rQuery)) {
            if ($ipTV_db->query('INSERT INTO `user_activity` (`server_id`,`user_id`,`isp`,`external_device`,`stream_id`,`date_start`,`user_agent`,`user_ip`,`date_end`,`container`,`geoip_country_code`,`divergence`) VALUES ' . $rQuery)) {
                $rFirstID = $ipTV_db->last_insert_id();
                $i = 0;
                while ($i < $rCount) {
                    $rUpdateQuery .= '(' . $rUpdates[$i][0] . ',' . $ipTV_db->escape($rUpdates[$i][1]) . ',' . ($rFirstID + $i) . ',' . $ipTV_db->escape($rUpdates[$i][2]) . '),';
                    $i++;
                }
            }
        }
    }
    $rUpdateQuery = rtrim($rUpdateQuery, ',');
    if (!empty($rUpdateQuery)) {
        $ipTV_db->query('INSERT INTO `lines`(`id`,`last_ip`,`last_activity`,`last_activity_array`) VALUES ' . $rUpdateQuery . ' ON DUPLICATE KEY UPDATE `id`=VALUES(`id`), `last_ip`=VALUES(`last_ip`), `last_activity`=VALUES(`last_activity`), `last_activity_array`=VALUES(`last_activity_array`);');
    }
}
function parseLog($rFile) {
    global $ipTV_db;
    $rQuery = '';
    $rUpdates = array();
    $rCount = 0;
    if (file_exists($rFile)) {
        $rFP = fopen($rFile, 'r');
        while (!feof($rFP)) {
            $rLine = trim(fgets($rFP));
            if (!empty($rLine)) {
                $rLine = json_decode(base64_decode($rLine), true);
                if ($rLine['server_id'] && $rLine['user_id'] && $rLine['stream_id'] && $rLine['user_ip']) {
                    $rUpdates[] = array($rLine['user_id'], $rLine['user_ip'], json_encode(array('date_end' => $rLine['date_end'], 'stream_id' => $rLine['stream_id'])));
                    $rLine = array_map(array($ipTV_db, 'escape'), $rLine);
                    $rQuery .= '(' . $rLine['server_id'] . ',' . $rLine['user_id'] . ',' . $rLine['isp'] . ',' . $rLine['external_device'] . ',' . $rLine['stream_id'] . ',' . $rLine['date_start'] . ',' . $rLine['user_agent'] . ',' . $rLine['user_ip'] . ',' . $rLine['date_end'] . ',' . $rLine['container'] . ',' . $rLine['geoip_country_code'] . ',' . $rLine['divergence'] . '),';
                    $rCount++;
                }
                break;
            }
        }
        fclose($rFP);
    }
    return array($rQuery, $rUpdates, $rCount);
}
function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
