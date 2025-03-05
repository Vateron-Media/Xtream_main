<?php

header('Cache-Control: no-store, no-cache, must-revalidate');
require_once '../init.php';

if ((CoreUtilities::$settings['enable_cache'] && !file_exists(CACHE_TMP_PATH . 'cache_complete') || empty(CoreUtilities::$settings['live_streaming_pass']))) {
    generateError('CACHE_INCOMPLETE');
}

$rIsMag = false;
$rMagToken = null;

if (isset(CoreUtilities::$request['token']) && !ctype_xdigit(CoreUtilities::$request['token'])) {
    $rData = explode('/', ipTV_streaming::decryptData(CoreUtilities::$request['token'], CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA));
    CoreUtilities::$request['type'] = $rData[0];
    $rTypeSplit = explode('::', CoreUtilities::$request['type']);

    if (count($rTypeSplit) == 2) {
        CoreUtilities::$request['type'] = $rTypeSplit[1];
        $rIsMag = true;
    }

    if (CoreUtilities::$request['type'] == 'timeshift') {
        list(, CoreUtilities::$request['username'], CoreUtilities::$request['password'], CoreUtilities::$request['duration'], CoreUtilities::$request['start'], CoreUtilities::$request['stream']) = $rData;

        if ($rIsMag) {
            $rMagToken = $rData[6];
        }

        CoreUtilities::$request['extension'] = 'ts';
    } else {
        list(, CoreUtilities::$request['username'], CoreUtilities::$request['password'], CoreUtilities::$request['stream']) = $rData;

        if (5 <= count($rData)) {
            CoreUtilities::$request['extension'] = $rData[4];
        }

        if (count($rData) == 6) {
            if ($rIsMag) {
                $rMagToken = $rData[5];
            } else {
                $rExpiry = $rData[5];
            }
        }

        if (!isset(CoreUtilities::$request['extension'])) {
            CoreUtilities::$request['extension'] = 'ts';
        }
    }

    unset(CoreUtilities::$request['token'], $rData);
}

if (isset(CoreUtilities::$request['utc'])) {
    CoreUtilities::$request['type'] = 'timeshift';
    CoreUtilities::$request['start'] = CoreUtilities::$request['utc'];
    CoreUtilities::$request['duration'] = 3600 * 6;
    unset(CoreUtilities::$request['utc']);
}

$rType = (isset(CoreUtilities::$request['type']) ? CoreUtilities::$request['type'] : 'live');
$streamID = intval(CoreUtilities::$request['stream']);
$rExtension = (isset(CoreUtilities::$request['extension']) ? strtolower(preg_replace('/[^A-Za-z0-9 ]/', '', trim(CoreUtilities::$request['extension']))) : null);

if (!$rExtension && in_array($rType, array('movie', 'series', 'subtitle'))) {
    $rStream = pathinfo(CoreUtilities::$request['stream']);
    $streamID = intval($rStream['filename']);
    $rExtension = strtolower(preg_replace('/[^A-Za-z0-9 ]/', '', trim($rStream['extension'])));
}

