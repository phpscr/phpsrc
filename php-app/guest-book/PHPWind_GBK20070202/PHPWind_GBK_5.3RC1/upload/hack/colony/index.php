<?php
!function_exists('readover') && exit('Forbidden');
require_once(R_P."require/forum.php");
require_once(R_P."require/bbscode.php");
require_once(H_P."require/function.php");
include_once(D_P."data/bbscache/cn_config.php");
include_once(D_P."data/bbscache/cn_class.php");

$db_perpage = 10;
$small_width = 100;
$small_height = 100;
$photodir	= "photo";
list($moneyname,$moneyunit)=cn_credit();
$cy_name  = $db_hackdb['colony'][0];

if($groupid != 3 && !$cn_open){
	Showmsg('colony_close');
}
!$winduid && Showmsg('not_login');

$cyid   = (int)$cyid;
$classid= (int)$classid;
$aid	= (int)$aid;
$pid	= (int)$pid;
$tid	= (int)$tid;

if (!$job){
	require(H_P."require/default.php");
	exit;
}

if($cyid){
	$cydb   = $mycydb = array();
	$mycydb  = $db->get_one("SELECT cm.*,cy.* FROM pw_cmembers cm LEFT JOIN pw_colonys cy ON cy.id=cm.colonyid WHERE cm.uid='$winduid' AND cm.colonyid='$cyid'");
	if($mycydb){ //此数组说明当前会员属于该群
		$cydb = $mycydb; 
	}else {
		$cydb = $db->get_one("SELECT * FROM pw_colonys WHERE id='$cyid'");
		!$cydb && Showmsg("colony_not_exists");
	}
	if($mycydb['ifadmin']=="-1" && $job!="quit") $mycydb=array(); //非审核会员作游客处理
	if(!$cydb['ifopen'] && $job!="join" && $job!="viewphoto" && $groupid!=3 && !$mycydb){
		Showmsg('colony_openlimit');
	}
	//浏览相片和加入群跳过检验		
	if($cydb['level']){
		$cn_albumnum=$cn_albumnum_a;
		$cn_memberfull=$cn_memberfull_a;
	}
	$cydb['createtime'] = get_date($cydb['createtime'],"Y.m.d");
}else {
	$job!="creat" && $job!="help" && Showmsg('undefined_action');
}
$default_array=array("creat","join","help");
if($job=="view"){
	require(H_P."require/home.php");
}elseif (in_array($job,$default_array)) {
	require(H_P."require/default.php");
}elseif ($job=="viewphoto"){
	require(H_P."require/photo.php");
}else{
	require(H_P."require/main.php");
}
?>