<?php

register_shutdown_function('shutdown');
set_time_limit(0);
require '../init.php';
header('Access-Control-Allow-Origin: *');
$streaming_block = true;
if (!isset(ipTV_lib::$request['start']) || !isset(ipTV_lib::$request['duration']) || !isset(ipTV_lib::$request['stream'])) {
    die('Missing parameters.');
}
if (ipTV_lib::$settings['use_buffer'] == 0) {
    header('X-Accel-Buffering: no');
}
$geoip = MaxMind\Db\Reader(GEOIP2COUNTRY_FILENAME);
$activity_id = 0;
$connection_speed_file = null;
$container_priority = null;
$username = empty(ipTV_lib::$request['username']) ? '' : ipTV_lib::$request['username'];
$password = empty(ipTV_lib::$request['password']) ? '' : ipTV_lib::$request['password'];
$stream_id = ipTV_lib::$request['stream'];
$size = 0;
if (!is_numeric($stream_id) && stristr($stream_id, '_')) {
    list($stream_id, $pos, $size) = explode('_', $stream_id);
    $stream_id = intval($stream_id);
    $stream_item = intval($pos);
    $size = intval($size);
    ipTV_lib::$request['extension'] = 'm3u8';
}
$user_ip = ipTV_streaming::getUserIP();
$user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : htmlentities(trim($_SERVER['HTTP_USER_AGENT']));
$geoip_country_code = $geoip->getWithPrefixLen($user_ip)[0]['registered_country']['iso_code'];
$geoip->close();
$play_token = empty(ipTV_lib::$request['play_token']) ? null : ipTV_lib::$request['play_token'];
if ($user_info = ipTV_streaming::getUserInfo(null, $username, $password, true, false, true, array(), false, $user_ip, $user_agent, array(), $play_token, $stream_id)) {
    if (isset($user_info['mag_invalid_token'])) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'MAG_TOKEN_INVALID', $user_ip);
        die;
    }
    if (!is_null($user_info['exp_date']) && time() >= $user_info['exp_date']) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'USER_EXPIRED', $user_ip);
        ipTV_streaming::showVideo($user_info['is_restreamer'], 'show_expired_video', 'expired_video_path');
        die;
    }
    if ($user_info['admin_enabled'] == 0) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'USER_BAN', $user_ip);
        ipTV_streaming::showVideo($user_info['is_restreamer'], 'show_banned_video', 'banned_video_path');
        die;
    }
    if ($user_info['enabled'] == 0) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'USER_DISABLED', $user_ip);
        ipTV_streaming::showVideo($user_info['is_restreamer'], 'show_banned_video', 'banned_video_path');
        die;
    }
    if (empty($user_agent) && ipTV_lib::$settings['disallow_empty_user_agents'] == 1) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'EMPTY_UA', $user_ip);
        die;
    }
    if (!empty($user_info['allowed_ips']) && !in_array($user_ip, array_map('gethostbyname', $user_info['allowed_ips']))) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'IP_BAN', $user_ip);
        die;
    }
    if (!empty($geoip_country_code)) {
        $forced_country = !empty($user_info['forced_country']) ? true : false;
        if ($forced_country && $user_info['forced_country'] != 'ALL' && $geoip_country_code != $user_info['forced_country']) {
            ipTV_streaming::clientLog($stream_id, $user_info['id'], 'COUNTRY_DISALLOW', $user_ip);
            die;
        }
        if (!$forced_country && !in_array('ALL', ipTV_lib::$settings['allow_countries']) && !in_array($geoip_country_code, ipTV_lib::$settings['allow_countries'])) {
            ipTV_streaming::clientLog($stream_id, $user_info['id'], 'COUNTRY_DISALLOW', $user_ip);
            die;
        }
    }
    if (!empty($user_info['allowed_ua']) && !in_array($user_agent, $user_info['allowed_ua'])) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'USER_AGENT_BAN', $user_ip);
        die;
    }
    if (ipTV_streaming::checkIsCracked($user_ip)) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'CRACKED', $user_ip);
        die;
    }
    if (isset($user_info['ip_limit_reached'])) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'USER_ALREADY_CONNECTED', $user_ip);
        die;
    }
    $streaming_block = false;
    if (!in_array($stream_id, $user_info['channel_ids'])) {
        http_response_code(406);
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'NOT_IN_BOUQUET', $user_ip);
        die;
    }
    if ($user_info['max_connections'] != 0) {
        if (!empty($user_info['pair_line_info'])) {
            if ($user_info['pair_line_info']['max_connections'] != 0) {
                if ($user_info['pair_line_info']['active_cons'] >= $user_info['pair_line_info']['max_connections']) {
                    ipTV_streaming::closeLastCon($user_info['pair_id'], $user_info['pair_line_info']['max_connections']);
                }
            }
        }
        if ($user_info['active_cons'] >= $user_info['max_connections']) {
            ipTV_streaming::closeLastCon($user_info['id'], $user_info['max_connections']);
        }
    }
    if ($user_info['isp_violate'] == 1) {
        http_response_code(401);
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'ISP_LOCK_FAILED', $user_ip, json_encode(array('old' => $user_info['isp_desc'], 'new' => $user_info['con_isp_name'])));
        die;
    }
    if ($user_info['isp_is_server'] == 1) {
        ipTV_streaming::clientLog($stream_id, $user_info['id'], 'CON_SVP', $user_ip, json_encode(array('user_agent' => $user_agent, 'isp' => $user_info['con_isp_name'], 'type' => $user_info['con_isp_type'])), true);
        http_response_code(401);
        die;
    }
} else {
    die;
}
$channel_info = ipTV_streaming::channelInfo($stream_id, 'ts', $user_info, $user_ip, $geoip_country_code, '', $user_info['con_isp_name'], 'archive');
if (empty($channel_info)) {
    http_response_code(403);
    die;
}
$start = ipTV_lib::$request['start'];
$duration = intval(ipTV_lib::$request['duration']);
if (!is_numeric($start)) {
    if (substr_count($start, '-') == 1) {
        list($date, $job) = explode('-', $start);
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        $minutes = 0;
        $Ed62709841469f20fe0f7a17a4268692 = $job;
    } else {
        list($date, $job) = explode(':', $start);
        list($year, $month, $day) = explode('-', $date);
        list($Ed62709841469f20fe0f7a17a4268692, $minutes) = explode('-', $job);
    }
    $start_timestamp = mktime($Ed62709841469f20fe0f7a17a4268692, $minutes, 0, $month, $day, $year);
} else {
    $duration *= 24;
    $files = array_values(array_filter(explode('', shell_exec('ls -tr ' . ARCHIVE_PATH . $stream_id . ' | sed -e \'s/\\s\\+/\\n/g\''))));
    $start_time = $start * 24;
    if (count($files) >= $start_time) {
        $start_time = $files[count($files) - $start_time];
    } else {
        $start_time = $files[0];
    }
    if (preg_match('/(.*)-(.*)-(.*):(.*)\\./', $start_time, $matches)) {
        $start_timestamp = mktime($matches[4], 0, 0, $matches[2], $matches[3], $matches[1]);
    } else {
        die;
    }
}
$queue = array();
$file = ARCHIVE_PATH . $stream_id . '/' . date('Y-m-d:H-i', $start_timestamp) . '.ts';
if (empty($stream_id) || empty($start_timestamp) || empty($duration)) {
    header('HTTP/1.1 400 Bad Request');
    die;
}
if (!file_exists($file) || !is_readable($file)) {
    header('HTTP/1.1 404 Not Found');
    die;
}
$queue = array();
$index = 0;
while ($index < $duration) {
    $file = ARCHIVE_PATH . $stream_id . '/' . date('Y-m-d:H-i', $start_timestamp + $index * 60) . '.ts';
    if (file_exists($file)) {
        $queue[] = array('filename' => $file, 'filesize' => filesize($file));
    }
    $index++;
}
if (!empty($queue)) {
    $date = time();
    $container_priority = 'TV Archive';
    switch (ipTV_lib::$request['extension']) {
        case 'm3u8':
            if (isset($stream_item)) {
                if (!empty($queue[$stream_item]) && file_exists($queue[$stream_item]['filename']) && $queue[$stream_item]['filesize'] == $size) {
                    $offset = 0;
                    $length = $queue[$stream_item]['filesize'];
                    if ($stream_item == 0) {
                        $offset = $queue[$stream_item]['filesize'] * 0.3;
                        $length = $queue[$stream_item]['filesize'] - $offset;
                    }
                    header('Content-Type: video/mp2t');
                    header('Content-Length: ' . $length);
                    $fp = fopen($queue[$stream_item]['filename'], 'r');
                    fseek($fp, $offset);
                    while (!feof($fp)) {
                        echo stream_get_line($fp, ipTV_lib::$settings['read_buffer_size']);
                    }
                    fclose($fp);
                }
                die;
            }
            $ipTV_db->query('SELECT activity_id,hls_end FROM `lines_live` WHERE `user_id` = \'%d\' AND `server_id` = \'%d\' AND `container` = \'hls\' AND `user_ip` = \'%s\' AND `user_agent` = \'%s\' AND `stream_id` = \'%d\'', $user_info['id'], SERVER_ID, $user_ip, $user_agent, $stream_id);
            if ($ipTV_db->num_rows() == 0) {
                if ($user_info['max_connections'] != 0) {
                    $ipTV_db->query('UPDATE `lines_live` SET `hls_end` = 1 WHERE `user_id` = \'%d\' AND `container` = \'hls\'', $user_info['id']);
                }
                $ipTV_db->query('INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`,`hls_last_read`) VALUES(\'%d\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\',\'%d\')', $user_info['id'], $stream_id, SERVER_ID, $user_agent, $user_ip, $container_priority . ' (HLS)', getmypid(), $date, $geoip_country_code, $user_info['con_isp_name'], $external_device, $date);
                $activity_id = $ipTV_db->last_insert_id();
            } else {
                $row = $ipTV_db->get_row();
                if ($row['hls_end'] == 1) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
                    die;
                }
                $activity_id = $row['activity_id'];
                $ipTV_db->query('UPDATE `lines_live` SET `hls_last_read` = \'%d\' WHERE `activity_id` = \'%d\'', time(), $row['activity_id']);
            }
            $ipTV_db->close_mysql();
            $output = '#EXTM3U';
            $output .= '#EXT-X-VERSION:3';
            $output .= '#EXT-X-TARGETDURATION:60';
            $output .= '#EXT-X-MEDIA-SEQUENCE:0';
            $output .= '#EXT-X-PLAYLIST-TYPE:VOD';
            foreach ($queue as $k => $item) {
                $output .= '#EXTINF:60.0,';
                $output .= "/timeshift/{$username}/{$password}/{$duration}/{$start}/{$stream_id}_{$k}_" . $item['filesize'] . '.ts';
            }
            $output .= '#EXT-X-ENDLIST';
            header('Content-Type: application/x-mpegurl');
            header('Content-Length: ' . strlen($output));
            echo $output;
            die;
            break;
        default:
            header('Content-Type: video/mp2t');
            if (!empty($user_info)) {
                $ipTV_db->query('INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`) VALUES(\'%d\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\',\'%d\',\'%d\',\'%s\',\'%s\',\'%s\')', $user_info['id'], $stream_id, SERVER_ID, $user_agent, $user_ip, $container_priority, getmypid(), $date, $geoip_country_code, $user_info['con_isp_name'], $external_device);
                $activity_id = $ipTV_db->last_insert_id();
                $connection_speed_file = TMP_PATH . $activity_id . '.con';
                $ipTV_db->close_mysql();
            }
            $length = $size = queueSize($queue);
            $bitrate = $size * 0.008 / ($duration * 60);
            header("Accept-Ranges: 0-{$length}");
            $start = 0;
            $end = $size - 1;
            if (isset($_SERVER['HTTP_RANGE'])) {
                $c_start = $start;
                $c_end = $end;
                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                if (strpos($range, ',') !== false) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes {$start}-{$end}/{$size}");
                    die;
                }
                if ($range == '-') {
                    $c_start = $size - substr($range, 1);
                } else {
                    $range = explode('-', $range);
                    $c_start = $range[0];
                    $c_end = isset($range[1]) && is_numeric($range[1]) ? $range[1] : $size;
                }
                $c_end = $c_end > $end ? $end : $c_end;
                if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes {$start}-{$end}/{$size}");
                    die;
                }
                $start = $c_start;
                $end = $c_end;
                $length = $end - $start + 1;
                header('HTTP/1.1 206 Partial Content');
            }
            header("Content-Range: bytes {$start}-{$end}/{$size}");
            header('Content-Length: ' . $length);
            $b3fcd87510baa9521882b459861dcb64 = 0;
            if ($start > 0) {
                $b3fcd87510baa9521882b459861dcb64 = floor($start / ($size / count($queue)));
            }
            $c77e7ff2c5d6b14d931b3344c54e0cc5 = false;
            $offset = 0;
            $total_file_size = 0;
            $total_bitrate = $bitrate * 125;
            $total_bitrate += $total_bitrate * ipTV_lib::$settings['vod_bitrate_plus'] * 0.01;
            $time_start = time();
            $movie_file_size = 0;
            $buffer = ipTV_lib::$settings['read_buffer_size'];
            $index = 0;
            $limit_count = 0;
            if (ipTV_lib::$settings['vod_limit_at'] > 0) {
                $vod_limit_at_length = intval($size * ipTV_lib::$settings['vod_limit_at'] / 100);
            } else {
                $vod_limit_at_length = $size;
            }
            $check_buffer = false;
            foreach ($queue as $k => $item) {
                $total_file_size += $item['filesize'];
                if (!$c77e7ff2c5d6b14d931b3344c54e0cc5 && $b3fcd87510baa9521882b459861dcb64 > 0) {
                    if ($b3fcd87510baa9521882b459861dcb64 > $k) {
                    } else {
                        $c77e7ff2c5d6b14d931b3344c54e0cc5 = true;
                        $offset = $start - $total_file_size;
                    }
                }
                $fp = fopen($item['filename'], 'rb');
                fseek($fp, $offset);
                while (!feof($fp)) {
                    $pos = ftell($fp);
                    $response = stream_get_line($fp, $buffer);
                    echo $response;
                    $movie_file_size += strlen($response);
                    ++$index;
                    if (!$check_buffer && $limit_count * $buffer >= $vod_limit_at_length) {
                        $check_buffer = true;
                    } else {
                        ++$limit_count;
                    }
                    if ($total_bitrate > 0 && $check_buffer && $index >= ceil($total_bitrate / $buffer)) {
                        sleep(1);
                        $index = 0;
                    }
                    if (time() - $time_start >= 30) {
                        file_put_contents($connection_speed_file, intval($movie_file_size / 1024 / 30));
                        $time_start = time();
                        $movie_file_size = 0;
                    }
                }
                if (is_resource($fp)) {
                    fclose($fp);
                }
                $offset = 0;
            }
    }
}
function queueSize($queue) {
    $length = 0;
    foreach ($queue as $item) {
        $length += $item['filesize'];
    }
    return $length;
}
function shutdown() {
    global $ipTV_db, $streaming_block, $activity_id, $connection_speed_file, $user_info, $container_priority, $stream_id, $user_agent, $user_ip, $geoip_country_code, $external_device, $date;
    if ($streaming_block) {
        CheckFlood();
        http_response_code(401);
    }
    $ipTV_db->close_mysql();
    if ($activity_id !== false) {
        ipTV_streaming::closeAndTransfer($activity_id);
        ipTV_streaming::writeOfflineActivity(SERVER_ID, $user_info['id'], $stream_id, $date, $user_agent, $user_ip, $container_priority, $geoip_country_code, $user_info['con_isp_name'], $external_device);
        ipTV_lib::unlinkFile($connection_speed_file);
    }
    fastcgi_finish_request();
    posix_kill(getmypid(), 9);
}
