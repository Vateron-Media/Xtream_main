<?php

require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';

$crons = scandir(CRON_PATH);
foreach ($crons as $cron) {
    $full_path = CRON_PATH . $cron;
    if (!is_file($full_path)) {
        continue;
    }
    if (pathinfo($full_path, PATHINFO_EXTENSION) != 'php') {
        continue;
    }
    print "Running: " . $cron . "\n";
    // shell_exec(PHP_BIN . ' ' . $full_path);
}
