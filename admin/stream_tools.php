<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or (!hasPermissions("adv", "stream_tools"))) {
    exit;
}

if (isset(CoreUtilities::$request["replace_dns"])) {
    $rOldDNS = str_replace("/", "\/", CoreUtilities::$request["old_dns"]);
    $rNewDNS = str_replace("/", "\/", CoreUtilities::$request["new_dns"]);
    $ipTV_db_admin->query("UPDATE `streams` SET `stream_source` = REPLACE(`stream_source`, '" . $rOldDNS . "', '" . $rNewDNS . "');");
    $_STATUS = 1;
} elseif (isset(CoreUtilities::$request["move_streams"])) {
    $rSource = CoreUtilities::$request["source_server"];
    $rReplacement = CoreUtilities::$request["replacement_server"];
    $rExisting = array();
    $ipTV_db_admin->query("SELECT `id` FROM `streams_servers` WHERE `server_id` = " . intval($rReplacement) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $rExisting[] = intval($row["id"]);
        }
    }
    $ipTV_db_admin->query("SELECT `id` FROM `streams_servers` WHERE `server_id` = " . intval($rSource) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if (in_array(intval($row["id"]), $rExisting)) {
                $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `id` = " . intval($row["id"]) . ";");
            }
        }
    }
    $ipTV_db_admin->query("UPDATE `streams_servers` SET `server_id` = " . intval($rReplacement) . " WHERE `server_id` = " . intval($rSource) . ";");
    $_STATUS = 2;
} elseif (isset(CoreUtilities::$request["cleanup_streams"])) {
    $rStreams = getStreamList();
    $rStreamArray = array();
    foreach ($rStreams as $rStream) {
        $rStreamArray[] = intval($rStream["id"]);
    }
    $rDelete = array();
    $ipTV_db_admin->query("SELECT `server_stream_id`, `stream_id` FROM `streams_servers`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["server_stream_id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $ipTV_db_admin->query("DELETE FROM `streams_servers` WHERE `server_stream_id` IN (" . join(",", $rDelete) . ");");
    }
    $rDelete = array();
    $ipTV_db_admin->query("SELECT `id`, `stream_id` FROM `client_logs`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $ipTV_db_admin->query("DELETE FROM `client_logs` WHERE `id` IN (" . join(",", $rDelete) . ");");
    }
    $rDelete = array();
    $ipTV_db_admin->query("SELECT `id`, `stream_id` FROM `stream_logs`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $ipTV_db_admin->query("DELETE FROM `stream_logs` WHERE `id` IN (" . join(",", $rDelete) . ");");
    }
    $rDelete = array();
    $ipTV_db_admin->query("SELECT `activity_id`, `stream_id` FROM `user_activity`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["activity_id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $ipTV_db_admin->query("DELETE FROM `user_activity` WHERE `activity_id` IN (" . join(",", $rDelete) . ");");
    }
    $_STATUS = 3;
}

