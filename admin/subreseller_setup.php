<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "subreseller"))) {
    exit;
}

if (isset($_POST["submit_subreseller"])) {
    if (!((intval($_POST["reseller"]) > 0) && (intval($_POST["subreseller"]) > 0) && (intval($_POST["reseller"]) <> intval($_POST["subreseller"])))) {
        $_STATUS = 1;
    }
    if (!isset($_STATUS)) {
        $rArray = array("reseller" => 0, "subreseller" => 0);
        foreach ($_POST as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
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
            $rCols = "id," . $rCols;
            $rValues = ESC($_POST["edit"]) . "," . $rValues;
        }
        $rQuery = "REPLACE INTO `subreseller_setup`(" . $rCols . ") VALUES(" . $rValues . ");";
        if ($ipTV_db_admin->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
            } else {
                $rInsertID = $ipTV_db_admin->last_insert_id();
            }
        }
        if (isset($rInsertID)) {
            header("Location: ./subresellers.php");
            exit;
        } else {
            $_STATUS = 1;
        }
    }
}

if (isset($_GET["id"])) {
    $rSubreseller = getSubresellerSetup($_GET["id"]);
    if (!$rSubreseller) {
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
                                        <a href="./subresellers.php">
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                                <?= $_["back_to_subresellers"] ?></li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?= $_["subreseller_setup"] ?></h4>
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
                                    <?= $_["there_was_an_error"] ?>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./subreseller_setup.php<?php if (isset($rSubreseller)) {
                                        echo "?id=" . $rSubreseller["id"];
                                    } ?>" method="POST" id="subreseller_form">
                                        <?php if (isset($rSubreseller)) { ?>
                                            <input type="hidden" name="edit" value="<?= $rSubreseller["id"] ?>" />
                                        <?php } ?>
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#setup" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"> <?= $_["setup"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="setup">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <p class="sub-header">
                                                                <?= $_["select_a_master_reseller"] ?>
                                                            </p>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="reseller">
                                                                    <?= $_["master_group"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="reseller" id="reseller"
                                                                        class="form-control select2"
                                                                        data-toggle="select2">
                                                                        <?php foreach (getMemberGroups() as $rGroup) {
                                                                            if ($rGroup["is_reseller"] == 1) { ?>
                                                                                <option <?php if (isset($rSubreseller)) {
                                                                                    if (intval($rSubreseller["reseller"]) == intval($rGroup["group_id"])) {
                                                                                        echo "selected ";
                                                                                    }
                                                                                } ?>value="<?= $rGroup["group_id"] ?>"><?= $rGroup["group_name"] ?></option>
                                                                            <?php }
                                                                        } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="subreseller"> <?= $_["subreseller"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="subreseller" id="subreseller"
                                                                        class="form-control select2"
                                                                        data-toggle="select2">
                                                                        <?php foreach (getMemberGroups() as $rGroup) {
                                                                            if ($rGroup["is_reseller"] == 1) { ?>
                                                                                <option <?php if (isset($rSubreseller)) {
                                                                                    if (intval($rSubreseller["subreseller"]) == intval($rGroup["group_id"])) {
                                                                                        echo "selected ";
                                                                                    }
                                                                                } ?>value="<?= $rGroup["group_id"] ?>"><?= $rGroup["group_name"] ?></option>
                                                                            <?php }
                                                                        } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="next list-inline-item float-right">
                                                            <input name="submit_subreseller" type="submit"
                                                                class="btn btn-primary" value=" <?= $_["setup"] ?>" />
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
            <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
            <script src="assets/libs/treeview/jstree.min.js"></script>
            <script src="assets/js/pages/treeview.init.js"></script>
            <script src="assets/js/pages/form-wizard.init.js"></script>
            <script src="assets/js/app.min.js"></script>

            <script>
                $(document).ready(function () {
                    $('select.select2').select2({
                        width: '100%'
                    })

                    $(window).keypress(function (event) {
                        if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                    });

                    $("form").attr('autocomplete', 'off');
                });
            </script>
            </body>

            </html>