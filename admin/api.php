<?php
include "./functions.php";

if (!isset($_SESSION['hash'])) {
    exit;
}

if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "stream":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_stream"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rStreamID = intval($_GET["stream_id"]);
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo APIRequest(array("action" => "stream", "sub" => $rSub, "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } else if ($rSub == "restart") {
                echo APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } else if ($rSub == "delete") {
                $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $result = $db->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_sys` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($result->fetch_assoc()["count"] == 0) {
                    $db->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                }
                scanBouquets();
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "movie":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_movie"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rStreamID = intval($_GET["stream_id"]);
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo APIRequest(array("action" => "vod", "sub" => $rSub, "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } else if ($rSub == "delete") {
                $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $result = $db->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_sys` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($result->fetch_assoc()["count"] == 0) {
                    $db->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    deleteMovieFile($rServerID, $rStreamID);
                    scanBouquets();
                }
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "episode":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_episode"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rStreamID = intval($_GET["stream_id"]);
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo APIRequest(array("action" => "vod", "sub" => "start", "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } else if ($rSub == "delete") {
                $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $result = $db->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_sys` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($result->fetch_assoc()["count"] == 0) {
                    $db->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    $db->query("DELETE FROM `series_episodes` WHERE `stream_id` = " . intval($rStreamID) . ";");
                    deleteMovieFile($rServerID, $rStreamID);
                    scanBouquets();
                }
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "user":
            $rUserID = intval($_GET["user_id"]);
            // Check if this user falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("user", $rUserID))) {
                echo json_encode(array("result" => False));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_user"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or ($rPermissions["is_admin"])) {
                    if ($rPermissions["is_reseller"]) {
                        $rUserDetails = getUser($rUserID);
                        if ($rUserDetails) {
                            if ($rUserDetails["is_mag"]) {
                                $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rUserDetails["username"]) . "', '" . ESC($rUserDetails["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete MAG</u>]');");
                            } else if ($rUserDetails["is_e2"]) {
                                $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rUserDetails["username"]) . "', '" . ESC($rUserDetails["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Enigma</u>]');");
                            } else {
                                $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rUserDetails["username"]) . "', '" . ESC($rUserDetails["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Line</u>]');");
                            }
                        }
                    }
                    $db->query("DELETE FROM `users` WHERE `id` = " . intval($rUserID) . ";");
                    $db->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rUserID) . ";");
                    $db->query("DELETE FROM `enigma2_devices` WHERE `user_id` = " . intval($rUserID) . ";");
                    $db->query("DELETE FROM `mag_devices` WHERE `user_id` = " . intval($rUserID) . ";");
                    echo json_encode(array("result" => True));
                    exit;
                } else {
                    echo json_encode(array("result" => False));
                    exit;
                }
            } else if ($rSub == "enable") {
                $db->query("UPDATE `users` SET `enabled` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "disable") {
                $db->query("UPDATE `users` SET `enabled` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "ban") {
                if (!$rPermissions["is_admin"]) {
                    echo json_encode(array("result" => False));
                    exit;
                }
                $db->query("UPDATE `users` SET `admin_enabled` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "unban") {
                if (!$rPermissions["is_admin"]) {
                    echo json_encode(array("result" => False));
                    exit;
                }
                $db->query("UPDATE `users` SET `admin_enabled` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
                //isp lock
            } else if ($rSub == "resetispuser") {
                $db->query("UPDATE `users` SET `isp_desc` = NULL WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "lockk") {
                $db->query("UPDATE `users` SET `is_isplock` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "unlockk") {
                $db->query("UPDATE `users` SET `is_isplock` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
                //isp lock	
            } else if ($rSub == "kill") {
                $rResult = $db->query("SELECT `pid`, `server_id` FROM `lines_live` WHERE `user_id` = " . intval($rUserID) . ";");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    while ($rRow = $rResult->fetch_assoc()) {
                        sexec($rRow["server_id"], "kill -9 " . $rRow["pid"]);
                        $db->query("DELETE FROM `lines_live` WHERE `pid` = " . intval($rRow["pid"]) . ";");
                    }
                }
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "user_activity":
            $rPID = intval($_GET["pid"]);
            // Check if the user running this PID falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("pid", $rPID))) {
                echo json_encode(array("result" => False));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "connection_logs"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "kill") {
                $rResult = $db->query("SELECT `server_id` FROM `lines_live` WHERE `pid` = " . intval($rPID) . " LIMIT 1;");
                if (($rResult) && ($rResult->num_rows == 1)) {
                    sexec($rResult->fetch_assoc()["server_id"], "kill -9 " . $rPID);
                    $db->query("DELETE FROM `lines_live` WHERE `pid` = " . $rPID . ";");
                    echo json_encode(array("result" => True));
                    exit;
                }
            }
            echo json_encode(array("result" => False));
            exit;
        case "process":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "process_monitor"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            sexec(intval($_GET["server"]), "kill -9 " . intval($_GET["pid"]));
            echo json_encode(array("result" => True));
            exit;
        case "reg_user":
            $rUserID = intval($_GET["user_id"]);
            // Check if this registered user falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("reg_user", $rUserID))) {
                echo json_encode(array("result" => False));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_reguser"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or ($rPermissions["is_admin"])) {
                    if ($rPermissions["is_reseller"]) {
                        $rUserDetails = getRegisteredUser($rUserID);
                        if ($rUserDetails) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rUserDetails["username"]) . "', '', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Subreseller</u>]');");
                        }
                        $rPrevOwner = getRegisteredUser($rUserDetails["owner_id"]);
                        $rCredits = $rUserDetails["credits"];
                        $rNewCredits = $rPrevOwner["credits"] + $rCredits;
                        $db->query("UPDATE `reg_users` SET `credits` = " . floatval($rNewCredits) . " WHERE `id` = " . intval($rPrevOwner["id"]) . ";");
                    }
                    $db->query("DELETE FROM `reg_users` WHERE `id` = " . intval($rUserID) . ";");
                    echo json_encode(array("result" => True));
                    exit;
                } else {
                    echo json_encode(array("result" => False));
                    exit;
                }
            } else if ($rSub == "reset") {
                $db->query("UPDATE `reg_users` SET `google_2fa_sec` = '' WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "enable") {
                $db->query("UPDATE `reg_users` SET `status` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "disable") {
                $db->query("UPDATE `reg_users` SET `status` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "ticket":
            $rTicketID = intval($_GET["ticket_id"]);
            // Check if this ticket falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("ticket", $rTicketID))) {
                echo json_encode(array("result" => False));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "ticket"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `tickets` WHERE `id` = " . intval($rTicketID) . ";");
                $db->query("DELETE FROM `tickets_replies` WHERE `ticket_id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "close") {
                $db->query("UPDATE `tickets` SET `status` = 0 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "reopen") {
                $db->query("UPDATE `tickets` SET `status` = 1 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "unread") {
                $db->query("UPDATE `tickets` SET `admin_read` = 0 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "read") {
                $db->query("UPDATE `tickets` SET `admin_read` = 1 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "mag":
            $rMagID = intval($_GET["mag_id"]);
            // Check if this device falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("mag", $rMagID))) {
                echo json_encode(array("result" => False));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_mag"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $rMagDetails = getMag($rMagID);
                if (isset($rMagDetails["user_id"])) {
                    $db->query("DELETE FROM `users` WHERE `id` = " . intval($rMagDetails["user_id"]) . ";");
                    $db->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rMagDetails["user_id"]) . ";");
                }
                $db->query("DELETE FROM `mag_devices` WHERE `mag_id` = " . intval($rMagID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "mag_event":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "manage_events"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rMagID = intval($_GET["mag_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `mag_events` WHERE `id` = " . intval($rMagID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "epg":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_epg"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rEPGID = intval($_GET["epg_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `epg` WHERE `id` = " . intval($rEPGID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "profile":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "tprofiles"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rProfileID = intval($_GET["profile_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `transcoding_profiles` WHERE `profile_id` = " . intval($rProfileID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "series":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_series"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSeriesID = intval($_GET["series_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `series` WHERE `id` = " . intval($rSeriesID) . ";");
                $rResult = $db->query("SELECT `stream_id` FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesID) . ";");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    while ($rRow = $rResult->fetch_assoc()) {
                        $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = " . intval($rRow["stream_id"]) . ";");
                        $db->query("DELETE FROM `streams` WHERE `id` = " . intval($rRow["stream_id"]) . ";");
                        deleteMovieFile($rServerID, $rStreamID);
                    }
                    $db->query("DELETE FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesID) . ";");
                    scanBouquets();
                }
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "folder":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "folder_watch"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rFolderID = intval($_GET["folder_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `watch_folders` WHERE `id` = " . intval($rFolderID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "useragent":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "block_uas"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rUAID = intval($_GET["ua_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `blocked_user_agents` WHERE `id` = " . intval($rUAID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "isp":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "block_isps"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rISPID = intval($_GET["isp_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `isp_addon` WHERE `id` = " . intval($rISPID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "ip":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "block_ips"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rIPID = intval($_GET["ip"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $rResult = $db->query("SELECT `ip` FROM `blocked_ips` WHERE `id` = " . intval($rIPID) . ";");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    foreach ($rServers as $rServer) {
                        sexec($rServer["id"], "sudo /sbin/iptables -D INPUT -s " . $rResult->fetch_assoc()["ip"] . " -j DROP");
                    }
                }
                $db->query("DELETE FROM `blocked_ips` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "login_flood":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "add_login_flood"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rIPID = intval($_GET["ip"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `login_flood` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "rtmp_ip":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "add_rtmp"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rIPID = intval($_GET["ip"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `rtmp_ips` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "subreseller_setup":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "subreseller"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rID = intval($_GET["id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `subreseller_setup` WHERE `id` = " . intval($rID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "watch_output":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "folder_watch_output"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rID = intval($_GET["result_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `watch_output` WHERE `id` = " . intval($rID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "enigma":
            $rEnigmaID = intval($_GET["enigma_id"]);
            // Check if this device falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!hasPermissions("e2", $rEnigmaID))) {
                echo json_encode(array("result" => False));
                exit;
            } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_e2"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $rEnigmaDetails = getEnigma($rEnigmaID);
                if (isset($rEnigmaDetails["user_id"])) {
                    $db->query("DELETE FROM `users` WHERE `id` = " . intval($rEnigmaDetails["user_id"]) . ";");
                    $db->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rEnigmaDetails["user_id"]) . ";");
                }
                $db->query("DELETE FROM `enigma2_devices` WHERE `device_id` = " . intval($rEnigmaID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "server":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                if ($rServers[$_GET["server_id"]]["can_delete"] == 1) {
                    $db->query("DELETE FROM `streaming_servers` WHERE `id` = " . intval($rServerID) . ";");
                    $db->query("DELETE FROM `streams_sys` WHERE `server_id` = " . intval($rServerID) . ";");
                    echo json_encode(array("result" => True));
                    exit;
                } else {
                    echo json_encode(array("result" => False));
                    exit;
                }
            } else if ($rSub == "kill") {
                $rResult = $db->query("SELECT `pid`, `server_id` FROM `lines_live` WHERE `server_id` = " . intval($rServerID) . ";");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    while ($rRow = $rResult->fetch_assoc()) {
                        sexec($rRow["server_id"], "kill -9 " . $rRow["pid"]);
                    }
                }
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "start") {
                $rStreamIDs = array();
                $rResult = $db->query("SELECT `stream_id` FROM `streams_sys` WHERE `server_id` = " . intval($rServerID) . " AND `on_demand` = 0;");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    while ($rRow = $rResult->fetch_assoc()) {
                        $rStreamIDs[] = intval($rRow["stream_id"]);
                    }
                }
                if (count($rStreamIDs) > 0) {
                    $rResult = APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array_values($rStreamIDs), "servers" => array(intval($rServerID))));
                }
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "stop") {
                $rStreamIDs = array();
                $rResult = $db->query("SELECT `stream_id` FROM `streams_sys` WHERE `server_id` = " . intval($rServerID) . " AND `on_demand` = 0;");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    while ($rRow = $rResult->fetch_assoc()) {
                        $rStreamIDs[] = intval($rRow["stream_id"]);
                    }
                }
                if (count($rStreamIDs) > 0) {
                    $rResult = APIRequest(array("action" => "stream", "sub" => "stop", "stream_ids" => array_values($rStreamIDs), "servers" => array(intval($rServerID))));
                }
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "package":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_package"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rPackageID = intval($_GET["package_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `packages` WHERE `id` = " . intval($rPackageID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else if (in_array($rSub, array("is_trial", "is_official", "can_gen_mag", "can_gen_e2", "only_mag", "only_e2"))) {
                $db->query("UPDATE `packages` SET `" . ESC($rSub) . "` = " . intval($_GET["value"]) . " WHERE `id` = " . intval($rPackageID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "group":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_group"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rGroupID = intval($_GET["group_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `member_groups` WHERE `group_id` = " . intval($rGroupID) . " AND `can_delete` = 1;");
                echo json_encode(array("result" => True));
                exit;
            } else if (in_array($rSub, array("is_banned", "is_admin", "is_reseller"))) {
                $db->query("UPDATE `member_groups` SET `" . ESC($rSub) . "` = " . intval($_GET["value"]) . " WHERE `group_id` = " . intval($rGroupID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "bouquet":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_bouquet"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rBouquetID = intval($_GET["bouquet_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `bouquets` WHERE `id` = " . intval($rBouquetID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "category":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_cat"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rCategoryID = intval($_GET["category_id"]);
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $db->query("DELETE FROM `stream_categories` WHERE `id` = " . intval($rCategoryID) . ";");
                echo json_encode(array("result" => True));
                exit;
            } else {
                echo json_encode(array("result" => False));
                exit;
            }
        case "get_package":
            $rReturn = array();
            $rOverride = json_decode($rUserInfo["override_packages"], True);
            $rResult = $db->query("SELECT `id`, `bouquets`, `official_credits` AS `cost_credits`, `official_duration`, `official_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = " . intval($_GET["package_id"]) . ";");
            if (($rResult) && ($rResult->num_rows == 1)) {
                $rData = $rResult->fetch_assoc();
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
                foreach (json_decode($rData["bouquets"], True) as $rBouquet) {
                    $rResult = $db->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rBouquet) . ";");
                    if (($rResult) && ($rResult->num_rows == 1)) {
                        $rRow = $rResult->fetch_assoc();
                        $rReturn[] = array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], True), "bouquet_series" => json_decode($rRow["bouquet_series"], True));
                    }
                }
                echo json_encode(array("result" => True, "bouquets" => $rReturn, "data" => $rData));
            } else {
                echo json_encode(array("result" => False));
            }
            exit;
        case "get_package_trial":
            $rReturn = array();
            $rResult = $db->query("SELECT `bouquets`, `trial_credits` AS `cost_credits`, `trial_duration`, `trial_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = " . intval($_GET["package_id"]) . ";");
            if (($rResult) && ($rResult->num_rows == 1)) {
                $rData = $rResult->fetch_assoc();
                $rData["exp_date"] = date('Y-m-d', strtotime('+' . intval($rData["trial_duration"]) . ' ' . $rData["trial_duration_in"]));
                foreach (json_decode($rData["bouquets"], True) as $rBouquet) {
                    $rResult = $db->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rBouquet) . ";");
                    if (($rResult) && ($rResult->num_rows == 1)) {
                        $rRow = $rResult->fetch_assoc();
                        $rReturn[] = array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], True), "bouquet_series" => json_decode($rRow["bouquet_series"], True));
                    }
                }
                echo json_encode(array("result" => True, "bouquets" => $rReturn, "data" => $rData));
            } else {
                echo json_encode(array("result" => False));
            }
            exit;
        case "streams":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "streams"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rData = array();
            $rStreamIDs = json_decode($_GET["stream_ids"], True);
            $rStreams = getStreams(null, false, $rStreamIDs);
            echo json_encode(array("result" => True, "data" => $rStreams));
            exit;
        case "chart_stats":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "index"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rStatistics = array("users" => array(), "conns" => array());
            $rPeriod = intval($rAdminSettings["dashboard_stats_frequency"]) ?: 600;
            $rMax = roundUpToAny(time(), $rPeriod);
            $rMin = $rMax - (60 * 60 * 24 * 7);
            $rResult = $db->query("SELECT `type`, `time`, `count` FROM `dashboard_statistics` WHERE `time` >= " . intval($rMin) . " AND `time` <= " . intval($rMax) . " AND `type` = 'conns';");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    $rStatistics[$rRow["type"]][] = array(intval($rRow["time"]) * 1000, intval($rRow["count"]));
                }
            }
            echo json_encode(array("result" => True, "data" => $rStatistics, "dates" => array("hour" => array($rMax - (60 * 60), $rMax), "day" => array($rMax - (60 * 60 * 24), $rMax), "week" => array(null, null))));
            exit;
        case "stats":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "index"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $return = array("cpu" => 0, "mem" => 0, "uptime" => "--", "total_running_streams" => 0, "bytes_sent" => 0, "bytes_received" => 0, "offline_streams" => 0, "servers" => array());
            if (isset($_GET["server_id"])) {
                $rServerID = intval($_GET["server_id"]);
                $rWatchDog = json_decode($rServers[$rServerID]["watchdog_data"], True);
                if (is_array($rWatchDog)) {
                    $return["uptime"] = $rWatchDog["uptime"];
                    $return["mem"] = intval($rWatchDog["total_mem_used_percent"]);
                    $return["cpu"] = intval($rWatchDog["cpu_avg"]);
                    $return["bytes_received"] = intval($rWatchDog["bytes_received"]);
                    $return["bytes_sent"] = intval($rWatchDog["bytes_sent"]);
                }
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `server_id` = " . $rServerID . ";");
                $return["open_connections"] = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `lines_live`;");
                $return["total_connections"] = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(`user_id`) AS `count` FROM `lines_live` WHERE `server_id` = " . $rServerID . " GROUP BY `user_id`;");
                $return["online_users"] = $result->num_rows;
                $result = $db->query("SELECT COUNT(`user_id`) AS `count` FROM `lines_live` GROUP BY `user_id`;");
                $return["total_users"] = $result->num_rows;
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `stream_status` <> 2 AND `type` IN (1,3);");
                $return["total_streams"] = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `pid` > 0 AND `type` IN (1,3);");
                $return["total_running_streams"] = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = " . $rServerID . " AND ((`streams_sys`.`monitor_pid` IS NOT NULL AND `streams_sys`.`monitor_pid` > 0) AND (`streams_sys`.`pid` IS NULL OR `streams_sys`.`pid` <= 0) AND `streams_sys`.`stream_status` <> 0);");
                $return["offline_streams"] = $result->fetch_assoc()["count"];
                $return["network_guaranteed_speed"] = $rServers[$rServerID]["network_guaranteed_speed"];
            } else {
                $rUptime = 0;
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `lines_live`;");
                $rTotalConnections = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `lines_live` GROUP BY `user_id`;");
                $rTotalUsers = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT `user_id` FROM `lines_live` GROUP BY `user_id`;");
                $return["online_users"] = $result->num_rows;
                $return["open_connections"] = $rTotalConnections;
                foreach (array_keys($rServers) as $rServerID) {
                    $rArray = array();
                    $result = $db->query("SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `server_id` = " . $rServerID . ";");
                    $rArray["open_connections"] = $result->fetch_assoc()["count"];
                    $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `stream_status` <> 2 AND `type` IN (1,3);");
                    $rArray["total_streams"] = $result->fetch_assoc()["count"];
                    $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = " . $rServerID . " AND `pid` > 0 AND `type` IN (1,3);");
                    $rArray["total_running_streams"] = $result->fetch_assoc()["count"];
                    $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = " . $rServerID . " AND ((`streams_sys`.`monitor_pid` IS NOT NULL AND `streams_sys`.`monitor_pid` > 0) AND (`streams_sys`.`pid` IS NULL OR `streams_sys`.`pid` <= 0) AND `streams_sys`.`stream_status` <> 0);");
                    $rArray["offline_streams"] = $result->fetch_assoc()["count"];
                    $rArray["network_guaranteed_speed"] = $rServers[$rServerID]["network_guaranteed_speed"];
                    $result = $db->query("SELECT `user_id` FROM `lines_live` WHERE `server_id` = " . intval($rServerID) . " GROUP BY `user_id`;");
                    $rArray["online_users"] = $result->num_rows;
                    $rWatchDog = json_decode($rServers[$rServerID]["watchdog_data"], True);
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
                echo json_encode(array("result" => False));
                exit;
            }
            $return = array("open_connections" => 0, "online_users" => 0, "active_accounts" => 0, "credits" => 0);
            $result = $db->query("SELECT `activity_id` FROM `lines_live` AS `a` LEFT JOIN `users` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (" . ESC(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))) . ");");
            $return["open_connections"] = $result->num_rows;
            $result = $db->query("SELECT `activity_id` FROM `lines_live` AS `a` LEFT JOIN `users` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (" . ESC(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))) . ") GROUP BY `a`.`user_id`;");
            $return["online_users"] = $result->num_rows;
            $result = $db->query("SELECT `id` FROM `users` WHERE `member_id` IN (" . ESC(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))) . ");");
            $return["active_accounts"] = $result->num_rows;
            $return["credits"] = $rUserInfo["credits"];
            echo json_encode($return);
            exit;
        case "review_selection":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "edit_cchannel") && (!hasPermissions("adv", "create_channel"))))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $return = array("streams" => array(), "result" => true);
            if (isset($_POST["data"])) {
                foreach ($_POST["data"] as $rStreamID) {
                    $rResult = $db->query("SELECT `id`, `stream_display_name`, `stream_source` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if (($rResult) && ($rResult->num_rows == 1)) {
                        $rData = $rResult->fetch_assoc();
                        $return["streams"][] = $rData;
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "review_bouquet":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "edit_bouquet") && (!hasPermissions("adv", "add_bouquet"))))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $return = array("streams" => array(), "vod" => array(), "series" => array(), "radios" => array(), "result" => true);
            // stream
            if (isset($_POST["data"]["stream"])) {
                foreach ($_POST["data"]["stream"] as $rStreamID) {
                    $rResult = $db->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if (($rResult) && ($rResult->num_rows == 1)) {
                        $return["streams"][] = $rResult->fetch_assoc();
                    }
                }
            }
            // vod
            if (isset($_POST["data"]["vod"])) {
                foreach ($_POST["data"]["vod"] as $rStreamID) {
                    $rResult = $db->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if (($rResult) && ($rResult->num_rows == 1)) {
                        $return["vod"][] = $rResult->fetch_assoc();
                    }
                }
            }
            // radios
            if (isset($_POST["data"]["radios"])) {
                foreach ($_POST["data"]["radios"] as $rStreamID) {
                    $rResult = $db->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if (($rResult) && ($rResult->num_rows == 1)) {
                        $return["radios"][] = $rResult->fetch_assoc();
                    }
                }
            }
            // series
            if (isset($_POST["data"]["series"])) {
                foreach ($_POST["data"]["series"] as $rSeriesID) {
                    $rResult = $db->query("SELECT `id`, `title` FROM `series` WHERE `id` = " . intval($rSeriesID) . ";");
                    if (($rResult) && ($rResult->num_rows == 1)) {
                        $rData = $rResult->fetch_assoc();
                        $return["series"][] = $rData;
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "userlist":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "edit_e2") && (!hasPermissions("adv", "add_e2")) && (!hasPermissions("adv", "add_mag")) && (!hasPermissions("adv", "edit_mag"))))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $return = array("total_count" => 0, "items" => array(), "result" => true);
            if (isset($_GET["search"])) {
                if (isset($_GET["page"])) {
                    $rPage = intval($_GET["page"]);
                } else {
                    $rPage = 1;
                }
                $rResult = $db->query("SELECT COUNT(`id`) AS `id` FROM `users` WHERE `username` LIKE '%" . ESC($_GET["search"]) . "%' AND `is_e2` = 0 AND `is_mag` = 0;");
                $return["total_count"] = $rResult->fetch_assoc()["id"];
                $rResult = $db->query("SELECT `id`, `username` FROM `users` WHERE `username` LIKE '%" . ESC($_GET["search"]) . "%' AND `is_e2` = 0 AND `is_mag` = 0 ORDER BY `username` ASC LIMIT " . (($rPage - 1) * 100) . ", 100;");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    while ($rRow = $rResult->fetch_assoc()) {
                        $return["items"][] = array("id" => $rRow["id"], "text" => $rRow["username"]);
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "streamlist":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "manage_mag"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $return = array("total_count" => 0, "items" => array(), "result" => true);
            if (isset($_GET["search"])) {
                if (isset($_GET["page"])) {
                    $rPage = intval($_GET["page"]);
                } else {
                    $rPage = 1;
                }
                $rResult = $db->query("SELECT COUNT(`id`) AS `id` FROM `streams` WHERE `stream_display_name` LIKE '%" . ESC($_GET["search"]) . "%';");
                $return["total_count"] = $rResult->fetch_assoc()["id"];
                $rResult = $db->query("SELECT `id`, `stream_display_name` FROM `streams` WHERE `stream_display_name` LIKE '%" . ESC($_GET["search"]) . "%' ORDER BY `stream_display_name` ASC LIMIT " . (($rPage - 1) * 100) . ", 100;");
                if (($rResult) && ($rResult->num_rows > 0)) {
                    while ($rRow = $rResult->fetch_assoc()) {
                        $return["items"][] = array("id" => $rRow["id"], "text" => $rRow["stream_display_name"]);
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "force_epg":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "epg"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $db->query("TRUNCATE TABLE `epg_data`;");
            sexec($_INFO["server_id"], "/home/xtreamcodes/iptv_xtream_codes/php/bin/php /home/xtreamcodes/iptv_xtream_codes/crons/epg.php");
            echo json_encode(array("result" => True));
            exit;
        case "tmdb_search":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_series")) && (!hasPermissions("adv", "edit_series")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")))) {
                echo json_encode(array("result" => False));
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
                    $rJSON[] = json_decode($rResult->getJSON(), True);
                }
            } else if ($_GET["type"] == "series") {
                $rResults = $rTMDB->searchTVShow($rTerm);
                foreach ($rResults as $rResult) {
                    $rJSON[] = json_decode($rResult->getJSON(), True);
                }
            } else {
                $rJSON = json_decode($rTMDB->getSeason($rTerm, intval($_GET["season"]))->getJSON(), True);
            }
            if (count($rJSON) > 0) {
                echo json_encode(array("result" => True, "data" => $rJSON));
                exit;
            }
            echo json_encode(array("result" => False));
            exit;
        case "tmdb":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_series")) && (!hasPermissions("adv", "edit_series")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")))) {
                echo json_encode(array("result" => False));
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
                $rResult = json_decode($rMovie->getJSON(), True);
                $rResult["trailer"] = $rMovie->getTrailer();
            } else if ($_GET["type"] == "series") {
                $rSeries = $rTMDB->getTVShow($rID);
                $rResult = json_decode($rSeries->getJSON(), True);
                $rResult["trailer"] = getSeriesTrailer($rID);
            }
            if ($rResult) {
                echo json_encode(array("result" => True, "data" => $rResult));
                exit;
            }
            echo json_encode(array("result" => False));
            exit;
        case "listdir":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "create_channel")) && (!hasPermissions("adv", "edit_cchannel")) && (!hasPermissions("adv", "folder_watch_add")))) {
                echo json_encode(array("result" => False));
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
                echo json_encode(array("result" => True, "data" => listDir(intval($_GET["server"]), $_GET["dir"], $rFilter)));
                exit;
            }
            echo json_encode(array("result" => False));
            exit;
        case "fingerprint":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "fingerprint"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rData = json_decode($_GET["data"], true);
            $rActiveServers = array();
            foreach ($rServers as $rServer) {
                if (((((time() - $rServer["last_check_ago"]) > 360)) or ($rServer["status"] == 2)) and ($rServer["can_delete"] == 1) and ($rServer["status"] <> 3)) {
                    $rServerError = True;
                } else {
                    $rServerError = False;
                }
                if (($rServer["status"] == 1) && (!$rServerError)) {
                    $rActiveServers[] = $rServer["id"];
                }
            }
            if (($rData["id"] > 0) && ($rData["font_size"] > 0) && (strlen($rData["font_color"]) > 0) && (strlen($rData["xy_offset"]) > 0) && ((strlen($rData["message"]) > 0) or ($rData["type"] < 3))) {
                $result = $db->query("SELECT `lines_live`.`activity_id`, `lines_live`.`user_id`, `lines_live`.`server_id`, `users`.`username` FROM `lines_live` LEFT JOIN `users` ON `users`.`id` = `lines_live`.`user_id` WHERE `lines_live`.`container` = 'ts' AND `stream_id` = " . intval($rData["id"]) . ";");
                if (($result) && ($result->num_rows > 0)) {
                    set_time_limit(360);
                    ini_set('max_execution_time', 360);
                    ini_set('default_socket_timeout', 15);
                    while ($row = $result->fetch_assoc()) {
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
                    echo json_encode(array("result" => True));
                    exit;
                }
            }
            echo json_encode(array("result" => False));
            exit;
        case "restart_services":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $rServer = $rServers[$rServerID];
                $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "restart");
                file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
                startcmd();
                echo json_encode(array("result" => True));
                exit;
            }
            startcmd();
            echo json_encode(array("result" => False));
            exit;
        case "reboot_server":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $rServer = $rServers[$rServerID];
                $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "reboot");
                file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
                startcmd();
                echo json_encode(array("result" => True));
                exit;
            }
            startcmd();
            echo json_encode(array("result" => False));
            exit;
        case "remake_server":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $rServer = $rServers[$rServerID];
                $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "sreload");
                file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
                startcmd();
                echo json_encode(array("result" => True));
                exit;
            }
            startcmd();
            echo json_encode(array("result" => False));
            exit;
        case "remake_balancer":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $rServer = $rServers[$rServerID];
                $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "breload");
                file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
                startcmd();
                echo json_encode(array("result" => True));
                exit;
            }
            startcmd();
            echo json_encode(array("result" => False));
            exit;
        case "fremake_server":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $rServer = $rServers[$rServerID];
                $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "fsremake");
                file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
                startcmd();
                echo json_encode(array("result" => True));
                exit;
            }
            startcmd();
            echo json_encode(array("result" => False));
            exit;
        case "fremake_balancer":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $rServer = $rServers[$rServerID];
                $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "fbremake");
                file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
                startcmd();
                echo json_encode(array("result" => True));
                exit;
            }
            startcmd();
            echo json_encode(array("result" => False));
            exit;
        case "update_release":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rServerID = intval($_GET["server_id"]);
            if (isset($rServers[$rServerID])) {
                $rServer = $rServers[$rServerID];
                $rJSON = array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "urelease");
                file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/" . $rServerID . ".json", json_encode($rJSON));
                startcmd();
                echo json_encode(array("result" => True));
                exit;
                startcmd();
            }
            echo json_encode(array("result" => False));
            exit;
        case "map_stream":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_stream")) && (!hasPermissions("adv", "edit_stream")))) {
                echo json_encode(array("result" => False));
                exit;
            }
            set_time_limit(300);
            ini_set('max_execution_time', 300);
            ini_set('default_socket_timeout', 300);
            echo shell_exec("/home/xtreamcodes/iptv_xtream_codes/bin/ffprobe -v quiet -probesize 4000000 -print_format json -show_format -show_streams \"" . $_GET["stream"] . "\"");
            exit;
        case "clear_logs":
            if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "reg_userlog")) && (!hasPermissions("adv", "client_request_log")) && (!hasPermissions("adv", "connection_logs")) && (!hasPermissions("adv", "stream_errors")) && (!hasPermissions("adv", "panel_errors")) && (!hasPermissions("adv", "credits_log")) && (!hasPermissions("adv", "folder_watch_settings")))) {
                echo json_encode(array("result" => False));
                exit;
            }
            if (strlen($_GET["from"]) == 0) {
                $rStartTime = null;
            } else if (!$rStartTime = strtotime($_GET["from"] . " 00:00:00")) {
                echo json_encode(array("result" => False));
                exit;
            }
            if (strlen($_GET["to"]) == 0) {
                $rEndTime = null;
            } else if (!$rEndTime = strtotime($_GET["to"] . " 23:59:59")) {
                echo json_encode(array("result" => False));
                exit;
            }
            if (in_array($_GET["type"], array("client_logs", "stream_logs", "user_activity", "credits_log", "reg_userlog", "panel_logs"))) {
                if ($_GET["type"] == "user_activity") {
                    $rColumn = "date_start";
                } else {
                    $rColumn = "date";
                }
                if (($rStartTime) && ($rEndTime)) {
                    $db->query("DELETE FROM `" . ESC($_GET["type"]) . "` WHERE `" . $rColumn . "` >= " . intval($rStartTime) . " AND `" . $rColumn . "` <= " . intval($rEndTime) . ";");
                } else if ($rStartTime) {
                    $db->query("DELETE FROM `" . ESC($_GET["type"]) . "` WHERE `" . $rColumn . "` >= " . intval($rStartTime) . ";");
                } else if ($rEndTime) {
                    $db->query("DELETE FROM `" . ESC($_GET["type"]) . "` WHERE `" . $rColumn . "` <= " . intval($rEndTime) . ";");
                } else {
                    $db->query("DELETE FROM `" . ESC($_GET["type"]) . "`;");
                }
            } else if ($_GET["type"] == "watch_output") {
                if (($rStartTime) && ($rEndTime)) {
                    $db->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= " . intval($rStartTime) . " AND UNIX_TIMESTAMP(`dateadded`) <= " . intval($rEndTime) . ";");
                } else if ($rStartTime) {
                    $db->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= " . intval($rStartTime) . ";");
                } else if ($rEndTime) {
                    $db->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) <= " . intval($rEndTime) . ";");
                } else {
                    $db->query("DELETE FROM `watch_output`;");
                }
            }
            echo json_encode(array("result" => True));
            exit;
        case "backup":
            if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "database"))) {
                echo json_encode(array("result" => False));
                exit;
            }
            $rSub = $_GET["sub"];
            if ($rSub == "delete") {
                $rBackup = pathinfo($_GET["filename"])["filename"];
                if (file_exists(MAIN_DIR . "adtools/backups/" . $rBackup . ".sql")) {
                    unlink(MAIN_DIR . "adtools/backups/" . $rBackup . ".sql");
                }
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "restore") {
                $rBackup = pathinfo($_GET["filename"])["filename"];
                $rFilename = MAIN_DIR . "adtools/backups/" . $rBackup . ".sql";
                $rCommand = "mysql -u " . $_INFO["db_user"] . " -p" . $_INFO["db_pass"] . " -P " . $_INFO["db_port"] . " " . $_INFO["db_name"] . " < \"" . $rFilename . "\"";
                $rRet = shell_exec($rCommand);
                echo json_encode(array("result" => True));
                exit;
            } else if ($rSub == "backup") {
                $rFilename = MAIN_DIR . "adtools/backups/backup_" . date("Y-m-d_H:i:s") . ".sql";
                $rCommand = "mysqldump -u " . $_INFO["db_user"] . " -p" . $_INFO["db_pass"] . " -P " . $_INFO["db_port"] . " " . $_INFO["db_name"] . " --ignore-table=xtream_iptvpro.user_activity --ignore-table=xtream_iptvpro.stream_logs --ignore-table=xtream_iptvpro.panel_logs --ignore-table=xtream_iptvpro.client_logs --ignore-table=xtream_iptvpro.epg_data > \"" . $rFilename . "\"";
                $rRet = shell_exec($rCommand);
                if (file_exists($rFilename)) {
                    $rBackups = getBackups();
                    if ((count($rBackups) > intval($rAdminSettings["backups_to_keep"])) && (intval($rAdminSettings["backups_to_keep"]) > 0)) {
                        $rDelete = array_slice($rBackups, 0, count($rBackups) - intval($rAdminSettings["backups_to_keep"]));
                        foreach ($rDelete as $rItem) {
                            if (file_exists(MAIN_DIR . "adtools/backups/" . $rItem["filename"])) {
                                unlink(MAIN_DIR . "adtools/backups/" . $rItem["filename"]);
                            }
                        }
                    }
                    echo json_encode(array("result" => True, "data" => array("filename" => pathinfo($rFilename)["filename"] . ".sql", "timestamp" => filemtime($rFilename), "date" => date("Y-m-d H:i:s", filemtime($rFilename)))));
                    exit;
                }
                echo json_encode(array("result" => True));
                exit;
            }
            echo json_encode(array("result" => False));
            exit;
            /* 
         case "send_event":
            if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "manage_events"))) { echo json_encode(Array("result" => False)); exit; }
            $rData = json_decode($_GET["data"], True);
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
                    echo json_encode(Array("result" => True));exit;
                } else {
                    $rData["need_confirm"] = 0;
                    $rData["reboot_portal"] = 0;
                    $rData["message"] = "";
                }
                if ($db->query("INSERT INTO `mag_events`(`status`, `mag_device_id`, `event`, `need_confirm`, `msg`, `reboot_after_ok`, `send_time`) VALUES (0, ".intval($rData["id"]).", '".ESC($rData["type"])."', ".intval($rData["need_confirm"]).", '".ESC($rData["message"])."', ".intval($rData["reboot_portal"]).", ".intval(time()).");")) {
                    echo json_encode(Array("result" => True));exit;
                }
            }
            echo json_encode(Array("result" => False));exit;
        }  
        */
            // SEND MAG EVENT RESELLERS
        case "send_event":
            if (($rPermissions["is_admin"]) && (hasPermissions("adv", "manage_events")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_mag_events"]))) {
                $rData = json_decode($_GET["data"], True);
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
                        echo json_encode(array("result" => True));
                        exit;
                    } else {
                        $rData["need_confirm"] = 0;
                        $rData["reboot_portal"] = 0;
                        $rData["message"] = "";
                    }
                    if ((!$rPermissions["is_admin"]) && !$rData["message"] == 0) {
                        $rData["reseller_message_prefix"] = "Reseller Sent: ";
                    }
                    if ($db->query("INSERT INTO `mag_events`(`status`, `mag_device_id`, `event`, `need_confirm`, `msg`, `reboot_after_ok`, `send_time`) VALUES (0, " . intval($rData["id"]) . ", '" . ESC($rData["type"]) . "', " . intval($rData["need_confirm"]) . ", '" . ESC($rData["reseller_message_prefix"]) . "" . ESC($rData["message"]) . "', " . intval($rData["reboot_portal"]) . ", " . intval(time()) . ");")) {
                        echo json_encode(array("result" => True));
                        exit;
                    }
                }
                echo json_encode(array("result" => False));
                exit;
            }
            // SEND MAG EVENT RESELLERS
            else {
                echo json_encode(array("result" => False));
                exit;
            }
    }
}

echo json_encode(array("result" => False));
