<?php

require "../init.php";
session_start();
if (!(empty($_SESSION["client_loggedin"]) && $_SESSION["client_loggedin"] != true && empty($_SESSION["cl_data"]))) {
  $Bf4bb0ad11102aaccbf77b6cdc1fd66f = ipTV_streaming::GetUserInfo(null, $_SESSION["cl_data"]["username"], $_SESSION["cl_data"]["password"], true, true, true, array("movie"));
  $afdd6246d0a110a7f7c2599f764bb8e9 = array();
  $Bbb2d0a1dd6b9567deea1c5361ce620f = array();
  $D465fc5085f41251c6fa7c77b8333b0f = array();
  $B9756c2ca174cd617ad8d0ed4704e5c6 = array();
  $b9aa22d3a119ac1ac77eb3f04654aed8 = '';
  foreach ($Bf4bb0ad11102aaccbf77b6cdc1fd66f["channels"] as $Af57be96316de3c2ab0dcc47177c9de2) {
    $bb92a0ec481b88b8319214e164a636dc = $Af57be96316de3c2ab0dcc47177c9de2["category_name"] == null ? "Uncategorized" : $Af57be96316de3c2ab0dcc47177c9de2["category_name"];
    $afdd6246d0a110a7f7c2599f764bb8e9[$bb92a0ec481b88b8319214e164a636dc][] = $Af57be96316de3c2ab0dcc47177c9de2;
    if (in_array($Af57be96316de3c2ab0dcc47177c9de2["category_id"], $Bbb2d0a1dd6b9567deea1c5361ce620f)) {
      goto cd369a50ce5886c3afae9af747bba048;
    }
    $Bbb2d0a1dd6b9567deea1c5361ce620f[$bb92a0ec481b88b8319214e164a636dc] = $Af57be96316de3c2ab0dcc47177c9de2["category_id"];
    cd369a50ce5886c3afae9af747bba048:
  }
  $Bbb2d0a1dd6b9567deea1c5361ce620f["All"] = 0;
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
    <link href="https://noraesae.github.io/perfect-scrollbar/perfect-scrollbar.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/jquery.mobile.min.css" />

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <script src="js/jquery.mobile.min.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/greedynav.js"></script>
    <script src="https://noraesae.github.io/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://noraesae.github.io/perfect-scrollbar/bootstrap.min.js"></script>
    <script src="https://noraesae.github.io/perfect-scrollbar/prettify.js"></script>
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
    <script>
      $(function() {
        prettyPrint();
      });
    </script>

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
        <img src="images/videos_btn_hover.png" />
      </div>
      <div class="button_Radio">
        <img src="images/radio_btn.png" onmouseover="this.src='images/radio_btn_hover.png'"
          onmouseout="this.src='images/radio_btn.png'" onClick="parent.location='radio.php'" />
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
                                                                      goto bd170100fbf9072548e9b06a2675252a;
                                                                    }
                                                                    echo date("d/m/Y H:i", $_SESSION["cl_data"]["exp_date"]);
                                                                    bd170100fbf9072548e9b06a2675252a:
                                                                    ?>
            </a></li>
          <li style="margin-left:30px;"><img src="images/logout_btn.png"
              onmouseover="this.src='images/logout_btn_hover.png'" onmouseout="this.src='images/logout_btn.png'"
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
              <?php
              $b9aa22d3a119ac1ac77eb3f04654aed8 = false;
              if (!(isset($_GET["cat_id"]) && is_numeric($_GET["cat_id"]))) {
                goto b81a876cfd48097b0a6853ecd3fc01e3;
              }
              $b9aa22d3a119ac1ac77eb3f04654aed8 = intval($_GET["cat_id"]);
              b81a876cfd48097b0a6853ecd3fc01e3:
              $afdd6246d0a110a7f7c2599f764bb8e9["All"] = array();
              foreach ($afdd6246d0a110a7f7c2599f764bb8e9 as $c80b1640ee0f6de06abc98f8d4d277ab => $B7b713518c0441856fbd05133143db63) {
                $f409603490270683e24dc87b262cfe7d = empty($Bbb2d0a1dd6b9567deea1c5361ce620f[$c80b1640ee0f6de06abc98f8d4d277ab]) ? 0 : $Bbb2d0a1dd6b9567deea1c5361ce620f[$c80b1640ee0f6de06abc98f8d4d277ab];
                $c7a6c17fd39e43d1cb5987f0e4979b87 = count($B7b713518c0441856fbd05133143db63);
                if ("{$f409603490270683e24dc87b262cfe7d}" === "{$b9aa22d3a119ac1ac77eb3f04654aed8}") {
                  echo "<li><a href=\"#\" style=\"color:#F60\" onClick=\"window.location='vod.php?cat_id={$f409603490270683e24dc87b262cfe7d}'\">{$c80b1640ee0f6de06abc98f8d4d277ab} ( {$c7a6c17fd39e43d1cb5987f0e4979b87} )</a></li>";
                  goto b5c3dd6bdf971d66d6fb3d1f98536f6c;
                }
                echo "<li><a href=\"#\" onClick=\"window.location='vod.php?cat_id={$f409603490270683e24dc87b262cfe7d}'\">{$c80b1640ee0f6de06abc98f8d4d277ab} ( {$c7a6c17fd39e43d1cb5987f0e4979b87} )</a></li>";
                b5c3dd6bdf971d66d6fb3d1f98536f6c:
                foreach ($B7b713518c0441856fbd05133143db63 as $A66766c8194fa7aac4791468fd0c7eb6) {
                  if (!(!empty($b9aa22d3a119ac1ac77eb3f04654aed8) && $f409603490270683e24dc87b262cfe7d != $b9aa22d3a119ac1ac77eb3f04654aed8)) {
                    if (!($b9aa22d3a119ac1ac77eb3f04654aed8 === 0 && $f409603490270683e24dc87b262cfe7d != $b9aa22d3a119ac1ac77eb3f04654aed8)) {
                      $D465fc5085f41251c6fa7c77b8333b0f[] = $A66766c8194fa7aac4791468fd0c7eb6;
                      if (in_array($A66766c8194fa7aac4791468fd0c7eb6["channel_id"], $B9756c2ca174cd617ad8d0ed4704e5c6)) {
                        goto E5d43d3ab9ea99cb10a2d21455e40935;
                      }
                      $B9756c2ca174cd617ad8d0ed4704e5c6[] = $A66766c8194fa7aac4791468fd0c7eb6["channel_id"];
                      E5d43d3ab9ea99cb10a2d21455e40935:
                      goto B9393c146d32675ffbbd6bcc75e7b92c;
                    }
                    goto e71324af90298a67d4e3569c745d7adb;
                  }
                  B9393c146d32675ffbbd6bcc75e7b92c:
                  e71324af90298a67d4e3569c745d7adb:
                }
              }
              ?>

            </ul>
            <ul class='hidden-links hidden'></ul>
          </nav>
        </center>
        <!--movies-->
        <poster>

          <?php
          foreach ($D465fc5085f41251c6fa7c77b8333b0f as $c3a18c26bfa971a25d2e6ada870ff735) {
            $E2e6656d8b1675f70c487f89e4f27a3b = $c3a18c26bfa971a25d2e6ada870ff735["target_container"];
            $e3539ad64f4d9fc6c2e465986c622369 = ipTV_lib::$StreamingServers[SERVER_ID]["site_url"] . "movie/{$_SESSION["cl_data"]["username"]}/{$_SESSION["cl_data"]["password"]}/{$c3a18c26bfa971a25d2e6ada870ff735["id"]}.{$E2e6656d8b1675f70c487f89e4f27a3b}";
            $e79b00eede3b88257cb3495721e75fe1 = json_decode($c3a18c26bfa971a25d2e6ada870ff735["movie_properties"], true);
            $B64c0bedcb468022a4c21a174e659580 = ipTV_lib::$settings["client_area_plugin"] == "vlc" ? "post('player.php',{link:'{$e3539ad64f4d9fc6c2e465986c622369}',display_name:'{$c3a18c26bfa971a25d2e6ada870ff735["stream_display_name"]}'});" : "window.location.href='{$e3539ad64f4d9fc6c2e465986c622369}'";
            echo "<div class=\"movie_Frame\"><div class=\"movie_thump\">";
            if (!empty($e79b00eede3b88257cb3495721e75fe1["movie_image"])) {
              echo "<img width=\"214\" height=\"317\" src=\"{$e79b00eede3b88257cb3495721e75fe1["movie_image"]}\" onmouseover=\"this.src='images/movie_thump_hover.png'\" onmouseout=\"this.src='{$e79b00eede3b88257cb3495721e75fe1["movie_image"]}'\" onclick=\"{$B64c0bedcb468022a4c21a174e659580}\" ></div>";
              goto de1b2f8d6fca403d0d5b1395553d62e2;
            }
            echo "<img width=\"214\" height=\"317\" src=\"images/no_poster.jpg\" onmouseover=\"this.src='images/movie_thump_hover.png'\" onmouseout=\"this.src='images/no_poster.jpg'\" onclick=\"{$B64c0bedcb468022a4c21a174e659580}\"></div>";
            de1b2f8d6fca403d0d5b1395553d62e2:
            echo "<div class=\"movie_Line\"></div>";
            echo "<center><p>" . $c3a18c26bfa971a25d2e6ada870ff735["stream_display_name"] . "</p></center>";
            echo "<div class=\"rating_star\" target=\"_blank\"><a></a><h1>" . $e79b00eede3b88257cb3495721e75fe1["rating"] . "</h1>";
            echo "<div class=\"Demo\" id=\"scroll_" . $c3a18c26bfa971a25d2e6ada870ff735["id"] . "\"><ul style=\"width:150px;\">";
            echo "<li style=\"margin-left:5px; width:180px; position:relative;\"><a style=\"color:#FC6;\">Genre:&nbsp;</a>" . $e79b00eede3b88257cb3495721e75fe1["genre"] . "</li>";
            echo "<li style=\"margin-top:10px; margin-left:5px; width:180px; position:relative;\"><a style=\"color:#FC6;\">Cast:&nbsp;</a>" . $e79b00eede3b88257cb3495721e75fe1["cast"] . "</li>";
            echo "<li style=\"margin-top:10px; margin-left:5px; width:180px;\"><a style=\"color:#FC6; position:relative;\">Director:&nbsp;Director:&nbsp;</a>" . $e79b00eede3b88257cb3495721e75fe1["director"] . "</li>";
            echo "<li style=\"margin-top:10px; margin-left:5px; width:180px;\"><a style=\"color:#FC6; position:relative;\">Release Date:&nbsp;</a>" . $e79b00eede3b88257cb3495721e75fe1["releasedate"] . "</li></ul>";
            echo "<li style=\"margin-top:10px; margin-left:5px; width:180px;\"><a style=\"color:#FC6; position:relative;\">Plot:&nbsp;</a>" . $e79b00eede3b88257cb3495721e75fe1["plot"] . "</li></div>&nbsp;</div></div>";
            echo "<script>Ps.initialize(document.getElementById('scroll_" . $c3a18c26bfa971a25d2e6ada870ff735["id"] . "'));</script>";
          }
          ?>

        </poster>
      </div>
    </div>
    </div>
    <!--/movies-->

    <!--footer-->

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
