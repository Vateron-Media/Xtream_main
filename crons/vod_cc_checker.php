<?php

if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XtreamCodes[VOD CC Checker]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::check_cron($unique_id);
        ini_set('memory_limit', -1);
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as XtreamCodes!' . "\n");
}

function loadCron() {
    global $ipTV_db;
    $ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `transcoding_profiles` t2 ON t2.profile_id = t1.transcode_profile_id WHERE t1.type = 3');
    if (0 < $ipTV_db->num_rows()) {
        $streams = $ipTV_db->get_rows();
        foreach ($streams as $stream) {
            echo "\n\n[*] Checking Stream " . $stream['stream_display_name'] . "\n";
            ipTV_stream::TranscodeBuild($stream['id']);
            switch (ipTV_stream::TranscodeBuild($stream['id'])) {
                case 1:
                    echo "\tBuild Is Still Going!\n";
                    break;
                case 2:
                    echo "\tBuild Finished\n";
                    break;
            }
        }
    }
    $pid = ipTV_servers::getPidFromProcessName(SERVER_ID, FFMPEG_PATH);
    $ipTV_db->query("SELECT t1.*,t2.* FROM `streams_servers` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.direct_source = 0 INNER JOIN `streams_types` t3 ON t3.type_id = t2.type AND t3.live = 0 WHERE (t1.to_analyze = 1 OR t1.stream_status = 2) AND t1.server_id = '%d'", SERVER_ID);
    if (0 < $ipTV_db->num_rows()) {
        $series_data = $ipTV_db->get_rows();
        foreach ($series_data as $data) {
            echo '[*] Checking Movie ' . $data['stream_display_name'] . ' ON Server ID ' . $data['server_id'] . " \t\t---> ";
            if ($data['to_analyze'] == 1) {
                if (!empty($pid[$data['server_id']]) && in_array($data['pid'], $pid[$data['server_id']])) {
                    echo "WORKING\n";
                } else {
                    echo "\n\n\n";
                    $target_container = json_decode($data['target_container'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['target_container'] = $target_container;
                    } else {
                        $data['target_container'] = array($data['target_container']);
                    }
                    $data['target_container'] = $data['target_container'][0];
                    $fileURL = VOD_PATH . $data['stream_id'] . '.' . $data['target_container'];
                    if ($stream_info = ipTV_stream::analyzeStream($fileURL, $data['server_id'])) {
                        $duration = isset($stream_info['duration']) ? $stream_info['duration'] : 0;
                        sscanf($duration, '%d:%d:%d', $hours, $minutes, $seconds);
                        $duration_secs = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
                        $resultCommand = ipTV_servers::RunCommandServer($data['server_id'], 'wc -c < ' . $fileURL, 'raw');
                        $bitrate = round($resultCommand[$data['server_id']] * 0.008 / $duration_secs);
                        $movie_properties = json_decode($data['movie_properties'], true);
                        if (!is_array($movie_properties)) {
                            $movie_properties = array();
                        }
                        if (!isset($movie_properties['duration_secs']) && $duration_secs != $movie_properties['duration_secs']) {
                            $movie_properties['duration_secs'] = $duration_secs;
                            $movie_properties['duration'] = $duration;
                        }
                        if (!isset($movie_properties['video']) && $stream_info['codecs']['video']['codec_name'] != $movie_properties['video']) {
                            $movie_properties['video'] = $stream_info['codecs']['video'];
                        }
                        if (!isset($movie_properties['audio']) && $stream_info['codecs']['audio']['codec_name'] != $movie_properties['audio']) {
                            $movie_properties['audio'] = $stream_info['codecs']['audio'];
                        }
                        if (!isset($movie_properties['bitrate']) && $bitrate != $movie_properties['bitrate']) {
                            $movie_properties['bitrate'] = $bitrate;
                        }
                        $ipTV_db->query("UPDATE `streams` SET `movie_properties` = '%s' WHERE `id` = '%d'", json_encode($movie_properties), $data["stream_id"]);
                        $ipTV_db->query("UPDATE `streams_servers` SET `bitrate` = '%d',`to_analyze` = 0,`stream_status` = 0,`stream_info` = '%s'  WHERE `server_stream_id` = '%d'", $bitrate, json_encode($stream_info), $data["server_stream_id"]);
                        echo "VALID\n";
                    } else {
                        $ipTV_db->query("UPDATE `streams_servers` SET `to_analyze` = 0,`stream_status` = 1  WHERE `server_stream_id` = '%d'", $data["server_stream_id"]);
                        echo "BAD MOVIE\n";
                    }
                }
            } else {
                echo "NO ACTION\n";
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
