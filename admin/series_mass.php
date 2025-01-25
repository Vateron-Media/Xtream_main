<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "mass_sedits"))) {
    exit;
}

$rCategories = getCategories_admin("series");

if (isset(ipTV_lib::$request["submit_series"])) {
    $rArray = array();
    if (isset(ipTV_lib::$request["c_category_id"])) {
        $rArray["category_id"] = intval(ipTV_lib::$request["category_id"]);
    }
    $rSeriesIDs = json_decode(ipTV_lib::$request["series"], true);
    if (count($rSeriesIDs) > 0) {
        foreach ($rSeriesIDs as $rSeriesID) {
            $rQueries = array();
            foreach ($rArray as $rKey => $rValue) {
                $rQueries[] = "`" . $rKey . "` = '" . $rValue . "'";
            }
            if (count($rQueries) > 0) {
                $rQueryString = join(",", $rQueries);
                $rQuery = "UPDATE `series` SET " . $rQueryString . " WHERE `id` = " . intval($rSeriesID) . ";";
                if (!$ipTV_db_admin->query($rQuery)) {
                    $_STATUS = 1;
                }
            }
            if (isset(ipTV_lib::$request["c_bouquets"])) {
                $rBouquets = ipTV_lib::$request["bouquets"];
                foreach ($rBouquets as $rBouquet) {
                    addToBouquet("series", $rBouquet, $rSeriesID);
                }
                foreach (getBouquets() as $rBouquet) {
                    if (!in_array($rBouquet["id"], $rBouquets)) {
                        removeFromBouquet("series", $rBouquet["id"], $rSeriesID);
                    }
                }
            }
        }
        if (isset(ipTV_lib::$request["reprocess_tmdb"])) {
            foreach ($rSeriesIDs as $rSeriesID) {
                if (intval($rSeriesID) > 0) {
                    $ipTV_db_admin->query("INSERT INTO `tmdb_async`(`type`, `stream_id`, `status`) VALUES(2, " . intval($rSeriesID) . ", 0);");
                }
            }
        }
        if (isset(ipTV_lib::$request["c_bouquets"])) {
            scanBouquets();
        }
    }
    $_STATUS = 0;
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
                            <a href="./series.php">
                                <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                    <?= $_["back_to_series"] ?></li>
                            </a>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["mass_edit_series"] ?> <small id="selected_count"></small>
                    </h4>
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
                        <?= $_["mass_edit_of_series_was_successfully"] ?>
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
                        <form action="./series_mass.php" method="POST" id="stream_form">
                            <input type="hidden" name="series" id="series" value="" />
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#stream-selection" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-youtube-tv mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["series"] ?></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#stream-details" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="stream-selection">
                                        <div class="row">
                                            <div class="col-md-5 col-6">
                                                <input type="text" class="form-control" id="stream_search" value=""
                                                    placeholder="<?= $_["search_series"] ?>">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <select id="category_search" class="form-control" data-toggle="select2">
                                                    <option value="" selected><?= $_["all_categories"] ?>
                                                    </option>
                                                    <option value="-1"><?= $_["no_tmdb_match"] ?></option>
                                                    <?php foreach ($rCategories as $rCategory) { ?>
                                                        <option value="<?= $rCategory["id"] ?>" <?php if ((isset(ipTV_lib::$request["category"])) && (ipTV_lib::$request["category"] == $rCategory["id"])) {
                                                              echo " selected";
                                                          } ?>>
                                                            <?= $rCategory["category_name"] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 col-8">
                                                <select id="show_entries" class="form-control" data-toggle="select2">
                                                    <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                        <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                            echo " selected";
                                                        } ?> value="<?= $rShow ?>">
                                                            <?= $rShow ?></option>
                                                        <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1 col-2">
                                                <button type="button" class="btn btn-info waves-effect waves-light"
                                                    onClick="toggleStreams()">
                                                    <i class="mdi mdi-selection"></i>
                                                </button>
                                            </div>
                                            <table id="datatable-mass" class="table table-hover table-borderless mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-center"><?= $_["id"] ?></th>
                                                        <th><?= $_["series_name"] ?></th>
                                                        <th><?= $_["category"] ?></th>
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
                                                        <input type="checkbox" class="activate" data-name="category_id"
                                                            name="c_category_id">
                                                        <label></label>
                                                    </div>
                                                    <label class="col-md-3 col-form-label"
                                                        for="category_id"><?= $_["category_name"] ?></label>
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
                                                        <input type="checkbox" class="activate" data-name="bouquets"
                                                            name="c_bouquets">
                                                        <label></label>
                                                    </div>
                                                    <label class="col-md-3 col-form-label"
                                                        for="bouquets"><?= $_["select_bouquets"] ?></label>
                                                    <div class="col-md-8">
                                                        <select disabled name="bouquets[]" id="bouquets"
                                                            class="form-control select2-multiple" data-toggle="select2"
                                                            multiple="multiple" data-placeholder="<?= $_["choose"] ?>">
                                                            <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                <option value="<?= $rBouquet["id"] ?>">
                                                                    <?= $rBouquet["bouquet_name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <div class="col-md-1"></div>
                                                    <label class="col-md-3 col-form-label"
                                                        for="reprocess_tmdb"><?= $_["reprocess_tmdb_data"] ?></label>
                                                    <div class="col-md-2">
                                                        <input name="reprocess_tmdb" id="reprocess_tmdb" type="checkbox"
                                                            data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
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
                                                <input name="submit_series" type="submit" class="btn btn-primary"
                                                    value="<?= $_["edit_series"] ?>" />
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

    function getFilter() {
        return $("#filter").val();
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
            $("#series").val(JSON.stringify(window.rSelected));
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
                    d.id = "series_list",
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
        $('#filter').change(function () {
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