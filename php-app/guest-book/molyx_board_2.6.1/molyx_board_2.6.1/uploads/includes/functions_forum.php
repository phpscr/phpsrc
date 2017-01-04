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
class functions_forum {

	var $strip_invisible = 1;
	var $forum_cache = array();
	var $foruminfo = array();
	var $moderator_cache = FALSE;
	var $mod_cache = array();

	function forums_init()
	{
		global $forums;
		$forums->func->check_cache('forum');
		$forums->func->check_cache('forum_stats', 'forum');
		$this->forum_cache = array();
		$this->forum_cache['root'] = array();
		foreach((array) $forums->cache['forum'] AS $i => $forum ) {
			if ($this->strip_invisible) {
				if ($forum['canshow'] != '*') {
					if ($forums->func->fetch_permissions($forum['canshow'], 'canshow') != true) {
						continue;
					}
				}
			}
			if ($forum['parentid'] == -1) {
				$forum['parentid'] = 'root';
			}
			$forum = array_merge($forum, (array) $forums->cache['forum_stats'][$forum['id']]);
			$this->forum_cache[$forum['parentid']][$forum['id']] = $forum;
			$this->foruminfo[$forum['id']] = &$this->forum_cache[$forum['parentid']][$forum['id']];
		}
	}

	function single_forum($forumid = '')
	{
		global $forums;
		if ($forumid) {
			$forums->func->check_cache('forum');
			$forums->func->check_cache('forum_stats', 'forum');
			$forum = array_merge((array) $forums->cache['forum'][$forumid], (array) $forums->cache['forum_stats'][$forumid]);
			return $forum;
		} else {
			return array();
		}
	}

	function forums_moderator_cache()
	{
		global $forums;
		$forums->func->check_cache('moderator');
		if (is_array($forums->cache['moderator']) && $forums->cache['moderator']) {
			foreach( $forums->cache['moderator'] as $i => $r ) {
				$this->mod_cache[ $r['forumid'] ][ $r['moderatorid'] ] = array(
					'name' => $r['username'],
					'userid' => $r['userid'],
					'id' => $r['moderatorid'],
					'isgroup' => $r['isgroup'],
					'usergroupname' => $r['usergroupname'],
					'gid' => $r['usergroupid'],
				);
			}
		}
		$this->moderator_cache = true;
	}

	function forums_moderator($forumid="")
	{
		global $DB, $forums, $bboptions;
		$this->forums_moderator_cache();
		if ($forumid == '') {
			return '';
		}
		$mod_string = '';
		$method = (THIS_SCRIPT == 'forumdisplay') ? $bboptions['forumdisplaymoderatorlist'] : $bboptions['indexmoderatorlist'];
		if ( is_array($this->mod_cache[ $forumid ] ) ) {
			if ($method == 1) {
				$mod_string .= "<select style='width: 90px' onchange='redirlocate(this)'><option value='' selected='selected'>".$forums->lang['_moderator']."</option>";
				foreach ($this->mod_cache[ $forumid ] AS $moderator) {
					if ($moderator['isgroup'] == 1) {
						$mod_string .= "<option value='memberlist.php?{$forums->sessionurl}max_results=30&amp;filter={$moderator['gid']}&amp;order=asc&amp;sortby=name&amp;pp=0&amp;b=1'>".$forums->func->fetch_trimmed_title($moderator['usergroupname'], 10)."</option>\n";
					} else {
						$mod_string .= "<option value='profile.php?{$forums->sessionurl}u={$moderator['userid']}'>".$forums->func->fetch_trimmed_title($moderator['name'], 10)."</option>\n";
					}
				}
			} else {
				foreach ($this->mod_cache[ $forumid ] AS $moderator) {
					if ($moderator['isgroup'] == 1) {
						$mod_string .= "<a href='memberlist.php?{$forums->sessionurl}max_results=30&amp;filter={$moderator['gid']}&amp;order=asc&amp;sortby=name&amp;pp=0&amp;b=1'>".$forums->func->fetch_trimmed_title($moderator['usergroupname'], 10)."</a>, ";
					} else {
						$mod_string .= "<a href='profile.php?{$forums->sessionurl}u={$moderator['userid']}'>".$forums->func->fetch_trimmed_title($moderator['name'], 10)."</a>, ";
					}
				}
				$mod_string = substr($mod_string, 0, -2);
			}
			unset($this->mod_cache);
			$mod_string = preg_replace( "!,\s+$!", "", $mod_string );
		} else {
			if ($method == 1) {
				$mod_string .= "<select disabled='disabled' style='width: 90px' onchange='redirlocate(this)'><option value='' selected='selected'>".$forums->lang['_invitemod']."</option>";
			}
		}
		$mod_string .= "</select>";
		return $mod_string;
	}

