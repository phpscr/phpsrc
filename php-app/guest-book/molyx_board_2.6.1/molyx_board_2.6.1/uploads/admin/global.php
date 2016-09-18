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
define('ROOT_PATH', './../');
define ('IN_ACP', 1);
require_once(ROOT_PATH.'includes/init.php');
set_magic_quotes_runtime(0);
if (function_exists("set_time_limit") == 1) {
  @set_time_limit(0);
}
@header('Content-Type:text/html; charset=UTF-8');
require(ROOT_PATH."includes/functions.php");
$forums->func = new functions();
$bboptions['language'] = 'en-us';

@require(ROOT_PATH.'includes/config.php');
$db_file = $config['dbtype'] ? trim($config['dbtype']) : "mysql";
require(ROOT_PATH.'includes/db/db_'.$db_file.'.php');
define('TABLE_PREFIX', $config['tableprefix']);
$DB = new db;
$DB->server = $config['servername'];
$DB->database = $config['dbname'];
$DB->user = $config['dbusername'];
$DB->password = $config['dbpassword'];
$DB->pconnect = $config['usepconnect'];
$DB->dbcharset = $config['dbcharset'];
$DB->use_shutdown = 1;
$DB->connect();
define ('SUPERADMIN', $config['superadmin']);

$_INPUT = $forums->func->init_variable();
if (empty($_INPUT['url'])) {
	$url = REFERRER;
} else {
	if ($_INPUT['url'] == REFERRER) {
		$url = 'index.php';
	} else {
		$url = &$_INPUT['url'];
	}
}
if ($url == SCRIPTPATH OR empty($url)) {
	$url = 'index.php';
}
$forums->url = xss_clean($url);
if ( USE_SHUTDOWN ) {
	$ROOT_PATH = realpath(ROOT_PATH);
	register_shutdown_function(array(&$forums->func, 'do_shutdown'));
}
$forums->func->check_cache('settings');
$forums->func->check_cache('cron');
$bboptions = $forums->cache['settings'];
$bboptions['bburl'] = $config['forum_url'] ? $config['forum_url'] : $bboptions['bburl'];
$forums->func->check_lang();

$forums->lang = $forums->func->load_lang($forums->lang, 'admin' );
require ROOT_PATH."includes/functions_forum.php";
$forums->forum  = new functions_forum();
require ROOT_PATH."includes/sessions.php";
$session = new session();

