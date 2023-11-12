<?php

register_shutdown_function('shutdown');
set_time_limit(0);
require '../init.php';
$f0ac6ad2b40669833242a10c23cad2e0 = true;
if (isset(ipTV_lib::$request["qs"])) {
    if (stristr(ipTV_lib::$request["qs"], ':p=')) {
        $Af236a5462da6c610990628f594f801e = explode(':p=', ipTV_lib::$request["qs"]);
        ipTV_lib::$request["password"] = $Af236a5462da6c610990628f594f801e[1];
        ipTV_lib::$request["username"] = substr($Af236a5462da6c610990628f594f801e[0], 2);
    }
}
if (!isset(ipTV_lib::$request["extension"]) || !isset(ipTV_lib::$request["username"]) || !isset(ipTV_lib::$request["password"]) || !isset(ipTV_lib::$request["stream"])) {
    die;
}
$geolite2 = new Reader(GEOIP2_FILENAME);
$activity_id = 0;
$close_connection = true;
$connection_speed_file = null;
$user_ip = ipTV_streaming::getUserIP();
$user_agent = empty($_SERVER["HTTP_USER_AGENT"]) ? '' : htmlentities(trim($_SERVER["HTTP_USER_AGENT"]));
$external_device = null;
$username = ipTV_lib::$request["username"];
$password = ipTV_lib::$request["password"];
$stream_id = intval(ipTV_lib::$request["stream"]);
$extension = preg_replace('/[^A-Za-z0-9 ]/', '', trim(ipTV_lib::$request["extension"]));
$date = time();
if (ipTV_lib::$settings["use_buffer"] == 0) {
    header('X-Accel-Buffering: no');
}

