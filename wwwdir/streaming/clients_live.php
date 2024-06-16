<?php
register_shutdown_function('shutdown');
set_time_limit(0);
require '../init.php';
$streaming_block = true;
if (isset(ipTV_lib::$request["qs"])) {
    if (stristr(ipTV_lib::$request["qs"], ':p=')) {
        $qs = explode(':p=', ipTV_lib::$request["qs"]);
        ipTV_lib::$request["password"] = $qs[1];
        ipTV_lib::$request["username"] = substr($qs[0], 2);
    }
}
if (!isset(ipTV_lib::$request["extension"]) || !isset(ipTV_lib::$request["username"]) || !isset(ipTV_lib::$request["password"]) || !isset(ipTV_lib::$request["uuid"]) || !isset(ipTV_lib::$request["stream"])) {
    die;
}
$geoip = new Reader(GEOIP2_FILENAME);
$activity_id = 0;
$close_connection = true;
$connection_speed_file = null;
$user_ip = ipTV_streaming::getUserIP();
$rCountryCode = ipTV_streaming::getIPInfo($user_ip)['country']['iso_code'];
$user_agent = empty($_SERVER["HTTP_USER_AGENT"]) ? '' : htmlentities(trim($_SERVER["HTTP_USER_AGENT"]));
$rSegmentName = empty(ipTV_lib::$request["segment"]) ? '' : ipTV_lib::$request["segment"];
$external_device = null;
$username = ipTV_lib::$request["username"];
$password = ipTV_lib::$request["password"];
$uuid = ipTV_lib::$request["uuid"];
$stream_id = intval(ipTV_lib::$request["stream"]);
$extension = preg_replace('/[^A-Za-z0-9 ]/', '', trim(ipTV_lib::$request["extension"]));
$date = time();
$rConnection = null;

if (ipTV_lib::$settings["use_buffer"] == 0) {
    header('X-Accel-Buffering: no');
}
header('Access-Control-Allow-Origin: *');
$play_token = empty(ipTV_lib::$request["play_token"]) ? null : ipTV_lib::$request["play_token"];

