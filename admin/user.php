<?php
include "session.php";
include "functions.php";
if ((!$rPermissions["is_admin"]) or ((!hasPermissions("adv", "add_user")) && (!hasPermissions("adv", "edit_user")))) {
    exit;
}
if (isset(ipTV_lib::$request["submit_user"])) {
    ipTV_lib::$request["mac_address_mag"] = strtoupper(ipTV_lib::$request["mac_address_mag"]);
    ipTV_lib::$request["mac_address_e2"] = strtoupper(ipTV_lib::$request["mac_address_e2"]);
    if (isset(ipTV_lib::$request["edit"])) {
        if (!hasPermissions("adv", "edit_user")) {
            exit;
        }
        $rArray = getUser(ipTV_lib::$request["edit"]);
        if (($rArray["is_mag"]) && (!hasPermissions("adv", "edit_mag"))) {
            exit;
        }
        if (($rArray["is_e2"]) && (!hasPermissions("adv", "edit_e2"))) {
            exit;
        }
        unset($rArray["id"]);
    } else {
        if (!hasPermissions("adv", "add_user")) {
            exit;
        }
        $rArray = array("member_id" => 0, "username" => "", "password" => "", "exp_date" => null, "admin_enabled" => 1, "enabled" => 1, "admin_notes" => "", "reseller_notes" => "", "bouquet" => array(), "max_connections" => 1, "is_restreamer" => 0, "allowed_ips" => array(), "allowed_ua" => array(), "created_at" => time(), "created_by" => -1, "is_mag" => 0, "is_e2" => 0, "force_server_id" => 0, "is_isplock" => 0, "isp_desc" => "", "forced_country" => "", "is_stalker" => 0, "bypass_ua" => 0, "play_token" => "");
    }
    if (strlen(ipTV_lib::$request["username"]) == 0) {
        ipTV_lib::$request["username"] = generateString(10);
    }
    if (strlen(ipTV_lib::$request["password"]) == 0) {
        ipTV_lib::$request["password"] = generateString(10);
    }
    if (!isset(ipTV_lib::$request["edit"])) {
        $ipTV_db_admin->query("SELECT `id` FROM `lines` WHERE `username` = ?;", ipTV_lib::$request["username"]);
        if ($ipTV_db_admin->num_rows() > 0) {
            $_STATUS = 3; // Username in use.
        }
    }
    if (((ipTV_lib::$request["is_mag"]) && (!filter_var(ipTV_lib::$request["mac_address_mag"], FILTER_VALIDATE_MAC))) or ((strlen(ipTV_lib::$request["mac_address_e2"]) > 0) && (!filter_var(ipTV_lib::$request["mac_address_e2"], FILTER_VALIDATE_MAC)))) {
        $_STATUS = 4;
    } elseif (ipTV_lib::$request["is_mag"]) {
        $ipTV_db_admin->query("SELECT `user_id` FROM `mag_devices` WHERE mac = '" . base64_encode(ipTV_lib::$request["mac_address_mag"]) . "' LIMIT 1;");
        if ($ipTV_db_admin->num_rows() > 0) {
            if (isset(ipTV_lib::$request["edit"])) {
                if (intval($ipTV_db_admin->get_row()["user_id"]) <> intval(ipTV_lib::$request["edit"])) {
                    $_STATUS = 5; // MAC in use.
                }
            } else {
                $_STATUS = 5; // MAC in use.
            }
        }
    } elseif (ipTV_lib::$request["is_e2"]) {
        $ipTV_db_admin->query("SELECT `user_id` FROM `enigma2_devices` WHERE mac = '" . ipTV_lib::$request["mac_address_e2"] . "' LIMIT 1;");
        if ($ipTV_db_admin->num_rows() > 0) {
            if (isset(ipTV_lib::$request["edit"])) {
                if (intval($ipTV_db_admin->get_row()["user_id"]) <> intval(ipTV_lib::$request["edit"])) {
                    $_STATUS = 5; // MAC in use.
                }
            } else {
                $_STATUS = 5; // MAC in use.
            }
        }
    }
    foreach (array("max_connections", "enabled", "admin_enabled") as $rSelection) {
        if (isset(ipTV_lib::$request[$rSelection])) {
            $rArray[$rSelection] = intval(ipTV_lib::$request[$rSelection]);
            unset(ipTV_lib::$request[$rSelection]);
        } else {
            $rArray[$rSelection] = 1;
        }
    }
    foreach (array("is_stalker", "is_e2", "is_mag", "is_restreamer", "is_trial") as $rSelection) {
        if (isset(ipTV_lib::$request[$rSelection])) {
            $rArray[$rSelection] = 1;
            unset(ipTV_lib::$request[$rSelection]);
        } else {
            $rArray[$rSelection] = 0;
        }
    }
    $rArray["bouquet"] = sortArrayByArray(array_values(json_decode(ipTV_lib::$request["bouquets_selected"], true)), array_keys(getBouquetOrder()));
    $rArray["bouquet"] = "[" . join(",", $rArray["bouquet"]) . "]";
    unset(ipTV_lib::$request["bouquets_selected"]);
    if ((isset(ipTV_lib::$request["exp_date"])) && (!isset(ipTV_lib::$request["no_expire"]))) {
        if ((strlen(ipTV_lib::$request["exp_date"]) > 0) and (ipTV_lib::$request["exp_date"] <> "1970-01-01")) {
            try {
                $rDate = new DateTime(ipTV_lib::$request["exp_date"]);
                $rArray["exp_date"] = $rDate->format("U");
            } catch (Exception $e) {
                echo "Incorrect date.";
                $_STATUS = 1;
            }
        }
        unset(ipTV_lib::$request["exp_date"]);
    } else {
        $rArray["exp_date"] = null;
    }
    if (isset(ipTV_lib::$request["allowed_ips"])) {
        if (!is_array(ipTV_lib::$request["allowed_ips"])) {
            ipTV_lib::$request["allowed_ips"] = array(ipTV_lib::$request["allowed_ips"]);
        }
        $rArray["allowed_ips"] = json_encode(ipTV_lib::$request["allowed_ips"]);
    } else {
        $rArray["allowed_ips"] = "[]";
    }
    if (isset(ipTV_lib::$request["allowed_ua"])) {
        if (!is_array(ipTV_lib::$request["allowed_ua"])) {
            ipTV_lib::$request["allowed_ua"] = array(ipTV_lib::$request["allowed_ua"]);
        }
        $rArray["allowed_ua"] = json_encode(ipTV_lib::$request["allowed_ua"]);
    } else {
        $rArray["allowed_ua"] = "[]";
    }
    if (isset(ipTV_lib::$request["access_output"])) {
        if (!is_array(ipTV_lib::$request["access_output"])) {
            ipTV_lib::$request["access_output"] = array(ipTV_lib::$request["access_output"]);
        }
        $rArray["allowed_outputs"] = json_encode(ipTV_lib::$request["access_output"]);
    } else {
        $rArray["allowed_outputs"] = "[]";
    }
    //isp lock_device
    if (isset(ipTV_lib::$request["is_isplock"])) {
        $rArray["is_isplock"] = true;
        unset(ipTV_lib::$request["is_isplock"]);
    } else {
        $rArray["is_isplock"] = false;
    }
    //isp lock_device
    if (!isset($_STATUS)) {
        foreach (ipTV_lib::$request as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
        }
        if (!$rArray["member_id"]) {
            $rArray["member_id"] = -1;
        }
        $rArray["created_by"] = $rArray["member_id"];
        $rCols = "`" . implode('`,`', array_keys($rArray)) . "`";
        foreach (array_values($rArray) as $rValue) {
            isset($rValues) ? $rValues .= ',' : $rValues = '';
            if (is_array($rValue)) {
                $rValue = json_encode($rValue);
            }
            if (is_null($rValue)) {
                $rValues .= 'NULL';
            } else {
                $rValues .= '\'' . $rValue . '\'';
            }
        }
        if (isset(ipTV_lib::$request["edit"])) {
            $rCols = "`id`," . $rCols;
            $rValues = ipTV_lib::$request["edit"] . "," . $rValues;
        }
        $rQuery = "REPLACE INTO `lines`(" . $rCols . ") VALUES(" . $rValues . ");";
        if ($ipTV_db_admin->query($rQuery)) {
            if (isset(ipTV_lib::$request["edit"])) {
                $rInsertID = intval(ipTV_lib::$request["edit"]);
            } else {
                $rInsertID = $ipTV_db_admin->last_insert_id();
            }
            if ((isset($rInsertID)) && (isset(ipTV_lib::$request["access_output"]))) {
                if ($rArray["is_mag"] == 1) {
                    if (hasPermissions("adv", "add_mag")) {
                        if (isset(ipTV_lib::$request["lock_device"])) {
                            $rSTBLock = 1;
                        } else {
                            $rSTBLock = 0;
                        }
                        $ipTV_db_admin->query("SELECT `mag_id` FROM `mag_devices` WHERE `user_id` = " . intval($rInsertID) . " LIMIT 1;");
                        if ($ipTV_db_admin->num_rows() == 0) {
                            $ipTV_db_admin->query("INSERT INTO `mag_devices`(`user_id`, `mac`, `lock_device`) VALUES(" . intval($rInsertID) . ", '" . base64_encode(ipTV_lib::$request["mac_address_mag"]) . "', " . intval($rSTBLock) . ");");
                        } else {
                            $ipTV_db_admin->query("UPDATE `mag_devices` SET `mac` = '" . base64_encode(ipTV_lib::$request["mac_address_mag"]) . "', `lock_device` = " . intval($rSTBLock) . " WHERE `user_id` = " . intval($rInsertID) . ";");
                        }
                        if (isset(ipTV_lib::$request["edit"])) {
                            $ipTV_db_admin->query("DELETE FROM `enigma2_devices` WHERE `user_id` = " . intval($rInsertID) . ";");
                        }
                    }
                } elseif ($rArray["is_e2"] == 1) {
                    if (hasPermissions("adv", "add_e2")) {
                        $ipTV_db_admin->query("SELECT `device_id` FROM `enigma2_devices` WHERE `user_id` = " . intval($rInsertID) . " LIMIT 1;");
                        if ($ipTV_db_admin->num_rows() == 0) {
                            $ipTV_db_admin->query("INSERT INTO `enigma2_devices`(`user_id`, `mac`) VALUES(" . intval($rInsertID) . ", '" . ipTV_lib::$request["mac_address_e2"] . "');");
                        } else {
                            $ipTV_db_admin->query("UPDATE `enigma2_devices` SET `mac` = '" . ipTV_lib::$request["mac_address_e2"] . "' WHERE `user_id` = " . intval($rInsertID) . ";");
                        }
                        if (isset(ipTV_lib::$request["edit"])) {
                            $ipTV_db_admin->query("DELETE FROM `mag_devices` WHERE `user_id` = " . intval($rInsertID) . ";");
                        }
                    }
                } elseif (isset(ipTV_lib::$request["edit"])) {
                    $ipTV_db_admin->query("DELETE FROM `mag_devices` WHERE `user_id` = " . intval($rInsertID) . ";");
                    $ipTV_db_admin->query("DELETE FROM `enigma2_devices` WHERE `user_id` = " . intval($rInsertID) . ";");
                }
            }
            header("Location: ./user.php?id=" . $rInsertID);
            exit;
        } else {
            $_STATUS = 2;
        }
    }
}

