<?php

// Register shutdown function to handle cleanup
register_shutdown_function('shutdown');
set_time_limit(0);
require 'init.php';

global $rDeny;
$rDeny = true;

// Validate API password
if (empty(ipTV_lib::$request['password']) || ipTV_lib::$request['password'] !== ipTV_lib::$settings['live_streaming_pass']) {
    generateError('INVALID_API_PASSWORD');
}

unset(ipTV_lib::$request['password']); // Remove password from memory for security

// Validate IP address
if (!in_array($rIP, ipTV_lib::$allowedIPs)) {
    generateError('API_IP_NOT_ALLOWED');
}

// Allow CORS
header('Access-Control-Allow-Origin: *');

$action = ipTV_lib::$request['action'] ?? '';
$rDeny = false; // Reset deny flag after authentication

switch ($action) {
    
    case 'vod':
        if (empty(ipTV_lib::$request['stream_ids']) || empty(ipTV_lib::$request['function'])) {
            exit(json_encode(['result' => false, 'error' => 'Missing parameters']));
        }

        $streamIDs = array_map('intval', ipTV_lib::$request['stream_ids']);
        $function = ipTV_lib::$request['function'];

        // Validate function type
        if (!in_array($function, ['start', 'stop'])) {
            exit(json_encode(['result' => false, 'error' => 'Invalid function']));
        }

        // Process VOD actions
        foreach ($streamIDs as $streamID) {
            if ($function === 'start') {
                ipTV_stream::stopMovie($streamID); // Ensure previous instance is stopped
                ipTV_stream::startMovie($streamID);
            } elseif ($function === 'stop') {
                ipTV_stream::stopMovie($streamID);
            }
            usleep(50000); // Allow slight delay for execution
        }

        exit(json_encode(['result' => true]));

    case 'stream':
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
                    exit(json_encode(['result' => true]));

                case 'stop':
                    foreach ($streamIDs as $streamID) {
                        ipTV_stream::stopMovie($streamID);
                    }
                    exit(json_encode(['result' => true]));
            }
        }
        break;

    case 'stats':
        exit(json_encode(getStats()));

    case 'getDiff':
        if (!empty(ipTV_lib::$request['main_time'])) {
            exit(json_encode(ipTV_lib::$request['main_time'] - time()));
        }
        break;

    case 'pidsAreRunning':
        if (empty(ipTV_lib::$request['pids']) || !is_array(ipTV_lib::$request['pids']) || empty(ipTV_lib::$request['program'])) {
            break;
        }

        $PIDs = array_map('intval', ipTV_lib::$request['pids']);
        $program = ipTV_lib::$request['program'];
        $output = [];

        foreach ($PIDs as $rPID) {
            $output[$rPID] = file_exists("/proc/$rPID") &&
                             is_readable("/proc/$rPID/exe") &&
                             strpos(basename(readlink("/proc/$rPID/exe")), basename($program)) === 0;
        }

        exit(json_encode($output));

    case 'getFile':
        if (empty(ipTV_lib::$request['filename'])) {
            break;
        }

        $filename = ipTV_lib::$request['filename'];
        $allowedExtensions = ['log', 'tar.gz', 'gz', 'zip', 'm3u8', 'mp4', 'mkv', 'avi', 'mpg', 'flv', '3gp', 'm4v', 'wmv', 'mov', 'ts', 'srt', 'sub', 'sbv', 'jpg', 'png', 'bmp', 'jpeg', 'gif', 'tif'];

        if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowedExtensions) &&
            file_exists($filename) && is_readable($filename)) {

            header('Content-Type: application/octet-stream');
            $size = filesize($filename);
            header("Accept-Ranges: bytes");

            if (isset($_SERVER['HTTP_RANGE'])) {
                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                list($start, $end) = explode('-', $range);
                $start = $start ? intval($start) : 0;
                $end = $end ? intval($end) : ($size - 1);

                if ($end >= $size || $start > $end) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    exit();
                }

                header("Content-Range: bytes $start-$end/$size");
                header('HTTP/1.1 206 Partial Content');
            } else {
                $start = 0;
                $end = $size - 1;
            }

            header("Content-Length: " . ($end - $start + 1));

            $fp = fopen($filename, 'rb');
            fseek($fp, $start);
            while (!feof($fp) && ftell($fp) <= $end) {
                echo fread($fp, 8192);
            }
            fclose($fp);
            exit();
        }

        exit(json_encode(['result' => false, 'error' => 'Invalid file extension.']));

    case 'free_temp':
        exec('rm -rf ' . MAIN_DIR . 'tmp/*');
        shell_exec(PHP_BIN . ' ' . CRON_PATH . 'cache.php');
        exit(json_encode(['result' => true]));

    case 'free_streams':
        exec('rm ' . MAIN_DIR . 'content/streams/*');
        exit(json_encode(['result' => true]));

    case 'get_free_space':
        exec('df -h', $output);
        exit(json_encode($output));

    case 'get_pids':
        exec('ps -e -o user,pid,%cpu,%mem,vsz,rss,tty,stat,time,etime,command', $output);
        exit(json_encode($output));

    case 'kill_pid':
        $rPID = intval(ipTV_lib::$request['pid']);
        if ($rPID > 0) {
            posix_kill($rPID, 9);
            exit(json_encode(['result' => true]));
        } else {
            exit(json_encode(['result' => false]));
        }

	case 'redirect_connection':
		if (empty(ipTV_lib::$request['activity_id']) || empty(ipTV_lib::$request['stream_id']) || empty(ipTV_lib::$request['uuid'])) {
			exit(json_encode(['result' => false, 'error' => 'Missing required parameters']));
		}

		// Set request type as redirect
		ipTV_lib::$request['type'] = 'redirect';

		// Save request data to the appropriate signals file
		$filePath = SIGNALS_PATH . ipTV_lib::$request['uuid'];
		if (file_put_contents($filePath, json_encode(ipTV_lib::$request)) === false) {
			exit(json_encode(['result' => false, 'error' => 'Failed to write to signals file']));
		}

		exit(json_encode(['result' => true]));

    case 'signal_send':
        if (!empty(ipTV_lib::$request['activity_id']) && !empty(ipTV_lib::$request['stream_id'])) {
            ipTV_lib::$request['type'] = ($action === 'redirect_connection') ? 'redirect' : 'signal';
            file_put_contents(SIGNALS_PATH . ipTV_lib::$request['uuid'], json_encode(ipTV_lib::$request));
        }
        break;

    case 'viewDir':
        $dir = urldecode(ipTV_lib::$request['dir']);
        if (file_exists($dir) && is_readable($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            natcasesort($files);

            if (!empty($files)) {
                echo '<ul class="jqueryFileTree" style="display: none;">';
                foreach ($files as $file) {
                    $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($filePath)) {
                        echo '<li class="directory collapsed"><a href="#" rel="' . htmlentities($filePath) . '/">' . htmlentities($file) . '</a></li>';
                    } else {
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        echo "<li class=\"file ext_{$ext}\"><a href=\"#\" rel=\"" . htmlentities($filePath) . '">' . htmlentities($file) . '</a></li>';
                    }
                }
                echo '</ul>';
            }
        }
        die;

    default:
        exit(json_encode(['result' => false]));
}

/**
 * Shutdown function to handle flood prevention
 */
function shutdown() {
    global $rDeny;
    if ($rDeny) {
        checkFlood();
    }
}