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
define('THIS_SCRIPT', 'register');

require_once('./global.php');

class register {

    function show()
    {
    	global $forums, $_INPUT, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'register' );
		$forums->lang['errorusername'] = sprintf($forums->lang['errorusername'], $bboptions['usernameminlength'], $bboptions['usernamemaxlength']);
		require_once(ROOT_PATH."includes/functions_email.php");
		$this->email = new functions_email();
    	switch($_INPUT['do']) {
    		case 'create':
    			$this->create();
    			break;
    		case 'validate':
    			$this->validate();
    			break;
    		case 'activationaccount':
    			$this->do_form();
    			break;
    		case 'lostpassform':
    			$this->do_form('lostpass');
    			break;
    		case 'changeemail':
    			$this->do_form('newemail');
    			break;
    		case 'lostpassword':
    			$this->lostpassword();
    			break;
    		case 'sendlostpassword':
    			$this->sendlostpassword();
    			break;
    		case 'showimage':
				$simg = $_INPUT['simg'] ? intval($_INPUT['simg']) : 0;
    			$forums->func->showimage($simg);
    			break;
    		case 'resend':
    			$this->reactivationform();
    			break;
    		case 'reactivation':
    			$this->do_reactivation();
    			break;
			case 'checkname':
				$this->checkusername();
				break;
			case 'checkemail':
				$this->checkeusermail();
				break;
			case 'safechange':
				$userid = intval($_INPUT['id']);
				$this->safechange($userid);
				break;
    		default:
    			$this->start_register();
    			break;
    	}
 	}

	function is_inviteduser()
	{
		global $DB,$_INPUT;
		$result = $DB->query_first("SELECT validatecode FROM ".TABLE_PREFIX."inviteduser WHERE validatecode='".trim($_INPUT['invitecode'])."'");
		if ($result['validatecode']) {
			return true;
		} else {
			return false;
		}
	}

	function start_register($errors = "")
    {
    	global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
    	if ( (!$bboptions['allowregistration'] OR !$bboptions['bbactive']) AND !$this->is_inviteduser()) {
    		if ($bboptions['isopeninvite']){
				if (!trim($_INPUT['invitecode'])) {
					$forums->func->standard_error("notallowbutcaninvited");
				} else {
					$forums->func->standard_error("invitedurlcanotuse");
				}
			} else{
				$forums->func->standard_error("notallowregistration");
			}
    	}
		$errors = $forums->lang[$errors];
		if ( $bbuserinfo['id'] ) {
    		$forums->func->standard_redirect();
    	}
		if ($bboptions['reg_ip_time'] > 0) {
			$check_time = time() - $bboptions['reg_ip_time'] * 60;
			$DB->query("SELECT id FROM ".TABLE_PREFIX."user WHERE host != '127.0.0.1' AND host = '".$DB->escape_string(SESSION_HOST)."' AND joindate > {$check_time}");
			if ($DB->num_rows()) {
				$forums->func->standard_error("limit_time_registration", 0, $bboptions['reg_ip_time']);
			}
		}
		$pagetitle = $forums->lang['register']." - ".$bboptions['bbtitle'];
    	if ( !$_INPUT['step']) {
			$nav = array( $forums->lang['register'].' - '.$forums->lang['step1'] );
			$cache = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."setting WHERE varname='registerrule'");
    		$text  = $cache['value'] ? $cache['value'] : $cache['defaultvalue'];
			$text  = str_replace( "\n", "<br />", $text );
			$text  = str_replace( "{bbtitle}", $bboptions['bbtitle'], $text );
			if ( $this->is_inviteduser() ){
				$inviteduser = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."inviteduser  WHERE validatecode='".trim($_INPUT['invitecode'])."'");
				if ( ( $inviteduser['sendtime'] + $inviteduser['expiry']*3600*24 ) < time()) {
					$forums->func->standard_error('overdue');
				}
				$invitemail = $inviteduser['email'];
			}
			require $forums->func->load_template('register');
			exit;
    	} else {
			if ( ! $_INPUT['agree_to_terms'] ) {
				$forums->func->standard_error("notagreeterms", 0, $bboptions['forumindex']);
			}
			if ($bboptions['moderatememberstype']) {
				$show['extra'] = TRUE;
			}
			$nav = array( $forums->lang['register'].' - '.$forums->lang['step2'] );
			$this->clean_validations();
			if ($bboptions['enableantispam']) {
				$regimagehash = md5( uniqid(microtime()) );
				mt_srand ((double) TIMENOW * 1000000);
				$imagestamp = mt_rand(100000,999999);
				$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."antispam
					(regimagehash, imagestamp, host, dateline)
					VALUES ('".$regimagehash."', '".$imagestamp."', '".$DB->escape_string(SESSION_HOST)."', '".TIMENOW."')"
				);
			}
			if ($bboptions['enableantispam'] == 'gd') {
				$show['gd'] = true;
			} else if ($bboptions['enableantispam'] == 'gif') {
				$forums->func->rc = $regimagehash;
				$image = $forums->func->showimage();
				$show['gif'] = true;
			}
			$offset = ( $_INPUT['timezoneoffset'] != "" ) ? $_INPUT['timezoneoffset'] : 8;
			$time_select = "<select name='timezoneoffset'>";
			require_once(ROOT_PATH."includes/functions_user.php");
			$this->fu = new functions_user();
			foreach( $this->fu->fetch_timezone() AS $off => $words ) {
				$time_select .= $off == $offset ? "<option value='$off' selected='selected'>$words</option>\n" : "<option value='$off'>$words</option>\n";
			}
			$time_select .= "</select>";
			$showsignatures = 'checked="checked"';
			$showavatars = 'checked="checked"';
			$usepm = 'checked="checked"';
			$pmpop = 'checked="checked"';
			$allowadmin = 'checked="checked"';
			if ( $_INPUT['do'] == 'creat' ) {
				$showsignatures = $_INPUT['showsignatures'] ? 'checked="checked"' : '';
				$showavatars = $_INPUT['showavatars'] ? 'checked="checked"' : '';
				$usepm = $_INPUT['usepm'] ? 'checked="checked"' : '';
				$pmpop = $_INPUT['pmpop'] ? 'checked="checked"' : '';
				$allowadmin = $_INPUT['allowadmin'] ? 'checked="checked"' : '';
			}
			$usewysiwyg = $_INPUT['usewysiwyg'] ? 'checked="checked"' : '';
			$emailonpm = $_INPUT['emailonpm'] ? 'checked="checked"' : '';
			$hideemail = $_INPUT['hideemail'] ? 'checked="checked"' : '';
			$dst_checked = $_INPUT['dst'] ? 'checked="checked"' : '';
			if ($bboptions['isajax']) {
				$mxajax_request_type = "POST";
				$jsinclude['ajax'] = 1;
			}
			$forums->lang['namefaq'] = sprintf($forums->lang['namefaq'], $bboptions['usernameminlength'], $bboptions['usernamemaxlength']);
			include $forums->func->load_template('register');
			exit;
    	}
   	}

	function create()
	{
		global $forums, $DB, $_INPUT, $bboptions, $_USEROPTIONS;
    	if ( ( !$bboptions['allowregistration'] OR !$bboptions['bbactive'] ) AND !$this->is_inviteduser()){
    		$forums->func->standard_error("notallowregistration");
    	}
		$username = preg_replace("/\s{2,}/", " ", trim(str_replace('|', '&#124;' , $_INPUT['username'])));
		$password = trim($_INPUT['password']);
		$email = $forums->func->strtolower(trim($_INPUT['email']));
		$_INPUT['emailconfirm'] = $forums->func->strtolower(trim($_INPUT['emailconfirm']));
		if ($_INPUT['emailconfirm'] != $email) {
			return $this->start_register('erroremailconfirm');
		}
		$check = $forums->func->unclean_value($username);
		$len_u = $forums->func->strlen($check);
		if (empty($username) OR strstr($check, ';') OR $len_u < $bboptions['usernameminlength'] OR $len_u > $bboptions['usernamemaxlength'] OR (strlen($username) > 60)) {
			return $this->start_register('errorusername');
		}
		if (empty($password) OR ($forums->func->strlen($password) < 3) OR (strlen($password) > 32)) {
			return $this->start_register('passwordfaq');
		}
		if ($_INPUT['passwordconfirm'] != $password) {
			return $this->start_register('errorpassword');
		}
		if (strlen($email) < 6) {
			return $this->start_register('erroremail');
		}
		$email = $forums->func->clean_email($email);
		if ( ! $email ) {
			return $this->start_register('erroremail');
		}
		$checkuser = $DB->query_first("SELECT id, name, email, usergroupid, password, host, salt
				FROM ".TABLE_PREFIX."user
				WHERE LOWER(name)='".$forums->func->strtolower($username)."' OR name='".$username."'");
		if (($checkuser['id']) OR ($username == $forums->lang['guest'])) {
			return $this->start_register('namealreadyexist');
		}
		$DB->query("SELECT email FROM ".TABLE_PREFIX."user WHERE email = '".$email."'");
		if ( $DB->num_rows() != 0 ) {
			$this->start_register('mailalreadyexist');
			return;
		}
		$banfilter = array();
		$DB->query("SELECT * FROM ".TABLE_PREFIX."banfilter WHERE type != 'title'");
		while( $r = $DB->fetch_array() ) {
			$banfilter[ $r['type'] ][] = $r['content'];
		}
		if ( is_array( $banfilter['name'] ) AND count( $banfilter['name'] ) ) {
			foreach ( $banfilter['name'] AS $n ) {
				if ( $n == "" ) {
					continue;
				}
				if ( preg_match( "/".preg_quote($n, '/' )."/i", $username ) ) {
					return $this->start_register('badusername');
				}
			}
		}
		if ( is_array( $banfilter['email'] ) AND count( $banfilter['email'] ) ) {
			foreach ( $banfilter['email'] AS $banemail ) {
				$banemail = preg_replace( "/\*/", '.*' , $banemail );
				if ( preg_match( "/$banemail/", $email ) ) {
					$forums->func->standard_error("bademail");
				}
			}
		}
		if ($bboptions['enableantispam']) {
			if ($_INPUT['regimagehash'] == "") {
				$this->start_register('badimagehash');
				return;
			}
			if ( !$row = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."antispam WHERE regimagehash='".addslashes(trim($_INPUT['regimagehash']))."'") ) {
				return $this->start_register('badimagehash');
			}
			if ( trim( intval($_INPUT['imagestamp']) ) != $row['imagestamp'] ) {
				return $this->start_register('badimagehash');
			}
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."antispam WHERE regimagehash='".addslashes(trim($_INPUT['regimagehash']))."'");
		}
		if (strlen($_INPUT['website']) > 150) {
			return $this->start_register("errorwebsite");
		}
		if ( ($_INPUT['qq']) && (!preg_match( "/^(?:\d+)$/", $_INPUT['qq'] ) ) ) {
			return $this->start_register("errorqq");
		}
		if ( ($_INPUT['icq']) && (!preg_match( "/^(?:\d+)$/", $_INPUT['icq'] ) ) ) {
			return $this->start_register("erroricq");
		}
		if ( ($_INPUT['uc']) && (!preg_match( "/^(?:\d+)$/", $_INPUT['uc'] ) ) ) {
			return $this->start_register("erroruc");
		}
		$usergroupid = 3;
		if ($bboptions['moderatememberstype']) {
			$usergroupid = 1;
		}
		$newusers = $DB->query("SELECT * FROM ".TABLE_PREFIX."setting WHERE varname IN ('newuser_give', 'newuser_pm')");
		if ($DB->num_rows()) {
			while ($newuser = $DB->fetch_array($newusers)) {
				if ( $newuser['varname'] == 'newuser_pm' AND ($newuser['value'] != '' OR $newuser['defaultvalue'] != '') ) {
					$do_send_pm = TRUE;
					$pm_contents = $newuser['value'] ? $newuser['value'] : $newuser['defaultvalue'];
				}
				if ( $newuser['varname'] == 'newuser_give' AND ($newuser['value'] != '' OR $newuser['defaultvalue'] != '') ) {
					$given = $newuser['value'] ? $newuser['value'] : $newuser['defaultvalue'];
					$given = explode("|", $given);
					$to_give_money = $given[0];
					$to_give_reputation = $given[1];
				}
			}
		}

		$salt = $forums->func->generate_user_salt(5);
		$saltpassword = md5(md5($password) . $salt);
		$options['showsignatures'] = $_INPUT['showsignatures'] ? 1 : 0;
		$options['showavatars'] = $_INPUT['showavatars'] ? 1 : 0;
		$options['adminemail'] = $_INPUT['allowadmin'] ? 1 : 0;
		$options['dstonoff'] = $_INPUT['dst'] ? 1 : 0;
		$options['hideemail'] = $_INPUT['hideemail'] ? 1 : 0;
		$options['usepm'] = $_INPUT['usepm'] ? 1 : 0;
		$options['pmpop'] = $_INPUT['pmpop'] ? 1 : 0;
		$options['emailonpm'] = $_INPUT['emailonpm'] ? 1 : 0;
		$options['usewysiwyg'] = $_INPUT['usewysiwyg'] ? 1 : 0;
		$options = $forums->func->convert_array_to_bits($options, $_USEROPTIONS);
		$referrerid = $DB->query_first("SELECT userid FROM ".TABLE_PREFIX."inviteduser  WHERE email='".$email."'");
		$user = array(
			'name' => $username,
			'salt' => $salt,
			'password' => $saltpassword,
			'email' => $email,
			'usergroupid' => $usergroupid,
			'posts' => 0,
			'joindate' => TIMENOW,
			'host' => SESSION_HOST,
			'timezoneoffset' => $_INPUT['timezoneoffset'],
			'gender' => intval($_INPUT['gender']),
			'website' => $_INPUT['website'],
			'qq' => intval($_INPUT['qq']),
			'popo' => $_INPUT['popo'],
			'uc' => intval($_INPUT['uc']),
			'skype' => trim($_INPUT['skype']),
			'icq' => intval($_INPUT['icq']),
			'msn' => $_INPUT['msn'],
			'aim' => $_INPUT['aim'],
			'yahoo' => $_INPUT['yahoo'],
			'referrerid' => intval($referrerid['userid']),
			'forbidpost' => 0,
			'options' => $options,
			'pmtotal' => 0,
			'pmunread' => 0,
			'cash' => intval($to_give_money),
			'reputation' => intval($to_give_reputation),
		);

		$forums->func->fetch_query_sql( $user, 'user' );
		$user['id'] = $DB->insert_id();
		if ($do_send_pm) {
			$_INPUT['title'] = $forums->lang['welcome_register'] . $user['name'];
			$_POST['post'] = $pm_contents;
			$_INPUT['username'] = $user['name'];
			require_once( ROOT_PATH.'includes/functions_private.php' );
			$pm = new functions_private();
			$_INPUT['noredirect'] = 1;
			$pm->cookie_mxeditor = "wysiwyg";
			$pm->sendpm();
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."inviteduser SET regsterid='".$user['id']."' WHERE email='".$user['email']."'");
		$activationkey = md5($forums->func->make_password() . TIMENOW);
		if ( ($bboptions['moderatememberstype'] == 'user') OR ($bboptions['moderatememberstype'] == 'admin') ) {
			$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."useractivation
				(useractivationid, userid, usergroupid, tempgroup, dateline, type, host)
				VALUES
				('".$activationkey."', ".$user['id'].", 3, 1, ".TIMENOW.", 2, '".$DB->escape_string(SESSION_HOST)."')");
			if ( $bboptions['moderatememberstype'] == 'user' ) {
				$message = $this->email->fetch_email_activationaccount( array(
					'link' => $bboptions['bburl']."/register.php?do=validate&amp;u=".urlencode($user['id'])."&amp;a=".urlencode($activationkey),
					'name' => $user['name'],
					'linkpage' => $bboptions['bburl']."/register.php?do=activationaccount",
					'id' => $userid,
					'code' => $activationkey)
				);
				$this->email->build_message( $message );
				$forums->lang['registerinfo'] = sprintf( $forums->lang['registerinfo'], $bboptions['bbtitle'] );
				$this->email->subject = $forums->lang['registerinfo'];
				$this->email->to = $user['email'];
				$this->email->send_mail();
				$forums->lang['mustactivation'] = sprintf( $forums->lang['mustactivation'], $user['name'], $user['email'] );
				$forums->func->redirect_screen( $forums->lang['mustactivation'] );
			} else if ( $bboptions['moderatememberstype'] == 'admin' ) {
				$forums->lang['adminactivation'] = sprintf( $forums->lang['adminactivation'], $user['name'] );
				$forums->func->redirect_screen( $forums->lang['adminactivation'] );
			}
		} else {
			$forums->func->check_cache('stats');
			$forums->cache['stats']['newusername'] = $user['name'];
			$forums->cache['stats']['newuserid'] = $user['id'];
			$forums->cache['stats']['numbermembers']++;
			$forums->func->update_cache(array('name' => 'stats'));
			$forums->func->set_cookie("userid", $user['id'] , 86400);
			$forums->func->set_cookie("password", $user['password'], 86400);
			$forums->func->standard_redirect('login.php?'.$forums->sessionurl.'do=autologin&amp;logintype=fromreg');
		}
	}

	function reactivationform($errors="")
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
    	$username = $bbuserinfo['id'] == "" ? '' : $bbuserinfo['name'];
		$pagetitle = $forums->lang['resend']." - ".$bboptions['bbtitle'];
		$nav = array( $forums->lang['resend'] );
		include $forums->func->load_template('register_reactivation');
		exit;
	}

	function do_reactivation()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$username = trim($_INPUT['username']);
		if ( !$username ) {
			$this->reactivationform('errorusername');
			return;
		}
		if ( ! $user = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."user WHERE LOWER(name)='".$forums->func->strtolower($username)."' OR name='".$username."'" ) ) {
			$this->reactivationform('namenotexist');
			return;
		}
		if ( ! $activation = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."useractivation WHERE userid='".$user['id']."'" ) ) {
			$this->reactivationform('namenotexist');
			return;
		}
		if ($activation['type'] == 1) {
			$message = $this->email->fetch_email_lostpassword( array(
				'name' => $user['name'],
				'link' => $bboptions['bburl']."/register.php?do=lostpassform&u=".$user['id']."&a=".$activation['useractivationid'],
				'linkpage' => $bboptions['bburl']."/register.php?do=lostpassform",
				'id' => $user['id'],
				'code' => $activation['useractivationid'],
				'host'   => SESSION_HOST)
			);
			$this->email->build_message( $message );
			$forums->lang['resetpassword'] = sprintf( $forums->lang['resetpassword'], $bboptions['bbtitle'] );
			$this->email->subject = $forums->lang['resetpassword'];
			$this->email->to = $user['email'];
			$this->email->send_mail();
		} else if ( $activation['type'] == 2 ) {
			$message = $this->email->fetch_email_activationaccount( array(
				'link' => $bboptions['bburl']."/register.php?do=validate&amp;u=".urlencode($user['id'])."&amp;a=".urlencode($val['useractivationid']),
				'name' => $user['name'],
				'linkpage' => $bboptions['bburl']."/register.php?do=activationaccount",
				'id' => $userid,
				'code' => $activation['useractivationid'],)
			);
			$this->email->build_message( $message );
			$forums->lang['registerinfo'] = sprintf( $forums->lang['registerinfo'], $bboptions['bbtitle'] );
			$this->email->subject = $forums->lang['registerinfo'];
			$this->email->to = $user['email'];
			$this->email->send_mail();
		} else if ($activation['type'] == 3) {
			$message = $this->email->fetch_email_changeemail( array(
				'link' => $bboptions['bburl']."/register.php?do=validate&amp;type=newemail&amp;u=".urlencode($user['id'])."&amp;a=".urlencode( $activation['useractivationid'] ),
				'name' => $user['name'],
				'linkpage' => $bboptions['bburl']."/register.php?do=changeemail",
				'id' => $userid,
				'code' => $activation['useractivationid'],)
			);
			$this->email->build_message( $message );
			$forums->lang['changeemail'] = sprintf( $forums->lang['changeemail'], $bboptions['bbtitle'] );
			$this->email->subject = $forums->lang['changeemail'];
			$this->email->to = $email;
			$this->email->send_mail();
		} else {
			$this->reactivationform('namenotexist');
			return;
		}
		$forums->func->redirect_screen( $forums->lang['hassendmail'] );
	}

	function lostpassword($errors="")
	{
		global $forums, $DB, $bboptions, $bbuserinfo;
		if ($bboptions['enableantispam']) {
			$passtime = TIMENOW - (60*60*6);
			$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."antispam WHERE dateline < ".$passtime."" );
			$regimagehash = md5( uniqid(microtime()) );
			mt_srand ((double) TIMENOW * 1000000);
			$imagestamp = mt_rand(100000,999999);
			$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."antispam
										(regimagehash, imagestamp, host, dateline)
									VALUES
										('".$regimagehash."', '".$imagestamp."', '".$DB->escape_string(SESSION_HOST)."', '".TIMENOW."')"
								);
		}
		$errors = $forums->lang[$errors];
		if ($bboptions['enableantispam'] == 'gd') {
			$show['gd'] = true;
		} else if ($bboptions['enableantispam'] == 'gif') {
			$forums->func->rc = $regimagehash;
			$image = $forums->func->showimage();
			$show['gif'] = true;
		}
		$pagetitle = $forums->lang['lostpassword']." - ".$bboptions['bbtitle'];
    	$nav = array( $forums->lang['lostpassword'] );
		include $forums->func->load_template('lostpassword');
		exit;
    }

    function sendlostpassword()
    {
    	global $forums, $DB, $_INPUT, $bboptions;
    	if ($bboptions['enableantispam']) {
			if ($_INPUT['regimagehash'] == "") {
				return $this->lostpassword('badimagehash');
			}
			if ( ! $row = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."antispam WHERE regimagehash='".trim(addslashes($_INPUT['regimagehash']))."'") ) {
				return $this->lostpassword('badimagehash');
			}
			if ( trim( intval($_INPUT['imagestamp']) ) != $row['imagestamp'] ) {
				return $this->lostpassword('badimagehash');
			}
		}
		$username = trim($_INPUT['username']);
		if ($username == "") {
			return $this->lostpassword('errorusername');
		}
		if ( ! $user = $DB->query_first( "SELECT id, name, email, usergroupid FROM ".TABLE_PREFIX."user WHERE LOWER(name)='".$forums->func->strtolower($username)."' OR name='".$username."'" ) ) {
			return $this->lostpassword('namenotexist');
		} else {
			if ($_INPUT['safechange']) {
				$this->safechange($user['id']);
			} else {
				$activationkey = md5( $forums->func->make_password() . TIMENOW );
				$DB->shutdown_query("INSERT INTO ".TABLE_PREFIX."useractivation
											(useractivationid, userid, usergroupid, tempgroup, dateline, type, host)
										VALUES
											('".$activationkey."', '".$user['id']."', '".$user['usergroupid']."', '".$user['usergroupid']."', '".TIME_NOW."', 1, '".$DB->escape_string(SESSION_HOST)."')"
									);
				$message = $this->email->fetch_email_lostpassword( array(
													'name'         => $user['name'],
													'link'     => $bboptions['bburl']."/register.php?do=lostpassform&amp;u=".$user['id']."&amp;a=".$activationkey,
													'linkpage'     => $bboptions['bburl']."/register.php?do=lostpassform",
													'id'           => $user['id'],
													'code'         => $activationkey,
													'host'   => SESSION_HOST
												  )
											);
				$this->email->build_message( $message );
				$forums->lang['resetpassword'] = sprintf( $forums->lang['resetpassword'], $bboptions['bbtitle'] );
				$this->email->subject = $forums->lang['resetpassword'];
				$this->email->to = $user['email'];
				$this->email->send_mail();
				$forums->func->redirect_screen( $forums->lang['hassendpass'] );
			}
		}
    }

	function safechange($userid=0)
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$safe = $DB->query_first( "SELECT answer, question FROM ".TABLE_PREFIX."userextra WHERE id = {$userid}" );
		if (!$safe['question'] OR !$safe['answer']) {
			$forums->func->standard_error("cannotusesafe");
		}
		if ($_INPUT['update']) {
			if ($safe['answer'] != $_INPUT['answer']) {
				$forums->func->standard_error("answererror");
			}
			$password = trim($_INPUT['password']);
			if (empty($password) OR (strlen($password) < 3) OR (strlen($password) > 32)) {
				$errors = $forums->lang['passwordfaq'];
			}
			if ($_INPUT['passwordconfirm'] != $password) {
				$errors = $forums->lang['errorpassword'];
			}
			if (!$errors) {
				$salt = $forums->func->generate_user_salt(5);
				$saltpassword = md5(md5($password) . $salt);
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET password='".$saltpassword."', salt='".addslashes($salt)."' WHERE id={$userid}");
				$forums->func->redirect_screen( $forums->lang['hasresetpass'] );
			}
		}
		$pagetitle = $forums->lang['lostpassword']." - ".$bboptions['bbtitle'];
		$nav = array( $forums->lang['lostpassword'] );
		include $forums->func->load_template('lostpassword_safe');
		exit;
	}

	function validate()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$userid = intval(trim(rawurldecode($_INPUT['u'])));
		$activationkey = trim(rawurldecode($_INPUT['a']));
		$type = trim($_INPUT['type']);
		$username = trim($_INPUT['name']);
		if ($type == "") {
			$type = 'reg';
		}
		if (! preg_match( "/^(?:[\d\w]){32}$/", $activationkey ) OR ! preg_match( "/^(?:\d){1,}$/", $userid ) ) {
			$forums->func->standard_error("cannotvalidate");
		}
		if( $username ) {
			$user = $DB->query_first( "SELECT id,name,password,salt,email FROM ".TABLE_PREFIX."user WHERE LOWER(name)='".$forums->func->strtolower($username)."' OR name='".$username."'" );
		} else {
			$user = $DB->query_first( "SELECT id,name,password,salt,email FROM ".TABLE_PREFIX."user WHERE id='".$userid."'" );
		}
		if ( ! $user['id'] ) {
			$forums->func->standard_error("cannotvalidate");
		}
		$useractivation = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."useractivation WHERE userid='".$user['id']."'" );
		if ( ! $useractivation['userid'] ) {
			$forums->func->standard_error("cannotvalidate");
		}
		if (($useractivation['type'] == 2) && ($bboptions['moderatememberstype'] == "admin")) {
			$forums->func->standard_error("requireadminmoderate");
		}
		if ($useractivation['useractivationid'] != $activationkey) {
			$forums->func->standard_error("badimagehash");
		} else {
			if ($type == 'reg') {
				if ( $useractivation['type'] != 2 ) {
					$forums->func->standard_error("cannotvalidate");
				}
				if (empty($useractivation['usergroupid'])) {
					$useractivation['usergroupid'] = 3;
				}
				$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET usergroupid='".intval($useractivation['usergroupid'])."' WHERE id='".intval($user['id'])."'" );
				$forums->func->check_cache('stats');
				$forums->cache['stats']['newusername'] = $user['name'];
				$forums->cache['stats']['newuserid'] = $user['id'];
				$forums->cache['stats']['numbermembers']++;
				$forums->func->update_cache(array('name' => 'stats'));
				$forums->func->set_cookie("userid", $user['id'], 86400);
				$forums->func->set_cookie("password", $user['password'], 86400);
				$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."useractivation WHERE useractivationid='".$useractivation['useractivationid']."' OR (userid='".$user['id']."' AND type=2)" );
				$this->clean_validations();
				$forums->func->standard_redirect('login.php?'.$forums->sessionurl.'do=autologin&amp;logintype=fromreg');
			} else if ($type == 'lostpass') {
				if ($useractivation['type'] != 1) {
					$forums->func->standard_error("notfindpassword");
				}
				if ( $_INPUT['pass1'] == "" OR $_INPUT['pass2'] == "" ) {
					$forums->func->standard_error("plzinputallform");
				}
				$pass1 = trim($_INPUT['pass1']);
				$pass2 = trim($_INPUT['pass2']);
				if ( strlen($pass1) < 3 ) {
					$forums->func->standard_error("passwordtooshort");
				}
				if ( $pass1 != $pass2 ) {
					$forums->func->standard_error("errorpassword");
				}
				$newpassword = md5($pass1);
				if ( ! $user['email'] OR ! $newpassword ) {
					return FALSE;
				}
				$newpassword = md5($newpassword . $user['salt']);
				$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET password='".$newpassword."' WHERE id='".intval($user['id'])."'" );
				$forums->func->set_cookie("userid", $user['id'], 86400);
				$forums->func->set_cookie("password", $newpassword, 86400);
				$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."useractivation WHERE useractivationid='".$useractivation['useractivationid']."' OR (userid={$user['id']} AND type=1)");
				$this->clean_validations();
				$forums->func->standard_redirect('login.php?'.$forums->sessionurl.'do=autologin&amp;logintype=frompass');
			} else if ($type == 'newemail') {
				if ( $useractivation['type'] != 3 ) {
					$forums->func->standard_error("validatetooold");
				}
				if (empty($useractivation['usergroupid'])) {
					$useractivation['usergroupid'] = 3;
				}
				$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET usergroupid='".intval($useractivation['usergroupid'])."' WHERE id='".intval($user['id'])."'" );
				$forums->func->set_cookie("userid", $user['id'], 86400);
				$forums->func->set_cookie("password", $user['password'], 86400);
				$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."useractivation WHERE useractivationid='".$useractivation['useractivationid']."' OR (userid={$user['id']} AND type=3)");
				$this->clean_validations();
				$forums->func->standard_redirect('login.php?'.$forums->sessionurl.'do=autologin&amp;logintype=fromemail');
			}
		}
	}

	function do_form($type='reg')
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$pagetitle = $forums->lang['activationform']." - ".$bboptions['bbtitle'];
		$nav = array( $forums->lang['activationform'] );
		if ( $type == 'lostpass' ) {
			if ( $_INPUT['u'] AND $_INPUT['a'] ) {
				$userid      = intval(trim(rawurldecode($_INPUT['u'])));
				$activationkey = trim(rawurldecode($_INPUT['a']));
				if (! preg_match( "/^(?:[\d\w]){32}$/", $activationkey ) OR ! preg_match( "/^(?:\d){1,}$/", $userid ) ) {
					$forums->func->standard_error("cannotvalidate");
				}
				if ( ! $user = $DB->query_first("SELECT * FROM  ".TABLE_PREFIX."user WHERE id=$userid ") ) {
					$forums->func->standard_error("cannotvalidate");
				}
				$validate = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."useractivation WHERE userid=$userid AND useractivationid='$activationkey' AND type=1" );
				if (!$validate['userid']) {
					$forums->func->standard_error("cannotvalidate");
				}
				$show['havekey'] = TRUE;
			} else {
				$show['validate'] = TRUE;
			}
		} else {
			$show['validate'] = TRUE;
		}
		include $forums->func->load_template('register_validate');
		exit;
	}

	function clean_validations()
	{
		global $forums, $DB, $bboptions;
		$userids = array();
		$activationids = array();
		if ( intval($bboptions['removemoderate']) > 0 ) {
			$less_than = TIMENOW - $bboptions['removemoderate'] * 86400;
			$DB->query("SELECT ua.useractivationid, ua.userid, u.posts FROM ".TABLE_PREFIX."useractivation ua LEFT JOIN ".TABLE_PREFIX."user u ON (ua.userid=u.id) WHERE ua.dateline < ".$less_than." AND ua.type != 1");
			while( $i = $DB->fetch_array() ) {
				if ( intval($i['posts']) < 1 ) {
					$userids[] = $i['userid'];
					$activationids[] = "'".$i['useractivationid']."'";
				}
			}
			if ( count($userids) > 0 ) {
				$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."user WHERE id IN(".implode(",",$userids).")");
				$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."useractivation WHERE useractivationid IN(".implode(",",$activationids).")");
			}
		}
	}

	function checkusername()
	{
		global $forums, $bboptions, $DB, $_INPUT;
		$_INPUT['name'] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $_INPUT['name']);
		$username = preg_replace( "/\s{2,}/", " ", trim( str_replace( '|', '&#124;' , $_INPUT['name'] ) ) );
		$check = $forums->func->unclean_value($username);
		$len_u = $forums->func->strlen($check);
		if (empty($username) OR strstr($check, ';') OR ($len_u < $bboptions['usernameminlength']) OR ($len_u > $bboptions['usernamemaxlength']) OR (strlen($username) > 60)) {
			echo $forums->lang['ajaxerror1'];
			exit;
		}
		$DB->query("SELECT content FROM ".TABLE_PREFIX."banfilter WHERE type = 'name'");
		while($r = $DB->fetch_array()) {
			if ($r['content']) {
				if (preg_match( "/".preg_quote($r['content'], '/' )."/i", $username)) {
					echo $forums->lang['ajaxerror1'];
					exit;
				}
			}
		}
		$checkuser = $DB->query_first("SELECT id FROM ".TABLE_PREFIX."user WHERE LOWER(name)='".$forums->func->strtolower($username)."' OR name='".$username."'");
		if (($checkuser['id']) OR ($username == $forums->lang['guest'])) {
			echo $forums->lang['ajaxexist'];
			exit;
		}else{
            echo $forums->lang['ajaxv'];
		}
	}

	function checkeusermail()
	{
		global $forums, $DB, $_INPUT;
		$_INPUT['email'] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $_INPUT['email']);
		$email = $forums->func->strtolower( trim($_INPUT['email']) );
		if (strlen($email) < 6) {
			echo $forums->lang['ajaxerroremail'];
			exit;
		}
		$email = $forums->func->clean_email($email);
		if ( ! $email ) {
			echo $forums->lang['ajaxerroremail'];
			exit;
		}
		$DB->query("SELECT content FROM ".TABLE_PREFIX."banfilter WHERE type = 'email'");
		while ($r = $DB->fetch_array()) {
			if ($r['content']) {
				$banemail = preg_replace("/\*/", '.*', $r['content']);
				if (preg_match("/$banemail/", $email)) {
					echo $forums->lang['ajaxerroremail'];
					exit;
				}
			}
		}
		$DB->query("SELECT email FROM ".TABLE_PREFIX."user WHERE email = '".$email."'");
		if ($DB->num_rows() != 0) {
			echo $forums->lang['ajaxmailexist'];
			exit;
		} else {
            echo $forums->lang['ajaxv'];
		}
	}
}

$output = new register();
$output->show();

?>