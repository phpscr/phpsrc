<?php
!function_exists('readover') && exit('Forbidden');

$CK = explode("\t",StrCode(GetCookie('AdminUser'),'DECODE'));
$bbsclose = ($timestamp-$CK[0]<1800 && $CK[1]==$manager && SafeCheck($CK,PwdCode($manager_pwd))) ? 0 : 1;

if($logined && $bbsclose==0){
	Cookie('logined',1,$timestamp + 1800);
}elseif(!GetCookie('logined') || $bbsclose){
	$skin	 = $skinco ? $skinco : $db_defaultstyle;
	$groupid = '';
	Showmsg($db_whybbsclose,($bbsclose ? 0 : 'bbsclose'));
}
?>