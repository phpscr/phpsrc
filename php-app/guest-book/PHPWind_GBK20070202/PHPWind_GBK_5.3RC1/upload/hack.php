<?php
require_once('global.php');
require_once(R_P.'require/header.php');
if(ereg("^http",$H_name)!==false){
	ObHeader("$H_name");
}elseif(!$db_hackdb[$H_name] || !is_dir(R_P."hack/$H_name") || !file_exists(R_P."hack/$H_name/index.php")){
	Showmsg("hack_error");
}
define('H_P',R_P."hack/$H_name/");
$basename="hack.php?H_name=$H_name";
$hkimg = "hack/$H_name/image";

require_once Pcv(H_P."index.php");

function PrintHack($template,$EXT="htm"){
	return H_P."template/".$template.".$EXT";
}
?>