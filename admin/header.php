<?php if (count(get_included_files()) == 1) { exit; } ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?=htmlspecialchars($rSettings["server_name"])?></title>
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
        <link href="assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet" type="text/css" />
		<link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
		<?php if (!$rAdminSettings["dark_mode"]) { ?>
        <link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.css" rel="stylesheet" type="text/css" />
		<?php } else { ?>
		<link href="assets/css/bootstrap.dark.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.dark.css" rel="stylesheet" type="text/css" />
		<?php } ?>
    </head>
    <body>
        <!-- Navigation Bar-->
        <header id="topnav">
            <!-- Topbar Start -->
            <div class="navbar-custom">
                <div class="container-fluid">
                    <ul class="list-unstyled topnav-menu float-right mb-0">
                        <li class="dropdown notification-list">
                            <!-- Mobile menu toggle-->
                            <a class="navbar-toggle nav-link">
                                <div class="lines text-white">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </a>
                            <!-- End mobile menu toggle-->
                        </li>
						<li class="notification-list username">
                            <a class="nav-link text-white waves-effect" href="./edit_profile.php" role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?=$_["edit_profile"]?>">
                                <th class="text-center"><span class="mdi mdi-account-circle-outline mdi-18px"> <?=$_["welcome"]?>:&nbsp;</th><td class="text-center"><b><?=$rUserInfo["username"]?></b></td>
                            </a>
                        </li>
						<?php if (($rServerError) && ($rPermissions["is_admin"]) && (hasPermissions("adv", "servers"))) { ?>
                        <li class="notification-list">
                            <a href="./servers.php" class="nav-link right-bar-toggle waves-effect text-warning">
                                <i class="mdi mdi-wifi-strength-off noti-icon"></i>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($rPermissions["is_reseller"]) { ?>
                        <li class="notification-list">
                            <a class="nav-link text-white waves-effect" href="#" role="button">
                                <i class="fe-dollar-sign noti-icon text-warning"></i>
                                <?php if (floor($rUserInfo["credits"]) == $rUserInfo["credits"]) {
                                    echo number_format($rUserInfo["credits"], 0);
                                } else {
                                    echo number_format($rUserInfo["credits"], 2);
                                } ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($rPermissions["is_admin"]) {
						if ((hasPermissions("adv", "settings")) OR (hasPermissions("adv", "database")) OR (hasPermissions("adv", "block_ips")) OR (hasPermissions("adv", "block_isps")) OR (hasPermissions("adv", "block_uas")) OR (hasPermissions("adv", "categories")) OR (hasPermissions("adv", "channel_order")) OR (hasPermissions("adv", "epg")) OR (hasPermissions("adv", "folder_watch")) OR (hasPermissions("adv", "mng_groups")) OR (hasPermissions("adv", "mass_delete")) OR (hasPermissions("adv", "mng_packages")) OR (hasPermissions("adv", "process_monitor")) OR (hasPermissions("adv", "rtmp")) OR (hasPermissions("adv", "subresellers")) OR (hasPermissions("adv", "tprofiles"))) { ?>
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect text-white" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <i class="fe-settings noti-icon text-warning"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right profile-dropdown">
								<?php if ((hasPermissions("adv", "settings")) OR (hasPermissions("adv", "database"))) { ?>
                                <a href="./settings.php" class="dropdown-item notify-item"><span class="mdi mdi-wrench-outline mdi-rotate-90 mdi-18px"> <?=$_["settings"]?></span></a>
								<?php }
								if (hasPermissions("adv", "mng_packages")) { ?>
                                <a href="./packages.php" class="dropdown-item notify-item"><span class="mdi mdi-package mdi-18px"> <?=$_["packages"]?></span></a>
								<?php }
								if (hasPermissions("adv", "categories")) { ?>
                                <a href="./stream_categories.php" class="dropdown-item notify-item"><span class="mdi mdi-folder-open-outline mdi-18px"> <?=$_["categories"]?></span></a>
								<?php }
								if (hasPermissions("adv", "mng_groups")) { ?>
                                <a href="./groups.php" class="dropdown-item notify-item"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["groups"]?></span></a>
								<?php }
								if (hasPermissions("adv", "epg")) { ?>
                                <a href="./epgs.php" class="dropdown-item notify-item"><span class="mdi mdi-play-protected-content mdi-18px"> <?=$_["epgs"]?></span></a>
								<?php }
								if (hasPermissions("adv", "channel_order")) { ?>
                                <a href="./channel_order.php" class="dropdown-item notify-item"><span class="mdi mdi-reorder-horizontal mdi-18px"> <?=$_["channel_order"]?></span></a>
								<?php }
								if (hasPermissions("adv", "folder_watch")) { ?>
                                <a href="./watch.php" class="dropdown-item notify-item"><span class="mdi mdi-eye-outline mdi-18px"> <?=$_["folder_watch"]?></span></a>
								<?php }
								if (hasPermissions("adv", "subresellers")) { ?>
                                <a href="./subresellers.php" class="dropdown-item notify-item"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["subresellers"]?></span></a>
								<?php }
								if (hasPermissions("adv", "login_flood")) { ?>
                                <a href="./flood_login.php" class="dropdown-item notify-item"><span class="mdi mdi-account-alert mdi-18px"> Logins Flood</span></a>
                                <?php }
								if (hasPermissions("adv", "security_center")) { ?>
                                <a href="./security_center.php" class="dropdown-item notify-item"><span class="mdi mdi-security mdi-18px"> Security Center</span></a>
								<?php }
								if (hasPermissions("adv", "block_ips")) { ?>
                                <a href="./ips.php" class="dropdown-item notify-item"><span class="mdi mdi-close-octagon-outline mdi-18px"> <?=$_["blocked_ips"]?></span></a>
								<?php }
                                if (hasPermissions("adv", "block_isps")) { ?>
                                <a href="./isps.php" class="dropdown-item notify-item"><span class="mdi mdi-close-network mdi-18px"> <?=$_["blocked_isps"]?></span></a>
                                <?php }
								if (hasPermissions("adv", "rtmp")) { ?>
                                <a href="./rtmp_ips.php" class="dropdown-item notify-item"><span class="mdi mdi-close mdi-18px"> <?=$_["rtmp_ips"]?></span></a>
								<?php }
								if (hasPermissions("adv", "block_uas")) { ?>
                                <a href="./useragents.php" class="dropdown-item notify-item"><span class="mdi mdi-close-box-outline mdi-18px"> <?=$_["blocked_uas"]?></span></a>
								<?php }
								if (hasPermissions("adv", "process_monitor")) { ?>
                                <a href="./process_monitor.php?server=<?=$_INFO["server_id"]?>" class="dropdown-item notify-item"><span class="mdi mdi-chart-line mdi-18px"> <?=$_["process_monitor"]?></span></a>
								<?php }
								if (hasPermissions("adv", "tprofiles")) { ?>
                                <a href="./profiles.php" class="dropdown-item notify-item"><span class="mdi mdi-find-replace mdi-18px"> <?=$_["transcode_profiles"]?></span></a>
								<?php } ?>
                            </div>
                        </li>
                        <?php }
						} ?>
                        <li class="notification-list">
                            <a href="./logout.php" class="nav-link right-bar-toggle waves-effect text-white">
                                <i class="fe-power noti-icon text-danger"></i>
                            </a>
                        </li>
                    </ul>
                    <!-- LOGO -->
                    <div class="logo-box">
                        <a href="<?php if ($rPermissions["is_admin"]) { ?>dashboard.php<?php } else { ?>reseller.php<?php } ?>" class="logo text-center">
                            <span class="logo-lg">
                                <img src="<?=$rSettings["logo_url"]?>" alt="" height="26">
                            </span>
                            <span class="logo-sm">
                                <img src="<?=$rSettings["logo_url"]?>" alt="" height="28">
                            </span>
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </div> 
            </div>
            <!-- end Topbar -->
            <div class="topbar-menu">
                <div class="container-fluid">
                    <div id="navigation">
                        <!-- Navigation Menu-->
                        <ul class="navigation-menu">
                            <li>
                                <a href="./<?php if ($rPermissions["is_admin"]) { ?>dashboard.php<?php } else { ?>reseller.php<?php } ?>"><i class="mdi mdi-view-dashboard-outline mdi-18px text-purple"></i><?=$_["dashboard"]?></a>
                            </li>
                            <?php if (($rPermissions["is_reseller"]) && ($rPermissions["reseller_client_connection_logs"])) { ?>
                            <li class="has-submenu">
                                <a href="#"><i class="mdi mdi-information-outline mdi-18px text-danger"></i><?=$_["logs"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <li><a href="./live_connections.php"><span class="mdi mdi-account-network-outline mdi-18px"></span> <?=$_["live_connections"]?></a></li><p>
                                    <li><a href="./user_activity.php"><span class="mdi mdi-file-document-outline mdi-18px"> <?=$_["activity_logs"]?></a></li>
                                </ul>
                            </li>
                            <?php }
                            if ($rPermissions["is_admin"]) {
							if ((hasPermissions("adv", "servers")) OR (hasPermissions("adv", "add_server")) OR (hasPermissions("adv", "live_connections")) OR (hasPermissions("adv", "connection_logs"))) { ?>
                            <li class="has-submenu">
                                <a href="#"><i class="mdi mdi-server-network mdi-18px text-warning"></i><?=$_["servers"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <?php if (hasPermissions("adv", "add_server")) { ?>
                                    <li><a href="./server.php"><span class="mdi mdi-upload-network-outline mdi-18px"></span> <?=$_["add_existing_lb"]?></a></li><p>
                                    <li><a href="./install_server.php"><span class="mdi mdi-plus-network-outline mdi-18px"> <?=$_["install_load_balancer"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "servers")) { ?>
                                    <li><a href="./servers.php"><span class="mdi mdi-server-network mdi-18px"></span> <?=$_["manage_servers"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "servers")) { ?>
                                    <li><a href="./smonitor.php"><span class="mdi mdi-chart-line-variant mdi-18px"></span> <?=$_["server_monitor"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							if ((hasPermissions("adv", "add_user")) OR (hasPermissions("adv", "users")) OR (hasPermissions("adv", "mass_edit_users")) OR (hasPermissions("adv", "mng_regusers")) OR (hasPermissions("adv", "add_reguser")) OR (hasPermissions("adv", "credits_log")) OR (hasPermissions("adv", "client_request_log")) OR (hasPermissions("adv", "reg_userlog"))) { ?>
							<li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-account-multiple-outline mdi-18px text-primary"></i><?=$_["reg_users"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <?php if (hasPermissions("adv", "add_reguser")) { ?>
                                    <li><a href="./reg_user.php"><span class="mdi mdi-account-multiple-plus-outline mdi-18px"> <?=$_["add_registered_user"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "mng_regusers")) { ?>
                                    <li><a href="./reg_users.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_registered_users"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
                            <?php }
							if ((hasPermissions("adv", "add_user")) OR (hasPermissions("adv", "users")) OR (hasPermissions("adv", "mass_edit_users")) OR (hasPermissions("adv", "mng_regusers")) OR (hasPermissions("adv", "add_reguser")) OR (hasPermissions("adv", "credits_log")) OR (hasPermissions("adv", "client_request_log")) OR (hasPermissions("adv", "reg_userlog")) OR (hasPermissions("adv", "add_mag")) OR (hasPermissions("adv", "manage_mag")) OR (hasPermissions("adv", "add_e2")) OR (hasPermissions("adv", "manage_e2")) OR (hasPermissions("adv", "manage_events"))) { ?>
							<li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-account-outline mdi-18px text-pink"></i><?=$_["users"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <?php if (hasPermissions("adv", "add_user")) { ?>
                                    <li><a href="./user.php"><span class="mdi mdi-account-plus-outline mdi-18px"></span> <?=$_["add_user"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "users")) { ?>
                                    <li><a href="./users.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_users"]?></a></li><p>
									<?php }
									if ((hasPermissions("adv", "add_mag")) OR (hasPermissions("adv", "manage_mag"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									 if (hasPermissions("adv", "add_mag")) { ?>
                                    <li><a href="./user.php?mag"><span class="mdi mdi-account-plus-outline mdi-18px"></span> <?=$_["add_mag"]?></a></li><p>
                                    <!--<li><a href="./mag.php"><?=$_["link_mag"]?></a></li>-->
									<?php }
									if (hasPermissions("adv", "manage_mag")) { ?>
                                    <li><a href="./mags.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_mag_devices"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "add_mag")) { ?>
									<li><a href="./mag.php"><span class="mdi mdi-account-switch mdi-18px"> <?=$_["link_mag"]?></a></li><p>
									<?php }
                                    if ((hasPermissions("adv", "add_e2")) OR (hasPermissions("adv", "manage_e2"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									if (hasPermissions("adv", "add_e2")) { ?>
                                    <li><a href="./user.php?e2"><span class="mdi mdi-account-plus-outline mdi-18px"></span> <?=$_["add_enigma"]?></a></li><p>
                                    <!--<li><a href="./enigma.php"><?=$_["link_enigma"]?></a></li>-->
									<?php }
									if (hasPermissions("adv", "manage_e2")) { ?>
                                    <li><a href="./enigmas.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_enigma_devices"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "add_e2")) { ?>
                                    <li><a href="./enigma.php"><span class="mdi mdi-account-switch mdi-18px"> <?=$_["link_enigma"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							} else { ?>
							<li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-account-outline mdi-18px text-pink"></i><?=$_["users"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <?php if ((!$rAdminSettings["disable_trial"]) && ($rPermissions["total_allowed_gen_trials"] > 0) && ($rUserInfo["credits"] >= $rPermissions["minimum_trial_credits"])) { ?>
                                    <li><a href="./user_reseller.php?trial"><span class="mdi mdi-account-plus-outline mdi-18px"></span> <?=$_["generate_trial"]?></a></li><p>
                                    <?php } ?>
									<div class="separator"></div>
                                    <li><a href="./user_reseller.php"><span class="mdi mdi-account-plus-outline mdi-18px"></span> <?=$_["add_user"]?></a></li><p>
                                    <li><a href="./users.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_users"]?></a></li><p>
									<div class="separator"></div>
									<li><a href="./user_reseller.php?mag"><span class="mdi mdi-account-plus-outline mdi-18px"></span> <?=$_["add_mag"]?></a></li><p>
                                    <li><a href="./mags.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_mag_devices"]?></a></li><p>
									<div class="separator"></div>
									<li><a href="./user_reseller.php?e2"><span class="mdi mdi-account-plus-outline mdi-18px"></span> <?=$_["add_enigma"]?></a></li><p>
                                    <li><a href="./enigmas.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_enigma_devices"]?></a></li>
                                </ul>
                            </li>
							<?php }
                            if (($rPermissions["is_reseller"]) && ($rPermissions["create_sub_resellers"])) { ?>
                            <li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-account-multiple-outline mdi-18px text-primary"></i><?=$_["reg_users"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <?php if ($rPermissions["is_admin"]) { ?>
                                    <li><a href="./reg_user.php"><span class="mdi mdi-account-multiple-plus-outline mdi-18px"> <?=$_["add_subreseller"]?></a></li><p>
                                    <?php } else { ?>
                                    <li><a href="./subreseller.php"><span class="mdi mdi-account-multiple-plus-outline mdi-18px"> <?=$_["add_subreseller"]?></a></li><p>
                                    <?php } ?>
                                    <li><a href="./reg_users.php"><span class="mdi mdi-account-multiple-outline mdi-18px"> <?=$_["manage_subresellers"]?></a></li>
                                </ul>
                            </li>
                            <?php }
							if ($rPermissions["is_admin"]) {
							if ((hasPermissions("adv", "add_movie")) OR (hasPermissions("adv", "import_movies")) OR (hasPermissions("adv", "movies")) OR (hasPermissions("adv", "series")) OR (hasPermissions("adv", "add_series")) OR (hasPermissions("adv", "radio")) OR (hasPermissions("adv", "add_radio")) OR (hasPermissions("adv", "mass_sedits_vod")) OR (hasPermissions("adv", "mass_sedits")) OR (hasPermissions("adv", "mass_edits_radio"))) { ?>
                            <li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-video-outline mdi-18px text-success"></i><?=$_["vod"]?> <div class="arrow-down"></div></a>
                                       <ul class="submenu">
											<?php if (hasPermissions("adv", "add_movie")) { ?>
                                            <li><a href="./movie.php"><span class="mdi mdi-plus mdi-18px"> <?=$_["add_movie"]?></a></li><p>
											<?php }
											if (hasPermissions("adv", "movies")) { ?>
                                            <li><a href="./movies.php"><span class="mdi mdi-movie mdi-18px"> <?=$_["manage_movies"]?></a></li><p>
											<?php }
                                            if ((hasPermissions("adv", "add_series")) OR (hasPermissions("adv", "series")) OR (hasPermissions("adv", "episodes"))) { ?>
                                            <div class="separator"></div>
                                            <?php }
											if (hasPermissions("adv", "add_series")) { ?>
                                            <li><a href="./serie.php"><span class="mdi mdi-plus mdi-18px"> <?=$_["add_series"]?></a></li><p>
											<?php }
											if (hasPermissions("adv", "series")) { ?>
                                            <li><a href="./series.php"><span class="mdi mdi-youtube-tv mdi-18px"> <?=$_["manage_series"]?></a></li><p>
											<?php }
											if (hasPermissions("adv", "episodes")) { ?>
                                            <li><a href="./episodes.php"><span class="mdi mdi-youtube-tv mdi-18px"> <?=$_["manage_episodes"]?></a></li><p>
											<?php }
											if ((hasPermissions("adv", "mass_sedits_vod")) OR (hasPermissions("adv", "mass_sedits")) OR (hasPermissions("adv", "mass_edit_radio"))) { ?>
                                            <div class="separator"></div>
											<?php }
											if (hasPermissions("adv", "add_radio")) { ?>
                                            <li><a href="./radio.php"><span class="mdi mdi-plus mdi-18px"> <?=$_["add_station"]?></a></li><p>
											<?php }
											if (hasPermissions("adv", "radio")) { ?>
                                            <li><a href="./radios.php"><span class="mdi mdi-radio mdi-18px"> <?=$_["manage_stations"]?></a></li>
											<?php } ?>
                                        </ul>
                                    </li>
							<?php }
							if ((hasPermissions("adv", "add_stream")) OR (hasPermissions("adv", "import_streams")) OR (hasPermissions("adv", "create_channel")) OR (hasPermissions("adv", "streams")) OR (hasPermissions("adv", "mass_edit_streams"))  OR (hasPermissions("adv", "stream_tools"))  OR (hasPermissions("adv", "stream_errors"))  OR (hasPermissions("adv", "fingerprint"))) { ?>
                            <li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-play-circle-outline mdi-18px text-info"></i><?=$_["streams"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
									<?php if (hasPermissions("adv", "add_stream")) { ?>
                                    <li><a href="./stream.php"><span class="mdi mdi-plus mdi-18px"> <?=$_["add_stream"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "streams")) { ?>
                                    <li><a href="./streams.php"><span class="mdi mdi-play-circle-outline mdi-18px"> <?=$_["manage_streams"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "create_channel")) { ?>
                                    <li><a href="./created_channel.php"><span class="mdi mdi-plus mdi-18px"> <?=$_["create_channel"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							if ((hasPermissions("adv", "add_bouquet")) OR (hasPermissions("adv", "bouquets"))) { ?>
                            <li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-flower-tulip-outline text-purple"></i><?=$_["bouquets"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
									<?php if (hasPermissions("adv", "add_bouquet")) { ?>
                                    <li><a href="./bouquet.php"><span class="mdi mdi-plus mdi-18px"> <?=$_["add_bouquet"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "bouquets")) { ?>
                                    <li><a href="./bouquets.php"><span class="mdi mdi-flower-tulip-outline mdi-18px"> <?=$_["manage_bouquets"]?></a></li><p>
                                    <?php }
									if (hasPermissions("adv", "edit_bouquet")) { ?>
                                    <li><a href="./bouquet_sort.php"><span class="mdi mdi-reorder-horizontal mdi-18px"> <?=$_["order_bouquets"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
                            <?php }
							}
                            if (($rPermissions["is_reseller"]) && ($rPermissions["reset_stb_data"])) { ?>
                            <li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-play-circle-outline mdi-18px text-info"></i><?=$_["content"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <li><a href="./streams.php"><?=$_["streams"]?></a></li><p>
                                    <li><a href="./movies.php"><?=$_["movies"]?></a></li><p>
                                    <li><a href="./series.php"><?=$_["series"]?></a></li><p>
                                    <li><a href="./episodes.php"><?=$_["episodes"]?></a></li><p>
                                    <li><a href="./radios.php"><?=$_["stations"]?></a></li>
                                </ul>
                            </li>
                            <?php }
							if ((hasPermissions("adv", "add_user")) OR (hasPermissions("adv", "users")) OR (hasPermissions("adv", "mass_edit_users")) OR (hasPermissions("adv", "mng_regusers")) OR (hasPermissions("adv", "add_reguser")) OR (hasPermissions("adv", "credits_log")) OR (hasPermissions("adv", "panel_errors"))  OR (hasPermissions("adv", "client_request_log")) OR (hasPermissions("adv", "reg_userlog"))OR (hasPermissions("adv", "live_connections")) OR (hasPermissions("adv", "connection_logs")) OR (hasPermissions("adv", "stream_errors")) OR (hasPermissions("adv", "manage_events"))) { ?>
							<li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-information-outline mdi-18px text-danger"></i><?=$_["logs"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
								    <?php if (hasPermissions("adv", "live_connections")) { ?>
                                    <li><a href="./live_connections.php"><span class="mdi mdi-account-network-outline mdi-18px"> <?=$_["live_connections"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "panel_errors")) { ?>
                                    <li><a href="./panel_logs.php"><span class="mdi mdi-file-document-outline mdi-18px"> <?=$_["panel_logs"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "connection_logs")) { ?>
                                    <li><a href="./user_activity.php"><span class="mdi mdi-file-document-outline mdi-18px"> <?=$_["activity_logs"]?></a></li><p>
                                    <li><a href="./user_ips.php"><span class="mdi mdi-ip mdi-18px"> <?=$_["line_ip_usage"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "credits_log")) { ?>
                                    <li><a href="./credit_logs.php"><span class="mdi mdi-credit-card-multiple mdi-18px"> <?=$_["credit_logs"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "client_request_log")) { ?>
                                    <li><a href="./client_logs.php"><span class="mdi mdi-account-search mdi-18px"> <?=$_["client_logs"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "reg_userlog")) { ?>
                                    <li><a href="./reg_user_logs.php"><span class="mdi mdi-account-details mdi-18px"> <?=$_["reseller_logs"]?></a></li><p>
                                    <?php }
									if (hasPermissions("adv", "stream_errors")) { ?>
                                    <li><a href="./stream_logs.php"><span class="mdi mdi-file-document-outline mdi-18px"> <?=$_["stream_logs"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "manage_events")) { ?>
                                    <li><a href="./mag_events.php"><span class="mdi mdi-message-outline mdi-18px"> <?=$_["mag_event_logs"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php } 
							if ((hasPermissions("adv", "add_user")) OR (hasPermissions("adv", "users")) OR (hasPermissions("adv", "mass_edit_users")) OR (hasPermissions("adv", "import_streams")) OR (hasPermissions("adv", "streams")) OR (hasPermissions("adv", "mass_edit_streams")) OR (hasPermissions("adv", "manage_events")) OR (hasPermissions("adv", "import_movies")) OR (hasPermissions("adv", "movies")) OR (hasPermissions("adv", "series")) OR (hasPermissions("adv", "radio")) OR (hasPermissions("adv", "mass_sedits_vod")) OR (hasPermissions("adv", "mass_sedits")) OR (hasPermissions("adv", "mass_edits_radio")) OR (hasPermissions("adv", "stream_tools")) OR (hasPermissions("adv", "fingerprint")) OR (hasPermissions("adv", "mass_delete"))) { ?>
							<li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-progress-wrench mdi-18px text-primary"></i><?=$_["tools"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
								    <?php if (hasPermissions("adv", "mass_edit_users")) { ?>
                                    <li><a href="./user_mass.php"><span class="mdi mdi-account-edit mdi-18px"> <?=$_["mass_edit_users"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "mass_edit_streams")) { ?>
                                    <li><a href="./stream_mass.php"><span class="mdi mdi-border-color mdi-18px"> <?=$_["mass_edit_streams"]?></a></li><p> 
									<?php }
									if (hasPermissions("adv", "mass_sedits_vod")) { ?>
                                    <li><a href="./movie_mass.php"><span class="mdi mdi-border-color mdi-18px"> <?=$_["mass_edit_movies"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "mass_sedits")) { ?>
                                    <li><a href="./series_mass.php"><span class="mdi mdi-border-color mdi-18px"> <?=$_["mass_edit_series"]?></a></li><p>
                                    <li><a href="./episodes_mass.php"><span class="mdi mdi-border-color mdi-18px"> <?=$_["mass_edit_episodes"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "mass_edit_radio")) { ?>
                                    <li><a href="./radio_mass.php"><span class="mdi mdi-border-color mdi-18px"> <?=$_["mass_edit_stations"]?></a></li><p>
									<?php }
								    if (hasPermissions("adv", "mass_delete")) { ?>
                                    <li><a href="./mass_delete.php"><span class="mdi mdi-delete-outline mdi-18px"> <?=$_["mass_delete"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "fingerprint")) { ?>
                                    <li><a href="./fingerprint.php"><span class="mdi mdi-fingerprint mdi-18px"> <?=$_["fingerprint"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "stream_tools")) { ?>
									<li><a href="./stream_tools.php"><span class="mdi mdi-wrench-outline mdi-rotate-90 mdi-18px"> <?=$_["stream_tools"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "import_movies")) { ?>
                                    <li><a href="./movie.php?import"><span class="mdi mdi-file-plus mdi-18px"> <?=$_["import_movies"]?></a></li><p>
									<?php }
									if (hasPermissions("adv", "import_streams")) { ?>
                                    <li><a href="./stream.php?import"><span class="mdi mdi-file-plus mdi-18px"> <?=$_["import_streams"]?></a></li><p>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
                            if ($rPermissions["is_reseller"]) { ?>
                            <li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-email-outline mdi-18px text-warning"></i><?=$_["support"]?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <li><a href="./ticket.php"><span class="mdi mdi-message-text-outline mdi-18px"> <?=$_["create_ticket"]?></a></li><p>
                                    <li><a href="./tickets.php"><span class="mdi mdi-message-settings-variant mdi-18px"> <?=$_["manage_tickets"]?></a></li><p>
									<?php if ($rPermissions["allow_import"]) { ?>
                                    <li><a href="./resellersmarters.php"><span class="mdi mdi-message-settings-variant mdi-18px"> Reseller API Key</a></li>
									<?php } ?>
                                </ul>
                            </li>
							<li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-apps mdi-18px text-success"></i>Apps Iptv <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <li><a href="./duplexplay.php"><span class="mdi mdi-account-star mdi-18px"> DUPLEX IPTV</a></li><p>
                                    <li><a href="./netiptv.php"><span class="mdi mdi-account-star mdi-18px"> NET IPTV</a></li><p>
									<li><a href="./siptv.php"><span class="mdi mdi-account-star mdi-18px"> SMART IPTV</a></li><p>
									<li><a href="./siptvextreme.php"><span class="mdi mdi-account-star mdi-18px"> IPTV EXTREME</a></li><p>
									<li><a href="./nanomid.php"><span class="mdi mdi-account-star mdi-18px"> NANOMID</a></li>
									<!--<li><a href="./ss-iptv.php"><span class="mdi mdi-account-star mdi-18px"> SS IPTV</a></li>-->
                                </ul>
                            </li>
							<?php }
                            if (($rPermissions["is_admin"]) && (hasPermissions("adv", "manage_tickets"))) { ?>
                            <li>
                                <a href="./tickets.php"> <i class="mdi mdi-email-outline mdi-18px text-pink"></i><?=$_["tickets"]?></a>
                            </li>
							<li class="has-submenu">
                                <a href="#"> <i class="mdi mdi-apps mdi-18px text-success"></i>Apps Iptv <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <li><a href="./duplexplay.php"><span class="mdi mdi-account-star mdi-18px"> DUPLEX IPTV</a></li><p>
                                    <li><a href="./netiptv.php"><span class="mdi mdi-account-star mdi-18px"> NET IPTV</a></li><p>
									<li><a href="./siptv.php"><span class="mdi mdi-account-star mdi-18px"> SMART IPTV</a></li><p>
									<li><a href="./siptvextreme.php"><span class="mdi mdi-account-star mdi-18px"> IPTV EXTREME</a></li><p>
									<li><a href="./nanomid.php"><span class="mdi mdi-account-star mdi-18px"> NANOMID</a></li>
									<!--<li><a href="./ss-iptv.php"><span class="mdi mdi-account-star mdi-18px"> SS IPTV</a></li>-->
                                </ul>
                            </li>
                            <?php } 
							if (($rPermissions["is_reseller"]) && ($rAdminSettings["active_mannuals"])) { ?>
                            <li>
                                <a href="./reseller_mannuals.php"> <i class="mdi mdi-book-open-page-variant mdi-18px text-info"></i><?=$_["mannuals"]?></a>
                            </li>
                            <?php } ?>
                        </ul>
                        <!-- End navigation menu -->
                        <div class="clearfix"></div>
                    </div>
                    <!-- end #navigation -->
                </div>
                <!-- end container -->
            </div>
            <!-- end navbar-custom -->
        </header>
        <!-- End Navigation Bar-->