<?php
if ($argc) {
    register_shutdown_function('shutdown');
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    cli_set_process_title('XtreamCodes[Live Checker]');
    $unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
    ipTV_lib::check_cron($unique_id);
    loadCron();
} else {
    exit(0);
}

function loadCron() {
    global $ipTV_db;
    $activePIDs = array();
    $streamIDs = array();

    $ipTV_db->query("SELECT t2.stream_display_name, t1.stream_info, t1.stream_status, t1.progress_info, t1.stream_id, t1.monitor_pid, t1.on_demand, t1.server_stream_id, t1.pid, clients.online_clients, t2.tv_archive_pid FROM `streams_sys` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.direct_source = 0 INNER JOIN `streams_types` t3 ON t3.type_id = t2.type LEFT JOIN (SELECT stream_id, COUNT(*) AS online_clients FROM `lines_live` WHERE `server_id` = '%d' GROUP BY stream_id) AS clients ON clients.stream_id = t1.stream_id WHERE (t1.pid IS NOT NULL OR t1.stream_status <> 0 OR t1.to_analyze = 1) AND t1.server_id = '%d' AND t3.live = 1", SERVER_ID, SERVER_ID);
    if (0 < $ipTV_db->num_rows()) {
        $streams = $ipTV_db->get_rows();
        foreach ($streams as $stream) {
            echo 'Stream ID: ' . $stream["stream_id"] . "\n";
            $streamIDs[] = $stream["stream_id"];
            if (ipTV_streaming::CheckMonitorRunning($stream["monitor_pid"], $stream["stream_id"]) || $stream["on_demand"]) {
                if ($stream["on_demand"] == 1 && $stream["online_clients"] == 0) {
                    echo 'Stop on-demand stream...' . "\n\n";
                    ipTV_stream::stopStream($stream["stream_id"], true);
                }
                // if ($stream["tv_archive_server_id"] == SERVER_ID || !ipTV_streaming::isArchiveRunning($stream["tv_archive_pid"], $stream["stream_id"])) {
                //     echo 'Start TV Archive...' . "\n";
                //     shell_exec(PHP_BIN . ' ' . TOOLS_PATH . 'archive.php ' . intval($stream["stream_id"]) . ' >/dev/null 2>/dev/null & echo $!');
                // }
                foreach (glob(STREAMS_PATH . $stream["stream_id"] . '_*.ts.enc') as $File) {
                    if (!file_exists(rtrim($File, '.enc'))) {
                        unlink($File);
                    }
                }
                if (file_exists(STREAMS_PATH . $stream["stream_id"] . '_.pid')) {
                    $PID = intval(file_get_contents(STREAMS_PATH . $stream["stream_id"] . '_.pid'));
                } else {
                    $PID = intval(shell_exec("ps aux | grep -v grep | grep '/" . intval($stream["stream_id"]) . "_.m3u8' | awk '{print \$2}'"));
                }
                $activePIDs[] = intval($PID);
                $Playlist = STREAMS_PATH . $stream["stream_id"] . '_.m3u8';
                if (ipTV_streaming::isStreamRunning($PID, $stream["stream_id"]) && file_exists($Playlist)) {
                    echo 'Update Stream Information...' . "\n";
                    $Bitrate = ipTV_streaming::getStreamBitrate('live', STREAMS_PATH . $stream["stream_id"] . '_.m3u8');
                    if (file_exists(STREAMS_PATH . $stream["stream_id"] . '_.progress')) {
                        $Progress = file_get_contents(STREAMS_PATH . $stream["stream_id"] . '_.progress');
                        unlink(STREAMS_PATH . $stream['stream_id'] . '_.progress');
                        // if ($stream['fps_restart']) {
                        //     file_put_contents(STREAMS_PATH . $stream['stream_id'] . '_.progress_check', $Progress);
                        // }
                    } else {
                        $Progress = $stream['progress_info'];
                    }
                    if (file_exists(STREAMS_PATH . $stream['stream_id'] . '_.stream_info')) {
                        $streamInfo = file_get_contents(STREAMS_PATH . $stream['stream_id'] . '_.stream_info');
                        unlink(STREAMS_PATH . $stream['stream_id'] . '_.stream_info');
                    } else {
                        $streamInfo = $stream['stream_info'];
                    }
                    if ($stream['pid'] != $PID) {
                        $ipTV_db->query("UPDATE `streams_sys` SET `pid` = '%d', `progress_info` = '%s', `stream_info` = '%s', `bitrate` = '%d', WHERE `server_stream_id` = '%d'", $PID, $Progress, $streamInfo, $Bitrate, $stream['server_stream_id']);
                    } else {
                        $ipTV_db->query("UPDATE `streams_sys` SET `progress_info` = '%s',`stream_info` = '%s', `bitrate` = '%d' WHERE `server_stream_id` = '%d'", $Progress, $streamInfo, $Bitrate, $stream['server_stream_id']);
                    }
                }
                echo "\n";
            } else {
                echo 'Start monitor...' . "\n\n";
                ipTV_stream::startMonitor($stream['stream_id']);
                usleep(50000);
            }
        }
    }


    // not checked code
    // $ipTV_db->query('SELECT `streams`.`id` FROM `streams` LEFT JOIN `streams_sys` ON `streams_sys`.`stream_id` = `streams`.`id` WHERE `streams`.`direct_source` = 1 AND `streams`.`direct_proxy` = 1 AND `streams_sys`.`server_id` = ? AND `streams_sys`.`pid` > 0;', SERVER_ID);
    // if (0 < $ipTV_db->num_rows()) {
    //     foreach ($ipTV_db->get_rows() as $stream) {
    //         if (file_exists(STREAMS_PATH . $stream['id'] . '.analyse')) {
    //             $FFProbeOutput = ipTV_stream::analyzeStream(STREAMS_PATH . $stream['id'] . '.analyse', SERVER_ID);
    //             if ($FFProbeOutput) {
    //                 $Bitrate = $FFProbeOutput['bitrate'] / 1024;
    //             }
    //             echo 'Stream ID: ' . $stream['id'] . "\n";
    //             echo 'Update Stream Information...' . "\n";
    //             $ipTV_db->query('UPDATE `streams_sys` SET `bitrate` = '%d', `stream_info` = '%s' WHERE `stream_id` = ? AND `server_id` = ?', $Bitrate, json_encode($FFProbeOutput) $stream['id'], SERVER_ID);
    //         }
    //         $UUIDs = array();
    //         $Connections = ipTV_streaming::getConnections(SERVER_ID, null, $stream['id']);
    //         foreach ($Connections as $Items) {
    //             foreach ($Items as $Item) {
    //                 $UUIDs[] = $Item['uuid'];
    //             }
    //         }
    //         if ($Handle = opendir(CONS_TMP_PATH . $stream['id'] . '/')) {
    //             while (false !== ($Filename = readdir($Handle))) {
    //                 if ($Filename != '.' && $Filename != '..') {
    //                     if (!in_array($Filename, $UUIDs)) {
    //                         unlink(CONS_TMP_PATH . $stream['id'] . '/' . $Filename);
    //                     }
    //                 }
    //             }
    //             closedir($Handle);
    //         }
    //     }
    // }
    // not checked code



    $ipTV_db->query("SELECT `stream_id` FROM `streams_sys` WHERE `on_demand` = 1 AND `server_id` = '%d';", SERVER_ID);
    $OnDemandIDs = array_keys($ipTV_db->get_rows(true, 'stream_id'));
    $Processes = shell_exec('ps aux | grep XtreamCodes');
    if (preg_match_all('/XtreamCodes\\[(.*)\\]/', $Processes, $Matches)) {
        $Remove = array_diff($Matches[1], $streamIDs);
        $Remove = array_diff($Remove, $OnDemandIDs);
        foreach ($Remove as $streamID) {
            if (is_numeric($streamID)) {
                echo 'Kill Stream ID: ' . $streamID . "\n";
                shell_exec("kill -9 `ps -ef | grep '/" . intval($streamID) . "_.m3u8\\|XtreamCodes\\[" . intval($streamID) . "\\]' | grep -v grep | awk '{print \$2}'`;");
                shell_exec('rm -f ' . STREAMS_PATH . intval($streamID) . '_*');
            }
        }
    }
}
function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
