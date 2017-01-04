<?php
require_once('global.php');
require_once(R_P.'require/bbscode.php');
require_once(R_P.'require/postfunc.php');
include_once(D_P.'data/bbscache/level.php');
include_once(D_P.'data/bbscache/creditdb.php');
//require R_P.'require/windxiu.php';//插件形象

$groupid=='guest' && Showmsg('not_login');
require_once(R_P.'require/header.php');
if (!$action){
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize)=explode("\t",$db_upload);
	$userdb = $db->get_one("SELECT m.*,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.lastvisit,md.onlinetime,mb.deposit,mb.ddeposit FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) LEFT JOIN pw_memberinfo mb USING(uid) WHERE m.uid='$winduid'");
	require_once(R_P.'require/credit.php');
	$creditdb = GetCredit($winduid);
	$iconarray = explode('|',$userdb['icon']);
	$uploadimg = '';
	$userface = showfacedesign($userdb['icon']);
	if ($userdb['groupid']=='-1'){
		$systitle='';
	} else{
		$systitle=$ltitle[$userdb['groupid']];
	}
	isset($ltitle[$userdb['memberid']]) && $memtitle=$ltitle[$userdb['memberid']];
	$userdb['rvrc']=floor($userdb['rvrc']/10);
	if ($userdb['onlinetime']){
		$userdb['onlinetime']=floor($userdb['onlinetime']/3600);
	} else{
		$userdb['onlinetime']=0;
	}
	$userdb['regdate']=get_date($userdb['regdate'],"Y-m-d");
	$userdb['lastvisit']=get_date($userdb['lastvisit'],"Y-m-d");

	$query = $db->query("SELECT mid,fromuid,touid,username,ifnew,title,mdate FROM pw_msg WHERE type='rebox' AND touid='$winduid' ORDER BY mdate DESC LIMIT 5");
	while($msg=$db->fetch_array($query)){
		$msg['title']=substrs($msg['title'],35);
		$msg['mdate']=get_date($msg['mdate']);
		$msg['username']=$msg['username'];
		$msg['to']=$windid;
		$msgdb[]=$msg;
	}

	include_once(D_P.'data/bbscache/forum_cache.php');
	$favordb=array();
	$rt=$db->get_one("SELECT tids FROM pw_favors WHERE uid='$winddb[uid]'");
	if ($rt['tids']){
		$tidarr = explode(',',$rt['tids']);
		$tids   = $num = 0;
		foreach ($tidarr as $key => $value){
			if (is_numeric($value)){
				$num ++;
				$tids .= ','.$value;
			}
			if ($num == 5){
				break;
			}
		}
		$query=$db->query("SELECT fid,tid,subject,postdate,author,authorid,replies,hits FROM pw_threads WHERE tid IN($tids) ORDER BY postdate DESC");
		while ($rt=$db->fetch_array($query)){
			$rt['subject']=substrs($rt['subject'],35);
			$rt['postdate']=get_date($rt['postdate']);
			$rt['forum']=$forum[$rt['fid']]['name'];
			$favordb[]=$rt;
		}
	}
	$query = $db->query("SELECT cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid='$winduid'");
	while ($rt = $db->fetch_array($query)){
		$colonydb[]=$rt;
	}
	require_once(PrintEot('profile'));footer();
}elseif ($action=='show'){
	if ($uid){
		$sql="m.uid='$uid'";
	} else{
		$sql="m.username='$username'";
	}
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	$userdb   = $db->get_one("SELECT m.*,md.thisvisit,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.lastvisit,md.lastpost,md.todaypost,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE $sql");
	if (!$userdb) {
		$errorname='';
		Showmsg('user_not_exists');
	}
	if ($windid != $userdb['username'] && !$gp_allowprofile && $windid != $manager){
		Showmsg('profile_right');
	}

	require_once(R_P.'require/credit.php');
	$creditdb = GetCredit($userdb['uid']);
	$query = $db->query("SELECT cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid='$userdb[uid]'");
	while ($rt = $db->fetch_array($query)){
		$colonydb[]=$rt;
	}
	include_once(D_P.'data/bbscache/md_config.php');
	if($md_ifopen){
		include_once(D_P.'data/bbscache/medaldb.php');
		if($userdb['medals']){
			$userdb['medals'] = explode(',',$userdb['medals']);
		}else{
			$userdb['medals'] = '';
		}
	}
	$newmemberid=getmemberid($userdb['postnum'],$userdb['rvrc'],$userdb['money'],$userdb['credit'],$userdb['onlinetime']);
	if ($userdb['memberid'] != $newmemberid){
		$userdb['memberid']=$newmemberid;
		$db->update("UPDATE pw_members SET memberid='$newmemberid' WHERE uid='$userdb[uid]'");
	}
	if($db_autoban){
		require_once(R_P.'require/autoban.php');
		autoban($userdb['uid']);
	}
	if ($userdb['groupid']=='6'){
		$bandb=$db->get_one("SELECT * FROM pw_banuser WHERE uid='$userdb[uid]'");
		if (!$bandb){
			$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$userdb[uid]'");
			$userdb['groupid']=-1;
		} elseif ($bandb['type']==1 && $timestamp-$bandb['startdate']>$bandb['days']*86400){
			$db->update("DELETE FROM pw_banuser WHERE uid='$userdb[uid]'");
			$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$userdb[uid]'");
			$userdb['groupid']=-1;
		}
	}

	$userdb['rvrc']=floor($userdb['rvrc']/10);
	if ($userdb['site'] && strpos($userdb['site'],"://")==false){
		$userdb['site']="http://$userdb[site]";
	}
	if ($userdb['groupid']=='-1'){
		$systitle='';
	} else{
		$systitle=$ltitle[$userdb['groupid']];
	}
	isset($ltitle[$userdb['memberid']]) && $memtitle=$ltitle[$userdb['memberid']];
	/*
	*插件形象
	*/
	/*if ($$userdb['xuni']==1)
		$usericon=getwindxiu($userdb['username'],$postxuni,140,226);
	else
	{
		if ($userdb['icon']=="")
			$usericon="<img src=\"$imgpath/face/0.gif\" width=%70>";
		else*/
			$usericon=showfacedesign($userdb['icon']);
	//}
	$rawauthor=rawurlencode($userdb['username']);
	if ($userdb['publicmail']==1){	  
		$sendemail="<a href=sendemail.php?uid=$userdb[uid]>$userdb[email]</a>";
	} else{
		$sendemail="<a href=sendemail.php?uid=$userdb[uid]><img src=$imgpath/$stylepath/read/email.gif border=0></a>";
		if ($windid==$manager)
			$sendemail.="( $userdb[email] )";
	}
	$logininfo=explode('|',$userdb['onlineip']);
	$lasttime=get_date($userdb['lastvisit'],"Y-m-d");
	$posttime ? $posttime=get_date($userdb['lastpost'],"Y-m-d") : $posttime="x";
	if (!$userdb['todaypost']||$userdb['lastpost']<$tdtime) $userdb['todaypost']=0;
	$averagepost=floor($userdb['postnum']/(ceil(($timestamp-$userdb['regdate'])/(3600*24))));
	$show_regdate=get_date($userdb['regdate'],"Y-m-d");
	
	if ($db_signwindcode && $userdb['signchange']==2){
		if($_G['imgwidth'] && $_G['imgheight']){
			$db_windpic['picwidth']  = $_G['imgwidth'];
			$db_windpic['picheight'] = $_G['imgheight'];
		}
		$_G['fontsize'] && $db_windpic['size'] = $_G['fontsize'];
		$tempsign=convert($userdb['signature'],$db_windpic,2);
	} else{
		$tempsign=$userdb['signature'];
	}
	$tempsign=str_replace("\n","<br>",$tempsign);
	$tempintroduce=str_replace("\n","<br>",$userdb['introduce']);
	if ($userdb['onlinetime']){
		$userdb['onlinetime']=floor($userdb['onlinetime']/3600);
	} else{
		$userdb['onlinetime']=0;
	}
	require_once(PrintEot('showuserdb'));footer();
}elseif ($action=="modify"){
	$userdb = $db->get_one("SELECT m.*,md.currency,md.starttime FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.uid='$winddb[uid]'");
	list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize)=explode("\t",$db_upload);

	if (empty($_POST['step'])){

		require_once(R_P.'require/header.php');
		if ($userdb['publicmail']) $ifchecked="checked";
		$fp=opendir(D_P."data/style/");


		$selected[$skin]='selected';
		while ($skinfile=readdir($fp)){
			if (eregi("\.php$",$skinfile)) {
				$skinfile=str_replace(".php","",$skinfile);
				$choseskin.="<option value=\"$skinfile\" $selected[$skinfile]>$skinfile</option>";
			}
		}closedir($fp);

		$userdb['timedf'] < 0 ? ${'zone_0'.str_replace('.','_',abs($userdb['timedf']))}='selected' : ${'zone_'.str_replace('.','_',$userdb['timedf'])}='selected';
		if ($userdb['datefm']){
			if (strpos($userdb['datefm'],'h:i A')){
				$userdb['datefm']=str_replace(' h:i A','',$userdb['datefm']);
				$check_12='checked';
			} else{
				$userdb['datefm']=str_replace(' H:i','',$userdb['datefm']);
				$check_24='checked';
			}
			$userdb['datefm'] = str_replace('m', 'mm', $userdb['datefm']);
			$userdb['datefm'] = str_replace('n', 'm', $userdb['datefm']);
			$userdb['datefm'] = str_replace('d', 'dd', $userdb['datefm']);
			$userdb['datefm'] = str_replace('j', 'd', $userdb['datefm']);
			$userdb['datefm'] = str_replace('y', 'yy', $userdb['datefm']);
			$userdb['datefm'] = str_replace('Y', 'yyyy', $userdb['datefm']);
			$d_type_1='checked';
		} else{
			$userdb['datefm']='yyyy-mm-dd';
			$d_type_0='checked';$check_24='checked';
		}
		$userdb['t_num'] && ${'T_'.$userdb['t_num']}='selected';
		$userdb['p_num'] && ${'P_'.$userdb['p_num']}='selected';
		$windcode='';
		if ($db_signwindcode){
			if ($db_signwindcode){
				$db_windpic['pic'] ? $windcode.="<br> [img] - Open" : $windcode.="<br> [img] - Close";
				$db_windpic['flash'] ? $windcode.="<br> [flash] - Open" : $windcode.="<br> [flash] - Close";
			}
		}
		$sexselect[$userdb['gender']]="selected";
		$getbirthday = explode("-",$userdb['bday']);
		$yearslect[(int)$getbirthday[0]]="selected";
		$monthslect[(int)$getbirthday[1]]="selected";
		$dayslect[(int)$getbirthday[2]]="selected";
		$imgpatherror='';
		if (ereg("^http",$picpath)){
			$picpath=basename($picpath);//如果您将图片路径更名为其他服务器上的图片,请务必保持图片目录同名,否则出错不在程序bug 之内
			if (!file_exists($picpath)){
				$imgpatherror=1;
			}
		}

		$iconarray=explode('|',$userdb['icon']);
		$uploadimg='';
		if ($iconarray[1] && ereg("^[0-9]{1,9}",$iconarray[1])){
			$uploadimg=$iconarray[1];
			$iconarray[1]='';
		}
		if (!$iconarray[0]){
			$iconarray[0]='none.gif';
		}
		$img=@opendir("$imgdir/face");
		while ($imagearray=@readdir($img)){
			if ($imagearray!="." && $imagearray!=".." && $imagearray!="" && $imagearray!="none.gif"){
				if ($imagearray==$iconarray[0]){
					$imgselect.= "<option selected value='$imagearray'>$imagearray</option>";
				} else{
					$imgselect.="<option value='$imagearray'>$imagearray</option>";
				}
			}
		}
		@closedir($img);
		$userdb['receivemail']?$email_open='checked':$email_close='checked';

		$groupselect=$maingroup='';
		if ($userdb['groups']){
			if ($userdb['groupid']=='-1'){
				$maingroup="<option></option>";
			} else{
				$maingroup="<option value='$userdb[groupid]'>".$ltitle[$userdb['groupid']]."</option>";
			}
			$groups=explode(',',$userdb['groups']);
			foreach($groups as $key => $value){
				if ($value){
					$groupselect.="<option value='$value'>$ltitle[$value]</option>";
				}
			}
		}
		list($pay,$payemail) = explode("\t",$userdb['payemail']);
		$pay==1 ? $pay_1 = 'checked' : $pay_2 = 'checked';
		if ($db_signmoney && strpos($db_signgroup,",$groupid,") !== false){
			$userdb['showsign'] ? $showsign_1 = 'checked' : $showsign_0 = 'checked';
			$days = $haveshow = 0;
			if($userdb['starttime'] && $userdb['starttime'] <= $tdtime){
				$haveshow = floor(($tdtime - $userdb['starttime'])/86400)+1;
			}
			if ($userdb['currency'] <= 0){
				$days = 0;
			}else{
				$days = floor($userdb['currency']/$db_signmoney);
			}
		}
		require_once(PrintEot('profile'));footer();
	}  elseif ($_POST['step']==2){
		Add_S($userdb);
		if (!empty($propwd)||$userdb['email']!=$proemail){
			if ($propwd && $windid==$manager){
				Showmsg('pro_manager');
			}
			$oldpwd=md5($oldpwd);
			if (strlen($userdb['password'])==16){
				$oldpwd=substr($oldpwd,8,16);/*支持 16 位 md5截取密码*/
			}
			if ($userdb['password']!=$oldpwd){
				Showmsg('password_confirm_fail');
			}
			include_once(D_P."data/bbscache/dbreg.php");
			if ($rg_emailcheck && $userdb['email']!=$proemail){
				Showmsg('pro_emailcheck');
			}
		}
		$groups='';
		if ($newgroupid && $newgroupid != $userdb['groupid']){
			if (strpos($userdb['groups'],','.$newgroupid.',')===false){
				Showmsg('undefined_action');
			}else{
				if ($userdb['groupid']=='-1'){
					$groups=str_replace(','.$newgroupid.',',',',$userdb['groups']);
				} else{
					$groups=str_replace(','.$newgroupid.',',','.$userdb['groupid'].',',$userdb['groups']);
				}
				$groups=",groupid='$newgroupid',groups='$groups'";
			}
		}
		if ($d_type && $date_f){
			if (strpos($date_f,'mm')!==false){
				$date_f = str_replace('mm','m',$date_f);
			} else{
				$date_f = str_replace('m','n',$date_f);
			}
			if (strpos($date_f,'dd')!==false){
				$date_f = str_replace('dd','d',$date_f);
			} else{
				$date_f = str_replace('d','j',$date_f);
			}
			$date_f = str_replace('yyyy','Y',$date_f);
			$date_f = str_replace('yy','y',$date_f);
			$timefm=$time_f=='12' ? ' h:i A' :' H:i';
			$date_f.=$timefm;
		} else{
			$date_f='';
		}

		!is_numeric($time_cv) && $time_cv='';
		if (!in_array($groupid,array(3,4,5)) && $t_num>40){
			$t_num='';
		}
		if (!@in_array($groupid,array(3,4,5)) && $p_num>30){
			$p_num='';
		}
		$userdb['yahoo']=Char_cv($proyahoo);
		$userdb['msn']=Char_cv($promsn);
		$userdb['email']=$proemail;
		$userdb['oicq']=$prooicq;
		$userdb['icq']=$proicq;
		$userdb['site']=$prohomepage;
		$userdb['gender']=$progender;
		$userdb['location']=$profrom;
		$userdb['signature']=$prosign;
		$userdb['introduce']=$prointroduce;
		if (!empty($propwd)){
			$propwd!=$check_pwd && Showmsg('password_confirm');
			$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
			foreach($S_key as $value){
				if (strpos($propwd,$value)!==false){ 
					Showmsg('illegal_password'); 
				}
			}
			$userdb['password']=$propwd;
			$userdb['password']=str_replace("\t","",$userdb['password']); 
			$userdb['password']=str_replace("\r","",$userdb['password']); 
			$userdb['password']=str_replace("\n","",$userdb['password']);
			$userdb['password']=md5($userdb['password']);
		}

		$userdb['publicmail'] =(int)$propublicemail;
		$userdb['receivemail']=$proreceivemail;
		if (!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$userdb['email'])) {
			Showmsg('illegal_email'); 
		}
		if ($payemail && !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$payemail)) {
			Showmsg('illegal_email'); 
		}
		if (!ereg("^[0-9]{0,}$",$userdb['oicq'])){
			Showmsg('illegal_OICQ');
		}
		if (!ereg("^[0-9]{0,}$",$userdb['icq'])){
			Showmsg('illegal_OICQ'); 
		}
		if (strlen($userdb['signature'])>$gp_signnum && $gp_signnum!=0){
			Showmsg('sign_limit');
		}
		if (strlen($userdb['introduce'])>500){
			Showmsg('introduce_limit'); 
		}
		if (@include_once(D_P."data/bbscache/wordsfb.php")){
			if($wordsfb){
				foreach($wordsfb as $key => $value){
					if(strpos($userdb['signature'],(string)$key) !== false){
						$banword = $key;
						Showmsg('post_wordsfb');
					}
					if(strpos($userdb['introduce'],(string)$key) !== false){
						$banword = $key;
						Showmsg('post_wordsfb');
					}
				}
			}
		}
		if (!empty($proyear)||!empty($promonth)||!empty($proday)){
			$userdb['bday']=$proyear."-".$promonth."-".$proday;
		}
		$userdb['site']		= Char_cv($userdb['site']);
		$userdb['introduce']= Char_cv($userdb['introduce']);
		$userdb['signature']= Char_cv($userdb['signature']);	 
		$userdb['location'] = Char_cv($userdb['location']);
		$lxsign=convert($userdb['signature'],$db_windpic,2);
		if ($lxsign==$userdb['signature']){
			$userdb['signchange']=1;
		} else{
			$userdb['signchange']=2;
		}
		if ($gp_allowhonor) {
			$prohonor=Char_cv($prohonor);
		} else{
			$prohonor=$userdb['honor'];
		}
		$prohonor=substrs($prohonor,30);
		$iconarray=explode('|',$userdb['icon']);

		if ($iconarray[1] && ereg("^[0-9]{1,9}",$iconarray[1])){
			$haveupload=1;
		} else{
			$haveupload=0;
		}

		unset($source);
		$upload=$_FILES['upload'];
		if (is_array($upload)){
			$upload_name=$upload['name'];
			$upload_size=$upload['size'];
			$upload=$upload['tmp_name'];
		}
		if ($upload && $upload!='none'){
			
			$checkuplode=1;
			$attach_ext = substr(strrchr($upload_name,'.'),1);
			$attach_ext=strtolower($attach_ext);

			if (function_exists('is_uploaded_file') && !is_uploaded_file($upload)){
				$checkuplode=0;
			} elseif ($upload['error']==4){
				$checkuplode=0;
			} elseif (!in_array($attach_ext, array('gif','jpg','bmp'))) {
				Showmsg('illegal_loadimg');
			}
			if ($checkuplode==1){
				if ($haveupload==1){
					Showmsg('pro_loadimg_fail');
				}
				!$db_upload && Showmsg('pro_loadimg_close');
				$gp_upload==0 && Showmsg('pro_loadimg_right');
				if ($upload_size>$db_imgsize){
					Showmsg('pro_loadimg_limit');
				}
				$source=$imgdir.'/upload/'.(int)$winduid.'.'.$attach_ext;
				$proownportait[0]=$winduid.'.'.$attach_ext;
				if (function_exists("move_uploaded_file") && @move_uploaded_file($upload, $source)){
					chmod($source,0777);
				}elseif (@copy($upload, $source)){
					chmod($source,0777);
				}elseif (is_readable($upload) && $attcontent=readover($upload)){
					writeover($source,$attcontent);
					chmod($source,0777);
				}

				if (function_exists('getimagesize') && !(list($proownportait[1],$proownportait[2])=getimagesize($source))){
					P_unlink($source);
					Showmsg('pro_loadimg_error');
				}
				if ($proownportait[1]>$db_imgwidth || $proownportait[2]>$db_imglen){
					Showmsg('pro_loadimg_sizelimit');
				}
				unset($proownportait[1],$proownportait[2]);
			}else{
				Showmsg('pro_loadimg_error');
			}
		}elseif ($gp_allowportait && $proownportait[0]){
			
			if ($haveupload==1){
				Showmsg('pro_custom_fail');
			}
			$proownportait[0]=Char_cv($proownportait[0]);
			if (!ereg("^http",$proownportait[0]) || strrpos($proownportait[0],'|')!==false){
				Showmsg('illegal_customimg');
			}
			if (!ereg("^[0-9]{2,3}$",$proownportait[1]) || !ereg("^[0-9]{2,3}$",$proownportait[2]) || $proownportait[1]>185 || $proownportait[2]>200){
				Showmsg('illegal_customimg');
			}
		}elseif ($haveupload==1){
			$proownportait[0]=$iconarray[1];
		}else{
			unset($proownportait);
		}
		$userdb['icon']=str_replace('|','',Char_cv($proicon)).'|'.$proownportait[0].'|'.(int)$proownportait[1].'|'.(int)$proownportait[2];
		strlen($userdb['icon'])>100 && Showmsg('illegal_customimg');

		if (GetCookie('skinco') && $tpskin!=GetCookie('skinco')){
			Cookie('skinco','',0);
		}
		$payemail="$pay\t$payemail";
		$showsign = $showsign ? 1 : 0;
		if ($userdb['showsign']==1 && $showsign==0){
			$db->update("UPDATE pw_memberdata SET starttime='0' WHERE uid='$winduid'");
		} elseif ($userdb['showsign']==0 && $showsign==1){
			if($userdb['currency'] < $db_signmoney){
				Showmsg('noenough_currency');
			}
			$db->update("UPDATE pw_memberdata SET starttime='$tdtime',currency=currency-'$db_signmoney' WHERE uid='$winduid'");
			require_once(R_P.'require/tool.php');
			$logdata=array(
				'type'		=>	'sign',
				'nums'		=>	0,
				'money'		=>	0,
				'descrip'	=>	'sign_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'currency'	=>	$db_signmoney
			);
			writetoollog($logdata);
		}
		$db->update("UPDATE pw_members SET password ='$userdb[password]',email='$userdb[email]' $groups,honor='$prohonor',publicmail='$userdb[publicmail]',icon='$userdb[icon]',gender='$userdb[gender]',signature='$userdb[signature]',introduce='$userdb[introduce]',oicq='$userdb[oicq]',icq='$userdb[icq]',yahoo='$userdb[yahoo]',msn='$userdb[msn]',site='$userdb[site]',location='$userdb[location]',bday='$userdb[bday]',style='$tpskin',datefm='$date_f',timedf='$timedf',t_num='$t_num',p_num='$p_num',receivemail='$userdb[receivemail]',signchange='$userdb[signchange]',showsign='$showsign',payemail='$payemail' WHERE uid='$winduid'");

		refreshto("profile.php?action=show&uid=$winddb[uid]",'operate_success');
	}
}elseif($action=='permission'){

	$per = array();
	$per['hide']	= $gp_allowhide		? 1 : 0;
	$per['read']	= $gp_allowread		? 1 : 0;
	$per['search']	= $gp_allowsearch	? 1 : 0;
	$per['member']	= $gp_allowmember	? 1 : 0;
	$per['profile']	= $gp_allowprofile	? 1 : 0;
	$per['show']	= $_G['show']		? 1 : 0;
	$per['report']	= $gp_allowreport	? 1 : 0;
	$per['upload']	= $gp_upload		? 1 : 0;
	$per['portait']	= $gp_allowportait	? 1 : 0;
	$per['honor']	= $gp_allowhonor	? 1 : 0;

	$per['post']	= $gp_allowpost		? 1 : 0;
	$per['rp']		= $gp_allowrp		? 1 : 0;
	$per['newvote']	= $gp_allownewvote	? 1 : 0;
	$per['vote']	= $gp_allowvote		? 1 : 0;
	$per['vwvt']	= $_G['viewvote']	? 1 : 0;
	$per['html']	= $gp_htmlcode		? 1 : 0;
	$per['hidden']	= $gp_allowhidden	? 1 : 0;
	$per['encode']	= $gp_allowencode	? 1 : 0;
	$per['sell']	= $gp_allowsell		? 1 : 0;
	$per['mark']	= $_G['markable']	? 1 : 0;

	$per['attach']	= $gp_allowupload	? 1 : 0;
	$per['down']	= $gp_allowdownload	? 1 : 0;
	!$_G['uploadmaxsize'] && $_G['uploadmaxsize'] = ceil($db_uploadmaxsize/1024);
	!$_G['uploadtype'] && !$_G['uploadtype'] = $db_uploadfiletype;

	require_once(PrintEot('profile'));footer();
}elseif($action=='forumright'){
	if(!$fid){
		Showmsg('data_error');
	}
	require_once(R_P.'require/forum.php');
	include_once(D_P."data/bbscache/creditdb.php");

	list($db_moneyname,,$db_rvrcname,,,)=explode("\t",$db_credits);

	$rt=$db->get_one("SELECT f.name,f.allowvisit,f.allowpost,f.allowrp,f.allowdownload,f.allowupload,fe.creditset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid'");
	$creditset    = get_creditset($rt['creditset'],$db_creditset);

	foreach($creditset as $key=>$val){
		if(is_numeric($key)){
			$creditset[$key]['name'] = $_CREDITDB[$key][0];
		}else{
			switch($key){
				case 'rvrc'   : $creditset[$key]['name'] = $db_rvrcname;break;
				case 'money'  : $creditset[$key]['name'] = $db_moneyname;break;
				case 'credit' : $creditset[$key]['name'] = $db_creditname;break;
			}
		}
		if($key=='rvrc'){
			foreach($val as $k=>$v){
				$creditset[$key][$k] /= 10;
			}
		}
	}
	if($rt['allowvisit'] && strpos($rt['allowvisit'],",$groupid,")===false){
		$per['visit'] = 0;
	}else{
		$per['visit'] = 1;
	}
	if($rt['allowpost'] && strpos($rt['allowpost'],",$groupid,")===false){
		$per['post'] = 0;
	}elseif(!$rt['allowpost'] && $gp_allowpost==0){
		$per['post'] = 0;
	}else{
		$per['post'] = 1;
	}
	if($rt['allowrp'] && strpos($rt['allowrp'],",$groupid,")===false){
		$per['rp'] = 0;
	}elseif(!$rt['allowrp'] && $gp_allowpost==0){
		$per['rp'] = 0;
	}else{
		$per['rp'] = 1;
	}
	if($rt['allowdownload'] && strpos($rt['allowdownload'],",$groupid,")===false){
		$per['down'] = 0;
	}elseif(!$rt['allowdownload'] && $gp_allowpost==0){
		$per['down'] = 0;
	}else{
		$per['down'] = 1;
	}
	if($rt['allowupload'] && strpos($rt['allowupload'],",$groupid,")===false){
		$per['upload'] = 0;
	}elseif(!$rt['allowupload'] && $gp_allowpost==0){
		$per['upload'] = 0;
	}else{
		$per['upload'] = 1;
	}

	require_once(PrintEot('profile'));footer();
}elseif($action=='log'){
	if(!$_G['atclog']){
		Showmsg('no_atclog_right');
	}
	require_once GetLang('log');
	require_once(R_P.'require/forum.php');
	include_once(D_P.'data/bbscache/forum_cache.php');
	$sqladd = "WHERE username1='".addslashes($windid)."'";
	if ($type && $logtype[$type]){
		$sqladd .= " AND type='$type'";
	}
	$type_sel[$type]='selected';
	$db_perpage = 30;

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_adminlog $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"profile.php?action=log&type=$type&");
	$query = $db->query("SELECT * FROM pw_adminlog $sqladd ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']  = get_date($rt['timestamp']);
		$logdb[] = $rt;
	}
	require_once(PrintEot('profile'));footer();
}elseif($action=='favor'){
	if($groupid=='guest'){
		Showmsg('not_login');
	}
	if(!$job){
		include_once(D_P.'data/bbscache/forum_cache.php');
		require_once(R_P.'require/forum.php');
		$favordb=array();
		$favor=$db->get_one("SELECT tids FROM pw_favors WHERE uid='$winddb[uid]'");
		if($favor['tids']){
			$query=$db->query("SELECT t.fid,t.tid,t.subject,t.postdate,t.author,t.authorid,t.replies,t.hits FROM pw_threads t WHERE t.tid IN($favor[tids]) ORDER BY t.postdate DESC");
			while($rt=$db->fetch_array($query)){
				$newtids.=$rt['tid'].',';
				$rt['subject'] = substrs($rt['subject'],50);
				$rt['postdate']=get_date($rt['postdate']);
				$rt['forum']=$forum[$rt['fid']]['name'];
				$favordb[]=$rt;
			}
			$newtids=substr($newtids,0,-1);
			$newtids==$favor['tids'] || $db->update("UPDATE pw_favors SET tids='$newtids' WHERE uid='$winddb[uid]'");
		}
		require_once PrintEot('profile');footer();
	} elseif($job=='add'){
		if(empty($tid)||!is_numeric($tid)){
			Showmsg('illegal_tid');
		}
		$rs=$db->get_one("SELECT tids FROM pw_favors WHERE uid='$winddb[uid]'");
		if($rs){
			$tids=$rs['tids'];
			$tid_db=explode(",",$tids);
			if(count($tid_db)>$_G['maxfavor']){
				Showmsg('job_favor_full');
			}
			$olddb=','.$tids.',';
			if(strpos($olddb,','.$tid.',')!==false){
				Showmsg('job_favor_error');
			}
			$tids.=','.$tid;
			$db->update("UPDATE pw_favors SET tids='$tids' WHERE uid='$winddb[uid]'");
		}else{
			$db->update("INSERT INTO pw_favors(uid,tids) VALUES('$winddb[uid]','$tid')");
		}
		refreshto("profile.php?action=favor",'operate_success');
	} elseif($job=='clear'){
		$rs=$db->get_one("SELECT tids FROM pw_favors WHERE uid='$winddb[uid]'");
		if($rs){
			$tids=$rs['tids'];
			$tid_db=explode(",",$tids);
			$t_count=count($tid_db);
			for($i=0;$i<$t_count;$i++){
				if(@in_array($tid_db[$i],$delid)){
					unset($tid_db[$i]);
				}
			}
			$new_tids=implode(",",$tid_db);
			if($new_tids!=$tids){
				if($new_tids){
					$db->update("UPDATE pw_favors SET tids='$new_tids' WHERE uid='$winddb[uid]'");
				}else{
					$db->update("DELETE FROM pw_favors WHERE uid='$winddb[uid]'");
				}
				refreshto("profile.php?action=favor",'operate_success');
			}else{
				Showmsg('job_favor_del');
			}
		}else{
			Showmsg('job_favor_del');
		}
	}
}
?>