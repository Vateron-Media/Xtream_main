<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        set_time_limit(0);
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title('XC_VM[TMP]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::checkCron($unique_id);
        foreach (array(TMP_PATH, CRONS_TMP_PATH, DIVERGENCE_TMP_PATH, FLOOD_TMP_PATH, STALKER_TMP_PATH, SIGNALS_TMP_PATH, LOGS_TMP_PATH) as $tmpPath) {
            foreach (scandir($tmpPath) as $file) {
                if (600 <= time() - filemtime($tmpPath . $file) && stripos($file, 'stalker_') === false) {
                    if (is_file($tmpPath . $file)) {
                        unlink($tmpPath . $file);
                    }
                }
            }
        }
        foreach (scandir(PLAYLIST_PATH) as $file) {
            if (ipTV_lib::$settings['cache_playlists'] < time() - filemtime(PLAYLIST_PATH . $file)) {
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
} else {
    exit('Please run as XC_VM!' . "\n");
}
