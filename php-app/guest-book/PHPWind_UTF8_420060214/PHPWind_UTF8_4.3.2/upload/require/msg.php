<?php
!function_exists('readover') && exit('Forbidden');
function writenewmsg($msg,$sysmsg=0){
	global $db,$groupid;
	$rt = $db->get_one("SELECT uid,username,newpm,banpm,msggroups FROM pw_members WHERE username='".addslashes($msg[0])."'");
	if(!$rt['username']){
		$errorname=$msg[0];
		!$sysmsg && Showmsg('user_not_exists');
	} elseif(!$sysmsg && ($rt['msggroups'] && strpos($rt['msggroups'],",$groupid,")===false || strpos($rt['banpm'],','.$msg[1].',')!==false)){
		Showmsg('msg_refuse');
	} else{
		@extract($GLOBALS, EXTR_SKIP);
        include(GetLang('writemsg'));
        $lang[$msg[2]] && $msg[2]=$lang[$msg[2]];
		$lang[$msg[4]] && $msg[4]=$lang[$msg[4]];
		!$msg[6] && $msg[6]='SYSTEM';
		$db->update("INSERT INTO pw_msg (touid,fromuid,username,type,ifnew,title,mdate,content) VALUES ('$rt[uid]','$msg[1]','".addslashes($msg[6])."','rebox','1','$msg[2]','$msg[3]','$msg[4]')");
		if($msg[5] && $msg[5]=='Y'){
			$db->update("INSERT INTO pw_msg (touid,fromuid,username,type,ifnew,title,mdate,content) VALUES ('$rt[uid]','$msg[1]','".addslashes($rt['username'])."','sebox','0','$msg[2]','$msg[3]','$msg[4]')");
		}
		$rt['newpm']==0 && $db->update("UPDATE pw_members SET newpm=1 WHERE uid='$rt[uid]'");
	}
}
?>