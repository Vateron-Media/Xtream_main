<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XC_VM[VOD]');
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
    $ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t3 ON t3.stream_id = t1.id LEFT JOIN `transcoding_profiles` t2 ON t2.profile_id = t1.transcode_profile_id WHERE t1.type = 3 AND t3.server_id = ? AND t3.parent_id IS NULL;', SERVER_ID);
    if ($ipTV_db->num_rows() > 0) {
        $streams = $ipTV_db->get_rows();
        foreach ($streams as $stream) {
            echo "\n\n" . '[*] Checking Stream ' . $stream['stream_display_name'] . "\n";
            $PID = intval(file_get_contents(CREATED_PATH . $stream['id'] . '_.create'));
            if ($PID && ipTV_servers::isPIDRunning(SERVER_ID, $PID, PHP_BIN)) {
                echo "\t" . 'Build Is Still Going!' . "\n";
            } else {
                $sourcesLeft = array_diff(json_decode($stream['stream_source'], true), json_decode($stream['cchannel_rsources'], true));
                if (0 < count($sourcesLeft)) {
                    echo "\t" . 'Needs Updating!' . "\n";
                    ipTV_stream::queueChannel($stream['id']);
                } else {
                    if (file_exists(CREATED_PATH . $stream['id'] . '_.info')) {
                        $rCCInfo = file_get_contents(CREATED_PATH . $stream['id'] . '_.info');
                        $ipTV_db->query('UPDATE `streams_servers` SET `cc_info` = ? WHERE `server_id` = ? AND `stream_id` = ?;', $rCCInfo, SERVER_ID, $stream['id']);
                        unlink(CREATED_PATH . $stream['id'] . '_.info');
                    }
                    echo "\t" . 'Build Finished' . "\n";
                }
            }
        }
    }
    // $ipTV_db->query('SELECT `id` FROM `recordings` WHERE `status` NOT IN (1,2) AND `source_id` = ? AND ((`start` <= UNIX_TIMESTAMP() AND `end` > UNIX_TIMESTAMP()) OR (`archive` = 1));', SERVER_ID);
    // if ($ipTV_db->num_rows() > 0) {
    //     foreach ($ipTV_db->get_rows() as $row) {
    //         shell_exec(PHP_BIN . ' ' . INCLUDES_PATH . 'cli/record.php ' . intval($row['id']) . ' > /dev/null 2>/dev/null &');
    //     }
    // }
    exec("ps ax | grep 'ffmpeg' | awk '{print \$1}'", $PIDs);
    $ipTV_db->query('SELECT COUNT(*) AS `count` FROM `streams_servers` WHERE `to_analyze` = 1 AND `server_id` = ?', SERVER_ID);
    $rCount = $ipTV_db->get_row()['count'];
    if ($rCount > 0) {
        $steps = range(0, $rCount, 1000);
        if (!$steps) {
            $steps = array(0);
        }
        foreach ($steps as $step) {
            $ipTV_db->query('SELECT t1.*,t2.* FROM `streams_servers` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.direct_source = 0 INNER JOIN `streams_types` t3 ON t3.type_id = t2.type AND t3.live = 0 WHERE t1.to_analyze = 1 AND t1.server_id = ? LIMIT ' . $step . ', 1000', SERVER_ID);
            if ($ipTV_db->num_rows() > 0) {
                $rows = $ipTV_db->get_rows();
                foreach ($rows as $row) {
                    echo '[*] Checking Movie ' . $row['stream_display_name'] . ' ' . "\t\t" . '---> ';
                    if (in_array($row['pid'], $PIDs)) {
                        echo 'ENCODING...' . "\n";
                    } else {
                        $moviePath = VOD_PATH . intval($row['stream_id']) . '.' . escapeshellcmd($row['target_container']);
                        if ($FFProbee = ipTV_stream::probeStream($moviePath)) {
                            $rDuration = (isset($FFProbee['duration']) ? $FFProbee['duration'] : 0);
                            sscanf($rDuration, '%d:%d:%d', $rHours, $minutes, $seconds);
                            $seconds = (isset($seconds) ? $rHours * 3600 + $minutes * 60 + $seconds : $rHours * 60 + $minutes);
                            $size = filesize($moviePath);
                            $rBitrate = round(($size * 0.008) / $seconds);
                            $movieProperties = json_decode($row['movie_properties'], true);
                            if (!is_array($movieProperties)) {
                                $movieProperties = array();
                            }
                            if (!isset($movieProperties['duration_secs']) && $seconds != $movieProperties['duration_secs']) {
                                $movieProperties['duration_secs'] = $seconds;
                                $movieProperties['duration'] = $rDuration;
                            }
                            if (!isset($movieProperties['video']) && $FFProbee['codecs']['video']['codec_name'] != $movieProperties['video']) {
                                $movieProperties['video'] = $FFProbee['codecs']['video'];
                            }
                            if (!isset($movieProperties['audio']) && $FFProbee['codecs']['audio']['codec_name'] != $movieProperties['audio']) {
                                $movieProperties['audio'] = $FFProbee['codecs']['audio'];
                            }
                            // if (ipTV_lib::$settings['extract_subtitles']) {
                            //     if (!isset($movieProperties['subtitle']) && $FFProbee['codecs']['subtitle']['codec_name'] != $movieProperties['subtitle']) {
                            //         $movieProperties['subtitle'] = $FFProbee['codecs']['subtitle'];
                            //     }
                            // }
                            if (!isset($movieProperties['bitrate']) && $rBitrate != $movieProperties['bitrate']) {
                                if (0 < $rBitrate) {
                                    $movieProperties['bitrate'] = $rBitrate;
                                } else {
                                    $rBitrate = $movieProperties['bitrate'];
                                }
                            }
                            // if (isset($FFProbee['codecs']['subtitle']) && ipTV_lib::$settings['extract_subtitles']) {
                            //     $i = 0;
                            //     foreach ($FFProbee['codecs']['subtitle'] as $subtitle) {
                            //         ipTV_stream::extractSubtitle($row['stream_id'], $moviePath, $i);
                            //         $i++;
                            //     }
                            // }
                            $rCompatible = 0;
                            $rAudioCodec = $rVideoCodec = $resolution = null;
                            if ($FFProbee) {
                                $rCompatible = intval(ipTV_stream::checkCompatibility($FFProbee));
                                $rAudioCodec = ($FFProbee['codecs']['audio']['codec_name'] ?: null);
                                $rVideoCodec = ($FFProbee['codecs']['video']['codec_name'] ?: null);
                                $resolution = ($FFProbee['codecs']['video']['height'] ?: null);
                                if ($resolution) {
                                    $resolution = ipTV_stream::getNearest(array(240, 360, 480, 576, 720, 1080, 1440, 2160), $resolution);
                                }
                            }
                            $ipTV_db->query('UPDATE `streams` SET `movie_properties` = ? WHERE `id` = ?', json_encode($movieProperties, JSON_UNESCAPED_UNICODE), $row['stream_id']);
                            $ipTV_db->query('UPDATE `streams_servers` SET `bitrate` = ?,`to_analyze` = 0,`stream_status` = 0,`stream_info` = ?,`audio_codec` = ?,`video_codec` = ?,`resolution` = ?,`compatible` = ? WHERE `server_stream_id` = ?', $rBitrate, json_encode($FFProbee, JSON_UNESCAPED_UNICODE), $rAudioCodec, $rVideoCodec, $resolution, $rCompatible, $row['server_stream_id']);
                            echo 'VALID' . "\n";
                        } else {
                            $ipTV_db->query('UPDATE `streams_servers` SET `to_analyze` = 0,`stream_status` = 1 WHERE `server_stream_id` = ?', $row['server_stream_id']);
                            echo 'BROKEN' . "\n";
                        }
                        ipTV_streaming::updateStream($row['stream_id']);
                    }
                }
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
