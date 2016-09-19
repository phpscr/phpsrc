<?php
require_once('global.php');
if ($action=='delimg'){
	!$winduid && Showmsg('undefined_action');
	$imgdb=$db->get_one("SELECT icon FROM pw_members WHERE uid='$winduid'");
	Add_S($imgdb);
	if($imgdb){
		$deldb=explode("|",$imgdb['icon']);
		if($deldb[1] && !ereg("^http",$deldb[1]) && strpos($deldb[1],'..')===false){
			if($db_ftpweb && !file_exists($attachdir.'/upload/'.$deldb[1])){
				require_once(R_P.'require/ftp.php');
				$ftp->delete('upload/'.$deldb[1]);
				$ftp->close();
				unset($ftp);
			}else{
				P_unlink("$attachdir/upload/$deldb[1]");
			}
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
		$atc=$db->get_one("SELECT fid,author,authorid,postdate,subject,anonymous,tm.ifmark,credit FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid LEFT JOIN pw_memberinfo m ON m.uid='$winddb[uid]' WHERE t.tid='$tid'");
	} else{
		$pw_posts = GetPtable('N',$tid);
		$atc=$db->get_one("SELECT fid,author,authorid,postdate,subject,ifmark,anonymous,credit,content FROM $pw_posts p LEFT JOIN pw_memberinfo m ON m.uid='$winddb[uid]' WHERE pid='$pid'");
	}
	Add_S($atc);
	$fid=$atc['fid'];
	$foruminfo=$db->get_one("SELECT name,f_type,style,password,allowvisit,allowhtm,cms,forumadmin,fupadmin FROM pw_forums WHERE fid='$fid' AND type<>'category'");
	!$foruminfo && Showmsg('data_error');
	wind_forumcheck($foruminfo);
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	list($maxcredit,$minper,$maxper,$credittype)=explode("|",$_G['markdb']);
	!$minper && $minper=0;
	!$maxper && $maxper=0;
	if(($windid != $manager && !admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid) && !$_G['markable']) || !$credittype || ($minper==0 && $maxper==0)){
		Showmsg('no_markright');
	}
	if($db_pingtime && $timestamp-$atc['postdate']>$db_pingtime*3600 && $gp_gptype!='system'){
		Showmsg('pingtime_over');
	}
	if($_POST['step'] && $_G['markable']<2 && strpos($atc['ifmark'],'('.$windid.')')!==false){
		Showmsg('no_markagain');
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
		if($atc['anonymous'] && $groupid!='3'){
			$check_Y='disabled';
			$check_N='checked';
		}else{
			$check_Y='checked';
			$check_N='';
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
		autoban($atc['authorid']);
	}
	$ifmark=$atc['ifmark']? $name.':'.$addpoint.'('.addslashes($windid).")\t".$atc['ifmark'] : $name.':'.$addpoint.'('.addslashes($windid).')';
	if(strlen($ifmark)>240){
		$ifmark=substr($ifmark,0,240);	
		$ifmark=substr($ifmark,0,strrpos($ifmark,"\t"));
	}
	if(!is_numeric($pid)){
		$db->update("UPDATE pw_threads SET ifmark=ifmark+'$addpoint' WHERE tid='$tid'");
		$db->update("UPDATE pw_tmsgs SET ifmark='$ifmark' WHERE tid='$tid'");
	} else{
		$db->update("UPDATE $pw_posts SET ifmark='$ifmark' WHERE pid='$pid'");
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
	if($foruminfo['allowhtm'] && $page==1){
		if($foruminfo['cms']){
			require_once(R_P.'require/c_buildhtml.php');
			BuildTopicHtml($tid,$foruminfo);
		} else {
			require_once(R_P.'require/template.php');
		}
		refreshto("read.php?tid=$tid&page=$page",'enter_thread');
	} else{
		refreshto("read.php?tid=$tid&page=$page",'enter_thread');
	}
}elseif($_POST['action']=='delping'){
	require_once(R_P.'require/msg.php');
	require_once(R_P.'require/forum.php');
	include_once(D_P.'data/bbscache/forum_cache.php');
	
	$groupid=='guest' && Showmsg('not_login');

	if(!is_numeric($pid)){
		$atc=$db->get_one("SELECT t.fid,t.postdate,t.authorid,t.author,t.subject,tm.ifmark FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid='$tid'");
	} else{
		$pw_posts = GetPtable('N',$tid);
		$atc=$db->get_one("SELECT fid,postdate,authorid,author,ifmark,subject,content FROM $pw_posts WHERE pid='$pid'");
	}
	Add_S($atc);
	if(strpos($atc['ifmark'],"(".$windid.")")===false){
		Showmsg('have_not_showping');
	}
	$fid=$atc['fid'];
	$foruminfo=$db->get_one("SELECT name,f_type,style,password,allowvisit,allowhtm,cms,forumadmin,fupadmin FROM pw_forums WHERE fid='$fid' AND type<>'category'");
	!$foruminfo && Showmsg('data_error');
	wind_forumcheck($foruminfo);
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	$markdb=explode("\t",$atc['ifmark']);
	$cid='';
	foreach($markdb as $key=>$mark){
		if(strpos($mark,"(".$windid.")")!==false){
			$credit = substr($mark,0,strpos($mark,":"));
			list($addpoint) = explode("(",substr($mark,strpos($mark,":")+1));
			$addpoint = (int) $addpoint;
			unset($markdb[$key]);
			if($credit==$db_rvrcname){
				$cid='rvrc';
			}elseif($credit==$db_moneyname){
				$cid='money';
			}elseif($credit==$db_creditname){
				$cid='credit';
			}else{
				$rt=$db->get_one("SELECT cid FROM pw_credits WHERE name='$credit'");
				$cid = $rt['cid'];
			}
			break;
		}
	}
	!$cid && Showmsg('delping_credit_error');
	$addpoint = $addpoint>0 ? -$addpoint : abs($addpoint);
	addcredit($atc['authorid'],$cid,$addpoint);

	$ifmark=implode("\t",$markdb);
	if(!is_numeric($pid)){
		$db->update("UPDATE pw_threads SET ifmark=ifmark+'$addpoint' WHERE tid='$tid'");
		$db->update("UPDATE pw_tmsgs SET ifmark='$ifmark' WHERE tid='$tid'");
	} else{
		$db->update("UPDATE $pw_posts SET ifmark='$ifmark' WHERE pid='$pid'");
	}
	$atc_content=Char_cv($atc_content);
	!$atc['subject'] && $atc['subject']=substrs($atc['content'],35);
	if($ifmsg){
		$msg=array(
			$atc['author'],
			$winduid,
			'delping_title',
			$timestamp,
			'delping_content',
			'',
			$windid,
			'fid'		=> $atc['fid'],
			'tid'		=> $tid,
			'subject'	=> $atc['subject'],
			'postdate'	=> get_date($atc['postdate']),
			'forum'		=> $forum[$atc['fid']]['name'],
			'affect'    => "$credit:$addpoint",
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
		'field1'    => $atc['fid'],
		'field2'    => '',
		'field3'    => '',
		'descrip'   => 'creditdel_descrip',
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
		'tid'		=> $tid,
		'forum'		=> $forum[$atc['fid']]['name'],
		'subject'	=> $atc['subject'],
		'affect'	=> "$name:$addpoint",
		'reason'	=> $atc_content
	);
	writelog($log);

	if($foruminfo['allowhtm'] && $page==1){
		if($foruminfo['cms']){
			require_once(R_P.'require/c_buildhtml.php');
			BuildTopicHtml($tid,$foruminfo);
		} else {
			require_once(R_P.'require/template.php');
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
	} elseif($_POST['step']==2){
		if($ifmsg){
			if($pid>0){
				$pw_posts = GetPtable('N',$tid);
				$sqlsel="t.content as subject,t.postdate,";
				$sqltab="$pw_posts t";
				$sqladd="WHERE t.pid='$pid'";
			}else{
				$sqlsel="t.subject,t.postdate,";
				$sqltab="pw_threads t";
				$sqladd="WHERE t.tid='$tid'";
			}
			$rs=$db->get_one("SELECT $sqlsel t.fid,f.forumadmin FROM $sqltab LEFT JOIN pw_forums f USING(fid) $sqladd");
			if($rs['forumadmin']){
				include_once(D_P.'data/bbscache/forum_cache.php');
				require_once(R_P.'require/msg.php');
				$admin_a=explode(',',$rs['forumadmin']);
				$msg=array(
					'',
					$winduid,
					'report_title',
					$timestamp,
					'report_content_'.$type,
					'',
					$windid,
					'fid'		=> $rs['fid'],
					'tid'		=> $tid.'#'.$pid,
					'postdate'	=> get_date($rs['postdate']),
					'forum'		=> $forum[$rs['fid']]['name'],
					'subject'	=> $rs['subject'],
					'admindate'	=> get_date($timestamp),
					'reason'	=> $reason
				);
				foreach($admin_a as $key=>$forumadmin){
					if(!$forumadmin)continue;
					$msg['0']=$forumadmin;
					writenewmsg($msg,1);
				}
			}
		}
		$reason=Char_cv($reason);
		$db->update("INSERT INTO pw_report(tid,pid,uid,type,reason) VALUES('$tid','$pid','$winduid','$type','$reason')");
		Showmsg('report_success');
	}
}elseif($action=='sign'){
	!$winduid && Showmsg('undefined_action');
	$db->update("UPDATE pw_memberdata SET lastvisit='$timestamp' WHERE uid='$winduid'");
	refreshto("$db_bfn",'operate_success');
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
	set_time_limit(0);
	if(is_numeric($aid)){
		if(is_numeric($pid)){
			$table = GetPtable('N',$tid);
			$where = "pid='$pid'";
			$post  = $db->get_one("SELECT fid,aid FROM $table WHERE pid='$pid'");
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
	$foruminfo=$db->get_one("SELECT f_type,style,password,allowvisit,forumadmin,fupadmin,allowdownload FROM pw_forums WHERE fid='$fid'");
	!$foruminfo && Showmsg('data_error');
	wind_forumcheck($foruminfo);
	/*
	* 获取管理权限
	*/
	if($groupid=='3' || admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
		$admincheck=1;
	} else{
		$admincheck=0;
	}
	/**
	* 版块权限判断
	*/
	if($foruminfo['allowdownload'] && !allowcheck($foruminfo['allowdownload'],$groupid,$winddb['groups']) && !$admincheck){
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
	if(!$attach_url && !$db_ftpweb && !is_readable("$attachdir/$attachurl")){
		Showmsg('job_attach_error');
	}
	$db->update("UPDATE pw_attachs SET hits=hits+1 WHERE aid='$aid'");

	$attach[$aid]['hits']++;
	$filesize = 0;

	$attach=addslashes(serialize($attach));
	$db->update("UPDATE $table SET aid='$attach' WHERE $where");

	$filename= basename("$attachdir/$attachurl");
	$fileext = substr(strrchr($attachurl,'.'),1);

	if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')!==false && $fileext=='torrent' ){
		$attachment='inline';
	} else{
		$attachment='attachment';
	}

	if($db_charset=='utf-8'){
		if(function_exists('mb_convert_encoding')){
			$name = mb_convert_encoding($name, "gbk",'utf-8');
		}else{
			require_once(R_P.'wap/chinese.php');
			$chs = new Chinese('UTF8','gbk');
			$name= $chs->Convert($name);
		}
	}
	$fgeturl = geturl($attachurl);

	if($fgeturl[0]){
		if($fgeturl[1]=='Local'){
			$fgeturl[0]=D_P.$fgeturl[0];
			$filesize = filesize($fgeturl[0]);
		}
	}else{
		Showmsg('job_attach_error');
	}
	ob_end_clean();
	//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s',$timestamp+31536000).' GMT');
	header('Cache-control: max-age=31536000');
	header('Content-Encoding: none');
	header('Content-Disposition: '.$attachment.'; filename='.$name);
	header('Content-type: '.$fileext);
	$filesize && header('Content-Length: '.$filesize);
	
	readfile($fgeturl[0]);
	exit;
} elseif($action=='showimg'){
	if($verify!=md5("showimg{$tid}{$pid}{$fid}{$aid}{$db_hash}")){
		Showmsg('undefined_action');
	}
	if(function_exists('file_get_contents')){
		$rs = $db->get_one("SELECT attachurl FROM pw_attachs WHERE aid='$aid' AND tid='$tid' AND fid='$fid'");
		if($rs){
			$fgeturl = geturl($rs['attachurl']);
			if($fgeturl[0]){
				echo file_get_contents($fgeturl[0]);exit;
			}
		}
	}
	Showmsg('job_attach_error');
} elseif($action=='deldownfile'){
	if(is_numeric($aid)){
		if(is_numeric($pid)){
			$table = $pw_posts = GetPtable('N',$tid);			
			$where = "pid='$pid'";
			$post  = $db->get_one("SELECT fid,tid,aid AS oldaid,authorid FROM $pw_posts WHERE pid='$pid'");
		} else{
			$table = 'pw_tmsgs';
			$where = "tid='$tid'";
			$post  = $db->get_one("SELECT t.tid,t.fid,t.authorid,t.ptable,tm.aid AS oldaid FROM pw_threads t LEFT JOIN pw_tmsgs tm USING(tid) WHERE t.tid='$tid'");
			$pw_posts=GetPtable($post['ptable']);
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

	$foruminfo=$db->get_one("SELECT name,f_type,style,password,allowvisit,forumadmin,fupadmin,allowhtm,cms FROM pw_forums WHERE fid='$fid'");
	!$foruminfo && Showmsg('data_error');
	wind_forumcheck($foruminfo);
	/*
	*  获取管理权限
	*/
	if($windid==$manager || admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
		$admincheck=1;
	}elseif($SYSTEM['delattach']){
		if(!$SYSTEM['rightwhere'] || strpos(",".$SYSTEM['rightwhere'].",",",".$fid.",")!==false){
			$admincheck=1;
		}else{
			$admincheck=0;
		}
	}else{
		$admincheck=0;
	}
	if($groupid!='guest' && ($admincheck || $post['authorid']==$winduid)){
		$a_url=geturl($attachurl);
		if($a_url[1]=='Local'){
			P_unlink("$attachdir/$attachurl");
		}elseif($a_url[1]=='Ftp'){
			require_once(R_P.'require/ftp.php');
			$ftp->delete($attachurl);
			$ftp->close();
			unset($ftp);
		}

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
		if($foruminfo['allowhtm'] && $page==1){
			if($foruminfo['cms']){
				require_once(R_P.'require/c_buildhtml.php');
				BuildTopicHtml($tid,$foruminfo);
			} else {
				require_once(R_P.'require/template.php');
			}
			empty($j_p) && $j_p="read.php?tid=$tid";
			refreshto($j_p,'operate_success');
		} else{
			refreshto("read.php?tid=$tid&page=$page",'operate_success');
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
	if($count%$db_perpage==0){
		$numofpage=$count/$db_perpage;  //$numofpage为 一共多少页
	} else{
		$numofpage=floor($count/$db_perpage)+1; 
	}
	if($page>$numofpage)
		$page=$numofpage;
	$pagemin=min(($page-1)*$db_perpage , $count-1);  
	$pagemax=min($pagemin+$db_perpage-1, $count-1);
	$pages=numofpage($count,$page,$numofpage,"job.php?action=viewtody&");
	
	$inbbsdb=array();
	for($i=$pagemin; $i<=$pagemax; $i++) {
		if(!trim($todayshow[$i]))
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
	$tpcs = array();
	if($pid == 'tpc'){
		$pw_posts = 'pw_tmsgs';
		$tpcs = $db->get_one("SELECT authorid,content,buy FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid='$tid'");
	}elseif(is_numeric($pid)){
		$pw_posts = GetPtable('N',$tid);
		$tpcs = $db->get_one("SELECT authorid,content,buy FROM $pw_posts WHERE pid='$pid' && tid='$tid'");
	}
	!$tpcs && Showmsg('illegal_tid');

	$tpcs['content']=substr($tpcs['content'],strpos($tpcs['content'],'[sell=')+6);
	$sellmoney = substr($tpcs['content'],0,strpos($tpcs['content'],']'));
	list($db_moneyname,)=explode("\t",$db_credits);
	if(empty($windid) || $winduid==$tpcs['authorid'] || !is_numeric($sellmoney) || $sellmoney<0){
		Showmsg('undefined_action');
	}
	$sellmoney = (int)$sellmoney;
	if($sellmoney > 1000){
		$sellmoney = 1000;
	}
	$winddb['money'] < $sellmoney && Showmsg('job_buy_noenough');
	if($tpcs['buy'] && strpos($tpcs['buy'].',',','.$windid.',')!==false){
		Showmsg('job_havebuy');
	}
	$db->update("UPDATE pw_memberdata SET money=money-'$sellmoney' WHERE uid='$winduid'");
	$sellmoney>10 && $sellmoney=$sellmoney*0.9;
	$db->update("UPDATE pw_memberdata SET money=money+'$sellmoney' WHERE uid='$tpcs[authorid]'");	

	$buy=$tpcs['buy'].",".$windid;
	$db->update("UPDATE $pw_posts SET buy='".addslashes($buy)."' WHERE tid='$tid'");
	refreshto("read.php?tid=$tid",'operate_success');
}elseif($votejop=='vote'){
	require_once(R_P.'require/forum.php');
	@extract($db->get_one("SELECT t.fid,t.tid,t.postdate,t.locked,t.ifcheck,p.* FROM pw_polls p LEFT JOIN pw_threads t ON p.tid=t.tid WHERE p.tid='$tid'"));
	/**
	* 得到版块基本信息,版块权限验证
	*/
	
	$foruminfo=$db->get_one("SELECT name,f_type,style,password,allowvisit,forumadmin,fupadmin,allowhtm,cms FROM pw_forums WHERE fid='$fid'");
	!$foruminfo && Showmsg('data_error');
	wind_forumcheck($foruminfo);

	/*
	*  获取管理权限
	*/
	if($windid==$manager || admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
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
	}elseif($state || ($timelimit && $timestamp-$postdate>$timelimit*86400)){
		Showmsg('job_vote_close');
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
		if($gp_edittime && ($timestamp-$postdate)>$gp_edittime*60){
			Showmsg('modify_timelimit');
		}elseif(!$modifiable){
			Showmsg('vote_not_modify');
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
	foreach($voteid as $k=>$id){
		$votearray['options'][(int)$id][1]++;
		$votearray['options'][(int)$id][2][]=$v_uid;
	}
	$voteopts = addslashes(serialize($votearray));
	$db->update("UPDATE pw_polls SET voteopts='$voteopts' WHERE tid='$tid'");
	$db->update("UPDATE pw_threads SET lastpost='$timestamp' WHERE tid='$tid'");
	if($foruminfo['allowhtm']==1){
		if($foruminfo['cms']){
			require_once(R_P.'require/c_buildhtml.php');
			BuildTopicHtml($tid,$foruminfo);
		} else {
			require_once(R_P.'require/template.php');
		}
	}
	empty($j_p) && $j_p="read.php?tid=$tid";
	refreshto($j_p,'operate_success');
}elseif($action=='reward'){
	!$winduid && Showmsg('undefined_action');
	
	$rs = $db->get_one("SELECT author,authorid,ptable,subject,postdate,rewardinfo FROM pw_threads WHERE tid='$tid' AND special='3'");
	$reward = explode("\n",$rs['rewardinfo']);
	(!$rs || $reward[0]!='1') && Showmsg('illegal_tid');
	$pw_posts = GetPtable($rs['ptable']);
	
	$rt=$db->get_one("SELECT tid,fid,author,authorid,ifreward FROM $pw_posts WHERE pid='$pid'");
	$rt['tid']!=$tid && Showmsg('illegal_tid');
	
	$rs['authorid']!=$winduid && Showmsg('reward_noright');
	if(!$step){
		require_once(R_P.'require/header.php');
		require_once PrintEot('reward');footer();
	}elseif($_POST['step']=='3'){		
		list($rw_b_name,$rw_b_val,$rw_a_name,$rw_a_val)=explode('|',$reward[1]);
		if($type=='1'){
			$rewardinfo = "2\n".$reward[1]."\n".$rt['author'].'|'.$pid;
			$db->update("UPDATE pw_threads SET rewardinfo='$rewardinfo' WHERE tid='$rt[tid]'");
			$db->update("UPDATE $pw_posts SET ifreward='2' WHERE pid='$pid'");
			addcredit($rt['authorid'],$rw_b_name,$rw_b_val);  /*最佳答案者加分*/
			addcredit($winduid,$rw_b_name,$rw_b_val);  /*悬赏者返分*/
			
			if($rw_a_val>0){
				return_value($rt['tid']);
			}
			if($ifmsg){
				require_once(R_P.'require/msg.php');
				include_once(D_P.'data/bbscache/creditdb.php');
				include_once(D_P.'data/bbscache/forum_cache.php');
				list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
				$creditname = is_numeric($rw_b_name) ? $_CREDITDB[$rw_b_name][0] : ${'db_'.$rw_b_name.'name'};
				$affect=$creditname.":".$rw_b_val;
				$msg=array(
					$rt['author'],
					$winduid,
					'reward_title',
					$timestamp,
					'reward_content',
					'',
					$windid,
					'fid'		=> $rt['fid'],
					'tid'		=> $rt['tid'],
					'subject'	=> $rs['subject'],
					'postdate'	=> get_date($rs['postdate']),
					'forum'		=> $forum[$rt['fid']]['name'],
					'affect'    => $affect,
					'admindate'	=> get_date($timestamp),
					'reason'	=> "None"
				);
				writenewmsg($msg,1);
			}
		} else{
			$rw_a_val<1 && Showmsg('reward_change_error');
			$ifreward = $rt['ifreward'] ? 0 : 1;
			if($ifreward==1){
				$rew=$db->get_one("SELECT pid FROM $pw_posts WHERE tid='$rt[tid]' AND ifreward='1' AND authorid='$rt[authorid]' LIMIT 1");
				$rew && Showmsg('reward_have');
			}else{
				$db->update("UPDATE $pw_posts SET ifreward='1' WHERE tid='$rt[tid]' AND ifreward='0' AND pid>'$pid' AND authorid='$rt[authorid]' ORDER BY postdate ASC LIMIT 1");
			}
			$db->update("UPDATE $pw_posts SET ifreward='$ifreward' WHERE pid='$pid'");
		}
		refreshto("read.php?tid=$rt[tid]&page=$page",'operate_success');
	}
	/******* 悬赏 ********/

}elseif($action=='endreward'){
	!$winduid && Showmsg('undefined_action');
	require_once(R_P.'require/forum.php');
	$S_sql='';
	if($step=='3'){
		$S_sql=',t.postdate,f.allowreward';
		$ifmsg && $S_sql.=',t.fid,t.subject';
	}	
	$rt=$db->get_one("SELECT t.author,t.authorid,t.ptable,t.rewardinfo,f.forumadmin,f.fupadmin $S_sql FROM pw_threads t LEFT JOIN pw_forums f USING(fid) WHERE t.tid='$tid' AND t.special='3' AND f.allowreward>0");
	$reward = explode("\n",$rt['rewardinfo']);
	(!$rt || $reward[0]!='1') && Showmsg('illegal_tid');

	$pw_posts = GetPtable($rt['ptable']);
	if($groupid!='3' && $groupid!='4' && !admincheck($rt['forumadmin'],$rt['fupadmin'],$windid)){
		Showmsg('mawhole_right');
	}
	if(!$step){
		require_once(R_P.'require/header.php');
		require_once PrintEot('reward');footer();
	}elseif($_POST['step']=='3'){
		include(GetLang('other'));
		if($type=='1'){
			list($rw_b_name,$rw_b_val,$rw_a_name,$rw_a_val)=explode('|',$reward[1]);
			$rewardinfo = "2\n".$reward[1]."\n".$lang['cancle'];
			$db->update("UPDATE pw_threads SET rewardinfo='$rewardinfo' WHERE tid='$tid'");
			$rw_b_val*=2;
			addcredit($rt['authorid'],$rw_b_name,$rw_b_val);

			if($rw_a_val>0){
				return_value($tid);
			}
		} else{
			if($timestamp-$rt['postdate']<$rt['allowreward']*86400 && $groupid!='3' && $groupid!='4'){
				Showmsg('reward_time_limit');
			}
			list($rw_b_name,$rw_b_val,$rw_a_name,$rw_a_val)=explode('|',$reward[1]);
			$rewardinfo = "2\n".$reward[1]."\n".$lang['finished'];
			$db->update("UPDATE pw_threads SET rewardinfo='$rewardinfo' WHERE tid='$tid'");
			if($rw_a_val>0){
				return_value($tid);
			}
		}
		if($ifmsg){
			require_once(R_P.'require/msg.php');
			include_once(D_P.'data/bbscache/creditdb.php');
			include_once(D_P.'data/bbscache/forum_cache.php');
			if($type=='1'){
				list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
				$creditname = is_numeric($rw_b_name) ? $_CREDITDB[$rw_b_name][0] : ${'db_'.$rw_b_name.'name'};
				$affect=$creditname.":".$rw_b_val;
			}
			$msg=array(
				$rt['author'],
				$winduid,
				'endreward_title_'.$type,
				$timestamp,
				'endreward_content_'.$type,
				'',
				$windid,
				'fid'		=> $rt['fid'],
				'tid'		=> $tid,
				'subject'	=> $rt['subject'],
				'postdate'	=> get_date($rt['postdate']),
				'forum'		=> $forum[$rt['fid']]['name'],
				'affect'    => $affect,
				'admindate'	=> get_date($timestamp),
				'reason'	=> "None"
			);
			writenewmsg($msg,1);
		}
		refreshto("read.php?tid=$tid",'operate_success');
	}
}elseif($action=='rewardmsg'){
	!$winduid && Showmsg('undefined_action');
	$rt=$db->get_one("SELECT t.fid,t.subject,t.authorid,t.postdate,t.rewardinfo,f.forumadmin FROM pw_threads t LEFT JOIN pw_forums f USING(fid) WHERE t.tid='$tid' AND t.special='3'");
	$reward = explode("\n",$rt['rewardinfo']);
	(!$rt || $reward[0]!='1') && Showmsg('illegal_tid');
	$rt['authorid']!=$winduid && Showmsg('reward_noright');
	!$rt['forumadmin'] && Showmsg('reward_no_forumadmin');
	require_once(R_P.'require/msg.php');
	include_once(D_P.'data/bbscache/forum_cache.php');
	
	$admin_db=explode(',',substr($rt['forumadmin'],1,-1));
	$ifsend=$db->get_one("SELECT mid FROM pw_msg g LEFT JOIN pw_members m ON g.touid=m.uid WHERE g.type='rebox' AND g.fromuid='$winduid' AND m.username='$admin_db[0]' AND g.title LIKE '%:$tid)%'");
	$ifsend && Showmsg('reward_have_sendmsg');
	foreach($admin_db as $key=>$name){
		$msg=array(
			$name,
			$winduid,
			'rewardmsg_title',
			$timestamp,
			'rewardmsg_content',
			'',
			$windid,
			'fid'		=> $rt['fid'],
			'tid'		=> $tid,
			'subject'	=> $rt['subject'],
			'postdate'	=> get_date($rt['postdate']),
			'forum'		=> $forum[$rt['fid']]['name'],
			'admindate'	=> get_date($timestamp),
			'reason'	=> "None"
		);
		writenewmsg($msg,1);
	}
	Showmsg('rewardmsg_success');
}
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
function addcredits($uids,$cid,$addpoint){
	global $db;
	if($cid=='rvrc'){
		$addpoint*=10;
		$db->update("UPDATE pw_memberdata SET rvrc=rvrc+'$addpoint' WHERE uid IN($uids)");
	}elseif($cid=='money'){
		$db->update("UPDATE pw_memberdata SET money=money+'$addpoint' WHERE uid IN($uids)");
	}elseif($cid=='credit'){
		$db->update("UPDATE pw_memberdata SET credit=credit+'$addpoint' WHERE uid IN($uids)");
	}elseif(is_numeric($cid)){
		$uid_update=array();
		$query=$db->query("SELECT uid FROM pw_membercredit WHERE cid='$cid' AND uid IN($uids)");
		while($rt=$db->fetch_array($query)){
			$uid_update[]=$rt['uid'];
		}
		$uid_all=explode(',',$uids);
		$uid_insert=array_diff($uid_all,$uid_update);
		$uidss=implode(',',$uid_update);
		$uidss && $db->update("UPDATE pw_membercredit SET value=value+'$addpoint' WHERE cid='$cid' AND uid IN($uidss)");
		if($uid_insert){
			foreach($uid_insert as $key=>$uid){
				$db->update("INSERT INTO pw_membercredit (uid,cid,value) VALUES ('$uid','$cid','$addpoint')");
			}
		}
	} else{
		Showmsg('credit_error');
	}
}
function return_value($tid){
	global $db,$rw_a_name,$rw_a_val,$pw_posts;
	$limit=$rw_a_val+1;
	$query=$db->query("SELECT pid,authorid,ifreward FROM $pw_posts WHERE tid='$tid' AND ifreward='1' ORDER BY postdate ASC LIMIT $limit");
	$pids=$uids='';
	$j=0;
	while($user=$db->fetch_array($query)){
		$j++;
		if($j>$rw_a_val)break;
		$pids .= $pids ? ','.$user['pid'] : $user['pid'];
		$uids .= $uids ? ','.$user['authorid'] : $user['authorid'];
	}
	$uids && addcredits($uids,$rw_a_name,1);

	if($j>$rw_a_val){
		$sql = $pids ? "  AND pid NOT IN($pids)" : "";
		$db->update("UPDATE $pw_posts SET ifreward='0' WHERE tid='$tid' AND ifreward='1' $sql");
	}elseif($j<$rw_a_val){
		@extract($db->get_one("SELECT COUNT(*) AS count FROM $pw_posts WHERE tid='$tid' GROUP BY authorid"));
		if($count<$rw_a_val){
			$returnval=$rw_a_val-$count;
			addcredit($winduid,$rw_a_name,$returnval);
		}
	}
}
?>