<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
define('STATUS_FAILURE', 0);
define('STATUS_SUCCESS', 1);
define('STATUS_INVALID_IP', 9);
define('STATUS_INVALID_INPUT', 34);
define('STATUS_FAILURE_GEOLITE', 49);
define('STATUS_SUCCESS_GEOLITE', 50);
define('STATUS_SUCCESS_UPDATE', 51);

$_INFO = array();
$rTimeout = 60;             // Seconds Timeout for Functions & Requests
$rSQLTimeout = 5;           // Max execution time for MySQL queries.

require_once '/home/xc_vm/wwwdir/constants.php';
require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'lib.php';
require_once INCLUDES_PATH . 'pdo.php';
// require_once INCLUDES_PATH . 'streaming.php';
// require_once INCLUDES_PATH . 'servers.php';
// require_once INCLUDES_PATH . 'stream.php';
require_once INCLUDES_PATH . 'admin_api.php';
require_once INCLUDES_PATH . 'libs/mobiledetect.php';
require_once INCLUDES_PATH . 'libs/gauth.php';
register_shutdown_function('shutdown_admin');

if (file_exists(MAIN_DIR . 'config')) {
    $_INFO = parse_ini_file(CONFIG_PATH . 'config.ini');
    define('SERVER_ID', $_INFO['server_id']);
} else {
    die('no config found');
}

$ipTV_db_admin = new Database($_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['hostname'], $_INFO['port'], empty($_INFO['pconnect']) ? false : true);

ipTV_lib::$ipTV_db = &$ipTV_db_admin;
ipTV_lib::init();
// ipTV_streaming::$ipTV_db = &$ipTV_db_admin;
// ipTV_stream::$ipTV_db = &$ipTV_db_admin;
API::$ipTV_db = &$ipTV_db_admin;
API::init();
ipTV_lib::connectRedis();

$rProtocol = getProtocol();
$rSettings = ipTV_lib::$settings;
$detect = new Mobile_Detect;

date_default_timezone_set($rSettings['default_timezone']);

set_time_limit($rTimeout);
ini_set('mysql.connect_timeout', $rSQLTimeout);
ini_set('max_execution_time', $rTimeout);
ini_set('default_socket_timeout', $rTimeout);

