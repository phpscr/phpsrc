<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=creathtm&type=$type";

$sqladd = "WHERE type<>'category' AND allowvisit='' AND f_type!='hidden' AND cms='0'";
if(!$action){
	@include_once(D_P.'data/bbscache/forumcache.php');
	$num=0;
	$forumcheck="<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
	

	$query=$db->query("SELECT fid,name,allowhtm FROM pw_forums $sqladd");
	while($rt=$db->fetch_array($query)){
		$num++;
		$htm_tr = $num % 5 == 0 ? '</tr><tr>' : '';
		$checked = $rt['allowhtm'] ? 'checked' : $checked='';
		$forumcheck.="<td><input type='checkbox' name='selid[]' value='$rt[fid]' $checked>$rt[name]</td>$htm_tr";
	}
	$forumcheck.="</tr></table>";
	if($db_plist){
		$p_table="<option value=\"0\">post</option>";
		$p_list=explode(',',$db_plist);
		foreach($p_list as $key=>$val){
			$p_table .= "<option value=\"$val\">post$val</option>";
		}
		$p_table=str_replace("<option value=\"$db_ptable\">","<option value=\"$db_ptable\" selected>",$p_table);
	}
	include PrintEot('creathtm');exit;
} elseif($_POST['action']=='submit'){
	$selid = checkselid($selid);
	if($selid === false){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');
	} elseif ($selid == ''){
		$db->update("UPDATE pw_forums SET allowhtm='0' $sqladd");
	} elseif ($selid){
		$db->update("UPDATE pw_forums SET allowhtm='1' $sqladd AND fid IN($selid)");
		$db->update("UPDATE pw_forums SET allowhtm='0' $sqladd AND fid NOT IN($selid)");
	}
	updatecache_f();
	adminmsg('operate_success');
} elseif($action=='creat'){
	$step && PostCheck($verify);
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	if(is_numeric($creatfid)){
		$rt=$db->get_one("SELECT allowhtm FROM pw_forums WHERE fid='$creatfid'");
		if(!$rt['allowhtm']){
			adminmsg('template_error');
		}
	}
	$output='readtpl';
	include_once Pcv(R_P."data/style/$db_defaultstyle.php");
	if(!file_exists(R_P."template/$tplpath/$output.htm")){
		$tplpath='wind';
	}
	$tid = '';
	require_once(R_P.'require/forum.php');
	include_once(R_P.'require/template.php');
	include_once(D_P.'data/bbscache/forum_cache.php');
	include_once(D_P.'data/bbscache/customfield.php');
	$fieldadd=$tableadd='';
	foreach($customfield as $key=>$val){
		$val['id'] = (int) $val['id'];
		$fieldadd .= ",mb.field_$val[id]"; 
	}
	$fieldadd && $tableadd="LEFT JOIN pw_memberinfo mb ON mb.uid=t.authorid";
	
	$imgpath	= $db_http	!= 'N' ? $db_http : $picpath;
	$attachpath	= $db_attachurl	!= 'N' ? $db_attachurl	: $attachname;
	$yeyestyle=='no' ? $i_table="bgcolor=$tablecolor" : $i_table='class=i_table';
	$url="read.php";
	
	$S_sql=',tm.*,m.uid,m.username,m.oicq,m.kf,m.groupid,m.memberid,m.icon AS micon,m.hack,m.honor,m.signature, m.showsign,m.payemail,m.regdate,m.signchange,m.medals,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit,p.voteopts,p.state,p.modifiable,p.previewable,p.timelimit';
	$J_sql='LEFT JOIN pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid LEFT JOIN pw_polls p ON p.tid=t.tid';
	!$step && $step=1;
	!$percount && $percount=100;
	$start=($step-1)*$percount;
	$next=$start+$percount;
	$step++;
	$j_url="$basename&action=$action&step=$step&percount=$percount&creatfid=$creatfid&fids=$fids";
	$goon=0;
	if($creatfid=='all'){
		$fids = '';
		$query = $db->query("SELECT fid FROM pw_forums $sqladd AND allowhtm='1'");
		while ($rt = $db->fetch_array($query)){
			$fids .= $ext.$rt['fid'];
			!$ext && $ext = ',';
		}
		if($fids){
			$creatadd=" WHERE ifcheck=1 AND t.fid IN('".str_replace(",","','",$fids)."')";
			unset($fids);
		} else{
			adminmsg('template_noforum');
		}
	} elseif(is_numeric($creatfid)){
		$creatadd="WHERE ifcheck=1 AND t.fid='$creatfid'";
	} else{
		adminmsg('forumid_error');
	}
	$creatadd .= " AND t.special NOT IN(2,3)";
	if($db_plist){
		$creatadd .= " AND t.ptable='$ptable'";
		$pw_posts = GetPtable($ptable);
		$j_url .= '&ptable='.$ptable;
	}else{
		$pw_posts = 'pw_posts';
	}
	$query=$db->query("SELECT t.* $S_sql $fieldadd FROM pw_threads t $J_sql $tableadd $creatadd ORDER BY fid DESC,topped DESC, lastpost DESC LIMIT $start,$percount");
	unset($creatadd);
	$topicdb=$articledb=array();
	$tids='';
	while($topic =$db->fetch_array($query)){
		if(!$topic['tid'])continue;
		$goon=1;
		is_numeric($topic['tid']) && $tids.=$topic['tid'].',';
		$topicdb[]=$topic;
	}
	$db->free_result($query);
	
	$tids=substr($tids,0,-1);
	if($tids){
		$readnum=$db_readperpage-1;
		$query = $db->query("SELECT t.*,m.uid,m.username,m.oicq,m.kf,m.groupid,m.memberid,m.icon AS micon,m.hack,m.honor,m.signature,m.showsign,m.payemail,m.regdate,m.signchange,m.medals,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit $fieldadd FROM $pw_posts t LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid $tableadd WHERE t.tid IN($tids) AND ifcheck=1 ORDER BY postdate");
		unset($tids);
		while($article=$db->fetch_array($query)){
			if(!is_array($articledb[$article['tid']]))settype($articledb[$article['tid']],'array');
			$articledb[$article['tid']][]=$article;
		}
		$db->free_result($query);
	}
	//ob_end_clean();
	include(D_P.'data/bbscache/advert_data.php');
	$padvertdb = $advertdb;
	foreach($topicdb as $key=>$read){
		$readdb=$votedb=$advertdb=array();
		$fid=$read['fid'];$tid=$read['tid'];
		
		$header_ad=$footer_ad='';
		$advertdb = AdvertInit('read',$fid);
		if(is_array($advertdb['header'])){
			$header_ad = $advertdb['header'][array_rand($advertdb['header'])]['code'];
		}
		if(is_array($advertdb['footer'])){
			$footer_ad = $advertdb['footer'][array_rand($advertdb['footer'])]['code'].'<br />';
		}
		$foruminfo=$db->get_one("SELECT * FROM pw_forums WHERE fid='$fid'");
		$date=date('ym',$read['postdate']);
		$page=1;
		$count =$read['replies']+1;
		if($read['special']==1){
			$modifiable = $read['modifiable'];
			$previewable= $read['previewable'];
			$vote_close = ($read['state'] || ($read['timelimit'] && $timestamp-$read['postdate']>$read['timelimit']*86400)) ? 1 : 0;
			$tpc_date=get_date($read['postdate']);
			$tpc_endtime = $read['timelimit'] ? get_date($read['postdate']+$read['timelimit']*86400) : 0;
			htmvote($read['voteopts']);
		}
		$readdb[]  = htmread($read,0);
		$authorids = $read['authorid'];
		unset($topicdb[$key]);
		$subject=$read['subject'];
		$tpctitle='- '.$subject;
		$favortitle=str_replace("&#39","‘",$subject);
		if($read['replies']>0){
			$floors=1;
			if($articledb[$tid]){
				foreach($articledb[$tid] as $key=>$read){
					$readdb[]=htmread($read,$floors);
					$authorids .=','.$read['authorid'];
					unset($articledb[$tid][$key]);
					$floors++;
					if($floors>9)break;
				}
			}
			unset($sign);
		}
		if($db_showcolony){
			$colonydb=array();
			$query = $db->query("SELECT c.uid,cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid IN($authorids) AND c.ifadmin!='-1'");
			while ($rt = $db->fetch_array($query)){
				if(!$colonydb[$rt['uid']]){
					$colonydb[$rt['uid']] = $rt;
				}
			}
		}
		if($db_showcustom){
			$customdb=array();
			@include(D_P.'data/bbscache/creditdb.php');
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
		$name=$forum[$fid]['name'];
		if ($count%$db_readperpage==0){ //$count $db_readperpage read.php?fid=$fid&tid=$tid&
			$numofpage=$count/$db_readperpage;
		} else{
			$numofpage=floor($count/$db_readperpage)+1;
		}
		$pages=numofpage($count,$page,$numofpage,"$url?fid=$fid&tid=$tid&");//文章数,页码,共几页,路径
		if(!is_dir(R_P.$htmdir.'/'.$fid)){
			@mkdir(R_P.$htmdir.'/'.$fid);
			@chmod(R_P.$htmdir.'/'.$fid,0777);
			writeover(R_P."$htmdir/$fid/index.html",'');
			@chmod(R_P."$htmdir/$fid/index.html",0777);
		}
		if(!is_dir(R_P.$htmdir.'/'.$fid.'/'.$date)){
			@mkdir(R_P.$htmdir.'/'.$fid.'/'.$date);
			@chmod(R_P.$htmdir.'/'.$fid.'/'.$date,0777);
			writeover(R_P."$htmdir/$fid/$date/index.html",'');
			@chmod(R_P."$htmdir/$fid/$date/index.html",0777);
		}
		ob_start();
		include Pcv(R_P."template/$tplpath/$output.htm");
		$content = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
		ob_end_clean();
		writeover(R_P."$htmdir/$fid/$date/$tid.html",$content,"rb+",0);
		@chmod(R_P."$htmdir/$fid/$date/$tid.html",0777);
	}
	if($goon){
		adminmsg('updatecache_step',EncodeUrl($j_url));
	} else{
		adminmsg('operate_success');
	}
} elseif($_POST['action']=='delete'){
	@include_once(D_P.'data/bbscache/forum_cache.php');
	if($creatfid=='all'){
		$handle = opendir(R_P.$htmdir.'/');
		while ($file = readdir($handle)) {
			if (($file!=".") && ($file!="..") && ($file!="")){
				if (is_dir(R_P.$htmdir.'/'.$file)){
					//cms
					if(!$forum[$file]['cms']){
						deldir(R_P.$htmdir.'/'.$file);
					}
					//cms
				}
			}
		}
	} elseif(is_numeric($creatfid)){
		deldir(R_P.$htmdir.'/'.$creatfid);
	} else{
		adminmsg('forumid_error');
	}
	adminmsg('operate_success');
}
function AdvertInit($SCR,$fid){
	global $timestamp,$padvertdb;
	$newadvert = array();
	foreach($padvertdb as $key=>$val){
		foreach($val as $k=>$v){
			if(($key=='header' || $key=='footer') && !$v['endtime'] || PwStrtoTime($v['endtime']) < $timestamp){
				continue;
			}
			if($SCR == 'read' && strpos(",$v[fid],",",-3,")!==false){
				$newadvert[$key][]=$v;
			}elseif(strpos(",$v[fid],",",-4,")!==false){
				$newadvert[$key][]=$v;
			}elseif($fid && strpos(",$v[fid],",",$fid,")!==false){
				$newadvert[$key][]=$v;
			}
		}
	}
	return $newadvert;
}
?>