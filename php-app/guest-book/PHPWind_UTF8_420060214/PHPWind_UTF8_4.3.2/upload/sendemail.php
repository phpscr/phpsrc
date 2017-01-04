<?php
require_once('global.php');
require_once(R_P.'require/header.php');

if(!$ml_mailifopen){
	Showmsg('sendmail_closed');
}
if ($groupid=='guest'){
	Showmsg('not_login');
} elseif ($uid && !is_numeric($uid)){
	$errorname='';
	Showmsg('user_not_exists');
}
if ($action != 'mailto' && $action != 'tofriend'){
	$action = 'mailto';
}
if ($uid || $username){
	if ($uid){
		$sql="uid='$uid'";
	} else {
		$sql="username='".addslashes($username)."'";
	}
	$userdb=$db->get_one("SELECT uid,username,email,publicmail,receivemail FROM pw_members WHERE $sql");
	if (!$userdb['receivemail'] && $groupid!='3' && $groupid!='4'){
		Showmsg('sendeamil_refused');
	}
}
list(,,,,$othergd)=explode("\t",$db_gdcheck);
if (empty($step)){
	if ($uid || $username){
		if ($uid){
			$sql="uid='$uid'";
		} else {
			$sql="username='".addslashes($username)."'";
		}
		$userdb=$db->get_one("SELECT uid,username,email,publicmail,receivemail FROM pw_members WHERE $sql");
		if (!$userdb['receivemail'] && $groupid!='3' && $groupid!='4' && $windid!=$manager){
			Showmsg('sendeamil_refused');
		}
		$to_mail=$userdb['email'];
		$to_user=$userdb['username'];
		if ($userdb['publicmail']!=1 && $groupid!='3' && $groupid!='4' && $windid!=$manager){
			$hiddenmail=1;
		} else {
			$hiddenmail=0;
		}
	} else {
		$to_mail="";
		$to_user="";
	}
	$atc_name=$ifdisabled='';
	if ($action=='mailto'){
		$ifdisabled='disabled';
	} elseif ($action=='tofriend'){
		!is_numeric($tid) && showmsg('illegal_tid');
		$atcinfo=$db->get_one("SELECT subject,author FROM pw_threads WHERE tid='$tid'");
		$atc_name=$atcinfo['subject'];
	}
	require_once(PrintEot('sendmail'));footer();
} elseif ($_POST['step']=="2"){
	$othergd && GdConfirm($gdcode);
	if ($action=='mailto'){
		$sendtoemail = $userdb['email'];
	}
	if (empty($subject)){
		Showmsg('sendeamil_subject_limit');
	}
	if (empty($atc_content) || strlen($atc_content)<=20){
		Showmsg('sendeamil_content_limit');
	} elseif (!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$sendtoemail) || !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$fromemail)){
		Showmsg('illegal_email');
	}
	if ($timestamp-GetCookie('lastwrite')<=60){//$gp_postpertime
		Showmsg('sendeamil_limit');
	}
	Cookie('lastwrite',$timestamp);
	require_once(R_P.'require/sendemail.php');
	if (sendemail($sendtoemail,$subject,$atc_content,$winddb['email'])) {
		refreshto('index.php','mail_success');
	} else {
		Showmsg('mail_failed');
	}
}
?>