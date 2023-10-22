<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "add_cat"))) { exit; }

if (isset($_POST["submit_category"])) {
    $rArray = Array("category_type" => "live", "category_name" => "", "parent_id" => 0, "cat_order" => 99);
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
    if ((isset($_POST["edit"])) && (hasPermissions("adv", "edit_cat"))) {
        $rCols = "id,".$rCols;
        $rValues = ESC($_POST["edit"]).",".$rValues;
    }
    $rQuery = "REPLACE INTO `stream_categories`(".$rCols.") VALUES(".$rValues.");";
    if ($db->query($rQuery)) {
        if (isset($_POST["edit"])) {
            $rInsertID = intval($_POST["edit"]);
        } else {
            $rInsertID = $db->insert_id;
        }
    }
    if (isset($rInsertID)) {
        header("Location: ./stream_categories.php");exit;
    } else {
        $_STATUS = 1;
    }
}

if (isset($_GET["id"])) {
    $rCategoryArr = getCategory($_GET["id"]);
    if ((!$rCategoryArr) or (!hasPermissions("adv", "edit_cat"))) {
        exit;
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
                                    <a href="./stream_categories.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Categories</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rCategoryArr)) { echo "Edit"; } else { echo "Add"; } ?> Category</h4>
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
                            Category operation was completed successfully.
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
                                <form action="./stream_category.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="category_form" data-parsley-validate="">
                                    <?php if (isset($rCategoryArr)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rCategoryArr["id"]?>" />
                                    <input type="hidden" name="cat_order" value="<?=$rCategoryArr["cat_order"]?>" />
                                    <?php } ?>
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#category-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <?php if (isset($rCategoryArr)) { ?>
                                            <li class="nav-item">
                                                <a href="#view-channels" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-play mr-1"></i>
                                                    <span class="d-none d-sm-inline">View Streams</span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="category-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <?php if (!isset($rCategoryArr)) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_type">Category Type</label>
                                                            <div class="col-md-8">
                                                                <select name="category_type" id="category_type" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (Array("live" => "Live TV", "movie" => "Movie", "series" => "TV Series", "radio" => "Radio Station") as $rGroupID => $rGroup) { ?>
                                                                    <option <?php if (isset($rCategoryArr)) { if ($rCategoryArr["category_type"] == $rGroupID) { echo "selected "; } } ?>value="<?=$rGroupID?>"><?=$rGroup?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <?php } else { ?>
                                                        <input type="hidden" name="category_type" value="<?=$rCategoryArr["category_type"]?>" />
                                                        <?php } ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_name">Category Name</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="category_name" name="category_name" value="<?php if (isset($rCategoryArr)) { echo htmlspecialchars($rCategoryArr["category_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item float-right">
                                                        <input name="submit_category" type="submit" class="btn btn-primary" value="<?php if (isset($rCategoryArr)) { echo "Edit"; } else { echo "Add"; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="view-channels">
                                                <div class="row">
                                                    <div class="col-12" style="overflow-x:auto;">
                                                        <table id="datatable" class="table dt-responsive nowrap">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-center">Stream ID</th>
                                                                    <th>Stream Name</th>
                                                                    <th class="text-center">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
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
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        $(document).ready(function() {
            $('select').select2({width: '100%'})
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $("form").attr('autocomplete', 'off');
            <?php if (isset($rCategoryArr)) { ?>
            $("#datatable").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('[data-toggle="tooltip"]').tooltip();
                },
                responsive: false,
                bAutoWidth: false,
                bInfo: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table.php",
                    "data": function(d) {
                        <?php if ($rCategoryArr["category_type"] == "live") { ?>
                        d.id = "streams_short";
                        <?php } else if ($rCategoryArr["category_type"] == "movie") { ?>
                        d.id = "movies_short";
                        <?php } else if ($rCategoryArr["category_type"] == "radio") { ?>
                        d.id = "radios_short";
                        <?php } else { ?>
                        d.id = "series_short";
                        <?php } ?>
                        d.category_id = <?=$rCategoryArr["id"]?>;
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,2]}
                ],
            });
            <?php } ?>
        });
        </script>
    </body>
</html>