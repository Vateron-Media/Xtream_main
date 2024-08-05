<?php

function decrypt_config($data, $key) {
    $index = 0;
    $output = '';
    foreach (str_split($data) as $char) {
        $output .= chr(ord($char) ^ ord($key[$index++ % strlen($key)]));
    }
    return $output;
}
function getStats() {
    $rJSON = array();
    $rJSON['cpu'] = round(getTotalCPU(), 2);
    $rJSON['cpu_cores'] = intval(shell_exec('cat /proc/cpuinfo | grep "^processor" | wc -l'));
    $rJSON['cpu_avg'] = round((sys_getloadavg()[0] * 100) / (($rJSON['cpu_cores'] ?: 1)), 2);
    $rJSON['cpu_name'] = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | uniq | awk -F: '{print \$2}'"));
    if ($rJSON['cpu_avg'] > 100) {
        $rJSON['cpu_avg'] = 100;
    }
    $rFree = explode("\n", trim(shell_exec('free')));
    $rMemory = preg_split('/[\\s]+/', $rFree[1]);
    $rTotalUsed = intval($rMemory[2]);
    $rTotalRAM = intval($rMemory[1]);
    $rJSON['total_mem'] = $rTotalRAM;
    $rJSON['total_mem_free'] = $rTotalRAM - $rTotalUsed;
    $rJSON['total_mem_used'] = $rTotalUsed + getTotalTmpfs();
    $rJSON['total_mem_used_percent'] = round($rJSON['total_mem_used'] / $rJSON['total_mem'] * 100, 2);
    $rJSON['total_disk_space'] = disk_total_space(IPTV_ROOT_PATH);
    $rJSON['free_disk_space'] = disk_free_space(IPTV_ROOT_PATH);
    $rJSON['kernel'] = trim(shell_exec('uname -r'));
    $rJSON['uptime'] = getUptime();
    $rJSON['total_running_streams'] = (int) trim(shell_exec('ps ax | grep -v grep | grep -c ffmpeg'));
    $rJSON['bytes_sent'] = 0;
    $rJSON['bytes_sent_total'] = 0;
    $rJSON['bytes_received'] = 0;
    $rJSON['bytes_received_total'] = 0;
    $rJSON['network_speed'] = 0;
    $rJSON['interfaces'] = getNetworkInterfaces();
    $rJSON['network_speed'] = 0;
    if ($rJSON['cpu'] > 100) {
        $rJSON['cpu'] = 100;
    }
    if ($rJSON['total_mem'] < $rJSON['total_mem_used']) {
        $rJSON['total_mem_used'] = $rJSON['total_mem'];
    }
    if ($rJSON['total_mem_used_percent'] > 100) {
        $rJSON['total_mem_used_percent'] = 100;
    }

    $rJSON['network_info'] = getNetwork((ipTV_lib::$StreamingServers[SERVER_ID]['network_interface'] == 'auto' ? null : ipTV_lib::$StreamingServers[SERVER_ID]['network_interface']));
    foreach ($rJSON['network_info'] as $rInterface => $rData) {
        $rJSON['bytes_sent_total'] = (intval(trim(file_get_contents('/sys/class/net/' . $rInterface . '/statistics/tx_bytes'))) ?: 0);
        $rJSON['bytes_received_total'] = (intval(trim(file_get_contents('/sys/class/net/' . $rInterface . '/statistics/tx_bytes'))) ?: 0);
        $rJSON['bytes_sent'] += $rData['bytes_sent'];
        $rJSON['bytes_received'] += $rData['bytes_received'];
    }

    list($rJSON['cpu_load_average']) = sys_getloadavg();
    return $rJSON;
}
function isMobileDevice() {
    $aMobileUA = array("/iphone/i" => "iPhone", "/ipod/i" => "iPod", "/ipad/i" => "iPad", "/android/i" => "Android", "/blackberry/i" => "BlackBerry", "/webos/i" => "Mobile");
    foreach ($aMobileUA as $sMobileKey => $sMobileOS) {
        if (preg_match($sMobileKey, $_SERVER["HTTP_USER_AGENT"])) {
            return true;
        }
    }
    return false;
}
function epg_search($array, $key, $value) {
    $results = array();
    formatArrayResults($array, $key, $value, $results);
    return $results;
}
function formatArrayResults($array, $key, $value, &$results) {
    if (!is_array($array)) {
        return;
    }
    if (isset($array[$key]) && $array[$key] == $value) {
        $results[] = $array;
    }
    foreach ($array as $item_value) {
        formatArrayResults($item_value, $key, $value, $results);
    }
}
function KillProcessCmd($file, $time = 600) {
    if (file_exists($file)) {
        $pid = trim(file_get_contents($file));
        if (file_exists("/proc/" . $pid)) {
            if (time() - filemtime($file) < $time) {
                die("Running...");
            }
            posix_kill($pid, 9);
        }
    }
    file_put_contents($file, getmypid());
    return false;
}
/** 
 * Checks for flood attempts based on IP address. 
 * 
 * This function checks for flood attempts based on the provided IP address. 
 * It handles the restriction of flood attempts based on settings and time intervals. 
 * If the IP is not provided, it retrieves the user's IP address. 
 * It excludes certain IPs from flood checking based on settings. 
 * It tracks and limits flood attempts within a specified time interval. 
 * If the number of requests exceeds the limit, it blocks the IP and logs the attack. 
 * 
 * @param string|null $rIP (Optional) The IP address to check for flood attempts. 
 * @return null|null Returns null if no flood attempt is detected, or a string indicating the block status if the IP is blocked. 
 */
