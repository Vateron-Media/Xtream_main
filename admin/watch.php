<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "folder_watch"))) { exit; }

if (isset($_GET["kill"])) {
    if (isset($rAdminSettings["watch_pid"])) {
        exec("pkill -9 ".$rAdminSettings["watch_pid"]);
    }
}

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
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
										<?php if (hasPermissions("adv", "folder_watch_settings")) { ?>
                                        <a href="settings_watch.php">
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                Settings
                                            </button>
                                        </a>
										<?php }
										if (hasPermissions("adv", "folder_watch_output")) { ?>
                                        <a href="watch_output.php">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                Watch Output
                                            </button>
                                        </a>
										<?php } ?>
                                        <a href="watch.php?kill=1">
                                            <button type="button" class="btn btn-danger waves-effect waves-light btn-sm" data-toggle="tooltip" data-placement="top" title="" data-original-title="Kill Process">
                                                <i class="mdi mdi-hammer"></i>
                                            </button>
                                        </a>
										<?php if (hasPermissions("adv", "folder_watch_add")) { ?>
                                        <a href="watch_add.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add Folder">
                                                <i class="mdi mdi-plus"></i>
                                            </button>
                                        </a>
										<?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title">Folder Watch</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 

                <div class="row">
                    <div class="col-12">
                        <?php if (isset($_GET["kill"])) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Folder watch process has been killed.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <table id="datatable" class="table dt-responsive nowrap">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th>Type</th>
                                            <th>Server Name</th>
                                            <th>Directory</th>
                                            <th class="text-center">Last Run</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (getWatchFolders() as $rFolder) {
                                        if ($rFolder["last_run"] > 0) {
                                            $rDate = date("Y-m-d H:i:s", $rFolder["last_run"]);
                                        } else {
                                            $rDate = "Never";
                                        }
                                        ?>
                                        <tr id="folder-<?=$rFolder["id"]?>">
                                            <td class="text-center"><?=$rFolder["id"]?></td>
                                            <td><?=Array("movie" => "Movies", "series" => "Series")[$rFolder["type"]]?></td>
                                            <td><?=$rServers[$rFolder["server_id"]]["server_name"]?></td>
                                            <td><?=$rFolder["directory"]?></td>
                                            <td class="text-center"><?=$rDate?></td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="./watch_add.php?id=<?=$rFolder["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                    <button type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rFolder["id"]?>, 'delete');"><i class="mdi mdi-close"></i></button>
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
        <script src="assets/js/app.min.js"></script>

        <script>
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this profile? This cannot be undone!') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=folder&sub=" + rType + "&folder_id=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $("#folder-" + rID).remove();
                        $.toast("Folder successfully deleted.");
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
    </body>
</html>