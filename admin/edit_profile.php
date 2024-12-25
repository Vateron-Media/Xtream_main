<?php
include "session.php";
include "functions.php";

$nabillangues = array("" => "Default - EN", "fr" => "French", "es" => "Spanish", "it" => "Italian", "pt" => "Portuguese", "ru" => "Русский");

if (isset(ipTV_lib::$request["submit_profile"])) {
    if ((strlen(ipTV_lib::$request["password"]) < intval($rSettings["pass_length"])) && (intval($rSettings["pass_length"]) > 0)) {
        $_STATUS = 1;
    }
    if (((strlen(ipTV_lib::$request["email"]) == 0) or (!filter_var(ipTV_lib::$request["email"], FILTER_VALIDATE_EMAIL))) && (($rAdminSettings["change_own_email"]) or ($rPermissions["is_admin"]))) {
        $_STATUS = 2;
    }
    if ((strlen(ipTV_lib::$request["reseller_dns"]) > 0) && (!filter_var("http://" . ipTV_lib::$request["reseller_dns"], FILTER_VALIDATE_URL))) {
        $_STATUS = 3;
    }
    if (isset(ipTV_lib::$request["sidebar"])) {
        $rSidebar = true;
    } else {
        $rSidebar = false;
    }
    if (isset(ipTV_lib::$request["dark_mode"])) {
        $rDarkMode = true;
    } else {
        $rDarkMode = false;
    }
    if (isset(ipTV_lib::$request["expanded_sidebar"])) {
        $rExpanded = true;
    } else {
        $rExpanded = false;
    }
    if (!isset($_STATUS)) {
        if ((strlen(ipTV_lib::$request["password"]) > 0) && (($rAdminSettings["change_own_password"]) or ($rPermissions["is_admin"]))) {
            $rPassword = cryptPassword(ipTV_lib::$request["password"]);
        } else {
            $rPassword = $rUserInfo["password"];
        }
        if (($rAdminSettings["change_own_email"]) or ($rPermissions["is_admin"])) {
            $rEmail = ipTV_lib::$request["email"];
        } else {
            $rEmail = $rUserInfo["email"];
        }
        if (($rAdminSettings["change_own_dns"]) or ($rPermissions["is_admin"])) {
            $rDNS = ipTV_lib::$request["reseller_dns"];
        } else {
            $rDNS = $rUserInfo["reseller_dns"];
        }
        if (($rAdminSettings["change_own_lang"]) or ($rPermissions["is_admin"])) {
            $bob = ipTV_lib::$request["default_lang"];
        } else {
            $bob = $rUserInfo["default_lang"];
        }
        if (isset(ipTV_lib::$request['port_admin']) && $rPermissions["is_admin"] && ipTV_lib::$request['port_admin'] != $rSettings["port_admin"]) {
            $portadmin = ipTV_lib::$request["port_admin"];
            exec("sed -i 's/listen " . $rSettings["port_admin"] . "/listen " . intval(ipTV_lib::$request["port_admin"]) . "/g' /home/xtreamcodes/bin/nginx/conf/nginx.conf");
            if ($rSettings["is_ufw"] == 1) {
                exec("sudo ufw allow " . intval(ipTV_lib::$request["port_admin"]) . " && sudo ufw delete allow " . $rSettings["port_admin"] . "");
            }
            exec("sudo /home/xtreamcodes/bin/nginx/sbin/nginx -s reload");
            sleep(1);
        } else {
            $portadmin = $rSettings["port_admin"];
        }
        ipTV_lib::setSettings(["port_admin" => $portadmin]);
        $ipTV_db_admin->query("UPDATE `reg_users` SET `password` = '" . $rPassword . "', `email` = '" . $rEmail . "', `reseller_dns` = '" . $rDNS . "', `default_lang` = '" . $bob . "', `dark_mode` = " . intval($rDarkMode) . ", `sidebar` = " . intval($rSidebar) . ", `expanded_sidebar` = " . intval($rExpanded) . " WHERE `id` = " . intval($rUserInfo["id"]) . ";");
        $rUserInfo = getRegisteredUser($rUserInfo["id"]);
        $rAdminSettings["dark_mode"] = $rUserInfo["dark_mode"];
        $rAdminSettings["expanded_sidebar"] = $rUserInfo["expanded_sidebar"];
        $rSettings["sidebar"] = $rUserInfo["sidebar"];
        $_STATUS = 0;
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
                                <h4 class="page-title"><?= $_["profile"] ?></h4>
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
                                    <?= $_["profile_success"] ?>
                                </div>
                            <?php } elseif ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= str_replace("{num}", $rSettings["pass_length"], $_["profile_fail_1"]) ?>
                                </div>
                            <?php } elseif ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["profile_fail_2"] ?>
                                </div>
                            <?php } elseif ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["profile_fail_3"] ?>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./edit_profile.php" method="POST" id="edit_profile_form" data-parsley-validate="">
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="user-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="username"><?= $_["username"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($rUserInfo["username"]) ?>" readonly>
                                                                </div>
                                                            </div>
                                                            <?php if (($rPermissions["is_admin"]) or ($rAdminSettings["change_own_password"])) { ?>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="password"><?= $_["change_password"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="password" name="password" value="">
                                                                    </div>
                                                                </div>
                                                            <?php }
                                                            if (($rPermissions["is_admin"]) or ($rAdminSettings["change_own_email"])) { ?>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="email"><?= $_["email_address"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="email" id="email" class="form-control" name="email" required value="<?= htmlspecialchars($rUserInfo["email"]) ?>" required data-parsley-trigger="change">
                                                                    </div>
                                                                </div>
                                                            <?php }
                                                            if (($rPermissions["is_reseller"]) && ($rAdminSettings["change_own_dns"])) { ?>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="reseller_dns"><?= $_["reseller_dns"] ?></label>
                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control" id="reseller_dns" name="reseller_dns" value="<?= htmlspecialchars($rUserInfo["reseller_dns"]) ?>">
                                                                    </div>
                                                                </div>
                                                            <?php }
                                                            if (($rPermissions["is_admin"]) or ($rAdminSettings["change_own_lang"])) { ?>
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label" for="default_lang">UI Language</label>
                                                                    <div class="col-md-8">
                                                                        <select type="default_lang" name="default_lang" id="default_lang" class="form-control" data-toggle="select2">
                                                                            <?php foreach ($nabillangues as $rKey => $rLanguage) { ?>
                                                                                <option<?php if ($rUserInfo["default_lang"] == $rKey) {
                                                                                            echo " selected";
                                                                                       } ?> value="<?= $rKey ?>"><?= $rLanguage ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="sidebar"><?= $_["sidebar_nav"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input name="sidebar" id="sidebar" type="checkbox" <?php if ($rUserInfo["sidebar"] == 1) {
                                                                                                                            echo "checked ";
                                                                                                                       } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-4 col-form-label" for="expanded_sidebar"><?= $_["expanded_sidebar"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input name="expanded_sidebar" id="expanded_sidebar" type="checkbox" <?php if ($rUserInfo["expanded_sidebar"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                         } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="dark_mode"><?= $_["dark_mode"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input name="dark_mode" id="dark_mode" type="checkbox" <?php if ($rUserInfo["dark_mode"] == 1) {
                                                                                                                                echo "checked ";
                                                                                                                           } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="list-inline-item float-right">
                                                            <input name="submit_profile" type="submit" class="btn btn-primary" value="<?= $_["save_profile"] ?>" />
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
                <script src="assets/libs/jquery-tabledit/jquery.tabledit.min.js"></script>
                <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
                <script src="assets/libs/moment/moment.min.js"></script>
                <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
                <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
                <script src="assets/libs/treeview/jstree.min.js"></script>
                <script src="assets/js/pages/treeview.init.js"></script>
                <script src="assets/js/pages/form-wizard.init.js"></script>
                <script src="assets/js/pages/form-remember.js"></script>
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

                        $(document).keypress(function(event) {
                            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                        });

                        $("form").attr('autocomplete', 'off');

                        formCache.init();
                    });

                    $(window).bind('beforeunload', function() {
                        formCache.save();
                    });
                </script>
                </body>

                </html>