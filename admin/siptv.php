<?php
include "session.php";
include "functions.php";
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "manage_tickets"))) {
    exit;
}
$rStatusArray = array(0 => "CLOSED", 1 => "OPEN", 2 => "RESPONDED", 3 => "READ");

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) { ?>
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
            <?php } else { ?>
                <div class="wrapper">
                    <div class="container-fluid">
                    <?php } ?>
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Smart Iptv</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">

                            <div class="card-body" style="overflow-x:auto;">
                                <center><iframe src="http://siptv.eu/mylist/"
                                        style=" background: white; border: none; width: 960px; height: 750px; align: center"></iframe>
                                </center>
                            </div> <!-- end card-body -->


                        </div> <!-- end col -->
                    </div>
                </div> <!-- end container -->
            </div>
            <!-- end wrapper -->
            <?php if ($rSettings["sidebar"]) {
                echo "</div>";
            } ?>
            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 copyright text-center"><?= getFooter() ?></div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

            <script src="assets/js/vendor.min.js"></script>
            <script src="assets/libs/jquery-knob/jquery.knob.min.js"></script>
            <script src="assets/libs/peity/jquery.peity.min.js"></script>
            <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
            <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
            <script src="assets/libs/jquery-number/jquery.number.js"></script>
            <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
            <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
            <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
            <script src="assets/js/pages/dashboard.init.js"></script>
            <script src="assets/js/app.min.js"></script>

            </body>

            </html>