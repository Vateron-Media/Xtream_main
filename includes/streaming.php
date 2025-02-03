<?php
class ipTV_streaming {
    
    public static $ipTV_db = null;

    /**
     * Fetches RTMP IPs from the database and resolves hostnames.
     *
     * @return array List of resolved RTMP IPs including localhost.
     */
    public static function rtmpIps() {
        if (!self::$ipTV_db) {
            return ["127.0.0.1"];
        }

        self::$ipTV_db->query("SELECT `ip` FROM `rtmp_ips`");
        $rtmpIps = self::$ipTV_db->get_rows();

        return array_merge(["127.0.0.1"], array_map("gethostbyname", ipTV_lib::arrayValuesRecursive($rtmpIps)));
    }

    /**
     * Applies an FFMPEG overlay signal to a given video segment.
     *
     * @param array  $signalData  Data containing message, font size, color, and position.
     * @param string $segmentFile Name of the segment file to process.
     * @param string $codec       Video codec to use (default: 'h264').
     * @param bool   $return      Whether to return processed video content instead of passthru.
     *
     * @return mixed Video data (if $return is true) or boolean success.
     */
    public static function sendSignalFFMPEG($signalData, $segmentFile, $codec = 'h264', $return = false) {
        // Determine overlay position (random if not provided)
        if (empty($signalData["xy_offset"])) {
            $x = rand(150, 380);
            $y = rand(110, 250);
        } else {
            list($x, $y) = explode("x", $signalData["xy_offset"]);
        }

        // Escape parameters to prevent shell injection
        $text = escapeshellcmd($signalData['message']);
        $fontSize = escapeshellcmd($signalData['font_size']);
        $fontColor = escapeshellcmd($signalData['font_color']);
        $segmentPath = escapeshellarg(STREAMS_PATH . $segmentFile);
        $fontFile = escapeshellarg(FFMPEG_FONTS_PATH);

        // Construct FFMPEG command
        $ffmpegCmd = sprintf(
            '%s -copyts -vsync 0 -nostats -nostdin -hide_banner -loglevel quiet -y -i %s -filter_complex "drawtext=fontfile=%s:text=\'%s\':fontsize=%s:x=%d:y=%d:fontcolor=%s" -map 0 -vcodec %s -preset ultrafast -acodec copy -scodec copy -mpegts_flags +initial_discontinuity -mpegts_copyts 1 -f mpegts ',
            ipTV_lib::$FFMPEG_CPU, 
            $segmentPath, 
            $fontFile, 
            $text, 
            $fontSize, 
            intval($x), 
            intval($y), 
            $fontColor, 
            $codec
        );

        if ($return) {
            // Process and return video data
            $outputPath = SIGNALS_PATH . $signalData['activity_id'] . '_' . $segmentFile;
            shell_exec($ffmpegCmd . escapeshellarg($outputPath));
            $data = file_get_contents($outputPath);
            ipTV_lib::unlinkFile($outputPath);
            return $data;
        }

        // Directly stream the modified segment
        passthru($ffmpegCmd . '-');
        return true;
    }

    /**
     * Retrieves the list of allowed admin IPs.
     *
     * @param bool $rForce If true, forces retrieval from the database instead of cache.
     * @return array List of allowed IP addresses.
     */
    public static function getAllowedIPsAdmin($rForce = false) {
        // Check cache unless forced to refresh
        if (!$rForce) {
            $cachedIPs = ipTV_lib::getCache('allowed_ips', 60);
            if (!empty($cachedIPs)) {
                return $cachedIPs;
            }
        }

        $allowedIPs = ['127.0.0.1', $_SERVER['SERVER_ADDR']];

        // Process IPs from server configurations
        foreach (ipTV_lib::$Servers as $serverInfo) {
            if (!empty($serverInfo['whitelist_ips'])) {
                $whitelistIPs = json_decode($serverInfo['whitelist_ips'], true);
                if (is_array($whitelistIPs)) {
                    $allowedIPs = array_merge($allowedIPs, $whitelistIPs);
                }
            }

            $allowedIPs[] = $serverInfo['server_ip'];

            if (!empty($serverInfo['private_ip'])) {
                $allowedIPs[] = $serverInfo['private_ip'];
            }

            // Add valid domain-based IPs
            foreach (explode(',', $serverInfo['domain_name']) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $allowedIPs[] = $ip;
                }
            }
        }

        // Merge with admin-allowed IPs from settings
        if (!empty(ipTV_lib::$settings['allowed_ips_admin'])) {
            $allowedIPs = array_merge($allowedIPs, explode(',', ipTV_lib::$settings['allowed_ips_admin']));
        }

        // Cache and return the unique list of allowed IPs
        $allowedIPs = array_unique($allowedIPs);
        ipTV_lib::setCache('allowed_ips', $allowedIPs);

