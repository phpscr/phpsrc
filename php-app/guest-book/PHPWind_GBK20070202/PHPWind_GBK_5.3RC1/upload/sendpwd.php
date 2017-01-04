<?php
require_once('global.php');
require_once(R_P.'require/header.php');
!$action && $action='sendpwd';
if($action=='sendpwd'){
	list(,,,,$othergd)=explode("\t",$db_gdcheck);
	if(!$_POST['step']){
		require_once(PrintEot('sendpwd'));footer();
	} elseif($_POST['step']==2){
		$othergd && GdConfirm($gdcode);
		$pwuser = Char_cv($pwuser);
		$userarray = $db->get_one("SELECT password,email,regdate FROM pw_members WHERE username='$pwuser'");
		if($userarray['email'] != $email){
			Showmsg('email_error',1);
		}
		if($userarray){
			if($timestamp-GetCookie('lastwrite')<=60){
				$gp_postpertime = 60;
				Showmsg('sendpwd_limit',1);
			}
			Cookie('lastwrite',$timestamp);
			$send_email = $userarray['email'];
			$submit     = $userarray['regdate'];
			$submit    .= md5(substr($userarray['password'],10));
			$pwuser   = rawurlencode($pwuser);
			require_once(R_P.'require/sendemail.php');
			if(sendemail($send_email,'email_sendpwd_subject','email_sendpwd_content','email_additional')){
				Showmsg('mail_success',1);
			} else{
				Showmsg('mail_failed',1);
			}
		} else{
			$errorname = $pwuser;
			Showmsg('user_not_exists',1);
		}
	}
} elseif($action=='getback'){
	if($pwuser==$manager){
		Showmsg('undefined_action',1);
	}
	$pwuser = Char_cv($pwuser);
	$detail = $db->get_one("SELECT password,regdate FROM pw_members WHERE username='$pwuser'");
	if($detail){
		$is_right  = $detail['regdate'];
		$is_right .= md5(substr($detail['password'],10));
		if($submit==$is_right){
			if(!$jop){
				require_once PrintEot('getpwd');footer();
			} elseif($jop==2){
				if($new_pwd!=$pwdreapt){
					Showmsg('password_confirm',1);
				} else{
					$new_pwd = stripslashes($new_pwd);
					$new_pwd = str_replace("\t","",$new_pwd);
					$new_pwd = str_replace("\r","",$new_pwd);
					$new_pwd = str_replace("\n","",$new_pwd);
					$new_pwd = md5($new_pwd);
					$db->update("UPDATE pw_members SET password='$new_pwd' WHERE username='$pwuser'");
					Showmsg('password_change_success',1);
				}
			}
		} else {
			Showmsg('password_confirm_fail',1);
		}
	} else {
		$errorname = $pwuser;
		Showmsg('user_not_exists',1);
	}
}
?>