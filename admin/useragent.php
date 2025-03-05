<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "block_uas"))) {
    exit;
}

if (isset(CoreUtilities::$request["submit_ua"])) {
    $rArray = array("user_agent" => "", "exact_match" => 0, "attempts_blocked" => 0);
    if (isset(CoreUtilities::$request["exact_match"])) {
        $rArray["exact_match"] = true;
        unset(CoreUtilities::$request["exact_match"]);
    }
    foreach (CoreUtilities::$request as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
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
    if (isset(CoreUtilities::$request["edit"])) {
        $rCols = "id," . $rCols;
        $rValues = CoreUtilities::$request["edit"] . "," . $rValues;
    }
    $rQuery = "REPLACE INTO `blocked_user_agents`(" . $rCols . ") VALUES(" . $rValues . ");";
    if ($ipTV_db_admin->query($rQuery)) {
        if (isset(CoreUtilities::$request["edit"])) {
            $rInsertID = intval(CoreUtilities::$request["edit"]);
        } else {
            $rInsertID = $ipTV_db_admin->last_insert_id();
        }
    }
    if (isset($rInsertID)) {
        header("Location: ./useragents.php");
        exit;
    } else {
        $_STATUS = 1;
    }
}

if (isset(CoreUtilities::$request["id"])) {
    $rUAArr = getUserAgent(CoreUtilities::$request["id"]);
    if (!$rUAArr) {
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
                                        <a href="./useragents.php">
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?= $_["back_to_user-agents"] ?></li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php if (isset($rUAArr)) {
                                                            echo $_["edit"];
                                                       } else {
                                                           echo $_["block"];
                                                       } ?> <?= $_["user-agent"] ?></h4>
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
                                    <?= $_["user-agent_operation"] ?>
                                </div>
                            <?php } elseif ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["there_was_an_error"] ?>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./useragent.php<?php if (isset(CoreUtilities::$request["id"])) {
                                                                        echo "?id=" . CoreUtilities::$request["id"];
                                                                 } ?>" method="POST" id="useragent_form" data-parsley-validate="">
                                        <?php if (isset($rUAArr)) { ?>
                                            <input type="hidden" name="edit" value="<?= $rUAArr["id"] ?>" />
                                        <?php } ?>
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#useragent-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="useragent-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="user_agent"><?= $_["user-agent"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="user_agent" name="user_agent" value="<?php if (isset($rUAArr)) {
                                                                                                                                                            echo htmlspecialchars($rUAArr["user_agent"]);
                                                                                                                                                     } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="exact_match"><?= $_["exact_match"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input name="exact_match" id="exact_match" type="checkbox" <?php if (isset($rUAArr)) {
                                                                        if ($rUAArr["exact_match"] == 1) {
                                                                            echo "checked ";
                                                                        }
                                                                                                                               } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="next list-inline-item float-right">
                                                            <input name="submit_ua" type="submit" class="btn btn-primary" value="<?php if (isset($rUAArr)) {
                                                                                                                                        echo $_["edit"];
                                                                                                                                 } else {
                                                                                                                                     echo $_["block"];
                                                                                                                                 } ?>" />
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
                <script src="assets/libs/parsleyjs/parsley.min.js"></script>
                <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
                <script src="assets/js/pages/form-wizard.init.js"></script>
                <script src="assets/js/app.min.js"></script>

                <script>
                    $(document).ready(function() {
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