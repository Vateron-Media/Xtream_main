<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "manage_tickets"))) { exit; }
$rStatusArray = Array(0 => "CLOSED", 1 => "OPEN", 2 => "RESPONDED", 3 => "READ");

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper"><div class="container-fluid">
        <?php } ?>
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <?php if (!$rPermissions["is_admin"]) { ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>  
                <br>		
                <br>				
                <div class="row">
                    <div class="col-12">
                        <div class="col-12">
                        <div class="card-box card-body">
                            <div class="row">
                                <div class="col-1">
									<?php if ($rAdminSettings["dark_mode"]) { ?>
									<div class="avatar-sm bg-secondary rounded">
										<i class="fas fa-info-circle avatar-title font-24 text-white"></i>
									</div>
									<?php } else { ?>
                                    <div class="avatar-sm bg-soft-warning rounded">
                                        <i class="fas fa-info-circle avatar-title font-24 text-danger"></i>
                                    </div>
									<?php } ?>
                                </div>
                                <div class="col-11">
                                    <div class="text-left">
                                        <?=$rSettings["page_mannuals"]?>
                                    </div>
                                </div>
                            </div>
						</div>
					</div>
                    </div><!-- end col -->
                </div>
                <!-- end row -->
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
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
        <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        <script>
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this ticket?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=ticket&sub=" + rType + "&ticket_id=" + rID, function(data) {
                if (data.result == true) {
                    location.reload();
                } else {
                    $.toast("An error occured while processing your request.");
                }
            }).fail(function() {
                $.toast("An error occured while processing your request.");
            });
        }        
        $(document).ready(function() {
            $("#tickets-table").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                },
                order: [[ 0, "desc" ]],
                stateSave: true
            });
            $("#tickets-table").css("width", "100%");
        });
		</script>
<!-- copiar comando -->
		<script>
		function myFunction() {
        var copyText = document.getElementById("myInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999)
        document.execCommand("copy");
        alert("Copiou o texto: " + copyText.value);
        }
        </script>
    </body>
</html>





