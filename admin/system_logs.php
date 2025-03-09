<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "system_logs"))) {
    exit;
}

include "header.php";
?>

<div class="wrapper boxed-layout-ext">
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
                    <h4 class="page-title"><?= $_["system_logs"] ?> </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <div class="form-group row mb-4">
                        </div>
                        <table id="datatable" class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center"><?= $_["id"] ?> </th>
                                    <th class="text-center"><?= $_["server_id"] ?> </th>
                                    <th class="text-center"><?= $_["type"] ?> </th>
                                    <th class="text-center"><?= $_["message"] ?> </th>
                                    <th class="text-center"><?= $_["username"] ?> </th>
                                    <th class="text-center"><?= $_["ip"] ?> </th>
                                    <th class="text-center"><?= $_["date"] ?> </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (UIController::getSystemLogs() as $rPlog) {
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $rPlog["id"] ?> </td>
                                        <td class="text-center"><?= $rPlog["server_id"] ?> </td>
                                        <td class="text-center"><?= $rPlog["type"] ?> </td>
                                        <!-- <td><?= (strlen($rPlog["error"]) > 130 ? substr($rPlog["error"], 0, 130) . "..." : $rPlog["error"]) ?> </td> -->
                                        <td><?= $rPlog["error"] ?> </td>
                                        <td class="text-center"><?= $rPlog["username"] ?> </td>
                                        <td class="text-center"><?= $rPlog["ip"] ?> </td>

                                        <td class="text-center"><?= date("Y-m-d H:i:s", $rPlog["date"]) ?> </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
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
                <h4 class="modal-title" id="clearLogsLabel"><?= $_["clear_logs"] ?> </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group row mb-4">
                    <label class="col-md-4 col-form-label" for="range_clear"><?= $_["date_range"] ?>
                    </label>
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
                    <input id="clear_logs" type="submit" class="btn btn-primary" value="Clear" style="width:100%" />
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

<!-- Datatables init -->
<script>
    function getServer() {
        return $("#server").val();
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
            if (confirm('<?= $_["are_you_sure_you_want_to_clear"] ?>') == false) {
                return;
            }
            $(".bs-logs-modal-center").modal("hide");
            $.getJSON("./api.php?action=clear_logs&type=mysql_syslog&from=" + encodeURIComponent($("#range_clear_from").val()) + "&to=" + encodeURIComponent($("#range_clear_to").val()), function (data) {
                $.toast("Logs have been cleared.");
                $("#datatable-activity").DataTable().ajax.reload(null, false);
            });
        });
        $("#datatable").DataTable({
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            columnDefs: [{
                "className": "dt-center",
                "targets": [0, 2]
            },
            {
                "orderable": false,
                "targets": [1, 2]
            },
            {
                "visible": false,
                "targets": []
            }
            ],
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                $('[data-toggle="tooltip"]').tooltip();
            },
            responsive: false
        });
        $("#datatable").css("width", "100%");
    });
</script>
</body>

</html>