<?php
!function_exists('readover') && exit('Forbidden');
function cvipfrom($ip){
	global $ipa0;
	$d_ip=explode(".",$ip);
	$f_n=file_exists(R_P."ipdata/$d_ip[0].txt") ? R_P."ipdata/$d_ip[0].txt" : R_P.'ipdata/0.txt';
	$ip=implode('.',d_ip($d_ip));
	$db=fopen($f_n,"rb");
	flock($db,LOCK_SH);
	$d=fread($db,filesize($f_n));
	$s_ip="\n$d_ip[0].$d_ip[1].$d_ip[2]";
	if($s=strpos($d,$s_ip)){
		!($f=s_ip($db,$s,$ip)) && list($l_d,$ff)=nset($db);
	}else{	
		$s_ip="\n$d_ip[0].$d_ip[1]";
		if($s=strpos($d,$s_ip)){
			!($f=s_ip($db,$s,$ip)) && list($l_d,$ff)=nset($db);
		}elseif($s=strpos($d,"\n$d_ip[0]") && $f_n==R_P.'ipdata/0.txt'){ 
			$s_ip="\n$d_ip[0]";
			!($f=s_ip($db,$s,$ip)) && list($l_d,$ff)=nset($db);
		}else{
			$f='Unknown';
		}
	}
	if(empty($f) && $s!==false){
		while(ereg("^$s_ip","\n".$l_d)!==false){
			if($ipa0==1 || $f=s_ip($db,$s,$ip,$l_d)) break;
			list($l_d,$cff)=nset($db);
			$cff && $ff=$cff;
		}
	}
	fclose($db);
	return $f ? $f : $ff;
}
function s_ip($db,$s,$ip,$l_d=''){
	global $ipa0;
	if(!$l_d){
		fseek($db,$s+1,SEEK_SET);
		$l_d=fgets($db,100);
	}
	$ip_a=explode("\t",$l_d);
	$ip_a[0]=implode('.',d_ip(explode('.',$ip_a[0])));
	$ip_a[1]=implode('.',d_ip(explode('.',$ip_a[1])));
	if($ip<$ip_a[0]) $ipa0=1;
	if ($ip>=$ip_a[0] && $ip<=$ip_a[1]) return $ip_a[2].$ip_a[3];
}
function nset($db){
	$l_d=fgets($db,100);
	$ip_a=explode("\t",$l_d);
	return array($l_d,$ip_a[2].$ip_a[3]);
}
function d_ip($d_ip){
	for($i=0; $i<=3; $i++){
		$d_ip[$i]     = sprintf("%03d", $d_ip[$i]);
	}
	return $d_ip;
}
function lastinfo($fid,$allowhtm=0,$type='',$sys_type=''){
	global $db,$R_url,$htmdir,$foruminfo,$tid,$windid,$timestamp,$atc_title,$t_date,$replytitle;
	if($type == 'new'){
		$rt['tid']      = $tid;
		$rt['postdate'] = $timestamp;
		$rt['lastpost'] = $timestamp;
		$author   = $windid;
		$subject  = substrs($atc_title,21);
		$topicadd = ",tpost=tpost+1,article=article+1,topic=topic+1";
		$fupadd   = "tpost=tpost+1,article=article+1,topic=topic+1";
	} elseif($type == 'reply'){
		$rt['tid']      = $tid;
		$rt['postdate'] = $t_date;
		$rt['lastpost'] = $timestamp;
		$author         = $windid;
		$subject  = $atc_title ? substrs($atc_title,21) : 'Re:'.addslashes(substrs($replytitle,21));
		$topicadd = ",tpost=tpost+1,article=article+1";
		$fupadd   = "tpost=tpost+1,article=article+1";
	} else{
		$rt = $db->get_one("SELECT tid,author,postdate,subject,lastpost,lastposter FROM pw_threads WHERE fid='$fid' ORDER BY lastpost DESC LIMIT 0,1");

		if($rt['postdate'] == $rt['lastpost']){
			$subject = addslashes(substrs($rt['subject'],21));
			$author  = $rt['author'];
		}else{
			$subject = 'Re:'.addslashes(substrs($rt['subject'],21));
			$author  = $rt['lastposter'];
		}
		$topicadd=$fupadd="";
	}

	$htmurl   = $htmdir.'/'.$fid.'/'.date('ym',$rt['postdate']).'/'.$rt['tid'].'.html';
	$new_url  = file_exists(R_P.$htmurl) && $allowhtm && $sys_type!='1B' ? "$R_url/$htmurl" : "read.php?tid=$rt[tid]&page=e#a";
	$lastpost = $subject."\t".addslashes($author)."\t".$rt['lastpost']."\t".$new_url;
	$db->update("UPDATE pw_forumdata SET lastpost='$lastpost' $topicadd WHERE fid='$fid'");

	if($foruminfo['type'] == 'sub'){
		if($foruminfo['password'] != '' || $foruminfo['allowvisit'] != '' || $foruminfo['f_type'] == 'hidden'){
			$lastpost = '';
		} else{
			$lastpost = "lastpost='$lastpost'";
		}
		if($lastpost && $fupadd){
			$lastpost .= ', ';
		}
		if($lastpost || $fupadd){
			$db->update("UPDATE pw_forumdata SET $lastpost $fupadd WHERE fid='$foruminfo[fup]'");
			$rt1 = $db->get_one("SELECT fup,type FROM pw_forums WHERE fid='$foruminfo[fup]'");
			if($rt1['type'] == 'sub'){
				$db->update("UPDATE pw_forumdata SET $lastpost $fupadd WHERE fid='$rt1[fup]'");
			}
		}
	}
}