	function check_permissions($fid, $prompt_login=0, $in='forum', $uid=0)
	{
		global $forums, $bbuserinfo;
		if ( $in == 'thread' AND $bbuserinfo['id'] == $uid) {
			return true;
		}
		$deny_access = true;
		if ( isset($this->foruminfo[$fid]) && $forums->func->fetch_permissions($this->foruminfo[$fid]['canshow'], 'canshow') == true ) {
			if ( $forums->func->fetch_permissions($this->foruminfo[$fid]['canread'], 'canread') == true ) {
				$deny_access = false;
			} else {
				if ( $this->foruminfo[$fid]['showthreadlist'] ) {
					if ( $in == 'forum' ) {
						$deny_access = false;
					} else {
						$this->forums_custom_error($fid);
						$deny_access = true;
					}
				} else {
					$this->forums_custom_error($fid);
					$deny_access = true;
				}
			}
		} else {
			$this->forums_custom_error($fid);
			$deny_access = true;
		}
		if (!$deny_access) {
			if ($this->foruminfo[$fid]['password']) {
				if ( $this->check_password( $fid ) == true ) {
					$deny_access = false;
				} else {
					$deny_access = true;
					if ( $prompt_login == 1 ) {
						$this->forums_show_login( $fid );
					}
				}
			}
		} else {
        	$forums->func->standard_error("cannotviewboard");
        }
	}

	function check_password( $fid )
	{
		global $forums;
		$forum_password = $forums->func->get_cookie( 'forum_'.$fid );
		if ( trim($forum_password) == md5($this->foruminfo[$fid]['password']) ) return TRUE;
		else return FALSE;
	}

