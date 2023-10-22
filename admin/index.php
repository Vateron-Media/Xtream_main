<?php
include "functions.php";

if (isset($_SESSION['hash'])) {
	if (!$rPermissions["is_admin"]) {
		header("Location: ./reseller.php");
	} else {
		header("Location: ./dashboard.php");
	}
} else {
    header("Location: ./login.php");
}
?>