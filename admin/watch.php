<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!UIController::hasPermissions("adv", "folder_watch"))) {
    exit;
}

if (isset(CoreUtilities::$request["kill"])) {
    if (isset($rSettings["watch_pid"])) {
        exec("pkill -9 " . $rSettings["watch_pid"]);
    }
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
                                <?php if (UIController::hasPermissions("adv", "folder_watch_settings")) { ?>
                                    <a href="settings_watch.php">
                                        <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                            <?= $_["settings"] ?>
                                        </button>
                                    </a>
                                <?php }
                                if (UIController::hasPermissions("adv", "folder_watch_output")) { ?>
                                    <a href="watch_output.php">
                                        <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                            <?= $_["watch_output"] ?>
                                        </button>
                                    </a>
                                <?php } ?>
                                <a href="watch.php?kill=1">
                                    <button type="button" class="btn btn-danger waves-effect waves-light btn-sm"
                                        data-toggle="tooltip" data-placement="top" title=""
                                        data-original-title="<?= $_["kill_process"] ?>">
                                        <i class="mdi mdi-hammer"></i>
                                    </button>
                                </a>
                                <?php if (UIController::hasPermissions("adv", "folder_watch_add")) { ?>
                                    <a href="watch_add.php">
                                        <button type="button" class="btn btn-success waves-effect waves-light btn-sm"
                                            data-toggle="tooltip" data-placement="top" title=""
                                            data-original-title="<?= $_["add_folder"] ?>">
                                            <i class="mdi mdi-plus"></i>
                                        </button>
                                    </a>
                                <?php } ?>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["folder_watch"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <?php if (isset(CoreUtilities::$request["kill"])) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["folder_watch_process"] ?>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <table id="datatable" class="table table-hover dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center"><?= $_["id"] ?></th>
                                    <th><?= $_["type"] ?></th>
                                    <th><?= $_["server_name"] ?></th>
                                    <th><?= $_["directory"] ?></th>
                                    <th class="text-center"><?= $_["last_run"] ?></th>
                                    <th class="text-center"><?= $_["actions"] ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (UIController::getWatchFolders() as $rFolder) {
                                    if ($rFolder["last_run"] > 0) {
                                        $rDate = date("Y-m-d H:i:s", $rFolder["last_run"]);
                                    } else {
                                        $rDate = "Never";
                                    }
                                    ?>
                                    <tr id="folder-<?= $rFolder["id"] ?>">
                                        <td class="text-center"><?= $rFolder["id"] ?></td>
                                        <td><?= array("movie" => "Movies", "series" => "Series")[$rFolder["type"]] ?>
                                        </td>
                                        <td><?= $rServers[$rFolder["server_id"]]["server_name"] ?></td>
                                        <td><?= $rFolder["directory"] ?></td>
                                        <td class="text-center"><?= $rDate ?></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="./watch_add.php?id=<?= $rFolder["id"] ?>"><button type="button"
                                                        class="btn btn-light waves-effect waves-light btn-xs"><i
                                                            class="mdi mdi-pencil-outline"></i></button></a>
                                                <button type="button" class="btn btn-light waves-effect waves-light btn-xs"
                                                    onClick="api(<?= $rFolder["id"] ?>, 'delete');"><i
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
<script src="assets/js/app.min.js"></script>

<script>
    function api(rID, rType) {
        if (rType == "delete") {
            if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_profile"] ?>') == false) {
                return;
            }
        }
        $.getJSON("./api.php?action=folder&sub=" + rType + "&folder_id=" + rID, function (data) {
            if (data.result === true) {
                if (rType == "delete") {
                    $("#folder-" + rID).remove();
                    $.toast("<?= $_["folder_successfully_deleted"] ?>");
                }
                $.each($('.tooltip'), function (index, element) {
                    $(this).remove();
                });
                $('[data-toggle="tooltip"]').tooltip();
            } else {
                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
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
            pageLength: 50,
            lengthMenu: [10, 25, 50, 250, 500, 1000],
            responsive: false,
            stateSave: true
        });
        $("#datatable").css("width", "100%");
    });
</script>
</body>

</html>