<?php
#**************************************************************************#
#   MolyX2
#   ------------------------------------------------------
#   copyright (c) 2004-2006 HOGE Software.
#   official forum : http://molyx.com
#   license : MolyX License, http://molyx.com/license
#   MolyX2 is free software. You can redistribute this file and/or modify
#   it under the terms of MolyX License. If you do not accept the Terms
#   and Conditions stated in MolyX License, please do not redistribute
#   this file.Please visit http://molyx.com/license periodically to review
#   the Terms and Conditions, or contact HOGE Software.
#**************************************************************************#
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'login');
require_once('./global.php');

class login {

    function show($message='')
    {
    	global $_INPUT, $forums, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'login' );
    	if ($bboptions['forcelogin'] == 1) {
    		$this->message = $forums->lang['forcelogin'];
    	}
    	switch($_INPUT['do'])
		{
    		case 'login':
    			$this->dologin();
    			break;
    		case 'logout':
	  			$this->dologout();
    			break;
    		case 'autologin':
    			$this->autologin();
    			break;
    		default:
  				$this->loginpage();
    			break;
    	}
 	}

    function loginpage()
    {
        global $forums, $_INPUT, $bboptions, $bbuserinfo;
		if ($bbuserinfo['id']) {
			$forums->func->standard_redirect();
		}
        if ($this->message != "") {
			$message = $this->message;
			$show['errors'] = TRUE;
		}
		$forums->func->check_cache('style');
		if ( $bboptions['allowselectstyles'] AND count($forums->cache['style']) > 1 ) {
			$show['style'] = TRUE;
			$select_style = "";
			foreach( $forums->cache['style'] AS $id => $style ) {
				$select_style .= "<option value='$id'>";
				$parentlist = explode(',', $style['parentlist']);
				$style_prefix = "";
				for($i = 1, $n = count($parentlist); $i < $n; $i++) {
					$style_prefix .= "--";
				}
				$select_style .= $style_prefix . $style['title'];
				$select_style .="</option>\n";
			}
		}
		$referer = $forums->url;
		$nav = array( $forums->lang['dologin'] );
		$pagetitle = $forums->lang['dologin']." - ".$bboptions['bbtitle'];
		include $forums->func->load_template('login');
		exit;
    }

    function dologin()
    {
    	global $DB, $_INPUT, $forums, $bboptions, $_USEROPTIONS;
		$username = trim( $_INPUT['username'] );
		$password = trim( $_INPUT['password'] );
		if ($username == "" OR $password == "" ) {
    		$forums->func->standard_error("plzinputallform");
    	}
		if ($_INPUT['logintype'] == 2) {
			$where = "id=".intval($username)."";
		} elseif ($_INPUT['logintype'] == 3) {
			if (strlen($username) < 6) {
				$forums->func->standard_error('erroremail');
			}
			$username = $forums->func->clean_email($username);
			if ( ! $username ) {
				$forums->func->standard_error('erroremail');
			}
			$where = "email='".$forums->func->strtolower($username)."'";
		} else {
			if ($_INPUT['charset'] == 'gb') {
				$username = $forums->func->convert_encoding($username, 'GBK', 'UTF-8');
			} else if ($_INPUT['charset'] == 'big5') {
				$username = $forums->func->convert_encoding($username, 'BIG5', 'UTF-8');
			}
			if (strlen($check_name) > 32) {
				$forums->func->standard_error("nametoolong");
			}
			$username = addslashes(str_replace( '|', '&#124;', $username));
			$where = "LOWER(name)='".$forums->func->strtolower($username)."' OR  name='".$username."'";
		}
		$check_password = preg_replace("/&#([0-9]+);/", "-", $password );
		if (strlen($check_password) > 32) {
			$forums->func->standard_error("passwordtoolong");
		}
		$password = md5( $password );
		$this->verify_strike_status($username);
		$user = $DB->query_first("SELECT id, name, email, usergroupid, password, host, options, salt from ".TABLE_PREFIX."user WHERE $where");
		if ( empty($user['id']) OR ($user['id'] == "") ) {
			$this->message = $forums->lang['nouser'];
			$this->exec_strike_user($username);
		}
		$old_pwd = $user['password'];
		if (strlen($old_pwd) == 16) {
			$check_pwd = substr($password,8,16);
			if($old_pwd == $check_pwd){
				$mysaltpassword = md5($password . $user['salt']);
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET password='".$mysaltpassword."' WHERE id = ".$user['id']);
			} else {
				$this->message = $forums->lang['errorpassword'];
				$this->exec_strike_user($username);
			}
		} else if ( $user['password'] != md5($password . $user['salt']) ) {
			$this->message = $forums->lang['errorpassword'];
			$this->exec_strike_user($username);
		}
		if ( $user['host'] == "" OR $user['host'] == '127.0.0.1' ) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET host='".$DB->escape_string(SESSION_HOST)."' WHERE id = ".$user['id']);
		}
		foreach($_USEROPTIONS AS $optionname => $optionval) {
			$user["$optionname"] = $user['options'] & $optionval ? 1 : 0;
		}
		$sessionid = "";
		if ( $forums->func->get_cookie('sessionid') ) {
			$sessionid = $forums->func->get_cookie('sessionid');
		} else if ( $_INPUT['s'] ) {
			$sessionid = $_INPUT['s'];
		}
		$invisible = $_INPUT['invisible'] ? 1 : 0;
		if ($sessionid) {
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."session SET username='".$user['name']."', userid=".$user['id'].", lastactivity=".TIMENOW.", usergroupid=".$user['usergroupid'].", invisible=".$invisible."  WHERE sessionhash='".$sessionid."'" );
		} else {
			$sessionid = md5( uniqid(microtime()) );
			$DB->shutdown_query("INSERT INTO ".TABLE_PREFIX."session  (sessionhash, username, userid, lastactivity, usergroupid, host, useragent, invisible) VALUES ('".$sessionid."', '".$user['name']."', '".$user['id']."', ".TIMENOW.", '".$user['usergroupid']."', '".$DB->escape_string(SESSION_HOST)."', '".$DB->escape_string(USER_AGENT)."', '".$invisible."')");
		}
		$bbuserinfo = $user;
		$forums->sessionid = $sessionid;
    	$url = '';
		if ($_INPUT['referer'] AND THIS_SCRIPT != 'register' AND strpos($_INPUT['referer'], 'logout') === false) {
			$url = preg_replace( "!s=(\w){32}!", "", $_INPUT['referer'] );
		}
		$style = '';
		if ( $bboptions['allowselectstyles'] ) {
			$forums->func->check_cache('style');
			$styleid = intval($_INPUT['style']);
			$style = $forums->cache['style'][$styleid]['userselect'] ? 'style='.$styleid.', ' : '';
		}
		$bbuserinfo['options'] = $forums->func->convert_array_to_bits(array_merge($bbuserinfo , array('invisible' => $_INPUT['invisible'], 'loggedin' => 1)), $_USEROPTIONS);
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET ".$style."options=".$bbuserinfo['options']." WHERE id='".$bbuserinfo['id']."'");
		$DB->shutdown_query("DELETE FROM ".TABLE_PREFIX."useractivation WHERE userid='".$bbuserinfo['id']."' AND type=1");
		$DB->shutdown_query("DELETE FROM ".TABLE_PREFIX."strikes WHERE strikeip = '".$DB->escape_string(SESSION_HOST)."' AND username='".addslashes($username)."'");
		if ($_POST['cookiedate']) {
			$forums->func->set_cookie("userid", $user['id'], $_POST['cookiedate']);
			$forums->func->set_cookie("password", $user['password'], $_POST['cookiedate']);
			$forums->func->set_cookie("sessionid", $forums->sessionid, $_POST['cookiedate']);
		}
		if ( $_POST['return'] != "" ) {
			$doreturn = rawurldecode($_POST['return']);
			if ( preg_match( "#^http://#", $doreturn ) ) {
				$forums->func->standard_redirect($doreturn);
			}
		}
		$text = $forums->lang['welcomeback'].': '.$bbuserinfo['name'];
		$forums->func->redirect_screen( $text, $url );
	}

	function dologout()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions, $_USEROPTIONS;
		$bbuserinfo['options'] = $forums->func->convert_array_to_bits(array_merge($bbuserinfo , array('invisible' => $bbuserinfo['invisible'], 'loggedin' => 0)), $_USEROPTIONS);
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."session SET username='', userid='0', invisible='0' WHERE sessionhash='". $forums->sessionid ."'");
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET options=".$bbuserinfo['options'].", lastvisit=".TIMENOW.", lastactivity=".TIMENOW." WHERE id=".$bbuserinfo['id']);
		$forums->func->set_cookie('password' , '-1');
 		$forums->func->set_cookie('userid' , '-1');
 		$forums->func->set_cookie('sessionid', '-1');
 		$forums->func->set_cookie('threadread', '-1');
 		$forums->func->set_cookie('invisible' , '-1');
 		$forums->func->set_cookie('forumread', '-1');
		if (is_array($_COOKIE)) {
 			foreach( $_COOKIE AS $cookie => $value ) {
 				if (preg_match( "/^(".$bboptions['cookieprefix'].".*$)/i", $cookie, $match)) {
 					$forums->func->set_cookie( str_replace( $bboptions['cookieprefix'], "", $match[0] ) , '', -1 );
 				}
 			}
 		}
		if ( $_INPUT['return'] != "" ) {
			$doreturn = rawurldecode($_INPUT['return']);
			if ( preg_match( "#^http://#", $doreturn ) ) {
				$forums->func->standard_redirect($doreturn);
			}
		}
		$text = $forums->lang['alreadylogout'];
		$forums->func->redirect_screen( $text, "" );
	}

	function autologin()
 	{
 		global $forums, $DB, $bboptions, $bbuserinfo, $_INPUT;
 		if ( ! $bbuserinfo['id'] ) {
			$userid = intval($forums->func->get_cookie('userid'));
			$password = $forums->func->get_cookie('password');
			If ( $userid AND $password ) {
				$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE id='$userid' AND password='$password'");
				if ( $user = $DB->fetch_array() ) {
					$bbuserinfo = $user;
					$forums->func->load_style();
					$forums->sessionid = "";
					$forums->func->set_cookie('sessionid', '-1');
				}
			}
 		}
 		$login_success  = $forums->lang['loginsuccess'];
 		$login_failed = $forums->lang['loginfailed'];
 		$show = FALSE;
		switch ($_INPUT['logintype'])
		{
    		case 'fromreg':
				$login_success  = $forums->lang['regsuccess'];
				$login_failed = $forums->lang['regfailed'];
				$show = TRUE;
    			break;
    		case 'fromemail':
				$login_success  = $forums->lang['mailsuccess'];
				$login_failed = $forums->lang['mailfailed'];
				$show = TRUE;
    			break;
    		case 'frompass':
				$login_success  = $forums->lang['passsuccess'];
				$login_failed = $forums->lang['passfailed'];
				$show = TRUE;
    			break;
		}
 		if ($bbuserinfo['id']) {
 			if ($show) {
 				$forums->func->redirect_screen( $login_success );
 			} else {
 				$forums->func->standard_redirect();
 			}
 		} else {
 			if ($show) {
 				$forums->func->redirect_screen( $login_failed, 'login.php' );
 			} else {
 				$forums->func->standard_redirect('login.php?'.$forums->sessionurl);
 			}
 		}
 	}

	function verify_strike_status($username = '')
	{
		global $DB, $_INPUT, $forums;
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."strikes WHERE striketime < " . (TIMENOW - 3600));
		$strikes = $DB->query_first("SELECT COUNT(*) AS strikes, MAX(striketime) AS lasttime FROM ".TABLE_PREFIX."strikes WHERE strikeip = '" . $DB->escape_string(SESSION_HOST) . "' AND username = '".addslashes($username) . "'");
		$this->strikes = $strikes['strikes'];
		if ($this->strikes >= 5 AND $strikes['lasttime'] > TIMENOW - 900) {
			$this->message = $forums->lang['strikefailed1'];
			return $this->loginpage();
		}
		$maxstrikes = $DB->query_first("SELECT COUNT(*) AS strikes FROM ".TABLE_PREFIX."strikes WHERE strikeip = '".$DB->escape_string(SESSION_HOST)."'");
		if ($this->strikes >= 30 AND $strikes['lasttime'] > TIMENOW - 1800) {
			$this->message = $forums->lang['strikefailed2'];
			return $this->loginpage();
		}
	}

	function exec_strike_user($username = '')
	{
		global $DB, $forums;
		$DB->shutdown_query("INSERT INTO " . TABLE_PREFIX . "strikes
		(striketime, strikeip, username)
			VALUES
			(".TIMENOW.", '".$DB->escape_string(SESSION_HOST)."', '" . addslashes($username) . "')
		");
		$this->strikes++;
		$times = $this->strikes;
		$forums->lang['striketimes'] = sprintf( $forums->lang['striketimes'], $times );
		$this->message .= $forums->lang['striketimes'];
		return $this->loginpage();
	}
}

$output = new login();
$output->show();

?>