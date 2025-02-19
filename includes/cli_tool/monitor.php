<?php
// Notice
// Monitor script for XC_VM streams
// Handles stream monitoring, auto-restart, and health checks

function checkRunning($streamID) {
    clearstatcache(true);
    if (file_exists(STREAMS_PATH . $streamID . '_.monitor')) {
        $rPID = intval(file_get_contents(STREAMS_PATH . $streamID . '_.monitor'));
    }
    if (empty($rPID)) {
        shell_exec("kill -9 `ps -ef | grep 'XC_VM\\[" . intval($streamID) . "\\]' | grep -v grep | awk '{print \$2}'`;");
    } else {
        if (file_exists('/proc/' . $rPID)) {
            $rCommand = trim(file_get_contents('/proc/' . $rPID . '/cmdline'));
            if ($rCommand == 'XC_VM[' . $streamID . ']' && is_numeric($rPID) && 0 < $rPID) {
                posix_kill($rPID, 9);
            }
        }
    }
}

// Verify running as xtreamcodes user
if (posix_getpwuid(posix_geteuid())['name'] != 'xtreamcodes') {
    exit('Please run as XC_VM!' . "\n");
}

// Validate arguments
if (!$argc || $argc <= 1) {
    exit(0);
}

$streamID = intval($argv[1]);
$restart = !empty($argv[2]);

// Initialize
require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
checkRunning($streamID);
set_time_limit(0);
cli_set_process_title('XC_VM[' . $streamID . ']');

// Get stream info
$ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t2.stream_id = t1.id AND t2.server_id = ? WHERE t1.id = ?', SERVER_ID, $streamID);
if ($ipTV_db->num_rows() <= 0) {
    ipTV_stream::stopStream($streamID);
    exit();
}

$streamInfo = $ipTV_db->get_row();
$ipTV_db->query('UPDATE `streams_servers` SET `monitor_pid` = ? WHERE `server_stream_id` = ?', getmypid(), $streamInfo['server_stream_id']);

// Update stream cache if enabled
if (ipTV_lib::$settings["enable_cache"]) {
    ipTV_streaming::updateStream($streamID);
}

// Initialize stream variables
$rPID = (file_exists(STREAMS_PATH . $streamID . '_.pid') ? intval(file_get_contents(STREAMS_PATH . $streamID . '_.pid')) : $streamInfo['pid']);
$rAutoRestart = json_decode($streamInfo['auto_restart'], true);
$rPlaylist = STREAMS_PATH . $streamID . '_.m3u8';
$rDelayPID = $streamInfo['delay_pid'];
$rParentID = $streamInfo['parent_id'];
$streamProbe = false;
$sources = array();
$segmentTime = ipTV_lib::$SegmentsSettings['seg_time'];
$rPrioritySwitch = false;
$rMaxFails = 0;

// Get stream sources
if ($rParentID == 0) {
    $sources = json_decode($streamInfo['stream_source'], true);
}

// Set current source
if ($rParentID <= 0) {
    $rCurrentSource = $streamInfo['current_source'];
} else {
    $rCurrentSource = 'Loopback: #' . $rParentID;
}

// Initialize stream parameters
$rLastSegment = null;
$rForceSource = null;

// Get stream arguments
$ipTV_db->query('SELECT t1.*, t2.* FROM `streams_options` t1, `streams_arguments` t2 WHERE t1.stream_id = ? AND t1.argument_id = t2.id', $streamID);
$streamArguments = $ipTV_db->get_rows();

// Setup delay settings
if ($streamInfo['delay_minutes'] <= 0 && $streamInfo['parent_id'] == 0) {
    $rDelay = false;
    $rFolder = STREAMS_PATH;
} else {
    $rFolder = DELAY_PATH;
    $rPlaylist = DELAY_PATH . $streamID . '_.m3u8';
    $rDelay = true;
}

$rFirstRun = true;
$rTotalCalls = 0;

// Check if stream is running
if (ipTV_streaming::isStreamRunning($rPID, $streamID)) {
    echo 'Stream is running.' . "\n";
    if ($restart) {
        $rTotalCalls = MONITOR_CALLS;
        if (is_numeric($rPID) && $rPID > 0) {
            shell_exec('kill -9 ' . intval($rPID));
        }
        shell_exec('rm -f ' . STREAMS_PATH . intval($streamID) . '_*');
        file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
        
        if ($rDelay && ipTV_streaming::isDelayRunning($rDelayPID, $streamID) && is_numeric($rDelayPID) && $rDelayPID > 0) {
            shell_exec('kill -9 ' . intval($rDelayPID));
        }
        usleep(50000);
        $rDelayPID = $rPID = 0;
    }
} else {
    file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
}