$rCountries = array(array("id" => "ALL", "name" => "All Countries"), array("id" => "A1", "name" => "Anonymous Proxy"), array("id" => "A2", "name" => "Satellite Provider"), array("id" => "O1", "name" => "Other Country"), array("id" => "AF", "name" => "Afghanistan"), array("id" => "AX", "name" => "Aland Islands"), array("id" => "AL", "name" => "Albania"), array("id" => "DZ", "name" => "Algeria"), array("id" => "AS", "name" => "American Samoa"), array("id" => "AD", "name" => "Andorra"), array("id" => "AO", "name" => "Angola"), array("id" => "AI", "name" => "Anguilla"), array("id" => "AQ", "name" => "Antarctica"), array("id" => "AG", "name" => "Antigua And Barbuda"), array("id" => "AR", "name" => "Argentina"), array("id" => "AM", "name" => "Armenia"), array("id" => "AW", "name" => "Aruba"), array("id" => "AU", "name" => "Australia"), array("id" => "AT", "name" => "Austria"), array("id" => "AZ", "name" => "Azerbaijan"), array("id" => "BS", "name" => "Bahamas"), array("id" => "BH", "name" => "Bahrain"), array("id" => "BD", "name" => "Bangladesh"), array("id" => "BB", "name" => "Barbados"), array("id" => "BY", "name" => "Belarus"), array("id" => "BE", "name" => "Belgium"), array("id" => "BZ", "name" => "Belize"), array("id" => "BJ", "name" => "Benin"), array("id" => "BM", "name" => "Bermuda"), array("id" => "BT", "name" => "Bhutan"), array("id" => "BO", "name" => "Bolivia"), array("id" => "BA", "name" => "Bosnia And Herzegovina"), array("id" => "BW", "name" => "Botswana"), array("id" => "BV", "name" => "Bouvet Island"), array("id" => "BR", "name" => "Brazil"), array("id" => "IO", "name" => "British Indian Ocean Territory"), array("id" => "BN", "name" => "Brunei Darussalam"), array("id" => "BG", "name" => "Bulgaria"), array("id" => "BF", "name" => "Burkina Faso"), array("id" => "BI", "name" => "Burundi"), array("id" => "KH", "name" => "Cambodia"), array("id" => "CM", "name" => "Cameroon"), array("id" => "CA", "name" => "Canada"), array("id" => "CV", "name" => "Cape Verde"), array("id" => "KY", "name" => "Cayman Islands"), array("id" => "CF", "name" => "Central African Republic"), array("id" => "TD", "name" => "Chad"), array("id" => "CL", "name" => "Chile"), array("id" => "CN", "name" => "China"), array("id" => "CX", "name" => "Christmas Island"), array("id" => "CC", "name" => "Cocos (Keeling) Islands"), array("id" => "CO", "name" => "Colombia"), array("id" => "KM", "name" => "Comoros"), array("id" => "CG", "name" => "Congo"), array("id" => "CD", "name" => "Congo, Democratic Republic"), array("id" => "CK", "name" => "Cook Islands"), array("id" => "CR", "name" => "Costa Rica"), array("id" => "CI", "name" => "Cote D'Ivoire"), array("id" => "HR", "name" => "Croatia"), array("id" => "CU", "name" => "Cuba"), array("id" => "CY", "name" => "Cyprus"), array("id" => "CZ", "name" => "Czech Republic"), array("id" => "DK", "name" => "Denmark"), array("id" => "DJ", "name" => "Djibouti"), array("id" => "DM", "name" => "Dominica"), array("id" => "DO", "name" => "Dominican Republic"), array("id" => "EC", "name" => "Ecuador"), array("id" => "EG", "name" => "Egypt"), array("id" => "SV", "name" => "El Salvador"), array("id" => "GQ", "name" => "Equatorial Guinea"), array("id" => "ER", "name" => "Eritrea"), array("id" => "EE", "name" => "Estonia"), array("id" => "ET", "name" => "Ethiopia"), array("id" => "FK", "name" => "Falkland Islands (Malvinas)"), array("id" => "FO", "name" => "Faroe Islands"), array("id" => "FJ", "name" => "Fiji"), array("id" => "FI", "name" => "Finland"), array("id" => "FR", "name" => "France"), array("id" => "GF", "name" => "French Guiana"), array("id" => "PF", "name" => "French Polynesia"), array("id" => "TF", "name" => "French Southern Territories"), array("id" => "MK", "name" => "Fyrom"), array("id" => "GA", "name" => "Gabon"), array("id" => "GM", "name" => "Gambia"), array("id" => "GE", "name" => "Georgia"), array("id" => "DE", "name" => "Germany"), array("id" => "GH", "name" => "Ghana"), array("id" => "GI", "name" => "Gibraltar"), array("id" => "GR", "name" => "Greece"), array("id" => "GL", "name" => "Greenland"), array("id" => "GD", "name" => "Grenada"), array("id" => "GP", "name" => "Guadeloupe"), array("id" => "GU", "name" => "Guam"), array("id" => "GT", "name" => "Guatemala"), array("id" => "GG", "name" => "Guernsey"), array("id" => "GN", "name" => "Guinea"), array("id" => "GW", "name" => "Guinea-Bissau"), array("id" => "GY", "name" => "Guyana"), array("id" => "HT", "name" => "Haiti"), array("id" => "HM", "name" => "Heard Island & Mcdonald Islands"), array("id" => "VA", "name" => "Holy See (Vatican City State)"), array("id" => "HN", "name" => "Honduras"), array("id" => "HK", "name" => "Hong Kong"), array("id" => "HU", "name" => "Hungary"), array("id" => "IS", "name" => "Iceland"), array("id" => "IN", "name" => "India"), array("id" => "ID", "name" => "Indonesia"), array("id" => "IR", "name" => "Iran, Islamic Republic Of"), array("id" => "IQ", "name" => "Iraq"), array("id" => "IE", "name" => "Ireland"), array("id" => "IM", "name" => "Isle Of Man"), array("id" => "IL", "name" => "Israel"), array("id" => "IT", "name" => "Italy"), array("id" => "JM", "name" => "Jamaica"), array("id" => "JP", "name" => "Japan"), array("id" => "JE", "name" => "Jersey"), array("id" => "JO", "name" => "Jordan"), array("id" => "KZ", "name" => "Kazakhstan"), array("id" => "KE", "name" => "Kenya"), array("id" => "KI", "name" => "Kiribati"), array("id" => "KR", "name" => "Korea"), array("id" => "KW", "name" => "Kuwait"), array("id" => "KG", "name" => "Kyrgyzstan"), array("id" => "LA", "name" => "Lao People's Democratic Republic"), array("id" => "LV", "name" => "Latvia"), array("id" => "LB", "name" => "Lebanon"), array("id" => "LS", "name" => "Lesotho"), array("id" => "LR", "name" => "Liberia"), array("id" => "LY", "name" => "Libyan Arab Jamahiriya"), array("id" => "LI", "name" => "Liechtenstein"), array("id" => "LT", "name" => "Lithuania"), array("id" => "LU", "name" => "Luxembourg"), array("id" => "MO", "name" => "Macao"), array("id" => "MG", "name" => "Madagascar"), array("id" => "MW", "name" => "Malawi"), array("id" => "MY", "name" => "Malaysia"), array("id" => "MV", "name" => "Maldives"), array("id" => "ML", "name" => "Mali"), array("id" => "MT", "name" => "Malta"), array("id" => "MH", "name" => "Marshall Islands"), array("id" => "MQ", "name" => "Martinique"), array("id" => "MR", "name" => "Mauritania"), array("id" => "MU", "name" => "Mauritius"), array("id" => "YT", "name" => "Mayotte"), array("id" => "MX", "name" => "Mexico"), array("id" => "FM", "name" => "Micronesia, Federated States Of"), array("id" => "MD", "name" => "Moldova"), array("id" => "MC", "name" => "Monaco"), array("id" => "MN", "name" => "Mongolia"), array("id" => "ME", "name" => "Montenegro"), array("id" => "MS", "name" => "Montserrat"), array("id" => "MA", "name" => "Morocco"), array("id" => "MZ", "name" => "Mozambique"), array("id" => "MM", "name" => "Myanmar"), array("id" => "NA", "name" => "Namibia"), array("id" => "NR", "name" => "Nauru"), array("id" => "NP", "name" => "Nepal"), array("id" => "NL", "name" => "Netherlands"), array("id" => "AN", "name" => "Netherlands Antilles"), array("id" => "NC", "name" => "New Caledonia"), array("id" => "NZ", "name" => "New Zealand"), array("id" => "NI", "name" => "Nicaragua"), array("id" => "NE", "name" => "Niger"), array("id" => "NG", "name" => "Nigeria"), array("id" => "NU", "name" => "Niue"), array("id" => "NF", "name" => "Norfolk Island"), array("id" => "MP", "name" => "Northern Mariana Islands"), array("id" => "NO", "name" => "Norway"), array("id" => "OM", "name" => "Oman"), array("id" => "PK", "name" => "Pakistan"), array("id" => "PW", "name" => "Palau"), array("id" => "PS", "name" => "Palestinian Territory, Occupied"), array("id" => "PA", "name" => "Panama"), array("id" => "PG", "name" => "Papua New Guinea"), array("id" => "PY", "name" => "Paraguay"), array("id" => "PE", "name" => "Peru"), array("id" => "PH", "name" => "Philippines"), array("id" => "PN", "name" => "Pitcairn"), array("id" => "PL", "name" => "Poland"), array("id" => "PT", "name" => "Portugal"), array("id" => "PR", "name" => "Puerto Rico"), array("id" => "QA", "name" => "Qatar"), array("id" => "RE", "name" => "Reunion"), array("id" => "RO", "name" => "Romania"), array("id" => "RU", "name" => "Russian Federation"), array("id" => "RW", "name" => "Rwanda"), array("id" => "BL", "name" => "Saint Barthelemy"), array("id" => "SH", "name" => "Saint Helena"), array("id" => "KN", "name" => "Saint Kitts And Nevis"), array("id" => "LC", "name" => "Saint Lucia"), array("id" => "MF", "name" => "Saint Martin"), array("id" => "PM", "name" => "Saint Pierre And Miquelon"), array("id" => "VC", "name" => "Saint Vincent And Grenadines"), array("id" => "WS", "name" => "Samoa"), array("id" => "SM", "name" => "San Marino"), array("id" => "ST", "name" => "Sao Tome And Principe"), array("id" => "SA", "name" => "Saudi Arabia"), array("id" => "SN", "name" => "Senegal"), array("id" => "RS", "name" => "Serbia"), array("id" => "SC", "name" => "Seychelles"), array("id" => "SL", "name" => "Sierra Leone"), array("id" => "SG", "name" => "Singapore"), array("id" => "SK", "name" => "Slovakia"), array("id" => "SI", "name" => "Slovenia"), array("id" => "SB", "name" => "Solomon Islands"), array("id" => "SO", "name" => "Somalia"), array("id" => "ZA", "name" => "South Africa"), array("id" => "GS", "name" => "South Georgia And Sandwich Isl."), array("id" => "ES", "name" => "Spain"), array("id" => "LK", "name" => "Sri Lanka"), array("id" => "SD", "name" => "Sudan"), array("id" => "SR", "name" => "Suriname"), array("id" => "SJ", "name" => "Svalbard And Jan Mayen"), array("id" => "SZ", "name" => "Swaziland"), array("id" => "SE", "name" => "Sweden"), array("id" => "CH", "name" => "Switzerland"), array("id" => "SY", "name" => "Syrian Arab Republic"), array("id" => "TW", "name" => "Taiwan"), array("id" => "TJ", "name" => "Tajikistan"), array("id" => "TZ", "name" => "Tanzania"), array("id" => "TH", "name" => "Thailand"), array("id" => "TL", "name" => "Timor-Leste"), array("id" => "TG", "name" => "Togo"), array("id" => "TK", "name" => "Tokelau"), array("id" => "TO", "name" => "Tonga"), array("id" => "TT", "name" => "Trinidad And Tobago"), array("id" => "TN", "name" => "Tunisia"), array("id" => "TR", "name" => "Turkey"), array("id" => "TM", "name" => "Turkmenistan"), array("id" => "TC", "name" => "Turks And Caicos Islands"), array("id" => "TV", "name" => "Tuvalu"), array("id" => "UG", "name" => "Uganda"), array("id" => "UA", "name" => "Ukraine"), array("id" => "AE", "name" => "United Arab Emirates"), array("id" => "GB", "name" => "United Kingdom"), array("id" => "US", "name" => "United States"), array("id" => "UM", "name" => "United States Outlying Islands"), array("id" => "UY", "name" => "Uruguay"), array("id" => "UZ", "name" => "Uzbekistan"), array("id" => "VU", "name" => "Vanuatu"), array("id" => "VE", "name" => "Venezuela"), array("id" => "VN", "name" => "Viet Nam"), array("id" => "VG", "name" => "Virgin Islands, British"), array("id" => "VI", "name" => "Virgin Islands, U.S."), array("id" => "WF", "name" => "Wallis And Futuna"), array("id" => "EH", "name" => "Western Sahara"), array("id" => "YE", "name" => "Yemen"), array("id" => "ZM", "name" => "Zambia"), array("id" => "ZW", "name" => "Zimbabwe"));
$rGeoCountries = array('ALL' => 'All Countries', 'A1' => 'Anonymous Proxy', 'A2' => 'Satellite Provider', 'O1' => 'Other Country', 'AF' => 'Afghanistan', 'AX' => 'Aland Islands', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua And Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia And Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo', 'CD' => 'Congo, Democratic Republic', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'CI' => "Cote D'Ivoire", 'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'MK' => 'Fyrom', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island & Mcdonald Islands', 'VA' => 'Holy See (Vatican City State)', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran, Islamic Republic Of', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle Of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KR' => 'Korea', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => "Lao People's Democratic Republic", 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libyan Arab Jamahiriya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia, Federated States Of', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territory, Occupied', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russian Federation', 'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts And Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre And Miquelon', 'VC' => 'Saint Vincent And Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome And Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia And Sandwich Isl.', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard And Jan Mayen', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syrian Arab Republic', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad And Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks And Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Outlying Islands', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' => 'Viet Nam', 'VG' => 'Virgin Islands, British', 'VI' => 'Virgin Islands, U.S.', 'WF' => 'Wallis And Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');
$rClientFilters = array('LB_TOKEN_INVALID' => 'Token Failure', 'NOT_IN_BOUQUET' => 'Not in Bouquet', 'BLOCKED_ASN' => 'Blocked ASN', 'ISP_LOCK_FAILED' => 'ISP Lock Failed', 'USER_DISALLOW_EXT' => 'Extension Disallowed', 'AUTH_FAILED' => 'Authentication Failed', 'USER_EXPIRED' => 'User Expired', 'USER_DISABLED' => 'User Disabled', 'USER_BAN' => 'User Banned', 'MAG_TOKEN_INVALID' => 'MAG Token Invalid', 'STALKER_CHANNEL_MISMATCH' => 'Stalker Channel Mismatch', 'STALKER_IP_MISMATCH' => 'Stalker IP Mismatch', 'STALKER_KEY_EXPIRED' => 'Stalker Key Expired', 'STALKER_DECRYPT_FAILED' => 'Stalker Decrypt Failed', 'EMPTY_UA' => 'Empty User-Agent', 'IP_BAN' => 'IP Banned', 'COUNTRY_DISALLOW' => 'Country Disallowed', 'USER_AGENT_BAN' => 'User-Agent Disallowed', 'USER_ALREADY_CONNECTED' => 'IP Limit Reached', 'RESTREAM_DETECT' => 'Restream Detected', 'PROXY_DETECT' => 'Proxy / VPN Detected', 'HOSTING_DETECT' => 'Hosting Server Detected', 'LINE_CREATE_FAIL' => 'Connection Failed', 'CONNECTION_LOOP' => 'Connection Loop', 'TOKEN_EXPIRED' => 'Token Expired', 'IP_MISMATCH' => 'IP Mismatch');
$rTMDBLanguages = array("" => "Default - EN", "aa" => "Afar", "af" => "Afrikaans", "ak" => "Akan", "an" => "Aragonese", "as" => "Assamese", "av" => "Avaric", "ae" => "Avestan", "ay" => "Aymara", "az" => "Azerbaijani", "ba" => "Bashkir", "bm" => "Bambara", "bi" => "Bislama", "bo" => "Tibetan", "br" => "Breton", "ca" => "Catalan", "cs" => "Czech", "ce" => "Chechen", "cu" => "Slavic", "cv" => "Chuvash", "kw" => "Cornish", "co" => "Corsican", "cr" => "Cree", "cy" => "Welsh", "da" => "Danish", "de" => "German", "dv" => "Divehi", "dz" => "Dzongkha", "eo" => "Esperanto", "et" => "Estonian", "eu" => "Basque", "fo" => "Faroese", "fj" => "Fijian", "fi" => "Finnish", "fr" => "French", "fy" => "Frisian", "ff" => "Fulah", "gd" => "Gaelic", "ga" => "Irish", "gl" => "Galician", "gv" => "Manx", "gn" => "Guarani", "gu" => "Gujarati", "ht" => "Haitian", "ha" => "Hausa", "sh" => "Serbo-Croatian", "hz" => "Herero", "ho" => "Hiri Motu", "hr" => "Croatian", "hu" => "Hungarian", "ig" => "Igbo", "io" => "Ido", "ii" => "Yi", "iu" => "Inuktitut", "ie" => "Interlingue", "ia" => "Interlingua", "id" => "Indonesian", "ik" => "Inupiaq", "is" => "Icelandic", "it" => "Italian", "ja" => "Japanese", "kl" => "Kalaallisut", "kn" => "Kannada", "ks" => "Kashmiri", "kr" => "Kanuri", "kk" => "Kazakh", "km" => "Khmer", "ki" => "Kikuyu", "rw" => "Kinyarwanda", "ky" => "Kirghiz", "kv" => "Komi", "kg" => "Kongo", "ko" => "Korean", "kj" => "Kuanyama", "ku" => "Kurdish", "lo" => "Lao", "la" => "Latin", "lv" => "Latvian", "li" => "Limburgish", "ln" => "Lingala", "lt" => "Lithuanian", "lb" => "Letzeburgesch", "lu" => "Luba-Katanga", "lg" => "Ganda", "mh" => "Marshall", "ml" => "Malayalam", "mr" => "Marathi", "mg" => "Malagasy", "mt" => "Maltese", "mo" => "Moldavian", "mn" => "Mongolian", "mi" => "Maori", "ms" => "Malay", "my" => "Burmese", "na" => "Nauru", "nv" => "Navajo", "nr" => "Ndebele", "nd" => "Ndebele", "ng" => "Ndonga", "ne" => "Nepali", "nl" => "Dutch", "nn" => "Norwegian Nynorsk", "nb" => "Norwegian Bokmal", "no" => "Norwegian", "ny" => "Chichewa", "oc" => "Occitan", "oj" => "Ojibwa", "or" => "Oriya", "om" => "Oromo", "os" => "Ossetian; Ossetic", "pi" => "Pali", "pl" => "Polish", "pt" => "Portuguese", "pt-BR" => "Portuguese - Brazil", "qu" => "Quechua", "rm" => "Raeto-Romance", "ro" => "Romanian", "rn" => "Rundi", "ru" => "Russian", "sg" => "Sango", "sa" => "Sanskrit", "si" => "Sinhalese", "sk" => "Slovak", "sl" => "Slovenian", "se" => "Northern Sami", "sm" => "Samoan", "sn" => "Shona", "sd" => "Sindhi", "so" => "Somali", "st" => "Sotho", "es" => "Spanish", "sq" => "Albanian", "sc" => "Sardinian", "sr" => "Serbian", "ss" => "Swati", "su" => "Sundanese", "sw" => "Swahili", "sv" => "Swedish", "ty" => "Tahitian", "ta" => "Tamil", "tt" => "Tatar", "te" => "Telugu", "tg" => "Tajik", "tl" => "Tagalog", "th" => "Thai", "ti" => "Tigrinya", "to" => "Tonga", "tn" => "Tswana", "ts" => "Tsonga", "tk" => "Turkmen", "tr" => "Turkish", "tw" => "Twi", "ug" => "Uighur", "uk" => "Ukrainian", "ur" => "Urdu", "uz" => "Uzbek", "ve" => "Venda", "vi" => "Vietnamese", "vo" => "Volapük", "wa" => "Walloon", "wo" => "Wolof", "xh" => "Xhosa", "yi" => "Yiddish", "za" => "Zhuang", "zu" => "Zulu", "ab" => "Abkhazian", "zh" => "Mandarin", "ps" => "Pushto", "am" => "Amharic", "ar" => "Arabic", "bg" => "Bulgarian", "cn" => "Cantonese", "mk" => "Macedonian", "el" => "Greek", "fa" => "Persian", "he" => "Hebrew", "hi" => "Hindi", "hy" => "Armenian", "en" => "English", "ee" => "Ewe", "ka" => "Georgian", "pa" => "Punjabi", "bn" => "Bengali", "bs" => "Bosnian", "ch" => "Chamorro", "be" => "Belarusian", "yo" => "Yoruba");
$rMAGs = array("MAG200", "MAG245", "MAG245D", "MAG250", "MAG254", "MAG255", "MAG256", "MAG257", "MAG260", "MAG270", "MAG275", "MAG322", "MAG322w1", "MAG322w2", "MAG323", "MAG324", "MAG324C", "MAG324w2", "MAG325", "MAG349", "MAG350", "MAG351", "MAG352", "MAG420", "MAG420w1", "MAG420w2", "MAG422", "MAG422A", "MAG422Aw1", "MAG424", "MAG424w1", "MAG424w2", "MAG424w3", "MAG424A", "MAG424Aw3", "MAG425", "MAG425A", "AuraHD", "AuraHD0", "AuraHD1", "AuraHD2", "AuraHD3", "AuraHD4", "AuraHD5", "AuraHD6", "AuraHD7", "AuraHD8", "AuraHD9", "WR320", "IM2100", "IM2100w1", "IM2100V", "IM2100VI", "IM2101", "IM2101V", "IM2101VI", "IM2101VO", "IM2101w2", "IM2102", "IM4410", "IM4410w3", "IM4411", "IM4411w1", "IM4412", "IM4414", "IM4414w1", "IP_STB_HD");
$rTimeZones = array("Africa/Abidjan" => "Africa/Abidjan [GMT  00:00]", "Africa/Accra" => "Africa/Accra [GMT  00:00]", "Africa/Addis_Ababa" => "Africa/Addis_Ababa [EAT +03:00]", "Africa/Algiers" => "Africa/Algiers [CET +01:00]", "Africa/Asmara" => "Africa/Asmara [EAT +03:00]", "Africa/Bamako" => "Africa/Bamako [GMT  00:00]", "Africa/Bangui" => "Africa/Bangui [WAT +01:00]", "Africa/Banjul" => "Africa/Banjul [GMT  00:00]", "Africa/Bissau" => "Africa/Bissau [GMT  00:00]", "Africa/Blantyre" => "Africa/Blantyre [CAT +02:00]", "Africa/Brazzaville" => "Africa/Brazzaville [WAT +01:00]", "Africa/Bujumbura" => "Africa/Bujumbura [CAT +02:00]", "Africa/Cairo" => "Africa/Cairo [EET +02:00]", "Africa/Casablanca" => "Africa/Casablanca [WEST +01:00]", "Africa/Ceuta" => "Africa/Ceuta [CEST +02:00]", "Africa/Conakry" => "Africa/Conakry [GMT  00:00]", "Africa/Dakar" => "Africa/Dakar [GMT  00:00]", "Africa/Dar_es_Salaam" => "Africa/Dar_es_Salaam [EAT +03:00]", "Africa/Djibouti" => "Africa/Djibouti [EAT +03:00]", "Africa/Douala" => "Africa/Douala [WAT +01:00]", "Africa/El_Aaiun" => "Africa/El_Aaiun [WEST +01:00]", "Africa/Freetown" => "Africa/Freetown [GMT  00:00]", "Africa/Gaborone" => "Africa/Gaborone [CAT +02:00]", "Africa/Harare" => "Africa/Harare [CAT +02:00]", "Africa/Johannesburg" => "Africa/Johannesburg [SAST +02:00]", "Africa/Juba" => "Africa/Juba [EAT +03:00]", "Africa/Kampala" => "Africa/Kampala [EAT +03:00]", "Africa/Khartoum" => "Africa/Khartoum [EAT +03:00]", "Africa/Kigali" => "Africa/Kigali [CAT +02:00]", "Africa/Kinshasa" => "Africa/Kinshasa [WAT +01:00]", "Africa/Lagos" => "Africa/Lagos [WAT +01:00]", "Africa/Libreville" => "Africa/Libreville [WAT +01:00]", "Africa/Lome" => "Africa/Lome [GMT  00:00]", "Africa/Luanda" => "Africa/Luanda [WAT +01:00]", "Africa/Lubumbashi" => "Africa/Lubumbashi [CAT +02:00]", "Africa/Lusaka" => "Africa/Lusaka [CAT +02:00]", "Africa/Malabo" => "Africa/Malabo [WAT +01:00]", "Africa/Maputo" => "Africa/Maputo [CAT +02:00]", "Africa/Maseru" => "Africa/Maseru [SAST +02:00]", "Africa/Mbabane" => "Africa/Mbabane [SAST +02:00]", "Africa/Mogadishu" => "Africa/Mogadishu [EAT +03:00]", "Africa/Monrovia" => "Africa/Monrovia [GMT  00:00]", "Africa/Nairobi" => "Africa/Nairobi [EAT +03:00]", "Africa/Ndjamena" => "Africa/Ndjamena [WAT +01:00]", "Africa/Niamey" => "Africa/Niamey [WAT +01:00]", "Africa/Nouakchott" => "Africa/Nouakchott [GMT  00:00]", "Africa/Ouagadougou" => "Africa/Ouagadougou [GMT  00:00]", "Africa/Porto-Novo" => "Africa/Porto-Novo [WAT +01:00]", "Africa/Sao_Tome" => "Africa/Sao_Tome [GMT  00:00]", "Africa/Tripoli" => "Africa/Tripoli [EET +02:00]", "Africa/Tunis" => "Africa/Tunis [CET +01:00]", "Africa/Windhoek" => "Africa/Windhoek [WAST +02:00]", "America/Adak" => "America/Adak [HADT -09:00]", "America/Anchorage" => "America/Anchorage [AKDT -08:00]", "America/Anguilla" => "America/Anguilla [AST -04:00]", "America/Antigua" => "America/Antigua [AST -04:00]", "America/Araguaina" => "America/Araguaina [BRT -03:00]", "America/Argentina/Buenos_Aires" => "America/Argentina/Buenos_Aires [ART -03:00]", "America/Argentina/Catamarca" => "America/Argentina/Catamarca [ART -03:00]", "America/Argentina/Cordoba" => "America/Argentina/Cordoba [ART -03:00]", "America/Argentina/Jujuy" => "America/Argentina/Jujuy [ART -03:00]", "America/Argentina/La_Rioja" => "America/Argentina/La_Rioja [ART -03:00]", "America/Argentina/Mendoza" => "America/Argentina/Mendoza [ART -03:00]", "America/Argentina/Rio_Gallegos" => "America/Argentina/Rio_Gallegos [ART -03:00]", "America/Argentina/Salta" => "America/Argentina/Salta [ART -03:00]", "America/Argentina/San_Juan" => "America/Argentina/San_Juan [ART -03:00]", "America/Argentina/San_Luis" => "America/Argentina/San_Luis [ART -03:00]", "America/Argentina/Tucuman" => "America/Argentina/Tucuman [ART -03:00]", "America/Argentina/Ushuaia" => "America/Argentina/Ushuaia [ART -03:00]", "America/Aruba" => "America/Aruba [AST -04:00]", "America/Asuncion" => "America/Asuncion [PYT -04:00]", "America/Atikokan" => "America/Atikokan [EST -05:00]", "America/Bahia" => "America/Bahia [BRT -03:00]", "America/Bahia_Banderas" => "America/Bahia_Banderas [CDT -05:00]", "America/Barbados" => "America/Barbados [AST -04:00]", "America/Belem" => "America/Belem [BRT -03:00]", "America/Belize" => "America/Belize [CST -06:00]", "America/Blanc-Sablon" => "America/Blanc-Sablon [AST -04:00]", "America/Boa_Vista" => "America/Boa_Vista [AMT -04:00]", "America/Bogota" => "America/Bogota [COT -05:00]", "America/Boise" => "America/Boise [MDT -06:00]", "America/Cambridge_Bay" => "America/Cambridge_Bay [MDT -06:00]", "America/Campo_Grande" => "America/Campo_Grande [AMT -04:00]", "America/Cancun" => "America/Cancun [CDT -05:00]", "America/Caracas" => "America/Caracas [VET -04:30]", "America/Cayenne" => "America/Cayenne [GFT -03:00]", "America/Cayman" => "America/Cayman [EST -05:00]", "America/Chicago" => "America/Chicago [CDT -05:00]", "America/Chihuahua" => "America/Chihuahua [MDT -06:00]", "America/Costa_Rica" => "America/Costa_Rica [CST -06:00]", "America/Creston" => "America/Creston [MST -07:00]", "America/Cuiaba" => "America/Cuiaba [AMT -04:00]", "America/Curacao" => "America/Curacao [AST -04:00]", "America/Danmarkshavn" => "America/Danmarkshavn [GMT  00:00]", "America/Dawson" => "America/Dawson [PDT -07:00]", "America/Dawson_Creek" => "America/Dawson_Creek [MST -07:00]", "America/Denver" => "America/Denver [MDT -06:00]", "America/Detroit" => "America/Detroit [EDT -04:00]", "America/Dominica" => "America/Dominica [AST -04:00]", "America/Edmonton" => "America/Edmonton [MDT -06:00]", "America/Eirunepe" => "America/Eirunepe [ACT -05:00]", "America/El_Salvador" => "America/El_Salvador [CST -06:00]", "America/Fortaleza" => "America/Fortaleza [BRT -03:00]", "America/Glace_Bay" => "America/Glace_Bay [ADT -03:00]", "America/Godthab" => "America/Godthab [WGST -02:00]", "America/Goose_Bay" => "America/Goose_Bay [ADT -03:00]", "America/Grand_Turk" => "America/Grand_Turk [AST -04:00]", "America/Grenada" => "America/Grenada [AST -04:00]", "America/Guadeloupe" => "America/Guadeloupe [AST -04:00]", "America/Guatemala" => "America/Guatemala [CST -06:00]", "America/Guayaquil" => "America/Guayaquil [ECT -05:00]", "America/Guyana" => "America/Guyana [GYT -04:00]", "America/Halifax" => "America/Halifax [ADT -03:00]", "America/Havana" => "America/Havana [CDT -04:00]", "America/Hermosillo" => "America/Hermosillo [MST -07:00]", "America/Indiana/Indianapolis" => "America/Indiana/Indianapolis [EDT -04:00]", "America/Indiana/Knox" => "America/Indiana/Knox [CDT -05:00]", "America/Indiana/Marengo" => "America/Indiana/Marengo [EDT -04:00]", "America/Indiana/Petersburg" => "America/Indiana/Petersburg [EDT -04:00]", "America/Indiana/Tell_City" => "America/Indiana/Tell_City [CDT -05:00]", "America/Indiana/Vevay" => "America/Indiana/Vevay [EDT -04:00]", "America/Indiana/Vincennes" => "America/Indiana/Vincennes [EDT -04:00]", "America/Indiana/Winamac" => "America/Indiana/Winamac [EDT -04:00]", "America/Inuvik" => "America/Inuvik [MDT -06:00]", "America/Iqaluit" => "America/Iqaluit [EDT -04:00]", "America/Jamaica" => "America/Jamaica [EST -05:00]", "America/Juneau" => "America/Juneau [AKDT -08:00]", "America/Kentucky/Louisville" => "America/Kentucky/Louisville [EDT -04:00]", "America/Kentucky/Monticello" => "America/Kentucky/Monticello [EDT -04:00]", "America/Kralendijk" => "America/Kralendijk [AST -04:00]", "America/La_Paz" => "America/La_Paz [BOT -04:00]", "America/Lima" => "America/Lima [PET -05:00]", "America/Los_Angeles" => "America/Los_Angeles [PDT -07:00]", "America/Lower_Princes" => "America/Lower_Princes [AST -04:00]", "America/Maceio" => "America/Maceio [BRT -03:00]", "America/Managua" => "America/Managua [CST -06:00]", "America/Manaus" => "America/Manaus [AMT -04:00]", "America/Marigot" => "America/Marigot [AST -04:00]", "America/Martinique" => "America/Martinique [AST -04:00]", "America/Matamoros" => "America/Matamoros [CDT -05:00]", "America/Mazatlan" => "America/Mazatlan [MDT -06:00]", "America/Menominee" => "America/Menominee [CDT -05:00]", "America/Merida" => "America/Merida [CDT -05:00]", "America/Metlakatla" => "America/Metlakatla [PST -08:00]", "America/Mexico_City" => "America/Mexico_City [CDT -05:00]", "America/Miquelon" => "America/Miquelon [PMDT -02:00]", "America/Moncton" => "America/Moncton [ADT -03:00]", "America/Monterrey" => "America/Monterrey [CDT -05:00]", "America/Montevideo" => "America/Montevideo [UYT -03:00]", "America/Montserrat" => "America/Montserrat [AST -04:00]", "America/Nassau" => "America/Nassau [EDT -04:00]", "America/New_York" => "America/New_York [EDT -04:00]", "America/Nipigon" => "America/Nipigon [EDT -04:00]", "America/Nome" => "America/Nome [AKDT -08:00]", "America/Noronha" => "America/Noronha [FNT -02:00]", "America/North_Dakota/Beulah" => "America/North_Dakota/Beulah [CDT -05:00]", "America/North_Dakota/Center" => "America/North_Dakota/Center [CDT -05:00]", "America/North_Dakota/New_Salem" => "America/North_Dakota/New_Salem [CDT -05:00]", "America/Ojinaga" => "America/Ojinaga [MDT -06:00]", "America/Panama" => "America/Panama [EST -05:00]", "America/Pangnirtung" => "America/Pangnirtung [EDT -04:00]", "America/Paramaribo" => "America/Paramaribo [SRT -03:00]", "America/Phoenix" => "America/Phoenix [MST -07:00]", "America/Port-au-Prince" => "America/Port-au-Prince [EDT -04:00]", "America/Port_of_Spain" => "America/Port_of_Spain [AST -04:00]", "America/Porto_Velho" => "America/Porto_Velho [AMT -04:00]", "America/Puerto_Rico" => "America/Puerto_Rico [AST -04:00]", "America/Rainy_River" => "America/Rainy_River [CDT -05:00]", "America/Rankin_Inlet" => "America/Rankin_Inlet [CDT -05:00]", "America/Recife" => "America/Recife [BRT -03:00]", "America/Regina" => "America/Regina [CST -06:00]", "America/Resolute" => "America/Resolute [CDT -05:00]", "America/Rio_Branco" => "America/Rio_Branco [ACT -05:00]", "America/Santa_Isabel" => "America/Santa_Isabel [PDT -07:00]", "America/Santarem" => "America/Santarem [BRT -03:00]", "America/Santiago" => "America/Santiago [CLST -03:00]", "America/Santo_Domingo" => "America/Santo_Domingo [AST -04:00]", "America/Sao_Paulo" => "America/Sao_Paulo [BRT -03:00]", "America/Scoresbysund" => "America/Scoresbysund [EGST  00:00]", "America/Sitka" => "America/Sitka [AKDT -08:00]", "America/St_Barthelemy" => "America/St_Barthelemy [AST -04:00]", "America/St_Johns" => "America/St_Johns [NDT -02:30]", "America/St_Kitts" => "America/St_Kitts [AST -04:00]", "America/St_Lucia" => "America/St_Lucia [AST -04:00]", "America/St_Thomas" => "America/St_Thomas [AST -04:00]", "America/St_Vincent" => "America/St_Vincent [AST -04:00]", "America/Swift_Current" => "America/Swift_Current [CST -06:00]", "America/Tegucigalpa" => "America/Tegucigalpa [CST -06:00]", "America/Thule" => "America/Thule [ADT -03:00]", "America/Thunder_Bay" => "America/Thunder_Bay [EDT -04:00]", "America/Tijuana" => "America/Tijuana [PDT -07:00]", "America/Toronto" => "America/Toronto [EDT -04:00]", "America/Tortola" => "America/Tortola [AST -04:00]", "America/Vancouver" => "America/Vancouver [PDT -07:00]", "America/Whitehorse" => "America/Whitehorse [PDT -07:00]", "America/Winnipeg" => "America/Winnipeg [CDT -05:00]", "America/Yakutat" => "America/Yakutat [AKDT -08:00]", "America/Yellowknife" => "America/Yellowknife [MDT -06:00]", "Antarctica/Casey" => "Antarctica/Casey [AWST +08:00]", "Antarctica/Davis" => "Antarctica/Davis [DAVT +07:00]", "Antarctica/DumontDUrville" => "Antarctica/DumontDUrville [DDUT +10:00]", "Antarctica/Macquarie" => "Antarctica/Macquarie [MIST +11:00]", "Antarctica/Mawson" => "Antarctica/Mawson [MAWT +05:00]", "Antarctica/McMurdo" => "Antarctica/McMurdo [NZDT +13:00]", "Antarctica/Palmer" => "Antarctica/Palmer [CLST -03:00]", "Antarctica/Rothera" => "Antarctica/Rothera [ROTT -03:00]", "Antarctica/Syowa" => "Antarctica/Syowa [SYOT +03:00]", "Antarctica/Troll" => "Antarctica/Troll [CEST +02:00]", "Antarctica/Vostok" => "Antarctica/Vostok [VOST +06:00]", "Arctic/Longyearbyen" => "Arctic/Longyearbyen [CEST +02:00]", "Asia/Aden" => "Asia/Aden [AST +03:00]", "Asia/Almaty" => "Asia/Almaty [ALMT +06:00]", "Asia/Amman" => "Asia/Amman [EEST +03:00]", "Asia/Anadyr" => "Asia/Anadyr [ANAT +12:00]", "Asia/Aqtau" => "Asia/Aqtau [AQTT +05:00]", "Asia/Aqtobe" => "Asia/Aqtobe [AQTT +05:00]", "Asia/Ashgabat" => "Asia/Ashgabat [TMT +05:00]", "Asia/Baghdad" => "Asia/Baghdad [AST +03:00]", "Asia/Bahrain" => "Asia/Bahrain [AST +03:00]", "Asia/Baku" => "Asia/Baku [AZST +05:00]", "Asia/Bangkok" => "Asia/Bangkok [ICT +07:00]", "Asia/Beirut" => "Asia/Beirut [EEST +03:00]", "Asia/Bishkek" => "Asia/Bishkek [KGT +06:00]", "Asia/Brunei" => "Asia/Brunei [BNT +08:00]", "Asia/Chita" => "Asia/Chita [IRKT +08:00]", "Asia/Choibalsan" => "Asia/Choibalsan [CHOT +08:00]", "Asia/Colombo" => "Asia/Colombo [IST +05:30]", "Asia/Damascus" => "Asia/Damascus [EEST +03:00]", "Asia/Dhaka" => "Asia/Dhaka [BDT +06:00]", "Asia/Dili" => "Asia/Dili [TLT +09:00]", "Asia/Dubai" => "Asia/Dubai [GST +04:00]", "Asia/Dushanbe" => "Asia/Dushanbe [TJT +05:00]", "Asia/Gaza" => "Asia/Gaza [EET +02:00]", "Asia/Hebron" => "Asia/Hebron [EET +02:00]", "Asia/Ho_Chi_Minh" => "Asia/Ho_Chi_Minh [ICT +07:00]", "Asia/Hong_Kong" => "Asia/Hong_Kong [HKT +08:00]", "Asia/Hovd" => "Asia/Hovd [HOVT +07:00]", "Asia/Irkutsk" => "Asia/Irkutsk [IRKT +08:00]", "Asia/Jakarta" => "Asia/Jakarta [WIB +07:00]", "Asia/Jayapura" => "Asia/Jayapura [WIT +09:00]", "Asia/Jerusalem" => "Asia/Jerusalem [IDT +03:00]", "Asia/Kabul" => "Asia/Kabul [AFT +04:30]", "Asia/Kamchatka" => "Asia/Kamchatka [PETT +12:00]", "Asia/Karachi" => "Asia/Karachi [PKT +05:00]", "Asia/Kathmandu" => "Asia/Kathmandu [NPT +05:45]", "Asia/Khandyga" => "Asia/Khandyga [YAKT +09:00]", "Asia/Kolkata" => "Asia/Kolkata [IST +05:30]", "Asia/Krasnoyarsk" => "Asia/Krasnoyarsk [KRAT +07:00]", "Asia/Kuala_Lumpur" => "Asia/Kuala_Lumpur [MYT +08:00]", "Asia/Kuching" => "Asia/Kuching [MYT +08:00]", "Asia/Kuwait" => "Asia/Kuwait [AST +03:00]", "Asia/Macau" => "Asia/Macau [CST +08:00]", "Asia/Magadan" => "Asia/Magadan [MAGT +10:00]", "Asia/Makassar" => "Asia/Makassar [WITA +08:00]", "Asia/Manila" => "Asia/Manila [PHT +08:00]", "Asia/Muscat" => "Asia/Muscat [GST +04:00]", "Asia/Nicosia" => "Asia/Nicosia [EEST +03:00]", "Asia/Novokuznetsk" => "Asia/Novokuznetsk [KRAT +07:00]", "Asia/Novosibirsk" => "Asia/Novosibirsk [NOVT +06:00]", "Asia/Omsk" => "Asia/Omsk [OMST +06:00]", "Asia/Oral" => "Asia/Oral [ORAT +05:00]", "Asia/Phnom_Penh" => "Asia/Phnom_Penh [ICT +07:00]", "Asia/Pontianak" => "Asia/Pontianak [WIB +07:00]", "Asia/Pyongyang" => "Asia/Pyongyang [KST +09:00]", "Asia/Qatar" => "Asia/Qatar [AST +03:00]", "Asia/Qyzylorda" => "Asia/Qyzylorda [QYZT +06:00]", "Asia/Rangoon" => "Asia/Rangoon [MMT +06:30]", "Asia/Riyadh" => "Asia/Riyadh [AST +03:00]", "Asia/Sakhalin" => "Asia/Sakhalin [SAKT +10:00]", "Asia/Samarkand" => "Asia/Samarkand [UZT +05:00]", "Asia/Seoul" => "Asia/Seoul [KST +09:00]", "Asia/Shanghai" => "Asia/Shanghai [CST +08:00]", "Asia/Singapore" => "Asia/Singapore [SGT +08:00]", "Asia/Srednekolymsk" => "Asia/Srednekolymsk [SRET +11:00]", "Asia/Taipei" => "Asia/Taipei [CST +08:00]", "Asia/Tashkent" => "Asia/Tashkent [UZT +05:00]", "Asia/Tbilisi" => "Asia/Tbilisi [GET +04:00]", "Asia/Tehran" => "Asia/Tehran [IRST +03:30]", "Asia/Thimphu" => "Asia/Thimphu [BTT +06:00]", "Asia/Tokyo" => "Asia/Tokyo [JST +09:00]", "Asia/Ulaanbaatar" => "Asia/Ulaanbaatar [ULAT +08:00]", "Asia/Urumqi" => "Asia/Urumqi [XJT +06:00]", "Asia/Ust-Nera" => "Asia/Ust-Nera [VLAT +10:00]", "Asia/Vientiane" => "Asia/Vientiane [ICT +07:00]", "Asia/Vladivostok" => "Asia/Vladivostok [VLAT +10:00]", "Asia/Yakutsk" => "Asia/Yakutsk [YAKT +09:00]", "Asia/Yekaterinburg" => "Asia/Yekaterinburg [YEKT +05:00]", "Asia/Yerevan" => "Asia/Yerevan [AMT +04:00]", "Atlantic/Azores" => "Atlantic/Azores [AZOST  00:00]", "Atlantic/Bermuda" => "Atlantic/Bermuda [ADT -03:00]", "Atlantic/Canary" => "Atlantic/Canary [WEST +01:00]", "Atlantic/Cape_Verde" => "Atlantic/Cape_Verde [CVT -01:00]", "Atlantic/Faroe" => "Atlantic/Faroe [WEST +01:00]", "Atlantic/Madeira" => "Atlantic/Madeira [WEST +01:00]", "Atlantic/Reykjavik" => "Atlantic/Reykjavik [GMT  00:00]", "Atlantic/South_Georgia" => "Atlantic/South_Georgia [GST -02:00]", "Atlantic/St_Helena" => "Atlantic/St_Helena [GMT  00:00]", "Atlantic/Stanley" => "Atlantic/Stanley [FKST -03:00]", "Australia/Adelaide" => "Australia/Adelaide [ACDT +10:30]", "Australia/Brisbane" => "Australia/Brisbane [AEST +10:00]", "Australia/Broken_Hill" => "Australia/Broken_Hill [ACDT +10:30]", "Australia/Currie" => "Australia/Currie [AEDT +11:00]", "Australia/Darwin" => "Australia/Darwin [ACST +09:30]", "Australia/Eucla" => "Australia/Eucla [ACWST +08:45]", "Australia/Hobart" => "Australia/Hobart [AEDT +11:00]", "Australia/Lindeman" => "Australia/Lindeman [AEST +10:00]", "Australia/Lord_Howe" => "Australia/Lord_Howe [LHDT +11:00]", "Australia/Melbourne" => "Australia/Melbourne [AEDT +11:00]", "Australia/Perth" => "Australia/Perth [AWST +08:00]", "Australia/Sydney" => "Australia/Sydney [AEDT +11:00]", "Europe/Amsterdam" => "Europe/Amsterdam [CEST +02:00]", "Europe/Andorra" => "Europe/Andorra [CEST +02:00]", "Europe/Athens" => "Europe/Athens [EEST +03:00]", "Europe/Belgrade" => "Europe/Belgrade [CEST +02:00]", "Europe/Berlin" => "Europe/Berlin [CEST +02:00]", "Europe/Bratislava" => "Europe/Bratislava [CEST +02:00]", "Europe/Brussels" => "Europe/Brussels [CEST +02:00]", "Europe/Bucharest" => "Europe/Bucharest [EEST +03:00]", "Europe/Budapest" => "Europe/Budapest [CEST +02:00]", "Europe/Busingen" => "Europe/Busingen [CEST +02:00]", "Europe/Chisinau" => "Europe/Chisinau [EEST +03:00]", "Europe/Copenhagen" => "Europe/Copenhagen [CEST +02:00]", "Europe/Dublin" => "Europe/Dublin [IST +01:00]", "Europe/Gibraltar" => "Europe/Gibraltar [CEST +02:00]", "Europe/Guernsey" => "Europe/Guernsey [BST +01:00]", "Europe/Helsinki" => "Europe/Helsinki [EEST +03:00]", "Europe/Isle_of_Man" => "Europe/Isle_of_Man [BST +01:00]", "Europe/Istanbul" => "Europe/Istanbul [EEST +03:00]", "Europe/Jersey" => "Europe/Jersey [BST +01:00]", "Europe/Kaliningrad" => "Europe/Kaliningrad [EET +02:00]", "Europe/Kiev" => "Europe/Kiev [EEST +03:00]", "Europe/Lisbon" => "Europe/Lisbon [WEST +01:00]", "Europe/Ljubljana" => "Europe/Ljubljana [CEST +02:00]", "Europe/London" => "Europe/London [BST +01:00]", "Europe/Luxembourg" => "Europe/Luxembourg [CEST +02:00]", "Europe/Madrid" => "Europe/Madrid [CEST +02:00]", "Europe/Malta" => "Europe/Malta [CEST +02:00]", "Europe/Mariehamn" => "Europe/Mariehamn [EEST +03:00]", "Europe/Minsk" => "Europe/Minsk [MSK +03:00]", "Europe/Monaco" => "Europe/Monaco [CEST +02:00]", "Europe/Moscow" => "Europe/Moscow [MSK +03:00]", "Europe/Oslo" => "Europe/Oslo [CEST +02:00]", "Europe/Paris" => "Europe/Paris [CEST +02:00]", "Europe/Podgorica" => "Europe/Podgorica [CEST +02:00]", "Europe/Prague" => "Europe/Prague [CEST +02:00]", "Europe/Riga" => "Europe/Riga [EEST +03:00]", "Europe/Rome" => "Europe/Rome [CEST +02:00]", "Europe/Samara" => "Europe/Samara [SAMT +04:00]", "Europe/San_Marino" => "Europe/San_Marino [CEST +02:00]", "Europe/Sarajevo" => "Europe/Sarajevo [CEST +02:00]", "Europe/Simferopol" => "Europe/Simferopol [MSK +03:00]", "Europe/Skopje" => "Europe/Skopje [CEST +02:00]", "Europe/Sofia" => "Europe/Sofia [EEST +03:00]", "Europe/Stockholm" => "Europe/Stockholm [CEST +02:00]", "Europe/Tallinn" => "Europe/Tallinn [EEST +03:00]", "Europe/Tirane" => "Europe/Tirane [CEST +02:00]", "Europe/Uzhgorod" => "Europe/Uzhgorod [EEST +03:00]", "Europe/Vaduz" => "Europe/Vaduz [CEST +02:00]", "Europe/Vatican" => "Europe/Vatican [CEST +02:00]", "Europe/Vienna" => "Europe/Vienna [CEST +02:00]", "Europe/Vilnius" => "Europe/Vilnius [EEST +03:00]", "Europe/Volgograd" => "Europe/Volgograd [MSK +03:00]", "Europe/Warsaw" => "Europe/Warsaw [CEST +02:00]", "Europe/Zagreb" => "Europe/Zagreb [CEST +02:00]", "Europe/Zaporozhye" => "Europe/Zaporozhye [EEST +03:00]", "Europe/Zurich" => "Europe/Zurich [CEST +02:00]", "Indian/Antananarivo" => "Indian/Antananarivo [EAT +03:00]", "Indian/Chagos" => "Indian/Chagos [IOT +06:00]", "Indian/Christmas" => "Indian/Christmas [CXT +07:00]", "Indian/Cocos" => "Indian/Cocos [CCT +06:30]", "Indian/Comoro" => "Indian/Comoro [EAT +03:00]", "Indian/Kerguelen" => "Indian/Kerguelen [TFT +05:00]", "Indian/Mahe" => "Indian/Mahe [SCT +04:00]", "Indian/Maldives" => "Indian/Maldives [MVT +05:00]", "Indian/Mauritius" => "Indian/Mauritius [MUT +04:00]", "Indian/Mayotte" => "Indian/Mayotte [EAT +03:00]", "Indian/Reunion" => "Indian/Reunion [RET +04:00]", "Pacific/Apia" => "Pacific/Apia [WSDT +14:00]", "Pacific/Auckland" => "Pacific/Auckland [NZDT +13:00]", "Pacific/Bougainville" => "Pacific/Bougainville [BST +11:00]", "Pacific/Chatham" => "Pacific/Chatham [CHADT +13:45]", "Pacific/Chuuk" => "Pacific/Chuuk [CHUT +10:00]", "Pacific/Easter" => "Pacific/Easter [EASST -05:00]", "Pacific/Efate" => "Pacific/Efate [VUT +11:00]", "Pacific/Enderbury" => "Pacific/Enderbury [PHOT +13:00]", "Pacific/Fakaofo" => "Pacific/Fakaofo [TKT +13:00]", "Pacific/Fiji" => "Pacific/Fiji [FJT +12:00]", "Pacific/Funafuti" => "Pacific/Funafuti [TVT +12:00]", "Pacific/Galapagos" => "Pacific/Galapagos [GALT -06:00]", "Pacific/Gambier" => "Pacific/Gambier [GAMT -09:00]", "Pacific/Guadalcanal" => "Pacific/Guadalcanal [SBT +11:00]", "Pacific/Guam" => "Pacific/Guam [ChST +10:00]", "Pacific/Honolulu" => "Pacific/Honolulu [HST -10:00]", "Pacific/Johnston" => "Pacific/Johnston [HST -10:00]", "Pacific/Kiritimati" => "Pacific/Kiritimati [LINT +14:00]", "Pacific/Kosrae" => "Pacific/Kosrae [KOST +11:00]", "Pacific/Kwajalein" => "Pacific/Kwajalein [MHT +12:00]", "Pacific/Majuro" => "Pacific/Majuro [MHT +12:00]", "Pacific/Marquesas" => "Pacific/Marquesas [MART -09:30]", "Pacific/Midway" => "Pacific/Midway [SST -11:00]", "Pacific/Nauru" => "Pacific/Nauru [NRT +12:00]", "Pacific/Niue" => "Pacific/Niue [NUT -11:00]", "Pacific/Norfolk" => "Pacific/Norfolk [NFT +11:30]", "Pacific/Noumea" => "Pacific/Noumea [NCT +11:00]", "Pacific/Pago_Pago" => "Pacific/Pago_Pago [SST -11:00]", "Pacific/Palau" => "Pacific/Palau [PWT +09:00]", "Pacific/Pitcairn" => "Pacific/Pitcairn [PST -08:00]", "Pacific/Pohnpei" => "Pacific/Pohnpei [PONT +11:00]", "Pacific/Port_Moresby" => "Pacific/Port_Moresby [PGT +10:00]", "Pacific/Rarotonga" => "Pacific/Rarotonga [CKT -10:00]", "Pacific/Saipan" => "Pacific/Saipan [ChST +10:00]", "Pacific/Tahiti" => "Pacific/Tahiti [TAHT -10:00]", "Pacific/Tarawa" => "Pacific/Tarawa [GILT +12:00]", "Pacific/Tongatapu" => "Pacific/Tongatapu [TOT +13:00]", "Pacific/Wake" => "Pacific/Wake [WAKT +12:00]", "Pacific/Wallis" => "Pacific/Wallis [WFT +12:00]", "UTC" => "UTC [UTC  00:00]");

