<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=postcheck";
include_once(R_P.'require/forum.php');

if ($admin_gid == 5){
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sql = "fid IN($allowfid)";
} else {
	include(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	if($admin_gid == 3){
		$forumcache .= $hideforum;
		$sql = '1';
	} else{
		$sql = "fid NOT IN($hidefid)";
	}
}

if(!$_POST['step']){
	$sql .= " AND ifcheck='0'";
	is_numeric($fid) && $sql .= " AND fid='$fid'";
	if($username){
		$rt  = $db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
		$uid = $rt['uid'];
	}
	is_numeric($uid) && $sql .= "AND authorid='$uid'";
	$sql .= " ORDER BY postdate DESC";

	if($db_plist){
		!isset($ptable) && $ptable=$db_ptable;
		$p_table="<option value=\"0\">post</option>";
		$p_list=explode(',',$db_plist);
		foreach($p_list as $key=>$val){
			$p_table .= "<option value=\"$val\">post$val</option>";
		}
		$p_table=str_replace("<option value=\"$ptable\">","<option value=\"$ptable\" selected>",$p_table);
		$pw_posts = GetPtable($ptable);
	}else{
		$pw_posts = 'pw_posts';
	}
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE $sql");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&fid=$fid&uid=$uid&");

	$postdb=array();
	$query = $db->query("SELECT pid,tid,fid,subject,author,authorid,ifcheck,postdate,content FROM $pw_posts WHERE $sql $limit");
	while($rt = $db->fetch_array($query)){
		if($rt['subject']){
			$rt['subject'] = substrs($rt['subject'],35);
		} else{
			$rt['subject'] = substrs($rt['content'],35);
		}
		$rt['name']     = $forum[$rt['fid']]['name'];
		$rt['postdate'] = get_date($rt['postdate']);
		$postdb[]       = $rt;
	}
	include PrintEot('postcheck');exit;
} elseif($_POST['step']==2){
	if(!$selid = checkselid($selid)){
		$basename = "javascript:history.go(-1);";
		adminmsg('operate_error');	
	}
	$pw_posts = GetPtable($ptable);
	if($type == 'pass'){
		$fids  = $tids = array();
		$query = $db->query("SELECT fid,tid FROM $pw_posts WHERE $sql AND pid IN($selid)");
		while($rt = $db->fetch_array($query)){
			$tids[$rt['tid']] ++;
			$fids[$rt['fid']] ++;
		}
		foreach($tids as $key => $value){
			$rt = $db->get_one("SELECT postdate,author FROM $pw_posts WHERE tid='$key' ORDER BY postdate DESC LIMIT 1");
			$db->update("UPDATE pw_threads SET replies=replies+'$value',lastpost='$rt[postdate]',lastposter ='$rt[author]' WHERE tid='$key'");
		}
		foreach($fids as $key => $value){
			$db->update("UPDATE pw_forumdata SET article=article+'$value',tpost=tpost+'$value' WHERE fid='$key'");
		}
		$db->update("UPDATE $pw_posts SET ifcheck='1' WHERE $sql AND pid IN($selid)");
	} else{
		$db->update("DELETE FROM $pw_posts WHERE $sql AND pid IN($selid)");
	}
	adminmsg('operate_success');
}
?>