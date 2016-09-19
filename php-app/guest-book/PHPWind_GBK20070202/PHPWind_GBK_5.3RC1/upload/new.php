<?php

/*
**************************************************************************
*
* PHPWind 首页调用
*
* $color   : 标题后增加显示信息颜色，例如作者，时间，点击数
* $prefix  : 标题前字符，可以用图片 : <img src="http://bbs.phpwind.net/pre.gif" border="0">
*
**************************************************************************
*/

$color   = '#666666';
$prefix  = array('<li>','◇','·','○','●','- ','□-');

/*****************************************************/
define('D_P',__FILE__ ? dirname(__FILE__).'/' : './');
define('R_P',D_P);

error_reporting(0);
set_magic_quotes_runtime(0);
if (!get_magic_quotes_gpc()){
     Add_S($_GET);
}
if($_SERVER['HTTP_X_FORWARDED_FOR']){
	$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}elseif($_SERVER['HTTP_CLIENT_IP']){
	$onlineip = $_SERVER['HTTP_CLIENT_IP'];
}else{
	$onlineip = $_SERVER['REMOTE_ADDR'];
}
$timestamp = time();
require_once(R_P.'require/defend.php');
$db_cvtime != 0 && $timestamp += $db_cvtime*60;
$per = $db_jsper;

$REFERER = parse_url($_SERVER['HTTP_REFERER']);

include(GetLang('other'));
if(!$db_jsifopen){
	exit("document.write(\"$lang[js_close]\");");
}
if ($db_bindurl && $_SERVER['HTTP_REFERER'] && strpos(",$db_bindurl,",",$REFERER[host],") === false){
	exit("document.write(\"$lang[bindurl]\");");
}

