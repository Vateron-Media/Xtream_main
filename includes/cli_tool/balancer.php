<?php
// Notice
// $rType = 1 install lb
// $rType = 2 update lb

if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc && $argc >= 6) {
        $rServerID = intval($argv[2]);
        if ($rServerID != 0) {
            shell_exec("kill -9 `ps -ef | grep 'XC_VM Install\\[" . $rServerID . "\\]' | grep -v grep | awk '{print \$2}'`;");
            set_time_limit(0);
            cli_set_process_title('XC_VM Install[' . $rServerID . ']');
            register_shutdown_function('shutdown');
            require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
            unlink(CACHE_TMP_PATH . 'servers');
            ipTV_lib::$Servers = ipTV_lib::getServers();
            $rType = intval($argv[1]);
            $rPort = intval($argv[3]);
            list(,,,, $rUsername, $rPassword) = $argv;
            $rHTTPPort = (empty($argv[6]) ? 25461 : intval($argv[6]));
            $rHTTPSPort = (empty($argv[7]) ? 25463 : intval($argv[7]));
            $rUpdateSysctl = (empty($argv[8]) ? 0 : intval($argv[8]));

            if (ipTV_lib::$settings['update_chanel'] == 'stable') {
                $release = 'latest_release';
            } else {
                $release = 'latest_prerelease';
            }

            $rSysCtl = '# XC_VM' . PHP_EOL . PHP_EOL . 'net.ipv4.tcp_congestion_control = bbr' . PHP_EOL . 'net.core.default_qdisc = fq' . PHP_EOL . 'net.ipv4.tcp_rmem = 8192 87380 134217728' . PHP_EOL . 'net.ipv4.udp_rmem_min = 16384' . PHP_EOL . 'net.core.rmem_default = 262144' . PHP_EOL . 'net.core.rmem_max = 268435456' . PHP_EOL . 'net.ipv4.tcp_wmem = 8192 65536 134217728' . PHP_EOL . 'net.ipv4.udp_wmem_min = 16384' . PHP_EOL . 'net.core.wmem_default = 262144' . PHP_EOL . 'net.core.wmem_max = 268435456' . PHP_EOL . 'net.core.somaxconn = 1000000' . PHP_EOL . 'net.core.netdev_max_backlog = 250000' . PHP_EOL . 'net.core.optmem_max = 65535' . PHP_EOL . 'net.ipv4.tcp_max_tw_buckets = 1440000' . PHP_EOL . 'net.ipv4.tcp_max_orphans = 16384' . PHP_EOL . 'net.ipv4.ip_local_port_range = 2000 65000' . PHP_EOL . 'net.ipv4.tcp_no_metrics_save = 1' . PHP_EOL . 'net.ipv4.tcp_slow_start_after_idle = 0' . PHP_EOL . 'net.ipv4.tcp_fin_timeout = 15' . PHP_EOL . 'net.ipv4.tcp_keepalive_time = 300' . PHP_EOL . 'net.ipv4.tcp_keepalive_probes = 5' . PHP_EOL . 'net.ipv4.tcp_keepalive_intvl = 15' . PHP_EOL . 'fs.file-max=20970800' . PHP_EOL . 'fs.nr_open=20970800' . PHP_EOL . 'fs.aio-max-nr=20970800' . PHP_EOL . 'net.ipv4.tcp_timestamps = 1' . PHP_EOL . 'net.ipv4.tcp_window_scaling = 1' . PHP_EOL . 'net.ipv4.tcp_mtu_probing = 1' . PHP_EOL . 'net.ipv4.route.flush = 1' . PHP_EOL . 'net.ipv6.route.flush = 1';
            $rInstallDir = BIN_PATH . 'install/';
            $rFiles = array('lb' => 'lb_xui.tar.gz', 'lb_update' => 'update.tar.gz');
            $lastVersion = mb_substr(getGithubReleases("Vateron-Media/Xtream_lb")[$release], 1);

            if ($rType == 1) {
                $rPackages = array(
                    'cpufrequtils',
                    'iproute2',
                    'python',
                    'net-tools',
                    'dirmngr',
                    'gpg-agent',
                    'software-properties-common',
                    'libmaxminddb0',
                    'libmaxminddb-dev',
                    'mmdb-bin',
                    'libcurl4',
                    'libgeoip-dev',
                    'libxslt1-dev',
                    'libonig-dev',
                    'e2fsprogs',
                    'wget',
                    'sysstat',
                    'alsa-utils',
                    'v4l-utils',
                    'mcrypt',
                    'python3',
                    'certbot',
                    'iptables-persistent',
                    'libjpeg-dev',
                    'libpng-dev',
                    'php-ssh2',
                    'xz-utils',
                    'zip',
                    'unzip',
                    'libcurl4-gnu-utls',
                    'libxslt1-dev',
                    'nscd',
                    'htop',
                    'mc',
                    'libpng16-16',
                    'libzip5',
                    'mariadb-server'
                );
                $rInstallFiles = 'https://github.com/Vateron-Media/Xtream_lb/releases/download/v' . $lastVersion . '/' . $rFiles['lb'];
            } elseif ($rType == 2) {
                $rPackages = array('cpufrequtils');
                $rInstallFiles = 'https://github.com/Vateron-Media/Xtream_lb/releases/download/v' . $lastVersion . '/' . $rFiles['lb_update'];
            } else {
                $ipTV_db->query('UPDATE `servers` SET `status` = 4 WHERE `id` = ?;', $rServerID);
                echo 'Invalid type specified!' . "\n";
                exit();
            }

            file_put_contents($rInstallDir . $rServerID . '.json', json_encode(array('root_username' => $rUsername, 'root_password' => $rPassword, 'ssh_port' => $rPort)));

            $rHost = ipTV_lib::$Servers[$rServerID]['server_ip'];
            echo 'Connecting to ' . $rHost . ':' . $rPort . "\n";
            if ($rConn = ssh2_connect($rHost, $rPort)) {
                if ($rUsername == 'root') {
                    echo 'Connected! Authenticating as root user...' . "\n";
                } else {
                    echo 'Connected! Authenticating as non-root user...' . "\n";
                }
                $rResult = @ssh2_auth_password($rConn, $rUsername, $rPassword);
                if ($rResult) {
                    echo "\n" . 'Stopping any previous version of XC_VM' . "\n";
                    runCommand($rConn, 'sudo systemctl stop xc_vm');
                    runCommand($rConn, 'sudo killall -9 -u xc_vm');
                    echo "\n" . 'Updating system' . "\n";
                    runCommand($rConn, 'sudo rm /var/lib/dpkg/lock-frontend && sudo rm /var/cache/apt/archives/lock && sudo rm /var/lib/dpkg/lock');
                    if ($rType == 1) {
                        runCommand($rConn, 'sudo add-apt-repository -y ppa:maxmind/ppa');
                    }
                    runCommand($rConn, 'sudo apt update && sudo apt full-upgrade -y');
                    foreach ($rPackages as $rPackage) {
                        echo 'Installing package: ' . $rPackage . "\n";
                        runCommand($rConn, 'sudo DEBIAN_FRONTEND=noninteractive apt-get -yq install ' . $rPackage);
                    }
                    if ($rType == 1) {
                        echo 'Creating XC_VM system user' . "\n";
                        runCommand($rConn, 'sudo adduser --system --shell /bin/false --group --disabled-login xc_vm');
                        runCommand($rConn, 'sudo mkdir ' . MAIN_DIR);
                        // runCommand($rConn, 'sudo rm -rf ' . BIN_PATH);
                    }

                    echo 'Download and install panel' . "\n";
                    runCommand($rConn, 'wget -q -O "/tmp/lb_xui.tar.gz" ' . $rInstallFiles);
                    runCommand($rConn, 'sudo tar -zxvf "/tmp/lb_xui.tar.gz" -C "' . MAIN_DIR . '"');
                    runCommand($rConn, 'sudo rm -f "/tmp/lb_xui.tar.gz"');
                    if (!file_exists(MAIN_DIR . 'status')) {
                        $ipTV_db->query('UPDATE `servers` SET `status` = 4 WHERE `id` = ?;', $rServerID);
                        echo 'Failed to extract files! Exiting' . "\n";
                        exit();
                    }
                    if (in_array($rType, array(1, 2))) {
                        if (stripos(runCommand($rConn, 'sudo cat /etc/fstab')['output'], STREAMS_PATH) === false) {
                            echo 'Adding ramdisk mounts' . "\n";
                            runCommand($rConn, 'sudo echo "tmpfs ' . STREAMS_PATH . ' tmpfs defaults,noatime,nosuid,nodev,noexec,mode=1777,size=90% 0 0" >> /etc/fstab');
                            runCommand($rConn, 'sudo echo "tmpfs ' . TMP_PATH . ' tmpfs defaults,noatime,nosuid,nodev,noexec,mode=1777,size=2G 0 0" >> /etc/fstab');
                        }
                        if (stripos(runCommand($rConn, 'sudo cat /etc/sysctl.conf')['output'], 'XC_VM') === false) {
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
                    }
                    echo 'Generating configuration file' . "\n";
                    $rMasterConfig = parse_ini_file(CONFIG_PATH . 'config.ini');

                    $rNewConfig = '; XC_VM Configuration' . "\n" . '; -----------------' . "\n" . '; Your username and password will be encrypted and' . "\n" . "; saved to the 'credentials' file in this folder" . "\n" . '; automatically.' . "\n" . ';' . "\n" . '; To change your username or password, modify BOTH' . "\n" . '; below and XC_VM will read and re-encrypt them.' . "\n\n" . '[XC_VM]' . "\n" . 'hostname    =   "' . ipTV_lib::$Servers[SERVER_ID]['server_ip'] . '"' . "\n" . 'database    =   "xc_vm"' . "\n" . 'port        =   ' . intval(ipTV_lib::$config['port']) . "\n" . 'server_id   =   ' . $rServerID . "\n" . 'is_lb       =   1' . "\n\n" . '[Encrypted]' . "\n" . 'username    =   "lb_' . $rServerID . '"' . "\n" . 'password    =   ""';
                    file_put_contents(TMP_PATH . 'config_' . $rServerID, $rNewConfig);
                    sendfile($rConn, TMP_PATH . 'config_' . $rServerID, CONFIG_PATH . 'config.ini');
                    echo 'Installing service' . "\n";
                    runCommand($rConn, 'sudo rm /etc/systemd/system/xc_vm.service');
                    $rSystemd = '[Unit]' . "\n" . 'SourcePath=/home/xc_vm/service' . "\n" . 'Description=XC_VM Service' . "\n" . 'After=network.target' . "\n" . 'StartLimitIntervalSec=0' . "\n\n" . '[Service]' . "\n" . 'Type=simple' . "\n" . 'User=root' . "\n" . 'Restart=always' . "\n" . 'RestartSec=1' . "\n" . 'ExecStart=/bin/bash /home/xc_vm/service start' . "\n" . 'ExecReload=/bin/bash /home/xc_vm/service restart' . "\n" . 'ExecStop=/bin/bash /home/xc_vm/service stop' . "\n\n" . '[Install]' . "\n" . 'WantedBy=multi-user.target';
                    file_put_contents(TMP_PATH . 'systemd_' . $rServerID, $rSystemd);
                    sendfile($rConn, TMP_PATH . 'systemd_' . $rServerID, '/etc/systemd/system/xc_vm.service');
                    runCommand($rConn, 'sudo chmod +x /etc/systemd/system/xc_vm.service');
                    runCommand($rConn, 'sudo rm /etc/init.d/xc_vm');
                    runCommand($rConn, 'sudo systemctl daemon-reload');
                    runCommand($rConn, 'sudo systemctl enable xc_vm');

                    // sendfile($rConn, CONFIG_PATH . 'credentials', CONFIG_PATH . 'credentials');
                    sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/custom.conf', MAIN_DIR . 'bin/nginx/conf/custom.conf');
                    sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/realip_cdn.conf', MAIN_DIR . 'bin/nginx/conf/realip_cdn.conf');
                    sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/realip_cloudflare.conf', MAIN_DIR . 'bin/nginx/conf/realip_cloudflare.conf');
                    sendfile($rConn, MAIN_DIR . 'bin/nginx/conf/realip_xtream.conf', MAIN_DIR . 'bin/nginx/conf/realip_xtream.conf');
                    runCommand($rConn, 'sudo echo "" > "/home/xc_vm/bin/nginx/conf/limit.conf"');
                    runCommand($rConn, 'sudo echo "" > "/home/xc_vm/bin/nginx/conf/limit_queue.conf"');
                    if ($rType == 1) {
                        runCommand($rConn, 'sudo echo "listen ' . $rHTTPPort . ';" > "/home/xc_vm/bin/nginx/conf/ports/http.conf"');
                        runCommand($rConn, 'sudo echo "listen ' . $rHTTPSPort . ' ssl;" > "/home/xc_vm/bin/nginx/conf/ports/https.conf"');
                        $rIP = '127.0.0.1:' . ipTV_lib::$Servers[$rServerID]['http_broadcast_port'];
                        runCommand($rConn, 'sudo echo "on_play http://' . $rIP . '/streaming/rtmp.php; on_publish http://' . $rIP . '/streaming/rtmp.php; on_play_done http://' . $rIP . '/streaming/rtmp.php;" > "/home/xc_vm/bin/nginx_rtmp/conf/live.conf"');
                        $rServices = (intval(runCommand($rConn, 'sudo cat /proc/cpuinfo | grep "^processor" | wc -l')['output']) ?: 4);
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
                            $rTmpPath = TMP_PATH . md5(time() . $i . '.conf');
                            file_put_contents($rTmpPath, str_replace('#PATH#', MAIN_DIR, str_replace('#ID#', $i, $rTemplate)));
                            sendfile($rConn, $rTmpPath, '/etc/php/8.4/fpm/pool.d/' . $i . '.conf');
                        }
                        $rNewBalance .= '}';
                        $rTmpPath = TMP_PATH . md5(time() . 'daemons.sh');
                        file_put_contents($rTmpPath, $rNewScript);
                        sendfile($rConn, $rTmpPath, MAIN_DIR . 'bin/daemons.sh');

                        $rTmpPath = TMP_PATH . md5(time() . 'balance.conf');
                        file_put_contents($rTmpPath, $rNewBalance);
                        sendfile($rConn, $rTmpPath, MAIN_DIR . 'bin/nginx/conf/balance.conf');
                        runCommand($rConn, 'sudo chmod +x ' . MAIN_DIR . 'bin/daemons.sh');
                    }

                    $rSystemConf = runCommand($rConn, 'sudo cat "/etc/systemd/system.conf"')['output'];
                    if (strpos($rSystemConf, 'DefaultLimitNOFILE=1048576') === false) {
                        runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILE=1048576" >> "/etc/systemd/system.conf"');
                        runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILE=1048576" >> "/etc/systemd/user.conf"');
                    }
                    if (strpos($rSystemConf, 'nDefaultLimitNOFILESoft=1048576') === false) {
                        runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILESoft=1048576" >> "/etc/systemd/system.conf"');
                        runCommand($rConn, 'sudo echo "' . "\n" . 'DefaultLimitNOFILESoft=1048576" >> "/etc/systemd/user.conf"');
                    }
                    runCommand($rConn, 'sudo systemctl stop apparmor');
                    runCommand($rConn, 'sudo systemctl disable apparmor');
                    runCommand($rConn, 'sudo mount -a');
                    runCommand($rConn, "sudo echo 'net.ipv4.ip_unprivileged_port_start=0' > /etc/sysctl.d/50-allports-nonroot.conf && sudo sysctl --system");
                    sleep(3);
                    runCommand($rConn, 'sudo chown -R xc_vm:xc_vm ' . MAIN_DIR . 'tmp');
                    runCommand($rConn, 'sudo chown -R xc_vm:xc_vm ' . MAIN_DIR . 'content/streams');
                    runCommand($rConn, 'sudo chown -R xc_vm:xc_vm ' . MAIN_DIR);
                    runCommand($rConn, 'sleep 2 && sudo ' . MAIN_DIR . 'permissions.sh > /dev/null');
                    echo 'Installation complete! Starting XC_VM' . "\n";
                    runCommand($rConn, 'sudo service xc_vm restart');
                    if ($rType == 1) {
                        runCommand($rConn, 'sudo ' . MAIN_DIR . 'status 1');
                        runCommand($rConn, 'sudo -u xc_vm ' . PHP_BIN . ' ' . CLI_PATH . 'startup.php');
                        runCommand($rConn, 'sudo -u xc_vm ' . PHP_BIN . ' ' . CRON_PATH . 'servers.php');
                    } elseif ($rType == 2) {
                        runCommand($rConn, 'sudo ' . PHP_BIN . ' ' . CLI_PATH . 'update.php "post-update"');
                        runCommand($rConn, 'sudo ' . MAIN_DIR . 'status 1');
                        runCommand($rConn, 'sudo -u xc_vm ' . PHP_BIN . ' ' . CLI_PATH . 'startup.php');
                        runCommand($rConn, 'sudo -u xc_vm ' . PHP_BIN . ' ' . CRON_PATH . 'servers.php');
                    } else {
                        runCommand($rConn, 'sudo -u xc_vm ' . PHP_BIN . ' ' . INCLUDES_PATH . 'startup.php');
                    }

                    if ($rType == 1) {
                        $ipTV_db->query('UPDATE `servers` SET `status` = 1, `http_broadcast_port` = ?, `https_broadcast_port` = ?, `total_services` = ? WHERE `id` = ?;', $rHTTPPort, $rHTTPSPort, $rServices, $rServerID);
                    } else {
                        $ipTV_db->query('UPDATE `servers` SET `status` = 1 WHERE `id` = ?;', $rServerID);
                    }
                    unlink($rInstallDir . $rServerID . '.json');
                } else {
                    $ipTV_db->query('UPDATE `servers` SET `status` = 4 WHERE `id` = ?;', $rServerID);
                    echo 'Failed to authenticate using credentials. Exiting' . "\n";
                    exit();
                }
            } else {
                $ipTV_db->query('UPDATE `servers` SET `status` = 4 WHERE `id` = ?;', $rServerID);
                echo 'Failed to connect to server. Exiting' . "\n";
                exit();
            }
        } else {
            exit();
        }
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}

