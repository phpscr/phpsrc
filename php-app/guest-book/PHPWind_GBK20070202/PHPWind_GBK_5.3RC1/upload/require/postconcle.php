<?php
!function_exists('readover') && exit('Forbidden');

global $picpath,$attachname;
$imgdt    = $timestamp + $db_hour;
$attachdt = $imgdt + $db_hour * 100;
if(@rename($picpath,$imgdt) && @rename($attachname,$attachdt)){
	require_once(R_P.'require/updateset.php');
	$setting = array();
	$setting['pic'] = $imgdt;
	$setting['att'] = $attachdt;
	write_config($setting);
}
writeover(D_P."data/bbscache/set_cache.php","<?die;?>|$timestamp");
?>