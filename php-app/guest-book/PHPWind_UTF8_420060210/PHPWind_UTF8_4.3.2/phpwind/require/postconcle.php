<?php
!function_exists('readover') && exit('Forbidden');

global $picpath,$attachname;
$imgdt    = $timestamp + $db_hour;
$attachdt = $imgdt + $db_hour * 100;
if(@rename($picpath,$imgdt) && @rename($attachname,$attachdt)){
	$dbcontent="<?php\n\$picpath='$imgdt';\n\$attachname='$attachdt';\n?>";
	writeover(D_P."data/bbscache/dbset.php",$dbcontent);
}
writeover(D_P."data/bbscache/set_cache.php","<?die;?>|$timestamp");
?>