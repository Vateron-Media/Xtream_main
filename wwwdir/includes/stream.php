<?php

class ipTV_stream {
    public static $ipTV_db;
    /**
     * Deletes files based on the provided sources.
     *
     * This function takes an array of file sources and deletes the corresponding files from the STREAMS_PATH directory if they exist.
     *
     * @param array $sources An array of file sources to be deleted.
     * @return void
     */
    static function deleteCache($sources) {
        if (!empty($sources)) {
            foreach ($sources as $source) {
                if (file_exists(STREAMS_PATH . md5($source))) {
                    unlink(STREAMS_PATH . md5($source));
                }
            }
        } else {
            return null;
        }
    }
    /**
     * Transcodes and builds a stream based on the provided stream ID.
     *
     * This function retrieves stream data from the database, transcodes the stream using FFmpeg with specified attributes, creates a new MPEG-TS file, and updates the stream information in the database accordingly.
     *
     * @param int $streamID The ID of the stream to transcode and build.
     * @return int Returns 1 if the stream is successfully transcoded and built, 2 if there are no PIDs for the channel, or 2 if there are no differences in stream sources.
     */
    static function TranscodeBuild($streamID) {
        self::$ipTV_db->query('SELECT * FROM `streams` t1 LEFT JOIN `transcoding_profiles` t3 ON t1.transcode_profile_id = t3.profile_id WHERE t1.`id` = \'%d\'', $streamID);
        $stream = self::$ipTV_db->get_row();
        $stream['cchannel_rsources'] = json_decode($stream['cchannel_rsources'], true);
        $stream['stream_source'] = json_decode($stream['stream_source'], true);
        $stream['pids_create_channel'] = json_decode($stream['pids_create_channel'], true);
        $stream['transcode_attributes'] = json_decode($stream['profile_options'], true);

        // Set default audio and video codecs if not present
        if (!array_key_exists('-acodec', $stream['transcode_attributes'])) {
            $stream['transcode_attributes']['-acodec'] = 'copy';
        }
        if (!array_key_exists('-vcodec', $stream['transcode_attributes'])) {
            $stream['transcode_attributes']['-vcodec'] = 'copy';
        }

        // Construct FFmpeg command
        $ffmpegCommand = FFMPEG_PATH . ' -fflags +genpts -async 1 -y -nostdin -hide_banner -loglevel quiet -i "{INPUT}" ';
        $ffmpegCommand .= implode(' ', self::parseTranscode($stream['transcode_attributes'])) . ' ';
        $ffmpegCommand .= '-strict -2 -mpegts_flags +initial_discontinuity -f mpegts "' . CREATED_CHANNELS . $streamID . '_{INPUT_MD5}.ts" >/dev/null 2>/dev/null & jobs -p';

        $result = array_diff($stream['stream_source'], $stream['cchannel_rsources']);
        $json_string_data = '';

        // Generate JSON string data for stream sources
        foreach ($stream['stream_source'] as $source) {
            $json_string_data .= 'file \'' . CREATED_CHANNELS . $streamID . '_' . md5($source) . '.ts\'';
        }
        $json_string_data = base64_encode($json_string_data);

        if ((!empty($result) || $stream['stream_source'] !== $stream['cchannel_rsources'])) {
            foreach ($result as $source) {
                $stream['pids_create_channel'][] = ipTV_servers::RunCommandServer($stream['created_channel_location'], str_ireplace(array('{INPUT}', '{INPUT_MD5}'), array($source, md5($source)), $ffmpegCommand), 'raw')[$stream['created_channel_location']];
            }
            self::$ipTV_db->query('UPDATE `streams` SET pids_create_channel = \'%s\',`cchannel_rsources` = \'%s\' WHERE `id` = \'%d\'', json_encode($stream['pids_create_channel']), json_encode($stream['stream_source']), $streamID);
            ipTV_servers::RunCommandServer($stream['created_channel_location'], "echo {$json_string_data} | base64 --decode > \"" . CREATED_CHANNELS . $streamID . '_.list"', 'raw');
            return 1;
        } else if (!empty($stream['pids_create_channel'])) {
            foreach ($stream['pids_create_channel'] as $key => $pid) {
                if (!ipTV_servers::PidsChannels($stream['created_channel_location'], $pid, FFMPEG_PATH)) {
                    unset($stream['pids_create_channel'][$key]);
                }
            }
            self::$ipTV_db->query('UPDATE `streams` SET pids_create_channel = \'%s\' WHERE `id` = \'%d\'', json_encode($stream['pids_create_channel']), $streamID);
            return empty($stream['pids_create_channel']) ? 2 : 1;
        }

        return 2;
    }
    /** 
     * Analyze a stream using FFprobe. 
     * 
     * @param string $InputFileUrl The URL of the input file 
     * @param int $serverId The ID of the server 
     * @param array $options Additional options for FFprobe 
     * @param string $dir The directory path 
     * @return array The parsed codecs from the analyzed stream 
     */
    static function analyzeStream(string $InputFileUrl, int $serverId, $options = [], string $dir = '') {
        $streamMaxAnalyze = abs(intval(ipTV_lib::$settings['stream_max_analyze']));
        $streamProbesize = abs(intval(ipTV_lib::$settings['probesize']));
        $timeout = intval($streamMaxAnalyze / 1000000) + 5;
        $command = "{$dir}/usr/bin/timeout {$timeout}s " . FFPROBE_PATH . " -probesize {$streamProbesize} -analyzeduration {$streamMaxAnalyze} " . implode(' ', $options) . " -i \"{$InputFileUrl}\" -v quiet -print_format json -show_streams -show_format";
        $result = ipTV_servers::RunCommandServer($serverId, $command, 'raw', $timeout * 2, $timeout * 2);
        return self::parseFFProbe(json_decode($result[$serverId], true));
    }
    public static function parseFFProbe($data) {
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
    /** 
     * Starts a stream with a specified delay. 
     * 
     * @param int $streamID The unique identifier for the stream. 
     * @param int $stream_delay The boolean value indicating whether to delay the stream. 
     * @return void 
     */
    static function startMonitor(int $streamID, int $stream_delay  = 0) {
        // Define the lock file for the stream
        $stream_lock_file = STREAMS_PATH . $streamID . '.lock';
        // Open the lock file for writing
        $fp = fopen($stream_lock_file, 'a+');
        // Check if file locking was successful
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            // Convert bool to integer
            $stream_delay = intval($stream_delay);
            // Execute the stream monitor script with stream_id and delay
            shell_exec(PHP_BIN . ' ' . TOOLS_PATH . "stream_monitor.php {$streamID} {$stream_delay} >/dev/null 2>/dev/null &");
            // Pause for 300 microseconds
            usleep(300);
            // Release the file lock
            flock($fp, LOCK_UN);
        }
        // Close the file pointer
        fclose($fp);
    }
    static function stopStream($streamID, $reset_stream_sys = false) {
        if (file_exists("/home/xtreamcodes/iptv_xtream_codes/streams/{$streamID}.monitor")) {
            $pid_stream_monitor = intval(file_get_contents("/home/xtreamcodes/iptv_xtream_codes/streams/{$streamID}.monitor"));
            if (self::checkPID($pid_stream_monitor, "XtreamCodes[{$streamID}]")) {
                posix_kill($pid_stream_monitor, 9);
            }
        }
        if (file_exists(STREAMS_PATH . $streamID . '_.pid')) {
            $pid = intval(file_get_contents(STREAMS_PATH . $streamID . '_.pid'));
            if (self::checkPID($pid, "{$streamID}_.m3u8")) {
                posix_kill($pid, 9);
            }
        }
        shell_exec('rm -f ' . STREAMS_PATH . $streamID . '_*');
        if ($reset_stream_sys) {
            shell_exec('rm -f ' . DELAY_PATH . $streamID . '_*');
            self::$ipTV_db->query('UPDATE `streams_sys` SET `bitrate` = NULL,`current_source` = NULL,`to_analyze` = 0,`pid` = NULL,`stream_started` = NULL,`stream_info` = NULL,`stream_status` = 0,`monitor_pid` = NULL WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', $streamID, SERVER_ID);
        }
        ipTV_streaming::updateStream($streamID);
    }
    static function checkPID($pid, $search) {
        if (file_exists('/proc/' . $pid)) {
            $value = trim(file_get_contents("/proc/{$pid}/cmdline"));
            if (stristr($value, $search)) {
                return true;
            }
        }
        return false;
    }
    static function startVODstream($streamID) {
        $stream = array();
        self::$ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 0 LEFT JOIN `transcoding_profiles` t4 ON t1.transcode_profile_id = t4.profile_id WHERE t1.direct_source = 0 AND t1.id = \'%d\'', $streamID);
        if (self::$ipTV_db->num_rows() > 0) {
            $stream['stream_info'] = self::$ipTV_db->get_row();
            $target_container = json_decode($stream['stream_info']['target_container'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $stream['stream_info']['target_container'] = $target_container;
            } else {
                $stream['stream_info']['target_container'] = array($stream['stream_info']['target_container']);
            }
            self::$ipTV_db->query('SELECT * FROM `streams_sys` WHERE stream_id  = \'%d\' AND `server_id` = \'%d\'', $streamID, SERVER_ID);
            if (self::$ipTV_db->num_rows() > 0) {
                $stream['server_info'] = self::$ipTV_db->get_row();
                self::$ipTV_db->query('SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = \'%d\' AND t1.argument_id = t2.id', $streamID);
                $stream['stream_arguments'] = self::$ipTV_db->get_rows();
                list($streamSource) = json_decode($stream['stream_info']['stream_source'], true);
                if (substr($streamSource, 0, 2) == 's:') {
                    $movieSource = explode(':', $streamSource, 3);
                    $movieServerID = $movieSource[1];
                    if ($movieServerID != SERVER_ID) {
                        $moviePath = ipTV_lib::$StreamingServers[$movieServerID]['api_url'] . '&action=getFile&filename=' . urlencode($movieSource[2]);
                    } else {
                        $moviePath = $movieSource[2];
                    }
                    $protocol = null;
                } else {
                    if (substr($streamSource, 0, 1) == '/') {
                        $movieServerID = SERVER_ID;
                        $moviePath = $streamSource;
                        $protocol = null;
                    } else {
                        $protocol = substr($streamSource, 0, strpos($streamSource, '://'));
                        $moviePath = str_replace(' ', '%20', $streamSource);
                        $rFetchOptions = implode(' ', self::getFormattedStreamArguments($stream['stream_arguments'], $protocol, 'fetch'));
                    }
                }
                if ((isset($movieServerID) && $movieServerID == SERVER_ID || file_exists($moviePath)) && $stream['stream_info']['movie_symlink'] == 1) {
                    $rFFMPEG = 'ln -sfn ' . escapeshellarg($moviePath) . ' ' . VOD_PATH . intval($streamID) . '.' . escapeshellcmd(pathinfo($moviePath)['extension']) . ' >/dev/null 2>/dev/null & echo $! > ' . VOD_PATH . intval($streamID) . '_.pid';
                } else {
                    $rSubtitles = json_decode($stream['stream_info']['movie_subtitles'], true);
                    $rSubtitlesImport = '';
                    $rSubtitlesMetadata = '';
                    if (!empty($rSubtitles)) {
                        for ($i = 0; $i < count($rSubtitles['files']); $i++) {
                            $rSubtitleFile = escapeshellarg($rSubtitles['files'][$i]);
                            $rInputCharset = escapeshellarg($rSubtitles['charset'][$i]);
                            if ($rSubtitles['location'] == SERVER_ID) {
                                $rSubtitlesImport .= '-sub_charenc ' . $rInputCharset . ' -i ' . $rSubtitleFile . ' ';
                            } else {
                                $rSubtitlesImport .= '-sub_charenc ' . $rInputCharset . ' -i "' . ipTV_lib::$StreamingServers[$rSubtitles['location']]['api_url'] . '&action=getFile&filename=' . urlencode($rSubtitleFile) . '" ';
                            }
                        }
                        for ($i = 0; $i < count($rSubtitles['files']); $i++) {
                            $rSubtitlesMetadata .= '-map ' . ($i + 1) . ' -metadata:s:s:' . $i . ' title=' . escapeshellcmd($rSubtitles['names'][$i]) . ' -metadata:s:s:' . $i . ' language=' . escapeshellcmd($rSubtitles['names'][$i]) . ' ';
                        }
                    }
                    if ($stream['stream_info']['read_native'] == 1) {
                        $readNative = '-re';
                    } else {
                        $readNative = '';
                    }
                    if ($stream['stream_info']['enable_transcode'] == 1) {
                        if ($stream['stream_info']['transcode_profile_id'] == -1) {
                            $stream['stream_info']['transcode_attributes'] = array_merge(self::getFormattedStreamArguments($stream['stream_arguments'], $protocol, 'transcode'), json_decode($stream['stream_info']['transcode_attributes'], true));
                        } else {
                            $stream['stream_info']['transcode_attributes'] = json_decode($stream['stream_info']['profile_options'], true);
                        }
                    } else {
                        $stream['stream_info']['transcode_attributes'] = array();
                    }
                    $rLogoOptions = (isset($stream['stream_info']['transcode_attributes'][16]) ? $stream['stream_info']['transcode_attributes'][16]['cmd'] : '');
                    $rInputCodec = '';
                    $rFFMPEG = FFMPEG_PATH . ' -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} -fflags +genpts -async 1 {READ_NATIVE} -i {STREAM_SOURCE} {LOGO} ' . $rSubtitlesImport;
                    $map = '-map 0 -copy_unknown ';
                    if (!empty($stream['stream_info']['custom_map'])) {
                        $map = escapeshellcmd($stream['stream_info']['custom_map']) . ' -copy_unknown ';
                    } else {
                        if ($stream['stream_info']['remove_subtitles'] == 1) {
                            $map = '-map 0:a -map 0:v';
                        }
                    }
                    if (!array_key_exists('-acodec', $stream['stream_info']['transcode_attributes'])) {
                        $stream['stream_info']['transcode_attributes']['-acodec'] = 'copy';
                    }
                    if (!array_key_exists('-vcodec', $stream['stream_info']['transcode_attributes'])) {
                        $stream['stream_info']['transcode_attributes']['-vcodec'] = 'copy';
                    }

                    $fileExtensions = array();
                    foreach ($stream['stream_info']['target_container'] as $extension) {
                        $fileExtensions[$extension] = "-movflags +faststart -dn {$map} -ignore_unknown {$rSubtitlesMetadata} " . VOD_PATH . intval($streamID) . "." . $extension . " ";
                    }

                    foreach ($fileExtensions as $extension => $codec) {
                        if ($extension == 'mp4') {
                            $stream['stream_info']['transcode_attributes']['-scodec'] = 'mov_text';
                        } elseif ($extension == 'mkv') {
                            $stream['stream_info']['transcode_attributes']['-scodec'] = 'srt';
                        } else {
                            $stream['stream_info']['transcode_attributes']['-scodec'] = 'copy';
                        }
                        $rFFMPEG .= implode(' ', self::parseTranscode($stream['stream_info']['transcode_attributes'])) . ' ';
                        $rFFMPEG .= $codec;
                    }
                    $rFFMPEG .= ' >/dev/null 2>' . VOD_PATH . intval($streamID) . '.errors & echo $! > ' . VOD_PATH . intval($streamID) . '_.pid';
                    $rFFMPEG = str_replace(array('{INPUT_CODEC}', '{LOGO}', '{FETCH_OPTIONS}', '{STREAM_SOURCE}', '{READ_NATIVE}'), array($rInputCodec, $rLogoOptions, (empty($rFetchOptions) ? '' : $rFetchOptions), escapeshellarg($moviePath), (empty($stream['stream_info']['custom_ffmpeg']) ? $readNative : '')), $rFFMPEG);
                }
                shell_exec($rFFMPEG);
                file_put_contents(VOD_PATH . $streamID . '_.ffmpeg', $rFFMPEG);
                $streamPID = intval(file_get_contents(VOD_PATH . $streamID . '_.pid'));
                self::$ipTV_db->query('UPDATE `streams_sys` SET `to_analyze` = 1,`stream_started` = \'%d\',`stream_status` = 0,`pid` = \'%d\' WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', time(), $streamPID, $streamID, SERVER_ID);
                ipTV_streaming::updateStream($streamID);
                return $streamPID;
            }
            return false;
        }
        return false;
    }
    static function stopVODstream($streamID) {
        if (file_exists(VOD_PATH . $streamID . '_.pid')) {
            $pid = (int) file_get_contents(VOD_PATH . $streamID . '_.pid');
            posix_kill($pid, 9);
        }
        shell_exec('rm -f ' . VOD_PATH . $streamID . '.*');
        self::$ipTV_db->query('UPDATE `streams_sys` SET `bitrate` = NULL,`current_source` = NULL,`to_analyze` = 0,`pid` = NULL,`stream_started` = NULL,`stream_info` = NULL,`stream_status` = 0 WHERE `stream_id` = \'%d\' AND `server_id` = \'%d\'', $streamID, SERVER_ID);
    }
    static function startStream(int $streamID, &$streamStatusCounter2, $streamUrl = null) {
        ++$streamStatusCounter2;
        if (file_exists(STREAMS_PATH . $streamID . '_.pid')) {
            unlink(STREAMS_PATH . $streamID . '_.pid');
        }
        $stream = array();
        self::$ipTV_db->query("SELECT * FROM `streams` t1
                               INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 1
                               LEFT JOIN `transcoding_profiles` t4 ON t1.transcode_profile_id = t4.profile_id 
                               WHERE t1.direct_source = 0 AND t1.id = '%d'", $streamID);
        if (self::$ipTV_db->num_rows() <= 0) {
            return false;
        }
        $stream['stream_info'] = self::$ipTV_db->get_row();
        self::$ipTV_db->query('SELECT * FROM `streams_sys` WHERE stream_id  = \'%d\' AND `server_id` = \'%d\'', $streamID, SERVER_ID);
        if (self::$ipTV_db->num_rows() <= 0) {
            return false;
        }
        $stream['server_info'] = self::$ipTV_db->get_row();
        self::$ipTV_db->query('SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = \'%d\' AND t1.argument_id = t2.id', $streamID);
        $stream['stream_arguments'] = self::$ipTV_db->get_rows();
        if ($stream['server_info']['on_demand'] == 1) {
            $stream_probesize = $stream['stream_info']['probesize_ondemand'];
            $streamMaxAnalyze = '10000000';
        } else {
            $streamMaxAnalyze = abs(intval(ipTV_lib::$settings['stream_max_analyze']));
            $stream_probesize = abs(intval(ipTV_lib::$settings['probesize']));
        }
        $streamTimeout = intval($streamMaxAnalyze / 1000000) + 7;
        $Fa28e3498375fc4da68f3f818d774249 = "/usr/bin/timeout {$streamTimeout}s " . FFPROBE_PATH . " {FETCH_OPTIONS} -probesize {$stream_probesize} -analyzeduration {$streamMaxAnalyze} {CONCAT} -i \"{STREAM_SOURCE}\" -v quiet -print_format json -show_streams -show_format";
        $formattedArguments = array();
        if ($stream["server_info"]["parent_id"] == 0) {
            $streamUrlArr = $stream["stream_info"]["type_key"] == "created_live" ? array(CREATED_CHANNELS . $streamID . "_.list") : json_decode($stream["stream_info"]["stream_source"], true);
        } else {
            $streamUrlArr = array(ipTV_lib::$StreamingServers[$stream['server_info']['parent_id']]['site_url_ip'] . 'streaming/admin_live.php?stream=' . $streamID . '&password=' . ipTV_lib::$settings['live_streaming_pass'] . '&extension=ts');
        }

        if (count($streamUrlArr) > 0) {
            if (ipTV_lib::$settings["priority_backup"] != 1) {
                if (!empty($stream['server_info']['current_source'])) {
                    $currentSourceIndex = array_search($stream['server_info']['current_source'], $streamUrlArr);
                    if ($currentSourceIndex !== false) {
                        $streamUrlKey = 0;
                        while ($streamUrlKey <= $currentSourceIndex) {
                            $Ad110d626a9e62f0778a8f19383a0613 = $streamUrlArr[$streamUrlKey];
                            unset($streamUrlArr[$streamUrlKey]);
                            array_push($streamUrlArr, $Ad110d626a9e62f0778a8f19383a0613);
                            $streamUrlKey++;
                        }
                        $streamUrlArr = array_values($streamUrlArr);
                    }
                }
            }
        } elseif (!empty($streamUrl)) {
            $streamUrlArr = array($streamUrl);
        }
        $streamStatusCounter1 = $streamStatusCounter2 <= RESTART_TAKE_CACHE ? true : false;
        if (!$streamStatusCounter1) {
            self::deleteCache($streamUrlArr);
        }
        foreach ($streamUrlArr as $streamUrl) {
            $ParseStreamUrl = self::parseStreamURL($streamUrl);
            $streamProtocol = strtolower(substr($ParseStreamUrl, 0, strpos($ParseStreamUrl, '://')));
            $formattedArguments = implode(' ', self::getFormattedStreamArguments($stream['stream_arguments'], $streamProtocol, 'fetch'));
            if ($streamStatusCounter1 && file_exists(STREAMS_PATH . md5($ParseStreamUrl))) {
                $streamData = json_decode(file_get_contents(STREAMS_PATH . md5($ParseStreamUrl)), true);
                break;
            }
            $streamData = json_decode(shell_exec(str_replace(array('{FETCH_OPTIONS}', '{CONCAT}', '{STREAM_SOURCE}'), array($formattedArguments, $stream['stream_info']['type_key'] == 'created_live' && $stream['server_info']['parent_id'] == 0 ? '-safe 0 -f concat' : '', $ParseStreamUrl), $Fa28e3498375fc4da68f3f818d774249)), true);
            if (!empty($streamData)) {
                break;
            }
        }
        if (empty($streamData)) {
            if ($stream["server_info"]["stream_status"] == 0 || $stream["server_info"]["to_analyze"] == 1 || $stream["server_info"]["pid"] != -1) {
                self::$ipTV_db->query("UPDATE `streams_sys` SET `progress_info` = '',`to_analyze` = 0,`pid` = -1,`stream_status` = 1 WHERE `server_id` = '%d' AND `stream_id` = '%d'", SERVER_ID, $streamID);
            }
            return 0;
        }
        if (!$streamStatusCounter1) {
            file_put_contents(STREAMS_PATH . md5($ParseStreamUrl), json_encode($streamData));
        }
        $streamData = self::parseFFProbe($streamData);
        $stream_external_push = json_decode($stream["stream_info"]["external_push"], true);
        $streamProgressUrl = "http://127.0.0.1:" . ipTV_lib::$StreamingServers[SERVER_ID]["http_broadcast_port"] . "/progress.php?stream_id={$streamID}";
        if (empty($stream["stream_info"]["custom_ffmpeg"])) {
            $ffmpegRunCommand = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} {GEN_PTS} {READ_NATIVE} -probesize {$stream_probesize} -analyzeduration {$streamMaxAnalyze} -progress \"{$streamProgressUrl}\" {CONCAT} -i \"{STREAM_SOURCE}\" ";

            // set map option ffmpeg
            $ffmpegMapOptions = '';
            if ($stream["stream_info"]["stream_all"] == 1) {
                $ffmpegMapOptions = "-map 0 -copy_unknown ";
            } elseif (!empty($stream["stream_info"]["custom_map"])) {
                $ffmpegMapOptions = $stream["stream_info"]["custom_map"] . " -copy_unknown ";
            } elseif ($stream["stream_info"]["type_key"] == "radio_streams") {
                $ffmpegMapOptions = "-map 0:a? ";
            }

            // set timestamps options ffmpeg
            if (($stream["stream_info"]["gen_timestamps"] == 1 || empty($streamProtocol)) && $stream["stream_info"]["type_key"] != "created_live") {
                $ffmpegTimestampsOptions = "-fflags +genpts -async 1";
            } else {
                $ffmpegTimestampsOptions = "-nofix_dts -start_at_zero -copyts -vsync 0 -correct_ts_overflow 0 -avoid_negative_ts disabled -max_interleave_delta 0";
            }

            $ffmpegNativeOptions = '';
            if ($stream["server_info"]["parent_id"] == 0 && ($stream["stream_info"]["read_native"] == 1 or stristr($streamData["container"], "hls") or empty($streamProtocol) or stristr($streamData["container"], "mp4") or stristr($streamData["container"], "matroska"))) {
                $ffmpegNativeOptions = "-re";
            }

            if ($stream["server_info"]["parent_id"] == 0 and $stream["stream_info"]["enable_transcode"] == 1 and $stream["stream_info"]["type_key"] != "created_live") {
                if ($stream["stream_info"]["transcode_profile_id"] == -1) {
                    $stream["stream_info"]["transcode_attributes"] = array_merge(self::getFormattedStreamArguments($stream["stream_arguments"], $streamProtocol, "transcode"), json_decode($stream["stream_info"]["transcode_attributes"], true));
                } else {
                    $stream["stream_info"]["transcode_attributes"] = json_decode($stream["stream_info"]["profile_options"], true);
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
        } else {
            $stream["stream_info"]["transcode_attributes"] = array();
            $d1006c7cc041221972025137b5112b7d = ""; //заглушка, ее недолжно быть переменой просто нету
            $ffmpegRunCommand = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel quiet {$d1006c7cc041221972025137b5112b7d} -progress \"{$streamProgressUrl}\" " . $stream["stream_info"]["custom_ffmpeg"];
        }

        $ffmpegOutputFormats = array();
        $ffmpegOutputFormats["mpegts"][] = "{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time " . ipTV_lib::$SegmentsSettings["seg_time"] . " -segment_list_size " . ipTV_lib::$SegmentsSettings["seg_list_size"] . " -segment_format_options \"mpegts_flags=+initial_discontinuity:mpegts_copyts=1\" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list \"" . STREAMS_PATH . $streamID . "_.m3u8\" \"" . STREAMS_PATH . $streamID . "_%d.ts\" ";
        if ($stream['stream_info']['rtmp_output'] == 1) {
            $ffmpegOutputFormats['flv'][] = '{MAP} {AAC_FILTER} -f flv rtmp://127.0.0.1:' . ipTV_lib::$StreamingServers[$stream['server_info']['server_id']]['rtmp_port'] . '/live/{$streamID} ';
        }
        if (!empty($stream_external_push[SERVER_ID])) {
            foreach ($stream_external_push[SERVER_ID] as $b202bc9c1c41da94906c398ceb9f3573) {
                $ffmpegOutputFormats["flv"][] = "{MAP} {AAC_FILTER} -f flv \"{$b202bc9c1c41da94906c398ceb9f3573}\" ";
            }
        }
        $delay_start_at = 0;

        if (!($stream["stream_info"]["delay_minutes"] > 0 && $stream["server_info"]["parent_id"] == 0)) {
            foreach ($ffmpegOutputFormats as $f72c3a34155eca511d79ca3671e1063f) {
                foreach ($f72c3a34155eca511d79ca3671e1063f as $cd7bafd64552e6ca58318f09800cbddd) {
                    $ffmpegRunCommand .= implode(" ", self::parseTranscode($stream["stream_info"]["transcode_attributes"])) . " ";
                    $ffmpegRunCommand .= $cd7bafd64552e6ca58318f09800cbddd;
                }
            }
        } else {
            $ccac9556cf5f7f83df650c022d673042 = 0;
            if (file_exists(DELAY_PATH . $streamID . "_.m3u8")) {
                $Ca434bcc380e9dbd2a3a588f6c32d84f = file(DELAY_PATH . $streamID . "_.m3u8");
                if (stristr($Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 1], $streamID . "_")) {
                    if (preg_match("/\\_(.*?)\\.ts/", $Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 1], $ae37877cee3bc97c8cfa6ec5843993ed)) {
                        $ccac9556cf5f7f83df650c022d673042 = intval($ae37877cee3bc97c8cfa6ec5843993ed[1]) + 1;
                    }
                } else {
                    if (preg_match("/\\_(.*?)\\.ts/", $Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 2], $ae37877cee3bc97c8cfa6ec5843993ed)) {
                        $ccac9556cf5f7f83df650c022d673042 = intval($ae37877cee3bc97c8cfa6ec5843993ed[1]) + 1;
                    }
                }
                if (file_exists(DELAY_PATH . $streamID . "_.m3u8_old")) {
                    file_put_contents(DELAY_PATH . $streamID . "_.m3u8_old", file_get_contents(DELAY_PATH . $streamID . "_.m3u8_old") . file_get_contents(DELAY_PATH . $streamID . "_.m3u8"));
                    shell_exec("sed -i '/EXTINF\\|.ts/!d' DELAY_PATH" . $streamID . "_.m3u8_old");
                } else {
                    copy(DELAY_PATH . $streamID . "_.m3u8", DELAY_PATH . $streamID . "_.m3u8_old");
                }
            }
            $ffmpegRunCommand .= implode(" ", self::parseTranscode($stream["stream_info"]["transcode_attributes"])) . " ";
            $ffmpegRunCommand .= "{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time " . ipTV_lib::$SegmentsSettings["seg_time"] . " -segment_list_size " . $stream["stream_info"]["delay_minutes"] * 6 . " -segment_start_number {$ccac9556cf5f7f83df650c022d673042} -segment_format_options \"mpegts_flags=+initial_discontinuity:mpegts_copyts=1\" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list \"" . DELAY_PATH . $streamID . "_.m3u8\" \"" . DELAY_PATH . $streamID . "_%d.ts\" ";
            $Dedb93a1e8822879d8790c1f2fc7d6f1 = $stream["stream_info"]["delay_minutes"] * 60;
            if ($ccac9556cf5f7f83df650c022d673042 > 0) {
                $Dedb93a1e8822879d8790c1f2fc7d6f1 -= ($ccac9556cf5f7f83df650c022d673042 - 1) * 10;
                if ($Dedb93a1e8822879d8790c1f2fc7d6f1 <= 0) {
                    $Dedb93a1e8822879d8790c1f2fc7d6f1 = 0;
                }
            }
        }

