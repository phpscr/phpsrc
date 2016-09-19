<?php
error_reporting(0);
define('W_P',__FILE__ ? substr(__FILE__,0,-14) : '../');
require_once(W_P.'global.php');
require_once(R_P.'wap/chinese.php');
require_once(R_P.'wap/wap_mod.php');
include_once(D_P.'data/bbscache/forum_cache.php');

if(!$db_wapifopen){
	wap_msg('wap_closed');
}
if($charset != 'utf8'){
	$chs = new Chinese('UTF8',$charset);
	foreach($_POST as $key=>$value){
		$$key=$chs->Convert($$key);
	}
}
function forumcheck($fid){
	global $db,$groupid,$gp_allowrp;
	$fm=$db->get_one("SELECT password,allowvisit,f_type,f_check,allowpost,allowrp FROM pw_forums WHERE fid='$fid'");
	if(!$fm || $fm['password']!='' || $rt['f_type']=='hidden' || $fm['allowvisit'] && @strpos($fm['allowvisit'],",$groupid,")===false || $rt['f_check']>'0'){
		wap_msg('forum_right');
	}
}
function postcheck($fid,$action){
	global $db,$groupid,$gp_allowpost,$gp_allowrp;

	$fm=$db->get_one("SELECT password,allowvisit,f_type,f_check,allowpost,allowrp FROM pw_forums WHERE fid='$fid'");
	if(!$fm || $fm['password']!='' || $rt['f_type']=='hidden' || $fm['allowvisit'] && @strpos($fm['allowvisit'],",$groupid,")===false){
		wap_msg('post_right');
	}
	if($action=='new'){
		if($fm['f_check']=='1' || $fm['f_check']=='3'){
			wap_msg('post_right');
		}
		if($fm['allowpost'] && strpos($fm['allowpost'],",$groupid,")===false){
			wap_msg('post_right');
		}
		if(!$fm['allowpost'] && $gp_allowpost==0){
			wap_msg('post_group');
		}
	}elseif($action=='reply'){
		if($fm['f_check']=='2' || $fm['f_check']=='3'){
			wap_msg('reply_right');
		}
		if($fm['allowrp'] && strpos($fm['allowrp'],",$groupid,")===false){
			wap_msg('reply_right');
		}
		if(!$fm['allowrp'] && $gp_allowrp==0){
			wap_msg('reply_group');
		}
	}
}

function wap_cv($msg){
	$msg = str_replace('&','&amp;',$msg);
	$msg = str_replace('&nbsp;',' ',$msg);
	$msg = str_replace('"','&quot;',$msg);
	$msg = str_replace("'",'&#39;',$msg);
	$msg = str_replace("<","&lt;",$msg);
	$msg = str_replace(">","&gt;",$msg);
	$msg = str_replace("\t","   &nbsp;  &nbsp;",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);
	return $msg;
}
?>