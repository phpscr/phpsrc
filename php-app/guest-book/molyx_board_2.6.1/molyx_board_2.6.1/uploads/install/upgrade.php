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
error_reporting  (E_ERROR | E_WARNING | E_PARSE);
define( 'ROOT_PATH', './../' );
define('IN_MXB', TRUE);
@set_time_limit(0);
if (function_exists('ini_get')) {
	$safe_mode = @ini_get("safe_mode") ? 1 : 0;
} else {
	$safe_mode = 1;
}
define( 'SAFE_MODE', $safe_mode );

require_once(ROOT_PATH.'install/install_functions.php');
require_once(ROOT_PATH.'includes/config.php');

if (!$_GET['lang'] && !$_POST['lang'] && !$_COOKIE['lang']) {
	preg_match('/^([a-z-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
	$language = $matches[1];
	switch ($language) {
		case 'zh':
		case 'zh-cn':
			$language = 'zh-cn';
			break;
		case 'zh-tw':
		case 'zh-hk':
		case 'zh-mo':
		case 'zh-sg':
			$language = 'zh-tw';
			break;
		default:
			$language = 'en-us';
			break;
	}
	require(ROOT_PATH.'install/install_lang_'.$language.'.php');
	p_header();
	if ($_POST['next']) {
		$error = true;
	}
	p_selectlang($error, $language,'upgrade');
	p_footer('update', array(
			'l_username' => $l_username,
			'l_userpassword' => $l_userpassword
		));
	exit;
}
$_POST['lang'] = $_POST['lang']?$_POST['lang']:$_GET['lang'];
$_GET['lang'] = $_POST['lang'];
require(ROOT_PATH.'install/install_lang_'.$_POST['lang'].'.php');

define('TABLE_PREFIX', $config['tableprefix']);
require_once( ROOT_PATH."includes/functions.php" );
$forums->func = new functions();
require_once ( ROOT_PATH.'includes/db/db_mysql.php' );
$DB = new db;
$DB->server = $config['servername'];
$DB->database = $config['dbname'];
$DB->user = $config['dbusername'];
$DB->password = $config['dbpassword'];
$DB->pconnect = $config['usepconnect'];
$DB->dbcharset = $config['dbcharset'];
$DB->connect();

$r_registry = $DB->query_first("SELECT defaultvalue FROM ".TABLE_PREFIX."setting WHERE varname='version'");
$version = $r_registry['defaultvalue'];

$user = $DB->query_first("SELECT id,usergroupid,membergroupids,password, salt FROM ".TABLE_PREFIX."user WHERE name='".addslashes( $l_username )."'");

if( $user['id'] ) {
	if( $user['password'] != md5(md5($l_userpassword).$user['salt']) ) {
		$action = 'login';
		$loginsys = false;
	}
	if ($user['usergroupid'] == 4 OR preg_match("/,4,/i", ",".$user['membergroupids'].",") OR preg_match("/,".$user['id'].",/i", ",".$config['superadmin'].",")) {
	} else {
		$action = 'login';
		$loginsys = false;
	}
} else {
	$action = 'login';
	$loginsys = false;
}
@header("Content-Type:text/html; charset=UTF-8");
switch($action)
{
	case 'login':
		p_header();
		p_loginform($loginsys);
		p_footer('welcome');
		break;

	case 'importstyles':

		require_once ( ROOT_PATH.'includes/functions.php' );
		$forums->func = new functions();
		$forums->func->check_lang();

		require_once ( ROOT_PATH.'includes/adminfunctions_template.php' );
		$dotemplates = new adminfunctions_template();

		$xml = @file_get_contents(ROOT_PATH.'install/MolyX-style.xml');

		$dotemplates->intemplate = 0;
		$counter = 0;
		$dotemplates->curtag = '';
		$arr = array();
		$dotemplates->parser = xml_parser_create();
		xml_parser_set_option($dotemplates->parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_object($dotemplates->parser, $dotemplates);
		xml_set_element_handler($dotemplates->parser, 'parse_style_otag', 'parse_style_ctag');
		xml_set_character_data_handler($dotemplates->parser, 'parse_style_cdata');
		if (!@xml_parse($dotemplates->parser, $xml)) {
			p_errormsg(lng('error'),lng('xmlparseerror').": ".xml_error_string(xml_get_error_code($dotemplates->parser))." ".lng('atlines')." ".xml_get_current_line_number($dotemplates->parser)."");
		}
		xml_parser_free($dotemplates->parser);
		if (empty($arr)) {
			p_errormsg(lng('error'), lng('styleerror'));
		}
		$version = $dotemplates->style_version;
		$master = $dotemplates->style_master;
		$title = $dotemplates->style_title;
		$querybits = array();
		$querytemplates = 0;
		$stylebits['css'] = '';
		foreach($arr AS $type) {
			foreach($type AS $title => $template) {
				$title = addslashes($title);
				$template['template'] = addslashes($template['template']);
				if ($template['templatetype'] == 'css') {
					$ttttemplates = unserialize(stripslashes($template['template']));
					$stylebits['css'] .= $title." {";
					foreach ($ttttemplates AS $key => $value) {
						$stylebits['css'] .= $key.':'.$value.';';
					}
					$stylebits['css'] .= " }\n";
					continue;
				} elseif ($template['templatetype'] == 'stylevars') {
					$stylebits[$title] = $template['template'];
					continue;
				} else {
					$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "template WHERE styleid=1 AND title='{$title}'");
					$querybits[] = "(1, '$title', '$template[template]', '$template[templategroup]')";
				}
				if (++$querytemplates % 20 == 0) {
					$DB->query_unbuffered("
						REPLACE INTO ".TABLE_PREFIX."template
						(styleid, title, template, templategroup)
						VALUES
						" . implode(',', $querybits) . "
					");
					$querybits = array();
				}
			}
		}
		if (!empty($querybits)) {
			$DB->query_unbuffered("
				REPLACE INTO ".TABLE_PREFIX."template
				(styleid, title, template, templategroup)
				VALUES
				" . implode(',', $querybits) . "
			");
		}
		unset($querybits);
		$stylebits['css'] = str_replace( array('<', '>'), array('&lt;', '&gt;'), $stylebits['css'] );

		$DB->query_unbuffered("
				REPLACE INTO ".TABLE_PREFIX."style
				(styleid, title, imagefolder, userselect, usedefault, csstype, parentid, parentlist, css, csscache, version)
				VALUES
				('1', 'Global Style', '".$stylebits['imagefolder']."', 0, 0, 0, 0, 1, '".$stylebits['css']."', '', '".$version."')
			");
		$ds = $DB->query_first("SELECT styleid,userselect,usedefault FROM ".TABLE_PREFIX."style WHERE title='".lng('defaultstyle')."' LIMIT 1");
		if($ds['styleid']) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."style SET version='".$version."' WHERE styleid=".$ds['styleid']."");
			$styleid = $ds['styleid'];
		} else {
			$DB->query_unbuffered("
					REPLACE INTO ".TABLE_PREFIX."style
					(styleid, title, imagefolder, userselect, csstype, parentid, parentlist, css, csscache, version)
					VALUES
					('', '".lng('defaultstyle')."', '".$stylebits['imagefolder']."', 1, 1, 1, 1, '', '', '".$version."')
				");
			$styleid = $DB->insert_id();
		}
		$parentlist = $styleid.',1';
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."style SET parentlist='".$parentlist."' WHERE styleid = $styleid");
		$dotemplates->rebuildallcaches();
		require_once ( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$cache = new adminfunctions_cache();
		$forums->func->check_cache('stats');
		$cache->all_recache();

			$finished = p_done(1);
			p_header();
			echo $finished;
			p_footer();
		break;

	case 'startupdate':
		include $scriptname;
		$update = new CUpdate;
		$add_charset = $DB->dbcharset ? " default charset=".$DB->dbcharset : "";
		require_once ( ROOT_PATH.'includes/functions.php' );
		$forums->func = new functions();

		if( !$update->AllowUpdate() )
		{
			p_errormsg(lng('error'),
				lng('cantexec'));
		}
		p_header(1);
		ob_flush();

		if( $update->RunUpdate() )
		{
			p_errormsg(lng('error'),
				$update->GetError());
		}
		else
		{
				p_add_tb();
				p_importstyles();
				ob_flush();

		}
		p_footer('importstyles', array(
					'scriptname' => $scriptname,
					'l_username' => $l_username,
					'l_userpassword' => $l_userpassword
				));
		ob_flush();
		break;
	case 'update':
		$scriptname = ROOT_PATH.'install/upgrades/'.$scriptname;
		if( !file_exists($scriptname) || !$scriptname )
		{
			p_errormsg(lng('error'), lng('notfound'));
		}
		else
		{
			include $scriptname;

			$update = new CUpdate;
			$update->Notes = lng('updatenotes');

			if( $update->UpdaterVer > $cfg['updater_ver'] )
			{
				p_errormsg(lng('error'), lng('tooold'));
			}
			else
			{
				p_header();
				p_updateinfo($update);
				p_footer('startupdate', array(
					'scriptname' => $scriptname,
					'l_username' => $l_username,
					'l_userpassword' => $l_userpassword
				));
			}
		}
		break;

	case 'welcome':
	default:
		$a_file = array();
		$dp = opendir(ROOT_PATH.'install/upgrades/');
		while( $file = readdir($dp) )
		{
			if( substr($file, -7, 7) == '.update' )
			{
				$a_file[] = $file;
			}
		}
		natsort($a_file);
		p_header();
		p_updatewelcome($a_file);
		p_footer('update', array(
			'l_username' => $l_username,
			'l_userpassword' => $l_userpassword
		));
		break;
}
?>