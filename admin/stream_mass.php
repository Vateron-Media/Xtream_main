<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "mass_edit_streams"))) {
    exit;
}

if (isset(ipTV_lib::$request["submit_stream"])) {
    $rArray = array();
    if (isset(ipTV_lib::$request["c_days_to_restart"])) {
        if ((isset(ipTV_lib::$request["days_to_restart"])) && (preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", ipTV_lib::$request["time_to_restart"]))) {
            $rTimeArray = array("days" => array(), "at" => ipTV_lib::$request["time_to_restart"]);
            foreach (ipTV_lib::$request["days_to_restart"] as $rID => $rDay) {
                $rTimeArray["days"][] = $rDay;
            }
            $rArray["auto_restart"] = json_encode($rTimeArray);
        } else {
            $rArray["auto_restart"] = "";
        }
    }
    if (isset(ipTV_lib::$request["c_gen_timestamps"])) {
        if (isset(ipTV_lib::$request["gen_timestamps"])) {
            $rArray["gen_timestamps"] = 1;
        } else {
            $rArray["gen_timestamps"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_allow_record"])) {
        if (isset(ipTV_lib::$request["allow_record"])) {
            $rArray["allow_record"] = 1;
        } else {
            $rArray["allow_record"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_rtmp_output"])) {
        if (isset(ipTV_lib::$request["rtmp_output"])) {
            $rArray["rtmp_output"] = 1;
        } else {
            $rArray["rtmp_output"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_stream_all"])) {
        if (isset(ipTV_lib::$request["stream_all"])) {
            $rArray["stream_all"] = 1;
        } else {
            $rArray["stream_all"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_direct_source"])) {
        if (isset(ipTV_lib::$request["direct_source"])) {
            $rArray["direct_source"] = 1;
        } else {
            $rArray["direct_source"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_read_native"])) {
        if (isset(ipTV_lib::$request["read_native"])) {
            $rArray["read_native"] = 1;
        } else {
            $rArray["read_native"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_tv_archive_server_id"])) {
        if (isset(ipTV_lib::$request["tv_archive_server_id"])) {
            $rArray["tv_archive_server_id"] = intval(ipTV_lib::$request["tv_archive_server_id"]);
        } else {
            $rArray["tv_archive_server_id"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_tv_archive_duration"])) {
        if (isset(ipTV_lib::$request["tv_archive_duration"])) {
            $rArray["tv_archive_duration"] = intval(ipTV_lib::$request["tv_archive_duration"]);
        } else {
            $rArray["tv_archive_duration"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_delay_minutes"])) {
        if (isset(ipTV_lib::$request["delay_minutes"])) {
            $rArray["delay_minutes"] = intval(ipTV_lib::$request["delay_minutes"]);
        } else {
            $rArray["delay_minutes"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_probesize_ondemand"])) {
        if (isset(ipTV_lib::$request["probesize_ondemand"])) {
            $rArray["probesize_ondemand"] = intval(ipTV_lib::$request["probesize_ondemand"]);
        } else {
            $rArray["probesize_ondemand"] = 0;
        }
    }
    if (isset(ipTV_lib::$request["c_category_id"])) {
        $rArray["category_id"] = intval(ipTV_lib::$request["category_id"]);
    }
    if (isset(ipTV_lib::$request["c_custom_sid"])) {
        $rArray["custom_sid"] = ipTV_lib::$request["custom_sid"];
    }
    if (isset(ipTV_lib::$request["c_custom_ffmpeg"])) {
        $rArray["custom_ffmpeg"] = ipTV_lib::$request["custom_ffmpeg"];
    }
    if (isset(ipTV_lib::$request["c_transcode_profile_id"])) {
        $rArray["transcode_profile_id"] = ipTV_lib::$request["transcode_profile_id"];
        if ($rArray["transcode_profile_id"] > 0) {
            $rArray["enable_transcode"] = 1;
        } else {
            $rArray["enable_transcode"] = 0;
        }
    }
    $rStreamIDs = json_decode(ipTV_lib::$request["streams"], true);
    if (count($rStreamIDs) > 0) {
        foreach ($rStreamIDs as $rStreamID) {
            $rQueries = array();
            foreach ($rArray as $rKey => $rValue) {
                $rQueries[] = "`" . $rKey . "` = '" . $rValue . "'";
            }
            if (count($rQueries) > 0) {
                $rQueryString = join(",", $rQueries);
                $rQuery = "UPDATE `streams` SET " . $rQueryString . " WHERE `id` = " . intval($rStreamID) . ";";
                if (!$ipTV_db_admin->query($rQuery)) {
                    $_STATUS = 1;
                }
            }
            if (isset(ipTV_lib::$request["c_server_tree"])) {
                $rOnDemandArray = array();
                if (isset(ipTV_lib::$request["on_demand"])) {
                    foreach (ipTV_lib::$request["on_demand"] as $rID) {
                        $rOnDemandArray[] = intval($rID);
                    }
                }
                $rStreamExists = array();
                $ipTV_db_admin->query("SELECT `server_stream_id`, `server_id` FROM `streams_servers` WHERE `stream_id` = " . intval($rStreamID) . ";");
                if ($ipTV_db_admin->num_rows() > 0) {
                    foreach ($ipTV_db_admin->get_rows() as $row) {
                        $rStreamExists[intval($row["server_id"])] = intval($row["server_stream_id"]);
                    }
                }
                $rStreamsAdded = array();
                $rServerTree = json_decode(ipTV_lib::$request["server_tree_data"], true);
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
                            if (!$ipTV_db_admin->query("UPDATE `streams_servers` SET `parent_id` = " . $rParent . ", `on_demand` = " . $rOD . " WHERE `server_stream_id` = " . $rStreamExists[$rServerID] . ";")) {
                                $_STATUS = 1;
                            }
                        } else {
                            if (!$ipTV_db_admin->query("INSERT INTO `streams_servers`(`stream_id`, `server_id`, `parent_id`, `on_demand`) VALUES(" . intval($rStreamID) . ", " . $rServerID . ", " . $rParent . ", " . $rOD . ");")) {
                                $_STATUS = 1;
                            }
                        }
                    }
                }
                foreach ($rStreamExists as $rServerID => $rDBID) {
                    if (!in_array($rServerID, $rStreamsAdded)) {
                        $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `server_stream_id` = " . $rDBID . ";");
                    }
                }
            }
            if (isset(ipTV_lib::$request["c_user_agent"])) {
                $ipTV_db_admin->query("DELETE FROM `streams_options` WHERE `stream_id` = " . intval($rStreamID) . " AND `argument_id` = 1;");
                if ((isset(ipTV_lib::$request["user_agent"])) && (strlen(ipTV_lib::$request["user_agent"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rStreamID) . ", 1, '" . ipTV_lib::$request["user_agent"] . "');");
                }
            }
            if (isset(ipTV_lib::$request["c_http_proxy"])) {
                $ipTV_db_admin->query("DELETE FROM `streams_options` WHERE `stream_id` = " . intval($rStreamID) . " AND `argument_id` = 2;");
                if ((isset(ipTV_lib::$request["http_proxy"])) && (strlen(ipTV_lib::$request["http_proxy"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rStreamID) . ", 2, '" . ipTV_lib::$request["http_proxy"] . "');");
                }
            }
            if (isset(ipTV_lib::$request["c_cookie"])) {
                $ipTV_db_admin->query("DELETE FROM `streams_options` WHERE `stream_id` = " . intval($rStreamID) . " AND `argument_id` = 17;");
                if ((isset(ipTV_lib::$request["cookie"])) && (strlen(ipTV_lib::$request["cookie"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rStreamID) . ", 17, '" . ipTV_lib::$request["cookie"] . "');");
                }
            }
            if (isset(ipTV_lib::$request["c_headers"])) {
                $ipTV_db_admin->query("DELETE FROM `streams_options` WHERE `stream_id` = " . intval($rStreamID) . " AND `argument_id` = 19;");
                if ((isset(ipTV_lib::$request["headers"])) && (strlen(ipTV_lib::$request["headers"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rStreamID) . ", 19, '" . ipTV_lib::$request["headers"] . "');");
                }
            }
            if (isset(ipTV_lib::$request["c_bouquets"])) {
                $rBouquets = ipTV_lib::$request["bouquets"];
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
        if (isset(ipTV_lib::$request["restart_on_edit"])) {
            APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array_values($rStreamIDs)));
        }
        if (isset(ipTV_lib::$request["c_bouquets"])) {
            scanBouquets();
        }
    }
    $_STATUS = 0;
}


$rStreamArguments = getStreamArguments();
$rTranscodeProfiles = getTranscodeProfiles();
$rServerTree = array();
$rServerTree[] = array("id" => "source", "parent" => "#", "text" => "<strong>Stream Source</strong>", "icon" => "mdi mdi-youtube-tv", "state" => array("opened" => true));
foreach ($rServers as $rServer) {
    $rServerTree[] = array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => array("opened" => true));
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) { ?>
    <div class="content-page">
        <div class="content boxed-layout">
            <div class="container-fluid">
            <?php } else { ?>
                <div class="wrapper boxed-layout">
                    <div class="container-fluid">
                    <?php } ?>
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <a href="./streams.php">
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                                <?= $_["back_to_streams"] ?> </li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?= $_["mass_edit_streams"] ?> <small
                                        id="selected_count"></small></h4>
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
                                    <?= $_["mass_edit_of_streams"] ?>
                                </div>
                            <?php } elseif ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["generic_fail"] ?>
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
                                                    <a href="#stream-selection" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-play mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["streams"] ?> </span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#stream-details" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?> </span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#auto-restart" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-clock-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["auto_restart"] ?>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#load-balancing" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-server-network mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["servers"] ?> </span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="stream-selection">
                                                    <div class="row">
                                                        <div class="col-md-4 col-6">
                                                            <input type="text" class="form-control" id="stream_search"
                                                                value="" placeholder="<?= $_["search_streams"] ?>">
                                                        </div>
                                                        <div class="col-md-4 col-6">
                                                            <select id="category_search" class="form-control"
                                                                data-toggle="select2">
                                                                <option value="" selected><?= $_["all_categories"] ?>
                                                                </option>
                                                                <?php foreach ($rCategories as $rCategory) { ?>
                                                                    <option value="<?= $rCategory["id"] ?>" <?php if ((isset(ipTV_lib::$request["category"])) && (ipTV_lib::$request["category"] == $rCategory["id"])) {
                                                                          echo " selected";
                                                                      } ?>><?= $rCategory["category_name"] ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <label class="col-md-1 col-2 col-form-label text-center"
                                                            for="show_entries"><?= $_["show"] ?> </label>
                                                        <div class="col-md-2 col-8">
                                                            <select id="show_entries" class="form-control"
                                                                data-toggle="select2">
                                                                <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                                    <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                                        echo " selected";
                                                                    } ?> value="<?= $rShow ?>">
                                                                        <?= $rShow ?></option>
                                                                    <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-1 col-2">
                                                            <button type="button"
                                                                class="btn btn-info waves-effect waves-light"
                                                                onClick="toggleStreams()">
                                                                <i class="mdi mdi-selection"></i>
                                                            </button>
                                                        </div>
                                                        <table id="datatable-mass"
                                                            class="table table-hover table-borderless mb-0">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th class="text-center"><?= $_["id"] ?> </th>
                                                                    <th><?= $_["streams_name"] ?> </th>
                                                                    <th><?= $_["categoty"] ?> </th>
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
                                                                <?= $_["mass_edit_info"] ?>
                                                            </p>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="category_id" name="c_category_id">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="category_id"><?= $_["category_name"] ?>
                                                                </label>
                                                                <div class="col-md-8">
                                                                    <select disabled name="category_id" id="category_id"
                                                                        class="form-control" data-toggle="select2">
                                                                        <?php foreach ($rCategories as $rCategory) { ?>
                                                                            <option value="<?= $rCategory["id"] ?>">
                                                                                <?= $rCategory["category_name"] ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="bouquets" name="c_bouquets">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="bouquets"><?= $_["select_bouquets"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <select disabled name="bouquets[]" id="bouquets"
                                                                        class="form-control select2-multiple"
                                                                        data-toggle="select2" multiple="multiple"
                                                                        data-placeholder="<?= $_["choose"] ?>">
                                                                        <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                            <option value="<?= $rBouquet["id"] ?>">
                                                                                <?= $rBouquet["bouquet_name"] ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="gen_timestamps" data-type="switch"
                                                                        name="c_gen_timestamps">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="gen_timestamps"><?= $_["generate_pts"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input name="gen_timestamps" id="gen_timestamps"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="read_native"><?= $_["native_frames"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input name="read_native" id="read_native"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="read_native" data-type="switch"
                                                                        name="c_read_native">
                                                                    <label></label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="stream_all" data-type="switch"
                                                                        name="c_stream_all">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="stream_all"><?= $_["stream_all_codecs"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input name="stream_all" id="stream_all"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="allow_record"><?= $_["allow_recording"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input name="allow_record" id="allow_record"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="allow_record" data-type="switch"
                                                                        name="c_allow_record">
                                                                    <label></label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="rtmp_output" data-type="switch"
                                                                        name="c_rtmp_output">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="rtmp_output"><?= $_["allow_rtmp_output"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input name="rtmp_output" id="rtmp_output"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="direct_source"><?= $_["direct_source"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input name="direct_source" id="direct_source"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="direct_source" data-type="switch"
                                                                        name="c_direct_source">
                                                                    <label></label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="custom_sid" name="c_custom_sid">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="custom_sid"><?= $_["custom_channel_sid"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="custom_sid" name="custom_sid" value="">
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="delay_minutes"><?= $_["minute_delay"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="delay_minutes" name="delay_minutes"
                                                                        value="0">
                                                                </div>
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="delay_minutes"
                                                                        name="c_delay_minutes">
                                                                    <label></label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="custom_ffmpeg"
                                                                        name="c_custom_ffmpeg">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="custom_ffmpeg"><?= $_["custom_ffmpeg_command"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="custom_ffmpeg" name="custom_ffmpeg"
                                                                        value="">
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="probesize_ondemand"><?= $_["on_demand_probesize"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="probesize_ondemand"
                                                                        name="probesize_ondemand" value="128000">
                                                                </div>
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="probesize_ondemand"
                                                                        name="c_probesize_ondemand">
                                                                    <label></label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="user_agent" name="c_user_agent">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="user_agent"><?= $_["user_agent"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="user_agent" name="user_agent"
                                                                        value="<?php echo htmlspecialchars($rStreamArguments["user_agent"]["argument_default_value"]); ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="http_proxy" name="c_http_proxy">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="http_proxy"><?= $_["http_proxy"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="http_proxy" name="http_proxy"
                                                                        value="<?php echo htmlspecialchars($rStreamArguments["proxy"]["argument_default_value"]); ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="cookie" name="c_cookie">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="cookie"><?= $_["cookie"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="cookie" name="cookie"
                                                                        value="<?php echo htmlspecialchars($rStreamArguments["cookie"]["argument_default_value"]); ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="headers" name="c_headers">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="headers"><?= $_["headers"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <input type="text" disabled class="form-control"
                                                                        id="headers" name="headers"
                                                                        value="<?php echo htmlspecialchars($rStreamArguments["headers"]["argument_default_value"]); ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="transcode_profile_id"
                                                                        name="c_transcode_profile_id">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="transcode_profile_id"><?= $_["transcoding_profile"] ?>
                                                                </label>
                                                                <div class="col-md-8">
                                                                    <select name="transcode_profile_id" disabled
                                                                        id="transcode_profile_id" class="form-control"
                                                                        data-toggle="select2">
                                                                        <option selected value="0">
                                                                            <?= $_["transcoding_disabled"] ?>
                                                                        </option>
                                                                        <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                            <option value="<?= $rProfile["profile_id"] ?>">
                                                                                <?= $rProfile["profile_name"] ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["prev"] ?> </a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["next"] ?> </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="tab-pane" id="auto-restart">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="days_to_restart"
                                                                        name="c_days_to_restart">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="days_to_restart"><?= $_["days_to_restart"] ?>
                                                                </label>
                                                                <div class="col-md-8">
                                                                    <?php $rAutoRestart = array("days" => array(), "at" => "06:00"); ?>
                                                                    <select disabled id="days_to_restart"
                                                                        name="days_to_restart[]"
                                                                        class="form-control select2-multiple"
                                                                        data-toggle="select2" multiple="multiple"
                                                                        data-placeholder="<?= $_["choose"] ?>">
                                                                        <?php foreach (array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday") as $rDay) { ?>
                                                                            <option value="<?= $rDay ?>"><?= $rDay ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div class="col-md-1"></div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="time_to_restart"><?= $_["time_to_restart"] ?>
                                                                </label>
                                                                <div class="col-md-8">
                                                                    <div class="input-group clockpicker"
                                                                        data-placement="top" data-align="top"
                                                                        data-autoclose="true">
                                                                        <input disabled id="time_to_restart"
                                                                            name="time_to_restart" type="text"
                                                                            class="form-control"
                                                                            value="<?= $rAutoRestart["at"] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text"><i
                                                                                    class="mdi mdi-clock-outline"></i></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["prev"] ?> </a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["next"] ?> </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-pane" id="load-balancing">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" data-name="on_demand"
                                                                        class="activate" name="c_server_tree"
                                                                        id="c_server_tree">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="server_tree"><?= $_["server_tree"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <div id="server_tree"></div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div class="col-md-1"></div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="on_demand"><?= $_["on_demand"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <select disabled id="on_demand" name="on_demand[]"
                                                                        class="form-control select2-multiple"
                                                                        data-toggle="select2" multiple="multiple"
                                                                        data-placeholder="<?= $_["choose"] ?>">
                                                                        <?php foreach ($rServers as $rServerItem) { ?>
                                                                            <option value="<?= $rServerItem["id"] ?>">
                                                                                <?= $rServerItem["server_name"] ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="tv_archive_server_id"
                                                                        name="c_tv_archive_server_id">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="tv_archive_server_id"><?= $_["timeshift_server"] ?>
                                                                </label>
                                                                <div class="col-md-8">
                                                                    <select disabled name="tv_archive_server_id"
                                                                        id="tv_archive_server_id" class="form-control"
                                                                        data-toggle="select2">
                                                                        <option value=""><?= $_["timeshift_disabled"] ?>
                                                                        </option>
                                                                        <?php foreach ($rServers as $rServer) { ?>
                                                                            <option value="<?= $rServer["id"] ?>">
                                                                                <?= $rServer["server_name"] ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <div
                                                                    class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                    <input type="checkbox" class="activate"
                                                                        data-name="tv_archive_duration"
                                                                        name="c_tv_archive_duration">
                                                                    <label></label>
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="tv_archive_duration"><?= $_["timeshift_days"] ?>
                                                                </label>
                                                                <div class="col-md-3">
                                                                    <input disabled type="text" class="form-control"
                                                                        id="tv_archive_duration"
                                                                        name="tv_archive_duration" value="0" />
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="restart_on_edit"><?= $_["restart_on_edit"] ?>
                                                                </label>
                                                                <div class="col-md-2">
                                                                    <input name="restart_on_edit" id="restart_on_edit"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd"
                                                                        checked />
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["prev"] ?> </a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <input name="submit_stream" type="submit"
                                                                class="btn btn-primary"
                                                                value="<?= $_["edit_streams"] ?>" />
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
            <?php if ($rSettings["sidebar"]) {
                echo "</div>";
            } ?>

            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 copyright text-center"><?= getFooter() ?></div>
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
                    $("#datatable-mass tr").each(function () {
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
                (function ($) {
                    $.fn.inputFilter = function (inputFilter) {
                        return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
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
                $(document).ready(function () {
                    $('select').select2({
                        width: '100%'
                    })
                    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                    elems.forEach(function (html) {
                        var switchery = new Switchery(html);
                        window.rSwitches[$(html).attr("id")] = switchery;
                        if ($(html).attr("id") != "restart_on_edit") {
                            window.rSwitches[$(html).attr("id")].disable();
                        }
                    });
                    $('#server_tree').jstree({
                        'core': {
                            'check_callback': function (op, node, parent, position, more) {
                                switch (op) {
                                    case 'move_node':
                                        if (node.id == "source") {
                                            return false;
                                        }
                                        return true;
                                }
                            },
                            'data': <?= json_encode($rServerTree) ?>
                        },
                        "plugins": ["dnd"]
                    });
                    $("#stream_form").submit(function (e) {
                        $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {
                            flat: true
                        })));
                        rPass = false;
                        $.each($('#server_tree').jstree(true).get_json('#', {
                            flat: true
                        }), function (k, v) {
                            if (v.parent == "source") {
                                rPass = true;
                            }
                        });
                        if ((rPass == false) && ($("#c_server_tree").is(":checked"))) {
                            e.preventDefault();
                            $.toast("<?= $_["select_at_least_one_server"] ?>");
                        }
                        $("#streams").val(JSON.stringify(window.rSelected));
                        if (window.rSelected.length == 0) {
                            e.preventDefault();
                            $.toast("<?= $_["select_at_least_one_stream_to_edit"] ?>");
                        }
                    });
                    $("input[type=checkbox].activate").change(function () {
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
                    $(window).keypress(function (event) {
                        if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                    });
                    $("#probesize_ondemand").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#delay_minutes").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("#tv_archive_duration").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("form").attr('autocomplete', 'off');
                    rTable = $("#datatable-mass").DataTable({
                        language: {
                            paginate: {
                                previous: "<i class='mdi mdi-chevron-left'>",
                                next: "<i class='mdi mdi-chevron-right'>"
                            }
                        },
                        drawCallback: function () {
                            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                        },
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "./table_search.php",
                            "data": function (d) {
                                d.id = "stream_list",
                                    d.category = getCategory()
                            }
                        },
                        columnDefs: [{
                            "className": "dt-center",
                            "targets": [0]
                        }],
                        "rowCallback": function (row, data) {
                            if ($.inArray(data[0], window.rSelected) !== -1) {
                                $(row).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                            }
                        },
                        pageLength: <?= $rSettings["default_entries"] ?: 10 ?>
                    });
                    $('#stream_search').keyup(function () {
                        rTable.search($(this).val()).draw();
                    })
                    $('#show_entries').change(function () {
                        rTable.page.len($(this).val()).draw();
                    })
                    $('#category_search').change(function () {
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