<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_radio")) && (!hasPermissions("adv", "edit_radio")))) {
    exit;
}

if (isset(ipTV_lib::$request["submit_radio"])) {
    if (isset(ipTV_lib::$request["edit"])) {
        if (!hasPermissions("adv", "edit_radio")) {
            exit;
        }
        $rArray = getStream(ipTV_lib::$request["edit"]);
        unset($rArray["id"]);
    } else {
        if (!hasPermissions("adv", "add_radio")) {
            exit;
        }
        $rArray = array("type" => 4, "added" => time(), "read_native" => 0, "stream_all" => 0, "redirect_stream" => 1, "direct_source" => 0, "gen_timestamps" => 0, "transcode_attributes" => array(), "stream_display_name" => "", "stream_source" => array(), "category_id" => array(), "stream_icon" => "", "notes" => "", "custom_sid" => "", "custom_ffmpeg" => "", "custom_map" => "", "transcode_profile_id" => 0, "enable_transcode" => 0, "auto_restart" => "[]", "allow_record" => 0, "rtmp_output" => 0, "epg_id" => null, "channel_id" => null, "epg_lang" => null, "tv_archive_server_id" => 0, "tv_archive_duration" => 0, "delay_minutes" => 0, "external_push" => array(), "probesize_ondemand" => 128000);
    }
    if ((isset(ipTV_lib::$request["days_to_restart"])) && (preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", ipTV_lib::$request["time_to_restart"]))) {
        $rTimeArray = array("days" => array(), "at" => ipTV_lib::$request["time_to_restart"]);
        foreach (ipTV_lib::$request["days_to_restart"] as $rID => $rDay) {
            $rTimeArray["days"][] = $rDay;
        }
        $rArray["auto_restart"] = $rTimeArray;
    } else {
        $rArray["auto_restart"] = "";
    }
    $rOnDemandArray = array();
    if (isset(ipTV_lib::$request["on_demand"])) {
        foreach (ipTV_lib::$request["on_demand"] as $rID) {
            $rOnDemandArray[] = $rID;
        }
    }
    if (isset(ipTV_lib::$request["custom_ffmpeg"])) {
        $rArray["custom_ffmpeg"] = ipTV_lib::$request["custom_ffmpeg"];
    }
    if (isset(ipTV_lib::$request["custom_sid"])) {
        $rArray["custom_sid"] = ipTV_lib::$request["custom_sid"];
    }
    if (isset(ipTV_lib::$request["direct_source"])) {
        $rArray["direct_source"] = 1;
        unset(ipTV_lib::$request["direct_source"]);
    } else {
        $rArray["direct_source"] = 0;
    }
    if (isset(ipTV_lib::$request["probesize_ondemand"])) {
        $rArray["probesize_ondemand"] = intval(ipTV_lib::$request["probesize_ondemand"]);
        unset(ipTV_lib::$request["probesize_ondemand"]);
    } else {
        $rArray["probesize_ondemand"] = 128000;
    }
    if (isset(ipTV_lib::$request["restart_on_edit"])) {
        $rRestart = true;
        unset(ipTV_lib::$request["restart_on_edit"]);
    } else {
        $rRestart = false;
    }
    $rBouquets = ipTV_lib::$request["bouquets"];
    unset(ipTV_lib::$request["bouquets"]);
    foreach (ipTV_lib::$request as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    $rImportStreams = array();
    if (strlen(ipTV_lib::$request["stream_source"][0]) > 0) {
        $rImportArray = array("stream_source" => ipTV_lib::$request["stream_source"], "stream_icon" => $rArray["stream_icon"], "stream_display_name" => $rArray["stream_display_name"]);
        if (isset(ipTV_lib::$request["edit"])) {
            $rImportStreams[] = $rImportArray;
        } else {
            $ipTV_db_admin->query("SELECT COUNT(`id`) AS `count` FROM `streams` WHERE `stream_display_name` = '" . $rImportArray["stream_display_name"] . "' AND `type` = 4;");
            if ($ipTV_db_admin->get_row()["count"] == 0) {
                $rImportStreams[] = $rImportArray;
            } else {
                $_STATUS = 2;
                $rStation = $rArray;
            }
        }
    } else {
        $_STATUS = 1;
    }
    if (count($rImportStreams) > 0) {
        foreach ($rImportStreams as $rImportStream) {
            $rImportArray = $rArray;
            if ($rSettings["download_images"]) {
                $rImportStream["stream_icon"] = downloadImage($rImportStream["stream_icon"]);
            }
            foreach (array_keys($rImportStream) as $rKey) {
                $rImportArray[$rKey] = $rImportStream[$rKey];
            }
            $rImportArray["order"] = getNextOrder();
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
                    $rValues .= '\'' . $rValue . '\'';
                }
            }
            if (isset(ipTV_lib::$request["edit"])) {
                $rCols = "`id`," . $rCols;
                $rValues = ipTV_lib::$request["edit"] . "," . $rValues;
            }
            $rQuery = "REPLACE INTO `streams`(" . $rCols . ") VALUES(" . $rValues . ");";
            if ($ipTV_db_admin->query($rQuery)) {
                if (isset(ipTV_lib::$request["edit"])) {
                    $rInsertID = intval(ipTV_lib::$request["edit"]);
                } else {
                    $rInsertID = $ipTV_db_admin->last_insert_id();
                }
            }
            if (isset($rInsertID)) {
                $rStationExists = array();
                if (isset(ipTV_lib::$request["edit"])) {
                    $ipTV_db_admin->query("SELECT `server_stream_id`, `server_id` FROM `streams_servers` WHERE `stream_id` = " . intval($rInsertID) . ";");
                    if ($ipTV_db_admin->num_rows() > 0) {
                        foreach ($ipTV_db_admin->get_rows() as $row) {
                            $rStationExists[intval($row["server_id"])] = intval($row["server_stream_id"]);
                        }
                    }
                }
                if (isset(ipTV_lib::$request["server_tree_data"])) {
                    $rStationsAdded = array();
                    $rServerTree = json_decode(ipTV_lib::$request["server_tree_data"], true);
                    foreach ($rServerTree as $rServer) {
                        if ($rServer["parent"] <> "#") {
                            $rServerID = intval($rServer["id"]);
                            $rStationsAdded[] = $rServerID;
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
                            if (isset($rStationExists[$rServerID])) {
                                $ipTV_db_admin->query("UPDATE `streams_servers` SET `parent_id` = " . $rParent . ", `on_demand` = " . $rOD . " WHERE `server_stream_id` = " . $rStationExists[$rServerID] . ";");
                            } else {
                                $ipTV_db_admin->query("INSERT INTO `streams_servers`(`stream_id`, `server_id`, `parent_id`, `on_demand`) VALUES(" . intval($rInsertID) . ", " . $rServerID . ", " . $rParent . ", " . $rOD . ");");
                            }
                        }
                    }
                    foreach ($rStationExists as $rServerID => $rDBID) {
                        if (!in_array($rServerID, $rStationsAdded)) {
                            $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `server_stream_id` = " . $rDBID . ";");
                        }
                    }
                }
                $ipTV_db_admin->query("DELETE FROM `streams_options` WHERE `stream_id` = " . intval($rInsertID) . ";");
                if ((isset(ipTV_lib::$request["user_agent"])) && (strlen(ipTV_lib::$request["user_agent"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rInsertID) . ", 1, '" . ipTV_lib::$request["user_agent"] . "');");
                }
                if ((isset(ipTV_lib::$request["http_proxy"])) && (strlen(ipTV_lib::$request["http_proxy"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rInsertID) . ", 2, '" . ipTV_lib::$request["http_proxy"] . "');");
                }
                if ((isset(ipTV_lib::$request["cookie"])) && (strlen(ipTV_lib::$request["cookie"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rInsertID) . ", 17, '" . ipTV_lib::$request["cookie"] . "');");
                }
                if ((isset(ipTV_lib::$request["headers"])) && (strlen(ipTV_lib::$request["headers"]) > 0)) {
                    $ipTV_db_admin->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(" . intval($rInsertID) . ", 19, '" . ipTV_lib::$request["headers"] . "');");
                }
                if ($rRestart) {
                    APIRequest(array("action" => "stream", "sub" => "start", "stream_ids" => array($rInsertID)));
                }
                foreach ($rBouquets as $rBouquet) {
                    addToBouquet("radio", $rBouquet, $rInsertID);
                }
                if (isset(ipTV_lib::$request["edit"])) {
                    foreach (getBouquets() as $rBouquet) {
                        if (!in_array($rBouquet["id"], $rBouquets)) {
                            removeFromBouquet("radio", $rBouquet["id"], $rInsertID);
                        }
                    }
                }
                $_STATUS = 0;
            } else {
                $_STATUS = 1;
                $rStation = $rArray;
            }
        }
        scanBouquets();
        if (isset($rInsertID)) {
            header("Location: ./radio.php?id=" . $rInsertID);
            exit;
        }
    } else {
        if (!isset($_STATUS)) {
            $_STATUS = 3;
            $rStation = $rArray;
        }
    }
}

if (isset($_STATUS)) {
    foreach ($rStation as $rKey => $rValue) {
        if (is_array($rValue)) {
            $rStation[$rKey] = json_encode($rValue);
        }
    }
}

$rStationArguments = getStreamArguments();
$rServerTree = array();
$rOnDemand = array();
$rServerTree[] = array("id" => "source", "parent" => "#", "text" => "<strong>" . $_["stream_source"] . "</strong>", "icon" => "mdi mdi-youtube-tv", "state" => array("opened" => true));
if (isset(ipTV_lib::$request["id"])) {
    if (!hasPermissions("adv", "edit_radio")) {
        exit;
    }
    $rStation = getStream(ipTV_lib::$request["id"]);
    if ((!$rStation) or ($rStation["type"] <> 4)) {
        exit;
    }
    $rStationOptions = getStreamOptions(ipTV_lib::$request["id"]);
    $rStationSys = getStreamSys(ipTV_lib::$request["id"]);
    foreach ($rServers as $rServer) {
        if (isset($rStationSys[intval($rServer["id"])])) {
            if ($rStationSys[intval($rServer["id"])]["parent_id"] <> 0) {
                $rParent = intval($rStationSys[intval($rServer["id"])]["parent_id"]);
            } else {
                $rParent = "source";
            }
        } else {
            $rParent = "#";
        }
        $rServerTree[] = array("id" => $rServer["id"], "parent" => $rParent, "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => array("opened" => true));
    }
    foreach ($rStationSys as $rStationItem) {
        if ($rStationItem["on_demand"] == 1) {
            $rOnDemand[] = $rStationItem["server_id"];
        }
    }
} else {
    if (!hasPermissions("adv", "add_radio")) {
        exit;
    }
    foreach ($rServers as $rServer) {
        $rServerTree[] = array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => array("opened" => true));
    }
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
                                        <li>
                                            <a href="./radios.php">
                                                <button type="button"
                                                    class="btn btn-primary waves-effect waves-light btn-sm">
                                                    <?= $_["view_stations"] ?>
                                                </button>
                                            </a>
                                        </li>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php if (isset($rStation["id"])) {
                                    echo $rStation["stream_display_name"];
                                                       } else {
                                                           echo $_["add_radio_station"];
                                                       } ?></h4>
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
                                    <?= $_["radio_success"] ?>
                                </div>
                            <?php } elseif ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    <?= $_["radio_info_1"] ?>
                                    </div>
                            <?php } elseif ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                    <?= $_["radio_info_2"] ?>
                                        </div>
                            <?php }
                            if (isset($rStation["id"])) { ?>
                                <div class="card text-xs-center">
                                    <div class="table">
                                        <table id="datatable" class="table table-borderless mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                    <th><?= $_["source"] ?></th>
                                                    <th><?= $_["clients"] ?></th>
                                                    <th><?= $_["uptime"] ?></th>
                                                    <th><?= $_["actions"] ?></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="7" class="text-center">
                                                        <?= $_["loading_station_information"] ?>...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./radio.php<?php if (isset(ipTV_lib::$request["id"])) {
                                        echo "?id=" . ipTV_lib::$request["id"];
                                                             } ?>" method="POST" id="radio_form" data-parsley-validate="">
                                        <?php if (isset($rStation["id"])) { ?>
                                            <input type="hidden" name="edit" value="<?= $rStation["id"] ?>" />
                                        <?php } ?>
                                        <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#stream-details" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#advanced-options" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["advanced"] ?></span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#auto-restart" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-clock-outline mr-1"></i>
                                                        <span
                                                            class="d-none d-sm-inline"><?= $_["auto_restart"] ?></span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#load-balancing" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-server-network mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["servers"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="stream-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="stream_display_name"><?= $_["station_name"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        id="stream_display_name"
                                                                        name="stream_display_name" value="<?php if (isset($rStation)) {
                                                                            echo htmlspecialchars($rStation["stream_display_name"]);
                                                                                                          } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4 stream-url">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="stream_source"><?= $_["station_url"] ?></label>
                                                                <div class="col-md-8 input-group">
                                                                    <input type="text" id="stream_source"
                                                                        name="stream_source[]" class="form-control"
                                                                        value="<?php if (isset($rStation)) {
                                                                            echo htmlspecialchars(json_decode($rStation["stream_source"], true)[0]);
                                                                               } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="category_id"><?= $_["category_name"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="category_id" id="category_id"
                                                                        class="form-control" data-toggle="select2">
                                                                        <?php foreach (getCategories_admin("radio") as $rCategory) { ?>
                                                                            <option <?php if (isset($rStation)) {
                                                                                if (intval($rStation["category_id"]) == intval($rCategory["id"])) {
                                                                                    echo "selected ";
                                                                                }
                                                                                    } elseif ((isset(ipTV_lib::$request["category"])) && (ipTV_lib::$request["category"] == $rCategory["id"])) {
                                                                                        echo "selected ";
                                                                                    } ?>value="<?= $rCategory["id"] ?>">
                                                                                <?= $rCategory["category_name"] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="bouquets"><?= $_["add_to_bouquets"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="bouquets[]" id="bouquets"
                                                                        class="form-control select2-multiple"
                                                                        data-toggle="select2" multiple="multiple"
                                                                        data-placeholder="<?= $_["choose"] ?>...">
                                                                        <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                            <option <?php if (isset($rStation)) {
                                                                                if (in_array($rStation["id"], json_decode($rBouquet["bouquet_radios"], true))) {
                                                                                    echo "selected ";
                                                                                }
                                                                                    } ?>value="<?= $rBouquet["id"] ?>">
                                                                                <?= htmlspecialchars($rBouquet["bouquet_name"]) ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="stream_icon"><?= $_["station_logo_url"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        id="stream_icon" name="stream_icon" value="<?php if (isset($rStation)) {
                                                                            echo htmlspecialchars($rStation["stream_icon"]);
                                                                                                                   } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="notes"><?= $_["notes"] ?></label>
                                                                <div class="col-md-8">
                                                                    <textarea id="notes" name="notes"
                                                                        class="form-control" rows="3" placeholder=""><?php if (isset($rStation)) {
                                                                            echo htmlspecialchars($rStation["notes"]);
                                                                                                                     } ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="next list-inline-item float-right">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["next"] ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-pane" id="advanced-options">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="direct_source"><?= $_["direct_source"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="Don't run source through Xtream Codes, just redirect instead."
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input name="direct_source" id="direct_source"
                                                                        type="checkbox" <?php if (isset($rStation)) {
                                                                            if ($rStation["direct_source"] == 1) {
                                                                                echo "checked ";
                                                                            }
                                                                                        } ?>data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-4 col-form-label"
                                                                    for="custom_sid"><?= $_["custom_channel_sid"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="Here you can specify the SID of the channel in order to work with the epg on the enigma2 devices. You have to specify the code with the ':' but without the first number, 1 or 4097 . Example: if we have this code:  '1:0:1:13f:157c:13e:820000:0:0:0:2097' then you have to add on this field:  ':0:1:13f:157c:13e:820000:0:0:0:"
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control"
                                                                        id="custom_sid" name="custom_sid" value="<?php if (isset($rStation)) {
                                                                            echo htmlspecialchars($rStation["custom_sid"]);
                                                                                                                 } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="custom_ffmpeg"><?= $_["custom_ffmpeg_command"] ?>
                                                                    <i data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="In this field you can write your own custom FFmpeg command. Please note that this command will be placed after the input and before the output. If the command you will specify here is about to do changes in the output video or audio, it may require to transcode the stream. In this case, you have to use and change at least the Video/Audio Codecs using the transcoding attributes below. The custom FFmpeg command will only be used by the server(s) that take the stream from the Source."
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control"
                                                                        id="custom_ffmpeg" name="custom_ffmpeg" value="<?php if (isset($rStation)) {
                                                                            echo htmlspecialchars($rStation["custom_ffmpeg"]);
                                                                                                                       } ?>">
                                                                </div>
                                                                <label class="col-md-4 col-form-label"
                                                                    for="probesize_ondemand"><?= $_["on_demand_probesize"] ?>
                                                                    <i data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="Adjustable probesize for ondemand streams. Adjust this setting if you experience issues with no audio."
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control"
                                                                        id="probesize_ondemand"
                                                                        name="probesize_ondemand" value="<?php if (isset($rStation)) {
                                                                            echo htmlspecialchars($rStation["probesize_ondemand"]);
                                                                                                         } else {
                                                                                                             echo "128000";
                                                                                                         } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="user_agent"><?= $_["user_agent"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        id="user_agent" name="user_agent" value="<?php if (isset($rStationOptions[1])) {
                                                                            echo htmlspecialchars($rStationOptions[1]["value"]);
                                                                                                                 } else {
                                                                                                                     echo htmlspecialchars($rStationArguments["user_agent"]["argument_default_value"]);
                                                                                                                 } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="http_proxy"><?= $_["http_proxy"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title="" data-original-title="Format: ip:port"
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        id="http_proxy" name="http_proxy" value="<?php if (isset($rStationOptions[2])) {
                                                                            echo htmlspecialchars($rStationOptions[2]["value"]);
                                                                                                                 } else {
                                                                                                                     echo htmlspecialchars($rStationArguments["proxy"]["argument_default_value"]);
                                                                                                                 } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="cookie"><?= $_["cookie"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="Format: key=value;"
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="cookie"
                                                                        name="cookie" value="<?php if (isset($rStationOptions[17])) {
                                                                            echo htmlspecialchars($rStationOptions[17]["value"]);
                                                                                             } else {
                                                                                                 echo htmlspecialchars($rStationArguments["cookie"]["argument_default_value"]);
                                                                                             } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="headers"><?= $_["headers"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="FFmpeg -headers command."
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="headers"
                                                                        name="headers" value="<?php if (isset($rStreamOptions[19])) {
                                                                            echo htmlspecialchars($rStreamOptions[19]["value"]);
                                                                                              } else {
                                                                                                  echo htmlspecialchars($rStreamArguments["headers"]["argument_default_value"]);
                                                                                              } ?>">
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["prev"] ?></a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["next"] ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-pane" id="auto-restart">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="days_to_restart"><?= $_["days_to_restart"] ?></label>
                                                                <div class="col-md-8">
                                                                    <?php
                                                                    $rAutoRestart = array("days" => array(), "at" => "06:00");
                                                                    if (isset($rStation)) {
                                                                        if (strlen($rStation["auto_restart"])) {
                                                                            $rAutoRestart = json_decode($rStation["auto_restart"], true);
                                                                            if (!isset($rAutoRestart["days"])) {
                                                                                $rAutoRestart["days"] = array();
                                                                            }
                                                                            if (!isset($rAutoRestart["at"])) {
                                                                                $rAutoRestart["at"] = "06:00";
                                                                            }
                                                                        }
                                                                    } ?>
                                                                    <select id="days_to_restart"
                                                                        name="days_to_restart[]"
                                                                        class="form-control select2-multiple"
                                                                        data-toggle="select2" multiple="multiple"
                                                                        data-placeholder="<?= $_["choose"] ?>...">
                                                                        <?php foreach (array($_["monday"] => "Monday", $_["tuesday"] => "Tuesday", $_["wednesday"] => "Wednesday", $_["thursday"] => "Thursday", $_["friday"] => "Friday", $_["saturday"] => "Saturday", $_["sunday"] => "Sunday") as $rDay) { ?>
                                                                            <option value="<?= $rDay ?>" <?php if (in_array($rDay, $rAutoRestart["days"])) {
                                                                                  echo " selected";
                                                                                           } ?>><?= $rDay ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="time_to_restart"><?= $_["time_to_restart"] ?></label>
                                                                <div class="col-md-8">
                                                                    <div class="input-group clockpicker"
                                                                        data-placement="top" data-align="top"
                                                                        data-autoclose="true">
                                                                        <input id="time_to_restart"
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
                                                                class="btn btn-secondary"><?= $_["prev"] ?></a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["next"] ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-pane" id="load-balancing">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="servers"><?= $_["server_tree"] ?></label>
                                                                <div class="col-md-8">
                                                                    <div id="server_tree"></div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="on_demand"><?= $_["on_demand"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select id="on_demand" name="on_demand[]"
                                                                        class="form-control select2-multiple"
                                                                        data-toggle="select2" multiple="multiple"
                                                                        data-placeholder="<?= $_["choose"] ?>...">
                                                                        <?php foreach ($rServers as $rServerItem) { ?>
                                                                            <option value="<?= $rServerItem["id"] ?>" <?php if (in_array($rServerItem["id"], $rOnDemand)) {
                                                                                  echo " selected";
                                                                                           } ?>>
                                                                                <?= $rServerItem["server_name"] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="restart_on_edit"><?php if (isset($rStation["id"])) {
                                                                        ?><?= $_["restart_on_edit"] ?><?php
                                                                                          } else {
                                                                                                ?><?= $_["start_stream_now"] ?><?php
                                                                                          } ?></label>
                                                                <div class="col-md-2">
                                                                    <input name="restart_on_edit" id="restart_on_edit"
                                                                        type="checkbox" data-plugin="switchery"
                                                                        class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["prev"] ?></a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <input name="submit_radio" type="submit"
                                                                class="btn btn-primary" value="<?php if (isset($rStation["id"])) {
                                                                    echo $_["edit"];
                                                                                               } else {
                                                                                                   echo $_["add"];
                                                                                               } ?>" />
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
            <!-- file preview template -->
            <div class="d-none" id="uploadPreviewTemplate">
                <div class="card mt-1 mb-0 shadow-none border">
                    <div class="p-2">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img data-dz-thumbnail class="avatar-sm rounded bg-light" alt="">
                            </div>
                            <div class="col pl-0">
                                <a href="javascript:void(0);" class="text-muted font-weight-bold" data-dz-name></a>
                                <p class="mb-0" data-dz-size></p>
                            </div>
                            <div class="col-auto">
                                <!-- Button -->
                                <a href="" class="btn btn-link btn-lg text-muted" data-dz-remove>
                                    <i class="mdi mdi-close-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
            <script src="assets/libs/treeview/jstree.min.js"></script>
            <script src="assets/js/pages/treeview.init.js"></script>
            <script src="assets/js/pages/form-wizard.init.js"></script>
            <script src="assets/libs/parsleyjs/parsley.min.js"></script>
            <script src="assets/js/app.min.js"></script>

            <script>
                var rSwitches = [];

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

                function reloadStream() {
                    $("#datatable").DataTable().ajax.reload(null, false);
                    setTimeout(reloadStream, 5000);
                }

                function api(rID, rServerID, rType) {
                    if (rType == "delete") {
                        if (confirm('<?= $_["radio_delete_confirm"] ?>') == false) {
                            return;
                        }
                    }
                    $.getJSON("./api.php?action=stream&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function (data) {
                        if (data.result == true) {
                            if (rType == "start") {
                                $.toast("<?= $_["radio_started"] ?>");
                            } else if (rType == "stop") {
                                $.toast("<?= $_["radio_stopped"] ?>");
                            } else if (rType == "restart") {
                                $.toast("<?= $_["radio_restarted"] ?>");
                            } else if (rType == "delete") {
                                $("#stream-" + rID + "-" + rServerID).remove();
                                $.toast("<?= $_["radio_deleted"] ?>");
                            }
                            $("#datatable").DataTable().ajax.reload(null, false);
                        } else {
                            $.toast("<?= $_["error_occured"] ?>");
                        }
                    }).fail(function () {
                        $.toast("<?= $_["error_occured"] ?>");
                    });
                }

                function setSwitch(switchElement, checkedBool) {
                    if ((checkedBool && !switchElement.isChecked()) || (!checkedBool && switchElement.isChecked())) {
                        switchElement.setPosition(true);
                        switchElement.handleOnchange(true);
                    }
                }
                $(document).ready(function () {
                    $('select').select2({
                        width: '100%'
                    })
                    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                    elems.forEach(function (html) {
                        var switchery = new Switchery(html);
                        window.rSwitches[$(html).attr("id")] = switchery;
                    });
                    $(".clockpicker").clockpicker();
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
                    $("#direct_source").change(function () {
                        evaluateDirectSource();
                    });

                    function evaluateDirectSource() {
                        $(["custom_ffmpeg", "probesize_ondemand", "user_agent", "http_proxy", "cookie", "headers", "days_to_restart", "time_to_restart", "on_demand", "restart_on_edit"]).each(function (rID, rElement) {
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

                    $("#radio_form").submit(function (e) {
                        if ($("#stream_display_name").val().length == 0) {
                            e.preventDefault();
                            $.toast("<?= $_["enter_a_radio_station_name"] ?>");
                        }
                        $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {
                            flat: true
                        })));
                    });

                    $(document).keypress(function (event) {
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
                    <?php if (isset($rStation["id"])) { ?>
                        $("#datatable").DataTable({
                            ordering: false,
                            paging: false,
                            searching: false,
                            processing: true,
                            serverSide: true,
                            bInfo: false,
                            ajax: {
                                url: "./table_search.php",
                                "data": function (d) {
                                    d.id = "radios";
                                    d.stream_id = <?= $rStation["id"] ?>;
                                }
                            },
                            columnDefs: [{
                                "className": "dt-center",
                                "targets": [2, 3, 4, 5]
                            },
                            {
                                "visible": false,
                                "targets": [0, 1, 6]
                            }
                            ],
                        });
                        setTimeout(reloadStream, 5000);
                    <?php } ?>
                    evaluateDirectSource();
                });
            </script>
            </body>

            </html>