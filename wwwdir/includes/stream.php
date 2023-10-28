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
        $ffmpegCommand .= implode(' ', self::F6664C80BDe3e9BbE2C12ceB906D5A11($stream['transcode_attributes'])) . ' ';
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
    static function stopVODstream($stream_id) {
        if (file_exists(MOVIES_PATH . $stream_id . '_.pid')) {
            $pid = (int) file_get_contents(MOVIES_PATH . $stream_id . '_.pid');
            posix_kill($pid, 9);
        }
        shell_exec('rm -f ' . MOVIES_PATH . $stream_id . '.*');
        self::$ipTV_db->query('UPDATE `streams_sys` SET `bitrate` = NULL,`current_source` = NULL,`to_analyze` = 0,`pid` = NULL,`stream_started` = NULL,`stream_info` = NULL,`stream_status` = 0 WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', $stream_id, SERVER_ID);
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
            $be9f906faa527985765b1d8c897fb13a = implode(' ', self::eA860C1D3851c46D06e64911E3602768($stream['stream_arguments'], $server_protocol, 'fetch'));
        }

        if (!(isset($server_id) && $server_id == SERVER_ID && $stream['stream_info']['movie_symlink'] == 1)) {
            $movie_subtitles = json_decode($stream['stream_info']['movie_subtitles'], true);
            $commandSubCharenc = '';
            $index = 0;

            while ($index < count($movie_subtitles['files'])) {
                $fileUrl = urldecode($movie_subtitles['files'][$index]);
                $charset = $movie_subtitles['charset'][$index];
                if ($movie_subtitles['location'] == SERVER_ID) {
                    $commandSubCharenc .= "-sub_charenc \"{$charset}\" -i \"{$fileUrl}\" ";
                } else {
                    $commandSubCharenc .= "-sub_charenc \"{$charset}\" -i \"" . ipTV_lib::$StreamingServers[$movie_subtitles['location']]['api_url'] . '&action=getFile&filename=' . urlencode($fileUrl) . '" ';
                }
                $index++;
            }

            $f2130ba0f82d2308b743977b2ba5eaa9 = '';
            $index = 0;

            while ($index < count($movie_subtitles['files'])) {
                $f2130ba0f82d2308b743977b2ba5eaa9 .= '-map ' . ($index + 1) . " -metadata:s:s:{$index} title={$movie_subtitles['names'][$index]} -metadata:s:s:{$index} language={$movie_subtitles['names'][$index]} ";
                $index++;
            }

            $command = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} -fflags +genpts -async 1 {READ_NATIVE} -i \"{STREAM_SOURCE}\" {$commandSubCharenc}";
            $read_native = '';
            if ($stream['stream_info']['read_native'] == 1) {
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
            if (empty($stream['stream_info']['custom_map'])) {
                $map = $stream['stream_info']['custom_map'] . ' -copy_unknown ';
            } else if ($stream['stream_info']['remove_subtitles'] == 1) {
                $map = '-map 0:a -map 0:v';
            }

            if (!array_key_exists('-acodec', $stream['stream_info']['transcode_attributes'])) {
                $stream['stream_info']['transcode_attributes']['-acodec'] = 'copy';
            }
            if (!array_key_exists('-vcodec', $stream['stream_info']['transcode_attributes'])) {
                $stream['stream_info']['transcode_attributes']['-vcodec'] = 'copy';
            }
            $A7c6258649492b26d77c75c60c793409 = array();
            foreach ($stream['stream_info']['target_container'] as $container_priority) {
                $A7c6258649492b26d77c75c60c793409[$container_priority] = "-movflags +faststart -dn {$map} -ignore_unknown {$f2130ba0f82d2308b743977b2ba5eaa9} " . MOVIES_PATH . $stream_id . '.' . $container_priority . ' ';
            }
            foreach ($A7c6258649492b26d77c75c60c793409 as $output_key => $cd7bafd64552e6ca58318f09800cbddd) {
                if (($output_key == 'mp4')) {
                    $stream['stream_info']['transcode_attributes']['-scodec'] = 'mov_text';
                } else if ($output_key == 'mkv') {
                    $stream['stream_info']['transcode_attributes']['-scodec'] = 'srt';
                } else {
                    $stream['stream_info']['transcode_attributes']['-scodec'] = 'copy';
                }
                $command .= implode(' ', self::F6664c80BDe3e9bbe2c12CEb906D5A11($stream['stream_info']['transcode_attributes'])) . ' ';
                $command .= $cd7bafd64552e6ca58318f09800cbddd;
            }

            $command .= ' >/dev/null 2>' . MOVIES_PATH . $stream_id . '.errors & echo $! > ' . MOVIES_PATH . $stream_id . '_.pid';
            $command = str_replace(array('{FETCH_OPTIONS}', '{STREAM_SOURCE}', '{READ_NATIVE}'), array(empty($be9f906faa527985765b1d8c897fb13a) ? '' : $be9f906faa527985765b1d8c897fb13a, $fileURL, empty($stream['stream_info']['custom_ffmpeg']) ? $read_native : ''), $command);
            $command = "ln -s \"{$fileURL}\" " . MOVIES_PATH . $stream_id . '.' . pathinfo($fileURL, PATHINFO_EXTENSION) . ' >/dev/null 2>/dev/null & echo $! > ' . MOVIES_PATH . $stream_id . '_.pid';
            shell_exec($command);
            file_put_contents('/tmp/commands', $command . '', FILE_APPEND);
            $pid = intval(file_get_contents(MOVIES_PATH . $stream_id . '_.pid'));
            self::$ipTV_db->query('UPDATE `streams_sys` SET `to_analyze` = 1,`stream_started` = \'%d\',`stream_status` = 0,`pid` = \'%d\' WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', time(), $pid, $stream_id, SERVER_ID);
            return $pid;
        }
    }
    static function CEBeee6A9C20e0da24C41A0247cf1244($stream_id, &$bb1b9dfc97454460e165348212675779, $B71703fbd9f237149967f9ac3c41dc19 = null) {
        ++$bb1b9dfc97454460e165348212675779;
        if (file_exists(STREAMS_PATH . $stream_id . '_.pid')) {
            unlink(STREAMS_PATH . $stream_id . '_.pid');
        }
        $stream = array();
        self::$ipTV_db->query('SELECT * FROM `streams` t1 
                               INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 1
                               LEFT JOIN `transcoding_profiles` t4 ON t1.transcode_profile_id = t4.profile_id 
                               WHERE t1.direct_source = 0 AND t1.id = \'%d\'', $stream_id);
        if (self::$ipTV_db->num_rows() <= 0) {
            return false;
        }
        $stream['stream_info'] = self::$ipTV_db->get_row();
        self::$ipTV_db->query('SELECT * FROM `streams_sys` WHERE stream_id  = \'%d\' AND `server_id` = \'%d\'', $stream_id, SERVER_ID);
        if (self::$ipTV_db->num_rows() <= 0) {
            return false;
        }
        $stream['server_info'] = self::$ipTV_db->get_row();
        self::$ipTV_db->query('SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = \'%d\' AND t1.argument_id = t2.id', $stream_id);
        $stream['stream_arguments'] = self::$ipTV_db->get_rows();
        if ($stream['server_info']['on_demand'] == 1) {
            $probesize = $stream['stream_info']['probesize_ondemand'];
            $stream_max_analyze = '10000000';
        } else {
            $stream_max_analyze = abs(intval(ipTV_lib::$settings['stream_max_analyze']));
            $probesize = abs(intval(ipTV_lib::$settings['probesize']));
        }
        $d1c5b35a94aa4152ee37c6cfedfb2ec3 = intval($stream_max_analyze / 1000000) + 7;
        $Fa28e3498375fc4da68f3f818d774249 = "/usr/bin/timeout {$d1c5b35a94aa4152ee37c6cfedfb2ec3}s " . FFPROBE_PATH . " {FETCH_OPTIONS} -probesize {$probesize} -analyzeduration {$stream_max_analyze} {CONCAT} -i \"{STREAM_SOURCE}\" -v quiet -print_format json -show_streams -show_format";
        $be9f906faa527985765b1d8c897fb13a = array();
        if ($stream['server_info']['parent_id'] == 0) {
            $sources = $stream['stream_info']['type_key'] == 'created_live' ? array(CREATED_CHANNELS . $stream_id . '_.list') : json_decode($stream['stream_info']['stream_source'], true);
        } else {
            $sources = array(ipTV_lib::$StreamingServers[$stream['server_info']['parent_id']]['site_url_ip'] . 'streaming/admin_live.php?stream=' . $stream_id . '&password=' . ipTV_lib::$settings['live_streaming_pass'] . '&extension=ts');
        }
        if (count($sources) > 0) {
            if (empty($B71703fbd9f237149967f9ac3c41dc19)) {
                if (ipTV_lib::$settings['priority_backup'] != 1) {
                    $sources = array($B71703fbd9f237149967f9ac3c41dc19);
                } else if (!empty($stream['server_info']['current_source'])) {
                    $k = array_search($stream['server_info']['current_source'], $sources);
                    if ($k !== false) {
                        $index = 0;
                        while ($index <= $k) {
                            $sourceItem = $sources[$index];
                            unset($sources[$index]);
                            array_push($sources, $sourceItem);
                            $index++;
                        }
                        $sources = array_values($sources);
                    }
                }

                $set = $bb1b9dfc97454460e165348212675779 <= RESTART_TAKE_CACHE ? true : false;
                if (!$set) {
                    self::deleteFiles($sources);
                }
                foreach ($sources as $source) {
                    $stream_source = self::ParseStreamURL($source);
                    $server_protocol = strtolower(substr($stream_source, 0, strpos($stream_source, '://')));
                    $be9f906faa527985765b1d8c897fb13a = implode(' ', self::Ea860c1d3851C46D06E64911E3602768($stream['stream_arguments'], $server_protocol, 'fetch'));
                    if ($set && file_exists(STREAMS_PATH . md5($stream_source))) {
                        $StreamInfo = json_decode(file_get_contents(STREAMS_PATH . md5($stream_source)), true);
                        break;
                    }
                    $StreamInfo = json_decode(shell_exec(str_replace(array('{FETCH_OPTIONS}', '{CONCAT}', '{STREAM_SOURCE}'), array($be9f906faa527985765b1d8c897fb13a, $stream['stream_info']['type_key'] == 'created_live' && $stream['server_info']['parent_id'] == 0 ? '-safe 0 -f concat' : '', $stream_source), $Fa28e3498375fc4da68f3f818d774249)), true);
                    if (!empty($StreamInfo)) {
                        break;
                    }
                }
                if (empty($StreamInfo)) {
                    if ($stream['server_info']['stream_status'] == 0 || $stream['server_info']['to_analyze'] == 1 || $stream['server_info']['pid'] != -1) {
                        self::$ipTV_db->query('UPDATE `streams_sys` SET `progress_info` = \'\',`to_analyze` = 0,`pid` = -1,`stream_status` = 1 WHERE `server_id` = \'%d\' AND `stream_id` = \'%d\'', SERVER_ID, $stream_id);
                    }
                    return 0;
                }
                if (!$set) {
                    file_put_contents(STREAMS_PATH . md5($stream_source), json_encode($StreamInfo));
                }
                $StreamInfo = self::ParseCodecs($StreamInfo);
                $external_push = json_decode($stream['stream_info']['external_push'], true);
                $progress = 'http://127.0.0.1:' . ipTV_lib::$StreamingServers[SERVER_ID]['http_broadcast_port'] . "/progress.php?stream_id={$stream_id}";
                if (empty($stream['stream_info']['custom_ffmpeg'])) {
                    $command = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} {GEN_PTS} {READ_NATIVE} -probesize {$probesize} -analyzeduration {$stream_max_analyze} -progress \"{$progress}\" {CONCAT} -i \"{STREAM_SOURCE}\" ";
                    if (($stream['stream_info']['stream_all'] == 1)) {
                        $map = '-map 0 -copy_unknown ';
                    } else if (empty($stream['stream_info']['custom_map'])) {
                        $map = $stream['stream_info']['custom_map'] . ' -copy_unknown ';
                    }
                    if ($stream['stream_info']['type_key'] == 'radio_streams') {
                        $map = '-map 0:a? ';
                    } else {
                        $map = '';
                    }
                    if (($stream['stream_info']['gen_timestamps'] == 1 || empty($server_protocol)) && $stream['stream_info']['type_key'] != 'created_live') {
                        $e9652f3db39531a69b91900690d5d064 = '-fflags +genpts -async 1';
                    } else {
                        $e9652f3db39531a69b91900690d5d064 = '-nofix_dts -start_at_zero -copyts -vsync 0 -correct_ts_overflow 0 -avoid_negative_ts disabled -max_interleave_delta 0';
                    }
                    $read_native = '';
                    if ($stream['server_info']['parent_id'] == 0 && ($stream['stream_info']['read_native'] == 1 or stristr($StreamInfo['container'], 'hls') or empty($server_protocol) or stristr($StreamInfo['container'], 'mp4') or stristr($StreamInfo['container'], 'matroska'))) {
                        $read_native = '-re';
                    }
                    if ($stream['server_info']['parent_id'] == 0 and $stream['stream_info']['enable_transcode'] == 1 and $stream['stream_info']['type_key'] != 'created_live') {
                        if ($stream['stream_info']['transcode_profile_id'] == -1) {
                            $stream['stream_info']['transcode_attributes'] = array_merge(self::EA860c1D3851c46d06E64911E3602768($stream['stream_arguments'], $server_protocol, 'transcode'), json_decode($stream['stream_info']['transcode_attributes'], true));
                        } else {
                            $stream['stream_info']['transcode_attributes'] = json_decode($stream['stream_info']['profile_options'], true);
                        }
                    } else {
                        $stream['stream_info']['transcode_attributes'] = array();
                    }
                    if (!array_key_exists('-acodec', $stream['stream_info']['transcode_attributes'])) {
                        $stream['stream_info']['transcode_attributes']['-acodec'] = 'copy';
                    }
                    if (!array_key_exists('-vcodec', $stream['stream_info']['transcode_attributes'])) {
                        $stream['stream_info']['transcode_attributes']['-vcodec'] = 'copy';
                    }
                    if (!array_key_exists('-scodec', $stream['stream_info']['transcode_attributes'])) {
                        $stream['stream_info']['transcode_attributes']['-scodec'] = 'copy';
                    }
                    $A7c6258649492b26d77c75c60c793409 = array();
                    $A7c6258649492b26d77c75c60c793409['mpegts'][] = '{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time ' . ipTV_lib::$SegmentsSettings['seg_time'] . ' -segment_list_size ' . ipTV_lib::$SegmentsSettings['seg_list_size'] . ' -segment_format_options "mpegts_flags=+initial_discontinuity:mpegts_copyts=1" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list "' . STREAMS_PATH . $stream_id . '_.m3u8" "' . STREAMS_PATH . $stream_id . '_%d.ts" ';
                    if ($stream['stream_info']['rtmp_output'] == 1) {
                        $A7c6258649492b26d77c75c60c793409['flv'][] = '{MAP} {AAC_FILTER} -f flv rtmp://127.0.0.1:' . ipTV_lib::$StreamingServers[$stream['server_info']['server_id']]['rtmp_port'] . "/live/{$stream_id} ";
                    }
                    if (!empty($external_push[SERVER_ID])) {
                        foreach ($external_push[SERVER_ID] as $b202bc9c1c41da94906c398ceb9f3573) {
                            $A7c6258649492b26d77c75c60c793409['flv'][] = "{MAP} {AAC_FILTER} -f flv \"{$b202bc9c1c41da94906c398ceb9f3573}\" ";
                        }
                    }
                    $delay_start_at = 0;
                    if (!($stream['stream_info']['delay_minutes'] > 0 && $stream['server_info']['parent_id'] == 0)) {
                        foreach ($A7c6258649492b26d77c75c60c793409 as $output_key => $f72c3a34155eca511d79ca3671e1063f) {
                            foreach ($f72c3a34155eca511d79ca3671e1063f as $cd7bafd64552e6ca58318f09800cbddd) {
                                $command .= implode(' ', self::f6664c80bde3e9BBe2c12ceb906d5a11($stream['stream_info']['transcode_attributes'])) . ' ';
                                $command .= $cd7bafd64552e6ca58318f09800cbddd;
                            }
                        }
                    } else {
                        $segment_start_number = 0;
                        if (file_exists(DELAY_STREAM . $stream_id . '_.m3u8')) {
                            $file = file(DELAY_STREAM . $stream_id . '_.m3u8');
                            if (stristr($file[count($file) - 1], $stream_id . '_')) {
                                if (preg_match('/\\_(.*?)\\.ts/', $file[count($file) - 1], $matches)) {
                                    $segment_start_number = intval($matches[1]) + 1;
                                }
                            } else {
                                if (preg_match('/\\_(.*?)\\.ts/', $file[count($file) - 2], $matches)) {
                                    $segment_start_number = intval($matches[1]) + 1;
                                }
                            }
                            if (file_exists(DELAY_STREAM . $stream_id . '_.m3u8_old')) {
                                file_put_contents(DELAY_STREAM . $stream_id . '_.m3u8_old', file_get_contents(DELAY_STREAM . $stream_id . '_.m3u8_old') . file_get_contents(DELAY_STREAM . $stream_id . '_.m3u8'));
                                shell_exec('sed -i \'/EXTINF\\|.ts/!d\' ' . DELAY_STREAM . $stream_id . '_.m3u8_old');
                            } else {
                                copy(DELAY_STREAM . $stream_id . '_.m3u8', DELAY_STREAM . $stream_id . '_.m3u8_old');
                            }
                        }
                        $command .= implode(' ', self::f6664C80BDe3E9bbe2c12ceB906D5A11($stream['stream_info']['transcode_attributes'])) . ' ';
                        $command .= '{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time ' . ipTV_lib::$SegmentsSettings['seg_time'] . ' -segment_list_size ' . $stream['stream_info']['delay_minutes'] * 6 . " -segment_start_number {$segment_start_number} -segment_format_options \"mpegts_flags=+initial_discontinuity:mpegts_copyts=1\" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list \"" . DELAY_STREAM . $stream_id . '_.m3u8" "' . DELAY_STREAM . $stream_id . '_%d.ts" ';
                        $delay_minutes = $stream['stream_info']['delay_minutes'] * 60;
                        if ($segment_start_number > 0) {
                            $delay_minutes -= ($segment_start_number - 1) * 10;
                            if ($delay_minutes <= 0) {
                                $delay_minutes = 0;
                            }
                        }
                    }
                    $command .= ' >/dev/null 2>>' . STREAMS_PATH . $stream_id . '.errors & echo $! > ' . STREAMS_PATH . $stream_id . '_.pid';
                    $command = str_replace(array('{INPUT}', '{FETCH_OPTIONS}', '{GEN_PTS}', '{STREAM_SOURCE}', '{MAP}', '{READ_NATIVE}', '{CONCAT}', '{AAC_FILTER}'), array("\"{$stream_source}\"", empty($stream['stream_info']['custom_ffmpeg']) ? $be9f906faa527985765b1d8c897fb13a : '', empty($stream['stream_info']['custom_ffmpeg']) ? $e9652f3db39531a69b91900690d5d064 : '', $stream_source, empty($stream['stream_info']['custom_ffmpeg']) ? $map : '', empty($stream['stream_info']['custom_ffmpeg']) ? $read_native : '', $stream['stream_info']['type_key'] == 'created_live' && $stream['server_info']['parent_id'] == 0 ? '-safe 0 -f concat' : '', !stristr($StreamInfo['container'], 'flv') && $StreamInfo['codecs']['audio']['codec_name'] == 'aac' && $stream['stream_info']['transcode_attributes']['-acodec'] == 'copy' ? '-bsf:a aac_adtstoasc' : ''), $command);
                    shell_exec($command);
                    $pid = $pid = intval(file_get_contents(STREAMS_PATH . $stream_id . '_.pid'));
                    if (SERVER_ID == $stream['stream_info']['tv_archive_server_id']) {
                        shell_exec(PHP_BIN . ' ' . TOOLS_PATH . 'archive.php ' . $stream_id . ' >/dev/null 2>/dev/null & echo $!');
                    }
                    $delay_enabled = $stream['stream_info']['delay_minutes'] > 0 && $stream['server_info']['parent_id'] == 0 ? true : false;
                    $delay_start_at = $delay_enabled ? time() + $delay_minutes : 0;
                    self::$ipTV_db->query('UPDATE `streams_sys` SET `delay_available_at` = \'%d\',`to_analyze` = 0,`stream_started` = \'%d\',`stream_info` = \'%s\',`stream_status` = 0,`pid` = \'%d\',`progress_info` = \'%s\',`current_source` = \'%s\' WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', $delay_start_at, time(), json_encode($StreamInfo), $pid, json_encode(array()), $source, $stream_id, SERVER_ID);
                    $playlist = !$delay_enabled ? STREAMS_PATH . $stream_id . '_.m3u8' : DELAY_STREAM . $stream_id . '_.m3u8';
                    return array('main_pid' => $pid, 'stream_source' => $stream_source, 'delay_enabled' => $delay_enabled, 'parent_id' => $stream['server_info']['parent_id'], 'delay_start_at' => $delay_start_at, 'playlist' => $playlist);
                } else {
                    $stream['stream_info']['transcode_attributes'] = array();
                    $command = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel quiet {$d1006c7cc041221972025137b5112b7d} -progress \"{$progress}\" " . $stream['stream_info']['custom_ffmpeg'];
                }
            }
        }
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
    public static function F6664c80bdE3E9BBe2C12CeB906D5a11($transcode_attributes) {
        $e80cbed8655f14b141bd53699dbbdc10 = array();
        foreach ($transcode_attributes as $k => $attribute) {
            if (isset($attribute['cmd'])) {
                $transcode_attributes[$k] = $attribute = $attribute['cmd'];
            }
            if (preg_match('/-filter_complex "(.*?)"/', $attribute, $matches)) {
                $transcode_attributes[$k] = trim(str_replace($matches[0], '', $transcode_attributes[$k]));
                $e80cbed8655f14b141bd53699dbbdc10[] = $matches[1];
            }
        }
        if (!empty($e80cbed8655f14b141bd53699dbbdc10)) {
            $transcode_attributes[] = '-filter_complex "' . implode(',', $e80cbed8655f14b141bd53699dbbdc10) . '"';
        }
        $B54918193a6b3b39c547eb9486c4c2ff = array();
        foreach ($transcode_attributes as $k => $e7ddd0b219bd2e9b7547185c8bccb6a9) {
            if (is_numeric($k)) {
                $B54918193a6b3b39c547eb9486c4c2ff[] = $e7ddd0b219bd2e9b7547185c8bccb6a9;
            } else {
                $B54918193a6b3b39c547eb9486c4c2ff[] = $k . ' ' . $e7ddd0b219bd2e9b7547185c8bccb6a9;
            }
        }
        $B54918193a6b3b39c547eb9486c4c2ff = array_filter($B54918193a6b3b39c547eb9486c4c2ff);
        uasort($B54918193a6b3b39c547eb9486c4c2ff, array(__CLASS__, 'customOrder'));
        return array_map('trim', array_values(array_filter($B54918193a6b3b39c547eb9486c4c2ff)));
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