function sendFile($rConn, $rPath, $rOutput, $rWarn = false) {
    $rMD5 = md5_file($rPath);
    ssh2_scp_send($rConn, $rPath, $rOutput);
    $rOutMD5 = trim(explode(' ', runCommand($rConn, 'md5sum "' . $rOutput . '"')['output'])[0]);
    if ($rMD5 == $rOutMD5) {
        return true;
    }
    if ($rWarn) {
        echo 'Failed to write using SCP, reverting to SFTP transfer... This will be take significantly longer!' . "\n";
    }
    $rSFTP = ssh2_sftp($rConn);
    $rSuccess = true;
    $rStream = @fopen('ssh2.sftp://' . $rSFTP . $rOutput, 'wb');
    try {
        $rData = @file_get_contents($rPath);
        if (@fwrite($rStream, $rData) === false) {
            $rSuccess = false;
        }
        fclose($rStream);
    } catch (Exception $e) {
        $rSuccess = false;
        fclose($rStream);
    }
    return $rSuccess;
}

function runCommand($rConn, $rCommand) {
    $rStream = ssh2_exec($rConn, $rCommand);
    $rError = ssh2_fetch_stream($rStream, SSH2_STREAM_STDERR);
    stream_set_blocking($rError, true);
    stream_set_blocking($rStream, true);
    return array('output' => stream_get_contents($rStream), 'error' => stream_get_contents($rError));
}

