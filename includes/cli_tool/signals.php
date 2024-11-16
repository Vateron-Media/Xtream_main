<?php
if ($argc) {
    set_time_limit(0);
    require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
    cli_set_process_title('XtreamCodes[Signal Receiver]');
    shell_exec('kill $(ps aux | grep \'Signal Receiver\' | grep -v grep | grep -v ' . getmypid() . ' | awk \'{print $2}\')');
    $rMD5 = md5_file(__FILE__);
    while (true) {
        if ($ipTV_db->query('SELECT `signal_id`, `pid`, `rtmp` FROM `signals` WHERE `server_id` = \'%s\' AND `pid` IS NOT NULL ORDER BY `signal_id` ASC LIMIT 100', SERVER_ID)) {
            if ($ipTV_db->num_rows() > 0) {
                $rIDs = array();
                foreach ($ipTV_db->get_rows() as $rRow) {
                    $rIDs[] = $rRow['signal_id'];
                    $PID = $rRow['pid'];
                    if ($rRow['rtmp'] == 0) {
                        if (!empty($PID) && file_exists('/proc/' . $PID) && is_numeric($PID) && 0 < $PID) {
                            shell_exec('kill -9 ' . intval($PID));
                        }
                    } else {
                        shell_exec('wget --timeout=2 -O /dev/null -o /dev/null "' . ipTV_lib::$Servers[SERVER_ID]['rtmp_mport_url'] . 'control/drop/client?clientid=' . intval($PID) . '" >/dev/null 2>/dev/null &');
                    }
                }
                if (count($rIDs) > 0) {
                    $ipTV_db->query('DELETE FROM `signals` WHERE `signal_id` IN (' . implode(',', $rIDs) . ')');
                }
            }
            if ($ipTV_db->query('SELECT `signal_id`, `custom_data` FROM `signals` WHERE `server_id` = \'%s\' AND `cache` = 1 ORDER BY `signal_id` ASC LIMIT 1000;', SERVER_ID)) {
                if ($ipTV_db->num_rows() > 0) {
                    $rDeletedLines = $rUpdatedStreams = $rUpdatedLines = $rIDs = array();
                    foreach ($ipTV_db->get_rows() as $rRow) {
                        $rCustomData = json_decode($rRow['custom_data'], true);
                        $rIDs[] = $rRow['signal_id'];
                        switch ($rCustomData['type']) {
                            case 'update_stream':
                                if (!in_array($rCustomData['id'], $rUpdatedStreams)) {
                                    $rUpdatedStreams[] = $rCustomData['id'];
                                }
                                break;
                            case 'update_line':
                                if (!in_array($rCustomData['id'], $rUpdatedLines)) {
                                    $rUpdatedLines[] = $rCustomData['id'];
                                }
                                break;
                            case 'update_streams':
                                foreach ($rCustomData['id'] as $rID) {
                                    if (!in_array($rID, $rUpdatedStreams)) {
                                        $rUpdatedStreams[] = $rID;
                                    }
                                }
                                break;
                            case 'update_lines':
                                foreach ($rCustomData['id'] as $rID) {
                                    if (!in_array($rID, $rUpdatedLines)) {
                                        $rUpdatedLines[] = $rID;
                                    }
                                }
                                break;
                            case 'delete_con':
                                ipTV_lib::unlinkFile(CONS_TMP_PATH . $rCustomData['uuid']);
                                break;
                            case 'delete_vod':
                                exec('rm ' . VOD_PATH . intval($rCustomData['id']) . '.*');
                                break;
                            case 'delete_vods':
                                foreach ($rCustomData['id'] as $rID) {
                                    exec('rm ' . VOD_PATH . intval($rID) . '.*');
                                }
                                break;
                        }
                    }
                    if (count($rUpdatedStreams) > 0) {
                        shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "streams_update" "' . implode(',', $rUpdatedStreams) . '"');
                    }
                    if (count($rUpdatedLines) > 0) {
                        shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "users_update" "' . implode(',', $rUpdatedLines) . '"');
                    }
                    if (count($rIDs) > 0) {
                        $ipTV_db->query('DELETE FROM `signals` WHERE `signal_id` IN (' . implode(',', $rIDs) . ')');
                    }
                }
                usleep(250000);
            }
            break;
        }
    }
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
} else {
    exit(0);
}
