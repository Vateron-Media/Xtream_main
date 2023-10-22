<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "create_channel")) && (!hasPermissions("adv", "edit_cchannel")))) { exit; }

$rCategories = getCategories("live");
$rTranscodeProfiles = getTranscodeProfiles();

if (isset($_POST["submit_stream"])) {
    if (isset($_POST["edit"])) {
		if (!hasPermissions("adv", "edit_cchannel")) { exit; }
        $rArray = getStream($_POST["edit"]);
        unset($rArray["id"]);
    } else {
		if (!hasPermissions("adv", "create_channel")) { exit; }
        $rArray = Array("type" => 3, "added" => time(), "read_native" => 1, "stream_all" => 1, "redirect_stream" => 0, "direct_source" => 0, "gen_timestamps" => 1, "transcode_attributes" => Array(), "stream_display_name" => "", "stream_source" => Array(), "category_id" => 0, "stream_icon" => "", "notes" => "", "custom_sid" => "", "custom_ffmpeg" => "", "custom_map" => "", "transcode_profile_id" => 0, "enable_transcode" => 0, "auto_restart" => "[]", "allow_record" => 0, "rtmp_output" => 0, "epg_id" => null, "channel_id" => null, "epg_lang" => null, "tv_archive_server_id" => 0, "tv_archive_duration" => 0, "delay_minutes" => 0, "external_push" => "", "probesize_ondemand" => 128000, "pids_create_channel" => Array(), "created_channel_location" => 0, "cchannel_rsources" => Array(), "series_no" => 0);
    }
    $rArary["transcode_profile_id"] = $_POST["transcode_profile_id"];
    if (!$rArray["transcode_profile_id"]) {
        $rArray["transcode_profile_id"] = 0;
    }
	if (isset($_POST["restart_on_edit"])) {
		$rRestart = true;
		unset($_POST["restart_on_edit"]);
	} else {
		$rRestart = false;
	}
    $rArray["movie_propeties"] = Array("type" => intval($_POST["channel_type"]));
    if (intval($_POST["channel_type"]) == 0) {
        $rPlaylist = generateSeriesPlaylist($_POST["series_no"]);
        if ($rPlaylist["success"]) {
            $rArray["created_channel_location"] = $rPlaylist["server_id"];
            $rArray["stream_source"] = $rPlaylist["sources"];
            $rArray["series_no"] = intval($_POST["series_no"]);
            unset($_POST["created_channel_location"]);
        } else {
            $_STATUS = 2;
        }
    } else {
        $rArray["created_channel_location"] = intval($_POST["created_channel_location"]);
        $rArray["stream_source"] = $_POST["video_files"];
        $rArray["series_no"] = 0;
    }
    $rArray["cchannel_rsources"] = Array();
    $rBouquets = $_POST["bouquets"];
    unset($_POST["bouquets"]);
    foreach($_POST as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    if (count($rArray["stream_source"]) > 0) {
        if ($rAdminSettings["download_images"]) {
            $rArray["stream_icon"] = downloadImage($rArray["stream_icon"]);
        }
        $rArray["order"] = getNextOrder();
        $rCols = ESC("`".implode('`,`', array_keys($rArray))."`");
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
                $rInsertID = $db->insert_id;
            }
        }
        if (isset($rInsertID)) {
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
                            $db->query("UPDATE `streams_sys` SET `parent_id` = ".$rParent." WHERE `server_stream_id` = ".$rStreamExists[$rServerID].";");
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
                APIRequest(Array("action" => "stream", "sub" => "start", "stream_ids" => Array($rInsertID)));
            }
            foreach ($rBouquets as $rBouquet) {
                addToBouquet("stream", $rBouquet, $rInsertID);
            }
            if (isset($_POST["edit"])) {
                foreach (getBouquets() as $rBouquet) {
                    if (!in_array($rBouquet["id"], $rBouquets)) {
                        removeFromBouquet("stream", $rBouquet["id"], $rInsertID);
                    }
                }
            }
            if (count($rBouquets) > 0) {
                scanBouquets();
            }
            $_STATUS = 0;
            header("Location: ./created_channel.php?id=".$rInsertID); exit;
        } else {
            $_STATUS = 1;
            $rStream = $rArray;
        }
    } else {
		if (!isset($_STATUS)) {
			$_STATUS = 1;
            $rStream = $rArray;
		}
    }
}

