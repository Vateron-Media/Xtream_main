<?php
if ($argc) {
    set_time_limit(0);
    require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
    cli_set_process_title('XtreamCodes[TMP Cleaner]');
    $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
    ipTV_lib::check_cron($unique_id);
    foreach (array(TMP_PATH, CRONS_TMP_PATH, DIVERGENCE_TMP_PATH, FLOOD_TMP_PATH, STALKER_TMP_PATH, LOGS_TMP_PATH) as $tmpPath) {
        foreach (scandir($tmpPath) as $file) {
            if (file_exists($tmpPath . $file)) {
                if (600 <= time() - filemtime($tmpPath . $file) && stripos($file, 'stalker_') === false) {
                    if (is_file($tmpPath . $file)) {
                        unlink($tmpPath . $file);
                    }
                }
            }
        }
    }
    foreach (scandir(PLAYLIST_PATH) as $file) {
        if (CACHE_PLAYLIST < time() - filemtime(PLAYLIST_PATH . $file)) {
            if (is_file(PLAYLIST_PATH . $file)) {
                unlink(PLAYLIST_PATH . $file);
            }
        }
    }
    clearstatcache();
    @unlink($unique_id);
} else {
    exit(0);
}
