<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('STATUS_FAILURE', 0);
define('STATUS_SUCCESS', 1);

$_INFO = array();
$rTimeout = 60;             // Seconds Timeout for Functions & Requests
$rSQLTimeout = 5;           // Max execution time for MySQL queries.

require_once '/home/xtreamcodes/wwwdir/constants.php';
// require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'lib.php';
require_once INCLUDES_PATH . 'pdo.php';
// require_once INCLUDES_PATH . 'streaming.php';
// require_once INCLUDES_PATH . 'servers.php';
// require_once INCLUDES_PATH . 'stream.php';
require_once INCLUDES_PATH . 'admin_api.php';
require_once INCLUDES_PATH . 'libs/mobiledetect.php';
require_once INCLUDES_PATH . 'libs/gauth.php';
register_shutdown_function('shutdown_admin');



if (file_exists(MAIN_DIR . 'config')) {
    $_INFO = parse_ini_file(CONFIG_PATH . 'config.ini');
    define('SERVER_ID', $_INFO['server_id']);
} else {
    die('no config found');
}

$ipTV_db_admin = new ipTV_db($_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['hostname'], $_INFO['port'], empty($_INFO['pconnect']) ? false : true, false);

if (!$db = new mysqli($_INFO["hostname"], $_INFO["username"], $_INFO["password"], $_INFO["database"], $_INFO["port"])) {
    exit("No MySQL connection!");
}
$db->set_charset("utf8");
$db->query("SET GLOBAL MAX_EXECUTION_TIME=" . ($rSQLTimeout * 1000) . ";");


ipTV_lib::$ipTV_db = &$ipTV_db_admin;
// ipTV_streaming::$ipTV_db = &$ipTV_db_admin;
// ipTV_stream::$ipTV_db = &$ipTV_db_admin;
API::$ipTV_db = &$ipTV_db_admin;
API::init();
ipTV_lib::init();
ipTV_lib::connectRedis();

$rProtocol = getProtocol();
$rSettings = ipTV_lib::$settings;
$detect = new Mobile_Detect;

date_default_timezone_set($rSettings['default_timezone']);

set_time_limit($rTimeout);
ini_set('mysql.connect_timeout', $rSQLTimeout);
ini_set('max_execution_time', $rTimeout);
ini_set('default_socket_timeout', $rTimeout);


function getAdminSettings() {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `type`, `value` FROM `admin_settings`;");
    $rows = $ipTV_db_admin->get_rows();
    foreach ($rows as $val) {
        $output[$val['type']] = $val['value'];
    }
    return $output;
}

function getRegisteredUserHash($rHash) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `reg_users` WHERE MD5(`username`) = '%s' LIMIT 1;", $rHash);
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function doLogin($rUsername, $rPassword) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `id`, `username`, `password`, `member_group_id`, `google_2fa_sec`, `status` FROM `reg_users` WHERE `username` = '%s' LIMIT 1;", $rUsername);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rRow = $ipTV_db_admin->get_row();

        if (cryptPassword($rPassword) == $rRow["password"]) {
            return $rRow;
        }
    }
    return null;
}

function getPermissions($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `member_groups` WHERE `group_id` = %s;", intval($rID));
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

// function getCategories($rType = "live") {
//     global $ipTV_db_admin;
//     $return = array();
//     if ($rType) {
//         $ipTV_db_admin->query("SELECT * FROM `stream_categories` WHERE `category_type` = '" . ESC($rType) . "' ORDER BY `cat_order` ASC;");
//     } else {
//         $ipTV_db_admin->query("SELECT * FROM `stream_categories` ORDER BY `cat_order` ASC;");
//     }
//     if ($ipTV_db_admin->num_rows() > 0) {
//         foreach ($ipTV_db_admin->get_rows() as $rRow) {
//             $rReturn[intval($rRow['id'])] = $rRow;
//         }
//     }
//     return $return;
// }

function getStreamingServers($rActive = false) {
    global $ipTV_db_admin, $rPermissions;
    $return = array();
    if ($rActive) {
        $ipTV_db_admin->query("SELECT * FROM `streaming_servers` WHERE `status` = 1 ORDER BY `id` ASC;");
    } else {
        $ipTV_db_admin->query("SELECT * FROM `streaming_servers` ORDER BY `id` ASC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if ($rPermissions["is_reseller"]) {
                $row["server_name"] = "Server #" . $row["id"];
            }
            $return[intval($row['id'])] = $row;
        }
    }
    return $return;
}

