<?php
include "functions.php";
if (!isset($_SESSION['hash'])) { header("Location: ./login.php"); exit; }
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "rtmp"))) { exit; }

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
							<?php if (hasPermissions("adv", "add_rtmp")) { ?>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="rtmp_ip.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> Add IP Address
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
							<?php } ?>
                            <h4 class="page-title">RTMP IP Addresses</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <table id="datatable" class="table dt-responsive nowrap">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">IP Address</th>
                                            <th>Notes</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (getRTMPIPs() as $rIP) {
                                        ?>
                                        <tr id="ip-<?=$rIP["id"]?>">
                                            <td class="text-center"><?=$rIP["id"]?></td>
                                            <td class="text-center"><?=$rIP["ip"]?></td>
                                            <td><?=$rIP["notes"]?></td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <?php if (hasPermissions("adv", "add_rtmp")) { ?>
                                                    <a href="./rtmp_ip.php?id=<?=$rIP["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                    <?php } ?>
                                                    <button type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rIP["id"]?>, 'delete');"><i class="mdi mdi-close"></i></button>
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
                if (confirm('Are you sure you want to delete this IP? This cannot be undone!') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=rtmp_ip&sub=" + rType + "&ip=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $("#ip-" + rID).remove();
                        $.toast("IP successfully deleted.");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip();
                } else {
                    $.toast("An error occured while processing your request.");
                }
            });
        }
        
        $(document).ready(function() {
            $("#datatable").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
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