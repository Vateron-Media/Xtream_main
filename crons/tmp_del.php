<?php

if (@$argc) {
    require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
    cli_set_process_title('XtreamCodes[TMP Cleaner]');
    $unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
    KillProcessCmd($unique_id);
    $types = array('cloud_ips', 'cache_x', 'new_rewrite', 'series_data.php', 'bouquets_cache.php', 'servers_cache.php', 'settings_cache.php', 'customisp_cache.php', 'uagents_cache.php');
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
