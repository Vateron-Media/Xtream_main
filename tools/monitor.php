<?php

function checkRunning($streamID) {
    clearstatcache(true);
    if (file_exists(STREAMS_PATH . $streamID . '_.monitor')) {
        $rPID = intval(file_get_contents(STREAMS_PATH . $streamID . '_.monitor'));
    }
    if (empty($rPID)) {
        shell_exec("kill -9 `ps -ef | grep 'XtreamCodes\\[" . intval($streamID) . "\\]' | grep -v grep | awk '{print \$2}'`;");
    } else {
        if (file_exists('/proc/' . $rPID)) {
            $rCommand = trim(file_get_contents('/proc/' . $rPID . '/cmdline'));
            if ($rCommand == 'XtreamCodes[' . $streamID . ']' && is_numeric($rPID) && 0 < $rPID) {
                posix_kill($rPID, 9);
            }
        }
    }
}
// if ((posix_getpwuid(posix_geteuid())['name'] != 'xtreamcodes')) {
//     exit('Please run as XtreamCodes!' . "\n");
// }
if ((!@$argc || ($argc <= 1))) {
    exit(0);
}
$streamID = intval($argv[1]);
$restart = !empty($argv[2]);
require str_replace('\\', '/', dirname($argv[0])) . "/../wwwdir/init.php";
checkRunning($streamID);
set_time_limit(0);
cli_set_process_title('XtreamCodes[' . $streamID . ']');
$ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t2.stream_id = t1.id AND t2.server_id = \'%d\' WHERE t1.id = \'%d\'', SERVER_ID, $streamID);
if (($ipTV_db->num_rows() <= 0)) {
    ipTV_stream::stopStream($streamID);
    exit();
}
$streamInfo = $ipTV_db->get_row();
$ipTV_db->query('UPDATE `streams_servers` SET `monitor_pid` = \'%d\' WHERE `server_stream_id` = \'%d\'', getmypid(), $streamInfo['server_stream_id']);

if (ipTV_lib::$settings["enable_cache"]) {
    ipTV_streaming::updateStream($streamID);
}


