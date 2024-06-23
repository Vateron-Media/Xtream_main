<?php
set_time_limit(0);
if ($argc && count($argv) == 2) {
    register_shutdown_function('shutdown');
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    $rCommand = $argv[1];
    loadcli();
} else {
    exit(0);
}
function loadcli() {
    global $ipTV_db;
    global $rCommand;
    switch ($rCommand) {
        case 'update':
            if (ipTV_lib::$StreamingServers[SERVER_ID]['is_main']) {
                $rUpdate = checkUpdate(SCRIPT_VERSION);
            }
            if ($rUpdate && 0 < strlen($rUpdate['url'])) {
                $rData = fopen($rUpdate['url'], 'rb');
                $rOutputDir = TMP_DIR . '.update.tar.gz';
                $rOutput = fopen($rOutputDir, 'wb');
                stream_copy_to_stream($rData, $rOutput);
                fclose($rData);
                fclose($rOutput);
                if (md5_file($rOutputDir) == $rUpdate['md5']) {
                    $ipTV_db->query('UPDATE `streaming_servers` SET `status` = 5 WHERE `id` = \'%s\';', SERVER_ID);
                    $rCommand = 'sudo /usr/bin/python3 ' . IPTV_PANEL_DIR . 'pytools3/update.py "' . $rOutputDir . '" "' . $rUpdate['md5'] . '" > /dev/null 2>&1 &';
                    shell_exec($rCommand);
                    exit(1);
                }
                exit(-1);
            }
            exit(0);
        case 'post-update':
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

            //exec('sudo ' . IPTV_PANEL_DIR . 'upd_bd.php');
            break;
    }
}
function shutdown() {
    global $ipTV_db;
    if (!is_object($ipTV_db)) {
    } else {
        $ipTV_db->close_mysql();
    }
}
