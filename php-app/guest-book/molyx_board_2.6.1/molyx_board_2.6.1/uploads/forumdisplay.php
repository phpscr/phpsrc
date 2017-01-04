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
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'forumdisplay');

require_once('./global.php');

class forum {
	var $posthash = '';
	var $threadread = array();
	var $forum = array();
	var $newpost = 0;
	var $extra = "";

    function show()
    {
        global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$forums->lang = $forums->func->load_lang($forums->lang, 'forumdisplay' );
		$this->posthash = $forums->func->md5_check();
        if ( $read = $forums->func->get_cookie('threadread') ) {
        	$this->threadread = unserialize($read);
		}
		$forums->forum->forums_init();
		switch ($_INPUT['f'])
		{
			case 'search':
				$goto = 'search.php';
				break;
			case 'wol':
				$goto = 'online.php';
				break;
			case 'cp':
				$goto = 'usercp.php';
				break;
			case 'home':
				$goto = $bboptions['forumindex'];
				break;
			case 'faq':
				$goto = 'faq.php';
				break;
		}
		if ($goto != '') {
			$forums->func->standard_redirect("$goto?".$forums->sessionurl);
		}
		$forumid = intval($_INPUT['f']);
		$this->forum = $forums->forum->single_forum($forumid);
		if (! $this->forum['id'] ) {
        	$forums->func->standard_error("cannotfindforum");
		}
		$forums->func->load_forum_style($this->forum['style']);
		$this->forum['forum_jump'] = $forums->func->construct_forum_jump();
		if ( $_INPUT['pwd'] ) {
			$this->check_permissions();
		} else {
			$forums->forum->check_permissions($this->forum['id'], 1);
		}

		if ($this->forum['allowposting']) {
			$this->render_forum($this->forum['id']);
		} else {
			$this->show_subforums($this->forum['id']);
		}
     }

	function show_subforums($fid)
	{
		global $DB, $forums, $bboptions, $bbuserinfo;
		$pagetitle = $this->forum['name']." - ".$bboptions['bbtitle'];
		$nav = $forums->forum->forums_nav( $this->forum['id'] );
		$rsslink = true;
		include $forums->func->load_template('forumdisplay');
		exit;
    }

	function check_permissions()
	{
		global $forums, $_INPUT;
		if ($_INPUT['password'] == "") {
			$forums->func->standard_error("requiredpassword");
		}
		if ( $_INPUT['password'] != $this->forum['password'] ) {
			$forums->func->standard_error("errorforumpassword");
		}
		$forums->func->set_cookie( "forum_".$this->forum['id'], md5($_INPUT['password']) );
		$forums->func->standard_redirect("forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}");
	}

