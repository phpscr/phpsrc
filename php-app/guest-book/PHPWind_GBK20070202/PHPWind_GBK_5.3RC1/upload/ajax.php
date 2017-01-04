<?php
require_once('global.php');
require_once(R_P.'require/forum.php');

$groupid == 'guest' && exit('Forbidden');
$atc = $db->get_one("SELECT fid,authorid,subject,ptable,ifsign FROM pw_threads t LEFT JOIN pw_tmsgs tm USING(tid) WHERE t.tid='$tid'");
$foruminfo = $db->get_one("SELECT f.*,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe ON fe.fid=f.fid WHERE f.fid='$atc[fid]' AND f.type<>'category'");
if(!is_numeric($atc['fid']) || !$foruminfo){
	exit('Forbidden');
}
wind_forumcheck($foruminfo);
$forumset  = unserialize($foruminfo['forumset']);
$db_recycle && $fid==$db_recycle && exit('Forbidden');
$tid && !is_numeric($tid) && exit('Forbidden');
/*
* 获取管理权限
*/
if($groupid==3 || $groupid==4 || admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
	$admincheck=1;
} else{
	$admincheck=0;
}
if($winduid != $atc['authorid'] && $groupid != 3 && $groupid != 4){
	$authordb = $db->get_one("SELECT groupid FROM pw_members WHERE uid='$atc[authorid]'");
	if(($authordb['groupid'] == 3 || $authordb['groupid'] == 4)){
		exit('Forbidden');
	}
}
header("Content-Type: text/html; charset=$db_charset");

if($action == 'fetchtitle'){
	$rt = $db->get_one("SELECT subject FROM pw_threads WHERE tid='$tid'");
	$rt['subject'] = str_replace(array('&lt;','&gt;'),array('<','>'),addslashes($rt['subject']));
	echo"<script language=\"JavaScript1.2\">parent.AJAX_response('fetchcode','$rt[subject]');</script>";exit;
}elseif($action == 'subject'){
	if(!$db_ajaxsubject || !$admincheck){
		exit('Forbidden');
	}
	!$atc_content && exit('Forbidden');
	$atc_content = Char_cv($atc_content);
	$db->update("UPDATE pw_threads SET subject='$atc_content' WHERE tid='$tid'");
	$rt = $db->get_one("SELECT titlefont FROM pw_threads WHERE tid='$tid'");
	if ($rt['titlefont']){
		$detail=explode("~",$rt['titlefont']);
		$detail[0] && $atc_content="<font color=$detail[0]>$atc_content</font>";
		$detail[1] && $atc_content="<b>$atc_content</b>";
		$detail[2] && $atc_content="<i>$atc_content</i>";
		$detail[3] && $atc_content="<u>$atc_content</u>";
	}
	echo"<script language=\"JavaScript1.2\">parent.AJAX_response('showcode','$atc_content');</script>";exit;
}elseif($action == 'content'){
	if(!$db_ajaxcontent || !$admincheck && $atc['authorid'] != $winduid){
		exit('Forbidden');
	}
	!$atc_content && exit('Forbidden');

	if($atc['ifsign'] < 2){
		$atc_content = Char_cv($atc_content);
	} else{
		$atc_content = preg_replace('/javascript/i','java script',str_replace('.','&#46;',$atc_content));
	}
	require_once(R_P.'require/bbscode.php');
	if(file_exists(R_P."data/style/$skin.php") && strpos($skin,'..')===false){
		@include(R_P."data/style/$skin.php");
	}else{
		@include(R_P."data/style/wind.php");
	}
	$ptable    = $atc['ptable'];
	$ifconvert = convert($atc_content,$db_windpost) == $atc_content ? 1 : 2;
	$db->update("UPDATE pw_tmsgs SET content='$atc_content',ifconvert='$ifconvert' WHERE tid='$tid'");
	if($ifconvert == 2){
		$atc_content=convert($atc_content,$db_windpost);
	}
	if($atc['ifsign'] < 2){
		$atc_content=str_replace("\n",'<br>',$atc_content);
	}
	$atc_content = str_replace(array("\r","\n","'"),array("","\\n","\'"),$atc_content);
	echo"<script language=\"JavaScript1.2\">parent.AJAX_response('showcode','$atc_content');</script>";exit;
}elseif($action == 'fetchcode'){
	$rt = $db->get_one("SELECT ifsign,ifconvert,content FROM pw_tmsgs WHERE tid='$tid'");
	$atc_content = trim(addslashes($rt['content']));
	if (strpos($atc_content,$db_bbsurl) !== false){
		$atc_content = str_replace('p_w_picpath',$picpath,$atc_content);
		$atc_content = str_replace('p_w_upload',$attachname,$atc_content);
	}
	$atc_content = str_replace(array("\r","\n"),array("","\\n"),$atc_content);
	echo"<script language=\"JavaScript1.2\">parent.AJAX_response('fetchcode','$atc_content');</script>";exit;
}
?>