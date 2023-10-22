<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "mng_packages"))) { exit; }

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
							<?php if (hasPermissions("adv", "add_packages")) { ?>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="package.php" style="margin-right:10px;">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> <?=$_["add_package"]?>
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
							<?php } ?>
                            <h4 class="page-title"><?=$_["packages"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <table id="datatable" class="table dt-responsive nowrap">
                                    <thead>
                                        <tr>
                                            <th class="text-center"><?=$_["id"]?></th>
                                            <th><?=$_["package_name"]?></th>
                                            <th class="text-center"><?=$_["trial"]?></th>
                                            <th class="text-center"><?=$_["official"]?></th>
                                            <th class="text-center"><?=$_["create_mag"]?></th>
                                            <th class="text-center"><?=$_["only_mag"]?></th>
                                            <th class="text-center"><?=$_["create_enigma"]?></th>
                                            <th class="text-center"><?=$_["only_enigma"]?></th>
                                            <th class="text-center"><?=$_["actions"]?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (getPackages() as $rPackage) { ?>
                                        <tr id="package-<?=$rPackage["id"]?>">
                                            <td class="text-center"><?=$rPackage["id"]?></td>
                                            <td><?=$rPackage["package_name"]?></td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input <?php if (!hasPermissions("adv", "edit_package")) { echo "disabled "; } ?>data-id="<?=$rPackage["id"]?>" data-name="is_trial" type="checkbox" class="custom-control-input" id="is_trial_<?=$rPackage["id"]?>" name="is_trial"<?php if ($rPackage["is_trial"]) { echo " checked"; } ?>>
                                                    <label class="custom-control-label" for="is_trial_<?=$rPackage["id"]?>"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input <?php if (!hasPermissions("adv", "edit_package")) { echo "disabled "; } ?>data-id="<?=$rPackage["id"]?>" data-name="is_official" type="checkbox" class="custom-control-input" id="is_official_<?=$rPackage["id"]?>" name="is_official"<?php if ($rPackage["is_official"]) { echo " checked"; } ?>>
                                                    <label class="custom-control-label" for="is_official_<?=$rPackage["id"]?>"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input <?php if (!hasPermissions("adv", "edit_package")) { echo "disabled "; } ?>data-id="<?=$rPackage["id"]?>" data-name="can_gen_mag" type="checkbox" class="custom-control-input" id="can_gen_mag_<?=$rPackage["id"]?>" name="can_gen_mag"<?php if ($rPackage["can_gen_mag"]) { echo " checked"; } ?>>
                                                    <label class="custom-control-label" for="can_gen_mag_<?=$rPackage["id"]?>"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input <?php if (!hasPermissions("adv", "edit_package")) { echo "disabled "; } ?>data-id="<?=$rPackage["id"]?>" data-name="only_mag" type="checkbox" class="custom-control-input" id="only_mag_<?=$rPackage["id"]?>" name="only_mag"<?php if ($rPackage["only_mag"]) { echo " checked"; } ?>>
                                                    <label class="custom-control-label" for="only_mag_<?=$rPackage["id"]?>"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input <?php if (!hasPermissions("adv", "edit_package")) { echo "disabled "; } ?>data-id="<?=$rPackage["id"]?>" data-name="can_gen_e2" type="checkbox" class="custom-control-input" id="can_gen_e2_<?=$rPackage["id"]?>" name="can_gen_e2"<?php if ($rPackage["can_gen_e2"]) { echo " checked"; } ?>>
                                                    <label class="custom-control-label" for="can_gen_e2_<?=$rPackage["id"]?>"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input <?php if (!hasPermissions("adv", "edit_package")) { echo "disabled "; } ?>data-id="<?=$rPackage["id"]?>" data-name="only_e2" type="checkbox" class="custom-control-input" id="only_e2_<?=$rPackage["id"]?>" name="only_e2"<?php if ($rPackage["only_e2"]) { echo " checked"; } ?>>
                                                    <label class="custom-control-label" for="only_e2_<?=$rPackage["id"]?>"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
												<?php if (hasPermissions("adv", "edit_package")) { ?>
                                                <div class="btn-group">
                                                    <a href="./package.php?id=<?=$rPackage["id"]?>"><button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["edit_package"]?>" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$_["delete_package"]?>" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rPackage["id"]?>, 'delete');""><i class="mdi mdi-close"></i></button>
                                                </div>
												<?php } else { echo "--"; } ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->
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
        <script src="assets/js/app.min.js"></script>

        <script>
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('<?=$_["package_delete_confirm"]?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=package&sub=" + rType + "&package_id=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $("#package-" + rID).remove();
                        $.toast("<?=$_["package_deleted"]?>");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip();
                } else {
                    $.toast("<?=$_["error_occured"]?>");
                }
            });
        }
        <?php if (hasPermissions("adv", "edit_package")) { ?>
        $('input:checkbox').change(function() {
            $.getJSON("./api.php?action=package&sub=" + $(this).data("name") + "&package_id=" + $(this).data("id") + "&value=" + ($(this).is(":checked") ? 1 : 0), function(data) {
                $.toast("<?=$_["package_modified"]?>");
            });
        });
        <?php } ?>
        $(document).ready(function() {
            $("#datatable").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                responsive: false,
                paging: false,
                bInfo: false
            });
            $("#datatable").css("width", "100%");
        });
        </script>
    </body>
</html>