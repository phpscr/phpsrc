<?php
$wind_in='nt';
require_once('global.php');
require_once(R_P.'require/header.php');
require_once(R_P.'require/forum.php');
include_once(D_P."data/bbscache/forum_cache.php");
require_once(R_P.'require/bbscode.php');
!$fid && $fid=-1;
if($fid!='-1' && $fid!='-2'){
	$foruminfo=$db->get_one("SELECT * FROM pw_forums WHERE fid='$fid'");
	if($foruminfo['type']!='category'){
		if(!$foruminfo){
			require_once(R_P.'require/url_error.php');
		}
		wind_forumcheck($foruminfo);
	}
}

if(!is_numeric($fid)) showmsg('notice_illegalid');
if($fid=='-1'){
	$guide[$db_bbsname]='index.php';
}elseif($fid=='-2'){
	$guide[$db_wwwname]=$db_wwwurl;
}elseif($forum[$fid]['type']=='category'){
	$guide[$forum[$fid]['name']]="index.php?cateid=$fid";
} else{
	$guide[$forum[$fid]['name']]="thread.php?fid=$fid";
}
$noticedb=array();
$query=$db->query("SELECT * FROM pw_announce WHERE fid='$fid' ORDER BY vieworder,startdate DESC");
while($notice=$db->fetch_array($query)){
	$notice['rawurl']=rawurlencode($notice['author']);
	$notice['startdate']=get_date($notice['startdate']);
	$notice['content']=str_replace("\n","<br />",$notice['content']);
	$notice['content']=convert($notice['content'],$db_windpost,2);
	$noticedb[]=$notice;
}
$db->free_result($query);
$msg_guide=headguide($guide);
require_once(PrintEot('notice'));footer();
?>