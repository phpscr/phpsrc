<?php
$url='http://www.weather.com.cn/weather/101130301.shtml';
$ot=file_get_contents("$url");
echo $ot;
?>
