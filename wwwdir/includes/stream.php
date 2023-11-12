<?php

class ipTV_stream {
    public static $ipTV_db;
    static function deleteFiles($sources) {
        if (empty($sources)) {
            return;
        }
        foreach ($sources as $source) {
            if (file_exists(STREAMS_PATH . md5($source))) {
                unlink(STREAMS_PATH . md5($source));
            }
        }
    }
    static function TranscodeBuild($stream_id) {
        self::$ipTV_db->query('SELECT * FROM `streams` t1 LEFT JOIN `transcoding_profiles` t3 ON t1.transcode_profile_id = t3.profile_id WHERE t1.`id` = \'%d\'', $stream_id);
        $stream = self::$ipTV_db->get_row();
        $stream['cchannel_rsources'] = json_decode($stream['cchannel_rsources'], true);
        $stream['stream_source'] = json_decode($stream['stream_source'], true);
        $stream['pids_create_channel'] = json_decode($stream['pids_create_channel'], true);
        $stream['transcode_attributes'] = json_decode($stream['profile_options'], true);
        if (!array_key_exists('-acodec', $stream['transcode_attributes'])) {
            $stream['transcode_attributes']['-acodec'] = 'copy';
        }
        if (!array_key_exists('-vcodec', $stream['transcode_attributes'])) {
            $stream['transcode_attributes']['-vcodec'] = 'copy';
        }
        $ffmpegCommand = FFMPEG_PATH . ' -fflags +genpts -async 1 -y -nostdin -hide_banner -loglevel quiet -i "{INPUT}" ';
        $ffmpegCommand .= implode(' ', self::formatAttributes($stream['transcode_attributes'])) . ' ';
        $ffmpegCommand .= '-strict -2 -mpegts_flags +initial_discontinuity -f mpegts "' . CREATED_CHANNELS . $stream_id . '_{INPUT_MD5}.ts" >/dev/null 2>/dev/null & jobs -p';
        $result = array_diff($stream['stream_source'], $stream['cchannel_rsources']);
        $json_string_data = '';
        foreach ($stream['stream_source'] as $source) {
            $json_string_data .= 'file \'' . CREATED_CHANNELS . $stream_id . '_' . md5($source) . '.ts\'';
        }
        $json_string_data = base64_encode($json_string_data);
        if ((!empty($result) || $stream['stream_source'] !== $stream['cchannel_rsources'])) {
            foreach ($result as $source) {
                $stream['pids_create_channel'][] = ipTV_servers::RunCommandServer($stream['created_channel_location'], str_ireplace(array('{INPUT}', '{INPUT_MD5}'), array($source, md5($source)), $ffmpegCommand), 'raw')[$stream['created_channel_location']];
            }
            self::$ipTV_db->query('UPDATE `streams` SET pids_create_channel = \'%s\',`cchannel_rsources` = \'%s\' WHERE `id` = \'%d\'', json_encode($stream['pids_create_channel']), json_encode($stream['stream_source']), $stream_id);
            ipTV_servers::RunCommandServer($stream['created_channel_location'], "echo {$json_string_data} | base64 --decode > \"" . CREATED_CHANNELS . $stream_id . '_.list"', 'raw');
            return 1;
        } else if (!empty($stream['pids_create_channel'])) {
            foreach ($stream['pids_create_channel'] as $key => $pid) {
                if (!ipTV_servers::PidsChannels($stream['created_channel_location'], $pid, FFMPEG_PATH)) {
                    unset($stream['pids_create_channel'][$key]);
                }
            }
            self::$ipTV_db->query('UPDATE `streams` SET pids_create_channel = \'%s\' WHERE `id` = \'%d\'', json_encode($stream['pids_create_channel']), $stream_id);
            return empty($stream['pids_create_channel']) ? 2 : 1;
        }

        return 2;
    }
    static function E0A1164567005185e0818F081674E240($InputFileUrl, $serverId, $f84c1c6145bb73410b3ea7c0f8b4a9f3 = array(), $dir = '') {
        $stream_max_analyze = abs(intval(ipTV_lib::$settings['stream_max_analyze']));
        $probesize = abs(intval(ipTV_lib::$settings['probesize']));
        $timeout = intval($stream_max_analyze / 1000000) + 5;
        $command = "{$dir}/usr/bin/timeout {$timeout}s " . FFPROBE_PATH . " -probesize {$probesize} -analyzeduration {$stream_max_analyze} " . implode(' ', $f84c1c6145bb73410b3ea7c0f8b4a9f3) . " -i \"{$InputFileUrl}\" -v quiet -print_format json -show_streams -show_format";
        $result = ipTV_servers::RunCommandServer($serverId, $command, 'raw', $timeout * 2, $timeout * 2);
        return self::ParseCodecs(json_decode($result[$serverId], true));
    }
    public static function ParseCodecs($data) {
        if (!empty($data)) {
            if (!empty($data['codecs'])) {
                return $data;
            }
            $output = array();
            $output['codecs']['video'] = '';
            $output['codecs']['audio'] = '';
            $output['container'] = $data['format']['format_name'];
            $output['filename'] = $data['format']['filename'];
            $output['bitrate'] = !empty($data['format']['bit_rate']) ? $data['format']['bit_rate'] : null;
            $output['of_duration'] = !empty($data['format']['duration']) ? $data['format']['duration'] : 'N/A';
            $output['duration'] = !empty($data['format']['duration']) ? gmdate('H:i:s', intval($data['format']['duration'])) : 'N/A';
            foreach ($data['streams'] as $streamData) {
                if (!isset($streamData['codec_type'])) {
                    continue;
                }
                if ($streamData['codec_type'] != 'audio' && $streamData['codec_type'] != 'video') {
                    continue;
                }
                $output['codecs'][$streamData['codec_type']] = $streamData;
            }
            return $output;
        }
        return false;
    }
    static function startStream($stream_id, $delay_minutes = 0) {
        $stream_lock_file = STREAMS_PATH . $stream_id . '.lock';
        $fp = fopen($stream_lock_file, 'a+');
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            $delay_minutes = intval($delay_minutes);
            shell_exec(PHP_BIN . ' ' . TOOLS_PATH . "stream_monitor.php {$stream_id} {$delay_minutes} >/dev/null 2>/dev/null &");
            usleep(300);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    static function stopStream($stream_id, $reset_stream_sys = false) {
        if (file_exists("/home/xtreamcodes/iptv_xtream_codes/streams/{$stream_id}.monitor")) {
            $pid_stream_monitor = intval(file_get_contents("/home/xtreamcodes/iptv_xtream_codes/streams/{$stream_id}.monitor"));
            if (self::FindPidByValue($pid_stream_monitor, "XtreamCodes[{$stream_id}]")) {
                posix_kill($pid_stream_monitor, 9);
            }
        }
        if (file_exists(STREAMS_PATH . $stream_id . '_.pid')) {
            $pid = intval(file_get_contents(STREAMS_PATH . $stream_id . '_.pid'));
            if (self::FindPidByValue($pid, "{$stream_id}_.m3u8")) {
                posix_kill($pid, 9);
            }
        }
        shell_exec('rm -f ' . STREAMS_PATH . $stream_id . '_*');
        if ($reset_stream_sys) {
            shell_exec('rm -f ' . DELAY_STREAM . $stream_id . '_*');
            self::$ipTV_db->query('UPDATE `streams_sys` SET `bitrate` = NULL,`current_source` = NULL,`to_analyze` = 0,`pid` = NULL,`stream_started` = NULL,`stream_info` = NULL,`stream_status` = 0,`monitor_pid` = NULL WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', $stream_id, SERVER_ID);
        }
    }
    static function FindPidByValue($pid, $search) {
        if (file_exists('/proc/' . $pid)) {
            $value = trim(file_get_contents("/proc/{$pid}/cmdline"));
            if (stristr($value, $search)) {
                return true;
            }
        }
        return false;
    }
    static function startVODstream($stream_id) {
        $stream = array();
        self::$ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 0 LEFT JOIN `transcoding_profiles` t4 ON t1.transcode_profile_id = t4.profile_id WHERE t1.direct_source = 0 AND t1.id = \'%d\'', $stream_id);
        if (self::$ipTV_db->num_rows() <= 0) {
            return false;
        }
        $stream['stream_info'] = self::$ipTV_db->get_row();
        $target_container = json_decode($stream['stream_info']['target_container'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $stream['stream_info']['target_container'] = $target_container;
        } else {
            $stream['stream_info']['target_container'] = array($stream['stream_info']['target_container']);
        }
        self::$ipTV_db->query('SELECT * FROM `streams_sys` WHERE stream_id  = \'%d\' AND `server_id` = \'%d\'', $stream_id, SERVER_ID);
        if (self::$ipTV_db->num_rows() <= 0) {
            return false;
        }
        $stream['server_info'] = self::$ipTV_db->get_row();
        self::$ipTV_db->query('SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = \'%d\' AND t1.argument_id = t2.id', $stream_id);
        $stream['stream_arguments'] = self::$ipTV_db->get_rows();
        $stream_source = urldecode(json_decode($stream['stream_info']['stream_source'], true)[0]);
        if (substr($stream_source, 0, 2) == 's:') {
            $source = explode(':', $stream_source, 3);
            $server_id = $source[1];
            if ($server_id != SERVER_ID) {
                $fileURL = ipTV_lib::$StreamingServers[$server_id]['api_url'] . '&action=getFile&filename=' . urlencode($source[2]);
            } else {
                $fileURL = $source[2];
            }
            $server_protocol = null;
        } else {
            $server_protocol = substr($stream_source, 0, strpos($stream_source, '://'));
            $fileURL = str_replace(' ', '%20', $stream_source);
        }
        $streamArguments = implode(' ', self::eA860C1D3851c46D06e64911E3602768($stream['stream_arguments'], $server_protocol, 'fetch'));

        if (isset($server_id) && $server_id == SERVER_ID && $stream['stream_info']['movie_symlink'] == 1) {
            $command = "ln -s \"{$fileURL}\" " . MOVIES_PATH . $stream_id . "." . pathinfo($fileURL, PATHINFO_EXTENSION) . " >/dev/null 2>/dev/null & echo \$! > " . MOVIES_PATH . $stream_id . "_.pid";
        }
        $subtitles = json_decode($stream["stream_info"]["movie_subtitles"], true);
        $commandSubCharenc = '';

        for ($index = 0; $index < count($subtitles["files"]); $index++) {
            $subtitleFile = urldecode($subtitles["files"][$index]);
            $subtitleCharset = $subtitles["charset"][$index];
            if ($subtitles["location"] == SERVER_ID) {
                $commandSubCharenc .= "-sub_charenc \"{$subtitleCharset}\" -i \"{$subtitleFile}\" ";
            } else {
                $commandSubCharenc .= "-sub_charenc \"{$subtitleCharset}\" -i \"" . ipTV_lib::$StreamingServers[$subtitles["location"]]["api_url"] . "&action=getFile&filename=" . urlencode($subtitleFile) . "\" ";
            }
        }

        $command = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} -fflags +genpts -async 1 {READ_NATIVE} -i \"{STREAM_SOURCE}\" {$commandSubCharenc}";
        $read_native = '';
        if (!($stream['stream_info']['read_native'] == 1)) {
            $read_native = '-re';
        }
        if ($stream['stream_info']['enable_transcode'] == 1) {
            if ($stream['stream_info']['transcode_profile_id'] == -1) {
                $stream['stream_info']['transcode_attributes'] = array_merge(self::ea860c1d3851c46d06E64911e3602768($stream['stream_arguments'], $server_protocol, 'transcode'), json_decode($stream['stream_info']['transcode_attributes'], true));
            } else {
                $stream['stream_info']['transcode_attributes'] = json_decode($stream['stream_info']['profile_options'], true);
            }
        } else {
            $stream['stream_info']['transcode_attributes'] = array();
        }
        $map = '-map 0 -copy_unknown ';
        if (!empty($stream['stream_info']['custom_map'])) {
            $map = $stream['stream_info']['custom_map'] . ' -copy_unknown ';
        } elseif ($stream['stream_info']['remove_subtitles'] == 1) {
            $map = '-map 0:a -map 0:v';
        }

        if (array_key_exists('-acodec', $stream['stream_info']['transcode_attributes'])) {
            $stream['stream_info']['transcode_attributes']['-acodec'] = 'copy';
        }
        if (array_key_exists('-vcodec', $stream['stream_info']['transcode_attributes'])) {
            $stream['stream_info']['transcode_attributes']['-vcodec'] = 'copy';
        }
        $fileExtensions = array();
        foreach ($stream['stream_info']['target_container'] as $extension) {
            $fileExtensions[$extension] = "-movflags +faststart -dn {$map} -ignore_unknown {$subtitlesOptions} " . MOVIES_PATH . $stream_id . "." . $extension . " ";
        }

        foreach ($fileExtensions as $extension => $codec) {
            if ($extension == 'mp4') {
                $stream['stream_info']['transcode_attributes']['-scodec'] = 'mov_text';
            } elseif ($extension == 'mkv') {
                $stream['stream_info']['transcode_attributes']['-scodec'] = 'srt';
            } else {
                $stream['stream_info']['transcode_attributes']['-scodec'] = 'copy';
            }
            $command .= implode(' ', self::formatAttributes($stream['stream_info']['transcode_attributes'])) . ' ';
            $command .= $codec;
        }

        $command .= ' >/dev/null 2>' . MOVIES_PATH . $stream_id . '.errors & echo $! > ' . MOVIES_PATH . $stream_id . '_.pid';
        $command = str_replace(array('{FETCH_OPTIONS}', '{STREAM_SOURCE}', '{READ_NATIVE}'), array(empty($streamArguments) ? '' : $streamArguments, $fileURL, empty($stream['stream_info']['custom_ffmpeg']) ? $read_native : ''), $command);
        shell_exec($command);
        file_put_contents('/tmp/commands', $command . '\n', FILE_APPEND);
        $pid = intval(file_get_contents(MOVIES_PATH . $stream_id . '_.pid'));
        self::$ipTV_db->query('UPDATE `streams_sys` SET `to_analyze` = 1,`stream_started` = \'%d\',`stream_status` = 0,`pid` = \'%d\' WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', time(), $pid, $stream_id, SERVER_ID);
        return $pid;
    }
    static function stopVODstream($stream_id) {
        if (file_exists(MOVIES_PATH . $stream_id . '_.pid')) {
            $pid = (int) file_get_contents(MOVIES_PATH . $stream_id . '_.pid');
            posix_kill($pid, 9);
        }
        shell_exec('rm -f ' . MOVIES_PATH . $stream_id . '.*');
        self::$ipTV_db->query('UPDATE `streams_sys` SET `bitrate` = NULL,`current_source` = NULL,`to_analyze` = 0,`pid` = NULL,`stream_started` = NULL,`stream_info` = NULL,`stream_status` = 0 WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', $stream_id, SERVER_ID);
    }
    static function CEBeee6A9C20e0da24C41A0247cf1244($stream_id, &$bb1b9dfc97454460e165348212675779, $B71703fbd9f237149967f9ac3c41dc19 = null) {
        ++$bb1b9dfc97454460e165348212675779;
        if (file_exists(STREAMS_PATH . $stream_id . '_.pid')) {
            unlink(STREAMS_PATH . $stream_id . '_.pid');
        }
        $stream = array();
        self::$ipTV_db->query("SELECT * FROM `streams` t1
                               INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 1
                               LEFT JOIN `transcoding_profiles` t4 ON t1.transcode_profile_id = t4.profile_id 
                               WHERE t1.direct_source = 0 AND t1.id = '%d'", $stream_id);
        if (!(self::$ipTV_db->num_rows() <= 0)) {
            $stream['stream_info'] = self::$ipTV_db->get_row();
            self::$ipTV_db->query("SELECT * FROM `streams_sys` WHERE stream_id  = '%d' AND `server_id` = '%d'", $stream_id, SERVER_ID);
            if (!(self::$ipTV_db->num_rows() <= 0)) {
                $stream['server_info'] = self::$ipTV_db->get_row();
                self::$ipTV_db->query("SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = '%d' AND t1.argument_id = t2.id", $stream_id);
                $stream['stream_arguments'] = self::$ipTV_db->get_rows();
                if ($stream['server_info']['on_demand'] == 1) {
                    $stream_probesize = $stream['stream_info']['probesize_ondemand'];
                    $stream_max_analyze = '10000000';
                } else {
                    $stream_max_analyze = abs(intval(ipTV_lib::$settings['stream_max_analyze']));
                    $stream_probesize = abs(intval(ipTV_lib::$settings['probesize']));
                }
                $d1c5b35a94aa4152ee37c6cfedfb2ec3 = intval($stream_max_analyze / 1000000) + 7;
                $Fa28e3498375fc4da68f3f818d774249 = "/usr/bin/timeout {$d1c5b35a94aa4152ee37c6cfedfb2ec3}s " . FFPROBE_PATH . " {FETCH_OPTIONS} -probesize {$stream_probesize} -analyzeduration {$stream_max_analyze} {CONCAT} -i \"{STREAM_SOURCE}\" -v quiet -print_format json -show_streams -show_format";
                $be9f906faa527985765b1d8c897fb13a = array();
                if ($stream["server_info"]["parent_id"] == 0) {
                    $A733a5416ffab6ff47547550f3f9f641 = $stream["stream_info"]["type_key"] == "created_live" ? array(CREATED_CHANNELS . $stream_id . "_.list") : json_decode($stream["stream_info"]["stream_source"], true);
                } else {
                    $A733a5416ffab6ff47547550f3f9f641 = array(ipTV_lib::$StreamingServers[$stream["server_info"]["parent_id"]]["site_url_ip"] . "streaming/admin_live.php?stream=" . $stream_id . "&password=" . ipTV_lib::$settings["live_streaming_pass"] . "&extension=ts");
                }
                if (!(count($A733a5416ffab6ff47547550f3f9f641) > 0)) {
                    goto Addf182f86a94b305381bd0e81174f08;
                }
                if (!empty($B71703fbd9f237149967f9ac3c41dc19)) {
                    $A733a5416ffab6ff47547550f3f9f641 = array($B71703fbd9f237149967f9ac3c41dc19);
                    goto ac8a864b3489c444d14e1904ec5dfd7e;
                }
                if (ipTV_lib::$settings["priority_backup"] != 1) {
                    if (empty($stream["server_info"]["current_source"])) {
                        goto e7eedc2b99021a3a11f4a0933af2c2b1;
                    }
                    $Baee0c34e5755f1cfaa4159ea7e8702e = array_search($stream["server_info"]["current_source"], $A733a5416ffab6ff47547550f3f9f641);
                    if (!($Baee0c34e5755f1cfaa4159ea7e8702e !== false)) {
                        goto D63aacc4ea564f12e24abd6538c7b052;
                    }
                    $C48e0083a9caa391609a3c645a2ec889 = 0;
                    B1fcf06a1d6da24af4b5d7d516d25b90:
                    if (!($C48e0083a9caa391609a3c645a2ec889 <= $Baee0c34e5755f1cfaa4159ea7e8702e)) {
                        $A733a5416ffab6ff47547550f3f9f641 = array_values($A733a5416ffab6ff47547550f3f9f641);
                        D63aacc4ea564f12e24abd6538c7b052:
                        e7eedc2b99021a3a11f4a0933af2c2b1:
                        goto Fd0c9fa73a22d3ad21baec039c9f9b6c;
                    }
                    $Ad110d626a9e62f0778a8f19383a0613 = $A733a5416ffab6ff47547550f3f9f641[$C48e0083a9caa391609a3c645a2ec889];
                    unset($A733a5416ffab6ff47547550f3f9f641[$C48e0083a9caa391609a3c645a2ec889]);
                    array_push($A733a5416ffab6ff47547550f3f9f641, $Ad110d626a9e62f0778a8f19383a0613);
                    $C48e0083a9caa391609a3c645a2ec889++;
                    goto B1fcf06a1d6da24af4b5d7d516d25b90;
                }
                Fd0c9fa73a22d3ad21baec039c9f9b6c:
                ac8a864b3489c444d14e1904ec5dfd7e:
                Addf182f86a94b305381bd0e81174f08:
                $F7b03a1f7467c01c6ea18452d9a5202f = $bb1b9dfc97454460e165348212675779 <= RESTART_TAKE_CACHE ? true : false;
                if ($F7b03a1f7467c01c6ea18452d9a5202f) {
                    goto ebd27b3edaaacb30705e86c5be704ca9;
                }
                self::deleteFiles($A733a5416ffab6ff47547550f3f9f641);
                ebd27b3edaaacb30705e86c5be704ca9:
                foreach ($A733a5416ffab6ff47547550f3f9f641 as $F3803fa85b38b65447e6d438f8e9176a) {
                    $B16ceb354351bfb3944291018578c764 = self::ParseStreamURL($F3803fa85b38b65447e6d438f8e9176a);
                    $F53be324c8d9391cc021f5be5dacdfc1 = strtolower(substr($B16ceb354351bfb3944291018578c764, 0, strpos($B16ceb354351bfb3944291018578c764, "://")));
                    $be9f906faa527985765b1d8c897fb13a = implode(" ", self::Ea860c1d3851C46D06E64911E3602768($stream["stream_arguments"], $F53be324c8d9391cc021f5be5dacdfc1, "fetch"));
                    if (!($F7b03a1f7467c01c6ea18452d9a5202f && file_exists(STREAMS_PATH . md5($B16ceb354351bfb3944291018578c764)))) {
                        $e49460014c491accfafaa768ea84cd9c = json_decode(shell_exec(str_replace(array("{FETCH_OPTIONS}", "{CONCAT}", "{STREAM_SOURCE}"), array($be9f906faa527985765b1d8c897fb13a, $stream["stream_info"]["type_key"] == "created_live" && $stream["server_info"]["parent_id"] == 0 ? "-safe 0 -f concat" : '', $B16ceb354351bfb3944291018578c764), $Fa28e3498375fc4da68f3f818d774249)), true);
                        if (empty($e49460014c491accfafaa768ea84cd9c)) {
                        }
                        goto D4dc4038a49e681798bdc5fcc086c56d;
                    }
                    $e49460014c491accfafaa768ea84cd9c = json_decode(file_get_contents(STREAMS_PATH . md5($B16ceb354351bfb3944291018578c764)), true);
                    goto D4dc4038a49e681798bdc5fcc086c56d;
                }
                D4dc4038a49e681798bdc5fcc086c56d:
                if (!empty($e49460014c491accfafaa768ea84cd9c)) {
                    if ($F7b03a1f7467c01c6ea18452d9a5202f) {
                        goto f21367977400d55935f38a3b5a6cd287;
                    }
                    file_put_contents(STREAMS_PATH . md5($B16ceb354351bfb3944291018578c764), json_encode($e49460014c491accfafaa768ea84cd9c));
                    f21367977400d55935f38a3b5a6cd287:
                    $e49460014c491accfafaa768ea84cd9c = self::ParseCodecs($e49460014c491accfafaa768ea84cd9c);
                    $stream_external_push = json_decode($stream["stream_info"]["external_push"], true);
                    $e1dc30615033011f7166d1950e7036ee = "http://127.0.0.1:" . ipTV_lib::$StreamingServers[SERVER_ID]["http_broadcast_port"] . "/progress.php?stream_id={$stream_id}";
                    if (empty($stream["stream_info"]["custom_ffmpeg"])) {
                        $af428179032a83d9ec1df565934b1c89 = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} {GEN_PTS} {READ_NATIVE} -probesize {$stream_probesize} -analyzeduration {$stream_max_analyze} -progress \"{$e1dc30615033011f7166d1950e7036ee}\" {CONCAT} -i \"{STREAM_SOURCE}\" ";
                        if ($stream["stream_info"]["stream_all"] == 1) {
                            $fd85ae68a4de5cc6cec54942d82e8f80 = "-map 0 -copy_unknown ";
                            goto F7052b7340617388b1314ad99c08b3b6;
                        }
                        if (!empty($stream["stream_info"]["custom_map"])) {
                            $fd85ae68a4de5cc6cec54942d82e8f80 = $stream["stream_info"]["custom_map"] . " -copy_unknown ";
                            goto F7052b7340617388b1314ad99c08b3b6;
                        }
                        if ($stream["stream_info"]["type_key"] == "radio_streams") {
                            $fd85ae68a4de5cc6cec54942d82e8f80 = "-map 0:a? ";
                            goto c2fac9fbdb037e05684fb8450b6a5ba7;
                        }
                        $fd85ae68a4de5cc6cec54942d82e8f80 = '';
                        c2fac9fbdb037e05684fb8450b6a5ba7:
                        F7052b7340617388b1314ad99c08b3b6:
                        if (($stream["stream_info"]["gen_timestamps"] == 1 || empty($F53be324c8d9391cc021f5be5dacdfc1)) && $stream["stream_info"]["type_key"] != "created_live") {
                            $e9652f3db39531a69b91900690d5d064 = "-fflags +genpts -async 1";
                            goto a6cb04ba2fdaf4417d4a82959148687f;
                        }
                        $e9652f3db39531a69b91900690d5d064 = "-nofix_dts -start_at_zero -copyts -vsync 0 -correct_ts_overflow 0 -avoid_negative_ts disabled -max_interleave_delta 0";
                        a6cb04ba2fdaf4417d4a82959148687f:
                        $feb3f2070e6ccf961f6265281e875b1a = '';
                        if (!($stream["server_info"]["parent_id"] == 0 && ($stream["stream_info"]["read_native"] == 1 or stristr($e49460014c491accfafaa768ea84cd9c["container"], "hls") or empty($F53be324c8d9391cc021f5be5dacdfc1) or stristr($e49460014c491accfafaa768ea84cd9c["container"], "mp4") or stristr($e49460014c491accfafaa768ea84cd9c["container"], "matroska")))) {
                            goto f283f80882362b693eafe8affe5b7574;
                        }
                        $feb3f2070e6ccf961f6265281e875b1a = "-re";
                        f283f80882362b693eafe8affe5b7574:
                        if ($stream["server_info"]["parent_id"] == 0 and $stream["stream_info"]["enable_transcode"] == 1 and $stream["stream_info"]["type_key"] != "created_live") {
                            if ($stream["stream_info"]["transcode_profile_id"] == -1) {
                                $stream["stream_info"]["transcode_attributes"] = array_merge(self::EA860c1D3851c46d06E64911E3602768($stream["stream_arguments"], $F53be324c8d9391cc021f5be5dacdfc1, "transcode"), json_decode($stream["stream_info"]["transcode_attributes"], true));
                            } else {
                                $stream["stream_info"]["transcode_attributes"] = json_decode($stream["stream_info"]["profile_options"], true);
                            }
                            goto D724bdd07744b75724723c57be250efb;
                        }
                        $stream['stream_info']['transcode_attributes'] = array();
                        D724bdd07744b75724723c57be250efb:
                        if (!array_key_exists('-acodec', $stream['stream_info']['transcode_attributes'])) {
                            $stream['stream_info']['transcode_attributes']['-acodec'] = 'copy';
                        }
                        if (!array_key_exists('-vcodec', $stream['stream_info']['transcode_attributes'])) {
                            $stream['stream_info']['transcode_attributes']['-vcodec'] = 'copy';
                        }
                        if (!array_key_exists('-scodec', $stream['stream_info']['transcode_attributes'])) {
                            $stream['stream_info']['transcode_attributes']['-scodec'] = 'copy';
                        }
                        goto A7314975472ac8c8f1bdd009199221a3;
                    }
                    $stream["stream_info"]["transcode_attributes"] = array();
                    $af428179032a83d9ec1df565934b1c89 = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel quiet {$d1006c7cc041221972025137b5112b7d} -progress \"{$e1dc30615033011f7166d1950e7036ee}\" " . $stream["stream_info"]["custom_ffmpeg"];
                    A7314975472ac8c8f1bdd009199221a3:
                    $A7c6258649492b26d77c75c60c793409 = array();
                    $A7c6258649492b26d77c75c60c793409["mpegts"][] = "{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time " . ipTV_lib::$SegmentsSettings["seg_time"] . " -segment_list_size " . ipTV_lib::$SegmentsSettings["seg_list_size"] . " -segment_format_options \"mpegts_flags=+initial_discontinuity:mpegts_copyts=1\" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list \"" . STREAMS_PATH . $stream_id . "_.m3u8\" \"" . STREAMS_PATH . $stream_id . "_%d.ts\" ";
                    if ($stream['stream_info']['rtmp_output'] == 1) {
                        $A7c6258649492b26d77c75c60c793409['flv'][] = '{MAP} {AAC_FILTER} -f flv rtmp://127.0.0.1:' . ipTV_lib::$StreamingServers[$stream['server_info']['server_id']]['rtmp_port'] . '/live/{$stream_id} ';
                    }
                    if (!empty($stream_external_push[SERVER_ID])) {
                        foreach ($stream_external_push[SERVER_ID] as $b202bc9c1c41da94906c398ceb9f3573) {
                            $A7c6258649492b26d77c75c60c793409["flv"][] = "{MAP} {AAC_FILTER} -f flv \"{$b202bc9c1c41da94906c398ceb9f3573}\" ";
                        }
                    }
                    $delay_start_at = 0;
                    if (!($stream["stream_info"]["delay_minutes"] > 0 && $stream["server_info"]["parent_id"] == 0)) {
                        foreach ($A7c6258649492b26d77c75c60c793409 as $bca72c242cf770f855c0eae8936335b7 => $f72c3a34155eca511d79ca3671e1063f) {
                            foreach ($f72c3a34155eca511d79ca3671e1063f as $cd7bafd64552e6ca58318f09800cbddd) {
                                $af428179032a83d9ec1df565934b1c89 .= implode(" ", self::formatAttributes($stream["stream_info"]["transcode_attributes"])) . " ";
                                $af428179032a83d9ec1df565934b1c89 .= $cd7bafd64552e6ca58318f09800cbddd;
                            }
                        }
                        goto Ab3616c8de8bd4a36124f90f72d5bf1e;
                    }
                    $ccac9556cf5f7f83df650c022d673042 = 0;
                    if (!file_exists(DELAY_STREAM . $stream_id . "_.m3u8")) {
                        goto b5c7effa65c597936232525ef71cee85;
                    }
                    $Ca434bcc380e9dbd2a3a588f6c32d84f = file(DELAY_STREAM . $stream_id . "_.m3u8");
                    if (stristr($Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 1], $stream_id . "_")) {
                        if (!preg_match("/\\_(.*?)\\.ts/", $Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 1], $ae37877cee3bc97c8cfa6ec5843993ed)) {
                            goto E376243a25162a7e9a96615f05736000;
                        }
                        $ccac9556cf5f7f83df650c022d673042 = intval($ae37877cee3bc97c8cfa6ec5843993ed[1]) + 1;
                        E376243a25162a7e9a96615f05736000:
                        goto Bc358712da2195af6049c6dc19157a6b;
                    }
                    if (!preg_match("/\\_(.*?)\\.ts/", $Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 2], $ae37877cee3bc97c8cfa6ec5843993ed)) {
                        goto Ae9f3a95d0d642988cb827b99cf0c542;
                    }
                    $ccac9556cf5f7f83df650c022d673042 = intval($ae37877cee3bc97c8cfa6ec5843993ed[1]) + 1;
                    Ae9f3a95d0d642988cb827b99cf0c542:
                    Bc358712da2195af6049c6dc19157a6b:
                    if (file_exists(DELAY_STREAM . $stream_id . "_.m3u8_old")) {
                        file_put_contents(DELAY_STREAM . $stream_id . "_.m3u8_old", file_get_contents(DELAY_STREAM . $stream_id . "_.m3u8_old") . file_get_contents(DELAY_STREAM . $stream_id . "_.m3u8"));
                        shell_exec("sed -i '/EXTINF\\|.ts/!d' DELAY_STREAM" . $stream_id . "_.m3u8_old");
                        goto Bb78eb9c44efca565d4e7263bcc49b55;
                    }
                    copy(DELAY_STREAM . $stream_id . "_.m3u8", DELAY_STREAM . $stream_id . "_.m3u8_old");
                    Bb78eb9c44efca565d4e7263bcc49b55:
                    b5c7effa65c597936232525ef71cee85:
                    $af428179032a83d9ec1df565934b1c89 .= implode(" ", self::formatAttributes($stream["stream_info"]["transcode_attributes"])) . " ";
                    $af428179032a83d9ec1df565934b1c89 .= "{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time " . ipTV_lib::$SegmentsSettings["seg_time"] . " -segment_list_size " . $stream["stream_info"]["delay_minutes"] * 6 . " -segment_start_number {$ccac9556cf5f7f83df650c022d673042} -segment_format_options \"mpegts_flags=+initial_discontinuity:mpegts_copyts=1\" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list \"" . DELAY_STREAM . $stream_id . "_.m3u8\" \"" . DELAY_STREAM . $stream_id . "_%d.ts\" ";
                    $Dedb93a1e8822879d8790c1f2fc7d6f1 = $stream["stream_info"]["delay_minutes"] * 60;
                    if (!($ccac9556cf5f7f83df650c022d673042 > 0)) {
                        goto A5521d14dac6bbbe40d9e44c33dfa8a9;
                    }
                    $Dedb93a1e8822879d8790c1f2fc7d6f1 -= ($ccac9556cf5f7f83df650c022d673042 - 1) * 10;
                    if (!($Dedb93a1e8822879d8790c1f2fc7d6f1 <= 0)) {
                        goto ccbae97f6af29994e24944d9fa86c5ee;
                    }
                    $Dedb93a1e8822879d8790c1f2fc7d6f1 = 0;
                    ccbae97f6af29994e24944d9fa86c5ee:
                    A5521d14dac6bbbe40d9e44c33dfa8a9:
                    Ab3616c8de8bd4a36124f90f72d5bf1e:
                    $af428179032a83d9ec1df565934b1c89 .= " >/dev/null 2>>" . STREAMS_PATH . $stream_id . ".errors & echo \$! > " . STREAMS_PATH . $stream_id . "_.pid";
                    $af428179032a83d9ec1df565934b1c89 = str_replace(array("{INPUT}", "{FETCH_OPTIONS}", "{GEN_PTS}", "{STREAM_SOURCE}", "{MAP}", "{READ_NATIVE}", "{CONCAT}", "{AAC_FILTER}"), array("\"{$B16ceb354351bfb3944291018578c764}\"", empty($stream["stream_info"]["custom_ffmpeg"]) ? $be9f906faa527985765b1d8c897fb13a : '', empty($stream["stream_info"]["custom_ffmpeg"]) ? $e9652f3db39531a69b91900690d5d064 : '', $B16ceb354351bfb3944291018578c764, empty($stream["stream_info"]["custom_ffmpeg"]) ? $fd85ae68a4de5cc6cec54942d82e8f80 : '', empty($stream["stream_info"]["custom_ffmpeg"]) ? $feb3f2070e6ccf961f6265281e875b1a : '', $stream["stream_info"]["type_key"] == "created_live" && $stream["server_info"]["parent_id"] == 0 ? "-safe 0 -f concat" : '', !stristr($e49460014c491accfafaa768ea84cd9c["container"], "flv") && $e49460014c491accfafaa768ea84cd9c["codecs"]["audio"]["codec_name"] == "aac" && $stream["stream_info"]["transcode_attributes"]["-acodec"] == "copy" ? "-bsf:a aac_adtstoasc" : ''), $af428179032a83d9ec1df565934b1c89);
                    shell_exec($af428179032a83d9ec1df565934b1c89);
                    $Bc7d327b1510891329ca9859db27320f = $D90a38f0f1d7f1bcd1b2eee088e76aca = intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid"));
                    if (!(SERVER_ID == $stream["stream_info"]["tv_archive_server_id"])) {
                        goto ab4b9fd020e95bcd610e36802fdc7435;
                    }
                    shell_exec("PHP_BIN TOOLS_PATHarchive.php " . $stream_id . " >/dev/null 2>/dev/null & echo \$!");
                    ab4b9fd020e95bcd610e36802fdc7435:
                    $Dac1208baefb5d684938829a3a0e0bc6 = $stream["stream_info"]["delay_minutes"] > 0 && $stream["server_info"]["parent_id"] == 0 ? true : false;
                    $delay_start_at = $Dac1208baefb5d684938829a3a0e0bc6 ? time() + $Dedb93a1e8822879d8790c1f2fc7d6f1 : 0;
                    self::$ipTV_db->query("UPDATE `streams_sys` SET `delay_available_at` = '%d',`to_analyze` = 0,`stream_started` = '%d',`stream_info` = '%s',`stream_status` = 0,`pid` = '%d',`progress_info` = '%s',`current_source` = '%s' WHERE `stream_id` = '%d' AND `server_id` = '%d'", $delay_start_at, time(), json_encode($e49460014c491accfafaa768ea84cd9c), $Bc7d327b1510891329ca9859db27320f, json_encode(array()), $F3803fa85b38b65447e6d438f8e9176a, $stream_id, SERVER_ID);
                    $Bb37b848bec813a5c13ea0b018962c40 = !$Dac1208baefb5d684938829a3a0e0bc6 ? STREAMS_PATH . $stream_id . "_.m3u8" : DELAY_STREAM . $stream_id . "_.m3u8";
                    return array("main_pid" => $Bc7d327b1510891329ca9859db27320f, "stream_source" => $B16ceb354351bfb3944291018578c764, "delay_enabled" => $Dac1208baefb5d684938829a3a0e0bc6, "parent_id" => $stream["server_info"]["parent_id"], "delay_start_at" => $delay_start_at, "playlist" => $Bb37b848bec813a5c13ea0b018962c40);
                }
                if (!($stream["server_info"]["stream_status"] == 0 || $stream["server_info"]["to_analyze"] == 1 || $stream["server_info"]["pid"] != -1)) {
                    goto E3f0f6fa6f88a988847fe7991a8d19df;
                }
                self::$ipTV_db->query("UPDATE `streams_sys` SET `progress_info` = '',`to_analyze` = 0,`pid` = -1,`stream_status` = 1 WHERE `server_id` = '%d' AND `stream_id` = '%d'", SERVER_ID, $stream_id);
                E3f0f6fa6f88a988847fe7991a8d19df:
                return 0;
            }
            return false;
        }
        return false;
    }
    public static function customOrder($a, $b) {
        if (substr($a, 0, 3) == '-i ') {
            return -1;
        }
        return 1;
    }
    public static function EA860c1D3851C46d06E64911E3602768($stream_arguments, $server_protocol, $type) {
        $Eb6e347d24315f277ac38240a6589dd0 = array();
        if (!empty($stream_arguments)) {
            foreach ($stream_arguments as $f091df572e6d2b79881acbf4e5500a7e => $attribute) {
                if ($attribute['argument_cat'] != $type) {
                    continue;
                }
                if (!is_null($attribute['argument_wprotocol']) && !stristr($server_protocol, $attribute['argument_wprotocol']) && !is_null($server_protocol)) {
                    continue;
                }
                if ($attribute['argument_type'] == 'text') {
                    $Eb6e347d24315f277ac38240a6589dd0[] = sprintf($attribute['argument_cmd'], $attribute['value']);
                } else {
                    $Eb6e347d24315f277ac38240a6589dd0[] = $attribute['argument_cmd'];
                }
            }
        }
        return $Eb6e347d24315f277ac38240a6589dd0;
    }
    public static function formatAttributes($transcode_attributes) {
        $filter_complex = array();
        foreach ($transcode_attributes as $k => $attribute) {
            if (isset($attribute['cmd'])) {
                $transcode_attributes[$k] = $attribute = $attribute['cmd'];
            }
            if (preg_match('/-filter_complex "(.*?)"/', $attribute, $matches)) {
                $transcode_attributes[$k] = trim(str_replace($matches[0], '', $transcode_attributes[$k]));
                $filter_complex[] = $matches[1];
            }
        }
        if (!empty($filter_complex)) {
            $transcode_attributes[] = '-filter_complex "' . implode(',', $filter_complex) . '"';
        }
        $formatted_attributes = array();
        foreach ($transcode_attributes as $k => $attribute) {
            if (is_numeric($k)) {
                $formatted_attributes[] = $attribute;
            } else {
                $formatted_attributes[] = $k . ' ' . $attribute;
            }
        }
        $formatted_attributes = array_filter($formatted_attributes);
        uasort($formatted_attributes, array(__CLASS__, 'customOrder'));
        return array_map('trim', array_values(array_filter($formatted_attributes)));
    }
    public static function ParseStreamURL($url) {
        $server_protocol = strtolower(substr($url, 0, 4));
        if (($server_protocol == 'rtmp')) {
            if (stristr($url, '$OPT')) {
                $rtmp_url = 'rtmp://$OPT:rtmp-raw=';
                $url = trim(substr($url, stripos($url, $rtmp_url) + strlen($rtmp_url)));
            }
            $url .= ' live=1 timeout=10';
        } else if ($server_protocol == 'http') {
            $hosts = array('youtube.com', 'youtu.be', 'livestream.com', 'ustream.tv', 'twitch.tv', 'vimeo.com', 'facebook.com', 'dailymotion.com', 'cnn.com', 'edition.cnn.com', 'youporn.com', 'pornhub.com', 'youjizz.com', 'xvideos.com', 'redtube.com', 'ruleporn.com', 'pornotube.com', 'skysports.com', 'screencast.com', 'xhamster.com', 'pornhd.com', 'pornktube.com', 'tube8.com', 'vporn.com', 'giniko.com', 'xtube.com');
            $host = str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
            if (in_array($host, $hosts)) {
                $urls = trim(shell_exec(YOUTUBE_PATH . " \"{$url}\" -q --get-url --skip-download -f best"));
                $url = explode('', $urls)[0];
            }
        }
        return $url;
    }
}
