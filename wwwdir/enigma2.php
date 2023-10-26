<?php

require "./init.php";
class SimpleXMLExtended extends SimpleXMLElement {
    public function addCData($fca317707effbca84f074c293920f0f7) {
        $E21561cba90465c4a869c01eb89269bf = dom_import_simplexml($this);
        $B10ca538b01a4f983cf13350e70508a9 = $E21561cba90465c4a869c01eb89269bf->ownerDocument;
        $E21561cba90465c4a869c01eb89269bf->appendChild($B10ca538b01a4f983cf13350e70508a9->createCDATASection($fca317707effbca84f074c293920f0f7));
    }
}
$f0ac6ad2b40669833242a10c23cad2e0 = true;
if (!(!isset(A78bF8D35765bE2408c50712Ce7A43aD::$request["username"]) || !isset(A78BF8D35765Be2408c50712Ce7A43ad::$request["password"]))) {
    $f6806488699d3315dc5dc1e27a401b3e = a78bF8D35765be2408C50712ce7a43Ad::$request["username"];
    $password = A78bF8D35765be2408c50712cE7A43AD::$request["password"];
    $a28758c1ab974badfc544e11aaf19a57 = !empty(A78bF8D35765bE2408c50712ce7A43aD::$request["type"]) ? A78bF8D35765Be2408c50712ce7a43ad::$request["type"] : null;
    $f409603490270683e24dc87b262cfe7d = !empty(a78bf8D35765be2408C50712Ce7a43Ad::$request["cat_id"]) ? intval(a78BF8D35765Be2408C50712Ce7A43AD::$request["cat_id"]) : null;
    $D3a052197c7120cda8c89f487458f986 = !empty(A78BF8D35765be2408C50712Ce7A43ad::$request["scat_id"]) ? intval(A78Bf8D35765be2408C50712CE7A43Ad::$request["scat_id"]) : null;
    $b3671c2f351ad83d82df47bd43ef8768 = !empty(A78Bf8D35765bE2408c50712ce7a43aD::$request["series_id"]) ? intval(a78bf8d35765Be2408C50712ce7A43aD::$request["series_id"]) : null;
    $c0792eb00d656504ed969c0d4d84f7e3 = !empty(A78BF8D35765be2408C50712CE7a43Ad::$request["season"]) ? intval(a78Bf8d35765BE2408c50712ce7a43Ad::$request["season"]) : null;
    $e3539ad64f4d9fc6c2e465986c622369 = !empty($_SERVER["HTTP_HOST"]) ? "http://" . $_SERVER["HTTP_HOST"] . "/" : a78Bf8d35765be2408c50712ce7A43aD::$StreamingServers[SERVER_ID]["site_url"];
    ini_set("memory_limit", -1);
    if (!($D321370cfdc22e783dd897e5afed673e = Cd89785224751Cca8017139DaF9e891e::E5550592aa298dD1D5eE59cdCE063a12(null, $f6806488699d3315dc5dc1e27a401b3e, $password, true, true, false))) {
        goto eff04542170cb093c97fe2fdef7716c7;
    }
    $f0ac6ad2b40669833242a10c23cad2e0 = false;
    $F93ee1f4357cf2c3676871a1bc44af65 = b303f4b9bcfA8d2FfC2aE41c5d2aA387("live");
    $a646f0bc753ffe6eb4d18abe30bbcd66 = B303F4B9bCFA8D2FfC2ae41C5D2AA387("movie");
    $f24472413ed27fc2ffc06adda68c0806 = B303f4b9BCFa8d2ffc2aE41c5D2aa387("series");
    $aeb2c11d5afc757ad86eb60a666c0eee = array();
    $e109afabe8e0c1e646e3f9ec3cd2a7c9 = array();
    foreach ($D321370cfdc22e783dd897e5afed673e["channels"] as $a8df9f055e91a1e9240230b69af85555) {
        if ($a8df9f055e91a1e9240230b69af85555["live"] == 0) {
            $e109afabe8e0c1e646e3f9ec3cd2a7c9[] = $a8df9f055e91a1e9240230b69af85555;
            goto dad717a7453450ca870a6af60045cbe2;
        }
        $aeb2c11d5afc757ad86eb60a666c0eee[] = $a8df9f055e91a1e9240230b69af85555;
        dad717a7453450ca870a6af60045cbe2:
    }
    switch ($a28758c1ab974badfc544e11aaf19a57) {
        case "get_live_categories":
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "Live [ " . a78bf8d35765Be2408c50712cE7A43ad::$settings["bouquet_name"] . " ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "Live [ " . A78bf8D35765Be2408c50712Ce7a43aD::$settings["bouquet_name"] . " ]");
            $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("All"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("Live Streams Category [ ALL ]"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", 0);
            $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
            $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_live_streams&cat_id=0" . $d623cb8e6629e10f288da34e620b78b9["id"]);
            foreach ($F93ee1f4357cf2c3676871a1bc44af65 as $b10d12e0226d30efcf0ab5f1cb845a0a => $d623cb8e6629e10f288da34e620b78b9) {
                if (CD89785224751cCa8017139DAf9e891E::Bc358DB57d4903Bfddf6652560fae708($b10d12e0226d30efcf0ab5f1cb845a0a, $D321370cfdc22e783dd897e5afed673e["bouquet"])) {
                    $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode($d623cb8e6629e10f288da34e620b78b9["category_name"]));
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("Live Streams Category"));
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $d623cb8e6629e10f288da34e620b78b9["id"]);
                    $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
                    $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_live_streams&cat_id=" . $d623cb8e6629e10f288da34e620b78b9["id"]);
                    goto ccd9e188cc3bccec4c2c3303d83264c3;
                }
                ccd9e188cc3bccec4c2c3303d83264c3:
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        case "get_vod_categories":
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "Movie [ " . a78bF8D35765be2408C50712cE7A43ad::$settings["bouquet_name"] . " ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "Movie [ " . A78bF8D35765be2408c50712ce7a43ad::$settings["bouquet_name"] . " ]");
            $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("All"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("Movie Streams Category [ ALL ]"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", 0);
            $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
            $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_vod_streams&cat_id=0" . $d623cb8e6629e10f288da34e620b78b9["id"]);
            foreach ($a646f0bc753ffe6eb4d18abe30bbcd66 as $B51a7e937f03bda1f1e77ebf1bb5a96e => $d623cb8e6629e10f288da34e620b78b9) {
                if (cd89785224751ccA8017139daF9E891e::bC358dB57d4903BfDdf6652560fae708($B51a7e937f03bda1f1e77ebf1bb5a96e, $D321370cfdc22e783dd897e5afed673e["bouquet"])) {
                    $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode($d623cb8e6629e10f288da34e620b78b9["category_name"]));
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("Movie Streams Category"));
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $d623cb8e6629e10f288da34e620b78b9["id"]);
                    $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
                    $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_vod_streams&cat_id=" . $d623cb8e6629e10f288da34e620b78b9["id"]);
                    goto Db0dfd645bf58c763a5de108ef121d74;
                }
                Db0dfd645bf58c763a5de108ef121d74:
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        case "get_series_categories":
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "SubCategory [ " . A78BF8d35765be2408c50712cE7A43AD::$settings["bouquet_name"] . " ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "SubCategory [ " . a78Bf8D35765be2408c50712Ce7a43AD::$settings["bouquet_name"] . " ]");
            $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("All"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("TV Series Category [ ALL ]"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", 0);
            $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
            $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_series&cat_id=0" . $d623cb8e6629e10f288da34e620b78b9["id"]);
            foreach ($f24472413ed27fc2ffc06adda68c0806 as $B51a7e937f03bda1f1e77ebf1bb5a96e => $d623cb8e6629e10f288da34e620b78b9) {
                $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode($d623cb8e6629e10f288da34e620b78b9["category_name"]));
                $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("TV Series Category"));
                $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $d623cb8e6629e10f288da34e620b78b9["id"]);
                $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
                $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_series&cat_id=" . $d623cb8e6629e10f288da34e620b78b9["id"]);
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        case "get_series":
            if (!(isset($f409603490270683e24dc87b262cfe7d) || is_null($f409603490270683e24dc87b262cfe7d))) {
                goto e1c2007084a9c202ebef1adde9ad3636;
            }
            $f409603490270683e24dc87b262cfe7d = is_null($f409603490270683e24dc87b262cfe7d) ? 0 : $f409603490270683e24dc87b262cfe7d;
            $A27497ef3d4dad3da90c414c89f81615 = !empty($f24472413ed27fc2ffc06adda68c0806[$f409603490270683e24dc87b262cfe7d]) ? $f24472413ed27fc2ffc06adda68c0806[$f409603490270683e24dc87b262cfe7d]["category_name"] : "ALL";
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "TV Series [ {$A27497ef3d4dad3da90c414c89f81615} ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "TV Series [ {$A27497ef3d4dad3da90c414c89f81615} ]");
            $deff942ee62f1e5c2c16d11aee464729 = a78Bf8d35765Be2408c50712cE7A43ad::DCa7Aa6Db7c4ce371E41571a19bcE930();
            foreach ($deff942ee62f1e5c2c16d11aee464729 as $acb1d10773fb0d1b6ac8cf2c16ecf1b5 => $A0766c7ec9b7cbc336d730454514b34f) {
                if (in_array($acb1d10773fb0d1b6ac8cf2c16ecf1b5, $D321370cfdc22e783dd897e5afed673e["series_ids"])) {
                    if (!($f409603490270683e24dc87b262cfe7d != 0)) {
                        goto f735f8ec6726d3a03cdde977ff57547e;
                    }
                    if (!($f409603490270683e24dc87b262cfe7d != $A0766c7ec9b7cbc336d730454514b34f["category_id"])) {
                        f735f8ec6726d3a03cdde977ff57547e:
                        $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                        $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode($A0766c7ec9b7cbc336d730454514b34f["title"]));
                        $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", '');
                        $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $acb1d10773fb0d1b6ac8cf2c16ecf1b5);
                        $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
                        $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_seasons&series_id=" . $acb1d10773fb0d1b6ac8cf2c16ecf1b5);
                        goto Ed8a8fed51116deef51fba4ae58b3eee;
                    }
                    goto Edc907f4ce7d81ffbc13f1b13c4cf06a;
                }
                Ed8a8fed51116deef51fba4ae58b3eee:
                Edc907f4ce7d81ffbc13f1b13c4cf06a:
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            e1c2007084a9c202ebef1adde9ad3636:
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        case "get_seasons":
            if (!isset($b3671c2f351ad83d82df47bd43ef8768)) {
                goto Cb94d6608b319af7b86be2815e9eaa46;
            }
            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `series` WHERE `id` = '%d'", $b3671c2f351ad83d82df47bd43ef8768);
            $A0766c7ec9b7cbc336d730454514b34f = $f566700a43ee8e1f0412fe10fbdf03df->f1ed191D78470660EDff4A007696bc1f();
            $A27497ef3d4dad3da90c414c89f81615 = $A0766c7ec9b7cbc336d730454514b34f["title"];
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "TV Series [ {$A27497ef3d4dad3da90c414c89f81615} ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "TV Series [ {$A27497ef3d4dad3da90c414c89f81615} ]");
            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `series_episodes` t1 INNER JOIN `streams` t2 ON t2.id=t1.stream_id WHERE t1.series_id = '%d' ORDER BY t1.season_num ASC, t1.sort ASC", $b3671c2f351ad83d82df47bd43ef8768);
            $Cd4eabf7ecf553f46c17f0bd5a382c46 = $f566700a43ee8e1f0412fe10fbdf03df->C126fD559932F625CDF6098d86C63880(true, "season_num", false);
            foreach (array_keys($Cd4eabf7ecf553f46c17f0bd5a382c46) as $c59070c3eab15fea2abe4546ccf476de) {
                if (!($f409603490270683e24dc87b262cfe7d != 0)) {
                    goto B4e203eaa2d3d8f992a66a079585b414;
                }
                if (!($f409603490270683e24dc87b262cfe7d != $A0766c7ec9b7cbc336d730454514b34f["category_id"])) {
                    B4e203eaa2d3d8f992a66a079585b414:
                    $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("Season {$c59070c3eab15fea2abe4546ccf476de}"));
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", '');
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $c59070c3eab15fea2abe4546ccf476de);
                    $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
                    $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_series_streams&series_id=" . $b3671c2f351ad83d82df47bd43ef8768 . "&season=" . $c59070c3eab15fea2abe4546ccf476de);
                    goto a33a52469f9a1c5b6323dbbebe6eb521;
                }
                a33a52469f9a1c5b6323dbbebe6eb521:
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            Cb94d6608b319af7b86be2815e9eaa46:
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        case "get_series_streams":
            if (!(isset($b3671c2f351ad83d82df47bd43ef8768) && isset($c0792eb00d656504ed969c0d4d84f7e3) && in_array($b3671c2f351ad83d82df47bd43ef8768, $D321370cfdc22e783dd897e5afed673e["series_ids"]))) {
                goto D9dc3b438ba9b7cfe972d77243146aba;
            }
            $A0766c7ec9b7cbc336d730454514b34f = a78BF8D35765BE2408C50712Ce7A43Ad::DcA7Aa6Db7c4cE371E41571A19BCE930()[$b3671c2f351ad83d82df47bd43ef8768];
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "TV Series [ {$A0766c7ec9b7cbc336d730454514b34f["title"]} Season {$c0792eb00d656504ed969c0d4d84f7e3} ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "TV Series [ {$A0766c7ec9b7cbc336d730454514b34f["title"]} Season {$c0792eb00d656504ed969c0d4d84f7e3} ]");
            foreach ($A0766c7ec9b7cbc336d730454514b34f["series_data"][$c0792eb00d656504ed969c0d4d84f7e3] as $a14a8f906639aa7f5509518ff935b8f0) {
                $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("Episode " . sprintf("%02d", ++$e831c6d2f20288c01902323cccc3733a)));
                $d4c3c80b508f5d00d05316e7aa0858de = '';
                $Ef1890c26c22f7b0ebc5881c7a8f4728 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("desc_image");
                $Ef1890c26c22f7b0ebc5881c7a8f4728->addCData($A0766c7ec9b7cbc336d730454514b34f["cover"]);
                $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode($d4c3c80b508f5d00d05316e7aa0858de));
                $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $f409603490270683e24dc87b262cfe7d);
                $a1ef5a6a798dd2f8725ccec3f544f380 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("stream_url");
                if (!empty($a14a8f906639aa7f5509518ff935b8f0["stream_source"])) {
                    $F3803fa85b38b65447e6d438f8e9176a = json_decode($a14a8f906639aa7f5509518ff935b8f0["stream_source"], true)[0];
                    goto A8c90e787422bae5d3a95168fcf374af;
                }
                $F3803fa85b38b65447e6d438f8e9176a = $e3539ad64f4d9fc6c2e465986c622369 . "series/{$f6806488699d3315dc5dc1e27a401b3e}/{$password}/{$a14a8f906639aa7f5509518ff935b8f0["stream_id"]}." . dC53aE228Df72D4C140fda7fD5E7e0BE($a14a8f906639aa7f5509518ff935b8f0["target_container"]);
                A8c90e787422bae5d3a95168fcf374af:
                $a1ef5a6a798dd2f8725ccec3f544f380->addCData($F3803fa85b38b65447e6d438f8e9176a);
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            D9dc3b438ba9b7cfe972d77243146aba:
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        case "get_live_streams":
            if (!(isset($f409603490270683e24dc87b262cfe7d) || is_null($f409603490270683e24dc87b262cfe7d))) {
                goto ed2dc732ecb392145b1a156205a761f8;
            }
            $f409603490270683e24dc87b262cfe7d = is_null($f409603490270683e24dc87b262cfe7d) ? 0 : $f409603490270683e24dc87b262cfe7d;
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "Live [ " . A78BF8d35765BE2408C50712ce7a43Ad::$settings["bouquet_name"] . " ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "Live [ " . a78bF8d35765be2408C50712cE7A43ad::$settings["bouquet_name"] . " ]");
            foreach ($aeb2c11d5afc757ad86eb60a666c0eee as $a8df9f055e91a1e9240230b69af85555) {
                if (!($f409603490270683e24dc87b262cfe7d != 0)) {
                    goto ecceb6c69cb74dfc4cb513fe1036ab38;
                }
                if (!($f409603490270683e24dc87b262cfe7d != $a8df9f055e91a1e9240230b69af85555["category_id"])) {
                    ecceb6c69cb74dfc4cb513fe1036ab38:
                    $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT *,UNIX_TIMESTAMP(start) as start_timestamp, UNIX_TIMESTAMP(end) as stop_timestamp FROM `epg_data` WHERE `channel_id` = '%s' AND  `end` >= '%s' LIMIT 2", $a8df9f055e91a1e9240230b69af85555["channel_id"], date("Y-m-d H:i:00"));
                    $F8094cb3ced6b4e46ebea7b66bd0e870 = $f566700a43ee8e1f0412fe10fbdf03df->C126fD559932f625cdF6098D86C63880();
                    $d4c3c80b508f5d00d05316e7aa0858de = '';
                    $Ee61f16c515768a2a4ecfa726784a15f = '';
                    $C48e0083a9caa391609a3c645a2ec889 = 0;
                    foreach ($F8094cb3ced6b4e46ebea7b66bd0e870 as $c72d66b481d02f854f0bef67db92a547) {
                        $d4c3c80b508f5d00d05316e7aa0858de .= "[" . date("H:i", $c72d66b481d02f854f0bef67db92a547["start_timestamp"]) . "] " . base64_decode($c72d66b481d02f854f0bef67db92a547["title"]) . "\n( " . base64_decode($c72d66b481d02f854f0bef67db92a547["description"]) . ")\n";
                        if (!($C48e0083a9caa391609a3c645a2ec889 == 0)) {
                            goto ce2dde0536810e4d26eafa916fff59f4;
                        }
                        $Ee61f16c515768a2a4ecfa726784a15f = "[" . date("H:i", $c72d66b481d02f854f0bef67db92a547["start_timestamp"]) . " - " . date("H:i", $c72d66b481d02f854f0bef67db92a547["stop_timestamp"]) . "] + " . round(($c72d66b481d02f854f0bef67db92a547["stop_timestamp"] - time()) / 60, 1) . " min   " . base64_decode($c72d66b481d02f854f0bef67db92a547["title"]);
                        $C48e0083a9caa391609a3c645a2ec889++;
                        ce2dde0536810e4d26eafa916fff59f4:
                    }
                    $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode($a8df9f055e91a1e9240230b69af85555["stream_display_name"] . " " . $Ee61f16c515768a2a4ecfa726784a15f));
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode($d4c3c80b508f5d00d05316e7aa0858de));
                    $Ef1890c26c22f7b0ebc5881c7a8f4728 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("desc_image");
                    $Ef1890c26c22f7b0ebc5881c7a8f4728->addCData($a8df9f055e91a1e9240230b69af85555["stream_icon"]);
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $f409603490270683e24dc87b262cfe7d);
                    $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("stream_url");
                    if (!empty($a8df9f055e91a1e9240230b69af85555["stream_source"])) {
                        $F3803fa85b38b65447e6d438f8e9176a = json_decode($a8df9f055e91a1e9240230b69af85555["stream_source"], true)[0];
                        goto Da639d7299b9392bc7786ff173dae4f1;
                    }
                    $F3803fa85b38b65447e6d438f8e9176a = $e3539ad64f4d9fc6c2e465986c622369 . "live/{$f6806488699d3315dc5dc1e27a401b3e}/{$password}/{$a8df9f055e91a1e9240230b69af85555["id"]}.ts";
                    Da639d7299b9392bc7786ff173dae4f1:
                    $acfcb8efbada54f036f7bf632f1038a9->addCData($F3803fa85b38b65447e6d438f8e9176a);
                    goto a1f2063aa70a7cb8316f6385b5dc6ad9;
                }
                a1f2063aa70a7cb8316f6385b5dc6ad9:
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            ed2dc732ecb392145b1a156205a761f8:
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        case "get_vod_streams":
            if (!(isset($f409603490270683e24dc87b262cfe7d) || is_null($f409603490270683e24dc87b262cfe7d))) {
                goto be6e42346e122f4a74516f6769bac73a;
            }
            $f409603490270683e24dc87b262cfe7d = is_null($f409603490270683e24dc87b262cfe7d) ? 0 : $f409603490270683e24dc87b262cfe7d;
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", "Movie [ " . A78Bf8d35765be2408C50712ce7a43ad::$settings["bouquet_name"] . " ]");
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", "Movie [ " . a78bf8d35765BE2408c50712CE7A43ad::$settings["bouquet_name"] . " ]");
            foreach ($e109afabe8e0c1e646e3f9ec3cd2a7c9 as $a8df9f055e91a1e9240230b69af85555) {
                if (!($f409603490270683e24dc87b262cfe7d != 0)) {
                    goto d212070d748a7834f0c9f7a4af5ba3bb;
                }
                if (!($f409603490270683e24dc87b262cfe7d != $a8df9f055e91a1e9240230b69af85555["category_id"])) {
                    d212070d748a7834f0c9f7a4af5ba3bb:
                    $movie_properties = A78Bf8D35765be2408c50712ce7A43ad::cAdEB9125B2e81b183688842C5AC3AD7($a8df9f055e91a1e9240230b69af85555["id"]);
                    $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode($a8df9f055e91a1e9240230b69af85555["stream_display_name"]));
                    $d4c3c80b508f5d00d05316e7aa0858de = '';
                    if (!$movie_properties) {
                        goto bf223dd4c648b1edb9a3a16c38a561f5;
                    }
                    foreach ($movie_properties as $E7cca48cfca85fc445419a32d7d8f973 => $eb98f53b15ea5d816e72b353cc6c3326) {
                        if (!($E7cca48cfca85fc445419a32d7d8f973 == "movie_image")) {
                            $d4c3c80b508f5d00d05316e7aa0858de .= strtoupper($E7cca48cfca85fc445419a32d7d8f973) . ": " . $eb98f53b15ea5d816e72b353cc6c3326 . "\n";
                            goto e63087dd3e7a841631f7ae2eeb177d3b;
                        }
                        e63087dd3e7a841631f7ae2eeb177d3b:
                    }
                    bf223dd4c648b1edb9a3a16c38a561f5:
                    $Ef1890c26c22f7b0ebc5881c7a8f4728 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("desc_image");
                    $Ef1890c26c22f7b0ebc5881c7a8f4728->addCData($movie_properties["movie_image"]);
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode($d4c3c80b508f5d00d05316e7aa0858de));
                    $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", $f409603490270683e24dc87b262cfe7d);
                    $a1ef5a6a798dd2f8725ccec3f544f380 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("stream_url");
                    if (!empty($a8df9f055e91a1e9240230b69af85555["stream_source"])) {
                        $F3803fa85b38b65447e6d438f8e9176a = json_decode($a8df9f055e91a1e9240230b69af85555["stream_source"], true)[0];
                        goto c68c0b008ee00ad68eac384a3fc49849;
                    }
                    $F3803fa85b38b65447e6d438f8e9176a = $e3539ad64f4d9fc6c2e465986c622369 . "movie/{$f6806488699d3315dc5dc1e27a401b3e}/{$password}/{$a8df9f055e91a1e9240230b69af85555["id"]}." . dc53AE228df72D4C140FdA7Fd5E7e0bE($a8df9f055e91a1e9240230b69af85555["target_container"]);
                    c68c0b008ee00ad68eac384a3fc49849:
                    $a1ef5a6a798dd2f8725ccec3f544f380->addCData($F3803fa85b38b65447e6d438f8e9176a);
                    goto F12a8b71d65ccef2f3caaace581e405d;
                }
                F12a8b71d65ccef2f3caaace581e405d:
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
            be6e42346e122f4a74516f6769bac73a:
            goto C30773cd31621ab54fa8976b5b0ed2b8;
        default:
            $a41f6a5b2ce6655f27b7747349ad1f33 = new SimpleXMLExtended("<items/>");
            $a41f6a5b2ce6655f27b7747349ad1f33->addChild("playlist_name", a78Bf8d35765bE2408c50712ce7A43Ad::$settings["bouquet_name"]);
            $d623cb8e6629e10f288da34e620b78b9 = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("category");
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_id", 1);
            $d623cb8e6629e10f288da34e620b78b9->addChild("category_title", A78bf8D35765Be2408c50712ce7A43Ad::$settings["bouquet_name"]);
            if (empty($aeb2c11d5afc757ad86eb60a666c0eee)) {
                goto e0d06bd5829f3f676c34303823e6e43b;
            }
            $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("Live Streams"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("Live Streams Category"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", 0);
            $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
            $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_live_categories");
            e0d06bd5829f3f676c34303823e6e43b:
            if (empty($e109afabe8e0c1e646e3f9ec3cd2a7c9)) {
                goto a77d83d9e239b61c1af2a37dd2f4cce2;
            }
            $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("Vod"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("Video On Demand Category"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", 1);
            $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
            $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_vod_categories");
            a77d83d9e239b61c1af2a37dd2f4cce2:
            $B6fc8577128465b7a7ca16798a93f3cd = $a41f6a5b2ce6655f27b7747349ad1f33->addChild("channel");
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("title", base64_encode("TV Series"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("description", base64_encode("TV Series Category"));
            $B6fc8577128465b7a7ca16798a93f3cd->addChild("category_id", 2);
            $acfcb8efbada54f036f7bf632f1038a9 = $B6fc8577128465b7a7ca16798a93f3cd->addChild("playlist_url");
            $acfcb8efbada54f036f7bf632f1038a9->addCData($e3539ad64f4d9fc6c2e465986c622369 . "enigma2.php?username={$f6806488699d3315dc5dc1e27a401b3e}&password={$password}&type=get_series_categories");
            header("Content-Type: application/xml; charset=utf-8");
            echo $a41f6a5b2ce6655f27b7747349ad1f33->asXML();
    }
    C30773cd31621ab54fa8976b5b0ed2b8:
    eff04542170cb093c97fe2fdef7716c7:
    if (!$f0ac6ad2b40669833242a10c23cad2e0) {
        goto efb007882c5d3ecc647c0bc83bb4a7a5;
    }
    http_response_code(401);
    d9f93B7C177e377d0BBFE315eAeaE505();
    efb007882c5d3ecc647c0bc83bb4a7a5:
    // [PHPDeobfuscator] Implied script end
    return;
}
die("Missing parameters.");
