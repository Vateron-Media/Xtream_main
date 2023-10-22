<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_bouquet"))) { exit; }

if (isset($_POST["reorder"])) {
    $rOrder = json_decode($_POST["stream_order_array"], True);
    if (is_array($rOrder)) {
        $rStreamOrder = $rOrder["stream"];
        foreach ($rOrder["movie"] as $rID) {
            $rStreamOrder[] = $rID;
        }
        foreach ($rOrder["radio"] as $rID) {
            $rStreamOrder[] = $rID;
        }
        $db->query("UPDATE `bouquets` SET `bouquet_channels` = '".ESC(json_encode($rStreamOrder))."', `bouquet_series` = '".ESC(json_encode($rOrder["series"]))."' WHERE `id` = ".intval($_POST["reorder"]).";");
    }
}

if (!isset($_GET["id"])) { exit; }
$rBouquet = getBouquet($_GET["id"]);
if (!$rBouquet) { exit; }

$rListings = Array("stream" => Array(), "movie" => Array(), "radio" => Array(), "series" => Array());
$rOrdered = Array("stream" => Array(), "movie" => Array(), "radio" => Array(), "series" => Array());
$rChannels = json_decode($rBouquet["bouquet_channels"], True);
$rSeries = json_decode($rBouquet["bouquet_series"], True);

if (is_array($rChannels)) {
    $result = $db->query("SELECT `streams`.`id`, `streams`.`type`, `streams`.`category_id`, `streams`.`stream_display_name`, `stream_categories`.`category_name` FROM `streams`, `stream_categories` WHERE `streams`.`category_id` = `stream_categories`.`id` AND `streams`.`id` IN (".ESC(join(",", $rChannels)).");");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if ($row["type"] == 2) {
				$rListings["movie"][intval($row["id"])] = $row;
            } else if ($row["type"] == 4) {
                $rListings["radio"][intval($row["id"])] = $row;
            } else {
                $rListings["stream"][intval($row["id"])] = $row;
            }
        }
    }
}
if (is_array($rSeries)) {
    $result = $db->query("SELECT `series`.`id`, `series`.`category_id`, `series`.`title`, `stream_categories`.`category_name` FROM `series`, `stream_categories` WHERE `series`.`category_id` = `stream_categories`.`id` AND `series`.`id` IN (".ESC(join(",", $rSeries)).");");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rListings["series"][intval($row["id"])] = $row;
        }
    }
}

foreach ($rChannels as $rChannel) {
    if (isset($rListings["stream"][intval($rChannel)])) {
        $rOrdered["stream"][] = $rListings["stream"][intval($rChannel)];
    } else if (isset($rListings["movie"][intval($rChannel)])) {
        $rOrdered["movie"][] = $rListings["movie"][intval($rChannel)];
    } else if (isset($rListings["radio"][intval($rChannel)])) {
        $rOrdered["radio"][] = $rListings["radio"][intval($rChannel)];
    }
}

foreach ($rSeries as $rItem) {
    if (isset($rListings["series"][intval($rItem)])) {
        $rOrdered["series"][] = $rListings["series"][intval($rItem)];
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
                                        <a href="bouquet.php?id=<?=$_GET["id"]?>">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-pencil-outline"></i> <?=$_["edit_bouquet"]?>
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$rBouquet["bouquet_name"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="./bouquet_order.php?id=<?=$_GET["id"]?>" method="POST" id="bouquet_order_form">
                                    <input type="hidden" id="stream_order_array" name="stream_order_array" value="" />
                                    <input type="hidden" name="reorder" value="<?=$_GET["id"]?>" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#bouquet-stream" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="la la-play-circle-o mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["streams"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#bouquet-movie" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="la la-video-camera mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["movies"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#bouquet-series" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="la la-tv mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["series"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#bouquet-stations" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-radio-tower mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["stations"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="bouquet-stream">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_stream" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["stream"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["stream_display_name"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('stream')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('stream')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('stream')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('stream')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('stream')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="bouquet-movie">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_movie" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["movie"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["stream_display_name"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('movie')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('movie')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('movie')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('movie')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('movie')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="bouquet-series">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_series" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["series"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["title"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('series')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('series')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('series')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('series')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('series')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="bouquet-stations">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_radio" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["radio"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["stream_display_name"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('series')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('series')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('series')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('series')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('series')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
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
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/nestable2/jquery.nestable.min.js"></script>
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
        <script src="assets/libs/datatables/dataTables.rowReorder.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        function AtoZ(rType) {
            $("#sort_" + rType).append($("#sort_" + rType + " option").remove().sort(function(a, b) {
                var at = $(a).text().toUpperCase(), bt = $(b).text().toUpperCase();
                return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
            }));
        }
        function MoveUp(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                var rPrevious = rSelected.first().prev()[0];
                if ($(rPrevious).html() != '') {
                    rSelected.first().prev().before(rSelected);
                }
            }
        }
        function MoveDown(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                rSelected.last().next().after(rSelected);
            }
        }
        function MoveTop(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                rSelected.prependTo($('#sort_' + rType));
            }
        }
        function MoveBottom(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                rSelected.appendTo($('#sort_' + rType));
            }
        }
        $(document).ready(function() {
            $("#bouquet_order_form").submit(function(e){
                var rOrder = {"stream": [], "movie": [], "radio": [], "series": []};
                $('#sort_stream option').each(function() {
                    rOrder["stream"].push($(this).val());
                });
                $('#sort_movie option').each(function() {
                    rOrder["movie"].push($(this).val());
                });
                $('#sort_radio option').each(function() {
                    rOrder["radio"].push($(this).val());
                });
                $('#sort_series option').each(function() {
                    rOrder["series"].push($(this).val());
                });
                $("#stream_order_array").val(JSON.stringify(rOrder));
            });
        });
        </script>
    </body>
</html>