$forums->imageurl = "../images/controlpanel";
$bboptions['uploadurl'] = $bboptions['uploadurl'] ? $bboptions['uploadurl'] : $bboptions['bburl'].'/data/uploads';
$bboptions['uploadfolder'] = $bboptions['uploadfolder'] ? $bboptions['uploadfolder'] : ROOT_PATH.'data/uploads';
require_once(ROOT_PATH."includes/adminfunctions.php");
$forums->admin = new admin_functions();
$session_validated = 0;
$this_session      = array();
$validate = FALSE;
if ( defined('IN_SQL') AND $fp = @fopen( ROOT_PATH.'data/dbbackup/unlock.key', 'r' ) ) {
	$validate = TRUE;
	fclose( $fp );
} elseif ($_INPUT['login'] != 'yes') {
	if ( !defined('IN_SQL') AND $fp = @fopen( ROOT_PATH.'data/dbbackup/unlock.key', 'r' ) ) {
		$forums->admin->print_cp_error($forums->lang['unlockfileexist']);
		fclose( $fp );
	}
	if ( ! $_INPUT['s'] ) {
		$forums->admin->print_cp_login();
	} else {
		$DB->query("SELECT * FROM ".TABLE_PREFIX."adminsession WHERE sessionhash='".$_INPUT['s']."'");
		$row = $DB->fetch_array();
		if ($row['sessionhash'] == "" OR $row['userid'] == "") {
			$forums->admin->print_cp_login();
		} else {
			$user = $DB->query_first( "SELECT u.*, g.* FROM ".TABLE_PREFIX."user u, ".TABLE_PREFIX."usergroup g WHERE id=".intval($row['userid'])." AND u.usergroupid=g.usergroupid" );
			$session->user = $user;
			$session->build_group_permissions();
			$bbuserinfo = $session->user;
			if ($bbuserinfo['id'] == "") {
				$forums->admin->print_cp_login($forums->lang['usernotexist']);
			} else {
				if ($row['password'] != $bbuserinfo['password']) {
					$forums->admin->print_cp_login($forums->lang['passwordwrong']);
				} else {
					$admin = explode(',', SUPERADMIN);
					if ($bbuserinfo['cancontrolpanel'] != 1 AND !in_array($bbuserinfo['id'], $admin)) {
						$forums->admin->print_cp_login($forums->lang['noadmincpperms']);
					} else {
						$session_validated = 1;
						$this_session = $row;
					}
				}
			}
		}
	}
} else {
	$username = trim($_INPUT['username']);
	$username = addslashes(str_replace( '|', '&#124;', $username));
	if ( empty($username) ) {
		$forums->admin->print_cp_login($forums->lang['requireusername']);
	}
	if ( empty($_INPUT['password']) ) {
		$forums->admin->print_cp_login($forums->lang['requirepassword']);
	}
	$user = $DB->query_first( "SELECT u.*, g.*
				FROM ".TABLE_PREFIX."user u, ".TABLE_PREFIX."usergroup g
				WHERE (LOWER(u.name)='".$forums->func->strtolower($username)."' OR u.name='".$username."') AND u.usergroupid=g.usergroupid" );
	$session->user = $user;
	$session->build_group_permissions();
	$user = $session->user;
	if ( empty($user['id']) ) {
		$forums->admin->print_cp_login($forums->lang['usernotexist']);
	}
	$password = md5( $_INPUT['password'] );
	if ( $user['password'] != md5($password . $user['salt']) ) {
		$forums->admin->print_cp_login($forums->lang['passwordwrong']);
	} else {
		$admin = explode(',', SUPERADMIN);
		if ($user['cancontrolpanel'] != 1 AND !in_array($bbuserinfo['id'], $admin)) {
			$forums->admin->print_cp_login($forums->lang['noadmincpperms']);
		} else {
			$forums->sessionid = md5( uniqid( microtime() ) );
			$forums->func->fetch_query_sql( array (
																'sessionhash'	=> $forums->sessionid,
																'host'				=> SESSION_HOST,
																'username'		=> $user['name'],
																'userid'			=> $user['id'],
																'password'		=> $user['password'],
																'location'		=> 'index',
																'logintime'		=> TIMENOW,
																'lastactivity'		=> TIMENOW,
													  ), 'adminsession' );
			$forums->func->standard_redirect("./index.php?frames=1&amp;s=".$forums->sessionid."&amp;reffer_url=".urlencode($_INPUT['reffer_url'])."");
		}
	}
}
if ( ! $validate ) {
	if ($session_validated) {
		if ( $this_session['lastactivity'] < ( TIMENOW - 60*60*2) ) {
			$session_validated = 0;
			$forums->admin->print_cp_login($forums->lang['loginovertime']);
		} else if ($this_session['host'] != SESSION_HOST) {
			$session_validated = 0;
			$forums->admin->print_cp_login($forums->lang['ipaddressnotmatch']);
		}
		$forums->sessionid = $this_session['sessionhash'];
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin)) {
			$forums->adminperms = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."administrator WHERE aid=".$bbuserinfo['id']."");
		}
		$forums->func->fetch_query_sql( 	array( 'lastactivity' => TIMENOW, 'location' => SCRIPT ), 'adminsession', 'userid='.$bbuserinfo['id']." AND sessionhash='".$forums->sessionid."'" );
		require_once( ROOT_PATH.'includes/adminfunctions_template.php' );
		$forums->cache_func = new adminfunctions_template();
		$forums->sessionurl = 's='.$forums->sessionid.'&amp;';
		$forums->js_sessionurl = 's='.$forums->sessionid.'&';
	} else {
		$forums->admin->print_cp_login($forums->lang['loginovertime']);
	}
}
if ($_INPUT['frames']) {
	$forums->admin->print_frame_set();
	exit;
}
if ($_INPUT['do'] == 'menu') {
	$forums->admin->menu();
	exit;
}
if ($_INPUT['do'] == 'nav') {
	$forums->admin->nav();
	exit;
}

?>