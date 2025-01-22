<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_server")) && (!hasPermissions("adv", "edit_server")))) {
    exit;
}

if (!checkPermissions()) {
    goHome();
}

if (!isset(ipTV_lib::$request['id']) || !isset($rServers[ipTV_lib::$request['id']])) {
    goHome();
    return;
}

$rServerArr = $rServers[ipTV_lib::$request['id']];

$rWatchdog = json_decode($rServerArr['watchdog_data'], true);
$rServiceMax = (0 < intval($rWatchdog['cpu_cores']) ? $rWatchdog['cpu_cores'] : 16);

if ($rServiceMax < 4) {
    $rServiceMax = 4;
}


$_TITLE = 'Edit Server';
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
                            <a href="./servers.php">
                                <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                    <?= $_["back_to_servers"] ?></li>
                            </a>
                        </ol>
                    </div>
                    <h4 class="page-title"><?php if (isset($rServerArr)) {
                        echo $_["edit"];
                    } else {
                        echo $_["add"];
                    } ?> <?= $_["server"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <?php if (isset($_STATUS) && $_STATUS == STATUS_SUCCESS): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $_["server_success"] ?>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-body">
                        <form action="#" method="POST" id="server_form" data-parsley-validate="">
                            <?php if (isset($rServerArr)) { ?>
                                <input type="hidden" name="edit" value="<?= $rServerArr["id"] ?>" />
                                <input type="hidden" name="status" value="<?= $rServerArr["status"] ?>" />
                            <?php } ?>
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#server-details" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#advanced-options" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["advanced"] ?></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#performance" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-flash mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["perfomance"] ?></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#ispmanager" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["isp_manager"] ?></span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="server-details">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="server_name"><?= $_["server_name"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="server_name"
                                                            name="server_name" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["server_name"]);
                                                            } ?>" required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="domain_name"><?= $_["domaine_name"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="domain_name"
                                                            name="domain_name" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["domain_name"]);
                                                            } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="server_ip"><?= $_["server_ip"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="server_ip"
                                                            name="server_ip" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["server_ip"]);
                                                            } ?>" required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="private_ip"><?= $_["private_ip"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="private_ip"
                                                            name="private_ip" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["private_ip"]);
                                                            } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="total_clients"><?= $_["max_clients"] ?></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="total_clients"
                                                            name="total_clients" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["total_clients"]);
                                                            } else {
                                                                echo "1000";
                                                            } ?>" required data-parsley-trigger="change">
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="timeshift_only"><?= $_["timeshift_only"] ?></label>
                                                    <div class="col-md-2">
                                                        <input name="timeshift_only" id="timeshift_only" type="checkbox"
                                                            <?php if (isset($rServerArr)) {
                                                                if ($rServerArr["timeshift_only"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="enabled">Enabled <i
                                                            title="Utilise this server for connections and streams."
                                                            class="tooltip text-secondary far fa-circle"></i></label>
                                                    <div class="col-md-2">
                                                        <input <?php if ($rServerArr['is_main']) {
                                                            echo 'readonly';
                                                        } ?>
                                                            name="enabled" id="enabled" type="checkbox" <?php if ($rServerArr['enabled'] == 1) {
                                                                echo 'checked';
                                                            } ?>
                                                            data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                    <label class="col-md-4 col-form-label" for="enable_proxy">Proxied
                                                        (not suport) <i
                                                            title="Route connections through allocated proxies."
                                                            class="tooltip text-secondary far fa-circle"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="enable_proxy" id="enable_proxy" type="checkbox"
                                                            <?php if ($rServerArr['enable_proxy'] == 1) {
                                                                echo 'checked';
                                                            } ?> data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="next list-inline-item float-right">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["next"] ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" id="advanced-options">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="http_broadcast_port"><?= $_["http_port"] ?></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="http_broadcast_port"
                                                            name="http_broadcast_port" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["http_broadcast_port"]);
                                                            } else {
                                                                echo "25461";
                                                            } ?>" required data-parsley-trigger="change">
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="https_broadcast_port"><?= $_["https_port"] ?></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control"
                                                            id="https_broadcast_port" name="https_broadcast_port" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["https_broadcast_port"]);
                                                            } else {
                                                                echo "25463";
                                                            } ?>" required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="rtmp_port"><?= $_["rtmp_port"] ?></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="rtmp_port"
                                                            name="rtmp_port" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["rtmp_port"]);
                                                            } else {
                                                                echo "25462";
                                                            } ?>" required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="network_interface">Network Interface</label>
                                                    <div class="col-md-2">
                                                        <select name="network_interface" id="network_interface"
                                                            class="form-control select2" data-toggle="select2">network
                                                            interface
                                                            <?php foreach (array_merge(['auto'], json_decode($rServerArr['interfaces'], true) ?: []) as $rInterface): ?>
                                                                <option <?= $rServerArr['network_interface'] == $rInterface ? 'selected' : ''; ?> value="<?= $rInterface; ?>">
                                                                    <?= $rInterface; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="network_guaranteed_speed"><?= $_["network_speed"] ?></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control"
                                                            id="network_guaranteed_speed"
                                                            name="network_guaranteed_speed" value="<?php if (isset($rServerArr)) {
                                                                echo htmlspecialchars($rServerArr["network_guaranteed_speed"]);
                                                            } else {
                                                                echo "1000";
                                                            } ?>" required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="geoip_type">GeoIP
                                                        Priority</label>
                                                    <div class="col-md-8">
                                                        <select name="geoip_type" id="geoip_type"
                                                            class="form-control select2" data-toggle="select2">
                                                            <?php foreach (['high_priority' => 'High Priority', 'low_priority' => 'Low Priority', 'strict' => 'Strict'] as $rType => $rText): ?>
                                                                <option <?= $rServerArr['geoip_type'] == $rType ? 'selected' : ''; ?> value="<?= $rType; ?>"><?= $rText; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="geoip_countries"><?= $_["geoip_countries"] ?></label>
                                                    <div class="col-md-8">
                                                        <select name="geoip_countries[]" id="geoip_countries"
                                                            class="form-control select2-multiple" data-toggle="select2"
                                                            multiple="multiple" data-placeholder="<?= $_["choose"] ?>">
                                                            <?php $rSelected = json_decode($rServerArr["geoip_countries"], true);
                                                            foreach ($rCountries as $rCountry) { ?>
                                                                <option <?php if (isset($rServerArr)) {
                                                                    if (!empty($rSelected) && in_array($rCountry["id"], $rSelected)) {
                                                                        echo "selected ";
                                                                    }
                                                                } ?>value="<?= $rCountry["id"] ?>">
                                                                    <?= $rCountry["name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="enable_geoip"><?= $_["geoip_load_balancing"] ?></label>
                                                    <div class="col-md-2">
                                                        <input name="enable_geoip" id="enable_geoip" type="checkbox"
                                                            <?php if (isset($rServerArr)) {
                                                                if ($rServerArr["enable_geoip"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="previous list-inline-item">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["prev"] ?></a>
                                            </li>
                                            <li class="next list-inline-item float-right">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["next"] ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" id="performance">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="total_services">PHP
                                                        Services <i
                                                            title="How many PHP-FPM daemons to run on this server. You can use up to a maximum of one per core."
                                                            class="tooltip text-secondary far fa-circle"></i></label>
                                                    <div class="col-md-2">
                                                        <select name="total_services" id="total_services"
                                                            class="form-control select2" data-toggle="select2">
                                                            <?php foreach (range(1, $rServiceMax) as $rInt): ?>
                                                                <option <?php if ($rServerArr['total_services'] == $rInt || $rInt == 4)
                                                                    echo 'selected '; ?>value="<?php echo $rInt; ?>">
                                                                    <?php echo $rInt; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <?php if ($rServerArr['is_main']): ?>
                                                        <label class="col-md-4 col-form-label" for="enable_gzip">GZIP
                                                            Compression <i
                                                                title="Compressing server output on your main server will reduce network output significantly, but will increase CPU usage. If you have CPU to spare but your network usage is high, you should enable this."
                                                                class="tooltip text-secondary far fa-circle"></i></label>
                                                        <div class="col-md-2">
                                                            <input name="enable_gzip" id="enable_gzip" type="checkbox" <?php if ($rServerArr['enable_gzip'] == 1)
                                                                echo 'checked '; ?>data-plugin="switchery" class="js-switch"
                                                                data-color="#039cfd" />
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="limit_requests">Rate
                                                        Limit - Per Second <i
                                                            title="Limit requests per second. This can be enabled if your server can't keep up with the incoming requests. Set to 0 to disable."
                                                            class="tooltip text-secondary far fa-circle"></i></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control text-center"
                                                            id="limit_requests" name="limit_requests"
                                                            value="<?php echo htmlspecialchars($rServerArr['limit_requests']); ?>"
                                                            required data-parsley-trigger="change">
                                                    </div>
                                                    <label class="col-md-4 col-form-label" for="limit_burst">Rate Limit
                                                        - Burst Queue <i
                                                            title="When the request limit is reached, excess requests will be dropped by default. You can push these requests into a queue which will be fulfilled in order rather than concurrently. This will help ease the flow of traffic and make sure service isn't disrupted by the rate limiting."
                                                            class="tooltip text-secondary far fa-circle"></i></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control text-center"
                                                            id="limit_burst" name="limit_burst"
                                                            value="<?php echo htmlspecialchars($rServerArr['limit_burst']); ?>"
                                                            required data-parsley-trigger="change">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="sysctl">Custom
                                                        Sysctl.conf <i
                                                            title="Write a custom sysctl.conf to the server. You can break your server by inputting incorrect values here, this is for advanced usage only. The Default template is provided for restorative and informative purposes."
                                                            class="tooltip text-secondary far fa-circle"></i><br /><br /><input
                                                            onClick="setDefault();" type="button"
                                                            class="btn btn-light btn-xs" value="Default" /></label>
                                                    <div class="col-md-8">
                                                        <textarea class="form-control" id="sysctl" name="sysctl"
                                                            rows="16"><?php echo $rServerArr['sysctl']; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <ul class="list-inline wizard mb-0">
                                            <li class="previous list-inline-item">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["prev"] ?></a>
                                            </li>
                                            <li class="next list-inline-item float-right">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["next"] ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" id="ispmanager">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="enable_isp">enable
                                                        isp</label>
                                                    <div class="col-md-2">
                                                        <input name="enable_isp" id="enable_isp" type="checkbox" <?php if (isset($rServerArr)) {
                                                            if ($rServerArr["enable_isp"] == 1) {
                                                                echo "checked ";
                                                            }
                                                        } ?>data-plugin="switchery"
                                                            class="js-switch" data-color="#039cfd" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <select name="isp_type" id="isp_type"
                                                            class="form-control select2" data-toggle="select2">
                                                            <?php foreach (array("high_priority" => "High Priority", "low_priority" => "Low Priority", "strict" => "Strict") as $rType => $rText) { ?>
                                                                <option <?php if (isset($rServerArr)) {
                                                                    if ($rServerArr["isp_type"] == $rType) {
                                                                        echo "selected ";
                                                                    }
                                                                } ?>value="<?= $rType ?>">
                                                                    <?= $rText ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="isp_field">Allowed ISP
                                                        Names</label>
                                                    <div class="col-md-8 input-group">
                                                        <input type="text" id="isp_field" class="form-control" value="">
                                                        <div class="input-group-append">
                                                            <a href="javascript:void(0)" id="add_isp"
                                                                class="btn btn-primary waves-effect waves-light"><i
                                                                    class="mdi mdi-plus"></i></a>
                                                            <a href="javascript:void(0)" id="remove_isp"
                                                                class="btn btn-danger waves-effect waves-light"><i
                                                                    class="mdi mdi-close"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="isp_names">&nbsp;</label>
                                                    <div class="col-md-8">
                                                        <select id="isp_names" name="isp_names[]" size=6
                                                            class="form-control" multiple="multiple">
                                                            <?php $rnabilosss = json_decode($rServerArr["isp_names"], true);
                                                            if ((isset($rServerArr)) & (is_array($rnabilosss))) {
                                                                foreach ($rnabilosss as $ispnom) { ?>
                                                                    <option value="<?= $ispnom ?>"><?= $ispnom ?>
                                                                    </option>
                                                                <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>

                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="previous list-inline-item">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["prev"] ?></a>
                                            </li>
                                            <li class="next list-inline-item float-right">
                                                <input name="submit_server" type="submit" class="btn btn-primary" value="<?php if (isset($rServerArr)) {
                                                    echo $_["edit"];
                                                } else {
                                                    echo $_["add"];
                                                } ?>" />
                                            </li>
                                        </ul>
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
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/libs/treeview/jstree.min.js"></script>
<script src="assets/js/pages/treeview.init.js"></script>
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/libs/parsleyjs/parsley.min.js"></script>
<script src="assets/js/app.min.js"></script>
<?php include 'post.php'; ?>


<script>
    var swObjs = {};
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
        $('select.select2').select2({
            width: '100%'
        })
        $("#geoip_countries").select2({
            width: '100%'
        })
        $(".js-switch").each(function (index, element) {
            var init = new Switchery(element);
            window.swObjs[element.id] = init;
        });

        $('#exp_date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minDate: new Date(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        $("#no_expire").change(function () {
            if ($(this).prop("checked")) {
                $("#exp_date").prop("disabled", true);
            } else {
                $("#exp_date").removeAttr("disabled");
            }
        });
        $("#server_form").submit(function (e) {
            $("#isp_names option").prop('selected', true);
        });
        $("#add_isp").click(function () {
            if ($("#isp_field").val().length > 0) {
                var o = new Option($("#isp_field").val(), $("#isp_field").val());
                $("#isp_names").append(o);
                $("#isp_field").val("");
            } else {
                $.toast("Please enter a valid ISP name.");
            }
        });
        $("#remove_isp").click(function () {
            $('#isp_names option:selected').remove();
        });

        $(window).keypress(function (event) {
            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
        });

        $("#total_clients").inputFilter(function (value) {
            return /^\d*$/.test(value);
        });
        $("#http_broadcast_port").inputFilter(function (value) {
            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
        });
        $("#https_broadcast_port").inputFilter(function (value) {
            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
        });
        $("#rtmp_port").inputFilter(function (value) {
            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
        });
        $("#network_guaranteed_speed").inputFilter(function (value) {
            return /^\d*$/.test(value);
        });
        $("form").attr('autocomplete', 'off');
        $("form").submit(function (e) {
            e.preventDefault();
            $(':input[type="submit"]').prop('disabled', true);
            submitForm(window.rCurrentPage, new FormData($("form")[0]));
        });

    });

    function setDefault() {
        $("#sysctl").val("# XC_VM\n\nnet.ipv4.tcp_congestion_control = bbr\nnet.core.default_qdisc = fq\nnet.ipv4.tcp_rmem = 8192 87380 134217728\nnet.ipv4.udp_rmem_min = 16384\nnet.core.rmem_default = 262144\nnet.core.rmem_max = 268435456\nnet.ipv4.tcp_wmem = 8192 65536 134217728\nnet.ipv4.udp_wmem_min = 16384\nnet.core.wmem_default = 262144\nnet.core.wmem_max = 268435456\nnet.core.somaxconn = 1000000\nnet.core.netdev_max_backlog = 250000\nnet.core.optmem_max = 65535\nnet.ipv4.tcp_max_tw_buckets = 1440000\nnet.ipv4.tcp_max_orphans = 16384\nnet.ipv4.ip_local_port_range = 2000 65000\nnet.ipv4.tcp_no_metrics_save = 1\nnet.ipv4.tcp_slow_start_after_idle = 0\nnet.ipv4.tcp_fin_timeout = 15\nnet.ipv4.tcp_keepalive_time = 300\nnet.ipv4.tcp_keepalive_probes = 5\nnet.ipv4.tcp_keepalive_intvl = 15\nfs.file-max=20970800\nfs.nr_open=20970800\nfs.aio-max-nr=20970800\nnet.ipv4.tcp_timestamps = 1\nnet.ipv4.tcp_window_scaling = 1\nnet.ipv4.tcp_mtu_probing = 1\nnet.ipv4.route.flush = 1\nnet.ipv6.route.flush = 1");
    }
</script>
</body>

</html>