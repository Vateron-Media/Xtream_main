--TEST--
__serialize() mechanism (005): parent::__unserialize() is safe
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php

// NOTE: PHP 8.1.0 changed the way property iteration is done to optimize memory usage(affects var_dump output order but not the correctness of igbinary).
// To work around this, declare all properties on the same class.
class A {
    private $data;
    protected $data2;
    public function __construct(array $data) {
        $this->data = $data;
    }
    public function __serialize() {
        return $this->data;
    }
    public function __unserialize(array $data) {
        $this->data = $data;
    }
}

class B extends A {
    public function __construct(array $data, array $data2) {
        parent::__construct($data);
        $this->data2 = $data2;
    }
    public function __serialize() {
        return [$this->data2, parent::__serialize()];
    }
    public function __unserialize(array $payload) {
        [$data2, $data] = $payload;
        parent::__unserialize($data);
        $this->data2 = $data2;
    }
}

$common = new stdClass;
$obj = new B([$common], [$common]);
var_dump(bin2hex($s = igbinary_serialize($obj)));
var_dump(igbinary_unserialize($s));

?>
--EXPECT--
string(70) "0000000217014214020600140106001708737464436c61737314000601140106002202"
object(B)#3 (2) {
  ["data":"A":private]=>
  array(1) {
    [0]=>
    object(stdClass)#4 (0) {
    }
  }
  ["data2":protected]=>
  array(1) {
    [0]=>
    object(stdClass)#4 (0) {
    }
  }
}
