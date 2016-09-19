<?php
$wind_in='nt';
require_once('global.php');
require_once(R_P.'require/header.php');
require_once(R_P.'require/forum.php');
require_once(R_P.'require/bbscode.php');
include_once(D_P."data/bbscache/forum_cache.php");
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
$cateid=0;
if($fid=='-1'){
	$guide[$fid] = array($db_bbsname,'index.php');
}elseif($fid=='-2'){
	$guide[$fid] = array($db_wwwname,$db_wwwurl);
}elseif($forum[$fid]['type']=='category'){
	$guide[$fid] = array($forum[$fid]['name'],"index.php?cateid=$fid");
} else{
	$guide[$fid] = array($forum[$fid]['name'],"thread.php?fid=$fid");
}
$noticedb=array();
$query=$db->query("SELECT * FROM pw_announce WHERE fid='$fid' AND ffid='' ORDER BY vieworder,startdate DESC");
while($notice=$db->fetch_array($query)){
	$notice['rawurl']=rawurlencode($notice['author']);
	$notice['startdate']=get_date($notice['startdate']);
	if($notice['fid']=='-1' && $notice['url']){
		$notice['content']="<a href=\"$notice[url]\" target=\"_blank\">".$notice['url']."</a>";
	}else{
		$notice['content']=str_replace("\n","<br />",$notice['content']);
		$notice['content']=convert($notice['content'],$db_windpost,2);
	}
	$noticedb[]=$notice;
}
$db->free_result($query);
list($msg_guide,$forumlist) = headguide($guide);
require_once(PrintEot('notice'));footer();
?>