function checkFlood($rIP = null) {
    global $ipTV_db;
    if (ipTV_lib::$settings['flood_limit'] != 0) {
        if (!$rIP) {
            $rIP = ipTV_streaming::getUserIP();
        }
        if (!(empty($rIP) || in_array($rIP, ipTV_streaming::getAllowedIPs()))) {
            $rFloodExclude = array_filter(array_unique(explode(',', ipTV_lib::$settings['flood_ips_exclude'])));
            if (!in_array($rIP, $rFloodExclude)) {
                $rIPFile = FLOOD_TMP_PATH . $rIP;
                if (file_exists($rIPFile)) {
                    $rFloodRow = json_decode(file_get_contents($rIPFile), true);
                    $rFloodSeconds = ipTV_lib::$settings['flood_seconds'];
                    $rFloodLimit = ipTV_lib::$settings['flood_limit'];
                    if (time() - $rFloodRow['last_request'] <= $rFloodSeconds) {
                        $rFloodRow['requests']++;
                        if ($rFloodLimit > $rFloodRow['requests']) {
                            $rFloodRow['last_request'] = time();
                            file_put_contents($rIPFile, json_encode($rFloodRow), LOCK_EX);
                        } else {
                            if (!in_array($rIP, ipTV_lib::$blockedISP)) {
                                $ipTV_db->query('INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES(\'%s\',\'%s\',\'%d\')', $rIP, 'FLOOD ATTACK', time());
                                touch(FLOOD_TMP_PATH . 'block_' . $rIP);
                            }
                            ipTV_lib::unlink_file($rIPFile);
                            return null;
                        }
                    } else {
                        $rFloodRow['requests'] = 0;
                        $rFloodRow['last_request'] = time();
                        file_put_contents($rIPFile, json_encode($rFloodRow), LOCK_EX);
                    }
                } else {
                    file_put_contents($rIPFile, json_encode(array('requests' => 0, 'last_request' => time())), LOCK_EX);
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
}
/** 
 * Checks for authentication flood attempts for a user and IP address. 
 * 
 * This function checks for authentication flood attempts for a user and optional IP address. 
 * It verifies if the user is not a restreamer and checks the IP address against allowed IPs and exclusions. 
 * It tracks and limits authentication flood attempts based on settings and time intervals. 
 * If the number of attempts exceeds the limit, it blocks further attempts until a specified time. 
 * 
 * @param array $rUser The user information containing the ID and restreamer status. 
 * @param string|null $rIP (Optional) The IP address of the user. 
 * @return null|null Returns null if no authentication flood attempt is detected, or a string indicating the block status if the user is blocked. 
 */
function checkAuthFlood($rUser, $rIP = null) {
    if (ipTV_lib::$settings['auth_flood_limit'] != 0) {
        if (!$rUser['is_restreamer']) {
            if (!$rIP) {
                $rIP = ipTV_streaming::getUserIP();
            }
            if (!(empty($rIP) || in_array($rIP, ipTV_streaming::getAllowedIPs()))) {
                $rFloodExclude = array_filter(array_unique(explode(',', ipTV_lib::$settings['flood_ips_exclude'])));
                if (!in_array($rIP, $rFloodExclude)) {
                    $rUserFile = FLOOD_TMP_PATH . intval($rUser['id']) . '_' . $rIP;
                    if (file_exists($rUserFile)) {
                        $rFloodRow = json_decode(file_get_contents($rUserFile), true);
                        $rFloodSeconds = ipTV_lib::$settings['auth_flood_seconds'];
                        $rFloodLimit = ipTV_lib::$settings['auth_flood_limit'];
                        $rFloodRow['attempts'] = truncateAttempts($rFloodRow['attempts'], $rFloodSeconds, true);
                        if ($rFloodLimit < count($rFloodRow['attempts'])) {
                            $rFloodRow['block_until'] = time() + intval(ipTV_lib::$settings['auth_flood_seconds']);
                        }
                        $rFloodRow['attempts'][] = time();
                        file_put_contents($rUserFile, json_encode($rFloodRow), LOCK_EX);
                    } else {
                        file_put_contents($rUserFile, json_encode(array('attempts' => array(time()))), LOCK_EX);
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
}
/** 
 * Checks for brute force attempts based on IP, MAC address, and username. 
 * 
 * This function checks for brute force attempts based on the provided IP, MAC address, and username. 
 * It handles the restriction of brute force attempts based on settings and frequency. 
 * If the IP is not provided, it retrieves the user's IP address. 
 * It excludes certain IPs from flood checking based on settings. 
 * It tracks and limits brute force attempts for MAC and username separately. 
 * If the number of attempts exceeds the limit, it blocks the IP and logs the attack. 
 * 
 * @param string|null $rIP (Optional) The IP address of the user. 
 * @param string|null $rMAC (Optional) The MAC address of the device. 
 * @param string|null $rUsername (Optional) The username of the user. 
 * @return null|null|string Returns null if no brute force attempt is detected, or a string indicating the type of attack if the IP is blocked. 
 */
function checkBruteforce($rIP = null, $rMAC = null, $rUsername = null) {
    global $ipTV_db;
    if ($rMAC || $rUsername) {
        if (!($rMAC && ipTV_lib::$settings['bruteforce_mac_attempts'] == 0)) {
            if (!($rUsername && ipTV_lib::$settings['bruteforce_username_attempts'] == 0)) {
                if (!$rIP) {
                    $rIP = ipTV_streaming::getUserIP();
                }
                if (!(empty($rIP) || in_array($rIP, ipTV_streaming::getAllowedIPs()))) {
                    $rFloodExclude = array_filter(array_unique(explode(',', ipTV_lib::$settings['flood_ips_exclude'])));
                    if (!in_array($rIP, $rFloodExclude)) {
                        $rFloodType = (!is_null($rMAC) ? 'mac' : 'user');
                        $rTerm = (!is_null($rMAC) ? $rMAC : $rUsername);
                        $rIPFile = FLOOD_TMP_PATH . $rIP . '_' . $rFloodType;
                        if (file_exists($rIPFile)) {
                            $rFloodRow = json_decode(file_get_contents($rIPFile), true);
                            $rFloodSeconds = intval(ipTV_lib::$settings['bruteforce_frequency']);
                            $rFloodLimit = intval(ipTV_lib::$settings[array('mac' => 'bruteforce_mac_attempts', 'user' => 'bruteforce_username_attempts')[$rFloodType]]);
                            $rFloodRow['attempts'] = truncateAttempts($rFloodRow['attempts'], $rFloodSeconds);
                            if (!in_array($rTerm, array_keys($rFloodRow['attempts']))) {
                                $rFloodRow['attempts'][$rTerm] = time();
                                if ($rFloodLimit > count($rFloodRow['attempts'])) {
                                    file_put_contents($rIPFile, json_encode($rFloodRow), LOCK_EX);
                                } else {
                                    if (!in_array($rIP, ipTV_lib::$blockedIPs)) {
                                        $ipTV_db->query('INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES(\'%s\',\'%s\',\'%s\')', $rIP, 'BRUTEFORCE ' . strtoupper($rFloodType) . ' ATTACK', time());
                                        touch(FLOOD_TMP_PATH . 'block_' . $rIP);
                                    }
                                    ipTV_lib::unlink_file($rIPFile);
                                    return null;
                                }
                            }
                        } else {
                            $rFloodRow = array('attempts' => array($rTerm => time()));
                            file_put_contents($rIPFile, json_encode($rFloodRow), LOCK_EX);
                        }
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
}
/** 
 * Truncates the attempts based on a given frequency. 
 * 
 * This function takes an array of attempts and a frequency value as input. 
 * It checks if the time difference between the current time and each attempt time is less than the given frequency. 
 * If the $rList parameter is true, it iterates through the attempt times directly. 
 * If $rList is false, it iterates through the attempts as key-value pairs. 
 * It returns an array of allowed attempts that meet the frequency criteria. 
 * 
 * @param array $rAttempts An array of attempt times or key-value pairs. 
 * @param int $rFrequency The time frequency in seconds to compare against. 
 * @param bool $rList (Optional) If true, iterates through attempts directly; otherwise, iterates through key-value pairs. 
 * @return array An array containing the allowed attempts based on the frequency criteria. 
 */
function truncateAttempts($rAttempts, $rFrequency, $rList = false) {
    $rAllowedAttempts = array();
    $rTime = time();
    if ($rList) {
        foreach ($rAttempts as $rAttemptTime) {
            if ($rTime - $rAttemptTime < $rFrequency) {
                $rAllowedAttempts[] = $rAttemptTime;
            }
        }
    } else {
        foreach ($rAttempts as $rAttempt => $rAttemptTime) {
            if ($rTime - $rAttemptTime < $rFrequency) {
                $rAllowedAttempts[$rAttempt] = $rAttemptTime;
            }
        }
    }
    return $rAllowedAttempts;
}
function GetEPGStream($stream_id, $from_now = false) {
    global $ipTV_db;
    $ipTV_db->query('SELECT `type`,`movie_properties`,`epg_id`,`channel_id` FROM `streams` WHERE `id` = \'%d\'', $stream_id);
    if ($ipTV_db->num_rows() > 0) {
        $data = $ipTV_db->get_row();
        if ($data['type'] != 2) {
            if ($from_now) {
                $ipTV_db->query('SELECT * FROM `epg_data` WHERE `epg_id` = \'%d\' AND `channel_id` = \'%s\' AND `end` >= \'%s\'', $data['epg_id'], $data['channel_id'], date('Y-m-d H:i:00'));
            } else {
                $ipTV_db->query('SELECT * FROM `epg_data` WHERE `epg_id` = \'%d\' AND `channel_id` = \'%s\'', $data['epg_id'], $data['channel_id']);
            }
            return $ipTV_db->get_rows();
        } else {
            return json_decode($data['movie_properties'], true);
        }
    }
    return array();
}
function getTotalCPU() {
    $rTotalLoad = 0;
    exec('ps -Ao pid,pcpu', $processes);
    foreach ($processes as $process) {
        $cols = explode(' ', preg_replace('!\\s+!', ' ', trim($process)));
        $rTotalLoad += floatval($cols[1]);
    }
    return $rTotalLoad / intval(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
}

function getTotalTmpfs() {
    $rTotal = 0;
    exec('df | grep tmpfs', $rOutput);
    foreach ($rOutput as $rLine) {
        $rSplit = explode(' ', preg_replace('!\\s+!', ' ', $rLine));
        if ($rSplit[0] = 'tmpfs') {
            $rTotal += intval($rSplit[2]);
        }
    }
    return $rTotal;
}

function GetCategories($type = null) {
    global $ipTV_db;
    if (is_string($type)) {
        $ipTV_db->query('SELECT t1.* FROM `stream_categories` t1 WHERE t1.category_type = \'%s\' GROUP BY t1.id ORDER BY t1.cat_order ASC', $type);
    } else {
        $ipTV_db->query('SELECT t1.* FROM `stream_categories` t1 ORDER BY t1.cat_order ASC');
    }
    return $ipTV_db->num_rows() > 0 ? $ipTV_db->get_rows(true, 'id') : array();
}
function generateUniqueCode() {
    return substr(md5(ipTV_lib::$settings['unique_id']), 0, 15);
}
function generateUserPlaylist($rUserInfo, $rDeviceKey, $rOutputKey = 'ts', $rTypeKey = null, $rNoCache = false) {
    global $ipTV_db;

    $cache_playlists = 60;

    if (!empty($rDeviceKey)) {
        if ($rOutputKey == 'mpegts') {
            $rOutputKey = 'ts';
        }
        if ($rOutputKey == 'hls') {
            $rOutputKey = 'm3u8';
        }
        if (empty($rOutputKey)) {
            $ipTV_db->query('SELECT t1.output_ext FROM `access_output` t1 INNER JOIN `devices` t2 ON t2.default_output = t1.access_output_id AND `device_key` = \'%s\'', $rDeviceKey);
        } else {
            $ipTV_db->query('SELECT t1.output_ext FROM `access_output` t1 WHERE `output_key` = \'%s\'', $rOutputKey);
        }
        if ($ipTV_db->num_rows() > 0) {
            $rCacheName = $rUserInfo['id'] . '_' . $rDeviceKey . '_' . $rOutputKey . '_' . implode('_', ($rTypeKey ?: array()));
            $rOutputExt = $ipTV_db->get_col();
            $rEncryptPlaylist = ($rUserInfo['is_restreamer'] ? ipTV_lib::$settings['encrypt_playlist_restreamer'] : ipTV_lib::$settings['encrypt_playlist']);
            if ($rUserInfo['is_stalker']) {
                $rEncryptPlaylist = false;
            }
            if (ipTV_lib::$settings['use_mdomain_in_lists'] == 1) {
                $rDomainName = ipTV_lib::$StreamingServers[SERVER_ID]['site_url'];
            } else {
                list($host, $act) = explode(':', $_SERVER['HTTP_HOST']);
                $rDomainName = ipTV_lib::$StreamingServers[SERVER_ID]['server_protocol'] . '://' . $host . ':' . ipTV_lib::$StreamingServers[SERVER_ID]['request_port'] . '/';
            }
            if ($rDomainName) {
                $rRTMPRows = array();
                if ($rOutputKey == 'rtmp') {
                    $ipTV_db->query('SELECT t1.id,t2.server_id FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t2.stream_id = t1.id WHERE t1.rtmp_output = 1');
                    $rRTMPRows = $ipTV_db->get_rows(true, 'id', false, 'server_id');
                }
                if (empty($rOutputExt)) {
                    $rOutputExt = 'ts';
                }
                $ipTV_db->query('SELECT t1.*,t2.* FROM `devices` t1 LEFT JOIN `access_output` t2 ON t2.access_output_id = t1.default_output WHERE t1.device_key = \'%s\' LIMIT 1', $rDeviceKey);
                if (0 >= $ipTV_db->num_rows()) {
                    return false;
                }
                $rDeviceInfo = $ipTV_db->get_row();
                if (strlen($rUserInfo['access_token']) == 32) {
                    $rFilename = str_replace('{USERNAME}', $rUserInfo['access_token'], $rDeviceInfo['device_filename']);
                } else {
                    $rFilename = str_replace('{USERNAME}', $rUserInfo['username'], $rDeviceInfo['device_filename']);
                }
                if (!(0 < $cache_playlists && !$rNoCache && file_exists(PLAYLIST_PATH . md5($rCacheName)))) {
                    $rData = '';
                    $rSeriesAllocation = $rSeriesEpisodes = $rSeriesInfo = array();
                    $rUserInfo['episode_ids'] = array();
                    if (count($rUserInfo['series_ids']) > 0) {
                        $ipTV_db->query('SELECT * FROM `streams_series` WHERE `id` IN (' . implode(',', $rUserInfo['series_ids']) . ')');
                        $rSeriesInfo = $ipTV_db->get_rows(true, 'id');
                        if (count($rUserInfo['series_ids']) > 0) {
                            $ipTV_db->query('SELECT stream_id, series_id, season_num, episode_num FROM `streams_episodes` WHERE series_id IN (' . implode(',', $rUserInfo['series_ids']) . ') ORDER BY FIELD(series_id,' . implode(',', $rUserInfo['series_ids']) . '), season_num ASC, episode_num ASC');
                            foreach ($ipTV_db->get_rows(true, 'series_id', false) as $rSeriesID => $rEpisodes) {
                                foreach ($rEpisodes as $rEpisode) {
                                    $rSeriesEpisodes[$rEpisode['stream_id']] = array($rEpisode['season_num'], $rEpisode['episode_num']);
                                    $rSeriesAllocation[$rEpisode['stream_id']] = $rSeriesID;
                                    $rUserInfo['episode_ids'][] = $rEpisode['stream_id'];
                                }
                            }
                        }
                    }
                    if (count($rUserInfo['episode_ids']) > 0) {
                        $rUserInfo['channel_ids'] = array_merge($rUserInfo['channel_ids'], $rUserInfo['episode_ids']);
                    }
                    $rChannelIDs = array();
                    $rAdded = false;
                    if ($rTypeKey) {
                        foreach ($rTypeKey as $rType) {
                            switch ($rType) {
                                case 'live':
                                case 'created_live':
                                    if (!$rAdded) {
                                        $rChannelIDs = array_merge($rChannelIDs, $rUserInfo['live_ids']);
                                        $rAdded = true;
                                        break;
                                    }
                                    break;
                                case 'movie':
                                    $rChannelIDs = array_merge($rChannelIDs, $rUserInfo['vod_ids']);
                                    break;
                                case 'radio_streams':
                                    $rChannelIDs = array_merge($rChannelIDs, $rUserInfo['radio_ids']);
                                    break;
                                case 'series':
                                    $rChannelIDs = array_merge($rChannelIDs, $rUserInfo['episode_ids']);
                                    break;
                            }
                        }
                    } else {
                        $rChannelIDs = $rUserInfo['channel_ids'];
                    }
                    if (in_array(ipTV_lib::$settings['channel_number_type'], array('bouquet_new', 'manual'))) {
                        $rChannelIDs = ipTV_lib::sortChannels($rChannelIDs);
                    }
                    unset($rUserInfo['live_ids'], $rUserInfo['vod_ids'], $rUserInfo['radio_ids'], $rUserInfo['episode_ids'], $rUserInfo['channel_ids']);
                    $rOutputFile = null;
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    if (strlen($rUserInfo['access_token']) == 32) {
                        header('Content-Disposition: attachment; filename="' . str_replace('{USERNAME}', $rUserInfo['access_token'], $rDeviceInfo['device_filename']) . '"');
                    } else {
                        header('Content-Disposition: attachment; filename="' . str_replace('{USERNAME}', $rUserInfo['username'], $rDeviceInfo['device_filename']) . '"');
                    }
                    if (ipTV_lib::$settings['cache_playlists'] > 0) {
                        $rOutputPath = PLAYLIST_PATH . md5($rCacheName) . '.write';
                        $rOutputFile = fopen($rOutputPath, 'w');
                    }
                    if ($rDeviceKey == 'starlivev5') {
                        $rOutput = array();
                        $rOutput['iptvstreams_list'] = array();
                        $rOutput['iptvstreams_list']['@version'] = 1;
                        $rOutput['iptvstreams_list']['group'] = array();
                        $rOutput['iptvstreams_list']['group']['name'] = 'IPTV';
                        $rOutput['iptvstreams_list']['group']['channel'] = array();
                        foreach (array_chunk($rChannelIDs, 1000) as $rBlockIDs) {
                            $rOrder = 'FIELD(`t1`.`id`,' . implode(',', $rBlockIDs) . ')';
                            $ipTV_db->query('SELECT t1.id,t1.channel_id,t1.movie_properties,t1.stream_icon,t1.custom_sid,t1.category_id,t1.stream_display_name,t2.type_output,t2.type_key,t1.target_container,t2.live FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type WHERE `t1`.`id` IN (' . implode(',', array_map('intval', $rBlockIDs)) . ') ORDER BY ' . $rOrder . ';');
                            $rRows = $ipTV_db->get_rows();
                            foreach ($rRows as $rChannelInfo) {
                                if (!$rTypeKey || in_array($rChannelInfo['type_output'], $rTypeKey)) {
                                    if (!$rChannelInfo['target_container']) {
                                        $rChannelInfo['target_container'] = 'mp4';
                                    }
                                    $rProperties = (!is_array($rChannelInfo['movie_properties']) ? json_decode($rChannelInfo['movie_properties'], true) : $rChannelInfo['movie_properties']);
                                    if ($rChannelInfo['type_key'] == 'series') {
                                        $rSeriesID = $rSeriesAllocation[$rChannelInfo['id']];
                                        $rChannelInfo['live'] = 0;
                                        $rChannelInfo['stream_display_name'] = $rSeriesInfo[$rSeriesID]['title'] . ' S' . sprintf('%02d', $rSeriesEpisodes[$rChannelInfo['id']][0]) . 'E' . sprintf('%02d', $rSeriesEpisodes[$rChannelInfo['id']][1]);
                                        $rChannelInfo['movie_properties'] = array('movie_image' => (!empty($rProperties['movie_image']) ? $rProperties['movie_image'] : $rSeriesInfo['cover']));
                                        $rChannelInfo['type_output'] = 'series';
                                        $rChannelInfo['category_id'] = $rSeriesInfo[$rSeriesID]['category_id'];
                                    } else {
                                        $rChannelInfo['stream_display_name'] = $rChannelInfo['stream_display_name'];
                                    }
                                    if (strlen($rUserInfo['access_token']) == 32) {
                                        $rURL = $rDomainName . $rChannelInfo['type_output'] . '/' . $rUserInfo['access_token'] . '/';
                                        if ($rChannelInfo['live'] == 0) {
                                            $rURL .= $rChannelInfo['id'] . '.' . $rChannelInfo['target_container'];
                                        } else {
                                            $rURL .= $rChannelInfo['id'] . '.' . $rOutputExt;
                                        }
                                    } else {
                                        if ($rEncryptPlaylist) {
                                            $rEncData = $rChannelInfo['type_output'] . '/' . $rUserInfo['username'] . '/' . $rUserInfo['password'] . '/';
                                            if ($rChannelInfo['live'] == 0) {
                                                $rEncData .= $rChannelInfo['id'] . '/' . $rChannelInfo['target_container'];
                                            } else {
                                                $rEncData .= $rChannelInfo['id'] . '/' . $rOutputExt;
                                            }
                                            $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                                            $rURL = $rDomainName . 'play/' . $rToken;
                                            if ($rChannelInfo['live'] == 0) {
                                                $rURL .= '#.' . $rChannelInfo['target_container'];
                                            }
                                        } else {
                                            $rURL = $rDomainName . $rChannelInfo['type_output'] . '/' . $rUserInfo['username'] . '/' . $rUserInfo['password'] . '/';
                                            if ($rChannelInfo['live'] == 0) {
                                                $rURL .= $rChannelInfo['id'] . '.' . $rChannelInfo['target_container'];
                                            } else {
                                                $rURL .= $rChannelInfo['id'] . '.' . $rOutputExt;
                                            }
                                        }
                                    }
                                    if ($rChannelInfo['live'] == 0) {
                                        if (!empty($rProperties['movie_image'])) {
                                            $rIcon = $rProperties['movie_image'];
                                        }
                                    } else {
                                        $rIcon = $rChannelInfo['stream_icon'];
                                    }
                                    $rChannel = array();
                                    $rChannel['name'] = $rChannelInfo['stream_display_name'];
                                    $rChannel['icon'] = $rIcon;
                                    $rChannel['stream_url'] = $rURL;
                                    $rChannel['stream_type'] = 0;
                                    $rOutput['iptvstreams_list']['group']['channel'][] = $rChannel;
                                }
                            }
                            unset($rRows);
                        }
                        $rData = json_encode((object) $rOutput);
                    } else {
                        if (!empty($rDeviceInfo['device_header'])) {
                            $rAppend = ($rDeviceInfo['device_header'] == '#EXTM3U' ? "\n" . '#EXT-X-SESSION-DATA:DATA-ID="XtreamUI.' . str_replace('.', '_', SCRIPT_VERSION) . '"' : '');
                            $rData = str_replace(array('&lt;', '&gt;'), array('<', '>'), str_replace(array('{BOUQUET_NAME}', '{USERNAME}', '{PASSWORD}', '{SERVER_URL}', '{OUTPUT_KEY}'), array(ipTV_lib::$settings['server_name'], $rUserInfo['username'], $rUserInfo['password'], $rDomainName, $rOutputKey), $rDeviceInfo['device_header'] . $rAppend)) . "\n";
                            if ($rOutputFile) {
                                fwrite($rOutputFile, $rData);
                            }
                            echo $rData;
                            unset($rData);
                        }
                        if (!empty($rDeviceInfo['device_conf'])) {
                            if (preg_match('/\\{URL\\#(.*?)\\}/', $rDeviceInfo['device_conf'], $rMatches)) {
                                $rCharts = str_split($rMatches[1]);
                                $rPattern = $rMatches[0];
                            } else {
                                $rCharts = array();
                                $rPattern = '{URL}';
                            }
                            foreach (array_chunk($rChannelIDs, 1000) as $rBlockIDs) {
                                $rOrder = 'FIELD(`t1`.`id`,' . implode(',', $rBlockIDs) . ')';
                                $ipTV_db->query('SELECT t1.id,t1.channel_id,t1.movie_properties,t1.stream_icon,t1.custom_sid,t1.category_id,t1.stream_display_name,t2.type_output,t2.type_key,t1.target_container,t2.live,t1.tv_archive_duration,t1.tv_archive_server_id FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type WHERE `t1`.`id` IN (' . implode(',', array_map('intval', $rBlockIDs)) . ') ORDER BY ' . $rOrder . ';');
                                $rRows = $ipTV_db->get_rows();
                                foreach ($rRows as $rChannel) {
                                    if (!empty($rTypeKey) && in_array($rChannel['type_output'], $rTypeKey)) {
                                        if (!$rChannel['target_container']) {
                                            $rChannel['target_container'] = 'mp4';
                                        }
                                        $rConfig = $rDeviceInfo['device_conf'];
                                        if ($rDeviceInfo['device_key'] == 'm3u_plus') {
                                            if (!$rChannel['live']) {
                                                $rConfig = str_replace('tvg-id="{CHANNEL_ID}" ', '', $rConfig);
                                            }
                                            if (!$rEncryptPlaylist) {
                                                $rConfig = str_replace('xui-id="{XUI_ID}" ', '', $rConfig);
                                            }
                                            if (0 < $rChannel['tv_archive_server_id'] && 0 < $rChannel['tv_archive_duration']) {
                                                $rConfig = str_replace('#EXTINF:-1 ', '#EXTINF:-1 timeshift="' . intval($rChannel['tv_archive_duration']) . '" ', $rConfig);
                                            }
                                        }
                                        $rProperties = (!is_array($rChannel['movie_properties']) ? json_decode($rChannel['movie_properties'], true) : $rChannel['movie_properties']);
                                        if ($rChannel['type_key'] == 'series') {
                                            $rSeriesID = $rSeriesAllocation[$rChannel['id']];
                                            $rChannel['live'] = 0;
                                            $rChannel['stream_display_name'] = $rSeriesInfo[$rSeriesID]['title'] . ' S' . sprintf('%02d', $rSeriesEpisodes[$rChannel['id']][0]) . 'E' . sprintf('%02d', $rSeriesEpisodes[$rChannel['id']][1]);
                                            $rChannel['movie_properties'] = array('movie_image' => (!empty($rProperties['movie_image']) ? $rProperties['movie_image'] : $rSeriesInfo['cover']));
                                            $rChannel['type_output'] = 'series';
                                            $rChannel['category_id'] = $rSeriesInfo[$rSeriesID]['category_id'];
                                        } else {
                                            $rChannel['stream_display_name'] = $rChannel['stream_display_name'];
                                        }
                                        if ($rChannel['live'] == 0) {
                                            if (strlen($rUserInfo['access_token']) == 32) {
                                                $rURL = $rDomainName . $rChannel['type_output'] . '/' . $rUserInfo['access_token'] . '/' . $rChannel['id'] . '.' . $rChannel['target_container'];
                                            } else {
                                                if ($rEncryptPlaylist) {
                                                    $rEncData = $rChannel['type_output'] . '/' . $rUserInfo['username'] . '/' . $rUserInfo['password'] . '/' . $rChannel['id'] . '/' . $rChannel['target_container'];
                                                    $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                                                    $rURL = $rDomainName . 'play/' . $rToken . '#.' . $rChannel['target_container'];
                                                } else {
                                                    $rURL = $rDomainName . $rChannel['type_output'] . '/' . $rUserInfo['username'] . '/' . $rUserInfo['password'] . '/' . $rChannel['id'] . '.' . $rChannel['target_container'];
                                                }
                                            }
                                            if (!empty($rProperties['movie_image'])) {
                                                $rIcon = $rProperties['movie_image'];
                                            }
                                        } else {
                                            if ($rOutputKey != 'rtmp' || !array_key_exists($rChannel['id'], $rRTMPRows)) {
                                                if (strlen($rUserInfo['access_token']) == 32) {
                                                    $rURL = $rDomainName . $rChannel['type_output'] . '/' . $rUserInfo['access_token'] . '/' . $rChannel['id'] . '.' . $rOutputExt;
                                                } else {
                                                    if ($rEncryptPlaylist) {
                                                        $rEncData = $rChannel['type_output'] . '/' . $rUserInfo['username'] . '/' . $rUserInfo['password'] . '/' . $rChannel['id'];
                                                        $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                                                        $rURL = $rDomainName . 'play/' . $rToken . '/' . $rOutputExt;
                                                    } else {
                                                        $rURL = $rDomainName . $rChannel['type_output'] . '/' . $rUserInfo['username'] . '/' . $rUserInfo['password'] . '/' . $rChannel['id'] . '.' . $rOutputExt;
                                                    }
                                                }
                                            } else {
                                                $rAvailableServers = array_values(array_keys($rRTMPRows[$rChannel['id']]));
                                                if (in_array($rUserInfo['force_server_id'], $rAvailableServers)) {
                                                    $rServerID = $rUserInfo['force_server_id'];
                                                } else {
                                                    if (ipTV_lib::$settings['rtmp_random'] == 1) {
                                                        $rServerID = $rAvailableServers[array_rand($rAvailableServers, 1)];
                                                    } else {
                                                        $rServerID = $rAvailableServers[0];
                                                    }
                                                }
                                                if (strlen($rUserInfo['access_token']) == 32) {
                                                    $rURL = ipTV_lib::$StreamingServers[$rServerID]['rtmp_server'] . $rChannel['id'] . '?token=' . $rUserInfo['access_token'];
                                                } else {
                                                    if ($rEncryptPlaylist) {
                                                        $rEncData = $rUserInfo['username'] . '/' . $rUserInfo['password'];
                                                        $rToken = encryptData($rEncData, ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                                                        $rURL = ipTV_lib::$StreamingServers[$rServerID]['rtmp_server'] . $rChannel['id'] . '?token=' . $rToken;
                                                    } else {
                                                        $rURL = ipTV_lib::$StreamingServers[$rServerID]['rtmp_server'] . $rChannel['id'] . '?username=' . $rUserInfo['username'] . '&password=' . $rUserInfo['password'];
                                                    }
                                                }
                                            }
                                            $rIcon = $rChannel['stream_icon'];
                                        }
                                        $rESRID = ($rChannel['live'] == 1 ? 1 : 4097);
                                        $rSID = (!empty($rChannel['custom_sid']) ? $rChannel['custom_sid'] : ':0:1:0:0:0:0:0:0:0:');
                                        $rCategoryIDs = json_decode($rChannel['category_id'], true);
                                        if (count($rCategoryIDs) > 0) {
                                            foreach ($rCategoryIDs as $rCategoryID) {
                                                if (isset(ipTV_lib::$categories[$rCategoryID])) {
                                                    $rData = str_replace(array('&lt;', '&gt;'), array('<', '>'), str_replace(array($rPattern, '{ESR_ID}', '{SID}', '{CHANNEL_NAME}', '{CHANNEL_ID}', '{XUI_ID}', '{CATEGORY}', '{CHANNEL_ICON}'), array(str_replace($rCharts, array_map('urlencode', $rCharts), $rURL), $rESRID, $rSID, $rChannel['stream_display_name'], $rChannel['channel_id'], $rChannel['id'], ipTV_lib::$categories[$rCategoryID]['category_name'], $rIcon), $rConfig)) . "\r\n";
                                                    if ($rOutputFile) {
                                                        fwrite($rOutputFile, $rData);
                                                    }
                                                    echo $rData;
                                                    unset($rData);
                                                    // if (stripos($rDeviceInfo['device_conf'], '{CATEGORY}') == false) {
                                                    //     break;
                                                    // }
                                                }
                                            }
                                        } else {
                                            $rData = str_replace(array('&lt;', '&gt;'), array('<', '>'), str_replace(array($rPattern, '{ESR_ID}', '{SID}', '{CHANNEL_NAME}', '{CHANNEL_ID}', '{XUI_ID}', '{CHANNEL_ICON}'), array(str_replace($rCharts, array_map('urlencode', $rCharts), $rURL), $rESRID, $rSID, $rChannel['stream_display_name'], $rChannel['channel_id'], $rChannel['id'], $rIcon), $rConfig)) . "\r\n";
                                            if ($rOutputFile) {
                                                fwrite($rOutputFile, $rData);
                                            }
                                            echo $rData;
                                            unset($rData);
                                        }
                                    }
                                }
                                unset($rRows);
                            }
                            $rData = trim(str_replace(array('&lt;', '&gt;'), array('<', '>'), $rDeviceInfo['device_footer']));
                            if ($rOutputFile) {
                                fwrite($rOutputFile, $rData);
                            }
                            echo $rData;
                            unset($rData);
                        }
                    }
                    if ($rOutputFile) {
                        fclose($rOutputFile);
                        rename(PLAYLIST_PATH . md5($rCacheName) . '.write', PLAYLIST_PATH . md5($rCacheName));
                    }
                    exit();
                } else {
                    header('Content-Description: File Transfer');
                    header('Content-Type: audio/mpegurl');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Disposition: attachment; filename="' . $rFilename . '"');
                    header('Content-Length: ' . filesize(PLAYLIST_PATH . md5($rCacheName)));
                    readfile(PLAYLIST_PATH . md5($rCacheName));
                    exit();
                }
            } else {
                exit();
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function GetContainerExtension($target_container, $stalker_container_priority = false) {
    $tmp = json_decode($target_container, true);
    if (is_array($tmp)) {
        $target_container = array_map('strtolower', $tmp);
    } else {
        return $target_container;
    }
    $container = $stalker_container_priority ? ipTV_lib::$settings['stalker_container_priority'] : ipTV_lib::$settings['gen_container_priority'];
    if (is_array($container)) {
        foreach ($container as $container_priority) {
            if (in_array($container_priority, $target_container)) {
                return $container_priority;
            }
        }
    }
    return $target_container[0];
}
function crontab_refresh() {
    global $ipTV_db;
    if (!file_exists(TMP_DIR . 'crontab')) {
        $rJobs = array();
        $ipTV_db->query('SELECT * FROM `crontab` WHERE `enabled` = 1;');
        foreach ($ipTV_db->get_rows() as $rRow) {
            $rFullPath = CRON_PATH . $rRow['filename'];
            if (!(pathinfo($rFullPath, PATHINFO_EXTENSION) == 'php' && file_exists($rFullPath))) {
            } else {
                $rJobs[] = $rRow['time'] . ' ' . PHP_BIN . ' ' . $rFullPath . ' # XtreamUI';
            }
        }
        shell_exec('crontab -r');
        $rTempName = tempnam('/tmp', 'crontab');
        $rHandle = fopen($rTempName, 'w');
        fwrite($rHandle, implode("\n", $rJobs) . "\n");
        fclose($rHandle);
        shell_exec('crontab -u xtreamcodes ' . $rTempName);
        @unlink($rTempName);
        file_put_contents(TMP_DIR . 'crontab', 1);
        return true;
    } else {
        return false;
    }
}
function searchQuery($tableName, $columnName, $value) {
    global $ipTV_db;
    $ipTV_db->query("SELECT * FROM `{$tableName}` WHERE `{$columnName}` = '%s'", $value);
    if ($ipTV_db->num_rows() > 0) {
        return true;
    }
    return false;
}
function getUptime() {
    if (!(file_exists('/proc/uptime') && is_readable('/proc/uptime'))) {
        return '';
    }
    $tmp = explode(' ', file_get_contents('/proc/uptime'));
    return secondsToTime(intval($tmp[0]));
}
function getNetworkInterfaces() {
    $rReturn = array();
    exec('ls /sys/class/net/', $rOutput, $rReturnVar);
    foreach ($rOutput as $rInterface) {
        $rInterface = trim(rtrim($rInterface, ':'));
        if ($rInterface != 'lo' && substr($rInterface, 0, 4) != 'bond') {
            $rReturn[] = $rInterface;
        }
    }
    return $rReturn;
}
function getNetwork($Interface = null) {
    $Return = array();
    if (file_exists(LOGS_TMP_PATH . 'network')) {
        $Network = json_decode(file_get_contents(LOGS_TMP_PATH . 'network'), true);
        foreach ($Network as $Key => $Line) {
            if (!($Interface && $Key != $Interface) && !($Key == 'lo' || !$Interface && substr($Key, 0, 4) == 'bond')) {
                $Return[$Key] = $Line;
            }
        }
    }
    return $Return;
}

function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;
    $days = (int) floor($inputSeconds / $secondsInADay);
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = (int) floor($hourSeconds / $secondsInAnHour);
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = (int) floor($minuteSeconds / $secondsInAMinute);
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = (int) ceil($remainingSeconds);
    $final = '';
    if ($days != 0) {
        $final .= "{$days}d ";
    }
    if ($hours != 0) {
        $final .= "{$hours}h ";
    }
    if ($minutes != 0) {
        $final .= "{$minutes}m ";
    }
    $final .= "{$seconds}s";
    return $final;
}
/** 
 * Encrypts the provided data using AES-256-CBC encryption with a given decryption key and device ID. 
 *  
 * @param string $rData The data to be encrypted. 
 * @param string $decryptionKey The decryption key used to encrypt the data. 
 * @param string $rDeviceID The device ID used in the encryption process. 
 * @return string The encrypted data in base64url encoding. 
 */
function encryptData($rData, $decryptionKey, $rDeviceID) {
    return base64url_encode(openssl_encrypt($rData, 'aes-256-cbc', md5(sha1($rDeviceID) . $decryptionKey), OPENSSL_RAW_DATA, substr(md5(sha1($decryptionKey)), 0, 16)));
}

/** 
 * Decrypts the provided data using AES-256-CBC decryption with a given decryption key and device ID. 
 *  
 * @param string $rData The data to be decrypted. 
 * @param string $decryptionKey The decryption key used to decrypt the data. 
 * @param string $rDeviceID The device ID used in the decryption process. 
 * @return string The decrypted data. 
 */
function decryptData($rData, $decryptionKey, $rDeviceID) {
    return openssl_decrypt(base64url_decode($rData), 'aes-256-cbc', md5(sha1($rDeviceID) . $decryptionKey), OPENSSL_RAW_DATA, substr(md5(sha1($decryptionKey)), 0, 16));
}
/** 
 * Encodes the input data using base64url encoding. 
 * 
 * This function takes the input data and encodes it using base64 encoding. It then replaces the characters '+' and '/' with '-' and '_', respectively, to make the encoding URL-safe. Finally, it removes any padding '=' characters at the end of the encoded string. 
 * 
 * @param string $rData The input data to be encoded. 
 * @return string The base64url encoded string. 
 */
function base64url_encode($rData) {
    return rtrim(strtr(base64_encode($rData), '+/', '-_'), '=');
}
/** 
 * Decodes the input data encoded using base64url encoding. 
 * 
 * This function takes the input data encoded using base64url encoding and decodes it. It first replaces the characters '-' and '_' back to '+' and '/' respectively, to revert the URL-safe encoding. Then, it decodes the base64 encoded string to retrieve the original data. 
 * 
 * @param string $rData The base64url encoded data to be decoded. 
 * @return string|false The decoded original data, or false if decoding fails. 
 */
function base64url_decode($rData) {
    return base64_decode(strtr($rData, '-_', '+/'));
}
/**
 * Generates a UUID (Universally Unique Identifier) using a specified key and length.
 *
 * @param string|null $key The key used to generate the UUID. If not provided, a random 16-byte key is generated.
 * @return string The generated UUID with the specified length.
 */
function generateUUID($key = null) {
    if ($key === null) {
        $key = random_bytes(16); // Generate a random 16-byte key
    }

    $data = openssl_random_pseudo_bytes(16, $crytoStrong);
    assert($data !== false && $crytoStrong === true);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set bits 6-7 to 10

    $uuid = vsprintf('%s%s-%s-%s-%s%s', str_split(bin2hex($key . $data), 4));

    return $uuid;
}
/** 
 * Function to check for updates based on the current version provided. 
 * 
 * @param string $currentVersion The current version to compare against 
 * @return array|bool Returns an array with the next version information (version, url, md5) if an update is available, otherwise returns false 
 */
function checkUpdate($currentVersion, $type = "main") {
    $rURL = "https://raw.githubusercontent.com/Vateron-Media/Xtream_Update/main/version.json";
    $rData = json_decode(file_get_contents($rURL), True);

    if ($rData[$type]) {
        if (version_compare($rData[$type], $currentVersion)) {
            $mainVersions = $rData["main_versions"];

            // Find the index of the current version
            $currentIndex = array_search($currentVersion, array_column($mainVersions, 'version'));

            if ($currentIndex !== false && $currentIndex < count($mainVersions) - 1) {
                // Get the next version
                $nextVersion = $mainVersions[$currentIndex + 1]['version'];
                $hashNextVersion = $mainVersions[$currentIndex + 1]['md5'];

                $version["version"] = $nextVersion;
                $version["url"] = "https://github.com/Vateron-Media/Xtream_main/releases/download/v" . $nextVersion . "/" . $type . "_xui.tar.gz";
                $version["md5"] = $hashNextVersion;
                return $version;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

function startDownload($rType, $rUser, $rDownloadPID) {
    $rFloodLimit = 2;
    if ($rFloodLimit != 0) {
        if (!$rUser['is_restreamer']) {
            $rFile = FLOOD_TMP_PATH . $rUser['id'] . '_downloads';
            if (file_exists($rFile) && time() - filemtime($rFile) < 10) {
                $rFloodRow[$rType] = array();
                foreach (json_decode(file_get_contents($rFile), true)[$rType] as $rPID) {
                    if (ipTV_streaming::isProcessRunning($rPID, 'php-fpm') && $rPID != $rDownloadPID) {
                        $rFloodRow[$rType][] = $rPID;
                    }
                }
            } else {
                $rFloodRow = array('epg' => array(), 'playlist' => array());
            }
            $rAllow = false;
            if (count($rFloodRow[$rType]) >= $rFloodLimit) {
            } else {
                $rFloodRow[$rType][] = $rDownloadPID;
                $rAllow = true;
            }
            file_put_contents($rFile, json_encode($rFloodRow), LOCK_EX);
            return $rAllow;
        } else {
            return true;
        }
    } else {
        return true;
    }
}

function stopDownload($rType, $rUser, $rDownloadPID) {
    $rFloodLimit = 2;
    if ($rFloodLimit != 0) {
        if (!$rUser['is_restreamer']) {
            $rFile = FLOOD_TMP_PATH . $rUser['id'] . '_downloads';
            if (file_exists($rFile)) {
                $rFloodRow[$rType] = array();
                foreach (json_decode(file_get_contents($rFile), true)[$rType] as $rPID) {
                    if (!(ipTV_streaming::isProcessRunning($rPID, 'php-fpm') && $rPID != $rDownloadPID)) {
                    } else {
                        $rFloodRow[$rType][] = $rPID;
                    }
                }
            } else {
                $rFloodRow = array('epg' => array(), 'playlist' => array());
            }
            file_put_contents($rFile, json_encode($rFloodRow), LOCK_EX);
        } else {
            return null;
        }
    } else {
        return null;
    }
    /** 
     * Function to display the call stack, showing the sequence of function calls leading up to the current point in the code. 
     * 
     * This function retrieves the backtrace of the current execution point, excluding the current function call itself. 
     * It then iterates through the backtrace array, extracting information such as function name, file, and line number for each call. 
     * The extracted information is formatted and stored in an array, with each entry representing a function call in the call stack. 
     * Finally, the formatted call stack information is concatenated into a string with newline characters and returned. 
     * 
     * @return string The formatted call stack information as a string with each function call entry on a new line. 
     */
    function display_call_stack() {
        $trace = debug_backtrace();
        $stack = array();

        foreach ($trace as $index => $call) {
            if ($index === 0) {
                continue; // Skip the current function call
            }

            $function = isset($call['class']) ? $call['class'] . $call['type'] . $call['function'] : $call['function'];
            $file = isset($call['file']) ? $call['file'] : '[internal function]';
            $line = isset($call['line']) ? $call['line'] : '';

            $stack[] = sprintf("#%d %s(%s:%d)", $index, $function, $file, $line);
        }

        return implode("\n", $stack) . "\n";
    }
}
