<?php
define('SCR','thread');
require_once('global.php');
require_once(R_P.'require/forum.php');
require_once(R_P.'require/updateforum.php');

$foruminfo = $db->get_one("SELECT f.*,fe.creditset,fe.forumset,fd.topic,fd.top1,fd.top2,a.aid,a.author,a.startdate,a.subject,a.content FROM pw_forums f LEFT JOIN pw_forumsextra fe ON fe.fid=f.fid LEFT JOIN pw_forumdata fd ON fd.fid=f.fid LEFT JOIN pw_announce a ON a.ffid=f.fid WHERE f.fid='$fid'");
$forumset  = unserialize($foruminfo['forumset']);
$forumset['newtime'] && $db_newtime=$forumset['newtime'];
$forumset['link'] && ObHeader($forumset['link']);
$foruminfo['type']=='category' && ObHeader("index.php?cateid=$fid");

unset($searchadd,$thread_children,$thread_online,$fastpost);
if(!$foruminfo){
	require_once(R_P.'require/url_error.php');
}
wind_forumcheck($foruminfo);

include_once(D_P.'data/bbscache/forum_cache.php');
$forumname=$forum[$foruminfo['fid']]['name'];
require_once(R_P.'require/header.php');

$ajaxcheck = $groupid == 3 ? 1 : 0;
if($windid==$manager){
	$admincheck=1;
	$ajaxcheck=1;
} elseif($foruminfo['forumadmin'] && strpos($foruminfo['forumadmin'],','.$windid.',')!==false){
	$admincheck=1;
	$ajaxcheck=1;
} elseif($groupid!=5 && ($SYSTEM['tpctype'] || $SYSTEM['check'] || $SYSTEM['typeadmin'] || $SYSTEM['delatc'] || $SYSTEM['moveatc'] || $SYSTEM['copyatc'] || $SYSTEM['topped'])){
	$admincheck=1;
} else{
	$admincheck=0;
}
!$windid && $admincheck = 0;
if ($windid != $manager && $groupid != 3 && !$foruminfo['allowvisit'] && (!$foruminfo['forumadmin'] || strpos($foruminfo['forumadmin'],','.$windid.',')===false)){
	list($db_moneyname,,$db_rvrcname,,$db_creditname,)=explode("\t",$db_credits);
	forum_creditcheck();
}

$adminarray='';
$adminarray=explode("\t",$foruminfo['forumadmin']);
if($adminarray[0]){
	$forumadmin=explode(",",$adminarray[0]);
	foreach($forumadmin as $key => $value){
		if($value){
			if(!$db_adminshow){
				if ($key==10) {$admin_T['admin'].='...'; break;}
				$admin_T['admin'].="<a href=profile.php?action=show&username=".rawurlencode($value).">$value</a> ";
			} else{
				$admin_T['admin'].="<option value=$value>$value</option>";
			}
		}
	}
	$admin_T['admin']='&nbsp;'.$admin_T['admin'];
}
Update_ol();
if($db_threadonline==1){
	$trd_hide=$trd_nothide=$trd_guest=0;
	$guestarray=readover(D_P.'data/bbscache/guest.php');
	$detail=explode("<FiD>$fid\t",$guestarray);
	$trd_guest=count($detail)-1;
	$onlinearray=openfile(D_P.'data/bbscache/online.php');
	$count_ol=count($onlinearray);
	for($i=1; $i<$count_ol; $i++){
		$detail=explode("\t",$onlinearray[$i]);
		if ($detail[3]==$fid){
			if(strpos($db_showgroup,",".$detail[5].",")!==false){
				$img=$detail[5];
			} else{
				$img='6';
			}
			if($trd_nothide%8==0)$trd_onlineinfo.="</tr><tr>";
			if($detail[9]=='<>') {$trd_hide++; continue;} else $trd_nothide++;
			$trd_onlineinfo.="<td width=12%>&nbsp;<img src='$imgpath/$stylepath/group/$img.gif' align='bottom'><a href=profile.php?action=show&uid=$detail[8]>$detail[0]</a></td>";
		}
	}
	unset($guestarray,$detail);
	$trd_sumonline2=$trd_nothide+$trd_guest+$trd_hide;
	$trd_sumonline1=$trd_nothide+$trd_hide;
	$thread_online='thread_online';
}

