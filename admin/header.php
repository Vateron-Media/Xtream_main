<?php if (count(get_included_files()) == 1) {
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= $rSettings['server_name'] ?: 'XC_VM'; ?> <?= isset($_TITLE) ? ' | ' . $_TITLE : ''; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/libs/jquery-nice-select/nice-select.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/switchery/switchery.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/jquery-toast/jquery.toast.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/treeview/style.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/nestable2/jquery.nestable.min.css" rel="stylesheet" />
    <link href="assets/libs/magnific-popup/magnific-popup.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/quill/quill.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/jbox/jBox.all.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/jquery-vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet" type="text/css" />
    <?php if (isset($_SETUP) || !$rSettings["dark_mode"]): ?>
        <link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/listings.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
    <?php else: ?>
        <link href="assets/css/bootstrap.dark.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.dark.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/listings.dark.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/custom.dark.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>
    <link href="assets/css/extra.css" rel="stylesheet" type="text/css" />
    <?php if (!$rModal): ?>
        <!-- No modal specific CSS needed -->
    <?php else: ?>
        <link href="assets/css/modal.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>
</head>

<body>
    <!-- Navigation Bar-->
    <header id="topnav">
        <div
            class="navbar-overlay bg-animate<?= (0 < strlen($rUserInfo['hue']) && in_array($rUserInfo['hue'], array_keys($rHues))) ? '-' . $rUserInfo['hue'] : ''; ?>">
        </div>
        <div class="navbar-custom">
            <div class="container-fluid">
                <div class="logo-box">
                    <a href="index" class="logo text-center">
                        <span class="logo-lg<?= (0 >= strlen($rUserInfo['hue'])) ? '' : ' whiteout'; ?>">
                            <img src="assets/images/logo.png" alt="" height="26">
                        </span>
                        <span class="logo-sm<?= (0 >= strlen($rUserInfo['hue'])) ? '' : ' whiteout'; ?>">
                            <img src="assets/images/logo.png" alt="" height="28">
                        </span>
                    </a>
                </div>

                <?php if (!isset($_SETUP)): ?>
                    <!-- Streams, Channels, Movies, Episodes & Radio Stations -->
                    <!-- Include similar structure for multiselect_streams, multiselect_series, etc. -->
                    <ul class="list-unstyled topnav-menu float-right mb-0 topnav-custom">
                        <li class="dropdown notification-list">
                            <a class="navbar-toggle nav-link">
                                <div class="lines text-white">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </a>
                        </li>
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect" data-toggle="dropdown" href="#"
                                role="button" aria-haspopup="false" aria-expanded="false">
                                <span class="pro-user-name text-white ml-1">
                                    <?= htmlspecialchars($rUserInfo['username']) ?> <i class="mdi mdi-chevron-down"></i>
                                </span>
                                <span class="pro-user-name-mob nav-link text-white waves-effect">
                                    <i class="fe-user noti-icon"></i>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right profile-dropdown">
                                <a href="edit_profile" class="dropdown-item notify-item">
                                    <span><?= $_["edit_profile"] ?></span>
                                </a>
                                <?php if (hasPermissions('adv', 'settings')): ?>
                                    <a href="./settings" class="dropdown-item notify-item">
                                        <span><?= $_["settings"] ?></span>
                                    </a>
                                <?php endif; ?>
                                <?php if (hasPermissions('adv', 'database')): ?>
                                    <a href="./backups" class="dropdown-item notify-item">
                                        <span><?= $_["backup_settings"] ?></span>
                                    </a>
                                    <a href="./cache" class="dropdown-item notify-item">
                                        <span><?= $_["cache_cron_redis_settings"] ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if (hasPermissions('adv', 'folder_watch_settings')): ?>
                                    <a href="settings_watch" class="dropdown-item notify-item">
                                        <span>Watch Settings</span>
                                    </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a href="logout" class="dropdown-item notify-item">
                                    <span>Logout</span>
                                </a>
                            </div>
                        </li>

                        <!-- User Profile, General Settings, etc. -->
                        <?php if ($rServerError && hasPermissions('adv', 'servers')): ?>
                            <li class="notification-list">
                                <a href="servers"
                                    class="nav-link right-bar-toggle waves-effect <?php echo $rUserInfo['theme'] == 1 ? 'text-white' : 'text-warning'; ?>">
                                    <i class="mdi mdi-wifi-strength-off noti-icon"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php if (!isset($_SETUP)): ?>
            <div class="topbar-menu">
                <div class="container-fluid">
                    <div id="navigation">
                        <ul class="navigation-menu">
                            <li>
                                <a href="index"><i class="fe-activity"></i><?= $_['dashboard']; ?></a>
                            </li>
                            <?php if (hasPermissions('adv', 'servers') || hasPermissions('adv', 'add_server') || hasPermissions('adv', 'process_monitor')): ?>
                                <li class="has-submenu">
                                    <a href="#"><i class="mdi mdi-server-network mdi-18px text-warning"></i>
                                        <?= $_['servers']; ?>
                                        <div class="arrow-down">
                                        </div>
                                    </a>
                                    <ul class="submenu">
                                        <?php if (hasPermissions('adv', 'add_server')): ?>
                                            <li>
                                                <a href="./server">
                                                    <i class="mdi mdi-upload-network-outline"></i> <?= $_["add_existing_lb"] ?></a>
                                            </li>
                                            <li><a href="./server_install">
                                                    <i class="mdi mdi-plus-network-outline"></i>
                                                    <?= $_["install_load_balancer"] ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'servers')): ?>
                                            <li>
                                                <a href="./servers">
                                                    <i class="mdi mdi-server-network"></i> <?= $_['manage_servers']; ?></a>
                                            </li>
                                            <li>
                                                <a href="./smonitor">
                                                    <i class="mdi mdi-chart-line-variant"></i> <?= $_['server_monitor']; ?></a>
                                            </li>
                                            <li>
                                                <a href="./process_monitor?server=<?= $_INFO["server_id"] ?>">
                                                    <i class="mdi mdi-chart-line-variant"></i> <?= $_['process_monitor']; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (hasPermissions('adv', 'add_user') || hasPermissions('adv', 'users') || hasPermissions('adv', 'add_mag') || hasPermissions('adv', 'manage_mag') || hasPermissions('adv', 'add_e2') || hasPermissions('adv', 'manage_e2') || hasPermissions('adv', 'mass_edit_users') || hasPermissions('adv', 'mass_edit_mags') || hasPermissions('adv', 'mass_edit_enigmas')): ?>
                                <li class="has-submenu">
                                    <a href="#"> <i class="mdi mdi-monitor mdi-18px text-pink"></i> <?= $_["users"] ?>
                                        <div class="arrow-down"></div>
                                    </a>
                                    <ul class="submenu">
                                        <?php if (hasPermissions('adv', 'add_user') || hasPermissions('adv', 'users') || hasPermissions('adv', 'mass_edit_users')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-account-multiple"></i> <?= $_["users"] ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_user')): ?>
                                                        <li><a href="./user"><i class="mdi mdi-account-plus-outline "></i>
                                                                <?= $_["add_user"] ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'users')): ?>
                                                        <li><a href="./users"><i class="mdi mdi-account-multiple-outline "></i>
                                                                <?= $_["manage_users"] ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mass_edit_users')): ?>
                                                        <li><a href="./user_mass"><i class="mdi mdi-account-edit"></i>
                                                                <?= $_["mass_edit_users"] ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'add_mag') || hasPermissions('adv', 'manage_mag') || hasPermissions('adv', 'mass_edit_mags')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-cellphone-link"></i> <?= $_['mag_devices']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_mag')): ?>
                                                        <li><a href="./user?mag"><i class="mdi mdi-account-plus-outline"> </i>
                                                                <?= $_['add_mag']; ?></a></li>
                                                        <!--<li><a href="./mag"><?= $_["link_mag"] ?></a></li>-->
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'manage_mag')): ?>
                                                        <li><a href="./mags"><i class="mdi mdi-account-multiple-outline "> </i>
                                                                <?= $_['manage_mag_devices']; ?></a></li>
                                                    <?php endif; ?>

                                                    <?php if (hasPermissions('adv', 'add_mag')): ?>
                                                        <li><a href="./mag"><i class="mdi mdi-account-switch"> </i>
                                                                <?= $_['link_mag']; ?></a></li>
                                                    <?php endif; ?>
                                                    <!-- <?php if (hasPermissions('adv', 'mass_edit_mags')): ?>
                                                        <li><a href="mag_mass">Mass Edit Mags</a></li>
                                                    <?php endif; ?> -->
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'add_e2') || hasPermissions('adv', 'manage_e2') || hasPermissions('adv', 'mass_edit_enigmas')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-cellphone-link"></i> <?= $_['enigma_devices']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_e2')): ?>
                                                        <li><a href="./user?e2"><i class="mdi mdi-account-plus-outline"></i>
                                                                <?= $_['add_enigma']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'manage_e2')): ?>
                                                        <li><a href="./enigmas"><i class="mdi mdi-account-multiple-outline"></i>
                                                                <?= $_['manage_enigma_devices']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'add_e2')): ?>
                                                        <li><a href="./enigma"><i class="mdi mdi-account-switch"></i>
                                                                <?= $_['link_enigma']; ?></a></li>
                                                    <?php endif; ?>
                                                    <!-- <?php if (hasPermissions('adv', 'mass_edit_enigmas')): ?>
                                                        <li><a href="enigma_mass">Mass Edit Enigmas</a></li>
                                                    <?php endif; ?> -->
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'add_reguser') || hasPermissions('adv', 'mng_regusers') || hasPermissions('adv', 'mass_edit_users')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-account-network-outline"></i> Reseller <div
                                                        class="arrow-down"></div></a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_reguser')): ?>
                                                        <li><a href="./reg_user"><i class="mdi mdi-account-multiple-plus-outline"></i>
                                                                <?= $_["add_registered_user"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mng_regusers')): ?>
                                                        <li><a href="./reg_users"><i class="mdi mdi-account-multiple-outline"></i>
                                                                <?= $_["manage_registered_users"] ?></a></li>
                                                    <?php endif; ?>
                                                    <!-- <?php if (hasPermissions('adv', 'mass_edit_users')): ?>
                                                        <li><a href="./reg_user_mass">Mass Edit Resellers</a></li>
                                                    <?php endif; ?> -->
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (hasPermissions('adv', 'add_stream') || hasPermissions('adv', 'create_channel') || hasPermissions('adv', 'import_streams') || hasPermissions('adv', 'mass_edit_streams') || hasPermissions('adv', 'streams') || hasPermissions('adv', 'add_movie') || hasPermissions('adv', 'import_movies') || hasPermissions('adv', 'movies') || hasPermissions('adv', 'series') || hasPermissions('adv', 'episodes') || hasPermissions('adv', 'add_series') || hasPermissions('adv', 'radio') || hasPermissions('adv', 'add_radio') || hasPermissions('adv', 'mass_sedits_vod') || hasPermissions('adv', 'mass_sedits') || hasPermissions('adv', 'mass_edit_radio')): ?>
                                <li class="has-submenu">
                                    <a href="#"> <i class="fas fa-play text-success"></i><?= $_['content']; ?>
                                        <div class="arrow-down"></div>
                                    </a>
                                    <ul class="submenu">
                                        <?php if (hasPermissions('adv', 'add_stream') || hasPermissions('adv', 'import_streams') || hasPermissions('adv', 'mass_edit_streams') || hasPermissions('adv', 'streams')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-movie"></i> <?= $_['streams']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_stream')): ?>
                                                        <li><a href="./stream"><i class="mdi mdi-plus"></i> <?= $_['add_stream']; ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'import_streams')): ?>
                                                        <li><a href="./stream?import"><i class="mdi mdi-file-plus"></i>
                                                                <?= $_["import_streams"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'streams')): ?>
                                                        <li><a href="./streams"><i class="mdi mdi-play-circle-outline"></i>
                                                                <?= $_['manage_streams']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mass_edit_streams')): ?>
                                                        <li><a href="./stream_mass"><i class="mdi mdi-border-color"></i>
                                                                <?= $_["mass_edit_streams"] ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'create_channel')): ?>
                                            <li>
                                                <a href="./created_channel">
                                                    <i class="mdi mdi-television-classic"></i> <?= $_["create_channel"] ?></a>

                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'add_movie') || hasPermissions('adv', 'import_movies') || hasPermissions('adv', 'movies') || hasPermissions('adv', 'mass_sedits_vod')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-video-outline"></i> <?= $_['movies']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_movie')): ?>
                                                        <li><a href="./movie"><i class="mdi mdi-plus"></i>
                                                                <?= $_['add_movie']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'import_movies')): ?>
                                                        <li><a href="./movie?import"><i class="mdi mdi-file-plus"></i>
                                                                <?= $_["import_movies"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'movies')): ?>
                                                        <li><a href="./movies"><i class="mdi mdi-movie"></i>
                                                                <?= $_['manage_movies']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mass_sedits_vod')): ?>
                                                        <li><a href="./movie_mass"><i class="mdi mdi-border-color"></i>
                                                                <?= $_["mass_edit_movies"] ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'add_series') || hasPermissions('adv', 'series') || hasPermissions('adv', 'episodes') || hasPermissions('adv', 'mass_sedits')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-video-outline"></i> <?= $_['series']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_series')): ?>
                                                        <li><a href="./serie"><i class="mdi mdi-plus"></i> <?= $_['add_series']; ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'series')): ?>
                                                        <li><a href="./series"><i class="mdi mdi-youtube-tv"></i>
                                                                <?= $_['manage_series']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'episodes')): ?>
                                                        <li><a href="./episodes"><i class="mdi mdi-youtube-tv"></i>
                                                                <?= $_['manage_episodes']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mass_sedits')): ?>
                                                        <li><a href="./series_mass"><i class="mdi mdi-border-color"></i>
                                                                <?= $_["mass_edit_series"] ?></a></li>
                                                        <li><a href="./episodes_mass"><i class="mdi mdi-border-color"></i>
                                                                <?= $_["mass_edit_episodes"] ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'add_radio') || hasPermissions('adv', 'radio') || hasPermissions('adv', 'mass_edit_radio')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-radio"></i> <?= $_['stations']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'add_radio')): ?>
                                                        <li><a href="./radio"><i class="mdi mdi-plus"></i> <?= $_['add_station']; ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'radio')): ?>
                                                        <li><a href="./radios"><i class="mdi mdi-radio"></i>
                                                                <?= $_['manage_stations']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mass_edit_radio')): ?>
                                                        <li><a href="./radio_mass"><i class="mdi mdi-border-color"></i>
                                                                <?= $_["mass_edit_stations"] ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (hasPermissions('adv', 'add_bouquet') || hasPermissions('adv', 'bouquets') || hasPermissions('adv', 'edit_bouquet')): ?>
                                <li class="has-submenu">
                                    <a href="#"> <i class="fas fa-spa text-purple"></i><?= $_['bouquets']; ?>
                                        <div class="arrow-down"></div>
                                    </a>
                                    <ul class="submenu">
                                        <?php if (hasPermissions("adv", "add_bouquet")) { ?>
                                            <li><a href="./bouquet"><i class="mdi mdi-plus"></i>
                                                    <?= $_["add_bouquet"] ?></a></li>
                                        <?php }
                                        if (hasPermissions("adv", "bouquets")) { ?>
                                            <li><a href="./bouquets"><i class="mdi mdi-flower-tulip-outline"></i>
                                                    <?= $_["manage_bouquets"] ?></a></li>
                                        <?php }
                                        if (hasPermissions("adv", "edit_bouquet")) { ?>
                                            <li><a href="./bouquet_sort"><i class="mdi mdi-reorder-horizontal"></i>
                                                    <?= $_["order_bouquets"] ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (hasPermissions('adv', 'mng_packages') || hasPermissions('adv', 'categories') || hasPermissions('adv', 'mng_groups') || hasPermissions('adv', 'epg') || hasPermissions('adv', 'tprofiles') || hasPermissions('adv', 'folder_watch') || hasPermissions('adv', 'block_ips') || hasPermissions('adv', 'block_isps') || hasPermissions('adv', 'block_uas') || hasPermissions('adv', 'rtmp') || hasPermissions('adv', 'security_center') || hasPermissions('adv', 'channel_order') || hasPermissions('adv', 'fingerprint') || hasPermissions('adv', 'mass_delete') || hasPermissions('adv', 'stream_tools') || hasPermissions('adv', 'subresellers') || hasPermissions('adv', 'login_flood') || hasPermissions('adv', 'connection_logs') || hasPermissions('adv', 'client_request_log') || hasPermissions('adv', 'credits_log') || hasPermissions('adv', 'live_connections') || hasPermissions('adv', 'manage_events') || hasPermissions('adv', 'panel_logs') || hasPermissions('adv', 'reg_userlog') || hasPermissions('adv', 'stream_errors') || hasPermissions('adv', 'system_logs') || hasPermissions('adv', 'manage_tickets')): ?>
                                <li class="has-submenu">
                                    <a href="#"> <i class="fas fa-wrench text-danger"></i><?= $_['management']; ?>
                                        <div class="arrow-down">
                                        </div>
                                    </a>
                                    <ul class="submenu">
                                        <?php if (hasPermissions('adv', 'mng_packages') || hasPermissions('adv', 'categories') || hasPermissions('adv', 'mng_groups') || hasPermissions('adv', 'epg') || hasPermissions('adv', 'tprofiles') || hasPermissions('adv', 'folder_watch')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-progress-wrench"></i>Service Setup <div
                                                        class="arrow-down"></div></a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'mng_packages')): ?>
                                                        <li><a href="./packages"><i class="mdi mdi-package"></i>
                                                                <?= $_["packages"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'categories')): ?>
                                                        <li><a href="./stream_categories"><i class="mdi mdi-folder-open-outline"></i>
                                                                <?= $_["categories"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mng_groups')): ?>
                                                        <li><a href="./groups"><i class="mdi mdi-account-multiple-outline"></i>
                                                                <?= $_["groups"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'epg')): ?>
                                                        <li><a href="./epgs"><i class="mdi mdi-play-protected-content"></i>
                                                                <?= $_["epgs"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'tprofiles')): ?>
                                                        <li><a href="./profiles"><i class="mdi mdi-find-replace"></i>
                                                                <?= $_["transcode_profiles"] ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'folder_watch')): ?>
                                                        <li><a href="./watch"><i class="mdi mdi-eye-outline"></i>
                                                                <?= $_["folder_watch"] ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'block_ips') || hasPermissions('adv', 'block_isps') || hasPermissions('adv', 'block_uas') || hasPermissions('adv', 'rtmp') || hasPermissions('adv', 'security_center')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi mdi-security"></i>Security <div class="arrow-down">
                                                    </div></a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'block_ips')): ?>
                                                        <li><a href="./ips"><i class="mdi mdi-close-octagon-outline"></i>
                                                                <?= $_['blocked_ips']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'block_isps')): ?>
                                                        <li><a href="./isps"><i class="mdi mdi-close-network"></i>
                                                                <?= $_['blocked_isps']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'block_uas')): ?>
                                                        <li><a href="./useragents"><i class="mdi mdi-close-box-outline"></i>
                                                                <?= $_['blocked_uas']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'rtmp')): ?>
                                                        <li><a href="./rtmp_ips"><i class="mdi mdi-close"></i>
                                                                <?= $_['rtmp_ips']; ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'security_center')): ?>
                                                        <li><a href="./security_center"><i class="mdi mdi-security"></i> Security
                                                                Center</a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'channel_order') || hasPermissions('adv', 'fingerprint') || hasPermissions('adv', 'mass_delete') || hasPermissions('adv', 'stream_tools') || hasPermissions('adv', 'subresellers') || hasPermissions('adv', 'login_flood')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-toolbox-outline"></i> <?= $_['tools']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu">
                                                    <?php if (hasPermissions('adv', 'channel_order') && !$rMobile): ?>
                                                        <li><a href="./channel_order"><i
                                                                    class="mdi mdi-reorder-horizontal mdi-18px"></i>
                                                                <?= $_['channel_order']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'fingerprint')): ?>
                                                        <li><a href="./fingerprint"><i class="mdi mdi-fingerprint"></i>
                                                                <?= $_['fingerprint']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'mass_delete')): ?>
                                                        <li><a href="./mass_delete"><i class="mdi mdi-delete-outline"></i>
                                                                <?= $_['mass_delete']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'stream_tools')): ?>
                                                        <li><a href="./stream_tools"><i
                                                                    class="mdi mdi-wrench-outline mdi-rotate-90"></i>
                                                                <?= $_['stream_tools']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'subresellers')): ?>
                                                        <li><a href="./subresellers"><i
                                                                    class="mdi mdi mdi-account-multiple-outline"></i>
                                                                <?= $_['subresellers']; ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (hasPermissions('adv', 'login_flood')): ?>
                                                        <li><a href="./flood_login"><i class="mdi mdi-account-alert"></i>
                                                                Logins Flood</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'connection_logs') || hasPermissions('adv', 'client_request_log') || hasPermissions('adv', 'credits_log') || hasPermissions('adv', 'live_connections') || hasPermissions('adv', 'manage_events') || hasPermissions('adv', 'panel_logs') || hasPermissions('adv', 'reg_userlog') || hasPermissions('adv', 'stream_errors') || hasPermissions('adv', 'system_logs')): ?>
                                            <li class="has-submenu">
                                                <a href="#"><i class="mdi mdi-information-outline"></i> <?= $_['logs']; ?>
                                                    <div class="arrow-down"></div>
                                                </a>
                                                <ul class="submenu megamenu">
                                                    <li>
                                                        <ul>
                                                            <?php
                                                            $logs = [
                                                                ['url' => 'user_activity', 'icon' => 'mdi mdi-file-document-outline', 'title' => $_['activity_logs'], 'permissions' => ['connection_logs']],
                                                                ['url' => 'client_logs', 'icon' => 'mdi mdi-account-search', 'title' => $_['client_logs'], 'permissions' => ['client_request_log']],
                                                                ['url' => 'credit_logs', 'icon' => 'mdi mdi-credit-card-multiple', 'title' => $_['credit_logs'], 'permissions' => ['credits_log']],
                                                                ['url' => 'live_connections', 'icon' => 'mdi mdi-account-network-outline', 'title' => $_['live_connections'], 'permissions' => ['live_connections']],
                                                                ['url' => 'mag_events', 'icon' => 'mdi mdi-message-outline', 'title' => $_['mag_event_logs'], 'permissions' => ['manage_events']],
                                                                ['url' => 'panel_logs', 'icon' => 'mdi mdi-file-document-outline', 'title' => 'Panel Errors', 'permissions' => ['panel_logs']],
                                                                ['url' => 'reg_user_logs', 'icon' => 'mdi mdi-account-details', 'title' => $_['reseller_logs'], 'permissions' => ['reg_userlog']],
                                                                ['url' => 'stream_logs', 'icon' => 'mdi mdi-file-document-outline', 'title' => $_['stream_logs'], 'permissions' => ['stream_errors']],
                                                                ['url' => 'system_logs', 'icon' => 'mdi mdi-file-document-outline', 'title' => $_["system_logs"], 'permissions' => ['system_logs']],
                                                                ['url' => 'user_ips', 'icon' => 'mdi mdi-ip', 'title' => $_['line_ip_usage'], 'permissions' => ['connection_logs']]
                                                            ];
                                                            $filteredLogs = array_filter($logs, function ($log) {
                                                                return array_reduce($log['permissions'], function ($carry, $permission) {
                                                                    return $carry || hasPermissions('adv', $permission);
                                                                }, false);
                                                            });
                                                            $splitIndex = count($filteredLogs) > 8 ? ceil(count($filteredLogs) / 2) : null;
                                                            $i = 0;
                                                            foreach ($filteredLogs as $log) {
                                                                if ($splitIndex && $i == $splitIndex) {
                                                                    echo '</ul></li><li><ul>';
                                                                }
                                                                echo '<li><a href="./' . $log['url'] . '"><i class="' . $log['icon'] . '"></i> ' . $log['title'] . '</a></li>';
                                                                $i++;
                                                            }
                                                            ?>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (hasPermissions('adv', 'manage_tickets')): ?>
                                            <li><a href="./tickets"><i class="mdi mdi-email-outline"></i> <?= $_['tickets']; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (hasPermissions('adv', 'manage_tickets')): ?>
                                <li class="has-submenu">
                                    <a href="#"> <i class="mdi mdi-apps mdi-18px text-success"></i>Apps Iptv <div
                                            class="arrow-down"></div></a>
                                    <ul class="submenu">
                                        <li><a href="./duplexplay"><i class="mdi mdi-account-star"></i> DUPLEX IPTV</a></li>
                                        <li><a href="./netiptv"><i class="mdi mdi-account-star"></i> NET IPTV</a></li>
                                        <li><a href="./siptv"><i class="mdi mdi-account-star"></i> SMART IPTV</a></li>
                                        <li><a href="./siptvextreme"><i class="mdi mdi-account-star"></i> IPTV EXTREME</a></li>
                                        <li><a href="./nanomid"><i class="mdi mdi-account-star"></i> NANOMID</a></li>
                                        <li><a href="./ss-iptv"><i class="mdi mdi-account-star"></i> SS IPTV</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </header>
    <!-- End Navigation Bar-->