$user_info = ipTV_streaming::GetUserInfo(null, $username, $password, true, false, true, array(), false, $user_ip, $user_agent, array(), $play_token, $stream_id, $rSegmentName);
if ($user_info) {
    if (isset($user_info["mag_invalid_token"])) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'MAG_TOKEN_INVALID', $user_ip);
        die;
    }
    if ($user_info["bypass_ua"] == 0) {
        ipTV_streaming::checkGlobalBlockUA($user_agent);
    }
    if ($user_info["is_stalker"] == 1) {
        if (empty(ipTV_lib::$request["stalker_key"]) || $extension != 'ts') {
            die;
        }
        $stalker_key = base64_decode(urldecode(ipTV_lib::$request["stalker_key"]));
        if ($decrypt_key = ipTV_lib::mc_decrypt($stalker_key, md5(ipTV_lib::$settings["live_streaming_pass"]))) {
            $stalker_data = explode('=', $decrypt_key);
            if ($stalker_data[2] != $stream_id) {
                ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'STALKER_CHANNEL_MISMATCH', $user_ip);
                die;
            }
            if ($stalker_data[1] != $user_ip) {
                ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'STALKER_IP_MISMATCH', $user_ip);
                die;
            }
            if (time() > $stalker_data[3]) {
                ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'STALKER_KEY_EXPIRED', $user_ip);
                die;
            }
            $external_device = $stalker_data[0];
        } else {
            ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'STALKER_DECRYPT_FAILED', $user_ip);
            die;
        }
    }
    if (!is_null($user_info["exp_date"]) && time() >= $user_info["exp_date"]) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'USER_EXPIRED', $user_ip);
        ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_expired_video', 'expired_video_path', $extension);
        die;
    }
    if ($user_info["admin_enabled"] == 0) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'USER_BAN', $user_ip);
        ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_banned_video', 'banned_video_path', $extension);
        die;
    }
    if ($user_info["enabled"] == 0) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'USER_DISABLED', $user_ip);
        ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_banned_video', 'banned_video_path', $extension);
        die;
    }
    if (empty($user_agent) && ipTV_lib::$settings["disallow_empty_user_agents"] == 1) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'EMPTY_UA', $user_ip);
        die;
    }
    $geoip_country_code = $geoip->getWithPrefixLen($user_ip)[0];
    $geoip->close();
    if (!empty($geoip_country_code)) {
        $geoip_country_code = $geoip_country_code["registered_country"]["iso_code"];
    }
    if (!empty($user_info["allowed_ips"]) && !in_array($user_ip, array_map('gethostbyname', $user_info["allowed_ips"]))) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'IP_BAN', $user_ip);
        die;
    }
    if (!empty($geoip_country_code)) {
        $forced_country = !empty($user_info['forced_country']) ? true : false;
        if ($forced_country && $user_info['forced_country'] != 'ALL' && $geoip_country_code != $user_info['forced_country']) {
            ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'COUNTRY_DISALLOW', $user_ip);
            die;
        }
        if (!$forced_country && !in_array('ALL', ipTV_lib::$settings['allow_countries']) && !in_array($geoip_country_code, ipTV_lib::$settings['allow_countries'])) {
            ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'COUNTRY_DISALLOW', $user_ip);
            die;
        }
    }
    if (!empty($user_info["allowed_ua"]) && !in_array($user_agent, $user_info["allowed_ua"])) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'USER_AGENT_BAN', $user_ip);
        die;
    }
    if (ipTV_streaming::checkIsCracked($user_ip)) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'CRACKED', $user_ip);
        die;
    }
    if (isset($user_info["ip_limit_reached"])) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'USER_ALREADY_CONNECTED', $user_ip);
        die;
    }
    $streaming_block = false;
    if (!array_key_exists($extension, $user_info["output_formats"])) {
        http_response_code(405);
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'USER_DISALLOW_EXT', $user_ip);
        die;
    }
    if (!in_array($stream_id, $user_info["channel_ids"])) {
        http_response_code(406);
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'NOT_IN_BOUQUET', $user_ip);
        die;
    }
    if ($user_info["isp_violate"] == 1) {
        http_response_code(401);
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'ISP_LOCK_FAILED', $user_ip, json_encode(array('old' => $user_info["isp_desc"], 'new' => $user_info["con_isp_name"])));
        die;
    }
    if ($user_info["isp_is_server"] == 1) {
        http_response_code(401);
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'CON_SVP', $user_ip, json_encode(array('user_agent' => $user_agent, 'isp' => $user_info["con_isp_name"], 'type' => $user_info["con_isp_type"])), true);
        die;
    }
    if ($user_info["max_connections"] != 0) {
        if (!empty($user_info["pair_line_info"])) {
            if ($user_info["pair_line_info"]["max_connections"] != 0) {
                if ($user_info["pair_line_info"]["active_cons"] >= $user_info["pair_line_info"]["max_connections"]) {
                    ipTV_streaming::CloseLastCon($user_info["pair_id"], $user_info["pair_line_info"]["max_connections"]);
                }
            }
        }
        if ($user_info["active_cons"] >= $user_info["max_connections"] && $extension != 'm3u8') {
            ipTV_streaming::CloseLastCon($user_info["id"], $user_info["max_connections"]);
        }
    }
    $channel_info = ipTV_streaming::ChannelInfo($stream_id, $extension, $user_info, $user_ip, $geoip_country_code, $external_device, $user_info["con_isp_name"], 'live');
    if ($channel_info) {
        $playlist = STREAMS_PATH . $stream_id . '_.m3u8';
        if (!ipTV_streaming::ps_running($channel_info["pid"], FFMPEG_PATH)) {
            if ($channel_info["on_demand"] == 1) {
                if (!ipTV_streaming::CheckMonitorRunning($channel_info["monitor_pid"], $stream_id)) {
                    ipTV_stream::startMonitor($stream_id);
                }
            } else {
                ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_not_on_air_video', 'not_on_air_video_path', $extension);
            }
        }
        switch ($extension) {
            case 'm3u8':
                if (empty(ipTV_lib::$request["segment"])) {
                    $rProxyID = null;
                    $ipTV_db->query('SELECT activity_id,hls_end FROM `lines_live` WHERE `uuid` = \'%d\' AND `user_id` = \'%d\' AND `server_id` = \'%s\' AND `container` = \'hls\' AND `stream_id` = \'%s\' AND `hls_end` = 0', $uuid, $user_info["id"], SERVER_ID, $stream_id);

                    if ($ipTV_db->num_rows() > 0) {
                        $rConnection = $ipTV_db->get_row();
                    }

                    if (!$rConnection) {
                        // if (time() > $rExpiresAt) {
                        //     generateError("TOKEN_EXPIRED");
                        //     http_response_code(404);
                        // }

                        $rResult = $ipTV_db->query('INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`proxy_id`,`user_agent`,`user_ip`,`container`,`pid`,`uuid`,`date_start`,`geoip_country_code`,`isp`,`external_device`,`hls_last_read`) VALUES(\'%d\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\',\'%d\',\'%d\',\'%d\')', $user_info["id"], $stream_id, SERVER_ID, $rProxyID, $user_agent, $user_ip, "hls", NULL, $uuid, time(), $rCountryCode, $user_info["con_isp_name"], null, time() - (int) ipTV_lib::$StreamingServers[SERVER_ID]["time_offset"]);
                    } else {
                        $rIPMatch = ipTV_lib::$settings["ip_subnet_match"] ? implode(".", array_slice(explode(".", $rConnection["user_ip"]), 0, -1)) == implode(".", array_slice(explode(".", $user_ip), 0, -1)) : $rConnection["user_ip"] == $user_ip;
                        if (!$rIPMatch && ipTV_lib::$settings["restrict_same_ip"]) {
                            ipTV_streaming::ClientLog($stream_id, $user_info["id"], "IP_MISMATCH", $user_ip);
                            // generateError("IP_MISMATCH");
                            http_response_code(404);
                        }
                        $rResult = $ipTV_db->query("UPDATE `lines_live` SET `hls_last_read` = ?, `hls_end` = 0, `server_id` = ?, `proxy_id` = ? WHERE `activity_id` = ?", time() - (int) ipTV_lib::$StreamingServers[SERVER_ID]["time_offset"], SERVER_ID, $rProxyID, $rConnection["activity_id"]);
                    }
                    if (!$rResult) {
                        ipTV_streaming::ClientLog($stream_id, $user_info["id"], "LINE_CREATE_FAIL", $user_ip);
                        // generateError("LINE_CREATE_FAIL");
                        http_response_code(404);
                    }
                    // ipTV_streaming::validateConnections($rUserInfo, $user_ip, $user_agent);

                    $ipTV_db->close_mysql();

                    $rHLS = ipTV_streaming::GeneratePlayListWithAuthentication($playlist, $username, $password, $uuid, $stream_id);
                    if ($rHLS) {
                        touch(CLOSE_OPEN_CONS_PATH . $uuid);
                        ob_end_clean();
                        header("Content-Type: application/x-mpegurl");
                        header("Content-Length: " . strlen($rHLS));
                        header("Cache-Control: no-store, no-cache, must-revalidate");
                        echo $rHLS;
                    } else {
                        // ipTV_streaming::ShowVideo("show_not_on_air_video", "not_on_air_video_path", $rExtension, $rUserInfo, $user_ip, $rCountryCode, $rUserInfo["con_isp_name"], SERVER_ID, $rProxyID);
                    }
                    exit;
                } else {
                    $ipTV_db->close_mysql();
                    $segment = STREAMS_PATH . str_replace(array('\\', '/'), '', urldecode(ipTV_lib::$request["segment"]));

                    $current_ts = explode("_", basename($segment));
                    if (!file_exists($segment) || $current_ts[0] != $stream_id || empty(ipTV_lib::$request["token"])) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
                        die;
                    }
                    $token = ipTV_lib::$request["token"];
                    $token_segment = md5(urldecode(ipTV_lib::$request["segment"]) . $username . $uuid . ipTV_lib::$settings["crypt_load_balancing"] . filesize($segment));
                    if ($token_segment != $token) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
                        die;
                    }
                    $size = filesize($segment);
                    header('Content-Length: ' . $size);
                    header('Content-Type: video/mp2t');
                    readfile($segment);
                }
                break;
            default:
                // generate ts stream
                $ipTV_db->query("INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%s','%s','%s')", $user_info["id"], $stream_id, SERVER_ID, $user_agent, $user_ip, $extension, getmypid(), $date, $geoip_country_code, $user_info["con_isp_name"], $external_device);
                $activity_id = $ipTV_db->last_insert_id();
                $connection_speed_file = TMP_DIR . $activity_id . ".con";
                $ipTV_db->close_mysql();
                header("Content-Type: video/mp2t");
                $segmentsOfPlaylist = ipTV_streaming::GetSegmentsOfPlaylist($playlist, ipTV_lib::$settings["client_prebuffer"]);
                if (!empty($segmentsOfPlaylist)) {
                    if (is_array($segmentsOfPlaylist)) {
                        if (ipTV_lib::$settings["restreamer_prebuffer"] == 1 && $user_info["is_restreamer"] == 1 || $user_info["is_restreamer"] == 0) {
                            $size = 0;
                            $epgStart = time();
                            foreach ($segmentsOfPlaylist as $segment) {
                                if (file_exists(STREAMS_PATH . $segment)) {
                                    $size += readfile(STREAMS_PATH . $segment);
                                } else {
                                    exit;
                                }
                            }
                            $final_time = time() - $epgStart;
                            if ($final_time == 0) {
                                $final_time = 0.1;
                            }
                            file_put_contents($connection_speed_file, intval($size / $final_time / 1024));
                        }
                        preg_match("/_(.*)\\./", array_pop($segmentsOfPlaylist), $pregmatches);
                        $current = $pregmatches[1];
                    } else {
                        $current = $segmentsOfPlaylist;
                    }
                } else {
                    if (!file_exists($playlist)) {
                        $current = -1;
                    } else {
                        exit;
                    }
                }
                $fails = 0;
                $total_failed_tries = ipTV_lib::$SegmentsSettings["seg_time"] * 2;
                while (true) {
                    $segment_file = sprintf("%d_%d.ts", $channel_info["stream_id"], $current + 1);
                    $nextsegment_file1 = sprintf("%d_%d.ts", $channel_info["stream_id"], $current + 2);
                    $totalItems = 0;
                    while (file_exists(STREAMS_PATH . $segment_file) || $totalItems > $total_failed_tries * 10) {
                        if (file_exists(STREAMS_PATH . $segment_file)) {
                            if (empty($channel_info["pid"]) && file_exists(STREAMS_PATH . $stream_id . "_.pid")) {
                                $channel_info["pid"] = intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid"));
                            }
                            // Check if the file exists
                            if (!file_exists(SIGNALS_PATH . $activity_id)) {
                                $fails = 0;
                                $time_start = time();
                                $fp = fopen(STREAMS_PATH . $segment_file, "r");

                                // Check conditions for streaming
                                if (ipTV_streaming::ps_running($channel_info["pid"], FFMPEG_PATH) && $fails <= $total_failed_tries && file_exists(STREAMS_PATH . $segment_file) && is_resource($fp)) {
                                    $sizee = filesize(STREAMS_PATH . $segment_file);
                                    $linee = $sizee - ftell($fp);

                                    // Read and output stream line
                                    if (0 < $linee) {
                                        echo stream_get_line($fp, $linee);
                                    }

                                    $final_time = time() - $time_start;
                                    if ($final_time <= 0) {
                                        $final_time = 0.1;
                                    }

                                    // Calculate and store connection speed
                                    file_put_contents($connection_speed_file, intval($sizee / 1024 / $final_time));
                                } else {
                                    // Check if user is allowed to retry or not
                                    if (!($user_info["is_restreamer"] == 1 || $total_failed_tries < $fails)) {
                                        $totalItems = 0;

                                        // Loop until conditions are met
                                        while ($totalItems > ipTV_lib::$SegmentsSettings["seg_time"] || ipTV_streaming::CheckPidChannelM3U8Exist($channel_info["pid"], $stream_id)) {
                                            if (ipTV_lib::$SegmentsSettings["seg_time"] >= $totalItems && ipTV_streaming::CheckPidChannelM3U8Exist($channel_info["pid"], $stream_id)) {
                                                $current = -2;
                                            } else {
                                                exit;
                                            }
                                        }
                                        sleep(1);
                                        if (file_exists(STREAMS_PATH . $stream_id . "_.pid")) {
                                            $channel_info["pid"] = intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid"));
                                        }
                                        $totalItems++;
                                    } else {
                                        exit;
                                    }
                                }
                                fclose($fp);
                                $fails = 0;
                                $current++;
                            } else {
                                $data = json_decode(file_get_contents(SIGNALS_PATH . $activity_id), true);
                                switch ($data["type"]) {
                                    case "signal":
                                        $totalItems = 0;
                                        while (file_exists(STREAMS_PATH . $nextsegment_file1) || $totalItems > $total_failed_tries) {
                                            ipTV_streaming::startFFMPEGSegment($data, $segment_file);
                                            $current++;
                                            break;
                                        }
                                        sleep(1);
                                        $totalItems++;
                                    case "redirect":
                                        $channel_info["stream_id"] = $data["stream_id"];
                                        $stream_id = $channel_info["stream_id"];
                                        $playlist = STREAMS_PATH . $stream_id . "_.m3u8";
                                        $channel_info["pid"] = NULL;
                                        $segmentsOfPlaylist = ipTV_streaming::GetSegmentsOfPlaylist($playlist, ipTV_lib::$settings["client_prebuffer"]);
                                        preg_match("/_(.*)\\./", array_pop($segmentsOfPlaylist), $pregmatches);
                                        $current = $pregmatches[1];
                                        break;
                                }
                                $data = NULL;
                                unlink(SIGNALS_PATH . $activity_id);
                            }
                        } else {
                            exit;
                        }
                    }
                    usleep(100000);
                    $totalItems++;
                }
        }
    } else {
        ipTV_streaming::ShowVideo($user_info["is_restreamer"], "show_not_on_air_video", "not_on_air_video_path", $extension);
    }
} else {
    ipTV_streaming::ClientLog($stream_id, 0, 'AUTH_FAILED', $user_ip);
}
function shutdown() {
    global $ipTV_db, $activity_id, $close_connection, $connection_speed_file, $user_info, $extension, $streaming_block, $stream_id, $user_agent, $user_ip, $geoip_country_code, $external_device, $date;
    if ($streaming_block) {
        CheckFlood();
        http_response_code(401);
    }
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    if ($activity_id != 0 && $close_connection) {
        ipTV_streaming::CloseAndTransfer($activity_id);
        ipTV_streaming::SaveClosedConnection(SERVER_ID, $user_info["id"], $stream_id, $date, $user_agent, $user_ip, $extension, $geoip_country_code, $user_info["con_isp_name"], $external_device);
        if (file_exists($connection_speed_file)) {
            unlink($connection_speed_file);
        }
    }
    fastcgi_finish_request();
    if ($activity_id != 0 || !file_exists(IPTV_PANEL_DIR . 'kill_pids')) {
        posix_kill(getmypid(), 9);
    }
}
