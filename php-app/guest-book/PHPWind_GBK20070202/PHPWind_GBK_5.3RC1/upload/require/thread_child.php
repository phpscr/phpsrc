<?php
!function_exists('readover') && exit('Forbidden');

$query = $db->query("SELECT f.fid,f.logo,f.name, f.descrip,f.forumadmin,f.password,f.allowvisit,f.f_type,fd.tpost,fd.topic,fd.article,fd.subtopic,fd.lastpost FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.fup='$fid' ORDER BY f.vieworder");
while($child = $db->fetch_array($query)) {
	if(empty($child['allowvisit']) || strpos($child['allowvisit'],','.$groupid.',')!==false){
		list($f_a,$child['au'],$f_c,$child['ft'])=explode("\t",$child['lastpost']);
		$child['pic'] = $winddb['lastvisit']<$f_c && ($f_c+172800>$timestamp) ? 'new' : 'old';
		$child['newtitle']=get_date($f_c);
		$child['t']=substrs($f_a,21);
	} else{
		if($child['f_type']==='hidden'){
			continue;
		}
		$child['pic']="lock";
	}
	$child['topics']=$child['topic']+$child['subtopic'];
	if($db_indexfmlogo==2){
		$child['logo']!='' ? $child['logo']="<img align=left src=$child[logo] border=0>":'';
	} elseif($db_indexfmlogo==1){
		$forumlogofile="$stylepath/forumlogo/$child[fid].gif";
		file_exists("$imgdir/$forumlogofile") && $child['logo']="<img align=left src='$imgpath/$forumlogofile' border=0>";
	}
	if($child['forumadmin']){
		$forumadmin=explode(",",$child['forumadmin']);
		foreach($forumadmin as $key=> $value){
			if($value){
				if(!$db_adminshow){
					//if ($key==4) {$child['admin'].='...'; break;}
					$child['admin'].="<a href=profile.php?action=show&username=".rawurlencode($value).">$value</a> ";
				} else{
					$child['admin'].="<option value=$value>$value</option>";
				}
			}
		}
		$db_adminshow && $child['admin'].='</select>';
	}
	$forumdb[]=$child;
}
$db->free_result($query);
$forumdb && $thread_children='thread_children';
if($foruminfo['viewsub']==1){
	require_once PrintEot('thread_childmain');footer();
}
?>