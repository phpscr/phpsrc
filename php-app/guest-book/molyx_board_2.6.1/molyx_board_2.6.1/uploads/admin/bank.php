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
require ('./global.php');

class settings {

	function show() 
	{
		global $forums, $_INPUT, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditbank']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		require_once(ROOT_PATH."includes/adminfunctions_cache.php");
		$this->cache = new adminfunctions_cache();
		switch ( $_INPUT['do'] ) {
			case 'banksetting_update' : 
				$this->banksetting_update();
				break;
			default: 
				$this->banksetting_view();
				break;
		}		
	}

	function banksetting_view()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle  = $forums->lang['managebank'];
		$detail = $forums->lang['managebankdesc'];
		$forums->admin->nav[] = array( 'bank.php', $forums->lang['managebank'] );

		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header( array( 1 => array('do', 'banksetting_update') ) );

		$DB->query("SELECT * FROM ".TABLE_PREFIX."setting WHERE groupid = 18 ORDER BY displayorder, title");
		while ( $r = $DB->fetch_array() )
		{
			$entry[$r['settingid']] = $r;
		}
		$title = $forums->lang['managebank'];
		$key_array = array();

		echo "<table width='100%' cellspacing='0' cellpadding='0' align='center' border='0'>\n";
		echo "<tr><td class='tableborder'>\n";
		echo "<div class='catfont'><img src='".$forums->imageurl."/arrow.gif' class='inline' alt='' />&nbsp;&nbsp;".$title."</div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
		foreach( $entry AS $id => $r ) {
			if ( $r['title'] == $forums->lang['loanfunction'] ) {
				echo "<table width='100%' cellspacing='0' cellpadding='0' align='center' border='0'>\n";
				echo "<tr><td class='tableborder'>\n";
				echo "<div class='catfont'><img src='".$forums->imageurl."/arrow.gif' class='inline' alt='' />&nbsp;&nbsp;".$forums->lang['loansetting']."</div>\n";
				echo "</td></tr>\n";
				echo "</table>\n";
			}
			$this->parse_entry( $r );
		}

