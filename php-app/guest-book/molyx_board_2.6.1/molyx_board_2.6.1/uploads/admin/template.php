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

class template {

	function show()
	{
		global $forums,$_INPUT, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditstyles']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$this->unaltered = "<img src='{$forums->imageurl}/style_unaltered.gif' border='0' alt='-' title='".$forums->lang['styleunaltered']."' />&nbsp;";
		$this->altered = "<img src='{$forums->imageurl}/style_altered.gif' border='0' alt='+' title='".$forums->lang['stylealtered']."' />&nbsp;";
		$this->inherited = "<img src='{$forums->imageurl}/style_inherited.gif' border='0' alt='|' title='".$forums->lang['styleinherited']."' />&nbsp;";
		switch($_INPUT['do']) {
			case 'edit':
				$this->edittemplates();
				break;
			case 'editgroup':
				$this->edittemplategroups();
				break;
			case 'newgroup':
				$this->editgroupbit('new');
				break;
			case 'editgroupbit':
				$this->editgroupbit();
				break;
			case 'doedit':
				$this->do_edit();
				break;
			case 'doeditgroup':
				$this->do_editgroup();
				break;
			case 'remove_bit':
				$this->removetemplatebit();
				break;
			case 'edittemplatebit':
				$this->edittemplatebit();
				break;
			case 'floateditor':
				$this->floatededitor();
				break;
			case 'addbit':
				$this->addtemplatebit();
				break;
			case 'doadd':
				$this->do_addtemplatebit();
				break;
			case 'preview':
				$this->do_preview();
				break;
			case 'search':
				$this->edittemplates('search');
				break;
			case 'compare':
				$this->compare();
				break;
			default:
				$this->edittemplates();
				break;
		}
	}

