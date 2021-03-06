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
    	global $forums, $_INPUT;
		//$forums->lang = $forums->func->load_lang($forums->lang, 'register' );
		require_once(ROOT_PATH."includes/functions_email.php");
		$this->email = new functions_email();
    	switch($_INPUT['do']) {
    		case 'create':
    			$this->create();
    			break;
    		default:
    			$this->start_register();
    			break;
    	}
 	}

	function start_register($errors = "")
    {
    	global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
    	if ( !$bboptions['allowregistration'] OR !$bboptions['bbactive'] ) {
    		$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
			$contents = convert($forums->lang['notallowregistration']);
			include $forums->func->load_template('wap_info');
			exit;
    	}
		$message = $forums->lang[$errors];
		if ( $bbuserinfo['id'] ) {
			redirect("index.php?{$forums->sessionurl}");
    	}
		$pagetitle = $forums->lang['register'];

		if ($bboptions['moderatememberstype']) {
			$show['extra'] = TRUE;
		}
		$forums->lang['register'] = convert($forums->lang['register']);
		$forums->lang['username'] = convert($forums->lang['username']);
		$forums->lang['password'] = convert($forums->lang['password']);
		$forums->lang['sex'] = convert($forums->lang['sex']);
		$forums->lang['male'] = convert($forums->lang['male']);
		$forums->lang['female'] = convert($forums->lang['female']);
		$forums->lang['email'] = convert($forums->lang['email']);
		$forums->lang['confirmpass'] = convert($forums->lang['confirmpass']);

		include $forums->func->load_template('wap_register');
		exit;
   	}

	function create()
	{
		global $forums, $DB, $_INPUT, $bboptions, $_USEROPTIONS;
		if (!$bboptions['allowregistration'] OR !$bboptions['bbactive']) {
    		$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
			$contents = convert($forums->lang['notallowregistration']);
			include $forums->func->load_template('wap_info');
			exit;
    	}
		$username = preg_replace( "/\s{2,}/", " ", trim( str_replace( '|', '&#124;' , $_INPUT['username'] ) ) );
		$password = trim($_INPUT['password']);
		$email    = $forums->func->strtolower( trim($_INPUT['email']) );
		$check = $forums->func->unclean_value($username);
		$len_u = $forums->func->strlen($check);
		if ((empty($username)) OR strstr($check, ';') OR $len_u < $bboptions['usernameminlength'] OR $len_u > $bboptions['usernamemaxlength'] OR strlen($username) > 60) {
			return $this->start_register('errorusername');
		}
		if (empty($password) OR ($forums->func->strlen($password) < 3) OR (strlen($password) > 32)) {
			return $this->start_register('passwordtooshort');
		}
		if (trim($_INPUT['confirmpass']) != $password) {
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
					return $this->start_register('errorusername');
				}
			}
		}
		if ( is_array( $banfilter['email'] ) AND count( $banfilter['email'] ) ) {
			foreach ( $banfilter['email'] AS $banemail ) {
				$banemail = preg_replace( "/\*/", '.*' , $banemail );
				if ( preg_match( "/$banemail/", $email ) ) {
					$forums->func->standard_error("mailalreadyexist");
				}
			}
		}
		$usergroupid = 3;
		if ($bboptions['moderatememberstype']) {
			$usergroupid = 1;
		}
		$salt = $forums->func->generate_user_salt(5);
		$saltpassword = md5(md5($password) . $salt);
		$options['showsignatures'] = 1;
		$options['showavatars'] = 1;
		$options['adminemail'] = 1;
		$options['dstonoff'] = 0;
		$options['hideemail'] = 0;
		$options['usepm'] = 1;
		$options['pmpop'] = 1;
		$options['emailonpm'] = 0;
		$options['usewysiwyg'] = 0;
		$options = $forums->func->convert_array_to_bits($options, $_USEROPTIONS);
		$user = array('name'			=> $username,
						 'salt'					=> $salt,
						 'password'			=> $saltpassword,
						 'email'				=> $email,
						 'usergroupid'		=> $usergroupid,
						 'posts'				=> 0,
						 'joindate'			=> TIMENOW,
						 'host'				=> SESSION_HOST,
						 'timezoneoffset'	=> $bboptions['timezoneoffset'],
						 'gender'			=> $_INPUT['gender'],
						 'website'			=> $_INPUT['website'],
						 'qq'					=> $_INPUT['qq'],
						 'icq'					=> $_INPUT['icq'],
						 'msn'				=> $_INPUT['msn'],
						 'aim'					=> $_INPUT['aim'],
						 'yahoo'				=> $_INPUT['yahoo'],
						 'forbidpost'		=> 0,
						 'options'			=> $options,
						 'pmtotal'			=> 0,
						 'pmunread'		=> 0,
					   );

		$forums->func->fetch_query_sql( $user, 'user' );
		$user['id'] = $DB->insert_id();

		$activationkey = md5( $forums->func->make_password() . TIMENOW );
		if ( ($bboptions['moderatememberstype'] == 'user') OR ($bboptions['moderatememberstype'] == 'admin') ) {
			$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."useractivation
									(useractivationid, userid, usergroupid, tempgroup, dateline, type, host)
								VALUES
									('".$activationkey."', ".$user['id'].", 3, 1, ".TIMENOW.", 2, '".$DB->escape_string(SESSION_HOST)."')"
							);
			if ( $bboptions['moderatememberstype'] == 'user' ) {
				$message = $this->email->fetch_email_activationaccount( array(
													'link'     => $bboptions['bburl']."/register.php?do=validate&u=".urlencode($user['id'])."&a=".urlencode($activationkey),
													'name'         => $user['name'],
													'linkpage'     => $bboptions['bburl']."/register.php?do=activationaccount",
													'id'           => $userid,
													'code'         => $activationkey,
												  )
											);
				$this->email->build_message( $message );
				$forums->lang = $forums->func->load_lang($forums->lang, 'register' );
				$forums->lang['registerinfo'] = sprintf( $forums->lang['registerinfo'], $bboptions['bbtitle'] );
				$this->email->subject = $forums->lang['registerinfo'];
				$this->email->to = $user['email'];
				$this->email->send_mail();
				$forums->lang['mustactivation'] = sprintf( $forums->lang['mustactivation'], $user['name'], $user['email'] );
				redirect("index.php?{$forums->sessionurl}", $forums->lang['mustactivation']);
			} else if ( $bboptions['moderatememberstype'] == 'admin' ) {
				$forums->lang['adminactivation'] = sprintf( $forums->lang['adminactivation'], $user['name'] );
				redirect("index.php?{$forums->sessionurl}", $forums->lang['adminactivation']);
			}
		} else {
			$forums->func->check_cache('stats');
			$forums->cache['stats']['newusername'] = $user['name'];
			$forums->cache['stats']['newuserid'] = $user['id'];
			$forums->cache['stats']['numbermembers'] += 1;
			$forums->func->update_cache(array('name' => 'stats'));
			redirect("index.php?{$forums->sessionurl}&amp;bbuid={$user['id']}&amp;bbpwd={$saltpassword}", $forums->lang['adminactivation']);
		}
	}
}

$output = new register();
$output->show();

?>