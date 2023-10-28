<?php

require "../init.php";
session_start();
if (!(empty($_SESSION["client_loggedin"]) && $_SESSION["client_loggedin"] != true && empty($_SESSION["cl_data"]))) {
    $Bf4bb0ad11102aaccbf77b6cdc1fd66f = ipTV_streaming::GetUserInfo(null, $_SESSION["cl_data"]["username"], $_SESSION["cl_data"]["password"], true, true, true, array("live", "created_live"));
    $afdd6246d0a110a7f7c2599f764bb8e9 = array();
    $Bbb2d0a1dd6b9567deea1c5361ce620f = array();
    $D465fc5085f41251c6fa7c77b8333b0f = array();
    $B9756c2ca174cd617ad8d0ed4704e5c6 = array();
    $b9aa22d3a119ac1ac77eb3f04654aed8 = '';
    foreach ($Bf4bb0ad11102aaccbf77b6cdc1fd66f["channels"] as $aeb2c11d5afc757ad86eb60a666c0eee) {
        $B2fc0a022f457f988075bf6e0b5c504b = $aeb2c11d5afc757ad86eb60a666c0eee["category_name"] == null ? "Uncategorized" : $aeb2c11d5afc757ad86eb60a666c0eee["category_name"];
        $afdd6246d0a110a7f7c2599f764bb8e9[$B2fc0a022f457f988075bf6e0b5c504b][] = $aeb2c11d5afc757ad86eb60a666c0eee;
        if (in_array($aeb2c11d5afc757ad86eb60a666c0eee["category_id"], $Bbb2d0a1dd6b9567deea1c5361ce620f)) {
            goto a20f0af027dfb2475e1ba31ba5a419d7;
        }
        $Bbb2d0a1dd6b9567deea1c5361ce620f[$B2fc0a022f457f988075bf6e0b5c504b] = $aeb2c11d5afc757ad86eb60a666c0eee["category_id"];
        a20f0af027dfb2475e1ba31ba5a419d7:
    }
    $be0be3a45f977b44d94af94640af3c0f = isMobileDevice();
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <title>Live_TV</title>
        <link rel="stylesheet" href="css/main.css" type="text/css" />
        <link rel="stylesheet" type="text/css" href="css/greedynav.css">
        <link rel="stylesheet" type="text/css" href="css/reset.min.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <link rel="stylesheet" href="css/jquery.mobile.min.css" />
        <script src="js/jquery.mobile.min.js"></script>




        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/greedynav.js"></script>
        <script>
            function post(path, params, method) {
                method = method || "post";
                var form = document.createElement("form");
                form.setAttribute("method", method);
                form.setAttribute("action", path);

                for (var key in params) {
                    if (params.hasOwnProperty(key)) {
                        var hiddenField = document.createElement("input");
                        hiddenField.setAttribute("type", "hidden");
                        hiddenField.setAttribute("name", key);
                        hiddenField.setAttribute("value", params[key]);

                        form.appendChild(hiddenField);
                    }
                }

                document.body.appendChild(form);
                form.submit();
            }
        </script>
    </head>

    <body>
        <!-- header -->

        <div class="header">

            <div class="logo"></div>

            <div class="button_Live">
                <img src="images/live_btn_hover.png" />
            </div>

            <div class="button_Movies">
                <img src="images/videos_btn.png" onmouseover="this.src='images/videos_btn_hover.png'" onmouseout="this.src='images/videos_btn.png'" onClick="parent.location='vod.php'" />
            </div>
            <div class="button_Radio">
                <img src="images/radio_btn.png" onmouseover="this.src='images/radio_btn_hover.png'" onmouseout="this.src='images/radio_btn.png'" onClick="parent.location='radio.php'" />
            </div>
            <div class="User"><img src="images/user_icon.png"><a style="margin-left:10px; color:#C60;"><?php
                                                                                                        echo $_SESSION["cl_data"]["username"];
                                                                                                        ?>
                </a>
                <div style="width:3px; height:103px;position:absolute; margin-top:-40px; margin-left:-10px;"><img src="images/Header_default_line.png"></div>
                <div style="width:3px; height:103px;position:absolute; margin-top:-40px; margin-left:140px;"><img src="images/Header_default_line.png"></div>
                <ul>
                    <li><a style=" color:#c60; font-size:12px;">Expire Date:</a><a style="margin-left:10px; color:#fff; font-size:12px;"><?php
                                                                                                                                            if (empty($_SESSION["cl_data"]["exp_date"])) {
                                                                                                                                                echo "Unlimited";
                                                                                                                                                goto Ec9abc252e5f0f74eaf017f72638eef0;
                                                                                                                                            }
                                                                                                                                            echo date("d/m/Y H:i", $_SESSION["cl_data"]["exp_date"]);
                                                                                                                                            Ec9abc252e5f0f74eaf017f72638eef0:
                                                                                                                                            ?>
                        </a></li>
                    <li style="margin-left:30px;"><img src="images/logout_btn.png" onmouseover="this.src='images/logout_btn_hover.png'" onmouseout="this.src='images/logout_btn.png'" onClick="parent.location='index.php?action=logout'" /></li>

            </div>
        </div>
        <!-- /header -->
        <div class="wrapper">
            <div data-role="listview" data-inset="true" data-filter="true" data-filter-placeholder="search">
                <center>
                    <nav class='greedy-nav'>
                        <button>
                            <div class="hamburger"></div>
                        </button>
                        <ul class='visible-links'>
                            <?php
                            if (isset($_GET["cat_id"]) && is_numeric($_GET["cat_id"])) {
                                echo "<li><a href=\"#\" onClick=\"window.location='live.php'\">All</a></li>";
                                $b9aa22d3a119ac1ac77eb3f04654aed8 = intval($_GET["cat_id"]);
                                goto b2cdd140880216af1c71ef1f381bc70a;
                            }
                            echo "<li><a href=\"#\" style=\"color:#F60\" onClick=\"window.location='live.php'\">All</a></li>";
                            b2cdd140880216af1c71ef1f381bc70a:
                            foreach ($afdd6246d0a110a7f7c2599f764bb8e9 as $c80b1640ee0f6de06abc98f8d4d277ab => $B7b713518c0441856fbd05133143db63) {
                                $f409603490270683e24dc87b262cfe7d = empty($Bbb2d0a1dd6b9567deea1c5361ce620f[$c80b1640ee0f6de06abc98f8d4d277ab]) ? 0 : $Bbb2d0a1dd6b9567deea1c5361ce620f[$c80b1640ee0f6de06abc98f8d4d277ab];
                                $c7a6c17fd39e43d1cb5987f0e4979b87 = count($B7b713518c0441856fbd05133143db63);
                                if ("{$f409603490270683e24dc87b262cfe7d}" === "{$b9aa22d3a119ac1ac77eb3f04654aed8}") {
                                    echo "<li><a href=\"#\" style=\"color:#F60\" onClick=\"window.location='live.php?cat_id={$f409603490270683e24dc87b262cfe7d}'\">{$c80b1640ee0f6de06abc98f8d4d277ab} ( {$c7a6c17fd39e43d1cb5987f0e4979b87} )</a></li>";
                                    goto e4cb004391969426a338be91f449f117;
                                }
                                echo "<li><a href=\"#\" onClick=\"window.location='live.php?cat_id={$f409603490270683e24dc87b262cfe7d}'\">{$c80b1640ee0f6de06abc98f8d4d277ab} ( {$c7a6c17fd39e43d1cb5987f0e4979b87} )</a></li>";
                                e4cb004391969426a338be91f449f117:
                                foreach ($B7b713518c0441856fbd05133143db63 as $A66766c8194fa7aac4791468fd0c7eb6) {
                                    if (!(!empty($b9aa22d3a119ac1ac77eb3f04654aed8) && $f409603490270683e24dc87b262cfe7d != $b9aa22d3a119ac1ac77eb3f04654aed8)) {
                                        if (!($b9aa22d3a119ac1ac77eb3f04654aed8 === 0 && $f409603490270683e24dc87b262cfe7d != $b9aa22d3a119ac1ac77eb3f04654aed8)) {
                                            $D465fc5085f41251c6fa7c77b8333b0f[] = $A66766c8194fa7aac4791468fd0c7eb6;
                                            if (in_array($A66766c8194fa7aac4791468fd0c7eb6["channel_id"], $B9756c2ca174cd617ad8d0ed4704e5c6)) {
                                                goto Bfdf85cf85c2cd9492134edb75f90548;
                                            }
                                            $B9756c2ca174cd617ad8d0ed4704e5c6[] = $A66766c8194fa7aac4791468fd0c7eb6["channel_id"];
                                            Bfdf85cf85c2cd9492134edb75f90548:
                                            goto cffd37d05783ae25bea9d0001d277667;
                                        }
                                        goto Ae0c11c95f811b40a3c5e230fb7d4a0b;
                                    }
                                    cffd37d05783ae25bea9d0001d277667:
                                    Ae0c11c95f811b40a3c5e230fb7d4a0b:
                                }
                            }
                            ?>
                        </ul>
                        <ul class='hidden-links hidden'></ul>
                    </nav>
                </center>
                </br>
                <div class="live_now">
                    <a style="color:#FFF; font-size:15px; font-family:Tahoma, Geneva, sans-serif; margin-left:120px; top:5px; position:relative; font-style:italic;">Live Now...</a>
                </div>
                <div class="coming_next">
                    <a style="color:#252525; font-size:15px; font-family:Tahoma, Geneva, sans-serif; margin-left:45%; top:5px; position:relative; font-style:italic;">Coming Next...</a>

                </div>
                <!--channels-->
                <?php
                $B9756c2ca174cd617ad8d0ed4704e5c6 = "'" . implode("','", array_unique($B9756c2ca174cd617ad8d0ed4704e5c6)) . "'";
                $ipTV_db->query("SELECT *,UNIX_TIMESTAMP(start) as start_timestamp,UNIX_TIMESTAMP(end) as stop_timestamp from `epg_data` WHERE `end` >= '%s' AND `end` <= '%s' AND channel_id IN ({$B9756c2ca174cd617ad8d0ed4704e5c6})", date("Y-m-d H:i:00"), date("Y-m-d H:i:00", strtotime("+12 hours")));
                $F8094cb3ced6b4e46ebea7b66bd0e870 = $ipTV_db->get_rows(true, "channel_id", false);
                $C48e0083a9caa391609a3c645a2ec889 = 0;
                if (ipTV_lib::$settings["client_area_plugin"] == "vlc") {
                    $B8bfd81380fdac631e9d094da9da7ee1 = "ts";
                    goto Cdd2811d802daa8b3b3551a699b3b890;
                }
                $B8bfd81380fdac631e9d094da9da7ee1 = "m3u8";
                Cdd2811d802daa8b3b3551a699b3b890:
                foreach ($D465fc5085f41251c6fa7c77b8333b0f as $c3a18c26bfa971a25d2e6ada870ff735) {
                    if ($C48e0083a9caa391609a3c645a2ec889 === 0) {
                        echo "<center><div class=\"channel_Frame\"><div class=\"channel_Icon\">";
                        goto A5e7bec3acde53467a1f7d0311596392;
                    }
                    echo "<center><div class=\"channel_Frame\"><div style=\"margin-top:15px;\" class=\"channel_Icon\">";
                    A5e7bec3acde53467a1f7d0311596392:
                    echo "<p>" . cEd7bA3E6F658d0C20eb2C1E803b6003($c3a18c26bfa971a25d2e6ada870ff735["stream_display_name"], "15") . "</p>";
                    if (!empty($c3a18c26bfa971a25d2e6ada870ff735["stream_icon"])) {
                        echo "<img src=\"" . $c3a18c26bfa971a25d2e6ada870ff735["stream_icon"] . "\" width=\"100\" height=\"40\"></div>";
                        goto Dc9ea6f0b896fcb5753da86f4cd86ff8;
                    }
                    echo "</div>";
                    Dc9ea6f0b896fcb5753da86f4cd86ff8:
                    $Af236a5462da6c610990628f594f801e = 0;
                    if (!empty($F8094cb3ced6b4e46ebea7b66bd0e870[$c3a18c26bfa971a25d2e6ada870ff735["channel_id"]])) {
                        goto d4dac59fb3f7384deef8b104782422cc;
                    }
                    $e3539ad64f4d9fc6c2e465986c622369 = ipTV_lib::$StreamingServers[SERVER_ID]["site_url"] . "live/{$_SESSION["cl_data"]["username"]}/{$_SESSION["cl_data"]["password"]}/{$c3a18c26bfa971a25d2e6ada870ff735["id"]}." . $B8bfd81380fdac631e9d094da9da7ee1;
                    $B64c0bedcb468022a4c21a174e659580 = !$be0be3a45f977b44d94af94640af3c0f ? "post('player.php',{link:'{$e3539ad64f4d9fc6c2e465986c622369}',display_name:'{$c3a18c26bfa971a25d2e6ada870ff735["stream_display_name"]}'});" : "window.location.href='{$e3539ad64f4d9fc6c2e465986c622369}'";
                    echo "<div class=\"channel_Line\"></div>\n                   <div class=\"channel_Live_Now\"></br><p>No Data</p><p><br/></p>\n                   <div class=\"Play_Live_Button\"  onclick=\"{$B64c0bedcb468022a4c21a174e659580}\">\n                   </div></div>\n                   <div class=\"channel_Line\"></div>";
                    echo "<div class=\"channel_Coming_Next\"></br><p>No Data</p><p><br/></p></div><div class=\"channel_Line\"></div>";
                    echo "<div class=\"channel_Coming_Next\"></br><p>No Data</p><p><br/></p></div><div class=\"channel_Line\"></div>";
                    echo "<div class=\"channel_Coming_Next\"></br><p>No Data</p><p><br/></p></div>";
                    d4dac59fb3f7384deef8b104782422cc:
                    foreach ($F8094cb3ced6b4e46ebea7b66bd0e870[$c3a18c26bfa971a25d2e6ada870ff735["channel_id"]] as $af3d6b1e7696873385892872e750dd94) {
                        if (!($Af236a5462da6c610990628f594f801e > 3)) {
                            if ($Af236a5462da6c610990628f594f801e === 0) {
                                $e3539ad64f4d9fc6c2e465986c622369 = ipTV_lib::$StreamingServers[SERVER_ID]["site_url"] . "live/{$_SESSION["cl_data"]["username"]}/{$_SESSION["cl_data"]["password"]}/{$c3a18c26bfa971a25d2e6ada870ff735["id"]}." . $B8bfd81380fdac631e9d094da9da7ee1;
                                $B64c0bedcb468022a4c21a174e659580 = !$be0be3a45f977b44d94af94640af3c0f ? "post('player.php',{link:'{$e3539ad64f4d9fc6c2e465986c622369}',display_name:'{$c3a18c26bfa971a25d2e6ada870ff735["stream_display_name"]}'});" : "window.location.href='{$e3539ad64f4d9fc6c2e465986c622369}'";
                                echo "<div class=\"channel_Line\"></div>\n                   <div class=\"channel_Live_Now\"><p style=\"margin-top:10px;\">" . date("H:i", $af3d6b1e7696873385892872e750dd94["start_timestamp"]) . " - " . date("H:i", $af3d6b1e7696873385892872e750dd94["stop_timestamp"]) . "</p><now><p>" . base64_decode($af3d6b1e7696873385892872e750dd94["title"]) . "</p>\n                   <div class=\"Play_Live_Button\"  onclick=\"{$B64c0bedcb468022a4c21a174e659580}\">\n                   </div></div>\n                   <div class=\"channel_Line\"></div></now>";
                                goto Ed86656f7afcc6bd26252e78add4bca4;
                            }
                            echo "<div class=\"channel_Coming_Next\"><p style=\"margin-top:10px;\">" . date("H:i", $af3d6b1e7696873385892872e750dd94["start_timestamp"]) . " - " . date("H:i", $af3d6b1e7696873385892872e750dd94["stop_timestamp"]) . "</p><next><p>" . base64_decode($af3d6b1e7696873385892872e750dd94["title"]) . "</p></div></next>";
                            Ed86656f7afcc6bd26252e78add4bca4:
                            if (!($Af236a5462da6c610990628f594f801e !== 3)) {
                                goto c55da838b3f656646af2cbfdb0b77189;
                            }
                            echo "<div class=\"channel_Line\"></div>";
                            c55da838b3f656646af2cbfdb0b77189:
                            ++$Af236a5462da6c610990628f594f801e;
                        }
                        goto Fbd86370e01644eb64cc7e27b1f07fdf;
                    }
                    Fbd86370e01644eb64cc7e27b1f07fdf:
                    echo "</div></center>";
                    ++$C48e0083a9caa391609a3c645a2ec889;
                }
                ?>
            </div>
        </div>




        <!--/channels-->

        <!--footer-->
        </br></br></br>
        <div class="footer"><a><img style="float:right;" src="images/footer.png"></a>
        </div>

        <!--/footer-->
    </body>

    </html>

<?php
    function CEd7Ba3E6f658d0c20Eb2C1E803b6003($F999d6c638356ee8a5d971e3eabf821a, $b362cb2e1492b66663cf3718328409ad) {
        if (strlen($F999d6c638356ee8a5d971e3eabf821a) > $b362cb2e1492b66663cf3718328409ad) {
            return substr($F999d6c638356ee8a5d971e3eabf821a, 0, -$b362cb2e1492b66663cf3718328409ad) . "<br/>" . substr($F999d6c638356ee8a5d971e3eabf821a, -$b362cb2e1492b66663cf3718328409ad);
        }
        return $F999d6c638356ee8a5d971e3eabf821a;
    }
    // [PHPDeobfuscator] Implied script end
    return;
}
header("Location: index.php");
die;
