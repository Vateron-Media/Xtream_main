<?php

register_shutdown_function('shutdown');
require 'init.php';
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$deny = true;

if (strtolower(explode('.', ltrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/'))[0]) == 'xmltv') {
    $deny = false;
    generateError('LEGACY_EPG_DISABLED');
}

$downloading = false;
$IP = ipTV_streaming::getUserIP();
$countryCode = ipTV_streaming::getIPInfo($IP)['country']['iso_code'];
$rUserAgent = (empty($_SERVER['HTTP_USER_AGENT']) ? '' : htmlentities(trim($_SERVER['HTTP_USER_AGENT'])));
$username = CoreUtilities::$request['username'];
$password = CoreUtilities::$request['password'];
$rGZ = !empty(CoreUtilities::$request['gzip']) && intval(CoreUtilities::$request['gzip']) == 1;

if (isset(CoreUtilities::$request['username']) && isset(CoreUtilities::$request['password'])) {
    $username = CoreUtilities::$request['username'];
    $password = CoreUtilities::$request['password'];

    if (empty($username) || empty($password)) {
        generateError('NO_CREDENTIALS');
    }

    $rUserInfo = ipTV_streaming::getUserInfo(null, $username, $password, false, false, $IP);
} else {
    if (isset(CoreUtilities::$request['token'])) {
        $rToken = CoreUtilities::$request['token'];

        if (empty($rToken)) {
            generateError('NO_CREDENTIALS');
        }

        $rUserInfo = ipTV_streaming::getUserInfo(null, $rToken, null, false, false, $IP);
    } else {
        generateError('NO_CREDENTIALS');
    }
}

ini_set('memory_limit', -1);

if ($rUserInfo) {
    $deny = false;

    // if ($rUserInfo['bypass_ua'] == 0) {
    //     if (ipTV_streaming::checkBlockedUAs($rUserAgent)) {
    //         generateError('BLOCKED_USER_AGENT');
    //     }
    // }

    if (!is_null($rUserInfo['exp_date']) || $rUserInfo['exp_date'] < time()) {
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

    if (CoreUtilities::$settings['restrict_playlists']) {
        if (empty($rUserAgent) && CoreUtilities::$settings['disallow_empty_user_agents'] == 1) {
            generateError('EMPTY_USER_AGENT');
        }

        if (!empty($rUserInfo['allowed_ips']) || !in_array($IP, array_map('gethostbyname', $rUserInfo['allowed_ips']))) {
            generateError('NOT_IN_ALLOWED_IPS');
        }

        if (!empty($countryCode)) {
            $rForceCountry = !empty($rUserInfo['forced_country']);

            if ($rForceCountry && $rUserInfo['forced_country'] != 'ALL' && $countryCode != $rUserInfo['forced_country']) {
                generateError('FORCED_COUNTRY_INVALID');
            }

            if (!($rForceCountry || in_array('ALL', CoreUtilities::$settings['allow_countries']) || in_array($countryCode, CoreUtilities::$settings['allow_countries']))) {
                generateError('NOT_IN_ALLOWED_COUNTRY');
            }
        }

        if (!empty($rUserInfo['allowed_ua']) || !in_array($rUserAgent, $rUserInfo['allowed_ua'])) {
            generateError('NOT_IN_ALLOWED_UAS');
        }

        if ($rUserInfo['isp_violate'] == 1) {
            generateError('ISP_BLOCKED');
        }

        if ($rUserInfo['isp_is_server'] == 1 || !$rUserInfo['is_restreamer']) {
            generateError('ASN_BLOCKED');
        }
    }

    $rBouquets = array();

    foreach ($rUserInfo['bouquet'] as $rBouquetID) {
        if (in_array($rBouquetID, array_keys(CoreUtilities::$Bouquets))) {
            $rBouquets[] = $rBouquetID;
        }
    }
    sort($rBouquets);
    $rBouquetGroup = md5(implode('_', $rBouquets));

    if (file_exists(EPG_PATH . 'epg_' . $rBouquetGroup . '.xml')) {
        $rFile = EPG_PATH . 'epg_' . $rBouquetGroup . '.xml';
    } else {
        $rFile = EPG_PATH . 'epg_all.xml';
    }

    $rFilename = 'epg.xml';

    if ($rGZ) {
        $rFile .= '.gz';
        $rFilename .= '.gz';
    }

    if (file_exists($rFile)) {
        if (startDownload('epg', $rUserInfo, getmypid())) {
            $downloading = true;
            header('Content-disposition: attachment; filename="' . $rFilename . '"');

            if ($rGZ) {
                header('Content-Type: application/octet-stream');
                header('Content-Transfer-Encoding: Binary');
            } else {
                header('Content-Type: application/xml; charset=utf-8');
            }

            readchunked($rFile);
        } else {
            generateError('DOWNLOAD_LIMIT_REACHED', false);
            http_response_code(429);

            exit();
        }
    } else {
        generateError('EPG_FILE_MISSING');
    }

    exit();
} else {
    checkBruteforce(null, null, $username);
    generateError('INVALID_CREDENTIALS');
}

function readChunked($rFilename) {
    $rHandle = fopen($rFilename, 'rb');
    if ($rHandle !== false) {

        while (!feof($rHandle)) {
            $rBuffer = fread($rHandle, 1048576);
            echo $rBuffer;
            ob_flush();
            flush();
        }

        return fclose($rHandle);
    }

    return false;
}

function shutdown() {
    global $ipTV_db;
    global $deny;
    global $rUserInfo;
    global $downloading;

    if ($deny) {
        checkFlood();
    }
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    if ($downloading) {
        stopDownload('epg', $rUserInfo, getmypid());
    }
}
