<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "fingerprint"))) { exit; }

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
                                    <a href="./streams.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?=$_["back_to_streams"]?></li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["fingerprint_stream"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="./fingerprint.php" method="POST" id="fingerprint_form">
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item" id="stream-selection-tab">
                                                <a href="#stream-selection" id="stream-selection-nav" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-play mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["stream"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item disabled" id="stream-activity-tab">
                                                <a href="#stream-activity" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-group mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["activity"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="stream-selection">
                                                <div class="row">
                                                    <div class="col-md-5 col-6">
                                                        <input type="text" class="form-control" id="stream_search" value="" placeholder="<?=$_["search_streams"]?>...">
                                                    </div>
                                                    <div class="col-md-4 col-6">
                                                        <select id="category_search" class="form-control" data-toggle="select2">
                                                            <option value="" selected><?=$_["all_categories"]?></option>
                                                            <?php foreach ($rCategories as $rCategory) { ?>
                                                            <option value="<?=$rCategory["id"]?>"<?php if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo " selected"; } ?>><?=$rCategory["category_name"]?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <label class="col-md-1 col-2 col-form-label text-center" for="show_entries"><?=$_["show"]?></label>
                                                    <div class="col-md-2 col-8">
                                                        <select id="show_entries" class="form-control" data-toggle="select2">
                                                            <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                            <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <table id="datatable-md1" class="table table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="text-center"><?=$_["id"]?></th>
                                                                <th><?=$_["stream_name"]?></th>
                                                                <th><?=$_["category"]?></th>
                                                                <th class="text-center"><?=$_["clients"]?></th>
                                                                <th class="text-center"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="stream-activity">
                                                <div class="row">
                                                    <div class="alert alert-warning alert-dismissible fade show col-md-12 col-12 text-center" role="alert">
                                                        <?=$_["warning_fingerprint"]?>
                                                    </div>
                                                </div>
                                                <div class="row" id="filter_selection">
                                                    <label class="col-md-1 col-2 col-form-label text-center" for="fingerprint_type"><?=$_["type"]?></label>
                                                    <div class="col-md-2 col-6">
                                                        <select id="fingerprint_type" class="form-control text-center" data-toggle="select2">
                                                            <option value="1"><?=$_["activity_id"]?></option>
                                                            <option value="2"><?=$_["username"]?></option>
                                                            <option value="3"><?=$_["message"]?></option>
                                                        </select>
                                                    </div>
                                                    <label class="col-md-1 col-2 col-form-label text-center" for="font_size"><?=$_["size"]?></label>
                                                    <div class="col-md-1 col-2">
                                                        <input type="text" class="form-control text-center" id="font_size" value="36" placeholder="">
                                                    </div>
                                                    <label class="col-md-1 col-2 col-form-label text-center" for="font_color"><?=$_["colour"]?></label>
                                                    <div class="col-md-2 col-2">
                                                        <input type="text" id="font_color" class="form-control text-center" value="#ffffff">
                                                    </div>
                                                    <label class="col-md-1 col-2 col-form-label text-center" for="position"><?=$_["position"]?></label>
                                                    <div class="col-md-1 col-2">
                                                        <input type="text" class="form-control text-center" id="position_x" value="10" placeholder="X">
                                                    </div>
                                                    <div class="col-md-1 col-2">
                                                        <input type="text" class="form-control text-center" id="position_y" value="10" placeholder="Y">
                                                    </div>
                                                    <div class="col-md-1 col-2">
                                                        <button type="button" class="btn btn-info waves-effect waves-light" onClick="activateFingerprint()">
                                                            <i class="mdi mdi-fingerprint"></i>
                                                        </button>
                                                    </div>
                                                    <div class="col-md-12 col-2" style="margin-top:10px;display:none;" id="custom_message_div">
                                                        <input type="text" class="form-control" id="custom_message" value="" placeholder="<?=$_["custom_message"]?>">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <table id="datatable-md2" class="table table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="text-center"><?=$_["id"]?></th>
                                                                <th class="text-center"><?=$_["status"]?></th>
                                                                <th><?=$_["username"]?></th>
                                                                <th><?=$_["stream"]?></th>
                                                                <th><?=$_["server"]?></th>
                                                                <th class="text-center"><?=$_["time"]?></th>
                                                                <th class="text-center"><?=$_["ip"]?></th>
                                                                <th class="text-center"><?=$_["country"]?></th>
                                                                <th class="text-center"><?=$_["actions"]?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
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
        <script src="assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var rStreamID = -1;
        
        function getCategory() {
            return $("#category_search").val();
        }
        function getStreamID() {
            return window.rStreamID;
        }
        function selectFingerprint(rID) {
            $("#stream-activity-tab").attr("disabled", false);
            $('[href="#stream-activity"]').tab('show');
            window.rStreamID = rID;
        }
        function activateFingerprint() {
            rArray = {"id": window.rStreamID, "font_size": $("#font_size").val(), "font_color": $("#font_color").val(), "message": "", "type": $("#fingerprint_type").val(), "xy_offset": ""};
            if (rArray.type == 3) {
                rArray["message"] = $("#custom_message").val();
            }
            if (($("#position_x").val() >= 0) && ($("#position_y").val() >= 0)) {
                rArray["xy_offset"] = $("#position_x").val() + "x" + $("#position_y").val();
            }
            if ((rArray["font_size"] > 0) && (rArray["font_color"].length > 0) && ((rArray["message"].length > 0) || (rArray["type"] != 3))  && (rArray["font_size"] > 0) && (rArray["xy_offset"].length > 0)) {
                $.getJSON("./api.php?action=fingerprint&data=" + encodeURIComponent(JSON.stringify(rArray)), function(data) {
                    if (data.result == true) {
                        $.toast("<?=$_["fingerprint_success"]?>");
                    } else {
                        $.toast("<?=$_["error_occured"]?>");
                    }
                });
                $("#datatable-md2").DataTable().ajax.reload( null, false );
                $("#filter_selection").fadeOut(500, function() {
                    $('#datatable-md2').parents('div.dataTables_wrapper').first().fadeIn(500);
                });
            } else {
                $.toast("<?=$_["fingerprint_fail"]?>");
            }
        }
        function api(rID, rType, rAID) {
            $.getJSON("./api.php?action=user_activity&sub=" + rType + "&pid=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "kill") {
                        $.toast("<?=$_["connection_has_been_killed"]?>");
                        $("#row-" + rAID).remove();
                    }
                } else {
                    $.toast("<?=$_["error_occured"]?>");
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
            $("#font_color").colorpicker({format:"auto"});
            $(document).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $("#probesize_ondemand").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#delay_minutes").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#tv_archive_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            $("#datatable-md1").DataTable({
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
                        d.id = "stream_unique",
                        d.category = getCategory()
                    }
                },
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                columnDefs: [
                    {"className": "dt-center", "targets": [0,3,4]},
                    {"orderable": false, "targets": [4]}
                ],
                order: [[ 3, "desc" ]],
            });
            $('#stream_search').keyup(function(){
                $("#datatable-md1").DataTable().search($(this).val()).draw();
            });
            $('#show_entries').change(function(){
                $("#datatable-md1").DataTable().page.len($(this).val()).draw();
            });
            $('#category_search').change(function(){
                $("#datatable-md1").DataTable().ajax.reload(null, false);
            });
            $("#datatable-md2").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                rowCallback: function (row, data) {
                    $(row).attr("id", "row-" + data[0]);
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "live_connections",
                        d.stream_id = getStreamID(),
                        d.fingerprint = true;
                    }
                },
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                columnDefs: [
                    {"className": "dt-center", "targets": [0,1,5,6,7,8]},
                    {"visible": false, "targets": [1,3]}
                ],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                lengthMenu: [10, 25, 50, 250, 500, 1000],
                order: [[ 0, "desc" ]]
            });
            $("#fingerprint_type").change(function() {
                if ($(this).val() == 3) {
                    $("#custom_message_div").show();
                } else {
                    $("#custom_message_div").hide();
                }
            });
            $("#font_size").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#position_x").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#position_y").inputFilter(function(value) { return /^\d*$/.test(value); });
            $('#datatable-md2').parents('div.dataTables_wrapper').first().hide();
            $(".nav li.disabled a").click(function() {
                return false;
            });
            $("#stream-selection-nav").click(function() {
                $("#stream-activity-tab").attr("disabled", true);
                window.rStreamID = -1;
                $("#filter_selection").show();
                $('#datatable-md2').parents('div.dataTables_wrapper').first().hide();
                $("#datatable-md1").DataTable().ajax.reload( null, false );
            });
        });
        </script>
    </body>
</html>