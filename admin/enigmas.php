<?php
include "session.php"; include "functions.php";

if ($rPermissions["is_admin"]) {
	if (!hasPermissions("adv", "manage_e2")) { exit; }
	$rRegisteredUsers = getRegisteredUsers();
} else {
    $rRegisteredUsers = getRegisteredUsers($rUserInfo["id"]);
}

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
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="#" onClick="clearFilters();">
                                            <button type="button" class="btn btn-warning waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-filter-remove"></i>
                                            </button>
                                        </a>
                                        <a href="#" onClick="changeZoom();">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-magnify"></i>
                                            </button>
                                        </a>
                                        <?php if (!$detect->isMobile()) { ?>
                                        <a href="#" onClick="toggleAuto();">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> <span class="auto-text"><?=$_["auto_refresh"]?></span>
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="javascript:location.reload();" onClick="toggleAuto();">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> <?=$_["refresh"]?>
                                            </button>
                                        </a>
                                        <?php }
                                        if (($rPermissions["is_admin"]) && (hasPermissions("adv", "add_mag"))) { ?>
                                        <a href="enigma.php">
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-link"></i> <?=$_["link_enigma"]?>
                                            </button>
                                        </a>
                                        <?php }
										if ((hasPermissions("adv", "add_mag")) OR ($rPermissions["is_reseller"])) { ?>
                                        <a href="user<?php if ($rPermissions["is_reseller"]) { echo "_reseller"; } ?>.php?e2">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> <?=$_["add_enigma"]?>
                                            </button>
                                        </a>
										<?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["enigma_devices"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <form id="e2_form">
                                    <div class="form-group row mb-4">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="e2_search" value="" placeholder="<?=$_["search_devices"]?>...">
                                        </div>
                                        <label class="col-md-2 col-form-label text-center" for="e2_reseller"><?=$_["filter_results"]?></label>
                                        <div class="col-md-3">
                                            <select id="e2_reseller" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_resellers"]?></option>
                                                <?php foreach ($rRegisteredUsers as $rRegisteredUser) { ?>
                                                <option value="<?=$rRegisteredUser["id"]?>"><?=$rRegisteredUser["username"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select id="e2_filter" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["no_filter"]?></option>
                                                <option value="1"><?=$_["active"]?></option>
                                                <option value="2"><?=$_["disabled"]?></option>
                                                <option value="3"><?=$_["banned"]?></option>
                                                <option value="4"><?=$_["expired"]?></option>
                                                <option value="5"><?=$_["trial"]?></option>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="e2_show_entries"><?=$_["show"]?></label>
                                        <div class="col-md-1">
                                            <select id="e2_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <table id="datatable-users" class="table dt-responsive nowrap font-normal">
                                    <thead>
                                        <tr>
                                            <th class="text-center"><?=$_["id"]?></th>
                                            <th><?=$_["username"]?></th>
                                            <th class="text-center"><?=$_["mac_address"]?></th>
                                            <th><?=$_["owner"]?></th>
                                            <th class="text-center"><?=$_["status"]?></th>
                                            <th class="text-center"><?=$_["online"]?></th>
                                            <th class="text-center"><?=$_["trial"]?></th>
                                            <th class="text-center"><?=$_["expiration"]?></th>
                                            <th class="text-center"><?=$_["actions"]?></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables/buttons.flash.min.js"></script>
        <script src="assets/libs/datatables/buttons.print.min.js"></script>
        <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="assets/libs/datatables/dataTables.select.min.js"></script>
        <script src="assets/js/pages/form-remember.js"></script>

        <!-- Datatables init -->
        <script>
        var autoRefresh = true;
        var rClearing = false;
        
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('<?=$_["device_delete_confirm"]?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=user&sub=" + rType + "&user_id=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $.toast("<?=$_["device_confirmed_1"]?>");
                    } else if (rType == "enable") {
                        $.toast("<?=$_["device_confirmed_2"]?>");
                    } else if (rType == "disable") {
                        $.toast("<?=$_["device_confirmed_3"]?>");
                    } else if (rType == "unban") {
                        $.toast("<?=$_["device_confirmed_4"]?>");
                    } else if (rType == "ban") {
                        $.toast("<?=$_["device_confirmed_5"]?>");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-users").DataTable().ajax.reload(null, false);
                } else {
                    $.toast("<?=$_["error_occured"]?>");
                }
            });
        }
        
        function toggleAuto() {
            if (autoRefresh == true) {
                autoRefresh = false;
                $(".auto-text").html("<?=$_["manual_mode"]?>");
            } else {
                autoRefresh = true;
                $(".auto-text").html("<?=$_["auto_refresh"]?>");
            }
        }
        
        function getFilter() {
            return $("#e2_filter").val();
        }
        function getReseller() {
            return $("#e2_reseller").val();
        }
        
        function reloadUsers() {
            if (autoRefresh == true) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-users").DataTable().ajax.reload(null, false);
            }
            setTimeout(reloadUsers, 10000);
        }
        function changeZoom() {
            if ($("#datatable-users").hasClass("font-large")) {
                $("#datatable-users").removeClass("font-large");
                $("#datatable-users").addClass("font-normal");
            } else if ($("#datatable-users").hasClass("font-normal")) {
                $("#datatable-users").removeClass("font-normal");
                $("#datatable-users").addClass("font-small");
            } else {
                $("#datatable-users").removeClass("font-small");
                $("#datatable-users").addClass("font-large");
            }
            $("#datatable-users").DataTable().draw();
        }
        function clearFilters() {
            window.rClearing = true;
            $("#e2_search").val("").trigger('change');
            $('#e2_filter').val("").trigger('change');
            $('#e2_reseller').val("").trigger('change');
            $('#e2_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-users').DataTable().search($("#e2_search").val());
            $('#datatable-users').DataTable().page.len($('#e2_show_entries').val());
            $("#datatable-users").DataTable().page(0).draw('page');
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-users").DataTable().ajax.reload( null, false );
        }
        $(document).ready(function() {
			$(window).keypress(function(event){
				if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
			});
            formCache.init();
            formCache.fetch();

            $.fn.dataTable.ext.errMode = 'none';
            $('select').select2({width: '100%'});
            $("#datatable-users").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                    infoFiltered: ""
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('[data-toggle="tooltip"]').tooltip();
                },
                createdRow: function(row, data, index) {
                    $(row).addClass('user-' + data[0]);
                },
                responsive: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "enigmas",
                        d.filter = getFilter(),
                        d.reseller = getReseller()
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,2,4,5,6,7,8]},
                    {"orderable": false, "targets": [8]},
                    {"visible": false, "targets": [1]}
                ],
                order: [[ 0, "desc" ]],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                stateSave: true
            });
            $("#datatable-users").css("width", "100%");
            $('#e2_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-users').DataTable().search($(this).val()).draw();
                }
            });
            $('#e2_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-users').DataTable().page.len($(this).val()).draw();
                }
            });
            $('#e2_filter').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-users").DataTable().ajax.reload( null, false );
                }
            });
            $('#e2_reseller').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-users").DataTable().ajax.reload( null, false );
                }
            });
            <?php if (!$detect->isMobile()) { ?>
            setTimeout(reloadUsers, 10000);
            <?php } ?>
            $('#datatable-users').DataTable().search($(this).val()).draw();
            <?php if (!$rAdminSettings["auto_refresh"]) { ?>
            toggleAuto();
            <?php } ?>
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>
    </body>
</html>