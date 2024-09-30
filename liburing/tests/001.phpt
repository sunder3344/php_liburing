--TEST--
Check if liburing is loaded
--EXTENSIONS--
liburing
--FILE--
<?php
echo 'The extension "liburing" is available';
?>
--EXPECT--
The extension "liburing" is available
