<?php
// close conections reseler
set_time_limit(0);
if (!@$argc) {
    die(0);
}
require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
shell_exec('kill $(ps aux | grep pipe_reader | grep -v grep | grep -v ' . getmypid() . ' | awk \'{print $2}\')');

if (is_dir(CLOSE_OPEN_CONS_PATH)) {
    shell_exec('(sleep 2; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
} else {
    mkdir(CLOSE_OPEN_CONS_PATH);
}

$files = scandir(CLOSE_OPEN_CONS_PATH);
unset($files[0]);
unset($files[1]);

if (!empty($files)) {
    foreach ($files as $file) {
        unlink(CLOSE_OPEN_CONS_PATH . $file);
    }
    if ($ipTV_db->query('DELETE FROM `lines_live` WHERE `activity_id` IN (' . implode(',', $files) . ')') !== false) {
        // Query executed successfully
    }
} else {
    usleep(4000);
}