        return $allowedIPs;
    }

    /**
     * Marks an activity for closure and transfers processing.
     *
     * @param int|string $activity_id The activity ID to close.
     * @return void
     */
    public static function closeAndTransfer($activity_id) {
        file_put_contents(CONS_TMP_PATH . $activity_id, '1');
    }

    /**
     * Retrieves stream data for a given stream ID.
     *
     * @param int $streamID The ID of the stream to retrieve.
     * @return array|false Stream data or false if not found.
     */
    public static function getStreamData($streamID) {
        $cacheFile = TMP_PATH . $streamID . "_cacheStream";

        // Check cache before querying the database
        if (CACHE_STREAMS && file_exists($cacheFile) && (time() - filemtime($cacheFile)) <= CACHE_STREAMS_TIME) {
            return igbinary_unserialize(file_get_contents($cacheFile));
        }

        // Fetch stream information
        self::$ipTV_db->query(
            'SELECT * FROM `streams` t1 
             LEFT JOIN `streams_types` t2 ON t2.type_id = t1.type 
             WHERE t1.`id` = ?',
            $streamID
        );

        if (self::$ipTV_db->num_rows() === 0) {
            return false; // Stream not found
        }

        $rStreamInfo = self::$ipTV_db->get_row();
        $rServers = [];

        // Fetch server data if the stream is not a direct source
        if ($rStreamInfo['direct_source'] == 0) {
            self::$ipTV_db->query('SELECT * FROM `streams_servers` WHERE `stream_id` = ?', $streamID);
            $rServers = (self::$ipTV_db->num_rows() > 0) ? self::$ipTV_db->get_rows(true, 'server_id') : [];
        }

        // Construct output data
        $rOutput = [
            'bouquets' => self::getBouquetMap($streamID),
            'info' => $rStreamInfo,
            'servers' => $rServers
        ];

        // Cache stream data if caching is enabled
        if (CACHE_STREAMS) {
            file_put_contents($cacheFile, igbinary_serialize($rOutput), LOCK_EX);
        }

        return $rOutput;
    }

    /**
     * Retrieves channel information and determines the best server for streaming.
     *
     * @param int $streamID Stream ID.
     * @param string $extension Stream format extension.
     * @param array $userInfo User information.
     * @param string $rCountryCode Country code for geolocation filtering.
     * @param string $rUserISP User's ISP name.
     * @param string $rType Type of stream ('archive', 'movie', or default live stream).
     * @return array|int|false Stream information with the best server, a redirect ID, or false on failure.
     */
    public static function channelInfo($streamID, $extension, $userInfo, $rCountryCode, $rUserISP = '', $rType = '') {
        // Retrieve stream data from cache or database
        if (ipTV_lib::$cached) {
            $rStream = igbinary_unserialize(file_get_contents(STREAMS_TMP_PATH . "stream_{$streamID}")) ?: null;
            $rStream['bouquets'] = self::getBouquetMap($streamID);
        } else {
            $rStream = self::getStreamData($streamID);
        }

        if (!$rStream) {
            return false; // Stream not found
        }

        // Assign bouquet data
        $rStream['info']['bouquets'] = $rStream['bouquets'];
        $rAvailableServers = [];

        // Check if stream is an archive
        if ($rType === 'archive' && $rStream['info']['tv_archive_duration'] > 0 && 
            $rStream['info']['tv_archive_server_id'] > 0 && 
            array_key_exists($rStream['info']['tv_archive_server_id'], ipTV_lib::$Servers)) {
            return $rStream['info']['tv_archive_server_id'];
        }

        // If not a direct source, check available servers
        if ($rStream['info']['direct_source'] != 1) {
            foreach (ipTV_lib::$Servers as $serverID => $serverInfo) {
                if (!isset($rStream['servers'][$serverID]) || !$serverInfo['server_online'] || $serverInfo['server_type'] != 0) {
                    continue;
                }

                $serverData = $rStream['servers'][$serverID];

                if ($rType === 'movie') {
                    if (!empty($serverData['pid']) && 
                        $serverData['to_analyze'] == 0 && 
                        $serverData['stream_status'] == 0 && 
                        ($rStream['info']['target_container'] == $extension || $extension === 'srt') && 
                        $serverInfo['timeshift_only'] == 0) {
                        $rAvailableServers[] = $serverID;
                    }
                } else {
                    if (($serverData['on_demand'] == 1 && $serverData['stream_status'] != 1 || 
                        $serverData['pid'] > 0 && $serverData['stream_status'] == 0) && 
                        $serverData['to_analyze'] == 0 && 
                        (int) $serverData['delay_available_at'] <= time() && 
                        $serverInfo['timeshift_only'] == 0) {
                        $rAvailableServers[] = $serverID;
                    }
                }
            }
        } else {
            // Redirect to direct stream source
            header('Location: ' . str_replace(' ', '%20', json_decode($rStream['info']['stream_source'], true)[0]));
            exit();
        }

        if (empty($rAvailableServers)) {
            return false; // No available servers found
        }

        shuffle($rAvailableServers);
        $rServerCapacity = self::getCapacity();
        $rAcceptServers = [];

        // Filter servers based on capacity and availability
        foreach ($rAvailableServers as $serverID) {
            $rOnlineClients = $rServerCapacity[$serverID]['online_clients'] ?? 0;
            if ($rOnlineClients === 0) {
                $rServerCapacity[$serverID]['capacity'] = 0;
            }
            $rAcceptServers[$serverID] = (ipTV_lib::$Servers[$serverID]['total_clients'] > 0 &&
                $rOnlineClients < ipTV_lib::$Servers[$serverID]['total_clients']) ? 
                $rServerCapacity[$serverID]['capacity'] : false;
        }

        // Filter servers that have numeric capacity
        $rAcceptServers = array_filter($rAcceptServers, 'is_numeric');

        if (empty($rAcceptServers)) {
            return ($rType === 'archive') ? null : [];
        }

        // Sort available servers by lowest capacity first
        asort($rAcceptServers);

        $rRedirectID = null;

        // Prioritize RTMP server if available
        if ($extension === 'rtmp' && array_key_exists(SERVER_ID, $rAcceptServers)) {
            $rRedirectID = SERVER_ID;
        } elseif (isset($userInfo['force_server_id']) && $userInfo['force_server_id'] != 0 && 
                  array_key_exists($userInfo['force_server_id'], $rAcceptServers)) {
            $rRedirectID = $userInfo['force_server_id'];
        } else {
            // Apply GeoIP and ISP filtering
            $rPriorityServers = [];
            foreach (array_keys($rAcceptServers) as $serverID) {
                if (ipTV_lib::$Servers[$serverID]['enable_geoip']) {
                    if (in_array($rCountryCode, ipTV_lib::$Servers[$serverID]['geoip_countries'])) {
                        $rRedirectID = $serverID;
                        break;
                    }
                    if (ipTV_lib::$Servers[$serverID]['geoip_type'] === 'strict') {
                        unset($rAcceptServers[$serverID]);
                    } else {
                        $rPriorityServers[$serverID] = ipTV_lib::$Servers[$serverID]['geoip_type'] === 'low_priority' ? 3 : 2;
                    }
                } elseif (ipTV_lib::$Servers[$serverID]['enable_isp']) {
                    if (in_array(strtolower(trim(preg_replace('/[^A-Za-z0-9 ]/', '', $rUserISP))), ipTV_lib::$Servers[$serverID]['isp_names'])) {
                        $rRedirectID = $serverID;
                        break;
                    }
                    if (ipTV_lib::$Servers[$serverID]['isp_type'] === 'strict') {
                        unset($rAcceptServers[$serverID]);
                    } else {
                        $rPriorityServers[$serverID] = ipTV_lib::$Servers[$serverID]['isp_type'] === 'low_priority' ? 3 : 2;
                    }
                } else {
                    $rPriorityServers[$serverID] = 1;
                }
            }

            if (empty($rRedirectID)) {
                $rRedirectID = array_search(min($rPriorityServers), $rPriorityServers) ?: false;
            }
        }

        if ($rType === 'archive') {
            return $rRedirectID;
        }

        // Assign redirect ID to stream info and return
        $rStream['info']['redirect_id'] = $rRedirectID;
        return array_merge($rStream['info'], $rStream['servers'][$rRedirectID] ?? []);
    }

    /**
     * Retrieves server capacity based on different distribution methods.
     *
     * @return array Associative array of server capacities and online client counts.
     */
    public static function getCapacity() {
        // Query database to get the count of online clients per server
        self::$ipTV_db->query(
            'SELECT `server_id`, COUNT(*) AS `online_clients`
             FROM `lines_live`
             WHERE `server_id` <> 0 AND `hls_end` = 0
             GROUP BY `server_id`;'
        );

        $serverStats = self::$ipTV_db->get_rows(true, 'server_id');
        $splitMethod = ipTV_lib::$settings['split_by'];

        // Fetch network speed configurations
        if ($splitMethod === 'band' || $splitMethod === 'guar_band') {
            $serverSpeeds = [];
            foreach (ipTV_lib::$Servers as $serverID => $serverInfo) {
                $hardwareInfo = json_decode($serverInfo['server_hardware'], true);
                $serverSpeeds[$serverID] = !empty($hardwareInfo['network_speed'])
                    ? (float) $hardwareInfo['network_speed']
                    : ($serverInfo['network_guaranteed_speed'] > 0
                        ? $serverInfo['network_guaranteed_speed']
                        : 1000); // Default to 1000 Mbps if not defined
            }
        }

        // Calculate capacity based on the configured split method
        foreach ($serverStats as $serverID => $stats) {
            $currentOutput = isset(ipTV_lib::$Servers[$serverID]['watchdog']['bytes_sent'])
                ? intval(ipTV_lib::$Servers[$serverID]['watchdog']['bytes_sent'] / 125000) // Convert bytes to Mbps
                : 0;

            switch ($splitMethod) {
                case 'band': // Split by network speed
                    $serverStats[$serverID]['capacity'] = $serverSpeeds[$serverID] > 0
                        ? (float) ($currentOutput / $serverSpeeds[$serverID])
                        : 1;
                    break;

                case 'maxclients': // Split by max clients allowed per server
                    $maxClients = ipTV_lib::$Servers[$serverID]['total_clients'] ?: 1;
                    $serverStats[$serverID]['capacity'] = (float) ($stats['online_clients'] / $maxClients);
                    break;

                case 'guar_band': // Split by guaranteed bandwidth
                    $guaranteedSpeed = ipTV_lib::$Servers[$serverID]['network_guaranteed_speed'] ?: 1;
                    $serverStats[$serverID]['capacity'] = (float) ($currentOutput / $guaranteedSpeed);
                    break;

                default: // Default case: use number of online clients as capacity
                    $serverStats[$serverID]['capacity'] = $stats['online_clients'];
                    break;
            }
        }

        // Cache the computed server capacities
        file_put_contents(CACHE_TMP_PATH . "servers_capacity", json_encode($serverStats), LOCK_EX);

        return $serverStats;
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
    
    /**
     * Checks if a category exists within the provided bouquets.
     *
     * @param int $category_id The category ID to check.
     * @param array|string $bouquets The list of bouquet IDs (JSON string or array).
     * @return bool True if category exists in any bouquet, otherwise false.
     */
    public static function categoriesBouq($category_id, $bouquets) {
        $cacheFile = TMP_PATH . 'categories_bouq';

        // Return true if cache file does not exist
        if (!file_exists($cacheFile)) {
            return true;
        }

        // Ensure bouquets are properly formatted as an array
        if (!is_array($bouquets)) {
            $bouquets = json_decode($bouquets, true) ?: [];
        }

        // Read and deserialize cache data
        $categoryMap = igbinary_unserialize(file_get_contents($cacheFile));

        // Check if the category exists in any provided bouquet
        foreach ($bouquets as $bouquet) {
            if (!empty($categoryMap[$bouquet]) && in_array($category_id, $categoryMap[$bouquet])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves all adult categories.
     *
     * @return array List of adult category IDs.
     */
    public static function getAdultCategories() {
        return array_map(
            fn($category) => intval($category['id']),
            array_filter(ipTV_lib::$categories, fn($category) => !empty($category['is_adult']))
        );
    }

    /**
     * Retrieves information about a MAG device and its associated user.
     *
     * @param int|null $mag_id The MAG device ID (optional).
     * @param string|null $mac The MAC address (optional, required if $mag_id is null).
     * @param bool $get_ChannelIDS Whether to retrieve channel IDs for the user.
     * @param bool $getBouquetInfo Whether to retrieve bouquet information for the user.
     * @param bool $get_cons Whether to retrieve consumption data for the user.
     * @return array|false Returns MAG device information or false if not found.
     */
    public static function getMagInfo($mag_id = null, $mac = null, $get_ChannelIDS = false, $getBouquetInfo = false, $get_cons = false) {
        if (empty($mag_id) && empty($mac)) {
            return false; // Both parameters cannot be empty
        }

        // Query database for MAG device info using either MAG ID or MAC address
        $query = 'SELECT * FROM `mag_devices` WHERE ' . (empty($mag_id) ? '`mac` = ?' : '`mag_id` = ?');
        $param = empty($mag_id) ? base64_encode($mac) : $mag_id;

        self::$ipTV_db->query($query, $param);

        if (self::$ipTV_db->num_rows() === 0) {
            return false; // MAG device not found
        }

        // Retrieve MAG device information
        $magInfo = ['mag_device' => self::$ipTV_db->get_row()];
        $magInfo['mag_device']['mac'] = base64_decode($magInfo['mag_device']['mac']);

        // Retrieve user info
        $magInfo['user_info'] = self::getUserInfo(
            $magInfo['mag_device']['user_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons
        ) ?: [];

        // Retrieve paired line info if applicable
        $magInfo['pair_line_info'] = [];
        if (!empty($magInfo['user_info']['pair_id'])) {
            $magInfo['pair_line_info'] = self::getUserInfo(
                $magInfo['user_info']['pair_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons
            ) ?: [];
        }

        return $magInfo;
    }

    /**
     * Retrieves information about an Enigma2 device and its associated user.
     *
     * @param array $maginfo The device information containing MAC or device ID.
     * @param bool $get_ChannelIDS Whether to retrieve channel IDs for the user.
     * @param bool $getBouquetInfo Whether to retrieve bouquet information for the user.
     * @param bool $get_cons Whether to retrieve consumption data for the user.
     * @return array|false Returns Enigma2 device information or false if not found.
     */
    public static function enigmaDevices($maginfo, $get_ChannelIDS = false, $getBouquetInfo = false, $get_cons = false) {
        if (empty($maginfo['device_id']) && empty($maginfo['mac'])) {
            return false; // Both device_id and MAC cannot be empty
        }

        // Query database for Enigma2 device info using either device ID or MAC address
        $query = 'SELECT * FROM `enigma2_devices` WHERE ' . (empty($maginfo['device_id']) ? '`mac` = ?' : '`device_id` = ?');
        $param = empty($maginfo['device_id']) ? $maginfo['mac'] : $maginfo['device_id'];

        self::$ipTV_db->query($query, $param);

        if (self::$ipTV_db->num_rows() === 0) {
            return false; // Device not found
        }

        // Retrieve Enigma2 device information
        $enigma2Devices = ['enigma2' => self::$ipTV_db->get_row()];

        // Retrieve user info
        $enigma2Devices['user_info'] = self::getUserInfo(
            $enigma2Devices['enigma2']['user_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons
        ) ?: [];

        // Retrieve paired line info if applicable
        $enigma2Devices['pair_line_info'] = [];
        if (!empty($enigma2Devices['user_info']['pair_id'])) {
            $enigma2Devices['pair_line_info'] = self::getUserInfo(
                $enigma2Devices['user_info']['pair_id'], null, null, $get_ChannelIDS, $getBouquetInfo, $get_cons
            ) ?: [];
        }

        return $enigma2Devices;
    }

    /**
     * Validates and enforces the maximum connection limit for a user.
     *
     * @param array $userInfo User information containing ID and max connections.
     * @param string|null $IP (Optional) User's IP address.
     * @param string|null $userAgent (Optional) User's User-Agent string.
     * @return void
     */
    public static function validateConnections($userInfo, $IP = null, $userAgent = null) {
        // Ensure max connections is set and greater than zero
        if (!isset($userInfo['max_connections']) || $userInfo['max_connections'] <= 0) {
            return;
        }

        // Check for paired connection and enforce limits
        if (!empty($userInfo['pair_id'])) {
            self::closeConnections($userInfo['pair_id'], $userInfo['max_connections'], $IP, $userAgent);
        }

        // Enforce connection limits for the user
        self::closeConnections($userInfo['id'], $userInfo['max_connections'], $IP, $userAgent);
    }
    
    /**
     * Retrieves active connections from Redis based on given filters.
     *
     * @param int|null  $rUserID     User ID (filters by user if provided).
     * @param int|null  $rServerID   Server ID (filters by server if provided).
     * @param int|null  $rStreamID   Stream ID (filters by stream if provided).
     * @param bool      $rOpenOnly   Consider only active connections (currently not used in code).
     * @param bool      $rCountOnly  If true, returns only the count of total connections and unique users.
     * @param bool      $rGroup      If true, groups connections by unique users.
     * @param bool      $rHLSOnly    If true, filters only HLS connections.
     *
     * @return array
     * - If `$rCountOnly = true`: Returns `[total_connections, unique_users]`.
     * - If `$rGroup = true`: Returns an array where keys are unique users and values are arrays of their connections.
     * - Otherwise: Returns a list of all connections without grouping.
     */
    public static function getRedisConnections($rUserID = null, $rServerID = null, $rStreamID = null, $rOpenOnly = false, $rCountOnly = false, $rGroup = true, $rHLSOnly = false) {
        // Initialize return variable
        $rReturn = $rCountOnly ? [0, 0] : [];

        // Ensure Redis is connected
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }

        $rUniqueUsers = [];
        $rUserID = $rUserID > 0 ? intval($rUserID) : null;
        $rServerID = $rServerID > 0 ? intval($rServerID) : null;
        $rStreamID = $rStreamID > 0 ? intval($rStreamID) : null;

        // Determine which Redis key to query based on provided filters
        if ($rUserID) {
            $redisKey = "LINE#{$rUserID}";
        } elseif ($rStreamID) {
            $redisKey = "STREAM#{$rStreamID}";
        } elseif ($rServerID) {
            $redisKey = "SERVER#{$rServerID}";
        } else {
            $redisKey = "LIVE";
        }

        // Fetch connection keys from Redis
        $rKeys = ipTV_lib::$redis->zRangeByScore($redisKey, '-inf', '+inf');

        if (!empty($rKeys)) {
            // Retrieve and process connection data
            foreach (ipTV_lib::$redis->mGet(array_unique($rKeys)) as $rRow) {
                $rRow = igbinary_unserialize($rRow);
                if (!$rRow) continue; // Skip invalid/unserializable data

                // Apply filtering conditions
                if (($rServerID && $rServerID != $rRow['server_id']) ||
                    ($rStreamID && $rStreamID != $rRow['stream_id']) ||
                    ($rUserID && $rUserID != $rRow['user_id']) ||
                    ($rHLSOnly && $rRow['container'] != 'hls')) {
                    continue;
                }

                // Determine unique user identifier
                $rUUID = !empty($rRow['user_id']) ? $rRow['user_id'] : $rRow['hmac_id'] . '_' . $rRow['hmac_identifier'];

                if ($rCountOnly) {
                    $rReturn[0]++; // Increment total connections
                    $rUniqueUsers[] = $rUUID;
                } elseif ($rGroup) {
                    $rReturn[$rUUID][] = $rRow;
                } else {
                    $rReturn[] = $rRow;
                }
            }
        }

        // Count unique users if required
        if ($rCountOnly) {
            $rReturn[1] = count(array_unique($rUniqueUsers));
        }

        return $rReturn;
    }

    /**
     * Closes active connections for a user if they exceed the allowed maximum.
     *
     * @param int      $userID          The user ID whose connections need to be managed.
     * @param int      $rMaxConnections The maximum allowed connections.
     * @param string|null $IP           Optional. The IP address to filter connections.
     * @param string|null $userAgent    Optional. The user agent to filter connections.
     *
     * @return int|null Returns the number of terminated connections or null if no connections needed to be closed.
     */
    public static function closeConnections($userID, $rMaxConnections, $IP = null, $userAgent = null) {
        if (!$rMaxConnections) {
            return null;
        }

        $rConnections = [];
        $rToKill = 0;

        // Determine whether to fetch connections from Redis or MySQL
        if (ipTV_lib::$settings['redis_handler']) {
            $rKeys = self::getConnections($userID, true, true);
            $rToKill = count($rKeys) - $rMaxConnections;
            if ($rToKill > 0) {
                foreach (array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rKeys)) as $rConnection) {
                    if (is_array($rConnection)) {
                        $rConnections[] = $rConnection;
                    }
                }
                // Sort connections by start time (oldest first)
                array_multisort(array_column($rConnections, 'date_start'), SORT_ASC, $rConnections);
            } else {
                return null;
            }
        } else {
            // Fetch connections from MySQL
            self::$ipTV_db->query(
                'SELECT `lines_live`.*, `on_demand` 
                 FROM `lines_live` 
                 LEFT JOIN `streams_servers` ON `streams_servers`.`stream_id` = `lines_live`.`stream_id` 
                 AND `streams_servers`.`server_id` = `lines_live`.`server_id` 
                 WHERE `lines_live`.`user_id` = ? 
                 AND `lines_live`.`hls_end` = 0 
                 ORDER BY `lines_live`.`activity_id` ASC', 
                $userID
            );

            $rToKill = self::$ipTV_db->num_rows() - $rMaxConnections;
            if ($rToKill > 0) {
                $rConnections = self::$ipTV_db->get_rows();
            } else {
                return null;
            }
        }

        $rKilled = 0;
        $IDs = [];
        $rDelUUID = [];
        $rDelSID = [];

        // Define priority for termination (based on IP/User-Agent matching)
        $rKillTypes = $IP && $userAgent ? [2, 1, 0] : ($IP ? [1, 0] : [0]);

        // Loop through connections and close excess ones
        foreach ($rKillTypes as $rKillOwnIP) {
            foreach ($rConnections as $i => $rConnection) {
                if ($rKilled >= $rToKill) {
                    break;
                }

                if ($rConnection['pid'] !== getmypid() &&
                    (($rKillOwnIP === 2 && $rConnection['user_ip'] === $IP && $rConnection['user_agent'] === $userAgent) ||
                     ($rKillOwnIP === 1 && $rConnection['user_ip'] === $IP) ||
                     $rKillOwnIP === 0)) {

                    if (self::closeConnection($rConnection)) {
                        $rKilled++;
                        
                        if ($rConnection['container'] !== 'hls') {
                            if (ipTV_lib::$settings['redis_handler']) {
                                $IDs[] = $rConnection;
                            } else {
                                $IDs[] = intval($rConnection['activity_id']);
                            }
                            $rDelUUID[] = $rConnection['uuid'];
                            $rDelSID[$rConnection['stream_id']][] = $rDelUUID;
                        }

                        // Remove from queue if on-demand streaming is enabled
                        if ($rConnection['on_demand'] && $rConnection['server_id'] == SERVER_ID && ipTV_lib::$settings['on_demand_instant_off']) {
                            self::removeFromQueue($rConnection['stream_id'], $rConnection['pid']);
                        }
                    }
                }
            }
        }

        // Remove terminated connections from Redis or MySQL
        if (!empty($IDs)) {
            if (ipTV_lib::$settings['redis_handler']) {
                $rUUIDs = [];
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
                self::$ipTV_db->query(
                    'DELETE FROM `lines_live` WHERE `activity_id` IN (' . implode(',', array_map('intval', $IDs)) . ')'
                );
            }

            // Delete temp files for removed connections
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

    /**
     * Adds a process (PID) to the queue for a given stream.
     *
     * @param int $streamID The stream ID.
     * @param int $rAddPID The process ID to be added to the queue.
     *
     * @return void
     */
    public static function addToQueue($streamID, $rAddPID) {
        $queueFile = SIGNALS_TMP_PATH . 'queue_' . intval($streamID);
        $rActivePIDs = [];

        // Load existing PIDs from the queue file if it exists
        if (file_exists($queueFile)) {
            $PIDs = igbinary_unserialize(file_get_contents($queueFile)) ?: [];
        } else {
            $PIDs = [];
        }

        // Filter out only active PIDs
        foreach ($PIDs as $PID) {
            if (self::isProcessRunning($PID, 'php-fpm')) {
                $rActivePIDs[] = $PID;
            }
        }

        // Add new PID if it's not already in the active list
        if (!in_array($rAddPID, $rActivePIDs, true)) {
            $rActivePIDs[] = $rAddPID;
        }

        // Save the updated queue back to the file
        file_put_contents($queueFile, igbinary_serialize($rActivePIDs), LOCK_EX);
    }
    
    /**
     * Removes a process ID (PID) from the stream queue and manages queue cleanup.
     *
     * @param int $streamID The ID of the stream to manage.
     * @param int $PID The process ID to remove from the queue.
     *
     * @return void
     */
    public static function removeFromQueue($streamID, $PID) {
        $queueFile = SIGNALS_TMP_PATH . 'queue_' . intval($streamID);

        // Ensure the queue file exists before attempting to read it
        if (!file_exists($queueFile)) {
            return;
        }

        // Load the queue and filter out inactive or matching PIDs
        $PIDs = igbinary_unserialize(file_get_contents($queueFile)) ?: [];
        $rActivePIDs = array_filter($PIDs, fn($rActivePID) => 
            self::isProcessRunning($rActivePID, 'php-fpm') && $rActivePID !== $PID
        );

        // Update or delete the queue file based on remaining active PIDs
        if (!empty($rActivePIDs)) {
            file_put_contents($queueFile, igbinary_serialize($rActivePIDs), LOCK_EX);
        } else {
            unlink($queueFile);
        }
    }

    /**
     * Closes an active streaming connection and performs cleanup operations.
     *
     * @param mixed   $rActivityInfo Either an array containing activity information or a string containing UUID/activity_id.
     * @param boolean $rRemove       Whether to remove the connection data from storage (default: true).
     * @param boolean $rEnd          Whether to mark the connection as ended for HLS streams (default: true).
     *
     * @return boolean Returns true if the connection was successfully closed, false otherwise.
     */
    public static function closeConnection($rActivityInfo, $rRemove = true, $rEnd = true) {
        if (empty($rActivityInfo)) {
            return false;
        }

        // Ensure Redis is connected if using Redis storage
        if (ipTV_lib::$settings['redis_handler'] && !is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }

        // Fetch activity information if only UUID/activity_id is provided
        if (!is_array($rActivityInfo)) {
            if (!ipTV_lib::$settings['redis_handler']) {
                self::$ipTV_db->query(
                    'SELECT * FROM `lines_live` WHERE ' . 
                    (strlen(strval($rActivityInfo)) == 32 ? '`uuid` = ?' : '`activity_id` = ?'),
                    $rActivityInfo
                );
                $rActivityInfo = self::$ipTV_db->get_row();
            } else {
                $rActivityInfo = igbinary_unserialize(ipTV_lib::$redis->get($rActivityInfo));
            }
        }

        // Validate retrieved connection data
        if (empty($rActivityInfo)) {
            return false;
        }

        // Handle different stream container types
        switch ($rActivityInfo['container']) {
            case 'rtmp':
                self::terminateRTMPConnection($rActivityInfo);
                break;

            case 'hls':
                if (!$rRemove && $rEnd && $rActivityInfo['hls_end'] == 0) {
                    self::endHLSStream($rActivityInfo);
                }
                break;

            default:
                self::terminateGenericConnection($rActivityInfo);
                break;
        }

        // Remove temporary connection files
        if ($rActivityInfo['server_id'] == SERVER_ID) {
            ipTV_lib::unlinkFile(CONS_TMP_PATH . $rActivityInfo['uuid']);
        }

        // Remove the connection from storage if required
        if ($rRemove) {
            self::removeConnectionFromStorage($rActivityInfo);
        }

        // Log offline activity
        self::writeOfflineActivity(
            $rActivityInfo['server_id'],
            $rActivityInfo['user_id'],
            $rActivityInfo['stream_id'],
            $rActivityInfo['date_start'],
            $rActivityInfo['user_agent'],
            $rActivityInfo['user_ip'],
            $rActivityInfo['container'],
            $rActivityInfo['geoip_country_code'],
            $rActivityInfo['isp'],
            $rActivityInfo['external_device'],
            $rActivityInfo['divergence']
        );

        return true;
    }

    /**
     * Terminates an RTMP streaming connection.
     */
    private static function terminateRTMPConnection($rActivityInfo) {
        if ($rActivityInfo['server_id'] == SERVER_ID) {
            shell_exec('wget --timeout=2 -O /dev/null -o /dev/null "' . 
                ipTV_lib::$Servers[SERVER_ID]['rtmp_mport_url'] . 
                'control/drop/client?clientid=' . intval($rActivityInfo['pid']) . 
                '" >/dev/null 2>/dev/null &');
        } else {
            self::sendTerminationSignal($rActivityInfo, 1);
        }
    }

    /**
     * Marks an HLS stream as ended.
     */
    private static function endHLSStream($rActivityInfo) {
        if (ipTV_lib::$settings['redis_handler']) {
            self::updateConnection($rActivityInfo, [], 'close');
        } else {
            self::$ipTV_db->query('UPDATE `lines_live` SET `hls_end` = 1 WHERE `activity_id` = ?', $rActivityInfo['activity_id']);
        }
        ipTV_lib::unlinkFile(CONS_TMP_PATH . $rActivityInfo['stream_id'] . '/' . $rActivityInfo['uuid']);
    }

    /**
     * Terminates a non-HLS streaming connection.
     */
    private static function terminateGenericConnection($rActivityInfo) {
        if ($rActivityInfo['server_id'] == SERVER_ID) {
            if ($rActivityInfo['pid'] != getmypid() && is_numeric($rActivityInfo['pid']) && $rActivityInfo['pid'] > 0) {
                posix_kill(intval($rActivityInfo['pid']), 9);
            }
        } else {
            self::sendTerminationSignal($rActivityInfo, 0);
        }
    }

    /**
     * Sends a termination signal for a process running on a different server.
     */
    private static function sendTerminationSignal($rActivityInfo, $rtmpFlag) {
        if (ipTV_lib::$settings['redis_handler']) {
            self::redisSignal($rActivityInfo['pid'], $rActivityInfo['server_id'], $rtmpFlag);
        } else {
            self::$ipTV_db->query(
                'INSERT INTO `signals` (`pid`,`server_id`,`rtmp`,`time`) VALUES(?,?,?,UNIX_TIMESTAMP())', 
                $rActivityInfo['pid'], 
                $rActivityInfo['server_id'], 
                $rtmpFlag
            );
        }
    }

    /**
     * Removes a connection from storage (Redis or MySQL).
     */
    private static function removeConnectionFromStorage($rActivityInfo) {
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

    /**
     * Closes the last connection(s) for a user if they exceed the maximum allowed connections.
     *
     * @param int $user_id        The ID of the user whose connections need to be managed.
     * @param int $max_connections The maximum number of allowed connections.
     *
     * @return int Number of closed connections.
     */
    public static function closeLastCon($user_id, $max_connections) {
        // Fetch active connections sorted by oldest first
        self::$ipTV_db->query(
            'SELECT * FROM `lines_live` WHERE `user_id` = ? ORDER BY `activity_id` ASC', 
            $user_id
        );
        $rows = self::$ipTV_db->get_rows();

        // Calculate number of excess connections
        $excessConnections = count($rows) - $max_connections + 1;
        if ($excessConnections <= 0) {
            return 0;
        }

        $closedCount = 0;
        $connectionsToRemove = [];

        foreach ($rows as $index => $connection) {
            // Skip already ended HLS connections
            if ($connection['hls_end'] == 1) {
                continue;
            }

            // Attempt to remove the connection
            if (self::removeConnection($connection, false)) {
                $closedCount++;

                // Store non-HLS connections for batch deletion
                if ($connection['container'] != 'hls') {
                    $connectionsToRemove[] = (int) $connection['activity_id'];
                }
            }

            // Stop once we have closed the required number of connections
            if ($closedCount >= $excessConnections) {
                break;
            }
        }

        // Batch delete non-HLS connections from the database
        if (!empty($connectionsToRemove)) {
            self::$ipTV_db->query(
                'DELETE FROM `lines_live` WHERE `activity_id` IN (' . implode(',', $connectionsToRemove) . ')'
            );
        }

        return $closedCount;
    }

    /**
     * Removes a streaming connection and performs necessary cleanup.
     *
     * @param mixed   $activity_id               The activity ID or an array containing connection details.
     * @param boolean $ActionUserActivityNow     Whether to delete the connection immediately (default: true).
     *
     * @return boolean Returns true if the connection was successfully removed, false otherwise.
     */
    public static function removeConnection($activity_id, $ActionUserActivityNow = true) {
        // Validate input
        if (empty($activity_id)) {
            return false;
        }

        // Fetch connection details if only an activity ID is provided
        if (!is_array($activity_id)) {
            self::$ipTV_db->query('SELECT * FROM `lines_live` WHERE `activity_id` = ?', $activity_id);
            $activity_id = self::$ipTV_db->get_row();
        }

        // Ensure valid connection data exists
        if (empty($activity_id)) {
            return false;
        }

        // Handle different container types
        switch ($activity_id['container']) {
            case 'hls':
                if (!$ActionUserActivityNow) {
                    self::$ipTV_db->query(
                        'UPDATE `lines_live` SET `hls_end` = 1 WHERE `activity_id` = ?', 
                        $activity_id['activity_id']
                    );
                }
                break;

            case 'rtmp':
                self::terminateRTMPConnection($activity_id);
                break;

            default:
                self::terminateGenericConnection($activity_id);
                break;
        }

        // Remove the connection from storage if required
        if ($ActionUserActivityNow) {
            self::$ipTV_db->query('DELETE FROM `lines_live` WHERE `activity_id` = ?', $activity_id['activity_id']);
        }

        // Log offline activity
        self::writeOfflineActivity(
            $activity_id['server_id'],
            $activity_id['user_id'],
            $activity_id['stream_id'],
            $activity_id['date_start'],
            $activity_id['user_agent'],
            $activity_id['user_ip'],
            $activity_id['container'],
            $activity_id['geoip_country_code'],
            $activity_id['isp'],
            $activity_id['external_device']
        );

        return true;
    }
    
    /**
     * Handles the termination of an RTMP streaming session when playback is done.
     *
     * @param int $PID The process ID (PID) of the stream.
     * @return bool Returns true if the connection was removed successfully, false otherwise.
     */
    public static function playDone($PID) {
        // Validate input
        if (empty($PID) || !is_numeric($PID)) {
            return false;
        }

        // Fetch active RTMP connection details
        self::$ipTV_db->query(
            'SELECT * FROM `lines_live` WHERE `container` = ? AND `pid` = ? AND `server_id` = ?',
            'rtmp',
            $PID,
            SERVER_ID
        );

        // Check if an active connection exists
        if (self::$ipTV_db->num_rows() > 0) {
            $activity = self::$ipTV_db->get_row();

            // Delete the active RTMP connection from database
            self::$ipTV_db->query(
                'DELETE FROM `lines_live` WHERE `activity_id` = ?',
                $activity['activity_id']
            );

            // Log offline activity for record-keeping
            self::writeOfflineActivity(
                $activity['server_id'],
                $activity['user_id'],
                $activity['stream_id'],
                $activity['date_start'],
                $activity['user_agent'],
                $activity['user_ip'],
                $activity['container'],
                $activity['geoip_country_code'],
                $activity['isp'],
                $activity['external_device']
            );

            return true;
        }

        return false;
    }

    /**
     * Logs offline user activity when a streaming session ends.
     *
     * @param int     $serverID        Server ID of the connection.
     * @param int     $userID          User ID of the connection.
     * @param int     $streamID        Stream ID of the connection.
     * @param int     $start           Timestamp when the stream started.
     * @param string  $userAgent       User-Agent string of the client.
     * @param string  $IP              IP address of the user.
     * @param string  $extension       Stream container type (e.g., hls, rtmp).
     * @param string  $GeoIP           User's country code based on GeoIP.
     * @param string  $rISP            Internet Service Provider (ISP) of the user.
     * @param string  $rExternalDevice (Optional) External device identifier.
     * @param int     $rDivergence     (Optional) Stream divergence (e.g., buffering metric).
     *
     * @return void
     */
    public static function writeOfflineActivity(
        $serverID,
        $userID,
        $streamID,
        $start,
        $userAgent,
        $IP,
        $extension,
        $GeoIP,
        $rISP,
        $rExternalDevice = '',
        $rDivergence = 0
    ) {
        // Ensure closed connection logging is enabled
        if (empty(ipTV_lib::$settings['save_closed_connection'])) {
            return;
        }

        // Validate mandatory fields
        if (empty($serverID) || empty($userID) || empty($streamID)) {
            return;
        }

        // Prepare the activity data
        $rActivityInfo = [
            'user_id'            => (int) $userID,
            'stream_id'          => (int) $streamID,
            'server_id'          => (int) $serverID,
            'date_start'         => (int) $start,
            'user_agent'         => trim($userAgent),
            'user_ip'            => filter_var($IP, FILTER_SANITIZE_STRING),
            'date_end'           => time(),
            'container'          => trim($extension),
            'geoip_country_code' => strtoupper(trim($GeoIP)),
            'isp'               => trim($rISP),
            'external_device'    => filter_var($rExternalDevice, FILTER_SANITIZE_STRING),
            'divergence'         => (int) $rDivergence,
        ];

        // Log activity data securely
        file_put_contents(
            LOGS_TMP_PATH . 'activity',
            base64_encode(json_encode($rActivityInfo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Logs client actions if logging is enabled or if bypass is set to true.
     *
     * @param int    $streamID  Stream ID.
     * @param int    $userID    User ID performing the action.
     * @param string $action    The action performed.
     * @param string $IP        User IP address.
     * @param string $data      Additional data to log (optional).
     * @param bool   $bypass    If true, bypasses the logging setting (optional).
     *
     * @return void
     */
    public static function clientLog($streamID, $userID, $action, $IP, $data = '', $bypass = false) {
        if (!empty(ipTV_lib::$settings['client_logs_save']) || $bypass) {
            $logData = [
                'user_id'      => (int) $userID,
                'stream_id'    => (int) $streamID,
                'action'       => trim($action),
                'query_string' => filter_var($_SERVER['QUERY_STRING'] ?? '', FILTER_SANITIZE_STRING),
                'user_agent'   => filter_var($_SERVER['HTTP_USER_AGENT'] ?? '', FILTER_SANITIZE_STRING),
                'user_ip'      => filter_var($IP, FILTER_VALIDATE_IP) ?: 'Unknown',
                'time'         => time(),
                'extra_data'   => trim($data),
            ];

            file_put_contents(
                LOGS_TMP_PATH . 'client_request.log',
                base64_encode(json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . "\n",
                FILE_APPEND | LOCK_EX
            );
        }
    }

    /**
     * Logs stream actions if restart logging is enabled.
     *
     * @param int    $streamID Stream ID.
     * @param int    $serverID Server ID where the stream is running.
     * @param string $action   Action performed on the stream.
     * @param string $source   Additional source information (optional).
     *
     * @return void
     */
    public static function streamLog($streamID, $serverID, $action, $source = '') {
        if (!empty(ipTV_lib::$settings['save_restart_logs'])) {
            $logData = [
                'server_id' => (int) $serverID,
                'stream_id' => (int) $streamID,
                'action'    => trim($action),
                'source'    => trim($source),
                'time'      => time(),
            ];

            file_put_contents(
                LOGS_TMP_PATH . 'stream_log.log',
                base64_encode(json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . "\n",
                FILE_APPEND | LOCK_EX
            );
        }
    }

    /**
     * Retrieves segments from a playlist file for HLS streaming.
     *
     * @param string  $playlist         Path to the playlist file.
     * @param int     $prebuffer        Prebuffer duration (in seconds).
     * @param int     $segmentDuration  Expected duration of each segment (default: 10s).
     *
     * @return array|string|null
     * - If `$prebuffer > 0`: Returns last `$totalSegments` segments.
     * - If `$prebuffer == -1`: Returns all segments.
     * - Otherwise: Returns the current segment number.
     */
    public static function getPlaylistSegments($playlist, $prebuffer = 0, $segmentDuration = 10) {
        if (!file_exists($playlist)) {
            return null;
        }

        $source = file_get_contents($playlist);
        if (preg_match_all('/(.*?).ts/', $source, $matches)) {
            $segments = $matches[0];

            if ($prebuffer > 0) {
                $totalSegments = max(1, intval($prebuffer / $segmentDuration));
                return array_slice($segments, -$totalSegments);
            }

            if ($prebuffer == -1) {
                return $segments;
            }

            preg_match('/_(\d+)\./', end($segments), $currentSegment);
            return $currentSegment[1] ?? null;
        }

        return null;
    }

    /**
     * Generates an HLS playlist for Admin access with secure segment URLs.
     *
     * @param string $rM3U8    Path to the M3U8 file.
     * @param string $password Admin password for authentication.
     * @param int    $streamID Stream ID.
     * @param string $rUIToken UI Token for secure authentication (optional).
     *
     * @return string|false The modified M3U8 content with secure links or false if file doesn't exist.
     */
    public static function generateAdminHLS($rM3U8, $password, $streamID, $rUIToken) {
        if (!file_exists($rM3U8)) {
            return false;
        }

        $playlistContent = file_get_contents($rM3U8);
        
        if (preg_match_all('/(.*?)\.ts/', $playlistContent, $matches)) {
            foreach ($matches[0] as $segment) {
                $replacement = $rUIToken 
                    ? "/admin/live.php?extension=m3u8&segment={$segment}&uitoken={$rUIToken}"
                    : "/admin/live.php?password={$password}&extension=m3u8&segment={$segment}&stream={$streamID}";

                $playlistContent = str_replace($segment, $replacement, $playlistContent);
            }
        }

        return $playlistContent;
    }

    /**
     * Generates a secure HLS playlist with encryption and authentication tokens.
     *
     * @param string  $rM3U8       Path to the M3U8 file.
     * @param string  $username    User's username.
     * @param string  $password    User's password.
     * @param int     $streamID    Stream ID.
     * @param string  $rUUID       Unique connection identifier.
     * @param string  $IP          User's IP address.
     * @param string  $rVideoCodec Video codec used (default: 'h264').
     * @param int     $rOnDemand   Whether it's an on-demand stream (default: 0).
     * @param int|null $serverID   Server ID handling the stream (optional).
     *
     * @return string|false The modified M3U8 content with secure links or false if file doesn't exist.
     */
    public static function generateHLS($rM3U8, $username, $password, $streamID, $rUUID, $IP, $rVideoCodec = 'h264', $rOnDemand = 0, $serverID = null) {
        if (!file_exists($rM3U8)) {
            return false;
        }

        $playlistContent = file_get_contents($rM3U8);

        // Apply AES-128 encryption if enabled
        if (!empty(ipTV_lib::$settings['encrypt_hls'])) {
            $rKeyToken = encryptData("{$IP}/{$streamID}", ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
            $encryptionTag = "#EXTM3U\n#EXT-X-KEY:METHOD=AES-128,URI=\"/key/{$rKeyToken}\",IV=0x" . bin2hex(file_get_contents(STREAMS_PATH . "{$streamID}_.iv")) . "\n";
            
            // Ensure EXT-X-KEY tag is placed correctly
            $playlistContent = preg_replace('/^#EXTM3U/', $encryptionTag, $playlistContent);
        }

        // Secure each segment with a unique token
        if (preg_match_all('/(.*?)\.ts/', $playlistContent, $matches)) {
            foreach ($matches[0] as $segment) {
                $secureToken = encryptData(
                    "{$username}/{$password}/{$IP}/{$streamID}/{$segment}/{$rUUID}/{$serverID}/{$rVideoCodec}/{$rOnDemand}",
                    ipTV_lib::$settings['live_streaming_pass'],
                    OPENSSL_EXTRA
                );

                $replacement = ipTV_lib::$settings['allow_cdn_access'] 
                    ? "/hls/{$segment}?token={$secureToken}"
                    : "/hls/{$secureToken}";

                $playlistContent = str_replace($segment, $replacement, $playlistContent);
            }
        }

        return $playlistContent;
    }
    
    /**
     * Checks if a User-Agent is blocked based on exact or partial matches.
     *
     * @param string $userAgent The User-Agent string to check.
     *
     * @return bool Returns true if the User-Agent is blocked, otherwise false.
     */
    public static function checkBlockedUAs($userAgent) {
        $userAgent = strtolower($userAgent);

        foreach (ipTV_lib::$blockedUA as $blocked) {
            $blockedUA = strtolower($blocked['blocked_ua']);

            if (($blocked['exact_match'] && $userAgent === $blockedUA) || (!$blocked['exact_match'] && str_contains($userAgent, $blockedUA))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a user IP has been flagged as cracked.
     *
     * @param string $user_ip The user’s IP address.
     *
     * @return bool Returns true if the IP is flagged as cracked, otherwise false.
     */
    public static function checkIsCracked($user_ip) {
        $user_ip_file = TMP_PATH . md5("{$user_ip}_cracked");

        if (file_exists($user_ip_file)) {
            return intval(file_get_contents($user_ip_file)) === 1;
        }

        file_put_contents($user_ip_file, '0', LOCK_EX);
        return false;
    }

    /**
     * Checks if an archive process is running with the specified PID and stream ID.
     *
     * @param int    $PID      The process ID to check.
     * @param int    $streamID The stream ID associated with the archive.
     * @param string $EXE      The executable path to validate (default: PHP_BIN).
     *
     * @return bool Returns true if the archive process is running, otherwise false.
     */
    public static function isArchiveRunning($PID, $streamID, $EXE = PHP_BIN) {
        if (empty($PID) || !file_exists("/proc/{$PID}") || !is_readable("/proc/{$PID}/exe")) {
            return false;
        }

        // Ensure the process is using the expected executable
        if (basename(readlink("/proc/{$PID}/exe")) !== basename($EXE)) {
            return false;
        }

        // Validate the command line argument
        return trim(file_get_contents("/proc/{$PID}/cmdline")) === "TVArchive[{$streamID}]";
    }

    /**
     * Checks if a monitor process is running with the specified PID and stream ID.
     *
     * @param int    $PID         The process ID of the monitor.
     * @param int    $streamID    The stream ID to check against.
     * @param string $ffmpeg_path The path to the FFmpeg executable (default: PHP_BIN).
     *
     * @return bool Returns true if the monitor process is running with the specified PID and stream ID, false otherwise.
     */
    public static function checkMonitorRunning($PID, $streamID, $ffmpeg_path = PHP_BIN) {
        if (empty($PID) || !file_exists("/proc/{$PID}") || !is_readable("/proc/{$PID}/exe")) {
            return false;
        }

        // Ensure the process is using the expected FFmpeg binary
        if (basename(readlink("/proc/{$PID}/exe")) !== basename($ffmpeg_path)) {
            return false;
        }

        // Validate the process command line argument
        return trim(file_get_contents("/proc/{$PID}/cmdline")) === "XC_VM[{$streamID}]";
    }

    /**
     * Checks if a delay process is running with the specified PID and stream ID.
     *
     * @param int $PID The process ID of the delay stream.
     * @param int $streamID The stream ID associated with the delay.
     *
     * @return bool Returns true if the delay process is running, otherwise false.
     */
    public static function isDelayRunning($PID, $streamID) {
        if (empty($PID) || !file_exists("/proc/{$PID}") || !is_readable("/proc/{$PID}/exe")) {
            return false;
        }

        // Check if the process command line matches expected delay format
        return trim(file_get_contents("/proc/{$PID}/cmdline")) === "XC_VMDelay[{$streamID}]";
    }

    /**
     * Checks if a streaming process is running with the specified PID and stream ID.
     *
     * @param int $PID The process ID of the streaming instance.
     * @param int $streamID The stream ID associated with the process.
     *
     * @return bool Returns true if the stream is running, otherwise false.
     */
    public static function isStreamRunning($PID, $streamID) {
        if (empty($PID) || !file_exists("/proc/{$PID}") || !is_readable("/proc/{$PID}/exe")) {
            return false;
        }

        $exePath = readlink("/proc/{$PID}/exe");
        $command = trim(file_get_contents("/proc/{$PID}/cmdline"));

        // Check if the process is FFmpeg and running the expected stream files
        if (strpos(basename($exePath), 'ffmpeg') === 0) {
            return stripos($command, "/{$streamID}_.m3u8") !== false || stripos($command, "/{$streamID}_%d.ts") !== false;
        }

        // Check if the process is a PHP script
        return strpos(basename($exePath), 'php') === 0;
    }

    /**
     * Checks if a process is running based on its PID and optional executable name.
     *
     * @param int $PID The process ID.
     * @param string|null $EXE Optional. The name of the executable to match.
     *
     * @return bool Returns true if the process is running, otherwise false.
     */
    public static function isProcessRunning($PID, $EXE = null) {
        if (empty($PID) || !file_exists("/proc/{$PID}") || !is_readable("/proc/{$PID}/exe")) {
            return false;
        }

        // If an executable name is provided, verify it matches
        if ($EXE) {
            return strpos(basename(readlink("/proc/{$PID}/exe")), basename($EXE)) === 0;
        }

        return true;
    }

    /**
     * Displays a video file based on user settings and stream type.
     *
     * @param int $is_restreamer Determines if the user is a restreamer.
     * @param string $video_id_setting The setting that enables/disables video playback.
     * @param string $video_path_id The setting containing the video file path.
     * @param string $extension The video format (default: 'ts').
     *
     * @return void Outputs the video file or an error response.
     */
    public static function showVideo($is_restreamer = 0, $video_id_setting, $video_path_id, $extension = 'ts') {
        global $showErrors;

        if ($is_restreamer == 0 && !empty(ipTV_lib::$settings[$video_id_setting])) {
            $videoPath = ipTV_lib::$settings[$video_path_id];

            if ($extension === 'm3u8') {
                $extm3u = <<<M3U
    #EXTM3U
    #EXT-X-VERSION:3
    #EXT-X-MEDIA-SEQUENCE:0
    #EXT-X-ALLOW-CACHE:YES
    #EXT-X-TARGETDURATION:11
    #EXTINF:10.0,
    $videoPath
    #EXT-X-ENDLIST
    M3U;
                header('Content-Type: application/x-mpegurl');
                header('Content-Length: ' . strlen($extm3u));
                echo $extm3u;
            } else {
                header('Content-Type: video/mp2t');
                readfile($videoPath);
            }
            exit();
        }

        // Show errors if enabled, otherwise return a 403 response
        if (!empty($showErrors)) {
            print_r($video_id_setting);
        } else {
            http_response_code(403);
        }
        exit();
    }


    /**
     * Displays a video or redirects based on server settings and user status.
     *
     * @param string $video_id_setting The setting that determines the video type.
     * @param string $video_path_id The path ID of the video.
     * @param string $extension The video format (e.g., 'm3u8', 'ts').
     * @param array $userInfo User information array.
     * @param string $IP User IP address.
     * @param string $rCountryCode User country code.
     * @param string $rISP User ISP name.
     * @param int|null $serverID The ID of the server to use (optional).
     *
     * @return void
     */
    public static function showVideoServer($video_id_setting, $video_path_id, $extension, $userInfo, $IP, $rCountryCode, $rISP, $serverID = null) {
        $video_path_id = self::getVideoPath($video_path_id);

        // Check if the user is not a restreamer and video settings allow playback
        if ($userInfo['is_restreamer'] || !ipTV_lib::$settings[$video_id_setting] || empty($video_path_id)) {
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
            exit();
        }

        // Determine the server ID if not provided
        if (!$serverID) {
            $serverID = self::selectOptimalServer($userInfo, $IP, $rCountryCode, $rISP) ?: SERVER_ID;
        }

        // Generate the base URL for the server
        if (!empty(ipTV_lib::$Servers[$serverID]['random_ip']) && !empty(ipTV_lib::$Servers[$serverID]['domains']['urls'])) {
            $randomDomain = ipTV_lib::$Servers[$serverID]['domains']['urls'][array_rand(ipTV_lib::$Servers[$serverID]['domains']['urls'])];
            $rURL = ipTV_lib::$Servers[$serverID]['domains']['protocol'] . '://' . $randomDomain . ':' . ipTV_lib::$Servers[$serverID]['domains']['port'];
        } else {
            $rURL = rtrim(ipTV_lib::$Servers[$serverID]['site_url'], '/');
        }

        // Generate a secure token for authentication
        $rTokenData = [
            'expires' => time() + 10,
            'video_path' => $video_path_id
        ];
        $rToken = encryptData(json_encode($rTokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);

        // Handle HLS (m3u8) format
        if ($extension === 'm3u8') {
            $rM3U8 = <<<M3U
    #EXTM3U
    #EXT-X-VERSION:3
    #EXT-X-MEDIA-SEQUENCE:0
    #EXT-X-ALLOW-CACHE:YES
    #EXT-X-TARGETDURATION:10
    #EXTINF:10.0,
    $rURL/auth/$rToken
    #EXT-X-ENDLIST
    M3U;
            header('Content-Type: application/x-mpegurl');
            header('Content-Length: ' . strlen($rM3U8));
            echo $rM3U8;
            exit();
        }

        // Redirect for other formats
        header('Location: ' . $rURL . '/auth/' . $rToken);
        exit();
    }

    /**
     * Selects an optimal server for the user based on availability, capacity, geo-IP, and ISP rules.
     *
     * @param array  $userInfo      The user's information.
     * @param string $rUserIP       The user's IP address.
     * @param string $rCountryCode  The user's country code.
     * @param string $rUserISP      The user's ISP name (optional).
     *
     * @return int|false Returns the selected server ID or false if no suitable server is found.
     */
    public static function selectOptimalServer($userInfo, $rUserIP, $rCountryCode, $rUserISP = '') {
        $availableServers = [];

        // Collect online servers
        foreach (ipTV_lib::$Servers as $serverID => $serverInfo) {
            if ($serverInfo['server_online'] && $serverInfo['server_type'] == 0) {
                $availableServers[] = $serverID;
            }
        }

        if (empty($availableServers)) {
            return false;
        }

        shuffle($availableServers); // Randomize selection

        // Get server capacity info
        $serverCapacity = self::getCapacity();
        $acceptableServers = [];

        foreach ($availableServers as $serverID) {
            $onlineClients = $serverCapacity[$serverID]['online_clients'] ?? 0;
            $serverCapacity[$serverID]['capacity'] = ($onlineClients == 0) ? 0 : $serverCapacity[$serverID]['capacity'];

            $acceptableServers[$serverID] = (
                ipTV_lib::$Servers[$serverID]['total_clients'] > 0 &&
                $onlineClients < ipTV_lib::$Servers[$serverID]['total_clients']
            ) ? $serverCapacity[$serverID]['capacity'] : false;
        }

        $acceptableServers = array_filter($acceptableServers, 'is_numeric');

        if (empty($acceptableServers)) {
            return false;
        }

        // Sort servers by lowest capacity usage
        array_multisort(array_values($acceptableServers), SORT_ASC, array_keys($acceptableServers), SORT_ASC);
        $sortedServers = array_combine(array_keys($acceptableServers), array_values($acceptableServers));

        // Force user to specific server if set
        if ($userInfo['force_server_id'] != 0 && isset($sortedServers[$userInfo['force_server_id']])) {
            return $userInfo['force_server_id'];
        }

        // Prioritize servers based on GeoIP and ISP rules
        $priorityServers = [];
        foreach (array_keys($sortedServers) as $serverID) {
            $serverInfo = ipTV_lib::$Servers[$serverID];

            // GeoIP Matching
            if ($serverInfo['enable_geoip'] && in_array($rCountryCode, $serverInfo['geoip_countries'])) {
                return $serverID;
            }

            if ($serverInfo['geoip_type'] === 'strict') {
                unset($sortedServers[$serverID]);
            } else {
                $priorityServers[$serverID] = ($serverInfo['geoip_type'] === 'low_priority') ? 1 : 2;
            }

            // ISP Matching
            if ($serverInfo['enable_isp'] && in_array($rUserISP, $serverInfo['isp_names'])) {
                return $serverID;
            }

            if ($serverInfo['isp_type'] === 'strict') {
                unset($sortedServers[$serverID]);
            } else {
                $priorityServers[$serverID] = ($serverInfo['isp_type'] === 'low_priority') ? 1 : 2;
            }
        }

        // Pick the best server from priority list
        return !empty($priorityServers) ? array_search(min($priorityServers), $priorityServers) : false;
    }


    /**
     * Retrieves the correct video file path based on the provided video path identifier.
     *
     * @param string $videoPathID The identifier for the video path.
     *
     * @return string|null Returns the valid file path if found, otherwise null.
     */
    public static function getVideoPath($videoPathID) {
        // If the setting exists and is not empty, return its value
        if (!empty(ipTV_lib::$settings[$videoPathID])) {
            return ipTV_lib::$settings[$videoPathID];
        }

        // Define default video file mappings
        $videoFiles = [
            'connected_video_path'  => 'connected.ts',
            'expired_video_path'    => 'expired.ts',
            'banned_video_path'     => 'banned.ts',
            'not_on_air_video_path' => 'offline.ts',
            'expiring_video_path'   => 'expiring.ts',
        ];

        // Check if the identifier exists in the mapping
        if (isset($videoFiles[$videoPathID])) {
            $filePath = VIDEO_PATH . $videoFiles[$videoPathID];

            // Return the file path if it exists
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    /**
     * Checks if a given stream is valid by verifying if its process is running and the playlist file exists.
     *
     * @param string $playlist The path to the playlist file.
     * @param int|null $PID The process ID of the stream.
     *
     * @return bool Returns true if the process is running and the playlist file exists; otherwise, false.
     */
    public static function isValidStream($playlist, $PID) {
        if (empty($PID)) {
            return false;
        }
        return self::isProcessRunning($PID, ipTV_lib::$FFMPEG_CPU) && file_exists($playlist);
    }

    /**
     * Retrieves the real IP address of the user from various server headers.
     *
     * @return string|null Returns the detected IP address or null if not found.
     */
    public static function getUserIP() {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CF_CONNECTING_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]); // Handle multiple IPs (e.g., proxies)
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                    return $ip;
                }
            }
        }

        return null;
    }

    /**
     * Retrieves geolocation information for a given IP address.
     *
     * @param string $IP The IP address to look up.
     *
     * @return array|false Returns an array of IP information if successful; otherwise, false.
     */
    public static function getIPInfo($IP) {
        if (empty($IP)) {
            return false;
        }

        $cacheFile = CONS_TMP_PATH . md5($IP) . '_geo2';

        // Return cached data if it exists
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        try {
            $rGeoIP = new MaxMind\Db\Reader(GEOIP2COUNTRY_FILENAME);
            $rResponse = $rGeoIP->get($IP);
            $rGeoIP->close();

            if ($rResponse) {
                file_put_contents($cacheFile, json_encode($rResponse), LOCK_EX);
                return $rResponse;
            }
        } catch (Exception $e) {
            error_log("GeoIP lookup failed: " . $e->getMessage());
        }

        return false;
    }


    /**
     * Calculates the bitrate of a stream based on file size and duration.
     *
     * @param string $type Type of the stream ('movie' or 'live').
     * @param string $path Path to the stream file or playlist.
     * @param string|null $force_duration (Optional) Duration for movie calculations in HH:MM:SS format.
     *
     * @return int|false Returns the calculated bitrate in kbps or false if calculation fails.
     */
    public static function getStreamBitrate($type, $path, $force_duration = null) {
        clearstatcache();

        if (!file_exists($path)) {
            return false;
        }

        switch ($type) {
            case 'movie':
                return self::calculateMovieBitrate($path, $force_duration);
            
            case 'live':
                return self::calculateLiveBitrate($path);
            
            default:
                return false;
        }
    }

    /**
     * Calculates the bitrate for a movie based on file size and duration.
     *
     * @param string $path Path to the movie file.
     * @param string|null $force_duration Duration in HH:MM:SS format.
     *
     * @return int|false Returns the calculated bitrate in kbps or false on failure.
     */
    private static function calculateMovieBitrate($path, $force_duration) {
        if (is_null($force_duration)) {
            return false;
        }

        sscanf($force_duration, "%d:%d:%d", $hours, $minutes, $seconds);
        $totalSeconds = ($hours * 3600) + ($minutes * 60) + ($seconds ?: 0);

        if ($totalSeconds <= 0) {
            return false;
        }

        return round((filesize($path) * 0.008) / $totalSeconds);
    }

    /**
     * Calculates the average bitrate for a live stream based on segment files.
     *
     * @param string $playlist Path to the HLS playlist file.
     *
     * @return int|false Returns the calculated bitrate in kbps or false on failure.
     */
    private static function calculateLiveBitrate($playlist) {
        $bitrates = [];
        $fp = fopen($playlist, 'r');

        if (!$fp) {
            return false;
        }

        while (($line = fgets($fp)) !== false) {
            $line = trim($line);
            
            if (str_starts_with($line, "#EXTINF")) {
                list(, $duration) = explode(":", $line);
                $duration = floatval(rtrim($duration, ","));
                
                if ($duration <= 0) {
                    continue;
                }

                $segmentFile = trim(fgets($fp));
                $segmentPath = dirname($playlist) . "/" . $segmentFile;

                if (!file_exists($segmentPath)) {
                    fclose($fp);
                    return false;
                }

                $bitrates[] = (filesize($segmentPath) * 0.008) / $duration;
            }
        }

        fclose($fp);

        return !empty($bitrates) ? round(array_sum($bitrates) / count($bitrates)) : false;
    }

    /**
     * Retrieves ISP information using MaxMind's database.
     *
     * @param string $rIP The IP address to look up.
     * @return array|false Returns ISP information as an array or false on failure.
     */
    public static function getISP($rIP) {
        if (empty($rIP) || !filter_var($rIP, FILTER_VALIDATE_IP)) {
            return false;
        }

        $cacheFile = CONS_TMP_PATH . md5($rIP) . '_isp';

        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        try {
            $rGeoIP = new MaxMind\Db\Reader(GEOIP2ISP_FILENAME);
            $rResponse = $rGeoIP->get($rIP);
            $rGeoIP->close();

            if (!empty($rResponse)) {
                file_put_contents($cacheFile, json_encode($rResponse));
                return $rResponse;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Retrieves ISP information using an external API fallback.
     *
     * @param string $user_ip The IP address to check.
     * @return array|false Returns ISP information or false if not available.
     */
    public static function getISP_reserv($user_ip) {
        if (empty($user_ip) || !filter_var($user_ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        $cacheFile = CONS_TMP_PATH . md5($user_ip) . '_isp';

        // Use cached data if available
        if (file_exists($cacheFile)) {
            return igbinary_unserialize(file_get_contents($cacheFile));
        }

        // Fetch ISP information from external API
        try {
            $apiResponse = file_get_contents("https://db-ip.com/demo/home.php?s=" . urlencode($user_ip));
            $rData = json_decode($apiResponse, true);

            if (!empty($rData["demoInfo"]["isp"])) {
                $json = [
                    "isp" => $rData["demoInfo"]["asName"],
                    "autonomous_system_number" => $rData["demoInfo"]["asNumber"],
                    "isp_info" => [
                        "as_number" => $rData["demoInfo"]["asNumber"],
                        "description" => $rData["demoInfo"]["isp"],
                        "type" => $rData["demoInfo"]["usageType"],
                        "ip" => $rData["demoInfo"]["ipAddress"],
                        "country_code" => $rData["demoInfo"]["countryCode"],
                        "country_name" => $rData["demoInfo"]["countryName"],
                        "is_server" => $rData["demoInfo"]["usageType"] !== "consumer"
                    ]
                ];

                file_put_contents($cacheFile, igbinary_serialize($json));
                return $json;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Retrieves active connections based on server, user, or stream.
     *
     * @param int|null $serverID Filter by server ID (default: null).
     * @param int|null $userID   Filter by user ID (default: null).
     * @param int|null $streamID Filter by stream ID (default: null).
     * @return array Returns an array of active connections.
     */
    public static function getConnections($serverID = null, $userID = null, $streamID = null) {
        try {
            // Ensure Redis is connected
            if (ipTV_lib::$settings['redis_handler'] || !is_object(ipTV_lib::$redis)) {
                ipTV_lib::connectRedis();
            }

            // Use Redis if enabled
            if (ipTV_lib::$settings['redis_handler']) {
                $rKeys = [];

                if ($serverID) {
                    $rKeys = ipTV_lib::$redis->zRangeByScore('SERVER#' . $serverID, '-inf', '+inf');
                } elseif ($userID) {
                    $rKeys = ipTV_lib::$redis->zRangeByScore('LINE#' . $userID, '-inf', '+inf');
                } elseif ($streamID) {
                    $rKeys = ipTV_lib::$redis->zRangeByScore('STREAM#' . $streamID, '-inf', '+inf');
                } else {
                    $rKeys = ipTV_lib::$redis->zRangeByScore('LIVE', '-inf', '+inf');
                }

                if (!empty($rKeys)) {
                    return [$rKeys, array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rKeys))];
                }

                return [];
            }

            // Fallback to MySQL if Redis is not enabled
            $conditions = [];
            $params = [];

            if (!empty($serverID)) {
                $conditions[] = 't1.server_id = ?';
                $params[] = intval($serverID);
            }
            if (!empty($userID)) {
                $conditions[] = 't1.user_id = ?';
                $params[] = intval($userID);
            }
            
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $query = "SELECT t2.*, t3.*, t5.bitrate, t1.*, t1.uuid AS `uuid`
                      FROM `lines_live` t1
                      LEFT JOIN `lines` t2 ON t2.id = t1.user_id
                      LEFT JOIN `streams` t3 ON t3.id = t1.stream_id
                      LEFT JOIN `streams_servers` t5 ON t5.stream_id = t1.stream_id AND t5.server_id = t1.server_id
                      $whereClause
                      ORDER BY t1.activity_id ASC";

            self::$ipTV_db->query($query, ...$params);
            return self::$ipTV_db->get_rows(true, 'user_id', false);
        } catch (Exception $e) {
            error_log('getConnections Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ensures Redis is connected before performing any operations.
     */
    private static function ensureRedisConnected() {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }
    }

    /**
     * Retrieves a connection from Redis based on UUID.
     *
     * @param string $rUUID The UUID of the connection.
     * @return array|false Returns connection data as an associative array or false if not found.
     */
    public static function getConnection($rUUID) {
        try {
            self::ensureRedisConnected();
            $data = ipTV_lib::$redis->get($rUUID);
            return $data ? igbinary_unserialize($data) : false;
        } catch (Exception $e) {
            error_log('Redis Error in getConnection: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new connection entry in Redis.
     *
     * @param array $rData The connection data to store.
     * @return bool Returns true on success, false on failure.
     */
    public static function createConnection($rData) {
        try {
            self::ensureRedisConnected();
            $rRedis = ipTV_lib::$redis->pipeline();

            // Add to multiple sorted sets
            $rRedis->zAdd('LINE#' . $rData['identity'], $rData['date_start'], $rData['uuid']);
            $rRedis->zAdd('LINE_ALL#' . $rData['identity'], $rData['date_start'], $rData['uuid']);
            $rRedis->zAdd('STREAM#' . $rData['stream_id'], $rData['date_start'], $rData['uuid']);
            $rRedis->zAdd('SERVER#' . $rData['server_id'], $rData['date_start'], $rData['uuid']);
            
            // If user exists, store in SERVER_LINES
            if (!empty($rData['user_id'])) {
                $rRedis->zAdd('SERVER_LINES#' . $rData['server_id'], $rData['user_id'], $rData['uuid']);
            }

            // Global connection tracking
            $rRedis->zAdd('CONNECTIONS', $rData['date_start'], $rData['uuid']);
            $rRedis->zAdd('LIVE', $rData['date_start'], $rData['uuid']);

            // Store full connection data
            $rRedis->set($rData['uuid'], igbinary_serialize($rData));

            // Execute all commands in one batch
            return $rRedis->exec() !== false;
        } catch (Exception $e) {
            error_log('Redis Error in createConnection: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing connection entry in Redis.
     *
     * @param array $rData The original connection data.
     * @param array $rChanges The changes to apply to the connection.
     * @param string|null $rOption ('open' or 'close') Determines if the connection should be marked as active or ended.
     * @return array|false Returns the updated connection data or false if an error occurs.
     */
    public static function updateConnection($rData, $rChanges = [], $rOption = null) {
        try {
            self::ensureRedisConnected();

            if (empty($rData) || empty($rData['uuid'])) {
                error_log('updateConnection Error: Invalid connection data.');
                return false;
            }

            $rUUID = $rData['uuid'];
            $rUpdatedData = array_merge($rData, $rChanges);
            $rRedis = ipTV_lib::$redis->pipeline();

            if ($rOption === 'open') {
                $rRedis->sRem('ENDED', $rUUID);
                $rRedis->zAdd('LIVE', $rUpdatedData['date_start'], $rUUID);
                $rRedis->zAdd('LINE#' . $rUpdatedData['identity'], $rUpdatedData['date_start'], $rUUID);
                $rRedis->zAdd('STREAM#' . $rUpdatedData['stream_id'], $rUpdatedData['date_start'], $rUUID);
                $rRedis->zAdd('SERVER#' . $rUpdatedData['server_id'], $rUpdatedData['date_start'], $rUUID);

                if (!empty($rUpdatedData['user_id']) && $rUpdatedData['hls_end'] == 1) {
                    $rUpdatedData['hls_end'] = 0;
                    $rRedis->zAdd('SERVER_LINES#' . $rUpdatedData['server_id'], $rUpdatedData['user_id'], $rUUID);
                }
            } elseif ($rOption === 'close') {
                $rRedis->sAdd('ENDED', $rUUID);
                $rRedis->zRem('LIVE', $rUUID);
                $rRedis->zRem('LINE#' . $rData['identity'], $rUUID);
                $rRedis->zRem('STREAM#' . $rData['stream_id'], $rUUID);
                $rRedis->zRem('SERVER#' . $rData['server_id'], $rUUID);

                if (!empty($rUpdatedData['user_id']) && $rUpdatedData['hls_end'] == 0) {
                    $rUpdatedData['hls_end'] = 1;
                    $rRedis->zRem('SERVER_LINES#' . $rData['server_id'], $rUUID);
                }
            }

            // Store the updated connection data
            $rRedis->set($rUUID, igbinary_serialize($rUpdatedData));

            // Execute all commands and check for success
            return $rRedis->exec() ? $rUpdatedData : false;
        } catch (Exception $e) {
            error_log('Redis Error in updateConnection: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieves active stream connections from Redis.
     *
     * @param array $streamIDs Array of stream IDs to fetch connections for.
     * @param bool $rGroup If true, groups connections by stream ID.
     * @param bool $rCount If true, returns only the connection count per stream.
     *
     * @return array Connection data structured by stream ID.
     */
    public static function getStreamConnections($streamIDs, $rGroup = true, $rCount = false) {
        try {
            if (empty($streamIDs) || !is_array($streamIDs)) {
                error_log('getStreamConnections Error: Invalid stream IDs.');
                return [];
            }

            self::ensureRedisConnected();
            $rRedis = ipTV_lib::$redis->pipeline();

            // Fetch Redis keys for each stream ID
            foreach ($streamIDs as $streamID) {
                $rRedis->zRevRangeByScore("STREAM#{$streamID}", '+inf', '-inf');
            }

            $rGroups = $rRedis->exec();
            $rConnectionMap = [];
            $rRedisKeys = [];

            foreach ($rGroups as $index => $rKeys) {
                if ($rCount) {
                    $rConnectionMap[$streamIDs[$index]] = count($rKeys);
                } elseif (!empty($rKeys)) {
                    $rRedisKeys = array_merge($rRedisKeys, $rKeys);
                }
            }

            if (!$rCount && !empty($rRedisKeys)) {
                $rRedisKeys = array_unique($rRedisKeys);
                $rRedis = ipTV_lib::$redis->pipeline();

                foreach ($rRedisKeys as $rKey) {
                    $rRedis->get($rKey);
                }

                $rResults = $rRedis->exec();

                foreach ($rResults as $rData) {
                    $rRow = igbinary_unserialize($rData);
                    if (!empty($rRow) && isset($rRow['stream_id'])) {
                        if ($rGroup) {
                            $rConnectionMap[$rRow['stream_id']][] = $rRow;
                        } else {
                            $rConnectionMap[$rRow['stream_id']][$rRow['server_id']][] = $rRow;
                        }
                    }
                }
            }

            return $rConnectionMap;
        } catch (Exception $e) {
            error_log('Redis Error in getStreamConnections: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Updates a stream by inserting a signal entry into the database.
     *
     * @param int $streamID The ID of the stream to update.
     * @return bool Always returns true.
     */
    public static function updateStream($streamID) {
        $customData = json_encode(['type' => 'update_stream', 'id' => $streamID]);

        // Insert directly, ensuring unique entry
        self::$ipTV_db->query(
            'INSERT INTO `signals` (`server_id`, `cache`, `time`, `custom_data`)
            SELECT ?, 1, ?, ? FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1 FROM `signals` WHERE `server_id` = ? AND `cache` = 1 AND `custom_data` = ?
            );',
            self::getMainID(), time(), $customData, self::getMainID(), $customData
        );

        return true;
    }

    /**
     * Retrieves the streaming URL for a given server.
     *
     * @param int|null $serverID The ID of the server (defaults to the current server).
     * @param bool $rForceHTTP If true, forces HTTP protocol.
     * @return string The generated streaming URL.
     */
    public static function getStreamingURL($serverID = null, $rForceHTTP = false) {
        $serverID = $serverID ?? SERVER_ID;
        $server = ipTV_lib::$Servers[$serverID] ?? null;

        if (!$server) {
            error_log("getStreamingURL Error: Invalid server ID: {$serverID}");
            return '';
        }

        $protocol = $rForceHTTP ? 'http' : ($server['server_protocol'] ?? 'http');
        $domain = null;

        if (!empty(HOST) && in_array(strtolower(HOST), array_map('strtolower', $server['domains']['urls'] ?? []))) {
            $domain = HOST;
        } elseif ($server['random_ip'] && !empty($server['domains']['urls'])) {
            $domain = $server['domains']['urls'][array_rand($server['domains']['urls'])];
        }

        return $domain
            ? "{$protocol}://{$domain}:{$server[$protocol . '_broadcast_port']}"
            : rtrim($server[$protocol . '_url'] ?? '', '/');
    }

    /**
     * Retrieves the main server ID.
     *
     * @return int|null The ID of the main server, or null if not found.
     */
    public static function getMainID() {
        return array_search(true, array_column(ipTV_lib::$Servers, 'is_main'), true) ?: null;
    }

    /**
     * Retrieves bouquet mappings for a given stream ID.
     *
     * @param int $streamID The stream ID.
     * @return array The bouquet mappings.
     */
    public static function getBouquetMap($streamID) {
        $cacheFile = CACHE_TMP_PATH . 'bouquet_map';

        if (!file_exists($cacheFile)) {
            return [];
        }

        $rBouquetMap = igbinary_unserialize(file_get_contents($cacheFile));

        return isset($rBouquetMap[$streamID]) ? $rBouquetMap[$streamID] : [];
    }

    /**
     * Retrieves all ended streaming connections.
     *
     * @return array An array of ended connections.
     */
    public static function getEnded() {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }

        $rKeys = ipTV_lib::$redis->sMembers('ENDED');

        return !empty($rKeys) ? array_map('igbinary_unserialize', ipTV_lib::$redis->mGet($rKeys)) : [];
    }

    /**
     * Sends a Redis signal to a specific server.
     *
     * @param int $PID Process ID.
     * @param int $serverID Server ID.
     * @param int $rRTMP RTMP flag.
     * @param mixed $rCustomData Additional custom data.
     * @return bool True if successful, false otherwise.
     */
    public static function redisSignal($PID, $serverID, $rRTMP, $rCustomData = null) {
        if (!is_object(ipTV_lib::$redis)) {
            ipTV_lib::connectRedis();
        }

        $rKey = 'SIGNAL#' . md5("{$serverID}#{$PID}#{$rRTMP}");
        $rData = [
            'pid' => $PID,
            'server_id' => $serverID,
            'rtmp' => $rRTMP,
            'time' => time(),
            'custom_data' => $rCustomData,
            'key' => $rKey
        ];

        return ipTV_lib::$redis->multi()
            ->sAdd("SIGNALS#{$serverID}", $rKey)
            ->set($rKey, igbinary_serialize($rData))
            ->exec();
    }

    /**
     * Checks if an IP address matches a CIDR range.
     *
     * @param string $rASN The ASN (Autonomous System Number) identifier.
     * @param string $rIP The IP address to check.
     * @return array|null The CIDR match data or null if no match is found.
     */
    public static function matchCIDR($rASN, $rIP) {
        $cidrFile = CIDR_TMP_PATH . $rASN;

        if (!file_exists($cidrFile) || !filter_var($rIP, FILTER_VALIDATE_IP)) {
            return null;
        }

        $rCIDRs = json_decode(file_get_contents($cidrFile), true);

        foreach ($rCIDRs as $rCIDR => $rData) {
            if (ip2long($rData[1]) <= ip2long($rIP) && ip2long($rIP) <= ip2long($rData[2])) {
                return $rData;
            }
        }

        return null;
    }

    /**
     * Checks if the given ISP is blocked.
     *
     * @param string $rConISP The ISP name to check.
     * @return int Returns 1 if blocked, 0 otherwise.
     */
    public static function checkISP($rConISP) {
        if (empty($rConISP) || !is_array(ipTV_lib::$blockedISP)) {
            return 0;
        }

        foreach (ipTV_lib::$blockedISP as $rISP) {
            if (!isset($rISP['isp'], $rISP['blocked'])) {
                continue;
            }

            if (strcasecmp($rConISP, $rISP['isp']) === 0) {
                return intval($rISP['blocked']);
            }
        }

        return 0;
    }

    /**
     * Checks if the given ASN (Autonomous System Number) is blocked.
     *
     * @param string $rASN The ASN to check.
     * @return bool Returns true if blocked, false otherwise.
     */
    public static function checkServer($rASN) {
        return (!empty($rASN) && isset(ipTV_lib::$blockedServers[$rASN]));
    }

}
