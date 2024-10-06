<?php
include 'session.php';
include 'functions.php';

if (!$rPermissions["is_admin"]) {
    exit;
}

$rSettings = getSettings();
$_TITLE = 'Cache Settings';
if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ((isset($_POST["submit_settings"])) && (hasPermissions("adv", "settings"))) {
    $rCheck = array(false, false);
    $rCron = array('*', '*', '*', '*', '*');
    $rPattern = '/^[0-9\\/*,-]+$/';
    $rCron[0] = $_POST['minute'];
    preg_match($rPattern, $rCron[0], $rMatches);
    $rCheck[0] = 0 < count($rMatches);
    $rCron[1] = $_POST['hour'];
    preg_match($rPattern, $rCron[1], $rMatches);
    $rCheck[1] = 0 < count($rMatches);
    $rCronOutput = implode(' ', $rCron);

    if (isset($_POST['cache_changes'])) {
        $rCacheChanges = true;
    } else {
        $rCacheChanges = false;
    }

    if ($rCheck[0] && $rCheck[1]) {
        $db->query("UPDATE `crontab` SET `time` = '" . $rCronOutput . "' WHERE `filename` = 'cache_engine.php';");
        setSettings(["cache_thread_count" => $_POST['cache_thread_count'], "cache_changes" => $rCacheChanges]);

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
                        <h4 class="page-title"><?= $_["cache_cron_settings"] ?></h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <?php if (isset($_STATUS) && $_STATUS == 1): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["cache_cron_succefull_update"] ?>
                        </div>
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="tab-pane">
                                <div class="row">
                                    <div class="col-12">
                                        <?php if ($rSettings['enable_cache']): ?>
                                            <?php
                                            $cron = $db->query("SELECT `time` FROM `crontab` WHERE `filename` = 'cache_engine.php';");
                                            list($rMinute, $rHour, $rDayOfMonth, $rMonth, $rDayOfWeek) = explode(' ', $cron->fetch_assoc()['time']);
                                            $users = $db->query('SELECT `id` FROM `users`;');
                                            $rUserCount = $users->num_rows;
                                            $streams = $db->query('SELECT `id` FROM `streams`;');
                                            $rStreamCount = $streams->num_rows;
                                            $db->query('SELECT `id` FROM `streams_series`;');
                                            $series = $rSeriesCount = $series->num_rows;
                                            $rUserCountR = count(glob(USER_TMP_PATH . 'user_i_*'));
                                            $rStreamCountR = count(glob(STREAMS_TMP_PATH . 'stream_*'));
                                            $rSeriesCountR = count(glob(SERIES_TMP_PATH . 'series_*')) - 2;
                                            $rSeriesCountR = max($rSeriesCountR, 0);
                                            $rFreeCache = 100 - intval(disk_free_space(TMP_PATH) / disk_total_space(TMP_PATH) * 100);
                                            ?>

                                            <?php if ($rFreeCache >= 90): ?>
                                                <div class="alert alert-danger mb-4" role="alert">
                                                    <?= str_replace("{free_cache}",  $rFreeCache, $_["cache_cron_danger"]) ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!file_exists(CACHE_TMP_PATH . 'cache_complete')): ?>
                                                <div class="alert alert-warning mb-4" role="alert">
                                                    <?= $_["cache_cron_warning"] ?>
                                                </div>
                                            <?php endif; ?>

                                            <h5 class="card-title"><?= $_["cache_cron"] ?></h5>
                                            <p><?= str_replace("{time}", date("Y-m-d H:i:s", $rSettings['last_cache']), $_["cache_cron_description"]) ?></p>
                                            <div class="form-group row mb-4">
                                                <table class="table table-striped table-borderless mb-0" id="datatable-cache">
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center"><?= $_["cache_cron_minute"] ?></td>
                                                            <td style="width:250px;"><input type="text" class="form-control text-center" id="minute" name="minute" value="<?= $rMinute ?>"></td>
                                                            <td class="text-center"><?= $_["cache_cron_hour"] ?></td>
                                                            <td style="width:250px;"><input type="text" class="form-control text-center" id="hour" name="hour" value="<?= $rHour ?>"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center"><?= $_["cache_cron_thread_count"] ?></td>
                                                            <td><input type="text" class="form-control text-center" id="cache_thread_count" name="cache_thread_count" value="<?= intval($rSettings['cache_thread_count']) ?>"></td>
                                                            <!-- <td class="text-center">Update Changes Only</td>
                                                            <td>
                                                                <input name="cache_changes" id="cache_changes" type="checkbox" <?= $rSettings['cache_changes'] == 1 ? 'checked' : '' ?> data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                            </td> -->
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center"><?= $_["cache_cron_streams"] ?></td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-info btn-xs waves-effect waves-light"><?= number_format($rStreamCountR) ?> / <?= number_format($rStreamCount) ?></button>
                                                            <td class="text-center"><?= $_["cache_cron_users"] ?></td>
                                                            </td>

                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-info btn-xs waves-effect waves-light"><?= number_format($rUserCountR) ?> / <?= number_format($rUserCount) ?></button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center"><?= $_["cache_cron_series"] ?></td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-info btn-xs waves-effect waves-light"><?= number_format($rSeriesCountR) ?> / <?= number_format($rSeriesCount) ?></button>
                                                            </td>
                                                            <td class="text-center"><?= $_["cache_cron_time_taken"] ?></td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-info btn-xs waves-effect waves-light"><?= secondsToTime($rSettings['last_cache_taken']) ?></button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <h5 class="card-title">Cache is Disabled</h5>
                                            <p>You have chosen to disable Cache system. You can re-enable it by clicking the Enable Cache box below, however when doing so you would get best results restarting XUI on this server.</p>
                                        <?php endif; ?>

                                        <ul class="list-inline wizard mb-0" style="margin-top:30px;">
                                            <?php if ($rSettings['enable_cache']): ?>
                                                <li class="list-inline-item">
                                                    <button id="disable_cache" onClick="api('disable_cache')" class="btn btn-danger" type="button">Disable Cache</button>
                                                    <button id="regenerate_cache" onClick="api('regenerate_cache')" class="btn btn-info" type="button">Regenerate Cache</button>
                                                </li>
                                                <li class="list-inline-item float-right">
                                                    <input name="submit_settings" type="submit" class="btn btn-primary" value="Save Cron" />
                                                </li>
                                            <?php else: ?>
                                                <li class="list-inline-item">
                                                    <button id="enable_cache" onClick="api('enable_cache')" class="btn btn-success" type="button">Enable Cache</button>
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



<script>
    function api(rType) {
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
        }
        $.getJSON("./api.php?action=" + rType, function(data) {
            if (data.result == true) {
                window.location.reload();
            } else {
                $.toast("An error occured while processing your request.");
            }
        }).fail(function() {
            $.toast("An error occured while processing your request.");
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
        $('select').select2({
            width: '100%'
        });
        $("#minute").keypress(function(e) {
            return checkRegex(e);
        });
        $("#hour").keypress(function(e) {
            return checkRegex(e);
        });
        $("#cache_thread_count").inputFilter(function(value) {
            return /^\d*$/.test(value);
        });
        $("form").attr('autocomplete', 'off');
    });
</script>
<!-- App js-->
<script src="assets/js/app.min.js"></script>
</body>

</html>