<?php
!function_exists('db_cv') && exit('Forbidden');
$query=$db->query("SELECT t.tid,t.authorid,t.subject,t.postdate,f.fid,f.name FROM pw_threads t LEFT JOIN pw_forums f USING(fid) WHERE $timestamp-t.postdate>f.allowreward*86400 AND t.special='3' AND LEFT(rewardinfo,1)='1' AND f.allowreward>0 ORDER BY t.postdate ASC LIMIT 100");
$tids=$uiddb=array();
while($rt=$db->fetch_array($query)){
	$rt['postdate']=get_date($rt['postdate']);
	$tids[$rt['tid']]=$rt;
}
$title='rewardmsg_notice_title';
$content='rewardmsg_notice_content';
include_once GetLang('writemsg');
$lang[$title] && $title = Char_cv($lang[$title]);
$lang[$content] && $content = Char_cv($lang[$content]);

foreach($tids as $tid=>$msg){
	$writemsg =str_replace(
		array("\$tid","\$msg[subject]","\$msg[postdate]","\$msg[fid]","\$msg[name]"),
		array($tid,$msg['subject'],$msg['postdate'],$msg['fid'],$msg['name']),
		$content
		);	
	$db->update("INSERT INTO pw_msg(touid,fromuid,username,type,ifnew,title,mdate,content) VALUES('$msg[authorid]','0','SYSTEM','rebox','1','$title','$timestamp','$writemsg')");
	$uiddb[]=$msg['authorid'];
}
if($uiddb){
	array_unique($uiddb);
	$uids=implode(',',$uiddb);
	$db->update("UPDATE pw_members SET newpm=newpm+'1' WHERE uid IN($uids) AND (newpm='0' OR newpm='2')");
}
?>