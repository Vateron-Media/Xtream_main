<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")))) { exit; }
if ((isset($_GET["import"])) && (!hasPermissions("adv", "import_movies"))) { exit; }

$rCategories = getCategories("movie");
$rTranscodeProfiles = getTranscodeProfiles();

if (isset($_POST["submit_movie"])) {
    set_time_limit(0);
    ini_set('mysql.connect_timeout', 0);
    ini_set('max_execution_time', 0);
    ini_set('default_socket_timeout', 0);
    if (isset($_POST["edit"])) {
		if (!hasPermissions("adv", "edit_movie")) { exit; }
        $rArray = getStream($_POST["edit"]);
        unset($rArray["id"]);
    } else {
		if (!hasPermissions("adv", "add_movie")) { exit; }
        $rArray = Array("movie_symlink" => 0, "type" => 2, "target_container" => Array("mp4"), "added" => time(), "read_native" => 0, "stream_all" => 0, "redirect_stream" => 1, "direct_source" => 0, "gen_timestamps" => 1, "transcode_attributes" => Array(), "stream_display_name" => "", "stream_source" => Array(), "movie_subtitles" => Array(), "category_id" => 0, "stream_icon" => "", "notes" => "", "custom_sid" => "", "custom_ffmpeg" => "", "transcode_profile_id" => 0, "enable_transcode" => 0, "auto_restart" => "[]", "allow_record" => 0, "rtmp_output" => 0, "epg_id" => null, "channel_id" => null, "epg_lang" => null, "tv_archive_server_id" => 0, "tv_archive_duration" => 0, "delay_minutes" => 0, "external_push" => Array(), "probesize_ondemand" => 128000);
    }
    $rArray["stream_display_name"] = $_POST["stream_display_name"];
    if (strlen($_POST["movie_subtitles"]) > 0) {
        $rSplit = explode(":", $_POST["movie_subtitles"]);
        $rArray["movie_subtitles"] = Array("files" => Array($rSplit[2]), "names" => Array("Subtitles"), "charset" => Array("UTF-8"), "location" => intval($rSplit[1]));
    } else {
        $rArray["movie_subtitles"] = Array();
    }
    $rArray["notes"] = $_POST["notes"];
    if (isset($_POST["target_container"])) {
        $rArray["target_container"] = Array($_POST["target_container"]);
    }
    $rArray["category_id"] = $_POST["category_id"];
    if (isset($_POST["custom_sid"])) {
        $rArray["custom_sid"] = $_POST["custom_sid"];
    }
    $rArray["transcode_profile_id"] = $_POST["transcode_profile_id"];
    if (!$rArray["transcode_profile_id"]) {
        $rArray["transcode_profile_id"] = 0;
    }
    if ($rArray["transcode_profile_id"] > 0) {
        $rArray["enable_transcode"] = 1;
    }
    if (isset($_POST["read_native"])) {
        $rArray["read_native"] = 1;
        unset($_POST["read_native"]);
    } else {
        $rArray["read_native"] = 0;
    }
    if (isset($_POST["movie_symlink"])) {
        $rArray["movie_symlink"] = 1;
        unset($_POST["movie_symlink"]);
    } else {
        $rArray["movie_symlink"] = 0;
    }
    if (isset($_POST["direct_source"])) {
        $rArray["direct_source"] = 1;
        unset($_POST["direct_source"]);
    } else {
        $rArray["direct_source"] = 0;
    }
    if (isset($_POST["remove_subtitles"])) {
        $rArray["remove_subtitles"] = 1;
        unset($_POST["remove_subtitles"]);
    } else {
        $rArray["remove_subtitles"] = 0;
    }
	if (isset($_POST["restart_on_edit"])) {
		$rRestart = true;
		unset($_POST["restart_on_edit"]);
	} else {
		$rRestart = false;
	}
    $rBouquets = $_POST["bouquets"];
    unset($_POST["bouquets"]);
    $rImportStreams = Array();
    if (!empty($_FILES['m3u_file']['tmp_name'])) {
		if (!hasPermissions("adv", "import_movies")) { exit; }
        $rStreamDatabase = Array();
        $result = $db->query("SELECT `stream_source` FROM `streams` WHERE `type` = 2;");
        if (($result) && ($result->num_rows > 0)) {
            while ($row = $result->fetch_assoc()) {
                foreach (json_decode($row["stream_source"], True) as $rSource) {
                    if (strlen($rSource) > 0) {
                        $rStreamDatabase[] = $rSource;
                    }
                }
            }
        }
        $rFile = '';
        if ((!empty($_FILES['m3u_file']['tmp_name'])) && (strtolower(pathinfo($_FILES['m3u_file']['name'], PATHINFO_EXTENSION)) == "m3u")) {
            $rFile = file_get_contents($_FILES['m3u_file']['tmp_name']);
        }
        preg_match_all('/(?P<tag>#EXTINF:[-1,0])|(?:(?P<prop_key>[-a-z]+)=\"(?P<prop_val>[^"]+)")|(?<name>,[^\r\n]+)|(?<url>http[^\s]*:\/\/.*\/.*)/', $rFile, $rMatches);
        $rResults = [];
        $rIndex = -1;
        for ($i = 0; $i < count($rMatches[0]); $i++) {
            $rItem = $rMatches[0][$i];
            if (!empty($rMatches['tag'][$i])) {
                ++$rIndex;
            } elseif (!empty($rMatches['prop_key'][$i])) {
                $rResults[$rIndex][$rMatches['prop_key'][$i]] = trim($rMatches['prop_val'][$i]);
            } elseif (!empty($rMatches['name'][$i])) {
                $rResults[$rIndex]['name'] = trim(substr($rItem, 1));
            } elseif (!empty($rMatches['url'][$i])) {
                $rResults[$rIndex]['url'] = str_replace(" ", "%20", trim($rItem));
            }
        }
        foreach ($rResults as $rResult) {
			if (!in_array($rResult["url"], $rStreamDatabase)) {
                $rPathInfo = pathinfo($rResult["url"]);
                $rImportArray = Array("stream_source" => Array($rResult["url"]), "stream_icon" => $rResult["tvg-logo"] ?: "", "stream_display_name" => $rResult["name"] ?: "", "movie_propeties" => Array(), "async" => true, "target_container" => Array($rPathInfo["extension"]));
				$rImportStreams[] = $rImportArray;
			}
        }
    } else if (!empty($_POST["import_folder"])) {
		if (!hasPermissions("adv", "import_movies")) { exit; }
        $rStreamDatabase = Array();
        $result = $db->query("SELECT `stream_source` FROM `streams` WHERE `type` = 2;");
        if (($result) && ($result->num_rows > 0)) {
            while ($row = $result->fetch_assoc()) {
                foreach (json_decode($row["stream_source"], True) as $rSource) {
                    if (strlen($rSource) > 0) {
                        $rStreamDatabase[] = $rSource;
                    }
                }
            }
        }
        $rParts = explode(":", $_POST["import_folder"]);
        if (is_numeric($rParts[1])) {
            if (isset($_POST["scan_recursive"])) {
                $rFiles = scanRecursive(intval($rParts[1]), $rParts[2], Array("mp4", "mkv", "avi", "mpg", "flv")); // Only these containers are accepted.
            } else {
                $rFiles = Array();
                foreach (listDir(intval($rParts[1]), rtrim($rParts[2], "/"), Array("mp4", "mkv", "avi", "mpg", "flv"))["files"] as $rFile) {
                    $rFiles[] = rtrim($rParts[2], "/")."/".$rFile;
                }
            }
            foreach ($rFiles as $rFile) {
                $rFilePath = "s:".intval($rParts[1]).":".$rFile;
                if (!in_array($rFilePath, $rStreamDatabase)) {
                    $rPathInfo = pathinfo($rFile);
                    $rImportArray = Array("stream_source" => Array($rFilePath), "stream_icon" => "", "stream_display_name" => $rPathInfo["filename"], "movie_propeties" => Array(), "async" => true, "target_container" => Array($rPathInfo["extension"]));
                    $rImportStreams[] = $rImportArray;
                }
            }
        }
    } else {
        $rImportArray = Array("stream_source" => Array($_POST["stream_source"]), "stream_icon" => $rArray["stream_icon"], "stream_display_name" => $rArray["stream_display_name"], "movie_propeties" => Array(), "async" => false);
        if (strlen($_POST["tmdb_id"]) > 0) {
            $rTMDBURL = "https://www.themoviedb.org/movie/".$_POST["tmdb_id"];
        } else {
            $rTMDBURL = "";
        }
        if ($rAdminSettings["download_images"]) {
            $_POST["movie_image"] = downloadImage($_POST["movie_image"]);
            $_POST["backdrop_path"] = downloadImage($_POST["backdrop_path"]);
        }
        $rSeconds = intval($_POST["episode_run_time"]) * 60;
        $rImportArray["movie_propeties"] = Array("kinopoisk_url" => $rTMDBURL, "tmdb_id" => $_POST["tmdb_id"], "name" => $rArray["stream_display_name"], "o_name" => $rArray["stream_display_name"], "cover_big" => $_POST["movie_image"], "movie_image" => $_POST["movie_image"], "releasedate" => $_POST["releasedate"], "episode_run_time" => $_POST["episode_run_time"], "youtube_trailer" => $_POST["youtube_trailer"], "director" => $_POST["director"], "actors" => $_POST["cast"], "cast" => $_POST["cast"], "description" => $_POST["plot"], "plot" => $_POST["plot"], "age" => "", "mpaa_rating" => "", "rating_count_kinopoisk" => 0, "country" => $_POST["country"], "genre" => $_POST["genre"], "backdrop_path" => Array($_POST["backdrop_path"]), "duration_secs" => $rSeconds, "duration" => sprintf('%02d:%02d:%02d', ($rSeconds/3600),($rSeconds/60%60), $rSeconds%60), "video" => Array(), "audio" => Array(), "bitrate" => 0, "rating" => $_POST["rating"]);
        if (strlen($rImportArray["movie_propeties"]["backdrop_path"][0]) == 0) {
            unset($rImportArray["movie_propeties"]["backdrop_path"]);
        }
		if (isset($_POST["edit"])) {
			$rImportStreams[] = $rImportArray;
		} else {
			$rResult = $db->query("SELECT COUNT(`id`) AS `count` FROM `streams` WHERE `stream_display_name` = '".ESC($rImportArray["stream_display_name"])."' AND `type` = 2;");
			if ($rResult->fetch_assoc()["count"] == 0) {
				$rImportStreams[] = $rImportArray;
			} else {
				$_STATUS = 2;
				$rMovie = array_merge($rArray, $rImportArray);
			}
		}
    }
    if (count($rImportStreams) > 0) {
        $rRestartIDs = Array();
        foreach ($rImportStreams as $rImportStream) {
            $rImportArray = $rArray;
            foreach (array_keys($rImportStream) as $rKey) {
				$rImportArray[$rKey] = $rImportStream[$rKey];
            }
            $rImportArray["order"] = getNextOrder();
            $rSync = $rImportArray["async"];
            unset($rImportArray["async"]);
            $rCols = "`".ESC(implode('`,`', array_keys($rImportArray)))."`";
            $rValues = null;
            foreach (array_values($rImportArray) as $rValue) {
                isset($rValues) ? $rValues .= ',' : $rValues = '';
                if (is_array($rValue)) {
                    $rValue = json_encode($rValue);
                }
                if (is_null($rValue)) {
                    $rValues .= 'NULL';
                } else {
                    $rValues .= '\''.ESC($rValue).'\'';
                }
            }
            if (isset($_POST["edit"])) {
                $rCols = "`id`,".$rCols;
                $rValues = ESC($_POST["edit"]).",".$rValues;
            }
            $rQuery = "REPLACE INTO `streams`(".$rCols.") VALUES(".$rValues.");";
            if ($db->query($rQuery)) {
                if (isset($_POST["edit"])) {
                    $rInsertID = intval($_POST["edit"]);
                } else {
                    $rInsertID = $db->insert_id;
                }
                $rStreamExists = Array();
                if (isset($_POST["edit"])) {
                    $result = $db->query("SELECT `server_stream_id`, `server_id` FROM `streams_sys` WHERE `stream_id` = ".intval($rInsertID).";");
                    if (($result) && ($result->num_rows > 0)) {
                        while ($row = $result->fetch_assoc()) {
                            $rStreamExists[intval($row["server_id"])] = intval($row["server_stream_id"]);
                        }
                    }
                }
                if (isset($_POST["server_tree_data"])) {
                    $rStreamsAdded = Array();
                    $rServerTree = json_decode($_POST["server_tree_data"], True);
                    foreach ($rServerTree as $rServer) {
                        if ($rServer["parent"] <> "#") {
                            $rServerID = intval($rServer["id"]);
                            $rStreamsAdded[] = $rServerID;
                            if ($rServer["parent"] == "source") {
                                $rParent = "NULL";
                            } else {
                                $rParent = intval($rServer["parent"]);
                            }
                            if (isset($rStreamExists[$rServerID])) {
                                $db->query("UPDATE `streams_sys` SET `parent_id` = ".$rParent.", `on_demand` = 0 WHERE `server_stream_id` = ".$rStreamExists[$rServerID].";");
                            } else {
                                $db->query("INSERT INTO `streams_sys`(`stream_id`, `server_id`, `parent_id`, `on_demand`) VALUES(".intval($rInsertID).", ".$rServerID.", ".$rParent.", 0);");
                            }
                        }
                    }
                    foreach ($rStreamExists as $rServerID => $rDBID) {
                        if (!in_array($rServerID, $rStreamsAdded)) {
                            $db->query("DELETE FROM `streams_sys` WHERE `server_stream_id` = ".$rDBID.";");
                        }
                    }
                }
                if ($rRestart) {
                    $rRestartIDs[] = $rInsertID;
                }
                foreach ($rBouquets as $rBouquet) {
                    addToBouquet("stream", $rBouquet, $rInsertID);
                }
                foreach (getBouquets() as $rBouquet) {
                    if (!in_array($rBouquet["id"], $rBouquets)) {
                        removeFromBouquet("stream", $rBouquet["id"], $rInsertID);
                    }
                }
                if ($rSync) {
                    // Sync TMDb in background.
                    $db->query("INSERT INTO `tmdb_async`(`type`, `stream_id`) VALUES(1, ".intval($rInsertID).");");
                }
            }
        }
        scanBouquets();
        if ($rRestart) {
            APIRequest(Array("action" => "vod", "sub" => "start", "stream_ids" => $rRestartIDs));
        }
        if (isset($_FILES["m3u_file"])) {
            header("Location: ./movies.php");exit;
        } else if (!isset($_GET["id"])) {
            header("Location: ./movie.php?id=".$rInsertID); exit;
        }
    } else {
        if (!isset($_STATUS)) {
			$_STATUS = 3;
            $rMovie = $rArray;
		}
    }
}