function getRegisteredUser($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `reg_users` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function shutdown_admin() {
    global $ipTV_db_admin;

    if (is_object($ipTV_db_admin)) {
        $ipTV_db_admin->close_mysql();
    }
}


function getRegisteredUserHash($rHash) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `reg_users` WHERE MD5(`username`) = ? LIMIT 1;", $rHash);
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function doLogin($rUsername, $rPassword) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `id`, `username`, `password`, `member_group_id`,`status` FROM `reg_users` WHERE `username` = ? LIMIT 1;", $rUsername);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rRow = $ipTV_db_admin->get_row();

        if (cryptPassword($rPassword) == $rRow["password"]) {
            return $rRow;
        }
    }
    return null;
}

function cryptPassword($password, $salt = "xc_vm", $rounds = 20000) {
    if ($salt == "") {
        $salt = substr(bin2hex(openssl_random_pseudo_bytes(16)), 0, 16);
    }
    $hash = crypt($password, sprintf('$6$rounds=%d$%s$', $rounds, $salt));
    return $hash;
}

function getUser($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `lines` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getPermissions($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `member_groups` WHERE `group_id` = ?;", intval($rID));
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getCategories_admin($rType = "live") {
    global $ipTV_db_admin;
    $return = array();
    if ($rType) {
        $ipTV_db_admin->query("SELECT * FROM `stream_categories` WHERE `category_type` = '" . $rType . "' ORDER BY `cat_order` ASC;");
    } else {
        $ipTV_db_admin->query("SELECT * FROM `stream_categories` ORDER BY `cat_order` ASC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $rRow) {
            $return[intval($rRow['id'])] = $rRow;
        }
    }
    return $return;
}

function getStreamingServers($online = false) {
    global $ipTV_db_admin, $rPermissions;
    $return = array();
    if ($online) {
        $ipTV_db_admin->query("SELECT * FROM `servers` WHERE `status` = 1 ORDER BY `id` ASC;");
    } else {
        $ipTV_db_admin->query("SELECT * FROM `servers` ORDER BY `id` ASC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if (isset($rPermissions["is_reseller"]) && $rPermissions["is_reseller"]) {
                $row["server_name"] = "Server #" . $row["id"];
            }
            $return[intval($row['id'])] = $row;
        }
    }
    return $return;
}

function generateString($strength = 10) {
    $input = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $input_length = strlen($input);
    $random_string = '';
    for ($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}


function isSecure() {
    // Check for HTTPS in a web server environment
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    // Check for secure port
    if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        return true;
    }

    return false;
}
function getProtocol() {
    if (isSecure()) {
        return 'https';
    }
    return 'http';
}

function getScriptVer() {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `script_version` FROM `servers` WHERE `is_main` = '1'");
    $version = $ipTV_db_admin->get_row()["script_version"];
    return $version;
}

function getFooter() {
    // Don't be a dick. Leave it.
    global $rPermissions, $rSettings, $_;
    if ($rPermissions["is_admin"]) {
        return $_["copyright"] . " &copy; 2023 - " . date("Y") . " - <a href=\"https://github.com/Vateron-Media/Xtream_main\">Xtream UI</a> " . getScriptVer() . " - " . $_["free_forever"];
    } else {
        return $rSettings["copyrights_text"];
    }
}

function getIP() {
    $ip = null;

    // Check IP in order of priority
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // May contain a list of IPs, take the first one (original IP)
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    }

    // Check if the IP address is correct
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        // XSS protection (if IP is displayed on the page)
        return htmlspecialchars($ip, ENT_QUOTES, 'UTF-8');
    }

    // If IP is incorrect, return null
    return null;
}

