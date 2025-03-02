<?php
include "/home/xc_vm/admin/functions.php";
require INCLUDES_PATH . 'libs/tmdb.php';
require INCLUDES_PATH . 'libs/tmdb_release.php';

$rSettings = getSettings();
$rServers = getStreamingServers();
$rWatchCategories = array(1 => getWatchCategories(1), 2 => getWatchCategories(2));

$ipTV_db_admin->query("SELECT * FROM `watch_settings`;");
if ($ipTV_db_admin->num_rows() == 1) {
    $rWatchSettings = $ipTV_db_admin->get_row();
}

$rPID = getmypid();
if (isset($rSettings["watch_pid"])) {
    if ((file_exists("/proc/" . $rSettings["watch_pid"])) && (strlen($rSettings["watch_pid"]) > 0)) {
        exit;
    } else {
        ipTV_lib::setSettings(["watch_pid" => intval($rPID)]);
    }
}

$rTimeout = 3000;       // Limit by time.
$rScanOffset = intval($rWatchSettings["scan_seconds"]) ?: 3600;

set_time_limit($rTimeout);
ini_set('max_execution_time', $rTimeout);

if (strlen($rSettings["tmdb_api_key"]) == 0) {
    exit;
}

if (strlen($rSettings["tmdb_language"]) > 0) {
    $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rSettings["tmdb_language"]);
} else {
    $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
}

if ($rSettings["local_api"]) {
    $rAPI = "http://127.0.0.1:" . $rServers[$_INFO["server_id"]]["http_broadcast_port"] . "/api.php";
} else {
    $rAPI = "http://" . $rServers[$_INFO["server_id"]]["server_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"] . "/api.php";
}

$rStreamDatabase = array();
$ipTV_db_admin->query("SELECT `stream_source` FROM `streams` WHERE `type` IN (2,5);");
if ($ipTV_db_admin->num_rows() > 0) {
    foreach ($ipTV_db_admin->get_rows() as $row) {
        foreach (json_decode($row["stream_source"], True) as $rSource) {
            if (strlen($rSource) > 0) {
                $rStreamDatabase[] = $rSource;
            }
        }
    }
}