// Kill rogue ffmpeg processes if enabled
if (ipTV_lib::$settings['kill_rogue_ffmpeg']) {
    exec('ps aux | grep -v grep | grep \'/' . $streamID . '_.m3u8\' | awk \'{print $2}\'', $rFFMPEG);
    foreach ($rFFMPEG as $roguePID) {
        if (is_numeric($roguePID) && intval($roguePID) > 0 && intval($roguePID) != intval($rPID)) {
            shell_exec('kill -9 ' . $roguePID . ';');
        }
    }
}

// Main monitoring loop
while (true) {
    // Check if stream needs to be started/restarted
    if (!ipTV_streaming::isStreamRunning($rPID, $streamID)) {
        $streamStarted = false;
        echo 'Restarting...' . "\n";
        shell_exec('rm -f ' . STREAMS_PATH . intval($streamID) . '_*');
        file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
        $rOffset = 0;
        $rTotalCalls++;

        // Handle HLS encryption cleanup
        if (ipTV_lib::$settings['encrypt_hls']) {
            foreach (glob(STREAMS_PATH . $streamID . '_*.ts.enc') as $encryptedFile) {
                if (!file_exists(rtrim($encryptedFile, '.enc'))) {
                    unlink($encryptedFile);
                }
            }
        }

        // Handle different stream start scenarios
        if ($streamInfo['parent_id'] > 0 && ipTV_lib::$settings['php_loopback']) {
            $rData = ipTV_stream::startLoopback($streamID);
        } else if ($streamInfo['type'] == 3) {
            if ($rPID > 0 && !$streamInfo['parent_id'] && $streamInfo['stream_started'] > 0) {
                $rCCInfo = json_decode($streamInfo['cc_info'], true);
                if ($rCCInfo && (time() - $streamInfo['stream_started']) < (intval($rCCInfo[count($rCCInfo) - 1]['finish']) * 0.95)) {
                    $rOffset = time() - $streamInfo['stream_started'];
                }
            }
            $rData = ipTV_stream::startStream($streamID, false, $rForceSource, false, $rOffset);
        } else if ($streamInfo['llod'] > 0 && $streamInfo['on_demand'] && $rFirstRun) {
            if ($streamInfo['llod'] == 1) {
                if ($rForceSource) {
                    $streamSource = $rForceSource;
                } else {
                    $streamSource = json_decode($streamInfo['stream_source'], true)[0];
                }
                $rData = ipTV_stream::startStream($streamID, false, $streamSource, true);
            } else {
                if ($streamInfo['parent_id']) {
                    $rForceSource = ipTV_lib::$Servers[$streamInfo['parent_id']]['public_url_ip'] . 'admin/live?stream=' . intval($streamID) . '&password=' . urlencode(ipTV_lib::$settings['live_streaming_pass']) . '&extension=ts';
                }
                $rData = ipTV_stream::startLLOD($streamID, $streamInfo, $streamInfo['parent_id'] ? array() : $streamArguments, $rForceSource);
            }
        } else {
            $rData = ipTV_stream::startStream($streamID, $rTotalCalls < MONITOR_CALLS, $rForceSource);
        }

        // Process stream start result
        if (is_numeric($rData) && $rData == 0) {
            $streamStarted = true;
            $rMaxFails++;
            if (ipTV_lib::$settings['stop_failures'] > 0 && $rMaxFails == ipTV_lib::$settings['stop_failures']) {
                echo 'Failure limit reached, exiting.' . "\n";
                exit();
            }
        }

        if (!$rData) {
            exit();
        }

        // Update stream information
        if (!$streamStarted) {
            $rPID = intval($rData['main_pid']);
            if ($rPID) {
                file_put_contents(STREAMS_PATH . $streamID . '_.pid', $rPID);
            }
            $rPlaylist = $rData['playlist'];
            $rDelay = $rData['delay_enabled'];
            $streamInfo['delay_available_at'] = $rData['delay_start_at'];
            $rParentID = $rData['parent_id'];
            
            if ($rParentID <= 0) {
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

            // Verify stream is actually running
            if (!ipTV_streaming::isStreamRunning($rPID, $streamID)) {
                echo 'Stream failed to start properly.' . "\n";
                ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_START_FAIL', $rCurrentSource);
                continue;
            }

            // Wait for playlist to be created
            $playlistWaitTime = 0;
            while (!file_exists($rPlaylist) && $playlistWaitTime < 30) {
                usleep(100000); // Wait 100ms
                $playlistWaitTime++;
            }

            if (!file_exists($rPlaylist)) {
                echo 'Playlist file not created.' . "\n";
                ipTV_streaming::streamLog($streamID, SERVER_ID, 'PLAYLIST_CREATE_FAIL', $rCurrentSource);
                continue;
            }
        }

        // Update stream information
        $rCompatible = 0;
        $rAudioCodec = $rVideoCodec = $rResolution = null;
        if ($streamInfo['stream_info']) {
            $rStreamJSON = json_decode($streamInfo['stream_info'], true);
            $rCompatible = intval(ipTV_stream::checkCompatibility($rStreamJSON));
            $rAudioCodec = $rStreamJSON['codecs']['audio']['codec_name'] ?: null;
            $rVideoCodec = $rStreamJSON['codecs']['video']['codec_name'] ?: null;
            $rResolution = $rStreamJSON['codecs']['video']['height'] ?: null;
            if ($rResolution) {
                $rResolution = ipTV_stream::getNearest(array(240, 360, 480, 576, 720, 1080, 1440, 2160), $rResolution);
            }
        }

        // Update database with stream info
        if (!$streamStarted && $streamInfo['stream_info'] && $streamInfo['on_demand']) {
            if ($streamInfo['stream_info']) {
                $ipTV_db->query('UPDATE `streams_servers` SET `stream_info` = ?, `compatible` = ?, `audio_codec` = ?, `video_codec` = ?, `resolution` = ?, `bitrate` = ?, `stream_status` = 0, `stream_started` = ? WHERE `server_stream_id` = ?', 
                    $streamInfo['stream_info'], 
                    $rCompatible, 
                    $rAudioCodec, 
                    $rVideoCodec, 
                    $rResolution, 
                    intval($rBitrate), 
                    time() - $rOffset, 
                    $streamInfo['server_stream_id']
                );
            } else {
                $ipTV_db->query('UPDATE `streams_servers` SET `stream_status` = 0, `stream_info` = NULL, `compatible` = 0, `audio_codec` = NULL, `video_codec` = NULL, `resolution` = NULL, `stream_started` = ? WHERE `server_stream_id` = ?',
                    time() - $rOffset,
                    $streamInfo['server_stream_id']
                );
            }
        } else {
            $ipTV_db->query('UPDATE `streams_servers` SET `stream_info` = ?, `compatible` = ?, `audio_codec` = ?, `video_codec` = ?, `resolution` = ?, `bitrate` = ?, `stream_status` = 0 WHERE `server_stream_id` = ?',
                $streamInfo['stream_info'],
                $rCompatible,
                $rAudioCodec,
                $rVideoCodec,
                $rResolution,
                intval($rBitrate),
                $streamInfo['server_stream_id']
            );
        }

        // Update cache if enabled
        if (ipTV_lib::$settings['enable_cache']) {
            ipTV_streaming::updateStream($streamID);
        }

        echo 'End start process' . "\n";
    }

    // Monitor running stream
    if ($rPID > 0) {
        $ipTV_db->close_mysql();
        $startedTime = $rDurationChecked = $rAudioChecked = $rCheckedTime = $rBackupsChecked = time();
        $rMD5 = md5_file($rPlaylist);
        $streamRunning = ipTV_streaming::isStreamRunning($rPID, $streamID) && file_exists($rPlaylist);
        $lastFPS = null;

        while (ipTV_streaming::isStreamRunning($rPID, $streamID) && file_exists($rPlaylist)) {
            // Check auto-restart conditions
            if (!empty($rAutoRestart['days']) && !empty($rAutoRestart['at'])) {
                list($rHour, $rMinutes) = explode(':', $rAutoRestart['at']);
                if (in_array(date('l'), $rAutoRestart['days']) && date('H') == $rHour && $rMinutes == date('i')) {
                    echo 'Auto-restart' . "\n";
                    ipTV_streaming::streamLog($streamID, SERVER_ID, 'AUTO_RESTART', $rCurrentSource);
                    $streamRunning = false;
                    break;
                }
            }

            // Check segment updates
            if ($segmentTime * 6 <= time() - $rCheckedTime) {
                $currentMD5 = md5_file($rPlaylist);
                if ($rMD5 !== $currentMD5) {
                    $rMD5 = $currentMD5;
                    $rCheckedTime = time();

                    // Handle HLS encryption cleanup
                    if (ipTV_lib::$settings['encrypt_hls']) {
                        foreach (glob(STREAMS_PATH . $streamID . '_*.ts.enc') as $encryptedFile) {
                            if (!file_exists(rtrim($encryptedFile, '.enc'))) {
                                unlink($encryptedFile);
                            }
                        }
                    }

                    // Check stream info validity
                    $streamInfoArray = json_decode($streamInfo['stream_info'], true);
                    if (is_array($streamInfoArray) && count($streamInfoArray) === 0) {
                        $streamProbe = true;
                    }
                } else {
                    // Only break if multiple consecutive unchanged checks
                    if (isset($unchangedCount)) {
                        $unchangedCount++;
                        if ($unchangedCount >= 3) {
                            ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_STALLED', $rCurrentSource);
                            break;
                        }
                    } else {
                        $unchangedCount = 1;
                    }
                }
            }

            // Check stream probe and duration with more lenient timing
            if ($streamProbe || (!file_exists(STREAMS_PATH . $streamID . '_.dur') && time() - $rDurationChecked > 600)) {
                echo 'Probe Stream' . "\n";
                $segment = ipTV_streaming::getPlaylistSegments($rPlaylist, 10)[0];
                if (!empty($segment)) {
                    if (time() - $rDurationChecked > 600 && $segment == $rLastSegment) {
                        ipTV_streaming::streamLog($streamID, SERVER_ID, 'FFMPEG_ERROR', $rCurrentSource);
                        break;
                    }
                    $rLastSegment = $segment;
                    $probeResult = ipTV_stream::probeStream($rFolder . $segment);
                    if (intval($probeResult['of_duration']) > 10) {
                        $probeResult['of_duration'] = 10;
                    }
                    file_put_contents(STREAMS_PATH . $streamID . '_.dur', intval($probeResult['of_duration']));
                    if ($segmentTime < intval($probeResult['of_duration'])) {
                        $segmentTime = intval($probeResult['of_duration']);
                    }
                    file_put_contents(STREAMS_PATH . $streamID . '_.stream_info', json_encode($probeResult, JSON_UNESCAPED_UNICODE));
                    $streamInfo['stream_info'] = json_encode($probeResult, JSON_UNESCAPED_UNICODE);
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

            // Check FPS if enabled
            if ($streamInfo['fps_restart'] == 1 && time() - $startedTime > ipTV_lib::$settings['fps_delay'] && file_exists(STREAMS_PATH . $streamID . '_.progress_check')) {
                echo 'Checking FPS...' . "\n";
                $currentFPS = floatval(json_decode(file_get_contents(STREAMS_PATH . $streamID . '_.progress_check'), true)['fps']) ?: 0;
                
                if ($currentFPS > 0) {
                    if (!$lastFPS) {
                        if (ipTV_lib::$settings['fps_check_type'] == 1) {
                            $segment = ipTV_streaming::getPlaylistSegments($rPlaylist, 10)[0];
                            if (!empty($segment)) {
                                $probeResult = ipTV_stream::probeStream($rFolder . $segment);
                                if (isset($probeResult['codecs']['video']['avg_frame_rate']) || isset($probeResult['codecs']['video']['r_frame_rate'])) {
                                    $fps = $probeResult['codecs']['video']['avg_frame_rate'] ?: $probeResult['codecs']['video']['r_frame_rate'];
                                    if (stripos($fps, '/') !== false) {
                                        list($num, $den) = array_map('floatval', explode('/', $fps));
                                        $lastFPS = floatval($num / $den);
                                    } else {
                                        $lastFPS = floatval($fps);
                                    }
                                }
                            }
                        } else {
                            $lastFPS = $currentFPS;
                        }
                    } else if ($lastFPS && $currentFPS * ($streamInfo['fps_threshold'] ?: 100) < $lastFPS) {
                        echo 'FPS dropped below threshold! Break' . "\n";
                        ipTV_streaming::streamLog($streamID, SERVER_ID, 'FPS_DROP_THRESHOLD', $rCurrentSource);
                        break;
                    }
                }
                unlink(STREAMS_PATH . $streamID . '_.progress_check');
            }

            // Check for forced source change
            if (file_exists(SIGNALS_TMP_PATH . $streamID . '.force') && $rParentID == 0) {
                $rForceID = intval(file_get_contents(SIGNALS_TMP_PATH . $streamID . '.force'));
                $streamSource = ipTV_stream::parseStreamURL($sources[$rForceID]);
                
                if ($sources[$rForceID] != $rCurrentSource) {
                    $rProtocol = strtolower(substr($streamSource, 0, strpos($streamSource, '://')));
                    $rArguments = implode(' ', ipTV_stream::getFormattedStreamArguments($streamArguments, $rProtocol, 'fetch'));
                    
                    if ($probeResult = ipTV_stream::probeStream($streamSource, $rArguments)) {
                        echo 'Force new source' . "\n";
                        ipTV_streaming::streamLog($streamID, SERVER_ID, 'FORCE_SOURCE', $sources[$rForceID]);
                        $rForceSource = $sources[$rForceID];
                        unlink(SIGNALS_TMP_PATH . $streamID . '.force');
                        $streamRunning = false;
                        break;
                    }
                }
                unlink(SIGNALS_TMP_PATH . $streamID . '.force');
            }

            // Start delay if needed
            if ($rDelay && $streamInfo['delay_available_at'] <= time() && !ipTV_streaming::isDelayRunning($rDelayPID, $streamID)) {
                echo 'Start Delay' . "\n";
                ipTV_streaming::streamLog($streamID, SERVER_ID, 'DELAY_START');
                $rDelayPID = intval(shell_exec(PHP_BIN . ' ' . CLI_PATH . 'delay.php ' . intval($streamID) . ' ' . intval($streamInfo['delay_minutes']) . ' >/dev/null 2>/dev/null & echo $!'));
            }

            // Check audio loss if enabled
            if (ipTV_lib::$settings['audio_restart_loss'] == 1 && time() - $rAudioChecked > 300) {
                echo 'Checking audio...' . "\n";
                $segments = ipTV_streaming::getPlaylistSegments($rPlaylist, 10);
                $segment = !empty($segments) ? $segments[0] : null;
                
                if (!empty($segment)) {
                    $probeResult = ipTV_stream::probeStream($rFolder . $segment);
                    if (!isset($probeResult['codecs']['audio']) || empty($probeResult['codecs']['audio'])) {
                        echo 'Lost audio! Break' . "\n";
                        ipTV_streaming::streamLog($streamID, SERVER_ID, 'AUDIO_LOSS', $rCurrentSource);
                        break;
                    }
                    
                    // Check for audio/video sync issues
                    if (isset($probeResult['codecs']['audio']['start_time']) && isset($probeResult['codecs']['video']['start_time'])) {
                        $audioStart = floatval($probeResult['codecs']['audio']['start_time']);
                        $videoStart = floatval($probeResult['codecs']['video']['start_time']);
                        $syncDiff = abs($audioStart - $videoStart);
                        
                        // If desync is more than 3 seconds, restart the stream
                        if ($syncDiff > 3.0) {
                            echo "Audio/Video desync detected: {$syncDiff} seconds! Break" . "\n";
                            ipTV_streaming::streamLog($streamID, SERVER_ID, 'AV_DESYNC', $rCurrentSource);
                            break;
                        }
                    }
                    
                    $rAudioChecked = time();
                }
            }

            sleep(1);
        }
    }

    // Handle stream cleanup and restart
    if ($streamRunning) {
        ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_FAILED', $rCurrentSource);
        echo 'Stream failed!' . "\n";
    }
    $ipTV_db->db_connect();

    // Kill running stream if needed
    if (ipTV_streaming::isStreamRunning($rPID, $streamID)) {
        echo 'Killing stream...' . "\n";
        if (is_numeric($rPID) && $rPID > 0) {
            shell_exec('kill -9 ' . intval($rPID));
        }
        usleep(50000);
    }

    // Kill delay stream if running
    if (ipTV_streaming::isDelayRunning($rDelayPID, $streamID)) {
        echo 'Killing stream delay...' . "\n";
        if (is_numeric($rDelayPID) && $rDelayPID > 0) {
            shell_exec('kill -9 ' . intval($rDelayPID));
        }
        usleep(50000);
    }

    // Reset total calls if needed
    if ($rTotalCalls >= MONITOR_CALLS) {
        $rTotalCalls = 0;
    }

    // Handle stream restart
    if (!ipTV_streaming::isStreamRunning($rPID, $streamID)) {
        $streamStarted = false;
        echo 'Restarting...' . "\n";
        shell_exec('rm -f ' . STREAMS_PATH . intval($streamID) . '_*');
        file_put_contents(STREAMS_PATH . $streamID . '_.monitor', getmypid());
        $rOffset = 0;
        $rTotalCalls++;

        // Handle different stream start scenarios
        if ($streamInfo['parent_id'] > 0 && ipTV_lib::$settings['php_loopback']) {
            $rData = ipTV_stream::startLoopback($streamID);
        } else if ($streamInfo['type'] == 3) {
            if ($rPID > 0 && !$streamInfo['parent_id'] && $streamInfo['stream_started'] > 0) {
                $rCCInfo = json_decode($streamInfo['cc_info'], true);
                if ($rCCInfo && (time() - $streamInfo['stream_started']) < (intval($rCCInfo[count($rCCInfo) - 1]['finish']) * 0.95)) {
                    $rOffset = time() - $streamInfo['stream_started'];
                }
            }
            $rData = ipTV_stream::startStream($streamID, false, $rForceSource, false, $rOffset);
        } else if ($streamInfo['llod'] > 0 && $streamInfo['on_demand'] && $rFirstRun) {
            if ($streamInfo['llod'] == 1) {
                if ($rForceSource) {
                    $streamSource = $rForceSource;
                } else {
                    $streamSource = json_decode($streamInfo['stream_source'], true)[0];
                }
                $rData = ipTV_stream::startStream($streamID, false, $streamSource, true);
            } else {
                if ($streamInfo['parent_id']) {
                    $rForceSource = ipTV_lib::$Servers[$streamInfo['parent_id']]['public_url_ip'] . 'admin/live?stream=' . intval($streamID) . '&password=' . urlencode(ipTV_lib::$settings['live_streaming_pass']) . '&extension=ts';
                }
                $rData = ipTV_stream::startLLOD($streamID, $streamInfo, $streamInfo['parent_id'] ? array() : $streamArguments, $rForceSource);
            }
        } else {
            $rData = ipTV_stream::startStream($streamID, $rTotalCalls < MONITOR_CALLS, $rForceSource);
        }

        // Process stream start result
        if (is_numeric($rData) && $rData == 0) {
            $streamStarted = true;
            $rMaxFails++;
            if (ipTV_lib::$settings['stop_failures'] > 0 && $rMaxFails == ipTV_lib::$settings['stop_failures']) {
                echo 'Failure limit reached, exiting.' . "\n";
                exit();
            }
        }

        if (!$rData) {
            exit();
        }

        // Update stream information if started successfully
        if (!$streamStarted) {
            $rPID = intval($rData['main_pid']);
            if ($rPID) {
                file_put_contents(STREAMS_PATH . $streamID . '_.pid', $rPID);
            }
            $rPlaylist = $rData['playlist'];
            $rDelay = $rData['delay_enabled'];
            $streamInfo['delay_available_at'] = $rData['delay_start_at'];
            $rParentID = $rData['parent_id'];
            
            if ($rParentID <= 0) {
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

            // Verify stream is actually running
            if (!ipTV_streaming::isStreamRunning($rPID, $streamID)) {
                echo 'Stream failed to start properly.' . "\n";
                ipTV_streaming::streamLog($streamID, SERVER_ID, 'STREAM_START_FAIL', $rCurrentSource);
                continue;
            }

            // Wait for playlist to be created
            $playlistWaitTime = 0;
            while (!file_exists($rPlaylist) && $playlistWaitTime < 30) {
                usleep(100000); // Wait 100ms
                $playlistWaitTime++;
            }

            if (!file_exists($rPlaylist)) {
                echo 'Playlist file not created.' . "\n";
                ipTV_streaming::streamLog($streamID, SERVER_ID, 'PLAYLIST_CREATE_FAIL', $rCurrentSource);
                continue;
            }
        }
    }
}