function changePort($rServerID, $rType, $rPort, $rReload = false) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServerID, time(), json_encode(array('action' => 'set_port', 'type' => intval($rType), 'port' => $rPort, 'reload' => $rReload)));
}

function setServices($rServerID, $rNumServices, $rReload = true) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServerID, time(), json_encode(array('action' => 'set_services', 'count' => intval($rNumServices), 'reload' => $rReload)));
}

function setSysctl($rServerID, $rSysCtl) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServerID, time(), json_encode(array('action' => 'set_sysctl', 'data' => $rSysCtl)));
}

function scanBouquets() {
    shell_exec(PHP_BIN . ' ' . CLI_PATH . 'tools.php "bouquets" > /dev/null 2>/dev/null &');
}

function scanBouquet($rID) {
    global $ipTV_db_admin;
    $rBouquet = getBouquet($rID);
    if ($rBouquet) {
        $rStreamIDs = array();
        $ipTV_db_admin->query("SELECT `id` FROM `streams`;");
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $row) {
                $rStreamIDs[0][] = intval($row['id']);
            }
        }
        $ipTV_db_admin->query("SELECT `id` FROM `series`;");
        if ($ipTV_db_admin->num_rows() > 0) {
            foreach ($ipTV_db_admin->get_rows() as $row) {
                $rStreamIDs[1][] = intval($row['id']);
            }
        }
        $rUpdate = array(array(), array(), array(), array());
        foreach (json_decode($rBouquet['bouquet_channels'], true) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[0])) {
                $rUpdate[0][] = intval($rID);
            }
        }
        foreach (json_decode($rBouquet['bouquet_movies'], true) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[0])) {
                $rUpdate[1][] = intval($rID);
            }
        }
        foreach (json_decode($rBouquet['bouquet_radios'], true) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[0])) {
                $rUpdate[2][] = intval($rID);
            }
        }
        foreach (json_decode($rBouquet['bouquet_series'], true) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[1])) {
                $rUpdate[3][] = intval($rID);
            }
        }
        $ipTV_db_admin->query("UPDATE `bouquets` SET `bouquet_channels` = '" . json_encode($rUpdate[0]) . "', `bouquet_movies` = '" . json_encode($rUpdate[1]) . "', `bouquet_radios` = '" . json_encode($rUpdate[2]) . "', `bouquet_series` = '" . json_encode($rUpdate[3]) . "' WHERE `id` = " . intval($rBouquet["id"]) . ";");
    }
}

