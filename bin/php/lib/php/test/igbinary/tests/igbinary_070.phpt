--TEST--
__serialize() mechanism in igbinary
--SKIPIF--
<?php
if (PHP_VERSION_ID < 70400) {
    die('skip requires php 7.4+');
}
?>
--FILE--
<?php
class Test {
    public $prop;
    public $prop2;
    public function __serialize() {
        return ["value" => $this->prop, 42 => $this->prop2];
    }
    public function __unserialize(array $data) {
        $this->prop = 'unser' . $data["value"];
        $this->prop2 = 'unser' . $data[42];
    }
}
$test = new Test;
$test->prop = "foobar";
$test->prop2 = "barfoo";
$s = igbinary_serialize($test);
echo bin2hex($s) . "\n";
var_dump(igbinary_unserialize($s));
?>
--EXPECT--
000000021704546573741402110576616c75651106666f6f626172062a1106626172666f6f
object(Test)#2 (2) {
  ["prop"]=>
  string(11) "unserfoobar"
  ["prop2"]=>
  string(11) "unserbarfoo"
}
