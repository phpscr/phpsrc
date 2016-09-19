<?php
$secondurl='message.php';
require_once('global.php');
/**
* 用户组权限判断
*/
$groupid=='guest'  && Showmsg('not_login');
$gp_maxmsg==0 && Showmsg('msg_group_right');

if($action=="write"){
	$js_path=file_exists(D_P."data/{$stylepath}_editor.js") ? "data/{$stylepath}_editor.js" : "data/wind_editor.js";
}
require_once(R_P.'require/header.php');

$msginfo['mdate']='';
if(!$action) $action='receivebox';

$rs = $db->get_one("SELECT COUNT(*) AS msgcount FROM pw_msg WHERE touid='$winduid' AND type='rebox'");
$msgcount = $rs['msgcount'];
if ($msgcount){
	$contl=number_format(($msgcount/$gp_maxmsg)*100,3);
}else{
	$msgcount='0';	$contl='0';
}

$action!="read" && $action!='clear' && $action!='del' && getusermsg($winduid,$winddb['newpm']);

$msgdb=array();
/**
* 收件箱
*/
if ($action=="receivebox"){
	$num=0;$readtype='read';
	$query = $db->query("SELECT mid,fromuid,touid,username,ifnew,title,mdate FROM pw_msg WHERE type='rebox' AND touid='$winduid' ORDER BY mdate DESC");
	while($msginfo=$db->fetch_array($query)){
		$num++;
		if($num>$gp_maxmsg){
			$db->update("DELETE FROM pw_msg WHERE mid='$msginfo[mid]'");
			continue;
		}
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['from']=$msginfo['username'];
		$msginfo['to']=$windid;
		$msgdb[]=$msginfo;		
	}
	$towhere='receivebox';
	require_once(PrintEot('message'));footer();
}
/**
* 发件箱
*/
if ($action=="sendbox"){
	$num=0;$readtype='readsnd';
	$query = $db->query("SELECT mid,fromuid,touid,username,ifnew,title,mdate FROM pw_msg WHERE type='sebox' AND fromuid='$winduid' ORDER BY mdate DESC");
	while($msginfo=$db->fetch_array($query)){
		$num++;
		if($num>$gp_maxmsg){
			$db->update("DELETE FROM pw_msg WHERE mid='$msginfo[mid]'");
			continue;
		}
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['from']=$windid;
		$msginfo['to']=$msginfo['username'];
		$msgdb[]=$msginfo;
	}
	$towhere='sendbox';
	require_once(PrintEot('message'));footer();
}
/**
* 消息跟踪
*/
if ($action=="scout"){
	$readtype='readscout';
	$query = $db->query("SELECT mid,fromuid,touid,ms.username,me.username AS toname,ifnew,title,mdate FROM pw_msg ms LEFT JOIN pw_members me ON me.uid=ms.touid WHERE type='rebox' AND fromuid='$winduid' ORDER BY mdate DESC LIMIT $gp_maxmsg");
	while($msginfo=$db->fetch_array($query)){
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['from']=$windid;
		$msginfo['to']=$msginfo['toname'];
		$msgdb[]=$msginfo;
	}
	$towhere='scout';
	require_once(PrintEot('message'));footer();
}

