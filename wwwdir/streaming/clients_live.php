<?php
// register_shutdown_function('shutdown');
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
$geoip_country_code = ipTV_streaming::getIPInfo($user_ip)['country']['iso_code'];
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

    if (ipTV_lib::$settings["disallow_2nd_ip_con"] && !$user_info["is_restreamer"] && ($user_info["max_connections"] < ipTV_lib::$settings["disallow_2nd_ip_max"] && 0 < $user_info["max_connections"] || ipTV_lib::$settings["disallow_2nd_ip_max"] == 0)) {
        $rAcceptIP = NULL;
        if (ipTV_lib::$settings["redis_handler"]) {
            $rConnections = ipTV_streaming::getConnections($user_info["id"], true);
            if (count($rConnections) > 0) {
                $rDate = array_column($rConnections, "date_start");
                array_multisort($rDate, SORT_ASC, $rConnections);
                $rAcceptIP = $rConnections[0]["user_ip"];
            }
        } else {
            $ipTV_db->query("SELECT `user_ip` FROM `lines_live` WHERE `user_id` = ? AND `hls_end` = 0 ORDER BY `activity_id` DESC LIMIT 1;", $user_info["id"]);
            if ($ipTV_db->num_rows() == 1) {
                $rAcceptIP = $ipTV_db->get_row()["user_ip"];
            }
        }
        $rIPMatch = ipTV_lib::$settings["ip_subnet_match"] ? implode(".", array_slice(explode(".", $rAcceptIP), 0, -1)) == implode(".", array_slice(explode(".", $rIP), 0, -1)) : $rAcceptIP == $rIP;
        if ($rAcceptIP && !$rIPMatch) {
            ipTV_streaming::ClientLog($rStreamID, $user_info["id"], "USER_ALREADY_CONNECTED", $rIP);
            // ipTV_streaming::ShowVideo("show_connected_video", "connected_video_path", $rExtension, $user_info, $rIP, $rCountryCode, $user_info["con_isp_name"], $rServerID, $rProxyID);
        }
    }


    // if ($user_info["max_connections"] != 0) {
    //     if (!empty($user_info["pair_line_info"])) {
    //         if ($user_info["pair_line_info"]["max_connections"] != 0) {
    //             if ($user_info["pair_line_info"]["active_cons"] >= $user_info["pair_line_info"]["max_connections"]) {
    //                 ipTV_streaming::CloseLastCon($user_info["pair_id"], $user_info["pair_line_info"]["max_connections"]);
    //             }
    //         }
    //     }
    //     if ($user_info["active_cons"] >= $user_info["max_connections"] && $extension != 'm3u8') {
    //         ipTV_streaming::CloseLastCon($user_info["id"], $user_info["max_connections"]);
    //     }
    // }




    $channel_info = ipTV_streaming::ChannelInfo($stream_id, $extension, $user_info, $user_ip, $geoip_country_code, $external_device, $user_info["con_isp_name"], 'live');
    if ($channel_info) {
        $playlist = STREAMS_PATH . $stream_id . '_.m3u8';
        if (!ipTV_streaming::isProcessRunning($channel_info["pid"], FFMPEG_PATH)) {
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

                        $rResult = $ipTV_db->query('INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`proxy_id`,`user_agent`,`user_ip`,`container`,`pid`,`uuid`,`date_start`,`geoip_country_code`,`isp`,`external_device`,`hls_last_read`) VALUES(\'%d\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\',\'%d\',\'%d\',\'%d\')', $user_info["id"], $stream_id, SERVER_ID, $rProxyID, $user_agent, $user_ip, "hls", NULL, $uuid, time(), $geoip_country_code, $user_info["con_isp_name"], null, time() - (int) ipTV_lib::$StreamingServers[SERVER_ID]["time_offset"]);
                    } else {
                        $user_ipMatch = ipTV_lib::$settings["ip_subnet_match"] ? implode(".", array_slice(explode(".", $rConnection["user_ip"]), 0, -1)) == implode(".", array_slice(explode(".", $user_ip), 0, -1)) : $rConnection["user_ip"] == $user_ip;
                        if (!$user_ipMatch && ipTV_lib::$settings["restrict_same_ip"]) {
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
                    // ipTV_streaming::validateConnections($user_info, $user_ip, $user_agent);

                    $ipTV_db->close_mysql();

                    $rHLS = ipTV_streaming::GeneratePlayListWithAuthentication($playlist, $username, $password, $uuid, $stream_id);
                    if ($rHLS) {
                        touch(CONS_TMP_PATH . $uuid);
                        ob_end_clean();
                        header("Content-Type: application/x-mpegurl");
                        header("Content-Length: " . strlen($rHLS));
                        header("Cache-Control: no-store, no-cache, must-revalidate");
                        echo $rHLS;
                    } else {
                        // ipTV_streaming::ShowVideo("show_not_on_air_video", "not_on_air_video_path", $extension, $user_info, $user_ip, $geoip_country_code, $user_info["con_isp_name"], SERVER_ID, $rProxyID);
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
                $ipTV_db->query('SELECT `activity_id`, `pid`, `user_ip` FROM `lines_live` WHERE `uuid` = \'%d\' AND `user_id` = \'%d\' AND `server_id` = \'%s\' AND `container` = \'%d\' AND `stream_id` = \'%d\';', $uuid, $user_info["id"], SERVER_ID, $extension, $stream_id);

                if ($ipTV_db->num_rows() > 0) {
                    $rConnection = $ipTV_db->get_row();
                }



                if (!$rConnection) {
                    // if (time() > $rExpiresAt) {
                    //     generateError("TOKEN_EXPIRED");
                    // }

                    $rResult = $ipTV_db->query("INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`uuid`,`date_start`,`geoip_country_code`,`isp`,`external_device`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%d','%s','%s','%s')", $user_info["id"], $stream_id, SERVER_ID, $user_agent, $user_ip, $extension, getmypid(), $uuid, $date, $geoip_country_code, $user_info["con_isp_name"], $external_device);
                } else {
                    $user_ipMatch = ipTV_lib::$settings["ip_subnet_match"] ? implode(".", array_slice(explode(".", $rConnection["user_ip"]), 0, -1)) == implode(".", array_slice(explode(".", $user_ip), 0, -1)) : $rConnection["user_ip"] == $user_ip;
                    if (!$user_ipMatch && ipTV_lib::$settings["restrict_same_ip"]) {
                        ipTV_streaming::clientLog($stream_id, $user_info["id"], "IP_MISMATCH", $user_ip);
                        // generateError("IP_MISMATCH");
                    }
                    if (ipTV_streaming::isProcessRunning($rConnection["pid"], "php-fpm") && $rPID != $rConnection["pid"] && is_numeric($rConnection["pid"]) && 0 < $rConnection["pid"]) {
                        posix_kill((int) $rConnection["pid"], 9);
                    }

                    $rResult = $ipTV_db->query("UPDATE `lines_live` SET `hls_end` = 0, `hls_last_read` = ?, `pid` = ? WHERE `activity_id` = ?;", time() - (int) ipTV_lib::$StreamingServers[SERVER_ID]["time_offset"], $rPID, $rConnection["activity_id"]);
                }

                if (!$rResult) {
                    ipTV_streaming::clientLog($stream_id, $user_info["id"], "LINE_CREATE_FAIL", $user_ip);
                    // generateError("LINE_CREATE_FAIL");
                }
                // ipTV_streaming::validateConnections($user_info, $user_ip, $user_agent);

                $ipTV_db->close_mysql();

                $rCloseCon = true;
                // if (ipTV_lib::$settings["monitor_connection_status"]) {
                //     ob_implicit_flush(true);
                //     while (ob_get_level()) {
                //         ob_end_clean();
                //     }
                // }
                touch(CONS_TMP_PATH . $uuid);

                header("Content-Type: video/mp2t");
                $rConSpeedFile = DIVERGENCE_TMP_PATH . $uuid;
                if (file_exists($playlist)) {
                    if ($user_info["is_restreamer"]) {
                        if ($rTokenData["prebuffer"]) {
                            $rPrebuffer = ipTV_lib::$SegmentsSettings["seg_time"];
                        } else {
                            $rPrebuffer = ipTV_lib::$settings["restreamer_prebuffer"];
                        }
                    } else {
                        $rPrebuffer = ipTV_lib::$settings["client_prebuffer"];
                    }
                    if (file_exists(STREAMS_PATH . $stream_id . "_.dur")) {
                        $rDuration = (int) file_get_contents(STREAMS_PATH . $stream_id . "_.dur");
                        if ($rDuration > ipTV_lib::$SegmentsSettings["seg_time"]) {
                            ipTV_lib::$SegmentsSettings["seg_time"] = $rDuration;
                        }
                    }
                    $rSegments = ipTV_streaming::GetSegmentsOfPlaylist($playlist, $rPrebuffer, ipTV_lib::$SegmentsSettings["seg_time"]);
                } else {
                    $rSegments = NULL;
                }
                if (!is_null($rSegments)) {
                    if (is_array($rSegments)) {
                        $rBytes = 0;
                        $rStartTime = time();
                        foreach ($rSegments as $rSegment) {
                            if (file_exists(STREAMS_PATH . $rSegment)) {
                                $rBytes .= readfile(STREAMS_PATH . $rSegment);
                            } else {
                                exit;
                            }
                        }
                        $rTotalTime = time() - $rStartTime;
                        if ($rTotalTime == 0) {
                            $rTotalTime = 0;
                        }
                        $rDivergence = (int) ($rBytes / $rTotalTime / 1024);
                        file_put_contents($rConSpeedFile, $rDivergence);
                        preg_match("/_(.*)\\./", array_pop($rSegments), $rCurrentSegment);
                        $rCurrent = $rCurrentSegment[1];
                    } else {
                        $rCurrent = $rSegments;
                    }
                } else {
                    if (!file_exists($playlist)) {
                        $rCurrent = -1;
                    } else {
                        exit;
                    }
                }
                $rFails = 0;
                $rTotalFails = ipTV_lib::$SegmentsSettings["seg_time"] * 2;
                $segment_wait_time = 20; //var in bd
                if ($rTotalFails < (int) $segment_wait_time ?: 20) {
                    $rTotalFails = (int) $segment_wait_time ?: 20;
                }
                $rMonitorCheck = $rLastCheck = time();
                while (true) {
                    $rSegmentFile = sprintf("%d_%d.ts", $channel_info["stream_id"], $rCurrent + 1);
                    $rNextSegment = sprintf("%d_%d.ts", $channel_info["stream_id"], $rCurrent + 2);
                    for ($rChecks = 0; !file_exists(STREAMS_PATH . $rSegmentFile) && $rChecks < $rTotalFails; $rChecks++) {
                        sleep(1);
                    }
                    if (file_exists(STREAMS_PATH . $rSegmentFile)) {
                        if (file_exists(SIGNALS_PATH . $uuid)) {
                            $rSignalData = json_decode(file_get_contents(SIGNALS_PATH . $uuid), true);
                            if ($rSignalData["type"] == "signal") {
                                for ($rChecks = 0; !file_exists(STREAMS_PATH . $rNextSegment) && $rChecks < $rTotalFails; $rChecks++) {
                                    sleep(1);
                                }
                                ipTV_streaming::sendSignalFFMPEG($rSignalData, $rSegmentFile, $rVideoCodec ?: "h264");
                                unlink(SIGNALS_PATH . $uuid);
                                $rCurrent++;
                            }
                        }
                        $rFails = 0;
                        $rTimeStart = time();
                        $rFP = fopen(STREAMS_PATH . $rSegmentFile, "r");
                        while ($rFails < $rTotalFails && !file_exists(STREAMS_PATH . $rNextSegment)) {
                            $rData = stream_get_line($rFP, ipTV_lib::$settings["read_buffer_size"]);
                            if (!empty($rData)) {
                                echo $rData;
                                $rData = "";
                                $rFails = 0;
                            } else {
                                if (ipTV_streaming::isStreamRunning($channel_info["pid"], $stream_id)) {
                                    sleep(1);
                                    $rFails++;
                                }
                            }
                        }
                        if (ipTV_streaming::isStreamRunning($channel_info["pid"], $stream_id) && $rFails < $rTotalFails && file_exists(STREAMS_PATH . $rSegmentFile) && is_resource($rFP)) {
                            $rSegmentSize = filesize(STREAMS_PATH . $rSegmentFile);
                            $rRestSize = $rSegmentSize - ftell($rFP);
                            if ($rRestSize > 0) {
                                echo stream_get_line($rFP, $rRestSize);
                            }
                            $rTotalTime = time() - $rTimeStart;
                            if (0 > $rTotalTime) {
                                $rTotalTime = 0;
                            }
                            file_put_contents($rConSpeedFile, (int) ($rSegmentSize / 1024 / $rTotalTime));
                        } else {
                            if (!($user_info["is_restreamer"] == 1 || $rTotalFails < $rFails)) {
                                for ($rChecks = 0; $rChecks < ipTV_lib::$SegmentsSettings["seg_time"] && !ipTV_streaming::isStreamRunning($channel_info["pid"], $stream_id); $rChecks++) {
                                    if (file_exists(STREAMS_PATH . $stream_id . "_.pid")) {
                                        $channel_info["pid"] = (int) file_get_contents(STREAMS_PATH . $stream_id . "_.pid");
                                    }
                                }
                                sleep(1);
                                if ($rChecks < ipTV_lib::$SegmentsSettings["seg_time"] && ipTV_streaming::isStreamRunning($channel_info["pid"], $stream_id)) {
                                    if (!file_exists(STREAMS_PATH . $rNextSegment)) {
                                        $rCurrent = -2;
                                    }
                                } else {
                                    exit;
                                }
                            } else {
                                exit;
                            }
                        }
                        fclose($rFP);
                        $rFails = 0;
                        $rCurrent++;
                        // if (ipTV_lib::$settings["monitor_connection_status"] && 5 < time() - $rMonitorCheck) {
                        //     if (connection_status() == CONNECTION_NORMAL) {
                        //         $rMonitorCheck = time();
                        //     } else {
                        //         exit;
                        //     }
                        // }
                        if (time() - $rLastCheck > 300) {
                            $rLastCheck = time();
                            $rConnection = NULL;

                            $ipTV_db->db_connect();
                            $ipTV_db->query("SELECT `pid`, `hls_end` FROM `lines_live` WHERE `uuid` = ?", $uuid);
                            if ($ipTV_db->num_rows() == 1) {
                                $rConnection = $ipTV_db->get_row();
                            }
                            $ipTV_db->close_mysql();

                            if (!is_array($rConnection) || $rConnection["hls_end"] != 0 || $rConnection["pid"] != $rPID) {
                                exit;
                            }
                        }
                    } else {
                        exit;
                    }
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
