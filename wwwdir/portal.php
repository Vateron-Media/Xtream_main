<?php

require './init.php';
include './langs/mag_langs.php';

header('cache-control: no-store, no-cache, must-revalidate');
header('cache-control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
@header('Content-type: text/javascript');

$rPageItems = 14;


$rReqType = (!empty($_REQUEST['type']) ? $_REQUEST['type'] : null);
$rReqAction = (!empty($_REQUEST['action']) ? $_REQUEST['action'] : null);

if ($rReqType && $rReqAction) {
    switch ($rReqType) {
        case 'stb':
            switch ($rReqAction) {
                case 'get_ad':
                    exit(json_encode(array('js' => array())));

                case 'get_storages':
                    exit(json_encode(array('js' => array())));

                case 'log':
                    exit(json_encode(array('js' => true)));

                case 'get_countries':
                    exit(json_encode(array('js' => array())));

                case 'get_timezones':
                    exit(json_encode(array('js' => array())));

                case 'get_cities':
                    exit(json_encode(array('js' => array())));

                case 'search_cities':
                    exit(json_encode(array('js' => array())));
            }

            break;

        case 'remote_pvr':
            switch ($rReqAction) {
                case 'start_record_on_stb':
                    exit(json_encode(array('js' => true)));

                case 'stop_record_on_stb':
                    exit(json_encode(array('js' => true)));

                case 'get_active_recordings':
                    exit(json_encode(array('js' => array())));
            }

            break;

        case 'media_favorites':
            exit(json_encode(array('js' => '')));

        case 'tvreminder':
            exit(json_encode(array('js' => array())));

        case 'series':
        case 'vod':
            switch ($rReqAction) {
                case 'set_not_ended':
                    exit(json_encode(array('js' => true)));

                case 'del_link':
                    exit(json_encode(array('js' => true)));

                case 'log':
                    exit(json_encode(array('js' => 1)));
            }

            break;

        case 'downloads':
            exit(json_encode(array('js' => true)));

        case 'weatherco':
            exit(json_encode(array('js' => false)));

        case 'course':
            exit(json_encode(array('js' => true)));

        case 'account_info':
            switch ($rReqAction) {
                case 'get_terms_info':
                    exit(json_encode(array('js' => true)));

                case 'get_payment_info':
                    exit(json_encode(array('js' => true)));

                case 'get_demo_video_parts':
                    exit(json_encode(array('js' => true)));

                case 'get_agreement_info':
                    exit(json_encode(array('js' => true)));
            }

            break;

        case 'tv_archive':
            switch ($rReqAction) {
                case 'set_played_timeshift':
                    exit(json_encode(array('js' => true)));

                case 'set_played':
                    exit(json_encode(array('js' => true)));

                case 'update_played_timeshift_end_time':
                    exit(json_encode(array('js' => true)));
            }

            break;

        case 'itv':
            switch ($rReqAction) {
                case 'set_fav_status':
                    exit(json_encode(array('js' => array())));

                case 'set_played':
                    exit(json_encode(array('js' => true)));
            }

            break;
    }
}



register_shutdown_function('shutdown');

$rIP = ipTV_streaming::getUserIP();
$rCountryCode = ipTV_streaming::getIPInfo($rIP)['country']['iso_code'];
$rMAC = (!empty(ipTV_lib::$request['mac']) ? ipTV_lib::$request['mac'] : $_COOKIE['mac']);
$rUserAgent = (!empty($_SERVER['HTTP_X_USER_AGENT']) ? $_SERVER['HTTP_X_USER_AGENT'] : null);
$rGMode = (!empty(ipTV_lib::$request['gmode']) ? intval(ipTV_lib::$request['gmode']) : null);
$rDebug = ipTV_lib::$settings['enable_debug_stalker'];
$rDevice = array();
$rTypes = array('live', 'created_live');
$rForceProtocol = (ipTV_lib::$settings['mag_disable_ssl'] ? 'http' : null);
$rUpdateCache = false;

if ($rReqType == 'stb' && $rReqAction == 'handshake') {
    $rDevice = getDevice(null, $rMAC);
    $rVerifyToken = null;

    if ($rDevice) {
        $rDevice['token'] = strtoupper(md5(uniqid(rand(), true)));
        $rVerifyToken = encryptData(serialize(array('id' => $rDevice['mag_id'], 'token' => $rDevice['token'])), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
        $rDevice['authenticated'] = false;
        $ipTV_db->query('UPDATE `mag_devices` SET `token` = \'%s\' WHERE `mag_id` = \'%s\'', $rDevice['token'], $rDevice['mag_id']);
        $ipTV_db->query('INSERT INTO `signals`(`server_id`, `cache`, `time`, `custom_data`) VALUES(\'%s\', 1, \'%s\', \'%s\');', SERVER_ID, time(), json_encode(array('type' => 'update_line', 'id' => $rDevice['user_id'])));
        updatecache();
    } else {
        $rDevice = array();
    }

    exit(json_encode(array('js' => array('token' => $rVerifyToken))));
}
if (empty($rDevice['locale']) && !empty($_COOKIE['locale'])) {
    $rDevice['locale'] = $_COOKIE['locale'];
} else {
    $rDevice['locale'] = 'en_GB.utf8';
}

if (function_exists('getallheaders')) {
    $rHeaders = getallheaders();
} else {
    $rHeaders = getHeaders();
}

$rAuthToken = null;
$rAuthHeader = (!empty($rHeaders['Authorization']) ? $rHeaders['Authorization'] : null);

if ($rAuthHeader && preg_match('/Bearer\\s+(.*)$/i', $rAuthHeader, $rMatches)) {
    $rAuthToken = trim($rMatches[1]);
}

if ($rAuthToken) {
    $rVerify = unserialize(decryptData($rAuthToken, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA));
    $rDevice = (isset($rVerify['id']) ? getDevice($rVerify['id']) : array());

    if ($rDevice['token'] != $rVerify['token']) {
        $rDevice = array();
    } else {
        $rDevice['authenticated'] = true;
        updatecache();
    }
}

if ($rDevice && $rReqType == 'stb' && $rReqAction == 'get_profile') {
    $rSerialNumber = (!empty(ipTV_lib::$request['sn']) ? ipTV_lib::$request['sn'] : null);
    $rSTBType = (!empty(ipTV_lib::$request['stb_type']) ? ipTV_lib::$request['stb_type'] : null);
    $rVersion = (!empty(ipTV_lib::$request['ver']) ? ipTV_lib::$request['ver'] : null);
    $rImageVersion = (!empty(ipTV_lib::$request['image_version']) ? ipTV_lib::$request['image_version'] : null);
    $rDeviceID = (!empty(ipTV_lib::$request['device_id']) ? ipTV_lib::$request['device_id'] : null);
    $rDeviceID2 = (!empty(ipTV_lib::$request['device_id2']) ? ipTV_lib::$request['device_id2'] : null);
    $rHWVersion = (!empty(ipTV_lib::$request['hw_version']) ? ipTV_lib::$request['hw_version'] : null);
    $rVerified = true;

    if (!(empty(ipTV_lib::$settings['allowed_stb_types']) || in_array(strtolower($rSTBType), ipTV_lib::$settings['allowed_stb_types']))) {
        // console log STB Types not alowed
        $rVerified = false;
    }

    //MAGSCAN
    //If No SerialNumber Is Posted
    if (empty($rSerialNumber)) {
        $rBanData = array('ip' => $rIP, 'notes' => "[MS] No Serial Number", 'date' => time());
        touch(FLOOD_TMP_PATH . 'block_' . $rIP);
        $ipTV_db->query('INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES(\'%s\', \'%s\', \'%s\')', $rBanData['ip'], $rBanData['notes'], $rBanData['date']);
        http_response_code(404);
        die();
    }
    //If Posted SN is different from Device
    if (!empty($rDevice['sn']) && $rDevice['sn'] !== $rSerialNumber) {
        $rBanData = array('ip' => $rIP, 'notes' => "[MS] Invalid Serial Number", 'date' => time());
        touch(FLOOD_TMP_PATH . 'block_' . $rIP);
        $ipTV_db->query('INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES(\'%s\', \'%s\', \'%s\')', $rBanData['ip'], $rBanData['notes'], $rBanData['date']);
        http_response_code(404);
        die();
    }
    //MANGSCAN

    if ($rDevice['lock_device']) {
        if (!empty($rDevice['sn']) || $rDevice['sn'] != $rSerialNumber) {
            $rVerified = false;
        }

        if (!empty($rDevice['device_id']) || $rDevice['device_id'] != $rDeviceID) {
            $rVerified = false;
        }

        if (!empty($rDevice['device_id2']) || $rDevice['device_id2'] != $rDeviceID2) {
            $rVerified = false;
        }

        if (!empty($rDevice['hw_version']) || $rDevice['hw_version'] != $rHWVersion) {
            $rVerified = false;
        }
    }

    if (!empty(ipTV_lib::$settings['stalker_lock_images']) && !in_array($ver, ipTV_lib::$settings['stalker_lock_images'])) {
        $rVerified = false;
    }


    if ($rDebug) {
        $rVerified = true;
    }

    if ($rVerified) {
        $rDevice['ip'] = $rIP;
        $rDevice['stb_type'] = $rSTBType;
        $rDevice['sn'] = $rSerialNumber;
        $rDevice['ver'] = $rVersion;
        $rDevice['image_version'] = $rImageVersion;
        $rDevice['device_id'] = $rDeviceID;
        $rDevice['device_id2'] = $rDeviceID2;
        $rDevice['hw_version'] = $rHWVersion;
        $rDevice['get_profile_vars']['ip'] = ($rIP ?: '127.0.0.1');
        $rDevice['get_profile_vars']['image_version'] = $rImageVersion;
        $rDevice['get_profile_vars']['stb_type'] = $rSTBType;
        $rDevice['get_profile_vars']['hw_version'] = $rHWVersion;
        $rDevice['authenticated'] = true;
        $ipTV_db->query('UPDATE `mag_devices` SET `ip` = \'%s\', `stb_type` = \'%s\', `sn` = \'%s\', `ver` = \'%s\', `image_version` = \'%s\', `device_id` = \'%s\', `device_id2` = \'%s\', `hw_version` = \'%s\' WHERE `mag_id` = \'%s\';', $rIP, $rSTBType, $rSerialNumber, $rVersion, $rImageVersion, $rDeviceID, $rDeviceID2, $rHWVersion, $rDevice['mag_id']);
        updatecache();
    } else {
        ipTV_lib::unlink_file(STALKER_TMP_PATH . 'stalker_' . $rDevice['id']);
        $rDevice = array();
    }
}

$rAuthenticated = ($rDevice['authenticated'] ?: false);
// $rAuthenticated = true;


$rMagData = array();
$rProfile = array('id' => $rDevice['mag_id'], 'name' => $rDevice['mag_id'], 'sname' => '', 'pass' => '', 'use_embedded_settings' => '', 'parent_password' => '0000', 'bright' => '200', 'contrast' => '127', 'saturation' => '127', 'video_out' => '', 'volume' => '70', 'playback_buffer_bytes' => '0', 'playback_buffer_size' => '0', 'audio_out' => '1', 'mac' => $rMAC, 'ip' => '127.0.0.1', 'ls' => '', 'version' => '', 'lang' => '', 'locale' => $rDevice['locale'], 'city_id' => '0', 'hd' => '1', 'main_notify' => '1', 'fav_itv_on' => '0', 'now_playing_start' => '2018-02-18 17:33:43', 'now_playing_type' => '1', 'now_playing_content' => 'Test channel', 'additional_services_on' => '1', 'time_last_play_tv' => '0000-00-00 00:00:00', 'time_last_play_video' => '0000-00-00 00:00:00', 'operator_id' => '0', 'storage_name' => '', 'hd_content' => '0', 'image_version' => 'undefined', 'last_change_status' => '0000-00-00 00:00:00', 'last_start' => '2018-02-18 17:33:38', 'last_active' => '2018-02-18 17:33:43', 'keep_alive' => '2018-02-18 17:33:43', 'screensaver_delay' => '10', 'phone' => '', 'fname' => '', 'login' => '', 'password' => '', 'stb_type' => '', 'num_banks' => '0', 'tariff_plan_id' => '0', 'comment' => null, 'now_playing_link_id' => '0', 'now_playing_streamer_id' => '0', 'just_started' => '1', 'last_watchdog' => '2018-02-18 17:33:39', 'created' => '2018-02-18 14:40:12', 'plasma_saving' => '0', 'ts_enabled' => '0', 'ts_enable_icon' => '1', 'ts_path' => '', 'ts_max_length' => '3600', 'ts_buffer_use' => 'cyclic', 'ts_action_on_exit' => 'no_save', 'ts_delay' => 'on_pause', 'video_clock' => 'Off', 'verified' => '0', 'hdmi_event_reaction' => 1, 'pri_audio_lang' => '', 'sec_audio_lang' => '', 'pri_subtitle_lang' => '', 'sec_subtitle_lang' => '', 'subtitle_color' => '16777215', 'subtitle_size' => '20', 'show_after_loading' => '', 'play_in_preview_by_ok' => null, 'hw_version' => 'undefined', 'openweathermap_city_id' => '0', 'theme' => '', 'settings_password' => '0000', 'expire_billing_date' => '0000-00-00 00:00:00', 'reseller_id' => null, 'account_balance' => '', 'client_type' => 'STB', 'hw_version_2' => '62', 'blocked' => '0', 'units' => 'metric', 'tariff_expired_date' => null, 'tariff_id_instead_expired' => null, 'activation_code_auto_issue' => '1', 'last_itv_id' => 0, 'updated' => array('id' => '1', 'uid' => '1', 'anec' => '0', 'vclub' => '0'), 'rtsp_type' => '4', 'rtsp_flags' => '0', 'stb_lang' => 'en', 'display_menu_after_loading' => '', 'record_max_length' => 180, 'web_proxy_host' => '', 'web_proxy_port' => '', 'web_proxy_user' => '', 'web_proxy_pass' => '', 'web_proxy_exclude_list' => '', 'demo_video_url' => '', 'tv_quality' => 'high', 'tv_quality_filter' => '', 'is_moderator' => false, 'timeslot_ratio' => 0.33333333333333, 'timeslot' => 40, 'kinopoisk_rating' => '1', 'enable_tariff_plans' => '', 'strict_stb_type_check' => '', 'cas_type' => 0, 'cas_params' => null, 'cas_web_params' => null, 'cas_additional_params' => array(), 'cas_hw_descrambling' => 0, 'cas_ini_file' => '', 'logarithm_volume_control' => '', 'allow_subscription_from_stb' => '1', 'deny_720p_gmode_on_mag200' => '1', 'enable_arrow_keys_setpos' => '1', 'show_purchased_filter' => '', 'timezone_diff' => 0, 'enable_connection_problem_indication' => '1', 'invert_channel_switch_direction' => '', 'play_in_preview_only_by_ok' => false, 'enable_stream_error_logging' => '', 'always_enabled_subtitles' => (ipTV_lib::$settings['always_enabled_subtitles'] == 1 ? '1' : ''), 'enable_service_button' => '', 'enable_setting_access_by_pass' => '', 'tv_archive_continued' => '', 'plasma_saving_timeout' => '600', 'show_tv_only_hd_filter_option' => '', 'tv_playback_retry_limit' => '0', 'fading_tv_retry_timeout' => '1', 'epg_update_time_range' => 0.6, 'store_auth_data_on_stb' => false, 'account_page_by_password' => '', 'tester' => false, 'enable_stream_losses_logging' => '', 'external_payment_page_url' => '', 'max_local_recordings' => '10', 'tv_channel_default_aspect' => 'fit', 'default_led_level' => '10', 'standby_led_level' => '90', 'show_version_in_main_menu' => '1', 'disable_youtube_for_mag200' => '1', 'auth_access' => false, 'epg_data_block_period_for_stb' => '5', 'standby_on_hdmi_off' => '1', 'force_ch_link_check' => '', 'stb_ntp_server' => 'pool.ntp.org', 'overwrite_stb_ntp_server' => '', 'hide_tv_genres_in_fullscreen' => null, 'advert' => null);
$rLocales['get_locales']['English'] = 'en_GB.utf8';
$rLocales['get_locales']['Ελληνικά'] = 'el_GR.utf8';
$rMagData['get_years'] = array('js' => array(array('id' => '*', 'title' => '*')));

foreach (range(1900, date('Y')) as $rYear) {
    $rMagData['get_years']['js'][] = array('id' => $rYear, 'title' => $rYear);
}
$rMagData['get_abc'] = array('js' => array(array('id' => '*', 'title' => '*'), array('id' => 'A', 'title' => 'A'), array('id' => 'B', 'title' => 'B'), array('id' => 'C', 'title' => 'C'), array('id' => 'D', 'title' => 'D'), array('id' => 'E', 'title' => 'E'), array('id' => 'F', 'title' => 'F'), array('id' => 'G', 'title' => 'G'), array('id' => 'H', 'title' => 'H'), array('id' => 'I', 'title' => 'I'), array('id' => 'G', 'title' => 'G'), array('id' => 'K', 'title' => 'K'), array('id' => 'L', 'title' => 'L'), array('id' => 'M', 'title' => 'M'), array('id' => 'N', 'title' => 'N'), array('id' => 'O', 'title' => 'O'), array('id' => 'P', 'title' => 'P'), array('id' => 'Q', 'title' => 'Q'), array('id' => 'R', 'title' => 'R'), array('id' => 'S', 'title' => 'S'), array('id' => 'T', 'title' => 'T'), array('id' => 'U', 'title' => 'U'), array('id' => 'V', 'title' => 'V'), array('id' => 'W', 'title' => 'W'), array('id' => 'X', 'title' => 'X'), array('id' => 'W', 'title' => 'W'), array('id' => 'Z', 'title' => 'Z')));
$rTimezone = (empty($_COOKIE['timezone']) || $_COOKIE['timezone'] == 'undefined' ? ipTV_lib::$settings['default_timezone'] : $_COOKIE['timezone']);

if (!in_array($rTimezone, DateTimeZone::listIdentifiers())) {
    $rTimezone = ipTV_lib::$settings['default_timezone'];
}

$rTheme = (empty(ipTV_lib::$settings['stalker_theme']) ? 'default' : ipTV_lib::$settings['stalker_theme']);

switch ($rReqType) {
    case 'stb':
        switch ($rReqAction) {
            case 'get_profile':
                $rTotal = ($rAuthenticated ? array_merge($rProfile, $rDevice['get_profile_vars']) : $rProfile);
                $rTotal['status'] = intval(!$rAuthenticated);
                $rTotal['update_url'] = (empty(ipTV_lib::$settings['update_url']) ? '' : ipTV_lib::$settings['update_url']);
                $rTotal['test_download_url'] = (empty(ipTV_lib::$settings['test_download_url']) ? '' : ipTV_lib::$settings['test_download_url']);
                $rTotal['default_timezone'] = ipTV_lib::$settings['default_timezone'];
                $rTotal['default_locale'] = $rDevice['locale'];
                $rTotal['allowed_stb_types'] = ipTV_lib::$settings['allowed_stb_types'];
                $rTotal['allowed_stb_types_for_local_recording'] = ipTV_lib::$settings['allowed_stb_types'];
                $rTotal['storages'] = array();
                $rTotal['tv_channel_default_aspect'] = (empty(ipTV_lib::$settings['tv_channel_default_aspect']) ? 'fit' : ipTV_lib::$settings['tv_channel_default_aspect']);
                $rTotal['playback_limit'] = (empty(ipTV_lib::$settings['playback_limit']) ? false : intval(ipTV_lib::$settings['playback_limit']));

                if (empty($rTotal['playback_limit'])) {
                    $rTotal['enable_playback_limit'] = false;
                }

                $rTotal['show_tv_channel_logo'] = !empty(ipTV_lib::$settings['show_tv_channel_logo']);
                $rTotal['show_channel_logo_in_preview'] = !empty(ipTV_lib::$settings['show_channel_logo_in_preview']);
                $rTotal['enable_connection_problem_indication'] = !empty(ipTV_lib::$settings['enable_connection_problem_indication']);
                $rTotal['hls_fast_start'] = '1';
                $rTotal['check_ssl_certificate'] = 0;
                $rTotal['enable_buffering_indication'] = 1;
                $rTotal['watchdog_timeout'] = mt_rand(80, 120);

                if (empty($rTotal['aspect']) && ipTV_lib::$StreamingServers[SERVER_ID]['server_protocol'] == 'https') {
                    $rTotal['aspect'] = '16';
                }

                exit(json_encode(array('js' => $rTotal), JSON_PARTIAL_OUTPUT_ON_ERROR));

            case 'get_localization':
                exit(json_encode(array('js' => $magLangs[$rDevice['locale']])));
            case 'log':
                exit(json_encode(array('js' => true)));

            case 'get_modules':
                $rModules = array('all_modules' => array('media_browser', 'vclub', 'tv', 'sclub', 'radio', 'dvb', 'tv_archive', 'time_shift', 'time_shift_local', 'epg.reminder', 'epg.recorder', 'epg', 'epg.simple', 'downloads_dialog', 'downloads', 'records', 'pvr_local', 'settings.parent', 'settings.localization', 'settings.update', 'settings.playback', 'settings.common', 'settings.network_status', 'settings', 'account', 'internet', 'logout', 'account_menu'), 'switchable_modules' => array('sclub', 'vlub'), 'disabled_modules' => array('records', 'downloads', 'settings.update', 'settings.common', 'pvr_local', 'media_browser'), 'restricted_modules' => array(), 'template' => $rTheme, 'launcher_url' => '', 'launcher_profile_url' => '');

                exit(json_encode(array('js' => $rModules)));
        }

        break;

    default:
        if ($rAuthenticated) {
            $rDevice['mag_player'] = trim($rDevice['mag_player'], "'\"");
            $rPlayer = (!empty($rDevice['mag_player']) ? $rDevice['mag_player'] . ' ' : 'ffmpeg ');

            switch ($rReqType) {
                case 'stb':
                    switch ($rReqAction) {
                        case 'get_tv_aspects':
                            if (!empty($rDevice['aspect'])) {
                                exit($rDevice['aspect']);
                            }
                            exit(json_encode($rDevice['aspect']));

                        case 'set_volume':
                            $rVolume = ipTV_lib::$request['vol'];

                            if (empty($rVolume)) {
                                break;
                            }

                            $rDevice['volume'] = $rVolume;
                            $ipTV_db->query('UPDATE `mag_devices` SET `volume` = \'%s\' WHERE `mag_id` = \'%s\'', $rVolume, $rDevice['mag_id']);
                            updatecache();

                            exit(json_encode(array('data' => true)));

                        case 'get_preload_images':
                            $rMode = (is_numeric($rGMode) ? 'i_' . $rGMode : 'i');
                            $rImages = array('template/' . $rTheme . '/' . $rMode . '/alert_triangle.png', 'template/' . $rTheme . '/' . $rMode . '/archive.png', 'template/' . $rTheme . '/' . $rMode . '/archive_white.png', 'template/' . $rTheme . '/' . $rMode . '/bg.png', 'template/' . $rTheme . '/' . $rMode . '/bg2.png', 'template/' . $rTheme . '/' . $rMode . '/ears_arrow_l.png', 'template/' . $rTheme . '/' . $rMode . '/ears_arrow_r.png', 'template/' . $rTheme . '/' . $rMode . '/hd.png', 'template/' . $rTheme . '/' . $rMode . '/hd_white.png', 'template/' . $rTheme . '/' . $rMode . '/mb_prev_bg.png', 'template/' . $rTheme . '/' . $rMode . '/mm_hor_surround.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_account.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_default.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_internet.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_mb.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_radio.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_setting.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_tv.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_video.png', 'template/' . $rTheme . '/' . $rMode . '/mm_ico_youtube.png', 'template/' . $rTheme . '/' . $rMode . '/left_white.png', 'template/' . $rTheme . '/' . $rMode . '/logo.png', 'template/' . $rTheme . '/' . $rMode . '/play.png', 'template/' . $rTheme . '/' . $rMode . '/play_white.png', 'template/' . $rTheme . '/' . $rMode . '/rec.png', 'template/' . $rTheme . '/' . $rMode . '/rec_white.png', 'template/' . $rTheme . '/' . $rMode . '/right_white.png', 'template/' . $rTheme . '/' . $rMode . '/star.png', 'template/' . $rTheme . '/' . $rMode . '/star_white.png', 'template/' . $rTheme . '/' . $rMode . '/tv_prev_bg.png', 'template/' . $rTheme . '/' . $rMode . '/volume_bar.png', 'template/' . $rTheme . '/' . $rMode . '/volume_bg.png', 'template/' . $rTheme . '/' . $rMode . '/volume_off.png');

                            exit(json_encode(array('js' => $rImages)));

                        case 'get_settings_profile':
                            $ipTV_db->query('SELECT * FROM `mag_devices` WHERE `mag_id` = \'%s\'', $rDevice['mag_id']);
                            $rInfo = $ipTV_db->get_row();
                            $rSettings = array('js' => array('modules' => array(array('name' => 'lock'), array('name' => 'lang'), array('name' => 'update'), array('name' => 'net_info', 'sub' => array(array('name' => 'wired'), array('name' => 'pppoe', 'sub' => array(array('name' => 'dhcp'), array('name' => 'dhcp_manual'), array('name' => 'disable'))), array('name' => 'wireless'), array('name' => 'speed'))), array('name' => 'video'), array('name' => 'audio'), array('name' => 'net', 'sub' => array(array('name' => 'ethernet', 'sub' => array(array('name' => 'dhcp'), array('name' => 'dhcp_manual'), array('name' => 'manual'), array('name' => 'no_ip'))), array('name' => 'pppoe', 'sub' => array(array('name' => 'dhcp'), array('name' => 'dhcp_manual'), array('name' => 'disable'))), array('name' => 'wifi', 'sub' => array(array('name' => 'dhcp'), array('name' => 'dhcp_manual'), array('name' => 'manual'))), array('name' => 'speed'))), array('name' => 'advanced'), array('name' => 'dev_info'), array('name' => 'reload'), array('name' => 'internal_portal'), array('name' => 'reboot'))));
                            $rSettings['js']['parent_password'] = $rInfo['parent_password'];
                            $rSettings['js']['update_url'] = ipTV_lib::$settings['update_url'];
                            $rSettings['js']['test_download_url'] = ipTV_lib::$settings['test_download_url'];
                            $rSettings['js']['playback_buffer_size'] = $rInfo['playback_buffer_size'];
                            $rSettings['js']['screensaver_delay'] = $rInfo['screensaver_delay'];
                            $rSettings['js']['plasma_saving'] = $rInfo['plasma_saving'];
                            $rSettings['js']['spdif_mode'] = $rInfo['spdif_mode'];
                            $rSettings['js']['ts_enabled'] = $rInfo['ts_enabled'];
                            $rSettings['js']['ts_enable_icon'] = $rInfo['ts_enable_icon'];
                            $rSettings['js']['ts_path'] = $rInfo['ts_path'];
                            $rSettings['js']['ts_max_length'] = $rInfo['ts_max_length'];
                            $rSettings['js']['ts_buffer_use'] = $rInfo['ts_buffer_use'];
                            $rSettings['js']['ts_action_on_exit'] = $rInfo['ts_action_on_exit'];
                            $rSettings['js']['ts_delay'] = $rInfo['ts_delay'];
                            $rSettings['js']['hdmi_event_reaction'] = $rInfo['hdmi_event_reaction'];
                            $rSettings['js']['pri_audio_lang'] = $rProfile['pri_audio_lang'];
                            $rSettings['js']['show_after_loading'] = $rInfo['show_after_loading'];
                            $rSettings['js']['sec_audio_lang'] = $rProfile['sec_audio_lang'];

                            if (ipTV_lib::$settings['always_enabled_subtitles'] == 1) {
                                $rSettings['js']['pri_subtitle_lang'] = $rProfile['pri_subtitle_lang'];
                                $rSettings['js']['sec_subtitle_lang'] = $rProfile['sec_subtitle_lang'];
                            } else {
                                $rSettings['js']['sec_subtitle_lang'] = '';
                                $rSettings['js']['pri_subtitle_lang'] = $rSettings['js']['sec_subtitle_lang'];
                            }

                            exit(json_encode($rSettings));

                        case 'get_locales':
                            $ipTV_db->query('SELECT `locale` FROM `mag_devices` WHERE `mag_id` = \'%s\'', $rDevice['mag_id']);
                            $rSelected = $ipTV_db->get_row();
                            $rOutput = array();

                            foreach ($rLocales['get_locales'] as $country => $code) {
                                $rSelected = ($rSelected['locale'] == $code ? 1 : 0);
                                $rOutput[] = array('label' => $country, 'value' => $code, 'selected' => $rSelected);
                            }

                            exit(json_encode(array('js' => $rOutput)));


                        case 'set_aspect':
                            $rChannelID = ipTV_lib::$request['ch_id'];
                            $rAspect = ipTV_lib::$request['aspect'];
                            $rDeviceAspect = $rDevice['aspect'];

                            if (empty($rDeviceAspect)) {
                                $rDevice['aspect'] = array('js' => array($rChannelID => $rAspect));
                                $ipTV_db->query('UPDATE `mag_devices` SET `aspect` = \'%s\' WHERE mag_id = \'%s\'', json_encode(array('js' => array($rChannelID => $rAspect))), $rDevice['mag_id']);
                            } else {
                                $rDeviceAspect = json_decode($rDeviceAspect, true);
                                $rDeviceAspect['js'][$rChannelID] = $rAspect;
                                $rDevice['aspect'] = $rDeviceAspect;
                                $ipTV_db->query('UPDATE `mag_devices` SET `aspect` = \'%s\' WHERE mag_id = \'%s\'', json_encode($rDeviceAspect), $rDevice['mag_id']);
                            }

                            updatecache();

                            exit(json_encode(array('js' => true)));

                        case 'set_stream_error':
                            exit(json_encode(array('js' => true)));

                        case 'set_screensaver_delay':
                            if (!empty($_SERVER['HTTP_COOKIE'])) {
                                $rDelay = intval(ipTV_lib::$request['screensaver_delay']);
                                $rDevice['screensaver_delay'] = $rDelay;
                                $ipTV_db->query('UPDATE `mag_devices` SET `screensaver_delay` = \'%s\' WHERE `mag_id` = \'%s\'', $rDelay, $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));

                        case 'set_playback_buffer':
                            if (!empty($_SERVER['HTTP_COOKIE'])) {
                                $rBufferBytes = intval(ipTV_lib::$request['playback_buffer_bytes']);
                                $rBufferSize = intval(ipTV_lib::$request['playback_buffer_size']);
                                $rDevice['playback_buffer_bytes'] = $rBufferBytes;
                                $rDevice['playback_buffer_size'] = $rBufferSize;
                                $ipTV_db->query('UPDATE `mag_devices` SET `playback_buffer_bytes` = \'%s\' , `playback_buffer_size` = \'%s\' WHERE `mag_id` = \'%s\'', $rBufferBytes, $rBufferSize, $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));

                        case 'set_plasma_saving':
                            $rPlasmaSaving = intval(ipTV_lib::$request['plasma_saving']);
                            $rDevice['plasma_saving'] = $rPlasmaSaving;
                            $ipTV_db->query('UPDATE `mag_devices` SET `plasma_saving` = \'%s\' WHERE `mag_id` = \'%s\'', $rPlasmaSaving, $rDevice['mag_id']);
                            updatecache();

                            exit(json_encode(array('js' => true)));

                        case 'set_parent_password':
                            if (isset(ipTV_lib::$request['parent_password']) && isset(ipTV_lib::$request['pass']) && isset(ipTV_lib::$request['repeat_pass']) && ipTV_lib::$request['pass'] == ipTV_lib::$request['repeat_pass']) {
                                $rDevice['parent_password'] = ipTV_lib::$request['pass'];
                                $ipTV_db->query('UPDATE `mag_devices` SET `parent_password` = \'%s\' WHERE `mag_id` = \'%s\'', ipTV_lib::$request['pass'], $rDevice['mag_id']);
                                updatecache();

                                exit(json_encode(array('js' => true)));
                            }

                            exit(json_encode(array('js' => true)));

                        case 'set_locale':
                            if (!empty(ipTV_lib::$request['locale'])) {
                                $rDevice['locale'] = ipTV_lib::$request['locale'];
                                $ipTV_db->query('UPDATE `mag_devices` SET `locale` = \'%s\' WHERE `mag_id` = \'%s\'', ipTV_lib::$request['locale'], $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => array())));

                        case 'set_hdmi_reaction':
                            if (!empty($_SERVER['HTTP_COOKIE']) || isset(ipTV_lib::$request['data'])) {
                                $rReaction = ipTV_lib::$request['data'];
                                $rDevice['hdmi_event_reaction'] = $rReaction;
                                $ipTV_db->query('UPDATE `mag_devices` SET `hdmi_event_reaction` = \'%s\' WHERE `mag_id` = \'%s\'', $rReaction, $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));
                    }

                    break;

                case 'watchdog':
                    $rDevice['last_watchdog'] = time();
                    $ipTV_db->query('UPDATE `mag_devices` SET `last_watchdog` = \'%s\' WHERE `mag_id` = \'%s\'', time(), $rDevice['mag_id']);
                    updatecache();

                    switch ($rReqAction) {
                        case 'get_events':
                            $ipTV_db->query('SELECT * FROM `mag_events` WHERE `mag_device_id` = \'%s\' AND `status` = 0 ORDER BY `id` ASC LIMIT 1', $rDevice['mag_id']);
                            $rData = array('data' => array('msgs' => 0, 'additional_services_on' => 1));

                            if ($ipTV_db->num_rows() > 0) {
                                $rEvents = $ipTV_db->get_row();
                                $ipTV_db->query('SELECT count(*) FROM `mag_events` WHERE `mag_device_id` = \'%s\' AND `status` = 0 ', $rDevice['mag_id']);
                                $rMessages = $ipTV_db->get_col();
                                $rData = array('data' => array('msgs' => $rMessages, 'id' => $rEvents['id'], 'event' => $rEvents['event'], 'need_confirm' => $rEvents['need_confirm'], 'msg' => $rEvents['msg'], 'reboot_after_ok' => $rEvents['reboot_after_ok'], 'auto_hide_timeout' => $rEvents['auto_hide_timeout'], 'send_time' => date('d-m-Y H:i:s', $rEvents['send_time']), 'additional_services_on' => $rEvents['additional_services_on'], 'updated' => array('anec' => $rEvents['anec'], 'vclub' => $rEvents['vclub'])));
                                $rAutoStatus = array('reboot', 'reload_portal', 'play_channel', 'cut_off');

                                if (in_array($rEvents['event'], $rAutoStatus)) {
                                    $ipTV_db->query('UPDATE `mag_events` SET `status` = 1 WHERE `id` = \'%s\'', $rEvents['id']);
                                }
                            }

                            exit(json_encode(array('js' => $rData), JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'confirm_event':
                            if (empty(ipTV_lib::$request['event_active_id'])) {
                                break;
                            }

                            $rActiveID = ipTV_lib::$request['event_active_id'];
                            $ipTV_db->query('UPDATE `mag_events` SET `status` = 1 WHERE `id` = \'%s\'', $rActiveID);

                            exit(json_encode(array('js' => array('data' => 'ok'))));
                    }

                    break;

                case 'audioclub':
                    switch ($rReqAction) {
                        case 'get_categories':
                            $rOutput = array();
                            $rOutput['js'] = array();

                            if (ipTV_lib::$settings['show_all_category_mag'] == 1) {
                                $rOutput['js'][] = array('id' => '*', 'title' => 'All', 'alias' => '*', 'censored' => 0);
                            }

                            foreach (ipTV_lib::$categories as $rCategoryID => $rCategory) {
                                if ($rCategory['category_type'] == 'movie' && in_array($rCategory['id'], $rDevice['category_ids'])) {
                                    $rOutput['js'][] = array('id' => $rCategory['id'], 'title' => $rCategory['category_name'], 'alias' => $rCategory['category_name'], 'censored' => intval($rCategory['is_adult']));
                                }
                            }

                            exit(json_encode($rOutput));
                    }

                    break;

                case 'itv':
                    switch ($rReqAction) {
                        case 'create_link':
                            $rCommand = ipTV_lib::$request['cmd'];
                            $rValue = 'http://localhost/ch/';
                            list($rStreamID, $rStreamValue) = explode('_', substr($rCommand, strpos($rCommand, $rValue) + strlen($rValue)));

                            if (empty($rStreamValue)) {
                                $rEncData = 'ministra::live/' . $rDevice['username'] . '/' . $rDevice['password'] . '/' . $rStreamID . '/' . ipTV_lib::$settings['mag_container'] . '/' . $rDevice['token'];
                                $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                                $rURL = $rPlayer . ((ipTV_lib::$settings['mag_disable_ssl'] ? ipTV_lib::$StreamingServers[SERVER_ID]['http_url'] : ipTV_lib::$StreamingServers[SERVER_ID]['site_url'])) . 'play/' . $rToken;

                                if (ipTV_lib::$settings['mag_keep_extension']) {
                                    $rURL .= '?ext=.' . ipTV_lib::$settings['mag_container'];
                                }
                            } else {
                                $rURL = $rPlayer . $rStreamValue;
                            }

                            exit(json_encode(array('js' => array('id' => $rStreamID, 'cmd' => $rURL), 'streamer_id' => 0, 'link_id' => 0, 'load' => 0, 'error' => '')));

                        case 'set_claim':
                            if (!empty(ipTV_lib::$request['id']) || !empty(ipTV_lib::$request['real_type'])) {
                                $rID = intval(ipTV_lib::$request['id']);
                                $rRealType = ipTV_lib::$request['real_type'];
                                $rDate = date('Y-m-d H:i:s');
                                $ipTV_db->query('INSERT INTO `mag_claims` (`stream_id`,`mag_id`,`real_type`,`date`) VALUES(\'%s\', \'%s\', \'%s\', \'%s\')', $rID, $rDevice['mag_id'], $rRealType, $rDate);
                            }

                            exit(json_encode(array('js' => true)));

                        case 'set_fav':
                            $rChannels = (empty(ipTV_lib::$request['fav_ch']) ? '' : ipTV_lib::$request['fav_ch']);
                            $rChannels = array_filter(array_map('intval', explode(',', $rChannels)));
                            $rDevice['fav_channels']['live'] = $rChannels;
                            $ipTV_db->query('UPDATE `mag_devices` SET `fav_channels` = \'%s\' WHERE `mag_id` = \'%s\'', json_encode($rDevice['fav_channels']), $rDevice['mag_id']);
                            updatecache();

                            exit(json_encode(array('js' => true)));

                        case 'get_fav_ids':
                            exit(json_encode(array('js' => $rDevice['fav_channels']['live'])));

                        case 'get_all_channels':
                            $rGenre = (empty(ipTV_lib::$request['genre']) || !is_numeric(ipTV_lib::$request['genre']) ? null : intval(ipTV_lib::$request['genre']));

                            exit(getStreams($rGenre, true));

                        case 'get_ordered_list':
                            $rFav = (!empty(ipTV_lib::$request['fav']) ? 1 : null);
                            $rSortBy = (!empty(ipTV_lib::$request['sortby']) ? ipTV_lib::$request['sortby'] : null);
                            $rGenre = (empty(ipTV_lib::$request['genre']) || !is_numeric(ipTV_lib::$request['genre']) ? null : intval(ipTV_lib::$request['genre']));
                            $rSearch = (!empty(ipTV_lib::$request['search']) ? ipTV_lib::$request['search'] : null);

                            exit(getStreams($rGenre, false, $rFav, $rSortBy, $rSearch));

                        case 'get_all_fav_channels':
                            $rGenre = (empty(ipTV_lib::$request['genre']) || !is_numeric(ipTV_lib::$request['genre']) ? null : intval(ipTV_lib::$request['genre']));

                            exit(getStreams($rGenre, true, 1));

                        case 'get_epg_info':
                            exit(json_encode(array('js' => array('data' => array())), JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'get_short_epg':
                            if (!empty(ipTV_lib::$request['ch_id'])) {
                                $rChannelID = ipTV_lib::$request['ch_id'];
                                $rEPG = array('js' => array());
                                $rTime = time();
                                $rEPGData = array();

                                if (file_exists(EPG_PATH . 'stream_' . intval($rChannelID))) {
                                    $rRows = unserialize(file_get_contents(EPG_PATH . 'stream_' . $rChannelID));

                                    foreach ($rRows as $rRow) {
                                        if ($rRow['start'] <= $rTime && $rTime <= $rRow['end'] || $rTime <= $rRow['start']) {
                                            $rRow['start_timestamp'] = $rRow['start'];
                                            $rRow['stop_timestamp'] = $rRow['end'];
                                            $rEPGData[] = $rRow;
                                        }
                                    }
                                }

                                if (!empty($rEPGData)) {
                                    $rTimeDifference = (ipTV_lib::getDiffTimezone($rTimezone) ?: 0);
                                    $i = 0;

                                    for ($n = 0; $n < count($rEPGData); $n++) {
                                        if ($rEPGData[$n]['end'] >= time()) {
                                            $rStartTime = new DateTime();
                                            $rStartTime->setTimestamp($rEPGData[$n]['start']);
                                            $rStartTime->modify((string) $rTimeDifference . ' seconds');
                                            $rEndTime = new DateTime();
                                            $rEndTime->setTimestamp($rEPGData[$n]['end']);
                                            $rEndTime->modify((string) $rTimeDifference . ' seconds');
                                            $rEPG['js'][$i]['id'] = $rEPGData[$n]['id'];
                                            $rEPG['js'][$i]['ch_id'] = $rChannelID;
                                            $rEPG['js'][$i]['correct'] = $rStartTime->format('Y-m-d H:i:s');
                                            $rEPG['js'][$i]['time'] = $rStartTime->format('Y-m-d H:i:s');
                                            $rEPG['js'][$i]['time_to'] = $rEndTime->format('Y-m-d H:i:s');
                                            $rEPG['js'][$i]['duration'] = $rEPGData[$n]['stop_timestamp'] - $rEPGData[$n]['start_timestamp'];
                                            $rEPG['js'][$i]['name'] = $rEPGData[$n]['title'];
                                            $rEPG['js'][$i]['descr'] = $rEPGData[$n]['description'];
                                            $rEPG['js'][$i]['real_id'] = $rChannelID . '_' . $rEPGData[$n]['start_timestamp'];
                                            $rEPG['js'][$i]['category'] = '';
                                            $rEPG['js'][$i]['director'] = '';
                                            $rEPG['js'][$i]['actor'] = '';
                                            $rEPG['js'][$i]['start_timestamp'] = $rStartTime->getTimestamp();
                                            $rEPG['js'][$i]['stop_timestamp'] = $rEndTime->getTimestamp();
                                            $rEPG['js'][$i]['t_time'] = $rStartTime->format('H:i');
                                            $rEPG['js'][$i]['t_time_to'] = $rEndTime->format('H:i');
                                            $rEPG['js'][$i]['mark_memo'] = 0;
                                            $rEPG['js'][$i]['mark_archive'] = 0;

                                            if (count($rEPG['js']) != ((intval(ipTV_lib::$request['size']) ?: 4))) {
                                                $i++;
                                            }
                                        }
                                    }
                                }
                            }

                            exit(json_encode($rEPG, JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'set_last_id':
                            $rChannelID = intval(ipTV_lib::$request['id']);

                            if ($rChannelID > 0) {
                                $rDevice['last_itv_id'] = $rChannelID;
                                $ipTV_db->query('UPDATE `mag_devices` SET `last_itv_id` = \'%s\' WHERE `mag_id` = \'%s\'', $rChannelID, $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));

                        case 'get_genres':
                            $rOutput = array();
                            $rNumber = 1;

                            if (ipTV_lib::$settings['show_all_category_mag'] == 1) {
                                $rOutput['js'][] = array('id' => '*', 'title' => 'All', 'alias' => 'All', 'active_sub' => true, 'censored' => 0);
                            }

                            foreach (ipTV_lib::$categories as $rCategoryID => $rCategory) {
                                if ($rCategory['category_type'] == 'live' && in_array($rCategory['id'], $rDevice['category_ids'])) {
                                    $rOutput['js'][] = array('id' => $rCategory['id'], 'title' => $rCategory['category_name'], 'modified' => '', 'number' => $rNumber++, 'alias' => strtolower($rCategory['category_name']), 'censored' => intval($rCategory['is_adult']));
                                }
                            }

                            exit(json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR));
                    }

                    break;
                case 'vod':
                    switch ($rReqAction) {
                        case 'set_claim':
                            if (!empty(ipTV_lib::$request['id']) || !empty(ipTV_lib::$request['real_type'])) {
                                $rID = intval(ipTV_lib::$request['id']);
                                $rRealType = ipTV_lib::$request['real_type'];
                                $rDate = date('Y-m-d H:i:s');
                                $ipTV_db->query('INSERT INTO `mag_claims` (`stream_id`,`mag_id`,`real_type`,`date`) VALUES(\'%s\', \'%s\', \'%s\', \'%s\')', $rID, $rDevice['mag_id'], $rRealType, $rDate);
                            }

                        case 'set_fav':
                            if (!empty(ipTV_lib::$request['video_id'])) {
                                $rVideoID = intval(ipTV_lib::$request['video_id']);

                                if (!in_array($rVideoID, $rDevice['fav_channels']['movie'])) {
                                    $rDevice['fav_channels']['movie'][] = $rVideoID;
                                }

                                $ipTV_db->query('UPDATE `mag_devices` SET `fav_channels` = \'%s\' WHERE `mag_id` = \'%s\'', json_encode($rDevice['fav_channels']), $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));

                        case 'del_fav':
                            if (!empty(ipTV_lib::$request['video_id'])) {
                                $rVideoID = intval(ipTV_lib::$request['video_id']);

                                foreach ($rDevice['fav_channels']['movie'] as $rKey => $rValue) {
                                    if ($rValue == $rVideoID) {
                                        unset($rDevice['fav_channels']['movie'][$rKey]);
                                        break;
                                    }
                                }
                                $ipTV_db->query('UPDATE `mag_devices` SET `fav_channels` = \'%s\' WHERE `mag_id` = \'%s\'', json_encode($rDevice['fav_channels']), $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));

                        case 'get_categories':
                            $rOutput = array();
                            $rOutput['js'] = array();

                            if (ipTV_lib::$settings['show_all_category_mag'] == 1) {
                                $rOutput['js'][] = array('id' => '*', 'title' => 'All', 'alias' => '*', 'censored' => 0);
                            }

                            foreach (ipTV_lib::$categories as $rCategoryID => $rCategory) {
                                if ($rCategory['category_type'] == 'movie' && in_array($rCategory['id'], $rDevice['category_ids'])) {
                                    $rOutput['js'][] = array('id' => $rCategory['id'], 'title' => $rCategory['category_name'], 'alias' => $rCategory['category_name'], 'censored' => intval($rCategory['is_adult']));
                                }
                            }

                            exit(json_encode($rOutput));

                        case 'get_genres_by_category_alias':
                            $rOutput = array();
                            $rOutput['js'][] = array('id' => '*', 'title' => '*');

                            foreach (ipTV_lib::$categories as $rCategoryID => $rCategory) {
                                if ($rCategory['category_type'] == 'movie' && in_array($rCategory['id'], $rDevice['category_ids'])) {
                                    $rOutput['js'][] = array('id' => $rCategory['id'], 'title' => $rCategory['category_name']);
                                }
                            }

                            exit(json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'get_years':
                            exit(json_encode($rMagData['get_years']));

                        case 'get_ordered_list':
                            $rCategory = (!empty(ipTV_lib::$request['category']) && is_numeric(ipTV_lib::$request['category']) ? ipTV_lib::$request['category'] : null);
                            $rFav = (!empty(ipTV_lib::$request['fav']) ? 1 : null);
                            $rSortBy = (!empty(ipTV_lib::$request['sortby']) ? ipTV_lib::$request['sortby'] : 'added');
                            $rSearch = (!empty(ipTV_lib::$request['search']) ? ipTV_lib::$request['search'] : null);
                            $rPicking = array();
                            $rPicking['abc'] = (!empty(ipTV_lib::$request['abc']) ? ipTV_lib::$request['abc'] : '*');
                            $rPicking['genre'] = (!empty(ipTV_lib::$request['genre']) ? ipTV_lib::$request['genre'] : '*');
                            $rPicking['years'] = (!empty(ipTV_lib::$request['years']) ? ipTV_lib::$request['years'] : '*');

                            exit(getMovies($rCategory, $rFav, $rSortBy, $rSearch, $rPicking));

                        case 'create_link':
                            $rCommand = ipTV_lib::$request['cmd'];
                            $rSeries = (!empty(ipTV_lib::$request['series']) ? (int) ipTV_lib::$request['series'] : 0);
                            $rError = '';

                            if (!stristr($rCommand, '/media/')) {
                                $rCommand = json_decode(base64_decode($rCommand), true);
                            } else {
                                $rCommand = array('series_data' => $rCommand, 'type' => 'series');
                            }

                            if ($rSeries) {
                                $rCommand['type'] = 'series';
                            }

                            $rValid = false;

                            switch ($rCommand['type']) {
                                case 'movie':
                                    $rValid = in_array($rCommand['stream_id'], $rDevice['vod_ids']);

                                    break;

                                case 'series':
                                    if (!empty($rCommand['series_data'])) {
                                        list($rCommand['series_id'], $rCommand['season_num']) = explode(':', basename($rCommand['series_data'], '.mpg'));
                                    }

                                    $ipTV_db->query('SELECT t1.stream_id,t2.target_container FROM `streams_episodes` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id WHERE t1.`series_id` = \'%s\' AND t1.`season_num` = \'%s\' ORDER BY `episode_num` ASC LIMIT ' . intval($rSeries - 1) . ', 1', $rCommand['series_id'], $rCommand['season_num']);

                                    if (0 < $ipTV_db->num_rows()) {
                                        $rRow = $ipTV_db->get_row();
                                        $rCommand['stream_id'] = $rRow['stream_id'];
                                        $rCommand['target_container'] = $rRow['target_container'];
                                        $rValid = in_array($rCommand['series_id'], $rDevice['series_ids']);
                                    } else {
                                        $rError = 'player_file_missing';
                                    }
                            }
                            $rEncData = 'ministra::' . $rCommand['type'] . '/' . $rDevice['username'] . '/' . $rDevice['password'] . '/' . $rCommand['stream_id'] . '/' . $rCommand['target_container'] . '/' . $rDevice['token'];
                            $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                            $rURL = ((ipTV_lib::$settings['mag_disable_ssl'] ? ipTV_lib::$StreamingServers[SERVER_ID]['http_url'] : ipTV_lib::$StreamingServers[SERVER_ID]['site_url'])) . 'play/' . $rToken;

                            if (ipTV_lib::$settings['mag_keep_extension']) {
                                $rURL .= '?ext=.' . $rCommand['target_container'];
                            }

                            $rOutput = array('js' => array('id' => $rCommand['stream_id'], 'cmd' => $rPlayer . $rURL, 'load' => '', 'subtitles' => array(), 'error' => $rError));

                            exit(json_encode($rOutput));

                        case 'get_abc':
                            exit(json_encode($rMagData['get_abc']));
                    }

                    break;

                case 'series':
                    switch ($rReqAction) {
                        case 'set_claim':
                            if (!empty(ipTV_lib::$request['id']) || !empty(ipTV_lib::$request['real_type'])) {
                                $rID = intval(ipTV_lib::$request['id']);
                                $rRealType = ipTV_lib::$request['real_type'];
                                $rDate = date('Y-m-d H:i:s');
                                $ipTV_db->query('INSERT INTO `mag_claims` (`stream_id`,`mag_id`,`real_type`,`date`) VALUES(\'%s\', \'%s\', \'%s\', \'%s\')', $rID, $rDevice['mag_id'], $rRealType, $rDate);
                            }

                            exit(json_encode(array('js' => true)));

                        case 'set_fav':
                            if (!empty(ipTV_lib::$request['video_id'])) {
                                $rVideoID = intval(ipTV_lib::$request['video_id']);

                                if (!in_array($rVideoID, $rDevice['fav_channels']['series'])) {
                                    $rDevice['fav_channels']['series'][] = $rVideoID;
                                }

                                $ipTV_db->query('UPDATE `mag_devices` SET `fav_channels` = \'%s\' WHERE `mag_id` = \'%s\'', json_encode($rDevice['fav_channels']), $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));

                        case 'del_fav':
                            if (!empty(ipTV_lib::$request['video_id'])) {
                                $rVideoID = intval(ipTV_lib::$request['video_id']);

                                foreach ($rDevice['fav_channels']['series'] as $rKey => $rValue) {
                                    if ($rValue == $rVideoID) {
                                        unset($rDevice['fav_channels']['series'][$rKey]);

                                        break;
                                    }
                                }
                                $ipTV_db->query('UPDATE `mag_devices` SET `fav_channels` = \'%s\' WHERE `mag_id` = \'%s\'', json_encode($rDevice['fav_channels']), $rDevice['mag_id']);
                                updatecache();
                            }

                            exit(json_encode(array('js' => true)));

                        case 'get_categories':
                            $rOutput = array();
                            $rOutput['js'] = array();

                            if (ipTV_lib::$settings['show_all_category_mag'] == 1) {
                                $rOutput['js'][] = array('id' => '*', 'title' => 'All', 'alias' => '*', 'censored' => 0);
                            }

                            foreach (ipTV_lib::$categories as $rCategoryID => $rCategory) {
                                if ($rCategory['category_type'] == 'series' && in_array($rCategory['id'], $rDevice['category_ids'])) {
                                    $rOutput['js'][] = array('id' => $rCategory['id'], 'title' => $rCategory['category_name'], 'alias' => $rCategory['category_name'], 'censored' => intval($rCategory['is_adult']));
                                }
                            }

                            exit(json_encode($rOutput));

                        case 'get_genres_by_category_alias':
                            $rOutput = array();
                            $rOutput['js'][] = array('id' => '*', 'title' => '*');

                            foreach (ipTV_lib::$categories as $rCategoryID => $rCategory) {
                                if ($rCategory['category_type'] == 'series' && in_array($rCategory['id'], $rDevice['category_ids'])) {
                                    $rOutput['js'][] = array('id' => $rCategory['id'], 'title' => $rCategory['category_name']);
                                }
                            }

                            exit(json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'get_years':
                            exit(json_encode($rMagData['get_years']));

                        case 'get_ordered_list':
                            $rCategory = (!empty(ipTV_lib::$request['category']) && is_numeric(ipTV_lib::$request['category']) ? ipTV_lib::$request['category'] : null);
                            $rFav = (!empty(ipTV_lib::$request['fav']) ? 1 : null);
                            $rSortBy = (!empty(ipTV_lib::$request['sortby']) ? ipTV_lib::$request['sortby'] : 'added');
                            $rSearch = (!empty(ipTV_lib::$request['search']) ? ipTV_lib::$request['search'] : null);
                            $rMovieID = (!empty(ipTV_lib::$request['movie_id']) ? (int) ipTV_lib::$request['movie_id'] : null);
                            $rPicking = array();
                            $rPicking['abc'] = (!empty(ipTV_lib::$request['abc']) ? ipTV_lib::$request['abc'] : '*');
                            $rPicking['genre'] = (!empty(ipTV_lib::$request['genre']) ? ipTV_lib::$request['genre'] : '*');
                            $rPicking['years'] = (!empty(ipTV_lib::$request['years']) ? ipTV_lib::$request['years'] : '*');

                            exit(getSeries($rMovieID, $rCategory, $rFav, $rSortBy, $rSearch, $rPicking));

                        case 'get_abc':
                            exit(json_encode($rMagData['get_abc']));
                    }

                    break;
                case 'account_info':
                    switch ($rReqAction) {
                        case 'get_main_info':
                            if (empty($rDevice['exp_date'])) {
                                $rExpiry = 'Unlimited';
                            } else {
                                $rExpiry = date('F j, Y, g:i a', $rDevice['exp_date']);
                            }

                            exit(json_encode(array('js' => array('mac' => $rMAC, 'phone' => $rExpiry, 'message' => htmlspecialchars_decode(str_replace("\n", '<br/>', ipTV_lib::$settings['mag_message']))))));
                    }
                    break;

                case 'radio':
                    switch ($rReqAction) {
                        case 'get_ordered_list':
                            $rFav = (!empty(ipTV_lib::$request['fav']) ? 1 : null);
                            $rSortBy = (!empty(ipTV_lib::$request['sortby']) ? ipTV_lib::$request['sortby'] : 'added');

                            exit(getStations(null, $rFav, $rSortBy));

                        case 'get_all_fav_radio':
                            exit(getStations(null, 1, null));

                        case 'set_fav':
                            $f3f9f9fa3c58c22b = (empty(ipTV_lib::$request['fav_radio']) ? '' : ipTV_lib::$request['fav_radio']);
                            $f3f9f9fa3c58c22b = array_filter(array_map('intval', explode(',', $f3f9f9fa3c58c22b)));
                            $rDevice['fav_channels']['radio_streams'] = $f3f9f9fa3c58c22b;
                            $ipTV_db->query('UPDATE `mag_devices` SET `fav_channels` = \'%s\' WHERE `mag_id` = \'%s\'', json_encode($rDevice['fav_channels']), $rDevice['mag_id']);
                            updatecache();

                            exit(json_encode(array('js' => true)));

                        case 'get_fav_ids':
                            exit(json_encode(array('js' => $rDevice['fav_channels']['radio_streams'])));
                    }

                    break;

                case 'tv_archive':
                    switch ($rReqAction) {
                        case 'get_next_part_url':
                            if (!empty(ipTV_lib::$request['id'])) {
                                $rID = ipTV_lib::$request['id'];
                                $rStreamID = substr($rID, 0, strpos($rID, '_'));
                                $rDate = strtotime(substr($rID, strpos($rID, '_') + 1));
                                $rRow = (getepg($rStreamID, $rDate, $rDate + 86400)[0] ?: null);

                                if ($rRow) {
                                    $rRow = $ipTV_db->get_row();
                                    $rProgramStart = $rRow['start'];
                                    $rDuration = intval(($rRow['end'] - $rRow['start']) / 60);
                                    $rTitle = $rRow['title'];
                                    $rEncData = 'ministra::timeshift/' . $rDevice['username'] . '/' . $rDevice['password'] . '/' . $rDuration . '/' . $rProgramStart . '/' . $rStreamID . '/' . $rDevice['token'];
                                    $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                                    $rURL = ((ipTV_lib::$settings['mag_disable_ssl'] ? ipTV_lib::$StreamingServers[SERVER_ID]['http_url'] : ipTV_lib::$StreamingServers[SERVER_ID]['site_url'])) . 'play/' . $rToken . '?&osd_title=' . $rTitle;

                                    if (ipTV_lib::$settings['mag_keep_extension']) {
                                        $rURL .= '&ext=.ts';
                                    }

                                    exit(json_encode(array('js' => $rPlayer . $rURL)));
                                }
                            }

                            exit(json_encode(array('js' => false)));

                        case 'create_link':
                            $rCommand = (empty(ipTV_lib::$request['cmd']) ? '' : ipTV_lib::$request['cmd']);
                            list($rEPGDataID, $rStreamID) = explode('_', pathinfo($rCommand)['filename']);
                            $rRow = (getprogramme($rStreamID, $rEPGDataID) ?: null);

                            if (!$rRow) {
                                break;
                            }

                            $rStart = $rRow['start'];
                            $rDuration = intval(($rRow['end'] - $rRow['start']) / 60);
                            $rEncData = 'ministra::timeshift/' . $rDevice['username'] . '/' . $rDevice['password'] . '/' . $rDuration . '/' . $rStart . '/' . $rStreamID . '/' . $rDevice['token'];
                            $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                            $rURL = ((ipTV_lib::$settings['mag_disable_ssl'] ? ipTV_lib::$StreamingServers[SERVER_ID]['http_url'] : ipTV_lib::$StreamingServers[SERVER_ID]['site_url'])) . 'play/' . $rToken;

                            if (ipTV_lib::$settings['mag_keep_extension']) {
                                $rURL .= '?ext=.ts';
                            }

                            $rOutput['js'] = array('id' => 0, 'cmd' => $rPlayer . $rURL, 'storage_id' => '', 'load' => 0, 'error' => '', 'download_cmd' => $rURL, 'to_file' => '');

                            exit(json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'get_link_for_channel':
                            $rOutput = array();
                            $rChannelID = (!empty(ipTV_lib::$request['ch_id']) ? intval(ipTV_lib::$request['ch_id']) : 0);
                            $rStart = strtotime(date('Ymd-H'));
                            $rEncData = 'ministra::timeshift/' . $rDevice['username'] . '/' . $rDevice['password'] . '/60/' . $rStart . '/' . $rChannelID . '/' . $rDevice['token'];
                            $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                            $rURL = ((ipTV_lib::$settings['mag_disable_ssl'] ? ipTV_lib::$StreamingServers[SERVER_ID]['http_url'] : ipTV_lib::$StreamingServers[SERVER_ID]['site_url'])) . 'play/' . $rToken . ((ipTV_lib::$settings['mag_keep_extension'] ? '?ext=.ts' : '')) . ' position:' . (intval(date('i')) * 60 + intval(date('s'))) . ' media_len:' . (intval(date('H')) * 3600 + intval(date('i')) * 60 + intval(date('s')));
                            $rOutput['js'] = array('id' => 0, 'cmd' => $rPlayer . $rURL, 'storage_id' => '', 'load' => 0, 'error' => '');

                            exit(json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR));
                    }

                    break;

                case 'epg':
                    switch ($rReqAction) {
                        case 'get_week':
                            $k = -16;
                            $i = 0;
                            $rEPGWeek = array();
                            $rCurDate = strtotime(date('Y-m-d'));

                            while ($k < 10) { // >=  fixed???
                                $rThisDate = $rCurDate + $k * 86400;
                                $rEPGWeek['js'][$i]['f_human'] = date('D d F', $rThisDate);
                                $rEPGWeek['js'][$i]['f_mysql'] = date('Y-m-d', $rThisDate);
                                $rEPGWeek['js'][$i]['today'] = ($k == 0 ? 1 : 0);
                                $k++;
                                $i++;
                            }

                            exit(json_encode($rEPGWeek));

                        case 'get_data_table':
                            exit(json_encode(array('js' => array()), JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'get_simple_data_table':
                            if (empty(ipTV_lib::$request['ch_id']) || empty(ipTV_lib::$request['date'])) {
                                exit();
                            }

                            $rChannelID = ipTV_lib::$request['ch_id'];
                            $rReqDate = ipTV_lib::$request['date'];
                            $rPage = intval(ipTV_lib::$request['p']);
                            $rPageItems = 10;
                            $rDefaultPage = false;
                            $rEPGDatas = array();
                            $rStartTime = strtotime($rReqDate . ' 00:00:00');
                            $rEndTime = strtotime($rReqDate . ' 23:59:59');

                            if (file_exists(EPG_PATH . 'stream_' . intval($rChannelID))) {
                                $rRows = unserialize(file_get_contents(EPG_PATH . 'stream_' . $rChannelID));

                                foreach ($rRows as $rRow) {
                                    if ($rStartTime <= $rRow['start'] && $rRow['start'] <= $rEndTime) {
                                        $rRow['start_timestamp'] = $rRow['start'];
                                        $rRow['stop_timestamp'] = $rRow['end'];
                                        $rEPGDatas[] = $rRow;
                                    }
                                }
                            }

                            if (file_exists(STREAMS_TMP_PATH . 'stream_' . intval($rChannelID))) {
                                $rStreamRow = unserialize(file_get_contents(STREAMS_TMP_PATH . 'stream_' . intval($rChannelID)))['info'];
                            } else {
                                $ipTV_db->query('SELECT `tv_archive_duration` FROM `streams` WHERE `id` = \'%s\';', ipTV_lib::$request['ch_id']);

                                if ($ipTV_db->num_rows() > 0) {
                                    $rStreamRow = $ipTV_db->get_row();
                                }
                            }

                            $rChannelIDx = 0;

                            foreach ($rEPGDatas as $rKey => $rEPGData) {
                                if ($rEPGData['start_timestamp'] <= time() && time() <= $rEPGData['stop_timestamp']) {
                                    $rChannelIDx = $rKey + 1;

                                    break;
                                }
                            }
                            if ($rPage == 0) {
                                $rDefaultPage = true;
                                $rPage = ceil($rChannelIDx / $rPageItems);

                                if ($rPage == 0) {
                                    $rPage = 1;
                                }

                                if ($rReqDate != date('Y-m-d')) {
                                    $rPage = 1;
                                    $rDefaultPage = false;
                                }
                            }

                            $rProgram = array_slice($rEPGDatas, ($rPage - 1) * $rPageItems, $rPageItems);
                            $rData = array();
                            $rTimeDifference = ipTV_lib::getDiffTimezone($rTimezone);

                            for ($i = 0; $i < count($rProgram); $i++) {
                                $open = 0;

                                if (time() < $rProgram[$i]['stop_timestamp']) {
                                    $open = 1;
                                }

                                $rStartTime = new DateTime();
                                $rStartTime->setTimestamp($rProgram[$i]['start']);
                                $rStartTime->modify((string) $rTimeDifference . ' seconds');
                                $rEndTime = new DateTime();
                                $rEndTime->setTimestamp($rProgram[$i]['end']);
                                $rEndTime->modify((string) $rTimeDifference . ' seconds');
                                $rData[$i]['id'] = $rProgram[$i]['id'] . '_' . $rChannelID;
                                $rData[$i]['ch_id'] = $rChannelID;
                                $rData[$i]['time'] = $rStartTime->format('Y-m-d H:i:s');
                                $rData[$i]['time_to'] = $rEndTime->format('Y-m-d H:i:s');
                                $rData[$i]['duration'] = $rProgram[$i]['stop_timestamp'] - $rProgram[$i]['start_timestamp'];
                                $rData[$i]['name'] = $rProgram[$i]['title'];
                                $rData[$i]['descr'] = $rProgram[$i]['description'];
                                $rData[$i]['real_id'] = $rChannelID . '_' . $rProgram[$i]['start'];
                                $rData[$i]['category'] = '';
                                $rData[$i]['director'] = '';
                                $rData[$i]['actor'] = '';
                                $rData[$i]['start_timestamp'] = $rStartTime->getTimestamp();
                                $rData[$i]['stop_timestamp'] = $rEndTime->getTimestamp();
                                $rData[$i]['t_time'] = $rStartTime->format('H:i');
                                $rData[$i]['t_time_to'] = $rEndTime->format('H:i');
                                $rData[$i]['open'] = $open;
                                $rData[$i]['mark_memo'] = 0;
                                $rData[$i]['mark_rec'] = 0;
                                $rData[$i]['mark_archive'] = (!empty($rStreamRow['tv_archive_duration']) && $rEndTime->getTimestamp() < time() && strtotime('-' . $rStreamRow['tv_archive_duration'] . ' days') <= $rEndTime->getTimestamp() ? 1 : 0);
                            }

                            if ($rDefaultPage) {
                                $rCurrentPage = $rPage;
                                $rSelectedItem = $rChannelIDx - ($rPage - 1) * $rPageItems;
                            } else {
                                $rCurrentPage = 0;
                                $rSelectedItem = 0;
                            }

                            $rOutput = array();
                            $rOutput['js']['cur_page'] = $rCurrentPage;
                            $rOutput['js']['selected_item'] = $rSelectedItem;
                            $rOutput['js']['total_items'] = count($rEPGDatas);
                            $rOutput['js']['max_page_items'] = $rPageItems;
                            $rOutput['js']['data'] = $rData;

                            exit(json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR));

                        case 'get_all_program_for_ch':
                            $rOutput = array();
                            $rOutput['js'] = array();
                            $rChannelID = (empty(ipTV_lib::$request['ch_id']) ? 0 : intval(ipTV_lib::$request['ch_id']));
                            $rTimeDifference = ipTV_lib::getDiffTimezone($rTimezone);

                            if (file_exists(STREAMS_TMP_PATH . 'stream_' . intval($rChannelID))) {
                                $rStreamRow = unserialize(file_get_contents(STREAMS_TMP_PATH . 'stream_' . intval($rChannelID)))['info'];
                            } else {
                                $ipTV_db->query('SELECT `tv_archive_duration` FROM `streams` WHERE `id` = \'%s\';', ipTV_lib::$request['ch_id']);

                                if ($ipTV_db->num_rows() > 0) {
                                    $rStreamRow = $ipTV_db->get_row();
                                }
                            }

                            $rTime = strtotime(date('Y-m-d 00:00:00'));

                            if (file_exists(EPG_PATH . 'stream_' . intval($rChannelID))) {
                                $rRows = unserialize(file_get_contents(EPG_PATH . 'stream_' . $rChannelID));

                                foreach ($rRows as $rRow) {
                                    if ($rTime < $rRow['start']) {
                                        $rRow['start_timestamp'] = $rRow['start'];
                                        $rRow['stop_timestamp'] = $rRow['end'];
                                        $rStartTime = new DateTime();
                                        $rStartTime->setTimestamp($rRow['start']);
                                        $rStartTime->modify((string) $rTimeDifference . ' seconds');
                                        $rEndTime = new DateTime();
                                        $rEndTime->setTimestamp($rRow['end']);
                                        $rEndTime->modify((string) $rTimeDifference . ' seconds');
                                        $rOutput['js'][] = array('start_timestamp' => $rStartTime->getTimestamp(), 'stop_timestamp' => $rEndTime->getTimestamp(), 'name' => $rRow['title']);
                                    }
                                }
                            }

                            exit(json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR));
                    }

                    break;
            }
        } else {
            if ($rReqType == 'stb' && $rReqAction == 'get_profile') {
                checkBruteforce($rIP, $rMAC);
                checkFlood();
            }

            exit();
        }
}



function getSeriesItems($rUserID, $rType = 'series', $rCategoryID = null, $rFav = null, $rOrderBy = null, $rSearchBy = null, $rPicking = array()) {
    global $rDevice;
    global $ipTV_db;

    if (0 < count($rDevice['series_ids'])) {
        $ipTV_db->query('SELECT *, (SELECT MAX(`streams`.`added`) FROM `streams_episodes` LEFT JOIN `streams` ON `streams`.`id` = `streams_episodes`.`stream_id` WHERE `streams_episodes`.`series_id` = `streams_series`.`id`) AS `last_modified_stream` FROM `streams_series` WHERE `id` IN (' . implode(',', array_map('intval', $rDevice['series_ids'])) . ') ORDER BY `last_modified_stream` DESC, `last_modified` DESC;');
        $rSeries = $ipTV_db->get_rows(true, 'id');
    } else {
        $rSeries = array();
    }

    $rOutputSeries = array();

    foreach ($rSeries as $rSeriesID => $rSeriesO) {
        $rSeriesO['last_modified'] = $rSeriesO['last_modified_stream'];

        if (empty($rCategoryID) && in_array($rCategoryID, json_decode($rSeriesO['category_id'], true))) {
            if (in_array($rCategoryID, json_decode($rSeriesO['category_id'], true))) {
                $rSeriesO['category_id'] = $rCategoryID;
            } else {
                list($rSeriesO['category_id']) = json_decode($rSeriesO['category_id'], true);
            }

            if ((empty($rSearchBy) || stristr($rSeriesO['title'], $rSearchBy)) && !(!empty($rPicking['abc']) && $rPicking['abc'] != '*' && strtoupper(substr($rSeriesO['title'], 0, 1)) != $rPicking['abc']) && !(!empty($rPicking['genre']) && $rPicking['genre'] != '*' && $rSeriesO['category_id'] != $rPicking['genre']) && !(!empty($rPicking['years']) && $rPicking['years'] != '*' && $rSeriesO['year'] != $rPicking['years'])) {
                if (!empty($rFav)) {
                    $rFound = false;

                    if (!empty($rDevice['fav_channels'][$rType]) || in_array($rSeriesID, $rDevice['fav_channels'][$rType])) {
                        $rFound = true;
                    }

                    if (!$rFound) {
                        goto jamp1;
                    }
                }
                $rOutputSeries[$rSeriesID] = $rSeriesO;
                jamp1:
            }
        }
    }

    switch ($rOrderBy) {
        case 'name':
            uasort($rOutputSeries, 'sortArrayStreamName');

            break;

        case 'rating':
        case 'top':
            uasort($rOutputSeries, 'sortArrayStreamRating');

            break;

        case 'number':
            uasort($rOutputSeries, 'sortArrayStreamNumber');

            break;

        default:
            uasort($rOutputSeries, 'sortArrayStreamAdded');
    }

    return $rOutputSeries;
}

function convertTypes($rTypes) {
    $rReturn = array();
    $rTypeInt = array('live' => 1, 'movie' => 2, 'created_live' => 3, 'radio_streams' => 4, 'series' => 5);

    foreach ($rTypes as $rType) {
        $rReturn[] = $rTypeInt[$rType];
    }

    return $rReturn;
}

function getItems($rTypes = array(), $rCategoryID = null, $rFav = null, $rOrderBy = null, $rSearchBy = null, $rPicking = array(), $rStart = 0, $rLimit = 10, $additionalOptions = null) {
    global $rDevice;
    global $ipTV_db;
    $rAdded = false;
    $rChannels = array();

    foreach ($rTypes as $rType) {
        switch ($rType) {
            case 'live':
            case 'created_live':
                if (!$rAdded) {
                    $rChannels = array_merge($rChannels, $rDevice['live_ids']);
                    $rAdded = true;
                }
                break;

            case 'movie':
                $rChannels = array_merge($rChannels, $rDevice['vod_ids']);
                break;

            case 'radio_streams':
                $rChannels = array_merge($rChannels, $rDevice['radio_ids']);
                break;

            case 'series':
                $rChannels = array_merge($rChannels, $rDevice['episode_ids']);
                break;
        }
    }
    $rStreams = array('count' => 0, 'streams' => array());
    $rAdultCategories = ipTV_streaming::getAdultCategories();
    $rKey = $rStart + 1;
    $rWhereV = $rWhere = array();

    if (count($rTypes) > 0) {
        $rWhere[] = '`type` IN (' . implode(',', convertTypes($rTypes)) . ')';
    }

    if (!empty($rCategoryID)) {
        $rWhere[] = "JSON_CONTAINS(`category_id`, '%s', '\$')";
        $rWhereV[] = $rCategoryID;
    }

    if (!empty($rPicking['genre']) && $rPicking['genre'] != '*') {
        $rWhere[] = "JSON_CONTAINS(`category_id`, '%s', '\$')";
        $rWhereV[] = $rPicking['genre'];
    }

    $rChannels = ipTV_lib::sortChannels($rChannels);

    if (!empty($rFav)) {
        $favoriteChannelIds = array();
        foreach ($rTypes as $rType) {
            if (array_key_exists($rType, $rDevice['fav_channels'])) {
                foreach ($rDevice['fav_channels'][$rType] as $rStreamID) {
                    $favoriteChannelIds[] = intval($rStreamID);
                }
            }
        }
        $rChannels = array_intersect($favoriteChannelIds, $rChannels);
    }

    if (!empty($rSearchBy)) {
        $rWhere[] = '`stream_display_name` LIKE \'%s\'';
        $rWhereV[] = '%' . $rSearchBy . '%';
    }


    if (!empty($rPicking['abc']) && $rPicking['abc'] != '*') {
        $rWhere[] = 'UCASE(LEFT(`stream_display_name`, 1)) = \'%s\'';
        $rWhereV[] = strtoupper($rPicking['abc']);
    }


    $rWhere[] = '`id` IN (' . implode(',', $rChannels) . ')';

    $rWhereString = 'WHERE ' . implode(' AND ', $rWhere);

    switch ($rOrderBy) {
        case 'name':
            $rOrder = '`stream_display_name` ASC';

            break;

        case 'top':
        case 'rating':
            $rOrder = '`rating` DESC';

            break;

        case 'added':
            $rOrder = '`added` DESC';

            break;

        case 'number':
        default:
            if (ipTV_lib::$settings['channel_number_type'] != 'manual') {
                $rOrder = 'FIELD(id,' . implode(',', $rChannels) . ')';
            } else {
                $rOrder = '`order` ASC';
            }


            break;
    }
    if (count($rChannels) > 0) {
        if (!$additionalOptions) {
            $ipTV_db->query("SELECT COUNT(`id`) AS `count` FROM `streams` " . $rWhereString . ";", ...$rWhereV);
            $rStreams["count"] = $ipTV_db->get_row()["count"];
            if ($rLimit) {
                $A6d7047f2fda966c = "SELECT (SELECT `stream_info` FROM `streams_servers` WHERE `streams_servers`.`pid` IS NOT NULL AND `streams_servers`.`stream_id` = `streams`.`id` LIMIT 1) AS `stream_info`, `id`, `stream_display_name`, `movie_properties`, `target_container`, `added`, `category_id`, `channel_id`, `epg_id`, `tv_archive_duration`, `stream_icon`, `allow_record`, `type` FROM `streams` " . $rWhereString . " ORDER BY " . $rOrder . " LIMIT " . $rStart . ", " . $rLimit . ";";
            } else {
                $A6d7047f2fda966c = "SELECT (SELECT `stream_info` FROM `streams_servers` WHERE `streams_servers`.`pid` IS NOT NULL AND `streams_servers`.`stream_id` = `streams`.`id` LIMIT 1) AS `stream_info`, `id`, `stream_display_name`, `movie_properties`, `target_container`, `added`, `category_id`, `channel_id`, `epg_id`, `tv_archive_duration`, `stream_icon`, `allow_record`, `type` FROM `streams` " . $rWhereString . " ORDER BY " . $rOrder . ";";
            }
            $ipTV_db->query($A6d7047f2fda966c, ...$rWhereV);
            $rRows = $ipTV_db->get_rows();
        } else {
            $rWhereV[] = $additionalOptions;
            $ipTV_db->query("SELECT * FROM (SELECT @row_number:=@row_number+1 AS `pos`, `id` FROM `streams`, (SELECT @row_number:=0) AS `t` " . $rWhereString . " ORDER BY " . $rOrder . ") `ids` WHERE `ids`.`id` = '%s';", ...$rWhereV);
            return $ipTV_db->get_row()["pos"] ?: NULL;
        }
    } else {
        if ($additionalOptions) {
            return NULL;
        }
        $rRows = [];
    }
    foreach ($rRows as $rStream) {
        $rStream["snumber"] = $rKey;
        $rStream["number"] = $rStream["snumber"];

        if (in_array($rCategoryID, json_decode($rStream["category_id"], true))) {
            $rStream["category_id"] = $rCategoryID;
        } else {
            list($rStream["category_id"]) = json_decode($rStream["category_id"], true);
        }

        if (in_array($rStream["category_id"], $rAdultCategories)) {
            $rStream["is_adult"] = 1;
        } else {
            $rStream["is_adult"] = 0;
        }

        $rStream["now_playing"] = getEPG($rStream["id"], time(), time() + 86400)[0] ?: NULL;
        $rStream["stream_info"] = json_decode($rStream["stream_info"], true);
        $rStreams["streams"][$rStream["id"]] = $rStream;
        $rKey++;
    }
    return $rStreams;
}

function sortArrayStreamRating($a, $b) {
    if (!isset($a['rating'])) {
        if (isset($a['movie_properties']) && isset($b['movie_properties'])) {
            if (!is_array($a['movie_properties'])) {
                $a = json_decode($a['movie_properties'], true);
            } else {
                $a = $a['movie_properties'];
            }
            if (!is_array($b['movie_properties'])) {
                $b = json_decode($b['movie_properties'], true);
            } else {
                $b = $b['movie_properties'];
            }
        } else {
            return 0;
        }
    }

    if ($a['rating'] != $b['rating']) {
        return ($b['rating'] < $a['rating'] ? -1 : 1);
    }

    return 0;
}

function sortArrayStreamAdded($a, $b) {
    $rColumn = (isset($a['added']) ? 'added' : 'last_modified');

    if (!is_numeric($a[$rColumn])) {
        $a[$rColumn] = strtotime($a['added']);
    }

    if (!is_numeric($b[$rColumn])) {
        $b[$rColumn] = strtotime($b[$rColumn]);
    }

    if ($a[$rColumn] != $b[$rColumn]) {
        return ($b[$rColumn] < $a[$rColumn] ? -1 : 1);
    }

    return 0;
}

function getDevice($rID = null, $rMAC = null) {
    global $ipTV_db;
    global $rIP;
    $rDevice = ($rID && file_exists(STALKER_TMP_PATH . 'stalker_' . $rID) ? ($rDevice = unserialize(file_get_contents(STALKER_TMP_PATH . 'stalker_' . $rID))) : null);
    $rMAC = base64_encode(strtoupper(urldecode($rMAC)));

    if (!$rDevice && $rMAC || $rDevice && 600 < time() - $rDevice['generated']) {
        if ($rMAC) {
            $ipTV_db->query('SELECT * FROM `mag_devices` WHERE `mac` = \'%s\' LIMIT 1', $rMAC);
        } else {
            if ($rDevice) {
                $ipTV_db->query('SELECT * FROM `mag_devices` WHERE `mac` = \'%s\' LIMIT 1', $rDevice['get_profile_vars']['mac']);
            }
        }

        if ($ipTV_db->num_rows() > 0) {
            $rDevice = $ipTV_db->get_row();
            $rUserInfo = ipTV_streaming::getUserInfo($rDevice['user_id'], null, null, true, false, $rIP);
            $rDevice = array_merge($rDevice, $rUserInfo);
            // $rDevice['allowed_ips'] = json_decode($rDevice['allowed_ips'], true);
            $rDevice['fav_channels'] = (!empty($rDevice['fav_channels']) ? json_decode($rDevice['fav_channels'], true) : array());

            if (empty($rDevice['fav_channels']['live'])) {
                $rDevice['fav_channels']['live'] = array();
            }

            if (empty($rDevice['fav_channels']['movie'])) {
                $rDevice['fav_channels']['movie'] = array();
            }

            if (empty($rDevice['fav_channels']['radio_streams'])) {
                $rDevice['fav_channels']['radio_streams'] = array();
            }

            $rDevice['mag_player'] = trim($rDevice['mag_player']);
            unset($rDevice['channel_ids']);
            $rDevice['get_profile_vars'] = array('id' => $rDevice['mag_id'], 'name' => $rDevice['mag_id'], 'parent_password' => ($rDevice['parent_password'] ?: '0000'), 'bright' => ($rDevice['bright'] ?: '200'), 'contrast' => ($rDevice['contrast'] ?: '127'), 'saturation' => ($rDevice['saturation'] ?: '127'), 'video_out' => ($rDevice['video_out'] ?: ''), 'volume' => ($rDevice['volume'] ?: '70'), 'playback_buffer_bytes' => ($rDevice['playback_buffer_bytes'] ?: '0'), 'playback_buffer_size' => ($rDevice['playback_buffer_size'] ?: '0'), 'audio_out' => ($rDevice['audio_out'] ?: '1'), 'mac' => $rDevice['mac'], 'ip' => '127.0.0.1', 'ls' => ($rDevice['ls'] ?: ''), 'lang' => ($rDevice['lang'] ?: ''), 'locale' => ($rDevice['locale'] ?: 'en_GB.utf8'), 'city_id' => ($rDevice['city_id'] ?: '0'), 'hd' => ($rDevice['hd'] ?: '1'), 'main_notify' => ($rDevice['main_notify'] ?: '1'), 'fav_itv_on' => ($rDevice['fav_itv_on'] ?: '0'), 'now_playing_start' => ($rDevice['now_playing_start'] ? date('Y-m-d H:i:s', $rDevice['now_playing_start']) : date('Y-m-d H:i:s')), 'now_playing_type' => ($rDevice['now_playing_type'] ?: '1'), 'now_playing_content' => ($rDevice['now_playing_content'] ?: ''), 'time_last_play_tv' => ($rDevice['time_last_play_tv'] ? date('Y-m-d H:i:s', $rDevice['time_last_play_tv']) : '0000-00-00 00:00:00'), 'time_last_play_video' => ($rDevice['time_last_play_video'] ? date('Y-m-d H:i:s', $rDevice['time_last_play_video']) : '0000-00-00 00:00:00'), 'hd_content' => ($rDevice['hd_content'] ?: '0'), 'image_version' => $rDevice['image_version'], 'last_change_status' => ($rDevice['last_change_status'] ? date('Y-m-d H:i:s', $rDevice['last_change_status']) : '0000-00-00 00:00:00'), 'last_start' => ($rDevice['last_start'] ? date('Y-m-d H:i:s', $rDevice['last_start']) : date('Y-m-d H:i:s')), 'last_active' => ($rDevice['last_active'] ? date('Y-m-d H:i:s', $rDevice['last_active']) : date('Y-m-d H:i:s')), 'keep_alive' => ($rDevice['keep_alive'] ? date('Y-m-d H:i:s', $rDevice['keep_alive']) : date('Y-m-d H:i:s')), 'screensaver_delay' => ($rDevice['screensaver_delay'] ?: '10'), 'stb_type' => $rDevice['stb_type'], 'now_playing_link_id' => ($rDevice['now_playing_link_id'] ?: '0'), 'now_playing_streamer_id' => ($rDevice['now_playing_streamer_id'] ?: '0'), 'last_watchdog' => ($rDevice['last_watchdog'] ? date('Y-m-d H:i:s', $rDevice['last_watchdog']) : date('Y-m-d H:i:s')), 'created' => ($rDevice['created'] ? date('Y-m-d H:i:s', $rDevice['created']) : date('Y-m-d H:i:s')), 'plasma_saving' => ($rDevice['plasma_saving'] ?: '0'), 'ts_enabled' => ($rDevice['ts_enabled'] ?: '0'), 'ts_enable_icon' => ($rDevice['ts_enable_icon'] ?: '1'), 'ts_path' => ($rDevice['ts_path'] ?: ''), 'ts_max_length' => ($rDevice['ts_max_length'] ?: '3600'), 'ts_buffer_use' => ($rDevice['ts_buffer_use'] ?: 'cyclic'), 'ts_action_on_exit' => ($rDevice['ts_action_on_exit'] ?: 'no_save'), 'ts_delay' => ($rDevice['ts_delay'] ?: 'on_pause'), 'video_clock' => ($rDevice['video_clock'] == 'On' ? 'On' : 'Off'), 'hdmi_event_reaction' => ($rDevice['hdmi_event_reaction'] ?: 1), 'show_after_loading' => ($rDevice['show_after_loading'] ?: ''), 'play_in_preview_by_ok' => ($rDevice['play_in_preview_by_ok'] ?: null), 'hw_version' => $rDevice['hw_version'], 'units' => ($rDevice['units'] ?: 'metric'), 'last_itv_id' => ($rDevice['last_itv_id'] ?: 0), 'rtsp_type' => ($rDevice['rtsp_type'] ?: '4'), 'rtsp_flags' => ($rDevice['rtsp_flags'] ?: '0'), 'stb_lang' => ($rDevice['stb_lang'] ?: 'en'), 'display_menu_after_loading' => ($rDevice['display_menu_after_loading'] ?: ''), 'record_max_length' => ($rDevice['record_max_length'] ?: 180), 'play_in_preview_only_by_ok' => ($rDevice['play_in_preview_only_by_ok'] ?: false), 'tv_archive_continued' => ($rDevice['tv_archive_continued'] ?: ''), 'plasma_saving_timeout' => ($rDevice['plasma_saving_timeout'] ?: '600'));
            $rDevice['mac'] = base64_encode($rDevice['mac']);
            $rDevice['generated'] = time();
        }
    } else {
        if ($rDevice) {
            $rLiveIDs = $rVODIDs = $rRadioIDs = $rCategoryIDs = $rChannelIDs = $rSeriesIDs = array();

            foreach ($rDevice['bouquet'] as $rID) {
                if (isset(ipTV_lib::$Bouquets[$rID]['streams'])) {
                    $rChannelIDs = array_merge($rChannelIDs, ipTV_lib::$Bouquets[$rID]['streams']);
                }

                if (isset(ipTV_lib::$Bouquets[$rID]['series'])) {
                    $rSeriesIDs = array_merge($rSeriesIDs, ipTV_lib::$Bouquets[$rID]['series']);
                }

                if (isset(ipTV_lib::$Bouquets[$rID]['channels'])) {
                    $rLiveIDs = array_merge($rLiveIDs, ipTV_lib::$Bouquets[$rID]['channels']);
                }

                if (isset(ipTV_lib::$Bouquets[$rID]['movies'])) {
                    $rVODIDs = array_merge($rVODIDs, ipTV_lib::$Bouquets[$rID]['movies']);
                }

                if (isset(ipTV_lib::$Bouquets[$rID]['radios'])) {
                    $rRadioIDs = array_merge($rRadioIDs, ipTV_lib::$Bouquets[$rID]['radios']);
                }
            }
            $rDevice['channel_ids'] = array_map('intval', array_unique($rChannelIDs));
            $rDevice['series_ids'] = array_map('intval', array_unique($rSeriesIDs));
            $rDevice['vod_ids'] = array_map('intval', array_unique($rVODIDs));
            $rDevice['live_ids'] = array_map('intval', array_unique($rLiveIDs));
            $rDevice['radio_ids'] = array_map('intval', array_unique($rRadioIDs));
        }
    }

    return $rDevice;
}

function getEPG($rStreamID, $rStartDate = null, $rFinishDate = null, $rByID = false) {
    $rReturn = array();
    $rData = (file_exists(EPG_PATH . 'stream_' . $rStreamID) ? unserialize(file_get_contents(EPG_PATH . 'stream_' . $rStreamID)) : array());

    foreach ($rData as $rItem) {
        if (!$rStartDate && !($rStartDate > $rItem['end'] && $rItem['start'] > $rFinishDate)) {
            if ($rByID) {
                $rReturn[$rItem['id']] = $rItem;
            } else {
                $rReturn[] = $rItem;
            }
        }
    }

    return $rReturn;
}

function getEPGs($rStreamIDs, $rStartDate = null, $rFinishDate = null) {
    $rReturn = array();

    foreach ($rStreamIDs as $rStreamID) {
        $rReturn[$rStreamID] = getepg($rStreamID, $rStartDate, $rFinishDate);
    }

    return $rReturn;
}

function getProgramme($rStreamID, $rProgrammeID) {
    $rData = getepg($rStreamID, null, null, true);

    if (isset($rData[$rProgrammeID])) {
        return $rData[$rProgrammeID];
    }
}

function updateCache() {
    global $rDevice;
    file_put_contents(STALKER_TMP_PATH . 'stalker_' . $rDevice['mag_id'], serialize($rDevice));
}

function getMovies($rCategoryID = null, $rFav = null, $rOrderBy = null, $rSearchBy = null, $rPicking = array()) {
    global $rDevice;
    global $rPageItems;
    global $rForceProtocol;
    $rDefaultPage = false;
    $rPage = (!empty(ipTV_lib::$request['p']) ? ipTV_lib::$request['p'] : 0);

    if ($rPage == 0) {
        $rDefaultPage = true;
        $rPage = 1;
    }

    $rStart = ($rPage - 1) * $rPageItems;
    $rStreams = getitems(array('movie'), $rCategoryID, $rFav, $rOrderBy, $rSearchBy, $rPicking, $rStart, $rPageItems);
    $rDatas = array();

    foreach ($rStreams['streams'] as $rMovie) {
        $rProperties = (!is_array($rMovie['movie_properties']) ? json_decode($rMovie['movie_properties'], true) : $rMovie['movie_properties']);
        $rHD = intval(1200 < $rMovie['stream_info']['codecs']['video']['width']);
        $rPostData = array('type' => 'movie', 'stream_id' => $rMovie['id'], 'target_container' => $rMovie['target_container']);
        $rThisMM = date('m');
        $rThisDD = date('d');
        $rThisYY = date('Y');

        if (mktime(0, 0, 0, $rThisMM, $rThisDD, $rThisYY) < $rMovie['added']) {
            $rAddedKey = 'today';
            $rAddedVal = 'Today';
        } else {
            if (mktime(0, 0, 0, $rThisMM, $rThisDD - 1, $rThisYY) < $rMovie['added']) {
                $rAddedKey = 'yesterday';
                $rAddedVal = 'Yesterday';
            } else {
                if (0 < $rMovie['added']) {
                    $rAddedKey = 'week_and_more';
                    $rDay = date('d', $rMovie['added']);

                    if (11 <= $rDay % 100 && $rDay % 100 <= 13) {
                        $rAbb = $rDay . 'th';
                    } else {
                        $rAbb = $rDay . array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th')[$rDay % 10];
                    }

                    $rAddedVal = date('M', $rMovie['added']) . ' ' . $rAbb . ' ' . date('Y', $rMovie['added']);
                } else {
                    $rAddedKey = 'week_and_more';
                    $rAddedVal = 'Unknown';
                }
            }
        }

        $rDuration = (isset($rProperties['duration_secs']) ? $rProperties['duration_secs'] : 60);
        $rDatas[] = array('id' => $rMovie['id'], 'owner' => '', 'name' => $rMovie['stream_display_name'], 'tmdb_id' => $rProperties['tmdb_id'], 'old_name' => '', 'o_name' => $rMovie['stream_display_name'], 'fname' => '', 'description' => (empty($rProperties['plot']) ? 'N/A' : $rProperties['plot']), 'pic' => '', 'cost' => 0, 'time' => intval($rDuration / 60), 'file' => '', 'path' => str_replace(' ', '_', $rMovie['stream_display_name']), 'protocol' => '', 'rtsp_url' => '', 'censored' => intval($rMovie['is_adult']), 'series' => array(), 'volume_correction' => 0, 'category_id' => $rMovie['category_id'], 'genre_id' => 0, 'genre_id_1' => 0, 'genre_id_2' => 0, 'genre_id_3' => 0, 'hd' => $rHD, 'genre_id_4' => 0, 'cat_genre_id_1' => $rMovie['category_id'], 'cat_genre_id_2' => 0, 'cat_genre_id_3' => 0, 'cat_genre_id_4' => 0, 'director' => (empty($rProperties['director']) ? 'N/A' : $rProperties['director']), 'actors' => (empty($rProperties['cast']) ? 'N/A' : $rProperties['cast']), 'year' => $rProperties['year'], 'accessed' => 1, 'status' => 1, 'disable_for_hd_devices' => 0, 'added' => date('Y-m-d H:i:s', $rMovie['added']), 'count' => 0, 'count_first_0_5' => 0, 'count_second_0_5' => 0, 'vote_sound_good' => 0, 'vote_sound_bad' => 0, 'vote_video_good' => 0, 'vote_video_bad' => 0, 'rate' => '', 'last_rate_update' => '', 'last_played' => '', 'for_sd_stb' => 0, 'rating_im' => (empty($rProperties['rating']) ? 'N/A' : $rProperties['rating']), 'rating_count_im' => '', 'rating_last_update' => '0000-00-00 00:00:00', 'age' => '12+', 'high_quality' => 0, 'rating_kinopoisk' => (empty($rProperties['rating']) ? 'N/A' : $rProperties['rating']), 'comments' => '', 'low_quality' => 0, 'is_series' => 0, 'year_end' => 0, 'autocomplete_provider' => 'im', 'screenshots' => '', 'is_movie' => 1, 'lock' => $rMovie['is_adult'], 'fav' => (in_array($rMovie['id'], $rDevice['fav_channels']['movie']) ? 1 : 0), 'for_rent' => 0, 'screenshot_uri' => (empty($rProperties['movie_image']) ? '' : $rProperties['movie_image']), 'genres_str' => (empty($rProperties['genre']) ? 'N/A' : $rProperties['genre']), 'cmd' => base64_encode(json_encode($rPostData, JSON_PARTIAL_OUTPUT_ON_ERROR)), $rAddedKey => $rAddedVal, 'has_files' => 0);
    }

    if (!$rDefaultPage) {
        $rPage = 0;
    }

    $rOutput = array('js' => array('total_items' => intval($rStreams['count']), 'max_page_items' => $rPageItems, 'selected_item' => 0, 'cur_page' => $rPage, 'data' => $rDatas));

    return json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR);
}

function getSeasons($rSeriesID) {
    global $ipTV_db;
    $ipTV_db->query('SELECT * FROM `streams_episodes` t1 INNER JOIN `streams` t2 ON t2.id=t1.stream_id WHERE t1.series_id = \'%s\' ORDER BY t1.season_num DESC, t1.episode_num ASC', $rSeriesID);

    return $ipTV_db->get_rows(true, 'season_num', false);
}

function getSeries($rMovieID = null, $rCategoryID = null, $rFav = null, $rOrderBy = null, $rSearchBy = null, $rPicking = array()) {
    global $rDevice;
    global $ipTV_db;
    global $rPageItems;
    global $rForceProtocol;
    $rPage = (!empty(ipTV_lib::$request['p']) ? ipTV_lib::$request['p'] : 0);
    $rDefaultPage = false;

    if (empty($rMovieID)) {
        $rItems = getseriesitems($rDevice['user_id'], 'series', $rCategoryID, $rFav, $rOrderBy, $rSearchBy, $rPicking);
    } else {
        $rItems = getSeasons($rMovieID);
        $ipTV_db->query('SELECT * FROM `streams_series` WHERE `id` = \'%s\'', $rMovieID);
        $rSeriesInfo = $ipTV_db->get_row();
    }

    $rCounter = count($rItems);
    $rChannelIDx = 0;

    if ($rPage == 0) {
        $rDefaultPage = true;
        $rPage = ceil($rChannelIDx / $rPageItems);

        if ($rPage == 0) {
            $rPage = 1;
        }
    }

    $rItems = array_slice($rItems, ($rPage - 1) * $rPageItems, $rPageItems, true);
    $rDatas = array();

    foreach ($rItems as $rKey => $rMovie) {
        if (!is_null($rFav) || $rFav == 1) {
            if (!in_array($rMovie['id'], $rDevice['fav_channels']['series'])) {
                $rCounter--;
            }
        }

        if (!empty($rSeriesInfo)) {
            $rProperties = $rSeriesInfo;
            $rMaxAdded = 0;

            foreach ($rMovie as $vod) {
                if ($rMaxAdded < $vod['added']) {
                    $rMaxAdded = $vod['added'];
                }
            }
        } else {
            $rProperties = $rMovie;
            $rMaxAdded = $rMovie['last_modified'];
        }

        $rPostData = array('series_id' => $rMovieID, 'season_num' => $rKey, 'type' => 'series');
        $rThisMM = date('m');
        $rThisDD = date('d');
        $rThisYY = date('Y');

        if (mktime(0, 0, 0, $rThisMM, $rThisDD, $rThisYY) < $rMaxAdded) {
            $rAddedKey = 'today';
            $rAddedVal = 'Today';
        } else {
            if (mktime(0, 0, 0, $rThisMM, $rThisDD - 1, $rThisYY) < $rMaxAdded) {
                $rAddedKey = 'yesterday';
                $rAddedVal = 'Yesterday';
            } else {
                if (mktime(0, 0, 0, $rThisMM, $rThisDD - 7, $rThisYY) < $rMaxAdded) {
                    $rAddedKey = 'week_and_more';
                    $rAddedVal = 'Last Week';
                } else {
                    $rAddedKey = 'week_and_more';

                    if (0 < $rMaxAdded) {
                        $rAddedVal = date('F', $rMaxAdded) . ' ' . date('Y', $rMaxAdded);
                    } else {
                        $rAddedVal = 'Unknown';
                    }
                }
            }
        }

        if (!empty($rSeriesInfo)) {
            if ($rKey == 0) {
                $rTitle = 'Specials';
            } else {
                $rTitle = 'Season ' . $rKey;
            }
        } else {
            $rTitle = $rMovie['title'];
        }

        $rDatas[] = array('id' => $rProperties['id'], 'owner' => '', 'name' => $rTitle, 'tmdb_id' => $rProperties['tmdb_id'], 'old_name' => '', 'o_name' => $rTitle, 'fname' => '', 'description' => (empty($rProperties['plot']) ? 'N/A' : $rProperties['plot']), 'pic' => '', 'cost' => 0, 'time' => 'N/a', 'file' => '', 'path' => str_replace(' ', '_', $rProperties['title']), 'protocol' => '', 'rtsp_url' => '', 'censored' => 0, 'series' => (!empty($rSeriesInfo) ? range(1, count($rMovie)) : array()), 'volume_correction' => 0, 'category_id' => $rProperties['category_id'], 'genre_id' => 0, 'genre_id_1' => 0, 'genre_id_2' => 0, 'genre_id_3' => 0, 'hd' => 1, 'genre_id_4' => 0, 'cat_genre_id_1' => $rProperties['category_id'], 'cat_genre_id_2' => 0, 'cat_genre_id_3' => 0, 'cat_genre_id_4' => 0, 'director' => (empty($rProperties['director']) ? 'N/A' : $rProperties['director']), 'actors' => (empty($rProperties['cast']) ? 'N/A' : $rProperties['cast']), 'year' => (empty($rProperties['release_date']) ? 'N/A' : $rProperties['release_date']), 'accessed' => 1, 'status' => 1, 'disable_for_hd_devices' => 0, 'added' => date('Y-m-d H:i:s', $rMaxAdded), 'count' => 0, 'count_first_0_5' => 0, 'count_second_0_5' => 0, 'vote_sound_good' => 0, 'vote_sound_bad' => 0, 'vote_video_good' => 0, 'vote_video_bad' => 0, 'rate' => '', 'last_rate_update' => '', 'last_played' => '', 'for_sd_stb' => 0, 'rating_im' => (empty($rProperties['rating']) ? 'N/A' : $rProperties['rating']), 'rating_count_im' => '', 'rating_last_update' => '0000-00-00 00:00:00', 'age' => '12+', 'high_quality' => 0, 'rating_kinopoisk' => (empty($rProperties['rating']) ? 'N/A' : $rProperties['rating']), 'comments' => '', 'low_quality' => 0, 'is_series' => 1, 'year_end' => 0, 'autocomplete_provider' => 'im', 'screenshots' => '', 'is_movie' => 1, 'lock' => 0, 'fav' => (in_array($rProperties['id'], $rDevice['fav_channels']['series']) ? 1 : 0), 'for_rent' => 0, 'screenshot_uri' => (empty($rProperties['cover']) ? '' : $rProperties['cover']), 'genres_str' => (empty($rProperties['genre']) ? 'N/A' : $rProperties['genre']), 'cmd' => (!empty($rSeriesInfo) ? base64_encode(json_encode($rPostData, JSON_PARTIAL_OUTPUT_ON_ERROR)) : ''), $rAddedKey => $rAddedVal, 'has_files' => (empty($rMovieID) ? 1 : 0));
    }

    if ($rDefaultPage) {
        $rCurrentPage = $rPage;
        $rSelectedItem = $rChannelIDx - ($rPage - 1) * $rPageItems;
    } else {
        $rCurrentPage = 0;
        $rSelectedItem = 0;
    }

    $rOutput = array('js' => array('total_items' => $rCounter, 'max_page_items' => $rPageItems, 'selected_item' => $rSelectedItem, 'cur_page' => $rCurrentPage, 'data' => $rDatas));

    return json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR);
}

function sortArrayStreamNumber($a, $b) {
    if ($a['number'] != $b['number']) {
        return ($a['number'] < $b['number'] ? -1 : 1);
    }

    return 0;
}

function sortArrayStreamName($a, $b) {
    $rColumn = (isset($a['stream_display_name']) ? 'stream_display_name' : 'title');

    return strcmp($a[$rColumn], $b[$rColumn]);
}

function getStations($rCategoryID = null, $rFav = null, $rOrderBy = null) {
    global $rDevice;
    global $rPlayer;
    global $rPageItems;
    $rDefaultPage = false;
    $rPage = (!empty(ipTV_lib::$request['p']) ? ipTV_lib::$request['p'] : 0);

    if ($rPage == 0) {
        $rDefaultPage = true;
        $rPage = 1;
    }

    $rStart = ($rPage - 1) * $rPageItems;
    $rStreams = getitems(array('radio_streams'), $rCategoryID, $rFav, $rOrderBy, null, null, $rStart, $rPageItems);
    $rDatas = array();
    $i = 0;
    foreach ($rStreams['streams'] as $rStream) {
        if (ipTV_lib::$settings['mag_security'] == 0) {
            $rEncData = 'ministra::live/' . $rDevice['username'] . '/' . $rDevice['password'] . '/' . $rStream['id'] . '/' . ipTV_lib::$settings['mag_container'] . '/' . $rDevice['token'];
            $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
            $rStreamURL = ((ipTV_lib::$settings['mag_disable_ssl'] ? ipTV_lib::$StreamingServers[SERVER_ID]['http_url'] : ipTV_lib::$StreamingServers[SERVER_ID]['site_url'])) . 'play/' . $rToken;

            if (ipTV_lib::$settings['mag_keep_extension']) {
                $rStreamURL .= '?ext=.' . ipTV_lib::$settings['mag_container'];
            }

            $rStreamSourceSt = 0;
        } else {
            $rStreamURL = 'http://localhost/ch/' . $rStream['id'] . '_';
            $rStreamSourceSt = 1;
        }

        $rDatas[] = array('id' => $rStream['id'], 'name' => $rStream['stream_display_name'], 'number' => $i++, 'cmd' => $rPlayer . $rStreamURL, 'count' => 0, 'open' => 1, 'status' => 1, 'volume_correction' => 0, 'use_http_tmp_link' => (string) $rStreamSourceSt, 'fav' => (in_array($rStream['id'], $rDevice['fav_channels']['radio_streams']) ? 1 : 0));
    }
    $i = 0;

    if (!$rDefaultPage) {
        $rPage = 0;
    }

    $rOutput = array('js' => array('total_items' => intval($rStreams['count']), 'max_page_items' => $rPageItems, 'selected_item' => 0, 'cur_page' => $rPage, 'data' => $rDatas));

    return json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR);
}

function getStreams($rCategoryID = null, $rAll = false, $rFav = null, $rOrderBy = null, $rSearchBy = null) {

    global $rDevice;
    global $rPlayer;
    global $rPageItems;
    global $rTimezone;
    global $rForceProtocol;
    $rDefaultPage = false;
    $rPage = (isset(ipTV_lib::$request['p']) ? intval(ipTV_lib::$request['p']) : 0);

    if ($rPage == 0 && $rCategoryID != -1) {
        $rDefaultPage = true;

        if (ipTV_lib::$request['p'] == 0 || !empty($rDevice['last_itv_id'])) {
            $rPosition = getitems(array('live', 'created_live'), $rCategoryID, $rFav, $rOrderBy, $rSearchBy, null, 0, 0, $rDevice['last_itv_id']);
            if (!is_array($rPosition)) {
                $rPage = floor(($rPosition - 1) / $rPageItems) + 1;
                $rPosition = $rPosition - ($rPage - 1) * $rPageItems;
            } else {
                $rPosition = 0;
            }
        }

        if ($rPage == 0) {
            $rPage = 1;
        }
    }

    $rStart = ($rPage - 1) * $rPageItems;

    if ($rCategoryID == -1) {
        $rStreams = getitems(array('live', 'created_live'), (0 < $rCategoryID ? $rCategoryID : null), $rFav, $rOrderBy, $rSearchBy, null, 0, 0);
    } else {
        if ($rAll) {
            $rStreams = getitems(array('live', 'created_live'), $rCategoryID, $rFav, $rOrderBy, $rSearchBy, null, 0, 0);
        } else {
            $rStreams = getitems(array('live', 'created_live'), $rCategoryID, $rFav, $rOrderBy, $rSearchBy, null, $rStart, $rPageItems);
        }
    }

    $rDatas = array();
    $rTimeDifference = ipTV_lib::getDiffTimezone($rTimezone);

    foreach ($rStreams['streams'] as $rStream) {
        $rHD = intval(1200 < $rStream['stream_info']['codecs']['video']['width']);

        if (ipTV_lib::$settings['mag_security'] == 0) {
            $rEncData = 'ministra::live/' . $rDevice['username'] . '/' . $rDevice['password'] . '/' . $rStream['id'] . '/' . ipTV_lib::$settings['mag_container'] . '/' . $rDevice['token'];
            $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
            $rStreamURL = ((ipTV_lib::$settings['mag_disable_ssl'] ? ipTV_lib::$StreamingServers[SERVER_ID]['http_url'] : ipTV_lib::$StreamingServers[SERVER_ID]['site_url'])) . 'play/' . $rToken;

            if (ipTV_lib::$settings['mag_keep_extension']) {
                $rStreamURL .= '?ext=.' . ipTV_lib::$settings['mag_container'];
            }

            $rStreamSourceSt = 0;
        } else {
            $rStreamURL = 'http://localhost/ch/' . $rStream['id'] . '_';
            $rStreamSourceSt = 1;
        }

        if ($rStream['now_playing']) {
            $rStartTime = new DateTime();
            $rStartTime->setTimestamp($rStream['now_playing']['start']);
            $rStartTime->modify((string) $rTimeDifference . ' seconds');
            $rEndTime = new DateTime();
            $rEndTime->setTimestamp($rStream['now_playing']['end']);
            $rEndTime->modify((string) $rTimeDifference . ' seconds');
            $rNowPlaying = $rStartTime->format('H:i') . ' - ' . $rEndTime->format('H:i') . ': ' . $rStream['now_playing']['title'];
        } else {
            $rNowPlaying = 'No channel information is available...';
        }

        $rDatas[] = array('id' => intval($rStream['id']), 'name' => $rStream['stream_display_name'], 'number' => (string) $rStream['number'], 'snumber' => (string) $rStream['number'], 'censored' => ($rStream['is_adult'] == 1 ? 1 : 0), 'cmd' => $rPlayer . $rStreamURL, 'cost' => '0', 'count' => '0', 'status' => 1, 'tv_genre_id' => $rStream['category_id'], 'base_ch' => '1', 'hd' => $rHD, 'xmltv_id' => (!empty($rStream['channel_id']) ? $rStream['channel_id'] : ''), 'service_id' => '', 'bonus_ch' => '0', 'volume_correction' => '0', 'use_http_tmp_link' => $rStreamSourceSt, 'mc_cmd' => '', 'enable_tv_archive' => (0 < $rStream['tv_archive_duration'] ? 1 : 0), 'wowza_tmp_link' => '0', 'wowza_dvr' => '0', 'monitoring_status' => '1', 'enable_monitoring' => '0', 'enable_wowza_load_balancing' => '0', 'cmd_1' => '', 'cmd_2' => '', 'cmd_3' => '', 'logo' => $rStream['stream_icon'], 'correct_time' => '0', 'nimble_dvr' => '0', 'allow_pvr' => (int) $rStream['allow_record'], 'allow_local_pvr' => (int) $rStream['allow_record'], 'allow_remote_pvr' => 0, 'modified' => '', 'allow_local_timeshift' => '1', 'nginx_secure_link' => $rStreamSourceSt, 'tv_archive_duration' => (0 < $rStream['tv_archive_duration'] ? $rStream['tv_archive_duration'] * 24 : 0), 'locked' => 0, 'lock' => $rStream['is_adult'], 'fav' => (in_array($rStream['id'], $rDevice['fav_channels']['live']) ? 1 : 0), 'archive' => (0 < $rStream['tv_archive_duration'] ? 1 : 0), 'genres_str' => '', 'cur_playing' => $rNowPlaying, 'epg' => array(), 'open' => 1, 'cmds' => array(array('id' => (string) $rStream['id'], 'ch_id' => (string) $rStream['id'], 'priority' => '0', 'url' => $rPlayer . $rStreamURL, 'status' => '1', 'use_http_tmp_link' => $rStreamSourceSt, 'wowza_tmp_link' => '0', 'user_agent_filter' => '', 'use_load_balancing' => '0', 'changed' => '', 'enable_monitoring' => '0', 'enable_balancer_monitoring' => '0', 'nginx_secure_link' => $rStreamSourceSt, 'flussonic_tmp_link' => '0')), 'use_load_balancing' => 0, 'pvr' => (int) $rStream['allow_record']);
    }

    if (!$rDefaultPage) {
        $rPage = 0;
        $rPosition = 0;
    }

    $rOutput = array('js' => array('total_items' => intval($rStreams['count']), 'max_page_items' => intval($rPageItems), 'selected_item' => $rPosition, 'cur_page' => ($rAll ? 0 : $rPage), 'data' => $rDatas));
    return json_encode($rOutput, JSON_PARTIAL_OUTPUT_ON_ERROR);
}

function getHeaders() {
    $rHeaders = array();

    foreach ($_SERVER as $rName => $rValue) {
        if (substr($rName, 0, 5) == 'HTTP_') {
            $rHeaders[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($rName, 5)))))] = $rValue;
        }
    }

    return $rHeaders;
}

function shutdown() {
    global $ipTV_db;

    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
