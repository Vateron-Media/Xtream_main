<?php
require_once 'constants.php';
require INCLUDES_PATH . 'functions.php';
require INCLUDES_PATH . 'lib.php';
require INCLUDES_PATH . 'pdo.php';
require INCLUDES_PATH . 'streaming.php';
require INCLUDES_PATH . 'servers.php';
require INCLUDES_PATH . 'stream.php';
require IPTV_ROOT_PATH . 'langs/English.php';

$_INFO = array();

if (file_exists(MAIN_DIR . 'config')) {
    $_INFO = parse_ini_file(CONFIG_PATH . 'config.ini');
    define('SERVER_ID', $_INFO['server_id']);
} else {
    die('no config found');
}

$ipTV_db = new ipTV_db($_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['hostname'], $_INFO['port'], empty($_INFO['pconnect']) ? false : true, false);

ipTV_lib::$ipTV_db = &$ipTV_db;
ipTV_streaming::$ipTV_db = &$ipTV_db;
ipTV_stream::$ipTV_db = &$ipTV_db;
ipTV_lib::init();
ipTV_lib::connectRedis();

$FILES = array('live.php', 'clients_movie.php', 'timeshift.php', 'admin_live.php', 'admin_movie.php', 'xmltv.php', 'panel_api.php', 'enigma2.php', 'portal.php', 'get.php');

if (empty($argc)) {
    if (!in_array(basename($_SERVER['SCRIPT_FILENAME']), $FILES)) {
        CheckFlood();
    }
}