include "header.php";
?>
<div class="wrapper boxed-layout">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="./streams.php">
                                <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                    <?= $_["back_to_streams"] ?> </li>
                            </a>
                        </ol>
                    </div>
                    <h4 class="page-title"><?= $_["stream_tools"] ?> </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <?php if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["stream_dns_replacement"] ?>
                    </div>
                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["streams_have_been_moved"] ?>
                    </div>
                <?php } elseif ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["stream_cleanup_was_successful"] ?>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <div id="basicwizard">
                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                <li class="nav-item">
                                    <a href="#dns-replacement" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                        <i class="mdi mdi-dns mr-1"></i>
                                        <span class="d-none d-sm-inline"><?= $_["dns_eeplacement"] ?>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#move-streams" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                        <i class="mdi mdi-folder-move mr-1"></i>
                                        <span class="d-none d-sm-inline"><?= $_["move_streams"] ?> </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#cleanup" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                        <i class="mdi mdi-wrench mr-1"></i>
                                        <span class="d-none d-sm-inline"><?= $_["bouquet_order"] ?>
                                            <?= $_["cleanup"] ?> </span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content b-0 mb-0 pt-0">
                                <div class="tab-pane" id="dns-replacement">
                                    <form action="./stream_tools.php" method="POST" id="tools_form"
                                        data-parsley-validate="">
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="sub-header">
                                                    <?= $_["the_dns_replacement"] ?>
                                                </p>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="old_dns"><?= $_["old_dns"] ?> </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="old_dns"
                                                            name="old_dns" value="" placeholder="http://example.com"
                                                            required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="new_dns"><?= $_["new_dns"] ?> </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="new_dns"
                                                            name="new_dns" value="" placeholder="http://newdns.com"
                                                            required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="list-inline-item">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="confirmReplace">
                                                    <label class="custom-control-label"
                                                        for="confirmReplace"><?= $_["i_confirm_remplace"] ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item float-right">
                                                <input disabled name="replace_dns" id="replace_dns" type="submit"
                                                    class="btn btn-primary" value="<?= $_["replace_dns"] ?>" />
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                                <div class="tab-pane" id="move-streams">
                                    <form action="./stream_tools.php" method="POST" id="tools_form"
                                        data-parsley-validate="">
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="sub-header">
                                                    <?= $_["this_tool_will_allow_you"] ?>
                                                </p>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="source_server"><?= $_["source_server"] ?>
                                                    </label>
                                                    <div class="col-md-8">
                                                        <select name="source_server" id="source_server"
                                                            class="form-control select2" data-toggle="select2">
                                                            <?php foreach ($rServers as $rServer) { ?>
                                                                <option value="<?= $rServer["id"] ?>">
                                                                    <?= $rServer["server_name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="replacement_server"><?= $_["replacement_server"] ?>
                                                    </label>
                                                    <div class="col-md-8">
                                                        <select name="replacement_server" id="replacement_server"
                                                            class="form-control select2" data-toggle="select2">
                                                            <?php foreach ($rServers as $rServer) { ?>
                                                                <option value="<?= $rServer["id"] ?>">
                                                                    <?= $rServer["server_name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="list-inline-item">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="confirmReplace2">
                                                    <label class="custom-control-label"
                                                        for="confirmReplace2"><?= $_["i_confirm_move"] ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item float-right">
                                                <input disabled name="move_streams" id="move_streams" type="submit"
                                                    class="btn btn-primary" value="<?= $_["move_streams"] ?>" />
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                                <div class="tab-pane" id="cleanup">
                                    <form action="./stream_tools.php" method="POST" id="tools_form"
                                        data-parsley-validate="">
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="sub-header">
                                                    <?= $_["this_tool_will_clean"] ?>
                                                </p>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="list-inline-item">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="confirmReplace3">
                                                    <label class="custom-control-label"
                                                        for="confirmReplace3"><?= $_["i_confirm_clean"] ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item float-right">
                                                <input disabled name="cleanup_streams" id="cleanup_streams"
                                                    type="submit" class="btn btn-primary"
                                                    value="<?= $_["cleanup"] ?>" />
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                            </div> <!-- tab-content -->
                        </div> <!-- end #basicwizard-->
                    </div> <!-- end card-body -->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
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
<script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
<script src="assets/libs/switchery/switchery.min.js"></script>
<script src="assets/libs/select2/select2.min.js"></script>
<script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
<script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
<script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
<script src="assets/libs/moment/moment.min.js"></script>
<script src="assets/libs/daterangepicker/daterangepicker.js"></script>
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
<script src="assets/libs/parsleyjs/parsley.min.js"></script>
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/js/app.min.js"></script>

<script>
    $(document).ready(function () {
        $('select.select2').select2({
            width: '100%'
        });
        $(window).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });
        $("#confirmReplace").change(function () {
            if ($(this).is(":checked")) {
                $("#replace_dns").attr("disabled", false);
            } else {
                $("#replace_dns").attr("disabled", true);
            }
        });
        $("#confirmReplace2").change(function () {
            if ($(this).is(":checked")) {
                $("#move_streams").attr("disabled", false);
            } else {
                $("#move_streams").attr("disabled", true);
            }
        });
        $("#confirmReplace3").change(function () {
            if ($(this).is(":checked")) {
                $("#cleanup_streams").attr("disabled", false);
            } else {
                $("#cleanup_streams").attr("disabled", true);
            }
        });
        $("form").attr('autocomplete', 'off');
    });
</script>
</body>

</html>