<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "mass_edit_streams"))) { exit; }

if (isset($_POST["submit_stream"])) {
    $rArray = Array();
    if (isset($_POST["c_days_to_restart"])) {
        if ((isset($_POST["days_to_restart"])) && (preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $_POST["time_to_restart"]))) {
            $rTimeArray = Array("days" => Array(), "at" => $_POST["time_to_restart"]);
            foreach ($_POST["days_to_restart"] as $rID => $rDay) {
                $rTimeArray["days"][] = $rDay;
            }
            $rArray["auto_restart"] = json_encode($rTimeArray);
        } else {
            $rArray["auto_restart"] = "";
        }
    }
    if (isset($_POST["c_gen_timestamps"])) {
        if (isset($_POST["gen_timestamps"])) {
            $rArray["gen_timestamps"] = 1;
        } else {
            $rArray["gen_timestamps"] = 0;
        }
    }
    if (isset($_POST["c_allow_record"])) {
        if (isset($_POST["allow_record"])) {
            $rArray["allow_record"] = 1;
        } else {
            $rArray["allow_record"] = 0;
        }
    }
    if (isset($_POST["c_rtmp_output"])) {
        if (isset($_POST["rtmp_output"])) {
            $rArray["rtmp_output"] = 1;
        } else {
            $rArray["rtmp_output"] = 0;
        }
    }
    if (isset($_POST["c_stream_all"])) {
        if (isset($_POST["stream_all"])) {
            $rArray["stream_all"] = 1;
        } else {
            $rArray["stream_all"] = 0;
        }
    }
    if (isset($_POST["c_direct_source"])) {
        if (isset($_POST["direct_source"])) {
            $rArray["direct_source"] = 1;
        } else {
            $rArray["direct_source"] = 0;
        }
    }
    if (isset($_POST["c_read_native"])) {
        if (isset($_POST["read_native"])) {
            $rArray["read_native"] = 1;
        } else {
            $rArray["read_native"] = 0;
        }
    }
    if (isset($_POST["c_tv_archive_server_id"])) {
        if (isset($_POST["tv_archive_server_id"])) {
            $rArray["tv_archive_server_id"] = intval($_POST["tv_archive_server_id"]);
        } else {
            $rArray["tv_archive_server_id"] = 0;
        }
    }
    if (isset($_POST["c_tv_archive_duration"])) {
        if (isset($_POST["tv_archive_duration"])) {
            $rArray["tv_archive_duration"] = intval($_POST["tv_archive_duration"]);
        } else {
            $rArray["tv_archive_duration"] = 0;
        }
    }
    if (isset($_POST["c_delay_minutes"])) {
        if (isset($_POST["delay_minutes"])) {
            $rArray["delay_minutes"] = intval($_POST["delay_minutes"]);
        } else {
            $rArray["delay_minutes"] = 0;
        }
    }
    if (isset($_POST["c_probesize_ondemand"])) {
        if (isset($_POST["probesize_ondemand"])) {
            $rArray["probesize_ondemand"] = intval($_POST["probesize_ondemand"]);
        } else {
            $rArray["probesize_ondemand"] = 0;
        }
    }
    if (isset($_POST["c_category_id"])) {
        $rArray["category_id"] = intval($_POST["category_id"]);
    }
    if (isset($_POST["c_custom_sid"])) {
        $rArray["custom_sid"] = $_POST["custom_sid"];
    }
    if (isset($_POST["c_custom_ffmpeg"])) {
        $rArray["custom_ffmpeg"] = $_POST["custom_ffmpeg"];
    }
    if (isset($_POST["c_transcode_profile_id"])) {
        $rArray["transcode_profile_id"] = $_POST["transcode_profile_id"];
        if ($rArray["transcode_profile_id"] > 0) {
            $rArray["enable_transcode"] = 1;
        } else {
            $rArray["enable_transcode"] = 0;
        }
    }
    $rStreamIDs = json_decode($_POST["streams"], True);
    if (count($rStreamIDs) > 0) {
        foreach ($rStreamIDs as $rStreamID) {
            $rQueries = Array();
            foreach ($rArray as $rKey => $rValue) {
                $rQueries[] = "`".ESC($rKey)."` = '".ESC($rValue)."'";
            }
            if (count($rQueries) > 0) {
                $rQueryString = join(",", $rQueries);
                $rQuery = "UPDATE `streams` SET ".$rQueryString." WHERE `id` = ".intval($rStreamID).";";
                if (!$db->query($rQuery)) {
                    $_STATUS = 1;
                }
            }
            if (isset($_POST["c_server_tree"])) {
                $rOnDemandArray = Array();
                if (isset($_POST["on_demand"])) {
                    foreach ($_POST["on_demand"] as $rID) {
                        $rOnDemandArray[] = intval($rID);
                    }
                }
                $rStreamExists = Array();
                $result = $db->query("SELECT `server_stream_id`, `server_id` FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID).";");
                if (($result) && ($result->num_rows > 0)) {
                    while ($row = $result->fetch_assoc()) {
                        $rStreamExists[intval($row["server_id"])] = intval($row["server_stream_id"]);
                    }
                }
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
                        if (in_array($rServerID, $rOnDemandArray)) {
                            $rOD = 1;
                        } else {
                            $rOD = 0;
                        }
                        if (isset($rStreamExists[$rServerID])) {
                            if (!$db->query("UPDATE `streams_sys` SET `parent_id` = ".$rParent.", `on_demand` = ".$rOD." WHERE `server_stream_id` = ".$rStreamExists[$rServerID].";")) {
                                $_STATUS = 1;
                            }
                        } else {
                            if (!$db->query("INSERT INTO `streams_sys`(`stream_id`, `server_id`, `parent_id`, `on_demand`) VALUES(".intval($rStreamID).", ".$rServerID.", ".$rParent.", ".$rOD.");")) {
                                $_STATUS = 1;
                            }
                        }
                    }
                }
                foreach ($rStreamExists as $rServerID => $rDBID) {
                    if (!in_array($rServerID, $rStreamsAdded)) {
                        $db->query("DELETE FROM `streams_sys` WHERE `server_stream_id` = ".$rDBID.";");
                    }
                }
            }
            if (isset($_POST["c_user_agent"])) {
                $db->query("DELETE FROM `streams_options` WHERE `stream_id` = ".intval($rStreamID)." AND `argument_id` = 1;");
                if ((isset($_POST["user_agent"])) && (strlen($_POST["user_agent"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rStreamID).", 1, '".ESC($_POST["user_agent"])."');");
                }
            }
            if (isset($_POST["c_http_proxy"])) {
                $db->query("DELETE FROM `streams_options` WHERE `stream_id` = ".intval($rStreamID)." AND `argument_id` = 2;");
                if ((isset($_POST["http_proxy"])) && (strlen($_POST["http_proxy"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rStreamID).", 2, '".ESC($_POST["http_proxy"])."');");
                }
            }
			if (isset($_POST["c_cookie"])) {
                $db->query("DELETE FROM `streams_options` WHERE `stream_id` = ".intval($rStreamID)." AND `argument_id` = 17;");
                if ((isset($_POST["cookie"])) && (strlen($_POST["cookie"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rStreamID).", 17, '".ESC($_POST["cookie"])."');");
                }
            }
			if (isset($_POST["c_headers"])) {
                $db->query("DELETE FROM `streams_options` WHERE `stream_id` = ".intval($rStreamID)." AND `argument_id` = 19;");
                if ((isset($_POST["headers"])) && (strlen($_POST["headers"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rStreamID).", 19, '".ESC($_POST["headers"])."');");
                }
            }
            if (isset($_POST["c_bouquets"])) {
                $rBouquets = $_POST["bouquets"];
                foreach ($rBouquets as $rBouquet) {
                    addToBouquet("stream", $rBouquet, $rStreamID);
                }
                foreach (getBouquets() as $rBouquet) {
                    if (!in_array($rBouquet["id"], $rBouquets)) {
                        removeFromBouquet("stream", $rBouquet["id"], $rStreamID);
                    }
                }
            }
        }
        if (isset($_POST["restart_on_edit"])) {
            APIRequest(Array("action" => "stream", "sub" => "start", "stream_ids" => array_values($rStreamIDs)));
        }
		if (isset($_POST["c_bouquets"])) {
			scanBouquets();
		}
    }
    $_STATUS = 0;
}


$rStreamArguments = getStreamArguments();
$rTranscodeProfiles = getTranscodeProfiles();
$rServerTree = Array();
$rServerTree[] = Array("id" => "source", "parent" => "#", "text" => "<strong>Stream Source</strong>", "icon" => "mdi mdi-youtube-tv", "state" => Array("opened" => true));
foreach ($rServers as $rServer) {
    $rServerTree[] = Array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
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
                                    <a href="./streams.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Streams</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title">Mass Edit Streams <small id="selected_count"></small></h4>
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
                            Mass edit of streams was successfully executed!
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./stream_mass.php" method="POST" id="stream_form">
                                    <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                                    <input type="hidden" name="streams" id="streams" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#stream-selection" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-play mr-1"></i>
                                                    <span class="d-none d-sm-inline">Streams</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#stream-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#auto-restart" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-clock-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Auto Restart</span>
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
                                            <div class="tab-pane" id="stream-selection">
                                                <div class="row">
                                                    <div class="col-md-4 col-6">
                                                        <input type="text" class="form-control" id="stream_search" value="" placeholder="Search Streams...">
                                                    </div>
                                                    <div class="col-md-4 col-6">
                                                        <select id="category_search" class="form-control" data-toggle="select2">
                                                            <option value="" selected>All Categories</option>
                                                            <?php foreach ($rCategories as $rCategory) { ?>
                                                            <option value="<?=$rCategory["id"]?>"<?php if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo " selected"; } ?>><?=$rCategory["category_name"]?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <label class="col-md-1 col-2 col-form-label text-center" for="show_entries">Show</label>
                                                    <div class="col-md-2 col-8">
                                                        <select id="show_entries" class="form-control" data-toggle="select2">
                                                            <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                            <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1 col-2">
                                                        <button type="button" class="btn btn-info waves-effect waves-light" onClick="toggleStreams()">
                                                            <i class="mdi mdi-selection"></i>
                                                        </button>
                                                    </div>
                                                    <table id="datatable-mass" class="table table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="text-center">ID</th>
                                                                <th>Stream Name</th>
                                                                <th>Category</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="stream-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            To mass edit any of the below options, tick the checkbox next to it and change the input value.
                                                        </p>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="category_id" name="c_category_id">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="category_id">Category Name</label>
                                                            <div class="col-md-8">
                                                                <select disabled name="category_id" id="category_id" class="form-control" data-toggle="select2">
                                                                    <?php foreach ($rCategories as $rCategory) { ?>
                                                                    <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="bouquets" name="c_bouquets">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="bouquets">Select Bouquets</label>
                                                            <div class="col-md-8">
                                                                <select disabled name="bouquets[]" id="bouquets" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose...">
                                                                    <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                    <option value="<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="gen_timestamps" data-type="switch" name="c_gen_timestamps">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="gen_timestamps">Generate PTS</label>
                                                            <div class="col-md-2">
                                                                <input name="gen_timestamps" id="gen_timestamps" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="read_native">Native Frames</label>
                                                            <div class="col-md-2">
                                                                <input name="read_native" id="read_native" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="read_native" data-type="switch" name="c_read_native">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="stream_all" data-type="switch" name="c_stream_all">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="stream_all">Stream All Codecs</label>
                                                            <div class="col-md-2">
                                                                <input name="stream_all" id="stream_all" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="allow_record">Allow Recording</label>
                                                            <div class="col-md-2">
                                                                <input name="allow_record" id="allow_record" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="allow_record" data-type="switch" name="c_allow_record">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="rtmp_output" data-type="switch" name="c_rtmp_output">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="rtmp_output">Allow RTMP Output</label>
                                                            <div class="col-md-2">
                                                                <input name="rtmp_output" id="rtmp_output" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="direct_source">Direct Source</label>
                                                            <div class="col-md-2">
                                                                <input name="direct_source" id="direct_source" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="direct_source" data-type="switch" name="c_direct_source">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="custom_sid" name="c_custom_sid">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="custom_sid">Custom Channel SID</label>
                                                            <div class="col-md-2">
                                                                <input type="text" disabled class="form-control" id="custom_sid" name="custom_sid" value="">
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="delay_minutes">Minute Delay</label>
                                                            <div class="col-md-2">
                                                                <input type="text" disabled class="form-control" id="delay_minutes" name="delay_minutes" value="0">
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="delay_minutes" name="c_delay_minutes">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="custom_ffmpeg" name="c_custom_ffmpeg">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="custom_ffmpeg">Custom FFmpeg</label>
                                                            <div class="col-md-2">
                                                                <input type="text" disabled class="form-control" id="custom_ffmpeg" name="custom_ffmpeg" value="">
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="probesize_ondemand">On Demand Probesize</label>
                                                            <div class="col-md-2">
                                                                <input type="text" disabled class="form-control" id="probesize_ondemand" name="probesize_ondemand" value="128000">
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="probesize_ondemand" name="c_probesize_ondemand">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="user_agent" name="c_user_agent">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="user_agent">User Agent</label>
                                                            <div class="col-md-8">
                                                                <input type="text" disabled class="form-control" id="user_agent" name="user_agent" value="<?php echo htmlspecialchars($rStreamArguments["user_agent"]["argument_default_value"]); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="http_proxy" name="c_http_proxy">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="http_proxy">HTTP Proxy</label>
                                                            <div class="col-md-8">
                                                                <input type="text" disabled class="form-control" id="http_proxy" name="http_proxy" value="<?php echo htmlspecialchars($rStreamArguments["proxy"]["argument_default_value"]); ?>">
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="cookie" name="c_cookie">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="cookie">Cookie</label>
                                                            <div class="col-md-8">
                                                                <input type="text" disabled class="form-control" id="cookie" name="cookie" value="<?php echo htmlspecialchars($rStreamArguments["cookie"]["argument_default_value"]); ?>">
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="headers" name="c_headers">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="headers">Headers</label>
                                                            <div class="col-md-8">
                                                                <input type="text" disabled class="form-control" id="headers" name="headers" value="<?php echo htmlspecialchars($rStreamArguments["headers"]["argument_default_value"]); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="transcode_profile_id" name="c_transcode_profile_id">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="transcode_profile_id">Transcoding Profile</label>
                                                            <div class="col-md-8">
                                                                <select name="transcode_profile_id" disabled id="transcode_profile_id" class="form-control" data-toggle="select2">
                                                                    <option selected value="0">Transcoding Disabled</option>
                                                                    <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                    <option value="<?=$rProfile["profile_id"]?>"><?=$rProfile["profile_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <div class="tab-pane" id="auto-restart">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="days_to_restart" name="c_days_to_restart">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="days_to_restart">Days to Restart</label>
                                                            <div class="col-md-8">
                                                                <?php $rAutoRestart = Array("days" => Array(), "at" => "06:00"); ?>
                                                                <select disabled id="days_to_restart" name="days_to_restart[]" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                                                    <?php foreach (Array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday") as $rDay) { ?>
                                                                    <option value="<?=$rDay?>"><?=$rDay?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="col-md-1"></div>
                                                            <label class="col-md-3 col-form-label" for="time_to_restart">Time to Restart</label>
                                                            <div class="col-md-8">
                                                                <div class="input-group clockpicker" data-placement="top" data-align="top" data-autoclose="true">
                                                                    <input disabled id="time_to_restart" name="time_to_restart" type="text" class="form-control" value="<?=$rAutoRestart["at"]?>">
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text"><i class="mdi mdi-clock-outline"></i></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
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
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" data-name="on_demand" class="activate" name="c_server_tree" id="c_server_tree">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="server_tree">Server Tree</label>
                                                            <div class="col-md-8">
                                                                <div id="server_tree"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="col-md-1"></div>
                                                            <label class="col-md-3 col-form-label" for="on_demand">On Demand</label>
                                                            <div class="col-md-8">
                                                                <select disabled id="on_demand" name="on_demand[]" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                                                    <?php foreach($rServers as $rServerItem) { ?>
                                                                        <option value="<?=$rServerItem["id"]?>"><?=$rServerItem["server_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="tv_archive_server_id" name="c_tv_archive_server_id">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="tv_archive_server_id">Timeshift Server</label>
                                                            <div class="col-md-8">
                                                                <select disabled name="tv_archive_server_id" id="tv_archive_server_id" class="form-control" data-toggle="select2">
                                                                    <option value="">Timeshift Disabled</option>
                                                                    <?php foreach ($rServers as $rServer) { ?>
                                                                    <option value="<?=$rServer["id"]?>"><?=$rServer["server_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="tv_archive_duration" name="c_tv_archive_duration">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="tv_archive_duration">Timeshift Days</label>
                                                            <div class="col-md-3">
                                                                <input disabled type="text" class="form-control" id="tv_archive_duration" name="tv_archive_duration" value="0" />
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="restart_on_edit">Restart on Edit</label>
                                                            <div class="col-md-2">
                                                                <input name="restart_on_edit" id="restart_on_edit" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd" checked />
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_stream" type="submit" class="btn btn-primary" value="Edit Streams" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>

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
        <script src="assets/libs/jquery-ui/jquery-ui.min.js"></script>
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
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var rSwitches = [];
        var rSelected = [];
        
        function getCategory() {
            return $("#category_search").val();
        }
        function toggleStreams() {
            $("#datatable-mass tr").each(function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.splice($.inArray($(this).find("td:eq(0)").html(), window.rSelected), 1);
                    }
                } else {            
                    $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.push($(this).find("td:eq(0)").html());
                    }
                }
            });
            $("#selected_count").html(" - " + window.rSelected.length + " selected")
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
        $(document).ready(function() {
            $('select').select2({width: '100%'})
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
                var switchery = new Switchery(html);
                window.rSwitches[$(html).attr("id")] = switchery;
                if ($(html).attr("id") != "restart_on_edit") {
                    window.rSwitches[$(html).attr("id")].disable();
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
                $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {flat:true})));
                rPass = false;
                $.each($('#server_tree').jstree(true).get_json('#', {flat:true}), function(k,v) {
                    if (v.parent == "source") {
                        rPass = true;
                    }
                });
                if ((rPass == false) && ($("#c_server_tree").is(":checked"))) {
                    e.preventDefault();
                    $.toast("Select at least one server.");
                }
                $("#streams").val(JSON.stringify(window.rSelected));
                if (window.rSelected.length == 0) {
                    e.preventDefault();
                    $.toast("Select at least one stream to edit.");
                }
            });
            $("input[type=checkbox].activate").change(function() {
                if ($(this).is(":checked")) {
                    if ($(this).data("type") == "switch") {
                        window.rSwitches[$(this).data("name")].enable();
                    } else {
                        $("#" + $(this).data("name")).prop("disabled", false);
                        if ($(this).data("name") == "days_to_restart") {
                            $("#time_to_restart").prop("disabled", false);
                        }
                    }
                } else {
                    if ($(this).data("type") == "switch") {
                        window.rSwitches[$(this).data("name")].disable();
                    } else {
                        $("#" + $(this).data("name")).prop("disabled", true);
                        if ($(this).data("name") == "days_to_restart") {
                            $("#time_to_restart").prop("disabled", true);
                        }
                    }
                }
            });
            $(".clockpicker").clockpicker();
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $("#probesize_ondemand").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#delay_minutes").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#tv_archive_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            rTable = $("#datatable-mass").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "stream_list",
                        d.category = getCategory()
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0]}
                ],
                "rowCallback": function(row, data) {
                    if ($.inArray(data[0], window.rSelected) !== -1) {
                        $(row).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    }
                },
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>
            });
            $('#stream_search').keyup(function(){
                rTable.search($(this).val()).draw();
            })
            $('#show_entries').change(function(){
                rTable.page.len($(this).val()).draw();
            })
            $('#category_search').change(function(){
                rTable.ajax.reload(null, false);
            })
            $("#datatable-mass").selectable({
                filter: 'tr',
                selected: function (event, ui) {
                    if ($(ui.selected).hasClass('selectedfilter')) {
                        $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                        window.rSelected.splice($.inArray($(ui.selected).find("td:eq(0)").html(), window.rSelected), 1);
                    } else {            
                        $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                        window.rSelected.push($(ui.selected).find("td:eq(0)").html());
                    }
                    $("#selected_count").html(" - " + window.rSelected.length + " selected")
                }
            });
        });
        </script>
    </body>
</html>