	function forums_custom_error( $forumid )
	{
		global $forums, $DB;
		$error = $DB->query_first( "SELECT customerror FROM ".TABLE_PREFIX."forum WHERE id=".$forumid."" );
		if ( $error['customerror'] ) {
			$forums->lang['customerror'] = $error['customerror'];
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."session SET badlocation=1 WHERE sessionhash='".$forums->sessionid."'" );
			$forums->func->standard_error('customerror');
		}
	}

	function forums_show_login( $forumid )
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		if (empty($bbuserinfo['id'])) $forums->func->standard_error("notlogin");
		$pagetitle = $this->foruminfo[$forumid]['name']." - ".$bboptions['bbtitle'];
		$nav = array( "<a href='forumdisplay.php?{$forums->sessionurl}f={$forumid}'>".$this->foruminfo[$forumid]['name']."</a>" );
		include $forums->func->load_template('forum_password');
		exit;
	}

	function forums_get_children( $forumid, $ids=array() )
	{
		if ( is_array( $this->forum_cache[ $forumid ] ) ) {
			foreach( $this->forum_cache[ $forumid ] AS $id => $forum_data ) {
				$ids[] = $forum_data['id'];
				$ids = $this->forums_get_children($forum_data['id'], $ids);
			}
		}
		return $ids;
	}

	function forums_calc_children($forumid, $forum=array())
	{
		global $forums, $bbuserinfo;
		if ( is_array( $this->forum_cache[ $forumid ] ) ) {
			foreach( $this->forum_cache[ $forumid ] AS $id => $data ) {
				if ($data['lastpost'] > $forum['lastpost']) {
					$forum['lastpost'] = $data['lastpost'];
					$forum['fid'] = $data['id'];
					$forum['lastthreadid'] = $data['lastthreadid'];
					$forum['lastthread'] = $data['lastthread'];
					$forum['password'] = $data['password'];
					$forum['lastposterid'] = $data['lastposterid'];
					$forum['lastposter'] = $data['lastposter'];
					$forum['status'] = $data['status'];
				}
				$forum['post'] += $data['post'];
				$forum['thread'] += $data['thread'];
				$forum['todaypost'] += $data['todaypost'];
				if ( $bbuserinfo['supermod'] OR $bbuserinfo['_moderator'][ $data['id'] ]['canmoderateposts'] ) {
					$forum['unmodposts']  += $data['unmodposts'];
					$forum['unmodthreads'] += $data['unmodthreads'];
				}
				$forum['subforums'][ $data['id'] ] = "<a href='forumdisplay.php?{$forums->sessionurl}f={$data['id']}'>{$data['name']}</a>";
				$forum = $this->forums_calc_children($data['id'], $forum);
			}
		}
		return $forum;
	}

	function forums_nav($forumid, $reffer='')
	{
		global $forums;
		$ids = explode(',', $this->foruminfo[$forumid]['parentlist'] );
		if ( is_array($ids) AND count($ids) ) {
			foreach( $ids AS $id ) {
				if ($id == -1) continue;
				$data = $this->foruminfo[$id];
				if ($reffer AND $data['id'] == $forumid) {
					$reffer = rawurldecode($reffer);
					$nav_array[] = "<a href='forumdisplay.php?{$forums->sessionurl}f={$data['id']}{$reffer}'>{$data['name']}</a>";
				} else {
					$nav_array[] = "<a href='forumdisplay.php?{$forums->sessionurl}f={$data['id']}'>{$data['name']}</a>";
				}
			}
		}
		return array_reverse($nav_array);
	}

	function forum_jump($html=0, $override=0)
	{
		global $forums, $_INPUT;
		$forums->func->check_cache('forum');
		foreach((array) $forums->cache['forum'] AS $id => $forum) {
			if ( $this->strip_invisible ) {
				if ( $forum['canshow'] != '*' ) {
					if ( $forums->func->fetch_permissions($forum['canshow'], 'canshow') != TRUE ) {
						continue;
					}
				}
			}
			if ($forum['url']) {
				continue;
			}
			$forumlist[] = array( $forum['id'],  );
			if ($html == 1 OR $override == 1) {
				$selected = ($_INPUT['f'] AND $_INPUT['f'] == $forum['id']) ? " selected='selected'" : "";
			}
			$forum_jump .= "<option value='".$forum['id']."'".$selected.">".$forums->func->construct_depth_mark($forum['depth'], '--')." ".$forum['name']."</option>\n";
		}
		return $forum_jump;
	}

	function forums_format_lastinfo($forum)
	{
		global $forums, $bbuserinfo, $bboptions;
		$show_subforums = TRUE;
		$forum['img_new_post'] = $this->forums_new_post($forum);
		$forum['lastpost'] = $forums->func->get_date($forum['lastpost'], 2);
		$forum['lastthread'] = $forum['lastthread'];
		$forum['full_lastthread'] = $forum['lastthread'];
		if ($forum['lastthreadid']) {
			$forum['lastthread'] = str_replace('&#33;', '!', $forum['lastthread']);
			$forum['lastthread'] = str_replace('&quot;', '"', $forum['lastthread']);
			$forum['lastthread'] = $forums->func->fetch_trimmed_title(strip_tags($forum['lastthread']), 14);
			if ( $forum['password'] OR ( $forums->func->fetch_permissions($forum['canread'], 'canread') != TRUE AND !$forum['showthreadlist'] ) ) {
				$forum['lastthread'] = $forums->lang['_hiddenthread'];
			} else {
				$forum['lastunread'] = 1;
				$forum['lastthread']  = "<a href='redirect.php?{$forums->sessionurl}t={$forum['lastthreadid']}&amp;goto=newpost' title='{$forum['full_lastthread']}'>{$forum['lastthread']}</a>";
			}
			if ( isset($forum['lastposter'])) {
				$forum['lastposter'] = $forum['lastposterid'] ? "<a href='profile.php?{$forums->sessionurl}u={$forum['lastposterid']}'>{$forum['lastposter']}</a>" : $forum['lastposter'];
			} else {
				$forum['lastposter'] = "----";
			}
		} else {
			$forum['lastthread'] = '';
		}
		$forum['post']  = $forums->func->fetch_number_format($forum['post']);
		$forum['thread'] = $forums->func->fetch_number_format($forum['thread']);
		$forum['todaypost'] = $forums->func->fetch_number_format($forum['todaypost']);
		$forum['moderator'] = $this->forums_moderator($forum['id']);
		if ( $bboptions['showsubforums'] AND !$forum['password'] ) {
			if ( count( $forum['subforums'] ) ) {
				$forum['show_subforums'] = "<div><strong>{$forums->lang['_subforums']}</strong>: ".implode( ' ', $forum['subforums'] )."</div>";
			}
		}
		if ( ( $bbuserinfo['supermod'] OR $bbuserinfo['_moderator'][ $forum['id'] ]['canmoderateposts'] == 1 )
		   AND ( $forum['unmodposts'] OR $forum['unmodthreads'] ) ) {
			$forum['moderateinfo'] = "&nbsp;(<span class='description'>{$forums->lang['_unmoderate']}<a href='forumdisplay.php?{$forums->sessionurl}f={$forum['id']}&amp;filter=visible&amp;daysprune=100'>{$forums->lang['_thread']}</a>: {$forum['unmodthreads']}, <a href='search.php?{$forums->sessionurl}do=findmod&amp;forumlist={$forum['id']}&amp;searchsubs=0'>{$forums->lang['_post']}</a>: {$forum['unmodposts']}</span>)";
		}
		return $forum;
	}

	function forums_new_post($forum)
	{
        global $forums, $bbuserinfo, $_INPUT;
        $lastvisit = $_INPUT['lastvisit'];
        $fid   = ($forum['fid'] == "") ? $forum['id'] : $forum['fid'];
        $readtime = $forums->forum_read[ $fid ];
        $lastvisit = $readtime > $lastvisit ? $readtime : $lastvisit;
		if ( ! $forum['status'] ) {
			return 'readonly';
		}
        if ($forum['password']) {
			return $forum['lastpost'] > $lastvisit ? 'brnew' : 'brnonew';
        }
		return $forum['lastpost']  > $lastvisit ? 'bfnew' : 'bfnonew';
    }
}

?>