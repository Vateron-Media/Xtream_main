<?php
include "session.php";
include "functions.php";
if (!$rPermissions["is_admin"]) {
    exit;
}
if ((!hasPermissions("adv", "settings")) && (!hasPermissions("adv", "database"))) {
    exit;
}

#Get versions
$rGeoLite2Latest = getGithubReleases("Vateron-Media/Xtream_Update")['latest_release'];
$rGeoLite2Curent = json_decode(file_get_contents("/home/xtreamcodes/bin/maxmind/version.json"), true)["geolite2_version"];
$rUpdatePanel = mb_substr(getGithubReleases("Vateron-Media/Xtream_main")['latest_release'], 1);
$rInfosUpdate = array();

if (isset(ipTV_lib::$request["geolite2"])) {
    if (updateGeoLite2()) {
        $_STATUS = 3;
    } else {
        $_STATUS = 2;
    }
}

if (isset(ipTV_lib::$request["panel_version"])) {
    $ipTV_db_admin->query("DELETE FROM `signals` WHERE `server_id` = " . $_INFO["server_id"] . " AND `custom_data` = `" . json_encode(array('action' => 'update')) . "`;");
    $ipTV_db_admin->query("INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES('" . $_INFO["server_id"] . "', '" . time() . "', '" . json_encode(array('action' => 'update')) . "');");
    $_STATUS = 5;
}

