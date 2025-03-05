<?php

register_shutdown_function('shutdown');
set_time_limit(0);
require 'init.php';
$rDeny = true;
global $rDeny;

if (empty(CoreUtilities::$request['password']) || CoreUtilities::$request['password'] != CoreUtilities::$settings['live_streaming_pass']) {
    generateError('INVALID_API_PASSWORD');
}

unset(CoreUtilities::$request['password']);

if (!in_array($rIP, CoreUtilities::$allowedIPs)) {
    generateError('API_IP_NOT_ALLOWED');
}

header('Access-Control-Allow-Origin: *');
$action = (!empty(CoreUtilities::$request['action']) ? CoreUtilities::$request['action'] : '');
$rDeny = false;

switch ($action) {
    // case 'view_log':
    //     if (empty(CoreUtilities::$request['stream_id'])) {
    //         break;
    //     }
    //     $streamID = intval(CoreUtilities::$request['stream_id']);
    //     if (file_exists(STREAMS_PATH . $streamID . '.errors')) {
    //         echo file_get_contents(STREAMS_PATH . $streamID . '.errors');
    //     } elseif (file_exists(VOD_PATH . $streamID . '.errors')) {
    //         echo file_get_contents(VOD_PATH . $streamID . '.errors');
    //     }
    //     exit();

    // case 'reload_epg':
    //     shell_exec(PHP_BIN . ' ' . CRON_PATH . 'epg.php >/dev/null 2>/dev/null &');
    //     break;
        
    case 'vod':
        if (!empty(CoreUtilities::$request['stream_ids']) && !empty(CoreUtilities::$request['function'])) {
            $streamIDs = array_map('intval', CoreUtilities::$request['stream_ids']);
            $function = CoreUtilities::$request['function'];
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
        if (!empty(CoreUtilities::$request['stream_ids']) && !empty(CoreUtilities::$request['function'])) {
            $streamIDs = array_map('intval', CoreUtilities::$request['stream_ids']);
            $function = CoreUtilities::$request['function'];

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
        echo json_encode(ipTV_servers::getStats());
        exit();

    case 'getDiff':
        if (!empty(CoreUtilities::$request['main_time'])) {
            $main_time = CoreUtilities::$request['main_time'];
            echo json_encode($main_time - time());
            die;
        }
        break;

    case 'pidsAreRunning':
        if (empty(CoreUtilities::$request['pids']) && !is_array(CoreUtilities::$request['pids']) && empty(CoreUtilities::$request['program'])) {
            break;
        }

        $PIDs = array_map('intval', CoreUtilities::$request['pids']);
        $program = CoreUtilities::$request['program'];
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
        if (empty(CoreUtilities::$request['filename'])) {
            break;
        }

        $filename = CoreUtilities::$request['filename'];
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
                    echo stream_get_line($fp, (intval(CoreUtilities::$settings['read_buffer_size']) ?: 8192));
                }
                fclose($fp);
            }

            exit();
        }
        exit(json_encode(array('result' => false, 'error' => 'Invalid file extension.')));

    case 'viewDir':
        $dir = urldecode(CoreUtilities::$request['dir']);
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

    case 'redirect_connection':
        if (!empty(CoreUtilities::$request['activity_id']) && !empty(CoreUtilities::$request['stream_id'])) {
            CoreUtilities::$request['type'] = 'redirect';
            file_put_contents(SIGNALS_PATH . CoreUtilities::$request['uuid'], json_encode(CoreUtilities::$request));
        }
        break;

    case 'signal_send':
        if (!empty(CoreUtilities::$request['message']) && !empty(CoreUtilities::$request['activity_id'])) {
            CoreUtilities::$request['type'] = 'signal';
            file_put_contents(SIGNALS_PATH . CoreUtilities::$request['uuid'], json_encode(CoreUtilities::$request));
        }
        break;

    case 'free_temp':
        exec('rm -rf ' . MAIN_DIR . 'tmp/*');
        shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache.php');
        echo json_encode(array('result' => true));
        break;

    case 'free_streams':
        exec('rm ' . MAIN_DIR . 'content/streams/*');
        echo json_encode(array('result' => true));
        break;

    case 'get_free_space':
        exec('df -h', $rReturn);
        echo json_encode($rReturn);
        exit();

    case 'get_pids':
        exec('ps -e -o user,pid,%cpu,%mem,vsz,rss,tty,stat,time,etime,command', $rReturn);
        echo json_encode($rReturn);
        exit();
        
    case 'kill_pid':
        $rPID = intval(CoreUtilities::$request['pid']);
        if ($rPID > 0) {
            posix_kill($rPID, 9);
            echo json_encode(array('result' => true));
        } else {
            echo json_encode(array('result' => false));
        }
        break;

    default:
        exit(json_encode(array('result' => false)));
}

function shutdown() {
    global $rDeny;

    if ($rDeny) {
        CoreUtilities::checkFlood();
    }
}
