<?php
!function_exists('readover') && exit('Forbidden');
function writenewmsg($msg,$sysmsg=0){

	@extract($GLOBALS, EXTR_SKIP);
	include(GetLang('writemsg'));
	$lang[$msg[2]] && $msg[2] = Char_cv($lang[$msg[2]]);
	$lang[$msg[4]] && $msg[4] = Char_cv($lang[$msg[4]]);
	$msg[0] = addslashes($msg[0]);
	$msg[6] = Char_cv($msg[6]);
	!$msg[6] && $msg[6]='SYSTEM';

	$rt = $db->get_one("SELECT uid,username,newpm FROM pw_members WHERE username='$msg[0]'");
	Add_S($rt);
	$db->update("INSERT INTO pw_msg(touid,fromuid,username,type,ifnew,title,mdate,content) VALUES('$rt[uid]','$msg[1]','$msg[6]','rebox','1','$msg[2]','$msg[3]','$msg[4]')");
	if($msg[5]=='Y'){
		$db->update("INSERT INTO pw_msg(touid,fromuid,username,type,ifnew,title,mdate,content) VALUES('$rt[uid]','$msg[1]','$rt[username]','sebox','0','$msg[2]','$msg[3]','$msg[4]')");
	}
	if($rt['newpm']==0 || $rt['newpm']==2){
		$db->update("UPDATE pw_members SET newpm=newpm+'1' WHERE uid='$rt[uid]' AND (newpm='0' OR newpm='2')");
	}
}
?>