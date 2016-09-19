<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=settings&type=$type";

@include_once(D_P.'data/bbscache/creditdb.php');
if ($action!='unsubmit'){
	!$type && $type='bbsset';
	if($type=='bbsset' || $type=='all'){
		$db_whybbsclose = str_replace('<br />',"\n",$db_whybbsclose);
		list($db_openpost,$db_poststart,$db_postend)=explode("\t",$db_openpost);
		list($db_opensch,$db_schstart,$db_schend)=explode("\t",$db_opensch);
		$db_forumdir=(int)$db_forumdir;
		${'forumdir_'.$db_forumdir}='checked';

		ifcheck($db_bbsifopen,'bbsifopen');
		ifcheck($db_openpost,'openpost');
		ifcheck($db_opensch,'opensch');
		ifcheck($db_regpopup,'regpopup');
		ifcheck($db_debug,'debug');
	}
	if($type=='setgd' || $type=='all'){
		list($reggd,$logingd,$postgd,$msggd,$othergd,$admingd)=explode("\t",$db_gdcheck);
		ifcheck($reggd,'reggd');
		ifcheck($logingd,'logingd');
		ifcheck($msggd,'msggd');
		ifcheck($othergd,'othergd');
		ifcheck($admingd,'admingd');
	}
	if($type=='basicset' || $type=='all'){
		(!$db_recycle || !is_numeric($db_recycle)) && $db_recycle=0;
		$db_hour && $hour_sel[$db_hour]='selected';
	}
	if($type=='pathset' || $type=='all'){
		if (file_exists($imgdir) && !is_writeable($imgdir)){
			$imgdisabled='disabled';
		}
		if (file_exists($attachdir) && !is_writeable($attachdir)){
			$attdisabled='disabled';
		}
		ifcheck($db_autochange,'autochange');
	}
	if($type=='coreset' || $type=='all'){
		require_once(R_P."require/forum.php");
		$choseskin=getstyles($db_defaultstyle);

		$db_timedf < 0 ? ${'zone_0'.str_replace('.','_',abs($db_timedf))}='selected' : ${'zone_'.str_replace('.','_',$db_timedf)}='selected';
		${'charset_'.str_replace('-','',$db_charset)}='selected';

		if($db_datefm){
			if(strpos($db_datefm,'h:i A')){
				$db_datefm=str_replace(' h:i A','',$db_datefm);
				$check_12='checked';
			} else{
				$db_datefm=str_replace(' H:i','',$db_datefm);
				$check_24='checked';
			}
			$db_datefm = str_replace('m', 'mm', $db_datefm);
			$db_datefm = str_replace('n', 'm', $db_datefm);
			$db_datefm = str_replace('d', 'dd', $db_datefm);
			$db_datefm = str_replace('j', 'd', $db_datefm);
			$db_datefm = str_replace('y', 'yy', $db_datefm);
			$db_datefm = str_replace('Y', 'yyyy', $db_datefm);
		} else{
			$db_datefm='yyyy-mm-dd';
			$check_24='checked';
		}
		list($db_upload,$db_imglen,$db_imgwidth,$db_imgsize)=explode("\t",$db_upload);
		$db_imgsize=ceil($db_imgsize/1024);
		$db_onlinetime/=60;

		ifcheck($db_lp,'lp');
		ifcheck($db_obstart,'obstart');
		ifcheck($db_upload,'upload');
		ifcheck($db_msgsound,'msgsound');
		ifcheck($db_ifonlinetime,'ifonlinetime');
		ifcheck($db_ifjump,'ifjump');
		ifcheck($db_footertime,'footertime');
		ifcheck($db_shield,'shield');
		ifcheck($db_tcheck,'tcheck');
		ifcheck($db_forcecharset,'forcecharset');
		ifcheck($db_adminset,'adminset');
		${'columns_'.$db_columns}='checked';
	}
	if($type=='creditset' || $type=='all'){
		list($credits_1,$credits_2,$credits_3,$credits_4,$credits_5,$credits_6)=explode("\t",$db_credits);
	}
	if($type=='regset' || $type=='all'){
		include(D_P.'data/bbscache/dbreg.php');
		$rg_whyregclose	= str_replace('<br />',"\n",$rg_whyregclose);
		$rg_welcomemsg	= str_replace('<br />',"\n",$rg_welcomemsg);
		$rg_rgpermit	= str_replace('<br />',"\n",$rg_rgpermit);
		$rg_regrvrc	   /= 10;
		$db_postallowtime && $regcheck[$db_postallowtime]='selected';
		list($rg_question,$rg_answer,$rg_variable) = explode("\t",$rg_unreg);
		$rg_question = Quot_cv($rg_question);
		$rg_answer   = Quot_cv($rg_answer);

		ifcheck($rg_allowregister,'allowregister');
		ifcheck($rg_reg,'reg');
		ifcheck($rg_regdetail,'regdetail');
		ifcheck($rg_emailcheck,'emailcheck');
		ifcheck($rg_regsendmsg,'regsendmsg');
		ifcheck($rg_ifcheck,'ifcheck');
		ifcheck($rg_regsendemail,'regsendemail');
		ifcheck($rg_rglower,'rglower');
	}
	if($type=='windcode' || $type=='all'){
		ifcheck($db_windpic['pic'],'windpic_pic');
		ifcheck($db_windpic['flash'],'windpic_flash');
		ifcheck($db_windpost['pic'],'windpost_pic');
		ifcheck($db_windpost['flash'],'windpost_flash');
		ifcheck($db_windpost['mpeg'],'windpost_mpeg');
		ifcheck($db_windpost['iframe'],'windpost_iframe');
		ifcheck($db_windreply['pic'],'windreply_pic');
		ifcheck($db_windreply['flash'],'windreply_flash');
		ifcheck($db_signwindcode,'signwindcode');
	}
	if($type=='attachset' || $type=='all'){
		!$db_attachnum && $db_attachnum=4;
		!$db_attachdir && $db_attachdir=0;
		$db_uploadmaxsize=ceil($db_uploadmaxsize/1024);
		$attachdir_ck[$db_attachdir]='checked';
	
		ifcheck($db_allowupload,'allowupload');
		ifcheck($db_replysendmail,'replysendmail');
		ifcheck($db_autoimg,'autoimg');
	}
	if($type=='atcset' || $type=='all'){
		$credit=unserialize($db_creditset);
		foreach($credit as $key => $value){
			foreach($value as $k => $val){
				if($key == 'rvrc' && $k != 'Reply' && $k != 'Deleterp'){
					$val /= 10;
				}
				$credit[$key][$k] = (int)$val;
			}
		}
	}
	if($type=='indexset' || $type=='all'){
		$gporder = explode(",",$db_showgroup);
		$usergroup = "<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
		foreach($ltitle as $key => $value){
			if($key==1||$key==2)continue;
			$num++;
			$htm_tr = $num % 2 == 0 ? '</tr><tr>' : '';
			if(in_array($key,$gporder)){
				$g_ck    = 'checked';
				$g_order = array_search($key,$gporder);
			}else{
				$g_ck = $g_order = '';
			}
			$usergroup .= "<td><input type='checkbox' name='gpshow[$key]' value='$key' $g_ck><input type='text' name='gporder[$key]' value='$g_order' size='1'> $value</td>$htm_tr";
		}
		$usergroup .= "</tr></table>";

		ifcheck($db_indexlink,'indexlink');
		ifcheck($db_indexmqshare,'indexmqshare');
		ifcheck($db_indexshowbirth,'indexshowbirth');
		ifcheck($db_indexonline,'indexonline');
		ifcheck($db_adminshow,'adminshow');

		ifcheck($db_showguest,'showguest');
		ifcheck($db_today,'today');
		ifcheck($db_todaypost,'todaypost');
	
		$db_indexfmlogo==1 ? $indexfmlogo_1="checked" : ($db_indexfmlogo==2 ? $indexfmlogo_2="checked" :  $indexfmlogo_0="checked");//自定义首页各版块的图片logo
	}
	if($type=='viewset' || $type=='all'){
		$db_hithour && $hithour_sel[$db_hithour]='selected';
		ifcheck($db_topped,'topped');
		ifcheck($db_threadonline,'threadonline');
		ifcheck($db_showonline,'showonline');
		ifcheck($db_showcolony,'showcolony');
		ifcheck($db_threademotion,'threademotion');
		ifcheck($db_ipfrom,'ipfrom');
		ifcheck($db_threadshowpost,'threadshowpost');
		($db_menu == 1 || $db_menu == 3) && $menu_p = 'checked';
		$db_menu>1 && $menu_f = 'checked';
	}
	if($type=='watermark' || $type=='all'){
		${'waterpos_ck_'.$db_waterpos}='checked';
		ifcheck($db_watermark,'watermark');
		${'ifgif_'.$db_ifgif}='checked';
	}
	if($type=='buysign' || $type=='all'){
		$signgroup = "<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
		foreach($ltitle as $key => $value){
			if($key==1||$key==2)continue;
			$num++;
			$htm_tr = $num % 3 == 0 ? '</tr><tr>' : '';
			if(strpos($db_signgroup,",$key,")!==false){
				$s_checked = 'checked';
			} else {
				$s_checked = '';
			}
			$signgroup .= "<td><input type='checkbox' name='signgroup[]' value='$key' $s_checked>$value</td>$htm_tr";
		}
		$signgroup .= "</tr></table>";	
	}
	if($type=='ajax' || $type=='all'){
		ifcheck($db_ajaxsubject,'ajaxsubject');
		ifcheck($db_ajaxcontent,'ajaxcontent');
	}
	if($type=='wap' || $type=='all'){
		ifcheck($db_wapifopen,'wapifopen');
	}
	if($type=='js' || $type=='all'){
		ifcheck($db_jsifopen,'jsifopen');
	}
	if($type=='mail' || $type=='all'){
		include_once(D_P.'data/bbscache/mail_config.php');
		${'mailmethod_'.$ml_mailmethod}='checked';
		ifcheck($ml_mailifopen,'mailifopen');
		ifcheck($ml_smtpauth,'smtpauth');
	}
	if($type=='safe' || $type=='all'){
		${'cc_'.$db_cc} = "checked";
		ifcheck($db_ipcheck,'ipcheck');
	}
	if($type=='ftp' || $type=='all'){
		@include_once(D_P.'data/bbscache/ftp_config.php');
		ifcheck($db_ifftp,'ifftp');
	}
	if($type=='other' || $type=='all'){
		ifcheck($db_enterreason,'enterreason');
	}
	include PrintEot('setting');exit;
}elseif ($_POST['action']=="unsubmit"){
	if(!is_writeable(D_P.'data/bbscache/config.php') && !chmod(D_P.'data/bbscache/config.php',0777)){
		adminmsg('config_777');
	}
	if($type=='bbsset' || $type=='all'){
		$config['whybbsclose'] = ieconvert($config['whybbsclose']);
		!is_numeric($config['onlinelmt']) && $config['onlinelmt']=5000;
		$config['openpost']=$config['openpost']."\t".$config['poststart']."\t".$config['postend'];
		unset($config['poststart'],$config['postend']);
		$config['opensch']=$schcontrol['opensch']."\t".$schcontrol['schstart']."\t".$schcontrol['schend'];
	}
	if($type=='setgd' || $type=='all'){
		(!is_numeric($gdcheck['post']) || $gdcheck['post'] < 0) && $gdcheck['post'] = 0;
		$config['gdcheck'] = implode("\t",$gdcheck);
	}
	if($type=='basicset' || $type=='all'){
		if(!$config['recycle'] || !is_numeric($config['recycle'])){
			$config['recycle']='0';
		} else{
			$forum=$db->get_one("SELECT type FROM pw_forums WHERE fid='$config[recycle]'");
			if(!$forum){
				adminmsg('setting_recycle_error');
			} elseif($forum['type']=='category'){
				adminmsg('setting_recycle_type');
			}
		}
	}
	if($type=='pathset' || $type=='all'){
		if ($config['http']!='N' && !ereg("^http",$config['http'])){
			adminmsg('setting_http');
		}
		if($config['autochange']){
			if(!is_writeable($imgdir) || !is_writeable($attachdir)){
				$config['autochange']=0;
			}
		}
		$setting = array();
		if(!is_dir($set['picpath']) && $picpath!=$set['picpath']){
			@rename($picpath,$set['picpath']) ? $setting['pic']=$set['picpath'] : adminmsg('setting_777');
		}
		if(!is_dir($set['attachpath']) && $attachname!=$set['attachpath']){
			@rename($attachname,$set['attachpath']) ? $setting['att']=$set['attachpath'] : adminmsg('setting_777');
		}
		if($setting){
			require_once(R_P.'require/updateset.php');
			write_config($setting);
		}
	}
	if($type=='coreset' || $type=='all'){
		if($config['datefm']){
			if(strpos($config['datefm'],'mm')!==false){
				$config['datefm'] = str_replace('mm','m',$config['datefm']);
			} else{
				$config['datefm'] = str_replace('m','n',$config['datefm']);
			}
			if(strpos($config['datefm'],'dd')!==false){
				$config['datefm'] = str_replace('dd','d',$config['datefm']);
			} else{
				$config['datefm'] = str_replace('d','j',$config['datefm']);
			}
			$config['datefm'] = str_replace('yyyy','Y',$config['datefm']);
			$config['datefm'] = str_replace('yy','y',$config['datefm']);
			$timefm=$time_f=='12' ? ' h:i A' :' H:i';
			$config['datefm'].=$timefm;
		} else{
			$config['datefm']='Y-n-j H:i';
		}
		if (!is_numeric($config['imgsize'])){
			$config['imgsize']=20480;
		} else{
			$config['imgsize']*=1024;
		}
		!is_numeric($config['imglen'])   && $config['imglen']=200;
		!is_numeric($config['imgwidth']) && $config['imgwidth']=180;
		$config['upload']=$config['upload']."\t".$config['imglen']."\t".$config['imgwidth']."\t".$config['imgsize'];
		unset($config['imglen'],$config['imgwidth'],$config['imgsize']);
		$config['onlinetime'] *= 60;
	}
	if($type=='creditset' || $type=='all'){
		$config['credits']=implode("\t",$credits);
	}
	if($type=='regset' || $type=='all'){
		include_once(D_P."data/bbscache/dbreg.php");
		$reg['welcomemsg']	= ieconvert($reg['welcomemsg']);
		$reg['rgpermit']	= ieconvert($reg['rgpermit']);
		!is_numeric($reg['regrvrc'])	&& $reg['regrvrc']=1;
		!is_numeric($reg['regmoney'])	&& $reg['regmoney']=0;
		!$reg['regminname'] && $reg['regminname']=3;
		!$reg['regmaxname'] && $reg['regmaxname']=12;
		$reg['timestart']=(int) $reg['timestart'];
		$reg['timeend']  =(int) $reg['timeend'];
		if($reg['timeend']-$reg['timestart'] > 150 || $reg['timeend']-$reg['timestart'] < 0){
			adminmsg('reg_timelimit');
		}
		if ($reg['regmaxname']>15){
			adminmsg('illegal_username');
		}
		$reg['regrvrc']*=10;
		if($unreg['question'] && $unreg['answer']){
			!ereg("^[a-z]{3,8}$",$unreg['variable']) && adminmsg('reg_variable');
			$reg['unreg'] = $unreg['question']."\t".$unreg['answer']."\t".$unreg['variable'];
		}
		foreach($reg as $key=>$value){
			if(${'rg_'.$key}!=$value){
				$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='rg_$key'");
				if($rt['db_name']=="rg_$key"){
					$db->update("UPDATE pw_config SET db_value='$value' WHERE db_name='rg_$key'");
				}else{
					$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('rg_$key','$value')");
				}
			}
		}
	}
	if($type=='attachset' || $type=='all'){
		if (!is_numeric($config['uploadmaxsize'])){
			$config['uploadmaxsize']=0;
		} else{
			$config['uploadmaxsize']*=1024;
		}
		!$config['postmax'] && $config['postmax'] = 50000;
	}
	if($type=='atcset' || $type=='all'){
		foreach($creditdb as $key => $value){
			foreach($value as $k => $val){
				if($key == 'rvrc' && $k != 'Reply' && $k != 'Deleterp'){
					$val *= 10;
				}
				$creditdb[$key][$k] = (int)$val;
			}
		}
		$config['creditset']=$creditdb ? serialize($creditdb) : '';
	}
	if($type=='indexset' || $type=='all'){
		if(is_array($gpshow)){
			$showgroup=array();
			foreach($gpshow as $key=>$value){
				$showgroup[$value]=$gporder[$key];
			}
			asort($showgroup);
			$showgroup=array_keys($showgroup);
			$config['showgroup']=','.implode(',',$showgroup).',';
		} else{
			$config['showgroup']='';
		}
	}
	if($type=='viewset' || $type=='all'){
		!$config['perpage']		&& $config['perpage']		= 25;
		!$config['readperpage'] && $config['readperpage']	= 10;
		if($showcustom){
			$config['showcustom']=",".implode(",",$showcustom).",";
		} else{
			$config['showcustom']='';
		}
		$config['menu']=0;
		$menu['p'] && $config['menu']+=1;
		$menu['f'] && $config['menu']+=2;
	}
	if($type=='watermark' || $type=='all'){
		if($config['watermark'] && (!function_exists('imagecreatefromgif') || !function_exists('imagettfbbox') || !function_exists('imagealphablending'))){
			adminmsg("setting_gd_error");
		}
		$config['waterpct']=(int)$config['waterpct'];
	}
	if($type=='buysign' || $type=='all'){
		if($signgroup){
			$config['signgroup']=",".implode(",",$signgroup).",";
		} else{
			$config['signgroup']='';
		}
	}
	if($type=='mail' || $type=='all'){
		include_once(D_P.'data/bbscache/mail_config.php');
		foreach($mailconfig as $key=>$value){
			if($$key != $value){
				$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='$key'");
				if($rt['db_name']==$key){
					$db->update("UPDATE pw_config SET db_value='$value' WHERE db_name='$key'");
				}else{
					$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('$key','$value')");
				}
			}
		}
		updatecache_ml();
	}
	if($type=='ftp' || $type=='all'){
		include_once(D_P.'data/bbscache/ftp_config.php');
		foreach($ftp as $key=>$value){
			if($$key !=$value){
				$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='$key'");
				if($rt['db_name']==$key){
					$db->update("UPDATE pw_config SET db_value='$value' WHERE db_name='$key'");
				}else{
					$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('$key','$value')");
				}
			}
		}
		updatecache_ftp();
	}
	if(!in_array($type,array('mail'))){
		//get pw_config value
		$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'db\_%' OR db_name LIKE 'rg\_%'");
		while ($rt = $db->fetch_array($query)){
			$rt['db_name']=str_replace("'","\'",$rt['db_name']);
			$configdb[$rt['db_name']]=$rt['db_value'];
		}
		foreach($config as $key=>$value){
			$c_key=${'db_'.$key};
			if(strpos($key,'_')!==false){
				$c_db=explode('_',$key);
				$c_db_array=${'db_'.$c_db[0]};
				$c_key=$c_db_array[$c_db[1]];
				$key=str_replace('_',"[\'",$key)."\']";
			}
			if($c_key!=$value || $configdb["db_$key"]!=$value){
				$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='db_$key'");
				if($rt['db_name']){
					$db->update("UPDATE pw_config SET db_value='$value' WHERE db_name='db_$key'");
				}else{
					$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_$key','$value')");
				}
			}
		}
	}
	$db_union_c=readover(D_P.'data/bbscache/file_lock.txt');
	if($db_union!=$db_union_c){
		$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='db_union'");
		if($rt['db_name']){
			$db->update("UPDATE pw_config SET db_value='$db_union_c' WHERE db_name='db_union'");
		}else{
			$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_union','$db_union_c')");
		}
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>