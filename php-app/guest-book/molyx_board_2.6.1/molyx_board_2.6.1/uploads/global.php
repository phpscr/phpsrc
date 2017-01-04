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
define( 'ROOT_PATH'  , "./" );
define ( 'IN_ACP', 0 );
$mtime = explode(' ', microtime());
@$forums->starttime = $mtime[1] + $mtime[0];
require_once(ROOT_PATH.'includes/init.php');
require_once(ROOT_PATH.'includes/functions.php');
header('Content-Type:text/html; charset=UTF-8');
$forums->func = new functions();
$_INPUT = $forums->func->init_variable();
$bboptions['language'] = 'en-us';

@require (ROOT_PATH.'includes/config.php');
$db_file = $config['dbtype'] ? trim($config['dbtype']) : "mysql";
@require (ROOT_PATH.'includes/db/db_'.$db_file.'.php');
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

$forums->url = REFERRER;
$forums->func->check_cache('settings');
$bboptions = $forums->cache['settings'];
$bboptions['mxemode'] = intval($bboptions['mxemode']);
$bboptions['quickeditordisplaymenu'] = intval($bboptions['quickeditordisplaymenu']);

$forums->func->check_cache('announcement');
$bboptions['bburl'] = $config['forum_url'] ? $config['forum_url'] : $bboptions['bburl'];
$forums->func->check_lang();

$forums->lang = $forums->func->load_lang($forums->lang, 'global');

require_once(ROOT_PATH.'includes/functions_forum.php');
$forums->forum = new functions_forum();

require_once(ROOT_PATH.'includes/functions_checkad.php');
$forums->ads = new functions_checkad();

require_once(ROOT_PATH.'includes/sessions.php');
$session = new session();
$bbuserinfo = $session->loadsession();

$forums->func->load_style();
$bbuserinfo['timenow'] = $forums->func->get_time( TIMENOW, 'H:i' );
$bboptions['gzipstatus'] = $bboptions['gzipoutput'] ? 'GZIP On' : 'GZIP Off';
if (THIS_SCRIPT != 'login' AND THIS_SCRIPT != 'register' AND THIS_SCRIPT != 'cron') {
	if (!$bbuserinfo['canview']) {
		$forums->func->standard_error('cannotviewboard');
	}
	if ( !$bboptions['bbactive'] ) {
		if (!$bbuserinfo['canviewoffline']) {
			$forums->func->board_offline();
		}
	}
	if ( (!$bbuserinfo['id']) AND $bboptions['forcelogin'] ) {
		require_once( ROOT_PATH."login.php" );
		$output = new login();
		$output->show();
	}
}
if ( $forums->sessiontype == 'cookie' ) {
	$forums->sessionid = '';
	$forums->sessionurl = '';
} else {
	$forums->sessionid = $session->sessionid;
	$forums->sessionurl = 's='.$forums->sessionid.'&amp;';
}
$forums->js_sessionurl = 's='.$forums->sessionid.'&';
$forums->forum->strip_invisible = 1;
list($maxthreads,$maxposts) = explode( "&", $bbuserinfo['viewprefs'] );
$bboptions['maxthreads'] = ($maxthreads > 0) ? $maxthreads : $bboptions['maxthreads'];
$bboptions['maxposts']  = ($maxposts > 0) ? $maxposts : $bboptions['maxposts'];
$forums->func->forumread();
$bboptions['uploadurl'] = $bboptions['uploadurl'] ? $bboptions['uploadurl'] : $bboptions['bburl'].'/data/uploads';
$bboptions['uploadfolder'] = $bboptions['uploadfolder'] ? $bboptions['uploadfolder'] : ROOT_PATH.'data/uploads';
if (USE_SHUTDOWN AND THIS_SCRIPT != 'cron') {
	$ROOT_PATH = realpath(ROOT_PATH);
	register_shutdown_function( array( &$forums->func, 'do_shutdown') );
}

$forums->func->check_cache('cron');
if (TIMENOW >= $forums->cache['cron']) {
	$bboptions['cron'] = '<img src="'.ROOT_PATH.'cron.php" border="0" height="1" width="1" alt="" />';
}

if ($bboptions['isajax'] && ($forums->func->is_browser('ie') || $forums->func->is_browser('mozilla'))) {
	$bboptions['isajax'] = $forums->func->get_cookie('closeajax') ? 0 : 1;
} else {
	$bboptions['isajax'] = 0;
}

if(!$bboptions['allowregistration'] AND $bboptions['isopeninvite'] AND $bbuserinfo['id']) {
	$bbuserinfo['hasnolimitinv'] = false;
	$bbuserinfo['haslimitinv'] = false;
	$bbuserinfo['invlink'] = false;
	$nolimitinv = explode(",",$bboptions['nolimitinviteusergroup']);
	if(is_array($nolimitinv)) {
		$membergroupids = explode(",",$bbuserinfo['membergroupids']);
		if(in_array($bbuserinfo['usergroupid'], $nolimitinv) OR array_intersect($membergroupids,$nolimitinv)) {
			$bbuserinfo['hasnolimitinv'] = true;
			$bbuserinfo['invlink'] = "[<a href='invite.php?".$forums->sessionurl."do=invite'>".$forums->lang['_invite']."</a>]";
		}
	}
	$limitinv = unserialize($bbuserinfo['ishasinvite']);
	if(!$bbuserinfo['hasnolimitinv'] AND $limitinv) {
		$bbuserinfo['haslimitinv'] = $limitinv;
		$bbuserinfo['haslimitinv']['invitetimend'] = $bbuserinfo['haslimitinv']['getinvitetime']+$bbuserinfo['haslimitinv']['expiry']*3600*24;
		$bbuserinfo['invlink'] = "[<a href='invite.php?".$forums->sessionurl."do=invite'>".$bbuserinfo['haslimitinv']['invitenum'].$forums->lang['_ininvite']."</a>]";
	}
}

$forums->lang_list = $forums->func->generate_lang();
$forums->style_list = $forums->func->generate_style();
?>