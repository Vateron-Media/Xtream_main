<?php
require_once 'constants.php';
require IPTV_INCLUDES_PATH . 'functions.php';
require IPTV_INCLUDES_PATH . 'lib.php';
require IPTV_INCLUDES_PATH . 'mysql.php';
require IPTV_INCLUDES_PATH . 'streaming.php';
require IPTV_INCLUDES_PATH . 'servers.php';
require IPTV_INCLUDES_PATH . 'stream.php';
require IPTV_ROOT_PATH . 'langs/English.php';

$_INFO = array();

if (file_exists(MAIN_DIR . 'config')) {
    $_INFO = json_decode(decrypt_config(base64_decode(file_get_contents(MAIN_DIR . 'config')), CONFIG_CRYPT_KEY), true);
    define('SERVER_ID', $_INFO['server_id']);
} else {
    die('no config found');
}

$ipTV_db = new ipTV_db($_INFO['db_user'], $_INFO['db_pass'], $_INFO['db_name'], $_INFO['host'], $_INFO['db_port'], empty($_INFO['pconnect']) ? false : true, false);

ipTV_lib::$ipTV_db = &$ipTV_db;
ipTV_streaming::$ipTV_db = &$ipTV_db;
ipTV_stream::$ipTV_db = &$ipTV_db;
ipTV_lib::init();

include IPTV_INCLUDES_PATH . 'geo/Reader.php';
include IPTV_INCLUDES_PATH . 'geo/Decoder.php';
include IPTV_INCLUDES_PATH . 'geo/Util.php';
include IPTV_INCLUDES_PATH . 'geo/Metadata.php';
include IPTV_INCLUDES_PATH . 'geo/InvalidDatabaseException.php';

$FILES = array('live.php', 'clients_movie.php', 'timeshift.php', 'admin_live.php', 'admin_movie.php', 'xmltv.php', 'panel_api.php', 'enigma2.php', 'portal.php', 'get.php');

if (empty($argc)) {
    if (!in_array(basename($_SERVER['SCRIPT_FILENAME']), $FILES)) {
        CheckFlood();
    }
}
