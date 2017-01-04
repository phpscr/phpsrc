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
class functions {

	var $stylecache = array();
	var $fcache = array();
	var $offset = "";
	var $time_offset = 0;
	var $allow_unicode = 1;
	var $convert = '';
	var $cachefunc = '';

	function generate_user_salt($length = 5)
	{
		$salt = '';
		for ($i = 0; $i < $length; $i++) {
			$salt .= chr(mt_rand(32, 126));
		}
		return $salt;
	}

	function cache_styles($styleid = 1, $depth = 0)
	{
		global $forums, $DB, $stylecache;
		if (!is_array($stylecache)) {
			$styles = $DB->query("SELECT * FROM " . TABLE_PREFIX . "style");
			while ($style = $DB->fetch_array($styles)) {
				$stylecache[$style['parentid']][$style['styleid']] = $style;
			}
		}
		if (is_array($stylecache[$styleid])) {
			foreach ($stylecache[$styleid] AS $style) {
				$this->stylecache[$style['styleid']] = $style;
				$this->stylecache[$style['styleid']]['depth'] = $depth;
				$this->cache_styles($style['styleid'], $depth + 1);
			}
		}
	}

	function cache_forums($forumid = '-1', $depth = 0, $renew=0)
	{
		global $DB, $fcache, $count, $order, $buildcache;
		if (!is_array($fcache) OR $renew) {
			$fcache = array();
			$pc = 1;
			$forumlist = $DB->query("SELECT * FROM " . TABLE_PREFIX . "forum ORDER BY parentid, displayorder");
			while ($forum = $DB->fetch_array($forumlist)) {
				$fcache[$forum['parentid']][$forum['displayorder']][$forum['id']] = $forum;
				if ($parentid != $forum['parentid'] ) {
					$pc = 1;
				}
				$count[$forum['parentid']] = $pc++;
				$order[$forum['id']] = $count[ $forum['parentid'] ];
				$parentid=$forum['parentid'];
			}
			$this->forumcache = array();
		}
		if (is_array($fcache[$forumid])) {
			foreach ($fcache[$forumid] AS $holder) {
				foreach ($holder AS $forum) {
					if ($forum['status'] == 2 AND $buildcache) {
						continue;
					}
					if ($forum['url']) {
						$cforum['id'] = $forum['id'];
						$cforum['name'] = $forum['name'];
						$cforum['description'] = $forum['description'];
						$cforum['forumicon'] = $forum['forumicon'];
						$cforum['parentid'] = $forum['parentid'];
						$cforum['parentlist'] = $forum['parentlist'];
						$cforum['url'] = $forum['url'];
					} else {
						foreach( $forum AS $k => $v ) {
							if ( empty($v)) continue;
							$cforum[ $k ] = $v;
						}
					}
					$perms = unserialize(stripslashes($forum['permissions']));
					$cforum['canread'] = $perms['canread'];
					$cforum['canreply'] = $perms['canreply'];
					$cforum['canstart'] = $perms['canstart'];
					$cforum['canupload'] = $perms['canupload'];
					$cforum['canshow'] = $perms['canshow'];
					unset($cforum['permissions']);
					if($buildcache) {
						unset($cforum['customerror']);
					}
					$this->forumcache[$forum['id']] = $cforum;
					$this->forumcache[$forum['id']]['depth'] = $depth;
					$this->forumcache[$forum['id']]['count'] = $count[$forum['parentid']];
					$this->forumcache[$forum['id']]['order'] = $order[$forum['id']];
					$this->cache_forums($forum['id'], $depth + 1);
					unset($cforum);
				}
			}
		}
		return $this->forumcache;
	}

	function check_cache($cache_name = '', $extra = '')
	{
		global $forums;
		if ($forums->cache[$cache_name]) {
			return;
		}
		if (!(@include(ROOT_PATH.'cache/cache/'.$cache_name.'.php'))) {
			if (!is_object($this->cachefunc)) {
				include_once(ROOT_PATH.'includes/adminfunctions_cache.php');
				$this->cachefunc = new adminfunctions_cache();
			}
			$cache_name = $extra ? $extra : $cache_name;
			$recache = $cache_name.'_recache';
			$this->cachefunc->$recache();
		}
	}

	function load_lang($current_lang, $area)
	{
		global $bboptions, $bbuserinfo;
        include_once(ROOT_PATH."lang/{$bboptions['language']}/{$area}.php");
		if ($lang) {
			$current_lang = array_merge((array) $current_lang, $lang);
			unset($lang);
		}
        return $current_lang;
    }

	function update_cache($v = array())
	{
		global $forums, $DB;
		if ($v['name']) {
			if (empty($v['value'])) {
				$v['value'] = $forums->cache[$v['name']];
			} else {
				$forums->cache[$v['name']] = $v['value'];
			}
			$cache_file = ROOT_PATH.'cache/cache/'.$v['name'].'.php';
			@touch($cache_file);
			if (!@is_writable($cache_file))
			{
				@chmod($cache_file, 0777);
			}
			if ($fp = fopen($cache_file, 'rb+'))
			{
				$content = "<?php\n\$forums->cache['{$v['name']}'] = " . $this->build_cachedata($v['value']) . ";\n?>";
				@flock($fp, LOCK_EX | LOCK_NB);
				fwrite($fp, $content);
				@ftruncate($fp, strlen($content));
				@fclose($fp);
			}
		}
	}

	function build_cachedata($value)
	{
		if (is_array($value))
		{
			$lines = array();
			foreach ($value as $k => $v)
			{
				if (empty($v) && !is_array($v))
				{
					continue;
				}
				$lines[] = "'" . addcslashes($k, '\'\\') . "'=>" . $this->build_cachedata($v);
			}

			return 'array(' . implode(',', $lines) . ')';
		}
		else if (is_int($value))
		{
			return $value;
		}
		else if (is_bool($value))
		{
			return ($value) ? 'true' : 'false';
		}
		else
		{
			return "'" . addcslashes($value, '\'\\') . "'";
		}
	}

