<?php
require_once('global.php');
require_once(R_P.'require/bbscode.php');
require_once(R_P.'require/postfunc.php');
include_once(D_P.'data/bbscache/level.php');
include_once(D_P.'data/bbscache/creditdb.php');
//require R_P.'require/windxiu.php';//插件形象

$groupid=='guest' && Showmsg('not_login');

if(in_array($action,array('modify','show'))){
	include_once(D_P.'data/bbscache/customfield.php');
	$fieldadd=$tableadd='';
	foreach($customfield as $key=>$val){
		$val['id'] = (int) $val['id'];
		$fieldadd .= ",mb.field_$val[id]";
	}
	$fieldadd && $tableadd="LEFT JOIN pw_memberinfo mb ON mb.uid=m.uid";
}
require_once(R_P.'require/header.php');
if(!$action){
	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize)=explode("\t",$db_upload);
	$userdb = $db->get_one("SELECT m.*,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.lastvisit,md.onlinetime,mb.deposit,mb.ddeposit FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) LEFT JOIN pw_memberinfo mb USING(uid) WHERE m.uid='$winduid'");
	require_once(R_P.'require/credit.php');
	$creditdb = GetCredit($winduid);
	$iconarray= explode('|',$userdb['icon']);
	$uploadimg= '';
	$userface = showfacedesign($userdb['icon']);
	$systitle = $userdb['groupid']=='-1' ? '' : $ltitle[$userdb['groupid']];
	$memtitle = $ltitle[$userdb['memberid']];
	$userdb['rvrc']=floor($userdb['rvrc']/10);
	if($userdb['onlinetime']){
		$userdb['onlinetime']=floor($userdb['onlinetime']/3600);
	} else{
		$userdb['onlinetime']=0;
	}
	$userdb['regdate']=get_date($userdb['regdate'],"Y-m-d");
	$userdb['lastvisit']=get_date($userdb['lastvisit'],"Y-m-d");

	$msgdb = $favordb = $colonydb = array();
	$query = $db->query("SELECT mid,fromuid,touid,username,ifnew,title,mdate FROM pw_msg WHERE type='rebox' AND touid='$winduid' ORDER BY mdate DESC LIMIT 5");
	while($msg=$db->fetch_array($query)){
		$msg['title']=substrs($msg['title'],35);
		$msg['mdate']=get_date($msg['mdate']);
		$msg['username']=$msg['username'];
		$msg['to']=$windid;
		$msgdb[]=$msg;
	}

	$rt=$db->get_one("SELECT tids FROM pw_favors WHERE uid='$winddb[uid]'");
	if($rt['tids']){
		$tidarr = explode(',',str_replace('|',',',$rt['tids']));
		$tids   = $num = 0;
		foreach ($tidarr as $key => $value){
			if(is_numeric($value)){
				$num ++;
				$tids .= ','.$value;
			}
			if($num == 5) break;
		}
		include_once(D_P.'data/bbscache/forum_cache.php');
		$query=$db->query("SELECT fid,tid,subject,postdate,author,authorid,replies,hits FROM pw_threads WHERE tid IN($tids) ORDER BY postdate DESC");
		while ($rt=$db->fetch_array($query)){
			$rt['subject']=substrs($rt['subject'],35);
			$rt['postdate']=get_date($rt['postdate']);
			$rt['forum']=$forum[$rt['fid']]['name'];
			$favordb[]=$rt;
		}
		unset($forum,$tidarr,$tids,$num);
	}
	$query = $db->query("SELECT cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid='$winduid'");
	while ($rt = $db->fetch_array($query)){
		$colonydb[]=$rt;
	}
	require_once(PrintEot('profile'));footer();
}elseif($action=='show'){

	$sql = $uid ? "m.uid='$uid'" : "m.username='$username'";	list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
	$userdb = $db->get_one("SELECT m.*,md.thisvisit,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.lastvisit,md.lastpost,md.todaypost,md.onlineip $fieldadd FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid $tableadd WHERE $sql");
	if(!$userdb){
		$errorname='';
		Showmsg('user_not_exists');
	}
	if($windid != $userdb['username'] && !$gp_allowprofile){
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
	$usercredit = array(
		'postnum'	=> $userdb['postnum'],
		'digests'	=> $userdb['digests'],
		'rvrc'		=> $userdb['rvrc'],
		'money'		=> $userdb['money'],
		'credit'	=> $userdb['credit'],
		'onlinetime'=> $userdb['onlinetime'],
	);
	foreach($creditdb as $key=>$value){
		$usercredit[$key] = $value[1];
	}
	$upgradeset  = unserialize($db_upgrade);
	$totalcredit = CalculateCredit($usercredit,$upgradeset);
	$newmemberid = getmemberid($totalcredit);
	if($userdb['memberid'] != $newmemberid){
		$userdb['memberid'] = $newmemberid;
		$db->update("UPDATE pw_members SET memberid='$newmemberid' WHERE uid='$userdb[uid]'");
	}
	if($db_autoban){
		require_once(R_P.'require/autoban.php');
		autoban($userdb['uid']);
	}
	if($userdb['groupid']=='6'){
		$bandb=$db->get_one("SELECT * FROM pw_banuser WHERE uid='$userdb[uid]'");
		if(!$bandb){
			$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$userdb[uid]'");
			$userdb['groupid']=-1;
		} elseif($bandb['type']==1 && $timestamp-$bandb['startdate']>$bandb['days']*86400){
			$db->update("DELETE FROM pw_banuser WHERE uid='$userdb[uid]'");
			$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$userdb[uid]'");
			$userdb['groupid']=-1;
		}
		$bandb['startdate']=get_date($bandb['startdate']);
	}

	$userdb['rvrc']=floor($userdb['rvrc']/10);
	if($userdb['site'] && strpos($userdb['site'],"://")==false){
		$userdb['site']="http://$userdb[site]";
	}
	$systitle = $userdb['groupid']=='-1' ? '' : $ltitle[$userdb['groupid']];
	$memtitle = $ltitle[$userdb['memberid']];
	/*
	*插件形象
	*/
	/*if($$userdb['xuni']==1)
		$usericon=getwindxiu($userdb['username'],$postxuni,140,226);
	else
	{
		if($userdb['icon']=="")
			$usericon="<img src=\"$imgpath/face/0.gif\" width=%70>";
		else*/
			$usericon=showfacedesign($userdb['icon']);
	//}
	$rawauthor=rawurlencode($userdb['username']);
	if($userdb['publicmail']==1){	  
		$sendemail="<a href=sendemail.php?uid=$userdb[uid]>$userdb[email]</a>";
	} else{
		$sendemail="<a href=sendemail.php?uid=$userdb[uid]><img src=$imgpath/$stylepath/read/email.gif border=0></a>";
		$windid==$manager && $sendemail.="( $userdb[email] )";
	}
	$logininfo= explode('|',$userdb['onlineip']);
	$lasttime = get_date($userdb['lastvisit'],"Y-m-d");
	$posttime = $posttime ? get_date($userdb['lastpost'],"Y-m-d") : "x";
	if(!$userdb['todaypost']||$userdb['lastpost']<$tdtime) $userdb['todaypost']=0;
	$averagepost=floor($userdb['postnum']/(ceil(($timestamp-$userdb['regdate'])/(3600*24))));
	$show_regdate=get_date($userdb['regdate'],"Y-m-d");

	if($db_signwindcode && $userdb['signchange']==2){
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
	if($userdb['onlinetime']){
		$userdb['onlinetime']=floor($userdb['onlinetime']/3600);
	} else{
		$userdb['onlinetime']=0;
	}
	require_once(PrintEot('showuserdb'));footer();
}elseif($action=="modify"){
	$userdb = $db->get_one("SELECT m.*,md.currency,md.starttime,md.editor $fieldadd FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid $tableadd WHERE m.uid='$winddb[uid]'");
	list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize)=explode("\t",$db_upload);

	if(empty($_POST['step'])){
		require_once(R_P.'require/header.php');
		include_once(D_P."data/bbscache/dbreg.php");
		!$rg_timestart && $rg_timestart=1960;
		!$rg_timeend && $rg_timeend=2000;
		$userdb['publicmail'] && $ifchecked="checked";
		
		require_once(R_P."require/forum.php");
		$choseskin=getstyles($skin);

		$userdb['timedf'] < 0 ? ${'zone_0'.str_replace('.','_',abs($userdb['timedf']))}='selected' : ${'zone_'.str_replace('.','_',$userdb['timedf'])}='selected';
		if($userdb['datefm']){
			if(strpos($userdb['datefm'],'h:i A')){
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
		if($db_signwindcode){
			$db_windpic['pic'] ? $windcode.="<br> [img] - Open" : $windcode.="<br> [img] - Close";
			$db_windpic['flash'] ? $windcode.="<br> [flash] - Open" : $windcode.="<br> [flash] - Close";
		}
		$sexselect[$userdb['gender']]="selected";
		$getbirthday = explode("-",$userdb['bday']);
		$yearslect[(int)$getbirthday[0]]="selected";
		$monthslect[(int)$getbirthday[1]]="selected";
		$dayslect[(int)$getbirthday[2]]="selected";
		$imgpatherror='';
		if(ereg("^http",$picpath)){
			$picpath=basename($picpath);//如果您将图片路径更名为其他服务器上的图片,请务必保持图片目录同名,否则出错不在程序bug 之内
			if(!file_exists($picpath)){
				$imgpatherror=1;
			}
		}

		$iconarray=explode('|',$userdb['icon']);
		$uploadimg='';
		if($iconarray[1] && ereg("^[0-9]{1,9}",$iconarray[1])){
			$uploadimg=$iconarray[1];
			if($db_ftpweb && !file_exists($attachdir.'/upload/'.$uploadimg)){
				$upimgurl = $db_ftpweb.'/upload/'.$uploadimg;
			}else{
				$upimgurl = $attachpath.'/upload/'.$uploadimg;
			}
			$iconarray[1]='';
		}
		!$iconarray[0] && $iconarray[0]='none.gif';

		$img=@opendir("$imgdir/face");
		while ($imagearray=@readdir($img)){
			if($imagearray!="." && $imagearray!=".." && $imagearray!="" && $imagearray!="none.gif"){
				if($imagearray==$iconarray[0]){
					$imgselect.= "<option selected value='$imagearray'>$imagearray</option>";
				} else{
					$imgselect.="<option value='$imagearray'>$imagearray</option>";
				}
			}
		}
		@closedir($img);
		$userdb['receivemail'] ? $email_open = 'checked' : $email_close = 'checked';
		$userdb['editor'] ? $editor_wys = 'checked' : $editor_com = 'checked';

		$groupselect=$maingroup='';
		if($userdb['groups']){
			if($userdb['groupid']=='-1'){
				$maingroup="<option></option>";
			} else{
				$maingroup="<option value='$userdb[groupid]'>".$ltitle[$userdb['groupid']]."</option>";
			}
			$groups=explode(',',$userdb['groups']);
			$newgroups = ',';
			foreach($groups as $key => $value){
				if($value && array_key_exists($value,$ltitle)){
					$newgroups .= $value.',';
					$groupselect.="<option value=\"$value\">$ltitle[$value]</option>";
				}
			}
			$newgroups==',' && $newgroups = '';
			if($newgroups != $userdb['groups']){
				$db->update("UPDATE pw_members SET groups='$newgroups' WHERE uid='$winduid'");
			}
		}
		list($pay,$payemail) = explode("\t",$userdb['payemail']);
		is_numeric($pay) ? ${'pay_'.$pay}='checked' : $pay_3='checked';
		if($db_signmoney && strpos($db_signgroup,",$groupid,") !== false){
			$userdb['showsign'] ? $showsign_1 = 'checked' : $showsign_0 = 'checked';
			$days = $haveshow = 0;
			if($userdb['starttime'] && $userdb['starttime'] <= $tdtime){
				$haveshow = floor(($tdtime - $userdb['starttime'])/86400)+1;
			}
			$day = $userdb['currency'] <= 0 ? 0 : floor($userdb['currency']/$db_signmoney);
		}
		require_once(PrintEot('profile'));footer();
	}  elseif($_POST['step']==2){
		Add_S($userdb);

		if($passport_ifopen && $passport_type=='client'){
			$oldpwd=$propwd=$check_pwd='';
			$proemail=$userdb['email'];
			$propublicemail=$userdb['publicmail'];
		}

		if(!empty($propwd)||$userdb['email']!=$proemail){
			if($propwd && $windid==$manager){
				Showmsg('pro_manager');
			}
			$oldpwd=md5($oldpwd);
			if(strlen($userdb['password'])==16){
				$oldpwd=substr($oldpwd,8,16);/*支持 16 位 md5截取密码*/
			}
			if($userdb['password']!=$oldpwd){
				Showmsg('password_confirm_fail');
			}
			include_once(D_P."data/bbscache/dbreg.php");
			if($rg_emailcheck && $userdb['email']!=$proemail){
				Showmsg('pro_emailcheck');
			}
		}
		$groups='';
		if($newgroupid && $newgroupid != $userdb['groupid']){
			if(strpos($userdb['groups'],','.$newgroupid.',')===false){
				Showmsg('undefined_action');
			}else{
				if($userdb['groupid']=='-1'){
					$groups=str_replace(','.$newgroupid.',',',',$userdb['groups']);
					$groups==',' && $groups='';
				} else{
					$groups=str_replace(','.$newgroupid.',',','.$userdb['groupid'].',',$userdb['groups']);
				}
				$groups=",groupid='$newgroupid',groups='$groups'";
			}
		}
		if($d_type && $date_f){
			if(strpos($date_f,'mm')!==false){
				$date_f = str_replace('mm','m',$date_f);
			} else{
				$date_f = str_replace('m','n',$date_f);
			}
			if(strpos($date_f,'dd')!==false){
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
		if(!in_array($groupid,array(3,4,5)) && $t_num>40){
			$t_num='';
		}
		if(!@in_array($groupid,array(3,4,5)) && $p_num>30){
			$p_num='';
		}
		$userdb['yahoo']=Char_cv($proyahoo);
		$userdb['msn']=Char_cv($promsn);
		$userdb['email']=$proemail;
		$userdb['oicq']=$prooicq;
		$userdb['icq']=$proicq;
		$userdb['kf']=$prokf;
		$userdb['site']=$prohomepage;
		$userdb['gender']=(int)$progender;
		$userdb['location']=$profrom;
		$userdb['signature']=$prosign;
		$userdb['introduce']=$prointroduce;
		if(!empty($propwd)){
			$propwd!=$check_pwd && Showmsg('password_confirm');
			$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
			foreach($S_key as $value){
				if(strpos($propwd,$value)!==false){ 
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
		$userdb['receivemail']=(int)$proreceivemail;
		if(!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$userdb['email'])) {
			Showmsg('illegal_email'); 
		}
		if($payemail && !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$payemail)){
			Showmsg('illegal_email');
		}
		if(!ereg("^[0-9]{0,}$",$userdb['oicq'])){
			Showmsg('illegal_OICQ');
		}
		if(!ereg("^[0-9]{0,}$",$userdb['icq'])){
			Showmsg('illegal_OICQ'); 
		}
		if($userdb['kf'] && !ereg("^[0-9a-zA-Z\-]{5,22}$",$userdb['kf'])){
			Showmsg('illegal_53kf');
		}
		if(strlen($userdb['signature'])>$gp_signnum && $gp_signnum!=0){
			Showmsg('sign_limit');
		}
		if(strlen($userdb['introduce'])>500){
			Showmsg('introduce_limit'); 
		}
		if(@include_once(D_P."data/bbscache/wordsfb.php")){
			if($wordsfb){
				foreach($wordsfb as $key => $value){
					$banword = (string) stripslashes($key);
					if(strpos($userdb['signature'],$banword) !== false || strpos($userdb['introduce'],$banword) !== false){
						Showmsg('post_wordsfb');
					}
				}
			}
		}
		if(!empty($proyear) || !empty($promonth) || !empty($proday)){
			$userdb['bday']=$proyear."-".$promonth."-".$proday;
		}
		$userdb['site']		= Char_cv($userdb['site']);
		$userdb['introduce']= Char_cv($userdb['introduce']);
		$userdb['signature']= Char_cv($userdb['signature']);	 
		$userdb['location'] = Char_cv($userdb['location']);
		$tpskin = Char_cv($tpskin);
		$date_f = Char_cv($date_f);
		$timedf = Char_cv($timedf);
		$lxsign=convert($userdb['signature'],$db_windpic,2);
		if($lxsign==$userdb['signature']){
			$userdb['signchange']=1;
		} else{
			$userdb['signchange']=2;
		}
		if($gp_allowhonor) {
			$prohonor=Char_cv($prohonor);
		} else{
			$prohonor=$userdb['honor'];
		}
		$prohonor=substrs($prohonor,30);
		$iconarray=explode('|',$userdb['icon']);

		if($iconarray[1] && ereg("^[0-9]{1,9}",$iconarray[1])){
			$haveupload=1;
		} else{
			$haveupload=0;
		}

		unset($source);
		$upload=$_FILES['upload'];
		if(is_array($upload)){
			$upload_name=$upload['name'];
			$upload_size=$upload['size'];
			$upload=$upload['tmp_name'];
		}
		if($upload && $upload!='none'){
			$attach_ext = strtolower(substr(strrchr($upload_name,'.'),1));
			if(!if_uploaded_file($upload)){
				Showmsg('pro_loadimg_error');
			} elseif(!in_array($attach_ext, array('gif','jpg','bmp'))) {
				Showmsg('illegal_loadimg');
			}
			if($haveupload==1){
				Showmsg('pro_loadimg_fail');
			}
			!$db_upload && Showmsg('pro_loadimg_close');
			$gp_upload==0 && Showmsg('pro_loadimg_right');
			if($upload_size>$db_imgsize){
				Showmsg('pro_loadimg_limit');
			}
			$fileuplodeurl = 'upload/'.(int)$winduid.'.'.$attach_ext;

			$source = $db_ifftp ? $db_ftpweb."/".$fileuplodeurl : $attachdir.'/'.$fileuplodeurl;
			$proownportait[0]=$winduid.'.'.$attach_ext;
			if($db_ifftp){
				$db_attachdir=1;$savedir='upload';
				require_once(R_P.'require/ftp.php');
				if(!$ftpsize=$ftp->upload($upload,$fileuplodeurl)){
					Showmsg('pro_loadimg_error');
				}
			}elseif(!postupload($upload,$source)){
				Showmsg('pro_loadimg_error');
			}
			if(function_exists('getimagesize') && !(list($proownportait[1],$proownportait[2])=getimagesize($source))){
				$db_ifftp ? $ftp->delete($fileuplodeurl) : P_unlink($source);
				Showmsg('pro_loadimg_error');
			}
			if($proownportait[1]>$db_imgwidth || $proownportait[2]>$db_imglen){
				$db_ifftp ? $ftp->delete($fileuplodeurl) : P_unlink($source);
				Showmsg('pro_loadimg_sizelimit');
			}
			$proownportait[1]=$proownportait[2]='';
			if($db_ifftp){
				$ftp->close();unset($ftp);
			}
		}elseif($gp_allowportait && $proownportait[0]){			
			if($haveupload==1){
				Showmsg('pro_custom_fail');
			}
			$proownportait[0]=Char_cv($proownportait[0]);
			if(!ereg("^http",$proownportait[0]) || strrpos($proownportait[0],'|')!==false){
				Showmsg('illegal_customimg');
			}
			if(!ereg("^[0-9]{2,3}$",$proownportait[1]) || !ereg("^[0-9]{2,3}$",$proownportait[2]) || $proownportait[1]>185 || $proownportait[2]>200){
				Showmsg('illegal_customimg');
			}
		}elseif($haveupload==1){
			$proownportait[0]=$iconarray[1];
			$proownportait[1]=$proownportait[2]='';
		}else{
			$proownportait[0]=$proownportait[1]=$proownportait[2]='';
		}
		if($proicon && (strlen($proicon)>20 || !ereg("^[0-9A-Za-z]{1,}\.[A-Za-z]{2,5}$",$proicon))){
			Showmsg('undefined_action');
		}
		$userdb['icon']=str_replace('|','',Char_cv($proicon)).'|'.$proownportait[0].'|'.(int)$proownportait[1].'|'.(int)$proownportait[2];
		strlen($userdb['icon'])>100 && Showmsg('illegal_customimg');

		if(GetCookie('skinco') && $tpskin!=GetCookie('skinco')){
			Cookie('skinco','',0);
		}
		$payemail=(int)$pay."\t$payemail";
		$showsign = $showsign ? 1 : 0;
		$upsql = '';
		if($userdb['showsign']==1 && $showsign==0){
			$upsql = "starttime='0'";
		} elseif($userdb['showsign']==0 && $showsign==1){
			if($userdb['currency'] < $db_signmoney){
				Showmsg('noenough_currency');
			}
			$upsql = "starttime='$tdtime',currency=currency-'$db_signmoney'";
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
		$editor = $editor ? 1 : 0;
		if($editor != $userdb['editor']){
			$upsql .= $upsql ? ",editor='$editor'" : "editor='$editor'";
		}
		$upsql && $db->update("UPDATE pw_memberdata SET $upsql WHERE uid='$winduid'");

		$db->update("UPDATE pw_members SET password ='$userdb[password]',email='$userdb[email]' $groups,honor='$prohonor',publicmail='$userdb[publicmail]',icon='$userdb[icon]',gender='$userdb[gender]',signature='$userdb[signature]',introduce='$userdb[introduce]',oicq='$userdb[oicq]',icq='$userdb[icq]',kf='$userdb[kf]',yahoo='$userdb[yahoo]',msn='$userdb[msn]',site='$userdb[site]',location='$userdb[location]',bday='$userdb[bday]',style='$tpskin',datefm='$date_f',timedf='$timedf',t_num='$t_num',p_num='$p_num',receivemail='$userdb[receivemail]',signchange='$userdb[signchange]',showsign='$showsign',payemail='$payemail' WHERE uid='$winduid'");

		if($customfield){
			$fieldadd='';
			foreach($customfield as $key=>$val){
				$field="field_".(int)$val['id'];
				if($val['required'] && !$userdb[$field] && !$$field){
					Showmsg('field_empty');
				}
				if($$field && $userdb[$field] != $$field){
					if($val['maxlen'] && strlen($$field) > $val['maxlen']){
						Showmsg('field_lenlimit');
					}
					$$field = Char_cv($$field);
					$fieldadd .= $fieldadd ? ",$field='{$$field}'" : "$field='{$$field}'";
				}
			}
			if($fieldadd){
				$db->pw_update(
					"SELECT uid FROM pw_memberinfo WHERE uid='$winduid'",
					"UPDATE pw_memberinfo SET $fieldadd WHERE uid='$winduid'",
					"INSERT INTO pw_memberinfo SET uid='$winduid',$fieldadd"
				);
			}
		}
		refreshto("profile.php?action=show&uid=$winddb[uid]",'operate_success');
	}
}elseif($action=='friend'){
	if(!$job){
		$frienddb = array();
		$query = $db->query("SELECT f.*,m.username,md.thisvisit FROM pw_friends f LEFT JOIN pw_members m ON m.uid=f.friendid LEFT JOIN pw_memberdata md ON md.uid=f.friendid WHERE f.uid='$winduid' ORDER BY f.joindate DESC");
		while ($rt = $db->fetch_array($query)){
			$rt['joindate'] = get_date($rt['joindate']);
			$frienddb[]=$rt;
		}
		require_once(PrintEot('profile'));footer();
	}elseif($_POST['job']=='submit'){
		if($pwuser){
			$rt=$db->get_one("SELECT uid FROM pw_members WHERE username='$pwuser'");
			if(!$rt){
				$errorname = $pwuser;
				Showmsg('user_not_exists');
			}
			$rs = $db->get_one("SELECT uid FROM pw_friends WHERE uid='$winduid' AND friendid='$rt[uid]'");
			if($rs){
				Showmsg('friend_already_exists');
			}
			$descrip=Char_cv($descrip);
			$db->update("INSERT INTO pw_friends(uid,friendid,descrip,joindate) VALUES('$winduid','$rt[uid]','$descrip','$timestamp')");
		}
		foreach($selid as $key=>$val){
			if(is_numeric($val)){
				$db->update("DELETE FROM pw_friends WHERE uid='$winduid' AND friendid='$val'");
			}
		}
		if($frienddb){
			foreach($frienddb as $key=>$val){
				if(is_numeric($key)){
					$db->update("UPDATE pw_friends SET descrip='".Char_cv($val)."' WHERE uid='$winduid' AND friendid='$key'");
				}
			}
		}
		refreshto('profile.php?action=friend','friend_update_success');
	}elseif($job=='add'){
		$touid = (int)$touid;
		$rt=$db->get_one("SELECT uid,username FROM pw_members WHERE uid='$touid'");
		if(!$rt){
			$errorname = $touid;
			Showmsg('user_not_exists');
		}
		$rs = $db->get_one("SELECT uid FROM pw_friends WHERE uid='$winduid' AND friendid='$rt[uid]'");
		if($rs){
			Showmsg('friend_already_exists');
		}
		$db->update("INSERT INTO pw_friends(uid,friendid,joindate) VALUES('$winduid','$rt[uid]','$timestamp')");
		refreshto('profile.php?action=friend','friend_update_success');
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
	$_G['uploadmaxsize'] = $_G['uploadmaxsize'] ? ceil($_G['uploadmaxsize']/1024) : ceil($db_uploadmaxsize/1024);
	!$_G['uploadtype'] && !$_G['uploadtype'] = $db_uploadfiletype;

	require_once(PrintEot('profile'));footer();
}elseif($action=='forumright'){
	require_once(R_P.'require/forum.php');
	include_once(D_P."data/bbscache/creditdb.php");

	list($db_moneyname,,$db_rvrcname,,,)=explode("\t",$db_credits);
	$rt=$db->get_one("SELECT f.name,f.type,f.f_type,f.password,f.allowvisit,f.allowpost,f.allowrp,f.allowdownload,f.allowupload,f.cms,fe.creditset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid'");
	if(!$rt || $rt['type']=='category'){
		require_once(R_P.'require/url_error.php');
	}
	wind_forumcheck($rt);
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
				if($k != 'Reply' && $k != 'Deleterp'){
					$creditset[$key][$k] /= 10;
				}
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
	if($type && $logtype[$type]){
		$sqladd .= " AND type='$type'";
	}
	$type_sel[$type]='selected';
	$db_perpage = 30;

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_adminlog $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"profile.php?action=log&type=$type&");
	$logdb=array();
	$query = $db->query("SELECT * FROM pw_adminlog $sqladd ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']   = get_date($rt['timestamp']);
		$rt['descrip']=str_replace("\n","<br>",$rt['descrip']);
		$rt['descrip']=descriplog($rt['descrip']);
		$logdb[] = $rt;
	}
	require_once(PrintEot('profile'));footer();
}elseif($action=='buy'){
	require_once(R_P.'require/pw_func.php');
	if(!$job){
		$specialdb=array();
		$query=$db->query("SELECT gid,grouptitle,sright FROM pw_usergroups WHERE gptype='special'");
		while($rt=$db->fetch_array($query)){
			$rt['sright']=P_unserialize($rt['sright']);
			if($rt['sright']['allowbuy']){
				$rt['enddate'] = '-';
				$specialdb[$rt['gid']]=$rt;
			}
		}
		$query=$db->query("SELECT gid,startdate,days FROM pw_extragroups WHERE uid='$winduid'");
		while($rt=$db->fetch_array($query)){
			if(array_key_exists($rt['gid'],$specialdb)){
				$specialdb[$rt['gid']]['days']		= $rt['days'];
				$specialdb[$rt['gid']]['startdate']	= $rt['startdate'];
				$specialdb[$rt['gid']]['enddate']	= get_date($rt['startdate'] + $rt['days']*86400,'Y-m-d');
			}
		}
		require_once(PrintEot('profile'));footer();
	}elseif($job=='buy'){
		$rt=$db->get_one("SELECT uid,startdate,days FROM pw_extragroups WHERE uid='$winduid' AND gid='$gid'");
		if($rt && $timestamp <= $rt['startdate'] + $rt['days']*86400){
			$enddate = get_date($rt['startdate'] + $rt['days']*86400,'Y-m-d');
			Showmsg('specialgroup_exists');
		}
		$rt=$db->get_one("SELECT gid,grouptitle,sright FROM pw_usergroups WHERE gptype='special' AND gid='$gid'");
		if(!$rt){
			Showmsg('specialgroup_error');
		}
		$rt['sright']=P_unserialize($rt['sright']);
		if(!$rt['sright']['allowbuy']){
			Showmsg('special_allowbuy');
		}
		if(!$_POST['step']){
			require_once(PrintEot('profile'));footer();
		}else{
			if(!is_numeric($days) || $days<=0){
				Showmsg('illegal_nums');
			}
			
			$mb = $db->get_one("SELECT password,groups FROM pw_members WHERE uid='$winduid'");
			if(md5($pwpwd) != $mb['password']){
				Showmsg('password_error');
			}
			if($gid==$groupid || strpos($mb['groups'],",$gid,")!==false){
				Showmsg('specialgroup_noneed');
			}
			
			if($days < $rt['sright']['selllimit']){
				Showmsg('special_selllimit');
			}
			$needcurrency = $days*$rt['sright']['sellprice'];
			if($winddb['currency'] < $needcurrency){
				Showmsg('noenough_currency');
			}
			if($options==1){
				if($winddb['groupid']=='-1'){
					$db->update("UPDATE pw_members SET groupid='$gid' WHERE uid='$winduid'");
				}else{
					$groups = $mb['groups'] ? $mb['groups'].$winddb['groupid'].',' : ",$winddb[groupid],";
					$db->update("UPDATE pw_members SET groupid='$gid',groups='$groups' WHERE uid='$winduid'");
				}
			}else{
				$groups = $mb['groups'] ? $mb['groups'].$gid.',' : ",$gid,";
				$db->update("UPDATE pw_members SET groups='$groups' WHERE uid='$winduid'");
			}
			$db->update("UPDATE pw_memberdata SET currency=currency-'$needcurrency' WHERE uid='$winduid'");
			$db->pw_update(
				"SELECT uid FROM pw_extragroups WHERE uid='$winduid' AND gid='$gid'",
				"UPDATE pw_extragroups SET startdate='$timestamp',days='$days' WHERE uid='$winduid'AND gid='$gid'",
				"INSERT INTO pw_extragroups SET uid='$winduid',gid='$gid',startdate='$timestamp',days='$days'"
			);
			require_once(R_P.'require/tool.php');
			$logdata=array(
				'type'		=>	'group',
				'nums'		=>	0,
				'money'		=>	0,
				'descrip'	=>	'group_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'currency'	=>	$needcurrency,
				'gptitle'	=>	$rt['grouptitle'],
				'days'		=>	$days
			);
			writetoollog($logdata);
			refreshto("profile.php",'group_buy_success');
		}
	}
}elseif($action=='favor'){
	require_once(R_P."require/favor.php");
}

function descriplog($message){
	$message = str_replace("[b]","<b>",$message);
	$message = str_replace("[/b]","</b>",$message);
	if(strpos($message,'[/URL]')!==false || strpos($message,'[/url]')!==false){
		$message=preg_replace("/\[url=(https?)([^\[]+?)\](.+?)\[\/url\]/is","<a href=\"\\1\\2\" target=\"_blank\">\\3</a>",$message);
	}
	return $message;
}
?>