--TEST--
__serialize() mechanism (009): Object/reference ids should be the same whether or not __serialize is used.
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php
class Vest {
    public $value;
    public function __construct($value) {
        $this->value = $value;
    }
}

class Test {
    public $prop;
    public function __construct($value) {
        $this->prop = $value;
    }
    public function __serialize() {
        return ["value" => $this->prop];
    }
    public function __unserialize(array $data) {
        $this->prop = $data["value"];
    }
}
$vest = new Vest('first');
$vest2 = new Vest(null);
$vest3 = new Vest($vest2);
$sv = igbinary_serialize([$vest, $vest2, $vest3, $vest, $vest2]);

$test = new Test('first');
$test2 = new Test(null);
$test3 = new Test($test2);
$s = igbinary_serialize([$test, $test2, $test3, $test, $test2]);
// The only difference in the serialization should be the first byte of the only occurrence of the class name.
var_dump(bin2hex($sv));
var_dump(bin2hex($s));
var_dump(igbinary_unserialize($s));
var_dump(igbinary_unserialize($sv));

?>
--EXPECT--
string(114) "00000002140506001704566573741401110576616c75651105666972737406011a0014010e010006021a0014010e0122020603220106042202"
string(114) "00000002140506001704546573741401110576616c75651105666972737406011a0014010e010006021a0014010e0122020603220106042202"
array(5) {
  [0]=>
  object(Test)#7 (1) {
    ["prop"]=>
    string(5) "first"
  }
  [1]=>
  object(Test)#8 (1) {
    ["prop"]=>
    NULL
  }
  [2]=>
  object(Test)#9 (1) {
    ["prop"]=>
    object(Test)#8 (1) {
      ["prop"]=>
      NULL
    }
  }
  [3]=>
  object(Test)#7 (1) {
    ["prop"]=>
    string(5) "first"
  }
  [4]=>
  object(Test)#8 (1) {
    ["prop"]=>
    NULL
  }
}
array(5) {
  [0]=>
  object(Vest)#8 (1) {
    ["value"]=>
    string(5) "first"
  }
  [1]=>
  object(Vest)#7 (1) {
    ["value"]=>
    NULL
  }
  [2]=>
  object(Vest)#9 (1) {
    ["value"]=>
    object(Vest)#7 (1) {
      ["value"]=>
      NULL
    }
  }
  [3]=>
  object(Vest)#8 (1) {
    ["value"]=>
    string(5) "first"
  }
  [4]=>
  object(Vest)#7 (1) {
    ["value"]=>
    NULL
  }
}
