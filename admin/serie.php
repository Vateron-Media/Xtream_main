<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_series")) && (!hasPermissions("adv", "edit_series")))) {
    exit;
}

$rCategories = getCategories_admin("series");

if (isset($_POST["submit_series"])) {
    if (isset($_POST["edit"])) {
        if (!hasPermissions("adv", "edit_series")) {
            exit;
        }
        $rArray = getSerie($_POST["edit"]);
        unset($rArray["id"]);
    } else {
        if (!hasPermissions("adv", "add_series")) {
            exit;
        }
        $rArray = array("title" => "", "category_id" => "", "episode_run_time" => 0, "tmdb_id" => 0, "cover" => "", "genre" => "", "plot" => "", "cast" => "", "rating" => 0, "director" => "", "releaseDate" => "", "last_modified" => time(), "seasons" => array(), "backdrop_path" => array(), "youtube_trailer" => "");
    }
    if ($rAdminSettings["download_images"]) {
        $_POST["cover"] = downloadImage($_POST["cover"]);
        $_POST["backdrop_path"] = downloadImage($_POST["backdrop_path"]);
    }
    $rBouquets = $_POST["bouquets"];
    unset($_POST["bouquets"]);
    if (strlen($_POST["backdrop_path"]) == 0) {
        $rArray["backdrop_path"] = array();
    } else {
        $rArray["backdrop_path"] = array($_POST["backdrop_path"]);
    }
    unset($_POST["backdrop_path"]);
    $rArray["cover_big"] = $rArray["cover"];
    foreach ($_POST as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    $rCols = "`" . $ipTV_db_admin->escape(implode('`,`', array_keys($rArray))) . "`";
    $rValues = null;
    foreach (array_values($rArray) as $rValue) {
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
    if (isset($_POST["edit"])) {
        $rCols = "`id`," . $rCols;
        $rValues = $ipTV_db_admin->escape($_POST["edit"]) . "," . $rValues;
    }
    $rQuery = "REPLACE INTO `series`(" . $rCols . ") VALUES(" . $rValues . ");";
    if ($ipTV_db_adminTV_db_admin->query($rQuery)) {
        if (isset($_POST["edit"])) {
            $rInsertID = intval($_POST["edit"]);
        } else {
            $rInsertID = $ipTV_db_admin->last_insert_id();
        }
        updateSeries(intval($rInsertID));
        foreach ($rBouquets as $rBouquet) {
            addToBouquet("series", $rBouquet, $rInsertID);
        }
        foreach (getBouquets() as $rBouquet) {
            if (!in_array($rBouquet["id"], $rBouquets)) {
                removeFromBouquet("series", $rBouquet["id"], $rInsertID);
            }
        }
        scanBouquets();
    }
    if (isset($rInsertID)) {
        header("Location: ./serie.php?id=" . $rInsertID);
        exit;
    } else {
        $_STATUS = 1;
    }
}

if (isset($_GET["id"])) {
    $rSeries = getSerie($_GET["id"]);
    if ((!$rSeries) or (!hasPermissions("adv", "edit_series"))) {
        exit;
    }
} elseif (!hasPermissions("adv", "add_series")) {
    exit;
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
                                        <a href="./series.php">
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                                <?= $_["back_to_series"] ?></li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php if (isset($rSeries)) {
                                    echo $rSeries["title"];
                                                       } else {
                                                           echo $_["add_series"];
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
                                    <?= $_["series_operation"] ?>
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
                                    <form action="./serie.php<?php if (isset($_GET["id"])) {
                                        echo "?id=" . $_GET["id"];
                                                             } ?>" method="POST" id="series_form" data-parsley-validate="">
                                        <?php if (isset($rSeries)) { ?>
                                            <input type="hidden" name="edit" value="<?= $rSeries["id"] ?>" />
                                        <?php } ?>
                                        <!--<input type="hidden" id="tmdb_id" name="tmdb_id" value="<?php if (isset($rSeries)) {
                                            echo htmlspecialchars($rSeries["tmdb_id"]);
                                                                                                    } ?>" />-->
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
                                                    <a href="#movie-information" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-movie-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["information"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="stream-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="title"><?= $_["series_name"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="title"
                                                                        name="title" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["title"]);
                                                                                            } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="tmdb_search"><?= $_["tmdb_results"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select id="tmdb_search" class="form-control"
                                                                        data-toggle="select2"></select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="category_id"><?= $_["category_name"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="category_id" id="category_id"
                                                                        class="form-control" data-toggle="select2">
                                                                        <?php foreach ($rCategories as $rCategory) { ?>
                                                                            <option <?php if (isset($rSeries)) {
                                                                                if (intval($rSeries["category_id"]) == intval($rCategory["id"])) {
                                                                                    echo "selected ";
                                                                                }
                                                                                    } elseif ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) {
                                                                                        echo "selected ";
                                                                                    } ?>value="<?= $rCategory["id"] ?>">
                                                                                <?= $rCategory["category_name"] ?>
                                                                            </option>
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
                                                                        data-placeholder="<?= $_["choose"] ?>">
                                                                        <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                            <option <?php if (isset($rSeries)) {
                                                                                if (in_array($rSeries["id"], json_decode($rBouquet["bouquet_series"], true))) {
                                                                                    echo "selected ";
                                                                                }
                                                                                    } ?>value="<?= $rBouquet["id"] ?>"><?= htmlspecialchars($rBouquet["bouquet_name"]) ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
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
                                                <div class="tab-pane" id="movie-information">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="cover"><?= $_["poster_url"] ?></label>
                                                                <div class="col-md-8 input-group">
                                                                    <input type="text" class="form-control" id="cover"
                                                                        name="cover" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["cover"]);
                                                                                            } ?>">
                                                                    <div class="input-group-append">
                                                                        <a href="javascript:void(0)"
                                                                            onClick="openImage(this)"
                                                                            class="btn btn-primary waves-effect waves-light"><i
                                                                                class="mdi mdi-eye"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="backdrop_path"><?= $_["backdrop_url"] ?></label>
                                                                <div class="col-md-8 input-group">
                                                                    <input type="text" class="form-control"
                                                                        id="backdrop_path" name="backdrop_path" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars(json_decode($rSeries["backdrop_path"], true)[0]);
                                                                                                                       } ?>">
                                                                    <div class="input-group-append">
                                                                        <a href="javascript:void(0)"
                                                                            onClick="openImage(this)"
                                                                            class="btn btn-primary waves-effect waves-light"><i
                                                                                class="mdi mdi-eye"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="plot"><?= $_["plot"] ?></label>
                                                                <div class="col-md-8">
                                                                    <textarea rows="6" class="form-control" id="plot"
                                                                        name="plot"><?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["plot"]);
                                                                                    } ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="cast"><?= $_["cast"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="cast"
                                                                        name="cast" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["cast"]);
                                                                                           } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="director"><?= $_["director"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="director" name="director" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["director"]);
                                                                                                             } ?>">
                                                                </div>
                                                                <label class="col-md-2 col-form-label"
                                                                    for="genre"><?= $_["genres"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" id="genre"
                                                                        name="genre" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["genre"]);
                                                                                            } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="releaseDate"><?= $_["release_date"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="releaseDate" name="releaseDate" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["releaseDate"]);
                                                                                                                   } ?>">
                                                                </div>
                                                                <label class="col-md-2 col-form-label"
                                                                    for="episode_run_time"><?= $_["runtime"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="episode_run_time" name="episode_run_time"
                                                                        value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["episode_run_time"]);
                                                                               } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="youtube_trailer"><?= $_["youtube_trailer"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="youtube_trailer" name="youtube_trailer"
                                                                        value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["youtube_trailer"]);
                                                                               } ?>">
                                                                </div>
                                                                <label class="col-md-2 col-form-label"
                                                                    for="rating"><?= $_["rating"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" id="rating"
                                                                        name="rating" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["rating"]);
                                                                                             } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="tmdb_id"><?= $_["tmdb_id"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" id="tmdb_id"
                                                                        name="tmdb_id" value="<?php if (isset($rSeries)) {
                                                                            echo htmlspecialchars($rSeries["tmdb_id"]);
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
                                                        <li class="list-inline-item float-right">
                                                            <input name="submit_series" type="submit"
                                                                class="btn btn-primary" value="<?php if (isset($rSeries)) {
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
            <script src="assets/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
            <script src="assets/libs/treeview/jstree.min.js"></script>
            <script src="assets/js/pages/treeview.init.js"></script>
            <script src="assets/js/pages/form-wizard.init.js"></script>
            <script src="assets/libs/parsleyjs/parsley.min.js"></script>
            <script src="assets/js/app.min.js"></script>

            <script>
                var changeTitle = false;

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

                function openImage(elem) {
                    rPath = $(elem).parent().parent().find("input").val();
                    if (rPath.length > 0) {
                        if (rPath.substring(0, 1) == ".") {
                            window.open('<?= getURL() ?>' + rPath.substring(1, rPath.length));
                        } else if (rPath.substring(0, 1) == "/") {
                            window.open('<?= getURL() ?>' + rPath);
                        } else {
                            window.open(rPath);
                        }
                    }
                }
                $(document).ready(function () {
                    $('select').select2({
                        width: '100%'
                    });

                    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                    elems.forEach(function (html) {
                        var switchery = new Switchery(html);
                    });

                    $("#series_form").submit(function (e) {
                        if ($("#title").val().length == 0) {
                            e.preventDefault();
                            $.toast("<?= $_["enter_a_series_name"] ?>");
                        }
                    });

                    $(window).keypress(function (event) {
                        if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                    });

                    $("#title").change(function () {
                        if (!window.changeTitle) {
                            $("#tmdb_search").empty().trigger('change');
                            if ($("#title").val().length > 0) {
                                $.getJSON("./api.php?action=tmdb_search&type=series&term=" + $("#title").val(), function (data) {
                                    if (data.result == true) {
                                        if (data.data.length > 0) {
                                            newOption = new Option("<?= $_["found_"] ?>" + data.data.length + "<?= $_["_results"] ?>", -1, true, true);
                                        } else {
                                            newOption = new Option("<?= $_["no_results_found"] ?>", -1, true, true);
                                        }
                                        $("#tmdb_search").append(newOption).trigger('change');
                                        $(data.data).each(function (id, item) {
                                            if (item.first_air_date.length > 0) {
                                                rTitle = item.name + " (" + item.first_air_date.substring(0, 4) + ")";
                                            } else {
                                                rTitle = item.name;
                                            }
                                            newOption = new Option(rTitle, item.id, true, true);
                                            $("#tmdb_search").append(newOption);
                                        });
                                    } else {
                                        newOption = new Option("<?= $_["no_results_found"] ?>", -1, true, true);
                                    }
                                    $("#tmdb_search").val(-1).trigger('change');
                                });
                            }
                        } else {
                            window.changeTitle = false;
                        }
                    });
                    $("#tmdb_search").change(function () {
                        if (($("#tmdb_search").val()) && ($("#tmdb_search").val() > -1)) {
                            $.getJSON("./api.php?action=tmdb&type=series&id=" + $("#tmdb_search").val(), function (data) {
                                if (data.result == true) {
                                    window.changeTitle = true;
                                    $("#title").val(data.data.name);
                                    $("#cover").val("");
                                    if (data.data.poster_path.length > 0) {
                                        $("#cover").val("https://image.tmdb.org/t/p/w600_and_h900_bestv2" + data.data.poster_path);
                                    }
                                    $("#backdrop_path").val("");
                                    if (data.data.backdrop_path.length > 0) {
                                        $("#backdrop_path").val("https://image.tmdb.org/t/p/w1280" + data.data.backdrop_path);
                                    }
                                    $("#releaseDate").val(data.data.first_air_date);
                                    $("#episode_run_time").val(data.data.episode_run_time[0]);
                                    $("#youtube_trailer").val("");
                                    if (data.data.trailer) {
                                        $("#youtube_trailer").val(data.data.trailer);
                                    }
                                    rCast = "";
                                    rMemberID = 0;
                                    $(data.data.credits.cast).each(function (id, member) {
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
                                    $(data.data.genres).each(function (id, genre) {
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
                                    $(data.data.credits.crew).each(function (id, member) {
                                        if (member.department == "Directing") {
                                            $("#director").val(member.name);
                                            return true;
                                        }
                                    });
                                    $("#plot").val(data.data.overview);
                                    $("#rating").val(data.data.vote_average);
                                    $("#tmdb_id").val($("#tmdb_search").val());
                                }
                            });
                        }
                    });

                    $("#episode_run_time").inputFilter(function (value) {
                        return /^\d*$/.test(value);
                    });
                    $("form").attr('autocomplete', 'off');

                    <?php if (isset($rSeries)) { ?>
                        $("#title").trigger("change");
                    <?php } ?>
                });
            </script>
            </body>

            </html>