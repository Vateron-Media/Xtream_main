<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_reseller"]) or (!$rPermissions["create_sub_resellers"])) {
    exit;
}

if (isset($_POST["submit_user"])) {
    if (isset($_POST["edit"])) {
        if (!hasPermissions("reg_user", $_POST["edit"])) {
            exit;
        }
        $rArray = getRegisteredUser($_POST["edit"]);
        unset($rArray["id"]);
    } else {
        $rArray = array("username" => "", "date_registered" => time(), "password" => "", "email" => "", "reseller_dns" => "", "member_group_id" => 1, "verified" => 1, "credits" => 0, "notes" => "", "status" => 1, "owner_id" => intval($rUserInfo["id"]));
    }
    if (((strlen($_POST["username"]) == 0) or ((strlen($_POST["password"]) == 0)) or ((strlen($_POST["email"]) == 0))) and (!isset($_POST["edit"]))) {
        $_STATUS = 1;
    }
    $rUser = $_POST;
    if (!isset($_POST["edit"])) {
        $rCost = intval($rPermissions["create_sub_resellers_price"]);
        if ($rUserInfo["credits"] - $rCost < 0) {
            $_STATUS = 3;
        }
        $ipTV_db_admin->query("SELECT `id` FROM `reg_users` WHERE `username` = '" . ESC($_POST["username"]) . "';");
        if ($ipTV_db_admin->num_rows() > 0) {
            $_STATUS = 4;
        }
        $ipTV_db_admin->query("SELECT `subreseller` FROM `subreseller_setup` WHERE `reseller` = " . intval($rUserInfo["member_group_id"]) . ";");
        if ($ipTV_db_admin->num_rows() > 0) {
            $rArray["member_group_id"] = intval($ipTV_db_admin->get_row()["subreseller"]);
        } else {
            $_STATUS = 5;
        }
    }
    if (!isset($_STATUS)) {
        if (!isset($_POST["edit"])) {
            $rArray["username"] = $_POST["username"];
        }
        if (!strlen($_POST["password"]) == 0) {
            $rArray["password"] = cryptPassword($_POST["password"]);
        }
        if (isset($_POST["email"])) {
            $rArray["email"] = $_POST["email"];
        }
        if (isset($_POST["reseller_dns"])) {
            $rArray["reseller_dns"] = $_POST["reseller_dns"];
        }
        if (isset($_POST["notes"])) {
            $rArray["notes"] = $_POST["notes"];
        }
        $rCols = "`" . ESC(implode('`,`', array_keys($rArray))) . "`";
        foreach (array_values($rArray) as $rValue) {
            isset($rValues) ? $rValues .= ',' : $rValues = '';
            if (is_array($rValue)) {
                $rValue = json_encode($rValue);
            }
            if (is_null($rValue)) {
                $rValues .= 'NULL';
            } else {
                $rValues .= '\'' . ESC($rValue) . '\'';
            }
        }
        if (isset($_POST["edit"])) {
            $rCols = "`id`," . $rCols;
            $rValues = ESC($_POST["edit"]) . "," . $rValues;
        }
        $rQuery = "REPLACE INTO `reg_users`(" . $rCols . ") VALUES(" . $rValues . ");";
        if ($ipTV_db_admin->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
            } else {
                $rInsertID = $ipTV_db_admin->last_insert_id();
            }
            if (isset($rCost)) {
                $rNewCredits = floatval($rUserInfo["credits"]) - $rCost;
                $ipTV_db_admin->query("UPDATE `reg_users` SET `credits` = " . floatval($rNewCredits) . " WHERE `id` = " . intval($rUserInfo["id"]) . ";");
                $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rArray["username"]) . "', '" . ESC($rArray["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b>] -> " . $_["new_subreseller"] . " [" . ESC($_POST["username"]) . "] Credits: <font color=\"green\">" . floatval($rUserInfo["credits"]) . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
                $rUserInfo["credits"] = $rNewCredits;
            }
            header("Location: ./subreseller.php?id=" . $rInsertID);
            exit;
        } else {
            $_STATUS = 2;
        }
    }
}

