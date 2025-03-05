<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_stream")) && (!hasPermissions("adv", "edit_stream")))) {
    exit;
}
if ((isset(CoreUtilities::$request["import"])) && (!hasPermissions("adv", "import_streams"))) {
    exit;
}

$rEPGSources = getEPGSources();
$rStreamArguments = getStreamArguments();
$rTranscodeProfiles = getTranscodeProfiles();

$rEPGJS = array(0 => array());
foreach ($rEPGSources as $rEPG) {
    $rEPGJS[$rEPG["id"]] = json_decode($rEPG["data"], true);
}

$rServerTree = array();
$rOnDemand = array();
$rServerTree[] = array("id" => "source", "parent" => "#", "text" => "<strong>Stream Source</strong>", "icon" => "mdi mdi-youtube-tv", "state" => array("opened" => true));
if (isset(CoreUtilities::$request["id"])) {
    if ((isset(CoreUtilities::$request["import"])) or (!hasPermissions("adv", "edit_stream"))) {
        exit;
    }
    $rStream = getStream(CoreUtilities::$request["id"]);
    if ((!$rStream) or ($rStream["type"] <> 1)) {
        exit;
    }
    $rStreamOptions = getStreamOptions(CoreUtilities::$request["id"]);
    $rStreamSys = getStreamSys(CoreUtilities::$request["id"]);
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
        $rServerTree[] = array("id" => $rServer["id"], "parent" => $rParent, "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => array("opened" => true));
    }
    foreach ($rStreamSys as $rStreamItem) {
        if ($rStreamItem["on_demand"] == 1) {
            $rOnDemand[] = $rStreamItem["server_id"];
        }
    }
} else {
    if (!hasPermissions("adv", "add_stream")) {
        exit;
    }
    foreach ($rServers as $rServer) {
        $rServerTree[] = array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => array("opened" => true));
    }
}

