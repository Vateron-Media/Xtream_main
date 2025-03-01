<?php

$rErrorCodes = array(
    'API_IP_NOT_ALLOWED' => 'IP is not allowed to access the API.',
    'ASN_BLOCKED' => 'ASN has been blocked.',
    'BANNED' => 'Line has been banned.',
    'BLOCKED_USER_AGENT' => 'User-agent has been blocked.',
    'DEVICE_NOT_ALLOWED' => 'MAG & Enigma devices are not allowed to access this.',
    'DISABLED' => 'Line has been disabled.',
    'DOWNLOAD_LIMIT_REACHED' => 'Reached the simultaneous download limit.',
    'EMPTY_USER_AGENT' => 'Empty user-agents are disallowed.',
    'EPG_DISABLED' => 'EPG has been disabled.',
    'EPG_FILE_MISSING' => 'Cached EPG files are missing.',
    'EXPIRED' => 'Line has expired.',
    'FORCED_COUNTRY_INVALID' => 'Country does not match forced country.',
    'GENERATE_PLAYLIST_FAILED' => 'Playlist failed to generate.',
    'HLS_DISABLED' => 'HLS has been disabled.',
    'INVALID_API_PASSWORD' => 'API password is invalid.',
    'INVALID_CREDENTIALS' => 'Username or password is invalid.',
    'INVALID_HOST' => 'Domain name not recognised.',
    'INVALID_STREAM_ID' => "Stream ID doesn't exist.",
    'INVALID_TYPE_TOKEN' => "Tokens can't be used for this stream type.",
    'IP_MISMATCH' => 'Current IP doesn’t match initial connection IP.',
    'ISP_BLOCKED' => 'ISP has been blocked.',
    'LB_TOKEN_INVALID' => 'AES Token cannot be decrypted.',
    'LEGACY_EPG_DISABLED' => 'Legacy epg.php access has been disabled.',
    'LINE_CREATE_FAIL' => 'Line failed to insert into database.',
    'NO_CREDENTIALS' => 'No credentials have been specified.',
    'NO_TOKEN_SPECIFIED' => 'No AES encrypted token has been specified.',
    'NOT_IN_ALLOWED_COUNTRY' => 'Not in allowed country list.',
    'NOT_IN_ALLOWED_IPS' => 'Not in allowed IP list.',
    'NOT_IN_ALLOWED_UAS' => 'Not in allowed user-agent list.',
    'NOT_IN_BOUQUET' => 'Line doesn’t have access to this stream ID.',
    'RESTREAM_DETECT' => 'Restreaming has been detected.',
    'STALKER_CHANNEL_MISMATCH' => "Stream ID doesn't match stalker token.",
    'STALKER_DECRYPT_FAILED' => 'Failed to decrypt stalker token.',
    'STALKER_INVALID_KEY' => 'Invalid stalker key.',
    'STALKER_IP_MISMATCH' => "IP doesn't match stalker token.",
    'STALKER_KEY_EXPIRED' => 'Stalker token has expired.',
    'TOKEN_ERROR' => 'AES token has incomplete data.',
    'TOKEN_EXPIRED' => 'AES token has expired.',
    'TS_DISABLED' => 'MPEG-TS has been disabled.',
    'USER_ALREADY_CONNECTED' => 'Line already connected on a different IP.',
    'USER_DISALLOW_EXT' => 'Extension is not in allowed list.',
    'VOD_DOESNT_EXIST' => "VOD file doesn't exist.",
    'WAIT_TIME_EXPIRED' => 'Stream start has timed out, failed to start.',
    'NO_SERVERS_AVAILABLE' => 'No servers are currently available for this stream.'
);

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    generate404();
}

@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0) Gecko/20100101 Firefox/9.0');
@ini_set('default_socket_timeout', 5);

