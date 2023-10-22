<?php
include "session.php"; include "functions.php";
if (!isset($_GET["id"])) { exit; }
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "manage_tickets"))) { exit; }

$rTicket = getTicket($_GET["id"]);
if (!$rTicket) {
    exit;
}

if ($rUserInfo["id"] <> $rTicket["member_id"]) {
    $db->query("UPDATE `tickets` SET `admin_read` = 1 WHERE `id` = ".intval($_GET["id"]).";");
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
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <?php if ($rTicket["status"] > 0) {
							if (($rPermissions["is_reseller"]) OR (hasPermissions("adv", "ticket"))) { ?>
                            <div class="page-title-right">
                                <a href="./ticket.php?id=<?=$rTicket["id"]?>">
                                    <button type="button" class="btn btn-sm btn-primary waves-effect waves-light float-right">
                                        <i class="mdi mdi-plus"></i> Add Response
                                    </button>
                                </a>
                            </div>
                            <?php }
							} ?>
                            <h4 class="page-title"><?=$rTicket["title"]?></h4>
                        </div>
                    </div>
                </div>     
                <div class="row">
                    <div class="col-12">
                        <div class="timeline" dir="ltr">
                            <?php foreach ($rTicket["replies"] as $rReply) { ?>
                            <article class="timeline-item<?php if (!$rReply["admin_reply"]) { echo " timeline-item-left"; } ?>">
                                <div class="timeline-desk">
                                    <div class="timeline-box">
                                        <span class="arrow-alt"></span>
                                        <span class="timeline-icon"><i class="mdi mdi-adjust"></i></span>
                                        <h4 class="mt-0 font-16"><?php if (!$rReply["admin_reply"]) { echo $rTicket["user"]["username"]; } else { echo "Admin"; } ?></h4>
                                        <p class="text-muted"><small><?=date("Y-m-d H:i", $rReply["date"])?></small></p>
                                        <p class="mb-0"><?=$rReply["message"]?></p>
                                    </div>
                                </div>
                            </article>
                            <?php } ?>
                        </div>
                    </div><!-- end col -->
                </div>
                <!-- end row -->
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
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
        <script src="assets/js/app.min.js"></script>
    </body>
</html>