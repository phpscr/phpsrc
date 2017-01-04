<?php
$secondurl='message.php';
require_once('global.php');
/**
* 用户组权限判断
*/
$groupid=='guest'  && Showmsg('not_login');
$gp_maxmsg==0 && Showmsg('msg_group_right');

if($action=="write"){
	require_once(R_P.'require/forum.php');
	$js_path=geteditor();
}
require_once(R_P.'require/header.php');

$msginfo['mdate']='';
if(!$action) $action='receivebox';

$rs = $db->get_one("SELECT COUNT(*) AS msgcount FROM pw_msg WHERE touid='$winduid' AND type='rebox'");
$msgcount = $rs['msgcount'];
if($msgcount){
	$contl=number_format(($msgcount/$gp_maxmsg)*100,3);
} else{
	$msgcount='0';	$contl='0';
}

if(in_array($action,array('receivebox','readpub','del'))){
	$pubmsg = $db->get_one("SELECT readmsg,delmsg FROM pw_memberinfo WHERE uid='$winduid'");
	extract($pubmsg);
}
$msg_gid = $winddb['groupid'];
$verify  = substr(md5($winduid.$db_hash.$winddb['postnum']),0,8);
$msgdb   = array();

if($action=="receivebox"){
	/**
	* 收件箱
	*/
	$pubnew = $prnew = 0;

	$pmsgdb=array();
	$query = $db->query("SELECT mid,fromuid,togroups,username,type,title,mdate FROM pw_msg WHERE type='public' AND togroups LIKE '%,$msg_gid,%' ORDER BY mdate DESC");

	$newread=$newdel='';
	while($msginfo=$db->fetch_array($query)){
		if($delmsg && strpos(",$delmsg,",",$msginfo[mid],")!==false){
			$newdel .= $newdel ? ','.$msginfo['mid'] : $msginfo['mid']; 
			continue;
		}
		if($readmsg && strpos(",$readmsg,",",$msginfo[mid],")!==false){
			$newread .= $newread ? ','.$msginfo['mid'] : $msginfo['mid']; 
			$msginfo['ifnew']=0;
		}else{
			$pubnew = 2;
			$msginfo['ifnew']=1;
		}
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['from']=$msginfo['username'];
		$pmsgdb[]=$msginfo;
	}
	if($readmsg || $delmsg){
		$newread = $newread ? msgsort($newread) : '';
		$newdel  = $newdel  ? msgsort($newdel)  : '';
		if($readmsg!=$newread || $delmsg!=$newdel){
			$db->update("UPDATE pw_memberinfo SET readmsg='$newread',delmsg='$newdel' WHERE uid='$winduid'");
		}
	}

	$msgstart = $msgcount - $gp_maxmsg;
	$msgstart = $msgstart>0 ? $msgstart : 0;
	$query = $db->query("SELECT mid,fromuid,touid,username,ifnew,title,mdate FROM pw_msg WHERE type='rebox' AND touid='$winduid' ORDER BY mdate DESC LIMIT $msgstart,$gp_maxmsg");
	while($msginfo=$db->fetch_array($query)){
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['from']=$msginfo['username'];
		$msginfo['to']=$windid;
		$msginfo['ifnew'] && $prnew = 1;
		$msgdb[]=$msginfo;
	}
	$msgstart > 0 && $prnew = 1;

	$ifnew = $pubnew + $prnew;
	if($ifnew != $winddb['newpm']){
		$db->update("UPDATE pw_members SET newpm='$ifnew' WHERE uid='$winduid'");
	}
	require_once(PrintEot('message'));footer();

}elseif($action=="sendbox"){
	/**
	* 发件箱
	*/
	$num=0;
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
	require_once(PrintEot('message'));footer();

}elseif($action=="scout"){
	/**
	* 消息跟踪
	*/
	$query = $db->query("SELECT mid,fromuid,touid,ms.username,me.username AS toname,ifnew,title,mdate FROM pw_msg ms LEFT JOIN pw_members me ON me.uid=ms.touid WHERE type='rebox' AND fromuid='$winduid' ORDER BY mdate DESC LIMIT $gp_maxmsg");
	while($msginfo=$db->fetch_array($query)){
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['from']=$windid;
		$msginfo['to']=$msginfo['toname'];
		$msgdb[]=$msginfo;
	}
	require_once(PrintEot('message'));footer();

}elseif($action=='readpub'){
	require_once(R_P.'require/bbscode.php');

	$msginfo = $db->get_one("SELECT mid,fromuid,touid,username,ifnew,title,mdate,content FROM pw_msg WHERE mid='$mid' AND type='public' AND togroups LIKE '%,$msg_gid,%'");
	if ($msginfo){
		$msginfo['content']=str_replace("\n","<br>",$msginfo['content']);
		$msginfo['content']=convert($msginfo['content'],$db_windpost);
		$msginfo['title'] =str_replace('&ensp;$','$', $msginfo['title']);
		$msginfo['content'] =str_replace('&ensp;$','$', $msginfo['content']);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['content'] = str_replace("\$email",$winddb['email'],$msginfo['content']);
		$msginfo['content'] = str_replace("\$windid",$windid,$msginfo['content']);
		if($pubmsg){
			if(strpos(",$readmsg,",",$msginfo[mid],")===false){
				$readmsg .= $readmsg ? ','.$msginfo['mid'] : $msginfo['mid'];
				$readmsg=msgsort($readmsg);
				$db->update("UPDATE pw_memberinfo SET readmsg='$readmsg' WHERE uid='$winduid'");
			}
		}else{
			$readmsg=$msginfo['mid'];
			$db->update("INSERT INTO pw_memberinfo SET uid='$winduid',readmsg='$readmsg'");
		}
		getusermsg($winduid,$winddb['newpm'],'pub');
	} else{
		Showmsg('msg_error');
	}
	require_once(PrintEot('message'));footer();

}elseif($action=='read'){
	require_once(R_P.'require/bbscode.php');
	
	$msginfo = $db->get_one("SELECT mid,fromuid,touid,username,ifnew,title,mdate,content FROM pw_msg WHERE mid='$mid' AND type='rebox' AND touid='$winduid'");
	if ($msginfo){
		$msginfo['content']=str_replace("\n","<br>",$msginfo['content']);
		$msginfo['content']=convert($msginfo['content'],$db_windpost);
		$msginfo['title'] =str_replace('&ensp;$','$', $msginfo['title']);
		$msginfo['content'] =str_replace('&ensp;$','$', $msginfo['content']);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$db->update("UPDATE pw_msg SET ifnew=0 WHERE mid='$mid'");
		getusermsg($winduid,$winddb['newpm']);
	} else{
		Showmsg('msg_error');
	}
	require_once(PrintEot('message'));footer();

}elseif($action=='readsnd'){
	require_once(R_P.'require/bbscode.php');
	
	$msginfo = $db->get_one("SELECT mid,fromuid,touid,username,ifnew,title,mdate,content FROM pw_msg WHERE mid='$mid' AND type='sebox' AND fromuid='$winduid'");
	if ($msginfo){
		$msginfo['content']=str_replace("\n","<br>",$msginfo['content']);
		$msginfo['content']=convert($msginfo['content'],$db_windpost);
		$msginfo['title'] =str_replace('&ensp;$','$', $msginfo['title']);
		$msginfo['content'] =str_replace('&ensp;$','$', $msginfo['content']);
		$msginfo['mdate']=get_date($msginfo['mdate']);
	} else{
		Showmsg('msg_error');
	}
	require_once(PrintEot('message'));footer();

}elseif($action=='readscout'){
	require_once(R_P.'require/bbscode.php');
	
	$msginfo = $db->get_one("SELECT mid,fromuid,touid,username,ifnew,title,mdate,content FROM pw_msg WHERE mid='$mid' AND type='rebox' AND fromuid='$winduid'");
	if($msginfo){
		$msginfo['content']=str_replace("\n","<br>",$msginfo['content']);
		$msginfo['content']=convert($msginfo['content'],$db_windpost);
		$msginfo['title'] =str_replace('&ensp;$','$', $msginfo['title']);
		$msginfo['content'] =str_replace('&ensp;$','$', $msginfo['content']);
		$msginfo['mdate']=get_date($msginfo['mdate']);
	} else{
		Showmsg('msg_error');
	}
	require_once(PrintEot('message'));footer();

}elseif($action=="write"){
	/**
	* 写短信
	*/
	$gp_allowmessege == 0 && Showmsg('msg_group_right');
	if($gp_postpertime){
		$rp = $db->get_one("SELECT mdate FROM pw_msg WHERE fromuid='$winduid' ORDER BY mdate DESC LIMIT 1");
		$lastwrite = $rp['mdate'];
		if ($timestamp - $lastwrite <= $gp_postpertime){
			Showmsg('msg_limit');
		}
	}
	list(,,,$msggd)=explode("\t",$db_gdcheck);
	
	if(empty($_POST['step'])){
		$frienddb = array();
		$query = $db->query("SELECT f.friendid,m.username FROM pw_friends f LEFT JOIN pw_members m ON m.uid=f.friendid WHERE f.uid='$winduid'");
		while ($rt = $db->fetch_array($query)){
			$frienddb[]=$rt;
		}
		$subject=$atc_content='';
		if(is_numeric($remid)){
			$reinfo=$db->get_one("SELECT fromuid,touid,username,type,title,content FROM pw_msg WHERE mid='$remid' AND type='rebox' AND touid='$winduid'");
			if($reinfo){
				$msgid="value=\"$reinfo[username]\"";
				$subject=strpos($reinfo['title'],'Re:')===false ? 'Re:'.$reinfo['title']:$reinfo['title'];
				$atc_content="[quote]".trim(substrs(preg_replace("/\[quote\](.+?)\[\/quote\]/is",'',$reinfo['content']),100))."[/quote]\n\n";
			}
		}elseif(is_numeric($edmid)){
			$edinfo=$db->get_one("SELECT g.touid,g.type,g.title,g.content,m.username FROM pw_msg g LEFT JOIN pw_members m ON g.touid=m.uid WHERE g.mid='$edmid' AND g.fromuid='$winduid' AND g.ifnew='1' AND g.type='rebox'");
			if($edinfo){
				$msgid="value=\"$edinfo[username]\"";
				$subject=$edinfo['title'];
				$atc_content=$edinfo['content'];
			}			
		}elseif(is_numeric($touid)){
			$reinfo=$db->get_one("SELECT username FROM pw_members WHERE uid='$touid'");
			$msgid="value=\"$reinfo[username]\"";
		} else{
			$msgid='';
		}
		require_once(PrintEot('message'));footer();
	} elseif($_POST['step']==2){
		$msggd && GdConfirm($gdcode);
		require_once(R_P.'require/msg.php');
		$msg_title   = trim($msg_title);
		$atc_content = trim($atc_content);
		if (empty($atc_content) ||empty($msg_title)){
			Showmsg('msg_empty');
		} elseif (strlen($msg_title)>75||strlen($atc_content)>1500){
			Showmsg('msg_subject_limit');
		}
		if($pwuser){
			$rt=$db->get_one("SELECT uid FROM pw_members WHERE username='$pwuser'");
			if(!$rt){
				$errorname=$pwuser;
				Showmsg('user_not_exists');
			}
			$touid[]=$rt['uid'];
		}
		if(!$touid){
			Showmsg('msg_empty');
		}
		$touser = array();
		foreach($touid as $key=>$val){
			$rt = $db->get_one("SELECT username,newpm,banpm,msggroups FROM pw_members WHERE uid='".(int)$val."'");
			if($rt){
				$touser[]=$rt['username'];
			}
			if($rt['msggroups'] && strpos($rt['msggroups'],",$groupid,")===false || strpos(",$rt[banpm],",",$windid,")!==false){
				Showmsg('msg_refuse');
			}
		}
		$atc_content = Char_cv($atc_content);
		$msg_title   = Char_cv($msg_title);
		$atc_content = autourl($atc_content);
		foreach($touser as $key=>$val){
			if($edmid && $val==$pwuser){
				$db->update("UPDATE pw_msg SET title='$msg_title',mdate='$timestamp',content='$atc_content' WHERE mid='$edmid' AND fromuid='$winduid' AND ifnew='1'");
				continue;
			}     //未读短消息编辑
			$msg = array(
				$val,
				$winduid,
				$msg_title,
				$timestamp,
				$atc_content,
				$ifsave,
				$windid
			);
			writenewmsg($msg);
		}
		refreshto("message.php?action=receivebox",'operate_success');
	}
}elseif($action=="clear"){
	if($ckcode!=$verify){
		Showmsg('undefined_action');
	}
	$db->update("DELETE FROM pw_msg WHERE type='rebox' AND touid='$winduid'");
	$db->update("DELETE FROM pw_msg WHERE type='sebox' AND fromuid='$winduid'");
	getusermsg($winduid,$winddb['newpm']);
	Showmsg('del_success');

}elseif($action=="del"){	
	
	if(!is_numeric($delids)){
		$delids='';
		if(is_array($delid)){
			foreach($delid as $value){
				is_numeric($value) && $delids.=$value.',';
			}
			$delids && $delids=substr($delids,0,-1);
		}
	}
	if($towhere=='receivebox'){
		if(!is_numeric($pdelids)){
			$pdelids='';
			if(is_array($pdelid)){
				foreach($pdelid as $value){
					is_numeric($value) && $pdelids.=$value.',';
				}
				$pdelids && $pdelids=substr($pdelids,0,-1);
			}
		}
	}else{
		$pdelids='';
	}
	!$delids && !$pdelids && Showmsg('del_error');
	
	if($delids){
		if($towhere=='receivebox'){
			$sql = " AND type='rebox' AND touid='$winduid'";
		}elseif($towhere=='sendbox'){
			$sql = " AND type='sebox' AND fromuid='$winduid'";
		}else{
			$sql = " AND type='rebox' AND fromuid='$winduid' AND ifnew=1";
		}
		$db->update("DELETE FROM pw_msg WHERE mid IN($delids) $sql");
		getusermsg($winduid,$winddb['newpm']);
	}
	if($pdelids){
		if($pubmsg){
			$msginfo='';
			$readmsg=','.$readmsg.',';
			$delmsg = $delmsg ? ','.$delmsg.',' : ','; 
			$deliddb=explode(',',$pdelids);
			foreach($deliddb as $key=>$val){
				if(strpos("$readmsg",",$val,")!==false){
					$readmsg=str_replace(",$val,",",",$readmsg);
				}
				if(strpos($delmsg,",$val,")===false){
					$delmsg.=$val.',';
				}
			}
			$readmsg=msgsort(substr($readmsg,1,-1));
			$delmsg=msgsort(substr($delmsg,1,-1));
			$db->update("UPDATE pw_memberinfo SET readmsg='$readmsg',delmsg='$delmsg' WHERE uid='$winduid'");
		}else{
			$delmsg=msgsort($delids);
			$db->update("INSERT INTO pw_memberinfo SET uid='$winduid',delmsg='$delmsg'");
		}
		getusermsg($winduid,$winddb['newpm'],'pub');
	}
	
	refreshto("message.php?action=$towhere",'operate_success');

}elseif($action=="down"){
	
	$downids=$pdownids='';

	if(is_array($delid)){
		foreach($delid as $value){
			is_numeric($value) && $downids.=$value.',';
		}
		$downids && $downids=substr($downids,0,-1);
	}
	
	if(is_array($pdelid) && $towhere=='receivebox'){
		foreach($pdelid as $value){
			is_numeric($value) && $pdownids.=$value.',';
		}
		$pdownids && $pdownids=substr($pdownids,0,-1);
	}
	!$downids && !$pdownids && Showmsg('sel_error');
	
	if($pdownids){
		$query = $db->query("SELECT * FROM pw_msg WHERE mid IN($pdownids) AND type='public' AND togroups LIKE '%,$msg_gid,%'");
		while($msginfo=$db->fetch_array($query)){
			$msgdb[]=$msginfo;
		}		
	}
	if($downids){	
		if($towhere=='receivebox'){
			$sql = " AND type='rebox' AND touid='$winduid'";
		}elseif($towhere=='sendbox' || $towhere=='scout'){
			$sql = " AND fromuid='$winduid'";
		}else{
			Showmsg('undefined_action');
		}
		$query = $db->query("SELECT * FROM pw_msg WHERE mid IN($downids) $sql");
		while($msginfo=$db->fetch_array($query)){
			$msgdb[]=$msginfo;
		}
	}
	if($msgdb){
		$content= "$db_bbsname   URL:$db_bbsurl\r\n\r\n";
		$content.=$windid.' Message Download '.get_date($timestamp)."\r\n\r\n";
		foreach($msgdb as $key=>$msginfo){
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
	}else{
		Showmsg('undefined_action');
	}

}elseif ($action=="banned"){
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
					$usergroup .= "<div style=\"width:20%;float:left\"><input type=\"checkbox\" name=\"msggroups[]\" value=\"$key\" $checked>$value</div>";
					$num % 5 == 0 && $usergroup .='<div style="clear:both"></div>';
				}
			}
		}
		$banidinfo = $rs['banpm'];
		require_once(PrintEot('message'));footer();
	} elseif($_POST['step']==2){
		$groups='';
		if($_G['msggroup'] && $msggroups){
			foreach($msggroups as $key=>$val){
				if(is_numeric($val)){
					$groups .= $groups ? ','.$val : $val;
				}
			}
		}
		$groups && $groups = ",$groups,";
		$banpm = Char_cv($banidinfo);
		$db->update("UPDATE pw_members SET banpm='$banpm',msggroups='$groups' WHERE uid='$winduid'");
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
function getusermsg($winduid,$ifnew,$type=''){
	global $db;
	if($type==''){
		$rs=$db->get_one("SELECT ifnew FROM pw_msg WHERE touid='$winduid' AND ifnew='1' AND type='rebox' LIMIT 1");
		if(!$rs){
			if($ifnew==1 || $ifnew==3){
				$ifnew--;
				$db->update("UPDATE pw_members SET newpm='$ifnew' WHERE uid='$winduid'");
			}
		} else{
			if($ifnew==0 || $ifnew==2){
				$ifnew++;
				$db->update("UPDATE pw_members SET newpm='$ifnew' WHERE uid='$winduid'");
			}
		}
	} else{
		global $msg_gid,$readmsg,$delmsg;
		$checkmsg='0';
		$readmsg && $checkmsg .=','.$readmsg;
		$delmsg  && $checkmsg .=','.$delmsg;
		$rt=$db->get_one("SELECT mid FROM pw_msg WHERE type='public' AND togroups LIKE '%,$msg_gid,%' AND mid NOT IN ($checkmsg) LIMIT 1");
		if(!$rt){
			if($ifnew==3 || $ifnew==2){
				$ifnew-=2;
				$db->update("UPDATE pw_members SET newpm='$ifnew' WHERE uid='$winduid'");
			}
		} else{
			if($ifnew==0 || $ifnew==1){
				$ifnew+=2;
				$db->update("UPDATE pw_members SET newpm='$ifnew' WHERE uid='$winduid'");
			}
		}

	}
}
function msgsort($msgstr){
	if(empty($msgstr)){
		return '';
	}
	$msgdb=explode(',',$msgstr);
	arsort($msgdb);
	$newmsg=implode(',',$msgdb);
	return $newmsg;
}
?>