if (isset(ipTV_lib::$request["id"])) {
    $rUser = getUser(ipTV_lib::$request["id"]);
    if ((!$rUser) or (!hasPermissions("adv", "edit_user"))) {
        exit;
    }
    if (($rUser["is_mag"]) && (!hasPermissions("adv", "edit_mag"))) {
        exit;
    }
    if (($rUser["is_e2"]) && (!hasPermissions("adv", "edit_e2"))) {
        exit;
    }
    $rMAGUser = getMAGUser(ipTV_lib::$request["id"]);
    if (($rUser["is_mag"])) {
        $rUser["lock_device"] = $rMAGUser["lock_device"];
        $rUser["mac_address_mag"] = base64_decode($rMAGUser["mac"]);
    }
    if (($rUser["is_e2"])) {
        $rUser["mac_address_e2"] = getE2User(ipTV_lib::$request["id"])["mac"];
    }
    $rUser["outputs"] = getOutputs($rUser["id"]);
} elseif (!hasPermissions("adv", "add_user")) {
    exit;
}

$rRegisteredUsers = getRegisteredUsers();
$rCountries = array(array("id" => "", "name" => "Off"), array("id" => "A1", "name" => "Anonymous Proxy"), array("id" => "A2", "name" => "Satellite Provider"), array("id" => "O1", "name" => "Other Country"), array("id" => "AF", "name" => "Afghanistan"), array("id" => "AX", "name" => "Aland Islands"), array("id" => "AL", "name" => "Albania"), array("id" => "DZ", "name" => "Algeria"), array("id" => "AS", "name" => "American Samoa"), array("id" => "AD", "name" => "Andorra"), array("id" => "AO", "name" => "Angola"), array("id" => "AI", "name" => "Anguilla"), array("id" => "AQ", "name" => "Antarctica"), array("id" => "AG", "name" => "Antigua And Barbuda"), array("id" => "AR", "name" => "Argentina"), array("id" => "AM", "name" => "Armenia"), array("id" => "AW", "name" => "Aruba"), array("id" => "AU", "name" => "Australia"), array("id" => "AT", "name" => "Austria"), array("id" => "AZ", "name" => "Azerbaijan"), array("id" => "BS", "name" => "Bahamas"), array("id" => "BH", "name" => "Bahrain"), array("id" => "BD", "name" => "Bangladesh"), array("id" => "BB", "name" => "Barbados"), array("id" => "BY", "name" => "Belarus"), array("id" => "BE", "name" => "Belgium"), array("id" => "BZ", "name" => "Belize"), array("id" => "BJ", "name" => "Benin"), array("id" => "BM", "name" => "Bermuda"), array("id" => "BT", "name" => "Bhutan"), array("id" => "BO", "name" => "Bolivia"), array("id" => "BA", "name" => "Bosnia And Herzegovina"), array("id" => "BW", "name" => "Botswana"), array("id" => "BV", "name" => "Bouvet Island"), array("id" => "BR", "name" => "Brazil"), array("id" => "IO", "name" => "British Indian Ocean Territory"), array("id" => "BN", "name" => "Brunei Darussalam"), array("id" => "BG", "name" => "Bulgaria"), array("id" => "BF", "name" => "Burkina Faso"), array("id" => "BI", "name" => "Burundi"), array("id" => "KH", "name" => "Cambodia"), array("id" => "CM", "name" => "Cameroon"), array("id" => "CA", "name" => "Canada"), array("id" => "CV", "name" => "Cape Verde"), array("id" => "KY", "name" => "Cayman Islands"), array("id" => "CF", "name" => "Central African Republic"), array("id" => "TD", "name" => "Chad"), array("id" => "CL", "name" => "Chile"), array("id" => "CN", "name" => "China"), array("id" => "CX", "name" => "Christmas Island"), array("id" => "CC", "name" => "Cocos (Keeling) Islands"), array("id" => "CO", "name" => "Colombia"), array("id" => "KM", "name" => "Comoros"), array("id" => "CG", "name" => "Congo"), array("id" => "CD", "name" => "Congo, Democratic Republic"), array("id" => "CK", "name" => "Cook Islands"), array("id" => "CR", "name" => "Costa Rica"), array("id" => "CI", "name" => "Cote D'Ivoire"), array("id" => "HR", "name" => "Croatia"), array("id" => "CU", "name" => "Cuba"), array("id" => "CY", "name" => "Cyprus"), array("id" => "CZ", "name" => "Czech Republic"), array("id" => "DK", "name" => "Denmark"), array("id" => "DJ", "name" => "Djibouti"), array("id" => "DM", "name" => "Dominica"), array("id" => "DO", "name" => "Dominican Republic"), array("id" => "EC", "name" => "Ecuador"), array("id" => "EG", "name" => "Egypt"), array("id" => "SV", "name" => "El Salvador"), array("id" => "GQ", "name" => "Equatorial Guinea"), array("id" => "ER", "name" => "Eritrea"), array("id" => "EE", "name" => "Estonia"), array("id" => "ET", "name" => "Ethiopia"), array("id" => "FK", "name" => "Falkland Islands (Malvinas)"), array("id" => "FO", "name" => "Faroe Islands"), array("id" => "FJ", "name" => "Fiji"), array("id" => "FI", "name" => "Finland"), array("id" => "FR", "name" => "France"), array("id" => "GF", "name" => "French Guiana"), array("id" => "PF", "name" => "French Polynesia"), array("id" => "TF", "name" => "French Southern Territories"), array("id" => "MK", "name" => "Fyrom"), array("id" => "GA", "name" => "Gabon"), array("id" => "GM", "name" => "Gambia"), array("id" => "GE", "name" => "Georgia"), array("id" => "DE", "name" => "Germany"), array("id" => "GH", "name" => "Ghana"), array("id" => "GI", "name" => "Gibraltar"), array("id" => "GR", "name" => "Greece"), array("id" => "GL", "name" => "Greenland"), array("id" => "GD", "name" => "Grenada"), array("id" => "GP", "name" => "Guadeloupe"), array("id" => "GU", "name" => "Guam"), array("id" => "GT", "name" => "Guatemala"), array("id" => "GG", "name" => "Guernsey"), array("id" => "GN", "name" => "Guinea"), array("id" => "GW", "name" => "Guinea-Bissau"), array("id" => "GY", "name" => "Guyana"), array("id" => "HT", "name" => "Haiti"), array("id" => "HM", "name" => "Heard Island & Mcdonald Islands"), array("id" => "VA", "name" => "Holy See (Vatican City State)"), array("id" => "HN", "name" => "Honduras"), array("id" => "HK", "name" => "Hong Kong"), array("id" => "HU", "name" => "Hungary"), array("id" => "IS", "name" => "Iceland"), array("id" => "IN", "name" => "India"), array("id" => "ID", "name" => "Indonesia"), array("id" => "IR", "name" => "Iran, Islamic Republic Of"), array("id" => "IQ", "name" => "Iraq"), array("id" => "IE", "name" => "Ireland"), array("id" => "IM", "name" => "Isle Of Man"), array("id" => "IL", "name" => "Israel"), array("id" => "IT", "name" => "Italy"), array("id" => "JM", "name" => "Jamaica"), array("id" => "JP", "name" => "Japan"), array("id" => "JE", "name" => "Jersey"), array("id" => "JO", "name" => "Jordan"), array("id" => "KZ", "name" => "Kazakhstan"), array("id" => "KE", "name" => "Kenya"), array("id" => "KI", "name" => "Kiribati"), array("id" => "KR", "name" => "Korea"), array("id" => "KW", "name" => "Kuwait"), array("id" => "KG", "name" => "Kyrgyzstan"), array("id" => "LA", "name" => "Lao People's Democratic Republic"), array("id" => "LV", "name" => "Latvia"), array("id" => "LB", "name" => "Lebanon"), array("id" => "LS", "name" => "Lesotho"), array("id" => "LR", "name" => "Liberia"), array("id" => "LY", "name" => "Libyan Arab Jamahiriya"), array("id" => "LI", "name" => "Liechtenstein"), array("id" => "LT", "name" => "Lithuania"), array("id" => "LU", "name" => "Luxembourg"), array("id" => "MO", "name" => "Macao"), array("id" => "MG", "name" => "Madagascar"), array("id" => "MW", "name" => "Malawi"), array("id" => "MY", "name" => "Malaysia"), array("id" => "MV", "name" => "Maldives"), array("id" => "ML", "name" => "Mali"), array("id" => "MT", "name" => "Malta"), array("id" => "MH", "name" => "Marshall Islands"), array("id" => "MQ", "name" => "Martinique"), array("id" => "MR", "name" => "Mauritania"), array("id" => "MU", "name" => "Mauritius"), array("id" => "YT", "name" => "Mayotte"), array("id" => "MX", "name" => "Mexico"), array("id" => "FM", "name" => "Micronesia, Federated States Of"), array("id" => "MD", "name" => "Moldova"), array("id" => "MC", "name" => "Monaco"), array("id" => "MN", "name" => "Mongolia"), array("id" => "ME", "name" => "Montenegro"), array("id" => "MS", "name" => "Montserrat"), array("id" => "MA", "name" => "Morocco"), array("id" => "MZ", "name" => "Mozambique"), array("id" => "MM", "name" => "Myanmar"), array("id" => "NA", "name" => "Namibia"), array("id" => "NR", "name" => "Nauru"), array("id" => "NP", "name" => "Nepal"), array("id" => "NL", "name" => "Netherlands"), array("id" => "AN", "name" => "Netherlands Antilles"), array("id" => "NC", "name" => "New Caledonia"), array("id" => "NZ", "name" => "New Zealand"), array("id" => "NI", "name" => "Nicaragua"), array("id" => "NE", "name" => "Niger"), array("id" => "NG", "name" => "Nigeria"), array("id" => "NU", "name" => "Niue"), array("id" => "NF", "name" => "Norfolk Island"), array("id" => "MP", "name" => "Northern Mariana Islands"), array("id" => "NO", "name" => "Norway"), array("id" => "OM", "name" => "Oman"), array("id" => "PK", "name" => "Pakistan"), array("id" => "PW", "name" => "Palau"), array("id" => "PS", "name" => "Palestinian Territory, Occupied"), array("id" => "PA", "name" => "Panama"), array("id" => "PG", "name" => "Papua New Guinea"), array("id" => "PY", "name" => "Paraguay"), array("id" => "PE", "name" => "Peru"), array("id" => "PH", "name" => "Philippines"), array("id" => "PN", "name" => "Pitcairn"), array("id" => "PL", "name" => "Poland"), array("id" => "PT", "name" => "Portugal"), array("id" => "PR", "name" => "Puerto Rico"), array("id" => "QA", "name" => "Qatar"), array("id" => "RE", "name" => "Reunion"), array("id" => "RO", "name" => "Romania"), array("id" => "RU", "name" => "Russian Federation"), array("id" => "RW", "name" => "Rwanda"), array("id" => "BL", "name" => "Saint Barthelemy"), array("id" => "SH", "name" => "Saint Helena"), array("id" => "KN", "name" => "Saint Kitts And Nevis"), array("id" => "LC", "name" => "Saint Lucia"), array("id" => "MF", "name" => "Saint Martin"), array("id" => "PM", "name" => "Saint Pierre And Miquelon"), array("id" => "VC", "name" => "Saint Vincent And Grenadines"), array("id" => "WS", "name" => "Samoa"), array("id" => "SM", "name" => "San Marino"), array("id" => "ST", "name" => "Sao Tome And Principe"), array("id" => "SA", "name" => "Saudi Arabia"), array("id" => "SN", "name" => "Senegal"), array("id" => "RS", "name" => "Serbia"), array("id" => "SC", "name" => "Seychelles"), array("id" => "SL", "name" => "Sierra Leone"), array("id" => "SG", "name" => "Singapore"), array("id" => "SK", "name" => "Slovakia"), array("id" => "SI", "name" => "Slovenia"), array("id" => "SB", "name" => "Solomon Islands"), array("id" => "SO", "name" => "Somalia"), array("id" => "ZA", "name" => "South Africa"), array("id" => "GS", "name" => "South Georgia And Sandwich Isl."), array("id" => "ES", "name" => "Spain"), array("id" => "LK", "name" => "Sri Lanka"), array("id" => "SD", "name" => "Sudan"), array("id" => "SR", "name" => "Suriname"), array("id" => "SJ", "name" => "Svalbard And Jan Mayen"), array("id" => "SZ", "name" => "Swaziland"), array("id" => "SE", "name" => "Sweden"), array("id" => "CH", "name" => "Switzerland"), array("id" => "SY", "name" => "Syrian Arab Republic"), array("id" => "TW", "name" => "Taiwan"), array("id" => "TJ", "name" => "Tajikistan"), array("id" => "TZ", "name" => "Tanzania"), array("id" => "TH", "name" => "Thailand"), array("id" => "TL", "name" => "Timor-Leste"), array("id" => "TG", "name" => "Togo"), array("id" => "TK", "name" => "Tokelau"), array("id" => "TO", "name" => "Tonga"), array("id" => "TT", "name" => "Trinidad And Tobago"), array("id" => "TN", "name" => "Tunisia"), array("id" => "TR", "name" => "Turkey"), array("id" => "TM", "name" => "Turkmenistan"), array("id" => "TC", "name" => "Turks And Caicos Islands"), array("id" => "TV", "name" => "Tuvalu"), array("id" => "UG", "name" => "Uganda"), array("id" => "UA", "name" => "Ukraine"), array("id" => "AE", "name" => "United Arab Emirates"), array("id" => "GB", "name" => "United Kingdom"), array("id" => "US", "name" => "United States"), array("id" => "UM", "name" => "United States Outlying Islands"), array("id" => "UY", "name" => "Uruguay"), array("id" => "UZ", "name" => "Uzbekistan"), array("id" => "VU", "name" => "Vanuatu"), array("id" => "VE", "name" => "Venezuela"), array("id" => "VN", "name" => "Viet Nam"), array("id" => "VG", "name" => "Virgin Islands, British"), array("id" => "VI", "name" => "Virgin Islands, U.S."), array("id" => "WF", "name" => "Wallis And Futuna"), array("id" => "EH", "name" => "Western Sahara"), array("id" => "YE", "name" => "Yemen"), array("id" => "ZM", "name" => "Zambia"), array("id" => "ZW", "name" => "Zimbabwe"));

