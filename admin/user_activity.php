<?php
include "functions.php";
if (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "connection_logs"))) {
    exit;
}
if (($rPermissions["is_reseller"]) && (!$rPermissions["reseller_client_connection_logs"])) {
    exit;
}

if (!isset($_SESSION['hash'])) {
    header("Location: ./login.php");
    exit;
}
include "header.php";
?>
<div class="wrapper">
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
                                <?php if ($rPermissions["is_admin"]) { ?>
                                    <button type="button"
                                        class="btn btn-info waves-effect waves-light btn-sm btn-clear-logs">
                                        <i class="mdi mdi-minus"></i> <?= $_["clear_logs"] ?>
                                    </button>
                                <?php } ?>
                                <a href="javascript:location.reload();" onClick="toggleAuto();"
                                    style="margin-right:10px;">
                                    <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                        <i class="mdi mdi-refresh"></i> <?= $_["refresh"] ?>
                                    </button>
                                </a>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["activity_logs"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <form id="user_activity_search">
                            <div class="form-group row mb-4">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="act_search" value=""
                                        placeholder="<?= $_["search_logs"] ?>">
                                </div>
                                <label class="col-md-1 col-form-label text-center"
                                    for="act_filter"><?= $_["filter"] ?></label>
                                <div class="col-md-3">
                                    <select id="act_filter" class="form-control" data-toggle="select2">
                                        <option value="" selected><?= $_["all_servers"] ?></option>
                                        <?php foreach (UIController::getStreamingServers() as $rServer) { ?>
                                            <option value="<?= $rServer["id"] ?>"><?= $rServer["server_name"] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-md-1 col-form-label text-center"
                                    for="act_range"><?= $_["dates"] ?></label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control text-center date" id="act_range" name="range"
                                        data-toggle="date-picker" data-single-date-picker="true">
                                </div>
                                <label class="col-md-1 col-form-label text-center"
                                    for="act_show_entries"><?= $_["show"] ?></label>
                                <div class="col-md-1">
                                    <select id="act_show_entries" class="form-control" data-toggle="select2">
                                        <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                            <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                echo " selected";
                                            } ?> value="<?= $rShow ?>"><?= $rShow ?></option>
                                            <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <table id="datatable-activity" class="table table-hover dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center"><?= $_["id"] ?></th>
                                    <th><?= $_["username"] ?></th>
                                    <th><?= $_["stream"] ?></th>
                                    <th><?= $_["server"] ?></th>
                                    <th><?= $_["isp"] ?></th>
                                    <th class="text-center"><?= $_["start"] ?></th>
                                    <th class="text-center"><?= $_["stop"] ?></th>
                                    <th class="text-center"><?= $_["ip"] ?></th>
                                    <th class="text-center"><?= $_["country"] ?></th>
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
<?php if ($rPermissions["is_admin"]) { ?>
    <div class="modal fade bs-logs-modal-center" tabindex="-1" role="dialog" aria-labelledby="clearLogsLabel"
        aria-hidden="true" style="display: none;" data-id="">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="clearLogsLabel"><?= $_["clear_logs"] ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group row mb-4">
                        <label class="col-md-4 col-form-label" for="range_clear"><?= $_["date_range"] ?></label>
                        <div class="col-md-4">
                            <input type="text" class="form-control text-center date" id="range_clear_from"
                                name="range_clear_from" data-toggle="date-picker" data-single-date-picker="true"
                                autocomplete="off" placeholder="<?= $_["from"] ?>">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control text-center date" id="range_clear_to"
                                name="range_clear_to" data-toggle="date-picker" data-single-date-picker="true"
                                autocomplete="off" placeholder="<?= $_["to"] ?>">
                        </div>
                    </div>
                    <div class="text-center">
                        <input id="clear_logs" type="submit" class="btn btn-primary" value="<?= $_["clear"] ?>"
                            style="width:100%" />
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
<?php } ?>
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
<script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
<script src="assets/libs/select2/select2.min.js"></script>
<script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/datatables/buttons.html5.min.js"></script>
<script src="assets/libs/datatables/buttons.flash.min.js"></script>
<script src="assets/libs/datatables/buttons.print.min.js"></script>
<script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
<script src="assets/libs/datatables/dataTables.select.min.js"></script>
<script src="assets/libs/moment/moment.min.js"></script>
<script src="assets/libs/daterangepicker/daterangepicker.js"></script>
<script src="assets/js/pages/form-remember.js"></script>
<script src="assets/js/app.min.js"></script>

<!-- Datatables init -->
<script>
    var rClearing = false;

    function getServer() {
        return $("#act_filter").val();
    }

    function getRange() {
        return $("#act_range").val();
    }

    function clearFilters() {
        window.rClearing = true;
        $("#act_search").val("").trigger('change');
        $('#act_filter').val("").trigger('change');
        $('#act_range').val("").trigger('change');
        $('#act_show_entries').val("<?= $rSettings["default_entries"] ?: 10 ?>").trigger('change');
        window.rClearing = false;
        $('#datatable-activity').DataTable().search($("#act_search").val());
        $('#datatable-activity').DataTable().page.len($('#act_show_entries').val());
        $("#datatable-activity").DataTable().page(0).draw('page');
        $("#datatable-activity").DataTable().ajax.reload(null, false);
    }
    $(document).ready(function () {
        $(window).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });
        $('#act_range').daterangepicker({
            singleDatePicker: false,
            showDropdowns: true,
            locale: {
                format: 'YYYY-MM-DD'
            },
            autoUpdateInput: false
        }).val("");
        $('#act_range').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            if (!window.rClearing) {
                $("#datatable-activity").DataTable().ajax.reload(null, false);
            }
        });
        $('#act_range').on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
            if (!window.rClearing) {
                $("#datatable-activity").DataTable().ajax.reload(null, false);
            }
        });
        $('#act_range').on('change', function () {
            if (!window.rClearing) {
                $("#datatable-activity").DataTable().ajax.reload(null, false);
            }
        });
        <?php if ($rPermissions["is_admin"]) { ?>
            $('#range_clear_to').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                autoUpdateInput: false
            }).val("");
            $('#range_clear_from').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                autoUpdateInput: false
            }).val("");
            $('#range_clear_from').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            $('#range_clear_from').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
            $('#range_clear_to').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            $('#range_clear_to').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
            $(".btn-clear-logs").click(function () {
                $(".bs-logs-modal-center").modal("show");
            });
            $("#clear_logs").click(function () {
                if (confirm('<?= $_["are_you_sure_you_want_to_clear_logs"] ?>') == false) {
                    return;
                }
                $(".bs-logs-modal-center").modal("hide");
                $.getJSON("./api.php?action=clear_logs&type=user_activity&from=" + encodeURIComponent($("#range_clear_from").val()) + "&to=" + encodeURIComponent($("#range_clear_to").val()), function (data) {
                    $.toast("<?= $_["logs_have_been_cleared"] ?>");
                    $("#datatable-activity").DataTable().ajax.reload(null, false);
                });
            });
        <?php } ?>
        formCache.init();
        formCache.fetch();
        $('select').select2({
            width: '100%'
        });
        $("#datatable-activity").DataTable({
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                },
                infoFiltered: ""
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                $('[data-toggle="tooltip"]').tooltip();
            },
            responsive: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "./table_search.php",
                "data": function (d) {
                    d.id = "user_activity",
                        d.range = getRange(),
                        d.server = getServer()
                }
            },
            columnDefs: [{
                "className": "dt-center",
                "targets": [0, 5, 6, 7, 8]
            }],
            "order": [
                [0, "desc"]
            ],
            pageLength: <?= $rSettings["default_entries"] ?: 10 ?>,
            stateSave: true
        });
        $("#datatable-activity").css("width", "100%");
        $('#act_search').keyup(function () {
            if (!window.rClearing) {
                $('#datatable-activity').DataTable().search($(this).val()).draw();
            }
        })
        $('#act_show_entries').change(function () {
            if (!window.rClearing) {
                $('#datatable-activity').DataTable().page.len($(this).val()).draw();
            }
        })
        $('#act_filter').change(function () {
            if (!window.rClearing) {
                $("#datatable-activity").DataTable().ajax.reload(null, false);
            }
        })
        <?php if (isset(CoreUtilities::$request["search"])) { ?>
            $("#act_search").val("<?= str_replace('"', '\"', CoreUtilities::$request["search"]) ?>").trigger('change');
        <?php }
        if (isset(CoreUtilities::$request["dates"])) { ?>
            $("#act_range").val("<?= str_replace('"', '\"', CoreUtilities::$request["dates"]) ?>").trigger('change');
        <?php } ?>
        if ($('#act_search').val().length > 0) {
            $('#datatable-activity').DataTable().search($('#act_search').val()).draw();
        }
        if ($('#act_range').val().length > 0) {
            $("#datatable-activity").DataTable().ajax.reload(null, false);
        }
    });

    $(window).bind('beforeunload', function () {
        formCache.save();
    });
</script>
</body>

</html>