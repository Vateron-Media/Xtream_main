<?php
include_once("/home/xtreamcodes/admin/HTMLPurifier.standalone.php");
require_once '/home/xtreamcodes/includes/admin.php';

$rPurifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());
$rTableSearch = strtolower(basename($_SERVER["SCRIPT_FILENAME"], '.php')) === "table_search";

if (isset($_SESSION['hash'])) {
    $rUserInfo = getRegisteredUserHash($_SESSION['hash']);
    $rSettings["dark_mode"] = $rUserInfo["dark_mode"];
    $rPermissions = getPermissions($rUserInfo['member_group_id']);
    if ($rPermissions["is_admin"]) {
        $rPermissions["is_reseller"] = 0;
    }
    $rPermissions["advanced"] = json_decode($rPermissions["allowed_pages"], true);
    if ((!$rUserInfo) or (!$rPermissions) or ((!$rPermissions["is_admin"]) && (!$rPermissions["is_reseller"])) or (($_SESSION['ip'] <> getIP()) && ($rSettings["ip_logout"]))) {
        unset($rUserInfo);
        unset($rPermissions);
        session_unset();
        session_destroy();
        header("Location: ./index.php");
    }
    $rCategories = getCategories_admin();
    $rServers = getStreamingServers();
    $rServerError = false;
    foreach ($rServers as $rServer) {
        if (((((time() - $rServer["last_check_ago"]) > 360)) or ($rServer["status"] == 2)) and ($rServer["can_delete"] == 1) and ($rServer["status"] <> 3)) {
            $rServerError = true;
        }
        if (($rServer["status"] == 3) && ($rServer["last_check_ago"] > 0)) {
            $ipTV_db_admin->query("UPDATE `servers` SET `status` = 1 WHERE `id` = " . intval($rServer["id"]) . ";");
            $rServers[intval($rServer["id"])]["status"] = 1;
        }
    }
}

if ((strlen($nabilos["default_lang"]) > 0) && (file_exists("./lang/" . $nabilos["default_lang"] . ".php"))) {
    include "./lang/" . $nabilos["default_lang"] . ".php";
} else {
    include "/home/xtreamcodes/admin/lang/en.php";
}

if (isset(ipTV_lib::$request['status'])) {
    $_STATUS = intval(ipTV_lib::$request['status']);
}

if (getPageName() != 'setup') {
    $ipTV_db_admin->query('SELECT COUNT(`id`) AS `count` FROM `reg_users` LEFT JOIN `member_groups` ON `member_groups`.`group_id` = `reg_users`.`member_group_id` WHERE `member_groups`.`is_admin` = 1;');

    if ($ipTV_db_admin->get_row()['count'] == 0) {
        header('Location: ./setup.php');
        exit();
    }
}
