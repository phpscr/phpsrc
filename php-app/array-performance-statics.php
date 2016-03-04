<?php 
$arr= array(); 
for($i= 0; $i< 509999; $i++){ 
  $arr[]= $i*rand(1,9); 
} 
function GetRunTime() 
{ 
  list($usec,$sec)=explode(" ",microtime()); 
  return ((float)$usec+(float)$sec); 
} 
###################################### 
$time_start= GetRunTime(); 
for($i= 0; $i< count($arr); $i++){ 
  $str= $arr[$i]; 
} 
$time_end= GetRunTime(); 
$time_used= $time_end- $time_start; 
echo 'Used time of for:'.round($time_used, 7).'(s)<br /><br />'; 
unset($str, $time_start, $time_end, $time_used); 
###################################### 
$time_start= GetRunTime(); 
while(list($key, $val)= each($arr)){ 
$str= $val; 
} 
$time_end= GetRunTime(); 
$time_used= $time_end- $time_start; 
echo 'Used time of while:'.round($time_used, 7).'(s)<br /><br />'; 
unset($str, $key, $val, $time_start, $time_end, $time_used); 
###################################### 
$time_start= GetRunTime(); 
foreach($arr as$key=> $val){ 
  $str= $val; 
} 
$time_end= GetRunTime(); 
$time_used= $time_end- $time_start; 
echo 'Used time of foreach:'.round($time_used, 7).'(s)<br /><br />'; 
?> 