	function render_forum($fid)
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$posthash = $this->posthash;
		$forum = $this->forum;
		$forums->func->check_cache('announcement');
		$show['announce'] = false;
		if ( is_array( $forums->cache['announcement'] ) AND count( $forums->cache['announcement'] ) ) {
			$announcement = array();
			foreach( $forums->cache['announcement'] AS $id => $announce ) {
				if (  $announce['forumid'] == -1 OR strstr( ','.$announce['forumid'].',', ','.$this->forum['id'].',' ) ) {
					$show['announce'] = true;
					if ( $announce['startdate'] ) {
						$announce['startdate'] = $forums->func->get_date($announce['startdate'], 'Y-m-d');
					} else {
						$announce['startdate'] = '&nbsp;';
					}
					$announcement[$id] = $announce;
				}
			}
		}
		$firstpost = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
        $_INPUT['lastvisit'] = $forums->forum_read[ $this->forum['id'] ] > $_INPUT['lastvisit'] ? $forums->forum_read[ $_INPUT['f'] ] : $_INPUT['lastvisit'];
		$daysprune = $forums->func->select_var(array(1 => $_INPUT['daysprune'], 2 => $this->forum['prune'], 3 => '100'));
		$sortby = $forums->func->select_var(array(1 => $_INPUT['sortby'], 2 => $this->forum['sortby'], 3 => 'lastpost'));
		$threadfilter = $_INPUT['filter'] ? $_INPUT['filter'] : 'all';
		$threadprune = $daysprune != 100 ? (TIMENOW - ($daysprune * 60 * 60 * 24)) : 0;
		$bboptions['maxthreads'] = $bboptions['maxthreads'] ? $bboptions['maxthreads'] : '20';
		if($this->forum['specialtopic']) {
			$forums->func->check_cache('st');
			$forums->cache['st'][0]['name'] = $forums->lang['st_all'];
			$this->forum['specialtopic'] = '0,'.$this->forum['specialtopic'];
            $st = explode(",", $this->forum['specialtopic']);
			$st_cache = $forums->cache['st'];
			$st_current = intval($_INPUT['st']);
        }
		$sort = array( 'lastpost'		=> $forums->lang['lastpost'],
							'lastposter'		=> $forums->lang['lastposter'],
							'title'				=> $forums->lang['title'],
							'postusername'=> $forums->lang['postusername'],
							'dateline'		=> $forums->lang['dateline'],
							'attach'			=> $forums->lang['attach'],
							'post'				=> $forums->lang['post'],
							'views'			=> $forums->lang['views'],
						);
		$prune_by_day = array( '1' => $forums->lang['today'],
											'5' => $forums->lang['from'].' 5 '.$forums->lang['days'],
											'7' => $forums->lang['from'].' 7 '.$forums->lang['days'],
											'10' => $forums->lang['from'].' 10 '.$forums->lang['days'],
											'15' => $forums->lang['from'].' 15 '.$forums->lang['days'],
											'20' => $forums->lang['from'].' 20 '.$forums->lang['days'],
											'25' => $forums->lang['from'].' 25 '.$forums->lang['days'],
											'30' => $forums->lang['from'].' 30 '.$forums->lang['days'],
											'60' => $forums->lang['from'].' 60 '.$forums->lang['days'],
											'90' => $forums->lang['from'].' 90 '.$forums->lang['days'],
											'100' => $forums->lang['fromboardopen'],
											);
		$filter = array( 'all' => $forums->lang['allthread'],
									'open' => $forums->lang['openthread'],
									'closed' => $forums->lang['closedthread'],
									'quintessence' => $forums->lang['quintessencethread'],
									'hot' => $forums->lang['hotthread'],
									'poll' => $forums->lang['poll'],
									'moved' => $forums->lang['movedthread'],
									);
		if ( $bbuserinfo['is_mod'] ) {
        	$filter['visible'] = $forums->lang['visiblethread'];
        }
        if ( $bbuserinfo['id'] ) {
        	$filter['started'] = $forums->lang['istarted'];
        	$filter['replied'] = $forums->lang['ireplied'];
        }
		if ( (!isset($filter[$threadfilter])) OR (!isset($sort[$sortby])) OR (!isset($prune_by_day[$daysprune])) ) {
			$forums->func->standard_error("errororderlist");
		}
	    $r_sort_by = $sort_by == 'asc' ? 'ASC' : 'DESC';
	    $queryarray = array();
	    $addquery = "";
	    switch( $threadfilter )
	    {
	    	case 'all':
	    		break;
	    	case 'open':
	    		$queryarray[] = "open=1";
	    		break;
	    	case 'closed':
	    		$queryarray[] = "open=0";
	    		break;
	    	case 'quintessence':
	    		$queryarray[] = "quintessence=1";
	    		break;
	    	case 'hot':
	    		$queryarray[] = "open=1 AND post + 1 >= ".intval($bboptions['hotnumberposts']);
	    		break;
	    	case 'moved':
	    		$queryarray[] = "open=2";
	    		break;
	    	case 'poll':
	    		$queryarray[] = "pollstate=1";
	    		break;
	    	default:
	    		break;
	    }
		if ( $bbuserinfo['is_mod'] AND $threadfilter == 'visible' ) {
			$queryarray[] = "visible=0";
		}
	    if ( ! $bbuserinfo['canviewothers'] OR $threadfilter == 'started' ) {
            $queryarray[] = "postuserid='".$bbuserinfo['id']."'";
		}
		if($_INPUT['st']) {
           $queryarray[] = "stopic=".intval($_INPUT['st']);
       }
		if ( count($queryarray) ) {
			$addquery = ' AND '. implode( ' AND ', $queryarray );
		}
		if ( ! $bbuserinfo['is_mod'] ) {
			$visible = 'and visible=1';
		} else {
			$visible = '';
		}
		if ( $threadfilter == 'replied' ) {
			if ( $threadprune ) {
				$prune_filter = "and (sticky=1 OR lastpost > $threadprune)";
			} else {
				$prune_filter = "";
			}
			$threadscount = $DB->query_first("SELECT COUNT(DISTINCT(p.threadid)) as threads
				FROM ".TABLE_PREFIX."thread t
					LEFT JOIN ".TABLE_PREFIX."post p ON (p.threadid=t.tid AND p.userid='".$bbuserinfo['id']."' AND p.newthread=0)
				WHERE t.forumid='".$this->forum['id']."' {$visible} {$prune_filter}"
			);
		} else if ( $addquery OR $threadprune ) {
			$threadscount = $DB->query_first("SELECT COUNT(*) as threads FROM ".TABLE_PREFIX."thread WHERE forumid='".$this->forum['id']."' {$visible} AND (sticky=1 OR lastpost > {$threadprune}){$addquery}");
		} else {
			$threadscount['threads'] = $this->forum['thread'];
			$threadprune = 0;
		}
		if ($_INPUT['daysprune']) {
			$this->extra .= "&amp;daysprune=$daysprune";
		}
		if ($_INPUT['sortby']) {
			$this->extra .= "&amp;sortby=$sortby";
		}
		if ($_INPUT['filter']) {
			$this->extra .= "&amp;filter=$threadfilter";
		}
		if ($_INPUT['st']) {
			$this->extra .= "&amp;st={$_INPUT['st']}";
		}
		$forum['pagenav'] = $forums->func->build_pagelinks(
								array( 'totalpages'  => $threadscount['threads'],
											'perpage'    => $bboptions['maxthreads'],
											'curpage'  => $firstpost,
											'pagelink'    => "forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}{$this->extra}",
										  )
								   );
		if ($threadscount['threads'] < 1) {
			$show['nopost'] = TRUE;
		}
		$thread_array = array();
		$threadids   = array();
		$thread_sort  = "";
		if ( $threadprune ) {
			$query = "forumid=".$this->forum['id']." AND sticky != 2 {$visible} AND (lastpost > {$threadprune} OR sticky=1)";
		} else {
			$query = "forumid=".$this->forum['id']." AND sticky != 2 {$visible}";
		}
		if ($bboptions['threadpreview'] == 1) {
			$previewfield = "p.pagetext AS preview,p.hidepost,";
			$previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post p ON(p.pid = t.firstpostid)";
		} else if ($bboptions['threadpreview'] == 2) {
			$previewfield = "p.pagetext AS preview,p.hidepost,";
			$previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post p ON(p.pid = t.lastpostid)";
		} else {
			$previewfield = '';
			$previewjoin = '';
		}
		$shownormal = FALSE;
		$threadlist = array();
		if ($this->extra OR $_INPUT['pp']) {
			$this->extra .= "&amp;pp={$_INPUT['pp']}";
			$this->extra = urlencode(str_replace("&amp;", "&", $this->extra));
		}
		$forums->func->check_cache('globalstick');
		if (is_array($forums->cache['globalstick'])) {
			foreach ($forums->cache['globalstick'] AS $tid => $t) {
				if (empty($t['iconid']))
				{
					$t['iconid'] = 2;
				}
				$thread_array[ $t['tid'] ] = $t;
				$threadids[$t['tid']] = $t['tid'];
				$thread = $this->parse_data( $thread_array[ $t['tid'] ] );
				$thread['extra'] = $this->extra ? "&amp;extra=".$this->extra : "";
				$thread['class1'] = "row1";
				$thread['class2'] = "row2";
				$shownormal=TRUE;
				if ( !$thread['visible'] ) {
					if ($bbuserinfo['is_mod']) {
						$thread['class1'] = 'row1shaded';
						$thread['class2'] = 'row2shaded';
					} else {
						continue;
					}
				}
				$threadlist[] = $thread;
			}
		}
		$forums->func->check_cache('banksettings');
		if ( $threadfilter == 'replied' ) {
			$previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post p ON(p.threadid = t.tid AND p.userid='".$bbuserinfo['id']."')";
			$threads = $DB->query("SELECT DISTINCT(p.userid), t.*
												FROM ".TABLE_PREFIX."thread t
												$previewjoin
												WHERE {$query}  AND p.newthread=0
												ORDER BY sticky DESC, {$sortby} {$r_sort_by}
												LIMIT ".$firstpost.", ".$bboptions['maxthreads'].""
											);
		} else {
			$threads = $DB->query( "SELECT $previewfield t.* FROM ".TABLE_PREFIX."thread t $previewjoin WHERE {$query}{$addquery} ORDER BY sticky DESC, {$sortby} {$r_sort_by} LIMIT ".$firstpost.", ".$bboptions['maxthreads']."" );
		}
		while ( $t = $DB->fetch_array($threads) ) {
			$thread_array[ $t['tid'] ] = $t;
			$threadids[ $t['tid'] ] = $t['tid'];
			$thread = $this->parse_data( $thread_array[ $t['tid'] ] );
			$thread['extra'] = $this->extra ? "&amp;extra=".$this->extra : "";
			$thread['class1'] = "row1";
			$thread['class2'] = "row2";
			if ($t['sticky']) {
				$shownormal=TRUE;
			}
			if ($t['sticky']==0 AND $shownormal==TRUE) {
				$thread['shownormal'] = TRUE;
				$shownormal=FALSE;
			}
			if ( ! $thread['visible'] AND $bbuserinfo['is_mod'] ) {
				$thread['class1'] = 'row1shaded';
				$thread['class2'] = 'row2shaded';
			}
			if ($thread['pollstate'] == 1) {
				$thread['thread_icon'] = 2;
			} else {
				$thread['thread_icon'] = 1;
			}
			$thread['open'] = intval($thread['open']);
			$threadlist[] = $thread;
		}
		foreach ($sort as $k => $v) {
			$sort_html .= $k == $sortby ? "<option value='$k' selected='selected'>".$sort[ $k ]."</option>\n" : "<option value='$k'>".$sort[ $k ]."</option>\n";
		}
		foreach ($prune_by_day as  $k => $v) {
			$daysprune_html .= $k == $daysprune ? "<option value='$k' selected='selected'>".$prune_by_day[ $k ]."</option>\n" : "<option value='$k'>".$prune_by_day[ $k ]."</option>\n";
		}
		foreach ($filter as  $k => $v) {
			$filter_html .= $k == $threadfilter ? "<option value='$k' selected='selected'>".$filter[ $k ]."</option>\n" : "<option value='$k'>".$filter[ $k ]."</option>\n";
		}
		$show['sort_by'] = $sort_html;
		$show['sort_prune'] = $daysprune_html;
		$show['thread_filter'] = $filter_html;
		if ($this->newpost < 1) {
			$forums->forum_read[ $this->forum['id'] ] = TIMENOW;
			$forums->func->forumread(1);
		}
		if ($bboptions['showforumusers']) {
			$cutoff = ($bboptions['cookietimeout'] != "") ? $bboptions['cookietimeout'] * 60 : 900;
			$time = TIMENOW - $cutoff;
			$DB->query("SELECT s.userid, s.username, s.usergroupid, s.invisible, s.location, s.mobile
									FROM ".TABLE_PREFIX."session s
									WHERE s.inforum=".$this->forum['id']."
										AND s.lastactivity > ".$time."
										AND s.badlocation != 1
									ORDER BY s.lastactivity DESC");
			$cached = array();
			$online = array( 'guests' => 0, 'invisible' => 0, 'users' => 0, 'names' => "");
			$rows   = array( 0 => array( 'invisible'   => $bbuserinfo['invisible'],
														 'lastactivity' => TIMENOW,
														 'userid'    => $bbuserinfo['id'],
														 'username'  => $bbuserinfo['name'],
														 'usergroupid' => $bbuserinfo['usergroupid']
										)	);
			while ($r = $DB->fetch_array() ) {
				$rows[] = $r;
			}
			$count = 0;
			$forums->func->check_cache('usergroup');
			foreach( $rows AS $i => $result ) {
				$result['lastactivity'] = $forums->func->get_time( $result['lastactivity'] );
				$result['opentag'] = $forums->cache['usergroup'][ $result['usergroupid'] ]['opentag'];
				$result['closetag'] = $forums->cache['usergroup'][ $result['usergroupid'] ]['closetag'];
				$result['usericon'] = $forums->cache['usergroup'][ $result['usergroupid'] ]['onlineicon'] ? $forums->cache['usergroup'][ $result['usergroupid'] ]['onlineicon'] : 0;
				$result['mobile'] = $result['mobile'] ? 1 : 0;
				if ($result['userid'] == 0) {
					$online['guests']++;
				} else {
					if (empty( $cached[ $result['userid'] ] ) ) {
						$cached[ $result['userid'] ] = 1;
						if ($result['invisible'] == 1) {
							if ( $bbuserinfo['usergroupid'] == 4) {
								$result['show_icon'] = 1;
								$online['username'][] = $result;
							}
							$online['invisible']++;
						} else {
							$online['username'][] = $result;
						}
						$online['users']++;
					}
				}
			}
			$online['total'] = $online['users'] + $online['guests'];
			$online['names'] = preg_replace( "/,\s+$/", "" , $online['names'] );
			$forums->lang['onlineusers'] = sprintf( $forums->lang['onlineusers'], $online['total'], $online['users'], $online['guests'], $online['invisible'] );
		}
		if ($bboptions['isajax'] AND $bbuserinfo['is_mod']) {
			$mxajax_request_type = "POST";
			$jsinclude['ajax'] = 1;
		}

		$moderator = $forums->forum->forums_moderator($this->forum['id']);
		$pagetitle = strip_tags($this->forum['name'])." - ".$bboptions['bbtitle'];
		$nav = $forums->forum->forums_nav( $this->forum['id'] );
		$rsslink = true;
		include $forums->func->load_template('forumdisplay');
		exit;
    }

