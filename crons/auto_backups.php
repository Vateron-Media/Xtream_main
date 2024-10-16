<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    include "/home/xtreamcodes/admin/functions.php";
    $bDoBackup = false;
    if (isset($rAdminSettings['automatic_backups']) && !empty($rAdminSettings['automatic_backups'])) {
        if ($rAdminSettings['automatic_backups'] == 'hourly' && $rAdminSettings['automatic_backups_check'] < (time() - 3600)) {
            $bDoBackup = true;
        } elseif ($rAdminSettings['automatic_backups'] == 'daily' && $rAdminSettings['automatic_backups_check'] < (time() - 86400)) {
            $bDoBackup = true;
        } elseif ($rAdminSettings['automatic_backups'] == 'weekly' && $rAdminSettings['automatic_backups_check'] < (time() - 604800)) {
            $bDoBackup = true;
        } elseif ($rAdminSettings['automatic_backups'] == 'monthly' && $rAdminSettings['automatic_backups_check'] < (time() - 2592000)) {
            $bDoBackup = true;
        }
    }
    if ($bDoBackup) {
        $rFilename = MAIN_DIR . "adtools/backups/backup_" . date("Y-m-d_H:i:s") . ".sql";
        $rCommand = "mysqldump -u " . $_INFO['username'] . " -p" . $_INFO['password'] . " -P " . $_INFO['port'] . " " . $_INFO['database'] . " --ignore-table=xtream_iptvpro.user_activity --ignore-table=xtream_iptvpro.stream_logs --ignore-table=xtream_iptvpro.panel_logs --ignore-table=xtream_iptvpro.client_logs --ignore-table=xtream_iptvpro.epg_data > \"" . $rFilename . "\"";
        $rRet = shell_exec($rCommand);
        if (file_exists($rFilename)) {
            $rAdminSettings['automatic_backups_check'] = time();
            writeAdminSettings();
            $rBackups = getBackups();
            if ((count($rBackups) > intval($rAdminSettings["backups_to_keep"])) && (intval($rAdminSettings["backups_to_keep"]) > 0)) {
                $rDelete = array_slice($rBackups, 0, count($rBackups) - intval($rAdminSettings["backups_to_keep"]));
                foreach ($rDelete as $rItem) {
                    ipTV_lib::unlink_file(MAIN_DIR . "adtools/backups/" . $rItem["filename"]);
                }
            }
        }
    }
} else {
    exit('Please run as XtreamCodes!' . "\n");
}
