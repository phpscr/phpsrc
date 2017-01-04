<?php
require_once('global.php');

if($tid){
	$rt=$db->get_one("SELECT t.fid,t.tid,t.subject,t.author,t.replies,t.locked,t.postdate,tm.content FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid='$tid' AND ifcheck=1");
	if($rt['locked']==2){
		wap_msg('read_locked');
	}
	if(!$rt){
		wap_msg('illegal_tid');
	}
	forumcheck($rt['fid']);

	$per=5;
	(!is_numeric($page) || $page < 1) && $page=1;
	$totle=ceil($rt['replies']/$per);
	$totle==0 ? $page=1 : ($page > $totle ? $page=$totle : '');
	$next=$page+1;
	$pre=$page==1 ? 1 : $page-1;
	if($page==1){
		$rt['content']	= substrs($rt['content'],$db_waplimit);
		$rt['content']	= preg_replace("/\[post\](.+?)\[\/post\]/is","",$rt['content']);
		$rt['content']	= preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is","",$rt['content']);
		$rt['content']	= preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is","",$rt['content']);
		$rt['content']	= preg_replace("/<br>/is","_br_",$rt['content']);
		$rt['content']	= str_replace("_br_","<BR />",$rt['content']);
		$rt['postdate']	= get_date($rt['postdate']);
	}

	$satrt=($page-1)*$per;
	$id=$satrt;
	$limit="LIMIT $satrt,$per";
	$posts='';
	$query=$db->query("SELECT subject,author,content,postdate FROM pw_posts WHERE tid='$rt[tid]' AND ifcheck=1 ORDER BY postdate $limit");
	while($ct=$db->fetch_array($query)){
		if($ct['content']){
			$id++;
			$ct['content']	= substrs($ct['content'],$db_waplimit);
			$ct['content']	= preg_replace("/\[post\](.+?)\[\/post\]/is","",$ct['content']);
			$ct['content']	= preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is","",$ct['content']);
			$ct['content']	= preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is","",$ct['content']);
			$ct['content']	= preg_replace("/<br>/is","_br_",$ct['content']);
			$ct['content']	= str_replace("_br_","<BR />",$ct['content']);
			$ct['postdate']	= get_date($ct['postdate']);
			$ct['id']		= $id;
			$postdb[]		= $ct;
		}
	}
} else{
	wap_msg('illegal_tid');
}
wap_header('read',$db_bbsname);
require_once PrintEot('wap_read');
wap_footer();
?>