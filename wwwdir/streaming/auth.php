<?php

header('Cache-Control: no-store, no-cache, must-revalidate');
require '../init.php';

$rIsMag = false;
$rMagToken = null;

if (isset(ipTV_lib::$request['token']) && !ctype_xdigit(ipTV_lib::$request['token'])) {
    $rData = explode('/', decryptData(ipTV_lib::$request['token'], ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA));
    ipTV_lib::$request['type'] = $rData[0];
    $rTypeSplit = explode('::', ipTV_lib::$request['type']);

    if (count($rTypeSplit) == 2) {
        ipTV_lib::$request['type'] = $rTypeSplit[1];
        $rIsMag = true;
    }

    if (ipTV_lib::$request['type'] == 'timeshift') {
        list(, ipTV_lib::$request['username'], ipTV_lib::$request['password'], ipTV_lib::$request['duration'], ipTV_lib::$request['start'], ipTV_lib::$request['stream']) = $rData;

        if ($rIsMag) {
            $rMagToken = $rData[6];
        }

        ipTV_lib::$request['extension'] = 'ts';
    } else {
        list(, ipTV_lib::$request['username'], ipTV_lib::$request['password'], ipTV_lib::$request['stream']) = $rData;

        if (5 <= count($rData)) {
            ipTV_lib::$request['extension'] = $rData[4];
        }

        if (count($rData) == 6) {
            if ($rIsMag) {
                $rMagToken = $rData[5];
            } else {
                $rExpiry = $rData[5];
            }
        }

        if (!isset(ipTV_lib::$request['extension'])) {
            ipTV_lib::$request['extension'] = 'ts';
        }
    }

    unset(ipTV_lib::$request['token'], $rData);
}

if (isset(ipTV_lib::$request['utc'])) {
    ipTV_lib::$request['type'] = 'timeshift';
    ipTV_lib::$request['start'] = ipTV_lib::$request['utc'];
    ipTV_lib::$request['duration'] = 3600 * 6;
    unset(ipTV_lib::$request['utc']);
}

$rType = (isset(ipTV_lib::$request['type']) ? ipTV_lib::$request['type'] : 'live');
$streamID = intval(ipTV_lib::$request['stream']);
$rExtension = (isset(ipTV_lib::$request['extension']) ? strtolower(preg_replace('/[^A-Za-z0-9 ]/', '', trim(ipTV_lib::$request['extension']))) : null);



if (!$rExtension && in_array($rType, array('movie', 'series', 'subtitle'))) {
    $rStream = pathinfo(ipTV_lib::$request['stream']);
    $streamID = intval($rStream['filename']);
    $rExtension = strtolower(preg_replace('/[^A-Za-z0-9 ]/', '', trim($rStream['extension'])));
}

