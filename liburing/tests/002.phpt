--TEST--
test1() Basic test
--EXTENSIONS--
liburing
--FILE--
<?php
$ret = test1();

var_dump($ret);
?>
--EXPECT--
The extension liburing is loaded and working!
NULL