$rChanged = False;
$rUpdateSeries = array();
$rArray = array("movie_symlink" => 0, "type" => 0, "target_container" => array("mp4"), "added" => time(), "read_native" => 0, "stream_all" => 0, "redirect_stream" => 1, "direct_source" => 0, "gen_timestamps" => 1, "transcode_attributes" => array(), "stream_display_name" => "", "stream_source" => array(), "movie_subtitles" => array(), "category_id" => 0, "stream_icon" => "", "notes" => "", "custom_sid" => "", "custom_ffmpeg" => "", "transcode_profile_id" => 0, "enable_transcode" => 0, "auto_restart" => "[]", "allow_record" => 0, "rtmp_output" => 0, "epg_id" => null, "channel_id" => null, "epg_lang" => null, "tv_archive_server_id" => 0, "tv_archive_duration" => 0, "delay_minutes" => 0, "external_push" => array(), "probesize_ondemand" => 128000);
$ipTV_db_admin->query("SELECT * FROM `watch_folders` WHERE `active` = 1 AND UNIX_TIMESTAMP() - `last_run` > " . intval($rScanOffset) . " ORDER BY `id` ASC;");
if ($ipTV_db_admin->num_rows() > 0) {
    foreach ($ipTV_db_admin->get_rows() as $rRow) {
        $rArray["type"] = array("movie" => 2, "series" => 5)[$rRow["type"]];
        $ipTV_db_admin->query("UPDATE `watch_folders` SET `last_run` = UNIX_TIMESTAMP() WHERE `id` = " . intval($rRow["id"]) . ";");
        $rImportStreams = array();
        $rExtensions = json_decode($rRow["allowed_extensions"], True);
        if (!$rExtensions) {
            $rExtensions = array();
        }
        if (count($rExtensions) == 0) {
            $rExtensions = array("mp4", "mkv", "avi", "mpg", "flv");
        }
        $rFiles = scanRecursive(intval($rRow["server_id"]), $rRow["directory"], $rExtensions); // Only these containers are accepted.
        if (isset($rRow["auto_subtitles"])) {
            $rSubtitles = scanRecursive(intval($rRow["server_id"]), $rRow["directory"], array("srt", "sub", "sbv"));
        }
        foreach ($rFiles as $rFile) {
            $rFilePath = "s:" . intval($rRow["server_id"]) . ":" . $rFile;
            if (!in_array($rFilePath, $rStreamDatabase)) {
                $rPathInfo = pathinfo($rFile);
                $rImportArray = array("file" => $rFile, "stream_source" => array($rFilePath), "stream_icon" => "", "stream_display_name" => $rPathInfo["filename"], "movie_properties" => array(), "target_container" => array($rPathInfo["extension"]));
                $rImportStreams[] = $rImportArray;
            }
        }
        foreach ($rImportStreams as $rImportStream) {
            $rImportArray = $rArray;
            $rFile = $rImportStream["file"];
            unset($rImportStream["file"]);
            foreach (array_keys($rImportStream) as $rKey) {
                $rImportArray[$rKey] = $rImportStream[$rKey];
            }
            $ipTV_db_admin->query("DELETE FROM `watch_output` WHERE `filename` = '" . $ipTV_db_admin->escape($rFile) . "' AND `type` = " . (array("movie" => 1, "series" => 2)[$rRow["type"]]) . ";");
            if ((!$rWatchSettings["ffprobe_input"]) or (isset(checkSource($rRow["server_id"], $rFile)["streams"]))) {
                $rFilename = pathinfo($rFile)["filename"];
                if ($rSettings["release_parser"] == "php") {
                    $rRelease = new Release($rFilename);
                    $rTitle = $rRelease->getTitle();
                    $rYear = $rRelease->getYear();
                    $rReleaseSeason = $rRelease->getSeason();
                    $rReleaseEpisode = $rRelease->getEpisode();
                } else {
                    $rRelease = tmdbParseRelease($rFilename);
                    $rTitle = $rRelease["title"];
                    $rYear = $rRelease["year"];
                    $rReleaseSeason = $rRelease["season"];
                    $rReleaseEpisode = $rRelease["episode"];
                }
                if (!$rTitle) {
                    $rTitle = $rFilename;
                }
                $rMatch = null;
                if (!$rRow["disable_tmdb"]) {
                    if ($rRow["type"] == "movie") {
                        $rResults = $rTMDB->searchMovie($rTitle);
                    } else {
                        $rResults = $rTMDB->searchTVShow($rTitle);
                    }
                    $rMatches = array();
                    foreach ($rResults as $rResultArr) {
                        similar_text(strtoupper($rTitle), strtoupper($rResultArr->get("title") ?: $rResultArr->get("name")), $rPercentage);
                        if ($rPercentage >= $rWatchSettings["percentage_match"]) {
                            if ((!$rYear) or (intval(substr($rResultArr->get("release_date") ?: $rResultArr->get("first_air_date"), 0, 4)) == intval($rYear))) {
                                if (strtolower($rResultArr->get("name")) == strtolower($rTitle)) {
                                    $rMatches = array(array("percentage" => 100, "data" => $rResultArr));
                                    break;
                                } else {
                                    $rMatches[] = array("percentage" => $rPercentage, "data" => $rResultArr);
                                }
                            }
                        }
                    }
                    if (count($rMatches) > 0) {
                        $rMax = max(array_column($rMatches, 'percentage'));
                        $rKeys = array_filter(array_map(function ($rMatches) use ($rMax) {
                            return $rMatches['percentage'] == $rMax ? $rMatches['data'] : null;
                        }, $rMatches));
                        $rMatch = array_values($rKeys)[0];
                    }
                }
                if (($rMatch) or ($rRow["ignore_no_match"])) {
                    if ($rMatch) {
                        if ($rRow["type"] == "movie") {
                            $rMovie = $rTMDB->getMovie($rMatch->get("id"));
                            $rMovieData = json_decode($rMovie->getJSON(), True);
                            $rMovieData["trailer"] = $rMovie->getTrailer();
                            $rThumb = "https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rMovieData['poster_path'];
                            $rBG = "https://image.tmdb.org/t/p/w1280" . $rMovieData['backdrop_path'];
                            if ($rSettings["download_images"]) {
                                $rThumb = downloadImage($rThumb);
                                $rBG = downloadImage($rBG);
                            } else {
                                sleep(1); // Avoid limits.
                            }
                            $rCast = array();
                            foreach ($rMovieData["credits"]["cast"] as $rMember) {
                                if (count($rCast) < 5) {
                                    $rCast[] = $rMember["name"];
                                }
                            }
                            $rDirectors = array();
                            foreach ($rMovieData["credits"]["crew"] as $rMember) {
                                if ((count($rDirectors) < 5) && ($rMember["department"] == "Directing")) {
                                    $rDirectors[] = $rMember["name"];
                                }
                            }
                            $rCountry = "";
                            if (isset($rMovieData["production_countries"][0]["name"])) {
                                $rCountry = $rMovieData["production_countries"][0]["name"];
                            }
                            $rGenres = array();
                            foreach ($rMovieData["genres"] as $rGenre) {
                                if (count($rGenres) < 3) {
                                    $rGenres[] = $rGenre["name"];
                                }
                            }
                            $rSeconds = intval($rMovieData["runtime"]) * 60;
                            $rImportArray["stream_display_name"] = $rMovieData["title"];
                            if (strlen($rMovieData["release_date"]) > 0) {
                                $rImportArray["stream_display_name"] .= " (" . intval(substr($rMovieData["release_date"], 0, 4)) . ")";
                            }
                            $rImportArray["movie_properties"] = array("kinopoisk_url" => "https://www.themoviedb.org/movie/" . $rMovieData["id"], "tmdb_id" => $rMovieData["id"], "name" => $rMovieData["title"], "o_name" => $rMovieData["original_title"], "cover_big" => $rThumb, "movie_image" => $rThumb, "releasedate" => $rMovieData["release_date"], "episode_run_time" => $rMovieData["runtime"], "youtube_trailer" => $rMovieData["trailer"], "director" => join(", ", $rDirectors), "actors" => join(", ", $rCast), "cast" => join(", ", $rCast), "description" => $rMovieData["overview"], "plot" => $rMovieData["overview"], "age" => "", "mpaa_rating" => "", "rating_count_kinopoisk" => 0, "country" => $rCountry, "genre" => join(", ", $rGenres), "backdrop_path" => array($rBG), "duration_secs" => $rSeconds, "duration" => sprintf('%02d:%02d:%02d', ($rSeconds / 3600), ($rSeconds / 60 % 60), $rSeconds % 60), "video" => array(), "audio" => array(), "bitrate" => 0, "rating" => $rMovieData["vote_average"]);
                            $rImportArray["read_native"] = $rWatchSettings["read_native"] ?: 1;
                            $rImportArray["movie_symlink"] = $rWatchSettings["movie_symlink"] ?: 1;
                            $rImportArray["transcode_profile_id"] = $rWatchSettings["transcode_profile_id"] ?: 0;
                            $rImportArray["order"] = getNextOrder();
                            $rCategoryData = $rWatchCategories[1][intval($rMovieData["genres"][0]["id"])];
                            if ($rRow["category_id"] > 0) {
                                $rImportArray["category_id"] = intval($rRow["category_id"]);
                            } else if ($rCategoryData["category_id"] > 0) {
                                $rImportArray["category_id"] = intval($rCategoryData["category_id"]);
                            } else if ($rRow["fb_category_id"] > 0) {
                                $rImportArray["category_id"] = intval($rRow["fb_category_id"]);
                            } else {
                                $rImportArray["category_id"] = 0;
                            }
                        } else {
                            $rShow = $rTMDB->getTVShow($rMatch->get("id"));
                            $rShowData = json_decode($rShow->getJSON(), True);
                            $rSeries = getSeriesByTMDB($rShowData["id"]);
                            if (!$rSeries) {
                                // Series doesn't exist, create it!
                                $rSeriesArray = array("title" => $rShowData["name"], "category_id" => "", "episode_run_time" => 0, "tmdb_id" => $rShowData["id"], "cover" => "", "genre" => "", "plot" => $rShowData["overview"], "cast" => "", "rating" => $rShowData["vote_average"], "director" => "", "releaseDate" => $rShowData["first_air_date"], "last_modified" => time(), "seasons" => array(), "backdrop_path" => array(), "youtube_trailer" => "");
                                $rSeriesArray["youtube_trailer"] = getSeriesTrailer($rShowData["id"]);
                                $rSeriesArray["cover"] = "https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rShowData['poster_path'];
                                $rSeriesArray["cover_big"] = $rSeriesArray["cover"];
                                $rSeriesArray["backdrop_path"] = array("https://image.tmdb.org/t/p/w1280" . $rShowData['backdrop_path']);
                                if ($rSettings["download_images"]) {
                                    $rSeriesArray["cover"] = downloadImage($rSeriesArray["cover"]);
                                    $rSeriesArray["backdrop_path"] = array(downloadImage($rSeriesArray["backdrop_path"][0]));
                                }
                                $rCast = array();
                                foreach ($rShowData["credits"]["cast"] as $rMember) {
                                    if (count($rCast) < 5) {
                                        $rCast[] = $rMember["name"];
                                    }
                                }
                                $rSeriesArray["cast"] = join(", ", $rCast);
                                $rDirectors = array();
                                foreach ($rShowData["credits"]["crew"] as $rMember) {
                                    if ((count($rDirectors) < 5) && ($rMember["department"] == "Directing")) {
                                        $rDirectors[] = $rMember["name"];
                                    }
                                }
                                $rSeriesArray["director"] = join(", ", $rDirectors);
                                $rGenres = array();
                                foreach ($rShowData["genres"] as $rGenre) {
                                    if (count($rGenres) < 3) {
                                        $rGenres[] = $rGenre["name"];
                                    }
                                }
                                $rSeriesArray["genre"] = join(", ", $rGenres);
                                $rSeriesArray["episode_run_time"] = intval($rShowData["episode_run_time"][0]);
                                $rCategoryData = $rWatchCategories[2][intval($rShowData["genres"][0]["id"])];
                                if ($rRow["category_id"] > 0) {
                                    $rSeriesArray["category_id"] = intval($rRow["category_id"]);
                                } else if ($rCategoryData["category_id"] > 0) {
                                    $rSeriesArray["category_id"] = intval($rCategoryData["category_id"]);
                                } else if ($rRow["fb_category_id"] > 0) {
                                    $rSeriesArray["category_id"] = intval($rRow["fb_category_id"]);
                                } else {
                                    $rSeriesArray["category_id"] = 0;
                                }
                                if ($rSeriesArray["category_id"] > 0) {
                                    $rCols = "`" . implode('`,`', array_keys($rSeriesArray)) . "`";
                                    $rValues = null;
                                    foreach (array_values($rSeriesArray) as $rValue) {
                                        isset($rValues) ? $rValues .= ',' : $rValues = '';
                                        if (is_array($rValue)) {
                                            $rValue = json_encode($rValue);
                                        }
                                        if (is_null($rValue)) {
                                            $rValues .= 'NULL';
                                        } else {
                                            $rValues .= '\'' . $ipTV_db_admin->escape($rValue) . '\'';
                                        }
                                    }
                                    $rQuery = "INSERT INTO `series`(" . $ipTV_db_admin->escape($rCols) . ") VALUES(" . $rValues . ");";
                                    if ($ipTV_db_admin->query($rQuery)) {
                                        $rInsertID = $ipTV_db_admin->last_insert_id();
                                        $rSeries = getSerie($rInsertID);
                                        $rORBouquets = json_decode($rRow["bouquets"], True);
                                        if (!$rORBouquets) {
                                            $rORBouquets = array();
                                        }
                                        if (count($rORBouquets) > 0) {
                                            $rBouquets = json_decode($rRow["bouquets"], True);
                                        } else {
                                            $rBouquets = json_decode($rCategoryData["category_id"], True);
                                        }
                                        if (!$rBouquets) {
                                            $rBouquets = json_decode($rRow["fb_bouquets"], True);
                                        }
                                        if (!$rBouquets) {
                                            $rBouquets = array();
                                        }
                                        foreach ($rBouquets as $rBouquet) {
                                            addToBouquet("series", $rBouquet, $rInsertID);
                                            $rChanged = True;
                                        }
                                    }
                                }
                            }
                            $rImportArray["read_native"] = $rWatchSettings["read_native"] ?: 1;
                            $rImportArray["movie_symlink"] = $rWatchSettings["movie_symlink"] ?: 1;
                            $rImportArray["transcode_profile_id"] = $rWatchSettings["transcode_profile_id"] ?: 0;
                            $rImportArray["order"] = getNextOrder();
                            if (($rReleaseSeason) && ($rReleaseEpisode)) {
                                $rImportArray["stream_display_name"] = $rShowData["name"] . " - S" . sprintf('%02d', intval($rReleaseSeason)) . "E" . sprintf('%02d', $rReleaseEpisode);
                                $rEpisodes = json_decode($rTMDB->getSeason($rShowData["id"], intval($rReleaseSeason))->getJSON(), True);
                                foreach ($rEpisodes["episodes"] as $rEpisode) {
                                    if (intval($rEpisode["episode_number"]) == $rReleaseEpisode) {
                                        if (strlen($rEpisode["still_path"]) > 0) {
                                            $rImage = "https://image.tmdb.org/t/p/w300" . $rEpisode["still_path"];
                                            if ($rSettings["download_images"]) {
                                                $rImage = downloadImage($rImage);
                                            }
                                        }
                                        if (strlen($rEpisode["name"]) > 0) {
                                            $rImportArray["stream_display_name"] .= " - " . $rEpisode["name"];
                                        }
                                        $rSeconds = intval($rShowData["episode_run_time"][0]) * 60;
                                        $rImportArray["movie_properties"] = array("tmdb_id" => $rEpisode["id"], "releasedate" => $rEpisode["air_date"], "plot" => $rEpisode["overview"], "duration_secs" => $rSeconds, "duration" => sprintf('%02d:%02d:%02d', ($rSeconds / 3600), ($rSeconds / 60 % 60), $rSeconds % 60), "movie_image" => $rImage, "video" => array(), "audio" => array(), "bitrate" => 0, "rating" => $rEpisode["vote_average"], "season" => $rReleaseSeason);
                                        if (strlen($rImportArray["movie_properties"]["movie_image"][0]) == 0) {
                                            unset($rImportArray["movie_properties"]["movie_image"]);
                                        }
                                    }
                                }
                                if (strlen($rImportArray["stream_display_name"]) == 0) {
                                    $rImportArray["stream_display_name"] = "No Episode Title";
                                }
                            }
                        }
                    } else {
                        if ($rRow["type"] == "movie") {
                            $rImportArray["stream_display_name"] = $rTitle;
                            if ($rYear) {
                                $rImportArray["stream_display_name"] .= " (" . $rYear . ")";
                            }
                            $rImportArray["read_native"] = $rWatchSettings["read_native"] ?: 1;
                            $rImportArray["movie_symlink"] = $rWatchSettings["movie_symlink"] ?: 1;
                            $rImportArray["transcode_profile_id"] = $rWatchSettings["transcode_profile_id"] ?: 0;
                            $rImportArray["order"] = getNextOrder();
                            $rCategoryData = $rWatchCategories[1][intval($rMovieData["genres"][0]["id"])];
                            if ($rRow["category_id"] > 0) {
                                $rImportArray["category_id"] = intval($rRow["category_id"]);
                            } else if ($rRow["fb_category_id"] > 0) {
                                $rImportArray["category_id"] = intval($rRow["fb_category_id"]);
                            } else {
                                $rImportArray["category_id"] = 0;
                            }
                        } else if ($rSeries) {
                            if (($rReleaseSeason) && ($rReleaseEpisode)) {
                                $rImportArray["stream_display_name"] = $rTitle . " - S" . sprintf('%02d', intval($rReleaseSeason)) . "E" . sprintf('%02d', $rReleaseEpisode) . " - ";
                            }
                            $rImportArray["read_native"] = $rWatchSettings["read_native"] ?: 1;
                            $rImportArray["movie_symlink"] = $rWatchSettings["movie_symlink"] ?: 1;
                            $rImportArray["transcode_profile_id"] = $rWatchSettings["transcode_profile_id"] ?: 0;
                            $rImportArray["order"] = getNextOrder();
                        }
                    }
                    if (isset($rRow["auto_subtitles"])) {
                        $rPathInfo = pathinfo($rFile);
                        foreach (array("srt", "sub", "sbv") as $rExt) {
                            $rSubtitle = $rPathInfo["dirname"] . "/" . $rPathInfo["filename"] . "." . $rExt;
                            if (in_array($rSubtitle, $rSubtitles)) {
                                $rImportArray["movie_subtitles"] = array("files" => array($rSubtitle), "names" => array("Subtitles"), "charset" => array("UTF-8"), "location" => intval($rRow["server_id"]));
                                break;
                            }
                        }
                    }
                    if ($rRow["type"] == "movie") {
                        $rORBouquets = json_decode($rRow["bouquets"], True);
                        if (!$rORBouquets) {
                            $rORBouquets = array();
                        }
                        if (count($rORBouquets) > 0) {
                            $rBouquets = json_decode($rRow["bouquets"], True);
                        } else {
                            $rBouquets = json_decode($rCategoryData["category_id"], True);
                        }
                        if (!$rBouquets) {
                            $rBouquets = json_decode($rRow["fb_bouquets"], True);
                        }
                        if (!$rBouquets) {
                            $rBouquets = array();
                        }
                    }
                    if (($rImportArray["category_id"] > 0) or ($rSeries)) {
                        $rCols = "`" . implode('`,`', array_keys($rImportArray)) . "`";
                        $rValues = null;
                        foreach (array_values($rImportArray) as $rValue) {
                            isset($rValues) ? $rValues .= ',' : $rValues = '';
                            if (is_array($rValue)) {
                                $rValue = json_encode($rValue);
                            }
                            if (is_null($rValue)) {
                                $rValues .= 'NULL';
                            } else {
                                $rValues .= '\'' . $ipTV_db_admin->escape($rValue) . '\'';
                            }
                        }
                        $rQuery = "INSERT INTO `streams`(" . $ipTV_db_admin->escape($rCols) . ") VALUES(" . $rValues . ");";
                        if ($ipTV_db_admin->query($rQuery)) {
                            $rInsertID = $ipTV_db_admin->last_insert_id();
                            $ipTV_db_admin->query("INSERT INTO `streams_servers`(`stream_id`, `server_id`, `parent_id`, `on_demand`) VALUES(" . intval($rInsertID) . ", " . intval($rRow["server_id"]) . ", 0, 0);");
                            if ($rWatchSettings["auto_encode"]) {
                                $rPost = array("action" => "vod", "sub" => "start", "stream_ids" => array($rInsertID));
                                $rContext = stream_context_create(array(
                                    'http' => array(
                                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                        'method' => 'POST',
                                        'content' => http_build_query($rPost)
                                    )
                                ));
                                $rRet = json_decode(file_get_contents($rAPI, false, $rContext), True);
                            }
                            if ($rRow["type"] == "movie") {
                                foreach ($rBouquets as $rBouquet) {
                                    addToBouquet("stream", $rBouquet, $rInsertID);
                                    $rChanged = True;
                                }
                            } else {
                                $ipTV_db_admin->query("INSERT INTO `series_episodes`(`season_num`, `series_id`, `stream_id`, `sort`) VALUES(" . intval($rReleaseSeason) . ", " . intval($rSeries["id"]) . ", " . $rInsertID . ", " . intval($rReleaseEpisode) . ");");
                                if (!in_array($rSeries["id"], $rUpdateSeries)) {
                                    $rUpdateSeries[] = $rSeries["id"];
                                }
                            }
                            // Success!
                            $ipTV_db_admin->query("INSERT INTO `watch_output`(`type`, `server_id`, `filename`, `status`, `stream_id`) VALUES(" . (array("movie" => 1, "series" => 2)[$rRow["type"]]) . ", " . intval($rRow["server_id"]) . ", '" . $ipTV_db_admin->escape($rFile) . "', 1, " . intval($rInsertID) . ");");
                        } else {
                            // Insert failed.
                            $ipTV_db_admin->query("INSERT INTO `watch_output`(`type`, `server_id`, `filename`, `status`, `stream_id`) VALUES(" . (array("movie" => 1, "series" => 2)[$rRow["type"]]) . ", " . intval($rRow["server_id"]) . ", '" . $ipTV_db_admin->escape($rFile) . "', 2, 0);");
                        }
                    } else {
                        // No category.
                        $ipTV_db_admin->query("INSERT INTO `watch_output`(`type`, `server_id`, `filename`, `status`, `stream_id`) VALUES(" . (array("movie" => 1, "series" => 2)[$rRow["type"]]) . ", " . intval($rRow["server_id"]) . ", '" . $ipTV_db_admin->escape($rFile) . "', 3, 0);");
                    }
                } else {
                    // No match.
                    $ipTV_db_admin->query("INSERT INTO `watch_output`(`type`, `server_id`, `filename`, `status`, `stream_id`) VALUES(" . (array("movie" => 1, "series" => 2)[$rRow["type"]]) . ", " . intval($rRow["server_id"]) . ", '" . $ipTV_db_admin->escape($rFile) . "', 4, 0);");
                }
            } else {
                // File is broken.
                $ipTV_db_admin->query("INSERT INTO `watch_output`(`type`, `server_id`, `filename`, `status`, `stream_id`) VALUES(" . (array("movie" => 1, "series" => 2)[$rRow["type"]]) . ", " . intval($rRow["server_id"]) . ", '" . $ipTV_db_admin->escape($rFile) . "', 5, 0);");
            }
        }
    }
}
if ($rChanged) {
    scanBouquets();
}
foreach ($rUpdateSeries as $rSeriesID) {
    updateSeries(intval($rSeriesID));
}
