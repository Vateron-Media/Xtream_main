<?php

header("Access-Control-Allow-Origin: *");
register_shutdown_function("shutdown");
set_time_limit(0);
require "../init.php";
$user_ip = $_SERVER["REMOTE_ADDR"];
if (!in_array($user_ip, ipTV_streaming::getAllowedIPsAdmin(true))) {
    http_response_code(401);
    die;
}
if (empty(ipTV_lib::$request["stream"]) || empty(ipTV_lib::$request["extension"]) || empty(ipTV_lib::$request["password"]) || ipTV_lib::$settings["live_streaming_pass"] != ipTV_lib::$request["password"]) {
    http_response_code(401);
    die;
}
$password = ipTV_lib::$settings["live_streaming_pass"];
$stream_id = intval(ipTV_lib::$request["stream"]);
$extension = ipTV_lib::$request["extension"];
$ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t2.stream_id = t1.id AND t2.server_id = \'%d\' WHERE t1.`id` = \'%d\'', SERVER_ID, $stream_id);
if (ipTV_lib::$settings["use_buffer"] == 0) {
    header("X-Accel-Buffering: no");
}
if ($ipTV_db->num_rows() > 0) {
    $channel_info = $ipTV_db->get_row();
    $ipTV_db->close_mysql();
    $playlist = STREAMS_PATH . $stream_id . "_.m3u8";
    if (!ipTV_streaming::isStreamRunning($channel_info["pid"], $stream_id)) {
        if ($channel_info["on_demand"] == 1) {
            if (!ipTV_streaming::CheckMonitorRunning($channel_info["monitor_pid"], $stream_id)) {
                ipTV_stream::startMonitor($stream_id);
            }
        } else {
            http_response_code(403);
            die;
        }
    }
    switch ($extension) {
        case "m3u8":
            if (ipTV_streaming::IsValidStream($playlist, $channel_info["pid"])) {
                if (empty(ipTV_lib::$request["segment"])) {
                    if ($source = ipTV_streaming::GeneratePlayListWithAuthenticationAdmin($playlist, $password, $stream_id)) {
                        header("Content-Type: application/vnd.apple.mpegurl");
                        header("Content-Length: " . strlen($source));
                        ob_end_flush();
                        echo $source;
                    }
                } else {
                    $segment = STREAMS_PATH . str_replace(array("\\", "/"), '', urldecode(ipTV_lib::$request["segment"]));
                    if (file_exists($segment)) {
                        $size = filesize($segment);
                        header("Content-Length: " . $size);
                        header("Content-Type: video/mp2t");
                        readfile($segment);
                    }
                }
            }
            break;
        default:
            header("Content-Type: video/mp2t");
            $segmentsOfPlaylist = ipTV_streaming::GetSegmentsOfPlaylist($playlist, ipTV_lib::$settings["client_prebuffer"]);
            if (empty($segmentsOfPlaylist)) {
                if (!file_exists($playlist)) {
                    $current = -1;
                } else {
                    die;
                    if (is_array($segmentsOfPlaylist)) {
                        foreach ($segmentsOfPlaylist as $segment) {
                            readfile(STREAMS_PATH . $segment);
                        }
                        preg_match("/_(.*)\\./", array_pop($segmentsOfPlaylist), $pregmatches);
                        $current = $pregmatches[1];
                    } else {
                        $current = $segmentsOfPlaylist;
                    }
                }
                $fails = 0;
                $total_failed_tries = ipTV_lib::$SegmentsSettings["seg_time"] * 2;
                while (true) {
                    $segment_file = sprintf("%d_%d.ts", $stream_id, $current + 1);
                    $nextsegment_file = sprintf("%d_%d.ts", $stream_id, $current + 2);
                    $totalItems = 0;
                    while (!file_exists(STREAMS_PATH . $segment_file) && $totalItems <= $total_failed_tries * 10) {
                        usleep(100000);
                        ++$totalItems;
                    }
                    if (!file_exists(STREAMS_PATH . $segment_file)) {
                        die;
                    }
                    if (empty($channel_info["pid"]) && file_exists(STREAMS_PATH . $stream_id . "_.pid")) {
                        $channel_info["pid"] = intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid"));
                    }
                    $fails = 0;
                    $fp = fopen(STREAMS_PATH . $segment_file, "r");
                    while ($fails <= $total_failed_tries && !file_exists(STREAMS_PATH . $nextsegment_file)) {
                        $data = stream_get_line($fp, ipTV_lib::$settings["read_buffer_size"]);
                        if (empty($data)) {
                            sleep(1);
                            if (!is_resource($fp) || !file_exists(STREAMS_PATH . $segment_file)) {
                                die;
                            }
                            ++$fails;
                            continue;
                        }
                        echo $data;
                        $fails = 0;
                    }
                    if (ipTV_streaming::isProcessRunning($channel_info["pid"], ipTV_lib::$FFMPEG_CPU) && $fails <= $total_failed_tries && file_exists(STREAMS_PATH . $segment_file) && is_resource($fp)) {
                        $size = filesize(STREAMS_PATH . $segment_file);
                        $line = $size - ftell($fp);
                        if ($line > 0) {
                            echo stream_get_line($fp, $line);
                        }
                    } else {
                        die;
                    }
                    fclose($fp);
                    $fails = 0;
                    $current++;
                }
            }
    }
} else {
    http_response_code(403);
}
function shutdown() {
    fastcgi_finish_request();
}