	function edittemplates($type='list')
	{
		global $forums, $DB, $_INPUT;
		$groups     = array();
		$group_bits = array();
		$styles = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$forums->admin->nav[] = array( 'template.php?id='.$_INPUT['id'] ,$forums->lang['templatesetting'].' - "'.$styles['title'].'"' );
		if ( $type=='search' ) {
			$rawword = $_GET['searchkeywords'] ? rawurldecode( $_GET['searchkeywords'] ) : $forums->func->stripslashes_uni($_POST['searchkeywords']);
			$search_word = trim(  $rawword );
			$search_safe = urlencode( $search_word );
			$search_all  = intval( $_INPUT['searchall'] );
			if ( ! $search_word ) {
				$forums->main_msg = $forums->lang['requirekeyword'];
				return $this->edittemplates();
			}
			if ( $search_all ) {
				$templates = $forums->cache_func->_get_templates( $styles['parentlist'], 'all' );
			} else {
				$DB->query( "SELECT * FROM ".TABLE_PREFIX."template WHERE styleid='".$styles['styleid']."'" );
				while ( $r = $DB->fetch_array() ) {
					$templates[ $r['templategroup'] ][ $forums->func->strtolower($r['title']) ] = $r;
				}
			}
			if ( ! count( $templates ) ) {
				$forums->main_msg = $forums->lang['cannotfindtemplate'];
				return $this->edittemplates();
			}
			foreach( $templates AS $group => $d ) {
				foreach( $templates[ $group ] AS $tmp_name => $tmp_data ) {
					if ( strstr( $forums->func->strtolower( $tmp_data['template'] ), $forums->func->strtolower( $search_word ) ) ) {
						$final[ $group ][] = $tmp_data;
						$matches[ $group ]++;
					}
				}
			}
			if ( ! count($final) ) {
				$forums->main_msg = $forums->lang['cannotfindtemplate'];
				return $this->edittemplates();
			}
		}
		$pagetitle  = $forums->lang['edittemplate'];
		$detail = $forums->lang['edittemplatedesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$grouptitles = $forums->cache_func->_get_templates($styles['parentlist'], 'groups');
		foreach( $grouptitles AS $title => $g ) {
			$g['easy_name'] = "<strong>".$g['templategroup']."</strong>";
			$g['easy_desc'] = "";
			$groups[] = $g;
		}
		echo "<script language='javascript' type='text/javascript'>\n";
		echo "function pop_win(theUrl)\n";
		echo "{\n";
		echo "window.open(theUrl+'&{$forums->js_sessionurl}','Preview','width=400,height=450,resizable=yes,scrollbars=yes');\n";
		echo "}\n";
		echo "var toggleon  = 0;\n";
		echo "function toggleselectall()\n";
		echo "{\n";
		echo "if ( toggleon )\n";
		echo "{\n";
		echo "toggleon = 0;\n";
		echo "dotoggleselectall(0);\n";
		echo "}\n";
		echo "else\n";
		echo "{\n";
		echo "toggleon = 1;\n";
		echo "dotoggleselectall(1);\n";
		echo "}\n";
		echo "}\n";
		echo "function dotoggleselectall(selectall)\n";
		echo "{\n";
		echo "var fmobj = document.mutliact;\n";
		echo "for (var i=0;i<fmobj.elements.length;i++)\n";
		echo "{\n";
		echo "var e = fmobj.elements[i];\n";
		echo "if (e.type=='checkbox')\n";
		echo "{\n";
		echo "if ( selectall ) {\n";
		echo "e.checked = true;\n";
		echo "} else {\n";
		echo "e.checked = false;\n";
		echo "}\n";
		echo "}\n";
		echo "}\n";
		echo "}\n";
		echo "</script>\n";
		echo "<form action='template.php?{$forums->sessionurl}do=search&amp;id={$_INPUT['id']}&amp;searchall=1' method='post'>\n";
		echo "<table width='100%' cellspacing='0' cellpadding='0' align='center' border='0'>\n";
		echo "<tr><td class='tableborder'>\n";
		echo "<div style='float:right'><input type='text' size='20' class='textinput' name='searchkeywords' value='".$forums->lang['searchtpl']."...' onfocus='this.value=\"\"' />&nbsp;<input type='submit' class='button' value='".$forums->lang['ok']."' />&nbsp;</div>\n";
		echo "<div id='catfont'>\n";
		echo "<img src='".$forums->imageurl."/arrow.gif' class='inline' />&nbsp;&nbsp;".$forums->lang['templatelist']."</div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
		echo "\n<div class='tdrow1'>\n";
		foreach( $groups AS $group ) {
			$eid    = $group['tid'];
			$exp_content = "";
			if ($_INPUT['expand'] == $group['templategroup'] OR count( $final[ $group['templategroup'] ] )) {
				$forums->admin->checkdelete();
				$master_names = array();
				$DB->query( "SELECT title FROM ".TABLE_PREFIX."template WHERE templategroup='".$group['templategroup']."' AND styleid=1" );
				while ( $m = $DB->fetch_array() ) {
					$master_names[ $m['title'] ] = $m['title'];
				}
				echo "<a name='{$group['templategroup']}'></a>\n";
				echo "<div style='padding:4px;border-top:1px solid #E1EEF7;border-bottom:1px solid #142938;'>\n";
				echo "<table cellspacing='0' cellpadding='0' border='0'>\n";
				echo "<tr>\n";
				echo "<td align='center' width='1%'><img src='{$forums->imageurl}/toc_collapse.gif' alt='".$forums->lang['templategroup']."' style='vertical-align:middle' /></td>\n";
				echo "<td align='left' width='60%'>\n";
				if ($type == 'search') {
					$search_match = intval( $matches[ $group['templategroup'] ] );
					$poxbox = "_".$group['templategroup'];
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onclick=\"toggleview('popbox_{$group['templategroup']}'); return false;\">{$group['easy_name']}</a> (".$forums->lang['result'].": {$search_match})\n";
					echo "</td>\n";
					echo "<td align='right' width='40%'>{$group['easy_preview']}</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo "</div>\n";
					echo "<div style='margin-left:25px;border:1px solid #555;display:none;' id='popbox_{$group['templategroup']}'>\n";
				} else {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a  href='template.php?{$forums->sessionurl}do=edit&amp;id={$_INPUT['id']}&amp;expand={$group['templategroup']}'  onclick=\"toggleview('popbox'); return false;\">{$group['easy_name']}</a>\n";
					echo "</td>\n";
					echo "<td align='right' width='40%'>{$group['easy_preview']}</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo "</div>\n";
					echo "<div style='border:1px solid #555;' id='popbox'>\n";
				}
				echo "<div style='background-color:#142938' class='styleeditortopstrip'>\n";
				echo "<div style='float:right'>\n";
				echo "<a href='#' onclick=\"togglediv('popbox{$poxbox}'); return false;\" title='".$forums->lang['closetemplategroup']."'><img src='{$forums->imageurl}/style_close.gif' border='0' alt='".$forums->lang['closetemplategroup']."' /></a>\n";
				echo "</div>\n";
				echo "<div>\n";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onclick=\"toggleselectall(); return false;\" title='".$forums->lang['checkuncheck']."'><img src='{$forums->imageurl}/style_checked.gif' border='0' alt='".$forums->lang['checkuncheck']."' /></a>\n";
				echo "&nbsp;{$group['easy_name']}\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<form name='mutliact' action='template.php?{$forums->sessionurl}do=edittemplatebit&amp;expand={$group['templategroup']}&amp;id={$_INPUT['id']}&amp;p={$_INPUT['p']}&amp;search_string=$search_safe' method='post'>\n";
				echo "<div>\n";
				echo "<table cellspacing='0' cellpadding='2'>\n";
				$temp = "";
				$sec_arry = array();
				if($type == 'search') {
					$group_bits = $final[ $group['templategroup'] ];
				} else {
					$group_bits = $forums->cache_func->_get_templates($styles['parentlist'], 'groups', $_INPUT['expand']);
				}
				foreach( $group_bits AS $eye => $i ) {
					$sec_arry[ $i['tid'] ] = $i;
					$sec_arry[ $i['tid'] ]['easy_name'] = $i['title'];
				}
				foreach( $sec_arry AS $id => $sec ) {
					$sec['easy_name'] = preg_replace( "/^(\d+)\:\s+?/", "", $sec['easy_name'] );
					$custom_bit    = "";
					if ( $sec['styleid'] == $styles['styleid'] ) {
						$altered_image = $this->altered;
						$css_info      = '#4790C4';
					} else if ( $sec['styleid'] == '1' ) {
						$altered_image = $this->unaltered;
						$css_info      = '#285270';
					} else {
						$altered_image = $this->inherited;
						$css_info      = '#FFF2D3';
					}
					$remove_button = "<img src='{$forums->imageurl}/blank.gif' alt='' border='0' width='44' height='16' />&nbsp;";
					if ( $sec['styleid'] == $_INPUT['id'] ) {
						if ( $master_names[ $sec['title'] ] ) {
							$remove_button = "<a title='".$forums->lang['revertoriginaltpl']."' href=\"javascript:checkdelete('template.php','do=remove_bit&amp;tid={$sec['tid']}&amp;id={$_INPUT['id']}&amp;expand={$group['templategroup']}')\"><img src='{$forums->imageurl}/te_revert.gif' alt='X' border='0' /></a>&nbsp;";
						} else {
							$css_info = '#4790C4';
							$custom_bit = ' ('.$forums->lang['customtemplate'].')';
							$remove_button = "<a title='".$forums->lang['revertoriginaltpl']."' href=\"javascript:checkdelete('template.php','do=remove_bit&amp;tid={$sec['tid']}&amp;id={$_INPUT['id']}&amp;expand={$group['templategroup']}')\"><img src='{$forums->imageurl}/te_remove.gif' alt='X' border='0' /></a>&nbsp;";
						}
					}
					echo "<tr>\n";
					echo "<td width='2%' style='background-color:$css_info' align='center'><img src='{$forums->imageurl}/file.gif' title='".$forums->lang['styleid'].":{$sec['styleid']}' alt='".$forums->lang['template']."' style='vertical-align:middle' /></td>\n";
					echo "<td width='88%' style='background-color:$css_info'><input type='checkbox' style='background-color:$css_info' name='cb_{$sec['tid']}' value='1' />&nbsp;{$altered_image}<a href='template.php?{$forums->sessionurl}do=edittemplatebit&amp;tid={$sec['tid']}&amp;id={$_INPUT['id']}&amp;expand={$group['templategroup']}&amp;type=single&amp;search_string=$search_safe' title='".$forums->lang['templatename'].": {$sec['title']}'>{$sec['easy_name']}</a>{$custom_bit}</td>\n";
					echo "<td width='10%' style='background-color:$css_info' align='right' nowrap='nowrap'>".$remove_button."<a style='text-decoration:none' title='".$forums->lang['viewtemplateastext']."' href='javascript:pop_win(\"template.php?{$forums->sessionurl}do=preview&amp;tid={$sec['tid']}&amp;type=text\")'><img src='{$forums->imageurl}/preview_text.gif' border='0' alt='".$forums->lang['viewtemplateastext']."' /></a>\n";
					echo "<a style='text-decoration:none' title='".$forums->lang['viewtemplateashtml']."' href='javascript:pop_win(\"template.php?{$forums->sessionurl}do=preview&amp;tid={$sec['tid']}&amp;type=html\")'><img src='{$forums->imageurl}/preview_html.gif' border='0' alt='".$forums->lang['viewtemplateashtml']."' />&nbsp;</a>\n";
					echo "</td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n";
				echo "</div>\n";
				echo "<div style='background:#142938'>\n";
				echo "<div align='left' style='padding:5px;margin-left:25px'>\n";
				echo "<div style='float:right'>".$forums->admin->print_button( $forums->lang['addnewtemplate'], "template.php?{$forums->sessionurl}do=addbit&amp;id={$_INPUT['id']}&amp;p={$_INPUT['p']}&amp;expand={$group['templategroup']}", "button", $forums->lang['addnewtemplate'] )."</div>\n";
				echo "<div><input type='submit' class='button' value='".$forums->lang['editselecttemplate']."' /></div>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "</form>\n";
				echo "</div>\n";
			} else if ($type != 'search') {
				$altered      = sprintf( '%02d', intval($forums->cache_func->template_count[$_INPUT['id']][ $group['templategroup'] ]['count']) );
				$original     = sprintf( '%02d', intval($forums->cache_func->template_count[1][ $group['templategroup'] ]['count']) );
				$inherited    = sprintf( '%02d', intval($forums->cache_func->template_count[$styles['parentid']][ $group['templategroup'] ]['count']) );
				$count_string = "";
				if ( $styles['parentid'] != 1 ) {
					$count_string = "{$this->unaltered} $original | {$this->inherited} $inherited | {$this->altered} $altered ";
				} else {
					$count_string = "{$this->unaltered} $original | {$this->altered} $altered";
				}
				if ( $altered > 0 ) {
					$folder_blob = $this->altered;
				} else if ( $styles['parentid'] != 1 AND $inherited > 0 ) {
					$folder_blob = $this->inherited;
				} else {
					$folder_blob = $this->unaltered;
				}
				echo "<div style='padding:4px;border-bottom:1px solid #DDDDDD;'>\n";
				echo "<table cellspacing='0' cellpadding='0' border='0'>\n";
				echo "<tr>\n";
				echo "<td align='center' width='1%'><img src='{$forums->imageurl}/toc_expand.gif' alt='".$forums->lang['templategroup']."' style='vertical-align:middle' /></td>\n";
				echo "<td align='left' width='60%'>&nbsp;{$folder_blob}&nbsp;<a style='font-size:12px' onmouseover=\"return togglediv('desc_{$group['templategroup']}', 1);\" onmouseout=\"return togglediv('desc_{$group['templategroup']}', 0);\" href='template.php?{$forums->sessionurl}do=edit&amp;id={$_INPUT['id']}&amp;expand={$group['templategroup']}#{$group['templategroup']}'>{$group['easy_name']}</a></td>\n";
				echo "<td align='right' width='40%'>($count_string)</td>\n";
				echo "</tr>\n";
				echo "</table>\n";
				echo "</div>\n";
			}
		}
		echo "</div>\n";
		echo "<br />\n";
		echo "<div><strong>".$forums->lang['templatemenudesc'].":</strong><br />\n";
		echo $this->altered." ".$forums->lang['hastemplatealtered']."<br />\n";
		echo $this->unaltered." ".$forums->lang['hastemplateunaltered']."<br />\n";
		echo $this->inherited." ".$forums->lang['hastemplateinherited']."\n";
		echo "</div>\n";
		echo "<br />\n";
		$forums->admin->print_cp_footer();
	}

	function edittemplategroups($type='list')
	{
		global $forums, $DB, $_INPUT;
		$groups     = array();
		$group_bits = array();
		$forums->admin->nav[] = array( '',$forums->lang['templategroupsetting'] );
		$pagetitle  = $forums->lang['edittemplategroup'];
		$detail = $forums->lang['edittemplategroupdesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$newgroup = $forums->admin->print_button($forums->lang['addnewtemplategroup'], "template.php?{$forums->sessionurl}do=newgroup");
		echo "<table width='100%' cellspacing='0' cellpadding='0' align='center' border='0'>\n";
		echo "<tr><td class='tableborder'>\n";
		echo "<div style='float:right'>$newgroup&nbsp;</div>\n";
		echo "<div id='catfont'>\n";
		echo "<img src='".$forums->imageurl."/arrow.gif' class='inline' />&nbsp;&nbsp;".$forums->lang['assocgroupsetting']."</div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<div>\n<div class='tdrow1'>\n";
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."templategroup ORDER BY title" );
		while( $templategroup = $DB->fetch_array() ) {
			if ($_INPUT['expand'] == $group['templategroup'] OR count( $final[ $group['templategroup'] ] )) {
				echo "<a name='{$templategroup['title']}'></a>\n";
				echo "<div style='padding:4px;border-bottom:1px solid #629DC5;'>\n";
				echo "<table cellspacing='0' cellpadding='0' border='0'>\n";
				echo "<tr>\n";
				echo "<td align='center' width='1%'><img src='{$forums->imageurl}/toc_collapse.gif' alt='{$templategroup['title']}' style='vertical-align:middle' /></td>\n";
				echo "<td align='left' width='100%'>\n";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onclick=\"toggleview('popbox_{$templategroup['templategroupid']}'); return false;\">{$templategroup['title']}</a>\n";
				echo "</td>\n";
				echo "</tr>\n";
				echo "</table>\n";
				echo "</div>\n";
				echo "<div style='margin-left:25px;border:1px solid #555555;display:none;' id='popbox_{$templategroup['templategroupid']}'>\n";
				echo "<div>\n";
				echo "<form name='mutliact{$templategroup['templategroupid']}' action='template.php?{$forums->sessionurl}do=editgroupbit&amp;id={$templategroup['templategroupid']}' method='post'>\n";
				echo "<table cellspacing='0' cellpadding='2'>\n";
				$bit_array = explode("\n", trim($templategroup['templategroup']) );
				foreach( $bit_array AS $template ) {
					if ( ! trim($template) ) {
						continue;
					}
					$template = str_replace("<", "", $template);
					$template = str_replace(">", "", $template);
					echo "<tr>\n";
					echo "<td width='2%' align='center'><img src='{$forums->imageurl}/file.gif' title='".$forums->lang['template'].":{$template}' alt='".$forums->lang['template']."' style='vertical-align:middle' /></td>\n";
					echo "<td width='88%'>{$template}</td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n";
				echo "<div style='background:#142938'>\n";
				echo "<div align='left' style='padding:5px;margin-left:25px'>\n";
				echo "<div><input type='submit' class='button' value='".$forums->lang['changeassoctemplate']."' /></div>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "</form>\n";
				echo "</div></div>\n";
			}
		}
		echo "</div></div>\n";
		$forums->admin->print_cp_footer();
	}

	function do_preview()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['tid'] == "") {
			echo ($forums->lang['requiretemplatename']);
			exit();
		}
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."template WHERE tid='".$_INPUT['tid']."'" );
		if ( ! $template = $DB->fetch_array() ) {
			echo ($forums->lang['requiretemplatename']);
			exit();
		}
		if ( $_INPUT['ori'] ) {
			$styles = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid='1'" );
			if ( $orit = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."template WHERE title='".$template['title']."' AND styleid='1'" ) ) {
				$template = $orit;
			}
		} else {
			$styles = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$template['styleid']."" );
		}
		if ($_INPUT['type'] == 'html') {
			$css = $styles['csscache'];
			$css_text = "\n<style>\n<!--\n".str_replace( "<#IMAGE#>", "images/".$r['img_dir'], $css)."\n//-->\n</style>";

		}
		@header("Content-type: text/html;charset=UTF-8");
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		echo "<html xml:lang=\"en\" lang=\"en\" xmlns=\"http://www.w3.org/1999/xhtml\">\n";
		echo "<head><title>".$forums->lang['preview']."</title>$css_text</head><body> \n";
		echo "<table width='100%' cellpadding='4' style='background-color: #000000;font-family:verdana, arial;font-size:12px;color:white'>\n";
		echo "<tr>\n";
		echo "<td align='center' style='font-family:verdana, arial;font-size:12px;color:white'>".$forums->lang['templategroup'].": {$template['templategroup']} ; ".$forums->lang['templatename'].": {$template['title']}</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td align='center' style='font-family:verdana, arial;font-size:12px;color:white'>[ <a href='template.php?{$forums->sessionurl}do=preview&amp;tid={$_INPUT['tid']}&amp;type=text' style='font-family:verdana, arial;font-size:12px;color:white'>".$forums->lang['textmode']."</a> | <a href='template.php?{$forums->sessionurl}do=preview&amp;tid={$_INPUT['tid']}&amp;type=html' style='font-family:verdana, arial;font-size:12px;color:white'>".$forums->lang['htmlmode']."</a> ]</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "<br /><br />\n";
		if ($_INPUT['type'] == 'text') {
			$html = $this->convert_tags($template['template']);
			$html = str_replace( "<" , "&lt;"  , $html);
			$html = str_replace( ">" , "&gt;"  , $html);
			$html = str_replace( "\"", "&quot;", $html);
			$html = preg_replace( array("!&lt;\!--(.+?)(//)?--&gt;!s", "#&lt;([^&<>]+)&gt;#s", "#&lt;([^&<>]+)=#s", "#&lt;/([^&]+)&gt;#s", "!=(&quot;|')([^<>])(&quot;|')(\s|&gt;)!s"), array("&#60;&#33;<span style='color:yellow'>--\\1--\\2</span>&#62;", "&lt;<span style='color:blue'>\\1</span>&gt;", "&lt;<span style='color:blue'>\\1</span>=", "&lt;/<span style='color:blue'>\\1</span>&gt;", "=\\1<span style='color:purple'>\\2</span>\\3\\4"), $html );
			$html = str_replace( "\n", "<br />", str_replace("\r\n", "\n", $html ) );
			echo "<table width='100%' cellpadding='4' style='font-family:verdana, arial;font-size:12px;'><tr><td>".$html."</td></tr></table>";
			exit();
		} else if ($_INPUT['type'] == 'html') {
			echo $this->convert_tags($template['template']);
			exit();
		}
	}

	function edittemplatebit()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle  = $forums->lang['edittemplate'];
		$template_bit_ids = array();
		$ids = array();
		if ( $_INPUT['type'] == 'single' ) {
			$ids[] = $_INPUT['tid'];
		} else {
			foreach ($_INPUT AS $key => $value) {
				if ( preg_match( "/^cb_(\d+)$/", $key, $match ) ) {
					if ($_INPUT[$match[0]]) {
						$ids[] = $match[1];
					}
				}
			}
 		}
 		if ( count($ids) < 1 ) {
 			$forums->admin->print_cp_error($forums->lang['noselecttemplate']);
 		}
		$t = $DB->query( "SELECT * FROM ".TABLE_PREFIX."template WHERE tid IN (".implode(",",$ids).")" );
		while ( $i = $DB->fetch_array($t) ) {
			$sec_arry[ $i['tid'] ] = $i;
			$sec_arry[ $i['tid'] ]['easy_name'] = $i['title'];
			$groupname = $i['templategroup'];
		}
		$forums->admin->nav[] = array( "template.php?do=edit&amp;id={$_INPUT['id']}&amp;expand={$groupname}#{$groupname}", $forums->lang['templategroup']." - ".$groupname );
		$forums->admin->print_cp_header($pagetitle, $detail);
		echo "<script language='javascript' type='text/javascript'>\n";
		echo "<!--\n";
		echo "function restore(tid, expand, styleid)\n";
		echo "{\n";
		echo "if (confirm(\"".$forums->lang['reverttploriginal1']."\\n".$forums->lang['reverttploriginal2']."\"))\n";
		echo "{\n";
        echo "window.location = 'template.php?{$forums->js_sessionurl}do=edittemplatebit&type=single&tid=' + tid + '&expand=' + expand + '&id=' + styleid;\n";
       	echo "}\n";
      	echo "}\n";
		echo "//-->\n";
		echo "</script>\n";
		$forums->admin->print_form_header( array( 1 => array( 'do', 'doedit'    ),
															   2 => array( 'tid', $_INPUT['tid'] ),
															   3 => array( 'type', $_INPUT['type'] ),
															   4 => array( 'id', $_INPUT['id']   ),
													  )  , "theform"    );
		foreach( $sec_arry AS $id => $template ) {
			$setid     = $template['styleid'];
			if ( ! $_INPUT['error_raw_'.$template['tid']] ) {
				$templ     = $template['template'];
				$templ     = preg_replace("/&/", "&#38;", $templ );
				$templ     = preg_replace("/</", "&#60;", $templ );
				$templ     = preg_replace("/>/", "&#62;", $templ );
				$templ     = str_replace( '\n', '&#092;n', $templ );
			} else {
				$templ = $_INPUT['error_raw_'.$template['tid']];
			}
			if ( $template['styleid'] == $_INPUT['id'] ) {
				$altered_image = $this->altered;
			} else if ( $template['styleid'] == '1' ) {
				$altered_image = $this->unaltered;
			} else {
				$altered_image = $this->inherited;
			}
			$forums->admin->columns[] = array( "&nbsp;" , "20%" );
			$forums->admin->columns[] = array( "&nbsp;" , "80%" );
			$forums->admin->print_table_start($altered_image.$template['easy_name']);
			$forums->admin->print_cells_row( array( "<input type='button' value='".$forums->lang['enlargeeditor']."'  class='button' title='".$forums->lang['enlargeeditor']."' onclick=\"pop_win('template.php?{$forums->sessionurl}do=floateditor&amp;id={$template['tid']}&amp;name={$template['easy_name']}', 'Float{$template['tid']}', 800, 500)\" /><br /><br />".
			"<input type='button' value='".$forums->lang['reverttemplate']."'  class='button' title='".$forums->lang['reverttemplatedesc']."' onClick='restore(\"{$template['tid']}\",\"{$_INPUT['expand']}\",\"{$_INPUT['id']}\")' /><br /><br />".
			"<input type='button' value='".$forums->lang['vieworiginaltemplate']."'  class='button' title='".$forums->lang['vieworiginaltemplate']."' onClick='pop_win(\"template.php?{$forums->sessionurl}do=preview&amp;tid={$template['tid']}&amp;ori=1&amp;type=text\", \"OriginalPreview\", 400,400)' />\n", $forums->admin->print_textarea_row("txt{$template['tid']}", $templ, '100', '30', 'none', "t{$template['tid']}" ) ) );
			$forums->admin->print_cells_row( array( "<strong>".$forums->lang['searchintemplate']."</strong>", $forums->admin->print_input_row("string".$template['tid'], $_INPUT['search_string'])."<INPUT class='button' accessKey=f onclick=findInPage(t{$template['tid']},document.theform.string{$template['tid']}.value); tabIndex=1 type='button' value=' ".$forums->lang['find']." ' /><INPUT class=button accessKey=c onclick=HighlightAll(t{$template['tid']}); type='button' value=' ".$forums->lang['copy']." ' />" ) );
			$template_bit_ids[] = "t{$template['tid']}";
			unset($forums->admin->columns);
		}
		$forums->admin->print_form_submit($forums->lang['savetemplate'], '', "<input type='submit' name='savereload' value='".$forums->lang['savereload']."' class='button' />\n");
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function floatededitor()
	{
		global $forums, $DB, $_INPUT;
		$forums->admin->print_popup_header();

		$forums->admin->print_form_header('', "theform");
		$forums->admin->print_table_start($_INPUT['name']);
		$forums->admin->print_cells_single_row( $forums->admin->print_textarea_row("templatebit", $html, '100', '27', 'none', "templatebit" ) );
				echo "<script type='text/javascript'>\n";
		echo "var templategroupid = '{$_INPUT['id']}';\n";
		echo "var template_bit  = eval(\"opener.document.theform.txt\"+templategroupid+\".value\");\n";
		echo "document.theform.templatebit.value = template_bit;\n";
		echo "var template_bit_ids = 'templatebit';\n";
		echo "function saveandclose()\n";
		echo "{\n";
		echo "eval(\"opener.document.theform.txt\"+templategroupid+\".value = document.theform.templatebit.value\");\n";
		echo "window.close();\n";
		echo "}\n";
		echo "</script>\n";
		$forums->admin->print_form_end($forums->lang['saveandreturn'], "onclick='saveandclose()'");
		$forums->admin->print_table_footer();
		$forums->admin->print_popup_footer();
	}

	function editgroupbit($type='edit')
	{
		global $forums, $DB, $_INPUT;
		$pagetitle  = $forums->lang['editgroupassoc'];
		$detail = $forums->lang['editgroupassocdesc'];
		if ($type == 'edit') {
			if ( $_INPUT['id'] == '' ) {
				$forums->admin->print_cp_error($forums->lang['nofindgroupassoc']);
			}
			if ( !$group = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."templategroup WHERE templategroupid = ".$_INPUT['id']."" ) ) {
				$forums->admin->print_cp_error($forums->lang['nofindgroupassoc']);
			}
			$group['templategroup'] = str_replace("<", "", $group['templategroup']);
			$group['templategroup'] = str_replace(">", "", $group['templategroup']);
			$_INPUT['title'] = $_INPUT['title'] ? $_INPUT['title'] : $group['title'];
			$_INPUT['templategroup'] = $_INPUT['templategroup'] ? $_INPUT['templategroup'] : $group['templategroup'];
			$useheader = $group['noheader'] == 0 ? 1 : 0;
			$_INPUT['noheader'] = $_INPUT['noheader'] ? $_INPUT['noheader'] : $useheader;
			$groupname = $group['title'];
			$check = "<input type='checkbox' name='delete' value='1' />	 ".$forums->lang['delete'];
			$button = $forums->lang['editassocgroup'];
		} else {
			$groupname = $forums->lang['addnewassocgroup'];
			$button = $forums->lang['addnewassocgroup'];
		}
		$forums->admin->nav[] = array( "template.php?{$forums->sessionurl}do=editgroup&amp;id={$_INPUT['id']}", $forums->lang['assocgroup']." - ".$groupname );
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'doeditgroup'    ),
															   2 => array( 'type'  , $type ),
															   3 => array( 'id'    , $_INPUT['id']   ),
													  )  , "theform"    );
		$forums->admin->columns[] = array( ""  , "40%" );
		$forums->admin->columns[] = array( ""  , "60%" );
		$forums->admin->print_table_start( $pagetitle );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['assocgroupname']."</strong><div class='description'>".$forums->lang['assocgroupnamedesc']."</div>" ,
												  			   $forums->admin->print_input_row( 'title', $_INPUT['title'] )."&nbsp;".$check
										 		    	)      );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['assoctemplate']."</strong><div class='description'>".$forums->lang['assoctemplatedesc']."</div>" ,
												  			   $forums->admin->print_textarea_row( 'templategroup', $group['templategroup'] )
										 		    	)      );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['useheaders']."</strong><div class='description'>".$forums->lang['useheaderdesc']."</div>" ,
												  			   $forums->admin->print_yes_no_row( 'useheader', $_INPUT['noheader'] ? $_INPUT['noheader'] : 0 )
										 		    	)      );
		$forums->admin->print_form_submit( $button );
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function do_editgroup()
	{
		global $forums, $DB, $_INPUT;
		$_INPUT['title'] = trim( $_INPUT['title'] );
		$_INPUT['templategroup'] = trim( $_INPUT['templategroup'] );
		$bit_array = explode("<br />", $_INPUT['templategroup']);
		foreach( $bit_array AS $data ) {
			$data = trim($data);
			if (!$data) {
				continue;
			}
			$t = $DB->query( "SELECT * FROM ".TABLE_PREFIX."template WHERE title = '".$data."'" );
			if ( !$DB->num_rows($t) ) {
				$errors[] = $forums->lang['nofindtemplate']." - ".$data."<br />";
			}
			$templatebit .= "<<<".$data.">>>\n";
		}
		if($errors) {
			$forums->main_msg = '<strong>'.$forums->lang['notfindtemplate'].'</strong>';
			$forums->main_msg .= "<br />".implode("<br />", $errors);
			return $this->editgroupbit($_INPUT['type']);
		}
		if ($_INPUT['title'] == '' OR $_INPUT['templategroup'] == '') {
			$forums->admin->print_cp_error($forums->lang['inputallforms']);
		}
		$noheader = intval($_INPUT['useheader']) == 1 ? 0 : 1;
		$checkgrouptitle = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."templategroup WHERE title = '".$_INPUT['title']."'" );
		if( $_INPUT['type'] == 'edit' ) {
			if ( $_INPUT['id'] == '' ) {
				$forums->admin->print_cp_error($forums->lang['notfindassocgroup']);
			}
			if ( !$group = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."templategroup WHERE templategroupid = ".$_INPUT['id']."" ) ) {
				$forums->admin->print_cp_error($forums->lang['notfindassocgroup']);
			}
			if( $_INPUT['delete']) {
				$t = $DB->query( "SELECT styleid FROM ".TABLE_PREFIX."style" );
				while ($styleid = $DB->fetch_array($t)) {
					@unlink( ROOT_PATH."cache/templates/cacheid_".$styleid['styleid']."/".$group['title'].".php" );
				}
				$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."templategroup WHERE title = '".$group['title']."'" );
				$forums->admin->redirect( "style.php?do=tools", $forums->lang['assocgroupdeleted'], $forums->lang['assocgroupdeleteddesc'] );
			} else {
				$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."templategroup SET templategroup='".$templatebit."', title = '".$_INPUT['title']."', noheader = ".$noheader." WHERE templategroupid = '".$group['templategroupid']."'" );
			}
		} else {
			$DB->query_unbuffered( "INSERT INTO ".TABLE_PREFIX."templategroup
									(title, noheader, templategroup)
									VALUES
									('".$_INPUT['title']."', $noheader, '".$templatebit."')"
								);
		}
		$styleids = $DB->query( "SELECT styleid,parentlist FROM ".TABLE_PREFIX."style" );
		while ($styleid = $DB->fetch_array($styleids)) {
			$forums->cache_func->recachetemplates( $styleid['styleid'], $styleid['parentlist'], $_INPUT['title'] );
		}
		$forums->admin->redirect( "style.php?do=tools", $forums->lang['assocgroupupdated'], $forums->lang['assocgroupupdateddesc'] );
	}

	function do_edit()
	{
		global $forums, $DB, $_INPUT;
		$ids    = array();
		$cb_ids = array();
		require_once( ROOT_PATH.'includes/adminfunctions_template.php' );
		$this->template = new adminfunctions_template();
		foreach ($_INPUT AS $key => $value) {
			if ( preg_match( "/^txt(\d+)$/", $key, $match ) ) {
				if ($_INPUT[$match[0]]) {
					$ids[]    = $match[1];
					$cb_ids[ $match[1] ] = 'cb_'.$match[1];
				}
			}
		}
 		if ( count($ids) < 1 ) {
 			$forums->admin->print_cp_error($forums->lang['noselecttemplate']);
 		}
		$t = $DB->query( "SELECT * FROM ".TABLE_PREFIX."template WHERE tid IN (".implode(",",$ids).")" );
		while ( $r = $DB->fetch_array($t) ) {
			$template[ $r['tid'] ] = $r;
			$real_name = $r['templategroup'];
		}
		$style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid = ".$_INPUT['id']."" );
		foreach( $ids AS $id ) {
			$text = $forums->func->stripslashes_uni($_POST['txt'.$id]);
			$text = preg_replace("/&#60;/", "<", $text);
			$text = preg_replace("/&#62;/", ">", $text);
			//$text = preg_replace("/&#38;/", "&", $text);
			$text = str_replace( '&#092;n', '\n',$text );
			$text = str_replace( '\\n', '\\\\\\n', $text );
			$text = preg_replace("/\r/", "", $text);
			if ( $template[ $id ]['styleid'] == $_INPUT['id'] ) {
				$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."template SET template='".addslashes($text)."' WHERE tid =".$id."" );
			} else {
				$forums->func->fetch_query_sql( array (
					'styleid' => $_INPUT['id'],
					'templategroup' => $template[$id]['templategroup'],
					'template' => $text,
					'title' => $template[$id]['title']
				), 'template');
				if ($_INPUT['type'] == 'single' ) {
					$_INPUT['tid'] = $DB->insert_id();
				} else {
					$cb_ids[ $id ] = 'cb_'.$DB->insert_id();
					unset($_INPUT['cb_'.$id ]);
				}
			}
		}
		$forums->func->cache_styles();

		$tgroups = $DB->query( "SELECT * FROM ".TABLE_PREFIX."templategroup WHERE templategroup LIKE ('%<<<".trim($template[ $id ]['title']).">>>%')" );
		while ( $t = $DB->fetch_array($tgroups) ) {
			foreach($forums->func->stylecache AS $s) {
				if ( preg_match("/,".$style['styleid'].",/i", ",".$s['parentlist'].",") ) {
					$forums->cache_func->recachetemplates( $s['styleid'], $s['parentlist'], $t['title'] );
				}
			}
		}
		$templates = $DB->query( "SELECT * FROM ".TABLE_PREFIX."template WHERE template LIKE ('%{template:".trim($template[ $id ]['title'])."}%')" );
		while ( $template = $DB->fetch_array($templates) ) {
			$tgroups = $DB->query( "SELECT * FROM ".TABLE_PREFIX."templategroup WHERE templategroup LIKE ('%<<<".trim($template['title']).">>>%')" );
			while ( $t = $DB->fetch_array($tgroups) ) {
				foreach($forums->func->stylecache AS $s) {
					if ( preg_match("/,".$style['styleid'].",/i", ",".$s['parentlist'].",") ) {
						$forums->cache_func->recachetemplates( $s['styleid'], $s['parentlist'], $t['title'] );
					}
				}
			}
		}
		if ( ! $_INPUT['savereload'] ) {
			$forums->admin->redirect( "template.php?do=edit&amp;id={$_INPUT['id']}&amp;expand={$real_name}#{$real_name}", $forums->lang['templateupdated'], $forums->lang['templateupdateddesc'] );
		} else {
			$forums->main_msg = $forums->lang['templateupdated'];
			foreach( $cb_ids AS $i => $cb ) {
				$_INPUT[ $cb ] = 1;
			}
			$this->edittemplatebit();
		}
	}

	function addtemplatebit()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle  = $forums->lang['addnewtemplate'];
		$groupname = $_INPUT['expand'];
		$styles = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$forums->admin->nav[] = array( "template.php?{$forums->sessionurl}do=edit&amp;id={$_INPUT['id']}&amp;expand={$groupname}#{$groupname}", $styles['title'] );
		$forums->admin->nav[] = array( '', $forums->lang['addnewtemplate'].' - '.$styles['title'] );
		$forums->admin->print_cp_header($pagetitle, $detail);
		$grouptitles = $forums->cache_func->_get_templates( $styles['parentlist'], 'groups' );
		$formatted_groups = array();
		foreach ( $grouptitles AS $name => $d ) {
			$formatted_groups[] = array( $d['templategroup'], $d['templategroup'] );
		}
		$forums->admin->print_form_header( array( 1 => array( 'do', 'doadd' ),
															   2 => array( 'id', $_INPUT['id'] ),
															   3 => array( 'expand', $_INPUT['expand'] ),
													  )  , "theform"    );
		$forums->admin->columns[] = array( "", "25%" );
		$forums->admin->columns[] = array( "", "75%" );
		$forums->admin->print_table_start( $forums->lang['addnewtemplate'] );
		$forums->admin->print_cells_row( array(
														"<strong>".$forums->lang['templatename']."</strong><br />".$forums->lang['templatenamedesc'],
														$forums->admin->print_input_row('title', $_INPUT['title'])
											 )      );
		$forums->admin->print_cells_row( array(
														$forums->lang['templateingroup'],
														$forums->admin->print_input_select_row('templategroup', $formatted_groups, $_INPUT['templategroup'] ? $_INPUT['templategroup'] : $_INPUT['expand'] )
											 )      );
		$forums->admin->print_cells_row( array(
														$forums->lang['createnewgroup']."<br />".$forums->lang['createnewgroupdesc'],
														$forums->admin->print_input_row('new_templategroup', $_INPUT['new_templategroup'])
											 )      );
		$forums->admin->print_table_footer();
		$forums->admin->print_table_start( $forums->lang['addnewtemplate'] );
		$forums->admin->print_cells_single_row($forums->admin->print_textarea_row("newtemplate", $_POST['newtemplate'], '100', '40', 'none' ) );
		$forums->admin->print_form_end($forums->lang['addnewtemplate'], '', " <input type='submit' name='savereload' value='".$forums->lang['savereload']."' class='button'>");
		$forums->admin->print_table_footer();
		$forums->admin->print_cp_footer();
	}

	function do_addtemplatebit()
	{
		global $forums, $DB, $_INPUT;
		$styles = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		if ( $_POST['new_templategroup'] ) {
			if ( preg_match( "#[^\w_]#s", $_POST['new_templategroup'] ) ) {
				$forums->main_msg = $forums->lang['templategroupnameerror'];
				$this->addtemplatebit();
			}
		}
		if ( ! $_POST['title'] OR preg_match( "#[^\w_]#s", $_POST['title'] ) ) {
			$forums->main_msg = $forums->lang['templatenameerror'];
			$this->addtemplatebit();
		}
		if ( ! trim($_POST['newtemplate']) ) {
			$forums->main_msg = $forums->lang['templatenotempty'];
			$this->addtemplatebit();
		}
		$new_templategroup = $forums->func->strtolower(trim($_INPUT['new_templategroup']));
		$title = $forums->func->strtolower(trim($_INPUT['title']));
		$templategroup = $new_templategroup ? $new_templategroup : $_INPUT['templategroup'];
		$text = $forums->func->convert_andstr($forums->func->stripslashes_uni($_POST['newtemplate']));
		$text = str_replace(array('&amp;nbsp;', '&amp;lt;', '&amp;gt;'), array('&nbsp;', '&lt;', '&gt;'), $text);
		if ( $row = $DB->query_first( "SELECT tid FROM ".TABLE_PREFIX."template WHERE styleid=".$_INPUT['id']." AND templategroup='".$templategroup."' AND title='".$title."'" ) ) {
			$forums->lang['templateexist'] = sprintf( $forums->lang['templateexist'], $templategroup, $title );
			$forums->main_msg = $forums->lang['templateexist'];
			$this->addtemplatebit();
		}
		$forums->func->fetch_query_sql( array ( 'styleid' => $_INPUT['id'],
			'templategroup' => $templategroup,
			'template' => $text,
			'title' => $title,
		), 'template'   );
		$new_id = $DB->insert_id();
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$forums->admin->redirect( "template.php?do=editgroup", $forums->lang['templateadded'], $forums->lang['templateaddeddesc'] );
	}

	function removetemplatebit()
	{
		global $forums, $DB, $_INPUT;
		$tid = intval($_INPUT['tid']);
		$id = intval($_INPUT['id']);
		if ( !$tid ) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$row = $DB->query_first( "SELECT t.*, s.parentlist FROM ".TABLE_PREFIX."template t
		LEFT JOIN ".TABLE_PREFIX."style s ON (s.styleid = t.styleid) WHERE tid=".$tid."" );
		if ( $row['styleid'] == '1' ) {
			$forums->admin->print_cp_error($forums->lang['notdeleteorigtemplate']);
		}
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."template WHERE tid=".$tid."" );
		$templates = $DB->query( "SELECT * FROM ".TABLE_PREFIX."templategroup WHERE templategroup LIKE ('%<<<".$row['title'].">>>%')" );
		while ( $t = $DB->fetch_array($templates) ) {
			$forums->cache_func->recachetemplates( $id, $row['parentlist'], $t['title'] );
		}
		$expand = trim($_INPUT['expand']);
		$forums->admin->redirect( "template.php?do=edit&id=$id&expand=$expand#$expand", $forums->lang['templatereverted'], $forums->lang['templatereverteddesc'] );
	}

	function convert_tags($text="")
	{
		if ($text == "") {
			return "";
		}
		return preg_replace( array("/{?\\\$forums->sessionurl}?/", "/{?\\\$forums->sessionid}?/"), array("{sessionurl}", "{sessionid}"), $text );
	}
}

$output = new template();
$output->show();
?>