$guidename=forumindex($foruminfo['fup']);
$msg_guide=headguide($guidename);
include_once(D_P.'data/bbscache/thread_announce.php');
$ifsort=0;
$NT_A=$NT_C=array();
if($notice_A){
	$ifsort=1;
	$NT_A=array_shift($notice_A);
	$NT_A['rawauthor']=rawurlencode($NT_A['author']);
	$NT_A['startdate']=get_date($NT_A['startdate']);
}
if($notice_C[$cateid]){
	$ifsort=1;
	$NT_C=$notice_C[$cateid];
	$NT_C['rawauthor']=rawurlencode($NT_C['author']);
	$NT_C['startdate']=get_date($NT_C['startdate']);
}

if($foruminfo['aid']){
	$foruminfo['rawauthor']=rawurlencode($foruminfo['author']);
	$foruminfo['startdate']=get_date($foruminfo['startdate']);
	$foruminfo['content']=str_replace("\n","<br>",$foruminfo['content']);
}

if(strpos($_COOKIE['deploy'],"\t".thread."\t")===false){
	$thread_img='fold';
}else{
	$thread_img='open';
	$cate_thread='display:none;';
}
$forumdb=array();
if($foruminfo['childid']){

	$adminarray='';
	$query = $db->query("SELECT f.fid,f.logo,f.name, f.descrip,f.forumadmin,f.password,f.allowvisit,f.f_type,fd.topic,fd.article,fd.subtopic,fd.lastpost FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.fup='$fid' ORDER BY f.vieworder");
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
		$adminarray=explode("\t",$child['forumadmin']);
		if($adminarray[0]){
			$forumadmin=explode(",",$adminarray[0]);
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
}

if ($admincheck){
	!$_GET['managemode'] && $managemode=GetCookie('managemode');
	if($concle==1){
		$concle=2;	$managemode=1;
		Cookie("managemode","1",0);
	}elseif($concle==2){
		$concle=1;	$managemode='';
		Cookie("managemode","",0);
	}elseif(!$managemode){
		$concle=1;
	}elseif($managemode){
		$concle=2;
	}
	$trd_adminhide="<form action=mawhole.php method=post><input type=hidden name=fid value=$fid>";
}

$t_typedb=array();
$t_db=$foruminfo['t_type'];
$t_db && $t_typedb=explode("\t",$t_db);
unset($t_typedb[0]);/* 0 */
if($t_db && is_numeric($type) && isset($t_typedb[$type])){
	$searchadd=" AND type='$type' AND ifcheck='1'";
	$typeadd="type=$type&";
	$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_threads WHERE fid='$fid' $searchadd");
	$count=$rs['count'];
} elseif($search=='digest'){
	$searchadd=" AND digest>'0' AND ifcheck='1'";
	$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_threads WHERE fid='$fid' $searchadd");
	$count=$rs['count'];
} elseif($search=='check'){
	if($admincheck){
		$searchadd=" AND ifcheck='0'";
	}else{
		$searchadd=" AND authorid='$winduid' AND ifcheck='0'";
	}
	$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_threads WHERE fid='$fid' $searchadd");
	$count=$rs['count'];
} elseif(is_numeric($search)){
	$searchadd=" AND lastpost>='".($timestamp - $search*84600)."' AND ifcheck='1'";	
	$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_threads WHERE fid='$fid' $searchadd");
	$count=$rs['count'];
}else{
	$searchadd=" AND ifcheck='1'";
	$count=$foruminfo['topic'];
}
if ($winddb['t_num']){
	$db_perpage = $winddb['t_num'];
} elseif ($forumset['threadnum']){
	$db_perpage = $forumset['threadnum'];
}
if ($winddb['p_num']){
	$db_readperpage = $winddb['p_num'];
} elseif ($forumset['readnum']){
	$db_readperpage = $forumset['readnum'];
}

$tpcdb=array();

$db_maxpage && $page > $db_maxpage && $page=$db_maxpage;
(!is_numeric($page) || $page < 1) && $page=1;

if($db_recycle && $fid==$db_recycle){
	$sql="fid='$fid'";
} elseif($db_topped && $foruminfo['top1']){
	$sql="fid='$fid' AND topped=0";
	$topadd='';   ///
	$count+=$foruminfo['top1'];
} else{
	$sql="fid='$fid'";
	$topadd='topped DESC,';
}
$orderway ? $w_add="orderway=$orderway&asc=$asc&" : $w_add='';
if($gp_alloworder){
	if($orderway!='lastpost' && $orderway!='postdate' && $orderway!='hits' && $orderway!='replies'){
		$orderway='lastpost';
	}
	$ordersel[$orderway]='selected';
	$asc!='DESC' && $asc!='ASC' && $asc = 'DESC';
	$ascsel[$asc]='selected';
} else{
	$asc = 'DESC';
	$orderway='lastpost';
}

$numofpage=ceil($count/$db_perpage);
$totlepage=$numofpage;
if ($numofpage && $page>$numofpage){
	$page=$numofpage;
}

$start_limit = ($page - 1) * $db_perpage;

$R=0;
if($db_topped && $foruminfo['top1']){
	$rows=$foruminfo['top1']+$foruminfo['top2'];
	if($start_limit < $rows){
		$L=min($rows-$start_limit,$db_perpage);
		$limit="LIMIT $start_limit,$L";
		if($L == $db_perpage){
			$limit2="";
		} else{
			$limit2="LIMIT 0,".($db_perpage-$L);
		}
		$fids=getfids($fid);
		$query=$db->query("SELECT * FROM pw_threads WHERE topped=3 OR (topped=2 AND fid IN($fids)) OR (topped=1 AND fid='$fid') ORDER BY topped DESC,lastpost DESC $limit");
		while($rt=$db->fetch_array($query)){
			$tpcdb[]=$rt;
		}
	} else{
		$limit2="LIMIT ".($start_limit-$rows).",$db_perpage";
	}
} else{
	$limit2="LIMIT $start_limit,$db_perpage";
}

$pages=numofpage($count,$page,$numofpage,"thread.php?fid=$fid&search=$search&$w_add$typeadd",$db_maxpage);
$orderway.=' '.$asc;

$attachtype=array(
	'1'=>'img',
	'2'=>'txt',
	'3'=>'zip'
	);

if($limit2){
	$query = $db->query("SELECT * FROM pw_threads WHERE $sql $searchadd ORDER BY $topadd $orderway $limit2");
	while($thread = $db->fetch_array($query)) {
		$tpcdb[]=$thread;
	}
}

$threaddb=array();
foreach($tpcdb as $key => $thread){
	$foruminfo['allowhtm']==1 && $htmurl=$htmdir.'/'.$fid.'/'.date('ym',$thread['postdate']).'/'.$thread['tid'].'.html';
	$thread['tpcurl']="read.php?tid=$thread[tid]&fpage=$page";
	if($managemode==1){
		$thread['tpcurl'].='&toread=1';
	} elseif(!$foruminfo['cms'] && $foruminfo['allowhtm']==1 && file_exists(R_P.$htmurl)){
		$thread['tpcurl']="$htmurl";
	}
	$forumset['cutnums'] && $thread['subject'] = substrs($thread['subject'],$forumset['cutnums']);
	if ($thread['titlefont']){
		$titledetail=explode("~",$thread['titlefont']);
		if($titledetail[0])$thread['subject']="<font color=$titledetail[0]>$thread[subject]</font>";
		if($titledetail[1])$thread['subject']="<b>$thread[subject]</b>";
		if($titledetail[2])$thread['subject']="<i>$thread[subject]</i>";
		if($titledetail[3])$thread['subject']="<u>$thread[subject]</u>";
	}
	if($thread['ifmark']){
		$thread['ifmark']=$thread['ifmark']>0 ? " ( +$thread[ifmark] ) " : " ( $thread[ifmark] ) ";
	} else{
		unset($thread['ifmark']);
	}
	if($thread['pollid']&&$thread['locked']==0){
		$thread['status']="<img src='$imgpath/$stylepath/thread/vote.gif' border=0>";
	} elseif($thread['pollid']&&$thread['locked']>0){
		$thread['status']="<img src='$imgpath/$stylepath/thread/votelock.gif' border=0>";
	} else{
		if ($thread['locked']==1){
			$thread['status']="<img src='$imgpath/$stylepath/thread/topiclock.gif' border=0>";
		} elseif ($thread['locked']==2){
			$thread['status']="<img src='$imgpath/$stylepath/thread/topicclose.gif' border=0>";
		} elseif ($thread['replies']>=10){
			$thread['status']="<img src='$imgpath/$stylepath/thread/topichot.gif' border=0>";
		} else{
			$thread['status']="<img src='$imgpath/$stylepath/thread/topicnew.gif' border=0>";
		}
	}
	if($thread['topped'] && $thread['toolinfo'] != '' && $thread['toolfield']){
		if($timestamp > $thread['toolfield']){
			$db->update("UPDATE pw_threads SET toolinfo='',toolfield=0,topped=0 WHERE tid='$thread[tid]'");
		}
	}
	$thread['topped'] && $ifsort=1;

	$thread['ispage']='';
	if ($thread['replies']+1>$db_readperpage)
	{
		if (($thread['replies']+1)%$db_readperpage==0){
			$numofpage=($thread['replies']+1)/$db_readperpage;
		} else{
			$numofpage=floor(($thread['replies']+1)/$db_readperpage)+1;
		}
		$thread['ispage']=' ';
		$thread['ispage'].="[ <img src='$imgpath/$stylepath/file/multipage.gif' border=0><span style='font-size:7pt;font-family:verdana;'>";
		for ($j=1; $j<=$numofpage; $j++){
			if ($j==6 && $j+1<$numofpage){
				$thread['ispage'].=" .. <a href='read.php?tid=$thread[tid]&page=$numofpage&fpage=$page'>$numofpage</a>";
				break;
			}
			$thread['ispage'].=" <a href='read.php?tid=$thread[tid]&page=$j&fpage=$page'>$j</a>";
		}
		$thread['ispage'].='</span> ]';
	}
	$thread['postdate']=get_date($thread['postdate'],'Y-m-d');
	$postdetail=explode(",",$thread['lastpost']);

	if($thread['ifupload']){
		$atype=$attachtype[$thread['ifupload']];
		$thread['titleadd']=" <img src='$imgpath/$stylepath/file/$atype.gif' align='absbottom' border=0>";
	} else{
		$thread['titleadd']="";
	}

	$thread['lstptime']=get_date($thread['lastpost']);
	if ($admincheck){
		if($thread['fid']==$fid){
			$thread['adminbox']="<input type='checkbox' name='tidarray[]' value=$thread[tid]>";
		} else{
			$thread['adminbox']="<input type='checkbox' name='tidarray[]' value=$thread[tid] disabled>";
		}
	}
	if($db_threademotion){
		if ($thread['icon']=="R"||!$thread['icon']){
			$thread['useriocn']='';
		} else{
			$thread['useriocn']="<img src='$imgpath/post/emotion/$thread[icon].gif' border=0> ";
		}
	}
	$threaddb[]=$thread;
}
$db->free_result($query);

@include_once(D_P.'data/bbscache/forumcache.php');

if($db_threadshowpost==1 && $groupid!='guest'){
	if($db_signwindcode==1){
		$windcode="<br><a href='faq.php?faqjob=1#5'> Wind Code Open</a>";
		if ($db_windpic['pic']==1){
			$windcode.="<br> [img] - Open";
		} else{
			$windcode.="<br> [img] - Close";
		}
		if ($db_windpic['flash']==1){
			$windcode.="<br> [flash] - Open";
		} else{
			$windcode.="<br> [flash] - Close";
		}
	}else{
		$windcode="<br><a href='faq.php?faqjob=1#5'>Wind Code Close</a>";
	}
	$htmlpost=($foruminfo['allowhide'] && $gp_allowhidden ? '':"disabled");
	$htmlhide=($foruminfo['allowencode'] && $gp_allowencode ? '':"disabled");
	$htmlsell=($foruminfo['allowsell'] && $gp_allowsell ? '':"disabled");
	unset($titletop1);
	$fastpost='fastpost';
}
require_once PrintEot('thread');footer();
?>