function getBackups() {
    $rBackups = array();

    # create directory backups
    if (!is_dir(MAIN_DIR . "backups/")) {
        mkdir(MAIN_DIR . "backups/");
    }

    foreach (scandir(MAIN_DIR . "backups/") as $rBackup) {
        $rInfo = pathinfo(MAIN_DIR . "backups/" . $rBackup);
        if ($rInfo["extension"] == "sql") {
            $rBackups[] = array("filename" => $rBackup, "timestamp" => filemtime(MAIN_DIR . "backups/" . $rBackup), "date" => date("Y-m-d H:i:s", filemtime(MAIN_DIR . "backups/" . $rBackup)), "filesize" => filesize(MAIN_DIR . "backups/" . $rBackup));
        }
    }
    usort($rBackups, function ($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });
    return $rBackups;
}

function hasPermissions($rType, $rID) {
    global $rUserInfo, $ipTV_db_admin, $rPermissions;

    if (isset($rUserInfo) && isset($rPermissions)) {
        if ($rType == "user") {
            if (in_array(intval(getUser($rID)["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                return true;
            }
        } elseif ($rType == "pid") {
            $ipTV_db_admin->query("SELECT `user_id` FROM `lines_live` WHERE `pid` = " . intval($rID) . ";");
            if ($ipTV_db_admin->num_rows() > 0) {
                if (in_array(intval(getUser($ipTV_db_admin->get_row()["user_id"])["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                    return true;
                }
            }
        } elseif ($rType == "reg_user") {
            if ((in_array(intval($rID), array_keys(getRegisteredUsers($rUserInfo["id"])))) && (intval($rID) <> intval($rUserInfo["id"]))) {
                return true;
            }
        } elseif ($rType == "ticket") {
            if (in_array(intval(getTicket($rID)["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                return true;
            }
        } elseif ($rType == "mag") {
            $ipTV_db_admin->query("SELECT `user_id` FROM `mag_devices` WHERE `mag_id` = " . intval($rID) . ";");
            if ($ipTV_db_admin->num_rows() > 0) {
                if (in_array(intval(getUser($ipTV_db_admin->get_row()["user_id"])["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                    return true;
                }
            }
        } elseif ($rType == "e2") {
            $ipTV_db_admin->query("SELECT `user_id` FROM `enigma2_devices` WHERE `device_id` = " . intval($rID) . ";");
            if ($ipTV_db_admin->num_rows() > 0) {
                if (in_array(intval(getUser($ipTV_db_admin->get_row()["user_id"])["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                    return true;
                }
            }
        }
        if (!($rType == 'adv' && $rPermissions['is_admin'])) {
            return false;
        }

        if (0 < count($rPermissions['advanced']) && $rUserInfo['member_group_id'] != 1) {
            return in_array($rID, ($rPermissions['advanced'] ?: array()));
        }
        return true;
    }
    return false;
}

function getPageName() {
    return strtolower(basename(get_included_files()[0], '.php'));
}

function checkPermissions($rPage = null) {
    if (!$rPage) {
        $rPage = strtolower(basename($_SERVER['SCRIPT_FILENAME'], '.php'));
    }
    switch ($rPage) {
        case 'server_install':
            return hasPermissions('adv', 'add_server');
        case 'backups':
        case 'cache':
        case 'setup':
            return hasPermissions('adv', 'database');
        case 'server':
            if (isset(ipTV_lib::$request['id']) && hasPermissions('adv', 'edit_server')) {
                return true;
            }
            if (isset(ipTV_lib::$request['id']) || !hasPermissions('adv', 'add_server')) {
                break;
            }
            return true;
        case 'reg_user':
            if (isset(ipTV_lib::$request['id']) && hasPermissions('adv', 'edit_reguser')) {
                return true;
            }

            if (isset(ipTV_lib::$request['id']) || !hasPermissions('adv', 'add_reguser')) {
                break;
            }
            return true;
        default:
            return true;
    }
}

function goHome() {
    header('Location: dashboard');
    exit();
}

function verifyPostTable($rTable, $rData = array(), $rOnlyExisting = false) {
    global $ipTV_db_admin;
    $rReturn = array();
    $ipTV_db_admin->query('SELECT `column_name`, `column_default`, `is_nullable`, `data_type` FROM `information_schema`.`columns` WHERE `table_schema` = (SELECT DATABASE()) AND `table_name` = ? ORDER BY `ordinal_position`;', $rTable);

    foreach ($ipTV_db_admin->get_rows() as $rRow) {
        if ($rRow['column_default'] == 'NULL') {
            $rRow['column_default'] = null;
        }
        $rForceDefault = false;
        if ($rRow['is_nullable'] == 'NO' || !$rRow['column_default']) {
            if (in_array($rRow['data_type'], array('int', 'float', 'tinyint', 'double', 'decimal', 'smallint', 'mediumint', 'bigint', 'bit'))) {
                $rRow['column_default'] = 0;
            } else {
                $rRow['column_default'] = '';
            }
            $rForceDefault = true;
        }
        if (array_key_exists($rRow['column_name'], $rData)) {
            if (empty($rData[$rRow['column_name']]) && !is_numeric($rData[$rRow['column_name']]) && is_null($rRow['column_default'])) {
                $rReturn[$rRow['column_name']] = ($rForceDefault ? $rRow['column_default'] : null);
            } else {
                $rReturn[$rRow['column_name']] = $rData[$rRow['column_name']];
            }
        } else {
            if (!$rOnlyExisting) {
                $rReturn[$rRow['column_name']] = $rRow['column_default'];
            }
        }
    }
    return $rReturn;
}

function preparecolumn($rValue) {
    return strtolower(preg_replace('/[^a-z0-9_]+/i', '', $rValue));
}

/**
 * Prepares an array for SQL operations by formatting its keys and values.
 *
 * This function takes an associative array and prepares it for use in SQL queries.
 * It handles column names, placeholders for prepared statements, and data formatting.
 *
 * @param array $rArray The input associative array to be processed.
 *
 * @return array An array with the following keys:
 *               - 'placeholder': A string of comma-separated question marks for prepared statements.
 *               - 'columns': A string of comma-separated, backtick-enclosed column names.
 *               - 'data': An array of values, with arrays JSON-encoded and 'null' values set to null.
 *               - 'update': A string formatted for SQL UPDATE statements (column = ?).
 */
function prepareArray($rArray) {
    $rUpdate = $rColumns = $rPlaceholder = $rData = array();
    foreach (array_keys($rArray) as $rKey) {
        $rColumns[] = '`' . preparecolumn($rKey) . '`';
        $rUpdate[] = '`' . preparecolumn($rKey) . '` = ?';
    }
    foreach (array_values($rArray) as $rValue) {
        if (is_array($rValue)) {
            $rValue = json_encode($rValue, JSON_UNESCAPED_UNICODE);
        } else {
            if (!(is_null($rValue) || strtolower($rValue) == 'null')) {
            } else {
                $rValue = null;
            }
        }
        $rPlaceholder[] = '?';
        $rData[] = $rValue;
    }
    return array('placeholder' => implode(',', $rPlaceholder), 'columns' => implode(',', $rColumns), 'data' => $rData, 'update' => implode(',', $rUpdate));
}

function resetSTB($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("UPDATE `mag_devices` SET `ip` = NULL, `ver` = NULL, `image_version` = NULL, `stb_type` = NULL, `sn` = NULL, `device_id` = NULL, `device_id2` = NULL, `hw_version` = NULL, `token` = NULL WHERE `mag_id` = ?;", $rID);
}

function getSettings() {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `settings`;");
    foreach ($ipTV_db_admin->get_rows() as $row) {
        $return[$row["name"]] = $row["value"];
    }
    return $return;
}

function getPanelLogs() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `panel_logs` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getStreamingServersByID($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `servers` WHERE `id` = ?;", $rID);
    if ($ipTV_db_admin->num_rows() > 0) {
        return $ipTV_db_admin->get_row();
    }
    return false;
}

function getStreamList() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `stream_categories`.`category_name` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` ORDER BY `streams`.`stream_display_name` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getConnections($rServerID) {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `lines_live` WHERE `server_id` = '" . $rServerID . "';");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getEPGSources() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `epg`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[$row["id"]] = $row;
        }
    }
    return $return;
}

function findEPG($rEPGName) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `id`, `data` FROM `epg`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            foreach (json_decode($row["data"], true) as $rChannelID => $rChannelData) {
                if ($rChannelID == $rEPGName) {
                    if (count($rChannelData["langs"]) > 0) {
                        $rEPGLang = $rChannelData["langs"][0];
                    } else {
                        $rEPGLang = "";
                    }
                    return array("channel_id" => $rChannelID, "epg_lang" => $rEPGLang, "epg_id" => intval($row["id"]));
                }
            }
        }
    }
    return null;
}

function getStreamArguments() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `streams_arguments` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[$row["argument_key"]] = $row;
        }
    }
    return $return;
}

function getTranscodeProfiles() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `transcoding_profiles` ORDER BY `profile_id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getWatchFolders($rType = null) {
    global $ipTV_db_admin;
    $return = array();
    if ($rType) {
        $ipTV_db_admin->query("SELECT * FROM `watch_folders` WHERE `type` = '" . $rType . "' ORDER BY `id` ASC;");
    } else {
        $ipTV_db_admin->query("SELECT * FROM `watch_folders` ORDER BY `id` ASC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getWatchCategories($rType = null) {
    global $ipTV_db_admin;
    $return = array();
    if ($rType) {
        $ipTV_db_admin->query("SELECT * FROM `watch_categories` WHERE `type` = " . intval($rType) . " ORDER BY `genre_id` ASC;");
    } else {
        $ipTV_db_admin->query("SELECT * FROM `watch_categories` ORDER BY `genre_id` ASC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[$row["genre_id"]] = $row;
        }
    }
    return $return;
}

function getWatchFolder($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `watch_folders` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getSeriesByTMDB($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `series` WHERE `tmdb_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getSeries() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `series` ORDER BY `title` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getSerie($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `series` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}


function getUserAgents() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `blocked_user_agents` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getISPs() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `isp_addon` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getBlockedIPs() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `blocked_ips` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getSystemLogs() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `mysql_syslog` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getBlockedLogins() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `login_flood` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}
function getRTMPIPs() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `rtmp_ips` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getStream($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `streams` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}



function getEPG($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `epg` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getStreamOptions($rID) {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `streams_options` WHERE `stream_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["argument_id"])] = $row;
        }
    }
    return $return;
}

function getStreamSys($rID) {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `streams_servers` WHERE `stream_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["server_id"])] = $row;
        }
    }
    return $return;
}

function getRegisteredUsers($rOwner = null, $rIncludeSelf = true) {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `reg_users` ORDER BY `username` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if ((!$rOwner) or ($row["owner_id"] == $rOwner) or (($row["id"] == $rOwner) && ($rIncludeSelf))) {
                $return[intval($row["id"])] = $row;
            }
        }
    }
    if (count($return) == 0) {
        $return[-1] = array();
    }
    return $return;
}

function getMemberGroups() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `member_groups` ORDER BY `group_id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["group_id"])] = $row;
        }
    }
    return $return;
}

function getMemberGroup($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `member_groups` WHERE `group_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getOutputs($rUser = null) {
    global $ipTV_db_admin;
    $return = array();
    if ($rUser) {
        $ipTV_db_admin->query("SELECT `allowed_outputs` FROM `lines` WHERE `id` = " . intval($rUser) . ";");
    } else {
        $ipTV_db_admin->query("SELECT * FROM `access_output` ORDER BY `access_output_id` ASC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if ($rUser) {
                $return = json_decode($row["allowed_outputs"]);
            } else {
                $return[] = $row;
            }
        }
    }
    return $return;
}

function getUserBouquets() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT `id`, `bouquet` FROM `lines` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getBouquets() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `bouquets` ORDER BY `bouquet_order` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getBouquetOrder() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `bouquets` ORDER BY `bouquet_order` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getBouquet($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function addToBouquet($rType, $rBouquetID, $rID) {
    global $ipTV_db_admin;
    $rBouquet = getBouquet($rBouquetID);
    if ($rBouquet) {
        if ($rType == "stream") {
            $rColumn = "bouquet_channels";
        } elseif ($rType == "movie") {
            $rColumn = "bouquet_movies";
        } elseif ($rType == "radio") {
            $rColumn = "bouquet_radios";
        } else {
            $rColumn = "bouquet_series";
        }
        $rChannels = json_decode($rBouquet[$rColumn], true);
        if (!in_array($rID, $rChannels)) {
            $rChannels[] = $rID;
            if (count($rChannels) > 0) {
                $ipTV_db_admin->query("UPDATE `bouquets` SET `" . $rColumn . "` = '" . json_encode(array_values($rChannels)) . "' WHERE `id` = " . intval($rBouquetID) . ";");
            }
        }
    }
}

function removeFromBouquet($rType, $rBouquetID, $rID) {
    global $ipTV_db_admin;
    $rBouquet = getBouquet($rBouquetID);
    if ($rBouquet) {
        if ($rType == "stream") {
            $rColumn = "bouquet_channels";
        } elseif ($rType == "movie") {
            $rColumn = "bouquet_movies";
        } elseif ($rType == "radio") {
            $rColumn = "bouquet_radios";
        } else {
            $rColumn = "bouquet_series";
        }
        $rChannels = json_decode($rBouquet[$rColumn], true);
        if (($rKey = array_search($rID, $rChannels)) !== false) {
            unset($rChannels[$rKey]);
            $ipTV_db_admin->query("UPDATE `bouquets` SET `" . $rColumn . "` = '" . json_encode(array_values($rChannels)) . "' WHERE `id` = " . intval($rBouquetID) . ";");
        }
    }
}

function getPackages($rGroup = null) {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `packages` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if ((!isset($rGroup)) or (in_array(intval($rGroup), json_decode($row["groups"], true)))) {
                $return[intval($row["id"])] = $row;
            }
        }
    }
    return $return;
}

function getPackage($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `packages` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getTranscodeProfile($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `transcoding_profiles` WHERE `profile_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getUserAgent($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `blocked_user_agents` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getISP($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `isp_addon` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getRTMPIP($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `rtmp_ips` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getEPGs() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `epg` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getCategory($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `stream_categories` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return false;
}

function getMag($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `mag_devices` WHERE `mag_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        $row = $ipTV_db_admin->get_row();
        $ipTV_db_admin->query("SELECT `pair_id` FROM `lines` WHERE `id` = " . intval($row["user_id"]) . ";");
        if ($ipTV_db_admin->num_rows() == 1) {
            $magrow = $ipTV_db_admin->get_row();
            $row["paired_user"] = $magrow["pair_id"];
            $row["username"] = getUser($row["paired_user"])["username"];
        }
        return $row;
    }
    return array();
}

function getEnigma($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `enigma2_devices` WHERE `device_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        $row = $ipTV_db_admin->get_row();
        $ipTV_db_admin->query("SELECT `pair_id` FROM `lines` WHERE `id` = " . intval($row["user_id"]) . ";");
        if ($ipTV_db_admin->num_rows() == 1) {
            $e2row = $ipTV_db_admin->get_row();
            $row["paired_user"] = $e2row["pair_id"];
            $row["username"] = getUser($row["paired_user"])["username"];
        }
        return $row;
    }
    return array();
}

function getMAGUser($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `mag_devices` WHERE `user_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return "";
}

function getE2User($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `enigma2_devices` WHERE `user_id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return "";
}

function getTicket($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `tickets` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        $row = $ipTV_db_admin->get_row();
        $row["replies"] = array();
        $row["title"] = htmlspecialchars($row["title"]);
        $ipTV_db_admin->query("SELECT * FROM `tickets_replies` WHERE `ticket_id` = " . intval($rID) . " ORDER BY `date` ASC;");
        while ($reply = $ipTV_db_admin->get_row()) {
            // Hack to fix display issues on short text.
            $reply["message"] = htmlspecialchars($reply["message"]);
            if (strlen($reply["message"]) < 80) {
                $reply["message"] .= str_repeat("&nbsp; ", 80 - strlen($reply["message"]));
            }
            $row["replies"][] = $reply;
        }
        $row["user"] = getRegisteredUser($row["member_id"]);
        return $row;
    }
    return null;
}

function getExpiring($rID) {
    global $ipTV_db_admin;
    $rAvailableMembers = array_keys(getRegisteredUsers($rID));
    $return = array();
    $ipTV_db_admin->query("SELECT `id`, `member_id`, `username`, `password`, `exp_date` FROM `lines` WHERE `member_id` IN (" . join(",", $rAvailableMembers) . ") AND `exp_date` >= UNIX_TIMESTAMP() ORDER BY `exp_date` ASC LIMIT 100;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

function getTickets($rID = null) {
    global $ipTV_db_admin;
    $return = array();
    if ($rID) {
        $ipTV_db_admin->query("SELECT `tickets`.`id`, `tickets`.`member_id`, `tickets`.`title`, `tickets`.`status`, `tickets`.`admin_read`, `tickets`.`user_read`, `reg_users`.`username` FROM `tickets`, `reg_users` WHERE `member_id` = " . intval($rID) . " AND `reg_users`.`id` = `tickets`.`member_id` ORDER BY `id` DESC;");
    } else {
        $ipTV_db_admin->query("SELECT `tickets`.`id`, `tickets`.`member_id`, `tickets`.`title`, `tickets`.`status`, `tickets`.`admin_read`, `tickets`.`user_read`, `reg_users`.`username` FROM `tickets`, `reg_users` WHERE `reg_users`.`id` = `tickets`.`member_id` ORDER BY `id` DESC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $ipTV_db_admin->query("SELECT MIN(`date`) AS `date` FROM `tickets_replies` WHERE `ticket_id` = " . intval($row["id"]) . " AND `admin_reply` = 0;");
            if ($rDate = $ipTV_db_admin->get_row()["date"]) {
                $row["created"] = date("Y-m-d H:i", $rDate);
            } else {
                $row["created"] = "";
            }
            $ipTV_db_admin->query("SELECT MAX(`date`) AS `date` FROM `tickets_replies` WHERE `ticket_id` = " . intval($row["id"]) . " AND `admin_reply` = 1;");
            if ($rDate = $ipTV_db_admin->get_row()["date"]) {
                $row["last_reply"] = date("Y-m-d H:i", $rDate);
            } else {
                $row["last_reply"] = "";
            }
            if ($row["status"] <> 0) {
                if ($row["user_read"] == 0) {
                    $row["status"] = 2;
                }
                if ($row["admin_read"] == 1) {
                    $row["status"] = 3;
                }
            }
            $return[] = $row;
        }
    }
    return $return;
}

function checkTrials() {
    global $ipTV_db_admin, $rPermissions, $rUserInfo;
    $rTotal = $rPermissions["total_allowed_gen_trials"];
    if ($rTotal > 0) {
        $rTotalIn = $rPermissions["total_allowed_gen_in"];
        if ($rTotalIn == "hours") {
            $rTime = time() - (intval($rTotal) * 3600);
        } else {
            $rTime = time() - (intval($rTotal) * 3600 * 24);
        }
        $ipTV_db_admin->query("SELECT COUNT(`id`) AS `count` FROM `lines` WHERE `member_id` = " . intval($rUserInfo["id"]) . " AND `created_at` >= " . $rTime . " AND `is_trial` = 1;");
        return $ipTV_db_admin->get_row()["count"] < $rTotal;
    }
    return false;
}

function getSubresellerSetups() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT * FROM `subreseller_setup` ORDER BY `id` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getSubresellerSetup($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `subreseller_setup` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getEpisodeParents() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT `series_episodes`.`stream_id`, `series`.`id`, `series`.`title` FROM `series_episodes` LEFT JOIN `series` ON `series`.`id` = `series_episodes`.`series_id`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["stream_id"])] = $row;
        }
    }
    return $return;
}

function getSeriesList() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT `id`, `title` FROM `series` ORDER BY `title` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getWorldMapLive() {
    global $ipTV_db_admin;
    $rQuery = "SELECT geoip_country_code, count(geoip_country_code) AS total FROM lines_live GROUP BY geoip_country_code";
    if ($ipTV_db_admin->query($rQuery)) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $WorldMapLive = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
            echo $WorldMapLive;
        }
    }
}

function getSelections($rSources) {
    global $ipTV_db_admin;
    $return = array();
    foreach ($rSources as $rSource) {
        $ipTV_db_admin->query("SELECT `id` FROM `streams` WHERE `type` IN (2,5) AND `stream_source` LIKE '%" . str_replace("/", "\/", $rSource) . "\"%' ESCAPE '|' LIMIT 1;");
        if ($ipTV_db_admin->num_rows() == 1) {
            $return[] = intval($ipTV_db_admin->get_row()["id"]);
        }
    }
    return $return;
}

function getWorldMapActivity() {
    global $ipTV_db_admin;
    $rQuery = "SELECT DISTINCT geoip_country_code, COUNT(DISTINCT user_id) AS total FROM user_activity GROUP BY geoip_country_code";
    if ($ipTV_db_admin->query($rQuery)) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $WorldMapActivity = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
            echo $WorldMapActivity;
        }
    }
}

function getNextOrder() {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT MAX(`order`) AS `order` FROM `streams`;");
    if ($ipTV_db_admin->num_rows() == 1) {
        return intval($ipTV_db_admin->get_row()["order"]) + 1;
    }
    return 0;
}

function generateSeriesPlaylist($rSeriesNo) {
    global $ipTV_db_admin, $rServers, $rSettings;
    $rReturn = array("success" => false, "sources" => array(), "server_id" => 0);
    $ipTV_db_admin->query("SELECT `stream_id` FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesNo) . " ORDER BY `season_num` ASC, `sort` ASC;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $ipTV_db_admin->query("SELECT `stream_source` FROM `streams` WHERE `id` = " . intval($row["stream_id"]) . ";");
            if ($ipTV_db_admin->num_rows() > 0) {
                $rSource = json_decode($ipTV_db_admin->get_row()["stream_source"], true)[0];
                $rSplit = explode(":", $rSource);
                $rFilename = join(":", array_slice($rSplit, 2, count($rSplit) - 2));
                $rServerID = intval($rSplit[1]);
                if ($rReturn["server_id"] == 0) {
                    $rReturn["server_id"] = $rServerID;
                    $rReturn["success"] = true;
                }
                if ($rReturn["server_id"] <> $rServerID) {
                    $rReturn["success"] = false;
                    break;
                }
                $rReturn["sources"][] = $rFilename;
            }
        }
    }
    return $rReturn;
}

/**
 * Flushes blocked IP addresses and sends a flush signal to all servers and proxy servers.
 *
 * @global object $ipTV_db_admin Database connection object for executing queries.
 * @global array $rServers Array containing server information.
 * @global array $rProxyServers Array containing proxy server information.
 *
 * @return bool Returns `true` after successfully flushing the IPs.
 */
function flushIPs() {
    global $ipTV_db_admin;
    global $rServers;
    global $rProxyServers;
    $ipTV_db_admin->query('TRUNCATE `blocked_ips`;');
    shell_exec('rm ' . FLOOD_TMP_PATH . 'block_*');

    foreach ($rServers as $rServer) {
        $ipTV_db_admin->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServer['id'], time(), json_encode(array('action' => 'flush')));
    }

    foreach ($rProxyServers as $rServer) {
        $ipTV_db_admin->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServer['id'], time(), json_encode(array('action' => 'flush')));
    }

    return true;
}

function flushLogins() {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("DELETE FROM `login_flood`;");
}

function updateTMDbCategories() {
    global $ipTV_db_admin, $rSettings;
    include INCLUDES_PATH . 'libs/tmdb.php';
    if (strlen($rSettings["tmdb_language"]) > 0) {
        $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rSettings["tmdb_language"]);
    } else {
        $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
    }
    $rCurrentCats = array(1 => array(), 2 => array());
    $ipTV_db_admin->query("SELECT `id`, `type`, `genre_id` FROM `watch_categories`;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if (in_array($row["genre_id"], $rCurrentCats[$row["type"]])) {
                $ipTV_db_admin->query("DELETE FROM `watch_categories` WHERE `id` = " . intval($row["id"]) . ";");
            }
            $rCurrentCats[$row["type"]][] = $row["genre_id"];
        }
    }
    $rMovieGenres = $rTMDB->getMovieGenres();
    foreach ($rMovieGenres as $rMovieGenre) {
        if (!in_array($rMovieGenre->getID(), $rCurrentCats[1])) {
            $ipTV_db_admin->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(1, " . intval($rMovieGenre->getID()) . ", '" . $rMovieGenre->getName() . "', 0, '[]');");
        }
        if (!in_array($rMovieGenre->getID(), $rCurrentCats[2])) {
            $ipTV_db_admin->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(2, " . intval($rMovieGenre->getID()) . ", '" . $rMovieGenre->getName() . "', 0, '[]');");
        }
    }
    $rTVGenres = $rTMDB->getTVGenres();
    foreach ($rTVGenres as $rTVGenre) {
        if (!in_array($rTVGenre->getID(), $rCurrentCats[1])) {
            $ipTV_db_admin->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(1, " . intval($rTVGenre->getID()) . ", '" . $rTVGenre->getName() . "', 0, '[]');");
        }
        if (!in_array($rTVGenre->getID(), $rCurrentCats[2])) {
            $ipTV_db_admin->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(2, " . intval($rTVGenre->getID()) . ", '" . $rTVGenre->getName() . "', 0, '[]');");
        }
    }
}

