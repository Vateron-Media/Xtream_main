<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        set_time_limit(0);
        require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
        shell_exec('kill -9 $(ps aux | grep ondemand | grep -v grep | grep -v ' . getmypid() . " | awk '{print \$2}')");
        if (ipTV_lib::$settings['on_demand_instant_off']) {
            if (ipTV_lib::$settings['redis_handler']) {
                ipTV_lib::connectRedis();
            }
            $rMainID = ipTV_streaming::getMainID();
            $rLastCheck = null;
            $rInterval = 60;
            $rMD5 = md5_file(__FILE__);
            while (true && $ipTV_db && $ipTV_db->ping() && !(ipTV_lib::$settings['redis_handler'] && (!ipTV_lib::$redis || !ipTV_lib::$redis->ping()))) {
                if (!$rLastCheck && $rInterval < time() - $rLastCheck) {
                    if (md5_file(__FILE__) == $rMD5) {
                        ipTV_lib::$settings = ipTV_lib::getSettings(true);
                        $rLastCheck = time();
                    } else {
                        echo 'File changed! Break.' . "\n";
                    }
                }
                $rRows = array();
                if (ipTV_lib::$settings['redis_handler']) {
                    $rStreamIDs = $rAttached = $rRows = array();
                    if ($ipTV_db->query('SELECT t1.stream_id, servers_attached.attached FROM `streams_servers` t1 LEFT JOIN (SELECT `stream_id`, COUNT(*) AS `attached` FROM `streams_servers` WHERE `parent_id` = ? AND `pid` IS NOT NULL AND `pid` > 0 AND `monitor_pid` IS NOT NULL AND `monitor_pid` > 0) AS `servers_attached` ON `servers_attached`.`stream_id` = t1.`stream_id` WHERE t1.pid IS NOT NULL AND t1.pid > 0 AND t1.server_id = ? AND t1.`on_demand` = 1;', SERVER_ID, SERVER_ID)) {
                        foreach ($ipTV_db->get_rows() as $rRow) {
                            $rStreamIDs[] = $rRow['stream_id'];
                            $rAttached[$rRow['stream_id']] = $rRow['attached'];
                        }
                        if (count($rStreamIDs) > 0) {
                            $rConnections = ipTV_streaming::getStreamConnections($rStreamIDs, false, false);
                            foreach ($rStreamIDs as $rStreamID) {
                                $rRows[] = array('stream_id' => $rStreamID, 'online_clients' => (count($rConnections[$rStreamID][SERVER_ID]) ?: 0), 'attached' => ($rAttached[$rStreamID] ?: 0));
                            }
                        }
                    }
                    break;
                }
                if ($ipTV_db->query('SELECT t1.stream_id, clients.online_clients, servers_attached.attached FROM `streams_servers` t1 LEFT JOIN (SELECT stream_id, COUNT(*) as online_clients FROM `lines_live` WHERE `server_id` = ? AND `hls_end` = 0 GROUP BY stream_id) AS clients ON clients.stream_id = t1.stream_id LEFT JOIN (SELECT `stream_id`, COUNT(*) AS `attached` FROM `streams_servers` WHERE `parent_id` = ? AND `pid` IS NOT NULL AND `pid` > 0 AND `monitor_pid` IS NOT NULL AND `monitor_pid` > 0) AS `servers_attached` ON `servers_attached`.`stream_id` = t1.`stream_id` WHERE t1.pid IS NOT NULL AND t1.pid > 0 AND t1.server_id = ? AND t1.`on_demand` = 1;', SERVER_ID, SERVER_ID, SERVER_ID)) {
                    if ($ipTV_db->num_rows() > 0) {
                        $rRows = $ipTV_db->get_rows();
                    }
                }
            }
            if (is_object($ipTV_db)) {
                $ipTV_db->close_mysql();
            }
            shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
            if (count($rRows) > 0) {
                foreach ($rRows as $rRow) {
                    if (!(0 < $rRow['online_clients'] || 0 < $rRow['attached'])) {
                        $rStreamID = $rRow['stream_id'];
                        $rPID = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.pid'));
                        $rMonitorPID = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.monitor'));
                        $rAdminQueue = $rQueue = 0;
                        if (file_exists(SIGNALS_TMP_PATH . 'queue_' . intval($rStreamID))) {
                            foreach ((igbinary_unserialize(file_get_contents(SIGNALS_TMP_PATH . 'queue_' . intval($rStreamID))) ?: array()) as $rPID) {
                                if (ipTV_streaming::isProcessRunning($rPID, 'php-fpm')) {
                                    $rQueue++;
                                }
                            }
                        }
                        if (file_exists(SIGNALS_TMP_PATH . 'admin_' . intval($rStreamID)) && time() - filemtime(SIGNALS_TMP_PATH . 'admin_' . intval($rStreamID)) <= 30) {
                            $rAdminQueue = 1;
                        }
                        echo 'Queue: ' . ($rQueue + $rAdminQueue) . "\n";
                        if ($rQueue == 0 && $rAdminQueue == 0 && ipTV_streaming::checkMonitorRunning($rMonitorPID, $rStreamID)) {
                            echo 'Killing ID: ' . $rStreamID . "\n";
                            if (is_numeric($rMonitorPID) && 0 < $rMonitorPID) {
                                posix_kill($rMonitorPID, 9);
                            }
                            if (is_numeric($rPID) && 0 < $rPID) {
                                posix_kill($rPID, 9);
                            }
                            shell_exec('rm -f ' . STREAMS_PATH . intval($rStreamID) . '_*');
                            $ipTV_db->query('UPDATE `streams_servers` SET `bitrate` = NULL,`current_source` = NULL,`to_analyze` = 0,`pid` = NULL,`stream_started` = NULL,`stream_info` = NULL,`audio_codec` = NULL,`video_codec` = NULL,`resolution` = NULL,`compatible` = 0,`stream_status` = 0,`monitor_pid` = NULL WHERE `stream_id` = ? AND `server_id` = ?', $rStreamID, SERVER_ID);
                            $ipTV_db->query('INSERT INTO `signals`(`server_id`, `cache`, `time`, `custom_data`) VALUES(?, 1, ?, ?);', $rMainID, time(), json_encode(array('type' => 'update_stream', 'id' => $rStreamID)));
                            unlink(SIGNALS_TMP_PATH . 'queue_' . intval($rStreamID));
                            ipTV_streaming::updateStream($rStreamID);
                        }
                    }
                }
            }
            usleep(1000000);
        } else {
            echo 'On-Demand - Instant Off setting is disabled.' . "\n";
            exit();
        }
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
