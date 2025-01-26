<?php
set_time_limit(0);
if ($argc && count($argv) == 2) {
    register_shutdown_function('shutdown');
    require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
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
            $updateVersion = mb_substr(get_recent_stable_release("https://github.com/Vateron-Media/Xtream_main/releases/latest"), 1);
            $nextVersion = getNextVersionUpdate(SCRIPT_VERSION, $updateVersion);
            if (!$nextVersion) {
                exit(1);
            }

            $URL = "https://github.com/Vateron-Media/Xtream_main/releases/download/{$nextVersion}/update.tar.gz";

            echo 'Download Update.....' . "\n";
            $rData = fopen($URL, 'rb');
            $rOutputDir = TMP_PATH . 'update.tar.gz';
            $rOutput = fopen($rOutputDir, 'wb');
            stream_copy_to_stream($rData, $rOutput);
            fclose($rData);
            fclose($rOutput);

            echo 'Run python update.py' . "\n";
            $ipTV_db->query('UPDATE `servers` SET `status` = 5 WHERE `id` = ?;', SERVER_ID);
            $rCommand = 'sudo /usr/bin/python3 ' . MAIN_DIR . 'update.py > /dev/null 2>&1 &';
            shell_exec($rCommand);
            exit(1);

        case 'post-update':
            if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
                foreach (ipTV_lib::$Servers as $rServer) {
                    if ($rServer['enabled'] && $rServer['status'] == 1 && time() - $rServer['last_check_ago'] <= 180 || !$rServer['is_main']) {
                        $ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServer['id'], time(), json_encode(array('action' => 'update')));
                    }
                }
            }
            $ipTV_db->query('UPDATE `servers` SET `status` = 1, `script_version` = ? WHERE `id` = ?;', SCRIPT_VERSION, SERVER_ID);

            // // remove old script
            // if (!ipTV_lib::$Servers[SERVER_ID]['is_main']) {
            //     if (file_exists('/test')) {
            //         unlink('/test');
            //     }
            // }

            exec('sudo ' . PHP_BIN . ' ' . CLI_PATH . '/update_bd.php');
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
    $tags = json_decode(file_get_contents($URLTagsRelease, false, $context), true);

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
function get_recent_stable_release(string $url) {
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    // Execute cURL request
    $result = curl_exec($ch);

    if ($result === false) {
        error_log("cURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Get the effective URL after following redirects
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    // Close cURL session
    curl_close($ch);

    // Extract the version from the URL
    $version = basename($effective_url);

    if (empty($version)) {
        error_log("Error: Could not extract version from URL");
        return false;
    }

    return $version;
}

function shutdown() {
    global $ipTV_db;
    if (!is_object($ipTV_db)) {
    } else {
        $ipTV_db->close_mysql();
    }
}