/**
 * Fetches the latest release and pre-release information from a GitHub repository
 *
 * @param string $repo The repository name in format "owner/repository"
 *
 * @return array{
 *     latest_release?: string|null,
 *     latest_prerelease?: string|null,
 *     error?: string
 * } Returns an array containing:
 *           - latest_release: The tag name of the latest stable release (null if none found)
 *           - latest_prerelease: The tag name of the latest pre-release (null if none found)
 *           - error: Error message if the request fails
 *
 * @throws Exception When the GitHub API request fails or returns invalid data
 */
function getGithubReleases(string $repo): array {
    $url = "https://api.github.com/repos/$repo/releases";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP-Request'
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return ['error' => 'Request error: ' . curl_error($ch)];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => "GitHub API returned HTTP code $httpCode"];
    }

    $releases = json_decode($response, true);
    if (empty($releases)) {
        return ['error' => 'No releases found'];
    }

    $latestRelease = null;
    $latestPrerelease = null;

    foreach ($releases as $release) {
        if (!$release['prerelease'] && !$latestRelease) {
            $latestRelease = $release['tag_name'];
        }
        if ($release['prerelease'] && !$latestPrerelease) {
            $latestPrerelease = $release['tag_name'];
        }

        if ($latestRelease && $latestPrerelease) {
            break;
        }
    }

    return [
        'latest_release' => $latestRelease,
        'latest_prerelease' => $latestPrerelease
    ];
}