$rPID = (file_exists(STREAMS_PATH . $streamID . '_.pid') ? intval(file_get_contents(STREAMS_PATH . $streamID . '_.pid')) : $streamInfo['pid']);
$rAutoRestart = json_decode($streamInfo['auto_restart'], true);
$rPlaylist = STREAMS_PATH . $streamID . '_.m3u8';
$rDelayPID = $streamInfo['delay_pid'];
$rParentID = $streamInfo['parent_id'];
$streamProbe = false;
$sources = array();
$segmentTime = ipTV_lib::$SegmentsSettings["seg_time"];
$rPrioritySwitch = false;
$rMaxFails = 0;
if (($rParentID == 0)) {
    $sources = json_decode($streamInfo['stream_source'], true);
    //////////////////////////
}
if (0 >= $rParentID) {
    $rCurrentSource = $streamInfo['current_source'];
} else {
    $rCurrentSource = 'Loopback: #' . $rParentID;
}
$rLastSegment = $rForceSource = null;
$ipTV_db->query('SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = \'%d\' AND t1.argument_id = t2.id', $streamID);
$streamArguments = $ipTV_db->get_rows();
if (!(0 < $streamInfo['delay_minutes']) && ($streamInfo['parent_id'] == 0)) {
    $rDelay = false;
    $rFolder = STREAMS_PATH;
} else {
    $rFolder = DELAY_PATH;
    $rPlaylist = DELAY_PATH . $streamID . '_.m3u8';
    $rDelay = true;
}
$rFirstRun = true;
$rTotalCalls = 0;
if (ipTV_streaming::isStreamRunning($rPID, $streamID)) {
    echo 'Stream is running.' . "\n";
    if ($restart) {
        $rTotalCalls = MONITOR_CALLS;
        if ((is_numeric($rPID) && (0 < $rPID))) {
            shell_exec('kill -9 ' . intval($rPID));
        }
        shell_exec('rm -f ' . STREAMS_PATH . intval($streamID) . '_*');
        file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
        if (($rDelay && ipTV_streaming::isDelayRunning($rDelayPID, $streamID) && is_numeric($rDelayPID) && (0 < $rDelayPID))) {
            shell_exec('kill -9 ' . intval($rDelayPID));
        }
        usleep(50000);
        $rDelayPID = $rPID = 0;
    }
} else {
    file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
}
if (ipTV_lib::$settings["kill_rogue_ffmpeg"]) {
    exec('ps aux | grep -v grep | grep \'/' . $streamID . '_.m3u8\' | awk \'{print $2}\'', $rFFMPEG);
    foreach ($rFFMPEG as $roguePID) {
        if ((is_numeric($roguePID) && (0 < intval($roguePID)) && (intval($roguePID) != intval($rPID)))) {
            shell_exec('kill -9 ' . $roguePID . ';');
        }
    }
}
goto label235;
label235:
if (true) {
    if (!(0 < $rPID)) {
        goto label471;
    }
    $ipTV_db->close_mysql();
    $startedTime = $rDurationChecked = $rAudioChecked = $rCheckedTime = $rBackupsChecked = time();
    $rMD5 = md5_file($rPlaylist);
    $D97a4f098a8d1bf8 = ipTV_streaming::isStreamRunning($rPID, $streamID) && file_exists($rPlaylist);
    $b4015d24aedaf0db = null;
    goto label592;
    label592: //while
    if ((ipTV_streaming::isStreamRunning($rPID, $streamID) && file_exists($rPlaylist))) {
        if (!(!empty($rAutoRestart['days']) && !empty($rAutoRestart['at']))) {
            goto label195;
        }
        list($rHour, $rMinutes) = explode(':', $rAutoRestart['at']);
        if (!(in_array(date('l'), $rAutoRestart['days']) && (date('H') == $rHour))) {
            goto label195;
        }
        if (!($rMinutes == date('i'))) {
            goto label195;
        }
        echo 'Auto-restart' . "\n";
        ipTV_streaming::streamLog($streamID, SERVER_ID, 'AUTO_RESTART', $rCurrentSource);
        $D97a4f098a8d1bf8 = false;
        goto label1186;
    }
    goto label1186;
    label195:
    if (($streamProbe || (!file_exists(STREAMS_PATH . $streamID . '_.dur') && (300 < (time() - $rDurationChecked))))) {
        echo 'Probe Stream' . "\n";
        $segment = ipTV_streaming::GetSegmentsOfPlaylist($rPlaylist, 10)[0];
        if (!empty($segment)) {
            if (((300 < (time() - $rDurationChecked)) && ($segment == $rLastSegment))) {
                ipTV_streaming::streamLog($streamID, SERVER_ID, 'FFMPEG_ERROR', $rCurrentSource);
                goto label1186;
            }
            $rLastSegment = $segment;
            $E02429d2ee600884 = ipTV_stream::probeStream($rFolder . $segment);
            if ((10 < intval($E02429d2ee600884['of_duration']))) {
                $E02429d2ee600884['of_duration'] = 10;
            }
            file_put_contents(STREAMS_PATH . $streamID . '_.dur', intval($E02429d2ee600884['of_duration']));
            if (($segmentTime < intval($E02429d2ee600884['of_duration']))) {
                $segmentTime = intval($E02429d2ee600884['of_duration']);
            }
            file_put_contents(STREAMS_PATH . $streamID . '_.stream_info', json_encode($E02429d2ee600884, JSON_UNESCAPED_UNICODE));
            $streamInfo['stream_info'] = json_encode($E02429d2ee600884, JSON_UNESCAPED_UNICODE);
        }
        $streamProbe = false;
        $rDurationChecked = time();
        if (!file_exists(STREAMS_PATH . $streamID . '_.pid')) {
            file_put_contents(STREAMS_PATH . $streamID . '_.pid', $rPID);
        }
        if (!file_exists(STREAMS_PATH . $streamID . '_.monitor')) {
            file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
        }
    }
    if (!(($streamInfo['fps_restart'] == 1) && (ipTV_lib::$settings["fps_delay"] < (time() - $startedTime)) && file_exists(STREAMS_PATH . $streamID . '_.progress_check'))) {
        goto label298;
    }
    echo 'Checking FPS...' . "\n";
    $d75674a646265e7b = floatval(json_decode(file_get_contents(STREAMS_PATH . $streamID . '_.progress_check'), true)['fps']) ?: 0;
    if (!(0 < $d75674a646265e7b)) {
        goto label1847;
    }
    if (!$b4015d24aedaf0db) {
        goto label1087;
    }
    if (!($b4015d24aedaf0db && (($d75674a646265e7b * ($streamInfo['fps_threshold'] ?: 100)) < $b4015d24aedaf0db))) {
        goto label1847;
    }
    echo 'FPS dropped below threshold! Break' . "\n";
    ipTV_streaming::streamLog($streamID, SERVER_ID, 'FPS_DROP_THRESHOLD', $rCurrentSource);
    goto label1186;
    label884:
    $rArguments = implode(' ', ipTV_stream::getFormattedStreamArguments($streamArguments, $rProtocol, 'fetch'));
    if (($E02429d2ee600884 = ipTV_stream::probeStream($streamSource, $rArguments))) {
        echo 'Force new source' . "\n";
        ipTV_streaming::streamLog($streamID, SERVER_ID, 'FORCE_SOURCE', $sources[$rForceID]);
        $rForceSource = $sources[$rForceID];
        unlink(SIGNALS_TMP_PATH . $streamID . '.force');
        $D97a4f098a8d1bf8 = false;
        goto label1186;
    }
    goto label1631;
    label1631:
    unlink(SIGNALS_TMP_PATH . $streamID . '.force');
    label496:
    if ((file_exists(SIGNALS_TMP_PATH . $streamID . '.force') && ($rParentID == 0))) {
        $rForceID = intval(file_get_contents(SIGNALS_TMP_PATH . $streamID . '.force'));
        $streamSource = ipTV_stream::parseStreamURL($sources[$rForceID]);
        if (($sources[$rForceID] != $rCurrentSource)) {
            $rProtocol = strtolower(substr($streamSource, 0, strpos($streamSource, '://')));
            goto label884;
        }
        goto label1631;
    }
    if (($rDelay && ($streamInfo['delay_available_at'] <= time()) && !ipTV_streaming::isDelayRunning($rDelayPID, $streamID))) {
        echo 'Start Delay' . "\n";
        ipTV_streaming::streamLog($streamID, SERVER_ID, 'DELAY_START');
        $rDelayPID = intval(shell_exec(PHP_BIN . ' ' . TOOLS_PATH . 'delay.php ' . intval($streamID) . ' ' . intval($streamInfo['delay_minutes']) . ' >/dev/null 2>/dev/null & echo $!'));
    }
    sleep(1);
    goto label592;
}
goto label1880;
label1:
if ($streamInfo['parent_id']) {
    $rForceSource = (!is_null(ipTV_lib::$StreamingServers[SERVER_ID]['private_url_ip']) && !is_null(ipTV_lib::$StreamingServers[$streamInfo['parent_id']]['private_url_ip']) ? ipTV_lib::$StreamingServers[$streamInfo['parent_id']]['private_url_ip'] : ipTV_lib::$StreamingServers[$streamInfo['parent_id']]['public_url_ip']) . 'admin/live?stream=' . intval($streamID) . '&password=' . urlencode(ipTV_lib::$settings['live_streaming_pass']) . '&extension=ts';
}
$rData = XtreamCodes::startLLOD($streamID, $streamInfo, $streamInfo['parent_id'] ? array() : $streamArguments, $rForceSource);
goto label644;
label1512:
if ($rForceSource) {
    $Ea84d0933a1ef2f0 = $rForceSource;
} else {
    $Ea84d0933a1ef2f0 = json_decode($streamInfo['stream_source'], true)[0];
}
$rData = ipTV_stream::startStream($streamID, false, $Ea84d0933a1ef2f0, true);
label644:
goto label1131;
label1127:
$rData = XtreamCodes::startLoopback($streamID);
label1131:
if ((is_numeric($rData) && ($rData == 0))) {
    $E9d347a502b13abd = true;
    $rMaxFails++;
    if (((0 < ipTV_lib::$settings['stop_failures']) && ($rMaxFails == ipTV_lib::$settings['stop_failures']))) {
        echo 'Failure limit reached, exiting.' . "\n";
        exit();
        goto label1880;
    }
}
if (!$rData) {
    exit();
    goto label1880;
}
if ($E9d347a502b13abd) {
    goto label562;
}
$rPID = intval($rData['main_pid']);
if ($rPID) {
    file_put_contents(STREAMS_PATH . $streamID . '_.pid', $rPID);
}
$rPlaylist = $rData['playlist'];
$rDelay = $rData['delay_enabled'];
$streamInfo['delay_available_at'] = $rData['delay_start_at'];
$rParentID = $rData['parent_id'];
if (0 >= $rParentID) {
    $rCurrentSource = trim($rData['stream_source'], '\'"');
} else {
    $rCurrentSource = 'Loopback: #' . $rParentID;
}
$rOffset = $rData['offset'];
$streamProbe = true;
echo 'Stream started' . "\n";
echo $rCurrentSource . "\n";
if ($rPrioritySwitch) {
    $rForceSource = null;
    $rPrioritySwitch = false;
}
if (!$rDelay) {
    $rFolder = STREAMS_PATH;
} else {
    $rFolder = DELAY_PATH;
}
$e1bc98ce34937596 = $rFolder . $streamID . '_0.ts';
$ea6de21e70c530a9 = false;
$rChecks = 0;
$A63c815f93524582 = (($segmentTime * 3) <= 30 ? $segmentTime * 3 : 30);
if (!($A63c815f93524582 < 20)) {
    goto label998;
}
$A63c815f93524582 = 20;
goto label998;
label998:
if (true) {
    echo 'Checking for playlist ' . ($rChecks + 1) . ('/' . $A63c815f93524582 . '...' . "\n");
    if (ipTV_streaming::isStreamRunning($rPID, $streamID)) {
        if (file_exists($rPlaylist)) {
            echo 'Playlist exists!' . "\n";
            goto label1064;
        }
        if ((file_exists($e1bc98ce34937596) && !$ea6de21e70c530a9 && $streamInfo['on_demand'])) {
            echo 'Segment exists!' . "\n";
            $ea6de21e70c530a9 = true;
            $rChecks = 0;
            $ipTV_db->query('UPDATE `streams_servers` SET `stream_status` = 0, `stream_started` = \'%d\' WHERE `server_stream_id` = \'%d\'', time() - $rOffset, $streamInfo['server_stream_id']);
        }
        if (($rChecks == $A63c815f93524582)) {
            echo 'Reached max failures' . "\n";
            $E9d347a502b13abd = true;
            goto label1064;
        }
        $rChecks++;
        sleep(1);
        goto label998;
    }
    echo 'Ffmpeg stopped running' . "\n";
    $E9d347a502b13abd = true;
    goto label1064;
}
goto label1064;
label1064:
goto label562;
label562:
ipTV_lib::$settings = ipTV_lib::getSettings();
if (ipTV_streaming::isStreamRunning($rPID, $streamID) && !$E9d347a502b13abd) {
    goto label1267;
}
echo 'Stream start failed...' . "\n";
if (($rParentID == 0)) {
    ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_START_FAIL', $rCurrentSource);
}
if ((is_numeric($rPID) && (0 < $rPID) && ipTV_streaming::isStreamRunning($rPID, $streamID))) {
    shell_exec('kill -9 ' . intval($rPID));
}
$ipTV_db->query('UPDATE `streams_servers` SET `pid` = null, `stream_status` = 1 WHERE `server_stream_id` = \'%d\';', $streamInfo['server_stream_id']);
if (ipTV_lib::$settings["enable_cache"]) {
    ipTV_streaming::updateStream($streamID);
}
echo 'Sleep for ' . ipTV_lib::$settings['stream_fail_sleep'] . ' seconds...';
sleep(ipTV_lib::$settings['stream_fail_sleep']);
if (!(ipTV_lib::$settings['on_demand_failure_exit'] && $streamInfo['on_demand'])) {
    goto label554;
}
echo 'On-demand failed to run!' . "\n";
exit();
goto label1880;
label1186:
if ($D97a4f098a8d1bf8) {
    ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_FAILED', $rCurrentSource);
    echo 'Stream failed!' . "\n";
}
$ipTV_db->db_connect();
goto label471;
label471:
if (ipTV_streaming::isStreamRunning($rPID, $streamID)) {
    echo 'Killing stream...' . "\n";
    if ((is_numeric($rPID) && (0 < $rPID))) {
        shell_exec('kill -9 ' . intval($rPID));
    }
    usleep(50000);
}
if (ipTV_streaming::isDelayRunning($rDelayPID, $streamID)) {
    echo 'Killing stream delay...' . "\n";
    ////////////////////////
    if ((is_numeric($rDelayPID) && (0 < $rDelayPID))) {
        shell_exec('kill -9 ' . intval($rDelayPID));
    }
    usleep(50000);
}
goto label76;
//////////////////////////
label554:
if ((MONITOR_CALLS <= $rTotalCalls)) {
    $rTotalCalls = 0;
}
goto label76;
label76:
if (!ipTV_streaming::isStreamRunning($rPID, $streamID)) {
    $E9d347a502b13abd = false;
    echo 'Restarting...' . "\n";
    shell_exec('rm -f ' . STREAMS_PATH . intval($streamID) . '_*');
    file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
    $rOffset = 0;
    $rTotalCalls++;
    if ((0 < $streamInfo['parent_id']) && ipTV_lib::$settings['php_loopback']) {
        goto label1127;
    }
    if ((0 < $streamInfo['llod']) && $streamInfo['on_demand'] && $rFirstRun) {
        goto label933;
    }
    if ($streamInfo['type'] == 3) {
        if (((0 < $rPID) && !$streamInfo['parent_id'] && (0 < $streamInfo['stream_started']))) {
            $rCCInfo = json_decode($streamInfo['cc_info'], true);
            if (($rCCInfo && ((time() - $streamInfo['stream_started']) < (intval($rCCInfo[count($rCCInfo) - 1]['finish']) * 0.95)))) {
                $rOffset = time() - $streamInfo['stream_started'];
            }
        }
        $rData = ipTV_stream::startStream($streamID, false, $rForceSource, false, $rOffset);
        label933:
        if ($streamInfo['llod'] == 1) {
            goto label1512;
        }
        goto label1;
    }
    $rData = ipTV_stream::startStream($streamID, $rTotalCalls < MONITOR_CALLS, $rForceSource);
    goto label644;
}
goto label235;
label1087:
if (ipTV_lib::$settings['fps_check_type'] == 1) {
    goto label1094;
}
$b4015d24aedaf0db = $d75674a646265e7b;
goto label1847;
label1094:
$segment = ipTV_streaming::GetSegmentsOfPlaylist($rPlaylist, 10)[0];
if (empty($segment)) {
    goto label1847;
}
$E02429d2ee600884 = ipTV_stream::probeStream($rFolder . $segment);
if (!(isset($E02429d2ee600884['codecs']['video']['avg_frame_rate']) || isset($E02429d2ee600884['codecs']['video']['r_frame_rate']))) {
    goto label1847;
}
$d75674a646265e7b = $E02429d2ee600884['codecs']['video']['avg_frame_rate'] ?: $E02429d2ee600884['codecs']['video']['r_frame_rate'];
goto label768;
label768:
if (stripos($d75674a646265e7b, '/') !== false) {
    goto label780;
}
$d75674a646265e7b = floatval($d75674a646265e7b);
goto label1052;
label780:
list($Be71401a913607c0, $Cd98e5a46a318d0a) = array_map('floatval', explode('/', $d75674a646265e7b));
goto label1047;
label1047:
$d75674a646265e7b = floatval($Be71401a913607c0 / $Cd98e5a46a318d0a);
label1052:
if (!(0 < $d75674a646265e7b)) {
    goto label1057;
}
$b4015d24aedaf0db = $d75674a646265e7b;
label1057:
goto label1847;
label1267:
echo 'Started! Probe Stream' . "\n";
if ($rFirstRun) {
    $rFirstRun = false;
    ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_START', $rCurrentSource);
} else {
    ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_RESTART', $rCurrentSource);
}
$segment = $rFolder . ipTV_streaming::GetSegmentsOfPlaylist($rPlaylist, 10)[0];
$streamInfo['stream_info'] = null;
if (file_exists($segment)) {
    $E02429d2ee600884 = ipTV_stream::probeStream($segment);
    if ((10 < intval($E02429d2ee600884['of_duration']))) {
        $E02429d2ee600884['of_duration'] = 10;
    }
    file_put_contents(STREAMS_PATH . $streamID . '_.dur', intval($E02429d2ee600884['of_duration']));
    if (($segmentTime < intval($E02429d2ee600884['of_duration']))) {
        $segmentTime = intval($E02429d2ee600884['of_duration']);
    }
    if ($E02429d2ee600884) {
        $streamInfo['stream_info'] = json_encode($E02429d2ee600884, JSON_UNESCAPED_UNICODE);
        $rBitrate = ipTV_streaming::getStreamBitrate('live', STREAMS_PATH . $streamID . '_.m3u8');
        $streamProbe = false;
        $rDurationChecked = time();
    }
}
if (!$ea6de21e70c530a9 && $streamInfo['stream_info'] && $streamInfo['on_demand']) {
    if ($streamInfo['stream_info']) {
        $ipTV_db->query('UPDATE `streams_servers` SET `stream_info` = \'%d\', `bitrate` = \'%d\', `stream_status` = 0, `stream_started` = \'%d\' WHERE `server_stream_id` = \'%d\'', $streamInfo['stream_info'], intval($rBitrate), time() - $rOffset, $streamInfo['server_stream_id']);
    } else {
        $ipTV_db->query('UPDATE `streams_servers` SET `stream_status` = 0, `stream_info` = NULL, `compatible` = 0, `audio_codec` = NULL, `video_codec` = NULL, `resolution` = NULL, `stream_started` = \'%d\' WHERE `server_stream_id` = \'%d\'', time() - $rOffset, $streamInfo['server_stream_id']);
    }
} else {
    $ipTV_db->query('UPDATE `streams_servers` SET `stream_info` = \'%d\', `bitrate` = \'%d\', `stream_status` = 0 WHERE `server_stream_id` = \'%d\'', $streamInfo['stream_info'], intval($rBitrate), $streamInfo['server_stream_id']);
}
if (ipTV_lib::$settings["enable_cache"]) {
    ipTV_streaming::updateStream($streamID);
}
echo 'End start process' . "\n";
goto label554;
label1847:
unlink(STREAMS_PATH . $streamID . '_.progress_check');
label298:
if (!((ipTV_lib::$settings['audio_restart_loss'] == 1) && (300 < (time() - $rAudioChecked)))) {
    goto label617;
}
echo 'Checking audio...' . "\n";
$segment = ipTV_streaming::GetSegmentsOfPlaylist($rPlaylist, 10)[0];
if (!empty($segment)) {
    $E02429d2ee600884 = ipTV_stream::probeStream($rFolder . $segment);
    if ((!isset($E02429d2ee600884['codecs']['audio']) || empty($E02429d2ee600884['codecs']['audio']))) {
        echo 'Lost audio! Break' . "\n";
        ipTV_streaming::streamLog($streamID, SERVER_ID, 'AUDIO_LOSS', $rCurrentSource);
        goto label1186;
    }
    $rAudioChecked = time();
    label617:
    if ((($segmentTime * 6) <= time() - $rCheckedTime)) {
        $Fcfb63b23cad3c6e = md5_file($rPlaylist);
        if ($rMD5 != $Fcfb63b23cad3c6e) {
            $rMD5 = $Fcfb63b23cad3c6e;
            $rCheckedTime = time();
            label1851:
            if (ipTV_lib::$settings['encrypt_hls']) {
                foreach (glob(STREAMS_PATH . $streamID . '_*.ts.enc') as $rFile) {
                    if (!file_exists(rtrim($rFile, '.enc'))) {
                        unlink($rFile);
                    }
                }
            }
            if ((count(json_decode($streamInfo['stream_info'], true)) == 0)) {
                $streamProbe = true;
            }
            $rCheckedTime = time();
            goto label1095;
        }
        goto label1186;
    }
    label1095:
    if (((ipTV_lib::$settings['priority_backup'] == 1) && (1 < count($sources)) && ($rParentID == 0) && (300 < (time() - $rBackupsChecked)))) {
        echo 'Checking backups...' . "\n";
        $rBackupsChecked = time();
        $rKey = array_search($rCurrentSource, $sources);
        if ((!is_numeric($rKey) || (0 < $rKey))) {
            foreach ($sources as $source) {
                if (!(($source == $rCurrentSource) || ($source == $rForceSource))) {
                    $streamSource = ipTV_stream::parseStreamURL($source);
                    $rProtocol = strtolower(substr($streamSource, 0, strpos($streamSource, '://')));
                    $rArguments = implode(' ', ipTV_stream::getFormattedStreamArguments($streamArguments, $rProtocol, 'fetch'));
                    if (($E02429d2ee600884 = ipTV_stream::probeStream($streamSource, $rArguments))) {
                        echo 'Switch priority' . "\n";
                        ipTV_streaming::streamLog($streamID, SERVER_ID, 'PRIORITY_SWITCH', $source);
                        $rForceSource = $source;
                        $rPrioritySwitch = true;
                        $D97a4f098a8d1bf8 = false;
                        goto label1186;
                    }
                }
            }
        }
    }
    goto label496;
}
goto label1186;
label1880:;
