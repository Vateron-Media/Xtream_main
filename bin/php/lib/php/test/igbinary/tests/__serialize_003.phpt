--TEST--
__serialize() mechanism (003): Interoperability of different serialization mechanisms
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--INI--
; Note that php 8.1 deprecates using Serializable without __serialize/__unserialize but we are testing Serialize for igbinary. Suppress deprecations.
error_reporting=E_ALL & ~E_DEPRECATED
--FILE--
<?php

class Vest implements Serializable {
    public function serialize() {
        echo "serialize() called\n";
        return "payload";
    }
    public function unserialize($payload) {
    }
}
class Test implements Serializable {
    public function __sleep() {
        echo "__sleep() called\n";
    }

    public function __wakeup() {
        echo "__wakeup() called\n";
    }

    public function __serialize() {
        echo "__serialize() called\n";
        return ["key" => "value"];
    }

    public function __unserialize(array $data) {
        echo "__unserialize() called\n";
        var_dump($data);
    }

    public function serialize() {
        echo "serialize() called\n";
        return "payload";
    }

    public function unserialize($payload) {
        echo "unserialize() called\n";
        var_dump($payload);
    }
}

$test = new Test;
var_dump(bin2hex($s = igbinary_serialize($test)));
var_dump(igbinary_unserialize($s));

var_dump(igbinary_unserialize(hex2bin('000000021704546573741d077061796c6f6164')));

?>
--EXPECT--
__serialize() called
string(48) "00000002170454657374140111036b6579110576616c7565"
__unserialize() called
array(1) {
  ["key"]=>
  string(5) "value"
}
object(Test)#2 (0) {
}
unserialize() called
string(7) "payload"
object(Test)#2 (0) {
}