/**
 * Retrieves the most recent stable release version from a given URL.
 *
 * This function sends a HEAD request to the provided URL, follows any redirects,
 * and attempts to extract the version number from the final URL's basename.
 * It assumes the version is the basename of the URL, minus the first character.
 *
 * @param string $url The URL to check for the latest stable release.
 *
 * @return string|false The extracted version number as a string, or false on failure.
 *                      The returned version string does not include the first character
 *                      of the basename (typically removing a 'v' prefix if present).
 *
 * @throws Exception If there's an issue with the cURL request or version extraction.
 *                   The exception message will be logged using error_log().
 *
 */
function get_recent_stable_release(string $url) {
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    // Execute cURL request
    $result = curl_exec($ch);

    if ($result === false) {
        error_log("cURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Get the effective URL after following redirects
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    // Close cURL session
    curl_close($ch);

    // Extract the version from the URL
    $version = basename($effective_url);

    if (empty($version)) {
        error_log("Error: Could not extract version from URL");
        return false;
    }

    return $version;
}


////// NOT CHECKED ////////

function sortArrayByArray(array $rArray, array $rSort) {
    $rOrdered = array();
    foreach ($rSort as $rValue) {
        if (($rKey = array_search($rValue, $rArray)) !== false) {
            $rOrdered[] = $rValue;
            unset($rArray[$rKey]);
        }
    }
    return $rOrdered + $rArray;
}

function updateGeoLite2() {
    $rGeoLite2Latest = get_recent_stable_release("https://github.com/Vateron-Media/Xtream_Update/releases/latest");
    $rGeoLite2Curent = json_decode(file_get_contents("/home/xc_vm/bin/maxmind/version.json"), true)["geolite2_version"];
    if ($rGeoLite2Latest == $rGeoLite2Curent) {
        return true;
    }
    if ($rGeoLite2Latest) {
        $fileNames = ["GeoLite2-City.mmdb", "GeoLite2-Country.mmdb", "GeoLite2-ASN.mmdb"];
        $checker = [false, false, false];
        foreach ($fileNames as $key => $value) {
            $rFileData = file_get_contents("https://github.com/Vateron-Media/Xtream_Update/releases/download/{$rGeoLite2Latest}/{$value}");
            if (stripos($rFileData, "MaxMind.com") !== false) {
                $rFilePath = "/home/xc_vm/bin/maxmind/{$value}";
                exec("sudo chattr -i {$rFilePath}");
                unlink($rFilePath);
                file_put_contents($rFilePath, $rFileData);
                exec("sudo chmod 644 {$rFilePath}");
                exec("sudo chattr +i {$rFilePath}");
                if (file_get_contents($rFilePath) == $rFileData) {
                    $checker[$key] = true;
                }
            }
        }
        if ($checker[0] && $checker[1] && $checker[2]) {
            # create json version file and write version geolite
            $versionFile = "/home/xc_vm/bin/maxmind/version.json";
            $data = ["geolite2_version" => $rGeoLite2Latest];
            $json = json_encode($data);
            unlink($versionFile);
            file_put_contents($versionFile, $json);
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function APIRequest($rData, $rTimeout = 5) {
    ini_set('default_socket_timeout', $rTimeout);
    $rAPI = 'http://127.0.0.1:' . intval(ipTV_lib::$Servers[SERVER_ID]['http_broadcast_port']) . '/admin/api';

    if (!empty(ipTV_lib::$settings['api_pass'])) {
        $rData['api_pass'] = ipTV_lib::$settings['api_pass'];
    }

    $rPost = http_build_query($rData);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rAPI);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rPost);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $rTimeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $rTimeout);

    return curl_exec($ch);
}

function SystemAPIRequest($rServerID, $rData) {
    global $rServers, $rSettings;
    ini_set('default_socket_timeout', 5);
    $rAPI = "http://" . $rServers[intval($rServerID)]["server_ip"] . ":" . $rServers[intval($rServerID)]["http_broadcast_port"] . "/api.php";
    $rData["password"] = $rSettings["live_streaming_pass"];
    $rPost = http_build_query($rData);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rAPI);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rPost);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $rData = curl_exec($ch);
    return $rData;
}

/**
 * Retrieves a list of running processes (PIDs) from a remote server.
 *
 * @param int $rServerID The ID of the server to retrieve the process list from.
 *
 * @return array An array of processes, each containing:
 *               - `user` (string): The user who owns the process.
 *               - `pid` (int): The process ID.
 *               - `cpu` (float): CPU usage percentage.
 *               - `mem` (float): Memory usage percentage.
 *               - `vsz` (int): Virtual memory size.
 *               - `rss` (int): Resident set size (physical memory used).
 *               - `tty` (string): Terminal associated with the process.
 *               - `stat` (string): Process state.
 *               - `start` (string): Start time of the process.
 *               - `time` (string): Total CPU time used.
 *               - `command` (string): Command that started the process.
 */
function getPIDs($rServerID) {
    $rReturn = array();
    $rProcesses = json_decode(SystemAPIRequest($rServerID, array('action' => 'get_pids')), true);
    array_shift($rProcesses);
    foreach ($rProcesses as $rProcess) {
        $rSplit = explode(" ", preg_replace('!\s+!', ' ', trim($rProcess)));
        if (strlen($rSplit[0]) > 0) {
            $rReturn[] = array("user" => $rSplit[0], "pid" => $rSplit[1], "cpu" => $rSplit[2], "mem" => $rSplit[3], "vsz" => $rSplit[4], "rss" => $rSplit[5], "tty" => $rSplit[6], "stat" => $rSplit[7], "start" => $rSplit[8], "time" => $rSplit[9], "command" => join(" ", array_splice($rSplit, 10, count($rSplit) - 10)));
        }
    }
    return $rReturn;
}

/**
 * Retrieves free disk space information from a remote server.
 *
 * @param int $rServerID The ID of the server to check for free space.
 *
 * @return array An array of disk space details, where each entry contains:
 *               - `filesystem` (string): The name of the filesystem.
 *               - `size` (string): The total size of the filesystem.
 *               - `used` (string): The amount of space used.
 *               - `avail` (string): The amount of available space.
 *               - `percentage` (string): The percentage of space used.
 *               - `mount` (string): The mount point of the filesystem.
 */
function getFreeSpace($rServerID) {
    $rReturn = array();
    $rLines = json_decode(SystemAPIRequest($rServerID, array('action' => 'get_free_space')), true);
    array_shift($rLines);

    foreach ($rLines as $rLine) {
        $rSplit = explode(' ', preg_replace('!\\s+!', ' ', trim($rLine)));
        if (0 < strlen($rSplit[0]) && strpos($rSplit[5], 'xc_vm') !== false || $rSplit[5] == '/') {
            $rReturn[] = array('filesystem' => $rSplit[0], 'size' => $rSplit[1], 'used' => $rSplit[2], 'avail' => $rSplit[3], 'percentage' => $rSplit[4], 'mount' => implode(' ', array_slice($rSplit, 5, count($rSplit) - 5)));
        }
    }

    return $rReturn;
}

