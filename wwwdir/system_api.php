<?php

register_shutdown_function('shutdown');
set_time_limit(0);
require 'init.php';
$rDeny = true;
global $rDeny;

if (empty(ipTV_lib::$request['password']) || ipTV_lib::$request['password'] != ipTV_lib::$settings['live_streaming_pass']) {
    generateError('INVALID_API_PASSWORD');
}

unset(ipTV_lib::$request['password']);

if (!in_array($rIP, ipTV_lib::$allowedIPs)) {
    generateError('API_IP_NOT_ALLOWED');
}

header('Access-Control-Allow-Origin: *');
$action = (!empty(ipTV_lib::$request['action']) ? ipTV_lib::$request['action'] : '');
$rDeny = false;

switch ($action) {
    case 'view_log':
        if (empty(ipTV_lib::$request['stream_id'])) {
            break;
        }
        $streamID = intval(ipTV_lib::$request['stream_id']);
        if (file_exists(STREAMS_PATH . $streamID . '.errors')) {
            echo file_get_contents(STREAMS_PATH . $streamID . '.errors');
        } elseif (file_exists(VOD_PATH . $streamID . '.errors')) {
            echo file_get_contents(VOD_PATH . $streamID . '.errors');
        }
        exit();
    case 'reload_epg':
        shell_exec(PHP_BIN . ' ' . CRON_PATH . 'epg.php >/dev/null 2>/dev/null &');
        break;
    case 'vod':
        if (!empty(ipTV_lib::$request['stream_ids']) && !empty(ipTV_lib::$request['function'])) {
            $streamIDs = array_map('intval', ipTV_lib::$request['stream_ids']);
            $function = ipTV_lib::$request['function'];
            switch ($function) {
                case 'start':
                    foreach ($streamIDs as $streamID) {
                        ipTV_stream::stopMovie($streamID);
                        ipTV_stream::startMovie($streamID);
                        usleep(50000);
                    }
                    echo json_encode(array('result' => true));
                    exit();
                case 'stop':
                    foreach ($streamIDs as $streamID) {
                        ipTV_stream::stopMovie($streamID);
                    }
                    echo json_encode(array('result' => true));
                    exit();
            }
        }
    case 'stream':
        if (!empty(ipTV_lib::$request['stream_ids']) && !empty(ipTV_lib::$request['function'])) {
            $streamIDs = array_map('intval', ipTV_lib::$request['stream_ids']);
            $function = ipTV_lib::$request['function'];

            switch ($function) {
                case 'start':
                    foreach ($streamIDs as $streamID) {
                        if (ipTV_stream::startMonitor($streamID, true)) {
                            usleep(50000);
                        } else {
                            echo json_encode(array('result' => false));
                            exit();
                        }
                    }
                    echo json_encode(array('result' => true));
                    exit();
                case 'stop':
                    foreach ($streamIDs as $streamID) {
                        ipTV_stream::stopStream($streamID, true);
                    }
                    echo json_encode(array('result' => true));
                    exit();
                default:
                    break;
            }
        }
        break;
    case 'stats':
        echo json_encode(getStats());
        exit();
    case 'BackgroundCLI':
        if (!empty(ipTV_lib::$request['cmds'])) {
            $cmds = ipTV_lib::$request['cmds'];
            $output = array();
            foreach ($cmds as $key => $cmd) {
                if (!is_array($cmd)) {
                    $output[$key] = shell_exec($cmd);
                    usleep(ipTV_lib::$settings['stream_start_delay']);
                } else {
                    foreach ($cmd as $k2 => $cm) {
                        $output[$key][$k2] = shell_exec($cm);
                        usleep(ipTV_lib::$settings['stream_start_delay']);
                    }
                }
            }
            echo json_encode($output);
        }
        die;
    case 'getDiff':
        if (!empty(ipTV_lib::$request['main_time'])) {
            $main_time = ipTV_lib::$request['main_time'];
            echo json_encode($main_time - time());
            die;
        }
        break;
    case 'pidsAreRunning':
        if (empty(ipTV_lib::$request['pids']) && !is_array(ipTV_lib::$request['pids']) && empty(ipTV_lib::$request['program'])) {
            break;
        }

        $PIDs = array_map('intval', ipTV_lib::$request['pids']);
        $program = ipTV_lib::$request['program'];
        $output = array();

        foreach ($PIDs as $rPID) {
            $output[$rPID] = false;

            if (file_exists('/proc/' . $rPID) && is_readable('/proc/' . $rPID . '/exe') && strpos(basename(readlink('/proc/' . $rPID . '/exe')), basename($program)) === 0) {
                $output[$rPID] = true;
            }
        }
        echo json_encode($output);
        exit();
    case 'getFile':
        if (empty(ipTV_lib::$request['filename'])) {
            break;
        }

        $filename = ipTV_lib::$request['filename'];
        if (in_array(strtolower(pathinfo($filename)['extension']), array('log', 'tar.gz', 'gz', 'zip', 'm3u8', 'mp4', 'mkv', 'avi', 'mpg', 'flv', '3gp', 'm4v', 'wmv', 'mov', 'ts', 'srt', 'sub', 'sbv', 'jpg', 'png', 'bmp', 'jpeg', 'gif', 'tif'))) {
            if (file_exists($filename) && is_readable($filename)) {
                header('Content-Type: application/octet-stream');
                $fp = @fopen($filename, 'rb');
                $size = filesize($filename);
                $length = $size;
                $start = 0;
                $end = $size - 1;
                header("Accept-Ranges: 0-{$length}");
                if (isset($_SERVER['HTTP_RANGE'])) {
                    $rRangeEnd = $end;
                    list(, $rRange) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                    if (strpos($rRange, ',') === false) {
                        if ($rRange == '-') {
                            $rRangeStart = $size - substr($rRange, 1);
                        } else {
                            $rRange = explode('-', $rRange);
                            $rRangeStart = $rRange[0];
                            $rRangeEnd = (isset($rRange[1]) && is_numeric($rRange[1]) ? $rRange[1] : $size);
                        }

                        $rRangeEnd = ($end < $rRangeEnd ? $end : $rRangeEnd);

                        if (!($rRangeEnd < $rRangeStart || $size - 1 < $rRangeStart || $size <= $rRangeEnd)) {
                            $start = $rRangeStart;
                            $end = $rRangeEnd;
                            $length = $end - $start + 1;
                            fseek($fp, $start);
                            header('HTTP/1.1 206 Partial Content');
                        } else {
                            header('HTTP/1.1 416 Requested Range Not Satisfiable');
                            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);

                            exit();
                        }
                    } else {
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);

                        exit();
                    }
                }

                header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
                header('Content-Length: ' . $length);

                while (!feof($fp) && ftell($fp) <= $end) {
                    echo stream_get_line($fp, (intval(ipTV_lib::$settings['read_buffer_size']) ?: 8192));
                }
                fclose($fp);
            }

            exit();
        }
        exit(json_encode(array('result' => false, 'error' => 'Invalid file extension.')));

    case 'viewDir':
        $dir = urldecode(ipTV_lib::$request['dir']);
        if (file_exists($dir)) {
            $files = scandir($dir);
            natcasesort($files);
            if (count($files) > 2) {
                echo '<ul class="jqueryFileTree" style="display: none;">';
                foreach ($files as $file) {
                    if (file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file) && is_readable($dir . $file)) {
                        echo '<li class="directory collapsed"><a href="#" rel="' . htmlentities($dir . $file) . '/">' . htmlentities($file) . '</a></li>';
                    }
                }
                foreach ($files as $file) {
                    if (file_exists($dir . $file) && $file != '.' && $file != '..' && !is_dir($dir . $file) && is_readable($dir . $file)) {
                        $ext = preg_replace('/^.*\\./', '', $file);
                        echo "<li class=\"file ext_{$ext}\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . '">' . htmlentities($file) . '</a></li>';
                    }
                }
                echo '</ul>';
            }
        }
        die;
    case 'runCMD':
        if (!empty(ipTV_lib::$request['command']) && in_array($user_ip, array("127.0.0.1", $_SERVER["SERVER_ADDR"]))) {
            exec($_POST['command'], $outputCMD);
            echo json_encode($outputCMD);
            die;
        }
        break;
    case 'redirect_connection':
        if (!empty(ipTV_lib::$request['activity_id']) && !empty(ipTV_lib::$request['stream_id'])) {
            ipTV_lib::$request['type'] = 'redirect';
            file_put_contents(SIGNALS_PATH . ipTV_lib::$request['uuid'], json_encode(ipTV_lib::$request));
        }
        break;
    case 'signal_send':
        if (!empty(ipTV_lib::$request['message']) && !empty(ipTV_lib::$request['activity_id'])) {
            ipTV_lib::$request['type'] = 'signal';
            file_put_contents(SIGNALS_PATH . ipTV_lib::$request['uuid'], json_encode(ipTV_lib::$request));
        }
        break;
    default:
        exit(json_encode(array('result' => false)));
}

function shutdown() {
    global $rDeny;

    if ($rDeny) {
        checkFlood();
    }
}
