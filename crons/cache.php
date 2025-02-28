<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        ini_set('memory_limit', -1);
        $rStartup = false;
        if (count($argv) == 2) {
            $rStartup = true;
        }
        cli_set_process_title('XC_VM[Cache Builder]');
        $unique_id = CRONS_TMP_PATH . md5(generateUniqueCode() . __FILE__);
        ipTV_lib::checkCron($unique_id);
        loadCron();
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
function loadCron() {
    global $ipTV_db;
    global $rStartup;
    if (defined('CACHE_TMP_PATH')) {
        if ($rStartup && file_exists(CACHE_TMP_PATH . 'settings')) {
            echo 'Checking cache readability...' . "\n";
            $rSerialize = igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'settings'));
            if (!is_array($rSerialize) && !isset($rSerialize['server_name'])) {
                echo 'Clearing cache...' . "\n\n";
                foreach (array(STREAMS_TMP_PATH, USER_TMP_PATH, SERIES_TMP_PATH) as $rTmpPath) {
                    foreach (scandir($rTmpPath) as $rFile) {
                        unlink($rTmpPath . $rFile);
                    }
                }
                exec('sudo rm -rf ' . TMP_PATH . '*');
                exec('sudo rm -rf ' . SIGNALS_PATH . '*');
            }
        }
        foreach (array(EPG_PATH, VOD_PATH, ARCHIVE_PATH, CREATED_PATH, DELAY_PATH, VIDEO_PATH, PLAYLIST_PATH, CONS_TMP_PATH, CRONS_TMP_PATH, CACHE_TMP_PATH, DIVERGENCE_TMP_PATH, FLOOD_TMP_PATH, STALKER_TMP_PATH, SIGNALS_TMP_PATH, LOGS_TMP_PATH, CIDR_TMP_PATH, STREAMS_TMP_PATH, USER_TMP_PATH, SERIES_TMP_PATH) as $rPath) {
            if (!file_exists($rPath)) {
                mkdir($rPath);
            }
        }
        ipTV_lib::setCache('settings', ipTV_lib::getSettings(true));
        ipTV_lib::setCache('bouquets', ipTV_lib::getBouquets(true));
        $rServers = ipTV_lib::getServers(true);
        unset($rServers['php_pids']);
        ipTV_lib::setCache('servers', $rServers);
        ipTV_lib::setCache('blocked_ua', ipTV_lib::getBlockedUserAgents(true));
        ipTV_lib::setCache('customisp', ipTV_lib::getIspAddon(true));
        ipTV_lib::setCache('blocked_isp', ipTV_lib::getBlockedISP(true));
        ipTV_lib::setCache('blocked_ips', ipTV_lib::getBlockedIPs(true));
        ipTV_lib::setCache('allowed_ips', ipTV_lib::getAllowedIPs(true));
        ipTV_lib::setCache('categories', ipTV_lib::getCategories(null, true));

        if (ipTV_lib::$Servers[SERVER_ID]['is_main']) {
            $rOutputFormats = array();
            $ipTV_db->query('SELECT `access_output_id`, `output_key` FROM `access_output`;');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rOutputFormats[] = $rRow;
            }
            file_put_contents(CACHE_TMP_PATH . 'access_output', igbinary_serialize($rOutputFormats));
            $rRTMPIPs = array();
            $ipTV_db->query('SELECT `ip`, `password`, `push`, `pull` FROM `rtmp_ips`');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rRTMPIPs[gethostbyname($rRow['ip'])] = array('password' => $rRow['password'], 'push' => boolval($rRow['push']), 'pull' => boolval($rRow['pull']));
            }
            file_put_contents(CACHE_TMP_PATH . 'rtmp_ips', igbinary_serialize($rRTMPIPs));
            if (file_exists(BIN_PATH . 'maxmind/cidr.db')) {
                exec('ls ' . CIDR_TMP_PATH . ' | wc -l', $rOutput);
                if (intval($rOutput[0]) == 0) {
                    $rDatabase = json_decode(file_get_contents(BIN_PATH . 'maxmind/cidr.db'), true);
                    foreach ($rDatabase as $rASN => $rData) {
                        file_put_contents(CIDR_TMP_PATH . $rASN, json_encode($rData));
                    }
                }
            }
            $rChannelOrder = array();
            if (ipTV_lib::$settings['channel_number_type'] == 'manual') {
                $ipTV_db->query('SELECT `id`, `order` FROM `streams` ORDER BY `order` ASC;');
                foreach ($ipTV_db->get_rows() as $rRow) {
                    $rChannelOrder[] = intval($rRow['id']);
                }
            }
            $rCategoryMap = array();
            $rBouquetMap = array();
            $rStreamIDs = array('channels' => array(), 'radios' => array(), 'movies' => array(), 'episodes' => array(), 'series' => array());
            $ipTV_db->query('SELECT *, IF(`bouquet_order` > 0, `bouquet_order`, 999) AS `order` FROM `bouquets` ORDER BY `order` ASC;');
            foreach ($ipTV_db->get_rows(true, 'id') as $rID => $rChannels) {
                $rAllowedCategories = array();
                $rAllChannels = array();
                foreach (json_decode($rChannels['bouquet_channels'], true) as $rStreamID) {
                    if (intval($rStreamID) > 0 || !in_array($rStreamID, $rStreamIDs['channels'])) {
                        $rStreamIDs['channels'][] = $rStreamID;
                    }
                    if (!isset($rBouquetMap[intval($rStreamID)])) {
                        $rBouquetMap[intval($rStreamID)] = array();
                    }
                    $rBouquetMap[intval($rStreamID)][] = $rID;
                }
                foreach (json_decode($rChannels['bouquet_radios'], true) as $rStreamID) {
                    if (intval($rStreamID) > 0 || !in_array($rStreamID, $rStreamIDs['radios'])) {
                        $rStreamIDs['radios'][] = $rStreamID;
                    }
                    if (!isset($rBouquetMap[intval($rStreamID)])) {
                        $rBouquetMap[intval($rStreamID)] = array();
                    }
                    $rBouquetMap[intval($rStreamID)][] = $rID;
                }
                foreach (json_decode($rChannels['bouquet_movies'], true) as $rStreamID) {
                    if (intval($rStreamID) > 0 || !in_array($rStreamID, $rStreamIDs['movies'])) {
                        $rStreamIDs['movies'][] = $rStreamID;
                    }
                    if (!isset($rBouquetMap[intval($rStreamID)])) {
                        $rBouquetMap[intval($rStreamID)] = array();
                    }
                    $rBouquetMap[intval($rStreamID)][] = $rID;
                }
                foreach (json_decode($rChannels['bouquet_series'], true) as $rSeriesID) {
                    if (intval($rSeriesID) > 0 || !in_array($rSeriesID, $rStreamIDs['series'])) {
                        $ipTV_db->query('SELECT `stream_id` FROM `streams_episodes` WHERE `series_id` = ? ORDER BY `season_num` ASC, `episode_num` ASC;', $rSeriesID);
                        foreach ($ipTV_db->get_rows() as $rEpisode) {
                            if (intval($rEpisode['stream_id']) > 0) {
                                $rStreamIDs['episodes'][] = $rEpisode['stream_id'];
                            }
                            if (!isset($rBouquetMap[intval($rEpisode['stream_id'])])) {
                                $rBouquetMap[intval($rEpisode['stream_id'])] = array();
                            }
                            $rBouquetMap[intval($rEpisode['stream_id'])][] = $rID;
                        }
                    }
                }
                $rAllChannels = array_map('intval', array_unique(array_merge((json_decode($rChannels['bouquet_channels'], true) ?: array()), (json_decode($rChannels['bouquet_radios'], true) ?: array()), (json_decode($rChannels['bouquet_movies'], true) ?: array()))));
                $rAllSeries = array_map('intval', array_unique((json_decode($rChannels['bouquet_series'], true) ?: array())));
                if (count($rAllChannels) > 0) {
                    $ipTV_db->query('SELECT DISTINCT(`category_id`) AS `category_id` FROM `streams` WHERE `id` IN (' . implode(',', $rAllChannels) . ');');
                    foreach ($ipTV_db->get_rows() as $rRow) {
                        $rAllowedCategories = array_merge($rAllowedCategories, (json_decode($rRow['category_id'], true) ?: array()));
                    }
                }
                if (count($rAllSeries) > 0) {
                    $ipTV_db->query('SELECT DISTINCT(`category_id`) AS `category_id` FROM `streams_series` WHERE `id` IN (' . implode(',', $rAllSeries) . ');');
                    foreach ($ipTV_db->get_rows() as $rRow) {
                        $rAllowedCategories = array_merge($rAllowedCategories, (json_decode($rRow['category_id'], true) ?: array()));
                    }
                }
                $rCategoryMap[$rID] = array_unique($rAllowedCategories);
            }
            if (ipTV_lib::$settings['channel_number_type'] != 'manual') {
                foreach (array('channels', 'radios', 'movies', 'episodes') as $rKey) {
                    if (0 < count($rStreamIDs[$rKey])) {
                        $rWhere = 'AND `id` NOT IN (' . implode(',', array_map('intval', $rStreamIDs[$rKey])) . ')';
                    } else {
                        $rWhere = '';
                    }
                    switch ($rKey) {
                        case 'channels':
                            $rType = array(1, 3);
                            break;
                        case 'radios':
                            $rType = array(4);
                            break;
                        case 'movies':
                            $rType = array(2);
                            break;
                        case 'episodes':
                            $rType = array(5);
                            break;
                    }
                    if (count($rType) > 0) {
                        $ipTV_db->query('SELECT `id` FROM `streams` WHERE `type` IN (' . implode(',', $rType) . ') ' . $rWhere . ' ORDER BY `order` ASC;');
                        foreach ($ipTV_db->get_rows() as $rRow) {
                            $rStreamIDs[$rKey][] = $rRow['id'];
                        }
                    }
                }
                // if (ipTV_lib::$settings['vod_sort_newest']) {
                //     $rStreamIDs['movies'] = array();
                //     $rStreamIDs['episodes'] = array();
                //     $ipTV_db->query('SELECT `type`, `id` FROM `streams` WHERE `type` IN (2,5) ORDER BY `added` DESC, `id` DESC;');
                //     foreach ($ipTV_db->get_rows() as $rRow) {
                //         $rStreamIDs[array(2 => 'movies', 5 => 'episodes')[$rRow['type']]][] = $rRow['id'];
                //     }
                //     $rSeriesOrder = array();
                //     $ipTV_db->query('SELECT `id`, (SELECT MAX(`streams`.`added`) FROM `streams_episodes` LEFT JOIN `streams` ON `streams`.`id` = `streams_episodes`.`stream_id` WHERE `streams_episodes`.`series_id` = `streams_series`.`id`) AS `last_modified_stream` FROM `streams_series` ORDER BY `last_modified_stream` DESC, `last_modified` DESC, `id` DESC;');
                //     foreach ($ipTV_db->get_rows() as $rRow) {
                //         $rSeriesOrder[] = intval($rRow['id']);
                //     }
                //     file_put_contents(CACHE_TMP_PATH . 'series_order', igbinary_serialize($rSeriesOrder));
                // }
                foreach (array('channels', 'radios', 'movies', 'episodes') as $rKey) {
                    foreach ($rStreamIDs[$rKey] as $rStreamID) {
                        $rChannelOrder[] = intval($rStreamID);
                    }
                }
                $rChannelOrder = array_unique($rChannelOrder);
            }
            $rCategoryChannels = array();
            $ipTV_db->query('SELECT `id`, `category_id` FROM `streams`;');
            if ($ipTV_db->dbh && $ipTV_db->result) {
                if ($ipTV_db->num_rows() > 0) {
                    foreach ($ipTV_db->get_rows() as $rStreamInfo) {
                        $rCategoryChannels[$rStreamInfo['id']] = json_decode($rStreamInfo['category_id'], true);
                    }
                }
            }
            $rResellerDomains = array();
            $ipTV_db->query('SELECT `reseller_dns` FROM `reg_users` WHERE `status` = 1 AND `reseller_dns` IS NOT NULL;');
            foreach ($ipTV_db->get_rows() as $rRow) {
                $rResellerDomains[] = strtolower($rRow['reseller_dns']);
            }
            file_put_contents(CACHE_TMP_PATH . 'reseller_domains', igbinary_serialize($rResellerDomains));
            file_put_contents(CACHE_TMP_PATH . 'channel_order', igbinary_serialize($rChannelOrder));
            file_put_contents(CACHE_TMP_PATH . 'bouquet_map', igbinary_serialize($rBouquetMap));
            file_put_contents(CACHE_TMP_PATH . 'category_map', igbinary_serialize($rCategoryMap));
            file_put_contents(STREAMS_TMP_PATH . 'channels_categories', igbinary_serialize($rCategoryChannels));
        }
    } else {
        exit();
    }
}
function shutdown() {
    global $ipTV_db;
    global $unique_id;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
    @unlink($unique_id);
}