// FOLDERS
if (!defined('MAIN_DIR')) {
    define('MAIN_DIR', '/home/xc_vm/');
}
define('IPTV_ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('INCLUDES_PATH', MAIN_DIR . 'includes/');
define('IMAGES_PATH', MAIN_DIR . 'wwwdir/images/');
define('ENIGMA2_IMAGES_PATH', IMAGES_PATH . 'enigma2/');
define('CRON_PATH', MAIN_DIR . 'crons/');
define('ASYNC_DIR', MAIN_DIR . 'async_incs/');
define('BIN_PATH', MAIN_DIR . 'bin/');
define('SIGNALS_PATH', MAIN_DIR . 'signals/');
define('CONFIG_PATH', MAIN_DIR . 'config/');
// -------------------

// INCLUDES FOLDER
define('CLI_PATH', INCLUDES_PATH . 'cli_tool/');
// -------------------

// BINARIES FILE
define('PHP_BIN', '/bin/php');
define('YOUTUBE_PATH', BIN_PATH . 'yt-dlp');
define('GEOIP2COUNTRY_FILENAME', BIN_PATH . 'maxmind/GeoLite2-Country.mmdb');
define('GEOIP2ASN_FILENAME', BIN_PATH . 'maxmind/GeoLite2-ASN.mmdb');
define('GEOIP2CITY_FILENAME', BIN_PATH . 'maxmind/GeoLite2-City.mmdb');
define('GEOIP2ISP_FILENAME', BIN_PATH . 'maxmind/GeoIP2-ISP.mmdb');
define('FFMPEG_BIN_40', BIN_PATH . 'ffmpeg_bin/4.0/ffmpeg');
define('FFMPEG_BIN_43', BIN_PATH . 'ffmpeg_bin/4.3/ffmpeg');
define('FFMPEG_BIN_44', BIN_PATH . 'ffmpeg_bin/4.4/ffmpeg');
define('FFPROBE_BIN_40', BIN_PATH . 'ffmpeg_bin/4.0/ffprobe');
define('FFPROBE_BIN_43', BIN_PATH . 'ffmpeg_bin/4.3/ffprobe');
define('FFPROBE_BIN_44', BIN_PATH . 'ffmpeg_bin/4.4/ffprobe');
// -------------------

// TEMP FOLDERS
if (!defined('TMP_PATH')) {
    define('TMP_PATH', MAIN_DIR . 'tmp/');
}
define('CACHE_TMP_PATH', TMP_PATH . 'cache/');
define('CONS_TMP_PATH', TMP_PATH . 'opened_cons/');
define('DIVERGENCE_TMP_PATH', TMP_PATH . 'divergence/');
define('FLOOD_TMP_PATH', TMP_PATH . 'flood/');
define('STALKER_TMP_PATH', TMP_PATH . 'stalker/');
define('LOGS_TMP_PATH', TMP_PATH . 'logs/');
define('CRONS_TMP_PATH', TMP_PATH . 'crons/');
define('SIGNALS_TMP_PATH', TMP_PATH . 'signals/');
define('CIDR_TMP_PATH', TMP_PATH . 'cidr/');
// -------------------

// CACHE FOLDERS
define('STREAMS_TMP_PATH', CACHE_TMP_PATH . 'streams/');
define('USER_TMP_PATH', CACHE_TMP_PATH . 'lines/');
define('SERIES_TMP_PATH', CACHE_TMP_PATH . 'series/');
// -------------------

//CONTENT FOLDERS
if (!defined('CONTENT_PATH')) {
    define('CONTENT_PATH', MAIN_DIR . 'content/');
}
define('CREATED_CHANNELS', CONTENT_PATH . 'created_channels/');
define('DELAY_PATH', CONTENT_PATH . 'delayed/');
define('EPG_PATH', CONTENT_PATH . 'epg/');
define('PLAYLIST_PATH', CONTENT_PATH . 'playlists/');
define('STREAMS_PATH', CONTENT_PATH . 'streams/');
define('ARCHIVE_PATH', CONTENT_PATH . 'tv_archive/');
define('VOD_PATH', CONTENT_PATH . 'vod/');
define('CREATED_PATH', CONTENT_PATH . 'created/');
define('VIDEO_PATH', CONTENT_PATH . 'video/');
// -------------------

// CONSTANTS VAR
define('SCRIPT_VERSION', '1.2.6.3');
define('FFMPEG_FONTS_PATH', BIN_PATH . 'free-sans.ttf');
define('OPENSSL_EXTRA', '5gd46z5s4fg6sd8f4gs6');
define('RESTART_TAKE_CACHE', 5);
define('MONITOR_CALLS', 3);
// -------------------

define('CACHE_STREAMS', false);
define('CACHE_STREAMS_TIME', 10);
define('STREAM_TYPE', array('live', 'series', 'movie', 'created_live', 'radio_streams'));

global $argc;
$showErrors = false;

if (!$argc) {
    $rIP = $_SERVER['REMOTE_ADDR'];
    if (empty($rIP) || !file_exists(FLOOD_TMP_PATH . 'block_' . $rIP)) {
        define('HOST', trim(explode(':', $_SERVER['HTTP_HOST'])[0]));

        if (file_exists(CACHE_TMP_PATH . 'settings')) {
            $rData = file_get_contents(CACHE_TMP_PATH . 'settings');
            $Settings = igbinary_unserialize($rData);

            $showErrors = (isset($Settings['debug_show_errors']) ? $Settings['debug_show_errors'] : false);
        }
    } else {
        http_response_code(403);

        exit();
    }
}

define('PHP_ERRORS', $showErrors);
set_error_handler('log_error');
set_exception_handler('log_exception');
register_shutdown_function('log_fatal');

if (PHP_ERRORS) {
    error_reporting(5);
    ini_set('display_errors', true);
    ini_set('display_startup_errors', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

function getExceptionTraceAsString($exception) {
    $rtn = "";
    $count = 0;
    foreach ($exception->getTrace() as $frame) {
        $args = "";
        if (isset($frame['args'])) {
            $args = array();
            foreach ($frame['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . $arg . "'";
                } elseif (is_array($arg)) {
                    $args[] = "Array";
                } elseif (is_null($arg)) {
                    $args[] = 'NULL';
                } elseif (is_bool($arg)) {
                    $args[] = ($arg) ? "true" : "false";
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } elseif (is_resource($arg)) {
                    $args[] = get_resource_type($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $args = join(", ", $args);
        }
        $current_file = "[internal function]";
        if (isset($frame['file'])) {
            $current_file = $frame['file'];
        }
        $current_line = "";
        if (isset($frame['line'])) {
            $current_line = $frame['line'];
        }
        $rtn .= sprintf(
            "#%s %s(%s): %s(%s)\n",
            $count,
            $current_file,
            $current_line,
            $frame['function'],
            $args
        );
        $count++;
    }
    return $rtn;
}

function log_error($rErrNo, $rMessage, $rFile, $rLine, $rContext = null) {
    if (in_array($rErrNo, array(1, 2, 4))) {
        $error = array(1 => 'error', 2 => 'warning', 4 => 'parse')[$rErrNo];
        panellog($error, $rMessage, $rFile, $rLine);
    }
}

function log_exception($e) {
    panellog('exception', $e->getMessage(), "File: " . $e->getFile() . "\nTrace: " . $e->getTraceAsString(), $e->getLine());
}

function log_fatal() {
    $rError = error_get_last();
    if ($rError !== null && $rError['type'] == 1) {
        panellog('error', $rError['message'], $rError['file'], $rError['line']);
    }
}

function panelLog($rType, $rMessage, $rExtra = '', $rLine = 0) {
    $logFile = LOGS_TMP_PATH . 'error_log.log';
    // Ensure directory exists
    if (!is_dir(LOGS_TMP_PATH)) {
        mkdir(LOGS_TMP_PATH, 0775, true);
    }
    $data = [
        'type' => $rType,
        'message' => $rMessage,
        'extra' => $rExtra,
        'line' => $rLine,
        'time' => time()
    ];
    // Write log
    file_put_contents($logFile, base64_encode(json_encode($data)) . "\n", FILE_APPEND);
}


function generateError($rError, $rKill = true, $rCode = null) {
    global $rErrorCodes;
    global $Settings;

    if ($Settings['debug_show_errors']) {
        $rErrorDescription = ($rErrorCodes[$rError] ?: '');
        $rStyle = '*{-webkit-box-sizing:border-box;box-sizing:border-box}body{padding:0;margin:0}#notfound{position:relative;height:100vh}#notfound .notfound{position:absolute;left:50%;top:50%;-webkit-transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%);transform:translate(-50%,-50%)}.notfound{max-width:520px;width:100%;line-height:1.4;text-align:center}.notfound .notfound-404{position:relative;height:200px;margin:0 auto 20px;z-index:-1}.notfound .notfound-404 h1{font-family:Montserrat,sans-serif;font-size:236px;font-weight:200;margin:0;color:#211b19;text-transform:uppercase;position:absolute;left:50%;top:50%;-webkit-transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%);transform:translate(-50%,-50%)}.notfound .notfound-404 h2{font-family:Montserrat,sans-serif;font-size:28px;font-weight:400;text-transform:uppercase;color:#211b19;background:#fff;padding:10px 5px;margin:auto;display:inline-block;position:absolute;bottom:0;left:0;right:0}.notfound p{font-family:Montserrat,sans-serif;font-size:14px;font-weight:300;text-transform:uppercase}@media only screen and (max-width:767px){.notfound .notfound-404 h1{font-size:148px}}@media only screen and (max-width:480px){.notfound .notfound-404{height:148px;margin:0 auto 10px}.notfound .notfound-404 h1{font-size:86px}.notfound .notfound-404 h2{font-size:16px}}';
        echo '<html><head><title>XC_VM - Debug Mode</title><link href="https://fonts.googleapis.com/css?family=Montserrat:200,400,700" rel="stylesheet"><style>' . $rStyle . '</style></head><body><div id="notfound"><div class="notfound"><div class="notfound-404"><h1>XC_VM</h1><h2>' . $rError . '</h2><br/></div><p>' . $rErrorDescription . '</p></div></div></body></html>';
        if ($rKill) {
            exit();
        }
    } else {
        if ($rKill) {
            if (!$rCode) {
                generate404();
            } else {
                http_response_code($rCode);
                exit();
            }
        }
    }
}

function generate404($rKill = true) {
    echo "<html>\r\n<head><title>404 Not Found</title></head>\r\n<body>\r\n<center><h1>404 Not Found</h1></center>\r\n<hr><center>nginx</center>\r\n</body>\r\n</html>\r\n<!-- a padding to disable MSIE and Chrome friendly error page -->\r\n<!-- a padding to disable MSIE and Chrome friendly error page -->\r\n<!-- a padding to disable MSIE and Chrome friendly error page -->\r\n<!-- a padding to disable MSIE and Chrome friendly error page -->\r\n<!-- a padding to disable MSIE and Chrome friendly error page -->\r\n<!-- a padding to disable MSIE and Chrome friendly error page -->";
    http_response_code(404);

    if ($rKill) {
        exit();
    }
}
