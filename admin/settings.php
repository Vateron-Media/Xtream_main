<?php
include "session.php";
include "functions.php";
if (!$rPermissions["is_admin"]) {
    exit;
}
if ((!hasPermissions("adv", "settings")) && (!hasPermissions("adv", "database"))) {
    exit;
}

$rTMDBLanguages = array("" => "Default - EN", "aa" => "Afar", "af" => "Afrikaans", "ak" => "Akan", "an" => "Aragonese", "as" => "Assamese", "av" => "Avaric", "ae" => "Avestan", "ay" => "Aymara", "az" => "Azerbaijani", "ba" => "Bashkir", "bm" => "Bambara", "bi" => "Bislama", "bo" => "Tibetan", "br" => "Breton", "ca" => "Catalan", "cs" => "Czech", "ce" => "Chechen", "cu" => "Slavic", "cv" => "Chuvash", "kw" => "Cornish", "co" => "Corsican", "cr" => "Cree", "cy" => "Welsh", "da" => "Danish", "de" => "German", "dv" => "Divehi", "dz" => "Dzongkha", "eo" => "Esperanto", "et" => "Estonian", "eu" => "Basque", "fo" => "Faroese", "fj" => "Fijian", "fi" => "Finnish", "fr" => "French", "fy" => "Frisian", "ff" => "Fulah", "gd" => "Gaelic", "ga" => "Irish", "gl" => "Galician", "gv" => "Manx", "gn" => "Guarani", "gu" => "Gujarati", "ht" => "Haitian", "ha" => "Hausa", "sh" => "Serbo-Croatian", "hz" => "Herero", "ho" => "Hiri Motu", "hr" => "Croatian", "hu" => "Hungarian", "ig" => "Igbo", "io" => "Ido", "ii" => "Yi", "iu" => "Inuktitut", "ie" => "Interlingue", "ia" => "Interlingua", "id" => "Indonesian", "ik" => "Inupiaq", "is" => "Icelandic", "it" => "Italian", "ja" => "Japanese", "kl" => "Kalaallisut", "kn" => "Kannada", "ks" => "Kashmiri", "kr" => "Kanuri", "kk" => "Kazakh", "km" => "Khmer", "ki" => "Kikuyu", "rw" => "Kinyarwanda", "ky" => "Kirghiz", "kv" => "Komi", "kg" => "Kongo", "ko" => "Korean", "kj" => "Kuanyama", "ku" => "Kurdish", "lo" => "Lao", "la" => "Latin", "lv" => "Latvian", "li" => "Limburgish", "ln" => "Lingala", "lt" => "Lithuanian", "lb" => "Letzeburgesch", "lu" => "Luba-Katanga", "lg" => "Ganda", "mh" => "Marshall", "ml" => "Malayalam", "mr" => "Marathi", "mg" => "Malagasy", "mt" => "Maltese", "mo" => "Moldavian", "mn" => "Mongolian", "mi" => "Maori", "ms" => "Malay", "my" => "Burmese", "na" => "Nauru", "nv" => "Navajo", "nr" => "Ndebele", "nd" => "Ndebele", "ng" => "Ndonga", "ne" => "Nepali", "nl" => "Dutch", "nn" => "Norwegian Nynorsk", "nb" => "Norwegian Bokmal", "no" => "Norwegian", "ny" => "Chichewa", "oc" => "Occitan", "oj" => "Ojibwa", "or" => "Oriya", "om" => "Oromo", "os" => "Ossetian; Ossetic", "pi" => "Pali", "pl" => "Polish", "pt" => "Portuguese", "pt-BR" => "Portuguese - Brazil", "qu" => "Quechua", "rm" => "Raeto-Romance", "ro" => "Romanian", "rn" => "Rundi", "ru" => "Russian", "sg" => "Sango", "sa" => "Sanskrit", "si" => "Sinhalese", "sk" => "Slovak", "sl" => "Slovenian", "se" => "Northern Sami", "sm" => "Samoan", "sn" => "Shona", "sd" => "Sindhi", "so" => "Somali", "st" => "Sotho", "es" => "Spanish", "sq" => "Albanian", "sc" => "Sardinian", "sr" => "Serbian", "ss" => "Swati", "su" => "Sundanese", "sw" => "Swahili", "sv" => "Swedish", "ty" => "Tahitian", "ta" => "Tamil", "tt" => "Tatar", "te" => "Telugu", "tg" => "Tajik", "tl" => "Tagalog", "th" => "Thai", "ti" => "Tigrinya", "to" => "Tonga", "tn" => "Tswana", "ts" => "Tsonga", "tk" => "Turkmen", "tr" => "Turkish", "tw" => "Twi", "ug" => "Uighur", "uk" => "Ukrainian", "ur" => "Urdu", "uz" => "Uzbek", "ve" => "Venda", "vi" => "Vietnamese", "vo" => "Volapük", "wa" => "Walloon", "wo" => "Wolof", "xh" => "Xhosa", "yi" => "Yiddish", "za" => "Zhuang", "zu" => "Zulu", "ab" => "Abkhazian", "zh" => "Mandarin", "ps" => "Pushto", "am" => "Amharic", "ar" => "Arabic", "bg" => "Bulgarian", "cn" => "Cantonese", "mk" => "Macedonian", "el" => "Greek", "fa" => "Persian", "he" => "Hebrew", "hi" => "Hindi", "hy" => "Armenian", "en" => "English", "ee" => "Ewe", "ka" => "Georgian", "pa" => "Punjabi", "bn" => "Bengali", "bs" => "Bosnian", "ch" => "Chamorro", "be" => "Belarusian", "yo" => "Yoruba");
$rMAGs = array("MAG200", "MAG245", "MAG245D", "MAG250", "MAG254", "MAG255", "MAG256", "MAG257", "MAG260", "MAG270", "MAG275", "MAG322", "MAG322w1", "MAG322w2", "MAG323", "MAG324", "MAG324C", "MAG324w2", "MAG325", "MAG349", "MAG350", "MAG351", "MAG352", "MAG420", "MAG420w1", "MAG420w2", "MAG422", "MAG422A", "MAG422Aw1", "MAG424", "MAG424w1", "MAG424w2", "MAG424w3", "MAG424A", "MAG424Aw3", "MAG425", "MAG425A", "AuraHD", "AuraHD0", "AuraHD1", "AuraHD2", "AuraHD3", "AuraHD4", "AuraHD5", "AuraHD6", "AuraHD7", "AuraHD8", "AuraHD9", "WR320", "IM2100", "IM2100w1", "IM2100V", "IM2100VI", "IM2101", "IM2101V", "IM2101VI", "IM2101VO", "IM2101w2", "IM2102", "IM4410", "IM4410w3", "IM4411", "IM4411w1", "IM4412", "IM4414", "IM4414w1", "IP_STB_HD");

if (isset($_GET["geolite2"])) {
    if (updateGeoLite2()) {
        $_STATUS = 3;
    } else {
        $_STATUS = 2;
    }
}

if (isset($_GET["panel_version"])) {
    if (updatePanel()) {
        $_STATUS = 5;
    } else {
        $_STATUS = 4;
    }
}

