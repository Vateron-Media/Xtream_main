--TEST--
igbinary object with typed properties with reference to ArrayObject
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) die("skip test requires typed properties"); ?>
--INI--
; Note that php 8.1 deprecates using Serializable without __serialize/__unserialize but we are testing Serialize for igbinary. Suppress deprecations.
error_reporting=E_ALL & ~E_DEPRECATED
--FILE--
<?php
class TestClass {
  private ArrayAccess $env;
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
