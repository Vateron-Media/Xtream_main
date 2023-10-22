<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_reguser")) && (!hasPermissions("adv", "edit_reguser")))) { exit; }

if (isset($_POST["submit_user"])) {
    if (isset($_POST["edit"])) {
		if (!hasPermissions("adv", "edit_reguser")) { exit; }
        $rArray = getRegisteredUser($_POST["edit"]);
        unset($rArray["id"]);
    } else {
		if (!hasPermissions("adv", "add_reguser")) { exit; }
        $rArray = Array("username" => "", "password" => "", "email" => "", "member_group_id" => 1, "verified" => 0, "credits" => 0, "notes" => "", "status" => 1, "owner_id" => 0);
    }
    if ((strlen($_POST["username"]) == 0) OR ((strlen($_POST["email"]) == 0))) {
        $_STATUS = 1;
    }
    if (strlen($_POST["password"]) > 0) {
        $rArray["password"] = cryptPassword($_POST["password"]);
    } else if (!isset($_POST["edit"])) {
        $_STATUS = 1;
    }
    if (!isset($_STATUS)) {
        $rOverride = Array();
        foreach($_POST as $rKey => $rValue) {
            if (substr($rKey, 0, 9) == "override_") {
                $rID = intval(explode("override_", $rKey)[1]);
                $rCredits = $rValue;
                $rOverride[$rID] = Array("assign" => 1, "official_credits" => $rCredits);
                unset($_POST[$rKey]);
            }
        }
        $rArray["override_packages"] = json_encode($rOverride);
        if (isset($_POST["verified"])) {
            $rArray["verified"] = 1;
            unset($_POST["verified"]);
        } else {
            $rArray["verified"] = 0;
        }
        unset($_POST["password"]);
        if ($rArray["credits"] <> $_POST["credits"]) {
            $rCreditsAdjustment = $_POST["credits"] - $rArray["credits"];
            $rReason = $_POST["credits_reason"];
        }
        foreach($_POST as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
        }
        $rCols = "`".ESC(implode('`,`', array_keys($rArray)))."`";
        foreach (array_values($rArray) as $rValue) {
            isset($rValues) ? $rValues .= ',' : $rValues = '';
            if (is_array($rValue)) {
                $rValue = json_encode($rValue);
            }
            if (is_null($rValue)) {
                $rValues .= 'NULL';
            } else {
                $rValues .= '\''.ESC($rValue).'\'';
            }
        }
        if (isset($_POST["edit"])) {
            $rCols = "`id`,".$rCols;
            $rValues = ESC($_POST["edit"]).",".$rValues;
        }
        $rQuery = "REPLACE INTO `reg_users`(".$rCols.") VALUES(".$rValues.");";
        if ($db->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
            } else {
                $rInsertID = $db->insert_id;
            }
            if (isset($rCreditsAdjustment)) {
                $db->query("INSERT INTO `credits_log`(`target_id`, `admin_id`, `amount`, `date`, `reason`) VALUES(".$rInsertID.", ".intval($rUserInfo["id"]).", ".ESC($rCreditsAdjustment).", ".intval(time()).", '".ESC($rReason)."');");
            }
            header("Location: ./reg_user.php?id=".$rInsertID); exit;
        } else {
            $_STATUS = 2;
        }
    }
}

