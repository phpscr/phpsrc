<?php
require_once('global.php');
if ($action=='delimg'){
	!$winduid && Showmsg('undefined_action');
	$imgdb=$db->get_one("SELECT icon FROM pw_members WHERE uid='$winduid'");
	$deldb=explode("|",$imgdb['icon']);
	Add_S($imgdb);
	if($imgdb){
		$deldb=explode("|",$imgdb['icon']);
		if($deldb[1] && !ereg("^http",$deldb[1])&& strpos($deldb[1],'..')===false){
			P_unlink("$imgdir/upload/$deldb[1]");
			$db->update("UPDATE pw_members SET icon='$deldb[0]' WHERE uid='$winduid'");
		} else{
			Showmsg('job_delimg_error');
		}
	}
	refreshto("profile.php?action=modify",'operate_success');
}elseif($action=="showping"){
	require_once(R_P.'require/msg.php');
	require_once(R_P.'require/forum.php');
	include_once(D_P.'data/bbscache/forum_cache.php');

	if(!is_numeric($pid)){
		$atc=$db->get_one("SELECT fid,author,authorid,postdate,subject,tm.ifmark,credit FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid LEFT JOIN pw_memberinfo m ON m.uid='$winddb[uid]' WHERE t.tid='$tid'");
	} else{
		$atc=$db->get_one("SELECT fid,author,authorid,postdate,subject,ifmark,credit,content FROM pw_posts p LEFT JOIN pw_memberinfo m ON m.uid='$winddb[uid]' WHERE pid='$pid'");
	}
	Add_S($atc);
	$fid=$atc['fid'];
	$foruminfo=$db->get_one("SELECT name,f_type,style,password,allowvisit,cms,forumadmin FROM pw_forums WHERE fid='$fid' AND type<>'category'");
	if(!$foruminfo){
		require_once(R_P.'require/url_error.php');
	}
	wind_forumcheck($foruminfo);
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	list($maxcredit,$minper,$maxper,$credittype)=explode("|",$_G['markdb']);
	!$minper && $minper=0;
	!$maxper && $maxper=0;
	if(($windid != $manager && strpos($foruminfo['forumadmin'],','.$windid.',')===false && !$_G['markable']) || !$credittype || ($minper==0 && $maxper==0)){
		Showmsg('no_markright');
	}

	if(!$atc['author']){
		require_once(R_P.'require/url_error.php');
	}
	if ($winduid==$atc['authorid'] && $windid != $manager){
		Showmsg('masigle_manager');
    }
	$creditselect='';
	if(strpos($credittype,'rvrc')!==false){
		$creditselect='<option value=rvrc>'.$db_rvrcname.'</option>';
	}
	if(strpos($credittype,'money')!==false){
		$creditselect.='<option value=money>'.$db_moneyname.'</option>';
	}
	if(strpos($credittype,'credit')!==false){
		$creditselect.='<option value=credit>'.$db_creditname.'</option>';
	}
	$cid && $cid!='rvrc' && $cid!='money' && $cid!='credit' && !is_numeric($cid) && Showmsg('credit_error');
	if($cid=='rvrc'){
		$name=$db_rvrcname;
		$unit=$db_rvrcunit;
	} elseif($cid=='money'){
		$name=$db_moneyname;
		$unit=$db_moneyunit;
	}	elseif($cid=='credit'){
		$name=$db_creditname;
		$unit=$db_creditunit;
	} else{
		$name=$unit='';
	}
	$query=$db->query("SELECT cid,name,unit FROM pw_credits");
	while($creditdb=$db->fetch_array($query)){
		$key=$creditdb['cid'];
		if(strpos($credittype,','.$key.',')!==false){
			$creditselect.="<option value='$key'>$creditdb[name]</option>";
		}
		if($key==$cid){
			$name=$creditdb['name'];
			$unit=$creditdb['unit'];
		}
	}
	unset($creditdb);
	require_once(R_P.'require/header.php');
	if ($_POST['step'] != 1){
		$reason_sel='';
		$reason_a=explode("\n",$db_adminreason);
		foreach($reason_a as $k=>$v){
			if($v=trim($v)){
				$reason_sel .= "<option value=\"$v\">$v</option>";
			}else{
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		require_once PrintEot('mark');footer();
	}
	require_once GetLang('masigle');

	if(strpos($credittype,$cid)===false){
		Showmsg('masigle_credit_right');
	}
	$addpoint=(int)$addpoint;
	if(!is_numeric($addpoint) || $addpoint==0){
		Showmsg('member_credit_error');
	}
	if($addpoint>$maxper || $addpoint<$minper){
		Showmsg('masigle_creditlimit');
	}
	if($maxcredit){
		$creditdb=explode("\t",$atc['credit']);
		if($creditdb[0]<$tdtime){
			$creditdb[0]=$tdtime;
			$creditdb[1]=abs($addpoint);
			if($creditdb[1]>$maxcredit){
				$leavepoint=max(0,$maxcredit-$creditdb[1]);
				Showmsg('masigle_point');
			}
		} else{
			if($creditdb[1]+abs($addpoint)>$maxcredit){
				$leavepoint=max(0,$maxcredit-$creditdb[1]);
				Showmsg('masigle_point');
			} else{
				$creditdb[0]=$timestamp;
				$creditdb[1]+=abs($addpoint);
			}
		}
		$newcreditdb=$creditdb[0]."\t".$creditdb[1];
		$rt=$db->get_one("SELECT uid FROM pw_memberinfo WHERE uid='$winduid'");
		if($rt['uid']){
			$db->update("UPDATE pw_memberinfo SET credit='$newcreditdb' WHERE uid='$winduid'");
		} else{
			$db->update("INSERT INTO pw_memberinfo(uid,credit) VALUES('$winduid','$newcreditdb')");
		}
	}
	addcredit($atc['authorid'],$cid,$addpoint);
	
	if($db_autoban && $addpoint<0){
		require_once(R_P.'require/autoban.php');
		autoban($rt['authorid']);
	}
	$ifmark=$atc['ifmark']? $name.':'.$addpoint.'('.addslashes($windid).")\t".$atc['ifmark'] : $name.':'.$addpoint.'('.addslashes($windid).')';
	if(strlen($ifmark)>120){
		$ifmark=substr($ifmark,0,120);	
		$ifmark=substr($ifmark,0,strrpos($ifmark,"\t"));
	}
	if(!is_numeric($pid)){
		$db->update("UPDATE pw_threads SET ifmark=ifmark+'$addpoint' WHERE tid='$tid'");
		$db->update("UPDATE pw_tmsgs SET ifmark='$ifmark' WHERE tid='$tid'");
	} else{
		$db->update("UPDATE pw_posts SET ifmark='$ifmark' WHERE pid='$pid'");
	}
	$atc_content=Char_cv($atc_content);
	!$atc['subject'] && $atc['subject']=substrs($atc['content'],35);
	if($ifmsg){
		$msg=array(
			$atc['author'],
			$winduid,
			'ping_title',
			$timestamp,
			'ping_content',
			'',
			$windid,
			'fid'		=> $atc['fid'],
			'tid'		=> $tid,
			'subject'	=> $atc['subject'],
			'postdate'	=> get_date($atc['postdate']),
			'forum'		=> $forum[$atc['fid']]['name'],
			'affect'    => "$name:$addpoint",
			'admindate'	=> get_date($timestamp),
			'reason'	=> $atc_content
		);
		writenewmsg($msg,1);
	}
	require_once(R_P.'require/writelog.php');
	$log = array(
		'type'      => 'credit',
		'username1' => $atc['author'],
		'username2' => $windid,
		'field1'    => $fid,
		'field2'    => '',
		'field3'    => '',
		'descrip'   => 'credit_descrip',
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
		'tid'		=> $tid,
		'forum'		=> $foruminfo['name'],
		'subject'	=> $atc['subject'],
		'affect'	=> "$name:$addpoint",
		'reason'	=> $atc_content
	);
	writelog($log);
	$ifcheck=1;
	if($foruminfo['allowhtm']){
		if($foruminfo['cms']){
			require_once R_P.'require/c_buildhtml.php';
			BuildTopicHtml($tid,$foruminfo);
		} else {
			include_once R_P.'require/template.php';
		}
		refreshto("read.php?tid=$tid&page=$page",'enter_thread');
	} else{
		refreshto("read.php?tid=$tid&page=$page",'enter_thread');
	}
}elseif($action=='report'){
	!$gp_allowreport && Showmsg('report_right');
	$pid=(int)$pid;
	if(!$winduid || !is_numeric($tid) && !is_numeric($pid)){
		Showmsg('undefined_action');
	}
	$rt=$db->get_one("SELECT tid FROM pw_report WHERE uid='$winduid' AND tid='$tid' AND pid='$pid'");
	if($rt){
		Showmsg('have_report');
	}
	if(!$step){
		require_once(R_P.'require/header.php');
		require_once PrintEot('report');footer();
	} elseif($step==2){
		$reason=Char_cv($reason);
		$db->update("INSERT INTO pw_report(tid,pid,uid,type,reason) VALUES('$tid','$pid','$winduid','$type','$reason')");
		Showmsg('report_success');
	}
}elseif($action=='sign'){
	!$winduid && Showmsg('undefined_action');
	$db->update("UPDATE pw_memberdata SET lastvisit='$timestamp' WHERE uid='$winduid'");
	refreshto("$db_bfn",'operate_success');
}elseif($action=='switch'){
	!$winduid && Showmsg('undefined_action');
	$cur=$cur==0 ? 1 : 0;
	$db->update("UPDATE pw_memberdata SET editor='$cur' WHERE uid='$winduid'");
	$jump=str_replace('&#61;','=',$jump);
	refreshto($jump,'operate_success');
}elseif($previewjob=='preview'){
	require_once(R_P.'require/bbscode.php');
	require_once(R_P.'require/header.php');
	$atc_content=Char_cv($atc_content);
	$atc_content=str_replace("\n","<br>",$atc_content);
	$preatc=convert($atc_content,$db_windpost);
	require_once PrintEot('preview');footer();
} elseif($action=='redirect'){
	$aid=(int)$aid;
	$rt=$db->get_one("SELECT uid,uploadtime FROM pw_attachs WHERE aid='$aid'");
	if($rt){
		$urladd='';
		if($goto=='next'){
			$rt=$db->get_one("SELECT aid FROM pw_attachs WHERE uid='$rt[uid]' AND aid!='$aid' AND type='img' AND aid<='$aid' ORDER BY aid DESC LIMIT 1");
			!$rt['aid'] && $urladd='&nonext=1';
		}elseif($goto=='pre'){
			$rt=$db->get_one("SELECT aid FROM pw_attachs WHERE uid='$rt[uid]' AND aid!='$aid' AND type='img' AND aid>='$aid' ORDER BY aid LIMIT 1");
			!$rt['aid'] && $urladd='&nopre=1';
		}
		$rt['aid'] && $aid=$rt['aid'];
		ObHeader("show.php?action=pic&aid=$aid$urladd");
	}else{
		Showmsg('pic_not_exists');
	}

} elseif($rd_previous==1){
	if(!is_numeric($tid)){
		require_once(R_P.'require/url_error.php');
	}
	$rs = $db->get_one("SELECT fid,postdate,lastpost FROM pw_threads WHERE tid='$tid'");
	if($url){
		$lastpost = $rs['postdate'];
		$by='postdate';
	}else{
		$lastpost = $rs['lastpost'];
		$by='lastpost';
		$url='read.php';
	}
	$fid=$rs['fid'];

	if($goto=="previous"){
		$next = $db->get_one("SELECT tid,postdate FROM pw_threads WHERE fid='$fid' AND $by>'$lastpost' ORDER BY $by ASC LIMIT 1");
		if($next) {
			ObHeader("$url?tid=$next[tid]");
		} else {
			ObHeader("$url?tid=$tid");
		}
	} elseif($goto=="next"){
		$last = $db->get_one("SELECT tid,postdate FROM pw_threads WHERE fid='$fid' AND $by<'$lastpost' ORDER BY $by DESC LIMIT 1");
		if($last) {
			ObHeader("$url?tid=$last[tid]");
		} else {
			ObHeader("$url?tid=$tid");
		}
	}
} elseif($action=='download'){
	if(is_numeric($aid)){
		if(is_numeric($pid)){
			$table = 'pw_posts';
			$where = "pid='$pid'";
			$post  = $db->get_one("SELECT fid,aid FROM pw_posts WHERE pid='$pid'");
		} else{
			$table = 'pw_tmsgs';
			$where = "tid='$tid'";
			$post  = $db->get_one("SELECT t.fid,tm.aid FROM pw_threads t LEFT JOIN pw_tmsgs tm USING(tid) WHERE t.tid='$tid'");
		}
		$attach = unserialize(stripslashes($post['aid']));
		$fid = $post['fid'];
		$attachurl='';
		!$attach[$aid] && Showmsg('job_attach_error');
		@extract($attach[$aid]);
		if(!$attachurl || strpos($attachurl,'..')!==false){
			Showmsg('job_attach_error');
		}
	} else{
		Showmsg('job_attach_error');
	}
	require_once(R_P.'require/forum.php');
	$foruminfo=$db->get_one("SELECT f_type,style,password,allowvisit,forumadmin,allowdownload FROM pw_forums WHERE fid='$fid'");
	if(!$foruminfo){
		require_once(R_P.'require/url_error.php');
	}
	wind_forumcheck($foruminfo);
	/*
	* 获取管理权限
	*/
	if($windid==$manager || ($foruminfo['forumadmin'] && strpos($foruminfo['forumadmin'],','.$windid.',')!==false)){
		$admincheck=1;
	} else{
		$admincheck=0;
	}
	/**
	* 版块权限判断
	*/
	if($foruminfo['allowdownload'] && strpos($foruminfo['allowdownload'],','.$groupid.',')===false && !$admincheck){
		Showmsg('job_attach_forum');
	}
	/**
	* 用户组权限判断
	*/
	if(!$foruminfo['allowdownload'] && $gp_allowdownload==0 && !$admincheck){
		Showmsg('job_attach_group');
	}
	if($needrvrc && $userrvrc < $needrvrc && !$admincheck){
		list(,,$db_rvrcname,)=explode("\t",$db_credits);
		Showmsg('job_attach_rvrc');
	}
	if(!$attach_url && !is_readable("$attachdir/$attachurl")){
		Showmsg('job_attach_error');
	}
	$db->update("UPDATE pw_attachs SET hits=hits+1 WHERE aid='$aid'");

	$attach[$aid]['hits']++;
	$attach=addslashes(serialize($attach));
	$db->update("UPDATE $table SET aid='$attach' WHERE $where");

	$filename =basename("$attachdir/$attachurl");
	$fileext = substr(strrchr($attachurl,'.'),1);

	if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')!==false && $fileext=='torrent' ){
		$attachment='inline';
	} else{
		$attachment='attachment';
	}
	ob_end_clean();
	//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s',$timestamp+31536000).' GMT');
	header('Pragma: no-cache');
	header('Content-Encoding: none');
	header('Content-Disposition: '.$attachment.'; filename='.$name);
	header('Content-type: '.$fileext);

	if($attach_url && !file_exists("$attachdir/$attachurl") && function_exists('file_get_contents')){
		$downcontent=file_get_contents($attach_url."/$attachurl");
	}else{
		$filesize = filesize($attachdir.'/'.$attachurl);
		header('Content-Length: '.$filesize);
		$downcontent=readover("$attachdir/$attachurl");
	}
	echo $downcontent;
	exit;
} elseif($action=='deldownfile'){
	if(is_numeric($aid)){
		if(is_numeric($pid)){
			$table = 'pw_posts';
			$where = "pid='$pid'";
			$post  = $db->get_one("SELECT fid,tid,aid AS oldaid,authorid FROM pw_posts WHERE pid='$pid'");
		} else{
			$table = 'pw_tmsgs';
			$where = "tid='$tid'";
			$post  = $db->get_one("SELECT t.tid,t.fid,t.authorid,tm.aid AS oldaid FROM pw_threads t LEFT JOIN pw_tmsgs tm USING(tid) WHERE t.tid='$tid'");
		}
		$tid    = $post['tid'];
		$fid    = $post['fid'];
		$attach = unserialize(stripslashes($post['oldaid']));
		$attachurl='';
		!$attach[$aid] && Showmsg('job_attach_error');
		@extract($attach[$aid]);
		if(!$attachurl || strpos($attachurl,'..')!==false){
			Showmsg('job_attach_error');
		}
	} else{
		Showmsg('job_attach_error');
	}
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/updateforum.php');

	$foruminfo=$db->get_one("SELECT f_type,style,password,allowvisit,forumadmin,allowhtm,cms FROM pw_forums WHERE fid='$fid'");
	if(!$foruminfo){
		require_once(R_P.'require/url_error.php');
	}
	wind_forumcheck($foruminfo);
	/*
	*  获取管理权限
	*/
	if($windid==$manager || ($foruminfo['forumadmin'] && strpos($foruminfo['forumadmin'],','.$windid.',')!==false)){
		$admincheck=1;
	} else{
		$admincheck=0;
	}
	if ($groupid!='guest' && ($admincheck || $post['authorid']==$winduid || $SYSTEM['delattach'])){
		P_unlink("$attachdir/$attachurl");

		$attach=unserialize(stripslashes($post['oldaid']));
		unset($attach[$aid]);
		if($attach){
			$attach=addslashes(serialize($attach));
		} else{
			$attach='';
		}
		$db->update("UPDATE $table SET aid='$attach' WHERE $where");
		$db->update("DELETE FROM pw_attachs WHERE aid='$aid'");

		$ifupload=getattachtype($tid);
		$db->update("UPDATE pw_threads SET ifupload='$ifupload' WHERE tid='$tid'");

		if($foruminfo['allowhtm']){
			if($foruminfo['cms']){
				require_once R_P.'require/c_buildhtml.php';
				BuildTopicHtml($tid,$foruminfo);
			} else {
				include_once R_P.'require/template.php';
			}
			require_once R_P.'require/template.php';
			refreshto($jumpurl,'operate_success');
		} else{
			refreshto("read.php?fid=$fid&tid=$tid",'operate_success');
		}
	} else{
		Showmsg('job_attach_right');
	}
} elseif($action=='viewtody'){
	$wind_in='vt';
	if($db_today==0){
		Showmsg('job_viewtody_close');
	}
	require_once(R_P.'require/header.php');
	require_once(R_P.'require/forum.php');
	$check_admin="N";
	if ($windid==$manager) 
		$check_admin="Y";
	if(!is_numeric($page) || $page<1){
		$page=1;
	}
	$filename=D_P.'data/bbscache/today.php';
	$dbtdsize=100+1;
	$seed=$page*$db_perpage;$count=0;
	if($fp=@fopen($filename,"rb")){
		flock($fp,LOCK_SH);
		$node=fread($fp,$dbtdsize);
		$nodedb=explode("\t",$node);/*头结点在第二个数据段*/
		$nodefp=$dbtdsize*$nodedb[1];
		fseek($fp,$nodefp,SEEK_SET);
		$todayshow=fseeks($fp,$dbtdsize,$seed);/*传回数组*/
		fseek($fp,0,SEEK_END);
		$count=floor(ftell($fp)/$dbtdsize)-1;
		fclose($fp);
	}
	if ($count%$db_perpage==0){
		$numofpage=$count/$db_perpage;  //$numofpage为 一共多少页
	} else{
		$numofpage=floor($count/$db_perpage)+1; 
	}
	if ($page>$numofpage)
		$page=$numofpage;
	$pagemin=min(($page-1)*$db_perpage , $count-1);  
	$pagemax=min($pagemin+$db_perpage-1, $count-1);
	$pages=numofpage($count,$page,$numofpage,"job.php?action=viewtody&");
	
	$inbbsdb=array();
	for ($i=$pagemin; $i<=$pagemax; $i++) {
		if (!trim($todayshow[$i]))
			continue;
		list($inbbs['user'],$null1,$null2,$inbbs['rgtime'],$inbbs['logintime'],$inbbs['intime'],$inbbs['ip'],$inbbs['post'],$inbbs['rvrc'],$null)=explode("\t",$todayshow[$i]);
		$inbbs['rawuser']=rawurlencode($inbbs['user']);
		$inbbs['rvrc']=floor($inbbs['rvrc']/10);
		$inbbs['rgtime']=get_date($inbbs['rgtime']);
		$inbbs['logintime']=get_date($inbbs['logintime']);
		$inbbs['intime']=get_date($inbbs['intime']);
		if ($check_admin=="N")
		{
			$inbbs['ip']="secret";
		}
		$inbbsdb[]=$inbbs;
	}
	list(,,$db_rvrcname,)=explode("\t",$db_credits);

	require_once PrintEot('todayinbbs');footer();
}elseif($action=='buytopic'){
	$tpcs=$db->get_one("SELECT authorid,content FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid='$tid'");
	$tpcs['content']=substr($tpcs['content'],strpos($tpcs['content'],'[sell=')+6);
	$sellmoney = substr($tpcs['content'],0,strpos($tpcs['content'],']'));
	$money     = $winddb['money'];
	list($db_moneyname,)=explode("\t",$db_credits);
	if (empty($windid) || $winduid==$tpcs['authorid'] || !is_numeric($sellmoney) || $sellmoney<0){
		Showmsg('undefined_action');
	}
	$sellmoney = (int)$sellmoney;
	if($sellmoney > 1000){
		$sellmoney = 1000;
	}
	$money < $sellmoney && Showmsg('job_buy_noenough');
	$rs=$db->get_one("SELECT buy FROM pw_tmsgs  WHERE tid='$tid'");
	if($rs['buy'] && strpos($rs['buy'].',',','.$windid.',')!==false){
		Showmsg('job_havebuy');
	}
	$money=$money-$sellmoney;
	$sellmoney>10 && $sellmoney=$sellmoney*0.9;
	$db->update("UPDATE pw_memberdata SET money='$money' WHERE uid='$winduid'");
	$db->update("UPDATE pw_memberdata SET money=money+'$sellmoney' WHERE uid='$tpcs[authorid]'");	

	$buy=$rs['buy'].",".$windid;
	$db->update("UPDATE pw_tmsgs SET buy='".addslashes($buy)."' WHERE tid='$tid'");
	refreshto("read.php?tid=$tid",'operate_success');
}elseif($votejop=='vote'){
	require_once(R_P.'require/forum.php');
	@extract($db->get_one("SELECT t.fid,t.tid,t.postdate,t.locked,t.ifcheck,p.voteopts FROM pw_polls p LEFT JOIN pw_threads t ON p.pollid=t.pollid WHERE p.pollid='$pollid'"));
	/**
	* 得到版块基本信息,版块权限验证
	*/
	
	$foruminfo=$db->get_one("SELECT name,f_type,style,password,allowvisit,forumadmin,allowhtm,cms FROM pw_forums WHERE fid='$fid'");
	if(!$foruminfo){
		require_once(R_P.'require/url_error.php');
	}
	wind_forumcheck($foruminfo);

	/*
	*  获取管理权限
	*/
	if($windid==$manager || ($foruminfo['forumadmin'] && strpos($foruminfo['forumadmin'],','.$windid.',')!==false)){
		$admincheck=1;
	} else{
		$admincheck=0;
	}

	/*
	*用户组权限验证
	*/
	$gp_allowvote==0 && Showmsg('job_vote_right');
	$v_uid = $windid ? $windid : $onlineip;
	if(!$admincheck && $locked>0){
		Showmsg('job_vote_lock');
	}

	$votearray = unserialize($voteopts);

	if(!$voteaction){
		foreach($votearray['options'] as $option){
			if(@in_array($v_uid,$option[2])){
				Showmsg('job_havevote');
			}
		}
	}

	if(empty($voteid)){
		Showmsg('job_vote_sel');
	}
	if(count($voteid)>$votearray['multiple'][1]){
		Showmsg('job_vote_num');
	}
	if($voteaction=='modify'){
		if ($gp_edittime && ($timestamp-$postdate)>$gp_edittime*60){
			Showmsg('modify_timelimit');
		}
		foreach($votearray['options'] as $key=>$option){
			foreach($option[2] as $vid=>$value){
				if($value==$v_uid){
					$votearray['options'][$key][1]--;
					unset($votearray['options'][$key][2][$vid]);
				}
			}
		}
	}
	foreach($voteid as $id){
		$votearray['options'][$id][1]++;
		$votearray['options'][$id][2][]=$v_uid;
	}
	$voteopts = addslashes(serialize($votearray));
	$db->update("UPDATE pw_polls SET voteopts='$voteopts' WHERE pollid='$pollid'");
	$db->update("UPDATE pw_threads SET lastpost='$timestamp' WHERE tid='$tid'");
	if($foruminfo['allowhtm']==1){
		if($foruminfo['cms']){
			require_once R_P.'require/c_buildhtml.php';
			BuildTopicHtml($tid,$foruminfo);
		} else {
			include_once R_P.'require/template.php';
		}
	}
	empty($j_p) && $j_p="read.php?tid=$tid";
	refreshto($j_p,'operate_success');
}elseif($action=='pushtokjj'){
	!$db_kijiji && Showmsg('undefined_action');
	$rt = $db->get_one("SELECT t.authorid,t.subject,tm.content FROM pw_threads t LEFT JOIN pw_tmsgs tm USING(tid) WHERE t.tid='".(int)$tid."'");
	if($winduid != $rt['authorid']){
		Showmsg('undefined_action');
	}
	if(!$step){
		require_once(R_P.'require/header.php');
		$kjj_checked='checked';
		require_once PrintEot('pushtokjj');footer();
	}else{
		require_once(R_P."require/kijiji.php");
		$strPostXML = init_data($k_email,$area_id,$category_id,$rt['subject'],$rt['content']);
		$response1  = fsockPost($sendtourl,$strPostXML);
		refreshto("read.php?tid=$tid",'pushtokjj_success');
	}
}

//elseif($s_user=='htm'){
	//$
//}
function fseeks($fp,$dbtdsize,$seed){
	$num=0;
	while($break!=1 && $num<$seed){
		$num++;
		$sdata=fread($fp,$dbtdsize);
		$sdb=explode("\t",$sdata);
		$sdbnext=$sdb[2]*$dbtdsize;
		if($sdbnext!='NULL'){
			fseek($fp,$sdbnext,SEEK_SET);
		}else{
			$break=1;
		}
		$todayshow[]=$sdata;
	}
	return $todayshow;
}
function addcredit($uid,$cid,$addpoint){
	global $db;
	if($cid=='rvrc'){
		$addpoint*=10;
		$db->update("UPDATE pw_memberdata SET rvrc=rvrc+'$addpoint' WHERE uid='$uid'");
	}elseif($cid=='money'){
		$db->update("UPDATE pw_memberdata SET money=money+'$addpoint' WHERE uid='$uid'");
	}elseif($cid=='credit'){
		$db->update("UPDATE pw_memberdata SET credit=credit+'$addpoint' WHERE uid='$uid'");
	}elseif(is_numeric($cid)){
		$db->pw_update(
			"SELECT uid FROM pw_membercredit WHERE uid='$uid' AND cid='$cid'",
			"UPDATE pw_membercredit SET value=value+'$addpoint' WHERE uid='$uid' AND cid='$cid'",
			"INSERT INTO pw_membercredit SET uid='$uid',cid='$cid',value='$addpoint'"
		);
	} else{
		Showmsg('credit_error');
	}
}
?>