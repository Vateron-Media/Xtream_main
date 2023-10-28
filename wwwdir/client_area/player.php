<?php

require "../init.php";
session_start();
if (!(empty($_SESSION["client_loggedin"]) && $_SESSION["client_loggedin"] != true && empty($_SESSION["cl_data"]))) {
    $B80b91ec08fe2fc8b600751840264e3a = !empty($_POST["link"]) ? $_POST["link"] : '';
    $cfd246a8499e5bb4a9d89e37c524322a = !empty($_POST["display_name"]) ? $_POST["display_name"] : '';
    $a28758c1ab974badfc544e11aaf19a57 = "application/x-mpegurl";
    $Ac783e41c152569f224242dacb8b03d3 = "true";
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset=" utf-8">
        <title><?php
                echo $cfd246a8499e5bb4a9d89e37c524322a;
                ?>
        </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/functional.css">
        <!-- CSS for this demo -->


        <!-- Flowplayer-->
        <script src="js/flowplayer.min.js"></script>


        <script src="js/flowplayer.hlsjs.min.js"></script>
        <style>
            .fullscreen-bg {
                position: fixed;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                overflow: hidden;
                z-index: -100;
            }

            .fullscreen-bg__video {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
        </style>
    </head>

    <body>

        <?php
        if (ipTV_lib::$settings["client_area_plugin"] == "vlc") {
        ?>
            <object classid="clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921" codebase="http://download.videolan.org/pub/videolan/vlc/last/win32/axvlc.cab" id="vlc">
                <embed type="application/x-vlc-plugin" pluginspage="http://www.videolan.org" name="vlc" class="fullscreen-bg fullscreen-bg__video" target="<?php
                                                                                                                                                            echo $B80b91ec08fe2fc8b600751840264e3a;
                                                                                                                                                            ?>
" />
            </object>
        <?php
            goto bf9a385a4e688f089267117fc873dbea;
        }
        ?>
        <div id="fp-hlsjs"></div>

        <script>
            flowplayer("#fp-hlsjs", {
                ratio: 9 / 16,
                clip: {
                    autoplay: true,
                    title: "<? echo $cfd246a8499e5bb4a9d89e37c524322a; ?>",
                    sources: [{
                        type: "<? echo $a28758c1ab974badfc544e11aaf19a57; ?>",
                        src: "<? echo $B80b91ec08fe2fc8b600751840264e3a; ?> ",
                        live: <? echo $Ac783e41c152569f224242dacb8b03d3; ?>
                    }]
                },
                embed: false

            });
        </script>
        <?php
        bf9a385a4e688f089267117fc873dbea:
        ?>

    </body>

    </html><?php
            // [PHPDeobfuscator] Implied script end
            return;
        }
        header("Location: index.php");
        die;
