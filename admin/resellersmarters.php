<?php
include "functions.php";
if ((!$rPermissions["is_reseller"]) or (!$rPermissions["allow_import"])) {
    exit;
}

if (!isset($_SESSION['hash'])) {
    header("Location: ./login.php");
    exit;
}

if (isset(CoreUtilities::$request["submit_secret"])) {
    $salt = "!SMARTERS!";
    $return = array();
    $result = $ipTV_db_admin->query("CREATE TABLE IF NOT EXISTS reseller_credentials (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,member_id VARCHAR(30), api_key VARCHAR(100) NOT NULL,ip_allow VARCHAR(30))");
    $password = resellerapi_generateRandomString(15);
    $encrypted = resellerapi_encrypt($password, $salt);
    $ipTV_db_admin->query("SELECT * FROM `reseller_credentials` WHERE member_id = '" . intval($rUserInfo['id']) . "'");
    if ($ipTV_db_admin->num_rows() > 0) {
        $rQuery = "UPDATE `reseller_credentials` SET api_key = '" . $encrypted . "', ip_allow= '' WHERE member_id = '" . intval($rUserInfo['id']) . "'";
    } else {
        $rQuery = "INSERT INTO `reseller_credentials`(`member_id`,`api_key`, `ip_allow`) VALUES('" . intval($rUserInfo['id']) . "','" . $encrypted . "','11.11.11.11');";
    }
    if ($ipTV_db_admin->query($rQuery)) {
        $return['result'] = 'success';
        $return['msg'] = 'API Credential genrated/updated successfully!';
    }
}

function resellerapi_decrypt($q, $salt = null) {
    $qDecoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($salt), base64_decode($q), MCRYPT_MODE_CBC, md5(md5($salt))), "\0");
    return ($qDecoded);
}

function resellerapi_encrypt($q, $salt = null) {
    $qEncoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($salt), $q, MCRYPT_MODE_CBC, md5(md5($salt))));
    return ($qEncoded);
}

function resellerapi_generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

include "header.php";
$api_key = '';
$ipTV_db_admin->query("SELECT * FROM `reseller_credentials` WHERE member_id = '" . intval($rUserInfo['id']) . "'");
if ($ipTV_db_admin->num_rows() > 0) {
    $salt = "!SMARTERS!";
    foreach ($ipTV_db_admin->get_rows() as $row) {
        $api_key = resellerapi_decrypt($row['api_key'], $salt);
    }
}
?>

<div class="wrapper boxed-layout">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="./reseller.php">
                                <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to
                                    Dashboard</li>
                            </a>
                        </ol>
                    </div>
                    <h4 class="page-title">API Credentials</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <?php echo ($result['result'] == 'success') ? $result['msg'] : ''; ?>
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" id="ticket_form">
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#" style="color: #fff; background-color: #5089de;" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-key mr-1"></i>
                                            <span class="d-none d-sm-inline">Your Secret API Key</span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <center><b><?php echo $api_key; ?></b></center>
                                        </div> <!-- end col -->
                                    </div> <!-- end row -->
                                    <ul class="list-inline wizard mb-0">
                                        <li class="next list-inline-item float-right">
                                            <input name="submit_secret" type="submit" class="btn btn-primary"
                                                value="Generate" />
                                        </li>
                                    </ul>

                                </div> <!-- tab-content -->
                            </div> <!-- end #basicwizard-->
                        </form>

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
            <div class="col-md-12 copyright text-center"><?= UIController::getFooter() ?></div>
        </div>
    </div>
</footer>
<!-- end Footer -->

<!-- Vendor js -->
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

<!-- Plugins js-->
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

<!-- Tree view js -->
<script src="assets/libs/treeview/jstree.min.js"></script>
<script src="assets/js/pages/treeview.init.js"></script>
<script src="assets/js/pages/form-wizard.init.js"></script>

<!-- App js-->
<script src="assets/js/app.min.js"></script>

<script>
    $(document).ready(function () {
        $(document).keypress(function (event) {
            if (event.which == '13') {
                event.preventDefault();
            }
        });

        $("form").attr('autocomplete', 'off');
    });
</script>
</body>

</html>