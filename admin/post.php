<?php

error_reporting(E_ALL & ~E_NOTICE);
$rICount = count(get_included_files());
include 'session.php';
include 'functions.php';

$_PAGE = getPageName();
$_ERRORS = array();

foreach (get_defined_constants(true)['user'] as $rKey => $rValue) {
    if (substr($rKey, 0, 7) == 'STATUS_') {
        $_ERRORS[intval($rValue)] = $rKey;
    }
}

if (1 < $rICount) {
    ?>
    <script>
        var rCurrentPage = "<?= $_PAGE ?>";
        var rReferer = null;
        var rErrors = <?= json_encode($_ERRORS) ?>;

        function submitForm(rType, rData, rReferer = null) {
            $(".wrapper").fadeOut();
            $("#status").fadeIn();
            if (!rReferer) {
                rReferer = "";
            }
            $.ajax({
                type: "POST",
                url: "post.php?action=" + encodeURIComponent(rType) + "&referer=" + encodeURIComponent(rReferer),
                data: rData,
                processData: false,
                contentType: false,
                success: function (rReturn) {
                    try {
                        var rJSON = $.parseJSON(rReturn);
                    } catch (e) {
                        var rJSON = {
                            "status": 0,
                            "result": false
                        };
                    }
                    callbackForm(rJSON);
                }
            });
        }

        function callbackForm(rData) {
            if (rData.location) {
                if (self !== top) {
                    parent.closeEditModal();
                    parent.showSuccess("Item has been saved.");
                } else {
                    window.location.href = rData.location;
                }
            } else {
                $(".wrapper").fadeIn();
                $("#status").fadeOut();
                $(':input[type="submit"]').prop('disabled', false);

                if (window.rErrors[rData.status] == "STATUS_INVALID_INPUT") {
                    showError("Required entry fields have not been populated. Please check the form.");
                    return;
                }

                switch (window.rCurrentPage) {
                    case "server_install":
                    case "server":
                        switch (window.rErrors[rData.status]) {
                            case "STATUS_INVALID_IP":
                                showError("Please enter a valid IP address / CIDR.");
                                break;

                            case "STATUS_EXISTS_IP":
                                showError("This IP address is already in the database. Please use another.");
                                break;

                            default:
                                showError("An error occured while processing your request.");
                                break;
                        }
                        break;
                    default:
                        showError("An error occured while processing your request.");
                        break;
                }
            }
        }
    </script>

    <?php
} else {
    if (isset(ipTV_lib::$request['referer'])) {
        $rReferer = ipTV_lib::$request['referer'];
        unset(ipTV_lib::$request['referer']);
    } else {
        $rReferer = null;
    }

    $rAction = ipTV_lib::$request['action'];
    $rData = ipTV_lib::$request;
    unset($rData['action']);

    if (count($rData) == 0) {
        $rData = json_decode(file_get_contents('php://input'), true);

        if (is_array($rData)) {
            $rData = array(file_get_contents('php://input') => 1);
        }
    }

    if (!$rData) {
        echo json_encode(array('result' => false));
        exit();
    }
    if (checkPermissions($_PAGE)) {
        switch ($rAction) {
            case 'server_install':
                $rReturn = API::installServer($rData);
                if ($rReturn['status'] == STATUS_SUCCESS) {
                    // echo json_encode(array('result' => true, 'location' => 'server_view?id=' . intval($rReturn['data']['insert_id']) . '&status=' . intval($rReturn['status']), 'status' => $rReturn['status']));
                    echo json_encode(array('result' => true, 'location' => 'servers.php', 'status' => $rReturn['status']));
                    exit();
                }
                echo json_encode(array('result' => false, 'data' => $rReturn['data'], 'status' => $rReturn['status']));
                exit();
        }
    } else {
        echo json_encode(array('result' => false));
        exit();
    }
}
