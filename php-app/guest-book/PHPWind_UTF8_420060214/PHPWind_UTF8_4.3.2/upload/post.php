<?php
require_once('global.php');
require_once(R_P.'require/forum.php');
include_once(D_P.'data/bbscache/level.php');
/**
* 版块缓冲文件
*/
include_once(D_P.'data/bbscache/forum_cache.php');
$foruminfo = $db->get_one("SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid' AND type<>'category'");
if(!is_numeric($fid) || !$foruminfo){
	require_once(R_P.'require/url_error.php');
}
$forumset  = unserialize($foruminfo['forumset']);
$creditset = $foruminfo['creditset'];
wind_forumcheck($foruminfo);

if($db_recycle && $fid==$db_recycle){
	showmsg('post_recycle');
}
if($tid && !is_numeric($tid)){
	Showmsg('illegal_tid');
}
list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
/*
* 获取管理权限
*/
if($windid==$manager || $groupid==3 || $groupid==4|| ($foruminfo['forumadmin'] && strpos($foruminfo['forumadmin'],','.$windid.',')!==false)){
	$admincheck=1;
} else{
	$admincheck=0;
}
!$windid && $admincheck=0;
if ($windid != $manager && $groupid != 3 && !$foruminfo['allowvisit'] && (!$foruminfo['forumadmin'] || strpos($foruminfo['forumadmin'],','.$windid.',')===false)){
	forum_creditcheck();
}
$secondurl="thread.php?fid=$fid";
$top_post=0;
!$action && $action="new";
/**
* 调查版块只允许发起投票
*/
if($foruminfo['f_type']=='vote' && $action=='new' && !$admincheck){
	Showmsg('post_vote_only');
}

list($db_openpost,$db_poststart,$db_postend)=explode("\t",$db_openpost);
if($db_openpost==1 && $groupid != 3 && $groupid != 4){
	if($db_poststart < $db_postend && ($t['hours'] < $db_poststart || $t['hours'] >= $db_postend)){
		Showmsg('post_openpost');
	}elseif($db_poststart > $db_postend && ($t['hours'] < $db_poststart && $t['hours'] >= $db_postend)){
		Showmsg('post_openpost');
	}
}

/**
* 禁止受限制用户发言
*/
if($groupid=='6'){
	$bandb=$db->get_one("SELECT * FROM pw_banuser WHERE uid='$winduid'");
	if(!$bandb){
		$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$winduid'");
	} elseif($bandb['type']==1 && $timestamp-$bandb['startdate']>$bandb['days']*86400){
		$db->update("DELETE FROM pw_banuser WHERE uid='$winduid'");
		$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$winduid'");
	} else{
		if($bandb['type']==1){
			$s_date=get_date($bandb['startdate']);
			$e_date=$bandb['startdate']+$bandb['days']*86400;
			$e_date=get_date($e_date);
			Showmsg('ban_info1');
		}else{
			if($bandb['type']==3){
				Cookie('force',$winduid);
				Showmsg('ban_info3');
			} else{
				Showmsg('ban_info2');
			}
		}
	}
}
if(GetCookie('force') && $winduid != GetCookie('force')){
	$force=GetCookie('force');
	$bandb=$db->get_one("SELECT type FROM pw_banuser WHERE uid='$force'");
	if($bandb['type']==3){
		Showmsg('ban_info3');
	} else{
		Cookie('force','',0);
	}
}
/**
* 需要验证用户只有通过管理员验证后才能发帖
*/
if($groupid=='7'){
	Showmsg('post_check');
}
/*
* 新注册会员发帖时间限制
*/
if($db_postallowtime && $timestamp-$winddb['regdate']<$db_postallowtime*3600){
	Showmsg('post_newrg_limit');
}
$userlastptime = $groupid != 'guest' ?  $winddb['lastpost'] : GetCookie('userlastptime');
/**
* 灌水预防
*/
$tdtime >= $winddb['lastpost'] && $winddb['todaypost'] = 0;
if ($_G['postlimit'] && $winddb['todaypost'] >= $_G['postlimit']){
	Showmsg('post_gp_limit');
}

