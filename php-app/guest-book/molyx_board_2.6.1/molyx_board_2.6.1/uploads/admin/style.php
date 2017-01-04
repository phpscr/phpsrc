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

class style {

	function show()
	{
		global $forums,$DB, $_INPUT, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditstyles']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$this->scount = $DB->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "style");
		if( $this->scount['count'] <= 2 ) {
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "style SET usedefault=1 WHERE styleid != '1' AND usedefault != 1");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "style SET userselect=1, userselect=1 WHERE styleid != '1' AND userselect != 1");
		}
		switch($_INPUT['do']) {
			case 'addset':
				$this->addstyle();
				break;
			case 'edit':
				$this->do_form();
				break;
			case 'doedit':
				$this->savestyle();
				break;
			case 'remove':
				$this->remove_splash();
				break;
			case 'doremove':
				$this->do_remove();
				break;
			case 'export':
				$this->export();
				break;
			case 'revertallform':
				$this->revert_all_form();
				break;
			case 'dorevert':
				$this->do_revert_all();
				break;
			case 'toggledefault':
				$this->set_toggle_default();
				break;
			case 'togglevisible':
				$this->set_visible();
				break;
			case 'colouredit':
				$this->colouredit();
				break;
			case 'docolour':
				$this->do_colouredit();
				break;
			case 'optimize':
				$this->optimize_css();
				break;
			case 'cssedit':
				$this->edit_css();
				break;
			case 'doeditcss':
				$this->save_css();
				break;
			case 'tools':
				$this->style_tools();
				break;
			case 'rebuildcaches':
				$this->rebuildcaches();
				break;
			case 'changeuser':
				$this->change_user();
				break;
			case 'files':
				$this->stylefiles();
				break;
			case 'exportstyle':
				$this->exportstyle();
				break;
			case 'importstyle':
				$this->importstyle();
				break;
			default:
				$this->stylelist();
				break;
		}
	}

	function build_template_parentlists()
	{
		global $DB;
		$styles = $DB->query("SELECT styleid, title, parentlist, parentid FROM " . TABLE_PREFIX . "style ORDER BY parentid");
		while($style = $DB->fetch_array($styles)) {
			$parentlist = $this->fetch_template_parentlist($style['styleid']);
			if ($parentlist != $style['parentlist'])
			{
				$DB->query_unbuffered("
				UPDATE " . TABLE_PREFIX . "style
				SET parentlist = '" . addslashes($parentlist) . "'
				WHERE styleid = ".$style['styleid']
				);

			}
		}
	}

	function fetch_template_parentlist($styleid)
	{
		global $DB;
		$tmp_info = $DB->query_first("SELECT parentid FROM " . TABLE_PREFIX . "style WHERE styleid = $styleid");
		$tmp_array = $styleid;
		if ($tmp_info['parentid'] != 0) {
			$tmp_array .= ',' . $this->fetch_style_parentlist($tmp_info['parentid']);
		}
		if (substr($tmp_array, -1) != '1') {
			$tmp_array .= '1';
		}
		return $tmp_array;
	}

	function fetch_style_parentlist($styleid)
	{
		global $DB, $tmp_cache;
		static $tmp_arraycache;
		if (isset($tmp_arraycache[$styleid]))
		{
			return $tmp_arraycache[$styleid];
		} elseif (isset($tmp_cache[$styleid])) {
			return $tmp_cache[$styleid]['parentlist'];
		} else {
			$tmp_info = $DB->query_first("
				SELECT parentlist
				FROM " . TABLE_PREFIX . "style
				WHERE styleid = $styleid
			");
			$tmp_arraycache[$styleid] = $tmp_info['parentlist'];
			return $tmp_info['parentlist'];
		}
	}

	function stylelist()
	{
		global $forums, $DB;
		$this_style       = "";
		$template_array = array();
		$pagetitle  = $forums->lang['managestyle'];
		$detail = $forums->lang['managestyledesc'];
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$forums->admin->print_cp_header($pagetitle, $detail);
		$DB->query( "SELECT styleid FROM ".TABLE_PREFIX."template WHERE styleid != 1 GROUP BY styleid" );
		while ( $r = $DB->fetch_array() ) {
			$template_array[$r['styleid']] = $r['styleid'];
		}
		$editgroup = $forums->admin->print_button($forums->lang['viewtemplategroup'], "template.php?{$forums->sessionurl}do=editgroup");
		$newgroup = $forums->admin->print_button($forums->lang['addnewtemplategroup'], "template.php?{$forums->sessionurl}do=newgroup");
		echo "<script type='text/javascript'>\n";
		echo "function addnewpop(parentid, menuid)\n";
		echo "{\n";
		echo "if ( menuid )\n";
		echo "{\n";
		echo "togglediv( menuid, 0 );\n";
		echo "}\n";
		echo "document.jsform.id.value = parentid;\n";
		echo "scroll(0,0);\n";
		echo "togglediv( 'popbox', 1 );\n";
		echo "return false;\n";
		echo "}\n";
		echo "</script>\n";
		echo "<div align='center' style='position:absolute;width:99%;display:none;text-align:center' id='popbox'>\n";
		echo "<form name='jsform' action='style.php' method='post'>\n";
		echo "<input type='hidden' name='s' value='".$forums->sessionid."' />\n";
		echo "<input type='hidden' name='do' value='addset' />\n";
		echo "<input type='hidden' name='id' value='' />\n";
		echo "<table cellspacing='0' width='500' align='center' cellpadding='6' style='background:#285270;border:2px outset #629DC5;'>\n";
		echo "<tr>\n";
		echo "<td align='center' valign='top'>\n";
		echo "<strong>".$forums->lang['newstylename']."</strong><br /><input class='textinput' name='title' type='text' size='30' />\n";
		echo "<br />\n";
		echo "<input type='checkbox' name='userselect' value='0' /> ".$forums->lang['cannotselectstyle']."<br /><br />\n";
		echo "<input type='submit' class='button' value='".$forums->lang['addnewstyle']."' name='submitbutton' /> <input type='button' class='button' value='".$forums->lang['close']."' onclick=\"togglediv('popbox');\" />\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n";
		echo "</div>\n";
		echo "<table width='100%' cellspacing='0' cellpadding='0' align='center' border='0'>\n";
		echo "<tr><td class='tableborder'>\n";
		echo "<div style='float:right'><input type='button' name='addnew' class='button' value='".$forums->lang['addnewstyle']."'  onclick=\"addnewpop('1')\" />&nbsp;$editgroup&nbsp;$newgroup&nbsp;</div>\n";
		echo "<div class='catfont'>\n";
		echo "<img src='".$forums->imageurl."/arrow.gif' class='inline' />&nbsp;&nbsp;".$forums->lang['stylesetting']."</div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<div class='tdrow1'>\n";
		$style_sets  = array();
		$lastthreadid    = 0;
		$default_style = "";
		$forums->func->cache_styles();
		foreach( $forums->func->stylecache AS $r ) {
			$div_start    = "<div style='padding-top:3px;padding-bottom:3px;border-bottom:1px solid #629DC5;'>&nbsp;";
			$default      = "<a href='style.php?{$forums->sessionurl}do=toggledefault&amp;id={$r['styleid']}' title='".$forums->lang['setdefaultstyle']."'><img src='{$forums->imageurl}/style_notdefault.gif' border='0' alt='".$forums->lang['setdefaultstyle']."' /></a>";
			$hidden       = "<a href='style.php?{$forums->sessionurl}do=togglevisible&amp;id={$r['styleid']}' title='".$forums->lang['usercanselect']."'><img src='{$forums->imageurl}/style_visible.gif' border='0' alt='visible' title='".$forums->lang['usercanselect']."' /></a>";
			$foldericon  = 'style_folder.gif';
			$menu_text    = $forums->lang['masterstyle'];
			$remove_set   = "<a style='text-decoration:none;font-weight:bold' href='style.php?{$forums->sessionurl}do=remove&amp;id={$r['styleid']}'>".$forums->lang['deletethisstyle']."</a>";
			$restore_all = "";
			$margin_left = '40';
			$css_extra = "";
			$newstyle = "";
			$this->unaltered = "<img src='{$forums->imageurl}/style_unaltered.gif' border='0' alt='-' title='".$forums->lang['styleunaltered']."' />&nbsp;";
			$this->altered = "<img src='{$forums->imageurl}/style_altered.gif' border='0' alt='+' title='".$forums->lang['stylealtered']."' />&nbsp;";
			$this->inherited = "<img src='{$forums->imageurl}/style_inherited.gif' border='0' alt='|' title='".$forums->lang['styleinherited']."' />&nbsp;";
			if ($r['userselect'] == 0) {
				$hidden = "<a href='style.php?{$forums->sessionurl}do=togglevisible&amp;id={$r['styleid']}' title='".$forums->lang['usercannotselect']."'><img src='{$forums->imageurl}/style_invisible.gif' border='0' alt='' title='".$forums->lang['usercannotselect']."' /></a>";
				$foldericon = 'style_folder_hidden.gif';
				$css_extra   = 'color:#7F7FAA';
			}
			if ($r['usedefault'] == 1) {
				$default = "<img src='{$forums->imageurl}/style_default.gif' border='0' alt='' title='".$forums->lang['defaultstyle']."' />";
				$default_style = $r['title'];
			}
			$parentlist = explode(',', $r['parentlist']);
			foreach ($parentlist AS $k) {
				if (in_array($k, $template_array)) {
					$parentid = 1;
					if ($k['css']) {
						$parent_cssid = 1;
					}
				}
			}
			$templates_icon = $this->get_template_status( $template_array[ $r['styleid'] ], $parentid );
			$css_icon       = $this->get_template_status( $r['css'], $parent_cssid );
			$depth = $forums->func->construct_depth_mark($r['depth'], '- - ');
			$forums->lang['editstyletemplates'] = sprintf( $forums->lang['editstyletemplates'], $r['title'] );
			$forums->lang['editstylecss'] = sprintf( $forums->lang['editstylecss'], $r['title'] );
			$forums->lang['editstylecolour'] = sprintf( $forums->lang['editstylecolour'], $r['title'] );
			echo "<div style='padding:4px;border-bottom:1px solid #0C5280;'>\n";
			echo "<div style='float:right'>\n";
			echo $hidden."\n";
			echo $default."\n";
			echo "</div>\n";
			echo "<!--$i_sets,$no_sets-->{$depth} <!--ID:{$r['styleid']}--><img src='{$forums->imageurl}/{$foldericon}' title='".$forums->lang['styleid']." {$r['styleid']}' style='vertical-align:middle' />&nbsp;&nbsp;<strong><a onclick=\"toggleview('menu_{$r['styleid']}'); return false;\" title='".$forums->lang['viewstyleoptions']."' href='#' style='font-size:12px;{$css_extra}'>[".$forums->func->stripslashes_uni($r['title'])."]</a></strong>\n";
			echo "<div onmouseover=\"togglediv('menu_{$r['styleid']}', 1)\" onmouseout=\"togglediv('menu_{$r['styleid']}')\" id='menu_{$r['styleid']}' style='margin-left:{$margin_left}px;display:none;background:#285270;border:1px solid #142938;position:absolute;width:auto;padding:3px 5px 3px 3px;'>\n";
			echo $div_start.$templates_icon."<a style='text-decoration:none;font-weight:bold' href='template.php?{$forums->sessionurl}do=edit&amp;id={$r['styleid']}'>".$forums->lang['editstyletemplates']."</a></div>\n";
			echo $div_start.$css_icon."<a style='text-decoration:none;font-weight:bold' href='style.php?{$forums->sessionurl}do=cssedit&amp;id={$r['styleid']}'>".$forums->lang['editstylecss']."</a></div>\n";
			echo $div_start.$css_icon."<a style='text-decoration:none;font-weight:bold' href='style.php?{$forums->sessionurl}do=colouredit&amp;id={$r['styleid']}'>".$forums->lang['editstylecolour']."</a></div>\n";
			echo "<div style='padding-top:1px;padding-bottom:1px;border-bottom:1px solid #DDD;'>&nbsp;<a style='text-decoration:none;font-weight:bold' href='style.php?{$forums->sessionurl}do=edit&amp;id={$r['styleid']}'>".$forums->lang['editstylesettings']."</a></div>\n";
			echo "<div style='padding-top:1px;padding-bottom:1px;border-bottom:1px solid #DDD;'>&nbsp;<a style='text-decoration:none;font-weight:bold' href='style.php?{$forums->sessionurl}do=rebuildcaches&amp;styleid={$r['styleid']}'>".$forums->lang['rebuildstylecaches']."</a></div>\n";
			echo "<div style='padding-top:1px;padding-bottom:1px;border-bottom:1px solid #DDD;'>&nbsp;<a style='text-decoration:none;font-weight:bold' href='style.php?{$forums->sessionurl}do=optimize&amp;id={$r['styleid']}'>".$forums->lang['optimizecss']."</a></div>\n";
			echo "<div style='padding-top:1px;padding-bottom:1px;border-bottom:1px solid #DDD;'>&nbsp;<a style='text-decoration:none;font-weight:bold' href='style.php?{$forums->sessionurl}do=revertallform&amp;id={$r['styleid']}'>".$forums->lang['revertallform']."</a></div>\n";
			echo "<div style='padding-top:1px;padding-bottom:1px;border-bottom:1px solid #DDD;'>&nbsp;<a style='text-decoration:none;font-weight:bold' href='#' onclick=\"addnewpop('{$r['styleid']}','menu_{$r['styleid']}')\">".$forums->lang['addnewsubstyle']."</a></div>\n";
			echo "<div style='padding-top:1px;'>&nbsp;{$remove_set}</div>\n";
			echo "</div>\n";
			echo "</div>";
		}
		echo "</div><br /><div><strong>".$forums->lang['stylemenudesc'].":</strong><br />\n";
		echo "{$this->altered} ".$forums->lang['hasstylealtered']."\n";
		echo "<br />{$this->unaltered} ".$forums->lang['hasstyleunaltered']."\n";
		echo "<br />{$this->inherited} ".$forums->lang['hasstyleinherited']."\n";
		echo "</div>\n";
		$forums->admin->print_cp_footer();
	}

	function set_toggle_default()
	{
		global $forums, $DB, $_INPUT;
		$affected_ids = array();
		$children     = array();
		$message      = array();
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ( $_INPUT['id'] == 1 ) {
			$forums->main_msg = $forums->lang['cannotchangedstyle'];
			$this->stylelist();
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET usedefault=0" );
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET usedefault=1, userselect=1 WHERE styleid =".$_INPUT['id']."" );
		$forums->cache_func->rebuildallcaches( array( $_INPUT['id'] ) );
		$forums->main_msg = $forums->lang['stylesetdefault'];
		$this->stylelist();
	}

	function set_visible()
	{
		global $forums, $DB, $_INPUT;
		$affected_ids = array();
		$children     = array();
		$message      = array();
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ( $_INPUT['id'] == 1 ) {
			$forums->admin->print_cp_error($forums->lang['stylesetdefault']);
		}
		if ( $this->scount['count'] <= 2 ) {
			$forums->admin->print_cp_error( $forums->lang['cannotchangedlstyle'] );
		}
		$style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$userselect = 0;
		if ( ! $style['userselect'] ) {
			$userselect = 1;
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET userselect=".$userselect." WHERE styleid =".$_INPUT['id']."" );
		$forums->cache_func->rebuildallcaches( array( $_INPUT['id'] ) );
		$forums->main_msg = $forums->lang['stylevisiblechanged'];
		$this->stylelist();
	}

	function revert_all_form()
	{
		global $forums, $DB, $_INPUT;
		$templates = 0;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ( $_INPUT['id'] == 1 ) {
			$forums->main_msg = $forums->lang['cannotchangedstyle'];
			$this->stylelist();
		}
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$forums->admin->nav[] = array( '' ,$forums->lang['revertcustomsetting'].' '.$this_style['title'] );
		$pagetitle  = $forums->lang['deletecustomsetting'];
		$detail = "<strong>".$forums->lang['cannotrevert']."</strong>";
		$forums->admin->print_cp_header($pagetitle, $detail);
		$r = $DB->query_first( "SELECT count(*) as tcount FROM ".TABLE_PREFIX."template WHERE styleid='".$_INPUT['id']."'" );
		$templates = intval($r['tcount']);
		$this_style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$forums->admin->print_form_header( array( 1 => array( 'do', 'dorevert' ),
																 2 => array( 'id'  , $_INPUT['id'] ),
														) );
		$none = "<em>".$forums->lang['nocustonsetting']."</em>";
		$html    = $templates ? $forums->admin->print_yes_no_row('html'   , 0) : $none;
		$css     = $this_style['css'] ? $forums->admin->print_yes_no_row('css'    , 0) : $none;
		$forums->lang['revertalltemplatedesc'] = sprintf( $forums->lang['revertalltemplatedesc'], $templates );
		echo "<table width='100%' cellspacing='0' cellpadding='0' align='center' border='0'>\n";
		echo "<tr><td class='tableborder'>\n";
		echo "<div class='catfont'>\n";
		echo "<img src='".$forums->imageurl."/arrow.gif' class='inline' />&nbsp;&nbsp;".$forums->lang['revertcustomsetting']." - {$this_style['title']}</div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<div class='tdrow1'>\n";
		echo "<fieldset class='tdfset'>\n";
		echo "<legend><strong>".$forums->lang['customtemplate']."</strong></legend>\n";
		echo "<table width='100%' cellpadding='5' cellspacing='0' border='0'>\n";
		echo "<tr>\n";
		echo "<td width='40%' class='tdrow1'>".$forums->lang['revertalltemplate']."<br /><span class='description'>".$forums->lang['revertalltemplatedesc']."</span></td>\n";
		echo "<td width='60%' class='tdrow1'>{$html}</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</fieldset>\n";
		echo "<br />\n";
		echo "<fieldset class='tdfset'>\n";
		echo "<legend><strong>".$forums->lang['customcss']."</strong></legend>\n";
		echo "<table width='100%' cellpadding='5' cellspacing='0' border='0'>\n";
		echo "<tr>\n";
		echo "<td width='40%' class='tdrow1'>".$forums->lang['revertcustomcss']."</td>\n";
		echo "<td width='60%' class='tdrow1'>{$css}</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</fieldset>\n";
		echo "<div style='color:yellow;text-align:center;font-size:11px;padding:6px'>".$forums->lang['revertsettingsdesc']."</div>\n";
		echo "</div>\n";
		$forums->admin->print_form_end_standalone($forums->lang['revertsettings']);
		$forums->admin->print_cp_footer();
	}

	function do_revert_all()
	{
		global $forums, $DB, $_INPUT;
		$message = array();
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ( $_INPUT['id'] == 1 ) {
			$forums->main_msg = $forums->lang['cannotchangedstyle'];
			$this->stylelist();
		}
		$id = intval($_INPUT['id']);
		if ( $_INPUT['html'] ) {
			$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."template WHERE styleid=".$id."" );
			$message[] = $forums->lang['templatedeleting'];
		}
		if ( $_INPUT['css'] ) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET css='' WHERE styleid=".$id."" );
			$message[] = $forums->lang['cssdeleting'];
		}
		$forums->cache_func->rebuildallcaches( array( $id ) );
		$forums->main_msg = $forums->lang['templatedeleted'];
		$forums->main_msg .= "<br />".implode("<br />", array_merge( $message, $forums->cache_func->messages) );
		$this->stylelist();
	}

	function addstyle()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$new = array();
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ( $_INPUT['title'] == 1 ) {
			$forums->main_msg = $forums->lang['requirestylename'];
			$this->stylelist();
		}
		if ( $_INPUT['id'] == '1' ) {
			$new['parentid'] = 1;
		} else {
			$new['parentid'] = $_INPUT['id'];
		}
		$this_style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$new['parentid']."" );
		$new['title'] = $_INPUT['title'];
		$new['title'] = str_replace("　"," ",$new['title']);
		if (trim($new['title']) == "") {
			$forums->admin->redirect("style.php", $forums->lang['requirestylename'], $forums->lang['requirestylename'] );
		}
		$new['imagefolder'] = $this_style['imagefolder'];
		$new['userselect'] = $_INPUT['userselect'] == '0' ? 0 : 1;
		$new['usedefault'] = 0;
		$new['csstype'] = $this_style['csstype'];
		$new['csscache'] = $this_style['csscache'];
		$forums->func->fetch_query_sql( $new, 'style' );
		$newid = $DB->insert_id();
		if ( $_INPUT['id'] == 1 ) {
			$new['parentlist'] = $newid.',1';
		} else {
			$new['parentlist'] = $newid.','.$this_style['parentlist'];
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET parentlist='".$new['parentlist']."' WHERE styleid=".$newid."" );
		$forums->cache_func->rebuildallcaches( array( $newid ) );
		$forums->main_msg = '<strong>'.$forums->lang['styleadded'].'</strong>';
		$forums->main_msg .= "<br />".implode("<br />", array_merge( $message, $forums->cache_func->messages) );
		$this->stylelist();
	}

	function remove_splash()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle  = $forums->lang['deletestyle'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$this_style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$forums->admin->print_form_header( array( 1 => array( 'do', 'doremove'                  ),
																 2 => array( 'id'  , $_INPUT['id']      ),
																 3 => array( 'parentid', $this_style['parentid'] ),
																 4 => array( 'parentlist', $this_style['parentlist'] ),
														)      );
		$forums->admin->print_table_start( $forums->lang['deletestyle']." - {$this_style['title']}" );
		$forums->admin->print_cells_row( array( "<div style='color:red;font-weight:bold;font-size:12px'>".$forums->lang['deletestyledesc']."</div>" ) );
		$forums->admin->print_form_submit($forums->lang['confirmdeletestyle']." {$this_style['title']}");
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function do_remove()
	{
		global $forums, $DB, $_INPUT;
		$affected_ids = array();
		$children     = array();
		$message      = array();
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ( $_INPUT['id'] == 1 ) {
			$forums->main_msg = $forums->lang['cannotchangedstyle'];
			$this->stylelist();
		}
		$this_style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		if ( $this_style['usedefault'] == 1 ) {
			$forums->main_msg = $forums->lang['cannotdeletelast'];
			$this->stylelist();
		}
		$this_count = $DB->query_first( "SELECT count(*) as count FROM ".TABLE_PREFIX."style" );
		if ( $this_count['count'] == 2 ) {
			$forums->main_msg = $forums->lang['managestyle'];
			$this->stylelist();
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET style = '' WHERE style = ".$_INPUT['id']."" );
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."template WHERE styleid=".$_INPUT['id']."" );
		include(ROOT_PATH . 'lang/lang.php');
		foreach ($lang_list as $lang => $v) {
			@unlink(ROOT_PATH . 'cache/styleid_' . $_INPUT['id'] . '_' . $lang . '.css');
		}
		$message[] = $forums->lang['csscachedeleted'];
		$forums->admin->rm_dir( ROOT_PATH.'cache/templates/cacheid_'.$_INPUT['id'] );
		$message[] = $forums->lang['templatedeleted'];
		$DB->query( "SELECT styleid, parentlist FROM ".TABLE_PREFIX."style" );
		while ( $r = $DB->fetch_array() ) {
			if ( in_array($this_style['styleid'], explode(',', $r['parentlist'] ) ) ) {
				$affected_ids[] = $r['styleid'];
			}
		}
		$this->build_template_parentlists();
		$forums->cache_func->rebuildallcaches($affected_ids);
		$forums->main_msg = $forums->lang['styledeleted'];
		$forums->main_msg .= "<br />".implode("<br />", array_merge( $message, $forums->cache_func->messages) );
		$this->stylelist();
	}

	function do_form()
	{
		global $forums, $DB, $_INPUT;
		$sets     = array();
		$parents  = array( 0=> array( '1', $forums->lang['noparentstyle'] ) );
		$row      = array();
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$row=$DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$forums->func->cache_styles();
		foreach($forums->func->stylecache AS $style) {
			$parents[] = array( $style[styleid], $forums->func->construct_depth_mark($style['depth'], '--').$style[title] );
		}
		$images = array();
		$dh = opendir(ROOT_PATH.'images/');
 		while ( $file = readdir( $dh ) ) {
 			if (($file != ".") && ($file != "..")) {
				if ( is_dir(ROOT_PATH.'images/'.$file) ) {
					if ( preg_match( "/^style_(\d+)$/", $file ) ) {
						$images[] = array( $file, $file );
					}
				}
 			}
 		}
 		closedir( $dh );
		$icons = array();
		$dh = opendir( ROOT_PATH.'images/icons' );
 		while ( $file = readdir( $dh ) ) {
 			if (($file != ".") && ($file != "..")) {
				if ( is_dir(ROOT_PATH . 'images/icons/'.$file) ) {
					$icons[] = array( $file, $file );
				}
 			}
 		}
 		closedir( $dh );
		$smilies = array();
		$dh = opendir(ROOT_PATH . 'images/smiles' );
 		while ( $file = readdir( $dh ) ) {
 			if (($file != ".") && ($file != "..")) {
				if ( is_dir(ROOT_PATH . 'images/smiles/'.$file) ) {
					$smilies[] = array( $file, $file );
				}
 			}
 		}
 		closedir( $dh );
		if ( is_writeable( ROOT_PATH."cache" ) ) {
			$cssextra = $forums->admin->print_yes_no_row('csstype', $row['csstype']);
		} else {
			$cssextra = "<em>".$forums->lang['nowritepermission']."</em>";
		}
		$pagetitle  = $forums->lang['editstylesetting']." - ".$row['title'];
		$detail = $forums->lang['editstylesettingdesc'];
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$forums->admin->nav[] = array( 'style.php?do=edit&amp;id='.$_INPUT['id'], $forums->lang['editstylesetting'] );
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do', 'doedit' ),
																 2 => array( 'id'  , $_INPUT['id'] ),
																 3 => array( 'img' , $row['imagefolder'] ),
																 4 => array( 'd_parentid' , $row['parentid'] ),
																 5 => array( 'css' , $row['csstype']      ),
																 6 => array( 'folder' , $row['imagefolder']      ),
														) );
		$forums->admin->columns[] = array( "&nbsp;"  , "40%" );
		$forums->admin->columns[] = array( "&nbsp;"  , "60%" );
		$forums->admin->print_table_start( $forums->lang['stylebasicsetting'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['styletitle']."</strong>", $forums->admin->print_input_row('title', $row['title']) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['usercanselectstyle']."</strong>", $forums->admin->print_yes_no_row('userselect', $row['userselect']) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['usedefaultstyle']."</strong>", $forums->admin->print_yes_no_row('usedefault', $row['usedefault']) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['parentstyle']."</strong>", $forums->admin->print_input_select_row('parentid', $parents, $row['parentid']) ) );
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array( "&nbsp;"  , "40%" );
		$forums->admin->columns[] = array( "&nbsp;"  , "60%" );
		$forums->admin->print_table_start( $forums->lang['styleoptions'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['csscachefile']."</strong><br /><span class='description'>".$forums->lang['csscachefiledesc']."</span>", $cssextra."<br /><span style='color:yellow'>".$forums->lang['csscachefilewarn']."</span>" ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['styleimagesdir']."</strong><br /><span class='description'>".$forums->lang['styleimagesdirdesc']."</span>", $forums->admin->print_input_select_row('imagefolder', $images, $row['imagefolder']) ) );
		$forums->admin->print_form_submit($forums->lang['editstylesetting']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function savestyle()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ($_INPUT['title'] == "") {
			$forums->admin->print_cp_error($forums->lang['requirestyletitle']);
		}
		$tmp_info = $DB->query_first("SELECT styleid, title, parentlist FROM " . TABLE_PREFIX . "style WHERE styleid = ".$_INPUT['parentid']."");
		$parents = explode(',', $tmp_info['parentlist']);
		foreach($parents AS $childid) {
			if ($childid == $_INPUT['id']) {
				$forums->lang['nosameparent'] = sprintf( $forums->lang['nosameparent'], $tmp_info['title'] );
				$forums->admin->print_cp_error($forums->lang['nosameparent']);
			}
		}
		$this_style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$barney = array( 'title'				=> $forums->func->convert_andstr($forums->func->stripslashes_uni($_POST['title'])),
								 'csstype'		=> $_INPUT['csstype'],
								 'userselect'	=> $_INPUT['userselect'],
								 'usedefault'	=> $_INPUT['usedefault'],
								 'imagefolder'	=> $_INPUT['imagefolder'],
								 'parentid'		=> $_INPUT['parentid'],
							   );
		$affected_ids = array();
		if ( $_INPUT['css'] != $_INPUT['csstype'] OR $_INPUT['folder'] != $_INPUT['imagefolder'] ) {
			$affected_ids[ $this_style['styleid'] ] = $this_style['styleid'];
		}
		if ($_POST['usedefault'] == 1) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET usedefault=0 WHERE styleid !=".$_INPUT['id']."" );
		}
		$forums->func->fetch_query_sql( $barney, 'style', "styleid=".$_INPUT['id'] );
		if ( $_INPUT['d_parentid'] != $_INPUT['parentid'] ) {
			$this->build_template_parentlists();
		}
		$forums->cache_func->rebuildallcaches($affected_ids);
		$forums->main_msg = $forums->lang['styleupdated'];
		$forums->main_msg .= "<br />".implode("<br />", $forums->cache_func->messages);
		$forums->admin->redirect("style.php", $forums->lang['managestyle'], $forums->lang['styleupdated'] );
	}

	function get_template_status($ctemplate, $ptemplate)
	{
		if ( $ctemplate ) {
			return $this->altered;
		} else if ( $ptemplate ) {
			return $this->inherited;
		} else {
			return $this->unaltered;
		}
	}

	function colouredit()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$found_id      = "";
		$found_content = "";
		$this_style      = "";
		$style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$DB->query( "SELECT *, instr(',".$style['parentlist'].",', concat(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."style WHERE styleid in (".$style['parentlist'].") ORDER BY theorder" );
		while( $row = $DB->fetch_array() ) {
			if ( $row['css'] AND ! $found_id ) {
				$found_id      = $row['styleid'];
				$found_content = $row['css'];
			}
			if ( $_INPUT['id'] == $row['styleid'] ) {
				$this_style = $row;
			}
		}
		$css = str_replace( array('&lt;', '&gt;', '&quot;', '&#039;'), array('<', '>', '"', "'"), $found_content );
		$colours = array();
		preg_match_all( "/([\:\.\#\w\s,]+)\{(.+?)\}/s", $css, $match );
		for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
			$name    = trim($match[1][$i]);
			$content = trim($match[2][$i]);
			$defs = explode( ';', $content );
			if ( count( $defs ) > 0 ) {
				foreach( $defs AS $a ) {
					$a = trim($a);
					if ( $a != "" ) {
						list( $property, $value ) = explode( ":", $a, 2 );
						$property = trim($property);
						$value    = trim($value);
						if ( $property AND $value ) {
							$colours[ $name ][$property] = $value;
						}
					}
				}
			}
		}
		if ( count($colours) < 1 ) {
			$forums->admin->print_cp_error($forums->lang['cssfileerrors']);
		}
		$pagetitle  = $forums->lang['editcsscode'];
		$detail = $forums->lang['editcsscodedesc'];
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$forums->admin->nav[] = array( 'style.php?do=colouredit&amp;id='.$_INPUT['id'],$forums->lang['editcsscode'] );
		$forums->admin->print_cp_header($pagetitle, $detail);
		echo "<script src='" . ROOT_PATH . "clientscripts/colorpicker.js' type='text/javascript'></script>";
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'docolour'   ), 2 => array( 'id', $_INPUT['id'] ), 3 => array( 'parentlist', $style['parentlist'] ) ) );
		$forums->admin->columns[] = array( "&nbsp;", "50%" );
		$forums->admin->columns[] = array( "&nbsp;", "50%" );
		$forums->admin->print_table_start( $forums->lang['csscoloursetting'] );
		ksort($colours);
		$i = -1;
		foreach ( $colours AS $prop => $val ) {
			$name = $prop;
			$md5 = md5($name);
			$forums->admin->print_cells_single_row( $name, "left", "pformstrip" );
			$forums->admin->print_cells_row( array(
														"<fieldset title='".$forums->lang['cssfont']."'><legend>".$forums->lang['cssfont']."</legend>
														<table width='100%' border='0' cellpadding='4' cellspacing='0'>
														<tr><td width='30%'>".$forums->lang['csscolor']."</td><td>".
														     $forums->admin->print_input_row('frm_'.$md5.'_color', $val['color'], '', "id='color_".++$i."' onchange='preview_color(".$i.")'", 8)."&nbsp;&nbsp;<span class='colorpreview' id='preview_".$i."' onclick='open_color_picker(".$i.", event)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"
														."</td></tr>
														<tr><td width='30%'>".$forums->lang['cssfontsize']."</td><td>".
														     $forums->admin->print_input_row('frm_'.$md5.'_font-size', $val['font-size'], '', '', 30)
														."</td></tr>
														<tr><td width='30%'>".$forums->lang['cssfontfamily']."</td><td>".
														     $forums->admin->print_input_row('frm_'.$md5.'_font-family', $val['font-family'], '', '', 30)
														."</td></tr>
														</table></fieldset>",
														"<fieldset title='".$forums->lang['csstablebg']."'><legend>".$forums->lang['csstablebg']."</legend>
														<table width='100%' border='0' cellpadding='4' cellspacing='0'>
														<tr><td width='30%'>".$forums->lang['csscolor']."</td><td>".
														     $forums->admin->print_input_row('frm_'.$md5.'_background-color', $val['background-color'], '', "id='color_".++$i."' onchange='preview_color(".$i.")'", 8)."&nbsp;&nbsp;<span class='colorpreview' id='preview_".$i."' onclick='open_color_picker(".$i.", event)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"
														."</td></tr>
														<tr><td width='30%'>".$forums->lang['cssbackground']."</td><td>".
											 			   $forums->admin->print_input_row('frm_'.$md5.'_background-image', $val['background-image'], '', '', 30)
														."</td></tr>
														<tr><td width='30%'>".$forums->lang['cssborder']."</td><td>".
														     $forums->admin->print_input_row('frm_'.$md5.'_border', $val['border'], '', '', 30)
														."</td></tr>
														</table></fieldset>"
											 )      );
		}
		$forums->admin->print_table_footer();
		echo "<div align='center' class='pformstrip'>\n";
		echo "<input type='submit' name='submit' value=' ".$forums->lang['save']." ' class='button' />\n";
		echo "<input type='submit' name='savereload' value='".$forums->lang['savereload']."' class='button' />\n";
		echo "</div>\n";
		echo "</form><br />\n";
		echo "<div oncontextmenu='switch_color_picker(1); return false' id='colorPicker' onmousewheel='switch_color_picker(event.wheelDelta * -1); return false;' style='position: absolute;display: none'>\n";
		echo "<table class='colorBg' id='colorFeedback' cellSpacing='4' cellPadding='0' width='100%' border='0'>";
		echo "<tr>\n";
		echo "<td><button onclick='col_click(\"transparent\"); return false'><img title=\"'transparent'\" alt='' src='" . ROOT_PATH . "images/editor/colorpicker_transparent.gif' /></button>\n";
		echo "</td><td>\n";
		echo "<table id=colorSurround cellSpacing=0 cellPadding=0 border=0>\n";
		echo "<tr>\n";
		echo "<td id=oldColor onclick=close_color_picker()></td>\n";
		echo "<td id=newColor></td></tr></table></td>\n";
		echo "<td width='100%'><input id=txtColor size=8 /></td>\n";
		echo "<td style='white-space: nowrap'>\n";
		echo "<input id='colorPickerType' type='hidden' value='0' name='colorPickerType' /><button onclick='switch_color_picker(1); return false'><img alt='' src='" . ROOT_PATH . "images/editor/colorpicker_toggle.gif' /></button><button onclick='close_color_picker(); return false'><img alt='' src='" . ROOT_PATH . "images/editor/colorpicker_close.gif' /></button></td></tr></table>\n";
		echo "<table id='swatches' cellSpacing='1' cellPadding='0' border='0' style='cursor: pointer;'>\n";
		echo "<tr>\n";
		echo "<td id=sw0-0 style='background: #000000'></td>\n";
		echo "<td id=sw1-0 style='background: #000000'></td>\n";
		echo "<td id=sw2-0 style='background: #000000'></td>\n";
		echo "<td id=sw3-0 style='background: #ccffff'></td>\n";
		echo "<td id=sw4-0 style='background: #ccffcc'></td>\n";
		echo "<td id=sw5-0 style='background: #ccff99'></td>\n";
		echo "<td id=sw6-0 style='background: #ccff66'></td>\n";
		echo "<td id=sw7-0 style='background: #ccff33'></td>\n";
		echo "<td id=sw8-0 style='background: #ccff00'></td>\n";
		echo "<td id=sw9-0 style='background: #66ff00'></td>\n";
		echo "<td id=sw10-0 style='background: #66ff33'></td>\n";
		echo "<td id=sw11-0 style='background: #66ff66'></td>\n";
		echo "<td id=sw12-0 style='background: #66ff99'></td>\n";
		echo "<td id=sw13-0 style='background: #66ffcc'></td>\n";
		echo "<td id=sw14-0 style='background: #66ffff'></td>\n";
		echo "<td id=sw15-0 style='background: #00ffff'></td>\n";
		echo "<td id=sw16-0 style='background: #00ffcc'></td>\n";
		echo "<td id=sw17-0 style='background: #00ff99'></td>\n";
		echo "<td id=sw18-0 style='background: #00ff66'></td>\n";
		echo "<td id=sw19-0 style='background: #00ff33'></td>\n";
		echo "<td id=sw20-0 style='background: #00ff00'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-1 style='background: #000000'></td>\n";
		echo "<td id=sw1-1 style='background: #333333'></td>\n";
		echo "<td id=sw2-1 style='background: #000000'></td>\n";
		echo "<td id=sw3-1 style='background: #ccccff'></td>\n";
		echo "<td id=sw4-1 style='background: #cccccc'></td>\n";
		echo "<td id=sw5-1 style='background: #cccc99'></td>\n";
		echo "<td id=sw6-1 style='background: #cccc66'></td>\n";
		echo "<td id=sw7-1 style='background: #cccc33'></td>\n";
		echo "<td id=sw8-1 style='background: #cccc00'></td>\n";
		echo "<td id=sw9-1 style='background: #66cc00'></td>\n";
		echo "<td id=sw10-1 style='background: #66cc33'></td>\n";
		echo "<td id=sw11-1 style='background: #66cc66'></td>\n";
		echo "<td id=sw12-1 style='background: #66cc99'></td>\n";
		echo "<td id=sw13-1 style='background: #66cccc'></td>\n";
		echo "<td id=sw14-1 style='background: #66ccff'></td>\n";
		echo "<td id=sw15-1 style='background: #00ccff'></td>\n";
		echo "<td id=sw16-1 style='background: #00cccc'></td>\n";
		echo "<td id=sw17-1 style='background: #00cc99'></td>\n";
		echo "<td id=sw18-1 style='background: #00cc66'></td>\n";
		echo "<td id=sw19-1 style='background: #00cc33'></td>\n";
		echo "<td id=sw20-1 style='background: #00cc00'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-2 style='background: #000000'></td>\n";
		echo "<td id=sw1-2 style='background: #666666'></td>\n";
		echo "<td id=sw2-2 style='background: #000000'></td>\n";
		echo "<td id=sw3-2 style='background: #cc99ff'></td>\n";
		echo "<td id=sw4-2 style='background: #cc99cc'></td>\n";
		echo "<td id=sw5-2 style='background: #cc9999'></td>\n";
		echo "<td id=sw6-2 style='background: #cc9966'></td>\n";
		echo "<td id=sw7-2 style='background: #cc9933'></td>\n";
		echo "<td id=sw8-2 style='background: #cc9900'></td>\n";
		echo "<td id=sw9-2 style='background: #669900'></td>\n";
		echo "<td id=sw10-2 style='background: #669933'></td>\n";
		echo "<td id=sw11-2 style='background: #669966'></td>\n";
		echo "<td id=sw12-2 style='background: #669999'></td>\n";
		echo "<td id=sw13-2 style='background: #6699cc'></td>\n";
		echo "<td id=sw14-2 style='background: #6699ff'></td>\n";
		echo "<td id=sw15-2 style='background: #0099ff'></td>\n";
		echo "<td id=sw16-2 style='background: #0099cc'></td>\n";
		echo "<td id=sw17-2 style='background: #009999'></td>\n";
		echo "<td id=sw18-2 style='background: #009966'></td>\n";
		echo "<td id=sw19-2 style='background: #009933'></td>\n";
		echo "<td id=sw20-2 style='background: #009900'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-3 style='background: #000000'></td>\n";
		echo "<td id=sw1-3 style='background: #999999'></td>\n";
		echo "<td id=sw2-3 style='background: #000000'></td>\n";
		echo "<td id=sw3-3 style='background: #cc66ff'></td>\n";
		echo "<td id=sw4-3 style='background: #cc66cc'></td>\n";
		echo "<td id=sw5-3 style='background: #cc6699'></td>\n";
		echo "<td id=sw6-3 style='background: #cc6666'></td>\n";
		echo "<td id=sw7-3 style='background: #cc6633'></td>\n";
		echo "<td id=sw8-3 style='background: #cc6600'></td>\n";
		echo "<td id=sw9-3 style='background: #666600'></td>\n";
		echo "<td id=sw10-3 style='background: #666633'></td>\n";
		echo "<td id=sw11-3 style='background: #666666'></td>\n";
		echo "<td id=sw12-3 style='background: #666699'></td>\n";
		echo "<td id=sw13-3 style='background: #6666cc'></td>\n";
		echo "<td id=sw14-3 style='background: #6666ff'></td>\n";
		echo "<td id=sw15-3 style='background: #0066ff'></td>\n";
		echo "<td id=sw16-3 style='background: #0066cc'></td>\n";
		echo "<td id=sw17-3 style='background: #006699'></td>\n";
		echo "<td id=sw18-3 style='background: #006666'></td>\n";
		echo "<td id=sw19-3 style='background: #006633'></td>\n";
		echo "<td id=sw20-3 style='background: #006600'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-4 style='background: #000000'></td>\n";
		echo "<td id=sw1-4 style='background: #cccccc'></td>\n";
		echo "<td id=sw2-4 style='background: #000000'></td>\n";
		echo "<td id=sw3-4 style='background: #cc33ff'></td>\n";
		echo "<td id=sw4-4 style='background: #cc33cc'></td>\n";
		echo "<td id=sw5-4 style='background: #cc3399'></td>\n";
		echo "<td id=sw6-4 style='background: #cc3366'></td>\n";
		echo "<td id=sw7-4 style='background: #cc3333'></td>\n";
		echo "<td id=sw8-4 style='background: #cc3300'></td>\n";
		echo "<td id=sw9-4 style='background: #663300'></td>\n";
		echo "<td id=sw10-4 style='background: #663333'></td>\n";
		echo "<td id=sw11-4 style='background: #663366'></td>\n";
		echo "<td id=sw12-4 style='background: #663399'></td>\n";
		echo "<td id=sw13-4 style='background: #6633cc'></td>\n";
		echo "<td id=sw14-4 style='background: #6633ff'></td>\n";
		echo "<td id=sw15-4 style='background: #0033ff'></td>\n";
		echo "<td id=sw16-4 style='background: #0033cc'></td>\n";
		echo "<td id=sw17-4 style='background: #003399'></td>\n";
		echo "<td id=sw18-4 style='background: #003366'></td>\n";
		echo "<td id=sw19-4 style='background: #003333'></td>\n";
		echo "<td id=sw20-4 style='background: #003300'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-5 style='background: #000000'></td>\n";
		echo "<td id=sw1-5 style='background: #ffffff'></td>\n";
		echo "<td id=sw2-5 style='background: #000000'></td>\n";
		echo "<td id=sw3-5 style='background: #cc00ff'></td>\n";
		echo "<td id=sw4-5 style='background: #cc00cc'></td>\n";
		echo "<td id=sw5-5 style='background: #cc0099'></td>\n";
		echo "<td id=sw6-5 style='background: #cc0066'></td>\n";
		echo "<td id=sw7-5 style='background: #cc0033'></td>\n";
		echo "<td id=sw8-5 style='background: #cc0000'></td>\n";
		echo "<td id=sw9-5 style='background: #660000'></td>\n";
		echo "<td id=sw10-5 style='background: #660033'></td>\n";
		echo "<td id=sw11-5 style='background: #660066'></td>\n";
		echo "<td id=sw12-5 style='background: #660099'></td>\n";
		echo "<td id=sw13-5 style='background: #6600cc'></td>\n";
		echo "<td id=sw14-5 style='background: #6600ff'></td>\n";
		echo "<td id=sw15-5 style='background: #0000ff'></td>\n";
		echo "<td id=sw16-5 style='background: #0000cc'></td>\n";
		echo "<td id=sw17-5 style='background: #000099'></td>\n";
		echo "<td id=sw18-5 style='background: #000066'></td>\n";
		echo "<td id=sw19-5 style='background: #000033'></td>\n";
		echo "<td id=sw20-5 style='background: #000000'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-6 style='background: #000000'></td>\n";
		echo "<td id=sw1-6 style='background: #ff0000'></td>\n";
		echo "<td id=sw2-6 style='background: #000000'></td>\n";
		echo "<td id=sw3-6 style='background: #ff00ff'></td>\n";
		echo "<td id=sw4-6 style='background: #ff00cc'></td>\n";
		echo "<td id=sw5-6 style='background: #ff0099'></td>\n";
		echo "<td id=sw6-6 style='background: #ff0066'></td>\n";
		echo "<td id=sw7-6 style='background: #ff0033'></td>\n";
		echo "<td id=sw8-6 style='background: #ff0000'></td>\n";
		echo "<td id=sw9-6 style='background: #990000'></td>\n";
		echo "<td id=sw10-6 style='background: #990033'></td>\n";
		echo "<td id=sw11-6 style='background: #990066'></td>\n";
		echo "<td id=sw12-6 style='background: #990099'></td>\n";
		echo "<td id=sw13-6 style='background: #9900cc'></td>\n";
		echo "<td id=sw14-6 style='background: #9900ff'></td>\n";
		echo "<td id=sw15-6 style='background: #3300ff'></td>\n";
		echo "<td id=sw16-6 style='background: #3300cc'></td>\n";
		echo "<td id=sw17-6 style='background: #330099'></td>\n";
		echo "<td id=sw18-6 style='background: #330066'></td>\n";
		echo "<td id=sw19-6 style='background: #330033'></td>\n";
		echo "<td id=sw20-6 style='background: #330000'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-7 style='background: #000000'></td>\n";
		echo "<td id=sw1-7 style='background: #00ff00'></td>\n";
		echo "<td id=sw2-7 style='background: #000000'></td>\n";
		echo "<td id=sw3-7 style='background: #ff33ff'></td>\n";
		echo "<td id=sw4-7 style='background: #ff33cc'></td>\n";
		echo "<td id=sw5-7 style='background: #ff3399'></td>\n";
		echo "<td id=sw6-7 style='background: #ff3366'></td>\n";
		echo "<td id=sw7-7 style='background: #ff3333'></td>\n";
		echo "<td id=sw8-7 style='background: #ff3300'></td>\n";
		echo "<td id=sw9-7 style='background: #993300'></td>\n";
		echo "<td id=sw10-7 style='background: #993333'></td>\n";
		echo "<td id=sw11-7 style='background: #993366'></td>\n";
		echo "<td id=sw12-7 style='background: #993399'></td>\n";
		echo "<td id=sw13-7 style='background: #9933cc'></td>\n";
		echo "<td id=sw14-7 style='background: #9933ff'></td>\n";
		echo "<td id=sw15-7 style='background: #3333ff'></td>\n";
		echo "<td id=sw16-7 style='background: #3333cc'></td>\n";
		echo "<td id=sw17-7 style='background: #333399'></td>\n";
		echo "<td id=sw18-7 style='background: #333366'></td>\n";
		echo "<td id=sw19-7 style='background: #333333'></td>\n";
		echo "<td id=sw20-7 style='background: #333300'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-8 style='background: #000000'></td>\n";
		echo "<td id=sw1-8 style='background: #0000ff'></td>\n";
		echo "<td id=sw2-8 style='background: #000000'></td>\n";
		echo "<td id=sw3-8 style='background: #ff66ff'></td>\n";
		echo "<td id=sw4-8 style='background: #ff66cc'></td>\n";
		echo "<td id=sw5-8 style='background: #ff6699'></td>\n";
		echo "<td id=sw6-8 style='background: #ff6666'></td>\n";
		echo "<td id=sw7-8 style='background: #ff6633'></td>\n";
		echo "<td id=sw8-8 style='background: #ff6600'></td>\n";
		echo "<td id=sw9-8 style='background: #996600'></td>\n";
		echo "<td id=sw10-8 style='background: #996633'></td>\n";
		echo "<td id=sw11-8 style='background: #996666'></td>\n";
		echo "<td id=sw12-8 style='background: #996699'></td>\n";
		echo "<td id=sw13-8 style='background: #9966cc'></td>\n";
		echo "<td id=sw14-8 style='background: #9966ff'></td>\n";
		echo "<td id=sw15-8 style='background: #3366ff'></td>\n";
		echo "<td id=sw16-8 style='background: #3366cc'></td>\n";
		echo "<td id=sw17-8 style='background: #336699'></td>\n";
		echo "<td id=sw18-8 style='background: #336666'></td>\n";
		echo "<td id=sw19-8 style='background: #336633'></td>\n";
		echo "<td id=sw20-8 style='background: #336600'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-9 style='background: #000000'></td>\n";
		echo "<td id=sw1-9 style='background: #ffff00'></td>\n";
		echo "<td id=sw2-9 style='background: #000000'></td>\n";
		echo "<td id=sw3-9 style='background: #ff99ff'></td>\n";
		echo "<td id=sw4-9 style='background: #ff99cc'></td>\n";
		echo "<td id=sw5-9 style='background: #ff9999'></td>\n";
		echo "<td id=sw6-9 style='background: #ff9966'></td>\n";
		echo "<td id=sw7-9 style='background: #ff9933'></td>\n";
		echo "<td id=sw8-9 style='background: #ff9900'></td>\n";
		echo "<td id=sw9-9 style='background: #999900'></td>\n";
		echo "<td id=sw10-9 style='background: #999933'></td>\n";
		echo "<td id=sw11-9 style='background: #999966'></td>\n";
		echo "<td id=sw12-9 style='background: #999999'></td>\n";
		echo "<td id=sw13-9 style='background: #9999cc'></td>\n";
		echo "<td id=sw14-9 style='background: #9999ff'></td>\n";
		echo "<td id=sw15-9 style='background: #3399ff'></td>\n";
		echo "<td id=sw16-9 style='background: #3399cc'></td>\n";
		echo "<td id=sw17-9 style='background: #339999'></td>\n";
		echo "<td id=sw18-9 style='background: #339966'></td>\n";
		echo "<td id=sw19-9 style='background: #339933'></td>\n";
		echo "<td id=sw20-9 style='background: #339900'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-10 style='background: #000000'></td>\n";
		echo "<td id=sw1-10 style='background: #00ffff'></td>\n";
		echo "<td id=sw2-10 style='background: #000000'></td>\n";
		echo "<td id=sw3-10 style='background: #ffccff'></td>\n";
		echo "<td id=sw4-10 style='background: #ffcccc'></td>\n";
		echo "<td id=sw5-10 style='background: #ffcc99'></td>\n";
		echo "<td id=sw6-10 style='background: #ffcc66'></td>\n";
		echo "<td id=sw7-10 style='background: #ffcc33'></td>\n";
		echo "<td id=sw8-10 style='background: #ffcc00'></td>\n";
		echo "<td id=sw9-10 style='background: #99cc00'></td>\n";
		echo "<td id=sw10-10 style='background: #99cc33'></td>\n";
		echo "<td id=sw11-10 style='background: #99cc66'></td>\n";
		echo "<td id=sw12-10 style='background: #99cc99'></td>\n";
		echo "<td id=sw13-10 style='background: #99cccc'></td>\n";
		echo "<td id=sw14-10 style='background: #99ccff'></td>\n";
		echo "<td id=sw15-10 style='background: #33ccff'></td>\n";
		echo "<td id=sw16-10 style='background: #33cccc'></td>\n";
		echo "<td id=sw17-10 style='background: #33cc99'></td>\n";
		echo "<td id=sw18-10 style='background: #33cc66'></td>\n";
		echo "<td id=sw19-10 style='background: #33cc33'></td>\n";
		echo "<td id=sw20-10 style='background: #33cc00'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td id=sw0-11 style='background: #000000'></td>\n";
		echo "<td id=sw1-11 style='background: #ff00ff'></td>\n";
		echo "<td id=sw2-11 style='background: #000000'></td>\n";
		echo "<td id=sw3-11 style='background: #ffffff'></td>\n";
		echo "<td id=sw4-11 style='background: #ffffcc'></td>\n";
		echo "<td id=sw5-11 style='background: #ffff99'></td>\n";
		echo "<td id=sw6-11 style='background: #ffff66'></td>\n";
		echo "<td id=sw7-11 style='background: #ffff33'></td>\n";
		echo "<td id=sw8-11 style='background: #ffff00'></td>\n";
		echo "<td id=sw9-11 style='background: #99ff00'></td>\n";
		echo "<td id=sw10-11 style='background: #99ff33'></td>\n";
		echo "<td id=sw11-11 style='background: #99ff66'></td>\n";
		echo "<td id=sw12-11 style='background: #99ff99'></td>\n";
		echo "<td id=sw13-11 style='background: #99ffcc'></td>\n";
		echo "<td id=sw14-11 style='background: #99ffff'></td>\n";
		echo "<td id=sw15-11 style='background: #33ffff'></td>\n";
		echo "<td id=sw16-11 style='background: #33ffcc'></td>\n";
		echo "<td id=sw17-11 style='background: #33ff99'></td>\n";
		echo "<td id=sw18-11 style='background: #33ff66'></td>\n";
		echo "<td id=sw19-11 style='background: #33ff33'></td>\n";
		echo "<td id=sw20-11 style='background: #33ff00'></td>\n";
		echo "</tr></table></div>\n";
		echo "<script type=text/javascript>\n";
		echo "<!--\n";
		echo "var numcolors = ".++$i.";\n";
		echo "var colorPickerWidth = 253;\n";
		echo "var colorPickerType = 0;\n";
		echo "var tds = my_getbyid('swatches').getElementsByTagName('td');\n";
		echo "for (var i = 0; i < tds.length; i++)\n";
		echo "{\n";
		echo "tds[i].onclick = swatch_click;\n";
		echo "tds[i].onmouseover = swatch_over;\n";
		echo "}\n";
		echo "//-->\n";
		echo "</script>\n";
		echo "<script type='text/javascript'>init_color_preview();</script>\n";
		$forums->admin->print_cp_footer();
	}

	function do_colouredit()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$found_id = "";
		$found_content = "";
		$this_style = "";
		$style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$DB->query( "SELECT *, instr(',".$style['parentlist'].",', concat(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."style WHERE styleid in (".$style['parentlist'].") ORDER BY theorder" );
		while( $row = $DB->fetch_array() ) {
			if ( $row['css'] AND ! $found_id ) {
				$found_id      = $row['styleid'];
				$found_content = $row['css'];
			}
			if ( $_INPUT['id'] == $row['styleid'] ) {
				$this_style = $row;
			}
		}
		$css = $found_content;
		$colours = array();
		preg_match_all( "/([\:\.\#\w\s,]+)\{(.+?)\}/s", $css, $match );
		for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
			$name    = trim($match[1][$i]);
			$content = trim($match[2][$i]);
			$md5     = md5($name);
			$defs    = explode( ';', $content );
			if ( count( $defs ) > 0 ) {
				foreach( $defs AS $a ) {
					$a = trim($a);
					if ( $a != "" ) {
						list( $property, $value ) = explode( ":", $a, 2 );
						$property = trim($property);
						$value    = trim($value);
						if ( $property AND $value ) {
							if ( $property != 'color' AND $property != 'font-size' AND $property != 'font-family' AND $property != 'background-color' AND $property != 'border' AND $property != 'background-image' ) {
								$colours[ $name ][$property] = $value;
							}
						}
					}
				}
			}
			foreach( array( 'color', 'font-size', 'font-family', 'background-color', 'border', 'background-image' ) AS $prop ) {
				if ( isset($_INPUT['frm_'.$md5.'_'.$prop]) ) {
					$colours[ $name ][$prop] = stripslashes($_INPUT['frm_'.$md5.'_'.$prop]);
				}
			}
		}
		if ( count($colours) < 1 ) {
			$forums->admin->print_cp_error($forums->lang['cssfileerrors']);
		}
		unset($name);
		unset($property);
		$final = "";
		foreach( $colours AS $name => $property ) {
			$final .= $name." { ";
			if ( is_array($property) AND count($property) > 0 ) {
				foreach( $property AS $key => $value ) {
					if ( $key AND $value ) {
						$final .= $key.": ".$value.";";
					}
				}
			}
			$final .= " }\n";
		}
		if ($final == "") {
			$forums->admin->print_cp_error($forums->lang['cannotemptycss']);
		}
		$css = stripslashes($final);
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET css='".$css."' WHERE styleid=".$_INPUT['id']."" );
		$extra = "<strong>".$forums->lang['cssupdated']."</strong>";
		$message = $forums->cache_func->writecsscache( $_INPUT['id'], $_INPUT['parentlist'] );
		if ( ! $_INPUT['savereload'] ) {
			$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
			$forums->main_msg = $forums->lang['cssupdated'].": $extra";
			$forums->admin->redirect( "style.php", $forums->lang['cssupdated'], $forums->lang['csshasupdated'] );
		} else {
			$forums->main_msg = $forums->lang['cssupdated'].": $extra";
			$this->colouredit();
		}
	}

	function optimize_css()
	{
		global $forums, $DB, $_INPUT;
		if ( $_INPUT['id'] == "" OR !$style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" ) ) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$DB->query( "SELECT *, instr(',".$style['parentlist'].",', concat(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."style WHERE styleid in (".$style['parentlist'].") ORDER BY theorder" );
		while( $row = $DB->fetch_array() ) {
			if ( $row['css'] AND ! $found_id ) {
				$found_id      = $row['styleid'];
				$found_content = $row['css'];
			}
			if ( $_INPUT['id'] == $row['styleid'] ) {
				$this_style = $row;
			}
		}
		$found_content = $forums->func->unhtmlspecialchars($found_content);
		$orig_size = strlen($found_content);
		$orig_text = str_replace( array("\r\n", "\r", "\n\n"), array("\n", "\n", "\n"), $found_content);
		$parsed = array();
		$orig_text = preg_replace( "#/\*(.+?)\*/#s", "", $orig_text );
		preg_match_all( "/(.+?)\{(.+?)\}/s", $orig_text, $match, PREG_PATTERN_ORDER );
		for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
			$match[1][$i] = trim($match[1][$i]);
			$parsed[ $match[1][$i] ] = trim($match[2][$i]);
		}
		if ( count($parsed) < 1) {
			$forums->admin->print_cp_error($forums->lang['cannotanalyzecss']);
		}
		$final = "";
		foreach( $parsed AS $name => $p ) {
			if ( preg_match( "#^//#", $name) ) {
				continue;
			}
			$parts = explode( ";", $p);
			$defs  = array();
			foreach( $parts AS $part ) {
				if (trim($part) != "") {
					list($definition, $data) = explode( ":", $part );
					$defs[]   = trim($definition).": ".trim($data);
				}
			}
			$final .= $name . " { ".implode("; ", $defs). " }\n";
		}
		$final_size = strlen($final);
		if ($final_size < 1000) {
			$forums->admin->print_cp_error($forums->lang['cannotdealcss']);
		}
		$css = stripslashes($final);
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."style SET css='".$forums->func->htmlspecialchars_uni($css)."' WHERE styleid='".$_INPUT['id']."'");
		$saved    = $orig_size - $final_size;
		$pc_saved = 0;
		if ($saved > 0) {
			$pc_saved = sprintf( "%.2f", ($saved / $orig_size) * 100);
		}
		$forums->lang['cssoptimized'] = sprintf( $forums->lang['cssoptimized'], $saved, $pc_saved );
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$forums->admin->redirect( "style.php", $forums->lang['cssupdated'], $forums->lang['cssoptimized'] );
	}

	function edit_css()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$found_id      = "";
		$found_content = "";
		$this_style      = "";
		$style = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."style WHERE styleid=".$_INPUT['id']."" );
		$DB->query( "SELECT *, instr(',".$style['parentlist'].",', concat(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."style WHERE styleid in (".$style['parentlist'].") ORDER BY theorder" );
		while( $row = $DB->fetch_array() ) {
			if ( $row['css'] AND ! $found_id ) {
				$found_id      = $row['styleid'];
				$found_content = $row['css'];
			}
			if ( $_INPUT['id'] == $row['styleid'] ) {
				$this_style = $row;
			}
		}
		$css = $found_content;
		$css_elements = array();
		preg_match_all( "/(\.|\#)(\S+?)\s{0,}\{.+?\}/s", $css, $match );
		for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
			$type = trim($match[1][$i]);
			$name = trim($match[2][$i]);
			if ($type == '.') {
				$css_elements[] = array( 'class|'.$name, $type.$name );
			} else {
				$css_elements[] = array( 'id|'.$name, $type.$name );
			}
		}
		$forums->lang['editcsssetting'] = sprintf( $forums->lang['editcsssetting'], $this_style['title'] );
		$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
		$forums->admin->nav[] = array( '' ,$forums->lang['editcsssetting'] );
		$pagetitle  = $forums->lang['managecss'];
		$detail = $forums->lang['managecssdesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do', 'doeditcss' ), 2 => array( 'id', $_INPUT['id'] ), 3 => array( 'parentlist', $style['parentlist'] ) ), "theform" );
		$forums->admin->print_table_start( $forums->lang['editcsssetting'] );
		$forums->admin->print_cells_single_row($forums->admin->print_textarea_row("txtcss", $css, '100', '40', 'none', "txt{$data['textareaname']}" ) );
		$forums->admin->print_form_submit($forums->lang['savecss'], '', " <input type='submit' name='savereload' value='".$forums->lang['savereload']."' class='button' /> <input type='button' onclick=\"pop_win('css.html','".$forums->lang['view']."', 600,600)\" value='".$forums->lang['customnewcss']."' class='button' />");
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function stylefiles()
	{
		global $forums;
		$forums->func->cache_styles();
		foreach($forums->func->stylecache AS $style) {
			$styleid[] = array( $style[styleid], $forums->func->construct_depth_mark($style['depth'], '--').$style[title] );
		}
		$title  = $forums->lang['styleimextools'];
		$detail = $forums->lang['styleimextoolsdesc'];
		$forums->admin->nav[] = array( 'style.php?do=tools' ,$forums->lang['managestyle'] );
		$forums->admin->print_cp_header($title, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do' , 'exportstyle'  ), 'export' ) );
		$forums->admin->columns[] = array( "&nbsp;"  , "40%" );
		$forums->admin->columns[] = array( "&nbsp;"  , "60%" );
		$forums->admin->print_table_start( $forums->lang['styleexport'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['selectexportstyle']."</strong>", $forums->admin->print_input_select_row('styleid', $styleid, '') ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['stylesavefilename']."</strong>", $forums->admin->print_input_row('filename', 'MolyX-style.xml', '', '', 30) ) );
		$type_array = array(
							  0 => array( '0', $forums->lang['onlyforthisstyle'] ),
							  1 => array( '1',  $forums->lang['includeparentstyle']  ),
						   );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['exportoptions']."</strong>", $forums->admin->print_input_select_row('export_type', $type_array) ) );
		$forums->admin->print_form_submit($forums->lang['styleexport']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$mergestyle = array_merge( array( 0=> array( -1, $forums->lang['createnewstyle'] ) ), $styleid );
		$parentstyle = array_merge( array( 0=> array( 1, $forums->lang['noassociateparent'] ) ), $styleid );
		$forums->admin->print_form_header( array( 1 => array( 'do', 'importstyle' ) ) , "uploadform", " enctype='multipart/form-data' onsubmit='return confirmupload(this, this.fromlocal);'" );
		$forums->admin->columns[] = array( "&nbsp;"  , "40%" );
		$forums->admin->columns[] = array( "&nbsp;"  , "60%" );
		$forums->admin->print_table_start( $forums->lang['styleimport'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['uploadlocalstyle']."</strong>", $forums->admin->print_input_row('fromlocal', '', 'file', '', 30) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['uploadserverstyle']."</strong>", $forums->admin->print_input_row('fromserver', ROOT_PATH . 'MolyX-style.xml', '', '', 30) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['mergecurrentstyle']."</strong><br /><span class='description'>".$forums->lang['mergecurrentstyledesc']."</span>", $forums->admin->print_input_select_row('mergestyle', $mergestyle) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['ignorestyleversion']."</strong><br /><span class='description'>".$forums->lang['ignorestyleversiondesc']."</span>", $forums->admin->print_yes_no_row('checkversion', 0) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['importstylename']."</strong><br /><span class='description'>".$forums->lang['importstylenamedesc']."</span>", $forums->admin->print_input_row('changetitle', '', '', '', 30) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['parentstyle']."</strong>", $forums->admin->print_input_select_row('parentstyle', $parentstyle) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['usedefaultstyle']."</strong>", $forums->admin->print_yes_no_row('usedefault', 0) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['usercanselectstyle']."</strong>", $forums->admin->print_yes_no_row('userselect', 1) ) );
		$forums->admin->print_form_submit($forums->lang['styleimport']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function exportstyle()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		@set_time_limit(1200);
		$styleid = intval($_INPUT['styleid']);
		$filename = trim($_INPUT['filename']);
		$type = intval($_INPUT['export_type']);
		$templates = array();
		if (empty($filename)) {
			$filename = 'MolyX-style.xml';
		}
		$style = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "style WHERE styleid = $styleid");
		if ($type == 1) {
			$extra = ", instr(',".$style['parentlist'].",', concat(',',styleid,',') ) as theorder";
			$order = " ORDER BY theorder";
			$sqlcondition = "styleid <> 1 AND styleid in (".$style['parentlist'].")";
		} else {
			$extra = '';
			$order = '';
			$sqlcondition = "styleid = $styleid";
		}
		$title = $style['title'];
		$getstyles = $DB->query_first( "SELECT *$extra FROM ".TABLE_PREFIX."style WHERE css != '' AND $sqlcondition$order LIMIT 0,1" );
		$templates['StyleVars Setting'][] = array('title' => 'imagefolder', 'template' => $getstyles['imagefolder'], 'templatetype' => 'stylevars');

		if (!empty($getstyles['css'])) {
			$getstyles['css'] = str_replace( array('&lt;', '&gt;', '&quot;', '&#039;'), array('<', '>', '"', "'"), $getstyles['css'] );
			preg_match_all( "/(.+?)\{(.+?)\}/s", $getstyles['css'], $match, PREG_PATTERN_ORDER );
			for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
				$match[1][$i] = trim($match[1][$i]);
				$parsed[ $match[1][$i] ] = trim($match[2][$i]);
			}
			$final = "";
			foreach( $parsed AS $name => $p ) {
				if ( preg_match( "#^//#", $name) ) {
					continue;
				}
				$parts = explode( ";", $p);
				foreach( $parts AS $key  ) {
					$key = trim($key);
					if (empty($key)) continue;
					list( $property, $value ) = explode( ":", $key, 2 );
					$property = trim($property);
					$value    = trim($value);
					$new_arr[$property] = $value;
				}
				$css['title']= $name;
				$css['template'] = serialize($new_arr);
				$css['templatetype'] = 'css';
				$templates['CSS Settings'][] = $css;
				unset($new_arr);
			}
		}
		$gettemplates = $DB->query("SELECT *$extra FROM " . TABLE_PREFIX . "template WHERE $sqlcondition$order");
		while ($gettemplate = $DB->fetch_array($gettemplates)) {
				$gettemplate['templatetype'] = 'template';
				$isgrouped = false;
				if ($gettemplate['templategroup']) {
					$templates[$gettemplate['templategroup']][] = $gettemplate;
					$isgrouped = true;
				}
				if (!$isgrouped) {
					$templates['Unknown Templates'][] = $gettemplate;
				}
		}
		ksort($templates);
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n\r\n";
		$xml .= "<style name=\"$title\" type=\"".($styleid == 1 ? 'master' : 'custom')."\" version=\"".$bboptions['version']."\" created-time=\"".TIMENOW."\">\r\n\r\n";
		foreach($templates AS $group => $grouptemplates) {
			$xml .= "\t<templategroup name=\"".$group. "\">\r\n";
			foreach($grouptemplates AS $template) {
				$xml .= "\t\t<template name=\"".$forums->func->htmlspecialchars_uni($template['title'])."\" templatetype=\"".$forums->func->htmlspecialchars_uni($template['templatetype'])."\" group=\"".$forums->func->htmlspecialchars_uni($template['templategroup'])."\"><![CDATA[".$template['template']."]]></template>\r\n";
			}
			$xml .= "\t</templategroup>\r\n\r\n\r\n\r\n";
		}
		$xml .= "</style>";
		$forums->admin->show_download($xml, $filename, 'text/xml');
	}

	function importstyle()
	{
		global $forums, $DB, $_INPUT;
		if( ( ! $_FILES['fromlocal']['name'] AND ! $_INPUT['fromserver'] ) ) {
			$forums->main_msg = $forums->lang['nouploadfile'];
			$this->stylefiles();
		}
		if ($_FILES['fromlocal']['tmp_name'] && is_uploaded_file($_FILES['fromlocal']['tmp_name'])) {
			$xml = @file_get_contents($_FILES['fromlocal']['tmp_name']);
		} else if ($_INPUT['fromserver']) {
			$xml = @file_get_contents($_INPUT['fromserver']);
		}
		require_once(ROOT_PATH."includes/adminfunctions_template.php");
		$style = new adminfunctions_template();
		$style->importxmlstyle($xml, $_INPUT['mergestyle'], $_INPUT['parentstyle'], $_INPUT['changetitle'], $_INPUT['checkversion'], $_INPUT['usedefault'], $_INPUT['userselect']);
	}

	function style_tools()
	{
		global $forums;
		$forums->func->cache_styles();
		foreach($forums->func->stylecache AS $style) {
			$styleid[] = array( $style[styleid], $forums->func->construct_depth_mark($style['depth'], '--').$style[title] );
		}
		$title  = $forums->lang['managestyletools'];
		$detail = $forums->lang['managestyletoolsdesc'];
		$forums->admin->nav[] = array( 'style.php?do=tools' ,$forums->lang['managestyletools'] );
		$forums->admin->print_cp_header($title, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do' , 'search'  ) ), 'searchform', '', 'template.php' );
		$forums->admin->columns[] = array( "&nbsp;"  , "40%" );
		$forums->admin->columns[] = array( "&nbsp;"  , "60%" );
		$forums->admin->print_table_start( $forums->lang['stylesearch'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['keyword']."</strong><br /><span class='description'>".$forums->lang['inputtplkeyword']."</span>",
															       $forums->admin->print_input_row( 'searchkeywords', '', '', '', 30 )
													    )      );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['searchstyles']."</strong>",
															     $forums->admin->print_input_select_row('id', $styleid)
															     ."<br /><input type='checkbox' name='searchall' value='1' /> ".$forums->lang['searchstylesdesc']
													    )      );
		$forums->admin->print_form_submit($forums->lang['search']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do' , 'rebuildcaches'  ) ),'rebuildform' );
		$forums->admin->columns[] = array( "&nbsp;"  , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"  , "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidstylecache'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['selectrebulidstyles']."</strong><br /><span class='description'>".$forums->lang['selectrebulidstylesdesc']."</span>", $forums->admin->print_input_select_row('styleid', $styleid) ) );
		$forums->admin->print_form_submit($forums->lang['rebulidstylecache']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$change = array_merge( array( 0=> array( '', $forums->lang['useddefaultstyle'] ) ), $styleid );
		$forums->admin->print_form_header( array( 1 => array( 'do' , 'changeuser'  ) ) );
		$forums->admin->columns[] = array( "&nbsp;"  , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"  , "40%" );
		$forums->admin->print_table_start( $forums->lang['updateuserstyles'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['forceusestyles']."</strong>", $forums->admin->print_input_select_row('styleid', $styleid) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['changetostyles']."</strong>", $forums->admin->print_input_select_row('change', $change) ) );
		$forums->admin->print_form_submit($forums->lang['updateuserstyles']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function rebuildcaches()
	{
		global $forums, $DB, $_INPUT;
		$forums->cache_func->rebuildallcaches(array($_INPUT['styleid']));
		$forums->main_msg = $forums->lang['stylecacheupdated'].' (id: '.$_INPUT['styleid'].')';
		$forums->main_msg .= "<br />".implode("<br />", $forums->cache_func->messages);
		$this->style_tools();
	}

	function change_user()
	{
		global $forums, $DB, $_INPUT;
		$old_id = intval($_INPUT['styleid']);
		$new_id = intval($_INPUT['change']);
		if ($new_id == '0') {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET style='' WHERE style=".$old_id."" );
		} else {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET style=".$new_id." WHERE style=".$old_id."" );
		}
		$forums->main_msg = $forums->lang['userstyleupdated'];
		$this->style_tools();
	}

	function save_css()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "" OR $_INPUT['parentlist'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ($_POST['txtcss'] == "") {
			$forums->admin->print_cp_error($forums->lang['cannotemptycss']);
		}
		$_POST['txtcss'] = str_replace("<br />", "\n", $_POST['txtcss']);
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET css='".stripslashes($_POST['txtcss'])."' WHERE styleid=".$_INPUT['id']."" );
		$extra = "<strong>".$forums->lang['cssupdated']."</strong>";
		$message = $forums->cache_func->writecsscache( $_INPUT['id'], $_INPUT['parentlist'] );
		if ( ! $_INPUT['savereload'] ) {
			$forums->admin->nav[] = array( 'style.php' ,$forums->lang['managestyle'] );
			$forums->main_msg = $forums->lang['cssupdated'].": $extra";
			$forums->admin->redirect( "style.php", $forums->lang['cssupdated'], $forums->lang['cssupdated'] );
		} else {
			$forums->main_msg = $forums->lang['cssupdated'].": $extra";
			$this->edit_css();
		}
	}
}

$output = new style();
$output->show();

?>
