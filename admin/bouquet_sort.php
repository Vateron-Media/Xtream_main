<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_bouquet"))) { exit; }

if (isset($_POST["bouquet_order_array"])) {
    set_time_limit(0);
    ini_set('mysql.connect_timeout', 0);
    ini_set('max_execution_time', 0);
    ini_set('default_socket_timeout', 0);
    $rOrder = json_decode($_POST["bouquet_order_array"], True);
    $rSort = 1;
    foreach ($rOrder as $rBouquetID) {
        $db->query("UPDATE `bouquets` SET `bouquet_order` = ".intval($rSort)." WHERE `id` = ".intval($rBouquetID).";");
        $rSort ++;
    }
    if (isset($_POST["confirmReplace"])) {
        $rUsers = getUserBouquets();
        foreach ($rUsers as $rUser) {
            $rBouquet = json_decode($rUser["bouquet"], True);
            $rBouquet = sortArrayByArray($rBouquet, $rOrder);
            $db->query("UPDATE `users` SET `bouquet` = '[".ESC(join(",", $rBouquet))."]' WHERE `id` = ".intval($rUser["id"]).";");
        }
        $rPackages = getPackages();
        foreach ($rPackages as $rPackage) {
            $rBouquet = json_decode($rPackage["bouquets"], True);
            $rBouquet = sortArrayByArray($rBouquet, $rOrder);
            $db->query("UPDATE `packages` SET `bouquets` = '[".ESC(join(",", $rBouquet))."]' WHERE `id` = ".intval($rPackage["id"]).";");
        }
        $_STATUS = 0;
    } else {
        $_STATUS = 1;
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
                            <h4 class="page-title"><?=$_["bouquet_order"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-success show" role="alert">
                            Bouquet order has taken effect and all users and packages have been modified to utilise the new bouquet order.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-success show" role="alert">
                            Bouquet order has taken effect, any new users and packages will use this order.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./bouquet_sort.php" method="POST" id="bouquet_sort_form">
                                    <input type="hidden" id="bouquet_order_array" name="bouquet_order_array" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#order-stream" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-flower-tulip-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["bouquet_order"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="order-stream">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_bouquet" class="form-control" style="min-height:400px;">
                                                            <?php foreach (getBouquets() as $rBouquet) { ?>
                                                            <option value="<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <div class="custom-control custom-checkbox add-margin-top-20">
															<input type="checkbox" class="custom-control-input" name="confirmReplace" id="confirmReplace">
															<label class="custom-control-label" for="confirmReplace">Replace bouquet order for all users and packages retrospectively. This can take a while.</label>
														</div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp()" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown()" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop()" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom()" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ()" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
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
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/nestable2/jquery.nestable.min.js"></script>
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
        <script src="assets/libs/datatables/dataTables.rowReorder.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        function AtoZ() {
            $("#sort_bouquet").append($("#sort_bouquet option").remove().sort(function(a, b) {
                var at = $(a).text().toUpperCase(), bt = $(b).text().toUpperCase();
                return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
            }));
        }
        function MoveUp() {
            var rSelected = $('#sort_bouquet option:selected');
            if (rSelected.length) {
                var rPrevious = rSelected.first().prev()[0];
                if ($(rPrevious).html() != '') {
                    rSelected.first().prev().before(rSelected);
                }
            }
        }
        function MoveDown() {
            var rSelected = $('#sort_bouquet option:selected');
            if (rSelected.length) {
                rSelected.last().next().after(rSelected);
            }
        }
        function MoveTop() {
            var rSelected = $('#sort_bouquet option:selected');
            if (rSelected.length) {
                rSelected.prependTo($('#sort_bouquet'));
            }
        }
        function MoveBottom() {
            var rSelected = $('#sort_bouquet option:selected');
            if (rSelected.length) {
                rSelected.appendTo($('#sort_bouquet'));
            }
        }
        
        $(document).ready(function() {
            $('.select2').select2({width: '100%'});
            $("#bouquet_sort_form").submit(function(e){
                rOrder = [];
                $('#sort_bouquet option').each(function() {
                    rOrder.push($(this).val());
                });
                $("#bouquet_order_array").val(JSON.stringify(rOrder));
            });
        });
        </script>
    </body>
</html>