--TEST--
igbinary and not enough data for array
--FILE--
<?php
echo "One byte\n";
igbinary_unserialize("\x00\x00\x00\x02\x14");
echo "Two byte\n";
igbinary_unserialize("\x00\x00\x00\x02\x15\x01");
igbinary_unserialize("\x00\x00\x00\x02\x15");
igbinary_unserialize("\x00\x00\x00\x02\x15\x00\x01");
echo "Four byte\n";
igbinary_unserialize("\x00\x00\x00\x02\x16\x00");
igbinary_unserialize("\x00\x00\x00\x02\x16\x00\x00\x01");
igbinary_unserialize("\x00\x00\x00\x02\x16\x00\x00\x00\x01");
?>
--EXPECTF--
One byte

Warning: igbinary_unserialize_array: end-of-data in %s on line 3
Two byte

Warning: igbinary_unserialize_array: end-of-data in %s on line 5

Warning: igbinary_unserialize_array: end-of-data in %s on line 6

Warning: igbinary_unserialize_array: data size 0 smaller that requested array length 1. in %s on line 7
Four byte

Warning: igbinary_unserialize_array: end-of-data in %s on line 9

Warning: igbinary_unserialize_array: end-of-data in %s on line 10

Warning: igbinary_unserialize_array: data size 0 smaller that requested array length 1. in %s on line 11
