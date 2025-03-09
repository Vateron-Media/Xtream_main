<?php
include "session.php";
include "functions.php";
if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
    exit;
}
if (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "radio"))) {
    exit;
}

$rCategories = UIController::getCategories_admin("radio");

include "header.php";
?>

<div class="wrapper<?php if ($rPermissions["is_reseller"]) {
    echo " boxed-layout-ext";
} ?>">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li>
                                <a href="#" onClick="clearFilters();">
                                    <button type="button" class="btn btn-warning waves-effect waves-light btn-sm">
                                        <i class="mdi mdi-filter-remove"></i>
                                    </button>
                                </a>
                                <a href="#" onClick="changeZoom();">
                                    <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </a>
                                <?php if (!$detect->isMobile()) { ?>
                                    <a href="#" onClick="toggleAuto();">
                                        <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                            <span class="auto-text"><?= $_["auto_refresh"] ?></span>
                                        </button>
                                    </a>
                                <?php } else { ?>
                                    <a href="javascript:location.reload();" onClick="toggleAuto();">
                                        <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                            <?= $_["refresh"] ?>
                                        </button>
                                    </a>
                                <?php }
                                if (($rPermissions["is_admin"]) && (UIController::hasPermissions("adv", "add_radio"))) { ?>
                                    <a href="radio.php">
                                        <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                            <?= $_["add_station"] ?>
                                        </button>
                                    </a>
                                <?php } ?>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["radio_stations"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <form id="station_form">
                            <div class="form-group row mb-4">
                                <?php if ($rPermissions["is_reseller"]) { ?>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" id="station_search" value=""
                                            placeholder="<?= $_["search_stations"] ?>...">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="station_category_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_categories"] ?></option>
                                            <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?= $rCategory["id"] ?>" <?php if ((isset(CoreUtilities::$request["category"])) && (CoreUtilities::$request["category"] == $rCategory["id"])) {
                                                      echo " selected";
                                                  } ?>><?= $rCategory["category_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="station_server_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_servers"] ?></option>
                                            <?php foreach (UIController::getStreamingServers() as $rServer) { ?>
                                                <option value="<?= $rServer["id"] ?>" <?php if ((isset(CoreUtilities::$request["server"])) && (CoreUtilities::$request["server"] == $rServer["id"])) {
                                                      echo " selected";
                                                  } ?>>
                                                    <?= $rServer["server_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <label class="col-md-1 col-form-label text-center"
                                        for="station_show_entries"><?= $_["show"] ?></label>
                                    <div class="col-md-2">
                                        <select id="station_show_entries" class="form-control" data-toggle="select2">
                                            <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                    echo " selected";
                                                } ?>
                                                    value="<?= $rShow ?>"><?= $rShow ?></option>
                                                <?php } ?>
                                        </select>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="station_search" value=""
                                            placeholder="<?= $_["search_streams"] ?>...">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="station_server_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_servers"] ?></option>
                                            <?php foreach (UIController::getStreamingServers() as $rServer) { ?>
                                                <option value="<?= $rServer["id"] ?>" <?php if ((isset(CoreUtilities::$request["server"])) && (CoreUtilities::$request["server"] == $rServer["id"])) {
                                                      echo " selected";
                                                  } ?>>
                                                    <?= $rServer["server_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="station_category_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_categories"] ?></option>
                                            <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?= $rCategory["id"] ?>" <?php if ((isset(CoreUtilities::$request["category"])) && (CoreUtilities::$request["category"] == $rCategory["id"])) {
                                                      echo " selected";
                                                  } ?>><?= $rCategory["category_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select id="station_filter" class="form-control" data-toggle="select2">
                                            <option value="" <?php if (!isset(CoreUtilities::$request["filter"])) {
                                                echo " selected";
                                            } ?>><?= $_["no_filter"] ?></option>
                                            <option value="1" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 1)) {
                                                echo " selected";
                                            } ?>>
                                                <?= $_["online"] ?></option>
                                            <option value="2" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 2)) {
                                                echo " selected";
                                            } ?>>
                                                <?= $_["down"] ?></option>
                                            <option value="3" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 3)) {
                                                echo " selected";
                                            } ?>>
                                                <?= $_["stopped"] ?></option>
                                            <option value="4" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 4)) {
                                                echo " selected";
                                            } ?>>
                                                <?= $_["starting"] ?></option>
                                            <option value="5" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 5)) {
                                                echo " selected";
                                            } ?>>
                                                <?= $_["on_demand"] ?></option>
                                            <option value="6" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 6)) {
                                                echo " selected";
                                            } ?>>
                                                <?= $_["direct"] ?></option>
                                        </select>
                                    </div>
                                    <label class="col-md-1 col-form-label text-center"
                                        for="station_show_entries"><?= $_["show"] ?></label>
                                    <div class="col-md-1">
                                        <select id="station_show_entries" class="form-control" data-toggle="select2">
                                            <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                    echo " selected";
                                                } ?>
                                                    value="<?= $rShow ?>"><?= $rShow ?></option>
                                                <?php } ?>
                                        </select>
                                    </div>
                                <?php } ?>
                            </div>
                        </form>
                        <table id="datatable-streampage" class="table table-hover dt-responsive nowrap font-normal">
                            <thead>
                                <tr>
                                    <th class="text-center"><?= $_["id"] ?></th>
                                    <th><?= $_["name"] ?></th>
                                    <th><?= $_["source"] ?></th>
                                    <?php if ($rPermissions["is_admin"]) { ?>
                                        <th class="text-center"><?= $_["clients"] ?></th>
                                        <th class="text-center"><?= $_["uptime"] ?></th>
                                        <th class="text-center"><?= $_["actions"] ?></th>
                                    <?php } ?>
                                    <th class="text-center"><?= $_["stream_info"] ?></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div>
        <!-- end row-->
    </div> <!-- end container -->
