<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'root') {
    set_time_limit(0);
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::checkCron($unique_id);
        shell_exec("sudo kill -9 `ps -ef | grep 'XC_VM[Signals]' | grep -v grep | awk '{print \$2}'`;");
        cli_set_process_title('XC_VM[Signals]');
        file_put_contents(CONFIG_PATH . 'signals.last', time());
        $rSaveIPTables = false;
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as root!' . "\n");
}
function loadCron() {
    global $ipTV_db;
    global $rSaveIPTables;
    ipTV_lib::$Servers = ipTV_lib::getServers(true);
    $ipTV_db->query("SELECT `signal_id` FROM `signals` WHERE `server_id` = ? AND `custom_data` = '{\"action\":\"flush\"}' AND `cache` = 0;", SERVER_ID);
    if (0 < $ipTV_db->num_rows()) {
        echo "Flushing IP's...";
        flushIPs();
        saveiptables();
        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'FLUSH', 'Flushed blocked IP\\'s from iptables.', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
        $ipTV_db->query("DELETE FROM `signals` WHERE `server_id` = ? AND `custom_data` = '{\"action\":\"flush\"}' AND `cache` = 0;", SERVER_ID);
    } else {
        $rActualBlocked = getBlockedIPs();
        $rActualBlockedFlip = array_flip($rActualBlocked);
        $ipTV_db->query('SELECT `ip` FROM `blocked_ips`;');
        $rBlocked = array_keys($ipTV_db->get_rows(true, 'ip'));
        $rBlockedFlip = array_flip($rBlocked);
        $rAdd = $rDel = array();
        foreach (array_count_values($rActualBlocked) as $rIP => $rCount) {
            if ($rCount > 1) {
                echo $rCount . "\n";
                foreach (range(1, $rCount - 1) as $i) {
                    $rDel[] = $rIP;
                }
            }
        }
        foreach ($rBlocked as $rIP) {
            if (!isset($rActualBlockedFlip[$rIP])) {
                $rAdd[] = $rIP;
            }
        }
        foreach ($rActualBlocked as $rIP) {
            if (!isset($rBlockedFlip[$rIP])) {
                $rDel[] = $rIP;
            }
        }
        if (count($rDel) > 0) {
            $rSaveIPTables = true;
            foreach ($rDel as $rIP) {
                echo 'Unblock IP: ' . $rIP . "\n";
                unblockip($rIP);
            }
        }
        if (count($rAdd) > 0) {
            $rSaveIPTables = true;
            foreach ($rAdd as $rIP) {
                echo 'Block IP: ' . $rIP . "\n";
                blockip($rIP);
            }
        }
        if ($rSaveIPTables) {
            saveiptables();
            $rSaveIPTables = false;
        }
    }
    $rReload = false;
    $rAllowedIPs = ipTV_lib::getAllowedIPs();
    $XtreamList = array();
    foreach ($rAllowedIPs as $rIP) {
        if (!empty($rIP) || filter_var($rIP, FILTER_VALIDATE_IP) || !in_array('set_real_ip_from ' . $rIP . ';', $XtreamList)) {
        } else {
            $XtreamList[] = 'set_real_ip_from ' . $rIP . ';';
        }
    }
    $XtreamList = trim(implode("\n", array_unique($XtreamList)));
    $rCurrentList = (trim(file_get_contents(BIN_PATH . 'nginx/conf/realip_xtream.conf')) ?: '');
    if ($XtreamList != $rCurrentList) {
        echo 'Updating Xtream IP List...' . "\n";
        file_put_contents(BIN_PATH . 'nginx/conf/realip_xtream.conf', $XtreamList);
        $rReload = true;
    }
    $rCurrentList = (trim(file_get_contents(BIN_PATH . 'nginx/conf/realip_cloudflare.conf')) ?: '');
    if (ipTV_lib::$settings['cloudflare']) {
        if (empty($rCurrentList)) {
            echo 'Enabling Cloudflare...' . "\n";
            file_put_contents(BIN_PATH . 'nginx/conf/realip_cloudflare.conf', 'set_real_ip_from 103.21.244.0/22;' . "\n" . 'set_real_ip_from 103.22.200.0/22;' . "\n" . 'set_real_ip_from 103.31.4.0/22;' . "\n" . 'set_real_ip_from 104.16.0.0/13;' . "\n" . 'set_real_ip_from 104.24.0.0/14;' . "\n" . 'set_real_ip_from 108.162.192.0/18;' . "\n" . 'set_real_ip_from 131.0.72.0/22;' . "\n" . 'set_real_ip_from 141.101.64.0/18;' . "\n" . 'set_real_ip_from 162.158.0.0/15;' . "\n" . 'set_real_ip_from 172.64.0.0/13;' . "\n" . 'set_real_ip_from 173.245.48.0/20;' . "\n" . 'set_real_ip_from 188.114.96.0/20;' . "\n" . 'set_real_ip_from 190.93.240.0/20;' . "\n" . 'set_real_ip_from 197.234.240.0/22;' . "\n" . 'set_real_ip_from 198.41.128.0/17;' . "\n" . 'set_real_ip_from 2400:cb00::/32;' . "\n" . 'set_real_ip_from 2606:4700::/32;' . "\n" . 'set_real_ip_from 2803:f800::/32;' . "\n" . 'set_real_ip_from 2405:b500::/32;' . "\n" . 'set_real_ip_from 2405:8100::/32;' . "\n" . 'set_real_ip_from 2c0f:f248::/32;' . "\n" . 'set_real_ip_from 2a06:98c0::/29;');
            $rReload = true;
        }
    } else {
        if (!empty($rCurrentList)) {
            echo 'Disabling Cloudflare...' . "\n";
            file_put_contents(BIN_PATH . 'nginx/conf/realip_cloudflare.conf', '');
            $rReload = true;
        }
    }
    if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
        $rCurrentStatus = stripos((trim(file_get_contents(BIN_PATH . 'nginx/conf/gzip.conf')) ?: 'gzip off'), 'gzip on') !== false;
        if (ipTV_lib::$Servers[SERVER_ID]['enable_gzip']) {
            if (!$rCurrentStatus) {
                echo 'Enabling GZIP...' . "\n";
                file_put_contents(BIN_PATH . 'nginx/conf/gzip.conf', 'gzip on;' . "\n" . 'gzip_min_length 1000;' . "\n" . 'gzip_buffers 4 32k;' . "\n" . 'gzip_proxied any;' . "\n" . 'gzip_types application/json application/xml;' . "\n" . 'gzip_vary on;' . "\n" . 'gzip_disable "MSIE [1-6].(?!.*SV1)";');
                $rReload = true;
            }
        } else {
            if ($rCurrentStatus) {
                echo 'Disabling GZIP...' . "\n";
                file_put_contents(BIN_PATH . 'nginx/conf/gzip.conf', 'gzip off;');
                $rReload = true;
            }
        }
    }
    if (0 < ipTV_lib::$Servers[SERVER_ID]['limit_requests']) {
        $rLimitConf = 'limit_req_zone global zone=two:10m rate=' . intval(ipTV_lib::$Servers[SERVER_ID]['limit_requests']) . 'r/s;';
    } else {
        $rLimitConf = '';
    }
    $rCurrentConf = (trim(file_get_contents(BIN_PATH . 'nginx/conf/limit.conf')) ?: '');
    if ($rLimitConf != $rCurrentConf) {
        echo 'Updating rate limit...' . "\n";
        file_put_contents(BIN_PATH . 'nginx/conf/limit.conf', $rLimitConf);
        $rReload = true;
    }
    if (0 < ipTV_lib::$Servers[SERVER_ID]['limit_requests']) {
        $rLimitConf = 'limit_req zone=two burst=' . intval(ipTV_lib::$Servers[SERVER_ID]['limit_burst']) . ';';
    } else {
        $rLimitConf = '';
    }
    $rCurrentConf = (trim(file_get_contents(BIN_PATH . 'nginx/conf/limit_queue.conf')) ?: '');
    if ($rLimitConf != $rCurrentConf) {
        echo 'Updating rate limit queue...' . "\n";
        file_put_contents(BIN_PATH . 'nginx/conf/limit_queue.conf', $rLimitConf);
        $rReload = true;
    }
    if ($rReload) {
        shell_exec('sudo ' . BIN_PATH . 'nginx/sbin/nginx -s reload');
    }
    if (ipTV_lib::$settings['restart_php_fpm']) {
        $rPHP = $rNginx = 0;
        exec('ps -fp $(pgrep -u xc_vm)', $rOutput, $rReturnVar);
        foreach ($rOutput as $rProcess) {
            $rSplit = explode(' ', preg_replace('!\\s+!', ' ', trim($rProcess)));
            if ($rSplit[8] == 'php-fpm:' && $rSplit[9] == 'master') {
                $rPHP++;
            }
            if ($rSplit[8] == 'nginx:' && $rSplit[9] == 'master') {
                $rNginx++;
            }
        }
        if ($rNginx > 0) {
            if ($rPHP == 0) {
                echo 'PHP-FPM ERROR - Restarting...';
                $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'PHP-FPM', 'Restarted PHP-FPM instances due to a suspected crash.', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
                shell_exec('sudo systemctl stop xc_vm');
                shell_exec('sudo systemctl start xc_vm');
                exit();
            }
        }
        $rHandle = curl_init('http://127.0.0.1:' . ipTV_lib::$Servers[SERVER_ID]['http_broadcast_port'] . '/init');
        curl_setopt($rHandle, CURLOPT_RETURNTRANSFER, true);
        $rResponse = curl_exec($rHandle);
        $rCode = curl_getinfo($rHandle, CURLINFO_HTTP_CODE);
        if (!in_array($rCode, array(500, 502))) {
            curl_close($rHandle);
        } else {
            echo $rCode . ' ERROR - Restarting...';
            $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'PHP-FPM', 'Restarted services due to " . $rCode . " error.', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
            shell_exec('sudo systemctl stop xc_vm');
            shell_exec('sudo systemctl start xc_vm');
            exit();
        }
    }
    if ($ipTV_db->query("SELECT `signal_id`, `custom_data` FROM `signals` WHERE `server_id` = ? AND `custom_data` <> '' AND `cache` = 0 ORDER BY signal_id ASC;", SERVER_ID)) {
        $rRows = $ipTV_db->get_rows();
        $rCheck = array('mag' => true, 'php' => true, 'services' => true, 'ports' => true, 'ramdisk' => true);
        foreach ($rRows as $rRow) {
            $rData = json_decode($rRow['custom_data'], true);
            switch ($rData['action']) {
                case 'disable_ramdisk':
                case 'enable_ramdisk':
                    $rCheck['ramdisk'] = false;
                    break;
                    // case 'enable_ministra':
                    // case 'disable_ministra':
                    //     $rCheck['mag'] = false;
                    //     break;
                case 'set_services':
                    $rCheck['services'] = false;
                    break;
                case 'set_port':
                    $rCheck['ports'] = false;
                    break;
            }
        }
        // if ($rCheck['mag']) {
        //     if (ipTV_lib::$settings['mag_legacy_redirect']) {
        //         if (!file_exists(MAIN_DIR . 'wwwdir/c')) {
        //             array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'enable_ministra'))));
        //         }
        //     } else {
        //         if (file_exists(MAIN_DIR . 'wwwdir/c')) {
        //             array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'disable_ministra'))));
        //         }
        //     }
        // }

        if ($rCheck['services']) {
            $rCurServices = 0;
            $rStartScript = explode("\n", file_get_contents(MAIN_DIR . 'bin/daemons.sh'));
            foreach ($rStartScript as $rLine) {
                if (explode(' ', $rLine)[0] == 'start-stop-daemon') {
                    $rCurServices++;
                }
            }
            if (ipTV_lib::$Servers[SERVER_ID]['total_services'] != $rCurServices) {
                array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'set_services', 'count' => ipTV_lib::$Servers[SERVER_ID]['total_services'], 'reload' => true))));
            }
        }
        // if ($rCheck['ports']) {
        //     $rListen = $rPorts = array('http' => array(), 'https' => array());
        //     foreach (array_merge(array(intval(ipTV_lib::$Servers[SERVER_ID]['http_broadcast_port'])), explode(',', ipTV_lib::$Servers[SERVER_ID]['http_ports_add'])) as $rPort) {
        //         if (is_numeric($rPort) && 0 < $rPort && $rPort <= 65535) {
        //             $rListen['http'][] = 'listen ' . intval($rPort) . ';';
        //             $rPorts['http'][] = intval($rPort);
        //         }
        //     }
        //     foreach (array_merge(array(intval(ipTV_lib::$Servers[SERVER_ID]['https_broadcast_port'])), explode(',', ipTV_lib::$Servers[SERVER_ID]['https_ports_add'])) as $rPort) {
        //         if (is_numeric($rPort) && 0 < $rPort && $rPort <= 65535) {
        //             $rListen['https'][] = 'listen ' . intval($rPort) . ' ssl;';
        //             $rPorts['https'][] = intval($rPort);
        //         }
        //     }
        //     if (trim(implode(' ', $rListen['http'])) != trim(file_get_contents(MAIN_DIR . 'bin/nginx/conf/ports/http.conf'))) {
        //         array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'set_port', 'type' => 0, 'ports' => $rPorts['http'], 'reload' => true))));
        //     }
        //     if (trim(implode(' ', $rListen['https'])) != trim(file_get_contents(MAIN_DIR . 'bin/nginx/conf/ports/https.conf'))) {
        //         array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'set_port', 'type' => 1, 'ports' => $rPorts['https'], 'reload' => true))));
        //     }
        //     if ('listen ' . intval(ipTV_lib::$Servers[SERVER_ID]['rtmp_port']) . ';' != trim(file_get_contents(MAIN_DIR . 'bin/nginx_rtmp/conf/port.conf'))) {
        //         array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'set_port', 'type' => 2, 'ports' => array(intval(ipTV_lib::$Servers[SERVER_ID]['rtmp_port'])), 'reload' => true))));
        //     }
        // }
        // if ($rCheck['ramdisk']) {
        //     $rMounted = false;
        //     exec('df -h', $rLines);
        //     array_shift($rLines);
        //     foreach ($rLines as $rLine) {
        //         $rSplit = explode(' ', preg_replace('!\\s+!', ' ', trim($rLine)));
        //         if (implode(' ', array_slice($rSplit, 5, count($rSplit) - 5)) == rtrim(STREAMS_PATH, '/')) {
        //             $rMounted = true;
        //             break;
        //         }
        //     }
        // if (ipTV_lib::$Servers[SERVER_ID]['use_disk']) {
        //     if ($rMounted) {
        //         array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'disable_ramdisk'))));
        //     }
        // } else {
        //     if (!$rMounted) {
        //         array_unshift($rRows, array('custom_data' => json_encode(array('action' => 'enable_ramdisk'))));
        //     }
        // }
        // }
        if (file_exists(TMP_PATH . 'crontab')) {
            exec('crontab -u xc_vm -l', $rCrons);
            $rCurrentCron = trim(implode("\n", $rCrons));
            $ipTV_db->query('SELECT * FROM `crontab` WHERE `enabled` = 1;');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rFullPath = CRON_PATH . $rRow['filename'];
                if (pathinfo($rFullPath, PATHINFO_EXTENSION) == 'php' && file_exists($rFullPath)) {
                    $rJobs[] = $rRow['time'] . ' ' . PHP_BIN . ' ' . $rFullPath . ' # XC_VM';
                }
            }
            $rActualCron = trim(implode("\n", $rJobs));
            if ($rCurrentCron != $rActualCron) {
                echo 'Updating Crons...' . "\n";
                unlink(TMP_PATH . 'crontab');
            }
        }
        if (file_exists(CONFIG_PATH . 'sysctl.on')) {
            if (strtoupper(substr(explode("\n", file_get_contents('/etc/sysctl.conf'))[0], 0, 9)) != '# XC_VM') {
                echo 'Sysctl missing! Writing it.' . "\n";
                exec('sudo modprobe ip_conntrack');
                file_put_contents('/etc/sysctl.conf', implode(PHP_EOL, array('# XC_VM', '', 'net.core.somaxconn = 655350', 'net.ipv4.route.flush=1', 'net.ipv4.tcp_no_metrics_save=1', 'net.ipv4.tcp_moderate_rcvbuf = 1', 'fs.file-max = 6815744', 'fs.aio-max-nr = 6815744', 'fs.nr_open = 6815744', 'net.ipv4.ip_local_port_range = 1024 65000', 'net.ipv4.tcp_sack = 1', 'net.ipv4.tcp_rmem = 10000000 10000000 10000000', 'net.ipv4.tcp_wmem = 10000000 10000000 10000000', 'net.ipv4.tcp_mem = 10000000 10000000 10000000', 'net.core.rmem_max = 524287', 'net.core.wmem_max = 524287', 'net.core.rmem_default = 524287', 'net.core.wmem_default = 524287', 'net.core.optmem_max = 524287', 'net.core.netdev_max_backlog = 300000', 'net.ipv4.tcp_max_syn_backlog = 300000', 'net.netfilter.nf_conntrack_max=1215196608', 'net.ipv4.tcp_window_scaling = 1', 'vm.max_map_count = 655300', 'net.ipv4.tcp_max_tw_buckets = 50000', 'net.ipv6.conf.all.disable_ipv6 = 1', 'net.ipv6.conf.default.disable_ipv6 = 1', 'net.ipv6.conf.lo.disable_ipv6 = 1', 'kernel.shmmax=134217728', 'kernel.shmall=134217728', 'vm.overcommit_memory = 1', 'net.ipv4.tcp_tw_reuse=1')));
                exec('sudo sysctl -p > /dev/null');
            }
        }
        if (count($rRows) > 0) {
            foreach ($rRows as $rRow) {
                $rData = json_decode($rRow['custom_data'], true);
                if ($rRow['signal_id']) {
                    $ipTV_db->query('DELETE FROM `signals` WHERE `signal_id` = ?;', $rRow['signal_id']);
                }
                switch ($rData['action']) {
                    case 'reboot':
                        echo 'Rebooting system...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'REBOOT', 'System rebooted on request.', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
                        $ipTV_db->close_mysql();
                        shell_exec('sudo reboot');
                        break;
                    case 'restart_services':
                        echo 'Restarting services...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'RESTART', 'XC_VM services restarted on request.', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
                        shell_exec('sudo systemctl stop xc_vm');
                        shell_exec('sudo systemctl start xc_vm');
                        break;
                    case 'stop_services':
                        echo 'Stopping services...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'STOP', 'XC_VM services stopped on request.', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
                        shell_exec('sudo systemctl stop xc_vm');
                        break;
                    case 'reload_nginx':
                        echo 'Reloading nginx...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'RELOAD', 'NGINX services reloaded on request.', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
                        shell_exec('sudo ' . BIN_PATH . 'nginx_rtmp/sbin/nginx_rtmp -s reload');
                        shell_exec('sudo ' . BIN_PATH . 'nginx/sbin/nginx -s reload');
                        break;
                    case 'disable_ramdisk':
                        echo 'Disabling ramdisk...' . "\n";
                        $rFstab = file_get_contents('/etc/fstab');
                        $rOutput = array();
                        foreach (explode("\n", $rFstab) as $rLine) {
                            if (substr($rLine, 0, 31) != 'tmpfs /home/xc_vm/content/streams') {
                            } else {
                                $rLine = '#' . $rLine;
                            }
                            $rOutput[] = $rLine;
                        }
                        file_put_contents('/etc/fstab', implode("\n", $rOutput));
                        shell_exec('sudo umount -l ' . STREAMS_PATH);
                        shell_exec('sudo chown -R xc_vm:xc_vm ' . STREAMS_PATH);
                        break;
                    case 'enable_ramdisk':
                        echo 'Enabling ramdisk...' . "\n";
                        $rFstab = file_get_contents('/etc/fstab');
                        $rOutput = array();
                        foreach (explode("\n", $rFstab) as $rLine) {
                            if (substr($rLine, 0, 32) != '#tmpfs /home/xc_vm/content/streams') {
                            } else {
                                $rLine = ltrim($rLine, '#');
                            }
                            $rOutput[] = $rLine;
                        }
                        file_put_contents('/etc/fstab', implode("\n", $rOutput));
                        shell_exec('sudo mount ' . STREAMS_PATH);
                        shell_exec('sudo chown -R xc_vm:xc_vm ' . STREAMS_PATH);
                        break;
                        // case 'update_binaries':
                        //     echo 'Updating binaries...' . "\n";
                        //     $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'BINARIES', 'Updating XC_VM binaries from XC_VMr...', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
                        //     shell_exec('sudo ' . PHP_BIN . ' ' . CLI_PATH . 'binaries.php 2>&1 &');
                        //     break;
                    case 'update':
                        echo 'Updating...' . "\n";
                        $ipTV_db->query("INSERT INTO `mysql_syslog`(`server_id`, `type`, `error`, `username`, `ip`, `database`, `date`) VALUES(?, 'UPDATE', 'Updating XC_VM...', 'root', 'localhost', NULL, ?);", SERVER_ID, time());
                        shell_exec('sudo ' . PHP_BIN . ' ' . CLI_PATH . 'update.php "update" "' . $rData['version'] . '" 2>&1 &');
                        break;
                        // case 'enable_ministra':
                        //     echo 'Enabling ministra /c...';
                        //     shell_exec('sudo ln -sfn ' . MAIN_DIR . 'ministra ' . MAIN_DIR . 'www/c');
                        //     shell_exec('sudo ln -sfn ' . MAIN_DIR . 'ministra/portal.php ' . MAIN_DIR . 'www/portal.php');
                        //     break;
                        // case 'disable_ministra':
                        //     echo 'Disabling ministra /c...';
                        //     shell_exec('sudo rm ' . MAIN_DIR . 'www/c');
                        //     shell_exec('sudo rm ' . MAIN_DIR . 'www/portal.php');
                        //     break;
                    case 'set_services':
                        echo 'Setting PHP Services' . "\n";
                        $rServices = intval($rData['count']);
                        if ($rData['reload']) {
                            shell_exec('sudo systemctl stop xc_vm');
                        }
                        $rNewScript = "#! /bin/bash\n\n"
                            . "if pgrep -u xc_vm php-fpm8.4 > /dev/null; then\n"
                            . "    echo \"PHP-FPM is already running, stopping existing instances...\"\n"
                            . "    pkill -u xc_vm php-fpm8.4\n"
                            . "    sleep 2\n"
                            . "fi\n\n"
                            . "# Now start PHP-FPM instances\n";
                        $rNewBalance = 'upstream php {' . "\n" . '    least_conn;' . "\n";
                        $rTemplate = file_get_contents(MAIN_DIR . 'bin/install/php/fpm_pool_template');
                        foreach (range(1, $rServices) as $i) {
                            $rNewScript .= 'start-stop-daemon --start --quiet --pidfile ' . MAIN_DIR . 'bin/php_sockets/' . $i . '.pid --exec /usr/sbin/php-fpm8.4 -- --daemonize --fpm-config /etc/php/8.4/fpm/pool.d/' . $i . '.conf' . "\n";
                            $rNewBalance .= '    server unix:' . MAIN_DIR . 'bin/php_sockets/' . $i . '.sock;' . "\n";
                            file_put_contents('/etc/php/8.4/fpm/pool.d/' . $i . '.conf', str_replace('#PATH#', MAIN_DIR, str_replace('#ID#', $i, $rTemplate)));
                        }
                        file_put_contents(MAIN_DIR . 'bin/daemons.sh', $rNewScript);
                        file_put_contents(MAIN_DIR . 'bin/nginx/conf/balance.conf', $rNewBalance . '}');
                        shell_exec('sudo chmod 0771 ' . MAIN_DIR . 'bin/daemons.sh');
                        if ($rData['reload']) {
                            shell_exec('sudo systemctl start xc_vm');
                        }
                        break;
                    case 'set_sysctl':
                        $rNewConfig = $rData['data'];
                        if (!empty($rNewConfig)) {
                            $rSysCtl = file_get_contents('/etc/sysctl.conf');
                            if ($rSysCtl != $rNewConfig) {
                                shell_exec('sudo modprobe ip_conntrack > /dev/null');
                                file_put_contents('/etc/sysctl.conf', $rNewConfig);
                                shell_exec('sudo sysctl -p > /dev/null');
                                $ipTV_db->query('UPDATE `servers` SET `sysctl` = ? WHERE `id` = ?;', $rNewConfig, SERVER_ID);
                            }
                        }
                        break;
                    case 'set_port':
                        echo 'Setting NGINX Port' . "\n";
                        if (intval($rData['type']) == 0) {
                            $rListen = array();
                            $rPort = $rData['port'];

                            if (is_numeric($rPort) && 80 <= $rPort && $rPort <= 65535) {
                                $rListen[] = 'listen ' . intval($rPort) . ';';
                            }

                            file_put_contents(MAIN_DIR . 'bin/nginx/conf/ports/http.conf', implode(' ', $rListen));
                            file_put_contents(MAIN_DIR . 'bin/nginx_rtmp/conf/live.conf', 'on_play http://127.0.0.1:' . intval($rPort) . '/streaming/rtmp.php; on_publish http://127.0.0.1:' . intval($rPort) . '/streaming/rtmp.php; on_play_done http://127.0.0.1:' . intval($rPort) . '/streaming/rtmp.php;');
                            if ($rData['reload']) {
                                shell_exec('sudo ' . BIN_PATH . 'nginx/sbin/nginx -s reload');
                            }
                        } elseif (intval($rData['type']) == 1) {
                            $rListen = array();
                            $rPort = $rData['port'];

                            if (is_numeric($rPort) && 80 <= $rPort && $rPort <= 65535) {
                                $rListen[] = 'listen ' . intval($rPort) . ' ssl;';
                            }
                            file_put_contents(MAIN_DIR . 'bin/nginx/conf/ports/https.conf', implode(' ', $rListen));
                            if ($rData['reload']) {
                                shell_exec('sudo ' . BIN_PATH . 'nginx/sbin/nginx -s reload');
                            }
                        } else {
                            if (intval($rData['type']) == 2) {
                                $rPort = $rData['port'];

                                file_put_contents(MAIN_DIR . 'bin/nginx_rtmp/conf/port.conf', 'listen ' . intval($rPort) . ';');
                                if ($rData['reload']) {
                                    shell_exec('sudo ' . BIN_PATH . 'nginx_rtmp/sbin/nginx_rtmp -s reload');
                                }
                            }
                        }
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
/**
 * Retrieves a list of blocked IP addresses from iptables and ip6tables.
 *
 * This function checks the system's firewall rules and extracts all IPs
 * that have been explicitly blocked using the DROP rule.
 *
 * @return array An array of blocked IP addresses (both IPv4 and IPv6).
 */
function getBlockedIPs() {
    $blockedIPs = [];

    // Loop through both IPv4 and IPv6 firewall tables
    foreach (['iptables', 'ip6tables'] as $table) {
        $output = [];

        // Execute the command to list firewall rules
        exec("sudo $table -nL --line-numbers -t filter", $output);

        foreach ($output as $line) {
            // Normalize spaces and split the line into an array
            $columns = explode(' ', preg_replace('/\s+/', ' ', trim($line)));

            // Check if this line contains a DROP rule
            if (isset($columns[1]) && $columns[1] === 'DROP') {
                // IPv4 addresses are usually in column 4, IPv6 in column 3
                $ipIndex = ($table === 'iptables') ? 4 : 3;

                // Ensure the index exists before adding to the list
                if (isset($columns[$ipIndex])) {
                    $blockedIPs[] = $columns[$ipIndex];
                }
            }
        }
    }

    // Remove duplicates and empty values, then return the final list
    return array_values(array_unique(array_filter($blockedIPs)));
}

function blockip($rIP) {
    if (filter_var($rIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        exec('sudo iptables -I INPUT -s ' . escapeshellcmd($rIP) . ' -j DROP');
    } elseif (filter_var($rIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        exec('sudo ip6tables -I INPUT -s ' . escapeshellcmd($rIP) . ' -j DROP');
    }
    touch(FLOOD_TMP_PATH . 'block_' . $rIP);
}
function unblockip($rIP) {
    if (filter_var($rIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        exec('sudo iptables -D INPUT -s ' . escapeshellcmd($rIP) . ' -j DROP');
    } elseif (filter_var($rIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        exec('sudo ip6tables -D INPUT -s ' . escapeshellcmd($rIP) . ' -j DROP');
    }
    if (file_exists(FLOOD_TMP_PATH . 'block_' . $rIP)) {
        unlink(FLOOD_TMP_PATH . 'block_' . $rIP);
    }
}
function flushIPs() {
    exec('sudo iptables -F && sudo ip6tables -F');
    shell_exec('sudo rm ' . FLOOD_TMP_PATH . 'block_*');
}
function saveiptables() {
    exec('sudo iptables-save && sudo ip6tables-save');
}
function shutdown() {
    global $ipTV_db;
    global $unique_id;
    global $rSaveIPTables;
    if ($rSaveIPTables) {
        saveiptables();
    }
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    if (file_exists($unique_id)) {
        @unlink($unique_id);
    }
}
