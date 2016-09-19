<?php
!function_exists('readover') && exit('Forbidden');

function writelog($log){
	global $db,$db_moneyname,$db_rvrcname;
	require GetLang('log');
	$log['username1'] = addslashes($log['username1']);
	$log['username2'] = addslashes($log['username2']);
	$log['descrip']	  = addslashes($lang[$log['descrip']]);
	$db->update("INSERT INTO pw_adminlog (type,username1,username2,field1,field2,field3,descrip,timestamp,ip) VALUES('$log[type]','$log[username1]','$log[username2]','$log[field1]','$log[field2]','$log[field3]','$log[descrip]','$log[timestamp]','$log[ip]')");
}
?>