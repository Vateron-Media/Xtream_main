<?php
if ($argc && $argc >= 5) {
    $rServerID = intval($argv[2]);
    if ($rServerID != 0) {
        shell_exec("kill -9 `ps -ef | grep 'XtreamCodes Install\\[" . $rServerID . "\\]' | grep -v grep | awk '{print \$2}'`;");
        set_time_limit(0);
        cli_set_process_title('XtreamCodes Install[' . $rServerID . ']');
        register_shutdown_function('shutdown');
        require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
        unlink(CACHE_TMP_PATH . 'servers');
        ipTV_lib::$StreamingServers = ipTV_lib::getServers();
        $rPort = intval($argv[2]);
        list(,,,, $rUsername, $rPassword) = $argv;
        $rHTTPPort = (empty($argv[5]) ? 80 : intval($argv[5]));
        $rHTTPSPort = (empty($argv[6]) ? 443 : intval($argv[6]));
        $rUpdateSysctl = (empty($argv[7]) ? 0 : intval($argv[7]));
        $rPrivateIP = (empty($argv[8]) ? 0 : intval($argv[8]));
        $rParentIDs = (empty($argv[9]) ? array() : json_decode($argv[9], true));
        $rSysCtl = '# XtreamCodes' . PHP_EOL . PHP_EOL . 'net.ipv4.tcp_congestion_control = bbr' . PHP_EOL . 'net.core.default_qdisc = fq' . PHP_EOL . 'net.ipv4.tcp_rmem = 8192 87380 134217728' . PHP_EOL . 'net.ipv4.udp_rmem_min = 16384' . PHP_EOL . 'net.core.rmem_default = 262144' . PHP_EOL . 'net.core.rmem_max = 268435456' . PHP_EOL . 'net.ipv4.tcp_wmem = 8192 65536 134217728' . PHP_EOL . 'net.ipv4.udp_wmem_min = 16384' . PHP_EOL . 'net.core.wmem_default = 262144' . PHP_EOL . 'net.core.wmem_max = 268435456' . PHP_EOL . 'net.core.somaxconn = 1000000' . PHP_EOL . 'net.core.netdev_max_backlog = 250000' . PHP_EOL . 'net.core.optmem_max = 65535' . PHP_EOL . 'net.ipv4.tcp_max_tw_buckets = 1440000' . PHP_EOL . 'net.ipv4.tcp_max_orphans = 16384' . PHP_EOL . 'net.ipv4.ip_local_port_range = 2000 65000' . PHP_EOL . 'net.ipv4.tcp_no_metrics_save = 1' . PHP_EOL . 'net.ipv4.tcp_slow_start_after_idle = 0' . PHP_EOL . 'net.ipv4.tcp_fin_timeout = 15' . PHP_EOL . 'net.ipv4.tcp_keepalive_time = 300' . PHP_EOL . 'net.ipv4.tcp_keepalive_probes = 5' . PHP_EOL . 'net.ipv4.tcp_keepalive_intvl = 15' . PHP_EOL . 'fs.file-max=20970800' . PHP_EOL . 'fs.nr_open=20970800' . PHP_EOL . 'fs.aio-max-nr=20970800' . PHP_EOL . 'net.ipv4.tcp_timestamps = 1' . PHP_EOL . 'net.ipv4.tcp_window_scaling = 1' . PHP_EOL . 'net.ipv4.tcp_mtu_probing = 1' . PHP_EOL . 'net.ipv4.route.flush = 1' . PHP_EOL . 'net.ipv6.route.flush = 1';
        $rInstallDir = BIN_PATH . 'install/';
        $rUpdate = json_decode(file_get_contents("https://raw.githubusercontent.com/Vateron-Media/Xtream_Update/main/version.json", false, $rContext), True);

        $rPackages = array("libcurl4", "libxslt1-dev", "libgeoip-dev", "e2fsprogs", "wget", "mcrypt", "nscd", "htop", "zip", "unzip", "mc", "libzip5", "mariadb-server");
        $rInstallFiles = 'https://github.com/Vateron-Media/Xtream_sub/releases/download/v' . $rUpdate['sub'] . '/sub_xui.tar.gz';

        file_put_contents($rInstallDir . $rServerID . '.json', json_encode(array('root_username' => $rUsername, 'root_password' => $rPassword, 'ssh_port' => $rPort)));

        $rHost = ipTV_lib::$StreamingServers[$rServerID]['server_ip'];
        echo 'Connecting to ' . $rHost . ':' . $rPort . "\n";
        if ($rConn = ssh2_connect($rHost, $rPort)) {
            if ($rUsername == 'root') {
                echo 'Connected! Authenticating as root user...' . "\n";
            } else {
                echo 'Connected! Authenticating as non-root user...' . "\n";
            }
            $rResult = @ssh2_auth_password($rConn, $rUsername, $rPassword);
            if ($rResult) {
                echo "\n" . 'Stopping any previous version of XtreamCodes' . "\n";
                runCommand($rConn, 'sudo killall -u xtreamcodes');
                sleep(1);
                runCommand($rConn, 'sudo killall -u xtreamcodes');
                sleep(1);
                runCommand($rConn, 'sudo killall -u xtreamcodes');

                echo "\n" . 'Updating system' . "\n";
                runCommand($rConn, 'sudo rm /var/lib/dpkg/lock-frontend && sudo rm /var/cache/apt/archives/lock && sudo rm /var/lib/dpkg/lock');
                runCommand($rConn, 'sudo apt-get update');

                foreach ($rPackages as $rPackage) {
                    echo 'Installing package: ' . $rPackage . "\n";
                    runCommand($rConn, 'sudo DEBIAN_FRONTEND=noninteractive apt-get -yq install ' . $rPackage);
                }

                echo 'Creating XtreamCodes system user' . "\n";
                runCommand($rConn, 'sudo adduser --system --shell /bin/false --group --disabled-login xtreamcodes');
                runCommand($rConn, 'sudo mkdir ' . MAIN_DIR);
                // runCommand($rConn, 'sudo rm -rf ' . BIN_PATH);

                runCommand($rConn, 'wget -q -O "/tmp/sub_xui.tar.gz" ' . $rInstallFiles);
                runCommand($rConn, 'sudo tar -zxvf "/tmp/sub_xui.tar.gz" -C "' . MAIN_DIR . '"');
                runCommand($rConn, 'sudo rm -f "/tmp/sub_xui.tar.gz"');








                if (stripos(runCommand($rConn, 'sudo cat /etc/fstab')['output'], STREAMS_PATH) == false) {
                    echo 'Adding ramdisk mounts' . "\n";
                    runCommand($rConn, 'sudo echo "tmpfs ' . STREAMS_PATH . ' tmpfs defaults,noatime,nosuid,nodev,noexec,mode=1777,size=90% 0 0" >> /etc/fstab');
                    runCommand($rConn, 'sudo echo "tmpfs ' . TMP_PATH . ' tmpfs defaults,noatime,nosuid,nodev,noexec,mode=1777,size=2G 0 0" >> /etc/fstab');
                }
                if (stripos(runCommand($rConn, 'sudo cat /etc/sysctl.conf')['output'], 'XtreamCodes.one') === false) {
                    if ($rUpdateSysctl) {
                        echo 'Adding sysctl.conf' . "\n";
                        runCommand($rConn, 'sudo modprobe ip_conntrack');
                        file_put_contents(TMP_PATH . 'sysctl_' . $rServerID, $rSysCtl);
                        sendfile($rConn, TMP_PATH . 'sysctl_' . $rServerID, '/etc/sysctl.conf');
                        runCommand($rConn, 'sudo sysctl -p');
                        runCommand($rConn, 'sudo touch ' . CONFIG_PATH . 'sysctl.on');
                    } else {
                        runCommand($rConn, 'sudo rm ' . CONFIG_PATH . 'sysctl.on');
                    }
                } else {
                    if (!$rUpdateSysctl) {
                        runCommand($rConn, 'sudo rm ' . CONFIG_PATH . 'sysctl.on');
                    } else {
                        runCommand($rConn, 'sudo touch ' . CONFIG_PATH . 'sysctl.on');
                    }
                }

                echo 'Generating configuration file' . "\n";
                $rMasterConfig = parse_ini_file(CONFIG_PATH . 'config.ini');

                $rNewConfig = '; XtreamCodes Configuration' . "\n" . '; -----------------' . "\n" . '; Your username and password will be encrypted and' . "\n" . "; saved to the 'credentials' file in this folder" . "\n" . '; automatically.' . "\n" . ';' . "\n" . '; To change your username or password, modify BOTH' . "\n" . '; below and XtreamCodes will read and re-encrypt them.' . "\n\n" . '[XtreamCodes]' . "\n" . 'hostname    =   "' . ipTV_lib::$StreamingServers[SERVER_ID]['server_ip'] . '"' . "\n" . 'database    =   "xui"' . "\n" . 'port        =   ' . intval(XtreamCodes::$rConfig['port']) . "\n" . 'server_id   =   ' . $rServerID . "\n" . 'is_lb       =   1' . "\n\n" . '[Encrypted]' . "\n" . 'username    =   ""' . "\n" . 'password    =   ""';

                file_put_contents(TMP_PATH . 'config_' . $rServerID, $rNewConfig);
                sendfile($rConn, TMP_PATH . 'config_' . $rServerID, CONFIG_PATH . 'config.ini');
                echo 'Installing service' . "\n";
                runCommand($rConn, 'sudo rm /etc/systemd/system/xui.service');
                $rSystemd = '[Unit]' . "\n" . 'SourcePath=/home/xui/service' . "\n" . 'Description=XtreamCodes.one Service' . "\n" . 'After=network.target' . "\n" . 'StartLimitIntervalSec=0' . "\n\n" . '[Service]' . "\n" . 'Type=simple' . "\n" . 'User=root' . "\n" . 'Restart=always' . "\n" . 'RestartSec=1' . "\n" . 'ExecStart=/bin/bash /home/xui/service start' . "\n" . 'ExecRestart=/bin/bash /home/xui/service restart' . "\n" . 'ExecStop=/bin/bash /home/xui/service stop' . "\n\n" . '[Install]' . "\n" . 'WantedBy=multi-user.target';
                file_put_contents(TMP_PATH . 'systemd_' . $rServerID, $rSystemd);
                sendfile($rConn, TMP_PATH . 'systemd_' . $rServerID, '/etc/systemd/system/xuione.service');
                runCommand($rConn, 'sudo chmod +x /etc/systemd/system/xuione.service');
                runCommand($rConn, 'sudo rm /etc/init.d/xuione');
                runCommand($rConn, 'sudo systemctl daemon-reload');
                runCommand($rConn, 'sudo systemctl enable xuione');

                sendfile($rConn, CONFIG_PATH . 'credentials', CONFIG_PATH . 'credentials');
                sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/custom.conf', MAIN_DIR . 'bin/nginx/conf/custom.conf');
                sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/realip_cdn.conf', MAIN_DIR . 'bin/nginx/conf/realip_cdn.conf');
                sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/realip_cloudflare.conf', MAIN_DIR . 'bin/nginx/conf/realip_cloudflare.conf');
                sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/realip_xui.conf', MAIN_DIR . 'bin/nginx/conf/realip_xui.conf');
                runCommand($rConn, 'sudo echo "" > "/home/xtreamcodes/bin/nginx/conf/limit.conf"');
                runCommand($rConn, 'sudo echo "" > "/home/xtreamcodes/bin/nginx/conf/limit_queue.conf"');

                $rIP = '127.0.0.1:' . ipTV_lib::$StreamingServers[$rServerID]['http_broadcast_port'];
                runCommand($rConn, 'sudo echo "on_play http://' . $rIP . '/stream/rtmp; on_publish http://' . $rIP . '/stream/rtmp; on_play_done http://' . $rIP . '/stream/rtmp;" > "/home/xui/bin/nginx_rtmp/conf/live.conf"');
                $rServices = (intval(runCommand($rConn, 'sudo cat /proc/cpuinfo | grep "^processor" | wc -l')['output']) ?: 4);
                runCommand($rConn, 'sudo rm ' . MAIN_DIR . 'bin/php/etc/*.conf');
                $rNewScript = '#! /bin/bash' . "\n";
                $rNewBalance = 'upstream php {' . "\n" . '    least_conn;' . "\n";
                $rTemplate = file_get_contents(MAIN_DIR . 'bin/php/etc/template');
                foreach (range(1, $rServices) as $i) {
                    $rNewScript .= 'start-stop-daemon --start --quiet --pidfile ' . MAIN_DIR . 'bin/php/sockets/' . $i . '.pid --exec ' . MAIN_DIR . 'bin/php/sbin/php-fpm -- --daemonize --fpm-config ' . MAIN_DIR . 'bin/php/etc/' . $i . '.conf' . "\n";
                    $rNewBalance .= '    server unix:' . MAIN_DIR . 'bin/php/sockets/' . $i . '.sock;' . "\n";
                    $rTmpPath = TMP_PATH . md5(time() . $i . '.conf');
                    file_put_contents($rTmpPath, str_replace('#PATH#', MAIN_DIR, str_replace('#ID#', $i, $rTemplate)));
                    sendfile($rConn, $rTmpPath, MAIN_DIR . 'bin/php/etc/' . $i . '.conf');
                }
                $rNewBalance .= '}';
                $rTmpPath = TMP_PATH . md5(time() . 'daemons.sh');
                file_put_contents($rTmpPath, $rNewScript);
                sendfile($rConn, $rTmpPath, MAIN_DIR . 'bin/daemons.sh');
                $rTmpPath = TMP_PATH . md5(time() . 'balance.conf');
                file_put_contents($rTmpPath, $rNewBalance);
                sendfile($rConn, $rTmpPath, MAIN_DIR . 'bin/nginx/conf/balance.conf');
                runCommand($rConn, 'sudo chmod +x ' . MAIN_DIR . 'bin/daemons.sh');


                $rSystemConf = runCommand($rConn, 'sudo cat "/etc/systemd/system.conf"')['output'];
                if (strpos($rSystemConf, 'DefaultLimitNOFILE=1048576') !== false) {
                } else {
                    runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILE=1048576" >> "/etc/systemd/system.conf"');
                    runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILE=1048576" >> "/etc/systemd/user.conf"');
                }
                if (strpos($rSystemConf, 'nDefaultLimitNOFILESoft=1048576') !== false) {
                } else {
                    runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILESoft=1048576" >> "/etc/systemd/system.conf"');
                    runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILESoft=1048576" >> "/etc/systemd/user.conf"');
                }
                runCommand($rConn, 'sudo systemctl stop apparmor');
                runCommand($rConn, 'sudo systemctl disable apparmor');
                runCommand($rConn, 'sudo mount -a');
                runCommand($rConn, "sudo echo 'net.ipv4.ip_unprivileged_port_start=0' > /etc/sysctl.d/50-allports-nonroot.conf && sudo sysctl --system");
                sleep(3);
                runCommand($rConn, 'sudo chown -R xtreamcodes:xtreamcodes ' . MAIN_DIR . 'tmp');
                runCommand($rConn, 'sudo chown -R xtreamcodes:xtreamcodes ' . MAIN_DIR . '/streams');
                runCommand($rConn, 'sudo chown -R xtreamcodes:xtreamcodes ' . MAIN_DIR);
                //    Functions::grantPrivileges('TKbxeQrBXw2swDNwTh5yrj4jMV4RaLO0', $rHost);
                echo 'Installation complete! Starting XtreamCodes' . "\n";
                runCommand($rConn, 'sudo service xuione restart');

                runCommand($rConn, 'sudo ' . MAIN_DIR . 'status 1');
                runCommand($rConn, 'sudo -u xtreamcodes ' . PHP_BIN . ' ' . TOOLS_PATH . 'startup.php');
                runCommand($rConn, 'sudo -u xtreamcodes ' . PHP_BIN . ' ' . CRON_PATH . 'servers.php');

                $ipTV_db->query('UPDATE `streaming_servers` SET `status` = 1, `http_broadcast_port` = \'%s\', `https_broadcast_port` = \'%s\', `total_services` = \'%s\' WHERE `id` = \'%s\';', $rHTTPPort, $rHTTPSPort, $rServices, $rServerID);

                unlink($rInstallDir . $rServerID . '.json');
            } else {
                $ipTV_db->query('UPDATE `streaming_servers` SET `status` = 4 WHERE `id` = \'%s\';', $rServerID);
                echo 'Failed to authenticate using credentials. Exiting' . "\n";
                exit();
            }
        } else {
            $ipTV_db->query('UPDATE `streaming_servers` SET `status` = 4 WHERE `id` = \'%s\';', $rServerID);
            echo 'Failed to connect to server. Exiting' . "\n";
            exit();
        }
    } else {
        exit();
    }
} else {
    exit(0);
}

function runCommand($rConn, $rCommand) {
    $rStream = ssh2_exec($rConn, $rCommand);
    $rError = ssh2_fetch_stream($rStream, SSH2_STREAM_STDERR);
    stream_set_blocking($rError, true);
    stream_set_blocking($rStream, true);
    return array('output' => stream_get_contents($rStream), 'error' => stream_get_contents($rError));
}
function shutdown() {
    global $ipTV_db;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
