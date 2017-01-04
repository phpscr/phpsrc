<?php
define('SCR','thread');
require_once('global.php');
require_once(R_P.'require/forum.php');
include_once(D_P.'data/bbscache/cache_thread.php');

$foruminfo = $db->get_one("SELECT f.*,fe.creditset,fe.forumset,fd.topic,fd.top1,fd.top2,a.aid,a.author,a.startdate,a.subject,a.content FROM pw_forums f LEFT JOIN pw_forumsextra fe ON fe.fid=f.fid LEFT JOIN pw_forumdata fd ON fd.fid=f.fid LEFT JOIN pw_announce a ON a.ffid=f.fid WHERE f.fid='$fid'");
$forumset  = unserialize($foruminfo['forumset']);
$forumset['newtime'] && $db_newtime=$forumset['newtime'];
$forumset['link'] && ObHeader($forumset['link']);
$foruminfo['type']=='category' && ObHeader("index.php?cateid=$fid");

$searchadd=$thread_children=$thread_online=$fastpost=$typeadd='';
!$foruminfo && Showmsg('data_error');
wind_forumcheck($foruminfo);

$forumname = strip_tags($foruminfo['name']);
require_once(R_P.'require/header.php');

$ajaxcheck = $groupid == 3 ? 1 : 0;
if($windid==$manager){
	$admincheck=1;
	$ajaxcheck=1;
} elseif(admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
	$admincheck=1;
	$ajaxcheck=1;
} elseif($groupid!=5 && ($SYSTEM['tpctype'] || $SYSTEM['check'] || $SYSTEM['typeadmin'] || $SYSTEM['delatc'] || $SYSTEM['moveatc'] || $SYSTEM['copyatc'] || $SYSTEM['topped'])){
	$admincheck=1;
} else{
	$admincheck=0;
}
!$windid && $admincheck = 0;
if($groupid != 3 && !$foruminfo['allowvisit'] && !admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
	list($db_moneyname,,$db_rvrcname,,$db_creditname,)=explode("\t",$db_credits);
	forum_creditcheck();
}

if($foruminfo['forumadmin']){
	$forumadmin=explode(",",$foruminfo['forumadmin']);
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
}else{
	$admin_T = array();
}

Update_ol();
if($db_threadonline==1){
	$trd_hide=$trd_nothide=0;
	$fp=@fopen(D_P.'data/bbscache/online.php',"rb");
	flock($fp,LOCK_SH);
	while(feof($fp)===false){
		$a=fread($fp,97);
		if(strpos($a,"\t$fid\t")!==false){//"\t$fid\t"
			$detail=explode("\t",$a);
			if($detail[3]==$fid){
				$img = strpos($db_showgroup,",".$detail[5].",")!==false ? $detail[5] : '6';
				if($detail[9]=='<>') {$trd_hide++; continue;} else $trd_nothide++;
				$trd_onlineinfo.="<td width=12%><img src='$imgpath/$stylepath/group/$img.gif' align='bottom'>&nbsp;<a href=profile.php?action=show&uid=$detail[8]>$detail[0]</a></td>";
				$trd_nothide%8==0 && $trd_onlineinfo.="</tr><tr>";
			}
		}
	}
	fclose($fp);
	$trd_sumonline=$trd_nothide+$trd_hide;
	$thread_online='thread_online';
}

$guidename = forumindex($foruminfo['fup']);
list($msg_guide,$forumlist) = headguide($guidename);
unset($forum,$guidename,$foruminfo['forumset']);

$db_maxpage && $page > $db_maxpage && $page=$db_maxpage;
(!is_numeric($page) || $page < 1) && $page=1;

$ifsort=0;
$NT_A=$NT_C=array();
if($page==1){
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
}
unset($notice_A,$notice_C);

if($foruminfo['aid']){
	$foruminfo['rawauthor']=rawurlencode($foruminfo['author']);
	$foruminfo['startdate']=get_date($foruminfo['startdate']);
	$foruminfo['content']=str_replace("\n","<br>",$foruminfo['content']);
}

if(strpos($_COOKIE['deploy'],"\t".thread."\t")===false){
	$thread_img ='fold';
	$cate_thread='';
}else{
	$thread_img ='open';
	$cate_thread='display:none;';
}
$forumdb = $t_typedb = array();
if($foruminfo['childid']){
	require_once(R_P."require/thread_child.php");
}

