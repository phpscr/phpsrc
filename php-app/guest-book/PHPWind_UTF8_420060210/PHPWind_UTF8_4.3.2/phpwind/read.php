<?php
define('SCR','read');
require_once('global.php');
include_once(D_P.'data/bbscache/level.php');
require_once(R_P.'require/forum.php');
require_once(R_P.'require/bbscode.php');

include_once(D_P.'data/bbscache/md_config.php');
if($md_ifopen){
	include_once(D_P.'data/bbscache/medaldb.php');
}
if ($page>1){
	$S_sql=$J_sql='';
}else{
	$page<1 && $page!='e' && $page=1;
	$start_limit = 0;
	$S_sql=',tm.*,p.voteopts,p.pollid,m.uid,m.username,m.gender,m.oicq,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.showsign,m.regdate,m.signchange,m.medals,m.payemail,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.onlinetime,md.starttime';
	$J_sql='LEFT JOIN pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid LEFT JOIN pw_polls p ON p.pollid=t.pollid';
}
$read = $db->get_one("SELECT t.* $S_sql FROM pw_threads t $J_sql WHERE t.tid='$tid'");
if(!$read){
	Showmsg('illegal_tid');
}
$fid=$read['fid'];
$advertdb = AdvertInit(SCR,$fid);
if(is_array($advertdb['header'])){
	$header_ad = $advertdb['header'][array_rand($advertdb['header'])]['code'];
}
if(is_array($advertdb['footer'])){
	$footer_ad = $advertdb['footer'][array_rand($advertdb['footer'])]['code'];
}
list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);

$foruminfo = $db->get_one("SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid'");
$forumset  = unserialize($foruminfo['forumset']);
if(!$foruminfo){
	require_once(R_P.'require/url_error.php');
}
wind_forumcheck($foruminfo);
$subject  = $read['subject'];
$authorid = $read['authorid'];
if(!$foruminfo['allowvisit'] && $gp_allowread==0 && $_COOKIE){
	Showmsg('read_group_right');
}

$ajaxcheck = $groupid == 3 ? 1 : 0;
if($windid == $manager){
	$admincheck=1;
	$ajaxcheck=1;
} elseif($foruminfo['forumadmin'] && strpos($foruminfo['forumadmin'],','.$windid.',')!==false){
	$admincheck=1;
	$ajaxcheck=1;
} else{
	$admincheck=0;
}
!$windid && $admincheck=0;
if ($windid != $manager && $groupid != 3 && !$foruminfo['allowvisit'] && (!$foruminfo['forumadmin'] || strpos($foruminfo['forumadmin'],','.$windid.',')===false)){
	list($db_moneyname,,$db_rvrcname,,$db_creditname,)=explode("\t",$db_credits);
	forum_creditcheck();
}
/*
$postright = $voteright = $replyright = 1;
if($foruminfo['allowpost'] && strpos($foruminfo['allowpost'],",$groupid,")===false && !$admincheck){
	$postright = 0;
}
if(!$foruminfo['allowpost'] && !$admincheck && $gp_allowpost==0){
	$postright = 0;
}
if($groupid == '7'){
	$postright = 0;
}
if(!$foruminfo['allowpost'] && !$admincheck && $gp_allownewvote==0){
	$voteright = 0;
}
if(!$foruminfo['allowrp'] && !$admincheck && $gp_allowrp==0){
	$replyright = 0;
}
*/
if ($winddb['p_num']){
	$db_readperpage = $winddb['p_num'];
} elseif ($forumset['readnum']){
	$db_readperpage = $forumset['readnum'];
}

$count= $read['replies']+1;
if ($count%$db_readperpage==0){
	$numofpage=$count/$db_readperpage;
} else{
	$numofpage=floor($count/$db_readperpage)+1;
}
if($page=='e' || $page>$numofpage){
	$numofpage==1 && $page>1 && ObHeader("read.php?tid=$tid&page=1&toread=$toread");
	$page=$numofpage;
}
$page > 1 && $ajaxcheck=0;

//帖子广告代码
if($db_article){
	foreach($db_article as $key => $value){
		if(@strtotime($value[1]) < $timestamp){
			unset($db_article[$key]);
		}
	}
}
//帖子广告代码

