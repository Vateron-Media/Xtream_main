<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "security_center"))) {
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
                                            <?php if ($rPermissions["is_admin"]) { ?>
                                            <?php }
                                            if (!$detect->isMobile()) { ?>
                                                <a href="javascript:location.reload();">
                                                    <button type="button"
                                                        class="btn btn-dark waves-effect waves-light btn-sm">
                                                        <i class="mdi mdi-refresh"></i> Refresh
                                                    </button>
                                                </a>
                                            <?php } else { ?>
                                                <a href="javascript:location.reload();" onClick="toggleAuto();"
                                                    style="margin-right:10px;">
                                                    <button type="button"
                                                        class="btn btn-dark waves-effect waves-light btn-sm">
                                                        <i class="mdi mdi-refresh"></i> Refresh
                                                    </button>
                                                </a>
                                            <?php } ?>
                                        </li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Security Center</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <h5 class="page-title">
                        <p style="color:#bfbfbf">Restream Finder</p>
                    </h5>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" style="overflow-x:auto;">
                                    <table id="datatable"
                                        class="table table-bordered table-hover table-sm table-striped font-normal">
                                        <thead>
                                            <tr>
                                                <th class="text-center">User ID</th>
                                                <!--<th class="text-center">MAG Adress</th>-->
                                                <th class="text-center">Username</th>
                                                <!--<th class="text-center">Username</th>-->
                                                <!--<th class="text-center">Password</th>-->
                                                <th class="text-center">Channel</th>
                                                <th class="text-center">Max Connections</th>
                                                <th class="text-center">Active Connections</th>
                                                <th class="text-center">Total Active Connections</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (UIController::getSecurityCenter() as $rIP) {
                                                ?>
                                                <tr id="ip-<?= $rIP["id"] ?>">
                                                    <td class="text-center"><a
                                                            href="./user.php?id=<?= $rIP["id"] ?>"><?= $rIP["id"] ?></td>
                                                    <!--<td class="text-center"><?= $rIP["FROM_BASE64(mac)"] ?></td>-->
                                                    <td class="text-center"><?php
                                                    $MAG_or_M3U = $rIP["FROM_BASE64(mac)"];

                                                    // True because $MAG_or_M3U is empty
                                                    if (empty($MAG_or_M3U)) {
                                                        echo $rIP["username"];
                                                    }

                                                    // True because $MAG_or_M3U is set
                                                    if (isset($MAG_or_M3U)) {
                                                        echo $rIP["FROM_BASE64(mac)"];
                                                    }
                                                    ?></td>
                                                    <!--<td class="text-center"><?= $rIP["username"] ?></td>-->
                                                    <!--<td class="text-center"><?= $rIP["password"] ?></td>-->
                                                    <td class="text-center"><?= $rIP["stream_display_name"] ?></td>
                                                    <td class="text-center"><?= $rIP["max_connections"] ?></td>
                                                    <td class="text-center"><?= $rIP["active_connections"] ?></td>
                                                    <td class="text-center"><?= $rIP["total_active_connections"] ?></td>
                                                    <td class="text-center"><a
                                                            href="./user.php?id=<?= $rIP["id"] ?>"><button type="button"
                                                                class="btn btn-outline-danger waves-effect waves-light btn-xs"><i
                                                                    class="far fa-eye"></i></button></a></td>
                                                    <!--<td class="text-center"><?php
                                                    if ($rIP["is_restreamer"] > 0) {
                                                        echo '<i class="text-success fas fa-check fa-lg"></i>';
                                                    } else {
                                                        echo '<i class="text-danger fas fa-times fa-lg"></i>';
                                                    }
                                                    $rIP["is_restreamer"] ?></td>-->
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

                    </br>
                    <h5 class="page-title">
                        <p style="color:#bfbfbf">Check Leaked MAG and M3U Lines</p>
                    </h5>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" style="overflow-x:auto;">
                                    <table id="datatable2"
                                        class="table table-bordered table-hover table-sm table-striped font-normal">
                                        <thead>
                                            <tr>
                                                <th class="text-center">User ID</th>
                                                <!--<th class="text-center">MAG Adress</th>-->
                                                <th class="text-center">Username / Device</th>
                                                <!--<th class="text-center">Username</th>-->
                                                <!--<th class="text-center">Password</th>-->
                                                <th class="text-center">Containers</th>
                                                <th class="text-center">Flags</th>
                                                <th class="text-center">User IP's</th>
                                                <th class="text-center">Actions</th>
                                                <!--<th class="text-center">Is Restreamer</th>-->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (UIController::getLeakedLines() as $rIP) {
                                                ?>
                                                <tr id="ip-<?= $rIP["id"] ?>">
                                                    <td class="text-center"><a
                                                            href="./user.php?id=<?= $rIP["user_id"] ?>"><?= $rIP["user_id"] ?>
                                                    </td>
                                                    <!--<td class="text-center"><?= $rIP["FROM_BASE64(mac)"] ?></td>-->
                                                    <td class="text-center"><?php
                                                    $MAG_or_M3U = $rIP["FROM_BASE64(mac)"];

                                                    // True because $MAG_or_M3U is empty
                                                    if (empty($MAG_or_M3U)) {
                                                        echo $rIP["username"];
                                                    }

                                                    // True because $MAG_or_M3U is set
                                                    if (isset($MAG_or_M3U)) {
                                                        echo $rIP["FROM_BASE64(mac)"];
                                                    }
                                                    ?></td>
                                                    <!--<td class="text-center"><?= $rIP["username"] ?></td>-->
                                                    <!--<td class="text-center"><?= $rIP["password"] ?></td>-->
                                                    <td class="text-center"><?= $rIP["GROUP_CONCAT(DISTINCT container)"] ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= $rIP["GROUP_CONCAT(DISTINCT geoip_country_code)"] ?>
                                                    </td>
                                                    <td class="text-center"><?= $rIP["GROUP_CONCAT(DISTINCT user_ip)"] ?>
                                                    </td>
                                                    <td class="text-center"><a
                                                            href="./user.php?id=<?= $rIP["user_id"] ?>"><button
                                                                type="button"
                                                                class="btn btn-outline-danger waves-effect waves-light btn-xs"><i
                                                                    class="far fa-eye"></i></button></a></td>
                                                    <!--<td class="text-center"><?php
                                                    if ($rIP["is_restreamer"] > 0) {
                                                        echo '<i class="text-success fas fa-check fa-lg"></i>';
                                                    } else {
                                                        echo '<i class="text-danger fas fa-times fa-lg"></i>';
                                                    }
                                                    $rIP["is_restreamer"] ?></td>-->
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
                    if (confirm('Are you sure you want to delete this IP? This cannot be undone!') == false) {
                        return;
                    } else {
                        $.toast("The IP is being unblocked from each server...");
                        if (rType == "delete") {
                            $("#ip-" + rID).remove();
                        }
                        $.each($('.tooltip'), function (index, element) {
                            $(this).remove();
                        });
                        $('[data-toggle="tooltip"]').tooltip();
                    }
                }
                $.getJSON("./api.php?action=ip&sub=" + rType + "&ip=" + rID, function (data) {
                    if (data.result === true) {
                        if (rType == "delete") {
                            $.toast("IP successfully deleted.");
                        }
                    } else {
                        $.toast("An error occured while processing your request.");
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

            $(document).ready(function () {
                $("#datatable2").DataTable({
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
                $("#datatable2").css("width", "100%");
            });
        </script>

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>
        </body>

        </html>