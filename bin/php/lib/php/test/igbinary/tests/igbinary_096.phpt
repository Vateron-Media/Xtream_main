--TEST--
Test unserialization creates valid current()
--INI--
display_errors=stderr
error_reporting=E_ALL
--FILE--
<?php
$arr = [
	'one' => [
		'two' => [
			'three' => [
				'four'
			],
		],
	],
];
$test = current(current(current(current($arr))));

var_dump($test);

// Note: after unserialization the current is the last array element.
$arr2 = igbinary_unserialize(igbinary_serialize($arr));
$test2 = current(current(current(current($arr2))));

var_dump($test2);
?>
--EXPECT--
string(4) "four"
string(4) "four"