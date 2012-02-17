<?php
include 'database.class.php';

new DB('localhost', 'database', 'user', 'password');

//-- If you need another database connection, just extend the original class
class DB2 extends DB {}

new DB2('localhost', 'database2', 'user', 'password');

$variable = 'foo';

//-- Simple query to obtain one object
$result = DB::sexecute("SELECT * FROM `table` WHERE `id` = ".DB::prepare($variable)."LIMIT 1");

echo '<pre>';
var_dump($result);
echo '</pre>';

//-- Simple query to obtain an array of objects
$results = DB::execute("SELECT * FROM `table` LIMIT 5");

echo '<pre>';
var_dump($results);
echo '</pre>';

//-- Simple query when you don't want to obtain results
DB2::query("UPDATE `table` SET `field` = '".DB2::prepare($variable)."' WHERE `this` = 'that'");
?>