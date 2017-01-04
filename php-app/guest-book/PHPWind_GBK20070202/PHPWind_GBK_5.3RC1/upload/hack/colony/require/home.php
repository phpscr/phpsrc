<?php
!function_exists("cn_credit") && die("Forbidden");

$colonymembers=array();
$colonyadmins="";
$rs=$db->query("SELECT uid,username,gender,ifadmin FROM pw_cmembers WHERE colonyid='$cyid' LIMIT 20");
while ($memberdb=$db->fetch_array($rs)) {
	if($memberdb['ifadmin']==1){
		$colonyadmins.=" <a href=profile.php?action=show&uid=$memberdb[uid]>".$memberdb['username']."</a> ";
		$memberdb['adminimg']="<img src='$hkimg/admin.gif'  align=\"absmiddle\">";
	}
	$colonymembers[]=$memberdb;
}
!$cydb['intomoney'] && $cydb['intomoney'] =  $cn_joinmoney;
$cydb['descrip']    = str_replace("\n","<br>",$cydb['descrip']);
$cydb['annouce']    = str_replace("\n","<br>",$cydb['annouce']);

$argudb = array();
$query  = $db->query("SELECT * FROM pw_argument WHERE gid='$cyid' AND tpcid=0 ORDER BY lastpost DESC LIMIT 10");
while ($rt = $db->fetch_array($query)){
	$rt['lastpost'] = get_date($rt['lastpost']);
	$argudb[] = $rt;
}

$photodb = array();
$query2 =$db->query("SELECT a.aid,p.pid,p.path,p.uptime FROM pw_cnphoto p LEFT JOIN pw_cnalbum a USING(aid) WHERE a.cyid='$cyid' ORDER BY p.uptime DESC LIMIT 4");
while ($rt2 = $db->fetch_array($query2)) {
	$rt2['path']=getsmallurl($rt2['path']);
	//$rt2['uptime']=get_date($rt2['uptime'],"y.m.d H:i");
	$photodb[]=$rt2;
}

require_once PrintHack('home');footer();
?>