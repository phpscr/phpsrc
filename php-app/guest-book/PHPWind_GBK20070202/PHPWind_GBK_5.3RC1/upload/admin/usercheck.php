<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=usercheck&a_type=$a_type";
require_once(R_P.'require/forum.php');

if(empty($_POST['action'])){
	if($a_type=='checkemail'){
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_members WHERE yz>1");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

		$memdb_E=array();
		$query=$db->query("SELECT uid,username,regdate,email FROM pw_members WHERE yz>1 $limit");
		while($member=$db->fetch_array($query)){
			$member['regdate']=get_date($member['regdate']);
			$memdb_E[]=$member;
		}
	}elseif($a_type=='checkreg'){
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_members WHERE groupid='7'");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

		$memdb_R=array();
		$query=$db->query("SELECT m.uid,username,regdate,email,i.regreason FROM pw_members m LEFT JOIN pw_memberinfo i ON i.uid=m.uid WHERE groupid='7' $limit");
		while($member=$db->fetch_array($query)){
			$member['regdate']=get_date($member['regdate']);
			$memdb_R[]=$member;
		}
	}
	include PrintEot('usercheck');exit;
} elseif($action=='check'){
	!$_POST['yzmem'] && adminmsg('operate_error');
	$uids='';
	foreach($_POST['yzmem'] as $value){
		is_numeric($value) && $uids.=$value.',';
	}
	if($uids){
		$uids=substr($uids,0,-1);
		if($type=='pass'){
			if($a_type=='checkemail'){
				$db->update("UPDATE pw_members SET yz='1' WHERE uid IN($uids)");
			} elseif($a_type=='checkreg'){
				$db->update("UPDATE pw_members SET groupid='-1' WHERE uid IN($uids)");
			}
		}else{
			$db->update("DELETE FROM pw_members WHERE uid IN ($uids)");
			$db->update("DELETE FROM pw_memberdata WHERE uid IN ($uids)");
			$db->update("DELETE FROM pw_memberinfo WHERE uid IN ($uids)");
			@extract($db->get_one("SELECT count(*) AS count FROM pw_members"));
			@extract($db->get_one("SELECT username FROM pw_members ORDER BY uid DESC LIMIT 1"));
			$db->update("UPDATE pw_bbsinfo SET newmember='$username', totalmember='$count'  WHERE id='1'");
		}
	}
	adminmsg('operate_success');
}
?>