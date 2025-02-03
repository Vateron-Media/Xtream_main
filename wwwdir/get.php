<?php

require 'init.php';
register_shutdown_function('shutdown');
set_time_limit(0);
header('Access-Control-Allow-Origin: *');

$rDeny = true;
$rDownloading = false;
$rIP = ipTV_streaming::getUserIP();
$rUserAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? htmlentities(trim($_SERVER['HTTP_USER_AGENT'])) : '';
$rDeviceKey = ipTV_lib::$request['type'] ?? 'm3u_plus';
$rTypeKey = !empty(ipTV_lib::$request['key']) ? explode(',', ipTV_lib::$request['key']) : null;
$rOutputKey = ipTV_lib::$request['output'] ?? '';
$rNoCache = !empty(ipTV_lib::$request['nocache']);

$rCountryCode = ipTV_streaming::getIPInfo($rIP)['country']['iso_code'] ?? null;

// Check for valid user credentials
if (!empty(ipTV_lib::$request['username']) && !empty(ipTV_lib::$request['password'])) {
    $rUsername = ipTV_lib::$request['username'];
    $rPassword = ipTV_lib::$request['password'];
    $rUserInfo = ipTV_streaming::getUserInfo(null, $rUsername, $rPassword, true, false, $rIP);
} elseif (!empty(ipTV_lib::$request['token'])) {
    $rToken = ipTV_lib::$request['token'];
    $rUserInfo = ipTV_streaming::getUserInfo(null, $rToken, null, true, false, $rIP);
} else {
    generateError('NO_CREDENTIALS');
}

ini_set('memory_limit', -1);

if (!$rUserInfo) {
    checkBruteforce(null, null, $rUsername);
    generateError('INVALID_CREDENTIALS');
}

$rDeny = false;

// **User validation checks**
if ($rUserInfo['exp_date'] !== null && $rUserInfo['exp_date'] <= time()) {
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

if (empty($rUserAgent) && ipTV_lib::$settings['disallow_empty_user_agents']) {
    generateError('EMPTY_USER_AGENT');
}

if (!empty($rUserInfo['allowed_ips']) && !in_array($rIP, array_map('gethostbyname', $rUserInfo['allowed_ips']))) {
    generateError('NOT_IN_ALLOWED_IPS');
}

// **Country validation**
$rForceCountry = !empty($rUserInfo['forced_country']);

if (
    $rForceCountry && 
    $rUserInfo['forced_country'] !== 'ALL' && 
    $rCountryCode !== $rUserInfo['forced_country']
) {
    generateError('FORCED_COUNTRY_INVALID');
}

if (
    !$rForceCountry && 
    !in_array('ALL', ipTV_lib::$settings['allow_countries']) &&
    !in_array($rCountryCode, ipTV_lib::$settings['allow_countries'])
) {
    generateError('NOT_IN_ALLOWED_COUNTRY');
}

// **User Agent Validation**
if (!empty($rUserInfo['allowed_ua']) && !in_array($rUserAgent, $rUserInfo['allowed_ua'])) {
    generateError('NOT_IN_ALLOWED_UAS');
}

if ($rUserInfo['isp_violate']) {
    generateError('ISP_BLOCKED');
}

if ($rUserInfo['isp_is_server'] && !$rUserInfo['is_restreamer']) {
    generateError('ASN_BLOCKED');
}

// **Proceed with playlist generation**
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

// **Shutdown function**
function shutdown() {
    global $ipTV_db, $rDeny, $rDownloading, $rUserInfo;

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