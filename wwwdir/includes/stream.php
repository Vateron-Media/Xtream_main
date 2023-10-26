<?php

class E3cf480c172e8B47FE10857c2A5AEb48 {
    public static $ipTV_db;
    static function aD09D99Ce37614036D5a527595d569d9($A733a5416ffab6ff47547550f3f9f641) {
        if (!empty($A733a5416ffab6ff47547550f3f9f641)) {
            foreach ($A733a5416ffab6ff47547550f3f9f641 as $F3803fa85b38b65447e6d438f8e9176a) {
                if (!file_exists(STREAMS_PATH . md5($F3803fa85b38b65447e6d438f8e9176a))) {
                    goto fafad8b3428f2097a65fc4a56ab98c6d;
                }
                unlink(STREAMS_PATH . md5($F3803fa85b38b65447e6d438f8e9176a));
                fafad8b3428f2097a65fc4a56ab98c6d:
            }
            return;
        }
        return;
    }
    static function EeeD2f36fa093b45bC2D622ed0231684($E62a309a7fc72c8c292c032fe0fd23ab) {
        self::$ipTV_db->query("\n                SELECT * FROM `streams` t1 \n                LEFT JOIN `transcoding_profiles` t3 ON t1.transcode_profile_id = t3.profile_id\n                WHERE t1.`id` = '%d'", $E62a309a7fc72c8c292c032fe0fd23ab);
        $a5fd23cf4a741b0e9eb35bb60849c401 = self::$ipTV_db->f1Ed191D78470660eDFF4a007696Bc1F();
        $a5fd23cf4a741b0e9eb35bb60849c401["cchannel_rsources"] = json_decode($a5fd23cf4a741b0e9eb35bb60849c401["cchannel_rsources"], true);
        $a5fd23cf4a741b0e9eb35bb60849c401["stream_source"] = json_decode($a5fd23cf4a741b0e9eb35bb60849c401["stream_source"], true);
        $a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"] = json_decode($a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"], true);
        $a5fd23cf4a741b0e9eb35bb60849c401["transcode_attributes"] = json_decode($a5fd23cf4a741b0e9eb35bb60849c401["profile_options"], true);
        if (array_key_exists("-acodec", $a5fd23cf4a741b0e9eb35bb60849c401["transcode_attributes"])) {
            goto dab3d626d064d6894a8a9f942772288a;
        }
        $a5fd23cf4a741b0e9eb35bb60849c401["transcode_attributes"]["-acodec"] = "copy";
        dab3d626d064d6894a8a9f942772288a:
        if (array_key_exists("-vcodec", $a5fd23cf4a741b0e9eb35bb60849c401["transcode_attributes"])) {
            goto c27b757dc647836cd0df65d4e18578cf;
        }
        $a5fd23cf4a741b0e9eb35bb60849c401["transcode_attributes"]["-vcodec"] = "copy";
        c27b757dc647836cd0df65d4e18578cf:
        $bf1324315496910e8d570f42b29cf7bb = "FFMPEG_PATH -fflags +genpts -async 1 -y -nostdin -hide_banner -loglevel quiet -i \"{INPUT}\" ";
        $bf1324315496910e8d570f42b29cf7bb .= implode(" ", self::F6664C80BDe3e9BbE2C12ceB906D5A11($a5fd23cf4a741b0e9eb35bb60849c401["transcode_attributes"])) . " ";
        $bf1324315496910e8d570f42b29cf7bb .= "-strict -2 -mpegts_flags +initial_discontinuity -f mpegts \"CREATED_CHANNELS" . $E62a309a7fc72c8c292c032fe0fd23ab . "_{INPUT_MD5}.ts\" >/dev/null 2>/dev/null & jobs -p";
        $Ff86147ddc7b314b8090bc97616612a7 = array_diff($a5fd23cf4a741b0e9eb35bb60849c401["stream_source"], $a5fd23cf4a741b0e9eb35bb60849c401["cchannel_rsources"]);
        $F7385aab8f8489bee4d3920b1e33eac7 = '';
        foreach ($a5fd23cf4a741b0e9eb35bb60849c401["stream_source"] as $b593cd195ca5474bf633cc7331d67088) {
            $F7385aab8f8489bee4d3920b1e33eac7 .= "file 'CREATED_CHANNELS" . $E62a309a7fc72c8c292c032fe0fd23ab . "_" . md5($b593cd195ca5474bf633cc7331d67088) . ".ts'\n";
        }
        $F7385aab8f8489bee4d3920b1e33eac7 = base64_encode($F7385aab8f8489bee4d3920b1e33eac7);
        if (!empty($Ff86147ddc7b314b8090bc97616612a7) || $a5fd23cf4a741b0e9eb35bb60849c401["stream_source"] !== $a5fd23cf4a741b0e9eb35bb60849c401["cchannel_rsources"]) {
            foreach ($Ff86147ddc7b314b8090bc97616612a7 as $b593cd195ca5474bf633cc7331d67088) {
                $a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"][] = a7785208d901bEA02B65446067cFd0b3::F320b6a3920944D8A18D7949c8abaCe4($a5fd23cf4a741b0e9eb35bb60849c401["created_channel_location"], str_ireplace(array("{INPUT}", "{INPUT_MD5}"), array($b593cd195ca5474bf633cc7331d67088, md5($b593cd195ca5474bf633cc7331d67088)), $bf1324315496910e8d570f42b29cf7bb), "raw")[$a5fd23cf4a741b0e9eb35bb60849c401["created_channel_location"]];
            }
            self::$ipTV_db->query("UPDATE `streams` SET pids_create_channel = '%s',`cchannel_rsources` = '%s' WHERE `id` = '%d'", json_encode($a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"]), json_encode($a5fd23cf4a741b0e9eb35bb60849c401["stream_source"]), $E62a309a7fc72c8c292c032fe0fd23ab);
            a7785208d901Bea02B65446067Cfd0b3::f320b6A3920944D8a18d7949C8abaCe4($a5fd23cf4a741b0e9eb35bb60849c401["created_channel_location"], "echo {$F7385aab8f8489bee4d3920b1e33eac7} | base64 --decode > \"" . CREATED_CHANNELS . $E62a309a7fc72c8c292c032fe0fd23ab . "_.list\"", "raw");
            return 1;
        }
        if (!empty($a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"])) {
            foreach ($a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"] as $E7cca48cfca85fc445419a32d7d8f973 => $pid) {
                if (a7785208d901Bea02b65446067cfd0B3::eD79a31441202a0d242A25777F316FaC($a5fd23cf4a741b0e9eb35bb60849c401["created_channel_location"], $pid, FFMPEG_PATH)) {
                    goto e08ff4356534260be33d94d4a4136d32;
                }
                unset($a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"][$E7cca48cfca85fc445419a32d7d8f973]);
                e08ff4356534260be33d94d4a4136d32:
            }
            self::$ipTV_db->query("UPDATE `streams` SET pids_create_channel = '%s' WHERE `id` = '%d'", json_encode($a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"]), $E62a309a7fc72c8c292c032fe0fd23ab);
            return empty($a5fd23cf4a741b0e9eb35bb60849c401["pids_create_channel"]) ? 2 : 1;
        }
        d1cbb96c05439cbb179d4ff78e029ddb:
        return 2;
    }
    static function E0A1164567005185e0818F081674E240($C0379dd6700deb6b1021ed6026f648b9, $Aa894918d6f628c53ace2682189e44d5, $f84c1c6145bb73410b3ea7c0f8b4a9f3 = array(), $A7da0ef4553f5ea253d3907a7c9ef7f0 = '') {
        $C359d5e5ab36c7a88fca0754166e7996 = abs(intval(a78bf8d35765bE2408C50712CE7A43Ad::$settings["stream_max_analyze"]));
        $E1be7e0ba659254273dc1475ae9679e0 = abs(intval(a78bf8D35765bE2408c50712CE7A43Ad::$settings["probesize"]));
        $E2862eaf3f4716fdadef0a008a343507 = intval($C359d5e5ab36c7a88fca0754166e7996 / 1000000) + 5;
        $Fd219183e9990a8c0beae39264c6d004 = "{$A7da0ef4553f5ea253d3907a7c9ef7f0}/usr/bin/timeout {$E2862eaf3f4716fdadef0a008a343507}s " . FFPROBE_PATH . " -probesize {$E1be7e0ba659254273dc1475ae9679e0} -analyzeduration {$C359d5e5ab36c7a88fca0754166e7996} " . implode(" ", $f84c1c6145bb73410b3ea7c0f8b4a9f3) . " -i \"{$C0379dd6700deb6b1021ed6026f648b9}\" -v quiet -print_format json -show_streams -show_format";
        $C2eef5835abdc711ef2e0b2a24dc4e46 = a7785208D901BEA02b65446067CFD0b3::F320b6A3920944d8A18D7949c8abAce4($Aa894918d6f628c53ace2682189e44d5, $Fd219183e9990a8c0beae39264c6d004, "raw", $E2862eaf3f4716fdadef0a008a343507 * 2, $E2862eaf3f4716fdadef0a008a343507 * 2);
        return self::cCBD051C8a19a02Dc5B6dB256Ae31c07(json_decode($C2eef5835abdc711ef2e0b2a24dc4e46[$Aa894918d6f628c53ace2682189e44d5], true));
    }
    public static function CcBd051c8a19a02dc5B6dB256AE31c07($d8c887d4a07ddc3992dca7f1d440e7de) {
        if (empty($d8c887d4a07ddc3992dca7f1d440e7de)) {
            return false;
        }
        if (empty($d8c887d4a07ddc3992dca7f1d440e7de["codecs"])) {
            $output = array();
            $output["codecs"]["video"] = '';
            $output["codecs"]["audio"] = '';
            $output["container"] = $d8c887d4a07ddc3992dca7f1d440e7de["format"]["format_name"];
            $output["filename"] = $d8c887d4a07ddc3992dca7f1d440e7de["format"]["filename"];
            $output["bitrate"] = !empty($d8c887d4a07ddc3992dca7f1d440e7de["format"]["bit_rate"]) ? $d8c887d4a07ddc3992dca7f1d440e7de["format"]["bit_rate"] : null;
            $output["of_duration"] = !empty($d8c887d4a07ddc3992dca7f1d440e7de["format"]["duration"]) ? $d8c887d4a07ddc3992dca7f1d440e7de["format"]["duration"] : "N/A";
            $output["duration"] = !empty($d8c887d4a07ddc3992dca7f1d440e7de["format"]["duration"]) ? gmdate("H:i:s", intval($d8c887d4a07ddc3992dca7f1d440e7de["format"]["duration"])) : "N/A";
            foreach ($d8c887d4a07ddc3992dca7f1d440e7de["streams"] as $E91d1cd26e7223a0f44a617b8ab51d10) {
                if (isset($E91d1cd26e7223a0f44a617b8ab51d10["codec_type"])) {
                    if (!($E91d1cd26e7223a0f44a617b8ab51d10["codec_type"] != "audio" && $E91d1cd26e7223a0f44a617b8ab51d10["codec_type"] != "video")) {
                        $output["codecs"][$E91d1cd26e7223a0f44a617b8ab51d10["codec_type"]] = $E91d1cd26e7223a0f44a617b8ab51d10;
                        goto D5d73a4d121486dbd1a44308c584a724;
                    }
                    goto B118807d702f1626af252cf6e1925be3;
                }
                D5d73a4d121486dbd1a44308c584a724:
                B118807d702f1626af252cf6e1925be3:
            }
            return $output;
        }
        return $d8c887d4a07ddc3992dca7f1d440e7de;
    }
    static function C27C26b9eD331706a4c3f0292142fB52($streamId, $a10d30316266ccc4dd75c9b1ce4dd026 = false) {
        if (!file_exists("/home/xtreamcodes/iptv_xtream_codes/streams/{$streamId}.monitor")) {
            goto e00621fb8f5c80598aaf44240ec72bd5;
        }
        $e9d30118d498945b35ee33aa90ed9822 = intval(file_get_contents("/home/xtreamcodes/iptv_xtream_codes/streams/{$streamId}.monitor"));
        if (!self::F198E55FC8231996C50ee056Ac4226E0($e9d30118d498945b35ee33aa90ed9822, "XtreamCodes[{$streamId}]")) {
            goto e3d62cdd5b25b8bf9026c5f01c808a66;
        }
        posix_kill($e9d30118d498945b35ee33aa90ed9822, 9);
        e3d62cdd5b25b8bf9026c5f01c808a66:
        e00621fb8f5c80598aaf44240ec72bd5:
        if (!file_exists(STREAMS_PATH . $streamId . "_.pid")) {
            goto ab07046a2ff99fba0247cc147c896c9d;
        }
        $pid = intval(file_get_contents(STREAMS_PATH . $streamId . "_.pid"));
        if (!self::F198E55fC8231996C50eE056aC4226e0($pid, "{$streamId}_.m3u8")) {
            goto A3f3cddff7eaaadabaf56b091b806db2;
        }
        posix_kill($pid, 9);
        A3f3cddff7eaaadabaf56b091b806db2:
        ab07046a2ff99fba0247cc147c896c9d:
        shell_exec("rm -f STREAMS_PATH" . $streamId . "_*");
        if (!$a10d30316266ccc4dd75c9b1ce4dd026) {
            goto e648ce21c008f1118ce034834400d471;
        }
        shell_exec("rm -f DELAY_STREAM" . $streamId . "_*");
        self::$ipTV_db->query("UPDATE `streams_sys` SET `bitrate` = NULL,`current_source` = NULL,`to_analyze` = 0,`pid` = NULL,`stream_started` = NULL,`stream_info` = NULL,`stream_status` = 0,`monitor_pid` = NULL WHERE `stream_id` = '%d' AND `server_id` = '%d'", $streamId, SERVER_ID);
        e648ce21c008f1118ce034834400d471:
    }
    static function F198e55Fc8231996C50eE056ac4226e0($pid, $Afd5f79d62d4622597818545a5cf00d1) {
        if (!file_exists("/proc/" . $pid)) {
            goto B19eb1e84d9ab28db2485113b3a23039;
        }
        $ea5780c60b0a2afa62b1d8395f019e9a = trim(file_get_contents("/proc/{$pid}/cmdline"));
        if (!stristr($ea5780c60b0a2afa62b1d8395f019e9a, $Afd5f79d62d4622597818545a5cf00d1)) {
            B19eb1e84d9ab28db2485113b3a23039:
            return false;
        }
        return true;
    }
    static function E79092731573697C16A932C339d0A101($streamId, $c6a482793047d2f533b0b69299b7d24d = 0) {
        $d0ecfdcd1b9396ba72538b60109bf719 = STREAMS_PATH . $streamId . ".lock";
        $Ab9f45b38498c3a010f3c4276ad5767c = fopen($d0ecfdcd1b9396ba72538b60109bf719, "a+");
        if (!flock($Ab9f45b38498c3a010f3c4276ad5767c, "LOCK_OZ")) {
            goto E9b9c3b1629504183a3a72be9c4be3bf;
        }
        $c6a482793047d2f533b0b69299b7d24d = intval($c6a482793047d2f533b0b69299b7d24d);
        shell_exec("PHP_BIN TOOLS_PATH" . "stream_monitor.php {$streamId} {$c6a482793047d2f533b0b69299b7d24d} >/dev/null 2>/dev/null &");
        usleep(300);
        flock($Ab9f45b38498c3a010f3c4276ad5767c, LOCK_UN);
        E9b9c3b1629504183a3a72be9c4be3bf:
        fclose($Ab9f45b38498c3a010f3c4276ad5767c);
    }
    /**
     * Stops the transcoding process for a given stream.
     *
     * @param int $streamId The ID of the stream.
     * @return void
     */
    static function stopTranscoding($streamId) {
        // Check if the PID file exists
        if (!file_exists(MOVIES_PATH . $streamId . "_.pid")) {
            return;
        }
        // Get the PID from the PID file
        $pid = (int) file_get_contents(MOVIES_PATH . $streamId . "_.pid");

        // Kill the process with the given PID
        posix_kill($pid, 9);

        // Remove all files related to the stream
        shell_exec("rm -f " . MOVIES_PATH . $streamId . ".*");

        // Update the stream information in the database
        $query = "UPDATE `streams_sys` SET `bitrate` = NULL, `current_source` = NULL, `to_analyze` = 0, `pid` = NULL, `stream_started` = NULL, `stream_info` = NULL, `stream_status` = 0 WHERE `stream_id` = :streamId AND `server_id` = :serverId";
        $params = array('streamId' => $streamId, 'serverId' => SERVER_ID);
        self::$ipTV_db->query($query, $params);
    }
    /**
     * Starts transcoding for a given stream ID
     *
     * @param int $streamId The ID of the stream
     * @return int|bool The process ID of the transcoding or false if unsuccessful
     */
    static function startTranscoding($streamId) {
        $streamInfo = array();

        // Get stream information from the database
        self::$ipTV_db->query("SELECT * FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 0 LEFT JOIN `transcoding_profiles` t4 ON t1.transcode_profile_id = t4.profile_id WHERE t1.direct_source = 0 AND t1.id = '%d'", $streamId);

        // Check if stream information exists
        if (self::$ipTV_db->getRowCount() <= 0) {
            return false;
        }

        // Get stream information and decode target container
        $streamInfo["stream_info"] = self::$ipTV_db->f1ed191d78470660eDfF4A007696bc1f();
        $targetContainer = json_decode($streamInfo["stream_info"]["target_container"], true);

        // Check if target container is valid JSON
        if (json_last_error() === JSON_ERROR_NONE) {
            $streamInfo["stream_info"]["target_container"] = $targetContainer;
        } else {
            $streamInfo["stream_info"]["target_container"] = array($streamInfo["stream_info"]["target_container"]);
        }

        // Get server information from the database
        self::$ipTV_db->query("SELECT * FROM `streams_sys` WHERE stream_id  = '%d' AND `server_id` = '%d'", $streamId, SERVER_ID);

        // Check if server information exists
        if (self::$ipTV_db->getRowCount() <= 0) {
            return false;
        }

        $streamInfo["server_info"] = self::$ipTV_db->f1ed191D78470660EDFF4a007696BC1f();

        // Get stream arguments from the database
        self::$ipTV_db->query("SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = '%d' AND t1.argument_id = t2.id", $streamId);
        $streamInfo["stream_arguments"] = self::$ipTV_db->c126FD559932F625CDf6098d86C63880();

        $streamSource = urldecode(json_decode($streamInfo["stream_info"]["stream_source"], true)[0]);

        // Check if stream source is a server source or a URL
        if (substr($streamSource, 0, 2) == "s:") {
            $streamSourceParts = explode(":", $streamSource, 3);
            $serverId = $streamSourceParts[1];

            // Check if server ID is different from current server ID
            if ($serverId != SERVER_ID) {
                $filePath = a78Bf8d35765BE2408c50712ce7A43aD::$StreamingServers[$serverId]["api_url"] . "&action=getFile&filename=" . urlencode($streamSourceParts[2]);
            } else {
                $filePath = $streamSourceParts[2];
            }

            $streamProtocol = null;
        } else {
            $streamProtocol = substr($streamSource, 0, strpos($streamSource, "://"));
            $filePath = str_replace(" ", "%20", $streamSource);
        }

        // Create stream arguments string
        $streamArguments = implode(" ", self::eA860C1D3851c46D06e64911E3602768($streamInfo["stream_arguments"], $streamProtocol, "fetch"));

        // Create symlink command for movie symlink
        if (isset($serverId) && $serverId == SERVER_ID && $streamInfo["stream_info"]["movie_symlink"] == 1) {
            $symlinkCommand = "ln -s \"{$filePath}\" " . MOVIES_PATH . $streamId . "." . pathinfo($filePath, PATHINFO_EXTENSION) . " >/dev/null 2>/dev/null & echo \$! > " . MOVIES_PATH . $streamId . "_.pid";
        }

        $subtitles = json_decode($streamInfo["stream_info"]["movie_subtitles"], true);
        $subtitlesString = '';
        $subtitlesOptions = '';

        // Create subtitles string and options
        for ($index = 0; $index < count($subtitles["files"]); $index++) {
            $subtitleFile = urldecode($subtitles["files"][$index]);
            $subtitleCharset = $subtitles["charset"][$index];

            if ($subtitles["location"] == SERVER_ID) {
                $subtitlesString .= "-sub_charenc \"{$subtitleCharset}\" -i \"{$subtitleFile}\" ";
            } else {
                $subtitlesString .= "-sub_charenc \"{$subtitleCharset}\" -i \"" . a78BF8D35765be2408c50712Ce7a43aD::$StreamingServers[$subtitles["location"]]["api_url"] . "&action=getFile&filename=" . urlencode($subtitleFile) . "\" ";
            }

            $subtitlesOptions .= "-map " . ($index + 1) . " -metadata:s:s:{$index} title={$subtitles["names"][$index]} -metadata:s:s:{$index} language={$subtitles["names"][$index]} ";
        }

        // Create ffmpeg command for transcoding
        $symlinkCommand = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} -fflags +genpts -async 1 {READ_NATIVE} -i \"{STREAM_SOURCE}\" {$subtitlesString}";

        $readNativeOption = '';

        // Check if read native option is enabled
        if (!($streamInfo["stream_info"]["read_native"] == 1)) {
            $readNativeOption = "-re";
        }

        // Check if transcoding is enabled
        if ($streamInfo["stream_info"]["enable_transcode"] == 1) {
            if ($streamInfo["stream_info"]["transcode_profile_id"] == -1) {
                $streamInfo["stream_info"]["transcode_attributes"] = array_merge(self::ea860c1d3851c46d06E64911e3602768($streamInfo["stream_arguments"], $streamProtocol, "transcode"), json_decode($streamInfo["stream_info"]["transcode_attributes"], true));
            } else {
                $streamInfo["stream_info"]["transcode_attributes"] = json_decode($streamInfo["stream_info"]["profile_options"], true);
            }
        } else {
            $streamInfo["stream_info"]["transcode_attributes"] = array();
        }

        $copyUnknownOption = "-map 0 -copy_unknown ";

        // Check if custom map exists or if subtitles should be removed
        if (!empty($streamInfo["stream_info"]["custom_map"])) {
            $copyUnknownOption = $streamInfo["stream_info"]["custom_map"] . " -copy_unknown ";
        } elseif ($streamInfo["stream_info"]["remove_subtitles"] == 1) {
            $copyUnknownOption = "-map 0:a -map 0:v";
        }

        // Check if audio codec or video codec should be copied
        if (array_key_exists("-acodec", $streamInfo["stream_info"]["transcode_attributes"])) {
            $streamInfo["stream_info"]["transcode_attributes"]["-acodec"] = "copy";
        }

        if (array_key_exists("-vcodec", $streamInfo["stream_info"]["transcode_attributes"])) {
            $streamInfo["stream_info"]["transcode_attributes"]["-vcodec"] = "copy";
        }

        $fileExtensions = array();

        // Create file extensions array
        foreach ($streamInfo["stream_info"]["target_container"] as $extension) {
            $fileExtensions[$extension] = "-movflags +faststart -dn {$copyUnknownOption} -ignore_unknown {$subtitlesOptions} " . MOVIES_PATH . $streamId . "." . $extension . " ";
        }

        // Append transcoding attributes and codec to ffmpeg command
        foreach ($fileExtensions as $extension => $codec) {
            if ($extension == "mp4") {
                // Set transcoding attribute to "mov_text" if extension is "mp4"
                $streamInfo["stream_info"]["transcode_attributes"]["-scodec"] = "mov_text";
            } elseif ($extension == "mkv") {
                // Set transcoding attribute to "srt" if extension is "mkv"
                $streamInfo["stream_info"]["transcode_attributes"]["-scodec"] = "srt";
            } else {
                // Set transcoding attribute to "copy" for all other extensions
                $streamInfo["stream_info"]["transcode_attributes"]["-scodec"] = "copy";
            }

            // Append transcoding attributes to the symlink command
            $symlinkCommand .= implode(" ", self::F6664c80BDe3e9bbe2c12CEb906D5A11($streamInfo["stream_info"]["transcode_attributes"])) . " ";

            // Append codec to the symlink command
            $symlinkCommand .= $codec;
        }

        // Append output redirection and PID file creation to the symlink command
        $symlinkCommand .= " >/dev/null 2>" . MOVIES_PATH . $streamId . ".errors & echo \$! > " . MOVIES_PATH . $streamId . "_.pid";

        // Replace placeholders in the symlink command with actual values
        $symlinkCommand = str_replace(
            array("{FETCH_OPTIONS}", "{STREAM_SOURCE}", "{READ_NATIVE}"),
            array(empty($streamArguments) ? '' : $streamArguments, $filePath, empty($streamInfo["stream_info"]["custom_ffmpeg"]) ? $readNativeOption : ''),
            $symlinkCommand
        );

        // Execute the symlink command
        shell_exec($symlinkCommand);

        // Log the symlink command
        file_put_contents("/tmp/commands", $symlinkCommand . "\n", FILE_APPEND);

        // Get the PID from the PID file
        $pid = intval(file_get_contents(MOVIES_PATH . $streamId . "_.pid"));

        // Update the stream information in the database
        self::$ipTV_db->query("UPDATE `streams_sys` SET `to_analyze` = 1, `stream_started` = '%d', `stream_status` = 0, `pid` = '%d' WHERE `stream_id` = '%d' AND `server_id` = '%d'", time(), $pid, $streamId, SERVER_ID);

        // Return the PID
        return $pid;
    }
    static function CEBeee6A9C20e0da24C41A0247cf1244($streamId, &$bb1b9dfc97454460e165348212675779, $B71703fbd9f237149967f9ac3c41dc19 = null) {
        ++$bb1b9dfc97454460e165348212675779;
        if (!file_exists(STREAMS_PATH . $streamId . "_.pid")) {
            goto ebaf8e83d6903e697b868e20baf33caa;
        }
        unlink(STREAMS_PATH . $streamId . "_.pid");
        ebaf8e83d6903e697b868e20baf33caa:
        $streamInfo = array();
        self::$ipTV_db->query("SELECT * FROM `streams` t1 \n                               INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 1\n                               LEFT JOIN `transcoding_profiles` t4 ON t1.transcode_profile_id = t4.profile_id \n                               WHERE t1.direct_source = 0 AND t1.id = '%d'", $streamId);
        if (!(self::$ipTV_db->getRowCount() <= 0)) {
            $streamInfo["stream_info"] = self::$ipTV_db->F1ED191D78470660EDFF4A007696BC1f();
            self::$ipTV_db->query("SELECT * FROM `streams_sys` WHERE stream_id  = '%d' AND `server_id` = '%d'", $streamId, SERVER_ID);
            if (!(self::$ipTV_db->getRowCount() <= 0)) {
                $streamInfo["server_info"] = self::$ipTV_db->f1ed191D78470660eDFf4a007696bC1f();
                self::$ipTV_db->query("SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = '%d' AND t1.argument_id = t2.id", $streamId);
                $streamInfo["stream_arguments"] = self::$ipTV_db->c126FD559932f625cdf6098d86C63880();
                if ($streamInfo["server_info"]["on_demand"] == 1) {
                    $E1be7e0ba659254273dc1475ae9679e0 = $streamInfo["stream_info"]["probesize_ondemand"];
                    $C359d5e5ab36c7a88fca0754166e7996 = "10000000";
                    goto f03f13a140091b921f9b34e661f10b36;
                }
                $C359d5e5ab36c7a88fca0754166e7996 = abs(intval(A78bf8D35765Be2408C50712Ce7A43AD::$settings["stream_max_analyze"]));
                $E1be7e0ba659254273dc1475ae9679e0 = abs(intval(A78bf8D35765Be2408c50712cE7A43ad::$settings["probesize"]));
                f03f13a140091b921f9b34e661f10b36:
                $d1c5b35a94aa4152ee37c6cfedfb2ec3 = intval($C359d5e5ab36c7a88fca0754166e7996 / 1000000) + 7;
                $Fa28e3498375fc4da68f3f818d774249 = "/usr/bin/timeout {$d1c5b35a94aa4152ee37c6cfedfb2ec3}s " . FFPROBE_PATH . " {FETCH_OPTIONS} -probesize {$E1be7e0ba659254273dc1475ae9679e0} -analyzeduration {$C359d5e5ab36c7a88fca0754166e7996} {CONCAT} -i \"{STREAM_SOURCE}\" -v quiet -print_format json -show_streams -show_format";
                $streamArguments = array();
                if ($streamInfo["server_info"]["parent_id"] == 0) {
                    $A733a5416ffab6ff47547550f3f9f641 = $streamInfo["stream_info"]["type_key"] == "created_live" ? array(CREATED_CHANNELS . $streamId . "_.list") : json_decode($streamInfo["stream_info"]["stream_source"], true);
                    goto f881a4588c552e13e0f605962a965426;
                }
                $A733a5416ffab6ff47547550f3f9f641 = array(A78bF8d35765be2408C50712ce7A43aD::$StreamingServers[$streamInfo["server_info"]["parent_id"]]["site_url_ip"] . "streaming/admin_live.php?stream=" . $streamId . "&password=" . A78Bf8d35765BE2408c50712Ce7a43Ad::$settings["live_streaming_pass"] . "&extension=ts");
                f881a4588c552e13e0f605962a965426:
                if (!(count($A733a5416ffab6ff47547550f3f9f641) > 0)) {
                    goto Addf182f86a94b305381bd0e81174f08;
                }
                if (!empty($B71703fbd9f237149967f9ac3c41dc19)) {
                    $A733a5416ffab6ff47547550f3f9f641 = array($B71703fbd9f237149967f9ac3c41dc19);
                    goto ac8a864b3489c444d14e1904ec5dfd7e;
                }
                if (A78BF8D35765BE2408c50712cE7A43AD::$settings["priority_backup"] != 1) {
                    if (empty($streamInfo["server_info"]["current_source"])) {
                        goto e7eedc2b99021a3a11f4a0933af2c2b1;
                    }
                    $Baee0c34e5755f1cfaa4159ea7e8702e = array_search($streamInfo["server_info"]["current_source"], $A733a5416ffab6ff47547550f3f9f641);
                    if (!($Baee0c34e5755f1cfaa4159ea7e8702e !== false)) {
                        goto D63aacc4ea564f12e24abd6538c7b052;
                    }
                    $index = 0;
                    B1fcf06a1d6da24af4b5d7d516d25b90:
                    if (!($index <= $Baee0c34e5755f1cfaa4159ea7e8702e)) {
                        $A733a5416ffab6ff47547550f3f9f641 = array_values($A733a5416ffab6ff47547550f3f9f641);
                        D63aacc4ea564f12e24abd6538c7b052:
                        e7eedc2b99021a3a11f4a0933af2c2b1:
                        goto Fd0c9fa73a22d3ad21baec039c9f9b6c;
                    }
                    $Ad110d626a9e62f0778a8f19383a0613 = $A733a5416ffab6ff47547550f3f9f641[$index];
                    unset($A733a5416ffab6ff47547550f3f9f641[$index]);
                    array_push($A733a5416ffab6ff47547550f3f9f641, $Ad110d626a9e62f0778a8f19383a0613);
                    $index++;
                    goto B1fcf06a1d6da24af4b5d7d516d25b90;
                }
                Fd0c9fa73a22d3ad21baec039c9f9b6c:
                ac8a864b3489c444d14e1904ec5dfd7e:
                Addf182f86a94b305381bd0e81174f08:
                $F7b03a1f7467c01c6ea18452d9a5202f = $bb1b9dfc97454460e165348212675779 <= RESTART_TAKE_CACHE ? true : false;
                if ($F7b03a1f7467c01c6ea18452d9a5202f) {
                    goto ebd27b3edaaacb30705e86c5be704ca9;
                }
                self::Ad09d99ce37614036d5A527595d569D9($A733a5416ffab6ff47547550f3f9f641);
                ebd27b3edaaacb30705e86c5be704ca9:
                foreach ($A733a5416ffab6ff47547550f3f9f641 as $F3803fa85b38b65447e6d438f8e9176a) {
                    $streamSource = self::ParseStreamURL($F3803fa85b38b65447e6d438f8e9176a);
                    $streamProtocol = strtolower(substr($streamSource, 0, strpos($streamSource, "://")));
                    $streamArguments = implode(" ", self::Ea860c1d3851C46D06E64911E3602768($streamInfo["stream_arguments"], $streamProtocol, "fetch"));
                    if (!($F7b03a1f7467c01c6ea18452d9a5202f && file_exists(STREAMS_PATH . md5($streamSource)))) {
                        $e49460014c491accfafaa768ea84cd9c = json_decode(shell_exec(str_replace(array("{FETCH_OPTIONS}", "{CONCAT}", "{STREAM_SOURCE}"), array($streamArguments, $streamInfo["stream_info"]["type_key"] == "created_live" && $streamInfo["server_info"]["parent_id"] == 0 ? "-safe 0 -f concat" : '', $streamSource), $Fa28e3498375fc4da68f3f818d774249)), true);
                        if (empty($e49460014c491accfafaa768ea84cd9c)) {
                        }
                        goto D4dc4038a49e681798bdc5fcc086c56d;
                    }
                    $e49460014c491accfafaa768ea84cd9c = json_decode(file_get_contents(STREAMS_PATH . md5($streamSource)), true);
                    goto D4dc4038a49e681798bdc5fcc086c56d;
                }
                D4dc4038a49e681798bdc5fcc086c56d:
                if (!empty($e49460014c491accfafaa768ea84cd9c)) {
                    if ($F7b03a1f7467c01c6ea18452d9a5202f) {
                        goto f21367977400d55935f38a3b5a6cd287;
                    }
                    file_put_contents(STREAMS_PATH . md5($streamSource), json_encode($e49460014c491accfafaa768ea84cd9c));
                    f21367977400d55935f38a3b5a6cd287:
                    $e49460014c491accfafaa768ea84cd9c = self::Ccbd051c8A19a02dC5B6db256Ae31C07($e49460014c491accfafaa768ea84cd9c);
                    $Ee11a0d09ece7de916fbc0b2ca0136a3 = json_decode($streamInfo["stream_info"]["external_push"], true);
                    $e1dc30615033011f7166d1950e7036ee = "http://127.0.0.1:" . A78BF8d35765BE2408c50712Ce7a43AD::$StreamingServers[SERVER_ID]["http_broadcast_port"] . "/progress.php?stream_id={$streamId}";
                    if (empty($streamInfo["stream_info"]["custom_ffmpeg"])) {
                        $symlinkCommand = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel warning -err_detect ignore_err {FETCH_OPTIONS} {GEN_PTS} {READ_NATIVE} -probesize {$E1be7e0ba659254273dc1475ae9679e0} -analyzeduration {$C359d5e5ab36c7a88fca0754166e7996} -progress \"{$e1dc30615033011f7166d1950e7036ee}\" {CONCAT} -i \"{STREAM_SOURCE}\" ";
                        if ($streamInfo["stream_info"]["stream_all"] == 1) {
                            $copyUnknownOption = "-map 0 -copy_unknown ";
                            goto F7052b7340617388b1314ad99c08b3b6;
                        }
                        if (!empty($streamInfo["stream_info"]["custom_map"])) {
                            $copyUnknownOption = $streamInfo["stream_info"]["custom_map"] . " -copy_unknown ";
                            goto F7052b7340617388b1314ad99c08b3b6;
                        }
                        if ($streamInfo["stream_info"]["type_key"] == "radio_streams") {
                            $copyUnknownOption = "-map 0:a? ";
                            goto c2fac9fbdb037e05684fb8450b6a5ba7;
                        }
                        $copyUnknownOption = '';
                        c2fac9fbdb037e05684fb8450b6a5ba7:
                        F7052b7340617388b1314ad99c08b3b6:
                        if (($streamInfo["stream_info"]["gen_timestamps"] == 1 || empty($streamProtocol)) && $streamInfo["stream_info"]["type_key"] != "created_live") {
                            $e9652f3db39531a69b91900690d5d064 = "-fflags +genpts -async 1";
                            goto a6cb04ba2fdaf4417d4a82959148687f;
                        }
                        $e9652f3db39531a69b91900690d5d064 = "-nofix_dts -start_at_zero -copyts -vsync 0 -correct_ts_overflow 0 -avoid_negative_ts disabled -max_interleave_delta 0";
                        a6cb04ba2fdaf4417d4a82959148687f:
                        $feb3f2070e6ccf961f6265281e875b1a = '';
                        if (!($streamInfo["server_info"]["parent_id"] == 0 && ($streamInfo["stream_info"]["read_native"] == 1 or stristr($e49460014c491accfafaa768ea84cd9c["container"], "hls") or empty($streamProtocol) or stristr($e49460014c491accfafaa768ea84cd9c["container"], "mp4") or stristr($e49460014c491accfafaa768ea84cd9c["container"], "matroska")))) {
                            goto f283f80882362b693eafe8affe5b7574;
                        }
                        $feb3f2070e6ccf961f6265281e875b1a = "-re";
                        f283f80882362b693eafe8affe5b7574:
                        if ($streamInfo["server_info"]["parent_id"] == 0 and $streamInfo["stream_info"]["enable_transcode"] == 1 and $streamInfo["stream_info"]["type_key"] != "created_live") {
                            if ($streamInfo["stream_info"]["transcode_profile_id"] == -1) {
                                $streamInfo["stream_info"]["transcode_attributes"] = array_merge(self::EA860c1D3851c46d06E64911E3602768($streamInfo["stream_arguments"], $streamProtocol, "transcode"), json_decode($streamInfo["stream_info"]["transcode_attributes"], true));
                                goto E697cb32a3c22753497cd0431dae3aa2;
                            }
                            $streamInfo["stream_info"]["transcode_attributes"] = json_decode($streamInfo["stream_info"]["profile_options"], true);
                            E697cb32a3c22753497cd0431dae3aa2:
                            goto D724bdd07744b75724723c57be250efb;
                        }
                        $streamInfo["stream_info"]["transcode_attributes"] = array();
                        D724bdd07744b75724723c57be250efb:
                        if (array_key_exists("-acodec", $streamInfo["stream_info"]["transcode_attributes"])) {
                            goto C89c9ed51c1208202cd91f7bfa6b3b12;
                        }
                        $streamInfo["stream_info"]["transcode_attributes"]["-acodec"] = "copy";
                        C89c9ed51c1208202cd91f7bfa6b3b12:
                        if (array_key_exists("-vcodec", $streamInfo["stream_info"]["transcode_attributes"])) {
                            goto Ce66ed4205c9f3e4b7c19a393a6749d2;
                        }
                        $streamInfo["stream_info"]["transcode_attributes"]["-vcodec"] = "copy";
                        Ce66ed4205c9f3e4b7c19a393a6749d2:
                        if (array_key_exists("-scodec", $streamInfo["stream_info"]["transcode_attributes"])) {
                            goto cc3a8ad39c0f9a89d4e0743a7ee460fe;
                        }
                        $streamInfo["stream_info"]["transcode_attributes"]["-scodec"] = "copy";
                        cc3a8ad39c0f9a89d4e0743a7ee460fe:
                        goto A7314975472ac8c8f1bdd009199221a3;
                    }
                    $streamInfo["stream_info"]["transcode_attributes"] = array();
                    $symlinkCommand = FFMPEG_PATH . " -y -nostdin -hide_banner -loglevel quiet {$d1006c7cc041221972025137b5112b7d} -progress \"{$e1dc30615033011f7166d1950e7036ee}\" " . $streamInfo["stream_info"]["custom_ffmpeg"];
                    A7314975472ac8c8f1bdd009199221a3:
                    $fileExtensions = array();
                    $fileExtensions["mpegts"][] = "{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time " . a78BF8d35765BE2408c50712CE7a43ad::$SegmentsSettings["seg_time"] . " -segment_list_size " . A78bf8D35765bE2408C50712ce7a43ad::$SegmentsSettings["seg_list_size"] . " -segment_format_options \"mpegts_flags=+initial_discontinuity:mpegts_copyts=1\" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list \"" . STREAMS_PATH . $streamId . "_.m3u8\" \"" . STREAMS_PATH . $streamId . "_%d.ts\" ";
                    if (!($streamInfo["stream_info"]["rtmp_output"] == 1)) {
                        goto Ac4ca8dc61fceb4f7dcd2b2acb8c4881;
                    }
                    $fileExtensions["flv"][] = "{MAP} {AAC_FILTER} -f flv rtmp://127.0.0.1:" . a78bf8d35765be2408c50712CE7a43aD::$StreamingServers[$streamInfo["server_info"]["server_id"]]["rtmp_port"] . "/live/{$streamId} ";
                    Ac4ca8dc61fceb4f7dcd2b2acb8c4881:
                    if (empty($Ee11a0d09ece7de916fbc0b2ca0136a3[SERVER_ID])) {
                        goto F623bdc102312d6bb9cd010763330b88;
                    }
                    foreach ($Ee11a0d09ece7de916fbc0b2ca0136a3[SERVER_ID] as $b202bc9c1c41da94906c398ceb9f3573) {
                        $fileExtensions["flv"][] = "{MAP} {AAC_FILTER} -f flv \"{$b202bc9c1c41da94906c398ceb9f3573}\" ";
                    }
                    F623bdc102312d6bb9cd010763330b88:
                    $f32785b2a16d0d92cda0b44ed436f505 = 0;
                    if (!($streamInfo["stream_info"]["delay_minutes"] > 0 && $streamInfo["server_info"]["parent_id"] == 0)) {
                        foreach ($fileExtensions as $extension => $f72c3a34155eca511d79ca3671e1063f) {
                            foreach ($f72c3a34155eca511d79ca3671e1063f as $codec) {
                                $symlinkCommand .= implode(" ", self::f6664c80bde3e9BBe2c12ceb906d5a11($streamInfo["stream_info"]["transcode_attributes"])) . " ";
                                $symlinkCommand .= $codec;
                            }
                        }
                        goto Ab3616c8de8bd4a36124f90f72d5bf1e;
                    }
                    $ccac9556cf5f7f83df650c022d673042 = 0;
                    if (!file_exists(DELAY_STREAM . $streamId . "_.m3u8")) {
                        goto b5c7effa65c597936232525ef71cee85;
                    }
                    $Ca434bcc380e9dbd2a3a588f6c32d84f = file(DELAY_STREAM . $streamId . "_.m3u8");
                    if (stristr($Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 1], $streamId . "_")) {
                        if (!preg_match("/\\_(.*?)\\.ts/", $Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 1], $matches)) {
                            goto E376243a25162a7e9a96615f05736000;
                        }
                        $ccac9556cf5f7f83df650c022d673042 = intval($matches[1]) + 1;
                        E376243a25162a7e9a96615f05736000:
                        goto Bc358712da2195af6049c6dc19157a6b;
                    }
                    if (!preg_match("/\\_(.*?)\\.ts/", $Ca434bcc380e9dbd2a3a588f6c32d84f[count($Ca434bcc380e9dbd2a3a588f6c32d84f) - 2], $matches)) {
                        goto Ae9f3a95d0d642988cb827b99cf0c542;
                    }
                    $ccac9556cf5f7f83df650c022d673042 = intval($matches[1]) + 1;
                    Ae9f3a95d0d642988cb827b99cf0c542:
                    Bc358712da2195af6049c6dc19157a6b:
                    if (file_exists(DELAY_STREAM . $streamId . "_.m3u8_old")) {
                        file_put_contents(DELAY_STREAM . $streamId . "_.m3u8_old", file_get_contents(DELAY_STREAM . $streamId . "_.m3u8_old") . file_get_contents(DELAY_STREAM . $streamId . "_.m3u8"));
                        shell_exec("sed -i '/EXTINF\\|.ts/!d' DELAY_STREAM" . $streamId . "_.m3u8_old");
                        goto Bb78eb9c44efca565d4e7263bcc49b55;
                    }
                    copy(DELAY_STREAM . $streamId . "_.m3u8", DELAY_STREAM . $streamId . "_.m3u8_old");
                    Bb78eb9c44efca565d4e7263bcc49b55:
                    b5c7effa65c597936232525ef71cee85:
                    $symlinkCommand .= implode(" ", self::f6664C80BDe3E9bbe2c12ceB906D5A11($streamInfo["stream_info"]["transcode_attributes"])) . " ";
                    $symlinkCommand .= "{MAP} -individual_header_trailer 0 -f segment -segment_format mpegts -segment_time " . A78bF8D35765bE2408c50712cE7a43Ad::$SegmentsSettings["seg_time"] . " -segment_list_size " . $streamInfo["stream_info"]["delay_minutes"] * 6 . " -segment_start_number {$ccac9556cf5f7f83df650c022d673042} -segment_format_options \"mpegts_flags=+initial_discontinuity:mpegts_copyts=1\" -segment_list_type m3u8 -segment_list_flags +live+delete -segment_list \"" . DELAY_STREAM . $streamId . "_.m3u8\" \"" . DELAY_STREAM . $streamId . "_%d.ts\" ";
                    $Dedb93a1e8822879d8790c1f2fc7d6f1 = $streamInfo["stream_info"]["delay_minutes"] * 60;
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
                    $symlinkCommand .= " >/dev/null 2>>STREAMS_PATH" . $streamId . ".errors & echo \$! > " . STREAMS_PATH . $streamId . "_.pid";
                    $symlinkCommand = str_replace(array("{INPUT}", "{FETCH_OPTIONS}", "{GEN_PTS}", "{STREAM_SOURCE}", "{MAP}", "{READ_NATIVE}", "{CONCAT}", "{AAC_FILTER}"), array("\"{$streamSource}\"", empty($streamInfo["stream_info"]["custom_ffmpeg"]) ? $streamArguments : '', empty($streamInfo["stream_info"]["custom_ffmpeg"]) ? $e9652f3db39531a69b91900690d5d064 : '', $streamSource, empty($streamInfo["stream_info"]["custom_ffmpeg"]) ? $copyUnknownOption : '', empty($streamInfo["stream_info"]["custom_ffmpeg"]) ? $feb3f2070e6ccf961f6265281e875b1a : '', $streamInfo["stream_info"]["type_key"] == "created_live" && $streamInfo["server_info"]["parent_id"] == 0 ? "-safe 0 -f concat" : '', !stristr($e49460014c491accfafaa768ea84cd9c["container"], "flv") && $e49460014c491accfafaa768ea84cd9c["codecs"]["audio"]["codec_name"] == "aac" && $streamInfo["stream_info"]["transcode_attributes"]["-acodec"] == "copy" ? "-bsf:a aac_adtstoasc" : ''), $symlinkCommand);
                    shell_exec($symlinkCommand);
                    $pid = $D90a38f0f1d7f1bcd1b2eee088e76aca = intval(file_get_contents(STREAMS_PATH . $streamId . "_.pid"));
                    if (!(SERVER_ID == $streamInfo["stream_info"]["tv_archive_server_id"])) {
                        goto ab4b9fd020e95bcd610e36802fdc7435;
                    }
                    shell_exec("PHP_BIN TOOLS_PATHarchive.php " . $streamId . " >/dev/null 2>/dev/null & echo \$!");
                    ab4b9fd020e95bcd610e36802fdc7435:
                    $Dac1208baefb5d684938829a3a0e0bc6 = $streamInfo["stream_info"]["delay_minutes"] > 0 && $streamInfo["server_info"]["parent_id"] == 0 ? true : false;
                    $f32785b2a16d0d92cda0b44ed436f505 = $Dac1208baefb5d684938829a3a0e0bc6 ? time() + $Dedb93a1e8822879d8790c1f2fc7d6f1 : 0;
                    self::$ipTV_db->query("UPDATE `streams_sys` SET `delay_available_at` = '%d',`to_analyze` = 0,`stream_started` = '%d',`stream_info` = '%s',`stream_status` = 0,`pid` = '%d',`progress_info` = '%s',`current_source` = '%s' WHERE `stream_id` = '%d' AND `server_id` = '%d'", $f32785b2a16d0d92cda0b44ed436f505, time(), json_encode($e49460014c491accfafaa768ea84cd9c), $pid, json_encode(array()), $F3803fa85b38b65447e6d438f8e9176a, $streamId, SERVER_ID);
                    $Bb37b848bec813a5c13ea0b018962c40 = !$Dac1208baefb5d684938829a3a0e0bc6 ? STREAMS_PATH . $streamId . "_.m3u8" : DELAY_STREAM . $streamId . "_.m3u8";
                    return array("main_pid" => $pid, "stream_source" => $streamSource, "delay_enabled" => $Dac1208baefb5d684938829a3a0e0bc6, "parent_id" => $streamInfo["server_info"]["parent_id"], "delay_start_at" => $f32785b2a16d0d92cda0b44ed436f505, "playlist" => $Bb37b848bec813a5c13ea0b018962c40);
                }
                if (!($streamInfo["server_info"]["stream_status"] == 0 || $streamInfo["server_info"]["to_analyze"] == 1 || $streamInfo["server_info"]["pid"] != -1)) {
                    goto E3f0f6fa6f88a988847fe7991a8d19df;
                }
                self::$ipTV_db->query("UPDATE `streams_sys` SET `progress_info` = '',`to_analyze` = 0,`pid` = -1,`stream_status` = 1 WHERE `server_id` = '%d' AND `stream_id` = '%d'", SERVER_ID, $streamId);
                E3f0f6fa6f88a988847fe7991a8d19df:
                return 0;
            }
            return false;
        }
        return false;
    }
    public static function customOrder($D099d64305e0e1b9f20300f1ef51f8a7, $E28f7a505c062145e6df747991c0a2d3) {
        if (!(substr($D099d64305e0e1b9f20300f1ef51f8a7, 0, 3) == "-i ")) {
            return 1;
        }
        return -1;
    }
    public static function EA860c1D3851C46d06E64911E3602768($c31311861794ebdea68a9eab6a24fd6d, $streamProtocol, $a28758c1ab974badfc544e11aaf19a57) {
        $Eb6e347d24315f277ac38240a6589dd0 = array();
        if (empty($c31311861794ebdea68a9eab6a24fd6d)) {
            goto A7e7af14f087bfa69134afef2ab3e5af;
        }
        foreach ($c31311861794ebdea68a9eab6a24fd6d as $f091df572e6d2b79881acbf4e5500a7e => $e380987e83a27088358f65f47ff3117f) {
            if (!($e380987e83a27088358f65f47ff3117f["argument_cat"] != $a28758c1ab974badfc544e11aaf19a57)) {
                if (!(!is_null($e380987e83a27088358f65f47ff3117f["argument_wprotocol"]) && !stristr($streamProtocol, $e380987e83a27088358f65f47ff3117f["argument_wprotocol"]) && !is_null($streamProtocol))) {
                    if ($e380987e83a27088358f65f47ff3117f["argument_type"] == "text") {
                        $Eb6e347d24315f277ac38240a6589dd0[] = sprintf($e380987e83a27088358f65f47ff3117f["argument_cmd"], $e380987e83a27088358f65f47ff3117f["value"]);
                        goto B679e471f2e7d261f78d7effa138a94f;
                    }
                    $Eb6e347d24315f277ac38240a6589dd0[] = $e380987e83a27088358f65f47ff3117f["argument_cmd"];
                    B679e471f2e7d261f78d7effa138a94f:
                    goto C6dbbdac181311c8c1dd3cf7537549d9;
                }
                goto e0ffb06cc49d2710bde0e13e6fb02e4c;
            }
            C6dbbdac181311c8c1dd3cf7537549d9:
            e0ffb06cc49d2710bde0e13e6fb02e4c:
        }
        A7e7af14f087bfa69134afef2ab3e5af:
        return $Eb6e347d24315f277ac38240a6589dd0;
    }
    public static function F6664c80bdE3E9BBe2C12CeB906D5a11($Bddd92df0619e485304556731bb7ca2f) {
        $e80cbed8655f14b141bd53699dbbdc10 = array();
        foreach ($Bddd92df0619e485304556731bb7ca2f as $Baee0c34e5755f1cfaa4159ea7e8702e => $e380987e83a27088358f65f47ff3117f) {
            if (!isset($e380987e83a27088358f65f47ff3117f["cmd"])) {
                goto d5e302a7efdfb9d69e112be61940b67f;
            }
            $Bddd92df0619e485304556731bb7ca2f[$Baee0c34e5755f1cfaa4159ea7e8702e] = $e380987e83a27088358f65f47ff3117f = $e380987e83a27088358f65f47ff3117f["cmd"];
            d5e302a7efdfb9d69e112be61940b67f:
            if (!preg_match("/-filter_complex \"(.*?)\"/", $e380987e83a27088358f65f47ff3117f, $matches)) {
                goto F145724d6c67edfccb510b14b854225d;
            }
            $Bddd92df0619e485304556731bb7ca2f[$Baee0c34e5755f1cfaa4159ea7e8702e] = trim(str_replace($matches[0], '', $Bddd92df0619e485304556731bb7ca2f[$Baee0c34e5755f1cfaa4159ea7e8702e]));
            $e80cbed8655f14b141bd53699dbbdc10[] = $matches[1];
            F145724d6c67edfccb510b14b854225d:
        }
        if (empty($e80cbed8655f14b141bd53699dbbdc10)) {
            goto b7dfbebb47c7785fcaa4ff01222e8cae;
        }
        $Bddd92df0619e485304556731bb7ca2f[] = "-filter_complex \"" . implode(",", $e80cbed8655f14b141bd53699dbbdc10) . "\"";
        b7dfbebb47c7785fcaa4ff01222e8cae:
        $B54918193a6b3b39c547eb9486c4c2ff = array();
        foreach ($Bddd92df0619e485304556731bb7ca2f as $Baee0c34e5755f1cfaa4159ea7e8702e => $e7ddd0b219bd2e9b7547185c8bccb6a9) {
            if (is_numeric($Baee0c34e5755f1cfaa4159ea7e8702e)) {
                $B54918193a6b3b39c547eb9486c4c2ff[] = $e7ddd0b219bd2e9b7547185c8bccb6a9;
                goto d7def6a14fdde8912b2efc46a3a4eb2c;
            }
            $B54918193a6b3b39c547eb9486c4c2ff[] = $Baee0c34e5755f1cfaa4159ea7e8702e . " " . $e7ddd0b219bd2e9b7547185c8bccb6a9;
            d7def6a14fdde8912b2efc46a3a4eb2c:
        }
        $B54918193a6b3b39c547eb9486c4c2ff = array_filter($B54918193a6b3b39c547eb9486c4c2ff);
        uasort($B54918193a6b3b39c547eb9486c4c2ff, array("E3cf480c172e8B47FE10857c2A5AEb48", "customOrder"));
        return array_map("trim", array_values(array_filter($B54918193a6b3b39c547eb9486c4c2ff)));
    }
    public static function ParseStreamURL($D849b6918b9e10195509dc8a824f49eb) {
        $streamProtocol = strtolower(substr($D849b6918b9e10195509dc8a824f49eb, 0, 4));
        if ($streamProtocol == "rtmp") {
            if (!stristr($D849b6918b9e10195509dc8a824f49eb, "\$OPT")) {
                goto a72059d797bb74b5705f80ff3d450921;
            }
            $b853b956930a081396b7a6beb8404265 = "rtmp://\$OPT:rtmp-raw=";
            $D849b6918b9e10195509dc8a824f49eb = trim(substr($D849b6918b9e10195509dc8a824f49eb, stripos($D849b6918b9e10195509dc8a824f49eb, $b853b956930a081396b7a6beb8404265) + strlen($b853b956930a081396b7a6beb8404265)));
            a72059d797bb74b5705f80ff3d450921:
            $D849b6918b9e10195509dc8a824f49eb .= " live=1 timeout=10";
            goto A241a8d3b9b9be4b98784fded18f7b85;
        }
        if ($streamProtocol == "http") {
            $d412be7a00d131e9be20aca9526c741f = array("youtube.com", "youtu.be", "livestream.com", "ustream.tv", "twitch.tv", "vimeo.com", "facebook.com", "dailymotion.com", "cnn.com", "edition.cnn.com", "youporn.com", "pornhub.com", "youjizz.com", "xvideos.com", "redtube.com", "ruleporn.com", "pornotube.com", "skysports.com", "screencast.com", "xhamster.com", "pornhd.com", "pornktube.com", "tube8.com", "vporn.com", "giniko.com", "xtube.com");
            $E8cb364637af05312e9ad4e7c0680ce2 = str_ireplace("www.", '', parse_url($D849b6918b9e10195509dc8a824f49eb, PHP_URL_HOST));
            if (!in_array($E8cb364637af05312e9ad4e7c0680ce2, $d412be7a00d131e9be20aca9526c741f)) {
                goto Cbfd637fabd30e0823bad6f57d835a9b;
            }
            $B13e3f304ca1f14e137f209a5138ea10 = trim(shell_exec(YOUTUBE_PATH . " \"{$D849b6918b9e10195509dc8a824f49eb}\" -q --get-url --skip-download -f best"));
            $D849b6918b9e10195509dc8a824f49eb = explode("\n", $B13e3f304ca1f14e137f209a5138ea10)[0];
            Cbfd637fabd30e0823bad6f57d835a9b:
            goto D5efec3320733f74b2a720a906fb240f;
        }
        D5efec3320733f74b2a720a906fb240f:
        A241a8d3b9b9be4b98784fded18f7b85:
        return $D849b6918b9e10195509dc8a824f49eb;
    }
}
