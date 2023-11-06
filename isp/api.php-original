<?php
$rURL = "https://isp.xtream-ui.com/api.php?ip=";

if ((isset($_GET["ip"])) && (filter_var($_GET["ip"], FILTER_VALIDATE_IP))) {
    if (!file_exists("./data/".md5($_GET["ip"]))) {
        $rData = json_decode(file_get_contents($rURL.$_GET["ip"]), True);
        if (strlen($rData["isp_info"]["description"]) > 0) {
            $rEnc = json_encode($rData);
            file_put_contents("./data/".md5($_GET["ip"]), $rEnc);
            echo $rEnc;
        }
    } else {
        echo file_get_contents("./data/".md5($_GET["ip"]));
    }
}
?>