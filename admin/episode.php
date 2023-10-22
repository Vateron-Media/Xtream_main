<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")))) { exit; }

$rTranscodeProfiles = getTranscodeProfiles();

if (isset($_POST["submit_stream"])) {
    if (isset($_POST["edit"])) {
		if (!hasPermissions("adv", "edit_episode")) { exit; }
        $rArray = getStream($_POST["edit"]);
        unset($rArray["id"]);
    } else {
		if (!hasPermissions("adv", "add_episode")) { exit; }
        $rArray = Array("movie_symlink" => 0, "type" => 5, "target_container" => Array("mp4"), "added" => time(), "read_native" => 0, "stream_all" => 0, "redirect_stream" => 1, "direct_source" => 0, "gen_timestamps" => 1, "transcode_attributes" => Array(), "stream_display_name" => "", "stream_source" => Array(), "movie_subtitles" => Array(), "category_id" => null, "stream_icon" => "", "notes" => "", "custom_sid" => "", "custom_ffmpeg" => "", "transcode_profile_id" => 0, "enable_transcode" => 0, "auto_restart" => "[]", "allow_record" => 0, "rtmp_output" => 0, "epg_id" => null, "channel_id" => null, "epg_lang" => null, "tv_archive_server_id" => 0, "tv_archive_duration" => 0, "delay_minutes" => 0, "external_push" => Array(), "probesize_ondemand" => 128000);
    }
    $rArray["stream_display_name"] = $_POST["stream_display_name"];
    $rArray["stream_source"] = Array($_POST["stream_source"]);
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
    $rProcessArray = Array();
    if (isset($_POST["multi"])) {
		if (!hasPermissions("adv", "import_episodes")) { exit; }
        set_time_limit(0);
        include "tmdb.php";
        $rSeries = getSerie(intval($_POST["series"]));
        if (strlen($rAdminSettings["tmdb_language"]) > 0) {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rAdminSettings["tmdb_language"]);
        } else {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
        }
        $rJSON = json_decode($rTMDB->getSeason($_POST["tmdb_id"], intval($_POST["season_num"]))->getJSON(), True);
        foreach ($_POST as $rKey => $rFilename) {
            $rSplit = explode("_", $rKey);
            if (($rSplit[0] == "episode") && ($rSplit[2] == "name")) {
                if (strlen($_POST["episode_".$rSplit[1]."_num"]) > 0) {
                    $rImportArray = Array("filename" => "", "properties" => Array(), "name" => "", "episode" => 0, "target_container" => Array());
                    $rEpisodeNum = intval($_POST["episode_".$rSplit[1]."_num"]);
                    $rImportArray["filename"] = "s:".$_POST["server"].":".$_POST["season_folder"].$rFilename;
                    $rImage = "";
                    if ((isset($_POST["addName1"])) && (isset($_POST["addName2"]))) {
                        $rImportArray["name"] = $rSeries["title"]." - S".sprintf('%02d', intval($_POST["season_num"]))."E".sprintf('%02d', $rEpisodeNum)." - ";
                    } else if (isset($_POST["addName1"])) {
                        $rImportArray["name"] = $rSeries["title"]." - ";
                    } else if (isset($_POST["addName2"])) {
                        $rImportArray["name"] = "S".sprintf('%02d', intval($_POST["season_num"]))."E".sprintf('%02d', $rEpisodeNum)." - ";
                    }
                    $rImportArray["episode"] = $rEpisodeNum;
                    foreach ($rJSON["episodes"] as $rEpisode) {
                        if (intval($rEpisode["episode_number"]) == $rEpisodeNum) {
                            if (strlen($rEpisode["still_path"]) > 0) {
                                $rImage = "https://image.tmdb.org/t/p/w600_and_h900_bestv2".$rEpisode["still_path"];
                                if ($rAdminSettings["download_images"]) {
                                    $rImage = downloadImage($rImage);
                                }
                            }
                            $rImportArray["name"] .= $rEpisode["name"];
                            $rSeconds = intval($rSeries["episode_run_time"]) * 60;
                            $rImportArray["properties"] = Array("tmdb_id" => $rEpisode["id"], "releasedate" => $rEpisode["air_date"], "plot" => $rEpisode["overview"], "duration_secs" => $rSeconds, "duration" => sprintf('%02d:%02d:%02d', ($rSeconds/3600),($rSeconds/60%60), $rSeconds%60), "movie_image" => $rImage, "video" => Array(), "audio" => Array(), "bitrate" => 0, "rating" => $rEpisode["vote_average"], "season" => $_POST["season_num"]);
                            if (strlen($rImportArray["properties"]["movie_image"][0]) == 0) {
                                unset($rImportArray["properties"]["movie_image"]);
                            }
                        }
                    }
                    if (strlen($rImportArray["name"]) == 0) {
                        $rImportArray["name"] = "No Episode Title";
                    }
                    $rPathInfo = pathinfo($rFilename);
                    $rImportArray["target_container"] = Array($rPathInfo["extension"]);
                    $rProcessArray[] = $rImportArray;
                }
            }
        }
    } else {
        $rImportArray = Array("filename" => $rArray["stream_source"][0], "properties" => Array(), "name" => $rArray["stream_display_name"], "episode" => $_POST["episode"]);
        if ($rAdminSettings["download_images"]) {
            $_POST["movie_image"] = downloadImage($_POST["movie_image"]);
        }
        $rSeconds = intval($_POST["episode_run_time"]) * 60;
        $rImportArray["properties"] = Array("releasedate" => $_POST["releasedate"], "plot" => $_POST["plot"], "duration_secs" => $rSeconds, "duration" => sprintf('%02d:%02d:%02d', ($rSeconds/3600),($rSeconds/60%60), $rSeconds%60), "movie_image" => $_POST["movie_image"], "video" => Array(), "audio" => Array(), "bitrate" => 0, "rating" => $_POST["rating"], "season" => $_POST["season_num"], "tmdb_id" => $_POST["tmdb_id"]);
        if (strlen($rImportArray["properties"]["movie_image"][0]) == 0) {
            unset($rImportArray["properties"]["movie_image"]);
        }
        $rProcessArray[] = $rImportArray;
    }
    $rRestartIDs = Array();
    foreach ($rProcessArray as $rImportArray) {
        $rArray["stream_source"] = Array($rImportArray["filename"]);
        $rArray["movie_propeties"] = $rImportArray["properties"];
        $rArray["stream_display_name"] = $rImportArray["name"];
        if (isset($rImportArray["target_container"])) {
            $rArray["target_container"] = $rImportArray["target_container"];
        }
        $rCols = "`".ESC(implode('`,`', array_keys($rArray)))."`";
        $rValues = null;
        foreach (array_values($rArray) as $rValue) {
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
                $rInsertID = intval($db->insert_id);
            }
            $db->query("DELETE FROM `series_episodes` WHERE `stream_id` = ".$rInsertID.";");
            $db->query("INSERT INTO `series_episodes`(`season_num`, `series_id`, `stream_id`, `sort`) VALUES(".intval($_POST["season_num"]).", ".intval($_POST["series"]).", ".$rInsertID.", ".intval($rImportArray["episode"]).");");
            updateSeries(intval($_POST["series"]));
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
            $db->query("UPDATE `series` SET `last_modified` = ".intval(time())." WHERE `id` = ".intval($_POST["series"]).";");
        }
    }
    if ($rRestart) {
        APIRequest(Array("action" => "vod", "sub" => "start", "stream_ids" => $rRestartIDs));
    }
    if (isset($_POST["multi"])) {
        header("Location: ./episodes.php?series=".intval($_POST["series"]));
        exit;
    } else {
        if (isset($rInsertID)) {
            $_GET["id"] = $rInsertID;
            $_STATUS = 0;
        } else {
            $_STATUS = 1;
        }
        header("Location: ./episode.php?sid=".$_POST["series"]."&id=".$rInsertID); exit;
    }
}