function secondsToTime($rInputSeconds, $rInclSecs = true) {
    $rSecondsInAMinute = 60;
    $rSecondsInAnHour = 60 * $rSecondsInAMinute;
    $rSecondsInADay = 24 * $rSecondsInAnHour;
    $rDays = (int) floor($rInputSeconds / (($rSecondsInADay ?: 1)));
    $rHourSeconds = $rInputSeconds % $rSecondsInADay;
    $rHours = (int) floor($rHourSeconds / (($rSecondsInAnHour ?: 1)));
    $rMinuteSeconds = $rHourSeconds % $rSecondsInAnHour;
    $rMinutes = (int) floor($rMinuteSeconds / (($rSecondsInAMinute ?: 1)));
    $rRemaining = $rMinuteSeconds % $rSecondsInAMinute;
    $rSeconds = (int) ceil($rRemaining);
    $rOutput = '';
    if ($rDays != 0) {
        $rOutput .= $rDays . 'd ';
    }
    if ($rHours != 0) {
        $rOutput .= $rHours . 'h ';
    }
    if ($rMinutes != 0) {
        $rOutput .= $rMinutes . 'm ';
    }
    if ($rInclSecs) {
        $rOutput .= $rSeconds . 's';
    }
    return $rOutput;
}

function generateString($strength = 10) {
    $input = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $input_length = strlen($input);
    $random_string = '';
    for ($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}

function shutdown_admin() {
    global $ipTV_db_admin;

    if (is_object($ipTV_db_admin)) {
        $ipTV_db_admin->close_mysql();
    }
}

function issecure() {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443;
}

/** 
 * Encrypts the provided data using AES-256-CBC encryption with a given decryption key and device ID. 
 *  
 * @param string $rData The data to be encrypted. 
 * @param string $decryptionKey The decryption key used to encrypt the data. 
 * @param string $rDeviceID The device ID used in the encryption process. 
 * @return string The encrypted data in base64url encoding. 
 */
function encryptData($rData, $decryptionKey, $rDeviceID) {
    return base64url_encode(openssl_encrypt($rData, 'aes-256-cbc', md5(sha1($rDeviceID) . $decryptionKey), OPENSSL_RAW_DATA, substr(md5(sha1($decryptionKey)), 0, 16)));
}
/** 
 * Decrypts the provided data using AES-256-CBC decryption with a given decryption key and device ID. 
 *  
 * @param string $rData The data to be decrypted. 
 * @param string $decryptionKey The decryption key used to decrypt the data. 
 * @param string $rDeviceID The device ID used in the decryption process. 
 * @return string The decrypted data. 
 */
function decryptData($rData, $decryptionKey, $rDeviceID) {
    return openssl_decrypt(base64url_decode($rData), 'aes-256-cbc', md5(sha1($rDeviceID) . $decryptionKey), OPENSSL_RAW_DATA, substr(md5(sha1($decryptionKey)), 0, 16));
}
/** 
 * Encodes the input data using base64url encoding. 
 * 
 * This function takes the input data and encodes it using base64 encoding. It then replaces the characters '+' and '/' with '-' and '_', respectively, to make the encoding URL-safe. Finally, it removes any padding '=' characters at the end of the encoded string. 
 * 
 * @param string $rData The input data to be encoded. 
 * @return string The base64url encoded string. 
 */
function base64url_encode($rData) {
    return rtrim(strtr(base64_encode($rData), '+/', '-_'), '=');
}
/** 
 * Decodes the input data encoded using base64url encoding. 
 * 
 * This function takes the input data encoded using base64url encoding and decodes it. It first replaces the characters '-' and '_' back to '+' and '/' respectively, to revert the URL-safe encoding. Then, it decodes the base64 encoded string to retrieve the original data. 
 * 
 * @param string $rData The base64url encoded data to be decoded. 
 * @return string|false The decoded original data, or false if decoding fails. 
 */
function base64url_decode($rData) {
    return base64_decode(strtr($rData, '-_', '+/'));
}

function getProtocol() {
    if (issecure()) {
        return 'https';
    }
    return 'http';
}
