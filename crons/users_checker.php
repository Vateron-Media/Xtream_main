<?php
set_time_limit(0);
ini_set('memory_limit', -1);
if ($argc) {
    register_shutdown_function('shutdown');
    require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
    cli_set_process_title('XtreamCodes[Users Parser]');
    $unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
    ipTV_lib::check_cron($unique_id);
    $rSync = null;
    loadCron();
} else {
    exit(0);
}

function loadCron() {
    global $ipTV_db;

    $rConnectionSpeeds = glob(DIVERGENCE_TMP_PATH . '*');
    if (count($rConnectionSpeeds) > 0) {
        $rBitrates = array();
        $ipTV_db->query('SELECT `lines_live`.`uuid`, `streams_servers`.`bitrate` FROM `lines_live` LEFT JOIN `streams_servers` ON `lines_live`.`stream_id` = `streams_servers`.`stream_id` AND `lines_live`.`server_id` = `streams_servers`.`server_id` WHERE `lines_live`.`server_id` = \'%d\';', SERVER_ID);
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
