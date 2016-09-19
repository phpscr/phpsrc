<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=editgroup";

if (!$action){
	$groupselect = '';
	$query       = $db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gid<>5 AND (gptype='system' OR gptype='special')");
	while ($group = $db->fetch_array($query)){
		$groupselect .= "<option value='$group[gid]'>$group[grouptitle]</option>";
	}
	include PrintEot('editgroup');exit;
} elseif ($_POST['action'] == 'add'){
	!$members && adminmsg('operate_fail');
	$groups = explode(",",$members);
	$groups = array_unique($groups);
	foreach ($groups as $value){
		if ($value){
			$member = $db->get_one("SELECT uid,groupid FROM pw_members WHERE username='$value'");
			if (!$member['uid']){
				$errorname = $value;
				adminmsg('user_not_exists');
			} elseif ($member['groupid'] != '-1'){
				adminmsg('member_only');
			} elseif ($gid == 3 && !If_manager){
				adminmsg('manager_right');
			} elseif ($gid == 4 && !If_manager && $admin_uid != 3){
				adminmsg('chiefadmin_right');
			} elseif ($gid == 5){
				adminmsg('setuser_forumadmin');
			}
			$memuid[] = $member['uid'];
		}
	}
	!$memuid && adminmsg('operate_fail');
	if ($gid == 6){
		foreach ($memuid as $uid){
			$db->update("REPLACE INTO pw_banuser VALUES('$uid','2','$timestamp','','".addslashes($admin_name)."','')");
		}
	}
	$uids = "'".implode("','",$memuid)."'";
	$db->update("UPDATE pw_members SET groupid='$gid' WHERE uid IN($uids)");
	adminmsg('operate_success');
}
?>