	function parse_data( $thread )
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$last_time = $this->threadread[$thread['tid']] > $_INPUT['lastvisit'] ? $this->threadread[$thread['tid']] : $_INPUT['lastvisit'];
		$maxposts = $bboptions['maxposts'] ? $bboptions['maxposts'] : '10';
		if ( $thread['attach'] ) {
			$thread['attach_img'] = 1;
		}
		$thread['lastposter'] = $thread['lastposterid'] ? $forums->func->fetch_user_link( $thread['lastposter'], $thread['lastposterid']) : "-".$thread['lastposter']."-";
		$thread['postusername'] = $thread['postuserid'] ? $forums->func->fetch_user_link( $thread['postusername'], $thread['postuserid']) : $thread['postusername']."*";
		$thread['folder_img'] = $forums->func->folder_icon( $thread, $last_time );
		$forums->func->check_cache('icon');
		$thread['cache_icon'] = $forums->cache['icon'][$thread['iconid']]['image'];
		if ($thread['cache_icon'] == '') {
				$thread['cache_icon'] = 'post.gif';
		}
		$thread['thread_icon'] = $thread['iconid']  ? 1 : 0;
		$thread['quintess'] = $thread['quintessence']  ? 1 : 0;
		$thread['showcash'] = sprintf($forums->lang['allcashinfo'],$forums->cache['banksettings']['bankcurrency']);
		if ($thread['pollstate']) {
			$thread['prefix']  = $bboptions['pollprefix'].' ';
			$thread['thread_icon'] = 2;
		}
		if ($thread['sticky']==2) {
			$thread['prefix'] = $bboptions['gstickyprefix'];
			$thread['title'] = $bboptions['stickopentag'].$thread['title'].$bboptions['stickclosetag'];
		} elseif ($thread['sticky']==1) {
			$thread['prefix'] = $bboptions['stickyprefix'];
			$thread['title'] = $bboptions['stickopentag'].$thread['title'].$bboptions['stickclosetag'];
		}
		if ($thread['stopic'] AND $forums->cache['st'][$thread['stopic']]['name']) {
            $thread['specialtopic'] = "[<a href='forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id']."&amp;st={$thread['stopic']}&amp;extra={$this->extra}'>".$forums->cache['st'][$thread['stopic']]['name']."</a>]  ";
        }
		$thread['dateline'] = $forums->func->get_date( $thread['dateline'], 3 );
		$thread['showpages']  = $forums->func->build_threadpages(
												array( 'id'  => $thread['tid'],
														'totalpost'  => $thread['post'],
														'perpage'    => $maxposts,
														'extra'    => $this->extra,
													  )
										   );
		$thread['post']  = $forums->func->fetch_number_format( intval($thread['post']) );
		$thread['views']	 = $forums->func->fetch_number_format( intval($thread['views']) );
		if ($last_time  && ($thread['lastpost'] > $last_time)) {
			$this->newpost++;
			$thread['gotonewpost']  = 1;
		} else {
			$thread['gotonewpost']  = 0;
		}
		$thread['lastpost']  = $forums->func->get_date( $thread['lastpost'], 1 );
		 if (isset($thread['preview']) AND $bboptions['threadpreview'] > 0) {
			$thread['info'] =  $forums->lang['postusername'].': '.$thread['postusername']."\n";
			$thread['info'] .=  $forums->lang['dateline'].': '.$thread['dateline']."\n";
			$thread['info'] .=  $forums->lang['lastpost'].': '.$thread['lastpost']."\n";
			$thread['info'] .=  $forums->lang['post'].': '.$thread['post'].' | '.$forums->lang['views'].': '.$thread['views']."\n";
			$text = $bboptions['threadpreview'] == 1 ? $forums->lang['threadinfo'].': ' : $forums->lang['replyinfo'].': ';
			$thread['preview'] = $thread['hidepost'] ? $forums->lang['_posthidden'] : strip_tags($thread['info'].$text.$thread['preview']);
			$thread['preview'] = $forums->func->htmlspecialchars_uni($forums->func->fetch_trimmed_title($thread['preview'], 200));
		}
		if ($thread['open'] == 2) {
			$t_array = explode("&", $thread['moved']);
			$thread['tid'] = $t_array[0];
			$thread['forumid'] = $t_array[1];
			$thread['title'] = $thread['title'];
			$thread['views'] = '--';
			$thread['post'] = '--';
			$thread['prefix'] = $bboptions['movedprefix']." ";
			$thread['gotonewpost'] = "";
		}
		return $thread;
	}
}

$output = new forum();
$output->show();
?>