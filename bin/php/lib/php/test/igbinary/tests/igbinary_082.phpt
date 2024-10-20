--TEST--
igbinary object with reference to ArrayObject
--FILE--
<?php
class TestClass {
  private $env;
  public function setEnv(ArrayObject &$e) {
    $this->env = &$e;
  }
}

$arrayObject = new ArrayObject();

$testClass = new TestClass();
$testClass->setEnv($arrayObject);

var_dump(igbinary_unserialize(igbinary_serialize($testClass)));
?>
--EXPECTF--
object(TestClass)#%d (1) {
  ["env":"TestClass":private]=>
  object(ArrayObject)#%d (1) {
    ["storage":"ArrayObject":private]=>
    array(0) {
    }
  }
}
