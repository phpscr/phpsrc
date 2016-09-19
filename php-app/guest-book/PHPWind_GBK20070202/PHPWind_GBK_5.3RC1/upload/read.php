<?php
define('SCR','read');
require_once('global.php');
require_once(R_P.'require/forum.php');
require_once(R_P.'require/bbscode.php');
include_once(D_P.'data/bbscache/cache_read.php');

$fieldadd=$tablaadd=$fastpost=$special='';
foreach($customfield as $key=>$val){
	$val['id'] = (int) $val['id'];
	$fieldadd .= ",mb.field_$val[id]";
}
$fieldadd && $tablaadd="LEFT JOIN pw_memberinfo mb ON mb.uid=t.authorid";
$fpage = (int)$fpage;
if($page>1){
	$S_sql=$J_sql='';
}else{
	$page<1 && $page!='e' && $page=1;
	$start_limit = 0;
	$S_sql=',tm.*,m.uid,m.username,m.gender,m.oicq,m.kf,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.showsign,m.regdate,m.signchange,m.medals,m.payemail,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.onlinetime,md.starttime';
	$J_sql='LEFT JOIN pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid';
}
$read = $db->get_one("SELECT t.* $S_sql $fieldadd FROM pw_threads t $J_sql $tablaadd WHERE t.tid='$tid'");
!$read && Showmsg('illegal_tid');

$fid      = $read['fid'];
$ptable   = $read['ptable'];
$pw_posts = GetPtable($ptable);
$advertdb = AdvertInit(SCR,$fid);
if(is_array($advertdb['header'])){
	$header_ad = $advertdb['header'][array_rand($advertdb['header'])]['code'];
}
if(is_array($advertdb['footer'])){
	$footer_ad = $advertdb['footer'][array_rand($advertdb['footer'])]['code'].'<br />';
}
list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);

$foruminfo = $db->get_one("SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid'");
$forumset  = unserialize($foruminfo['forumset']);
!$foruminfo && Showmsg('data_error');
wind_forumcheck($foruminfo);
$subject  = $read['subject'];
$authorid = $read['authorid'];
if(!$foruminfo['allowvisit'] && $gp_allowread==0 && $_COOKIE){
	Showmsg('read_group_right');
}
unset($advertdb['header'],$advertdb['footer'],$S_sql,$J_sql,$foruminfo['forumset']);

$rewardtype=0; /*** 悬赏 ***/

$ajaxcheck = $groupid == 3 ? 1 : 0;
if($windid == $manager){
	$admincheck=1;
	$ajaxcheck=1;
} elseif(admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
	$admincheck=1;
	$ajaxcheck=1;
} else{
	$admincheck=0;
}
!$windid && $admincheck=0;
if($groupid != 3 && !$foruminfo['allowvisit'] && !admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
	forum_creditcheck();
}
if($winddb['p_num']){
	$db_readperpage = $winddb['p_num'];
} elseif($forumset['readnum']){
	$db_readperpage = $forumset['readnum'];
}

$count= $read['replies']+1;
if($count%$db_readperpage==0){
	$numofpage=$count/$db_readperpage;
} else{
	$numofpage=floor($count/$db_readperpage)+1;
}
if($page=='e' || $page>$numofpage){
	$numofpage==1 && $page>1 && ObHeader("read.php?tid=$tid&page=1&toread=$toread");
	$page=$numofpage;
}
$page > 1 && $ajaxcheck=0;

require_once(R_P.'require/header.php');
Update_ol();
$readdb    = array();
$authorids = $tpc_ifview = ''; /*** 控制隐藏贴次数 ***/

