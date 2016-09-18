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
if(!defined('IN_MXB')) exit('Access denied.Sorry, you can not access this file directly.');
class adminfunctions_template
{
	var $messages   = array();
	var $template   = "";

	function rebuildallcaches( $affected=array() )
	{
		global $forums, $DB;
		if (count($affected)==0) {
			$allids = $style = $DB->query( "SELECT styleid FROM ".TABLE_PREFIX."style WHERE styleid != 1");
			if ($DB->num_rows($allids)) {
				while($allid = $DB->fetch_array($allids)) {
					$affected[] = $allid['styleid'];
				}
			}
		}
		foreach( $affected AS $aid ) {
			if ( $aid == '1' ) {
				continue;
			}
			$style = $DB->query_first( "SELECT styleid, title, parentlist FROM ".TABLE_PREFIX."style WHERE styleid=".$aid."");
			$forums->lang['recachestylescss'] = sprintf( $forums->lang['recachestylescss'], $style['title'] );
			$forums->lang['recachetemplates'] = sprintf( $forums->lang['recachetemplates'], $style['title'] );
			$this->messages[] = "<strong>".$forums->lang['recachestylescss']."</strong>";
			$this->writecsscache( $aid, $style['parentlist'] );
			$this->messages[] = "<br /><strong>".$forums->lang['recachetemplates']."</strong>";
			$this->recachetemplates( $aid, $style['parentlist'] );
		}
		$this->messages[] = "<br /><strong>".$forums->lang['recachestyles']."</strong>";
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
    	$style = new adminfunctions_cache();
    	$style->style_recache();
		return $message;
	}

