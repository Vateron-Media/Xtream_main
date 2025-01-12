<?php
include 'session.php';
include 'functions.php';

if (!$rPermissions["is_admin"]) {
    exit;
}
ipTV_lib::$settings = ipTV_lib::getSettings(true);
$rSettings = ipTV_lib::$settings;

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ((isset(ipTV_lib::$request["submit_settings"])) && (hasPermissions("adv", "settings"))) {
    $rCheck = array(false, false);
    $rCron = array('*', '*', '*', '*', '*');
    $rPattern = '/^[0-9\\/*,-]+$/';
    $rCron[0] = ipTV_lib::$request['minute'];
    preg_match($rPattern, $rCron[0], $rMatches);
    $rCheck[0] = 0 < count($rMatches);
    $rCron[1] = ipTV_lib::$request['hour'];
    preg_match($rPattern, $rCron[1], $rMatches);
    $rCheck[1] = 0 < count($rMatches);
    $rCronOutput = implode(' ', $rCron);

    if (isset(ipTV_lib::$request['cache_changes'])) {
        $rCacheChanges = true;
    } else {
        $rCacheChanges = false;
    }

    if ($rCheck[0] && $rCheck[1]) {
        $ipTV_db_admin->query("UPDATE `crontab` SET `time` = '" . $rCronOutput . "' WHERE `filename` = 'cache_engine.php';");
        ipTV_lib::setSettings(["cache_thread_count" => ipTV_lib::$request['cache_thread_count'], "cache_changes" => $rCacheChanges]);

        if (file_exists(TMP_PATH . 'crontab')) {
            unlink(TMP_PATH . 'crontab');
        }

        $_STATUS = 0;
    }

    $_STATUS = 1;
}
?>

