<?php
include "functions.php";
if (!isset($_SESSION['hash'])) {
    header("Location: ./login.php");
    exit;
}

if ($rPermissions["is_reseller"]) {
    $ipTV_db_admin->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '', '', " . intval(time()) . ", '[<b>UserPanel</b>] -> Logged Out');");
}

session_destroy();
header("Location: ./login.php");
