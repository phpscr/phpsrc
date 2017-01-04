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
class adminfunctions_cache
{
	function all_recache()
    {
		$this->forum_recache();
		$this->usergroup_recache();
		$this->style_recache();
		$this->moderator_recache();
		$this->stats_recache();
		$this->ranks_recache();
		$this->olranks_recache();
		$this->birthdays_recache();
		$this->bbcode_recache();
		$this->settings_recache();
		$this->smile_recache();
		$this->icon_recache();
		$this->badword_recache();
		$this->banfilter_recache();
		$this->attachmenttype_recache();
		$this->announcement_recache();
		$this->league_recache();
		$this->banksettings_recache();
		$this->globalstick_recache();
		$this->award_recache();
		$this->credit_recache();
		$this->realjs_recache();
		$this->st_recache();
		$this->ad_recache();
		$this->cron_recache();
		$this->usergroup_recache();
		return;
    }

	function cron_recache()
	{
		global $forums;
		$forums->func->update_cache(array('name' => 'cron', 'value' => TIMENOW));
	}

	function banksettings_recache()
	{
		global $forums, $DB;
		$forums->cache['banksettings'] = array();
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."setting WHERE groupid = 18" );
		while ( $r = $DB->fetch_array() ) {
			$value = $r['value'] != "" ?  $r['value'] : $r['defaultvalue'];
			if ( $value == '{blank}' )
				$value = '';
			$forums->cache['banksettings'][ $r['varname'] ] = $value;
		}
		$forums->func->update_cache(array('name' => 'banksettings'));
	}

	function announcement_recache()
    {
    	global $forums, $DB;
    	$forums->cache['announcement'] = array();
		$DB->query("SELECT a.*, u.id AS userid, u.name
				 FROM ".TABLE_PREFIX."announcement a
				  LEFT JOIN ".TABLE_PREFIX."user u on (a.userid=u.id)
				 WHERE a.active != 0 ORDER BY startdate DESC, enddate DESC");
		while ( $r = $DB->fetch_array() ) {
			$start_ok = FALSE;
			$end_ok   = FALSE;
			if ( ! $r['startdate'] ) {
				$start_ok = TRUE;
			} else if ( $r['startdate'] < time() ) {
				$start_ok = TRUE;
			}
			if ( ! $r['enddate'] ) {
				$end_ok = TRUE;
			} else if ( $r['enddate'] > time() ) {
				$end_ok = TRUE;
			}
			if ( $start_ok AND $end_ok ) {
				$forums->cache['announcement'][$r['id']] = array(
					'id' => $r['id'],
					'title' => $r['title'],
					'startdate'	=> $r['startdate'],
					'enddate' => $r['enddate'],
					'forumid' => $r['forumid'],
					'views' => $r['views'],
					'userid' => $r['userid'],
					'username' => $r['name']
				);
			}
		}
		$forums->func->update_cache(array('name' => 'announcement'));
    }

	function attachmenttype_recache()
	{
		global $forums, $DB;
		$forums->cache['attachmenttype'] = array();
		$DB->query( "SELECT extension,mimetype,usepost,useavatar,attachimg FROM ".TABLE_PREFIX."attachmenttype WHERE usepost=1" );
		while ( $r = $DB->fetch_array() ) {
			$forums->cache['attachmenttype'][ $r['extension'] ] = $r;
		}
		$forums->func->update_cache(array('name' => 'attachmenttype'));
	}

	function badword_recache()
	{
		global $forums, $DB;
		$forums->cache['badword'] = array();
		$DB->query( "SELECT badbefore,badafter,type FROM ".TABLE_PREFIX."badword" );
		while ( $r = $DB->fetch_array() ) {
			$forums->cache['badword'][] = $r;
		}
		$forums->func->update_cache(array('name' => 'badword'));
	}

	function banfilter_recache()
	{
		global $forums, $DB;
		$forums->cache['banfilter'] = array();
		$DB->query( "SELECT content FROM ".TABLE_PREFIX."banfilter WHERE type='ip'" );
		while ( $r = $DB->fetch_array() ) {
			$forums->cache['banfilter'][] = $r['content'];
		}
		$forums->func->update_cache(array('name' => 'banfilter'));
	}

	function bbcode_recache()
	{
		global $forums, $DB;
		$forums->cache['bbcode'] = array();
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."bbcode" );
		while ( $r = $DB->fetch_array() ) {
			$forums->cache['bbcode'][] = $r;
		}
		$forums->func->update_cache(array('name' => 'bbcode'));
	}

	function birthdays_recache()
	{
		require_once( ROOT_PATH.'includes/functions_cron.php' );
		$func = new functions_cron();
		require_once( ROOT_PATH.'includes/cron/birthdays.php' );
		$cron = new cron_birthdays();
		$cron->register_class( $func );
		$cron->docron();
	}

	function usergroup_recache()
	{
		global $forums, $DB;
		$forums->cache['usergroup'] = array();
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."usergroup ORDER BY displayorder" );
		while ($i = $DB->fetch_array()) {
			$forums->cache['usergroup'][$i['usergroupid']] = $i;
			$forums->func->update_cache(array( 'name' => 'usergroup_'.$i['usergroupid'], 'value' => $i));
		}
		$forums->func->update_cache(array('name' => 'usergroup'));
	}

	function moderator_recache()
	{
		global $forums, $DB;
		$forums->cache['moderator'] = array();
		$DB->query('SELECT * FROM '.TABLE_PREFIX.'moderator');
		while ($i = $DB->fetch_array()) {
			if ($i['isgroup']) {
				$forums->cache['moderator_group'][$i['usergroupid']][$i['moderatorid']] = $i;
			} else {
				$forums->cache['moderator_user'][$i['userid']][$i['moderatorid']] = $i;
			}
			$forums->cache['moderator_'.$i['forumid']][$i['moderatorid']] = $i;
			$forums->cache['moderator'][$i['moderatorid']] = $i;
		}
		if (is_array($forums->cache["moderator_user"])) {
			foreach ($forums->cache["moderator_user"] AS $userid => $data) {
				$forums->func->update_cache(array('name' => "moderator_user_$userid", 'value' => $data));
			}
		}
		if (is_array($forums->cache['moderator_group'])) {
			foreach ($forums->cache['moderator_group'] AS $usergroupid => $data) {
				$forums->func->update_cache(array('name' => "moderator_group_$usergroupid", 'value' => $data));
			}
		}
		$forumlist = $DB->query("SELECT * FROM " . TABLE_PREFIX . "forum");
		while ($forum = $DB->fetch_array($forumlist)) {
			$forums->func->update_cache(array('name' => 'moderator_'.$forum['id'], 'value' => $forums->cache['moderator_'.$forum['id']]));
		}
		$forums->func->update_cache(array('name' => 'moderator'));
	}

	function ranks_recache()
	{
		global $forums, $DB;
		$forums->cache['ranks'] = array();
		$DB->query( "SELECT id, title, ranklevel, post FROM ".TABLE_PREFIX."usertitle ORDER BY post DESC" );
		while ($i = $DB->fetch_array()) {
			$forums->cache['ranks'][ $i['id'] ] = array( 'title' => $i['title'], 'ranklevel'  => $i['ranklevel'], 'post' => $i['post'] );
		}
		$forums->func->update_cache(array('name' => 'ranks'));
	}

	function olranks_recache()
	{
		global $forums, $DB;
		$forums->cache['olranks'] = array();
		$DB->query( "SELECT onlinerankid, onlinerankimg, maxnum, onlineranklevel FROM ".TABLE_PREFIX."userolrank ORDER BY onlineranklevel" );
		while ($i = $DB->fetch_array())
		{
			$forums->cache['olranks'][ $i['onlinerankid'] ] = array( 'onlinerankimg' => $i['onlinerankimg'], 'maxnum'  => $i['maxnum'], 'onlineranklevel' => $i['onlineranklevel'] );
		}
		$forums->func->update_cache(array('name' => 'olranks'));
	}

	function settings_recache()
	{
		global $forums, $DB;
		$forums->cache['settings'] = array();
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."setting WHERE addcache=1" );
		while ( $r = $DB->fetch_array() ) {
			$value = $r['value'] != "" ?  $r['value'] : $r['defaultvalue'];
			if ( $value == '{blank}' ) {
				$value = '';
			}
			$forums->cache['settings'][ $r['varname'] ] = $value;
		}
		$forums->func->update_cache(array('name' => 'settings'));
	}

	function smile_recache()
	{
		global $forums, $DB;
		$forums->cache['smile'] = array();
		$smile = $DB->query( "SELECT id,smiletext,image FROM ".TABLE_PREFIX."smile ORDER BY displayorder, id" );
		while ( $r = $DB->fetch_array( $smile ) ) {
			$forums->cache['smile'][] = $r;
		}
		$forums->func->update_cache(array('name' => 'smile'));
	}

	function icon_recache()
	{
		global $forums, $DB;
		$forums->cache['icon'] = array();
		$icon = $DB->query( "SELECT id,icontext,image FROM ".TABLE_PREFIX."icon ORDER BY displayorder" );
		while ( $r = $DB->fetch_array( $icon ) ) {
			$forums->cache['icon'][$r['id']] = $r;
		}
		$forums->func->update_cache(array('name' => 'icon'));
	}

	function stats_recache($cache = 1)
	{
		global $forums, $DB, $bboptions, $_INPUT;
		if ($_INPUT['post'] OR $cache) {
			$r = $DB->query_first( "SELECT count(pid) as post FROM ".TABLE_PREFIX."post WHERE moderate != 1" );
			$forums->cache['stats']['totalposts'] = intval($r['post']);
			$r = $DB->query_first( "SELECT count(tid) as thread FROM ".TABLE_PREFIX."thread WHERE visible = 1" );
			$forums->cache['stats']['totalthreads']   = intval($r['thread']);
		}
		if ($_INPUT['users'] OR $cache) {
			$r = $DB->query_first( "SELECT count(id) as users FROM ".TABLE_PREFIX."user WHERE usergroupid <> 2" );
			$forums->cache['stats']['numbermembers'] = intval($r['users']);
		}
		if ($_INPUT['lastreg'] OR $cache) {
			$r = $DB->query_first( "SELECT id, name FROM ".TABLE_PREFIX."user WHERE usergroupid <> 2 ORDER BY id DESC LIMIT 0, 1" );
			$forums->cache['stats']['newusername'] = $r['name'];
			$forums->cache['stats']['newuserid']   = $r['id'];
		}
		if ($_INPUT['online']) {
			$forums->cache['stats']['maxonlinedate'] = time();
			$forums->cache['stats']['maxonline'] = 1;
		}
		if ($cache === 'corntodaypost') {
			$forums->cache['stats']['todaypost'] = 0;
		} else {
			$r = $DB->query_first( "SELECT SUM(todaypost) AS todaypost FROM ".TABLE_PREFIX."forum" );
			$forums->cache['stats']['todaypost'] = intval($r['todaypost']);
		}
		$forums->func->update_cache(array('name' => 'stats'));
	}

	function style_recache($styles=array())
	{
		global $forums, $DB;
		$forums->cache['style'] = array();
		$forums->func->cache_styles();
		foreach($forums->func->stylecache AS $style) {
			if ( ! $style['userselect'] ) {
				continue;
			}
			$styleid = intval($style['styleid']);
			$forums->cache['style'][$styleid] = array(
				'styleid' => $styleid,
				'title' => $forums->func->construct_depth_mark($style['depth'], '--').$style['title'],
				'parentid' => $style['parentid'],
				'parentlist' => $style['parentlist'],
				'userselect' => $style['userselect'],
				'usedefault' => $style['usedefault'],
				'imagefolder' => $style['imagefolder'],
			);
		}
		$forums->func->update_cache(array('name' => 'style'));
	}

	function forum_recache()
	{
		global $forums, $DB, $buildcache;
		$forums->cache['forum'] = array();
		$buildcache = true;
		$forums->cache['forum'] = $forums->func->cache_forums('-1',0,1,1);
		foreach ( $forums->cache['forum'] AS $fid => $r ) {
			$forum_stats[$fid] = array(
				'thread' => intval($r['thread']),
				'post' => intval($r['post']),
				'todaypost' => intval($r['todaypost']),
				'lastthread' => strip_tags($r['lastthread']),
				'lastthreadid' => intval($r['lastthreadid']),
				'lastpost' => $r['lastpost'],
				'lastposter' => $r['lastposter'],
				'lastposterid' => intval($r['lastposterid']),
				'unmodthreads' => intval($r['unmodthreads']),
				'unmodposts' => intval($r['unmodposts']),
			);
			unset($r['thread'], $r['post'], $r['todaypost'], $r['lastthread'], $r['lastthreadid'], $r['lastpost'], $r['lastposter'], $r['lastposterid'], $r['unmodthreads'], $r['unmodposts']);
			$cache_forum[$fid] = $r;
		}
		$forums->func->update_cache( array('name' => 'forum', 'value' => $cache_forum ));
		$forums->func->update_cache( array('name' => 'forum_stats', 'value' => $forum_stats ));
	}

	function league_recache()
	{
		global $forums, $DB;
		$forums->cache['league'] = array();
		$leagues = $DB->query( "SELECT * FROM ".TABLE_PREFIX."league WHERE type != 3 ORDER BY type, displayorder" );
		while ( $r = $DB->fetch_array( $leagues ) ) {
			$forums->cache['league'][] = $r;
		}
		$forums->func->update_cache(array('name' => 'league'));
	}

	function globalstick_recache()
	{
		global $forums, $DB, $bboptions;
		if ($bboptions['enablerecyclebin'] AND $bboptions['recycleforumid'] ) {
			$not_in = " AND forumid != ".intval($bboptions['recycleforumid'])."";
		}
		$forums->cache['globalstick'] = array();
		$gs = $DB->query("SELECT * FROM ".TABLE_PREFIX."thread WHERE sticky = 2".$not_in." ORDER BY lastpost DESC");

		while ($r = $DB->fetch_array($gs)) {
			unset($r['stopic']);
			$forums->cache['globalstick'][] = $r;
		}
		$forums->func->update_cache(array('name' => 'globalstick'));
	}

	function award_recache()
	{
		global $forums, $DB;
		$forums->cache['award'] = array();
		$DB->query("SELECT id, name, explanation, img FROM ".TABLE_PREFIX."award ORDER BY id");
		while ($r = $DB->fetch_array()) {
			$forums->cache['award'][$r['id']] = $r;
		}
		$forums->func->update_cache(array('name' => 'award'));
	}

	function credit_recache()
	{
		global $forums, $DB;
		$forums->cache['credit'] = array();
		$DB->query("SELECT * FROM ".TABLE_PREFIX."credit ORDER BY creditid");
		while ($r = $DB->fetch_array()) {
			$forums->cache['credit']['list'][$r['tag_name']] = $r['name'];
			if ($r['used']) {
				$forums->cache['credit']['showpost'][$r['tag_name']] = $r['name'];
			}
			foreach (array('newthread', 'newreply', 'quintessence', 'award', 'downattach', 'sendpm', 'search', 'c_limit') AS $key) {
				if ($r[$key] || $key == 'c_limit') {
					$forums->cache['credit'][ $key ][$r['tag_name']] = array('name' => $r['name'], 'tag_name' => $r['tag_name'], $key => $r[$key]);
				}
			}
		}
		$forums->func->update_cache(array('name' => 'credit'));
	}

	function realjs_recache()
	{
		global $forums, $DB;
		$forums->cache['realjs'] = array();
		$DB->query("SELECT id, type, jsname, inids, numbers, perline, selecttype, daylimit, orderby, trimtitle, trimdescription, trimpagetext, export, htmlcode FROM ".TABLE_PREFIX."javascript ORDER BY id");
		while ($r = $DB->fetch_array()) {
			$forums->cache['realjs'][$r['id']] = $r;
		}
		$forums->func->update_cache(array('name' => 'realjs'));
	}

	function st_recache()
	{
		global $forums, $DB;
		$forums->cache['st'] = array();
		$DB->query("SELECT id, name, forumids FROM ".TABLE_PREFIX."specialtopic ORDER BY id");
		while ( $r = $DB->fetch_array() ) {
			$forums->cache['st'][$r['id']] = $r;
		}
		$forums->func->update_cache(array('name' => 'st'));
	}

	function blog_cache_recache()
	{
		global $forums,$DB;
		include_once(ROOT_PATH."includes/adminfunctions_blogcache.php");
		$forums->blogcache = new adminfunctions_blogcache();
		$forums->blogcache->all_recache();
	}

	function ad_recache()
	{
		global $forums, $DB;
		$forums->cache['ad'] = array();
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."ad WHERE (endtime =0 OR endtime >= ".time().") AND starttime <= ".time()." ORDER BY type, displayorder" );
		while ( $r = $DB->fetch_array() ) {
			$forums->cache['ad']['content'][$r['id']] = $r['htmlcode'];
			if ($r['ad_in'] == '-1') {
				$forums->cache['ad'][$r['type']]['all'][] = $r['id'];
			} else {
				$forumids = explode(",", $r['ad_in']);
				foreach ($forumids AS $fid) {
					if ($fid == '0') {
						$forums->cache['ad'][$r['type']]['index'][] = $r['id'];
					} else {
						$forums->cache['ad'][$r['type']][$fid][] = $r['id'];
					}
				}
			}
		}
		$forums->func->update_cache(array('name' => 'ad'));
	}
}

?>
