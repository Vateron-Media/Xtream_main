<?php
include "./functions.php";

if (!isset($_SESSION['hash'])) {
    exit;
}

if (isset(CoreUtilities::$request["action"])) {
    switch (CoreUtilities::$request["action"]) {
        case "stream":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_stream"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStreamID = intval(CoreUtilities::$request["stream_id"]);
            $rServerID = intval(CoreUtilities::$request["server_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo UIController::APIRequest(array("action" => "stream", "sub" => $rSub, "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } elseif ($rSub == "restart") {
                echo UIController::APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } elseif ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $ipTV_db_admin->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($ipTV_db_admin->get_row()["count"] == 0) {
                    $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                }
                UIController::scanBouquets();
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "movie":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_movie"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStreamID = intval(CoreUtilities::$request["stream_id"]);
            $rServerID = intval(CoreUtilities::$request["server_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo UIController::APIRequest(array("action" => "vod", "sub" => $rSub, "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } elseif ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $ipTV_db_admin->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($ipTV_db_admin->get_row()["count"] == 0) {
                    $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    UIController::deleteMovieFile($rServerID, $rStreamID);
                    UIController::scanBouquets();
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "episode":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_episode"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStreamID = intval(CoreUtilities::$request["stream_id"]);
            $rServerID = intval(CoreUtilities::$request["server_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if (in_array($rSub, array("start", "stop"))) {
                echo UIController::APIRequest(array("action" => "vod", "sub" => "start", "stream_ids" => array($rStreamID), "servers" => array($rServerID)));
                exit;
            } elseif ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . " AND `server_id` = " . intval($rServerID) . ";");
                $ipTV_db_admin->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($ipTV_db_admin->get_row()["count"] == 0) {
                    $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `series_episodes` WHERE `stream_id` = " . intval($rStreamID) . ";");
                    UIController::deleteMovieFile($rServerID, $rStreamID);
                    UIController::scanBouquets();
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "user":
            $rUserID = intval(CoreUtilities::$request["user_id"]);
            // Check if this user falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!UIController::hasPermissions("user", $rUserID))) {
                echo json_encode(array("result" => false));
                exit;
            } elseif (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "edit_user"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or ($rPermissions["is_admin"])) {
                    if ($rPermissions["is_reseller"]) {
                        $rUserDetails = UIController::getUser($rUserID);
                        if ($rUserDetails) {
                            if ($rUserDetails["is_mag"]) {
                                $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '" . $rUserDetails["password"] . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete MAG</u>]');");
                            } elseif ($rUserDetails["is_e2"]) {
                                $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '" . $rUserDetails["password"] . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Enigma</u>]');");
                            } else {
                                $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '" . $rUserDetails["password"] . "', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Line</u>]');");
                            }
                        }
                    }
                    $ipTV_db_admin->query("DELETE FROM `lines` WHERE `id` = " . intval($rUserID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rUserID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `enigma2_devices` WHERE `user_id` = " . intval($rUserID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `mag_devices` WHERE `user_id` = " . intval($rUserID) . ";");
                    echo json_encode(array("result" => true));
                    exit;
                } else {
                    echo json_encode(array("result" => false));
                    exit;
                }
            } elseif ($rSub == "enable") {
                $ipTV_db_admin->query("UPDATE `lines` SET `enabled` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "disable") {
                $ipTV_db_admin->query("UPDATE `lines` SET `enabled` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "ban") {
                if (!$rPermissions["is_admin"]) {
                    echo json_encode(array("result" => false));
                    exit;
                }
                $ipTV_db_admin->query("UPDATE `lines` SET `admin_enabled` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "unban") {
                if (!$rPermissions["is_admin"]) {
                    echo json_encode(array("result" => false));
                    exit;
                }
                $ipTV_db_admin->query("UPDATE `lines` SET `admin_enabled` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
                //isp lock
            } elseif ($rSub == "resetispuser") {
                $ipTV_db_admin->query("UPDATE `lines` SET `isp_desc` = NULL WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "lockk") {
                $ipTV_db_admin->query("UPDATE `lines` SET `is_isplock` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "unlockk") {
                $ipTV_db_admin->query("UPDATE `lines` SET `is_isplock` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
                //isp lock
            } elseif ($rSub == "kill") {
                if (CoreUtilities::$settings['redis_handler']) {
                    foreach (ipTV_streaming::getRedisConnections($rUserID, null, null, true, false, false) as $rConnection) {
                        ipTV_streaming::closeConnection($rConnection);
                    }
                } else {
                    $ipTV_db_admin->query('SELECT * FROM `lines_live` WHERE `user_id` = ?;', $rUserID);
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        ipTV_streaming::closeConnection($rRow);
                    }
                }
                echo json_encode(array('result' => true));
                exit();
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "user_activity":
            $rPID = intval(CoreUtilities::$request["pid"]);
            // Check if the user running this PID falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!UIController::hasPermissions("pid", $rPID))) {
                echo json_encode(array("result" => false));
                exit;
            } elseif (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "connection_logs"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "kill") {
                $ipTV_db_admin->query("SELECT `server_id` FROM `lines_live` WHERE `pid` = " . intval($rPID) . " LIMIT 1;");
                if ($ipTV_db_admin->num_rows() == 1) {
                    UIController::SystemAPIRequest($ipTV_db_admin->get_row()["server_id"], array('action' => 'kill_pid', 'pid' => $rPID));
                    $ipTV_db_admin->query("DELETE FROM `lines_live` WHERE `pid` = " . $rPID . ";");
                    echo json_encode(array("result" => true));
                    exit;
                }
            }
            echo json_encode(array("result" => false));
            exit;
        case "process":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "process_monitor"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            UIController::SystemAPIRequest(CoreUtilities::$request['server'], array('action' => 'kill_pid', 'pid' => intval(CoreUtilities::$request['pid'])));
            echo json_encode(array('result' => true));
            exit();
        case "reg_user":
            $rUserID = intval(CoreUtilities::$request["user_id"]);
            // Check if this registered user falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!UIController::hasPermissions("reg_user", $rUserID))) {
                echo json_encode(array("result" => false));
                exit;
            } elseif (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "edit_reguser"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or ($rPermissions["is_admin"])) {
                    if ($rPermissions["is_reseller"]) {
                        $rUserDetails = UIController::getRegisteredUser($rUserID);
                        if ($rUserDetails) {
                            $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUserDetails["username"] . "', '', " . intval(time()) . ", '[<b>UserPanel</b> -> <u>Delete Subreseller</u>]');");
                        }
                        $rPrevOwner = UIController::getRegisteredUser($rUserDetails["owner_id"]);
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
            } elseif ($rSub == "enable") {
                $ipTV_db_admin->query("UPDATE `reg_users` SET `status` = 1 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "disable") {
                $ipTV_db_admin->query("UPDATE `reg_users` SET `status` = 0 WHERE `id` = " . intval($rUserID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "ticket":
            $rTicketID = intval(CoreUtilities::$request["ticket_id"]);
            // Check if this ticket falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!UIController::hasPermissions("ticket", $rTicketID))) {
                echo json_encode(array("result" => false));
                exit;
            } elseif (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "ticket"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `tickets` WHERE `id` = " . intval($rTicketID) . ";");
                $ipTV_db_admin->query("DELETE FROM `tickets_replies` WHERE `ticket_id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "close") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `status` = 0 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "reopen") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `status` = 1 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "unread") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `admin_read` = 0 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "read") {
                $ipTV_db_admin->query("UPDATE `tickets` SET `admin_read` = 1 WHERE `id` = " . intval($rTicketID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "mag":
            $rMagID = intval(CoreUtilities::$request["mag_id"]);
            // Check if this device falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!UIController::hasPermissions("mag", $rMagID))) {
                echo json_encode(array("result" => false));
                exit;
            } elseif (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "edit_mag"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $rMagDetails = UIController::getMag($rMagID);
                if (isset($rMagDetails["user_id"])) {
                    $ipTV_db_admin->query("DELETE FROM `lines` WHERE `id` = " . intval($rMagDetails["user_id"]) . ";");
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
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "manage_events"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rMagID = intval(CoreUtilities::$request["mag_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `mag_events` WHERE `id` = " . intval($rMagID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "epg":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_epg"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rEPGID = intval(CoreUtilities::$request["epg_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `epg` WHERE `id` = " . intval($rEPGID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "profile":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "tprofiles"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rProfileID = intval(CoreUtilities::$request["profile_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `transcoding_profiles` WHERE `profile_id` = " . intval($rProfileID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "series":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_series"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSeriesID = intval(CoreUtilities::$request["series_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `series` WHERE `id` = " . intval($rSeriesID) . ";");
                $ipTV_db_admin->query("SELECT `stream_id` FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesID) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `stream_id` = " . intval($rRow["stream_id"]) . ";");
                        $ipTV_db_admin->query("DELETE FROM `streams` WHERE `id` = " . intval($rRow["stream_id"]) . ";");
                        UIController::deleteMovieFile($rServerID, $rStreamID);
                    }
                    $ipTV_db_admin->query("DELETE FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesID) . ";");
                    UIController::scanBouquets();
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "folder":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "folder_watch"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rFolderID = intval(CoreUtilities::$request["folder_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `watch_folders` WHERE `id` = " . intval($rFolderID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "useragent":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "block_uas"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rUAID = intval(CoreUtilities::$request["ua_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `blocked_user_agents` WHERE `id` = " . intval($rUAID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "isp":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "block_isps"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rISPID = intval(CoreUtilities::$request["isp_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `isp_addon` WHERE `id` = " . intval($rISPID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "ip":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "block_ips"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = CoreUtilities::$request['sub'];

            if ($rSub == 'delete') {
                UIController::rdeleteBlockedIP(CoreUtilities::$request['ip']);
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();
        case "login_flood":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "add_login_flood"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rIPID = intval(CoreUtilities::$request["ip"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `login_flood` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "rtmp_ip":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "add_rtmp"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rIPID = intval(CoreUtilities::$request["ip"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `rtmp_ips` WHERE `id` = " . intval($rIPID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "subreseller_setup":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "subreseller"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rID = intval(CoreUtilities::$request["id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `subreseller_setup` WHERE `id` = " . intval($rID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "watch_output":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "folder_watch_output"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rID = intval(CoreUtilities::$request["result_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE `id` = " . intval($rID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "enigma":
            $rEnigmaID = intval(CoreUtilities::$request["enigma_id"]);
            // Check if this device falls under the reseller or subresellers.
            if (($rPermissions["is_reseller"]) && (!UIController::hasPermissions("e2", $rEnigmaID))) {
                echo json_encode(array("result" => false));
                exit;
            } elseif (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "edit_e2"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $rEnigmaDetails = UIController::getEnigma($rEnigmaID);
                if (isset($rEnigmaDetails["user_id"])) {
                    $ipTV_db_admin->query("DELETE FROM `lines` WHERE `id` = " . intval($rEnigmaDetails["user_id"]) . ";");
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
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rServerID = intval(CoreUtilities::$request["server_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                if ($rServers[CoreUtilities::$request["server_id"]]["can_delete"] == 1) {
                    $ipTV_db_admin->query("DELETE FROM `servers` WHERE `id` = " . intval($rServerID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `server_id` = " . intval($rServerID) . ";");
                    // drop user mysql
                    $ipTV_db_admin->query("DROP USER 'lb_" . CoreUtilities::$request["server_id"] . "'@'" . $rServers[CoreUtilities::$request["server_id"]]["server_ip"] . "';");
                    echo json_encode(array("result" => true));
                    exit;
                } else {
                    echo json_encode(array("result" => false));
                    exit;
                }
            } elseif ($rSub == "kill") {
                $ipTV_db_admin->query("SELECT `pid`, `server_id` FROM `lines_live` WHERE `server_id` = " . intval($rServerID) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        UIController::SystemAPIRequest($rRow["server_id"], array('action' => 'kill_pid', 'pid' => intval($rRow["pid"])));
                    }
                }
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "start") {
                $rStreamIDs = array();
                $ipTV_db_admin->query("SELECT `stream_id` FROM `streams_servers` WHERE `server_id` = " . intval($rServerID) . " AND `on_demand` = 0;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $rStreamIDs[] = intval($rRow["stream_id"]);
                    }
                }
                if (count($rStreamIDs) > 0) {
                    $rResult = UIController::APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array_values($rStreamIDs), "servers" => array(intval($rServerID))));
                }
                echo json_encode(array("result" => true));
                exit;
            } elseif ($rSub == "stop") {
                $rStreamIDs = array();
                $ipTV_db_admin->query("SELECT `stream_id` FROM `streams_servers` WHERE `server_id` = " . intval($rServerID) . " AND `on_demand` = 0;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $rStreamIDs[] = intval($rRow["stream_id"]);
                    }
                }
                if (count($rStreamIDs) > 0) {
                    $rResult = UIController::APIRequest(array("action" => "stream", "sub" => "stop", "stream_ids" => array_values($rStreamIDs), "servers" => array(intval($rServerID))));
                }
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "package":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_package"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rPackageID = intval(CoreUtilities::$request["package_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `packages` WHERE `id` = " . intval($rPackageID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } elseif (in_array($rSub, array("is_trial", "is_official", "can_gen_mag", "can_gen_e2", "only_mag", "only_e2"))) {
                $ipTV_db_admin->query("UPDATE `packages` SET `" . $rSub . "` = " . intval(CoreUtilities::$request["value"]) . " WHERE `id` = " . intval($rPackageID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "group":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_group"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rGroupID = intval(CoreUtilities::$request["group_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `member_groups` WHERE `group_id` = " . intval($rGroupID) . " AND `can_delete` = 1;");
                echo json_encode(array("result" => true));
                exit;
            } elseif (in_array($rSub, array("is_banned", "is_admin", "is_reseller"))) {
                $ipTV_db_admin->query("UPDATE `member_groups` SET `" . $rSub . "` = " . intval(CoreUtilities::$request["value"]) . " WHERE `group_id` = " . intval($rGroupID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "bouquet":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_bouquet"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rBouquetID = intval(CoreUtilities::$request["bouquet_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `bouquets` WHERE `id` = " . intval($rBouquetID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "category":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_cat"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rCategoryID = intval(CoreUtilities::$request["category_id"]);
            $rSub = CoreUtilities::$request["sub"];
            if ($rSub == "delete") {
                $ipTV_db_admin->query("DELETE FROM `stream_categories` WHERE `id` = " . intval($rCategoryID) . ";");
                echo json_encode(array("result" => true));
                exit;
            } else {
                echo json_encode(array("result" => false));
                exit;
            }
        case "get_package":
            $return = array();
            $rOverride = json_decode($rUserInfo["override_packages"], true);
            $ipTV_db_admin->query("SELECT `id`, `bouquets`, `official_credits` AS `cost_credits`, `official_duration`, `official_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = " . intval(CoreUtilities::$request["package_id"]) . ";");
            if ($ipTV_db_admin->num_rows() == 1) {
                $rData = $ipTV_db_admin->get_row();
                if ((isset($rOverride[$rData["id"]]["official_credits"])) && (strlen($rOverride[$rData["id"]]["official_credits"]) > 0)) {
                    $rData["cost_credits"] = $rOverride[$rData["id"]]["official_credits"];
                }
                $rData["exp_date"] = date('Y-m-d', strtotime('+' . intval($rData["official_duration"]) . ' ' . $rData["official_duration_in"]));
                if (isset(CoreUtilities::$request["user_id"])) {
                    if ($rUser = UIController::getUser(CoreUtilities::$request["user_id"])) {
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
                        $return[] = array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], true), "bouquet_series" => json_decode($rRow["bouquet_series"], true));
                    }
                }
                echo json_encode(array("result" => true, "bouquets" => $return, "data" => $rData));
            } else {
                echo json_encode(array("result" => false));
            }
            exit;
        case "get_package_trial":
            $return = array();
            $ipTV_db_admin->query("SELECT `bouquets`, `trial_credits` AS `cost_credits`, `trial_duration`, `trial_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = " . intval(CoreUtilities::$request["package_id"]) . ";");
            if ($ipTV_db_admin->num_rows() == 1) {
                $rData = $ipTV_db_admin->get_row();
                $rData["exp_date"] = date('Y-m-d', strtotime('+' . intval($rData["trial_duration"]) . ' ' . $rData["trial_duration_in"]));
                foreach (json_decode($rData["bouquets"], true) as $rBouquet) {
                    $ipTV_db_admin->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rBouquet) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $rRow = $ipTV_db_admin->get_row();
                        $return[] = array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], true), "bouquet_series" => json_decode($rRow["bouquet_series"], true));
                    }
                }
                echo json_encode(array("result" => true, "bouquets" => $return, "data" => $rData));
            } else {
                echo json_encode(array("result" => false));
            }
            exit;
        case "streams":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "streams"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rData = array();
            $rStreamIDs = json_decode(CoreUtilities::$request["stream_ids"], true);
            $rStreams = getStreams(null, false, $rStreamIDs);
            echo json_encode(array("result" => true, "data" => $rStreams));
            exit;
        case "chart_stats":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "index"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rStatistics = array("users" => array(), "conns" => array());
            $rPeriod = intval($rSettings["dashboard_stats_frequency"]) ?: 600;
            $rMax = UIController::roundUpToAny(time(), $rPeriod);
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
            if (!UIController::hasPermissions('adv', 'index')) {
                echo json_encode(array('result' => false));
                exit();
            }
            $rServers = CoreUtilities::getServers(true);
            $return = array('cpu' => 0, 'mem' => 0, 'io' => 0, 'fs' => 0, 'uptime' => '--', 'bytes_sent' => 0, 'bytes_received' => 0, 'open_connections' => 0, 'total_connections' => 0, 'online_users' => 0, 'total_users' => 0, 'total_streams' => 0, 'total_running_streams' => 0, 'offline_streams' => 0, 'requests_per_second' => 0, 'servers' => array());

            if (CoreUtilities::$settings['redis_handler']) {
                $return['total_users'] = CoreUtilities::$settings['total_users'];
            } else {
                $ipTV_db_admin->query('SELECT `activity_id` FROM `lines_live` WHERE `hls_end` = 0 GROUP BY `user_id`;');

                if ($ipTV_db_admin->num_rows() > 0) {
                    $return['total_users'] = $ipTV_db_admin->num_rows();
                }
            }

            if (isset(CoreUtilities::$request['server_id'])) {
                $rServerID = intval(CoreUtilities::$request['server_id']);
                $rWatchDog = json_decode($rServers[$rServerID]['watchdog_data'], true);

                if (is_array($rWatchDog)) {
                    $return['uptime'] = $rWatchDog['uptime'];
                    $return['mem'] = round($rWatchDog['total_mem_used_percent'], 0);
                    $return['cpu'] = round($rWatchDog['cpu'], 0);

                    if (isset($rWatchDog['iostat_info'])) {
                        $return['io'] = round($rWatchDog['iostat_info']['avg-cpu']['iowait'], 0);
                    }

                    if (isset($rWatchDog['total_disk_space'])) {
                        $return['fs'] = intval(($rWatchDog['total_disk_space'] - $rWatchDog['free_disk_space']) / $rWatchDog['total_disk_space'] * 100);
                    }

                    $return['bytes_received'] = intval($rWatchDog['bytes_received']);
                    $return['bytes_sent'] = intval($rWatchDog['bytes_sent']);
                }

                $return['requests_per_second'] = $rServers[$rServerID]['requests_per_second'];

                if (CoreUtilities::$settings['redis_handler']) {
                    $return['open_connections'] = $rServers[$rServerID]['connections'];
                    $return['online_users'] = $rServers[$rServerID]['users'];

                    foreach (array_keys($rServers) as $rSID) {
                        if ($rServers[$rSID]['server_online']) {
                            $return['total_connections'] += $rServers[$rSID]['connections'];
                        }
                    }
                } else {
                    $ipTV_db_admin->query('SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `server_id` = ? AND `hls_end` = 0;', $rServerID);

                    if ($ipTV_db_admin->num_rows() > 0) {
                        $return['open_connections'] = $ipTV_db_admin->get_row()['count'];
                    }

                    $ipTV_db_admin->query('SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `hls_end` = 0;');

                    if ($ipTV_db_admin->num_rows() > 0) {
                        $return['total_connections'] = $ipTV_db_admin->get_row()['count'];
                    }

                    $ipTV_db_admin->query('SELECT `activity_id` FROM `lines_live` WHERE `server_id` = ? AND `hls_end` = 0 GROUP BY `user_id`;', $rServerID);

                    if ($ipTV_db_admin->num_rows() > 0) {
                        $return['online_users'] = $ipTV_db_admin->num_rows();
                    }
                }

                $ipTV_db_admin->query('SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = ? AND `stream_status` <> 2 AND `type` = 1;', $rServerID);

                if ($ipTV_db_admin->num_rows() > 0) {
                    $return['total_streams'] = $ipTV_db_admin->get_row()['count'];
                }

                $ipTV_db_admin->query('SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = ? AND `pid` > 0 AND `type` = 1;', $rServerID);

                if ($ipTV_db_admin->num_rows() > 0) {
                    $return['total_running_streams'] = $ipTV_db_admin->get_row()['count'];
                }

                $ipTV_db_admin->query('SELECT COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `server_id` = ? AND `type` = 1 AND (`streams`.`direct_source` = 0 AND (`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0);', $rServerID);

                if ($ipTV_db_admin->num_rows() > 0) {
                    $return['offline_streams'] = $ipTV_db_admin->get_row()['count'];
                }

                $return['network_guaranteed_speed'] = $rServers[$rServerID]['network_guaranteed_speed'];
            } else {
                $rUptime = 0;

                if (!CoreUtilities::$settings['redis_handler']) {
                    $ipTV_db_admin->query('SELECT COUNT(*) AS `count` FROM `lines_live` WHERE `hls_end` = 0;');

                    if (0 < $ipTV_db_admin->num_rows()) {
                        $rTotalConnections = $ipTV_db_admin->get_row()['count'];
                    } else {
                        $rTotalConnections = 0;
                    }

                    $ipTV_db_admin->query('SELECT `activity_id` AS `count` FROM `lines_live` WHERE `hls_end` = 0 GROUP BY `user_id`;');

                    if (0 < $ipTV_db_admin->num_rows()) {
                        $rTotalUsers = $ipTV_db_admin->num_rows();
                    } else {
                        $rTotalUsers = 0;
                    }

                    $ipTV_db_admin->query('SELECT `user_id` FROM `lines_live` WHERE `hls_end` = 0 GROUP BY `user_id`;');
                    $return['online_users'] = $ipTV_db_admin->num_rows();
                    $return['open_connections'] = $rTotalConnections;
                }

                $rTotalStreams = $rOnlineStreams = $rOfflineStreams = $rOnlineUsers = $rOpenConnections = array();
                $ipTV_db_admin->query('SELECT `server_id`, COUNT(*) AS `count` FROM `lines_live` WHERE `hls_end` = 0 GROUP BY `server_id`;');

                foreach ($ipTV_db_admin->get_rows() as $rRow) {
                    $rOpenConnections[intval($rRow['server_id'])] = intval($rRow['count']);
                }
                $ipTV_db_admin->query('SELECT `server_id`, COUNT(DISTINCT(`user_id`)) AS `count` FROM `lines_live` GROUP BY `server_id`;');

                foreach ($ipTV_db_admin->get_rows() as $rRow) {
                    $rOnlineUsers[intval($rRow['server_id'])] = intval($rRow['count']);
                }
                $ipTV_db_admin->query('SELECT `server_id`, COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `stream_status` <> 2 AND `type` = 1 GROUP BY `server_id`;');

                foreach ($ipTV_db_admin->get_rows() as $rRow) {
                    $rTotalStreams[intval($rRow['server_id'])] = intval($rRow['count']);
                }
                $ipTV_db_admin->query('SELECT `server_id`, COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `type` = 1 AND (`streams`.`direct_source` = 0 AND (`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0) GROUP BY `server_id`;');

                foreach ($ipTV_db_admin->get_rows() as $rRow) {
                    $rOfflineStreams[intval($rRow['server_id'])] = intval($rRow['count']);
                }
                $ipTV_db_admin->query('SELECT `server_id`, COUNT(*) AS `count` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `pid` > 0 AND `type` = 1 GROUP BY `server_id`;');

                foreach ($ipTV_db_admin->get_rows() as $rRow) {
                    $rOnlineStreams[intval($rRow['server_id'])] = intval($rRow['count']);
                }

                foreach (array_keys($rServers) as $rServerID) {
                    if ($rServers[$rServerID]['server_online']) {
                        $rArray = array();

                        if (CoreUtilities::$settings['redis_handler']) {
                            $rArray['open_connections'] = $rServers[$rServerID]['connections'];
                            $return['open_connections'] += $rServers[$rServerID]['connections'];
                            $return['total_connections'] += $rServers[$rServerID]['connections'];
                            $rArray['online_users'] = $rServers[$rServerID]['users'];
                            $return['online_users'] += $rServers[$rServerID]['users'];
                            $return['total_users'] += $rServers[$rServerID]['users'];
                        } else {
                            $rArray['open_connections'] = $rOpenConnections[$rServerID] ?? 0;
                            $rArray['online_users'] = $rOnlineUsers[$rServerID] ?? 0;
                        }

                        $rArray['requests_per_second'] = $rServers[$rServerID]['requests_per_second'];
                        $rArray['total_streams'] = ($rTotalStreams[$rServerID] ?? 0);
                        $rArray['total_running_streams'] = ($rOnlineStreams[$rServerID] ?? 0);
                        $rArray['offline_streams'] = ($rOfflineStreams[$rServerID] ?? 0);
                        $rArray['network_guaranteed_speed'] = $rServers[$rServerID]['network_guaranteed_speed'];
                        $rWatchDog = json_decode($rServers[$rServerID]['watchdog_data'], true);

                        if (is_array($rWatchDog)) {
                            $rArray['uptime'] = $rWatchDog['uptime'];
                            $rArray['mem'] = round($rWatchDog['total_mem_used_percent'], 0);
                            $rArray['cpu'] = round($rWatchDog['cpu'], 0);

                            if (isset($rWatchDog['iostat_info'])) {
                                $rArray['io'] = round($rWatchDog['iostat_info']['avg-cpu']['iowait'], 0);
                            }

                            if (isset($rWatchDog['total_disk_space'])) {
                                $rArray['fs'] = intval(($rWatchDog['total_disk_space'] - $rWatchDog['free_disk_space']) / $rWatchDog['total_disk_space'] * 100);
                            }

                            $rArray['bytes_received'] = intval($rWatchDog['bytes_received']);
                            $rArray['bytes_sent'] = intval($rWatchDog['bytes_sent']);
                            $return['bytes_received'] += intval($rWatchDog['bytes_received']);
                            $return['bytes_sent'] += intval($rWatchDog['bytes_sent']);
                        }

                        $rArray['total_connections'] = $rTotalConnections;
                        $rArray['server_id'] = $rServerID;
                        $rArray['server_type'] = $rServers[$rServerID]['server_type'];
                        $return['servers'][] = $rArray;
                    }
                }

                foreach ($return['servers'] as $rServerArray) {
                    $return['total_streams'] += $rServerArray['total_streams'];
                    $return['total_running_streams'] += $rServerArray['total_running_streams'];
                    $return['offline_streams'] += $rServerArray['offline_streams'];
                }
                $return['online_users'] = CoreUtilities::$settings['total_users'];
            }

            echo json_encode($return, JSON_PARTIAL_OUTPUT_ON_ERROR);

            exit();

        case "reseller_dashboard":
            if ($rPermissions["is_admin"]) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("open_connections" => 0, "online_users" => 0, "active_accounts" => 0, "credits" => 0);
            $ipTV_db_admin->query("SELECT `activity_id` FROM `lines_live` AS `a` LEFT JOIN `lines` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (" . join(",", array_keys(UIController::getRegisteredUsers($rUserInfo["id"]))) . ");");
            $return["open_connections"] = $ipTV_db_admin->num_rows();
            $ipTV_db_admin->query("SELECT `activity_id` FROM `lines_live` AS `a` LEFT JOIN `lines` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (" . join(",", array_keys(UIController::getRegisteredUsers($rUserInfo["id"]))) . ") GROUP BY `a`.`user_id`;");
            $return["online_users"] = $ipTV_db_admin->num_rows();
            $ipTV_db_admin->query("SELECT `id` FROM `lines` WHERE `member_id` IN (" . join(",", array_keys(UIController::getRegisteredUsers($rUserInfo["id"]))) . ");");
            $return["active_accounts"] = $ipTV_db_admin->num_rows();
            $return["credits"] = $rUserInfo["credits"];
            echo json_encode($return);
            exit;
        case "review_selection":
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "edit_cchannel") && (!UIController::hasPermissions("adv", "create_channel"))))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("streams" => array(), "result" => true);
            if (isset(CoreUtilities::$request["data"])) {
                foreach (CoreUtilities::$request["data"] as $rStreamID) {
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
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "edit_bouquet") && (!UIController::hasPermissions("adv", "add_bouquet"))))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("streams" => array(), "vod" => array(), "series" => array(), "radios" => array(), "result" => true);
            // stream
            if (isset(CoreUtilities::$request["data"]["stream"])) {
                foreach (CoreUtilities::$request["data"]["stream"] as $rStreamID) {
                    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $return["streams"][] = $ipTV_db_admin->get_row();
                    }
                }
            }
            // vod
            if (isset(CoreUtilities::$request["data"]["vod"])) {
                foreach (CoreUtilities::$request["data"]["vod"] as $rStreamID) {
                    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $return["vod"][] = $ipTV_db_admin->get_row();
                    }
                }
            }
            // radios
            if (isset(CoreUtilities::$request["data"]["radios"])) {
                foreach (CoreUtilities::$request["data"]["radios"] as $rStreamID) {
                    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = " . intval($rStreamID) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $return["radios"][] = $ipTV_db_admin->get_row();
                    }
                }
            }
            // series
            if (isset(CoreUtilities::$request["data"]["series"])) {
                foreach (CoreUtilities::$request["data"]["series"] as $rSeriesID) {
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
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "edit_e2") && (!UIController::hasPermissions("adv", "add_e2")) && (!UIController::hasPermissions("adv", "add_mag")) && (!UIController::hasPermissions("adv", "edit_mag"))))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("total_count" => 0, "items" => array(), "result" => true);
            if (isset(CoreUtilities::$request["search"])) {
                if (isset(CoreUtilities::$request["page"])) {
                    $rPage = intval(CoreUtilities::$request["page"]);
                } else {
                    $rPage = 1;
                }
                $ipTV_db_admin->query("SELECT COUNT(`id`) AS `id` FROM `lines` WHERE `username` LIKE '%" . CoreUtilities::$request["search"] . "%' AND `is_e2` = 0 AND `is_mag` = 0;");
                $return["total_count"] = $ipTV_db_admin->get_row()["id"];
                $ipTV_db_admin->query("SELECT `id`, `username` FROM `lines` WHERE `username` LIKE '%" . CoreUtilities::$request["search"] . "%' AND `is_e2` = 0 AND `is_mag` = 0 ORDER BY `username` ASC LIMIT " . (($rPage - 1) * 100) . ", 100;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $return["items"][] = array("id" => $rRow["id"], "text" => $rRow["username"]);
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "streamlist":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "manage_mag"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $return = array("total_count" => 0, "items" => array(), "result" => true);
            if (isset(CoreUtilities::$request["search"])) {
                if (isset(CoreUtilities::$request["page"])) {
                    $rPage = intval(CoreUtilities::$request["page"]);
                } else {
                    $rPage = 1;
                }
                $ipTV_db_admin->query("SELECT COUNT(`id`) AS `id` FROM `streams` WHERE `stream_display_name` LIKE '%" . CoreUtilities::$request["search"] . "%';");
                $return["total_count"] = $ipTV_db_admin->get_row()["id"];
                $ipTV_db_admin->query("SELECT `id`, `stream_display_name` FROM `streams` WHERE `stream_display_name` LIKE '%" . CoreUtilities::$request["search"] . "%' ORDER BY `stream_display_name` ASC LIMIT " . (($rPage - 1) * 100) . ", 100;");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $rRow) {
                        $return["items"][] = array("id" => $rRow["id"], "text" => $rRow["stream_display_name"]);
                    }
                }
            }
            echo json_encode($return);
            exit;
        case "force_epg":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "epg"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $ipTV_db_admin->query("TRUNCATE TABLE `epg_data`;");
            shell_exec(PHP_BIN . ' ' . CRON_PATH . 'epg.php > /dev/null 2>/dev/null &');
            echo json_encode(array('result' => true));
            exit();
        case "tmdb_search":
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "add_series")) && (!UIController::hasPermissions("adv", "edit_series")) && (!UIController::hasPermissions("adv", "add_movie")) && (!UIController::hasPermissions("adv", "edit_movie")) && (!UIController::hasPermissions("adv", "add_episode")) && (!UIController::hasPermissions("adv", "edit_episode")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            include INCLUDES_PATH . 'libs/tmdb.php';
            if (strlen($rSettings["tmdb_language"]) > 0) {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rSettings["tmdb_language"]);
            } else {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
            }
            $rTerm = CoreUtilities::$request["term"];
            if ($rSettings["release_parser"] == "php") {
                include INCLUDES_PATH . "libs/tmdb_release.php";
                $rRelease = new Release($rTerm);
                $rTerm = $rRelease->getTitle();
            } else {
                $rRelease = UIController::tmdbParseRelease($rTerm);
                $rTerm = $rRelease["title"];
            }
            $rJSON = array();
            if (CoreUtilities::$request["type"] == "movie") {
                $rResults = $rTMDB->searchMovie($rTerm);
                foreach ($rResults as $rResult) {
                    $rJSON[] = json_decode($rResult->getJSON(), true);
                }
            } elseif (CoreUtilities::$request["type"] == "series") {
                $rResults = $rTMDB->searchTVShow($rTerm);
                foreach ($rResults as $rResult) {
                    $rJSON[] = json_decode($rResult->getJSON(), true);
                }
            } else {
                $rJSON = json_decode($rTMDB->getSeason($rTerm, intval(CoreUtilities::$request["season"]))->getJSON(), true);
            }
            if (count($rJSON) > 0) {
                echo json_encode(array("result" => true, "data" => $rJSON));
                exit;
            }
            echo json_encode(array("result" => false));
            exit;
        case "tmdb":
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "add_series")) && (!UIController::hasPermissions("adv", "edit_series")) && (!UIController::hasPermissions("adv", "add_movie")) && (!UIController::hasPermissions("adv", "edit_movie")) && (!UIController::hasPermissions("adv", "add_episode")) && (!UIController::hasPermissions("adv", "edit_episode")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            include INCLUDES_PATH . 'libs/tmdb.php';
            if (strlen($rSettings["tmdb_language"]) > 0) {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rSettings["tmdb_language"]);
            } else {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
            }
            $rID = CoreUtilities::$request["id"];
            if (CoreUtilities::$request["type"] == "movie") {
                $rMovie = $rTMDB->getMovie($rID);
                $rResult = json_decode($rMovie->getJSON(), true);
                $rResult["trailer"] = $rMovie->getTrailer();
            } elseif (CoreUtilities::$request["type"] == "series") {
                $rSeries = $rTMDB->getTVShow($rID);
                $rResult = json_decode($rSeries->getJSON(), true);
                $rResult["trailer"] = UIController::getSeriesTrailer($rID);
            }
            if ($rResult) {
                echo json_encode(array("result" => true, "data" => $rResult));
                exit;
            }
            echo json_encode(array("result" => false));
            exit;
        case "listdir":
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "add_episode")) && (!UIController::hasPermissions("adv", "edit_episode")) && (!UIController::hasPermissions("adv", "add_movie")) && (!UIController::hasPermissions("adv", "edit_movie")) && (!UIController::hasPermissions("adv", "create_channel")) && (!UIController::hasPermissions("adv", "edit_cchannel")) && (!UIController::hasPermissions("adv", "folder_watch_add")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            if (CoreUtilities::$request["filter"] == "video") {
                $rFilter = array("mp4", "mkv", "avi", "mpg", "flv");
            } elseif (CoreUtilities::$request["filter"] == "subs") {
                $rFilter = array("srt", "sub", "sbv");
            } else {
                $rFilter = null;
            }
            if ((isset(CoreUtilities::$request["server"])) && (isset(CoreUtilities::$request["dir"]))) {
                echo json_encode(array("result" => true, "data" => UIController::listDir(intval(CoreUtilities::$request["server"]), CoreUtilities::$request["dir"], $rFilter)));
                exit;
            }
            echo json_encode(array("result" => false));
            exit;
        case "fingerprint":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "fingerprint"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rData = json_decode(CoreUtilities::$request["data"], true);
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
                $ipTV_db_admin->query("SELECT `lines_live`.`activity_id`, `lines_live`.`user_id`, `lines_live`.`server_id`, `lines`.`username` FROM `lines_live` LEFT JOIN `lines` ON `lines`.`id` = `lines_live`.`user_id` WHERE `lines_live`.`container` = 'ts' AND `stream_id` = " . intval($rData["id"]) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    set_time_limit(360);
                    ini_set('max_execution_time', 360);
                    ini_set('default_socket_timeout', 15);
                    while ($row = $ipTV_db_admin->get_row()) {
                        if (in_array($row["server_id"], $rActiveServers)) {
                            $rArray = array("font_size" => $rData["font_size"], "font_color" => $rData["font_color"], "xy_offset" => $rData["xy_offset"], "message" => "", "activity_id" => $row["activity_id"]);
                            if ($rData["type"] == 1) {
                                $rArray["message"] = "#" . $row["activity_id"];
                            } elseif ($rData["type"] == 2) {
                                $rArray["message"] = $row["username"];
                            } elseif ($rData["type"] == 3) {
                                $rArray["message"] = $rData["message"];
                            }
                            $rArray["action"] = "signal_send";
                            $rSuccess = UIController::SystemAPIRequest(intval($row["server_id"]), $rArray);
                        }
                    }
                    echo json_encode(array("result" => true));
                    exit;
                }
            }
            echo json_encode(array("result" => false));
            exit;
        case "restart_services":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rServerID = intval(CoreUtilities::$request["server_id"]);
            if (isset($rServers[$rServerID])) {
                $ipTV_db_admin->query("INSERT INTO `signals`(`server_id`, `custom_data`, `time`) VALUES('" . $rServerID . "', '" . json_encode(array('action' => 'restart_services')) . "', '" . time() . "');");
                echo json_encode(array('result' => true));
            }
            echo json_encode(array('result' => false));
            exit;
        case "reboot_server":
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "edit_server"))) {
                echo json_encode(array("result" => false));
                exit;
            }
            $rServerID = intval(CoreUtilities::$request["server_id"]);
            if (isset($rServers[$rServerID])) {
                $ipTV_db_admin->query("INSERT INTO `signals`(`server_id`, `custom_data`, `time`) VALUES('" . $rServerID . "', '" . json_encode(array('action' => 'reboot')) . "', '" . time() . "');");
                echo json_encode(array('result' => true));
            }
            echo json_encode(array('result' => false));
            exit;
        case "map_stream":
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "add_stream")) && (!UIController::hasPermissions("adv", "edit_stream")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            set_time_limit(300);
            ini_set('max_execution_time', 300);
            ini_set('default_socket_timeout', 300);
            echo shell_exec("/home/xc_vm/bin/ffprobe -v quiet -probesize 4000000 -print_format json -show_format -show_streams \"" . CoreUtilities::$request["stream"] . "\"");
            exit;
        case "clear_logs":
            if ((!$rPermissions["is_admin"]) or ((!UIController::hasPermissions("adv", "reg_userlog")) && (!UIController::hasPermissions("adv", "client_request_log")) && (!UIController::hasPermissions("adv", "connection_logs")) && (!UIController::hasPermissions("adv", "stream_errors")) && (!UIController::hasPermissions("adv", "panel_errors")) && (!UIController::hasPermissions("adv", "credits_log")) && (!UIController::hasPermissions("adv", "folder_watch_settings")))) {
                echo json_encode(array("result" => false));
                exit;
            }
            if (strlen(CoreUtilities::$request["from"]) == 0) {
                $rStartTime = null;
            } elseif (!$rStartTime = strtotime(CoreUtilities::$request["from"] . " 00:00:00")) {
                echo json_encode(array("result" => false));
                exit;
            }
            if (strlen(CoreUtilities::$request["to"]) == 0) {
                $rEndTime = null;
            } elseif (!$rEndTime = strtotime(CoreUtilities::$request["to"] . " 23:59:59")) {
                echo json_encode(array("result" => false));
                exit;
            }
            if (in_array(CoreUtilities::$request["type"], array("client_logs", "stream_logs", "user_activity", "credits_log", "reg_userlog", "panel_logs", "mysql_syslog"))) {
                if (CoreUtilities::$request["type"] == "user_activity") {
                    $rColumn = "date_start";
                } else {
                    $rColumn = "date";
                }
                if (($rStartTime) && ($rEndTime)) {
                    $ipTV_db_admin->query("DELETE FROM `" . CoreUtilities::$request["type"] . "` WHERE `" . $rColumn . "` >= " . intval($rStartTime) . " AND `" . $rColumn . "` <= " . intval($rEndTime) . ";");
                } elseif ($rStartTime) {
                    $ipTV_db_admin->query("DELETE FROM `" . CoreUtilities::$request["type"] . "` WHERE `" . $rColumn . "` >= " . intval($rStartTime) . ";");
                } elseif ($rEndTime) {
                    $ipTV_db_admin->query("DELETE FROM `" . CoreUtilities::$request["type"] . "` WHERE `" . $rColumn . "` <= " . intval($rEndTime) . ";");
                } else {
                    $ipTV_db_admin->query("DELETE FROM `" . CoreUtilities::$request["type"] . "`;");
                }
            } elseif (CoreUtilities::$request["type"] == "watch_output") {
                if (($rStartTime) && ($rEndTime)) {
                    $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= " . intval($rStartTime) . " AND UNIX_TIMESTAMP(`dateadded`) <= " . intval($rEndTime) . ";");
                } elseif ($rStartTime) {
                    $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= " . intval($rStartTime) . ";");
                } elseif ($rEndTime) {
                    $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) <= " . intval($rEndTime) . ";");
                } else {
                    $ipTV_db_admin->query("DELETE FROM `watch_output`;");
                }
            }
            echo json_encode(array("result" => true));
            exit;
        case 'backup':
            if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "database"))) {
                echo json_encode(array('result' => false));
                exit();
            }
            $rSub = CoreUtilities::$request['sub'];

            if ($rSub == 'delete') {
                $rBackup = pathinfo(CoreUtilities::$request['filename'])['filename'];

                if (file_exists(MAIN_DIR . 'backups/' . $rBackup . '.sql')) {
                    unlink(MAIN_DIR . 'backups/' . $rBackup . '.sql');
                }
                echo json_encode(array('result' => true));

                exit();
            }

            if ($rSub == 'restore') {
                $rBackup = pathinfo(CoreUtilities::$request["filename"])["filename"];
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
    if ((!$rPermissions["is_admin"]) OR (!UIController::hasPermissions("adv", "manage_events"))) { echo json_encode(Array("result" => false)); exit; }
    $rData = json_decode(CoreUtilities::$request["data"], true);
    $rMag = UIController::getMag($rData["id"]);
    if ($rMag) {
        if ($rData["type"] == "send_msg") {
            $rData["need_confirm"] = 1;
        } else if ($rData["type"] == "play_channel") {
            $rData["need_confirm"] = 0;
            $rData["reboot_portal"] = 0;
            $rData["message"] = intval($rData["channel"]);
        } else if ($rData["type"] == "reset_stb_lock") {
            UIController::resetSTB($rData["id"]);
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
            if (($rPermissions["is_admin"]) && (UIController::hasPermissions("adv", "manage_events")) or (($rPermissions["is_reseller"]) && ($rSettings["reseller_mag_events"]))) {
                $rData = json_decode(CoreUtilities::$request["data"], true);
                $rMag = UIController::getMag($rData["id"]);
                if ($rMag) {
                    if ($rData["type"] == "send_msg") {
                        $rData["need_confirm"] = 1;
                    } elseif ($rData["type"] == "play_channel") {
                        $rData["need_confirm"] = 0;
                        $rData["reboot_portal"] = 0;
                        $rData["message"] = intval($rData["channel"]);
                    } elseif ($rData["type"] == "reset_stb_lock") {
                        UIController::resetSTB($rData["id"]);
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
            if (UIController::hasPermissions('adv', 'backups')) {
                CoreUtilities::setSettings(["enable_cache" => 1]);

                shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php');

                $rCache = intval(trim(shell_exec('pgrep -U xc_vm | xargs ps -f -p | grep cache_handler | grep -v grep | grep -v pgrep | wc -l')));
                if ($rCache == 0) {
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'cache_handler.php > /dev/null 2>/dev/null &');
                }
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();

        case "disable_cache":
            if (UIController::hasPermissions('adv', 'backups')) {
                CoreUtilities::setSettings(["enable_cache" => 0]);

                shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache.php');
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();
        case 'regenerate_cache':
            if (UIController::hasPermissions('adv', 'backups')) {
                shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "force"');
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();

        case 'disable_handler':
            if (UIController::hasPermissions('adv', 'backups')) {
                CoreUtilities::setSettings(["redis_handler" => 0]);

                if (file_exists(CACHE_TMP_PATH . 'settings')) {
                    unlink(CACHE_TMP_PATH . 'settings');
                }

                exec('pgrep -u xc_vm redis-server', $rRedis);

                if (0 < count($rRedis) && is_numeric($rRedis[0])) {
                    $rPID = intval($rRedis[0]);
                    shell_exec('kill -9 ' . $rPID);
                }

                exec("pgrep -U xc_vm | xargs ps | grep signals | awk '{print \$1}'", $rPID);

                if (0 < count($rPID) && is_numeric($rPID[0])) {
                    $rPID = intval($rPID[0]);
                    shell_exec('kill -9 ' . $rPID);
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'signals.php > /dev/null 2>/dev/null &');
                }

                exec("pgrep -U xc_vm | xargs ps | grep watchdog | awk '{print \$1}'", $rPID);

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
            if (UIController::hasPermissions('adv', 'backups')) {
                CoreUtilities::setSettings(["redis_handler" => 1]);

                if (file_exists(CACHE_TMP_PATH . 'settings')) {
                    unlink(CACHE_TMP_PATH . 'settings');
                }

                exec('pgrep -u xc_vm redis-server', $rRedis);

                if (count($rRedis) < 0 && !is_numeric($rRedis[0])) {
                    $rPID = intval($rRedis[0]);
                    shell_exec('kill -9 ' . $rPID);
                }

                shell_exec(BIN_PATH . 'redis/redis-server ' . BIN_PATH . 'redis/redis.conf > /dev/null 2>/dev/null &');
                sleep(1);
                exec("pgrep -U xc_vm | xargs ps | grep signals | awk '{print \$1}'", $rPID);

                if (0 < count($rPID) && is_numeric($rPID[0])) {
                    $rPID = intval($rPID[0]);
                    shell_exec('kill -9 ' . $rPID);
                    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'signals.php > /dev/null 2>/dev/null &');
                }

                exec("pgrep -U xc_vm | xargs ps | grep watchdog | awk '{print \$1}'", $rPID);

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
            if (UIController::hasPermissions('adv', 'backups')) {
                CoreUtilities::$redis->flushAll();
                echo json_encode(array('result' => true));
                exit();
            }
            echo json_encode(array('result' => false));
            exit();
    }
}

echo json_encode(array("result" => false));
