<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "folder_watch_output"))) { exit; }

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
                                        <button type="button" class="btn btn-info waves-effect waves-light btn-sm btn-clear-logs">
                                            <i class="mdi mdi-minus"></i> Clear Logs
                                        </button>
                                        <a href="./watch.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                View Folders
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title">Folder Watch Output</h4>
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
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="result_search" value="" placeholder="Search Results...">
                                        </div>
                                        <div class="col-md-2">
                                            <select id="result_server" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Servers</option>
                                                <?php foreach ($rServers as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select id="result_type" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Types</option>
                                                <?php foreach (Array(1 => "Movies", 2 => "Series") as $rID => $rType) { ?>
                                                <option value="<?=$rID?>"><?=$rType?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select id="result_status" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Statuses</option>
                                                <?php foreach (Array(1 => "Added", 2 => "SQL Error", 3 => "No Category", 4 => "No Match", 5 => "Invalid File") as $rID => $rType) { ?>
                                                <option value="<?=$rID?>"><?=$rType?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="result_show_entries">Show</label>
                                        <div class="col-md-2">
                                            <select id="result_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <table id="datatable-md1" class="table dt-responsive nowrap font-normal">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th>Type</th>
                                            <th>Server</th>
                                            <th>Filename</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Date Added</th>
                                            <th class="text-center">Actions</th>
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
        <div class="modal fade bs-logs-modal-center" tabindex="-1" role="dialog" aria-labelledby="clearLogsLabel" aria-hidden="true" style="display: none;" data-id="">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="clearLogsLabel">Clear Logs</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row mb-4">
                            <label class="col-md-4 col-form-label" for="range_clear">Date Range</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control text-center date" id="range_clear_from" name="range_clear_from" data-toggle="date-picker" data-single-date-picker="true" autocomplete="off" placeholder="From">
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control text-center date" id="range_clear_to" name="range_clear_to" data-toggle="date-picker" data-single-date-picker="true" autocomplete="off" placeholder="To">
                            </div>
                        </div>
                        <div class="text-center">
                            <input id="clear_logs" type="submit" class="btn btn-primary" value="Clear" style="width:100%" />
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
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/js/pages/form-remember.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var rClearing = false;
        
        <?php if ($rPermissions["is_admin"]) { ?>
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this record?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=watch_output&sub=" + rType + "&result_id=" + rID, function(data) {
                if (data.result == true) {
                    if (rType == "delete") {
                        $.toast("Record successfully deleted.");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-md1").DataTable().ajax.reload( null, false );
                } else {
                    $.toast("An error occured while processing your request.");
                }
            }).fail(function() {
                $.toast("An error occured while processing your request.");
            });
        }
        <?php } ?>
        function getServer() {
            return $("#result_server").val();
        }
        function getType() {
            return $("#result_type").val();
        }
        function getStatus() {
            return $("#result_status").val();
        }
        function clearFilters() {
            window.rClearing = true;
            $("#result_search").val("").trigger('change');
            $('#result_server').val("").trigger('change');
            $('#result_type').val("").trigger('change');
            $('#result_status').val("").trigger('change');
            $('#result_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-md1').DataTable().search($("#result_search").val());
            $('#datatable-md1').DataTable().page.len($('#result_show_entries').val());
            $("#datatable-md1").DataTable().page(0).draw('page');
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-md1").DataTable().ajax.reload( null, false );
        }
        $(document).ready(function() {
            formCache.init();
            formCache.fetch();
            
            $('select').select2({width: '100%'});
            $("#datatable-md1").DataTable({
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
                    $(row).addClass('result-' + data[0]);
                },
                responsive: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "watch_output";
                        d.server = getServer();
                        d.type = getType();
                        d.status = getStatus();
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,4,5,6]},
                    {"orderable": false, "targets": [6]}
                ],
                order: [[ 5, "desc" ]],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                stateSave: true
            });
            $("#datatable-md1").css("width", "100%");
            $('#result_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-md1').DataTable().search($(this).val()).draw();
                }
            })
            $('#result_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-md1').DataTable().page.len($(this).val()).draw();
                }
            })
            $('#result_server').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-md1").DataTable().ajax.reload( null, false );
                }
            })
            $('#result_type').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-md1").DataTable().ajax.reload( null, false );
                }
            })
            $('#result_status').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-md1").DataTable().ajax.reload( null, false );
                }
            })
            $('#datatable-md1').DataTable().search($(this).val()).draw();
            $('#range_clear_to').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                autoUpdateInput: false
            }).val("");
            $('#range_clear_from').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                autoUpdateInput: false
            }).val("");
            $('#range_clear_from').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            $('#range_clear_from').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            $('#range_clear_to').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            $('#range_clear_to').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            $(".btn-clear-logs").click(function() {
                $(".bs-logs-modal-center").modal("show");
            });
            $("#clear_logs").click(function() {
                if (confirm('Are you sure you want to clear logs for this period?') == false) {
                    return;
                }
                $(".bs-logs-modal-center").modal("hide");
                $.getJSON("./api.php?action=clear_logs&type=watch_output&from=" + encodeURIComponent($("#range_clear_from").val()) + "&to=" + encodeURIComponent($("#range_clear_to").val()), function(data) {
                    $.toast("Logs have been cleared.");
                    //window.location.href = './watch_output.php';
                });
            });
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>
    </body>
</html>