include "header.php";
?>
<div class="wrapper boxed-layout">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li>
                                <a href="./streams.php<?php if (isset(CoreUtilities::$request["category"])) {
                                                            echo "?category=" . CoreUtilities::$request["category"];
                                                        } ?>">
                                    <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                        <?= $_["permission_streams"] ?>
                                    </button>
                                </a>
                                <?php if (!isset($rStream)) {
                                    if (!isset(CoreUtilities::$request["import"])) { ?>
                                        <a href="./stream.php?import=1">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <?= $_["import_m3u"] ?>
                                            </button>
                                        </a>
                                    <?php } else { ?>
                                        <a href="./stream.php">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <?= $_["add_single"] ?>
                                            </button>
                                        </a>
                                <?php }
                                } ?>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title"><?php if (isset($rStream["id"])) {
                                                echo $rStream["stream_display_name"] . ' &nbsp;<button type="button" class="btn btn-outline-info waves-effect waves-light btn-xs" onClick="player(' . $rStream["id"] . ');"><i class="mdi mdi-play"></i></button>';
                                            } elseif (isset(CoreUtilities::$request["import"])) {
                                                echo $_["import_streams"];
                                            } else {
                                                echo $_["add_stream"];
                                            } ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <?php if ((isset($_STATUS)) && ($_STATUS == STATUS_SUCCESS)) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["stream_operation_was_completed_successfully"] ?>
                    </div>
                <?php } 
                if (isset($rStream["id"])) { ?>
                    <div class="card text-xs-center">
                        <div class="table">
                            <table id="datatable" class="table table-borderless mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th><?= $_["id"] ?></th>
                                        <th><?= $_["icon"] ?></th>
                                        <th><?= $_["stream_name"] ?></th>
                                        <th><?= $_["source"] ?></th>
                                        <th><?= $_["clients"] ?></th>
                                        <th><?= $_["uptime"] ?></th>
                                        <th><?= $_["actions"] ?></th>
                                        <th><?= $_["player"] ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <?= $_["loading_stream_information"] ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <form
                            <?php if (isset(CoreUtilities::$request["import"])): ?>
                            enctype="multipart/form-data"
                            <?php endif; ?>
                            action="#"
                            method="POST"
                            id="stream_form"
                            data-parsley-validate="">
                            <?php if (isset($rStream["id"])): ?>
                                <input type="hidden" name="edit" value="<?= htmlspecialchars($rStream["id"]) ?>" />
                            <?php endif; ?>
                            <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#stream-details" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["details"] ?> </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#advanced-options" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["advanced"] ?> </span>
                                        </a>
                                    </li>
                                    <?php if (!isset(CoreUtilities::$request["import"])) { ?>
                                        <li class="nav-item">
                                            <a href="#stream-map" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                <i class="mdi mdi-map mr-1"></i>
                                                <span class="d-none d-sm-inline"><?= $_["map"] ?> </span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <li class="nav-item">
                                        <a href="#auto-restart" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-clock-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["auto_restart"] ?>
                                            </span>
                                        </a>
                                    </li>
                                    <?php if (!isset(CoreUtilities::$request["import"])) { ?>
                                        <li class="nav-item">
                                            <a href="#epg-options" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                <i class="mdi mdi-television-guide mr-1"></i>
                                                <span class="d-none d-sm-inline"><?= $_["epg"] ?> </span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <li class="nav-item">
                                        <a href="#load-balancing" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-server-network mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["servers"] ?> </span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="stream-details">
                                        <div class="row">
                                            <div class="col-12">
                                                <?php if (!isset(CoreUtilities::$request["import"])) { ?>
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="stream_display_name"><?= $_["stream_name"] ?>
                                                        </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" id="stream_display_name"
                                                                name="stream_display_name" value="<?php if (isset($rStream)) {
                                                                                                        echo htmlspecialchars($rStream["stream_display_name"]);
                                                                                                    } ?>" required data-parsley-trigger="change">
                                                        </div>
                                                    </div>
                                                    <span class="streams">
                                                        <?php
                                                        if (isset($rStream)) {
                                                            $rStreamSources = json_decode($rStream["stream_source"], true);
                                                            if (!$rStreamSources) {
                                                                $rStreamSources = array("");
                                                            }
                                                        } else {
                                                            $rStreamSources = array("");
                                                        }
                                                        $i = 0;
                                                        foreach ($rStreamSources as $rStreamSource) {
                                                            $i++
                                                        ?>
                                                            <div class="form-group row mb-4 stream-url">
                                                                <label class="col-md-4 col-form-label" for="stream_source">
                                                                    <?= $_["stream_url"] ?>
                                                                </label>
                                                                <div class="col-md-8 input-group">
                                                                    <input type="text" id="stream_source" name="stream_source[]"
                                                                        class="form-control"
                                                                        value="<?= htmlspecialchars($rStreamSource) ?>">
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-info waves-effect waves-light"
                                                                            onClick="moveUp(this);" type="button"><i
                                                                                class="mdi mdi-chevron-up"></i></button>
                                                                        <button class="btn btn-info waves-effect waves-light"
                                                                            onClick="moveDown(this);" type="button"><i
                                                                                class="mdi mdi-chevron-down"></i></button>
                                                                        <button class="btn btn-primary waves-effect waves-light"
                                                                            onClick="addStream();" type="button"><i
                                                                                class="mdi mdi-plus"></i></button>
                                                                        <button class="btn btn-danger waves-effect waves-light"
                                                                            onClick="removeStream(this);" type="button"><i
                                                                                class="mdi mdi-close"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </span>
                                                <?php } else { ?>
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="m3u_file"><?= $_["m3u"] ?> </label>
                                                        <div class="col-md-8">
                                                            <input type="file" id="m3u_file" name="m3u_file" />
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="category_id"><?= $_["category_name"] ?>
                                                    </label>
                                                    <div class="col-md-8">
                                                        <select name="category_id[]" id="category_id"
                                                            class="form-control select2-multiple" data-toggle="select2"
                                                            multiple="multiple" data-placeholder="Choose...">
                                                            <?php foreach (getCategories_admin('live') as $rCategory): ?>
                                                                <option <?php if (isset($rStream) && in_array(intval($rCategory['id']), json_decode($rStream['category_id'], true))) {
                                                                            echo 'selected ';
                                                                        } ?>value="<?php echo $rCategory['id']; ?>">
                                                                    <?php echo $rCategory['category_name']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="bouquets"><?= $_["add_to_bouquets"] ?> </label>
                                                    <div class="col-md-8">
                                                        <select name="bouquets[]" id="bouquets"
                                                            class="form-control select2-multiple" data-toggle="select2"
                                                            multiple="multiple" data-placeholder="<?= $_["choose"] ?>">
                                                            <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                <option <?php if (isset($rStream)) {
                                                                            if (in_array($rStream["id"], json_decode($rBouquet["bouquet_channels"], true))) {
                                                                                echo "selected ";
                                                                            }
                                                                        } ?>value="<?= $rBouquet["id"] ?>">
                                                                    <?= $rBouquet["bouquet_name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php if (!isset(CoreUtilities::$request["import"])) { ?>
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="stream_icon"><?= $_["stream_logo_url"] ?>
                                                        </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" id="stream_icon"
                                                                name="stream_icon" value="<?php if (isset($rStream)) {
                                                                                                echo htmlspecialchars($rStream["stream_icon"]);
                                                                                            } ?>">
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="notes"><?= $_["notes"] ?> </label>
                                                    <div class="col-md-8">
                                                        <textarea id="notes" name="notes" class="form-control" rows="3"
                                                            placeholder=""><?php if (isset($rStream)) {
                                                                                echo htmlspecialchars($rStream["notes"]);
                                                                            } ?></textarea>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="next list-inline-item float-right">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["next"] ?> </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" id="advanced-options">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="gen_timestamps"><?= $_["generate_pts"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="Allow FFmpeg to generate presentation timestamps for you to achieve better synchronization with the stream codecs. In some streams this can cause de-sync."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="gen_timestamps" id="gen_timestamps" type="checkbox"
                                                            <?php if (isset($rStream)) {
                                                                if ($rStream["gen_timestamps"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } else {
                                                                echo "checked ";
                                                            } ?>data-plugin="switchery"
                                                            class="js-switch" data-color="#039cfd" />
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="read_native"><?= $_["native_frames"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="You should always read live streams as non-native frames. However if you are streaming static video files, set this to true otherwise the encoding process will fail."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="read_native" id="read_native" type="checkbox" <?php if (isset($rStream)) {
                                                                                                                        if ($rStream["read_native"] == 1) {
                                                                                                                            echo "checked ";
                                                                                                                        }
                                                                                                                    } ?>data-plugin="switchery"
                                                            class="js-switch" data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="stream_all"><?= $_["stream_all_codecs"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="This option will stream all codecs from your stream. Some streams have more than one audio/video/subtitles channels."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="stream_all" id="stream_all" type="checkbox" <?php if (isset($rStream)) {
                                                                                                                        if ($rStream["stream_all"] == 1) {
                                                                                                                            echo "checked ";
                                                                                                                        }
                                                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="allow_record"><?= $_["allow_recording"] ?>
                                                    </label>
                                                    <div class="col-md-2">
                                                        <input name="allow_record" id="allow_record" type="checkbox"
                                                            <?php if (isset($rStream)) {
                                                                if ($rStream["allow_record"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } else {
                                                                echo "checked ";
                                                            } ?>data-plugin="switchery"
                                                            class="js-switch" data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="rtmp_output"><?= $_["allow_rtmp_output"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="Enable RTMP output for this channel."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="rtmp_output" id="rtmp_output" type="checkbox" <?php if (isset($rStream)) {
                                                                                                                        if ($rStream["rtmp_output"] == 1) {
                                                                                                                            echo "checked ";
                                                                                                                        }
                                                                                                                    } ?>data-plugin="switchery"
                                                            class="js-switch" data-color="#039cfd" />
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="direct_source"><?= $_["direct_source"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="Don't run source through Xtream Codes, just redirect instead."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="direct_source" id="direct_source" type="checkbox"
                                                            <?php if (isset($rStream)) {
                                                                if ($rStream["direct_source"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="custom_sid"><?= $_["custom_channel_sid"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="Here you can specify the SID of the channel in order to work with the epg on the enigma2 devices. You have to specify the code with the ':' but without the first number, 1 or 4097 . Example: if we have this code:  '1:0:1:13f:157c:13e:820000:0:0:0:2097' then you have to add on this field:  ':0:1:13f:157c:13e:820000:0:0:0:"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="custom_sid"
                                                            name="custom_sid" value="<?php if (isset($rStream)) {
                                                                                            echo htmlspecialchars($rStream["custom_sid"]);
                                                                                        } ?>">
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="delay_minutes"><?= $_["minute_delay"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="Delay stream by X minutes. Will not work with on demand streams."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="delay_minutes"
                                                            name="delay_minutes" value="<?php if (isset($rStream)) {
                                                                                            echo $rStream["delay_minutes"];
                                                                                        } else {
                                                                                            echo "0";
                                                                                        } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="custom_ffmpeg"><?= $_["custom_ffmpeg_command"] ?>
                                                        <i data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="In this field you can write your own custom FFmpeg command. Please note that this command will be placed after the input and before the output. If the command you will specify here is about to do changes in the output video or audio, it may require to transcode the stream. In this case, you have to use and change at least the Video/Audio Codecs using the transcoding attributes below. The custom FFmpeg command will only be used by the server(s) that take the stream from the Source."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="custom_ffmpeg"
                                                            name="custom_ffmpeg" value="<?php if (isset($rStream)) {
                                                                                            echo htmlspecialchars($rStream["custom_ffmpeg"]);
                                                                                        } ?>">
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="probesize_ondemand"><?= $_["on_demand_probesize"] ?>
                                                        <i data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="Adjustable probesize for ondemand streams. Adjust this setting if you experience issues with no audio."
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="probesize_ondemand"
                                                            name="probesize_ondemand" value="<?php if (isset($rStream)) {
                                                                                                    echo $rStream["probesize_ondemand"];
                                                                                                } else {
                                                                                                    echo "128000";
                                                                                                } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="user_agent"><?= $_["user_agent"] ?> </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="user_agent"
                                                            name="user_agent" value="<?php if (isset($rStreamOptions[1])) {
                                                                                            echo htmlspecialchars($rStreamOptions[1]["value"]);
                                                                                        } else {
                                                                                            echo htmlspecialchars($rStreamArguments["user_agent"]["argument_default_value"]);
                                                                                        } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="http_proxy"><?= $_["http_proxy"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="Format: ip:port"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="http_proxy"
                                                            name="http_proxy" value="<?php if (isset($rStreamOptions[2])) {
                                                                                            echo htmlspecialchars($rStreamOptions[2]["value"]);
                                                                                        } else {
                                                                                            echo htmlspecialchars($rStreamArguments["proxy"]["argument_default_value"]);
                                                                                        } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="cookie"><?= $_["cookie"] ?> <i data-toggle="tooltip"
                                                            data-placement="top" title=""
                                                            data-original-title="Format: key=value;"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="cookie"
                                                            name="cookie" value="<?php if (isset($rStreamOptions[17])) {
                                                                                        echo htmlspecialchars($rStreamOptions[17]["value"]);
                                                                                    } else {
                                                                                        echo htmlspecialchars($rStreamArguments["cookie"]["argument_default_value"]);
                                                                                    } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="headers"><?= $_["headers"] ?> <i data-toggle="tooltip"
                                                            data-placement="top" title=""
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
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="transcode_profile_id"><?= $_["transcoding_profile"] ?>
                                                        <i data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="<?= $_["episode_tooltip_7"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-8">
                                                        <select name="transcode_profile_id" id="transcode_profile_id"
                                                            class="form-control" data-toggle="select2">
                                                            <option <?php if (isset($rStream)) {
                                                                        if (intval($rStream["transcode_profile_id"]) == 0) {
                                                                            echo "selected ";
                                                                        }
                                                                    } ?>value="0">
                                                                <?= $_["transcoding_disabled"] ?>
                                                            </option>
                                                            <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                <option <?php if (isset($rStream)) {
                                                                            if (intval($rStream["transcode_profile_id"]) == intval($rProfile["profile_id"])) {
                                                                                echo "selected ";
                                                                            }
                                                                        } ?>value="<?= $rProfile["profile_id"] ?>">
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
                                    <?php if (!isset(CoreUtilities::$request["import"])) { ?>
                                        <div class="tab-pane" id="stream-map">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-3 col-form-label"
                                                            for="custom_map"><?= $_["custom_map"] ?> </label>
                                                        <div class="col-md-9 input-group">
                                                            <input type="text" class="form-control" id="custom_map"
                                                                name="custom_map" value="<?php if (isset($rStream)) {
                                                                                                echo htmlspecialchars($rStream["custom_map"]);
                                                                                            } ?>">
                                                            <div class="input-group-append">
                                                                <button class="btn btn-primary waves-effect waves-light"
                                                                    id="load_maps" type="button"><i
                                                                        class="mdi mdi-magnify"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-warning bg-warning text-white border-0"
                                                        role="alert">
                                                        <?= $_["custom_maps_are_advanced"] ?>
                                                    </div>
                                                    <table id="datatable-map" class="table table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th><?= $_["type"] ?> </th>
                                                                <th><?= $_["information"] ?> </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
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
                                    <?php } ?>
                                    <div class="tab-pane" id="auto-restart">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="days_to_restart"><?= $_["days_to_restart"] ?>
                                                    </label>
                                                    <div class="col-md-8">
                                                        <?php
                                                        $rAutoRestart = array("days" => array(), "at" => "06:00");
                                                        if (isset($rStream)) {
                                                            if (strlen($rStream["auto_restart"])) {
                                                                $rAutoRestart = json_decode($rStream["auto_restart"], true);
                                                                if (!isset($rAutoRestart["days"])) {
                                                                    $rAutoRestart["days"] = array();
                                                                }
                                                                if (!isset($rAutoRestart["at"])) {
                                                                    $rAutoRestart["at"] = "06:00";
                                                                }
                                                            }
                                                        } ?>
                                                        <select id="days_to_restart" name="days_to_restart[]"
                                                            class="form-control select2-multiple" data-toggle="select2"
                                                            multiple="multiple" data-placeholder="<?= $_["choose_"] ?>">
                                                            <?php foreach (array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday") as $rDay) { ?>
                                                                <option value="<?= $rDay ?>" <?php if (in_array($rDay, $rAutoRestart["days"])) {
                                                                                                    echo " selected";
                                                                                                } ?>>
                                                                    <?= $rDay ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="time_to_restart"><?= $_["time_to_restart"] ?>
                                                    </label>
                                                    <div class="col-md-8">
                                                        <div class="input-group clockpicker" data-placement="top"
                                                            data-align="top" data-autoclose="true">
                                                            <input id="time_to_restart" name="time_to_restart"
                                                                type="text" class="form-control"
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
                                    <?php if (!isset(CoreUtilities::$request["import"])) { ?>
                                        <div class="tab-pane" id="epg-options">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="epg_id"><?= $_["epg_source"] ?> </label>
                                                        <div class="col-md-8">
                                                            <select name="epg_id" id="epg_id" class="form-control"
                                                                data-toggle="select2">
                                                                <option <?php if (isset($rStream)) {
                                                                            if (intval($rStream["epg_id"]) == 0) {
                                                                                echo "selected ";
                                                                            }
                                                                        } ?>value="0">
                                                                    <?= $_["no_epg"] ?>
                                                                </option>
                                                                <?php foreach ($rEPGSources as $rEPG) { ?>
                                                                    <option <?php if (isset($rStream)) {
                                                                                if (intval($rStream["epg_id"]) == $rEPG["id"]) {
                                                                                    echo "selected ";
                                                                                }
                                                                            } ?>value="<?= $rEPG["id"] ?>">
                                                                        <?= $rEPG["epg_name"] ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="channel_id"><?= $_["epg_channel_id"] ?>
                                                        </label>
                                                        <div class="col-md-8">
                                                            <select name="channel_id" id="channel_id" class="form-control"
                                                                data-toggle="select2">
                                                                <?php if (isset($rStream)) {
                                                                    foreach ((array) json_decode($rEPGSources[intval($rStream["epg_id"])]["data"], true) as $rKey => $rEPGChannel) { ?>
                                                                        <option value="<?= $rKey ?>" <?php if ($rStream["channel_id"] == $rKey) {
                                                                                                            echo " selected";
                                                                                                        } ?>>
                                                                            <?= $rEPGChannel["display_name"] ?>
                                                                        </option>
                                                                <?php }
                                                                } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="epg_lang"><?= $_["epg_language"] ?> </label>
                                                        <div class="col-md-8">
                                                            <select name="epg_lang" id="epg_lang" class="form-control"
                                                                data-toggle="select2">
                                                                <?php if (isset($rStream)) {
                                                                    foreach ((array) json_decode($rEPGSources[intval($rStream["epg_id"])]["data"], true)[$rStream["channel_id"]]["langs"] as $rID => $rLang) { ?>
                                                                        <option value="<?= $rLang ?>" <?php if ($rStream["epg_lang"] == $rLang) {
                                                                                                            echo " selected";
                                                                                                        } ?>>
                                                                            <?= $rLang ?>
                                                                        </option>
                                                                <?php }
                                                                } ?>
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
                                    <?php } ?>
                                    <div class="tab-pane" id="load-balancing">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="servers"><?= $_["server_tree"] ?> </label>
                                                    <div class="col-md-8">
                                                        <div id="server_tree"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="on_demand"><?= $_["on_demand"] ?> </label>
                                                    <div class="col-md-8">
                                                        <select id="on_demand" name="on_demand[]"
                                                            class="form-control select2-multiple" data-toggle="select2"
                                                            multiple="multiple"
                                                            data-placeholder="<?= $_["bouquet_order"] ?>Choose ...">
                                                            <?php foreach ($rServers as $rServerItem) { ?>
                                                                <option value="<?= $rServerItem["id"] ?>" <?php if (in_array($rServerItem["id"], $rOnDemand)) {
                                                                                                                echo " selected";
                                                                                                            } ?>>
                                                                    <?= $rServerItem["server_name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="tv_archive_server_id"><?= $_["timeshift_server"] ?>
                                                    </label>
                                                    <div class="col-md-8">
                                                        <select name="tv_archive_server_id" id="tv_archive_server_id"
                                                            class="form-control" data-toggle="select2">
                                                            <option value="0">
                                                                <?= $_["timeshift_disabled"] ?>
                                                            </option>
                                                            <?php foreach ($rServers as $rServer) { ?>
                                                                <option value="<?= $rServer["id"] ?>" <?php if ((isset($rStream)) && ($rStream["tv_archive_server_id"] == $rServer["id"])) {
                                                                                                            echo " selected";
                                                                                                        } ?>>
                                                                    <?= $rServer["server_name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="tv_archive_duration"><?= $_["timeshift_days"] ?>
                                                    </label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="tv_archive_duration"
                                                            name="tv_archive_duration" value="<?php if (isset($rStream)) {
                                                                                                    echo $rStream["tv_archive_duration"];
                                                                                                } else {
                                                                                                    echo "0";
                                                                                                } ?>">
                                                        </select>
                                                    </div>
                                                    <label class="col-md-4 col-form-label" for="restart_on_edit"><?php if (isset($rStream["id"])) {
                                                                                                                    ?><?= $_["restart_on_edit"] ?>
                                                    <?php } else {
                                                    ?>
                                                        <?= $_["start_stream_now"] ?>
                                                    <?php } ?></label>
                                                    <div class="col-md-2">
                                                        <input name="restart_on_edit" id="restart_on_edit"
                                                            type="checkbox" data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
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
                                            <li class="list-inline-item float-right"><input name="submit_stream" type="submit" class="btn btn-primary" value="Save" /></li>
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
<?php include 'post.php'; ?>

<script>
    var rEPG = <?= json_encode($rEPGJS) ?>;
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

    function moveUp(elem) {
        if ($(elem).parent().parent().parent().prevAll().length > 0) {
            $(elem).parent().parent().parent().insertBefore($('.streams>div').eq($(elem).parent().parent().parent().prevAll().length - 1));
        }
    }

    function moveDown(elem) {
        if ($(elem).parent().parent().parent().prevAll().length < $(".streams>div").length) {
            $(elem).parent().parent().parent().insertAfter($('.streams>div').eq($(elem).parent().parent().parent().prevAll().length + 1));
        }
    }

    function addStream() {
        $(".stream-url:first").clone().appendTo(".streams");
        $(".stream-url:last label").html("Stream URL");
        $(".stream-url:last input").val("");
    }

    function removeStream(rField) {
        if ($('.stream-url').length > 1) {
            $(rField).parent().parent().parent().remove();
        } else {
            $(rField).parent().parent().find("#stream_source").val("");
        }
    }

    function selectEPGSource() {
        $("#channel_id").empty();
        $("#epg_lang").empty();
        if (rEPG[$("#epg_id").val()]) {
            $.each(rEPG[$("#epg_id").val()], function(key, data) {
                $("#channel_id").append(new Option(data["display_name"], key, false, false));
            });
            selectEPGID();
        }
    }

    function selectEPGID() {
        $("#epg_lang").empty();
        if (rEPG[$("#epg_id").val()][$("#channel_id").val()]) {
            $.each(rEPG[$("#epg_id").val()][$("#channel_id").val()]["langs"], function(i, data) {
                $("#epg_lang").append(new Option(data, data, false, false));
            });
        }
    }

    function reloadStream() {
        $("#datatable").DataTable().ajax.reload(null, false);
        setTimeout(reloadStream, 5000);
    }

    function api(rID, rServerID, rType) {
        if (rType == "delete") {
            if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_stream"] ?>') == false) {
                return;
            }
        }
        $.getJSON("./api.php?action=stream&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
            if (data.result == true) {
                if (rType == "start") {
                    $.toast("<?= $_["stream_successfully_started"] ?>");
                } else if (rType == "stop") {
                    $.toast("<?= $_["stream_successfully_stopped"] ?>");
                } else if (rType == "restart") {
                    $.toast("<?= $_["stream_successfully_restarted"] ?>");
                } else if (rType == "delete") {
                    $("#stream-" + rID + "-" + rServerID).remove();
                    $.toast("<?= $_["stream_successfully_deleted"] ?>");
                }
                $("#datatable").DataTable().ajax.reload(null, false);
            } else {
                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
            }
        }).fail(function() {
            $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
        });
    }

    function player(rID) {
        $.magnificPopup.open({
            items: {
                src: "./player.php?type=live&id=" + rID,
                type: 'iframe'
            }
        });
    }

    function setSwitch(switchElement, checkedBool) {
        if ((checkedBool && !switchElement.isChecked()) || (!checkedBool && switchElement.isChecked())) {
            switchElement.setPosition(true);
            switchElement.handleOnchange(true);
        }
    }
    $(document).ready(function() {
        $('select').select2({
            width: '100%'
        });
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        elems.forEach(function(html) {
            var switchery = new Switchery(html);
            window.rSwitches[$(html).attr("id")] = switchery;
        });
        $("#epg_id").on("select2:select", function(e) {
            selectEPGSource();
        });
        $("#channel_id").on("select2:select", function(e) {
            selectEPGID();
        });

        $(".clockpicker").clockpicker();

        $('#server_tree').jstree({
            'core': {
                'check_callback': function(op, node, parent, position, more) {
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

        $("#direct_source").change(function() {
            evaluateDirectSource();
        });

        function evaluateDirectSource() {
            $(["read_native", "gen_timestamps", "stream_all", "allow_record", "rtmp_output", "delay_minutes", "custom_ffmpeg", "probesize_ondemand", "user_agent", "http_proxy", "cookie", "headers", "transcode_profile_id", "custom_map", "days_to_restart", "time_to_restart", "epg_id", "epg_lang", "channel_id", "on_demand", "tv_archive_duration", "tv_archive_server_id", "restart_on_edit"]).each(function(rID, rElement) {
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
        $("#load_maps").click(function() {
            rURL = $("#stream_source:eq(0)").val();
            if (rURL.length > 0) {
                $.toast("<?= $_["stream_map_has_started"] ?>");
                $("#datatable-map").DataTable().clear().draw();
                $.getJSON("./api.php?action=map_stream&stream=" + encodeURIComponent(rURL), function(data) {
                    $(data.streams).each(function(id, array) {
                        if (array.codec_type == "video") {
                            rString = array.codec_name.toUpperCase();
                            if (array.profile) {
                                rString += " (" + array.profile + ")";
                            }
                            if (array.pix_fmt) {
                                rString += " - " + array.pix_fmt;
                            }
                            if ((array.width) && (array.height)) {
                                rString += " - " + array.width + "x" + array.height;
                            }
                            if ((array.avg_frame_rate) && (array.avg_frame_rate.split("/")[0] > 0)) {
                                rString += " - " + array.avg_frame_rate.split("/")[0] + " fps";
                            }
                            $("#datatable-map").DataTable().row.add([array.index, "Video", rString]);
                        } else if (array.codec_type == "audio") {
                            rString = array.codec_name.toUpperCase();
                            if ((array.sample_rate) && (array.sample_rate > 0)) {
                                rString += " - " + array.sample_rate + " Hz";
                            }
                            if (array.channel_layout) {
                                rString += " - " + array.channel_layout;
                            }
                            if (array.sample_fmt) {
                                rString += " - " + array.sample_fmt;
                            }
                            if (array.bit_rate) {
                                rString += " - " + Math.ceil(array.bit_rate / 1000) + " kb/s";
                            }
                            if (array.disposition.visual_impaired) {
                                rString += " - Visual Impaired";
                            }
                            if (array.disposition.hearing_impaired) {
                                rString += " - Hearing Impaired";
                            }
                            if (array.disposition.dub) {
                                rString += " - Dub";
                            }
                            $("#datatable-map").DataTable().row.add([array.index, "Audio", rString]);
                        } else if ((array.codec_type == "audio") && (array.tags.language)) {
                            rString = array.codec_name.toUpperCase();
                            if (array.tags.language) {
                                rString += " - " + array.tags.language.toUpperCase();
                            }
                            if ((array.sample_rate) && (array.sample_rate > 0)) {
                                rString += " - " + array.sample_rate + " Hz";
                            }
                            if (array.channel_layout) {
                                rString += " - " + array.channel_layout;
                            }
                            if (array.sample_fmt) {
                                rString += " - " + array.sample_fmt;
                            }
                            if ((array.bit_rate) || (array.tags.variant_bitrate)) {
                                if (array.bit_rate) {
                                    rString += " - " + Math.ceil(array.bit_rate / 1000) + " kb/s";
                                } else {
                                    rString += " - " + Math.ceil(array.tags.variant_bitrate / 1000) + " vbr";
                                }
                            }
                            if (array.disposition.visual_impaired) {
                                rString += " - Visual Impaired";
                            }
                            if (array.disposition.hearing_impaired) {
                                rString += " - Hearing Impaired";
                            }
                            if (array.disposition.dub) {
                                rString += " - Dub";
                            }
                            $("#datatable-map").DataTable().row.add([array.index, "Audio", rString]);
                        } else if (array.codec_type == "subtitle") {
                            rString = array.codec_long_name.toUpperCase();
                            if (array.tags.language) {
                                rString += " - " + array.tags.language.toUpperCase();
                            }
                            $("#datatable-map").DataTable().row.add([array.index, "Subtitle", rString]);
                        } else {
                            rString = array.codec_long_name.toUpperCase();
                            if (array.tags.variant_bitrate) {
                                rString += " - " + Math.ceil(array.tags.variant_bitrate / 1000) + " vbr";
                            }
                            $("#datatable-map").DataTable().row.add([array.index, "Data", rString]);
                        }
                    });
                    $("#datatable-map").DataTable().draw();
                    if (data.streams.length > 0) {
                        $.toast("<?= $_["stream_map_complete"] ?>");
                    } else {
                        $.toast("<?= $_["stream_mapping"] ?>");
                    }
                }).fail(function() {
                    $.toast("<?= $_["an_error_occured_while_mapping_streams"] ?>");
                });
            }
        });

        $("#stream_form").submit(function(e) {
            <?php if (!isset(CoreUtilities::$request["import"])) { ?>
                if ($("#stream_display_name").val().length == 0) {
                    e.preventDefault();
                    $.toast("Enter a stream name.");
                }
            <?php } else { ?>
                if ($("#m3u_file").val().length == 0) {
                    e.preventDefault();
                    $.toast("<?= $_["please_select_a_m3u"] ?>");
                }
            <?php } ?>
            $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {
                flat: true
            })));
        });

        $(window).keypress(function(event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });

        $("#probesize_ondemand").inputFilter(function(value) {
            return /^\d*$/.test(value);
        });
        $("#delay_minutes").inputFilter(function(value) {
            return /^\d*$/.test(value);
        });
        $("#tv_archive_duration").inputFilter(function(value) {
            return /^\d*$/.test(value);
        });
        $("form").attr('autocomplete', 'off');
        $("form").submit(function(e) {
            e.preventDefault();
            $(':input[type="submit"]').prop('disabled', true);
            submitForm(window.rCurrentPage, new FormData($("form")[0]));
        });
        <?php if (isset($rStream["id"])) { ?>
            $("#datatable").DataTable({
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
                        d.stream_id = <?= $rStream["id"] ?>;
                    }
                },
                columnDefs: [{
                        "className": "dt-center",
                        "targets": [0, 1, 2, 3, 4, 5, 6, 7]
                    },
                    {
                        "visible": false,
                        "targets": []
                    }
                ],
            });
            setTimeout(reloadStream, 5000);
        <?php } ?>
        $("#datatable-map").DataTable({
            paging: false,
            searching: false,
            bInfo: false,
            columnDefs: [{
                "className": "dt-center",
                "targets": [0, 1, 2]
            }, ],
            select: {
                style: 'multi'
            }
        }).on('select', function(e, dt, type, indexes) {
            var i;
            var rMap = "";
            for (i = 0; i < $("#datatable-map").DataTable().rows('.selected').data().length; i++) {
                rMap += "-map 0:" + $("#datatable-map").DataTable().rows('.selected').data()[i][0] + " ";
            }
            $("#custom_map").val(rMap.trim());
        });
        evaluateDirectSource();
    });
</script>
</body>

</html>