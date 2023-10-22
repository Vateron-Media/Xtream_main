<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) { exit; }
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "episodes"))) { exit; }

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
										if (hasPermissions("adv", "add_episode")) { ?>
                                        <a href="#" onClick="showModal()">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> <?=$_["add_episode"]?>
                                            </button>
                                        </a>
										<?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["episodes"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <form id="episodes_form">
                                    <div class="form-group row mb-4">
                                        <?php if ($rPermissions["is_reseller"]) { ?>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="episodes_search" value="" placeholder="<?=$_["search_episodes"]?>...">
                                        </div>
                                        <div class="col-md-3">
                                            <select id="episodes_server" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Servers</option>
                                                <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"<?php if ((isset($_GET["server"])) && ($_GET["server"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select id="episodes_series" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Series</option>
                                                <?php foreach (getSeriesList() as $rSeriesArr) { ?>
                                                <option value="<?=$rSeriesArr["id"]?>"<?php if ((isset($_GET["series"])) && ($_GET["series"] == $rSeriesArr["id"])) { echo " selected"; } ?>><?=$rSeriesArr["title"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="episodes_show_entries"><?=$_["show"]?></label>
                                        <div class="col-md-2">
                                            <select id="episodes_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } else { ?>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="episodes_search" value="" placeholder="<?=$_["search_episodes"]?>...">
                                        </div>
                                        <div class="col-md-3">
                                            <select id="episodes_server" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_servers"]?></option>
                                                <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"<?php if ((isset($_GET["server"])) && ($_GET["server"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select id="episodes_series" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_series"]?></option>
                                                <?php foreach (getSeriesList() as $rSeriesArr) { ?>
                                                <option value="<?=$rSeriesArr["id"]?>"<?php if ((isset($_GET["series"])) && ($_GET["series"] == $rSeriesArr["id"])) { echo " selected"; } ?>><?=$rSeriesArr["title"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select id="episodes_filter" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["no_filter"]?></option>
                                                <option value="1"><?=$_["encoded"]?></option>
                                                <option value="2"><?=$_["encoding"]?></option>
                                                <option value="3"><?=$_["down"]?></option>
                                                <option value="4"><?=$_["ready"]?></option>
                                                <option value="5"><?=$_["direct"]?></option>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="episodes_show_entries"><?=$_["show"]?></label>
                                        <div class="col-md-1">
                                            <select id="episodes_show_entries" class="form-control" data-toggle="select2">
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
        <div class="modal fade addModal" role="dialog" aria-labelledby="addLabel" aria-hidden="true" style="display: none;" data-username="" data-password="">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="addModal"><?=$_["select_series"]?>:</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <select id="add_series_id" class="form-control" data-toggle="select2">
                                <?php foreach (getSeriesList() as $rSeries) { ?>
                                <option value="<?=$rSeries["id"]?>"><?=$rSeries["title"]?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-12 add-margin-top-20">
                            <div class="input-group">
                                <div class="input-group-append" style="width:100%">
                                    <button style="width:50%" class="btn btn-success waves-effect waves-light" type="button" onClick="addEpisode();"><i class="mdi mdi-plus-circle-outline"></i> <?=$_["add_episode"]?></button>
                                    <button style="width:50%" class="btn btn-info waves-effect waves-light" type="button" onClick="addEpisodes();"><i class="mdi mdi-plus-circle-multiple-outline"></i> <?=$_["multiple_episodes"]?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
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
        
        function api(rID, rServerID, rType) {
            if (rType == "delete") {
                if (confirm('<?=$_["episode_delete_confirm"]?>') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=episode&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
                if (data.result == true) {
                    if (rType == "start") {
                        $.toast("<?=$_["episode_encoding_start"]?>");
                    } else if (rType == "stop") {
                        $.toast("<?=$_["episode_encoding_stop"]?>");
                    } else if (rType == "delete") {
                        $.toast("<?=$_["episode_deleted"]?>");
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
                    src: "./player.php?type=series&id=" + rID + "&container=" + rContainer,
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

        function getSeries() {
            return $("#episodes_series").val();
        }
        function getFilter() {
            return $("#episodes_filter").val();
        }
        function getServer() {
            return $("#episodes_server").val();
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
            $("#episodes_search").val("").trigger('change');
            $('#episodes_filter').val("").trigger('change');
            $('#episodes_server').val("").trigger('change');
            $('#episodes_series').val("").trigger('change');
            $('#episodes_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-streampage').DataTable().search($("#episodes_search").val());
            $('#datatable-streampage').DataTable().page.len($('#episodes_show_entries').val());
            $("#datatable-streampage").DataTable().page(0).draw('page');
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-streampage").DataTable().ajax.reload( null, false );
        }
        function showModal() {
            $('.addModal').modal('show');
        }
        function addEpisode() {
            window.location.href = "./episode.php?sid=" + $("#add_series_id").val();
        }
        function addEpisodes() {
            window.location.href = "./episode.php?sid=" + $("#add_series_id").val() + "&multi";
        }
        $(document).ready(function() {
			$(window).keypress(function(event){
				if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
			});
            formCache.init();
            <?php if (!isset($_GET["series"])) { ?>
            formCache.fetch();
            <?php } ?>
            
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
                        d.id = "episodes";
                        d.series = getSeries();
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
            $('#episodes_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().search($(this).val()).draw();
                }
            })
            $('#episodes_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().page.len($(this).val()).draw();
                }
            })
            $('#episodes_series').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            })
            $('#episodes_server').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            })
            $('#episodes_filter').change(function(){
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
            if ($('#episodes_search').val().length > 0) {
                $('#datatable-streampage').DataTable().search($('#episodes_search').val()).draw();
            }
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>
    </body>
</html>