require_once(R_P.'require/header.php');
Update_ol();
$readdb    = array();
$authorids = '';
if($page==1){
	$foruminfo['allowhtm']==1 && $htmurl=$htmdir.'/'.$fid.'/'.date('ym',$read['postdate']).'/'.$read['tid'].'.html';
	if($foruminfo['allowhtm']==1  && !$foruminfo['cms'] && !$toread && file_exists(R_P.$htmurl)){
		ObHeader("$R_url/$htmurl");
	}
	$read['pid'] = 'tpc';
	$readdb[]    = viewread($read,0);
	$authorids   = $read['authorid'];
}

$pages=numofpage($count,$page,$numofpage,"read.php?tid=$tid&fpage=$fpage&toread=$toread&");//文章数,页码,共几页,路径

if($read['ifcheck']==0 && $foruminfo['f_check'] && !$admincheck && !$SYSTEM['viewcheck'] && $windid!=$read['author']){
	Showmsg('read_check');
}
if ($read['locked']==2 && !$admincheck && !$SYSTEM['viewclose']){
	Showmsg('read_locked');
}

$tpc_locked=$read['locked']>0 ? 1 : 0;

if(!$db_hithour){
	$db->update("UPDATE pw_threads SET hits=hits+1 WHERE tid='$tid'");
} else{
	writeover(D_P."data/bbscache/hits.txt",$tid."\t",'ab');
}
$favortitle=str_replace(array("&#39;","'","\"","\\"),array("‘","\\'","\\\"","\\\\"),$subject);

include_once(D_P.'data/bbscache/forum_cache.php');
$guidename=forumindex($foruminfo['fup']);
unset($fourm,$fastpost);
$guidename[$subject]="read.php?tid=$tid";
$msg_guide=headguide($guidename);
$db_bbsname_a=addslashes($db_bbsname);//模版内用到

$pollid=$read['pollid'];
if($pollid && ($page==1 || $numofpage==1) && $read['voteopts']){
	require_once(R_P.'require/readvote.php');
	$tpc_date=get_date($read['postdate']);
	vote($read['voteopts']);
}