if ((isset(ipTV_lib::$request["submit_settings"])) && (hasPermissions("adv", "settings"))) {
    $rArray = getSettings();
    foreach (array("recaptcha_enable", "disable_trial", "mag_disable_ssl", "disable_mag_token", "ignore_invalid_users", "block_streaming_servers", "block_proxies", "detect_restream_block_user", "allow_cdn_access", "restrict_same_ip", "ip_subnet_match", "kill_rogue_ffmpeg", "ignore_keyframes", "ffmpeg_warnings", "ondemand_balance_equal", "on_demand_failure_exit", "on_demand_instant_off", "restrict_playlists", "encrypt_playlist_restreamer", "encrypt_playlist", "encrypt_hls", "disable_ts_allow_restream", "disable_ts", "disable_hls", "disable_hls_allow_restream", "disallow_empty_user_agents", "persistent_connections", "monitor_connection_status", "show_all_category_mag", "show_not_on_air_video", "show_banned_video", "show_expired_video", "rtmp_random", "use_buffer", "audio_restart_loss", "save_closed_connection", "client_logs_save", "case_sensitive_line", "county_override_1st", "disallow_2nd_ip_con", "use_mdomain_in_lists", "hash_lb", "show_isps", "enable_isp_lock", "block_svp", "mag_security", "always_enabled_subtitles", "enable_connection_problem_indication", "show_tv_channel_logo", "show_channel_logo_in_preview", "stb_change_pass", "enable_debug_stalker", "priority_backup", "debug_show_errors") as $rSetting) {
        if (isset(ipTV_lib::$request[$rSetting])) {
            $rArray[$rSetting] = 1;
            unset(ipTV_lib::$request[$rSetting]);
        } else {
            $rArray[$rSetting] = 0;
        }
    }
    if (!isset(ipTV_lib::$request["allowed_stb_types_for_local_recording"])) {
        $rArray["allowed_stb_types_for_local_recording"] = array();
    }
    if (!isset(ipTV_lib::$request["allowed_stb_types"])) {
        $rArray["allowed_stb_types"] = array();
    }
    //next 6 lines are for reseller mag events
    if (isset(ipTV_lib::$request["reseller_mag_events"])) {
        $rAdminSettings["reseller_mag_events"] = true;
        unset(ipTV_lib::$request["reseller_mag_events"]);
    } else {
        $rAdminSettings["reseller_mag_events"] = false;
    }
    // previous 6 lines are for reseller mag events
    if (isset(ipTV_lib::$request["ip_logout"])) {
        $rAdminSettings["ip_logout"] = true;
        unset(ipTV_lib::$request["ip_logout"]);
    } else {
        $rAdminSettings["ip_logout"] = false;
    }
    if (isset(ipTV_lib::$request["alternate_scandir"])) {
        $rAdminSettings["alternate_scandir"] = true;
        unset(ipTV_lib::$request["alternate_scandir"]);
    } else {
        $rAdminSettings["alternate_scandir"] = false;
    }
    if (isset(ipTV_lib::$request["download_images"])) {
        $rAdminSettings["download_images"] = true;
        unset(ipTV_lib::$request["download_images"]);
    } else {
        $rAdminSettings["download_images"] = false;
    }
    if (isset(ipTV_lib::$request["auto_refresh"])) {
        $rAdminSettings["auto_refresh"] = true;
        unset(ipTV_lib::$request["auto_refresh"]);
    } else {
        $rAdminSettings["auto_refresh"] = false;
    }
    if (isset(ipTV_lib::$request["local_api"])) {
        $rAdminSettings["local_api"] = true;
        unset(ipTV_lib::$request["local_api"]);
    } else {
        $rAdminSettings["local_api"] = false;
    }
    if (isset(ipTV_lib::$request["dark_mode_login"])) {
        $rAdminSettings["dark_mode_login"] = true;
        unset(ipTV_lib::$request["dark_mode_login"]);
    } else {
        $rAdminSettings["dark_mode_login"] = false;
    }
    if (isset(ipTV_lib::$request["dashboard_stats"])) {
        $rAdminSettings["dashboard_stats"] = true;
        unset(ipTV_lib::$request["dashboard_stats"]);
    } else {
        $rAdminSettings["dashboard_stats"] = false;
    }
    if (isset(ipTV_lib::$request["dashboard_world_map_live"])) {
        $rAdminSettings["dashboard_world_map_live"] = true;
        unset(ipTV_lib::$request["dashboard_world_map_live"]);
    } else {
        $rAdminSettings["dashboard_world_map_live"] = false;
    }
    if (isset(ipTV_lib::$request["dashboard_world_map_activity"])) {
        $rAdminSettings["dashboard_world_map_activity"] = true;
        unset(ipTV_lib::$request["dashboard_world_map_activity"]);
    } else {
        $rAdminSettings["dashboard_world_map_activity"] = false;
    }
    if (isset(ipTV_lib::$request["change_usernames"])) {
        $rAdminSettings["change_usernames"] = true;
        unset(ipTV_lib::$request["change_usernames"]);
    } else {
        $rAdminSettings["change_usernames"] = false;
    }
    if (isset(ipTV_lib::$request["change_own_dns"])) {
        $rAdminSettings["change_own_dns"] = true;
        unset(ipTV_lib::$request["change_own_dns"]);
    } else {
        $rAdminSettings["change_own_dns"] = false;
    }
    if (isset(ipTV_lib::$request["change_own_email"])) {
        $rAdminSettings["change_own_email"] = true;
        unset(ipTV_lib::$request["change_own_email"]);
    } else {
        $rAdminSettings["change_own_email"] = false;
    }
    if (isset(ipTV_lib::$request["change_own_password"])) {
        $rAdminSettings["change_own_password"] = true;
        unset(ipTV_lib::$request["change_own_password"]);
    } else {
        $rAdminSettings["change_own_password"] = false;
    }
    if (isset(ipTV_lib::$request["reseller_restrictions"])) {
        $rAdminSettings["reseller_restrictions"] = true;
        unset(ipTV_lib::$request["reseller_restrictions"]);
    } else {
        $rAdminSettings["reseller_restrictions"] = false;
    }
    if (isset(ipTV_lib::$request["default_entries"])) {
        $rAdminSettings["default_entries"] = ipTV_lib::$request["default_entries"];
    }
    if (isset(ipTV_lib::$request["tmdb_language"])) {
        $rAdminSettings["tmdb_language"] = ipTV_lib::$request["tmdb_language"];
        unset(ipTV_lib::$request["tmdb_language"]);
    }
    if (isset(ipTV_lib::$request["release_parser"])) {
        $rAdminSettings["release_parser"] = ipTV_lib::$request["release_parser"];
        unset(ipTV_lib::$request["release_parser"]);
    }
    if (isset(ipTV_lib::$request["backups_to_keep"])) {
        $rAdminSettings["backups_to_keep"] = ipTV_lib::$request["backups_to_keep"];
        unset(ipTV_lib::$request["backups_to_keep"]);
    }
    if (isset(ipTV_lib::$request["change_own_lang"])) {
        $rAdminSettings["change_own_lang"] = true;
        unset(ipTV_lib::$request["change_own_lang"]);
    } else {
        $rAdminSettings["change_own_lang"] = false;
    }
    /*if (isset(ipTV_lib::$request["reseller_select_bouquets"])) {
        $rAdminSettings["reseller_select_bouquets"] = true;
        unset(ipTV_lib::$request["reseller_select_bouquets"]);
    } else {
        $rAdminSettings["reseller_select_bouquets"] = false;
    }	*/
    if (isset(ipTV_lib::$request["active_mannuals"])) {
        $rAdminSettings["active_mannuals"] = true;
        unset(ipTV_lib::$request["active_mannuals"]);
    } else {
        $rAdminSettings["active_mannuals"] = false;
    }
    if (isset(ipTV_lib::$request["reseller_can_isplock"])) {
        $rAdminSettings["reseller_can_isplock"] = true;
        unset(ipTV_lib::$request["reseller_can_isplock"]);
    } else {
        $rAdminSettings["reseller_can_isplock"] = false;
    }
    if (isset(ipTV_lib::$request["reseller_reset_isplock"])) {
        $rAdminSettings["reseller_reset_isplock"] = true;
        unset(ipTV_lib::$request["reseller_reset_isplock"]);
    } else {
        $rAdminSettings["reseller_reset_isplock"] = false;
    }
    if (isset(ipTV_lib::$request["login_flood"])) {
        $rAdminSettings["login_flood"] = ipTV_lib::$request["login_flood"];
        unset(ipTV_lib::$request["login_flood"]);
    }
    if (isset(ipTV_lib::$request["dashboard_stats_frequency"])) {
        $rAdminSettings["dashboard_stats_frequency"] = ipTV_lib::$request["dashboard_stats_frequency"];
        unset(ipTV_lib::$request["dashboard_stats_frequency"]);
    }
    writeAdminSettings();
    foreach (ipTV_lib::$request as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    if (ipTV_lib::setSettings($rArray)) {
        $_STATUS = 0;
    } else {
        $_STATUS = 1;
    }
}

$rSettings = getSettings(); // Update
$rSettings["sidebar"] = $rUserInfo["sidebar"];

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) { ?>
    <div class="content-page">
        <div class="content boxed-layout-ext">
            <div class="container-fluid">
<?php } else { ?>
                <div class="wrapper boxed-layout-ext">
                    <div class="container-fluid">
<?php } ?>
                    <form action="./settings.php" method="POST" id="category_form">
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?= $_["settings"] ?></h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <div class="row">
                            <div class="col-xl-12">
                                <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["settings_sucessfully_updated"] ?>
                                    </div>
                                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        <?= $_["there_was_an_error_saving_settings"] ?>
                                        </div>
                                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                        <?= $_["failed_to_update_GeoLite2"] ?>
                                            </div>
                                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                        <?= $_["geoLite2_has_been_updated"] ?>
                                                </div>
                                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 4)) { ?>
                                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        Failed to update Panel! Please try again.
                                                    </div>
                                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 5)) { ?>
                                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                            XC_VM is currently waiting to be updated... Your server will become
                                                            unavailable once the process begins.
                                                        </div>
                                <?php } elseif ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                        <?= $_["there_was_an_error_saving_settings"] ?>
                                                            </div>
                                <?php } ?>
                                <?php if (version_compare($rGeoLite2Latest, $rGeoLite2Curent ? $rGeoLite2Curent : "0.0.0")) { ?>
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["a_new_version_of_GeoLite2"] ?> (<?= $rGeoLite2Latest ?>)
                                        <?= $_["is_available"] ?>
                                        <a href="./settings.php?geolite2"><?= $_["click_here_to_update"] ?></a>
                                    </div>
                                <?php } ?>
                                <?php if (version_compare($rUpdatePanel, getScriptVer())) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        A new version (<?= $rUpdatePanel ?>) <?= $_["is_available"] ?> <a
                                            href="./settings.php?panel_version"><?= $_["click_here_to_update"] ?></a>
                                    </div>
                                <?php } ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="bg-soft-light border-light border">
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <p class="text-muted mb-0 mt-3"><?= $_["installed_version"] ?></p>
                                                    <h2 class="font-weight-normal mb-3">
                                                        <small
                                                            class="mdi mdi-checkbox-blank-circle text-success align-middle mr-1"></small>
                                                        <span><?= getScriptVer() ?></span>
                                                    </h2>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="text-muted mb-0 mt-3"><?= $_["official_release"] ?></p>
                                                    <h2 class="font-weight-normal mb-3">
                                                        <small
                                                            class="mdi mdi-checkbox-blank-circle text-info align-middle mr-1"></small>
                                                        <span><?= $rUpdatePanel ?></span>
                                                    </h2>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="text-muted mb-0 mt-3"><?= $_["geoLite2_version"] ?></p>
                                                    <h2 class="font-weight-normal mb-3">
                                                        <small
                                                            class="mdi mdi-checkbox-blank-circle text-pink align-middle mr-1"></small>
                                                        <span><?= $rGeoLite2Curent ?></span>
                                                    </h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <?php if (hasPermissions("adv", "settings")) { ?>
                                                    <li class="nav-item">
                                                        <a href="#general-details" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["general"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#security" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-shield-lock mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["security"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#xui" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-settings mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["xtream_ui"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#reseller" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-coins mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["reseller"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#streaming" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-play mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["streaming"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#mag" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-tablet mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["mag"] ?></span>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if (hasPermissions("adv", "database")) { ?>
                                                    <li class="nav-item">
                                                        <a href="#backups" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-backup-restore mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["backups"] ?></span>
                                                        </a>
                                                    </li>
                                                    <!-- <li class="nav-item">
                                                        <a href="#infos" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="fas fa-info mr-1"></i>
                                                            <span class="d-none d-sm-inline">Infos</span>
                                                        </a>
                                                    </li> -->
                                                    <li class="nav-item">
                                                        <a href="#database" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-database mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["database"] ?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <?php if (hasPermissions("adv", "settings")) { ?>
                                                    <div class="tab-pane" id="general-details">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                </br>
                                                                <h5 class="card-title mb-4">Preferences</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="server_name"><?= $_["server_name"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="server_name" name="server_name"
                                                                            value="<?= htmlspecialchars($rSettings["server_name"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="logo_url"><?= $_["logo_url"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="logo_url" name="logo_url"
                                                                            value="<?= htmlspecialchars($rSettings["logo_url"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="logo_url_sidebar"><?= $_["logo_url_sidebar"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="logo_url_sidebar" name="logo_url_sidebar"
                                                                            value="<?= htmlspecialchars($rSettings["logo_url_sidebar"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="default_timezone"><?= $_["timezone"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <select name="default_timezone"
                                                                            id="default_timezone" class="form-control"
                                                                            data-toggle="select2">
                                                                            <?php
                                                                            foreach ($rTimeZones as $rValue => $rText) { ?>
                                                                                <option <?php if ($rSettings["default_timezone"] == $rValue) {
                                                                                    echo "selected ";
                                                                                        } ?>value="<?= $rValue ?>">
                                                                                    <?= $rText ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="message_of_day"><?= $_["message_of_the_day"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["message_to_display_api"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="message_of_day" name="message_of_day"
                                                                            value="<?= htmlspecialchars($rSettings["message_of_day"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="bouquet_name"><?= $_["enigma2_bouquet_name"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="bouquet_name" name="bouquet_name"
                                                                            value="<?= htmlspecialchars($rSettings["bouquet_name"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="live_streaming_pass"><?= $_["live_streaming_pass"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="live_streaming_pass"
                                                                            name="live_streaming_pass"
                                                                            value="<?= htmlspecialchars($rSettings["live_streaming_pass"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="default_entries"><?= $_["default_entries_show"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["default_entries_for_users"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <select name="default_entries" id="default_entries"
                                                                            class="form-control" data-toggle="select2">
                                                                            <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) {
                                                                                    echo " selected";
                                                                                       } ?>
                                                                                    value="<?= $rShow ?>">
                                                                                    <?= $rShow ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">Dashboard</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="dashboard_stats"><?= $_["dashboard_stats"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["enable_dashboard_option"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dashboard_stats" id="dashboard_stats"
                                                                            type="checkbox" <?php if ($rAdminSettings["dashboard_stats"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="dashboard_stats_frequency"><?= $_["stats_frequency"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["stats_interval"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="dashboard_stats_frequency"
                                                                            name="dashboard_stats_frequency"
                                                                            value="<?= htmlspecialchars($rAdminSettings["dashboard_stats_frequency"]) ?: 600 ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="dashboard_world_map_activity">Dashboard World
                                                                        Map Activity <i data-toggle="tooltip"
                                                                            data-placement="top" title=""
                                                                            data-original-title="Enable this option to show interactive connection statistics activity on dashboard."
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dashboard_world_map_activity"
                                                                            id="dashboard_world_map_activity"
                                                                            type="checkbox" <?php if ($rAdminSettings["dashboard_world_map_activity"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="dashboard_world_map_live">Dashboard World Map
                                                                        Live <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="Enable this option to show interactive connection statistics live on dashboard."
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dashboard_world_map_live"
                                                                            id="dashboard_world_map_live" type="checkbox"
                                                                            <?php if ($rAdminSettings["dashboard_world_map_live"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">Debug</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="debug_show_errors"><?= $_["debug_show_errors"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="debug_show_errors"
                                                                            id="debug_show_errors" type="checkbox" <?php if ($rSettings["debug_show_errors"] == 1) {
                                                                                echo "checked ";
                                                                                                                   } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">reCAPTCHA</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="recaptcha_enable"><?= $_["enable_recaptcha"] ?>
                                                                        <i class="mdi mdi-information" data-toggle="modal"
                                                                            data-target=".bs-domains"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="recaptcha_enable" id="recaptcha_enable"
                                                                            type="checkbox" <?php if ($rSettings["recaptcha_enable"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="recaptcha_v2_site_key"><?= $_["recaptcha_v2_site_key"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["your_api_keys"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="recaptcha_v2_site_key"
                                                                            name="recaptcha_v2_site_key"
                                                                            value="<?= htmlspecialchars($rSettings["recaptcha_v2_site_key"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="recaptcha_v2_secret_key"><?= $_["recaptcha_v2_secret_key"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["your_secret_api_keys"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="recaptcha_v2_secret_key"
                                                                            name="recaptcha_v2_secret_key"
                                                                            value="<?= htmlspecialchars($rSettings["recaptcha_v2_secret_key"]) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ul class="list-inline wizard mb-0">
                                                            <!--<li class="list-inline-item">
                                                        <a href="https://syte.com/donate.html">
                                                            <button type="button" class="btn btn-info waves-effect waves-light btn-xl"><i class="mdi mdi-credit-card"></i> <?= $_["credit-card"] ?></button></a>
                                                        </a>
                                                        <a href="https://commerce.coinbase.com">
                                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-xl"><i class="mdi mdi-currency-btc"></i> <?= $_["btc"] ?></button></a>
                                                        </a>
                                                        <a href="https://www.paypal.com">
                                                            <button type="button" class="btn btn-success waves-effect waves-light btn-xl"><i class="mdi mdi-paypal"></i> <?= $_["paypal"] ?></button></a>
                                                        </a>
                                                    </li>-->
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit"
                                                                    class="btn btn-primary" value=<?= $_["save_changes"] ?> />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="security">
                                                        </br>
                                                        <h5 class="card-title mb-4">IP Security</h5>
                                                        </br>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label"
                                                                for="ip_subnet_match"><?= $_["ip_subnet_match"] ?> <i
                                                                    data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["desc_ip_subnet_match"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="ip_subnet_match" id="ip_subnet_match"
                                                                    type="checkbox" <?php if ($rAdminSettings["ip_subnet_match"] == 1) {
                                                                        echo "checked ";
                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                                    data-color="#039cfd" />
                                                            </div>
                                                            <label class="col-md-4 col-form-label"
                                                                for="ip_logout"><?= $_["logout_on_ip_change"] ?> <i
                                                                    data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["logout_session"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="ip_logout" id="ip_logout" type="checkbox" <?php if ($rAdminSettings["ip_logout"] == 1) {
                                                                    echo "checked ";
                                                                                                                       } ?>data-plugin="switchery" class="js-switch"
                                                                    data-color="#039cfd" />
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label"
                                                                for="restrict_same_ip"><?= $_["restrict_same_ip"] ?> <i
                                                                    data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["desc_restrict_same_ip"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="restrict_same_ip" id="restrict_same_ip"
                                                                    type="checkbox" <?php if ($rSettings["restrict_same_ip"] == 1) {
                                                                        echo "checked ";
                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                                    data-color="#039cfd" />
                                                            </div>
                                                            <label class="col-md-4 col-form-label"
                                                                for="rtmp_random"><?= $_["random_rtmp_ip"] ?> <i
                                                                    data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["use_random_ip_for_rmtp"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="rtmp_random" id="rtmp_random" type="checkbox"
                                                                    <?php if ($rSettings["rtmp_random"] == 1) {
                                                                        echo "checked ";
                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                                    data-color="#039cfd" />
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label"
                                                                for="disallow_2nd_ip_con"><?= $_["disallow_2nd_ip_connection"] ?>
                                                                <i data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["disallow_connection"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="disallow_2nd_ip_con" id="disallow_2nd_ip_con"
                                                                    type="checkbox" <?php if ($rSettings["disallow_2nd_ip_con"] == 1) {
                                                                        echo "checked ";
                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                                    data-color="#039cfd" />
                                                            </div>
                                                            <label class="col-md-4 col-form-label"
                                                                for="disallow_2nd_ip_max"><?= $_["disallow_2nd_ip_max"] ?>
                                                                <i data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["desc_disallow_2nd_ip_max"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control"
                                                                    id="disallow_2nd_ip_max" name="disallow_2nd_ip_max"
                                                                    value="<?= htmlspecialchars($rSettings["disallow_2nd_ip_max"]) ?: 0 ?>">
                                                            </div>
                                                        </div>
                                                        </br>
                                                        <h5 class="card-title mb-4">On-Demand Settings(not working)</h5>
                                                        </br>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label"
                                                                for="detect_restream_block_user"><?= $_["detect_restream_block_user"] ?>
                                                                <i data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["desc_detect_restream_block_user"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="detect_restream_block_user"
                                                                    id="detect_restream_block_user" type="checkbox" <?php if ($rSettings["detect_restream_block_user"] == 1) {
                                                                        echo "checked ";
                                                                                                                    } ?>data-plugin="switchery"
                                                                    class="js-switch" data-color="#039cfd" />
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label"
                                                                for="block_streaming_servers"><?= $_["block_streaming_servers"] ?>
                                                                <i data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["desc_block_streaming_servers"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="block_streaming_servers"
                                                                    id="block_streaming_servers" type="checkbox" <?php if ($rSettings["block_streaming_servers"] == 1) {
                                                                        echo "checked ";
                                                                                                                 } ?>data-plugin="switchery"
                                                                    class="js-switch" data-color="#039cfd" />
                                                            </div>
                                                            <label class="col-md-4 col-form-label"
                                                                for="block_proxies"><?= $_["block_proxies"] ?>
                                                                <i data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["desc_block_proxies"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="block_proxies" id="block_proxies"
                                                                    type="checkbox" <?php if ($rSettings["block_proxies"] == 1) {
                                                                        echo "checked ";
                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                                    data-color="#039cfd" />
                                                            </div>
                                                        </div>
                                                        </br>
                                                        <h5 class="card-title mb-4">Spam Prevention</h5>
                                                        </br>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label"
                                                                for="flood_limit"><?= $_["flood_limit"] ?> <i
                                                                    data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["enter_to_disable_flood_detection"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="flood_limit"
                                                                    name="flood_limit"
                                                                    value="<?= htmlspecialchars($rSettings["flood_limit"]) ?>">
                                                            </div>
                                                            <label class="col-md-4 col-form-label"
                                                                for="flood_ips_exclude"><?= $_["flood_ip_exclude"] ?>
                                                                <i data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["separate_each_ip"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control"
                                                                    id="flood_ips_exclude" name="flood_ips_exclude"
                                                                    value="<?= htmlspecialchars($rSettings["flood_ips_exclude"]) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label"
                                                                for="login_flood"><?= $_["maximum_login_attempts"] ?>
                                                                <i data-toggle="tooltip" data-placement="top" title=""
                                                                    data-original-title="<?= $_["how_many_login_attempts"] ?>"
                                                                    class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="login_flood"
                                                                    name="login_flood"
                                                                    value="<?= htmlspecialchars($rAdminSettings["login_flood"]) ?: 0 ?>">
                                                            </div>
                                                        </div>
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit"
                                                                    class="btn btn-primary"
                                                                    value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="xui">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="tmdb_api_key"><?= $_["tmdb_api_key"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="tmdb_api_key" name="tmdb_api_key"
                                                                            value="<?= htmlspecialchars($rSettings["tmdb_api_key"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="tmdb_language"><?= $_["tmdb_language"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["select_which_language"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <select name="tmdb_language" id="tmdb_language"
                                                                            class="form-control" data-toggle="select2">
                                                                            <?php foreach ($rTMDBLanguages as $rKey => $rLanguage) { ?>
                                                                                <option<?php if ($rAdminSettings["tmdb_language"] == $rKey) {
                                                                                    echo " selected";
                                                                                       } ?> value="<?= $rKey ?>">
                                                                                    <?= $rLanguage ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="release_parser"><?= $_["release_parser"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["select_which_parser"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <select name="release_parser" id="release_parser"
                                                                            class="form-control" data-toggle="select2">
                                                                            <?php foreach (array("python" => "Python Based (slower, more accurate)", "php" => "PHP Based (faster, less accurate)") as $rKey => $rParser) { ?>
                                                                                <option<?php if ($rAdminSettings["release_parser"] == $rKey) {
                                                                                    echo " selected";
                                                                                       } ?> value="<?= $rKey ?>">
                                                                                    <?= $rParser ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>


                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="pass_length"><?= $_["minimum_pass_length"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["set_this_enforce_password"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="pass_length" name="pass_length"
                                                                            value="<?= htmlspecialchars($rSettings["pass_length"]) ?: 0 ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="api_container"><?= $_["api_container"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_api_container"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <select name="api_container" id="api_container"
                                                                            class="form-control" data-toggle="select2">
                                                                            <?php
                                                                            foreach (array("ts" => "MPEG-TS", "m3u8" => "HLS") as $rValue => $rText) { ?>
                                                                                <option <?php if ($rSettings["api_container"] == $rValue) {
                                                                                    echo "selected ";
                                                                                        } ?>value="<?= $rValue ?>">
                                                                                    <?= $rText ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="local_api"><?= $_["local_api"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["select_this_option"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="local_api" id="local_api"
                                                                            type="checkbox" <?php if ($rAdminSettings["local_api"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="dark_mode_login"><?= $_["dark_mode_login"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dark_mode_login" id="dark_mode_login"
                                                                            type="checkbox" <?php if ($rAdminSettings["dark_mode_login"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="download_images"><?= $_["download_images"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["images_from_server_tmdb"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="download_images" id="download_images"
                                                                            type="checkbox" <?php if ($rAdminSettings["download_images"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="auto_refresh"><?= $_["auto-refresh_by_default"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["auto_refresh_pages_by_deault"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="auto_refresh" id="auto_refresh"
                                                                            type="checkbox" <?php if ($rAdminSettings["auto_refresh"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="alternate_scandir"><?= $_["alternate_scandir_method"] ?>
                                                                        (Cloud) <i data-toggle="tooltip"
                                                                            data-placement="top" title=""
                                                                            data-original-title="<?= $_["use_an_alternate_method"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="alternate_scandir"
                                                                            id="alternate_scandir" type="checkbox" <?php if ($rAdminSettings["alternate_scandir"] == 1) {
                                                                                echo "checked ";
                                                                                                                   } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="allowed_ips_admin"><?= $_["admin_streaming_ips"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["allowed_ip_to_access"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="allowed_ips_admin" name="allowed_ips_admin"
                                                                            value="<?= htmlspecialchars($rSettings["allowed_ips_admin"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="api_ips"><?= $_["api_ips"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["allowed_ip_to_access_api"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="api_ips"
                                                                            name="api_ips"
                                                                            value="<?= htmlspecialchars($rSettings["api_ips"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="save_closed_connection"><?= $_["save_connection_logs"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["save_closed_connection_database"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="save_closed_connection"
                                                                            id="save_closed_connection" type="checkbox"
                                                                            <?php if ($rSettings["save_closed_connection"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="client_logs_save"><?= $_["save_client_logs"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["save_client_logs_to_database"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="client_logs_save" id="client_logs_save"
                                                                            type="checkbox" <?php if ($rSettings["client_logs_save"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit"
                                                                    class="btn btn-primary"
                                                                    value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="reseller">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="copyrights_text"><?= $_["copyrights_text"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            id="copyrights_text" name="copyrights_text"
                                                                            value="<?= htmlspecialchars($rSettings["copyrights_text"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="disable_trial"><?= $_["disable_trial"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["use_this_option_to_temporarily_disable_generating_trials"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disable_trial" id="disable_trial"
                                                                            type="checkbox" <?php if ($rSettings["disable_trial"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="reseller_restrictions"><?= $_["allow_restrictions"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["set_this_option_to_allow_resellers"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_restrictions"
                                                                            id="reseller_restrictions" type="checkbox" <?php if ($rAdminSettings["reseller_restrictions"] == 1) {
                                                                                echo "checked ";
                                                                                                                       } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="change_usernames"><?= $_["change_usernames"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["set_this_option_to_allow_change_own_usernames"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_usernames" id="change_usernames"
                                                                            type="checkbox" <?php if ($rAdminSettings["change_usernames"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="change_own_dns"><?= $_["change_own_dns"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["set_this_option_to_allow_change_own_dns"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_dns" id="change_own_dns"
                                                                            type="checkbox" <?php if ($rAdminSettings["change_own_dns"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="change_own_email"><?= $_["change_own_email"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["set_this_option_to_allow_change_own_email"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_email" id="change_own_email"
                                                                            type="checkbox" <?php if ($rAdminSettings["change_own_email"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="change_own_password"><?= $_["change_own_password"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["set_this_option_to_allow_change_own_password"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_password"
                                                                            id="change_own_password" type="checkbox" <?php if ($rAdminSettings["change_own_password"] == 1) {
                                                                                echo "checked ";
                                                                                                                     } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="change_own_lang"><?= $_["change_own_language_resellers"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["change_own_language_resellers_msg"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_lang" id="change_own_lang"
                                                                            type="checkbox" <?php if ($rAdminSettings["change_own_lang"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="reseller_mag_events"><?= $_["reseller_send_events"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["resellers_to_be_able_to_send_mag_events"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_mag_events"
                                                                            id="reseller_mag_events" type="checkbox" <?php if ($rAdminSettings["reseller_mag_events"] == 1) {
                                                                                echo "checked ";
                                                                                                                     } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="reseller_can_isplock"><?= $_["reseller_can_isplock"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["message_reseller_can_isplock"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_can_isplock"
                                                                            id="reseller_can_isplock" type="checkbox" <?php if ($rAdminSettings["reseller_can_isplock"] == 1) {
                                                                                echo "checked ";
                                                                                                                      } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="reseller_reset_isplock"><?= $_["reseller_reset_isplock"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["message_reseller_reset_isplock"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_reset_isplock"
                                                                            id="reseller_reset_isplock" type="checkbox"
                                                                            <?php if ($rAdminSettings["reseller_reset_isplock"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <!--<label class="col-md-4 col-form-label" for="reseller_select_bouquets"><?= $_["reseller_select_bouquets"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["message_reseller_select_bouquets"] ?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="reseller_select_bouquets" id="reseller_select_bouquets" type="checkbox"<?php if ($rAdminSettings["reseller_select_bouquets"] == 1) {
                                                                    echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>-->
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="active_mannuals"><?= $_["active_mannuals"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["message_active_mannuals"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="active_mannuals" id="active_mannuals"
                                                                            type="checkbox" <?php if ($rAdminSettings["active_mannuals"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit"
                                                                    class="btn btn-primary"
                                                                    value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="streaming">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                </br>
                                                                <h5 class="card-title mb-4">Preferences</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="enable_isp_lock"><?= $_["enable_isp_lock1"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["enable_isp_lock_msg"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="enable_isp_lock" id="enable_isp_lock"
                                                                            type="checkbox" <?php if ($rSettings["enable_isp_lock"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="block_svp"><?= $_["block_svp"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["block_svp_desc"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="block_svp" id="block_svp"
                                                                            type="checkbox" <?php if ($rSettings["block_svp"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="disable_ts"><?= $_["disable_ts"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_disable_ts"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disable_ts" id="disable_ts"
                                                                            type="checkbox" <?php if ($rSettings["disable_ts"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="disable_ts_allow_restream"><?= $_["disable_ts_allow_restream"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_disable_ts_allow_restream"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disable_ts_allow_restream"
                                                                            id="disable_ts_allow_restream" type="checkbox"
                                                                            <?php if ($rSettings["disable_ts_allow_restream"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="disable_hls"><?= $_["disable_hls"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_disable_hls"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disable_hls" id="disable_hls"
                                                                            type="checkbox" <?php if ($rSettings["disable_hls"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="disable_hls_allow_restream"><?= $_["disable_hls_allow_restream"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_disable_hls_allow_restream"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disable_hls_allow_restream"
                                                                            id="disable_hls_allow_restream" type="checkbox"
                                                                            <?php if ($rSettings["disable_disable_hls_allow_restreamts_allow_restream"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="case_sensitive_line"><?= $_["case_sensitive_line"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_case_sensitive_line"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="case_sensitive_line"
                                                                            id="case_sensitive_line" type="checkbox" <?php if ($rSettings["case_sensitive_line"] == 1) {
                                                                                echo "checked ";
                                                                                                                     } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="county_override_1st"><?= $_["override_country_with_first"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["override_country_with_connected"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="county_override_1st"
                                                                            id="county_override_1st" type="checkbox" <?php if ($rSettings["county_override_1st"] == 1) {
                                                                                echo "checked ";
                                                                                                                     } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="encrypt_hls"><?= $_["encrypt_hls"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_encrypt_hls"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="encrypt_hls" id="encrypt_hls"
                                                                            type="checkbox" <?php if ($rSettings["encrypt_hls"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="disallow_empty_user_agents"><?= $_["disallow_empty_ua"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["dont_allow_connections_from_clients"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disallow_empty_user_agents"
                                                                            id="disallow_empty_user_agents" type="checkbox"
                                                                            <?php if ($rSettings["disallow_empty_user_agents"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="vod_bitrate_plus">VOD Download Speed <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="Specify the bitrate here in kbps. Enter number only. 2000 kB/s = 2 MB/s."
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="vod_bitrate_plus" name="vod_bitrate_plus"
                                                                            value="<?= htmlspecialchars($rSettings["vod_bitrate_plus"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="vod_limit_perc"><?= $_["vod_limit_perc"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_vod_limit_perc"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="vod_limit_perc" name="vod_limit_perc"
                                                                            value="<?= htmlspecialchars($rSettings["vod_limit_perc"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="user_auto_kick_hours"><?= $_["auto_kick_users"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["automatically_kick_users"] ?> "
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="user_auto_kick_hours"
                                                                            name="user_auto_kick_hours"
                                                                            value="<?= htmlspecialchars($rSettings["user_auto_kick_hours"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="use_mdomain_in_lists"><?= $_["use_domain_in_lists"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["use_domaine_name"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="use_mdomain_in_lists"
                                                                            id="use_mdomain_in_lists" type="checkbox" <?php if ($rSettings["use_mdomain_in_lists"] == 1) {
                                                                                echo "checked ";
                                                                                                                      } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="encrypt_playlist"><?= $_["encrypt_playlist"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_encrypt_playlist"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="encrypt_playlist" id="encrypt_playlist"
                                                                            type="checkbox" <?php if ($rSettings["encrypt_playlist"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="encrypt_playlist_restreamer"><?= $_["encrypt_playlist_restreamer"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_encrypt_playlist_restreamer"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="encrypt_playlist_restreamer"
                                                                            id="encrypt_playlist_restreamer" type="checkbox"
                                                                            <?php if ($rSettings["encrypt_playlist_restreamer"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="restrict_playlists"><?= $_["restrict_playlists"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_restrict_playlists"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="restrict_playlists"
                                                                            id="restrict_playlists" type="checkbox" <?php if ($rSettings["restrict_playlists"] == 1) {
                                                                                echo "checked ";
                                                                                                                    } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="ignore_invalid_users"><?= $_["ignore_invalid_users"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_ignore_invalid_users"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="ignore_invalid_users"
                                                                            id="ignore_invalid_users" type="checkbox" <?php if ($rSettings["ignore_invalid_users"] == 1) {
                                                                                echo "checked ";
                                                                                                                      } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="client_prebuffer"><?= $_["client_prebuffer"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["how_much_data_will_be_sent_to_the_client_1"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="client_prebuffer" name="client_prebuffer"
                                                                            value="<?= htmlspecialchars($rSettings["client_prebuffer"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="restreamer_prebuffer"><?= $_["restreamer_prebuffer"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["how_much_data_will_be_sent_to_the_client_2"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="restreamer_prebuffer"
                                                                            name="restreamer_prebuffer"
                                                                            value="<?= htmlspecialchars($rSettings["restreamer_prebuffer"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="split_by"><?= $_["split_by"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_split_by"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <select name="split_by" id="split_by"
                                                                            class="form-control" data-toggle="select2">
                                                                            <option<?php if ($rSettings["split_by"] == "conn") {
                                                                                echo " selected";
                                                                                   } ?> value="conn">
                                                                                <?= $_["connections"] ?></option>
                                                                                <option<?php if ($rSettings["split_by"] == "maxclients") {
                                                                                    echo " selected";
                                                                                       } ?>
                                                                                    value="maxclients">
                                                                                    <?= $_["max_clients"] ?></option>
                                                                                    <option<?php if ($rSettings["split_by"] == "guar_band") {
                                                                                        echo " selected";
                                                                                           } ?>
                                                                                        value="guar_band">
                                                                                        <?= $_["network_speed"] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="channel_number_type"><?= $_["channel_sorting_type"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="channel_number_type"
                                                                            id="channel_number_type" class="form-control"
                                                                            data-toggle="select2">
                                                                            <option<?php if ($rSettings["channel_number_type"] == "bouquet") {
                                                                                echo " selected";
                                                                                   } ?> value="bouquet">
                                                                                <?= $_["bouquet"] ?></option>
                                                                                <option<?php if ($rSettings["channel_number_type"] == "manual") {
                                                                                    echo " selected";
                                                                                       } ?> value="manual">
                                                                                    <?= $_["manual"] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="use_buffer"><?= $_["use_nginx_buffer"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["proxy_buffering"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="use_buffer" id="use_buffer"
                                                                            type="checkbox" <?php if ($rSettings["use_buffer"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="show_isps"><?= $_["enable_isps"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["grab_isp_information"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_isps" id="show_isps"
                                                                            type="checkbox" <?php if ($rSettings["show_isps"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="online_capacity_interval"><?= $_["online_capacity_interval"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["interval_at_which_to_check"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="online_capacity_interval"
                                                                            name="online_capacity_interval"
                                                                            value="<?= htmlspecialchars($rSettings["online_capacity_interval"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="monitor_connection_status"><?= $_["monitor_connection_status"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_monitor_connection_status"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="monitor_connection_status"
                                                                            id="monitor_connection_status" type="checkbox"
                                                                            <?php if ($rSettings["monitor_connection_status"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="kill_rogue_ffmpeg"><?= $_["kill_rogue_ffmpeg"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_kill_rogue_ffmpeg"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="kill_rogue_ffmpeg"
                                                                            id="kill_rogue_ffmpeg" type="checkbox" <?php if ($rSettings["kill_rogue_ffmpeg"] == 1) {
                                                                                echo "checked ";
                                                                                                                   } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="create_expiration"><?= $_["create_expiration"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_create_expiration"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="create_expiration" name="create_expiration"
                                                                            value="<?= htmlspecialchars($rSettings["create_expiration"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="read_buffer_size"><?= $_["read_buffer_size"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_read_buffer_size"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="read_buffer_size" name="read_buffer_size"
                                                                            value="<?= htmlspecialchars($rSettings["read_buffer_size"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="stop_failures"><?= $_["stop_failures"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_stop_failures"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="stop_failures" name="stop_failures"
                                                                            value="<?= htmlspecialchars($rSettings["stop_failures"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="allow_cdn_access"><?= $_["allow_cdn_access"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_allow_cdn_access"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="allow_cdn_access" id="allow_cdn_access"
                                                                            type="checkbox" <?php if ($rSettings["allow_cdn_access"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">On-Demand Settings
                                                                </h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="on_demand_instant_off"><?= $_["on_demand_instant_off"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_on_demand_instant_off"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="on_demand_instant_off"
                                                                            id="on_demand_instant_off" type="checkbox" <?php if ($rSettings["on_demand_instant_off"] == 1) {
                                                                                echo "checked ";
                                                                                                                       } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="on_demand_failure_exit"><?= $_["on_demand_failure_exit"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_on_demand_failure_exit"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="on_demand_failure_exit"
                                                                            id="on_demand_failure_exit" type="checkbox"
                                                                            <?php if ($rSettings["on_demand_failure_exit"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="on_demand_wait_time"><?= $_["on_demand_wait_time"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_on_demand_wait_time"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="on_demand_wait_time"
                                                                            name="on_demand_wait_time"
                                                                            value="<?= htmlspecialchars($rSettings["on_demand_wait_time"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="ondemand_balance_equal"><?= $_["ondemand_balance_equal"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_ondemand_balance_equal"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="ondemand_balance_equal"
                                                                            id="ondemand_balance_equal" type="checkbox"
                                                                            <?php if ($rSettings["ondemand_balance_equal"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">Segment Settings</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="seg_time"><?= $_["seg_time"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_seg_time"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="seg_time" name="seg_time"
                                                                            value="<?= htmlspecialchars($rSettings["seg_time"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="seg_list_size"><?= $_["seg_list_size"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_seg_list_size"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="seg_list_size" name="seg_list_size"
                                                                            value="<?= htmlspecialchars($rSettings["seg_list_size"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="seg_delete_threshold"><?= $_["seg_delete_threshold"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_seg_delete_threshold"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="seg_delete_threshold"
                                                                            name="seg_delete_threshold"
                                                                            value="<?= htmlspecialchars($rSettings["seg_delete_threshold"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="segment_wait_time"><?= $_["segment_wait_time"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_segment_wait_time"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="segment_wait_time" name="segment_wait_time"
                                                                            value="<?= htmlspecialchars($rSettings["segment_wait_time"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="stream_max_analyze"><?= $_["analysis_duration"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["longer duration"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="stream_max_analyze"
                                                                            name="stream_max_analyze"
                                                                            value="<?= htmlspecialchars($rSettings["stream_max_analyze"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="probesize"><?= $_["probe_size"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["probed_in_bytes"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="probesize" name="probesize"
                                                                            value="<?= htmlspecialchars($rSettings["probesize"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="segment_type"><?= $_["segment_type"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_segment_type"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <select name="segment_type" id="segment_type"
                                                                            class="form-control" data-toggle="select2">
                                                                            <option<?php if ($rSettings["segment_type"] == "0") {
                                                                                echo " selected";
                                                                                   } ?> value="0">-f hls</option>
                                                                                <option<?php if ($rSettings["segment_type"] == "1") {
                                                                                    echo " selected";
                                                                                       } ?> value="1">-f segment
                                                                                    </option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="ffmpeg_warnings"><?= $_["ffmpeg_warnings"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_ffmpeg_warnings"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="ffmpeg_warnings" id="ffmpeg_warnings"
                                                                            type="checkbox" <?php if ($rSettings["ffmpeg_warnings"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="ignore_keyframes"><?= $_["ignore_keyframes"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_ignore_keyframes"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="ignore_keyframes" id="ignore_keyframes"
                                                                            type="checkbox" <?php if ($rSettings["ignore_keyframes"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">Stream Monitor Settings</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="audio_restart_loss"><?= $_["restart_on_audio_loss"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["restart_stream_periodically"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="audio_restart_loss"
                                                                            id="audio_restart_loss" type="checkbox" <?php if ($rSettings["audio_restart_loss"] == 1) {
                                                                                echo "checked ";
                                                                                                                    } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="priority_backup">Priority Backup Stream <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="Enable if you want the first backup stream to be a priority if you are online"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="priority_backup" id="priority_backup"
                                                                            type="checkbox" <?php if ($rSettings["priority_backup"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="probe_extra_wait"><?= $_["probe_extra_wait"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_probe_extra_wait"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="probe_extra_wait" name="probe_extra_wait"
                                                                            value="<?= htmlspecialchars($rSettings["probe_extra_wait"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="stream_fail_sleep"><?= $_["stream_fail_sleep"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_stream_fail_sleep"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="stream_fail_sleep" name="stream_fail_sleep"
                                                                            value="<?= htmlspecialchars($rSettings["stream_fail_sleep"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="fps_delay"><?= $_["fps_delay"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_fps_delay"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="fps_delay" name="fps_delay"
                                                                            value="<?= htmlspecialchars($rSettings["fps_delay"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="fps_check_type"><?= $_["fps_check_type"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_fps_check_type"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <select name="fps_check_type" id="fps_check_type"
                                                                            class="form-control" data-toggle="select2">
                                                                            <option<?php if ($rSettings["fps_check_type"] == "0") {
                                                                                echo " selected";
                                                                                   } ?> value="0">
                                                                                <?= $_["fps_check_type_0"] ?></option>
                                                                                <option<?php if ($rSettings["fps_check_type"] == "1") {
                                                                                    echo " selected";
                                                                                       } ?> value="1">
                                                                                    <?= $_["fps_check_type_1"] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">Off Air Videos</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="show_not_on_air_video"><?= $_["stream_down_video"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["show_this_video"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_not_on_air_video"
                                                                            id="show_not_on_air_video" type="checkbox" <?php if ($rSettings["show_not_on_air_video"] == 1) {
                                                                                echo "checked ";
                                                                                                                       } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" class="form-control"
                                                                            id="not_on_air_video_path"
                                                                            name="not_on_air_video_path"
                                                                            value="<?= htmlspecialchars($rSettings["not_on_air_video_path"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="show_banned_video"><?= $_["banned_video"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["show_this_video_banned"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_banned_video"
                                                                            id="show_banned_video" type="checkbox" <?php if ($rSettings["show_banned_video"] == 1) {
                                                                                echo "checked ";
                                                                                                                   } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" class="form-control"
                                                                            id="banned_video_path" name="banned_video_path"
                                                                            value="<?= htmlspecialchars($rSettings["banned_video_path"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="show_expired_video"><?= $_["expired_video"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["show_this_video_expired"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_expired_video"
                                                                            id="show_expired_video" type="checkbox" <?php if ($rSettings["show_expired_video"] == 1) {
                                                                                echo "checked ";
                                                                                                                    } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" class="form-control"
                                                                            id="expired_video_path"
                                                                            name="expired_video_path"
                                                                            value="<?= htmlspecialchars($rSettings["expired_video_path"]) ?>">
                                                                    </div>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">Allowed Countries <i
                                                                        data-toggle="tooltip" data-placement="top" title=""
                                                                        data-original-title="<?= $_["desc_allow_countries"] ?>"
                                                                        class="mdi mdi-information"></i></h5>
                                                                </br>
                                                                <div class="col-md-12">
                                                                    <select name="allow_countries[]" id="allow_countries"
                                                                        class="form-control select2-multiple"
                                                                        data-toggle="select2" multiple="multiple"
                                                                        data-placeholder="<?= $_["choose"] ?>...">
                                                                        <?php foreach ($rGeoCountries as $rValue => $rText) { ?>
                                                                            <option <?php if (in_array($rValue, json_decode($rSettings["allow_countries"], true))) {
                                                                                echo "selected ";
                                                                                    } ?>value="<?= $rValue ?>">
                                                                                <?= $rText ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                </br>
                                                                <h5 class="card-title mb-4">Other</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="stream_start_delay"><?= $_["stream_start_delay"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["before_starting_stream"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="stream_start_delay"
                                                                            name="stream_start_delay"
                                                                            value="<?= htmlspecialchars($rSettings["stream_start_delay"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="vod_limit_at">VOD Download Limit <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="Specify the percentage. Enter number only. Enter 0 to disable."
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="vod_limit_at" name="vod_limit_at"
                                                                            value="<?= htmlspecialchars($rSettings["vod_limit_at"]) ?>">
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit"
                                                                    class="btn btn-primary"
                                                                    value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="mag">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                </br>
                                                                <h5 class="card-title mb-4">Preferences</h5>
                                                                </br>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="show_all_category_mag"><?= $_["show_all_categories"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["show_all_mag_category_on_mag_devices"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_all_category_mag"
                                                                            id="show_all_category_mag" type="checkbox" <?php if ($rSettings["show_all_category_mag"] == 1) {
                                                                                echo "checked ";
                                                                                                                       } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="mag_security"><?= $_["mag_security"] ?> <i
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["enable_additional_mag"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="mag_security" id="mag_security"
                                                                            type="checkbox" <?php if ($rSettings["mag_security"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="always_enabled_subtitles"><?= $_["always_enabled_subtitles"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["force_subtitles"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="always_enabled_subtitles"
                                                                            id="always_enabled_subtitles" type="checkbox"
                                                                            <?php if ($rSettings["always_enabled_subtitles"] == 1) {
                                                                                echo "checked ";
                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="enable_connection_problem_indication"><?= $_["connection_problem_indication"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="enable_connection_problem_indication"
                                                                            id="enable_connection_problem_indication"
                                                                            type="checkbox" <?php if ($rSettings["enable_connection_problem_indication"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="show_tv_channel_logo"><?= $_["show_channel_logos"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_tv_channel_logo"
                                                                            id="show_tv_channel_logo" type="checkbox" <?php if ($rSettings["show_tv_channel_logo"] == 1) {
                                                                                echo "checked ";
                                                                                                                      } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="show_channel_logo_in_preview"><?= $_["show_preview_channel_logos"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_channel_logo_in_preview"
                                                                            id="show_channel_logo_in_preview"
                                                                            type="checkbox" <?php if ($rSettings["show_channel_logo_in_preview"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="stb_change_pass"><?= $_["allow_stb_pass_change"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="stb_change_pass" id="stb_change_pass"
                                                                            type="checkbox" <?php if ($rSettings["stb_change_pass"] == 1) {
                                                                                echo "checked ";
                                                                                            } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="enable_debug_stalker"><?= $_["stalker_debug"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="enable_debug_stalker"
                                                                            id="enable_debug_stalker" type="checkbox" <?php if ($rSettings["enable_debug_stalker"] == 1) {
                                                                                echo "checked ";
                                                                                                                      } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="mag_container"><?= $_["default_container"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="mag_container" id="mag_container"
                                                                            class="form-control" data-toggle="select2">
                                                                            <?php
                                                                            foreach (array("ts" => "TS", "m3u8" => "M3U8") as $rValue => $rText) { ?>
                                                                                <option <?php if ($rSettings["mag_container"] == $rValue) {
                                                                                    echo "selected ";
                                                                                        } ?>value="<?= $rValue ?>">
                                                                                    <?= $rText ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="stalker_theme"><?= $_["default_theme"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="stalker_theme" id="stalker_theme"
                                                                            class="form-control" data-toggle="select2">
                                                                            <?php
                                                                            foreach (array("default" => "Default", "digital" => "Digital", "emerald" => "Emerald", "cappucino" => "Cappucino", "ocean_blue" => "Ocean Blue") as $rValue => $rText) { ?>
                                                                                <option <?php if ($rSettings["stalker_theme"] == $rValue) {
                                                                                    echo "selected ";
                                                                                        } ?>value="<?= $rValue ?>">
                                                                                    <?= $rText ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="record_max_length"><?= $_["record_max_length"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="record_max_length" name="record_max_length"
                                                                            value="<?= htmlspecialchars($rSettings["record_max_length"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="max_local_recordings"><?= $_["max_local_recordings"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="max_local_recordings"
                                                                            name="max_local_recordings"
                                                                            value="<?= htmlspecialchars($rSettings["max_local_recordings"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="playback_limit"><?= $_["playback_limit"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="playback_limit" name="playback_limit"
                                                                            value="<?= htmlspecialchars($rSettings["playback_limit"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="disable_mag_token"><?= $_["disable_mag_token"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_disable_mag_token"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disable_mag_token"
                                                                            id="disable_mag_token" type="checkbox" <?php if ($rSettings["disable_mag_token"] == 1) {
                                                                                echo "checked ";
                                                                                                                   } ?>data-plugin="switchery"
                                                                            class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="mag_disable_ssl"><?= $_["mag_disable_ssl"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["desc_mag_disable_ssl"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="mag_disable_ssl" name="mag_disable_ssl"
                                                                            value="<?= htmlspecialchars($rSettings["mag_disable_ssl"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="allowed_stb_types"><?= $_["allowed_stb_types"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <select name="allowed_stb_types[]"
                                                                            id="allowed_stb_types"
                                                                            class="form-control select2-multiple"
                                                                            data-toggle="select2" multiple="multiple"
                                                                            data-placeholder="<?= $_["choose"] ?>...">
                                                                            <?php foreach ($rMAGs as $rMAG) { ?>
                                                                                <option <?php if (in_array($rMAG, json_decode($rSettings["allowed_stb_types"], true))) {
                                                                                    echo "selected ";
                                                                                        } ?>value="<?= $rMAG ?>">
                                                                                    <?= $rMAG ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="allowed_stb_types_for_local_recording"><?= $_["allowed_stb_recording"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <select
                                                                            name="allowed_stb_types_for_local_recording[]"
                                                                            id="allowed_stb_types_for_local_recording"
                                                                            class="form-control select2-multiple"
                                                                            data-toggle="select2" multiple="multiple"
                                                                            data-placeholder="<?= $_["choose"] ?>...">
                                                                            <?php foreach ($rMAGs as $rMAG) { ?>
                                                                                <option <?php if (in_array($rMAG, json_decode($rSettings["allowed_stb_types_for_local_recording"], true))) {
                                                                                    echo "selected ";
                                                                                        } ?>value="<?= $rMAG ?>">
                                                                                    <?= $rMAG ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit"
                                                                    class="btn btn-primary"
                                                                    value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php }
                                                if (hasPermissions("adv", "database")) { ?>
                                                    <div class="tab-pane" id="backups">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="automatic_backups"><?= $_["automatic_backups"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="automatic_backups"
                                                                            id="automatic_backups" class="form-control"
                                                                            data-toggle="select2">
                                                                            <?php foreach (array("off" => "Off", "hourly" => "Hourly", "daily" => "Daily", "weekly" => "Weekly", "monthly" => "Monthly") as $rType => $rText) { ?>
                                                                                <option<?php if ($rSettings["automatic_backups"] == $rType) {
                                                                                    echo " selected";
                                                                                       } ?>
                                                                                    value="<?= $rType ?>">
                                                                                    <?= $rText ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="backups_to_keep"><?= $_["backups_to_keep"] ?>
                                                                        <i data-toggle="tooltip" data-placement="top"
                                                                            title=""
                                                                            data-original-title="<?= $_["enter_for_unlimited"] ?>"
                                                                            class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control"
                                                                            id="backups_to_keep" name="backups_to_keep"
                                                                            value="<?= htmlspecialchars($rAdminSettings["backups_to_keep"] ? $rAdminSettings["backups_to_keep"] : 0) ?>">
                                                                    </div>
                                                                </div>
                                                                <table class="table table-borderless mb-0"
                                                                    id="datatable-backups">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th class="text-center"><?= $_["date"] ?></th>
                                                                            <th class="text-center"><?= $_["filename"] ?>
                                                                            </th>
                                                                            <th class="text-center"><?= $_["filesize"] ?>
                                                                            </th>
                                                                            <th class="text-center"><?= $_["actions"] ?>
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody></tbody>
                                                                </table>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                        <ul class="list-inline wizard mb-0" style="margin-top:30px;">
                                                            <li class="list-inline-item float-right">
                                                                <button id="create_backup" onClick="api('', 'backup')"
                                                                    class="btn btn-info"><?= $_["create_backup_now"] ?></button>
                                                                <input name="submit_settings" type="submit"
                                                                    class="btn btn-primary"
                                                                    value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <!-- <div class="tab-pane" id="infos">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="bg-soft-light border-light border">
                                                                            <div class="row">
                                                                                <div class="col-md-12">
                                                                                    <p
                                                                                        class="text-muted mb-0 mt-3 text-left">
                                                                                        </small><b><a
                                                                                                class="text-dark"><?= $rInfosUpdate["title"][0] ?></a></b>
                                                                                    </p>
                                                                                    <h5 class="font-weight-normal mb-3">
                                                                                        <span><?= $rInfosUpdate["infos"][0] ?><sup
                                                                                                class="font-13">
                                                                                                <?= $rInfosUpdate["infos"][1] ?></sup></span>
                                                                                    </h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> 
                                                    </div>-->
                                                    <div class="tab-pane" id="database">
                                                        <div class="row">
                                                            <iframe width="100%" height="650px" src="./database.php"
                                                                style="overflow-x:hidden;border:0px;"></iframe>
                                                        </div> <!-- end row -->
                                                    </div>
                                                <?php } ?>
                                            </div> <!-- tab-content -->
                                        </div> <!-- end #basicwizard-->
                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                                <div class="modal fade bs-domains" tabindex="-1" role="dialog"
                                    aria-labelledby="modalLabel" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="modalLabel"><?= $_["domain_list"] ?></h4>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-hidden="true">×</button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="sub-header"><?= $_["ensure_the_following_domains"] ?></p>
                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th><?= $_["type_reseller"] ?></th>
                                                                <th><?= $_["domaine_name"] ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (strlen($rServers[$_INFO["server_id"]]["server_ip"]) > 0) { ?>
                                                                <tr>
                                                                    <td><?= $_["server_ip"] ?></td>
                                                                    <td><?= $rServers[$_INFO["server_id"]]["server_ip"] ?>
                                                                    </td>
                                                                </tr>
                                                            <?php }
                                                            if (strlen($rServers[$_INFO["server_id"]]["vpn_ip"]) > 0) { ?>
                                                                <tr>
                                                                    <td><?= $_["server_vpn"] ?></td>
                                                                    <td><?= $rServers[$_INFO["server_id"]]["vpn_ip"] ?></td>
                                                                </tr>
                                                            <?php }
                                                            if (strlen($rServers[$_INFO["server_id"]]["domain_name"]) > 0) { ?>
                                                                <tr>
                                                                    <td><?= $_["server_domain"] ?></td>
                                                                    <td><?= $rServers[$_INFO["server_id"]]["domain_name"] ?>
                                                                    </td>
                                                                </tr>
                                                            <?php }
                                                            $ipTV_db_admin->query("SELECT `username`, `reseller_dns` FROM `reg_users` WHERE `reseller_dns` <> '' AND `verified` = 1 ORDER BY `username` ASC;");
                                                            if ($ipTV_db_admin->num_rows() > 0) {
                                                                foreach ($ipTV_db_admin->get_rows() as $row) { ?>
                                                                    <tr>
                                                                        <td><?= $row["username"] ?></td>
                                                                        <td><?= $row["reseller_dns"] ?></td>
                                                                    </tr>
                                                                <?php }
                                                            } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->
                            </div> <!-- end col -->
                        </div>
                    </form>
                </div> <!-- end container -->
            </div>
            <!-- end wrapper -->
            <?php if ($rSettings["sidebar"]) {
                echo "</div>";
            } ?>
            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 copyright text-center"><?= getFooter() ?></div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->
            <script src="assets/js/minified/sceditor.min.js"></script>
            <script src="assets/js/minified/formats/xhtml.js"></script>
            <script src="assets/js/vendor.min.js"></script>
            <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
            <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
            <script src="assets/libs/switchery/switchery.min.js"></script>
            <script src="assets/libs/select2/select2.min.js"></script>
            <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
            <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
            <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
            <script src="assets/libs/moment/moment.min.js"></script>
            <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
            <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
            <script src="assets/js/pages/form-wizard.init.js"></script>
            <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
            <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
            <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
            <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
            <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
            <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
            <script src="assets/libs/datatables/buttons.html5.min.js"></script>
            <script src="assets/libs/datatables/buttons.flash.min.js"></script>
            <script src="assets/libs/datatables/buttons.print.min.js"></script>
            <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
            <script src="assets/libs/datatables/dataTables.select.min.js"></script>
            <script src="assets/js/app.min.js"></script>

            <script>
                function api(rID, rType) {
                    if (rType == "delete") {
                        if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_backup"] ?>') == false) {
                            return;
                        }
                    } else if (rType == "restore") {
                        if (confirm('<?= $_["are_you_sure_you_want_to_restore_from_this_backup"] ?>') == false) {
                            return;
                        } else {
                            $.toast("<?= $_["restoring_backup"] ?>");
                            $(".content-page").fadeOut();
                        }
                    } else if (rType == "backup") {
                        $("#create_backup").attr("disabled", true);
                    }
                    $.getJSON("./api.php?action=backup&sub=" + rType + "&filename=" + encodeURIComponent(rID), function (
                        data) {
                        if (data.result === true) {
                            if (rType == "delete") {
                                $.each($('.tooltip'), function (index, element) {
                                    $(this).remove();
                                });
                                $('[data-toggle="tooltip"]').tooltip();
                                $.toast("<?= $_["backup_successfully_deleted"] ?>");
                            } else if (rType == "restore") {
                                $.toast("<?= $_["restored_from_backup"] ?>");
                                $(".content-page").fadeIn();
                            } else if (rType == "backup") {
                                $.toast("<?= $_["backup_has_been_successfully_generated"] ?>");
                                $("#create_backup").attr("disabled", false);
                            }
                            $("#datatable-backups").DataTable().ajax.reload(null, false);
                        } else {
                            $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
                            if (rType == "backup") {
                                $("#create_backup").attr("disabled", false);
                            }
                            if (!$(".content-page").is(":visible")) {
                                $(".content-page").fadeIn();
                            }
                        }
                    });
                }

                (function ($) {
                    $.fn.inputFilter = function (inputFilter) {
                        return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
                            if (inputFilter(this.value)) {
                                this.oldValue = this.value;
                                this.oldSelectionStart = this.selectionStart;
                                this.oldSelectionEnd = this.selectionEnd;
                            } else if (this.hasOwnProperty("oldValue")) {
                                this.value = this.oldValue;
                                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                            }
                        });
                    };
                }(jQuery));

                $(document).ready(function () {
                    $('select').select2({
                        width: '100%'
                    });
                    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                    elems.forEach(function (html) {
                        var switchery = new Switchery(html);
                    });
                    $(window).keypress(function (event) {
                        if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                    });

                    $("#datatable-backups").DataTable({
                        language: {
                            paginate: {
                                previous: "<i class='mdi mdi-chevron-left'>",
                                next: "<i class='mdi mdi-chevron-right'>"
                            }
                        },
                        drawCallback: function () {
                            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                            $('[data-toggle="tooltip"]').tooltip();
                        },
                        bInfo: false,
                        paging: false,
                        searching: false,
                        bSort: false,
                        responsive: false,
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "./table_search.php",
                            "data": function (d) {
                                d.id = "backups"
                            }
                        },
                        order: [
                            [0, "desc"]
                        ],
                        columnDefs: [{
                            "className": "dt-center",
                            "targets": [0, 1, 2, 3]
                        }],

                    });
                    $("#datatable-backups").css("width", "100%");
                    $("form").attr('autocomplete', 'off');
                    $("#flood_limit").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#user_auto_kick_hours").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#probesize").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#stream_max_analyze").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#client_prebuffer").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#restreamer_prebuffer").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#backups_to_keep").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                });
            </script>
            </body>

            </html>