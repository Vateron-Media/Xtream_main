<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) { exit; }
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "movies"))) { exit; }

$rCategories = getCategories("movie");

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
                                        if (($rPermissions["is_admin"]) && (hasPermissions("adv", "add_movie"))) { ?>
                                        <a href="movie.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> <?=$_["add_movie"]?>
                                            </button>
                                        </a>
                                        <?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["movies"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <form id="movies_form">
                                    <div class="form-group row mb-4">
                                        <?php if ($rPermissions["is_reseller"]) { ?>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="movies_search" value="" placeholder="<?=$_["search_movies"]?>...">
                                        </div>
                                        <div class="col-md-3">
                                            <select id="movies_category_id" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_categories"]?></option>
                                                <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select id="movies_server" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_servers"]?></option>
                                                <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"<?php if ((isset($_GET["server"])) && ($_GET["server"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="movies_show_entries"><?=$_["show"]?></label>
                                        <div class="col-md-2">
                                            <select id="movies_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } else { ?>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="movies_search" value="" placeholder="<?=$_["search_movies"]?>...">
                                        </div>
                                        <div class="col-md-3">
                                            <select id="movies_server" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_servers"]?></option>
                                                <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"<?php if ((isset($_GET["server"])) && ($_GET["server"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select id="movies_category_id" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_categories"]?></option>
                                                <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select id="movies_filter" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["no_filter"]?></option>
                                                <option value="1"><?=$_["encoded"]?></option>
                                                <option value="2"><?=$_["encoding"]?></option>
                                                <option value="3"><?=$_["down"]?></option>
                                                <option value="4"><?=$_["ready"]?></option>
                                                <option value="5"><?=$_["direct"]?></option>
                                                <option value="6"><?=$_["no_tmdb_match"]?></option>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="movies_show_entries"><?=$_["show"]?></label>
                                        <div class="col-md-1">
                                            <select id="movies_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </form>
                                <table id="datatable-streampage" class="table dt-responsive nowrap font-normal">
                                    <thead>
                                        <tr>
                                            <th class="text-center"><?=$_["id"]?></th>
                                            <th><?=$_["name"]?></th>
                                            <th><?=$_["server"]?></th>
                                            <?php if ($rPermissions["is_admin"]) { ?>
                                            <th class="text-center"><?=$_["clients"]?></th>
                                            <th class="text-center"><?=$_["status"]?></th>
                                            <th class="text-center"><?=$_["actions"]?></th>
                                            <th class="text-center"><?=$_["player"]?></th>
                                            <?php } ?>
                                            <th class="text-center"><?=$_["stream_info"]?></th>
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
                $(".auto-text").html("<?=$_["manual_mode"]?>");
            } else {
                autoRefresh = true;
                $(".auto-text").html("<?=$_["auto_refresh"]?>");
            }
        }
        
        function api(rID, rServerID, rType) {
            if (rType == "delete") {
                if (confirm('<?=$_["movie_delete_confirm"]?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=movie&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
                if (data.result == true) {
                    if (rType == "start") {
                        $.toast("<?=$_["movie_encode_started"]?>");
                    } else if (rType == "stop") {
                        $.toast("<?=$_["movie_encode_stopped"]?>");
                    } else if (rType == "delete") {
                        $.toast("<?=$_["movie_delete_confirmed"]?>");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                } else {
                    $.toast("<?=$_["error_occured"]?>");
                }
            }).fail(function() {
                $.toast("<?=$_["error_occured"]?>");
            });
        }
        function player(rID, rContainer) {
            $.magnificPopup.open({
                items: {
                    src: "./player.php?type=movie&id=" + rID + "&container=" + rContainer,
                    type: 'iframe'
                }
            });
        }
        function reloadStreams() {
            if (autoRefresh == true) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload( null, false );
            }
            setTimeout(reloadStreams, 5000);
        }

        function getCategory() {
            return $("#movies_category_id").val();
        }
        function getFilter() {
            return $("#movies_filter").val();
        }
        function getServer() {
            return $("#movies_server").val();
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
            $("#movies_search").val("").trigger('change');
            $('#movies_filter').val("").trigger('change');
            $('#movies_server').val("").trigger('change');
            $('#movies_category_id').val("").trigger('change');
            $('#movies_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-streampage').DataTable().search($("#movies_search").val());
            $('#datatable-streampage').DataTable().page.len($('#movies_show_entries').val());
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
                        d.id = "movies";
                        d.category = getCategory();
                        d.server = getServer();
                        <?php if ($rPermissions["is_admin"]) { ?>
                        d.filter = getFilter();
                        <?php } else { ?>
                        d.filter = 1;
                        <?php } ?>
                    }
                },
                columnDefs: [
                    <?php if ($rPermissions["is_admin"]) { ?>
                    {"className": "dt-center", "targets": [0,3,4,5,6,7]},
                    {"orderable": false, "targets": [5,6]}
                    <?php } else { ?>
                    {"className": "dt-center", "targets": [0,3]}
                    <?php } ?>
                ],
                order: [[ 0, "desc" ]],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                stateSave: true
            });
            $("#datatable-streampage").css("width", "100%");
            $('#movies_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().search($(this).val()).draw();
                }
            })
            $('#movies_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().page.len($(this).val()).draw();
                }
            })
            $('#movies_category_id').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            })
            $('#movies_server').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            })
            $('#movies_filter').change(function(){
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
            if ($('#movies_search').val().length > 0) {
                $('#datatable-streampage').DataTable().search($('#movies_search').val()).draw();
            }
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>
    </body>
</html>