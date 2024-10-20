<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '/home/xtreamcodes/wwwdir/constants.php';
require INCLUDES_PATH . 'functions.php';
require INCLUDES_PATH . 'lib.php';
require INCLUDES_PATH . 'pdo.php';
require INCLUDES_PATH . 'streaming.php';
require INCLUDES_PATH . 'servers.php';
require INCLUDES_PATH . 'stream.php';
