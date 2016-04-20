<?php 
//max_execution_time=100; 
ini_set("max_execution_time", 1); //用此function才能真正在运行时设置 
for($i=1; $i< 99999999; $i++) 
{ 
echo "No. {$i}\n"; 
echo '<br />'; 
//flush(); 
} 
?> 
