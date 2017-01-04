<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=singleright";

if(!$action){
	require_once(R_P.'require/forum.php');
	$sql="";
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";

	if($username){
		$sql=" WHERE m.username='$username'";
		$count=1;
	}else{
		extract($db->get_one("SELECT COUNT(*) AS count FROM pw_singleright"));
	}
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&");

	$query=$db->query("SELECT m.uid,m.username,m.groupid,m.memberid FROM pw_singleright sr LEFT JOIN pw_members m USING(uid) $sql $limit");
	$memberdb=array();
	while($rt=$db->fetch_array($query)){
		$rt['level'] = $rt['groupid']=='-1' ? $ltitle[$rt['memberid']] : $ltitle[$rt['groupid']];
		$memberdb[]=$rt;
	}

	include PrintEot('singleright');exit;
}elseif($action=='setright'){
	if(!$_POST['step']){
		include_once(D_P.'data/bbscache/forumcache.php');
		list($hidefid,$hideforum) = GetHiddenForum();
		$forumcache .= $hideforum;
		$forumcache = "<option></option>".$forumcache;
		$forum_visit=$forum_post=$forum_reply=$forumcache;
		include PrintEot('singleright');exit;
	}else{
		!$username && adminmsg('operate_error');
		$men=$db->get_one("SELECT m.uid,sr.uid as ifset FROM pw_members m LEFT JOIN pw_singleright sr USING(uid) WHERE m.username='$username'");
		if(!$men){
			$errorname=$username;
			adminmsg('user_not_exists');
		}
		$men['ifset'] && adminmsg('right_set');
		$uid=$men['uid'];
		$visit=checkselid($visit);
		$post=checkselid($post);
		$reply=checkselid($reply);
		$db->update("INSERT INTO pw_singleright (uid,visit,post,reply) VALUES ('$uid','$visit','$post','$reply')");
		adminmsg('operate_success');
	}
}elseif($action=='editright'){
	if(!$_POST['step']){
		include_once(D_P.'data/bbscache/forumcache.php');
		list($hidefid,$hideforum) = GetHiddenForum();
		$forumcache .= $hideforum;
		$forumcache = "<option></option>".$forumcache;
		
		$rt=$db->get_one("SELECT sr.*,m.username FROM pw_singleright sr LEFT JOIN pw_members m USING(uid) WHERE sr.uid='$uid'");
		!$rt && adminmsg('operate_error');
		$visit = explode(',',$rt['visit']);
		$post  = explode(',',$rt['post']);
		$reply = explode(',',$rt['reply']);

		$forum_visit=$forum_post=$forum_reply=$forumcache;
		foreach($visit as $key=>$value){
			$forum_visit = str_replace("<option value=\"$value\">","<option value=\"$value\" selected>",$forum_visit);
		}
		foreach($post as $key=>$value){
			$forum_post  = str_replace("<option value=\"$value\">","<option value=\"$value\" selected>",$forum_post);
		}
		foreach($reply as $key=>$value){
			$forum_reply = str_replace("<option value=\"$value\">","<option value=\"$value\" selected>",$forum_reply);
		}
		$username=$rt['username'];
		include PrintEot('singleright');exit;
	}else{
		$visit=checkselid($visit);
		$post =checkselid($post);
		$reply=checkselid($reply);
		$db->update("UPDATE pw_singleright SET visit='$visit',post='$post',reply='$reply' WHERE uid='$uid'");
		adminmsg('operate_success');
	}
}elseif($_POST['action']=='del'){
	if(!$selid=checkselid($selid)){
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_singleright WHERE uid IN($selid)");
	adminmsg('operate_success');
}
?>