<?php
include "./functions.php";

if (!isset($_SESSION['hash'])) {
    exit;
}

if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "stream":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_stream"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStreamID = intval($_GET["stream_id"]);
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo APIRequest(array("action" => "stream", "sub" => $rSub, "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } elseif ($rSub == "restart") {
                echo APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } elseif ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $ipTV_db_admin->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($ipTV_db_admin->get_row()["count"] == 0) {
                    $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                }
                scanBouquets();
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "movie":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_movie"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStreamID = intval($_GET["stream_id"]);
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo APIRequest(array("action" => "vod", "sub" => $rSub, "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } else if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $ipTV_db_admin->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($ipTV_db_admin->get_row()["count"] == 0) {
                    $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    deleteMovieFile($rServerID, $rStreamID);
                    scanBouquets();
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "episode":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_episode"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStreamID = intval($_GET["stream_id"]);
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo APIRequest(array("action" => "vod", "sub" => "start", "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } else if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $ipTV_db_admin->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($ipTV_db_admin->get_row()["count"] == 0) {
                    $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `series_episodes` WHERE `stream_id` = " . intval($rStreamID) . ";");
                    deleteMovieFile($rServerID, $rStreamID);
                    scanBouquets();
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "user":
            $rUserID = intval($_GET["user_id"]);
            // Check if this user falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("user", $rUserID))) {
                echo json_encode(array("result" => false));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_user"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or ($rPermissions["is_admin"])) {
                    if ($rPermissions["is_reseller"]) {
                        $rUserDetails = getUser($rUserID);
                        if ($rUserDetails) {
                            if ($rUserDetails["is_mag"]) {
                                $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '" . $rUserDetails["password"] . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete MAG</u>]');");
                            } else if ($rUserDetails["is_e2"]) {
                                $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '" . $rUserDetails["password"] . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Enigma</u>]');");
                            } else {
                                $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '" . $rUserDetails["password"] . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Line</u>]');");
                            }
                        }
                    }
                    $ipTV_db_admin->query("DELETE FROM `users` WHERE `id` = " . intval($rUserID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rUserID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `enigma2_devices` WHERE `user_id` = " . intval($rUserID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `mag_devices` WHERE `user_id` = " . intval($rUserID) . ";");
                    echo json_encode(array("result" => true));
                    exit;
                } else {
                    echo json_encode(array("result" => false));
                    exit;
                }
            } else if ($rSub == "enable") {
                $ipTV_db_admin->query("UPDATE `users` SET `enabled` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "disable") {
                $ipTV_db_admin->query("UPDATE `users` SET `enabled` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "ban") {
                if (!$rPermissions["is_admin"]) {
                    echo json_encode(array("result" => false));
                    exit;
                }
                $ipTV_db_admin->query("UPDATE `users` SET `admin_enabled` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "unban") {
                if (!$rPermissions["is_admin"]) {
                    echo json_encode(array("result" => false));
                    exit;
                }
                $ipTV_db_admin->query("UPDATE `users` SET `admin_enabled` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
                //isp lock
            } else if ($rSub == "resetispuser") {
                $ipTV_db_admin->query("UPDATE `users` SET `isp_desc` = NULL WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "lockk") {
                $ipTV_db_admin->query("UPDATE `users` SET `is_isplock` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "unlockk") {
                $ipTV_db_admin->query("UPDATE `users` SET `is_isplock` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
                //isp lock	
            } else if ($rSub == "kill") {
                $ipTV_db_admin->query("SELECT `pid`, `server_id` FROM `lines_live` WHERE `user_id` = " . intval($rUserID) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        sexec($rRow["server_id"], "kill -9 " . $rRow["pid"]);
                        $ipTV_db_admin->query("DELETE FROM `lines_live` WHERE `pid` = " . intval($rRow["pid"]) . ";");
                    }
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "user_activity":
            $rPID = intval($_GET["pid"]);
            // Check if the user running this PID falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("pid", $rPID))) {
                echo json_encode(array("result" => false));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "connection_logs"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "kill") {
                $ipTV_db_admin->query("SELECT `server_id` FROM `lines_live` WHERE `pid` = " . intval($rPID) . " LIMIT 1;");
                if ($ipTV_db_admin->num_rows() == 1) {
                    sexec($ipTV_db_admin->get_row()["server_id"], "kill -9 " . $rPID);
                    $ipTV_db_admin->query("DELETE FROM `lines_live` WHERE `pid` = " . $rPID . ";");
                    echo json_encode(array("result" => true));
                    exit;
                }
            }
            echo json_encode(array("result" => false));
            exit;
        case "process":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "process_monitor"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            sexec(intval($_GET["server"]), "kill -9 " . intval($_GET["pid"]));
            echo json_encode(array("result" => true));
            exit;
        case "reg_user":
            $rUserID = intval($_GET["user_id"]);
            // Check if this registered user falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("reg_user", $rUserID))) {
                echo json_encode(array("result" => false));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_reguser"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or ($rPermissions["is_admin"])) {
                    if ($rPermissions["is_reseller"]) {
                        $rUserDetails = getRegisteredUser($rUserID);
                        if ($rUserDetails) {
                            $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Subreseller</u>]');");
                        }
                        $rPrevOwner = getRegisteredUser($rUserDetails["owner_id"]);
                        $rCredits = $rUserDetails["credits"];
                        $rNewCredits = $rPrevOwner["credits"] + $rCredits;
                        $ipTV_db_admin->query("UPDATE `reg_users` SET `credits` = " . floatval($rNewCredits) . " WHERE `id` = " . intval($rPrevOwner["id"]) . ";");
                    }
                    $ipTV_db_admin->query("DELETE FROM `reg_users` WHERE `id` = " . intval($rUserID) . ";");
                    echo json_encode(array("result" => true));
                    exit;
                } else {
                    echo json_encode(array("result" => false));
                    exit;
                }
                // } else if ($rSub == "reset") {
                //     $ipTV_db_admin->query("UPDATE `reg_users` SET `google_2fa_sec` = '' WHERE `id` = " . intval($rUserID) . ";");
                //     echo json_encode(array("result" => true));
                //     exit;
            } else if ($rSub == "enable") {
                $ipTV_db_admin->query("UPDATE `reg_users` SET `status` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "disable") {
                $ipTV_db_admin->query("UPDATE `reg_users` SET `status` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "ticket":
            $rTicketID = intval($_GET["ticket_id"]);
            // Check if this ticket falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("ticket", $rTicketID))) {
                echo json_encode(array("result" => false));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "ticket"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `tickets` WHERE `id` = " . intval($rTicketID) . ";");
                $ipTV_db_admin->query("DELETE FROM `tickets_replies` WHERE `ticket_id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "close") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `status` = 0 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "reopen") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `status` = 1 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "unread") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `admin_read` = 0 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "read") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `admin_read` = 1 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "mag":
            $rMagID = intval($_GET["mag_id"]);
            // Check if this device falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("mag", $rMagID))) {
                echo json_encode(array("result" => false));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_mag"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $rMagDetails = getMag($rMagID);
                if (isset($rMagDetails["user_id"])) {
                    $ipTV_db_admin->query("DELETE FROM `users` WHERE `id` = " . intval($rMagDetails["user_id"]) . ";");
                    $ipTV_db_admin->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rMagDetails["user_id"]) . ";");
                }
                $ipTV_db_admin->query("DELETE FROM `mag_devices` WHERE `mag_id` = " . intval($rMagID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "mag_event":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "manage_events"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rMagID = intval($_GET["mag_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `mag_events` WHERE `id` = " . intval($rMagID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "epg":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_epg"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rEPGID = intval($_GET["epg_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `epg` WHERE `id` = " . intval($rEPGID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "profile":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "tprofiles"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rProfileID = intval($_GET["profile_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `transcoding_profiles` WHERE `profile_id` = " . intval($rProfileID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "series":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_series"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSeriesID = intval($_GET["series_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `series` WHERE `id` = " . intval($rSeriesID) . ";");
                $ipTV_db_admin->query("SELECT `stream_id` FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesID) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rRow["stream_id"]) . ";");
                        $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rRow["stream_id"]) . ";");
                        deleteMovieFile($rServerID, $rStreamID);
                    }
                    $ipTV_db_admin->query("DELETE FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesID) . ";");
                    scanBouquets();
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "folder":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "folder_watch"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rFolderID = intval($_GET["folder_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `watch_folders` WHERE `id` = " . intval($rFolderID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "useragent":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "block_uas"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rUAID = intval($_GET["ua_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `blocked_user_agents` WHERE `id` = " . intval($rUAID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "isp":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "block_isps"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rISPID = intval($_GET["isp_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `isp_addon` WHERE `id` = " . intval($rISPID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "ip":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "block_ips"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rIPID = intval($_GET["ip"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("SELECT `ip` FROM `blocked_ips` WHERE `id` = " . intval($rIPID) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($rServers as $rServer) {
                        sexec($rServer["id"], "sudo /sbin/iptables -D INPUT -s " . $ipTV_db_admin->get_row()["ip"] . " -j DROP");
                    }
                }
                $ipTV_db_admin->query("DELETE FROM `blocked_ips` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "login_flood":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "add_login_flood"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rIPID = intval($_GET["ip"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `login_flood` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "rtmp_ip":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "add_rtmp"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rIPID = intval($_GET["ip"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `rtmp_ips` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "subreseller_setup":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "subreseller"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rID = intval($_GET["id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `subreseller_setup` WHERE `id` = " . intval($rID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "watch_output":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "folder_watch_output"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rID = intval($_GET["result_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE `id` = " . intval($rID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "enigma":
            $rEnigmaID = intval($_GET["enigma_id"]);
            // Check if this device falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("e2", $rEnigmaID))) {
                echo json_encode(array("result" => false));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_e2"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $rEnigmaDetails = getEnigma($rEnigmaID);
                if (isset($rEnigmaDetails["user_id"])) {
                    $ipTV_db_admin->query("DELETE FROM `users` WHERE `id` = " . intval($rEnigmaDetails["user_id"]) . ";");
                    $ipTV_db_admin->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rEnigmaDetails["user_id"]) . ";");
                }
                $ipTV_db_admin->query("DELETE FROM `enigma2_devices` WHERE `device_id` = " . intval($rEnigmaID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "server":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                if ($rServers[$_GET["server_id"]]["can_delete"] == 1) {
                    $ipTV_db_admin->query("DELETE FROM `streaming_servers` WHERE `id` = " . intval($rServerID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `server_id` = " . intval($rServerID) . ";");
                    // drop user mysql 
                    $ipTV_db_admin->query("DROP USER 'lb_" . $_GET["server_id"] . "'@'" . $rServers[$_GET["server_id"]]["server_ip"] . "';");
                    echo json_encode(array("result" => true));
                    exit;
                } else {
                    echo json_encode(array("result" => false));
                    exit;
                }
            } else if ($rSub == "kill") {
                $ipTV_db_admin->query("SELECT `pid`, `server_id` FROM `lines_live` WHERE `server_id` = " . intval($rServerID) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        sexec($rRow["server_id"], "kill -9 " . $rRow["pid"]);
                    }
                }
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "start") {
                $rStreamIDs = array();
                $ipTV_db_admin->query("SELECT `stream_id` FROM `streams_servers` WHERE `server_id` = " . intval($rServerID) . " AND `on_demand` = 0;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $rStreamIDs[] = intval($rRow["stream_id"]);
                    }
                }
                if (count($rStreamIDs) > 0) {
                    $rResult = APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array_values($rStreamIDs), "servers" => array(intval($rServerID))));
                }
                echo json_encode(array("result" => true));
                exit;
            } else if ($rSub == "stop") {
                $rStreamIDs = array();
                $ipTV_db_admin->query("SELECT `stream_id` FROM `streams_servers` WHERE `server_id` = " . intval($rServerID) . " AND `on_demand` = 0;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $rStreamIDs[] = intval($rRow["stream_id"]);
                    }
                }
                if (count($rStreamIDs) > 0) {
                    $rResult = APIRequest(array("action" => "stream", "sub" => "stop", "stream_ids" => array_values($rStreamIDs), "servers" => array(intval($rServerID))));
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "package":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_package"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rPackageID = intval($_GET["package_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `packages` WHERE `id` = " . intval($rPackageID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else if (in_array($rSub, array("is_trial", "is_official", "can_gen_mag", "can_gen_e2", "only_mag", "only_e2"))) {
                $ipTV_db_admin->query("UPDATE `packages` SET `" . $rSub . "` = " . intval($_GET["value"]) . " WHERE `id` = " . intval($rPackageID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "group":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_group"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rGroupID = intval($_GET["group_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `member_groups` WHERE `group_id` = " . intval($rGroupID) . " AND `can_delete` = 1;");
                echo json_encode(array("result" => true));
                exit;
            } else if (in_array($rSub, array("is_banned", "is_admin", "is_reseller"))) {
                $ipTV_db_admin->query("UPDATE `member_groups` SET `" . $rSub . "` = " . intval($_GET["value"]) . " WHERE `group_id` = " . intval($rGroupID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "bouquet":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_bouquet"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rBouquetID = intval($_GET["bouquet_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `bouquets` WHERE `id` = " . intval($rBouquetID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "category":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_cat"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rCategoryID = intval($_GET["category_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `stream_categories` WHERE `id` = " . intval($rCategoryID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "get_package":
            $rReturn = array();
            $rOverride = json_decode($rUserInfo["override_packages"], true);
            $ipTV_db_admin->query("SELECT `id`, `bouquets`, `official_credits` AS `cost_credits`, `official_duration`, `official_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = " . intval($_GET["package_id"]) . ";");
            if ($ipTV_db_admin->num_rows() == 1) {
                $rData = $ipTV_db_admin->get_row();
                if ((isset($rOverride[$rData["id"]]["official_credits"])) && (strlen($rOverride[$rData["id"]]["official_credits"]) > 0)) {
                    $rData["cost_credits"] = $rOverride[$rData["id"]]["official_credits"];
                }
                $rData["exp_date"] = date('Y-m-d', strtotime('+' . intval($rData["official_duration"]) . ' ' . $rData["official_duration_in"]));
                if (isset($_GET["user_id"])) {
                    if ($rUser = getUser($_GET["user_id"])) {
                        if (time() < $rUser["exp_date"]) {
                            $rData["exp_date"] = date('Y-m-d', strtotime('+' . intval($rData["official_duration"]) . ' ' . $rData["official_duration_in"], $rUser["exp_date"]));
                        } else {
                            $rData["exp_date"] = date('Y-m-d', strtotime('+' . intval($rData["official_duration"]) . ' ' . $rData["official_duration_in"]));
                        }
                    }
                }
                foreach (json_decode($rData["bouquets"], true) as $rBouquet) {
                    $ipTV_db_admin->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rBouquet) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $rRow = $ipTV_db_admin->get_row();
                        $rReturn[] = array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], true), "bouquet_series" => json_decode($rRow["bouquet_series"], true));
                    }
                }
                echo json_encode(array("result" => true, "bouquets" => $rReturn, "data" => $rData));
            } else {
                echo json_encode(array("result" => false));
            }
            exit;
        case "get_package_trial":
            $rReturn = array();
            $ipTV_db_admin->query("SELECT `bouquets`, `trial_credits` AS `cost_credits`, `trial_duration`, `trial_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = " . intval($_GET["package_id"]) . ";");
            if ($ipTV_db_admin->num_rows() == 1) {
                $rData = $ipTV_db_admin->get_row();
                $rData["exp_date"] = date('Y-m-d', strtotime('+' . intval($rData["trial_duration"]) . ' ' . $rData["trial_duration_in"]));
                foreach (json_decode($rData["bouquets"], true) as $rBouquet) {
                    $ipTV_db_admin->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rBouquet) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $rRow = $ipTV_db_admin->get_row();
                        $rReturn[] = array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], true), "bouquet_series" => json_decode($rRow["bouquet_series"], true));
                    }
                }
                echo json_encode(array("result" => true, "bouquets" => $rReturn, "data" => $rData));
            } else {
                echo json_encode(array("result" => false));
            }
            exit;
        case "streams":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "streams"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rData = array();
            $rStreamIDs = json_decode($_GET["stream_ids"], true);
            $rStreams = getStreams(null, false, $rStreamIDs);
            echo json_encode(array("result" => true, "data" => $rStreams));
            exit;
        case "chart_stats":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "index"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStatistics = array("users" => array(), "conns" => array());
            $rPeriod = intval($rAdminSettings["dashboard_stats_frequency"]) ?: 600;
            $rMax = roundUpToAny(time(), $rPeriod);
            $rMin = $rMax - (60 * 60 * 24 * 7);
            $ipTV_db_admin->query("SELECT `type`, `time`, `count` FROM `dashboard_statistics` WHERE `time` >= " . intval($rMin) . " AND `time` <= " . intval($rMax) . " AND `type` = 'conns';");
            if ($ipTV_db_admin->num_rows() > 0) {
                foreach ($ipTV_db_admin->get_rows() as $rRow) {
                    $rStatistics[$rRow["type"]][] = array(intval($rRow["time"]) * 1000, intval($rRow["count"]));
                }
            }
            echo json_encode(array("result" => true, "data" => $rStatistics, "dates" => array("hour" => array($rMax - (60 * 60), $rMax), "day" => array($rMax - (60 * 60 * 24), $rMax), "week" => array(null, null))));
            exit;
        case "stats":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "index"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("cpu" => 0, "mem" => 0, "uptime" => "--", "total_running_streams" => 0, "bytes_sent" => 0, "bytes_received" => 0, "offline_streams" => 0, "servers" => array());
            if (isset($_GET["server_id"])) {
                $rServerID = intval($_GET["server_id"]);
                $rWatchDog = json_decode($rServers[$rServerID]["watchdog_data"], true);
                if (is_array($rWatchDog)) {
                    $return["uptime"] = $rWatchDog["uptime"];
                    $return["mem"] = intval($rWatchDog["total_mem_used_percent"]);
                    $return["cpu"] = intval($rWatchDog["cpu_avg"]);
                    $return["bytes_received"] = intval($rWatchDog["bytes_received"]);
                    $return["bytes_sent"] = intval($rWatchDog["bytes_sent"]);
                }
                $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `server_id` = " . $rServerID . ";");
                $return["open_connections"] = $ipTV_db_admin->get_row()["count"];
                $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `lines_live`;");
                $return["total_connections"] = $ipTV_db_admin->get_row()["count"];
                $ipTV_db_admin->query("SELECT COUNT(`user_id`) AS `count` FROM `lines_live` WHERE `server_id` = " . $rServerID . " GROUP BY `user_id`;");
                $return["online_users"] = $ipTV_db_admin->num_rows();
                $ipTV_db_admin->query("SELECT COUNT(`user_id`) AS `count` FROM `lines_live` GROUP BY `user_id`;");
                $return["total_users"] = $ipTV_db_admin->num_rows();
                $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `stream_status` <> 2 AND `type` IN (1,3);");
                $return["total_streams"] = $ipTV_db_admin->get_row()["count"];
                $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `pid` > 0 AND `type` IN (1,3);");
                $return["total_running_streams"] = $ipTV_db_admin->get_row()["count"];
                $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = " . $rServerID . " AND ((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0);");
                $return["offline_streams"] = $ipTV_db_admin->get_row()["count"];
                $return["network_guaranteed_speed"] = $rServers[$rServerID]["network_guaranteed_speed"];
            } else {
                $rUptime = 0;
                $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `lines_live`;");
                $rTotalConnections = $ipTV_db_admin->get_row()["count"];
                $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `lines_live` GROUP BY `user_id`;");
                $rTotalUsers = $ipTV_db_admin->get_row()["count"];
                $ipTV_db_admin->query("SELECT `user_id` FROM `lines_live` GROUP BY `user_id`;");
                $return["online_users"] = $ipTV_db_admin->num_rows();
                $return["open_connections"] = $rTotalConnections;
                foreach (array_keys($rServers) as $rServerID) {
                    $rArray = array();
                    $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `server_id` = " . $rServerID . ";");
                    $rArray["open_connections"] = $ipTV_db_admin->get_row()["count"];
                    $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `stream_status` <> 2 AND `type` IN (1,3);");
                    $rArray["total_streams"] = $ipTV_db_admin->get_row()["count"];
                    $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `pid` > 0 AND `type` IN (1,3);");
                    $rArray["total_running_streams"] = $ipTV_db_admin->get_row()["count"];
                    $ipTV_db_admin->query("SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = " . $rServerID . " AND ((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0);");
                    $rArray["offline_streams"] = $ipTV_db_admin->get_row()["count"];
                    $rArray["network_guaranteed_speed"] = $rServers[$rServerID]["network_guaranteed_speed"];
                    $ipTV_db_admin->query("SELECT `user_id` FROM `lines_live` WHERE `server_id` = " . intval($rServerID) . " GROUP BY `user_id`;");
                    $rArray["online_users"] = $ipTV_db_admin->num_rows();
                    $rWatchDog = json_decode($rServers[$rServerID]["watchdog_data"], true);
                    if (is_array($rWatchDog)) {
                        $rArray["uptime"] = $rWatchDog["uptime"];
                        $rArray["mem"] = intval($rWatchDog["total_mem_used_percent"]);
                        $rArray["cpu"] = intval($rWatchDog["cpu_avg"]);
                        $rArray["bytes_received"] = intval($rWatchDog["bytes_received"]);
                        $rArray["bytes_sent"] = intval($rWatchDog["bytes_sent"]);
                    }
                    $rArray["total_connections"] = $rTotalConnections;
                    $rArray["total_users"] = $rTotalUsers;
                    $rArray["server_id"] = $rServerID;
                    $return["servers"][] = $rArray;
                }
                foreach ($return["servers"] as $rServerArray) {
                    $return["total_streams"] += $rServerArray["total_streams"];
                    $return["total_running_streams"] += $rServerArray["total_running_streams"];
                    $return["offline_streams"] += $rServerArray["offline_streams"];
                    $return["bytes_received"] += $rServerArray["bytes_received"]; // total input
                    $return["bytes_sent"] += $rServerArray["bytes_sent"]; // total output
                }
            }
            echo json_encode($return);
            exit;
        case "reseller_dashboard":
            if ($rPermissions["is_admin"]) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("open_connections" => 0, "online_users" => 0, "active_accounts" => 0, "credits" => 0);
            $ipTV_db_admin->query("SELECT `activity_id` FROM `lines_live` AS `a` LEFT JOIN `users` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (" . join(",", array_keys(getRegisteredUsers($rUserInfo["id"]))) . ");");
            $return["open_connections"] = $ipTV_db_admin->num_rows();
            $ipTV_db_admin->query("SELECT `activity_id` FROM `lines_live` AS `a` LEFT JOIN `users` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (" . join(",", array_keys(getRegisteredUsers($rUserInfo["id"]))) . ") GROUP BY `a`.`user_id`;");
            $return["online_users"] = $ipTV_db_admin->num_rows();
            $ipTV_db_admin->query("SELECT `id` FROM `users` WHERE `member_id` IN (" . join(",", array_keys(getRegisteredUsers($rUserInfo["id"]))) . ");");
            $return["active_accounts"] = $ipTV_db_admin->num_rows();
            $return["credits"] = $rUserInfo["credits"];
            echo json_encode($return);
            exit;
        case "review_selection":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "edit_cchannel") && (!hasPermissions("adv", "create_channel"))))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("streams" => array(), "result" => true);
            if (isset($_POST["data"])) {
                foreach ($_POST["data"] as $rStreamID) {
                    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `stream_source` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $rData = $ipTV_db_admin->get_row();
                        $return["streams"][] = $rData;
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "review_bouquet":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "edit_bouquet") && (!hasPermissions("adv", "add_bouquet"))))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("streams" => array(), "vod" => array(), "series" => array(), "radios" => array(), "result" => true);
            // stream
            if (isset($_POST["data"]["stream"])) {
                foreach ($_POST["data"]["stream"] as $rStreamID) {
                    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $return["streams"][] = $ipTV_db_admin->get_row();
                    }
                }
            }
            // vod
            if (isset($_POST["data"]["vod"])) {
                foreach ($_POST["data"]["vod"] as $rStreamID) {
                    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $return["vod"][] = $ipTV_db_admin->get_row();
                    }
                }
            }
            // radios
            if (isset($_POST["data"]["radios"])) {
                foreach ($_POST["data"]["radios"] as $rStreamID) {
                    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $return["radios"][] = $ipTV_db_admin->get_row();
                    }
                }
            }
            // series
            if (isset($_POST["data"]["series"])) {
                foreach ($_POST["data"]["series"] as $rSeriesID) {
                    $ipTV_db_admin->query("SELECT `id`, `title` FROM `series` WHERE `id` = " . intval($rSeriesID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $rData = $ipTV_db_admin->get_row();
                        $return["series"][] = $rData;
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "userlist":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "edit_e2") && (!hasPermissions("adv", "add_e2")) && (!hasPermissions("adv", "add_mag")) && (!hasPermissions("adv", "edit_mag"))))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("total_count" => 0, "items" => array(), "result" => true);
            if (isset($_GET["search"])) {
                if (isset($_GET["page"])) {
                    $rPage = intval($_GET["page"]);
                } else {
                    $rPage = 1;
                }
                $ipTV_db_admin->query("SELECT COUNT(`id`) AS `id` FROM `users` WHERE `username` LIKE '%" . $_GET["search"] . "%' AND `is_e2` = 0 AND `is_mag` = 0;");
                $return["total_count"] = $ipTV_db_admin->get_row()["id"];
                $ipTV_db_admin->query("SELECT `id`, `username` FROM `users` WHERE `username` LIKE '%" . $_GET["search"] . "%' AND `is_e2` = 0 AND `is_mag` = 0 ORDER BY `username` ASC LIMIT " . (($rPage - 1) * 100) . ", 100;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $return["items"][] = array("id" => $rRow["id"], "text" => $rRow["username"]);
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "streamlist":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "manage_mag"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("total_count" => 0, "items" => array(), "result" => true);
            if (isset($_GET["search"])) {
                if (isset($_GET["page"])) {
                    $rPage = intval($_GET["page"]);
                } else {
                    $rPage = 1;
                }
                $ipTV_db_admin->query("SELECT COUNT(`id`) AS `id` FROM `streams` WHERE `stream_display_name` LIKE '%" . $_GET["search"] . "%';");
                $return["total_count"] = $ipTV_db_admin->get_row()["id"];
                $ipTV_db_admin->query("SELECT `id`, `stream_display_name` FROM `streams` WHERE `stream_display_name` LIKE '%" . $_GET["search"] . "%' ORDER BY `stream_display_name` ASC LIMIT " . (($rPage - 1) * 100) . ", 100;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $return["items"][] = array("id" => $rRow["id"], "text" => $rRow["stream_display_name"]);
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "force_epg":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "epg"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $ipTV_db_admin->query("TRUNCATE TABLE `epg_data`;");
            sexec($_INFO["server_id"], "/home/xtreamcodes/bin/php/bin/php /home/xtreamcodes/crons/epg.php");
            echo json_encode(array("result" => true));
            exit;
        case "tmdb_search":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_series")) && (!hasPermissions("adv", "edit_series")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            include "tmdb.php";
            if (strlen($rAdminSettings["tmdb_language"]) > 0) {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rAdminSettings["tmdb_language"]);
            } else {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
            }
            $rTerm = $_GET["term"];
            if ($rAdminSettings["release_parser"] == "php") {
                include "tmdb_release.php";
                $rRelease = new Release($rTerm);
                $rTerm = $rRelease->getTitle();
            } else {
                $rRelease = tmdbParseRelease($rTerm);
                $rTerm = $rRelease["title"];
            }
            $rJSON = array();
            if ($_GET["type"] == "movie") {
                $rResults = $rTMDB->searchMovie($rTerm);
                foreach ($rResults as $rResult) {
                    $rJSON[] = json_decode($rResult->getJSON(), true);
                }
            } else if ($_GET["type"] == "series") {
                $rResults = $rTMDB->searchTVShow($rTerm);
                foreach ($rResults as $rResult) {
                    $rJSON[] = json_decode($rResult->getJSON(), true);
                }
            } else {
                $rJSON = json_decode($rTMDB->getSeason($rTerm, intval($_GET["season"]))->getJSON(), true);
            }
            if (count($rJSON) > 0) {
                echo json_encode(array("result" => true, "data" => $rJSON));
                exit;
            }
            echo json_encode(array("result" => false));
            exit;
        case "tmdb":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_series")) && (!hasPermissions("adv", "edit_series")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            include "tmdb.php";
            if (strlen($rAdminSettings["tmdb_language"]) > 0) {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rAdminSettings["tmdb_language"]);
            } else {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
            }
            $rID = $_GET["id"];
            if ($_GET["type"] == "movie") {
                $rMovie = $rTMDB->getMovie($rID);
                $rResult = json_decode($rMovie->getJSON(), true);
                $rResult["trailer"] = $rMovie->getTrailer();
            } else if ($_GET["type"] == "series") {
                $rSeries = $rTMDB->getTVShow($rID);
                $rResult = json_decode($rSeries->getJSON(), true);
                $rResult["trailer"] = getSeriesTrailer($rID);
            }
            if ($rResult) {
                echo json_encode(array("result" => true, "data" => $rResult));
                exit;
            }
            echo json_encode(array("result" => false));
            exit;
        case "listdir":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "create_channel")) && (!hasPermissions("adv", "edit_cchannel")) && (!hasPermissions("adv", "folder_watch_add")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            if ($_GET["filter"] == "video") {
                $rFilter = array("mp4", "mkv", "avi", "mpg", "flv");
            } else if ($_GET["filter"] == "subs") {
                $rFilter = array("srt", "sub", "sbv");
            } else {
                $rFilter = null;
            }
            if ((isset($_GET["server"])) && (isset($_GET["dir"]))) {
                echo json_encode(array("result" => true, "data" => listDir(intval($_GET["server"]), $_GET["dir"], $rFilter)));
                exit;
            }
            echo json_encode(array("result" => false));
            exit;
        case "fingerprint":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "fingerprint"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rData = json_decode($_GET["data"], true);
            $rActiveServers = array();
            foreach ($rServers as $rServer) {
                if (((((time() - $rServer["last_check_ago"]) > 360)) or ($rServer["status"] == 2)) and ($rServer["can_delete"] == 1) and ($rServer["status"] <> 3)) {
                    $rServerError = true;
                } else {
                    $rServerError = false;
                }
                if (($rServer["status"] == 1) && (!$rServerError)) {
                    $rActiveServers[] = $rServer["id"];
                }
            }
            if (($rData["id"] > 0) && ($rData["font_size"] > 0) && (strlen($rData["font_color"]) > 0) && (strlen($rData["xy_offset"]) > 0) && ((strlen($rData["message"]) > 0) or ($rData["type"] < 3))) {
                $ipTV_db_admin->query("SELECT `lines_live`.`activity_id`, `lines_live`.`user_id`, `lines_live`.`server_id`, `users`.`username` FROM `lines_live` LEFT JOIN `users` ON `users`.`id` = `lines_live`.`user_id` WHERE `lines_live`.`container` = 'ts' AND `stream_id` = " . intval($rData["id"]) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    set_time_limit(360);
                    ini_set('max_execution_time', 360);
                    ini_set('default_socket_timeout', 15);
                    while ($row = $ipTV_db_admin->get_row()) {
                        if (in_array($row["server_id"], $rActiveServers)) {
                            $rArray = array("font_size" => $rData["font_size"], "font_color" => $rData["font_color"], "xy_offset" => $rData["xy_offset"], "message" => "", "activity_id" => $row["activity_id"]);
                            if ($rData["type"] == 1) {
                                $rArray["message"] = "#" . $row["activity_id"];
                            } else if ($rData["type"] == 2) {
                                $rArray["message"] = $row["username"];
                            } else if ($rData["type"] == 3) {
                                $rArray["message"] = $rData["message"];
                            }
                            $rArray["action"] = "signal_send";
                            $rSuccess = SystemAPIRequest(intval($row["server_id"]), $rArray);
                        }
                    }
                    echo json_encode(array("result" => true));
                    exit;
                }
            }
            echo json_encode(array("result" => false));
            exit;
        case "restart_services":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $ipTV_db_admin->query("INSERT INTO `signals`(`server_id`, `custom_data`, `time`) VALUES('" . $rServerID . "', '" . json_encode(array('action' => 'restart_services')) . "', '" . time() . "');");
                echo json_encode(array('result' => true));
            }
            echo json_encode(array('result' => false));
            exit;
        case "reboot_server":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $ipTV_db_admin->query("INSERT INTO `signals`(`server_id`, `custom_data`, `time`) VALUES('" . $rServerID . "', '" . json_encode(array('action' => 'reboot')) . "', '" . time() . "');");
                echo json_encode(array('result' => true));
            }
            echo json_encode(array('result' => false));
            exit;
        // case "update_release":
        //     if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
        //         echo json_encode(array("result" => false));
        //         exit;
        //     }
        //     $rServerID = intval($_GET["server_id"]);
        //     if (isset($rServers[$rServerID])) {
        //         $rServer = $rServers[$rServerID];
        //         $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "urelease");
        //         file_put_contents("/home/xtreamcodes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
        //         echo json_encode(array("result" => true));
        //         exit;
        //     }
        //     echo json_encode(array("result" => false));
        //     exit;
        case "map_stream":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_stream")) && (!hasPermissions("adv", "edit_stream")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            set_time_limit(300);
            ini_set('max_execution_time', 300);
            ini_set('default_socket_timeout', 300);
            echo shell_exec("/home/xtreamcodes/bin/ffprobe -v quiet -probesize 4000000 -print_format json -show_format -show_streams \"" . $_GET["stream"] . "\"");
            exit;
        case "clear_logs":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "reg_userlog")) && (!hasPermissions("adv", "client_request_log")) && (!hasPermissions("adv", "connection_logs")) && (!hasPermissions("adv", "stream_errors")) && (!hasPermissions("adv", "panel_errors")) && (!hasPermissions("adv", "credits_log")) && (!hasPermissions("adv", "folder_watch_settings")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            if (strlen($_GET["from"]) == 0) {
                $rStartTime = null;
            } else if (!$rStartTime = strtotime($_GET["from"] . " 00:00:00")) {
                echo json_encode(array("result" => false));
                exit;
            }
            if (strlen($_GET["to"]) == 0) {
                $rEndTime = null;
            } else if (!$rEndTime = strtotime($_GET["to"] . " 23:59:59")) {
                echo json_encode(array("result" => false));
                exit;
            }
            if (in_array($_GET["type"], array("client_logs", "stream_logs", "user_activity", "credits_log", "reg_userlog", "panel_logs", "mysql_syslog"))) {
                if ($_GET["type"] == "user_activity") {
                    $rColumn = "date_start";
                } else {
                    $rColumn = "date";
                }
                if (($rStartTime) && ($rEndTime)) {
                    $ipTV_db_admin->query("DELETE FROM `" . $_GET["type"] . "` WHERE `" . $rColumn . "` >= " . intval($rStartTime) . " AND `" . $rColumn . "` <= " . intval($rEndTime) . ";");
                } else if ($rStartTime) {
                    $ipTV_db_admin->query("DELETE FROM `" . $_GET["type"] . "` WHERE `" . $rColumn . "` >= " . intval($rStartTime) . ";");
                } else if ($rEndTime) {
                    $ipTV_db_admin->query("DELETE FROM `" . $_GET["type"] . "` WHERE `" . $rColumn . "` <= " . intval($rEndTime) . ";");
                } else {
                    $ipTV_db_admin->query("DELETE FROM `" . $_GET["type"] . "`;");
                }
            } else if ($_GET["type"] == "watch_output") {
                if (($rStartTime) && ($rEndTime)) {
                    $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= " . intval($rStartTime) . " AND UNIX_TIMESTAMP(`dateadded`) <= " . intval($rEndTime) . ";");
                } else if ($rStartTime) {
                    $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= " . intval($rStartTime) . ";");
                } else if ($rEndTime) {
                    $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) <= " . intval($rEndTime) . ";");
                } else {
                    $ipTV_db_admin->query("DELETE FROM `watch_output`;");
                }
            }
            echo json_encode(array("result" => true));
            exit;
        case 'backup':
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "database"))) {
                echo json_encode(array('result' => false));
                exit();
            }
            $rSub = $_GET['sub'];

            if ($rSub == 'delete') {
                $rBackup = pathinfo($_GET['filename'])['filename'];

                if (file_exists(MAIN_DIR . 'backups/' . $rBackup . '.sql')) {
                    unlink(MAIN_DIR . 'backups/' . $rBackup . '.sql');
                }
                echo json_encode(array('result' => true));

                exit();
            }

            if ($rSub == 'restore') {
                $rBackup = pathinfo($_GET["filename"])["filename"];
                $rFilename = MAIN_DIR . "backups/" . $rBackup . ".sql";
                $rCommand = "mysql -u " . $_INFO['username'] . " -p" . $_INFO['password'] . " -P " . $_INFO['port'] . " " . $_INFO['database'] . " < \"" . $rFilename . "\"";
                $rRet = shell_exec($rCommand);
                echo json_encode(array("result" => true));
                exit;
            }

            if ($rSub == 'backup') {
                $rCommand = PHP_BIN . ' ' . CRON_PATH . 'backups.php 1 > /dev/null 2>/dev/null &';
                $rRet = shell_exec($rCommand);
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();

        /* 
 case "send_event":
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "manage_events"))) { echo json_encode(Array("result" => false)); exit; }
    $rData = json_decode($_GET["data"], true);
    $rMag = getMag($rData["id"]);
    if ($rMag) {
        if ($rData["type"] == "send_msg") {
            $rData["need_confirm"] = 1;
        } else if ($rData["type"] == "play_channel") {
            $rData["need_confirm"] = 0;
            $rData["reboot_portal"] = 0;
            $rData["message"] = intval($rData["channel"]);
        } else if ($rData["type"] == "reset_stb_lock") {
            resetSTB($rData["id"]);
            echo json_encode(Array("result" => true));exit;
        } else {
            $rData["need_confirm"] = 0;
            $rData["reboot_portal"] = 0;
            $rData["message"] = "";
        }
        if ($ipTV_db_admin->query("INSERT INTO `mag_events`(`status`, `mag_device_id`, `event`, `need_confirm`, `msg`, `reboot_after_ok`, `send_time`) VALUES (0, ".intval($rData["id"]).", '".$rData["type"]."', ".intval($rData["need_confirm"]).", '".$rData["message"]."', ".intval($rData["reboot_portal"]).", ".intval(time()).");")) {
            echo json_encode(Array("result" => true));exit;
        }
    }
    echo json_encode(Array("result" => false));exit;
}  
*/
        // SEND MAG EVENT RESELLERS
        case "send_event":
            if (($rPermissions["is_admin"]) && (hasPermissions("adv", "manage_events")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_mag_events"]))) {
                $rData = json_decode($_GET["data"], true);
                $rMag = getMag($rData["id"]);
                if ($rMag) {
                    if ($rData["type"] == "send_msg") {
                        $rData["need_confirm"] = 1;
                    } else if ($rData["type"] == "play_channel") {
                        $rData["need_confirm"] = 0;
                        $rData["reboot_portal"] = 0;
                        $rData["message"] = intval($rData["channel"]);
                    } else if ($rData["type"] == "reset_stb_lock") {
                        resetSTB($rData["id"]);
                        echo json_encode(array("result" => true));
                        exit;
                    } else {
                        $rData["need_confirm"] = 0;
                        $rData["reboot_portal"] = 0;
                        $rData["message"] = "";
                    }
                    if ((!$rPermissions["is_admin"]) && !$rData["message"] == 0) {
                        $rData["reseller_message_prefix"] = "Reseller Sent: ";
                    }
                    if ($ipTV_db_admin->query("INSERT INTO `mag_events`(`status`, `mag_device_id`, `event`, `need_confirm`, `msg`, `reboot_after_ok`, `send_time`) VALUES (0, " . intval($rData["id"]) . ", '" . $rData["type"] . "', " . intval($rData["need_confirm"]) . ", '" . $rData["reseller_message_prefix"] . "" . $rData["message"] . "', " . intval($rData["reboot_portal"]) . ", " . intval(time()) . ");")) {
                        echo json_encode(array("result" => true));
                        exit;
                    }
                }
                echo json_encode(array("result" => false));
                exit;
            }
            // SEND MAG EVENT RESELLERS
            else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "enable_cache":
            if (hasPermissions('adv', 'backups')) {
                ipTV_lib::setSettings(["enable_cache" => 1]);

                shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php');

                $rCache = intval(trim(shell_exec('pgrep -U xtreamcodes | xargs ps -f -p | grep cache_handler | grep -v grep | grep -v pgrep | wc -l')));
                if ($rCache == 0) {
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'cache_handler.php > /dev/null 2>/dev/null &');
                }
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();

        case "disable_cache":
            if (hasPermissions('adv', 'backups')) {
                ipTV_lib::setSettings(["enable_cache" => 0]);

                shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache.php');
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();
        case 'regenerate_cache':
            if (hasPermissions('adv', 'backups')) {
                shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "force"');
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();

        case 'disable_handler':
            if (hasPermissions('adv', 'backups')) {
                ipTV_lib::setSettings(["redis_handler" => 0]);

                if (file_exists(CACHE_TMP_PATH . 'settings')) {
                    unlink(CACHE_TMP_PATH . 'settings');
                }

                exec('pgrep -u xtreamcodes redis-server', $rRedis);

                if (0 < count($rRedis) && is_numeric($rRedis[0])) {
                    $rPID = intval($rRedis[0]);
                    shell_exec('kill -9 ' . $rPID);
                }

                exec("pgrep -U xtreamcodes | xargs ps | grep signals | awk '{print \$1}'", $rPID);

                if (0 < count($rPID) && is_numeric($rPID[0])) {
                    $rPID = intval($rPID[0]);
                    shell_exec('kill -9 ' . $rPID);
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'signals.php > /dev/null 2>/dev/null &');
                }

                exec("pgrep -U xtreamcodes | xargs ps | grep watchdog | awk '{print \$1}'", $rPID);

                if (0 < count($rPID) && is_numeric($rPID[0])) {
                    $rPID = intval($rPID[0]);
                    shell_exec('kill -9 ' . $rPID);
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'watchdog.php > /dev/null 2>/dev/null &');
                }

                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();

        case 'enable_handler':
            if (hasPermissions('adv', 'backups')) {
                ipTV_lib::setSettings(["redis_handler" => 1]);

                if (file_exists(CACHE_TMP_PATH . 'settings')) {
                    unlink(CACHE_TMP_PATH . 'settings');
                }

                exec('pgrep -u xtreamcodes redis-server', $rRedis);

                if (count($rRedis) < 0 && !is_numeric($rRedis[0])) {
                    $rPID = intval($rRedis[0]);
                    shell_exec('kill -9 ' . $rPID);
                }

                shell_exec(BIN_PATH . 'redis/redis-server ' . BIN_PATH . 'redis/redis.conf > /dev/null 2>/dev/null &');
                sleep(1);
                exec("pgrep -U xtreamcodes | xargs ps | grep signals | awk '{print \$1}'", $rPID);

                if (0 < count($rPID) && is_numeric($rPID[0])) {
                    $rPID = intval($rPID[0]);
                    shell_exec('kill -9 ' . $rPID);
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'signals.php > /dev/null 2>/dev/null &');
                }

                exec("pgrep -U xtreamcodes | xargs ps | grep watchdog | awk '{print \$1}'", $rPID);

                if (0 < count($rPID) && is_numeric($rPID[0])) {
                    $rPID = intval($rPID[0]);
                    shell_exec('kill -9 ' . $rPID);
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'watchdog.php > /dev/null 2>/dev/null &');
                }

                shell_exec(PHP_BIN . ' ' . CRON_PATH . 'users.php 1 > /dev/null 2>/dev/null &');
                echo json_encode(array('result' => true));

                exit();
            }
            echo json_encode(array('result' => false));
            exit();

        case 'clear_redis':
            if (hasPermissions('adv', 'backups')) {
                iptv_lib::$redis->flushAll();
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();
    }
}

echo json_encode(array("result" => false));