if ($rExtension) {
    if (!($streamID && (!CoreUtilities::$settings['enable_cache'] || file_exists(STREAMS_TMP_PATH . 'stream_' . $streamID)))) {
        generateError('INVALID_STREAM_ID');
    }

    if ((CoreUtilities::$settings['ignore_invalid_users'] && CoreUtilities::$settings['enable_cache'])) {
        if (isset(CoreUtilities::$request['token'])) {
            if (!file_exists(USER_TMP_PATH . 'line_t_' . CoreUtilities::$request['token'])) {
                generateError('INVALID_CREDENTIALS');
            }
        } else {
            if ((isset(CoreUtilities::$request['username']) && isset(CoreUtilities::$request['password']))) {
                if (CoreUtilities::$settings['case_sensitive_line']) {
                    $rPath = USER_TMP_PATH . 'line_c_' . CoreUtilities::$request['username'] . '_' . CoreUtilities::$request['password'];
                } else {
                    $rPath = USER_TMP_PATH . 'line_c_' . strtolower(CoreUtilities::$request['username']) . '_' . strtolower(CoreUtilities::$request['password']);
                }

                if (!file_exists($rPath)) {
                    generateError('INVALID_CREDENTIALS');
                }
            }
        }
    }

    if ((CoreUtilities::$settings['enable_cache'] && !CoreUtilities::$settings['show_not_on_air_video'] && file_exists(CACHE_TMP_PATH . 'servers'))) {
        $rServers = igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'servers'));
        $rStream = (igbinary_unserialize(file_get_contents(STREAMS_TMP_PATH . 'stream_' . $streamID)) ?: null);
        $rAvailableServers = array();

        if ($rType == 'archive') {
            if ((0 < $rStream['info']['tv_archive_duration'] && 0 < $rStream['info']['tv_archive_server_id'] && array_key_exists($rStream['info']['tv_archive_server_id'], $rServers) && $rServers[$rStream['info']['tv_archive_server_id']]['server_online'])) {
                $rAvailableServers[] = array($rStream['info']['tv_archive_server_id']);
            }
        } else {
            if (($rStream['info']['direct_source'] == 1)) {
                $rAvailableServers[] = $rServerID;
            }

            foreach ($rServers as $rServerID => $rServerInfo) {
                if (!(!array_key_exists($rServerID, $rStream['servers']) || !$rServerInfo['server_online'] || $rServerInfo['server_type'] != 0)) {
                    if (isset($rStream['servers'][$rServerID])) {
                        if ($rType == 'movie') {
                            if (((!empty($rStream['servers'][$rServerID]['pid']) && $rStream['servers'][$rServerID]['to_analyze'] == 0 && $rStream['servers'][$rServerID]['stream_status'] == 0) && ($rStream['info']['target_container'] == $rExtension || ($rExtension = 'srt')) && $rServerInfo['timeshift_only'] == 0)) {
                                $rAvailableServers[] = $rServerID;
                            }
                        } else {
                            if ((($rStream['servers'][$rServerID]['on_demand'] == 1 && $rStream['servers'][$rServerID]['stream_status'] != 1 || 0 < $rStream['servers'][$rServerID]['pid'] && $rStream['servers'][$rServerID]['stream_status'] == 0) && $rStream['servers'][$rServerID]['to_analyze'] == 0 && (int) $rStream['servers'][$rServerID]['delay_available_at'] <= time() && $rServerInfo['timeshift_only'] == 0)) {
                                $rAvailableServers[] = $rServerID;
                            }
                        }
                    }
                }
            }
        }

        if (count($rAvailableServers) == 0) {
            ipTV_streaming::showVideoServer('show_not_on_air_video', 'not_on_air_video_path', $rExtension, $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
        }
    }
    header('Access-Control-Allow-Origin: *');
    register_shutdown_function('shutdown');
    $rRestreamDetect = false;
    $rPrebuffer = isset(CoreUtilities::$request['prebuffer']);

    foreach (getallheaders() as $rKey => $rValue) {
        if (strtoupper($rKey) == 'X-XTREAMUI-DETECT') {
            $rRestreamDetect = true;
        } else {
            if (strtoupper($rKey) == 'X-XTREAMUI-PREBUFFER') {
                $rPrebuffer = true;
            }
        }
    }
    $rIsEnigma = false;
    $rUserInfo = null;
    $rIdentifier = '';
    $PID = getmypid();
    $rUUID = md5(uniqid());
    $IP = ipTV_streaming::getUserIP();
    $rCountryCode = ipTV_streaming::getIPInfo($IP)['country']['iso_code'];
    $rUserAgent = (empty($_SERVER['HTTP_USER_AGENT']) ? '' : htmlentities(trim($_SERVER['HTTP_USER_AGENT'])));
    $rDeny = true;
    $rExternalDevice = null;
    $rActivityStart = time();

    if (!isset($rExpiry)) {
        $rExpiry = null;
    }

    if (isset(CoreUtilities::$request['token'])) {
        $rAccessToken = CoreUtilities::$request['token'];
        $rUserInfo = ipTV_streaming::getUserInfo(null, $rAccessToken, null, false, false, $IP);
    } else {
        $rUsername = CoreUtilities::$request['username'];
        $rPassword = CoreUtilities::$request['password'];
        $rUserInfo = ipTV_streaming::getUserInfo(null, $rUsername, $rPassword, false, false, $IP);
    }

    if ($rUserInfo) {
        $rDeny = false;
        CoreUtilities::checkAuthFlood($rUserInfo, $IP);

        if ($rUserInfo['is_e2']) {
            $rIsEnigma = true;
        }

        if (isset($rAccessToken)) {
            $rUsername = $rUserInfo['username'];
            $rPassword = $rUserInfo['password'];
        }

        if (!(is_null($rUserInfo['exp_date']) || $rUserInfo['exp_date'] > time())) {
            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_EXPIRED', $IP);

            if (in_array($rType, array('live', 'timeshift'))) {
                ipTV_streaming::showVideoServer('show_expired_video', 'expired_video_path', $rExtension, $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
            } else {
                if (in_array($rType, array('movie', 'series'))) {
                    ipTV_streaming::showVideoServer('show_expired_video', 'expired_video_path', 'ts', $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
                } else {
                    generateError('EXPIRED');
                }
            }
        }

        if ($rUserInfo['admin_enabled'] == 0) {
            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_BAN', $IP);

            if (in_array($rType, array('live', 'timeshift'))) {
                ipTV_streaming::showVideoServer('show_banned_video', 'banned_video_path', $rExtension, $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
            } else {
                if (in_array($rType, array('movie', 'series'))) {
                    ipTV_streaming::showVideoServer('show_banned_video', 'banned_video_path', 'ts', $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
                } else {
                    generateError('BANNED');
                }
            }
        }

        if ($rUserInfo['enabled'] == 0) {
            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_DISABLED', $IP);

            if (in_array($rType, array('live', 'timeshift'))) {
                ipTV_streaming::showVideoServer('show_banned_video', 'banned_video_path', $rExtension, $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
            } else {
                if (in_array($rType, array('movie', 'series'))) {
                    ipTV_streaming::showVideoServer('show_banned_video', 'banned_video_path', 'ts', $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
                } else {
                    generateError('DISABLED');
                }
            }
        }

        if ($rType != 'subtitle') {
            if ($rUserInfo['bypass_ua'] == 0) {
                if (ipTV_streaming::checkBlockedUAs($rUserAgent)) {
                    generateError('BLOCKED_USER_AGENT');
                }
            }

            if ((empty($rUserAgent) && CoreUtilities::$settings['disallow_empty_user_agents'])) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'EMPTY_UA', $IP);
                generateError('EMPTY_USER_AGENT');
            }

            if (!(empty($rUserInfo['allowed_ips']) || in_array($IP, array_map('gethostbyname', $rUserInfo['allowed_ips'])))) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'IP_BAN', $IP);
                generateError('NOT_IN_ALLOWED_IPS');
            }

            if (!empty($rCountryCode)) {
                $rForceCountry = !empty($rUserInfo['forced_country']);

                if (($rForceCountry && $rUserInfo['forced_country'] != 'ALL' && $rCountryCode != $rUserInfo['forced_country'])) {
                    ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'COUNTRY_DISALLOW', $IP);
                    generateError('FORCED_COUNTRY_INVALID');
                }

                if (!($rForceCountry || in_array('ALL', CoreUtilities::$settings['allow_countries']) || in_array($rCountryCode, CoreUtilities::$settings['allow_countries']))) {
                    ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'COUNTRY_DISALLOW', $IP);
                    generateError('NOT_IN_ALLOWED_COUNTRY');
                }
            }

            if (!(empty($rUserInfo['allowed_ua']) || in_array($rUserAgent, $rUserInfo['allowed_ua']))) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_AGENT_BAN', $IP);
                generateError('NOT_IN_ALLOWED_UAS');
            }

            if ($rUserInfo['isp_violate']) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'ISP_LOCK_FAILED', $IP, json_encode(array('old' => $rUserInfo['isp_desc'], 'new' => $rUserInfo['con_isp_name'])));
                generateError('ISP_BLOCKED');
            }

            if ($rUserInfo['isp_is_server'] && !$rUserInfo['is_restreamer']) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'BLOCKED_ASN', $IP, json_encode(array('user_agent' => $rUserAgent, 'isp' => $rUserInfo['con_isp_name'], 'asn' => $rUserInfo['isp_asn'])), true);
                generateError('ASN_BLOCKED');
            }

            if ($rUserInfo['is_mag'] && !$rIsMag) {
                generateError('DEVICE_NOT_ALLOWED');
            } else {
                if ($rIsMag && !CoreUtilities::$settings['disable_mag_token'] && (!$rMagToken || $rMagToken != $rUserInfo['mag_token'])) {
                    generateError('TOKEN_EXPIRED');
                } else {
                    if (($rExpiry && $rExpiry < time())) {
                        ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'TOKEN_EXPIRED', $IP);
                        generateError('TOKEN_EXPIRED');
                    }
                }
            }
        }

        if (!in_array($rType, array('thumb', 'subtitle'))) {
            if (!($rUserInfo['is_restreamer'] || in_array($IP, CoreUtilities::$allowedIPs))) {
                if ((CoreUtilities::$settings['block_streaming_servers'] || CoreUtilities::$settings['block_proxies'])) {
                    $rCIDR = ipTV_streaming::matchCIDR($rUserInfo['isp_asn'], $IP);

                    if ($rCIDR) {
                        if ((CoreUtilities::$settings['block_streaming_servers'] && $rCIDR[3]) && !$rCIDR[4]) {
                            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'HOSTING_DETECT', $IP, json_encode(array('user_agent' => $rUserAgent, 'isp' => $rUserInfo['con_isp_name'], 'asn' => $rUserInfo['isp_asn'])), true);
                            generateError('HOSTING_DETECT');
                        }

                        if ((CoreUtilities::$settings['block_proxies'] && $rCIDR[4])) {
                            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'PROXY_DETECT', $IP, json_encode(array('user_agent' => $rUserAgent, 'isp' => $rUserInfo['con_isp_name'], 'asn' => $rUserInfo['isp_asn'])), true);
                            generateError('PROXY_DETECT');
                        }
                    }
                }

                if ($rRestreamDetect) {
                    if (CoreUtilities::$settings['detect_restream_block_user']) {
                        if (CoreUtilities::$cached) {
                            CoreUtilities::setSignal('restream_block_user/' . $rUserInfo['id'] . '/' . $streamID . '/' . $IP, 1);
                        } else {
                            $ipTV_db->query('UPDATE `lines` SET `admin_enabled` = 0 WHERE `id` = ?;', $rUserInfo['id']);
                        }
                    }

                    if ((CoreUtilities::$settings['restream_deny_unauthorised'] || CoreUtilities::$settings['detect_restream_block_user'])) {
                        ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'RESTREAM_DETECT', $IP, json_encode(array('user_agent' => $rUserAgent, 'isp' => $rUserInfo['con_isp_name'], 'asn' => $rUserInfo['isp_asn'])), true);
                        generateError('RESTREAM_DETECT');
                    }
                }
            }
        }

        if ($rType == 'live') {
            if (!in_array($rExtension, $rUserInfo['output_formats'])) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_DISALLOW_EXT', $IP);
                generateError('USER_DISALLOW_EXT');
            }
        }

        if (($rType == 'live' && CoreUtilities::$settings['show_expiring_video'] && !$rUserInfo['is_trial'] && !is_null($rUserInfo['exp_date']) && $rUserInfo['exp_date'] - 86400 * 7 <= time() && (86400 <= time() - $rUserInfo['last_expiration_video'] || !$rUserInfo['last_expiration_video']))) {
            if (CoreUtilities::$cached) {
                CoreUtilities::setSignal('expiring/' . $rUserInfo['id'], time());
            } else {
                $ipTV_db->query('UPDATE `lines` SET `last_expiration_video` = ? WHERE `id` = ?;', time(), $rUserInfo['id']);
            }

            ipTV_streaming::showVideoServer('show_expiring_video', 'expiring_video_path', $rExtension, $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
        }
    } else {
        CoreUtilities::checkBruteforce($IP, null, $rUsername);
        ipTV_streaming::clientLog($streamID, 0, 'AUTH_FAILED', $IP);
        generateError('INVALID_CREDENTIALS');
    }

    if ($rIsMag) {
        $rForceHTTP = CoreUtilities::$settings['mag_disable_ssl'];
    } else {
        if ($rIsEnigma) {
            $rForceHTTP = true;
        } else {
            $rForceHTTP = false;
        }
    }

    switch ($rType) {
        case 'live':
            $rChannelInfo = ipTV_streaming::channelInfo($streamID, $rExtension, $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'live');
            if (is_array($rChannelInfo)) {
                if (count(array_keys($rChannelInfo)) == 0) {
                    generateError('NO_SERVERS_AVAILABLE');
                }
                if (!array_intersect($rUserInfo['bouquet'], $rChannelInfo['bouquets'])) {
                    generateError('NOT_IN_BOUQUET');
                }

                $rURL = ipTV_streaming::getStreamingURL($rChannelInfo['redirect_id'], $rForceHTTP);
                $rStreamInfo = json_decode($rChannelInfo['stream_info'], true);
                $rVideoCodec = ($rStreamInfo['codecs']['video']['codec_name'] ?: 'h264');

                switch ($rExtension) {
                    case 'm3u8':
                        if ((CoreUtilities::$settings['disable_hls'] && (!$rUserInfo['is_restreamer'] || !CoreUtilities::$settings['disable_hls_allow_restream']))) {
                            generateError('HLS_DISABLED');
                        }
                        $tokenData = array('stream_id' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'pid' => $PID, 'channel_info' => array('redirect_id' => $rChannelInfo['redirect_id'], 'pid' => $rChannelInfo['pid'], 'on_demand' => $rChannelInfo['on_demand'], 'llod' => $rChannelInfo['llod'], 'monitor_pid' => $rChannelInfo['monitor_pid']), 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_id' => $rUserInfo['pair_id'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'external_device' => $rExternalDevice, 'activity_start' => $rActivityStart, 'country_code' => $rCountryCode, 'video_codec' => $rVideoCodec, 'uuid' => $rUUID);

                        $rToken = ipTV_streaming::encryptData(json_encode($tokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                        if (CoreUtilities::$settings['allow_cdn_access']) {
                            header('Location: ' . $rURL . '/sauth/' . $streamID . '.m3u8?token=' . $rToken);
                            exit();
                        }

                        header('Location: ' . $rURL . '/sauth/' . $rToken);

                        exit();


                        // no break
                    case 'ts':
                        if ((CoreUtilities::$settings['disable_ts'] && (!$rUserInfo['is_restreamer'] || !CoreUtilities::$settings['disable_ts_allow_restream']))) {
                            generateError('TS_DISABLED');
                        }
                        $tokenData = array('stream_id' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'channel_info' => array('stream_id' => $rChannelInfo['stream_id'], 'redirect_id' => ($rChannelInfo['redirect_id'] ?: null), 'pid' => $rChannelInfo['pid'], 'on_demand' => $rChannelInfo['on_demand'], 'llod' => $rChannelInfo['llod'], 'monitor_pid' => $rChannelInfo['monitor_pid']), 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_id' => $rUserInfo['pair_id'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'pid' => $PID, 'prebuffer' => $rPrebuffer, 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'external_device' => $rExternalDevice, 'video_codec' => $rVideoCodec, 'uuid' => $rUUID);

                        $rToken = ipTV_streaming::encryptData(json_encode($tokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                        if (CoreUtilities::$settings['allow_cdn_access']) {
                            header('Location: ' . $rURL . '/sauth/' . $streamID . '.ts?token=' . $rToken);
                            exit();
                        }
                        header('Location: ' . $rURL . '/sauth/' . $rToken);

                        exit();
                }
            } else {
                ipTV_streaming::showVideoServer('show_not_on_air_video', 'not_on_air_video_path', $rExtension, $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
            }

            break;

        case 'movie':
        case 'series':
            $rChannelInfo = ipTV_streaming::channelInfo($streamID, $rExtension, $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'movie');

            if ($rChannelInfo) {
                $rURL = ipTV_streaming::getStreamingURL($rChannelInfo['redirect_id'], $rForceHTTP);

                $tokenData = array('stream_id' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'type' => $rType, 'pid' => $PID, 'channel_info' => array('stream_id' => $rChannelInfo['stream_id'], 'bitrate' => $rChannelInfo['bitrate'], 'target_container' => $rChannelInfo['target_container'], 'redirect_id' => $rChannelInfo['redirect_id'], 'pid' => $rChannelInfo['pid']), 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_id' => $rUserInfo['pair_id'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'is_mag' => $rIsMag, 'uuid' => $rUUID, 'http_range' => (isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null));

                $rToken = ipTV_streaming::encryptData(json_encode($tokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                if (CoreUtilities::$settings['allow_cdn_access']) {
                    header('Location: ' . $rURL . '/vauth/' . $streamID . '.' . $rExtension . '?token=' . $rToken);
                    exit();
                }
                header('Location: ' . $rURL . '/vauth/' . $rToken);
                exit();
            }
            ipTV_streaming::showVideoServer('show_not_on_air_video', 'not_on_air_video_path', 'ts', $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
            break;

        case 'timeshift':
            $rRedirectID = ipTV_streaming::channelInfo($streamID, $rExtension, $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'archive');
            if (!$rRedirectID) {
                ipTV_streaming::showVideoServer('show_not_on_air_video', 'not_on_air_video_path', $rExtension, $rUserInfo, $IP, $rCountryCode, $rUserInfo['con_isp_name'], SERVER_ID);
                break;
            }
            $rURL = ipTV_streaming::getStreamingURL($rRedirectID, $rForceHTTP);
            $rStartDate = CoreUtilities::$request['start'];
            $rDuration = intval(CoreUtilities::$request['duration']);

            switch ($rExtension) {
                case 'm3u8':
                    if ((CoreUtilities::$settings['disable_hls'] && (!$rUserInfo['is_restreamer'] || !CoreUtilities::$settings['disable_hls_allow_restream']))) {
                        generateError('HLS_DISABLED');
                    }

                    $tokenData = array('stream' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'pid' => $PID, 'start' => $rStartDate, 'duration' => $rDuration, 'redirect_id' => $rRedirectID, 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_line_info' => $rUserInfo['pair_line_info'], 'pair_id' => $rUserInfo['pair_id'], 'active_cons' => $rUserInfo['active_cons'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'uuid' => $rUUID, 'http_range' => (isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null));
                    $rToken = ipTV_streaming::encryptData(json_encode($tokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                    if (CoreUtilities::$settings['allow_cdn_access']) {
                        header('Location: ' . $rURL . '/tsauth/' . $streamID . '_' . $rStartDate . '_' . $rDuration . '.m3u8?token=' . $rToken);

                        exit();
                    }

                    header('Location: ' . $rURL . '/tsauth/' . $rToken);

                    exit();

                default:
                    if ((CoreUtilities::$settings['disable_ts'] && (!$rUserInfo['is_restreamer'] || !CoreUtilities::$settings['disable_ts_allow_restream']))) {
                        generateError('TS_DISABLED');
                    }

                    $rActivityStart = time();
                    $tokenData = array('stream' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'pid' => $PID, 'start' => $rStartDate, 'duration' => $rDuration, 'redirect_id' => $rRedirectID, 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_line_info' => $rUserInfo['pair_line_info'], 'pair_id' => $rUserInfo['pair_id'], 'active_cons' => $rUserInfo['active_cons'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'uuid' => $rUUID, 'http_range' => (isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null));
                    $rToken = ipTV_streaming::encryptData(json_encode($tokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                    if (CoreUtilities::$settings['allow_cdn_access']) {
                        header('Location: ' . $rURL . '/tsauth/' . $streamID . '_' . $rStartDate . '_' . $rDuration . '.ts?token=' . $rToken);

                        exit();
                    }

                    header('Location: ' . $rURL . '/tsauth/' . $rToken);

                    exit();
            }
            // no break
        case 'thumb':
            $rStreamInfo = null;

            if (CoreUtilities::$cached) {
                $rStreamInfo = igbinary_unserialize(file_get_contents(STREAMS_TMP_PATH . 'stream_' . $streamID));
            } else {
                $ipTV_db->query('SELECT * FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type AND t2.live = 1 LEFT JOIN `profiles` t4 ON t1.transcode_profile_id = t4.profile_id WHERE t1.direct_source = 0 AND t1.id = ?', $streamID);

                if (0 < $ipTV_db->num_rows()) {
                    $rStreamInfo = array('info' => $ipTV_db->get_row());
                }
            }

            if (!$rStreamInfo) {
                generateError('INVALID_STREAM_ID');
            }

            if ($rStreamInfo['info']['vframes_server_id'] == 0) {
                generateError('THUMBNAILS_NOT_ENABLED');
            }

            $tokenData = array('stream' => $streamID, 'expires' => time() + 5);

            $rURL = ipTV_streaming::getStreamingURL($rStreamInfo['info']['vframes_server_id'], $rForceHTTP);
            $rToken = ipTV_streaming::encryptData(json_encode($tokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);
            header('Location: ' . $rURL . '/thauth/' . $rToken);

            exit();

        case 'subtitle':
            $rChannelInfo = ipTV_streaming::channelInfo($streamID, 'srt', $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'movie');

            if ($rChannelInfo) {
                $rURL = ipTV_streaming::getStreamingURL($rChannelInfo['redirect_id'], $rForceHTTP);
                $tokenData = array('stream_id' => $streamID, 'sub_id' => (intval(CoreUtilities::$request['sid']) ?: 0), 'webvtt' => (intval(CoreUtilities::$request['webvtt']) ?: 0), 'expires' => time() + 5);
                $rToken = ipTV_streaming::encryptData(json_encode($tokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                header('Location: ' . $rURL . '/subauth/' . $rToken);

                exit();
            }

            generateError('INVALID_STREAM_ID');

            break;
    }
} else {
    switch ($rType) {
        case 'timeshift':
        case 'live':
            $rExtension = 'ts';
            break;

        case 'series':
        case 'movie':
            $rExtension = 'mp4';
            break;
    }
}

function shutdown() {
    global $rDeny;
    global $ipTV_db;

    if ($rDeny) {
        CoreUtilities::checkFlood();
    }

    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
