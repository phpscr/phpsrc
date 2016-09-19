<?php
require_once('global.php');

$listdb=array();
if($fid){
	$per=10;
	$fm=$db->get_one("SELECT topic FROM pw_forumdata WHERE fid='$fid'");
	(!is_numeric($page) || $page < 1) && $page=1;
	$totle=ceil($fm['topic']/$per);
	$totle==0 ? $page=1 : ($page > $totle ? $page=$totle : '');
	$next=$page+1;
	$pre=$page==1 ? 1 : $page-1;
	forumcheck($fid);
	$list='';
	$satrt=($page-1)*$per;
	$id=$satrt;
	$limit="LIMIT $satrt,$per";
	$query=$db->query("SELECT tid,subject,postdate FROM pw_threads WHERE fid='$fid' AND ifcheck=1 ORDER BY lastpost DESC $limit");
	while($rt=$db->fetch_array($query)){
		$id++;
		$rt['postdate'] = get_date($rt['postdate']);
		$rt['id'] = $id;
		$listdb[] = $rt;
	}
}
wap_header('list',$db_bbsname);
require_once PrintEot('wap_list');
wap_footer();
?>