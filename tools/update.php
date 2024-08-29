<?php
set_time_limit(0);
if ($argc && count($argv) == 2) {
    register_shutdown_function('shutdown');
    require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
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
            $rContext = stream_context_create(array('http' => array('timeout' => 3)));
            $updateVersion = json_decode(file_get_contents("https://raw.githubusercontent.com/Vateron-Media/Xtream_Update/main/version.json", false, $rContext), True)["main"];
            $nextVersion = getNextVersionUpdate(SCRIPT_VERSION, $updateVersion);
            if (!$nextVersion) {
                exit(1);
            }

            $URL = "https://github.com/Vateron-Media/Xtream_main/releases/download/{$nextVersion}/update.tar.gz";

            # make dir
            if (!file_exists(UPDATE_PATH)) {
                mkdir(UPDATE_PATH);
            }

            echo 'Download Update.....' . "\n";
            $data = file_get_contents($URL);
            file_put_contents(UPDATE_PATH . "update.tar.gz", $data);

            echo 'Unzip update.tar.gz' . "\n";
            $phar = new PharData(UPDATE_PATH . 'update.tar.gz');
            $phar->extractTo(UPDATE_PATH, null, true);

            if (file_exists(UPDATE_PATH . "update.tar.gz")) {
                unlink(UPDATE_PATH . "update.tar.gz");
            }

            echo 'Run python update.py' . "\n";
            $ipTV_db->query('UPDATE `streaming_servers` SET `status` = 5 WHERE `id` = \'%s\';', SERVER_ID);
            $rCommand = 'sudo /usr/bin/python3 ' . MAIN_DIR . 'update.py > /dev/null 2>&1 &';
            shell_exec($rCommand);
            exit(1);

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

            exec('sudo ' . PHP_BIN . ' ' . TOOLS_PATH . '/update_bd.php');
            exec('sudo ' . MAIN_DIR . 'status');
            break;
    }
}
function getNextVersionUpdate($curentVersion, $updateVersion) {
    $context = stream_context_create(
        array(
            "http" => array(
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
            )
        )
    );
    $URLTagsRelease = "https://api.github.com/repos/Vateron-Media/Xtream_main/git/refs/tags";
    $tags = json_decode(file_get_contents($URLTagsRelease, false, $context), True);

    $versions = [];
    foreach ($tags as $value) {
        $latestTag = $value['ref'];
        $latestTag = str_replace("refs/tags/", "", $latestTag);
        $versions[] = $latestTag;
    }
    #get key in array versions
    $CurentKey = array_search("v{$curentVersion}", $versions);
    $UpdKey = array_search("v{$updateVersion}", $versions);

    if ($CurentKey < $UpdKey) {
        return $versions[$CurentKey + 1];
    } else {
        return false;
    }
}

function shutdown() {
    global $ipTV_db;
    if (!is_object($ipTV_db)) {
    } else {
        $ipTV_db->close_mysql();
    }
}
