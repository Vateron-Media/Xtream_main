<?php
include "functions.php";
if (isset($_SESSION['hash'])) {
	header("Location: ./dashboard.php");
	exit;
}

if (intval($rSettings["login_flood"]) > 0) {
	$ipTV_db_admin->query("SELECT COUNT(`id`) AS `count` FROM `login_flood` WHERE `ip` = '" . getIP() . "' AND TIME_TO_SEC(TIMEDIFF(NOW(), `dateadded`)) <= 86400;");
	if ($ipTV_db_admin->num_rows() == 1) {
		if (intval($ipTV_db_admin->get_row()["count"]) >= intval($rSettings["login_flood"])) {
			$_STATUS = 7;
		}
	}
}

if (!isset($_STATUS)) {
	$rGA = new PHPGangsta_GoogleAuthenticator();
	if ((isset(ipTV_lib::$request["username"])) && (isset(ipTV_lib::$request["password"]))) {
		if ($rSettings["recaptcha_enable"]) {
			$rResponse = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $rSettings["recaptcha_v2_secret_key"] . '&response=' . ipTV_lib::$request['g-recaptcha-response']), true);
			if ((!$rResponse["success"]) && (!in_array("invalid-input-secret", $rResponse["error-codes"]))) {
				$_STATUS = 5;
			}
		}
		if (!isset($_STATUS)) {
			$rUserInfo = doLogin(ipTV_lib::$request["username"], ipTV_lib::$request["password"]);
			if (isset($rUserInfo)) {
				if ((strlen(ipTV_lib::$request["password"]) < intval($rSettings["pass_length"])) && (intval($rSettings["pass_length"]) > 0)) {
					$rChangePass = md5($rUserInfo["password"]);
				} else {
					$rPermissions = getPermissions($rUserInfo["member_group_id"]);
					if (($rPermissions) && ((($rPermissions["is_admin"]) or ($rPermissions["is_reseller"])) && ((!$rPermissions["is_banned"]) && ($rUserInfo["status"] == 1)))) {
						$ipTV_db_admin->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `ip` = ? WHERE `id` = ?;", getIP(), intval($rUserInfo["id"]));
						$_SESSION['hash'] = md5($rUserInfo["username"]);
						$_SESSION['ip'] = getIP();
						if ($rPermissions["is_admin"]) {
							if (strlen(ipTV_lib::$request["referrer"]) > 0) {
								header("Location: ." . ipTV_lib::$request["referrer"]);
							} else {
								header("Location: ./dashboard.php");
							}
						} else {
							$ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '', '', " . intval(time()) . ", '[<b>UserPanel</b>] -> Logged In');");
							if (strlen(ipTV_lib::$request["referrer"]) > 0) {
								header("Location: ." . ipTV_lib::$request["referrer"]);
							} else {
								header("Location: ./reseller.php");
							}
						}
					} elseif (($rPermissions) && ((($rPermissions["is_admin"]) or ($rPermissions["is_reseller"])) && ($rPermissions["is_banned"]))) {
						$_STATUS = 2;
					} elseif (($rPermissions) && ((($rPermissions["is_admin"]) or ($rPermissions["is_reseller"])) && (!$rUserInfo["status"]))) {
						$_STATUS = 3;
					} else {
						$_STATUS = 4;
					}
				}
			} else {
				if (intval($rSettings["login_flood"]) > 0) {
					$ipTV_db_admin->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('" . ipTV_lib::$request["username"] . "', '" . getIP() . "');");
				}
				$_STATUS = 0;
			}
		}
	} elseif ((isset(ipTV_lib::$request["newpass"])) && (isset(ipTV_lib::$request["confirm"])) && (isset(ipTV_lib::$request["hash"])) && (isset(ipTV_lib::$request["change"]))) {
		$rUserInfo = getRegisteredUserHash(ipTV_lib::$request["hash"]);
		$rChangePass = ipTV_lib::$request["change"];
		if (($rUserInfo) && ($rChangePass == md5($rUserInfo["password"]))) {
			if ((ipTV_lib::$request["newpass"] == ipTV_lib::$request["confirm"]) && (strlen(ipTV_lib::$request["newpass"]) >= intval($rSettings["pass_length"]))) {
				$rPermissions = getPermissions($rUserInfo["member_group_id"]);
				if (($rPermissions) && ((($rPermissions["is_admin"]) or ($rPermissions["is_reseller"])) && ((!$rPermissions["is_banned"]) && ($rUserInfo["status"] == 1)))) {
					$ipTV_db_admin->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `password` = '" . cryptPassword(ipTV_lib::$request["newpass"]) . "', `ip` = '" . getIP() . "' WHERE `id` = " . intval($rUserInfo["id"]) . ";");
					$_SESSION['hash'] = md5($rUserInfo["username"]);
					$_SESSION['ip'] = getIP();
					if ($rPermissions["is_admin"]) {
						header("Location: ./dashboard.php");
					} else {
						$ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '', '', " . intval(time()) . ", '[<b>UserPanel</b>] -> Logged In');");
						header("Location: ./reseller.php");
					}
				} elseif (($rPermissions) && ((($rPermissions["is_admin"]) or ($rPermissions["is_reseller"])) && ($rPermissions["is_banned"]))) {
					$_STATUS = 2;
				} elseif (($rPermissions) && ((($rPermissions["is_admin"]) or ($rPermissions["is_reseller"])) && (!$rUserInfo["status"]))) {
					$_STATUS = 3;
				} else {
					$_STATUS = 4;
				}
			} else {
				$_STATUS = 6;
			}
		} else {
			if (intval($rSettings["login_flood"]) > 0) {
				$ipTV_db_admin->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('" . ipTV_lib::$request["username"] . "', '" . getIP() . "');");
			}
			$_STATUS = 0;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title><?= htmlspecialchars($rSettings["server_name"]) ?> - <?= $_["login"] ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">
	<!-- App css -->
	<link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/app.css" rel="stylesheet" type="text/css" />
	<style>
		.g-recaptcha {
			display: inline-block;
		}
	</style>
</head>

<body class="authentication-bg authentication-bg-pattern">
	<div class="account-pages mt-5 mb-5">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6 col-xl-5">
					<?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
						<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
							role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
							<?= $_["login_message_2"] ?>
						</div>
					<?php } elseif ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
						<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
							role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
							<?= $_["login_message_3"] ?>
						</div>
					<?php } elseif ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
						<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
							role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
							<?= $_["login_message_4"] ?>
						</div>
					<?php } elseif ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
						<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
							role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
							<?= $_["login_message_5"] ?>
						</div>
					<?php } elseif ((isset($_STATUS)) && ($_STATUS == 4)) { ?>
						<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
							role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
							<?= $_["login_message_6"] ?>
						</div>
					<?php } elseif ((isset($_STATUS)) && ($_STATUS == 5)) { ?>
						<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
							role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
							<?= $_["login_message_7"] ?>
						</div>
					<?php } elseif ((isset($_STATUS)) && ($_STATUS == 6)) { ?>
						<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
							role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
							<?= str_replace("{num}", $rSettings["pass_length"], $_["login_message_8"]) ?>
						</div>
					<?php } ?>
					<div class="card-login">
						<div class="card-body p-4">
							<div class="text-center w-auto m-autologin">
								<span><img src="<?= $rSettings["logo_url"] ?>" width="100px" alt=""></span>
								<p class="text-muted mb-4 mt-3"></p>
							</div>
							<h5 class="auth-title"><?= $_["admin_reseller_interface"] ?></h5>
							<?php if ((!isset($_STATUS)) or ($_STATUS <> 7)) { ?>
								<form action="./login.php" method="POST" data-parsley-validate="" id="login_form">
									<input type="hidden" name="referrer" value="<?= ipTV_lib::$request["referrer"] ?>" />
									<?php if ((!isset($rQR)) && (!isset($rChangePass))) { ?>
										<div class="form-group mblog-3" id="username_group">
											<label class="label-login" for="username"><?= $_["username"] ?></label>
											<input class="form-login" autocomplete="off" type="text" id="username"
												name="username" required data-parsley-trigger="change"
												placeholder="<?= $_["enter_your_username"] ?>">
										</div>
										<div class="form-group mblog-3">
											<label class="label-login" for="password"><?= $_["password"] ?></label>
											<input class="form-login" autocomplete="off" type="password" required
												data-parsley-trigger="change" id="password" name="password"
												placeholder="<?= $_["enter_your_password"] ?>">
										</div>
										<?php if ($rSettings["recaptcha_enable"]) { ?>
											<h5 class="auth-title text-center">
												<div class="g-recaptcha" id="verification"
													data-sitekey="<?= $rSettings["recaptcha_v2_site_key"] ?>"></div>
											</h5>
										<?php }
									} elseif (isset($rChangePass)) { ?>
										<input type="hidden" name="hash" value="<?= md5($rUserInfo["username"]) ?>" />
										<input type="hidden" name="change" value="<?= $rChangePass ?>" />
										<div class="form-group mb-3 text-center">
											<p><?= str_replace("{num}", $rSettings["pass_length"], $_["login_message_9"]) ?>
											</p>
										</div>
										<div class="form-group mb-3">
											<label for="newpass"><?= $_["new_password"] ?></label>
											<input class="form-login" autocomplete="off" type="password" id="newpass"
												name="newpass" required data-parsley-trigger="change"
												placeholder="<?= $_["enter_a_new_password"] ?>">
										</div>
										<div class="form-group mb-3">
											<label for="confirm"><?= $_["confirm_password"] ?></label>
											<input class="form-login" autocomplete="off" type="password" id="confirm"
												name="confirm" required data-parsley-trigger="change"
												placeholder="<?= $_["confirm_your_password"] ?>">
										</div>
									<?php } else { ?>
										<input type="hidden" name="hash" value="<?= md5($rUserInfo["username"]) ?>" />
										<input type="hidden" name="auth" value="<?= $rAuth ?>" />
										<?php if (isset($rNew2F)) { ?>
											<div class="form-group mb-3 text-center">
												<p><?= $_["login_message_10"] ?></p>
												<img src="<?= $rQR ?>">
											</div>
										<?php } ?>
										<div class="form-group mb-3">
											<label for="gauth"><?= $_["google_authenticator_code"] ?></label>
											<input class="form-login" autocomplete="off" type="gauth" required="" id="gauth"
												name="gauth" placeholder="<?= $_["enter_your_auth_code"] ?>">
										</div>
									<?php } ?>
									<div class="form-group mb-0 text-center">
										<button class="btn btn-dangerlog btn-block" type="submit"
											id="login_button"><?= $_["login"] ?></button>
									</div>
								</form>
							<?php } else { ?>
								<div class="form-group mb-3 text-center text-danger">
									<p><?= $_["login_message_11"] ?></p>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="assets/js/vendor.min.js"></script>
		<script src="assets/libs/parsleyjs/parsley.min.js"></script>
		<script src="assets/js/app.min.js"></script>
		<?php if ($rSettings["recaptcha_enable"]) { ?>
			<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<?php } ?>
		<script>
			$(document).ready(function () {
				if (window.location.hash.substring(0, 1) == "#") {
					$("#username_group").hide();
					$("#username").val(window.location.hash.substring(1));
					$("#login_form").attr('action', './login.php#' + window.location.hash.substring(1));
					$("#login_button").html("<?= $_["login_as"] ?> " + window.location.hash.substring(1).toUpperCase());
				}
			});
		</script>
</body>
<!-- XC_VM -->
</html>