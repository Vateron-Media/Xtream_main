<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_reseller"]) or (!$rPermissions["create_sub_resellers"])) {
    exit;
}

if ((isset(CoreUtilities::$request["submit_credits"])) && (isset(CoreUtilities::$request["id"]))) {
    if (!UIController::hasPermissions("reg_user", CoreUtilities::$request["id"])) {
        exit;
    }
    $rUser = UIController::getRegisteredUser(CoreUtilities::$request["id"]);
    $rCost = intval(CoreUtilities::$request["credits"]);
    if (($rUserInfo["credits"] - $rCost < 0) && ($rCost > 0)) {
        $_STATUS = 1;
    }
    if ($rUser["credits"] + $rCost < 0) {
        $_STATUS = 1;
    }
    if ((!isset($_STATUS)) && ($rUser)) {
        $rNewCredits = floatval($rUserInfo["credits"]) - floatval($rCost);
        $rUpdCredits = floatval($rUser["credits"]) + floatval($rCost);
        $ipTV_db_admin->query("UPDATE `reg_users` SET `credits` = " . $rNewCredits . " WHERE `id` = " . intval($rUserInfo["id"]) . ";");
        $ipTV_db_admin->query("UPDATE `reg_users` SET `credits` = " . $rUpdCredits . " WHERE `id` = " . intval($rUser["id"]) . ";");
        $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . $rUser["username"] . "', '', " . intval(time()) . ", '[<b>UserPanel</b>] -> " . $_["transfer_credits_to"] . " [" . $rUser["username"] . "] Credits: <font color=\"green\">" . $rUserInfo["credits"] . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
        $ipTV_db_admin->query("INSERT INTO `credits_log`(`target_id`, `admin_id`, `amount`, `date`, `reason`) VALUES(" . $rUser["id"] . ", " . intval($rUserInfo["id"]) . ", " . $rCost . ", " . intval(time()) . ", 'Reseller credits transfer');");
        header("Location: ./reg_users.php");
        exit;
    }
}

if (!isset(CoreUtilities::$request["id"])) {
    exit;
}
if (!UIController::hasPermissions("reg_user", CoreUtilities::$request["id"])) {
    exit;
}
$rUser = UIController::getRegisteredUser(CoreUtilities::$request["id"]);
if (!$rUser) {
    exit;
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
                            <a href="./reg_users.php">
                                <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                    <?= $_["back_to_subresellers"] ?></li>
                            </a>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["transfer_credits_to"] ?>: <?= $rUser["username"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["transfer_success"] ?>
                    </div>
                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["transfer_fail"] ?>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <form action="./credits_add.php<?php if (isset(CoreUtilities::$request["id"])) {
                            echo "?id=" . CoreUtilities::$request["id"];
                        } ?>" method="POST" id="credits_form" data-parsley-validate="">
                            <input type="hidden" name="id" value="<?= CoreUtilities::$request["id"] ?>" />
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["transfer_details"] ?></span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="user-details">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-danger" role="alert" id="no-credits"
                                                    style="display:none;">
                                                    <i class="mdi mdi-block-helper mr-2"></i>
                                                    <?= $_["transfer_fail"] ?>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-8 col-form-label"
                                                        for="credits"><?= $_["credits_to_transfer"] ?></label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control"
                                                            onkeypress="return isNumberKey(event)" id="credits"
                                                            name="credits" value="0" required
                                                            data-parsley-trigger="change">
                                                    </div>
                                                    <table class="table" id="credits-cost" style="margin-top:30px;">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">
                                                                    <?= $_["total_credits"] ?>
                                                                </th>
                                                                <th class="text-center">
                                                                    <?= $_["purchase_cost"] ?>
                                                                </th>
                                                                <th class="text-center">
                                                                    <?= $_["remaining_credits"] ?>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="text-center">
                                                                    <?= number_format($rUserInfo["credits"], 2) ?>
                                                                </td>
                                                                <td class="text-center" id="cost_credits">
                                                                </td>
                                                                <td class="text-center" id="remaining_credits"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="next list-inline-item float-right">
                                                <input name="submit_credits" type="submit"
                                                    class="btn btn-primary purchase" value="<?= $_["purchase"] ?>" />
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
<script src="assets/js/pages/jquery.number.min.js"></script>
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/libs/treeview/jstree.min.js"></script>
<script src="assets/js/pages/treeview.init.js"></script>
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/libs/parsleyjs/parsley.min.js"></script>
<script src="assets/js/app.min.js"></script>

<script>
    (function ($) {
        $.fn.inputFilter = function (inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
                if (inputFilter(this.value)) {
                    this.oldValue = this.value;
                    this.oldSelectionStart = this.selectionStart;
                    this.oldSelectionEnd = this.selectionEnd;
                } else if (this.hasOwnProperty("oldValue")) {
                    this.value = this.oldValue;
                    this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                }
            });
        };
    }(jQuery));

    function calculateCredits() {
        var rCredits = $("#credits").val();
        var rUserCredits = <?= $rUser["credits"] ?>;

        if (!$.isNumeric(rCredits)) {
            rCredits = 0;
        }
        $("#cost_credits").html($.number(rCredits, 2));
        $("#remaining_credits").html($.number(<?= $rUserInfo["credits"] ?> - rCredits, 0));
        if ((parseFloat(<?= $rUserInfo["credits"] ?>) - parseFloat(rCredits) < 0) || (parseFloat(rUserCredits) + parseFloat(rCredits) < 0)) {
            $("#no-credits").show()
            $(".purchase").prop('disabled', true);
        } else {
            $("#no-credits").hide()
            $(".purchase").prop('disabled', false);
        }
        if (rCredits == 0) {
            $(".purchase").prop('disabled', true);
        } else {
            $(".purchase").prop('disabled', false);
        }
    }

    $(document).ready(function () {
        $('select.select2').select2({
            width: '100%'
        })
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        elems.forEach(function (html) {
            var switchery = new Switchery(html);
        });

        $(document).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });

        $("#credits").on('input', function () {
            calculateCredits();
        });

        $("form").attr('autocomplete', 'off');
        calculateCredits();
    });
</script>
</body>

</html>