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
        $rCrons = array();
        if (file_exists(CRON_PATH . 'root_signals.php')) {
            $rCrons[] = '* * * * * ' . PHP_BIN . ' ' . CRON_PATH . 'root_signals.php # XtreamCodes';
        }
        $rWrite = false;
        exec('sudo crontab -l', $rOutput);
        foreach ($rCrons as $rCron) {
            if (!in_array($rCron, $rOutput)) {
                $rOutput[] = $rCron;
                $rWrite = true;
            }
        }
        if ($rWrite) {
            $rCronFile = tempnam(TMP_PATH, 'crontab');
            file_put_contents($rCronFile, implode("\n", $rOutput) . "\n");
            exec('sudo chattr -i /var/spool/cron/crontabs/root');
            exec('sudo crontab -r');
            exec('sudo crontab ' . $rCronFile);
            exec('sudo chattr +i /var/spool/cron/crontabs/root');
            echo 'Crontab installed' . "\n";
        } else {
            echo 'Crontab already installed' . "\n";
        }

        if (!$rFixCron) {
            exec('sudo -u xtreamcodes ' . PHP_BIN . ' ' . CRON_PATH . 'cache.php 1', $rOutput);
            if (file_exists(CRON_PATH . 'cache_engine.php') || !file_exists(CACHE_TMP_PATH . 'cache_complete')) {
                echo 'Generating cache...' . "\n";
                exec('sudo -u xtreamcodes ' . PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php >/dev/null 2>/dev/null &');
            }
        }
    } else {
        if (!$rFixCron) {
            exec(PHP_BIN . ' ' . CRON_PATH . 'cache.php 1');
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
