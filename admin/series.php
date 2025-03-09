<?php
include "session.php";
include "functions.php";
if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
    exit;
}
if (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "series"))) {
    exit;
}

$rCategories = UIController::getCategories_admin("series");

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
                                            <i class="mdi mdi-refresh"></i> <span
                                                class="auto-text"><?= $_["auto_refresh"] ?></span>
                                        </button>
                                    </a>
                                <?php } else { ?>
                                    <a href="javascript:location.reload();" onClick="toggleAuto();">
                                        <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                            <i class="mdi mdi-refresh"></i> <?= $_["refresh"] ?>
                                        </button>
                                    </a>
                                <?php }
                                if (($rPermissions["is_admin"]) && (UIController::hasPermissions("adv", "add_series"))) { ?>
                                    <a href="serie.php">
                                        <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                            <i class="mdi mdi-plus"></i> <?= $_["add_series"] ?>
                                        </button>
                                    </a>
                                <?php } ?>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["series"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <form id="series_form">
                            <div class="form-group row mb-4">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="series_search" value=""
                                        placeholder="<?= $_["search_series"] ?>">
                                </div>
                                <div class="col-md-3">
                                    <select id="series_category_id" class="form-control" data-toggle="select2">
                                        <option value="" selected><?= $_["all_categories"] ?></option>
                                        <option value="-1"><?= $_["no_tmdb_match"] ?></option>
                                        <?php foreach ($rCategories as $rCategory) { ?>
                                            <option value="<?= $rCategory["id"] ?>">
                                                <?= $rCategory["category_name"] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-md-1 col-form-label text-center"
                                    for="series_show_entries"><?= $_["show"] ?></label>
                                <div class="col-md-2">
                                    <select id="series_show_entries" class="form-control" data-toggle="select2">
                                        <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                            <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                echo $_["selected"];
                                            } ?> value="<?= $rShow ?>"><?= $rShow ?>
                                                </option>
                                            <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <table id="datatable-streampage" class="table table-hover dt-responsive nowrap font-normal">
                            <thead>
                                <tr>
                                    <th class="text-center"><?= $_["id"] ?></th>
                                    <th><?= $_["name"] ?></th>
                                    <th><?= $_["category"] ?></th>
                                    <th class="text-center"><?= $_["seasons"] ?></th>
                                    <th class="text-center"><?= $_["episodes"] ?></th>
                                    <th class="text-center"><?= $_["first_aired"] ?></th>
                                    <?php if ($rPermissions["is_admin"]) { ?>
                                        <th class="text-center"><?= $_["last_updated"] ?></th>
                                        <th class="text-center"><?= $_["actions"] ?></th>
                                    <?php } ?>
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
            $(".auto-text").html("<?= $_["manual_mode"] ?>");
        } else {
            autoRefresh = true;
            $(".auto-text").html("<?= $_["auto_refresh"] ?>");
        }
    }
    <?php if ($rPermissions["is_admin"]) { ?>

        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_series"] ?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=series&sub=" + rType + "&series_id=" + rID, function (data) {
                if (data.result == true) {
                    if (rType == "delete") {
                        $.toast("<?= $_["series_successfully_deleted"] ?>");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload(null, false);
                } else {
                    $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
                }
            }).fail(function () {
                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
            });
        }
    <?php } ?>

    function reloadStreams() {
        if (autoRefresh == true) {
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-streampage").DataTable().ajax.reload(null, false);
        }
        setTimeout(reloadStreams, 5000);
    }

    function getCategory() {
        return $("#series_category_id").val();
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
        $("#datatable-streampage").DataTable().draw();
    }

    function clearFilters() {
        window.rClearing = true;
        $("#series_search").val("").trigger('change');
        $('#series_category_id').val("").trigger('change');
        $('#series_show_entries').val("<?= $rSettings["default_entries"] ?: 10 ?>").trigger('change');
        window.rClearing = false;
        $('#datatable-streampage').DataTable().search($("#series_search").val());
        $('#datatable-streampage').DataTable().page.len($('#series_show_entries').val());
        $("#datatable-streampage").DataTable().page(0).draw('page');
        $('[data-toggle="tooltip"]').tooltip("hide");
        $("#datatable-streampage").DataTable().ajax.reload(null, false);
    }
    $(document).ready(function () {
        $(window).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });
        formCache.init();
        formCache.fetch();

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
                    d.id = "series";
                    d.category = getCategory();
                }
            },
            columnDefs: [
                <?php if ($rPermissions["is_reseller"]) { ?> {
                        "className": "dt-center",
                        "targets": [0, 3, 4, 5]
                    },
                <?php } else { ?> {
                        "className": "dt-center",
                        "targets": [0, 3, 4, 5, 6, 7]
                    },
                    {
                        "orderable": false,
                        "targets": [7]
                    }
                                <?php } ?>
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: <?= $rSettings["default_entries"] ?: 10 ?>,
            stateSave: true
        });
        $("#datatable-streampage").css("width", "100%");
        $('#series_search').keyup(function () {
            if (!window.rClearing) {
                $('#datatable-streampage').DataTable().search($(this).val()).draw();
            }
        })
        $('#series_show_entries').change(function () {
            if (!window.rClearing) {
                $('#datatable-streampage').DataTable().page.len($(this).val()).draw();
            }
        })
        $('#series_category_id').change(function () {
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
        if ($('#series_search').val().length > 0) {
            $('#datatable-streampage').DataTable().search($('#series_search').val()).draw();
        }
    });

    $(window).bind('beforeunload', function () {
        formCache.save();
    });
</script>
</body>

</html>