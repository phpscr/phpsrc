
<?php
//这个方法纯粹是背函数，不解释；
/*
function countStr($str){
$str_array=str_split($str);
$str_array=array_count_values($str_array);
arsort($str_array);
return $str_array;
}
//以下是例子；
$str="asdfgfdas323344##$\$fdsdfg*$**$*$**$$443563536254fas";
print_r(countStr($str));
?>
*/
/*
<?php
//这个方法有些数据结构的思想，不过还是很好理解的：）
function countStr2($str){
$str_array=str_split($str);
$result_array=array();
foreach($str_array as $value){//判断该字符是否是新出现的种类，是的话就设置为1，不是的话就自加；
if(!$result_array[$value]){
$result_array[$value]=1;
}else{
$result_array[$value]++;
}
}
arsort($result_array);
return $result_array;
}
$str="asdfgfdas323344##$\$fdsdfg*$**$*$**$$443563536254fas";
var_dump(countStr2($str))
?>
*/

/*
<?php
//这个方法纯粹是解法一的蹩脚版本，先找出所有字符的总类，然后在一个一个用substr_count函数统计。
function countStr3($str){
$str_array=str_split($str);
$unique=array_unique($str_array);
foreach ($unique as $v){
$result_array[$v]=substr_count($str,$v);
}
arsort($result_array);
return $result_array;
}
$str="asdfgfdas323344##$\$fdsdfg*$**$*$**$$443563536254fas";
var_dump(countStr3($str));
?>
*/