$rServerTree = Array();
$rServerTree[] = Array("id" => "source", "parent" => "#", "text" => "<strong>".$_["stream_source"]."</strong>", "icon" => "mdi mdi-youtube-tv", "state" => Array("opened" => true));

$rSeries = getSerie($_GET["sid"]);
if (!$rSeries) { header("Location: ./series.php");exit; }

if (isset($_GET["id"])) {
	if (!hasPermissions("adv", "edit_episode")) { exit; }
    $rEpisode = getStream($_GET["id"]);
    if ((!$rEpisode) or ($rEpisode["type"] <> 5)) {
        exit;
    }
    $result = $db->query("SELECT `season_num`, `sort` FROM `series_episodes` WHERE `stream_id` = ".intval($rEpisode["id"]).";");
    if (($result) && ($result->num_rows == 1)) {
        $row = $result->fetch_assoc();
        $rEpisode["episode"] = intval($row["sort"]);
        $rEpisode["season"] = intval($row["season_num"]);
    } else {
        $rEpisode["episode"] = 0;
        $rEpisode["season"] = 0;
    }
    $rEpisode["properties"] = json_decode($rEpisode["movie_propeties"], True);
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
	if (!hasPermissions("adv", "add_episode")) { exit; }
    foreach ($rServers as $rServer) {
        $rServerTree[] = Array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
    }
    if (isset($_GET["multi"])) {
		if (!hasPermissions("adv", "import_episodes")) { exit; }
        $rMulti = true;
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
                                        <a href="./episodes.php?series=<?=$_GET["sid"]?>">
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                <?=$_["view_episodes"]?>
                                            </button>
                                        </a>
                                        <?php if (!isset($rEpisode)) { if ($rMulti) { ?>
                                        <a href="./episode.php?sid=<?=$_GET["sid"]?>">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <?=$_["add_single"]?>
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="./episode.php?sid=<?=$_GET["sid"]?>&multi">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <?=$_["add_multiple"]?>
                                            </button>
                                        </a>
                                        <?php } } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rEpisode)) { echo $rEpisode["stream_display_name"].' &nbsp;<button type="button" class="btn btn-outline-info waves-effect waves-light btn-xs" onClick="player('.$rEpisode["id"].', \''.json_decode($rEpisode["target_container"], True)[0].'\');"><i class="mdi mdi-play"></i></button>'; } else if ($rMulti) { echo $_["add_multiple"]; } else { echo $_["add_single"]; } ?></h4>
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
                            <?=$_["episode_success"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["generic_fail"]?>
                        </div>
                        <?php }
                        if (isset($rEpisode)) { ?>
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
                                            <td colspan="7" class="text-center"><?=$_["loading_info"]?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php $rEncodeErrors = getEncodeErrors($rEpisode["id"]);
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
                                <form action="./episode.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="stream_form" data-parsley-validate="">
                                    <?php if (isset($rEpisode)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rEpisode["id"]?>" />
                                    <?php }
                                    if (!isset($rMulti)) { ?>
                                    <input type="hidden" id="tmdb_id" name="tmdb_id" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["properties"]["tmdb_id"]); } ?>" />
                                    <?php } else { ?>
                                    <input type="hidden" name="multi" id="multi" value="" />
                                    <input type="hidden" name="server" id="server" value="" />
                                    <input type="hidden" id="tmdb_id" name="tmdb_id" value="<?php echo htmlspecialchars($rSeries["tmdb_id"]); ?>" />
                                    <?php } ?>
                                    <input type="hidden" name="series" value="<?=$rSeries["id"]?>" />
                                    <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#stream-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["details"]?></span>
                                                </a>
                                            </li>
                                            <?php if (!isset($rMulti)) { ?>
                                            <li class="nav-item">
                                                <a href="#episode-information" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
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
                                                    <span class="d-none d-sm-inline"><?=$_["servers"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <?php if (!isset($rMulti)) { ?>
                                            <div class="tab-pane" id="stream-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="series_name"><?=$_["series_name"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="series_name" name="series_name" value="<?=$rSeries["title"]?>" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="season_num"><?=$_["season_number"]?></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control text-center" id="season_num" name="season_num" placeholder="" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["season"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="episode"><?=$_["episode_number"]?></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control text-center" id="episode" name="episode" placeholder="" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["episode"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="tmdb_search"><?=$_["tmdb_results"]?></label>
                                                            <div class="col-md-8">
                                                                <select id="tmdb_search" class="form-control" data-toggle="select2"></select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_display_name"><?=$_["episode_name"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="stream_display_name" name="stream_display_name" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["stream_display_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <?php
                                                        if (isset($rEpisode)) {
                                                            $rEpisodeSource = json_decode($rEpisode["stream_source"], True)[0];
                                                        } else {
                                                            $rEpisodeSource = "";
                                                        } ?>
                                                        <div class="form-group row mb-4 stream-url">
                                                            <label class="col-md-4 col-form-label" for="stream_source"><?=$_["episode_path"]?></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" id="stream_source" name="stream_source" class="form-control" value="<?=$rEpisodeSource?>" required data-parsley-trigger="change">
                                                                <div class="input-group-append">
                                                                    <a href="#file-browser" id="filebrowser" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-folder-open-outline"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="notes"><?=$_["notes"]?></label>
                                                            <div class="col-md-8">
                                                                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder=""><?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["notes"]); } ?></textarea>
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
                                            <?php } else { ?>
                                            <div class="tab-pane" id="stream-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="series_name"><?=$_["series_name"]?></label>
                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" id="series_name" name="series_name" value="<?=$rSeries["title"]?>" readonly>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control text-center" id="season_num" name="season_num" placeholder="Season" value="" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4 stream-url">
                                                            <label class="col-md-4 col-form-label" for="season_folder"><?=$_["season_folder"]?></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" id="season_folder" name="season_folder" readonly class="form-control" value="" required data-parsley-trigger="change">
                                                                <div class="input-group-append">
                                                                    <a href="#file-browser" id="filebrowser" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-folder-open-outline"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="episode_add"></div>
                                                        <div class="form-group row mb-4">
                                                            <div class="col-md-6">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="addName1" name="addName1" checked>
                                                                    <label class="custom-control-label" for="addName1"><?=$_["add_series_name"]?></label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="addName2" name="addName2" checked>
                                                                    <label class="custom-control-label" for="addName2"><?=$_["add_episode_number"]?></label>
                                                                </div>
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
                                            <?php } ?>
                                            <div class="tab-pane" id="episode-information">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="movie_image"><?=$_["image_url"]?></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" class="form-control" id="movie_image" name="movie_image" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["properties"]["movie_image"]); } ?>">
                                                                <div class="input-group-append">
                                                                    <a href="javascript:void(0)" onClick="openImage(this)" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-eye"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="plot"><?=$_["plot"]?></label>
                                                            <div class="col-md-8">
                                                                <textarea rows="6" class="form-control" id="plot" name="plot"><?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["properties"]["plot"]); } ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="releasedate"><?=$_["release_date"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="releasedate" name="releasedate" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["properties"]["releasedate"]); } ?>">
                                                            </div>
                                                            <label class="col-md-2 col-form-label" for="episode_run_time"><?=$_["runtime"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="episode_run_time" name="episode_run_time" value="<?php if (isset($rEpisode)) { echo intval($rEpisode["properties"]["duration_secs"]/60.0); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="rating"><?=$_["rating"]?></label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control" id="rating" name="rating" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["properties"]["rating"]); } ?>">
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
                                                                <input name="direct_source" id="direct_source" type="checkbox" <?php if (isset($rEpisode)) { if ($rEpisode["direct_source"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="read_native"><?=$_["native_frames"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="read_native" id="read_native" type="checkbox" <?php if (isset($rEpisode)) { if ($rEpisode["read_native"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="movie_symlink"><?=$_["create_symlink"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_2"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="movie_symlink" id="movie_symlink" type="checkbox" <?php if (isset($rEpisode)) { if ($rEpisode["movie_symlink"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="remove_subtitles"><?=$_["remove_existing_subtitles"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_3"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="remove_subtitles" id="remove_subtitles" type="checkbox" <?php if (isset($rEpisode)) { if ($rEpisode["remove_subtitles"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <?php if (!$rMulti) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="target_container"><?=$_["target_container"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_4"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <select name="target_container" id="target_container" class="form-control" data-toggle="select2">
                                                                    <?php foreach (Array("mp4", "mkv", "avi", "mpg", "flv") as $rContainer) { ?>
                                                                    <option <?php if (isset($rEpisode)) { if (json_decode($rEpisode["target_container"], True)[0] == $rContainer) { echo "selected "; } } ?>value="<?=$rContainer?>"><?=$rContainer?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="custom_sid"><?=$_["custom_channel_sid"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_5"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="custom_sid" name="custom_sid" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rEpisode["custom_sid"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <?php }
                                                        $rSubFile = "";
                                                        if (isset($rEpisode)) {
                                                            $rSubData = json_decode($rEpisode["movie_subtitles"], True);
                                                            if (isset($rSubData["location"])) {
                                                                $rSubFile = "s:".$rSubData["location"].":".$rSubData["files"][0];
                                                            }
                                                        }
                                                        ?>
                                                        <?php if (!$rMulti) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="movie_subtitles"><?=$_["subtitle_location"]?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["episode_tooltip_6"]?>" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" id="movie_subtitles" name="movie_subtitles" class="form-control" value="<?php if (isset($rEpisode)) { echo htmlspecialchars($rSubFile); } ?>">
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
                                                                    <option <?php if (isset($rEpisode)) { if (intval($rEpisode["transcode_profile_id"]) == 0) { echo "selected "; } } ?>value="0"><?=$_["transcoding_disabled"]?></option>
                                                                    <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                    <option <?php if (isset($rEpisode)) { if (intval($rEpisode["transcode_profile_id"]) == intval($rProfile["profile_id"])) { echo "selected "; } } ?>value="<?=$rProfile["profile_id"]?>"><?=$rProfile["profile_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
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
															<label class="col-md-4 col-form-label" for="restart_on_edit"><?php if (isset($rEpisode)) { ?><?=$_["reprocess_on_edit"]?><?php } else { ?><?=$_["process_now"]?><?php } ?></label>
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
                                                        <input name="submit_stream" type="submit" class="btn btn-primary" value="<?php if (isset($rEpisode)) { echo $_["edit"]; } else { echo $_["add"]; } ?>" />
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
                                        <div class="form-group row mb-4"<?php if (isset($rMulti)) { echo "style='display:none;'"; } ?>>
                                            <label class="col-md-4 col-form-label" for="search"><?=$_["search_directory"]?></label>
                                            <div class="col-md-8 input-group">
                                                <input type="text" id="search" name="search" class="form-control" placeholder="<?=$_["filter_directory"]?>">
                                                <div class="input-group-append">
                                                    <button class="btn btn-warning waves-effect waves-light" type="button" onClick="clearSearch()"><i class="mdi mdi-close"></i></button>
                                                    <button class="btn btn-primary waves-effect waves-light" type="button" id="doSearch"><i class="mdi mdi-magnify"></i></button>
                                                </div>
                                            </div>
                                        </div>
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
                                        <?php if (isset($rMulti)) { ?>
                                        <div class="float-right">
                                            <input id="select_folder" type="button" class="btn btn-info" value="<?=$_["add_this_directory"]?>" />
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
        var rEpisodes = {};
        
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
        
        function pad(n) {
            if (n < 10)
                return "0" + n;
            return n;
        }
        function api(rID, rServerID, rType) {
            if (rType == "delete") {
                if (confirm('<?=$_["episode_delete_confirm"]?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=episode&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
                if (data.result == true) {
                    if (rType == "start") {
                        $.toast("<?=$_["episode_encoding_start"]?>.");
                    } else if (rType == "stop") {
                        $.toast("<?=$_["episode_encoding_stop"]?>");
                    } else if (rType == "delete") {
                        $.toast("<?=$_["episode_deleted"]?>");
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
                    src: "./player.php?type=series&id=" + rID + "&container=" + rContainer,
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
            
            $("#select_folder").click(function() {
                $("#season_folder").val(window.currentDirectory);
                $("#server").val($("#server_id").val());
                rID = 1;
                $("#episode_add").html("");
                $("#datatable-files").DataTable().rows().every(function ( rowIdx, tableLoop, rowLoop) {
                    var data = this.data();
                    rExt = data[1].split('.').pop().toLowerCase();
                    if (["mp4", "mkv", "mov", "avi", "mpg", "mpeg", "flv", "wmv"].includes(rExt)) {
                        $("#episode_add").append('<div class="form-group row mb-4"><label class="col-md-4 col-form-label" for="episode_' + rID + '_name"><?=$_["episode_to_add"]?></label><div class="col-md-6"><input type="text" class="form-control" id="episode_' + rID + '_name" name="episode_' + rID + '_name" value="' + data[1] + '" readonly></div><div class="col-md-2"><input type="text" class="form-control text-center" id="episode_' + rID + '_num" name="episode_' + rID + '_num" placeholder="Episode" value=""></div></div>');
                        $("#episode_" + rID + "_num").inputFilter(function(value) { return /^\d*$/.test(value); });
                    }
                    rID ++;
                });
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
                        $("#datatable").DataTable().row.add(["<i class='mdi mdi-subdirectory-arrow-left'></i>", "Parent Directory"]);
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
            <?php if (!$rMulti) { ?>
            $('#datatable-files').on('click', 'tbody > tr', function() {
                selectFile($(this).find("td").eq(1).html());
            });
            <?php } ?>
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
                <?php if (!$rMulti) { ?>
                if ($("#stream_display_name").val().length == 0) {
                    e.preventDefault();
                    $.toast("<?=$_["enter_an_episode_name"]?>");
                }
                if ($("#stream_source").val().length == 0) {
                    e.preventDefault();
                    $.toast("<?=$_["enter_an_episode_source"]?>");
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
                $("#changeDir").click();
                $($.fn.dataTable.tables(true)).css('width', '100%');
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
            });
            $("#filebrowser-sub").on("mfpOpen", function() {
                clearSearch();
                $("#changeDir").click();
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
            
            <?php if (!$rMulti) { ?>
            $("#season_num").change(function() {
                if (!window.changeTitle) {
                    $("#tmdb_search").empty().trigger('change');
                    if ($("#season_num").val().length > 0) {
                        window.rEpisodes = {};
                        $.getJSON("./api.php?action=tmdb_search&type=episode&term=<?=$rSeries["tmdb_id"]?>&season=" + $("#season_num").val(), function(data) {
                            if (data.result == true) {
                                if (data.data.episodes.length > 0) {
                                    newOption = new Option("<?=$_["found_episodes"]?>".replace("{num}", data.data.episodes.length), -1, true, true);
                                } else {
                                    newOption = new Option("<?=$_["no_episodes_found"]?>", -1, true, true);
                                }
                                $("#tmdb_search").append(newOption).trigger('change');
                                $(data.data.episodes).each(function(id, item) {
                                    window.rEpisodes[item.id] = item;
                                    rTitle = "<?=$_["episode"]?> " + item.episode_number + " - " + item.name;
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
                    var rEpisode = window.rEpisodes[$("#tmdb_search").val()];
                    var rFormat = "S" + pad(rEpisode.season_number) + "E" + pad(rEpisode.episode_number);
                    $("#stream_display_name").val($("#series_name").val() + " - " + rFormat + " - " + rEpisode.name);
                    $("#movie_image").val("");
                    if (rEpisode.still_path.length > 0) {
                        $("#movie_image").val("https://image.tmdb.org/t/p/w300" + rEpisode.still_path);
                    }
                    $("#releasedate").val(rEpisode.air_date);
                    $("#episode_run_time").val('<?=$rSeries["episode_run_time"]?>');
                    $("#plot").val(rEpisode.overview);
                    $("#rating").val(rEpisode.vote_average);
                    $("#tmdb_id").val(rEpisode.id);
                    $("#episode").val(rEpisode.episode_number);
                }
            });
            <?php }
            if (isset($rEpisode)) { ?>
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
                        d.id = "episodes";
                        d.stream_id = <?=$rEpisode["id"]?>;
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [2,3,4,5]},
                    {"visible": false, "targets": [0,1,6,7]}
                ],
            });
            setTimeout(reloadStream, 5000);
            $("#season_num").trigger('change');
            <?php } ?>
            
            $("#runtime").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#season_num").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            
            $("#changeDir").click();
            evaluateDirectSource();
            evaluateSymlink();
        });
        </script>
    </body>
</html>