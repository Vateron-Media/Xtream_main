<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc && $argc > 1) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
        $rStreamID = intval($argv[1]);
        checkRunning($rStreamID);
        set_time_limit(0);
        cli_set_process_title('XC_VMCreate[' . $rStreamID . ']');
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
function checkRunning($rStreamID) {
    clearstatcache(true);
    if (file_exists(CREATED_PATH . $rStreamID . '_.create')) {
        $rPID = intval(file_get_contents(CREATED_PATH . $rStreamID . '_.create'));
    }
    if (empty($rPID)) {
        shell_exec("kill -9 `ps -ef | grep 'XC_VMCreate\\[" . intval($rStreamID) . "\\]' | grep -v grep | awk '{print \$2}'`;");
    } else {
        if (file_exists('/proc/' . $rPID)) {
            $rCommand = trim(file_get_contents('/proc/' . $rPID . '/cmdline'));
            if ($rCommand == 'XC_VMCreate[' . $rStreamID . ']') {
                posix_kill($rPID, 9);
            }
        }
    }
    file_put_contents(CREATED_PATH . $rStreamID . '_.create', getmypid());
}
function shutdown() {
    global $ipTV_db;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
