<?php
set_time_limit(0);
if ($argc) {
    register_shutdown_function('shutdown');
    require str_replace('\\', '/', dirname($argv[0])) . '/../www/init.php';
    cli_set_process_title('XtreamCodes[Errors]');
    $unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
    ipTV_lib::check_cron($unique_id);
    $rIgnoreErrors = array('the user-agent option is deprecated', 'last message repeated', 'deprecated', 'packets poorly interleaved', 'invalid timestamps', 'timescale not set', 'frame size not set', 'non-monotonous dts in output stream', 'invalid dts', 'no trailing crlf', 'failed to parse extradata', 'truncated', 'missing picture', 'non-existing pps', 'clipping', 'out of range', 'cannot use rename on non file protocol', 'end of file', 'stream ends prematurely');
    loadCron();
} else {
    exit(0);
}

function parseLog($rLog) {
    global $ipTV_db;
    $errorHashes = array();
    $rQuery = '';
    if (!file_exists($rLog)) {
    } else {
        $rFP = fopen($rLog, 'r');
        while (!feof($rFP)) {
            $rLine = trim(fgets($rFP));
            if (!empty($rLine)) {
                $rLine = json_decode(base64_decode($rLine), true);
                $errorHash = md5($rLine['type'] . $rLine['message'] . $rLine['extra'] . $rLine['line']);
                if (in_array($errorHash, $errorHashes)) {
                } else {
                    if (!(stripos($rLine['message'], 'server has gone away') !== false && stripos($rLine['message'], 'socket error on read socket') !== false && stripos($rLine['message'], 'connection lost') !== false)) {
                        $rLine = array_map(array($ipTV_db, 'escape'), $rLine);
                        $rQuery .= '(' . SERVER_ID . ',' . $rLine['type'] . ',' . $rLine['message'] . ',' . $rLine['extra'] . ',' . $rLine['line'] . ',' . $rLine['time'] . ",'" . $errorHash . "'),";
                        $errorHashes[] = $errorHash;
                    }
                }
                break;
            }
        }
        fclose($rFP);
    }
    return $rQuery;
}
function inArray($needles, $haystack) {
    foreach ($needles as $needle) {
        if (!stristr($haystack, $needle)) {
        } else {
            return true;
        }
    }
    return false;
}
function loadCron() {
    global $rIgnoreErrors;
    global $ipTV_db;
    $rQuery = '';
    // foreach (array(STREAMS_PATH) as $rPath) {
    //     if (!($rHandle = opendir($rPath))) {
    //     } else {
    //         while (false !== ($fileEntry = readdir($rHandle))) {
    //             if (!($fileEntry != '.' && $fileEntry != '..' && is_file($rPath . $fileEntry))) {
    //             } else {
    //                 $rFile = $rPath . $fileEntry;
    //                 list($rStreamID, $rExtension) = explode('.', $fileEntry);
    //                 if ($rExtension != 'errors') {
    //                 } else {
    //                     $rErrors = array_values(array_unique(array_map('trim', explode("\n", file_get_contents($rFile)))));
    //                     foreach ($rErrors as $rError) {
    //                         if (!(empty($rError) || inArray($rIgnoreErrors, $rError))) {
    //                             if (ipTV_lib::$settings['stream_logs_save']) {
    //                                 $rQuery .= '(' . $rStreamID . ',' . SERVER_ID . ',' . time() . ',' . $ipTV_db->escape($rError) . '),';
    //                             }
    //                         }
    //                     }
    //                     unlink($rFile);
    //                 }
    //             }
    //         }
    //         closedir($rHandle);
    //     }
    // }
    // if (ipTV_lib::$settings['stream_logs_save'] || empty($rQuery)) {
    //     $rQuery = rtrim($rQuery, ',');
    //     $ipTV_db->query('INSERT INTO `streams_errors` (`stream_id`,`server_id`,`date`,`error`) VALUES ' . $rQuery . ';');
    // }
    $rLog = TMP_DIR . 'error_log.log';
    if (!file_exists($rLog)) {
    } else {
        $rQuery = rtrim(parseLog($rLog), ',');
        $ipTV_db->query('INSERT IGNORE INTO `panel_logs` (`server_id`,`type`,`log_message`,`log_extra`,`line`,`date`,`unique`) VALUES ' . $rQuery . ';');
        unlink($rLog);
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