include "header.php";
?>
<div class="wrapper boxed-layout">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="./users.php<?php if (isset(ipTV_lib::$request["mag"])) {
                                                    echo "?mag";
                                                } elseif (isset(ipTV_lib::$request["e2"])) {
                                                    echo "?e2";
                                                } ?>">
                                <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                    <?= $_["back_to_users"] ?></li>
                            </a>
                        </ol>
                    </div>
                    <h4 class="page-title"><?php if (isset($rUser)) {
                                                echo $_["edit"];
                                            } else {
                                                echo $_["add"];
                                            } ?> <?= $_["user"] ?></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <?php if (isset($_STATUS)) {
                    if ($_STATUS == 0) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["user_operation_was_completed_successfully"] ?>
                        </div>
                    <?php } elseif ($_STATUS == 1) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["an_incorrect_expiration_date_was_entered"] ?>
                        </div>
                    <?php } elseif ($_STATUS == 2) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["generic_fail"] ?>
                        </div>
                    <?php } elseif ($_STATUS == 3) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["this_username_already_exists"] ?>
                        </div>
                    <?php } elseif ($_STATUS == 4) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["an_invalid_mac_address_was_entered"] ?>
                        </div>
                    <?php } elseif ($_STATUS == 5) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?= $_["this_mac_address_is_already_in_use"] ?>
                        </div>
                <?php }
                } ?>
                <div class="card">
                    <div class="card-body">
                        <form action="./user.php<?php if (isset(ipTV_lib::$request["id"])) {
                                                    echo "?id=" . ipTV_lib::$request["id"];
                                                } ?>" method="POST" id="user_form" data-parsley-validate="">
                            <?php if (isset($rUser)) { ?>
                                <input type="hidden" name="edit" value="<?= $rUser["id"] ?>" />
                                <input type="hidden" name="admin_enabled" value="<?= $rUser["admin_enabled"] ?>" />
                                <input type="hidden" name="enabled" value="<?= $rUser["enabled"] ?>" />
                            <?php } ?>
                            <input type="hidden" name="bouquets_selected" id="bouquets_selected" value="" />
                            <div id="basicwizard">
                                <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                    <li class="nav-item">
                                        <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#advanced-options" data-toggle="tab"
                                            class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["advanced"] ?></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#restrictions" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-hazard-lights mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["restrictions"] ?></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#bouquets" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-flower-tulip mr-1"></i>
                                            <span class="d-none d-sm-inline"><?= $_["bouquets"] ?></span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content b-0 mb-0 pt-0">
                                    <div class="tab-pane" id="user-details">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="username"><?= $_["username"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="username"
                                                            name="username"
                                                            placeholder="<?= $_["auto_generate_if_blank"] ?>" value="<?php if (isset($rUser)) {
                                                                                                                            echo htmlspecialchars($rUser["username"]);
                                                                                                                        } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="password"><?= $_["password"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="password"
                                                            name="password"
                                                            placeholder="<?= $_["auto_generate_if_blank"] ?>" value="<?php if (isset($rUser)) {
                                                                                                                            echo htmlspecialchars($rUser["password"]);
                                                                                                                        } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="member_id"><?= $_["owner"] ?></label>
                                                    <div class="col-md-8">
                                                        <select name="member_id" id="member_id"
                                                            class="form-control select2" data-toggle="select2">
                                                            <option value="-1"><?= $_["no_owner"] ?>
                                                            </option>
                                                            <?php foreach ($rRegisteredUsers as $rRegisteredUser) { ?>
                                                                <option <?php if (isset($rUser)) {
                                                                            if (intval($rUser["member_id"]) == intval($rRegisteredUser["id"])) {
                                                                                echo "selected ";
                                                                            }
                                                                        } else {
                                                                            if (intval($rUserInfo["id"]) == intval($rRegisteredUser["id"])) {
                                                                                echo "selected ";
                                                                            }
                                                                        } ?>value="<?= $rRegisteredUser["id"] ?>">
                                                                    <?= $rRegisteredUser["username"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="max_connections"><?= $_["max_connections"] ?></label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="max_connections"
                                                            name="max_connections" value="<?php if (isset($rUser)) {
                                                                                                echo htmlspecialchars($rUser["max_connections"]);
                                                                                            } else {
                                                                                                echo "1";
                                                                                            } ?>" required
                                                            data-parsley-trigger="<?= $_["change"] ?>">
                                                    </div>
                                                    <label class="col-md-2 col-form-label"
                                                        for="exp_date"><?= $_["expiry"] ?> <i data-toggle="tooltip"
                                                            data-placement="top" title=""
                                                            data-original-title="<?= $_["leave_blank_for_unlimited"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2"
                                                        style="padding-right: 0px; padding-left: 0px;">
                                                        <input type="text"
                                                            style="padding-right: 1px; padding-left: 1px;"
                                                            class="form-control text-center datetime" id="exp_date"
                                                            name="exp_date" value="<?php if (isset($rUser)) {
                                                                                        if (!is_null($rUser["exp_date"])) {
                                                                                            echo date("Y-m-d HH:mm", $rUser["exp_date"]);
                                                                                        } else {
                                                                                            echo "\" disabled=\"disabled";
                                                                                        }
                                                                                    } ?>" data-toggle="date-picker"
                                                            data-single-date-picker="true">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="custom-control custom-checkbox mt-1">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="no_expire" name="no_expire" <?php if (isset($rUser)) {
                                                                                                    if (is_null($rUser["exp_date"])) {
                                                                                                        echo " checked";
                                                                                                    }
                                                                                                } ?>>
                                                            <label class="custom-control-label"
                                                                for="no_expire"><?= $_["never"] ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="admin_notes"><?= $_["admin_notes"] ?></label>
                                                    <div class="col-md-8">
                                                        <textarea id="admin_notes" name="admin_notes"
                                                            class="form-control" rows="3" placeholder=""><?php if (isset($rUser)) {
                                                                                                                echo htmlspecialchars($rUser["admin_notes"]);
                                                                                                            } ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="reseller_notes"><?= $_["reseller_notes"] ?></label>
                                                    <div class="col-md-8">
                                                        <textarea id="reseller_notes" name="reseller_notes"
                                                            class="form-control" rows="3" placeholder=""><?php if (isset($rUser)) {
                                                                                                                echo htmlspecialchars($rUser["reseller_notes"]);
                                                                                                            } ?></textarea>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="next list-inline-item float-right">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["next"] ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" id="advanced-options">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="force_server_id"><?= $_["forced_connection"] ?>
                                                        <i data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="<?= $_["force_this_user_to_connect_to"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-8">
                                                        <select name="force_server_id" id="force_server_id"
                                                            class="form-control select2" data-toggle="select2">
                                                            <option <?php if (isset($rUser)) {
                                                                        if (intval($rUser["force_server_id"]) == 0) {
                                                                            echo "selected ";
                                                                        }
                                                                    } ?>value="0">
                                                                <?= $_["disabled"] ?>
                                                            </option>
                                                            <?php foreach ($rServers as $rServer) { ?>
                                                                <option <?php if (isset($rUser)) {
                                                                            if (intval($rUser["force_server_id"]) == intval($rServer["id"])) {
                                                                                echo "selected ";
                                                                            }
                                                                        } ?>value="<?= $rServer["id"] ?>">
                                                                    <?= htmlspecialchars($rServer["server_name"]) ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="is_stalker"><?= $_["ministra_portal"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="<?= $_["select_this_option"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="is_stalker" id="is_stalker" type="checkbox" <?php if (isset($rUser)) {
                                                                                                                        if ($rUser["is_stalker"] == 1) {
                                                                                                                            echo "checked ";
                                                                                                                        }
                                                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="is_restreamer"><?= $_["restreamer"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="<?= $_["if_selected_this_user"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input name="is_restreamer" id="is_restreamer" type="checkbox"
                                                            <?php if (isset($rUser)) {
                                                                if ($rUser["is_restreamer"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } ?>data-plugin="switchery"
                                                            class="js-switch" data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="is_e2"><?= $_["enigma_device"] ?> <i data-toggle="tooltip"
                                                            data-placement="top" title=""
                                                            data-original-title="<?= $_["this_option_will_be_selected_enigma"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input <?php if (!hasPermissions("adv", "add_e2")) {
                                                                    echo "disabled ";
                                                                } ?>name="is_e2" id="is_e2" type="checkbox"
                                                            <?php if (isset($rUser)) {
                                                                if ($rUser["is_e2"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } elseif ((isset(ipTV_lib::$request["e2"])) && (hasPermissions("adv", "add_e2"))) {
                                                                echo "checked ";
                                                            } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="is_mag"><?= $_["mag_device"] ?> <i data-toggle="tooltip"
                                                            data-placement="top" title=""
                                                            data-original-title="<?= $_["this_option_will_be_selected_mag"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-2">
                                                        <input <?php if (!hasPermissions("adv", "add_mag")) {
                                                                    echo "disabled ";
                                                                } ?>name="is_mag" id="is_mag" type="checkbox"
                                                            <?php if (isset($rUser)) {
                                                                if ($rUser["is_mag"] == 1) {
                                                                    echo "checked ";
                                                                }
                                                            } elseif ((isset(ipTV_lib::$request["mag"])) && (hasPermissions("adv", "add_mag"))) {
                                                                echo "checked ";
                                                            } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="is_trial"><?= $_["trial_account"] ?></label>
                                                    <div class="col-md-2">
                                                        <input name="is_trial" id="is_trial" type="checkbox" <?php if (isset($rUser)) {
                                                                                                                    if ($rUser["is_trial"] == 1) {
                                                                                                                        echo "checked ";
                                                                                                                    }
                                                                                                                } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                    <label class="col-md-4 col-form-label"
                                                        for="lock_device"><?= $_["mag_stb_lock"] ?></label>
                                                    <div class="col-md-2">
                                                        <input name="lock_device" id="lock_device" type="checkbox" <?php if (isset($rUser)) {
                                                                                                                        if ($rUser["lock_device"] == 1) {
                                                                                                                            echo "checked ";
                                                                                                                        }
                                                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label" for="is_isplock">ISP
                                                        LOCK</label>
                                                    <div class="col-md-2">
                                                        <input name="is_isplock" id="is_isplock" type="checkbox" <?php if (isset($rUser)) {
                                                                                                                        if ($rUser["is_isplock"] == 1) {
                                                                                                                            echo "checked ";
                                                                                                                        }
                                                                                                                    } ?>data-plugin="switchery" class="js-switch"
                                                            data-color="#039cfd" />
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4" style="display:none"
                                                    id="mac_entry_mag">
                                                    <label class="col-md-4 col-form-label"
                                                        for="mac_address_mag"><?= $_["mac_address"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="mac_address_mag"
                                                            name="mac_address_mag" value="<?php if (isset($rUser)) {
                                                                                                echo htmlspecialchars($rUser["mac_address_mag"]);
                                                                                            } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4" style="display:none" id="mac_entry_e2">
                                                    <label class="col-md-4 col-form-label"
                                                        for="mac_address_e2"><?= $_["mac_address"] ?></label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="mac_address_e2"
                                                            name="mac_address_e2" value="<?php if (isset($rUser)) {
                                                                                                echo htmlspecialchars($rUser["mac_address_e2"]);
                                                                                            } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="forced_country"><?= $_["forced_country"] ?> <i
                                                            data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title="<?= $_["force_user_to_connect"] ?>"
                                                            class="mdi mdi-information"></i></label>
                                                    <div class="col-md-8">
                                                        <select name="forced_country" id="forced_country"
                                                            class="form-control select2" data-toggle="select2">
                                                            <?php foreach ($rCountries as $rCountry) { ?>
                                                                <option <?php if (isset($rUser)) {
                                                                            if ($rUser["forced_country"] == $rCountry["id"]) {
                                                                                echo "selected ";
                                                                            }
                                                                        } ?>value="<?= $rCountry["id"] ?>">
                                                                    <?= $rCountry["name"] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="access_output"><?= $_["access_output"] ?></label>
                                                    <div class="col-md-8">
                                                        <?php foreach (getOutputs() as $rOutput) { ?>
                                                            <div class="checkbox form-check-inline">
                                                                <input data-size="large" type="checkbox"
                                                                    id="access_output_<?= $rOutput["access_output_id"] ?>"
                                                                    name="access_output[]"
                                                                    value="<?= $rOutput["access_output_id"] ?>" <?php if (isset($rUser)) {
                                                                                                                    if (in_array($rOutput["access_output_id"], $rUser["outputs"])) {
                                                                                                                        echo " checked";
                                                                                                                    }
                                                                                                                } else {
                                                                                                                    echo " checked";
                                                                                                                } ?>>
                                                                <label
                                                                    for="access_output_<?= $rOutput["access_output_id"] ?>">
                                                                    <?= $rOutput["output_name"] ?> </label>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="previous list-inline-item">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["prev"] ?></a>
                                            </li>
                                            <li class="next list-inline-item float-right">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["next"] ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" id="restrictions">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="ip_field"><?= $_["allowed_ip_addresses"] ?></label>
                                                    <div class="col-md-8 input-group">
                                                        <input type="text" id="ip_field" class="form-control" value="">
                                                        <div class="input-group-append">
                                                            <a href="javascript:void(0)" id="add_ip"
                                                                class="btn btn-primary waves-effect waves-light"><i
                                                                    class="mdi mdi-plus"></i></a>
                                                            <a href="javascript:void(0)" id="remove_ip"
                                                                class="btn btn-danger waves-effect waves-light"><i
                                                                    class="mdi mdi-close"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="allowed_ips">&nbsp;</label>
                                                    <div class="col-md-8">
                                                        <select id="allowed_ips" name="allowed_ips[]" size=6
                                                            class="form-control" multiple="multiple">
                                                            <?php if (isset($rUser)) {
                                                                foreach (json_decode($rUser["allowed_ips"], true) as $rIP) { ?>
                                                                    <option value="<?= $rIP ?>"><?= $rIP ?></option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="ua_field"><?= $_["allowed_user-agents"] ?></label>
                                                    <div class="col-md-8 input-group">
                                                        <input type="text" id="ua_field" class="form-control" value="">
                                                        <div class="input-group-append">
                                                            <a href="javascript:void(0)" id="add_ua"
                                                                class="btn btn-primary waves-effect waves-light"><i
                                                                    class="mdi mdi-plus"></i></a>
                                                            <a href="javascript:void(0)" id="remove_ua"
                                                                class="btn btn-danger waves-effect waves-light"><i
                                                                    class="mdi mdi-close"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-md-4 col-form-label"
                                                        for="allowed_ua">&nbsp;</label>
                                                    <div class="col-md-8">
                                                        <select id="allowed_ua" name="allowed_ua[]" size=6
                                                            class="form-control" multiple="multiple">
                                                            <?php if (isset($rUser)) {
                                                                foreach (json_decode($rUser["allowed_ua"], true) as $rUA) { ?>
                                                                    <option value="<?= $rUA ?>"><?= $rUA ?></option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="previous list-inline-item">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["prev"] ?></a>
                                            </li>
                                            <li class="next list-inline-item float-right">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["next"] ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" id="bouquets">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row mb-4">
                                                    <table id="datatable-bouquets" class="table table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="text-center"><?= $_["id"] ?></th>
                                                                <th><?= $_["bouquet_name"] ?></th>
                                                                <th class="text-center"><?= $_["streams"] ?>
                                                                </th>
                                                                <th class="text-center"><?= $_["series"] ?>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach (getBouquets() as $rBouquet) {
                                                                echo "<tr";
                                                                if (isset($rUser)) {
                                                                    if (in_array($rBouquet["id"], json_decode($rUser["bouquet"], true))) {
                                                                        echo " class='selected selectedfilter ui-selected'";
                                                                    }
                                                                }
                                                                echo ">"; ?>
                                                                <td class="text-center"><?= $rBouquet["id"] ?>
                                                                </td>
                                                                <td><?= $rBouquet["bouquet_name"] ?></td>
                                                                <td class="text-center">
                                                                    <?= count(json_decode($rBouquet["bouquet_channels"], true)) ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?= count(json_decode($rBouquet["bouquet_series"], true)) ?>
                                                                </td>
                                                                </>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row -->
                                        <ul class="list-inline wizard mb-0">
                                            <li class="previous list-inline-item">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-secondary"><?= $_["prev"] ?></a>
                                            </li>
                                            <li class="list-inline-item float-right">
                                                <a href="javascript: void(0);" onClick="toggleBouquets()"
                                                    class="btn btn-info"><?= $_["toggle_bouquets"] ?></a>
                                                <input name="submit_user" type="submit" class="btn btn-primary" value="<?php if (isset($rUser)) {
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
<script src="assets/libs/jquery-ui/jquery-ui.min.js"></script>
<script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
<script src="assets/libs/switchery/switchery.min.js"></script>
<script src="assets/libs/select2/select2.min.js"></script>
<script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
<script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
<script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
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
<script src="assets/libs/moment/moment.min.js"></script>
<script src="assets/libs/daterangepicker/daterangepicker.js"></script>
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/libs/treeview/jstree.min.js"></script>
<script src="assets/js/pages/treeview.init.js"></script>
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/libs/parsleyjs/parsley.min.js"></script>
<script src="assets/js/app.min.js"></script>
<style>
    .daterangepicker select.ampmselect,
    .daterangepicker select.hourselect,
    .daterangepicker select.minuteselect,
    .daterangepicker select.secondselect {
        background: #fff;
        border: 1px solid #fff;
        color: rgb(0, 0, 0)
    }
</style>


<script>
    var swObjs = {};
    <?php if (isset($rUser)) { ?>
        var rBouquets = <?= $rUser["bouquet"]; ?>;
    <?php } else { ?>
        var rBouquets = [];
    <?php } ?>

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

    function toggleBouquets() {
        $("#datatable-bouquets tr").each(function() {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                if ($(this).find("td:eq(0)").html()) {
                    window.rBouquets.splice(parseInt($.inArray($(this).find("td:eq(0)").html()), window.rBouquets), 1);
                }
            } else {
                $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                if ($(this).find("td:eq(0)").html()) {
                    window.rBouquets.push(parseInt($(this).find("td:eq(0)").html()));
                }
            }
        });
    }

    function isValidDate(dateString) {
        var regEx = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateString.match(regEx)) return false; // Invalid format
        var d = new Date(dateString);
        var dNum = d.getTime();
        if (!dNum && dNum !== 0) return false; // NaN value, Invalid date
        return d.toISOString().slice(0, 10) === dateString;
    }

    function isValidIP(rIP) {
        if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(rIP)) {
            return true;
        } else {
            return false;
        }
    }

    function evaluateForm() {
        if (($("#is_mag").is(":checked")) || ($("#is_e2").is(":checked"))) {
            if ($("#is_mag").is(":checked")) {
                <?php if (hasPermissions("adv", "add_mag")) { ?>
                    $("#mac_entry_mag").show();
                    window.swObjs["lock_device"].enable();
                <?php }
                if (hasPermissions("adv", "add_e2")) { ?>
                    window.swObjs["is_e2"].disable();
                <?php } ?>
            } else {
                <?php if (hasPermissions("adv", "add_mag")) { ?>
                    $("#mac_entry_e2").show();
                <?php }
                if (hasPermissions("adv", "add_e2")) { ?>
                    window.swObjs["is_mag"].disable();
                    window.swObjs["lock_device"].disable();
                <?php } ?>
            }
        } else {
            <?php if (hasPermissions("adv", "add_e2")) { ?>
                $("#mac_entry_e2").hide();
                window.swObjs["is_e2"].enable();
            <?php }
            if (hasPermissions("adv", "add_mag")) { ?>
                $("#mac_entry_mag").hide();
                window.swObjs["is_mag"].enable();
            <?php } ?>
            window.swObjs["lock_device"].disable();
        }
    }

    $(document).ready(function() {
        $('select.select2').select2({
            width: '100%'
        })
        $(".js-switch").each(function(index, element) {
            var init = new Switchery(element);
            window.swObjs[element.id] = init;
        });
        <?php if (hasPermissions("adv", "edit_user") && (!empty(ipTV_lib::$request["id"]))) {
            $startDate = "startDate: '" . date("Y-m-d H:i:s", $rUser["exp_date"]) . "'";
        } else {
            $startDate = "startDate: '" . date('Y-m-d H:i:s') . "'";
        }
        ?>
        $('#exp_date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            timePicker24Hour: true,
            timePicker: true,
            <?php echo $startDate; ?>,
            endDate: moment().startOf('hour').add(32, 'hour'),
            minDate: new Date(),
            locale: {
                format: 'YYYY-MM-DD HH:mm'
            }
        });

        $("#datatable-bouquets").DataTable({
            columnDefs: [{
                "className": "dt-center",
                "targets": [0, 2, 3]
            }],
            "rowCallback": function(row, data) {
                if ($.inArray(data[0], window.rBouquets) !== -1) {
                    $(row).addClass("selected");
                }
            },
            paging: false,
            bInfo: false,
            searching: false
        });
        $("#datatable-bouquets").selectable({
            filter: 'tr',
            selected: function(event, ui) {
                if ($(ui.selected).hasClass('selectedfilter')) {
                    $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    window.rBouquets.splice(parseInt($.inArray($(ui.selected).find("td:eq(0)").html()), window.rBouquets), 1);
                } else {
                    $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    window.rBouquets.push(parseInt($(ui.selected).find("td:eq(0)").html()));
                }
            }
        });

        $("#no_expire").change(function() {
            if ($(this).prop("checked")) {
                $("#exp_date").prop("disabled", true);
            } else {
                $("#exp_date").removeAttr("disabled");
            }
        });

        $(".js-switch").on("change", function() {
            evaluateForm();
        });

        $("#user_form").submit(function(e) {
            var rBouquets = [];
            $("#datatable-bouquets tr.selected").each(function() {
                rBouquets.push($(this).find("td:eq(0)").html());
            });
            $("#bouquets_selected").val(JSON.stringify(rBouquets));
            $("#allowed_ua option").prop('selected', true);
            $("#allowed_ips option").prop('selected', true);
        });
        $(document).keypress(function(e) {
            if (e.which == 13 && e.target.nodeName != "TEXTAREA") return false;
        });
        $("#add_ip").click(function() {
            if (($("#ip_field").val().length > 0) && (isValidIP($("#ip_field").val()))) {
                var o = new Option($("#ip_field").val(), $("#ip_field").val());
                $("#allowed_ips").append(o);
                $("#ip_field").val("");
            } else {
                $.toast("<?= $_["please_enter_a_valid_ip_address"] ?>");
            }
        });
        $("#remove_ip").click(function() {
            $('#allowed_ips option:selected').remove();
        });
        $("#add_ua").click(function() {
            if ($("#ua_field").val().length > 0) {
                var o = new Option($("#ua_field").val(), $("#ua_field").val());
                $("#allowed_ua").append(o);
                $("#ua_field").val("");
            } else {
                $.toast("<?= $_["please_enter_a_user_agent"] ?>");
            }
        });
        $("#remove_ua").click(function() {
            $('#allowed_ua option:selected').remove();
        });
        $("#max_connections").inputFilter(function(value) {
            return /^\d*$/.test(value);
        });
        $("form").attr('autocomplete', 'off');

        evaluateForm();
    });
</script>
</body>

</html>