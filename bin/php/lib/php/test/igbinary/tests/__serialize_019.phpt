--TEST--
__serialize() freed on unserialize exception without calling destructor.
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php

class Test {
    public $prop;
    public $prop2;
    public function __serialize() {
        return [0 => $this->prop, "value" => $this->prop2];
    }
    public function __unserialize(array $data) {
        echo "In __unserialize Test\n";
        $this->prop = $data[0];
        $this->prop2 = $data['value'];
        unset($data[0]);
        unset($data['value']);
    }

    public function __destruct() {
        echo "In __destruct Test\n";
    }
}

$obj = new stdClass();
$testObj = new Test();
$testObj->prop = 123;
$testObj->prop2 = ['xyz'];
$obj->test = 'bar';
$obj->value = &$testObj;

var_dump(bin2hex($s = igbinary_serialize($obj)));
var_dump(igbinary_unserialize($s));
echo "Done\n";
?>
--EXPECT--
string(116) "000000021708737464436c61737314021104746573741103626172110576616c75652517045465737414020600067b0e0314010600110378797a"
In __unserialize Test
object(stdClass)#3 (2) {
  ["test"]=>
  string(3) "bar"
  ["value"]=>
  object(Test)#4 (2) {
    ["prop"]=>
    int(123)
    ["prop2"]=>
    array(1) {
      [0]=>
      string(3) "xyz"
    }
  }
}
In __destruct Test
Done
In __destruct Test
