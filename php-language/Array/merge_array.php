<?php
/*
1 键名为数字时，array_merge()不会覆盖掉原来的值，但'＋'合并数组则会把最先出现的值作为最终结果返回,
而把后面的数组拥有相同键名的那些值抛弃”掉,不是覆盖.
2 键名为字符时，'＋'仍然把最先出现的值作为最终结果返回，而把后面的数组拥有相同键名的那些值“抛弃”掉,
但array_merge()此时会覆盖掉前面相同键名的值.

*/


/*
$a = array('a','b'); 
$b = array('c', 'd'); 
$c = $a + $b; 
var_dump($b + $a); 
var_dump(array_merge($a, $b)); 
*/
$a = array(0 => 'a', 1 => 'b'); 
$b = array(0 => 'c', 1 => 'b'); 
$c = $a + $b; 
var_dump($c); 
var_dump(array_merge($a, $b)); 
/*

$a = array('a', 'b'); 
$b = array('0' => 'c', 1 => 'b'); 
$c = $a + $b; 
var_dump($c); 
var_dump(array_merge($a, $b)); 

$a = array(0 => 'a', 1 => 'b'); 
$b = array('0' => 'c', '1' => 'b'); 
$c = $a + $b; 
var_dump($c); 
var_dump(array_merge($a, $b)); 
*/
?>
