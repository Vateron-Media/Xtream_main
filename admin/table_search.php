<?php
include "functions.php";
if (!isset($_SESSION['hash'])) {
    exit;
}

set_time_limit($rSQLTimeout);
ini_set('mysql.connect_timeout', $rSQLTimeout);
ini_set('max_execution_time', $rSQLTimeout);
ini_set('default_socket_timeout', $rSQLTimeout);

$rStatusArray = array(0 => "<button type='button' class='btn btn-outline-warning btn-rounded btn-xs waves-effect waves-light'>STOPPED</button>", 1 => "RUNNING", 2 => "<button type='button' class='btn btn-outline-primary btn-rounded btn-xs waves-effect waves-light'>STARTING</button>", 3 => "<button type='button' class='btn btn-outline-danger btn-rounded btn-xs waves-effect waves-light'><i class='mdi mdi-checkbox-blank-circle'></i> DOWN</button>", 4 => "<button type='button' class='btn btn-outline-pink btn-rounded btn-xs waves-effect waves-light'>ON DEMAND</button>", 5 => "<button type='button' class='btn btn-outline-purple btn-rounded btn-xs waves-effect waves-light'>DIRECT</button>", 6 => "<button type='button' class='btn btn-outline-warning btn-rounded btn-xs waves-effect waves-light'>CREATING...</button>");
$rVODStatusArray = array(0 => "<i class='text-dark mdi mdi-checkbox-blank-circle-outline'></i>", 1 => "<i class='text-success mdi mdi-check-circle'></i>", 2 => "<i class='text-warning mdi mdi-checkbox-blank-circle'></i>", 3 => "<i class='text-primary mdi mdi-web'></i>", 4 => "<i class='text-danger mdi mdi-triangle'></i>");
$rWatchStatusArray = array(1 => "<button type='button' class='btn btn-outline-success btn-rounded btn-xs waves-effect waves-light'>ADDED</button>", 2 => "<button type='button' class='btn btn-outline-danger btn-rounded btn-xs waves-effect waves-light'>SQL FAILED</button>", 3 => "<button type='button' class='btn btn-outline-danger btn-rounded btn-xs waves-effect waves-light'>NO CATEGORY</button>", 4 => "<button type='button' class='btn btn-outline-danger btn-rounded btn-xs waves-effect waves-light'>NO TMDb MATCH</button>", 5 => "<button type='button' class='btn btn-outline-danger btn-rounded btn-xs waves-effect waves-light'>INVALID FILE</button>");

$rType = $_GET["id"];
$rStart = intval($_GET["start"]);
$rLimit = intval($_GET["length"]);

if (($rLimit > 1000) or ($rLimit == -1) or ($rLimit == 0)) {
    $rLimit = 1000;
}