	function writecsscache($id, $parentlist)
	{
		global $forums, $DB, $bboptions;
		$this_style = $DB->query_first( "SELECT *, instr(',$parentlist,', concat(',', styleid ,',') ) as theorder FROM ".TABLE_PREFIX."style WHERE css != '' AND styleid in ($parentlist) ORDER BY theorder LIMIT 0,1" );
		$css = $this_style['css'];
		$ids[] = $this_style;
		$styleid = array( $id );
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."style WHERE css='' AND parentlist LIKE '%".$id.",%'" );
		while ( $r = $DB->fetch_array() ) {
			$ids[] = $r;
			$styleid[] = $r['styleid'];
		}
		$folder = $DB->query_first( "SELECT imagefolder FROM ".TABLE_PREFIX."style WHERE styleid=".$id."" );
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."style SET csscache='".addslashes($css)."' WHERE styleid IN (".implode(',',$styleid).") AND styleid != 1" );
		if ( file_exists( ROOT_PATH."cache" ) ) {
			if ( is_writeable( ROOT_PATH."cache" ) ) {
				include(ROOT_PATH.'lang/lang.php');
				foreach ($lang_list as $lang => $val) {
					$thiscss = $css;
					$thiscss = str_replace( array('&lt;', '&gt;', '&quot;', '&#039;'), array('<', '>', '"', "'"), $thiscss );
					$thiscss = str_replace("<#IMAGE#>/", "../images/{$folder['imagefolder']}/$lang/", $thiscss);
					$thiscss = str_replace( "\r\n", "\n", $thiscss );
					$thiscss = str_replace( "\r", "\n", $thiscss );
					foreach( $ids AS $id ) {
						if ($id['styleid'] == '1') {
							continue;
						}
						@unlink( ROOT_PATH."cache/styleid_".$id['styleid'].'_'.$lang.".css" );
						$thiscss = str_replace( 'cache/', '', stripslashes($thiscss) );
						if ($fp = @fopen(ROOT_PATH."cache/styleid_".$id['styleid'].'_'.$lang.".css", 'wb')) {
							@fputs($fp, $thiscss);
							@fclose($fp);
							@chmod(ROOT_PATH."cache/styleid_".$id['styleid'].'_'.$lang.".css", 0777 );
							$recachestylecssfile = sprintf($forums->lang['recachestylecssfile'], $id['title'], $id['styleid'].'_'.$lang);
							$this->messages[] = $recachestylecssfile;
						} else {
							$cssfilenotupdate = sprintf($forums->lang['cssfilenotupdate'], $id['styleid'].'_'.$lang, $id['styleid'].'_'.$lang);
							$this->messages[] = "<strong>".$cssfilenotupdate."</strong>";
						}
					}
				}
			} else {
				$this->messages[] = "<strong>".$forums->lang['cssdirnotwrite']."</strong>";
			}
		} else {
			$this->messages[] = "<strong>".$forums->lang['cssdirnotwrite']."</strong>";
		}
	}

	function recachetemplates( $id, $parentlist, $group_only="" )
	{
		global $DB, $forums, $bboptions;
		$styles = $DB->query( "SELECT *, instr(',$parentlist,', concat(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."style WHERE styleid in (".$parentlist.") ORDER BY theorder LIMIT 0,1" );
		while ( $tid = $DB->fetch_array($styles) ) {
			$parentlist = $tid['parentlist'];
			$imagefolder = $tid['imagefolder'];
			if ($tid['csstype']) {
				$cssfile = "<link rel='stylesheet' type='text/css' href='./cache/styleid_".$id."_{\$bboptions['language']}.css' />";
			} else {
				$tid['csscache'] = str_replace( array('&lt;', '&gt;', '&quot;', '&#039;'), array('<', '>', '"', "'"), $tid['csscache'] );
				$tid['csscache'] = str_replace('<#IMAGE#>', 'images/<#IMAGE#>',$tid['csscache']);
				$cssfile = "<style type='text/css'>\n".$tid['csscache']."</style>";
			}
			$grouptitles = $this->gettemplates( $parentlist, 'groups' );
			foreach ( $grouptitles AS $name => $group ) {
				if ( $group_only != '' ) {
					if ( $group_only != $group['title'] ) {
						continue;
					}
				}
				$templatecache = "<?php\n/*\n";
				$templatecache .= "==== Powered By MolyX Board ====\n";
				$templatecache .= "This File Cached By MolyX.\n";
				$templatecache .= "If You Any Questions, Please Visit: www.molyx.com\n";
				$templatecache .= "========================\n";
				$templatecache .= "*/\n";
				$templatecache .= "if(!defined('IN_MXB')) exit('Access denied.Sorry, you can not access this file directly.');\n?>\n";
				$content = "";
				if ($group['templategroup'] == '') {
					$con = $DB->query_first( "SELECT templategroup FROM ".TABLE_PREFIX."templategroup WHERE title = '".$group['title']."' ORDER BY title DESC LIMIT 0, 1" );
					$group['templategroup'] = $con['templategroup'];
				}
				$templates = $this->gettemplates( $parentlist, 'templates', $group['title'], $group['templategroup'] );
				if ( !$group['noheader']) {
					$templatecache .= '<?php include $forums->func->load_template(\'header\'); ?>';
				}
				$out_templates = $group['templategroup'];
				foreach ($templates AS $title => $data ) {
					$data['template'] = str_replace( "{CACHE_CSS}", $cssfile, $data['template'] );
					$content .= $this->converttohtml( $data['title'], $data['template'], $imagefolder, $parentlist ) ."\n";
					$out_templates = str_replace( "<<<".$data['title'].">>>", $content  , $out_templates);
					unset ($content);
				}
				$templatecache .= $out_templates."\r\n";
				if ( !$group['noheader']) {
					$templatecache .= '<?php include $forums->func->load_template(\'footer\'); ?>'."\r\n";
					$templatecache .= '<?php $forums->func->finish(); ?>';
				}
				$templatecache = preg_replace( "#<<<(.+?)>>>#", "", $templatecache);
				$templategroup = $DB->query( "SELECT templategroupid FROM ".TABLE_PREFIX."templategroup WHERE title='".$group['title']."'" );
				$this->writetemplate( $id, $group['title'], $templatecache );
			}
			$this->messages[] = $forums->lang['templaterecached']."... (id: $id)";
		}
	}

	function writetemplate($id, $templategroup, $content)
	{
		global $DB, $forums;
		if ( $id == '1') {
			return;
		}
		$return     = 0;
		$good_to_go = 1;
		if (SAFE_MODE) {
			if ( is_writeable( ROOT_PATH.'cache/templates' ) ) {
				$good_to_go = 1;
				if ( file_exists( ROOT_PATH.'cache/templates/cacheid_'.$id.'_'.$templategroup.'.php' ) ) {
					if ( ! is_writeable( ROOT_PATH.'cache/templates/cacheid_'.$id.'_'.$templategroup.'.php' ) ) {
						$this->messages[] = "::cacheid_{$id}_{$templategroup}.php ".$forums->lang['templatesnotwrite'];
						$good_to_go = 0;
					} else {
						$good_to_go = 1;
					}
				} else {
					$good_to_go = 1;
				}
			} else {
				$good_to_go = 0;
				$forums->lang['templatedirnotwrite'] = sprintf( $forums->lang['templatedirnotwrite'], $change );
				$this->messages[] = $forums->lang['templatedirnotwrite'];
			}
			if ( $good_to_go ) {
				if ($fp = fopen( ROOT_PATH.'cache/templates/cacheid_'.$id.'_'.$templategroup.'.php', 'wb')) {
					fwrite($fp, $content);
					fclose($fp);
					chmod(ROOT_PATH.'cache/templates/cacheid_'.$id.'_'.$templategroup.'.php', 0777);
					$return = 1;
					$this->messages[] = $forums->lang['writed']." cache/templates/cacheid_{$id}_{$templategroup}.php";
				}
			}
		} else {
			if ( is_writeable( ROOT_PATH.'cache/templates' ) ) {
				$good_to_go = 1;
				if ( ! is_dir( ROOT_PATH.'cache/templates/cacheid_'.$id ) ) {
					if ( ! @ mkdir( ROOT_PATH.'cache/templates/cacheid_'.$id, 0777 ) ) {
						$good_to_go = 0;
					} else {
						@chmod( ROOT_PATH.'cache/templates/cacheid_'.$id, 0777 );
						$good_to_go = 1;
					}
				} else {
					if ( file_exists( ROOT_PATH.'cache/templates/cacheid_'.$id.'/'.$templategroup.'.php' ) ) {
						if ( ! is_writeable( ROOT_PATH.'cache/templates/cacheid_'.$id.'/'.$templategroup.'.php' ) ) {
							$this->messages[] = "::cacheid_{$id}/{$templategroup}.php ".$forums->lang['templatesnotwrite'];
							$good_to_go = 0;
						} else {
							$good_to_go = 1;
						}
					} else {
						$good_to_go = 1;
					}
				}
			} else {
				$good_to_go = 0;
				$forums->lang['templatedirnotwrite'] = sprintf( $forums->lang['templatedirnotwrite'], $change );
				$this->messages[] = $forums->lang['templatedirnotwrite'];
			}
			if ( $good_to_go ) {
				if ($fp = @fopen( ROOT_PATH.'cache/templates/cacheid_'.$id.'/'.$templategroup.'.php', 'wb')) {
					fwrite($fp, $content);
					fclose($fp);
					@chmod(ROOT_PATH.'cache/templates/cacheid_'.$id.'/'.$templategroup.'.php', 0777);
					$return = 1;
					$this->messages[] = $forums->lang['writed']." cache/templates/cacheid_{$id}/{$templategroup}.php";
				}
			}
		}
		return $return;
	}

	function gettemplates($parentlist, $type='', $group='', $group_templates='')
	{
		global $forums, $DB;
		$templates = array();
		if ( $type == 'groups' ) {
			$dotemplates = $DB->query("SELECT * FROM ".TABLE_PREFIX."templategroup ORDER BY title DESC");
		} else if ( $type == 'templates' ) {
			$group_templates = preg_replace("#<<<(.*)>>>#","'\\1'",trim($group_templates));
			$group_templates = preg_replace('#\\n#',',',$group_templates);
			if (!trim($group_templates)) return;
			$dotemplates = $DB->query( "SELECT *, INSTR(',".$parentlist.",' , CONCAT(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."template WHERE styleid IN (".$parentlist.") AND title IN (".$group_templates.") ORDER BY title,theorder DESC" );
		} else {
			$newq = $DB->query("SELECT *, INSTR(',".$parentlist.",' , CONCAT(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."template WHERE styleid IN (".$parentlist.") ORDER BY templategroup, title, theorder DESC");
		}
		while ( $r = $DB->fetch_array($dotemplates) ) {
			if ( $type == 'groups' ) {
				$templates[ $r['title'] ] = $r;
			} else if ( $type == 'templates' ) {
				$templates[ $forums->func->strtolower($r['title']) ] = $r;
			} else {
				$templates[ $r['templategroup'] ][ $forums->func->strtolower($r['title']) ] = $r;
			}
		}
		ksort($templates);
		return $templates;
	}

	function _get_templates($parentlist, $type='', $group='')
	{
		global $forums, $DB;
		$templates = array();
		if ( $type == 'groups' AND $group == '' ) {
			$dotemplates = $DB->query( "SELECT templategroup, styleid, tid, title, INSTR(',".$parentlist.",' , CONCAT(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."template WHERE styleid IN (".$parentlist.") ORDER BY templategroup, theorder DESC" );
			$return = 'titles';
		} else if ( $type == 'groups' AND $group != '' ) {
			$dotemplates = $DB->query( "SELECT *, INSTR(',".$parentlist.",' , CONCAT(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."template WHERE styleid IN (".$parentlist.") AND templategroup='".$group."' ORDER BY title,theorder DESC" );
			$return = 'group';
		} else {
			$dotemplates = $DB->query( "SELECT *, INSTR(',".$parentlist.",' , CONCAT(',',styleid,',') ) as theorder FROM ".TABLE_PREFIX."template WHERE styleid IN (".$parentlist.") ORDER BY templategroup, title, theorder DESC" );
		}
		while ( $r = $DB->fetch_array($dotemplates) ) {
			if ( $return == 'titles' ) {
				$templates[ $r['templategroup'] ] = $r;
				$this->template_count[ $r['styleid'] ][ $r['templategroup'] ]['count']++;
			} else if ( $return == 'group' ) {
				$templates[ $forums->func->strtolower($r['title']) ] = $r;
			} else {
				$templates[ $r['templategroup'] ][ $forums->func->strtolower($r['title']) ] = $r;
			}
		}
		ksort($templates);
		return $templates;
	}

	function converttohtml($title, $html, $imagefolder='', $parentlist='')
	{
		$html = str_replace( "<#IMAGE#>", $imagefolder.'/{$bboptions[\'language\']}', $html );
		$html = preg_replace( "#(?:\s+?)?{template:(.+?)}#ise", "\$this->converttemplate('\\1')", $html );
		$html = preg_replace( "#(?:\s+?)?{ads:(.+?)}#ise", "\$this->convertad('\\1')", $html );
		if ( preg_match( "#<if=[\"'].+?[\"']>#si", $html ) OR preg_match( "#<foreach=[\"'].+?[\"']>#si", $html ) ) {
			$html = $this->doaddslashes($html);
			$html = preg_replace( "#(?:\s+?)?<if=[\"'](.+?)[\"']>#ise", "\$this->convertif('\\1')", $html );
			$html = preg_replace( "#(?:\s+?)?<elseif=[\"'](.+?)[\"']>#ise", "\$this->convertelseif('\\1')", $html );
			$html = str_replace( "<else>", "<?php } else { ?>", $html );
			$html = preg_replace( "#(?:\s+?)?<foreach=[\"'](.+?)[\"']>#ise", "\$this->convertforeach('\\1')", $html );
			$html = str_replace( "</foreach>", "<?php } ?>", $html );
			$html = str_replace( "</if>", "<?php } ?>", $html );
		}
		$html = preg_replace( "#<\#CODEBEGIN\#>(.+?)<\#CODEEND\#>#ise", "\$this->convertcode('\\1')", $html );
		$html = preg_replace( "#(\r\n|\r|\n){0,}(<\?|\?>)(\r\n|\r|\n){0,}#", "\\2", $html );
		$html = preg_replace( "#(</td>|</tr>|<td>|<tr>)( |\r\n|\r|\n){0,}(</td>|</tr>|<td>|<tr>)#", "\\1\\3", $html );
		$html = str_replace( "\t", "", $html );
		$html = $this->converttags($html);
		return $html;
	}

	function converttemplate($template)
	{
		return "<?php include \$forums->func->load_template(\"{$template}\"); ?>";
	}

	function convertad( $code )
	{
		$v = explode(",", $code);
		foreach($v AS $data) {
			$data = trim($data);
			if (preg_match("#^\\$(.+?)#si", $data)) {
				$value[] = $data;
			} else {
				$value[] = "'".$data."'";
			}
		}
		return "<?php echo \$forums->ads->check_ad(".implode(", ", $value)."); ?>";
	}

	function convertif( $code )
	{
		$code = $this->replaceif($code);
		return "<?php if ( $code ) { ?>";
	}

	function replaceif( $code )
	{
		$code = $this->doaddslashes($code);
		return trim(preg_replace( "/(^|and|or)(\s+)(.+?)(\s|$)/ise", "\$this->replaceleft('\\3', '\\1', '\\2', '\\4')", ' '.$code ));
	}

	function replaceleft($left, $andor="", $fs="", $ls="")
	{
		$left = trim($this->doaddslashes($left));
		if ( preg_match( "/^forums\./", $left ) ) {
			$left = preg_replace( "/^forums\.(.+?)$/", '$forums->'."\\1", $left );
		}
		return $andor.$fs.$left.$ls;
	}

	function convertforeach( $code )
	{
		return "<?php foreach ( $code ) { ?>";
	}

	function convertcode( $code )
	{
		return "<?php $code ?>";
	}

	function convertelseif( $code )
	{
		$html = $this->replacehtml($html);
		$code = $this->replaceif($code);
		return "<?php } elseif ( $code ) { ?>";
	}

	function replacehtml($html)
	{
		return trim($this->doaddslashes($html));
	}

	function doaddslashes($code)
	{
		if ( !get_magic_quotes_gpc() ) $code = stripslashes($code);
		return $code;
	}

	function converttags($text="")
	{
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\"\'\$\x7f-\xff]+\])*)";
		$text = preg_replace("/\{\\\$lang([a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/s", '<?php echo $forums->lang\\1; ?>', $text);
		$text = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/s", "<?php echo \\1; ?>", $text);
		$text = preg_replace( "/{sessionurl}/i" , '<?php echo $forums->sessionurl; ?>', $text);
		$text = preg_replace( "/{js_sessionurl}/i" , '<?php echo $forums->js_sessionurl; ?>', $text);
		$text = preg_replace( "/{sessionid}/i" , '<?php echo $forums->sessionid; ?>', $text);
		$text = preg_replace( "/{url}/i" , '<?php echo $forums->url; ?>', $text);
		$text = preg_replace( "/{debug}/i" , '<?php echo $forums->func->debug(); ?>', $text);
		$text = preg_replace( "/{finish}/i" , '<?php echo $forums->func->finish(); ?>', $text);
		$text = preg_replace( "/{server_load}/i" , '<?php echo $forums->server_load; ?>', $text);
		$text = preg_replace( "/{scriptpath}/i" , '<?php echo SCRIPTPATH; ?>', $text);
		$text = preg_replace( "/{lang_list}/i" , '<?php echo $forums->lang_list; ?>', $text);
		$text = preg_replace( "/{style_list}/i" , '<?php echo $forums->style_list; ?>', $text);
		$text = preg_replace( "/{dbcount}/i" , '<?php echo $DB->query_count(); ?>', $text);
		$text = preg_replace( "/{global\.(folder_links)([\'\a-zA-Z_\x7f-\xff\']*)}/s", '<?php echo $forums->\\1\\2; ?>' , $text );
		$text = preg_replace( "/{DB\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff])([\'\a-zA-Z_\x7f-\xff\']*)}/s", '<?php echo $DB->\\1\\2; ?>' , $text );
		return $text;
	}

	function importxmlstyle($xml = false, $styleid = -1, $parentid = 1, $title = '', $anyversion = 0, $usedefault = 0, $userselect = 1)
	{
		global $DB, $forums, $bboptions, $counter, $arr;
		if ($xml == false) {
			$forums->admin->print_cp_error($forums->lang['cannotimportstyle']);
		}
		$this->intemplate = 0;
		$counter = 0;
		$this->curtag = '';
		$arr = array();
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'parse_style_otag', 'parse_style_ctag');
		xml_set_character_data_handler($this->parser, 'parse_style_cdata');
		if (!@xml_parse($this->parser, $xml)) {
			$forums->admin->print_cp_error($forums->lang['xmlerror'].": ".xml_error_string(xml_get_error_code($this->parser))." in line: ".xml_get_current_line_number($this->parser)."");
		}
		xml_parser_free($this->parser);
		if (empty($arr)) {
			$forums->admin->print_cp_error($forums->lang['styleerror']);
		}
		$version = $this->style_version;
		$master = $this->style_master;
		$title = empty($title) ? $this->style_title : $title;
		if ($version != $bboptions['version'] AND !$anyversion AND !$master) {
			$forums->lang['styleversionnotsame'] = sprintf( $forums->lang['styleversionnotsame'], $version, $bboptions['version'] );
			$forums->admin->print_cp_error($forums->lang['styleversionnotsame']);
		}
		if ($master) {
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "template WHERE styleid = -10");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "template SET styleid = -10 WHERE styleid = 1");
			$styleid = 1;
		} else {
			if ($styleid == -1) {
				if ($DB->query_first("SELECT styleid FROM " . TABLE_PREFIX . "style WHERE title = '" . addslashes($title) . "'")) {
					$forums->lang['styleexist'] = sprintf( $forums->lang['styleexist'], $title );
					$forums->admin->print_cp_error($forums->lang['styleexist']);
				} else {
					$styleresult = $DB->query_unbuffered("INSERT INTO " . TABLE_PREFIX . "style
																	(title, parentid, userselect, usedefault)
																	VALUES
																	('".addslashes($title)."', $parentid, $userselect, $usedefault)
																");
					$styleid = $DB->insert_id($styleresult);
				}
			} else {
				if (!$getstyle = $DB->query_first("SELECT title FROM " . TABLE_PREFIX . "style WHERE styleid = $styleid")) {
					$forums->admin->print_cp_error($forums->lang['notoveremstyles']);
				}
			}
		}
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
					$querybits[] = "($styleid, '$title', '$template[template]', '$template[templategroup]')";
				}
				if (++$querytemplates % 20 == 0) {
					$DB->query_unbuffered("
						REPLACE INTO " . TABLE_PREFIX . "template
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
				REPLACE INTO " . TABLE_PREFIX . "template
				(styleid, title, template, templategroup)
				VALUES
				" . implode(',', $querybits) . "
			");
		}
		unset($querybits);
		if ($stylebits['css']) {
			$stylebits['css'] = str_replace( array('<', '>'), array('&lt;', '&gt;'), $stylebits['css'] );
		} else {
			unset($stylebits['css']);
		}
		if ($styleid != 1) {
			$parent = $DB->query_first("SELECT parentlist FROM " . TABLE_PREFIX . "style WHERE styleid = $parentid");
			$stylebits['parentlist'] = $styleid.','.$parent['parentlist'];
		}
		if ($stylebits) {
			$forums->func->fetch_query_sql( $stylebits, 'style', 'styleid='.$styleid );
		}
		$DB->query("SELECT * FROM " . TABLE_PREFIX . "template WHERE styleid = 1");
		while ($dtemplate=$DB->fetch_array()) {
			$dtemplates[] = $dtemplate['title'];
		}
		$DB->query("SELECT * FROM " . TABLE_PREFIX . "template WHERE styleid = -10");
		while ($dotemplate=$DB->fetch_array()) {
			if (in_array($dotemplate['title'], $dtemplates)) {
				$deltemplates[] = $dotemplate['tid'];
			}
		}
		if (is_array($deltemplates)) {
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "template WHERE styleid = -10 AND tid IN (".implode(",", $deltemplates).")");
		}
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "template SET styleid = 1 WHERE styleid = -10");
		$this->rebuildallcaches();
		$forums->admin->redirect( "style.php", $forums->lang['styleimported'], $forums->lang['styleimporteddesc'] );
	}

	function parse_style_otag($parser, $name, $attrs)
	{
		global $counter, $arr, $type;
		$this->curtag = $name;
		switch($name) {
			case 'style':
				$this->style_title = $attrs['name'];
				$this->style_version = $attrs['version'];
				$this->style_master = ($attrs['type'] == 'master' ? 1 : 0);
			break;
			case 'template':
				$this->intemplate = 1;
				$counter = $attrs['name'];
				$type = $attrs['templatetype'];
				$arr["$type"]["$counter"] = array();
				$arr["$type"]["$counter"]['templatetype'] = $attrs['templatetype'];
				$arr["$type"]["$counter"]['templategroup'] = $attrs['group'];
				$arr["$type"]["$counter"]['template'] = '';
			break;
		}
	}

	function parse_style_ctag($parser, $name)
	{
		if ($name == 'template') {
			$this->intemplate = 0;
		}
	}

	function parse_style_cdata($parser, $data)
	{
		global $arr, $counter, $type;
		if ($this->curtag == 'template' AND $this->intemplate) {
			$arr["$type"]["$counter"]['template'] .= $data;
		}
	}
}

?>