if($admincheck){
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
	$trd_adminhide="<form action=\"mawhole.php\" method=\"post\" name=\"mawhole\"><input type=\"hidden\" name=\"fid\" value=\"$fid\">";
} else{
	$trd_adminhide='';
}
$t_db = $foruminfo['t_type'];
$t_db && $t_typedb = array_unique(explode("\t",$t_db));
$t_per= (int)$t_typedb[0];
unset($t_typedb[0],$foruminfo['t_type']);/* 0 */

if($t_db && is_numeric($type) && isset($t_typedb[$type])){
	$searchadd=" AND type='$type' AND ifcheck='1'";
	$typeadd="type=$type&";
	$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_threads WHERE fid='$fid' $searchadd");
	$count=$rs['count'];
} elseif(is_numeric($special)){
	$searchadd=" AND special='$special' AND ifcheck='1'";
	$typeadd="special=$special&";
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
} else{
	$searchadd=" AND ifcheck='1'";
	$count=$foruminfo['topic'];
}
if($winddb['t_num']){
	$db_perpage = $winddb['t_num'];
} elseif($forumset['threadnum']){
	$db_perpage = $forumset['threadnum'];
}
if($winddb['p_num']){
	$db_readperpage = $winddb['p_num'];
} elseif($forumset['readnum']){
	$db_readperpage = $forumset['readnum'];
}

if($db_recycle && $fid==$db_recycle){
	$sql="fid='$fid'";
	$topadd='';
} elseif($db_topped){
	$sql="fid='$fid' AND topped<2";
	$topadd='topped DESC,';
	$count+=$foruminfo['top1'];
} else{
	$sql="fid='$fid'";
	$topadd='topped DESC,';
}

$tpcdb=$ordersel=$ascsel=array();
if($gp_alloworder){
	if(!in_array($orderway,array('lastpost','postdate','hits','replies'))){
		$orderway = $forumset['orderway'] ? $forumset['orderway'] : 'lastpost';
	}
	$ordersel[$orderway]='selected';
	if(!in_array($asc,array('DESC','ASC'))){
		$asc = $forumset['asc'] ? $forumset['asc'] : 'DESC';
	}
	$ascsel[$asc]='selected';
	$w_add = $orderway ? "orderway=$orderway&asc=$asc&" : '';
} else{
	$w_add='';
	$asc = $forumset['asc'] ? $forumset['asc'] : 'DESC';
	$orderway = $forumset['orderway'] ? $forumset['orderway'] : 'lastpost';
}

$numofpage=ceil($count/$db_perpage);
$totlepage=$numofpage;
if($numofpage && $page>$numofpage){
	$page=$numofpage;
}

$start_limit = ($page - 1) * $db_perpage;

$R=0;
if($db_topped){
	$rows=$foruminfo['top1'] + $foruminfo['top2'];
	if($start_limit < $rows){
		$L = min($rows-$start_limit,$db_perpage);
		$limit  = "LIMIT $start_limit,$L";
		$limit2 = $L == $db_perpage ? '' : "LIMIT 0,".($db_perpage-$L);
		include_once(D_P."data/bbscache/toppeddb.php");
		$toptids = $toppeddb['3'][1];
		if($toppeddb['2'][$cateid][1]){
			$toptids && $toptids .= ',';
			$toptids .= $toppeddb['2'][$cateid][1];
		}
		$query=$db->query("SELECT * FROM pw_threads WHERE tid IN($toptids) AND topped>1 ORDER BY topped DESC,lastpost DESC $limit");
		while($rt=$db->fetch_array($query)){
			$tpcdb[]=$rt;
		}
		$db->free_result($query);
		unset($toptids,$L,$limit,$toppeddb);
	} else{
		$limit2="LIMIT ".($start_limit-$rows).",$db_perpage";
	}
	unset($rows);
} else{
	$limit2="LIMIT $start_limit,$db_perpage";
}
$pages=numofpage($count,$page,$numofpage,"thread.php?fid=$fid&search={$search}&{$w_add}{$typeadd}",$db_maxpage);
$orderway .= ' '.$asc;
$attachtype=array(
	'1'=>'img',
	'2'=>'txt',
	'3'=>'zip'
);
if($limit2){
	$query = $db->query("SELECT * FROM pw_threads WHERE $sql $searchadd ORDER BY $topadd $orderway $limit2");
	while($thread = $db->fetch_array($query)){
		$tpcdb[]=$thread;
	}
	$db->free_result($query);
}