if ((isset($_POST["submit_settings"])) && (hasPermissions("adv", "settings"))) {
    $rArray = getSettings();
    foreach (array("disallow_empty_user_agents", "persistent_connections", "show_all_category_mag", "show_not_on_air_video", "show_banned_video", "show_expired_video", "new_sorting_bouquet", "rtmp_random", "use_buffer", "audio_restart_loss", "save_closed_connection", "client_logs_save", "case_sensitive_line", "county_override_1st", "disallow_2nd_ip_con", "use_mdomain_in_lists", "hash_lb", "show_isps", "enable_isp_lock", "block_svp", "mag_security", "always_enabled_subtitles", "enable_connection_problem_indication", "show_tv_channel_logo", "show_channel_logo_in_preview", "stb_change_pass", "enable_debug_stalker", "priority_backup") as $rSetting) {
        if (isset($_POST[$rSetting])) {
            $rArray[$rSetting] = 1;
            unset($_POST[$rSetting]);
        } else {
            $rArray[$rSetting] = 0;
        }
    }
    if (!isset($_POST["allowed_stb_types_for_local_recording"])) {
        $rArray["allowed_stb_types_for_local_recording"] = array();
    }
    if (!isset($_POST["allowed_stb_types"])) {
        $rArray["allowed_stb_types"] = array();
    }
    if (isset($_POST["disable_trial"])) {
        $rAdminSettings["disable_trial"] = true;
        unset($_POST["disable_trial"]);
    } else {
        $rAdminSettings["disable_trial"] = false;
    }
    //next 6 lines are for reseller mag events
    if (isset($_POST["reseller_mag_events"])) {
        $rAdminSettings["reseller_mag_events"] = true;
        unset($_POST["reseller_mag_events"]);
    } else {
        $rAdminSettings["reseller_mag_events"] = false;
    }
    // previous 6 lines are for reseller mag events											
    if (isset($_POST["ip_logout"])) {
        $rAdminSettings["ip_logout"] = true;
        unset($_POST["ip_logout"]);
    } else {
        $rAdminSettings["ip_logout"] = false;
    }
    if (isset($_POST["alternate_scandir"])) {
        $rAdminSettings["alternate_scandir"] = true;
        unset($_POST["alternate_scandir"]);
    } else {
        $rAdminSettings["alternate_scandir"] = false;
    }
    if (isset($_POST["recaptcha_enable"])) {
        $rAdminSettings["recaptcha_enable"] = true;
        unset($_POST["recaptcha_enable"]);
    } else {
        $rAdminSettings["recaptcha_enable"] = false;
    }
    if (isset($_POST["download_images"])) {
        $rAdminSettings["download_images"] = true;
        unset($_POST["download_images"]);
    } else {
        $rAdminSettings["download_images"] = false;
    }
    if (isset($_POST["auto_refresh"])) {
        $rAdminSettings["auto_refresh"] = true;
        unset($_POST["auto_refresh"]);
    } else {
        $rAdminSettings["auto_refresh"] = false;
    }
    if (isset($_POST["local_api"])) {
        $rAdminSettings["local_api"] = true;
        unset($_POST["local_api"]);
    } else {
        $rAdminSettings["local_api"] = false;
    }
    if (isset($_POST["dark_mode_login"])) {
        $rAdminSettings["dark_mode_login"] = true;
        unset($_POST["dark_mode_login"]);
    } else {
        $rAdminSettings["dark_mode_login"] = false;
    }
    if (isset($_POST["dashboard_stats"])) {
        $rAdminSettings["dashboard_stats"] = true;
        unset($_POST["dashboard_stats"]);
    } else {
        $rAdminSettings["dashboard_stats"] = false;
    }
    if (isset($_POST["dashboard_world_map_live"])) {
        $rAdminSettings["dashboard_world_map_live"] = true;
        unset($_POST["dashboard_world_map_live"]);
    } else {
        $rAdminSettings["dashboard_world_map_live"] = false;
    }
    if (isset($_POST["dashboard_world_map_activity"])) {
        $rAdminSettings["dashboard_world_map_activity"] = true;
        unset($_POST["dashboard_world_map_activity"]);
    } else {
        $rAdminSettings["dashboard_world_map_activity"] = false;
    }
    if (isset($_POST["change_usernames"])) {
        $rAdminSettings["change_usernames"] = true;
        unset($_POST["change_usernames"]);
    } else {
        $rAdminSettings["change_usernames"] = false;
    }
    if (isset($_POST["change_own_dns"])) {
        $rAdminSettings["change_own_dns"] = true;
        unset($_POST["change_own_dns"]);
    } else {
        $rAdminSettings["change_own_dns"] = false;
    }
    if (isset($_POST["change_own_email"])) {
        $rAdminSettings["change_own_email"] = true;
        unset($_POST["change_own_email"]);
    } else {
        $rAdminSettings["change_own_email"] = false;
    }
    if (isset($_POST["change_own_password"])) {
        $rAdminSettings["change_own_password"] = true;
        unset($_POST["change_own_password"]);
    } else {
        $rAdminSettings["change_own_password"] = false;
    }
    if (isset($_POST["reseller_restrictions"])) {
        $rAdminSettings["reseller_restrictions"] = true;
        unset($_POST["reseller_restrictions"]);
    } else {
        $rAdminSettings["reseller_restrictions"] = false;
    }
    if (isset($_POST["google_2factor"])) {
        $rAdminSettings["google_2factor"] = true;
        unset($_POST["google_2factor"]);
    } else {
        $rAdminSettings["google_2factor"] = false;
    }
    if (isset($_POST["default_entries"])) {
        $rAdminSettings["default_entries"] = $_POST["default_entries"];
    }
    if (isset($_POST["admin_username"])) {
        $rAdminSettings["admin_username"] = $_POST["admin_username"];
        unset($_POST["admin_username"]);
    }
    if (isset($_POST["admin_password"])) {
        $rAdminSettings["admin_password"] = $_POST["admin_password"];
        unset($_POST["admin_password"]);
    }
    if (isset($_POST["tmdb_language"])) {
        $rAdminSettings["tmdb_language"] = $_POST["tmdb_language"];
        unset($_POST["tmdb_language"]);
    }
    if (isset($_POST["release_parser"])) {
        $rAdminSettings["release_parser"] = $_POST["release_parser"];
        unset($_POST["release_parser"]);
    }
    if (isset($_POST["automatic_backups"])) {
        $rAdminSettings["automatic_backups"] = $_POST["automatic_backups"];
        unset($_POST["automatic_backups"]);
    }
    if (isset($_POST["backups_to_keep"])) {
        $rAdminSettings["backups_to_keep"] = $_POST["backups_to_keep"];
        unset($_POST["backups_to_keep"]);
    }
    if (isset($_POST["change_own_lang"])) {
        $rAdminSettings["change_own_lang"] = true;
        unset($_POST["change_own_lang"]);
    } else {
        $rAdminSettings["change_own_lang"] = false;
    }
    /*if (isset($_POST["reseller_select_bouquets"])) {
        $rAdminSettings["reseller_select_bouquets"] = true;       
        unset($_POST["reseller_select_bouquets"]);
    } else {
        $rAdminSettings["reseller_select_bouquets"] = false;
    }	*/
    if (isset($_POST["active_mannuals"])) {
        $rAdminSettings["active_mannuals"] = true;
        unset($_POST["active_mannuals"]);
    } else {
        $rAdminSettings["active_mannuals"] = false;
    }
    if (isset($_POST["reseller_can_isplock"])) {
        $rAdminSettings["reseller_can_isplock"] = true;
        unset($_POST["reseller_can_isplock"]);
    } else {
        $rAdminSettings["reseller_can_isplock"] = false;
    }
    if (isset($_POST["reseller_reset_isplock"])) {
        $rAdminSettings["reseller_reset_isplock"] = true;
        unset($_POST["reseller_reset_isplock"]);
    } else {
        $rAdminSettings["reseller_reset_isplock"] = false;
    }
    if (isset($_POST["recaptcha_v2_site_key"])) {
        $rAdminSettings["recaptcha_v2_site_key"] = $_POST["recaptcha_v2_site_key"];
        unset($_POST["recaptcha_v2_site_key"]);
    }
    if (isset($_POST["recaptcha_v2_secret_key"])) {
        $rAdminSettings["recaptcha_v2_secret_key"] = $_POST["recaptcha_v2_secret_key"];
        unset($_POST["recaptcha_v2_secret_key"]);
    }
    if (isset($_POST["login_flood"])) {
        $rAdminSettings["login_flood"] = $_POST["login_flood"];
        unset($_POST["login_flood"]);
    }
    if (isset($_POST["pass_length"])) {
        $rAdminSettings["pass_length"] = $_POST["pass_length"];
        unset($_POST["pass_length"]);
    }
    if (isset($_POST["dashboard_stats_frequency"])) {
        $rAdminSettings["dashboard_stats_frequency"] = $_POST["dashboard_stats_frequency"];
        unset($_POST["dashboard_stats_frequency"]);
    }
    writeAdminSettings();
    foreach ($_POST as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    $rValues = array();
    foreach ($rArray as $rKey => $rValue) {
        if (is_array($rValue)) {
            $rValue = json_encode($rValue);
        }
        if (is_null($rValue)) {
            $rValues[] = '`' . ESC($rKey) . '` = NULL';
        } else {
            $rValues[] = '`' . ESC($rKey) . '` = \'' . ESC($rValue) . '\'';
        }
    }
    $rQuery = "UPDATE `settings` SET " . join(", ", $rValues) . ";";
    if ($db->query($rQuery)) {
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
                                <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["there_was_an_error_saving_settings"] ?>
                                    </div>
                                <?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["failed_to_update_GeoLite2"] ?>
                                    </div>
                                <?php } else if ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["geoLite2_has_been_updated"] ?>
                                    </div>
                                <?php } else if ((isset($_STATUS)) && ($_STATUS == 4)) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        Failed to update Panel! Please try again.
                                    </div>
                                <?php } else if ((isset($_STATUS)) && ($_STATUS == 5)) { ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        THE PANEL IS UPDATED...
                                    </div>
                                <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["there_was_an_error_saving_settings"] ?>
                                    </div>
                                <?php }
                                $rContext = stream_context_create(array('http' => array('timeout' => 3)));
                                $rUpdatePanel = json_decode(file_get_contents("https://raw.githubusercontent.com/Vateron-Media/Xtream_Update/main/version.json", false, $rContext), True);
                                $rInfos = array(); //json_decode(file_get_contents("http://xtream-ui.mine.nu/Update/infos.json", false, $rContext), True);
                                $rGeoLite2 = json_decode(file_get_contents("https://raw.githubusercontent.com/Vateron-Media/Xtream_Update/main/status.json", false, $rContext), True);
                                ?>
                                <?php if (version_compare($rGeoLite2["version"], $rAdminSettings["geolite2_version"])) { ?>
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["a_new_version_of_GeoLite2"] ?> (<?= $rGeoLite2["version"] ?>) <?= $_["is_available"] ?> <a href="./settings.php?geolite2"><?= $_["click_here_to_update"] ?></a>
                                    </div>
                                <?php } ?>
                                <?php if (version_compare($rUpdatePanel["main"], getScriptVer())) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        A new version (<?= $rUpdatePanel["main"] ?>) <?= $_["is_available"] ?> <a href="./settings.php?panel_version"><?= $_["click_here_to_update"] ?></a>
                                    </div>
                                <?php } ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="bg-soft-light border-light border">
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <p class="text-muted mb-0 mt-3"><?= $_["installed_version"] ?></p>
                                                    <h2 class="font-weight-normal mb-3">
                                                        <small class="mdi mdi-checkbox-blank-circle text-success align-middle mr-1"></small>
                                                        <span><?= getScriptVer() ?></span>
                                                    </h2>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="text-muted mb-0 mt-3"><?= $_["official_release"] ?></p>
                                                    <h2 class="font-weight-normal mb-3">
                                                        <small class="mdi mdi-checkbox-blank-circle text-info align-middle mr-1"></small>
                                                        <span><?= $rUpdatePanel["main"] ?></span>
                                                    </h2>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="text-muted mb-0 mt-3"><?= $_["geoLite2_version"] ?></p>
                                                    <h2 class="font-weight-normal mb-3">
                                                        <small class="mdi mdi-checkbox-blank-circle text-pink align-middle mr-1"></small>
                                                        <span><?= $rAdminSettings["geolite2_version"] ?></span>
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
                                                        <a href="#general-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["general"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#xui" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-settings mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["xtream_ui"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#reseller" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-coins mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["reseller"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#streaming" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-play mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["streaming"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#mag" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-tablet mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["mag"] ?></span>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if (hasPermissions("adv", "database")) { ?>
                                                    <li class="nav-item">
                                                        <a href="#backups" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-backup-restore mr-1"></i>
                                                            <span class="d-none d-sm-inline"><?= $_["backups"] ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#infos" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="fas fa-info mr-1"></i>
                                                            <span class="d-none d-sm-inline">Infos</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#database" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
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
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="server_name"><?= $_["server_name"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="server_name" name="server_name" value="<?= htmlspecialchars($rSettings["server_name"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="logo_url"><?= $_["logo_url"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="logo_url" name="logo_url" value="<?= htmlspecialchars($rSettings["logo_url"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="logo_url_sidebar"><?= $_["logo_url_sidebar"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="logo_url_sidebar" name="logo_url_sidebar" value="<?= htmlspecialchars($rSettings["logo_url_sidebar"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="default_timezone"><?= $_["timezone"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <select name="default_timezone" id="default_timezone" class="form-control" data-toggle="select2">
                                                                            <?php
                                                                            $rTimeZones = array("Africa/Abidjan" => "Africa/Abidjan [GMT  00:00]", "Africa/Accra" => "Africa/Accra [GMT  00:00]", "Africa/Addis_Ababa" => "Africa/Addis_Ababa [EAT +03:00]", "Africa/Algiers" => "Africa/Algiers [CET +01:00]", "Africa/Asmara" => "Africa/Asmara [EAT +03:00]", "Africa/Bamako" => "Africa/Bamako [GMT  00:00]", "Africa/Bangui" => "Africa/Bangui [WAT +01:00]", "Africa/Banjul" => "Africa/Banjul [GMT  00:00]", "Africa/Bissau" => "Africa/Bissau [GMT  00:00]", "Africa/Blantyre" => "Africa/Blantyre [CAT +02:00]", "Africa/Brazzaville" => "Africa/Brazzaville [WAT +01:00]", "Africa/Bujumbura" => "Africa/Bujumbura [CAT +02:00]", "Africa/Cairo" => "Africa/Cairo [EET +02:00]", "Africa/Casablanca" => "Africa/Casablanca [WEST +01:00]", "Africa/Ceuta" => "Africa/Ceuta [CEST +02:00]", "Africa/Conakry" => "Africa/Conakry [GMT  00:00]", "Africa/Dakar" => "Africa/Dakar [GMT  00:00]", "Africa/Dar_es_Salaam" => "Africa/Dar_es_Salaam [EAT +03:00]", "Africa/Djibouti" => "Africa/Djibouti [EAT +03:00]", "Africa/Douala" => "Africa/Douala [WAT +01:00]", "Africa/El_Aaiun" => "Africa/El_Aaiun [WEST +01:00]", "Africa/Freetown" => "Africa/Freetown [GMT  00:00]", "Africa/Gaborone" => "Africa/Gaborone [CAT +02:00]", "Africa/Harare" => "Africa/Harare [CAT +02:00]", "Africa/Johannesburg" => "Africa/Johannesburg [SAST +02:00]", "Africa/Juba" => "Africa/Juba [EAT +03:00]", "Africa/Kampala" => "Africa/Kampala [EAT +03:00]", "Africa/Khartoum" => "Africa/Khartoum [EAT +03:00]", "Africa/Kigali" => "Africa/Kigali [CAT +02:00]", "Africa/Kinshasa" => "Africa/Kinshasa [WAT +01:00]", "Africa/Lagos" => "Africa/Lagos [WAT +01:00]", "Africa/Libreville" => "Africa/Libreville [WAT +01:00]", "Africa/Lome" => "Africa/Lome [GMT  00:00]", "Africa/Luanda" => "Africa/Luanda [WAT +01:00]", "Africa/Lubumbashi" => "Africa/Lubumbashi [CAT +02:00]", "Africa/Lusaka" => "Africa/Lusaka [CAT +02:00]", "Africa/Malabo" => "Africa/Malabo [WAT +01:00]", "Africa/Maputo" => "Africa/Maputo [CAT +02:00]", "Africa/Maseru" => "Africa/Maseru [SAST +02:00]", "Africa/Mbabane" => "Africa/Mbabane [SAST +02:00]", "Africa/Mogadishu" => "Africa/Mogadishu [EAT +03:00]", "Africa/Monrovia" => "Africa/Monrovia [GMT  00:00]", "Africa/Nairobi" => "Africa/Nairobi [EAT +03:00]", "Africa/Ndjamena" => "Africa/Ndjamena [WAT +01:00]", "Africa/Niamey" => "Africa/Niamey [WAT +01:00]", "Africa/Nouakchott" => "Africa/Nouakchott [GMT  00:00]", "Africa/Ouagadougou" => "Africa/Ouagadougou [GMT  00:00]", "Africa/Porto-Novo" => "Africa/Porto-Novo [WAT +01:00]", "Africa/Sao_Tome" => "Africa/Sao_Tome [GMT  00:00]", "Africa/Tripoli" => "Africa/Tripoli [EET +02:00]", "Africa/Tunis" => "Africa/Tunis [CET +01:00]", "Africa/Windhoek" => "Africa/Windhoek [WAST +02:00]", "America/Adak" => "America/Adak [HADT -09:00]", "America/Anchorage" => "America/Anchorage [AKDT -08:00]", "America/Anguilla" => "America/Anguilla [AST -04:00]", "America/Antigua" => "America/Antigua [AST -04:00]", "America/Araguaina" => "America/Araguaina [BRT -03:00]", "America/Argentina/Buenos_Aires" => "America/Argentina/Buenos_Aires [ART -03:00]", "America/Argentina/Catamarca" => "America/Argentina/Catamarca [ART -03:00]", "America/Argentina/Cordoba" => "America/Argentina/Cordoba [ART -03:00]", "America/Argentina/Jujuy" => "America/Argentina/Jujuy [ART -03:00]", "America/Argentina/La_Rioja" => "America/Argentina/La_Rioja [ART -03:00]", "America/Argentina/Mendoza" => "America/Argentina/Mendoza [ART -03:00]", "America/Argentina/Rio_Gallegos" => "America/Argentina/Rio_Gallegos [ART -03:00]", "America/Argentina/Salta" => "America/Argentina/Salta [ART -03:00]", "America/Argentina/San_Juan" => "America/Argentina/San_Juan [ART -03:00]", "America/Argentina/San_Luis" => "America/Argentina/San_Luis [ART -03:00]", "America/Argentina/Tucuman" => "America/Argentina/Tucuman [ART -03:00]", "America/Argentina/Ushuaia" => "America/Argentina/Ushuaia [ART -03:00]", "America/Aruba" => "America/Aruba [AST -04:00]", "America/Asuncion" => "America/Asuncion [PYT -04:00]", "America/Atikokan" => "America/Atikokan [EST -05:00]", "America/Bahia" => "America/Bahia [BRT -03:00]", "America/Bahia_Banderas" => "America/Bahia_Banderas [CDT -05:00]", "America/Barbados" => "America/Barbados [AST -04:00]", "America/Belem" => "America/Belem [BRT -03:00]", "America/Belize" => "America/Belize [CST -06:00]", "America/Blanc-Sablon" => "America/Blanc-Sablon [AST -04:00]", "America/Boa_Vista" => "America/Boa_Vista [AMT -04:00]", "America/Bogota" => "America/Bogota [COT -05:00]", "America/Boise" => "America/Boise [MDT -06:00]", "America/Cambridge_Bay" => "America/Cambridge_Bay [MDT -06:00]", "America/Campo_Grande" => "America/Campo_Grande [AMT -04:00]", "America/Cancun" => "America/Cancun [CDT -05:00]", "America/Caracas" => "America/Caracas [VET -04:30]", "America/Cayenne" => "America/Cayenne [GFT -03:00]", "America/Cayman" => "America/Cayman [EST -05:00]", "America/Chicago" => "America/Chicago [CDT -05:00]", "America/Chihuahua" => "America/Chihuahua [MDT -06:00]", "America/Costa_Rica" => "America/Costa_Rica [CST -06:00]", "America/Creston" => "America/Creston [MST -07:00]", "America/Cuiaba" => "America/Cuiaba [AMT -04:00]", "America/Curacao" => "America/Curacao [AST -04:00]", "America/Danmarkshavn" => "America/Danmarkshavn [GMT  00:00]", "America/Dawson" => "America/Dawson [PDT -07:00]", "America/Dawson_Creek" => "America/Dawson_Creek [MST -07:00]", "America/Denver" => "America/Denver [MDT -06:00]", "America/Detroit" => "America/Detroit [EDT -04:00]", "America/Dominica" => "America/Dominica [AST -04:00]", "America/Edmonton" => "America/Edmonton [MDT -06:00]", "America/Eirunepe" => "America/Eirunepe [ACT -05:00]", "America/El_Salvador" => "America/El_Salvador [CST -06:00]", "America/Fortaleza" => "America/Fortaleza [BRT -03:00]", "America/Glace_Bay" => "America/Glace_Bay [ADT -03:00]", "America/Godthab" => "America/Godthab [WGST -02:00]", "America/Goose_Bay" => "America/Goose_Bay [ADT -03:00]", "America/Grand_Turk" => "America/Grand_Turk [AST -04:00]", "America/Grenada" => "America/Grenada [AST -04:00]", "America/Guadeloupe" => "America/Guadeloupe [AST -04:00]", "America/Guatemala" => "America/Guatemala [CST -06:00]", "America/Guayaquil" => "America/Guayaquil [ECT -05:00]", "America/Guyana" => "America/Guyana [GYT -04:00]", "America/Halifax" => "America/Halifax [ADT -03:00]", "America/Havana" => "America/Havana [CDT -04:00]", "America/Hermosillo" => "America/Hermosillo [MST -07:00]", "America/Indiana/Indianapolis" => "America/Indiana/Indianapolis [EDT -04:00]", "America/Indiana/Knox" => "America/Indiana/Knox [CDT -05:00]", "America/Indiana/Marengo" => "America/Indiana/Marengo [EDT -04:00]", "America/Indiana/Petersburg" => "America/Indiana/Petersburg [EDT -04:00]", "America/Indiana/Tell_City" => "America/Indiana/Tell_City [CDT -05:00]", "America/Indiana/Vevay" => "America/Indiana/Vevay [EDT -04:00]", "America/Indiana/Vincennes" => "America/Indiana/Vincennes [EDT -04:00]", "America/Indiana/Winamac" => "America/Indiana/Winamac [EDT -04:00]", "America/Inuvik" => "America/Inuvik [MDT -06:00]", "America/Iqaluit" => "America/Iqaluit [EDT -04:00]", "America/Jamaica" => "America/Jamaica [EST -05:00]", "America/Juneau" => "America/Juneau [AKDT -08:00]", "America/Kentucky/Louisville" => "America/Kentucky/Louisville [EDT -04:00]", "America/Kentucky/Monticello" => "America/Kentucky/Monticello [EDT -04:00]", "America/Kralendijk" => "America/Kralendijk [AST -04:00]", "America/La_Paz" => "America/La_Paz [BOT -04:00]", "America/Lima" => "America/Lima [PET -05:00]", "America/Los_Angeles" => "America/Los_Angeles [PDT -07:00]", "America/Lower_Princes" => "America/Lower_Princes [AST -04:00]", "America/Maceio" => "America/Maceio [BRT -03:00]", "America/Managua" => "America/Managua [CST -06:00]", "America/Manaus" => "America/Manaus [AMT -04:00]", "America/Marigot" => "America/Marigot [AST -04:00]", "America/Martinique" => "America/Martinique [AST -04:00]", "America/Matamoros" => "America/Matamoros [CDT -05:00]", "America/Mazatlan" => "America/Mazatlan [MDT -06:00]", "America/Menominee" => "America/Menominee [CDT -05:00]", "America/Merida" => "America/Merida [CDT -05:00]", "America/Metlakatla" => "America/Metlakatla [PST -08:00]", "America/Mexico_City" => "America/Mexico_City [CDT -05:00]", "America/Miquelon" => "America/Miquelon [PMDT -02:00]", "America/Moncton" => "America/Moncton [ADT -03:00]", "America/Monterrey" => "America/Monterrey [CDT -05:00]", "America/Montevideo" => "America/Montevideo [UYT -03:00]", "America/Montserrat" => "America/Montserrat [AST -04:00]", "America/Nassau" => "America/Nassau [EDT -04:00]", "America/New_York" => "America/New_York [EDT -04:00]", "America/Nipigon" => "America/Nipigon [EDT -04:00]", "America/Nome" => "America/Nome [AKDT -08:00]", "America/Noronha" => "America/Noronha [FNT -02:00]", "America/North_Dakota/Beulah" => "America/North_Dakota/Beulah [CDT -05:00]", "America/North_Dakota/Center" => "America/North_Dakota/Center [CDT -05:00]", "America/North_Dakota/New_Salem" => "America/North_Dakota/New_Salem [CDT -05:00]", "America/Ojinaga" => "America/Ojinaga [MDT -06:00]", "America/Panama" => "America/Panama [EST -05:00]", "America/Pangnirtung" => "America/Pangnirtung [EDT -04:00]", "America/Paramaribo" => "America/Paramaribo [SRT -03:00]", "America/Phoenix" => "America/Phoenix [MST -07:00]", "America/Port-au-Prince" => "America/Port-au-Prince [EDT -04:00]", "America/Port_of_Spain" => "America/Port_of_Spain [AST -04:00]", "America/Porto_Velho" => "America/Porto_Velho [AMT -04:00]", "America/Puerto_Rico" => "America/Puerto_Rico [AST -04:00]", "America/Rainy_River" => "America/Rainy_River [CDT -05:00]", "America/Rankin_Inlet" => "America/Rankin_Inlet [CDT -05:00]", "America/Recife" => "America/Recife [BRT -03:00]", "America/Regina" => "America/Regina [CST -06:00]", "America/Resolute" => "America/Resolute [CDT -05:00]", "America/Rio_Branco" => "America/Rio_Branco [ACT -05:00]", "America/Santa_Isabel" => "America/Santa_Isabel [PDT -07:00]", "America/Santarem" => "America/Santarem [BRT -03:00]", "America/Santiago" => "America/Santiago [CLST -03:00]", "America/Santo_Domingo" => "America/Santo_Domingo [AST -04:00]", "America/Sao_Paulo" => "America/Sao_Paulo [BRT -03:00]", "America/Scoresbysund" => "America/Scoresbysund [EGST  00:00]", "America/Sitka" => "America/Sitka [AKDT -08:00]", "America/St_Barthelemy" => "America/St_Barthelemy [AST -04:00]", "America/St_Johns" => "America/St_Johns [NDT -02:30]", "America/St_Kitts" => "America/St_Kitts [AST -04:00]", "America/St_Lucia" => "America/St_Lucia [AST -04:00]", "America/St_Thomas" => "America/St_Thomas [AST -04:00]", "America/St_Vincent" => "America/St_Vincent [AST -04:00]", "America/Swift_Current" => "America/Swift_Current [CST -06:00]", "America/Tegucigalpa" => "America/Tegucigalpa [CST -06:00]", "America/Thule" => "America/Thule [ADT -03:00]", "America/Thunder_Bay" => "America/Thunder_Bay [EDT -04:00]", "America/Tijuana" => "America/Tijuana [PDT -07:00]", "America/Toronto" => "America/Toronto [EDT -04:00]", "America/Tortola" => "America/Tortola [AST -04:00]", "America/Vancouver" => "America/Vancouver [PDT -07:00]", "America/Whitehorse" => "America/Whitehorse [PDT -07:00]", "America/Winnipeg" => "America/Winnipeg [CDT -05:00]", "America/Yakutat" => "America/Yakutat [AKDT -08:00]", "America/Yellowknife" => "America/Yellowknife [MDT -06:00]", "Antarctica/Casey" => "Antarctica/Casey [AWST +08:00]", "Antarctica/Davis" => "Antarctica/Davis [DAVT +07:00]", "Antarctica/DumontDUrville" => "Antarctica/DumontDUrville [DDUT +10:00]", "Antarctica/Macquarie" => "Antarctica/Macquarie [MIST +11:00]", "Antarctica/Mawson" => "Antarctica/Mawson [MAWT +05:00]", "Antarctica/McMurdo" => "Antarctica/McMurdo [NZDT +13:00]", "Antarctica/Palmer" => "Antarctica/Palmer [CLST -03:00]", "Antarctica/Rothera" => "Antarctica/Rothera [ROTT -03:00]", "Antarctica/Syowa" => "Antarctica/Syowa [SYOT +03:00]", "Antarctica/Troll" => "Antarctica/Troll [CEST +02:00]", "Antarctica/Vostok" => "Antarctica/Vostok [VOST +06:00]", "Arctic/Longyearbyen" => "Arctic/Longyearbyen [CEST +02:00]", "Asia/Aden" => "Asia/Aden [AST +03:00]", "Asia/Almaty" => "Asia/Almaty [ALMT +06:00]", "Asia/Amman" => "Asia/Amman [EEST +03:00]", "Asia/Anadyr" => "Asia/Anadyr [ANAT +12:00]", "Asia/Aqtau" => "Asia/Aqtau [AQTT +05:00]", "Asia/Aqtobe" => "Asia/Aqtobe [AQTT +05:00]", "Asia/Ashgabat" => "Asia/Ashgabat [TMT +05:00]", "Asia/Baghdad" => "Asia/Baghdad [AST +03:00]", "Asia/Bahrain" => "Asia/Bahrain [AST +03:00]", "Asia/Baku" => "Asia/Baku [AZST +05:00]", "Asia/Bangkok" => "Asia/Bangkok [ICT +07:00]", "Asia/Beirut" => "Asia/Beirut [EEST +03:00]", "Asia/Bishkek" => "Asia/Bishkek [KGT +06:00]", "Asia/Brunei" => "Asia/Brunei [BNT +08:00]", "Asia/Chita" => "Asia/Chita [IRKT +08:00]", "Asia/Choibalsan" => "Asia/Choibalsan [CHOT +08:00]", "Asia/Colombo" => "Asia/Colombo [IST +05:30]", "Asia/Damascus" => "Asia/Damascus [EEST +03:00]", "Asia/Dhaka" => "Asia/Dhaka [BDT +06:00]", "Asia/Dili" => "Asia/Dili [TLT +09:00]", "Asia/Dubai" => "Asia/Dubai [GST +04:00]", "Asia/Dushanbe" => "Asia/Dushanbe [TJT +05:00]", "Asia/Gaza" => "Asia/Gaza [EET +02:00]", "Asia/Hebron" => "Asia/Hebron [EET +02:00]", "Asia/Ho_Chi_Minh" => "Asia/Ho_Chi_Minh [ICT +07:00]", "Asia/Hong_Kong" => "Asia/Hong_Kong [HKT +08:00]", "Asia/Hovd" => "Asia/Hovd [HOVT +07:00]", "Asia/Irkutsk" => "Asia/Irkutsk [IRKT +08:00]", "Asia/Jakarta" => "Asia/Jakarta [WIB +07:00]", "Asia/Jayapura" => "Asia/Jayapura [WIT +09:00]", "Asia/Jerusalem" => "Asia/Jerusalem [IDT +03:00]", "Asia/Kabul" => "Asia/Kabul [AFT +04:30]", "Asia/Kamchatka" => "Asia/Kamchatka [PETT +12:00]", "Asia/Karachi" => "Asia/Karachi [PKT +05:00]", "Asia/Kathmandu" => "Asia/Kathmandu [NPT +05:45]", "Asia/Khandyga" => "Asia/Khandyga [YAKT +09:00]", "Asia/Kolkata" => "Asia/Kolkata [IST +05:30]", "Asia/Krasnoyarsk" => "Asia/Krasnoyarsk [KRAT +07:00]", "Asia/Kuala_Lumpur" => "Asia/Kuala_Lumpur [MYT +08:00]", "Asia/Kuching" => "Asia/Kuching [MYT +08:00]", "Asia/Kuwait" => "Asia/Kuwait [AST +03:00]", "Asia/Macau" => "Asia/Macau [CST +08:00]", "Asia/Magadan" => "Asia/Magadan [MAGT +10:00]", "Asia/Makassar" => "Asia/Makassar [WITA +08:00]", "Asia/Manila" => "Asia/Manila [PHT +08:00]", "Asia/Muscat" => "Asia/Muscat [GST +04:00]", "Asia/Nicosia" => "Asia/Nicosia [EEST +03:00]", "Asia/Novokuznetsk" => "Asia/Novokuznetsk [KRAT +07:00]", "Asia/Novosibirsk" => "Asia/Novosibirsk [NOVT +06:00]", "Asia/Omsk" => "Asia/Omsk [OMST +06:00]", "Asia/Oral" => "Asia/Oral [ORAT +05:00]", "Asia/Phnom_Penh" => "Asia/Phnom_Penh [ICT +07:00]", "Asia/Pontianak" => "Asia/Pontianak [WIB +07:00]", "Asia/Pyongyang" => "Asia/Pyongyang [KST +09:00]", "Asia/Qatar" => "Asia/Qatar [AST +03:00]", "Asia/Qyzylorda" => "Asia/Qyzylorda [QYZT +06:00]", "Asia/Rangoon" => "Asia/Rangoon [MMT +06:30]", "Asia/Riyadh" => "Asia/Riyadh [AST +03:00]", "Asia/Sakhalin" => "Asia/Sakhalin [SAKT +10:00]", "Asia/Samarkand" => "Asia/Samarkand [UZT +05:00]", "Asia/Seoul" => "Asia/Seoul [KST +09:00]", "Asia/Shanghai" => "Asia/Shanghai [CST +08:00]", "Asia/Singapore" => "Asia/Singapore [SGT +08:00]", "Asia/Srednekolymsk" => "Asia/Srednekolymsk [SRET +11:00]", "Asia/Taipei" => "Asia/Taipei [CST +08:00]", "Asia/Tashkent" => "Asia/Tashkent [UZT +05:00]", "Asia/Tbilisi" => "Asia/Tbilisi [GET +04:00]", "Asia/Tehran" => "Asia/Tehran [IRST +03:30]", "Asia/Thimphu" => "Asia/Thimphu [BTT +06:00]", "Asia/Tokyo" => "Asia/Tokyo [JST +09:00]", "Asia/Ulaanbaatar" => "Asia/Ulaanbaatar [ULAT +08:00]", "Asia/Urumqi" => "Asia/Urumqi [XJT +06:00]", "Asia/Ust-Nera" => "Asia/Ust-Nera [VLAT +10:00]", "Asia/Vientiane" => "Asia/Vientiane [ICT +07:00]", "Asia/Vladivostok" => "Asia/Vladivostok [VLAT +10:00]", "Asia/Yakutsk" => "Asia/Yakutsk [YAKT +09:00]", "Asia/Yekaterinburg" => "Asia/Yekaterinburg [YEKT +05:00]", "Asia/Yerevan" => "Asia/Yerevan [AMT +04:00]", "Atlantic/Azores" => "Atlantic/Azores [AZOST  00:00]", "Atlantic/Bermuda" => "Atlantic/Bermuda [ADT -03:00]", "Atlantic/Canary" => "Atlantic/Canary [WEST +01:00]", "Atlantic/Cape_Verde" => "Atlantic/Cape_Verde [CVT -01:00]", "Atlantic/Faroe" => "Atlantic/Faroe [WEST +01:00]", "Atlantic/Madeira" => "Atlantic/Madeira [WEST +01:00]", "Atlantic/Reykjavik" => "Atlantic/Reykjavik [GMT  00:00]", "Atlantic/South_Georgia" => "Atlantic/South_Georgia [GST -02:00]", "Atlantic/St_Helena" => "Atlantic/St_Helena [GMT  00:00]", "Atlantic/Stanley" => "Atlantic/Stanley [FKST -03:00]", "Australia/Adelaide" => "Australia/Adelaide [ACDT +10:30]", "Australia/Brisbane" => "Australia/Brisbane [AEST +10:00]", "Australia/Broken_Hill" => "Australia/Broken_Hill [ACDT +10:30]", "Australia/Currie" => "Australia/Currie [AEDT +11:00]", "Australia/Darwin" => "Australia/Darwin [ACST +09:30]", "Australia/Eucla" => "Australia/Eucla [ACWST +08:45]", "Australia/Hobart" => "Australia/Hobart [AEDT +11:00]", "Australia/Lindeman" => "Australia/Lindeman [AEST +10:00]", "Australia/Lord_Howe" => "Australia/Lord_Howe [LHDT +11:00]", "Australia/Melbourne" => "Australia/Melbourne [AEDT +11:00]", "Australia/Perth" => "Australia/Perth [AWST +08:00]", "Australia/Sydney" => "Australia/Sydney [AEDT +11:00]", "Europe/Amsterdam" => "Europe/Amsterdam [CEST +02:00]", "Europe/Andorra" => "Europe/Andorra [CEST +02:00]", "Europe/Athens" => "Europe/Athens [EEST +03:00]", "Europe/Belgrade" => "Europe/Belgrade [CEST +02:00]", "Europe/Berlin" => "Europe/Berlin [CEST +02:00]", "Europe/Bratislava" => "Europe/Bratislava [CEST +02:00]", "Europe/Brussels" => "Europe/Brussels [CEST +02:00]", "Europe/Bucharest" => "Europe/Bucharest [EEST +03:00]", "Europe/Budapest" => "Europe/Budapest [CEST +02:00]", "Europe/Busingen" => "Europe/Busingen [CEST +02:00]", "Europe/Chisinau" => "Europe/Chisinau [EEST +03:00]", "Europe/Copenhagen" => "Europe/Copenhagen [CEST +02:00]", "Europe/Dublin" => "Europe/Dublin [IST +01:00]", "Europe/Gibraltar" => "Europe/Gibraltar [CEST +02:00]", "Europe/Guernsey" => "Europe/Guernsey [BST +01:00]", "Europe/Helsinki" => "Europe/Helsinki [EEST +03:00]", "Europe/Isle_of_Man" => "Europe/Isle_of_Man [BST +01:00]", "Europe/Istanbul" => "Europe/Istanbul [EEST +03:00]", "Europe/Jersey" => "Europe/Jersey [BST +01:00]", "Europe/Kaliningrad" => "Europe/Kaliningrad [EET +02:00]", "Europe/Kiev" => "Europe/Kiev [EEST +03:00]", "Europe/Lisbon" => "Europe/Lisbon [WEST +01:00]", "Europe/Ljubljana" => "Europe/Ljubljana [CEST +02:00]", "Europe/London" => "Europe/London [BST +01:00]", "Europe/Luxembourg" => "Europe/Luxembourg [CEST +02:00]", "Europe/Madrid" => "Europe/Madrid [CEST +02:00]", "Europe/Malta" => "Europe/Malta [CEST +02:00]", "Europe/Mariehamn" => "Europe/Mariehamn [EEST +03:00]", "Europe/Minsk" => "Europe/Minsk [MSK +03:00]", "Europe/Monaco" => "Europe/Monaco [CEST +02:00]", "Europe/Moscow" => "Europe/Moscow [MSK +03:00]", "Europe/Oslo" => "Europe/Oslo [CEST +02:00]", "Europe/Paris" => "Europe/Paris [CEST +02:00]", "Europe/Podgorica" => "Europe/Podgorica [CEST +02:00]", "Europe/Prague" => "Europe/Prague [CEST +02:00]", "Europe/Riga" => "Europe/Riga [EEST +03:00]", "Europe/Rome" => "Europe/Rome [CEST +02:00]", "Europe/Samara" => "Europe/Samara [SAMT +04:00]", "Europe/San_Marino" => "Europe/San_Marino [CEST +02:00]", "Europe/Sarajevo" => "Europe/Sarajevo [CEST +02:00]", "Europe/Simferopol" => "Europe/Simferopol [MSK +03:00]", "Europe/Skopje" => "Europe/Skopje [CEST +02:00]", "Europe/Sofia" => "Europe/Sofia [EEST +03:00]", "Europe/Stockholm" => "Europe/Stockholm [CEST +02:00]", "Europe/Tallinn" => "Europe/Tallinn [EEST +03:00]", "Europe/Tirane" => "Europe/Tirane [CEST +02:00]", "Europe/Uzhgorod" => "Europe/Uzhgorod [EEST +03:00]", "Europe/Vaduz" => "Europe/Vaduz [CEST +02:00]", "Europe/Vatican" => "Europe/Vatican [CEST +02:00]", "Europe/Vienna" => "Europe/Vienna [CEST +02:00]", "Europe/Vilnius" => "Europe/Vilnius [EEST +03:00]", "Europe/Volgograd" => "Europe/Volgograd [MSK +03:00]", "Europe/Warsaw" => "Europe/Warsaw [CEST +02:00]", "Europe/Zagreb" => "Europe/Zagreb [CEST +02:00]", "Europe/Zaporozhye" => "Europe/Zaporozhye [EEST +03:00]", "Europe/Zurich" => "Europe/Zurich [CEST +02:00]", "Indian/Antananarivo" => "Indian/Antananarivo [EAT +03:00]", "Indian/Chagos" => "Indian/Chagos [IOT +06:00]", "Indian/Christmas" => "Indian/Christmas [CXT +07:00]", "Indian/Cocos" => "Indian/Cocos [CCT +06:30]", "Indian/Comoro" => "Indian/Comoro [EAT +03:00]", "Indian/Kerguelen" => "Indian/Kerguelen [TFT +05:00]", "Indian/Mahe" => "Indian/Mahe [SCT +04:00]", "Indian/Maldives" => "Indian/Maldives [MVT +05:00]", "Indian/Mauritius" => "Indian/Mauritius [MUT +04:00]", "Indian/Mayotte" => "Indian/Mayotte [EAT +03:00]", "Indian/Reunion" => "Indian/Reunion [RET +04:00]", "Pacific/Apia" => "Pacific/Apia [WSDT +14:00]", "Pacific/Auckland" => "Pacific/Auckland [NZDT +13:00]", "Pacific/Bougainville" => "Pacific/Bougainville [BST +11:00]", "Pacific/Chatham" => "Pacific/Chatham [CHADT +13:45]", "Pacific/Chuuk" => "Pacific/Chuuk [CHUT +10:00]", "Pacific/Easter" => "Pacific/Easter [EASST -05:00]", "Pacific/Efate" => "Pacific/Efate [VUT +11:00]", "Pacific/Enderbury" => "Pacific/Enderbury [PHOT +13:00]", "Pacific/Fakaofo" => "Pacific/Fakaofo [TKT +13:00]", "Pacific/Fiji" => "Pacific/Fiji [FJT +12:00]", "Pacific/Funafuti" => "Pacific/Funafuti [TVT +12:00]", "Pacific/Galapagos" => "Pacific/Galapagos [GALT -06:00]", "Pacific/Gambier" => "Pacific/Gambier [GAMT -09:00]", "Pacific/Guadalcanal" => "Pacific/Guadalcanal [SBT +11:00]", "Pacific/Guam" => "Pacific/Guam [ChST +10:00]", "Pacific/Honolulu" => "Pacific/Honolulu [HST -10:00]", "Pacific/Johnston" => "Pacific/Johnston [HST -10:00]", "Pacific/Kiritimati" => "Pacific/Kiritimati [LINT +14:00]", "Pacific/Kosrae" => "Pacific/Kosrae [KOST +11:00]", "Pacific/Kwajalein" => "Pacific/Kwajalein [MHT +12:00]", "Pacific/Majuro" => "Pacific/Majuro [MHT +12:00]", "Pacific/Marquesas" => "Pacific/Marquesas [MART -09:30]", "Pacific/Midway" => "Pacific/Midway [SST -11:00]", "Pacific/Nauru" => "Pacific/Nauru [NRT +12:00]", "Pacific/Niue" => "Pacific/Niue [NUT -11:00]", "Pacific/Norfolk" => "Pacific/Norfolk [NFT +11:30]", "Pacific/Noumea" => "Pacific/Noumea [NCT +11:00]", "Pacific/Pago_Pago" => "Pacific/Pago_Pago [SST -11:00]", "Pacific/Palau" => "Pacific/Palau [PWT +09:00]", "Pacific/Pitcairn" => "Pacific/Pitcairn [PST -08:00]", "Pacific/Pohnpei" => "Pacific/Pohnpei [PONT +11:00]", "Pacific/Port_Moresby" => "Pacific/Port_Moresby [PGT +10:00]", "Pacific/Rarotonga" => "Pacific/Rarotonga [CKT -10:00]", "Pacific/Saipan" => "Pacific/Saipan [ChST +10:00]", "Pacific/Tahiti" => "Pacific/Tahiti [TAHT -10:00]", "Pacific/Tarawa" => "Pacific/Tarawa [GILT +12:00]", "Pacific/Tongatapu" => "Pacific/Tongatapu [TOT +13:00]", "Pacific/Wake" => "Pacific/Wake [WAKT +12:00]", "Pacific/Wallis" => "Pacific/Wallis [WFT +12:00]", "UTC" => "UTC [UTC  00:00]");
                                                                            foreach ($rTimeZones as $rValue => $rText) { ?>
                                                                                <option <?php if ($rSettings["default_timezone"] == $rValue) {
                                                                                            echo "selected ";
                                                                                        } ?>value="<?= $rValue ?>"><?= $rText ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="bouquet_name"><?= $_["enigma2_bouquet_name"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="bouquet_name" name="bouquet_name" value="<?= htmlspecialchars($rSettings["bouquet_name"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="live_streaming_pass"><?= $_["live_streaming_pass"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="live_streaming_pass" name="live_streaming_pass" value="<?= htmlspecialchars($rSettings["live_streaming_pass"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="crypt_load_balancing"><?= $_["load_balancing_key"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="crypt_load_balancing" name="crypt_load_balancing" value="<?= htmlspecialchars($rSettings["crypt_load_balancing"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="userpanel_mainpage"><?= $_["mensagem_dashboard_Revendedores"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["ativar_mensagem_dashboard_revendedores"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="userpanel_mainpage" name="userpanel_mainpage" value="<?= htmlspecialchars($rSettings["userpanel_mainpage"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="page_mannuals"><?= $_["mannuals_revendedores"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["manualls_revendedores"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="page_mannuals" name="page_mannuals" value="<?= htmlspecialchars($rSettings["page_mannuals"]) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ul class="list-inline wizard mb-0">
                                                            <!--<li class="list-inline-item">
														<a href="https://syte.com/donate.html">
				                                            <button type="button" class="btn btn-info waves-effect waves-light btn-xl"><i class="mdi mdi-credit-card"></i> <?= $_["credit-card"] ?></button></a>
														</a>
														<a href="https://commerce.coinbase.com/checkout/55484922-e35e-4efb-b15c-4c1e59fe7734">
														    <button type="button" class="btn btn-primary waves-effect waves-light btn-xl"><i class="mdi mdi-currency-btc"></i> <?= $_["btc"] ?></button></a>
														</a>
														<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=BB8LWS2VEZX2U&currency_code=GBP&source=url">
															<button type="button" class="btn btn-success waves-effect waves-light btn-xl"><i class="mdi mdi-paypal"></i> <?= $_["paypal"] ?></button></a>
														</a>
													</li>-->
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit" class="btn btn-primary" value=<?= $_["save_changes"] ?> />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="xui">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <!--<div class="form-group row mb-4">
                                                              <label class="col-md-4 col-form-label" for="language"><?= $_["ui_language"] ?></label>
                                                              <div class="col-md-8"> 
                                                                  <select name="language" id="language" class="form-control" data-toggle="select2">
                                                                    <?php foreach (getLanguages() as $rLanguage) { ?>
                                                                     <option<?php if ($rAdminSettings["language"] == $rLanguage["key"]) {
                                                                                echo " selected";
                                                                            } ?> value="<?= $rLanguage["key"] ?>"><?= $rLanguage["language"] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            </div>-->
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="admin_username"><?= $_["player_credentials"] ?><i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["play_live_streams"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-4">
                                                                        <input type="text" placeholder="<?= $_["line_username"] ?>" class="form-control" id="admin_username" name="admin_username" value="<?= htmlspecialchars($rAdminSettings["admin_username"]) ?>">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <input type="text" placeholder="<?= $_["line_password"] ?>" class="form-control" id="admin_password" name="admin_password" value="<?= htmlspecialchars($rAdminSettings["admin_password"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="tmdb_api_key"><?= $_["tmdb_api_key"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="tmdb_api_key" name="tmdb_api_key" value="<?= htmlspecialchars($rSettings["tmdb_api_key"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="tmdb_language"><?= $_["tmdb_language"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["select_which_language"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <select name="tmdb_language" id="tmdb_language" class="form-control" data-toggle="select2">
                                                                            <?php foreach ($rTMDBLanguages as $rKey => $rLanguage) { ?>
                                                                                <option<?php if ($rAdminSettings["tmdb_language"] == $rKey) {
                                                                                            echo " selected";
                                                                                        } ?> value="<?= $rKey ?>"><?= $rLanguage ?></option>
                                                                                <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="release_parser"><?= $_["release_parser"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["select_which_parser"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <select name="release_parser" id="release_parser" class="form-control" data-toggle="select2">
                                                                            <?php foreach (array("python" => "Python Based (slower, more accurate)", "php" => "PHP Based (faster, less accurate)") as $rKey => $rParser) { ?>
                                                                                <option<?php if ($rAdminSettings["release_parser"] == $rKey) {
                                                                                            echo " selected";
                                                                                        } ?> value="<?= $rKey ?>"><?= $rParser ?></option>
                                                                                <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="recaptcha_v2_site_key"><?= $_["recaptcha_v2_site_key"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["your_api_keys"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="recaptcha_v2_site_key" name="recaptcha_v2_site_key" value="<?= htmlspecialchars($rAdminSettings["recaptcha_v2_site_key"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="recaptcha_v2_secret_key"><?= $_["recaptcha_v2_secret_key"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["your_secret_api_keys"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="recaptcha_v2_secret_key" name="recaptcha_v2_secret_key" value="<?= htmlspecialchars($rAdminSettings["recaptcha_v2_secret_key"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="recaptcha_enable"><?= $_["enable_recaptcha"] ?> <i class="mdi mdi-information" data-toggle="modal" data-target=".bs-domains"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="recaptcha_enable" id="recaptcha_enable" type="checkbox" <?php if ($rAdminSettings["recaptcha_enable"] == 1) {
                                                                                                                                                    echo "checked ";
                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="ip_logout"><?= $_["logout_on_ip_change"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["logout_session"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="ip_logout" id="ip_logout" type="checkbox" <?php if ($rAdminSettings["ip_logout"] == 1) {
                                                                                                                                    echo "checked ";
                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="login_flood"><?= $_["maximum_login_attempts"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["how_many_login_attempts"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="login_flood" name="login_flood" value="<?= htmlspecialchars($rAdminSettings["login_flood"]) ?: 0 ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="pass_length"><?= $_["minimum_pass_length"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["set_this_enforce_password"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="pass_length" name="pass_length" value="<?= htmlspecialchars($rAdminSettings["pass_length"]) ?: 0 ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="default_entries"><?= $_["default_entries_show"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["default_entries_for_users"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <select name="default_entries" id="default_entries" class="form-control" data-toggle="select2">
                                                                            <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) {
                                                                                            echo " selected";
                                                                                        } ?> value="<?= $rShow ?>"><?= $rShow ?></option>
                                                                                <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="google_2factor"><?= $_["two_factor_authentication"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["enable_two_factor"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="google_2factor" id="google_2factor" type="checkbox" <?php if ($rAdminSettings["google_2factor"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="local_api"><?= $_["local_api"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["select_this_option"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="local_api" id="local_api" type="checkbox" <?php if ($rAdminSettings["local_api"] == 1) {
                                                                                                                                    echo "checked ";
                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="dark_mode_login"><?= $_["dark_mode_login"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dark_mode_login" id="dark_mode_login" type="checkbox" <?php if ($rAdminSettings["dark_mode_login"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="dashboard_stats"><?= $_["dashboard_stats"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["enable_dashboard_option"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dashboard_stats" id="dashboard_stats" type="checkbox" <?php if ($rAdminSettings["dashboard_stats"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="dashboard_stats_frequency"><?= $_["stats_frequency"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["stats_interval"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="dashboard_stats_frequency" name="dashboard_stats_frequency" value="<?= htmlspecialchars($rAdminSettings["dashboard_stats_frequency"]) ?: 600 ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="dashboard_world_map_live">Dashboard World Map Live <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable this option to show interactive connection statistics live on dashboard." class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dashboard_world_map_live" id="dashboard_world_map_live" type="checkbox" <?php if ($rAdminSettings["dashboard_world_map_live"] == 1) {
                                                                                                                                                                    echo "checked ";
                                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="dashboard_world_map_activity">Dashboard World Map Activity <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable this option to show interactive connection statistics activity on dashboard." class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="dashboard_world_map_activity" id="dashboard_world_map_activity" type="checkbox" <?php if ($rAdminSettings["dashboard_world_map_activity"] == 1) {
                                                                                                                                                                            echo "checked ";
                                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="download_images"><?= $_["download_images"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["images_from_server_tmdb"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="download_images" id="download_images" type="checkbox" <?php if ($rAdminSettings["download_images"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="auto_refresh"><?= $_["auto-refresh_by_default"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["auto_refresh_pages_by_deault"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="auto_refresh" id="auto_refresh" type="checkbox" <?php if ($rAdminSettings["auto_refresh"] == 1) {
                                                                                                                                            echo "checked ";
                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="alternate_scandir"><?= $_["alternate_scandir_method"] ?> (Cloud) <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["use_an_alternate_method"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="alternate_scandir" id="alternate_scandir" type="checkbox" <?php if ($rAdminSettings["alternate_scandir"] == 1) {
                                                                                                                                                    echo "checked ";
                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="reseller">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="copyrights_text"><?= $_["copyrights_text"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="copyrights_text" name="copyrights_text" value="<?= htmlspecialchars($rSettings["copyrights_text"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="disable_trial"><?= $_["disable_trial"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["use_this_option_to_temporarily_disable_generating_trials"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disable_trial" id="disable_trial" type="checkbox" <?php if ($rAdminSettings["disable_trial"] == 1) {
                                                                                                                                            echo "checked ";
                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="reseller_restrictions"><?= $_["allow_restrictions"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["set_this_option_to_allow_resellers"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_restrictions" id="reseller_restrictions" type="checkbox" <?php if ($rAdminSettings["reseller_restrictions"] == 1) {
                                                                                                                                                            echo "checked ";
                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="change_usernames"><?= $_["change_usernames"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["set_this_option_to_allow_change_own_usernames"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_usernames" id="change_usernames" type="checkbox" <?php if ($rAdminSettings["change_usernames"] == 1) {
                                                                                                                                                    echo "checked ";
                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="change_own_dns"><?= $_["change_own_dns"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["set_this_option_to_allow_change_own_dns"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_dns" id="change_own_dns" type="checkbox" <?php if ($rAdminSettings["change_own_dns"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="change_own_email"><?= $_["change_own_email"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["set_this_option_to_allow_change_own_email"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_email" id="change_own_email" type="checkbox" <?php if ($rAdminSettings["change_own_email"] == 1) {
                                                                                                                                                    echo "checked ";
                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="change_own_password"><?= $_["change_own_password"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["set_this_option_to_allow_change_own_password"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_password" id="change_own_password" type="checkbox" <?php if ($rAdminSettings["change_own_password"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="change_own_lang"><?= $_["change_own_language_resellers"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["change_own_language_resellers_msg"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="change_own_lang" id="change_own_lang" type="checkbox" <?php if ($rAdminSettings["change_own_lang"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="reseller_mag_events"><?= $_["reseller_send_events"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["resellers_to_be_able_to_send_mag_events"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_mag_events" id="reseller_mag_events" type="checkbox" <?php if ($rAdminSettings["reseller_mag_events"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="reseller_can_isplock"><?= $_["reseller_can_isplock"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["message_reseller_can_isplock"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_can_isplock" id="reseller_can_isplock" type="checkbox" <?php if ($rAdminSettings["reseller_can_isplock"] == 1) {
                                                                                                                                                            echo "checked ";
                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="reseller_reset_isplock"><?= $_["reseller_reset_isplock"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["message_reseller_reset_isplock"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="reseller_reset_isplock" id="reseller_reset_isplock" type="checkbox" <?php if ($rAdminSettings["reseller_reset_isplock"] == 1) {
                                                                                                                                                                echo "checked ";
                                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <!--<label class="col-md-4 col-form-label" for="reseller_select_bouquets"><?= $_["reseller_select_bouquets"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["message_reseller_select_bouquets"] ?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="reseller_select_bouquets" id="reseller_select_bouquets" type="checkbox"<?php if ($rAdminSettings["reseller_select_bouquets"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>-->
                                                                    <label class="col-md-4 col-form-label" for="active_mannuals"><?= $_["active_mannuals"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["message_active_mannuals"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="active_mannuals" id="active_mannuals" type="checkbox" <?php if ($rAdminSettings["active_mannuals"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="streaming">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="flood_limit"><?= $_["flood_limit"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["enter_to_disable_flood_detection"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="flood_limit" name="flood_limit" value="<?= htmlspecialchars($rSettings["flood_limit"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="flood_ips_exclude"><?= $_["flood_ip_exclude"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["separate_each_ip"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="flood_ips_exclude" name="flood_ips_exclude" value="<?= htmlspecialchars($rSettings["flood_ips_exclude"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="user_auto_kick_hours"><?= $_["auto_kick_users"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["automatically_kick_users"] ?> class=" mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="user_auto_kick_hours" name="user_auto_kick_hours" value="<?= htmlspecialchars($rSettings["user_auto_kick_hours"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="disallow_empty_user_agents"><?= $_["disallow_empty_ua"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["dont_allow_connections_from_clients"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disallow_empty_user_agents" id="disallow_empty_user_agents" type="checkbox" <?php if ($rSettings["disallow_empty_user_agents"] == 1) {
                                                                                                                                                                        echo "checked ";
                                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="client_prebuffer"><?= $_["client_prebuffer"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["how_much_data_will_be_sent_to_the_client_1"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="client_prebuffer" name="client_prebuffer" value="<?= htmlspecialchars($rSettings["client_prebuffer"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="restreamer_prebuffer"><?= $_["restreamer_prebuffer"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["how_much_data_will_be_sent_to_the_client_2"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="restreamer_prebuffer" name="restreamer_prebuffer" value="<?= htmlspecialchars($rSettings["restreamer_prebuffer"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="split_clients"><?= $_["split_clients"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="split_clients" id="split_clients" class="form-control" data-toggle="select2">
                                                                            <option<?php if ($rSettings["split_clients"] == "equal") {
                                                                                        echo " selected";
                                                                                    } ?> value="equal"><?= $_["equally"] ?></option>
                                                                                <option<?php if ($rSettings["split_clients"] == "load") {
                                                                                            echo " selected";
                                                                                        } ?> value="load"><?= $_["load"] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="split_by"><?= $_["split_by"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="split_by" id="split_by" class="form-control" data-toggle="select2">
                                                                            <option<?php if ($rSettings["split_by"] == "conn") {
                                                                                        echo " selected";
                                                                                    } ?> value="conn"><?= $_["connections"] ?></option>
                                                                                <option<?php if ($rSettings["split_by"] == "maxclients") {
                                                                                            echo " selected";
                                                                                        } ?> value="maxclients"><?= $_["max_clients"] ?></option>
                                                                                    <option<?php if ($rSettings["split_by"] == "guar_band") {
                                                                                                echo " selected";
                                                                                            } ?> value="guar_band"><?= $_["network_speed"] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="channel_number_type"><?= $_["channel_sorting_type"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="channel_number_type" id="channel_number_type" class="form-control" data-toggle="select2">
                                                                            <option<?php if ($rSettings["channel_number_type"] == "bouquet") {
                                                                                        echo " selected";
                                                                                    } ?> value="bouquet"><?= $_["bouquet"] ?></option>
                                                                                <option<?php if ($rSettings["channel_number_type"] == "manual") {
                                                                                            echo " selected";
                                                                                        } ?> value="manual"><?= $_["manual"] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="new_sorting_bouquet"><?= $_["new_sorting_bouquet"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="new_sorting_bouquet" id="new_sorting_bouquet" type="checkbox" <?php if ($rSettings["new_sorting_bouquet"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="stream_max_analyze"><?= $_["analysis_duration"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["longer duration"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="stream_max_analyze" name="stream_max_analyze" value="<?= htmlspecialchars($rSettings["stream_max_analyze"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="probesize"><?= $_["probe_size"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["probed_in_bytes"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="probesize" name="probesize" value="<?= htmlspecialchars($rSettings["probesize"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="persistent_connections"><?= $_["persistent_connections"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["enable_PHP_persistent_connections"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="persistent_connections" id="persistent_connections" type="checkbox" <?php if ($rSettings["persistent_connections"] == 1) {
                                                                                                                                                                echo "checked ";
                                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="rtmp_random"><?= $_["random_rtmp_ip"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["use_random_ip_for_rmtp"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="rtmp_random" id="rtmp_random" type="checkbox" <?php if ($rSettings["rtmp_random"] == 1) {
                                                                                                                                        echo "checked ";
                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="stream_start_delay"><?= $_["stream_start_delay"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["before_starting_stream"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="stream_start_delay" name="stream_start_delay" value="<?= htmlspecialchars($rSettings["stream_start_delay"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="online_capacity_interval"><?= $_["online_capacity_interval"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["interval_at_which_to_check"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="online_capacity_interval" name="online_capacity_interval" value="<?= htmlspecialchars($rSettings["online_capacity_interval"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="use_buffer"><?= $_["use_nginx_buffer"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["proxy_buffering"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="use_buffer" id="use_buffer" type="checkbox" <?php if ($rSettings["use_buffer"] == 1) {
                                                                                                                                        echo "checked ";
                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="audio_restart_loss"><?= $_["restart_on_audio_loss"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["restart_stream_periodically"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="audio_restart_loss" id="audio_restart_loss" type="checkbox" <?php if ($rSettings["audio_restart_loss"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="save_closed_connection"><?= $_["save_connection_logs"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["save_closed_connection_database"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="save_closed_connection" id="save_closed_connection" type="checkbox" <?php if ($rSettings["save_closed_connection"] == 1) {
                                                                                                                                                                echo "checked ";
                                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="client_logs_save"><?= $_["save_client_logs"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["save_client_logs_to_database"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="client_logs_save" id="client_logs_save" type="checkbox" <?php if ($rSettings["client_logs_save"] == 1) {
                                                                                                                                                    echo "checked ";
                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="case_sensitive_line"><?= $_["case_sensitive_details"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["case_sensitive"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="case_sensitive_line" id="case_sensitive_line" type="checkbox" <?php if ($rSettings["case_sensitive_line"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="county_override_1st"><?= $_["override_country_with_first"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["override_country_with_connected"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="county_override_1st" id="county_override_1st" type="checkbox" <?php if ($rSettings["county_override_1st"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="disallow_2nd_ip_con"><?= $_["disallow_2nd_ip_connection"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["disallow_connection"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="disallow_2nd_ip_con" id="disallow_2nd_ip_con" type="checkbox" <?php if ($rSettings["disallow_2nd_ip_con"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="block_svp"><?= $_["block_svp"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["block_svp_desc"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="block_svp" id="block_svp" type="checkbox" <?php if ($rSettings["block_svp"] == 1) {
                                                                                                                                    echo "checked ";
                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="use_mdomain_in_lists"><?= $_["use_domain_in_lists"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["use_domaine_name"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="use_mdomain_in_lists" id="use_mdomain_in_lists" type="checkbox" <?php if ($rSettings["use_mdomain_in_lists"] == 1) {
                                                                                                                                                            echo "checked ";
                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="hash_lb"><?= $_["hash_load_balancers"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["any_client_is_being_redirected"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="hash_lb" id="hash_lb" type="checkbox" <?php if ($rSettings["hash_lb"] == 1) {
                                                                                                                                echo "checked ";
                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="show_isps"><?= $_["enable_isps"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["grab_isp_information"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_isps" id="show_isps" type="checkbox" <?php if ($rSettings["show_isps"] == 1) {
                                                                                                                                    echo "checked ";
                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="enable_isp_lock"><?= $_["enable_isp_lock1"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["enable_isp_lock_msg"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="enable_isp_lock" id="enable_isp_lock" type="checkbox" <?php if ($rSettings["enable_isp_lock"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="vod_bitrate_plus">VOD Download Speed <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Specify the bitrate here in kbps. Enter number only. 2000 kB/s = 2 MB/s." class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="vod_bitrate_plus" name="vod_bitrate_plus" value="<?= htmlspecialchars($rSettings["vod_bitrate_plus"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="vod_limit_at">VOD Download Limit <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Specify the percentage. Enter number only. Enter 0 to disable." class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="vod_limit_at" name="vod_limit_at" value="<?= htmlspecialchars($rSettings["vod_limit_at"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="priority_backup">Priority Backup Stream <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable if you want the first backup stream to be a priority if you are online" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="priority_backup" id="priority_backup" type="checkbox" <?php if ($rSettings["priority_backup"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="show_not_on_air_video"><?= $_["stream_down_video"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["show_this_video"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_not_on_air_video" id="show_not_on_air_video" type="checkbox" <?php if ($rSettings["show_not_on_air_video"] == 1) {
                                                                                                                                                            echo "checked ";
                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" class="form-control" id="not_on_air_video_path" name="not_on_air_video_path" value="<?= htmlspecialchars($rSettings["not_on_air_video_path"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="show_banned_video"><?= $_["banned_video"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["show_this_video_banned"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_banned_video" id="show_banned_video" type="checkbox" <?php if ($rSettings["show_banned_video"] == 1) {
                                                                                                                                                    echo "checked ";
                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" class="form-control" id="banned_video_path" name="banned_video_path" value="<?= htmlspecialchars($rSettings["banned_video_path"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="show_expired_video"><?= $_["expired_video"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["show_this_video_expired"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_expired_video" id="show_expired_video" type="checkbox" <?php if ($rSettings["show_expired_video"] == 1) {
                                                                                                                                                        echo "checked ";
                                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" class="form-control" id="expired_video_path" name="expired_video_path" value="<?= htmlspecialchars($rSettings["expired_video_path"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="allowed_ips_admin"><?= $_["admin_streaming_ips"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["allowed_ip_to_access"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="allowed_ips_admin" name="allowed_ips_admin" value="<?= htmlspecialchars($rSettings["allowed_ips_admin"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="api_ips"><?= $_["api_ips"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["allowed_ip_to_access_api"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="api_ips" name="api_ips" value="<?= htmlspecialchars($rSettings["api_ips"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="message_of_day"><?= $_["message_of_the_day"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["message_to_display_api"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="message_of_day" name="message_of_day" value="<?= htmlspecialchars($rSettings["message_of_day"]) ?>">
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="mag">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="show_all_category_mag"><?= $_["show_all_categories"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["show_all_mag_category_on_mag_devices"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_all_category_mag" id="show_all_category_mag" type="checkbox" <?php if ($rSettings["show_all_category_mag"] == 1) {
                                                                                                                                                            echo "checked ";
                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="mag_security"><?= $_["mag_security"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["enable_additional_mag"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="mag_security" id="mag_security" type="checkbox" <?php if ($rSettings["mag_security"] == 1) {
                                                                                                                                            echo "checked ";
                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="always_enabled_subtitles"><?= $_["always_enabled_subtitles"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["force_subtitles"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input name="always_enabled_subtitles" id="always_enabled_subtitles" type="checkbox" <?php if ($rSettings["always_enabled_subtitles"] == 1) {
                                                                                                                                                                    echo "checked ";
                                                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="enable_connection_problem_indication"><?= $_["connection_problem_indication"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="enable_connection_problem_indication" id="enable_connection_problem_indication" type="checkbox" <?php if ($rSettings["enable_connection_problem_indication"] == 1) {
                                                                                                                                                                                            echo "checked ";
                                                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="show_tv_channel_logo"><?= $_["show_channel_logos"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_tv_channel_logo" id="show_tv_channel_logo" type="checkbox" <?php if ($rSettings["show_tv_channel_logo"] == 1) {
                                                                                                                                                            echo "checked ";
                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="show_channel_logo_in_preview"><?= $_["show_preview_channel_logos"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="show_channel_logo_in_preview" id="show_channel_logo_in_preview" type="checkbox" <?php if ($rSettings["show_channel_logo_in_preview"] == 1) {
                                                                                                                                                                            echo "checked ";
                                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="stb_change_pass"><?= $_["allow_stb_pass_change"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="stb_change_pass" id="stb_change_pass" type="checkbox" <?php if ($rSettings["stb_change_pass"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="enable_debug_stalker"><?= $_["stalker_debug"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input name="enable_debug_stalker" id="enable_debug_stalker" type="checkbox" <?php if ($rSettings["enable_debug_stalker"] == 1) {
                                                                                                                                                            echo "checked ";
                                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="mag_container"><?= $_["default_container"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="mag_container" id="mag_container" class="form-control" data-toggle="select2">
                                                                            <?php
                                                                            foreach (array("ts" => "TS", "m3u8" => "M3U8") as $rValue => $rText) { ?>
                                                                                <option <?php if ($rSettings["mag_container"] == $rValue) {
                                                                                            echo "selected ";
                                                                                        } ?>value="<?= $rValue ?>"><?= $rText ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="stalker_theme"><?= $_["default_theme"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="stalker_theme" id="stalker_theme" class="form-control" data-toggle="select2">
                                                                            <?php
                                                                            foreach (array("default" => "Default", "digital" => "Digital", "emerald" => "Emerald", "cappucino" => "Cappucino", "ocean_blue" => "Ocean Blue") as $rValue => $rText) { ?>
                                                                                <option <?php if ($rSettings["stalker_theme"] == $rValue) {
                                                                                            echo "selected ";
                                                                                        } ?>value="<?= $rValue ?>"><?= $rText ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="record_max_length"><?= $_["record_max_length"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="record_max_length" name="record_max_length" value="<?= htmlspecialchars($rSettings["record_max_length"]) ?>">
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="max_local_recordings"><?= $_["max_local_recordings"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="max_local_recordings" name="max_local_recordings" value="<?= htmlspecialchars($rSettings["max_local_recordings"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="playback_limit"><?= $_["playback_limit"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="playback_limit" name="playback_limit" value="<?= htmlspecialchars($rSettings["playback_limit"]) ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="allowed_stb_types"><?= $_["allowed_stb_types"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <select name="allowed_stb_types[]" id="allowed_stb_types" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="<?= $_["choose"] ?>...">
                                                                            <?php foreach ($rMAGs as $rMAG) { ?>
                                                                                <option <?php if (in_array($rMAG, json_decode($rSettings["allowed_stb_types"], True))) {
                                                                                            echo "selected ";
                                                                                        } ?>value="<?= $rMAG ?>"><?= $rMAG ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="allowed_stb_types_for_local_recording"><?= $_["allowed_stb_recording"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <select name="allowed_stb_types_for_local_recording[]" id="allowed_stb_types_for_local_recording" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="<?= $_["choose"] ?>...">
                                                                            <?php foreach ($rMAGs as $rMAG) { ?>
                                                                                <option <?php if (in_array($rMAG, json_decode($rSettings["allowed_stb_types_for_local_recording"], True))) {
                                                                                            echo "selected ";
                                                                                        } ?>value="<?= $rMAG ?>"><?= $rMAG ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                        <ul class="list-inline wizard mb-0">
                                                            <li class="list-inline-item float-right">
                                                                <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php }
                                                if (hasPermissions("adv", "database")) { ?>
                                                    <div class="tab-pane" id="backups">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="automatic_backups"><?= $_["automatic_backups"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <select name="automatic_backups" id="automatic_backups" class="form-control" data-toggle="select2">
                                                                            <?php foreach (array("off" => "Off", "hourly" => "Hourly", "daily" => "Daily", "weekly" => "Weekly", "monthly" => "Monthly") as $rType => $rText) { ?>
                                                                                <option<?php if ($rAdminSettings["automatic_backups"] == $rType) {
                                                                                            echo " selected";
                                                                                        } ?> value="<?= $rType ?>"><?= $rText ?></option>
                                                                                <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <label class="col-md-4 col-form-label" for="backups_to_keep"><?= $_["backups_to_keep"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["enter_for_unlimited"] ?>" class="mdi mdi-information"></i></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="backups_to_keep" name="backups_to_keep" value="<?= htmlspecialchars($rAdminSettings["backups_to_keep"] ? $rAdminSettings["backups_to_keep"] : 0) ?>">
                                                                    </div>
                                                                </div>
                                                                <table class="table table-borderless mb-0" id="datatable-backups">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th class="text-center"><?= $_["date"] ?></th>
                                                                            <th class="text-center"><?= $_["filename"] ?></th>
                                                                            <th class="text-center"><?= $_["filesize"] ?></th>
                                                                            <th class="text-center"><?= $_["actions"] ?></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody></tbody>
                                                                </table>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                        <ul class="list-inline wizard mb-0" style="margin-top:30px;">
                                                            <li class="list-inline-item float-right">
                                                                <button id="create_backup" onClick="api('', 'backup')" class="btn btn-info"><?= $_["create_backup_now"] ?></button>
                                                                <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?>" />
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="infos">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="bg-soft-light border-light border">
                                                                            <div class="row">
                                                                                <div class="col-md-12">
                                                                                    <p class="text-muted mb-0 mt-3 text-left"></small><b><a class="text-dark"><?= $rInfos["title"][0] ?></a></b></p>
                                                                                    <h5 class="font-weight-normal mb-3">
                                                                                        <span><?= $rInfos["infos"][0] ?><sup class="font-13"> <?= $rInfos["infos"][1] ?></sup></span>
                                                                                    </h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div> <!-- end col -->
                                                        </div> <!-- end row -->
                                                    </div> <!-- tab-content -->
                                                    <div class="tab-pane" id="database">
                                                        <div class="row">
                                                            <iframe width="100%" height="650px" src="./database.php" style="overflow-x:hidden;border:0px;"></iframe>
                                                        </div> <!-- end row -->
                                                    </div>
                                                <?php } ?>
                                            </div> <!-- tab-content -->
                                        </div> <!-- end #basicwizard-->
                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                                <div class="modal fade bs-domains" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="modalLabel"><?= $_["domain_list"] ?></h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
                                                                    <td><?= $rServers[$_INFO["server_id"]]["server_ip"] ?></td>
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
                                                                    <td><?= $rServers[$_INFO["server_id"]]["domain_name"] ?></td>
                                                                </tr>
                                                                <?php }
                                                            $result = $db->query("SELECT `username`, `reseller_dns` FROM `reg_users` WHERE `reseller_dns` <> '' AND `verified` = 1 ORDER BY `username` ASC;");
                                                            if (($result) && ($result->num_rows > 0)) {
                                                                while ($row = $result->fetch_assoc()) { ?>
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
                <link rel="stylesheet" href="assets/js/minified/themes/default.min.css" id="theme-style" />
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
                        $.getJSON("./api.php?action=backup&sub=" + rType + "&filename=" + encodeURIComponent(rID), function(data) {
                            if (data.result === true) {
                                if (rType == "delete") {
                                    $.each($('.tooltip'), function(index, element) {
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

                    (function($) {
                        $.fn.inputFilter = function(inputFilter) {
                            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
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

                    $(document).ready(function() {
                        $('select').select2({
                            width: '100%'
                        });
                        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                        elems.forEach(function(html) {
                            var switchery = new Switchery(html);
                        });
                        $(window).keypress(function(event) {
                            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                        });

                        $("#datatable-backups").DataTable({
                            language: {
                                paginate: {
                                    previous: "<i class='mdi mdi-chevron-left'>",
                                    next: "<i class='mdi mdi-chevron-right'>"
                                }
                            },
                            drawCallback: function() {
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
                                "data": function(d) {
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
                        $("#flood_limit").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#user_auto_kick_hours").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#probesize").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#stream_max_analyze").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#client_prebuffer").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#restreamer_prebuffer").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#backups_to_keep").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                    });
                </script>
                <script>
                    var textarea = document.getElementById('userpanel_mainpage');
                    sceditor.create(textarea, {
                        format: 'bbcode',
                        icons: 'monocons',
                        style: '../assets/js/minified/themes/content/default.min.css'
                    });


                    var themeInput = document.getElementById('theme');
                    themeInput.onchange = function() {
                        var theme = '../assets/js/minified/themes/' + themeInput.value + '.min.css';

                        document.getElementById('theme-style').href = theme;
                    };
                </script>
                <script>
                    var textarea = document.getElementById('page_mannuals');
                    sceditor.create(textarea, {
                        format: 'bbcode',
                        icons: 'monocons',
                        style: '../assets/js/minified/themes/content/default.min.css'
                    });


                    var themeInput = document.getElementById('theme');
                    themeInput.onchange = function() {
                        var theme = '../assets/js/minified/themes/' + themeInput.value + '.min.css';

                        document.getElementById('theme-style').href = theme;
                    };
                </script>
                </body>

                </html>