        $ffmpegRunCommand .= " >/dev/null 2>>" . STREAMS_PATH . $streamID . ".errors & echo \$! > " . STREAMS_PATH . $streamID . "_.pid";
        $ffmpegRunCommand = str_replace(array("{INPUT}", "{FETCH_OPTIONS}", "{GEN_PTS}", "{STREAM_SOURCE}", "{MAP}", "{READ_NATIVE}", "{CONCAT}", "{AAC_FILTER}"), array("\"{$ParseStreamUrl}\"", empty($stream["stream_info"]["custom_ffmpeg"]) ? $formattedArguments : '', empty($stream["stream_info"]["custom_ffmpeg"]) ? $ffmpegTimestampsOptions : '', $ParseStreamUrl, empty($stream["stream_info"]["custom_ffmpeg"]) ? $ffmpegMapOptions : '', empty($stream["stream_info"]["custom_ffmpeg"]) ? $ffmpegNativeOptions : '', $stream["stream_info"]["type_key"] == "created_live" && $stream["server_info"]["parent_id"] == 0 ? "-safe 0 -f concat" : '', !stristr($streamData["container"], "flv") && $streamData["codecs"]["audio"]["codec_name"] == "aac" && $stream["stream_info"]["transcode_attributes"]["-acodec"] == "copy" ? "-bsf:a aac_adtstoasc" : ''), $ffmpegRunCommand);

