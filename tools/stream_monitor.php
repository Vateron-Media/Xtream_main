<?php
function StopMonitorStream($stream_id) {
    clearstatcache(true);
    if (file_exists('/home/xtreamcodes/iptv_xtream_codes/streams/' . $stream_id . '.monitor')) {
        $pid = intval(file_get_contents('/home/xtreamcodes/iptv_xtream_codes/streams/' . $stream_id . '.monitor'));
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
        file_put_contents('/home/xtreamcodes/iptv_xtream_codes/streams/' . $stream_id . '.monitor', getmypid());
    }
}

if (@$argc) {
    if ($argc > 1) {
        define('FETCH_BOUQUETS', false);
        $stream_id = intval($argv[1]);
        $stream_delay = empty($argv[2]) ? false : true;
        StopMonitorStream($stream_id);
        cli_set_process_title('XtreamCodes[' . $stream_id . ']');
        require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
        set_time_limit(0);
        $ipTV_db->query("SELECT * FROM `streams` t1 INNER JOIN `streams_sys` t2 ON t2.stream_id = t1.id AND t2.server_id = '%d' WHERE t1.id = '%d'", SERVER_ID, $stream_id);
        if ($ipTV_db->num_rows() > 0) {
            $stream = $ipTV_db->get_row();
            $ipTV_db->query("UPDATE `streams_sys` SET `monitor_pid` = '%d' WHERE `server_stream_id` = '%d'", getmypid(), $stream["server_stream_id"]);
            $stream_pid = file_exists(STREAMS_PATH . $stream_id . "_.pid") ? intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid")) : $stream["pid"];
            $stream_auto_restart = json_decode($stream["auto_restart"], true);
            $stream_m3u8_file_path = STREAMS_PATH . $stream_id . "_.m3u8";
            $stream_delay_pid = $stream["delay_pid"];
            $stream_parent_id = $stream["parent_id"];
            $A733a5416ffab6ff47547550f3f9f641 = [];
            if ($stream_parent_id == 0) {
                $A733a5416ffab6ff47547550f3f9f641 = json_decode($stream["stream_source"], true);
            }
            $stream_curent_source = $stream["current_source"];
            $B71703fbd9f237149967f9ac3c41dc19 = NULL;
            $ipTV_db->query("SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = '%d' AND t1.argument_id = t2.id", $stream_id);
            $Ec54d2818a814ae4c359a5fc4ffff2ee = $ipTV_db->get_rows();
            if (0 < $stream["delay_minutes"] && $stream["parent_id"] == 0) {
                $stream_path = DELAY_STREAM;
                $stream_m3u8_file_path = DELAY_STREAM . $stream_id . "_.m3u8";
                $Cb60ed5772c86d5ca16425608a588951 = true;
            } else {
                $Cb60ed5772c86d5ca16425608a588951 = false;
                $stream_path = STREAMS_PATH;
            }
            $ecae69bb74394743482337ade627630b = 0;
            if (ipTV_streaming::CheckPidChannelM3U8Exist($stream_pid, $stream_id)) {
                if ($stream_delay) {
                    $ecae69bb74394743482337ade627630b = RESTART_TAKE_CACHE + 1;
                    shell_exec("kill -9 " . $stream_pid);
                    shell_exec("rm -f " . STREAMS_PATH . $stream_id . "_*");
                    if ($Cb60ed5772c86d5ca16425608a588951 && ipTV_streaming::CheckPidStreamExist($stream_delay_pid, $stream_id)) {
                        shell_exec("kill -9 " . $stream_delay_pid);
                    }
                    usleep(50000);
                    $stream_delay_pid = $stream_pid = 0;
                }
            }
            if (0 < $stream_pid) {
                $ipTV_db->close_mysql();
                $audio_restart_loss = $seg_time = $priority_backup = time();
                $stream_out_file_path = md5_file($stream_m3u8_file_path);
                while (!(ipTV_streaming::CheckPidChannelM3U8Exist($stream_pid, $stream_id) && file_exists($stream_m3u8_file_path))) {
                    if (!(empty($stream_auto_restart["days"]) || empty($stream_auto_restart["at"]))) {
                        print_r($Ed62709841469f20fe0f7a17a4268692);
                        list($Ed62709841469f20fe0f7a17a4268692, $Bc1d36e0762a7ca0e7cbaddd76686790) = explode(":", $stream_auto_restart["at"]);
                        if (in_array(date("l"), $stream_auto_restart["days"]) && date("H") == $Ed62709841469f20fe0f7a17a4268692) {
                            if ($Bc1d36e0762a7ca0e7cbaddd76686790 != date("i")) {
                            }
                        }
                    }
                    if (ipTV_lib::$settings["audio_restart_loss"] == 1 && 300 < time() - $audio_restart_loss) {
                        list($fe9d0d199fc51f64065055d8bcade279) = ipTV_streaming::GetSegmentsOfPlaylist($stream_m3u8_file_path, 10);
                        if (!empty($fe9d0d199fc51f64065055d8bcade279)) {
                            $E40539dbfb9861abbd877a2ee47b9e65 = ipTV_stream::e0a1164567005185E0818F081674e240($stream_path . $fe9d0d199fc51f64065055d8bcade279, SERVER_ID);
                            if (isset($E40539dbfb9861abbd877a2ee47b9e65["codecs"]["audio"]) && !empty($E40539dbfb9861abbd877a2ee47b9e65["codecs"]["audio"])) {
                                $audio_restart_loss = time();
                            }
                        }
                    }
                    if (ipTV_lib::$SegmentsSettings["seg_time"] * 6 <= time() - $seg_time) {
                        $new_stream_out_file_path = md5_file($stream_m3u8_file_path);
                        if ($stream_out_file_path != $new_stream_out_file_path) {
                            $stream_out_file_path = $new_stream_out_file_path;
                            $seg_time = time();
                        }
                    }
                    if (ipTV_lib::$settings["priority_backup"] == 1 && 1 < count($A733a5416ffab6ff47547550f3f9f641) && $stream_parent_id == 0 && 10 < time() - $priority_backup) {
                        $priority_backup = time();
                        $Baee0c34e5755f1cfaa4159ea7e8702e = array_search($stream_curent_source, $A733a5416ffab6ff47547550f3f9f641);
                        if (0 < $Baee0c34e5755f1cfaa4159ea7e8702e) {
                            foreach ($A733a5416ffab6ff47547550f3f9f641 as $F3803fa85b38b65447e6d438f8e9176a) {
                                $B16ceb354351bfb3944291018578c764 = ipTV_stream::ParseStreamURL($F3803fa85b38b65447e6d438f8e9176a);
                                if ($B16ceb354351bfb3944291018578c764 != $stream_curent_source) {
                                    $F53be324c8d9391cc021f5be5dacdfc1 = strtolower(substr($B16ceb354351bfb3944291018578c764, 0, strpos($B16ceb354351bfb3944291018578c764, "://")));
                                    $be9f906faa527985765b1d8c897fb13a = implode(" ", ipTV_stream::EA860c1d3851C46D06e64911E3602768($Ec54d2818a814ae4c359a5fc4ffff2ee, $F53be324c8d9391cc021f5be5dacdfc1, "fetch"));
                                    if (!($Ec610f8d82d35339f680a3ec9bbc078c = ipTV_stream::e0a1164567005185E0818f081674e240($B16ceb354351bfb3944291018578c764, SERVER_ID, $be9f906faa527985765b1d8c897fb13a))) {
                                    } else {
                                        $B71703fbd9f237149967f9ac3c41dc19 = $B16ceb354351bfb3944291018578c764;
                                    }
                                }
                            }
                        }
                    }
                    if ($Cb60ed5772c86d5ca16425608a588951 && $stream["delay_available_at"] <= time() && !ipTV_streaming::CheckPidStreamExist($stream_delay_pid, $stream_id)) {
                        $stream_delay_pid = intval(shell_exec(PHP_BIN . " " . TOOLS_PATH . "delay.php " . $stream_id . " " . $stream["delay_minutes"] . " >/dev/null 2>/dev/null & echo \$!"));
                    }
                    sleep(1);
                }
                $ipTV_db->db_connect();
            }
            if (ipTV_streaming::CheckPidChannelM3U8Exist($stream_pid, $stream_id)) {
                shell_exec("kill -9 " . $stream_pid);
                usleep(50000);
            }
            if (!ipTV_streaming::CheckPidStreamExist($stream_delay_pid, $stream_id)) {
                while (ipTV_streaming::CheckPidChannelM3U8Exist($stream_pid, $stream_id)) {
                }
            } else {
                shell_exec("kill -9 " . $stream_delay_pid);
                usleep(50000);
            }
            echo "Restarting...\n";
            shell_exec("rm -f " . STREAMS_PATH . $stream_id . "_*");
            $d76067cf9572f7a6691c85c12faf2a29 = ipTV_stream::cebEeE6A9c20E0da24C41A0247cF1244($stream_id, $ecae69bb74394743482337ade627630b, $B71703fbd9f237149967f9ac3c41dc19);
            if ($d76067cf9572f7a6691c85c12faf2a29 !== false) {
                if (!(is_numeric($d76067cf9572f7a6691c85c12faf2a29) && $d76067cf9572f7a6691c85c12faf2a29 == 0)) {
                    sleep(mt_rand(5, 10));
                    $stream_pid = $d76067cf9572f7a6691c85c12faf2a29["main_pid"];
                    $stream_m3u8_file_path = $d76067cf9572f7a6691c85c12faf2a29["playlist"];
                    $Cb60ed5772c86d5ca16425608a588951 = $d76067cf9572f7a6691c85c12faf2a29["delay_enabled"];
                    $stream["delay_available_at"] = $d76067cf9572f7a6691c85c12faf2a29["delay_start_at"];
                    $stream_curent_source = $d76067cf9572f7a6691c85c12faf2a29["stream_source"];
                    $stream_parent_id = $d76067cf9572f7a6691c85c12faf2a29["parent_id"];
                    $B71703fbd9f237149967f9ac3c41dc19 = NULL;
                    if ($Cb60ed5772c86d5ca16425608a588951) {
                        $stream_path = DELAY_STREAM;
                    } else {
                        $stream_path = STREAMS_PATH;
                    }
                    for ($a88c8d86d7956601164a5f156d5df985 = 0; !(ipTV_streaming::CheckPidChannelM3U8Exist($stream_pid, $stream_id) && !file_exists($stream_m3u8_file_path) && $a88c8d86d7956601164a5f156d5df985 <= ipTV_lib::$SegmentsSettings["seg_time"] * 3); $a88c8d86d7956601164a5f156d5df985++) {
                        echo "Checking For PlayList...\n";
                        sleep(1);
                    }
                    if ($a88c8d86d7956601164a5f156d5df985 == ipTV_lib::$SegmentsSettings["seg_time"] * 3) {
                        shell_exec("kill -9 " . $stream_pid);
                        usleep(50000);
                    }
                    if (RESTART_TAKE_CACHE < $ecae69bb74394743482337ade627630b) {
                        $ecae69bb74394743482337ade627630b = 0;
                    }
                } else {
                    sleep(mt_rand(10, 25));
                }
            } else {
                exit;
            }
        } else {
            ipTV_stream::stopStream($stream_id);
            exit;
        }
    } else {
        echo "[*] Correct Usage: php " . __FILE__ . " <stream_id> [restart]\n";
        exit;
    }
} else {
    exit(0);
}
