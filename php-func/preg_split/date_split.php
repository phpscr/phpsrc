<?php 
date_default_timezone_set("Asia/Shanghai");
$date = date("Y-m-d h-m-s");
$ar = preg_split('/[- :]/',$date); 
foreach($ar as $v){
echo "  \n ";
//echo $v;
}
//var_dump($ar); 
$imageTypes = '{*.jpg,*.JPG,*.jpeg,*.JPEG,*.png,*.PNG,*.gif,*.GIF}';
var_dump($imageTypes);
?> 

