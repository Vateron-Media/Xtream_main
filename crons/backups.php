<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(32757);
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    set_time_limit(0);
    if ($argc) {
        require str_replace('\\', '/', dirname($argv[0])) . '/../includes/admin.php';
        if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
            cli_set_process_title('XC_VM[Backups]');
            $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
            ipTV_lib::checkCron($unique_id);
            $rForce = false;
            if (count($argv) > 1) {
                if (intval($argv[1]) == 1) {
                    $rForce = true;
                }
            }
            $rBackups = ipTV_lib::$settings['automatic_backups'];
            $rLastBackup = intval(ipTV_lib::$settings['last_backup']);
            $rPeriod = array('hourly' => 3600, 'daily' => 86400, 'weekly' => 604800, 'monthly' => 2419200);
            if (!$rForce) {
                $rPID = getmypid();
                if (file_exists('/proc/' . ipTV_lib::$settings['backups_pid']) && 0 < strlen(ipTV_lib::$settings['backups_pid'])) {
                    exit();
                }
                ipTV_lib::setSettings(["backups_pid" => $rPID]);
            }

            if (isset($rBackups) && $rBackups != 'off' || $rForce) {
                if ($rLastBackup + $rPeriod[$rBackups] <= time() || $rForce) {
                    if (!$rForce) {
                        ipTV_lib::setSettings(["last_backup" => time()]);
                    }
                    $ipTV_db_admin->close_mysql();

                    $rFilename = MAIN_DIR . 'backups/backup_' . date('Y-m-d_H:i:s') . '.sql';
                    $rCommand = "mysqldump -u " . $_INFO['username'] . " -p" . $_INFO['password'] . " -P " . $_INFO['port'] . " " . $_INFO['database'] . " --ignore-table=xc_vm.user_activity --ignore-table=xc_vm.stream_logs --ignore-table=xc_vm.panel_logs --ignore-table=xc_vm.client_logs --ignore-table=xc_vm.epg_data > \"" . $rFilename . "\"";
                    $rRet = shell_exec($rCommand);
                    if (filesize($rFilename) < 0) {
                        unlink($rFilename);
                    }
                }
            }
            $rBackups = getBackups();
            if ((count($rBackups) > intval(ipTV_lib::$settings['backups_to_keep'])) && (intval(ipTV_lib::$settings['backups_to_keep']) > 0)) {
                $rDelete = array_slice($rBackups, 0, count($rBackups) - intval(ipTV_lib::$settings['backups_to_keep']));
                foreach ($rDelete as $rItem) {
                    if (file_exists(MAIN_DIR . 'backups/' . $rItem['filename'])) {
                        unlink(MAIN_DIR . 'backups/' . $rItem['filename']);
                    }
                }
            }
            @unlink($unique_id);
        } else {
            exit('Please run on main server.');
        }
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
