<?php

register_shutdown_function("shutdown");
set_time_limit(0);
require "../init.php";

$f0ac6ad2b40669833242a10c23cad2e0 = true;
$E824497a502b6e5dd609c0acb45697c7 = false;
$e13ac89e162bcc9913e553b949f755b6 = 0;
$E821605d1d9382d422040b86d29632d9 = 0;
$f4889efa84e1f2e30e5e9780973f68cb = $_SERVER["REMOTE_ADDR"];
if (in_array($f4889efa84e1f2e30e5e9780973f68cb, Cd89785224751CCA8017139daF9e891e::aB69E1103c96eE33FE21a6453d788925(true))) {
    if (!(empty(A78bf8d35765be2408C50712ce7a43aD::$request["stream"]) || empty(A78BF8d35765bE2408C50712ce7A43aD::$request["password"]) || a78Bf8d35765be2408c50712Ce7a43aD::$settings["live_streaming_pass"] != a78BF8D35765bE2408C50712CE7A43AD::$request["password"])) {
        $f0ac6ad2b40669833242a10c23cad2e0 = false;
        $c3a18c26bfa971a25d2e6ada870ff735 = pathinfo(a78bf8d35765be2408c50712Ce7a43AD::$request["stream"]);
        $ba85d77d367dcebfcc2a3db9e83bb581 = intval($c3a18c26bfa971a25d2e6ada870ff735["filename"]);
        $F1350a5569e4b73d2f9cb26483f2a0c1 = $c3a18c26bfa971a25d2e6ada870ff735["extension"];
        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.* FROM `streams` t1 INNER JOIN `streams_sys` t2 ON t2.stream_id = t1.id AND t2.pid IS NOT NULL AND t2.server_id = '%d' INNER JOIN `streams_types` t3 ON t3.type_id = t1.type AND t3.type_key = 'movie' WHERE t1.`id` = '%d'", SERVER_ID, $ba85d77d367dcebfcc2a3db9e83bb581);
        if (!(A78bF8d35765BE2408c50712cE7a43ad::$settings["use_buffer"] == 0)) {
            goto E4408e37ec6ae596b1eee5e7497396f5;
        }
        header("X-Accel-Buffering: no");
        E4408e37ec6ae596b1eee5e7497396f5:
        if (!($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0)) {
            goto A87b38db3382712a57fe78b72653a92e;
        }
        $Fc8c2b91e5fde0dc817c47293478940a = $f566700a43ee8e1f0412fe10fbdf03df->F1ed191D78470660edfF4A007696Bc1f();
        $f566700a43ee8e1f0412fe10fbdf03df->ca531f7bDC43b966dEFB4aBA3C8Faf22();
        $E6dd23f358d554b9a74e3ae676bc8c9b = MOVIES_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "." . $F1350a5569e4b73d2f9cb26483f2a0c1;
        if (!file_exists($E6dd23f358d554b9a74e3ae676bc8c9b)) {
            A87b38db3382712a57fe78b72653a92e:
            function shutdown() {
                global $f566700a43ee8e1f0412fe10fbdf03df, $f0ac6ad2b40669833242a10c23cad2e0;
                if (!$f0ac6ad2b40669833242a10c23cad2e0) {
                    goto Aa4d2685d66ddf0b5d5f86f5f8edfd0f;
                }
                D9f93B7c177E377D0BBFE315EaEaE505();
                Aa4d2685d66ddf0b5d5f86f5f8edfd0f:
                if (!is_object($f566700a43ee8e1f0412fe10fbdf03df)) {
                    goto Df4bed6499125751ad34d2048f932c77;
                }
                $f566700a43ee8e1f0412fe10fbdf03df->cA531f7BdC43B966dEFb4aBA3c8FAf22();
                Df4bed6499125751ad34d2048f932c77:
                fastcgi_finish_request();
            }
            // [PHPDeobfuscator] Implied script end
            return;
        }
        switch ($F1350a5569e4b73d2f9cb26483f2a0c1) {
            case "mp4":
                header("Content-type: video/mp4");
                goto C582df01b0baba36e89ad6747d20587c;
            case "mkv":
                header("Content-type: video/x-matroska");
                goto C582df01b0baba36e89ad6747d20587c;
            case "avi":
                header("Content-type: video/x-msvideo");
                goto C582df01b0baba36e89ad6747d20587c;
            case "3gp":
                header("Content-type: video/3gpp");
                goto C582df01b0baba36e89ad6747d20587c;
            case "flv":
                header("Content-type: video/x-flv");
                goto C582df01b0baba36e89ad6747d20587c;
            case "wmv":
                header("Content-type: video/x-ms-wmv");
                goto C582df01b0baba36e89ad6747d20587c;
            case "mov":
                header("Content-type: video/quicktime");
                goto C582df01b0baba36e89ad6747d20587c;
            case "ts":
                header("Content-type: video/mp2t");
                goto C582df01b0baba36e89ad6747d20587c;
            default:
                header("Content-Type: application/octet-stream");
        }
        C582df01b0baba36e89ad6747d20587c:
        $Ab9f45b38498c3a010f3c4276ad5767c = @fopen($E6dd23f358d554b9a74e3ae676bc8c9b, "rb");
        $Ff876e96994aa5b09ce92e771efe2038 = filesize($E6dd23f358d554b9a74e3ae676bc8c9b);
        $b362cb2e1492b66663cf3718328409ad = $Ff876e96994aa5b09ce92e771efe2038;
        $start = 0;
        $ebe823668f9748302d3bd87782a71948 = $Ff876e96994aa5b09ce92e771efe2038 - 1;
        header("Accept-Ranges: 0-{$b362cb2e1492b66663cf3718328409ad}");
        if (!isset($_SERVER["HTTP_RANGE"])) {
            goto f2934775915c6c890c49e769efac5cf8;
        }
        $dccf2f0f292208ba833261a4da87860d = $start;
        $A34771e85be87aded632236239e94d98 = $ebe823668f9748302d3bd87782a71948;
        list(, $cabafd9509f1a525c1d85143a5372ed8) = explode("=", $_SERVER["HTTP_RANGE"], 2);
        if (!(strpos($cabafd9509f1a525c1d85143a5372ed8, ",") !== false)) {
            if ($cabafd9509f1a525c1d85143a5372ed8 == "-") {
                $dccf2f0f292208ba833261a4da87860d = $Ff876e96994aa5b09ce92e771efe2038 - substr($cabafd9509f1a525c1d85143a5372ed8, 1);
                goto ad88b23ba6d021cf7fa3173ed8293848;
            }
            $cabafd9509f1a525c1d85143a5372ed8 = explode("-", $cabafd9509f1a525c1d85143a5372ed8);
            $dccf2f0f292208ba833261a4da87860d = $cabafd9509f1a525c1d85143a5372ed8[0];
            $A34771e85be87aded632236239e94d98 = isset($cabafd9509f1a525c1d85143a5372ed8[1]) && is_numeric($cabafd9509f1a525c1d85143a5372ed8[1]) ? $cabafd9509f1a525c1d85143a5372ed8[1] : $Ff876e96994aa5b09ce92e771efe2038;
            ad88b23ba6d021cf7fa3173ed8293848:
            $A34771e85be87aded632236239e94d98 = $A34771e85be87aded632236239e94d98 > $ebe823668f9748302d3bd87782a71948 ? $ebe823668f9748302d3bd87782a71948 : $A34771e85be87aded632236239e94d98;
            if (!($dccf2f0f292208ba833261a4da87860d > $A34771e85be87aded632236239e94d98 || $dccf2f0f292208ba833261a4da87860d > $Ff876e96994aa5b09ce92e771efe2038 - 1 || $A34771e85be87aded632236239e94d98 >= $Ff876e96994aa5b09ce92e771efe2038)) {
                $start = $dccf2f0f292208ba833261a4da87860d;
                $ebe823668f9748302d3bd87782a71948 = $A34771e85be87aded632236239e94d98;
                $b362cb2e1492b66663cf3718328409ad = $ebe823668f9748302d3bd87782a71948 - $start + 1;
                fseek($Ab9f45b38498c3a010f3c4276ad5767c, $start);
                header("HTTP/1.1 206 Partial Content");
                f2934775915c6c890c49e769efac5cf8:
                header("Content-Range: bytes {$start}-{$ebe823668f9748302d3bd87782a71948}/{$Ff876e96994aa5b09ce92e771efe2038}");
                header("Content-Length: " . $b362cb2e1492b66663cf3718328409ad);
                $C7558f823ac28009bfd4730a82f1f01b = 8192;
                a28124da1815e0b87ed638f4cd963820:
                if (!(!feof($Ab9f45b38498c3a010f3c4276ad5767c) && ($f11bd4ac0a2baf9850141d4517561cff = ftell($Ab9f45b38498c3a010f3c4276ad5767c)) <= $ebe823668f9748302d3bd87782a71948)) {
                    fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                    die;
                }
                $B83f861d078c18d9643641c765cefee9 = stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, $C7558f823ac28009bfd4730a82f1f01b);
                echo $B83f861d078c18d9643641c765cefee9;
                goto a28124da1815e0b87ed638f4cd963820;
            }
            header("HTTP/1.1 416 Requested Range Not Satisfiable");
            header("Content-Range: bytes {$start}-{$ebe823668f9748302d3bd87782a71948}/{$Ff876e96994aa5b09ce92e771efe2038}");
            die;
        }
        header("HTTP/1.1 416 Requested Range Not Satisfiable");
        header("Content-Range: bytes {$start}-{$ebe823668f9748302d3bd87782a71948}/{$Ff876e96994aa5b09ce92e771efe2038}");
        die;
    }
    http_response_code(401);
    die;
}
http_response_code(401);
die;
