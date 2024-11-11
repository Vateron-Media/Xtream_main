<?php
include "session.php";
include "functions.php";

if (!checkPermissions()) {
    goHome();
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) { ?>
    <div class="content-page">
        <div class="content boxed-layout">
            <div class="container-fluid">
            <?php } else { ?>
                <div class="wrapper boxed-layout">
                    <div class="container-fluid">
                    <?php } ?>
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <a href="./servers.php">
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                                <?= $_["back_to_servers"] ?></li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?= $_["load_balancer_installation"] ?></h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-xl-12">
                            <?php if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["error_occured"] ?>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./install_server.php" method="POST" id="server_form"
                                        data-parsley-validate="">
                                        <input type="hidden" name="type" value="1" />
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#server-details" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-creation mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="server-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-3 col-form-label"
                                                                    for="server_name"><?= $_["server_name"] ?></label>
                                                                <div class="col-md-9">
                                                                    <input type="text" class="form-control"
                                                                        id="server_name" name="server_name" value=""
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-3 col-form-label"
                                                                    for="server_ip"><?= $_["server_ip"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="server_ip" name="server_ip" value=""
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="ssh_port"><?= $_["ssh_port"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="ssh_port" name="ssh_port" value="22"
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-3 col-form-label"
                                                                    for="root_username"><?= $_["ssh_username"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="root_username" name="root_username" value="root"
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="root_password"><?= $_["ssh_password"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="root_password" name="root_password" value=""
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-3 col-form-label" for="update_sysctl">Update
                                                                    sysctl.conf</label>
                                                                <div class="col-md-3">
                                                                    <input name="update_sysctl" id="update_sysctl" type="checkbox"
                                                                        data-plugin="switchery" class="js-switch" checked
                                                                        data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="http_broadcast_port"><?= $_["http_port"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="http_broadcast_port"
                                                                        name="http_broadcast_port" value="25461"
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-3 col-form-label"
                                                                    for="https_broadcast_port"><?= $_["https_port"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="https_broadcast_port"
                                                                        name="https_broadcast_port" value="25463"
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                                <label class="col-md-3 col-form-label"
                                                                    for="rtmp_port"><?= $_["rtmp_port"] ?></label>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        id="rtmp_port" name="rtmp_port" value="25462"
                                                                        required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="next list-inline-item float-right">
                                                            <input name="submit_server" type="submit"
                                                                class="btn btn-primary"
                                                                value="<?= $_["install_server"] ?>" />
                                                        </li>
                                                    </ul>
                                                </div>
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
                <?php include 'post.php'; ?>

                <script>
                    (function($) {
                        $.fn.inputFilter = function(inputFilter) {
                            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
                                if (inputFilter(this.value)) {
                                    this.oldValue = this.value;
                                    this.oldSelectionStart = this.selectionStart;
                                    this.oldSelectionEnd = this.selectionEnd;
                                } else if (this.hasOwnProperty("oldValue")) {
                                    this.value = this.oldValue;
                                    this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                                }
                            });
                        };
                    }(jQuery));

                    $(document).ready(function() {
                        $(document).keypress(function(event) {
                            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                        });
                        $("#ssh_port").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#rtmp_port").inputFilter(function(value) {
                            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
                        });
                        $("#http_broadcast_port").inputFilter(function(value) {
                            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
                        });
                        $("#https_broadcast_port").inputFilter(function(value) {
                            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
                        });
                        $("form").attr('autocomplete', 'off');
                        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                        elems.forEach(function(html) {
                            var switchery = new Switchery(html);
                        });
                        $("form").submit(function(e) {
                            e.preventDefault();
                            $(':input[type="submit"]').prop('disabled', true);
                            submitForm(window.rCurrentPage, new FormData($("form")[0]));
                        });
                    });

                    function hideModal(rName, rDispose = false) {
                        $(rName).modal("hide");
                        if (rDispose) {
                            $(rName).modal("dispose");
                        }
                        $(rName).css("display", "none");
                    }

                    function closeEditModal() {
                        $('.modal').modal('hide');
                        if ($("#datatable-users").length) {
                            $("#datatable-users").DataTable().ajax.reload(null, false);
                        }
                        if ($("#datatable-streampage").length) {
                            $("#datatable-streampage").DataTable().ajax.reload(null, false);
                        }
                    }

                    function showError(rText) {
                        $.toast({
                            text: rText,
                            icon: 'warning',
                            loader: true,
                            loaderBg: '#c62828',
                            hideAfter: 8000
                        })
                    }

                    function showSuccess(rText) {
                        $.toast({
                            text: rText,
                            icon: 'success',
                            loader: true,
                            hideAfter: 5000
                        })
                    }
                </script>
                </body>

                </html>