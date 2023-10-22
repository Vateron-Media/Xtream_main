<?php
include "session.php"; include "functions.php";

if (isset($_POST["submit_profile"])) {
	if ((strlen($_POST["password"]) < intval($rAdminSettings["pass_length"])) && (intval($rAdminSettings["pass_length"]) > 0)) {
		$_STATUS = 1;
	}
	if (((strlen($_POST["email"]) == 0) OR (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))) && (($rAdminSettings["change_own_email"]) OR ($rPermissions["is_admin"]))) {
		$_STATUS = 2;
	}
	if ((strlen($_POST["reseller_dns"]) > 0) && (!filter_var("http://".$_POST["reseller_dns"], FILTER_VALIDATE_URL))) {
		$_STATUS = 3;
	}
	if (isset($_POST["sidebar"])) {
        $rSidebar = true;
    } else {
        $rSidebar = false;
    }
	if (isset($_POST["dark_mode"])) {
        $rDarkMode = true;
    } else {
        $rDarkMode = false;
    }
	if (isset($_POST["expanded_sidebar"])) {
        $rExpanded = true;
    } else {
        $rExpanded = false;
    }
    if (!isset($_STATUS)) {
		if ((strlen($_POST["password"]) > 0) && (($rAdminSettings["change_own_password"]) OR ($rPermissions["is_admin"]))) {
			$rPassword = cryptPassword($_POST["password"]);
		} else {
			$rPassword = $rUserInfo["password"];
		}
        if (($rAdminSettings["change_own_email"]) OR ($rPermissions["is_admin"])) {
            $rEmail = $_POST["email"];
        } else {
            $rEmail = $rUserInfo["email"];
        }
        if (($rAdminSettings["change_own_dns"]) OR ($rPermissions["is_admin"])) {
            $rDNS = $_POST["reseller_dns"];
        } else {
            $rDNS = $rUserInfo["reseller_dns"];
        }
		$db->query("UPDATE `reg_users` SET `password` = '".ESC($rPassword)."', `email` = '".ESC($rEmail)."', `reseller_dns` = '".ESC($rDNS)."', `dark_mode` = ".intval($rDarkMode).", `sidebar` = ".intval($rSidebar).", `expanded_sidebar` = ".intval($rExpanded)." WHERE `id` = ".intval($rUserInfo["id"]).";");
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
        <div class="content-page"><div class="content boxed-layout"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title"><?=$_["profile"]?></h4>
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
                            <?=$_["profile_success"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=str_replace("{num}", $rAdminSettings["pass_length"], $_["profile_fail_1"])?>
                        </div>
						<?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["profile_fail_2"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["profile_fail_3"]?>
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
                                                    <span class="d-none d-sm-inline"><?=$_["details"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="user-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="username"><?=$_["username"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="username" name="username" value="<?=htmlspecialchars($rUserInfo["username"])?>" readonly>
                                                            </div>
                                                        </div>
                                                        <?php if (($rPermissions["is_admin"]) OR ($rAdminSettings["change_own_password"])) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="password"><?=$_["change_password"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="password" name="password" value="">
                                                            </div>
                                                        </div>
                                                        <?php }
                                                        if (($rPermissions["is_admin"]) OR ($rAdminSettings["change_own_email"])) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="email"><?=$_["email_address"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="email" id="email" class="form-control" name="email" required value="<?=htmlspecialchars($rUserInfo["email"])?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
														<?php }
                                                        if (($rPermissions["is_reseller"]) && ($rAdminSettings["change_own_dns"])) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="reseller_dns"><?=$_["reseller_dns"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="reseller_dns" name="reseller_dns" value="<?=htmlspecialchars($rUserInfo["reseller_dns"])?>">
                                                            </div>
                                                        </div>
														<?php } ?>
														<div class="form-group row mb-4">
															<label class="col-md-4 col-form-label" for="sidebar"><?=$_["sidebar_nav"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="sidebar" id="sidebar" type="checkbox"<?php if ($rUserInfo["sidebar"] == 1) { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
															<label class="col-md-4 col-form-label" for="expanded_sidebar"><?=$_["expanded_sidebar"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="expanded_sidebar" id="expanded_sidebar" type="checkbox"<?php if ($rUserInfo["expanded_sidebar"] == 1) { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
															<label class="col-md-4 col-form-label" for="dark_mode"><?=$_["dark_mode"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="dark_mode" id="dark_mode" type="checkbox"<?php if ($rUserInfo["dark_mode"] == 1) { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item float-right">
                                                        <input name="submit_profile" type="submit" class="btn btn-primary" value="<?=$_["save_profile"]?>" />
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
            $('select.select2').select2({width: '100%'})
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
              var switchery = new Switchery(html);
            });
            
            $(document).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
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