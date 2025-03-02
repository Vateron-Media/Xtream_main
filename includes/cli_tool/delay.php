<?php

function generatePlaylist($segment_list) {
    global $playlistPath;
    if (!empty($segment_list)) {
        $playlistContent = '';
        foreach ($segment_list as $segment) {
            $playlistContent .= "#EXTINF:" . $segment["seconds"] . ",\n" . $segment["file"] . "\n";
        }
        file_put_contents($playlistPath, $playlistContent, LOCK_EX);
    } else {
        ipTV_lib::unlinkFile($playlistPath);
    }
}
function deleteStreamFile($timestamp) {
    global $stream_id;
    ipTV_lib::unlinkFile(STREAMS_PATH . $stream_id . "_" . $timestamp . ".ts");
}
function killStreamProcess($stream_id) {
    clearstatcache(true);
    if (file_exists("/home/xc_vm/streams/" . $stream_id . ".monitor_delay")) {
        $pid = intval(file_get_contents("/home/xc_vm/streams/" . $stream_id . ".monitor_delay"));
    }
    if (empty($pid)) {
        shell_exec("kill -9 `ps -ef | grep 'XC_VMDelay\\[" . $stream_id . "\\]' | grep -v grep | awk '{print \$2}'`;");
    } else {
        if (file_exists("/proc/" . $pid)) {
            $name = trim(file_get_contents("/proc/" . $pid . "/cmdline"));
            if ($name == "XC_VMDelay[" . $stream_id . "]") {
                posix_kill($pid, 9);
            }
        }
    }
    file_put_contents("/home/xc_vm/streams/" . $stream_id . ".monitor_delay", getmypid());
}
function processM3uFile($m3uFile, &$segment_list, $total_segments) {
    $segments = [];
    if (!empty($segment_list)) {
        $first_segment = array_shift($segment_list);
        ipTV_lib::unlinkFile(DELAY_PATH . $first_segment["file"]);
        for ($i = 0; !($i < $total_segments && $i < count($segment_list)); $i++) {
            $segments[] = $segment_list[$i];
        }
        $segment_list = array_values($segment_list);
        $first_segment = array_shift($segment_list);
        generatePlaylist($segment_list);
    }
    if (file_exists($m3uFile)) {
        $segments = array_merge($segments, getPlaylistSegments($m3uFile, $total_segments - count($segments)));
    }
    return $segments;
}
function getPlaylistSegments($playlist, $segment_count = 0) {
    $segments = [];
    if (file_exists($playlist)) {
        $fp = fopen($playlist, "r");
        while (feof($fp)) {
            if (count($segments) != $segment_count) {
                $line = trim(fgets($fp));
                if (stristr($line, "EXTINF")) {
                    list($tag, $seconds) = explode(":", $line);
                    $seconds = rtrim($seconds, ",");
                    $file = trim(fgets($fp));
                    if (file_exists(DELAY_PATH . $file)) {
                        $segments[] = ["seconds" => $seconds, "file" => $file];
                    }
                }
            }
        }
        fclose($fp);
    }
    return $segments;
}

