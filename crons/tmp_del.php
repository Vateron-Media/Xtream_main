<?php

if (@$argc) {
    require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
    cli_set_process_title('XtreamCodes[TMP Cleaner]');
    $unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
    KillProcessCmd($unique_id);
    foreach (scandir(PLAYLIST_PATH) as $rFile) {
        if (0 < time() - filemtime(PLAYLIST_PATH . $rFile)) {
            unlink(PLAYLIST_PATH . $rFile);
        }
    }
    foreach (array(TMP_DIR, DIVERGENCE_TMP_PATH, FLOOD_TMP_PATH, STALKER_TMP_PATH) as $rTmpPath) {
        foreach (scandir($rTmpPath) as $rFile) {
            if (600 <= time() - filemtime($rTmpPath . $rFile) && stripos($rFile, 'ministra_') === false) {
                unlink($rTmpPath . $rFile);
            }
        }
    }
    $types = array('cloud_ips', 'new_rewrite', 'series_data.php');
    foreach (STREAM_TYPE as $connections) {
        $types[] = $connections . '_main.php';
    }
    if ($handle = opendir(TMP_DIR)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && is_file(TMP_DIR . $file) && !in_array($file, $types)) {
                if (800 <= time() - filemtime(TMP_DIR . $file)) {
                    unlink(TMP_DIR . $file);
                }
            }
        }
        closedir($handle);
    }
    clearstatcache();
} else {
    exit(0);
}
