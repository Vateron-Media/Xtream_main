<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_bouquet")) && (!hasPermissions("adv", "edit_bouquet")))) { exit; }

if (isset($_POST["submit_bouquet"])) {
    $rArray = Array("bouquet_name" => "", "bouquet_channels" => Array(), "bouquet_series" => Array());
    if (is_array(json_decode($_POST["bouquet_data"], True))) {
        $rBouquetData = json_decode($_POST["bouquet_data"], True);
        $rArray["bouquet_channels"] = array_values($rBouquetData["stream"]);
        $rArray["bouquet_series"] = array_values($rBouquetData["series"]);
    } else if (isset($_POST["edit"])) {
        echo $_["bouquet_data_not_transfered"]; exit;
    }
    if (!isset($_POST["edit"])) {
        $rArray["bouquet_order"] = intval($db->query("SELECT MAX(`bouquet_order`) AS `max` FROM `bouquets`;")->fetch_assoc()["max"]) + 1;
    }
    foreach($_POST as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    $rCols = "`".ESC(implode('`,`', array_keys($rArray)))."`";
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
		if (!hasPermissions("adv", "edit_bouquet")) { exit; }
        $rCols = "id,".$rCols;
        $rValues = ESC($_POST["edit"]).",".$rValues;
    } else if (!hasPermissions("adv", "add_bouquet")) { exit; }
    $rQuery = "REPLACE INTO `bouquets`(".$rCols.") VALUES(".$rValues.");";
    if ($db->query($rQuery)) {
        if (isset($_POST["edit"])) {
            $rInsertID = intval($_POST["edit"]);
        } else {
            $rInsertID = $db->insert_id;
        }
        $_STATUS = 0;
        scanBouquet($rInsertID);
        header("Location: ./bouquet.php?id=".$rInsertID); exit;
    } else {
        $_STATUS = 1;
    }
}

