<?php
include "session.php";
include "functions.php";
if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) {
    exit;
}
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "streams"))) {
    exit;
}

if (isset(CoreUtilities::$request["category"])) {
    if (!isset($rCategories[CoreUtilities::$request["category"]])) {
        exit;
    } else {
        $rCategory = $rCategories[CoreUtilities::$request["category"]];
    }
} else {
    $rCategory = null;
}

include "header.php";
?>

<div class="wrapper<?php if ($rPermissions["is_reseller"]) {
                        echo " boxed-layout-ext";
                    } ?>">
    <div class="container-fluid">
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
                                <?php
                                if ($rPermissions["is_admin"]) {
                                    if (hasPermissions("adv", "add_stream")) { ?>
                                        <a href="stream.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <?= $_["add_stream"] ?>
                                            </button>
                                        </a>
                                    <?php }
                                    if (hasPermissions("adv", "create_channel")) { ?>
                                        <a href="created_channel.php">
                                            <button type="button" class="btn btn-purple waves-effect waves-light btn-sm">
                                                <?= $_["create"] ?>
                                            </button>
                                        </a>
                                <?php }
                                } ?>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["streams"] ?> <?php if ($rCategory) {
                                                                    echo " - " . $rCategory["category_name"];
                                                                } ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-x:auto;">
                        <form id="stream_form">
                            <div class="form-group row mb-4">
                                <?php if ($rPermissions["is_reseller"]) { ?>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" id="stream_search" value=""
                                            placeholder="<?= $_["search_streams"] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="stream_category_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_categories"] ?></option>
                                            <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?= $rCategory["id"] ?>" <?php if ((isset(CoreUtilities::$request["category"])) && (CoreUtilities::$request["category"] == $rCategory["id"])) {
                                                                                            echo " selected";
                                                                                        } ?>><?= $rCategory["category_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="stream_server_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_servers"] ?></option>
                                            <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?= $rServer["id"] ?>" <?php if ((isset(CoreUtilities::$request["server"])) && (CoreUtilities::$request["server"] == $rServer["id"])) {
                                                                                            echo " selected";
                                                                                        } ?>><?= $rServer["server_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <label class="col-md-1 col-form-label text-center"
                                        for="stream_show_entries"><?= $_["show"] ?></label>
                                    <div class="col-md-2">
                                        <select id="stream_show_entries" class="form-control" data-toggle="select2">
                                            <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                            echo " selected";
                                                        } ?> value="<?= $rShow ?>"><?= $rShow ?></option>
                                                <?php } ?>
                                        </select>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="stream_search" value=""
                                            placeholder="<?= $_["search_streams"] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="stream_server_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_servers"] ?></option>
                                            <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?= $rServer["id"] ?>" <?php if ((isset(CoreUtilities::$request["server"])) && (CoreUtilities::$request["server"] == $rServer["id"])) {
                                                                                            echo " selected";
                                                                                        } ?>><?= $rServer["server_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="stream_category_id" class="form-control" data-toggle="select2">
                                            <option value="" selected><?= $_["all_categories"] ?></option>
                                            <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?= $rCategory["id"] ?>" <?php if ((isset(CoreUtilities::$request["category"])) && (CoreUtilities::$request["category"] == $rCategory["id"])) {
                                                                                            echo " selected";
                                                                                        } ?>><?= $rCategory["category_name"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select id="stream_filter" class="form-control" data-toggle="select2">
                                            <option value="" <?php if (!isset(CoreUtilities::$request["filter"])) {
                                                                    echo " selected";
                                                                } ?>><?= $_["no_filter"] ?></option>
                                            <option value="1" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 1)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["online"] ?></option>
                                            <option value="2" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 2)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["down"] ?></option>
                                            <option value="3" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 3)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["stopped"] ?></option>
                                            <option value="4" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 4)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["starting"] ?></option>
                                            <option value="5" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 5)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["on_demand"] ?></option>
                                            <option value="6" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 6)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["direct"] ?></option>
                                            <option value="7" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 7)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["timeshift"] ?></option>
                                            <option value="8" <?php if ((isset(CoreUtilities::$request["filter"])) && (CoreUtilities::$request["filter"] == 8)) {
                                                                    echo " selected";
                                                                } ?>><?= $_["created_channel"] ?></option>
                                        </select>
                                    </div>
                                    <label class="col-md-1 col-form-label text-center"
                                        for="stream_show_entries"><?= $_["show"] ?></label>
                                    <div class="col-md-1">
                                        <select id="stream_show_entries" class="form-control" data-toggle="select2">
                                            <?php foreach (array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rSettings["default_entries"] == $rShow) {
                                                            echo " selected";
                                                        } ?> value="<?= $rShow ?>"><?= $rShow ?></option>
                                                <?php } ?>
                                        </select>
                                    </div>
                                <?php } ?>
                            </div>
                        </form>
                        <table id="datatable-streampage" class="table table-hover dt-responsive nowrap font-normal">
                            <thead>
                                <tr>
                                    <th class="text-center"><?= $_["id"] ?></th>
                                    <th class="text-center"><?= $_["icon"] ?></th>
                                    <th><?= $_["name"] ?></th>
                                    <th><?= $_["source"] ?></th>
                                    <?php if ($rPermissions["is_admin"]) { ?>
                                        <th class="text-center"><?= $_["clients"] ?></th>
                                        <th class="text-center"><?= $_["uptime"] ?></th>
                                        <th class="text-center"><?= $_["actions"] ?></th>
                                        <th class="text-center"><?= $_["player"] ?></th>
                                        <th class="text-center"><?= $_["epg"] ?></th>
                                    <?php } ?>
                                    <th class="text-center"><?= $_["stream_info"] ?></th>
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
<!-- Footer Start -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 copyright text-center"><?= getFooter() ?></div>
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
    var rClearing = false;

    function api(rID, rServerID, rType) {
        if (rType == "delete") {
            if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_stream"] ?>') == false) {
                return;
            }
        }
        $.getJSON("./api.php?action=stream&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
            if (data.result == true) {
                if (rType == "start") {
                    $.toast("<?= $_["stream_successfully_started"] ?>");
                } else if (rType == "stop") {
                    $.toast("<?= $_["stream_successfully_stopped"] ?>");
                } else if (rType == "restart") {
                    $.toast("<?= $_["stream_successfully_restarted"] ?>");
                } else if (rType == "delete") {
                    $.toast("<?= $_["stream_successfully_deleted"] ?>");
                }
                $.each($('.tooltip'), function(index, element) {
                    $(this).remove();
                });
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            } else {
                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
            }
        }).fail(function() {
            $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
        });
    }

    function player(rID) {
        $.magnificPopup.open({
            items: {
                src: "./player.php?type=live&id=" + rID,
                type: 'iframe'
            }
        });
    }

    function getStreamIDs() {
        var rStreamIDs = [];
        var rIndexes = [];
        $("#datatable-streampage").DataTable().rows().every(function(rowIdx, tableLoop, rowLoop) {
            rStreamIDs.push($($("#datatable-streampage").DataTable().row(rowIdx).data()[0]).text());
            rIndexes.push(rowIdx);
        });
        return [rStreamIDs, rIndexes];
    }

    function refreshInformation() {
        if (!window.rProcessing) {
            var rUpdateColumns = [4, 5, 6, 7, 9];
            var rStreamIDs = getStreamIDs();
            if (rStreamIDs[0].length > 0) {
                $.getJSON("./table_search.php?" + $.param($("#datatable-streampage").DataTable().ajax.params()) + "&refresh=" + rStreamIDs[0].join(","), function(rTable) {
                    if (!window.rProcessing) {
                        $(rTable.data).each(function(rIndex, rItem) {
                            for (i in rUpdateColumns) {
                                var rIndex = rStreamIDs[0].indexOf($(rItem[0]).text());
                                if (rIndex >= 0) {
                                    if ($('#datatable-streampage').DataTable().cell(rStreamIDs[1][rIndex], rUpdateColumns[i]).data() != rItem[rUpdateColumns[i]]) {
                                        $('#datatable-streampage').DataTable().cell(rStreamIDs[1][rIndex], rUpdateColumns[i]).data(rItem[rUpdateColumns[i]]);
                                    }
                                }
                            }
                        });
                    }
                });
            }
        }
        clearTimeout(window.rRefresh);
        window.rRefresh = setTimeout(refreshInformation, 5000);
    }

    function getCategory() {
        return $("#stream_category_id").val();
    }

    function getFilter() {
        return $("#stream_filter").val();
    }

    function getServer() {
        return $("#stream_server_id").val();
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
        $("#datatable-streampage").draw();
    }

    function clearFilters() {
        window.rClearing = true;
        $("#stream_search").val("").trigger('change');
        $('#stream_filter').val("").trigger('change');
        $('#stream_server_id').val("").trigger('change');
        $('#stream_category_id').val("").trigger('change');
        $('#stream_show_entries').val("<?= $rSettings["default_entries"] ?: 10 ?>").trigger('change');
        window.rClearing = false;
        $('#datatable-streampage').DataTable().search($("#stream_search").val());
        $('#datatable-streampage').DataTable().page.len($('#stream_show_entries').val());
        $("#datatable-streampage").DataTable().page(0).draw('page');
        $('[data-toggle="tooltip"]').tooltip("hide");
        $("#datatable-streampage").DataTable().ajax.reload(null, false);
    }
    $(document).ready(function() {
        $(window).keypress(function(event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });
        formCache.init();
        <?php if (!isset(CoreUtilities::$request["filter"])) { ?>
            formCache.fetch();
        <?php } ?>

        $('select').select2({
            width: '100%'
        });
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
                    d.id = "streams",
                        d.category = getCategory();
                    <?php if ($rPermissions["is_admin"]) { ?>
                        d.filter = getFilter();
                    <?php } else { ?>
                        d.filter = 1;
                    <?php } ?>
                    d.server = getServer();
                }
            },
            columnDefs: [
                <?php if ($rPermissions["is_admin"]) {
                ?> {
                        "className": "dt-center",
                        "targets": [0, 1, 4, 5, 6, 7, 8, 9]
                    },
                    {
                        "orderable": false,
                        "targets": [6, 7]
                    }
                <?php } else {
                ?> {
                        "className": "dt-center",
                        "targets": [0, 1, 4]
                    }
                <?php } ?>
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: <?= $rSettings["default_entries"] ?: 10 ?>,
            lengthMenu: [10, 25, 50, 250, 500, 1000],
            stateSave: true
        });
        $("#datatable-streampage").css("width", "100%");
        $('#stream_search').keyup(function() {
            if (!window.rClearing) {
                $('#datatable-streampage').DataTable().search($(this).val()).draw();
            }
        });
        $('#stream_show_entries').change(function() {
            if (!window.rClearing) {
                $('#datatable-streampage').DataTable().page.len($(this).val()).draw();
            }
        });
        $('#stream_category_id').change(function() {
            if (!window.rClearing) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            }
        });
        $('#stream_server_id').change(function() {
            if (!window.rClearing) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            }
        });
        $('#stream_filter').change(function() {
            if (!window.rClearing) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            }
        });
        if ($("#datatable-streampage").DataTable().rows().count() <= 50) {
            setTimeout(refreshInformation, 5000);
        }
        if ($('#stream_search').val().length > 0) {
            $('#datatable-streampage').DataTable().search($('#stream_search').val()).draw();
        }
    });

    $(window).bind('beforeunload', function() {
        formCache.save();
    });
</script>
</body>

</html>