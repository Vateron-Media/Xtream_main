<?php
include "session.php";
include "functions.php";
if ($rPermissions["is_admin"]) {
    exit;
}
$rStatusArray = array(0 => "CLOSED", 1 => "OPEN", 2 => "RESPONDED", 3 => "READ");
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
                    <!--<div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title"><?= $_["dashboard"] ?></h4>
                        </div>
                    </div>
                </div>-->
                    <div class="card-box1">

                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-md-6 col-xl-3">
                            <div class="card-box active-connections bg-info">
                                <div class="row">
                                    <div class="col-6">
                                        <?php if ($rAdminSettings["dark_mode"]) { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-zap avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } else { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-box avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-right">
                                            <h3 class="text-white my-1"><span data-plugin="counterup"
                                                    class="entry">0</span></h3>
                                            <p class="text-white mb-1 text-truncate"><?= $_["connections"] ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end card-box-->
                        </div> <!-- end col -->

                        <div class="col-md-6 col-xl-3">
                            <div class="card-box online-users bg-success">
                                <div class="row">
                                    <div class="col-6">
                                        <?php if ($rAdminSettings["dark_mode"]) { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-zap avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } else { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-users avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-right">
                                            <h3 class="text-white my-1"><span data-plugin="counterup"
                                                    class="entry">0</span></h3>
                                            <p class="text-white mb-1 text-truncate"><?= $_["online_users"] ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end card-box-->
                        </div> <!-- end col -->

                        <div class="col-md-6 col-xl-3">
                            <div class="card-box active-accounts bg-purple">
                                <div class="row">
                                    <div class="col-6">
                                        <?php if ($rAdminSettings["dark_mode"]) { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-zap avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } else { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-check-circle avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-right">
                                            <h3 class="text-white my-1"><span data-plugin="counterup"
                                                    class="entry">0</span></h3>
                                            <p class="text-white mb-1 text-truncate"><?= $_["active_accounts"] ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end card-box-->
                        </div> <!-- end col -->

                        <div class="col-md-6 col-xl-3">
                            <div class="card-box credits bg-warning">
                                <div class="row">
                                    <div class="col-6">
                                        <?php if ($rAdminSettings["dark_mode"]) { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-zap avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } else { ?>
                                            <div class="avatar-md rounded">
                                                <i class="fe-dollar-sign avatar-title font-22 text-white"></i>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div id="statistics-collapse" class="col-6 collapsept-3 show">
                                        <div class="text-right">
                                            <h3 class="text-white my-1"><span data-plugin="counterup"
                                                    class="entry">0</span></h3>
                                            <p class="text-white mb-1 text-truncate"><?= $_["credits"] ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end card-box-->
                        </div> <!-- end col -->
                        <!--<div class="col-12">
                    <p class="text-muted">MENSSAGEM</p>
                    </div>-->
                        <div class="col-12">
                            <div class="card-header bg-white text-danger">
                                <a data-toggle="collapse" href="#cardCollpase1" class="arrow-none card-drop"
                                    data-parent="#cardCollpase1" role="tablist" aria-expanded="true"
                                    aria-controls="cardCollpase1">
                                    <i class="mdi mdi-magnify-minus"></i></a>

                                <div id="cardCollpase1" class="collapse pt-3 show bg-white card-box"
                                    style="margin-bottom:-8px;">
                                    <div class="row">
                                        <div class="col-1">
                                            <?php if ($rAdminSettings["dark_mode"]) { ?>
                                                <div class="avatar-sm bg-secondary rounded">
                                                    <i class="fas fa-info-circle avatar-title font-24 text-white"></i>
                                                </div>
                                            <?php } else { ?>
                                                <div class="avatar-sm bg-soft-warning rounded">
                                                    <i class="fas fa-info-circle avatar-title font-24 text-danger"></i>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-0"><?= $_["recent_activity"] ?></h4>
                                    <div id="cardActivity" class="pt-3">
                                        <div class="slimscroll" style="height:350px;">
                                            <div class="timeline-alt">
                                                <?php
                                                $ipTV_db_admin->query("SELECT `u`.`username`, `r`.`owner`, `r`.`date`, `r`.`type` FROM `reg_userlog` AS `r` INNER JOIN `reg_users` AS `u` ON `r`.`owner` = `u`.`id` WHERE `r`.`owner` IN (" . $ipTV_db_admin->escape(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))) . ") ORDER BY `r`.`date` DESC LIMIT 100;");
                                                if ($ipTV_db_admin->num_rows() > 0) {
                                                    foreach ($ipTV_db_admin->get_rows() as $rRow) { ?>
                                                        <div class="timeline-item">
                                                            <i class="timeline-icon"></i>
                                                            <div class="timeline-item-info">
                                                                <a href="#"
                                                                    class="text-body font-weight-semibold mb-1 d-block"><?= $rRow["username"] ?></a>
                                                                <small><?= html_entity_decode($rRow["type"]) ?></small>
                                                                <p>
                                                                    <small
                                                                        class="text-muted"><?= date("Y-m-d H:i:s", $rRow["date"]) ?></small>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php }
                                                } ?>
                                            </div>
                                            <!-- end timeline -->
                                        </div> <!-- end slimscroll -->
                                    </div> <!-- collapsed end -->
                                </div> <!-- end card-body -->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-0"><?= $_["expiring_lines"] ?></h4>
                                    <div id="cardActivity" class="pt-3">
                                        <div class="slimscroll" style="height: 350px;">
                                            <table
                                                class="table table-hover m-0 table-centered dt-responsive nowrap w-100"
                                                id="users-table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center"><?= $_["username"] ?></th>
                                                        <th class="text-center"><?= $_["password"] ?></th>
                                                        <th class="text-center"><?= $_["reseller"] ?></th>
                                                        <th class="text-center"><?= $_["expiration"] ?></th>
                                                        <th class="text-center"><?= $_["action"] ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $rRegisteredUsers = getRegisteredUsers();
                                                    foreach (getExpiring($rUserInfo["id"]) as $rUser) { ?>
                                                        <tr id="user-<?= $rUser["id"] ?>">
                                                            <td class="text-center"><?= $rUser["username"] ?></td>
                                                            <td class="text-center"><?= $rUser["password"] ?></td>
                                                            <td class="text-center">
                                                                <?= $rRegisteredUsers[$rUser["member_id"]]["username"] ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?= date("Y-m-d H:i:s", $rUser["exp_date"]) ?>
                                                            </td>
                                                            <td class="text-center"><a
                                                                    href="./user_reseller.php?id=<?= $rUser["id"] ?>"><button
                                                                        data-toggle="tooltip" data-placement="top" title=""
                                                                        data-original-title="Renew" type="button"
                                                                        class="btn btn-light waves-effect waves-light btn-xs"><i
                                                                            class="mdi mdi-autorenew mdi-spin"></i></button></a>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div> <!-- end slimscroll -->
                                    </div> <!-- collapsed end -->
                                </div> <!-- end card-body -->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    </div>
                    <!-- end row -->

                </div> <!-- end container -->
            </div>
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
            <script src="assets/libs/jquery-knob/jquery.knob.min.js"></script>
            <script src="assets/libs/peity/jquery.peity.min.js"></script>
            <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
            <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
            <script src="assets/libs/jquery-number/jquery.number.js"></script>
            <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
            <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
            <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
            <script src="assets/js/pages/dashboard.init.js"></script>
            <script src="assets/js/app.min.js"></script>

            <script>
                function getStats() {
                    var rStart = Date.now();
                    $.getJSON("./api.php?action=reseller_dashboard", function (data) {
                        $(".active-connections .entry").html($.number(data.open_connections, 0));
                        $(".online-users .entry").html($.number(data.online_users, 0));
                        $(".active-accounts .entry").html($.number(data.active_accounts, 0));
                        <?php if (floor($rUserInfo["credits"]) == $rUserInfo["credits"]) { ?>
                            $(".credits .entry").html($.number(data.credits, 0));
                        <?php } else { ?>
                            $(".credits .entry").html($.number(data.credits, 2));
                        <?php } ?>
                        if (Date.now() - rStart < 1000) {
                            setTimeout(getStats, 1000 - (Date.now() - rStart));
                        } else {
                            getStats();
                        }
                    }).fail(function () {
                        setTimeout(getStats, 1000);
                    });
                }

                $(document).ready(function () {
                    getStats();
                });
            </script>
            </body>

            </html>