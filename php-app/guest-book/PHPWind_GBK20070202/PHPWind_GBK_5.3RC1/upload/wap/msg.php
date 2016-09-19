<?php
require_once('global.php');

!$windid && wap_msg('not_login');
wap_header('msg',$db_bbsname);
if(!$action){
	$allnum=$newnum=0;
	$query=$db->query("SELECT COUNT(*) AS num,ifnew FROM pw_msg WHERE touid='$winduid' AND type='rebox' GROUP BY ifnew=0");
	while($rt=$db->fetch_array($query)){
		 $allnum += $rt['num'];
		$rt['ifnew'] && $newnum=$rt['num'];
	}
	require_once PrintEot('wap_msg');
	wap_footer();
}elseif($action=='new'){
	$msgdb=array();
	$query=$db->query("SELECT * FROM pw_msg WHERE touid='$winduid' AND type='rebox' AND ifnew=1 ORDER BY mdate DESC");
	while($rt=$db->fetch_array($query)){
		$rt['mdate'] = get_date($rt['mdate']);
		$msgdb[] = $rt;
	}
	require_once PrintEot('wap_msg');
	wap_footer();
}elseif($action=='all'){
	$msgdb=array();
	$query=$db->query("SELECT * FROM pw_msg WHERE touid='$winduid' AND type='rebox' ORDER BY mdate DESC");
	while($rt=$db->fetch_array($query)){
		$rt['mdate'] = get_date($rt['mdate']);
		$msgdb[] = $rt;
	}
	require_once PrintEot('wap_msg');
	wap_footer();
}elseif($action=='read'){
	$rt=$db->get_one("SELECT * FROM pw_msg WHERE touid='$winduid' AND type='rebox' AND mid='".(int)$mid."'");
	if(!$rt){
		wap_msg('no_msg');
	}
	if($rt['ifnew']){
		$db->update("UPDATE pw_msg SET ifnew=0 WHERE mid='$rt[mid]'");
	}
	$rt['mdate'] = get_date($rt['mdate']);
	require_once PrintEot('wap_msg');
	wap_footer();
}elseif($action=='write'){
	if(!$pwuser || !$_POST['title'] || !$_POST['content']){
		if(is_numeric($touid)){
			$rt=$db->get_one("SELECT username FROM pw_members WHERE uid='$touid'");
			if($rt){
				$pwuser = $rt['username'];
			}
		}
		require_once PrintEot('wap_msg');
		wap_footer();
	}else{
		$rt=$db->get_one("SELECT uid,newpm,banpm,msggroups FROM pw_members WHERE username='$pwuser'");
		if(!$rt){
			wap_msg('user_not_exists');
		}
		if($rt['msggroups'] && strpos($rt['msggroups'],",$groupid,")===false || strpos($rt['banpm'],",$windid,")!==false){
			wap_msg('msg_refuse');
		}
		$title	= wap_cv($title);
		$content= wap_cv($content);
		$db->update("INSERT INTO pw_msg (touid,fromuid,username,type,ifnew,title,mdate,content) VALUES ('$rt[uid]','$winduid','$windid','rebox','1','$title','$timestamp','$content')");
		if($rt['newpm']==0 || $rt['newpm']==2){
			$rt['newpm']++;
			$db->update("UPDATE pw_members SET newpm='$rt[newpm]' WHERE uid='$rt[uid]'");
		}
		wap_msg('msg_success','msg.php');
	}
}elseif($action=='delete'){
	$db->update("DELETE FROM pw_msg WHERE mid='".(int)$mid."' AND type='rebox' AND touid='$winduid'");
	wap_msg('msg_delete','msg.php');
}
?>