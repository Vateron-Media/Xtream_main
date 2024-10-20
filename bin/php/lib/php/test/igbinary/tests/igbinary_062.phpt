--TEST--
igbinary should not call __wakeup() if Serializable::unserialize was used to unserialize the object data (like `unserialize`)
--INI--
; Note that php 8.1 deprecates using Serializable without __serialize/__unserialize but we are testing Serialize for igbinary. Suppress deprecations.
error_reporting=E_ALL & ~E_DEPRECATED
--FILE--
<?php

class A implements Serializable {
   public $prop = 'value';
   public function serialize() {
       echo "In serialize " . $this->prop . "\n";
       return $this->prop;
   }
   public function unserialize($data) {
       echo "In unserialize $data\n";
       $this->prop = $data;
   }

   public function __wakeup() {
       echo "In __wakeup, unexpectedly\n";
   }
}

function testA() {
  $a = new A();
  $a->prop = 'other';
  $ser = serialize($a);
  $b = unserialize($ser);
  var_dump($b);

  $c = new A();
  $c->prop = 'igprop';
  $serC = igbinary_serialize($c);
  echo bin2hex($serC) . "\n";
  $d = igbinary_unserialize($serC);
  var_dump($d);

}

testA();
?>
--EXPECTF--
In serialize other
In unserialize other
object(A)#%d (1) {
  ["prop"]=>
  string(5) "other"
}
In serialize igprop
000000021701411d06696770726f70
In unserialize igprop
object(A)#%d (1) {
  ["prop"]=>
  string(6) "igprop"
}