$threaddb=array();
foreach($tpcdb as $key => $thread){
	$foruminfo['allowhtm']==1 && $htmurl=$htmdir.'/'.$fid.'/'.date('ym',$thread['postdate']).'/'.$thread['tid'].'.html';
	$thread['tpcurl']="read.php?tid=$thread[tid]".($page>1 ? "&fpage=$page" : '');
	if($managemode==1){
		$thread['tpcurl'].='&toread=1';
	} elseif(!$foruminfo['cms'] && $foruminfo['allowhtm']==1 && file_exists(R_P.$htmurl)){
		$thread['tpcurl']="$htmurl";
	}
	$forumset['cutnums'] && $thread['subject'] = substrs($thread['subject'],$forumset['cutnums']);
	if($thread['titlefont']){
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
	if($thread['special']==1){
		$p_status = $thread['locked'] == 0 ? 'vote' : 'votelock';
	}elseif($thread['locked']>0){
		$p_status = $thread['locked'] == 1 ? 'topiclock' : 'topicclose';
	}else{
		$p_status = $thread['replies']>=10 ? 'topichot' : 'topicnew';
	}
	$thread['status'] = "<img src='$imgpath/$stylepath/thread/".$p_status.".gif' border=0>";

	if($thread['topped'] && $thread['toolinfo'] != '' && $thread['toolfield']){
		if($timestamp > $thread['toolfield']){
			$db->update("UPDATE pw_threads SET toolinfo='',toolfield=0,topped=0 WHERE tid='$thread[tid]'");
		}
	}
	$thread['topped'] && $ifsort=1;

	$thread['ispage']='';
	if($thread['replies']+1>$db_readperpage){
		if(($thread['replies']+1)%$db_readperpage==0){
			$numofpage=($thread['replies']+1)/$db_readperpage;
		} else{
			$numofpage=floor(($thread['replies']+1)/$db_readperpage)+1;
		}
		$thread['ispage']=' ';
		$thread['ispage'].="[ <img src='$imgpath/$stylepath/file/multipage.gif' border=0><span style='font-size:7pt;font-family:verdana;'>";
		for($j=1; $j<=$numofpage; $j++){
			if($j==6 && $j+1<$numofpage){
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
	if($admincheck){
		if($thread['fid']==$fid){
			$thread['adminbox']="<input type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" />";
		} else{
			$thread['adminbox']="<input type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" disabled />";
		}
	}
	if($db_threademotion){
		if($thread['icon']=="R"||!$thread['icon']){
			$thread['useriocn']='';
		} else{
			$thread['useriocn']="<img src='$imgpath/post/emotion/$thread[icon].gif' border=0> ";
		}
	}
	if($thread['anonymous'] && !$ajaxcheck && !$SYSTEM['viewhide'] && $thread['authorid']!=$winduid){
		$thread['author']=$db_anonymousname;
		$thread['authorid']=0;
	}
	$threaddb[]=$thread;
}
unset($tpcdb,$query,$topadd,$searchadd,$sql,$limit2,$p_status);

if($db_threadshowpost==1 && $groupid!='guest'){
	if($db_signwindcode==1){
		$windcode  = '<br /><a href="faq.php?faqjob=1#5"> Wind Code Open</a><br> [img] - ';
		$windcode .= $db_windpic['pic']==1 ? 'Open' : 'Close';
		$windcode .= '<br> [flash] - ';
		$windcode .= $db_windpic['flash']==1 ? 'Open' : 'Close';
	}else{
		$windcode  = '<br><a href="faq.php?faqjob=1#5">Wind Code Close</a>';
	}
	$htmlpost = $foruminfo['allowhide'] && $gp_allowhidden ? '' : "disabled";
	$htmlhide = $foruminfo['allowencode'] && $gp_allowencode ? '' : "disabled";
	$htmlsell = $foruminfo['allowsell'] && $gp_allowsell ? '' : "disabled";
	$psot_sta = $titletop1 = '';
	$fastpost = 'fastpost';
	$t_exits  = 0;
	if($t_typedb){
		foreach($t_typedb as $value){
			if($value) $t_exits=1;
		}
	}
	$db_forcetype = $t_exits && $t_per=='2' && !$admincheck ? 1 : 0; // 是否需要强制主题分类
}
require_once PrintEot('thread');footer();
?>