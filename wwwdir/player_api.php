<?php

register_shutdown_function('shutdown');
require 'init.php';
set_time_limit(0);

$rDeny = true;
$rPanelAPI = false;

if (strtolower(explode('.', ltrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/'))[0]) == 'panel_api') {
    $rPanelAPI = true;
}

$rIP = $_SERVER['REMOTE_ADDR'];
$rUserAgent = trim($_SERVER['HTTP_USER_AGENT']);
$rOffset = (empty(ipTV_lib::$request['params']['offset']) ? 0 : abs(intval(ipTV_lib::$request['params']['offset'])));
$rLimit = (empty(ipTV_lib::$request['params']['items_per_page']) ? 0 : abs(intval(ipTV_lib::$request['params']['items_per_page'])));
$rNameTypes = array('live' => 'Live Streams', 'movie' => 'Movies', 'created_live' => 'Created Channels', 'radio_streams' => 'Radio Stations', 'series' => 'TV Series');
if (ipTV_lib::$settings['use_mdomain_in_lists'] == 1) {
    $rDomainName = ipTV_lib::$Servers[SERVER_ID]['site_url'];
} else {
    list($host, $act) = explode(':', $_SERVER['HTTP_HOST']);
    $rDomainName = ipTV_lib::$Servers[SERVER_ID]['server_protocol'] . '://' . $host . ':' . ipTV_lib::$Servers[SERVER_ID]['request_port'] . '/';
}
$rDomain = parse_url($rDomainName)['host'];
$rValidActions = array(200 => 'get_vod_categories', 201 => 'get_live_categories', 202 => 'get_live_streams', 203 => 'get_vod_streams', 204 => 'get_series_info', 205 => 'get_short_epg', 206 => 'get_series_categories', 207 => 'get_simple_data_table', 208 => 'get_series', 209 => 'get_vod_info');
$output = array();
$rAction = (!empty(ipTV_lib::$request['action']) && (in_array(ipTV_lib::$request['action'], $rValidActions) || array_key_exists(ipTV_lib::$request['action'], $rValidActions)) ? ipTV_lib::$request['action'] : '');

if (isset($rValidActions[$rAction])) {
    $rAction = $rValidActions[$rAction];
}

if ($rPanelAPI && empty($rAction)) {
    $rGetChannels = true;
} else {
    $rGetChannels = in_array($rAction, array('get_series', 'get_vod_streams', 'get_live_streams'));
}

$rExtract = array('offset' => $rOffset, 'items_per_page' => $rLimit);

if (isset(ipTV_lib::$request['username']) && isset(ipTV_lib::$request['password'])) {
    $rUsername = ipTV_lib::$request['username'];
    $rPassword = ipTV_lib::$request['password'];

    if (empty($rUsername) || empty($rPassword)) {
        generateError('NO_CREDENTIALS');
    }

    $rUserInfo = ipTV_streaming::getUserInfo(null, $rUsername, $rPassword, $rGetChannels);
} else {
    if (isset(ipTV_lib::$request['token'])) {
        $rToken = ipTV_lib::$request['token'];

        if (empty($rToken)) {
            generateError('NO_CREDENTIALS');
        }

        $rUserInfo = ipTV_streaming::getUserInfo(null, $rToken, null, $rGetChannels);
    }
}

ini_set('memory_limit', -1);

if ($rUserInfo) {
    $rDeny = false;
    $rValidUser = false;
    $mobile_apps = ipTV_lib::$settings['mobile_apps'];

    if ($rUserInfo['admin_enabled'] == 1 && $rUserInfo['enabled'] == 1 && (is_null($rUserInfo['exp_date']) || time() < $rUserInfo['exp_date'])) {
        $rValidUser = true;
    } else {
        if (!$rUserInfo['admin_enabled']) {
            generateError('BANNED');
        } else {
            if (!$rUserInfo['enabled']) {
                generateError('DISABLED');
            } else {
                generateError('EXPIRED');
            }
        }
    }

    checkAuthFlood($rUserInfo);
    header('Content-Type: application/json');

    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    }

    header('Access-Control-Allow-Credentials: true');

    switch ($rAction) {
        case 'get_series_info':
            $rSeriesID = (empty(ipTV_lib::$request['series_id']) ? 0 : intval(ipTV_lib::$request['series_id']));


            $ipTV_db->query('SELECT * FROM `streams_episodes` t1 INNER JOIN `streams` t2 ON t2.id=t1.stream_id WHERE t1.series_id = ? ORDER BY t1.season_num ASC, t1.episode_num ASC', $rSeriesID);
            $rRows = $ipTV_db->get_rows(true, 'season_num', false);
            $ipTV_db->query('SELECT * FROM `streams_series` WHERE `id` = ?', $rSeriesID);
            $rSeriesInfo = $ipTV_db->get_row();


            $output['seasons'] = array();

            foreach ((!empty($rSeriesInfo['seasons']) ? array_values(json_decode($rSeriesInfo['seasons'], true)) : array()) as $rSeason) {
                $rSeason['cover'] = $rSeason['cover'];
                $rSeason['cover_big'] = $rSeason['cover_big'];
                $output['seasons'][] = $rSeason;
            }
            $rBackdrops = json_decode($rSeriesInfo['backdrop_path'], true);

            if (count($rBackdrops) > 0) {
                foreach (range(0, count($rBackdrops) - 1) as $i) {
                    $rBackdrops[$i] = $rBackdrops[$i];
                }
            }

            $output['info'] = array('name' => $rSeriesInfo['title'], 'title' => $rSeriesInfo['title'], 'year' => $rSeriesInfo['year'], 'cover' => $rSeriesInfo['cover'], 'plot' => $rSeriesInfo['plot'], 'cast' => $rSeriesInfo['cast'], 'director' => $rSeriesInfo['director'], 'genre' => $rSeriesInfo['genre'], 'release_date' => $rSeriesInfo['release_date'], 'releaseDate' => $rSeriesInfo['release_date'], 'last_modified' => $rSeriesInfo['last_modified'], 'rating' => number_format($rSeriesInfo['rating'], 0), 'rating_5based' => number_format($rSeriesInfo['rating'] * 0.5, 1) + 0, 'backdrop_path' => $rBackdrops, 'youtube_trailer' => $rSeriesInfo['youtube_trailer'], 'episode_run_time' => $rSeriesInfo['episode_run_time'], 'category_id' => strval(json_decode($rSeriesInfo['category_id'], true)[0]), 'category_ids' => json_decode($rSeriesInfo['category_id'], true));

            foreach ($rRows as $rSeason => $rEpisodes) {
                $rNum = 1;

                foreach ($rEpisodes as $rEpisode) {

                    $rEpisodeData = $rEpisode;

                    $rProperties = (!empty($rEpisodeData['movie_properties']) ? json_decode($rEpisodeData['movie_properties'], true) : '');
                    $rProperties['cover_big'] = $rProperties['cover_big'];
                    $rProperties['movie_image'] = $rProperties['movie_image'];

                    if ($rProperties['cover_big']) {
                    } else {
                        $rProperties['cover_big'] = $rProperties['movie_image'];
                    }

                    if (count($rProperties['backdrop_path']) > 0) {
                        foreach (range(0, count($rProperties['backdrop_path']) - 1) as $i) {
                            if ($rProperties['backdrop_path'][$i]) {
                                $rProperties['backdrop_path'][$i] = $rProperties['backdrop_path'][$i];
                            }
                        }
                    }

                    $rSubtitles = array();

                    if (is_array($rProperties['subtitle'])) {
                        $i = 0;

                        foreach ($rProperties['subtitle'] as $rSubtitle) {
                            $rSubtitles[] = array('index' => $i, 'language' => ($rSubtitle['tags']['language'] ?: null), 'title' => ($rSubtitle['tags']['title'] ?: null));
                            $i++;
                        }
                    }

                    foreach (array('audio', 'video', 'subtitle') as $rKey) {
                        if (isset($rProperties[$rKey])) {
                            unset($rProperties[$rKey]);
                        }
                    }
                    $output['episodes'][$rSeason][] = array('id' => $rEpisode['stream_id'], 'episode_num' => $rEpisode['episode_num'], 'title' => $rEpisodeData['stream_display_name'], 'container_extension' => $rEpisodeData['target_container'], 'info' => $rProperties, 'subtitles' => $rSubtitles, 'custom_sid' => strval($rEpisodeData['custom_sid']), 'added' => ($rEpisodeData['added'] ?: ''), 'season' => $rSeason, 'direct_source' => "");
                }
            }

            break;

        case 'get_series':
            $rCategoryIDSearch = (empty(ipTV_lib::$request['category_id']) ? null : intval(ipTV_lib::$request['category_id']));
            $rMovieNum = 0;

            if (count($rUserInfo['series_ids']) > 0) {
                if (!empty($rUserInfo['series_ids'])) {
                    if (ipTV_lib::$settings['vod_sort_newest']) {
                        $ipTV_db->query('SELECT *, (SELECT MAX(`streams`.`added`) FROM `streams_episodes` LEFT JOIN `streams` ON `streams`.`id` = `streams_episodes`.`stream_id` WHERE `streams_episodes`.`series_id` = `streams_series`.`id`) AS `last_modified_stream` FROM `streams_series` WHERE `id` IN (' . implode(',', array_map('intval', $rUserInfo['series_ids'])) . ') ORDER BY `last_modified_stream` DESC, `last_modified` DESC;');
                    } else {
                        $ipTV_db->query('SELECT * FROM `streams_series` WHERE `id` IN (' . implode(',', array_map('intval', $rUserInfo['series_ids'])) . ') ORDER BY FIELD(`id`,' . implode(',', $rUserInfo['series_ids']) . ') ASC;');
                    }

                    $rSeries = $ipTV_db->get_rows(true, 'id');

                    foreach ($rSeries as $rSeriesItem) {
                        if (isset($rSeriesItem['last_modified_stream']) || !empty($rSeriesItem['last_modified_stream'])) {
                            $rSeriesItem['last_modified'] = $rSeriesItem['last_modified_stream'];
                        }

                        $rBackdrops = json_decode($rSeriesItem['backdrop_path'], true);

                        if (count($rBackdrops) > 0) {
                            foreach (range(0, count($rBackdrops) - 1) as $i) {
                                $rBackdrops[$i] = $rBackdrops[$i];
                            }
                        }

                        $rCategoryIDs = json_decode($rSeriesItem['category_id'], true);

                        foreach ($rCategoryIDs as $rCategoryID) {
                            if (empty($rCategoryIDSearch) || $rCategoryIDSearch == $rCategoryID) {
                                $output[] = array('num' => ++$rMovieNum, 'name' => $rSeriesItem['title'], 'title' => $rSeriesItem['title'], 'year' => $rSeriesItem['year'], 'stream_type' => 'series', 'series_id' => (int) $rSeriesItem['id'], 'cover' => $rSeriesItem['cover'], 'plot' => $rSeriesItem['plot'], 'cast' => $rSeriesItem['cast'], 'director' => $rSeriesItem['director'], 'genre' => $rSeriesItem['genre'], 'release_date' => $rSeriesItem['release_date'], 'releaseDate' => $rSeriesItem['release_date'], 'last_modified' => $rSeriesItem['last_modified'], 'rating' => number_format($rSeriesItem['rating'], 0), 'rating_5based' => number_format($rSeriesItem['rating'] * 0.5, 1) + 0, 'backdrop_path' => $rBackdrops, 'youtube_trailer' => $rSeriesItem['youtube_trailer'], 'episode_run_time' => $rSeriesItem['episode_run_time'], 'category_id' => strval($rCategoryID), 'category_ids' => $rCategoryIDs);
                            }

                            if (!$rCategoryIDSearch) {
                                break;
                            }
                        }
                    }
                }
            }

            break;

        case 'get_vod_categories':
            $rCategories = GetCategories('movie');

            foreach ($rCategories as $rCategory) {
                if (in_array($rCategory['id'], $rUserInfo['category_ids'])) {
                    $output[] = array('category_id' => strval($rCategory['id']), 'category_name' => $rCategory['category_name'], 'parent_id' => 0);
                }
            }

            break;

        case 'get_series_categories':
            $rCategories = GetCategories('series');

            foreach ($rCategories as $rCategory) {
                if (in_array($rCategory['id'], $rUserInfo['category_ids'])) {
                    $output[] = array('category_id' => strval($rCategory['id']), 'category_name' => $rCategory['category_name'], 'parent_id' => 0);
                }
            }

            break;

        case 'get_live_categories':
            $rCategories = array_merge(GetCategories('live'), GetCategories('radio'));

            foreach ($rCategories as $rCategory) {
                if (in_array($rCategory['id'], $rUserInfo['category_ids'])) {
                    $output[] = array('category_id' => strval($rCategory['id']), 'category_name' => $rCategory['category_name'], 'parent_id' => 0);
                }
            }

            break;

        case 'get_simple_data_table':
            $output['epg_listings'] = array();

            if (!empty(ipTV_lib::$request['stream_id'])) {
                if (is_numeric(ipTV_lib::$request['stream_id']) && !isset(ipTV_lib::$request['multi'])) {
                    $rMulti = false;
                    $rStreamIDs = array(intval(ipTV_lib::$request['stream_id']));
                } else {
                    $rMulti = true;
                    $rStreamIDs = array_map('intval', explode(',', ipTV_lib::$request['stream_id']));
                }

                if (count($rStreamIDs) > 0) {
                    $rArchiveInfo = array();
                    $ipTV_db->query('SELECT `id`, `tv_archive_duration` FROM `streams` WHERE `id` IN (' . implode(',', array_map('intval', $rStreamIDs)) . ');');

                    if ($ipTV_db->num_rows() > 0) {
                        foreach ($ipTV_db->get_rows() as $rRow) {
                            $rArchiveInfo[$rRow['id']] = intval($rRow['tv_archive_duration']);
                        }
                    }


                    foreach ($rStreamIDs as $rStreamID) {
                        if (file_exists(EPG_PATH . 'stream_' . intval($rStreamID))) {
                            $rRows = igbinary_unserialize(file_get_contents(EPG_PATH . 'stream_' . $rStreamID));

                            foreach ($rRows as $rEPGData) {
                                $rNowPlaying = $rHasArchive = 0;
                                $rEPGData['start_timestamp'] = $rEPGData['start'];
                                $rEPGData['stop_timestamp'] = $rEPGData['end'];

                                if ($rEPGData['start_timestamp'] <= time() && time() <= $rEPGData['stop_timestamp']) {
                                    $rNowPlaying = 1;
                                }

                                if (!empty($rArchiveInfo[$rStreamID]) && $rEPGData['stop_timestamp'] < time() && strtotime('-' . $rArchiveInfo[$rStreamID] . ' days') <= $rEPGData['stop_timestamp']) {
                                    $rHasArchive = 1;
                                }

                                $rEPGData['now_playing'] = $rNowPlaying;
                                $rEPGData['has_archive'] = $rHasArchive;
                                $rEPGData['title'] = base64_encode($rEPGData['title']);
                                $rEPGData['description'] = base64_encode($rEPGData['description']);
                                $rEPGData['start'] = date('Y-m-d H:i:s', $rEPGData['start_timestamp']);
                                $rEPGData['end'] = date('Y-m-d H:i:s', $rEPGData['stop_timestamp']);

                                if ($rMulti) {
                                    $output['epg_listings'][$rStreamID][] = $rEPGData;
                                } else {
                                    $output['epg_listings'][] = $rEPGData;
                                }
                            }
                        }
                    }
                }
            }

            break;

        case 'get_short_epg':
            $output['epg_listings'] = array();

            if (!empty(ipTV_lib::$request['stream_id'])) {
                $rLimit = (empty(ipTV_lib::$request['limit']) ? 4 : intval(ipTV_lib::$request['limit']));

                if (is_numeric(ipTV_lib::$request['stream_id']) && !isset(ipTV_lib::$request['multi'])) {
                    $rMulti = false;
                    $rStreamIDs = array(intval(ipTV_lib::$request['stream_id']));
                } else {
                    $rMulti = true;
                    $rStreamIDs = array_map('intval', explode(',', ipTV_lib::$request['stream_id']));
                }

                if (count($rStreamIDs) > 0) {
                    $rTime = time();

                    foreach ($rStreamIDs as $rStreamID) {
                        if (file_exists(EPG_PATH . 'stream_' . intval($rStreamID))) {
                            $rRows = igbinary_unserialize(file_get_contents(EPG_PATH . 'stream_' . $rStreamID));

                            foreach ($rRows as $rRow) {
                                if ($rRow['start'] <= $rTime && $rTime <= $rRow['end'] || $rTime <= $rRow['start']) {
                                    $rRow['start_timestamp'] = $rRow['start'];
                                    $rRow['stop_timestamp'] = $rRow['end'];
                                    $rRow['title'] = base64_encode($rRow['title']);
                                    $rRow['description'] = base64_encode($rRow['description']);
                                    $rRow['start'] = date('Y-m-d H:i:s', $rRow['start']);


                                    $rRow['stop'] = date('Y-m-d H:i:s', $rRow['end']);

                                    if ($rMulti) {
                                        $output['epg_listings'][$rStreamID][] = $rRow;
                                    } else {
                                        $output['epg_listings'][] = $rRow;
                                    }

                                    if ($rLimit < count($output['epg_listings'])) {
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            break;

        case 'get_live_streams':
            $rCategoryIDSearch = (empty(ipTV_lib::$request['category_id']) ? null : intval(ipTV_lib::$request['category_id']));
            $rLiveNum = 0;
            $rUserInfo['live_ids'] = array_merge($rUserInfo['live_ids'], $rUserInfo['radio_ids']);

            if (!empty($rExtract['items_per_page'])) {
                $rUserInfo['live_ids'] = array_slice($rUserInfo['live_ids'], $rExtract['offset'], $rExtract['items_per_page']);
            }

            $rUserInfo['live_ids'] = ipTV_lib::sortChannels($rUserInfo['live_ids']);
            $rChannels = array();

            if (count($rUserInfo['live_ids']) > 0) {
                $rWhereV = $rWhere = array();

                if (!empty($rCategoryIDSearch)) {
                    $rWhere[] = "JSON_CONTAINS(`category_id`, ?, '\$')";
                    $rWhereV[] = $rCategoryIDSearch;
                }

                $rWhere[] = '`t1`.`id` IN (' . implode(',', $rUserInfo['live_ids']) . ')';
                $rWhereString = 'WHERE ' . implode(' AND ', $rWhere);

                if (ipTV_lib::$settings['channel_number_type'] != 'manual') {
                    $rOrder = 'FIELD(`t1`.`id`,' . implode(',', $rUserInfo['live_ids']) . ')';
                } else {
                    $rOrder = '`order`';
                }

                $ipTV_db->query('SELECT t1.id,t1.epg_id,t1.added,t1.allow_record,t1.channel_id,t1.stream_source,t1.tv_archive_server_id,t1.tv_archive_duration,t1.stream_icon,t1.custom_sid,t1.category_id,t1.stream_display_name,t1.series_no,t1.direct_source,t2.type_output,t1.target_container,t2.live,t1.rtmp_output,t1.order,t2.type_key FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type ' . $rWhereString . ' ORDER BY ' . $rOrder . ';', ...$rWhereV);
                $rChannels = $ipTV_db->get_rows();
            }

            foreach ($rChannels as $rChannel) {
                if (in_array($rChannel['type_key'], array('live', 'created_live', 'radio_streams'))) {
                    $rCategoryIDs = json_decode($rChannel['category_id'], true);
                    if (count($rCategoryIDs) > 0) {
                        foreach ($rCategoryIDs as $rCategoryID) {
                            if (empty($rCategoryIDSearch) || $rCategoryIDSearch == $rCategoryID) {
                                $rStreamIcon = ($rChannel['stream_icon']) ?: '';
                                $rTVArchive = (!empty($rChannel['tv_archive_server_id']) && !empty($rChannel['tv_archive_duration']) ? 1 : 0);
                                $output[] = array('num' => ++$rLiveNum, 'name' => $rChannel['stream_display_name'], 'stream_type' => $rChannel['type_key'], 'stream_id' => (int) $rChannel['id'], 'stream_icon' => $rStreamIcon, 'epg_channel_id' => $rChannel['channel_id'], 'added' => ($rChannel['added'] ?: ''), 'custom_sid' => strval($rChannel['custom_sid']), 'tv_archive' => $rTVArchive, 'direct_source' => '', 'tv_archive_duration' => ($rTVArchive ? intval($rChannel['tv_archive_duration']) : 0), 'category_id' => strval($rCategoryID), 'category_ids' => $rCategoryIDs, 'thumbnail' => "");
                            }

                            if ($rCategoryIDSearch) {
                                break;
                            }
                        }
                    } else {
                        $output[] = array('num' => ++$rLiveNum, 'name' => $rChannel['stream_display_name'], 'stream_type' => $rChannel['type_key'], 'stream_id' => (int) $rChannel['id'], 'stream_icon' => $rStreamIcon, 'epg_channel_id' => $rChannel['channel_id'], 'added' => ($rChannel['added'] ?: ''), 'custom_sid' => strval($rChannel['custom_sid']), 'tv_archive' => $rTVArchive, 'direct_source' => '', 'tv_archive_duration' => ($rTVArchive ? intval($rChannel['tv_archive_duration']) : 0), 'category_id' => '');
                    }
                }
            }

            break;

        case 'get_vod_info':
            $output['info'] = array();

            if (!empty(ipTV_lib::$request['vod_id'])) {
                $rVODID = intval(ipTV_lib::$request['vod_id']);

                $ipTV_db->query('SELECT * FROM `streams` WHERE `id` = ?', $rVODID);
                $rRow = $ipTV_db->get_row();

                if ($rRow) {

                    $output['info'] = json_decode($rRow['movie_properties'], true);
                    $output['info']['tmdb_id'] = intval($output['info']['tmdb_id']);
                    $output['info']['episode_run_time'] = intval($output['info']['episode_run_time']);
                    $output['info']['releasedate'] = $output['info']['release_date'];
                    $output['info']['cover_big'] = $output['info']['cover_big'];
                    $output['info']['movie_image'] = $output['info']['movie_image'];
                    $output['info']['rating'] = number_format($output['info']['rating'], 2) + 0;

                    if (count($output['info']['backdrop_path']) > 0) {
                        foreach (range(0, count($output['info']['backdrop_path']) - 1) as $i) {
                            $output['info']['backdrop_path'][$i] = $output['info']['backdrop_path'][$i];
                        }
                    }

                    $output['info']['subtitles'] = array();

                    if (is_array($output['info']['subtitle'])) {
                        $i = 0;

                        foreach ($output['info']['subtitle'] as $rSubtitle) {
                            $output['info']['subtitles'][] = array('index' => $i, 'language' => ($rSubtitle['tags']['language'] ?: null), 'title' => ($rSubtitle['tags']['title'] ?: null));
                            $i++;
                        }
                    }

                    foreach (array('audio', 'video', 'subtitle') as $rKey) {
                        if (isset($output['info'][$rKey])) {
                            unset($output['info'][$rKey]);
                        }
                    }
                    $output['movie_data'] = array('stream_id' => (int) $rRow['id'], 'name' => $rRow['stream_display_name'], 'title' => $rRow['stream_display_name'], 'year' => $rRow['year'], 'added' => ($rRow['added'] ?: ''), 'category_id' => strval(json_decode($rRow['category_id'], true)[0]), 'category_ids' => json_decode($rRow['category_id'], true), 'container_extension' => $rRow['target_container'], 'custom_sid' => strval($rRow['custom_sid']), 'direct_source' => '');
                }
            }

            break;

        case 'get_vod_streams':
            $rCategoryIDSearch = (empty(ipTV_lib::$request['category_id']) ? null : intval(ipTV_lib::$request['category_id']));
            $rMovieNum = 0;

            if (!empty($rExtract['items_per_page'])) {
                $rUserInfo['vod_ids'] = array_slice($rUserInfo['vod_ids'], $rExtract['offset'], $rExtract['items_per_page']);
            }

            $rUserInfo['vod_ids'] = ipTV_lib::sortChannels($rUserInfo['vod_ids']);

            $rChannels = array();

            if (count($rUserInfo['vod_ids']) > 0) {
                $rWhereV = $rWhere = array();

                if (!empty($rCategoryIDSearch)) {
                    $rWhere[] = "JSON_CONTAINS(`category_id`, ?, '\$')";
                    $rWhereV[] = $rCategoryIDSearch;
                }

                $rWhere[] = '`t1`.`id` IN (' . implode(',', $rUserInfo['vod_ids']) . ')';
                $rWhereString = 'WHERE ' . implode(' AND ', $rWhere);

                if (ipTV_lib::$settings['channel_number_type'] != 'manual') {
                    $rOrder = 'FIELD(`t1`.`id`,' . implode(',', $rUserInfo['vod_ids']) . ')';
                } else {
                    $rOrder = '`order`';
                }

                $ipTV_db->query('SELECT t1.id,t1.epg_id,t1.added,t1.allow_record,t1.channel_id,t1.stream_source,t1.tv_archive_server_id,t1.tv_archive_duration,t1.stream_icon,t1.custom_sid,t1.category_id,t1.stream_display_name,t1.series_no,t1.direct_source,t2.type_output,t1.target_container,t2.live,t1.rtmp_output,t1.order,t2.type_key FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type ' . $rWhereString . ' ORDER BY ' . $rOrder . ';', ...$rWhereV);
                $rChannels = $ipTV_db->get_rows();
            }

            foreach ($rChannels as $rChannel) {
                if (in_array($rChannel['type_key'], array('movie'))) {
                    $rProperties = json_decode($rChannel['movie_properties'], true);
                    $rCategoryIDs = json_decode($rChannel['category_id'], true);
                    if (count($rCategoryIDs) > 0) {
                        foreach ($rCategoryIDs as $rCategoryID) {
                            if (empty($rCategoryIDSearch) || $rCategoryIDSearch == $rCategoryID) {
                                $output[] = array('num' => ++$rMovieNum, 'name' => $rChannel['stream_display_name'], 'title' => $rChannel['stream_display_name'], 'year' => $rChannel['year'], 'stream_type' => $rChannel['type_key'], 'stream_id' => (int) $rChannel['id'], 'stream_icon' => ($rProperties['movie_image']) ?: '', 'rating' => number_format($rProperties['rating'], 1) + 0, 'rating_5based' => number_format($rProperties['rating'] * 0.5, 1) + 0, 'added' => ($rChannel['added'] ?: ''), 'plot' => $rProperties['plot'], 'cast' => $rProperties['cast'], 'director' => $rProperties['director'], 'genre' => $rProperties['genre'], 'release_date' => $rProperties['release_date'], 'youtube_trailer' => $rProperties['youtube_trailer'], 'episode_run_time' => $rProperties['episode_run_time'], 'category_id' => strval($rCategoryID), 'category_ids' => $rCategoryIDs, 'container_extension' => $rChannel['target_container'], 'custom_sid' => strval($rChannel['custom_sid']), 'direct_source' => "");
                            }

                            if (!$rCategoryIDSearch) {
                                break;
                            }
                        }
                    } else {
                        $output[] = array('num' => ++$rMovieNum, 'name' => $rChannel['stream_display_name'], 'title' => $rChannel['stream_display_name'], 'year' => $rChannel['year'], 'stream_type' => $rChannel['type_key'], 'stream_id' => (int) $rChannel['id'], 'stream_icon' => ($rProperties['movie_image']) ?: '', 'rating' => number_format($rProperties['rating'], 1) + 0, 'rating_5based' => number_format($rProperties['rating'] * 0.5, 1) + 0, 'added' => ($rChannel['added'] ?: ''), 'plot' => $rProperties['plot'], 'cast' => $rProperties['cast'], 'director' => $rProperties['director'], 'genre' => $rProperties['genre'], 'release_date' => $rProperties['release_date'], 'youtube_trailer' => $rProperties['youtube_trailer'], 'episode_run_time' => $rProperties['episode_run_time'], 'category_id' => "", 'category_ids' => [], 'container_extension' => $rChannel['target_container'], 'custom_sid' => strval($rChannel['custom_sid']), 'direct_source' => "");
                    }
                }
            }

            break;

        default:
            $output['user_info'] = array();
            $url = empty(ipTV_lib::$Servers[SERVER_ID]['domain_name']) ? ipTV_lib::$Servers[SERVER_ID]['server_ip'] : ipTV_lib::$Servers[SERVER_ID]['domain_name'];
            $output['server_info'] = array('url' => $url, 'port' => ipTV_lib::$Servers[SERVER_ID]['http_broadcast_port'], 'https_port' => ipTV_lib::$Servers[SERVER_ID]['https_broadcast_port'], 'server_protocol' => ipTV_lib::$Servers[SERVER_ID]['server_protocol'], 'rtmp_port' => ipTV_lib::$Servers[SERVER_ID]['rtmp_port'], 'timezone' => ipTV_lib::$settings['default_timezone'], 'timestamp_now' => time(), 'time_now' => date('Y-m-d H:i:s'));
            if ($mobile_apps == 1) {
                $output['server_info']['process'] = true;
            }
            $output['user_info']['username'] = $rUserInfo['username'];
            $output['user_info']['password'] = $rUserInfo['password'];
            $output['user_info']['message'] = ipTV_lib::$settings['message_of_day'];
            $output['user_info']['auth'] = 1;
            if ($rUserInfo['admin_enabled'] == 0) {
                $output['user_info']['status'] = 'Banned';
            } else if ($rUserInfo['enabled'] == 0) {
                $output['user_info']['status'] = 'Disabled';
            } else if (is_null($rUserInfo['exp_date']) or $rUserInfo['exp_date'] > time()) {
                $output['user_info']['status'] = 'Active';
            } else {
                $output['user_info']['status'] = 'Expired';
            }
            $output['user_info']['exp_date'] = $rUserInfo['exp_date'];
            $output['user_info']['is_trial'] = $rUserInfo['is_trial'];
            $output['user_info']['active_cons'] = $rUserInfo['active_cons'];
            $output['user_info']['created_at'] = $rUserInfo['created_at'];
            $output['user_info']['max_connections'] = $rUserInfo['max_connections'];
            $output['user_info']['allowed_output_formats'] = array_keys($rUserInfo['output_formats']);
    }
    die(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
} else {
    checkBruteforce(null, null, $rUsername);
    generateError('INVALID_CREDENTIALS');
}

function getOutputFormats($rFormats) {
    $rFormatArray = array(1 => 'm3u8', 2 => 'ts', 3 => 'rtmp');
    $rReturn = array();

    foreach ($rFormats as $rFormat) {
        $rReturn[] = $rFormatArray[$rFormat];
    }

    return $rReturn;
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
