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
class functions_codeparse {
	var $stripquotes = '';
	var $error = '';
	var $show_html = 0;
	var $nnl2br = 1;
	var $ignorecensored = 0;
	var $quote_html = array();
	var $quote_open = 0;
	var $quote_closed = 0;
	var $attachinpost = array();

	function functions_codeparse($load=0)
	{
		global $bboptions;
		$this->stripquotes = $bboptions['stripquotes'];
		if ($load) {
			$this->check_caches();
		}
	}

	function check_caches()
	{
		global $forums;
		$forums->func->check_cache('icon');
		$forums->func->check_cache('smile');
		$forums->func->check_cache('bbcode');
		$forums->func->check_cache('badword');
	}

	function strip_quote_tags( $text="" )
	{
		return preg_replace( "#\[quote(.+?)?\].*\[/quote\]#is", "", $text );
	}

	function parse_poll_tags($text)
	{
		$pregfind = array
		(
			"#\[img\](.+?)\[/img\]#ie",
			"#\[url\](\S+?)\[/url\]#ie",
			'#\[url=(&quot;|"|\'|)(.*)\\1\](.*)\[/url\]#esiU',
		);
		$pregreplace = array
		(
			"\$this->parse_image('\\1')",
			"\$this->parse_build_url(array('html' => '\\1', 'show' => '\\1'))",
			"\$this->parse_build_url(array('html' => '\\2', 'show' => '\\3'))",
		);
		return preg_replace($pregfind, $pregreplace, $text);
	}

	function convert_url_callback($text, $prepend)
	{
		$text = str_replace('\\"', '"', $text);
		$taglist = '\[b|strong|\[i|\[u|\[sub|\[sup|\[s|\[left|\[center|\[right|\[indent|\[quote|\[highlight|\[\*';
		$text = preg_replace("/(^|(?<=[^_a-z0-9-=\]\"'\/@]|(?<=" . $taglist . ")\]))((https?|ftp|gopher|news|telnet|rtsp|mms):\/\/|www\.)(?!\[\/url|\[\/img)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/ie" , "\$this->parse_build_url(array('html' => '\\2\\4', 'show' => '\\2\\4'))", $text);
		$text = preg_replace( "/(^|(?<=[^_a-z0-9-=\]\"'\/@]|(?<=" . $taglist . ")\]))((exeem):\/\/|www\.)(?!\[\/url|\[\/img)((\|(\w+)\|(\S+))|([^<> ]*))/ie" , "\$this->parse_build_url(array('html' => '\\2\\4', 'show' => '\\2\\4'))", $text );
		return $prepend . $text;
	}

	function convert($in=array( 'text' => '', 'allowsmilies' => 0, 'allowcode' => 0, 'allowhtml' => 0, 'usewysiwyg' => 0, 'change_editor' => 1))
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$this->check_caches();
		$text = $in['text'];
		$usewysiwyg = intval($in['usewysiwyg']);
		$change_editor = intval($in['change_editor']);

		if (!$change_editor) {
			$text = $this->safe_text($text);
		}

		$_INPUT['parseurl'] = isset($_INPUT['parseurl']) ? $_INPUT['parseurl'] : (isset($_INPUT['checkurl']) ? $_INPUT['checkurl'] : 222);

		$pregfind = array
		(
			'#<img[^>]+smilietext=(\'|")(.+)(\\1).*>#siU',
			'#<img[^>]+src=(\'|")(.+)(\\1).*>screen??(.*)>#siU',
			'#<img[^>]+src=(\'|")(.+)(\\1).*>#siU',
		);
		$pregreplace = array
		(
			'\2',
			'[img]\2[/img]',
			'[img]\2[/img]',
		);
		$text = preg_replace($pregfind, $pregreplace, $text);
		if ($_INPUT['parseurl']) {
			$text = preg_replace(array(
					'#<a href="([^"]*)\[([^"]+)"(.*)>(.*)\[\\2</a>#siU',
					'#(<[^<>]+ (src|href))=(\'|"|)??(.*)(\\3)#esiU',
					'#<a[^<>]+href="([^"]*)"(.*)>(.*)</a>#siU',
				), array(
					"<a href=\"\\1\"\\3>\\4</a>[\\2",
					"\$this->sanitize_url('\\1', '\\4')",
					"[url=\\1]\\3[/url]",
				), $text
			);
			$skiptaglist = 'url|email|code|real|music|movie';
			$text = preg_replace('#(^|\[/(' . $skiptaglist . ')\])(.+(?=\[(' . $skiptaglist . ')|$))#siUe', "\$this->convert_url_callback('\\3', '\\1')", $text);
		}

		$text = $this->bbcode_check($text);
		$text = preg_replace( "#(\?|&amp;|;|&)s=([0-9a-zA-Z]){32}(&amp;|;|&|$)?#e", "\$this->parse_bash_session('\\1', '\\3')", $text );

