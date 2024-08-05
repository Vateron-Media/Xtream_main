<?php

function StopMonitorStream($stream_id) {
    clearstatcache(true);
    if (file_exists('/home/xtreamcodes/streams/' . $stream_id . '.monitor')) {
        $pid = intval(file_get_contents('/home/xtreamcodes/streams/' . $stream_id . '.monitor'));
    }
    if (!empty($pid)) {
        if (file_exists('/proc/' . $pid)) {
            $name = trim(file_get_contents('/proc/' . $pid . '/cmdline'));
            if ($name == 'XtreamCodes[' . $stream_id . ']') {
                posix_kill($pid, 9);
            }
        } else {
            shell_exec('kill -SIGKILL `ps -ef | grep \'XtreamCodes\\[' . $stream_id . '\\]\' | grep -v grep | awk \'{print $2}\'`;');
        }
    }
    file_put_contents('/home/xtreamcodes/streams/' . $stream_id . '.monitor', getmypid());
}
if (!@$argc) {
    die(0);
}
if ($argc >= 1) {
    define('FETCH_BOUQUETS', false);
    $stream_id = intval($argv[1]);
    $stream_delay = empty($argv[2]) ? false : true;
    StopMonitorStream($stream_id);
    cli_set_process_title('XtreamCodes[' . $stream_id . ']');
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    set_time_limit(0);
    $ipTV_db->query("SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t2.stream_id = t1.id AND t2.server_id = '%d' WHERE t1.id = '%d'", SERVER_ID, $stream_id);
    if ($ipTV_db->num_rows() > 0) {
        $stream = $ipTV_db->get_row();
        $ipTV_db->query("UPDATE `streams_servers` SET `monitor_pid` = '%d' WHERE `server_stream_id` = '%d'", getmypid(), $stream["server_stream_id"]);

        $stream_pid = file_exists(STREAMS_PATH . $stream_id . "_.pid") ? intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid")) : $stream["pid"];
        $stream_auto_restart = json_decode($stream["auto_restart"], true);
        $stream_m3u8_file_path = STREAMS_PATH . $stream_id . "_.m3u8";
        $stream_delay_pid = $stream["delay_pid"];
        $stream_parent_id = $stream["parent_id"];
        $streamSources = [];
        if ($stream_parent_id == 0) {
            $streamSources = json_decode($stream["stream_source"], true);
        }
        $stream_curent_source = $stream["current_source"];
        $streamUrl = NULL;
        $ipTV_db->query("SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = '%d' AND t1.argument_id = t2.id", $stream_id);
        $Ec54d2818a814ae4c359a5fc4ffff2ee = $ipTV_db->get_rows();
        if (0 < $stream["delay_minutes"] && $stream["parent_id"] == 0) {
            $stream_path = DELAY_PATH;
            $stream_m3u8_file_path = DELAY_PATH . $stream_id . "_.m3u8";
            $isDelayEnabled = true;
        } else {
            $isDelayEnabled = false;
            $stream_path = STREAMS_PATH;
        }
        $streamStatusCounter = 0;
        if (ipTV_streaming::isStreamRunning($stream_pid, $stream_id)) {
            if ($stream_delay) {
                $streamStatusCounter = RESTART_TAKE_CACHE + 1;
                shell_exec("kill -9 " . $stream_pid);
                shell_exec("rm -f " . STREAMS_PATH . $stream_id . "_*");
                if ($isDelayEnabled && ipTV_streaming::isDelayRunning($stream_delay_pid, $stream_id)) {
                    shell_exec("kill -9 " . $stream_delay_pid);
                }
                usleep(50000);
                $stream_delay_pid = $stream_pid = 0;
            }
        }
        while (!false) {
            if (0 < $stream_pid) {
                $ipTV_db->close_mysql();
                $audio_restart_loss = $seg_time = $priority_backup = time();
                $stream_out_file_path = md5_file($stream_m3u8_file_path);
                while (ipTV_streaming::isStreamRunning($stream_pid, $stream_id) && file_exists($stream_m3u8_file_path)) {
                    if (!empty($stream_auto_restart["days"]) && !empty($stream_auto_restart["at"])) {
                        list($Ed62709841469f20fe0f7a17a4268692, $Bc1d36e0762a7ca0e7cbaddd76686790) = explode(":", $stream_auto_restart["at"]);
                        if (in_array(date("l"), $stream_auto_restart["days"]) && date("H") == $Ed62709841469f20fe0f7a17a4268692) {
                            if ($Bc1d36e0762a7ca0e7cbaddd76686790 == date("i")) {
                                break;
                            }
                        }
                    }
                    if (ipTV_lib::$settings["audio_restart_loss"] == 1 && 300 < time() - $audio_restart_loss) {
                        list($fe9d0d199fc51f64065055d8bcade279) = ipTV_streaming::GetSegmentsOfPlaylist($stream_m3u8_file_path, 10);
                        if (!empty($fe9d0d199fc51f64065055d8bcade279)) {
                            $E40539dbfb9861abbd877a2ee47b9e65 = ipTV_stream::analyzeStream($stream_path . $fe9d0d199fc51f64065055d8bcade279, SERVER_ID);
                            if (!(isset($E40539dbfb9861abbd877a2ee47b9e65["codecs"]["audio"]) && !empty($E40539dbfb9861abbd877a2ee47b9e65["codecs"]["audio"]))) {
                                break;
                            }
                            $audio_restart_loss = time();
                        } else {
                            break;
                        }
                    }
                    if (ipTV_lib::$SegmentsSettings["seg_time"] * 6 <= time() - $seg_time) {
                        $new_stream_out_file_path = md5_file($stream_m3u8_file_path);
                        if ($stream_out_file_path != $new_stream_out_file_path) {
                            $stream_out_file_path = $new_stream_out_file_path;
                            $seg_time = time();
                        } else {
                            break;
                        }
                    }
                    if (ipTV_lib::$settings["priority_backup"] == 1 && 1 < count($streamSources) && $stream_parent_id == 0 && 10 < time() - $priority_backup) {
                        $priority_backup = time();
                        $Baee0c34e5755f1cfaa4159ea7e8702e = array_search($stream_curent_source, $streamSources);
                        if (0 < $Baee0c34e5755f1cfaa4159ea7e8702e) {
                            foreach ($streamSources as $source) {
                                $parsedURL = ipTV_stream::parseStreamURL($source);
                                if ($parsedURL == $stream_curent_source) {
                                    break;
                                }
                                $protocol = strtolower(substr($parsedURL, 0, strpos($parsedURL, '://')));
                                $formattedArgs = implode(" ", ipTV_stream::getFormattedStreamArguments($Ec54d2818a814ae4c359a5fc4ffff2ee, $protocol, "fetch"));
                                if ($streamData = ipTV_stream::analyzeStream($parsedURL, SERVER_ID, $formattedArgs)) {
                                    $streamUrl = $parsedURL;
                                    break;
                                }
                            }
                        }
                    }
                    if ($isDelayEnabled && $stream["delay_available_at"] <= time() && !ipTV_streaming::isDelayRunning($stream_delay_pid, $stream_id)) {
                        $stream_delay_pid = intval(shell_exec(PHP_BIN . " " . TOOLS_PATH . "delay.php " . $stream_id . " " . $stream["delay_minutes"] . " >/dev/null 2>/dev/null & echo \$!"));
                    }
                    sleep(1);
                }
                $ipTV_db->db_connect();
            }
            if (ipTV_streaming::isStreamRunning($stream_pid, $stream_id)) {
                shell_exec("kill -9 " . $stream_pid);
                usleep(50000);
            }
            if (ipTV_streaming::isDelayRunning($stream_delay_pid, $stream_id)) {
                shell_exec("kill -9 " . $stream_delay_pid);
                usleep(50000);
            }
            while (!ipTV_streaming::isStreamRunning($stream_pid, $stream_id)) {
                echo "Restarting...\n";
                shell_exec("rm -f " . STREAMS_PATH . $stream_id . "_*");
                $d76067cf9572f7a6691c85c12faf2a29 = ipTV_stream::startStream($stream_id, $streamStatusCounter, $streamUrl);
                if ($d76067cf9572f7a6691c85c12faf2a29 === false) {
                    die;
                }
                if (is_numeric($d76067cf9572f7a6691c85c12faf2a29) && $d76067cf9572f7a6691c85c12faf2a29 == 0) {
                    sleep(mt_rand(10, 25));
                    continue;
                }
                sleep(mt_rand(5, 10));
                $stream_pid = $d76067cf9572f7a6691c85c12faf2a29["main_pid"];
                $stream_m3u8_file_path = $d76067cf9572f7a6691c85c12faf2a29["playlist"];
                $isDelayEnabled = $d76067cf9572f7a6691c85c12faf2a29["delay_enabled"];
                $stream["delay_available_at"] = $d76067cf9572f7a6691c85c12faf2a29["delay_start_at"];
                $stream_curent_source = $d76067cf9572f7a6691c85c12faf2a29["stream_source"];
                $stream_parent_id = $d76067cf9572f7a6691c85c12faf2a29["parent_id"];
                $streamUrl = NULL;
                if ($isDelayEnabled) {
                    $stream_path = DELAY_PATH;
                } else {
                    $stream_path = STREAMS_PATH;
                }
                $retryCount = 0;
                while (ipTV_streaming::isStreamRunning($stream_pid, $stream_id) && !file_exists($stream_m3u8_file_path) && $retryCount <= ipTV_lib::$SegmentsSettings["seg_time"] * 3) {
                    echo "Checking For PlayList...\n";
                    sleep(1);
                    ++$retryCount;
                }
                if ($retryCount == ipTV_lib::$SegmentsSettings["seg_time"] * 3) {
                    shell_exec("kill -9 " . $stream_pid);
                    usleep(50000);
                }
                if (RESTART_TAKE_CACHE < $streamStatusCounter) {
                    $streamStatusCounter = 0;
                }
            }
        }
    } else {
        ipTV_stream::stopStream($stream_id);
        die;
    }
} else {
    echo "[*] Correct Usage: php " . __FILE__ . " <stream_id> [restart]\n";
    exit;
}
