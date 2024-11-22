<?php

register_shutdown_function('shutdown');
set_time_limit(0);
require '../init.php';
unset(ipTV_lib::$settings['watchdog_data']);
unset(ipTV_lib::$settings['server_hardware']);
header('Access-Control-Allow-Origin: *');

$rCreateExpiration = 60;
$IP = ipTV_streaming::getUserIP();
$rUserAgent = (empty($_SERVER['HTTP_USER_AGENT']) ? '' : htmlentities(trim($_SERVER['HTTP_USER_AGENT'])));
$rConSpeedFile = null;
$rDivergence = 0;
$rCloseCon = false;
$rPID = getmypid();
$rIsMag = false;

if (isset(ipTV_lib::$request['token'])) {
    $rTokenData = json_decode(decryptData(ipTV_lib::$request['token'], ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA), true);

    if (!is_array($rTokenData)) {
        ipTV_streaming::clientLog(0, 0, "LB_TOKEN_INVALID", $IP);
        generateError('LB_TOKEN_INVALID');
    }

    if (isset($rTokenData['expires']) && $rTokenData['expires'] < time() - intval(ipTV_lib::$Servers[SERVER_ID]['time_offset'])) {
        generateError('TOKEN_EXPIRED');
    }

    $rUsername = $rTokenData['username'];
    $rPassword = $rTokenData['password'];

    $streamID = intval($rTokenData['stream_id']);
    $rExtension = $rTokenData['extension'];
    $rType = $rTokenData['type'];
    $rChannelInfo = $rTokenData['channel_info'];
    $rUserInfo = $rTokenData['user_info'];
    $activityStart = $rTokenData['activity_start'];
    $rCountryCode = $rTokenData['country_code'];
    $rIsMag = $rTokenData['is_mag'];

    if (!empty($rTokenData['http_range']) || !isset($_SERVER['HTTP_RANGE'])) {
        $_SERVER['HTTP_RANGE'] = $rTokenData['http_range'];
    }
} else {
    generateError('NO_TOKEN_SPECIFIED');
}

$rRequest = VOD_PATH . $streamID . '.' . $rExtension;

if (!file_exists($rRequest)) {
    generateError('VOD_DOESNT_EXIST');
}

if (ipTV_lib::$settings['use_buffer'] == 0) {
    header('X-Accel-Buffering: no');
}

