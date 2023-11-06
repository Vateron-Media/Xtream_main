<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "folder_watch_settings"))) {
    exit;
}

if (isset($_GET["update"])) {
    updateTMDbCategories();
    header("Location: ./settings_watch.php");
}

if (isset($_POST["submit_settings"])) {
    foreach ($_POST as $rKey => $rValue) {
        $rSplit = explode("_", $rKey);
        if ($rSplit[0] == "genre") {
            $rGenreID = intval($rSplit[1]);
            $rBouquets = json_encode($_POST["bouquet_" . $rGenreID]);
            if (!$rBouquets) {
                $rBouquets = "[]";
            }
            $db->query("UPDATE `watch_categories` SET `category_id` = " . intval($rValue) . ", `bouquets` = '" . ESC($rBouquets) . "' WHERE `genre_id` = " . intval($rGenreID) . " AND `type` = 1;");
        }
    }
    foreach ($_POST as $rKey => $rValue) {
        $rSplit = explode("_", $rKey);
        if ($rSplit[0] == "genretv") {
            $rGenreID = intval($rSplit[1]);
            $rBouquets = json_encode($_POST["bouquettv_" . $rGenreID]);
            if (!$rBouquets) {
                $rBouquets = "[]";
            }
            $db->query("UPDATE `watch_categories` SET `category_id` = " . intval($rValue) . ", `bouquets` = '" . ESC($rBouquets) . "' WHERE `genre_id` = " . intval($rGenreID) . " AND `type` = 2;");
        }
    }
    if (isset($_POST["read_native"])) {
        $rNative = 1;
    } else {
        $rNative = 0;
    }
    if (isset($_POST["movie_symlink"])) {
        $rSymLink = 1;
    } else {
        $rSymLink = 0;
    }
    if (isset($_POST["auto_encode"])) {
        $rAutoEncode = 1;
    } else {
        $rAutoEncode = 0;
    }
    if (isset($_POST["ffprobe_input"])) {
        $rProbeInput = 1;
    } else {
        $rProbeInput = 0;
    }
    $db->query("UPDATE `watch_settings` SET `ffprobe_input` = " . $rProbeInput . ", `percentage_match` = " . intval($_POST["percentage_match"]) . ", `read_native` = " . $rNative . ", `movie_symlink` = " . $rSymLink . ", `auto_encode` = " . $rAutoEncode . ", `transcode_profile_id` = " . intval($_POST["transcode_profile_id"]) . ", `scan_seconds` = " . intval($_POST["scan_seconds"]) . ";");
}

$rBouquets = getBouquets();

