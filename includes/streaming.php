<?php
class ipTV_streaming {
    public static $ipTV_db = null;

    public static function rtmpIps() {
        self::$ipTV_db->query("SELECT `ip` FROM `rtmp_ips`");
        return array_merge(array("127.0.0.1"), array_map("gethostbyname", ipTV_lib::arrayValuesRecursive(self::$ipTV_db->get_rows())));
    }
    public static function sendSignalFFMPEG($signalData, $segmentFile, $codec = 'h264', $return = false) {
        if (empty($signalData["xy_offset"])) {
            $x = rand(150, 380);
            $y = rand(110, 250);
        } else {
            list($x, $y) = explode("x", $signalData["xy_offset"]);
        }
        if ($return) {
            $rOutput = SIGNALS_PATH . $signalData['activity_id'] . '_' . $segmentFile;
            shell_exec(ipTV_lib::$FFMPEG_CPU . ' -copyts -vsync 0 -nostats -nostdin -hide_banner -loglevel quiet -y -i ' . escapeshellarg(STREAMS_PATH . $segmentFile) . ' -filter_complex "drawtext=fontfile=' . FFMPEG_FONTS_PATH . ":text='" . escapeshellcmd($signalData['message']) . "':fontsize=" . escapeshellcmd($signalData['font_size']) . ':x=' . intval($x) . ':y=' . intval($y) . ':fontcolor=' . escapeshellcmd($signalData['font_color']) . '" -map 0 -vcodec ' . $codec . ' -preset ultrafast -acodec copy -scodec copy -mpegts_flags +initial_discontinuity -mpegts_copyts 1 -f mpegts ' . escapeshellarg($rOutput));
            $data = file_get_contents($rOutput);
            ipTV_lib::unlinkFile($rOutput);
            return $data;
        }
        passthru(ipTV_lib::$FFMPEG_CPU . ' -copyts -vsync 0 -nostats -nostdin -hide_banner -loglevel quiet -y -i ' . escapeshellarg(STREAMS_PATH . $segmentFile) . ' -filter_complex "drawtext=fontfile=' . FFMPEG_FONTS_PATH . ":text='" . escapeshellcmd($signalData['message']) . "':fontsize=" . escapeshellcmd($signalData['font_size']) . ':x=' . intval($x) . ':y=' . intval($y) . ':fontcolor=' . escapeshellcmd($signalData['font_color']) . '" -map 0 -vcodec ' . $codec . ' -preset ultrafast -acodec copy -scodec copy -mpegts_flags +initial_discontinuity -mpegts_copyts 1 -f mpegts -');
        return true;
    }
    public static function getAllowedIPsAdmin($rForce = false) {
        if (!$rForce) {
            $rCache = ipTV_lib::getCache('allowed_ips', 60);
            if (!empty($rCache)) {
                return $rCache;
            }
        }
        $IPs = array('127.0.0.1', $_SERVER['SERVER_ADDR']);
        foreach (ipTV_lib::$Servers as $serverInfo) {
            if (!empty($serverInfo['whitelist_ips'])) {
                $whitelist_ips = json_decode($serverInfo['whitelist_ips'], true);
                if (is_array($whitelist_ips)) {
                    $IPs = array_merge($IPs, $whitelist_ips);
                }
            }
            $IPs[] = $serverInfo['server_ip'];
            if ($serverInfo['private_ip']) {
                $IPs[] = $serverInfo['private_ip'];
            }
            foreach (explode(',', $serverInfo['domain_name']) as $IP) {
                if (filter_var($IP, FILTER_VALIDATE_IP)) {
                    $IPs[] = $IP;
                }
            }
        }
        if (!empty(ipTV_lib::$settings['allowed_ips_admin'])) {
            $IPs = array_merge($IPs, explode(',', ipTV_lib::$settings['allowed_ips_admin']));
        }
        ipTV_lib::setCache('allowed_ips', $IPs);
        return array_unique($IPs);
    }
    public static function closeAndTransfer($activity_id) {
        file_put_contents(CONS_TMP_PATH . $activity_id, 1);
    }
    public static function getStreamData($streamID) {
        if (CACHE_STREAMS) {
            if (file_exists(TMP_PATH . $streamID . "_cacheStream") && time() - filemtime(TMP_PATH . $streamID . "_cacheStream") <= CACHE_STREAMS_TIME) {
                return igbinary_unserialize(file_get_contents(TMP_PATH . $streamID . "_cacheStream"));
            }
        }
        $rOutput = array();
        self::$ipTV_db->query('SELECT * FROM `streams` t1 LEFT JOIN `streams_types` t2 ON t2.type_id = t1.type WHERE t1.`id` = ?', $streamID);
        if (self::$ipTV_db->num_rows() > 0) {
            $rStreamInfo = self::$ipTV_db->get_row();
            $rServers = array();
            if ($rStreamInfo['direct_source'] == 0) {
                self::$ipTV_db->query('SELECT * FROM `streams_servers` WHERE `stream_id` = ?', $streamID);
                if (self::$ipTV_db->num_rows() > 0) {
                    $rServers = self::$ipTV_db->get_rows(true, 'server_id');
                }
            }
            $rOutput['bouquets'] = self::getBouquetMap($streamID);
            $rOutput['info'] = $rStreamInfo;
            $rOutput['servers'] = $rServers;
            if (CACHE_STREAMS) {
                file_put_contents(TMP_PATH . $streamID . "_cacheStream", igbinary_serialize($rOutput), LOCK_EX);
            }
        }
        return (!empty($rOutput) ? $rOutput : false);
    }
    public static function channelInfo($streamID, $extension, $userInfo, $rCountryCode, $rUserISP = '', $rType = '') {
        if (ipTV_lib::$cached) {
            $rStream = (igbinary_unserialize(file_get_contents(STREAMS_TMP_PATH . 'stream_' . $streamID)) ?: null);
            $rStream['bouquets'] = self::getBouquetMap($streamID);
        } else {
            $rStream = self::getStreamData($streamID);
        }
        if ($rStream) {
            $rStream['info']['bouquets'] = $rStream['bouquets'];
            $rAvailableServers = array();
            if ($rType == 'archive') {
                if (0 < $rStream['info']['tv_archive_duration'] && 0 < $rStream['info']['tv_archive_server_id'] && array_key_exists($rStream['info']['tv_archive_server_id'], ipTV_lib::$Servers)) {
                    $rAvailableServers = array($rStream['info']['tv_archive_server_id']);
                }
            } else {
                if ($rStream['info']['direct_source'] != 1) {
                    foreach (ipTV_lib::$Servers as $serverID => $serverInfo) {
                        if (!(!array_key_exists($serverID, $rStream['servers']) || !$serverInfo['server_online'] || $serverInfo['server_type'] != 0)) {
                            if (isset($rStream['servers'][$serverID])) {
                                if ($rType == 'movie') {
                                    if ((!empty($rStream['servers'][$serverID]['pid']) && $rStream['servers'][$serverID]['to_analyze'] == 0 && $rStream['servers'][$serverID]['stream_status'] == 0) && ($rStream['info']['target_container'] == $extension || ($extension = 'srt')) && $serverInfo['timeshift_only'] == 0) {
                                        $rAvailableServers[] = $serverID;
                                    }
                                } else {
                                    if (($rStream['servers'][$serverID]['on_demand'] == 1 && $rStream['servers'][$serverID]['stream_status'] != 1 || 0 < $rStream['servers'][$serverID]['pid'] && $rStream['servers'][$serverID]['stream_status'] == 0) && $rStream['servers'][$serverID]['to_analyze'] == 0 && (int) $rStream['servers'][$serverID]['delay_available_at'] <= time() && $serverInfo['timeshift_only'] == 0) {
                                        $rAvailableServers[] = $serverID;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    header('Location: ' . str_replace(' ', '%20', json_decode($rStream['info']['stream_source'], true)[0]));
                    exit();
                }
            }
            if (!empty($rAvailableServers)) {
                shuffle($rAvailableServers);
                $rServerCapacity = self::getCapacity();
                $rAcceptServers = array();
                foreach ($rAvailableServers as $serverID) {
                    $rOnlineClients = (isset($rServerCapacity[$serverID]['online_clients']) ? $rServerCapacity[$serverID]['online_clients'] : 0);
                    if ($rOnlineClients == 0) {
                        $rServerCapacity[$serverID]['capacity'] = 0;
                    }
                    $rAcceptServers[$serverID] = (0 < ipTV_lib::$Servers[$serverID]['total_clients'] && $rOnlineClients < ipTV_lib::$Servers[$serverID]['total_clients'] ? $rServerCapacity[$serverID]['capacity'] : false);
                }
                $rAcceptServers = array_filter($rAcceptServers, 'is_numeric');
                if (empty($rAcceptServers)) {
                    if ($rType == 'archive') {
                        return null;
                    }
                    return array();
                }
                $rKeys = array_keys($rAcceptServers);
                $rValues = array_values($rAcceptServers);
                array_multisort($rValues, SORT_ASC, $rKeys, SORT_ASC);
                $rAcceptServers = array_combine($rKeys, $rValues);
                if ($extension == 'rtmp' && array_key_exists(SERVER_ID, $rAcceptServers)) {
                    $rRedirectID = SERVER_ID;
                } else {
                    if (isset($userInfo) && $userInfo['force_server_id'] != 0 && array_key_exists($userInfo['force_server_id'], $rAcceptServers)) {
                        $rRedirectID = $userInfo['force_server_id'];
                    } else {
                        $rPriorityServers = array();
                        foreach (array_keys($rAcceptServers) as $serverID) {
                            if (ipTV_lib::$Servers[$serverID]['enable_geoip'] == 1) {
                                if (in_array($rCountryCode, ipTV_lib::$Servers[$serverID]['geoip_countries'])) {
                                    $rRedirectID = $serverID;
                                    break;
                                }
                                if (ipTV_lib::$Servers[$serverID]['geoip_type'] == 'strict') {
                                    unset($rAcceptServers[$serverID]);
                                } else {
                                    if (isset($rStream) && !ipTV_lib::$settings['ondemand_balance_equal'] && $rStream['servers'][$serverID]['on_demand']) {
                                        $rPriorityServers[$serverID] = (ipTV_lib::$Servers[$serverID]['geoip_type'] == 'low_priority' ? 3 : 2);
                                    } else {
                                        $rPriorityServers[$serverID] = (ipTV_lib::$Servers[$serverID]['geoip_type'] == 'low_priority' ? 2 : 1);
                                    }
                                }
                            } else {
                                if (ipTV_lib::$Servers[$serverID]['enable_isp'] == 1) {
                                    if (in_array(strtolower(trim(preg_replace('/[^A-Za-z0-9 ]/', '', $rUserISP))), ipTV_lib::$Servers[$serverID]['isp_names'])) {
                                        $rRedirectID = $serverID;
                                        break;
                                    }
                                    if (ipTV_lib::$Servers[$serverID]['isp_type'] == 'strict') {
                                        unset($rAcceptServers[$serverID]);
                                    } else {
                                        if (isset($rStream) && !ipTV_lib::$settings['ondemand_balance_equal'] && $rStream['servers'][$serverID]['on_demand']) {
                                            $rPriorityServers[$serverID] = (ipTV_lib::$Servers[$serverID]['isp_type'] == 'low_priority' ? 3 : 2);
                                        } else {
                                            $rPriorityServers[$serverID] = (ipTV_lib::$Servers[$serverID]['isp_type'] == 'low_priority' ? 2 : 1);
                                        }
                                    }
                                } else {
                                    if (isset($rStream) && !ipTV_lib::$settings['ondemand_balance_equal'] && $rStream['servers'][$serverID]['on_demand']) {
                                        $rPriorityServers[$serverID] = 2;
                                    } else {
                                        $rPriorityServers[$serverID] = 1;
                                    }
                                }
                            }
                        }
                        if (!(empty($rPriorityServers) && empty($rRedirectID))) {
                            $rRedirectID = (empty($rRedirectID) ? array_search(min($rPriorityServers), $rPriorityServers) : $rRedirectID);
                        } else {
                            return false;
                        }
                    }
                }
                if ($rType == 'archive') {
                    return $rRedirectID;
                }
                $rStream['info']['redirect_id'] = $rRedirectID;
                $fc4c58c5d1cd68d1 = $rRedirectID;
                return array_merge($rStream['info'], $rStream['servers'][$fc4c58c5d1cd68d1]);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public static function getCapacity() {
        self::$ipTV_db->query('SELECT `server_id`, COUNT(*) AS `online_clients` FROM `lines_live` WHERE `server_id` <> 0 AND `hls_end` = 0 GROUP BY `server_id`;');
        $rRows = self::$ipTV_db->get_rows(true, 'server_id');

        if (ipTV_lib::$settings['split_by'] == 'band') {
            $rServerSpeed = array();
            foreach (array_keys(ipTV_lib::$Servers) as $serverID) {
                $rServerHardware = json_decode(ipTV_lib::$Servers[$serverID]['server_hardware'], true);
                if (!empty($rServerHardware['network_speed'])) {
                    $rServerSpeed[$serverID] = (float) $rServerHardware['network_speed'];
                } else {
                    if (0 < ipTV_lib::$Servers[$serverID]['network_guaranteed_speed']) {
                        $rServerSpeed[$serverID] = ipTV_lib::$Servers[$serverID]['network_guaranteed_speed'];
                    } else {
                        $rServerSpeed[$serverID] = 1000;
                    }
                }
            }
            foreach ($rRows as $serverID => $rRow) {
                $rCurrentOutput = intval(ipTV_lib::$Servers[$serverID]['watchdog']['bytes_sent'] / 125000);
                $rRows[$serverID]['capacity'] = (float) ($rCurrentOutput / (($rServerSpeed[$serverID] ?: 1000)));
            }
        } else {
            if (ipTV_lib::$settings['split_by'] == 'maxclients') {
                foreach ($rRows as $serverID => $rRow) {
                    $rRows[$serverID]['capacity'] = (float) ($rRow['online_clients'] / ((ipTV_lib::$Servers[$serverID]['total_clients'] ?: 1)));
                }
            } else {
                if (ipTV_lib::$settings['split_by'] == 'guar_band') {
                    foreach ($rRows as $serverID => $rRow) {
                        $rCurrentOutput = intval(ipTV_lib::$Servers[$serverID]['watchdog']['bytes_sent'] / 125000);
                        $rRows[$serverID]['capacity'] = (float) ($rCurrentOutput / ((ipTV_lib::$Servers[$serverID]['network_guaranteed_speed'] ?: 1)));
                    }
                } else {
                    foreach ($rRows as $serverID => $rRow) {
                        $rRows[$serverID]['capacity'] = $rRow['online_clients'];
                    }
                }
            }
        }
        file_put_contents(CACHE_TMP_PATH . "servers_capacity", json_encode($rRows), LOCK_EX);
        return $rRows;
    }
    public static function getUserInfo($userID = null, $username = null, $password = null, $getChannelIDs = false, $getBouquetInfo = false, $IP = '') {
        $userInfo = null;
        if (ipTV_lib::$cached) {
            if (empty($password) && empty($userID) && strlen($username) == 32) {
                if (ipTV_lib::$settings['case_sensitive_line']) {
                    $userID = intval(file_get_contents(USER_TMP_PATH . 'user_t_' . $username));
                } else {
                    $userID = intval(file_get_contents(USER_TMP_PATH . 'user_t_' . strtolower($username)));
                }
            } else {
                if (!empty($username) && !empty($password)) {
                    if (ipTV_lib::$settings['case_sensitive_line']) {
                        $userID = intval(file_get_contents(USER_TMP_PATH . 'user_c_' . $username . '_' . $password));
                    } else {
                        $userID = intval(file_get_contents(USER_TMP_PATH . 'user_c_' . strtolower($username) . '_' . strtolower($password)));
                    }
                } else {
                    if (empty($userID)) {
                        return false;
                    }
                }
            }
            if ($userID) {
                $userInfo = igbinary_unserialize(file_get_contents(USER_TMP_PATH . 'user_i_' . $userID));
            }
        } else {
            if (empty($password) && empty($userID) && strlen($username) == 32) {
                self::$ipTV_db->query('SELECT * FROM `lines` WHERE `is_mag` = 0 AND `is_e2` = 0 AND `access_token` = ? AND LENGTH(`access_token`) = 32', $username);
            } else {
                if (!empty($username) && !empty($password)) {
                    self::$ipTV_db->query('SELECT `lines`.*, `mag_devices`.`token` AS `mag_token` FROM `lines` LEFT JOIN `mag_devices` ON `mag_devices`.`user_id` = `lines`.`id` WHERE `username` = ? AND `password` = ? LIMIT 1', $username, $password);
                } else {
                    if (!empty($userID)) {
                        self::$ipTV_db->query('SELECT `lines`.*, `mag_devices`.`token` AS `mag_token` FROM `lines` LEFT JOIN `mag_devices` ON `mag_devices`.`user_id` = `lines`.`id` WHERE `id` = ?', $userID);
                    } else {
                        return false;
                    }
                }
            }
            if (self::$ipTV_db->num_rows() > 0) {
                $userInfo = self::$ipTV_db->get_row();
            }
        }
        if (!$userInfo) {
            return false;
        }
        if (ipTV_lib::$settings['county_override_1st'] == 1 && empty($userInfo['forced_country']) && !empty($IP) && $userInfo['max_connections'] == 1) {
            $userInfo['forced_country'] = self::getIPInfo($IP)['registered_country']['iso_code'];
            if (ipTV_lib::$cached) {
                ipTV_lib::setSignal('forced_country/' . $userInfo['id'], $userInfo['forced_country']);
            } else {
                self::$ipTV_db->query('UPDATE `lines` SET `forced_country` = ? WHERE `id` = ?', $userInfo['forced_country'], $userInfo['id']);
            }
        }
        $userInfo['bouquet'] = json_decode($userInfo['bouquet'], true);
        $userInfo['allowed_ips'] = @array_filter(@array_map('trim', @json_decode($userInfo['allowed_ips'], true)));
        $userInfo['allowed_ua'] = @array_filter(@array_map('trim', @json_decode($userInfo['allowed_ua'], true)));
        $userInfo['allowed_outputs'] = array_map('intval', json_decode($userInfo['allowed_outputs'], true));
        $userInfo['output_formats'] = array();
        if (ipTV_lib::$cached) {
            foreach (igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'access_output')) as $rRow) {
                if (in_array(intval($rRow['access_output_id']), $userInfo['allowed_outputs'])) {
                    $userInfo['output_formats'][] = $rRow['output_key'];
                }
            }
        } else {
            self::$ipTV_db->query('SELECT `access_output_id`, `output_key` FROM `access_output`;');
            foreach (self::$ipTV_db->get_rows() as $rRow) {
                if (in_array(intval($rRow['access_output_id']), $userInfo['allowed_outputs'])) {
                    $userInfo['output_formats'][] = $rRow['output_key'];
                }
            }
        }

        $userInfo['con_isp_name'] = null;
        $userInfo['isp_violate'] = 0;
        $userInfo['isp_is_server'] = 0;

        if (ipTV_lib::$settings['show_isps'] == 1 || !empty($IP)) {
            $ISPLock = self::getISP($IP);
            if (is_array($ISPLock)) {
                if (!empty($ISPLock['isp'])) {
                    $userInfo['con_isp_name'] = $ISPLock['isp'];
                    $userInfo['isp_asn'] = $ISPLock['autonomous_system_number'];
                    $userInfo['isp_violate'] = self::checkISP($userInfo['con_isp_name']);
                    if (ipTV_lib::$settings['block_svp'] == 1) {
                        $userInfo['isp_is_server'] = intval(self::checkServer($userInfo['isp_asn']));
                    }
                }
            }
            if (!empty($userInfo['con_isp_name']) && ipTV_lib::$settings['enable_isp_lock'] == 1 && $userInfo['is_stalker'] == 0 && $userInfo['is_isplock'] == 1 && !empty($userInfo['isp_desc']) && strtolower($userInfo['con_isp_name']) != strtolower($userInfo['isp_desc'])) {
                $userInfo['isp_violate'] = 1;
            }
            if ($userInfo['isp_violate'] == 0 && strtolower($userInfo['con_isp_name']) != strtolower($userInfo['isp_desc'])) {
                if (ipTV_lib::$cached) {
                    ipTV_lib::setSignal('isp/' . $userInfo['id'], json_encode(array($userInfo['con_isp_name'], $userInfo['isp_asn'])));
                } else {
                    self::$ipTV_db->query('UPDATE `lines` SET `isp_desc` = ?, `as_number` = ? WHERE `id` = ?', $userInfo['con_isp_name'], $userInfo['isp_asn'], $userInfo['id']);
                }
            }
        }

        if ($getChannelIDs) {
            $rLiveIDs = $rVODIDs = $rRadioIDs = $rCategoryIDs = $rChannelIDs = $rSeriesIDs = array();
            foreach ($userInfo['bouquet'] as $ID) {
                if (isset(ipTV_lib::$Bouquets[$ID]['streams'])) {
                    $rChannelIDs = array_merge($rChannelIDs, ipTV_lib::$Bouquets[$ID]['streams']);
                }
                if (isset(ipTV_lib::$Bouquets[$ID]['series'])) {
                    $rSeriesIDs = array_merge($rSeriesIDs, ipTV_lib::$Bouquets[$ID]['series']);
                }
                if (isset(ipTV_lib::$Bouquets[$ID]['channels'])) {
                    $rLiveIDs = array_merge($rLiveIDs, ipTV_lib::$Bouquets[$ID]['channels']);
                }
                if (isset(ipTV_lib::$Bouquets[$ID]['movies'])) {
                    $rVODIDs = array_merge($rVODIDs, ipTV_lib::$Bouquets[$ID]['movies']);
                }
                if (isset(ipTV_lib::$Bouquets[$ID]['radios'])) {
                    $rRadioIDs = array_merge($rRadioIDs, ipTV_lib::$Bouquets[$ID]['radios']);
                }
            }
            $userInfo['channel_ids'] = array_map('intval', array_unique($rChannelIDs));
            $userInfo['series_ids'] = array_map('intval', array_unique($rSeriesIDs));
            $userInfo['vod_ids'] = array_map('intval', array_unique($rVODIDs));
            $userInfo['live_ids'] = array_map('intval', array_unique($rLiveIDs));
            $userInfo['radio_ids'] = array_map('intval', array_unique($rRadioIDs));
        }
        $rAllowedCategories = array();
        $rCategoryMap = igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'category_map'));
        foreach ($userInfo['bouquet'] as $ID) {
            $rAllowedCategories = array_merge($rAllowedCategories, ($rCategoryMap[$ID] ?: array()));
        }
        $userInfo['category_ids'] = array_values(array_unique($rAllowedCategories));
        return $userInfo;
    }
    public static function categoriesBouq($category_id, $bouquets) {
        if (!file_exists(TMP_PATH . 'categories_bouq')) {
            return true;
        }
        if (!is_array($bouquets)) {
            $bouquets = json_decode($bouquets, true);
        }
        $output = igbinary_unserialize(file_get_contents(TMP_PATH . 'categories_bouq'));
        foreach ($bouquets as $bouquet) {
            if (isset($output[$bouquet])) {
                if (in_array($category_id, $output[$bouquet])) {
                    return true;
                }
            }
        }
        return false;
    }
    public static function getAdultCategories() {
        $rReturn = array();
        foreach (ipTV_lib::$categories as $rCategory) {
            if ($rCategory['is_adult']) {
                $rReturn[] = intval($rCategory['id']);
            }
        }
        return $rReturn;
    }
    public static function getMagInfo($mag_id = null, $mac = null, $get_ChannelIDS = false, $getBouquetInfo = false, $get_cons = false) {
        if (empty($mag_id)) {
            self::$ipTV_db->query('SELECT * FROM `mag_devices` WHERE `mac` = ?', base64_encode($mac));
        } else {
            self::$ipTV_db->query('SELECT * FROM `mag_devices` WHERE `mag_id` = ?', $mag_id);
        }
        if (self::$ipTV_db->num_rows() > 0) {
            $maginfo = array();
            $maginfo['mag_device'] = self::$ipTV_db->get_row();
            $maginfo['mag_device']['mac'] = base64_decode($maginfo['mag_device']['mac']);
            $maginfo['user_info'] = array();
            if ($userInfo = self::getUserInfo($maginfo['mag_device']['user_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                $maginfo['user_info'] = $userInfo;
            }
            $maginfo['pair_line_info'] = array();
            if (!empty($maginfo['user_info'])) {
                $maginfo['pair_line_info'] = array();
                if (!is_null($maginfo['user_info']['pair_id'])) {
                    if ($userInfo = self::getUserInfo($maginfo['user_info']['pair_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                        $maginfo['pair_line_info'] = $userInfo;
                    }
                }
            }
            return $maginfo;
        }
        return false;
    }
    public static function enigmaDevices($maginfo, $get_ChannelIDS = false, $getBouquetInfo = false, $get_cons = false) {
        if (empty($maginfo['device_id'])) {
            self::$ipTV_db->query('SELECT * FROM `enigma2_devices` WHERE `mac` = ?', $maginfo['mac']);
        } else {
            self::$ipTV_db->query('SELECT * FROM `enigma2_devices` WHERE `device_id` = ?', $maginfo['device_id']);
        }
        if (self::$ipTV_db->num_rows() > 0) {
            $enigma2devices = array();
            $enigma2devices['enigma2'] = self::$ipTV_db->get_row();
            $enigma2devices['user_info'] = array();
            if ($userInfo = self::getUserInfo($enigma2devices['enigma2']['user_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                $enigma2devices['user_info'] = $userInfo;
            }
            $enigma2devices['pair_line_info'] = array();
            if (!empty($enigma2devices['user_info'])) {
                $enigma2devices['pair_line_info'] = array();
                if (!is_null($enigma2devices['user_info']['pair_id'])) {
                    if ($userInfo = self::getUserInfo($enigma2devices['user_info']['pair_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons)) {
                        $enigma2devices['pair_line_info'] = $userInfo;
                    }
                }
            }
            return $enigma2devices;
        }
        return false;
    }

    public static function validateConnections($userInfo, $IP = null, $userAgent = null) {
        if ($userInfo['max_connections'] != 0) {
            if (!empty($userInfo['pair_id'])) {
                self::closeConnections($userInfo['pair_id'], $userInfo['max_connections'], $IP, $userAgent);
            }
            self::closeConnections($userInfo['id'], $userInfo['max_connections'], $IP, $userAgent);
        }
    }
    /**
     * Closes active connections for a user when they exceed the maximum allowed limit
     *
     * @param int $userID The ID of the user whose connections need to be managed
     * @param int $rMaxConnections The maximum number of allowed connections
     * @param string $IP Optional. The IP address to filter connections (default: null)
     * @param string $userAgent Optional. The user agent to filter connections (default: null)
     *
     * @return int|null Returns the number of killed connections or null if no connections needed to be closed
     */
    public static function closeConnections($userID, $rMaxConnections, $IP = null, $userAgent = null) {
        if (ipTV_lib::$settings['redis_handler']) {
            $rConnections = array();
            $rKeys = self::getConnections($userID, true, true);
            $rToKill = count($rKeys) - $rMaxConnections;
            if ($rToKill > 0) {
                foreach (array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rKeys)) as $rConnection) {
                    if (is_array($rConnection)) {
                        $rConnections[] = $rConnection;
                    }
                }
                unset($rKeys);
                $rDate = array_column($rConnections, 'date_start');
                array_multisort($rDate, SORT_ASC, $rConnections);
            } else {
                return null;
            }
        } else {
            self::$ipTV_db->query('SELECT `lines_live`.*, `on_demand` FROM `lines_live` LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `lines_live`.`stream_id` AND `streams_servers`.`server_id` = `lines_live`.`server_id` WHERE `lines_live`.`user_id` = ? AND `lines_live`.`hls_end` = 0 ORDER BY `lines_live`.`activity_id` ASC', $userID);

            $rConnectionCount = self::$ipTV_db->num_rows();
            $rToKill = $rConnectionCount - $rMaxConnections;
            if ($rToKill > 0) {
                $rConnections = self::$ipTV_db->get_rows();
            } else {
                return null;
            }
        }
        $IP = self::getUserIP();
        $rKilled = 0;
        $rDelSID = $rDelUUID = $IDs = array();
        if ($IP && $userAgent) {
            $rKillTypes = array(2, 1, 0);
        } else {
            if ($IP) {
                $rKillTypes = array(1, 0);
            } else {
                $rKillTypes = array(0);
            }
        }
        foreach ($rKillTypes as $rKillOwnIP) {
            $i = 0;
            while ($i < count($rConnections) && $rKilled < $rToKill) {
                if ($rKilled != $rToKill) {
                    if ($rConnections[$i]['pid'] != getmypid()) {
                        if ($rConnections[$i]['user_ip'] == $IP && $rConnections[$i]['user_agent'] == $userAgent && $rKillOwnIP == 2 || $rConnections[$i]['user_ip'] == $IP && $rKillOwnIP == 1 || $rKillOwnIP == 0) {
                            if (self::closeConnection($rConnections[$i])) {
                                $rKilled++;
                                if ($rConnections[$i]['container'] != 'hls') {
                                    if (ipTV_lib::$settings['redis_handler']) {
                                        $IDs[] = $rConnections[$i];
                                    } else {
                                        $IDs[] = intval($rConnections[$i]['activity_id']);
                                    }
                                    $rDelUUID[] = $rConnections[$i]['uuid'];
                                    $rDelSID[$rConnections[$i]['stream_id']][] = $rDelUUID;
                                }
                                if ($rConnections[$i]['on_demand'] && $rConnections[$i]['server_id'] == SERVER_ID && ipTV_lib::$settings['on_demand_instant_off']) {
                                    self::removeFromQueue($rConnections[$i]['stream_id'], $rConnections[$i]['pid']);
                                }
                            }
                        }
                    }
                    $i++;
                } else {
                    break;
                }
            }
        }
        if (!empty($IDs)) {
            if (ipTV_lib::$settings['redis_handler']) {
                $rUUIDs = array();
                $rRedis = ipTV_lib::$redis->multi();
                foreach ($IDs as $rConnection) {
                    $rRedis->zRem('LINE#' . $rConnection['identity'], $rConnection['uuid']);
                    $rRedis->zRem('LINE_ALL#' . $rConnection['identity'], $rConnection['uuid']);
                    $rRedis->zRem('STREAM#' . $rConnection['stream_id'], $rConnection['uuid']);
                    $rRedis->zRem('SERVER#' . $rConnection['server_id'], $rConnection['uuid']);
                    if ($rConnection['user_id']) {
                        $rRedis->zRem('SERVER_LINES#' . $rConnection['server_id'], $rConnection['uuid']);
                    }
                    $rRedis->del($rConnection['uuid']);
                    $rUUIDs[] = $rConnection['uuid'];
                }
                $rRedis->zRem('CONNECTIONS', ...$rUUIDs);
                $rRedis->zRem('LIVE', ...$rUUIDs);
                $rRedis->sRem('ENDED', ...$rUUIDs);
                $rRedis->exec();
            } else {
                self::$ipTV_db->query('DELETE FROM `lines_live` WHERE `activity_id` IN (' . implode(',', array_map('intval', $IDs)) . ')');
            }
            foreach ($rDelUUID as $rUUID) {
                ipTV_lib::unlinkFile(CONS_TMP_PATH . $rUUID);
            }
            foreach ($rDelSID as $streamID => $rUUIDs) {
                foreach ($rUUIDs as $rUUID) {
                    ipTV_lib::unlinkFile(CONS_TMP_PATH . $streamID . '/' . $rUUID);
                }
            }
        }
        return $rKilled;
    }
    public static function addToQueue($streamID, $rAddPID) {
        $rActivePIDs = $PIDs = array();
        if (!file_exists(SIGNALS_TMP_PATH . 'queue_' . intval($streamID))) {
        } else {
            $PIDs = igbinary_unserialize(file_get_contents(SIGNALS_TMP_PATH . 'queue_' . intval($streamID)));
        }
        foreach ($PIDs as $PID) {
            if (!self::isProcessRunning($PID, 'php-fpm')) {
            } else {
                $rActivePIDs[] = $PID;
            }
        }
        if (in_array($rActivePIDs, $rAddPID)) {
        } else {
            $rActivePIDs[] = $rAddPID;
        }
        file_put_contents(SIGNALS_TMP_PATH . 'queue_' . intval($streamID), igbinary_serialize($rActivePIDs));
    }
    /**
     * Removes a process ID from the stream queue and manages queue cleanup
     *
     * @param int $streamID The ID of the stream to manage
     * @param int $PID The process ID to remove from the queue
     *
     * @return void
     */
    public static function removeFromQueue($streamID, $PID) {
        $rActivePIDs = array();
        foreach ((igbinary_unserialize(file_get_contents(SIGNALS_TMP_PATH . 'queue_' . intval($streamID))) ?: array()) as $rActivePID) {
            if (self::isProcessRunning($rActivePID, 'php-fpm') && $PID != $rActivePID) {
                $rActivePIDs[] = $rActivePID;
            }
        }
        if (0 < count($rActivePIDs)) {
            file_put_contents(SIGNALS_TMP_PATH . 'queue_' . intval($streamID), igbinary_serialize($rActivePIDs));
        } else {
            unlink(SIGNALS_TMP_PATH . 'queue_' . intval($streamID));
        }
    }
    /**
     * Closes an active streaming connection and performs cleanup operations
     *
     * @param mixed $rActivityInfo Either an array containing activity information or a string containing UUID/activity_id
     * @param boolean $rRemove Whether to remove the connection data from storage (default: true)
     * @param boolean $rEnd Whether to mark the connection as ended for HLS streams (default: true)
     *
     * @return boolean Returns true if connection was successfully closed, false otherwise
     */
    public static function closeConnection($rActivityInfo, $rRemove = true, $rEnd = true) {
        if (!empty($rActivityInfo)) {
            if (ipTV_lib::$settings['redis_handler'] && !is_object(ipTV_lib::$redis)) {
                ipTV_lib::connectRedis();
            }
            if (!is_array($rActivityInfo)) {
                if (!ipTV_lib::$settings['redis_handler']) {
                    if (strlen(strval($rActivityInfo)) == 32) {
                        self::$ipTV_db->query('SELECT * FROM `lines_live` WHERE `uuid` = ?', $rActivityInfo);
                    } else {
                        self::$ipTV_db->query('SELECT * FROM `lines_live` WHERE `activity_id` = ?', $rActivityInfo);
                    }
                    $rActivityInfo = self::$ipTV_db->get_row();
                } else {
                    $rActivityInfo = igbinary_unserialize(ipTV_lib::$redis->get($rActivityInfo));
                }
            } else {
                if ($rActivityInfo['container'] == 'rtmp') {
                    if ($rActivityInfo['server_id'] == SERVER_ID) {
                        shell_exec('wget --timeout=2 -O /dev/null -o /dev/null "' . ipTV_lib::$Servers[SERVER_ID]['rtmp_mport_url'] . 'control/drop/client?clientid=' . intval($rActivityInfo['pid']) . '" >/dev/null 2>/dev/null &');
                    } else {
                        if (ipTV_lib::$settings['redis_handler']) {
                            self::redisSignal($rActivityInfo['pid'], $rActivityInfo['server_id'], 1);
                        } else {
                            self::$ipTV_db->query('INSERT INTO `signals` (`pid`,`server_id`,`rtmp`,`time`) VALUES(?,?,?,UNIX_TIMESTAMP())', $rActivityInfo['pid'], $rActivityInfo['server_id'], 1);
                        }
                    }
                } else {
                    if ($rActivityInfo['container'] == 'hls') {
                        if (!$rRemove && $rEnd && $rActivityInfo['hls_end'] == 0) {
                            if (ipTV_lib::$settings['redis_handler']) {
                                self::updateConnection($rActivityInfo, array(), 'close');
                            } else {
                                self::$ipTV_db->query('UPDATE `lines_live` SET `hls_end` = 1 WHERE `activity_id` = ?', $rActivityInfo['activity_id']);
                            }
                            ipTV_lib::unlinkFile(CONS_TMP_PATH . $rActivityInfo['stream_id'] . '/' . $rActivityInfo['uuid']);
                        }
                    } else {
                        if ($rActivityInfo['server_id'] == SERVER_ID) {
                            if ($rActivityInfo['pid'] != getmypid() && is_numeric($rActivityInfo['pid']) && 0 < $rActivityInfo['pid']) {
                                posix_kill(intval($rActivityInfo['pid']), 9);
                            }
                        } else {
                            if (ipTV_lib::$settings['redis_handler']) {
                                self::redisSignal($rActivityInfo['pid'], $rActivityInfo['server_id'], 0);
                            } else {
                                self::$ipTV_db->query('INSERT INTO `signals` (`pid`,`server_id`,`time`) VALUES(?,?,UNIX_TIMESTAMP())', $rActivityInfo['pid'], $rActivityInfo['server_id']);
                            }
                        }
                    }
                }
                if ($rActivityInfo['server_id'] == SERVER_ID) {
                    ipTV_lib::unlinkFile(CONS_TMP_PATH . $rActivityInfo['uuid']);
                }
                if ($rRemove) {
                    if ($rActivityInfo['server_id'] == SERVER_ID) {
                        ipTV_lib::unlinkFile(CONS_TMP_PATH . $rActivityInfo['stream_id'] . '/' . $rActivityInfo['uuid']);
                    }
                    if (ipTV_lib::$settings['redis_handler']) {
                        $rRedis = ipTV_lib::$redis->multi();
                        $rRedis->zRem('LINE#' . $rActivityInfo['identity'], $rActivityInfo['uuid']);
                        $rRedis->zRem('LINE_ALL#' . $rActivityInfo['identity'], $rActivityInfo['uuid']);
                        $rRedis->zRem('STREAM#' . $rActivityInfo['stream_id'], $rActivityInfo['uuid']);
                        $rRedis->zRem('SERVER#' . $rActivityInfo['server_id'], $rActivityInfo['uuid']);
                        if ($rActivityInfo['user_id']) {
                            $rRedis->zRem('SERVER_LINES#' . $rActivityInfo['server_id'], $rActivityInfo['uuid']);
                        }
                        $rRedis->del($rActivityInfo['uuid']);
                        $rRedis->zRem('CONNECTIONS', $rActivityInfo['uuid']);
                        $rRedis->zRem('LIVE', $rActivityInfo['uuid']);
                        $rRedis->sRem('ENDED', $rActivityInfo['uuid']);
                        $rRedis->exec();
                    } else {
                        self::$ipTV_db->query('DELETE FROM `lines_live` WHERE `activity_id` = ?', $rActivityInfo['activity_id']);
                    }
                }
                self::writeOfflineActivity($rActivityInfo['server_id'], $rActivityInfo['user_id'], $rActivityInfo['stream_id'], $rActivityInfo['date_start'], $rActivityInfo['user_agent'], $rActivityInfo['user_ip'], $rActivityInfo['container'], $rActivityInfo['geoip_country_code'], $rActivityInfo['isp'], $rActivityInfo['external_device'], $rActivityInfo['divergence']);
                return true;
            }
            return false;
        }
        return false;
    }
    public static function closeLastCon($user_id, $max_connections) {
        self::$ipTV_db->query('SELECT * FROM `lines_live` WHERE `user_id` = ? ORDER BY activity_id ASC', $user_id);
        $rows = self::$ipTV_db->get_rows();
        $length = count($rows) - $max_connections + 1;
        if ($length <= 0) {
            return;
        }
        $total = 0;
        $connections = array();
        $index = 0;
        while ($index < count($rows) && $index < $length) {
            if ($rows[$index]['hls_end'] == 1) {
                continue;
            }
            if (self::removeConnection($rows[$index], false)) {
                ++$total;
                if ($rows[$index]['container'] != 'hls') {
                    $connections[] = $rows[$index]['activity_id'];
                }
            }
            $index++;
        }
        if (!empty($connections)) {
            self::$ipTV_db->query('DELETE FROM `lines_live` WHERE `activity_id` IN (' . implode(',', $connections) . ')');
        }
        return $total;
    }
    public static function removeConnection($activity_id, $ActionUserActivityNow = true) {
        if (empty($activity_id)) {
            return false;
        }
        if (empty($activity_id['activity_id'])) {
            self::$ipTV_db->query('SELECT * FROM `lines_live` WHERE `activity_id` = ?', $activity_id);
            $activity_id = self::$ipTV_db->get_row();
        }
        if (empty($activity_id)) {
            return false;
        }
        if (!($activity_id['container'] == 'rtmp')) {
            if ($activity_id['container'] == 'hls') {
                if (!$ActionUserActivityNow) {
                    self::$ipTV_db->query('UPDATE `lines_live` SET `hls_end` = 1 WHERE `activity_id` = ?', $activity_id['activity_id']);
                }
            } else {
                if ($activity_id['server_id'] == SERVER_ID) {
                    shell_exec("kill -9 {$activity_id['pid']} >/dev/null 2>/dev/null &");
                } else {
                    self::$ipTV_db->query('INSERT INTO `signals` (`pid`,`server_id`,`time`) VALUES(?,?,UNIX_TIMESTAMP())', $activity_id['pid'], $activity_id['server_id']);
                }
                if ($activity_id['server_id'] == SERVER_ID) {
                    shell_exec('wget --timeout=2 -O /dev/null -o /dev/null "' . ipTV_lib::$Servers[SERVER_ID]['rtmp_mport_url'] . "control/drop/client?clientid={$activity_id['pid']}\" >/dev/null 2>/dev/null &");
                } else {
                    self::$ipTV_db->query('INSERT INTO `signals` (`pid`,`server_id`,`rtmp`,`time`) VALUES(?,?,?,UNIX_TIMESTAMP())', $activity_id['pid'], $activity_id['server_id'], 1);
                }
            }
            if ($ActionUserActivityNow) {
                self::$ipTV_db->query('DELETE FROM `lines_live` WHERE `activity_id` = ?', $activity_id['activity_id']);
            }
            self::writeOfflineActivity($activity_id['server_id'], $activity_id['user_id'], $activity_id['stream_id'], $activity_id['date_start'], $activity_id['user_agent'], $activity_id['user_ip'], $activity_id['container'], $activity_id['geoip_country_code'], $activity_id['isp'], $activity_id['external_device']);
            return true;
        }
    }
    public static function playDone($PID) {
        if (empty($PID)) {
            return false;
        }
        self::$ipTV_db->query('SELECT * FROM `lines_live` WHERE `container` = \'rtmp\' AND `pid` = ? AND `server_id` = ?', $PID, SERVER_ID);
        if (self::$ipTV_db->num_rows() > 0) {
            $activity_id = self::$ipTV_db->get_row();
            self::$ipTV_db->query('DELETE FROM `lines_live` WHERE `activity_id` = ?', $activity_id['activity_id']);
            self::writeOfflineActivity($activity_id['server_id'], $activity_id['user_id'], $activity_id['stream_id'], $activity_id['date_start'], $activity_id['user_agent'], $activity_id['user_ip'], $activity_id['container'], $activity_id['geoip_country_code'], $activity_id['isp'], $activity_id['external_device']);
            return true;
        }
        return false;
    }
    public static function writeOfflineActivity($serverID, $userID, $streamID, $start, $userAgent, $IP, $extension, $GeoIP, $rISP, $rExternalDevice = '', $rDivergence = 0) {
        if (ipTV_lib::$settings['save_closed_connection'] != 0) {
            if ($serverID && $userID && $streamID) {
                $rActivityInfo = array('user_id' => intval($userID), 'stream_id' => intval($streamID), 'server_id' => intval($serverID), 'date_start' => intval($start), 'user_agent' => $userAgent, 'user_ip' => htmlentities($IP), 'date_end' => time(), 'container' => $extension, 'geoip_country_code' => $GeoIP, 'isp' => $rISP, 'external_device' => htmlentities($rExternalDevice), 'divergence' => intval($rDivergence));
                file_put_contents(LOGS_TMP_PATH . 'activity', base64_encode(json_encode($rActivityInfo)) . "\n", FILE_APPEND | LOCK_EX);
            }
        } else {
            return null;
        }
    }
    /**
     * Logs client actions to a file if client_logs_save setting is enabled or bypass flag is set to true.
     *
     * @param int $streamID The ID of the stream.
     * @param int $userID The ID of the user performing the action.
     * @param string $action The action being performed.
     * @param string $IP The IP address of the user.
     * @param string $data Additional data to be logged (optional).
     * @param bool $bypass Flag to bypass the client_logs_save setting (optional).
     * @return void|null
     */
    public static function clientLog($streamID, $userID, $action, $IP, $data = '', $bypass = false) {
        if (ipTV_lib::$settings['client_logs_save'] != 0 || $bypass) {
            $user_agent = (!empty($_SERVER['HTTP_USER_AGENT']) ? htmlentities($_SERVER['HTTP_USER_AGENT']) : '');
            $data = array('user_id' => $userID, 'stream_id' => $streamID, 'action' => $action, 'query_string' => htmlentities($_SERVER['QUERY_STRING']), 'user_agent' => $user_agent, 'user_ip' => $IP, 'time' => time(), 'extra_data' => $data);
            file_put_contents(LOGS_TMP_PATH . 'client_request.log', base64_encode(json_encode($data)) . "\n", FILE_APPEND);
        } else {
            return null;
        }
    }
    public static function streamLog($streamID, $serverID, $rAction, $rSource = '') {
        if (ipTV_lib::$settings['save_restart_logs'] != 0) {
            $rData = array('server_id' => $serverID, 'stream_id' => $streamID, 'action' => $rAction, 'source' => $rSource, 'time' => time());
            file_put_contents(LOGS_TMP_PATH . 'stream_log.log', base64_encode(json_encode($rData)) . "\n", FILE_APPEND);
        } else {
            return null;
        }
    }
    public static function getPlaylistSegments($playlist, $prebuffer = 0, $segmentDuration = 10) {
        if (file_exists($playlist)) {
            $source = file_get_contents($playlist);
            if (preg_match_all('/(.*?).ts/', $source, $rMatches)) {
                if (0 < $prebuffer) {
                    $totalSegments = intval($prebuffer / $segmentDuration);
                    if (!$totalSegments) {
                        $totalSegments = 1;
                    }
                    return array_slice($rMatches[0], 0 - $totalSegments);
                }
                if ($prebuffer == -1) {
                    return $rMatches[0];
                }
                preg_match('/_(.*)\\./', array_pop($rMatches[0]), $currentSegment);
                return $currentSegment[1];
            }
        }
    }
    public static function generateAdminHLS($rM3U8, $password, $streamID, $rUIToken) {
        if (file_exists($rM3U8)) {
            $rSource = file_get_contents($rM3U8);
            if (preg_match_all('/(.*?)\\.ts/', $rSource, $rMatches)) {
                foreach ($rMatches[0] as $rMatch) {
                    if ($rUIToken) {
                        $rSource = str_replace($rMatch, '/admin/live.php?extension=m3u8&segment=' . $rMatch . '&uitoken=' . $rUIToken, $rSource);
                    } else {
                        $rSource = str_replace($rMatch, '/admin/live.php?password=' . $password . '&extension=m3u8&segment=' . $rMatch . '&stream=' . $streamID, $rSource);
                    }
                }
                return $rSource;
            }
        }
        return false;
    }
    public static function generateHLS($rM3U8, $username, $password, $streamID, $rUUID, $IP, $rVideoCodec = 'h264', $rOnDemand = 0, $serverID = null) {
        if (file_exists($rM3U8)) {
            $rSource = file_get_contents($rM3U8);
            if (ipTV_lib::$settings['encrypt_hls']) {
                $rKeyToken = encryptData($IP . '/' . $streamID, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                $rSource = "#EXTM3U\n#EXT-X-KEY:METHOD=AES-128,URI=\"" . '/key/' . $rKeyToken . '",IV=0x' . bin2hex(file_get_contents(STREAMS_PATH . $streamID . '_.iv')) . "\n" . substr($rSource, 8, strlen($rSource) - 8);
            }
            if (preg_match_all('/(.*?)\\.ts/', $rSource, $rMatches)) {
                foreach ($rMatches[0] as $rMatch) {
                    $rToken = encryptData($username . '/' . $password . '/' . $IP . '/' . $streamID . '/' . $rMatch . '/' . $rUUID . '/' . $serverID . '/' . $rVideoCodec . '/' . $rOnDemand, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                    if (ipTV_lib::$settings['allow_cdn_access']) {
                        $rSource = str_replace($rMatch, '/hls/' . $rMatch . '?token=' . $rToken, $rSource);
                    } else {
                        $rSource = str_replace($rMatch, '/hls/' . $rToken, $rSource);
                    }
                }
                return $rSource;
            }
        }
        return false;
    }
    public static function checkBlockedUAs($userAgent) {
        $userAgent = strtolower($userAgent);
        foreach (ipTV_lib::$blockedUA as $rKey => $blocked) {
            if ($blocked['exact_match'] == 1) {
                if ($blocked['blocked_ua'] == $userAgent) {
                    return true;
                }
            } else {
                if (stristr($userAgent, $blocked['blocked_ua'])) {
                    return true;
                }
            }
        }
        return false;
    }
    public static function checkIsCracked($user_ip) {
        $user_ip_file = TMP_PATH . md5($user_ip . 'cracked');
        if (file_exists($user_ip_file)) {
            $contents = intval(file_get_contents($user_ip_file));
            return $contents == 1 ? true : false;
        }
        file_put_contents($user_ip_file, 0);
        return false;
    }
    public static function isArchiveRunning($PID, $streamID, $EXE = PHP_BIN) {
        if (!empty($PID)) {
            clearstatcache(true);
            if (!(file_exists('/proc/' . $PID) && is_readable('/proc/' . $PID . '/exe') && strpos(basename(readlink('/proc/' . $PID . '/exe')), basename($EXE)) === 0)) {
            } else {
                $command = trim(file_get_contents('/proc/' . $PID . '/cmdline'));
                if ($command != 'TVArchive[' . $streamID . ']') {
                } else {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
    /**
     * Checks if a monitor process is running with the specified PID and stream ID.
     *
     * @param int $PID The process ID of the monitor.
     * @param int $streamID The stream ID to check against.
     * @param string $ffmpeg_path The path to the FFmpeg executable (default is PHP_BIN).
     * @return bool Returns true if the monitor process is running with the specified PID and stream ID, false otherwise.
     */
    public static function checkMonitorRunning($PID, $streamID, $ffmpeg_path = PHP_BIN) {
        if (!empty($PID)) {
            clearstatcache(true);
            if (file_exists('/proc/' . $PID) && is_readable('/proc/' . $PID . '/exe') && basename(readlink('/proc/' . $PID . '/exe')) == basename($ffmpeg_path)) {
                $value = trim(file_get_contents("/proc/{$PID}/cmdline"));
                if ($value == "XC_VM[{$streamID}]") {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
    public static function isDelayRunning($PID, $streamID) {
        if (empty($PID)) {
            return false;
        }
        clearstatcache(true);
        if (file_exists('/proc/' . $PID) && is_readable('/proc/' . $PID . '/exe')) {
            $value = trim(file_get_contents("/proc/{$PID}/cmdline"));
            if ($value == "XC_VMDelay[{$streamID}]") {
                return true;
            }
        }
        return false;
    }
    public static function isStreamRunning($PID, $streamID) {
        if (!empty($PID)) {
            clearstatcache(true);
            if (file_exists('/proc/' . $PID) && is_readable('/proc/' . $PID . '/exe')) {
                if (strpos(basename(readlink('/proc/' . $PID . '/exe')), 'ffmpeg') === 0) {
                    $command = trim(file_get_contents('/proc/' . $PID . '/cmdline'));
                    if (stristr($command, '/' . $streamID . '_.m3u8') || stristr($command, '/' . $streamID . '_%d.ts')) {
                        return true;
                    }
                } else {
                    if (strpos(basename(readlink('/proc/' . $PID . '/exe')), 'php') !== 0) {
                    } else {
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }
    public static function isProcessRunning($PID, $EXE = null) {
        if (!empty($PID)) {
            clearstatcache(true);
            if (!(file_exists('/proc/' . $PID) && is_readable('/proc/' . $PID . '/exe') && strpos(basename(readlink('/proc/' . $PID . '/exe')), basename($EXE)) === 0) && $EXE) {
                return false;
            }
            return true;
        }
        return false;
    }
    public static function showVideo($is_restreamer = 0, $video_id_setting, $video_path_id, $extension = 'ts') {
        global $showErrors;
        if ($is_restreamer == 0 && ipTV_lib::$settings[$video_id_setting] == 1) {
            if ($extension == 'm3u8') {
                $extm3u = '#EXTM3U
				#EXT-X-VERSION:3
				#EXT-X-MEDIA-SEQUENCE:0
				#EXT-X-ALLOW-CACHE:YES
				#EXT-X-TARGETDURATION:11
				#EXTINF:10.0,
				' . ipTV_lib::$settings[$video_path_id] . '
				#EXT-X-ENDLIST';
                header('Content-Type: application/x-mpegurl');
                header('Content-Length: ' . strlen($extm3u));
                echo $extm3u;
                die;
            } else {
                header('Content-Type: video/mp2t');
                readfile(ipTV_lib::$settings[$video_path_id]);
                die;
            }
        }
        if ($showErrors) {
            print_r($video_id_setting);
        } else {
            http_response_code(403);
        }
        die;
    }
    public static function showVideoServer($video_id_setting, $video_path_id, $extension, $userInfo, $IP, $rCountryCode, $rISP, $serverID = null) {
        $video_path_id = self::B97D7AcBCF7C7A5e($video_path_id);
        if (!(!$userInfo['is_restreamer'] && ipTV_lib::$settings[$video_id_setting] && 0 < strlen($video_path_id))) {
            switch ($video_id_setting) {
                case 'show_expired_video':
                    generateError('EXPIRED');
                    break;
                case 'show_banned_video':
                    generateError('BANNED');
                    break;
                case 'show_not_on_air_video':
                    generateError('STREAM_OFFLINE');
                    break;
                default:
                    generate404();
                    break;
            }
        } else {
            if (!$serverID) {
                $serverID = self::F4221e28760b623E($userInfo, $IP, $rCountryCode, $rISP);
            }
            if (!$serverID) {
                $serverID = SERVER_ID;
            }
            if (ipTV_lib::$Servers[$serverID]['random_ip'] && 0 < count(ipTV_lib::$Servers[$serverID]['domains']['urls'])) {
                $rURL = ipTV_lib::$Servers[$serverID]['domains']['protocol'] . '://' . ipTV_lib::$Servers[$serverID]['domains']['urls'][array_rand(ipTV_lib::$Servers[$serverID]['domains']['urls'])] . ':' . ipTV_lib::$Servers[$serverID]['domains']['port'];
            } else {
                $rURL = rtrim(ipTV_lib::$Servers[$serverID]['site_url'], '/');
            }
            $rTokenData = array('expires' => time() + 10, 'video_path' => $video_path_id);
            $rToken = encryptData(json_encode($rTokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
            if ($extension == 'm3u8') {
                $rM3U8 = "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-MEDIA-SEQUENCE:0\n#EXT-X-ALLOW-CACHE:YES\n#EXT-X-TARGETDURATION:10\n#EXTINF:10.0,\n" . $rURL . '/auth/' . $rToken . "\n#EXT-X-ENDLIST";
                header('Content-Type: application/x-mpegurl');
                header('Content-Length: ' . strlen($rM3U8));
                echo $rM3U8;
                exit();
            }
            header('Location: ' . $rURL . '/auth/' . $rToken);
            exit();
        }
    }
    public static function F4221e28760B623E($userInfo, $rUserIP, $rCountryCode, $rUserISP = '') {
        $rAvailableServers = array();
        foreach (ipTV_lib::$Servers as $serverID => $serverInfo) {
            if ($serverInfo['server_online'] && $serverInfo['server_type'] == 0) {
                $rAvailableServers[] = $serverID;
            }
        }
        if (!empty($rAvailableServers)) {
            shuffle($rAvailableServers);
            $rServerCapacity = self::getCapacity();
            $rAcceptServers = array();
            foreach ($rAvailableServers as $serverID) {
                $rOnlineClients = (isset($rServerCapacity[$serverID]['online_clients']) ? $rServerCapacity[$serverID]['online_clients'] : 0);
                if ($rOnlineClients != 0) {
                } else {
                    $rServerCapacity[$serverID]['capacity'] = 0;
                }
                $rAcceptServers[$serverID] = (0 < ipTV_lib::$Servers[$serverID]['total_clients'] && $rOnlineClients < ipTV_lib::$Servers[$serverID]['total_clients'] ? $rServerCapacity[$serverID]['capacity'] : false);
            }
            $rAcceptServers = array_filter($rAcceptServers, 'is_numeric');
            if (empty($rAcceptServers)) {
                return false;
            }
            $rKeys = array_keys($rAcceptServers);
            $rValues = array_values($rAcceptServers);
            array_multisort($rValues, SORT_ASC, $rKeys, SORT_ASC);
            $rAcceptServers = array_combine($rKeys, $rValues);
            if ($userInfo['force_server_id'] != 0 && array_key_exists($userInfo['force_server_id'], $rAcceptServers)) {
                $rRedirectID = $userInfo['force_server_id'];
            } else {
                $rPriorityServers = array();
                foreach (array_keys($rAcceptServers) as $serverID) {
                    if (ipTV_lib::$Servers[$serverID]['enable_geoip'] == 1) {
                        if (in_array($rCountryCode, ipTV_lib::$Servers[$serverID]['geoip_countries'])) {
                            $rRedirectID = $serverID;
                            break;
                        }
                        if (ipTV_lib::$Servers[$serverID]['geoip_type'] == 'strict') {
                            unset($rAcceptServers[$serverID]);
                        } else {
                            $rPriorityServers[$serverID] = (ipTV_lib::$Servers[$serverID]['geoip_type'] == 'low_priority' ? 1 : 2);
                        }
                    } else {
                        if (ipTV_lib::$Servers[$serverID]['enable_isp'] == 1) {
                            if (in_array($rUserISP, ipTV_lib::$Servers[$serverID]['isp_names'])) {
                                $rRedirectID = $serverID;
                                break;
                            }
                            if (ipTV_lib::$Servers[$serverID]['isp_type'] == 'strict') {
                                unset($rAcceptServers[$serverID]);
                            } else {
                                $rPriorityServers[$serverID] = (ipTV_lib::$Servers[$serverID]['isp_type'] == 'low_priority' ? 1 : 2);
                            }
                        } else {
                            $rPriorityServers[$serverID] = 1;
                        }
                    }
                }
                if (!(empty($rPriorityServers) && empty($rRedirectID))) {
                    $rRedirectID = (empty($rRedirectID) ? array_search(min($rPriorityServers), $rPriorityServers) : $rRedirectID);
                } else {
                    return false;
                }
            }
            return $rRedirectID;
        } else {
            return false;
        }
    }
    public static function B97D7ACBCf7c7A5e($video_path_id) {
        if (!(isset(ipTV_lib::$settings[$video_path_id]) && 0 < strlen(ipTV_lib::$settings[$video_path_id]))) {
            switch ($video_path_id) {
                case 'connected_video_path':
                    if (!file_exists(VIDEO_PATH . 'connected.ts')) {
                        break;
                    }
                    return VIDEO_PATH . 'connected.ts';
                case 'expired_video_path':
                    if (!file_exists(VIDEO_PATH . 'expired.ts')) {
                        break;
                    }
                    return VIDEO_PATH . 'expired.ts';
                case 'banned_video_path':
                    if (!file_exists(VIDEO_PATH . 'banned.ts')) {
                        break;
                    }
                    return VIDEO_PATH . 'banned.ts';
                case 'not_on_air_video_path':
                    if (!file_exists(VIDEO_PATH . 'offline.ts')) {
                        break;
                    }
                    return VIDEO_PATH . 'offline.ts';
                case 'expiring_video_path':
                    if (!file_exists(VIDEO_PATH . 'expiring.ts')) {
                        break;
                    }
                    return VIDEO_PATH . 'expiring.ts';
            }
        } else {
            return ipTV_lib::$settings[$video_path_id];
        }
    }
    public static function isValidStream($playlist, $PID) {
        return self::isProcessRunning($PID, ipTV_lib::$FFMPEG_CPU) && file_exists($playlist);
    }
    public static function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    public static function getIPInfo($IP) {
        if (!empty($IP)) {
            if (!file_exists(CONS_TMP_PATH . md5($IP) . '_geo2')) {
                $rGeoIP = new MaxMind\Db\Reader(GEOIP2COUNTRY_FILENAME);
                $rResponse = $rGeoIP->get($IP);
                $rGeoIP->close();
                if ($rResponse) {
                    file_put_contents(CONS_TMP_PATH . md5($IP) . '_geo2', json_encode($rResponse));
                }
                return $rResponse;
            }
            return json_decode(file_get_contents(CONS_TMP_PATH . md5($IP) . '_geo2'), true);
        }
        return false;
    }
    public static function getStreamBitrate($type, $path, $force_duration = null) {
        clearstatcache();
        if (!file_exists($path)) {
            return false;
        }
        switch ($type) {
            case 'movie':
                if (!is_null($force_duration)) {
                    sscanf($force_duration, '%d:%d:%d', $hours, $minutes, $seconds);
                    $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
                    $bitrate = round(filesize($path) * 0.008 / $time_seconds);
                }
                break;
            case 'live':
                $fp = fopen($path, 'r');
                $bitrates = array();
                while (!feof($fp)) {
                    $line = trim(fgets($fp));
                    if (stristr($line, 'EXTINF')) {
                        list($trash, $seconds) = explode(':', $line);
                        $seconds = rtrim($seconds, ',');
                        if ($seconds <= 0) {
                            continue;
                        }
                        $segment_file = trim(fgets($fp));
                        if (!file_exists(dirname($path) . '/' . $segment_file)) {
                            fclose($fp);
                            return false;
                        }
                        $segment_size_in_kilobits = filesize(dirname($path) . '/' . $segment_file) * 0.008;
                        $bitrates[] = $segment_size_in_kilobits / $seconds;
                    }
                }
                fclose($fp);
                $bitrate = count($bitrates) > 0 ? round(array_sum($bitrates) / count($bitrates)) : 0;
                break;
        }
        return $bitrate > 0 ? $bitrate : false;
    }
    public static function getISP($rIP) {
        if (!empty($rIP)) {
            if (!file_exists(CONS_TMP_PATH . md5($rIP) . '_isp')) {
                $rGeoIP = new MaxMind\Db\Reader(GEOIP2ISP_FILENAME);
                $rResponse = $rGeoIP->get($rIP);
                $rGeoIP->close();
                if ($rResponse) {
                    file_put_contents(CONS_TMP_PATH . md5($rIP) . '_isp', json_encode($rResponse));
                }
                return $rResponse;
            }
            return json_decode(file_get_contents(CONS_TMP_PATH . md5($rIP) . '_isp'), true);
        }
        return false;
    }
    public static function getISP_reserv($user_ip) {
        if (!empty($user_ip)) {
            if (file_exists(CONS_TMP_PATH . md5($user_ip) . '_isp')) {
                return igbinary_unserialize(file_get_contents(CONS_TMP_PATH . md5($user_ip) . '_isp'));
            }
            if ((isset($user_ip)) && (filter_var($user_ip, FILTER_VALIDATE_IP))) {
                $rData = json_decode(file_get_contents("https://db-ip.com/demo/home.php?s=" . $user_ip), true);

                if (strlen($rData["demoInfo"]["isp"]) > 0) {
                    $json = array(
                        "isp" => $rData["demoInfo"]["asName"],
                        "autonomous_system_number" => $rData["demoInfo"]["asNumber"],

                        "isp_info" => array(
                            "as_number" => $rData["demoInfo"]["asNumber"],
                            "description" => $rData["demoInfo"]["isp"],
                            "type" => $rData["demoInfo"]["usageType"],
                            "ip" => $rData["demoInfo"]["ipAddress"],
                            "country_code" => $rData["demoInfo"]["countryCode"],
                            "country_name" => $rData["demoInfo"]["countryName"],
                            "is_server" => $rData["demoInfo"]["usageType"] != "consumer" ? true : false,
                            // note: if api is not returning correct usagetype, try another isp api source.
                        )
                    );
                    file_put_contents(CONS_TMP_PATH . md5($user_ip) . '_isp', igbinary_serialize($json));
                }
            }
            return $json;
        }
        return false;
    }
    public static function getConnections($serverID = null, $userID = null, $streamID = null) {
        if (ipTV_lib::$settings['redis_handler'] || !is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }
        if (ipTV_lib::$settings['redis_handler']) {
            if ($serverID) {
                $rKeys = ipTV_lib::$redis->zRangeByScore('SERVER#' . $serverID, '-inf', '+inf');
            } else {
                if ($userID) {
                    $rKeys = ipTV_lib::$redis->zRangeByScore('LINE#' . $userID, '-inf', '+inf');
                } else {
                    if ($streamID) {
                        $rKeys = ipTV_lib::$redis->zRangeByScore('STREAM#' . $streamID, '-inf', '+inf');
                    } else {
                        $rKeys = ipTV_lib::$redis->zRangeByScore('LIVE', '-inf', '+inf');
                    }
                }
            }
            if (count($rKeys) > 0) {
                return array($rKeys, array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rKeys)));
            }
        } else {
            $rWhere = array();
            if (!empty($serverID)) {
                $rWhere[] = 't1.server_id = ' . intval($serverID);
            }
            if (!empty($userID)) {
                $rWhere[] = 't1.user_id = ' . intval($userID);
            }
            $rExtra = '';
            if (count($rWhere) > 0) {
                $rExtra = 'WHERE ' . implode(' AND ', $rWhere);
            }
            $rQuery = 'SELECT t2.*,t3.*,t5.bitrate,t1.*,t1.uuid AS `uuid` FROM `lines_live` t1 LEFT JOIN `lines` t2 ON t2.id = t1.user_id LEFT JOIN `streams` t3 ON t3.id = t1.stream_id LEFT JOIN `streams_servers` t5 ON t5.stream_id = t1.stream_id AND t5.server_id = t1.server_id ' . $rExtra . ' ORDER BY t1.activity_id ASC';
            self::$ipTV_db->query($rQuery);
            return self::$ipTV_db->get_rows(true, 'user_id', false);
        }
    }
    public static function getConnection($rUUID) {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }
        return igbinary_unserialize(ipTV_lib::$redis->get($rUUID));
    }
    public static function createConnection($rData) {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }
        $rRedis = ipTV_lib::$redis->multi();
        $rRedis->zAdd('LINE#' . $rData['identity'], $rData['date_start'], $rData['uuid']);
        $rRedis->zAdd('LINE_ALL#' . $rData['identity'], $rData['date_start'], $rData['uuid']);
        $rRedis->zAdd('STREAM#' . $rData['stream_id'], $rData['date_start'], $rData['uuid']);
        $rRedis->zAdd('SERVER#' . $rData['server_id'], $rData['date_start'], $rData['uuid']);
        if ($rData['user_id']) {
            $rRedis->zAdd('SERVER_LINES#' . $rData['server_id'], $rData['user_id'], $rData['uuid']);
        }
        $rRedis->zAdd('CONNECTIONS', $rData['date_start'], $rData['uuid']);
        $rRedis->zAdd('LIVE', $rData['date_start'], $rData['uuid']);
        $rRedis->set($rData['uuid'], igbinary_serialize($rData));
        return $rRedis->exec();
    }
    public static function updateConnection($rData, $rChanges = array(), $rOption = null) {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }
        $rOrigData = $rData;
        foreach ($rChanges as $rKey => $rValue) {
            $rData[$rKey] = $rValue;
        }
        $rRedis = ipTV_lib::$redis->multi();
        if ($rOption == 'open') {
            $rRedis->sRem('ENDED', $rData['uuid']);
            $rRedis->zAdd('LIVE', $rData['date_start'], $rData['uuid']);
            $rRedis->zAdd('LINE#' . $rData['identity'], $rData['date_start'], $rData['uuid']);
            $rRedis->zAdd('STREAM#' . $rData['stream_id'], $rData['date_start'], $rData['uuid']);
            $rRedis->zAdd('SERVER#' . $rData['server_id'], $rData['date_start'], $rData['uuid']);
            if ($rData['hls_end'] == 1) {
                $rData['hls_end'] = 0;
                if ($rData['user_id']) {
                    $rRedis->zAdd('SERVER_LINES#' . $rData['server_id'], $rData['user_id'], $rData['uuid']);
                }
            }
        } else {
            if ($rOption == 'close') {
                $rRedis->sAdd('ENDED', $rData['uuid']);
                $rRedis->zRem('LIVE', $rData['uuid']);
                $rRedis->zRem('LINE#' . $rOrigData['identity'], $rData['uuid']);
                $rRedis->zRem('STREAM#' . $rOrigData['stream_id'], $rData['uuid']);
                $rRedis->zRem('SERVER#' . $rOrigData['server_id'], $rData['uuid']);
                if ($rData['hls_end'] == 0) {
                    $rData['hls_end'] = 1;
                    if ($rData['user_id']) {
                        $rRedis->zRem('SERVER_LINES#' . $rOrigData['server_id'], $rData['uuid']);
                    }
                }
            }
        }
        $rRedis->set($rData['uuid'], igbinary_serialize($rData));
        if ($rRedis->exec()) {
            return $rData;
        }
    }
    public static function getStreamConnections($streamIDs, $rGroup = true, $rCount = false) {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }
        $rRedis = ipTV_lib::$redis->multi();
        foreach ($streamIDs as $streamID) {
            $rRedis->zRevRangeByScore('STREAM#' . $streamID, '+inf', '-inf');
        }
        $rGroups = $rRedis->exec();
        $rConnectionMap = $rRedisKeys = array();
        foreach ($rGroups as $rGroupID => $rKeys) {
            if ($rCount) {
                $rConnectionMap[$streamIDs[$rGroupID]] = count($rKeys);
            } else {
                if (count($rKeys) > 0) {
                    $rRedisKeys = array_merge($rRedisKeys, $rKeys);
                }
            }
        }
        if (!$rCount) {
            foreach (ipTV_lib::$redis->mGet(array_unique($rRedisKeys)) as $rRow) {
                $rRow = igbinary_unserialize($rRow);
                if ($rGroup) {
                    $rConnectionMap[$rRow['stream_id']][] = $rRow;
                } else {
                    $rConnectionMap[$rRow['stream_id']][$rRow['server_id']][] = $rRow;
                }
            }
        }
        return $rConnectionMap;
    }
    public static function updateStream($streamID) {
        self::$ipTV_db->query('SELECT COUNT(*) AS `count` FROM `signals` WHERE `server_id` = ? AND `cache` = 1 AND `custom_data` = ?;', self::getMainID(), json_encode(array('type' => 'update_stream', 'id' => $streamID)));
        if (self::$ipTV_db->get_row()['count'] == 0) {
            self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `cache`, `time`, `custom_data`) VALUES(?, 1, ?, ?);', self::getMainID(), time(), json_encode(array('type' => 'update_stream', 'id' => $streamID)));
        }
        return true;
    }
    public static function getStreamingURL($serverID = null, $rForceHTTP = false) {
        if (!isset($serverID)) {
            $serverID = SERVER_ID;
        }
        if ($rForceHTTP) {
            $rProtocol = 'http';
        } else {
            $rProtocol = ipTV_lib::$Servers[$serverID]['server_protocol'];
        }
        $rDomain = null;
        if (0 < strlen(HOST) && in_array(strtolower(HOST), array_map('strtolower', ipTV_lib::$Servers[$serverID]['domains']['urls']))) {
            $rDomain = HOST;
        } else {
            if (ipTV_lib::$Servers[$serverID]['random_ip'] && 0 < count(ipTV_lib::$Servers[$serverID]['domains']['urls'])) {
                $rDomain = ipTV_lib::$Servers[$serverID]['domains']['urls'][array_rand(ipTV_lib::$Servers[$serverID]['domains']['urls'])];
            }
        }
        if ($rDomain) {
            $rURL = $rProtocol . '://' . $rDomain . ':' . ipTV_lib::$Servers[$serverID][$rProtocol . '_broadcast_port'];
        } else {
            $rURL = rtrim(ipTV_lib::$Servers[$serverID][$rProtocol . '_url'], '/');
        }
        return $rURL;
    }
    public static function getMainID() {
        foreach (ipTV_lib::$Servers as $serverID => $rServer) {
            if ($rServer['is_main']) {
                return $serverID;
            }
        }
    }
    public static function getBouquetMap($streamID) {
        $rBouquetMap = igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'bouquet_map'));
        $rReturn = ($rBouquetMap[$streamID] ?: array());
        unset($rBouquetMap);
        return $rReturn;
    }
    public static function getEnded() {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }
        $rKeys = ipTV_lib::$redis->sMembers('ENDED');
        if (count($rKeys) > 0) {
            return array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rKeys));
        }
    }
    public static function redisSignal($PID, $serverID, $rRTMP, $rCustomData = null) {
        if (is_object(ipTV_lib::$redis)) {
        } else {
            ipTV_lib::connectRedis();
        }
        $rKey = 'SIGNAL#' . md5($serverID . '#' . $PID . '#' . $rRTMP);
        $rData = array('pid' => $PID, 'server_id' => $serverID, 'rtmp' => $rRTMP, 'time' => time(), 'custom_data' => $rCustomData, 'key' => $rKey);
        return ipTV_lib::$redis->multi()->sAdd('SIGNALS#' . $serverID, $rKey)->set($rKey, igbinary_serialize($rData))->exec();
    }
    public static function matchCIDR($rASN, $rIP) {
        if (file_exists(CIDR_TMP_PATH . $rASN)) {
            $rCIDRs = json_decode(file_get_contents(CIDR_TMP_PATH . $rASN), true);
            foreach ($rCIDRs as $rCIDR => $rData) {
                if (ip2long($rData[1]) <= ip2long($rIP) && ip2long($rIP) <= ip2long($rData[2])) {
                    return $rData;
                }
            }
        }
    }
    public static function checkISP($rConISP) {
        foreach (ipTV_lib::$blockedISP as $rISP) {
            if (strtolower($rConISP) == strtolower($rISP['isp'])) {
                return intval($rISP['blocked']);
            }
        }
        return 0;
    }
    public static function checkServer($rASN) {
        // return in_array($rASN, ipTV_lib::$blockedServers);
        return false;
    }
}
