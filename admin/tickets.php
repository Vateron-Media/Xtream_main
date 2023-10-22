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
                            <div class="page-title-right">
                                <a href="./ticket.php">
                                    <button type="button" class="btn btn-sm btn-primary waves-effect waves-light float-right">
                                        <i class="mdi mdi-plus"></i> Create Ticket
                                    </button>
                                </a>
                            </div>
                            <?php } ?>
                            <h4 class="page-title">Tickets</h4>
                        </div>
                    </div>
                </div>     
                <div class="row">
                    <div class="col-12">
                        <div class="card-box">
                            <table class="table table-hover m-0 table-centered dt-responsive nowrap w-100" id="tickets-table">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <?php if ($rPermissions["is_admin"]) { ?>
                                        <th>Reseller</th>
                                        <?php } ?>
                                        <th>Subject</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Created Date</th>
                                        <th class="text-center">Last Reply</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($rPermissions["is_admin"]) {
                                        $rTickets = getTickets();
                                    } else {
                                        $rTickets = getTickets($rUserInfo["id"]);
                                    }
                                    foreach ($rTickets as $rTicket) { ?>
                                    <tr id="ticket-<?=$rTicket["id"]?>">
                                        <td class="text-center"><a href="./ticket_view.php?id=<?=$rTicket["id"]?>"><?=$rTicket["id"]?></a></td>
                                        <?php if ($rPermissions["is_admin"]) { ?>
                                        <td><?=$rTicket["username"]?></td>
                                        <?php } ?>
                                        <td><?=$rTicket["title"]?></td>
                                        <td class="text-center"><span class="badge badge-<?=Array(0 => "secondary", 1 => "warning", 2 => "success", 3 => "warning")[$rTicket["status"]]?>"><?=$rStatusArray[$rTicket["status"]]?></span></td>
                                        <td class="text-center"><?=$rTicket["created"]?></td>
                                        <td class="text-center"><?=$rTicket["last_reply"]?></td>
                                        <td class="text-center">
                                            <div class="btn-group dropdown">
                                                <a href="javascript: void(0);" class="table-action-btn dropdown-toggle arrow-none btn btn-light btn-sm" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-dots-horizontal"></i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="./ticket_view.php?id=<?=$rTicket["id"]?>"><i class="mdi mdi-eye mr-2 text-muted font-18 vertical-middle"></i>View Ticket</a>
													<?php if (hasPermissions("adv", "ticket")) {
                                                    if ($rTicket["status"] > 0) { ?>
                                                    <a class="dropdown-item" href="javascript:void(0);" onClick="api(<?=$rTicket["id"]?>, 'close');"><i class="mdi mdi-check-all mr-2 text-muted font-18 vertical-middle"></i>Close</a>
                                                    <?php } else if ($rPermissions["is_admin"]) { ?>
                                                    <a class="dropdown-item" href="javascript:void(0);" onClick="api(<?=$rTicket["id"]?>, 'reopen');"><i class="mdi mdi-check-all mr-2 text-muted font-18 vertical-middle"></i>Re-Open</a>
                                                    <?php } ?>
                                                    <?php if ($rPermissions["is_admin"]) { ?>
                                                    <a class="dropdown-item" href="javascript:void(0);" onClick="api(<?=$rTicket["id"]?>, 'delete');"><i class="mdi mdi-delete mr-2 text-muted font-18 vertical-middle"></i>Delete</a>
                                                    <?php if ($rTicket["admin_read"] == 0) { ?>
                                                    <a class="dropdown-item" href="javascript:void(0);" onClick="api(<?=$rTicket["id"]?>, 'read');"><i class="mdi mdi-star mr-2 font-18 text-muted vertical-middle"></i>Mark as Read</a>
                                                    <?php } else { ?>
                                                    <a class="dropdown-item" href="javascript:void(0);" onClick="api(<?=$rTicket["id"]?>, 'unread');"><i class="mdi mdi-star mr-2 font-18 text-muted vertical-middle"></i>Mark as Unread</a>
                                                    <?php }
                                                    } } ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
    </body>
</html>