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
error_reporting(E_ALL & ~E_NOTICE);
define('ROOT_PATH', './../');
ob_start();
if ( !preg_match( '/(mozilla)/i', $_SERVER['HTTP_USER_AGENT'] ) ) {
	@header("Content-Type: text/vnd.wap.wml;charset=UTF-8");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
}
$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

function convert($data='')
{
	global $forums;
	if (is_array($data)) {
		foreach ($data AS $v) {
			$v = $forums->func->convert_andstr($v);
			$newdata[] = $v;
		}
	} else {
		$data = $forums->func->convert_andstr($data);
		$newdata = $data;
	}
	unset($data);
	return $newdata;
}
require_once( ROOT_PATH.'includes/init.php');
require_once( ROOT_PATH."includes/functions.php" );

$forums->func = new functions();
$forums->sessionurl = "";

@require ( ROOT_PATH.'includes/config.php' );
$db_file = $dbtype ? trim($dbtype) : "mysql";
@require ( ROOT_PATH.'includes/db/db_'.$db_file.'.php' );
define( 'TABLE_PREFIX', $config['tableprefix'] );
$DB = new db;
$DB->server = $config['servername'];
$DB->database = $config['dbname'];
$DB->user = $config['dbusername'];
$DB->password = $config['dbpassword'];
$DB->pconnect = $config['usepconnect'];
$DB->dbcharset = $config['dbcharset'];
$DB->use_shutdown = 1;
$DB->connect();


require_once( ROOT_PATH."wap/sessions.php" );
require_once( ROOT_PATH."includes/functions_forum.php" );
$forums->forum = new functions_forum();
$_INPUT = $forums->func->init_variable();
$forums->lang['returnindex'] = convert($forums->lang['returnindex']);
$forums->url = REFERRER;
$forums->func->check_cache('settings');
$bboptions = $forums->cache['settings'];
$bboptions['bburl'] = $config['forum_url'] ? $config['forum_url'] : $bboptions['bburl'];
check_lang();

$forums->lang = $forums->func->load_lang($forums->lang, 'wap' );

$session = new session();
$bbuserinfo = $session->loadsession();
$forums->func->load_style();

if ( preg_match( '/(mozilla)/i', $_SERVER['HTTP_USER_AGENT'] ) ) {
	@header("Content-Type: text/html;charset=UTF-8");
	$forums->lang = $forums->func->load_lang($forums->lang, 'global' );
	$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
	require_once( ROOT_PATH."includes/functions_checkad.php" );
	$forums->ads = new functions_checkad();
	$message = $forums->lang['wapusemobile'];
	$message = sprintf( $message, $bboptions['bburl']."/wap/index.php" );
    list($user, $domain) = explode( '@', $bboptions['emailreceived'] );
    $safe_string = str_replace( '&amp;', '&', $forums->func->clean_value( SCRIPT ) );
	$nav = array( $forums->lang['errorsinfo'] );
	$pagetitle = $forums->lang['errorsinfo']." - ".$bboptions['bbtitle'];
	include $forums->func->load_template('errors');
	$buffer = ob_get_contents();
	ob_end_clean();
	@ob_start('ob_gzhandler');
	$buffer = preg_replace('/(action|href|src|background)=(\'|"|)(\.\/|)(.+?)(\\2)/ie', "parse_hrperlink('\\1', '\\4', './../')", $buffer);
	print $buffer;
	exit;
}

define( 'IS_WAP'  , TRUE );

function generate_seed($length = 5) {
	$seed = '';
	srand( (double)time() * 1000000 );
	for ($i = 0; $i < $length; $i++) {
		$seed .= chr(rand(97, 122));
	}
	return $seed;
}

function parse_hrperlink($script='', $action='', $root='./../') {
	if ( preg_match( "/^(javascript)/i", $action) ) {
    	return $script."='".$action."'";
    }
	return $script."='".$root.$action."'";
}
if ( $forums->sessiontype == 'cookie' ) {
	$forums->sessionid .= "";
	$forums->sessionurl   .= "";
} else {
	$forums->sessionid .= $session->sessionid;
	$forums->sessionurl .= 's='.$forums->sessionid.'&amp;';
}
$seed = generate_seed();
$forums->sessionurl .= 'seed='.$seed.'&amp;';
if ($_INPUT['pwd']) {
	$forums->sessionurl .= 'pwd='.$_INPUT['pwd'] . "&amp;";
}
if (THIS_SCRIPT != 'login' AND THIS_SCRIPT != 'register') {
	if (!$bbuserinfo['canview']) {
		$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
		$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
		$contents = convert($forums->lang['cannotviewboard']);
    	include $forums->func->load_template('wap_info');
		exit;
	}
	if ( ! $bboptions['bbactive'] ) {
		if (!$bbuserinfo['canviewoffline']) {
			$row = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."setting WHERE varname='bbclosedreason'" );
    		$message = preg_replace( "/\n/", "<br />", stripslashes( $row['value'] ) );
			$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
			$contents = convert($message);
        	include $forums->func->load_template('wap_info');
			exit;
		}
	}
	if ( !$bbuserinfo['id'] AND $bboptions['forcelogin'] ) {
		require_once( ROOT_PATH."wap/login.php" );
		$output = new login();
		$output->show();
	}
}
if ($_GET['bbuid'] AND $_GET['bbpwd']) {
	if ($bbuserinfo['id']) {
		$forums->sessionurl .= 'bbuid='.$_GET['bbuid'] . "&amp;bbpwd=".rawurldecode($_GET['bbpwd'])."&amp;";
	}
}
if ($bbuserinfo['pmunread'] > 0 AND THIS_SCRIPT != 'private') {
	$message = sprintf( $forums->lang['pmunread'], $bbuserinfo['pmunread'] );
	$message .= "<br /><a href='pm.php?{$forums->sessionurl}do=list&amp;folderid=0'>".$forums->lang['viewpm']."</a>";
	$message .= "<br /><a href='pm.php?{$forums->sessionurl}do=ignorepm'>".$forums->lang['ignorepm']."</a>";
	$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
	$contents = convert($message);
    include $forums->func->load_template('wap_info');
	exit;
}