/**
 * Fetches the latest release and pre-release information from a GitHub repository
 *
 * @param string $repo The repository name in format "owner/repository"
 *
 * @return array{
 *     latest_release?: string|null,
 *     latest_prerelease?: string|null,
 *     error?: string
 * } Returns an array containing:
 *           - latest_release: The tag name of the latest stable release (null if none found)
 *           - latest_prerelease: The tag name of the latest pre-release (null if none found)
 *           - error: Error message if the request fails
 *
 * @throws Exception When the GitHub API request fails or returns invalid data
 */
function getGithubReleases(string $repo): array {
    $url = "https://api.github.com/repos/$repo/releases";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP-Request'
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return ['error' => 'Request error: ' . curl_error($ch)];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => "GitHub API returned HTTP code $httpCode"];
    }

    $releases = json_decode($response, true);
    if (empty($releases)) {
        return ['error' => 'No releases found'];
    }

    $latestRelease = null;
    $latestPrerelease = null;

    foreach ($releases as $release) {
        if (!$release['prerelease'] && !$latestRelease) {
            $latestRelease = $release['tag_name'];
        }
        if ($release['prerelease'] && !$latestPrerelease) {
            $latestPrerelease = $release['tag_name'];
        }

        if ($latestRelease && $latestPrerelease) {
            break;
        }
    }

    return [
        'latest_release' => $latestRelease,
        'latest_prerelease' => $latestPrerelease
    ];
}

function shutdown() {
    global $ipTV_db;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
