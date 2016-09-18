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
class modfunctions
{
	var $error = '';
	var $thread = array();
	var $moderator = array();
	var $type = array();

	function init($forum="", $thread="", $moderator="")
	{
		$this->forum = $forum;
		if ( is_array($thread) ) {
			$this->thread = $thread;
		}
		if ( is_array($moderator) ) {
			$this->moderator = $moderator;
		}
		return TRUE;
	}

	function post_delete($id)
	{
		global $forums, $DB, $bboptions;
		$post = array();
		$attach_tid = array();
		$thread = array();
		if ( is_array( $id ) ) {
			if ( count($id) > 0 ) {
				$ids = implode(",",$id);
				$pid = " IN ($ids)";
			} else {
				return FALSE;
			}
		} else {
			$id = intval($id);
			if ($id) {
				$ids = $id;
				$pid = " = $id";
			} else {
				return FALSE;
			}
		}
		$forums->func->check_cache('stats');
		$forums->func->check_cache('forum');
		$forums->func->check_cache('banksettings');
		$banksettings = $forums->cache['banksettings'];
		$posts = $DB->query("SELECT p.pid, p.threadid, p.userid, p.dateline, t.forumid FROM ".TABLE_PREFIX."post p LEFT JOIN ".TABLE_PREFIX."thread t ON p.threadid = t.tid WHERE p.pid".$pid."" );
		while ( $r = $DB->fetch_array($posts) ) {
			$postscount = ($forums->cache['forum'][$r['forumid']]['countposts']) ? ', posts = posts - 1' : '';
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET cash=cash - '".$banksettings['reducepmoney']."' $postscount WHERE id=".$r['userid']."");
			$post[$r['pid']] = $r['threadid'];
			$thread[$r['threadid']] = 1;
			$today = getdate();
			$posttime = $forums->func->get_time($r['dateline'], 'd');
			if ($today['mday'] == $posttime) {
				$forums->cache['stats']['todaypost']--;
			}
		}
		$attachments = $DB->query("SELECT * FROM ".TABLE_PREFIX."attachment WHERE postid".$pid."");
		$attachmentids = array();
		while ($attachment = $DB->fetch_array($attachments)) {
			if ($attachment['location']) {
				@unlink($bboptions['uploadfolder']."/".$attachment['attachpath']."/".$attachment['location']);
			}
			if ($attachment['thumblocation']) {
				@unlink($bboptions['uploadfolder']."/".$attachment['attachpath']."/".$attachment['thumblocation']);
			}
			$attachmentids[] = $attachment['attachmentid'];
			$attach_tid[$post[$attachment['postid']]] = $post[ $attachment['postid'] ];
		}
		if (count($attachmentids)) {
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."attachment WHERE attachmentid IN(".implode(",",$attachmentids).")");
			require_once(ROOT_PATH."includes/functions_post.php");
			$postlib = new functions_post(0);
			foreach($attach_tid as $apid => $tid) {
				$postlib->recount_attachment($tid);
			}
		}
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."post WHERE pid".$pid."");
		$forums->cache['stats']['totalposts'] -= count($post);
		$forums->func->update_cache(array('name' => 'stats'));
		foreach(array_keys($thread) as $tid) {
			$this->rebuild_thread($tid);
		}
		$this->add_moderate_log('', '', $pid, '', $forums->lang['deletepost']." ($ids)");
	}

	function rebuild_thread($tid, $doforum = 1)
	{
		global $forums, $DB;
		$tid = intval($tid);
		$post = $DB->query_first('SELECT COUNT(*) as posts FROM '.TABLE_PREFIX."post WHERE threadid = $tid AND moderate != 1");
		$postcount = intval($post['posts']) - 1;
		$modpost = $DB->query_first('SELECT COUNT(*) as posts FROM '.TABLE_PREFIX."post WHERE threadid = $tid AND moderate = 1");
		$modpostcount = intval($modpost['posts']);
		$lastpost = $DB->query_first('SELECT p.dateline, p.threadid, p.userid, p.username, p.pid, t.forumid
			FROM ' . TABLE_PREFIX . 'post p
				LEFT JOIN ' . TABLE_PREFIX . "thread t
					ON p.threadid = t.tid
			WHERE threadid = $tid
				AND moderate != 1
			ORDER BY pid DESC
			LIMIT 0,1");
		$first_post = $DB->query_first('SELECT dateline, userid, username, pid
			FROM ' . TABLE_PREFIX . "post
			WHERE threadid = $tid
			ORDER BY pid ASC
			LIMIT 0, 1");
		$attach = $DB->query_first('SELECT COUNT(*) as count
			FROM ' . TABLE_PREFIX . 'attachment a
				LEFT JOIN ' . TABLE_PREFIX . "post p
					ON a.postid = p.pid
			WHERE p.threadid = $tid");
		if ($postcount==-1) {
			$this->thread_delete($tid);
		} else {
			$dbs = array(
				'lastpost' => $lastpost['dateline'],
				'lastposterid' => $lastpost['userid'],
				'lastposter' => $lastpost['username'],
				'modposts' => $modpostcount,
				'post' => $postcount,
				'postuserid' => $first_post['userid'],
				'postusername' => $first_post['username'],
				'dateline' => $first_post['dateline'],
				'firstpostid' => $first_post['pid'],
				'lastpostid' => $lastpost['pid'],
				'attach' => $attach['count'],
			);
			$forums->func->fetch_query_sql($dbs, 'thread', "tid = $tid");
			unset($dbs);
		}
		if ( $first_post['newthread'] != 1 AND $first_post['pid'] ) {
			$DB->shutdown_query('UPDATE ' . TABLE_PREFIX . "post SET newthread = 1 WHERE pid = {$first_post['pid']}");
		}
		if ($forums->forum->foruminfo[$lastpost['forumid']]['lastthreadid'] == $tid AND $doforum == 1) {
			$thread = $DB->query_first("SELECT title, tid, lastpost, lastposterid, lastposter FROM ".TABLE_PREFIX."thread WHERE forumid=".$lastpost['forumid']." AND visible=1 ORDER BY lastpost DESC LIMIT 0, 1");
			$dbs = array (
				'lastthread' => $thread['title'] ? strip_tags($thread['title']) : '',
				'lastthreadid' => $thread['tid'] ? $thread['tid'] : '',
				'lastpost' => $thread['lastpost'] ? $thread['lastpost'] : '',
				'lastposter' => $thread['lastposter'] ? $thread['lastposter'] : '',
				'lastposterid' => $thread['lastposterid'] ? $thread['lastposterid'] : '',
			);
			$forums->func->fetch_query_sql($dbs, 'forum', "id=".intval($this->forum['id']));
			$forums->func->check_cache('forum_stats', 'forum');
			foreach ($dbs as $k => $v) {
				$forums->cache['forum_stats'][ $this->forum['id'] ][ $k ] = $v;
			}
			$forums->func->update_cache(array('name' => 'forum_stats'));
		}
	}

	function thread_close($id)
	{
		$this->type[] = array( 'open' => 0 );
		$this->do_run($id);
	}

	function thread_open($id)
	{
		$this->type[] = array( 'open' => 1 );
		$this->do_run($id);
	}

	function thread_stick($id)
	{
		$this->type[] = array( 'sticky' => 1 );
		$this->do_run($id);
	}

	function thread_unstick($id)
	{
		$this->type[] = array( 'sticky' => 0 );
		$this->do_run($id);
	}

	function thread_delete($id, $nostats=0, $reducecash=1)
	{
		global $forums, $DB, $bboptions;
		$post  = array();
		$attach = array();
		$this->error = "";
		if ( is_array( $id ) ) {
			if ( count($id) > 0 ) {
				$tid = " IN(".implode(",",$id).")";
			} else {
				return FALSE;
			}
		} else {
			if ( intval($id) ) {
				$tid   = "=$id";
			} else {
				return FALSE;
			}
		}
		$forums->func->check_cache('forum');
		$forums->func->check_cache('stats');
		$forums->func->check_cache('banksettings');
		$banksettings = $forums->cache['banksettings'];
		$posts = $DB->query( "SELECT p.pid, p.userid, p.newthread, p.dateline, t.forumid FROM ".TABLE_PREFIX."post p LEFT JOIN ".TABLE_PREFIX."thread t ON p.threadid = t.tid WHERE p.threadid".$tid."" );
		while ( $r = $DB->fetch_array($posts) ) {
			$postscount = ($forums->cache['forum'][$r['forumid']]['countposts']) ? ', posts = posts - 1' : '';
			if ($reducecash == 1) {
				if ($r['newthread']) {
					$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET cash=cash - '".$banksettings['reducetmoney']."' $postscount WHERE id=".$r['userid']."" );
				} else {
					$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET cash=cash - '".$banksettings['reducepmoney']."' $postscount WHERE id=".$r['userid']."" );
				}
			} else if ($postscount) {
				$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET posts = posts - 1 WHERE id=".$r['userid']."" );
			}
			$today = getdate();
			$posttime = $forums->func->get_time($r['dateline'], 'd');
			if ($today['mday'] == $posttime) {
				$forums->cache['stats']['todaypost']--;
			}
			$post[] = $r['pid'];
		}
		$forums->func->update_cache(array('name' => 'stats'));
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."poll WHERE tid".$tid."" );
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."thread WHERE tid".$tid."" );
		if ( count( $post ) ) {
			$attachments = $DB->query( "SELECT * FROM ".TABLE_PREFIX."attachment WHERE postid IN (".implode(",",$post).")" );
			while ( $attachment = $DB->fetch_array($attachments) ) {
				if ( $attachment['location'] ) {
					@unlink( $bboptions['uploadfolder']."/".$attachment['attachpath']."/".$attachment['location'] );
				}
				if ( $attachment['thumblocation'] ) {
					@unlink( $bboptions['uploadfolder']."/".$attachment['attachpath']."/".$attachment['thumblocation'] );
				}
				$attach[] = $attachment['attachmentid'];
			}
			if ( count( $attach ) ) {
				$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."attachment WHERE attachmentid IN (".implode(",",$attach).")" );
			}
		}
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."post WHERE threadid".$tid."" );
		if ( $nostats == 0 ) {
			if ( $this->forum['id'] ) {
				$this->forum_recount( $this->forum['id'] );
			}
			$this->stats_recount();
		}
	}

	function thread_move($thread, $source_id, $dest_id, $leavelink=0)
	{
		global $forums, $DB;
		$this->error = "";
		$source_id = intval($source_id);
		$dest_id = intval($dest_id);
		if ( is_array( $thread ) ) {
			if ( count($thread) > 0 )
			{
				$tid = " IN(".implode(",",$thread).")";
			} else {
				return FALSE;
			}
		} else {
			if ( intval($thread) ) {
				$tid   = "=$thread";
			} else {
				return FALSE;
			}
		}
		if ($source_id) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET forumid=".$dest_id." WHERE forumid=".$source_id." AND tid".$tid."" );
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."poll SET forumid=".$dest_id." WHERE forumid=".$source_id." AND tid".$tid."" );
		} else {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET forumid=".$dest_id." WHERE tid".$tid."" );
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."poll SET forumid=".$dest_id." WHERE tid".$tid."" );
		}
		if ( $leavelink != 0 ) {
			$newthread = $DB->query( "SELECT * FROM ".TABLE_PREFIX."thread WHERE tid".$tid."" );
			while ( $row = $DB->fetch_array($newthread) ) {
				$DB->query_unbuffered( "INSERT INTO ".TABLE_PREFIX."thread
										(title, description, open, post, views, postuserid, dateline, postusername, lastpost, forumid, visible, sticky, moved, lastposterid, lastposter)
									VALUES
										('".$DB->escape_string($row['title'])."', '".$DB->escape_string($row['description'])."', 2, 0, 0, ".$row['postuserid'].", ".$row['dateline'].", '".$DB->escape_string($row['postusername'])."', '".$DB->escape_string($row['lastpost'])."', ".$source_id.", 1, 0, '".$row['tid'].'&'.$dest_id."', ".$row['lastposterid'].", '".$DB->escape_string($row['lastposter'])."')"
								);
			}
		}
		$subscribethreads = $DB->query("SELECT s.*, u.id, t.tid, t.forumid, g.usergroupid
										 FROM ".TABLE_PREFIX."subscribethread s
										 LEFT JOIN ".TABLE_PREFIX."thread t ON (s.threadid=t.tid)
										 LEFT JOIN ".TABLE_PREFIX."user u on (u.id=s.userid)
										 LEFT JOIN ".TABLE_PREFIX."usergroup g on (g.usergroupid=u.usergroupid)
										WHERE s.threadid".$tid."");
		$threadid = array();
		while ( $r = $DB->fetch_array($subscribethreads) ) {
			$pass = FALSE;
			$forum_perm_array = explode( ",", $forums->forum->foruminfo[ $r['forumid'] ]['canread'] );
			if ( in_array( $r['usergroupid'], $forum_perm_array ) ) {
				$pass = TRUE;
			}
			if (!$pass) {
				$threadid[] = $r['subscribethreadid'];
			}
		}
		if ( count($threadid) > 0 ) {
			$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."subscribethread WHERE subscribethreadid IN (".implode(',', $threadid ).")" );
		}
		return TRUE;
	}

	function thread_st($thread, $st_id)
	{
		global $forums, $DB;
		$this->error = "";
		$st_id = intval($st_id);
		if ( is_array( $thread ) ) {
			if ( count($thread) > 0 ) {
				$tid = " IN(".implode(",",$thread).")";
			} else {
				return FALSE;
			}
		} else {
			if ( intval($thread) ) {
				$tid   = "=$thread";
			} else {
				return FALSE;
			}
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET stopic=".$st_id." WHERE tid".$tid);
		return TRUE;
	}

	function stats_recount()
	{
		global $forums, $DB;
		$forums->func->check_cache('stats');
		$thread = $DB->query_first( "SELECT COUNT(*) as tcount, SUM(post) as tposts FROM ".TABLE_PREFIX."thread WHERE visible=1" );
		$forums->cache['stats']['totalthreads']  = $thread['tcount'];
		$forums->cache['stats']['totalposts'] = $thread['tposts']+$thread['tcount'];
		$forums->func->update_cache(array('name' => 'stats'));
	}

	function forum_recount($fid="")
	{
		global $forums, $DB;
		$fid = intval($fid);
		if (!$fid) {
			if ( $this->forum['id'] ) {
				$fid = $this->forum['id'];
			} else {
				return FALSE;
			}
		}
		$thread = $DB->query_first( "SELECT COUNT(*) AS count FROM ".TABLE_PREFIX."thread WHERE visible=1 AND forumid=".$fid."" );
		$moderatethread = $DB->query_first( "SELECT COUNT(*) AS count FROM ".TABLE_PREFIX."thread WHERE visible=0 AND forumid=".$fid."" );
		$post = $DB->query_first( "SELECT SUM(post) AS replies FROM ".TABLE_PREFIX."thread WHERE visible=1 AND forumid=".$fid."" );
		$moderatepost = $DB->query_first( "SELECT SUM(modposts) AS replies FROM ".TABLE_PREFIX."thread WHERE forumid=".$fid."" );
		$lastpost = $DB->query_first( "SELECT tid, title, lastposterid, lastposter, lastpost FROM ".TABLE_PREFIX."thread WHERE forumid='".$fid."' AND visible=1 ORDER BY lastpost DESC LIMIT 0, 1" );
		$today = date('Y-n-j', TIMENOW);
		$day = explode('-', $today);
		$day_start = mktime(0, 0, 0, $day[1], $day[2], $day[0]);
		$todaypost = $DB->query_first( "SELECT COUNT(*) AS count FROM ".TABLE_PREFIX."thread t LEFT JOIN ".TABLE_PREFIX."post p ON (p.threadid=t.tid) WHERE t.visible=1 AND t.forumid=".$fid." AND p.dateline>=".$day_start."" );
		$dbs = array(
			'lastposterid' => intval($lastpost['lastposterid']),
			'lastposter' => $lastpost['lastposter'],
			'lastpost' => intval($lastpost['lastpost']),
			'lastthread' => strip_tags($lastpost['title']),
			'lastthreadid' => intval($lastpost['tid']),
			'thread' => intval($thread['count']),
			'post' => intval($post['replies']) + intval($thread['count']),
			'unmodthreads' => intval($moderatethread['count']),
			'unmodposts' => intval($moderatepost['replies']),
			'todaypost' => intval($todaypost['count']),
		);
		$forums->func->fetch_query_sql($dbs, 'forum', "id=".$fid);
		$forums->func->check_cache('forum_stats', 'forum');
		foreach($dbs as $k => $v) {
			$forums->cache['forum_stats'][$fid][$k] = $v;
		}
		$forums->func->update_cache(array('name' => 'forum_stats'));
		return TRUE;
	}

	function do_run($id)
	{
		global $forums, $DB;
		if ( count($this->type) < 1 ) {
			return FALSE;
		}
		$final_array = array();
		foreach( $this->type AS $idx => $real_array ) {
			foreach( $real_array AS $k => $v ) {
				$final_array[ $k ] = $v;
			}
		}
		if ( is_array($id) ) {
			if ( count($id) > 0 ) {
				$forums->func->fetch_query_sql( $db_string, 'thread', "tid IN(".implode( ",", $id ).")" );
				return TRUE;
			} else {
				return FALSE;
			}
		} else if ( intval($id) != "" ) {
			$forums->func->fetch_query_sql( $db_string, 'thread', "tid=".intval($id) );
		} else {
			return FALSE;
		}
	}

	function add_moderate_log($fid, $tid, $pid, $title, $action='Unknown')
	{
		global $DB, $bbuserinfo, $forums;
		$log = array(	'forumid' => intval($fid),
							'threadid' => intval($tid),
							'postid' => intval($pid),
							'userid' => $bbuserinfo['id'],
							'username' => $bbuserinfo['name'],
							'host' => SESSION_HOST,
							'referer' => REFERRER,
							'dateline' => TIMENOW,
							'title' => $title,
							'action' => $action,
						);
		$forums->func->fetch_query_sql($log, 'moderatorlog');
	}
}

?>