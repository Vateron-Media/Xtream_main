<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('STATUS_FAILURE', 0);
define('STATUS_SUCCESS', 1);

$_INFO = array();
$rTimeout = 60;             // Seconds Timeout for Functions & Requests
$rSQLTimeout = 5;           // Max execution time for MySQL queries.

require_once '/home/xtreamcodes/wwwdir/constants.php';
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

$ipTV_db_admin = new ipTV_db($_INFO['username'], $_INFO['password'], $_INFO['database'], $_INFO['hostname'], $_INFO['port'], empty($_INFO['pconnect']) ? false : true, false);

if (!$db = new mysqli($_INFO["hostname"], $_INFO["username"], $_INFO["password"], $_INFO["database"], $_INFO["port"])) {
    exit("No MySQL connection!");
}
$db->set_charset("utf8");
$db->query("SET GLOBAL MAX_EXECUTION_TIME=" . ($rSQLTimeout * 1000) . ";");


ipTV_lib::$ipTV_db = &$ipTV_db_admin;
// ipTV_streaming::$ipTV_db = &$ipTV_db_admin;
// ipTV_stream::$ipTV_db = &$ipTV_db_admin;
API::$ipTV_db = &$ipTV_db_admin;
API::init();
ipTV_lib::init();
ipTV_lib::connectRedis();

$rProtocol = getProtocol();
$rSettings = ipTV_lib::$settings;
$detect = new Mobile_Detect;

date_default_timezone_set($rSettings['default_timezone']);

set_time_limit($rTimeout);
ini_set('mysql.connect_timeout', $rSQLTimeout);
ini_set('max_execution_time', $rTimeout);
ini_set('default_socket_timeout', $rTimeout);

