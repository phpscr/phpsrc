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
		$subject  = substrs($atc_title,26);
		$topicadd = ",tpost=tpost+1,article=article+1,topic=topic+1";
		$fupadd   = "tpost=tpost+1,article=article+1,topic=topic+1";
	} elseif($type == 'reply'){
		$rt['tid']      = $tid;
		$rt['postdate'] = $t_date;
		$rt['lastpost'] = $timestamp;
		$author         = $windid;
		$subject  = $atc_title ? substrs($atc_title,26) : 'Re:'.addslashes(substrs($replytitle,26));
		$topicadd = ",tpost=tpost+1,article=article+1";
		$fupadd   = "tpost=tpost+1,article=article+1";
	} else{
		$rt = $db->get_one("SELECT tid,author,postdate,subject,lastpost,lastposter FROM pw_threads WHERE fid='$fid' ORDER BY lastpost DESC LIMIT 0,1");

		if($rt['postdate'] == $rt['lastpost']){
			$subject = addslashes(substrs($rt['subject'],26));
			$author  = $rt['author'];
		}else{
			$subject = 'Re:'.addslashes(substrs($rt['subject'],26));
			$author  = $rt['lastposter'];
		}
		$topicadd=$fupadd="";
	}
	$GLOBALS['anonymous'] && $author = $GLOBALS['db_anonymousname'];

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
	global $db,$gp_uploadmoney,$creditset,$db_creditset,$db_upgrade,$db_hour,$ifupload,$groupid,$windid,$winduid,$winddb,$timestamp,$top_post,$fatherid,$fid,$tid,$tdtime,$db_autochange,$db_tcheck,$atc_content;

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
		$winddb['monthpost'] ++;
		$winddb['lastpost'] = $timestamp;
		$winddb['postnum'] ++;

		if($top_post){
			$addrvrc  = $creditset['rvrc']['Post'];
			$addmoney = $creditset['money']['Post'];
			$winddb['rvrc']  += $creditset['rvrc']['Post'];
			$winddb['money'] += $creditset['money']['Post'];
			customcredit($winduid,$creditset,'Post');
		} else{
			$addrvrc  = $creditset['rvrc']['Reply'];
			$addmoney = $creditset['money']['Reply'];
			$winddb['rvrc']  += $creditset['rvrc']['Reply'];
			$winddb['money'] += $creditset['money']['Reply'];
			customcredit($winduid,$creditset,'Reply');
		}
		if($ifupload){
			$winddb['money'] -= $gp_uploadmoney;
			$addmoney -= $gp_uploadmoney;
		}
		$usercredit=array(
			'postnum'	=> $winddb['postnum'],
			'digests'	=> $winddb['digests'],
			'rvrc'		=> $winddb['rvrc'],
			'money'		=> $winddb['money'],
			'credit'	=> $winddb['credit'],
			'onlinetime'=> $winddb['onlinetime'],
		);
		$upgradeset = unserialize($db_upgrade);
		foreach($upgradeset as $key=>$val){
			if(is_numeric($key)){
				require_once(R_P.'require/credit.php');
				foreach(GetCredit($winduid) as $key=>$value){
					$usercredit[$key] = $value[1];
				}
				break;
			}
		}
		$memberid = getmemberid(CalculateCredit($usercredit,$upgradeset));
		if($winddb['memberid']!=$memberid){
			$db->update("UPDATE pw_members SET memberid='$memberid' WHERE uid='$winduid'");
		}
		$sqladd = $db_tcheck ? ",postcheck='".tcheck($atc_content)."'" : '';
		$db->update("UPDATE pw_memberdata SET postnum='$winddb[postnum]',rvrc=rvrc+'$addrvrc',money=money+'$addmoney',todaypost='$winddb[todaypost]',monthpost='$winddb[monthpost]',lastpost='$winddb[lastpost]',uploadtime='$winddb[uploadtime]',uploadnum='$winddb[uploadnum]' $sqladd WHERE uid='$winduid'");
	}else{
		Cookie('userlastptime',$timestamp);
	}
}