/**
* 阅读短消息
*/
if ($action=="read"||$action=="readsnd"||$action=="readscout"){
	require_once(R_P.'require/bbscode.php');
	if($action=="read"){
		$preaction='receivebox';
		$sqladd="type='rebox' AND touid='$winduid'";
	} elseif($action=="readsnd"){
		$preaction='sendbox';
		$sqladd="type='sebox' AND fromuid='$winduid'";
	} elseif($action=="readscout"){
		$preaction='scout';
		$sqladd="type='rebox' AND fromuid='$winduid'";
	} else{
		$sqladd='';
	}
	$msginfo = $db->get_one("SELECT mid,fromuid,touid,username,ifnew,title,mdate,content FROM pw_msg WHERE mid='$mid' AND $sqladd");
	if ($msginfo){
		$msginfo['content']=str_replace("\n","<br>",$msginfo['content']);
		$msginfo['content']=convert($msginfo['content'],$db_windpost);
		$msginfo['title'] =str_replace('&ensp;$','$', $msginfo['title']);
		$msginfo['content'] =str_replace('&ensp;$','$', $msginfo['content']);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		if($action!="readscout" && $msginfo['ifnew']==1)
			$db->update("UPDATE pw_msg SET ifnew=0 WHERE mid='$mid'");
	} else{
		Showmsg('msg_error');
	}
	$action=="read" && getusermsg($winduid,$winddb['newpm']);
	require_once(PrintEot('message'));footer();

}
/**
* 写短信
*/
if($action=="write"){
	$gp_allowmessege == 0 && Showmsg('msg_group_right');
	$lastwrite = $winddb['lastpost'];
	if ($timestamp - $lastwrite <= $gp_postpertime){
		Showmsg('msg_limit');
	}
	list(,,,$msggd)=explode("\t",$db_gdcheck);
	if (empty($_POST['step'])){
		$subject=$atc_content='';
		if(is_numeric($remid)){
			$reinfo=$db->get_one("SELECT fromuid,touid,username,type,title,content FROM pw_msg WHERE mid='$remid' AND (fromuid='$winduid' OR (type='rebox' AND touid='$winduid'))");
			if($reinfo){
				$msgid="value=$reinfo[username]";
				$subject=strpos($reinfo['title'],'Re:')===false ? 'Re:'.$reinfo['title']:$reinfo['title'];
				$atc_content="[quote]".trim(substrs(preg_replace("/\[quote\](.+?)\[\/quote\]/is",'',$reinfo['content']),100))."[/quote]\n\n";
			}
		} elseif(is_numeric($touid)){
			$reinfo=$db->get_one("SELECT username FROM pw_members WHERE uid='$touid'");
			$msgid="value=$reinfo[username]";
		} else{
			$msgid='';
		}
		require_once(PrintEot('message'));footer();
	} elseif($_POST['step']==2){
		$msggd && GdConfirm($gdcode);
		require_once(R_P.'require/msg.php');
		$msg_title   = trim($msg_title);
		$atc_content = trim($atc_content);
		if (empty($msg_ruser) || empty($atc_content) ||empty($msg_title)){
			Showmsg('msg_empty');	
		} elseif (strlen($msg_title)>75||strlen($atc_content)>1500){
			Showmsg('msg_subject_limit');
		}
		$atc_content = Char_cv($atc_content);
		$msg_title   = Char_cv($msg_title);
		$atc_content = autourl($atc_content);
		$messageinfo = array($msg_ruser,$winduid,$msg_title,$timestamp,$atc_content,$ifsave,$windid);
		writenewmsg($messageinfo);
		$db->update("UPDATE pw_memberdata SET lastpost='$timestamp' WHERE uid='$winduid'");
		refreshto("message.php?action=receivebox",'operate_success');
	}
}
if ($action=="clear"){
	$db->update("DELETE FROM pw_msg WHERE (type='rebox' AND touid='$winduid') OR (type='sebox' AND fromuid='$winduid')");
	getusermsg($winduid,$winddb['newpm']);
	Showmsg('del_success');
}
if ($action=="del"){
	if(!is_numeric($delids)){
		!$delid && Showmsg('del_error');
		$delids='';
		foreach($delid as $value){
			!is_numeric($value) && Showmsg('undefined_action');
			$delids.=$value.',';
		}
		$delids && $delids=substr($delids,0,-1);
	}
	if($delids){
		$db->update("DELETE FROM pw_msg WHERE mid IN($delids) AND ((type='rebox' AND touid='$winduid') OR (type='sebox' AND fromuid='$winduid') OR (type='rebox' AND fromuid='$winduid' AND ifnew=1))");
		if($db->affected_rows()==0){
			Showmsg('undefined_action');
		}
		getusermsg($winduid,$winddb['newpm']);
		refreshto("message.php?action=$towhere",'operate_success');
	} else{
		Showmsg('undefined_action');
	}
}
if($action=="down"){
	
	if(!is_numeric($downids)){
		$downids='';
		!$delid && Showmsg('del_error');
		foreach($delid as $value){
			!is_numeric($value) && Showmsg('undefined_action');
			$downids.=$value.',';
		}
		$downids && $downids=substr($downids,0,-1);
	}
	if($downids){
		$content= "$db_bbsname   URL:$db_bbsurl\r\n\r\n";
		$content.=$windid.' Message Download '.get_date($timestamp)."\r\n\r\n";
		$query = $db->query("SELECT * FROM pw_msg WHERE mid IN($downids) AND ((type='rebox' AND touid='$winduid') OR fromuid='$winduid')");
		while($msginfo=$db->fetch_array($query)){
			$content .= "================================================================================\r\n";
			$content .= "Author :\t".$msginfo['username']."\r\n";
			$content .= "Date :\t" .get_date($msginfo['mdate']). "\r\n";
			$content .= "Title :\t" .$msginfo['title']. "\r\n";
			$content .= "--------------------------------------------------------------------------------\r\n";
			$content .= "Content：\r\n ".$msginfo['content']."\r\n\r\n";
		}
		$filename='Message-'.$windid.'-'.get_date($timestamp,'Y-m-d').'.txt';

		ob_end_clean();
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Pragma: no-cache');
		header('Content-Encoding: none');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Length: ' . strlen($content));
		header('Content-type: txt');
		echo $content;
		exit;
	}
}
if ($action=="banned"){
	if(empty($_POST['step'])){
		include_once(D_P.'data/bbscache/level.php');
		$rs = $db->get_one("SELECT banpm,msggroups FROM pw_members WHERE uid='$winduid'");
		
		if($_G['msggroup']){
			$usergroup = '';
			$num = 0;
			foreach($ltitle as $key => $value){
				if($key != 1 && $key != 2){
					if($rs['msggroups'] && strpos($rs['msggroups'],','.$key.',') !== false){
						$checked = 'checked';
					} else{
						$checked = '';
					}
					$num ++;
					$htm_tr = $num % 5 == 0 ?  '</tr><tr>' : '';
					$usergroup .= "<td width='20%'><input type='checkbox' name='msggroups[]' value='$key' $checked>$value</td>$htm_tr";
				}
			}
		}
		$banidinfo = '';
		if($rs['banpm']){
			$rs['banpm']=substr($rs['banpm'],1,-1);
			$query=$db->query("SELECT username FROM pw_members WHERE uid IN($rs[banpm])");
			while($bandb=$db->fetch_array($query)){
				$banidinfo.=$bandb['username'].',';
			}
			$banidinfo && $banidinfo=substr($banidinfo,0,-1);
		}
		require_once(PrintEot('message'));footer();
	} elseif($_POST['step']==2 && strlen($banidinfo)<500){
		$msggroups = $_G['msggroup'] && $msggroups ? ','.implode(',',$msggroups).',' : '';
		$banuids   = '';
		$banidinfo = Char_cv($banidinfo);
		$sql_add   = "'".str_replace(',',"','",$banidinfo)."'";
		$query=$db->query("SELECT uid,username FROM pw_members WHERE username IN($sql_add)");
		while($bandb = $db->fetch_array($query)){
			$banarray[] = $bandb['username'];
			$banuids   .= $bandb['uid'].',';
		}
		$ban_A = explode(",",$banidinfo);
		foreach($ban_A as $value){
			if($value){
				if(!in_array($value,$banarray)){
					$errorname = $value;
					Showmsg('user_not_exists');
				}
			}
		}
		$banuids && $banuids = ','.$banuids;
		$db->update("UPDATE pw_members SET banpm='$banuids',msggroups='$msggroups' WHERE uid='$winduid'");
		Showmsg('msg_ban_success');
	} else{
		Showmsg('msg_ban_fail');
	}
}
function autourl($message){
	global $db_autoimg;
	if($db_autoimg==1){
		$message= preg_replace(array(
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+\.gif)/i",
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+\.jpg)/i"
				), array(
					"[img]\\1\\3[/img]",
					"[img]\\1\\3[/img]"
				), ' '.$message);
	}
	$message= preg_replace(	array(
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+)/i",
					"/(?<=[^\]a-z0-9\/\-_.~?=:.])([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/i"
				), array(
					"[url]\\1\\3[/url]",
					"[email]\\0[/email]"
				), ' '.$message);
	return $message;
}
function getusermsg($winduid,$ifnew){
	global $db;
	$rs=$db->get_one("SELECT ifnew FROM pw_msg WHERE touid='$winduid' AND ifnew='1' AND type='rebox'");
	if(!$rs){
		$db->update("UPDATE pw_members SET newpm=0 WHERE uid='$winduid'");
	}elseif($ifnew==0){
		$db->update("UPDATE pw_members SET newpm=1 WHERE uid='$winduid'");
	}
}

?>