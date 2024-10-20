--TEST--
__serialize() mechanism (006): DateTime
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php

$dt = new DateTime('2019-12-08 12:34', new DateTimeZone('UTC'));
var_dump(bin2hex($s = igbinary_serialize($dt)));
var_dump(igbinary_unserialize($s));
$dt = new DateTime('2019-12-08 12:34', new DateTimeZone('Pacific/Nauru'));
var_dump(bin2hex($s = igbinary_serialize($dt)));
var_dump(igbinary_unserialize($s));

?>
--EXPECT--
string(164) "0000000217084461746554696d651403110464617465111a323031392d31322d30382031323a33343a30302e303030303030110d74696d657a6f6e655f747970650603110874696d657a6f6e651103555443"
object(DateTime)#2 (3) {
  ["date"]=>
  string(26) "2019-12-08 12:34:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
string(184) "0000000217084461746554696d651403110464617465111a323031392d31322d30382031323a33343a30302e303030303030110d74696d657a6f6e655f747970650603110874696d657a6f6e65110d506163696669632f4e61757275"
object(DateTime)#1 (3) {
  ["date"]=>
  string(26) "2019-12-08 12:34:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Pacific/Nauru"
}
