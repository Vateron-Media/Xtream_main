<?php
set_time_limit(0);
if ($argc && count($argv) == 2) {
    register_shutdown_function('shutdown');
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    loadcli();
} else {
    exit(0);
}

function loadcli() {
    global $ipTV_db;

    if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
        foreach (ipTV_lib::$StreamingServers as $rServer) {
            if ($rServer['enabled'] && $rServer['status'] == 1 && time() - $rServer['last_check_ago'] <= 180 || !$rServer['is_main']) {
                $ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(\'%s\', \'%s\', \'%s\');', $rServer['id'], time(), json_encode(array('action' => 'update')));
            }
        }
    }
    $ipTV_db->query('UPDATE `streaming_servers` SET `status` = 1, `script_version` = \'%s\' WHERE `id` = \'%s\';', SCRIPT_VERSION, SERVER_ID);

    // // remove old script
    // if (!ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
    //     if (file_exists('/test')) {
    //         unlink('/test');
    //     }
    // }

    exec('sudo ' . PHP_BIN . ' ' . TOOLS_PATH . '/update_bd.php');
}
function shutdown() {
    global $ipTV_db;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
