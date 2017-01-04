<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=updatecache";

if(empty($action)){
	include PrintEot('update');exit;
} elseif($action=='cache'){
	updatecache();
	adminmsg('operate_success');
} elseif($action=='topped') {
	require_once(R_P.'require/updateforum.php');
	updatetop();
	adminmsg('operate_success');
} elseif($action=='bbsinfo'){
	@extract($db->get_one("SELECT COUNT(*) AS count FROM pw_members"));
	@extract($db->get_one("SELECT username FROM pw_members ORDER BY uid DESC LIMIT 1"));
	$db->update("UPDATE pw_bbsinfo SET newmember='".addslashes($username)."', totalmember='$count'  WHERE id='1'");
	adminmsg('operate_success');
} elseif($action=='online'){
	$writeinto=str_pad("<?die;?>",96)."\n";
	writeover(D_P.'data/bbscache/online.php',$writeinto);
	writeover(D_P.'data/bbscache/guest.php',$writeinto);
	writeover(D_P.'data/bbscache/olcache.php',"<?php\n\$userinbbs=0;\n\$guestinbbs=0;\n?>");
	adminmsg('operate_success');
} elseif($action=='member'){
	if(!$step){
		$db->update("UPDATE pw_memberdata SET postnum=0");
	    $step=1;
	}
	!$percount && $percount=300;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$jumpurl="$basename&action=$action&step=$step&percount=$percount";
	$goon=0;
	$query=$db->query("SELECT authorid,COUNT(*) as count FROM pw_threads GROUP BY authorid LIMIT $start,$percount");
	while($rt=$db->fetch_array($query)){
		$goon=1;
		$db->update("UPDATE pw_memberdata SET postnum=postnum+'$rt[count]' WHERE uid='$rt[authorid]'");
	}

	$query=$db->query("SELECT authorid,COUNT(*) as count FROM pw_posts GROUP BY authorid LIMIT $start,$percount");
	while($rt=$db->fetch_array($query)){
		$goon=1;
		$db->update("UPDATE pw_memberdata SET postnum=postnum+'$rt[count]' WHERE uid='$rt[authorid]'");
	}
	$basename="$admin_file?adminjob=setuser";
	if($goon){
		adminmsg('updatecache_step',$jumpurl);
	} else{
		adminmsg('operate_success');
	}
} elseif($action=='digest'){
	!$step && $step=1;
	!$percount && $percount=300;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$jumpurl="$basename&action=$action&step=$step&percount=$percount";
	$goon=0;
	$query=$db->query("SELECT authorid,COUNT(*) as count FROM pw_threads WHERE digest>0 AND ifcheck='1' GROUP BY authorid LIMIT $start,$percount");
	while($rt=$db->fetch_array($query)){
		$goon=1;
		$db->update("UPDATE pw_memberdata SET digests='$rt[count]' WHERE uid='$rt[authorid]'");
	}
	if($goon){
		adminmsg('updatecache_step',$jumpurl);
	} else{
		adminmsg('operate_success');
	}
} elseif($action=='forum') {
	include_once(D_P.'data/bbscache/forum_cache.php');
	if(!$step){
		$db->update("UPDATE pw_forumdata SET topic=0,article=0,subtopic=0");
	    $step=1;
	}
	!$percount && $percount=30;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$jumpurl="$basename&action=$action&step=$step&percount=$percount";
	$goon=0;
	$query=$db->query("SELECT fid,fup,type,allowhtm,cms FROM pw_forums LIMIT $start,$percount");
	while(@extract($db->fetch_array($query))){
		$goon=1;
		@extract($db->get_one("SELECT COUNT(*) AS topic,SUM( replies ) AS replies FROM pw_threads WHERE fid='$fid' AND ifcheck='1'"));
		$article=$topic+$replies;
		if($type=='sub'){
			$db->update("UPDATE pw_forumdata SET article=article+'$article',subtopic=subtopic+'$topic' WHERE fid='$fup'");
			if($forum[$fup]['type']=='sub'){
				$fup=$forum[$fup]['fup'];
				$db->update("UPDATE pw_forumdata SET article=article+'$article',subtopic=subtopic+'$topic' WHERE fid='$fup'");
			}
		} elseif($type=='category'){
			$topic=$article=0;
		}
		$lt = $db->get_one("SELECT tid,author,postdate,lastpost,lastposter,subject FROM pw_threads WHERE fid='$fid' AND ifcheck=1 ORDER BY lastpost DESC LIMIT 0,1");
		if($lt['tid']){
			$lt['subject'] = addslashes(substrs($lt['subject'],21));
			if($lt['postdate']!=$lt['lastpost']){
				$lt['subject']='Re:'.$lt['subject'];
				$add='&page=e#a';
			}
			$toread=$cms ? '&toread=1' : '';
			$htmurl=$htmdir.'/'.$fid.'/'.date('ym',$lt['postdate']).'/'.$lt['tid'].'.html';
			$new_url=file_exists(R_P.$htmurl) && $allowhtm==1 && !$cms ? "$R_url/$htmurl" : "read.php?tid=$lt[tid]$toread$add";
			$lastinfo=Char_cv($lt['subject'])."\t".$lt['lastposter']."\t".$lt['lastpost']."\t".$new_url;
		} else{
			$lastinfo='';
		}
		$db->update("UPDATE pw_forumdata SET topic='$topic',article=article+'$article',lastpost='$lastinfo' WHERE fid='$fid'");
	}
	if($goon){
		adminmsg('updatecache_step',$jumpurl);
	} else{
		adminmsg('operate_success');
	}
} elseif($action=='thread'){
	!$step && $step=1;
	!$percount &&$percount=300;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$jumpurl="$basename&action=$action&step=$step&percount=$percount";
	$goon=0;
	$query=$db->query("SELECT tid FROM pw_threads WHERE ifcheck='1' LIMIT $start,$percount");
	while(@extract($db->fetch_array($query))){
		$goon=1;
		@extract($db->get_one("SELECT COUNT(*) AS replies FROM pw_posts WHERE tid='$tid' AND ifcheck='1'"));
		$db->update("UPDATE pw_threads SET replies='$replies' WHERE tid='$tid'");
	}
	if($goon){		
		adminmsg('updatecache_step',$jumpurl);
	} else{
		adminmsg('operate_success');
	}
} elseif($action=='group'){
	!$step && $step=1;
	!$percount && $percount=300;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$jumpurl="$basename&action=$action&step=$step&percount=$percount";
	$goon=0;
	$query=$db->query("SELECT m.uid,m.memberid,md.postnum,md.rvrc,md.money,md.credit,md.onlinetime FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid LIMIT $start,$percount");
	while(@extract($db->fetch_array($query))){
		$goon=1;
		$memberid=getmemberid($postnum,$rvrc,$money,$credit,$onlinetime);
		$db->update("UPDATE pw_members SET memberid='$memberid' WHERE uid='$uid'");
	}
	if($goon){
		adminmsg('updatecache_step',$jumpurl);
	} else{
		adminmsg('operate_success');
	}
} elseif($action=='usergroup'){
	/*
	* 更新版主头衔
	*/
	$query=$db->query("SELECT forumadmin FROM pw_forums");
	while($forum=$db->fetch_array($query)){
		if($forum['forumadmin']){
			$adminarray=explode(",",$forum['forumadmin']);
			foreach($adminarray as $value){
				if($value){
					$member=$db->get_one("SELECT username,groupid FROM pw_members WHERE username='".addslashes($value)."'");
					if($member['groupid']=='-1'){
						$db->update("UPDATE pw_members SET groupid='5' WHERE username='".addslashes($value)."'");
					}
				}
			}
		}
	}
	$query=$db->query("SELECT gid FROM pw_usergroups WHERE gptype='system' OR gptype='special'");
	while(@extract($db->fetch_array($query))){
		$gids[]=$gid;
	}
	$query=$db->query("SELECT uid,groupid FROM pw_members WHERE groupid<>'-1'");
	while(@extract($db->fetch_array($query))){
		if(!in_array($groupid,$gids)){
			$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$uid'");
		}
	}
	adminmsg('operate_success');
}

function getmemberid($postnum,$rvrc,$money,$credit,$oltime){
	global $db_upgrade,$lneed;

	switch($db_upgrade){
		case 1:	$nums=$postnum;break;
		case 2:	$nums=floor($rvrc/10);break;
		case 3:	$nums=$money;break;
		case 4:	$nums=$credit;break;
		case 5:	$nums=floor($oltime/3600);break;
		default:$nums=$postnum;break;
	}

	arsort($lneed);
	reset($lneed);
	foreach($lneed as $key=>$lowneed){
		$gid=$key;
		if($nums>=$lowneed){
			break;
		}
	}
	return $gid;
}
?>