<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_server")) && (!hasPermissions("adv", "edit_server")))) {
    exit;
}

if (isset($_POST["submit_server"])) {
    if (isset($_POST["edit"])) {
        if (!hasPermissions("adv", "edit_server")) {
            exit;
        }
        $rArray = getStreamingServersByID($_POST["edit"]);
        unset($rArray["id"]);
    } else {
        if (!hasPermissions("adv", "add_server")) {
            exit;
        }
        $rArray = array("server_name" => "", "domain_name" => "", "server_ip" => "", "vpn_ip" => "", "diff_time_main" => 0, "http_broadcast_port" => 25461, "total_clients" => 1000, "system_os" => "", "network_interface" => "", "status" => 2, "enable_geoip" => 0, "geoip_countries" => "[]", "geoip_type" => "low_priority", "isp_names" => "[]", "isp_type" => "low_priority", "can_delete" => 1, "rtmp_port" => 25462, "enable_isp" => 0, "network_guaranteed_speed" => 1000, "https_broadcast_port" => 25463, "whitelist_ips" => array(), "timeshift_only" => 0);
    }
    if (strlen($_POST["server_ip"]) == 0) {
        $_STATUS = 1;
    }
    if (isset($rServers[$_POST["edit"]]["can_delete"])) {
        $rArray["can_delete"] = intval($rServers[$_POST["edit"]]["can_delete"]);
    }
    if (isset($_POST["enabled"])) {
        $rArray["enabled"] = intval($_POST["enabled"]);
        unset($_POST["enabled"]);
    }
    if (isset($_POST["total_clients"])) {
        $rArray["total_clients"] = intval($_POST["total_clients"]);
        unset($_POST["total_clients"]);
    }
    $rPorts = array($rArray["http_broadcast_port"], $rArray["https_broadcast_port"], $rArray["rtmp_port"], $rArray["http_isp_port"]);
    if (isset($_POST["http_broadcast_port"])) {
        $rArray["http_broadcast_port"] = intval($_POST["http_broadcast_port"]);
        unset($_POST["http_broadcast_port"]);
    }
    if (isset($_POST["https_broadcast_port"])) {
        $rArray["https_broadcast_port"] = intval($_POST["https_broadcast_port"]);
        unset($_POST["https_broadcast_port"]);
    }
    if (isset($_POST["rtmp_port"])) {
        $rArray["rtmp_port"] = intval($_POST["rtmp_port"]);
        unset($_POST["rtmp_port"]);
    }
    if (isset($_POST["http_isp_port"])) {
        $rArray["http_isp_port"] = intval($_POST["http_isp_port"]);
        unset($_POST["http_isp_port"]);
    }
    if (isset($_POST["diff_time_main"])) {
        $rArray["diff_time_main"] = intval($_POST["diff_time_main"]);
        unset($_POST["diff_time_main"]);
    }
    if (isset($_POST["network_guaranteed_speed"])) {
        $rArray["network_guaranteed_speed"] = intval($_POST["network_guaranteed_speed"]);
        unset($_POST["network_guaranteed_speed"]);
    }
    if (isset($_POST["timeshift_only"])) {
        $rArray["timeshift_only"] = true;
        unset($_POST["timeshift_only"]);
    } else {
        $rArray["timeshift_only"] = false;
    }
    if (isset($_POST["enable_geoip"])) {
        $rArray["enable_geoip"] = true;
        unset($_POST["enable_geoip"]);
    } else {
        $rArray["enable_geoip"] = false;
    }
    if (isset($_POST["geoip_countries"])) {
        $rArray["geoip_countries"] = array();
        foreach ($_POST["geoip_countries"] as $rCountry) {
            $rArray["geoip_countries"][] = $rCountry;
        }
        unset($_POST["geoip_countries"]);
    } else {
        $rArray["geoip_countries"] = array();
    }

    if (isset($_POST["enable_isp"])) {
        $rArray["enable_isp"] = true;
        unset($_POST["enable_isp"]);
    } else {
        $rArray["enable_isp"] = false;
    }
    if (isset($_POST["isp_names"])) {
        if (!is_array($_POST["isp_names"])) {
            $_POST["isp_names"] = array($_POST["isp_names"]);
        }
        $rArray["isp_names"] = json_encode($_POST["isp_names"]);
    } else {
        $rArray["isp_names"] = "[]";
    }
    if (!isset($_STATUS)) {
        foreach ($_POST as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
        }
        $rCols = "`" . ESC(implode('`,`', array_keys($rArray))) . "`";
        foreach (array_values($rArray) as $rValue) {
            isset($rValues) ? $rValues .= ',' : $rValues = '';
            if (is_array($rValue)) {
                $rValue = json_encode($rValue);
            }
            if (is_null($rValue)) {
                $rValues .= 'NULL';
            } else {
                $rValues .= '\'' . ESC($rValue) . '\'';
            }
        }
        if (isset($_POST["edit"])) {
            $rCols = "id," . $rCols;
            $rValues = ESC($_POST["edit"]) . "," . $rValues;
        }
        $rQuery = "REPLACE INTO `streaming_servers`(" . $rCols . ") VALUES(" . $rValues . ");";
        if ($db->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
                // Replace ports
                if ($rArray["http_broadcast_port"] <> $rPorts[0]) {
                    changePort($rInsertID, 0, $rPorts[0], $rArray["http_broadcast_port"]);
                }
                if ($rArray["https_broadcast_port"] <> $rPorts[1]) {
                    changePort($rInsertID, 1, $rPorts[1], $rArray["https_broadcast_port"]);
                }
                if ($rArray["rtmp_port"] <> $rPorts[2]) {
                    changePort($rInsertID, 2, $rPorts[2], $rArray["rtmp_port"]);
                }
                if ($rArray["http_isp_port"] <> $rPorts[3]) {
                    changePort($rInsertID, 3, $rPorts[3], $rArray["http_isp_port"]);
                }
            } else {
                $rInsertID = $db->insert_id;
            }
            $rDifference = getTimeDifference($rInsertID);
            $db->query("UPDATE `streaming_servers` SET `diff_time_main` = " . intval($rDifference) . " WHERE `id` = " . intval($rInsertID) . ";");
            $_STATUS = 0;
            $rServers = getStreamingServers();
            header("Location: ./server.php?id=" . $rInsertID);
            exit;
        } else {
            $_STATUS = 2;
        }
    }
}

