<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "servers"))) {
    exit;
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) { ?>
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
            <?php } else { ?>
                <div class="wrapper">
                    <div class="container-fluid">
                    <?php } ?>
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="javascript:location.reload();">
                                                <button type="button"
                                                    class="btn btn-dark waves-effect waves-light btn-sm">
                                                    <i class="mdi mdi-refresh"></i> <?= $_["refresh"] ?>
                                                </button>
                                            </a>
                                            <?php if (hasPermissions("adv", "add_server")) { ?>
                                                <a href="server.php">
                                                    <button type="button"
                                                        class="btn btn-success waves-effect waves-light btn-sm">
                                                        <i class="mdi mdi-plus"></i> <?= $_["add_server"] ?>
                                                    </button>
                                                </a>
                                                <a href="server_install.php">
                                                    <button type="button"
                                                        class="btn btn-info waves-effect waves-light btn-sm">
                                                        <i class="mdi mdi-creation"></i> <?= $_["install_lb"] ?>
                                                    </button>
                                                </a>
                                            <?php } ?>
                                        </li>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?= $_["servers"] ?> </h4>
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
                                                <th class="text-center"><?= $_["server_name"] ?> </th>
                                                <th class="text-center"><?= $_["status"] ?></th>
                                                <th class="text-center"><?= $_["latency"] ?></th>
                                                <th class="text-center"><?= $_["domaine_name"] ?> </th>
                                                <th class="text-center"><?= $_["server_ip"] ?> </th>
                                                <th class="text-center"><?= $_["client_slots"] ?></th>
                                                <th class="text-center"><?= $_["cpu_%"] ?></th>
                                                <th class="text-center"><?= $_["mem_%"] ?></th>
                                                <th class="text-center"><?= $_["actions"] ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rServers as $rServer) {
                                                if (((time() - $rServer["last_check_ago"]) > 360) and ($rServer["can_delete"] == 1) and ($rServer["status"] <> 3)) {
                                                    $rServer["status"] = 2;
                                                } // Server Timeout
                                                if (in_array($rServer["status"], array(0, 1))) {
                                                    $rServerText = array(0 => "Disabled", 1 => "Online")[$rServer["status"]];
                                                } else if ($rServer["status"] == 2) {
                                                    if ($rServer["last_check_ago"] > 0) {
                                                        $rServerText = "Offline for " . intval((time() - $rServer["last_check_ago"]) / 60) . " minutes";
                                                    } else {
                                                        $rServerText = "Offline";
                                                    }
                                                } else if ($rServer["status"] == 3) {
                                                    $rServerText = "Installing...";
                                                }
                                                $rWatchDog = json_decode($rServer["watchdog_data"], True);
                                                if (!is_array($rWatchDog)) {
                                                    $rWatchDog = array("total_mem_used_percent" => "N/A ", "cpu_avg" => "N/A ");
                                                }
                                                $rLatency = $rServer["latency"] * 1000;
                                                if ($rLatency > 0) {
                                                    $rLatency = $rLatency . " ms";
                                                } else {
                                                    $rLatency = "--";
                                                }
                                            ?>
                                                <tr id="server-<?= $rServer["id"] ?>">
                                                    <td class="text-center"><?= $rServer["id"] ?></td>
                                                    <td class="text-center"><?= $rServer["server_name"] ?></td>
                                                    <td class="text-center" data-toggle="tooltip" data-placement="top"
                                                        title="" data-original-title="<?= $rServerText ?>"><i
                                                            class="<?php if ($rServer["status"] == 1) {
                                                                        echo "btn-outline-success";
                                                                    } else if ($rServer["status"] == "3") {
                                                                        echo "btn-outline-info";
                                                                    } else {
                                                                        echo "btn-outline-danger";
                                                                    } ?> mdi mdi-<?= array(0 => "alarm-light-outline", 1 => "check-network", 2 => "alarm-light-outline", 3 => "creation")[$rServer["status"]] ?>"></i>
                                                    </td>
                                                    <td class="text-center"><?= $rLatency ?></td>
                                                    <td class="text-center"><?= $rServer["domain_name"] ?></td>
                                                    <td class="text-center"><?= $rServer["server_ip"] ?></td>
                                                    <?php if (hasPermissions("adv", "live_connections")) { ?>
                                                        <td class="text-center"><a
                                                                href="./live_connections.php?server_id=<?= $rServer["id"] ?>"><?= count(getConnections($rServer["id"])) ?>
                                                                / <?= $rServer["total_clients"] ?></a></td>
                                                    <?php } else { ?>
                                                        <td class="text-center"><?= count(getConnections($rServer["id"])) ?> /
                                                            <?= $rServer["total_clients"] ?></td>
                                                    <?php } ?>
                                                    <td class="text-center"><?= intval($rWatchDog["cpu_avg"]) ?>%</td>
                                                    <td class="text-center">
                                                        <?= intval($rWatchDog["total_mem_used_percent"]) ?>%</td>
                                                    <td class="text-center">
                                                        <?php if (hasPermissions("adv", "edit_server")) { ?>
                                                            <div class="btn-group">
                                                                <button type="button" data-toggle="tooltip" data-placement="top"
                                                                    title=""
                                                                    data-original-title="<?= $_["restart_reboot_fast-reload_full-remake_update-release"] ?>"
                                                                    class="btn btn-light waves-effect waves-light btn-xs btn-reboot-server"
                                                                    data-id="<?= $rServer["id"] ?>"><i
                                                                        class="mdi mdi-restart"></i></button>
                                                                <button type="button" data-toggle="tooltip" data-placement="top"
                                                                    title=""
                                                                    data-original-title="<?= $_["start_all_servers"] ?>"
                                                                    class="btn btn-light waves-effect waves-light btn-xs"
                                                                    onClick="api(<?= $rServer["id"] ?>, 'start');"><i
                                                                        class="mdi mdi-play"></i></button>
                                                                <button type="button" data-toggle="tooltip" data-placement="top"
                                                                    title="" data-original-title="<?= $_["stop_all_streams"] ?>"
                                                                    class="btn btn-light waves-effect waves-light btn-xs"
                                                                    onClick="api(<?= $rServer["id"] ?>, 'stop');"><i
                                                                        class="mdi mdi-stop"></i></button>
                                                                <button type="button" data-toggle="tooltip" data-placement="top"
                                                                    title=""
                                                                    data-original-title="<?= $_["kill_all_connections"] ?>"
                                                                    class="btn btn-light waves-effect waves-light btn-xs"
                                                                    onClick="api(<?= $rServer["id"] ?>, 'kill');"><i
                                                                        class="fas fa-hammer"></i></button>
                                                                <a href="./server.php?id=<?= $rServer["id"] ?>"><button
                                                                        type="button" data-toggle="tooltip" data-placement="top"
                                                                        title="" data-original-title="<?= $_["edit_server"] ?>"
                                                                        class="btn btn-light waves-effect waves-light btn-xs"><i
                                                                            class="mdi mdi-pencil-outline"></i></button></a>
                                                                <?php if ($rServer["can_delete"] == 1) { ?>
                                                                    <button type="button" data-toggle="tooltip" data-placement="top"
                                                                        title="" data-original-title="<?= $_["delete_server"] ?>"
                                                                        class="btn btn-light waves-effect waves-light btn-xs"
                                                                        onClick="api(<?= $rServer["id"] ?>, 'delete');"><i
                                                                            class="mdi mdi-close"></i></button>
                                                                <?php } else { ?>
                                                                    <button disabled type="button"
                                                                        class="btn btn-light waves-effect waves-light btn-xs"><i
                                                                            class="mdi mdi-close"></i></button>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } else {
                                                            echo "--";
                                                        } ?>
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
                <div class="modal fade bs-server-modal-center" tabindex="-1" role="dialog"
                    aria-labelledby="restartServicesLabel" aria-hidden="true" style="display: none;" data-id="">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="restartServicesLabel">
                                    <?= $_["advanced_functions_for_servers"] ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group row mb-4">
                                    <label class="col-md-3 col-form-label"
                                        for="root_password"><?= $_["ssh_password"] ?></label>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" id="root_password" value="">
                                    </div>
                                    <label class="col-md-2 col-form-label" for="ssh_port"><?= $_["ssh_port"] ?></label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="ssh_port" value="22"></p>
                                    </div>
                                </div>
                                </p>
                                <div class="form-group row mb-4">
                                    <div class="col-md-6">
                                        <input id="restart_services_ssh" type="submit" class="btn btn-primary"
                                            value="<?= $_["restart_services"] ?>" style="width:100%" /></p>
                                    </div>
                                    <div class="col-md-6">
                                        <input id="reboot_server_ssh" type="submit" class="btn btn-primary"
                                            value="<?= $_["reboot_server"] ?>" style="width:100%" /></p>
                                    </div>
                                </div>
                                <!--<div class="form-group row mb-4">
                            <div class="col-md-6 mx-auto">
                                <input id="update_release_ssh" type="submit" class="btn btn-danger" value="<?= $_["update_release"] ?>" style="width:100%" />
                            </div>
                        </div>-->
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                <!-- end wrapper -->
                <?php if ($rSettings["sidebar"]) {
                    echo "</div>";
                } ?>
                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 copyright text-center"><?= getFooter() ?></div>
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
                            if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_server"] ?>') == false) {
                                return;
                            }
                        } else if (rType == "kill") {
                            if (confirm('<?= $_["are_you_sure_you_want_to_kill_all_servers"] ?>') == false) {
                                return;
                            }
                        } else if (rType == "start") {
                            if (confirm('<?= $_["are_you_sure_you_want_to_start_all_severs"] ?>') == false) {
                                return;
                            }
                        } else if (rType == "stop") {
                            if (confirm('<?= $_["are_you_sure_you_want_to_stop_all_streams"] ?>') == false) {
                                return;
                            }
                        }
                        $.getJSON("./api.php?action=server&sub=" + rType + "&server_id=" + rID, function(data) {
                            if (data.result === true) {
                                if (rType == "delete") {
                                    $("#server-" + rID).remove();
                                    $.each($('.tooltip'), function(index, element) {
                                        $(this).remove();
                                    });
                                    $('[data-toggle="tooltip"]').tooltip();
                                    $.toast("<?= $_["server_successfully_deleted"] ?>");
                                } else if (rType == "kill") {
                                    $.toast("<?= $_["all_server_connections_have_been_killed"] ?>");
                                } else if (rType == "start") {
                                    $.toast("<?= $_["all_server_connections_have_been_started"] ?>");
                                } else if (rType == "stop") {
                                    $.toast("<?= $_["all_server_connections_have_been_stopped"] ?>");
                                }
                            } else {
                                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
                            }
                        });
                    }
                    $("#restart_services_ssh").click(function() {
                        $(".bs-server-modal-center").modal("hide");
                        $.getJSON("./api.php?action=restart_services&ssh_port=" + $("#ssh_port").val() + "&server_id=" + $(".bs-server-modal-center").data("id") + "&password=" + $("#root_password").val(), function(data) {
                            if (data.result === true) {
                                $.toast("<?= $_["services_will_be_restarted_shortly"] ?>");
                            } else {
                                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
                            }
                            $("#root_password").val("");
                            $("#ssh_port").val("22");
                            $(".bs-server-modal-center").data("id", "");
                        });
                    });
                    $("#reboot_server_ssh").click(function() {
                        $(".bs-server-modal-center").modal("hide");
                        $.getJSON("./api.php?action=reboot_server&ssh_port=" + $("#ssh_port").val() + "&server_id=" + $(".bs-server-modal-center").data("id") + "&password=" + $("#root_password").val(), function(data) {
                            if (data.result === true) {
                                $.toast("<?= $_["server_will_be_restarted_shortly"] ?>");
                            } else {
                                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
                            }
                            $("#root_password").val("");
                            $("#ssh_port").val("22");
                            $(".bs-server-modal-center").data("id", "");
                        });
                    });
                    $("#update_release_ssh").click(function() {
                        $(".bs-server-modal-center").modal("hide");
                        $.getJSON("./api.php?action=update_release&ssh_port=" + $("#ssh_port").val() + "&server_id=" + $(".bs-server-modal-center").data("id") + "&password=" + $("#root_password").val(), function(data) {
                            if (data.result === true) {
                                $.toast("Release will be updated shortly.");
                            } else {
                                $.toast("An error occured while processing your request.");
                            }
                            $("#root_password").val("");
                            $("#ssh_port").val("22");
                            $(".bs-server-modal-center").data("id", "");
                        });
                    });
                    $(".btn-reboot-server").click(function() {
                        $(".bs-server-modal-center").data("id", $(this).data("id"));
                        $(".bs-server-modal-center").modal("show");
                    });
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
                            pageLength: 50,
                            lengthMenu: [10, 25, 50, 100, 250],
                            responsive: false,
                            columnDefs: [
                                <?php if ($rPermissions["is_admin"]) { ?> {
                                        "orderable": false,
                                        "targets": [9]
                                    }
                                <?php } else { ?> {
                                        "className": "dt-center",
                                        "targets": []
                                    }
                                <?php } ?>
                            ],
                            stateSave: true

                        });
                        $("#datatable").css("width", "100%");
                    });
                </script>
                </body>

                </html>