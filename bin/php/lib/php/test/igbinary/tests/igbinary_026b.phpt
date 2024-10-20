--TEST--
Cyclic array test 2
--INI--
report_memleaks=0
--SKIPIF--
<?php
if (!extension_loaded('igbinary')) {
	echo "skip no igbinary";
}
if (PHP_MAJOR_VERSION > 7) {
	echo "skip requires php 7.x\n";
}
--FILE--
<?php

$a = array("foo" => &$b);
$b = array(1, 2, $a);

/* all three statements below should produce same output however PHP stock
 * unserialize/serialize produces different output (5.2.16). I consider this is
 * a PHP bug. - Oleg Grenrus
 */

//$k = $a;
//$k = unserialize(serialize($a));
$k = igbinary_unserialize(igbinary_serialize($a));

function check($a, $k) {
	ob_start();
	var_dump($a);
	$a_str = ob_get_clean();
	ob_start();
	var_dump($k);
	$k_str = ob_get_clean();

	if ($a_str !== $k_str) {
		echo "Output differs\n";
		echo "Expected:\n", $a_str, "\n";
		echo "Actual:\n", $k_str, "\n";
	} else {
		echo "OK\n";
	}
}

check($a, $k);

$a["foo"][2]["foo"][1] = "b";
$k["foo"][2]["foo"][1] = "b";

check($a, $k);

?>
--EXPECT--
OK
OK
