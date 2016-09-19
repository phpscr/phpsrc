<?php
require_once('global.php');

$uid = (int)$uid;
$men=$db->get_one("SELECT username,password,email,regdate FROM pw_members WHERE uid='$uid' AND yz>'2'");
!$men && Showmsg('remail_error',1);

if(!$step){
	@extract($men);
	require_once(R_P.'require/header.php');
	require_once PrintEot('remail');footer();
}elseif($_POST['step']==3){
	$men['password']!=md5($password) && Showmsg('password_error',1);
	$rg_email!=$men['email'] && Showmsg('email_error',1);
	if($to_email && !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$to_email)) {
		Showmsg('illegal_email'); 
	}
	!$to_email && $to_email=$men['email'];
	$rg_name=$men['username'];
	$winduid=$uid;
	$timestamp=$men['regdate'];
	$regpwd=$password;

	require_once(R_P.'require/sendemail.php');
	if(sendemail($to_email,'email_check_subject','email_check_content','email_additional')){
		Showmsg('remail_success',1);
	} else{
		Showmsg('reg_email_fail',1);
	}
}
?>