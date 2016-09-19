<?php
!function_exists('readover') && exit('Forbidden');

/*
* �������Ƿ����ú��û��Ƿ�ӵ��ʹ�õ��ߵ�Ȩ��
*/
function CheckUserTool($uid,$tooldb){
	global $db,$groupid;

	if(!$tooldb['state']){
		Showmsg('tool_close');
	}
	$condition = unserialize($tooldb['conditions']);
	if($condition['group'] && strpos($condition['group'],",$groupid,") === false){
		Showmsg('tool_grouplimit');
	}
	$userdb   = $db->get_one("SELECT postnum,digests,rvrc,money,credit FROM pw_memberdata WHERE uid='$uid'");
	require_once(R_P.'require/credit.php');
	$creditdb = GetCredit($uid);
	foreach($condition['credit'] as $key => $value){
		if($value){
			if(is_numeric($key)){
				$creditdb[$key][1] < $value && Showmsg('tool_creditlimit');
			} elseif($userdb[$key] < $value){
				Showmsg('tool_creditlimit');
			}
		}
	}
}
function writetoollog($log){
	global $db;
	require_once GetLang('toollog');
	$log['type']    = $lang[$log['type']];
	$log['descrip'] = $lang[$log['descrip']];
	$db->update("INSERT INTO pw_toollog(type,nums,money,descrip,uid,username,ip,time) VALUES('$log[type]','".(int)$log[nums]."','".(int)$log[money]."','$log[descrip]','$log[uid]','$log[username]','$log[ip]','$log[time]')");
}
?>