$rGeoCountries = array('ALL' => 'All Countries', 'A1' => 'Anonymous Proxy', 'A2' => 'Satellite Provider', 'O1' => 'Other Country', 'AF' => 'Afghanistan', 'AX' => 'Aland Islands', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua And Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia And Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo', 'CD' => 'Congo, Democratic Republic', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'CI' => "Cote D'Ivoire", 'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'MK' => 'Fyrom', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island & Mcdonald Islands', 'VA' => 'Holy See (Vatican City State)', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran, Islamic Republic Of', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle Of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KR' => 'Korea', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => "Lao People's Democratic Republic", 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libyan Arab Jamahiriya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia, Federated States Of', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territory, Occupied', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russian Federation', 'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts And Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre And Miquelon', 'VC' => 'Saint Vincent And Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome And Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia And Sandwich Isl.', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard And Jan Mayen', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syrian Arab Republic', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad And Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks And Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Outlying Islands', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' => 'Viet Nam', 'VG' => 'Virgin Islands, British', 'VI' => 'Virgin Islands, U.S.', 'WF' => 'Wallis And Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');
$rClientFilters = array('LB_TOKEN_INVALID' => 'Token Failure', 'NOT_IN_BOUQUET' => 'Not in Bouquet', 'BLOCKED_ASN' => 'Blocked ASN', 'ISP_LOCK_FAILED' => 'ISP Lock Failed', 'USER_DISALLOW_EXT' => 'Extension Disallowed', 'AUTH_FAILED' => 'Authentication Failed', 'USER_EXPIRED' => 'User Expired', 'USER_DISABLED' => 'User Disabled', 'USER_BAN' => 'User Banned', 'MAG_TOKEN_INVALID' => 'MAG Token Invalid', 'STALKER_CHANNEL_MISMATCH' => 'Stalker Channel Mismatch', 'STALKER_IP_MISMATCH' => 'Stalker IP Mismatch', 'STALKER_KEY_EXPIRED' => 'Stalker Key Expired', 'STALKER_DECRYPT_FAILED' => 'Stalker Decrypt Failed', 'EMPTY_UA' => 'Empty User-Agent', 'IP_BAN' => 'IP Banned', 'COUNTRY_DISALLOW' => 'Country Disallowed', 'USER_AGENT_BAN' => 'User-Agent Disallowed', 'USER_ALREADY_CONNECTED' => 'IP Limit Reached', 'RESTREAM_DETECT' => 'Restream Detected', 'PROXY_DETECT' => 'Proxy / VPN Detected', 'HOSTING_DETECT' => 'Hosting Server Detected', 'LINE_CREATE_FAIL' => 'Connection Failed', 'CONNECTION_LOOP' => 'Connection Loop', 'TOKEN_EXPIRED' => 'Token Expired', 'IP_MISMATCH' => 'IP Mismatch');
$rTMDBLanguages = array("" => "Default - EN", "aa" => "Afar", "af" => "Afrikaans", "ak" => "Akan", "an" => "Aragonese", "as" => "Assamese", "av" => "Avaric", "ae" => "Avestan", "ay" => "Aymara", "az" => "Azerbaijani", "ba" => "Bashkir", "bm" => "Bambara", "bi" => "Bislama", "bo" => "Tibetan", "br" => "Breton", "ca" => "Catalan", "cs" => "Czech", "ce" => "Chechen", "cu" => "Slavic", "cv" => "Chuvash", "kw" => "Cornish", "co" => "Corsican", "cr" => "Cree", "cy" => "Welsh", "da" => "Danish", "de" => "German", "dv" => "Divehi", "dz" => "Dzongkha", "eo" => "Esperanto", "et" => "Estonian", "eu" => "Basque", "fo" => "Faroese", "fj" => "Fijian", "fi" => "Finnish", "fr" => "French", "fy" => "Frisian", "ff" => "Fulah", "gd" => "Gaelic", "ga" => "Irish", "gl" => "Galician", "gv" => "Manx", "gn" => "Guarani", "gu" => "Gujarati", "ht" => "Haitian", "ha" => "Hausa", "sh" => "Serbo-Croatian", "hz" => "Herero", "ho" => "Hiri Motu", "hr" => "Croatian", "hu" => "Hungarian", "ig" => "Igbo", "io" => "Ido", "ii" => "Yi", "iu" => "Inuktitut", "ie" => "Interlingue", "ia" => "Interlingua", "id" => "Indonesian", "ik" => "Inupiaq", "is" => "Icelandic", "it" => "Italian", "ja" => "Japanese", "kl" => "Kalaallisut", "kn" => "Kannada", "ks" => "Kashmiri", "kr" => "Kanuri", "kk" => "Kazakh", "km" => "Khmer", "ki" => "Kikuyu", "rw" => "Kinyarwanda", "ky" => "Kirghiz", "kv" => "Komi", "kg" => "Kongo", "ko" => "Korean", "kj" => "Kuanyama", "ku" => "Kurdish", "lo" => "Lao", "la" => "Latin", "lv" => "Latvian", "li" => "Limburgish", "ln" => "Lingala", "lt" => "Lithuanian", "lb" => "Letzeburgesch", "lu" => "Luba-Katanga", "lg" => "Ganda", "mh" => "Marshall", "ml" => "Malayalam", "mr" => "Marathi", "mg" => "Malagasy", "mt" => "Maltese", "mo" => "Moldavian", "mn" => "Mongolian", "mi" => "Maori", "ms" => "Malay", "my" => "Burmese", "na" => "Nauru", "nv" => "Navajo", "nr" => "Ndebele", "nd" => "Ndebele", "ng" => "Ndonga", "ne" => "Nepali", "nl" => "Dutch", "nn" => "Norwegian Nynorsk", "nb" => "Norwegian Bokmal", "no" => "Norwegian", "ny" => "Chichewa", "oc" => "Occitan", "oj" => "Ojibwa", "or" => "Oriya", "om" => "Oromo", "os" => "Ossetian; Ossetic", "pi" => "Pali", "pl" => "Polish", "pt" => "Portuguese", "pt-BR" => "Portuguese - Brazil", "qu" => "Quechua", "rm" => "Raeto-Romance", "ro" => "Romanian", "rn" => "Rundi", "ru" => "Russian", "sg" => "Sango", "sa" => "Sanskrit", "si" => "Sinhalese", "sk" => "Slovak", "sl" => "Slovenian", "se" => "Northern Sami", "sm" => "Samoan", "sn" => "Shona", "sd" => "Sindhi", "so" => "Somali", "st" => "Sotho", "es" => "Spanish", "sq" => "Albanian", "sc" => "Sardinian", "sr" => "Serbian", "ss" => "Swati", "su" => "Sundanese", "sw" => "Swahili", "sv" => "Swedish", "ty" => "Tahitian", "ta" => "Tamil", "tt" => "Tatar", "te" => "Telugu", "tg" => "Tajik", "tl" => "Tagalog", "th" => "Thai", "ti" => "Tigrinya", "to" => "Tonga", "tn" => "Tswana", "ts" => "Tsonga", "tk" => "Turkmen", "tr" => "Turkish", "tw" => "Twi", "ug" => "Uighur", "uk" => "Ukrainian", "ur" => "Urdu", "uz" => "Uzbek", "ve" => "Venda", "vi" => "Vietnamese", "vo" => "Volapük", "wa" => "Walloon", "wo" => "Wolof", "xh" => "Xhosa", "yi" => "Yiddish", "za" => "Zhuang", "zu" => "Zulu", "ab" => "Abkhazian", "zh" => "Mandarin", "ps" => "Pushto", "am" => "Amharic", "ar" => "Arabic", "bg" => "Bulgarian", "cn" => "Cantonese", "mk" => "Macedonian", "el" => "Greek", "fa" => "Persian", "he" => "Hebrew", "hi" => "Hindi", "hy" => "Armenian", "en" => "English", "ee" => "Ewe", "ka" => "Georgian", "pa" => "Punjabi", "bn" => "Bengali", "bs" => "Bosnian", "ch" => "Chamorro", "be" => "Belarusian", "yo" => "Yoruba");
$rMAGs = array("MAG200", "MAG245", "MAG245D", "MAG250", "MAG254", "MAG255", "MAG256", "MAG257", "MAG260", "MAG270", "MAG275", "MAG322", "MAG322w1", "MAG322w2", "MAG323", "MAG324", "MAG324C", "MAG324w2", "MAG325", "MAG349", "MAG350", "MAG351", "MAG352", "MAG420", "MAG420w1", "MAG420w2", "MAG422", "MAG422A", "MAG422Aw1", "MAG424", "MAG424w1", "MAG424w2", "MAG424w3", "MAG424A", "MAG424Aw3", "MAG425", "MAG425A", "AuraHD", "AuraHD0", "AuraHD1", "AuraHD2", "AuraHD3", "AuraHD4", "AuraHD5", "AuraHD6", "AuraHD7", "AuraHD8", "AuraHD9", "WR320", "IM2100", "IM2100w1", "IM2100V", "IM2100VI", "IM2101", "IM2101V", "IM2101VI", "IM2101VO", "IM2101w2", "IM2102", "IM4410", "IM4410w3", "IM4411", "IM4411w1", "IM4412", "IM4414", "IM4414w1", "IP_STB_HD");
$rTimeZones = array("Africa/Abidjan" => "Africa/Abidjan [GMT  00:00]", "Africa/Accra" => "Africa/Accra [GMT  00:00]", "Africa/Addis_Ababa" => "Africa/Addis_Ababa [EAT +03:00]", "Africa/Algiers" => "Africa/Algiers [CET +01:00]", "Africa/Asmara" => "Africa/Asmara [EAT +03:00]", "Africa/Bamako" => "Africa/Bamako [GMT  00:00]", "Africa/Bangui" => "Africa/Bangui [WAT +01:00]", "Africa/Banjul" => "Africa/Banjul [GMT  00:00]", "Africa/Bissau" => "Africa/Bissau [GMT  00:00]", "Africa/Blantyre" => "Africa/Blantyre [CAT +02:00]", "Africa/Brazzaville" => "Africa/Brazzaville [WAT +01:00]", "Africa/Bujumbura" => "Africa/Bujumbura [CAT +02:00]", "Africa/Cairo" => "Africa/Cairo [EET +02:00]", "Africa/Casablanca" => "Africa/Casablanca [WEST +01:00]", "Africa/Ceuta" => "Africa/Ceuta [CEST +02:00]", "Africa/Conakry" => "Africa/Conakry [GMT  00:00]", "Africa/Dakar" => "Africa/Dakar [GMT  00:00]", "Africa/Dar_es_Salaam" => "Africa/Dar_es_Salaam [EAT +03:00]", "Africa/Djibouti" => "Africa/Djibouti [EAT +03:00]", "Africa/Douala" => "Africa/Douala [WAT +01:00]", "Africa/El_Aaiun" => "Africa/El_Aaiun [WEST +01:00]", "Africa/Freetown" => "Africa/Freetown [GMT  00:00]", "Africa/Gaborone" => "Africa/Gaborone [CAT +02:00]", "Africa/Harare" => "Africa/Harare [CAT +02:00]", "Africa/Johannesburg" => "Africa/Johannesburg [SAST +02:00]", "Africa/Juba" => "Africa/Juba [EAT +03:00]", "Africa/Kampala" => "Africa/Kampala [EAT +03:00]", "Africa/Khartoum" => "Africa/Khartoum [EAT +03:00]", "Africa/Kigali" => "Africa/Kigali [CAT +02:00]", "Africa/Kinshasa" => "Africa/Kinshasa [WAT +01:00]", "Africa/Lagos" => "Africa/Lagos [WAT +01:00]", "Africa/Libreville" => "Africa/Libreville [WAT +01:00]", "Africa/Lome" => "Africa/Lome [GMT  00:00]", "Africa/Luanda" => "Africa/Luanda [WAT +01:00]", "Africa/Lubumbashi" => "Africa/Lubumbashi [CAT +02:00]", "Africa/Lusaka" => "Africa/Lusaka [CAT +02:00]", "Africa/Malabo" => "Africa/Malabo [WAT +01:00]", "Africa/Maputo" => "Africa/Maputo [CAT +02:00]", "Africa/Maseru" => "Africa/Maseru [SAST +02:00]", "Africa/Mbabane" => "Africa/Mbabane [SAST +02:00]", "Africa/Mogadishu" => "Africa/Mogadishu [EAT +03:00]", "Africa/Monrovia" => "Africa/Monrovia [GMT  00:00]", "Africa/Nairobi" => "Africa/Nairobi [EAT +03:00]", "Africa/Ndjamena" => "Africa/Ndjamena [WAT +01:00]", "Africa/Niamey" => "Africa/Niamey [WAT +01:00]", "Africa/Nouakchott" => "Africa/Nouakchott [GMT  00:00]", "Africa/Ouagadougou" => "Africa/Ouagadougou [GMT  00:00]", "Africa/Porto-Novo" => "Africa/Porto-Novo [WAT +01:00]", "Africa/Sao_Tome" => "Africa/Sao_Tome [GMT  00:00]", "Africa/Tripoli" => "Africa/Tripoli [EET +02:00]", "Africa/Tunis" => "Africa/Tunis [CET +01:00]", "Africa/Windhoek" => "Africa/Windhoek [WAST +02:00]", "America/Adak" => "America/Adak [HADT -09:00]", "America/Anchorage" => "America/Anchorage [AKDT -08:00]", "America/Anguilla" => "America/Anguilla [AST -04:00]", "America/Antigua" => "America/Antigua [AST -04:00]", "America/Araguaina" => "America/Araguaina [BRT -03:00]", "America/Argentina/Buenos_Aires" => "America/Argentina/Buenos_Aires [ART -03:00]", "America/Argentina/Catamarca" => "America/Argentina/Catamarca [ART -03:00]", "America/Argentina/Cordoba" => "America/Argentina/Cordoba [ART -03:00]", "America/Argentina/Jujuy" => "America/Argentina/Jujuy [ART -03:00]", "America/Argentina/La_Rioja" => "America/Argentina/La_Rioja [ART -03:00]", "America/Argentina/Mendoza" => "America/Argentina/Mendoza [ART -03:00]", "America/Argentina/Rio_Gallegos" => "America/Argentina/Rio_Gallegos [ART -03:00]", "America/Argentina/Salta" => "America/Argentina/Salta [ART -03:00]", "America/Argentina/San_Juan" => "America/Argentina/San_Juan [ART -03:00]", "America/Argentina/San_Luis" => "America/Argentina/San_Luis [ART -03:00]", "America/Argentina/Tucuman" => "America/Argentina/Tucuman [ART -03:00]", "America/Argentina/Ushuaia" => "America/Argentina/Ushuaia [ART -03:00]", "America/Aruba" => "America/Aruba [AST -04:00]", "America/Asuncion" => "America/Asuncion [PYT -04:00]", "America/Atikokan" => "America/Atikokan [EST -05:00]", "America/Bahia" => "America/Bahia [BRT -03:00]", "America/Bahia_Banderas" => "America/Bahia_Banderas [CDT -05:00]", "America/Barbados" => "America/Barbados [AST -04:00]", "America/Belem" => "America/Belem [BRT -03:00]", "America/Belize" => "America/Belize [CST -06:00]", "America/Blanc-Sablon" => "America/Blanc-Sablon [AST -04:00]", "America/Boa_Vista" => "America/Boa_Vista [AMT -04:00]", "America/Bogota" => "America/Bogota [COT -05:00]", "America/Boise" => "America/Boise [MDT -06:00]", "America/Cambridge_Bay" => "America/Cambridge_Bay [MDT -06:00]", "America/Campo_Grande" => "America/Campo_Grande [AMT -04:00]", "America/Cancun" => "America/Cancun [CDT -05:00]", "America/Caracas" => "America/Caracas [VET -04:30]", "America/Cayenne" => "America/Cayenne [GFT -03:00]", "America/Cayman" => "America/Cayman [EST -05:00]", "America/Chicago" => "America/Chicago [CDT -05:00]", "America/Chihuahua" => "America/Chihuahua [MDT -06:00]", "America/Costa_Rica" => "America/Costa_Rica [CST -06:00]", "America/Creston" => "America/Creston [MST -07:00]", "America/Cuiaba" => "America/Cuiaba [AMT -04:00]", "America/Curacao" => "America/Curacao [AST -04:00]", "America/Danmarkshavn" => "America/Danmarkshavn [GMT  00:00]", "America/Dawson" => "America/Dawson [PDT -07:00]", "America/Dawson_Creek" => "America/Dawson_Creek [MST -07:00]", "America/Denver" => "America/Denver [MDT -06:00]", "America/Detroit" => "America/Detroit [EDT -04:00]", "America/Dominica" => "America/Dominica [AST -04:00]", "America/Edmonton" => "America/Edmonton [MDT -06:00]", "America/Eirunepe" => "America/Eirunepe [ACT -05:00]", "America/El_Salvador" => "America/El_Salvador [CST -06:00]", "America/Fortaleza" => "America/Fortaleza [BRT -03:00]", "America/Glace_Bay" => "America/Glace_Bay [ADT -03:00]", "America/Godthab" => "America/Godthab [WGST -02:00]", "America/Goose_Bay" => "America/Goose_Bay [ADT -03:00]", "America/Grand_Turk" => "America/Grand_Turk [AST -04:00]", "America/Grenada" => "America/Grenada [AST -04:00]", "America/Guadeloupe" => "America/Guadeloupe [AST -04:00]", "America/Guatemala" => "America/Guatemala [CST -06:00]", "America/Guayaquil" => "America/Guayaquil [ECT -05:00]", "America/Guyana" => "America/Guyana [GYT -04:00]", "America/Halifax" => "America/Halifax [ADT -03:00]", "America/Havana" => "America/Havana [CDT -04:00]", "America/Hermosillo" => "America/Hermosillo [MST -07:00]", "America/Indiana/Indianapolis" => "America/Indiana/Indianapolis [EDT -04:00]", "America/Indiana/Knox" => "America/Indiana/Knox [CDT -05:00]", "America/Indiana/Marengo" => "America/Indiana/Marengo [EDT -04:00]", "America/Indiana/Petersburg" => "America/Indiana/Petersburg [EDT -04:00]", "America/Indiana/Tell_City" => "America/Indiana/Tell_City [CDT -05:00]", "America/Indiana/Vevay" => "America/Indiana/Vevay [EDT -04:00]", "America/Indiana/Vincennes" => "America/Indiana/Vincennes [EDT -04:00]", "America/Indiana/Winamac" => "America/Indiana/Winamac [EDT -04:00]", "America/Inuvik" => "America/Inuvik [MDT -06:00]", "America/Iqaluit" => "America/Iqaluit [EDT -04:00]", "America/Jamaica" => "America/Jamaica [EST -05:00]", "America/Juneau" => "America/Juneau [AKDT -08:00]", "America/Kentucky/Louisville" => "America/Kentucky/Louisville [EDT -04:00]", "America/Kentucky/Monticello" => "America/Kentucky/Monticello [EDT -04:00]", "America/Kralendijk" => "America/Kralendijk [AST -04:00]", "America/La_Paz" => "America/La_Paz [BOT -04:00]", "America/Lima" => "America/Lima [PET -05:00]", "America/Los_Angeles" => "America/Los_Angeles [PDT -07:00]", "America/Lower_Princes" => "America/Lower_Princes [AST -04:00]", "America/Maceio" => "America/Maceio [BRT -03:00]", "America/Managua" => "America/Managua [CST -06:00]", "America/Manaus" => "America/Manaus [AMT -04:00]", "America/Marigot" => "America/Marigot [AST -04:00]", "America/Martinique" => "America/Martinique [AST -04:00]", "America/Matamoros" => "America/Matamoros [CDT -05:00]", "America/Mazatlan" => "America/Mazatlan [MDT -06:00]", "America/Menominee" => "America/Menominee [CDT -05:00]", "America/Merida" => "America/Merida [CDT -05:00]", "America/Metlakatla" => "America/Metlakatla [PST -08:00]", "America/Mexico_City" => "America/Mexico_City [CDT -05:00]", "America/Miquelon" => "America/Miquelon [PMDT -02:00]", "America/Moncton" => "America/Moncton [ADT -03:00]", "America/Monterrey" => "America/Monterrey [CDT -05:00]", "America/Montevideo" => "America/Montevideo [UYT -03:00]", "America/Montserrat" => "America/Montserrat [AST -04:00]", "America/Nassau" => "America/Nassau [EDT -04:00]", "America/New_York" => "America/New_York [EDT -04:00]", "America/Nipigon" => "America/Nipigon [EDT -04:00]", "America/Nome" => "America/Nome [AKDT -08:00]", "America/Noronha" => "America/Noronha [FNT -02:00]", "America/North_Dakota/Beulah" => "America/North_Dakota/Beulah [CDT -05:00]", "America/North_Dakota/Center" => "America/North_Dakota/Center [CDT -05:00]", "America/North_Dakota/New_Salem" => "America/North_Dakota/New_Salem [CDT -05:00]", "America/Ojinaga" => "America/Ojinaga [MDT -06:00]", "America/Panama" => "America/Panama [EST -05:00]", "America/Pangnirtung" => "America/Pangnirtung [EDT -04:00]", "America/Paramaribo" => "America/Paramaribo [SRT -03:00]", "America/Phoenix" => "America/Phoenix [MST -07:00]", "America/Port-au-Prince" => "America/Port-au-Prince [EDT -04:00]", "America/Port_of_Spain" => "America/Port_of_Spain [AST -04:00]", "America/Porto_Velho" => "America/Porto_Velho [AMT -04:00]", "America/Puerto_Rico" => "America/Puerto_Rico [AST -04:00]", "America/Rainy_River" => "America/Rainy_River [CDT -05:00]", "America/Rankin_Inlet" => "America/Rankin_Inlet [CDT -05:00]", "America/Recife" => "America/Recife [BRT -03:00]", "America/Regina" => "America/Regina [CST -06:00]", "America/Resolute" => "America/Resolute [CDT -05:00]", "America/Rio_Branco" => "America/Rio_Branco [ACT -05:00]", "America/Santa_Isabel" => "America/Santa_Isabel [PDT -07:00]", "America/Santarem" => "America/Santarem [BRT -03:00]", "America/Santiago" => "America/Santiago [CLST -03:00]", "America/Santo_Domingo" => "America/Santo_Domingo [AST -04:00]", "America/Sao_Paulo" => "America/Sao_Paulo [BRT -03:00]", "America/Scoresbysund" => "America/Scoresbysund [EGST  00:00]", "America/Sitka" => "America/Sitka [AKDT -08:00]", "America/St_Barthelemy" => "America/St_Barthelemy [AST -04:00]", "America/St_Johns" => "America/St_Johns [NDT -02:30]", "America/St_Kitts" => "America/St_Kitts [AST -04:00]", "America/St_Lucia" => "America/St_Lucia [AST -04:00]", "America/St_Thomas" => "America/St_Thomas [AST -04:00]", "America/St_Vincent" => "America/St_Vincent [AST -04:00]", "America/Swift_Current" => "America/Swift_Current [CST -06:00]", "America/Tegucigalpa" => "America/Tegucigalpa [CST -06:00]", "America/Thule" => "America/Thule [ADT -03:00]", "America/Thunder_Bay" => "America/Thunder_Bay [EDT -04:00]", "America/Tijuana" => "America/Tijuana [PDT -07:00]", "America/Toronto" => "America/Toronto [EDT -04:00]", "America/Tortola" => "America/Tortola [AST -04:00]", "America/Vancouver" => "America/Vancouver [PDT -07:00]", "America/Whitehorse" => "America/Whitehorse [PDT -07:00]", "America/Winnipeg" => "America/Winnipeg [CDT -05:00]", "America/Yakutat" => "America/Yakutat [AKDT -08:00]", "America/Yellowknife" => "America/Yellowknife [MDT -06:00]", "Antarctica/Casey" => "Antarctica/Casey [AWST +08:00]", "Antarctica/Davis" => "Antarctica/Davis [DAVT +07:00]", "Antarctica/DumontDUrville" => "Antarctica/DumontDUrville [DDUT +10:00]", "Antarctica/Macquarie" => "Antarctica/Macquarie [MIST +11:00]", "Antarctica/Mawson" => "Antarctica/Mawson [MAWT +05:00]", "Antarctica/McMurdo" => "Antarctica/McMurdo [NZDT +13:00]", "Antarctica/Palmer" => "Antarctica/Palmer [CLST -03:00]", "Antarctica/Rothera" => "Antarctica/Rothera [ROTT -03:00]", "Antarctica/Syowa" => "Antarctica/Syowa [SYOT +03:00]", "Antarctica/Troll" => "Antarctica/Troll [CEST +02:00]", "Antarctica/Vostok" => "Antarctica/Vostok [VOST +06:00]", "Arctic/Longyearbyen" => "Arctic/Longyearbyen [CEST +02:00]", "Asia/Aden" => "Asia/Aden [AST +03:00]", "Asia/Almaty" => "Asia/Almaty [ALMT +06:00]", "Asia/Amman" => "Asia/Amman [EEST +03:00]", "Asia/Anadyr" => "Asia/Anadyr [ANAT +12:00]", "Asia/Aqtau" => "Asia/Aqtau [AQTT +05:00]", "Asia/Aqtobe" => "Asia/Aqtobe [AQTT +05:00]", "Asia/Ashgabat" => "Asia/Ashgabat [TMT +05:00]", "Asia/Baghdad" => "Asia/Baghdad [AST +03:00]", "Asia/Bahrain" => "Asia/Bahrain [AST +03:00]", "Asia/Baku" => "Asia/Baku [AZST +05:00]", "Asia/Bangkok" => "Asia/Bangkok [ICT +07:00]", "Asia/Beirut" => "Asia/Beirut [EEST +03:00]", "Asia/Bishkek" => "Asia/Bishkek [KGT +06:00]", "Asia/Brunei" => "Asia/Brunei [BNT +08:00]", "Asia/Chita" => "Asia/Chita [IRKT +08:00]", "Asia/Choibalsan" => "Asia/Choibalsan [CHOT +08:00]", "Asia/Colombo" => "Asia/Colombo [IST +05:30]", "Asia/Damascus" => "Asia/Damascus [EEST +03:00]", "Asia/Dhaka" => "Asia/Dhaka [BDT +06:00]", "Asia/Dili" => "Asia/Dili [TLT +09:00]", "Asia/Dubai" => "Asia/Dubai [GST +04:00]", "Asia/Dushanbe" => "Asia/Dushanbe [TJT +05:00]", "Asia/Gaza" => "Asia/Gaza [EET +02:00]", "Asia/Hebron" => "Asia/Hebron [EET +02:00]", "Asia/Ho_Chi_Minh" => "Asia/Ho_Chi_Minh [ICT +07:00]", "Asia/Hong_Kong" => "Asia/Hong_Kong [HKT +08:00]", "Asia/Hovd" => "Asia/Hovd [HOVT +07:00]", "Asia/Irkutsk" => "Asia/Irkutsk [IRKT +08:00]", "Asia/Jakarta" => "Asia/Jakarta [WIB +07:00]", "Asia/Jayapura" => "Asia/Jayapura [WIT +09:00]", "Asia/Jerusalem" => "Asia/Jerusalem [IDT +03:00]", "Asia/Kabul" => "Asia/Kabul [AFT +04:30]", "Asia/Kamchatka" => "Asia/Kamchatka [PETT +12:00]", "Asia/Karachi" => "Asia/Karachi [PKT +05:00]", "Asia/Kathmandu" => "Asia/Kathmandu [NPT +05:45]", "Asia/Khandyga" => "Asia/Khandyga [YAKT +09:00]", "Asia/Kolkata" => "Asia/Kolkata [IST +05:30]", "Asia/Krasnoyarsk" => "Asia/Krasnoyarsk [KRAT +07:00]", "Asia/Kuala_Lumpur" => "Asia/Kuala_Lumpur [MYT +08:00]", "Asia/Kuching" => "Asia/Kuching [MYT +08:00]", "Asia/Kuwait" => "Asia/Kuwait [AST +03:00]", "Asia/Macau" => "Asia/Macau [CST +08:00]", "Asia/Magadan" => "Asia/Magadan [MAGT +10:00]", "Asia/Makassar" => "Asia/Makassar [WITA +08:00]", "Asia/Manila" => "Asia/Manila [PHT +08:00]", "Asia/Muscat" => "Asia/Muscat [GST +04:00]", "Asia/Nicosia" => "Asia/Nicosia [EEST +03:00]", "Asia/Novokuznetsk" => "Asia/Novokuznetsk [KRAT +07:00]", "Asia/Novosibirsk" => "Asia/Novosibirsk [NOVT +06:00]", "Asia/Omsk" => "Asia/Omsk [OMST +06:00]", "Asia/Oral" => "Asia/Oral [ORAT +05:00]", "Asia/Phnom_Penh" => "Asia/Phnom_Penh [ICT +07:00]", "Asia/Pontianak" => "Asia/Pontianak [WIB +07:00]", "Asia/Pyongyang" => "Asia/Pyongyang [KST +09:00]", "Asia/Qatar" => "Asia/Qatar [AST +03:00]", "Asia/Qyzylorda" => "Asia/Qyzylorda [QYZT +06:00]", "Asia/Rangoon" => "Asia/Rangoon [MMT +06:30]", "Asia/Riyadh" => "Asia/Riyadh [AST +03:00]", "Asia/Sakhalin" => "Asia/Sakhalin [SAKT +10:00]", "Asia/Samarkand" => "Asia/Samarkand [UZT +05:00]", "Asia/Seoul" => "Asia/Seoul [KST +09:00]", "Asia/Shanghai" => "Asia/Shanghai [CST +08:00]", "Asia/Singapore" => "Asia/Singapore [SGT +08:00]", "Asia/Srednekolymsk" => "Asia/Srednekolymsk [SRET +11:00]", "Asia/Taipei" => "Asia/Taipei [CST +08:00]", "Asia/Tashkent" => "Asia/Tashkent [UZT +05:00]", "Asia/Tbilisi" => "Asia/Tbilisi [GET +04:00]", "Asia/Tehran" => "Asia/Tehran [IRST +03:30]", "Asia/Thimphu" => "Asia/Thimphu [BTT +06:00]", "Asia/Tokyo" => "Asia/Tokyo [JST +09:00]", "Asia/Ulaanbaatar" => "Asia/Ulaanbaatar [ULAT +08:00]", "Asia/Urumqi" => "Asia/Urumqi [XJT +06:00]", "Asia/Ust-Nera" => "Asia/Ust-Nera [VLAT +10:00]", "Asia/Vientiane" => "Asia/Vientiane [ICT +07:00]", "Asia/Vladivostok" => "Asia/Vladivostok [VLAT +10:00]", "Asia/Yakutsk" => "Asia/Yakutsk [YAKT +09:00]", "Asia/Yekaterinburg" => "Asia/Yekaterinburg [YEKT +05:00]", "Asia/Yerevan" => "Asia/Yerevan [AMT +04:00]", "Atlantic/Azores" => "Atlantic/Azores [AZOST  00:00]", "Atlantic/Bermuda" => "Atlantic/Bermuda [ADT -03:00]", "Atlantic/Canary" => "Atlantic/Canary [WEST +01:00]", "Atlantic/Cape_Verde" => "Atlantic/Cape_Verde [CVT -01:00]", "Atlantic/Faroe" => "Atlantic/Faroe [WEST +01:00]", "Atlantic/Madeira" => "Atlantic/Madeira [WEST +01:00]", "Atlantic/Reykjavik" => "Atlantic/Reykjavik [GMT  00:00]", "Atlantic/South_Georgia" => "Atlantic/South_Georgia [GST -02:00]", "Atlantic/St_Helena" => "Atlantic/St_Helena [GMT  00:00]", "Atlantic/Stanley" => "Atlantic/Stanley [FKST -03:00]", "Australia/Adelaide" => "Australia/Adelaide [ACDT +10:30]", "Australia/Brisbane" => "Australia/Brisbane [AEST +10:00]", "Australia/Broken_Hill" => "Australia/Broken_Hill [ACDT +10:30]", "Australia/Currie" => "Australia/Currie [AEDT +11:00]", "Australia/Darwin" => "Australia/Darwin [ACST +09:30]", "Australia/Eucla" => "Australia/Eucla [ACWST +08:45]", "Australia/Hobart" => "Australia/Hobart [AEDT +11:00]", "Australia/Lindeman" => "Australia/Lindeman [AEST +10:00]", "Australia/Lord_Howe" => "Australia/Lord_Howe [LHDT +11:00]", "Australia/Melbourne" => "Australia/Melbourne [AEDT +11:00]", "Australia/Perth" => "Australia/Perth [AWST +08:00]", "Australia/Sydney" => "Australia/Sydney [AEDT +11:00]", "Europe/Amsterdam" => "Europe/Amsterdam [CEST +02:00]", "Europe/Andorra" => "Europe/Andorra [CEST +02:00]", "Europe/Athens" => "Europe/Athens [EEST +03:00]", "Europe/Belgrade" => "Europe/Belgrade [CEST +02:00]", "Europe/Berlin" => "Europe/Berlin [CEST +02:00]", "Europe/Bratislava" => "Europe/Bratislava [CEST +02:00]", "Europe/Brussels" => "Europe/Brussels [CEST +02:00]", "Europe/Bucharest" => "Europe/Bucharest [EEST +03:00]", "Europe/Budapest" => "Europe/Budapest [CEST +02:00]", "Europe/Busingen" => "Europe/Busingen [CEST +02:00]", "Europe/Chisinau" => "Europe/Chisinau [EEST +03:00]", "Europe/Copenhagen" => "Europe/Copenhagen [CEST +02:00]", "Europe/Dublin" => "Europe/Dublin [IST +01:00]", "Europe/Gibraltar" => "Europe/Gibraltar [CEST +02:00]", "Europe/Guernsey" => "Europe/Guernsey [BST +01:00]", "Europe/Helsinki" => "Europe/Helsinki [EEST +03:00]", "Europe/Isle_of_Man" => "Europe/Isle_of_Man [BST +01:00]", "Europe/Istanbul" => "Europe/Istanbul [EEST +03:00]", "Europe/Jersey" => "Europe/Jersey [BST +01:00]", "Europe/Kaliningrad" => "Europe/Kaliningrad [EET +02:00]", "Europe/Kiev" => "Europe/Kiev [EEST +03:00]", "Europe/Lisbon" => "Europe/Lisbon [WEST +01:00]", "Europe/Ljubljana" => "Europe/Ljubljana [CEST +02:00]", "Europe/London" => "Europe/London [BST +01:00]", "Europe/Luxembourg" => "Europe/Luxembourg [CEST +02:00]", "Europe/Madrid" => "Europe/Madrid [CEST +02:00]", "Europe/Malta" => "Europe/Malta [CEST +02:00]", "Europe/Mariehamn" => "Europe/Mariehamn [EEST +03:00]", "Europe/Minsk" => "Europe/Minsk [MSK +03:00]", "Europe/Monaco" => "Europe/Monaco [CEST +02:00]", "Europe/Moscow" => "Europe/Moscow [MSK +03:00]", "Europe/Oslo" => "Europe/Oslo [CEST +02:00]", "Europe/Paris" => "Europe/Paris [CEST +02:00]", "Europe/Podgorica" => "Europe/Podgorica [CEST +02:00]", "Europe/Prague" => "Europe/Prague [CEST +02:00]", "Europe/Riga" => "Europe/Riga [EEST +03:00]", "Europe/Rome" => "Europe/Rome [CEST +02:00]", "Europe/Samara" => "Europe/Samara [SAMT +04:00]", "Europe/San_Marino" => "Europe/San_Marino [CEST +02:00]", "Europe/Sarajevo" => "Europe/Sarajevo [CEST +02:00]", "Europe/Simferopol" => "Europe/Simferopol [MSK +03:00]", "Europe/Skopje" => "Europe/Skopje [CEST +02:00]", "Europe/Sofia" => "Europe/Sofia [EEST +03:00]", "Europe/Stockholm" => "Europe/Stockholm [CEST +02:00]", "Europe/Tallinn" => "Europe/Tallinn [EEST +03:00]", "Europe/Tirane" => "Europe/Tirane [CEST +02:00]", "Europe/Uzhgorod" => "Europe/Uzhgorod [EEST +03:00]", "Europe/Vaduz" => "Europe/Vaduz [CEST +02:00]", "Europe/Vatican" => "Europe/Vatican [CEST +02:00]", "Europe/Vienna" => "Europe/Vienna [CEST +02:00]", "Europe/Vilnius" => "Europe/Vilnius [EEST +03:00]", "Europe/Volgograd" => "Europe/Volgograd [MSK +03:00]", "Europe/Warsaw" => "Europe/Warsaw [CEST +02:00]", "Europe/Zagreb" => "Europe/Zagreb [CEST +02:00]", "Europe/Zaporozhye" => "Europe/Zaporozhye [EEST +03:00]", "Europe/Zurich" => "Europe/Zurich [CEST +02:00]", "Indian/Antananarivo" => "Indian/Antananarivo [EAT +03:00]", "Indian/Chagos" => "Indian/Chagos [IOT +06:00]", "Indian/Christmas" => "Indian/Christmas [CXT +07:00]", "Indian/Cocos" => "Indian/Cocos [CCT +06:30]", "Indian/Comoro" => "Indian/Comoro [EAT +03:00]", "Indian/Kerguelen" => "Indian/Kerguelen [TFT +05:00]", "Indian/Mahe" => "Indian/Mahe [SCT +04:00]", "Indian/Maldives" => "Indian/Maldives [MVT +05:00]", "Indian/Mauritius" => "Indian/Mauritius [MUT +04:00]", "Indian/Mayotte" => "Indian/Mayotte [EAT +03:00]", "Indian/Reunion" => "Indian/Reunion [RET +04:00]", "Pacific/Apia" => "Pacific/Apia [WSDT +14:00]", "Pacific/Auckland" => "Pacific/Auckland [NZDT +13:00]", "Pacific/Bougainville" => "Pacific/Bougainville [BST +11:00]", "Pacific/Chatham" => "Pacific/Chatham [CHADT +13:45]", "Pacific/Chuuk" => "Pacific/Chuuk [CHUT +10:00]", "Pacific/Easter" => "Pacific/Easter [EASST -05:00]", "Pacific/Efate" => "Pacific/Efate [VUT +11:00]", "Pacific/Enderbury" => "Pacific/Enderbury [PHOT +13:00]", "Pacific/Fakaofo" => "Pacific/Fakaofo [TKT +13:00]", "Pacific/Fiji" => "Pacific/Fiji [FJT +12:00]", "Pacific/Funafuti" => "Pacific/Funafuti [TVT +12:00]", "Pacific/Galapagos" => "Pacific/Galapagos [GALT -06:00]", "Pacific/Gambier" => "Pacific/Gambier [GAMT -09:00]", "Pacific/Guadalcanal" => "Pacific/Guadalcanal [SBT +11:00]", "Pacific/Guam" => "Pacific/Guam [ChST +10:00]", "Pacific/Honolulu" => "Pacific/Honolulu [HST -10:00]", "Pacific/Johnston" => "Pacific/Johnston [HST -10:00]", "Pacific/Kiritimati" => "Pacific/Kiritimati [LINT +14:00]", "Pacific/Kosrae" => "Pacific/Kosrae [KOST +11:00]", "Pacific/Kwajalein" => "Pacific/Kwajalein [MHT +12:00]", "Pacific/Majuro" => "Pacific/Majuro [MHT +12:00]", "Pacific/Marquesas" => "Pacific/Marquesas [MART -09:30]", "Pacific/Midway" => "Pacific/Midway [SST -11:00]", "Pacific/Nauru" => "Pacific/Nauru [NRT +12:00]", "Pacific/Niue" => "Pacific/Niue [NUT -11:00]", "Pacific/Norfolk" => "Pacific/Norfolk [NFT +11:30]", "Pacific/Noumea" => "Pacific/Noumea [NCT +11:00]", "Pacific/Pago_Pago" => "Pacific/Pago_Pago [SST -11:00]", "Pacific/Palau" => "Pacific/Palau [PWT +09:00]", "Pacific/Pitcairn" => "Pacific/Pitcairn [PST -08:00]", "Pacific/Pohnpei" => "Pacific/Pohnpei [PONT +11:00]", "Pacific/Port_Moresby" => "Pacific/Port_Moresby [PGT +10:00]", "Pacific/Rarotonga" => "Pacific/Rarotonga [CKT -10:00]", "Pacific/Saipan" => "Pacific/Saipan [ChST +10:00]", "Pacific/Tahiti" => "Pacific/Tahiti [TAHT -10:00]", "Pacific/Tarawa" => "Pacific/Tarawa [GILT +12:00]", "Pacific/Tongatapu" => "Pacific/Tongatapu [TOT +13:00]", "Pacific/Wake" => "Pacific/Wake [WAKT +12:00]", "Pacific/Wallis" => "Pacific/Wallis [WFT +12:00]", "UTC" => "UTC [UTC  00:00]");