if (isset($_GET["id"])) {
    if (!hasPermissions("reg_user", $_GET["id"])) {
        exit;
    }
    $rUser = getRegisteredUser($_GET["id"]);
    if (!$rUser) {
        exit;
    }
}
if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) { ?>
        <div class="content-page">
            <div class="content boxed-layout">
                <div class="container-fluid">
            <?php } else { ?>
                    <div class="wrapper boxed-layout">
                        <div class="container-fluid">
                    <?php } ?>
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <a href="./reg_users.php">
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?= $_["back_to_subresellers"] ?></li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php if (isset($rUser)) {
                                    echo $_["edit"];
                                } else {
                                    echo $_["add"];
                                } ?> <?= $_["subreseller"] ?></h4>
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
                                        <?= $_["subreseller_operation"] ?>
                                    </div>
                            <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        <?= $_["please_ensure_you"] ?>
                                        </div>
                            <?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                        <?= $_["generic_fail"] ?>
                                            </div>
                            <?php } else if ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                        <?= $_["you_don't_have_enough"] ?>
                                                </div>
                            <?php } else if ((isset($_STATUS)) && ($_STATUS == 4)) { ?>
                                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                        <?= $_["this_username_has_already"] ?>
                                                    </div>
                            <?php } else if ((isset($_STATUS)) && ($_STATUS == 5)) { ?>
                                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                        <?= $_["your_group_has_not_been"] ?>
                                                        </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./subreseller.php<?php if (isset($_GET["id"])) {
                                        echo "?id=" . $_GET["id"];
                                    } ?>" method="POST" id="user_form" data-parsley-validate="">
                                        <?php if (isset($_GET["id"])) { ?>
                                                <input type="hidden" name="edit" value="<?= $rUser["id"] ?>" />
                                        <?php } ?>
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                                    </a>
                                                </li>
                                                <?php if (!isset($_GET["id"])) { ?>
                                                        <li class="nav-item">
                                                            <a href="#review-purchase" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                                <i class="mdi mdi-book-open-variant mr-1"></i>
                                                                <span class="d-none d-sm-inline"><?= $_["review_purchase"] ?></span>
                                                            </a>
                                                        </li>
                                                <?php } ?>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="user-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="username"><?= $_["username"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input <?php if (isset($_GET["id"])) {
                                                                        echo "disabled ";
                                                                    } ?>type="text" class="form-control" id="username" name="username" value="<?php if (isset($rUser)) {
                                                                         echo htmlspecialchars($rUser["username"]);
                                                                     } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="password"><?php if (isset($_GET["id"])) { ?><?= $_["change"] ?> <?php } ?><?= $_["password"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="password" name="password" <?php if (!isset($rUser)) {
                                                                        echo 'value="' . generateString(10) . '" required data-parsley-trigger="change"';
                                                                    } else {
                                                                        echo 'value=""';
                                                                    } ?> required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="email"><?= $_["email_address"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="email" name="email" value="<?php if (isset($rUser)) {
                                                                        echo htmlspecialchars($rUser["email"]);
                                                                    } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="reseller_dns"><?= $_["reseller_dns"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="reseller_dns" name="reseller_dns" value="<?php if (isset($rUser)) {
                                                                        echo htmlspecialchars($rUser["reseller_dns"]);
                                                                    } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="notes"><?= $_["notes"] ?></label>
                                                                <div class="col-md-8">
                                                                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder=""><?php if (isset($rUser)) {
                                                                        echo htmlspecialchars($rUser["notes"]);
                                                                    } ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="next list-inline-item float-right">
                                                            <?php if (!isset($_GET["id"])) { ?>
                                                                    <a href="javascript: void(0);" class="btn btn-secondary"><?= $_["next"] ?></a>
                                                            <?php } else { ?>
                                                                    <input name="submit_user" type="submit" class="btn btn-primary" value="<?= $_["edit"] ?>" />
                                                            <?php } ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <?php if (!isset($_GET["id"])) { ?>
                                                        <div class="tab-pane" id="review-purchase">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <?php if ($rUserInfo["credits"] - $rPermissions["create_sub_resellers_price"] < 0) { ?>
                                                                            <div class="alert alert-danger" role="alert" id="no-credits">
                                                                                <i class="mdi mdi-block-helper mr-2"></i> <?= $_["you_do_not_have_enough_credits"] ?>
                                                                            </div>
                                                                    <?php } ?>
                                                                    <div class="form-group row mb-4">
                                                                        <table class="table" id="credits-cost">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="text-center"><?= $_["total_credits"] ?></th>
                                                                                    <th class="text-center"><?= $_["purchase_cost"] ?></th>
                                                                                    <th class="text-center"><?= $_["remaining_credits"] ?></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td class="text-center"><?= number_format($rUserInfo["credits"], 2) ?></td>
                                                                                    <td class="text-center" id="cost_credits"><?= number_format($rPermissions["create_sub_resellers_price"], 2) ?></td>
                                                                                    <td class="text-center" id="remaining_credits"><?= number_format($rUserInfo["credits"] - $rPermissions["create_sub_resellers_price"], 2) ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div> <!-- end col -->
                                                            </div> <!-- end row -->
                                                            <ul class="list-inline wizard mb-0">
                                                                <li class="previous list-inline-item">
                                                                    <a href="javascript: void(0);" class="btn btn-secondary"><?= $_["prev"] ?></a>
                                                                </li>
                                                                <li class="next list-inline-item float-right">
                                                                    <input <?php if ($rUserInfo["credits"] - $rPermissions["create_sub_resellers_price"] < 0) {
                                                                        echo "disabled ";
                                                                    } ?>name="submit_user" type="submit" class="btn btn-primary purchase" value="<?= $_["purchase"] ?>" />
                                                                </li>
                                                            </ul>
                                                        </div>
                                                <?php } ?>
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
                    (function($) {
                        $.fn.inputFilter = function(inputFilter) {
                            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
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

                    $(document).ready(function() {
                        $('select.select2').select2({
                            width: '100%'
                        })
                        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                        elems.forEach(function(html) {
                            var switchery = new Switchery(html);
                        });

                        $(window).keypress(function(event) {
                            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                        });

                        $("form").attr('autocomplete', 'off');
                    });
                </script>
                </body>

                </html>