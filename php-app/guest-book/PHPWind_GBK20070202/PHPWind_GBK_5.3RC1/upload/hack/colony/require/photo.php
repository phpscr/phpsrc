<?php
!function_exists("cn_credit") && die("Forbidden");

!$cn_phopen && Showmsg('colony_phopen');

(!$pid || !$aid) && Showmsg("data_error");
$albumdb=$db->get_one("SELECT * FROM pw_cnalbum WHERE aid='$aid'");
if($albumdb['atype']==1){
	!$mycydb && !$cydb['albumopen'] && $groupid!=3 && Showmsg('colony_opentocn');
}elseif ($albumdb['atype']==3){
	$albumdb['uid']!=$winduid && $groupid!=3 && Showmsg('colony_opentome');
}

$rs=$db->query("SELECT * FROM pw_cnphoto WHERE aid='$aid' ORDER BY uptime DESC ");
$smallphoto=array();
$photointro=$photopid=$photopath=$photoname=$photouploader=$photouptime=array();
$i=0;
while ($photodb=$db->fetch_array($rs)) {
	$i++;
	$photodb['small']=getsmallurl($photodb['path']);
	$photodb['path']=getphotourl($photodb['path']);
	$photodb['uptime']=get_date($photodb['uptime'],"y.m.d H:i");
	
	$photopid[]=$photodb['pid'];
	$photopath[]=addslashes($photodb['path']);
	$photoname[]=addslashes($photodb['pname']);
	$photouploader[]=addslashes($photodb['uploader']);
	$photointro[]=addslashes($photodb['pintro']);
	$photouptime[]=$photodb['uptime'];
	
	if($photodb['pid']==$pid){
		$thephoto=$photodb;
		$position=$i;
	}
	$i<=6 && $smallphoto[$i]=$photodb;
}

$paths=phptojs($photopath);
$names=phptojs($photoname);
$uploaders=phptojs($photouploader);
$uptimes=phptojs($photouptime);
$pids=phptojs($photopid);
$intros=phptojs($photointro);

require PrintHack("photo");footer();

function phptojs($a){
	$a=implode("','",$a);
	$a="'".$a."'";
	$a=str_replace("\n","<br>",$a);
	return $a;
}
?>