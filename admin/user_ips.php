<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "connection_logs"))) {
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
                    <h4 class="page-title"><?= $_["line_ip_usage"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <div class="form-group row mb-4">
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="log_search" value=""
                                    placeholder="<?= $_["search_logs"] ?>">
                            </div>
                            <div class="col-md-3">
                                <select id="range" class="form-control" data-toggle="select2">
                                    <option value="604800"><?= $_["last_7_days"] ?></option>
                                    <option value="86400"><?= $_["last_24_hours"] ?></option>
                                    <option value="3600"><?= $_["last_hour"] ?></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="show_entries" class="form-control" data-toggle="select2">
                                    <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                        <option<?php if ($rSettings["default_entries"] == $rShow) {
                                            echo " selected";
                                        } ?>
                                            value="<?= $rShow ?>"><?= $rShow ?></option>
                                        <?php } ?>
                                </select>
                            </div>
                        </div>
                        <table id="datatable-activity" class="table table-hover dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th><?= $_["user_id"] ?></th>
                                    <th><?= $_["username"] ?></th>
                                    <th><?= $_["ip_count"] ?></th>
                                    <th><?= $_["actions"] ?></th>
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

<!-- Datatables init -->
<script>
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
        $('#range').on('change', function () {
            $("#datatable-activity").DataTable().ajax.reload(null, false);
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
                    d.id = "user_ips",
                        d.range = getRange()
                }
            },
            columnDefs: [{
                "className": "dt-center",
                "targets": [0, 2, 3]
            }],
            "order": [
                [2, "desc"]
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
    });
</script>

<!-- App js-->
<script src="assets/js/app.min.js"></script>
</body>

</html>