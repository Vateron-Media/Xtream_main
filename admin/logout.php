<?php
include "functions.php";
if (!isset($_SESSION['hash'])) { header("Location: ./login.php"); exit; }

if ($rPermissions["is_reseller"]) {
    $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '', '', ".intval(time()).", '[<b>UserPanel</b> -> <u>Logged Out</u>]');");
}

session_destroy();
header("Location: ./login.php");
?>