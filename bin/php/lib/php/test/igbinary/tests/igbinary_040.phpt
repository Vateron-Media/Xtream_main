--TEST--
b0rked random data test
--SKIPIF--
--FILE--
<?php

if(!extension_loaded('igbinary')) {
	dl('igbinary.' . PHP_SHLIB_SUFFIX);
}

function test() {
	$serialized = igbinary_serialize(null);
	$serialized = substr($serialized, 0, -1);

	$length = mt_rand(1, 255);
	for ($i = 0; $i < $length; ++$i) {
		$serialized .= chr(mt_rand(0, 255));
	}

	// if returned null everything is OK
	if (($unserialized = igbinary_unserialize($serialized)) === null) {
		return true;
	}

	error_reporting(E_ALL);
	// whole data is read?
	$reserialized = igbinary_serialize($unserialized);
	if ($serialized === $reserialized) {
		return true;
	}
	if (is_string($reserialized) && strlen($reserialized) < strlen($serialized) && strncmp($reserialized, $serialized, 0) === 0) {
		return true;
	}

	// igbinary should not unserialize an object for invalid data - if it does, print the unexpected value.
	echo bin2hex($serialized), "\n";
	echo bin2hex($reserialized), "\n";
	var_dump($unserialized);

	return false;
}

// Test that 100 deterministic random values don't unserialize as valid data
mt_srand(0xface);
for ($i = 0; $i < 100; ++$i) {
	error_reporting(E_ERROR | E_PARSE);
	if (!test()) break;
}
// Test that igbinary_unserialize warns if extra data is added and fails (suppressed in the above checks)
error_reporting(E_ALL);
echo "After testing 100 random values\n";
$result = igbinary_unserialize(igbinary_serialize(true) . "\x00");
// Should deliberately return null if extra data was seen
var_dump($result);
?>
--EXPECTF--
After testing 100 random values

Warning: igbinary_unserialize: received more data to unserialize than expected in %s on line 48
NULL
