<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "mass_edit_users"))) { exit; }

if (isset($_POST["submit_user"])) {
    $rArray = Array();
    foreach (Array("is_stalker", "is_mag", "is_e2", "is_restreamer", "is_trial") as $rItem) {
        if (isset($_POST["c_".$rItem])) {
            if (isset($_POST[$rItem])) {
                $rArray[$rItem] = 1;
            } else {
                $rArray[$rItem] = 0;
            }
        }
    }
    if (isset($_POST["c_admin_notes"])) {
        $rArray["admin_notes"] = $_POST["admin_notes"];
    }
    if (isset($_POST["c_reseller_notes"])) {
        $rArray["reseller_notes"] = $_POST["reseller_notes"];
    }
    if (isset($_POST["c_forced_country"])) {
        $rArray["forced_country"] = $_POST["forced_country"];
    }
    if (isset($_POST["c_member_id"])) {
        $rArray["member_id"] = intval($_POST["member_id"]);
    }
    if (isset($_POST["c_force_server_id"])) {
        $rArray["force_server_id"] = intval($_POST["force_server_id"]);
    }
    if (isset($_POST["c_max_connections"])) {
        $rArray["max_connections"] = intval($_POST["max_connections"]);
    }
    if (isset($_POST["c_exp_date"])) {
        if (isset($_POST["no_expire"])) {
            $rArray["exp_date"] = "NULL";
        } else {
            try {
                $rDate = new DateTime($_POST["exp_date"]);
                $rArray["exp_date"] = $rDate->format("U");
            } catch (Exception $e) {
            }
        }
    }
    if (isset($_POST["c_bouquets"])) {
        $rArray["bouquet"] = Array();
		foreach (json_decode($_POST["bouquets_selected"], True) as $rBouquet) {
			if (is_numeric($rBouquet)) {
				$rArray["bouquet"][] = intval($rBouquet);
			}
		}       
        $rArray["bouquet"] = sortArrayByArray($rArray["bouquet"], array_keys(getBouquetOrder()));
        $rArray["bouquet"] = "[".join(",", $rArray["bouquet"])."]";
    }
    $rUsers = json_decode($_POST["users_selected"], True);
    if (count($rUsers) > 0) {
        foreach ($rUsers as $rUser) {
            $rQueries = Array();
            foreach ($rArray as $rKey => $rValue) {
                $rQueries[] = "`".ESC($rKey)."` = '".ESC($rValue)."'";
            }
            if (count($rQueries) > 0) {
                $rQueryString = join(",", $rQueries);
                $rQuery = "UPDATE `users` SET ".$rQueryString." WHERE `id` = ".intval($rUser).";";
                $db->query($rQuery);
            }
            if (isset($_POST["c_access_output"])) {
                $db->query("DELETE FROM `user_output` WHERE `user_id` = ".intval($rUser).";");
                foreach ($_POST["access_output"] as $rOutputID) {
                    $db->query("INSERT INTO `user_output`(`user_id`, `access_output_id`) VALUES(".intval($rUser).", ".intval($rOutputID).");");
                }
            }
        }
        if ((isset($_POST["c_lock_device"])) OR (isset($_POST["reset_stb_lock"]))) {
            $rResult = $db->query("SELECT `mag_id`, `user_id` FROM `mag_devices`;");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    if (in_array($rRow["user_id"], $rUsers)) {
                        if (isset($_POST["reset_stb_lock"])) {
                            resetSTB($rRow["mag_id"]);
                        }
                        if (isset($_POST["c_lock_device"])) {
                            if (isset($_POST["lock_device"])) {
                                $db->query("UPDATE `mag_devices` SET `lock_device` = 1 WHERE `mag_id` = ".intval($rRow["mag_id"]).";");
                            } else {
                                $db->query("UPDATE `mag_devices` SET `lock_device` = 0 WHERE `mag_id` = ".intval($rRow["mag_id"]).";");
                            }
                        }
                    }
                }
            }
        }
    }
    $_STATUS = 0;
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
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./users.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Users</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title">Mass Edit Users <small id="selected_count"></small></h4>
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
                            Mass edit of users was successfully executed!
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./user_mass.php" method="POST" id="user_form">
                                    <input type="hidden" name="users_selected" id="users_selected" value="" />
                                    <input type="hidden" name="bouquets_selected" id="bouquets_selected" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#user-selection" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-group mr-1"></i>
                                                    <span class="d-none d-sm-inline">Users</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#bouquets" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-flower-tulip mr-1"></i>
                                                    <span class="d-none d-sm-inline">Bouquets</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="user-selection">
                                                <div class="row">
                                                    <div class="col-md-3 col-6">
                                                        <input type="text" class="form-control" id="user_search" value="" placeholder="Search Users...">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select id="reseller_search" class="form-control" data-toggle="select2">
                                                            <option value="" selected>All Resellers</option>
                                                            <?php foreach (getRegisteredUsers() as $rRegisteredUser) { ?>
                                                            <option value="<?=$rRegisteredUser["id"]?>"><?=$rRegisteredUser["username"]?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <select id="filter" class="form-control" data-toggle="select2">
                                                            <option value="" selected>No Filter</option>
                                                            <option value="1">Active</option>
                                                            <option value="2">Disabled</option>
                                                            <option value="3">Banned</option>
                                                            <option value="4">Expired</option>
                                                            <option value="5">Trial</option>
															<option value="6">MAG Device</option>
															<option value="7">Enigma Device</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-8">
                                                        <select id="show_entries" class="form-control" data-toggle="select2">
                                                            <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                            <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1 col-2">
                                                        <button type="button" class="btn btn-info waves-effect waves-light" onClick="toggleUsers()">
                                                            <i class="mdi mdi-selection"></i>
                                                        </button>
                                                    </div>
                                                    <table id="datatable-mass" class="table table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="text-center">ID</th>
                                                                <th>Username</th>
                                                                <th></th>
                                                                <th>Reseller</th>
                                                                <th class="text-center">Status</th>
                                                                <th class="text-center">Online</th>
                                                                <th class="text-center">Trial</th>
                                                                <th class="text-center">Expiration</th>
                                                                <th></th>
                                                                <th class="text-center">Conns.</th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="user-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            To mass edit any of the below options, tick the checkbox next to it and change the input value.
                                                        </p>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="member_id" name="c_member_id">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="member_id">Owner</label>
                                                            <div class="col-md-8">
                                                                <select disabled name="member_id" id="member_id" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (getRegisteredUsers() as $rRegisteredUser) { ?>
                                                                    <option value="<?=$rRegisteredUser["id"]?>"><?=$rRegisteredUser["username"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="max_connections" name="c_max_connections">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="max_connections">Max Connections</label>
                                                            <div class="col-md-2">
                                                                <input disabled type="text" class="form-control" id="max_connections" name="max_connections" value="1">
                                                            </div>
                                                            <label class="col-md-2 col-form-label" for="exp_date">Expiry Date</label>
                                                            <div class="col-md-2">
                                                                <input disabled type="text" disabled class="form-control text-center date" id="exp_date" name="exp_date" value="" data-toggle="date-picker" data-single-date-picker="true">
                                                            </div>
                                                            <div class="col-md-1">
                                                                <div class="custom-control custom-checkbox mt-1">
                                                                    <input disabled type="checkbox" class="custom-control-input" id="no_expire" name="no_expire">
                                                                    <label class="custom-control-label" for="no_expire">Never</label>
                                                                </div>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="exp_date" name="c_exp_date">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="admin_notes" name="c_admin_notes">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="admin_notes">Admin Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea disabled id="admin_notes" name="admin_notes" class="form-control" rows="3" placeholder=""></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="reseller_notes" name="c_reseller_notes">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="reseller_notes">Reseller Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea disabled id="reseller_notes" name="reseller_notes" class="form-control" rows="3" placeholder=""></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="force_server_id" name="c_force_server_id">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="force_server_id">Forced Connection</label>
                                                            <div class="col-md-8">
                                                                <select disabled name="force_server_id" id="force_server_id" class="form-control select2" data-toggle="select2">
                                                                    <option selected value="0">Disabled</option>
                                                                    <?php foreach ($rServers as $rServer) { ?>
                                                                    <option value="<?=$rServer["id"]?>"><?=$rServer["server_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="is_stalker" data-type="switch" name="c_is_stalker">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="is_stalker">Ministra Portal</label>
                                                            <div class="col-md-2">
                                                                <input disabled name="is_stalker" id="is_stalker" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="is_restreamer">Restreamer</label>
                                                            <div class="col-md-2">
                                                                <input disabled name="is_restreamer" id="is_restreamer" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="is_restreamer" data-type="switch" name="c_is_restreamer">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="is_e2" data-type="switch" name="c_is_e2">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="is_e2">Enigma Device</label>
                                                            <div class="col-md-2">
                                                                <input disabled name="is_e2" id="is_e2" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="is_mag">MAG Device</label>
                                                            <div class="col-md-2">
                                                                <input disabled name="is_mag" id="is_mag" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="is_mag" data-type="switch" name="c_is_mag">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="lock_device" data-type="switch" name="c_lock_device">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="lock_device">MAG STB Lock</label>
                                                            <div class="col-md-2">
                                                                <input disabled name="lock_device" id="lock_device" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-5 col-form-label" for="reset_stb_lock">Reset STB Lock</label>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" name="reset_stb_lock">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="is_trial" data-type="switch" name="c_is_trial">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="is_trial">Trial Account</label>
                                                            <div class="col-md-2">
                                                                <input disabled name="is_trial" id="is_trial" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-2 col-form-label" for="access_output">Access Output</label>
                                                            <div class="col-md-3">
                                                                <?php foreach (getOutputs() as $rOutput) { ?>
                                                                <div class="checkbox form-check-inline">
                                                                    <input disabled class="output" data-size="large" type="checkbox" id="access_output_<?=$rOutput["access_output_id"]?>" name="access_output[]" value="<?=$rOutput["access_output_id"]?>" checked>
                                                                    <label for="access_output_<?=$rOutput["access_output_id"]?>"> <?=$rOutput["output_name"]?> </label>
                                                                </div>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="access_output" data-type="output" name="c_access_output">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="forced_country" name="c_forced_country">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="forced_country">Forced Country</label>
                                                            <div class="col-md-8">
                                                                <select disabled name="forced_country" id="forced_country" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach ($rCountries as $rCountry) { ?>
                                                                    <option value="<?=$rCountry["id"]?>"><?=$rCountry["name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="bouquets">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-bouquets" class="table table-borderless mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th class="text-center">ID</th>
                                                                        <th>Bouquet Name</th>
                                                                        <th class="text-center">Streams</th>
                                                                        <th class="text-center">Series</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                    <tr>
                                                                        <td class="text-center"><?=$rBouquet["id"]?></td>
                                                                        <td><?=$rBouquet["bouquet_name"]?></td>
                                                                        <td class="text-center"><?=count(json_decode($rBouquet["bouquet_channels"], True))?></td>
                                                                        <td class="text-center"><?=count(json_decode($rBouquet["bouquet_series"], True))?></td>
                                                                    </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                            </table>
                                                            <div class="custom-control col-md-12 custom-checkbox text-center" style="margin-top:20px;">
                                                                <input type="checkbox" class="custom-control-input" id="c_bouquets" data-name="bouquets" data-type="bouquet" name="c_bouquets">
                                                                <label class="custom-control-label" for="c_bouquets">Tick this box to apply the above bouquets to all selected users. Selecting none will remove bouquets from users.</label>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" onClick="toggleBouquets()" class="btn btn-info">Toggle Bouquets</a>
                                                        <input name="submit_user" type="submit" class="btn btn-primary" value="Mass Edit" />
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
        <script src="assets/libs/jquery-ui/jquery-ui.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
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
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var rSwitches = [];
        var rSelected = [];
        var rBouquets = [];
        
        function getReseller() {
            return $("#reseller_search").val();
        }
        function getFilter() {
            return $("#filter").val();
        }
        function toggleUsers() {
            $("#datatable-mass tr").each(function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.splice($.inArray($(this).find("td:eq(0)").html(), window.rSelected), 1);
                    }
                } else {            
                    $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.push($(this).find("td:eq(0)").html());
                    }
                }
            });
            $("#selected_count").html(" - " + window.rSelected.length + " selected")
        }
        function toggleBouquets() {
            $("#datatable-bouquets tr").each(function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rBouquets.splice($.inArray($(this).find("td:eq(0)").html(), window.rBouquets), 1);
                    }
                } else {            
                    $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rBouquets.push($(this).find("td:eq(0)").html());
                    }
                }
                if (!$("#c_bouquets").is(":checked")) {
                    $("#c_bouquets").prop('checked', true);
                }
            });
        }
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
            $('select').select2({width: '100%'})
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
                var switchery = new Switchery(html);
                window.rSwitches[$(html).attr("id")] = switchery;
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
            $("#user_form").submit(function(e){
                var rBouquets = [];
                $("#datatable-bouquets tr.selected").each(function() {
                    rBouquets.push($(this).find("td:eq(0)").html());
                });
                $("#bouquets_selected").val(JSON.stringify(rBouquets));
                $("#users_selected").val(JSON.stringify(window.rSelected));
                if (window.rSelected.length == 0) {
                    e.preventDefault();
                    $.toast("Select at least one user to edit.");
                }
            });
            $("input[type=checkbox].activate").change(function() {
                if ($(this).is(":checked")) {
                    if ($(this).data("type") == "switch") {
                        window.rSwitches[$(this).data("name")].enable();
                    } else if ($(this).data("type") == "output") {
                        $(".output").each(function() {
                            $(this).prop("disabled", false);
                        });
                    } else if ($(this).data("type") == "bouquet") {
                        $(".bouquet-checkbox").each(function() {
                            $(this).prop("disabled", false);
                        });
                    } else {
                        if ($(this).data("name") == "exp_date") {
                            $("#no_expire").prop("disabled", false);
                            if (!$("#no_expire").is(":checked")) {
                                $("#exp_date").prop("disabled", false);
                            }
                        } else {
                            $("#" + $(this).data("name")).prop("disabled", false);
                        }
                    }
                } else {
                    if ($(this).data("type") == "switch") {
                        window.rSwitches[$(this).data("name")].disable();
                    } else if ($(this).data("type") == "output") {
                        $(".output").each(function() {
                            $(this).prop("disabled", true);
                        });
                    } else if ($(this).data("type") == "bouquet") {
                        $(".bouquet-checkbox").each(function() {
                            $(this).prop("disabled", true);
                        });
                    } else {
                        if ($(this).data("name") == "exp_date") {
                            $("#no_expire").prop("disabled", true);
                            if (!$("#no_expire").is(":checked")) {
                                $("#exp_date").prop("disabled", true);
                            }
                        } else {
                            $("#" + $(this).data("name")).prop("disabled", true);
                        }
                    }
                }
            });
            $(".clockpicker").clockpicker();
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $("#probesize_ondemand").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#delay_minutes").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#tv_archive_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            rTable = $("#datatable-mass").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "users",
                        d.filter = getFilter(),
                        d.reseller = getReseller(),
						d.showall = true
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,4,6,7,9]},
                    {"visible": false, "targets": [2,5,8,10,11]}
                ],
                "rowCallback": function(row, data) {
                    if ($.inArray(data[0], window.rSelected) !== -1) {
                        $(row).addClass("selected");
                    }
                },
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>
            });
            bTable = $("#datatable-bouquets").DataTable({
                columnDefs: [
                    {"className": "dt-center", "targets": [0,2,3]}
                ],
                "rowCallback": function(row, data) {
                    if ($.inArray(data[0], window.rBouquets) !== -1) {
                        $(row).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    }
                },
                paging: false,
                bInfo: false,
                searching: false
            });
            $('#user_search').keyup(function(){
                rTable.search($(this).val()).draw();
            })
            $('#show_entries').change(function(){
                rTable.page.len($(this).val()).draw();
            })
            $('#reseller_search').change(function(){
                rTable.ajax.reload(null, false);
            })
            $('#filter').change(function(){
                rTable.ajax.reload( null, false );
            })
            $("#datatable-mass").selectable({
                filter: 'tr',
                selected: function (event, ui) {
                    if ($(ui.selected).hasClass('selectedfilter')) {
                        $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                        window.rSelected.splice($.inArray($(ui.selected).find("td:eq(0)").html(), window.rSelected), 1);
                    } else {            
                        $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                        window.rSelected.push($(ui.selected).find("td:eq(0)").html());
                    }
                    $("#selected_count").html(" - " + window.rSelected.length + " selected")
                }
            });
            $("#datatable-bouquets").selectable({
                filter: 'tr',
                selected: function (event, ui) {
                    if ($(ui.selected).hasClass('selectedfilter')) {
                        $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                        window.rBouquets.splice($.inArray($(ui.selected).find("td:eq(0)").html(), window.rBouquets), 1);
                    } else {            
                        $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                        window.rBouquets.push($(ui.selected).find("td:eq(0)").html());
                    }
                    if (!$("#c_bouquets").is(":checked")) {
                        $("#c_bouquets").prop('checked', true);
                    }
                }
            });
        });
        </script>
    </body>
</html>