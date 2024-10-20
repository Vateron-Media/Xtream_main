--TEST--
igbinary with reference group of size 1 created by array_walk_recursive
--FILE--
<?php
// Source: https://github.com/igbinary/igbinary/issues/268
//Data must be an array of at least depth 2
$data = [['hello', 'world']];
//Each leaf value must have been accessed
array_walk_recursive($data, function ($value) {});

//Then a second array must be constructed element-wise from the first
$data2 = [$data[0]];

//Then both arrays need to be serialized together
$a = [$data, $data2];
$ser1 = igbinary_serialize($a);
$b = igbinary_unserialize($ser1);

print serialize($a) . "\n";
print serialize($b) . "\n";
print bin2hex($ser1) . "\n";
print bin2hex(igbinary_serialize($b)) . "\n";
--EXPECT--
a:2:{i:0;a:1:{i:0;a:2:{i:0;s:5:"hello";i:1;s:5:"world";}}i:1;a:1:{i:0;a:2:{i:0;s:5:"hello";i:1;s:5:"world";}}}
a:2:{i:0;a:1:{i:0;a:2:{i:0;s:5:"hello";i:1;s:5:"world";}}i:1;a:1:{i:0;a:2:{i:0;s:5:"hello";i:1;s:5:"world";}}}
00000002140206001401060014020600110568656c6c6f06011105776f726c640601140106000102
00000002140206001401060014020600110568656c6c6f06011105776f726c640601140106000102
