--TEST--
Test serializing and unserializing many duplicate strings
--FILE--
<?php

function main() {
	$arr = array();
	// Just more than 2 ** 16
	$n = (2 ** 16) + 100;
	for ($i = 0; $i < $n; $i++) {
		$s = "$i";
		$arr[] = $s;
		$arr[] = $s;
	}
	$unser = igbinary_unserialize(igbinary_serialize($arr));
	var_export($arr === $unser);
}
main();
--EXPECT--
true
