<?php
include "session.php";
include "functions.php";
if (($rPermissions["is_admin"]) && (!UIController::hasPermissions("adv", "ticket"))) {
    exit;
}

if (isset(CoreUtilities::$request["submit_ticket"])) {
    if (((strlen(CoreUtilities::$request["title"]) == 0) && (!isset(CoreUtilities::$request["respond"]))) or ((strlen(CoreUtilities::$request["message"]) == 0))) {
        $_STATUS = 1;
    }
    if (!isset($_STATUS)) {
        if (!isset(CoreUtilities::$request["respond"])) {
            $rArray = array("member_id" => $rUserInfo["id"], "title" => CoreUtilities::$request["title"], "status" => 1, "admin_read" => 0, "user_read" => 1);
            $rCols = "`" . implode('`,`', array_keys($rArray)) . "`";
            foreach (array_values($rArray) as $rValue) {
                isset($rValues) ? $rValues .= ',' : $rValues = '';
                if (is_array($rValue)) {
                    $rValue = json_encode($rValue);
                }
                if (is_null($rValue)) {
                    $rValues .= 'NULL';
                } else {
                    $rValues .= '\'' . $rValue . '\'';
                }
            }
            $rQuery = "INSERT INTO `tickets`(" . $rCols . ") VALUES(" . $rValues . ");";
            if ($ipTV_db_admin->query($rQuery)) {
                $rInsertID = $ipTV_db_admin->last_insert_id();
                $ipTV_db_admin->query("INSERT INTO `tickets_replies`(`ticket_id`, `admin_reply`, `message`, `date`) VALUES(" . $rInsertID . ", 0, '" . CoreUtilities::$request["message"] . "', " . time() . ");");
                header("Location: ./ticket_view.php?id=" . intval($rInsertID));
            } else {
                $_STATUS = 2;
            }
        } else {
            $rTicket = UIController::getTicket(CoreUtilities::$request["respond"]);
            if ($rTicket) {
                if (intval($rUserInfo["id"]) == intval($rTicket["member_id"])) {
                    $ipTV_db_admin->query("UPDATE `tickets` SET `admin_read` = 0, `user_read` = 1 WHERE `id` = " . intval(CoreUtilities::$request["respond"]) . ";");
                    $ipTV_db_admin->query("INSERT INTO `tickets_replies`(`ticket_id`, `admin_reply`, `message`, `date`) VALUES(" . intval(CoreUtilities::$request["respond"]) . ", 0, '" . CoreUtilities::$request["message"] . "', " . time() . ");");
                } else {
                    $ipTV_db_admin->query("UPDATE `tickets` SET `admin_read` = 0, `user_read` = 0 WHERE `id` = " . intval(CoreUtilities::$request["respond"]) . ";");
                    $ipTV_db_admin->query("INSERT INTO `tickets_replies`(`ticket_id`, `admin_reply`, `message`, `date`) VALUES(" . intval(CoreUtilities::$request["respond"]) . ", 1, '" . CoreUtilities::$request["message"] . "', " . time() . ");");
                }
                header("Location: ./ticket_view.php?id=" . intval(CoreUtilities::$request["respond"]));
            } else {
                $_STATUS = 2;
            }
        }
    }
}

if (isset(CoreUtilities::$request["id"])) {
    $rTicket = UIController::getTicket(CoreUtilities::$request["id"]);
    if (!$rTicket) {
        exit;
    }
}

include "header.php";
?>
<div class="wrapper boxed-layout">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <?php if (isset($rTicket)) { ?>
                                <a href="./ticket_view.php?id=<?= $rTicket["id"] ?>">
                                    <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                        <?= $_["back_to_ticket"] ?></li>
                                </a>
                            <?php } else { ?>
                                <a href="./tickets.php">
                                    <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                        <?= $_["back_to_ticket"] ?></li>
                                </a>
                            <?php } ?>
                        </ol>
                    </div>
                    <?php if (isset($rTicket)) { ?>
                        <h4 class="page-title"><?= $_["ticket_response"] ?></h4>
                    <?php } else { ?>
                        <h4 class="page-title"><?= $_["create_ticket"] ?></h4>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <?php if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["generic_fail"] ?>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <form action="./ticket.php" method="POST" id="ticket_form" data-parsley-validate="">
                            <?php if (isset($rTicket)) { ?>
                                <input type="hidden" name="respond" value="<?= $rTicket["id"] ?>" />
                            <?php } ?>
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#ticket-details" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="ticket-details">
                                        <div class="row">
                                            <div class="col-12">
                                                <?php if (!isset($rTicket)) { ?>
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="title"><?= $_["subject"] ?></label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" id="title" name="title"
                                                                value="" required
                                                                data-parsley-trigger="<?= $_["change"] ?>">
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="message"><?= $_["message"] ?></label>
                                                    <div class="col-md-8">
                                                        <textarea id="message" name="message" class="form-control"
                                                            rows="3" placeholder="" required
                                                            data-parsley-trigger="<?= $_["change"] ?>"></textarea>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="next list-inline-item float-right">
                                                <input name="submit_ticket" type="submit" class="btn btn-primary"
                                                    value="<?= $_["create"] ?>" />
                                            </li>
                                        </ul>
                                    </div>
                                </div> <!-- tab-content -->
                            </div> <!-- end #basicwizard-->
                        </form>

                    </div> <!-- end card-body -->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
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
<script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
<script src="assets/libs/switchery/switchery.min.js"></script>
<script src="assets/libs/select2/select2.min.js"></script>
<script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
<script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
<script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
<script src="assets/libs/moment/moment.min.js"></script>
<script src="assets/libs/daterangepicker/daterangepicker.js"></script>
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/libs/treeview/jstree.min.js"></script>
<script src="assets/js/pages/treeview.init.js"></script>
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/libs/parsleyjs/parsley.min.js"></script>
<script src="assets/js/app.min.js"></script>

<script>
    $(document).ready(function () {
        $(window).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });

        $("form").attr('autocomplete', 'off');
    });
</script>
</body>

</html>