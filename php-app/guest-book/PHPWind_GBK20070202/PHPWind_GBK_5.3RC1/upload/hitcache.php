<?php
error_reporting(0);
define('D_P',__FILE__ ? dirname(__FILE__).'/' : './');

if(filesize(D_P.'data/bbscache/hits.txt')<5120){
	$timestamp=time();
	$lastupdate=$_COOKIE['lastupdate'];
	$onbbstime=$timestamp-$lastupdate;
	$tid =$_GET['tid'];

	setCookie('lastupdate',$timestamp,0);

	if($lastupdate && $onbbstime<=10){
		setCookie('lastupdate','',0);
	}elseif(strlen($tid)<9 && is_numeric($tid)){
		$handle=fopen(D_P."data/bbscache/hits.txt",'ab');
		flock($handle,LOCK_EX);
		fwrite($handle,$tid."\t");
		fclose($handle);
	}
}else{
	@unlink(D_P.'data/bbscache/hits.txt');
}
?>