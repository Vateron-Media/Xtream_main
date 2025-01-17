<?php

require_once 'constants.php';
require_once INCLUDES_PATH . 'lib.php';
require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'pdo.php';
require_once INCLUDES_PATH . 'streaming.php';
require_once INCLUDES_PATH . 'stream.php';
require_once INCLUDES_PATH . 'servers.php';

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $rHeaders = array();

        foreach ($_SERVER as $rName => $rValue) {
            if (substr($rName, 0, 5) != 'HTTP_') {
                $rHeaders[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($rName, 5)))))] = $rValue;
            }
        }

        return $rHeaders;
    }
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    generate404();
}

$_INFO = array();

if (file_exists(MAIN_DIR . 'config')) {
    $_INFO = parse_ini_file(CONFIG_PATH . 'config.ini');
    define('SERVER_ID', $_INFO['server_id']);
} else {
    die('no config found');
}

$ipTV_db = new Database($_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['hostname'], $_INFO['port'], empty($_INFO['pconnect']) ? false : true);

ipTV_lib::$ipTV_db = &$ipTV_db;
ipTV_streaming::$ipTV_db = &$ipTV_db;
ipTV_stream::$ipTV_db = &$ipTV_db;

$rFilename = strtolower(basename(get_included_files()[0], '.php'));

if (!in_array($rFilename, array('enigma2', 'epg', 'playlist', 'api', 'xplugin', 'live', 'thumb', 'timeshift', 'vod')) || $argc) {
    ipTV_lib::init();
} else {
    ipTV_lib::init(true);
}
