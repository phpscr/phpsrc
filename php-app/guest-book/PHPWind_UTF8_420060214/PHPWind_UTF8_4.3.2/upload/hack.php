<?php
require_once('global.php');
require_once(R_P.'require/header.php');
if(ereg("^http",$H_name)!==false){
	ObHeader("$H_name");
}elseif(!$db_hackdb[$H_name][2] || !file_exists(R_P.'hack/'.$db_hackdb[$H_name][2])){
	Showmsg("hack_error");
}
require_once(D_P."data/hack.php");
?>