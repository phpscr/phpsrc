<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$amind_file?adminjob=hack&hackset=new&id=new";

if ($action){
	$code  = str_replace('EOT','',$advert);
	$code1 = htmlspecialchars(stripslashes($code));
	$code2 = stripslashes($code);
}
include PrintHack('admin');exit;
?>