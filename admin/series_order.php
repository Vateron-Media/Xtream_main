<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_series"))) { exit; }

if (isset($_POST["reorder"])) {
    $rOrder = json_decode($_POST["episode_order_array"], True);
    if (is_array($rOrder)) {
        foreach ($rOrder as $rSeason => $rEpisodes) {
            $rSort = 0;
            foreach ($rEpisodes as $rStreamID) {
                $rSort ++;
                $db->query("UPDATE `series_episodes` SET `sort` = ".intval($rSort)." WHERE `id` = ".intval($rStreamID).";");
            }
        }
    }
}

if (!isset($_GET["id"])) { exit; }
$rSeries = getSerie($_GET["id"]);
if (!$rSeries) { exit; }

$rSeasons = Array();
$result = $db->query("SELECT `series_episodes`.`id`, `series_episodes`.`season_num`, `streams`.`stream_display_name` FROM `series_episodes` LEFT JOIN `streams` ON `streams`.`id` = `series_episodes`.`stream_id` WHERE `series_id` = ".intval($rSeries["id"])." ORDER BY `series_episodes`.`season_num` ASC, `series_episodes`.`sort` ASC;");
if (($result) && ($result->num_rows > 0)) {
    while ($row = $result->fetch_assoc()) {
        $rSeasons[$row["season_num"]][] = Array("id" => $row["id"], "title" => $row["stream_display_name"]);
    }
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./series.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Series</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$rSeries["title"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="./series_order.php?id=<?=$_GET["id"]?>" method="POST" id="episode_order_form">
                                    <input type="hidden" id="episode_order_array" name="episode_order_array" value="" />
                                    <input type="hidden" name="reorder" value="<?=$_GET["id"]?>" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <?php foreach ($rSeasons as $rSeasonNum => $rSeasonArray) { ?>
                                            <li class="nav-item">
                                                <a href="#season-<?=$rSeasonNum?>" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <span class="d-none d-sm-inline">S<?=sprintf('%02d', $rSeasonNum)?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <?php foreach ($rSeasons as $rSeasonNum => $rSeasonArray) { ?>
                                            <div class="tab-pane" id="season-<?=$rSeasonNum?>">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            To re-order an episode, select it and use the <i class="mdi mdi-chevron-up"></i> and <i class="mdi mdi-chevron-down"></i> buttons to move it. Select multiple by dragging or using CTRL. Click Save Changes at the bottom once finished.
                                                        </p>
                                                        <select multiple id="sort_episode_<?=$rSeasonNum?>" class="form-control" style="min-height:400px;">
                                                        <?php $i = 0; foreach ($rSeasonArray as $rEpisode) { $i ++; ?>
                                                            <option value="<?=$rEpisode["id"]?>"><?=$i?> - <?=$rEpisode["title"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp(<?=$rSeasonNum?>)" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown(<?=$rSeasonNum?>)" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ(<?=$rSeasonNum?>)" class="btn btn-info">Sort All A to Z</a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Changes</button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div> <!-- end #basicwizard-->
                                </form>
                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 copyright text-center"><?=getFooter()?></div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/nestable2/jquery.nestable.min.js"></script>
        <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables/buttons.flash.min.js"></script>
        <script src="assets/libs/datatables/buttons.print.min.js"></script>
        <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="assets/libs/datatables/dataTables.select.min.js"></script>
        <script src="assets/libs/datatables/dataTables.rowReorder.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        function AtoZ(rSeason) {
            $("#sort_episode_" + rSeason).append($("#sort_episode_" + rSeason + " option").remove().sort(function(a, b) {
                var at = $(a).text().toUpperCase().split("-").slice(1).join("-").trim()
                var bt = $(b).text().toUpperCase().split("-").slice(1).join("-").trim()
                return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
            }));
        }
        function MoveUp(rSeason) {
            var rSelected = $('#sort_episode_' + rSeason + ' option:selected');
            if (rSelected.length) {
                var rPrevious = rSelected.first().prev()[0];
                if ($(rPrevious).html() != '') {
                    rSelected.first().prev().before(rSelected);
                }
            }
        }
        function MoveDown(rSeason) {
            var rSelected = $('#sort_episode_' + rSeason + ' option:selected');
            if (rSelected.length) {
                rSelected.last().next().after(rSelected);
            }
        }
        $(document).ready(function() {
            $("#episode_order_form").submit(function(e){
                var rOrder = {};
                <?php foreach ($rSeasons as $rSeasonNum => $rSeasonArray) { ?>
                rOrder[<?=$rSeasonNum?>] = [];
                $('#sort_episode_<?=$rSeasonNum?> option').each(function() {
                    if ($(this).val()) {
                        rOrder[<?=$rSeasonNum?>].push($(this).val());
                    }
                });
                <?php } ?>
                $("#episode_order_array").val(JSON.stringify(rOrder));
            });
        });
        </script>
    </body>
</html>