if (isset($_GET["id"])) {
    $rServerArr = $rServers[$_GET["id"]];
    if ((!$rServerArr) or (!hasPermissions("adv", "edit_server"))) {
        exit;
    }
} else if (!hasPermissions("adv", "add_server")) {
    exit;
}

$rCountries = array(array("id" => "ALL", "name" => "All Countries"), array("id" => "A1", "name" => "Anonymous Proxy"), array("id" => "A2", "name" => "Satellite Provider"), array("id" => "O1", "name" => "Other Country"), array("id" => "AF", "name" => "Afghanistan"), array("id" => "AX", "name" => "Aland Islands"), array("id" => "AL", "name" => "Albania"), array("id" => "DZ", "name" => "Algeria"), array("id" => "AS", "name" => "American Samoa"), array("id" => "AD", "name" => "Andorra"), array("id" => "AO", "name" => "Angola"), array("id" => "AI", "name" => "Anguilla"), array("id" => "AQ", "name" => "Antarctica"), array("id" => "AG", "name" => "Antigua And Barbuda"), array("id" => "AR", "name" => "Argentina"), array("id" => "AM", "name" => "Armenia"), array("id" => "AW", "name" => "Aruba"), array("id" => "AU", "name" => "Australia"), array("id" => "AT", "name" => "Austria"), array("id" => "AZ", "name" => "Azerbaijan"), array("id" => "BS", "name" => "Bahamas"), array("id" => "BH", "name" => "Bahrain"), array("id" => "BD", "name" => "Bangladesh"), array("id" => "BB", "name" => "Barbados"), array("id" => "BY", "name" => "Belarus"), array("id" => "BE", "name" => "Belgium"), array("id" => "BZ", "name" => "Belize"), array("id" => "BJ", "name" => "Benin"), array("id" => "BM", "name" => "Bermuda"), array("id" => "BT", "name" => "Bhutan"), array("id" => "BO", "name" => "Bolivia"), array("id" => "BA", "name" => "Bosnia And Herzegovina"), array("id" => "BW", "name" => "Botswana"), array("id" => "BV", "name" => "Bouvet Island"), array("id" => "BR", "name" => "Brazil"), array("id" => "IO", "name" => "British Indian Ocean Territory"), array("id" => "BN", "name" => "Brunei Darussalam"), array("id" => "BG", "name" => "Bulgaria"), array("id" => "BF", "name" => "Burkina Faso"), array("id" => "BI", "name" => "Burundi"), array("id" => "KH", "name" => "Cambodia"), array("id" => "CM", "name" => "Cameroon"), array("id" => "CA", "name" => "Canada"), array("id" => "CV", "name" => "Cape Verde"), array("id" => "KY", "name" => "Cayman Islands"), array("id" => "CF", "name" => "Central African Republic"), array("id" => "TD", "name" => "Chad"), array("id" => "CL", "name" => "Chile"), array("id" => "CN", "name" => "China"), array("id" => "CX", "name" => "Christmas Island"), array("id" => "CC", "name" => "Cocos (Keeling) Islands"), array("id" => "CO", "name" => "Colombia"), array("id" => "KM", "name" => "Comoros"), array("id" => "CG", "name" => "Congo"), array("id" => "CD", "name" => "Congo, Democratic Republic"), array("id" => "CK", "name" => "Cook Islands"), array("id" => "CR", "name" => "Costa Rica"), array("id" => "CI", "name" => "Cote D'Ivoire"), array("id" => "HR", "name" => "Croatia"), array("id" => "CU", "name" => "Cuba"), array("id" => "CY", "name" => "Cyprus"), array("id" => "CZ", "name" => "Czech Republic"), array("id" => "DK", "name" => "Denmark"), array("id" => "DJ", "name" => "Djibouti"), array("id" => "DM", "name" => "Dominica"), array("id" => "DO", "name" => "Dominican Republic"), array("id" => "EC", "name" => "Ecuador"), array("id" => "EG", "name" => "Egypt"), array("id" => "SV", "name" => "El Salvador"), array("id" => "GQ", "name" => "Equatorial Guinea"), array("id" => "ER", "name" => "Eritrea"), array("id" => "EE", "name" => "Estonia"), array("id" => "ET", "name" => "Ethiopia"), array("id" => "FK", "name" => "Falkland Islands (Malvinas)"), array("id" => "FO", "name" => "Faroe Islands"), array("id" => "FJ", "name" => "Fiji"), array("id" => "FI", "name" => "Finland"), array("id" => "FR", "name" => "France"), array("id" => "GF", "name" => "French Guiana"), array("id" => "PF", "name" => "French Polynesia"), array("id" => "TF", "name" => "French Southern Territories"), array("id" => "MK", "name" => "Fyrom"), array("id" => "GA", "name" => "Gabon"), array("id" => "GM", "name" => "Gambia"), array("id" => "GE", "name" => "Georgia"), array("id" => "DE", "name" => "Germany"), array("id" => "GH", "name" => "Ghana"), array("id" => "GI", "name" => "Gibraltar"), array("id" => "GR", "name" => "Greece"), array("id" => "GL", "name" => "Greenland"), array("id" => "GD", "name" => "Grenada"), array("id" => "GP", "name" => "Guadeloupe"), array("id" => "GU", "name" => "Guam"), array("id" => "GT", "name" => "Guatemala"), array("id" => "GG", "name" => "Guernsey"), array("id" => "GN", "name" => "Guinea"), array("id" => "GW", "name" => "Guinea-Bissau"), array("id" => "GY", "name" => "Guyana"), array("id" => "HT", "name" => "Haiti"), array("id" => "HM", "name" => "Heard Island & Mcdonald Islands"), array("id" => "VA", "name" => "Holy See (Vatican City State)"), array("id" => "HN", "name" => "Honduras"), array("id" => "HK", "name" => "Hong Kong"), array("id" => "HU", "name" => "Hungary"), array("id" => "IS", "name" => "Iceland"), array("id" => "IN", "name" => "India"), array("id" => "ID", "name" => "Indonesia"), array("id" => "IR", "name" => "Iran, Islamic Republic Of"), array("id" => "IQ", "name" => "Iraq"), array("id" => "IE", "name" => "Ireland"), array("id" => "IM", "name" => "Isle Of Man"), array("id" => "IL", "name" => "Israel"), array("id" => "IT", "name" => "Italy"), array("id" => "JM", "name" => "Jamaica"), array("id" => "JP", "name" => "Japan"), array("id" => "JE", "name" => "Jersey"), array("id" => "JO", "name" => "Jordan"), array("id" => "KZ", "name" => "Kazakhstan"), array("id" => "KE", "name" => "Kenya"), array("id" => "KI", "name" => "Kiribati"), array("id" => "KR", "name" => "Korea"), array("id" => "KW", "name" => "Kuwait"), array("id" => "KG", "name" => "Kyrgyzstan"), array("id" => "LA", "name" => "Lao People's Democratic Republic"), array("id" => "LV", "name" => "Latvia"), array("id" => "LB", "name" => "Lebanon"), array("id" => "LS", "name" => "Lesotho"), array("id" => "LR", "name" => "Liberia"), array("id" => "LY", "name" => "Libyan Arab Jamahiriya"), array("id" => "LI", "name" => "Liechtenstein"), array("id" => "LT", "name" => "Lithuania"), array("id" => "LU", "name" => "Luxembourg"), array("id" => "MO", "name" => "Macao"), array("id" => "MG", "name" => "Madagascar"), array("id" => "MW", "name" => "Malawi"), array("id" => "MY", "name" => "Malaysia"), array("id" => "MV", "name" => "Maldives"), array("id" => "ML", "name" => "Mali"), array("id" => "MT", "name" => "Malta"), array("id" => "MH", "name" => "Marshall Islands"), array("id" => "MQ", "name" => "Martinique"), array("id" => "MR", "name" => "Mauritania"), array("id" => "MU", "name" => "Mauritius"), array("id" => "YT", "name" => "Mayotte"), array("id" => "MX", "name" => "Mexico"), array("id" => "FM", "name" => "Micronesia, Federated States Of"), array("id" => "MD", "name" => "Moldova"), array("id" => "MC", "name" => "Monaco"), array("id" => "MN", "name" => "Mongolia"), array("id" => "ME", "name" => "Montenegro"), array("id" => "MS", "name" => "Montserrat"), array("id" => "MA", "name" => "Morocco"), array("id" => "MZ", "name" => "Mozambique"), array("id" => "MM", "name" => "Myanmar"), array("id" => "NA", "name" => "Namibia"), array("id" => "NR", "name" => "Nauru"), array("id" => "NP", "name" => "Nepal"), array("id" => "NL", "name" => "Netherlands"), array("id" => "AN", "name" => "Netherlands Antilles"), array("id" => "NC", "name" => "New Caledonia"), array("id" => "NZ", "name" => "New Zealand"), array("id" => "NI", "name" => "Nicaragua"), array("id" => "NE", "name" => "Niger"), array("id" => "NG", "name" => "Nigeria"), array("id" => "NU", "name" => "Niue"), array("id" => "NF", "name" => "Norfolk Island"), array("id" => "MP", "name" => "Northern Mariana Islands"), array("id" => "NO", "name" => "Norway"), array("id" => "OM", "name" => "Oman"), array("id" => "PK", "name" => "Pakistan"), array("id" => "PW", "name" => "Palau"), array("id" => "PS", "name" => "Palestinian Territory, Occupied"), array("id" => "PA", "name" => "Panama"), array("id" => "PG", "name" => "Papua New Guinea"), array("id" => "PY", "name" => "Paraguay"), array("id" => "PE", "name" => "Peru"), array("id" => "PH", "name" => "Philippines"), array("id" => "PN", "name" => "Pitcairn"), array("id" => "PL", "name" => "Poland"), array("id" => "PT", "name" => "Portugal"), array("id" => "PR", "name" => "Puerto Rico"), array("id" => "QA", "name" => "Qatar"), array("id" => "RE", "name" => "Reunion"), array("id" => "RO", "name" => "Romania"), array("id" => "RU", "name" => "Russian Federation"), array("id" => "RW", "name" => "Rwanda"), array("id" => "BL", "name" => "Saint Barthelemy"), array("id" => "SH", "name" => "Saint Helena"), array("id" => "KN", "name" => "Saint Kitts And Nevis"), array("id" => "LC", "name" => "Saint Lucia"), array("id" => "MF", "name" => "Saint Martin"), array("id" => "PM", "name" => "Saint Pierre And Miquelon"), array("id" => "VC", "name" => "Saint Vincent And Grenadines"), array("id" => "WS", "name" => "Samoa"), array("id" => "SM", "name" => "San Marino"), array("id" => "ST", "name" => "Sao Tome And Principe"), array("id" => "SA", "name" => "Saudi Arabia"), array("id" => "SN", "name" => "Senegal"), array("id" => "RS", "name" => "Serbia"), array("id" => "SC", "name" => "Seychelles"), array("id" => "SL", "name" => "Sierra Leone"), array("id" => "SG", "name" => "Singapore"), array("id" => "SK", "name" => "Slovakia"), array("id" => "SI", "name" => "Slovenia"), array("id" => "SB", "name" => "Solomon Islands"), array("id" => "SO", "name" => "Somalia"), array("id" => "ZA", "name" => "South Africa"), array("id" => "GS", "name" => "South Georgia And Sandwich Isl."), array("id" => "ES", "name" => "Spain"), array("id" => "LK", "name" => "Sri Lanka"), array("id" => "SD", "name" => "Sudan"), array("id" => "SR", "name" => "Suriname"), array("id" => "SJ", "name" => "Svalbard And Jan Mayen"), array("id" => "SZ", "name" => "Swaziland"), array("id" => "SE", "name" => "Sweden"), array("id" => "CH", "name" => "Switzerland"), array("id" => "SY", "name" => "Syrian Arab Republic"), array("id" => "TW", "name" => "Taiwan"), array("id" => "TJ", "name" => "Tajikistan"), array("id" => "TZ", "name" => "Tanzania"), array("id" => "TH", "name" => "Thailand"), array("id" => "TL", "name" => "Timor-Leste"), array("id" => "TG", "name" => "Togo"), array("id" => "TK", "name" => "Tokelau"), array("id" => "TO", "name" => "Tonga"), array("id" => "TT", "name" => "Trinidad And Tobago"), array("id" => "TN", "name" => "Tunisia"), array("id" => "TR", "name" => "Turkey"), array("id" => "TM", "name" => "Turkmenistan"), array("id" => "TC", "name" => "Turks And Caicos Islands"), array("id" => "TV", "name" => "Tuvalu"), array("id" => "UG", "name" => "Uganda"), array("id" => "UA", "name" => "Ukraine"), array("id" => "AE", "name" => "United Arab Emirates"), array("id" => "GB", "name" => "United Kingdom"), array("id" => "US", "name" => "United States"), array("id" => "UM", "name" => "United States Outlying Islands"), array("id" => "UY", "name" => "Uruguay"), array("id" => "UZ", "name" => "Uzbekistan"), array("id" => "VU", "name" => "Vanuatu"), array("id" => "VE", "name" => "Venezuela"), array("id" => "VN", "name" => "Viet Nam"), array("id" => "VG", "name" => "Virgin Islands, British"), array("id" => "VI", "name" => "Virgin Islands, U.S."), array("id" => "WF", "name" => "Wallis And Futuna"), array("id" => "EH", "name" => "Western Sahara"), array("id" => "YE", "name" => "Yemen"), array("id" => "ZM", "name" => "Zambia"), array("id" => "ZW", "name" => "Zimbabwe"));
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
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?= $_["back_to_servers"] ?></li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php if (isset($rServerArr)) {
                                                            echo $_["edit"];
                                                        } else {
                                                            echo $_["add"];
                                                        } ?> <?= $_["server"] ?></h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-xl-12">
                            <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["server_operation_was_completed"] ?>
                                </div>
                            <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["generic_fail"] ?>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./server.php<?php if (isset($_GET["id"])) {
                                                                    echo "?id=" . $_GET["id"];
                                                                } ?>" method="POST" id="server_form" data-parsley-validate="">
                                        <?php if (isset($rServerArr)) { ?>
                                            <input type="hidden" name="edit" value="<?= $rServerArr["id"] ?>" />
                                            <input type="hidden" name="status" value="<?= $rServerArr["status"] ?>" />
                                        <?php } ?>
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#server-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#advanced-options" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["advanced"] ?></span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#ispmanager" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["isp_manager"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="server-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="server_name"><?= $_["server_name"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="server_name" name="server_name" value="<?php if (isset($rServerArr)) {
                                                                                                                                                            echo htmlspecialchars($rServerArr["server_name"]);
                                                                                                                                                        } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="domain_name"><?= $_["domaine_name"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="domain_name" name="domain_name" value="<?php if (isset($rServerArr)) {
                                                                                                                                                            echo htmlspecialchars($rServerArr["domain_name"]);
                                                                                                                                                        } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="server_ip"><?= $_["server_ip"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="server_ip" name="server_ip" value="<?php if (isset($rServerArr)) {
                                                                                                                                                        echo htmlspecialchars($rServerArr["server_ip"]);
                                                                                                                                                    } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="vpn_ip"><?= $_["vpn_ip"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="vpn_ip" name="vpn_ip" value="<?php if (isset($rServerArr)) {
                                                                                                                                                    echo htmlspecialchars($rServerArr["vpn_ip"]);
                                                                                                                                                } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="total_clients"><?= $_["max_clients"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" id="total_clients" name="total_clients" value="<?php if (isset($rServerArr)) {
                                                                                                                                                                echo htmlspecialchars($rServerArr["total_clients"]);
                                                                                                                                                            } else {
                                                                                                                                                                echo "1000";
                                                                                                                                                            } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                                <label class="col-md-4 col-form-label" for="timeshift_only"><?= $_["timeshift_only"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input name="timeshift_only" id="timeshift_only" type="checkbox" <?php if (isset($rServerArr)) {
                                                                                                                                            if ($rServerArr["timeshift_only"] == 1) {
                                                                                                                                                echo "checked ";
                                                                                                                                            }
                                                                                                                                        } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="next list-inline-item float-right">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?= $_["next"] ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-pane" id="advanced-options">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="http_broadcast_port"><?= $_["http_port"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" id="http_broadcast_port" name="http_broadcast_port" value="<?php if (isset($rServerArr)) {
                                                                                                                                                                            echo htmlspecialchars($rServerArr["http_broadcast_port"]);
                                                                                                                                                                        } else {
                                                                                                                                                                            echo "25461";
                                                                                                                                                                        } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                                <label class="col-md-4 col-form-label" for="https_broadcast_port"><?= $_["https_port"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" id="https_broadcast_port" name="https_broadcast_port" value="<?php if (isset($rServerArr)) {
                                                                                                                                                                                echo htmlspecialchars($rServerArr["https_broadcast_port"]);
                                                                                                                                                                            } else {
                                                                                                                                                                                echo "25463";
                                                                                                                                                                            } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="rtmp_port"><?= $_["rtmp_port"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" id="rtmp_port" name="rtmp_port" value="<?php if (isset($rServerArr)) {
                                                                                                                                                        echo htmlspecialchars($rServerArr["rtmp_port"]);
                                                                                                                                                    } else {
                                                                                                                                                        echo "25462";
                                                                                                                                                    } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                                <?php if (($_GET["id"] == 1)) { ?>
                                                                    <label class="col-md-4 col-form-label" for="http_isp_port"><?= $_["isp_port"] ?></label>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control" id="http_isp_port" name="http_isp_port" value="<?php if (isset($rServerArr)) {
                                                                                                                                                                    echo htmlspecialchars($rServerArr["http_isp_port"]);
                                                                                                                                                                } else {
                                                                                                                                                                    echo "";
                                                                                                                                                                } ?>" required data-parsley-trigger="change">
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="form-group row mb-4">

                                                                <label class="col-md-4 col-form-label" for="diff_time_main"><?= $_["time_difference"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" disabled class="form-control" id="diff_time_main" name="diff_time_main" value="<?php if (isset($rServerArr)) {
                                                                                                                                                                            echo htmlspecialchars($rServerArr["diff_time_main"]);
                                                                                                                                                                        } else {
                                                                                                                                                                            echo "0";
                                                                                                                                                                        } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="network_interface">Network Interface</label>
                                                                <div class="col-md-2">
                                                                    <select name="network_interface" id="network_interface" class="form-control select2" data-toggle="select2">network interface
                                                                        <?php
                                                                        foreach (netnet($_GET["id"]) as $bbb) { ?>
                                                                            <option <?php if (isset($rServerArr)) {
                                                                                        if ($rServerArr["network_interface"] == $bbb) {
                                                                                            echo "selected ";
                                                                                        }
                                                                                    } ?>value="<?= $bbb ?>"><?= $bbb ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <label class="col-md-4 col-form-label" for="network_guaranteed_speed"><?= $_["network_speed"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" id="network_guaranteed_speed" name="network_guaranteed_speed" value="<?php if (isset($rServerArr)) {
                                                                                                                                                                                        echo htmlspecialchars($rServerArr["network_guaranteed_speed"]);
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo "1000";
                                                                                                                                                                                    } ?>" required data-parsley-trigger="change">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="system_os"><?= $_["operating_system"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" id="system_os" name="system_os" value="<?php if (isset($rServerArr)) {
                                                                                                                                                        echo htmlspecialchars($rServerArr["system_os"]);
                                                                                                                                                    } else {
                                                                                                                                                        echo "Ubuntu 14.04.5 LTS";
                                                                                                                                                    } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="enable_geoip"><?= $_["geoip_load_balancing"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input name="enable_geoip" id="enable_geoip" type="checkbox" <?php if (isset($rServerArr)) {
                                                                                                                                        if ($rServerArr["enable_geoip"] == 1) {
                                                                                                                                            echo "checked ";
                                                                                                                                        }
                                                                                                                                    } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <select name="geoip_type" id="geoip_type" class="form-control select2" data-toggle="select2">
                                                                        <?php foreach (array("high_priority" => "High Priority", "low_priority" => "Low Priority", "strict" => "Strict") as $rType => $rText) { ?>
                                                                            <option <?php if (isset($rServerArr)) {
                                                                                        if ($rServerArr["geoip_type"] == $rType) {
                                                                                            echo "selected ";
                                                                                        }
                                                                                    } ?>value="<?= $rType ?>"><?= $rText ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="geoip_countries"><?= $_["geoip_countries"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="geoip_countries[]" id="geoip_countries" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="<?= $_["choose"] ?>">
                                                                        <?php $rSelected = json_decode($rServerArr["geoip_countries"], True);
                                                                        foreach ($rCountries as $rCountry) { ?>
                                                                            <!--<option <?php if (isset($rServerArr)) {
                                                                                            if (!empty($rSelected) && in_array($rCountry["id"], $rSelected)) {
                                                                                                echo "selected ";
                                                                                            }
                                                                                        } ?>value="<?= $rCountry["id"] ?>"><?= $rCountry["name"] ?></option>-->
                                                                            <option <?php if (isset($rServerArr)) {
                                                                                        if (!empty($rSelected) && in_array($rCountry["id"], $rSelected)) {
                                                                                            echo "selected ";
                                                                                        }
                                                                                    } ?>value="<?= $rCountry["id"] ?>"><?= $rCountry["name"] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?= $_["prev"] ?></a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?= $_["next"] ?></a>
                                                        </li>
                                                    </ul>
                                                </div>


                                                <div class="tab-pane" id="ispmanager">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="enable_isp">enable isp</label>
                                                                <div class="col-md-2">
                                                                    <input name="enable_isp" id="enable_isp" type="checkbox" <?php if (isset($rServerArr)) {
                                                                                                                                    if ($rServerArr["enable_isp"] == 1) {
                                                                                                                                        echo "checked ";
                                                                                                                                    }
                                                                                                                                } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <select name="isp_type" id="isp_type" class="form-control select2" data-toggle="select2">
                                                                        <?php foreach (array("high_priority" => "High Priority", "low_priority" => "Low Priority", "strict" => "Strict") as $rType => $rText) { ?>
                                                                            <option <?php if (isset($rServerArr)) {
                                                                                        if ($rServerArr["isp_type"] == $rType) {
                                                                                            echo "selected ";
                                                                                        }
                                                                                    } ?>value="<?= $rType ?>"><?= $rText ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="isp_field">Allowed ISP Names</label>
                                                                <div class="col-md-8 input-group">
                                                                    <input type="text" id="isp_field" class="form-control" value="">
                                                                    <div class="input-group-append">
                                                                        <a href="javascript:void(0)" id="add_isp" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-plus"></i></a>
                                                                        <a href="javascript:void(0)" id="remove_isp" class="btn btn-danger waves-effect waves-light"><i class="mdi mdi-close"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label" for="isp_names">&nbsp;</label>
                                                                <div class="col-md-8">
                                                                    <select id="isp_names" name="isp_names[]" size=6 class="form-control" multiple="multiple">
                                                                        <?php $rnabilosss = json_decode($rServerArr["isp_names"], True);
                                                                        if ((isset($rServerArr)) & (is_array($rnabilosss))) {
                                                                            foreach ($rnabilosss as $ispnom) { ?>
                                                                                <option value="<?= $ispnom ?>"><?= $ispnom ?></option>
                                                                        <?php }
                                                                        } ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);" class="btn btn-secondary"><?= $_["prev"] ?></a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <input name="submit_server" type="submit" class="btn btn-primary" value="<?php if (isset($rServerArr)) {
                                                                                                                                            echo $_["edit"];
                                                                                                                                        } else {
                                                                                                                                            echo $_["add"];
                                                                                                                                        } ?>" />
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

                <script>
                    var swObjs = {};
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
                        $('select.select2').select2({
                            width: '100%'
                        })
                        $("#geoip_countries").select2({
                            width: '100%'
                        })
                        $(".js-switch").each(function(index, element) {
                            var init = new Switchery(element);
                            window.swObjs[element.id] = init;
                        });

                        $('#exp_date').daterangepicker({
                            singleDatePicker: true,
                            showDropdowns: true,
                            minDate: new Date(),
                            locale: {
                                format: 'YYYY-MM-DD'
                            }
                        });

                        $("#no_expire").change(function() {
                            if ($(this).prop("checked")) {
                                $("#exp_date").prop("disabled", true);
                            } else {
                                $("#exp_date").removeAttr("disabled");
                            }
                        });
                        $("#server_form").submit(function(e) {
                            $("#isp_names option").prop('selected', true);
                        });
                        $("#add_isp").click(function() {
                            if ($("#isp_field").val().length > 0) {
                                var o = new Option($("#isp_field").val(), $("#isp_field").val());
                                $("#isp_names").append(o);
                                $("#isp_field").val("");
                            } else {
                                $.toast("Please enter a valid ISP name.");
                            }
                        });
                        $("#remove_isp").click(function() {
                            $('#isp_names option:selected').remove();
                        });

                        $(window).keypress(function(event) {
                            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
                        });

                        $("#total_clients").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#http_broadcast_port").inputFilter(function(value) {
                            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
                        });
                        $("#https_broadcast_port").inputFilter(function(value) {
                            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
                        });
                        $("#rtmp_port").inputFilter(function(value) {
                            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
                        });
                        $("#http_isp_port").inputFilter(function(value) {
                            return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535);
                        });
                        $("#diff_time_main").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("#network_guaranteed_speed").inputFilter(function(value) {
                            return /^\d*$/.test(value);
                        });
                        $("form").attr('autocomplete', 'off');
                    });
                </script>
                </body>

                </html>