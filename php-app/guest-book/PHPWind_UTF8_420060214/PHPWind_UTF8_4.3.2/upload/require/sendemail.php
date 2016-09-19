<?php
!function_exists('readover') && exit('Forbidden');
include_once(D_P.'data/bbscache/mail_config.php');

$M_db= new Mailconfig(
	array(
		'method'=> $ml_mailmethod,
		'host'	=> $ml_smtphost,
		'port'	=> $ml_smtpport,
		'auth'	=> $ml_smtpauth,
		'from'	=> $ml_smtpfrom,
		'user'	=> $ml_smtpuser,
		'pass'	=> $ml_smtppass
	)
);

Class Mailconfig {
	var $S_method = 1;
	var $smtp;
	function Mailconfig($smtp=array()){
		$this->S_method = $smtp['method'];
		if ($this->S_method == 1){
			//不用设置
		} elseif ($this->S_method == 2){
			$this->smtp['host'] = $smtp['host'];
			$this->smtp['port'] = $smtp['port'];
			$this->smtp['auth'] = $smtp['auth'];
			$this->smtp['from'] = $smtp['from'];
			$this->smtp['user'] = $smtp['user'];
			$this->smtp['pass'] = $smtp['pass'];
		} elseif ($this->S_method == 3){
			//hacker
		} else {
			//hacker
		}
	}
}
function sendemail($toemail,$subject,$message,$additional){
	global $M_db,$db_bbsname,$rg_name,$db_bbsurl,$winduid,$timestamp,$regpwd,$manager,$db_ceoemail,$fromemail,$pwd_user,$submit,$receiver,$old_title,$fid,$tid,$pwuser;
	!$fromemail && $fromemail = $db_ceoemail;
	require_once GetLang('email');
	$lang[$subject]		&& $subject    = $lang[$subject];
	$lang[$message]		&& $message    = $lang[$message];
	$lang[$additional]	&& $additional = $lang[$additional];
	$subject = Char_cv($subject);
	$message = Char_cv($message);
	if ($M_db->S_method == 1){
		if (@mail($toemail,$subject,$message,$additional)){
			return 1;
		} else{
			return 0;
		}
	} elseif ($M_db->S_method == 2){
		$fp = fsockopen($M_db->smtp['host'], $M_db->smtp['port'], &$errno, &$errstr, 30);
		if($M_db->smtp['auth']) {
			$from = $M_db->smtp['from'];
			fwrite($fp, "EHLO phpwind \r\n");
			fwrite($fp, "AUTH LOGIN \r\n");
			fwrite($fp, base64_encode($M_db->smtp['user'])." \r\n");
			fwrite($fp, base64_encode($M_db->smtp['pass'])." \r\n");
		} else {
			fwrite($fp, "HELO phpwind\r\n");
		}

		$from = preg_replace("/.*\<(.+?)\>.*/", "\\1", $from);
		fwrite($fp, "MAIL FROM: $from\r\n");
		fwrite($fp, "MAIL FROM: <$from>\r\n");
		fwrite($fp, "RCPT TO: $toemail\r\n");
		fwrite($fp, "RCPT TO: <$toemail>\r\n");
		fwrite($fp, "DATA\r\n");

		$msg  = "From: $fromemail\r\n";
		$msg .= "To:$toemail\r\n";
		$msg .= 'Subject: '.str_replace("\n", ' ', $subject)."\r\n\r\n$message\r\n.\r\n"; 
		fwrite($fp, $msg);
		fwrite($fp, "QUIT\r\n");
		fclose($fp);
		return 1;
		/*
		$fp = fsockopen($M_db->smtp['host'], $M_db->smtp['port'], &$errno, &$errstr, 30);
		if($M_db->smtp['auth']) {
			$from = $M_db->smtp['from'];
			fwrite($fp, "EHLO phpwind \r\n");
			fwrite($fp, "AUTH LOGIN \r\n");
			fwrite($fp, base64_encode($M_db->smtp['user'])." \r\n");
			fwrite($fp, base64_encode($M_db->smtp['pass'])." \r\n");
		} else {
			fwrite($fp, "HELO phpwind\r\n");
		}*/

		//$from = preg_replace("/.*\<(.+?)\>.*/", "\\1", $from);
		/*fwrite($fp, "MAIL FROM: $from\r\n");
		fwrite($fp, "MAIL FROM: <$from>\r\n");
		fwrite($fp, "RCPT TO: $toemail\r\n");
		fwrite($fp, "RCPT TO: <$toemail>\r\n");
		fwrite($fp, "DATA\r\n");

		$msg  = "From: $fromemail\r\n";
		$msg .= "To:$toemail\r\n";
		$msg .= 'Subject: '.str_replace("\n", ' ', $subject)."\r\n\r\n$message\r\n.\r\n"; 
		fwrite($fp, $msg);
		fwrite($fp, "QUIT\r\n");
		fclose($fp);
		return 1;
		*/
	} elseif ($M_db->S_method == 3){
		//hacker
	} else {
		//hacker
	}
}
?>