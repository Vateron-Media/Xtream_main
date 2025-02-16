<?php
set_time_limit(0);
if ($argc && count($argv) == 3) {
    register_shutdown_function('shutdown');
    require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
    $rCommand = $argv[1];
    $updateVersion = $argv[2];
    logMessage("Received command: $rCommand, Update version: $updateVersion");
    loadcli();
} else {
    logMessage("Invalid arguments. Expected 2, received " . (count($argv) - 1));
    exit(0);
}

function loadcli() {
    global $ipTV_db, $rCommand, $updateVersion;

    switch ($rCommand) {
        case 'update':
            logMessage("Starting update process...");

            $nextVersion = getNextVersionUpdate(SCRIPT_VERSION, $updateVersion);
            if (!$nextVersion) {
                logMessage("No newer version available. Exiting.");
                exit(1);
            }

            $URL = "https://github.com/Vateron-Media/Xtream_main/releases/download/{$nextVersion}/update.tar.gz";
            logMessage("Downloading update from: $URL");

            $rData = fopen($URL, 'rb');
            if (!$rData) {
                logMessage("Failed to open update URL");
                exit(1);
            }

            $rOutputDir = TMP_PATH . 'update.tar.gz';
            $rOutput = fopen($rOutputDir, 'wb');
            if (!$rOutput) {
                logMessage("Failed to open output file: $rOutputDir");
                fclose($rData);
                exit(1);
            }

            stream_copy_to_stream($rData, $rOutput);
            fclose($rData);
            fclose($rOutput);
            logMessage("Update downloaded successfully.");

            logMessage("Setting server status to updating...");
            $ipTV_db->query('UPDATE `servers` SET `status` = 5 WHERE `id` = ?;', SERVER_ID);

            logMessage("Passing python control to a script...");
            $rCommand = 'sudo /usr/bin/python3 ' . MAIN_DIR . 'update.py > /dev/null 2>&1 &';
            shell_exec($rCommand);
            logMessage("Update script executed. Exiting.");
            exit(1);

        case 'post-update':
            logMessage("Starting post-update process...");

            if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
                logMessage("Server is main, sending update signals to other servers.");
                foreach (ipTV_lib::$Servers as $rServer) {
                    if ($rServer['enabled'] && $rServer['status'] == 1 && time() - $rServer['last_check_ago'] <= 180 && !$rServer['is_main']) {
                        logMessage("Sending update signal to server ID: " . $rServer['id']);
                        $ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServer['id'], time(), json_encode(array('action' => 'update')));
                    }
                }
            }

            logMessage("Updating server status to active and setting script version.");
            $ipTV_db->query('UPDATE `servers` SET `status` = 1, `script_version` = ? WHERE `id` = ?;', SCRIPT_VERSION, SERVER_ID);

            logMessage("Executing database update script.");
            exec('sudo ' . PHP_BIN . ' ' . CLI_PATH . '/update_bd.php');

            logMessage("Executing server status script.");
            exec('sudo ' . MAIN_DIR . 'status');

            logMessage("Post-update process completed.");
            break;

        default:
            logMessage("Unknown command: $rCommand");
            break;
    }
}

function getNextVersionUpdate($curentVersion, $updateVersion) {
    logMessage("Checking for next available version. Current: v{$curentVersion}, Target: v{$updateVersion}");

    $context = stream_context_create([
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        ]
    ]);

    $URLTagsRelease = "https://api.github.com/repos/Vateron-Media/Xtream_main/git/refs/tags";
    $tags = json_decode(file_get_contents($URLTagsRelease, false, $context), true);

    if (!$tags) {
        logMessage("Failed to fetch version tags from GitHub.");
        return false;
    }

    $versions = [];
    foreach ($tags as $value) {
        $latestTag = str_replace("refs/tags/", "", $value['ref']);
        $versions[] = $latestTag;
    }

    $CurentKey = array_search("v{$curentVersion}", $versions);
    $UpdKey = array_search("v{$updateVersion}", $versions);

    if ($CurentKey !== false && $UpdKey !== false && $CurentKey < $UpdKey) {
        logMessage("Next version found: " . $versions[$CurentKey + 1]);
        return $versions[$CurentKey + 1];
    }

    logMessage("No newer version found.");
    return false;
}

function shutdown() {
    global $ipTV_db;
    logMessage("Shutting down script...");

    if (is_object($ipTV_db)) {
        logMessage("Closing database connection.");
        $ipTV_db->close_mysql();
    }

    logMessage("Script shutdown complete.");
}

function logMessage($message) {
    //file_put_contents(LOGS_TMP_PATH . 'update.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
    print($message);
}