		echo "<input type='hidden' name='settings_save' value='".implode(",",$this->key_array)."' />\n";
		$forums->admin->print_form_end_standalone($forums->lang['updatesetting']);
		$forums->admin->print_cp_footer();
	}
	
	function parse_entry($r)
	{
		global $forums, $DB, $_INPUT;
		$form_element  = "";
		$dropdown      = array();
		$start         = "";
		$end           = "";
		$revert_button = "";
		$tdrow1        = "tdrow1";
		$tdrow2        = "tdrow2";
		$key   = $r['varname'];
		$value = $r['value'] != "" ? $r['value'] : $r['defaultvalue'];
		$show  = 1;
		$css = "";
		if ( $r['value'] != "" AND ( $r['value'] != $r['defaultvalue'] ) ) {
			$tdrow1 = "tdrow1shaded";
			$tdrow2 = "tdrow2shaded";
			$revert_button = "<div style='width:auto;float:right;padding-top:2px;'><a href='settings.php?{$forums->sessionurl}do=setting_revert&amp;id={$r['settingid']}&amp;groupid={$r['groupid']}&amp;search={$_INPUT['search']}' title='".$forums->lang['restoredefault']."'><img src='{$forums->imageurl}/te_revert.gif' alt='' border='0' /></a></div>";
		}
		switch( $r['type'] ) {
			case 'input':
				$form_element = $forums->admin->print_input_row( $key, str_replace( "'", "&#39;", $value ), 30 );
				break;
			case 'textarea':
				$form_element = $forums->admin->print_textarea_row( $key, $value, 45, 5 );
				break;
			case 'yes_no':
				$form_element = $forums->admin->print_yes_no_row( $key, $value );
				break;
			default:
				if ( $r['dropextra'] ) {
					if ( $r['dropextra'] == '#show_forums#' ) {					
						$forums->forum->forums_init();
						$allforum = $forums->func->cache_forums();
						foreach($allforum AS $forum) {
							$dropdown[] = array( $forum[id], $forums->func->construct_depth_mark($forum['depth'], '--').$forum[name] );
						}
					} else if ( $r['dropextra'] == '#show_groups#' ) {
						$DB->query( "SELECT usergroupid, grouptitle FROM ".TABLE_PREFIX."usergroup" );
						while( $row = $DB->fetch_array() ) {
							$dropdown[] = array( $row['usergroupid'], $row['grouptitle'] );
						}
					} else if ( $r['dropextra'] == '#show_styles#' ) {
						$forums->func->cache_styles();
						foreach($forums->func->stylecache AS $style) {
							$dropdown[] = array( $style[styleid], $forums->func->construct_depth_mark($style['depth'], '--').$style[title] );
						}
					} else {
						foreach( explode( "\n", $r['dropextra'] ) AS $l ) {
							list ($k, $v) = explode( "=", $l );
							if ( $k != "" AND $v != "" ) {
								$dropdown[] = array( trim($k), trim($v) );
							}
						}
					}
				}
				if ($r['varname'] == 'timezoneoffset') {
					require_once(ROOT_PATH."includes/functions_user.php");
					$this->fu = new functions_user();
					foreach( $this->fu->fetch_timezone() AS $off => $words ) {
						$dropdown[] = array($off, $words);
					}
				}
				if ( $r['type'] == 'dropdown' ) {
					$form_element = $forums->admin->print_input_select_row( $key, $dropdown, $value );
				} else {
					$form_element = $forums->admin->print_multiple_select_row( $key, $dropdown, explode( ",", $value ), 5 );
				}			
				break;
		}
		echo "<table cellpadding='5' cellspacing='0' border='0' width='100%'>\n";
		echo "<tr>\n";
		echo "<td width='40%' class='$tdrow1' title='key: \$bboptions[".$r['varname']."]'><strong>{$r['title']}</strong><div class='description'>{$r['description']}</div></td>\n";
		echo "<td width='45%' class='$tdrow2'>{$revert_button}<div align='left' style='width:auto;'>{$form_element}</div></td>\n";
		echo "</tr></table>\n";
		$this->key_array[] = preg_replace( "/\[\]$/", "", $key );
	}	
	
	function banksetting_update( $donothing="" )
	{
		global $forums, $DB, $_INPUT;
		foreach ( $_INPUT AS $key => $value ) {
			if ( preg_match("/^cp_(\d+)$/", $key, $match) ) {
				if ( isset( $_INPUT[$match[0]]) )
					$forums->func->fetch_query_sql( array( 'displayorder' => $_INPUT[$match[0]] ), 'setting', 'settingid='.$match[1] ); 
			}
		}
		$fields = explode(",", trim($_INPUT['settings_save']) );
		if ( !count($fields ) ) {
			$forums->main_msg = $forums->lang['noselectitems'];
			$forums->banksettings_view();
		}
		$db_fields = array();
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."setting WHERE varname IN ('".implode( "','", $fields )."')" );
		while ( $r = $DB->fetch_array() ) {
			$db_fields[ $r['varname']  ] = $r;
		}
		foreach ( $db_fields AS $key => $data ) {
			if ( ($_POST[ $key ] != $data['defaultvalue']) ) {
				$value = str_replace( "&#39;", "'", $forums->func->convert_andstr($forums->func->stripslashes_uni($_POST[ $key ])) );
				$forums->func->fetch_query_sql( array( 'value' => $value ), 'setting', 'settingid='.$data['settingid'] );
			} else if ( $_INPUT[ $key ] != "" AND ( $_INPUT[ $key ] == $data['defaultvalue'] ) AND $data['value'] != '' ) {	
				$forums->func->fetch_query_sql( array( 'value' => '' ), 'setting', 'settingid='.$data['settingid'] );
			}
		}
		$_INPUT['groupid'] = $_INPUT['id'];
		$forums->main_msg = $forums->lang['bankupdated'];
		$this->cache->banksettings_recache();
		if ( ! $donothing ) {
			$this->banksetting_view();
		}
	}

	function setting_revert()
	{
		global $forums, $DB, $_INPUT;
		$_INPUT['id'] = intval($_INPUT['id']);
		if ( ! $_INPUT['id'] )
		{
			$forums->main_msg = $forums->lang['noids'];
			$this->banksetting_view();
		}
		$conf = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."setting WHERE settingid=".$_INPUT['id']."" );
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."setting SET value='' WHERE settingid=".$_INPUT['id']."" );
		$forums->main_msg = $forums->lang['bankrestored'];
		$this->cache->banksettings_recache();
		$this->banksetting_view();
	}

	function setting_make_dropdown()
	{
		global $forums, $DB, $_INPUT;
		$ret = "<form method='post' action='settings.php?{$forums->sessionurl}do=banksetting_view'><select name='groupid' class='dropdown'>";
		foreach ( $this->setting_groups AS $id => $data )
		{
			$ret .= ( $id == $_INPUT['groupid'] ) ? "<option value='{$id}' selected='selected'>{$data['title']}</option>" : "<option value='{$id}'>{$data['title']}</option>";
		}
		$ret .= "\n</select><input type='submit' id='button' value='".$forums->lang['ok']."' /></form>";
		return $ret;
	}
}

$output = new settings();
$output->show();

?>