if($read['special']==3 && $read['rewardinfo'] && $foruminfo['allowreward']){
	require_once(R_P.'require/readrew.php');
}
if($page==1){
	$foruminfo['allowhtm']==1 && $htmurl=$htmdir.'/'.$fid.'/'.date('ym',$read['postdate']).'/'.$read['tid'].'.html';
	if($foruminfo['allowhtm']==1  && !$foruminfo['cms'] && !$toread && file_exists(R_P.$htmurl)){
		ObHeader("$R_url/$htmurl");
	}
	$read['pid'] = 'tpc';
	$readdb[]    = viewread($read,0);
	$authorids   = $read['authorid'];
}
$pages=numofpage($count,$page,$numofpage,"read.php?tid=$tid&fpage=$fpage&toread=$toread&");//文章数,页码,共几页

if($read['ifcheck']==0 && $foruminfo['f_check'] && !$admincheck && !$SYSTEM['viewcheck'] && $windid!=$read['author']){
	Showmsg('read_check');
}
if($read['locked']==2 && !$admincheck && !$SYSTEM['viewclose']){
	Showmsg('read_locked');
}

$tpc_locked=$read['locked']>0 ? 1 : 0;

if(!$db_hithour){
	$db->update("UPDATE pw_threads SET hits=hits+1 WHERE tid='$tid'");
} else{
	writeover(D_P."data/bbscache/hits.txt",$tid."\t",'ab');
}
$favortitle=str_replace(array("&#39;","'","\"","\\"),array("‘","\\'","\\\"","\\\\"),$subject);

$guidename=forumindex($foruminfo['fup']);
$guidename[$fid]=array($subject,"read.php?tid=$tid");
list($msg_guide,$forumlist) = headguide($guidename);
unset($fourm,$guidename);
$db_bbsname_a=addslashes($db_bbsname);//模版内用到

if($read['special']==1 && ($page==1 || $numofpage==1)){
	require_once(R_P.'require/readvote.php');
}elseif($read['special']==2 && $forumset['allowactive'] && ($page==1 || $numofpage==1)){
	require_once(R_P.'require/readact.php');
}

if($read['replies']>0){
	$start_limit=($page-1)*$db_readperpage;
	if($page==1){
		$readnum=$db_readperpage-1;
	} else{
		$readnum=$db_readperpage;
		$start_limit-=1;
	}
	$order = $rewardtype=='2' ? "ifreward DESC,postdate" : "postdate";
	
	$query = $db->query("SELECT t.*,m.uid,m.username,m.gender,m.oicq,m.kf,m.groupid,m.memberid,m.icon AS micon,m.hack,m.honor,m.signature,m.regdate,m.signchange,m.medals,m.showsign,m.payemail,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.onlinetime,md.starttime $fieldadd FROM $pw_posts t LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid $tablaadd WHERE t.tid='$tid' AND t.ifcheck='1' ORDER BY $order LIMIT $start_limit, $readnum");
	
	$start_limit++;

	while($read=$db->fetch_array($query)){
		$readdb[]   = viewread($read,$start_limit);
		$authorids .= $authorids ? ','.$read['authorid'] : $read['authorid'];
		$start_limit++;
	}
	$db->free_result($query);
}
unset($sign,$ltitle,$lpic,$lneed,$gp_right,$_MEDALDB,$fieldadd,$tablaadd,$read,$order,$readnum);

if($db_showcolony && $authorids){
	$colonydb=array();
	$query = $db->query("SELECT c.uid,cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid IN($authorids) AND c.ifadmin!='-1'");
	while($rt = $db->fetch_array($query)){
		if(!$colonydb[$rt['uid']]){
			$colonydb[$rt['uid']] = $rt;
		}
	}
	$db->free_result($query);
}
if($db_showcustom && $authorids){
	$customdb=array();
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
		$db->free_result($query);
	}
}
if($groupid!='guest' && !$tpc_locked){
	if($db_signwindcode==1){
		$windcode  = '<br /><a href="faq.php?faqjob=1#5"> Wind Code Open</a><br /> [img] - ';
		$windcode .= $db_windpic['pic']==1 ? 'Open' : 'Close';
		$windcode .= '<br /> [flash] - ';
		$windcode .= $db_windpic['flash']==1 ? 'Open' : 'Close';
	} else{
		$windcode  = '<br /><a href="faq.php?faqjob=1#5">Wind Code Close</a>';
	}
	$htmlpost = $foruminfo['allowhide'] && $gp_allowhidden ? '' : "disabled";
	$htmlhide = $foruminfo['allowencode'] && $gp_allowencode ? '' : "disabled";
	$htmlsell = $foruminfo['allowsell'] && $gp_allowsell ? '' : "disabled";
	$psot_sta = 'reply';//control the faster reply
	$titletop1= substrs('Re:'.$subject,$db_titlemax);
	$fastpost = 'fastpost';
	$db_forcetype = 0;
}
require_once(PrintEot('read'));footer();

