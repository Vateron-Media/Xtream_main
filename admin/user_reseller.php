<?php
include "session.php";
include "functions.php";
if ($rPermissions["is_admin"]) {
    exit;
}

$rRegisteredUsers = getRegisteredUsers($rUserInfo["id"]);

if ((isset($_GET["trial"])) or (isset($_POST["trial"]))) {
    if ($rSettings["disable_trial"]) {
        $canGenerateTrials = False;
    } else if (floatval($rUserInfo["credits"]) < floatval($rPermissions["minimum_trial_credits"])) {
        $canGenerateTrials = False;
    } else {
        $canGenerateTrials = checkTrials();
    }
} else {
    $canGenerateTrials = True;
}

if (isset($_POST["submit_user"])) {
    $_POST["mac_address_mag"] = strtoupper($_POST["mac_address_mag"]);
    $_POST["mac_address_e2"] = strtoupper($_POST["mac_address_e2"]);
    if (isset($_POST["edit"])) {
        if (!hasPermissions("user", $_POST["edit"])) {
            exit;
        }
        $rUser = getUser($_POST["edit"]);
        if (!$rUser) {
            exit;
        }
    }
    if (isset($rUser)) {
        $rArray = $rUser;
        unset($rArray["id"]);
    } else {
        $rArray = array("member_id" => 0, "username" => "", "password" => "", "exp_date" => null, "admin_enabled" => 1, "enabled" => 1, "admin_notes" => "", "reseller_notes" => "", "bouquet" => array(), "max_connections" => 1, "is_restreamer" => 0, "allowed_ips" => array(), "allowed_ua" => array(), "created_at" => time(), "created_by" => -1, "is_mag" => 0, "is_e2" => 0, "force_server_id" => 0, "is_isplock" => 0, "isp_desc" => "", "forced_country" => "", "is_stalker" => 0, "bypass_ua" => 0, "play_token" => "");
    }
    if (!empty($_POST["package"])) {
        $rPackage = getPackage($_POST["package"]);
        // Check package is within permissions.
        if (($rPackage) && (in_array($rUserInfo["member_group_id"], json_decode($rPackage["groups"], True)))) {
            // Ignore post and get information from package instead.
            if ($_POST["trial"]) {
                $rCost = floatval($rPackage["trial_credits"]);
            } else {
                $rOverride = json_decode($rUserInfo["override_packages"], True);
                if ((isset($rOverride[$rPackage["id"]]["official_credits"])) && (strlen($rOverride[$rPackage["id"]]["official_credits"]) > 0)) {
                    $rCost = floatval($rOverride[$rPackage["id"]]["official_credits"]);
                } else {
                    $rCost = floatval($rPackage["official_credits"]);
                }
            }
            if ((floatval($rUserInfo["credits"]) >= $rCost) && ($canGenerateTrials)) {
                if ($_POST["trial"]) {
                    $rArray["exp_date"] = strtotime('+' . intval($rPackage["trial_duration"]) . ' ' . $rPackage["trial_duration_in"]);
                    $rArray["is_trial"] = 1;
                } else {
                    if (isset($rUser)) {
                        if ($rUser["exp_date"] >= time()) {
                            $rArray["exp_date"] = strtotime('+' . intval($rPackage["official_duration"]) . ' ' . $rPackage["official_duration_in"], intval($rUser["exp_date"]));
                        } else {
                            $rArray["exp_date"] = strtotime('+' . intval($rPackage["official_duration"]) . ' ' . $rPackage["official_duration_in"]);
                        }
                    } else {
                        $rArray["exp_date"] = strtotime('+' . intval($rPackage["official_duration"]) . ' ' . $rPackage["official_duration_in"]);
                    }
                    $rArray["is_trial"] = 0;
                }
                $rArray["bouquet"] = $rPackage["bouquets"];
                $rArray["max_connections"] = $rPackage["max_connections"];
                $rArray["is_restreamer"] = $rPackage["is_restreamer"];
                $rOwner = $_POST["member_id"];
                if (in_array($rOwner, array_keys($rRegisteredUsers))) {
                    $rArray["member_id"] = $rOwner;
                } else {
                    $rArray["member_id"] = $rUserInfo["id"]; // Invalid owner, reset.
                }
                $rArray["reseller_notes"] = $_POST["reseller_notes"];
                if (isset($_POST["is_mag"])) {
                    $rArray["is_mag"] = 1;
                }
                if (isset($_POST["is_e2"])) {
                    $rArray["is_e2"] = 1;
                }
            } else {
                $_STATUS = 4; // Not enough credits.
            }
        } else {
            $_STATUS = 3; // Invalid package.
        }
    } else if (isset($rUser)) {
        // No package, just editing fields.
        $rArray["reseller_notes"] = $_POST["reseller_notes"];
        $rOwner = $_POST["member_id"];
        if (in_array($rOwner, array_keys($rRegisteredUsers))) {
            $rArray["member_id"] = $rOwner;
        } else {
            $rArray["member_id"] = $rUserInfo["id"]; // Invalid owner, reset.
        }
    } else {
        $_STATUS = 3; // Invalid package.
    }
    if (!$rPermissions["allow_change_pass"]) {
        if (isset($rUser)) {
            $_POST["password"] = $rUser["password"];
        } else {
            $_POST["password"] = "";
        }
    }
    if ((!$rPermissions["allow_change_pass"]) && (!$rAdminSettings["change_usernames"])) {
        if (isset($rUser)) {
            $_POST["username"] = $rUser["username"];
        } else {
            $_POST["username"] = "";
        }
    }
    if ((strlen($_POST["username"]) == 0) or (($rArray["is_mag"]) && (!isset($rUser))) or (($rArray["is_e2"]) && (!isset($rUser)))) {
        $_POST["username"] = generateString(10);
    } else if ((($rArray["is_mag"]) && (isset($rUser))) or (($rArray["is_e2"]) && (isset($rUser)))) {
        $_POST["username"] = $rUser["username"];
    }
    if ((strlen($_POST["password"]) == 0) or (($rArray["is_mag"]) && (!isset($rUser))) or (($rArray["is_e2"]) && (!isset($rUser)))) {
        $_POST["password"] = generateString(10);
    } else if ((($rArray["is_mag"]) && (isset($rUser))) or (($rArray["is_e2"]) && (isset($rUser)))) {
        $_POST["password"] = $rUser["password"];
    }
    $rArray["username"] = $_POST["username"];
    $rArray["password"] = $_POST["password"];
    if (!isset($rUser)) {
        $result = $db->query("SELECT `id` FROM `users` WHERE `username` = '" . ESC($rArray["username"]) . "';");
        if (($result) && ($result->num_rows > 0)) {
            $_STATUS = 6; // Username in use.
        }
    }
    if ((($_POST["is_mag"]) && (!filter_var($_POST["mac_address_mag"], FILTER_VALIDATE_MAC))) or ((strlen($_POST["mac_address_e2"]) > 0) && (!filter_var($_POST["mac_address_e2"], FILTER_VALIDATE_MAC)))) {
        $_STATUS = 7;
    } else if ($_POST["is_mag"]) {
        $result = $db->query("SELECT `user_id` FROM `mag_devices` WHERE mac = '" . ESC(base64_encode($_POST["mac_address_mag"])) . "' LIMIT 1;");
        if (($result) && ($result->num_rows > 0)) {
            if (isset($_POST["edit"])) {
                if (intval($result->fetch_assoc()["user_id"]) <> intval($_POST["edit"])) {
                    $_STATUS = 8; // MAC in use.
                }
            } else {
                $_STATUS = 8; // MAC in use.
            }
        }
    } else if ($_POST["is_e2"]) {
        $result = $db->query("SELECT `user_id` FROM `enigma2_devices` WHERE mac = '" . ESC($_POST["mac_address_e2"]) . "' LIMIT 1;");
        if (($result) && ($result->num_rows > 0)) {
            if (isset($_POST["edit"])) {
                if (intval($result->fetch_assoc()["user_id"]) <> intval($_POST["edit"])) {
                    $_STATUS = 8; // MAC in use.
                }
            } else {
                $_STATUS = 8; // MAC in use.
            }
        }
    }
    if ($rAdminSettings["reseller_restrictions"]) {
        if (isset($_POST["allowed_ips"])) {
            if (!is_array($_POST["allowed_ips"])) {
                $_POST["allowed_ips"] = array($_POST["allowed_ips"]);
            }
            $rArray["allowed_ips"] = json_encode($_POST["allowed_ips"]);
        } else {
            $rArray["allowed_ips"] = "[]";
        }
        if (isset($_POST["allowed_ua"])) {
            if (!is_array($_POST["allowed_ua"])) {
                $_POST["allowed_ua"] = array($_POST["allowed_ua"]);
            }
            $rArray["allowed_ua"] = json_encode($_POST["allowed_ua"]);
        } else {
            $rArray["allowed_ua"] = "[]";
        }
    }
    if (!isset($_STATUS)) {
        $rArray["created_by"] = $rUserInfo["id"];
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
        if (isset($rUser)) {
            $rCols = "`id`," . $rCols;
            $rValues = ESC($rUser["id"]) . "," . $rValues;
        }
        $isMag = False;
        $isE2 = False;
        // Confirm Reseller can generate MAG.
        if ($rArray["is_mag"]) {
            if (($rPackage["can_gen_mag"]) or (isset($rUser))) {
                $isMag = True;
            }
        }
        if ($rArray["is_e2"]) {
            if (($rPackage["can_gen_e2"]) or (isset($rUser))) {
                $isE2 = True;
            }
        }
        if ((!$isMag) && (!$isE2) && (($rPackage["only_mag"]) or ($rPackage["only_e2"])) and (!isset($rUser))) {
            $_STATUS = 5; // Not allowed to generate normal users!
        } else {
            // Checks completed, run,
            $rQuery = "REPLACE INTO `users`(" . $rCols . ") VALUES(" . $rValues . ");";
            if ($db->query($rQuery)) {
                if (isset($rUser)) {
                    $rInsertID = intval($rUser["id"]);
                } else {
                    $rInsertID = $db->insert_id;
                }
                if (isset($rCost)) {
                    $rNewCredits = floatval($rUserInfo["credits"]) - floatval($rCost);
                    $db->query("UPDATE `reg_users` SET `credits` = '" . floatval($rNewCredits) . "' WHERE `id` = " . intval($rUserInfo["id"]) . ";");
                    if (isset($rUser)) {
                        if ($isMag) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rArray["username"]) . "', '" . ESC($rArray["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b>] -> [ " . ESC($_POST["mac_address_mag"]) . " ] " . $_["extend_mag"] . " [ " . ESC($rPackage["package_name"]) . " ], Credits: <font color=\"green\">" . ESC($rUserInfo["credits"]) . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
                        } else if ($isE2) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rArray["username"]) . "', '" . ESC($rArray["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b>] -> [ " . ESC($_POST["mac_address_e2"]) . " ] " . $_["extend_enigma"] . " [ " . ESC($rPackage["package_name"]) . " ], Credits: <font color=\"green\">" . ESC($rUserInfo["credits"]) . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
                        } else {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rArray["username"]) . "', '" . ESC($rArray["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b>] -> [ " . ESC($_POST["username"]) . " ] " . $_["extend_m3u"] . " [ " . ESC($rPackage["package_name"]) . " ], Credits: <font color=\"green\">" . ESC($rUserInfo["credits"]) . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
                        }
                    } else {
                        if ($isMag) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rArray["username"]) . "', '" . ESC($rArray["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b>] -> [ " . ESC($_POST["mac_address_mag"]) . " ] " . $_["new_mag"] . " [" . ESC($rPackage["package_name"]) . "], Credits: <font color=\"green\">" . ESC($rUserInfo["credits"]) . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
                        } else if ($isE2) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rArray["username"]) . "', '" . ESC($rArray["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b>] -> [ " . ESC($_POST["mac_address_e2"]) . " ] " . $_["new_enigma"] . " [" . ESC($rPackage["package_name"]) . "], Credits: <font color=\"green\">" . ESC($rUserInfo["credits"]) . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
                        } else {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '" . ESC($rArray["username"]) . "', '" . ESC($rArray["password"]) . "', " . intval(time()) . ", '[<b>UserPanel</b>] -> [ " . ESC($_POST["username"]) . " ] " . $_["new_m3u"] . " [" . ESC($rPackage["package_name"]) . "], Credits: <font color=\"green\">" . ESC($rUserInfo["credits"]) . "</font> -> <font color=\"red\">" . $rNewCredits . "</font>');");
                        }
                        $rAccessOutput = json_decode($rPackage["output_formats"], True);
                        $rLockDevice = $rPackage["lock_device"];
                    }
                    $rUserInfo["credits"] = $rNewCredits;
                }
                if ((!isset($rUser)) && ((isset($rInsertID)) && (isset($rAccessOutput)))) {
                    $db->query("DELETE FROM `user_output` WHERE `user_id` = " . intval($rInsertID) . ";");
                    foreach ($rAccessOutput as $rOutputID) {
                        $db->query("INSERT INTO `user_output`(`user_id`, `access_output_id`) VALUES(" . intval($rInsertID) . ", " . intval($rOutputID) . ");");
                    }
                }
                if ($isMag) {
                    $result = $db->query("SELECT `mag_id` FROM `mag_devices` WHERE `user_id` = " . intval($rInsertID) . " LIMIT 1;");
                    if ((isset($result)) && ($result->num_rows == 1)) {
                        $db->query("UPDATE `mag_devices` SET `mac` = '" . base64_encode(ESC(strtoupper($_POST["mac_address_mag"]))) . "' WHERE `user_id` = " . intval($rInsertID) . ";");
                    } else if (!isset($rUser)) {
                        $db->query("INSERT INTO `mag_devices`(`user_id`, `mac`, `lock_device`) VALUES(" . intval($rInsertID) . ", '" . ESC(base64_encode(strtoupper($_POST["mac_address_mag"]))) . "', " . intval($rLockDevice) . ");");
                    }
                } else if ($isE2) {
                    $result = $db->query("SELECT `device_id` FROM `enigma2_devices` WHERE `user_id` = " . intval($rInsertID) . " LIMIT 1;");
                    if ((isset($result)) && ($result->num_rows == 1)) {
                        $db->query("UPDATE `enigma2_devices` SET `mac` = '" . ESC(strtoupper($_POST["mac_address_e2"])) . "' WHERE `user_id` = " . intval($rInsertID) . ";");
                    } else if (!isset($rUser)) {
                        $db->query("INSERT INTO `enigma2_devices`(`user_id`, `mac`, `lock_device`) VALUES(" . intval($rInsertID) . ", '" . ESC(strtoupper($_POST["mac_address_e2"])) . "', " . intval($rLockDevice) . ");");
                    }
                }
                header("Location: ./user_reseller.php?id=" . $rInsertID);
                exit;
            } else {
                $_STATUS = 2;
            }
        }
    }
}

