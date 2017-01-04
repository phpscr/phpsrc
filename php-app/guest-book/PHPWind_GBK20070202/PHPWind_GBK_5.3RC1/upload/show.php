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
while($rt = $db->fetch_array($query)){
	if($db_recycle && $rt['fid'] == $db_recycle || $rt['password'] || $rt['allowvisit'] && strpos($rt['allowvisit'],",$groupid,") === false){
		is_numeric($rt['fid']) && $fidoff .= ','.$rt['fid'];
	}
}
$sqladd = "a.fid NOT IN($fidoff)";
if($pwuser || is_numeric($uid)){
	if($pwuser){
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
if($type == 1){
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
	
	$showdb=array();
	$query = $db->query("SELECT a.aid,a.uid,a.attachurl,a.type,a.fid,a.tid,a.pid,a.name,a.descrip,t.subject,t.ifcheck,tm.content,m.username FROM pw_attachs a LEFT JOIN pw_threads t ON t.tid=a.tid LEFT JOIN pw_tmsgs tm ON a.tid=tm.tid LEFT JOIN pw_members m ON m.uid=a.uid WHERE $sqladd ORDER BY aid DESC $limit");
	while($rt = $db->fetch_array($query)){
		$a_url = geturl($rt['attachurl'],'show');
		$rt['a_url'] = is_array($a_url) ? $a_url[0] : $a_url;
		if($groupid!='3' && $groupid!='4' && (!$rt['ifcheck'] || strpos($rt['content'],"[post]") !== false && strpos($rt['content'],"[/post]") !== false || strpos($rt['content'],"[hide") !== false && strpos($rt['content'],"[/hide]") !== false || strpos($rt['content'],"[sell") !== false && strpos($rt['content'],"[/sell]") !== false)){
			$rt['a_url']="none";
		}
		if(!$rt['a_url'])continue;
		!$rt['pid'] && $rt['pid'] = 'tpc';
		$rt['fname']      = $forum[$rt['fid']]['name'];
		$rt['uploadtime'] = get_date($rt['uploadtime']);
		if(!$rt['descrip']){
			$rt['descrip']= substrs($rt['subject'],20);
		}
		$showdb[] = $rt;
	}
	require_once PrintEot('show');footer();
} else{
	$rt=$db->get_one("SELECT a.aid,a.uid,a.attachurl,a.type,a.fid,a.tid,a.pid,a.name,a.descrip,t.subject,tm.content,m.username FROM pw_attachs a LEFT JOIN pw_threads t ON t.tid=a.tid LEFT JOIN pw_tmsgs tm ON tm.tid=a.tid LEFT JOIN pw_members m ON m.uid=a.uid WHERE a.aid='$aid' AND t.ifcheck='1'");
	if($rt){
		$a_url = geturl($rt['attachurl'],'show');
		$rt['a_url'] = is_array($a_url) ? $a_url[0] : $a_url;
		if($groupid!='3' && $groupid!='4' && (strpos($rt['content'],"[post]") !== false && strpos($rt['content'],"[/post]") !== false || strpos($rt['content'],"[hide") !== false && strpos($rt['content'],"[/hide]") !== false || strpos($rt['content'],"[sell") !== false && strpos($rt['content'],"[/sell]") !== false)){
			Showmsg('pic_not_exists');
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