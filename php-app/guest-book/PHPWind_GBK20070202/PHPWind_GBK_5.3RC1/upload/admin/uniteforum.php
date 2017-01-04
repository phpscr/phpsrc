<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=uniteforum&type=$type";
require_once(R_P.'require/updateforum.php');

if(empty($_POST['action'])){
	@include_once(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();

	include PrintEot('uniteforum');exit;
} else{
	if($fid==$tofid){
		adminmsg('unite_same');
	}
	if($fid==$db_recycle){
		adminmsg('unite_type');
	}

	$sub=$db->get_one("SELECT fid,name FROM pw_forums WHERE fup='$fid' LIMIT 1");
	if($sub){
		adminmsg('forum_havesub');
	}
	$forum=$db->get_one("SELECT type FROM pw_forums WHERE fid='$tofid' LIMIT 1");
	if($forum['type']=='category'){
		adminmsg('unite_type');
	}
	$forum=$db->get_one("SELECT fup,type FROM pw_forums WHERE fid='$fid' LIMIT 1");
	if($forum['type']=='category'){
		adminmsg('unite_type');
	}
	
	$db->update("UPDATE pw_threads SET fid='$tofid' WHERE fid='$fid'");
	$ptable_a=array('pw_posts');
	if($db_plist){
		$p_list=explode(',',$db_plist);
		foreach($p_list as $val){
			$ptable_a[]='pw_posts'.$val;
		}
	}
	foreach($ptable_a as $val){
		$db->update("UPDATE $val SET fid='$tofid' WHERE fid='$fid'");
	}
	$db->update("UPDATE pw_attachs SET fid='$tofid' WHERE fid='$fid'");
	$db->update("DELETE FROM pw_forums WHERE fid='$fid'");
	$db->update("DELETE FROM pw_forumdata WHERE fid='$fid'");
	$db->update("DELETE FROM pw_forumsextra WHERE fid='$fid'");

	updatecache_f();
	updateforum($tofid);
	if($forum['type']=='sub'){
		updateforum($forum['fup']);
	}
	adminmsg('operate_success');
}