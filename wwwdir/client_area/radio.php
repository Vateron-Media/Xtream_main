<?php

require "../init.php";
session_start();
if (!(empty($_SESSION["client_loggedin"]) && $_SESSION["client_loggedin"] != true && empty($_SESSION["cl_data"]))) {
    $Bf4bb0ad11102aaccbf77b6cdc1fd66f = ipTV_streaming::getUserInfo(null, $_SESSION["cl_data"]["username"], $_SESSION["cl_data"]["password"], true, true, true, array("radio_streams"));
    $afdd6246d0a110a7f7c2599f764bb8e9 = array();
    $Bbb2d0a1dd6b9567deea1c5361ce620f = array();
    $D465fc5085f41251c6fa7c77b8333b0f = array();
    $B9756c2ca174cd617ad8d0ed4704e5c6 = array();
    $b9aa22d3a119ac1ac77eb3f04654aed8 = '';
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
    </head>

    <body>
        <!-- header -->
        <div class="header">

            <div class="logo"></div>

            <div class="button_Live">
                <img src="images/live_btn.png" onmouseover="this.src='images/live_btn_hover.png'"
                    onmouseout="this.src='images/live_btn.png'" onClick="parent.location='live.php'" />
            </div>

            <div class="button_Movies">
                <img src="images/videos_btn.png" onmouseover="this.src='images/videos_btn_hover.png'"
                    onmouseout="this.src='images/videos_btn.png'" onClick="parent.location='vod.php'" />
            </div>
            <div class="button_Radio">
                <img src='images/radio_btn_hover.png' />
            </div>
            <div class="User"><img src="images/user_icon.png"><a style="margin-left:10px; color:#C60;"><?php
            echo $_SESSION["cl_data"]["username"];
            ?>
                </a>
                <div style="width:3px; height:103px;position:absolute; margin-top:-40px; margin-left:-10px;"><img
                        src="images/Header_default_line.png"></div>
                <div style="width:3px; height:103px;position:absolute; margin-top:-40px; margin-left:140px;"><img
                        src="images/Header_default_line.png"></div>
                <ul>
                    <li><a style=" color:#c60; font-size:12px;">Expire Date:</a><a
                            style="margin-left:10px; color:#fff; font-size:12px;"><?php
                            if (empty($_SESSION["cl_data"]["exp_date"])) {
                                echo "Unlimited";
                                goto d14c1ed7f0524e3b5ea77985bb6967ab;
                            }
                            echo date("d/m/Y H:i", $_SESSION["cl_data"]["exp_date"]);
                            d14c1ed7f0524e3b5ea77985bb6967ab:
                            ?>
                        </a></li>
                    <li style="margin-left:30px;"><img src="images/logout_btn.png"
                            onmouseover="this.src='images/logout_btn_hover.png'"
                            onmouseout="this.src='images/logout_btn.png'"
                            onClick="parent.location='index.php?action=logout'" /></li>

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
                        </ul>
                        <ul class='hidden-links hidden'></ul>
                    </nav>
                    <!--channels-->
                    <radio>
                        <?php
                        foreach ($Bf4bb0ad11102aaccbf77b6cdc1fd66f["channels"] as $E4166ae9900ab98d72b3688948a70564) {
                            $e3539ad64f4d9fc6c2e465986c622369 = ipTV_lib::$Servers[SERVER_ID]["site_url"] . "live/{$_SESSION["cl_data"]["username"]}/{$_SESSION["cl_data"]["password"]}/{$E4166ae9900ab98d72b3688948a70564["id"]}.ts";
                            echo "<div class=\"Radio_Frame\">\n            \t\t<div class=\"Radio_Icon\">";
                            if (!empty($E4166ae9900ab98d72b3688948a70564["stream_icon"]) && @getimagesize($E4166ae9900ab98d72b3688948a70564["stream_icon"])) {
                                echo "<img src=\"" . $E4166ae9900ab98d72b3688948a70564["stream_icon"] . "\"></div>";
                                goto F675a02ca228ffdbdb88d4986e5c4ef1;
                            }
                            echo "<img width=\"100\" height=\"100\" src=\"images/no_radio.png\"></div>";
                            F675a02ca228ffdbdb88d4986e5c4ef1:
                            echo " <div class=\"Radio_Line\"></div><div class=\"Radio_Live_Now\"></br><p>" . $E4166ae9900ab98d72b3688948a70564["stream_display_name"] . "</div><div class=\"Radio_Line\"></div><a href=\"" . $e3539ad64f4d9fc6c2e465986c622369 . "\" </a><div class=\"Play_Radio_Button\"></div></div>";
                        }
                        ?>
            </div>
            </center>
        </div>
        </div>
        </radio>

        <!--/channels-->

        <!--footer-->
        </br></br></br>
        <div class="footer"><a><img style="float:right;" src="images/footer.png"></a>
        </div>

        <!--/footer-->
    </body>

    </html>
    <?php
    // [PHPDeobfuscator] Implied script end
    return;
}
header("Location: index.php");
die;