	function load_template($template)
	{
		global $bbuserinfo, $DB, $forums, $bboptions;
		$forums->func->check_cache('style');
		if (!$bbuserinfo['style'] OR !($forums->cache['style'][$bbuserinfo['style']]['userselect'])) {
			foreach((array) $forums->cache['style'] as $sid => $data) {
				if ($data['usedefault']) {
					$bbuserinfo['style'] = $data['styleid'];
				}
			}
		}
		$this->optemplate++;
		if ($this->optemplate < 2) {
			$bbuserinfo['pminfo'] = '';
			if (($bbuserinfo['pmquota'] > 0) && ($bbuserinfo['pmtotal'] >= $bbuserinfo['pmquota'])) {
				$bbuserinfo['pminfo'] = $forums->lang['_pmfull'];
			} elseif ($bbuserinfo['pmunread'] > 0) {
				$forums->lang['_pmunread'] = sprintf( $forums->lang['_pmunread'], $bbuserinfo['pmunread'] );
				$bbuserinfo['pminfo'] = $forums->lang['_pmunread'];
				if ($bbuserinfo['pmpop'] AND THIS_SCRIPT != 'private') {
					$pmlimit = $bbuserinfo['pmunread'] > 5 ? 5 : $bbuserinfo['pmunread'];
					$bbuserinfo['showpm'] = TRUE;
					$bbuserinfo['newpm'] = array();
					$DB->query( "SELECT p.*, u.name, u.id FROM ".TABLE_PREFIX."pm p
										LEFT JOIN ".TABLE_PREFIX."user u ON (u.id=p.fromuserid)
										WHERE (userid=".$bbuserinfo['id']." AND p.folderid = 0) OR p.usergroupid != 0 ORDER BY p.dateline DESC LIMIT 0, ".$pmlimit."" );
					while ($pm = $DB->fetch_array()) {
						$pm['dateline'] = $this->get_date( $pm['dateline'], 1 );
						if ($pm['usergroupid'] != '-1' AND !empty($pm['usergroupid']) ) {
							if ( preg_match("/,".$bbuserinfo['usergroupid'].",/i", ",".$pm['usergroupid'].",") ) {
								$pm['name'] = $forums->lang['_systeminfo'];
								$bbuserinfo['newpm'][] = $pm;
							}
						} else {
							$bbuserinfo['newpm'][] = $pm;
						}
					}
				}
			}
			if ($bboptions['gzipoutput'] || $bboptions['rewritestatus']) {
				$buffer = ob_get_contents();
				ob_end_clean();
				$user_function = array();
				if ($bboptions['gzipoutput']) {
					$user_function[] = 'ob_gzhandler';
				}
				if ($bboptions['rewritestatus']) {
					$user_function[] = array(&$this, 'rewritestatus');
				}
				@ob_start($user_function);
				echo $buffer;
			}
			if (!IS_WAP) {
				@header("Content-Type: text/html;charset=UTF-8");
			}
		}
		$dot = SAFE_MODE ? "_" : "/";
		$tplfile = ROOT_PATH."cache/templates/cacheid_".$bbuserinfo['style'].$dot.$template.".php";
		if (!file_exists($tplfile)) {
			if ($this->loaded[$template]) {
				exit;
			}
			include_once(ROOT_PATH.'includes/adminfunctions_template.php');
			$recache = new adminfunctions_template();
			$recache->recachetemplates($bbuserinfo['style'], $forums->cache['style'][ $bbuserinfo['style'] ]['parentlist'], $template);
			$this->loaded[$template] = TRUE;
			return $this->load_template($template);
		} else {
			$template = $tplfile;
		}
		return $template;
	}

	function rewritestatus($buffer) {
         $buffer = preg_replace("/forumdisplay.php[?]f=([0-9]+)(?:&amp;|&)st=([0-9]+)(?:&amp;|&)pp=([0-9]+)/i", "forum-\\1-\\2-\\3.html", $buffer);
        $buffer = preg_replace("/forumdisplay.php[?]f=([0-9]+)(?:&amp;|&)filter=quintessence(?:&amp;|&)pp=([0-9]+)/i", "forum-\\1-q-\\2.html", $buffer);
        $buffer = preg_replace("/forumdisplay.php[?]f=([0-9]+)(?:&amp;|&)filter=quintessence/i", "forum-\\1-q.html", $buffer);
        $buffer = preg_replace("/forumdisplay.php[?]f=([0-9]+)(?:&amp;|&)pp=([0-9]+)/i", "forum-\\1-\\2.html", $buffer);
        $buffer = preg_replace("/forumdisplay.php[?]f=([0-9]+)(?:&amp;|&)st=([0-9]+)/i", "forum-\\1-0-\\2.html", $buffer);
        $buffer = preg_replace("/forumdisplay.php[?]f=([0-9]+)/i", "forum-\\1.html", $buffer);
        $buffer = preg_replace("/profile.php[?]u=([0-9]+)/i", "user-\\1.html", $buffer);
        $buffer = preg_replace("/showthread.php[?]t=([0-9]+)(?:&amp;|&)pp=([0-9]+)/i","thread-\\1-\\2.html", $buffer);
        $buffer = preg_replace("/showthread.php[?]t=([0-9]+)/i", "thread-\\1.html", $buffer);
        $buffer = preg_replace("/index.php[?]f([0-9]+)-([0-9]+).html/i", "f-\\1-\\2.html?", $buffer );
        $buffer = preg_replace("/index.php[?]f([0-9]+).html/i", "f-\\1-0.html?", $buffer );
        $buffer = preg_replace("/index.php[?]t([0-9]+)-([0-9]+).html/i", "t-\\1-\\2.html?", $buffer );
        $buffer = preg_replace("/index.php[?]t([0-9]+).html/i" , "t-\\1-0.html?", $buffer );
		$buffer = preg_replace("/html(?:&amp;|&)extra=[^'\" >\/]*/i", "html", $buffer);
        return $buffer;
    }

	function load_style()
    {
    	global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$update = FALSE;
		$forums->func->check_cache('style');
		if ( $_INPUT['styleid'] ) {
			$forums->func->set_cookie("styleid", $_INPUT['styleid']);
			$bbuserinfo['style'] = $_INPUT['styleid'];
			$update = TRUE;
		} else {
			$cookie_style = $forums->func->get_cookie("styleid");
			$bbuserinfo['style'] = $cookie_style ? $cookie_style : $bbuserinfo['style'];
		}
		if ( !$bbuserinfo['style'] OR !$forums->cache['style'][$bbuserinfo['style']]['userselect'] ) {
			foreach((array) $forums->cache['style'] as $sid => $data) {
				if ( $data['usedefault'] ) {
					$bbuserinfo['style'] = $data['styleid'];
					$update = TRUE;
				}
			}
		}
    	if ( $bbuserinfo['id'] AND $update ) {
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET style=".intval($bbuserinfo['style'])." WHERE id=".$bbuserinfo['id']."" );
    	}
		$bbuserinfo['imgurl'] = $forums->cache['style'][ $bbuserinfo['style'] ]['imagefolder'].'/'.$bboptions['language'];
    	return $bbuserinfo;
    }

	function load_forum_style($fs_id = array())
	{
    	global $forums, $bbuserinfo, $bboptions;
		if (!$fs_id) return;
		$forums->func->check_cache('style');
		if($forums->cache['style'][$fs_id]['userselect']) {
			$bbuserinfo['style'] = $fs_id;
			$bbuserinfo['imgurl'] = $forums->cache['style'][  $fs_id ]['imagefolder'].'/'.$bboptions['language'];
		}
	}

	function finish()
    {
		if (!USE_SHUTDOWN) {
        	$this->do_shutdown();
        }
    }

	function debug()
	{
		global $DB, $bboptions, $forums;
		$mtime = explode(' ', microtime());
		$totaltime = sprintf("%01.6f", ($mtime[1] + $mtime[0] - $forums->starttime));
		echo 'Processed in '.$totaltime.' second(s), '.$DB->query_count().' queries, '.$bboptions['gzipstatus'];
	}

	function do_shutdown()
	{
		global $DB;
		$DB->return_die = 0;
		if ($DB->shutdown_queries) {
			foreach($DB->shutdown_queries AS $query) {
				$DB->query_unbuffered($query);
			}
		}
		$DB->return_die = 1;
		$DB->shutdown_queries = array();
		$DB->close_db();
	}

	function fetch_query_sql($queryvalues, $table, $condition = '')
	{
		global $DB;
		$numfields = count($queryvalues);
		if (empty($condition)) {
			$fieldlist_arr = array();
			$valuelist_arr = array();
			foreach($queryvalues AS $fieldname => $value) {
				$fieldlist_arr[] = $fieldname;
				$fieldvalue = (is_numeric($value) AND intval($value) == $value) ? "'$value'" : "'" . $DB->escape_string($value) . "'";
				$valuelist_arr[] = $fieldvalue;
			}
			$fieldlist  = implode("`, `", $fieldlist_arr);
			$valuelist = implode(", ", $valuelist_arr);
			unset($fieldlist_arr, $valuelist_arr);
			$DB->query_unbuffered("INSERT INTO " . TABLE_PREFIX . "$table (`$fieldlist`) VALUES ($valuelist)");
		} else {
			$qs_arr = array();
			foreach($queryvalues as $fieldname => $value) {
				if (is_array($value) && is_numeric($value[1]) && count($value) == 2) {
					$fieldvalue = $fieldname . $value[0] . $value[1];
				} else {
					$fieldvalue = (is_numeric($value) AND intval($value) == $value) ? "'$value'" : "'" . $DB->escape_string($value) . "'";
				}
				$qs_arr[] = '`'.$fieldname."` = ".$fieldvalue;
			}
			$querystring = implode(', ', $qs_arr);
			unset($qs_arr);
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "$table SET $querystring WHERE $condition");
		}
	}

