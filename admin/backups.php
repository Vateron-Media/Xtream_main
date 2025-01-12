<?php
include 'session.php';
include 'functions.php';

if (!checkPermissions()) {
    goHome();
}

if ((isset(ipTV_lib::$request["submit_settings"])) && (hasPermissions("adv", "database"))) {
    $rArray = getSettings();

    foreach (ipTV_lib::$request as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    if (ipTV_lib::setSettings($rArray)) {
        $_STATUS = 0;
    } else {
        $_STATUS = 1;
    }
}

$rSettings = getSettings(); // Update

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}


?>
<div class="wrapper boxed-layout-ext" <?php if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo ' style="display: none;"';
} ?>>
    <div class="container-fluid">
        <form action="#" method="POST">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title"><?= $_["backups"] ?></h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <?php if (isset($_STATUS) && $_STATUS == STATUS_SUCCESS): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Backup settings successfully updated!
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Backups will not contain any logs, restoring a database will therefore clear all of your
                            logs.<br />If you want to keep your logs you should manually create your own backups.
                        </div>
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-body">
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#backups" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-backup-restore mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["backups"] ?></span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="backups">
                                        <div class="tab-pane" id="backups">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group row mb-4">
                                                        <label class="col-md-4 col-form-label"
                                                            for="automatic_backups"><?= $_["automatic_backups"] ?></label>
                                                        <div class="col-md-2">
                                                            <select name="automatic_backups" id="automatic_backups"
                                                                class="form-control" data-toggle="select2">
                                                                <?php foreach (array("off" => "Off", "hourly" => "Hourly", "daily" => "Daily", "weekly" => "Weekly", "monthly" => "Monthly") as $rType => $rText) { ?>
                                                                    <option<?php if ($rSettings["automatic_backups"] == $rType) {
                                                                        echo " selected";
                                                                    } ?> value="<?= $rType ?>">
                                                                        <?= $rText ?></option>
                                                                    <?php } ?>
                                                            </select>
                                                        </div>
                                                        <label class="col-md-4 col-form-label"
                                                            for="backups_to_keep"><?= $_["backups_to_keep"] ?>
                                                            <i data-toggle="tooltip" data-placement="top" title=""
                                                                data-original-title="<?= $_["enter_for_unlimited"] ?>"
                                                                class="mdi mdi-information"></i></label>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control" id="backups_to_keep"
                                                                name="backups_to_keep"
                                                                value="<?= htmlspecialchars($rSettings["backups_to_keep"]) ?>">
                                                        </div>
                                                    </div>
                                                    <table class="table table-borderless mb-0" id="datatable-backups">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th class="text-center"><?= $_["date"] ?></th>
                                                                <th class="text-center"><?= $_["filename"] ?>
                                                                </th>
                                                                <th class="text-center"><?= $_["filesize"] ?>
                                                                </th>
                                                                <th class="text-center"><?= $_["actions"] ?>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div> <!-- end col -->
                                            </div> <!-- end row -->
                                            <ul class="list-inline wizard mb-0" style="margin-top:30px;">
                                                <li class="list-inline-item float-right">
                                                    <button id="create_backup" onClick="api('', 'backup')"
                                                        class="btn btn-info"><?= $_["create_backup_now"] ?></button>
                                                    <input name="submit_settings" type="submit" class="btn btn-primary"
                                                        value="<?= $_["save_changes"] ?>" />
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- end wrapper -->
<?php if ($rSettings["sidebar"]) {
    echo "</div>";
} ?>
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
<script src="assets/libs/moment/moment.min.js"></script>
<script src="assets/libs/daterangepicker/daterangepicker.js"></script>
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/js/app.min.js"></script>
<script>
    function api(rID, rType) {
        if (rType == "delete") {
            if (confirm('<?= $_["are_you_sure_you_want_to_delete_this_backup"] ?>') == false) {
                return;
            }
        } else if (rType == "restore") {
            if (confirm('<?= $_["are_you_sure_you_want_to_restore_from_this_backup"] ?>') == false) {
                return;
            } else {
                $.toast("<?= $_["restoring_backup"] ?>");
                $(".content-page").fadeOut();
            }
        } else if (rType == "backup") {
            $("#create_backup").attr("disabled", true);
        }
        $.getJSON("./api.php?action=backup&sub=" + rType + "&filename=" + encodeURIComponent(rID), function (
            data) {
            if (data.result === true) {
                if (rType == "delete") {
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip();
                    $.toast("<?= $_["backup_successfully_deleted"] ?>");
                } else if (rType == "restore") {
                    $.toast("<?= $_["restored_from_backup"] ?>");
                    $(".content-page").fadeIn();
                } else if (rType == "backup") {
                    $.toast("<?= $_["backup_has_been_successfully_generated"] ?>");
                    $("#create_backup").attr("disabled", false);
                }
                $("#datatable-backups").DataTable().ajax.reload(null, false);
            } else {
                $.toast("<?= $_["an_error_occured_while_processing_your_request"] ?>");
                if (rType == "backup") {
                    $("#create_backup").attr("disabled", false);
                }
                if (!$(".content-page").is(":visible")) {
                    $(".content-page").fadeIn();
                }
            }
        });
    }

    (function ($) {
        $.fn.inputFilter = function (inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
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

    $(document).ready(function () {
        $('select').select2({
            width: '100%'
        });
        $(window).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });

        $("#datatable-backups").DataTable({
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                $('[data-toggle="tooltip"]').tooltip();
            },
            bInfo: false,
            paging: false,
            searching: false,
            bSort: false,
            responsive: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "./table_search.php",
                "data": function (d) {
                    d.id = "backups"
                }
            },
            order: [
                [0, "desc"]
            ],
            columnDefs: [{
                "className": "dt-center",
                "targets": [0, 1, 2, 3]
            }],

        });
        $("#datatable-backups").css("width", "100%");
        $("form").attr('autocomplete', 'off');
        $("#backups_to_keep").inputFilter(function (value) {
            return /^\d*$/.test(value);
        });
    });
</script>
</body>

</html>