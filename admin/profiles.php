<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "tprofiles"))) { exit; }

$rProfiles = getTranscodeProfiles();
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
							<?php if (hasPermissions("adv", "tprofile")) { ?>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="profile.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> <?=$_["add_profile"]?>
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
							<?php } ?>
                            <h4 class="page-title"><?=$_["transcode_profiles"]?></h4>
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
                                            <th><?=$_["profile_name"]?></th>
                                            <th><?=$_["options"]?></th>
                                            <th class="text-center"><?=$_["actions"]?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rProfiles as $rProfile) {
                                        ?>
                                        <tr id="profile-<?=$rProfile["profile_id"]?>">
                                            <td class="text-center"><?=$rProfile["profile_id"]?></td>
                                            <td><?=$rProfile["profile_name"]?></td>
                                            <td><?=(strlen($rProfile["profile_options"]) > 100 ? substr($rProfile["profile_options"],0,100)."..." : $rProfile["profile_options"])?></td>
                                            <td class="text-center">
												<?php if (hasPermissions("adv", "edit_tprofile")) { ?>
                                                <div class="btn-group">
                                                    <a href="./profile.php?id=<?=$rProfile["profile_id"]?>"><button type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                    <button type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rProfile["profile_id"]?>, 'delete');"><i class="mdi mdi-close"></i></button>
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
                if (confirm('<?=$_["profile_delete_confirm"]?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=profile&sub=" + rType + "&profile_id=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $("#profile-" + rID).remove();
                        $.toast("<?=$_["profile_deleted"]?>");
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
                responsive: false
            });
            $("#datatable").css("width", "100%");
        });
        </script>
    </body>
</html>