header('Access-Control-Allow-Origin: *');
$play_token = empty(ipTV_lib::$request["play_token"]) ? null : ipTV_lib::$request["play_token"];
if ($user_info = ipTV_streaming::GetUserInfo(null, $username, $password, true, false, true, array(), false, $user_ip, $user_agent, array(), $play_token, $stream_id)) {

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
    $country_code = $geolite2->getWithPrefixLen($user_ip)[0]["registered_country"]["iso_code"];
    $geolite2->close();
    if (!empty($user_info["allowed_ips"]) && !in_array($user_ip, array_map('gethostbyname', $user_info["allowed_ips"]))) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'IP_BAN', $user_ip);
        die;
    }
    if (empty($country_code)) {
        if (empty($user_info["forced_country"])) {
            ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'COUNTRY_DISALLOW', $user_ip);
            die;
        }
    }
    $ab59908f6050f752836a953eb8bb8e52 = !empty($user_info["forced_country"]) ? true : false;
    if ($ab59908f6050f752836a953eb8bb8e52 && $user_info["forced_country"] != 'ALL' && $country_code != $user_info["forced_country"]) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], 'COUNTRY_DISALLOW', $user_ip);
        die;
    }
    if (!$ab59908f6050f752836a953eb8bb8e52 && !in_array("ALL", ipTV_lib::$settings["allow_countries"]) && !in_array($country_code, ipTV_lib::$settings["allow_countries"])) {
        ipTV_streaming::ClientLog($stream_id, $user_info["id"], "COUNTRY_DISALLOW", $user_ip);
        die;
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
    $f0ac6ad2b40669833242a10c23cad2e0 = false;
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
    $chanel_info = ipTV_streaming::ChannelInfo($stream_id, $extension, $user_info, $user_ip, $country_code, $external_device, $user_info["con_isp_name"], 'live');
    if ($chanel_info) {
        $playlist = STREAMS_PATH . $stream_id . '_.m3u8';
        if (!ipTV_streaming::ps_running($chanel_info["pid"], FFMPEG_PATH)) {
            if ($chanel_info["on_demand"] == 1) {
                if (!ipTV_streaming::CheckPidExist($chanel_info["monitor_pid"], $stream_id)) {
                    ipTV_stream::startStream($stream_id);
                }
            } else {
                ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_not_on_air_video', 'not_on_air_video_path', $extension);
            }
        }
        switch ($extension) {
            case 'm3u8':
                $e84deaa90130ae0163381d3f216773e3 = false;
                $B1772eb944c03052cd5d180cdee51b89 = 0;
                a5783fd272d37bf2cf23d06cadf2c0b5:
                if (!(!file_exists($playlist) && $B1772eb944c03052cd5d180cdee51b89 <= 20)) {
                    if (!($B1772eb944c03052cd5d180cdee51b89 == 20)) {
                        if (empty(ipTV_lib::$request["segment"])) {
                            $ipTV_db->query("SELECT activity_id,hls_end FROM `user_activity_now` WHERE `user_id` = '%d' AND `server_id` = '%d' AND `container` = 'hls' AND `user_ip` = '%s' AND `user_agent` = '%s' AND `stream_id` = '%d'", $user_info["id"], SERVER_ID, $f4889efa84e1f2e30e5e9780973f68cb, $userAgent, $stream_id);
                            if ($ipTV_db->num_rows() == 0) {
                                if ($user_info["max_connections"] != 0) {
                                    $ipTV_db->query("UPDATE `user_activity_now` SET `hls_end` = 1 WHERE `user_id` = '%d' AND `container` = 'hls'", $user_info["id"]);
                                }
                                $ipTV_db->query("INSERT INTO `user_activity_now` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`,`hls_last_read`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%s','%s','%s','%d')", $user_info["id"], $stream_id, SERVER_ID, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, "hls", getmypid(), $a7e968a4f6d75092e74cdeb1b406041a, $A75f2436a5614184bfe3442ddd050ec5, $user_info["con_isp_name"], $a349f0750f0a814bd31ec4b3da51da95, time());
                                $activity_id = $ipTV_db->last_insert_id();
                                goto fe1ec2bc8c62ceae9fe6f8e0d06d6208;
                            }
                            $user_activity_now = $ipTV_db->get_row();
                            if (!($user_activity_now["hls_end"] == 1)) {
                                $activity_id = $user_activity_now["activity_id"];
                                $ipTV_db->query("UPDATE `user_activity_now` SET `hls_last_read` = '%d' WHERE `activity_id` = '%d'", time(), $user_activity_now["activity_id"]);
                                fe1ec2bc8c62ceae9fe6f8e0d06d6208:
                                $ipTV_db->close_mysql();
                                if ($F3803fa85b38b65447e6d438f8e9176a = ipTV_streaming::GeneratePlayListWithAuthentication($playlist, $username, $password, $stream_id)) {
                                    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                                    header("Content-Type: application/x-mpegurl");
                                    header("Content-Length: " . strlen($F3803fa85b38b65447e6d438f8e9176a));
                                    header("Cache-Control: no-store, no-cache, must-revalidate");
                                    echo $F3803fa85b38b65447e6d438f8e9176a;
                                }
                                die;
                            }
                            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
                            die;
                        }
                        $ipTV_db->close_mysql();
                        $fe9d0d199fc51f64065055d8bcade279 = STREAMS_PATH . str_replace(array("\\", "/"), '', urldecode(ipTV_lib::$request["segment"]));
                        $ff808659f878dbd58bfa6fabe039f10c = explode("_", basename($fe9d0d199fc51f64065055d8bcade279));
                        if (!(!file_exists($fe9d0d199fc51f64065055d8bcade279) || $ff808659f878dbd58bfa6fabe039f10c[0] != $stream_id || empty(ipTV_lib::$request["token"]))) {
                            $accessToken = ipTV_lib::$request["token"];
                            $A0450eaeae72ee603999aa268ea82b0c = md5(urldecode(ipTV_lib::$request["segment"]) . $user_info["username"] . ipTV_lib::$settings["crypt_load_balancing"] . filesize($fe9d0d199fc51f64065055d8bcade279));
                            if (!($A0450eaeae72ee603999aa268ea82b0c != $accessToken)) {
                                $e13ac89e162bcc9913e553b949f755b6 = filesize($fe9d0d199fc51f64065055d8bcade279);
                                header("Content-Length: " . $e13ac89e162bcc9913e553b949f755b6);
                                header("Content-Type: video/mp2t");
                                readfile($fe9d0d199fc51f64065055d8bcade279);
                                goto a1a191cea5b5ee867ae84b6dda4fbdb2;
                            }
                            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
                            die;
                        }
                        header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
                        die;
                    }
                    die;
                }
                usleep(500000);
                ++$B1772eb944c03052cd5d180cdee51b89;
                goto a5783fd272d37bf2cf23d06cadf2c0b5;
            default:
                $ipTV_db->query("INSERT INTO `user_activity_now` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%s','%s','%s')", $user_info["id"], $stream_id, SERVER_ID, $user_agent, $user_ip, $extension, getmypid(), $date, $country_code, $user_info["con_isp_name"], $external_device);
                $activity_id = $ipTV_db->num_rows();
                $connection_speed_file = TMP_DIR . $activity_id . ".con";
                $ipTV_db->close_mysql();
                header("Content-Type: video/mp2t");
                $C325d28e238c3a646bd7b095aa1ffa85 = ipTV_streaming::GetSegmentsOfPlaylist($playlist, ipTV_lib::$settings["client_prebuffer"]);
                if (!empty($C325d28e238c3a646bd7b095aa1ffa85)) {
                    if (is_array($C325d28e238c3a646bd7b095aa1ffa85)) {
                        if (!(ipTV_lib::$settings["restreamer_prebuffer"] == 1 && $user_info["is_restreamer"] == 1 || $user_info["is_restreamer"] == 0)) {
                            goto ad65a7596777f3252e1e3748791b1431;
                        }
                        $e13ac89e162bcc9913e553b949f755b6 = 0;
                        $A73d5129dfb465fd94f3e09e9b179de0 = time();
                        foreach ($C325d28e238c3a646bd7b095aa1ffa85 as $fe9d0d199fc51f64065055d8bcade279) {
                            if (file_exists(STREAMS_PATH . $fe9d0d199fc51f64065055d8bcade279)) {
                                $e13ac89e162bcc9913e553b949f755b6 += readfile(STREAMS_PATH . $fe9d0d199fc51f64065055d8bcade279);
                            }
                            die;
                        }
                        $D6db7e73f7da5e54d965f7ef1c369bd6 = time() - $A73d5129dfb465fd94f3e09e9b179de0;
                        if (!($D6db7e73f7da5e54d965f7ef1c369bd6 == 0)) {
                            goto Ebf15b0d0d17ce1ef777616d5204c03f;
                        }
                        $D6db7e73f7da5e54d965f7ef1c369bd6 = 0.1;
                        Ebf15b0d0d17ce1ef777616d5204c03f:
                        file_put_contents($connection_speed_file, intval($e13ac89e162bcc9913e553b949f755b6 / $D6db7e73f7da5e54d965f7ef1c369bd6 / 1024));
                        ad65a7596777f3252e1e3748791b1431:
                        preg_match("/_(.*)\\./", array_pop($C325d28e238c3a646bd7b095aa1ffa85), $adb24597b0e7956b0f3baad7c260916d);
                        $E76c20c612d64210f5bcc0611992d2f7 = $adb24597b0e7956b0f3baad7c260916d[1];
                        goto B3376bd613b7c7b3e2e13b2dba740e4f;
                    }
                    $E76c20c612d64210f5bcc0611992d2f7 = $C325d28e238c3a646bd7b095aa1ffa85;
                    B3376bd613b7c7b3e2e13b2dba740e4f:
                    goto f4a60f5a64a086fc0304bf38dd04c18d;
                }
                if (!file_exists($playlist)) {
                    $E76c20c612d64210f5bcc0611992d2f7 = -1;
                    f4a60f5a64a086fc0304bf38dd04c18d:
                    $c45cc215a073632a9e20d474ea91f7e3 = 0;
                    $f065eccc0636f7fd92043c7118f7409b = ipTV_lib::$SegmentsSettings["seg_time"] * 2;
                    ec83cd6ff50c6b79e6b8cffbb78eecbf:
                    if (!true) {
                    }
                    $c5f97e03cbf94a57a805526a8288042f = sprintf("%d_%d.ts", $chanel_info["stream_id"], $E76c20c612d64210f5bcc0611992d2f7 + 1);
                    $Bf3da9b14ae368d39b642b3f83d656fc = sprintf("%d_%d.ts", $chanel_info["stream_id"], $E76c20c612d64210f5bcc0611992d2f7 + 2);
                    $a88c8d86d7956601164a5f156d5df985 = 0;
                    Cf93be3ee45266203c1bef9fbf92206a:
                    if (!(!file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f) && $a88c8d86d7956601164a5f156d5df985 <= $f065eccc0636f7fd92043c7118f7409b * 10)) {
                        if (file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f)) {
                            if (!(empty($chanel_info["pid"]) && file_exists(STREAMS_PATH . $stream_id . "_.pid"))) {
                                goto ad53cf2275793650541bcbe2fded0fd6;
                            }
                            $chanel_info["pid"] = intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid"));
                            ad53cf2275793650541bcbe2fded0fd6:
                            if (!file_exists(SIGNALS_PATH . $activity_id)) {
                                $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                $c41986ad785eace90882e61c64cabb41 = time();
                                $Ab9f45b38498c3a010f3c4276ad5767c = fopen(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f, "r");
                                Cec1b4b5d1ec19950895bdff075c35b9:
                                if (!($c45cc215a073632a9e20d474ea91f7e3 <= $f065eccc0636f7fd92043c7118f7409b && !file_exists(STREAMS_PATH . $Bf3da9b14ae368d39b642b3f83d656fc))) {
                                    goto ef0705fe07490d2e2ab41bcda87af246;
                                }
                                $d76067cf9572f7a6691c85c12faf2a29 = stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, ipTV_lib::$settings["read_buffer_size"]);
                                if (!empty($d76067cf9572f7a6691c85c12faf2a29)) {
                                    echo $d76067cf9572f7a6691c85c12faf2a29;
                                    $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                    goto Cec1b4b5d1ec19950895bdff075c35b9;
                                }
                                if (ipTV_streaming::ps_running($chanel_info["pid"], FFMPEG_PATH)) {
                                    sleep(1);
                                    ++$c45cc215a073632a9e20d474ea91f7e3;
                                    goto Cec1b4b5d1ec19950895bdff075c35b9;
                                }
                                ef0705fe07490d2e2ab41bcda87af246:
                                if (ipTV_streaming::ps_running($chanel_info["pid"], FFMPEG_PATH) && $c45cc215a073632a9e20d474ea91f7e3 <= $f065eccc0636f7fd92043c7118f7409b && file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f) && is_resource($Ab9f45b38498c3a010f3c4276ad5767c)) {
                                    $F19b64ffad55876d290cb6f756a2dea5 = filesize(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f);
                                    $C73fe796a6baad7ca2e4251886562ef0 = $F19b64ffad55876d290cb6f756a2dea5 - ftell($Ab9f45b38498c3a010f3c4276ad5767c);
                                    if (!($C73fe796a6baad7ca2e4251886562ef0 > 0)) {
                                        goto Ce918f8fde55dedaced62b39f0728c56;
                                    }
                                    echo stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, $C73fe796a6baad7ca2e4251886562ef0);
                                    Ce918f8fde55dedaced62b39f0728c56:
                                    $D6db7e73f7da5e54d965f7ef1c369bd6 = time() - $c41986ad785eace90882e61c64cabb41;
                                    if (!($D6db7e73f7da5e54d965f7ef1c369bd6 <= 0)) {
                                        goto E2dc5f6f12f0224820bb5d48bc17b5db;
                                    }
                                    $D6db7e73f7da5e54d965f7ef1c369bd6 = 0.1;
                                    E2dc5f6f12f0224820bb5d48bc17b5db:
                                    file_put_contents($connection_speed_file, intval($F19b64ffad55876d290cb6f756a2dea5 / 1024 / $D6db7e73f7da5e54d965f7ef1c369bd6));
                                    goto A68eb1112c91909740ce1cfbc1a210ce;
                                }
                                if (!($user_info["is_restreamer"] == 1 || $c45cc215a073632a9e20d474ea91f7e3 > $f065eccc0636f7fd92043c7118f7409b)) {
                                    $a88c8d86d7956601164a5f156d5df985 = 0;
                                    F71d17aeef5dd4b69cc7d2e4bdabbeba:
                                    if (!($a88c8d86d7956601164a5f156d5df985 <= ipTV_lib::$SegmentsSettings["seg_time"] && !ipTV_streaming::CheckPidChannelM3U8Exist($chanel_info["pid"], $stream_id))) {
                                        if (!($a88c8d86d7956601164a5f156d5df985 > ipTV_lib::$SegmentsSettings["seg_time"] || !ipTV_streaming::CheckPidChannelM3U8Exist($chanel_info["pid"], $stream_id))) {
                                            $E76c20c612d64210f5bcc0611992d2f7 = -2;
                                            A68eb1112c91909740ce1cfbc1a210ce:
                                            fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                                            $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                            $E76c20c612d64210f5bcc0611992d2f7++;
                                            goto ec83cd6ff50c6b79e6b8cffbb78eecbf;
                                        }
                                        die;
                                    }
                                    sleep(1);
                                    if (!file_exists(STREAMS_PATH . $stream_id . "_.pid")) {
                                        goto Fd5fcda6b37e5a5e5c144e72bba0f3e7;
                                    }
                                    $chanel_info["pid"] = intval(file_get_contents(STREAMS_PATH . $stream_id . "_.pid"));
                                    Fd5fcda6b37e5a5e5c144e72bba0f3e7:
                                    ++$a88c8d86d7956601164a5f156d5df985;
                                    goto F71d17aeef5dd4b69cc7d2e4bdabbeba;
                                }
                                die;
                            }
                            $d38a1c3d822bdbbd61f649f33212ebde = json_decode(file_get_contents(SIGNALS_PATH . $activity_id), true);
                            switch ($d38a1c3d822bdbbd61f649f33212ebde["type"]) {
                                case "signal":
                                    $a88c8d86d7956601164a5f156d5df985 = 0;
                                    bebebcdc24b95d7496a99323abc492f0:
                                    if (!(!file_exists(STREAMS_PATH . $Bf3da9b14ae368d39b642b3f83d656fc) && $a88c8d86d7956601164a5f156d5df985 <= $f065eccc0636f7fd92043c7118f7409b)) {
                                        ipTV_streaming::startFFMPEGSegment($d38a1c3d822bdbbd61f649f33212ebde, $c5f97e03cbf94a57a805526a8288042f);
                                        ++$E76c20c612d64210f5bcc0611992d2f7;
                                        goto f1e4c23da8d982d6100119195ce48da9;
                                    }
                                    sleep(1);
                                    ++$a88c8d86d7956601164a5f156d5df985;
                                    goto bebebcdc24b95d7496a99323abc492f0;
                                case "redirect":
                                    $stream_id = $chanel_info["stream_id"] = $d38a1c3d822bdbbd61f649f33212ebde["stream_id"];
                                    $playlist = STREAMS_PATH . $stream_id . "_.m3u8";
                                    $chanel_info["pid"] = null;
                                    $C325d28e238c3a646bd7b095aa1ffa85 = ipTV_streaming::GetSegmentsOfPlaylist($playlist, ipTV_lib::$settings["client_prebuffer"]);
                                    preg_match("/_(.*)\\./", array_pop($C325d28e238c3a646bd7b095aa1ffa85), $adb24597b0e7956b0f3baad7c260916d);
                                    $E76c20c612d64210f5bcc0611992d2f7 = $adb24597b0e7956b0f3baad7c260916d[1];
                                    goto f1e4c23da8d982d6100119195ce48da9;
                            }
                            f1e4c23da8d982d6100119195ce48da9:
                            $d38a1c3d822bdbbd61f649f33212ebde = null;
                            unlink(SIGNALS_PATH . $activity_id);
                            goto ec83cd6ff50c6b79e6b8cffbb78eecbf;
                        }
                        die;
                    }
                    usleep(100000);
                    ++$a88c8d86d7956601164a5f156d5df985;
                    goto Cf93be3ee45266203c1bef9fbf92206a;
                }
                die;
        }
        a1a191cea5b5ee867ae84b6dda4fbdb2:
        goto E7de94241f1efed6db3bb965612215a4;
    }
    ipTV_streaming::ShowVideo($user_info["is_restreamer"], "show_not_on_air_video", "not_on_air_video_path", $extension);
    E7de94241f1efed6db3bb965612215a4:
} else {
    ipTV_streaming::ClientLog($stream_id, 0, 'AUTH_FAILED', $user_ip);
}
function shutdown() {
    global $ipTV_db, $activity_id, $close_connection, $connection_speed_file, $user_info, $extension, $f0ac6ad2b40669833242a10c23cad2e0, $stream_id, $user_agent, $user_ip, $country_code, $external_device, $date;
    if ($f0ac6ad2b40669833242a10c23cad2e0) {
        CheckFlood();
        http_response_code(401);
    }
    $ipTV_db->close_mysql();
    if ($activity_id != 0 && $close_connection) {
        ipTV_streaming::CloseAndTransfer($activity_id);
        ipTV_streaming::SaveClosedConnection(SERVER_ID, $user_info["id"], $stream_id, $date, $user_agent, $user_ip, $extension, $country_code, $user_info["con_isp_name"], $external_device);
        if (file_exists($connection_speed_file)) {
            unlink($connection_speed_file);
        }
    }
    fastcgi_finish_request();
    if ($activity_id != 0 || !file_exists(IPTV_PANEL_DIR . 'kill_pids')) {
        posix_kill(getmypid(), 9);
    }
}
