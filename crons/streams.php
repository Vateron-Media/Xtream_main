<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
        cli_set_process_title('XC_VM[Live Checker]');
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
    if (ipTV_lib::isRunning()) {
        if (ipTV_lib::$settings['redis_handler']) {
            ipTV_lib::connectRedis();
        }
        $activePIDs = array();
        $streamIDs = array();
        if (ipTV_lib::$settings['redis_handler']) {
            $ipTV_db->query('SELECT t2.stream_display_name, t1.stream_started, t1.stream_info, t2.fps_restart, t1.stream_status, t1.progress_info, t1.stream_id, t1.monitor_pid, t1.on_demand, t1.server_stream_id, t1.pid, servers_attached.attached, t2.vframes_server_id, t2.vframes_pid, t2.tv_archive_server_id, t2.tv_archive_pid FROM `streams_servers` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.direct_source = 0 INNER JOIN `streams_types` t3 ON t3.type_id = t2.type LEFT JOIN (SELECT `stream_id`, COUNT(*) AS `attached` FROM `streams_servers` WHERE `parent_id` = ? AND `pid` IS NOT NULL AND `pid` > 0 AND `monitor_pid` IS NOT NULL AND `monitor_pid` > 0) AS `servers_attached` ON `servers_attached`.`stream_id` = t1.`stream_id` WHERE (t1.pid IS NOT NULL OR t1.stream_status <> 0 OR t1.to_analyze = 1) AND t1.server_id = ? AND t3.live = 1', SERVER_ID, SERVER_ID);
        } else {
            $ipTV_db->query("SELECT t2.stream_display_name, t1.stream_started, t1.stream_info, t2.fps_restart, t1.stream_status, t1.progress_info, t1.stream_id, t1.monitor_pid, t1.on_demand, t1.server_stream_id, t1.pid, clients.online_clients, clients_hls.online_clients_hls, servers_attached.attached, t2.vframes_server_id, t2.vframes_pid, t2.tv_archive_server_id, t2.tv_archive_pid FROM `streams_servers` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.direct_source = 0 INNER JOIN `streams_types` t3 ON t3.type_id = t2.type LEFT JOIN (SELECT stream_id, COUNT(*) as online_clients FROM `lines_live` WHERE `server_id` = ? AND `hls_end` = 0 GROUP BY stream_id) AS clients ON clients.stream_id = t1.stream_id LEFT JOIN (SELECT `stream_id`, COUNT(*) AS `attached` FROM `streams_servers` WHERE `parent_id` = ? AND `pid` IS NOT NULL AND `pid` > 0 AND `monitor_pid` IS NOT NULL AND `monitor_pid` > 0) AS `servers_attached` ON `servers_attached`.`stream_id` = t1.`stream_id` LEFT JOIN (SELECT stream_id, COUNT(*) as online_clients_hls FROM `lines_live` WHERE `server_id` = ? AND `container` = 'hls' AND `hls_end` = 0 GROUP BY stream_id) AS clients_hls ON clients_hls.stream_id = t1.stream_id WHERE (t1.pid IS NOT NULL OR t1.stream_status <> 0 OR t1.to_analyze = 1) AND t1.server_id = ? AND t3.live = 1", SERVER_ID, SERVER_ID, SERVER_ID, SERVER_ID);
        }
        if ($ipTV_db->num_rows() > 0) {
            foreach ($ipTV_db->get_rows() as $stream) {
                echo 'Stream ID: ' . $stream['stream_id'] . "\n";
                $streamIDs[] = $stream['stream_id'];
                if (ipTV_streaming::checkMonitorRunning($stream['monitor_pid'], $stream['stream_id']) || $stream['on_demand']) {
                    if ($stream['on_demand'] == 1 && $stream['attached'] == 0) {
                        if (ipTV_lib::$settings['redis_handler']) {
                            $rCount = 0;
                            $rKeys = ipTV_lib::$redis->zRangeByScore('STREAM#' . $stream['stream_id'], '-inf', '+inf');
                            if (count($rKeys) > 0) {
                                $rConnections = array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rKeys));
                                foreach ($rConnections as $rConnection) {
                                    if ($rConnection && $rConnection['server_id'] == SERVER_ID) {
                                        $rCount++;
                                    }
                                }
                            }
                            $stream['online_clients'] = $rCount;
                        }
                        $rAdminQueue = $rQueue = 0;
                        if (ipTV_lib::$settings['on_demand_instant_off'] && file_exists(SIGNALS_TMP_PATH . 'queue_' . intval($stream['stream_id']))) {
                            foreach ((igbinary_unserialize(file_get_contents(SIGNALS_TMP_PATH . 'queue_' . intval($stream['stream_id']))) ?: array()) as $PID) {
                                if (ipTV_streaming::isProcessRunning($PID, 'php-fpm')) {
                                    $rQueue++;
                                }
                            }
                        }
                        if (file_exists(SIGNALS_TMP_PATH . 'admin_' . intval($stream['stream_id']))) {
                            if (time() - filemtime(SIGNALS_TMP_PATH . 'admin_' . intval($stream['stream_id'])) <= 30) {
                                $rAdminQueue = 1;
                            } else {
                                unlink(SIGNALS_TMP_PATH . 'admin_' . intval($stream['stream_id']));
                            }
                        }
                        if ($rQueue == 0 && $rAdminQueue == 0 && $stream['online_clients'] == 0 && (file_exists(STREAMS_PATH . $stream['stream_id'] . '_.m3u8') || intval(ipTV_lib::$settings['on_demand_wait_time']) < time() - intval($stream['stream_started']) || $stream['stream_status'] == 1)) {
                            echo 'Stop on-demand stream...' . "\n\n";
                            ipTV_stream::stopStream($stream['stream_id'], true);
                        }
                    }
                    // if ($stream['vframes_server_id'] == SERVER_ID || !ipTV_stream::isThumbnailRunning($stream['vframes_pid'], $stream['stream_id'])) {
                    //     echo 'Start Thumbnail...' . "\n";
                    //     ipTV_stream::startThumbnail($stream['stream_id']);
                    // }
                    if ($stream['tv_archive_server_id'] == SERVER_ID || !ipTV_streaming::isArchiveRunning($stream['tv_archive_pid'], $stream['stream_id'])) {
                        echo 'Start TV Archive...' . "\n";
                        shell_exec(PHP_BIN . ' ' . CLI_PATH . 'archive.php ' . intval($stream['stream_id']) . ' >/dev/null 2>/dev/null & echo $!');
                    }
                    foreach (glob(STREAMS_PATH . $stream['stream_id'] . '_*.ts.enc') as $File) {
                        if (!file_exists(rtrim($File, '.enc'))) {
                            unlink($File);
                        }
                    }
                    if (file_exists(STREAMS_PATH . $stream['stream_id'] . '_.pid')) {
                        $PID = intval(file_get_contents(STREAMS_PATH . $stream['stream_id'] . '_.pid'));
                    } else {
                        $PID = intval(shell_exec("ps aux | grep -v grep | grep '/" . intval($stream['stream_id']) . "_.m3u8' | awk '{print \$2}'"));
                    }
                    $activePIDs[] = intval($PID);
                    $Playlist = STREAMS_PATH . $stream['stream_id'] . '_.m3u8';
                    if (ipTV_streaming::isStreamRunning($PID, $stream['stream_id']) && file_exists($Playlist)) {
                        echo 'Update Stream Information...' . "\n";
                        $Bitrate = ipTV_streaming::getStreamBitrate('live', STREAMS_PATH . $stream['stream_id'] . '_.m3u8');
                        if (file_exists(STREAMS_PATH . $stream['stream_id'] . '_.progress')) {
                            $Progress = file_get_contents(STREAMS_PATH . $stream['stream_id'] . '_.progress');
                            unlink(STREAMS_PATH . $stream['stream_id'] . '_.progress');
                            if ($stream['fps_restart']) {
                                file_put_contents(STREAMS_PATH . $stream['stream_id'] . '_.progress_check', $Progress);
                            }
                        } else {
                            $Progress = $stream['progress_info'];
                        }
                        if (file_exists(STREAMS_PATH . $stream['stream_id'] . '_.stream_info')) {
                            $rStreamInfo = file_get_contents(STREAMS_PATH . $stream['stream_id'] . '_.stream_info');
                            unlink(STREAMS_PATH . $stream['stream_id'] . '_.stream_info');
                        } else {
                            $rStreamInfo = $stream['stream_info'];
                        }
                        $rCompatible = 0;
                        $rAudioCodec = $rVideoCodec = $rResolution = null;
                        if ($rStreamInfo) {
                            $rStreamJSON = json_decode($rStreamInfo, true);
                            $rCompatible = intval(ipTV_stream::checkCompatibility($rStreamJSON));
                            $rAudioCodec = ($rStreamJSON['codecs']['audio']['codec_name'] ?: null);
                            $rVideoCodec = ($rStreamJSON['codecs']['video']['codec_name'] ?: null);
                            $rResolution = ($rStreamJSON['codecs']['video']['height'] ?: null);
                            if ($rResolution) {
                                $rResolution = ipTV_stream::getNearest(array(240, 360, 480, 576, 720, 1080, 1440, 2160), $rResolution);
                            }
                        }
                        if ($stream['pid'] != $PID) {
                            $ipTV_db->query('UPDATE `streams_servers` SET `pid` = ?, `progress_info` = ?, `stream_info` = ?, `compatible` = ?, `bitrate` = ?, `audio_codec` = ?, `video_codec` = ?, `resolution` = ? WHERE `server_stream_id` = ?', $PID, $Progress, $rStreamInfo, $rCompatible, $Bitrate, $rAudioCodec, $rVideoCodec, $rResolution, $stream['server_stream_id']);
                        } else {
                            $ipTV_db->query('UPDATE `streams_servers` SET `progress_info` = ?, `stream_info` = ?, `compatible` = ?, `bitrate` = ?, `audio_codec` = ?, `video_codec` = ?, `resolution` = ? WHERE `server_stream_id` = ?', $Progress, $rStreamInfo, $rCompatible, $Bitrate, $rAudioCodec, $rVideoCodec, $rResolution, $stream['server_stream_id']);
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
        $ipTV_db->query('SELECT `streams`.`id` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` WHERE `streams`.`direct_source` = 1 AND `streams_servers`.`server_id` = ? AND `streams_servers`.`pid` > 0;', SERVER_ID);
        if ($ipTV_db->num_rows() > 0) {
            foreach ($ipTV_db->get_rows() as $stream) {
                if (file_exists(STREAMS_PATH . $stream['id'] . '.analyse')) {
                    $FFProbeOutput = ipTV_stream::probeStream(STREAMS_PATH . $stream['id'] . '.analyse');
                    if ($FFProbeOutput) {
                        $Bitrate = $FFProbeOutput['bitrate'] / 1024;
                        $rCompatible = intval(ipTV_stream::checkCompatibility($FFProbeOutput));
                        $rAudioCodec = ($FFProbeOutput['codecs']['audio']['codec_name'] ?: null);
                        $rVideoCodec = ($FFProbeOutput['codecs']['video']['codec_name'] ?: null);
                        $rResolution = ($FFProbeOutput['codecs']['video']['height'] ?: null);
                        if ($rResolution) {
                            $rResolution = ipTV_stream::getNearest(array(240, 360, 480, 576, 720, 1080, 1440, 2160), $rResolution);
                        }
                    }
                    echo 'Stream ID: ' . $stream['id'] . "\n";
                    echo 'Update Stream Information...' . "\n";
                    $ipTV_db->query('UPDATE `streams_servers` SET `bitrate` = ?, `stream_info` = ?, `audio_codec` = ?, `video_codec` = ?, `resolution` = ?, `compatible` = ? WHERE `stream_id` = ? AND `server_id` = ?', $Bitrate, json_encode($FFProbeOutput), $rAudioCodec, $rVideoCodec, $rResolution, $rCompatible, $stream['id'], SERVER_ID);
                }
                $rUUIDs = array();
                $rConnections = ipTV_streaming::getConnections(SERVER_ID, null, $stream['id']);
                foreach ($rConnections as $rUserID => $rItems) {
                    foreach ($rItems as $rItem) {
                        $rUUIDs[] = $rItem['uuid'];
                    }
                }
                if ($rHandle = opendir(CONS_TMP_PATH . $stream['id'] . '/')) {
                    while (false !== ($Filename = readdir($rHandle))) {
                        if ($Filename != '.' && $Filename != '..') {
                            if (!in_array($Filename, $rUUIDs)) {
                                unlink(CONS_TMP_PATH . $stream['id'] . '/' . $Filename);
                            }
                        }
                    }
                    closedir($rHandle);
                }
            }
        }
        $ipTV_db->query("SELECT `stream_id` FROM `streams_servers` WHERE `on_demand` = 1 AND `server_id` = ?;", SERVER_ID);
        $OnDemandIDs = array_keys($ipTV_db->get_rows(true, 'stream_id'));
        $Processes = shell_exec('ps aux | grep XC_VM');
        if (preg_match_all('/XC_VM\\[(.*)\\]/', $Processes, $Matches)) {
            $Remove = array_diff($Matches[1], $streamIDs);
            $Remove = array_diff($Remove, $OnDemandIDs);
            foreach ($Remove as $streamID) {
                if (is_numeric($streamID)) {
                    echo 'Kill Stream ID: ' . $streamID . "\n";
                    shell_exec("kill -9 `ps -ef | grep '/" . intval($streamID) . "_.m3u8\\|XC_VM\\[" . intval($streamID) . "\\]' | grep -v grep | awk '{print \$2}'`;");
                    shell_exec('rm -f ' . STREAMS_PATH . intval($streamID) . '_*');
                }
            }
        }
        if (ipTV_lib::$settings['kill_rogue_ffmpeg']) {
            exec("ps aux | grep -v grep | grep '/*_.m3u8' | awk '{print \$2}'", $rFFMPEG);
            foreach ($rFFMPEG as $PID) {
                $activePIDsMap = array_flip($activePIDs);
                if (is_numeric($PID) && intval($PID) > 0 && !isset($activePIDsMap[$PID])) {
                    echo 'Kill Roque PID: ' . $PID . "\n";
                    shell_exec('kill -9 ' . $PID . ';');
                }
            }
        }
    } else {
        echo 'XC_VM not running...' . "\n";
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
