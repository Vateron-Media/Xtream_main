SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xtream_iptvpro`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_output`
--

CREATE TABLE IF NOT EXISTS `access_output` (
  `access_output_id` int(11) NOT NULL AUTO_INCREMENT,
  `output_name` varchar(255) NOT NULL,
  `output_key` varchar(255) NOT NULL,
  `output_ext` varchar(255) NOT NULL,
  PRIMARY KEY (`access_output_id`),
  KEY `output_key` (`output_key`),
  KEY `output_ext` (`output_ext`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `access_output`
--

INSERT INTO `access_output` (`access_output_id`, `output_name`, `output_key`, `output_ext`) VALUES
(1, 'HLS', 'm3u8', 'm3u8'),
(2, 'MPEGTS', 'ts', 'ts'),
(3, 'RTMP', 'rtmp', '');

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE IF NOT EXISTS `admin_settings` (
  `type` varchar(128) NOT NULL DEFAULT '',
  `value` varchar(4096) NOT NULL DEFAULT '',
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`type`, `value`) VALUES
('active_mannuals', '1'),
('reseller_can_isplock', '1'),
('reseller_reset_isplock', '1'),
('auto_refresh', '1');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_ips`
--

CREATE TABLE IF NOT EXISTS `blocked_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(39) NOT NULL,
  `notes` mediumtext NOT NULL,
  `date` int(11) NOT NULL,
  `attempts_blocked` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_2` (`ip`),
  UNIQUE KEY `ip_3` (`ip`),
  KEY `ip` (`ip`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_isps`
--

CREATE TABLE IF NOT EXISTS `blocked_isps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isp` mediumtext DEFAULT NULL,
  `blocked` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_user_agents`
--

CREATE TABLE IF NOT EXISTS `blocked_user_agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_agent` varchar(255) NOT NULL,
  `exact_match` int(11) NOT NULL DEFAULT 0,
  `attempts_blocked` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `exact_match` (`exact_match`),
  KEY `user_agent` (`user_agent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bouquets`
--

CREATE TABLE IF NOT EXISTS `bouquets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bouquet_name` mediumtext NOT NULL,
  `bouquet_channels` mediumtext DEFAULT NULL,
  `bouquet_movies` mediumtext DEFAULT NULL,
  `bouquet_radios` mediumtext DEFAULT NULL,
  `bouquet_series` mediumtext DEFAULT NULL,
  `bouquet_order` int(16) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_logs`
--

CREATE TABLE IF NOT EXISTS `client_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_status` varchar(255) NOT NULL,
  `query_string` mediumtext NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `extra_data` mediumtext NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `created`
--

CREATE TABLE IF NOT EXISTS `created` (
  `id` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `category_id` tinyint(4) NOT NULL,
  `stream_display_name` tinyint(4) NOT NULL,
  `stream_source` tinyint(4) NOT NULL,
  `stream_icon` tinyint(4) NOT NULL,
  `notes` tinyint(4) NOT NULL,
  `created_channel_location` tinyint(4) NOT NULL,
  `enable_transcode` tinyint(4) NOT NULL,
  `transcode_attributes` tinyint(4) NOT NULL,
  `custom_ffmpeg` tinyint(4) NOT NULL,
  `movie_properties` tinyint(4) NOT NULL,
  `movie_subtitles` tinyint(4) NOT NULL,
  `read_native` tinyint(4) NOT NULL,
  `target_container` tinyint(4) NOT NULL,
  `stream_all` tinyint(4) NOT NULL,
  `remove_subtitles` tinyint(4) NOT NULL,
  `custom_sid` tinyint(4) NOT NULL,
  `epg_id` tinyint(4) NOT NULL,
  `channel_id` tinyint(4) NOT NULL,
  `epg_lang` tinyint(4) NOT NULL,
  `order` tinyint(4) NOT NULL,
  `auto_restart` tinyint(4) NOT NULL,
  `transcode_profile_id` tinyint(4) NOT NULL,
  `pids_create_channel` tinyint(4) NOT NULL,
  `cchannel_rsources` tinyint(4) NOT NULL,
  `gen_timestamps` tinyint(4) NOT NULL,
  `added` tinyint(4) NOT NULL,
  `series_no` tinyint(4) NOT NULL,
  `direct_source` tinyint(4) NOT NULL,
  `tv_archive_duration` tinyint(4) NOT NULL,
  `tv_archive_server_id` tinyint(4) NOT NULL,
  `tv_archive_pid` tinyint(4) NOT NULL,
  `movie_symlink` tinyint(4) NOT NULL,
  `redirect_stream` tinyint(4) NOT NULL,
  `rtmp_output` tinyint(4) NOT NULL,
  `number` tinyint(4) NOT NULL,
  `allow_record` tinyint(4) NOT NULL,
  `probesize_ondemand` tinyint(4) NOT NULL,
  `custom_map` tinyint(4) NOT NULL,
  `external_push` tinyint(4) NOT NULL,
  `delay_minutes` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credits_log`
--

CREATE TABLE IF NOT EXISTS `credits_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `date` int(11) NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `target_id` (`target_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crontab`
--

CREATE TABLE IF NOT EXISTS `crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `time` varchar(128) DEFAULT '* * * * *',
  `enabled` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`),
  KEY `filename` (`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `crontab`
--

INSERT INTO `crontab` (`id`, `filename`, `time`, `enabled`) VALUES
(1, 'activity.php', '* * * * *', 1),
(2, 'backups.php', '* * * * *', 1),
(3, 'cache_engine.php', '*/5 * * * *', 1),
(4, 'cache.php', '* * * * *', 1),
(5, 'epg.php', '0 0 * * *', 1),
(6, 'errors.php', '* * * * *', 1),
(7, 'kill_leaks.php', '* * * * *', 1),
(8, 'user_logs.php', '* * * * *', 1),
(9, 'pid_monitor.php', '0 * * * *', 1),
(10, 'servers.php', '* * * * *', 1),
(11, 'stats.php', '0 * * * *', 1),
(12, 'streams.php', '* * * * *', 1),
(13, 'tmdb_async.php', '0 * * * *', 1),
(14, 'tmp.php', '* * * * *', 1),
(15, 'users.php', '* * * * *', 1),
(16, 'series.php', '* * * * *', 1),
(17, 'vod.php', '* * * * *', 1),
(18, 'watch_folder.php', '*/5 * * * *', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_statistics`
--

CREATE TABLE IF NOT EXISTS `dashboard_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(16) NOT NULL DEFAULT '',
  `time` int(16) NOT NULL DEFAULT 0,
  `count` int(16) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE IF NOT EXISTS `devices` (
  `device_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_name` varchar(255) NOT NULL,
  `device_key` varchar(255) NOT NULL,
  `device_filename` varchar(255) NOT NULL,
  `device_header` mediumtext NOT NULL,
  `device_conf` mediumtext NOT NULL,
  `device_footer` mediumtext NOT NULL,
  `default_output` int(11) NOT NULL DEFAULT 0,
  `copy_text` mediumtext DEFAULT NULL,
  PRIMARY KEY (`device_id`),
  KEY `device_key` (`device_key`),
  KEY `default_output` (`default_output`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`device_id`, `device_name`, `device_key`, `device_filename`, `device_header`, `device_conf`, `device_footer`, `default_output`, `copy_text`) VALUES
(1, 'M3U Standard', 'm3u', 'playlist_{USERNAME}.m3u', '#EXTM3U', '#EXTINF:-1,{CHANNEL_NAME}\r\n{URL}', '', 2, NULL),
(2, 'M3U Plus', 'm3u_plus', 'playlist_{USERNAME}_plus.m3u', '#EXTM3U', '#EXTINF:-1 xui-id=\"{XUI_ID}\" tvg-id=\"{CHANNEL_ID}\" tvg-name=\"{CHANNEL_NAME}\" tvg-logo=\"{CHANNEL_ICON}\" group-title=\"{CATEGORY}\",{CHANNEL_NAME}\r\n{URL}', '', 2, NULL),
(3, 'Simple List', 'simple', 'simple_{USERNAME}.txt', '', '{URL} #Name: {CHANNEL_NAME}', '', 2, NULL),
(4, 'Ariva', 'ariva', 'ariva_{USERNAME}.txt', '', '{CHANNEL_NAME},{URL}', '', 2, NULL),
(5, 'DreamBox OE 2.0', 'dreambox', 'userbouquet.favourites.tv', '#NAME {BOUQUET_NAME}', '#SERVICE {ESR_ID}{SID}{URL#:}\r\n#DESCRIPTION {CHANNEL_NAME}', '', 2, NULL),
(6, 'Enigma 2 OE 1.6', 'enigma16', 'userbouquet.favourites.tv', '#NAME {BOUQUET_NAME}', '#SERVICE 4097{SID}{URL#:}\r\n#DESCRIPTION {CHANNEL_NAME}', '', 2, NULL),
(7, 'Enigma 2 OE 1.6 Auto Script', 'enigma216_script', 'iptv.sh', 'USERNAME=\"{USERNAME}\";PASSWORD=\"{PASSWORD}\";bouquet=\"{BOUQUET_NAME}\";directory=\"/etc/enigma2/iptv.sh\";url=\"{SERVER_URL}playlist/$USERNAME/$PASSWORD/enigma16?output={OUTPUT_KEY}\";rm /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv;wget -O /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv $url;if ! cat /etc/enigma2/bouquets.tv | grep -v grep | grep -c $bouquet > /dev/null;then echo \"[+] Creating IPTV folder...\";cat /etc/enigma2/bouquets.tv | sed -n 1p > /etc/enigma2/new_bouquets.tv;echo \'#SERVICE 1:7:1:0:0:0:0:0:0:0:FROM BOUQUET \"userbouquet.\'$bouquet\'__tv_.tv\" ORDER BY bouquet\' >> /etc/enigma2/new_bouquets.tv; cat /etc/enigma2/bouquets.tv | sed -n \'2,$p\' >> /etc/enigma2/new_bouquets.tv;rm /etc/enigma2/bouquets.tv;mv /etc/enigma2/new_bouquets.tv /etc/enigma2/bouquets.tv;fi;rm /usr/bin/enigma2_pre_start.sh;echo \"Writing to file...\";echo \"/bin/sh \"$directory\" > /dev/null 2>&1 &\" > /usr/bin/enigma2_pre_start.sh;chmod 777 /usr/bin/enigma2_pre_start.sh;wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\";wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\"; read -p \"Press enter to complete setup and reboot\";;', '', '', 2, 'wget -O /etc/enigma2/iptv.sh {DEVICE_LINK} && chmod 777 /etc/enigma2/iptv.sh && /etc/enigma2/iptv.sh'),
(8, 'Enigma 2 OE 2.0 Auto Script', 'enigma22_script', 'iptv.sh', 'USERNAME=\"{USERNAME}\";PASSWORD=\"{PASSWORD}\";bouquet=\"{BOUQUET_NAME}\";directory=\"/etc/enigma2/iptv.sh\";url=\"{SERVER_URL}playlist/$USERNAME/$PASSWORD/dreambox?output={OUTPUT_KEY}\";rm /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv;wget -O /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv $url;if ! cat /etc/enigma2/bouquets.tv | grep -v grep | grep -c $bouquet > /dev/null;then echo \"[+] Creating IPTV folder...\";cat /etc/enigma2/bouquets.tv | sed -n 1p > /etc/enigma2/new_bouquets.tv;echo \'#SERVICE 1:7:1:0:0:0:0:0:0:0:FROM BOUQUET \"userbouquet.\'$bouquet\'__tv_.tv\" ORDER BY bouquet\' >> /etc/enigma2/new_bouquets.tv; cat /etc/enigma2/bouquets.tv | sed -n \'2,$p\' >> /etc/enigma2/new_bouquets.tv;rm /etc/enigma2/bouquets.tv;mv /etc/enigma2/new_bouquets.tv /etc/enigma2/bouquets.tv;fi;rm /usr/bin/enigma2_pre_start.sh;echo \"Writing to file...\";echo \"/bin/sh \"$directory\" > /dev/null 2>&1 &\" > /usr/bin/enigma2_pre_start.sh;chmod 777 /usr/bin/enigma2_pre_start.sh;wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\";wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\"; read -p \"Press enter to complete setup and reboot\";', '', '', 2, 'wget -O /etc/enigma2/iptv.sh {DEVICE_LINK} && chmod 777 /etc/enigma2/iptv.sh && /etc/enigma2/iptv.sh'),
(9, 'Fortec999/Prifix9400/Starport', 'fps', 'Royal.cfg', '', 'IPTV: { {CHANNEL_NAME} } { {URL} }', '', 2, NULL),
(10, 'Geant/Starsat/Tiger/Qmax/Hyper/Royal', 'gst', '{USERNAME}_list.txt', '', 'I: {URL} {CHANNEL_NAME}', '', 2, NULL),
(11, 'GigaBlue', 'gigablue', 'userbouquet.favourites.tv', '#NAME {BOUQUET_NAME}', '#SERVICE 4097:0:1:0:0:0:0:0:0:0:{URL#:}\r\n#DESCRIPTION {CHANNEL_NAME}', '', 2, NULL),
(12, 'MediaStar / StarLive v4', 'mediastar', 'tvlist.txt', '', '{CHANNEL_NAME} {URL}', '', 2, NULL),
(13, 'Octagon', 'octagon', 'internettv.feed', '', '[TITLE]\r\n{CHANNEL_NAME}\r\n[URL]\r\n{URL}\r\n[DESCRIPTION]\r\nIPTV\r\n[TYPE]\r\nLive', '', 2, NULL),
(14, 'Octagon Auto Script', 'octagon_script', 'iptv', 'USERNAME=\"{USERNAME}\";PASSWORD=\"{PASSWORD}\";url=\"{SERVER_URL}get.php?username=$USERNAME&password=$PASSWORD&type=octagon&output={OUTPUT_KEY}\";rm /var/freetvplus/internettv.feed;wget -O /var/freetvplus/internettv.feed1 $url;chmod 777 /var/freetvplus/internettv.feed1;awk -v BINMODE=3 -v RS=\'(\\r\\n|\\n)\' -v ORS=\'\\n\' \'{ print }\' /var/freetvplus/internettv.feed1 > /var/freetvplus/internettv.feed;rm /var/freetvplus/internettv.feed1', '', '', 2, 'wget -qO /var/bin/iptv {DEVICE_LINK}'),
(15, 'Revolution 60/60 | Sunplus', 'revosun', 'network_iptv.cfg', '', 'IPTV: { {CHANNEL_NAME} } { {URL} }', '', 2, NULL),
(16, 'Spark', 'spark', 'webtv_usr.xml', '<?xml version=\"1.0\"?>\r\n<webtvs>', '<webtv title=\"{CHANNEL_NAME}\" urlkey=\"0\" url=\"{URL}\" description=\"\" iconsrc=\"{CHANNEL_ICON}\" iconsrc_b=\"\" group=\"0\" type=\"0\" />', '</webtvs>', 2, NULL),
(17, 'Starlive v3/StarSat HD6060/AZclass', 'starlivev3', 'iptvlist.txt', '', '{CHANNEL_NAME},{URL}', '', 2, NULL),
(18, 'StarLive v5', 'starlivev5', 'channel.jason', '', '', '', 2, NULL),
(19, 'WebTV List', 'webtvlist', 'webtv list.txt', '', 'Channel name:{CHANNEL_NAME}\r\nURL:{URL}', '[Webtv channel END]', 2, NULL),
(20, 'Zorro', 'zorro', 'iptv.cfg', '<NETDBS_TXT_VER_1>', 'IPTV: { {CHANNEL_NAME} } { {URL} } -HIDE_URL', '', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enigma2_actions`
--

CREATE TABLE IF NOT EXISTS `enigma2_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `type` text NOT NULL,
  `key` text NOT NULL,
  `command` text NOT NULL,
  `command2` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enigma2_devices`
--

CREATE TABLE IF NOT EXISTS `enigma2_devices` (
  `device_id` int(12) NOT NULL AUTO_INCREMENT,
  `mac` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `modem_mac` varchar(255) NOT NULL,
  `local_ip` varchar(255) NOT NULL,
  `public_ip` varchar(255) NOT NULL,
  `key_auth` varchar(255) NOT NULL,
  `enigma_version` varchar(255) NOT NULL,
  `cpu` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `lversion` text NOT NULL,
  `token` varchar(32) NOT NULL,
  `last_updated` int(11) NOT NULL,
  `watchdog_timeout` int(11) NOT NULL,
  `lock_device` tinyint(4) NOT NULL DEFAULT 0,
  `telnet_enable` tinyint(4) NOT NULL DEFAULT 1,
  `ftp_enable` tinyint(4) NOT NULL DEFAULT 1,
  `ssh_enable` tinyint(4) NOT NULL DEFAULT 1,
  `dns` varchar(255) NOT NULL,
  `original_mac` varchar(255) NOT NULL,
  `rc` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`device_id`),
  KEY `mac` (`mac`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enigma2_failed`
--

CREATE TABLE IF NOT EXISTS `enigma2_failed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_mac` varchar(255) NOT NULL,
  `virtual_mac` varchar(255) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `original_mac` (`original_mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `epg`
--

CREATE TABLE IF NOT EXISTS `epg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epg_name` varchar(255) NOT NULL,
  `epg_file` varchar(300) NOT NULL,
  `integrity` varchar(255) DEFAULT NULL,
  `last_updated` int(11) DEFAULT NULL,
  `days_keep` int(11) NOT NULL DEFAULT 7,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `epg_data`
--

CREATE TABLE IF NOT EXISTS `epg_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epg_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `start` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` mediumtext NOT NULL,
  `channel_id` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `epg_id` (`epg_id`),
  KEY `start` (`start`),
  KEY `end` (`end`),
  KEY `lang` (`lang`),
  KEY `channel_id` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `isp_addon`
--

CREATE TABLE IF NOT EXISTS `isp_addon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isp` text NOT NULL,
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lines_divergence`
--

CREATE TABLE IF NOT EXISTS `lines_divergence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(32) DEFAULT NULL,
  `divergence` float DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uuid` (`uuid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lines_live`
--

CREATE TABLE IF NOT EXISTS `lines_live` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `user_ip` varchar(39) DEFAULT NULL,
  `container` varchar(50) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `date_start` int(11) DEFAULT NULL,
  `date_end` int(11) DEFAULT NULL,
  `geoip_country_code` varchar(22) DEFAULT NULL,
  `isp` varchar(255) DEFAULT NULL,
  `external_device` varchar(255) DEFAULT NULL,
  `divergence` float DEFAULT 0,
  `hls_last_read` int(11) DEFAULT NULL,
  `hls_end` tinyint(4) DEFAULT 0,
  `fingerprinting` tinyint(4) DEFAULT 0,
  `uuid` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`activity_id`),
  KEY `user_agent` (`user_agent`),
  KEY `user_ip` (`user_ip`),
  KEY `container` (`container`),
  KEY `pid` (`pid`),
  KEY `geoip_country_code` (`geoip_country_code`),
  KEY `user_id` (`user_id`),
  KEY `stream_id` (`stream_id`),
  KEY `server_id` (`server_id`),
  KEY `date_start` (`date_start`),
  KEY `date_end` (`date_end`),
  KEY `hls_end` (`hls_end`),
  KEY `fingerprinting` (`fingerprinting`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_flood`
--

CREATE TABLE IF NOT EXISTS `login_flood` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL DEFAULT '',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `dateadded` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `data` mediumtext NOT NULL,
  `login_ip` varchar(255) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mag_claims`
--

CREATE TABLE IF NOT EXISTS `mag_claims` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mag_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `real_type` varchar(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mag_id` (`mag_id`),
  KEY `stream_id` (`stream_id`),
  KEY `real_type` (`real_type`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mag_devices`
--

CREATE TABLE IF NOT EXISTS `mag_devices` (
  `mag_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bright` int(10) NOT NULL DEFAULT 200,
  `contrast` int(10) NOT NULL DEFAULT 127,
  `saturation` int(10) NOT NULL DEFAULT 127,
  `aspect` mediumtext NOT NULL,
  `video_out` varchar(20) NOT NULL DEFAULT 'rca',
  `volume` int(5) NOT NULL DEFAULT 50,
  `playback_buffer_bytes` int(50) NOT NULL DEFAULT 0,
  `playback_buffer_size` int(50) NOT NULL DEFAULT 0,
  `audio_out` int(5) NOT NULL DEFAULT 1,
  `mac` varchar(50) NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `ls` varchar(20) DEFAULT NULL,
  `ver` varchar(300) DEFAULT NULL,
  `lang` varchar(50) DEFAULT NULL,
  `locale` varchar(30) NOT NULL DEFAULT 'en_GB.utf8',
  `city_id` int(11) DEFAULT 0,
  `hd` int(10) NOT NULL DEFAULT 1,
  `main_notify` int(5) NOT NULL DEFAULT 1,
  `fav_itv_on` int(5) NOT NULL DEFAULT 0,
  `now_playing_start` int(50) DEFAULT NULL,
  `now_playing_type` int(11) NOT NULL DEFAULT 0,
  `now_playing_content` varchar(50) DEFAULT NULL,
  `time_last_play_tv` int(50) DEFAULT NULL,
  `time_last_play_video` int(50) DEFAULT NULL,
  `hd_content` int(11) NOT NULL DEFAULT 1,
  `image_version` varchar(350) DEFAULT NULL,
  `last_change_status` int(11) DEFAULT NULL,
  `last_start` int(11) DEFAULT NULL,
  `last_active` int(11) DEFAULT NULL,
  `keep_alive` int(11) DEFAULT NULL,
  `playback_limit` int(11) NOT NULL DEFAULT 3,
  `screensaver_delay` int(11) NOT NULL DEFAULT 10,
  `stb_type` varchar(20) NOT NULL,
  `sn` varchar(255) DEFAULT NULL,
  `last_watchdog` int(50) DEFAULT NULL,
  `created` int(11) NOT NULL,
  `country` varchar(5) DEFAULT NULL,
  `plasma_saving` int(11) NOT NULL DEFAULT 0,
  `ts_enabled` int(11) DEFAULT 0,
  `ts_enable_icon` int(11) NOT NULL DEFAULT 1,
  `ts_path` varchar(35) DEFAULT NULL,
  `ts_max_length` int(11) NOT NULL DEFAULT 3600,
  `ts_buffer_use` varchar(15) NOT NULL DEFAULT 'cyclic',
  `ts_action_on_exit` varchar(20) NOT NULL DEFAULT 'no_save',
  `ts_delay` varchar(20) NOT NULL DEFAULT 'on_pause',
  `video_clock` varchar(10) NOT NULL DEFAULT 'Off',
  `rtsp_type` int(11) NOT NULL DEFAULT 4,
  `rtsp_flags` int(11) NOT NULL DEFAULT 0,
  `stb_lang` varchar(15) NOT NULL DEFAULT 'en',
  `display_menu_after_loading` int(11) NOT NULL DEFAULT 1,
  `record_max_length` int(11) NOT NULL DEFAULT 180,
  `plasma_saving_timeout` int(11) NOT NULL DEFAULT 600,
  `now_playing_link_id` int(11) DEFAULT NULL,
  `now_playing_streamer_id` int(11) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `device_id2` varchar(255) DEFAULT NULL,
  `hw_version` varchar(255) DEFAULT NULL,
  `parent_password` varchar(20) NOT NULL DEFAULT '0000',
  `spdif_mode` int(11) NOT NULL DEFAULT 1,
  `show_after_loading` varchar(60) NOT NULL DEFAULT 'main_menu',
  `play_in_preview_by_ok` int(11) NOT NULL DEFAULT 1,
  `hdmi_event_reaction` int(11) NOT NULL DEFAULT 1,
  `mag_player` varchar(20) DEFAULT 'ffmpeg',
  `play_in_preview_only_by_ok` varchar(10) NOT NULL DEFAULT 'true',
  `watchdog_timeout` int(11) NOT NULL,
  `fav_channels` mediumtext NOT NULL,
  `tv_archive_continued` mediumtext NOT NULL,
  `tv_channel_default_aspect` varchar(255) NOT NULL DEFAULT 'fit',
  `last_itv_id` int(11) NOT NULL DEFAULT 0,
  `units` varchar(20) DEFAULT 'metric',
  `token` varchar(32) DEFAULT '',
  `lock_device` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mag_id`),
  KEY `user_id` (`user_id`),
  KEY `mac` (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mag_events`
--

CREATE TABLE IF NOT EXISTS `mag_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) NOT NULL DEFAULT 0,
  `mag_device_id` int(11) NOT NULL,
  `event` varchar(20) NOT NULL,
  `need_confirm` tinyint(3) NOT NULL DEFAULT 0,
  `msg` mediumtext NOT NULL,
  `reboot_after_ok` tinyint(3) NOT NULL DEFAULT 0,
  `auto_hide_timeout` tinyint(3) DEFAULT 0,
  `send_time` int(50) NOT NULL,
  `additional_services_on` tinyint(3) NOT NULL DEFAULT 1,
  `anec` tinyint(3) NOT NULL DEFAULT 0,
  `vclub` tinyint(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `mag_device_id` (`mag_device_id`),
  KEY `event` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mag_logs`
--

CREATE TABLE IF NOT EXISTS `mag_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mag_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mag_id` (`mag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_groups`
--

CREATE TABLE IF NOT EXISTS `member_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` mediumtext NOT NULL,
  `group_color` varchar(7) NOT NULL DEFAULT '#000000',
  `is_banned` tinyint(4) NOT NULL DEFAULT 0,
  `is_admin` tinyint(4) NOT NULL DEFAULT 0,
  `is_reseller` tinyint(4) NOT NULL,
  `total_allowed_gen_trials` int(11) NOT NULL DEFAULT 0,
  `total_allowed_gen_in` varchar(255) NOT NULL,
  `delete_users` tinyint(4) NOT NULL DEFAULT 0,
  `allowed_pages` text NOT NULL,
  `can_delete` tinyint(4) NOT NULL DEFAULT 1,
  `reseller_force_server` tinyint(4) NOT NULL DEFAULT 0,
  `create_sub_resellers_price` float NOT NULL DEFAULT 0,
  `create_sub_resellers` tinyint(4) NOT NULL DEFAULT 0,
  `alter_packages_ids` tinyint(4) NOT NULL DEFAULT 0,
  `alter_packages_prices` tinyint(4) NOT NULL DEFAULT 0,
  `reseller_client_connection_logs` tinyint(4) NOT NULL DEFAULT 0,
  `reseller_assign_pass` tinyint(4) NOT NULL DEFAULT 0,
  `allow_change_pass` tinyint(4) NOT NULL DEFAULT 0,
  `allow_import` tinyint(4) NOT NULL DEFAULT 0,
  `allow_export` tinyint(4) NOT NULL DEFAULT 0,
  `reseller_trial_credit_allow` int(11) NOT NULL DEFAULT 0,
  `edit_mac` tinyint(4) NOT NULL DEFAULT 0,
  `edit_isplock` tinyint(4) NOT NULL DEFAULT 0,
  `reset_stb_data` tinyint(4) NOT NULL DEFAULT 0,
  `reseller_bonus_package_inc` tinyint(4) NOT NULL DEFAULT 0,
  `allow_download` tinyint(4) NOT NULL DEFAULT 1,
  `minimum_trial_credits` int(16) NOT NULL DEFAULT 0,
  `reseller_can_select_bouquets` int(16) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`),
  KEY `is_admin` (`is_admin`),
  KEY `is_banned` (`is_banned`),
  KEY `is_reseller` (`is_reseller`),
  KEY `can_delete` (`can_delete`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `member_groups`
--

INSERT INTO `member_groups` (`group_id`, `group_name`, `group_color`, `is_banned`, `is_admin`, `is_reseller`, `total_allowed_gen_trials`, `total_allowed_gen_in`, `delete_users`, `allowed_pages`, `can_delete`, `reseller_force_server`, `create_sub_resellers_price`, `create_sub_resellers`, `alter_packages_ids`, `alter_packages_prices`, `reseller_client_connection_logs`, `reseller_assign_pass`, `allow_change_pass`, `allow_import`, `allow_export`, `reseller_trial_credit_allow`, `edit_mac`, `edit_isplock`, `reset_stb_data`, `reseller_bonus_package_inc`, `allow_download`, `minimum_trial_credits`, `reseller_can_select_bouquets`) VALUES
(1, 'Channel Admin', '#FF0000', 0, 1, 0, 0, 'day', 0, '[\"add_stream\",\"edit_stream\",\"streams\",\"archive\",\"add_movie\",\"edit_movie\",\"import_movies\",\"filexplorer\",\"movies\",\"add_series\",\"series_list\",\"edit_series\",\"add_episode\",\"edit_episode\",\"import_episodes\",\"series\",\"add_radio\",\"edit_radio\",\"radio\",\"create_channel\",\"edit_cchannel\",\"manage_cchannels\",\"mass_sedits\",\"mass_sedits_vod\",\"epg\",\"epg_edit\",\"tprofiles\",\"categories\",\"edit_cat\",\"stream_tools\",\"add_bouquet\",\"edit_bouquet\",\"bouquets\"]', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(2, 'Registered Users', '#66FF66', 0, 0, 0, 0, '', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(3, 'Banned', '#194775', 1, 0, 0, 0, '', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(4, 'Resellers', '#FF9933', 0, 0, 1, 100000, 'month', 0, '[]', 0, 0, 0, 1, 1, 0, 1, 1, 1, 1, 0, 1, 1, 1, 1, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `movie_containers`
--

CREATE TABLE IF NOT EXISTS `movie_containers` (
  `container_id` int(11) NOT NULL AUTO_INCREMENT,
  `container_extension` varchar(255) NOT NULL,
  `container_header` varchar(255) NOT NULL,
  PRIMARY KEY (`container_id`),
  KEY `container_extension` (`container_extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE IF NOT EXISTS `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_name` varchar(255) NOT NULL,
  `is_trial` tinyint(4) NOT NULL,
  `is_official` tinyint(4) NOT NULL,
  `trial_credits` float NOT NULL,
  `official_credits` float NOT NULL,
  `trial_duration` int(11) NOT NULL,
  `trial_duration_in` varchar(255) NOT NULL,
  `official_duration` int(11) NOT NULL,
  `official_duration_in` varchar(255) NOT NULL,
  `groups` mediumtext NOT NULL,
  `bouquets` mediumtext NOT NULL,
  `can_gen_mag` tinyint(4) NOT NULL DEFAULT 0,
  `only_mag` tinyint(4) NOT NULL DEFAULT 0,
  `output_formats` mediumtext NOT NULL,
  `is_isplock` tinyint(4) NOT NULL DEFAULT 0,
  `max_connections` int(11) NOT NULL DEFAULT 1,
  `is_restreamer` tinyint(4) NOT NULL DEFAULT 0,
  `force_server_id` int(11) NOT NULL DEFAULT 0,
  `can_gen_e2` tinyint(4) NOT NULL DEFAULT 0,
  `only_e2` tinyint(4) NOT NULL DEFAULT 0,
  `forced_country` varchar(2) NOT NULL,
  `lock_device` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `is_trial` (`is_trial`),
  KEY `is_official` (`is_official`),
  KEY `can_gen_mag` (`can_gen_mag`),
  KEY `can_gen_e2` (`can_gen_e2`),
  KEY `only_e2` (`only_e2`),
  KEY `only_mag` (`only_mag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panel_logs`
--

CREATE TABLE IF NOT EXISTS `panel_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'pdo',
  `log_message` longtext DEFAULT NULL,
  `log_extra` longtext DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `unique` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reg_userlog`
--

CREATE TABLE IF NOT EXISTS `reg_userlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `username` mediumtext NOT NULL,
  `password` mediumtext NOT NULL,
  `date` int(30) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reg_users`
--

CREATE TABLE IF NOT EXISTS `reg_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `date_registered` int(11) NOT NULL,
  `verify_key` mediumtext DEFAULT NULL,
  `last_login` int(11) DEFAULT NULL,
  `member_group_id` int(11) NOT NULL,
  `verified` int(11) NOT NULL DEFAULT 0,
  `credits` float NOT NULL DEFAULT 0,
  `notes` mediumtext DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 1,
  `default_lang` mediumtext NOT NULL,
  `reseller_dns` text NOT NULL,
  `owner_id` int(11) NOT NULL DEFAULT 0,
  `override_packages` text DEFAULT NULL,
  `google_2fa_sec` varchar(50) NOT NULL,
  `dark_mode` int(1) NOT NULL DEFAULT 0,
  `sidebar` int(1) NOT NULL DEFAULT 0,
  `expanded_sidebar` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `member_group_id` (`member_group_id`),
  KEY `username` (`username`),
  KEY `password` (`password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reseller_imex`
--

CREATE TABLE IF NOT EXISTS `reseller_imex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_id` int(11) NOT NULL,
  `header` longtext NOT NULL,
  `data` longtext NOT NULL,
  `accepted` tinyint(4) NOT NULL DEFAULT 0,
  `finished` tinyint(4) NOT NULL DEFAULT 0,
  `bouquet_ids` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reg_id` (`reg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE IF NOT EXISTS `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `added` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rtmp_ips`
--

CREATE TABLE IF NOT EXISTS `rtmp_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `notes` mediumtext,
  `push` tinyint(1) DEFAULT NULL,
  `pull` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `series`
--

CREATE TABLE IF NOT EXISTS `series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `cover` varchar(255) NOT NULL,
  `cover_big` varchar(255) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `plot` text NOT NULL,
  `cast` text NOT NULL,
  `rating` int(11) NOT NULL,
  `director` varchar(255) NOT NULL,
  `releaseDate` varchar(255) NOT NULL,
  `last_modified` int(11) NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `seasons` mediumtext NOT NULL,
  `episode_run_time` int(11) NOT NULL DEFAULT 0,
  `backdrop_path` text NOT NULL,
  `youtube_trailer` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_modified` (`last_modified`),
  KEY `tmdb_id` (`tmdb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `series_episodes`
--

CREATE TABLE IF NOT EXISTS `series_episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_num` int(11) NOT NULL,
  `series_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `season_num` (`season_num`),
  KEY `series_id` (`series_id`),
  KEY `stream_id` (`stream_id`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `server_activity`
--

CREATE TABLE IF NOT EXISTS `server_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_server_id` int(11) NOT NULL,
  `dest_server_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `bandwidth` int(11) NOT NULL DEFAULT 0,
  `date_start` int(11) NOT NULL,
  `date_end` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `source_server_id` (`source_server_id`),
  KEY `dest_server_id` (`dest_server_id`),
  KEY `stream_id` (`stream_id`),
  KEY `pid` (`pid`),
  KEY `date_end` (`date_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servers_stats`
--

CREATE TABLE IF NOT EXISTS `servers_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) DEFAULT '0',
  `connections` int(11) DEFAULT '0',
  `streams` int(11) DEFAULT '0',
  `users` int(11) DEFAULT '0',
  `cpu` float DEFAULT '0',
  `cpu_cores` int(11) DEFAULT '0',
  `cpu_avg` float DEFAULT '0',
  `total_mem` int(11) DEFAULT '0',
  `total_mem_free` int(11) DEFAULT '0',
  `total_mem_used` int(11) DEFAULT '0',
  `total_mem_used_percent` float DEFAULT '0',
  `total_disk_space` bigint(20) DEFAULT '0',
  `uptime` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `total_running_streams` int(11) DEFAULT '0',
  `bytes_sent` bigint(20) DEFAULT '0',
  `bytes_received` bigint(20) DEFAULT '0',
  `bytes_sent_total` bigint(128) DEFAULT '0',
  `bytes_received_total` bigint(128) DEFAULT '0',
  `cpu_load_average` float DEFAULT '0',
  `gpu_info` mediumtext COLLATE utf8_unicode_ci,
  `iostat_info` mediumtext COLLATE utf8_unicode_ci,
  `time` int(16) DEFAULT '0',
  `total_users` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` varchar(4096) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
('1','id','1'),
('2','bouquet_name','Xtream Codes'),
('3','live_streaming_pass',''),
('4','email_verify_sub','Verify Registration @ {SERVER_NAME}'),
('5','email_verify_cont','Hello,<p><br /></p><p>Please Click at the following URL to activate your account {VERIFY_LINK}</p><p><br /></p><p>{SERVER_NAME} Team</p><p>Thank you</p>'),
('6','email_forgot_sub','Forgot Password @ {SERVER_NAME}'),
('7','email_forgot_cont','Hello,<p><br /></p><p>Someone requested new password @ {SERVER_NAME}. To verify this request please click at the following link: {FORGOT_LINK}<br /></p><p><br /></p><p>{SERVER_NAME} Team</p><p>Thank you</p>'),
('8','mail_from','support@website.com'),
('9','smtp_host','mail.website.com'),
('10','smtp_port','0'),
('11','min_password','5'),
('12','username_strlen','15'),
('13','username_alpha','0'),
('14','allow_multiple_accs','1'),
('15','allow_registrations','0'),
('16','server_name','Xtream Codes'),
('17','smtp_username','support@website.com'),
('18','smtp_password',''),
('19','email_new_pass_sub','Your New Password @ {SERVER_NAME}'),
('20','logo_url',''),
('21','email_new_pass_cont','Hello,<p><br /></p><p>Your New Password is: {NEW_PASSWORD}<br /></p><p><br /></p><p>{SERVER_NAME} Team</p><p>Thank you</p>'),
('22','smtp_from_name','Support'),
('23','confirmation_email','0'),
('24','smtp_encryption','no'),
('26','copyrights_removed','0'),
('27','copyrights_text','Xtream Codes'),
('28','default_timezone','Europe/London'),
('29','default_locale','en_GB.utf8'),
('30','allowed_stb_types','[\"MAG200\",\"MAG245\",\"MAG245D\",\"MAG250\",\"MAG254\",\"MAG255\",\"MAG256\",\"MAG257\",\"MAG260\",\"MAG270\",\"MAG275\",\"MAG322\",\"MAG323\",\"MAG324\",\"MAG325\",\"MAG349\",\"MAG350\",\"MAG351\",\"MAG352\",\"AuraHD\",\"AuraHD2\",\"AuraHD3\",\"AuraHD4\",\"AuraHD5\",\"AuraHD6\",\"AuraHD7\",\"AuraHD8\",\"AuraHD9\",\"WR320\"]'),
('31','client_prebuffer','15'),
('32', 'mag_disable_ssl', '0'),
('33','stream_max_analyze','5000000'),
('34','show_not_on_air_video','0'),
('35','not_on_air_video_path',''),
('36','show_banned_video','0'),
('37','banned_video_path',''),
('38','show_expired_video','0'),
('39','expired_video_path',''),
('40','mag_container','ts'),
('41','probesize','5000000'),
('42','allowed_ips_admin',''),
('43','block_svp','0'),
('44','allow_countries','[\"ALL\"]'),
('45','user_auto_kick_hours','3'),
('46','show_in_red_online','2'),
('47','disallow_empty_user_agents','0'),
('48','show_all_category_mag','1'),
('49','default_lang','English'),
('50','autobackup_status','0'),
('51','autobackup_pass',''),
('52','flood_limit','40'),
('53','flood_ips_exclude',''),
('54','reshare_deny_addon','0'),
('55','restart_http','0'),
('56','css_layout','light'),
('57','flood_seconds','2'),
('58','flood_max_attempts','3'),
('59','flood_apply_clients','0'),
('60','flood_apply_restreamers','0'),
('61','backup_source_all','0'),
('62','flood_get_block','0'),
('63','portal_block','0'),
('64','streaming_block','0'),
('65','stream_start_delay','0'),
('66','hash_lb','0'),
('67','vod_bitrate_plus','200'),
('68','read_buffer_size','8192'),
('69','tv_channel_default_aspect','fit'),
('70','playback_limit','3'),
('71','show_tv_channel_logo','1'),
('72','show_channel_logo_in_preview','1'),
('73','enable_connection_problem_indication','1'),
('74','enable_pseudo_hls','1'),
('75','vod_limit_at','0'),
('76','client_area_plugin','flow'),
('77','persistent_connections','1'),
('78','record_max_length','180'),
('79','total_records_length','600'),
('80','max_local_recordings','10'),
('81','allowed_stb_types_for_local_recording','[\"MAG255\",\"MAG256\",\"MAG257\"]'),
('82','allowed_stb_types_rec',''),
('83','show_captcha','1'),
('84','dynamic_timezone','1'),
('85','stalker_theme','default'),
('86','rtmp_random','1'),
('87','api_ips',''),
('88', 'disallow_2nd_ip_max', '1'),
('89','use_buffer','0'),
('90','restreamer_prebuffer','0'),
('91','audio_restart_loss','0'),
('92','stalker_lock_images',''),
('93','channel_number_type','bouquet'),
('94','stb_change_pass','1'),
('95','enable_debug_stalker','0'),
('96','online_capacity_interval','10'),
('97','always_enabled_subtitles','0'),
('98','test_download_url',''),
('99','xc_support_allow','0'),
('100','e2_arm7a',''),
('101','e2_mipsel',''),
('102','e2_mips32el',''),
('103','e2_sh4',''),
('104','e2_arm',''),
('105','api_pass',''),
('106','message_of_day','Welcome to Xtream Codes Reborn'),
('108','mysql_remote_sec','0'),
('109','enable_isp_lock','0'),
('110','show_isps','1'),
('111','userpanel_mainpage','[]'),
('112','save_closed_connection','1'),
('113','client_logs_save','1'),
('115','case_sensitive_line','1'),
('116','county_override_1st','0'),
('117','disallow_2nd_ip_con','0'),
('118', 'ip_subnet_match', '0'),
('119','split_by','conn'),
('120','use_mdomain_in_lists','0'),
('121','use_https',''),
('122','priority_backup','0'),
('123','use_buffer_table','0'),
('124','tmdb_api_key',''),
('125','toggle_menu','0'),
('126','mobile_apps','0'),
('127','stalker_container_priority','[\"mp4\",\"mkv\",\"avi\"]'),
('128','gen_container_priority','[\"mp4\",\"mkv\",\"avi\"]'),
('129','tmdb_default','en'),
('130','series_custom_name','0'),
('131','mag_security','1'),
('132','logo_url_sidebar',''),
('133','page_mannuals','<p><br /></p>'),
('134','debug_show_errors','0'),
('135', 'monitor_connection_status', '1'),
('136', 'segment_wait_time', '20'),
('137', 'disable_ts', '0'),
('138', 'disable_ts_allow_restream', '0'),
('139', 'disable_hls', '0'),
('140', 'disable_hls_allow_restream', '0'),
('141', 'encrypt_hls', '0'),
('142', 'encrypt_playlist', '0'),
('143', 'encrypt_playlist_restreamer', '0'),
('144', 'restrict_playlists', '1'),
('145', 'create_expiration', '5'),
('146', 'stop_failures', '3'),
('147', 'on_demand_instant_off', '0'),
('148', 'on_demand_failure_exit', '0'),
('149', 'on_demand_wait_time', '20'),
('150', 'ondemand_balance_equal', '0'),
('151', 'seg_time', '10'),
('152', 'seg_list_size', '6'),
('153', 'seg_delete_threshold', '4'),
('154', 'ffmpeg_warnings', '1'),
('155', 'ignore_keyframes', '0'),
('156', 'probe_extra_wait', '7'),
('157', 'stream_fail_sleep', '10'),
('158', 'fps_check_type', '1'),
('159', 'fps_delay', '600'),
('160', 'kill_rogue_ffmpeg', '1'),
('161', 'segment_type', '1'),
('162', 'vod_limit_perc', '150'),
('163', 'enable_cache', '0'),
('164', 'last_cache', '0'),
('165', 'last_cache_taken', '0'),
('166', 'cache_thread_count', '4'),
('167', 'cache_changes', '1'),
('168', 'restrict_same_ip', '1'),
('169', 'api_container', 'hls'),
('170', 'allow_cdn_access', '0'),
('171', 'detect_restream_block_user', '0'),
('172', 'block_streaming_servers', '0'),
('173', 'block_proxies', '0'),
('174', 'ignore_invalid_users', '0'),
('175', 'disable_mag_token', '0'),
('176', 'ffmpeg_cpu', ''),
('177', 'dts_legacy_ffmpeg', '0'),
('178', 'read_native_hls', '1'),
('179', 'player_allow_hevc', '0'),
('180', 'bruteforce_mac_attempts', '5'),
('181', 'bruteforce_username_attempts', '10'),
('182', 'bruteforce_frequency', '300'),
('183', 'restart_php_fpm', 1),
('184', 'redis_password', ""),
('185', 'redis_handler', 0),
('186', 'cache_playlists', 0),
('187', 'php_loopback', 1),
('188', 'pass_length', 8),
('189', 'disable_trial', 0),
('190', 'automatic_backups', 'off'),
('191', 'cc_time', 0),
('192', 'recaptcha_v2_secret_key', ''),
('193', 'recaptcha_v2_site_key', ''),
('194', 'recaptcha_enable', '0');
-- --------------------------------------------------------

--
-- Table structure for table `signals`
--

CREATE TABLE IF NOT EXISTS `signals` (
  `signal_id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `rtmp` tinyint(4) DEFAULT 0,
  `time` int(11) DEFAULT NULL,
  `custom_data` mediumtext DEFAULT NULL,
  `cache` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`signal_id`),
  KEY `server_id` (`server_id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streaming_servers`
--

CREATE TABLE IF NOT EXISTS `streaming_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(255) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `server_ip` varchar(255) DEFAULT NULL,
  `vpn_ip` varchar(255) NOT NULL,
  `ssh_password` mediumtext DEFAULT NULL,
  `ssh_port` int(11) DEFAULT NULL,
  `http_broadcast_port` int(11) NOT NULL,
  `total_clients` int(11) NOT NULL DEFAULT 0,
  `system_os` varchar(255) DEFAULT NULL,
  `network_interface` varchar(255) NOT NULL,
  `latency` float NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT -1,
  `enable_geoip` int(11) NOT NULL DEFAULT 0,
  `geoip_countries` mediumtext NOT NULL,
  `last_check_ago` int(11) NOT NULL DEFAULT 0,
  `can_delete` tinyint(4) NOT NULL DEFAULT 1,
  `server_hardware` text NOT NULL,
  `total_services` int(11) NOT NULL DEFAULT 3,
  `persistent_connections` tinyint(4) NOT NULL DEFAULT 0,
  `rtmp_port` int(11) NOT NULL DEFAULT 8001,
  `geoip_type` varchar(13) NOT NULL DEFAULT 'low_priority',
  `isp_names` mediumtext NOT NULL,
  `isp_type` varchar(13) NOT NULL DEFAULT 'low_priority',
  `enable_isp` tinyint(4) NOT NULL DEFAULT 0,
  `http_ports_add` text NOT NULL,
  `network_guaranteed_speed` int(11) NOT NULL DEFAULT 0,
  `https_broadcast_port` int(11) NOT NULL DEFAULT 25463,
  `https_ports_add` text NOT NULL,
  `whitelist_ips` text NOT NULL,
  `watchdog_data` mediumtext NOT NULL,
  `timeshift_only` tinyint(4) NOT NULL DEFAULT 0,
  `http_isp_port` int(11) NOT NULL DEFAULT 8805,
  `time_offset` int(11) DEFAULT 0,
  `script_version` varchar(50) DEFAULT NULL,
  `is_main` int(16) DEFAULT 0,
  `php_pids` longtext DEFAULT NULL,
  `remote_status` tinyint(1) DEFAULT 1,
  `last_status` tinyint(4) DEFAULT 1,
  `interfaces` mediumtext COLLATE utf8_unicode_ci,
  `ping` int(11) DEFAULT '0',
  `video_devices` mediumtext COLLATE utf8_unicode_ci,
  `sysctl` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `audio_devices` mediumtext COLLATE utf8_unicode_ci,
  `gpu_info` mediumtext COLLATE utf8_unicode_ci,
  `limit_requests` INT(11) NULL DEFAULT '0',
  `enable_gzip` TINYINT(1) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `server_ip` (`server_ip`,`http_broadcast_port`),
  KEY `total_clients` (`total_clients`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `streaming_servers`
--

INSERT INTO `streaming_servers` (`id`, `server_name`, `domain_name`, `server_ip`, `vpn_ip`, `ssh_password`, `ssh_port`, `http_broadcast_port`, `total_clients`, `system_os`, `network_interface`, `latency`, `status`, `enable_geoip`, `geoip_countries`, `last_check_ago`, `can_delete`, `server_hardware`, `total_services`, `persistent_connections`, `rtmp_port`, `geoip_type`, `isp_names`, `isp_type`, `enable_isp`, `http_ports_add`, `network_guaranteed_speed`, `https_broadcast_port`, `https_ports_add`, `whitelist_ips`, `watchdog_data`, `timeshift_only`, `http_isp_port`, `time_offset`, `script_version`, `is_main`, `php_pids`, `remote_status`, `last_status`, `interfaces`, `ping`, `video_devices`, `sysctl`, `audio_devices`, `gpu_info`, `limit_requests`, `enable_gzip`) VALUES
(1, 'Main Server', '', '127.0.0.1', '', NULL, NULL, 25461, 1000, '', '', 0, 1, 0, '[]', 0, 0, '', 3, 0, 25462, 'low_priority', '[]', 'low_priority', 0, '', 0, 25463, '', '', '', 0, 8805, 0, 'NULL', 1, '', 1, 1, '', 0, "", "", "", "", "0", "0");

-- --------------------------------------------------------

--
-- Table structure for table `streams`
--

CREATE TABLE IF NOT EXISTS `streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `category_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `stream_display_name` mediumtext NOT NULL,
  `stream_source` mediumtext DEFAULT NULL,
  `stream_icon` mediumtext NOT NULL,
  `notes` mediumtext DEFAULT NULL,
  `created_channel_location` int(11) DEFAULT NULL,
  `enable_transcode` tinyint(4) NOT NULL DEFAULT 0,
  `transcode_attributes` mediumtext NOT NULL,
  `custom_ffmpeg` mediumtext NOT NULL,
  `movie_properties` mediumtext DEFAULT NULL,
  `movie_subtitles` mediumtext NOT NULL,
  `read_native` tinyint(4) NOT NULL DEFAULT 1,
  `target_container` text DEFAULT NULL,
  `stream_all` tinyint(4) NOT NULL DEFAULT 0,
  `remove_subtitles` tinyint(4) NOT NULL DEFAULT 0,
  `custom_sid` varchar(150) DEFAULT NULL,
  `epg_id` int(11) DEFAULT NULL,
  `channel_id` varchar(255) DEFAULT NULL,
  `epg_lang` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `auto_restart` text NOT NULL,
  `transcode_profile_id` int(11) NOT NULL DEFAULT 0,
  `pids_create_channel` mediumtext NOT NULL,
  `cchannel_rsources` mediumtext NOT NULL,
  `gen_timestamps` tinyint(4) NOT NULL DEFAULT 1,
  `added` int(11) NOT NULL,
  `series_no` int(11) NOT NULL DEFAULT 0,
  `direct_source` tinyint(4) NOT NULL DEFAULT 0,
  `tv_archive_duration` int(11) NOT NULL DEFAULT 0,
  `tv_archive_server_id` int(11) NOT NULL DEFAULT 0,
  `tv_archive_pid` int(11) NOT NULL DEFAULT 0,
  `movie_symlink` tinyint(4) NOT NULL DEFAULT 0,
  `redirect_stream` tinyint(4) NOT NULL DEFAULT 0,
  `rtmp_output` tinyint(4) NOT NULL DEFAULT 0,
  `number` int(11) NOT NULL,
  `allow_record` tinyint(4) NOT NULL DEFAULT 0,
  `probesize_ondemand` int(11) NOT NULL DEFAULT 128000,
  `custom_map` text NOT NULL,
  `external_push` mediumtext NOT NULL,
  `delay_minutes` int(11) NOT NULL DEFAULT 0,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fps_restart` tinyint(1) DEFAULT '0',
  `vframes_server_id` int(11) DEFAULT '0',
  `vframes_pid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `created_channel_location` (`created_channel_location`),
  KEY `enable_transcode` (`enable_transcode`),
  KEY `read_native` (`read_native`),
  KEY `epg_id` (`epg_id`),
  KEY `channel_id` (`channel_id`),
  KEY `transcode_profile_id` (`transcode_profile_id`),
  KEY `order` (`order`),
  KEY `direct_source` (`direct_source`),
  KEY `rtmp_output` (`rtmp_output`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams_arguments`
--

CREATE TABLE IF NOT EXISTS `streams_arguments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `argument_cat` varchar(255) NOT NULL,
  `argument_name` varchar(255) NOT NULL,
  `argument_description` mediumtext NOT NULL,
  `argument_wprotocol` varchar(255) DEFAULT NULL,
  `argument_key` varchar(255) NOT NULL,
  `argument_cmd` varchar(255) DEFAULT NULL,
  `argument_type` varchar(255) NOT NULL,
  `argument_default_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `streams_arguments`
--

INSERT INTO `streams_arguments` (`id`, `argument_cat`, `argument_name`, `argument_description`, `argument_wprotocol`, `argument_key`, `argument_cmd`, `argument_type`, `argument_default_value`) VALUES
(1, 'fetch', 'User Agent', 'Set a Custom User Agent', 'http', 'user_agent', '-user_agent \"%s\"', 'text', 'Xtream-Codes IPTV Panel Pro'),
(2, 'fetch', 'HTTP Proxy', 'Set an HTTP Proxy in this format: ip:port', 'http', 'proxy', '-http_proxy \"%s\"', 'text', NULL),
(3, 'transcode', 'Average Video Bit Rate', 'With this you can change the bitrate of the target video. It is very useful in case you want your video to be playable on slow internet connections', NULL, 'bitrate', '-b:v %dk', 'text', NULL),
(4, 'transcode', 'Average Audio Bitrate', 'Change Audio Bitrate', NULL, 'audio_bitrate', '-b:a %dk', 'text', NULL),
(5, 'transcode', 'Minimum Bitrate Tolerance', '-minrate FFmpeg argument. Specify the minimum bitrate tolerance here. Specify in kbps. Enter INT number.', NULL, 'minimum_bitrate', '-minrate %dk', 'text', NULL),
(6, 'transcode', 'Maximum Bitrate Tolerance', '-maxrate FFmpeg argument. Specify the maximum bitrate tolerance here.Specify in kbps. Enter INT number. ', NULL, 'maximum_bitrate', '-maxrate %dk', 'text', NULL),
(7, 'transcode', 'Buffer Size', '-bufsize is the rate control buffer. Basically it is assumed that the receiver/end player will buffer that much data so its ok to fluctuate within that much. Specify in kbps. Enter INT number.', NULL, 'bufsize', '-bufsize %dk', 'text', NULL),
(8, 'transcode', 'CRF Value', 'The range of the quantizer scale is 0-51: where 0 is lossless, 23 is default, and 51 is worst possible. A lower value is a higher quality and a subjectively sane range is 18-28. Consider 18 to be visually lossless or nearly so: it should look the same or ', NULL, 'crf', '-crf %d', 'text', NULL),
(9, 'transcode', 'Scaling', 'Change the Width & Height of the target Video. (Eg. 320:240 ) .  If we\'d like to keep the aspect ratio, we need to specify only one component, either width or height, and set the other component to -1. (eg 320:-1)', NULL, 'scaling', '-filter_complex \"scale=%s\"', 'text', NULL),
(10, 'transcode', 'Aspect', 'Change the target Video Aspect. (eg 16:9)', NULL, 'aspect', '-aspect %s', 'text', NULL),
(11, 'transcode', 'Target Video FrameRate', 'Set the frame rate', NULL, 'video_frame_rate', '-r %d', 'text', NULL),
(12, 'transcode', 'Audio Sample Rate', 'Set the Audio Sample rate in Hz', NULL, 'audio_sample_rate', '-ar %d', 'text', NULL),
(13, 'transcode', 'Audio Channels', 'Specify Audio Channels', NULL, 'audio_channels', '-ac %d', 'text', NULL),
(14, 'transcode', 'Remove Sensitive Parts (delogo filter)', 'With this filter you can remove sensitive parts in your video. You will just specifiy the x & y pixels where there is a sensitive area and the width and height that will be removed. Example Use: x=0:y=0:w=100:h=77:band=10 ', NULL, 'delogo', '-filter_complex \"delogo=%s\"', 'text', NULL),
(15, 'transcode', 'Threads', 'Specify the number of threads you want to use for the transcoding process. Entering 0 as value will make FFmpeg to choose the most optimal settings', NULL, 'threads', '-threads %d', 'text', NULL),
(16, 'transcode', 'Logo Path', 'Add your Own Logo to the stream. The logo will be placed in the upper left. Please be sure that you have selected H.264 as codec otherwise this option won\'t work. Note that adding your own logo will consume A LOT of cpu power', NULL, 'logo', '-i \"%s\" -filter_complex \"overlay\"', 'text', NULL),
(17, 'fetch', 'Cookie', 'Set an HTTP Cookie that might be useful to fetch your INPUT Source.', 'http', 'cookie', '-cookies \'%s\'', 'text', NULL),
(18, 'transcode', 'DeInterlacing Filter', 'It check pixels of previous, current and next frames to re-create the missed field by some local adaptive method (edge-directed interpolation) and uses spatial check to prevent most artifacts. ', NULL, '', '-filter_complex \"yadif\"', 'radio', '0'),
(19, 'fetch', 'Headers', 'Set Custom Headers', 'http', 'headers', '-headers \"%s\"', 'text', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `streams_options`
--

CREATE TABLE IF NOT EXISTS `streams_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `argument_id` int(11) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  KEY `argument_id` (`argument_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams_seasons`
--

CREATE TABLE IF NOT EXISTS `streams_seasons` (
  `season_id` int(11) NOT NULL AUTO_INCREMENT,
  `season_name` varchar(255) NOT NULL,
  `stream_id` int(11) NOT NULL,
  PRIMARY KEY (`season_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams_servers`
--

CREATE TABLE IF NOT EXISTS `streams_servers` (
  `server_stream_id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `to_analyze` tinyint(4) DEFAULT '0',
  `stream_status` int(11) DEFAULT '0',
  `stream_started` int(11) DEFAULT NULL,
  `stream_info` mediumtext COLLATE utf8_unicode_ci,
  `monitor_pid` int(11) DEFAULT NULL,
  `aes_pid` int(11) DEFAULT NULL,
  `current_source` mediumtext COLLATE utf8_unicode_ci,
  `bitrate` int(11) DEFAULT NULL,
  `progress_info` mediumtext COLLATE utf8_unicode_ci,
  `cc_info` mediumtext COLLATE utf8_unicode_ci,
  `on_demand` tinyint(4) DEFAULT '0',
  `delay_pid` int(11) DEFAULT NULL,
  `delay_available_at` int(11) DEFAULT NULL,
  `pids_create_channel` mediumtext COLLATE utf8_unicode_ci,
  `cchannel_rsources` mediumtext COLLATE utf8_unicode_ci,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compatible` tinyint(1) DEFAULT '0',
  `audio_codec` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_codec` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resolution` int(5) DEFAULT NULL,
  `ondemand_check` int(16) DEFAULT NULL,
  PRIMARY KEY (`server_stream_id`),
  UNIQUE KEY `stream_id_2` (`stream_id`,`server_id`),
  KEY `stream_id` (`stream_id`),
  KEY `pid` (`pid`),
  KEY `server_id` (`server_id`),
  KEY `stream_status` (`stream_status`),
  KEY `stream_started` (`stream_started`),
  KEY `parent_id` (`parent_id`),
  KEY `to_analyze` (`to_analyze`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams_types`
--

CREATE TABLE IF NOT EXISTS `streams_types` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  `type_key` varchar(255) NOT NULL,
  `type_output` varchar(255) NOT NULL,
  `live` tinyint(4) NOT NULL,
  PRIMARY KEY (`type_id`),
  KEY `type_key` (`type_key`),
  KEY `type_output` (`type_output`),
  KEY `live` (`live`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `streams_types`
--

INSERT INTO `streams_types` (`type_id`, `type_name`, `type_key`, `type_output`, `live`) VALUES
(1, 'Live Streams', 'live', 'live', 1),
(2, 'Movies', 'movie', 'movie', 0),
(3, 'Created Live Channels', 'created_live', 'live', 1),
(4, 'Radio', 'radio_streams', 'live', 1),
(5, 'TV Series', 'series', 'series', 0);

-- --------------------------------------------------------

--
-- Table structure for table `stream_categories`
--

CREATE TABLE IF NOT EXISTS `stream_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_type` varchar(255) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `cat_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category_type` (`category_type`),
  KEY `cat_order` (`cat_order`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stream_logs`
--

CREATE TABLE IF NOT EXISTS `stream_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `error` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stream_subcategories`
--

CREATE TABLE IF NOT EXISTS `stream_subcategories` (
  `sub_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `subcategory_name` varchar(255) NOT NULL,
  PRIMARY KEY (`sub_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subreseller_setup`
--

CREATE TABLE IF NOT EXISTS `subreseller_setup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reseller` int(8) NOT NULL DEFAULT 0,
  `subreseller` int(8) NOT NULL DEFAULT 0,
  `status` int(1) NOT NULL DEFAULT 1,
  `dateadded` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suspicious_logs`
--

CREATE TABLE IF NOT EXISTS `suspicious_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `data` mediumtext NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `admin_read` tinyint(4) NOT NULL,
  `user_read` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `status` (`status`),
  KEY `admin_read` (`admin_read`),
  KEY `user_read` (`user_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets_replies`
--

CREATE TABLE IF NOT EXISTS `tickets_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `admin_reply` tinyint(4) NOT NULL,
  `message` mediumtext NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tmdb_async`
--

CREATE TABLE IF NOT EXISTS `tmdb_async` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) NOT NULL DEFAULT 0,
  `stream_id` int(16) NOT NULL DEFAULT 0,
  `status` int(8) NOT NULL DEFAULT 0,
  `dateadded` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transcoding_profiles`
--

CREATE TABLE IF NOT EXISTS `transcoding_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(255) NOT NULL,
  `profile_options` mediumtext NOT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `transcoding_profiles`
--

INSERT INTO `transcoding_profiles` (`profile_id`, `profile_name`, `profile_options`) VALUES
(1, 'Standard H264 AAC', '{\"-vcodec\":\"h264\",\"-acodec\":\"aac\"}');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `exp_date` int(11) DEFAULT NULL,
  `admin_enabled` int(11) NOT NULL DEFAULT 1,
  `enabled` int(11) NOT NULL DEFAULT 1,
  `admin_notes` mediumtext NOT NULL,
  `reseller_notes` mediumtext NOT NULL,
  `bouquet` mediumtext NOT NULL,
  `max_connections` int(11) NOT NULL DEFAULT 1,
  `is_restreamer` tinyint(4) NOT NULL DEFAULT 0,
  `allowed_ips` mediumtext NOT NULL,
  `allowed_ua` mediumtext NOT NULL,
  `is_trial` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `pair_id` int(11) DEFAULT NULL,
  `is_mag` tinyint(4) NOT NULL DEFAULT 0,
  `is_e2` tinyint(4) NOT NULL DEFAULT 0,
  `force_server_id` int(11) NOT NULL DEFAULT 0,
  `is_isplock` tinyint(4) NOT NULL DEFAULT 0,
  `as_number` varchar(30) DEFAULT NULL,
  `isp_desc` mediumtext DEFAULT NULL,
  `forced_country` varchar(3) NOT NULL,
  `is_stalker` tinyint(4) NOT NULL DEFAULT 0,
  `bypass_ua` tinyint(4) NOT NULL DEFAULT 0,
  `play_token` text NOT NULL,
  `last_expiration_video` int(11) DEFAULT NULL,
  `access_token` varchar(32) DEFAULT NULL,
  `last_ip` varchar(255) DEFAULT NULL,
  `last_activity` int(11) DEFAULT NULL,
  `last_activity_array` mediumtext DEFAULT NULL,
  `allowed_outputs` mediumtext COLLATE utf8_unicode_ci,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `exp_date` (`exp_date`),
  KEY `is_restreamer` (`is_restreamer`),
  KEY `admin_enabled` (`admin_enabled`),
  KEY `enabled` (`enabled`),
  KEY `is_trial` (`is_trial`),
  KEY `created_at` (`created_at`),
  KEY `created_by` (`created_by`),
  KEY `pair_id` (`pair_id`),
  KEY `is_mag` (`is_mag`),
  KEY `username` (`username`),
  KEY `password` (`password`),
  KEY `is_e2` (`is_e2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE IF NOT EXISTS `user_activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `user_ip` varchar(39) DEFAULT NULL,
  `container` varchar(50) DEFAULT NULL,
  `date_start` int(11) DEFAULT NULL,
  `date_end` int(11) DEFAULT NULL,
  `geoip_country_code` varchar(22) DEFAULT NULL,
  `isp` varchar(255) DEFAULT NULL,
  `external_device` varchar(255) DEFAULT NULL,
  `divergence` float DEFAULT 0,
  PRIMARY KEY (`activity_id`),
  KEY `user_id` (`user_id`),
  KEY `stream_id` (`stream_id`),
  KEY `server_id` (`server_id`),
  KEY `date_end` (`date_end`),
  KEY `container` (`container`),
  KEY `geoip_country_code` (`geoip_country_code`),
  KEY `date_start` (`date_start`),
  KEY `date_start_2` (`date_start`,`date_end`),
  KEY `user_ip` (`user_ip`),
  KEY `user_agent` (`user_agent`),
  KEY `isp` (`isp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_output`
--

CREATE TABLE IF NOT EXISTS `user_output` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `access_output_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `access_output_id` (`access_output_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watch_categories`
--

CREATE TABLE IF NOT EXISTS `watch_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) NOT NULL DEFAULT 0,
  `genre_id` int(8) NOT NULL DEFAULT 0,
  `genre` varchar(64) NOT NULL DEFAULT '',
  `category_id` int(8) NOT NULL DEFAULT 0,
  `bouquets` varchar(4096) NOT NULL DEFAULT '[]',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `watch_categories`
--

INSERT INTO `watch_categories` (`id`, `type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES
(1, 1, 28, 'Action', 0, '[]'),
(2, 1, 12, 'Adventure', 0, '[]'),
(3, 1, 16, 'Animation', 0, '[]'),
(4, 1, 35, 'Comedy', 0, '[]'),
(5, 1, 80, 'Crime', 0, '[]'),
(6, 1, 99, 'Documentary', 0, '[]'),
(7, 1, 18, 'Drama', 0, '[]'),
(8, 1, 10751, 'Family', 0, '[]'),
(9, 1, 14, 'Fantasy', 0, '[]'),
(10, 1, 36, 'History', 0, '[]'),
(11, 1, 27, 'Horror', 0, '[]'),
(12, 1, 10402, 'Music', 0, '[]'),
(13, 1, 9648, 'Mystery', 0, '[]'),
(14, 1, 10749, 'Romance', 0, '[]'),
(15, 1, 878, 'Science Fiction', 0, '[]'),
(16, 1, 10770, 'TV Movie', 0, '[]'),
(17, 1, 53, 'Thriller', 0, '[]'),
(18, 1, 10752, 'War', 0, '[]'),
(19, 1, 37, 'Western', 0, '[]');

-- --------------------------------------------------------

--
-- Table structure for table `watch_folders`
--

CREATE TABLE IF NOT EXISTS `watch_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL DEFAULT '',
  `directory` varchar(2048) NOT NULL DEFAULT '',
  `server_id` int(8) NOT NULL DEFAULT 0,
  `category_id` int(8) NOT NULL DEFAULT 0,
  `bouquets` varchar(4096) NOT NULL DEFAULT '[]',
  `last_run` int(32) NOT NULL DEFAULT 0,
  `active` int(1) NOT NULL DEFAULT 1,
  `disable_tmdb` int(1) NOT NULL DEFAULT 0,
  `ignore_no_match` int(1) NOT NULL DEFAULT 0,
  `auto_subtitles` int(1) NOT NULL DEFAULT 0,
  `fb_bouquets` varchar(4096) NOT NULL DEFAULT '[]',
  `fb_category_id` int(8) NOT NULL DEFAULT 0,
  `allowed_extensions` varchar(4096) NOT NULL DEFAULT '[]',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watch_output`
--

CREATE TABLE IF NOT EXISTS `watch_output` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) NOT NULL DEFAULT 0,
  `server_id` int(8) NOT NULL DEFAULT 0,
  `filename` varchar(4096) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT 0,
  `stream_id` int(8) NOT NULL DEFAULT 0,
  `dateadded` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watch_settings`
--

CREATE TABLE IF NOT EXISTS `watch_settings` (
  `read_native` int(1) NOT NULL DEFAULT 1,
  `movie_symlink` int(1) NOT NULL DEFAULT 1,
  `auto_encode` int(1) NOT NULL DEFAULT 0,
  `transcode_profile_id` int(8) NOT NULL DEFAULT 0,
  `scan_seconds` int(8) NOT NULL DEFAULT 3600,
  `percentage_match` int(3) NOT NULL DEFAULT 80,
  `ffprobe_input` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `watch_settings`
--

INSERT INTO `watch_settings` (`read_native`, `movie_symlink`, `auto_encode`, `transcode_profile_id`, `scan_seconds`, `percentage_match`, `ffprobe_input`) VALUES
(1, 1, 0, 0, 3600, 80, 0);
COMMIT;

CREATE TABLE IF NOT EXISTS `mysql_syslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `error` longtext COLLATE utf8_unicode_ci,
  `username` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `database` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `server_id` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