function getAdminSettings() {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `type`, `value` FROM `admin_settings`;");
    $rows = $ipTV_db_admin->get_rows();
    foreach ($rows as $val) {
        $output[$val['type']] = $val['value'];
    }
    return $output;
}

function getRegisteredUser($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `reg_users` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getRegisteredUserHash($rHash) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `reg_users` WHERE MD5(`username`) = '%s' LIMIT 1;", $rHash);
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function doLogin($rUsername, $rPassword) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `id`, `username`, `password`, `member_group_id`, `google_2fa_sec`, `status` FROM `reg_users` WHERE `username` = '%s' LIMIT 1;", $rUsername);
    if ($ipTV_db_admin->num_rows() == 1) {
        $rRow = $ipTV_db_admin->get_row();

        if (cryptPassword($rPassword) == $rRow["password"]) {
            return $rRow;
        }
    }
    return null;
}

function cryptPassword($password, $salt = "xtreamcodes", $rounds = 20000) {
    if ($salt == "") {
        $salt = substr(bin2hex(openssl_random_pseudo_bytes(16)), 0, 16);
    }
    $hash = crypt($password, sprintf('$6$rounds=%d$%s$', $rounds, $salt));
    return $hash;
}

function getUser($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `users` WHERE `id` = " . intval($rID) . ";");
    if ($ipTV_db_admin->num_rows() == 1) {
        return $ipTV_db_admin->get_row();
    }
    return null;
}

