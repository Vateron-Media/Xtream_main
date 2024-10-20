--TEST--
Test refusing to serialize/unserialize unserializable internal classes
--INI--
error_reporting=E_ALL
--SKIPIF--
<?php
if (PHP_VERSION_ID < 70400) { echo "skip CURLFile serialization forbidden in php 7.4, test requires 7.4+\n"; }
if (!extension_loaded('curl')) { echo "skip requires curl\n"; }
?>
--FILE--
<?php
// https://bugs.php.net/bug.php?id=81111
function check_serialize_throws($obj) {
    try {
        echo urlencode(igbinary_serialize($obj)), "\n";
    } catch (Throwable $e) {
        echo "Caught: " . $e->getMessage() . "\n";
    }
}
class Something extends CURLFile {
    public function __serialize() { return []; }
    public function __unserialize($value) { return new self('file'); }
}

check_serialize_throws(new CURLFile('file'));
check_serialize_throws(new Something('file'));
?>
--EXPECTF--
Caught: Serialization of 'CURLFile' is not allowed
Caught: Serialization of 'Something' is not allowed
