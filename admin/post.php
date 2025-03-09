<?php

error_reporting(E_ALL & ~E_NOTICE);
$rICount = count(get_included_files());
include 'session.php';
include 'functions.php';

$_PAGE = UIController::getPageName();
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
                success: function(rReturn) {
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
                    case "stream":
                        switch (window.rErrors[rData.status]) {
                            case "STATUS_INVALID_FILE":
                                showError("Could not process M3U file, please use another.");
                                break;
                            case "STATUS_EXISTS_SOURCE":
                                showError("This stream source is already in your database. Please use another URL.");
                                break;
                            case "STATUS_NO_SOURCES":
                                showError("Please select at least one source for your stream.");
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

        function closeEditModal() {
            $('.modal').modal('hide');
            if ($("#datatable-users").length) {
                $("#datatable-users").DataTable().ajax.reload(null, false);
            }
            if ($("#datatable-streampage").length) {
                $("#datatable-streampage").DataTable().ajax.reload(null, false);
            }
        }
    </script>

<?php
} else {
    if (isset(CoreUtilities::$request['referer'])) {
        $rReferer = CoreUtilities::$request['referer'];
        unset(CoreUtilities::$request['referer']);
    } else {
        $rReferer = null;
    }

    $rAction = CoreUtilities::$request['action'];
    $rData = CoreUtilities::$request;
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
    if (UIController::checkPermissions($_PAGE)) {
        switch ($rAction) {
            case 'server_install':
                $rReturn = AdminAPI::installServer($rData);
                if ($rReturn['status'] == STATUS_SUCCESS) {
                    // echo json_encode(array('result' => true, 'location' => 'server_view?id=' . intval($rReturn['data']['insert_id']) . '&status=' . intval($rReturn['status']), 'status' => $rReturn['status']));
                    echo json_encode(array('result' => true, 'location' => 'servers', 'status' => $rReturn['status']));
                    exit();
                }
                echo json_encode(array('result' => false, 'data' => $rReturn['data'], 'status' => $rReturn['status']));
                exit();
            case 'settings':
                $rReturn = AdminAPI::editSettings($rData);
                if ($rReturn['status'] == STATUS_SUCCESS) {
                    echo json_encode(array('result' => true, 'location' => 'settings?status=' . intval($rReturn['status']), 'status' => $rReturn['status']));
                    exit();
                }
                echo json_encode(array('result' => false, 'data' => $rReturn['data'], 'status' => $rReturn['status']));
                exit();
            case 'server':
                $rData['server_type'] = 0;
                $rReturn = AdminAPI::processServer($rData);
                if ($rReturn['status'] == STATUS_SUCCESS) {
                    echo json_encode(array('result' => true, 'location' => 'server?id=' . intval($rReturn['data']['insert_id']) . '&status=' . intval($rReturn['status']), 'status' => $rReturn['status']));
                    exit();
                }
                echo json_encode(array('result' => false, 'data' => $rReturn['data'], 'status' => $rReturn['status']));
                exit();
            case 'stream':
                $rReturn = AdminAPI::processStream($rData);

                if ($rReturn['status'] == STATUS_SUCCESS) {
                    if (isset($_FILES['m3u_file'])) {
                        echo json_encode(array('result' => true, 'location' => 'streams?status=' . intval($rReturn['status']), 'status' => $rReturn['status']));
                        exit();
                    }

                    echo json_encode(array('result' => true, 'location' => 'stream?id=' . intval($rReturn['data']['insert_id']) . '&status=' . intval($rReturn['status']), 'status' => $rReturn['status']));
                    exit();
                }

                echo json_encode(array('result' => false, 'data' => $rReturn['data'], 'status' => $rReturn['status']));
                exit();
        }
    }
    echo json_encode(array('result' => false));
    exit();
}
