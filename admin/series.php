<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) { exit; }
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "series"))) { exit; }

$rCategories = getCategories("series");

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page<?php if ($rPermissions["is_reseller"]) { echo " boxed-layout-ext"; } ?>"><div class="content"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper<?php if ($rPermissions["is_reseller"]) { echo " boxed-layout-ext"; } ?>"><div class="container-fluid">
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
                                                <i class="mdi mdi-refresh"></i> <span class="auto-text">Auto-Refresh</span>
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="javascript:location.reload();" onClick="toggleAuto();">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> Refresh
                                            </button>
                                        </a>
                                        <?php }
                                        if (($rPermissions["is_admin"]) && (hasPermissions("adv", "add_series"))) { ?>
                                        <a href="serie.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> Add Series
                                            </button>
                                        </a>
                                        <?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title">Series</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <form id="series_form">
                                    <div class="form-group row mb-4">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="series_search" value="" placeholder="Search Series...">
                                        </div>
                                        <div class="col-md-3">
                                            <select id="series_category_id" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Categories</option>
                                                <option value="-1">No TMDb Match</option>
                                                <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="series_show_entries">Show</label>
                                        <div class="col-md-2">
                                            <select id="series_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <table id="datatable-streampage" class="table dt-responsive nowrap font-normal">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th class="text-center">Seasons</th>
                                            <th class="text-center">Episodes</th>
                                            <th class="text-center">First Aired</th>
                                            <?php if ($rPermissions["is_admin"]) { ?>
                                            <th class="text-center">Last Updated</th>
                                            <th class="text-center">Actions</th>
                                            <?php } ?>
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
        <script src="assets/libs/select2/select2.min.js"></script>
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
        <script src="assets/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
        <script src="assets/js/pages/form-remember.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var autoRefresh = true;
        var rClearing = false;
        
        function toggleAuto() {
            if (autoRefresh == true) {
                autoRefresh = false;
                $(".auto-text").html("Manual Mode");
            } else {
                autoRefresh = true;
                $(".auto-text").html("Auto-Refresh");
            }
        }
        <?php if ($rPermissions["is_admin"]) { ?>
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this series and all episodes?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=series&sub=" + rType + "&series_id=" + rID, function(data) {
                if (data.result == true) {
                    if (rType == "delete") {
                        $.toast("Series successfully deleted.");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                } else {
                    $.toast("An error occured while processing your request.");
                }
            }).fail(function() {
                $.toast("An error occured while processing your request.");
            });
        }
        <?php } ?>
        function reloadStreams() {
            if (autoRefresh == true) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload( null, false );
            }
            setTimeout(reloadStreams, 5000);
        }
        function getCategory() {
            return $("#series_category_id").val();
        }
        function changeZoom() {
            if ($("#datatable-streampage").hasClass("font-large")) {
                $("#datatable-streampage").removeClass("font-large");
                $("#datatable-streampage").addClass("font-normal");
            } else if ($("#datatable-streampage").hasClass("font-normal")) {
                $("#datatable-streampage").removeClass("font-normal");
                $("#datatable-streampage").addClass("font-small");
            } else {
                $("#datatable-streampage").removeClass("font-small");
                $("#datatable-streampage").addClass("font-large");
            }
            $("#datatable-streampage").DataTable().draw();
        }
        function clearFilters() {
            window.rClearing = true;
            $("#series_search").val("").trigger('change');
            $('#series_category_id').val("").trigger('change');
            $('#series_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-streampage').DataTable().search($("#series_search").val());
            $('#datatable-streampage').DataTable().page.len($('#series_show_entries').val());
            $("#datatable-streampage").DataTable().page(0).draw('page');
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-streampage").DataTable().ajax.reload( null, false );
        }
        $(document).ready(function() {
			$(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            formCache.init();
            formCache.fetch();
            
            $('select').select2({width: '100%'});
            $("#datatable-streampage").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('[data-toggle="tooltip"]').tooltip();
                },
                createdRow: function(row, data, index) {
                    $(row).addClass('stream-' + data[0]);
                },
                responsive: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "series";
                        d.category = getCategory();
                    }
                },
                columnDefs: [
                    <?php if ($rPermissions["is_reseller"]) { ?>
                    {"className": "dt-center", "targets": [0,3,4,5]},
                    <?php } else { ?>
                    {"className": "dt-center", "targets": [0,3,4,5,6,7]},
                    {"orderable": false, "targets": [7]}
                    <?php } ?>
                ],
                order: [[ 0, "desc" ]],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                stateSave: true
            });
            $("#datatable-streampage").css("width", "100%");
            $('#series_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().search($(this).val()).draw();
                }
            })
            $('#series_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().page.len($(this).val()).draw();
                }
            })
            $('#series_category_id').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            })
            <?php if (!$detect->isMobile()) { ?>
            setTimeout(reloadStreams, 5000);
            <?php }
            if (!$rAdminSettings["auto_refresh"]) { ?>
            toggleAuto();
            <?php } ?>
            if ($('#series_search').val().length > 0) {
                $('#datatable-streampage').DataTable().search($('#series_search').val()).draw();
            }
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>
    </body>
</html>