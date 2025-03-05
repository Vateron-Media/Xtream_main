<?php

require 'init.php';
register_shutdown_function('shutdown');
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$rDeny = true;
$rDownloading = false;
$rIP = ipTV_streaming::getUserIP();
$rCountryCode = ipTV_streaming::getIPInfo($userIP)['country']['iso_code'];
$rUserAgent = (empty($_SERVER['HTTP_USER_AGENT']) ? '' : htmlentities(trim($_SERVER['HTTP_USER_AGENT'])));
$rDeviceKey = (empty(CoreUtilities::$request['type']) ? 'm3u_plus' : CoreUtilities::$request['type']);
$rTypeKey = (empty(CoreUtilities::$request['key']) ? null : explode(',', CoreUtilities::$request['key']));
$rOutputKey = (empty(CoreUtilities::$request['output']) ? '' : CoreUtilities::$request['output']);
$rNoCache = !empty(CoreUtilities::$request['nocache']);
if (isset(CoreUtilities::$request['username']) && isset(CoreUtilities::$request['password'])) {
    $rUsername = CoreUtilities::$request['username'];
    $rPassword = CoreUtilities::$request['password'];

    if (empty($rUsername) || empty($rPassword)) {
        generateError('NO_CREDENTIALS');
    }

    $rUserInfo = ipTV_streaming::getUserInfo(null, $rUsername, $rPassword, true, false, $rIP);
} else {
    if (isset(CoreUtilities::$request['token'])) {
        $rToken = CoreUtilities::$request['token'];

        if (empty($rToken)) {
            generateError('NO_CREDENTIALS');
        }

        $rUserInfo = ipTV_streaming::getUserInfo(null, $rToken, null, true, false, $rIP);
    } else {
        generateError('NO_CREDENTIALS');
    }
}

ini_set('memory_limit', -1);

if ($rUserInfo) {
    $rDeny = false;

    // if ($rUserInfo['bypass_ua'] == 0) {
    //     if (ipTV_streaming::checkBlockedUAs($rUserAgent)) {
    //         generateError('BLOCKED_USER_AGENT');
    //     }
    // }

    if (is_null($rUserInfo['exp_date']) || $rUserInfo['exp_date'] > time()) {
    } else {
        generateError('EXPIRED');
    }

    if ($rUserInfo['is_mag'] || $rUserInfo['is_e2']) {
        generateError('DEVICE_NOT_ALLOWED');
    }

    if (!$rUserInfo['admin_enabled']) {
        generateError('BANNED');
    }

    if (!$rUserInfo['enabled']) {
        generateError('DISABLED');
    }

    if (empty($rUserAgent) && CoreUtilities::$settings['disallow_empty_user_agents'] == 1) {
        generateError('EMPTY_USER_AGENT');
    }

    if (empty($rUserInfo['allowed_ips']) || in_array($rIP, array_map('gethostbyname', $rUserInfo['allowed_ips']))) {
    } else {
        generateError('NOT_IN_ALLOWED_IPS');
    }

    if (!empty($rCountryCode)) {
        $rForceCountry = !empty($rUserInfo['forced_country']);

        if ($rForceCountry && $rUserInfo['forced_country'] != 'ALL' && $rCountryCode != $rUserInfo['forced_country']) {
            generateError('FORCED_COUNTRY_INVALID');
        }

        if ($rForceCountry || in_array('ALL', CoreUtilities::$settings['allow_countries']) || in_array($rCountryCode, CoreUtilities::$settings['allow_countries'])) {
        } else {
            generateError('NOT_IN_ALLOWED_COUNTRY');
        }
    }

    if (empty($rUserInfo['allowed_ua']) || in_array($rUserAgent, $rUserInfo['allowed_ua'])) {
    } else {
        generateError('NOT_IN_ALLOWED_UAS');
    }

    if ($rUserInfo['isp_violate'] == 1) {
        generateError('ISP_BLOCKED');
    }

    if ($rUserInfo['isp_is_server'] == 1 && !$rUserInfo['is_restreamer']) {
        generateError('ASN_BLOCKED');
    }


    $rDownloading = true;

    if (startDownload('playlist', $rUserInfo, getmypid())) {
        if (!generatePlaylist($rUserInfo, $rDeviceKey, $rOutputKey, $rTypeKey, $rNoCache)) {
            generateError('GENERATE_PLAYLIST_FAILED');
        }
    } else {
        generateError('DOWNLOAD_LIMIT_REACHED', false);
        http_response_code(429);
        exit();
    }
} else {
    checkBruteforce(null, null, $rUsername);
    generateError('INVALID_CREDENTIALS');
}

function shutdown() {
    global $ipTV_db;
    global $rDeny;
    global $rDownloading;
    global $rUserInfo;

    if ($rDeny) {
        checkFlood();
    }

    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }

    if ($rDownloading) {
        stopDownload('playlist', $rUserInfo, getmypid());
    }
}
