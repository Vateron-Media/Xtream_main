<?php

register_shutdown_function('shutdown');
require 'init.php';
set_time_limit(0);
ini_set('memory_limit', '-1');

$rDeny = true;
$rRequestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rPanelAPI = stripos(ltrim($rRequestPath, '/'), 'panel_api') === 0;

// Securely Fetch and Sanitize Inputs
$rIP = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: '0.0.0.0';
$rUserAgent = trim($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
$rOffset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
$rLimit = filter_input(INPUT_GET, 'items_per_page', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);

// Define Content Categories
$rNameTypes = [
    'live'           => 'Live Streams',
    'movie'          => 'Movies',
    'created_live'   => 'Created Channels',
    'radio_streams'  => 'Radio Stations',
    'series'         => 'TV Series'
];

$rDomain = parse_url(getDomainName(), PHP_URL_HOST);
$rValidActions = [
    200 => 'get_vod_categories',
    201 => 'get_live_categories',
    202 => 'get_live_streams',
    203 => 'get_vod_streams',
    204 => 'get_series_info',
    205 => 'get_short_epg',
    206 => 'get_series_categories',
    207 => 'get_simple_data_table',
    208 => 'get_series',
    209 => 'get_vod_info'
];

// Determine Requested Action
$output = [];
$rAction = ipTV_lib::$request['action'] ?? '';
$rAction = $rValidActions[$rAction] ?? $rAction;

// Check if Channels Should be Retrieved
$rGetChannels = $rPanelAPI && empty($rAction) || in_array($rAction, ['get_series', 'get_vod_streams', 'get_live_streams'], true);

// Extract User Credentials Securely
$rUsername = ipTV_lib::$request['username'] ?? null;
$rPassword = ipTV_lib::$request['password'] ?? null;
$rToken = ipTV_lib::$request['token'] ?? null;

if (!empty($rUsername) && !empty($rPassword)) {
    $rUserInfo = ipTV_streaming::getUserInfo(null, $rUsername, $rPassword, $rGetChannels);
} elseif (!empty($rToken)) {
    $rUserInfo = ipTV_streaming::getUserInfo(null, $rToken, null, $rGetChannels);
} else {
    generateError('NO_CREDENTIALS');
}

// Validate User Authentication
if ($rUserInfo) {
    $rDeny = false;
    $rValidUser = false;

    // User Access Validation
    if (!$rUserInfo['admin_enabled']) {
        generateError('BANNED');
    } elseif (!$rUserInfo['enabled']) {
        generateError('DISABLED');
    } elseif (!is_null($rUserInfo['exp_date']) && time() >= $rUserInfo['exp_date']) {
        generateError('EXPIRED');
    } else {
        $rValidUser = true;
    }

    // Prevent Brute Force Attacks
    checkAuthFlood($rUserInfo);

    // Set JSON Response Headers
    header('Content-Type: application/json');
    header('Vary: Origin'); // Proper CORS Handling

    // Securely Handle CORS Headers
    if (!empty($_SERVER['HTTP_ORIGIN']) && filter_var($_SERVER['HTTP_ORIGIN'], FILTER_VALIDATE_URL)) {
        header('Access-Control-Allow-Origin: ' . htmlentities($_SERVER['HTTP_ORIGIN']));
    }

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Max-Age: 86400'); // Caches Preflight for 24 hours

    // Process Requested Action
    switch ($rAction) {

        case 'get_series_info':
            $rSeriesID = intval(ipTV_lib::$request['series_id'] ?? 0);

            // Fetch Series Episodes
            $ipTV_db->query(
                'SELECT * FROM `streams_episodes` t1 
                INNER JOIN `streams` t2 ON t2.id = t1.stream_id 
                WHERE t1.series_id = ? 
                ORDER BY t1.season_num ASC, t1.episode_num ASC', 
                $rSeriesID
            );
            $rRows = $ipTV_db->get_rows(true, 'season_num', false);

            // Fetch Series Info
            $ipTV_db->query('SELECT * FROM `streams_series` WHERE `id` = ?', $rSeriesID);
            $rSeriesInfo = $ipTV_db->get_row();

            if (!$rSeriesInfo) {
                generateError('SERIES_NOT_FOUND');
                break;
            }

            // Process Seasons
            $output['seasons'] = !empty($rSeriesInfo['seasons']) 
                ? array_values(json_decode($rSeriesInfo['seasons'], true)) 
                : [];

            // Process Backdrops
            $output['info'] = [
                'name'             => $rSeriesInfo['title'],
                'title'            => $rSeriesInfo['title'],
                'year'             => $rSeriesInfo['year'],
                'cover'            => $rSeriesInfo['cover'],
                'plot'             => $rSeriesInfo['plot'],
                'cast'             => $rSeriesInfo['cast'],
                'director'         => $rSeriesInfo['director'],
                'genre'            => $rSeriesInfo['genre'],
                'release_date'     => $rSeriesInfo['release_date'],
                'releaseDate'      => $rSeriesInfo['release_date'],
                'last_modified'    => $rSeriesInfo['last_modified'],
                'rating'           => number_format((float) $rSeriesInfo['rating'], 0),
                'rating_5based'    => number_format((float) $rSeriesInfo['rating'] * 0.5, 1),
                'backdrop_path'    => json_decode($rSeriesInfo['backdrop_path'], true) ?: [],
                'youtube_trailer'  => $rSeriesInfo['youtube_trailer'],
                'episode_run_time' => $rSeriesInfo['episode_run_time'],
                'category_id'      => strval(json_decode($rSeriesInfo['category_id'], true)[0] ?? ''),
                'category_ids'     => json_decode($rSeriesInfo['category_id'], true) ?: []
            ];

            // Process Episodes
            foreach ($rRows as $rSeason => $rEpisodes) {
                foreach ($rEpisodes as $rEpisode) {
                    $rProperties = !empty($rEpisode['movie_properties']) 
                        ? json_decode($rEpisode['movie_properties'], true) 
                        : [];

                    $rProperties['cover_big'] = $rProperties['cover_big'] ?? $rProperties['movie_image'] ?? '';

                    // Process Subtitles
                    $rSubtitles = [];
                    if (!empty($rProperties['subtitle']) && is_array($rProperties['subtitle'])) {
                        $rSubtitles = array_map(function ($index, $sub) {
                            return [
                                'index'    => $index,
                                'language' => $sub['tags']['language'] ?? null,
                                'title'    => $sub['tags']['title'] ?? null
                            ];
                        }, array_keys($rProperties['subtitle']), $rProperties['subtitle']);
                    }

                    // Remove unnecessary keys
                    foreach (['audio', 'video', 'subtitle'] as $rKey) {
                        unset($rProperties[$rKey]);
                    }

                    // Add Episode Data
                    $output['episodes'][$rSeason][] = [
                        'id'                 => $rEpisode['stream_id'],
                        'episode_num'        => $rEpisode['episode_num'],
                        'title'              => $rEpisode['stream_display_name'],
                        'container_extension'=> $rEpisode['target_container'],
                        'info'               => $rProperties,
                        'subtitles'          => $rSubtitles,
                        'custom_sid'         => strval($rEpisode['custom_sid']),
                        'added'              => $rEpisode['added'] ?? '',
                        'season'             => $rSeason,
                        'direct_source'      => ""
                    ];
                }
            }

            break;
            
        case 'get_series':
            // Retrieve category ID from request, default to null if not provided
            $rCategoryIDSearch = empty(ipTV_lib::$request['category_id']) ? null : intval(ipTV_lib::$request['category_id']);
            $rMovieNum = 0;
            
            // Ensure user has access to series
            if (!empty($rUserInfo['series_ids'])) {
                // Determine sorting method based on settings
                if (ipTV_lib::$settings['vod_sort_newest']) {
                    $query = 'SELECT *, 
                             (SELECT MAX(`streams`.`added`) 
                              FROM `streams_episodes` 
                              LEFT JOIN `streams` ON `streams`.`id` = `streams_episodes`.`stream_id` 
                              WHERE `streams_episodes`.`series_id` = `streams_series`.`id`) AS `last_modified_stream` 
                             FROM `streams_series` 
                             WHERE `id` IN (' . implode(',', array_map('intval', $rUserInfo['series_ids'])) . ') 
                             ORDER BY `last_modified_stream` DESC, `last_modified` DESC;';
                } else {
                    $query = 'SELECT * FROM `streams_series` 
                             WHERE `id` IN (' . implode(',', array_map('intval', $rUserInfo['series_ids'])) . ') 
                             ORDER BY FIELD(`id`,' . implode(',', $rUserInfo['series_ids']) . ') ASC;';
                }

                // Execute query
                $ipTV_db->query($query);
                $rSeries = $ipTV_db->get_rows(true, 'id');

                foreach ($rSeries as $rSeriesItem) {
                    // Use last modified stream date if available
                    if (!empty($rSeriesItem['last_modified_stream'])) {
                        $rSeriesItem['last_modified'] = $rSeriesItem['last_modified_stream'];
                    }

                    // Decode backdrop paths and ensure it's an array
                    $rBackdrops = json_decode($rSeriesItem['backdrop_path'], true) ?: [];

                    // Decode category IDs
                    $rCategoryIDs = json_decode($rSeriesItem['category_id'], true) ?: [];

                    foreach ($rCategoryIDs as $rCategoryID) {
                        // Check category filter or allow all if no filter set
                        if (empty($rCategoryIDSearch) || $rCategoryIDSearch == $rCategoryID) {
                            $output[] = [
                                'num' => ++$rMovieNum,
                                'name' => $rSeriesItem['title'],
                                'title' => $rSeriesItem['title'],
                                'year' => $rSeriesItem['year'],
                                'stream_type' => 'series',
                                'series_id' => (int) $rSeriesItem['id'],
                                'cover' => $rSeriesItem['cover'],
                                'plot' => $rSeriesItem['plot'],
                                'cast' => $rSeriesItem['cast'],
                                'director' => $rSeriesItem['director'],
                                'genre' => $rSeriesItem['genre'],
                                'release_date' => $rSeriesItem['release_date'],
                                'releaseDate' => $rSeriesItem['release_date'],
                                'last_modified' => $rSeriesItem['last_modified'],
                                'rating' => number_format($rSeriesItem['rating'], 0),
                                'rating_5based' => number_format($rSeriesItem['rating'] * 0.5, 1),
                                'backdrop_path' => $rBackdrops,
                                'youtube_trailer' => $rSeriesItem['youtube_trailer'],
                                'episode_run_time' => $rSeriesItem['episode_run_time'],
                                'category_id' => strval($rCategoryID),
                                'category_ids' => $rCategoryIDs,
                            ];
                        }
                        // If category filter is not set, break after the first match
                        if (!$rCategoryIDSearch) {
                            break;
                        }
                    }
                }
            }
            break;
            
        case 'get_vod_categories':
            // Fetch movie categories
            $rCategories = GetCategories('movie');

            foreach ($rCategories as $rCategory) {
                // Check if user has access to the category
                if (in_array($rCategory['id'], $rUserInfo['category_ids'])) {
                    $output[] = [
                        'category_id' => strval($rCategory['id']),
                        'category_name' => $rCategory['category_name'],
                        'parent_id' => 0,
                    ];
                }
            }
            break;

        case 'get_series_categories':
            // Fetch series categories
            $rCategories = GetCategories('series');

            foreach ($rCategories as $rCategory) {
                // Check if user has access to the category
                if (in_array($rCategory['id'], $rUserInfo['category_ids'])) {
                    $output[] = [
                        'category_id' => strval($rCategory['id']),
                        'category_name' => $rCategory['category_name'],
                        'parent_id' => 0,
                    ];
                }
            }
            break;

        case 'get_live_categories':
            // Fetch live and radio categories and merge them
            $rCategories = array_merge(GetCategories('live'), GetCategories('radio'));

            foreach ($rCategories as $rCategory) {
                // Check if user has access to the category
                if (in_array($rCategory['id'], $rUserInfo['category_ids'])) {
                    $output[] = [
                        'category_id' => strval($rCategory['id']),
                        'category_name' => $rCategory['category_name'],
                        'parent_id' => 0,
                    ];
                }
            }
            break;
            
        case 'get_simple_data_table':
            // Initialize EPG listings
            $output['epg_listings'] = [];

            if (!empty(ipTV_lib::$request['stream_id'])) {
                // Determine if multiple streams are requested
                if (is_numeric(ipTV_lib::$request['stream_id']) && !isset(ipTV_lib::$request['multi'])) {
                    $rMulti = false;
                    $rStreamIDs = [intval(ipTV_lib::$request['stream_id'])];
                } else {
                    $rMulti = true;
                    $rStreamIDs = array_map('intval', explode(',', ipTV_lib::$request['stream_id']));
                }

                if (!empty($rStreamIDs)) {
                    $rArchiveInfo = [];
                    $ipTV_db->query('SELECT `id`, `tv_archive_duration` FROM `streams` WHERE `id` IN (' . implode(',', $rStreamIDs) . ');');

                    if ($ipTV_db->num_rows() > 0) {
                        foreach ($ipTV_db->get_rows() as $rRow) {
                            $rArchiveInfo[$rRow['id']] = intval($rRow['tv_archive_duration']);
                        }
                    }

                    foreach ($rStreamIDs as $rStreamID) {
                        if (file_exists(EPG_PATH . 'stream_' . $rStreamID)) {
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
            // Initialize EPG listings
            $output['epg_listings'] = [];

            if (!empty(ipTV_lib::$request['stream_id'])) {
                $rLimit = empty(ipTV_lib::$request['limit']) ? 4 : intval(ipTV_lib::$request['limit']);

                // Determine if multiple streams are requested
                if (is_numeric(ipTV_lib::$request['stream_id']) && !isset(ipTV_lib::$request['multi'])) {
                    $rMulti = false;
                    $rStreamIDs = [intval(ipTV_lib::$request['stream_id'])];
                } else {
                    $rMulti = true;
                    $rStreamIDs = array_map('intval', explode(',', ipTV_lib::$request['stream_id']));
                }

                if (!empty($rStreamIDs)) {
                    $rTime = time();

                    foreach ($rStreamIDs as $rStreamID) {
                        if (file_exists(EPG_PATH . 'stream_' . $rStreamID)) {
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

                                    if (count($output['epg_listings']) >= $rLimit) {
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
            $rCategoryIDSearch = empty(ipTV_lib::$request['category_id']) ? null : intval(ipTV_lib::$request['category_id']);
            $rLiveNum = 0;
            $rUserInfo['live_ids'] = array_merge($rUserInfo['live_ids'], $rUserInfo['radio_ids']);

            if (!empty($rExtract['items_per_page'])) {
                $rUserInfo['live_ids'] = array_slice($rUserInfo['live_ids'], $rExtract['offset'], $rExtract['items_per_page']);
            }

            $rUserInfo['live_ids'] = ipTV_lib::sortChannels($rUserInfo['live_ids']);
            $rChannels = [];

            if (!empty($rUserInfo['live_ids'])) {
                $rWhere = [];
                $rWhereV = [];

                if (!empty($rCategoryIDSearch)) {
                    $rWhere[] = "JSON_CONTAINS(`category_id`, ?, '$')";
                    $rWhereV[] = $rCategoryIDSearch;
                }

                $rWhere[] = '`t1`.`id` IN (' . implode(',', $rUserInfo['live_ids']) . ')';
                $rWhereString = 'WHERE ' . implode(' AND ', $rWhere);

                $rOrder = ipTV_lib::$settings['channel_number_type'] != 'manual' ? 'FIELD(`t1`.`id`,' . implode(',', $rUserInfo['live_ids']) . ')' : '`order`';

                $ipTV_db->query('SELECT t1.*, t2.type_output, t2.live, t2.type_key FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type ' . $rWhereString . ' ORDER BY ' . $rOrder . ';', ...$rWhereV);
                $rChannels = $ipTV_db->get_rows();
            }

            foreach ($rChannels as $rChannel) {
                if (in_array($rChannel['type_key'], ['live', 'created_live', 'radio_streams'])) {
                    $output[] = [
                        'num' => ++$rLiveNum,
                        'name' => $rChannel['stream_display_name'],
                        'stream_type' => $rChannel['type_key'],
                        'stream_id' => (int) $rChannel['id'],
                        'stream_icon' => $rChannel['stream_icon'] ?: '',
                        'epg_channel_id' => $rChannel['channel_id'],
                        'added' => $rChannel['added'] ?: '',
                        'custom_sid' => strval($rChannel['custom_sid']),
                        'tv_archive' => !empty($rChannel['tv_archive_server_id']) && !empty($rChannel['tv_archive_duration']) ? 1 : 0,
                        'category_id' => strval($rCategoryIDSearch),
                        'thumbnail' => "",
                    ];
                }
            }
            break;
            
        case 'get_vod_info':
            // Initialize output
            $output['info'] = [];

            if (!empty(ipTV_lib::$request['vod_id'])) {
                $rVODID = intval(ipTV_lib::$request['vod_id']);

                // Fetch VOD info
                $ipTV_db->query('SELECT * FROM `streams` WHERE `id` = ?', $rVODID);
                $rRow = $ipTV_db->get_row();

                if ($rRow) {
                    $output['info'] = json_decode($rRow['movie_properties'], true);
                    $output['info']['tmdb_id'] = intval($output['info']['tmdb_id'] ?? 0);
                    $output['info']['episode_run_time'] = intval($output['info']['episode_run_time'] ?? 0);
                    $output['info']['releasedate'] = $output['info']['release_date'] ?? '';
                    $output['info']['cover_big'] = $output['info']['cover_big'] ?? '';
                    $output['info']['movie_image'] = $output['info']['movie_image'] ?? '';
                    $output['info']['rating'] = number_format($output['info']['rating'] ?? 0, 2) + 0;

                    $output['info']['subtitles'] = [];
                    if (!empty($output['info']['subtitle']) && is_array($output['info']['subtitle'])) {
                        foreach ($output['info']['subtitle'] as $index => $rSubtitle) {
                            $output['info']['subtitles'][] = [
                                'index' => $index,
                                'language' => $rSubtitle['tags']['language'] ?? null,
                                'title' => $rSubtitle['tags']['title'] ?? null
                            ];
                        }
                    }

                    // Remove unnecessary keys
                    foreach (['audio', 'video', 'subtitle'] as $rKey) {
                        unset($output['info'][$rKey]);
                    }

                    // Prepare movie data
                    $output['movie_data'] = [
                        'stream_id' => (int) $rRow['id'],
                        'name' => $rRow['stream_display_name'],
                        'title' => $rRow['stream_display_name'],
                        'year' => $rRow['year'],
                        'added' => $rRow['added'] ?? '',
                        'category_id' => strval(json_decode($rRow['category_id'], true)[0] ?? ''),
                        'category_ids' => json_decode($rRow['category_id'], true) ?? [],
                        'container_extension' => $rRow['target_container'],
                        'custom_sid' => strval($rRow['custom_sid']),
                        'direct_source' => ''
                    ];
                }
            }
            break;
            
        case 'get_vod_streams':
            $rCategoryIDSearch = empty(ipTV_lib::$request['category_id']) ? null : intval(ipTV_lib::$request['category_id']);
            $rMovieNum = 0;

            if (!empty($rExtract['items_per_page'])) {
                $rUserInfo['vod_ids'] = array_slice($rUserInfo['vod_ids'], $rExtract['offset'], $rExtract['items_per_page']);
            }

            $rUserInfo['vod_ids'] = ipTV_lib::sortChannels($rUserInfo['vod_ids']);
            $rChannels = [];

            if (!empty($rUserInfo['vod_ids'])) {
                $rWhere = [];
                $rWhereV = [];

                if (!empty($rCategoryIDSearch)) {
                    $rWhere[] = "JSON_CONTAINS(`category_id`, ?, '$')";
                    $rWhereV[] = $rCategoryIDSearch;
                }

                $rWhere[] = '`t1`.`id` IN (' . implode(',', $rUserInfo['vod_ids']) . ')';
                $rWhereString = 'WHERE ' . implode(' AND ', $rWhere);
                $rOrder = ipTV_lib::$settings['channel_number_type'] != 'manual' ? 'FIELD(`t1`.`id`,' . implode(',', $rUserInfo['vod_ids']) . ')' : '`order`';

                $ipTV_db->query('SELECT t1.*, t2.type_output, t2.live, t2.type_key FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type ' . $rWhereString . ' ORDER BY ' . $rOrder . ';', ...$rWhereV);
                $rChannels = $ipTV_db->get_rows();
            }

            foreach ($rChannels as $rChannel) {
                if ($rChannel['type_key'] === 'movie') {
                    $output[] = [
                        'num' => ++$rMovieNum,
                        'name' => $rChannel['stream_display_name'],
                        'stream_id' => (int) $rChannel['id'],
                        'category_id' => strval($rCategoryIDSearch),
                        'container_extension' => $rChannel['target_container'],
                        'custom_sid' => strval($rChannel['custom_sid']),
                    ];
                }
            }
            break;
            
        default:
            // Default user and server information
            $output['user_info'] = [];
            $url = empty(ipTV_lib::$Servers[SERVER_ID]['domain_name']) ? ipTV_lib::$Servers[SERVER_ID]['server_ip'] : ipTV_lib::$Servers[SERVER_ID]['domain_name'];
            $output['server_info'] = [
                'url' => $url,
                'port' => ipTV_lib::$Servers[SERVER_ID]['http_broadcast_port'],
                'https_port' => ipTV_lib::$Servers[SERVER_ID]['https_broadcast_port'],
                'server_protocol' => ipTV_lib::$Servers[SERVER_ID]['server_protocol'],
                'rtmp_port' => ipTV_lib::$Servers[SERVER_ID]['rtmp_port'],
                'timezone' => ipTV_lib::$settings['default_timezone'],
                'timestamp_now' => time(),
                'time_now' => date('Y-m-d H:i:s'),
                'process' => $mobile_apps == 1
            ];

            $output['user_info'] = [
                'username' => $rUserInfo['username'],
                'password' => $rUserInfo['password'],
                'message' => ipTV_lib::$settings['message_of_day'],
                'auth' => 1,
                'status' => ($rUserInfo['admin_enabled'] == 0 ? 'Banned' : ($rUserInfo['enabled'] == 0 ? 'Disabled' : (is_null($rUserInfo['exp_date']) || $rUserInfo['exp_date'] > time() ? 'Active' : 'Expired'))),
                'exp_date' => $rUserInfo['exp_date'],
                'is_trial' => $rUserInfo['is_trial'],
                'active_cons' => $rUserInfo['active_cons'],
                'created_at' => $rUserInfo['created_at'],
                'max_connections' => $rUserInfo['max_connections'],
                'allowed_output_formats' => array_keys($rUserInfo['output_formats'])
            ];
            break;
        }
        
        die(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));

    }
} else {
    checkBruteforce(null, null, $rUsername);
    generateError('INVALID_CREDENTIALS');
}

function getOutputFormats(array $rFormats): array {
    $rFormatArray = [1 => 'm3u8', 2 => 'ts', 3 => 'rtmp'];

    // Filter out invalid format keys and map them
    return array_values(array_intersect_key($rFormatArray, array_flip($rFormats)));
}

function shutdown(): void {
    global $rDeny, $ipTV_db;

    if (!empty($rDeny)) {
        checkFlood();
    }

    if (!empty($ipTV_db) && is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}