if (isset($_GET["id"])) {
    $rUser = getRegisteredUser($_GET["id"]);
    if ((!$rUser) OR (!hasPermissions("adv", "edit_reguser"))) {
        exit;
    }
} else if (!hasPermissions("adv", "add_reguser")) { exit; }

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
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./reg_users.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Registered Users</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rUser)) { echo "Edit"; } else { echo "Add"; } ?> Registered User</h4>
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
                            User operation was completed successfully.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Please enter a username, password and email address for this user.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./reg_user.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="reg_user_form" data-parsley-validate="">
                                    <?php if (isset($rUser)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rUser["id"]?>" />
                                    <input type="hidden" name="status" value="<?=$rUser["status"]?>" />
                                    <?php } ?>
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#package-override" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-package mr-1"></i>
                                                    <span class="d-none d-sm-inline">Package Override</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="user-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="username">Username</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="username" name="username" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["username"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="password"><?php if (isset($rUser)) { ?>Change <?php } ?>Password</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="password" name="password" <?php if (!isset($rUser)) { echo 'value="'.generateString(10).'" required data-parsley-trigger="change"'; } else { echo 'value=""'; } ?>>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="email">Email Address</label>
                                                            <div class="col-md-8">
                                                                <input type="email" id="email" class="form-control" name="email" required value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["email"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="member_group_id">Member Group</label>
                                                            <div class="col-md-8">
                                                                <select name="member_group_id" id="member_group_id" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (getMemberGroups() as $rGroup) { ?>
                                                                    <option <?php if (isset($rUser)) { if (intval($rUser["member_group_id"]) == intval($rGroup["group_id"])) { echo "selected "; } } ?>value="<?=$rGroup["group_id"]?>"><?=htmlspecialchars($rGroup["group_name"])?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="owner_id">Owner</label>
                                                            <div class="col-md-8">
                                                                <select name="owner_id" id="owner_id" class="form-control select2" data-toggle="select2">
                                                                    <option value="0">No Owner</option>
                                                                    <?php foreach (getRegisteredUsers(0) as $rRegUser) { ?>
                                                                    <option <?php if (isset($rUser)) { if (intval($rUser["owner_id"]) == intval($rRegUser["id"])) { echo "selected "; } } else { if (intval($rUserInfo["id"]) == intval($rRegUser["id"])) { echo "selected "; } } ?>value="<?=$rRegUser["id"]?>"><?=$rRegUser["username"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="verified">Verified</label>
                                                            <div class="col-md-2">
                                                                <input name="verified" id="verified" type="checkbox"<?php if ((isset($rUser)) && ($rUser["verified"] == 1)) { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="credits">Credits</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control text-center" id="credits" onkeypress="return isNumberKey(event)" name="credits" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["credits"]); } else { echo "0"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4" style="display: none;" id="credits_reason_div">
                                                            <label class="col-md-4 col-form-label" for="credits_reason">Reason for Credits Adjustment</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="credits_reason" name="credits_reason" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="reseller_dns">Reseller DNS</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="reseller_dns" name="reseller_dns" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["reseller_dns"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="notes">Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder=""><?php if (isset($rUser)) { echo htmlspecialchars($rUser["notes"]); } ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item float-right">
                                                        <input name="submit_user" type="submit" class="btn btn-primary" value="<?php if (isset($rUser)) { echo "Edit"; } else { echo "Add"; } ?> User" />
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="package-override">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            Leave the override cell blank to disable package override for the selected package.
                                                        </p>
                                                        <table class="table table-centered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-center">#</th>
                                                                    <th>Package</th>
                                                                    <th class="text-center">Credits</th>
                                                                    <th class="text-center">Override</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                if (isset($rUser)) {
                                                                    $rOverride = json_decode($rUser["override_packages"], True);
                                                                } else {
                                                                    $rOverride = Array();
                                                                }
                                                                foreach (getPackages($rUser["member_group_id"]) as $rPackage) {
                                                                if ($rPackage["is_official"]) { ?>
                                                                <tr>
                                                                    <td class="text-center"><?=$rPackage["id"]?></td>
                                                                    <td><?=$rPackage["package_name"]?></td>
                                                                    <td class="text-center"><?=$rPackage["official_credits"]?></td>
                                                                    <td align="center">
                                                                        <input class="form-control" onkeypress="return isNumberKey(event)" name="override_<?=$rPackage["id"]?>" type="text" value="<?php if (isset($rOverride[$rPackage["id"]])) { echo htmlspecialchars($rOverride[$rPackage["id"]]["official_credits"]); } ?>" style="width:100px;" class="text-center" />
                                                                    </td>
                                                                </tr>
                                                                <?php }
                                                                } ?>
                                                            </tbody>
                                                        </table><br/><br/>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item float-right">
                                                        <input name="submit_user" type="submit" class="btn btn-primary" value="<?php if (isset($rUser)) { echo "Edit"; } else { echo "Add"; } ?> User" />
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
        
        function selectAll() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', true);
            });
        }
        
        function selectNone() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', false);
            });
        }
        function isValidDate(dateString) {
              var regEx = /^\d{4}-\d{2}-\d{2}$/;
              if(!dateString.match(regEx)) return false;  // Invalid format
              var d = new Date(dateString);
              var dNum = d.getTime();
              if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
              return d.toISOString().slice(0,10) === dateString;
        }
        
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            } else {
                return true;
            }
        }
        
        $(document).ready(function() {
            $('select.select2').select2({width: '100%'})
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
              var switchery = new Switchery(html);
            });
            
            $('#exp_date').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minDate: new Date(),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
            
            $("#no_expire").change(function() {
                if ($(this).prop("checked")) {
                    $("#exp_date").prop("disabled", true);
                } else {
                    $("#exp_date").removeAttr("disabled");
                }
            });
            
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            
            $("#credits").change(function() {
                $("#credits_reason_div").show();
            });
            
            $("#max_connections").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');

            formCache.init();
            <?php if (isset($_STATUS)) {
                if ($_STATUS == 0) {
                    echo 'formCache.clear();';
                } else {
                    echo 'formCache.fetch();';
                }
            } ?>
        });

        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>
    </body>
</html>