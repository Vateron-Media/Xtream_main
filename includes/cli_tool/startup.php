<?php
set_time_limit(0);
if ($argc) {
    $rFixCron = false;
    if (count($argv) > 0) {
        if (intval($argv[1]) == 1) {
            $rFixCron = true;
        }
    }
    require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(32767);
    if (file_exists(MAIN_DIR . 'status')) {
        exec('sudo ' . MAIN_DIR . 'status 1');
    }
    if (filesize(MAIN_DIR . 'bin/daemons.sh') == 0) {
        echo 'Daemons corrupted! Regenerating...' . "\n";
        $rNewScript = "#! /bin/bash\n\n"
            . "if pgrep -u xtreamcodes php-fpm > /dev/null; then\n"
            . "    echo \"PHP-FPM is already running, stopping existing instances...\"\n"
            . "    pkill -u xtreamcodes php-fpm\n"
            . "    sleep 2\n"
            . "fi\n\n"
            . "# Now start PHP-FPM instances\n";
        $rNewBalance = 'upstream php {' . "\n" . '    least_conn;' . "\n";
        $rTemplate = file_get_contents(MAIN_DIR . 'bin/php/etc/template');
        exec('rm -f ' . MAIN_DIR . 'bin/php/etc/*.conf');
        foreach (range(1, 4) as $i) {
            $rNewScript .= 'start-stop-daemon --start --quiet --pidfile ' . MAIN_DIR . 'bin/php/sockets/' . $i . '.pid --exec ' . MAIN_DIR . 'bin/php/sbin/php-fpm -- --daemonize --fpm-config ' . MAIN_DIR . 'bin/php/etc/' . $i . '.conf' . "\n";
            $rNewBalance .= '    server unix:' . MAIN_DIR . 'bin/php/sockets/' . $i . '.sock;' . "\n";
            file_put_contents(MAIN_DIR . 'bin/php/etc/' . $i . '.conf', str_replace('#PATH#', MAIN_DIR, str_replace('#ID#', $i, $rTemplate)));
        }
        $rNewBalance .= '}';
        file_put_contents(MAIN_DIR . 'bin/daemons.sh', $rNewScript);
        file_put_contents(MAIN_DIR . 'bin/nginx/conf/balance.conf', $rNewBalance);
    }
    if (posix_getpwuid(posix_geteuid())['name'] == 'root') {
        $rCrons = array();
        if (file_exists(CRON_PATH . 'root_signals.php')) {
            $rCrons[] = '* * * * * ' . PHP_BIN . ' ' . CRON_PATH . 'root_signals.php # XC_VM';
        }
        // if (file_exists(CRON_PATH . 'root_mysql.php')) {
        //     $rCrons[] = '* * * * * ' . PHP_BIN . ' ' . CRON_PATH . 'root_mysql.php # XC_VM';
        // }
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
            if (file_exists(CRON_PATH . 'cache_engine.php') || !file_exists(CACHE_TMP_PATH . 'cache_complete')) {
                echo 'Generating cache...' . "\n";
                exec(PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php >/dev/null 2>/dev/null &');
            }
        }
    }
    echo "\n";
} else {
    exit(0);
}
