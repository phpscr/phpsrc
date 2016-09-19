<?php
require_once('global.php');
require_once(R_P.'require/header.php');

if ($groupid=='guest'){
	Showmsg('not_login');
}elseif($uid && !is_numeric($uid)){
	$errorname='';
	Showmsg('user_not_exists');
}
!$action && $action='mailto';

list(,,,,$othergd)=explode("\t",$db_gdcheck);

if($action == 'mailto'){

	if($uid || $username){
		if($uid){
			$sql="uid='$uid'";
		} else {
			$sql="username='".addslashes($username)."'";
		}
		$userdb=$db->get_one("SELECT uid,username,email,publicmail,receivemail FROM pw_members WHERE $sql");
	}else{
		$userdb='';
	}
	!$userdb && Showmsg('undefined_action');

	if(!$_POST['step']){
		if(!$userdb['receivemail'] && $groupid!='3' && $groupid!='4'){
			Showmsg('sendeamil_refused');
		}
		$to_mail=$userdb['email'];
		$to_user=$userdb['username'];

		if ($userdb['publicmail']!=1 && $groupid!='3' && $groupid!='4'){
			$hiddenmail=1;
		} else {
			$hiddenmail=0;
		}

		require_once(PrintEot('sendmail'));footer();
	} else {
		$othergd && GdConfirm($gdcode);

		if(!$userdb['receivemail'] && $groupid!='3' && $groupid!='4'){
			Showmsg('sendeamil_refused');
		}
		$sendtoemail = $userdb['email'];

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
		if (sendemail($sendtoemail,$subject,$atc_content,'email_additional')) {
			refreshto('index.php','mail_success');
		} else {
			Showmsg('mail_failed');
		}
	}
}elseif($action=='tofriend'){
	if(!$_POST['step']){
		!is_numeric($tid) && showmsg('illegal_tid');
		$atcinfo=$db->get_one("SELECT subject,author,anonymous FROM pw_threads WHERE tid='$tid'");
		$atcinfo['anonymous'] && $atcinfo['author']=$db_anonymousname;
		$atc_name=$atcinfo['subject'];

		require_once(PrintEot('sendmail'));footer();
	}elseif($_POST['step']==1){
		$othergd && GdConfirm($gdcode);
		require_once(R_P.'require/msg.php');
		if($sendtoname){
			$userdb=$db->get_one("SELECT username FROM pw_members WHERE username='$sendtoname'");
			$errorname = $sendtoname;
		}else{
			$userdb='';
		}
		!$userdb && Showmsg('user_not_exists');
		if(!$subject || !$atc_content){
			Showmsg('tofriend_msgerror');
		}

		$msgdb = array(
			$userdb['username'],
			$winduid,
			$subject,
			$timestamp,
			$atc_content,
			'N',
			$windid,
		);
		writenewmsg($msgdb,1);
		refreshto("read.php?tid=$tid",'operate_success');
	}elseif($_POST['step']==2){
		$othergd && GdConfirm($gdcode);
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
		if(sendemail($sendtoemail,$subject,$atc_content,'email_additional')) {
			refreshto('index.php','mail_success');
		} else {
			Showmsg('mail_failed');
		}
	}
}
?>