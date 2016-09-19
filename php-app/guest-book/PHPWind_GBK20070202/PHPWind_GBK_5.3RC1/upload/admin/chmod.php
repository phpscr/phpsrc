<?php
!function_exists('adminmsg') && exit('Forbidden');

@include_once Pcv(R_P."data/style/$skin.php");
$filepath = array(
	D_P.'data',
	D_P.'data/sql_config.php',
	D_P.'data/bbscache',
	D_P.'data/groupdb',
	R_P.'data/style',
	R_P.$attachname,
	R_P."$attachname/upload",
	R_P.'htm_data',
	R_P."template/$tplpath/header.htm",
	R_P.$picpath,
	R_P."$picpath/cn_img"
);
$filemode = array();
foreach ($filepath as $key => $value){
	if (!file_exists($value)){
		$filemode[$key] = 1;
	} elseif (!is_writable($value)){
		$filemode[$key] = 2;
	} else {
		$filemode[$key] = 0;
	}
}
include PrintEot('chmod');exit;
?>