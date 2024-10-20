--TEST--
Test serializing multiple reference groups to the same empty array
--SKIPIF--
<?php
if (!function_exists('json_encode')) { echo "skip requires json_encode\n"; }
?>
--FILE--
<?php

function dump($array) {
	echo count($array) . " values\n";
	foreach ($array as $k => $value) {
		echo "$k: " . json_encode($value) . "\n";
	}
}

function main() {
	$a = array();
	$b = $a;
	$c = $a;
	$value = array(&$b, $a, &$b, &$c, &$c);
	$ser = igbinary_serialize($value);
	echo bin2hex($ser) . "\n";
	$v = igbinary_unserialize($ser);
	dump($v);
	$v[0][] = 2;
	dump($v);
	$v[3][] = 3;
	dump($v);
	var_export($a);
}
main();
?>
--EXPECT--
000000021405060025140006011400060225010106032514000604250103
5 values
0: []
1: []
2: []
3: []
4: []
5 values
0: [2]
1: []
2: [2]
3: []
4: []
5 values
0: [2]
1: []
2: [2]
3: [3]
4: [3]
array (
)
