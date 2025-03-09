<?php
include "functions.php";
if (!isset($_SESSION['hash'])) {
    header("Location: ./login.php");
    exit;
}
if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "rtmp"))) {
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
                    <?php if (UIController::hasPermissions("adv", "add_rtmp")) { ?>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li>
                                    <a href="rtmp_ip.php">
                                        <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                            <i class="mdi mdi-plus"></i> <?= $_["add_ip_address"] ?>
                                        </button>
                                    </a>
                                </li>
                            </ol>
                        </div>
                    <?php } ?>
                    <h4 class="page-title"><?= $_["rtmp_ip_addresses"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <table id="datatable" class="table table-hover dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center"><?= $_["id"] ?></th>
                                    <th class="text-center"><?= $_["ip_address"] ?></th>
                                    <th><?= $_["notes"] ?></th>
                                    <th class="text-center"><?= $_["actions"] ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (UIController::getRTMPIPs() as $rIP) {
                                    ?>
                                    <tr id="ip-<?= $rIP["id"] ?>">
                                        <td class="text-center"><?= $rIP["id"] ?></td>
                                        <td class="text-center"><?= $rIP["ip"] ?></td>
                                        <td><?= $rIP["notes"] ?></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <?php if (UIController::hasPermissions("adv", "add_rtmp")) { ?>
                                                    <a href="./rtmp_ip.php?id=<?= $rIP["id"] ?>"><button type="button"
                                                            class="btn btn-light waves-effect waves-light btn-xs"><i
                                                                class="mdi mdi-pencil-outline"></i></button></a>
                                                <?php } ?>
                                                <button type="button" class="btn btn-light waves-effect waves-light btn-xs"
                                                    onClick="api(<?= $rIP["id"] ?>, 'delete');"><i
                                                        class="mdi mdi-close"></i></button>
                                            </div>
                                        </td>
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
<script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/datatables/buttons.html5.min.js"></script>
<script src="assets/libs/datatables/buttons.flash.min.js"></script>
<script src="assets/libs/datatables/buttons.print.min.js"></script>
<script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
<script src="assets/libs/datatables/dataTables.select.min.js"></script>
<script src="assets/libs/pdfmake/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/vfs_fonts.js"></script>

<script>
    function api(rID, rType) {
        if (rType == "delete") {
            if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_ip"] ?>') == false) {
                return;
            }
        }
        $.getJSON("./api.php?action=rtmp_ip&sub=" + rType + "&ip=" + rID, function (data) {
            if (data.result === true) {
                if (rType == "delete") {
                    $("#ip-" + rID).remove();
                    $.toast("<?= $_["ip_successfully_deleted"] ?>");
                }
                $.each($('.tooltip'), function (index, element) {
                    $(this).remove();
                });
                $('[data-toggle="tooltip"]').tooltip();
            } else {
                $.toast("<?= $_["an_error_occured_while_processing_your_reques"] ?>");
            }
        });
    }

    $(document).ready(function () {
        $("#datatable").DataTable({
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            responsive: false
        });
        $("#datatable").css("width", "100%");
    });
</script>

<!-- App js-->
<script src="assets/js/app.min.js"></script>
</body>

</html>