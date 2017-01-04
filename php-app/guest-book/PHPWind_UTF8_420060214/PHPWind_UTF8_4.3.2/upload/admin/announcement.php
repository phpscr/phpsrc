<?php
!function_exists('adminmsg') && exit('Forbidden');
require_once GetLang('all');
$basename="$admin_file?adminjob=announcement";

if ($admin_gid == 5){
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sqladd = "WHERE fid IN($allowfid)";
} else {
	include D_P.'data/bbscache/forumcache.php';
	list($hidefid,$hideforum) = GetHiddenForum();
	if($admin_gid == 3){
		$forumcache .= $hideforum;
		$sqladd = 'WHERE 1';
	} else{
		$sqladd = "WHERE fid NOT IN($hidefid)";
	}
}

if (empty($action)){
	include_once(D_P."data/bbscache/forum_cache.php");
	$annoucedb = array();
	$query     = $db->query("SELECT * FROM pw_announce $sqladd ORDER BY fid,vieworder,startdate DESC");
	while($rt  = $db->fetch_array($query)){
		if ($rt['fid'] == '-1'){
			$rt['name'] = "<a href='index.php'>$lang[whole_notice]</a>";
		} elseif ($rt['fid'] == '-2'){
			$rt['name'] = "<a href='index.php'>$lang[cms_notice]</a>";
		} elseif ($forum[$rt['fid']]['type'] == 'category'){
			$rt['name'] = "<a href='index.php?cateid=$rt[fid]'>".$forum[$rt['fid']]['name']."</a>";
		} else {
			$rt['name'] = "<a href='thread.php?fid=$rt[fid]'>".$forum[$rt['fid']]['name']."</a>";
		}
		$rt['subject']   = substrs($rt['subject'],30);
		$rt['startdate'] = get_date($rt['startdate']);
		$annoucedb[] = $rt;
	}
	include PrintEot('notice');exit;
} elseif ($action == 'add'){
	if (!$step){
		$atc_content = '';
		$js_path=file_exists(D_P."data/{$stylepath}_editor.js") ? "data/{$stylepath}_editor.js" : "data/wind_editor.js";
		include PrintEot('notice');exit;
	} else {
		if ($admin_gid == 5 && strpos(",$allowfid,",",$fid,") === false){
			if ($fid == '-1'){
				adminmsg('annouce_all');
			} elseif ($forum[$fid]['type']=='category'){
				adminmsg('annouce_category');
			} else {
				adminmsg('annouce_forum');
			}
		}
		!is_numeric($fid) && adminmsg('illegal_fid');
		!is_numeric($vieworder) && $vieworder=0;
		if (empty($newsubject) || empty($atc_content)){
			adminmsg('annouce_empty');
		}
		$newsubject  = ieconvert($newsubject);
		$atc_content = ieconvert($atc_content);
		$atc_content = trim(autourl($atc_content));

		$db->update("INSERT INTO pw_announce(fid,vieworder,author,startdate,subject,content) VALUES('$fid','$vieworder','".addslashes($admin_name)."','$timestamp','$newsubject','$atc_content')");
		updatecache_i();
		adminmsg('operate_success');
	}
} elseif ($action=='edit'){
	if (!$step){
		$js_path=file_exists(D_P."data/{$stylepath}_editor.js") ? "data/{$stylepath}_editor.js" : "data/wind_editor.js";
		@extract($db->get_one("SELECT * FROM pw_announce WHERE aid='$aid'"));
		if ($admin_gid == 5 && strpos(",$allowfid,",",$fid,") === false){
			adminmsg('annouce_right');
		}
		HtmlConvert($subject);
		HtmlConvert($content);
		$atc_content = $content;
		include PrintEot('notice');exit;
	} else{
		!is_numeric($vieworder) && $vieworder=0;
		$newsubject  = ieconvert($newsubject);
		$atc_content = ieconvert($atc_content);
		$atc_content = trim(autourl($atc_content));
		$db->update("UPDATE pw_announce SET vieworder='$vieworder',subject='$newsubject',content='$atc_content' $sqladd AND aid='$aid'");
		updatecache_i();
		adminmsg('operate_success');
	}
} elseif ($_POST['action']=='del'){
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');	
	}
	$db->update("DELETE FROM pw_announce $sqladd AND aid IN($selid)");
	updatecache_i();
	adminmsg('operate_success');
}
function autourl($message){
	global $db_autoimg;
	if ($db_autoimg==1){
		$message= preg_replace(array(
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\©¦]+\.gif)/i",
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\©¦]+\.jpg)/i"
				), array(
					"[img]\\1\\3[/img]",
					"[img]\\1\\3[/img]"
				), ' '.$message);
	}
	$message= preg_replace(	array(
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\©¦]+)/i",
					"/(?<=[^\]a-z0-9\/\-_.~?=:.])([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/i"
				), array(
					"[url]\\1\\3[/url]",
					"[email]\\0[/email]"
				), ' '.$message);

	return $message;
}
?>