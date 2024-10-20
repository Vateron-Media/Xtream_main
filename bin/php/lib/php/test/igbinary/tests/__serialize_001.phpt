--TEST--
__serialize() mechanism (001): Basics
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php

class Test {
    public $prop;
    public $prop2;
    public function __serialize() {
        return ["value" => $this->prop, 42 => $this->prop2];
    }
    public function __unserialize(array $data) {
        $this->prop = $data["value"];
        $this->prop2 = $data[42];
    }
}

$test = new Test;
$test->prop = "foobar";
$test->prop2 = "barfoo";
var_dump(bin2hex($s = igbinary_serialize($test)));
var_dump(igbinary_unserialize($s));

?>
--EXPECT--
string(74) "000000021704546573741402110576616c75651106666f6f626172062a1106626172666f6f"
object(Test)#2 (2) {
  ["prop"]=>
  string(6) "foobar"
  ["prop2"]=>
  string(6) "barfoo"
}
