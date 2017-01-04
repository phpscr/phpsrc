<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=unituser";
require_once(R_P.'require/credit.php');

if(!$action){
	require_once PrintEot('unituser');
}elseif($_POST['action']=="unit"){
	if(!$uids){
		adminmsg('unituser_username_empty');
	}
	if(!$newuid){
		adminmsg('unituser_newname_empty');
	}
	$touser = $db->get_one("SELECT username FROM pw_members WHERE uid='$newuid'");
	if(!$touser['username']){
		adminmsg('unituser_newname_error');
	}
	$oldinfo=array();
	$uids=explode(',',$uids);
	foreach($uids as $key=>$val){
		if(is_numeric($val)){
			if($val==$newuid){
				adminmsg('unituser_samename');
			}
			$rt = $db->get_one("SELECT m.uid,m.username,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.uid='$val'");
			if(!$rt['uid']){
				adminmsg('unituser_username_error');
			}else{
				$oldinfo[] = $rt;
			}		
		}
	}
	$ptable_a=array('pw_posts');
	if($db_plist){
		$p_list=explode(',',$db_plist);
		foreach($p_list as $val){
			$ptable_a[]='pw_posts'.$val;
		}
	}
	$postnum=$digests=$rvrc=$money=$credit=$currency=0;
	foreach($oldinfo as $key=>$value){
		$postnum  += $value['postnum'];
		$digests  += $value['digests'];
		$rvrc     += $value['rvrc'];
		$money    += $value['money'];
		$credit   += $value['credit'];
		$currency += $value['currency'];

		$creditdb=GetCredit($value['uid']);
		foreach($creditdb as $k=>$val){
			$db->pw_update(
				"SELECT uid FROM pw_membercredit WHERE uid='$newuid' AND cid='$k'",
				"UPDATE pw_membercredit SET value=value+'$val[1]' WHERE uid='$newuid' AND cid='$k'",
				"INSERT INTO pw_membercredit SET uid='$newuid',cid='$k',value='$val[1]'"
			);
		}

		$db->update("UPDATE pw_threads SET author='$touser[username]',authorid='$newuid' WHERE authorid='$value[uid]'");
		foreach($ptable_a as $val){
			$db->update("UPDATE $val SET author='$touser[username]',authorid='$newuid' WHERE authorid='$value[uid]'");
		}
		$db->update("UPDATE pw_attachs SET uid='$newuid' WHERE uid='$value[uid]'");
		

		$db->update("DELETE FROM pw_members WHERE uid='$value[uid]'");
		$db->update("DELETE FROM pw_memberdata WHERE uid='$value[uid]'");
		$db->update("DELETE FROM pw_msg WHERE type='rebox' AND touid='$value[uid]' OR type='sebox' AND fromuid='$value[uid]'");
	}
	$db->update("UPDATE pw_memberdata SET postnum=postnum+'$postnum',digests=digests+'$digests',rvrc=rvrc+'$rvrc',money=money+'$money',credit=credit+'$credit',currency=currency+'$currency' WHERE uid='$newuid'");
	adminmsg('operate_success');
}
?>