function bbspostguide(){
	global $db,$gp_uploadmoney,$creditset,$db_creditset,$db_upgrade,$db_hour,$ifupload,$groupid,$windid,$winduid,$winddb,$timestamp,$top_post,$fatherid,$fid,$tid,$tdtime,$db_autochange;

	$creditset = get_creditset($creditset,$db_creditset);
	if($db_autochange){
		if (file_exists(D_P."data/bbscache/set_cache.php")){
			list(,$set_control) = explode("|",readover(D_P."data/bbscache/set_cache.php"));
		} else{
			$set_control = 0;
		}
		if (($timestamp - $set_control) > $db_hour * 3600){
			require_once(R_P.'require/postconcle.php');
		}
	}
	if($groupid != 'guest'){
		$winddb['todaypost'] ++;
		$winddb['lastpost'] = $timestamp;
		$winddb['postnum'] ++;
		if($ifupload){
			$winddb['money'] -= $gp_uploadmoney;
		}

		if($top_post){
			$winddb['rvrc']  += $creditset['rvrc']['Post'];
			$winddb['money'] += $creditset['money']['Post'];
			customcredit($winduid,$creditset,'Post');
		} else{
			$winddb['rvrc']  += $creditset['rvrc']['Reply'];
			$winddb['money'] += $creditset['money']['Reply'];
			customcredit($winduid,$creditset,'Reply');
		}
		$memberid = getmemberid($winddb['postnum'],$winddb['rvrc'],$winddb['money'],$winddb['credit'],$winddb['onlinetime']);
		$winddb['memberid']!=$memberid && $db->update("UPDATE pw_members SET memberid='$memberid' WHERE uid='$winduid'");
		$db->update("UPDATE pw_memberdata SET postnum='$winddb[postnum]',rvrc='$winddb[rvrc]',money='$winddb[money]',todaypost='$winddb[todaypost]',lastpost='$winddb[lastpost]',uploadtime='$winddb[uploadtime]',uploadnum='$winddb[uploadnum]' WHERE uid='$winduid'");
	}else{	
		Cookie('userlastptime',$timestamp);
	}
}

function getmemberid($postnum,$rvrc,$money,$credit,$oltime){
	global $db_upgrade,$lneed;

	switch($db_upgrade){
		case 1:	$nums = $postnum;            break;
		case 2:	$nums = floor($rvrc/10);     break;
		case 3:	$nums = $money;              break;
		case 4:	$nums = $credit;             break;
		case 5:	$nums = floor($oltime/3600); break;
		default:$nums = $postnum;            break;
	}

	arsort($lneed);
	reset($lneed);
	foreach($lneed as $key=>$lowneed){
		$gid=$key;
		if($nums>=$lowneed){
			break;
		}
	}
	return $gid;
}

function check_data($type="post"){
	global $atc_usesign,$article,$msg_info,$vt_select,$db_titlemax,$db_postmin,$db_postmax,$atc_money;
	$atc_title=trim($_POST['atc_title']);
	$atc_content=trim($_POST['atc_content']);
	unset($_POST['atc_content']);
	if (strlen($atc_content)>=$db_postmax || strlen($atc_content)<$db_postmin){
		Showmsg('postfunc_content_limit');
	}
	if(empty($article) && !$atc_title || strlen($atc_title)>$db_titlemax){
		Showmsg('postfunc_subject_limit');
	}
	if ($atc_money>1000 || $atc_money<0){
		Showmsg('postfunc_money_limit'); 
	}
	if ($type=="vote" && empty($vt_select)){
		Showmsg('postfunc_noempty');
	}
	$atc_title = Char_cv($atc_title);
	$atc_content=$atc_usesign<2 ? Char_cv($atc_content) : preg_replace('/javascript/i','java script',str_replace('.','&#46;',$atc_content));
	return array($atc_title,$atc_content);
}

//自动url转变函数
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

function html_check($souce){
	global $db_bbsurl,$picpath,$attachname;
	if(strpos($souce,$db_bbsurl)!==false){
		$souce=str_replace($picpath,'p_w_picpath',$souce);
		$souce=str_replace($attachname,'p_w_upload',$souce);
	}
	return $souce;
}
?>