<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    set_time_limit(0);
    if ($argc) {
        require str_replace('\\', '/', dirname($argv[0])) . '/../includes/admin.php';
        cli_set_process_title('XtreamCodes[Series]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::checkCron($unique_id);
        loadCron();
        @unlink($unique_id);
    } else {
        exit(0);
    }
} else {
    exit('Please run as XtreamCodes!' . "\n");
}
function loadCron() {
    global $ipTV_db_admin;
    if (time() - ipTV_lib::$settings['cc_time'] < 3600) {
        exit();
    }
    ipTV_lib::setSettings(["cc_time" => intval(time())]);

    $ipTV_db_admin->query('SELECT `id`, `stream_display_name`, `series_no`, `stream_source` FROM `streams` WHERE `type` = 3 AND `series_no` <> 0;');
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $rRow) {
            $rPlaylist = generateSeriesPlaylist(intval($rRow['series_no']));
            if ($rPlaylist['success']) {
                $rSourceArray = json_decode($rRow['stream_source'], true);
                $rUpdate = false;
                foreach ($rPlaylist['sources'] as $rSource) {
                    if (!in_array($rSource, $rSourceArray)) {
                        $rUpdate = true;
                    }
                }
                if ($rUpdate) {
                    $ipTV_db_admin->query('UPDATE `streams` SET `stream_source` = ? WHERE `id` = ?;', json_encode($rPlaylist['sources'], JSON_UNESCAPED_UNICODE), $rRow['id']);
                    echo 'Updated: ' . $rRow['stream_display_name'] . "\n";
                }
            }
        }
    }
    scanBouquets();
}
