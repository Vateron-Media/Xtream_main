<?php
/*Rev:26.09.18r0*/

class ipTV_streaming {
    public static $ipTV_db;
    public static $AllowedIPs = array();
    public static function RtmpIps() {
        self::$ipTV_db->query("SELECT `ip` FROM `rtmp_ips`");
        return array_merge(array("127.0.0.1"), array_map("gethostbyname", ipTV_lib::array_values_recursive(self::$ipTV_db->get_rows())));
    }
    public static function startFFMPEGSegment($data, $segment_file) {
        if (empty($data["xy_offset"])) {
            $x = rand(150, 380);
            $y = rand(110, 250);
        } else {
            list($x, $y) = explode("x", $data["xy_offset"]);
        }
        passthru(FFMPEG_PATH . ' -nofix_dts -fflags +igndts -copyts -vsync 0 -nostats -nostdin -hide_banner -loglevel quiet -y -i "' . STREAMS_PATH . $segment_file . '" -filter_complex "drawtext=fontfile=' . FFMPEG_FONTS_PATH . ":text='{$data["message"]}':fontsize={$data["font_size"]}:x={$x}:y={$y}:fontcolor={$data["font_color"]}\" -map 0 -vcodec libx264 -preset ultrafast -acodec copy -scodec copy -mpegts_flags +initial_discontinuity -mpegts_copyts 1 -f mpegts -");
        return true;
    }
    public static function getAllowedIPsCloudIps() {
        $ips = array("127.0.0.1", $_SERVER["SERVER_ADDR"]);
        if (!file_exists(TMP_DIR . "cloud_ips") || time() - filemtime(TMP_DIR . "cloud_ips") >= 600) {
            $contents = ipTV_lib::SimpleWebGet("http://xtream-codes.com/cloud_ips");
            if (!empty($contents)) {
                file_put_contents(TMP_DIR . "cloud_ips", $contents);
            }
        }
        if (file_exists(TMP_DIR . "cloud_ips")) {
            $ips = array_filter(array_merge($ips, array_map('trim', file(TMP_DIR . 'cloud_ips'))));
        }
        return array_unique($ips);
    }
    public static function getAllowedIPsAdmin($reg_users = false) {
        if (!empty(self::$AllowedIPs)) {
            return self::$AllowedIPs;
        }
        $ips = array("127.0.0.1", $_SERVER["SERVER_ADDR"]);
        foreach (ipTV_lib::$StreamingServers as $server_id => $server) {
            if (!empty($server["whitelist_ips"])) {
                $ips = array_merge($ips, json_decode($server["whitelist_ips"], true));
            }
            $ips[] = $server["server_ip"];
            $ips[] = $server["server_ip"];
        }
        if ($reg_users) {
            if (!empty(ipTV_lib::$settings["allowed_ips_admin"])) {
                $ips = array_merge($ips, explode(",", ipTV_lib::$settings["allowed_ips_admin"]));
            }
            self::$ipTV_db->query("SELECT * FROM `xtream_main` WHERE id = 1");
            $xtream_main = self::$ipTV_db->get_row();
            if (!empty($xtream_main["root_ip"])) {
                $ips[] = $xtream_main["root_ip"];
            }
            self::$ipTV_db->query('SELECT DISTINCT t1.`ip` FROM `reg_users` t1 INNER JOIN `member_groups` t2 ON t2.group_id = t1.member_group_id AND t2.is_admin = 1 AND t1.`last_login` >= \'%d\'', strtotime('-2 hour'));
            $UsersIP = ipTV_lib::array_values_recursive(self::$ipTV_db->get_rows());
            $ips = array_merge($ips, $UsersIP);
        }
        if (!file_exists(TMP_DIR . "cloud_ips") || time() - filemtime(TMP_DIR . "cloud_ips") >= 600) {
            $contents = ipTV_lib::SimpleWebGet("http://xtream-codes.com/cloud_ips");
            if (!empty($contents)) {
                file_put_contents(TMP_DIR . "cloud_ips", $contents);
            }
        }
        if (file_exists(TMP_DIR . "cloud_ips")) {
            $ips = array_filter(array_merge($ips, array_map("trim", file(TMP_DIR . "cloud_ips"))));
        }
        self::$AllowedIPs = $ips;
        return array_unique($ips);
    }
    public static function CloseAndTransfer($activity_id) {
        file_put_contents(CLOSE_OPEN_CONS_PATH . $activity_id, 1);
    }
    public static function GetStreamData($stream_id) {
        if (CACHE_STREAMS) {
            if (file_exists(TMP_DIR . $stream_id . "_cacheStream") && time() - filemtime(TMP_DIR . $stream_id . "_cacheStream") <= CACHE_STREAMS_TIME) {
                return unserialize(file_get_contents(TMP_DIR . $stream_id . "_cacheStream"));
            }
        }
        $output = array();
        self::$ipTV_db->query('SELECT * FROM `streams` t1 LEFT JOIN `streams_types` t2 ON t2.type_id = t1.type WHERE t1.`id` = \'%d\'', $stream_id);
        if (self::$ipTV_db->num_rows() > 0) {
            $streamData = self::$ipTV_db->get_row();
            $servers = array();
            if ($streamData["direct_source"] == 0) {
                self::$ipTV_db->query('SELECT * FROM `streams_sys` WHERE `stream_id` = \'%d\'', $stream_id);
                if (self::$ipTV_db->num_rows() > 0) {
                    $servers = self::$ipTV_db->get_rows(true, "server_id");
                }
            }
            $output["info"] = $streamData;
            $output["servers"] = $servers;
            if (CACHE_STREAMS) {
                file_put_contents(TMP_DIR . $stream_id . "_cacheStream", serialize($output), LOCK_EX);
            }
        }
        return !empty($output) ? $output : false;
    }
    public static function ChannelInfo($stream_id, $extension, $user_info, $user_ip, $geoip_country_code, $external_device = '', $con_isp_name = '', $type) {
        if (!($type == "archive")) {
            $stream = self::GetStreamData($stream_id);
            if (!empty($stream)) {
                if (!($stream["info"]["direct_source"] == 1)) {
                    $StreamSysIds = array();
                    foreach (ipTV_lib::$StreamingServers as $B5d03ddefb862a50fd6abc8561488d01 => $C3af9fee694e49882d2d0c32f538efc8) {
                        if (!(!array_key_exists($B5d03ddefb862a50fd6abc8561488d01, $stream["servers"]) || !ipTV_lib::$StreamingServers[$B5d03ddefb862a50fd6abc8561488d01]["server_online"])) {
                            if ($type == "movie") {
                                if (!(!empty($stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["pid"]) && $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["to_analyze"] == 0 && $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["stream_status"] == 0 && $C3af9fee694e49882d2d0c32f538efc8["timeshift_only"] == 0)) {
                                    goto e98c598ed9f55756c7dfdfe8d7fcbbe7;
                                }
                                $StreamSysIds[] = $B5d03ddefb862a50fd6abc8561488d01;
                                e98c598ed9f55756c7dfdfe8d7fcbbe7:
                                goto B342294113c973e85af387b1da673eaa;
                            }
                            if (!(($stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["on_demand"] == 1 && $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["pid"] >= 0 && $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["stream_status"] == 0 || $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["pid"] > 0 && $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["stream_status"] == 0) && $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["to_analyze"] == 0 && time() >= (int) $stream["servers"][$B5d03ddefb862a50fd6abc8561488d01]["delay_available_at"] && $C3af9fee694e49882d2d0c32f538efc8["timeshift_only"] == 0)) {
                                goto f7fc5cbddb27503b0f07d4f00f75a612;
                            }
                            $StreamSysIds[] = $B5d03ddefb862a50fd6abc8561488d01;
                            f7fc5cbddb27503b0f07d4f00f75a612:
                            B342294113c973e85af387b1da673eaa:
                            goto b6e86aab7f8b19b2d92db1fe3fa4e42d;
                        }
                        b6e86aab7f8b19b2d92db1fe3fa4e42d:
                    }
                    if (!empty($StreamSysIds)) {
                        $servers = array();
                        if (ipTV_lib::$settings["online_capacity_interval"] != 0 && file_exists(TMP_DIR . "servers_capacity") && time() - filemtime(TMP_DIR . "servers_capacity") <= ipTV_lib::$settings["online_capacity_interval"]) {
                            $rows = json_decode(file_get_contents(TMP_DIR . "servers_capacity"), true);
                            goto Dcb6013d6c011b31fa12d890c6f527c4;
                        }
                        self::$ipTV_db->query("SELECT server_id, COUNT(*) AS online_clients FROM `user_activity_now` GROUP BY server_id");
                        $rows = self::$ipTV_db->get_rows(true, "server_id");
                        if (ipTV_lib::$settings["split_by"] == "band") {
                            $D8d3ca7afab93e5c110124dc7611906c = array();
                            foreach ($StreamSysIds as $server_id) {
                                $A8897e590149896423cc3c897a6c6651 = json_decode(ipTV_lib::$StreamingServers[$server_id]["server_hardware"], true);
                                if (!empty($A8897e590149896423cc3c897a6c6651["network_speed"])) {
                                    $D8d3ca7afab93e5c110124dc7611906c[$server_id] = (float) $A8897e590149896423cc3c897a6c6651["network_speed"];
                                    goto d6478715224e6c5d8fbba6cedd93a54a;
                                }
                                $D8d3ca7afab93e5c110124dc7611906c[$server_id] = 1000;
                                d6478715224e6c5d8fbba6cedd93a54a:
                            }
                            foreach ($rows as $server_id => $c72d66b481d02f854f0bef67db92a547) {
                                $rows[$server_id]["capacity"] = (float) ($c72d66b481d02f854f0bef67db92a547["online_clients"] / $D8d3ca7afab93e5c110124dc7611906c[$server_id]);
                            }
                            goto fb8512650313d24ebbda99a7e541af4a;
                        }
                        if (ipTV_lib::$settings["split_by"] == "maxclients") {
                            foreach ($rows as $server_id => $c72d66b481d02f854f0bef67db92a547) {
                                $rows[$server_id]["capacity"] = (float) ($c72d66b481d02f854f0bef67db92a547["online_clients"] / ipTV_lib::$StreamingServers[$server_id]["total_clients"]);
                            }
                            goto fb8512650313d24ebbda99a7e541af4a;
                        }
                        if (ipTV_lib::$settings["split_by"] == "guar_band") {
                            foreach ($rows as $server_id => $c72d66b481d02f854f0bef67db92a547) {
                                $rows[$server_id]["capacity"] = (float) ($c72d66b481d02f854f0bef67db92a547["online_clients"] / ipTV_lib::$StreamingServers[$server_id]["network_guaranteed_speed"]);
                            }
                            goto c0b522ed318d2cbfd86f7db3c745e349;
                        }
                        foreach ($rows as $server_id => $c72d66b481d02f854f0bef67db92a547) {
                            $rows[$server_id]["capacity"] = $c72d66b481d02f854f0bef67db92a547["online_clients"];
                        }
                        c0b522ed318d2cbfd86f7db3c745e349:
                        fb8512650313d24ebbda99a7e541af4a:
                        if (ipTV_lib::$settings["online_capacity_interval"] != 0) {
                            file_put_contents(TMP_DIR . "servers_capacity", json_encode($rows), LOCK_EX);
                        }
                        Dcb6013d6c011b31fa12d890c6f527c4:
                        foreach ($StreamSysIds as $server_id) {
                            $Fe028c63f38ae95c5a00bf47dbfb97a9 = isset($rows[$server_id]["online_clients"]) ? $rows[$server_id]["online_clients"] : 0;
                            if ($Fe028c63f38ae95c5a00bf47dbfb97a9 == 0) {
                                $rows[$server_id]["capacity"] = 0;
                            }
                            $servers[$server_id] = ipTV_lib::$StreamingServers[$server_id]["total_clients"] > 0 && ipTV_lib::$StreamingServers[$server_id]["total_clients"] > $Fe028c63f38ae95c5a00bf47dbfb97a9 ? $rows[$server_id]["capacity"] : false;
                        }
                        $servers = array_filter($servers, "is_numeric");
                        if (empty($servers)) {
                            E310f9d1d479044c3f38d4e3940098c7:
                            return false;
                        }
                        $aeab45b2c8e6c4f72bec66f6f1a380c0 = array_keys($servers);
                        $C3a0e56f71bc74a3da1fc67955fac9a6 = array_values($servers);
                        array_multisort($C3a0e56f71bc74a3da1fc67955fac9a6, SORT_ASC, $aeab45b2c8e6c4f72bec66f6f1a380c0, SORT_ASC);
                        $servers = array_combine($aeab45b2c8e6c4f72bec66f6f1a380c0, $C3a0e56f71bc74a3da1fc67955fac9a6);
                        if ($extension == "rtmp" && array_key_exists(SERVER_ID, $servers)) {
                            $force_server_id = SERVER_ID;
                            goto Abfdd728972a2798d5f98e139390ecf3;
                        }
                        if ($user_info["force_server_id"] != 0 and array_key_exists($user_info["force_server_id"], $servers)) {
                            $force_server_id = $user_info["force_server_id"];
                            goto e89ca2920e0492def23f0ac978bab6ac;
                        }
                        $C8a559944c9ad8d120b437a065024840 = array();
                        foreach (array_keys($servers) as $server_id) {
                            if (ipTV_lib::$StreamingServers[$server_id]["enable_geoip"] == 1) {
                                if (in_array($geoip_country_code, ipTV_lib::$StreamingServers[$server_id]["geoip_countries"])) {
                                    $force_server_id = $server_id;
                                    goto e05f1b72bef6daaab8430644e6cb5ae4;
                                }
                                if (ipTV_lib::$StreamingServers[$server_id]["geoip_type"] == "strict") {
                                    unset($servers[$server_id]);
                                    goto Fd7c8e366330c6c5185231676a8c2a7d;
                                }
                                $C8a559944c9ad8d120b437a065024840[$server_id] = ipTV_lib::$StreamingServers[$server_id]["geoip_type"] == "low_priority" ? 1 : 2;
                                Fd7c8e366330c6c5185231676a8c2a7d:
                                D60a6b55cfd678a3cab0a9b2bb62cc0b:
                                goto df1dab1529b7af4fa42d1ac9d461f6c7;
                            }
                            if (ipTV_lib::$StreamingServers[$server_id]["enable_isp"] == 1) {
                                if (in_array($con_isp_name, ipTV_lib::$StreamingServers[$server_id]["isp_names"])) {
                                    $force_server_id = $server_id;
                                    goto e05f1b72bef6daaab8430644e6cb5ae4;
                                }
                                if (ipTV_lib::$StreamingServers[$server_id]["isp_type"] == "strict") {
                                    unset($servers[$server_id]);
                                    goto F8729fb2e150ebd9eaececa784c85daa;
                                }
                                $C8a559944c9ad8d120b437a065024840[$server_id] = ipTV_lib::$StreamingServers[$server_id]["isp_type"] == "low_priority" ? 1 : 2;
                                F8729fb2e150ebd9eaececa784c85daa:
                                b5531bc8ac92447022ab49da65c2e7d0:
                                goto D5d6dab8f9060fc54285b93725e4084d;
                            }
                            $C8a559944c9ad8d120b437a065024840[$server_id] = 1;
                            D5d6dab8f9060fc54285b93725e4084d:
                            df1dab1529b7af4fa42d1ac9d461f6c7:
                        }
                        e05f1b72bef6daaab8430644e6cb5ae4:
                        if (!(empty($C8a559944c9ad8d120b437a065024840) && empty($force_server_id))) {
                            $force_server_id = empty($force_server_id) ? array_search(min($C8a559944c9ad8d120b437a065024840), $C8a559944c9ad8d120b437a065024840) : $force_server_id;
                            e89ca2920e0492def23f0ac978bab6ac:
                            Abfdd728972a2798d5f98e139390ecf3:
                            if ($force_server_id != SERVER_ID) {
                                if ($type == "live") {
                                    $D4a67bbd52a22a102a646011a4bec962 = $extension == "m3u8" ? 0 : time() + 6;
                                } else {
                                    $Cb08b127bfe426d7f3ccbd3e38f05471 = json_decode($stream["servers"][$force_server_id]["stream_info"], true);
                                    $D4a67bbd52a22a102a646011a4bec962 = time() + (int) $Cb08b127bfe426d7f3ccbd3e38f05471["of_duration"];
                                }
                                $data = array(
                                    "hash" => md5(
                                        json_encode(
                                            array(
                                                "stream_id" => $stream_id,
                                                "user_id" => $user_info["id"],
                                                "username" => $user_info["username"],
                                                "password" => $user_info["password"],
                                                "user_ip" => $user_ip,
                                                "live_streaming_pass" => ipTV_lib::$settings["live_streaming_pass"],
                                                "pid" => $stream["servers"][$force_server_id]["pid"],
                                                "external_device" => $external_device,
                                                "on_demand" => $stream["servers"][$force_server_id]["on_demand"],
                                                "isp" => $con_isp_name,
                                                "bitrate" => $stream["servers"][$force_server_id]["bitrate"],
                                                "country" => $geoip_country_code,
                                                "extension" => $extension,
                                                "is_restreamer" => $user_info["is_restreamer"],
                                                "max_connections" => $user_info["max_connections"],
                                                "monitor_pid" => $stream["servers"][$force_server_id]["monitor_pid"],
                                                "time" => $D4a67bbd52a22a102a646011a4bec962
                                            )
                                        )
                                    ),
                                    "stream_id" => $stream_id,
                                    "user_id" => $user_info["id"],
                                    "time" => $D4a67bbd52a22a102a646011a4bec962,
                                    "pid" => $stream["servers"][$force_server_id]["pid"],
                                    "external_device" => $external_device,
                                    "on_demand" => $stream["servers"][$force_server_id]["on_demand"],
                                    "isp" => $con_isp_name, "bitrate" => $stream["servers"][$force_server_id]["bitrate"],
                                    "country" => $geoip_country_code,
                                    "extension" => $extension,
                                    "is_restreamer" => $user_info["is_restreamer"],
                                    "max_connections" => $user_info["max_connections"],
                                    "monitor_pid" => $stream["servers"][$force_server_id]["monitor_pid"]
                                );
                                $req_uri = substr($_SERVER["REQUEST_URI"], 1);
                                $cb8983ea8c2dc44d7be007079a71c336 = substr_count($req_uri, "?") == 0 ? "?" : "&";
                                header("Location: " . ipTV_lib::$StreamingServers[$force_server_id]["site_url"] . $req_uri . $cb8983ea8c2dc44d7be007079a71c336 . "token=" . base64_encode(decrypt_config(json_encode($data), md5(ipTV_lib::$settings["crypt_load_balancing"]))));
                                die;
                            }
                            return array_merge($stream["info"], $stream["servers"][SERVER_ID]);
                        }
                        return false;
                    }
                    return false;
                }
                header("Location: " . str_replace(" ", "%20", json_decode($stream["info"]["stream_source"], true)[0]));
                die;
            }
            return false;
        }
        self::$ipTV_db->query("SELECT `tv_archive_server_id`,`tv_archive_duration` FROM `streams` WHERE `id` = '%d'", $stream_id);
        if (!(self::$ipTV_db->num_rows() > 0)) {
            goto fe44649b515c4ec3e22b8a3cf1fc4d22;
        }
        $c72d66b481d02f854f0bef67db92a547 = self::$ipTV_db->get_row();
        if (!($c72d66b481d02f854f0bef67db92a547["tv_archive_duration"] > 0 && $c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"] > 0 && array_key_exists($c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"], ipTV_lib::$StreamingServers))) {
            Ec3c82ae49cdcc8ab3c7882a72a65387:
            fe44649b515c4ec3e22b8a3cf1fc4d22:
            return false;
        }
        if ($c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"] != SERVER_ID) {
            parse_str($_SERVER["QUERY_STRING"], $Cc31a34e0b1fa157d875f9946912d9fa);
            $D4a67bbd52a22a102a646011a4bec962 = time() + $Cc31a34e0b1fa157d875f9946912d9fa["duration"] * 60;
            $data = array("hash" => md5(json_encode(array("user_id" => $user_info["id"], "username" => $user_info["username"], "password" => $user_info["password"], "user_ip" => $user_ip, "live_streaming_pass" => ipTV_lib::$settings["live_streaming_pass"], "external_device" => $external_device, "isp" => $con_isp_name, "country" => $geoip_country_code, "stream_id" => $stream_id, "start" => $Cc31a34e0b1fa157d875f9946912d9fa["start"], "duration" => $Cc31a34e0b1fa157d875f9946912d9fa["duration"], "extension" => $Cc31a34e0b1fa157d875f9946912d9fa["extension"], "time" => $D4a67bbd52a22a102a646011a4bec962))), "user_id" => $user_info["id"], "username" => $user_info["username"], "password" => $user_info["password"], "time" => $D4a67bbd52a22a102a646011a4bec962, "external_device" => $external_device, "isp" => $con_isp_name, "country" => $geoip_country_code, "stream_id" => $stream_id, "start" => $Cc31a34e0b1fa157d875f9946912d9fa["start"], "duration" => $Cc31a34e0b1fa157d875f9946912d9fa["duration"], "extension" => $Cc31a34e0b1fa157d875f9946912d9fa["extension"]);
            $req_uri = substr($_SERVER["REQUEST_URI"], 1);
            header("Location: " . ipTV_lib::$StreamingServers[$c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"]]["site_url"] . "streaming/timeshift.php?token=" . base64_encode(decrypt_config(json_encode($data), md5(ipTV_lib::$settings["crypt_load_balancing"]))));
            die;
        }
        return true;
    }
    public static function checkStreamExistInBouquet($stream_id, $connections = array(), $type = "movie") {
        if ($type == "movie") {
            return in_array($stream_id, $connections);
        }
        if ($type == "series") {
            $query = "SELECT series_id FROM `series_episodes` WHERE `stream_id` = '%d' LIMIT 1";
            self::$ipTV_db->query($query, $stream_id);
            if (self::$ipTV_db->num_rows() <= 0) {
                return in_array(self::$ipTV_db->get_col(), $connections);
            }
        }
        return false;
    }
    // checked
    public static function GetUserInfo($user_id = null, $username = null, $password = null, $get_channel_IDS = false, $getBouquetInfo = false, $get_cons = false, $type = array(), $is_adult = false, $user_ip = '', $user_agent = '', $a8851ef591e0cdd9aad6ec4f7bd4b160 = array(), $play_token = '', $stream_id = 0) {
        if (empty($user_id)) {
            self::$ipTV_db->query('SELECT * FROM `users` WHERE `username` = \'%s\' AND `password` = \'%s\' LIMIT 1', $username, $password);
        } else {
            self::$ipTV_db->query('SELECT * FROM `users` WHERE `id` = \'%d\'', $user_id);
        }
        if (!(self::$ipTV_db->num_rows() > 0)) {
            return false;
        }
        $user_info = self::$ipTV_db->get_row();
        if (ipTV_lib::$settings["case_sensitive_line"] == 1 && !empty($username) && !empty($password)) {
            if ($user_info["username"] == $username || $user_info["password"] == $password) {
                if (ipTV_lib::$settings["county_override_1st"] == 1 && empty($user_info["forced_country"]) && !empty($user_ip) && $user_info["max_connections"] == 1) {
                    $user_info["forced_country"] = geoip_country_code_by_name($user_ip);
                    self::$ipTV_db->query("UPDATE `users` SET `forced_country` = '%s' WHERE `id` = '%d'", $user_info["forced_country"], $user_info["id"]);
                }
                if ($user_info['is_mag'] == 1 && ipTV_lib::$settings['mag_security'] == 1) {
                    if (!empty($user_info['play_token']) && !empty($play_token)) {
                        list($token, $B96676565d19827b6e2eda6db94167c0, $cced8089119eaa83c17b19ea19d9af22) = explode(':', $user_info['play_token']);
                        if (!($token == $play_token && $B96676565d19827b6e2eda6db94167c0 >= time() && $cced8089119eaa83c17b19ea19d9af22 == $stream_id)) {
                            $user_info['mag_invalid_token'] = true;
                        }
                    } else {
                        $user_info['mag_invalid_token'] = true;
                    }
                }
                $user_info["bouquet"] = json_decode($user_info["bouquet"], true);
                $user_info["allowed_ips"] = @array_filter(array_map("trim", json_decode($user_info["allowed_ips"], true)));
                $user_info["allowed_ua"] = @array_filter(array_map("trim", json_decode($user_info["allowed_ua"], true)));
                if ($get_cons) {
                    self::$ipTV_db->query("SELECT COUNT(`activity_id`) FROM `user_activity_now` WHERE `user_id` = '%d'", $user_info["id"]);
                    $user_info["active_cons"] = self::$ipTV_db->get_col();
                    if ($user_info["max_connections"] == 1 && ipTV_lib::$settings["disallow_2nd_ip_con"] == 1 && $user_info["active_cons"] > 0 && !empty($user_ip)) {
                        self::$ipTV_db->query("SELECT user_ip FROM `user_activity_now` WHERE `user_id` = '%d' LIMIT 1", $user_info["id"]);
                        if (self::$ipTV_db->num_rows() > 0) {
                            $user_ip_db = self::$ipTV_db->get_col();
                            if ($user_ip_db != $user_ip) {
                                $user_info["ip_limit_reached"] = 1;
                            }
                        }
                    }
                    $user_info["pair_line_info"] = array();
                    if (!is_null($user_info["pair_id"])) {
                        self::$ipTV_db->query("SELECT COUNT(`activity_id`) FROM `user_activity_now` WHERE `user_id` = '%d'", $user_info["pair_id"]);
                        $user_info["pair_line_info"]["active_cons"] = self::$ipTV_db->get_col();
                        self::$ipTV_db->query("SELECT max_connections FROM `users` WHERE `id` = '%d'", $user_info["pair_id"]);
                        $user_info["pair_line_info"]["max_connections"] = self::$ipTV_db->get_col();
                    }
                } else {
                    $user_info["active_cons"] = "N/A";
                }
                if (file_exists(TMP_DIR . 'user_output' . $user_info["id"])) {
                    $user_info["output_formats"] = unserialize(file_get_contents(TMP_DIR . "user_output" . $user_info["id"]));
                } else {
                    self::$ipTV_db->query("SELECT * FROM `access_output` t1 INNER JOIN `user_output` t2 ON t1.access_output_id = t2.access_output_id WHERE t2.user_id = '%d'", $user_info["id"]);
                    $user_info["output_formats"] = self::$ipTV_db->get_rows(true, "output_key");
                    file_put_contents(TMP_DIR . 'user_output' . $user_info["id"], serialize($user_info["output_formats"]), LOCK_EX);
                }
                $user_info["con_isp_name"] = $user_info["con_isp_type"] = null;
                $user_info["isp_is_server"] = $user_info["isp_violate"] = 0;
                if (ipTV_lib::$settings['show_isps'] == 1 && !empty($user_ip)) {
                    $isp_lock = self::apiGetISPName($user_ip, $user_agent);
                    if (is_array($isp_lock)) {
                        if (!empty($isp_lock["isp_info"]["description"])) {
                            $user_info["con_isp_name"] = $isp_lock["isp_info"]["description"];
                            $IspIsBlocked = self::checkIspIsBlocked($user_info["con_isp_name"]);
                            if ($user_info["is_restreamer"] == 0 && ipTV_lib::$settings["block_svp"] == 1 && !empty($isp_lock["isp_info"]["is_server"])) {
                                $user_info["isp_is_server"] = $isp_lock["isp_info"]["is_server"];
                            }

                            if ($user_info["isp_is_server"] == 1) {
                                $user_info["con_isp_type"] = $isp_lock["isp_info"]["type"];
                            }
                            if ($IspIsBlocked !== false) {
                                $user_info["isp_is_server"] = $IspIsBlocked == 1 ? 1 : 0;
                                $user_info["con_isp_type"] = $user_info["isp_is_server"] == 1 ? "Custom" : null;
                            }
                        }
                    }
                    if (!empty($user_info["con_isp_name"]) && ipTV_lib::$settings["enable_isp_lock"] == 1 && $user_info["is_stalker"] == 0 && $user_info["is_isplock"] == 1 && !empty($user_info["isp_desc"]) && strtolower($user_info["con_isp_name"]) != strtolower($user_info["isp_desc"])) {
                        $user_info["isp_violate"] = 1;
                    }
                    if ($user_info["isp_violate"] == 0 && strtolower($user_info["con_isp_name"]) != strtolower($user_info["isp_desc"])) {
                        self::$ipTV_db->query("UPDATE `users` SET `isp_desc` = '%s' WHERE `id` = '%d'", $user_info["con_isp_name"], $user_info["id"]);
                    }
                }
                if ($get_channel_IDS) {
                    $array1 = $array2 = array();
                    if (ipTV_lib::$settings["new_sorting_bouquet"] != 1) {
                        sort($user_info["bouquet"]);
                    }
                    foreach ($user_info["bouquet"] as $id) {
                        if (isset(ipTV_lib::$Bouquets[$id]["streams"])) {
                            $array1 = array_merge($array1, ipTV_lib::$Bouquets[$id]["streams"]);
                        }
                        if (isset(ipTV_lib::$Bouquets[$id]["series"])) {
                            $array2 = array_merge($array2, ipTV_lib::$Bouquets[$id]["series"]);
                        }
                    }
                    if (ipTV_lib::$settings["new_sorting_bouquet"] != 1) {
                        $user_info["channel_ids"] = array_unique($array1);
                        $user_info["series_ids"] = array_unique($array2);
                    } else {
                        $user_info["channel_ids"] = array_reverse(array_unique(array_reverse($array1)));
                        $user_info["series_ids"] = array_reverse(array_unique(array_reverse($array2)));
                    }
                    if ($getBouquetInfo && !empty($user_info["channel_ids"])) {
                        $user_info["channels"] = array();
                        $output = array();
                        $types = empty($type) ? STREAM_TYPE : $type;
                        foreach ($types as $file) {
                            if (file_exists(TMP_DIR . $file . "_main.php")) {
                                $input = (include TMP_DIR . $file . "_main.php");
                                $output = array_replace($output, $input);
                            }
                        }
                        foreach ($user_info["channel_ids"] as $id) {
                            if (isset($output[$id])) {
                                if ($is_adult) {
                                    $output[$id]["is_adult"] = strtolower($output[$id]["category_name"]) == "for adults" ? 1 : 0;
                                }
                                $user_info["channels"][$id] = $output[$id];
                            }
                        }
                        $output = null;
                        if (!empty($a8851ef591e0cdd9aad6ec4f7bd4b160["items_per_page"])) {
                            $user_info["total_found_rows"] = count($user_info["channels"]);
                            $user_info["channels"] = array_slice($user_info["channels"], $a8851ef591e0cdd9aad6ec4f7bd4b160["offset"], $a8851ef591e0cdd9aad6ec4f7bd4b160["items_per_page"]);
                        }
                    }
                }

                return $user_info;
            }
        }
        return false;
    }
    public static function CategoriesBouq($category_id, $bouquets) {
        if (!file_exists(TMP_DIR . 'categories_bouq')) {
            return true;
        }
        if (!is_array($bouquets)) {
            $bouquets = json_decode($bouquets, true);
        }
        $output = unserialize(file_get_contents(TMP_DIR . 'categories_bouq'));
        foreach ($bouquets as $bouquet) {
            if (isset($output[$bouquet])) {
                if (in_array($category_id, $output[$bouquet])) {
                    return true;
                }
            }
        }
        return false;
    }
    public static function GetMagInfo($mag_id = null, $mac = null, $get_ChannelIDS = false, $getBouquetInfo = false, $get_cons = false) {
        if (empty($mag_id)) {
            self::$ipTV_db->query('SELECT * FROM `mag_devices` WHERE `mac` = \'%s\'', base64_encode($mac));
        } else {
            self::$ipTV_db->query('SELECT * FROM `mag_devices` WHERE `mag_id` = \'%d\'', $mag_id);
        }
        if (self::$ipTV_db->num_rows() > 0) {
            $maginfo = array();
            $maginfo['mag_device'] = self::$ipTV_db->get_row();
            $maginfo['mag_device']['mac'] = base64_decode($maginfo['mag_device']['mac']);
            $maginfo['user_info'] = array();
            if ($user_info = self::GetUserInfo($maginfo['mag_device']['user_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                $maginfo['user_info'] = $user_info;
            }
            $maginfo['pair_line_info'] = array();
            if (!empty($maginfo['user_info'])) {
                $maginfo['pair_line_info'] = array();
                if (!is_null($maginfo['user_info']['pair_id'])) {
                    if ($user_info = self::GetUserInfo($maginfo['user_info']['pair_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                        $maginfo['pair_line_info'] = $user_info;
                    }
                }
            }
            return $maginfo;
        }
        return false;
    }
    public static function EnigmaDevices($maginfo, $get_ChannelIDS = false, $getBouquetInfo = false, $get_cons = false) {
        if (empty($maginfo['device_id'])) {
            self::$ipTV_db->query('SELECT * FROM `enigma2_devices` WHERE `mac` = \'%s\'', $maginfo['mac']);
        } else {
            self::$ipTV_db->query('SELECT * FROM `enigma2_devices` WHERE `device_id` = \'%d\'', $maginfo['device_id']);
        }
        if (self::$ipTV_db->num_rows() > 0) {
            $enigma2devices = array();
            $enigma2devices['enigma2'] = self::$ipTV_db->get_row();
            $enigma2devices['user_info'] = array();
            if ($user_info = self::GetUserInfo($enigma2devices['enigma2']['user_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                $enigma2devices['user_info'] = $user_info;
            }
            $enigma2devices['pair_line_info'] = array();
            if (!empty($enigma2devices['user_info'])) {
                $enigma2devices['pair_line_info'] = array();
                if (!is_null($enigma2devices['user_info']['pair_id'])) {
                    if ($user_info = self::GetUserInfo($enigma2devices['user_info']['pair_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                        $enigma2devices['pair_line_info'] = $user_info;
                    }
                }
            }
            return $enigma2devices;
        }
        return false;
    }
    public static function CloseLastCon($user_id, $max_connections) {
        self::$ipTV_db->query('SELECT * FROM `user_activity_now` WHERE `user_id` = \'%d\' ORDER BY activity_id ASC', $user_id);
        $rows = self::$ipTV_db->get_rows();
        $length = count($rows) - $max_connections + 1;
        if ($length <= 0) {
            return;
        }
        $total = 0;
        $connections = array();
        $index = 0;
        while ($index < count($rows) && $index < $length) {
            if ($rows[$index]['hls_end'] == 1) {
                continue;
            }
            if (self::RemoveConnection($rows[$index], false)) {
                ++$total;
                if ($rows[$index]['container'] != 'hls') {
                    $connections[] = $rows[$index]['activity_id'];
                }
            }
            $index++;
        }
        if (!empty($connections)) {
            self::$ipTV_db->query('DELETE FROM `user_activity_now` WHERE `activity_id` IN (' . implode(',', $connections) . ')');
        }
        return $total;
    }
    public static function RemoveConnection($activity_id, $ActionUserActivityNow = true) {
        if (empty($activity_id)) {
            return false;
        }
        if (empty($activity_id['activity_id'])) {
            self::$ipTV_db->query('SELECT * FROM `user_activity_now` WHERE `activity_id` = \'%d\'', $activity_id);
            $activity_id = self::$ipTV_db->get_row();
        }
        if (empty($activity_id)) {
            return false;
        }
        if (!($activity_id['container'] == 'rtmp')) {
            if ($activity_id['container'] == 'hls') {
                if (!$ActionUserActivityNow) {
                    self::$ipTV_db->query('UPDATE `user_activity_now` SET `hls_end` = 1 WHERE `activity_id` = \'%d\'', $activity_id['activity_id']);
                }
            } else {
                if ($activity_id['server_id'] == SERVER_ID) {
                    shell_exec("kill -9 {$activity_id['pid']} >/dev/null 2>/dev/null &");
                } else {
                    self::$ipTV_db->query('INSERT INTO `signals` (`pid`,`server_id`,`time`) VALUES(\'%d\',\'%d\',UNIX_TIMESTAMP())', $activity_id['pid'], $activity_id['server_id']);
                }
                if ($activity_id['server_id'] == SERVER_ID) {
                    shell_exec('wget --timeout=2 -O /dev/null -o /dev/null "' . ipTV_lib::$StreamingServers[SERVER_ID]['rtmp_mport_url'] . "control/drop/client?clientid={$activity_id['pid']}\" >/dev/null 2>/dev/null &");
                } else {
                    self::$ipTV_db->query('INSERT INTO `signals` (`pid`,`server_id`,`rtmp`,`time`) VALUES(\'%d\',\'%d\',\'%d\',UNIX_TIMESTAMP())', $activity_id['pid'], $activity_id['server_id'], 1);
                }
            }
            if ($ActionUserActivityNow) {
                self::$ipTV_db->query('DELETE FROM `user_activity_now` WHERE `activity_id` = \'%d\'', $activity_id['activity_id']);
            }
            self::SaveClosedConnection($activity_id['server_id'], $activity_id['user_id'], $activity_id['stream_id'], $activity_id['date_start'], $activity_id['user_agent'], $activity_id['user_ip'], $activity_id['container'], $activity_id['geoip_country_code'], $activity_id['isp'], $activity_id['external_device']);
            return true;
        }
    }
    public static function playDone($pid) {
        if (empty($pid)) {
            return false;
        }
        self::$ipTV_db->query('SELECT * FROM `user_activity_now` WHERE `container` = \'rtmp\' AND `pid` = \'%d\' AND `server_id` = \'%d\'', $pid, SERVER_ID);
        if (self::$ipTV_db->num_rows() > 0) {
            $activity_id = self::$ipTV_db->get_row();
            self::$ipTV_db->query('DELETE FROM `user_activity_now` WHERE `activity_id` = \'%d\'', $activity_id['activity_id']);
            self::SaveClosedConnection($activity_id['server_id'], $activity_id['user_id'], $activity_id['stream_id'], $activity_id['date_start'], $activity_id['user_agent'], $activity_id['user_ip'], $activity_id['container'], $activity_id['geoip_country_code'], $activity_id['isp'], $activity_id['external_device']);
            return true;
        }
        return false;
    }
    public static function SaveClosedConnection($server_id, $user_id, $stream_id, $start, $user_agent, $user_ip, $extension, $geoip_country_code, $isp, $external_device = '') {
        if (ipTV_lib::$settings['save_closed_connection'] == 0) {
            return;
        }
        $activity_id = array('user_id' => intval($user_id), 'stream_id' => intval($stream_id), 'server_id' => intval($server_id), 'date_start' => intval($start), 'user_agent' => $user_agent, 'user_ip' => htmlentities($user_ip), 'date_end' => time(), 'container' => $extension, 'geoip_country_code' => $geoip_country_code, 'isp' => $isp, 'external_device' => htmlentities($external_device));
        file_put_contents(TMP_DIR . 'connections', base64_encode(json_encode($activity_id)) . '', FILE_APPEND | LOCK_EX);
    }
    # create clientlog file 
    public static function ClientLog($stream_id, $user_id, $action, $user_ip, $data = '', $clientLogsSave = false) {
        if (ipTV_lib::$settings['client_logs_save'] == 0 && !$clientLogsSave) {
            return;
        }
        $user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? htmlentities($_SERVER['HTTP_USER_AGENT']) : '';
        $query_string = empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING'];
        $data = array('user_id' => $user_id, 'stream_id' => $stream_id, 'action' => $action, 'query_string' => htmlentities($_SERVER['QUERY_STRING']), 'user_agent' => $user_agent, 'user_ip' => $user_ip, 'time' => time(), 'extra_data' => $data);
        file_put_contents(TMP_DIR . 'client_request.log', base64_encode(json_encode($data)) . '', FILE_APPEND);
    }
    public static function GetSegmentsOfPlaylist($playlist, $prebuffer = 0) {
        if (file_exists($playlist)) {
            $source = file_get_contents($playlist);
            if (preg_match_all('/(.*?).ts/', $source, $matches)) {
                if ($prebuffer > 0) {
                    $total_segs = intval($prebuffer / 10);
                    return array_slice($matches[0], -$total_segs);
                } else {
                    preg_match('/_(.*)\\./', array_pop($matches[0]), $pregmatches);
                    return $pregmatches[1];
                }
            }
        }
        return false;
    }
    public static function GeneratePlayListWithAuthenticationAdmin($m3u8_playlist, $password, $stream_id) {
        if (file_exists($m3u8_playlist)) {
            $source = file_get_contents($m3u8_playlist);
            if (preg_match_all('/(.*?)\\.ts/', $source, $matches)) {
                foreach ($matches[0] as $match) {
                    $source = str_replace($match, "/streaming/admin_live.php?password={$password}&extension=m3u8&segment={$match}&stream={$stream_id}", $source);
                }
                return $source;
            }
            return false;
        }
    }
    public static function GeneratePlayListWithAuthentication($m3u8_playlist, $username = '', $password = '', $stream_id) {
        if (file_exists($m3u8_playlist)) {
            $source = file_get_contents($m3u8_playlist);
            if (preg_match_all('/(.*?)\\.ts/', $source, $matches)) {
                foreach ($matches[0] as $match) {
                    $token = md5($match . $username . ipTV_lib::$settings['crypt_load_balancing'] . filesize(STREAMS_PATH . $match));
                    $source = str_replace($match, "/hls/{$username}/{$password}/{$stream_id}/{$token}/{$match}", $source);
                }
                return $source;
            }
            return false;
        }
    }
    public static function checkGlobalBlockUA($user_agent) {
        $user_agent = strtolower($user_agent);
        $id = false;
        foreach (ipTV_lib::$blockedUA as $key => $value) {
            if (($value['exact_match'] == 1)) {
                if ($value['blocked_ua'] == $user_agent) {
                    $id = $key;
                    break;
                }
            } else if (stristr($user_agent, $value['blocked_ua'])) {
                $id = $key;
            }
        }
        if ($id > 0) {
            self::$ipTV_db->query('UPDATE `blocked_user_agents` SET `attempts_blocked` = `attempts_blocked`+1 WHERE `id` = \'%d\'', $id);
            die;
        }
    }
    /** 
     * Check if a process with a given PID exists and is associated with a specific stream ID. 
     * 
     * @param int $pid The process ID to check. 
     * @param int $stream_id The stream ID to match against. 
     * @param string $ffmpeg_path The path to the ffmpeg executable (default is PHP_BIN). 
     * @return bool True if a matching process is found, false otherwise. 
     */
    public static function CheckPidExist($pid, $stream_id, $ffmpeg_path = PHP_BIN) {
        if (empty($pid)) {
            return false;
        }
        clearstatcache(true);
        if (file_exists('/proc/' . $pid) && is_readable('/proc/' . $pid . '/exe') && basename(readlink('/proc/' . $pid . '/exe')) == basename($ffmpeg_path)) {
            $value = trim(file_get_contents("/proc/{$pid}/cmdline"));
            if ($value == "XtreamCodes[{$stream_id}]") {
                return true;
            }
        }
        return false;
    }
    public static function checkIsCracked($user_ip) {
        $user_ip_file = TMP_DIR . md5($user_ip . 'cracked');
        if (file_exists($user_ip_file)) {
            $contents = intval(file_get_contents($user_ip_file));
            return $contents == 1 ? true : false;
        }
        file_put_contents($user_ip_file, 0);
        return false;
    }
    public static function CheckPidStreamExist($pid, $stream_id) {
        if (empty($pid)) {
            return false;
        }
        clearstatcache(true);
        if (file_exists('/proc/' . $pid) && is_readable('/proc/' . $pid . '/exe')) {
            $value = trim(file_get_contents("/proc/{$pid}/cmdline"));
            if ($value == "XtreamCodesDelay[{$stream_id}]") {
                return true;
            }
        }
        return false;
    }
    public static function CheckPidChannelM3U8Exist($pid, $stream_id, $ffmpeg_path = FFMPEG_PATH) {
        if (!empty($pid)) {
            clearstatcache(true);
            if (!(file_exists("/proc/" . $pid) && is_readable("/proc/" . $pid . "/exe") && basename(readlink("/proc/" . $pid . "/exe")) == basename($ffmpeg_path))) {
                return false;
            }
            $value = trim(file_get_contents("/proc/{$pid}/cmdline"));
            if (!stristr($value, "/{$stream_id}_.m3u8")) {
                return false;
            }
            return true;
        }
        return false;
    }
    public static function ps_running($pid, $ffmpeg_path) {
        if (empty($pid)) {
            return false;
        }
        clearstatcache(true);
        if (file_exists('/proc/' . $pid) && is_readable('/proc/' . $pid . '/exe') && basename(readlink('/proc/' . $pid . '/exe')) == basename($ffmpeg_path)) {
            return true;
        }
        return false;
    }
    public static function ShowVideo($is_restreamer = 0, $video_id_setting, $video_path_id, $extension = 'ts') {
        if ($is_restreamer == 0 && ipTV_lib::$settings[$video_id_setting] == 1) {
            if ($extension == 'm3u8') {
                $extm3u = '#EXTM3U
				#EXT-X-VERSION:3
				#EXT-X-MEDIA-SEQUENCE:0
				#EXT-X-ALLOW-CACHE:YES
				#EXT-X-TARGETDURATION:11
				#EXTINF:10.0,
				' . ipTV_lib::$settings[$video_path_id] . '
				#EXT-X-ENDLIST';
                header('Content-Type: application/x-mpegurl');
                header('Content-Length: ' . strlen($extm3u));
                echo $extm3u;
                die;
            } else {
                header('Content-Type: video/mp2t');
                readfile(ipTV_lib::$settings[$video_path_id]);
                die;
            }
        }
        http_response_code(403);
        die;
    }
    public static function IsValidStream($playlist, $pid) {
        return self::ps_running($pid, FFMPEG_PATH) && file_exists($playlist);
    }
    public static function getUserIP() {
        return !empty(ipTV_lib::$settings['get_real_ip_client']) && !empty($_SERVER[ipTV_lib::$settings['get_real_ip_client']]) ? $_SERVER[ipTV_lib::$settings['get_real_ip_client']] : $_SERVER['REMOTE_ADDR'];
    }
    public static function GetStreamBitrate($type, $path, $force_duration = null) {
        clearstatcache();
        if (!file_exists($path)) {
            return false;
        }
        switch ($type) {
            case 'movie':
                if (!is_null($force_duration)) {
                    sscanf($force_duration, '%d:%d:%d', $hours, $minutes, $seconds);
                    $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
                    $bitrate = round(filesize($path) * 0.008 / $time_seconds);
                }
                break;
            case 'live':
                $fp = fopen($path, 'r');
                $bitrates = array();
                while (!feof($fp)) {
                    $line = trim(fgets($fp));
                    if (stristr($line, 'EXTINF')) {
                        list($trash, $seconds) = explode(':', $line);
                        $seconds = rtrim($seconds, ',');
                        if ($seconds <= 0) {
                            continue;
                        }
                        $segment_file = trim(fgets($fp));
                        if (!file_exists(dirname($path) . '/' . $segment_file)) {
                            fclose($fp);
                            return false;
                        }
                        $segment_size_in_kilobits = filesize(dirname($path) . '/' . $segment_file) * 0.008;
                        $bitrates[] = $segment_size_in_kilobits / $seconds;
                    }
                }
                fclose($fp);
                $bitrate = count($bitrates) > 0 ? round(array_sum($bitrates) / count($bitrates)) : 0;
                break;
        }
        return $bitrate > 0 ? $bitrate : false;
    }
    public static function apiGetISPName($user_ip, $user_agent) {
        if (empty($user_ip)) {
            return false;
        }
        if (file_exists(TMP_DIR . md5($user_ip))) {
            return json_decode(file_get_contents(TMP_DIR . md5($user_ip)), true);
        }
        $ctx = stream_context_create(array('http' => array('timeout' => 2)));
        $response = @file_get_contents("http://api.xtream-codes.com/api.php?ip={$user_ip}&user_agent=" . base64_encode($user_agent) . '&block_svp=' . ipTV_lib::$settings['block_svp'], false, $ctx);
        if (!empty($response)) {
            file_put_contents(TMP_DIR . md5($user_ip), $response);
        }
        return json_decode($response, true);
    }
    public static function checkIspIsBlocked($con_isp_name) {
        foreach (ipTV_lib::$customISP as $isp) {
            if (strtolower($con_isp_name) == strtolower($isp['isp'])) {
                return $isp['blocked'];
            }
        }
        return false;
    }
}