if ($rChannelInfo) {
    $serverID = ($rChannelInfo['redirect_id'] ?: SERVER_ID);

    $ipTV_db->query('SELECT `server_id`, `activity_id`, `pid`, `user_ip` FROM `lines_live` WHERE `uuid` = ?;', $rTokenData['uuid']);

    if (0 < $ipTV_db->num_rows()) {
        $rConnection = $ipTV_db->get_row();
    } else {
        if (!empty($_SERVER['HTTP_RANGE'])) {
            $ipTV_db->query('SELECT `server_id`, `activity_id`, `pid`, `user_ip` FROM `lines_live` WHERE `user_id` = ? AND `container` = ? AND `user_agent` = ? AND `stream_id` = ?;', $rUserInfo['id'], 'VOD', $rUserAgent, $streamID);

            if (0 < $ipTV_db->num_rows()) {
                $rConnection = $ipTV_db->get_row();
            }
        }
    }


    if (!$rConnection) {
        if (!(file_exists(CONS_TMP_PATH . $rTokenData['uuid']) || ($activityStart + $rCreateExpiration) - intval(ipTV_lib::$Servers[SERVER_ID]['time_offset']) >= time())) {
            generateError('TOKEN_EXPIRED');
        }
        $rResult = $ipTV_db->query('INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`uuid`,`date_start`,`geoip_country_code`,`isp`) VALUES(?,?,?,?,?,?,?,?,?,?,?);', $rUserInfo['id'], $streamID, $serverID, $rUserAgent, $IP, 'VOD', $rPID, $rTokenData['uuid'], $activityStart, $rCountryCode, $rUserInfo['con_isp_name']);
    } else {
        $IPMatch = (ipTV_lib::$settings['ip_subnet_match'] ? implode('.', array_slice(explode('.', $rConnection['user_ip']), 0, -1)) == implode('.', array_slice(explode('.', $IP), 0, -1)) : $rConnection['user_ip'] == $IP);

        if (!$IPMatch || ipTV_lib::$settings['restrict_same_ip']) {
            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'IP_MISMATCH', $IP);
            generateError('IP_MISMATCH');
        }

        if (ipTV_streaming::isProcessRunning($rConnection['pid'], 'php-fpm') && $rPID != $rConnection['pid'] && is_numeric($rConnection['pid']) && 0 < $rConnection['pid']) {
            if ($rConnection['server_id'] == SERVER_ID) {
                posix_kill(intval($rConnection['pid']), 9);
            } else {
                $ipTV_db->query('INSERT INTO `signals` (`pid`,`server_id`,`time`) VALUES(?,?,UNIX_TIMESTAMP())', $rConnection['pid'], $rConnection['server_id']);
            }
        }

        $rResult = $ipTV_db->query('UPDATE `lines_live` SET `hls_end` = 0, `pid` = ? WHERE `activity_id` = ?;', $rPID, $rConnection['activity_id']);
    }

    if (!$rResult) {
        ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'LINE_CREATE_FAIL', $IP);
        generateError('LINE_CREATE_FAIL');
    }

    ipTV_streaming::validateConnections($rUserInfo, $IP, $rUserAgent);

    $ipTV_db->close_mysql();

    $rCloseCon = true;

    touch(CONS_TMP_PATH . $rTokenData['uuid']);


    $rConSpeedFile = DIVERGENCE_TMP_PATH . $rTokenData['uuid'];

    switch ($rChannelInfo['target_container']) {
        case 'mp4':
        case 'm4v':
            header('Content-type: video/mp4');
            break;
        case 'mkv':
            header('Content-type: video/x-matroska');
            break;
        case 'avi':
            header('Content-type: video/x-msvideo');
            break;
        case '3gp':
            header('Content-type: video/3gpp');
            break;
        case 'flv':
            header('Content-type: video/x-flv');
            break;
        case 'wmv':
            header('Content-type: video/x-ms-wmv');
            break;
        case 'mov':
            header('Content-type: video/quicktime');
            break;
        case 'ts':
            header('Content-type: video/mp2t');
            break;
        case 'mpg':
        case 'mpeg':
            header('Content-Type: video/mpeg');
            break;
        default:
            header('Content-Type: application/octet-stream');
    }
    $rDownloadBytes = (!empty($rChannelInfo['bitrate']) ? $rChannelInfo['bitrate'] * 125 : 0);
    $rDownloadBytes += $rDownloadBytes * ipTV_lib::$settings['vod_bitrate_plus'] * 0.01;
    $rRequest = VOD_PATH . $streamID . '.' . $rExtension;

    if (file_exists($rRequest)) {
        $fp = @fopen($rRequest, 'rb');
        $size = filesize($rRequest);
        $rLength = $size;
        $start = 0;
        $end = $size - 1;
        header('Accept-Ranges: 0-' . $rLength);

        if (!empty($_SERVER['HTTP_RANGE'])) {
            $rRangeStart = $start;
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
                    $rLength = $end - $start + 1;
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
        header('Content-Length: ' . $rLength);
        $rLastCheck = $rTimeStart = $rTimeChecked = time();
        $rBytesRead = 0;
        $buffer = ipTV_lib::$settings['read_buffer_size'];
        $i = 0;
        $o = 0;

        if (0 < ipTV_lib::$settings['vod_limit_perc'] && !$rUserInfo['is_restreamer']) {
            $rLimitAt = intval($rLength * floatval(ipTV_lib::$settings['vod_limit_perc'] / 100));
        } else {
            $rLimitAt = $rLength;
        }

        $applyLimit = false;

        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            $rResponse = stream_get_line($fp, $buffer);
            $i++;

            if (!$applyLimit && $rLimitAt <= $o * $buffer) {
                $applyLimit = true;
            } else {
                $o++;
            }

            echo $rResponse;
            $rBytesRead += strlen($rResponse);

            if (time() - $rTimeStart >= 30) {
                file_put_contents($rConSpeedFile, intval($rBytesRead / 1024 / 30));
                $rTimeStart = time();
                $rBytesRead = 0;
            }

            if ($rDownloadBytes > 0 && $applyLimit && ceil($rDownloadBytes / $buffer) <= $i) {
                sleep(1);
                $i = 0;
            }

            if (300 < time() - $rLastCheck) {
                $rLastCheck = time();
                $rConnection = null;
                ipTV_lib::$settings = ipTV_lib::getCache('settings');

                $ipTV_db->query('SELECT `pid`, `hls_end` FROM `lines_live` WHERE `uuid` = ?', $rTokenData['uuid']);

                if ($ipTV_db->num_rows() == 1) {
                    $rConnection = $ipTV_db->get_row();
                }

                $ipTV_db->close_mysql();

                if (!is_array($rConnection) || $rConnection['hls_end'] != 0 || $rConnection['pid'] != $rPID) {
                    exit();
                }
            }
        }
        fclose($fp);

        exit();
    }
} else {
    generateError('TOKEN_ERROR');
}

function shutdown() {
    global $rCloseCon;
    global $rTokenData;
    global $rPID;
    global $ipTV_db;
    ipTV_lib::$settings = ipTV_lib::getCache('settings');

    if ($rCloseCon) {
        $ipTV_db->query('UPDATE `lines_live` SET `hls_end` = 1, `hls_last_read` = ? WHERE `uuid` = ? AND `pid` = ?;', time() - intval(ipTV_lib::$Servers[SERVER_ID]['time_offset']), $rTokenData['uuid'], $rPID);
    }

    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