<div class="wrapper boxed-layout-ext" <?php if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo '';
                                      } else {
                                          echo ' style="display: none;"';
                                      } ?>>
    <div class="container-fluid">
        <form action="./cache.php" method="POST">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title"><?= $_["cache_cron_redis_settings"] ?></h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <?php if (isset($_STATUS) && $_STATUS == STATUS_SUCCESS) : ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["cache_cron_redis_succefull_update"] ?>
                        </div>
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-body">
                            <?php
                            $rColour = 'secondary'; // Default color
                            $rHeader = 'Poor'; // Default header
                            $rSize = 25; // Default size
                            
                            $rMessage = "You're using neither Caching or Redis Connection Handler, the server will perform poorly compared to having either enabled."; // Default message
                            
                            if (ipTV_lib::$settings['enable_cache'] || ipTV_lib::$settings['redis_handler']) {
                                $rHeader = 'Good';
                                $rColour = 'info';
                                $rMessage = "Redis Connection Handler is disabled on your service, if you have a lot of throughput you will see better performance with Redis enabled.<br/>If you maintain active connections of over 10,000 for example you should consider this. Below this amount you're unlikely to see any benefit.";
                                $rSize = 75;

                                if (!ipTV_lib::$settings['enable_cache']) {
                                    $rSize = 50;
                                    $rMessage = 'Caching is disabled on your service, this will impact performance significantly under load compared to having it enabled.';
                                }

                                if (ipTV_lib::$settings['enable_cache'] && ipTV_lib::$settings['redis_handler']) {
                                    $rSize = 100;
                                    $rColour = 'pink';
                                    $rHeader = 'Maximum';
                                    $rMessage = "You're using both Caching and Redis Connection Handler, your service is optimised for <strong>maximum performance</strong>!";
                                }
                            }
                            ?>
                            <h5 class="card-title"><?= $rHeader ?> Performance</h5>
                            <p><?= $rMessage ?></p>
                            <div class="progress mb-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $rColour ?>"
                                    role="progressbar" aria-valuenow="<?= $rSize ?>" aria-valuemin="0"
                                    aria-valuemax="100" style="width: <?= $rSize ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#cache" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-cached mr-1"></i>
                                            <span class="d-none d-sm-inline">Caching System</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#connections" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-lan-connect mr-1"></i>
                                            <span class="d-none d-sm-inline">Redis Connection Handler (not
                                                worked)</span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="cache">
                                        <div class="row">
                                            <div class="col-12">
                                                <?php if ($rSettings['enable_cache']) : ?>
                                                    <?php
                                                    $ipTV_db_admin->query("SELECT `time` FROM `crontab` WHERE `filename` = 'cache_engine.php';");
                                                    list($rMinute, $rHour, $rDayOfMonth, $rMonth, $rDayOfWeek) = explode(' ', $ipTV_db_admin->get_row()['time']);
                                                    $ipTV_db_admin->query('SELECT `id` FROM `users`;');
                                                    $rUserCount = $ipTV_db_admin->num_rows();
                                                    $ipTV_db_admin->query('SELECT `id` FROM `streams`;');
                                                    $rStreamCount = $ipTV_db_admin->num_rows();
                                                    $ipTV_db_admin->query('SELECT `id` FROM `series`;');
                                                    $rSeriesCount = $ipTV_db_admin->num_rows();
                                                    $rUserCountR = count(glob(USER_TMP_PATH . 'user_i_*'));
                                                    $rStreamCountR = count(glob(STREAMS_TMP_PATH . 'stream_*'));
                                                    $rSeriesCountR = count(glob(SERIES_TMP_PATH . 'series_*')) - 2;
                                                    $rSeriesCountR = max($rSeriesCountR, 0);
                                                    $rFreeCache = 100 - intval(disk_free_space(TMP_PATH) / disk_total_space(TMP_PATH) * 100);
                                                    ?>

                                                    <?php if ($rFreeCache >= 90) : ?>
                                                        <div class="alert alert-danger mb-4" role="alert">
                                                            <?= str_replace("{free_cache}", $rFreeCache, $_["cache_cron_danger"]) ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!file_exists(CACHE_TMP_PATH . 'cache_complete')) : ?>
                                                        <div class="alert alert-warning mb-4" role="alert">
                                                            <?= $_["cache_cron_warning"] ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <h5 class="card-title"><?= $_["cache_cron"] ?></h5>
                                                    <p><?= str_replace("{time}", date("Y-m-d H:i:s", $rSettings['last_cache']), $_["cache_cron_description"]) ?>
                                                    </p>
                                                    <div class="form-group row mb-4">
                                                        <table class="table table-striped table-borderless mb-0"
                                                            id="datatable-cache">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-center"><?= $_["cache_cron_minute"] ?>
                                                                    </td>
                                                                    <td style="width:250px;"><input type="text"
                                                                            class="form-control text-center" id="minute"
                                                                            name="minute" value="<?= $rMinute ?>"></td>
                                                                    <td class="text-center"><?= $_["cache_cron_hour"] ?>
                                                                    </td>
                                                                    <td style="width:250px;"><input type="text"
                                                                            class="form-control text-center" id="hour"
                                                                            name="hour" value="<?= $rHour ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-center">
                                                                        <?= $_["cache_cron_thread_count"] ?>
                                                                    </td>
                                                                    <td><input type="text" class="form-control text-center"
                                                                            id="cache_thread_count"
                                                                            name="cache_thread_count"
                                                                            value="<?= intval($rSettings['cache_thread_count']) ?>">
                                                                    </td>
                                                                    <td class="text-center">Update Changes Only</td>
                                                                    <td>
                                                                        <input name="cache_changes" id="cache_changes"
                                                                            type="checkbox"
                                                                            <?= $rSettings['cache_changes'] == 1 ? 'checked' : '' ?> data-plugin="switchery" class="js-switch"
                                                                            data-color="#039cfd" />
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-center"><?= $_["cache_cron_streams"] ?>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button type="button"
                                                                            class="btn btn-info btn-xs waves-effect waves-light"><?= number_format($rStreamCountR) ?>
                                                                            / <?= number_format($rStreamCount) ?></button>
                                                                    </td>
                                                                    <td class="text-center"><?= $_["cache_cron_users"] ?>
                                                                    </td>

                                                                    <td class="text-center">
                                                                        <button type="button"
                                                                            class="btn btn-info btn-xs waves-effect waves-light"><?= number_format($rUserCountR) ?>
                                                                            / <?= number_format($rUserCount) ?></button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-center"><?= $_["cache_cron_series"] ?>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button type="button"
                                                                            class="btn btn-info btn-xs waves-effect waves-light"><?= number_format($rSeriesCountR) ?>
                                                                            / <?= number_format($rSeriesCount) ?></button>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <?= $_["cache_cron_time_taken"] ?>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button type="button"
                                                                            class="btn btn-info btn-xs waves-effect waves-light"><?= secondsToTime($rSettings['last_cache_taken']) ?></button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else : ?>
                                                    <h5 class="card-title">Cache is Disabled</h5>
                                                    <p>You have chosen to disable Cache system. You can re-enable it by
                                                        clicking the Enable Cache box below, however when doing so you would
                                                        get best results restarting XUI on this server.</p>
                                                <?php endif; ?>

                                                <ul class="list-inline wizard mb-0" style="margin-top:30px;">
                                                    <?php if ($rSettings['enable_cache']) : ?>
                                                        <li class="list-inline-item">
                                                            <button id="disable_cache" onClick="api('disable_cache')"
                                                                class="btn btn-danger" type="button">Disable Cache</button>
                                                            <button id="regenerate_cache" onClick="api('regenerate_cache')"
                                                                class="btn btn-info" type="button">Regenerate Cache</button>
                                                        </li>
                                                        <li class="list-inline-item float-right">
                                                            <input name="submit_settings" type="submit"
                                                                class="btn btn-primary" value="Save Cron" />
                                                        </li>
                                                    <?php else : ?>
                                                        <li class="list-inline-item">
                                                            <button id="enable_cache" onClick="api('enable_cache')"
                                                                class="btn btn-success" type="button">Enable Cache</button>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="connections">
                                        <div class="row">
                                            <div class="col-12">
                                                <h5 class="card-title">Redis Connection Handler</h5>
                                                <p>The handler will allow all connections from clients to load balancers
                                                    to be verified and managed using Redis rather than through mysql
                                                    connections.<br /><br /><strong>Disabling Redis handler will
                                                        disconnect all of your active clients, enabling it however
                                                        should move the live connections from MySQL to Redis without
                                                        disconnects.</strong></p>
                                                <h5 class="card-title mt-4">Pros & Cons</h5>
                                                <p>Before deciding whether Redis Connection Handler is right for you,
                                                    you should know a few things. Firstly, enabling Redis will
                                                    significantly increase XUI's ability to handle connections as the
                                                    previous bottleneck would be from MySQL not being able to handle the
                                                    amount of incoming client requests. You'll also find that zap time
                                                    will be quicker, CPU should be lower and things will generally run
                                                    quite smoothly.<br /><br />The drawbacks from using Redis is that
                                                    the live connection database is stored in memory, although a backup
                                                    is periodically written, restarting XUI can result in connection
                                                    losses. In addition to this, your ability to filter or search some
                                                    content in the Admin or Reseller interface will be diminished. For
                                                    example, with Redis on you can only sort Live Connections by Time
                                                    Active ascending or descending and you cannot search the live
                                                    connection list. You also lose the ability to sort by Active
                                                    Connections in Lines or Content pages etc.<br /><br />The best way
                                                    to decide if Redis is right for you is to try it for yourself.</p>

                                                <?php if ($rSettings['redis_handler']) :
                                                    try {
                                                        ipTV_lib::$redis = new Redis();
                                                        ipTV_lib::$redis->connect(ipTV_lib::$Servers[SERVER_ID]['server_ip'], 6379);
                                                        $rStatus = true;
                                                    } catch (Exception $e) {
                                                        $rStatus = false;
                                                    }

                                                    try {
                                                        ipTV_lib::$redis->auth(ipTV_lib::$settings['redis_password']);
                                                        $rAuth = true;
                                                    } catch (Exception $e) {
                                                        $rAuth = false;
                                                    }
                                                    ?>

                                                    <div class="form-group row mb-4 mt-4">
                                                        <table class="table table-striped table-borderless mb-0"
                                                            id="datatable-redis">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-center">Server Status</td>
                                                                    <td class="text-center">
                                                                        <?php if ($rStatus) : ?>
                                                                            <button type="button"
                                                                                class="btn btn-success btn-xs waves-effect waves-light btn-fixed-xl">ONLINE</button>
                                                                        <?php else : ?>
                                                                            <button type="button"
                                                                                class="btn btn-danger btn-xs waves-effect waves-light btn-fixed-xl">OFFLINE</button>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="text-center">Authentication</td>
                                                                    <td class="text-center">
                                                                        <?php if ($rAuth) : ?>
                                                                            <button type="button"
                                                                                class="btn btn-success btn-xs waves-effect waves-light btn-fixed-xl">AUTHENTICATED</button>
                                                                        <?php else : ?>
                                                                            <button type="button"
                                                                                class="btn btn-danger btn-xs waves-effect waves-light btn-fixed-xl">INVALID
                                                                                PASSWORD</button>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else : ?>
                                                    <p><strong>You have chosen to disable Redis Connection Handler. Click
                                                            the button below to re-enable it.</strong></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <ul class="list-inline wizard mb-0" style="margin-top:30px;">
                                            <?php if ($rSettings['redis_handler']) : ?>
                                                <li class="list-inline-item">
                                                    <button id="disable_handler" onClick="api('disable_handler')"
                                                        class="btn btn-danger" type="button">Disable Handler</button>
                                                    <button id="clear_redis" onClick="api('clear_redis')"
                                                        class="btn btn-info" type="button">Clear Database</button>
                                                </li>
                                            <?php else : ?>
                                                <li class="list-inline-item">
                                                    <button id="enable_handler" onClick="api('enable_handler')"
                                                        class="btn btn-success" type="button">Enable Handler</button>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
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
    function api(rType, rConfirm = false) {
        if ((rType == "clear_redis") && (!rConfirm)) {
            new jBox("Confirm", {
                confirmButton: "Clear",
                cancelButton: "Cancel",
                content: "Are you sure you want to clear the Redis database? This will drop all connections.",
                confirm: function () {
                    api(rType, true);
                }
            }).open();
        } else {
            rConfirm = true;
        }
        if (rConfirm) {
            if (rType == "regenerate_cache") {
                $.toast("Regenerating cache in the background...");
                $("#regenerate_cache").attr("disabled", true);
            } else if (rType == "disable_cache") {
                $.toast("Cache has been completely disabled!");
                $("#disable_cache").attr("disabled", true);
                $("#restart_cache").attr("disabled", true);
            } else if (rType == "enable_cache") {
                $.toast("Cache has been enabled!");
                $("#enable_cache").attr("disabled", true);
            } else if (rType == "disable_handler") {
                $.toast("Handler has been completely disabled!");
                $("#disable_handler").attr("disabled", true);
            } else if (rType == "enable_handler") {
                $.toast("Handler has been enabled!");
                $("#enable_handler").attr("disabled", true);
            } else if (rType == "clear_redis") {
                $.toast("Redis database has been cleared!");
                $("#clear_redis").attr("disabled", true);
            }
            $.getJSON("./api.php?action=" + rType, function (data) {
                if (data.result == true) {
                    window.location.reload();
                } else {
                    $.toast("An error occured while processing your request.");
                }
            }).fail(function () {
                $.toast("An error occured while processing your request.");
            });
        }
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
        $("#minute").keypress(function (e) {
            return checkRegex(e);
        });
        $("#hour").keypress(function (e) {
            return checkRegex(e);
        });
        $("#cache_thread_count").inputFilter(function (value) {
            return /^\d*$/.test(value);
        });
        $("form").attr('autocomplete', 'off');
    });
</script>
<!-- App js-->
</body>

</html>