if($read['replies']>0){
	unset($asc);
	$start_limit=($page-1)*$db_readperpage;
	if($page==1){
		$readnum=$db_readperpage-1;
	} else{
		$readnum=$db_readperpage;
		$start_limit-=1;
	}

	$query = $db->query("SELECT p.*,m.uid,m.username,m.gender, m.oicq, m.groupid,m.memberid,m.icon AS micon,m.hack,m.honor,m.signature,m.regdate,m.signchange,m.medals,m.showsign,m.payemail,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.onlinetime,md.starttime FROM pw_posts p LEFT JOIN pw_members m ON m.uid=p.authorid LEFT JOIN pw_memberdata md ON md.uid=p.authorid WHERE p.tid='$tid' AND p.ifcheck='1' ORDER BY postdate $asc LIMIT $start_limit, $readnum");
	
	$start_limit++;

	while($read=$db->fetch_array($query)){
		$readdb[]=viewread($read,$start_limit);
		if($authorids){
			$authorids .=','.$read['authorid'];
		}else{
			$authorids  = $read['authorid'];
		}
		$start_limit++;
	}
	$db->free_result($query);
	unset($sign,$read);
}
if($db_showcolony && $authorids){
	$colonydb=array();
	$query = $db->query("SELECT c.uid,cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid IN($authorids) ORDER BY id DESC");
	while ($rt = $db->fetch_array($query)){
		if(!$colonydb[$rt['uid']]){
			$colonydb[$rt['uid']] = $rt;
		}
	}
}
if($db_showcustom && $authorids){
	$customdb=array();
	@include_once(D_P.'data/bbscache/creditdb.php');
	$cids = $add = '';
	foreach($_CREDITDB as $key=>$value){
		if(strpos($db_showcustom,",$key,")!==false){
			$cids .= $add.$key;
			!$add && $add = ',';
		}
	}
	if($cids){
		$query = $db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid IN($authorids) AND cid IN($cids)");
		while ($rt = $db->fetch_array($query)){
			$customdb[$rt['uid']][$rt['cid']] = $rt['value'];
		}
	}
}
include_once(D_P.'data/bbscache/forumcache.php');
if($groupid!='guest' && !$tpc_locked){
	if($db_signwindcode==1){
		$windcode='<br><a href=\'faq.php?faqjob=1#5\'> Wind Code Open</a>';
		if ($db_windpic['pic']==1){
			$windcode.='<br> [img] - Open';
		} else{
			$windcode.='<br> [img] - Close';
		}
		if ($db_windpic['flash']==1){
			$windcode.='<br> [flash] - Open';
		} else{
			$windcode.='<br> [flash] - Close';
		}
	} else{
		$windcode='<br><a href=\'faq.php?faqjob=1#5\'>Wind Code</a>Close';
	}
	$htmlpost=($foruminfo['allowhide'] && $gp_allowhidden	? '' : 'disabled');
	$htmlhide=($foruminfo['allowencode'] && $gp_allowencode ? '' : 'disabled');
	$htmlsell=($foruminfo['allowsell'] && $gp_allowsell		? '' : 'disabled');
	$psot_sta='reply';
	$titletop1=substrs('Re:'.$subject,$db_titlemax);
	$fastpost='fastpost';
}
require_once(PrintEot('read'));footer();
function viewread($read,$start_limit){
	global $SYSTEM,$_G,$groupid,$admincheck,$attach_url,$attachper,$winduid,$tablecolor,$tpc_author,$tpc_buy,$count,$timestamp,$db_onlinetime,$attachdir,$attachpath,$gp_allowloadrvrc,$readcolorone,$readcolortwo,$lpic,$ltitle,$imgpath,$db_ipfrom,$db_showonline,$stylepath,$db_windpost,$db_windpic,$db_signwindcode,$fid,$tid,$pid,$attachments,$aids,$md_ifopen,$_MEDALDB;
	$read['lou']=$start_limit;
	$read['jupend']=$start_limit==$count-1 ? "<a name=a><a name=$read[pid]>" : "<a name=$read[pid]>";
	$tpc_buy=$read['buy'];
	if ($start_limit%2==0){
		$read['colour']=$readcolorone;
	} else{
		$read['colour']=$readcolortwo;
	}
	$read['ifsign']<2 && $read['content']=str_replace("\n","<br>",$read['content']);

	if($read['groupid']!=''){
		$read['groupid']=='-1' && $read['groupid']=$read['memberid'];
		!$lpic[$read['groupid']] && $read['groupid']=8;
		$read['lpic']=$lpic[$read['groupid']];
		$read['level']=$ltitle[$read['groupid']];
		$read['regdate']=get_date($read['regdate'],"Y-m-d");
		$read['lastlogin']=get_date($read['thisvisit'],"Y-m-d");
		$read['aurvrc']=floor($read['rvrc']/10);
		$read['author']=$read['username'];
		$tpc_author=$read['author'];
		$read['face']=showfacedesign($read['micon']);

		$read['ipfrom'] = $db_ipfrom == 1 && $_G['viewipfrom'] ? ' From:'.$read['ipfrom'] : '';
		if($SYSTEM['viewip']==1 || ($admincheck && $SYSTEM['viewip']==2)){
			$read['ip']="IP:$read[userip] |";
		}
		$read['ontime']=(int)($read['onlinetime']/3600);

		global $sign;
		if(!$sign[$read['author']]){
			if ($read['ifsign']==1 || $read['ifsign']==3){
				global $db_signmoney,$db_signgroup,$tdtime;
				if(strpos($db_signgroup,",$read[groupid],") !== false && $db_signmoney && (!$read['showsign'] ||  (!$read['starttime'] || $read['currency'] < (($tdtime-$read['starttime'])/86400)*$db_signmoney))){
					$read['signature'] = '';
				} else{
					if ($db_signwindcode && $read['signchange']==2){
						include_once(D_P.'data/bbscache/gp_right.php');
						if($gp_right[$read['groupid']]['imgwidth'] && $gp_right[$read['groupid']]['imgheight']){
							$db_windpic['picwidth']  = $gp_right[$read['groupid']]['imgwidth'];
							$db_windpic['picheight'] = $gp_right[$read['groupid']]['imgheight'];
						}
						if($gp_right[$read['groupid']]['fontsize']){
							$db_windpic['size'] = $gp_right[$read['groupid']]['fontsize'];
						}
						$read['signature']=convert($read['signature'],$db_windpic,2);
					}
					$read['signature']=str_replace("\n","<br>",$read['signature']);
				}
			} else {
				$read['signature'] = '';
			}
		}else{
			$read['signature']=$sign[$read['author']];
		}
		$sign[$read['author']]=$read['signature'];
	}else{
		$read['face']="<br>";$read['lpic']='8';
		$read['level']=$read['digests']=$read['postnum']=$read['money']=$read['regdate']=$read['lastlogin']=$read['aurvrc']='*';
	}
	$read['postdate']=get_date($read['postdate']);
	if($read['ifmark']){
		$markdb=explode("\t",$read['ifmark']);
		$markinfo="<tr>";
		foreach($markdb as $key=>$value){
			$markinfo.='<td>'.$value.'</td>';
			$key++;
			if($key%3==0)$markinfo.='</tr><tr>';
		}
		$markinfo.='</tr>';
		$read['mark']=$markinfo;
	} else{
		$read['mark']='';
	}
	if($read['icon']){
		$read['icon']="<img src='$imgpath/post/emotion/$read[icon].gif' align=left border=0>";
	} else{
		$read['icon']='';
	}
	if($md_ifopen && $read['medals']){
		$medals='';
		$md_a=explode(',',$read['medals']);
		foreach($md_a as $key=>$value){
			if($value){
				$medals.="<img src=\"$imgpath/medal/{$_MEDALDB[$value][picurl]}\" alt=\"{$_MEDALDB[$value][name]}\"> ";
			}
		}
		$read['medals']=$medals;
	}else{
		$read['medals']='';
	}
	/*
	*  control the attachs hide
	*/
	$attachper=1;
	if($read['groupid'] == 6 && $groupid != 3){
		$attachper = 0;
		$read['subject'] = $read['icon'] ='';
		$read['content'] =  "<span style='color:black;background-color:#ffff66'>No permission to view this article</span>";
	}elseif($read['ifconvert']==2){
		$read['content']=convert($read['content'],$db_windpost);
	}

	$GLOBALS['foruminfo']['copyctrl'] && $read['content'] = preg_replace("/<br>/eis","copyctrl('$read[colour]')",$read['content']);

	if($attachper){
		$attachments=array();
		$downattach=$downpic='';
		if($read['aid']!=''){
			$attachs= unserialize(stripslashes($read['aid']));

			if(is_array($attachs)){
				if ($winduid==$read['authorid'] || $admincheck || ($groupid!=5 && $SYSTEM['delattach'])){
					$dfadmin=1;
				} else{
					$dfadmin=0;
				}
				foreach($attachs as $at){
					if(file_exists($attachdir.'/'.$at['attachurl'])){
						$a_url=$attachpath.'/'.$at['attachurl'];
					} elseif($attach_url){
						$a_url=$attach_url.'/'.$at['attachurl'];
					} else{
						continue;
					}
					if($at['type']=='img' && $at['needrvrc']==0){
						$dfurl='<br>'.cvpic($a_url,1,$db_windpost['picwidth'],$db_windpost['picheight']);
						$read['pic'][$at['aid']]=array($at['aid'],$dfurl,$dfadmin,$at['desc']);
						$attachments[$at['aid']]="<b>$at[desc]</b>$dfurl";
					} else{
						$read['downattach'][$at['aid']]=array($at['aid'],$at['name'],$at['size'],$at['hits'],$at['needrvrc'],$at['type'],$dfadmin,$at['desc']);
						$attachments[$at['aid']]="<a href='job.php?action=download&pid=$read[pid]&tid=$tid&aid=$at[aid]' target='_blank'>$at[name]</a>";
						$at['desc'] && $attachments[$at['aid']] = "<b>$at[desc]</b><br>".$attachments[$at['aid']];
					}
				}
				$aids=array();
				$read['content']=attachment($read['content']);
				foreach($aids as $key => $value){
					if($read['pic'][$value]){
						unset($read['pic'][$value]);
					}
					if($read['downattach'][$value]){
						unset($read['downattach'][$value]);
					}
				}
			}
		}
	}
	/**
	* convert the post content
	*/
	$read['alterinfo'] && $read['content'].="<br><br><br><font color=gray>[ $read[alterinfo] ]</font>";
	if($GLOBALS['keyword']){
		$keywords=explode("|",$GLOBALS['keyword']);
		foreach($keywords as $key => $value){
			$read['content'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($value,'/').")([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$)/siU","<u><font color=\"red\">\\1</font></u>\\2",$read['content']);
		}
	}
	return $read;
}
?>