if ($action!="modify" && !$SYSTEM['postpers'] && $gp_postpertime && $timestamp-$userlastptime<=$gp_postpertime){
	Showmsg('post_limit');
}
list(,,$postgd)=explode("\t",$db_gdcheck);
if($gp_wysiwyg && $winddb[editor]){
	$editor='wysiwyg_editor';
	$js_path=file_exists(D_P."data/{$stylepath}_c_editor.js") ? "data/{$stylepath}_c_editor.js" : "data/wind_c_editor.js";
}else{
	$editor='editor';
	$js_path=file_exists(D_P."data/{$stylepath}_editor.js") ? "data/{$stylepath}_editor.js" : "data/wind_editor.js";
}
if (!$_POST['step']){
	$atc_content=$atc_title='';
	include_once(D_P.'data/bbscache/postcache.php');
	if(!is_numeric($num_a) || !$num_a){
		$num_a=1;
	}
	!is_numeric($db_attachnum) && $db_attachnum=1;
	$_G['uploadmaxsize'] && $db_uploadmaxsize=$_G['uploadmaxsize'];
	$db_uploadmaxsize=ceil($db_uploadmaxsize/1024);
	$htmlpost=($foruminfo['allowhide'] && $gp_allowhidden ? '':"disabled");
	$htmlhide=($foruminfo['allowencode'] && $gp_allowencode ? '':"disabled");
	$htmlsell=($foruminfo['allowsell'] && $gp_allowsell ? '':"disabled");
	if($groupid=='guest'){
		$userrvrc=0;
	}
	$db_replysendmail!=1 && $hideemail="disabled";
	/**
	* phpwind code
	*/
	if($db_signwindcode){
		$windcode="<br><a href=\"faq.php?faqjob=1#5\"> Wind Code Open</a>";
		$db_windpost['pic'] ? $windcode.="<br> [img] - Open":$windcode.="<br> [img] - Close";
		$db_windpost['flash'] ? $windcode.="<br> [flash] - Open":$windcode.="<br> [flash] - Close";
	} else{
		$windcode="<br><a href=\"faq.php?faqjob=1#5\">Wind Code</a>Close"; 
	}
	/**
	* 标题表情
	*/
	$icondb=array(
			'1'=>'1.gif',	'2'=>'2.gif',
			'3'=>'3.gif',	'4'=>'4.gif',
			'5'=>'5.gif',	'6'=>'6.gif',
			'7'=>'7.gif',	'8'=>'8.gif',
			'9'=>'9.gif',	'10'=>'10.gif',
			'11'=>'11.gif',	'12'=>'12.gif',
			'13'=>'13.gif',	'14'=>'14.gif',
		);
} elseif($_POST['step']){
	if(!$windid)$windid='guest';
	$postgd && GdConfirm($gdcode);
	require_once(R_P.'require/bbscode.php');
	require_once(R_P.'require/postfunc.php');
	if(@include(D_P."data/bbscache/wordsfb.php")){
		foreach($wordsfb as $key => $value){
			if(strpos($atc_title,(string)$key) !== false){
				$banword = $key;
				Showmsg('post_wordsfb');
			}
			if(strpos($atc_content,(string)$key) !== false){
				$banword = $key;
				Showmsg('post_wordsfb');
			}
		}
	}
	list($lastip,$ipfrom)=explode("\t",str_replace('<','&lt;',$ipfrom));
	if($lastip != md5($onlineip)){
		$ipfrom=cvipfrom($onlineip);
		$ipfrom = str_replace("\n","",$ipfrom);
		Cookie('ipfrom',md5($onlineip)."\t".$ipfrom);
	}
	$atc_usesign=!$_POST['atc_usesign'] ? 0 : 1;
	if(($gp_htmlcode || ($gp_wysiwyg && $c_editor)) && $_POST['atc_html']){
		$atc_usesign+=2;
	}

	/**
	* 得到父版块id
	*/
	$fatherid=$foruminfo['type']=='sub' ? $foruminfo['fup']:'';
}

if ($action=="new"||$action=="vote"){
	require_once(R_P.'require/postnew.php');
} elseif($action=="reply" || $action=="quote"){
	require_once(R_P.'require/postreply.php');
} elseif($action=="modify"){
	require_once(R_P.'require/postmodify.php');
} else{
	Showmsg('undefined_action');
}
?>