$rServerTree = Array();
$rServerTree[] = Array("id" => "source", "parent" => "#", "text" => "<strong>Stream Source</strong>", "icon" => "mdi mdi-youtube-tv", "state" => Array("opened" => true));

if (isset($_GET["id"])) {
	if (!hasPermissions("adv", "edit_cchannel")) { exit; }
    $rChannel = getStream($_GET["id"]);
    if ((!$rChannel) or ($rChannel["type"] <> 3)) {
        exit;
    }
    $rProperties = json_decode($rChannel["movie_propeties"], True);
    if (!$rProperties) {
        if ($rChannel["series_no"] > 0) {
            $rProperties = Array("type" => 0);
        } else {
            $rProperties = Array("type" => 1);
        }
    }
    $rChannelSys = getStreamSys($_GET["id"]);
    foreach ($rServers as $rServer) {
        if (isset($rChannelSys[intval($rServer["id"])])) {
            if ($rChannelSys[intval($rServer["id"])]["parent_id"] <> 0) {
                $rParent = intval($rChannelSys[intval($rServer["id"])]["parent_id"]);
            } else {
                $rParent = "source";
            }
        } else {
            $rParent = "#";
        }
        $rServerTree[] = Array("id" => $rServer["id"], "parent" => $rParent, "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
    }
} else {
	if (!hasPermissions("adv", "create_channel")) { exit; }
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
        <div class="content-page"><div class="content boxed-layout-ext"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout-ext"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="./streams.php?filter=8">
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                View Channels
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rChannel)) { echo $rChannel["stream_display_name"].' &nbsp;<button type="button" class="btn btn-outline-info waves-effect waves-light btn-xs" onClick="player('.$rChannel["id"].', \''.json_decode($rChannel["target_container"], True)[0].'\');"><i class="mdi mdi-play"></i></button>'; } else { echo "Create Channel"; } ?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if (count($rTranscodeProfiles) == 0) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            You need at least one transcoding profile available to create a channel.
                        </div>
                        <?php }
                        if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Channel operation was completed successfully.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS ==  2)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            The series you have selected has sources from multiple servers, please select a series that all hosted on one server.
                        </div>
                        <?php }
                        if (isset($rChannel)) { ?>
                        <div class="card text-xs-center">
                            <div class="table">
                                <table id="datatable-list" class="table table-borderless mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th>Source</th>
                                            <th>Clients</th>
                                            <th>Uptime</th>
                                            <th>Actions</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center">Loading channel information...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php $rEncodeErrors = getEncodeErrors($rChannel["id"]);
                        foreach ($rEncodeErrors as $rServerID => $rEncodeError) { ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Error on Server - <?=$rServers[$rServerID]["server_name"]?></strong><br/>
                            <?=str_replace("\n", "<br/>", $rEncodeError)?>
                        </div>
                        <?php } } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./created_channel.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="stream_form" data-parsley-validate="">
                                    <?php if (isset($rChannel)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rChannel["id"]?>" />
                                    <?php } ?>
                                    <input type="hidden" name="created_channel_location" id="created_channel_location" value="<?php if (isset($rChannel)) { echo $rChannel["created_channel_location"]; } ?>" />
                                    <input type="hidden" name="video_files" id="video_files" value="" />
                                    <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#stream-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item" id="selection_nav">
                                                <a href="#selection" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-movie mr-1"></i>
                                                    <span class="d-none d-sm-inline">Selection</span>
                                                </a>
                                            </li>
                                            <li class="nav-item" id="review_nav">
                                                <a href="#review" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-marker mr-1"></i>
                                                    <span class="d-none d-sm-inline">Review</span>
                                                </a>
                                            </li>
                                            <li class="nav-item" id="videos_nav">
                                                <a href="#videos" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-movie mr-1"></i>
                                                    <span class="d-none d-sm-inline">Videos</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#load-balancing" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-server-network mr-1"></i>
                                                    <span class="d-none d-sm-inline">Servers</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="stream-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="channel_type">Selection Type</label>
                                                            <div class="col-md-8">
                                                                <select name="channel_type" id="channel_type" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (Array(0 => "Series", 1 => "File Browser", 2 => "VOD Selection") as $rID => $rType) { ?>
                                                                    <option <?php if (isset($rChannel)) { if ($rProperties["type"] == $rID) { echo "selected "; } } ?>value="<?=$rID?>"><?=$rType?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4" id="series_nav">
                                                            <label class="col-md-4 col-form-label" for="series_no">24/7 Series</label>
                                                            <div class="col-md-8">
                                                                <select name="series_no" id="series_no" class="form-control select2" data-toggle="select2">
                                                                    <option value="0">Select a series...</option>
                                                                    <?php foreach (getSeries() as $rSeries) { ?>
                                                                    <option <?php if (isset($rChannel)) { if (intval($rChannel["series_no"]) == intval($rSeries["id"])) { echo "selected "; } } ?>value="<?=$rSeries["id"]?>"><?=$rSeries["title"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_display_name">Channel Name</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="stream_display_name" name="stream_display_name" value="<?php if (isset($rChannel)) { echo htmlspecialchars($rChannel["stream_display_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_id">Category Name</label>
                                                            <div class="col-md-8">
                                                                <select name="category_id" id="category_id" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach ($rCategories as $rCategory) { ?>
                                                                    <option <?php if (isset($rChannel)) { if (intval($rChannel["category_id"]) == intval($rCategory["id"])) { echo "selected "; } } else if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo "selected "; } ?>value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="transcode_profile_id">Transcoding Profile</label>
                                                            <div class="col-md-8">
                                                                <select name="transcode_profile_id" id="transcode_profile_id" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                    <option <?php if (isset($rChannel)) { if (intval($rChannel["transcode_profile_id"]) == intval($rProfile["profile_id"])) { echo "selected "; } } ?>value="<?=$rProfile["profile_id"]?>"><?=$rProfile["profile_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="bouquets">Add To Bouquets</label>
                                                            <div class="col-md-8">
                                                                <select name="bouquets[]" id="bouquets" class="form-control select2-multiple select2" data-toggle="select2" multiple="multiple" data-placeholder="Choose...">
                                                                    <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                    <option <?php if (isset($rChannel)) { if (in_array($rChannel["id"], json_decode($rBouquet["bouquet_channels"], True))) { echo "selected "; } } ?>value="<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_icon">Stream Logo URL</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="stream_icon" name="stream_icon" value="<?php if (isset($rChannel)) { echo htmlspecialchars($rChannel["stream_icon"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="notes">Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder=""><?php if (isset($rChannel)) { echo htmlspecialchars($rChannel["notes"]); } ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item float-right">
                                                        <a href="javascript: void(0);" id="next_0" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="selection">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="server_idc">Server Name</label>
                                                            <div class="col-md-8">
                                                                <select id="server_idc" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (getStreamingServers() as $rServer) { ?>
                                                                    <option value="<?=$rServer["id"]?>"<?php if (isset($rChannel) && ($rChannel["created_channel_location"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_name">Category / Series</label>
                                                            <div class="col-md-8">
                                                                <select id="category_idv" class="form-control select2" data-toggle="select2">
                                                                    <option value="" selected>No Filter</option>
                                                                    <?php foreach (getCategories("movie") as $rCategory) { ?>
                                                                    <option value="0:<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php }
                                                                    foreach (getSeriesList() as $rSeries) { ?>
                                                                    <option value="1:<?=$rSeries["id"]?>"><?=$rSeries["title"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="vod_search">Search</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="vod_search" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-vod" class="table nowrap">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">ID</th>
                                                                        <th>Name</th>
                                                                        <th>Category / Series</th>
                                                                        <th class="text-center">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <span class="float-right">
                                                        <li class="next list-inline-item">
                                                            <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                        </li>
                                                    </span>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="review">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4 stream-url">
                                                            <div class="col-md-12">
                                                                <select multiple id="review_sort" name="review_sort" class="form-control" style="min-height:400px;">
                                                                <?php if ((isset($rChannel)) && (in_array(intval($rProperties["type"]), Array(2)))) { foreach (json_decode($rChannel["stream_source"], True) as $rSource) { ?>
                                                                    <option value="<?=$rSource?>"><?=$rSource?></option>
                                                                <?php } } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('review')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('review')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('review')" class="btn btn-info">A to Z</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="videos">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4 stream-url">
                                                            <label class="col-md-3 col-form-label" for="import_folder">Import Folder</label>
                                                            <div class="col-md-9 input-group">
                                                                <input type="text" id="import_folder" name="import_folder" readonly class="form-control" value="<?php if (isset($rChannel)) { echo htmlspecialchars($rServers[$rChannel["created_channel_location"]]["server_name"]); } ?>">
                                                                <div class="input-group-append">
                                                                    <a href="#file-browser" id="filebrowser" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-folder-open-outline"></i></a>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 add-margin-top-20">
                                                                <select multiple id="videos_sort" name="videos_sort" class="form-control" style="min-height:400px;">
                                                                <?php if ((isset($rChannel)) && (in_array(intval($rProperties["type"]), Array(1)))) { foreach (json_decode($rChannel["stream_source"], True) as $rSource) { ?>
                                                                    <option value="<?=$rSource?>"><?=$rSource?></option>
                                                                <?php } } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('videos')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('videos')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="Remove('videos')" class="btn btn-warning"><i class="mdi mdi-close"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('videos')" class="btn btn-info">A to Z</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="load-balancing">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="servers">Server Tree</label>
                                                            <div class="col-md-8">
                                                                <div id="server_tree"></div>
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
															<label class="col-md-4 col-form-label" for="restart_on_edit"><?php if (isset($rChannel)) { ?>Restart on Edit<?php } else { ?>Start Channel Now<?php } ?></label>
															<div class="col-md-2">
																<input name="restart_on_edit" id="restart_on_edit" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
															</div>
														</div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" id="previous_0" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <input name="submit_stream" type="submit" class="btn btn-primary" value="<?php if (isset($rChannel)) { echo "Edit"; } else { echo "Create"; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>
                                <div id="file-browser" class="mfp-hide white-popup-block">
                                    <div class="col-12">
                                        <div class="form-group row mb-4">
                                            <label class="col-md-4 col-form-label" for="server_id">Server Name</label>
                                            <div class="col-md-8">
                                                <select id="server_id" class="form-control select2" data-toggle="select2">
                                                    <?php foreach (getStreamingServers() as $rServer) { ?>
                                                    <option value="<?=$rServer["id"]?>"<?php if (isset($rChannel) && ($rChannel["created_channel_location"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-md-4 col-form-label" for="current_path">Current Path</label>
                                            <div class="col-md-8 input-group">
                                                <input type="text" id="current_path" name="current_path" class="form-control" value="/">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary waves-effect waves-light" type="button" id="changeDir"><i class="mdi mdi-chevron-right"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <div class="col-md-6">
                                                <table id="datatable" class="table">
                                                    <thead>
                                                        <tr>
                                                            <th width="20px"></th>
                                                            <th>Directory</th>
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
                                                            <th>Filename</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="float-right">
                                            <input id="select_folder" type="button" class="btn btn-info" value="Add This Directory" />
                                        </div>
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
        var rChannels = {};
                
        <?php if ((isset($rChannel)) && ($rProperties["type"] == 2)) { ?>
        var rSelection = <?=json_encode(getSelections(json_decode($rChannel["stream_source"], True)))?>;
        <?php } else { ?>
        var rSelection = [];
        <?php } ?>
        
        function AtoZ(rType) {
            $("#" + rType + "_sort").append($("#" + rType + "_sort option").remove().sort(function(a, b) {
                var at = $(a).text().toUpperCase().split("/").pop(), bt = $(b).text().toUpperCase().split("/").pop();
                return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
            }));
        }
        function MoveUp(rType) {
            var rSelected = $('#' + rType + '_sort option:selected');
            if (rSelected.length) {
                var rPrevious = rSelected.first().prev()[0];
                if ($(rPrevious).html() != '') {
                    rSelected.first().prev().before(rSelected);
                }
            }
        }
        function MoveDown(rType) {
            var rSelected = $('#' + rType + '_sort option:selected');
            if (rSelected.length) {
                rSelected.last().next().after(rSelected);
            }
        }
        function Remove(rType) {
            var rSelected = $('#' + rType + '_sort option:selected');
            if (rSelected.length) {
                rSelected.remove();
            }
        }
        function getCategory() {
            return $("#category_idv").val();
        }
        function getServer() {
            return $("#server_idc").val();
        }
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
        
        function toggleSelection(rID) {
            var rIndex = rSelection.indexOf(parseInt(rID));
            if (rIndex > -1) {
                rSelection = jQuery.grep(rSelection, function(rValue) {
                    return parseInt(rValue) != parseInt(rID);
                });
            } else {
                rSelection.push(parseInt(rID));
            }
            $("#datatable-vod").DataTable().ajax.reload(null, false);
            reviewSelection();
        }
        
        function reviewSelection() {
            $.post("./api.php?action=review_selection", {"data": rSelection}, function(rData) {
                if (rData.result === true) {
                    var rActiveStreams = [];
                    $(rData.streams).each(function(rIndex) {
                        rStreamSource = $.parseJSON(rData.streams[rIndex]["stream_source"])[0].replace("s:" + $("#server_idc").val() + ":", "");
                        rActiveStreams.push(rStreamSource);
                        rExt = rStreamSource.split('.').pop().toLowerCase();
                        if ((["mp4", "mkv", "mov", "avi", "mpg", "mpeg", "flv", "wmv"].includes(rExt)) && ($("#review_sort option[value='" + rStreamSource.replace("'", "\\'") + "']").length == 0)) {
                            $("#review_sort").append(new Option(rStreamSource, rStreamSource));
                        }
                    });
                    $("#review_sort option").each(function() {
                        if (!rActiveStreams.includes($(this).val())) {
                            $(this).remove();
                        }
                    });
                }
            }, "json");
        }
        
        function api(rID, rServerID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this channel?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=stream&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
                if (data.result == true) {
                    if (rType == "start") {
                        $.toast("Channel successfully started. It will take a minute or so before the channel becomes available.");
                    } else if (rType == "restart") {
                        $.toast("Channel successfully restarted. It will take a minute or so before the channel becomes available.");
                    } else if (rType == "stop") {
                        $.toast("Channel successfully stopped.");
                    } else if (rType == "delete") {
                        $.toast("Channel successfully deleted.");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $("#datatable-list").DataTable().ajax.reload( null, false );
                } else {
                    $.toast("An error occured while processing your request.");
                }
            }).fail(function() {
                $.toast("An error occured while processing your request.");
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
        function reloadStream() {
            $("#datatable-list").DataTable().ajax.reload( null, false );
            setTimeout(reloadStream, 5000);
        }
        function player(rID, rContainer) {
            $.magnificPopup.open({
                items: {
                    src: "./player.php?type=live&id=" + rID + "&container=" + rContainer,
                    type: 'iframe'
                }
            });
        }
        $(document).ready(function() {
            $('.select2').select2({width: '100%'});
            
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
                    "emptyTable": "No compatible files found"
                }
            });
            
            $("#datatable-vod").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                createdRow: function(row, data, index) {
                    $(row).addClass('vod-' + data[0]);
                    var rIndex = rSelection.indexOf(parseInt(data[0]));
                    if (rIndex > -1) {
                        $(row).find(".btn-remove").show();
                    } else {
                        $(row).find(".btn-add").show();
                    }
                },
                bInfo: false,
                bAutoWidth: false,
                searching: true,
                pageLength: 100,
                lengthChange: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table.php",
                    "data": function(d) {
                        d.id = "vod_selection";
                        d.category_id = getCategory();
                        d.server_id = getServer();
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,3]}
                ],
            });
            
            $("#category_idv").on("select2:select", function(e) { 
                $("#datatable-vod").DataTable().ajax.reload(null, false);
            });
            $('#vod_search').keyup(function(){
                $('#datatable-vod').DataTable().search($(this).val()).draw();
            })
            
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
              var switchery = new Switchery(html);
              window.rSwitches[$(html).attr("id")] = switchery;
            });
            
            $("#select_folder").click(function() {
                if ($("#server_id").val() != $("#created_channel_location").val()) {
                    $("#created_channel_location").val($("#server_id").val());
                    $("#videos_sort").empty();
                }
                $("#import_folder").val($("#server_id option:selected").text());
                $("#datatable-files").DataTable().rows().every(function ( rowIdx, tableLoop, rowLoop) {
                    var data = this.data();
                    rExt = data[1].split('.').pop().toLowerCase();
                    if ((["mp4", "mkv", "mov", "avi", "mpg", "mpeg", "flv", "wmv"].includes(rExt)) && ($("#videos_sort option[value='" + (window.currentDirectory + data[1]).replace("'", "\\'") + "']").length == 0)) {
                        $("#videos_sort").append(new Option(window.currentDirectory + data[1], window.currentDirectory + data[1]));
                    }
                });
                $.magnificPopup.close();
            });
            
            $("#changeDir").click(function() {
                window.currentDirectory = $("#current_path").val();
                if (window.currentDirectory.substr(-1) != "/") {
                    window.currentDirectory += "/";
                }
                $("#current_path").val(window.currentDirectory);
                $("#datatable").DataTable().clear();
                $("#datatable").DataTable().row.add(["", "Loading..."]);
                $("#datatable").DataTable().draw(true);
                $("#datatable-files").DataTable().clear();
                $("#datatable-files").DataTable().row.add(["", "Please wait..."]);
                $("#datatable-files").DataTable().draw(true);
                rFilter = "video";
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
                if ($(this).find("td").eq(1).html() == "Parent Directory") {
                    selectParent();
                } else {
                    selectDirectory($(this).find("td").eq(1).html());
                }
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
                var rVideoFiles = [];
                if ($("#channel_type").val() == 0) {
                    if ($("#series_no").val() == 0) {
                        $.toast("Please select a series to map.");
                        e.preventDefault();
                    }
                } else if ($("#channel_type").val() == 1) {
                    if ($("#videos_sort option").length == 0) {
                        $.toast("Please add at least one video to the channel.");
                        e.preventDefault();
                    }
                    $("#videos_sort option").each(function() {
                        rVideoFiles.push($(this).val());
                    });
                    $("#created_channel_location").val($("#server_id").val());
                } else if ($("#channel_type").val() == 2) {
                    if ($("#review_sort option").length == 0) {
                        $.toast("Please add at least one video to the channel.");
                        e.preventDefault();
                    }
                    $("#review_sort option").each(function() {
                        rVideoFiles.push($(this).val());
                    });
                    $("#created_channel_location").val($("#server_idc").val());
                }
                if (!$("#transcode_profile_id").val()) {
                    $.toast("Please select a trancoding profile.");
                    e.preventDefault();
                }
                $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {flat:true})));
                $("#video_files").val(JSON.stringify(rVideoFiles));
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
                $("#changeDir").click();
                $($.fn.dataTable.tables(true)).css('width', '100%');
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
            });
            $("#filebrowser-sub").on("mfpOpen", function() {
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
            
            $("#series_no").change(function() {
                if ($("#series_no").val() > 0) {
                    $("#stream_display_name").val("24/7 " + $("#series_no option:selected").text());
                }
            });
            
            $("#channel_type").change(function() {
                if ($("#channel_type").val() == 0) {
                    $("#review_nav").hide();
                    $("#selection_nav").hide()
                    $("#videos_nav").hide();
                    $("#series_nav").show();
                } else if ($("#channel_type").val() == 1) {
                    $("#review_nav").hide();
                    $("#selection_nav").hide()
                    $("#videos_nav").show();
                    $("#series_nav").hide();
                } else {
                    $("#review_nav").show();
                    $("#selection_nav").show()
                    $("#videos_nav").hide();
                    $("#series_nav").hide();
                }
            });
            
            $("#server_idc").change(function() {
                $("#review_sort").empty();
                $("#datatable-vod").DataTable().ajax.reload(null, false);
            });
            
            <?php if (isset($rChannel)) { ?>
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
                        d.id = "streams";
                        d.stream_id = <?=$rChannel["id"]?>;
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [3,4,5,6]},
                    {"visible": false, "targets": [0,1,2,7]}
                ],
            });
            setTimeout(reloadStream, 5000);
            $("#season_num").trigger('change');
            <?php } ?>
            
            $("#next_0").click(function() {
                if ($("#channel_type").val() == 0) {
                    $('[href="#load-balancing"]').tab('show');
                } else if ($("#channel_type").val() == 1) {
                    $('[href="#selection"]').tab('show');
                } else {
                    $('[href="#videos"]').tab('show');
                }
            });
            $("#previous_0").click(function() {
                if ($("#channel_type").val() == 0) {
                    $('[href="#stream-details"]').tab('show');
                } else if ($("#channel_type").val() == 1) {
                    $('[href="#videos"]').tab('show');
                } else {
                    $('[href="#review"]').tab('show');
                }
            });
            
            $("form").attr('autocomplete', 'off');
            $("#changeDir").click();
            $("#channel_type").trigger('change');
        });
        </script>
    </body>
</html>