function getmemberid($nums){
	global $lneed;
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
function CalculateCredit($creditdb,$upgradeset){
	$credit=0;
	foreach($upgradeset as $key=>$val){
		if($creditdb[$key] && $val){
			if($key == 'rvrc'){
				$creditdb[$key] /= 10;
			}elseif($key == 'onlinetime'){
				$creditdb[$key] /= 3600;
			}
			$credit += $creditdb[$key]*$val;
		}
	}
	return (int)$credit;
}
function check_data($type="new"){
	global $atc_usesign,$article,$db_titlemax,$db_postmin,$db_postmax,$atc_money,$foruminfo;

	$atc_title=trim($_POST['atc_title']);
	$atc_content=trim($_POST['atc_content']);
	unset($_POST['atc_content']);

	if(empty($article) && !$atc_title || strlen($atc_title)>$db_titlemax){
		Showmsg('postfunc_subject_limit');
	}
	if(strlen($atc_content)>=$db_postmax || strlen($atc_content)<$db_postmin){
		Showmsg('postfunc_content_limit');
	}
	if($atc_money>1000 || $atc_money<0){
		Showmsg('postfunc_money_limit');
	}
	$atc_title = Char_cv($atc_title);
	$ifconvert=1;

	if ($_POST['atc_convert']=="1"){
		$_POST['atc_autourl'] && $atc_content=autourl($atc_content);
		$atc_content = html_check($atc_content);
		/*
		* [post]、[hide、[sell=位置不能换
		*/
		if(!$foruminfo['allowhide'] || !$GLOBALS['gp_allowhidden']){
			$atc_content=str_replace("[post]","[\tpost]",$atc_content);
		} elseif($_POST['atc_hide']=='1'){
			if($type=='modify'){
				$atc_content=str_replace(array('[post]','[/post]'),"",$atc_content);
			}
			$atc_content="[post]".$atc_content."[/post]";
			$ifconvert=2;
		}
		if(!$foruminfo['allowencode'] || !$GLOBALS['gp_allowencode']){
			$atc_content=str_replace("[hide=","[\thide=",$atc_content);
		} elseif($_POST['atc_requirervrc']=='1'){
			if($type=='modify'){
				$atc_content=preg_replace("/\[hide=(.+?)\]/is","",$atc_content);
				$atc_content=str_replace("[/hide]","",$atc_content);
			}
			$atc_content="[hide=".(int)$_POST['atc_rvrc']."]".$atc_content."[/hide]";
			$ifconvert=2;
		}
		if(!$foruminfo['allowsell'] || !$GLOBALS['gp_allowsell']){
			$atc_content=str_replace("[sell=","[\tsell=",$atc_content);
		} elseif($_POST['atc_requiresell']=='1'){
			if($type=='modify'){
				$atc_content=preg_replace("/\[sell=(.+?)\]/is","",$atc_content);
				$atc_content=str_replace("[/sell]","",$atc_content);
			}
			$atc_content="[sell=".(int)$_POST['atc_money']."]".$atc_content."[/sell]";
			$ifconvert=2;
		}
	}
	if($atc_usesign<2){
		$atc_content=Char_cv($atc_content);
	}else{
		$atc_content=preg_replace(
			array("/<script.*>.*<\/script>/is","/<(([^\"']*?(\".*?\")*?[^\"']*?('.*?')*?[^\"']*?)*?)>/eis"),
			array("","jscv('\\1')"),
			str_replace('.','&#46;',$atc_content)
		);
	}
	if($ifconvert==1){
		$atc_content!=convert($atc_content) && $ifconvert=2;
	}
	return array($atc_title,$atc_content,$ifconvert);
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
function jscv($code){
	$code = preg_replace('/[\s]on[\w]+\s*=\s*("|\').+?\\1/is',"",$code);
	$code = preg_replace("/[\s]on[\w]+\s*=[^\s]*/is","",$code);
	return addcslashes('<'.stripslashes($code).'>',"'");
}
function tcheck($content){
	$content = trim($content);
	$content = strlen($content)>100 ? substr($content,0,100) : $content;
	return substr(md5($content),5,16);
}
function postupload($tmp_name,$filename){
	if(strpos($filename,'..')!==false || strpos($filename,'.php.')!==false || eregi("\.php$",$filename)){
		exit('illegal file type!');
	}
	if(function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name,$filename)){
		@chmod($filename,0777);
		return true;
	}elseif(@copy($tmp_name, $filename)){
		@chmod($filename,0777);
		return true;
	}elseif(is_readable($tmp_name)){
		writeover($filename,readover($tmp_name));
		if(file_exists($filename)){
			@chmod($filename,0777);
			return true;
		}
	}
	return false;
}
function if_uploaded_file($tmp_name){
	if(!$tmp_name || $tmp_name=='none'){
		return false;
	}elseif(function_exists('is_uploaded_file') && !is_uploaded_file($tmp_name) && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))){
		return false;
	}else{
		return true;
	}
}
?>