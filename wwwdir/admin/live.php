<?php

register_shutdown_function('shutdown');
header('Access-Control-Allow-Origin: *');
set_time_limit(0);
require '../init.php';
$rIP = ipTV_streaming::getUserIP();
$rPID = getmypid();

if (CoreUtilities::$settings['use_buffer'] == 0) {
    header('X-Accel-Buffering: no');
}

if (!empty(CoreUtilities::$request['uitoken'])) {
    $rTokenData = json_decode(decryptData(CoreUtilities::$request['uitoken'], CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA), true);
    CoreUtilities::$request['stream'] = $rTokenData['stream_id'];
    CoreUtilities::$request['extension'] = 'm3u8';
    $rIPMatch = (CoreUtilities::$settings['ip_subnet_match'] ? implode('.', array_slice(explode('.', $rTokenData['ip']), 0, -1)) == implode('.', array_slice(explode('.', ipTV_streaming::getUserIP()), 0, -1)) : $rTokenData['ip'] == ipTV_streaming::getUserIP());

    if ($rTokenData['expires'] < time() && !$rIPMatch) {
        generate404();
    }

    $rPrebuffer = CoreUtilities::$SegmentsSettings['seg_time'];
} else {
    if (empty(CoreUtilities::$request['password']) || CoreUtilities::$settings['live_streaming_pass'] != CoreUtilities::$request['password']) {
        generate404();
    } else {
        if (!in_array($rIP, CoreUtilities::getAllowedIPs())) {
            generate404();
        } else {
            $rPrebuffer = (isset(CoreUtilities::$request['prebuffer']) ? CoreUtilities::$SegmentsSettings['seg_time'] : 0);

            foreach (getallheaders() as $rKey => $rValue) {
                if (strtoupper($rKey) == 'X-XTREAMUI-PREBUFFER') {
                    $rPrebuffer = CoreUtilities::$SegmentsSettings['seg_time'];
                }
            }
        }
    }
}

$rPassword = CoreUtilities::$settings['live_streaming_pass'];
$rStreamID = intval(CoreUtilities::$request['stream']);
$rExtension = CoreUtilities::$request['extension'];
$rWaitTime = 20;
$ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t2.stream_id = t1.id AND t2.server_id = ? WHERE t1.`id` = ?', SERVER_ID, $rStreamID);