</div>
<!-- end wrapper -->
<!-- Footer Start -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 copyright text-center"><?= UIController::getFooter() ?></div>
        </div>
    </div>
</footer>
<!-- end Footer -->

<script src="assets/js/vendor.min.js"></script>
<script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
<script src="assets/libs/select2/select2.min.js"></script>
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
<script src="assets/js/pages/form-remember.js"></script>
<script src="assets/js/app.min.js"></script>

<script>
    var autoRefresh = true;
    var rClearing = false;

    function toggleAuto() {
        if (autoRefresh == true) {
            autoRefresh = false;
            $(".auto-text").html("Manual Mode");
        } else {
            autoRefresh = true;
            $(".auto-text").html("Auto-Refresh");
        }
    }

    function api(rID, rServerID, rType) {
        if (rType == "delete") {
            if (confirm('Are you sure you want to delete this station?') == false) {
                return;
            }
        }
        $.getJSON("./api.php?action=stream&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function (data) {
            if (data.result == true) {
                if (rType == "start") {
                    $.toast("Station successfully started. It will take a minute or so before the station becomes available.");
                } else if (rType == "stop") {
                    $.toast("Station successfully stopped.");
                } else if (rType == "restart") {
                    $.toast("Station successfully restarted. It will take a minute or so before the station becomes available.");
                } else if (rType == "delete") {
                    $.toast("Station successfully deleted.");
                }
                $.each($('.tooltip'), function (index, element) {
                    $(this).remove();
                });
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            } else {
                $.toast("An error occured while processing your request.");
            }
        }).fail(function () {
            $.toast("An error occured while processing your request.");
        });
    }

    function reloadStreams() {
        if (autoRefresh == true) {
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-streampage").DataTable().ajax.reload(null, false);
        }
        setTimeout(reloadStreams, 5000);
    }

    function getCategory() {
        return $("#station_category_id").val();
    }

    function getFilter() {
        return $("#station_filter").val();
    }

    function getServer() {
        return $("#station_server_id").val();
    }

    function changeZoom() {
        if ($("#datatable-streampage").hasClass("font-large")) {
            $("#datatable-streampage").removeClass("font-large");
            $("#datatable-streampage").addClass("font-normal");
        } else if ($("#datatable-streampage").hasClass("font-normal")) {
            $("#datatable-streampage").removeClass("font-normal");
            $("#datatable-streampage").addClass("font-small");
        } else {
            $("#datatable-streampage").removeClass("font-small");
            $("#datatable-streampage").addClass("font-large");
        }
        $("#datatable-streampage").draw();
    }

    function clearFilters() {
        window.rClearing = true;
        $("#station_search").val("").trigger('change');
        $('#station_filter').val("").trigger('change');
        $('#station_server_id').val("").trigger('change');
        $('#station_category_id').val("").trigger('change');
        $('#station_show_entries').val("<?= $rSettings["default_entries"] ?: 10 ?>").trigger('change');
        window.rClearing = false;
        $('#datatable-streampage').DataTable().search($("#station_search").val());
        $('#datatable-streampage').DataTable().page.len($('#station_show_entries').val());
        $("#datatable-streampage").DataTable().page(0).draw('page');
        $('[data-toggle="tooltip"]').tooltip("hide");
        $("#datatable-streampage").DataTable().ajax.reload(null, false);
    }
    $(document).ready(function () {
        $(window).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });
        formCache.init();
        <?php if (!isset(CoreUtilities::$request["filter"])) { ?>
            formCache.fetch();
        <?php } ?>

        $('select').select2({
            width: '100%'
        });
        $("#datatable-streampage").DataTable({
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                $('[data-toggle="tooltip"]').tooltip();
            },
            createdRow: function (row, data, index) {
                $(row).addClass('stream-' + data[0]);
            },
            responsive: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "./table_search.php",
                "data": function (d) {
                    d.id = "radios",
                        d.category = getCategory();
                    <?php if ($rPermissions["is_admin"]) { ?>
                        d.filter = getFilter();
                    <?php } else { ?>
                        d.filter = 1;
                    <?php } ?>
                    d.server = getServer();
                }
            },
            columnDefs: [
                <?php if ($rPermissions["is_admin"]) {
                    ?> {
                        "className": "dt-center",
                        "targets": [0, 3, 4, 5, 6]
                    },
                    {
                        "orderable": false,
                        "targets": [5]
                    }
                            <?php } else {
                    ?> {
                        "className": "dt-center",
                        "targets": [0, 3]
                    }
                            <?php } ?>
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: <?= $rSettings["default_entries"] ?: 10 ?>,
            lengthMenu: [10, 25, 50, 250, 500, 1000],
            stateSave: true
        });
        $("#datatable-streampage").css("width", "100%");
        $('#station_search').keyup(function () {
            if (!window.rClearing) {
                $('#datatable-streampage').DataTable().search($(this).val()).draw();
            }
        })
        $('#station_show_entries').change(function () {
            if (!window.rClearing) {
                $('#datatable-streampage').DataTable().page.len($(this).val()).draw();
            }
        })
        $('#station_category_id').change(function () {
            if (!window.rClearing) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            }
        })
        $('#station_server_id').change(function () {
            if (!window.rClearing) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            }
        })
        $('#station_filter').change(function () {
            if (!window.rClearing) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            }
        })
        <?php if (!$detect->isMobile()) { ?>
            setTimeout(reloadStreams, 5000);
        <?php }
        if (!$rSettings["auto_refresh"]) { ?>
            toggleAuto();
        <?php } ?>
        if ($('#station_search').val().length > 0) {
            $('#datatable-streampage').DataTable().search($('#station_search').val()).draw();
        }
    });

    $(window).bind('beforeunload', function () {
        formCache.save();
    });
</script>
</body>

</html>