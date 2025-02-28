<?php
include "/home/xc_vm/admin/functions.php";
require INCLUDES_PATH . 'libs/tmdb.php';
require INCLUDES_PATH . 'libs/tmdb_release.php';

$rSettings = getSettings();
$rCategories = getCategories_admin();
$rServers = getStreamingServers();

$ipTV_db_admin->query("SELECT * FROM `watch_settings`;");
if ($ipTV_db_admin->num_rows() == 1) {
    $rWatchSettings = $ipTV_db_admin->get_row();
}

$rPID = getmypid();
if (isset($rSettings["tmdb_pid"])) {
    if ((file_exists("/proc/" . $rSettings["tmdb_pid"])) && (strlen($rSettings["tmdb_pid"]) > 0)) {
        exit;
    } else {
        ipTV_lib::setSettings(["tmdb_pid" => intval($rPID)]);
    }
}

$rLimit = 250;      // Limit by quantity.
$rTimeout = 3000;   // Limit by time.

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

$rUpdateSeries = array();
$ipTV_db_admin->query("SELECT `id`, `type`, `stream_id` FROM `tmdb_async` WHERE `status` = 0 ORDER BY `stream_id` ASC LIMIT " . intval($rLimit) . ";");
if ($ipTV_db_admin->num_rows() > 0) {
    foreach ($ipTV_db_admin->get_rows() as $rRow) {
        if ($rRow["type"] == 1) { // Movies
            $ipTV_db_admin->query("SELECT * FROM `streams` WHERE `id` = " . intval($rRow["stream_id"]) . ";");
            if ($ipTV_db_admin->num_rows() == 1) {
                $rStream = $ipTV_db_admin->get_row();
                $rFilename = pathinfo(json_decode($rStream["stream_source"], true)[0])["filename"];
                if ($rSettings["release_parser"] == "php") {
                    $rRelease = new Release($rFilename);
                    $rTitle = $rRelease->getTitle();
                    $rYear = $rRelease->getYear();
                } else {
                    $rRelease = tmdbParseRelease($rFilename);
                    $rTitle = $rRelease["title"];
                    $rYear = $rRelease["year"];
                }
                if (!$rTitle) {
                    $rTitle = $rFilename;
                }
                $rResults = $rTMDB->searchMovie($rTitle);
                $rMatch = null;
                foreach ($rResults as $rResultArr) {
                    similar_text(strtoupper($rTitle), strtoupper($rResultArr->get("title") ?: $rResultArr->get("name")), $rPercentage);
                    if ($rPercentage >= $rWatchSettings["percentage_match"]) {
                        if ($rYear) {
                            $rResultYear = intval(substr($rResultArr->get("release_date"), 0, 4));
                            if ($rResultYear == $rYear) {
                                $rMatch = $rResultArr;
                                break;
                            }
                        } else {
                            $rMatch = $rResultArr;
                            break;
                        }
                    }
                }
                if ($rMatch) {
                    $rMovie = $rTMDB->getMovie($rMatch->get("id"));
                    $rMovieData = json_decode($rMovie->getJSON(), true);
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
                    $rProperties = array("kinopoisk_url" => "https://www.themoviedb.org/movie/" . $rMovieData["id"], "tmdb_id" => $rMovieData["id"], "name" => $rMovieData["title"], "o_name" => $rMovieData["original_title"], "cover_big" => $rThumb, "movie_image" => $rThumb, "releasedate" => $rMovieData["release_date"], "episode_run_time" => $rMovieData["runtime"], "youtube_trailer" => $rMovieData["trailer"], "director" => join(", ", $rDirectors), "actors" => join(", ", $rCast), "cast" => join(", ", $rCast), "description" => $rMovieData["overview"], "plot" => $rMovieData["overview"], "age" => "", "mpaa_rating" => "", "rating_count_kinopoisk" => 0, "country" => $rCountry, "genre" => join(", ", $rGenres), "backdrop_path" => array($rBG), "duration_secs" => $rSeconds, "duration" => sprintf('%02d:%02d:%02d', ($rSeconds / 3600), ($rSeconds / 60 % 60), $rSeconds % 60), "video" => array(), "audio" => array(), "bitrate" => 0, "rating" => $rMovieData["vote_average"]);
                    $rTitle = $rMovieData["title"];
                    if (strlen($rMovieData["release_date"]) > 0) {
                        $rTitle .= " (" . intval(substr($rMovieData["release_date"], 0, 4)) . ")";
                    }
                    $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = 1 WHERE `id` = " . intval($rRow["id"]) . ";");
                    $ipTV_db_admin->query("UPDATE `streams` SET `stream_display_name` = '" . $ipTV_db_admin->escape($rTitle) . "', `movie_properties` = '" . $ipTV_db_admin->escape(json_encode($rProperties)) . "' WHERE `id` = " . intval($rRow["stream_id"]) . ";");
                } else {
                    $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -1 WHERE `id` = " . intval($rRow["id"]) . ";");
                }
            } else {
                $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -2 WHERE `id` = " . intval($rRow["id"]) . ";");
            }
        } else if ($rRow["type"] == 2) { // Series
            $ipTV_db_admin->query("SELECT * FROM `series` WHERE `id` = " . intval($rRow["stream_id"]) . ";");
            if ($ipTV_db_admin->num_rows() == 1) {
                $rStream = $ipTV_db_admin->get_row();
                $rFilename = $rStream["title"];
                if ($rSettings["release_parser"] == "php") {
                    $rRelease = new Release($rFilename);
                    $rTitle = $rRelease->getTitle();
                    $rYear = $rRelease->getYear();
                } else {
                    $rRelease = tmdbParseRelease($rFilename);
                    $rTitle = $rRelease["title"];
                    $rYear = $rRelease["year"];
                }
                if (!$rTitle) {
                    $rTitle = $rFilename;
                }
                $rResults = $rTMDB->searchTVShow($rTitle);
                $rMatch = null;
                foreach ($rResults as $rResultArr) {
                    similar_text($rTitle, $rResultArr->get("title") ?: $rResultArr->get("name"), $rPercentage);
                    if ($rPercentage >= $rWatchSettings["percentage_match"]) {
                        if ($rYear) {
                            $rResultYear = intval(substr($rResultArr->get("release_date"), 0, 4));
                            if ($rResultYear == $rYear) {
                                $rMatch = $rResultArr;
                                break;
                            }
                        } else {
                            $rMatch = $rResultArr;
                            break;
                        }
                    }
                }
                if ($rMatch) {
                    $rShow = $rTMDB->getTVShow($rMatch->get("id"));
                    $rShowData = json_decode($rShow->getJSON(), true);
                    $rSeriesArray = $rStream;
                    $rSeriesArray["title"] = $rShowData["name"];
                    $rSeriesArray["tmdb_id"] = $rShowData["id"];
                    $rSeriesArray["plot"] = $rShowData["overview"];
                    $rSeriesArray["rating"] = $rShowData["vote_average"];
                    $rSeriesArray["releaseDate"] = $rShowData["first_air_date"];
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
                    $rQuery = "REPLACE INTO `series`(" . $ipTV_db_admin->escape($rCols) . ") VALUES(" . $rValues . ");";
                    $ipTV_db_admin->query($rQuery);
                    $rInsertID = $ipTV_db_admin->last_insert_id();
                    updateSeries(intval($rInsertID));
                    $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = 1 WHERE `id` = " . intval($rRow["id"]) . ";");
                } else {
                    $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -1 WHERE `id` = " . intval($rRow["id"]) . ";");
                }
            } else {
                $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -2 WHERE `id` = " . intval($rRow["id"]) . ";");
            }
        } else if ($rRow["type"] == 3) { // Episodes
            $ipTV_db_admin->query("SELECT * FROM `streams` WHERE `id` = " . intval($rRow["stream_id"]) . ";");
            if ($ipTV_db_admin->num_rows() == 1) {
                $rStream = $ipTV_db_admin->get_row();
                $ipTV_db_admin->query("SELECT * FROM `series_episodes` WHERE `stream_id` = " . intval($rRow["stream_id"]) . ";");
                if ($ipTV_db_admin->num_rows() == 1) {
                    $rSeriesEpisode = $ipTV_db_admin->get_row();
                    $rResultD = $ipTV_db_admin->query("SELECT * FROM `series` WHERE `id` = " . intval($rSeriesEpisode["series_id"]) . ";");
                    if ($ipTV_db_admin->num_rows() == 1) {
                        $rSeries = $ipTV_db_admin->get_row();
                        if (strlen($rSeries["tmdb_id"]) > 0) {
                            $rShow = $rTMDB->getTVShow($rSeries["tmdb_id"]);
                            $rShowData = json_decode($rShow->getJSON(), true);
                            if (isset($rShowData["name"])) {
                                // Get season and episode from filename.
                                $rFilename = pathinfo(json_decode($rStream["stream_source"], true)[0])["filename"];
                                if ($rSettings["release_parser"] == "php") {
                                    $rRelease = new Release($rFilename);
                                    $rReleaseSeason = $rRelease->getSeason();
                                    $rReleaseEpisode = $rRelease->getEpisode();
                                } else {
                                    $rRelease = tmdbParseRelease($rFilename);
                                    $rReleaseSeason = $rRelease["season"];
                                    $rReleaseEpisode = $rRelease["episode"];
                                }
                                if ((!$rReleaseSeason) or (!$rReleaseEpisode)) {
                                    $rReleaseSeason = $rSeriesEpisode["season_num"];
                                    $rReleaseEpisode = $rSeriesEpisode["sort"];
                                }
                                $rTitle = $rShowData["name"] . " - S" . sprintf('%02d', intval($rReleaseSeason)) . "E" . sprintf('%02d', $rReleaseEpisode);
                                $rEpisodes = json_decode($rTMDB->getSeason($rShowData["id"], intval($rReleaseSeason))->getJSON(), true);
                                $rProperties = array();
                                foreach ($rEpisodes["episodes"] as $rEpisode) {
                                    if (intval($rEpisode["episode_number"]) == $rReleaseEpisode) {
                                        if (strlen($rEpisode["still_path"]) > 0) {
                                            $rImage = "https://image.tmdb.org/t/p/w300" . $rEpisode["still_path"];
                                            if ($rSettings["download_images"]) {
                                                $rImage = downloadImage($rImage);
                                            }
                                        }
                                        if (strlen($rEpisode["name"]) > 0) {
                                            $rTitle .= " - " . $rEpisode["name"];
                                        }
                                        $rSeconds = intval($rShowData["episode_run_time"][0]) * 60;
                                        $rProperties = array("tmdb_id" => $rEpisode["id"], "releasedate" => $rEpisode["air_date"], "plot" => $rEpisode["overview"], "duration_secs" => $rSeconds, "duration" => sprintf('%02d:%02d:%02d', ($rSeconds / 3600), ($rSeconds / 60 % 60), $rSeconds % 60), "movie_image" => $rImage, "video" => array(), "audio" => array(), "bitrate" => 0, "rating" => $rEpisode["vote_average"], "season" => $rReleaseSeason);
                                        if (strlen($rProperties["movie_image"][0]) == 0) {
                                            unset($rProperties["movie_image"]);
                                        }
                                        break;
                                    }
                                }
                                $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = 1 WHERE `id` = " . intval($rRow["id"]) . ";");
                                $ipTV_db_admin->query("UPDATE `streams` SET `stream_display_name` = '" . $ipTV_db_admin->escape($rTitle) . "', `movie_properties` = '" . $$ipTV_db_admin->escape(json_encode($rProperties)) . "' WHERE `id` = " . intval($rRow["stream_id"]) . ";");
                                $ipTV_db_admin->query("UPDATE `series_episodes` SET `season_num` = " . intval($rReleaseSeason) . ", `sort` = " . intval($rReleaseEpisode) . " WHERE `stream_id` = " . intval($rRow["stream_id"]) . ";");
                                if (!in_array($rSeries["id"], $rUpdateSeries)) {
                                    $rUpdateSeries[] = $rSeries["id"];
                                }
                            }
                        } else {
                            $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -5 WHERE `id` = " . intval($rRow["id"]) . ";");
                        }
                    } else {
                        $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -4 WHERE `id` = " . intval($rRow["id"]) . ";");
                    }
                } else {
                    $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -3 WHERE `id` = " . intval($rRow["id"]) . ";");
                }
            } else {
                $ipTV_db_admin->query("UPDATE `tmdb_async` SET `status` = -2 WHERE `id` = " . intval($rRow["id"]) . ";");
            }
        }
    }
}

foreach ($rUpdateSeries as $rSeriesID) {
    updateSeries(intval($rSeriesID));
}
