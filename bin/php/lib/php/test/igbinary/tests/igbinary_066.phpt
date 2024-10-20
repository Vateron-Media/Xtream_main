--TEST--
Test serializing different empty arrays
--FILE--
<?php

// The serialization might be different based on php version and opcache settings.
// So, test the unserialization instead.
$a = array();
var_dump(igbinary_unserialize(igbinary_serialize(array('1st' => array(), '2nd' => array(), '3rd' => array()))));
echo "\n";
var_dump(igbinary_unserialize(igbinary_serialize(array('1st' => $a, '2nd' => $a, '3rd' => $a))));
echo "\n";
$result = igbinary_unserialize(igbinary_serialize(array('1st' => $a, '2nd' => &$a, '3rd' => &$a, '4th' => array())));
var_dump($result);
$result['2nd'][] = 2;
var_dump($result);
echo "\n";
?>
--EXPECT--
array(3) {
  ["1st"]=>
  array(0) {
  }
  ["2nd"]=>
  array(0) {
  }
  ["3rd"]=>
  array(0) {
  }
}

array(3) {
  ["1st"]=>
  array(0) {
  }
  ["2nd"]=>
  array(0) {
  }
  ["3rd"]=>
  array(0) {
  }
}

array(4) {
  ["1st"]=>
  array(0) {
  }
  ["2nd"]=>
  &array(0) {
  }
  ["3rd"]=>
  &array(0) {
  }
  ["4th"]=>
  array(0) {
  }
}
array(4) {
  ["1st"]=>
  array(0) {
  }
  ["2nd"]=>
  &array(1) {
    [0]=>
    int(2)
  }
  ["3rd"]=>
  &array(1) {
    [0]=>
    int(2)
  }
  ["4th"]=>
  array(0) {
  }
}