function freeTemp($rServerID) {
    SystemAPIRequest($rServerID, array('action' => 'free_temp'));
}

function freeStreams($rServerID) {
    SystemAPIRequest($rServerID, array('action' => 'free_streams'));
}


function getStreamPIDs($rServerID) {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `streams`.`type`, `streams_servers`.`pid`, `streams_servers`.`monitor_pid`, `streams_servers`.`delay_pid` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `streams_servers`.`server_id` = " . intval($rServerID) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            foreach (array("pid", "monitor_pid", "delay_pid") as $rPIDType) {
                if ($row[$rPIDType]) {
                    $return[$row[$rPIDType]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => $rPIDType);
                }
            }
        }
    }
    $ipTV_db_admin->query("SELECT `id`, `stream_display_name`, `type`, `tv_archive_pid` FROM `streams` WHERE `tv_archive_server_id` = " . intval($rServerID) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if ($row["pid"]) {
                $return[$row["pid"]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => "timeshift");
            }
        }
    }
    $ipTV_db_admin->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `streams`.`type`, `lines_live`.`pid` FROM `lines_live` LEFT JOIN `streams` ON `streams`.`id` = `lines_live`.`stream_id` WHERE `lines_live`.`server_id` = " . intval($rServerID) . ";");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if ($row["pid"]) {
                $return[$row["pid"]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => "activity");
            }
        }
    }
    return $return;
}

function roundUpToAny($n, $x = 5) {
    return round(($n + $x / 2) / $x) * $x;
}

function checkSource($rServerID, $rFilename) {
    global $rServers, $rSettings;
    $rAPI = "http://" . $rServers[intval($rServerID)]["server_ip"] . ":" . $rServers[intval($rServerID)]["http_broadcast_port"] . "/api.php?password=" . $rSettings["live_streaming_pass"] . "&action=getFile&filename=" . urlencode(escapeshellcmd($rFilename));
    $rCommand = 'timeout 5 ' . MAIN_DIR . 'bin/ffprobe -show_streams -v quiet "' . $rAPI . '" -of json';
    return json_decode(shell_exec($rCommand), true);
}

function tmdbParseRelease($Release) {
    $rCommand = "/usr/bin/python " . INCLUDES_PATH . "python/release.py \"" . escapeshellcmd($Release) . "\"";
    return json_decode(shell_exec($rCommand), true);
}

function listDir($rServerID, $rDirectory, $rAllowed = null) {
    global $rServers, $_INFO, $rSettings;
    set_time_limit(60);
    ini_set('max_execution_time', 60);
    $rReturn = array("dirs" => array(), "files" => array());
    if ($rServerID == $_INFO["server_id"]) {
        $rFiles = scanDir($rDirectory);
        foreach ($rFiles as $rKey => $rValue) {
            if (!in_array($rValue, array(".", ".."))) {
                if (is_dir($rDirectory . "/" . $rValue)) {
                    $rReturn["dirs"][] = $rValue;
                } else {
                    $rExt = strtolower(pathinfo($rValue)["extension"]);
                    if (((is_array($rAllowed)) && (in_array($rExt, $rAllowed))) or (!$rAllowed)) {
                        $rReturn["files"][] = $rValue;
                    }
                }
            }
        }
    } else {
        $rData = SystemAPIRequest($rServerID, array('action' => 'viewDir', 'dir' => $rDirectory));
        $rDocument = new DOMDocument();
        $rDocument->loadHTML($rData);
        $rFiles = $rDocument->getElementsByTagName('li');
        foreach ($rFiles as $rFile) {
            if (stripos($rFile->getAttribute('class'), "directory") !== false) {
                $rReturn["dirs"][] = $rFile->nodeValue;
            } elseif (stripos($rFile->getAttribute('class'), "file") !== false) {
                $rExt = strtolower(pathinfo($rFile->nodeValue)["extension"]);
                if (((is_array($rAllowed)) && (in_array($rExt, $rAllowed))) or (!$rAllowed)) {
                    $rReturn["files"][] = $rFile->nodeValue;
                }
            }
        }
    }
    return $rReturn;
}

function scanRecursive($rServerID, $rDirectory, $rAllowed = null) {
    $result = [];
    $rFiles = listDir($rServerID, $rDirectory, $rAllowed);
    foreach ($rFiles["files"] as $rFile) {
        $rFilePath = rtrim($rDirectory, "/") . '/' . $rFile;
        $result[] = $rFilePath;
    }
    foreach ($rFiles["dirs"] as $rDir) {
        foreach (scanRecursive($rServerID, rtrim($rDirectory, "/") . "/" . $rDir . "/", $rAllowed) as $rFile) {
            $result[] = $rFile;
        }
    }
    return $result;
}

function getEncodeErrors($rID) {
    $rServers = getStreamingServers(true);
    ini_set('default_socket_timeout', 3);
    $rErrors = array();
    $rStreamSys = getStreamSys($rID);
    foreach ($rStreamSys as $rServer) {
        $rServerID = $rServer["server_id"];
        if (isset($rServers[$rServerID])) {
            if (!($rServer["pid"] > 0 && $rServer["to_analyze"] == 0 && $rServer["stream_status"] <> 1)) {
                $rFilename = CONTENT_PATH . "vod/" . intval($rID) . ".errors";
                $rError = SystemAPIRequest($rServerID, array('action' => 'getFile', 'filename' => $rFilename));
                if (strlen($rError) > 0) {
                    $rErrors[$rServerID] = $rError;
                }
            }
        }
    }
    return $rErrors;
}

/**
 * Queue deletion signals for a VOD file across specified streaming servers
 * 
 * Inserts asynchronous deletion tasks into the signals table for external processing.
 * Handles both single server ID and server ID arrays for batch operations.
 *
 * @param int|int[] $rServerIDs Single server ID or array of server IDs to notify
 * @param int $rID Database ID of the VOD file to be removed
 * @return bool Always returns true (does not verify database insertion success)
 *
 */
function deleteMovieFile($rServerIDs, $rID) {
    global $ipTV_db_admin;
    if (!is_array($rServerIDs)) {
        $rServerIDs = array($rServerIDs);
    }
    foreach ($rServerIDs as $rServerID) {
        $ipTV_db_admin->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`, `cache`) VALUES(?, ?, ?, 1);', $rServerID, time(), json_encode(array('type' => 'delete_vod', 'id' => $rID)));
    }
    return true;
}
function getSeriesTrailer($rTMDBID) {
    // Not implemented in TMDB PHP API...
    global $rSettings;
    if (strlen($rSettings["tmdb_language"]) > 0) {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/videos?api_key=" . $rSettings["tmdb_api_key"] . "&language=" . $rSettings["tmdb_language"];
    } else {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/videos?api_key=" . $rSettings["tmdb_api_key"];
    }
    $rJSON = json_decode(file_get_contents($rURL), true);
    foreach ($rJSON["results"] as $rVideo) {
        if ((strtolower($rVideo["type"]) == "trailer") && (strtolower($rVideo["site"]) == "youtube")) {
            return $rVideo["key"];
        }
    }
    return "";
}

function getStills($rTMDBID, $rSeason, $rEpisode) {
    // Not implemented in TMDB PHP API...
    global $rSettings;
    if (strlen($rSettings["tmdb_language"]) > 0) {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/season/" . $rSeason . "/episode/" . $rEpisode . "/images?api_key=" . $rSettings["tmdb_api_key"] . "&language=" . $rSettings["tmdb_language"];
    } else {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/season/" . $rSeason . "/episode/" . $rEpisode . "/images?api_key=" . $rSettings["tmdb_api_key"];
    }
    return json_decode(file_get_contents($rURL), true);
}

// LEAKED LINES : For Show Restreamers, remove AND is_restreamer <1
function getLeakedLines() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT FROM_BASE64(mac), username, user_activity.user_id, user_activity.container, user_activity.geoip_country_code, GROUP_CONCAT(DISTINCT user_ip), GROUP_CONCAT(DISTINCT container), GROUP_CONCAT(DISTINCT geoip_country_code), is_restreamer FROM user_activity
INNER JOIN lines ON user_id = lines.id AND is_mag = 1
INNER JOIN mag_devices ON lines.id = mag_devices.user_id
WHERE 1 GROUP BY user_id HAVING COUNT(DISTINCT user_ip) > 1
AND
is_restreamer < 1
UNION
SELECT '', username, user_activity.user_id, user_activity.container, user_activity.geoip_country_code, GROUP_CONCAT(DISTINCT user_ip), GROUP_CONCAT(DISTINCT container), GROUP_CONCAT(DISTINCT geoip_country_code), is_restreamer FROM user_activity
INNER JOIN lines ON user_id = lines.id AND is_mag = 0
WHERE 1 GROUP BY user_id HAVING COUNT(DISTINCT user_ip) > 1
AND
is_restreamer < 1;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}

// SECURITY CENTER
function getSecurityCenter() {
    global $ipTV_db_admin;
    $return = array();
    $ipTV_db_admin->query("SELECT Distinct lines.id, lines.username, SUBSTR(`streams`.`stream_display_name`, 1, 30) stream_display_name, lines.max_connections, (SELECT count(*) FROM `lines_live` WHERE `lines_live`.`stream_id` = `streams`.`id`) AS `active_connections`, (SELECT count(*) FROM `lines_live` WHERE `lines`.`id` = `lines_live`.`user_id`) AS `total_active_connections` FROM lines_live
INNER JOIN `streams` ON `lines_live`.`stream_id` = `streams`.`id`
LEFT JOIN lines ON user_id = lines.id WHERE (SELECT count(*) FROM `lines_live` WHERE `lines`.`id` = `lines_live`.`user_id`) > `lines`.`max_connections`
AND
is_restreamer < 1;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            $return[] = $row;
        }
    }
    return $return;
}
//############



function downloadImage($rImage) {
    if ((strlen($rImage) > 0) && (substr(strtolower($rImage), 0, 4) == "http")) {
        $rPathInfo = pathinfo($rImage);
        $rExt = $rPathInfo["extension"];
        if (in_array(strtolower($rExt), array("jpg", "jpeg", "png"))) {
            $rPrevPath = MAIN_DIR . "wwwdir/images/" . $rPathInfo["filename"] . "." . $rExt;
            if (file_exists($rPrevPath)) {
                return getURL() . "/images/" . $rPathInfo["filename"] . "." . $rExt;
            } else {
                $rCont = stream_context_create(array('http' => array('timeout' => 10, 'method' => "GET")));
                $rData = file_get_contents($rImage, false, $rCont);
                if (strlen($rData) > 0) {
                    $rFilename = md5($rPathInfo["filename"]);
                    $rPath = MAIN_DIR . "wwwdir/images/" . $rFilename . "." . $rExt;
                    file_put_contents($rPath, $rData);
                    if (strlen(file_get_contents($rPath)) == strlen($rData)) {
                        return getURL() . "/images/" . $rFilename . "." . $rExt;
                    }
                }
            }
        }
    }
    return $rImage;
}

function updateSeries($rID) {
    global $ipTV_db_admin, $rSettings;
    require_once INCLUDES_PATH . 'libs/tmdb.php';
    $ipTV_db_admin->query("SELECT `tmdb_id` FROM `series` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        $rTMDBID = $ipTV_db_admin->get_row()["tmdb_id"];
        if (strlen($rTMDBID) > 0) {
            if (strlen($rSettings["tmdb_language"]) > 0) {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rSettings["tmdb_language"]);
            } else {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
            }
            $rReturn = array();
            $rSeasons = json_decode($rTMDB->getTVShow($rTMDBID)->getJSON(), true)["seasons"];
            foreach ($rSeasons as $rSeason) {
                if ($rSettings["download_images"]) {
                    $rSeason["cover"] = downloadImage("https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rSeason["poster_path"]);
                } else {
                    $rSeason["cover"] = "https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rSeason["poster_path"];
                }
                $rSeason["cover_big"] = $rSeason["cover"];
                unset($rSeason["poster_path"]);
                $rReturn[] = $rSeason;
            }
            $ipTV_db_admin->query("UPDATE `series` SET `seasons` = '" . json_encode($rReturn) . "', `last_modified` = " . intval(time()) . " WHERE `id` = " . intval($rID) . ";");
        }
    }
}

function getURL() {
    global $rServers, $_INFO;
    if (strlen($rServers[$_INFO["server_id"]]["domain_name"]) > 0) {
        return "http://" . $rServers[$_INFO["server_id"]]["domain_name"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
    } elseif (strlen($rServers[$_INFO["server_id"]]["private_ip"]) > 0) {
        return "http://" . $rServers[$_INFO["server_id"]]["private_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
    } else {
        return "http://" . $rServers[$_INFO["server_id"]]["server_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
    }
}

function clearSettingsCache() {
    unlink(CACHE_TMP_PATH . 'settings');
}

/**
 * Deletes a blocked IP entry from database and associated flood control file
 * 
 * Performs atomic removal of IP blocking record and its corresponding temp file.
 * First verifies existence before deletion to prevent errors.
 *
 * @param int $rID Database ID of the blocked IP record to remove
 * @return bool True if deletion succeeded, false if record not found
 *
 */
function rdeleteBlockedIP($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query('SELECT `id`, `ip` FROM `blocked_ips` WHERE `id` = ?;', $rID);

    if (0 >= $ipTV_db_admin->num_rows()) {
        return false;
    }

    $rRow = $ipTV_db_admin->get_row();
    $ipTV_db_admin->query('DELETE FROM `blocked_ips` WHERE `id` = ?;', $rID);

    if (file_exists(FLOOD_TMP_PATH . 'block_' . $rRow['ip'])) {
        unlink(FLOOD_TMP_PATH . 'block_' . $rRow['ip']);
    }

    return true;
}
