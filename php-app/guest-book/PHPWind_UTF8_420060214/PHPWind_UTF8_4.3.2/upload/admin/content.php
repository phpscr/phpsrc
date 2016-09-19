<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=content";

if($type == 'tpc'){

	$rt=$db->get_one("SELECT t.subject,tm.content FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid  WHERE t.tid='$id'");
	$rt['content']=str_replace("\n","<br>",$rt['content']);

	include PrintEot('content');exit;
} elseif($type == 'post'){

	$rt=$db->get_one("SELECT subject,content FROM pw_posts WHERE pid='$id'");
	$rt['content']=str_replace("\n","<br>",$rt['content']);
	include PrintEot('content');exit;
} elseif($type == 'message'){
	$rt  = $db->get_one("SELECT touid,title,content FROM pw_msg WHERE mid='$id'");
	$msg = $db->get_one("SELECT username,groupid FROM pw_members WHERE uid='$rt[touid]'");
	if($msg['username'] == $manager && !If_manager){
		adminmsg('msg_managerright');
	}
	if($msg['groupid'] == 3 && $admin_gid != 3){
		adminmsg('msg_adminright');
	}
	$rt['subject']=$rt['title'];
	$rt['content']=str_replace("\n","<br>",$rt['content']);
	include PrintEot('content');exit;
}
?>