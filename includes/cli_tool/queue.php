<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        set_time_limit(0);
        require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
        shell_exec('kill -9 $(ps aux | grep queue | grep -v grep | grep -v ' . getmypid() . " | awk '{print \$2}')");
        $rLastCheck = null;
        $rInterval = 60;
        $rMD5 = md5_file(__FILE__);
        while (true && $ipTV_db->ping()) {
            if (!($rLastCheck && $rInterval > time() - $rLastCheck)) {
                if (md5_file(__FILE__) == $rMD5) {
                    ipTV_lib::$settings = ipTV_lib::getSettings(true);
                    $rLastCheck = time();
                } else {
                    echo 'File changed! Break.' . "\n";
                }
            }
            if ($ipTV_db->query("SELECT `id`, `pid` FROM `queue` WHERE `server_id` = ? AND `pid` IS NOT NULL AND `type` = 'movie' ORDER BY `added` ASC;", SERVER_ID)) {
                $rDelete = $rInProgress = array();
                if ($ipTV_db->num_rows() > 0) {
                    foreach ($ipTV_db->get_rows() as $rRow) {
                        if ($rRow['pid'] && (ipTV_streaming::isProcessRunning($rRow['pid'], 'ffmpeg') || ipTV_streaming::isProcessRunning($rRow['pid'], PHP_BIN))) {
                            $rInProgress[] = $rRow['pid'];
                        } else {
                            $rDelete[] = $rRow['id'];
                        }
                    }
                }
                // $rFreeSlots = (0 < ipTV_lib::$settings['max_encode_movies'] ? intval(ipTV_lib::$settings['max_encode_movies']) - count($rInProgress) : 50);
                // if ($rFreeSlots > 0) {
                //     if ($ipTV_db->query("SELECT `id`, `stream_id` FROM `queue` WHERE `server_id` = ? AND `pid` IS NULL AND `type` = 'movie' ORDER BY `added` ASC LIMIT " . $rFreeSlots . ';', SERVER_ID)) {
                //         if ($ipTV_db->num_rows() > 0) {
                //             foreach ($ipTV_db->get_rows() as $rRow) {
                //                 $rPID = ipTV_stream::startMovie($rRow['stream_id']);
                //                 if ($rPID) {
                //                     $ipTV_db->query('UPDATE `queue` SET `pid` = ? WHERE `id` = ?;', $rPID, $rRow['id']);
                //                 } else {
                //                     $rDelete[] = $rRow['id'];
                //                 }
                //             }
                //         }
                //     }
                // }
                if ($ipTV_db->query("SELECT `id`, `pid` FROM `queue` WHERE `server_id` = ? AND `pid` IS NOT NULL AND `type` = 'channel' ORDER BY `added` ASC;", SERVER_ID)) {
                    $rInProgress = array();
                    if ($ipTV_db->num_rows() > 0) {
                        foreach ($ipTV_db->get_rows() as $rRow) {
                            if ($rRow['pid'] && ipTV_streaming::isProcessRunning($rRow['pid'], PHP_BIN)) {
                                $rInProgress[] = $rRow['pid'];
                            } else {
                                $rDelete[] = $rRow['id'];
                            }
                        }
                    }
                    // $rFreeSlots = (0 < ipTV_lib::$settings['max_encode_cc'] ? intval(ipTV_lib::$settings['max_encode_cc']) - count($rInProgress) : 1);
                    // if ($rFreeSlots > 0) {
                    //     if ($ipTV_db->query("SELECT `id`, `stream_id` FROM `queue` WHERE `server_id` = ? AND `pid` IS NULL AND `type` = 'channel' ORDER BY `added` ASC LIMIT " . $rFreeSlots . ';', SERVER_ID)) {
                    //         if ($ipTV_db->num_rows() > 0) {
                    //             foreach ($ipTV_db->get_rows() as $rRow) {
                    //                 if (file_exists(CREATED_PATH . $rRow['stream_id'] . '_.create')) {
                    //                     unlink(CREATED_PATH . $rRow['stream_id'] . '_.create');
                    //                 }
                    //                 shell_exec(PHP_BIN . ' ' . CLI_PATH . 'created.php ' . intval($rRow['stream_id']) . ' >/dev/null 2>/dev/null &');
                    //                 $rPID = null;
                    //                 foreach (range(1, 3) as $i) {
                    //                     if (!file_exists(CREATED_PATH . $rRow['stream_id'] . '_.create')) {
                    //                         usleep(100000);
                    //                     } else {
                    //                         $rPID = intval(file_get_contents(CREATED_PATH . $rRow['stream_id'] . '_.create'));
                    //                         break;
                    //                     }
                    //                 }
                    //                 if ($rPID) {
                    //                     $ipTV_db->query('UPDATE `queue` SET `pid` = ? WHERE `id` = ?;', $rPID, $rRow['id']);
                    //                 } else {
                    //                     $rDelete[] = $rRow['id'];
                    //                 }
                    //             }
                    //         }
                    //     }
                    // }
                    if (count($rDelete) > 0) {
                        $ipTV_db->query('DELETE FROM `queue` WHERE `id` IN (' . implode(',', $rDelete) . ');');
                    }
                    sleep((0 < ipTV_lib::$settings['queue_loop'] ? intval(ipTV_lib::$settings['queue_loop']) : 5));
                }
                break;
            }
        }
        if (is_object($ipTV_db)) {
            $ipTV_db->close_mysql();
        }
        shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
