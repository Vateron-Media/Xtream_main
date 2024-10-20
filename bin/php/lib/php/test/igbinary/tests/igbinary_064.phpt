--TEST--
Works when there are hash collisions in strings when serializing.
--SKIPIF--
<?php
if(!extension_loaded('igbinary')) {
    echo "skip no igbinary";
}
?>
--FILE--
<?php

#[AllowDynamicProperties]
class Fy{
    public $EzFy = 2;
    public function __construct($x) {
        $this->x = $x;
    }
}
class Ez {
    public $FyEz = 'EzEz';
}

class G8 {
    public $FyG8;
}

$data = array(new Fy('G8G8'), new Fy('EzG8'), new Ez(), new G8(), new Ez(), 'G8' => new G8(), 'F8Ez' => new G8(), array(new G8()));
var_dump($data);
echo "\n";
$str = igbinary_serialize($data);
echo bin2hex($str) . "\n";
$unserialized = igbinary_unserialize($str);
var_dump($unserialized);
echo "\n";
var_export(serialize($data) === serialize($unserialized));
?>
--EXPECT--
array(8) {
  [0]=>
  object(Fy)#1 (2) {
    ["EzFy"]=>
    int(2)
    ["x"]=>
    string(4) "G8G8"
  }
  [1]=>
  object(Fy)#2 (2) {
    ["EzFy"]=>
    int(2)
    ["x"]=>
    string(4) "EzG8"
  }
  [2]=>
  object(Ez)#3 (1) {
    ["FyEz"]=>
    string(4) "EzEz"
  }
  [3]=>
  object(G8)#4 (1) {
    ["FyG8"]=>
    NULL
  }
  [4]=>
  object(Ez)#5 (1) {
    ["FyEz"]=>
    string(4) "EzEz"
  }
  ["G8"]=>
  object(G8)#6 (1) {
    ["FyG8"]=>
    NULL
  }
  ["F8Ez"]=>
  object(G8)#7 (1) {
    ["FyG8"]=>
    NULL
  }
  [5]=>
  array(1) {
    [0]=>
    object(G8)#8 (1) {
      ["FyG8"]=>
      NULL
    }
  }
}

00000002140806001702467914021104457a4679060211017811044738473806011a0014020e0106020e021104457a473806021702457a140111044679457a1104457a457a06031702473814011104467947380006041a0514010e060e070e081a0814010e090011044638457a1a0814010e09000605140106001a0814010e0900
array(8) {
  [0]=>
  object(Fy)#9 (2) {
    ["EzFy"]=>
    int(2)
    ["x"]=>
    string(4) "G8G8"
  }
  [1]=>
  object(Fy)#10 (2) {
    ["EzFy"]=>
    int(2)
    ["x"]=>
    string(4) "EzG8"
  }
  [2]=>
  object(Ez)#11 (1) {
    ["FyEz"]=>
    string(4) "EzEz"
  }
  [3]=>
  object(G8)#12 (1) {
    ["FyG8"]=>
    NULL
  }
  [4]=>
  object(Ez)#13 (1) {
    ["FyEz"]=>
    string(4) "EzEz"
  }
  ["G8"]=>
  object(G8)#14 (1) {
    ["FyG8"]=>
    NULL
  }
  ["F8Ez"]=>
  object(G8)#15 (1) {
    ["FyG8"]=>
    NULL
  }
  [5]=>
  array(1) {
    [0]=>
    object(G8)#16 (1) {
      ["FyG8"]=>
      NULL
    }
  }
}

true
