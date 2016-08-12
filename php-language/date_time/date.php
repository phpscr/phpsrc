<?php
date_default_timezone_set('PRC');
global $date; 
$date = date("l jS F \@ g:i a", time());  
echo("[$date] Loading Configs\n");
echo $date;
?>