$forums->forum->strip_invisible = 1;
$forums->forum->forums_init();
list($maxthreads,$maxposts) = explode( "&", $bbuserinfo['viewprefs'] );
$bboptions['maxthreads'] = ($maxthreads > 0) ? $maxthreads : $bboptions['maxthreads'];
$bboptions['maxposts']  = ($maxposts > 0) ? $maxposts : $bboptions['maxposts'];

function redirect($url='',$text='')
{
	global $forums;
	$forums->lang['redirect'] = convert($forums->lang['redirect']);
	if ($text) {
		$text = convert($text);
	} else {
		$text = convert($forums->lang['actiondone']);
	}
	$forums->lang['redirectpage'] = convert($forums->lang['redirectpage']);
	$redirect = "<a href='{$url}'>{$forums->lang['redirectpage']}</a><br />\n";
	$redirect .= "<a href='index.php?{$forums->sessionurl}'>{$forums->lang['returnindex']}</a>\n";
	$timer = 3;
	include $forums->func->load_template('wap_redirect');
	exit;
}

function check_lang()
{
	global $forums, $bboptions, $_INPUT, $bbuserinfo;
	if ($_INPUT['lang']) {
		$bboptions['language'] = $_INPUT['lang'];
	}
	if ($bboptions['language'] == 1 OR !$bboptions['language']) {
		$forums->func->set_cookie("language", "");
		$bboptions['language'] = "zh-cn";
	}
	$bboptions['language'] = $bboptions['language'] ? $bboptions['language'] : ($bboptions['default_lang'] ? $bboptions['default_lang'] : "zh-cn");
	$forums->sessionurl = 'lang='.$bboptions['language'].'&amp;';
}

function check_password($fid, $prompt_login=0, $in='forum')
{
	global $forums;
	$deny_access = TRUE;
	if ( $forums->func->fetch_permissions($forums->forum->foruminfo[$fid]['canshow'], 'canshow') == TRUE ) {
		if ( $forums->func->fetch_permissions($forums->forum->foruminfo[$fid]['canread'], 'canread') == TRUE ) {
			$deny_access = FALSE;
		} else {
			if ( $forums->forum->foruminfo[$fid]['showthreadlist'] ) {
				if ( $in == 'forum' ) {
					$deny_access = FALSE;
				} else {
					forums_custom_error($fid);
					$deny_access = TRUE;
				}
			} else {
				forums_custom_error($fid);
				$deny_access = TRUE;
			}
		}
	} else {
		forums_custom_error($fid);
		$deny_access = TRUE;
	}
	if (!$deny_access) {
		if ($forums->forum->foruminfo[$fid]['password']) {
			if ( check_forumpwd( $fid ) == TRUE ) {
				$deny_access = FALSE;
			} else {
				$deny_access = TRUE;
				if ( $prompt_login == 1 ) {
					forums_show_login( $fid );
				}
			}
		}
	} else {
		$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
		$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
		$contents = convert($forums->lang['cannotviewboard']);
    	include $forums->func->load_template('wap_info');
		exit;
	}
}

function check_forumpwd( $fid )
{
		global $forums;
		$forum_password = $_INPUT['pwd'];
		if ( trim($forum_password) == $forums->forum->foruminfo[$fid]['password'] ) return TRUE;
		else return FALSE;
}

function forums_custom_error( $forumid )
{
	global $forums, $DB;
	$error = $DB->query_first( "SELECT customerror FROM ".TABLE_PREFIX."forum WHERE id=".$forumid."" );
	if ( $error['customerror'] ) {
		$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
		$contents = convert($error['customerror']);
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."session SET badlocation=1 WHERE sessionhash='".$forums->sessionid."'" );
    	include $forums->func->load_template('wap_info');
		exit;
	}
}

function forums_show_login( $forumid )
{
	global $forums, $DB, $bbuserinfo, $bboptions;
	if (empty($bbuserinfo['id'])) {
		$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
		$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
		$contents = convert($forums->lang['notlogin']);
    	include $forums->func->load_template('wap_info');
		exit;
	}
	$forumname = convert($forums->forum->foruminfo[$forumid]['name']);
	$forums->lang['password'] = convert($forums->lang['password']);
	$forums->lang['login'] = convert($forums->lang['login']);
	include $forums->func->load_template('wap_forum_password');
	exit;
}

?>
