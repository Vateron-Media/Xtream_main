<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "reg_userlog"))) {
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
                                            <button type="button"
                                                class="btn btn-info waves-effect waves-light btn-sm btn-clear-logs">
                                                <i class="mdi mdi-minus"></i> <?= $_["clear_logs"] ?>
                                            </button>
                                        </li>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?= $_["reseller_logs"] ?></h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" style="overflow-x:auto;">
                                    <div class="form-group row mb-4">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="log_search" value=""
                                                placeholder="<?= $_["search_logs"] ?>">
                                        </div>
                                        <label class="col-md-1 col-form-label text-center"
                                            for="reseller"><?= $_["reseller"] ?></label>
                                        <div class="col-md-3">
                                            <select id="reseller" class="form-control" data-toggle="select2">
                                                <option value="" selected><?= $_["all_resellers"] ?></option>
                                                <?php foreach (UIController::getRegisteredUsers() as $rReseller) { ?>
                                                        <option value="<?= $rReseller["id"] ?>"><?= $rReseller["username"] ?>
                                                        </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center"
                                            for="range"><?= $_["dates"] ?></label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control text-center date" id="range"
                                                name="range" data-toggle="date-picker" data-single-date-picker="true"
                                                autocomplete="off" placeholder="<?= $_["all_dates"] ?>">
                                        </div>
                                        <label class="col-md-1 col-form-label text-center"
                                            for="show_entries"><?= $_["show"] ?></label>
                                        <div class="col-md-1">
                                            <select id="show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                        <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                            echo " selected";
                                                        } ?> value="<?= $rShow ?>"><?= $rShow ?></option>
                                                    <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <table id="datatable-activity" class="table table-hover dt-responsive nowrap">
                                        <thead>
                                            <tr>
                                                <th class="text-center"><?= $_["id"] ?></th>
                                                <th><?= $_["reseller"] ?></th>
                                                <th><?= $_["user_device"] ?></th>
                                                <th><?= $_["action"] ?></th>
                                                <th class="text-center"><?= $_["date"] ?></th>
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
            <script src="assets/js/app.min.js"></script>

            <script>
                function getReseller() {
                    return $("#reseller").val();
                }

                function getRange() {
                    return $("#range").val();
                }

                $(document).ready(function () {
                    $(window).keypress(function (event) {
                        if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                    });
                    $('select').select2({
                        width: '100%'
                    });
                    $('#range').daterangepicker({
                        singleDatePicker: false,
                        showDropdowns: true,
                        locale: {
                            format: 'YYYY-MM-DD'
                        },
                        autoUpdateInput: false
                    }).val("");
                    $('#range').on('apply.daterangepicker', function (ev, picker) {
                        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                        $("#datatable-activity").DataTable().ajax.reload(null, false);
                    });
                    $('#range').on('cancel.daterangepicker', function (ev, picker) {
                        $(this).val('');
                        $("#datatable-activity").DataTable().ajax.reload(null, false);
                    });
                    $('#range').on('change', function () {
                        $("#datatable-activity").DataTable().ajax.reload(null, false);
                    });
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
                        if (confirm('<?= $_["are_you_sure_you_want_to_clear_logs_for_this_period"] ?>') == false) {
                            return;
                        }
                        $(".bs-logs-modal-center").modal("hide");
                        $.getJSON("./api.php?action=clear_logs&type=reg_userlog&from=" + encodeURIComponent($("#range_clear_from").val()) + "&to=" + encodeURIComponent($("#range_clear_to").val()), function (data) {
                            $.toast("Logs have been cleared.");
                            $("#datatable-activity").DataTable().ajax.reload(null, false);
                        });
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
                                d.id = "reg_user_logs",
                                    d.range = getRange(),
                                    d.reseller = getReseller()
                            }
                        },
                        columnDefs: [{
                            "className": "dt-center",
                            "targets": [0, 4]
                        }],
                        "order": [
                            [0, "desc"]
                        ],
                        pageLength: <?= $rSettings["default_entries"] ?: 10 ?>
                    });
                    $("#datatable-activity").css("width", "100%");
                    $('#log_search').keyup(function () {
                        $('#datatable-activity').DataTable().search($(this).val()).draw();
                    })
                    $('#show_entries').change(function () {
                        $('#datatable-activity').DataTable().page.len($(this).val()).draw();
                    })
                    $('#reseller').change(function () {
                        $("#datatable-activity").DataTable().ajax.reload(null, false);
                    })
                });
            </script>
            </body>

            </html>