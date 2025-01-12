<?php
include "functions.php";

if (isset($_SESSION['hash'])) {
	if (!$rPermissions["is_admin"]) {
		header("Location: ./reseller");
	} else {
		header("Location: ./dashboard");
	}
} else {
	header("Location: ./login");
}
