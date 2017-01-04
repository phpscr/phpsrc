<?php
require_once('global.php');
require_once(R_P.'require/header.php');
require_once(R_P.'require/forum.php');
include_once(D_P.'data/bbscache/forumcache.php');
include_once(D_P.'data/bbscache/forum_cache.php');

!$windid && Showmsg('not_login');
!$_G['show'] && Showmsg('groupright_show');
$db_showperpage = 16;

$fidoff=0;
$query = $db->query("SELECT fid,password,allowvisit FROM pw_forums WHERE type!='category'");
while ($rt = $db->fetch_array($query)){
	if($db_recycle && $rt['fid'] == $db_recycle || $rt['password'] || $rt['allowvisit'] && strpos($rt['allowvisit'],",$groupid,") === false){
		is_numeric($rt['fid']) && $fidoff .= ','.$rt['fid'];
	}
}
$sqladd = "a.fid NOT IN($fidoff) AND t.ifcheck=1";
if($pwuser || is_numeric($uid)){
	if ($pwuser){
		$rt  = $db->get_one("SELECT uid,username FROM pw_members WHERE username='$pwuser'");
		$errorname = $pwuser;
	}elseif(is_numeric($uid)){
		$rt  = $db->get_one("SELECT uid,username FROM pw_members WHERE uid='$uid'");
	}
	if(!$rt){
		Showmsg('user_not_exists');
	}else{
		$uid     = $rt['uid'];
		$owner   = $rt['username'];
		$sqladd .= " AND a.uid='$uid'";
	}
}

if(is_numeric($fid) && $fid > 0){
	$sqladd .= " AND a.fid='$fid'";
	$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
}
$type_1 = $type_2 = '';
if ($type == 1){
	$sqladd .= " AND a.type='img'";
	$type_1  = "selected";
} elseif($type == 2) {
	$sqladd .= " AND a.type!='img'";
	$type_2  = "selected";
}
if(!$action){
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_showperpage.",$db_showperpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_attachs a LEFT JOIN pw_threads t USING(tid) WHERE $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_showperpage),"show.php?uid=$uid&fid=$fid&type=$type&");

	$query = $db->query("SELECT a.aid,a.uid,a.attachurl,a.type,a.fid,a.tid,a.pid,a.name,a.descrip,t.subject,m.username FROM pw_attachs a LEFT JOIN pw_threads t ON t.tid=a.tid LEFT JOIN pw_members m ON m.uid=a.uid WHERE $sqladd ORDER BY aid DESC $limit");
	while($rt = $db->fetch_array($query)){
		if(file_exists($attachdir.'/'.$rt['attachurl'])){
			$rt['a_url']=$attachpath.'/'.$rt['attachurl'];
		} elseif($attach_url){
			$rt['a_url']=$attach_url.'/'.$rt['attachurl'];
		} else{
			continue;
		}
		!$rt['pid'] && $rt['pid'] = 'tpc';
		$rt['fname']      = $forum[$rt['fid']]['name'];
		$rt['uploadtime'] = get_date($rt['uploadtime']);
		if(!$rt['descrip']){
			$rt['descrip'] = substrs($rt['subject'],20);
		}
		$showdb[] = $rt;
	}
	require_once PrintEot('show');footer();
}else{
	$rt=$db->get_one("SELECT a.aid,a.uid,a.attachurl,a.type,a.fid,a.tid,a.pid,a.name,a.descrip,t.subject,m.username FROM pw_attachs a LEFT JOIN pw_threads t ON t.tid=a.tid LEFT JOIN pw_members m ON m.uid=a.uid WHERE a.aid='$aid'");
	if($rt){
		if(file_exists($attachdir.'/'.$rt['attachurl'])){
			$rt['a_url']=$attachpath.'/'.$rt['attachurl'];
		} elseif($attach_url){
			$rt['a_url']=$attach_url.'/'.$rt['attachurl'];
		}
	}else{
		Showmsg('pic_not_exists');
	}
	$uid  = $rt['uid'];
	$type = 1;
	$owner = $rt['username'];
	!$rt['pid'] && $rt['pid']='tpc';
	!$rt['descrip'] && $rt['descrip'] = substrs($rt['subject'],20);
	require_once PrintEot('show');footer();
}
?>