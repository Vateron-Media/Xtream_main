<?php

include 'functions.php';
$rFirstRun = true;
$ipTV_db_admin->query('SELECT COUNT(`id`) AS `count` FROM `reg_users` LEFT JOIN `users_groups` ON `users_groups`.`group_id` = `reg_users`.`member_group_id` WHERE `users_groups`.`is_admin` = 1;');

print_r($rMigrating);

if ($ipTV_db_admin->get_row()['count']>0) {
    $rFirstRun = false;
    include 'session.php';

    if (!checkPermissions()) {
        goHome();
    }
}

$rMigrating = false;

if (file_exists(TMP_PATH . '.migration.status') && file_exists(TMP_PATH . '.migration.pid')) {
    $rPID = file_get_contents(TMP_PATH . '.migration.pid');

    if (file_exists('/proc/' . $rPID)) {
        $rMigrating = true;
    }
}


if (!isset(ipTV_lib::$request['update'])) {
    if (isset(ipTV_lib::$request['migrate'])) {
        $rMigrateOptions = array();

        foreach (ipTV_lib::$request as $rKey => $rValue) {
            if (substr($rKey, 0, 8) == 'migrate#') {
                list(, $rMigrateOptions[]) = explode('#', $rKey);
            }
        }

        if (count($rMigrateOptions) != 0) {
            if (!file_exists(TMP_PATH . '.migration.pid')) {
            } else {
                $rPID = intval(file_get_contents(TMP_PATH . '.migration.pid'));
                exec('kill -9 ' . $rPID);
            }

            file_put_contents(TMP_PATH . '.migration.options', json_encode($rMigrateOptions));
            unlink(TMP_PATH . '.migration.status');
            unlink(TMP_PATH . '.migration.pid');
            unlink(TMP_PATH . '.migration.log');
            shell_exec(PHP_BIN . ' ' . CLI_PATH . 'migrate.php > ' . TMP_PATH . '.migration.log 2>&1 &');
            $rMigrating = true;
        } else {
            header('Location: ./setup');

            exit();
        }
    } else {
        if (isset(ipTV_lib::$request['new_user']) && $rFirstRun) {
            if (strlen(ipTV_lib::$request['password']) < 8 || strlen(ipTV_lib::$request['username']) < 8) {
                ipTV_lib::$request['new'] = 1;
                $_STATUS = STATUS_FAILURE;
            } else {
                $rArray = verifyPostTable('users');
                $rArray['username'] = ipTV_lib::$request['username'];
                $rArray['password'] = cryptPassword(ipTV_lib::$request['password']);
                $rArray['email'] = ipTV_lib::$request['email'];
                $rArray['last_login'] = time();
                $rArray['date_registered'] = $rArray['last_login'];
                $rArray['member_group_id'] = 1;
                $rArray['ip'] = getIP();
                $rArray['last_login'] = time();
                $rPrepare = prepareArray($rArray);
                $rQuery = 'INSERT INTO `users`(' . $rPrepare['columns'] . ') VALUES(' . $rPrepare['placeholder'] . ');';

                if ($ipTV_db_admin->query($rQuery, ...$rPrepare['data'])) {
                    $_SESSION['hash'] = $ipTV_db_admin->last_insert_id();
                    $_SESSION['ip'] = getIP();
                    $_SESSION['code'] = getCurrentCode();
                    $_SESSION['verify'] = md5($rArray['username'] . '||' . $rArray['password']);
                    $ipTV_db_admin->query('UPDATE `servers` SET `server_ip` = ? WHERE `is_main` = 1 AND `server_type` = 0 LIMIT 1;', $_SERVER['SERVER_ADDR']);

                    if ($_SESSION['code'] == 'setup') {
                        header('Location: ./codes');

                        exit();
                    }

                    header('Location: ./dashboard');

                    exit();
                }

                ipTV_lib::$request['new'] = 1;
                $_STATUS = STATUS_FAILURE;
            }
        }
    }

    if (!$rMigrating) {
        $rMigrateConnection = false;
        $rMigrateXC = false;
        $odb = new Database($_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['hostname'], $_INFO['port'], empty($_INFO['pconnect']) ? false : true);

        if ($odb->connected) {
            $rMigrateConnection = true;
        }

        $odb->query("SHOW TABLES LIKE 'access_codes';");

        if (0 >= $odb->num_rows()) {
        } else {
            $rMigrateXC = true;
        }
        $rCount = array('reg_users' => array('Users & Resellers', 0), 'users' => array('Lines - Standard, MAG & Enigma2 Devices', 0), 'enigma2_devices' => array('Device Info - Engima2', 0), 'mag_devices' => array('Device Info - MAG', 0), 'user_output' => array('Line Output - HLS, MPEG-TS & RTMP', 0), 'streaming_servers' => array('Servers - Load Balancers', 0), 'series' => array('TV Series', 0), 'series_episodes' => array('TV Episodes', 0), 'streams' => array('Streams - Live, Radio, Created & VOD', 0), 'streams_sys' => array('Stream Servers', 0), 'streams_options' => array('Stream Options', 0), 'stream_categories' => array('Stream Categories', 0), 'bouquets' => array('Bouquets', 0), 'users_groups' => array('Member Groups', 0), 'packages' => array('Reseller Packages', 0), 'rtmp_ips' => array("RTMP IP's", 0), 'epg' => array('EPG Providers ', 0), 'blocked_ips' => array('Blocked IP Addresses', 0), 'blocked_user_agents' => array('Blocked User-Agents', 0), 'isp_addon' => array("Blocked ISP's", 0), 'tickets' => array('Tickets', 0), 'tickets_replies' => array('Ticket Replies', 0), 'transcoding_profiles' => array('Transcoding Profile', 0), 'watch_folders' => array('Watch Folders', 0), 'members' => array('Users & Resellers', 0), 'epg_sources' => array('EPG Providers', 0), 'blocked_isps' => array("Blocked ISP's", 0), 'categories' => array('Stream Categories', 0), 'groups' => array('Member Groups', 0), 'servers' => array('Servers - Load Balancers', 0), 'stream_servers' => array('Stream Servers', 0));

        foreach (array_keys($rCount) as $rTable) {
            try {
                $odb->query("SHOW TABLES LIKE '" . $rTable . "';");

                if (0 >= $odb->num_rows()) {
                } else {
                    $odb->query('SELECT COUNT(*) AS `count` FROM `' . $rTable . '`;');
                    $rCount[$rTable][1] = $odb->get_row()['count'];
                }
            } catch (Exception $e) {
            }
        }
        $rTotalCount = 0;

        foreach ($rCount as $rTable => $rItemCount) {
            $rTotalCount += $rItemCount[1];
        }
        ksort($rCount);
    }

    if ($rFirstRun || checkPermissions()) {
    } else {
        goHome();
    }

    $_TITLE = 'Database Migration';
    $_SETUP = true;
    include 'header.php';
    ?>
    <div class="wrapper boxed-layout" <?php if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
                                      } else {
                                            ?> style="display: none;" <?php
                                      } ?>>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Database Migration</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card-box">
                        <?php if ($rMigrating) { ?>
                            <div class="col-md-12 align-self-center">
                                <div class="text-center" style="padding-top: 15px;">
                                    <i class="mdi mdi-creation avatar-title font-24 text-info"></i><br />
                                    <h4 class="header-title text-info">Migrating...</h4>
                                    <textarea readonly
                                        style="padding: 15px; margin-top: 20px; background: #56c2d6; color: #fff; border: 0; width: 100%; height: 300px; scroll-y: auto;"
                                        id="migration_progress"></textarea>
                                    <ul class="list-inline wizard mb-4">
                                        <li class="float-right">
                                            <button disabled onClick="migrateServer();" class="btn btn-info"
                                                id="migrate_button">Try Again</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php } else {
                            if (isset(ipTV_lib::$request['new']) && $rFirstRun) { ?>
                                <form action="./setup" method="POST" data-parsley-validate="">
                                    <div class="row">
                                        <div class="col-12">
                                            <?php
                                            if (isset($_STATUS) && $_STATUS == STATUS_FAILURE) { ?>
                                                <div class="alert alert-danger mb-4" role="alert">
                                                    Please ensure your username and password are at least 8 characters long.
                                                </div>
                                            <?php } else { ?>
                                                <div class="alert alert-info mb-4" role="alert">
                                                    As you've decided not to migrate a previous database, you need to create an admin
                                                    account below.<br />Choose a strong username and password or you may be susceptible
                                                    to attacks.
                                                </div>
                                            <?php } ?>
                                            <div class="form-group row mb-4">
                                                <label class="col-md-4 col-form-label" for="username">Admin Username</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="username" name="username" value=""
                                                        required data-parsley-trigger="change">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <label class="col-md-4 col-form-label" for="password">Admin Password</label>
                                                <div class="col-md-8">
                                                    <input type="password" class="form-control" id="password" name="password"
                                                        value="" required data-parsley-trigger="change">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <label class="col-md-4 col-form-label" for="email">Email Address</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="email" name="email" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="list-inline wizard mb-4">
                                        <li class="list-inline-item float-right">
                                            <input name="new_user" type="submit" class="btn btn-primary" value="Create" />
                                        </li>
                                    </ul>
                                </form>
                            <?php } else { ?>
                                <form action="./setup" method="POST" data-parsley-validate="">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-secondary mb-4" role="alert">
                                                In order to migrate your database from a previous installation of Xtream UI, ZapX
                                                (original and NXT), StreamCreed or generic Xtream Codes v2 installation, you will
                                                need to restore your migration database to the <strong>xc_migrate</strong> database
                                                as XC_VM will have access to it.<br /><br />The script will then loop through all of
                                                your previously existing data and alter it to work with XC_VM. No logs will be
                                                migrated and some clean up may need to be done post-migration but this tool should
                                                help significantly in carrying over your data to your new panel.<br /><br />Once you're done, refresh the page.
                                            </div>
                                            <?php if (!$rMigrateConnection) { ?>
                                                <div class="alert alert-danger mb-4" role="alert">
                                                    A connection to the xc_migrate database could not be made. Please ensure the
                                                    database exists, if it does not, create it.
                                                </div>
                                            <?php }
                                            if ($rMigrateXC) { ?>
                                                <div class="alert alert-danger mb-4" role="alert">
                                                    The data restored to the xc_migrate database seems to contain tables attributed
                                                    with XC_VM. You cannot migrate using this data, you need to restore it via the
                                                    Databases section in the admin panel.
                                                </div>
                                            <?php }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                    if ($rMigrateConnection && !$rMigrateXC && $rTotalCount > 0) { ?>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-secondary mb-4" role="alert">
                                                    Below is a list of records found in the migration database. Please check this over
                                                    and click Migrate when you're ready to begin. You can also uncheck tables you don't
                                                    want to migrate.
                                                </div>
                                                <table class="table table-striped table-borderless mb-4">
                                                    <thead>
                                                        <tr>
                                                            <th>Description</th>
                                                            <th>Table Name</th>
                                                            <th class="text-center">Records</th>
                                                            <th class="text-center">Migrate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($rCount as $rTable => $rItem) {
                                                            if ($rItem[1] != 0) { ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($rItem[0]); ?></td>
                                                                    <td><?php echo htmlspecialchars($rTable); ?></td>
                                                                    <td class="text-center"><button type="button"
                                                                            class="btn btn-<?php echo (0 < $rItem[1] ? 'info' : 'secondary'); ?> btn-xs waves-effect waves-light"><?php echo $rItem[1]; ?></button>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <div class="checkbox checkbox-single checkbox-info">
                                                                            <input name="migrate#<?php echo htmlspecialchars($rTable); ?>"
                                                                                <?php echo (0 < $rItem[1] ? 'checked' : 'disabled'); ?>
                                                                                type="checkbox" class="activate">
                                                                            <label></label>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php }
                                                        } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <ul class="list-inline wizard">
                                                <?php if ($rFirstRun) { ?>
                                                    <li class="list-inline-item">
                                                        <a href="./setup?new"><button name="dont_migrate" class="btn btn-danger"
                                                                type="button">Don't Migrate</button></a>
                                                    </li>
                                                <?php }
                                                if ($rMigrateConnection && !$rMigrateXC && $rTotalCount > 0) { ?>
                                                    <li class="list-inline-item float-right">
                                                        <input name="migrate" type="submit" class="btn btn-primary" value="Migrate" />
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                </form>
                            <?php }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include 'footer.php';
} else {
    if (file_exists(TMP_PATH . '.migration.log')) {
        $rLog = file_get_contents(TMP_PATH . '.migration.log');
        $rStatus = intval(file_get_contents(TMP_PATH . '.migration.status'));

        if ($rStatus) {
        } else {
            $rStatus = 1;
        }

        if ($rStatus != 2) {
        } else {
            unlink(TMP_PATH . '.migration.options');
            unlink(TMP_PATH . '.migration.status');
            unlink(TMP_PATH . '.migration.pid');
            unlink(TMP_PATH . '.migration.log');
        }

        echo json_encode(array('result' => true, 'status' => $rStatus, 'data' =>
        $rLog));
    } else {
        echo json_encode(array('result' => false));
    }

    exit();
}
?>