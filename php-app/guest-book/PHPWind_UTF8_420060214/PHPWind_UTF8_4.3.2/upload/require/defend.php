<?php
//Copyright (c) 2003-06 PHPWind
!function_exists('readover') && exit('Forbidden');

foreach($_POST as $_key=>$_value){
	!ereg("^\_",$_key) && !isset($$_key) && $$_key=$_POST[$_key];
}
foreach($_GET as $_key=>$_value){
	!ereg("^\_",$_key) && !isset($$_key) && $$_key=$_GET[$_key];
}
require_once(D_P.'data/bbscache/config.php');
if($db_cc){
	require_once(R_P.'require/fence.php');
}
?>