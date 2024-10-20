--TEST--
Test refusing to serialize/unserialize unserializable anonymous classes
--INI--
error_reporting=E_ALL
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

check_serialize_throws(new class () {
    public function __serialize() { return []; }
    public function __unserialize($value) { }
});
check_serialize_throws(function () { });

?>
--EXPECTF--
Caught: Serialization of 'class@anonymous' is not allowed
Caught: Serialization of 'Closure' is not allowed
