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
define('TIMENOW', time());
define('USE_SHUTDOWN', TRUE);
define('IN_MXB', TRUE);

if ($_SERVER['HTTP_CLIENT_IP']) {
	define('ALT_IP', $_SERVER['HTTP_CLIENT_IP']);
} else if ($_SERVER['HTTP_X_FORWARDED_FOR'] AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
	foreach ($matches[0] AS $ip) {
		if (!preg_match("#^(10|172\.16|192\.168)\.#", $ip)) {
			define('ALT_IP', $ip);
			break;
		}
	}
} else if ($_SERVER['HTTP_FROM']) {
	define('ALT_IP', $_SERVER['HTTP_FROM']);
} else {
	define('ALT_IP', $_SERVER['REMOTE_ADDR']);
}

if ($_ENV['REQUEST_URI'] OR $_SERVER['REQUEST_URI']) {
	$scriptpath = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
} else {
	if ($_ENV['PATH_INFO'] OR $_SERVER['PATH_INFO']) {
		$scriptpath = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO']: $_ENV['PATH_INFO'];
	} else if ($_ENV['REDIRECT_URL'] OR $_SERVER['REDIRECT_URL']) {
		$scriptpath = $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL']: $_ENV['REDIRECT_URL'];
	} else {
		$scriptpath = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
	}
	if ($_ENV['QUERY_STRING'] OR $_SERVER['QUERY_STRING']) {
		$scriptpath .= '?' . ($_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);
	}
}
function xss_clean($var)
{
	static $find, $replace;
	if (empty($find)) {
		$find = array("'", '"', '<', '>');
		$replace = array('&#39;', '&quot;', '&lt;', '&gt;');
	}
	$var = preg_replace('/(java|vb)script/i', '\\1 script', $var);
	return str_replace($find, $replace, $var);
}
$scriptpath = preg_replace('/(s|sessionhash)=[a-z0-9]{32}?&?/', '', $scriptpath);
$scriptpath = xss_clean($scriptpath);
$scriptpath = str_replace("&","&amp;",$scriptpath);
$script = preg_replace('#(\?.*)#', '', $scriptpath);
$wolpath = $scriptpath;
define('WOLPATH', $wolpath);
define('SCRIPTPATH', $scriptpath);
define('SCRIPT', $script);
if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
	define('SESSION_HOST', substr($_SERVER['HTTP_X_FORWARDED_FOR'], 0, 15));
} else {
	define('SESSION_HOST', substr($_SERVER['REMOTE_ADDR'], 0, 15));
}
define('USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
define('REFERRER', str_replace("&","&amp;",$_SERVER['HTTP_REFERER']));
if (function_exists('ini_get')) {
	$safe_mode = @ini_get("safe_mode") ? 1 : 0;
} else {
	$safe_mode = 1;
}
define( 'SAFE_MODE', $safe_mode );

if (!@file_exists(ROOT_PATH.'includes/config.php')) {
    echo "The file 'config.php' does not exist. ";
	if (@file_exists(ROOT_PATH.'install/install.php')) {
		print "<br />You can run <a href='./install/install.php'><font color='blue'>install</font></a> to install MolyX Board";
	}
	exit;
}
$_USEROPTIONS = array(
	'showsignatures'	=> 1,
	'showavatars'		=> 2,
	'adminemail'		=> 4,
	'dstonoff'			=> 8,
	'hideemail'			=> 16,
	'usepm'				=> 32,
	'pmpop'				=> 64,
	'emailonpm'		=> 128,
	'usewysiwyg'		=> 256,
	'invisible'				=> 512,
	'loggedin'			=> 1024,
	'redirecttype'		=> 2048,
);

?>