if ($rExtension) {
    if (!$streamID) {
        generateError('INVALID_STREAM_ID');
    }


    $ipTV_db->db_connect();

    header('Access-Control-Allow-Origin: *');
    register_shutdown_function('shutdown');
    $rRestreamDetect = false;
    $rPrebuffer = isset(ipTV_lib::$request['prebuffer']);

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
    $userIP = ipTV_streaming::getUserIP();
    $rCountryCode = ipTV_streaming::getIPInfo($userIP)['country']['iso_code'];
    $rUserAgent = (empty($_SERVER['HTTP_USER_AGENT']) ? '' : htmlentities(trim($_SERVER['HTTP_USER_AGENT'])));
    $rDeny = true;
    $rExternalDevice = null;
    $rActivityStart = time();

    if (!isset($rExpiry)) {
        $rExpiry = null;
    }

    if (isset(ipTV_lib::$request['token'])) {
        $rAccessToken = ipTV_lib::$request['token'];
        $rUserInfo = ipTV_streaming::GetUserInfo(null, $rAccessToken, null, false, false, $userIP);
    } else {
        $rUsername = ipTV_lib::$request['username'];
        $rPassword = ipTV_lib::$request['password'];
        $rUserInfo = ipTV_streaming::getUserInfo(null, $rUsername, $rPassword, false, false, $userIP);
    }

    if ($rUserInfo) {
        $rDeny = false;
        checkAuthFlood($rUserInfo);

        if ($rUserInfo['is_e2']) {
            $rIsEnigma = true;
        }

        if (isset($rAccessToken)) {
            $rUsername = $rUserInfo['username'];
            $rPassword = $rUserInfo['password'];
        }

        if (!(is_null($rUserInfo['exp_date']) || $rUserInfo['exp_date'] > time())) {
            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_EXPIRED', $userIP);

            if (in_array($rType, array('live', 'timeshift'))) {
                ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_expired_video', 'expired_video_path', $rExtension);
            } else {
                if (in_array($rType, array('movie', 'series'))) {
                    ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_expired_video', 'expired_video_path', 'ts');
                } else {
                    generateError('EXPIRED');
                }
            }
        }

        if ($rUserInfo['admin_enabled'] == 0) {
            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_BAN', $userIP);

            if (in_array($rType, array('live', 'timeshift'))) {
                ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_banned_video', 'banned_video_path', $rExtension);
            } else {
                if (in_array($rType, array('movie', 'series'))) {
                    ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_banned_video', 'banned_video_path', 'ts');
                } else {
                    generateError('BANNED');
                }
            }
        }

        if ($rUserInfo['enabled'] == 0) {
            ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_DISABLED', $userIP);

            if (in_array($rType, array('live', 'timeshift'))) {
                ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_banned_video', 'banned_video_path', $rExtension);
            } else {
                if (in_array($rType, array('movie', 'series'))) {
                    ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_banned_video', 'banned_video_path', 'ts');
                } else {
                    generateError('DISABLED');
                }
            }
        }

        if ($rType != 'subtitle') {
            if ($rUserInfo['bypass_ua'] == 0) {
                ipTV_streaming::checkGlobalBlockUA($user_agent);
            }

            if ((empty($rUserAgent) && ipTV_lib::$settings['disallow_empty_user_agents'])) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'EMPTY_UA', $userIP);
                generateError('EMPTY_USER_AGENT');
            }

            if (!(empty($rUserInfo['allowed_ips']) || in_array($userIP, array_map('gethostbyname', $rUserInfo['allowed_ips'])))) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'IP_BAN', $userIP);
                generateError('NOT_IN_ALLOWED_IPS');
            }

            if (!empty($rCountryCode)) {
                $rForceCountry = !empty($rUserInfo['forced_country']);

                if (($rForceCountry && $rUserInfo['forced_country'] != 'ALL' && $rCountryCode != $rUserInfo['forced_country'])) {
                    ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'COUNTRY_DISALLOW', $userIP);
                    generateError('FORCED_COUNTRY_INVALID');
                }

                if (!($rForceCountry || in_array('ALL', ipTV_lib::$settings['allow_countries']) || in_array($rCountryCode, ipTV_lib::$settings['allow_countries']))) {
                    ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'COUNTRY_DISALLOW', $userIP);
                    generateError('NOT_IN_ALLOWED_COUNTRY');
                }
            }

            if (!(empty($rUserInfo['allowed_ua']) || in_array($rUserAgent, $rUserInfo['allowed_ua']))) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'USER_AGENT_BAN', $userIP);
                generateError('NOT_IN_ALLOWED_UAS');
            }

            if ($rUserInfo['isp_violate']) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'ISP_LOCK_FAILED', $userIP, json_encode(array('old' => $rUserInfo['isp_desc'], 'new' => $rUserInfo['con_isp_name'])));
                generateError('ISP_BLOCKED');
            }

            if ($rUserInfo['isp_is_server'] && !$rUserInfo['is_restreamer']) {
                ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'BLOCKED_ASN', $userIP, json_encode(array('user_agent' => $rUserAgent, 'isp' => $rUserInfo['con_isp_name'], 'asn' => $rUserInfo['isp_asn'])), true);
                generateError('ASN_BLOCKED');
            }

            if ($rUserInfo['is_mag'] && !$rIsMag) {
                generateError('DEVICE_NOT_ALLOWED');
            } else {
                if ($rIsMag && !ipTV_lib::$settings['disable_mag_token'] && (!$rMagToken || $rMagToken != $rUserInfo['mag_token'])) {
                    generateError('TOKEN_EXPIRED');
                } else {
                    if (($rExpiry && $rExpiry < time())) {
                        ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'TOKEN_EXPIRED', $userIP);
                        generateError('TOKEN_EXPIRED');
                    }
                }
            }
        }

        //Testing is required on the mag set-top box, not on the emulator

        // if (($rUserInfo['is_stalker'] && in_array($rType, array('live', 'movie', 'series', 'timeshift')))) {
        //     if ((empty(ipTV_lib::$request['stalker_key']) || $rExtension != 'ts')) {
        //         generateError('STALKER_INVALID_KEY');
        //     }

        //     $rStalkerKey = base64_decode(urldecode(ipTV_lib::$request['stalker_key']));

        //     if ($rDecryptKey = ipTV_lib::mc_decrypt($rStalkerKey, md5(ipTV_lib::$settings['live_streaming_pass']))) {
        //         $rStalkerData = explode('=', $rDecryptKey);

        //         if ($rStalkerData[2] != $streamID) {
        //             ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'STALKER_CHANNEL_MISMATCH', $userIP);
        //             generateError('STALKER_CHANNEL_MISMATCH');
        //         }

        //         if ($rStalkerData[1] != $userIP && ipTV_lib::$settings['restrict_same_ip']) {
        //             ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'STALKER_IP_MISMATCH', $userIP);
        //             generateError('STALKER_IP_MISMATCH');
        //         }

        //         $rCreateExpiration = 5;

        //         if ($rStalkerData[3] < time() - $rCreateExpiration) {
        //             ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'STALKER_KEY_EXPIRED', $userIP);
        //             generateError('STALKER_KEY_EXPIRED');
        //         }

        //         $rExternalDevice = $rStalkerData[0];
        //     } else {
        //         ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'STALKER_DECRYPT_FAILED', $userIP);
        //         generateError('STALKER_DECRYPT_FAILED');
        //     }
        // }

        // ------------------------------------------------------------------

        if (!in_array($rType, array('thumb', 'subtitle'))) {
            if (!($rUserInfo['is_restreamer'] || in_array($userIP, ipTV_streaming::getAllowedIPs()))) {
                if ($rRestreamDetect) {
                    if (ipTV_lib::$settings['detect_restream_block_user']) {
                        $ipTV_db->query('UPDATE `lines` SET `admin_enabled` = 0 WHERE `id` = \'%s\';', $rUserInfo['id']);
                    }

                    if ((ipTV_lib::$settings['restream_deny_unauthorised'] || ipTV_lib::$settings['detect_restream_block_user'])) {
                        ipTV_streaming::clientLog($streamID, $rUserInfo['id'], 'RESTREAM_DETECT', $userIP, json_encode(array('user_agent' => $rUserAgent, 'isp' => $rUserInfo['con_isp_name'], 'asn' => $rUserInfo['isp_asn'])), true);
                        generateError('RESTREAM_DETECT');
                    }
                }
            }
        }

        if ($rType == 'live') {
            if (!array_key_exists($rExtension, $rUserInfo["output_formats"])) {
                ipTV_streaming::ClientLog($streamID, $rUserInfo["id"], 'USER_DISALLOW_EXT', $userIP);
                generateError('USER_DISALLOW_EXT');
                // http_response_code(405);
            }
        }

        if (($rType == 'live' && ipTV_lib::$settings['show_expiring_video'] && !$rUserInfo['is_trial'] && !is_null($rUserInfo['exp_date']) && $rUserInfo['exp_date'] - 86400 * 7 <= time() && (86400 <= time() - $rUserInfo['last_expiration_video'] || !$rUserInfo['last_expiration_video']))) {

            $ipTV_db->query('UPDATE `lines` SET `last_expiration_video` = \'%s\' WHERE `id` = \'%s\';', time(), $rUserInfo['id']);

            ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_expiring_video', 'expiring_video_path', $rExtension);
        }
    } else {
        checkBruteforce($userIP, null, $rUsername);
        ipTV_streaming::clientLog($streamID, 0, 'AUTH_FAILED', $userIP);
        generateError('INVALID_CREDENTIALS');
    }

    if ($rIsMag) {
        $rForceHTTP = ipTV_lib::$settings['mag_disable_ssl'];
    } else {
        if ($rIsEnigma) {
            $rForceHTTP = true;
        } else {
            $rForceHTTP = false;
        }
    }

    switch ($rType) {
        case 'live':
            $rChannelInfo = ipTV_streaming::ChannelInfo($streamID, $rExtension, $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'live');
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
                        if ((ipTV_lib::$settings['disable_hls'] && (!$rUserInfo['is_restreamer'] || !ipTV_lib::$settings['disable_hls_allow_restream']))) {
                            generateError('HLS_DISABLED');
                        }

                        if ($rChannelInfo['direct_proxy']) {
                            generateError('HLS_DISABLED');
                        }

                        $rAdaptive = json_decode($rChannelInfo['adaptive_link'], true);

                        if (is_array($rAdaptive) && 0 < count($rAdaptive)) {
                            $rParts = array();

                            foreach (array_merge(array($streamID), $rAdaptive) as $rAdaptiveID) {
                                if ($rAdaptiveID != $streamID) {
                                    $rAdaptiveInfo = ipTV_streaming::ChannelInfo($streamID, $rExtension, $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'live');
                                    $rURL = ipTV_streaming::getStreamingURL($rAdaptiveInfo['redirect_id'], $rForceHTTP);
                                } else {
                                    $rAdaptiveInfo = $rChannelInfo;
                                }

                                $rStreamInfo = json_decode($rAdaptiveInfo['stream_info'], true);
                                $rBitrate = ($rStreamInfo['bitrate'] ?: 0);
                                $rWidth = ($rStreamInfo['codecs']['video']['width'] ?: 0);
                                $rHeight = ($rStreamInfo['codecs']['video']['height'] ?: 0);

                                if ((0 < $rBitrate && 0 < $rHeight && 0 < $rWidth)) {
                                    $tokenData = array('stream_id' => $rAdaptiveID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'pid' => $PID, 'channel_info' => array('redirect_id' => $rAdaptiveInfo['redirect_id'], 'pid' => $rAdaptiveInfo['pid'], 'on_demand' => $rAdaptiveInfo['on_demand'], 'monitor_pid' => $rAdaptiveInfo['monitor_pid']), 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_id' => $rUserInfo['pair_id'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'external_device' => $rExternalDevice, 'activity_start' => $rActivityStart, 'country_code' => $rCountryCode, 'video_codec' => ($rStreamInfo['codecs']['video']['codec_name'] ?: 'h264'), 'uuid' => $rUUID, 'adaptive' => array($rChannelInfo['redirect_id'], $streamID));
                                    $rStreamURL = (string) $rURL . '/sauth/' . encryptData(json_encode($tokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                                    $rParts[$rBitrate] = '#EXT-X-STREAM-INF:BANDWIDTH=' . $rBitrate . ',RESOLUTION=' . $rWidth . 'x' . $rHeight . "\n" . $rStreamURL;
                                }
                            }

                            if (0 < count($rParts)) {
                                krsort($rParts);
                                $rM3U8 = "#EXTM3U\n" . implode("\n", array_values($rParts));
                                ob_end_clean();
                                header('Content-Type: application/x-mpegurl');
                                header('Content-Length: ' . strlen($rM3U8));
                                echo $rM3U8;

                                exit();
                            }
                            ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_not_on_air_video', 'not_on_air_video_path', 'ts');
                            exit();
                        } else {
                            $tokenData = array('stream_id' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'pid' => $PID, 'channel_info' => array('redirect_id' => $rChannelInfo['redirect_id'], 'pid' => $rChannelInfo['pid'], 'on_demand' => $rChannelInfo['on_demand'], 'monitor_pid' => $rChannelInfo['monitor_pid']), 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_id' => $rUserInfo['pair_id'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'external_device' => $rExternalDevice, 'activity_start' => $rActivityStart, 'country_code' => $rCountryCode, 'video_codec' => $rVideoCodec, 'uuid' => $rUUID);

                            $rToken = encryptData(json_encode($tokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                            header('Location: ' . $rURL . '/sauth/' . $rToken);

                            exit();
                        }

                        // no break
                    case 'ts':
                        if ((ipTV_lib::$settings['disable_ts'] && (!$rUserInfo['is_restreamer'] || !ipTV_lib::$settings['disable_ts_allow_restream']))) {
                            generateError('TS_DISABLED');
                        }

                        $tokenData = array('stream_id' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'channel_info' => array('stream_id' => $rChannelInfo['stream_id'], 'redirect_id' => ($rChannelInfo['redirect_id'] ?: null), 'pid' => $rChannelInfo['pid'], 'on_demand' => $rChannelInfo['on_demand'], 'monitor_pid' => $rChannelInfo['monitor_pid'], 'proxy' => $rChannelInfo['direct_proxy']), 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_id' => $rUserInfo['pair_id'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'pid' => $PID, 'prebuffer' => $rPrebuffer, 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'external_device' => $rExternalDevice, 'video_codec' => $rVideoCodec, 'uuid' => $rUUID);

                        $rToken = encryptData(json_encode($tokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                        header('Location: ' . $rURL . '/sauth/' . $rToken);

                        exit();
                }
            } else {
                ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_not_on_air_video', 'not_on_air_video_path', $rExtension);
            }

            break;

        case 'movie':
        case 'series':
            $rChannelInfo = ipTV_streaming::ChannelInfo($streamID, $rExtension, $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'movie');

            if ($rChannelInfo) {
                $rURL = ipTV_streaming::getStreamingURL($rChannelInfo['redirect_id'], $rForceHTTP);

                if ($rChannelInfo['direct_proxy']) {
                    $rChannelInfo['bitrate'] = (json_decode($rChannelInfo['movie_properties'], true)['duration_secs'] ?: 0);
                }
                $tokenData = array('stream_id' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'type' => $rType, 'pid' => $PID, 'channel_info' => array('stream_id' => $rChannelInfo['stream_id'], 'bitrate' => $rChannelInfo['bitrate'], 'target_container' => $rChannelInfo['target_container'], 'redirect_id' => $rChannelInfo['redirect_id'], 'pid' => $rChannelInfo['pid'], 'proxy' => ($rChannelInfo['direct_proxy'] ? json_decode($rChannelInfo['stream_source'], true)[0] : null)), 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_id' => $rUserInfo['pair_id'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'is_mag' => $rIsMag, 'uuid' => $rUUID, 'http_range' => (isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null));

                $rToken = encryptData(json_encode($tokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                header('Location: ' . $rURL . '/vauth/' . $rToken);

                exit();
            }
            ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_not_on_air_video', 'not_on_air_video_path', 'ts');

            break;

        case 'timeshift':
            $rRedirectID = ipTV_streaming::ChannelInfo($streamID, $rExtension, $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'archive');
            if (!$rRedirectID) {
                ipTV_streaming::ShowVideo($user_info["is_restreamer"], 'show_not_on_air_video', 'not_on_air_video_path', $rExtension);
                break;
            }
            $rURL = ipTV_streaming::getStreamingURL($rRedirectID, $rForceHTTP);
            $rStartDate = ipTV_lib::$request['start'];
            $rDuration = intval(ipTV_lib::$request['duration']);

            switch ($rExtension) {
                case 'm3u8':
                    if ((ipTV_lib::$settings['disable_hls'] && (!$rUserInfo['is_restreamer'] || !ipTV_lib::$settings['disable_hls_allow_restream']))) {
                        generateError('HLS_DISABLED');
                    }

                    $tokenData = array('stream' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'pid' => $PID, 'start' => $rStartDate, 'duration' => $rDuration, 'redirect_id' => $rRedirectID, 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_line_info' => $rUserInfo['pair_line_info'], 'pair_id' => $rUserInfo['pair_id'], 'active_cons' => $rUserInfo['active_cons'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'uuid' => $rUUID, 'http_range' => (isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null));
                    $rToken = encryptData(json_encode($tokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                    header('Location: ' . $rURL . '/tsauth/' . $rToken);

                    exit();

                default:
                    if ((ipTV_lib::$settings['disable_ts'] && (!$rUserInfo['is_restreamer'] || !ipTV_lib::$settings['disable_ts_allow_restream']))) {
                        generateError('TS_DISABLED');
                    }

                    $rActivityStart = time();
                    $tokenData = array('stream' => $streamID, 'username' => $rUserInfo['username'], 'password' => $rUserInfo['password'], 'extension' => $rExtension, 'pid' => $PID, 'start' => $rStartDate, 'duration' => $rDuration, 'redirect_id' => $rRedirectID, 'user_info' => array('id' => $rUserInfo['id'], 'max_connections' => $rUserInfo['max_connections'], 'pair_line_info' => $rUserInfo['pair_line_info'], 'pair_id' => $rUserInfo['pair_id'], 'active_cons' => $rUserInfo['active_cons'], 'con_isp_name' => $rUserInfo['con_isp_name'], 'is_restreamer' => $rUserInfo['is_restreamer']), 'country_code' => $rCountryCode, 'activity_start' => $rActivityStart, 'uuid' => $rUUID, 'http_range' => (isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null));
                    $rToken = encryptData(json_encode($tokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);

                    header('Location: ' . $rURL . '/tsauth/' . $rToken);

                    exit();
            }
            // no break
        case 'subtitle':
            $rChannelInfo = ipTV_streaming::ChannelInfo($streamID, 'srt', $rUserInfo, $rCountryCode, $rUserInfo['con_isp_name'], 'movie');

            if ($rChannelInfo) {
                $rURL = ipTV_streaming::getStreamingURL($rChannelInfo['redirect_id'], null, $rForceHTTP);
                $tokenData = array('stream_id' => $streamID, 'sub_id' => (intval(ipTV_lib::$request['sid']) ?: 0), 'webvtt' => (intval(ipTV_lib::$request['webvtt']) ?: 0), 'expires' => time() + 5);
                $rToken = encryptData(json_encode($tokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
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
        checkFlood();
    }

    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
