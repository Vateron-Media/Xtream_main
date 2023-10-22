<?php
include "session.php"; include "functions.php";
if ($rPermissions["is_admin"]) { exit; }
$rStatusArray = Array(0 => "CLOSED", 1 => "OPEN", 2 => "RESPONDED", 3 => "READ");
if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">Dashboard</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 

                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card-box active-connections">
                            <div class="row">
                                <div class="col-6">
									<?php if ($rAdminSettings["dark_mode"]) { ?>
									<div class="avatar-sm bg-secondary rounded">
										<i class="fe-zap avatar-title font-22 text-white"></i>
									</div>
									<?php } else { ?>
                                    <div class="avatar-sm bg-soft-purple rounded">
                                        <i class="fe-zap avatar-title font-22 text-purple"></i>
                                    </div>
									<?php } ?>
                                </div>
                                <div class="col-6">
                                    <div class="text-right">
                                        <h3 class="text-dark my-1"><span data-plugin="counterup" class="entry">0</span></h3>
                                        <p class="text-muted mb-1 text-truncate">Connections</p>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end card-box-->
                    </div> <!-- end col -->

                    <div class="col-md-6 col-xl-3">
                        <div class="card-box online-users">
                            <div class="row">
                                <div class="col-6">
									<?php if ($rAdminSettings["dark_mode"]) { ?>
									<div class="avatar-sm bg-secondary rounded">
										<i class="fe-zap avatar-title font-22 text-white"></i>
									</div>
									<?php } else { ?>
                                    <div class="avatar-sm bg-soft-success rounded">
                                        <i class="fe-users avatar-title font-22 text-success"></i>
                                    </div>
									<?php } ?>
                                </div>
                                <div class="col-6">
                                    <div class="text-right">
                                        <h3 class="text-dark my-1"><span data-plugin="counterup" class="entry">0</span></h3>
                                        <p class="text-muted mb-1 text-truncate">Online Users</p>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end card-box-->
                    </div> <!-- end col -->

                    <div class="col-md-6 col-xl-3">
                        <div class="card-box active-accounts">
                            <div class="row">
                                <div class="col-6">
									<?php if ($rAdminSettings["dark_mode"]) { ?>
									<div class="avatar-sm bg-secondary rounded">
										<i class="fe-zap avatar-title font-22 text-white"></i>
									</div>
									<?php } else { ?>
                                    <div class="avatar-sm bg-soft-primary rounded">
                                        <i class="fe-check-circle avatar-title font-22 text-primary"></i>
                                    </div>
									<?php } ?>
                                </div>
                                <div class="col-6">
                                    <div class="text-right">
                                        <h3 class="text-dark my-1"><span data-plugin="counterup" class="entry">0</span></h3>
                                        <p class="text-muted mb-1 text-truncate">Active Accounts</p>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end card-box-->
                    </div> <!-- end col -->

                    <div class="col-md-6 col-xl-3">
                        <div class="card-box credits">
                            <div class="row">
                                <div class="col-6">
									<?php if ($rAdminSettings["dark_mode"]) { ?>
									<div class="avatar-sm bg-secondary rounded">
										<i class="fe-zap avatar-title font-22 text-white"></i>
									</div>
									<?php } else { ?>
                                    <div class="avatar-sm bg-soft-info rounded">
                                        <i class="fe-dollar-sign avatar-title font-22 text-info"></i>
                                    </div>
									<?php } ?>
                                </div>
                                <div class="col-6">
                                    <div class="text-right">
                                        <h3 class="text-dark my-1"><span data-plugin="counterup" class="entry">0</span></h3>
                                        <p class="text-muted mb-1 text-truncate">Credits</p>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end card-box-->
                    </div> <!-- end col -->
                </div>
                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-0">Recent Activity</h4>
                                <div id="cardActivity" class="pt-3">
                                    <div class="slimscroll" style="height:350px;">
                                        <div class="timeline-alt">
                                            <?php 
                                            $rResult = $db->query("SELECT `u`.`username`, `r`.`owner`, `r`.`date`, `r`.`type` FROM `reg_userlog` AS `r` INNER JOIN `reg_users` AS `u` ON `r`.`owner` = `u`.`id` WHERE `r`.`owner` IN (".ESC(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))).") ORDER BY `r`.`date` DESC LIMIT 100;");
                                            if (($rResult) && ($rResult->num_rows > 0)) {
                                                while ($rRow = $rResult->fetch_assoc()) { ?>
                                                <div class="timeline-item">
                                                    <i class="timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="#" class="text-body font-weight-semibold mb-1 d-block"><?=$rRow["username"]?></a>
                                                        <small><?=html_entity_decode($rRow["type"])?></small>
                                                        <p>
                                                            <small class="text-muted"><?=date("Y-m-d H:i:s", $rRow["date"])?></small>
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
                                <h4 class="header-title mb-0">Expiring Lines</h4>
                                <div id="cardActivity" class="pt-3">
                                    <div class="slimscroll" style="height: 350px;">
                                        <table class="table table-hover m-0 table-centered dt-responsive nowrap w-100" id="users-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Username</th>
													<th class="text-center">Password</th>
                                                    <th class="text-center">Reseller</th>
                                                    <th class="text-center">Expiration</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $rRegisteredUsers = getRegisteredUsers();
												foreach (getExpiring($rUserInfo["id"]) as $rUser) { ?>
                                                <tr id="user-<?=$rUser["id"]?>">
                                                    <td class="text-center"><a href="./user_reseller.php?id=<?=$rUser["id"]?>"><?=$rUser["username"]?></a></td>
													<td class="text-center"><?=$rUser["password"]?></td>
                                                    <td class="text-center"><?=$rRegisteredUsers[$rUser["member_id"]]["username"]?></td>
                                                    <td class="text-center"><?=date("Y-m-d H:i:s", $rUser["exp_date"])?></td>
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
            $.getJSON("./api.php?action=reseller_dashboard", function(data) {
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
            }).fail(function() {
                setTimeout(getStats, 1000);
            });
        }
        
        $(document).ready(function() {
            getStats();
        });
        </script>
    </body>
</html>