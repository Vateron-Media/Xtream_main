<?php

require "./init.php";
$user_ip = ipTV_streaming::getUserIP();
if (!isset(ipTV_lib::$request["username"]) || !isset(ipTV_lib::$request["password"]) || !isset(ipTV_lib::$request["type"])) {
    if (ipTV_lib::$settings["flood_get_block"] == 1) {
        $ipTV_db->query("INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES('%s','%s','%d')", $user_ip, "BRUTE FORCING", time());
        ipTV_servers::RunCommandServer(array_keys(ipTV_lib::$StreamingServers), "sudo /sbin/iptables -A INPUT -s $user_ip -j DROP");
    }
    exit("Missing parameters.");
}

$username = ipTV_lib::$request["username"];
$password = ipTV_lib::$request["password"];
$device_key = ipTV_lib::$request["type"];
$output_key = (empty(ipTV_lib::$request["output"]) ? "" : ipTV_lib::$request["output"]);
$ipTV_db->query("SELECT `id` FROM `users` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1", $username, $password);

if (0 < $ipTV_db->num_rows()) {
    $user_id = $ipTV_db->get_col();
    if ($output = GenerateList($user_id, $device_key, $output_key, true)) {
        echo $output;
        exit();
    }
} else if (ipTV_lib::$settings["flood_get_block"] == 1) {
    $ipTV_db->query("INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES('%s','%s','%d')", $user_ip, "BRUTE FORCING", time());
    ipTV_servers::RunCommandServer(array_keys(ipTV_lib::$StreamingServers), "sudo /sbin/iptables -A INPUT -s $user_ip -j DROP");
}