if ($action == 'forum'){
	include_once(D_P.'data/bbscache/forum_cache.php');
	$pre       = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
	$fids      = explode('_',$fidin);
	$foruminfo = '';
	foreach ($fids as $key => $value){
		if (is_numeric($value)){
			$foruminfo .= "$pre <a href='$db_bbsurl/thread.php?fid=$value' target='_blank'>".$forum[$value]['name']."</a><br>";
		}
	}
	echo "document.write(\"$foruminfo\");";
} elseif ($action == 'notice'){
	$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$pre.(int)$num.(int)$length);
	if (time() - @filemtime($cachefile) >= $per){
		$pre      = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
		$num      = is_numeric($num) ? $num : 5;
		$length	  = is_numeric($length) ? $length : 35;

		require_once(D_P.'data/sql_config.php');
		require_once(R_P.'require/db_'.$database.'.php');

		$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect,$manager_pwd);

		$noticedb = '';
		$query = $db->query("SELECT aid,subject,startdate FROM pw_announce WHERE fid='-1' ORDER BY vieworder,startdate DESC LIMIT $num");
		while($rt = $db->fetch_array($query)){
			$startdate = $date ? '('.gmdate('Y-m-d H:i',$rt['startdate']+8*3600).')' : '';
			$noticedb .= "$pre <a href='$db_bbsurl/notice.php?fid=-1#$rt[aid]' target='_blank'>".substrs(preg_replace("/\<(.+?)\>/eis",'',$rt['subject']),$length)."</a>$startdate<br>";
		}
		$noticedb = str_replace('"','\"',$noticedb);
		$noticedb = "document.write(\"$noticedb\");";
		echo $noticedb;
		writeover($cachefile,$noticedb);
	} else {
		@readfile($cachefile);
	}
} elseif ($action == 'info'){

	$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$pre.(int)$member.(int)$article.(int)$yesterday.(int)$online);
	if (time() - @filemtime($cachefile) >= $per){
		$pre = is_numeric($pre) ? $prefix[$pre] : $prefix[0];

		require_once(D_P.'data/sql_config.php');
		require_once Pcv(R_P.'require/db_'.$database.'.php');

		$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect,$manager_pwd);

		$t		= array('hours'=>gmdate('G',$timestamp+$db_timedf*3600));
		$tdtime	= (floor($timestamp/3600)-$t['hours'])*3600;
		$bbsinfo = $db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
		if($bbsinfo['tdtcontrol']!=$tdtime){
			if($db_hostweb == 1){
				$rt=$db->get_one("SELECT SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
				$db->update("UPDATE pw_bbsinfo SET yposts='$rt[tposts]',tdtcontrol='$tdtime' WHERE id=1");
				$db->update("UPDATE pw_forumdata SET tpost=0 WHERE tpost<>'0'");
			}
		}
		$info = '';
		if($member){
			$info   .= "$pre $lang[js_totalmember]:$bbsinfo[totalmember]<br>$pre $lang[js_newmember]:$bbsinfo[newmember]<br>";
		}
		if($article){
			$rs = $db->get_one("SELECT SUM(fd.topic) as topic,SUM(fd.subtopic) as subtopic,SUM(fd.article) as article,SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
			$topic   = $rs['topic'] + $rs['subtopic'];
			$article = $rs['article'];
			$info   .= "$pre $lang[js_topic]:$topic<br>$pre $lang[js_article]:$article<br>";
		}
		if($yesterday){
			if(!$article){
				$rs = $db->get_one("SELECT SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
			}
			if(!$member){
				$bbsinfo = $db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
			}
			$tposts = $rs['tposts'];
			$info  .= "$pre $lang[js_today]:$tposts<br>$pre $lang[js_yesterday]:$bbsinfo[yposts]<br>$pre $lang[js_highday]:$bbsinfo[hposts]<br>";
		}
		if($online){
			if(!$member && !$yesterday){
				$bbsinfo = $db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
			}
			@include_once(D_P.'data/bbscache/olcache.php');
			$usertotal  = $guestinbbs+$userinbbs;
			$higholtime = gmdate('Y-m-d',$bbsinfo['higholtime']+8*3600);
			$info .= "$pre $lang[js_online]:$usertotal<br>$pre $lang[js_onlinemen]:$userinbbs<br>$pre $lang[js_onlineguest]:$guestinbbs<br>$pre $lang[js_highonline]:$bbsinfo[higholnum]<br>$pre $lang[js_happen]:$higholtime";
		}
		$info = "document.write(\"$info\");";
		echo $info ;
		writeover($cachefile,$info);
	} else {
		@readfile($cachefile);
	}
} elseif ($action == 'member'){
	$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$num.(int)$pre.(int)$order);
	if (time() - @filemtime($cachefile) >= $per){
		$num	  = is_numeric($num) ? $num : 10;
		$pre	  = is_numeric($pre) ? $prefix[$pre] : $prefix[0];

		$orderway = array(
			'1'   => 'uid',
			'2'   => 'postnum',
			'3'   => 'digests',
			'4'   => 'rvrc',
			'5'   => 'money',
			'6'   => 'credit'
		);
		$orderby  = is_numeric($order) ? $orderway[$order] : $orderway[1];

		require_once(D_P.'data/sql_config.php');
		require_once Pcv(R_P.'require/db_'.$database.'.php');
		include_once(D_P.'data/bbscache/forum_cache.php');

		$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect,$manager_pwd);

		$query = $db->query("SELECT m.uid,m.username,md.$orderby FROM pw_memberdata md LEFT JOIN pw_members m USING(uid) ORDER BY md.$orderby DESC LIMIT $num");
		while($rt = $db->fetch_array($query)){
			if($orderby != 'uid'){
				$orderby == 'rvrc' && $rt[$orderby] = floor($rt[$orderby] /= 10);
				$useradd = "($rt[$orderby])";
			} else{
				$useradd = '';
			}
			$userdb = "$pre <a href='$db_bbsurl/profile.php?action=show&uid=$rt[uid]' target='_blank'>$rt[username]</a> $useradd";
			$newlist .= "document.write(\"$userdb<br>\");\n";
		}
		echo $newlist;
		writeover($cachefile,$newlist);
	} else {
		@readfile($cachefile);
	}
} elseif ($action == 'article'){
	$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$num.(int)$length.$fidin.$fidout.(int)$postdate.(int)$author.(int)$fname.(int)$hits.(int)$replies.(int)$pre.(int)$digest.(int)$order);
	if (time() - @filemtime($cachefile) >= $per){
		$num	  = is_numeric($num) ? $num : 10;
		$length	  = is_numeric($length) ? $length : 35;
		$pre	  = is_numeric($pre) ? $prefix[$pre] : $prefix[0];

		$orderway = array(
			'1'   => 'lastpost',
			'2'   => 'postdate',
			'3'   => 'replies',
			'4'   => 'hits'
		);
		$orderby  = is_numeric($order) ? $orderway[$order] : $orderway[1];

		require_once(D_P.'data/sql_config.php');
		require_once Pcv(R_P.'require/db_'.$database.'.php');
		include_once(D_P.'data/bbscache/forum_cache.php');

		$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect,$manager_pwd);


		$sqladd = "ifcheck=1";
		$fidoff=$ext='';
		$query = $db->query("SELECT fid FROM pw_forums WHERE password!='' OR allowvisit!='' OR f_type='hidden'");
		while ($rt=$db->fetch_array($query)){
			$fidoff .= $ext.$rt['fid'];
			!$ext && $ext = ',';
		}
		$fidoff && $sqladd .= " AND fid NOT IN($fidoff)";
		if ($fidin){
			$sqladd .= " AND fid IN ('".str_replace('_', "','", $fidin)."')";
		} elseif ($fidout){
			$sqladd .= " AND fid NOT IN ('".str_replace('_', "','", $fidout)."')";
		}
		$digest && $sqladd .= " AND digest>'0'";

		$query   = $db->query("SELECT tid,fid,author,authorid,subject,postdate,hits,replies FROM pw_threads WHERE $sqladd ORDER BY $orderby DESC LIMIT 0, $num");
		while ($threads = $db->fetch_array($query)){
			$threads['subject'] = substrs($threads['subject'], $length);
			$article = "$pre <a href='$db_bbsurl/read.php?tid=$threads[tid]' target='_blank'>$threads[subject]</a> ";
			if ($postdate){
				$article .= " <font color='$color'>(".date("Y-m-d H:i",$threads['postdate']).')</font>';
			}
			if ($author){
				$article .= " <a href='$db_bbsurl/profile.php?action=show&uid=$threads[authorid]' target='_blank'><font color='$color'>($threads[author])</font></a>";
			}
			if ($replies){
				$article .= " <font color='$color'>($lang[js_replies]：$threads[replies])</font></a>";
			}
			if ($hits){
				$article .= " <font color='$color'>($lang[js_hits]：$threads[hits])</font></a>";
			}
			if ($fname){
				$article .= " <a href='$db_bbsurl/thread.php?fid=$threads[fid]' target='_blank'><font color='$color'>(".$forum[$threads['fid']]['name'].")</font></a>";
			}
			$newlist .= "document.write(\"$article<br>\");\n";
		}
		echo $newlist;
		writeover($cachefile,$newlist);
	} else {
		@readfile($cachefile);
	}
}
function Cookie($ck_Var,$ck_Value,$ck_Time = 'F'){
	global $db_ckpath,$db_ckdomain,$timestamp;
	$ck_Time = $ck_Time == 'F' ? $timestamp + 31536000 : ($ck_Value == '' && $ck_Time == 0 ? $timestamp - 31536000 : $ck_Time);
	$S		 = $_SERVER['SERVER_PORT'] == '443' ? 1:0;
	!$db_ckpath && $db_ckpath = '/';
	setCookie(CookiePre().'_'.$ck_Var,$ck_Value,$ck_Time,$db_ckpath,$db_ckdomain,$S);
}
function GetCookie($Var){
    return $_COOKIE[CookiePre().'_'.$Var];
}
function CookiePre(){
	return substr(md5($GLOBALS['db_hash']),0,5);
}
function P_unlink($filename){
	strpos($filename,'..')!==false && exit('Forbidden');
	@unlink($filename);
}
function readover($filename,$method="rb"){
	strpos($filename,'..')!==false && exit('Forbidden');
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}
function GdConfirm(){
}
function writeover($filename,$data,$method="rb+",$iflock=1,$check=1,$chmod=1){
	$check && strpos($filename,'..')!==false && exit('Forbidden');
	touch($filename);
	$handle=fopen($filename,$method);
	if($iflock){
		flock($handle,LOCK_EX);
	}
	fwrite($handle,$data);
	if($method=="rb+") ftruncate($handle,strlen($data));
	fclose($handle);
	$chmod && @chmod($filename,0777);
}
function substrs($content,$length) {
	if(strlen($content)>$length){
		$num=0;
		for($i=0;$i<$length-3;$i++) {
			if(ord($content[$i])>0xa0)$num++;
		}
		$num%2==1 ? $content=substr($content,0,$length-4):$content=substr($content,0,$length-3);
		$content.=' ...';
	}
	return $content;
}
function Add_S(&$array){
     foreach($array as $key=>$value){
           if(!is_array($value)){
                 $array[$key]=addslashes($value);
           }else{
                 Add_S($array[$key]);
           }
     }
}
function GetLang($lang,$EXT="php"){
	global $db_defaultstyle;
	if(file_exists(R_P."data/style/$db_defaultstyle.php") && strpos($db_defaultstyle,'..')===false){
		@include Pcv(R_P."data/style/$db_defaultstyle.php");
	}else{
		@include(R_P."data/style/wind.php");
	}
	$path=R_P."template/$tplpath/lang_$lang.$EXT";
	!file_exists($path) && $path=R_P."template/wind/lang_$lang.$EXT";
	return $path;
}
function Pcv($filename,$ifcheck=1){
	strpos($filename,'http://')!==false && exit('Forbidden');
	$ifcheck && strpos($filename,'..')!==false && exit('Forbidden');
	return $filename;
}
?>