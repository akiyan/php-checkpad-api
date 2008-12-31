--TEST--
CheckPad
--SKIPIF--
--FILE--
<?php
error_reporting(E_ALL);
require_once 'Services/CheckPad.php';

$checkpad = new Services_CheckPad();
var_dump(get_class($checkpad));
?>
--GET--
--POST--
--EXPECT--
string(17) "Services_CheckPad"