if (isset($_GET["id"])) {
    if (!hasPermissions("user", $_GET["id"])) {
        exit;
    }
    $rUser = getUser($_GET["id"]);
    if (!$rUser) {
        exit;
    }
    $rMAGUser = getMAGUser($_GET["id"]);
    if (($rUser["is_mag"])) {
        $rUser["lock_device"] = $rMAGUser["lock_device"];
        $rUser["mac_address_mag"] = base64_decode($rMAGUser["mac"]);
    }
    if (($rUser["is_e2"])) {
        $rUser["mac_address_e2"] = getE2User($_GET["id"])["mac"];
    }
    $rUser["outputs"] = getOutputs($rUser["id"]);
}

$rCountries = array(array("id" => "", "name" => "Off"), array("id" => "A1", "name" => "Anonymous Proxy"), array("id" => "A2", "name" => "Satellite Provider"), array("id" => "O1", "name" => "Other Country"), array("id" => "AF", "name" => "Afghanistan"), array("id" => "AX", "name" => "Aland Islands"), array("id" => "AL", "name" => "Albania"), array("id" => "DZ", "name" => "Algeria"), array("id" => "AS", "name" => "American Samoa"), array("id" => "AD", "name" => "Andorra"), array("id" => "AO", "name" => "Angola"), array("id" => "AI", "name" => "Anguilla"), array("id" => "AQ", "name" => "Antarctica"), array("id" => "AG", "name" => "Antigua And Barbuda"), array("id" => "AR", "name" => "Argentina"), array("id" => "AM", "name" => "Armenia"), array("id" => "AW", "name" => "Aruba"), array("id" => "AU", "name" => "Australia"), array("id" => "AT", "name" => "Austria"), array("id" => "AZ", "name" => "Azerbaijan"), array("id" => "BS", "name" => "Bahamas"), array("id" => "BH", "name" => "Bahrain"), array("id" => "BD", "name" => "Bangladesh"), array("id" => "BB", "name" => "Barbados"), array("id" => "BY", "name" => "Belarus"), array("id" => "BE", "name" => "Belgium"), array("id" => "BZ", "name" => "Belize"), array("id" => "BJ", "name" => "Benin"), array("id" => "BM", "name" => "Bermuda"), array("id" => "BT", "name" => "Bhutan"), array("id" => "BO", "name" => "Bolivia"), array("id" => "BA", "name" => "Bosnia And Herzegovina"), array("id" => "BW", "name" => "Botswana"), array("id" => "BV", "name" => "Bouvet Island"), array("id" => "BR", "name" => "Brazil"), array("id" => "IO", "name" => "British Indian Ocean Territory"), array("id" => "BN", "name" => "Brunei Darussalam"), array("id" => "BG", "name" => "Bulgaria"), array("id" => "BF", "name" => "Burkina Faso"), array("id" => "BI", "name" => "Burundi"), array("id" => "KH", "name" => "Cambodia"), array("id" => "CM", "name" => "Cameroon"), array("id" => "CA", "name" => "Canada"), array("id" => "CV", "name" => "Cape Verde"), array("id" => "KY", "name" => "Cayman Islands"), array("id" => "CF", "name" => "Central African Republic"), array("id" => "TD", "name" => "Chad"), array("id" => "CL", "name" => "Chile"), array("id" => "CN", "name" => "China"), array("id" => "CX", "name" => "Christmas Island"), array("id" => "CC", "name" => "Cocos (Keeling) Islands"), array("id" => "CO", "name" => "Colombia"), array("id" => "KM", "name" => "Comoros"), array("id" => "CG", "name" => "Congo"), array("id" => "CD", "name" => "Congo, Democratic Republic"), array("id" => "CK", "name" => "Cook Islands"), array("id" => "CR", "name" => "Costa Rica"), array("id" => "CI", "name" => "Cote D'Ivoire"), array("id" => "HR", "name" => "Croatia"), array("id" => "CU", "name" => "Cuba"), array("id" => "CY", "name" => "Cyprus"), array("id" => "CZ", "name" => "Czech Republic"), array("id" => "DK", "name" => "Denmark"), array("id" => "DJ", "name" => "Djibouti"), array("id" => "DM", "name" => "Dominica"), array("id" => "DO", "name" => "Dominican Republic"), array("id" => "EC", "name" => "Ecuador"), array("id" => "EG", "name" => "Egypt"), array("id" => "SV", "name" => "El Salvador"), array("id" => "GQ", "name" => "Equatorial Guinea"), array("id" => "ER", "name" => "Eritrea"), array("id" => "EE", "name" => "Estonia"), array("id" => "ET", "name" => "Ethiopia"), array("id" => "FK", "name" => "Falkland Islands (Malvinas)"), array("id" => "FO", "name" => "Faroe Islands"), array("id" => "FJ", "name" => "Fiji"), array("id" => "FI", "name" => "Finland"), array("id" => "FR", "name" => "France"), array("id" => "GF", "name" => "French Guiana"), array("id" => "PF", "name" => "French Polynesia"), array("id" => "TF", "name" => "French Southern Territories"), array("id" => "MK", "name" => "Fyrom"), array("id" => "GA", "name" => "Gabon"), array("id" => "GM", "name" => "Gambia"), array("id" => "GE", "name" => "Georgia"), array("id" => "DE", "name" => "Germany"), array("id" => "GH", "name" => "Ghana"), array("id" => "GI", "name" => "Gibraltar"), array("id" => "GR", "name" => "Greece"), array("id" => "GL", "name" => "Greenland"), array("id" => "GD", "name" => "Grenada"), array("id" => "GP", "name" => "Guadeloupe"), array("id" => "GU", "name" => "Guam"), array("id" => "GT", "name" => "Guatemala"), array("id" => "GG", "name" => "Guernsey"), array("id" => "GN", "name" => "Guinea"), array("id" => "GW", "name" => "Guinea-Bissau"), array("id" => "GY", "name" => "Guyana"), array("id" => "HT", "name" => "Haiti"), array("id" => "HM", "name" => "Heard Island & Mcdonald Islands"), array("id" => "VA", "name" => "Holy See (Vatican City State)"), array("id" => "HN", "name" => "Honduras"), array("id" => "HK", "name" => "Hong Kong"), array("id" => "HU", "name" => "Hungary"), array("id" => "IS", "name" => "Iceland"), array("id" => "IN", "name" => "India"), array("id" => "ID", "name" => "Indonesia"), array("id" => "IR", "name" => "Iran, Islamic Republic Of"), array("id" => "IQ", "name" => "Iraq"), array("id" => "IE", "name" => "Ireland"), array("id" => "IM", "name" => "Isle Of Man"), array("id" => "IL", "name" => "Israel"), array("id" => "IT", "name" => "Italy"), array("id" => "JM", "name" => "Jamaica"), array("id" => "JP", "name" => "Japan"), array("id" => "JE", "name" => "Jersey"), array("id" => "JO", "name" => "Jordan"), array("id" => "KZ", "name" => "Kazakhstan"), array("id" => "KE", "name" => "Kenya"), array("id" => "KI", "name" => "Kiribati"), array("id" => "KR", "name" => "Korea"), array("id" => "KW", "name" => "Kuwait"), array("id" => "KG", "name" => "Kyrgyzstan"), array("id" => "LA", "name" => "Lao People's Democratic Republic"), array("id" => "LV", "name" => "Latvia"), array("id" => "LB", "name" => "Lebanon"), array("id" => "LS", "name" => "Lesotho"), array("id" => "LR", "name" => "Liberia"), array("id" => "LY", "name" => "Libyan Arab Jamahiriya"), array("id" => "LI", "name" => "Liechtenstein"), array("id" => "LT", "name" => "Lithuania"), array("id" => "LU", "name" => "Luxembourg"), array("id" => "MO", "name" => "Macao"), array("id" => "MG", "name" => "Madagascar"), array("id" => "MW", "name" => "Malawi"), array("id" => "MY", "name" => "Malaysia"), array("id" => "MV", "name" => "Maldives"), array("id" => "ML", "name" => "Mali"), array("id" => "MT", "name" => "Malta"), array("id" => "MH", "name" => "Marshall Islands"), array("id" => "MQ", "name" => "Martinique"), array("id" => "MR", "name" => "Mauritania"), array("id" => "MU", "name" => "Mauritius"), array("id" => "YT", "name" => "Mayotte"), array("id" => "MX", "name" => "Mexico"), array("id" => "FM", "name" => "Micronesia, Federated States Of"), array("id" => "MD", "name" => "Moldova"), array("id" => "MC", "name" => "Monaco"), array("id" => "MN", "name" => "Mongolia"), array("id" => "ME", "name" => "Montenegro"), array("id" => "MS", "name" => "Montserrat"), array("id" => "MA", "name" => "Morocco"), array("id" => "MZ", "name" => "Mozambique"), array("id" => "MM", "name" => "Myanmar"), array("id" => "NA", "name" => "Namibia"), array("id" => "NR", "name" => "Nauru"), array("id" => "NP", "name" => "Nepal"), array("id" => "NL", "name" => "Netherlands"), array("id" => "AN", "name" => "Netherlands Antilles"), array("id" => "NC", "name" => "New Caledonia"), array("id" => "NZ", "name" => "New Zealand"), array("id" => "NI", "name" => "Nicaragua"), array("id" => "NE", "name" => "Niger"), array("id" => "NG", "name" => "Nigeria"), array("id" => "NU", "name" => "Niue"), array("id" => "NF", "name" => "Norfolk Island"), array("id" => "MP", "name" => "Northern Mariana Islands"), array("id" => "NO", "name" => "Norway"), array("id" => "OM", "name" => "Oman"), array("id" => "PK", "name" => "Pakistan"), array("id" => "PW", "name" => "Palau"), array("id" => "PS", "name" => "Palestinian Territory, Occupied"), array("id" => "PA", "name" => "Panama"), array("id" => "PG", "name" => "Papua New Guinea"), array("id" => "PY", "name" => "Paraguay"), array("id" => "PE", "name" => "Peru"), array("id" => "PH", "name" => "Philippines"), array("id" => "PN", "name" => "Pitcairn"), array("id" => "PL", "name" => "Poland"), array("id" => "PT", "name" => "Portugal"), array("id" => "PR", "name" => "Puerto Rico"), array("id" => "QA", "name" => "Qatar"), array("id" => "RE", "name" => "Reunion"), array("id" => "RO", "name" => "Romania"), array("id" => "RU", "name" => "Russian Federation"), array("id" => "RW", "name" => "Rwanda"), array("id" => "BL", "name" => "Saint Barthelemy"), array("id" => "SH", "name" => "Saint Helena"), array("id" => "KN", "name" => "Saint Kitts And Nevis"), array("id" => "LC", "name" => "Saint Lucia"), array("id" => "MF", "name" => "Saint Martin"), array("id" => "PM", "name" => "Saint Pierre And Miquelon"), array("id" => "VC", "name" => "Saint Vincent And Grenadines"), array("id" => "WS", "name" => "Samoa"), array("id" => "SM", "name" => "San Marino"), array("id" => "ST", "name" => "Sao Tome And Principe"), array("id" => "SA", "name" => "Saudi Arabia"), array("id" => "SN", "name" => "Senegal"), array("id" => "RS", "name" => "Serbia"), array("id" => "SC", "name" => "Seychelles"), array("id" => "SL", "name" => "Sierra Leone"), array("id" => "SG", "name" => "Singapore"), array("id" => "SK", "name" => "Slovakia"), array("id" => "SI", "name" => "Slovenia"), array("id" => "SB", "name" => "Solomon Islands"), array("id" => "SO", "name" => "Somalia"), array("id" => "ZA", "name" => "South Africa"), array("id" => "GS", "name" => "South Georgia And Sandwich Isl."), array("id" => "ES", "name" => "Spain"), array("id" => "LK", "name" => "Sri Lanka"), array("id" => "SD", "name" => "Sudan"), array("id" => "SR", "name" => "Suriname"), array("id" => "SJ", "name" => "Svalbard And Jan Mayen"), array("id" => "SZ", "name" => "Swaziland"), array("id" => "SE", "name" => "Sweden"), array("id" => "CH", "name" => "Switzerland"), array("id" => "SY", "name" => "Syrian Arab Republic"), array("id" => "TW", "name" => "Taiwan"), array("id" => "TJ", "name" => "Tajikistan"), array("id" => "TZ", "name" => "Tanzania"), array("id" => "TH", "name" => "Thailand"), array("id" => "TL", "name" => "Timor-Leste"), array("id" => "TG", "name" => "Togo"), array("id" => "TK", "name" => "Tokelau"), array("id" => "TO", "name" => "Tonga"), array("id" => "TT", "name" => "Trinidad And Tobago"), array("id" => "TN", "name" => "Tunisia"), array("id" => "TR", "name" => "Turkey"), array("id" => "TM", "name" => "Turkmenistan"), array("id" => "TC", "name" => "Turks And Caicos Islands"), array("id" => "TV", "name" => "Tuvalu"), array("id" => "UG", "name" => "Uganda"), array("id" => "UA", "name" => "Ukraine"), array("id" => "AE", "name" => "United Arab Emirates"), array("id" => "GB", "name" => "United Kingdom"), array("id" => "US", "name" => "United States"), array("id" => "UM", "name" => "United States Outlying Islands"), array("id" => "UY", "name" => "Uruguay"), array("id" => "UZ", "name" => "Uzbekistan"), array("id" => "VU", "name" => "Vanuatu"), array("id" => "VE", "name" => "Venezuela"), array("id" => "VN", "name" => "Viet Nam"), array("id" => "VG", "name" => "Virgin Islands, British"), array("id" => "VI", "name" => "Virgin Islands, U.S."), array("id" => "WF", "name" => "Wallis And Futuna"), array("id" => "EH", "name" => "Western Sahara"), array("id" => "YE", "name" => "Yemen"), array("id" => "ZM", "name" => "Zambia"), array("id" => "ZW", "name" => "Zimbabwe"));

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
                                        <a href="./users.php">
                                            <li class="breadcrumb-item"><i class="mdi mdi-backspace"></i>
                                                <?= $_["back_to_users"] ?></li>
                                        </a>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php if (isset($rUser)) {
                                                            echo $_["edit"];
                                                        } else {
                                                            echo $_["add"];
                                                        } ?> <?php if (isset($_GET["trial"])) {
                                                                    echo $_["trial"];
                                                                } ?><?= $_["user"] ?></h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-xl-12">
                            <?php if (!$canGenerateTrials) { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["you_have_used_your_allowance"] ?>
                                </div>
                                <?php }
                            if (isset($_STATUS)) {
                                if ($_STATUS == 0) { ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["user_operation_was_completed_successfully"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 1) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["an_invalid_expiration_date_was_entered"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 2) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["there_was_an_error"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 3) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["an_invalid_package_was_selected"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 4) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["you_don't_have_enough_credits"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 5) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["you_are_not_permitted_to_generate"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 6) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["this_username_already_exists"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 7) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["an_invalid_mac_address_was_entered"] ?>
                                    </div>
                                <?php } else if ($_STATUS == 8) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?= $_["this_mac_address_is_already_in_use"] ?>
                                    </div>
                                <?php }
                            }
                            if ((isset($rUser)) and ($rUser["is_trial"])) { ?>
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?= $_["this_is_a_trial_user"] ?>
                                </div>
                            <?php } ?>
                            <div class="card">
                                <div class="card-body">
                                    <form action="./user_reseller.php<?php if (isset($_GET["id"])) {
                                                                            echo "?id=" . $_GET["id"];
                                                                        } ?>" method="POST" id="user_form">
                                        <?php if (isset($rUser)) { ?>
                                            <input type="hidden" name="edit" value="<?= $rUser["id"] ?>" />
                                        <?php }
                                        if (isset($_GET["trial"])) { ?>
                                            <input type="hidden" name="trial" value="1" />
                                        <?php } ?>
                                        <div id="basicwizard">
                                            <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                                <li class="nav-item">
                                                    <a href="#user-details" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                        <span class="d-none d-sm-inline"><?= $_["details"] ?></span>
                                                    </a>
                                                </li>
                                                <?php if ($rAdminSettings["reseller_restrictions"]) { ?>
                                                    <li class="nav-item">
                                                        <a href="#restrictions" data-toggle="tab"
                                                            class="nav-link rounded-0 pt-2 pb-2">
                                                            <i class="mdi mdi-hazard-lights mr-1"></i>
                                                            <span
                                                                class="d-none d-sm-inline"><?= $_["restrictions"] ?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                                <li class="nav-item">
                                                    <a href="#review-purchase" data-toggle="tab"
                                                        class="nav-link rounded-0 pt-2 pb-2">
                                                        <i class="mdi mdi-book-open-variant mr-1"></i>
                                                        <span
                                                            class="d-none d-sm-inline"><?= $_["review_purchase"] ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content b-0 mb-0 pt-0">
                                                <div class="tab-pane" id="user-details">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group row mb-4" id="uname">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="username"><?= $_["username"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input<?php if ((!$rPermissions["allow_change_pass"]) && (!$rAdminSettings["change_usernames"])) {
                                                                                echo $_[" disabled"];
                                                                            } ?> type="text"
                                                                        class="form-control" id="username"
                                                                        name="username"
                                                                        placeholder="<?= $_["auto_generate_if_blank"] ?>"
                                                                        value="<?php if (isset($rUser)) {
                                                                                    echo htmlspecialchars($rUser["username"]);
                                                                                } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4" id="pass">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="password"><?= $_["password"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input<?php if (!$rPermissions["allow_change_pass"]) {
                                                                                echo " disabled";
                                                                            } ?> type="text"
                                                                        class="form-control" id="password"
                                                                        name="password"
                                                                        placeholder="<?= $_["auto_generate_if_blank"] ?>"
                                                                        value="<?php if (isset($rUser)) {
                                                                                    echo htmlspecialchars($rUser["password"]);
                                                                                } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="member_id"><?= $_["owner"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="member_id" id="member_id"
                                                                        class="form-control select2"
                                                                        data-toggle="select2">
                                                                        <?php foreach ($rRegisteredUsers as $rRegisteredUser) { ?>
                                                                            <option <?php if (isset($rUser)) {
                                                                                        if (intval($rUser["member_id"]) == intval($rRegisteredUser["id"])) {
                                                                                            echo "selected ";
                                                                                        }
                                                                                    } else if ($rUserInfo["id"] == $rRegisteredUser["id"]) {
                                                                                        echo "selected ";
                                                                                    } ?>value="<?= $rRegisteredUser["id"] ?>">
                                                                                <?= $rRegisteredUser["username"] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="package"><?php if (isset($rUser)) {
                                                                                        echo "Extend ";
                                                                                    } ?><?= $_["package"] ?></label>
                                                                <div class="col-md-8">
                                                                    <select name="package" id="package"
                                                                        class="form-control select2"
                                                                        data-toggle="select2">
                                                                        <?php if (isset($rUser)) { ?>
                                                                            <option value=""><?= $_["no_changes"] ?>
                                                                            </option>
                                                                            <?php }
                                                                        foreach (getPackages() as $rPackage) {
                                                                            if (in_array($rUserInfo["member_group_id"], json_decode($rPackage["groups"], True))) {
                                                                                if ((($rPackage["is_trial"]) && ((isset($_GET["trial"])) or (isset($_POST["trial"])))) or (($rPackage["is_official"]) && ((!isset($_GET["trial"])) and (!isset($_POST["trial"]))))) { ?>
                                                                                    <option value="<?= $rPackage["id"] ?>">
                                                                                        <?= $rPackage["package_name"] ?></option>
                                                                        <?php }
                                                                            }
                                                                        } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="max_connections"><?= $_["max_connections"] ?></label>
                                                                <div class="col-md-2">
                                                                    <input disabled type="text" class="form-control"
                                                                        id="max_connections" name="max_connections"
                                                                        value="<?php if (isset($rUser)) {
                                                                                    echo htmlspecialchars($rUser["max_connections"]);
                                                                                } else {
                                                                                    echo "1";
                                                                                } ?>">
                                                                </div>
                                                                <label class="col-md-4 col-form-label"
                                                                    for="exp_date"><?= $_["expiry"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="<?= $_["leave_blank_for_unlimited"] ?>"
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input type="text" disabled
                                                                        class="form-control text-center date"
                                                                        id="exp_date" name="exp_date"
                                                                        value="<?php if (isset($rUser)) {
                                                                                    if (!is_null($rUser["exp_date"])) {
                                                                                        echo date("Y-m-d", $rUser["exp_date"]);
                                                                                    } else {
                                                                                        echo "\" disabled=\"disabled";
                                                                                    }
                                                                                } ?>"
                                                                        data-toggle="date-picker"
                                                                        data-single-date-picker="true">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="is_mag"><?= $_["mag_device"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="<?= $_["this_option_will_be_selected_mag"] ?>"
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input<?php if (isset($rUser)) {
                                                                                echo " disabled";
                                                                            } ?> name="is_mag" id="is_mag" type="checkbox"
                                                                        <?php if (isset($rUser)) {
                                                                            if ($rUser["is_mag"] == 1) {
                                                                                echo "checked ";
                                                                            }
                                                                        } else if (isset($_GET["mag"])) {
                                                                            echo "checked ";
                                                                        } ?>data-plugin="switchery" class="js-switch"
                                                                        data-color="#039cfd" />
                                                                </div>
                                                                <label class="col-md-4 col-form-label"
                                                                    for="is_e2"><?= $_["enigma_device"] ?> <i
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title=""
                                                                        data-original-title="<?= $_["this_option_will_be_selected_enigma"] ?>"
                                                                        class="mdi mdi-information"></i></label>
                                                                <div class="col-md-2">
                                                                    <input<?php if (isset($rUser)) {
                                                                                echo " disabled";
                                                                            } ?> name="is_e2" id="is_e2" type="checkbox" <?php if (isset($rUser)) {
                                                                                                                                if ($rUser["is_e2"] == 1) {
                                                                                                                                    echo "checked ";
                                                                                                                                }
                                                                                                                            } else if (isset($_GET["e2"])) {
                                                                                                                                echo "checked ";
                                                                                                                            } ?>data-plugin="switchery" class="js-switch"
                                                                        data-color="#039cfd" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4" style="display:none"
                                                                id="mac_entry_mag">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="mac_address_mag"><?= $_["mac_address"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        id="mac_address_mag" name="mac_address_mag"
                                                                        value="<?php if (isset($rUser)) {
                                                                                    echo htmlspecialchars($rUser["mac_address_mag"]);
                                                                                } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4" style="display:none"
                                                                id="mac_entry_e2">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="mac_address_e2"><?= $_["mac_address"] ?></label>
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        id="mac_address_e2" name="mac_address_e2"
                                                                        value="<?php if (isset($rUser)) {
                                                                                    echo htmlspecialchars($rUser["mac_address_e2"]);
                                                                                } ?>">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <label class="col-md-4 col-form-label"
                                                                    for="reseller_notes"><?= $_["reseller_notes"] ?></label>
                                                                <div class="col-md-8">
                                                                    <textarea id="reseller_notes" name="reseller_notes"
                                                                        class="form-control" rows="3"
                                                                        placeholder=""><?php if (isset($rUser)) {
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
                                                <?php if ($rAdminSettings["reseller_restrictions"]) { ?>
                                                    <div class="tab-pane" id="restrictions">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group row mb-4">
                                                                    <label class="col-md-4 col-form-label"
                                                                        for="ip_field"><?= $_["allowed_ip_addresses"] ?></label>
                                                                    <div class="col-md-8 input-group">
                                                                        <input type="text" id="ip_field"
                                                                            class="form-control" value="">
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
                                                                        <select class="form-control" id="allowed_ips"
                                                                            name="allowed_ips[]" size=6 class="form-control"
                                                                            multiple="multiple">
                                                                            <?php if (isset($rUser)) {
                                                                                foreach (json_decode($rUser["allowed_ips"], True) as $rIP) { ?>
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
                                                                        <input type="text" id="ua_field"
                                                                            class="form-control" value="">
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
                                                                        <select class="form-control" id="allowed_ua"
                                                                            name="allowed_ua[]" size=6 class="form-control"
                                                                            multiple="multiple">
                                                                            <?php if (isset($rUser)) {
                                                                                foreach (json_decode($rUser["allowed_ua"], True) as $rUA) { ?>
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
                                                <?php } ?>
                                                <div class="tab-pane" id="review-purchase">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="alert alert-danger" role="alert"
                                                                style="display:none;" id="no-credits">
                                                                <i class="mdi mdi-block-helper mr-2"></i>
                                                                <?= $_["you_do_not_have_enough_credits"] ?>
                                                            </div>
                                                            <div class="form-group row mb-4">
                                                                <table class="table" id="credits-cost">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">
                                                                                <?= $_["total_credits"] ?></th>
                                                                            <th class="text-center">
                                                                                <?= $_["purchase_cost"] ?></th>
                                                                            <th class="text-center">
                                                                                <?= $_["remaining_credits"] ?></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td class="text-center">
                                                                                <?= number_format($rUserInfo["credits"], 2) ?>
                                                                            </td>
                                                                            <td class="text-center" id="cost_credits">
                                                                            </td>
                                                                            <td class="text-center"
                                                                                id="remaining_credits"></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                <table id="datatable-review"
                                                                    class="table dt-responsive nowrap"
                                                                    style="margin-top:30px;">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center"><?= $_["id"] ?></th>
                                                                            <th><?= $_["bouquet_name"] ?></th>
                                                                            <th class="text-center">
                                                                                <?= $_["channels"] ?></th>
                                                                            <th class="text-center"><?= $_["series"] ?>
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody></tbody>
                                                                </table>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div> <!-- end row -->
                                                    <ul class="list-inline wizard mb-0">
                                                        <li class="previous list-inline-item">
                                                            <a href="javascript: void(0);"
                                                                class="btn btn-secondary"><?= $_["prev"] ?></a>
                                                        </li>
                                                        <li class="next list-inline-item float-right">
                                                            <input name="submit_user" type="submit"
                                                                class="btn btn-primary purchase"
                                                                value="<?= $_["purchase"] ?>" />
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
                <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
                <script src="assets/js/pages/form-wizard.init.js"></script>
                <script src="assets/js/pages/jquery.number.min.js"></script>
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
                                $("#mac_entry_mag").show();
                                $("#uname").hide()
                                $("#pass").hide()
                                window.swObjs["is_e2"].disable();
                            } else {
                                $("#mac_entry_e2").show();
                                $("#uname").hide()
                                $("#pass").hide()
                                window.swObjs["is_mag"].disable();
                            }
                        } else {
                            $("#mac_entry_mag").hide();
                            $("#mac_entry_e2").hide();
                            $("#uname").show()
                            $("#pass").show()
                            <?php if (!isset($rUser)) { ?>
                                window.swObjs["is_e2"].enable();
                                window.swObjs["is_mag"].enable();
                            <?php } else { ?>
                                window.swObjs["is_e2"].disable();
                                window.swObjs["is_mag"].disable();
                            <?php } ?>
                        }
                    }

                    $("#package").change(function() {
                        getPackage();
                    });

                    function getPackage() {
                        var rTable = $('#datatable-review').DataTable();
                        rTable.clear();
                        rTable.draw();
                        if ($("#package").val().length > 0) {
                            $.getJSON("./api.php?action=get_package<?php if (isset($_GET["trial"])) {
                                                                        echo "_trial";
                                                                    } ?>&package_id=" + $("#package").val() <?php if (isset($rUser)) {
                                                                                                                echo " + \"&user_id=" . $rUser["id"] . "\"";
                                                                                                            } ?>, function(rData) {
                                if (rData.result === true) {
                                    $("#max_connections").val(rData.data.max_connections);
                                    $("#cost_credits").html($.number(rData.data.cost_credits, 2));
                                    $("#remaining_credits").html($.number(<?= $rUserInfo["credits"] ?> - rData.data.cost_credits, 2));
                                    $("#exp_date").val(rData.data.exp_date);
                                    if (<?= $rUserInfo["credits"] ?> - rData.data.cost_credits < 0) {
                                        $("#credits-cost").hide();
                                        $("#no-credits").show()
                                        $(".purchase").prop('disabled', true);
                                    } else {
                                        $("#credits-cost").show();
                                        $("#no-credits").hide()
                                        $(".purchase").prop('disabled', false);
                                    }
                                    <?php if (!$canGenerateTrials) { ?>
                                        // No trials left!
                                        $(".purchase").prop('disabled', true);
                                    <?php }
                                    if (!isset($rUser)) { ?>
                                        if (rData.data.can_gen_mag == 0) {
                                            window.swObjs["is_mag"].disable();
                                            $("#mac_entry_mag").hide();
                                        }
                                        if (rData.data.can_gen_e2 == 0) {
                                            window.swObjs["is_e2"].disable();
                                            $("#mac_entry_e2").hide();
                                        }
                                    <?php } ?>
                                    $(rData.bouquets).each(function(rIndex) {
                                        rTable.row.add([rData.bouquets[rIndex].id, rData.bouquets[rIndex].bouquet_name, rData.bouquets[rIndex].bouquet_channels.length, rData.bouquets[rIndex].bouquet_series.length]);
                                    });
                                }
                                rTable.draw();
                            });
                        } else {
                            $("#max_connections").val(<?= $rUser["max_connections"] ?>);
                            $("#cost_credits").html(0);
                            $("#remaining_credits").html($.number(<?= $rUserInfo["credits"] ?>, 2));
                            $("#exp_date").val('<?= date("Y-m-d", $rUser["exp_date"]) ?>');
                            <?php if (!$canGenerateTrials) { ?>
                                $(".purchase").prop('disabled', true);
                                <?php }
                            foreach (json_decode($rUser["bouquet"], True) as $rBouquetID) {
                                $rBouquetData = getBouquet($rBouquetID);
                                if (strlen($rBouquetID) > 0) { ?>
                                    rTable.row.add([<?= $rBouquetID ?>, '<?= $rBouquetData["bouquet_name"] ?>', <?= count(json_decode($rBouquetData["bouquet_channels"], True)) ?>, <?= count(json_decode($rBouquetData["bouquet_series"], True)) ?>]);
                            <?php }
                            } ?>
                            rTable.draw();
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

                        $(".js-switch").on("change", function() {
                            evaluateForm();
                        });

                        $("#datatable-review").DataTable({
                            language: {
                                paginate: {
                                    previous: "<i class='mdi mdi-chevron-left'>",
                                    next: "<i class='mdi mdi-chevron-right'>"
                                }
                            },
                            drawCallback: function() {
                                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                            },
                            columnDefs: [{
                                "className": "dt-center",
                                "targets": [0, 2, 3]
                            }],
                            responsive: false,
                            bInfo: false,
                            searching: false,
                            paging: false
                        });
                        $("#user_form").submit(function(e) {
                            $("#allowed_ua option").prop('selected', true);
                            $("#allowed_ips option").prop('selected', true);
                        });

                        $(window).keypress(function(event) {
                            if (event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
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
                        getPackage();
                    });
                </script>
                </body>

                </html>