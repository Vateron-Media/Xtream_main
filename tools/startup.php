<?php
set_time_limit(0);
if ($argc) {
    $rFixCron = false;
    if (count($argv) > 1) {
        if (intval($argv[1]) == 1) {
            $rFixCron = true;
        }
    }
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    if (file_exists(MAIN_DIR . 'status')) {
        exec('sudo ' . MAIN_DIR . 'status 1');
    }
    if (posix_getpwuid(posix_geteuid())['name'] == 'root') {
        exec('sudo crontab -l', $rOutput);
        if (!$rFixCron) {
            exec('sudo -u xtreamcodes ' . PHP_BIN . ' ' . CRON_PATH . 'setup_cache.php 1', $rOutput);
            if (file_exists(CRON_PATH . 'cache_engine.php') || !file_exists(CACHE_TMP_PATH . 'cache_complete')) {
                echo 'Generating cache...' . "\n";
                exec('sudo -u xtreamcodes ' . PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php >/dev/null 2>/dev/null &');
            }
        }
    } else {
        if (!$rFixCron) {
            exec(PHP_BIN . ' ' . CRON_PATH . 'setup_cache.php 1');
            if (file_exists(CRON_PATH . 'cache_engine.php') || file_exists(CACHE_TMP_PATH . 'cache_complete')) {
                echo 'Generating cache...' . "\n";
                exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php >/dev/null 2>/dev/null &');
            }
        }
    }
    echo "\n";
} else {
    exit(0);
}