        shell_exec($ffmpegRunCommand);

        $streamPid = intval(file_get_contents(STREAMS_PATH . $streamID . "_.pid"));
        if (SERVER_ID == $stream["stream_info"]["tv_archive_server_id"]) {
            shell_exec(PHP_BIN . ' ' . TOOLS_PATH . "archive.php " . $streamID . " >/dev/null 2>/dev/null & echo \$!");
        }
        $Dac1208baefb5d684938829a3a0e0bc6 = $stream["stream_info"]["delay_minutes"] > 0 && $stream["server_info"]["parent_id"] == 0 ? true : false;
        $delay_start_at = $Dac1208baefb5d684938829a3a0e0bc6 ? time() + $Dedb93a1e8822879d8790c1f2fc7d6f1 : 0;
        self::$ipTV_db->query("UPDATE `streams_sys` SET `delay_available_at` = '%d',`to_analyze` = 0,`stream_started` = '%d',`stream_info` = '%s',`stream_status` = 0,`pid` = '%d',`progress_info` = '%s',`current_source` = '%s' WHERE `stream_id` = '%d' AND `server_id` = '%d'", $delay_start_at, time(), json_encode($streamData), $streamPid, json_encode(array()), $streamUrl, $streamID, SERVER_ID);
        ipTV_streaming::updateStream($streamID);
        $streamPlaylist = !$Dac1208baefb5d684938829a3a0e0bc6 ? STREAMS_PATH . $streamID . "_.m3u8" : DELAY_PATH . $streamID . "_.m3u8";
        return array("main_pid" => $streamPid, "stream_source" => $ParseStreamUrl, "delay_enabled" => $Dac1208baefb5d684938829a3a0e0bc6, "parent_id" => $stream["server_info"]["parent_id"], "delay_start_at" => $delay_start_at, "playlist" => $streamPlaylist);
    }
    public static function customOrder($a, $b) {
        if (substr($a, 0, 3) == '-i ') {
            return -1;
        }
        return 1;
    }
    /**
     * Generates an array of stream arguments based on the provided stream arguments and server protocol.
     *
     * This method processes the input `$stream_arguments` array, filtering the arguments based on the
     * specified `$type` and `$server_protocol`. It then constructs an array of formatted arguments
     * that can be used in FFmpeg commands or other stream-related operations.
     *
     * @param array $stream_arguments An array of stream arguments, where each element is an associative
     *                                array with keys such as 'argument_cat', 'argument_wprotocol',
     *                                'argument_type', 'argument_cmd', and 'value'.
     * @param string $server_protocol The server protocol to be used for filtering the stream arguments.
     * @param string $type The type of stream arguments to be included in the output.
     * @return array An array of formatted stream arguments, ready for use in FFmpeg commands or other
     *               stream-related operations.
     */
    public static function getFormattedStreamArguments(array $stream_arguments, string $server_protocol, string $type) {
        $formattedArguments = [];

        if (!empty($stream_arguments)) {
            foreach ($stream_arguments as $argument) {
                if ($argument['argument_cat'] != $type) {
                    continue;
                }
                if (!is_null($argument['argument_wprotocol']) && !stristr($server_protocol, $argument['argument_wprotocol']) && !is_null($server_protocol)) {
                    continue;
                }
                if ($argument['argument_type'] == 'text') {
                    $formattedArguments[] = sprintf($argument['argument_cmd'], $argument['value']);
                } else {
                    $formattedArguments[] = $argument['argument_cmd'];
                }
            }
        }

        return $formattedArguments;
    }
    /**
     * Formats the transcode attributes for use in FFmpeg commands.
     *
     * This method processes the input `$transcode_attributes` array, which may contain
     * both individual attributes and complex filter expressions. It extracts the
     * filter expressions, formats the attributes, and returns an array of formatted
     * attributes ready for use in FFmpeg commands.
     *
     * @param array $transcode_attributes An array of transcode attributes, which may
     *                                   include individual attributes and/or complex
     *                                   filter expressions.
     * @return array An array of formatted transcode attributes, ready for use in
     *               FFmpeg commands.
     */
    public static function parseTranscode(array $transcode_attributes) {
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
    /**
     * Parses the stream URL and modifies it based on the server protocol.
     *
     * This function takes a stream URL as input, checks the server protocol, and processes the URL accordingly.
     * For RTMP protocol, it appends ' live=1 timeout=10' to the URL.
     * For HTTP protocol, it extracts the host from the URL and processes specific hosts to get the best stream URL.
     *
     * @param string $url The stream URL to be parsed.
     * @return string The modified stream URL after parsing.
     */
    public static function parseStreamURL($URL) {
        $protocol = strtolower(substr($URL, 0, 4));
        if ($protocol == 'rtmp') {
            if (stristr($URL, '$OPT')) {
                $Pattern = 'rtmp://$OPT:rtmp-raw=';
                $URL = trim(substr($URL, stripos($URL, $Pattern) + strlen($Pattern)));
            }
            $URL .= ' live=1 timeout=10';
        } else {
            if ($protocol == 'http') {
                $Platforms = array('livestream.com', 'ustream.tv', 'twitch.tv', 'vimeo.com', 'facebook.com', 'dailymotion.com', 'cnn.com', 'edition.cnn.com', 'youtube.com', 'youtu.be');
                $Host = str_ireplace('www.', '', parse_url($URL, PHP_URL_HOST));
                if (in_array($Host, $Platforms)) {
                    $URLs = trim(shell_exec(YOUTUBE_PATH . ' ' . escapeshellarg($URL) . ' -q --get-url --skip-download -f best'));
                    list($URL) = explode("\n", $URLs);
                }
            }
        }
        return $URL;
    }
}