function viewread($read,$start_limit){
	global $SYSTEM,$_G,$groupid,$admincheck,$attach_url,$attachper,$winduid,$tablecolor,$tpc_author,$tpc_buy,$tpc_pid,$count,$timestamp,$db_onlinetime,$attachdir,$attachpath,$gp_allowloadrvrc,$readcolorone,$readcolortwo,$lpic,$ltitle,$imgpath,$db_ipfrom,$db_showonline,$stylepath,$db_windpost,$db_windpic,$db_signwindcode,$fid,$tid,$pid,$attachments,$aids,$md_ifopen,$_MEDALDB,$rewardtype,$db_shield;
	$read['lou']    = $start_limit;
	$read['jupend'] = $start_limit==$count-1 ? "<a name=a></a><a name=$read[pid]></a>" : "<a name=$read[pid]></a>";
	//$read['colour'] = $start_limit%2==0 ? $readcolorone : $readcolortwo;
	$tpc_buy=$read['buy'];
	$tpc_pid=$read['pid'];

	$read['ifsign']<2 && $read['content']=str_replace("\n","<br />",$read['content']);
	$anonymous=($read['anonymous'] && !$SYSTEM['viewhide'] && !$admincheck && $winduid!=$read['authorid']) ? 1 : 0;

	if($read['groupid']!='' && $anonymous==0){
		$read['groupid']=='-1' && $read['groupid']=$read['memberid'];
		!array_key_exists($read['groupid'],$lpic) && $read['groupid'] = 8;
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

		if($read['ifsign']==1 || $read['ifsign']==3){
			global $sign;
			if(!$sign[$read['author']]){			
				global $db_signmoney,$db_signgroup,$tdtime;
				if(strpos($db_signgroup,",$read[groupid],") !== false && $db_signmoney && (!$read['showsign'] ||  (!$read['starttime'] || $read['currency'] < (($tdtime-$read['starttime'])/86400)*$db_signmoney))){
					$read['signature'] = '';
				} else{
					if($db_signwindcode && $read['signchange']==2){
						if($gp_right[$read['groupid']]['imgwidth'] && $gp_right[$read['groupid']]['imgheight']){
							$db_windpic['picwidth']  = $gp_right[$read['groupid']]['imgwidth'];
							$db_windpic['picheight'] = $gp_right[$read['groupid']]['imgheight'];
						}
						if($gp_right[$read['groupid']]['fontsize']){
							$db_windpic['size'] = $gp_right[$read['groupid']]['fontsize'];
						}
						$read['signature']=convert($read['signature'],$db_windpic,2);
					}
					$read['signature']=str_replace("\n","<br />",$read['signature']);
				}
				$sign[$read['author']]=$read['signature'];
			} else{
				$read['signature']=$sign[$read['author']];
			}
		} else{
			$read['signature'] = '';
		}
	} else{
		$read['face']="<br />";$read['lpic']='8';	$read['level']=$read['digests']=$read['postnum']=$read['money']=$read['regdate']=$read['lastlogin']=$read['aurvrc']=$read['credit']='*';
		if($anonymous){
			$read['signature']= $read['honor']=$read['medals']=$read['ipfrom']=$read['ip']='';
			$read['author']   = $GLOBALS['db_anonymousname'];
			$read['authorid'] = 0;
			foreach($GLOBALS['customfield'] as $key=>$val){
				$field="field_".(int)$val['id'];
				$read[$field] = '*';
			}
		}
	}
	$read['postdate']=get_date($read['postdate']);
	if($read['ifmark']){
		$markdb=explode("\t",$read['ifmark']);
		$markinfo="　";
		foreach($markdb as $key=>$value){
			$key && $key%4==0 && $markinfo.='<br />　';
			$markinfo.=$value.'　';
		}
		$read['mark']=$markinfo;
	} else{
		$read['mark']='';
	}

	$read['reward'] = '';
	if($rewardtype){
		if($read['lou']==0 || $rewardtype=='1' && ($winduid==$GLOBALS['authorid'] && $winduid!=$read['authorid']) || $rewardtype=='2' && $read['ifreward']){
			$read['reward'] = Getrewhtml($read['lou'],$read['ifreward'],$read['pid']);
		}
	}

	if($read['icon']){
		$read['icon']="<img src=\"$imgpath/post/emotion/$read[icon].gif\" align=\"left\" border=\"0\" />";
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
	if($read['ifshield']){ //单帖屏蔽
		$read['icon'] ='';
		$read['subject'] = $groupid=='3' ? shield('shield_title') : '';
		$groupid!='3' && $read['content'] = shield('shield_article');
	}elseif($read['groupid'] == 6 && $groupid != 3 && $db_shield){
		$read['subject'] = $read['icon'] ='';
		$read['content'] = shield('ban_article');
	}elseif($read['ifconvert']==2){
		$read['content'] = convert($read['content'],$db_windpost);
	}

	$GLOBALS['foruminfo']['copyctrl'] && $read['content'] = preg_replace("/<br \/>/eis","copyctrl()",$read['content']);

	if($attachper && $read['aid']){
		$attachments=array();
		$attachs= unserialize(stripslashes($read['aid']));
		if(is_array($attachs)){
			if($winduid==$read['authorid'] || $admincheck || ($groupid!=5 && $SYSTEM['delattach'])){
				$dfadmin=1;
			} else{
				$dfadmin=0;
			}
			foreach($attachs as $at){					
				if($at['type']=='img' && $at['needrvrc']==0){
					$a_url=geturl($at['attachurl'],'show');
					if($a_url=='imgurl'){
						$read['picurl'][$at['aid']]['0']=$attachments[$at['aid']]="<a href=\"job.php?action=showimg&tid={$tid}&pid={$read[pid]}&fid={$fid}&aid={$at[aid]}&verify=".md5("showimg{$tid}{$pid}{$fid}{$at[aid]}{$GLOBALS[db_hash]}")."\" target=\"_blank\">$at[name]</a>";
						$read['picurl'][$at['aid']]['1']=$dfadmin;
					}else{
						$dfurl='<br>'.cvpic($a_url[0],1,$db_windpost['picwidth'],$db_windpost['picheight']);
						$read['pic'][$at['aid']]=array($at['aid'],$dfurl,$dfadmin,$at['desc']);
						$attachments[$at['aid']]="<b>$at[desc]</b>$dfurl";
					}
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
				if($read['picurl'][$value]){
					unset($read['picurl'][$value]);
				}
			}
		}
	}
	/**
	* convert the post content
	*/
	$read['alterinfo'] && $read['content'].="<br /><br /><br /><font color=\"gray\">[ $read[alterinfo] ]</font>";
	if($read['remindinfo']){
		$remind=explode("\t",$read['remindinfo']);
		$remind[0]=str_replace("\n","<br />",$remind[0]);
		$remind[2]=get_date($remind[2]);
		$read['remindinfo']=$remind;
	}
	if($GLOBALS['keyword']){
		$keywords=explode("|",$GLOBALS['keyword']);
		foreach($keywords as $key => $value){
			$read['content'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($value,'/').")([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$)/siU","<u><font color=\"red\">\\1</font></u>\\2",$read['content']);
		}
	}
	return $read;
}
?>