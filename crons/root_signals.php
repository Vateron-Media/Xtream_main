<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'root') {
    set_time_limit(0);
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::check_cron($unique_id);
        shell_exec("sudo kill -9 `ps -ef | grep 'XtreamCodesSignals' | grep -v grep | awk '{print \$2}'`;");
        cli_set_process_title('XtreamCodesSignals');
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as root!' . "\n");
}


function loadCron() {
    global $ipTV_db;
    if ($ipTV_db->query("SELECT `signal_id`, `custom_data` FROM `signals` WHERE `server_id` = '%s' AND `custom_data` <> '' AND `cache` = 0 ORDER BY signal_id ASC;", SERVER_ID)) {
        $rRows = $ipTV_db->get_rows();
        if (file_exists(TMP_PATH . 'crontab')) {
            exec('crontab -u xtreamcodes -l', $rCrons);
            $rCurrentCron = trim(implode("\n", $rCrons));
            $ipTV_db->query('SELECT * FROM `crontab` WHERE `enabled` = 1;');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rFullPath = CRON_PATH . $rRow['filename'];
                if (pathinfo($rFullPath, PATHINFO_EXTENSION) == 'php' && file_exists($rFullPath)) {
                    $rJobs[] = $rRow['time'] . ' ' . PHP_BIN . ' ' . $rFullPath . ' # XtreamCodes';
                }
            }
            $rActualCron = trim(implode("\n", $rJobs));
            if ($rCurrentCron != $rActualCron) {
                echo 'Updating Crons...' . "\n";
                unlink(TMP_PATH . 'crontab');
            }
        }
        if (file_exists(CONFIG_PATH . 'sysctl.on')) {
            if (strtoupper(substr(explode("\n", file_get_contents('/etc/sysctl.conf'))[0], 0, 9)) == '# XtreamCodes') {
            } else {
                echo 'Sysctl missing! Writing it.' . "\n";
                exec('sudo modprobe ip_conntrack');
                file_put_contents('/etc/sysctl.conf', implode(PHP_EOL, array('# XtreamCodes', '', 'net.core.somaxconn = 655350', 'net.ipv4.route.flush=1', 'net.ipv4.tcp_no_metrics_save=1', 'net.ipv4.tcp_moderate_rcvbuf = 1', 'fs.file-max = 6815744', 'fs.aio-max-nr = 6815744', 'fs.nr_open = 6815744', 'net.ipv4.ip_local_port_range = 1024 65000', 'net.ipv4.tcp_sack = 1', 'net.ipv4.tcp_rmem = 10000000 10000000 10000000', 'net.ipv4.tcp_wmem = 10000000 10000000 10000000', 'net.ipv4.tcp_mem = 10000000 10000000 10000000', 'net.core.rmem_max = 524287', 'net.core.wmem_max = 524287', 'net.core.rmem_default = 524287', 'net.core.wmem_default = 524287', 'net.core.optmem_max = 524287', 'net.core.netdev_max_backlog = 300000', 'net.ipv4.tcp_max_syn_backlog = 300000', 'net.netfilter.nf_conntrack_max=1215196608', 'net.ipv4.tcp_window_scaling = 1', 'vm.max_map_count = 655300', 'net.ipv4.tcp_max_tw_buckets = 50000', 'net.ipv6.conf.all.disable_ipv6 = 1', 'net.ipv6.conf.default.disable_ipv6 = 1', 'net.ipv6.conf.lo.disable_ipv6 = 1', 'kernel.shmmax=134217728', 'kernel.shmall=134217728', 'vm.overcommit_memory = 1', 'net.ipv4.tcp_tw_reuse=1')));
                exec('sudo sysctl -p > /dev/null');
            }
        }
        if (count($rRows) > 0) {
            foreach ($rRows as $rRow) {
                $rData = json_decode($rRow['custom_data'], true);
                if ($rRow['signal_id']) {
                    $ipTV_db->query('DELETE FROM `signals` WHERE `signal_id` = \'%s\';', $rRow['signal_id']);
                }
                switch ($rData['action']) {
                    case 'reboot':
                        echo 'Rebooting system...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES('%s', 'REBOOT', 'System rebooted on request.', 'root', 'localhost', NULL, '%s');", SERVER_ID, time());
                        $ipTV_db->close_mysql();
                        shell_exec('sudo reboot');
                        break;
                    case 'restart_services':
                        echo 'Restarting services...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES('%s', 'RESTART', 'XtreamCodes services restarted on request.', 'root', 'localhost', NULL, '%s');", SERVER_ID, time());
                        shell_exec('sudo systemctl stop xtreamcodes');
                        shell_exec('sudo systemctl start xtreamcodes');
                        break;
                    case 'reload_nginx':
                        echo 'Reloading nginx...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES('%s', 'RELOAD', 'NGINX services reloaded on request.', 'root', 'localhost', NULL, '%s');", SERVER_ID, time());
                        shell_exec('sudo ' . BIN_PATH . 'nginx_rtmp/sbin/nginx_rtmp -s reload');
                        shell_exec('sudo ' . BIN_PATH . 'nginx/sbin/nginx -s reload');
                        break;
                    case 'update':
                        echo 'Updating...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES('%s', 'UPDATE', 'Updating XtreamCodes...', 'root', 'localhost', NULL, '%s');", SERVER_ID, time());
                        shell_exec('sudo ' . PHP_BIN . ' ' . TOOLS_PATH . 'update.php "update" 2>&1 &');
                        break;
                    default:
                        break;
                }
            }
        }
        $ipTV_db->query('DELETE FROM `signals` WHERE LENGTH(`custom_data`) > 0 AND UNIX_TIMESTAMP() - `time` >= 86400;');
        $ipTV_db->close_mysql();
    } else {
        exit();
    }
}

function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