		if ( $in['allowcode'] ) {
			$pregfind = array
			(
				"#\[email\](\S+?)\[/email\]#i",
				"#\[email\s*=\s*([\.\w\-]+\@[\.\w\-]+\.[\w\-]+)\s*\](.*?)\[\/email\]#i",
				"#\[email\s*=\s*\&quot\;([\.\w\-]+\@[\.\w\-]+\.[\.\w\-]+)\s*\&quot\;\s*\](.*?)\[\/email\]#i",
				"#\[indent\](.+?)\[/indent\]#is",
				"#\[url\](\S+?)\[/url\]#ie",
				'#\[url=(&quot;|"|\'|)(.*)\\1\](.*)\[/url\]#esiU',
			);
			$pregreplace = array
			(
				"<a href=\"mailto:\\1\">\\1</a>",
				"<a href=\"mailto:\\1\">\\2</a>",
				"<a href=\"mailto:\\1\">\\2</a>",
				"<blockquote>\\1</blockquote>",
				"\$this->parse_build_url(array('html' => '\\1', 'show' => '\\1'))",
				"\$this->parse_build_url(array('html' => '\\2', 'show' => '\\3'))",
			);
			$text = preg_replace($pregfind, $pregreplace, $text);

			$text = strip_tags($text, '<b><strong><i><em><u><sub><sup><s><a><div><span><p><blockquote><ol><ul><li><font><img><br><h1><h2><h3><h4><h5><h6><hr><table><td><tr>');

			if (!$change_editor) {
				$text = preg_replace( "#(\[quote(.+?)?\].*\[/quote\])#ies" , "\$this->parse_quotes('\\1')"  , $text );
				$text = preg_replace( "#\[code\](.+?)\[/code\]#ies"  , "\$this->parse_codes('\\1')", $text );
				$text = preg_replace( "#\[emule\](.+?)\[/emule\]#ies"  , "\$this->parse_emule('\\1')", $text );
			}

			while( preg_match( "#\n?\[list\](.+?)\[/list\]\n?#ies", $text ) ) {
				$text = preg_replace( "#\n?\[list\](.+?)\[/list\]\n?#ies", "\$this->parse_list('\\1')", $text );
			}
			while( preg_match( "#\n?\[list=(a|A|i|I|1)\](.+?)\[/list\]\n?#ies", $text ) ) {
				$text = preg_replace( "#\n?\[list=(a|A|i|I|1)\](.+?)\[/list\]\n?#ies", "\$this->parse_list('\\2','\\1')", $text );
			}
			while( preg_match( "#\[(b|i|u|sub|sup|s)\](.+?)\[/\\1\]#is", $text ) ) {
				$text = preg_replace( "#\[(b|i|u|sub|sup|s)\](.+?)\[/\\1\]#is", "<\\1>\\2</\\1>", $text );
			}
			while( preg_match( "#\[(left|right|center)\](<br.*>|\r\n|\n|\r)??(.+?)(<br.*>|\r\n|\n|\r)??\[/\\1\]#is", $text ) ) 	{
				$text = preg_replace( "#\[(left|right|center)\](<br.*>|\r\n|\n|\r)??(.+?)(<br.*>|\r\n|\n|\r)??\[/\\1\]#is", "<p align='\\1'>\\3</p>", $text );
			}
			while ( preg_match( "#\[(font|size|color|bgcolor)=(&quot;|&\#39;|'|\"|)([^\]]+)(\\2)\](.+?)\[/\\1\]#is", $text ) ) {
				$text = preg_replace( "#\[(font|size|color|bgcolor)=(&quot;|&\#39;|'|\"|)([^\]]+)(\\2)\](.+?)\[/\\1\]#ies", "\$this->parse_font('\\1','\\3','\\5')", $text );
			}
			if (!$change_editor) {
				$text = preg_replace( '#\[aid::(\d+)\]#iesU', "\$this->parse_attach('\\1')", $text );
				if (is_array($this->aip)) {
					$text = $this->parse_attach_contents($text);
				}
			}
			if ($bboptions['allowimages']) {
				$text = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->parse_image('\\1')", $text);
			}
			if (!$change_editor) {
				$pregfind = array
					(
						"#(\[flash\])(.+?)(\[\/flash\])#ie",
						"#(\[flash=)(\S+?)(\,)(\S+?)(\])(\S+?)(\[\/flash\])#ie",
					);
				$pregreplace = array
					(
						"\$this->parse_flash('".$bboptions['maxflashwidth']."','".$bboptions['maxflashheight']."','\\2')",
						"\$this->parse_flash('\\2','\\4','\\6')",
					);
				$text = preg_replace($pregfind, $pregreplace, $text);
			}
		}
		$text = str_replace(array("[hr]", "(c)", "(tm)", "(r)"), array("<hr>", "&copy;", "&#153;", "&reg;"), $text);
		$text = preg_replace( array("/\n/", "/&amp;#([0-9]+);/s"), array("<br />", "&#\\1;"), $text );
		if ($in['allowsmilies']) {
			$text = ' '.$text.' ';
			$forums->func->check_cache('smile');
			$smile = $forums->cache['smile'];
			usort($smile, array('functions_codeparse', 'smile_length_sort'));
			if (count($smile) > 0) {
				foreach($smile as $a_id => $row) {
					$code  = $row['smiletext'];
					$image = $row['image'];
					$code = preg_quote($code, "/");
					$text = preg_replace( "!(?<=[^\w&;/])$code(?=.\W|\W.|\W$)!e", "\$this->convert_smilies('$code', '$image')", $text );
				}
			}
			$text = trim($text);
		}
		$text = $this->censoredwords($text);
		if ((($bbuserinfo['canappendedit'] AND $_INPUT['appendedit']) OR !$bbuserinfo['canappendedit']) AND (THIS_SCRIPT=='editpost' OR THIS_SCRIPT=='editor')) {
			$edittime = $forums->func->get_date( TIMENOW , 2);
			$forums->lang['_editinfo'] = sprintf( $forums->lang['_editinfo'], $bbuserinfo['name'], $edittime );
			$text .= "<!--editpost--><br /><br /><br /><div><font class='editinfo'>".$forums->lang['_editinfo']."</font></div><!--editpost1-->";
		}
		if ( $in['allowhtml'] ) {
			$text = $this->parse_html($text);
		}
		return $text;
	}

	function unconvert($text = '', $code = 1, $html = 0, $wysiwyg = 0, $ajax = 0)
	{
		global $bbuserinfo, $forums;
		$pregfind = array
		(
			"#<!--editpost-->(.+?)<!--editpost1-->#",
			"#<!--emule-->(.+?)<!--emule1-->(.+?)<!--emule2-->(.+?)<!--emule3-->#sie",
			"#<!--code-->(.+?)<!--code1-->(.+?)<!--code2-->(.+?)<!--code3-->#sie",
			"#<!--quote-{1,2}([^>]+?)\+([^>]+?)\+([^>]+?)-->(.+?)<!--quote1-->#",
			"#<!--quote-{1,2}([^>]+?)\+([^>]+?)\+-->(.+?)<!--quote1-->#",
			"#<!--quote-{1,2}([^>]+?)\+\+-->(.+?)<!--quote1-->#",
			"#<!--quote-->(.+?)<!--quote1-->#",
			"#<!--quote2-->(.+?)<!--quote3-->#",
			"#<!--Flash (.+?)-->.+?<!--End Flash-->#siUe",
			"#<!--attachid::(\d+)-->(.+?)<!--attachid-->#",
		);
		$pregreplace = array
		(
			'',
			"\$this->unconvert_emule('\\2')",
			"\$this->unconvert_code('\\2', \$wysiwyg, \$ajax)",
			"[quote=\\1,\\2,\\3]",
			"[quote=\\1,\\2]",
			"[quote=\\1]",
			"[quote]",
			"[/quote]",
			"\$this->unconvert_flash('\\1')",
			"[aid::\\1]",
		);
		$text = preg_replace($pregfind, $pregreplace, $text);
		if (!$wysiwyg) {
			$text = $this->convert_wysiwyg_bbcode($text, $code, $html);
		}
		$text = $this->safe_text($text);
		return trim($text);
	}

	function convert_wysiwyg_bbcode($text = '', $code=1, $html=0)
	{
		global $forums;
		if (!$html) {
			$text = strip_tags($text, '<b><strong><i><em><u><s><a><div><span><p><blockquote><ol><ul><li><font><img><br><h1><h2><h3><h4><h5><h6><hr>');
		}
		if (preg_match_all('#\[(quote|code)\](.*)\[/\\1\]#siU', $text, $regs)) {
			foreach($regs[2] as $key => $val) {
				$orig = $val;
				$val = str_replace('&nbsp; ', '&nbsp;&nbsp;', $val);
				$val = preg_replace('#(&nbsp;){4,}#siU', "\t", $val);
				$text = str_replace($orig, $val, $text);
			}
		}
		$text = str_replace('&nbsp;', ' ', $text);
		if ($forums->func->is_browser('mozilla')) {
			$text = str_replace("\r\n", ' ', $text);
		} else {
			$text = str_replace("\r\n", '', $text);
		}
		if ($code) {
			$text = preg_replace(
				array(
					'#<a href="([^"]*)\[([^"]+)"(.*)>(.*)\[\\2</a>#siU',
					'#(<[^<>]+ (src|href))=(\'|"|)??(.*)(\\3)#esiU'
				),
				array (
					"<a href=\"\\1\"\\3>\\4</a>[\\2",
					"\$this->sanitize_url('\\1', '\\4')"
				), $text
			);
			$pregfind = array
			(
				'#<h([0-9]+)[^>]*>(.*)</h\\1>#siU',
				'#<img[^>]+smilietext=(\'|"|\\\")(.*)(\\1).*>#siU',
				'#<img[^>]+src=(\'|")(.+)(\\1).*>screen??(.*)>#siU',
				'#<img[^>]+src=(\'|")(.+)(\\1).*>#siU',
				'#<hr.*>#siU',
				'#<a name=[^>]*>(.*)</a>#siU',
				'#\[(code)\]((?>[^\[]+?|(?R)|.))*\[/\\1\]#siUe',
				'#\[url=(\'|"|&quot;|)<A href="(.*)/??">\\2/??</A>#siU',
				'#\&copy;#i',
				'#\&\#153;#i',
				'#\&reg;#i'
			);
			$pregreplace = array
			(
				"[size=\\1]\\2[/b]\n\n",
				'\2',
				'[img]\2[/img]',
				'[img]\2[/img]',
				"[hr]",
				'\1',
				"\$this->strip_tags_callback('\\0')",
				"[url=\\1\\2",
				'\(c\)',
				"\(tm\)",
				'\(r\)'
			);
			$text = preg_replace($pregfind, $pregreplace, $text);
			$text = $this->parse_wysiwyg_code('b', $text, 'parse_wysiwyg_code_replacement', 'b');
			$text = $this->parse_wysiwyg_code('strong', $text, 'parse_wysiwyg_code_replacement', 'b');
			$text = $this->parse_wysiwyg_code('i', $text, 'parse_wysiwyg_code_replacement', 'i');
			$text = $this->parse_wysiwyg_code('em', $text, 'parse_wysiwyg_code_replacement', 'i');
			$text = $this->parse_wysiwyg_code('u', $text, 'parse_wysiwyg_code_replacement', 'u');
			$text = $this->parse_wysiwyg_code('s', $text, 'parse_wysiwyg_code_replacement', 's');
			$text = $this->parse_wysiwyg_code('a', $text, 'parse_wysiwyg_anchor');
			$text = $this->parse_wysiwyg_code('font', $text, 'parse_wysiwyg_font');
			$text = $this->parse_wysiwyg_code('blockquote', $text, 'parse_wysiwyg_code_replacement', 'indent');
			$text = $this->parse_wysiwyg_code('ol', $text, 'parse_wysiwyg_list');
			$text = $this->parse_wysiwyg_code('ul', $text, 'parse_wysiwyg_list');
			$text = $this->parse_wysiwyg_code('div', $text, 'parse_wysiwyg_div');
			$text = $this->parse_wysiwyg_code('span', $text, 'parse_wysiwyg_span');
			$text = $this->parse_wysiwyg_code('p', $text, 'parse_wysiwyg_paragraph');
			$pregfind = array(
				'#<li>(.*)((?=<li>)|</li>)#iU',
				'#<p.*>#iU',
				'#<br.*>#iU',
				'#(\[/quote\])(\s?\r?\n){0,1}#si',
				'#<div.*>#iU',
			);
			$pregreplace = array(
				"\\1\n",
				"\n\n",
				"\n",
				'\1',
				'',
			);
			$text = preg_replace($pregfind, $pregreplace, $text);
			$strfind = array
			(
				'<A>',
				'</A>',
				'</LI>',
				'</li>',
				'&lt;',
				'&gt;',
				'&quot;',
				'&amp;',
			);
			$strreplace = array
			(
				'',
				'',
				'',
				'',
				'<',
				'>',
				'"',
				'&',
			);
			$text = str_replace($strfind, $strreplace, $text);
			$text = preg_replace("#(?<!\r|\n|^)\[(/list|list|\*)\]#", "\n[\\1]", $text);
		}
		if ($html) {
			$text = str_replace( "&#39;", "'", $text);
		}
		return trim($text);
	}

	function safe_text($text="")
	{
		$pregfind = array(
			'/script/i',
			'/alert/i',
			'/about:/i',
			'/onmouseover/i',
			'/onclick/i',
			'/onload/i',
			'/onsubmit/i',
			'/\[\/img\] *\}" border="0" \/>/i'
		);
		$pregreplace = array(
			'&#115;cript',
			'&#097;lert',
			'&#097;bout:',
			'&#111;nmouseover',
			'&#111;nclick',
			'&#111;nload',
			'&#111;nsubmit',
			'[/img] '
		);
		$text = preg_replace($pregfind, $pregreplace, $text);
		return $text;
	}

	function bbcode_check($text="")
	{
		global $forums;
		$count = array();
		$forums->func->check_cache('bbcode');
		if ( is_array( $forums->cache['bbcode'] ) AND count( $forums->cache['bbcode'] ) ) {
			foreach( $forums->cache['bbcode'] AS $i => $r ) {
				if ( $r['twoparams'] ) {
					$count[ $r['bbcodeid'] ]['open'] = substr_count( $text, '['.$r['bbcodetag'].'=' );
					$count[ $r['bbcodeid'] ]['wrongopen'] = substr_count( $text, '['.$r['bbcodetag'].']' );
				} else {
					$count[ $r['bbcodeid'] ]['open'] = substr_count( $text, '['.$r['bbcodetag'].']' );
					$count[ $r['bbcodeid'] ]['wrongopen'] = substr_count( $text, '['.$r['bbcodetag'].'=' );
				}
				$count[ $r['bbcodeid'] ]['closed'] = substr_count( $text, '[/'.$r['bbcodetag'].']' );
				if ( $count[ $r['bbcodeid'] ]['open'] != $count[ $r['bbcodeid'] ]['closed'] ) {
					$this->error = ( $count[ $r['bbcodeid'] ]['wrongopen'] == $count[ $r['bbcodeid'] ]['closed'] ) ? $forums->lang['_bbcodeerror1'] : $forums->lang['_bbcodeerror2'];
				} else {
					if (in_array($r['bbcodetag'], array("music", "movie", "real"))) {
						$text = preg_replace("#(|(\[url=(?:&quot;|&\#39;)?(.+?)(?:&quot;|&\#39;)?\]))(\[".$r['bbcodetag']."\])(.*)(\[/".$r['bbcodetag']."\])(\[/url\]|)#siUe", "\$this->strip_url('\\5', '\\4', '\\6')", $text);
					}
				}
			}
		}
		return $text;
	}

	function strip_url($text="", $tag_s="", $tag_e="")
	{
		$text = preg_replace("#\[(url|\/url|url=(?:&quot;|&\#39;|)?(.+?)(?:&quot;|&\#39;|))?\]#si", "", $text);
		return $tag_s.trim(strip_tags($text)).$tag_e;
	}

	function convert_text($text="")
	{
		if ( $this->show_html ) {
			$text = $this->parse_html( $text );
		}
		if ( strstr( $text, '[/' )  ) {
			$text = $this->parse_bbcode($text);
		}
		return $text;
	}

	function parse_bbcode($text="")
	{
		global $forums;
		$forums->func->check_cache('bbcode');
		if ( is_array( $forums->cache['bbcode'] ) AND count( $forums->cache['bbcode'] ) ) {
			foreach( $forums->cache['bbcode'] AS $row ) {
				if ( substr_count( $row['bbcodereplacement'], '{content}' ) > 1 ) {
					if ( $row['twoparams'] ) {
						preg_match_all( "#(\[".$row['bbcodetag']."=(?:&quot;|&\#39;)?(.+?)(?:&quot;|&\#39;)?\])(.+?)(\[/".$row['bbcodetag']."\])#si", $text, $match );
						for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
							$row['bbcodereplacement'] = str_replace( array('{option}', '{content}'), array($match[2][$i], $match[3][$i]), $row['bbcodereplacement'] );
							$text = str_replace( $match[0][$i], $row['bbcodereplacement'], $text );
						}
					} else {
						preg_match_all( "#(\[".$row['bbcodetag']."\])(.+?)(\[/".$row['bbcodetag']."\])#si", $text, $match );
						for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
							$bbcodereplacement = str_replace( '{content}', $match[2][$i], $row['bbcodereplacement'] );
							$text = str_replace( $match[0][$i], $bbcodereplacement, $text );
						}
					}
				} else {
					$replacement = explode( '{content}', $row['bbcodereplacement'] );
					if ( $row['twoparams'] ) {
						$text = preg_replace( "#\[".$row['bbcodetag']."=(?:&quot;|&\#39;)?(.+?)(?:&quot;|&\#39;)?\]#si", str_replace( '{option}', "\\1", $replacement[0] ), $text );
					} else 	{
						$text = str_replace( '['.$row['bbcodetag'].']' , $replacement[0], $text );
					}
					$text = str_replace( '[/'.$row['bbcodetag'].']', $replacement[1], $text );
				}
			}
		}
		return $text;
	}

	function parse_html($text="")
	{
		if ( $text == "" ) return $text;
		if ( $this->nnl2br != 1 ) {
			$text = preg_replace( "#<br.*>#siU", "\n", $text );
		}
		$strfind = array
		(
			"&#39;",
			"&#33;",
			"&#036;",
			"&#124;",
			"&amp;",
			"&gt;",
			"&lt;",
			"&quot;"
		);
		$strreplace = array
		(
			"'",
			"!",
			"$",
			"|",
			"&",
			">",
			"<",
			'"'
		);
		$text = str_replace($strfind, $strreplace, $text);
		return $text;
	}

	function censoredwords($text = "")
	{
		global $forums, $bbuserinfo;
		if ($text == "") return "";
		if ( intval($bbuserinfo['passbadword']) == 1 )	 return $text;
		$forums->func->check_cache('badword');
		if ( is_array( $forums->cache['badword'] ) ) {
			usort( $forums->cache['badword'] , array( 'functions_codeparse', 'word_length_sort' ) );
			if ( count($forums->cache['badword']) > 0 ) {
				foreach($forums->cache['badword'] AS $idx => $r) {
					$replace = $r['badafter'] == "" ? '******' : $r['badafter'];
					$r['badbefore'] = preg_quote($r['badbefore'], "/");
					$text = ($r['type'] == 1) ? preg_replace( "/(^|\b)".$r['badbefore']."(\b|!|\?|\.|,|$)/i", "$replace", $text ) : preg_replace( "/".$r['badbefore']."/i", "$replace", $text );
				}
			}
		}
		return $text;
	}

	function unconvert_flash($flash="")
	{
		global $bboptions;
		$f_arr = explode( "+", $flash );
		return ($bboptions['maxflashwidth'] == $f_arr[0] AND $bboptions['maxflashheight'] == $f_arr[1]) ? '[flash]'.$f_arr[2].'[/flash]' : '[flash='.$f_arr[0].','.$f_arr[1].']'.$f_arr[2].'[/flash]';
	}

	function unconvert_emule($text="")
	{
		$text = stripslashes($text);
		$link = array();
		preg_match_all( "#{emule_(\S+?)}#si", $text, $match );
		for ($i = 0, $n = count($match[0]); $i < $n; $i++) {
			$link[] = rawurldecode(trim($match[1][$i]));
		}
		return '[emule]'.implode("<br />", $link).'[/emule]';
	}

	function unconvert_code($html="", $wysiwyg=0, $ajax=0)
	{
		global $forums;
		$html = preg_replace( '#<span style=\'([^"]*)\'>(.*)</span>#siU', "\\2", $html );
		$html = preg_replace( "#\s*$#", "", $html );
		if (!$wysiwyg) {
			$html = preg_replace("#<br.*>#siU", "\n", $html);
		}
		$strfind = array (
			'&',
			'<',
			'>',
		);
		$strreplace = array (
			'&amp;',
			'&lt;',
			'&gt;',
		);
		if (!$ajax) {
			$html = str_replace($strfind, $strreplace, $html);
		}
		return '[code]'.$html.'[/code]';
	}

	function convert_smilies($code="", $image="")
	{
		if (!$code OR !$image) return;
		$code = stripslashes($code);
		$images = "<img src='images/smiles/".$image."' smilietext='".trim($code)."' border='0' style='vertical-align:middle' alt='".trim($code)."' title='".trim($code)."' />";
		return $images;
	}

	function wrap_style( $type='quote', $extra="" )
	{
		global $forums;
		$used = array(
			'quote' => array( 'title' => $forums->lang['_quote'].':', 'css_top' => 'quotetop' , 'css_main' => 'quotemain'),
			'code'  => array( 'title' => $forums->lang['_code'].':' , 'css_top' => 'codetop'  , 'css_main' => 'codemain'),
			'emule'  => array( 'title' => $forums->lang['_emule'].':' , 'css_top' => 'quotetop'  , 'css_main' => 'quotemain')
		);
		return array( 'start' => "<div class='{$used[ $type ]['css_top']}'>{$used[ $type ]['title']}{$extra}</div><div class='{$used[ $type ]['css_main']}'>", 'end'   => "</div>" );
	}

	function parse_list( $text="", $type="" )
	{
		if ($text == "") return;
		$text = str_replace('\\"', '"', $text);
		return ( $type == "" ) ? "<ul>".$this->parse_list_element($text)."</ul>" : "<ol type='$type'>".$this->parse_list_element($text)."</ol>";
	}

	function parse_list_element($text)
	{
		$text = preg_replace(array( "#\[\*\]#", "#^</?li>#" ), array( "</li><li>", "" ), trim($text));
		return str_replace( "\n</li>", "</li>", $text."</li>" );
	}

	function parse_htmlcode($html="")
	{
		$html = str_replace(array("&lt;", "&gt;", "&quot;", "&#039;"), array("<", ">", '"', "'"), $html);
		return $html;
	}

	function parse_codes($html="")
	{
		global $bbuserinfo, $forums;
		if ($html == "") return;
		$html = preg_replace("#<br.*>#siU", "\n", $html);
		if (preg_match( "/\[(quote|code)\].+?\[\\1\].+?\[\\1\].+?\[\\1\].+?\[\\1\]/i", $html) ) {
			return '';
		}
		$pregfind = array(
			"#:#",
			"#\[#",
			"#\]#",
			"#\)#",
			"#\(#",
			"#^(<br.*>|\s+)#",
			"#&lt;([^&<>]+)&gt;#",
			"#&lt;([^&<>]+)=#",
			"#&lt;/([^&]+)&gt;#",
			"!=(&quot;|&#39;)(.+?)?(&quot;|&#39;)(\s|&gt;)!",
			"!&#60;&#33;--(.+?)--&#62;!",
			"#(\r\n|\r|\n)#",
			"#\t#",
			"#\s{1};#",
			"#\s{2}#"
		);
		$pregreplace = array(
			"&#58;",
			"&#91;",
			"&#93;",
			"&#41;",
			"&#40;",
			'',
			"&lt;<span style='color:blue'>\\1</span>&gt;",
			"&lt;<span style='color:blue'>\\1</span>=",
			"&lt;/<span style='color:blue'>\\1</span>&gt;",
			"=\\1<span style='color:orange'>\\2</span>\\3\\4",
			"&lt;&#33;<span style='color:red'>--\\1--</span>&gt;",
			'<br />',
			'&nbsp;&nbsp;&nbsp;&nbsp;',
			"&#59;",
			'&nbsp;&nbsp;',
		);
		$html = preg_replace($pregfind, $pregreplace, $html);
		$wrap = $this->wrap_style( 'code' );
		return "<!--code-->{$wrap['start']}<!--code1-->$html<!--code2-->{$wrap['end']}<!--code3-->";
	}

	function parse_emule($text="")
	{
		global $bbuserinfo, $forums;
		$text = trim($text);
		if ($text == "") return;
		$text = preg_replace("#(<br.*>|<p.*>)#siU", "\n", $text);
		$emule = explode("\n", $text);
		$i = 0;
		$total_size = 0;
		$emule_info = '';
		foreach ($emule as $link) {
			$link = strip_tags(trim($link));
			if( preg_match('/^(ed2k:\/\/).*/i', $link) ) {
				$info=explode("|",$link);
				if (!$info[3]) continue;
				$this_file_size = $forums->func->fetch_number_format($info[3], true);
				$name = rawurldecode($info[2]);
				$total_size += $info[3];
				$showlink = implode("|", $info);
				$emule_info .= "<div class='row3'><div style='float:right'>{$this_file_size}</div>{emule_".$showlink."} <a href='".$showlink."' target='_blank'>{$name}</a></div>";
				$i++;
			}
		}
		$emule_info .= "<div style='float:right' id='{size_".$showlink."}'>".$forums->func->fetch_number_format($total_size, true)."</div>";
		$wrap = $this->wrap_style( 'emule' );
		return "<!--emule-->{$wrap['start']}<!--emule1-->$emule_info<!--emule2-->{$wrap['end']}<!--emule3-->";
	}

	function parse_attach($aid="")
	{
		$this->aip[] = $aid;
		return "[attachid::{$aid}]";
	}

	function parse_attach_contents($text="")
	{
		global $DB, $forums, $bbuserinfo, $bboptions;
		$forums->func->check_cache('attachmenttype');
		$DB->query("SELECT * FROM ".TABLE_PREFIX."attachment WHERE attachmentid IN (".implode(",", $this->aip).")");
		if ($DB->num_rows()) {
			while ($attach = $DB->fetch_array()) {
				$this->attachinpost[] = $attach['attachmentid'];
				if ( $bboptions['viewattachedimages'] AND $attach['image'] AND $bbuserinfo['candownload'] ) {
					if ( $attach['thumblocation'] AND $attach['thumblocation'] AND $bboptions['viewattachedthumbs']) {
						$attachinfo = "<a href='attachment.php?id={$attach['attachmentid']}&amp;u={$attach['userid']}&amp;extension={$attach['extension']}&amp;attach={$attach['location']}&amp;filename={$attach['filename']}&amp;attachpath={$attach['attachpath']}' title='{$attach['filename']} -  ".$forums->lang['_filesize'].$forums->func->fetch_number_format( $attach['filesize'], true )."' target='_blank'><img src='attachment.php?do=showthumb&amp;u={$attach['userid']}&amp;extension={$attach['extension']}&amp;attach={$attach['thumblocation']}&amp;attachpath={$attach['attachpath']}' width='{$attach['thumbwidth']}' height='{$attach['thumbheight']}' alt='{$attach['filename']} - ".$forums->lang['_filesize'].$forums->func->fetch_number_format( $attach['filesize'], true )." (".$forums->lang['_largeviews'].")' /></a> ";
					} else {
						$attachinfo = "<img src='attachment.php?id={$attach['attachmentid']}&amp;u={$attach['userid']}&amp;extension={$attach['extension']}&amp;attach={$attach['location']}&amp;attachpath={$attach['attachpath']}' alt='".$forums->lang['_uploadimages']."' onload='javascript:if(this.width>screen.width-500)this.style.width=screen.width-500;' onclick='javascript:window.open(this.src);' style='CURSOR: pointer' /> ";
					}
				} else {
					$attachinfo = "<img src='images/".$bbuserinfo['imgurl']."/{$forums->cache['attachmenttype'][ $attach['extension'] ]['attachimg']}' border='0' alt='".$forums->lang['_uploadattachs']."' />&nbsp;<a href='attachment.php?id={$attach['attachmentid']}&amp;u={$attach['userid']}&amp;extension={$attach['extension']}&amp;attach={$attach['location']}&amp;filename={$attach['filename']}&amp;attachpath={$attach['attachpath']}' title='' class='edit' target='_blank'>{$attach['filename']}</a><span class='edit'>( ".$forums->lang['_filesize'].": ".$forums->func->fetch_number_format($attach['filesize'], true)." )</span><br />";
				}
				$text = preg_replace( "#\[attachid::(".$attach['attachmentid'].")\]#is", "<!--attachid::{$attach['attachmentid']}-->{$attachinfo}<!--attachid-->", $text );
			}
		}
		return $text;
	}

	function parse_quotes($text="")
	{
		if ($text == "") return;
		$this->quote_html = $this->wrap_style('quote');
		$pregfind = array(
			"#\[quote\]#ie",
			"#\[quote=([^\],]+?),([^\]]+?),([^\]]+?)\](\s?\r?\n){0,1}#ie",
			"#\[quote=([^\],]+?),([^\]]+?)\](\s?\r?\n){0,1}#ie",
			"#\[quote=([^\]]+?)\]#ie",
			"#(\s?\r?\n){0,1}\[/quote\]#ie"
		);
		$pregreplace = array(
			"\$this->parse_quote_simple()",
			"\$this->parse_quote_begin('\\1', '\\2', '\\3')",
			"\$this->parse_quote_begin('\\1', '\\2', '')",
			"\$this->parse_quote_begin('\\1', '', '')",
			"\$this->parse_quote_end()"
		);
		$text = preg_replace($pregfind, $pregreplace, $text);
		$text = str_replace( array("\n", "\\\""), array("<br />", '"'), $text );
		if ( $this->quote_open == $this->quote_closed ) {
			$text = preg_replace( "#(<!--quote1-->.+?<!--quote2-->)#es", "\$this->parse_preserve_spacing('\\1')", trim($text) );
		}
		return $text;
	}

	function parse_preserve_spacing($text="")
	{
		$text = str_replace( "\\\"", '"', $text );
		return preg_replace(array("#^<!--quote1-->(<br />|<br>)#i", "#(<br />|<br>)<!--quote2-->#i", "#\s{2}#"), array("<!--quote1-->", "<!--quote2-->", "&nbsp; "), trim($text));
	}

	function parse_quote_simple()
	{
		$this->quote_open++;
		return "<!--quote-->{$this->quote_html['start']}<!--quote1-->";
	}

	function parse_quote_begin($name = '', $date = '', $pid = '')
	{
		global $forums;
		$pregfind = array(
			"+",
			"-",
			'[',
			']'
		);
		$pregreplace = array(
			"&#043;",
			"&#045;",
			"&#091;",
			"&#093;"
		);
		$this->quote_open++;
		$var = array('name' => $name, 'date' => $date, 'pid' => $pid);
		$link = $link_var = $extra = '';
		foreach ($var as $key => $value) {
			$value = str_replace($pregfind, $pregreplace, $value);
			if (!$link_var && strstr($value, 'pid')) {
				$id = intval(substr($value, 3));
				$link = '<a href="redirect.php?goto=findpost&p='.$id.'">'.$forums->lang['_viewpost'].'</a>';
				$link_var = $key;
			} else if (!empty($value)) {
				if (!empty($extra)) {
					$extra .= ' &#064; ';
				}
				$extra .= $value;
			}
		}
		$extra .= ($link) ? ' '.$link : '';
		$html = $this->wrap_style('quote', "( $extra )");
		$extra = "-".$name.'+'.$date.'+'.$pid;
		return "<!--quote".$extra."-->{$html['start']}<!--quote1-->";
	}

	function parse_quote_end()
	{
		if ($this->quote_open == 0) {
		 	return;
		}
		$this->quote_closed++;
		return "<!--quote2-->{$this->quote_html['end']}<!--quote3-->";
	}

	function parse_flash($width="", $height="", $url="")
	{
		global $bboptions, $bbuserinfo, $forums;
		$default = '[flash='.$width.','.$height.']'.$url.'[/flash]';
		if (!$bbuserinfo['canuseflash']) {
			return $default;
		}
		if ($width > $bboptions['maxflashwidth']) {
			$this->error = $forums->lang['_bbcodeerror3'];
			return $default;
		}
		if ($height > $bboptions['maxflashheight']) {
			$this->error = $forums->lang['_bbcodeerror4'];
			return $default;
		}
		$url = str_replace(array("\n", "\r\n", "\r"), array('', '', ''), trim($url));
		if (!preg_match( "/^http:\/\/(\S+)\.swf$/i", $url) ) {
			$this->error = $forums->lang['_bbcodeerror5'];
			return $default;
		}
		return "<!--Flash $width+$height+$url--><object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' width='{$width}' height='{$height}'><param name='movie' value='{$url}' /><param name='play' value='true' /><param name='loop' value='true' /><param name='quality' value='high' /><embed src='{$url}' width='{$width}' height='{$height}' play='true' loop='true' quality='high'></embed></object><!--End Flash-->";
	}

	function parse_image($url="")
	{
		global $bboptions, $forums;
		$url = trim($url);
		if (!$url) return;
		$default = "[img]".$url."[/img]";
		if (preg_match("/((a|&#097;)bout|(java|vb)(&#115;|s)cript)(\:|\s)/i", $url))
		{
			return $default;
		}
		if ($bboptions['allowdynimg'] != 1) {
			if (preg_match( "/[?&;]/", $url)) {
				$this->error = $forums->lang['_bbcodeerror6'];
				return $default;
			}
			if ($bboptions['imageextension']) {
				$extension = preg_replace( "#^.*\.(\S+)$#", "\\1", $url );
				$extension = strtolower($extension);
				if ( (! $extension) OR ( preg_match( "#/#", $extension ) ) ) {
					$this->error = $forums->lang['_bbcodeerror7'];
					return $default;
				}
				$bboptions['imageextension'] = strtolower($bboptions['imageextension']);
				if ( ! preg_match( "/".preg_quote($extension, '/')."(,|$)/", $bboptions['imageextension'] )) {
					$this->error = $forums->lang['_bbcodeerror6'];
					return $default;
				}
			}
		} else {
			if (preg_match("/login\.php/i", $url)) {
				return $default;
			}
		}
		return '<img src="'.$url.'" border="0" onclick="javascript:window.open(this.src);" alt="" style="CURSOR: pointer" onload="javascript:if(this.width>screen.width-500)this.style.width=screen.width-500;" />';
	}

	function parse_font($type="", $value="", $text="")
	{
		if (!$type OR !$value OR !$text) return "";
		$type = strtolower($type);
		if ( preg_match( "/;/", $size ) ) {
			$attr = explode( ";", $size );
			$value = $attr[0];
		}
		$value = preg_replace( "/[&\(\)\.\%]/", "", $value );
		$text = str_replace( '\\"', '"', $text );
		if ($type == 'size') {
			if ($value > 30) {
				$value = 30;
			}
			return "<font size=\"".$value."\">".$text."</font>";
		} elseif ($type == 'color') {
			return "<font color=\"".$value."\">".$text."</font>";
		} elseif ($type == 'font') {
			return "<font face=\"".$value."\">".$text."</font>";
		} elseif ($type == 'bgcolor') {
			return "<font style=\"background-color:".$value."\">".$text."</font>";
		}
	}

	function parse_build_url($url=array())
	{
		global $forums;
		$skip_it = 0;
		$url['show'] = str_replace('\"', '"', $url['show']);
		if ( preg_match( "/([\.,\?]|&#33;)$/", $url['html'], $match) ) {
			$url['end'] .= $match[1];
			$url['html'] = preg_replace( "/([\.,\?]|&#33;)$/", "", $url['html'] );
			$url['show'] = preg_replace( "/([\.,\?]|&#33;)$/", "", $url['show'] );
		}
		if (preg_match( "/\[\/(code|quote)/i", $url['html']) ) {
			return $url['html'];
		}
		$url['html'] = str_replace( array("&amp;", "[", "]"), array("&", "%5b", "%5d"), $url['html'] );
		$url['html'] = preg_replace(array("/(a|&#097;)bout:/i", "/(java|vb)(&#115;|s)cript:/i"), array("\\1 bout&#58; ", "\\1 \\2cript&#58; "), $url['html'] );
		if ( ! preg_match("#^(http|news|https|ftp|ed2k|rtsp|mms)://#", $url['html'] ) ) {
			$url['html'] = 'http://'.$url['html'];
		}
		if (preg_match( "/^<img src/i", $url['show'] )) {
			$skip_it = 1;
		}
		$url['show'] = str_replace('&amp;', '&', $url['show'] );
		$url['show'] = preg_replace(array("/(a|&#097;)bout:/i", "/(java|vb)(&#115;|s)cript:/i"), array("\\1 bout&#58; ", "\\1 \\2cript&#58; "), $url['show'] );
		if ((strlen($url['show']) - 58 ) < 3) {
			$skip_it = 1;
		}
		if (!preg_match("/^(http|news|https|ftp|ed2k|rtsp|mms):\/\//i", $url['show'])) {
			$skip_it = 1;
		}
		$show = $url['show'];
		if ($skip_it != 1 && !strstr($url['show'], '......')) {
			$stripped = preg_replace( "#^(http|news|https|ftp|ed2k|rtsp|mms)://(\S+)$#i", "\\2", $url['show'] );
			$uri_type = preg_replace( "#^(http|news|https|ftp|ed2k|rtsp|mms)://(\S+)$#i", "\\1", $url['show'] );
			$show = $uri_type.'://'.$forums->func->fetch_trimmed_title($stripped , 42).'...'.substr($stripped, -15);
		}
		return $url['pp'] . "<a href=\"".$url['html']."\" target=\"_blank\">".$show."</a>" . $url['end'];
	}

	function parse_bash_session($start_tok, $end_tok)
	{
		$start_tok = str_replace( '&amp;', '&', $start_tok );
		$end_tok   = str_replace( '&amp;', '&', $end_tok   );
		if ($start_tok == '?' AND $end_tok == '') {
			return "";
		} elseif ($start_tok == '?' AND $end_tok == '&') {
			return '?';
		} elseif ($start_tok == '&' AND $end_tok == '') {
			return "";
		} elseif ($start_tok == '&' AND $end_tok == '&') {
			return "&";
		} else 	{
			return $start_tok.$end_tok;
		}
	}

	function smile_length_sort($a, $b)
	{
		if ( strlen($a['smiletext']) == strlen($b['smiletext']) ) return 0;
		return ( strlen($a['smiletext']) > strlen($b['smiletext']) ) ? -1 : 1;
	}

	function word_length_sort($a, $b)
	{
		if ( strlen($a['type']) == strlen($b['type']) ) return 0;
		return ( strlen($a['type']) > strlen($b['type']) ) ? -1 : 1;
	}

	function strip_tags_callback($text)
	{
		$text = str_replace('\\"', '"', $text);
		return strip_tags($text, '<p><br>');
	}

	function sanitize_url($type, $url)
	{
		static $find, $replace;
		if (!is_array($find)) {
			$find = array('<', '>');
			$replace = array('&lt;', '&gt;');
		}
		return stripslashes($type) . '="' . str_replace($find, $replace, stripslashes($url)) . '"';
	}

	function parse_style_attribute($tagoptions, &$prependtags, &$appendtags)
	{
		$searchlist = array(
			array('tag' => 'left', 'option' => false, 'regex' => '#text-align:\s*(left);?#i'),
			array('tag' => 'center', 'option' => false, 'regex' => '#text-align:\s*(center);?#i'),
			array('tag' => 'right', 'option' => false, 'regex' => '#text-align:\s*(right);?#i'),
			array('tag' => 'color', 'option' => true, 'regex' => '#(?<![a-z0-9-])color:\s*([^;]+);?#i', 'match' => 1),
			array('tag' => 'font', 'option' => true, 'regex' => '#font-family:\s*([^;]+);?#i', 'match' => 1),
			array('tag' => 'bgcolor', 'option' => true, 'regex' => '#(?<![a-z0-9-])background-color:\s*([^;]+);?#i', 'match' => 1),
			array('tag' => 'b', 'option' => false, 'regex' => '#font-weight:\s*(bold);?#i'),
			array('tag' => 'i', 'option' => false, 'regex' => '#font-style:\s*(italic);?#i'),
			array('tag' => 'u', 'option' => false, 'regex' => '#text-decoration:\s*(underline);?#i')
		);
		$style = $this->parse_wysiwyg_tag_attribute('style=', $tagoptions);
		$style = preg_replace(
			'#(?<![a-z0-9-])(background\-color|color):\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)(;?)#ie',
			'sprintf("\\2: #%02X%02X%02X$4", $1, $2, $3)',
			$style
		);
		foreach ($searchlist AS $searchtag) {
			if (preg_match($searchtag['regex'], $style, $matches)) {
				$prependtags .= '[' . $searchtag['tag'] . ($searchtag['option'] == true ? '=' . $matches["$searchtag[match]"] : "") . ']';
				$appendtags = '[/' . $searchtag['tag'] . "]$appendtags";
			}
		}
	}

	function parse_wysiwyg_anchor($aoptions, $text)
	{
		$href = $this->parse_wysiwyg_tag_attribute('href=', $aoptions);
		if (substr($href, 0, 7) == 'mailto:') {
			$tag = 'email';
			$href = explode("?", $href);
			$href = substr($href[0], 7);
		} else {
			$tag = 'url';
		}
		return "[$tag=$href]" . $this->parse_wysiwyg_code('a', $text, 'parse_wysiwyg_anchor') . "[/$tag]";
	}

	function parse_wysiwyg_paragraph($poptions, $text)
	{
		$style = $this->parse_wysiwyg_tag_attribute('style=', $poptions);
		$align = strtolower($this->parse_wysiwyg_tag_attribute('align=', $poptions));
		switch ($align)
		{
			case 'left':
			case 'center':
			case 'right':
				break;
			default:
				$align = '';
		}
		$prepend = '';
		$append = '';
		$this->parse_style_attribute($poptions, $prepend, $append);
		if ($align) {
			$prepend = "[$align]";
			$append = "[/$align]";
		}
		$append .= "\n\n";
		return $prepend . $this->parse_wysiwyg_code('p', $text, 'parse_wysiwyg_paragraph') . $append;
	}

	function parse_wysiwyg_span($spanoptions, $text)
	{
		$prependtags = '';
		$appendtags = '';
		$this->parse_style_attribute($spanoptions, $prependtags, $appendtags);
		return $prependtags . $this->parse_wysiwyg_code('span', $text, 'parse_wysiwyg_span') . $appendtags;
	}

	function parse_wysiwyg_div($divoptions, $text)
	{
		$prepend = '';
		$append = '';
		$this->parse_style_attribute($divoptions, $prepend, $append);
		$align = $this->parse_wysiwyg_tag_attribute('align=', $divoptions);
		switch ($align)
		{
			case 'left':
			case 'center':
			case 'right':
				break;
			default:
				$align = '';
		}
		if ($align) {
			$prepend .= "[$align]";
			$append .= "[/$align]";
		}
		return $prepend . $this->parse_wysiwyg_code('div', $text, 'parse_wysiwyg_div') . $append;
	}

	function parse_wysiwyg_list_element($listoptions, $text)
	{
		return '[*]' . rtrim($text);
	}

	function parse_wysiwyg_list($listoptions, $text, $tagname)
	{
		$longtype = $this->parse_wysiwyg_tag_attribute('style=', $listoptions);
		$listtype = trim(preg_replace('#"?LIST-STYLE-TYPE:\s*([a-z0-9_-]+);?"?#si', '\\1', $longtype));
		if (empty($listtype) AND $tagname == 'ol') {
			$listtype = 'decimal';
		}
		$text = preg_replace('#<li>((.(?!</li))*)(?=</?ol|</?ul|<li|\[list|\[/list)#siU', '<li>\\1</li>', $text);
		$text = $this->parse_wysiwyg_code('li', $text, 'parse_wysiwyg_list_element');
		$validtypes = array(
			'upper-alpha' => 'A',
			'lower-alpha' => 'a',
			'upper-roman' => 'I',
			'lower-roman' => 'i',
			'decimal' => '1'
		);
		if (!isset($validtypes["$listtype"])) {
			$opentag = '[list]'; // default to bulleted
		} else {
			$opentag = '[list=' . $validtypes[$listtype] . ']';
		}
		return $opentag . $this->parse_wysiwyg_code($tagname, $text, 'parse_wysiwyg_list') . '[/list]';
	}

	function parse_wysiwyg_font($fontoptions, $text)
	{
		$tags = array(
			'font' => 'face=',
			'size' => 'size=',
			'color' => 'color='
		);
		$prependtags = '';
		$appendtags = '';
		$fontoptionlen = strlen($fontoptions);
		foreach ($tags as $bbcode => $locate) {
			$optionvalue = $this->parse_wysiwyg_tag_attribute($locate, $fontoptions);
			if ($optionvalue) {
				$prependtags .= "[$bbcode=$optionvalue]";
				$appendtags = "[/$bbcode]$appendtags";
			}
		}
		$this->parse_style_attribute($fontoptions, $prependtags, $appendtags);
		return $prependtags . $this->parse_wysiwyg_code('font', $text, 'parse_wysiwyg_font') . $appendtags;
	}

	function parse_wysiwyg_code_replacement($options, $text, $tagname, $parseto)
	{
		$useoptions = array();
		if (trim($text) == '') return '';
		if (empty($useoptions["$tagname"])) {
			$text = $this->parse_wysiwyg_code($tagname, $text, 'parse_wysiwyg_code_replacement', $parseto);
			return "[$parseto]{$text}[/$parseto]";
		} else {
			$optionvalue = $this->parse_wysiwyg_tag_attribute($useoptions["$tagname"], $options);
			if ($optionvalue) {
				return "[$parseto=$optionvalue]{$text}[/$parseto]";
			} else {
				return "[$parseto]{$text}[/$parseto]";
			}
		}
	}

	function parse_wysiwyg_code($tagname, $text, $functionhandle, $extraargs = '')
	{
		global $forums;
		$tagname = strtolower($tagname);
		$open_tag = '<'.$tagname;
		$open_tag_len = strlen($open_tag);
		$close_tag = "</$tagname>";
		$close_tag_len = strlen($close_tag);
		$beginsearchpos = 0;
		do {
			$textlower = $forums->func->strtolower($text);
			$tagbegin = @strpos($textlower, $open_tag, $beginsearchpos);
			if ($tagbegin === false) {
				break;
			}
			$strlen = strlen($text);
			$inquote = '';
			$found = false;
			$tagnameend = false;
			for ($optionend = $tagbegin; $optionend <= $strlen; $optionend++) {
				$char = $text{$optionend};
				if (($char == '"' OR $char == "'") AND $inquote == '') {
					$inquote = $char;
				} elseif (($char == '"' OR $char == "'") AND $inquote == $char) {
					$inquote = '';
				} elseif ($char == '>' AND !$inquote) {
					$found = true;
					break;
				} elseif (($char == '=' OR $char == ' ') AND !$tagnameend) {
					$tagnameend = $optionend;
				}
			}
			if (!$found) {
				break;
			}
			if (!$tagnameend) {
				$tagnameend = $optionend;
			}
			$offset = $optionend - ($tagbegin + $open_tag_len);
			$tagoptions = substr($text, $tagbegin + $open_tag_len, $offset);
			$acttagname = substr($textlower, $tagbegin + 1, $tagnameend - $tagbegin - 1);
			if ($acttagname != $tagname) {
				$beginsearchpos = $optionend;
				continue;
			}
			$tagend = strpos($textlower, $close_tag, $optionend);
			if ($tagend === false) {
				break;
			}
			$nestedopenpos = strpos($textlower, $open_tag, $optionend);
			if ($open_tag == '<b' && substr($textlower, $nestedopenpos, 3) == '<br') {
				$nestedopenpos = false;
			}
			while ($nestedopenpos !== false AND $tagend !== false) {
				if ($nestedopenpos > $tagend) {
					break;
				}
				$tagend = strpos($textlower, $close_tag, $tagend + $close_tag_len);
				$nestedopenpos = strpos($textlower, $open_tag, $nestedopenpos + $open_tag_len);
			}
			if ($tagend === false) {
				$beginsearchpos = $optionend;
				continue;
			}
			$localbegin = $optionend + 1;
			$localtext = $this->$functionhandle($tagoptions, substr($text, $localbegin, $tagend - $localbegin), $tagname, $extraargs);
			$text = substr_replace($text, $localtext, $tagbegin, $tagend + $close_tag_len - $tagbegin);

			$beginsearchpos = $tagbegin + strlen($localtext);
		} while ($tagbegin !== false);
		return $text;
	}

	function parse_wysiwyg_tag_attribute($option, $text)
	{
		if (($position = strpos($text, $option)) !== false) {
			$delimiter = $position + strlen($option);
			if ($text{$delimiter} == '"') {
				$delimchar = '"';
			} elseif ($text{$delimiter} == '\'') {
				$delimchar = '\'';
			} else {
				$delimchar = ' ';
			}
			$delimloc = strpos($text, $delimchar, $delimiter + 1);
			if ($delimloc === false) {
				$delimloc = strlen($text);
			} elseif ($delimchar == '"' OR $delimchar == '\'') {
				$delimiter++;
			}
			return trim(substr($text, $delimiter, $delimloc - $delimiter));
		} else {
			return '';
		}
	}
}

?>