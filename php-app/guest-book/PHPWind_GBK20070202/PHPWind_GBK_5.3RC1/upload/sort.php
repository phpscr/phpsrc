<?php
$wind_in='st';
require_once('global.php');
require_once(R_P.'require/header.php');
$groupid=='guest'&&	Showmsg('not_login');
!$gp_allowsort && Showmsg('sort_group_right');

$per=24;
$cachenum=20;
$cachetime='';

if(empty($action)){
	@include_once(D_P.'data/bbscache/olcache.php');
	$usertotal=$guestinbbs+$userinbbs;
	$bbsinfo=$db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
	$bbsinfo['higholtime']=get_date($bbsinfo['higholtime']);
	$rs=$db->get_one("SELECT SUM(fd.topic) as topic,SUM(fd.subtopic) as subtopic,SUM(fd.article) as article,SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
	$topic=$rs['topic']+$rs['subtopic'];
	$article=$rs['article'];
	$tposts=$rs['tposts'];
	if($bbsinfo['tdtcontrol']!=$tdtime){
		if($db_hostweb == 1){
			$db->update("UPDATE pw_bbsinfo SET yposts='$tposts',tdtcontrol='$tdtime' WHERE id=1");
			$db->update("UPDATE pw_forumdata SET tpost=0 WHERE tpost<>'0'");
		}
		if(file_exists(D_P.'data/bbscache/ip_cache.php')){
			P_unlink(D_P.'data/bbscache/ip_cache.php');
		}
	}
	require PrintEot('sort');footer();
} elseif($action=='ipstate'){
	!$type && $type='month';
	if($type=='month'){
		$c_month=date('Y-n');
		$c_year=is_numeric($year) ? $year : date('Y');
		$p_year=$c_year-1;
		$n_year=$c_year+1;
		$summip=0;
		$m_ipdb=array();
		$query=$db->query("SELECT month,sum(nums) as nums FROM pw_ipstates WHERE month like '$c_year%' group by month");
		while($rt=$db->fetch_array($query)){
			$summip+=$rt['nums'];
			$key=substr($rt['month'],strrpos($rt['month'],'-')+1);
			$rt['_month'] = str_replace('-','_',$rt['month']);
			$m_ipdb[$key]=$rt;
		}
		for($i=1;$i<=12;$i++){
			!$m_ipdb[$i] && $m_ipdb[$i]=array('month'=>$c_year.'-'.$i,'_month'=>$c_year.'_'.$i,'nums'=>'0');
		}
		ksort($m_ipdb);
	}elseif($type=='day'){
		$c_month=$month ? str_replace('_','-',$month) : date('Y-n');
		list($Y,$M)=explode('-',$c_month);
		if(!is_numeric($Y) || !is_numeric($M)){
			Showmsg('undefined_action');
		}
		if($M==1){
			$p_month=($Y-1).'_12';
			$n_month=$Y.'_2';
		}elseif($M==12){
			$p_month=$Y.'_11';
			$n_month=($Y+1).'_1';
		}else{
			$p_month=$Y.'_'.($M-1);
			$n_month=$Y.'_'.($M+1);
		}
		$sumip=0;
		$d_ipdb=array();
		$query=$db->query("SELECT day,nums FROM pw_ipstates WHERE month='$c_month' ORDER BY day");
		while($rt=$db->fetch_array($query)){
			$sumip+=$rt['nums'];
			$key=substr($rt['day'],strrpos($rt['day'],'-')+1);
			$d_ipdb[$key]=$rt;
		}
		for($i=1;$i<=date('t',PwStrtoTime($c_month.'-1'));$i++){
			!$d_ipdb[$i] && $d_ipdb[$i]=array('day'=>"$c_month-$i",'nums'=>'0');
		}
		ksort($d_ipdb);
	}
	require PrintEot('sort');footer();
} elseif($action=='online'){
	require_once(R_P.'require/forum.php');
	require_once(D_P.'data/bbscache/forum_cache.php');
	require_once(D_P.'data/bbscache/level.php');
	$ltitle['-1']='Member';
	$onlinedb=openfile(D_P.'data/bbscache/online.php');
	if(count($onlinedb)==1){
		$onlinedb=array();
	} else{
		unset($onlinedb[0]);
	}
	$online_A=$guest_A=array();
	foreach($onlinedb as $online){
		if(trim($online)){
			$detail=explode("\t",$online);
			$online_A[$online]=$detail[1];
		}
	}
	unset($onlinedb);
	@asort($online_A);
	$online_A=@array_keys($online_A);

	$guestdb=openfile(D_P.'data/bbscache/guest.php');
	if(count($guestdb)==1){
		$guestdb=array();
	} else{
		unset($guestdb[0]);
	}
	foreach($guestdb as $online){
		if(trim($online)){
			$detail=explode("\t",$online);
			$guest_A[$online]=$detail[1];
		}
	}
	unset($guestdb);
	@asort($guest_A);
	$guest_A=@array_keys($guest_A);
	$online_A = array_merge ($online_A, $guest_A);
	unset($guest_A);
	$count=count($online_A);
	if(!is_numeric($page) || $page<1){
		$page=1;
	}
	$numofpage=$count%$db_perpage==0 ? floor($count/$db_perpage) : floor($count/$db_perpage)+1;
	$numofpage && $page>$numofpage && $page=$numofpage;
	$pages=numofpage($count,$page,$numofpage,"sort.php?action=online&");
	$start=($page-1)*$db_perpage;
	$end=min($start+$db_perpage,$count);

	$threaddb=array();
	for($i=$start;$i<$end;$i++){
		if(!$online_A[$i]) continue;
		$thread=explode("\t",$online_A[$i]);
		if(count($thread)<10){
			$thread['username']='Guest';
			$thread['ip']=$windid!=$manager ? "-" : $thread[0];
			$thread['group']='Guest';
			$thread['action']=$thread[4];
			$thread['lasttime']=$thread[5];
			$thread[2]=str_replace('<FiD>','',$thread[2]);
			$forum[$thread[2]]['name'] && $thread['forum']="<a href='thread.php?fid=$thread[2]'>".$forum[$thread[2]]['name']."</a>";
			$thread['atc']=$thread[3];
		} else{
			$thread['username']=$thread[0];
			$thread['ip']=$windid!=$manager ? "-" : $thread[2];
			$thread['group']=$ltitle[$thread[5]];
			$thread['action']=$thread[6];
			$thread['lasttime']=$thread[7];
			$forum[$thread[3]]['name'] && $thread['forum']="<a href='thread.php?fid=$thread[3]'>".$forum[$thread[3]]['name']."</a>";
			$thread['atc']=$thread[4];
		}
		$threaddb[]=$thread;
	}
	require_once(PrintEot('sort'));footer();
} elseif($action=='member'){
	@set_time_limit(0);
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	$cachetime=@filemtime(D_P."data/bbscache/member_sort.php");
	if(!$per || !file_exists(D_P."data/bbscache/member_sort.php") || ($timestamp-$cachetime>$per*3600)){
		if(!$type){
			$_SORTDB=$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.postnum FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) ORDER BY md.postnum DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				if($memdb['postnum']){
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['post']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=digests",'update_cache');
		} elseif($type=='digests'){
			include(D_P."data/bbscache/member_tmp.php");
			$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.digests FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) ORDER BY md.digests DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				if($memdb['digests']){
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['digests']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=tpost",'update_cache');
		} elseif($type=='tpost'){
			include(D_P."data/bbscache/member_tmp.php");
			$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.todaypost FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) WHERE md.lastpost>'$tdtime' ORDER BY md.todaypost DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				if($memdb['todaypost']){
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['tpost']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=mpost",'update_cache');
		} elseif($type=='mpost'){
			include(D_P."data/bbscache/member_tmp.php");
			$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.monthpost FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) WHERE md.lastpost>'$montime' ORDER BY md.monthpost DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				if($memdb['monthpost']){
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['mpost']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=rvrc",'update_cache');
		} elseif($type=='rvrc'){
			include(D_P."data/bbscache/member_tmp.php");
			$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.rvrc FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) ORDER BY md.rvrc DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				$memdb['rvrc']=floor($memdb['rvrc']/10);
				if($memdb['rvrc']){
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['rvrc']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=money",'update_cache');
		} elseif($type=='money'){
			include(D_P."data/bbscache/member_tmp.php");
			$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.money FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) ORDER BY md.money DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				if($memdb['money']){
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['money']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=credit",'update_cache');
		} elseif($type=='credit'){
			include(D_P."data/bbscache/member_tmp.php");
			$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.credit FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) ORDER BY md.credit DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				if($memdb['credit']){
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['credit']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=onlinetime",'update_cache');
		} elseif($type=='onlinetime'){
			include(D_P."data/bbscache/member_tmp.php");
			$_sort=array();
			$query=$db->query("SELECT m.uid,m.username,md.onlinetime FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) ORDER BY md.onlinetime DESC LIMIT $cachenum");
			while($memdb=$db->fetch_array($query)){
				if($memdb['onlinetime']){
					$memdb['onlinetime']=floor($memdb['onlinetime']/3600);
					$_sort[]=$memdb;
				}
			}
			$_SORTDB['onlinetime']=$_sort;
			writeover(D_P.'data/bbscache/member_tmp.php',"<?php\r\n\$_SORTDB=".vvar_export($_SORTDB).";\r\n?>");
			refreshto("sort.php?action=member&type=custom",'update_cache');
		} elseif($type=='custom'){
			include(D_P."data/bbscache/member_tmp.php");
			include(D_P."data/bbscache/creditdb.php");
			foreach($_CREDITDB as $key=>$value){
				$_sort=array();
				$query=$db->query("SELECT mc.uid,username,value FROM pw_membercredit mc LEFT JOIN pw_members m ON m.uid=mc.uid WHERE mc.cid='$key' ORDER BY mc.value DESC LIMIT $cachenum");
				while($memdb=$db->fetch_array($query)){
					if($memdb['username'] && $memdb['value']){
						$_sort[]=array('uid'=>$memdb['uid'],'username'=>$memdb['username'],$key=>$memdb['value']);
					}
				}
				$_SORTDB[$key]=$_sort;
			}
			$MEMBERDB=savearray('_MEMBERDB',$_SORTDB);
			writeover(D_P.'data/bbscache/member_sort.php',"<?php\r\n".$MEMBERDB.'?>');
			P_unlink(D_P."data/bbscache/member_tmp.php");
			refreshto("sort.php?action=member",'update_cache');
		}
	}
	$cachetime=get_date($cachetime+$per*3600);
	require_once GetLang('sort');
	@include(D_P."data/bbscache/member_sort.php");
	@include(D_P."data/bbscache/creditdb.php");
	$_SORTDB=$_MEMBERDB;
	$show_url="profile.php?action=show&uid";
	require PrintEot('sort');footer();
} elseif($action=='forum'){
	$cachetime=@filemtime(D_P."data/bbscache/forum_sort.php");
	if(!$per || !file_exists(D_P."data/bbscache/forum_sort.php") || ($timestamp-$cachetime>$per*3600)){
		$_SORTDB=$_sort=array();
		$query=$db->query("SELECT f.fid,f.name,fd.topic FROM pw_forumdata fd LEFT JOIN pw_forums f USING(fid) WHERE f.password='' AND f.allowvisit='' AND f.f_type<>'hidden' ORDER BY fd.topic DESC LIMIT $cachenum");
		while($forums=$db->fetch_array($query)){
			if($forums['topic']){
				$_sort[]=$forums;
			}
		}
		$_SORTDB['topic']=$_sort;

		$_sort=array();
		$query=$db->query("SELECT f.fid,f.name,fd.article FROM pw_forumdata fd LEFT JOIN pw_forums f USING(fid) WHERE f.password='' AND f.allowvisit='' AND f.f_type<>'hidden' ORDER BY fd.article DESC LIMIT $cachenum");
		while($forums=$db->fetch_array($query)){
			if($forums['article']){
				$_sort[]=$forums;
			}
		}
		$_SORTDB['article']=$_sort;

		$_sort=array();
		$query=$db->query("SELECT f.fid,f.name,fd.tpost FROM pw_forumdata fd LEFT JOIN pw_forums f USING(fid) WHERE f.password='' AND f.allowvisit='' AND f.f_type<>'hidden' ORDER BY fd.tpost DESC LIMIT $cachenum");
		while($forums=$db->fetch_array($query)){
			if($forums['tpost']){
				$_sort[]=$forums;
			}
		}
		$_SORTDB['tpost']=$_sort;
		$FORUMDB=savearray('_FORUMDB',$_SORTDB);
		writeover(D_P.'data/bbscache/forum_sort.php',"<?php\r\n".$FORUMDB.'?>');
	}
	$cachetime=get_date($cachetime+$per*3600);
	require_once GetLang('sort');
	include(D_P."data/bbscache/forum_sort.php");
	$_SORTDB=$_FORUMDB;
	$show_url="thread.php?fid";
	require PrintEot('sort');footer();
} elseif($action=='article'){
	$cachetime=@filemtime(D_P."data/bbscache/article_sort.php");
	if(!$per || $timestamp-$cachetime>$per*3600){
		$_SORTDB=$_sort=array();
		$query=$db->query("SELECT t.tid,t.subject,t.replies,t.fid FROM pw_threads t LEFT JOIN pw_forums f ON t.fid=f.fid WHERE t.ifcheck='1' AND t.locked<'2' AND f.password='' AND f.allowvisit='' AND f.f_type<>'hidden' ORDER BY t.replies DESC LIMIT $cachenum");
		while($topic=$db->fetch_array($query)){
			if($topic['replies']){
				$topic['subject']=substrs($topic['subject'],25);
				$_sort[]=$topic;
			}
		}
		$_SORTDB['reply']=$_sort;

		$_sort=array();
		$query=$db->query("SELECT t.tid,t.subject,t.hits,t.fid FROM pw_threads t LEFT JOIN pw_forums f ON t.fid=f.fid WHERE t.ifcheck='1' AND t.locked<'2' AND f.password='' AND f.allowvisit='' AND f.f_type<>'hidden' ORDER BY t.hits DESC LIMIT $cachenum");
		while($topic=$db->fetch_array($query)){
			if($topic['hits']){
				$topic['subject']=substrs($topic['subject'],25);
				$_sort[]=$topic;
			}
		}
		$_SORTDB['hit']=$_sort;
		$_sort=array();
		$query=$db->query("SELECT t.tid,t.subject,t.digest,t.fid FROM pw_threads t LEFT JOIN pw_forums f ON t.fid=f.fid WHERE t.digest<>'0' AND t.ifcheck='1' AND t.locked<'2' AND f.password='' AND f.allowvisit='' AND f.f_type<>'hidden'  ORDER BY t.lastpost DESC LIMIT $cachenum");
		while($topic=$db->fetch_array($query)){
			$topic['subject']=substrs($topic['subject'],25);
			$_sort[]=$topic;
		}
		$_SORTDB['digest']=$_sort;
		$ARTICLEDB=savearray('_ARTICLEDB',$_SORTDB);

		writeover(D_P.'data/bbscache/article_sort.php',"<?php\r\n".$ARTICLEDB.'?>');
	}
	$cachetime=get_date($cachetime+$per*3600);
	require_once GetLang('sort');
	include(D_P.'data/bbscache/article_sort.php');
	include(D_P.'data/bbscache/forum_cache.php');
	$_SORTDB=$_ARTICLEDB;
	$show_url="read.php?tid";
	require PrintEot('sort');footer();
}elseif($action=='team'){
	list($db_moneyname,,$db_rvrcname,,$db_creditname,)=explode("\t",$db_credits);
	$cachetime=@filemtime(D_P."data/bbscache/team_sort.php");
	if(!$per || $timestamp-$cachetime>$per*3600){
		include_once(D_P.'data/bbscache/level.php');
		$gids=0;
		$query=$db->query("SELECT gid FROM pw_usergroups WHERE gptype='system' AND gid NOT IN(5,6,7)");
		while($rt=$db->fetch_array($query)){
			$gids.=','.$rt['gid'];
		}
		$teamdb=$tfdb=$fadmindb=$forumdb=$admin_a=array();
		$query=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE cms!='1' AND forumadmin!=''");
		while($rt=$db->fetch_array($query)){
			$fuids	 = explode(',',substr($rt['forumadmin'],1,-1));
			foreach($fuids as $key=>$val){
				if($val){
					$tfdb[$rt['fid']][]=$val;
					$admin_a[]=addslashes($val);
				}
			}
		}

		$admin_a=array_unique($admin_a);
		$fname = $admin_a ? "'".implode("','",$admin_a)."'" : '';
		$sql   = $fname ? " OR m.username IN($fname)" : '';
		
		$uids='';
		$query=$db->query("SELECT m.uid,m.username,m.groupid,m.groups,m.memberid,md.lastvisit,md.lastpost,md.postnum,md.rvrc,md.money,md.onlinetime,md.monoltime,md.monthpost FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.groupid IN($gids) $sql ORDER BY groupid");
		while($rt=$db->fetch_array($query)){
			$rt['leavedays']=floor(($timestamp-$rt['lastvisit'])/86400);
			$rt['lastpost']=get_date($rt['lastpost']);
			$rt['onlinetime']=round($rt['onlinetime']/3600,2);
			$rt['monoltime']=round($rt['monoltime']/3600,2);
			$rt['systitle']=$ltitle[$rt['groupid']];
			$rt['memtitle']=$ltitle[$rt['memberid']];
			$rt['rvrc']=floor($rt['rvrc']/10);
			if($rt['groupid']!=5 && $rt['groupid']!='-1'){
				$teamdb[]=$rt;
			}
			if($rt['groupid']==5 || strpos($rt['groups'],',5,')!==false){
				$fadmindb[$rt['uid']]=$rt;
				$uids .= $uids ? ','.$rt['uid'] : $rt['uid'];
			}
		}
		
		if($uids){
			$query=$db->query("SELECT SUM(hits) AS count,authorid FROM pw_threads WHERE postdate>'$montime' AND authorid IN($uids) GROUP BY authorid");
			while($rt=$db->fetch_array($query)){
				$fadmindb[$rt['authorid']]['hits']=$rt['count'];
			}
			$pw_posts=GetPtable($db_ptable);
			$query=$db->query("SELECT COUNT(*) AS count,authorid FROM $pw_posts WHERE postdate>'$montime' AND authorid IN($uids) GROUP BY authorid");
			while($rt=$db->fetch_array($query)){
				$fadmindb[$rt['authorid']]['post']=$rt['count'];
			}
		}
		foreach($tfdb as $fid=>$value){
			foreach($fadmindb as $key=>$val){
				if(in_array($val['username'],$value)){
					$forumdb[$fid][$val['uid']]=$val;
				}
			}
		}
		writeover(D_P.'data/bbscache/team_sort.php',"<?php\r\n\$teamdb=".vvar_export($teamdb).";\r\n\$forumdb=".vvar_export($forumdb).";\r\n?>");
	}else{
		include(D_P.'data/bbscache/team_sort.php');
	}
	$cachetime=get_date($cachetime+$per*3600);
	include_once(D_P.'data/bbscache/forum_cache.php');	

	require PrintEot('sort');footer();
}elseif($action=='admin'){
	$thismonth = get_date($timestamp,'n');
	if(!$month || !file_exists(D_P.'data/bbscache/admin_sort_'.$month.".php")){
		$month=$thismonth;
	}
	$cachetime=@filemtime(D_P."data/bbscache/admin_sort_".$month.".php");
	include_once(D_P.'data/bbscache/level.php');
	if((!$per || $timestamp-$cachetime>$per*3600) && $month==$thismonth){
		$gids=0;
		$query=$db->query("SELECT gid FROM pw_usergroups WHERE gptype='system' AND gid NOT IN(6,7)");
		while($rt=$db->fetch_array($query)){
			$gids.=','.$rt['gid'];
		}

		$admindb=array();
		$query=$db->query("SELECT uid,username,groupid,groups FROM pw_members WHERE groupid IN($gids) ORDER BY groupid");
		while($rt=$db->fetch_array($query)){
			$admindb[$rt['username']]=array('uid'=>$rt['uid'],'gid'=>$rt['groupid']);			
		}
		
		$query=$db->query("SELECT COUNT(*) AS count,username2 AS manager,type FROM pw_adminlog WHERE timestamp>'$montime' GROUP BY username2,type");
		while($rt=$db->fetch_array($query)){
			if(isset($admindb[$rt['manager']])){
				$admindb[$rt['manager']][$rt['type']]=$rt['count'];
			}
		}
		writeover(D_P."data/bbscache/admin_sort_".$month.".php","<?php\r\n\$admindb=".vvar_export($admindb).";\r\n?>");
	}
	$cachetime=get_date($cachetime+$per*3600);
	include Pcv(D_P."data/bbscache/admin_sort_".$month.".php");
	$fg=opendir(D_P.'data/bbscache/');
	while($othermonth=readdir($fg)) {
		if(eregi("^admin_sort_[1-9][0-2]?\.php$",$othermonth)){
			$year=get_date(filemtime(D_P.'data/bbscache/'.$othermonth),'Y');
			$othermonth=str_replace(array(".php","admin_sort_"),array("",""),$othermonth);
			$month_total.= $othermonth==$month ? "<b>&nbsp;$year-".$othermonth."</b>" : "&nbsp;<a href=\"sort.php?action=admin&month=$othermonth\">$year-".$othermonth."</a>";
		}
	}closedir($fg);
	require PrintEot('sort');footer();
}elseif($action=='delsort'){
	(!$month || !is_numeric($month) || $groupid!='3') && Showmsg('undefined_action');
	$verify != substr(md5($windid.$winduid.$groupid.$db_hash),0,8) && Showmsg('illegal_request');
	if(file_exists(D_P.'data/bbscache/admin_sort_'.$month.'.php')){
		P_unlink(D_P.'data/bbscache/admin_sort_'.$month.'.php');
	}
	refreshto("sort.php?action=admin",'operate_success');
}

function vvar_export($array,$c=1,$t='',$var=''){
	$c && $var="array(\r\n";
	$t.="  ";
	if(is_array($array)){
		foreach($array as $key => $value){
			$var.="$t'".addslashes($key)."'=>";
			if(is_array($value)){
				$var.="array(\r\n";
				$var=vvar_export($value,0,$t,$var);
				$var.="$t),\r\n";
			} else{
				$var.="'".addslashes($value)."',\r\n";
			}
		}
	}
	if($c){
		$var.=")";
	}
	return $var;
}

function savearray($name,$array){
	$arraydb="\$$name=array(\r\n\t\t";
	foreach($array as $key=>$value){
		$arraydb.="'".$key."'=>\narray(\r\n\t\t\t";
		foreach($value as $value1){
			$arraydb.='array(';
			foreach($value1 as $value2){
				$arraydb.='"'.addslashes($value2).'",';
			}
			$arraydb.="),\r\n\t\t\t";
		}
		$arraydb.="),\r\n\t\t";
	}
	$arraydb.=");\r\n";
	return $arraydb;
}
?>