$rResult = $db->query("SELECT * FROM `watch_settings`;");
if (($rResult) && ($rResult->num_rows == 1)) {
    $rWatchSettings = $rResult->fetch_assoc();
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) { ?>
    <div class="content-page">
        <div class="content boxed-layout-ext">
            <div class="container-fluid">
            <?php } else { ?>
                <div class="wrapper boxed-layout-ext">
                    <div class="container-fluid">
                    <?php } ?>
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="./watch.php">
                                                <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                    <?= $_["folders"] ?>
                                                </button>
                                            </a>
                                            <a href="./settings_watch.php?update=1">
                                                <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                    <?= $_["update_from_tmdb"] ?>
                                                </button>
                                            </a>
                                        </li>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?= $_["folder_watch_settings"] ?> </h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-xl-12">
                            <?php if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["generic_fail"] ?>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./settings_watch.php" method="POST" id="watch_settings_form">
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#setup" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["setup"] ?> </span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#categories" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-movie mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["movie_categories"] ?> </span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#categories-tv" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-youtube-tv mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["tv_categories"] ?> </span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="setup">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="read_native"><?= $_["native_frames"] ?> </label>
                                                                <div class="col-md-2">
                                                                    <input name="read_native" id="read_native" type="checkbox" <?php if ($rWatchSettings["read_native"] == 1) {
                                                                                                                                    echo "checked ";
                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-4 col-form-label" for="movie_symlink"><?= $_["create_symlink"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["generate_a_symlink"] ?>" class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input name="movie_symlink" id="movie_symlink" type="checkbox" <?php if ($rWatchSettings["movie_symlink"] == 1) {
                                                                                                                                        echo "checked ";
                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="auto_encode"><?= $_["auto_encode"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["start_encoding_as_soon"] ?>" class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input name="auto_encode" id="auto_encode" type="checkbox" <?php if ($rWatchSettings["auto_encode"] == 1) {
                                                                                                                                    echo "checked ";
                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-4 col-form-label" for="ffprobe_input"><?= $_["probe_input"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["use_ffmpeg_to_probe_input_files"] ?>" class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input name="ffprobe_input" id="ffprobe_input" type="checkbox" <?php if ($rWatchSettings["ffprobe_input"] == 1) {
                                                                                                                                        echo "checked ";
                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="scan_seconds"><?= $_["scan_frequency"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["scan_a_folder"] ?>" class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" id="scan_seconds" name="scan_seconds" value="<?php echo htmlspecialchars($rWatchSettings["scan_seconds"]); ?>" required data-parsley-trigger="<?= $_["change"] ?>">
                                                                </div>
                                                                <label class="col-md-4 col-form-label" for="percentage_match"><?= $_["match_percentage"] ?> <i data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $_["tmdb_match_tolerance"] ?>" class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" id="percentage_match" name="percentage_match" value="<?php echo htmlspecialchars($rWatchSettings["percentage_match"]); ?>" required data-parsley-trigger="<?= $_["change"] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="transcode_profile_id"><?= $_["transcoding_profile"] ?> </label>
                                                                <div class="col-md-8">
                                                                    <select name="transcode_profile_id" id="transcode_profile_id" class="form-control" data-toggle="select2">
                                                                        <option <?php if (intval($rWatchSettings["transcode_profile_id"]) == 0) {
                                                                                    echo "selected ";
                                                                                } ?>value="0"><?= $_["transcoding_disabled"] ?> </option>
                                                                        <?php foreach (getTranscodeProfiles() as $rProfile) { ?>
                                                                            <option <?php if (intval($rWatchSettings["transcode_profile_id"]) == intval($rProfile["profile_id"])) {
                                                                                        echo "selected ";
                                                                                    } ?>value="<?= $rProfile["profile_id"] ?>"><?= $rProfile["profile_name"] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="list-inline-item float-right">
                                                            <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?> " />
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-pane" id="categories">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <p class="sub-header">
                                                                <?= $_["select_a_category_and"] ?>
                                                            </p>
                                                            <?php $rResult = $db->query("SELECT * FROM `watch_categories` WHERE `type` = 1 ORDER BY `genre` ASC;");
                                                            if (($rResult) && ($rResult->num_rows > 0)) {
                                                                while ($rRow = $rResult->fetch_assoc()) { ?>
                                                                    <div class="form-group row mb-4">
                                                                        <label class="col-md-2 col-form-label" for="genre_<?= $rRow["genre_id"] ?>"><?= $rRow["genre"] ?></label>
                                                                        <div class="col-md-4">
                                                                            <select name="genre_<?= $rRow["genre_id"] ?>" id="genre_<?= $rRow["genre_id"] ?>" class="form-control select2" data-toggle="select2">
                                                                                <option <?php if (intval($rRow["category_id"]) == 0) {
                                                                                            echo "selected ";
                                                                                        } ?>value="0"><?= $_["do_not_use"] ?> </option>
                                                                                <?php foreach (getCategories("movie") as $rCategory) { ?>
                                                                                    <option <?php if (intval($rRow["category_id"]) == intval($rCategory["id"])) {
                                                                                                echo "selected ";
                                                                                            } ?>value="<?= $rCategory["id"] ?>"><?= $rCategory["category_name"] ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                        <label class="col-md-2 col-form-label" for="bouquet_<?= $rRow["genre_id"] ?>"><?= $_["add_to_bouquets"] ?> </label>
                                                                        <div class="col-md-4">
                                                                            <select name="bouquet_<?= $rRow["genre_id"] ?>[]" id="bouquet_<?= $rRow["genre_id"] ?>" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="<?= $_["choose"] ?>">
                                                                                <?php foreach ($rBouquets as $rBouquet) { ?>
                                                                                    <option <?php if (in_array(intval($rBouquet["id"]), json_decode($rRow["bouquets"], True))) {
                                                                                                echo "selected ";
                                                                                            } ?>value="<?= $rBouquet["id"] ?>"><?= $rBouquet["bouquet_name"] ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                            <?php }
                                                            } ?>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="list-inline-item float-right">
                                                            <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?> " />
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-pane" id="categories-tv">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <p class="sub-header">
                                                                <?= $_["select_a_category_and"] ?>
                                                            </p>
                                                            <?php $rResult = $db->query("SELECT * FROM `watch_categories` WHERE `type` = 2 ORDER BY `genre` ASC;");
                                                            if (($rResult) && ($rResult->num_rows > 0)) {
                                                                while ($rRow = $rResult->fetch_assoc()) { ?>
                                                                    <div class="form-group row mb-4">
                                                                        <label class="col-md-2 col-form-label" for="genretv_<?= $rRow["genre_id"] ?>"><?= $rRow["genre"] ?></label>
                                                                        <div class="col-md-4">
                                                                            <select name="genretv_<?= $rRow["genre_id"] ?>" id="genretv_<?= $rRow["genre_id"] ?>" class="form-control select2" data-toggle="select2">
                                                                                <option <?php if (intval($rRow["category_id"]) == 0) {
                                                                                            echo "selected ";
                                                                                        } ?>value="0"><?= $_["do_not_use"] ?></option>
                                                                                <?php foreach (getCategories("series") as $rCategory) { ?>
                                                                                    <option <?php if (intval($rRow["category_id"]) == intval($rCategory["id"])) {
                                                                                                echo "selected ";
                                                                                            } ?>value="<?= $rCategory["id"] ?>"><?= $rCategory["category_name"] ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                        <label class="col-md-2 col-form-label" for="bouquettv_<?= $rRow["genre_id"] ?>"><?= $_["add_to_bouquets"] ?></label>
                                                                        <div class="col-md-4">
                                                                            <select name="bouquettv_<?= $rRow["genre_id"] ?>[]" id="bouquettv_<?= $rRow["genre_id"] ?>" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="<?= $_["choose"] ?>">
                                                                                <?php foreach ($rBouquets as $rBouquet) { ?>
                                                                                    <option <?php if (in_array(intval($rBouquet["id"]), json_decode($rRow["bouquets"], True))) {
                                                                                                echo "selected ";
                                                                                            } ?>value="<?= $rBouquet["id"] ?>"><?= $rBouquet["bouquet_name"] ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                            <?php }
                                                            } ?>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="list-inline-item float-right">
                                                            <input name="submit_settings" type="submit" class="btn btn-primary" value="<?= $_["save_changes"] ?> " />
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
                <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
                <script src="assets/libs/switchery/switchery.min.js"></script>
                <script src="assets/libs/select2/select2.min.js"></script>
                <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
                <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
                <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
                <script src="assets/libs/moment/moment.min.js"></script>
                <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
                <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
                <script src="assets/libs/treeview/jstree.min.js"></script>
                <script src="assets/js/pages/treeview.init.js"></script>
                <script src="assets/js/pages/form-wizard.init.js"></script>
                <script src="assets/js/app.min.js"></script>
                <script>
                    $(document).ready(function() {
                        $('select').select2({
                            width: '100%'
                        });
                        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                        elems.forEach(function(html) {
                            var switchery = new Switchery(html);
                        });

                        $(window).keypress(function(event) {
                            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                        });

                        $("form").attr('autocomplete', 'off');
                    });
                </script>
                </body>

                </html>