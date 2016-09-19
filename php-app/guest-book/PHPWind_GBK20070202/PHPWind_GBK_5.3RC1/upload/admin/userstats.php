<?php
!function_exists('adminmsg') && exit('Forbidden');
//require_once D_P.'data/bbscache/forum_cache.php';
require_once(D_P.'data/bbscache/level.php');
require_once GetLang('all');
$ltitle['-1']=$lang['reg_member'];
$basename="$admin_file?adminjob=userstats";
if(empty($_POST['action'])){
	$groupnum=array();
	$query=$db->query("SELECT COUNT(*) AS count,groupid FROM pw_members GROUP BY groupid");
	while($group=$db->fetch_array($query)){
		$groupnum[]=array($group['count'],$group['groupid'],$ltitle[$group['groupid']]);
	}
/*
	$membergroup=array();
	$query=$db->query("SELECT COUNT(*) AS count,memberid FROM pw_members GROUP BY memberid");
	while($rt=$db->fetch_array($query)){
		$membergroup[]=array($rt['count'],$rt['memberid'],$ltitle[$rt['memberid']]);
	}
*/
	include PrintEot('userstats');exit;
}
?>