$rServerTree = Array();
$rServerTree[] = Array("id" => "source", "parent" => "#", "text" => "<strong>".$_["stream_source"]."</strong>", "icon" => "mdi mdi-youtube-tv", "state" => Array("opened" => true));
if (isset($_GET["id"])) {
    if ((isset($_GET["import"])) OR (!hasPermissions("adv", "edit_movie"))) { exit; }
    $rMovie = getStream($_GET["id"]);
    if ((!$rMovie) or ($rMovie["type"] <> 2)) {
        exit;
    }
    $rMovie["properties"] = json_decode($rMovie["movie_propeties"], True);
    $rStreamSys = getStreamSys($_GET["id"]);
    foreach ($rServers as $rServer) {
        if (isset($rStreamSys[intval($rServer["id"])])) {
            if ($rStreamSys[intval($rServer["id"])]["parent_id"] <> 0) {
                $rParent = intval($rStreamSys[intval($rServer["id"])]["parent_id"]);
            } else {
                $rParent = "source";
            }
        } else {
            $rParent = "#";
        }
        $rServerTree[] = Array("id" => $rServer["id"], "parent" => $rParent, "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
    }
} else {
	if (!hasPermissions("adv", "add_movie")) { exit; }
    foreach ($rServers as $rServer) {
        $rServerTree[] = Array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
    }
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="./movies.php<?php if (isset($_GET["category"])) { echo "?category=".$_GET["category"]; } ?>">
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                <?=$_["view_movies"]?>
                                            </button>
                                        </a>
                                        <?php if (!isset($_GET["import"])) { ?>
                                        <a href="./movie.php?import">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <?=$_["import_multiple"]?>
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="./movie.php">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <?=$_["add_single"]?>
                                            </button>
                                        </a>
                                        <?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rMovie["id"])) { echo $rMovie["stream_display_name"].' &nbsp;<button type="button" class="btn btn-outline-info waves-effect waves-light btn-xs" onClick="player('.$rMovie["id"].', \''.json_decode($rMovie["target_container"], True)[0].'\');"><i class="mdi mdi-play"></i></button>'; } else if (isset($_GET["import"])) { echo $_["import_movies"]; } else { echo $_["add_movie"]; } ?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["movies_info_1"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["movies_info_2"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["movies_info_3"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["movies_info_4"]?>
                        </div>
                        <?php }
                        if (isset($rMovie["id"])) { ?>
                        <div class="card text-xs-center">
                            <div class="table">
                                <table id="datatable-list" class="table table-borderless mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th><?=$_["server"]?></th>
                                            <th><?=$_["clients"]?></th>
                                            <th><?=$_["status"]?></th>
                                            <th><?=$_["actions"]?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center"><?=$_["loading_movie_information"]?>...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php $rEncodeErrors = getEncodeErrors($rMovie["id"]);
                        foreach ($rEncodeErrors as $rServerID => $rEncodeError) { ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong><?=$_["error_on_server"]?> - <?=$rServers[$rServerID]["server_name"]?></strong><br/>
                            <?=str_replace("\n", "<br/>", $rEncodeError)?>
                        </div>
                        <?php } } ?>
                        <div class="card">
                            <div class="card-body">
                                <form<?php if(isset($_GET["import"])) { echo " enctype=\"multipart/form-data\""; } ?> action="./movie.php<?php if (isset($_GET["import"])) { echo "?import"; } else if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="stream_form" data-parsley-validate="">
                                    <?php if (isset($rMovie["id"])) { ?>
                                    <input type="hidden" name="edit" value="<?=$rMovie["id"]?>" />
                                    <?php } ?>
                                    <input type="hidden" id="tmdb_id" name="tmdb_id" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["tmdb_id"]); } ?>" />
                                    <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#stream-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["details"]?></span>
                                                </a>
                                            </li>
                                            <?php if (!isset($_GET["import"])) { ?>
                                            <li class="nav-item">
                                                <a href="#movie-information" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-movie-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["information"]?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <li class="nav-item">
                                                <a href="#advanced-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["advanced"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#load-balancing" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-server-network mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["server"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="stream-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <?php if (!isset($_GET["import"])) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_display_name"><?=$_["movie_name"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="stream_display_name" name="stream_display_name" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["stream_display_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="tmdb_search"><?=$_["tmdb_results"]?></label>
                                                            <div class="col-md-8">
                                                                <select id="tmdb_search" class="form-control" data-toggle="select2"></select>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        if (isset($rMovie)) {
                                                            $rMovieSource = json_decode($rMovie["stream_source"], True)[0];
                                                        } else {
                                                            $rMovieSource = "";
                                                        } ?>
                                                        <div class="form-group row mb-4 stream-url">
                                                            <label class="col-md-4 col-form-label" for="stream_source"><?=$_["movie_path_or_url"]?></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" id="stream_source" name="stream_source" class="form-control" value="<?=$rMovieSource?>" required data-parsley-trigger="change">
                                                                <div class="input-group-append">
                                                                    <a href="#file-browser" id="filebrowser" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-folder-open-outline"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php } else { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="import_type"><?=$_["type"]?></label>
                                                            <div class="col-md-8">
                                                                <div class="custom-control custom-radio mt-1">
                                                                    <span>
                                                                        <input type="radio" id="import_type_1" name="customRadio" class="custom-control-input" checked>
                                                                        <label class="custom-control-label" for="import_type_1"><?=$_["m3u"]?></label>
                                                                    </span>
                                                                    <span style="padding-left:50px;">
                                                                        <input type="radio" id="import_type_2" name="customRadio" class="custom-control-input">
                                                                        <label class="custom-control-label" for="import_type_2"><?=$_["folder"]?></label>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="import_m3uf_toggle">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="m3u_file"><?=$_["m3u_file"]?></label>
                                                                <div class="col-md-8">
                                                                    <input type="file" id="m3u_file" name="m3u_file" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="import_folder_toggle" style="display:none;">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="import_folder"><?=$_["folder"]?></label>
                                                                <div class="col-md-8 input-group">
                                                                    <input type="text" id="import_folder" name="import_folder" class="form-control" value="<?=$rMovieSource?>">
                                                                    <div class="input-group-append">
                                                                        <a href="#file-browser" id="filebrowser" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-folder-open-outline"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="scan_recursive"><?=$_["scan_recursively"]?></label>
                                                                <div class="col-md-2">
                                                                    <input name="scan_recursive" id="scan_recursive" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_id"><?=$_["category_name"]?></label>
                                                            <div class="col-md-8">
                                                                <select name="category_id" id="category_id" class="form-control" data-toggle="select2">
                                                                    <?php foreach ($rCategories as $rCategory) { ?>
                                                                    <option <?php if (isset($rMovie)) { if (intval($rMovie["category_id"]) == intval($rCategory["id"])) { echo "selected "; } } else if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo "selected "; } ?>value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="bouquets"><?=$_["add_to_bouquets"]?></label>
                                                            <div class="col-md-8">
                                                                <select name="bouquets[]" id="bouquets" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="<?=$_["choose"]?>...">
                                                                    <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                    <option <?php if (isset($rMovie)) { if (in_array($rMovie["id"], json_decode($rBouquet["bouquet_channels"], True))) { echo "selected "; } } ?>value="<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="notes"><?=$_["notes"]?></label>
                                                            <div class="col-md-8">
                                                                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder=""><?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["notes"]); } ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="movie-information">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="movie_image"><?=$_["poster_url"]?></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" class="form-control" id="movie_image" name="movie_image" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["movie_image"]); } ?>">
                                                                <div class="input-group-append">
                                                                    <a href="javascript:void(0)" onClick="openImage(this)" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-eye"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="backdrop_path"><?=$_["backdrop_url"]?></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" class="form-control" id="backdrop_path" name="backdrop_path" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["backdrop_path"][0]); } ?>">
                                                                <div class="input-group-append">
                                                                    <a href="javascript:void(0)" onClick="openImage(this)" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-eye"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="plot"><?=$_["plot"]?></label>
                                                            <div class="col-md-8">
                                                                <textarea rows="6" class="form-control" id="plot" name="plot"><?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["plot"]); } ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="cast"><?=$_["cast"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="cast" name="cast" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["cast"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="director"><?=$_["director"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="director" name="director" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["director"]); } ?>">
                                                            </div>
                                                            <label class="col-md-2 col-form-label" for="genre"><?=$_["genres"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="genre" name="genre" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["genre"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="releasedate"><?=$_["release_date"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="releasedate" name="releasedate" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["releasedate"]); } ?>">
                                                            </div>
                                                            <label class="col-md-2 col-form-label" for="episode_run_time"><?=$_["runtime"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="episode_run_time" name="episode_run_time" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["episode_run_time"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="youtube_trailer"><?=$_["youtube_trailer"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="youtube_trailer" name="youtube_trailer" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["youtube_trailer"]); } ?>">
                                                            </div>
                                                            <label class="col-md-2 col-form-label" for="rating"><?=$_["rating"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="rating" name="rating" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["rating"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="country"><?=$_["country"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="country" name="country" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["properties"]["country"]); } ?>">
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="advanced-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="direct_source"><?=$_["direct_source"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_1"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="direct_source" id="direct_source" type="checkbox" <?php if (isset($rMovie)) { if ($rMovie["direct_source"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="read_native"><?=$_["native_frames"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="read_native" id="read_native" type="checkbox" <?php if (isset($rMovie)) { if ($rMovie["read_native"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="movie_symlink"><?=$_["create_symlink"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_2"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="movie_symlink" id="movie_symlink" type="checkbox" <?php if (isset($rMovie)) { if ($rMovie["movie_symlink"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <?php if (!isset($_GET["import"])) { ?>
                                                            <label class="col-md-4 col-form-label" for="custom_sid"><?=$_["custom_channel_sid"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_5"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="custom_sid" name="custom_sid" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rMovie["custom_sid"]); } ?>">
                                                            </div>
                                                            <?php } else { ?>
                                                            <label class="col-md-4 col-form-label" for="remove_subtitles"><?=$_["remove_existing_subtitles"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_3"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="remove_subtitles" id="remove_subtitles" type="checkbox" <?php if (isset($rMovie)) { if ($rMovie["remove_subtitles"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                        <?php if (!isset($_GET["import"])) {
                                                        $rSubFile = "";
                                                        if (isset($rMovie)) {
                                                            $rSubData = json_decode($rMovie["movie_subtitles"], True);
                                                            if (isset($rSubData["location"])) {
                                                                $rSubFile = "s:".$rSubData["location"].":".$rSubData["files"][0];
                                                            }
                                                        }
                                                        ?>
                                                        <div class="form-group row mb-4 stream-url">
                                                            <label class="col-md-4 col-form-label" for="movie_subtitles"><?=$_["subtitle_location"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_6"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" id="movie_subtitles" name="movie_subtitles" class="form-control" value="<?php if (isset($rMovie)) { echo htmlspecialchars($rSubFile); } ?>">
                                                                <div class="input-group-append">
                                                                    <a href="#file-browser" id="filebrowser-sub" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-folder-open-outline"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="transcode_profile_id"><?=$_["transcoding_profile"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_7"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8">
                                                                <select name="transcode_profile_id" id="transcode_profile_id" class="form-control" data-toggle="select2">
                                                                    <option <?php if (isset($rMovie)) { if (intval($rMovie["transcode_profile_id"]) == 0) { echo "selected "; } } ?>value="0"><?=$_["transcoding_disabled"]?></option>
                                                                    <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                    <option <?php if (isset($rMovie)) { if (intval($rMovie["transcode_profile_id"]) == intval($rProfile["profile_id"])) { echo "selected "; } } ?>value="<?=$rProfile["profile_id"]?>"><?=$rProfile["profile_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <?php if (!isset($_GET["import"])) { ?>
                                                            <label class="col-md-4 col-form-label" for="target_container"><?=$_["target_container"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_4"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <select name="target_container" id="target_container" class="form-control" data-toggle="select2">
                                                                    <?php foreach (Array("mp4", "mkv", "avi", "mpg", "flv") as $rContainer) { ?>
                                                                    <option <?php if (isset($rMovie)) { if (json_decode($rMovie["target_container"], True)[0] == $rContainer) { echo "selected "; } } ?>value="<?=$rContainer?>"><?=$rContainer?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="remove_subtitles"><?=$_["remove_existing_subtitles"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title=<?=$_["episode_tooltip_3"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="remove_subtitles" id="remove_subtitles" type="checkbox" <?php if (isset($rMovie)) { if ($rMovie["remove_subtitles"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="load-balancing">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="servers"><?=$_["server_tree"]?></label>
                                                            <div class="col-md-8">
                                                                <div id="server_tree"></div>
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
															<label class="col-md-4 col-form-label" for="restart_on_edit"><?php if (isset($rMovie)) { ?><?=$_["reprocess_on_edit"]?><?php } else { ?><?=$_["process_movie"]?><?php } ?></label>
															<div class="col-md-2">
																<input name="restart_on_edit" id="restart_on_edit" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
															</div>
														</div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <input name="submit_movie" type="submit" class="btn btn-primary" value="<?php if (isset($rMovie)) { echo "Edit"; } else { echo "Add"; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>
                                <div id="file-browser" class="mfp-hide white-popup-block">
                                    <div class="col-12">
                                        <div class="form-group row mb-4">
                                            <label class="col-md-4 col-form-label" for="server_id"><?=$_["server_name"]?></label>
                                            <div class="col-md-8">
                                                <select id="server_id" class="form-control" data-toggle="select2">
                                                    <?php foreach (getStreamingServers() as $rServer) { ?>
                                                    <option value="<?=$rServer["id"]?>"<?php if ((isset($_GET["server"])) && ($_GET["server"] == $rServer["id"])) { echo " selected"; } ?>><?=htmlspecialchars($rServer["server_name"])?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-md-4 col-form-label" for="current_path"><?=$_["current_path"]?></label>
                                            <div class="col-md-8 input-group">
                                                <input type="text" id="current_path" name="current_path" class="form-control" value="/">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary waves-effect waves-light" type="button" id="changeDir"><i class="mdi mdi-chevron-right"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!isset($_GET["import"])) { ?>
                                        <div class="form-group row mb-4">
                                            <label class="col-md-4 col-form-label" for="search"><?=$_["search_directory"]?></label>
                                            <div class="col-md-8 input-group">
                                                <input type="text" id="search" name="search" class="form-control" placeholder="<?=$_["filter_files"]?>...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-warning waves-effect waves-light" type="button" onClick="clearSearch()"><i class="mdi mdi-close"></i></button>
                                                    <button class="btn btn-primary waves-effect waves-light" type="button" id="doSearch"><i class="mdi mdi-magnify"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="form-group row mb-4">
                                            <div class="col-md-6">
                                                <table id="datatable" class="table">
                                                    <thead>
                                                        <tr>
                                                            <th width="20px"></th>
                                                            <th><?=$_["directory"]?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table id="datatable-files" class="table">
                                                    <thead>
                                                        <tr>
                                                            <th width="20px"></th>
                                                            <th><?=$_["filename"]?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <?php if (isset($_GET["import"])) { ?>
                                        <div class="float-right">
                                            <input id="select_folder" type="button" class="btn btn-info" value="Select" />
                                        </div>
                                        <?php } ?>
                                    </div> <!-- end col -->
                                </div>
                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 copyright text-center"><?=getFooter()?></div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables/buttons.flash.min.js"></script>
        <script src="assets/libs/datatables/buttons.print.min.js"></script>
        <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="assets/libs/datatables/dataTables.select.min.js"></script>
        <script src="assets/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var changeTitle = false;
        var rSwitches = [];
        
        (function($) {
          $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
              if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
              } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
              }
            });
          };
        }(jQuery));
        
        function api(rID, rServerID, rType) {
            if (rType == "delete") {
                if (confirm('<?=$_["movie_delete_confirm"]?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=movie&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
                if (data.result == true) {
                    if (rType == "start") {
                        $.toast("<?=$_["movie_encode_started"]?>");
                    } else if (rType == "stop") {
                        $.toast("<?=$_["movie_encode_stopped"]?>");
                    } else if (rType == "delete") {
                        $("#movie-" + rID + "-" + rServerID).remove();
                        $.toast("<?=$_["movie_delete_confirmed"]?>");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $("#datatable-list").DataTable().ajax.reload( null, false );
                } else {
                    $.toast("<?=$_["error_occured"]?>");
                }
            }).fail(function() {
                $.toast("<?=$_["error_occured"]?>");
            });
        }
        function selectDirectory(elem) {
            window.currentDirectory += elem + "/";
            $("#current_path").val(window.currentDirectory);
            $("#changeDir").click();
        }
        function selectParent() {
            $("#current_path").val(window.currentDirectory.split("/").slice(0,-2).join("/") + "/");
            $("#changeDir").click();
        }
        function selectFile(rFile) {
            if ($('li.nav-item .active').attr('href') == "#stream-details") {
                $("#stream_source").val("s:" + $("#server_id").val() + ":" + window.currentDirectory + rFile);
                var rExtension = rFile.substr((rFile.lastIndexOf('.')+1));
                if ($("#target_container option[value='" + rExtension + "']").length > 0) {
                    $("#target_container").val(rExtension).trigger('change');
                }
            } else {
                $("#movie_subtitles").val("s:" + $("#server_id").val() + ":" + window.currentDirectory + rFile);
            }
            $.magnificPopup.close();
        }
        function openImage(elem) {
            rPath = $(elem).parent().parent().find("input").val();
            if (rPath.length > 0) {
                if (rPath.substring(0,1) == ".") {
                    window.open('<?=getURL()?>' + rPath.substring(1, rPath.length));
                } else if (rPath.substring(0,1) == "/") {
                    window.open('<?=getURL()?>' + rPath);
                } else {
                    window.open(rPath);
                }
            }
        }
        function reloadStream() {
            $("#datatable-list").DataTable().ajax.reload( null, false );
            setTimeout(reloadStream, 5000);
        }
        function clearSearch() {
            $("#search").val("");
            $("#doSearch").click();
        }
        function player(rID, rContainer) {
            $.magnificPopup.open({
                items: {
                    src: "./player.php?type=movie&id=" + rID + "&container=" + rContainer,
                    type: 'iframe'
                }
            });
        }
        function setSwitch(switchElement, checkedBool) {
            if((checkedBool && !switchElement.isChecked()) || (!checkedBool && switchElement.isChecked())) {
                switchElement.setPosition(true);
                switchElement.handleOnchange(true);
            }
        }
        $(document).ready(function() {
            $('select').select2({width: '100%'});
            
            $("#datatable").DataTable({
                responsive: false,
                paging: false,
                bInfo: false,
                searching: false,
                scrollY: "250px",
                columnDefs: [
                    {"className": "dt-center", "targets": [0]},
                ],
                "language": {
                    "emptyTable": ""
                }
            });
            
            $("#datatable-files").DataTable({
                responsive: false,
                paging: false,
                bInfo: false,
                searching: true,
                scrollY: "250px",
                columnDefs: [
                    {"className": "dt-center", "targets": [0]},
                ],
                "language": {
                    "emptyTable": "<?=$_["no_compatible_file"]?>"
                }
            });
            
            $("#doSearch").click(function() {
                $('#datatable-files').DataTable().search($("#search").val()).draw();
            })
            
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
              var switchery = new Switchery(html);
              window.rSwitches[$(html).attr("id")] = switchery;
            });
            
            $("#select_folder").click(function() {
                $("#import_folder").val("s:" + $("#server_id").val() + ":" + window.currentDirectory);
                $.magnificPopup.close();
            });
            
            $("#changeDir").click(function() {
                $("#search").val("");
                window.currentDirectory = $("#current_path").val();
                if (window.currentDirectory.substr(-1) != "/") {
                    window.currentDirectory += "/";
                }
                $("#current_path").val(window.currentDirectory);
                $("#datatable").DataTable().clear();
                $("#datatable").DataTable().row.add(["", "<?=$_["loading"]?>..."]);
                $("#datatable").DataTable().draw(true);
                $("#datatable-files").DataTable().clear();
                $("#datatable-files").DataTable().row.add(["", "<?=$_["please_wait"]?>..."]);
                $("#datatable-files").DataTable().draw(true);
                if ($('li.nav-item .active').attr('href') == "#stream-details") {
                    rFilter = "video";
                } else {
                    rFilter = "subs";
                }
                $.getJSON("./api.php?action=listdir&dir=" + window.currentDirectory + "&server=" + $("#server_id").val() + "&filter=" + rFilter, function(data) {
                    $("#datatable").DataTable().clear();
                    $("#datatable-files").DataTable().clear();
                    if (window.currentDirectory != "/") {
                        $("#datatable").DataTable().row.add(["<i class='mdi mdi-subdirectory-arrow-left'></i>", "<?=$_["parent_directory"]?>"]);
                    }
                    if (data.result == true) {
                        $(data.data.dirs).each(function(id, dir) {
                            $("#datatable").DataTable().row.add(["<i class='mdi mdi-folder-open-outline'></i>", dir]);
                        });
                        $("#datatable").DataTable().draw(true);
                        $(data.data.files).each(function(id, dir) {
                            $("#datatable-files").DataTable().row.add(["<i class='mdi mdi-file-video'></i>", dir]);
                        });
                        $("#datatable-files").DataTable().draw(true);
                    }
                });
            });
            
            $('#datatable').on('click', 'tbody > tr', function() {
                if ($(this).find("td").eq(1).html() == "<?=$_["parent_directory"]?>") {
                    selectParent();
                } else {
                    selectDirectory($(this).find("td").eq(1).html());
                }
            });
            $('#datatable-files').on('click', 'tbody > tr', function() {
                selectFile($(this).find("td").eq(1).html());
            });
            
            $('#server_tree').jstree({ 'core' : {
                'check_callback': function (op, node, parent, position, more) {
                    switch (op) {
                        case 'move_node':
                            if (node.id == "source") { return false; }
                            return true;
                    }
                },
                'data' : <?=json_encode($rServerTree)?>
            }, "plugins" : [ "dnd" ]
            });
            
            $("#stream_form").submit(function(e){
                <?php if (!isset($_GET["import"])) { ?>
                if ($("#stream_display_name").val().length == 0) {
                    e.preventDefault();
                    $.toast("<?=$_["enter_movie_name"]?>");
                }
                if ($("#stream_source").val().length == 0) {
                    e.preventDefault();
                    $.toast("<?=$_["enter_movie_source"]?>");
                }
                <?php } else { ?>
                if (($("#m3u_file").val().length == 0) && ($("#import_folder").val().length == 0)) {
                    e.preventDefault();
                    $.toast("<?=$_["select_m3u_file"]?>");
                }
                <?php } ?>
                $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {flat:true})));
            });
            
            $("#filebrowser").magnificPopup({
                type: 'inline',
                preloader: false,
                focus: '#server_id',
                callbacks: {
                    beforeOpen: function() {
                        if ($(window).width() < 830) {
                            this.st.focus = false;
                        } else {
                            this.st.focus = '#server_id';
                        }
                    }
                }
            });
            $("#filebrowser-sub").magnificPopup({
                type: 'inline',
                preloader: false,
                focus: '#server_id',
                callbacks: {
                    beforeOpen: function() {
                        if ($(window).width() < 830) {
                            this.st.focus = false;
                        } else {
                            this.st.focus = '#server_id';
                        }
                    }
                }
            });
            
            $("#filebrowser").on("mfpOpen", function() {
                clearSearch();
                $($.fn.dataTable.tables(true)).css('width', '100%');
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
            });
            $("#filebrowser-sub").on("mfpOpen", function() {
                clearSearch();
                $($.fn.dataTable.tables(true)).css('width', '100%');
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
            });
            
            $(document).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            
            $("#server_id").change(function() {
                $("#current_path").val("/");
                $("#changeDir").click();
            });
            
            $("#direct_source").change(function() {
                evaluateDirectSource();
            });
            $("#movie_symlink").change(function() {
                evaluateSymlink();
            });
            
            function evaluateDirectSource() {
                $(["movie_symlink", "read_native", "transcode_profile_id", "target_container", "remove_subtitles", "movie_subtitles"]).each(function(rID, rElement) {
                    if ($(rElement)) {
                        if ($("#direct_source").is(":checked")) {
                            if (window.rSwitches[rElement]) {
                                setSwitch(window.rSwitches[rElement], false);
                                window.rSwitches[rElement].disable();
                            } else {
                                $("#" + rElement).prop("disabled", true);
                            }
                        } else {
                            if (window.rSwitches[rElement]) {
                                window.rSwitches[rElement].enable();
                            } else {
                                $("#" + rElement).prop("disabled", false);
                            }
                        }
                    }
                });
            }
            function evaluateSymlink() {
                $(["direct_source", "read_native", "transcode_profile_id"]).each(function(rID, rElement) {
                    if ($(rElement)) {
                        if ($("#movie_symlink").is(":checked")) {
                            if (window.rSwitches[rElement]) {
                                setSwitch(window.rSwitches[rElement], false);
                                window.rSwitches[rElement].disable();
                            } else {
                                $("#" + rElement).prop("disabled", true);
                            }
                        } else {
                            if (window.rSwitches[rElement]) {
                                window.rSwitches[rElement].enable();
                            } else {
                                $("#" + rElement).prop("disabled", false);
                            }
                        }
                    }
                });
            }
            
            $("#stream_display_name").change(function() {
                if (!window.changeTitle) {
                    $("#tmdb_search").empty().trigger('change');
                    if ($("#stream_display_name").val().length > 0) {
                        $.getJSON("./api.php?action=tmdb_search&type=movie&term=" + $("#stream_display_name").val(), function(data) {
                            if (data.result == true) {
                                if (data.data.length > 0) {
                                    newOption = new Option("<?=$_["found_results"]?>".replace('{num}', data.data.length), -1, true, true);
                                } else {
                                    newOption = new Option("<?=$_["no_results_found"]?>", -1, true, true);
                                }
                                $("#tmdb_search").append(newOption).trigger('change');
                                $(data.data).each(function(id, item) {
                                    if (item.release_date.length > 0) {
                                        rTitle = item.title + " (" + item.release_date.substring(0, 4) + ")";
                                    } else {
                                        rTitle = item.title;
                                    }
                                    newOption = new Option(rTitle, item.id, true, true);
                                    $("#tmdb_search").append(newOption);
                                });
                            } else {
                                newOption = new Option("<?=$_["no_results_found"]?>", -1, true, true);
                            }
                            $("#tmdb_search").val(-1).trigger('change');
                        });
                    }
                } else {
                    window.changeTitle = false;
                }
            });
            $("#tmdb_search").change(function() {
                if (($("#tmdb_search").val()) && ($("#tmdb_search").val() > -1)) {
                    $.getJSON("./api.php?action=tmdb&type=movie&id=" + $("#tmdb_search").val(), function(data) {
                        if (data.result == true) {
                            window.changeTitle = true;
                            rTitle = data.data.title;
                            if (data.data.release_date) {
                                rTitle += " (" + data.data.release_date.substr(0, 4) + ")";
                            }
                            $("#stream_display_name").val(rTitle);
                            $("#movie_image").val("");
                            if (data.data.poster_path.length > 0) {
                                $("#movie_image").val("https://image.tmdb.org/t/p/w600_and_h900_bestv2" + data.data.poster_path);
                            }
                            $("#backdrop_path").val("");
                            if (data.data.backdrop_path.length > 0) {
                                $("#backdrop_path").val("https://image.tmdb.org/t/p/w1280" + data.data.backdrop_path);
                            }
                            $("#releasedate").val(data.data.release_date);
                            $("#episode_run_time").val(data.data.runtime);
                            $("#youtube_trailer").val("");
                            if (data.data.trailer) {
                                $("#youtube_trailer").val(data.data.trailer);
                            }
                            rCast = "";
                            rMemberID = 0;
                            $(data.data.credits.cast).each(function(id, member) {
                                rMemberID += 1;
                                if (rMemberID <= 5) {
                                    if (rCast.length > 0) {
                                        rCast += ", ";
                                    }
                                    rCast += member.name;
                                }
                            });
                            $("#cast").val(rCast);
                            rGenres = "";
                            rGenreID = 0;
                            $(data.data.genres).each(function(id, genre) {
                                rGenreID += 1;
                                if (rGenreID <= 3) {
                                    if (rGenres.length > 0) {
                                        rGenres += ", ";
                                    }
                                    rGenres += genre.name;
                                }
                            });
                            $("#genre").val(rGenres);
                            $("#director").val("");
                            $(data.data.credits.crew).each(function(id, member) {
                                if (member.department == "Directing") {
                                    $("#director").val(member.name);
                                    return true;
                                }
                            });
                            $("#country").val("");
                            $("#plot").val(data.data.overview);
                            if (data.data.production_countries.length > 0) {
                                $("#country").val(data.data.production_countries[0].name);
                            }
                            $("#rating").val(data.data.vote_average);
                            $("#tmdb_id").val(data.data.id);
                        }
                    });
                }
            });
            
            <?php if (isset($rMovie["id"])) { ?>
            $("#datatable-list").DataTable({
                ordering: false,
                paging: false,
                searching: false,
                processing: true,
                serverSide: true,
                bInfo: false,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "movies";
                        d.stream_id = <?=$rMovie["id"]?>;
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [2,3,4,5]},
                    {"visible": false, "targets": [0,1,6,7]}
                ],
            });
            setTimeout(reloadStream, 5000);
            $("#stream_display_name").trigger('change');
            <?php } ?>
            
            $("#import_type_1").click(function() {
                $("#import_m3uf_toggle").show();
                $("#import_folder_toggle").hide();
            });
            $("#import_type_2").click(function() {
                $("#import_m3uf_toggle").hide();
                $("#import_folder_toggle").show();
            });
            
            $("#runtime").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            
            $("#changeDir").click();
            evaluateDirectSource();
            evaluateSymlink();
        });
        </script>
    </body>
</html>