if ($rType == "users") {
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "users")) && (!hasPermissions("adv", "mass_edit_users"))) {
        exit;
    }
    $rAvailableMembers = array_keys(getRegisteredUsers($rUserInfo["id"]));
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`users`.`id`", "`users`.`username`", "`users`.`password`", "`reg_users`.`username`", "`users`.`enabled`", "`active_connections`", "`users`.`is_trial`", "`users`.`exp_date`", "`users`.`max_connections`", "`users`.`max_connections`", "`users`.`isp_desc`", "`lines_live`.`user_ip`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (isset($_GET["showall"])) {
        if ($rPermissions["is_reseller"]) {
            $rWhere[] = "`users`.`member_id` IN (" . join(",", $rAvailableMembers) . ")";
        }
    } else {
        if ($rPermissions["is_admin"]) {
            $rWhere[] = "`users`.`is_mag` = 0 AND `users`.`is_e2` = 0";
        } else {
            $rWhere[] = "`users`.`is_mag` = 0 AND `users`.`is_e2` = 0 AND `users`.`member_id` IN (" . join(",", $rAvailableMembers) . ")";
        }
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`users`.`username` LIKE '%{$rSearch}%' OR `users`.`password` LIKE '%{$rSearch}%' OR `reg_users`.`username` LIKE '%{$rSearch}%' OR from_unixtime(`exp_date`) LIKE '%{$rSearch}%' OR `users`.`max_connections` LIKE '%{$rSearch}%' OR `users`.`reseller_notes` LIKE '%{$rSearch}%' OR `users`.`admin_notes` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "(`users`.`admin_enabled` = 1 AND `users`.`enabled` = 1 AND (`users`.`exp_date` IS NULL OR `users`.`exp_date` > UNIX_TIMESTAMP()))";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "`users`.`enabled` = 0";
        } else if ($_GET["filter"] == 3) {
            $rWhere[] = "`users`.`admin_enabled` = 0";
        } else if ($_GET["filter"] == 4) {
            $rWhere[] = "(`users`.`exp_date` IS NOT NULL AND `users`.`exp_date` <= UNIX_TIMESTAMP())";
        } else if ($_GET["filter"] == 5) {
            $rWhere[] = "`users`.`is_trial` = 1";
        } else if ($_GET["filter"] == 6) {
            $rWhere[] = "`users`.`is_mag` = 1";
        } else if ($_GET["filter"] == 7) {
            $rWhere[] = "`users`.`is_e2` = 1";
        }
    }
    if (strlen($_GET["reseller"]) > 0) {
        $rWhere[] = "`users`.`member_id` = " . intval($_GET["reseller"]);
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(`users`.`id`) AS `count` FROM `users` LEFT JOIN `reg_users` ON `reg_users`.`id` = `users`.`member_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `users`.`id`, `users`.`member_id`, `users`.`username`, `users`.`password`, `users`.`exp_date`, `users`.`admin_enabled`, `users`.`enabled`, `users`.`isp_desc`, `users`.`is_isplock`, `users`.`admin_notes`, `users`.`reseller_notes`, `users`.`max_connections`,  `users`.`is_trial`, `reg_users`.`username` AS `owner_name`, (SELECT count(*) FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id`) AS `active_connections`, (SELECT user_ip FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id` LIMIT 1) AS `user_ip`, (SELECT MAX(`date_start`) FROM `user_activity` WHERE `users`.`id` = `user_activity`.`user_id`) AS `last_active` FROM `users` LEFT JOIN `reg_users` ON `reg_users`.`id` = `users`.`member_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                if (!$rRow["admin_enabled"]) {
                    $rStatus = '<span class="mdi mdi-close mdi-24px text-danger role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Banned"></span>';
                } else {
                    if (!$rRow["enabled"]) {
                        $rStatus = '<span class="mdi mdi-close mdi-24px text-secondary" role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Disabled"></span>';
                    } else if (($rRow["exp_date"]) && ($rRow["exp_date"] < time())) {
                        $rStatus = '<span class="mdi mdi-close mdi-24px text-warning role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Expired"></span>';
                    } else {
                        $rStatus = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Active"></span>';
                    }
                }
                if ($rRow["active_connections"] > 0) {
                    $rActive = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Online"></span>';
                } else {
                    $rActive = '<span class="mdi mdi-close mdi-24px text-danger role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Offline"></span>';
                }
                if ($rRow["is_trial"]) {
                    $rTrial = '<span class="mdi mdi-close mdi-24px text-warning role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Trial"></span>';
                } else {
                    $rTrial = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Official"></span>';
                }
                if ($rRow["exp_date"]) {
                    if ($rRow["exp_date"] < time()) {
                        $rExpDate = "<mark span class=\"expired\">" . date("Y-m-d H:i", $rRow["exp_date"]) . "</mark>";
                    } else {
                        $rExpDate = date("Y-m-d H:i", $rRow["exp_date"]);
                    }
                } else {
                    $rExpDate = "Unlimited";
                }

                if ($rRow["max_connections"] == 0) {
                    $rRow["max_connections"] = "&infin;";
                } else {
                    $max_connections = $rRow["max_connections"];
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["reseller_client_connection_logs"])) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "live_connections")))) {
                    $rActiveConnections = "<a href=\"./live_connections.php?user_id=" . $rRow["id"] . "\"> " . $rRow["active_connections"] . " / " . $max_connections . "</a>";
                } else {
                    $rActiveConnections = "" . $rRow["active_connections"] . " / " . $max_connections . "</a>";
                }
                $rButtons = '<div class="btn-group">';
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_reset_isplock"]))) {
                    //if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user"))) {
                    //if (($rPermissions["is_reseller"]) OR (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {	
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Reset isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'resetispuser\');"><i class="mdi mdi-lock-reset"></i></button>
					';
                }
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_can_isplock"]))) {
                    //if (($rPermissions["is_reseller"]) OR (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {	
                    if ($rRow["is_isplock"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Unlock isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'unlockk\');"><i class="mdi mdi-lock"></i></button>';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Lock isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'lockk\');"><i class="mdi mdi-lock-outline"></i></button>';
                    }
                }
                if (((strlen($rRow["admin_notes"]) > 0) && ($rPermissions["is_admin"])) or (strlen($rRow["reseller_notes"]) > 0)) {
                    $rNotes = "";
                    if ($rPermissions["is_admin"]) {
                        if (strlen($rRow["admin_notes"]) > 0) {
                            $rNotes .= $rRow["admin_notes"];
                        }
                    }
                    if (strlen($rRow["reseller_notes"]) > 0) {
                        if (strlen($rNotes) <> 0) {
                            $rNotes .= "\n";
                        }
                        $rNotes .= $rRow["reseller_notes"];
                    }
                    $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rNotes . '"><i class="mdi mdi-note"></i></button>';
                } else {
                    $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                }
                if ($rPermissions["is_admin"]) {
                    if (hasPermissions("adv", "edit_user")) {
                        $rButtons .= '<a href="./user.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
						';
                    }
                } else {
                    //selecao de bouquets inicio
                    if ((($rPermissions["is_reseller"]) && ($rPermissions["reseller_can_select_bouquets"])) or ($rPermissions["is_admin"])) {
                        $rButtons .= '<a href="./user_reseller_edit.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit User Bouquets" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-format-line-spacing"></i></button></a>';
                    }
                    //selecao de bouquets fim
                    $rButtons .= '<a href="./user_reseller.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["allow_download"])) or ($rPermissions["is_admin"])) {
                    $rButtons .= '<button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Download Playlist" class="btn btn-light waves-effect waves-light btn-xs" onClick="download(\'' . $rRow["username"] . '\', \'' . $rRow["password"] . '\');"><i class="mdi mdi-arrow-collapse-down"></i></button>';
                }
                if (($rPermissions["is_reseller"]) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Kill Connections" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'kill\');"><i class="fas fa-hammer"></i></button>
					';
                }
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user"))) {
                    if ($rRow["admin_enabled"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Ban" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'ban\');"><i class="mdi mdi-minus-circle-outline"></i></button>';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Unban" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'unban\');"><i class="mdi mdi-minus-circle"></i></button>';
                    }
                }
                if (($rPermissions["is_reseller"]) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {
                    if ($rRow["enabled"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Disable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'disable\');"><i class="mdi mdi-checkbox-blank-circle-outline"></i></button>
						';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'enable\');"><i class="mdi mdi-checkbox-blank-circle"></i></button>
						';
                    }
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                }
                $rButtons .= '</div>';
                if ($rRow["last_active"]) {
                    $rLastActive = date("Y-m-d H:i", $rRow["last_active"]);
                } else {
                    $rLastActive = "Never";
                }
                $today = time();
                $leftdaynumber = (strtotime(date("Y-m-d H:i", $rRow["exp_date"])) - $today) / (60 * 60 * 24);
                $leftHourNumber = ($rRow["exp_date"] - $today) / (60 * 60);
                $leftMinNumber = ($rRow["exp_date"] - $today) / (60);
                if ($leftdaynumber > 0 && $leftdaynumber <= 1) {
                    $rLeftDate = "1 Day";
                } else if ($leftdaynumber > 1) {
                    $rLeftDate = round($leftdaynumber) . " Days";
                } else if ($leftHourNumber > 0 && $leftHourNumber <= 1) {
                    $rLeftDate = round($leftMinNumber) . " Minutes";
                } else if (round($leftHourNumber) == 1) {
                    $rLeftDate = "1 Hour";
                } else if ($leftHourNumber > 1) {
                    $rLeftDate = round($leftHourNumber) . " Hours";
                } else {
                    $rLeftDate = "<center>-</center>";
                }
                $query = "SELECT lines_live.date_start, lines_live.geoip_country_code, lines_live.user_ip, lines_live.stream_id, lines_live.user_id, streams.id, streams.stream_display_name FROM lines_live LEFT JOIN streams ON lines_live.stream_id = streams.id WHERE user_id = " . $rRow["id"];
                $ipTV_db_admin->query($query);
                $row2 = $ipTV_db_admin->get_row();
                if (!empty($row2['stream_display_name'])) {
                    $rTime = intval(time()) - intval($row2["date_start"]);
                    $rStream_name = "<span style='color: #20a009;'</span>" . $row2['stream_display_name'] . "<br><span style='color: #737373;'>" . "Uptime </span>" . "<span style='color: #737373;'>" . sprintf('%02d:%02d:%02d', ($rTime / 3600), ($rTime / 60 % 60), $rTime % 60) . "<br><a target='_blank' href='https://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $row2["user_ip"] . "'</span>" . $row2["user_ip"] . " " . $row2['geoip_country_code'] . "<br><span style='color: #737373;'></span>" . $rRow["isp_desc"];
                } else {
                    $rStream_name = "-";
                }

                $rReturn["data"][] = array($rRow["id"], $rRow["username"], $rRow["password"], $rRow["owner_name"], $rStatus, $rActive, $rTrial, $rExpDate, $rLeftDate, $rActiveConnections, $rLastActive, "<font size=1>" . $rStream_name, $rButtons);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "mags") {
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "manage_mag"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`users`.`id`", "`users`.`username`", "`mag_devices`.`mac`", "`reg_users`.`username`", "`users`.`enabled`", "`active_connections`", "`users`.`is_trial`", "`users`.`exp_date`", "`users`.`isp_desc`", "`lines_live`.`user_ip`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if ($rPermissions["is_reseller"]) {
        $rWhere[] = "`users`.`member_id` IN (" . join(",", array_keys(getRegisteredUsers($rUserInfo["id"]))) . ")";
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`users`.`username` LIKE '%{$rSearch}%' OR from_base64(`mag_devices`.`mac`) LIKE '%" . strtoupper($rSearch) . "%' OR `reg_users`.`username` LIKE '%{$rSearch}%' OR from_unixtime(`exp_date`) LIKE '%{$rSearch}%' OR `users`.`reseller_notes` LIKE '%{$rSearch}%' OR `users`.`admin_notes` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "(`users`.`admin_enabled` = 1 AND `users`.`enabled` = 1 AND (`users`.`exp_date` IS NULL OR `users`.`exp_date` > UNIX_TIMESTAMP()))";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "`users`.`enabled` = 0";
        } else if ($_GET["filter"] == 3) {
            $rWhere[] = "`users`.`admin_enabled` = 0";
        } else if ($_GET["filter"] == 4) {
            $rWhere[] = "(`users`.`exp_date` IS NOT NULL AND `users`.`exp_date` <= UNIX_TIMESTAMP())";
        } else if ($_GET["filter"] == 5) {
            $rWhere[] = "`users`.`is_trial` = 1";
        }
    }
    if ($rPermissions["is_admin"]) {
        if (strlen($_GET["reseller"]) > 0) {
            $rWhere[] = "`users`.`member_id` = " . intval($_GET["reseller"]);
        }
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(`users`.`id`) AS `count` FROM `users` LEFT JOIN `reg_users` ON `reg_users`.`id` = `users`.`member_id` INNER JOIN `mag_devices` ON `mag_devices`.`user_id` = `users`.`id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `users`.`id`, `users`.`username`, `mag_devices`.`mac`, `mag_devices`.`mag_id`, `users`.`exp_date`, `users`.`admin_enabled`, `users`.`enabled`, `users`.`isp_desc`, `users`.`is_isplock`, `users`.`admin_notes`, `users`.`reseller_notes`, `users`.`max_connections`,  `users`.`is_trial`, `reg_users`.`username` AS `owner_name`, (SELECT count(*) FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id`) AS `active_connections`, (SELECT user_ip FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id` LIMIT 1) AS `user_ip` FROM `users` LEFT JOIN `reg_users` ON `reg_users`.`id` = `users`.`member_id` INNER JOIN `mag_devices` ON `mag_devices`.`user_id` = `users`.`id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                if (!$rRow["admin_enabled"]) {
                    $rStatus = '<span class="mdi mdi-close mdi-24px text-danger role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Banned"></span>';
                } else {
                    if (!$rRow["enabled"]) {
                        $rStatus = '<span class="mdi mdi-close mdi-24px text-secondary" role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Disabled"></span>';
                    } else if (($rRow["exp_date"]) && ($rRow["exp_date"] < time())) {
                        $rStatus = '<span class="mdi mdi-close mdi-24px text-warning role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Expired"></span>';
                    } else {
                        $rStatus = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Active"></span>';
                    }
                }
                if ($rRow["active_connections"] > 0) {
                    $rActive = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Online"></span>';
                } else {
                    $rActive = '<span class="mdi mdi-close mdi-24px text-danger role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Offline"></span>';
                }
                if ($rRow["is_trial"]) {
                    $rTrial = '<span class="mdi mdi-close mdi-24px text-warning role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Trial"></span>';
                } else {
                    $rTrial = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Official"></span>';
                }
                if ($rRow["exp_date"]) {
                    if ($rRow["exp_date"] < time()) {
                        $rExpDate = "<mark span class=\"expired\">" . date("Y-m-d H:i", $rRow["exp_date"]) . "</mark>";
                    } else {
                        $rExpDate = date("Y-m-d H:i", $rRow["exp_date"]);
                    }
                } else {
                    $rExpDate = "Unlimited";
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["reseller_client_connection_logs"])) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "live_connections")))) {
                    $rActiveConnections = "<a href=\"./live_connections.php?user_id=" . $rRow["id"] . "\">" . $rRow["active_connections"] . "</a>";
                } else {
                    $rActiveConnections = $rRow["active_connections"];
                }
                $rButtons = '<div class="btn-group">';
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_reset_isplock"]))) {
                    //if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user"))) {
                    //if (($rPermissions["is_reseller"]) OR (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {	
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Reset isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'resetispuser\');"><i class="mdi mdi-lock-reset"></i></button>
					';
                }
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_can_isplock"]))) {
                    //if (($rPermissions["is_reseller"]) OR (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {	
                    if ($rRow["is_isplock"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Unlock isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'unlockk\');"><i class="mdi mdi-lock"></i></button>';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Lock isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'lockk\');"><i class="mdi mdi-lock-outline"></i></button>';
                    }
                }
                if (((strlen($rRow["admin_notes"]) > 0) && ($rPermissions["is_admin"])) or (strlen($rRow["reseller_notes"]) > 0)) {
                    $rNotes = "";
                    if ($rPermissions["is_admin"]) {
                        if (strlen($rRow["admin_notes"]) > 0) {
                            $rNotes .= $rRow["admin_notes"];
                        }
                    }
                    if (strlen($rRow["reseller_notes"]) > 0) {
                        if (strlen($rNotes) <> 0) {
                            $rNotes .= "\n";
                        }
                        $rNotes .= $rRow["reseller_notes"];
                    }
                    $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rNotes . '"><i class="mdi mdi-note"></i></button>';
                } else {
                    $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                }
                if ($rPermissions["is_admin"]) {
                    if (hasPermissions("adv", "manage_events")) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Send MAG Event" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="message(' . $rRow["mag_id"] . ', \'' . base64_decode($rRow["mac"]) . '\');"><i class="mdi mdi-comment-alert-outline"></i></button>
						';
                    }
                    if (hasPermissions("adv", "edit_mag")) {
                        $rButtons .= '<a href="./user.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
						';
                    }
                } else {
                    // next 4 lines add mag event button for resellers
                    if ($rAdminSettings["reseller_mag_events"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Seng MAG Event" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="message(' . $rRow["mag_id"] . ', \'' . base64_decode($rRow["mac"]) . '\');"><i class="mdi mdi-comment-alert-outline"></i></button>
						';
                    }
                    //selecao de bouquets inicio
                    if ((($rPermissions["is_reseller"]) && ($rPermissions["reseller_can_select_bouquets"])) or ($rPermissions["is_admin"])) {
                        $rButtons .= '<a href="./user_reseller_edit.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit User Bouquets" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-format-line-spacing"></i></button></a>';
                    }
                    //selecao de bouquets fim
                    $rButtons .= '<a href="./user_reseller.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
                }

                if (($rPermissions["is_reseller"]) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Kill Connections" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'kill\');"><i class="fas fa-hammer"></i></button>
					';
                }
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_mag"))) {
                    if ($rRow["admin_enabled"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Ban" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'ban\');"><i class="mdi mdi-minus-circle-outline"></i></button>
						';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Unban" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'unban\');"><i class="mdi mdi-minus-circle"></i></button>
						';
                    }
                }
                if (($rPermissions["is_reseller"]) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_mag")))) {
                    if ($rRow["enabled"] == 1) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Disable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'disable\');"><i class="mdi mdi-checkbox-blank-circle-outline"></i></button>
						';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'enable\');"><i class="mdi mdi-checkbox-blank-circle"></i></button>
						';
                    }
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_mag")))) {
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                }
                $rButtons .= '</div>';
                if ($rRow["last_active"]) {
                    $rLastActive = date("Y-m-d H:i", $rRow["last_active"]);
                } else {
                    $rLastActive = "Never";
                }
                $today = time();
                $leftdaynumber = (strtotime(date("Y-m-d H:i", $rRow["exp_date"])) - $today) / (60 * 60 * 24);
                $leftHourNumber = ($rRow["exp_date"] - $today) / (60 * 60);
                $leftMinNumber = ($rRow["exp_date"] - $today) / (60);
                if ($leftdaynumber > 0 && $leftdaynumber <= 1) {
                    $rLeftDate = "1 Day";
                } else if ($leftdaynumber > 1) {
                    $rLeftDate = round($leftdaynumber) . " Days";
                } else if ($leftHourNumber > 0 && $leftHourNumber <= 1) {
                    $rLeftDate = round($leftMinNumber) . " Minutes";
                } else if (round($leftHourNumber) == 1) {
                    $rLeftDate = "1 Hour";
                } else if ($leftHourNumber > 1) {
                    $rLeftDate = round($leftHourNumber) . " Hours";
                } else {
                    $rLeftDate = "<center>-</center>";
                }
                $query = "SELECT lines_live.date_start, lines_live.geoip_country_code, lines_live.user_ip, lines_live.stream_id, lines_live.user_id, streams.id, streams.stream_display_name FROM lines_live LEFT JOIN streams ON lines_live.stream_id = streams.id WHERE user_id = " . $rRow["id"];
                $ipTV_db_admin->query($query);
                $row2 = $ipTV_db_admin->get_row();
                if (!empty($row2['stream_display_name'])) {
                    $rTime = intval(time()) - intval($row2["date_start"]);
                    $rStream_name = "<span style='color: #20a009;'</span>" . $row2['stream_display_name'] . "<br><span style='color: #737373;'>" . "Uptime </span>" . "<span style='color: #737373;'>" . sprintf('%02d:%02d:%02d', ($rTime / 3600), ($rTime / 60 % 60), $rTime % 60) . "<br><a target='_blank' href='https://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $row2["user_ip"] . "'</span>" . $row2["user_ip"] . " " . $row2['geoip_country_code'] . "<br><span style='color: #737373;'></span>" . $rRow["isp_desc"];
                } else {
                    $rStream_name = "-";
                }
                $rButtons .= '</div>';
                $rReturn["data"][] = array($rRow["id"], $rRow["username"], base64_decode($rRow["mac"]), $rRow["owner_name"], $rStatus, $rActive, $rTrial, $rExpDate, $rLeftDate, "<font size=1>" . $rStream_name, $rButtons);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "enigmas") {
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "manage_e2"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`users`.`id`", "`users`.`username`", "`enigma2_devices`.`mac`", "`reg_users`.`username`", "`users`.`enabled`", "`active_connections`", "`users`.`is_trial`", "`users`.`exp_date`", "`users`.`isp_desc`", "`lines_live`.`user_ip`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if ($rPermissions["is_reseller"]) {
        $rWhere[] = "`users`.`member_id` IN (" . join(",", array_keys(getRegisteredUsers($rUserInfo["id"]))) . ")";
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`users`.`username` LIKE '%{$rSearch}%' OR `enigma2_devices`.`mac` LIKE '%{$rSearch}%' OR `reg_users`.`username` LIKE '%{$rSearch}%' OR from_unixtime(`exp_date`) LIKE '%{$rSearch}%' OR `users`.`reseller_notes` LIKE '%{$rSearch}%' OR `users`.`admin_notes` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "(`users`.`admin_enabled` = 1 AND `users`.`enabled` = 1 AND (`users`.`exp_date` IS NULL OR `users`.`exp_date` > UNIX_TIMESTAMP()))";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "`users`.`enabled` = 0";
        } else if ($_GET["filter"] == 3) {
            $rWhere[] = "`users`.`admin_enabled` = 0";
        } else if ($_GET["filter"] == 4) {
            $rWhere[] = "(`users`.`exp_date` IS NOT NULL AND `users`.`exp_date` <= UNIX_TIMESTAMP())";
        } else if ($_GET["filter"] == 5) {
            $rWhere[] = "`users`.`is_trial` = 1";
        }
    }
    if ($rPermissions["is_admin"]) {
        if (strlen($_GET["reseller"]) > 0) {
            $rWhere[] = "`users`.`member_id` = " . intval($_GET["reseller"]);
        }
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(`users`.`id`) AS `count` FROM `users` LEFT JOIN `reg_users` ON `reg_users`.`id` = `users`.`member_id` INNER JOIN `enigma2_devices` ON `enigma2_devices`.`user_id` = `users`.`id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `users`.`id`, `users`.`username`, `enigma2_devices`.`mac`, `users`.`exp_date`, `users`.`admin_enabled`, `users`.`enabled`, `users`.`isp_desc`, `users`.`is_isplock`, `users`.`admin_notes`, `users`.`reseller_notes`, `users`.`max_connections`,  `users`.`is_trial`, `reg_users`.`username` AS `owner_name`, (SELECT count(*) FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id`) AS `active_connections`, (SELECT user_ip FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id` LIMIT 1) AS `user_ip` FROM `users` LEFT JOIN `reg_users` ON `reg_users`.`id` = `users`.`member_id` INNER JOIN `enigma2_devices` ON `enigma2_devices`.`user_id` = `users`.`id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                if (!$rRow["admin_enabled"]) {
                    $rStatus = '<span class="mdi mdi-close mdi-24px text-danger role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Banned"></span>';
                } else {
                    if (!$rRow["enabled"]) {
                        $rStatus = '<span class="mdi mdi-close mdi-24px text-secondary" role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Disabled"></span>';
                    } else if (($rRow["exp_date"]) && ($rRow["exp_date"] < time())) {
                        $rStatus = '<span class="mdi mdi-close mdi-24px text-warning role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Expired"></span>';
                    } else {
                        $rStatus = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Active"></span>';
                    }
                }
                if ($rRow["active_connections"] > 0) {
                    $rActive = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Online"></span>';
                } else {
                    $rActive = '<span class="mdi mdi-close mdi-24px text-danger role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Offline"></span>';
                }
                if ($rRow["is_trial"]) {
                    $rTrial = '<span class="mdi mdi-close mdi-24px text-warning role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Trial"></span>';
                } else {
                    $rTrial = '<span class="mdi mdi-check mdi-24px text-success role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Official"></span>';
                }
                if ($rRow["exp_date"]) {
                    if ($rRow["exp_date"] < time()) {
                        $rExpDate = "<mark span class=\"expired\">" . date("Y-m-d H:i", $rRow["exp_date"]) . "</mark>";
                    } else {
                        $rExpDate = date("Y-m-d H:i", $rRow["exp_date"]);
                    }
                } else {
                    $rExpDate = "Unlimited";
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["reseller_client_connection_logs"])) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "live_connections")))) {
                    $rActiveConnections = "<a href=\"./live_connections.php?user_id=" . $rRow["id"] . "\">" . $rRow["active_connections"] . "</a>";
                } else {
                    $rActiveConnections = $rRow["active_connections"];
                }
                $rButtons = '<div class="btn-group">';
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_reset_isplock"]))) {
                    //if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user"))) {
                    //if (($rPermissions["is_reseller"]) OR (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {	
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Reset isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'resetispuser\');"><i class="mdi mdi-lock-reset"></i></button>
					';
                }
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")) or (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_can_isplock"]))) {
                    //if (($rPermissions["is_reseller"]) OR (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {	
                    if ($rRow["is_isplock"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Unlock isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'unlockk\');"><i class="mdi mdi-lock"></i></button>';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Lock isp" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'lockk\');"><i class="mdi mdi-lock-outline"></i></button>';
                    }
                }
                if (((strlen($rRow["admin_notes"]) > 0) && ($rPermissions["is_admin"])) or (strlen($rRow["reseller_notes"]) > 0)) {
                    $rNotes = "";
                    if ($rPermissions["is_admin"]) {
                        if (strlen($rRow["admin_notes"]) > 0) {
                            $rNotes .= $rRow["admin_notes"];
                        }
                    }
                    if (strlen($rRow["reseller_notes"]) > 0) {
                        if (strlen($rNotes) <> 0) {
                            $rNotes .= "\n";
                        }
                        $rNotes .= $rRow["reseller_notes"];
                    }
                    $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rNotes . '"><i class="mdi mdi-note"></i></button>';
                } else {
                    $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                }
                if ($rPermissions["is_admin"]) {
                    if (hasPermissions("adv", "edit_e2")) {
                        $rButtons .= '<a href="./user.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
                    }
                } else {
                    //selecao de bouquets inicio
                    if ((($rPermissions["is_reseller"]) && ($rPermissions["reseller_can_select_bouquets"])) or ($rPermissions["is_admin"])) {
                        $rButtons .= '<a href="./user_reseller_edit.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit User Bouquets" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-format-line-spacing"></i></button></a>';
                    }
                    //selecao de bouquets fim
                    $rButtons .= '<a href="./user_reseller.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
                }
                if (($rPermissions["is_reseller"]) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_user")))) {
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Kill Connections" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'kill\');"><i class="fas fa-hammer"></i></button>
					';
                }
                if (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_e2"))) {
                    if ($rRow["admin_enabled"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Ban" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'ban\');"><i class="mdi mdi-minus-circle-outline"></i></button>';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Unban" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'unban\');"><i class="mdi mdi-minus-circle"></i></button>';
                    }
                }
                if (($rPermissions["is_reseller"]) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_e2")))) {
                    if ($rRow["enabled"]) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Disable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'disable\');"><i class="mdi mdi-checkbox-blank-circle-outline"></i></button>
						';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'enable\');"><i class="mdi mdi-checkbox-blank-circle"></i></button>
						';
                    }
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_e2")))) {
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                }
                $today = time();
                $leftdaynumber = (strtotime(date("Y-m-d H:i", $rRow["exp_date"])) - $today) / (60 * 60 * 24);
                $leftHourNumber = ($rRow["exp_date"] - $today) / (60 * 60);
                $leftMinNumber = ($rRow["exp_date"] - $today) / (60);
                if ($leftdaynumber > 0 && $leftdaynumber <= 1) {
                    $rLeftDate = "1 Day";
                } else if ($leftdaynumber > 1) {
                    $rLeftDate = round($leftdaynumber) . " Days";
                } else if ($leftHourNumber > 0 && $leftHourNumber <= 1) {
                    $rLeftDate = round($leftMinNumber) . " Minutes";
                } else if (round($leftHourNumber) == 1) {
                    $rLeftDate = "1 Hour";
                } else if ($leftHourNumber > 1) {
                    $rLeftDate = round($leftHourNumber) . " Hours";
                } else {
                    $rLeftDate = "<center>-</center>";
                }
                $query = "SELECT lines_live.date_start, lines_live.geoip_country_code, lines_live.user_ip, lines_live.stream_id, lines_live.user_id, streams.id, streams.stream_display_name FROM lines_live LEFT JOIN streams ON lines_live.stream_id = streams.id WHERE user_id = " . $rRow["id"];
                $ipTV_db_admin->query($query);
                $row2 = $ipTV_db_admin->get_row();
                if (!empty($row2['stream_display_name'])) {
                    $rTime = intval(time()) - intval($row2["date_start"]);
                    $rStream_name = "<span style='color: #20a009;'</span>" . $row2['stream_display_name'] . "<br><span style='color: #737373;'>" . "Uptime </span>" . "<span style='color: #737373;'>" . sprintf('%02d:%02d:%02d', ($rTime / 3600), ($rTime / 60 % 60), $rTime % 60) . "<br><a target='_blank' href='https://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $row2["user_ip"] . "'</span>" . $row2["user_ip"] . " " . $row2['geoip_country_code'] . "<br><span style='color: #737373;'></span>" . $rRow["isp_desc"];
                } else {
                    $rStream_name = "-";
                }
                $rButtons .= '</div>';
                $rReturn["data"][] = array($rRow["id"], $rRow["username"], $rRow["mac"], $rRow["owner_name"], $rStatus, $rActive, $rTrial, $rExpDate, $rLeftDate, "<font size=1>" . $rStream_name, $rButtons);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "streams") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "streams")) && (!hasPermissions("adv", "mass_edit_streams"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", false, "`streams`.`stream_display_name`", "`streams_servers`.`current_source`", "`clients`", "`streams_servers`.`stream_started`", false, false, false, "`streams_servers`.`bitrate`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` in (1,3)";
    if (isset($_GET["stream_id"])) {
        $rWhere[] = "`streams`.`id` = " . intval($_GET["stream_id"]);
        $rOrderBy = "ORDER BY `streams_servers`.`server_stream_id` ASC";
    } else {
        if (strlen($_GET["search"]["value"]) > 0) {
            $rSearch = $_GET["search"]["value"];
            $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `streams`.`notes` LIKE '%{$rSearch}%' OR `streams_servers`.`current_source` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%' OR `streaming_servers`.`server_name` LIKE '%{$rSearch}%')";
        }
        if (strlen($_GET["filter"]) > 0) {
            if ($_GET["filter"] == 1) {
                $rWhere[] = "(`streams_servers`.`monitor_pid` > 0 AND `streams_servers`.`pid` > 0)";
            } else if ($_GET["filter"] == 2) {
                $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0)";
            } else if ($_GET["filter"] == 3) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`monitor_pid` IS NULL OR `streams_servers`.`monitor_pid` <= 0) AND `streams_servers`.`on_demand` = 0)";
            } else if ($_GET["filter"] == 4) {
                $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` = 0)";
            } else if ($_GET["filter"] == 5) {
                $rWhere[] = "`streams_servers`.`on_demand` = 1";
            } else if ($_GET["filter"] == 6) {
                $rWhere[] = "`streams`.`direct_source` = 1";
            } else if ($_GET["filter"] == 7) {
                $rWhere[] = "`streams`.`tv_archive_duration` > 0";
            } else if ($_GET["filter"] == 8) {
                $rWhere[] = "`streams`.`type` = 3";
            }
        }
        if (strlen($_GET["category"]) > 0) {
            $rWhere[] = "`streams`.`category_id` = " . intval($_GET["category"]);
        }
        if (strlen($_GET["server"]) > 0) {
            $rWhere[] = "`streams_servers`.`server_id` = " . intval($_GET["server"]);
        }
        if ($rOrder[$rOrderRow]) {
            $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
            $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
        }
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT (SELECT COUNT(`id`) FROM `epg_data` WHERE `epg_data`.`epg_id` = `streams`.`epg_id` AND `epg_data`.`channel_id` = `streams`.`channel_id`) AS `count_epg`, `streams`.`id`, `streams`.`type`, `streams`.`stream_icon`, `streams`.`cchannel_rsources`, `streams`.`stream_source`, `streams`.`stream_display_name`, `streams`.`tv_archive_duration`, `streams_servers`.`server_id`, `streams`.`notes`, `streams`.`direct_source`, `streams_servers`.`pid`, `streams_servers`.`monitor_pid`, `streams_servers`.`stream_status`, `streams_servers`.`stream_started`, `streams_servers`.`stream_info`, `streams_servers`.`current_source`, `streams_servers`.`bitrate`, `streams_servers`.`progress_info`, `streams_servers`.`on_demand`, `stream_categories`.`category_name`, `streaming_servers`.`server_name`, (SELECT COUNT(*) FROM `lines_live` WHERE `lines_live`.`server_id` = `streams_servers`.`server_id` AND `lines_live`.`stream_id` = `streams`.`id`) AS `clients` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                $rCategory = $rRow["category_name"] ?: "No Category";
                if ($rRow["tv_archive_duration"] > 0) {
                    $rRow["stream_display_name"] .= " <i class='text-danger mdi mdi-record'></i>";
                }
                $rStreamName = "<b>" . $rRow['stream_display_name'] . "</b><br><span style='font-size:11px;'>{$rCategory}</span>";
                if ($rRow["server_name"]) {
                    if ($rPermissions["is_admin"]) {
                        $rServerName = $rRow["server_name"];
                    } else {
                        $rServerName = "Server #" . $rRow["server_id"];
                    }
                } else {
                    $rServerName = "No Server Selected";
                }
                if ($rRow["type"] == 3) {
                    $rStreamSource = "<br/><span style='font-size:11px;'>Created Channel</span>";
                } else {
                    $rStreamSource = "<br/><span style='font-size:11px;'>" . parse_url($rRow["current_source"])['host'] . "</span>";
                }
                if ($rPermissions["is_admin"]) {
                    $rServerName .= $rStreamSource;
                }
                $rUptime = 0;
                $rActualStatus = 0;
                if (intval($rRow["direct_source"]) == 1) {
                    // Direct
                    $rActualStatus = 5;
                } else if ($rRow["monitor_pid"]) {
                    // Started
                    if (($rRow["pid"]) && ($rRow["pid"] > 0)) {
                        // Running
                        $rActualStatus = 1;
                        $rUptime = time() - intval($rRow["stream_started"]);
                    } else {
                        if (intval($rRow["stream_status"]) == 0) {
                            // Starting
                            $rActualStatus = 2;
                        } else {
                            // Stalled
                            $rActualStatus = 3;
                        }
                    }
                } else if (intval($rRow["on_demand"]) == 1) {
                    // On Demand
                    $rActualStatus = 4;
                } else {
                    // Stopped
                    $rActualStatus = 0;
                }
                if ($rRow["type"] == 3) {
                    if (count(json_decode($rRow["cchannel_rsources"], true)) <> count(json_decode($rRow["stream_source"], true))) {
                        // Still processing...
                        $rActualStatus = 6;
                    }
                }
                if (hasPermissions("adv", "live_connections")) {
                    $rClients = "<a href=\"./live_connections.php?stream_id=" . $rRow["id"] . "&server_id=" . $rRow["server_id"] . "\">" . $rRow["clients"] . "</a>";
                } else {
                    $rClients = $rRow["clients"];
                }
                if ($rActualStatus == 1) {
                    if ($rUptime >= 86400) {
                        $rUptime = sprintf('%02dd %02dh %02dm %02ds', ($rUptime / 86400), ($rUptime / 3600 % 24), ($rUptime / 60 % 60), ($rUptime % 60));
                    } else {
                        $rUptime = sprintf('%02dh %02dm %02ds', ($rUptime / 3600), ($rUptime / 60 % 60), ($rUptime % 60));
                    }
                    $rUptime = "<button type='button' class='btn btn-outline-success btn-rounded btn-xs waves-effect waves-light'>{$rUptime}</button>";
                } else {
                    $rUptime = $rStatusArray[$rActualStatus];
                }
                if (!$rRow["server_id"]) {
                    $rRow["server_id"] = 0;
                }
                $rButtons = '<div class="btn-group">';
                if ($rPermissions["is_admin"]) {
                    if (strlen($rRow["notes"]) > 0) {
                        $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rRow["notes"] . '"><i class="mdi mdi-note"></i></button>';
                    } else {
                        $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                    }
                }
                if (hasPermissions("adv", "edit_stream")) {
                    if ((intval($rActualStatus) == 1) or (intval($rActualStatus) == 2) or (intval($rActualStatus) == 3) or ($rRow["on_demand"] == 1) or ($rActualStatus == 5)) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Stop" type="button" class="btn btn-light waves-effect waves-light btn-xs api-stop" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'stop\');"><i class="mdi mdi-stop"></i></button>
						';
                        $rStatus = '';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Start" type="button" class="btn btn-light waves-effect waves-light btn-xs api-start" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'start\');"><i class="mdi mdi-play"></i></button>
						';
                        $rStatus = ' disabled';
                    }
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Restart" type="button" class="btn btn-light waves-effect waves-light btn-xs api-restart" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'restart\');"' . $rStatus . '><i class="mdi mdi-refresh"></i></button>
					';
                    if ($rRow["type"] == 3) {
                        $rButtons .= '<a href="./created_channel.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
						';
                    } else {
                        $rButtons .= '<a href="./stream.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
						';
                    }
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>
					';
                }
                $rButtons .= '</div>';
                if (hasPermissions("adv", "player")) {
                    if (((intval($rActualStatus) == 1) or ($rRow["on_demand"] == 1) or ($rActualStatus == 5))) {
                        $rPlayer = '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Play" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="player(' . $rRow["id"] . ');"><i class="mdi mdi-play"></i></button>';
                    } else {
                        $rPlayer = '<button type="button" disabled class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-play"></i></button>';
                    }
                } else {
                    $rPlayer = '<button type="button" disabled class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-play"></i></button>';
                }
                $rStreamInfoText = "<div style='font-size: 10px;' class='text-center' align='center'><tbody><tr><td colspan='5' class='col'>No information available</td></tr></tbody></div>";
                $rStreamInfo = json_decode($rRow["stream_info"], true);
                if ($rStreamInfo == 0 || $rStreamInfo == "") {
                    $rStreamInfo = [];
                }
                $rProgressInfo = json_decode($rRow["progress_info"], true);
                if ($rActualStatus == 1) {
                    if (!isset($rStreamInfo["codecs"]["video"])) {
                        $rStreamInfo["codecs"]["video"] = array("width" => "?", "height" => "?", "codec_name" => "N/A", "r_frame_rate" => "--");
                    }
                    if (!isset($rStreamInfo["codecs"]["audio"])) {
                        $rStreamInfo["codecs"]["audio"] = array("codec_name" => "N/A");
                    }
                    if ($rRow['bitrate'] == 0) {
                        $rRow['bitrate'] = "?";
                    }
                    if (isset($rProgressInfo["speed"])) {
                        $rSpeed = $rProgressInfo["speed"];
                    } else {
                        $rSpeed = "--";
                    }
                    if (isset($rProgressInfo["fps"])) {
                        $rFPS = intval($rProgressInfo["fps"]) . " FPS";
                    } else {
                        if (isset($rStreamInfo["codecs"]["video"]["r_frame_rate"])) {
                            $rFPS = intval($rStreamInfo["codecs"]["video"]["r_frame_rate"]) . " FPS";
                        } else {
                            $rFPS = "--";
                        }
                    }
                    $rStreamInfoText = "<div style='font-size: 12px;' class='text-center' align='center'>
                                <td class='col'><i class='mdi mdi-video' data-name='mdi-video' style='color: #20a009;'></i>" . $rStreamInfo["codecs"]["video"]["codec_name"] . "</td>
                                <td class='col'><i class='mdi mdi-volume-high' data-name='mdi-volume-high' style='color: #20a009;'></i>" . $rStreamInfo["codecs"]["audio"]["codec_name"] . "</td>
                                <td class='col'><i class='mdi mdi-play-speed' data-name='mdi-play-speed' style='color: #20a009;'></i>" . $rSpeed . "</td>
                                <td class='col'><i class='mdi mdi-layers' data-name='mdi-layers' style='color: #20a009;'></i>" . $rFPS . "</td>
								<br>
								<td class='col'>" . $rRow['bitrate'] . " Kbps " . $rStreamInfo["codecs"]["video"]["width"] . "x" . $rStreamInfo["codecs"]["video"]["height"] . "</td>
                    </div>";
                }
                if ($rRow["count_epg"] > 0) {
                    $rEPG = '<i class="text-success fas fa-circle"></i>';
                } else if ($rRow["channel_id"]) {
                    $rEPG = '<i class="text-warning fas fa-circle"></i>';
                } else {
                    $rEPG = '<i class="text-danger far fa-circle"></i>';
                }
                if (strlen($rRow["stream_icon"]) > 0) {
                    $rIcon = "<img src='./resize.php?max=32&url=" . $rRow["stream_icon"] . "' />";
                } else {
                    $rIcon = "";
                }
                if ($rPermissions["is_admin"]) {
                    $rReturn["data"][] = array($rRow["id"], $rIcon, $rStreamName, $rServerName, $rClients, $rUptime, $rButtons, $rPlayer, $rEPG, $rStreamInfoText);
                } else {
                    $rReturn["data"][] = array($rRow["id"], $rIcon, $rStreamName, $rServerName, $rStreamInfoText);
                }
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "radios") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "radio")) && (!hasPermissions("adv", "mass_edit_radio"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    if ($rPermissions["is_admin"]) {
        $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`streams_servers`.`current_source`", "`clients`", "`streams_servers`.`stream_started`", false, "`streams_servers`.`bitrate`");
    } else {
        $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`streams_servers`.`current_source`", "`streams_servers`.`bitrate`");
    }
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` = 4";
    if (isset($_GET["stream_id"])) {
        $rWhere[] = "`streams`.`id` = " . intval($_GET["stream_id"]);
        $rOrderBy = "ORDER BY `streams_servers`.`server_stream_id` ASC";
    } else {
        if (strlen($_GET["search"]["value"]) > 0) {
            $rSearch = $_GET["search"]["value"];
            $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `streams`.`notes` LIKE '%{$rSearch}%' OR `streams_servers`.`current_source` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%' OR `streaming_servers`.`server_name` LIKE '%{$rSearch}%')";
        }
        if (strlen($_GET["filter"]) > 0) {
            if ($_GET["filter"] == 1) {
                $rWhere[] = "(`streams_servers`.`monitor_pid` > 0 AND `streams_servers`.`pid` > 0)";
            } else if ($_GET["filter"] == 2) {
                $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0)";
            } else if ($_GET["filter"] == 3) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`monitor_pid` IS NULL OR `streams_servers`.`monitor_pid` <= 0) AND `streams_servers`.`on_demand` = 0)";
            } else if ($_GET["filter"] == 4) {
                $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` = 0)";
            } else if ($_GET["filter"] == 5) {
                $rWhere[] = "`streams_servers`.`on_demand` = 1";
            } else if ($_GET["filter"] == 6) {
                $rWhere[] = "`streams`.`direct_source` = 1";
            }
        }
        if (strlen($_GET["category"]) > 0) {
            $rWhere[] = "`streams`.`category_id` = " . intval($_GET["category"]);
        }
        if (strlen($_GET["server"]) > 0) {
            $rWhere[] = "`streams_servers`.`server_id` = " . intval($_GET["server"]);
        }
        if ($rOrder[$rOrderRow]) {
            $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
            $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
        }
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams`.`type`, `streams`.`cchannel_rsources`, `streams`.`stream_source`, `streams`.`stream_display_name`, `streams`.`tv_archive_duration`, `streams_servers`.`server_id`, `streams`.`notes`, `streams`.`direct_source`, `streams_servers`.`pid`, `streams_servers`.`monitor_pid`, `streams_servers`.`stream_status`, `streams_servers`.`stream_started`, `streams_servers`.`stream_info`, `streams_servers`.`current_source`, `streams_servers`.`bitrate`, `streams_servers`.`progress_info`, `streams_servers`.`on_demand`, `stream_categories`.`category_name`, `streaming_servers`.`server_name`, (SELECT COUNT(*) FROM `lines_live` WHERE `lines_live`.`server_id` = `streams_servers`.`server_id` AND `lines_live`.`stream_id` = `streams`.`id`) AS `clients` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                $rCategory = $rRow["category_name"] ?: "No Category";
                $rStreamName = "<b>" . $rRow['stream_display_name'] . "</b><br><span style='font-size:11px;'>{$rCategory}</span>";
                if ($rRow["server_name"]) {
                    if ($rPermissions["is_admin"]) {
                        $rServerName = $rRow["server_name"];
                    } else {
                        $rServerName = "Server #" . $rRow["server_id"];
                    }
                } else {
                    $rServerName = "No Server Selected";
                }
                $rStreamSource = "<br/><span style='font-size:11px;'>" . parse_url($rRow["current_source"])['host'] . "</span>";
                if ($rPermissions["is_admin"]) {
                    $rServerName .= $rStreamSource;
                }
                $rUptime = 0;
                $rActualStatus = 0;
                if (intval($rRow["direct_source"]) == 1) {
                    // Direct
                    $rActualStatus = 5;
                } else if ($rRow["monitor_pid"]) {
                    // Started
                    if (($rRow["pid"]) && ($rRow["pid"] > 0)) {
                        // Running
                        $rActualStatus = 1;
                        $rUptime = time() - intval($rRow["stream_started"]);
                    } else {
                        if (intval($rRow["stream_status"]) == 0) {
                            // Starting
                            $rActualStatus = 2;
                        } else {
                            // Stalled
                            $rActualStatus = 3;
                        }
                    }
                } else if (intval($rRow["on_demand"]) == 1) {
                    // On Demand
                    $rActualStatus = 4;
                } else {
                    // Stopped
                    $rActualStatus = 0;
                }
                if (hasPermissions("adv", "live_connections")) {
                    $rClients = "<a href=\"./live_connections.php?stream_id=" . $rRow["id"] . "&server_id=" . $rRow["server_id"] . "\">" . $rRow["clients"] . "</a>";
                } else {
                    $rClients = $rRow["clients"];
                }
                if ($rActualStatus == 1) {
                    if ($rUptime >= 86400) {
                        $rUptime = sprintf('%02dd %02dh %02dm %02ds', ($rUptime / 86400), ($rUptime / 3600 % 24), ($rUptime / 60 % 60), ($rUptime % 60));
                    } else {
                        $rUptime = sprintf('%02dh %02dm %02ds', ($rUptime / 3600), ($rUptime / 60 % 60), ($rUptime % 60));
                    }
                    $rUptime = "<button type='button' class='btn btn-outline-success btn-rounded btn-xs waves-effect waves-light'>{$rUptime}</button>";
                } else {
                    $rUptime = $rStatusArray[$rActualStatus];
                }
                if (!$rRow["server_id"]) {
                    $rRow["server_id"] = 0;
                }
                $rButtons = '<div class="btn-group">';
                if ($rPermissions["is_admin"]) {
                    if (strlen($rRow["notes"]) > 0) {
                        $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rRow["notes"] . '"><i class="mdi mdi-note"></i></button>';
                    } else {
                        $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                    }
                }
                if (hasPermissions("adv", "edit_radio")) {
                    if ((intval($rActualStatus) == 1) or (intval($rActualStatus) == 2) or (intval($rActualStatus) == 3) or ($rRow["on_demand"] == 1) or ($rActualStatus == 5)) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Stop" type="button" class="btn btn-light waves-effect waves-light btn-xs api-stop" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'stop\');"><i class="mdi mdi-stop"></i></button>
						';
                        $rStatus = '';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Start" type="button" class="btn btn-light waves-effect waves-light btn-xs api-start" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'start\');"><i class="mdi mdi-play"></i></button>
						';
                        $rStatus = ' disabled';
                    }
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Restart" type="button" class="btn btn-light waves-effect waves-light btn-xs api-restart" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'restart\');"' . $rStatus . '><i class="mdi mdi-refresh"></i></button>
					';
                    if ($rRow["type"] == 3) {
                        $rButtons .= '<a href="./created_channel.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
						';
                    } else {
                        $rButtons .= '<a href="./radio.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
						';
                    }
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>
					';
                }
                $rButtons .= '</div>';
                $rStreamInfoText = "<table style='font-size: 10px;' class='text-center' align='center'><tbody><tr><td colspan='5' class='col'>No information available</td></tr></tbody></table>";
                $rStreamInfo = json_decode($rRow["stream_info"], true);
                $rProgressInfo = json_decode($rRow["progress_info"], true);
                if ($rActualStatus == 1) {
                    if (!isset($rStreamInfo["codecs"]["video"])) {
                        $rStreamInfo["codecs"]["video"] = array("width" => "?", "height" => "?", "codec_name" => "N/A", "r_frame_rate" => "--");
                    }
                    if (!isset($rStreamInfo["codecs"]["audio"])) {
                        $rStreamInfo["codecs"]["audio"] = array("codec_name" => "N/A");
                    }
                    if ($rRow['bitrate'] == 0) {
                        $rRow['bitrate'] = "?";
                    }
                    if (isset($rProgressInfo["speed"])) {
                        $rSpeed = $rProgressInfo["speed"];
                    } else {
                        $rSpeed = "--";
                    }
                    if (isset($rProgressInfo["fps"])) {
                        $rFPS = intval($rProgressInfo["fps"]) . " FPS";
                    } else {
                        if (isset($rStreamInfo["codecs"]["video"]["r_frame_rate"])) {
                            $rFPS = intval($rStreamInfo["codecs"]["video"]["r_frame_rate"]) . " FPS";
                        } else {
                            $rFPS = "--";
                        }
                    }
                    $rStreamInfoText = "<table style='font-size: 12px;' class='text-center' align='center'>
                        <tbody>
                            <tr>
                                <td class='col' style='color: #20a009;'><i class='mdi mdi-video' data-name='mdi-video'></i></td>
                                <td class='col' style='color: #20a009;'><i class='mdi mdi-volume-high' data-name='mdi-volume-high'></i></td>
                                <td class='col' style='color: #20a009;'><i class='mdi mdi-play-speed' data-name='mdi-play-speed'></i></td>
                            </tr>
                            <tr>
                                <td class='col'>" . $rRow['bitrate'] . " Kbps</td>
                                <td class='col'>" . $rStreamInfo["codecs"]["audio"]["codec_name"] . "</td>
                                <td class='col'>" . $rSpeed . "</td>
                            </tr>
                        </tbody>
                    </table>";
                }
                if ($rPermissions["is_admin"]) {
                    $rReturn["data"][] = array($rRow["id"], $rStreamName, $rServerName, $rClients, $rUptime, $rButtons, $rStreamInfoText);
                } else {
                    $rReturn["data"][] = array($rRow["id"], $rStreamName, $rServerName, $rStreamInfoText);
                }
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "movies") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "movies")) && (!hasPermissions("adv", "mass_sedits_vod"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`streams_servers`.`current_source`", "`streaming_servers`.`server_name`", "`clients`", "`streams_servers`.`stream_started`", false, false, "`streams_servers`.`bitrate`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` = 2";
    if (isset($_GET["stream_id"])) {
        $rWhere[] = "`streams`.`id` = " . intval($_GET["stream_id"]);
        $rOrderBy = "ORDER BY `streams_servers`.`server_stream_id` ASC";
    } else {
        if (strlen($_GET["search"]["value"]) > 0) {
            $rSearch = $_GET["search"]["value"];
            $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `streams`.`notes` LIKE '%{$rSearch}%' OR `streams_servers`.`current_source` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%' OR `streaming_servers`.`server_name` LIKE '%{$rSearch}%')";
        }
        if (strlen($_GET["filter"]) > 0) {
            if ($_GET["filter"] == 1) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 0 AND `streams_servers`.`stream_status` <> 1)";
            } else if ($_GET["filter"] == 2) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 1 AND `streams_servers`.`stream_status` <> 1)";
            } else if ($_GET["filter"] == 3) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`stream_status` = 1)";
            } else if ($_GET["filter"] == 4) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 1)";
            } else if ($_GET["filter"] == 5) {
                $rWhere[] = "`streams`.`direct_source` = 1";
            } else if ($_GET["filter"] == 6) {
                $rWhere[] = "(`streams`.`movie_properties` IS NULL OR `streams`.`movie_properties` = '' OR `streams`.`movie_properties` = '[]' OR `streams`.`movie_properties` = '{}' OR `streams`.`movie_properties` LIKE '%tmdb_id\":\"\"%')";
            }
        }
        if (strlen($_GET["category"]) > 0) {
            $rWhere[] = "`streams`.`category_id` = " . intval($_GET["category"]);
        }
        if (strlen($_GET["server"]) > 0) {
            $rWhere[] = "`streams_servers`.`server_id` = " . intval($_GET["server"]);
        }
        if ($rOrder[$rOrderRow]) {
            $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
            $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
        }
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams_servers`.`to_analyze`, `streams`.`target_container`, `streams`.`stream_display_name`, `streams_servers`.`server_id`, `streams`.`notes`, `streams`.`direct_source`, `streams_servers`.`pid`, `streams_servers`.`monitor_pid`, `streams_servers`.`stream_status`, `streams_servers`.`stream_started`, `streams_servers`.`stream_info`, `streams_servers`.`current_source`, `streams_servers`.`bitrate`, `streams_servers`.`progress_info`, `streams_servers`.`on_demand`, `stream_categories`.`category_name`, `streaming_servers`.`server_name`, (SELECT COUNT(*) FROM `lines_live` WHERE `lines_live`.`server_id` = `streams_servers`.`server_id` AND `lines_live`.`stream_id` = `streams`.`id`) AS `clients` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                $rCategory = $rRow["category_name"] ?: "No Category";
                $rStreamName = "<b>" . $rRow['stream_display_name'] . "</b><br><span style='font-size:11px;'>{$rCategory}</span>";
                if ($rRow["server_name"]) {
                    if ($rPermissions["is_admin"]) {
                        $rServerName = $rRow["server_name"];
                    } else {
                        $rServerName = "Server #" . $rRow["server_id"];
                    }
                } else {
                    $rServerName = "No Server Selected";
                }
                $rUptime = 0;
                $rActualStatus = 0;
                if (intval($rRow["direct_source"]) == 1) {
                    // Direct
                    $rActualStatus = 3;
                } else if ($rRow["pid"]) {
                    if ($rRow["to_analyze"] == 1) {
                        $rActualStatus = 2; // Encoding
                    } else if ($rRow["stream_status"] == 1) {
                        $rActualStatus = 4; // Down
                    } else {
                        $rActualStatus = 1; // Encoded
                    }
                } else {
                    // Not Encoded
                    $rActualStatus = 0;
                }
                if (hasPermissions("adv", "live_connections")) {
                    $rClients = "<a href=\"./live_connections.php?stream_id=" . $rRow["id"] . "&server_id=" . $rRow["server_id"] . "\">" . $rRow["clients"] . "</a>";
                } else {
                    $rClients = $rRow["clients"];
                }
                if (!$rRow["server_id"]) {
                    $rRow["server_id"] = 0;
                }
                $rButtons = '<div class="btn-group">';
                if ($rPermissions["is_admin"]) {
                    if (strlen($rRow["notes"]) > 0) {
                        $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rRow["notes"] . '"><i class="mdi mdi-note"></i></button>';
                    } else {
                        $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                    }
                }
                if (hasPermissions("adv", "edit_movie")) {
                    if (intval($rActualStatus) == 1) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Encode" type="button" class="btn btn-light waves-effect waves-light btn-xs api-start" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'start\');"><i class="mdi mdi-refresh"></i></button>
						';
                    } else if (intval($rActualStatus) == 3) {
                        $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs api-stop"><i class="mdi mdi-stop"></i></button>
						';
                    } else if (intval($rActualStatus) == 2) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Stop Encoding" type="button" class="btn btn-light waves-effect waves-light btn-xs api-stop" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'stop\');"><i class="mdi mdi-stop"></i></button>
						';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Start Encoding" type="button" class="btn btn-light waves-effect waves-light btn-xs api-start" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'start\');"><i class="mdi mdi-play"></i></button>
						';
                    }
                    $rButtons .= '<a href="./movie.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
					<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                }
                $rButtons .= '</div>';
                if (hasPermissions("adv", "player")) {
                    if (((intval($rActualStatus) == 1) or ($rActualStatus == 3))) {
                        $rPlayer = '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Play" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="player(' . $rRow["id"] . ', \'' . json_decode($rRow["target_container"], true)[0] . '\');"><i class="mdi mdi-play"></i></button>';
                    } else {
                        $rPlayer = '<button type="button" disabled class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-play"></i></button>';
                    }
                } else {
                    $rPlayer = '<button type="button" disabled class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-play"></i></button>';
                }
                $rStreamInfoText = "<div style='font-size: 10px;' class='text-center' align='center'><tbody><tr><td colspan='3' class='col'>No information available</td></tr></tbody></div>";
                $rStreamInfo = json_decode($rRow["stream_info"], true);
                if ($rActualStatus == 1) {
                    if (!isset($rStreamInfo["codecs"]["video"])) {
                        $rStreamInfo["codecs"]["video"] = array("width" => "?", "height" => "?", "codec_name" => "N/A", "r_frame_rate" => "--");
                    }
                    if (!isset($rStreamInfo["codecs"]["audio"])) {
                        $rStreamInfo["codecs"]["audio"] = array("codec_name" => "N/A");
                    }
                    if ($rRow['bitrate'] == 0) {
                        $rRow['bitrate'] = "?";
                    }
                    $rStreamInfoText = "<div style='font-size: 12px;' class='text-center' align='center'>
                                <td class='col'><i class='mdi mdi-video' data-name='mdi-video' style='color: #20a009;'></i>" . $rStreamInfo["codecs"]["video"]["codec_name"] . "</td>
                                <td class='col'><i class='mdi mdi-volume-high' data-name='mdi-volume-high' style='color: #20a009;'></i>" . $rStreamInfo["codecs"]["audio"]["codec_name"] . "</td>
								<br>
								<td class='col'>" . $rRow['bitrate'] . " Kbps " . $rStreamInfo["codecs"]["video"]["width"] . "x" . $rStreamInfo["codecs"]["video"]["height"] . "</td>
                    </div>";
                }
                if ($rPermissions["is_admin"]) {
                    $rReturn["data"][] = array($rRow["id"], $rStreamName, $rServerName, $rClients, $rVODStatusArray[$rActualStatus], $rButtons, $rPlayer, $rStreamInfoText);
                } else {
                    $rReturn["data"][] = array($rRow["id"], $rStreamName, $rServerName, $rStreamInfoText);
                }
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "episode_list") {
    if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "import_episodes")) && (!hasPermissions("adv", "mass_delete")))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`series`.`title`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` = 5";
    if (strlen($_GET["series"]) > 0) {
        $rWhere[] = "`series_episodes`.`series_id` = " . intval($_GET["series"]);
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `series`.`title` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 0 AND `streams_servers`.`stream_status` <> 1)";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 1 AND `streams_servers`.`stream_status` <> 1)";
        } else if ($_GET["filter"] == 3) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`stream_status` = 1)";
        } else if ($_GET["filter"] == 4) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 1)";
        } else if ($_GET["filter"] == 5) {
            $rWhere[] = "`streams`.`direct_source` = 1";
        }
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `series_episodes` ON `series_episodes`.`stream_id` = `streams`.`id` LEFT JOIN `series` ON `series`.`id` = `series_episodes`.`series_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams`.`stream_display_name`, `series`.`title`, `streams`.`direct_source`, `streams_servers`.`to_analyze`, `streams_servers`.`pid` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `series_episodes` ON `series_episodes`.`stream_id` = `streams`.`id` LEFT JOIN `series` ON `series`.`id` = `series_episodes`.`series_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rActualStatus = 0;
                if (intval($rRow["direct_source"]) == 1) {
                    // Direct
                    $rActualStatus = 3;
                } else if ($rRow["pid"]) {
                    if ($rRow["to_analyze"] == 1) {
                        $rActualStatus = 2; // Encoding
                    } else if ($rRow["stream_status"] == 1) {
                        $rActualStatus = 4; // Down
                    } else {
                        $rActualStatus = 1; // Encoded
                    }
                } else {
                    // Not Encoded
                    $rActualStatus = 0;
                }
                $rReturn["data"][] = array($rRow["id"], $rRow["stream_display_name"], $rRow["title"], $rVODStatusArray[$rActualStatus]);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "user_activity") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["reseller_client_connection_logs"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "connection_logs"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`user_activity`.`activity_id`", "`users`.`username`", "`streams`.`stream_display_name`", "`streaming_servers`.`server_name`", "`user_activity`.`date_start`", "`user_activity`.`date_end`", "`user_activity`.`user_ip`", "`user_activity`.`geoip_country_code`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if ($rPermissions["is_reseller"]) {
        $rWhere[] = "`users`.`member_id` IN (" . join(",", array_keys(getRegisteredUsers($rUserInfo["id"]))) . ")";
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`user_activity`.`user_agent` LIKE '%{$rSearch}%' OR `user_activity`.`user_agent` LIKE '%{$rSearch}%' OR `user_activity`.`user_ip` LIKE '%{$rSearch}%' OR `user_activity`.`container` LIKE '%{$rSearch}%' OR FROM_UNIXTIME(`user_activity`.`date_start`) LIKE '%{$rSearch}%' OR FROM_UNIXTIME(`user_activity`.`date_end`) LIKE '%{$rSearch}%' OR `user_activity`.`geoip_country_code` LIKE '%{$rSearch}%' OR `users`.`username` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `streaming_servers`.`server_name` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["range"]) > 0) {
        $rStartTime = substr($_GET["range"], 0, 10);
        $rEndTime = substr($_GET["range"], strlen($_GET["range"]) - 10, 10);
        if (!$rStartTime = strtotime($rStartTime . " 00:00:00")) {
            $rStartTime = null;
        }
        if (!$rEndTime = strtotime($rEndTime . " 23:59:59")) {
            $rEndTime = null;
        }
        if (($rStartTime) && ($rEndTime)) {
            $rWhere[] = "(`user_activity`.`date_start` >= " . $rStartTime . " AND `user_activity`.`date_end` <= " . $rEndTime . ")";
        }
    }
    if (strlen($_GET["server"]) > 0) {
        $rWhere[] = "`user_activity`.`server_id` = " . intval($_GET["server"]);
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `user_activity` LEFT JOIN `users` ON `user_activity`.`user_id` = `users`.`id` LEFT JOIN `streams` ON `user_activity`.`stream_id` = `streams`.`id` LEFT JOIN `streaming_servers` ON `user_activity`.`server_id` = `streaming_servers`.`id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT SUBSTR(FROM_BASE64(mac), 1, 18) mag, `users`.`is_restreamer`, SUBSTR(`user_activity`.`isp`, 1, 47) isp, `user_activity`.`activity_id`, `user_activity`.`user_id`, `user_activity`.`stream_id`, `user_activity`.`server_id`, SUBSTR(`user_activity`.`user_agent`, 1, 22) user_agent, `user_activity`.`user_ip`, `user_activity`.`date_start`, `user_activity`.`date_end`, `user_activity`.`container`, `user_activity`.`geoip_country_code`, SUBSTR(`users`.`username`, 1, 18) username, SUBSTR(`streams`.`stream_display_name`, 1, 25) stream_display_name, `streams`.`type`, SUBSTR(`streaming_servers`.`server_name`, 1, 18) server_name, (`user_activity`.`date_end` - `user_activity`.`date_start`) total_time FROM `user_activity`
INNER JOIN `users` ON `user_activity`.`user_id` = `users`.`id`
LEFT JOIN `mag_devices` ON `user_activity`.`user_id` = `mag_devices`.`user_id`
LEFT JOIN `streams` ON `user_activity`.`stream_id` = `streams`.`id`
LEFT JOIN `streaming_servers` ON `user_activity`.`server_id` = `streaming_servers`.`id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                if ($rPermissions["is_admin"]) {
                    if (hasPermissions("adv", "edit_user")) {
                        if ($rRow["mag"] == NULL) {
                            $rUsername = "<a href='./user.php?id=" . $rRow["user_id"] . "'>" . $rRow["username"] . "</a>";
                        } else {
                            $rUsername = "<a href='./user.php?id=" . $rRow["user_id"] . "'>" . $rRow["mag"] . "</a>";
                            ;
                        }
                    }
                } else {
                    if ($rRow["mag"] == NULL) {
                        $rUsername = "<a href='./user_reseller.php?id=" . $rRow["user_id"] . "'>" . $rRow["username"] . "</a>";
                    } else {
                        $rUsername = "<a href='./user_reseller_edit.php?id=" . $rRow["user_id"] . "'>" . $rRow["mag"] . "</a>";
                        ;
                    }
                }
                $rChannel = $rRow["stream_display_name"];
                if ($rPermissions["is_admin"]) {
                    $rServer = $rRow["server_name"];
                } else {
                    $rServer = "Server #" . $rRow["server_id"];
                }
                if ($rRow["user_ip"]) {
                    $rIP = "<a target='_blank' href='https://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $rRow["user_ip"] . "'>" . $rRow["user_ip"] . "</a>";
                } else {
                    $rIP = "";
                }
                if (strlen($rRow["geoip_country_code"]) > 0) {
                    $rGeoCountry = $rRow["geoip_country_code"];
                } else {
                    $rGeoCountry = "";
                }
                if ($rRow["date_start"]) {
                    $rStart = date("Y-m-d H:i", $rRow["date_start"]);
                } else {
                    $rStart = "";
                }
                if ($rRow["date_end"]) {
                    $rStop = date("Y-m-d H:i", $rRow["date_end"]);
                } else {
                    $rStop = "";
                }
                $rReturn["data"][] = array($rRow["activity_id"], $rUsername, $rChannel, $rServer, $rRow["isp"], $rStart, $rStop, $rIP, $rGeoCountry);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "live_connections") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["reseller_client_connection_logs"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "live_connections"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`lines_live`.`activity_id`", "`lines_live`.`divergence`", "`users`.`username`", "`streams`.`stream_display_name`", "`streaming_servers`.`server_name`", "`lines_live`.`date_start`", "`lines_live`.`user_ip`", "`lines_live`.`geoip_country_code`", "`lines_live`.`isp`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if ($rPermissions["is_reseller"]) {
        $rWhere[] = "`users`.`member_id` IN (" . join(",", array_keys(getRegisteredUsers($rUserInfo["id"]))) . ")";
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`lines_live`.`user_agent` LIKE '%{$rSearch}%' OR `lines_live`.`user_agent` LIKE '%{$rSearch}%' OR `lines_live`.`user_ip` LIKE '%{$rSearch}%' OR `lines_live`.`container` LIKE '%{$rSearch}%' OR FROM_UNIXTIME(`lines_live`.`date_start`) LIKE '%{$rSearch}%' OR `lines_live`.`geoip_country_code` LIKE '%{$rSearch}%' OR `users`.`username` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `streaming_servers`.`server_name` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["server_id"]) > 0) {
        $rWhere[] = "`lines_live`.`server_id` = " . intval($_GET["server_id"]);
    }
    if (strlen($_GET["stream_id"]) > 0) {
        $rWhere[] = "`lines_live`.`stream_id` = " . intval($_GET["stream_id"]);
    }
    if (strlen($_GET["user_id"]) > 0) {
        $rWhere[] = "`lines_live`.`user_id` = " . intval($_GET["user_id"]);
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `lines_live` LEFT JOIN `users` ON `lines_live`.`user_id` = `users`.`id` LEFT JOIN `streams` ON `lines_live`.`stream_id` = `streams`.`id` LEFT JOIN `streaming_servers` ON `lines_live`.`server_id` = `streaming_servers`.`id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT FROM_BASE64(mac) mag, SUBSTR(`lines_live`.`isp`, 1, 47) isp, `lines_live`.`activity_id`, `lines_live`.`divergence`, `lines_live`.`user_id`, `lines_live`.`stream_id`, `lines_live`.`server_id`, SUBSTR(`lines_live`.`user_agent`, 1, 35) user_agent, `lines_live`.`user_ip`, `lines_live`.`container`, `lines_live`.`pid`, `lines_live`.`date_start`, `lines_live`.`geoip_country_code`, `users`.`username`, SUBSTR(`streams`.`stream_display_name`, 1, 30) stream_display_name, `streams`.`type`, SUBSTR(`streaming_servers`.`server_name`, 1, 25) server_name FROM `lines_live`
INNER JOIN `users` ON `lines_live`.`user_id` = `users`.`id`
LEFT JOIN `mag_devices` ON `lines_live`.`user_id` = `mag_devices`.`user_id`
LEFT JOIN `streams` ON `lines_live`.`stream_id` = `streams`.`id`
LEFT JOIN `streaming_servers` ON `lines_live`.`server_id` = `streaming_servers`.`id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                if ($rRow['divergence'] <= 50) {
                    $rDivergence = '<button type="button" class="btn btn-outline-success btn-rounded btn-xs waves-effect waves-light">' . intval(100 - $rRow['divergence']) . '%</button>';
                    ;
                } else if ($rRow['divergence'] <= 80) {
                    $rDivergence = '<button type="button" class="btn btn-outline-warning btn-rounded btn-xs waves-effect waves-light">' . intval(100 - $rRow['divergence']) . '%</button>';
                } else {
                    $rDivergence = '<button type="button" class="btn btn-outline-danger btn-rounded btn-xs waves-effect waves-light">' . intval(100 - $rRow['divergence']) . '%</button>';
                }
                if ($rPermissions["is_admin"]) {
                    if (hasPermissions("adv", "edit_user")) {
                        if ($rRow["mag"] == NULL) {
                            $rUsername = "<a href='./user.php?id=" . $rRow["user_id"] . "'>" . $rRow["username"] . "</a>";
                        } else {
                            $rUsername = "<a href='./user.php?id=" . $rRow["user_id"] . "'>" . $rRow["mag"] . "</a>";
                            ;
                        }
                    }
                } else {
                    if ($rRow["mag"] == NULL) {
                        $rUsername = "<a href='./user_reseller.php?id=" . $rRow["user_id"] . "'>" . $rRow["username"] . "</a>";
                    } else {
                        $rUsername = "<a href='./user_reseller.php?id=" . $rRow["user_id"] . "'>" . $rRow["mag"] . "</a>";
                        ;
                    }
                }
                $rChannel = $rRow["stream_display_name"];
                if ($rPermissions["is_admin"]) {
                    $rServer = $rRow["server_name"];
                } else {
                    $rServer = "Server #" . $rRow["server_id"];
                }
                if (0 < strlen($rRow['geoip_country_code'])) {
                    $rGeoCountry = "<img loading='lazy' src='assets/images/countries/" . strtolower($rRow['geoip_country_code']) . ".png'></img> &nbsp;";
                } else {
                    $rGeoCountry = '';
                }
                if ($rRow["user_ip"]) {
                    $rIP = $rGeoCountry . "<a target='_blank' href='https://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $rRow["user_ip"] . "'>" . $rRow["user_ip"] . "</a>";
                } else {
                    $rIP = "";
                }
                $rPlayer = trim(explode('(', $rRow['user_agent'])[0]);
                $rDuration = intval(time()) - intval($rRow['date_start']);
                $rColour = 'success';
                if ($rRow['hls_end']) {
                    $rDuration = "<button type='button' class='btn btn-secondary btn-xs waves-effect waves-light btn-fixed'>CLOSED</button>";
                } else {
                    if (86400 <= $rDuration) {
                        $rDuration = sprintf('%02dd %02dh', $rDuration / 86400, ($rDuration / 3600) % 24);
                        $rColour = 'danger';
                    } else {
                        if (3600 <= $rDuration) {
                            if (14400 < $rDuration) {
                                $rColour = 'warning';
                            } else if (43200 < $rDuration) {
                                $rColour = 'danger';
                            }
                            $rDuration = sprintf('%02dh %02dm', $rDuration / 3600, ($rDuration / 60) % 60);
                        } else {
                            $rDuration = sprintf('%02dm %02ds', ($rDuration / 60) % 60, $rDuration % 60);
                        }
                    }
                    if (!$rRow['is_restreamer']) {
                    } else {
                        $rColour = 'success';
                    }
                    $rDuration = "<button type='button' class='btn btn-" . $rColour . " btn-xs waves-effect waves-light btn-fixed'>" . $rDuration . '</button>';
                }
                if ($rRow['is_restreamer'] == 1) {
                    $rRestreamer = '<i class="text-info fas fa-square"></i>';
                } else {
                    $rRestreamer = '<i class="text-secondary fas fa-square"></i>';
                }
                if ($rRow["isp"]) {
                    $rnisp2 = "<span>" . $rRow["isp"] . "</span>";
                } else {
                    $rnisp2 = "no isp";
                }
                if (isset($_GET["fingerprint"])) {
                    $rButtons = '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Kill Connection" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["pid"] . ', \'kill\', ' . $rRow["activity_id"] . ');"><i class="fas fa-hammer"></i></button>';
                } else {
                    $rButtons = '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Kill Connection" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["pid"] . ', \'kill\');"><i class="fas fa-hammer"></i></button>';
                }
                $rReturn['data'][] = array($rRow['activity_id'], $rDivergence, $rUsername, $rChannel, $rServer, $rPlayer, $rRow['isp'], $rIP, $rDuration, strtoupper($rRow['container']), $rRestreamer, $rButtons);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "stream_list") {
    if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "import_streams")) && (!hasPermissions("adv", "mass_delete")))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`stream_categories`.`category_name`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (isset($_GET["include_channels"])) {
        $rWhere[] = "`streams`.`type` IN (1,3)";
    } else {
        $rWhere[] = "`streams`.`type` = 1";
    }
    if (strlen($_GET["category"]) > 0) {
        $rWhere[] = "`streams`.`category_id` = " . intval($_GET["category"]);
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "(`streams_servers`.`monitor_pid` > 0 AND `streams_servers`.`pid` > 0)";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0)";
        } else if ($_GET["filter"] == 3) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`monitor_pid` IS NULL OR `streams_servers`.`monitor_pid` <= 0) AND `streams_servers`.`on_demand` = 0)";
        } else if ($_GET["filter"] == 4) {
            $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` = 0)";
        } else if ($_GET["filter"] == 5) {
            $rWhere[] = "`streams_servers`.`on_demand` = 1";
        } else if ($_GET["filter"] == 6) {
            $rWhere[] = "`streams`.`direct_source` = 1";
        }
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%')";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id`  {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams`.`stream_display_name`, `stream_categories`.`category_name` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rReturn["data"][] = array($rRow["id"], $rRow["stream_display_name"], $rRow["category_name"], $rStatus);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "movie_list") {
    if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "import_movies")) && (!hasPermissions("adv", "mass_delete")))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`stream_categories`.`category_name`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` = 2";
    if (strlen($_GET["category"]) > 0) {
        $rWhere[] = "`streams`.`category_id` = " . intval($_GET["category"]);
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 0 AND `streams_servers`.`stream_status` <> 1)";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 1 AND `streams_servers`.`stream_status` <> 1)";
        } else if ($_GET["filter"] == 3) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`stream_status` = 1)";
        } else if ($_GET["filter"] == 4) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 1)";
        } else if ($_GET["filter"] == 5) {
            $rWhere[] = "`streams`.`direct_source` = 1";
        } else if ($_GET["filter"] == 6) {
            $rWhere[] = "(`streams`.`movie_properties` IS NULL OR `streams`.`movie_properties` = '' OR `streams`.`movie_properties` = '[]' OR `streams`.`movie_properties` = '{}' OR `streams`.`movie_properties` LIKE '%tmdb_id\":\"\"%')";
        }
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams`.`stream_display_name`, `stream_categories`.`category_name`, `streams`.`direct_source`, `streams_servers`.`to_analyze`, `streams_servers`.`pid` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rActualStatus = 0;
                if (intval($rRow["direct_source"]) == 1) {
                    // Direct
                    $rActualStatus = 3;
                } else if ($rRow["pid"]) {
                    if ($rRow["to_analyze"] == 1) {
                        $rActualStatus = 2; // Encoding
                    } else if ($rRow["stream_status"] == 1) {
                        $rActualStatus = 4; // Down
                    } else {
                        $rActualStatus = 1; // Encoded
                    }
                } else {
                    // Not Encoded
                    $rActualStatus = 0;
                }
                $rReturn["data"][] = array($rRow["id"], $rRow["stream_display_name"], $rRow["category_name"], $rVODStatusArray[$rActualStatus]);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "radio_list") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "mass_delete"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`stream_categories`.`category_name`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` = 4";
    if (strlen($_GET["category"]) > 0) {
        $rWhere[] = "`streams`.`category_id` = " . intval($_GET["category"]);
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "(`streams_servers`.`monitor_pid` > 0 AND `streams_servers`.`pid` > 0)";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 0)";
        } else if ($_GET["filter"] == 3) {
            $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`monitor_pid` IS NULL OR `streams_servers`.`monitor_pid` <= 0) AND `streams_servers`.`on_demand` = 0)";
        } else if ($_GET["filter"] == 4) {
            $rWhere[] = "((`streams_servers`.`monitor_pid` IS NOT NULL AND `streams_servers`.`monitor_pid` > 0) AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` = 0)";
        } else if ($_GET["filter"] == 5) {
            $rWhere[] = "`streams_servers`.`on_demand` = 1";
        } else if ($_GET["filter"] == 6) {
            $rWhere[] = "`streams`.`direct_source` = 1";
        }
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%')";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id`  {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams`.`stream_display_name`, `stream_categories`.`category_name` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rReturn["data"][] = array($rRow["id"], $rRow["stream_display_name"], $rRow["category_name"], $rStatus);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "series_list") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "mass_delete"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`series`.`id`", "`series`.`title`", "`stream_categories`.`category_name`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (strlen($_GET["category"]) > 0) {
        if ($_GET["category"] == -1) {
            $rWhere[] = "(`series`.`tmdb_id` = 0 OR `series`.`tmdb_id` IS NULL)";
        } else {
            $rWhere[] = "`series`.`category_id` = " . intval($_GET["category"]);
        }
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`series`.`id` LIKE '%{$rSearch}%' OR `series`.`title` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%')";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `series` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `series`.`category_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `series`.`id`, `series`.`title`, `stream_categories`.`category_name` FROM `series` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `series`.`category_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rReturn["data"][] = array($rRow["id"], $rRow["title"], $rRow["category_name"]);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "credits_log") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "credits_log"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`credits_log`.`id`", "`owner_username`", "`target_username`", "`credits_log`.`amount`", "`credits_log`.`reason`", "`date`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`target`.`username` LIKE '%{$rSearch}%' OR `owner`.`username` LIKE '%{$rSearch}%' OR FROM_UNIXTIME(`date`) LIKE '%{$rSearch}%' OR `credits_log`.`amount` LIKE '%{$rSearch}%' OR `credits_log`.`reason` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["range"]) > 0) {
        $rStartTime = substr($_GET["range"], 0, 10);
        $rEndTime = substr($_GET["range"], strlen($_GET["range"]) - 10, 10);
        if (!$rStartTime = strtotime($rStartTime . " 00:00:00")) {
            $rStartTime = null;
        }
        if (!$rEndTime = strtotime($rEndTime . " 23:59:59")) {
            $rEndTime = null;
        }
        if (($rStartTime) && ($rEndTime)) {
            $rWhere[] = "(`credits_log`.`date` >= " . $rStartTime . " AND `credits_log`.`date` <= " . $rEndTime . ")";
        }
    }
    if (strlen($_GET["reseller"]) > 0) {
        $rWhere[] = "(`credits_log`.`target_id` = " . intval($_GET["reseller"]) . " OR `credits_log`.`admin_id` = " . intval($_GET["reseller"]) . ")";
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `credits_log` LEFT JOIN `reg_users` AS `target` ON `target`.`id` = `credits_log`.`target_id` LEFT JOIN `reg_users` AS `owner` ON `owner`.`id` = `credits_log`.`admin_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `credits_log`.`id`, `credits_log`.`target_id`, `credits_log`.`admin_id`, `target`.`username` AS `target_username`, `owner`.`username` AS `owner_username`, `amount`, FROM_UNIXTIME(`date`) AS `date`, `credits_log`.`reason` FROM `credits_log` LEFT JOIN `reg_users` AS `target` ON `target`.`id` = `credits_log`.`target_id` LEFT JOIN `reg_users` AS `owner` ON `owner`.`id` = `credits_log`.`admin_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                if (hasPermissions("adv", "edit_reguser")) {
                    $rOwner = "<a href='./reg_user.php?id=" . $rRow["admin_id"] . "'>" . $rRow["owner_username"] . "</a>";
                    $rTarget = "<a href='./reg_user.php?id=" . $rRow["target_id"] . "'>" . $rRow["target_username"] . "</a>";
                } else {
                    $rOwner = $rRow["owner_username"];
                    $rTarget = $rRow["target_username"];
                }
                $rReturn["data"][] = array($rRow["id"], $rOwner, $rTarget, $rRow["amount"], $rRow["reason"], $rRow["date"]);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "user_ips") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "connection_logs"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`user_activity`.`user_id`", "`users`.`username`", "`ip_count`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array("`date_start` >= (UNIX_TIMESTAMP()-" . intval($_GET["range"]) . ")");
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`users`.`username` LIKE '%{$rSearch}%' OR `user_activity`.`user_id` LIKE '%{$rSearch}%' OR `user_activity`.`user_ip` LIKE '%{$rSearch}%')";
    }
    $rWhereString = "WHERE " . join(" AND ", $rWhere);
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(DISTINCT(`user_activity`.`user_id`)) AS `count` FROM `user_activity` LEFT JOIN `users` ON `users`.`id` = `user_activity`.`user_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `user_activity`.`user_id`, COUNT(DISTINCT(`user_activity`.`user_ip`)) AS `ip_count`, `users`.`username` FROM `user_activity` LEFT JOIN `users` ON `users`.`id` = `user_activity`.`user_id` {$rWhereString} GROUP BY `user_activity`.`user_id` {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rDates = date("Y-m-d H:i", time() - intval($_GET["range"])) . " - " . date("Y-m-d H:i", time());
                $rButtons = '<a href="./user_activity.php?search=' . $rRow["username"] . '&dates=' . $rDates . '"><button type="button" class="btn btn-light waves-effect waves-light btn-xs">View Logs</button></a>';
                $rReturn["data"][] = array("<a href='./user.php?id=" . $rRow["user_id"] . "'>" . $rRow["user_id"] . "</a>", $rRow["username"], $rRow["ip_count"], $rButtons);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "client_logs") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "client_request_log"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`client_logs`.`id`", "`users`.`username`", "`streams`.`stream_display_name`", "`client_logs`,`client_status`", "`client_logs`.`extra_data`", "`client_logs`.`ip`", "`client_logs`.`date`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`client_logs`.`client_status` LIKE '%{$rSearch}%' OR `client_logs`.`query_string` LIKE '%{$rSearch}%' OR FROM_UNIXTIME(`date`) LIKE '%{$rSearch}%' OR `client_logs`.`extra_data` LIKE '%{$rSearch}%' OR `client_logs`.`ip` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `users`.`username` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["range"]) > 0) {
        $rStartTime = substr($_GET["range"], 0, 10);
        $rEndTime = substr($_GET["range"], strlen($_GET["range"]) - 10, 10);
        if (!$rStartTime = strtotime($rStartTime . " 00:00:00")) {
            $rStartTime = null;
        }
        if (!$rEndTime = strtotime($rEndTime . " 23:59:59")) {
            $rEndTime = null;
        }
        if (($rStartTime) && ($rEndTime)) {
            $rWhere[] = "(`client_logs`.`date` >= " . $rStartTime . " AND `client_logs`.`date` <= " . $rEndTime . ")";
        }
    }
    if (strlen($_GET["filter"]) > 0) {
        $rWhere[] = "`client_logs`.`client_status` = '" . $_GET["filter"] . "'";
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `client_logs` LEFT JOIN `streams` ON `streams`.`id` = `client_logs`.`stream_id` LEFT JOIN `users` ON `users`.`id` = `client_logs`.`user_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `client_logs`.`id`, `client_logs`.`user_id`, `client_logs`.`stream_id`, `streams`.`stream_display_name`, `users`.`username`, `client_logs`.`client_status`, `client_logs`.`query_string`, `client_logs`.`user_agent`, `client_logs`.`ip`, FROM_UNIXTIME(`client_logs`.`date`) AS `date` FROM `client_logs` LEFT JOIN `streams` ON `streams`.`id` = `client_logs`.`stream_id` LEFT JOIN `users` ON `users`.`id` = `client_logs`.`user_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                if (hasPermissions("adv", "edit_user")) {
                    $rUsername = "<a href='./user.php?id=" . $rRow["user_id"] . "'>" . $rRow["username"] . "</a>";
                } else {
                    $rUsername = $rRow["username"];
                }
                $rReturn["data"][] = array($rRow["id"], $rUsername, $rRow["stream_display_name"], $clientFilters[$rRow['client_status']], $rRow['user_agent'], "<a target='_blank' href='https://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $rRow["ip"] . "'>" . $rRow["ip"] . "</a>", $rRow["date"]);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "reg_user_logs") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "reg_userlog"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`reg_userlog`.`id`", "`reg_users`.`username`", "`reg_userlog`.`username`", "`reg_userlog`.`type`", "`reg_userlog`.`date`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`reg_userlog`.`username` LIKE '%{$rSearch}%' OR `reg_userlog`.`type` LIKE '%{$rSearch}%' OR FROM_UNIXTIME(`date`) LIKE '%{$rSearch}%' OR `reg_users`.`username` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["range"]) > 0) {
        $rStartTime = substr($_GET["range"], 0, 10);
        $rEndTime = substr($_GET["range"], strlen($_GET["range"]) - 10, 10);
        if (!$rStartTime = strtotime($rStartTime . " 00:00:00")) {
            $rStartTime = null;
        }
        if (!$rEndTime = strtotime($rEndTime . " 23:59:59")) {
            $rEndTime = null;
        }
        if (($rStartTime) && ($rEndTime)) {
            $rWhere[] = "(`reg_userlog`.`date` >= " . $rStartTime . " AND `reg_userlog`.`date` <= " . $rEndTime . ")";
        }
    }
    if (strlen($_GET["reseller"]) > 0) {
        $rWhere[] = "`reg_userlog`.`owner` = '" . intval($_GET["reseller"]) . "'";
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `reg_userlog` LEFT JOIN `reg_users` ON `reg_users`.`id` = `reg_userlog`.`owner` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `reg_userlog`.`id`, `reg_userlog`.`owner` as `owner_id`, `reg_users`.`username` AS `owner`, `reg_userlog`.`username`, `reg_userlog`.`type`, FROM_UNIXTIME(`reg_userlog`.`date`) AS `date` FROM `reg_userlog` LEFT JOIN `reg_users` ON `reg_users`.`id` = `reg_userlog`.`owner` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                if (hasPermissions("adv", "edit_reguser")) {
                    $rOwner = "<a href='./reg_user.php?id=" . $rRow["owner_id"] . "'>" . $rRow["owner"] . "</a>";
                } else {
                    $rOwner = $rRow["owner"];
                }
                $rReturn["data"][] = array($rRow["id"], $rOwner, $rRow["username"], strip_tags($rRow["type"]), $rRow["date"]);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "stream_logs") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "stream_errors"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`stream_logs`.`id`", "`streams`.`stream_display_name`", "`streaming_servers`.`server_name`", "`stream_logs`.`error`", "`stream_logs`.`date`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `streaming_servers`.`server_name` LIKE '%{$rSearch}%' OR FROM_UNIXTIME(`date`) LIKE '%{$rSearch}%' OR `stream_logs`.`error` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["range"]) > 0) {
        $rStartTime = substr($_GET["range"], 0, 10);
        $rEndTime = substr($_GET["range"], strlen($_GET["range"]) - 10, 10);
        if (!$rStartTime = strtotime($rStartTime . " 00:00:00")) {
            $rStartTime = null;
        }
        if (!$rEndTime = strtotime($rEndTime . " 23:59:59")) {
            $rEndTime = null;
        }
        if (($rStartTime) && ($rEndTime)) {
            $rWhere[] = "(`stream_logs`.`date` >= " . $rStartTime . " AND `stream_logs`.`date` <= " . $rEndTime . ")";
        }
    }
    if (strlen($_GET["server"]) > 0) {
        $rWhere[] = "`stream_logs`.`server_id` = '" . intval($_GET["server"]) . "'";
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `stream_logs` LEFT JOIN `streams` ON `streams`.`id` = `stream_logs`.`stream_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `stream_logs`.`server_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `stream_logs`.`id`, `stream_logs`.`stream_id`, `stream_logs`.`server_id`, `streams`.`stream_display_name`, `streaming_servers`.`server_name`, `stream_logs`.`error`, FROM_UNIXTIME(`stream_logs`.`date`) AS `date` FROM `stream_logs` LEFT JOIN `streams` ON `streams`.`id` = `stream_logs`.`stream_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `stream_logs`.`server_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rReturn["data"][] = array($rRow["id"], $rRow["stream_display_name"], $rRow["server_name"], $rRow["error"], $rRow["date"]);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "stream_unique") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "fingerprint"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`stream_categories`.`category_name`", "`active_count`", null);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` = 1";
    $rWhere[] = "(SELECT COUNT(*) FROM `lines_live` WHERE `container` = 'ts' AND `lines_live`.`stream_id` = `streams`.`id`) > 0";
    if (strlen($_GET["category"]) > 0) {
        $rWhere[] = "`streams`.`category_id` = " . intval($_GET["category"]);
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%')";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams`.`stream_display_name`, `stream_categories`.`category_name`, (SELECT COUNT(*) FROM `lines_live` WHERE `container` = 'ts' AND `lines_live`.`stream_id` = `streams`.`id`) AS `active_count` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rReturn["data"][] = array($rRow["id"], $rRow["stream_display_name"], $rRow["category_name"], $rRow["active_count"], "<button type='button' class='btn waves-effect waves-light btn-xs' href='javascript:void(0);' onClick='selectFingerprint(" . $rRow["id"] . ")'><i class='mdi mdi-fingerprint'></i></button>");
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "reg_users") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["create_sub_resellers"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "mng_regusers"))) {
        exit;
    }
    $rAvailableMembers = array_keys(getRegisteredUsers($rUserInfo["id"]));
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`reg_users`.`id`", "`reg_users`.`username`", "`r`.`username`", "`reg_users`.`ip`", "`member_groups`.`group_name`", "`reg_users`.`status`", "`reg_users`.`credits`", "`user_count`", "`reg_users`.`last_login`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if ($rPermissions["is_reseller"]) {
        $rWhere[] = "`reg_users`.`owner_id` IN (" . join(",", $rAvailableMembers) . ")";
    }
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`reg_users`.`id` LIKE '%{$rSearch}%' OR `reg_users`.`username` LIKE '%{$rSearch}%' OR `reg_users`.`notes` LIKE '%{$rSearch}%' OR `r`.`username` LIKE '%{$rSearch}%' OR from_unixtime(`reg_users`.`date_registered`) LIKE '%{$rSearch}%' OR from_unixtime(`reg_users`.`last_login`) LIKE '%{$rSearch}%' OR `reg_users`.`email` LIKE '%{$rSearch}%' OR `reg_users`.`ip` LIKE '%{$rSearch}%' OR `member_groups`.`group_name` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["filter"]) > 0) {
        if ($_GET["filter"] == 1) {
            $rWhere[] = "`reg_users`.`status` = 1";
        } else if ($_GET["filter"] == 2) {
            $rWhere[] = "`reg_users`.`status` = 0";
        }
    }
    if (strlen($_GET["reseller"]) > 0) {
        $rWhere[] = "`reg_users`.`owner_id` = " . intval($_GET["reseller"]);
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `reg_users` LEFT JOIN `member_groups` ON `member_groups`.`group_id` = `reg_users`.`member_group_id` LEFT JOIN `reg_users` AS `r` on `r`.`id` = `reg_users`.`owner_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `reg_users`.`id`, `reg_users`.`status`, `reg_users`.`notes`, `reg_users`.`credits`, `reg_users`.`username`, `reg_users`.`email`, `reg_users`.`ip`, FROM_UNIXTIME(`reg_users`.`date_registered`) AS `date_registered`, FROM_UNIXTIME(`reg_users`.`last_login`) AS `last_login`, `r`.`username` as `owner_username`, `member_groups`.`group_name`, `reg_users`.`verified`, `reg_users`.`status`, (SELECT COUNT(`id`) FROM `users` WHERE `member_id` = `reg_users`.`id`) AS `user_count` FROM `reg_users` LEFT JOIN `member_groups` ON `member_groups`.`group_id` = `reg_users`.`member_group_id` LEFT JOIN `reg_users` AS `r` on `r`.`id` = `reg_users`.`owner_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                if ($rRow["status"] == 1) {
                    $rStatus = '<i class="text-success fas fa-circle"></i>';
                } else {
                    $rStatus = '<i class="text-danger fas fa-circle"></i>';
                }
                if (!$rRow["last_login"]) {
                    $rRow["last_login"] = "NEVER";
                }
                $rButtons = '<div class="btn-group">';
                if (strlen($rRow["notes"]) > 0) {
                    $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rRow["notes"] . '"><i class="mdi mdi-note"></i></button>';
                } else {
                    $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                }
                if ($rPermissions["is_admin"]) {
                    if (hasPermissions("adv", "edit_reguser")) {
                        $rButtons .= '<a href="./reg_user.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
						';
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Reset Two Factor Auth" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'reset\');"><i class="mdi mdi-two-factor-authentication"></i></button>
						';
                    }
                } else {
                    $rButtons .= '<a href="./credits_add.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Add Credits" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="fe-dollar-sign"></i></button></a>';
                    $rButtons .= '<a href="./subreseller.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
                }
                if (($rPermissions["is_reseller"]) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_reguser")))) {
                    if ($rRow["status"] == 1) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Disable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'disable\');"><i class="mdi mdi-lock-outline"></i></button>
						';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'enable\');"><i class="mdi mdi-lock"></i></button>
						';
                    }
                }
                if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) or (($rPermissions["is_admin"]) && (hasPermissions("adv", "edit_reguser")))) {
                    $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                }
                $rButtons .= '</div>';
                $rReturn["data"][] = array($rRow["id"], $rRow["username"], $rRow["owner_username"], $rRow["ip"], $rRow["group_name"], $rStatus, $rRow["credits"], $rRow["user_count"], $rRow["last_login"], $rButtons);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "series") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "series")) && (!hasPermissions("adv", "mass_sedits"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`series`.`id`", "`series`.`title`", "`stream_categories`.`category_name`", "`latest_season`", "`episode_count`", "`series`.`releaseDate`", "`series`.`last_modified`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`series`.`id` LIKE '%{$rSearch}%' OR `series`.`title` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%' OR `series`.`releaseDate` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["category"]) > 0) {
        if ($_GET["category"] == -1) {
            $rWhere[] = "(`series`.`tmdb_id` = 0 OR `series`.`tmdb_id` IS NULL)";
        } else {
            $rWhere[] = "`series`.`category_id` = " . intval($_GET["category"]);
        }
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection . ", `series`.`id` ASC";
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `series` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `series`.`category_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `series`.`id`, `series`.`title`, `stream_categories`.`category_name`, `series`.`releaseDate`, `series`.`last_modified`, (SELECT MAX(`season_num`) FROM `series_episodes` WHERE `series_id` = `series`.`id`) AS `latest_season`, (SELECT COUNT(*) FROM `series_episodes` WHERE `series_id` = `series`.`id`) AS `episode_count` FROM `series` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `series`.`category_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rButtons = '<div class="btn-group">';
                if (hasPermissions("adv", "add_episode")) {
                    $rButtons .= '<a href="./episode.php?sid=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Add Episode(s)" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-plus-circle-outline"></i></button></a>
					';
                }
                if (hasPermissions("adv", "episodes")) {
                    $rButtons .= '<a href="./episodes.php?series=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="View Episodes" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-eye"></i></button></a>
					';
                }
                if (hasPermissions("adv", "edit_series")) {
                    $rButtons .= '<a href="./series_order.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Reorder Episodes" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-format-line-spacing"></i></button></a>
					<a href="./serie.php?id=' . $rRow["id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
					<button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                }
                $rButtons .= '</div>';
                if (!$rRow["latest_season"]) {
                    $rRow["latest_season"] = 0;
                }
                if ($rRow["last_modified"] == 0) {
                    $rRow["last_modified"] = "Never";
                } else {
                    $rRow["last_modified"] = date("Y-m-d H:i", $rRow["last_modified"]);
                }
                if ($rPermissions["is_admin"]) {
                    $rReturn["data"][] = array($rRow["id"], $rRow["title"], $rRow["category_name"], $rRow["latest_season"], $rRow["episode_count"], $rRow["releaseDate"], $rRow["last_modified"], $rButtons);
                } else {
                    $rReturn["data"][] = array($rRow["id"], $rRow["title"], $rRow["category_name"], $rRow["latest_season"], $rRow["episode_count"], $rRow["releaseDate"]);
                }
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "episodes") {
    if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
        exit;
    }
    if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "episodes")) && (!hasPermissions("adv", "mass_sedits"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`streams`.`id`", "`streams`.`stream_display_name`", "`series`.`title`", "`streaming_servers`.`server_name`", "`clients`", "`streams_servers`.`stream_started`", false, false, "`streams_servers`.`bitrate`");
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    $rWhere[] = "`streams`.`type` = 5";
    if (isset($_GET["stream_id"])) {
        $rWhere[] = "`streams`.`id` = " . intval($_GET["stream_id"]);
        $rOrderBy = "ORDER BY `streams_servers`.`server_stream_id` ASC";
    } else {
        if (strlen($_GET["search"]["value"]) > 0) {
            $rSearch = $_GET["search"]["value"];
            $rWhere[] = "(`streams`.`id` LIKE '%{$rSearch}%' OR `streams`.`stream_display_name` LIKE '%{$rSearch}%' OR `series`.`title` LIKE '%{$rSearch}%' OR `streams`.`notes` LIKE '%{$rSearch}%' OR `streams_servers`.`current_source` LIKE '%{$rSearch}%' OR `stream_categories`.`category_name` LIKE '%{$rSearch}%' OR `streaming_servers`.`server_name` LIKE '%{$rSearch}%')";
        }
        if (strlen($_GET["filter"]) > 0) {
            if ($_GET["filter"] == 1) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 0 AND `streams_servers`.`stream_status` <> 1)";
            } else if ($_GET["filter"] == 2) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 1 AND `streams_servers`.`stream_status` <> 1)";
            } else if ($_GET["filter"] == 3) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND `streams_servers`.`stream_status` = 1)";
            } else if ($_GET["filter"] == 4) {
                $rWhere[] = "(`streams`.`direct_source` = 0 AND (`streams_servers`.`pid` IS NULL OR `streams_servers`.`pid` <= 0) AND `streams_servers`.`stream_status` <> 1)";
            } else if ($_GET["filter"] == 5) {
                $rWhere[] = "`streams`.`direct_source` = 1";
            }
        }
        if (strlen($_GET["series"]) > 0) {
            $rWhere[] = "`series`.`id` = " . intval($_GET["series"]);
        }
        if (strlen($_GET["server"]) > 0) {
            $rWhere[] = "`streams_servers`.`server_id` = " . intval($_GET["server"]);
        }
        if ($rOrder[$rOrderRow]) {
            $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
            $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
        }
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` LEFT JOIN `series_episodes` ON `series_episodes`.`stream_id` = `streams`.`id` LEFT JOIN `series` ON `series`.`id` = `series_episodes`.`series_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `streams`.`id`, `streams_servers`.`to_analyze`, `streams`.`target_container`, `streams`.`stream_display_name`, `streams_servers`.`server_id`, `streams`.`notes`, `streams`.`direct_source`, `streams_servers`.`pid`, `streams_servers`.`monitor_pid`, `streams_servers`.`stream_status`, `streams_servers`.`stream_started`, `streams_servers`.`stream_info`, `streams_servers`.`current_source`, `streams_servers`.`bitrate`, `streams_servers`.`progress_info`, `streams_servers`.`on_demand`, `stream_categories`.`category_name`, `streaming_servers`.`server_name`, (SELECT COUNT(*) FROM `lines_live` WHERE `lines_live`.`server_id` = `streams_servers`.`server_id` AND `lines_live`.`stream_id` = `streams`.`id`) AS `clients`, `series`.`title`, `series`.`id` AS `sid`, `series_episodes`.`season_num` FROM `streams` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `streams`.`id` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `streams_servers`.`server_id` LEFT JOIN `series_episodes` ON `series_episodes`.`stream_id` = `streams`.`id` LEFT JOIN `series` ON `series`.`id` = `series_episodes`.`series_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                // Format Rows
                $rSeriesName = $rRow["title"] . " - Season " . $rRow["season_num"];
                $rStreamName = "<b>" . $rRow['stream_display_name'] . "</b><br><span style='font-size:11px;'>{$rSeriesName}</span>";
                if ($rRow["server_name"]) {
                    if ($rPermissions["is_admin"]) {
                        $rServerName = $rRow["server_name"];
                    } else {
                        $rServerName = "Server #" . $rRow["server_id"];
                    }
                } else {
                    $rServerName = "No Server Selected";
                }
                $rUptime = 0;
                $rActualStatus = 0;
                if (intval($rRow["direct_source"]) == 1) {
                    // Direct
                    $rActualStatus = 3;
                } else if ($rRow["pid"]) {
                    if ($rRow["to_analyze"] == 1) {
                        $rActualStatus = 2; // Encoding
                    } else if ($rRow["stream_status"] == 1) {
                        $rActualStatus = 4; // Down
                    } else {
                        $rActualStatus = 1; // Encoded
                    }
                } else {
                    // Not Encoded
                    $rActualStatus = 0;
                }
                if (hasPermissions("adv", "live_connections")) {
                    $rClients = "<a href=\"./live_connections.php?stream_id=" . $rRow["id"] . "&server_id=" . $rRow["server_id"] . "\">" . $rRow["clients"] . "</a>";
                } else {
                    $rClients = $rRow["clients"];
                }
                if (!$rRow["server_id"]) {
                    $rRow["server_id"] = 0;
                }
                $rButtons = '<div class="btn-group">';
                if ($rPermissions["is_admin"]) {
                    if (strlen($rRow["notes"]) > 0) {
                        $rButtons .= '<button type="button" class="btn btn-light waves-effect waves-light btn-xs" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $rRow["notes"] . '"><i class="mdi mdi-note"></i></button>';
                    } else {
                        $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-note"></i></button>';
                    }
                }
                if (hasPermissions("adv", "edit_episode")) {
                    if (intval($rActualStatus) == 1) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Encode" type="button" class="btn btn-light waves-effect waves-light btn-xs api-start" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'start\');"><i class="mdi mdi-refresh"></i></button>
						';
                    } else if (intval($rActualStatus) == 3) {
                        $rButtons .= '<button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs api-stop"><i class="mdi mdi-stop"></i></button>
						';
                    } else if (intval($rActualStatus) == 2) {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Stop Encoding" type="button" class="btn btn-light waves-effect waves-light btn-xs api-stop" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'stop\');"><i class="mdi mdi-stop"></i></button>
						';
                    } else {
                        $rButtons .= '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Encode" type="button" class="btn btn-light waves-effect waves-light btn-xs api-start" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'start\');"><i class="mdi mdi-play"></i></button>
						';
                    }
                    $rButtons .= '<a href="./episode.php?id=' . $rRow["id"] . '&sid=' . $rRow["sid"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
					<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', ' . $rRow["server_id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                }
                $rButtons .= '</div>';
                if (hasPermissions("adv", "player")) {
                    if (((intval($rActualStatus) == 1) or ($rActualStatus == 3))) {
                        $rPlayer = '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Play" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="player(' . $rRow["id"] . ', \'' . json_decode($rRow["target_container"], true)[0] . '\');"><i class="mdi mdi-play"></i></button>';
                    } else {
                        $rPlayer = '<button type="button" disabled class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-play"></i></button>';
                    }
                } else {
                    $rPlayer = '<button type="button" disabled class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-play"></i></button>';
                }
                $rStreamInfoText = "<div style='font-size: 10px;' class='text-center' align='center'><tbody><tr><td colspan='3' class='col'>No information available</td></tr></tbody></div>";
                $rStreamInfo = json_decode($rRow["stream_info"], true);
                if ($rActualStatus == 1) {
                    if (!isset($rStreamInfo["codecs"]["video"])) {
                        $rStreamInfo["codecs"]["video"] = array("width" => "?", "height" => "?", "codec_name" => "N/A", "r_frame_rate" => "--");
                    }
                    if (!isset($rStreamInfo["codecs"]["audio"])) {
                        $rStreamInfo["codecs"]["audio"] = array("codec_name" => "N/A");
                    }
                    if ($rRow['bitrate'] == 0) {
                        $rRow['bitrate'] = "?";
                    }
                    $rStreamInfoText = "<div style='font-size: 12px;' class='text-center' align='center'>
                                <td class='col'><i class='mdi mdi-video' data-name='mdi-video' style='color: #20a009;'></i>" . $rStreamInfo["codecs"]["video"]["codec_name"] . "</td>
                                <td class='col'><i class='mdi mdi-volume-high' data-name='mdi-volume-high' style='color: #20a009;'></i>" . $rStreamInfo["codecs"]["audio"]["codec_name"] . "</td>
								<br>
								<td class='col'>" . $rRow['bitrate'] . " Kbps " . $rStreamInfo["codecs"]["video"]["width"] . "x" . $rStreamInfo["codecs"]["video"]["height"] . "</td>

                    </div>";
                }
                if ($rPermissions["is_admin"]) {
                    $rReturn["data"][] = array($rRow["id"], $rStreamName, $rServerName, $rClients, $rVODStatusArray[$rActualStatus], $rButtons, $rPlayer, $rStreamInfoText);
                } else {
                    $rReturn["data"][] = array($rRow["id"], $rStreamName, $rServerName, $rStreamInfoText);
                }
            }
        }
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "backups") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "database"))) {
        exit;
    }
    $rBackups = getBackups();
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => count($rBackups), "recordsFiltered" => count($rBackups), "data" => array());
    foreach ($rBackups as $rBackup) {
        $rButtons = '<div class="btn-group"><button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Restore Backup" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(\'' . $rBackup["filename"] . '\', \'restore\');"><i class="mdi mdi-folder-upload"></i></button>
		<button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete Backup" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(\'' . $rBackup["filename"] . '\', \'delete\');"><i class="mdi mdi-close"></i></button></div>';
        $rReturn["data"][] = array($rBackup["date"], $rBackup["filename"], ceil($rBackup["filesize"] / 1024 / 1024) . " MB", $rButtons);
    }
    echo json_encode($rReturn);
    exit;
} else if ($rType == "conn") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "database"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 1, "recordsFiltered" => 1, "data" => array($_INFO['hostname'], $_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['port']));
    echo json_encode($rReturn);
    exit;
} else if ($rType == "watch_output") {
    if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "folder_watch_output"))) {
        exit;
    }
    $rReturn = array("draw" => $_GET["draw"], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $rOrder = array("`watch_output`.`id`", "`watch_output`.`type`", "`watch_output`.`server_id`", "`watch_output`.`filename`", "`watch_output`.`status`", "`watch_output`.`dateadded`", false);
    if (strlen($_GET["order"][0]["column"]) > 0) {
        $rOrderRow = intval($_GET["order"][0]["column"]);
    } else {
        $rOrderRow = 0;
    }
    $rWhere = array();
    if (strlen($_GET["search"]["value"]) > 0) {
        $rSearch = $_GET["search"]["value"];
        $rWhere[] = "(`watch_output`.`id` LIKE '%{$rSearch}%' OR `watch_output`.`filename` LIKE '%{$rSearch}%' OR `watch_output`.`dateadded` LIKE '%{$rSearch}%')";
    }
    if (strlen($_GET["server"]) > 0) {
        $rWhere[] = "`watch_output`.`server_id` = " . intval($_GET["server"]);
    }
    if (strlen($_GET["type"]) > 0) {
        $rWhere[] = "`watch_output`.`type` = " . intval($_GET["type"]);
    }
    if (strlen($_GET["status"]) > 0) {
        $rWhere[] = "`watch_output`.`status` = " . intval($_GET["status"]);
    }
    if ($rOrder[$rOrderRow]) {
        $rOrderDirection = strtolower($_GET["order"][0]["dir"]) === 'desc' ? 'desc' : 'asc';
        $rOrderBy = "ORDER BY " . $rOrder[$rOrderRow] . " " . $rOrderDirection;
    }
    if (count($rWhere) > 0) {
        $rWhereString = "WHERE " . join(" AND ", $rWhere);
    } else {
        $rWhereString = "";
    }
    $rCountQuery = "SELECT COUNT(*) AS `count` FROM `watch_output` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `watch_output`.`server_id` {$rWhereString};";
    $ipTV_db_admin->query($rCountQuery);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rReturn["recordsTotal"] = $ipTV_db_admin->get_row()["count"];
    } else {
        $rReturn["recordsTotal"] = 0;
    }
    $rReturn["recordsFiltered"] = $rReturn["recordsTotal"];
    if ($rReturn["recordsTotal"] > 0) {
        $rQuery = "SELECT `watch_output`.`id`, `watch_output`.`type`, `watch_output`.`server_id`, `streaming_servers`.`server_name`, `watch_output`.`filename`, `watch_output`.`status`, `watch_output`.`stream_id`, `watch_output`.`dateadded` FROM `watch_output` LEFT JOIN `streaming_servers` ON `streaming_servers`.`id` = `watch_output`.`server_id` {$rWhereString} {$rOrderBy} LIMIT {$rStart}, {$rLimit};";
        $ipTV_db_admin->query($rQuery);
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $rRow) {
                $rButtons = '<div class="btn-group">';
                if ($rRow["stream_id"] > 0) {
                    if ($rRow["type"] == 1) {
                        if (hasPermissions("adv", "edit_movie")) {
                            $rButtons = '<a href="./movie.php?id=' . $rRow["stream_id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit Movie" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
							';
                        }
                    } else {
                        if (hasPermissions("adv", "edit_episode")) {
                            $rButtons = '<a href="./episode.php?id=' . $rRow["stream_id"] . '"><button data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit Episode" type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
							';
                        }
                    }
                }
                $rButtons .= '<button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(' . $rRow["id"] . ', \'delete\');"><i class="mdi mdi-close"></i></button>';
                $rButtons .= '</div>';
                $rReturn["data"][] = array($rRow["id"], array(1 => "Movies", 2 => "Series")[$rRow["type"]], $rRow["server_name"], $rRow["filename"], $rWatchStatusArray[$rRow["status"]], $rRow["dateadded"], $rButtons);
            }
        }
    }
    echo json_encode($rReturn);
    exit;
}