if (isset($_GET["id"])) {
    $rBouquets = getBouquets();
    $rBouquetArr = $rBouquets[$_GET["id"]];
    if ((!$rBouquetArr) OR (!hasPermissions("adv", "edit_bouquet"))) {
        exit;
    }
} else if (!hasPermissions("adv", "add_bouquet")) {
	exit;
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
                                    <a href="./bouquets.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?=$_["back_to_bouquets"]?></li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rBouquetArr)) { echo $_["edit_bouquet"]; } else { echo $_["add_bouquet"]; } ?></h4>
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
                            <?=$_["bouquet_success"];?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["generic_fail"];?>
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./bouquet.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="bouquet_form" data-parsley-validate="">
                                    <?php if (isset($rBouquetArr)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rBouquetArr["id"]?>" />
                                    <input type="hidden" id="bouquet_data" name="bouquet_data" value="" />
                                    <?php } ?>
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#bouquet-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["details"]?></span>
                                                </a>
                                            </li>
                                            <?php if (isset($rBouquetArr)) { ?>
                                            <li class="nav-item">
                                                <a href="#channels" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-play mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["streams"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#vod" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-movie mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["movies"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#series" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-youtube-tv mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["series"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#radios" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-radio mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["radio"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#review" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-book-open-variant mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["review"]?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="bouquet-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="bouquet_name"><?=$_["bouquet_name"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="bouquet_name" name="bouquet_name" value="<?php if (isset($rBouquetArr)) { echo htmlspecialchars($rBouquetArr["bouquet_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="next list-inline-item float-right">
                                                        <?php if (isset($rBouquetArr)) { ?>
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                        <?php } else { ?>
                                                        <input name="submit_bouquet" type="submit" class="btn btn-primary" value="<?=$_["add"]?>" />
                                                        <?php } ?>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php if (isset($rBouquetArr)) { ?>
                                            <div class="tab-pane" id="channels">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_name"><?=$_["category_name"]?></label>
                                                            <div class="col-md-8">
                                                                <select id="category_id" class="form-control" data-toggle="select2">
                                                                    <option value="" selected><?=$_["all_categories"]?></option>
                                                                    <?php foreach ($rCategories as $rCategory) { ?>
                                                                    <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_search"><?=$_["search"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="stream_search" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-streams" class="table nowrap">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center"><?=$_["id"]?></th>
                                                                        <th><?=$_["stream_name"]?></th>
                                                                        <th><?=$_["category"]?></th>
                                                                        <th class="text-center"><?=$_["actions"]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <span class="float-right">
                                                        <li class="list-inline-item">
                                                            <a href="javascript: void(0);" onClick="toggleBouquets('datatable-streams')" class="btn btn-primary"><?=$_["toggle_page"]?></a>
                                                        </li>
                                                        <li class="next list-inline-item">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                        </li>
                                                    </span>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="vod">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_name"><?=$_["category_name"]?></label>
                                                            <div class="col-md-8">
                                                                <select id="category_idv" class="form-control" data-toggle="select2">
                                                                    <option value="" selected><?=$_["all_categories"]?></option>
                                                                    <?php foreach (getCategories("movie") as $rCategory) { ?>
                                                                    <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="vod_search"><?=$_["search"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="vod_search" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-vod" class="table nowrap">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center"><?=$_["id"]?></th>
                                                                        <th><?=$_["vod_name"]?></th>
                                                                        <th><?=$_["category"]?></th>
                                                                        <th class="text-center"><?=$_["actions"]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <span class="float-right">
                                                        <li class="list-inline-item">
                                                            <a href="javascript: void(0);" onClick="toggleBouquets('datatable-vod')" class="btn btn-primary"><?=$_["toggle_page"]?></a>
                                                        </li>
                                                        <li class="next list-inline-item">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                        </li>
                                                    </span>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="series">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_name"><?=$_["category_name"]?></label>
                                                            <div class="col-md-8">
                                                                <select id="category_ids" class="form-control" data-toggle="select2">
                                                                    <option value="" selected><?=$_["all_categories"]?></option>
                                                                    <?php foreach (getCategories("series") as $rCategory) { ?>
                                                                    <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="series_search"><?=$_["search"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="series_search" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-series" class="table nowrap">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center"><?=$_["id"]?></th>
                                                                        <th><?=$_["series_name"]?></th>
                                                                        <th><?=$_["category"]?></th>
                                                                        <th class="text-center"><?=$_["actions"]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <span class="float-right">
                                                        <li class="list-inline-item">
                                                            <a href="javascript: void(0);" onClick="toggleBouquets('datatable-series')" class="btn btn-primary"><?=$_["toggle_page"]?></a>
                                                        </li>
                                                        <li class="next list-inline-item">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                        </li>
                                                    </span>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="radios">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_idr"><?=$_["category_name"]?></label>
                                                            <div class="col-md-8">
                                                                <select id="category_idr" class="form-control" data-toggle="select2">
                                                                    <option value="" selected><?=$_["all_categories"]?></option>
                                                                    <?php foreach (getCategories("radio") as $rCategory) { ?>
                                                                    <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="radios_search"><?=$_["search"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="radios_search" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-radios" class="table nowrap">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center"><?=$_["id"]?></th>
                                                                        <th><?=$_["station_name"]?></th>
                                                                        <th><?=$_["category"]?></th>
                                                                        <th class="text-center"><?=$_["actions"]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <span class="float-right">
                                                        <li class="list-inline-item">
                                                            <a href="javascript: void(0);" onClick="toggleBouquets('datatable-series')" class="btn btn-primary"><?=$_["toggle_page"]?></a>
                                                        </li>
                                                        <li class="next list-inline-item">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                        </li>
                                                    </span>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="review">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-review" class="table nowrap">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center"><?=$_["id"]?></th>
                                                                        <th><?=$_["type"]?></th>
                                                                        <th><?=$_["display_name"]?></th>
                                                                        <th class="text-center"><?=$_["actions"]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_bouquet" type="submit" class="btn btn-primary" value="<?php if (isset($rBouquetArr)) { echo $_["edit"]; } else { echo $_["add"]; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php } ?>
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
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
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
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        <?php if (isset($rBouquetArr)) {
        if (!is_array(json_decode($rBouquetArr["bouquet_series"], True))) { $rBouquetArr["bouquet_series"] = "[]"; }
        if (!is_array(json_decode($rBouquetArr["bouquet_channels"], True))) { $rBouquetArr["bouquet_channels"] = "[]"; }
        ?>
        var rBouquet = {"stream": $.parseJSON(<?=json_encode($rBouquetArr["bouquet_channels"])?>), "series": $.parseJSON(<?=json_encode($rBouquetArr["bouquet_series"])?>)};
        <?php } ?>
        function reviewBouquet() {
            var rTable = $('#datatable-review').DataTable();
            rTable.clear();
            rTable.draw();
            $.post("./api.php?action=review_bouquet", {"data": rBouquet}, function(rData) {
                if (rData.result === true) {
                    $(rData.streams).each(function(rIndex) {
                        rTable.row.add([rData.streams[rIndex].id, '<?=$_["stream"]?>', rData.streams[rIndex].stream_display_name, '<button type="button" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet(' + rData.streams[rIndex].id + ', \'stream\', true);"><i class="mdi mdi-minus"></i></button>']);
                    });
                    $(rData.vod).each(function(rIndex) {
                        rTable.row.add([rData.vod[rIndex].id, '<?=$_["movie"]?>', rData.vod[rIndex].stream_display_name, '<button type="button" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet(' + rData.vod[rIndex].id + ', \'vod\', true);"><i class="mdi mdi-minus"></i></button>']);
                    });
                    $(rData.radios).each(function(rIndex) {
                        rTable.row.add([rData.radios[rIndex].id, '<?=$_["radio"]?>', rData.radios[rIndex].stream_display_name, '<button type="button" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet(' + rData.radios[rIndex].id + ', \'radios\', true);"><i class="mdi mdi-minus"></i></button>']);
                    });
                    $(rData.series).each(function(rIndex) {
                        rTable.row.add([rData.series[rIndex].id, '<?=$_["series"]?>', rData.series[rIndex].title, '<button type="button" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet(' + rData.series[rIndex].id + ', \'series\', true);"><i class="mdi mdi-minus"></i></button>']);
                    });
                } else {
                    alert("Bouquet review failed!");
                }
                rTable.draw();
            }, "json");
        }
        
        function toggleBouquet(rID, rType, rReview = false) {
            if (rType == "vod") { rType = "stream"; }
            if (rType == "radios") { rType = "stream"; }
            var rIndex = rBouquet[rType].indexOf(parseInt(rID));
            if (rIndex > -1) {
                rBouquet[rType] = jQuery.grep(rBouquet[rType], function(rValue) {
                    return parseInt(rValue) != parseInt(rID);
                });
            } else {
                rBouquet[rType].push(parseInt(rID));
            }
            if (rReview == true) {
                if (rType == "stream") {
                    $("#datatable-streams").DataTable().ajax.reload(null, false);
                    $("#datatable-vod").DataTable().ajax.reload(null, false);
                    $("#datatable-radios").DataTable().ajax.reload(null, false);
                } else {
                    $("#datatable-series").DataTable().ajax.reload(null, false);
                }
                reviewBouquet()
            }
        }
        
        function toggleBouquets(rPage) {
            $("#" + rPage + " tr").each(function() {
                $(this).find("td:last-child button").filter(':visible').each(function() {
                    toggleBouquet($(this).data("id"), $(this).data("type"), false);
                });
            });
            $("#" + rPage).DataTable().ajax.reload(null, false);
            reviewBouquet()
        }
        
        $(document).ready(function() {
            $("#datatable-streams").DataTable({
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
                    $(row).addClass('stream-' + data[0]);
                    var rIndex = rBouquet["stream"].indexOf(parseInt(data[0]));
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
                        d.id = "bouquets_streams";
                        d.category_id = $("#category_id").val();
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,3]}
                ],
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
                    var rIndex = rBouquet["stream"].indexOf(parseInt(data[0]));
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
                        d.id = "bouquets_vod";
                        d.category_id = $("#category_idv").val();
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,3]}
                ],
            });
            $("#datatable-series").DataTable({
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
                    $(row).addClass('series-' + data[0]);
                    var rIndex = rBouquet["series"].indexOf(parseInt(data[0]));
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
                        d.id = "bouquets_series";
                        d.category_id = $("#category_ids").val();
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,3]}
                ],
            });
            $("#datatable-radios").DataTable({
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
                    $(row).addClass('radios-' + data[0]);
                    var rIndex = rBouquet["stream"].indexOf(parseInt(data[0]));
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
                        d.id = "bouquets_radios";
                        d.category_id = $("#category_idr").val();
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,3]}
                ],
            });
            $("#datatable-review").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                bInfo: false,
                bAutoWidth: false,
                searching: true,
                pageLength: 100,
                lengthChange: false,
                columnDefs: [
                    {"className": "dt-center", "targets": [0,1,3]}
                ],
            });
            $('select').select2({width: '100%'});
            $("#category_id").on("select2:select", function(e) { 
                $("#datatable-streams").DataTable().ajax.reload(null, false);
            });
            $('#stream_search').keyup(function(){
                $('#datatable-streams').DataTable().search($(this).val()).draw();
            })
            $("#category_idv").on("select2:select", function(e) { 
                $("#datatable-vod").DataTable().ajax.reload(null, false);
            });
            $('#vod_search').keyup(function(){
                $('#datatable-vod').DataTable().search($(this).val()).draw();
            })
            $("#category_ids").on("select2:select", function(e) { 
                $("#datatable-series").DataTable().ajax.reload(null, false);
            });
            $('#series_search').keyup(function(){
                $('#datatable-series').DataTable().search($(this).val()).draw();
            });
            $("#category_idr").on("select2:select", function(e) { 
                $("#datatable-radios").DataTable().ajax.reload(null, false);
            });
            $('#radios_search').keyup(function(){
                $('#datatable-radios').DataTable().search($(this).val()).draw();
            });
            $(document).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr("href") == "#review") {
                    reviewBouquet();
                }
            });
            $("#bouquet_form").submit(function(e){
                if ($("#bouquet_name").val().length == 0) {
                    e.preventDefault();
                    $.toast("<?=$_["enter_a_bouquet_name"]?>");
                }
                $("#bouquet_data").val(JSON.stringify(rBouquet));
            });
            $("form").attr('autocomplete', 'off');
        });
        </script>
    </body>
</html>