	function fetch_trimmed_title($text, $limit=12)
	{
		$search = array(
			'@\"javascript:.*[^>|^<].*\"@si', // '<' or '>' in javascript
			'@<script[^>]*?>.*?</script>@si', // javascript
            '@<[\\/\\!]*?[^<>]*?>@si', // HTML tags
            '@<style[^>]*?>.*?</style>@siU', // style tags
            '@<![\\s\\S]*?--[ \\t\\n\\r]*>@', // Strip multi-line comments including CDATA
		);
		$limit += strlen($text) - strlen(preg_replace($search, '', $text));
		$val = $this->csubstr($text, 0, $limit);
		$text = $val[1] ? $val[0] . '...' : $val[0];
		return $text;
	}

	function csubstr($text, $start = 0, $limit = 12)
	{
		if (function_exists('mb_substr')) {
			$more = (mb_strlen($text, 'UTF-8') > $limit) ? true : false;
			$text = mb_substr($text, $start, ($limit - 1), 'UTF-8');
			return array($text, $more);
		} else if (function_exists('iconv_substr')) {
			$more = (iconv_strlen($text, 'UTF-8') > $limit) ? true : false;
			$text = iconv_substr($text, $start, ($limit - 1), 'UTF-8');
			return array($text, $more);
		} else {
			preg_match_all('/./u', $text, $ar);
			if (count($ar[0]) > $limit) {
				$more = true;
				$text = implode('', array_slice($ar[0], $start, ($limit - 1)));
            } else {
				$more = false;
                $text = implode('', array_slice($ar[0], $start));
            }
			return array($text, $more);
		}
	}

	function strlen($text)
	{
		if (function_exists('mb_strlen')) {
			return mb_strlen($text, 'UTF-8');
		} else if (function_exists('iconv_strlen')) {
			return iconv_strlen($text, 'UTF-8');
		} else {
			return strlen(utf8_decode($text));
		}
	}

	function init_post($text="")
	{
		$text = str_replace( '$', "&#036;", $text);
		return preg_replace( "/\\\(&amp;#|\?#)/", "&#092;", $text );
	}

	function stripslashes_uni($text)
	{
    	return get_magic_quotes_gpc() ? stripslashes($text) : $text;
    }

	function safetyslashes($text="")
	{
		return str_replace( '\\', "\\\\", $this->stripslashes_uni($text));
	}

	function htmlspecialchars_uni($text = '')
	{
		return str_replace(array('<', '>', '"', "'"), array('&lt;', '&gt;', '&quot;', '&#039;'), preg_replace('/&(?!#[0-9]+;)/si', '&amp;', $text));
	}

	function unhtmlspecialchars($text = '', $unicode = false)
	{
		if ($unicode)
		{
			$text = preg_replace('/&#([0-9]+);/esiU', "\$this->int_utf8('\\1')", $text);
		}

		return str_replace(array('&lt;', '&gt;', '&quot;', '&#039;', '&amp;'), array('<', '>', '"', "'", '&'), $text);
	}

	function convert_andstr($text = '')
	{
		return str_replace(array('&amp;', '&'), array('&', '&amp;'), $text);
	}

	function md5_check()
	{
		global $bbuserinfo;
		return $bbuserinfo['id'] ? md5($bbuserinfo['email'].'&'.$bbuserinfo['password'].'&'.$bbuserinfo['joindate']) : '';
	}

