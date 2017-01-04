<?php
!function_exists('readover') && exit('Forbidden');
function Loginout(){
	global $db,$timestamp,$db_onlinetime,$groupid,$windid,$winduid,$db_ckpath,$db_ckdomain;
	$thisvisit=$timestamp-$db_onlinetime*1.5;
	$db->update("UPDATE pw_memberdata SET thisvisit='$thisvisit' WHERE uid='$winduid' ");
	list($db_ckpath,$db_ckdomain)=explode("\t",GetCookie('ck_info'));
	Cookie('winduser','',0);
	Cookie('hideid','',0);
	Cookie('lastvisit','',0);
	foreach(GetCookie('pwdcheck') as $key=>$val){
		Cookie("pwdcheck[$key]",'',0);
	}
	Cookie('ck_info','',0);
}
function Loginipwrite($winduid){
	global $db,$timestamp,$onlineip;
	$logininfo="$onlineip|$timestamp|6";
	$db->update("UPDATE pw_memberdata SET lastvisit=thisvisit,thisvisit='$timestamp',onlineip='$logininfo' WHERE uid='$winduid' ");
}
function checkpass($username,$password){
	global $db,$timestamp,$onlineip,$db_ckpath,$db_ckdomain,$men_uid;

	$men=$db->get_one("SELECT m.uid,m.password,m.groupid,m.yz,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE username='$username'");
	if($men){
		$e_login=explode("|",$men['onlineip']);
		if($e_login[0]!=$onlineip.' *' || ($timestamp-$e_login[1])>600 || $e_login[2]>1 ){
			$men_uid=$men['uid'];
			$men_pwd=$men['password'];
			$check_pwd=$password;
			if($men['yz'] > 2){
				Showmsg('login_jihuo');
			}
			if(strlen($men_pwd)==16){
				$check_pwd=substr($password,8,16);/*支持 16 位 md5截取密码*/
			}
			if($men_pwd==$check_pwd){
				if(strlen($men_pwd)==16){
					$db->update("UPDATE pw_members SET password='$password' WHERE uid='$men_uid'");
				}
				$L_groupid=(int)$men['groupid'];
				Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
			}else{
				global $L_T;
				$L_T=$e_login[2];
				$L_T ? $L_T--:$L_T=5;
				$F_login="$onlineip *|$timestamp|$L_T";
				$db->update("UPDATE pw_memberdata SET onlineip='$F_login' WHERE uid='$men_uid'");
				Showmsg('login_pwd_error');
			}
		}else{
			global $L_T;
			$L_T=600-($timestamp-$e_login[1]);
			Showmsg('login_forbid');
		}
	} else {
		global $errorname;
		$errorname=$username;
		Showmsg('user_not_exists');
	}
	return array($men_uid,$L_groupid,PwdCode($password));
}
?>