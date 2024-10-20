--TEST--
Resource
--SKIPIF--
<?php
if (!extension_loaded("igbinary")) print "skip extension not loaded\n";
--FILE--
<?php

function test($type, $variable, $test) {
	$serialized = igbinary_serialize($variable);
	$unserialized = igbinary_unserialize($serialized);

	echo $type, "\n";
	echo substr(bin2hex($serialized), 8), "\n";
	echo $test || $unserialized === null ? 'OK' : 'FAIL';
	echo "\n";
}

$res = tmpfile();

test('resource', $res, false);

fclose($res);

test('resource', $res, false);

--EXPECTF--
Deprecated: igbinary_serialize(): Cannot serialize resource(stream) and resources may be converted to objects that cannot be serialized in future php releases. Serializing the value as null instead in %sigbinary_023.php on line 4
resource
00
OK

Deprecated: igbinary_serialize(): Cannot serialize resource(Unknown) and resources may be converted to objects that cannot be serialized in future php releases. Serializing the value as null instead in %sigbinary_023.php on line 4
resource
00
OK
