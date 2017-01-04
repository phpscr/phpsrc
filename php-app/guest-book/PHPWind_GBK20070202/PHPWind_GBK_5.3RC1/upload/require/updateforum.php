<?php
!function_exists('readover') && exit('Forbidden');

function updateforum($fid,$lastinfo=''){
	global $db;	
	$fm = $db->get_one("SELECT fup,type,password,allowvisit,f_type FROM pw_forums WHERE fid='$fid'");
	if($fm['type'] != 'category'){
		$subtopics = $subrepliess = 0;
		$query = $db->query("SELECT fid FROM pw_forums WHERE fup='$fid'");
		while($subinfo = $db->fetch_array($query)){
			@extract($db->get_one("SELECT COUNT(*) AS subtopic,SUM( replies ) AS subreplies FROM pw_threads WHERE fid='$subinfo[fid]' AND ifcheck='1'"));
			$subtopics   += $subtopic;
			$subrepliess += $subreplies;
			$query2 = $db->query("SELECT fid FROM pw_forums WHERE fup='$subinfo[fid]'");
			while($subinfo2 = $db->fetch_array($query2)){
				@extract($db->get_one("SELECT COUNT(*) AS subtopic,SUM( replies ) AS subreplies FROM pw_threads WHERE fid='$subinfo[fid]' AND ifcheck='1'"));
				$subtopics   += $subtopic;
				$subrepliess += $subreplies;
			}
		}
		$rs       = $db->get_one("SELECT COUNT(*) AS topic,SUM( replies ) AS replies FROM pw_threads WHERE fid='$fid' AND ifcheck='1'");
		$topic    = $rs['topic'];
		$replies  = $rs['replies'];
		$article  = $topic + $replies + $subtopics + $subrepliess;
		if(!$lastinfo){
			$lt = $db->get_one("SELECT tid,author,postdate,lastpost,lastposter,subject FROM pw_threads WHERE fid='$fid' AND ifcheck=1 ORDER BY lastpost DESC LIMIT 0,1");
			if($lt['postdate'] == $lt['lastpost']){
				$subject = addslashes(substrs($lt['subject'],26));
			}else{
				$subject = 'Re:'.addslashes(substrs($lt['subject'],26));
			}
			$author  = addslashes($lt['lastposter']);
			$lastinfo = $lt['tid'] ? $subject."\t".$author."\t".$lt['lastpost']."\t"."read.php?tid=$lt[tid]&page=e#a" : '' ;
		}
		$db->update("UPDATE pw_forumdata SET topic='$topic',article='$article',subtopic='$subtopics',lastpost='$lastinfo' WHERE fid='$fid'");
		if($fm['password'] != '' || $fm['allowvisit'] != '' || $fm['f_type'] == 'hidden'){
			$lastinfo = '';
		}
		if($fm['type'] == 'sub'){
			updateforum($fm['fup'],$lastinfo);
		}
	}
}
function updatetop(){
	global $db;
	include(D_P.'data/bbscache/forum_cache.php');
	
	$toppeddb=$fupdb=$topfid=$fups=array();
	foreach($forum as $f=>$v){
		if($v['type']=='category') continue;
		$fup = $v['fup'];
		if($forum[$fup]['type']=='category'){
			$cateid=$fup;
		}elseif($forum[$fup]['type']=='forum'){
			$cateid=$forum[$fup]['fup'];
		}elseif($forum[$fup]['type']=='sub'){
			$fup=$forum[$fup]['fup'];
			$cateid=$forum[$fup]['fup'];
		}
		$fupdb[$f] = $cateid;
		$fups[$cateid][] = $f;
	}
	$query=$db->query("SELECT tid,fid,topped FROM pw_threads WHERE topped>'1'");
	while($rt=$db->fetch_array($query)){
		$topfid[$rt['fid']]['0']++;
		if($rt['topped']=='3'){
			$toppeddb['3'][0]++;
			$toppeddb['3'][1] .= $toppeddb['3'][1] ? ','.$rt['tid'] : $rt['tid'];
		}elseif($rt['topped']=='2'){
			$cateid = $fupdb[$rt['fid']];
			$toppeddb['2'][$cateid][0]++;
			$toppeddb['2'][$cateid][1] .= $toppeddb['2'][$cateid][1] ? ','.$rt['tid'] : $rt['tid'];
			foreach($fups[$cateid] as $k=>$f){
				$topfid[$f]['1']++;
			}
		}
	}
	$cachedb ="\$toppeddb=array(\r\n";
	$cachedb.="\t'3'=>array('{$toppeddb['3'][0]}','{$toppeddb['3'][1]}'),\r\n";
	$cachedb.="\t'2'=>array(\r\n";
	foreach($toppeddb['2'] as $key=>$val){
		$cachedb.="\t\t'$key'=>array('{$val[0]}','{$val[1]}'),\r\n";
	}
	$cachedb.="\t)\r\n";
	$cachedb.=");";
	writeover(D_P.'data/bbscache/toppeddb.php',"<?php\r\n{$cachedb}\r\n?>");

	$db->update("UPDATE pw_forumdata SET top1='".$toppeddb['3'][0]."',top2=0");
	foreach($topfid as $key => $v){
		$top2 = $v['0'];
		$top1 = $toppeddb['3'][0] + $v['1'] - $v['0'];
		$db->update("UPDATE pw_forumdata SET top1='".(int)$top1."',top2='".(int)$top2."' WHERE fid='$key'");
	}
}
function getattachtype($tid){
	global $db,$pw_posts;
	$attach=$db->get_one("SELECT aid FROM $pw_posts WHERE tid='$tid' AND aid<>'' ORDER BY postdate DESC LIMIT 1");
	if(!$attach){
		$attach=$db->get_one("SELECT aid FROM pw_tmsgs WHERE tid='$tid'");
	}
	if($attach){
		$attachs= unserialize(stripslashes($attach['aid']));
		$last=@array_pop($attachs);
		$type=$last['type'];
		switch($type){
			case 'img': return 1;
			case 'txt': return 2;
			case 'zip': return 3;
		}
	}
	return 0;
}
function dtchange($user,$wwz,$postn,$money){
	global $db;
	$user=='guest' || $db->update("UPDATE pw_memberdata SET postnum=postnum+'$postn',rvrc=rvrc+'$wwz',money=money+'$money' WHERE uid='$user'");
}
/*
function getfids($fid){
	include(D_P.'data/bbscache/forum_cache.php');
	$fup=$forum[$fid]['fup'];
	if($forum[$fup]['type']=='category'){
		$cateid=$fup;
	} elseif($forum[$fup]['type']=='forum'){
		$cateid=$forum[$fup]['fup'];
	} else{
		$fup=$forum[$fup]['fup'];
		$cateid=$forum[$fup]['fup'];
	}
	$start=0;
	$fids=$extra='';
	foreach($forum as $key => $value){
		if($start){
			if($value['type']!='category'){
				$fids.=$extra.$value['fid'];
				$extra=',';
			} else{
				break;
			}
		}
		if($value['fid']==$cateid){
			$start=1;
		}
	}
	return $fids;
}
*/
?>