	function is_browser($browser, $version = 0)
	{
		static $is;
		if (!is_array($is)) {
			$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
			$is = array (
				'opera' => 0,
				'ie' => 0,
				'mozilla' => 0,
				'firebird' => 0,
				'firefox' => 0,
				'camino' => 0,
				'konqueror' => 0,
				'safari' => 0,
				'webkit' => 0,
				'webtv' => 0,
				'netscape' => 0,
				'mac' => 0
			);
			if (strpos($useragent, 'opera') !== false) {
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = $regs[2];
			} else if (strpos($useragent, 'msie ') !== false) {
				preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}
			if (strpos($useragent, 'mac') !== false) {
				$is['mac'] = 1;
				if (strpos($useragent, 'applewebkit') !== false) {
					preg_match('#applewebkit/(\d+)#', $useragent, $regs);
					$is['webkit'] = $regs[1];

					if (strpos($useragent, 'safari') !== false) {
						preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
						$is['safari'] = $regs[1];
					}
				}
			}

			if (strpos($useragent, 'konqueror') !== false) {
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = $regs[1];
			}
			if (!$is['safari'] AND !$is['konqueror'] AND strpos($useragent, 'gecko') !== false) {
				preg_match('#gecko/(\d+)#', $useragent, $regs);
				$is['mozilla'] = $regs[1];

				if (strpos($useragent, 'firefox') !== false OR strpos($useragent, 'firebird') !== false OR strpos($useragent, 'phoenix') !== false) {
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = $regs[3];

					if ($regs[1] == 'firefox') {
						$is['firefox'] = $regs[3];
					}
				}
				if (strpos($useragent, 'chimera') !== false OR strpos($useragent, 'camino') !== false) {
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = $regs[2];
				}
			}
			if (strpos($useragent, 'webtv') !== false) {
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = $regs[1];
			}
			if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs)) {
				$is['netscape'] = "$regs[1].$regs[2]";
			}
		}
		$browser = strtolower($browser);
		if (substr($browser, 0, 3) == 'is_') {
			$browser = substr($browser, 3);
		}

		if ($is[$browser]) {
			if ($version) {
				if ($is[$browser] >= $version) {
					return $is[$browser];
				}
			} else {
				return $is[$browser];
			}
		}

		return 0;
	}

	function banned_detect($bline)
	{
		if ( is_array( $bline ) ) {
			$factor = $bline['unit'] == 'd' ? 86400 : 3600;
			$date_end = $bline['timespan'] == -1 ? -1 : (TIMENOW + ( $bline['timespan'] * $factor ));
			return TIMENOW . ':' . $date_end . ':' . $bline['timespan'] . ':' . $bline['unit'] . ':' . $bline['groupid'] . ':' . $bline['banuser'];
		} else {
			$arr = array();
			list( $arr['date_start'], $arr['date_end'], $arr['timespan'], $arr['unit'], $arr['groupid'], $arr['banuser'] ) = explode( ":", $bline );
			return $arr;
		}
	}

	function fetch_permissions($forum_perm = '', $type)
	{
		global $forums, $bbuserinfo;
		if ( ! is_array( $forums->perm_id_array ) ) {
			return false;
		}
		if ( $forum_perm == '-' ) {
			return false;
		} else if ( $forum_perm == '*' ) {
			return true;
		} else if ( $forum_perm == '' ) {
			if ($type == 'canshow') return $bbuserinfo['canshow'];
			if ($type == 'canread') return $bbuserinfo['canviewothers'];
			if ($type == 'canreply') return $bbuserinfo['canreplyothers'];
			if ($type == 'canstart') return $bbuserinfo['canpostnew'];
			if ($type == 'canupload') return $bbuserinfo['attachlimit'] != -1 ? 1 : 0;
		} else {
			$forum_perm_array = explode(',', $forum_perm );
			foreach($forums->perm_id_array as $u_id) {
				if (in_array($u_id, $forum_perm_array)) {
					return true;
				}
			}
			return false;
		}
	}

	function fetch_user_link($name, $id=0)
	{
		global $forums;
		if (!$id) {
			return $name;
		}
		return "<a href='profile.php?{$forums->sessionurl}u={$id}'>{$name}</a>";
	}

	function fetch_online_time($user=array())
	{
		global $forums, $bboptions, $bbuserinfo;
		if (!$user['id']) return "";
		if (!$this->onlineuser[$user['id']]['onlinerankimg']) {
			$user['onlinehours'] = floor ($user['onlinetime']/3600);
			$user['onlinerank'] = floor (( $bboptions['olrankstep']/2 - $bboptions['olrankstart'] + sqrt (pow ($bboptions['olrankstart'] - $bboptions['olrankstep']/2, 2) + 2*$bboptions['olrankstep']*$user['onlinehours']) )/$bboptions['olrankstep'] );
			$user['nextneedhours'] = ($bboptions['olrankstep']/2*pow($user['onlinerank'] + 1, 2) + ($bboptions['olrankstart']-$bboptions['olrankstep']/2)*($user['onlinerank'] + 1))-$user['onlinehours'];

			$temprank = $user['onlinerank'];
			if ($temprank > 0) {
				for ($tc = 0; $tc < 3; $tc++) {
					if ($temprank < 64 AND $temprank >= 16) {
						$this->onlineuser[$user['id']]['onlinerankimg'][1][$tc]['imgsrc']= 'ollevel3';
						$this->onlineuser[$user['id']]['onlinerankimg'][1][$tc]['onlinerank']= $user['onlinerank'];
						$this->onlineuser[$user['id']]['onlinerankimg'][1][$tc]['onlinehours']= $user['onlinehours'];
						$this->onlineuser[$user['id']]['onlinerankimg'][1][$tc]['nextneedhours']= $user['nextneedhours'];
						$temprank = $temprank - 16;
					}
				}

				for ($tc = 0; $tc < 3; $tc++) {
					if ($temprank < 16 AND $temprank >= 4) {
						$this->onlineuser[$user['id']]['onlinerankimg'][2][$tc]['imgsrc']= 'ollevel2';
						$this->onlineuser[$user['id']]['onlinerankimg'][2][$tc]['onlinerank']= $user['onlinerank'];
						$this->onlineuser[$user['id']]['onlinerankimg'][2][$tc]['onlinehours']= $user['onlinehours'];
						$this->onlineuser[$user['id']]['onlinerankimg'][2][$tc]['nextneedhours']= $user['nextneedhours'];
						$temprank = $temprank - 4;
					}
				}
				for ($tc = 0; $tc < 3; $tc++) {
					if ($temprank < 4 AND $temprank >= 1) {
						$this->onlineuser[$user['id']]['onlinerankimg'][3][$tc]['imgsrc']= 'ollevel1';
						$this->onlineuser[$user['id']]['onlinerankimg'][3][$tc]['onlinerank']= $user['onlinerank'];
						$this->onlineuser[$user['id']]['onlinerankimg'][3][$tc]['onlinehours']= $user['onlinehours'];
						$this->onlineuser[$user['id']]['onlinerankimg'][3][$tc]['nextneedhours']= $user['nextneedhours'];
						$temprank = $temprank - 1;
					}
				}

				if ($temprank >= 64) {
					for ($tc = 0; $tc < 4; $tc++) {
						$this->onlineuser[$user['id']]['onlinerankimg'][4][$tc]['imgsrc']= 'ollevel3';
						$this->onlineuser[$user['id']]['onlinerankimg'][4][$tc]['onlinerank']= $user['onlinerank'];
						$this->onlineuser[$user['id']]['onlinerankimg'][4][$tc]['onlinehours']= $user['onlinehours'];
						$this->onlineuser[$user['id']]['onlinerankimg'][4][$tc]['nextneedhours']= $user['nextneedhours'];
					}
				}
			} else {
				$this->onlineuser[$user['id']]['onlinerankimg'][5][0]['imgsrc']= 'ollevel0';
				$this->onlineuser[$user['id']]['onlinerankimg'][5][0]['onlinerank']= $user['onlinerank'];
				$this->onlineuser[$user['id']]['onlinerankimg'][5][0]['onlinehours']= $user['onlinehours'];
				$this->onlineuser[$user['id']]['onlinerankimg'][5][0]['nextneedhours']= $user['nextneedhours'];
			}
		}
		return $this->onlineuser[$user['id']]['onlinerankimg'];
	}

	function fetch_award($data="")
	{
		global $forums;
		$forums->func->check_cache('award');
		$award = explode(",", $data);
		foreach ($award AS $id) {
			if (!$id) continue;
			$show[$id]['img'] = $forums->cache['award'][$id]['img'];
			$show[$id]['name'] = $forums->cache['award'][$id]['name'];
		}
		return $show;
	}

	function fetch_credit($user)
	{
		global $forums;
		$forums->func->check_cache('credit');
		if (count($forums->cache['credit']['showpost'])) {
			foreach ($forums->cache['credit']['showpost'] AS $tag => $name) {
				$credit_post .= "<br />" . $name . ": " . intval($user[$tag]);
			}
		}
		return $credit_post;
	}

	function fetch_user($user = array())
	{
		global $forums, $_USEROPTIONS, $bbuserinfo, $bboptions;
		$user['quintessence'] = $user['quintessence'] ? "<a href='search.php?{$forums->sessionurl}do=search&amp;namesearch=".urlencode($user['name'])."&amp;exactmatch=1&amp;searchin=quintessence' target='_blank'>".$this->fetch_number_format($user['quintessence'])."</a>" : 0;
		$forums->func->check_cache('usergroup');
		$user['name'] = $forums->cache['usergroup'][ $user['usergroupid'] ]['opentag'].$user['name'].$forums->cache['usergroup'][ $user['usergroupid'] ]['closetag'];
		$user['avatar'] = $this->get_avatar( $user['avatarlocation'], $bbuserinfo['showavatars'], $user['avatarsize'], $user['avatartype'] );
		$ranklevel = 0;
		if (isset($user['posts'])) {
			$forums->func->check_cache('ranks');
			if(is_array($forums->cache['ranks'])) {
				foreach($forums->cache['ranks'] as $k => $v) {
					if ($user['posts'] >= $v['post']) {
						if ($forums->cache['usergroup'][ $user['usergroupid'] ]['groupranks']) {
							$user['title'] = $forums->cache['usergroup'][ $user['usergroupid'] ]['groupranks'];
						} else if (!$user['title']) {
							$user['title'] = $forums->cache['ranks'][ $k ]['title'];
						}
						$ranklevel = $v['ranklevel'];
						break;
					}
				}
			}
		}
		if ($user['award_data']) {
			$user['award'] = $this->fetch_award($user['award_data']);
		}
		if($bboptions['openolrank'] ) {
			$forums->func->check_cache('olranks');
			if (count($forums->cache['olranks'])) {
				$user['onlinerankimg'] = $forums->func->fetch_online_time(array('id' => $user['id'], 'onlinetime' => $user['onlinetime']));
			}
		} else {
			$user['onlinerankimg'] = false;
			$user['onlinerank'] = false;
			$user['onlinehours'] = false;
		}
		if ( preg_match("/,".$user['usergroupid'].",/i", ",".$bbuserinfo['canmodrep'].",") ) {
			$user['canmodrep'] = TRUE;
		}
		foreach($_USEROPTIONS AS $optionname => $optionval) {
			$user["$optionname"] = $user['options'] & $optionval ? 1 : 0;
		}
		if ( $forums->cache['usergroup'][ $user['usergroupid'] ]['groupicon'] ) {
			$user['rank'] = 1;
			$user['rank_ext'] = $forums->cache['usergroup'][ $user['usergroupid'] ]['groupicon'];
		} else if ( $ranklevel ) {
			if ( is_numeric( $ranklevel ) ) {
				for ($i = 1; $i <= $ranklevel; ++$i) {
					$user['rank'] = 2;
					$user['rank_ext'][] = 1;
				}
			} else {
				$user['rank'] = 3;
				$user['rank_ext'] = $ranklevel;
			}
		}
		$user['credit_post'] = $this->fetch_credit( $user );
		$user['joindate'] = $this->get_date( $user['joindate'], 3 );
		$user['grouptitle']  = $user['customtitle'] ? $user['customtitle'] : $forums->cache['usergroup'][ $user['usergroupid'] ]['grouptitle'];
		$user['posts']  = $this->fetch_number_format($user['posts']);
		$user['userid'] = $this->fetch_number_format($user['id']);
		$user['pmicon']  = $user['usepm'] ? $user['id'] : 0;
		$user['email_icon']  = $user['hideemail'] ? 0 : $user['id'];
		$user['qq_other']['site'] = urlencode($bboptions['bburl']);
		$user['qq_icon'] = $user['qq'] ? 1 : 0;
		$user['uc_icon'] = $user['uc'] ? 1 : 0;
		$user['popo_icon'] = $user['popo'] ? 1 : 0;
		$user['blog'] = $user['canblog'] ? 1 : 0;
		return $user;
	}

	function fetch_number_format( $number, $bytesize=false )
	{
		global $forums, $bboptions;
		$decimals = 0;
		if ($bytesize) {
			if ($number >= 1073741824) {
				$decimals = 2;
				$number = round($number / 1073741824 * 100 ) / 100;
				$type = "GB";
			} else if ($number >= 1048576) {
				$decimals = 2;
				$number = round($number / 1048576 * 100 ) / 100;
				$type = "MB";
			} else if ($number >= 1024) {
				$decimals = 1;
				$number = round($number / 1024 * 100 ) / 100;
				$type = "KB";
			} else {
				$decimals = 0;
				$type = "Bytes";
			}
		}
		if ($bboptions['numberformat'] != 'none') {
			return str_replace('_', '&nbsp;', number_format($number , $decimals, '.', $bboptions['numberformat'])).$type;
		} else {
			return $number.$type;
		}
	}

	function forumread($set="")
	{
		global $forums;
		if ( $set == "" ) {
			if ( $fread = $this->get_cookie('forumread') ) {
				$farray = unserialize(stripslashes($fread));
				if ( is_array($farray) AND count($farray) > 0 ) {
					foreach( $farray AS $id => $stamp ) {
						$forums->forum_read[$id] = $stamp;
					}
				}
			}
			unset($fread, $farray);
			return TRUE;
		} else {
			$fread = addslashes(serialize($forums->forum_read));
			$this->set_cookie('forumread', $fread);
			return TRUE;
		}
	}

	function my_br2nl($text = '')
	{
		return preg_replace("#(?:\n|\r)?<br.*>(?:\n|\r)?#", "\n", $text );
	}

	function standard_redirect($url = '')
	{
		global $bboptions, $forums;
		$this->do_shutdown();
		if ($url === '') {
			$url = $bboptions['redirecturl'] == 1 ? $bboptions['forumindex'].'?'.$forums->sessionurl : $bboptions['homeurl'];
		}
		$url = str_replace('&amp;', '&', $url );
		if ($bboptions['headerredirect'] == 'refresh') {
			@header("Refresh: 0;url=".$url);
		} else if ($bboptions['headerredirect'] == 'html') {
			@flush();
			echo("<html><head><meta http-equiv='refresh' content='0; url=$url'></head><body></body></html>");
		} else {
			@header("location: ".$url);
		}
		exit();
	}

	function make_password()
	{
		$pass = '';
		$chars = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0',  'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J',  'k', 'K', 'l', 'L', 'm', 'M', 'n', 'N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T',  'u', 'U', 'v', 'V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z');
		$count = count($chars) - 1;
		for ($i = 0; $i < 8; $i++) {
			$pass .= $chars[mt_rand(0, $count)];
		}
		return($pass);
	}

	function folder_icon($thread, $last_time)
	{
		global $forums, $bbuserinfo, $bboptions;
		$icons = 'folder.gif';
		$title = $forums->lang['_nonew'];
		if ( $thread['sticky'] ) {
			$icons = "sticky.gif";
			$title = $forums->lang['_stickthread'];
		} else if ($thread['open'] == 0) {
			$icons = "closedfolder.gif";
			$title = $forums->lang['_closethread'];
		} else if ( $thread['open'] == 2 ) {
			$icons = "movedfolder.gif";
			$title = $forums->lang['_movethread'];
		} else if (($thread['post'] + 1 >= $bboptions['hotnumberposts']) AND ( (isset($last_time) )  && ($thread['lastpost'] <= $last_time ))) {
			$icons = "hotfolder.gif";
			$title = $forums->lang['_nonew'];
		} else if ($thread['post'] + 1 >= $bboptions['hotnumberposts']) {
			$icons = "newhotfolder.gif";
			$title = $forums->lang['_new'];
		} else if ($last_time  && ($thread['lastpost'] > $last_time)) {
			$icons = "newfolder.gif";
			$title = $forums->lang['_new'];
		}
		$return['icons'] = $icons;
		$return['title'] = $title;
		if (($bbuserinfo['_moderator'][ $thread['forumid'] ] OR $bbuserinfo['supermod']) AND $bboptions['isajax']){
			$return['isajax'] = 1;
			return $return;
		} else {
			$return['isajax'] = 0;
			return $return;
		}
	}

	function build_pagelinks($data)
	{
		global $forums;
		$results['pages'] = ceil( $data['totalpages'] / $data['perpage'] );
		$results['total_page'] = $results['pages'] ? $results['pages'] : 1;
		$results['current_page'] = $data['curpage'] > 0 ? intval($data['curpage'] / $data['perpage']) + 1 : 1;
		$prevlink = "";
		$nextlink = "";
		if ($results['total_page'] <= 1) {
			return '';
		} else {
			if ( $results['current_page'] > 1 ) {
				$start = $data['curpage'] - $data['perpage'];
				$prevlink = "<span class='pagelink'><a href='{$data['pagelink']}&amp;pp={$start}' title='".$forums->lang['_prevpage']."'>&lt;</a></span>";
			}
			if ( $results['current_page'] < $results['total_page'] ) {
				$start = $data['curpage'] + $data['perpage'];
				$nextlink = " <span class='pagelink'><a href='{$data['pagelink']}&amp;pp={$start}' title='".$forums->lang['_nextpage']."'>&gt;</a></span>";
			}
			$pagenav = "<span class='pagelink'><a title='".$forums->lang['_jumppage']."' href=\"javascript:multi_page_jump('{$data['pagelink']}','{$data['totalpages']}','{$data['perpage']}');\">Total: {$results['total_page']}</a></span> ";
			$minpage = $results['current_page'] - 6;
			$maxpage = $results['current_page'] + 5;
			$minpage = $minpage < 0 ? 0 : $minpage;
			$maxpage = $maxpage > $results['total_page'] ? $results['total_page'] : $maxpage;
			for( $i = $minpage; $i < $maxpage; ++$i ) {
				$numberid = $i * $data['perpage'];
				$pagenumber = $i+1;
				if ($numberid == $data['curpage']) {
					$curpage .=  " <span class='pagecurrent'>{$pagenumber}</span>";
				} else {
					if ($pagenumber < ($results['current_page'] - 4)) {
						$firstlink = "<span class='pagelink'><a href='{$data['pagelink']}' title='".$forums->lang['_firstpage']."'>&laquo;</a></span> ";
						continue;
					}
					if ($pagenumber > ($results['current_page'] + 4)) {
						$url = "{$data['pagelink']}&amp;pp=".($results['total_page']-1) * $data['perpage'];
						$lastlink = " <span class='pagelink'><a href='$url' title='".$forums->lang['_lastpage']."'>&raquo;</a></span>";
						continue;
					}
					$curpage .= " <span class='pagelink'><a href='{$data['pagelink']}&amp;pp={$numberid}' title='$page'>{$pagenumber}</a></span>";
				}
			}
			return $pagenav.$firstlink.$prevlink.$curpage.$nextlink.$lastlink;
		}
	}

	function build_threadpages($data)
	{
		global $forums, $bbuserinfo;
		$pages = 1;
		if ($data['totalpost']) {
			$totalpages = $pages = (($data['totalpost'] + 1) % $data['perpage']) == 0 ? ($data['totalpost'] + 1) / $data['perpage'] : ceil( ( ($data['totalpost'] + 1) / $data['perpage'] ) );
		}
		if ($data['extra'] AND !$this->extra) {
			$this->extra = "&amp;extra={$data['extra']}";
		}
		if ($pages > 1) {
			$pages = $pages > 7 ? 7 : $pages;
			for ($i = 0 ; $i < $pages ; ++$i ) {
				$real_no = $i * $data['perpage'];
				$page_no = $i + 1;
				if ($page_no == 6 AND $pages > 6) {
					$real_no = ($totalpages - 1) * $data['perpage'];
					$pagelink .= "...";
					$pagelink .= "<span class='minipagelink'><a href='showthread.php?".$forums->sessionurl."t=".$data['id']."&amp;pp=".$real_no."{$this->extra}'>".$totalpages."</a></span>";
					break;
				} else {
					$pagelink .= "<span class='minipagelink'><a href='showthread.php?".$forums->sessionurl."t=".$data['id']."&amp;pp=".$real_no."{$this->extra}'>".$page_no."</a></span>";
				}
			}
			$thread['ppages'] = $data['totalpost'] + 1;
			$pagelink = "&nbsp;<a href=\"javascript:multi_page_jump('showthread.php?".$forums->sessionurl."t=".$data['id']."{$this->extra}',".$thread['ppages'].",".$data['perpage'].");\" title='".$forums->lang['_multipage']."'><img src='images/".$bbuserinfo['imgurl']."/multipage.gif' alt='' border='0' /></a> ".$pagelink;
		}
		return $pagelink;
	}

	function construct_forum_jump($html=1, $override=0)
	{
		global $DB, $forums;
		if ($html == 1) {
			$forumjump = "<form onsubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='forumdisplay.php' method='get' name='jumpmenu'>
			             <input type='hidden' name='s' value='".$forums->sessionid."' />
			             <select name='f' onchange=\"if(this.options[this.selectedIndex].value != -1){ document.jumpmenu.submit() }\">
			             <optgroup label='".$forums->lang['_jumpto']."'>
			              <option value='home'>".$forums->lang['_boardindex']."</option>
			              <option value='search'>".$forums->lang['_search']."</option>
			              <option value='faq'>".$forums->lang['_faq']."</option>
			              <option value='cp'>".$forums->lang['_usercp']."</option>
			              <option value='wol'>".$forums->lang['_online']."</option>
			             </optgroup>
			             <optgroup label='".$forums->lang['_boardjump']."'>";
		}
		$forumjump .= $forums->forum->forum_jump($html, $override);
		if ($html == 1) {
			$forumjump .= "</optgroup>\n</select>&nbsp;<input type='submit' value='".$forums->lang['_ok']."' class='button' /></form>";
		}
		return $forumjump;
	}

	function construct_depth_mark($depth, $depthchar, $depthmark = '',$type = "open")
	{
		$depthtext = '';
		if ($type == 'open')
		{
			for ($i = 0; $i < $depth; $i++) {
				$depthtext .= $depthchar . $depthmark;
			}
		}
		else
		{
			for ($i = 0; $i < $depth; $i++) {
				$depthtext .= $depthmark . $depthchar;
			}
		}
		return $depthtext;
	}

	function clean_email($email = "")
	{
		$email = trim($email);
		$email = str_replace(' ', '', $email);
    	$email = preg_replace("#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", '', $email);
    	if (preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email)) {
    		return $email;
    	}
    	return '';
	}

	function get_time($date, $method='h:i A')
    {
        if ($this->time_offset == 0) {
			$this->offset = $this->get_time_offset();
			$this->time_offset = 1;
        }
        return gmdate($method, ($date + $this->offset));
    }

	function mk_time($hour, $minute, $second, $month, $day, $year)
	{
		if ($this->time_offset == 0) {
			$this->offset = $this->get_time_offset();
			$this->time_offset = 1;
		}
		return gmmktime($hour, $minute, $second, $month, $day, $year) - $this->offset;
    }

    function get_date($date, $method)
    {
		global $forums, $bboptions;
		$timeoptions = array( 1 => $bboptions['standardtimeformat'], 2 => $bboptions['longtimeformat'], 3 => $bboptions['registereddateformat'] );
        if (!$date) return '--';
        if (empty($method)) $method = 2;
        return $this->get_time($date, $timeoptions[$method]);
    }

    function get_time_offset()
    {
    	global $forums, $bbuserinfo, $bboptions;
    	$r = 0;
    	$bbuserinfo['timezoneoffset'] = ($bbuserinfo['timezoneoffset'] !== '') ? $bbuserinfo['timezoneoffset'] : $bboptions['timezoneoffset'];
    	$r = $bbuserinfo['timezoneoffset'] * 3600;
		if ($bboptions['timeadjust']) {
			$r += ($bboptions['timeadjust'] * 60);
		}
		if ($bbuserinfo['dstonoff']) {
			$r += 3600;
		}
		if ($bbuserinfo['timezoneoffset'] > 0 AND $bbuserinfo['timezoneoffset'] != 8 AND strpos($bbuserinfo['timezoneoffset'], '+') === false) {
			$bbuserinfo['timezone'] = $forums->lang['_gmt'] . '+' . $bbuserinfo['timezoneoffset'];
		} else if ($bbuserinfo['timezoneoffset'] == 8 AND strpos($bbuserinfo['timezoneoffset'], '+') === false) {
			$bbuserinfo['timezone'] = $forums->lang['_bjt'];
		} else if ($bbuserinfo['timezoneoffset'] < 0) {
			$bbuserinfo['timezone'] = $forums->lang['_gmt'] . $bbuserinfo['timezoneoffset'] . $forums->lang['_hours'];
		} else {
			$bbuserinfo['timezone'] = $forums->lang['_gmt'];
		}
    	return $r;
    }

	function convert_bits_to_array($bitfield, $_FIELDNAMES)
	{
		$bitfield = intval($bitfield);
		$arry = array();
		foreach ($_FIELDNAMES as $field => $bitvalue) {
			$arry[$field] = ($bitfield & $bitvalue) ? 1 : 0;
		}
		return $arry;
	}

	function convert_array_to_bits($arry, $_FIELDNAMES, $unset = 0)
	{
		$bits = 0;
		foreach($_FIELDNAMES as $fieldname => $bitvalue) {
			if ($arry[$fieldname] == 1) {
				$bits += $bitvalue;
			}
			if ($unset) {
				unset($arry["$fieldname"]);
			}
		}
		return $bits;
	}

    function set_cookie($name, $value = '', $cookiedate = 0)
    {
        global $forums, $bboptions;
        if ( $forums->noheader ) {
        	return '';
        }
        if ($cookiedate > 0) {
        	$expires = TIMENOW + $cookiedate;
        }
        $bboptions['cookiedomain'] = $bboptions['cookiedomain'] == '' ? ''  : $bboptions['cookiedomain'];
        $bboptions['cookiepath']   = $bboptions['cookiepath']   == '' ? '/' : $bboptions['cookiepath'];
        $name = $bboptions['cookieprefix'].$name;
        @setcookie($name, $value, $expires, $bboptions['cookiepath'], $bboptions['cookiedomain']);
    }

    function get_cookie($name)
    {
    	global $bboptions, $_COOKIE;
    	if (isset($_COOKIE[$bboptions['cookieprefix'].$name])) {
    		return rawurldecode($_COOKIE[$bboptions['cookieprefix'].$name]);
		}
    	return FALSE;
    }

    function init_variable()
    {
    	$return = array();
		foreach(array($_GET, $_POST) AS $type) {
			if(is_array($type)) {
				foreach ($type as $k => $v) {
					if (is_array($type[$k])) {
						foreach ($type[$k] as $k1 => $v1) {
							$return[$this->clean_key($k)][$this->clean_key($k1)] = $this->clean_value($v1);
						}
					} else {
						$return[$this->clean_key($k)] = $this->clean_value($v);
					}
				}
			}
		}
		return $return;
	}

    function clean_key($key)
	{
    	if ($key === 0) {
    		return 0;
    	} else if ($key == '') {
    		return '';
    	}
    	return preg_replace(array("/\.\./", "/\_\_(.+?)\_\_/", "/^([\w\.\-\_]+)$/"), array("", "", "$1"), $key);
    }

    function clean_value($val)
    {
		if ($val == '') {
			return "";
		}

		$pregfind = array("&#032;", "&", "<!--", "-->");
		$pregreplace = array( " ", "&amp;", "&#60;&#33;--", "--&#62;" );
		$val = str_replace($pregfind, $pregreplace, $val);

    	$val = preg_replace( "/<script/i", "&#60;script", $val );

		$pregfind = array( ">", "<", "\"", "!", "'" );
		$pregreplace = array ( "&gt;", "&lt;", "&quot;", "&#33;", "&#39;" );
		$val = str_replace($pregfind, $pregreplace, $val);

		$pregfind = array( "/\n/", "/\\\$/", "/\r/" );
		$pregreplace = array( "<br />", "&#036;", "" );
		$val = preg_replace($pregfind, $pregreplace, $val);

    	if ( $this->allow_unicode ) {
			$val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );
		}
    	if ( get_magic_quotes_gpc() ) {
    		$val = stripslashes($val);
    	}
    	return preg_replace( "/\\\(&amp;#|\?#)/", "&#092;", $val );
    }

    function unclean_value($val)
    {
    	$pregfind = array('&amp;', '&gt;', '&lt;', '&quot;', '&#33;', '&#39;'. '&#60;', '&#62;', '&#036;', '&#092;');
    	$pregreplace = array('&', '>', '<', '"', '!', '\'', '<', '>', '$', '\\');
		$val = str_replace($pregfind, $pregreplace, $val);
		return $val;
    }

    function set_up_guest($name='')
	{
    	global $forums, $bboptions;
    	return array(
	    	'name' => $name ? $name : $forums->lang['_guset'],
	    	'id' => 0,
	    	'password' => '',
	    	'email' => '',
	    	'title' => $forums->lang['_unregister'],
	    	'usergroupid' => 2,
	    	'showsignatures' => $bboptions['guestviewsignature'],
	    	'showavatars' => $bboptions['guestviewavatar'],
	    	'timezoneoffset' => $bboptions['timezoneoffset'],
	    );
    }

    function get_avatar($avatar = '', $showavatars = 0, $avatardimension = 'x', $avatartype = '')
    {
    	global $forums, $bboptions;
		if ($avatar=='' AND $bboptions['defaultavatar']) {
			$avatar = $bboptions['defaultavatar'];
		}
    	if (!$avatar OR $showavatars == 0 OR !$bboptions['avatarsenabled'] OR (!$bboptions['allowflash'] AND preg_match("/\.swf/i", $avatar))) {
    		return '';
    	}
    	$davatar_dims = explode('x', $bboptions['avatardimension']);
    	$this_dims = explode('x', $avatardimension);
		if (!$this_dims[0]) {
			$this_dims[0] = $davatar_dims[0];
		}
		if (!$this_dims[1]) {
			$this_dims[1] = $davatar_dims[1];
		}
		$return['width'] = $this_dims[0];
		$return['height'] = $this_dims[1];
		if ( $avatartype == '1' ) {
			if (preg_match ("/\.attach/", $avatar)) {
				$return['type'] = 'object';
				$return['value'] = $avatar;
				return $return;
			} else {
				$return['type'] = 'img';
				$return['src'] = $avatar;
				$return['value'] = $bboptions['uploadurl'].'/avatar/'.$avatar;
				return $return;
			}
		} else if ($bboptions['avatamaxsize'] > 1 AND $avatartype == '2') {
			if (preg_match("/\.attach/", $avatar)) {
				$return['type'] = 'object';
				$return['value'] = $avatar;
				return $return;
			} else {
				$return['type'] = 'img';
				$return['src'] = $bboptions['uploadurl'].'/avatar/'.$avatar;
				$return['value'] = $avatar;
				return $return;
			}
		} else if ($avatar != '') {
			$return['type'] = 'sysimg';
			$return['src'] = $bboptions['bburl'].'/images/avatars/'.$avatar;
			$return['value'] = $avatar;
			return $return;
		} else {
			return '';
		}
    }

    function standard_error($message, $nologin = 0, $replace = '')
    {
    	global $DB, $forums, $bboptions, $bbuserinfo;
		$forums->lang = $this->load_lang($forums->lang, 'error');
		$message = $forums->lang[$message];
		if (!empty($replace)) {
			$message = sprintf($message, $replace);
		}
    	list($user, $domain) = explode('@', $bboptions['emailreceived']);
    	$safe_string = str_replace('&amp;', '&', $this->clean_value(SCRIPT));
    	$DB->shutdown_query("UPDATE ".TABLE_PREFIX."session SET badlocation = 1 WHERE sessionhash='".$forums->sessionid."'");
		if ( !$bbuserinfo['id'] AND THIS_SCRIPT != 'register' AND THIS_SCRIPT != 'login' AND $nologin != 1) {
			$forums->lang = $this->load_lang($forums->lang, 'login');
			$show['errors'] = true;
			$referer = SCRIPTPATH;
			$nav = array($forums->lang['dologin']);
			$pagetitle = $forums->lang['dologin'] . ' - ' . $bboptions['bbtitle'];
			include $this->load_template('login');
			exit;
		} else {
			$nav = array($forums->lang['errorsinfo']);
			$pagetitle = $forums->lang['errorsinfo'] . ' - ' . $bboptions['bbtitle'];
			include $this->load_template('errors');
			exit;
		}
    }

    function board_offline()
    {
    	global $DB, $forums, $bbuserinfo, $bboptions;
    	$row = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."setting WHERE varname='bbclosedreason'");
    	$message = preg_replace("/\n/", "<br />", stripslashes($row['value']));
		$pagetitle = $forums->lang['_closed'] . ' - ' . $bboptions['bbtitle'];
		$nav = array($forums->lang['_closed'] );
		include $this->load_template('offline');
		exit;
    }

    function select_var($array)
	{
    	if (!is_array($array)) {
    		return -1;
    	}
    	ksort($array);
    	$chosen = -1;
    	foreach ($array as $k => $v) {
    		if (isset($v)) {
    			$chosen = $v;
    			break;
    		}
    	}
    	return $chosen;
    }

	function redirect_screen($text = '', $url = '', $override = 0)
    {
    	global $forums, $bboptions, $bbuserinfo;
        if ($override != 1) {
			if ($url == '') {
				$url = $bboptions['redirecturl'] == 1 ? $bboptions['forumindex'] : $bboptions['homeurl'];
			}
    	}
		$url = preg_replace("#(.*)(^|\.php)#", '\\1\\2?'.$forums->sessionurl, str_replace( "?", '', preg_replace("!s=(\w){32}!", '', $url)));
    	if ($bboptions['removeredirect']) {
    		$this->standard_redirect($url);
    	}
    	include $this->load_template('redirect');
		exit;
    }

	function showimage($simg=0)
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$rc = $_INPUT['rc'] ? $_INPUT['rc'] : $this->rc;
		if ($rc == '') {
			return false;
		}
		if (!$row = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."antispam WHERE regimagehash='".addslashes(trim($rc))."'")) {
			return false;
		}
		include_once(ROOT_PATH."includes/functions_user.php");
		$this->fu = new functions_user();
		if ($bboptions['enableantispam'] == 'gd') {
			$this->fu->show_gd_img($row['imagestamp'], $simg);
		} else {
			return $this->fu->show_gif_img($row['imagestamp']);
		}
	}

	function int_utf8($intval)
	{
		$intval = intval($intval);
		switch ($intval)
		{
			case 0:
				return chr(0);
			case ($intval & 0x7F):
				return chr($intval);
			case ($intval & 0x7FF):
				return chr(0xC0 | (($intval >> 6) & 0x1F)) .
					chr(0x80 | ($intval & 0x3F));
			case ($intval & 0xFFFF):
				return chr(0xE0 | (($intval >> 12) & 0x0F)) .
					chr(0x80 | (($intval >> 6) & 0x3F)) .
					chr (0x80 | ($intval & 0x3F));
			case ($intval & 0x1FFFFF):
				return chr(0xF0 | ($intval >> 18)) .
					chr(0x80 | (($intval >> 12) & 0x3F)) .
					chr(0x80 | (($intval >> 6) & 0x3F)) .
					chr(0x80 | ($intval & 0x3F));
		}
	}

	function recache_cache($cache = '')
	{
		if ($cache) {
			if (!is_object($this->cachefunc)) {
				include_once(ROOT_PATH.'includes/adminfunctions_cache.php');
				$this->cachefunc = new adminfunctions_cache();
			}
			$cache .= '_recache';
			$this->cachefunc->$cache();
		}
	}

	function generate_lang()
	{
		global $forums, $bboptions;
		$c = "<optgroup label='{$forums->lang['_selected_lang']}'>\n";
		foreach ($this->lang_list AS $dir => $name) {
			$selected = ($bboptions['language'] == $dir) ? " selected='selected'" : "";
			$c .= "<option value='{$dir}'$selected>{$name}</option>\n";
		}
		$c .= "</optgroup>\n";
		return $c;
	}

	function generate_style()
	{
		global $forums, $bbuserinfo;
		$select_style = "<optgroup label='{$forums->lang['_selected_style']}'>\n";
		$forums->func->check_cache('style');
		foreach ($forums->cache['style'] as $id => $style ) {
			$selected = ($id == $bbuserinfo['style']) ? " selected='selected'" : "";
			$select_style .= "<option value='$id'{$selected}>";
			$parentlist = explode(',', $style['parentlist']);
			$style_prefix = "";
			for ($i = 1, $n = count($parentlist); $i < $n; $i++) {
				$style_prefix .= "--";
			}
			$select_style .= $style_prefix . " " . $style['title'];
			$select_style .="</option>\n";
		}
		$select_style .= "</optgroup>\n";
		return $select_style;
	}

	function check_lang()
	{
		global $forums, $bboptions, $_INPUT, $bbuserinfo;
		@include(ROOT_PATH.'lang/lang.php');
		$this->lang_list = $lang_list;
		if (isset($_INPUT['lang']) && array_key_exists($_INPUT['lang'], $this->lang_list)) {
			$forums->func->set_cookie("language", $_INPUT['lang']);
			$bboptions['language'] = $_INPUT['lang'];
		} else {
			$bboptions['language'] = $forums->func->get_cookie("language");
		}
		if (!$this->lang_list[$bboptions['language']] AND !$this->lang_list[$bboptions['default_lang']]) {
			$forums->func->set_cookie('language', '');
			$bboptions['language'] = "zh-cn";
		}
		$bboptions['language'] = $bboptions['language'] ? $bboptions['language'] : ($bboptions['default_lang'] ? $bboptions['default_lang'] : "zh-cn");
	}

	function convert_encoding($str, $from, $to)
	{
		global $forums;
		if (function_exists('mb_convert_encoding')) {
			$str = mb_convert_encoding($str, $to, $from);
		} elseif (function_exists('iconv')) {
			$str = iconv($from, $to, $str);
		} else {
			if (!is_object($forums->func->convert)) {
				include_once(ROOT_PATH.'includes/functions_encoding.php');
				$forums->func->convert = new Encoding();
			}
			$str = $forums->func->convert->EncodeString($str, $from, $to);
	    }
		return $str;
	}

	function strtolower($text)
	{
		if (function_exists('mb_strtolower')) {
			return mb_strtolower($text, 'UTF-8');
		} else {
			return strtr($text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
		}
	}
}
?>