if (0 < $ipTV_db->num_rows()) {
    touch(SIGNALS_TMP_PATH . 'admin_' . intval($rStreamID));
    $rChannelInfo = $ipTV_db->get_row();
    $ipTV_db->close_mysql();

    if (file_exists(STREAMS_PATH . $rStreamID . '_.pid')) {
        $rChannelInfo['pid'] = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.pid'));
    }

    if (file_exists(STREAMS_PATH . $rStreamID . '_.monitor')) {
        $rChannelInfo['monitor_pid'] = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.monitor'));
    }

    if (CoreUtilities::$settings['on_demand_instant_off'] && $rChannelInfo['on_demand'] == 1) {
        ipTV_streaming::addToQueue($rStreamID, $rPID);
    }

    if (!ipTV_streaming::isStreamRunning($rChannelInfo['pid'], $rStreamID)) {
        $rChannelInfo['pid'] = null;

        if ($rChannelInfo['on_demand'] == 1) {
            if (!ipTV_streaming::checkMonitorRunning($rChannelInfo['monitor_pid'], $rStreamID)) {
                ipTV_stream::startMonitor($rStreamID);

                for ($rRetries = 0; !file_exists(STREAMS_PATH . intval($rStreamID) . '_.monitor') && $rRetries < 300; $rRetries++) {
                    usleep(10000);
                }
                $rChannelInfo['monitor_pid'] = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.monitor'));
            }
        } else {
            generate404();
        }
    }

    $rRetries = 0;
    $rPlaylist = STREAMS_PATH . $rStreamID . '_.m3u8';

    if ($rExtension == 'ts') {
        if (!file_exists($rPlaylist)) {
            $rFirstTS = STREAMS_PATH . $rStreamID . '_0.ts';
            $rFP = null;

            while ($rRetries < intval($rWaitTime) * 100) {
                if (file_exists($rFirstTS) || $rFP) {
                    $rFP = fopen($rFirstTS, 'r');
                }

                if (!($rFP && fread($rFP, 1))) {
                    usleep(10000);
                    $rRetries++;

                    break;
                }
            }

            if ($rFP) {
                fclose($rFP);
            }
        }
    } else {
        $rFirstTS = STREAMS_PATH . $rStreamID . '_.m3u8';

        while (!file_exists($rPlaylist) && !file_exists($rFirstTS) && $rRetries < intval($rWaitTime) * 100) {
            usleep(10000);
            $rRetries++;
        }
    }

    if ($rRetries == intval($rWaitTime) * 10) {
        if (isset(CoreUtilities::$request['odstart'])) {
            echo '0';
            exit();
        }

        generate404();
    } else {
        if (isset(CoreUtilities::$request['odstart'])) {
            echo '1';

            exit();
        }
    }

    if (!$rChannelInfo['pid']) {
        $rChannelInfo['pid'] = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.pid'));
    }

    switch ($rExtension) {
        case 'm3u8':
            if (ipTV_streaming::isValidStream($rPlaylist, $rChannelInfo['pid'])) {
                if (empty(CoreUtilities::$request['segment'])) {
                    if ($rSource = ipTV_streaming::generateAdminHLS($rPlaylist, $rPassword, $rStreamID, CoreUtilities::$request['uitoken'])) {
                        header('Content-Type: application/vnd.apple.mpegurl');
                        header('Content-Length: ' . strlen($rSource));
                        ob_end_flush();
                        echo $rSource;

                        exit();
                    }
                } else {
                    $rSegment = STREAMS_PATH . str_replace(array('\\', '/'), '', urldecode(CoreUtilities::$request['segment']));

                    if (file_exists($rSegment)) {
                        $rBytes = filesize($rSegment);
                        header('Content-Length: ' . $rBytes);
                        header('Content-Type: video/mp2t');
                        readfile($rSegment);

                        exit();
                    }
                }
            }

            break;

        default:
            header('Content-Type: video/mp2t');

            if (file_exists($rPlaylist)) {
                if (file_exists(STREAMS_PATH . $rStreamID . '_.dur')) {
                    $rDuration = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.dur'));

                    CoreUtilities::$SegmentsSettings['seg_time'] = max(CoreUtilities::$SegmentsSettings['seg_time'], $rDuration);
                }

                $rSegments = ipTV_streaming::getPlaylistSegments($rPlaylist, $rPrebuffer, CoreUtilities::$SegmentsSettings['seg_time']);
            } else {
                $rSegments = null;
            }

            if (!is_null($rSegments)) {
                if (is_array($rSegments)) {
                    $rBytes = 0;
                    $rStartTime = time();

                    foreach ($rSegments as $rSegment) {
                        if (file_exists(STREAMS_PATH . $rSegment)) {
                            $rBytes += readfile(STREAMS_PATH . $rSegment);
                        } else {
                            exit();
                        }
                    }
                    preg_match('/_(.*)\\./', array_pop($rSegments), $rCurrentSegment);
                    $rCurrent = $rCurrentSegment[1];
                } else {
                    $rCurrent = $rSegments;
                }
            } else {
                if (!file_exists($rPlaylist)) {
                    $rCurrent = -1;
                } else {
                    exit();
                }
            }

            $rFails = 0;
            $rTotalFails = CoreUtilities::$SegmentsSettings['seg_time'] * 2;

            if (($rTotalFails < intval(CoreUtilities::$settings['segment_wait_time']) ?: 20)) {
                $rTotalFails = (intval(CoreUtilities::$settings['segment_wait_time']) ?: 20);
            }

            while (true) {
                $rSegmentFile = sprintf('%d_%d.ts', $rStreamID, $rCurrent + 1);
                $rNextSegment = sprintf('%d_%d.ts', $rStreamID, $rCurrent + 2);
                $rChecks = 0;

                while (!file_exists(STREAMS_PATH . $rSegmentFile) && $rChecks <= $rTotalFails * 10) {
                    usleep(100000);
                    $rChecks++;
                }

                if (file_exists(STREAMS_PATH . $rSegmentFile)) {
                    if (empty($rChannelInfo['pid']) && file_exists(STREAMS_PATH . $rStreamID . '_.pid')) {
                        $rChannelInfo['pid'] = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.pid'));
                    }

                    $rFails = 0;
                    $rTimeStart = time();
                    $rFP = fopen(STREAMS_PATH . $rSegmentFile, 'r');

                    while ($rFails <= $rTotalFails && !file_exists(STREAMS_PATH . $rNextSegment)) {
                        $rData = stream_get_line($rFP, CoreUtilities::$settings['read_buffer_size']);

                        if (!empty($rData)) {
                            echo $rData;
                            $rData = '';
                            $rFails = 0;

                            break;
                        }

                        if (ipTV_streaming::isStreamRunning($rChannelInfo['pid'], $rStreamID)) {
                            sleep(1);
                            $rFails++;
                        }
                    }

                    if (ipTV_streaming::isStreamRunning($rChannelInfo['pid'], $rStreamID) && $rFails <= $rTotalFails && file_exists(STREAMS_PATH . $rSegmentFile) && is_resource($rFP)) {
                        $rSegmentSize = filesize(STREAMS_PATH . $rSegmentFile);
                    } else {
                        exit();
                    }
                } else {
                    exit();
                }
            }
    }
    $rRestSize = $rSegmentSize - ftell($rFP);

    if ($rRestSize > 0) {
        echo stream_get_line($rFP, $rRestSize);
    }

    fclose($rFP);
    $rFails = 0;
    $rCurrent++;
} else {
    generate404();
}

function shutdown() {
    global $ipTV_db;
    global $rChannelInfo;
    global $rPID;
    global $rStreamID;

    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }

    if (CoreUtilities::$settings['on_demand_instant_off'] && $rChannelInfo['on_demand'] == 1) {
        ipTV_streaming::removeFromQueue($rStreamID, $rPID);
    }
}
