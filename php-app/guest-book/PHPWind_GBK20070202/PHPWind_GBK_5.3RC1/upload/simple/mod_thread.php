<?php
!function_exists('readover') && exit('Forbidden');

require_once(R_P.'require/forum.php');
$forumname=$forum[$fid]['name'];

$foruminfo=$db->get_one("SELECT f.*,fe.forumset,fd.topic FROM pw_forums f LEFT JOIN pw_forumsextra fe ON fe.fid=f.fid LEFT JOIN pw_forumdata fd ON fd.fid=f.fid WHERE f.fid='$fid'");
if($foruminfo['type']=='category'){
	header("Location: index.php?cateid=$fid");exit;
}
unset($searchadd);
if(!$foruminfo){
	require_once(R_P.'require/url_error.php');
}
wind_forumcheck($foruminfo);

$forumset  = unserialize($foruminfo['forumset']);
if($groupid != 3 && !$foruminfo['allowvisit'] && admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
	list($db_moneyname,,$db_rvrcname,,$db_creditname,)=explode("\t",$db_credits);
	forum_creditcheck();
}

$db_perpage=100;
(!is_numeric($page) || $page < 1) && $page=1;
if($page>1) {
	$start_limit = ($page - 1) * $db_perpage;
} else{
	$start_limit = 0;
	$page = 1;
}
$startid=$start_limit+1;
$count=$foruminfo['topic'];
$numofpage=ceil($count/$db_perpage);
if ($numofpage && $page>$numofpage){
	$page=$numofpage;
}
$pages=PageDiv($count,$page,$numofpage,"{$DIR}f$fid");

$threaddb=array();
$query = $db->query("SELECT * FROM pw_threads WHERE fid='$fid' AND ifcheck='1' ORDER BY topped DESC, lastpost DESC LIMIT $start_limit,$db_perpage");
while($thread = $db->fetch_array($query)) {
	$threaddb[]=$thread;
}
$db->free_result($query);

require_once PrintEot('simple_header');
require_once PrintEot('simple_thread');
?>