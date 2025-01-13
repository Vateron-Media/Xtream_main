<?php
include "session.php";
include "functions.php";
if (!$rPermissions["is_admin"]) {
    header("Location: ./reseller.php");
}

if ($rSettings["dark_mode"]) {
    $rColours = array(1 => array("secondary", "#7e8e9d"), 2 => array("secondary", "#7e8e9d"), 3 => array("secondary", "#7e8e9d"), 4 => array("secondary", "#7e8e9d"));
} else {
    $rColours = array(1 => array("purple", "#675db7"), 2 => array("success", "#23b397"), 3 => array("pink", "#e36498"), 4 => array("info", "#56C3D6"));
}

include "header.php";
?>
                <div class="wrapper">
                    <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Server Monitor</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" style="overflow-x:auto;">
                                    <center><iframe src="./monitor/index.php"
                                            style=" background: white; border: none; width: 1520px; height: 1860px; align: center"></iframe>
                                    </center>
                                </div> <!-- end card-body -->
                            </div> <!-- end card-->

                        </div> <!-- end col -->
                    </div>
                </div> <!-- end container -->
            </div>
            <!-- end wrapper -->
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
            <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
            <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
            <script src="assets/libs/switchery/switchery.min.js"></script>
            <script src="assets/libs/select2/select2.min.js"></script>
            <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
            <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
            <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
            <script src="assets/libs/moment/moment.min.js"></script>
            <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
            <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
            <script src="assets/libs/treeview/jstree.min.js"></script>
            <script src="assets/js/pages/treeview.init.js"></script>
            <script src="assets/js/pages/form-wizard.init.js"></script>
            <script src="assets/libs/parsleyjs/parsley.min.js"></script>
            <script src="assets/js/app.min.js"></script>


            </body>

            </html>