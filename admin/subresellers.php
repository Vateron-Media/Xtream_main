<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "subresellers"))) { exit; }

$rMemberGroups = getMemberGroups();

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
                                    <li>
										<?php if (hasPermissions("adv", "mng_regusers")) { ?>
                                        <a href="reg_users.php">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-account-group"></i> Registered Users
                                            </button>
                                        </a>
										<?php }
										if (hasPermissions("adv", "subreseller")) { ?>
                                        <a href="subreseller_setup.php">
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> Setup Access
                                            </button>
                                        </a>
										<?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title">Subreseller Setup</h4>
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
                                            <th>Reseller Owner</th>
                                            <th>Subreseller</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (getSubresellerSetups() as $rItem) { ?>
                                        <tr id="setup-<?=$rItem["id"]?>">
                                            <td class="text-center"><?=$rItem["id"]?></td>
                                            <td><?=$rMemberGroups[$rItem["reseller"]]["group_name"]?></td>
                                            <td><?=$rMemberGroups[$rItem["subreseller"]]["group_name"]?></td>
                                            <td class="text-center">
												<?php if (hasPermissions("adv", "subreseller")) { ?>
                                                <div class="btn-group">
                                                    <a href="./subreseller_setup.php?id=<?=$rItem["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                    <button type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rItem["id"]?>, 'delete');"><i class="mdi mdi-close"></i></button>
												</div>
                                                <?php } else { echo "--"; } ?>
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
        <script src="assets/js/app.min.js"></script>

        <script>
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this setup? This cannot be undone!') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=subreseller_setup&sub=" + rType + "&id=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $("#setup-" + rID).remove();
                        $.toast("Setup successfully deleted.");
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
                bInfo: false,
                searching: false,
                paging: false,
                responsive: false
            });
            $("#datatable").css("width", "100%");
        });
        </script>
    </body>
</html>