function getPermissions($rID) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT * FROM `member_groups` WHERE `group_id` = %s;", intval($rID));
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

function getStreamingServers($rActive = false) {
    global $ipTV_db_admin, $rPermissions;
    $return = array();
    if ($rActive) {
        $ipTV_db_admin->query("SELECT * FROM `streaming_servers` WHERE `status` = 1 ORDER BY `id` ASC;");
    } else {
        $ipTV_db_admin->query("SELECT * FROM `streaming_servers` ORDER BY `id` ASC;");
    }
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            if ($rPermissions["is_reseller"]) {
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

function shutdown_admin() {
    global $ipTV_db_admin;

    if (is_object($ipTV_db_admin)) {
        $ipTV_db_admin->close_mysql();
    }
}

function issecure() {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443;
}

function getProtocol() {
    if (issecure()) {
        return 'https';
    }
    return 'http';
}

function getScriptVer() {
    global $ipTV_db_admin;
    $ipTV_db_admin->query("SELECT `script_version` FROM `streaming_servers` WHERE `is_main` = '1'");
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
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}


function changePort_new($rServerID, $rType, $rPorts, $rReload = false) {
    global $ipTV_db_admin;
    $ipTV_db_admin->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(\'%d\', \'%s\', \'%s\');', $rServerID, time(), json_encode(array('action' => 'set_port', 'type' => intval($rType), 'ports' => $rPorts, 'reload' => $rReload)));
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