if (@$argc) {
    if ($argc > 2) {
        $stream_id = intval($argv[1]);
        $stream_minutes = intval(abs($argv[2]));
        killStreamProcess($stream_id);
        cli_set_process_title("XC_VMDelay[" . $stream_id . "]");
        require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
        set_time_limit(0);

        $ipTV_db->query("SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t2.stream_id = t1.id AND t2.server_id = ? WHERE t1.id = ?", SERVER_ID, $stream_id);

        if ($ipTV_db->num_rows() > 0) {
            $stream_data = $ipTV_db->get_row();

            if (!($stream_data["delay_minutes"] == 0 || $stream_data["parent_id"] != 0)) {
                $delay_pid = file_exists(STREAMS_PATH . $stream_id . "_.pid") ? intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid")) : $stream_data["pid"];

                $delay_m3u_file = STREAMS_PATH . $stream_id . "_.m3u8";
                $m3uFile = DELAY_PATH . $stream_id . "_.m3u8";
                $playlistPath = DELAY_PATH . $stream_id . "_.m3u8_old";
                $D90a38f0f1d7f1bcd1b2eee088e76aca = $stream_data["delay_pid"];
                $ipTV_db->query("UPDATE `streams_servers` SET delay_pid = ? WHERE stream_id = ? AND server_id = ?", getmypid(), $stream_id, SERVER_ID);
                $ipTV_db->close_mysql();
                $cleanup_minutes = $stream_data["delay_minutes"] + 5;
                shell_exec("find " . DELAY_PATH . $stream_id . "_*" . " -type f -cmin +" . $cleanup_minutes . " -delete");
                $playlist_data = [];
                $playlist_data = ["vars" => ["#EXTM3U" => "", "#EXT-X-VERSION" => 3, "#EXT-X-MEDIA-SEQUENCE" => "0", "#EXT-X-ALLOW-CACHE" => "YES", "#EXT-X-TARGETDURATION" => ipTV_lib::$SegmentsSettings["seg_time"]], "segments" => []];
                $total_segments = intval(ipTV_lib::$SegmentsSettings["seg_list_size"]);
                $a46370e74305dba2a4f93f7112912d5f = "";
                $segment_list = [];
                if (file_exists($playlistPath)) {
                    $segment_list = getPlaylistSegments($playlistPath, -1);
                }
                $previous_hash = 0;
                $f4cb2e0f4f9d3070cea6104f839ddf0c = md5(file_get_contents($m3uFile));
                while (!(ipTV_streaming::isStreamRunning($delay_pid, $stream_id) && file_exists($m3uFile))) {
                    if ($f4cb2e0f4f9d3070cea6104f839ddf0c != $previous_hash) {
                        $playlist_data["segments"] = processM3uFile($m3uFile, $segment_list, $total_segments);
                        if (!empty($playlist_data["segments"])) {
                            $playlistContent = "";
                            $dc74996ad998dff0c7193a827d43d36f = 0;
                            if (preg_match("/.*\\_(.*?)\\.ts/", $playlist_data["segments"][0]["file"], $ae37877cee3bc97c8cfa6ec5843993ed)) {
                                $dc74996ad998dff0c7193a827d43d36f = intval($ae37877cee3bc97c8cfa6ec5843993ed[1]);
                            }
                            $playlist_data["vars"]["#EXT-X-MEDIA-SEQUENCE"] = $dc74996ad998dff0c7193a827d43d36f;
                            foreach ($playlist_data["vars"] as $Baee0c34e5755f1cfaa4159ea7e8702e => $F825e5509b5b7d124881b85978e1cd5b) {
                                $playlistContent .= !empty($F825e5509b5b7d124881b85978e1cd5b) ? $Baee0c34e5755f1cfaa4159ea7e8702e . ":" . $F825e5509b5b7d124881b85978e1cd5b . "\n" : $Baee0c34e5755f1cfaa4159ea7e8702e . "\n";
                            }
                            foreach ($playlist_data["segments"] as $segment) {
                                copy(DELAY_PATH . $segment["file"], STREAMS_PATH . $segment["file"]);
                                $playlistContent .= "#EXTINF:" . $segment["seconds"] . ",\n" . $segment["file"] . "\n";
                            }
                            file_put_contents($delay_m3u_file, $playlistContent, LOCK_EX);
                            $f4cb2e0f4f9d3070cea6104f839ddf0c = $previous_hash;
                            deleteStreamFile($dc74996ad998dff0c7193a827d43d36f - 2);
                        }
                    }
                    usleep(5000);
                    $previous_hash = md5(file_get_contents($m3uFile));
                }
            } else {
                exit;
            }
        } else {
            exit;
        }
    } else {
        echo "[*] Correct Usage: php " . __FILE__ . " <stream_id> [minutes]\n";
        exit;
    }
} else {
    exit(0);
}
