<?php

class API {
	public static $ipTV_db = null;
	public static $rSettings = array();
	public static $rServers = array();
	public static $rUserInfo = array();

	public static function init($rUserID = null) {
		self::$rSettings = getSettings();
		self::$rServers = getStreamingServers();

		if (!$rUserID || isset($_SESSION['hash'])) {
			$rUserID = $_SESSION['hash'];
		}

		if ($rUserID) {
			self::$rUserInfo = getRegisteredUser($rUserID);
		}
	}
}
