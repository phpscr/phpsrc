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
define('THIS_SCRIPT', 'search');

require_once('./global.php');

class search
{
    var $page = 0;
	var $threadread = array();
	var $highlight = '';

	var $showposts = 0;
	var $searchin = 'post';
	var $sortby = 'lastpost';
	var $order = 'desc';
	var $prune = 0;
	var $usertitle = array();
	var $cached_query = 0;
	var $cached_matches = 0;
    var $search_type = 'post';

    function show()
    {
    	global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'search' );
		if ($_INPUT['do'] != 'getnew' AND $_INPUT['do'] != 'show' AND $_INPUT['do'] != 'finduser' AND $_INPUT['do'] != 'finduserthread' AND $_INPUT['do'] != 'getquintessence') {
			if (! $bboptions['enablesearches']) {
				$forums->func->standard_error("cannotsearch");
			}
			if ($bbuserinfo['cansearch'] != 1) {
				$forums->func->standard_error("cannotviewthispage");
			}
		}
		$forums->forum->forums_init();
		$this->highlight = $this->convert_highlight($_INPUT['highlight']);
		if ( $threadread = $forums->func->get_cookie('threadread') ) {
			$this->threadread = unserialize($threadread);
		}
		$this->page = intval($_INPUT['pp']);
		require_once(ROOT_PATH."includes/functions_credit.php");
		$this->credit = new functions_credit();
    	switch($_INPUT['do']) {
    		case 'search':
    			$this->do_search();
    			break;
    		case 'getnew':
    			$this->get_new_post();
    			break;
			case 'getquintessence':
				$this->get_new_quintessence();
			    break;
    		case 'show':
    			$this->show_results();
    			break;
    		case 'finduser':
    			$this->find_user_post();
    			break;
    		case 'searchthread':
    			$this->search_thread();
    			break;
    		case 'finduserthread':
    			$this->find_user_thread();
    			break;
			case 'findmod':
    			$this->find_mod_post();
    			break;
    		default:
    			$this->showform();
    			break;
    	}
 	}

	function flood_contol()
	{
		global $DB, $bbuserinfo, $forums;
		if ($bbuserinfo['searchflood'] > 0) {
			$flood_time = TIMENOW - $bbuserinfo['searchflood'];
			if ($bbuserinfo['id']) {
				$where = "userid=".$bbuserinfo['id'];
			} else {
				$where = "host=".$DB->escape_string(SESSION_HOST);
			}
			if ( $DB->query_first("SELECT searchid FROM ".TABLE_PREFIX."search WHERE $where AND dateline > '".$flood_time."'") ) {
				$forums->func->standard_error("searchflood", 0, $bbuserinfo['searchflood']);
			}
		}
	}

 	function showform()
 	{
 		global $forums, $_INPUT, $bboptions, $bbuserinfo;
		$showforum = $forums->forum->forum_jump(1, 1);
		if ( ! $_INPUT['f'] ) {
			$selected = ' selected="selected"';
		}
		$credit_list = $this->credit->show_credit('search');
		$pagetitle = $forums->lang['search']." - ".$bboptions['bbtitle'];
		$nav = array( $forums->lang['search'] );
		include $forums->func->load_template( 'search' );
		exit;
 	}

	function search_thread()
	{
    	global $_INPUT;
    	$this->threadid = intval($_INPUT['thread']);
    	$this->showposts = 1;
    	$_INPUT['searchin'] = 'post';
    	$this->do_search();
    }

 	function find_user_thread()
 	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$this->flood_contol();
		$forumlist = $this->get_forums();
		$userid = intval($_INPUT['u']);
		if (!$userid) {
			$forums->func->standard_error("cannotsearchuser");
		}
		$results = $DB->query_first("SELECT count(*) as count FROM ".TABLE_PREFIX."thread WHERE visible=1 AND forumid IN($forumlist) AND postuserid=$userid");
		if ( !$results['count']  ) {
			$forums->func->standard_error("nosearchresult");
		}
		$query_to_cache = "SELECT *, title as threadtitle
											FROM ".TABLE_PREFIX."thread
											WHERE visible=1 AND forumid IN(".$forumlist.") AND postuserid='".$userid."'
											ORDER BY lastpost DESC";
		$DB->query( $query_to_cache );
		$uniqueid = md5(uniqid(microtime()));
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."search
								(searchid, dateline, maxpost, sortby, sortorder, userid, host, query)
							  VALUES
								('".$uniqueid."', '".TIMENOW."', '".$results['count']."', '".$this->sortby."', '".$this->order."', '".$bbuserinfo['id']."', '".$DB->escape_string(SESSION_HOST)."', '".addslashes($query_to_cache)."')"
							);
		$forums->func->standard_redirect( "search.php?{$forums->sessionurl}do=show&amp;searchid=$uniqueid&amp;searchin=thread" );
	}

 	function find_user_post()
 	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$this->flood_contol();
		$forumlist = $this->get_forums();
		$userid = intval($_INPUT['u']);
		if ($userid == "") {
			$forums->func->standard_error("cannotsearchuser");
		}
		$results =  $DB->query_first("SELECT count(*) as count
			FROM ".TABLE_PREFIX."post p
			LEFT JOIN ".TABLE_PREFIX."thread t ON (p.threadid=t.tid)
			WHERE p.moderate != 1 AND t.forumid IN(".$forumlist.") AND p.userid=".$userid."");
		if ( !$results['count']) {
			$forums->func->standard_error("nosearchresult");
		}
		$anonymous = ($bbuserinfo['usergroupid'] == 4 AND $userid==$bbuserinfo['id']) ? '' : " AND p.anonymous != 1";
		$query_to_cache = "SELECT p.*, t.*, t.post as thread_post, t.title as threadtitle, u.*
			FROM ".TABLE_PREFIX."post p
			LEFT JOIN ".TABLE_PREFIX."thread t ON (p.threadid=t.tid)
			LEFT JOIN ".TABLE_PREFIX."user u ON (u.id=p.userid)
			WHERE p.moderate != 1 AND t.forumid IN(".$forumlist.") AND p.userid='".$userid."'$anonymous
			ORDER BY p.dateline DESC";
		$DB->query( $query_to_cache );
		$uniqueid = md5(uniqid(microtime()));
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."search
								(searchid, dateline, maxpost, sortby, sortorder, userid, host, query)
							  VALUES
								('".$uniqueid."', '".TIMENOW."', '".$results['count']."', '".$this->sortby."', '".$this->order."', '".$bbuserinfo['id']."', '".$DB->escape_string(SESSION_HOST)."', '".addslashes($query_to_cache)."')"
							);
		$forums->func->standard_redirect( "search.php?{$forums->sessionurl}do=show&amp;searchid=$uniqueid&amp;searchin=post&amp;showposts=1" );
	}

	function find_mod_post()
 	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$this->flood_contol();
		$forumlist = $this->get_forums();
		if ($forumlist == "" OR !$bbuserinfo['is_mod']) {
			$forums->func->standard_error("selectsearchforum");
		}
		$results =  $DB->query_first("SELECT count(*) as count
													FROM ".TABLE_PREFIX."post p
													LEFT JOIN ".TABLE_PREFIX."thread t ON (p.threadid=t.tid)
													WHERE p.moderate = 1 AND t.forumid IN(".$forumlist.")"
												);
		if ( ! $results['count'] ) {
			$forums->func->standard_error("nosearchuserresult");
		}
		$query_to_cache = "SELECT p.*, t.*, t.post as thread_post, t.title as threadtitle, u.*
											FROM ".TABLE_PREFIX."post p
											LEFT JOIN ".TABLE_PREFIX."thread t ON (p.threadid=t.tid)
											LEFT JOIN ".TABLE_PREFIX."user u ON (u.id=p.userid)
											WHERE p.moderate = 1 AND t.forumid IN(".$forumlist.")
											ORDER BY p.dateline DESC";
		$DB->query( $query_to_cache );
		$uniqueid = md5(uniqid(microtime()));
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."search
								(searchid, dateline, maxpost, sortby, sortorder, userid, host, query)
							  VALUES
								('".$uniqueid."', '".TIMENOW."', '".$results['count']."', '".$this->sortby."', '".$this->order."', '".$bbuserinfo['id']."', '".$DB->escape_string(SESSION_HOST)."', '".addslashes($query_to_cache)."')"
							);
		$forums->func->standard_redirect( "search.php?{$forums->sessionurl}do=show&amp;searchid=$uniqueid&amp;searchin=post&amp;showposts=1" );
	}

 	function get_new_post()
 	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		if ( !$bbuserinfo['id'] ) {
			$forums->func->standard_error("notlogin");
		}
		$this->flood_contol();
		$last_time = $bbuserinfo['lastvisit'];
		if ( $_INPUT['active'] ) {
			if ( $_INPUT['lastdate'] ) {
				$last_time = TIMENOW - intval($_INPUT['lastdate']);
			} else {
				$last_time = TIMENOW - 86400;
			}
		}
		$forumlist = $this->get_forums();
		if ($forumlist == "") {
			$forums->func->standard_error("selectsearchforum");
		}
		$results = $DB->query_first("SELECT count(*) as count
													FROM ".TABLE_PREFIX."thread
													WHERE visible=1 AND forumid IN(".$forumlist.") AND lastpost > '".$last_time."'"
												);
		if ( !$results['count'] )
		{
			$forums->func->standard_error("nonewpost");
		}
		$query_to_cache = "SELECT *, title as threadtitle
											FROM ".TABLE_PREFIX."thread
											WHERE visible=1 AND forumid IN(".$forumlist.") AND lastpost > '".$last_time."'
											ORDER BY lastpost DESC";
		$DB->query( $query_to_cache );
		$uniqueid = md5(uniqid(microtime()));
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."search
								(searchid, dateline, maxpost, sortby, sortorder, userid, host, query)
							  VALUES
								('".$uniqueid."', '".TIMENOW."', '".$results['count']."', '".$this->sortby."', '".$this->order."', '".$bbuserinfo['id']."', '".$DB->escape_string(SESSION_HOST)."', '".addslashes($query_to_cache)."')"
							);
		$forums->func->standard_redirect( "search.php?{$forums->sessionurl}do=show&amp;searchid=$uniqueid&amp;searchin=thread&amp;lastdate={$_INPUT['lastdate']}" );
	}

	function get_new_quintessence()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		if ( !$bbuserinfo['id'] ) {
			$forums->func->standard_error("notlogin");
		}
		$this->flood_contol();
		$last_time = $bbuserinfo['lastvisit'];
		$forumlist = $this->get_forums();
		if ($forumlist == "") {
			$forums->func->standard_error("selectsearchforum");
		}
		$results = $DB->query_first("SELECT count(*) as count
													FROM ".TABLE_PREFIX."thread
													WHERE quintessence=1 AND forumid IN(".$forumlist.")"
												);
		if ( !$results['count'] )
		{
			$forums->func->standard_error("noquintessence");
		}
		$query_to_cache = "SELECT *, title as threadtitle
											FROM ".TABLE_PREFIX."thread
											WHERE quintessence=1 AND forumid IN(".$forumlist.")
											ORDER BY lastpost DESC";
		$DB->query( $query_to_cache );
		$uniqueid = md5(uniqid(microtime()));
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."search
								(searchid, dateline, maxpost, sortby, sortorder, userid, host, query)
							  VALUES
								('".$uniqueid."', '".TIMENOW."', '".$results['count']."', '".$this->sortby."', '".$this->order."', '".$bbuserinfo['id']."', '".$DB->escape_string(SESSION_HOST)."', '".addslashes($query_to_cache)."')"
							);
		$forums->func->standard_redirect( "search.php?{$forums->sessionurl}do=show&amp;searchid=$uniqueid&amp;searchin=thread" );
	}

	function do_search()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$this->credit->check_credit_limit('search');
		if ($_INPUT['namesearch'] != "") {
			$name_filter = $this->filter_keywords($_INPUT['namesearch'], 1);
		}
		$keywords = $this->filter_keywords($_INPUT['keywords']);
		if ( $name_filter == "" AND $_INPUT['keywords'] != "" ) {
			$type= 'postonly';
		} else if ( $name_filter != "" AND $_INPUT['keywords'] == "" ) {
			$type='nameonly';
		}
		$checkwords = str_replace( "%", "",  trim($keywords) );
		if ( !$checkwords OR $checkwords == "" OR !isset($checkwords) ) {
			if ($type != 'nameonly') {
				$forums->func->standard_error("requirekeyword");
			}
		}
		$this->searchin = $_INPUT['searchin'];
		$forumlist = $this->get_forums();
		if ($forumlist == "") {
			$forums->func->standard_error("selectsearchforum");
		}
		foreach( array( 'lastpost', 'post', 'postusername', 'forumid' ) AS $v ) {
			if ($_INPUT['sortby'] == $v) {
				$this->sortby = $v;
			}
		}
		foreach ( array( 1, 7, 30, 60, 90, 180, 365, 0 ) AS $v ) {
			if ($_INPUT['prune'] == $v) {
				$this->prune = $v;
			}
		}
		if ($_INPUT['order'] == 'asc') {
			$this->order = 'asc';
		}
		if ($_INPUT['showposts']) {
			$this->showposts = 1;
		}
		$bboptions['minsearchlength'] = $bboptions['minsearchlength'] ? $bboptions['minsearchlength'] : 4;
		if ($this->prune > 0) {
			$gt_lt = $_INPUT['prune_type'] == 'older' ? "<" : ">";
			$time = time() - ($_INPUT['prune'] * 86400);
			$threads_datecut = "t.lastpost $gt_lt $time AND";
			$posts_datecut  = "p.dateline $gt_lt $time AND";
		}
		 $name_filter = trim( $name_filter );
		 $user_string = "";
		 if ( $name_filter != "" ) {
			$name_filter = str_replace( '|', "&#124;", $name_filter );
			if ($_INPUT['exactmatch'] == 1) {
				$sql_query = "SELECT id from ".TABLE_PREFIX."user WHERE LOWER(name)='".$forums->func->strtolower($name_filter)."' OR name='".$name_filter."'";
			} else {
				$sql_query = "SELECT id from ".TABLE_PREFIX."user WHERE LOWER(name) LIKE concat('%','".$forums->func->strtolower($name_filter)."','%') OR name LIKE concat('%','".$name_filter."','%')";
			}
			$DB->query( $sql_query );
			while ($row = $DB->fetch_array()) {
				$user_string .= "'".$row['id']."',";
			}
			$user_string = preg_replace( "/,$/", "", $user_string );
			if ($user_string == "") {
				$forums->func->standard_error("nosearchuserresult");
			}
			$posts_name  = " AND p.userid IN ($user_string)";
			$threads_name = " AND t.postuserid IN ($user_string)";
		}
		if ( $type != 'nameonly' ) {
			if (preg_match( "/ and|or|&|\| /", $keywords) ) {
				preg_match_all( "/(^|and|or|&|\|)\s{1,}(\S+?)\s{1,}/", $keywords, $matches );
				$title_like = "(";
				$post_like  = "(";
				for ($i = 0, $n = count($matches[0]); $i < $n; $i++) {
					$boolean = $matches[1][$i];
					$word    = trim($matches[2][$i]);
					if ($forums->func->strlen($word) < $bboptions['minsearchlength']) {
						$forums->func->standard_error("keywordtooshort", 0, $bboptions['minsearchlength']);
					}
					if ($boolean) {
						if ($boolean=="&") $boolean = "AND";
						if ($boolean=="|") $boolean = "OR";
						$boolean = " $boolean";
					}
					$title_like .= "$boolean LOWER(t.title) LIKE '%$word%' ";
					$post_like  .= "$boolean LOWER(p.pagetext) LIKE '%$word%' ";
				}
				$title_like .= ")";
				$post_like  .= ")";
			} else {
				$keywords = str_replace( '|', "&#124;", $keywords );
				if ($forums->func->strlen(trim($keywords)) < $bboptions['minsearchlength']) {
					$forums->func->standard_error("keywordtooshort", 0 , $bboptions['minsearchlength']);
				}
				$title_like = " LOWER(t.title) LIKE '%".trim($keywords)."%' ";
				$post_like  = " LOWER(p.pagetext) LIKE '%".trim($keywords)."%' ";
			}
		}
		$uniqueid = md5(uniqid(microtime()));
		if($this->threadid != '') {
			$search_tid = " AND t.tid = $this->threadid";
			$search_pid = " AND p.threadid = $this->threadid";
		}
		if ($this->searchin == 'quintessence') {
			$quintessence = " AND t.quintessence = 1";
		}
		if ($this->searchin == 'reputation') {
			$rept = " AND t.allrep != 0";
			$repp = " AND p.reppost != ''";
			$this->searchin = 'post';
		}
		if ($type != 'nameonly') {
			if ($this->searchin == 'post' AND $bbuserinfo['cansearchpost']) {
				$posts_query = "SELECT p.pid
								FROM ".TABLE_PREFIX."post p
								 LEFT JOIN ".TABLE_PREFIX."thread t on (t.tid=p.threadid)
								WHERE $posts_datecut  t.forumid IN ($forumlist) $search_pid $repp
								 AND p.moderate != 1
								 $posts_name AND ($post_like)";
			}
			$threads_query = "SELECT t.tid
							FROM ".TABLE_PREFIX."thread t
							WHERE $threads_datecut t.forumid IN ($forumlist)
							$threads_name $quintessence $rept $search_tid AND t.visible=1 AND ($title_like)";
		} else {
			if ($this->searchin == 'post' AND $bbuserinfo['cansearchpost']) {
				$posts_query = "SELECT p.pid
								FROM ".TABLE_PREFIX."post p
								LEFT JOIN ".TABLE_PREFIX."thread t on (t.tid=p.threadid)
								WHERE $posts_datecut  t.forumid IN ($forumlist) $repp
								AND p.moderate != 1
								 $posts_name";
			}
			$threads_query = "SELECT t.tid
							FROM ".TABLE_PREFIX."thread t
							WHERE $threads_datecut t.forumid IN ($forumlist)
							$threads_name $quintessence $rept";
		}
		$threads = "";
		$posts  = "";
		$DB->query($threads_query);
		$maxthread = $DB->num_rows();
		while ($row = $DB->fetch_array() ) {
			$threads .= $row['tid'].",";
		}
		$DB->free_result();
		if ($this->searchin == 'post' AND $bbuserinfo['cansearchpost']) {
			$DB->query($posts_query);
			$maxpost = $DB->num_rows();
			while ($row = $DB->fetch_array() ) {
				$posts .= $row['pid'].",";
			}
			$DB->free_result();
		}
		$threads = preg_replace( "/,$/", "", $threads );
		$posts  = preg_replace( "/,$/", "", $posts );
		if ($threads == "" AND $posts == "") {
			$forums->func->standard_error("nosearchuserresult");
		}
		$uniqueid = md5(uniqid(microtime()));
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."search
								(searchid, dateline, threadid, maxthread, sortby, sortorder, userid, host, postid, maxpost)
							  VALUES
								('".$uniqueid."', '".TIMENOW."', '".$threads."', '".$maxthread."', '".$this->sortby."', '".$this->order."', '".$bbuserinfo['id']."', '".$DB->escape_string(SESSION_HOST)."', '".$posts."', '".intval($maxpost)."')"
							);
		$this->credit->update_credit('search', $bbuserinfo['id']);
		$forums->func->standard_redirect( "search.php?{$forums->sessionurl}do=show&amp;searchid=$uniqueid&amp;searchin=".$this->searchin."&amp;showposts=".$this->showposts."&amp;highlight=".urlencode(trim($keywords)) );
	}

	function show_results()
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$this->cached_query   = 0;
		$this->cached_matches = 0;
		require_once( ROOT_PATH."includes/functions_codeparse.php" );
       	$this->parser = new functions_codeparse();
        $this->showposts = $_INPUT['showposts'];
        $this->searchin = $_INPUT['searchin'];
		$this->uniqueid = $_INPUT['searchid'];
		if ($this->uniqueid == "") {
			$forums->func->standard_error("nosearchuserresult");
		}
		$results = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."search WHERE searchid='".$this->uniqueid."'");
		$this->order = $results['sortorder'];
		$this->sortby   = $results['sortby'];
		if ( !$results['query'] ) {
			$thread = $results['threadid'];
			$maxthread = $results['maxthread'];
			$post = $results['postid'];
			$maxpost  = $results['maxpost'];
			$thread_array = array();
			$post_array  = array();
			if ( $thread ) {
				foreach( explode( ",", $thread ) AS $t ) {
					$thread_array[ $t ] = $t;
				}
			}
			if ( $post ) {
				foreach( explode( ",", $post ) AS $t ) {
					$post_array[ $t ] = $t;
				}
			}
			if ( ! $thread AND ! $post ) {
				$forums->func->standard_error("nosearchuserresult");
			}
		}
		if (! $this->showposts) {
			$show['threadmode'] = TRUE;
			if ( $results['query'] ) {
				$pagelinks = $this->start_page($results['maxpost']);
				$rows = $DB->query( $results['query']." LIMIT ".$this->page.", 15" );
			} else if ($this->searchin == 'titles') {
				if ( ! $thread ) {
					$forums->func->standard_error("nosearchuserresult");
				}
				$pagelinks = $this->start_page($maxthread);
				$rows = $DB->query("SELECT *, title AS threadtitle FROM ".TABLE_PREFIX."thread WHERE tid IN(".$thread.") ORDER BY sticky DESC, ".$this->sortby." ".$this->order." LIMIT ".$this->page.", 15");
			} else {
				if ($post) {
					$p = $DB->query("SELECT threadid FROM ".TABLE_PREFIX."post WHERE pid IN(".$post.")");
					while ( $pr = $DB->fetch_array($p) ) {
						$thread_array[ $pr['threadid'] ] = $pr['threadid'];
					}
					$thread         = implode( ",", $thread_array );
					$maxthread = count( $thread_array );
				}
				$pagelinks = $this->start_page($maxthread);
				$rows = $DB->query("SELECT *, title AS threadtitle FROM ".TABLE_PREFIX."thread WHERE tid IN(".$thread.") ORDER BY sticky DESC, ".$this->sortby." ".$this->order." LIMIT ".$this->page.", 15");
			}
			if ( $DB->num_rows($rows) ) {
				$thread['cansee'] = TRUE;
				while ( $row = $DB->fetch_array($rows) ) {
					$row['keywords'] = urlencode($this->highlight);
					$allthread[] = $this->parse_entry($row);
				}
			} else {
				if ( ! $_INPUT['lastdate'] ) {
					$forums->func->standard_error("nosearchuserresult");
				}
			}
		} else {
			$postids = array();
			if ( $results['query'] ) {
				$pagelinks = $this->start_page($results['maxpost'], 1);
				$rows = $DB->query( $results['query']." LIMIT ".$this->page.", 15" );
			} else {
				if ($this->searchin == 'titles') {
					$pagelinks = $this->start_page($maxthread, 1);
					$rows = $DB->query("SELECT t.*, t.post as thread_post, t.title as threadtitle, p.pid, p.userid, p.username AS username, p.dateline, p.pagetext, p.hidepost, u.*
						FROM ".TABLE_PREFIX."thread t
						LEFT JOIN ".TABLE_PREFIX."post p ON (t.tid=p.threadid AND p.newthread=1)
						LEFT JOIN ".TABLE_PREFIX."user u ON (u.id=p.userid)
						WHERE t.tid IN(".$thread.")
						ORDER BY p.dateline DESC
						LIMIT ".$this->page.",15"
					);
				} else {
					if ($thread) {
						$p = $DB->query( "SELECT pid FROM ".TABLE_PREFIX."post WHERE threadid IN(".$thread.") AND newthread=1" );
						while ( $posts = $DB->fetch_array($p) ) {
							$post_array[ $posts['pid'] ] = $posts['pid'];
						}
						$post = implode( ",", $post_array );
						$maxpost = count( $post_array );
					}
					$pagelinks = $this->start_page($maxpost, 1);
					$rows = $DB->query("SELECT t.*, t.post as thread_post, t.title as threadtitle, p.pid, p.userid, p.username AS username, p.dateline, p.pagetext, p.hidepost, u.*
						FROM ".TABLE_PREFIX."post p
						LEFT JOIN ".TABLE_PREFIX."thread t ON (t.tid=p.threadid)
						LEFT JOIN ".TABLE_PREFIX."user u ON (u.id=p.userid)
						WHERE p.pid IN(".$post.")
						ORDER BY p.dateline DESC
						LIMIT ".$this->page.",15"
					);
				}
			}
			if ( $DB->num_rows($rows) ) {
				while ( $row = $DB->fetch_array($rows) ) {
					$row['keywords']  = urlencode($this->highlight);
					$row['dateline'] = $forums->func->get_date( $row['dateline'], 2 );
					$row['pagetext'] = $row['hidepost'] ? $forums->lang['_posthidden'] : $this->parser->convert_text( $row['pagetext'] );
					$user = $forums->func->fetch_user( $row );
					$post = $this->parse_entry($row, 1);
					$allpost[] = array('post' => $post, 'user' => $user);
				}
			} else {
				$forums->func->standard_error("nosearchuserresult");
			}
		}
		$pagetitle = $forums->lang['searchresult']." - ".$bboptions['bbtitle'];
		$nav = array( "<a href='search.php?{$forums->sessionurl}'>".$forums->lang['search']."</a>", $forums->lang['searchresult'] );
		$jsinclude['ajax'] = 1;
		$mxajax_request_type = "POST";
		include $forums->func->load_template('search_results');
		exit;
	}

	function start_page($amount)
	{
		global $forums;
		$this->links = $forums->func->build_pagelinks( array( 'totalpages'  => $amount,
													 'perpage'    => 15,
													 'curpage'  => $this->page,
													 'pagelink'    => "search.php?{$forums->sessionurl}&amp;do=show&amp;searchid=".$this->uniqueid."&amp;searchin=".$this->searchin."&amp;showposts=".$this->showposts."&amp;highlight=".$this->highlight,
											)	   );
		return $this->links;
	}

	function parse_entry($thread, $view_as_post=0)
	{
		global $DB, $forums, $bboptions, $bbuserinfo;
		$thread = $this->parse_data($thread);
		if ($thread['sticky'] == 1) {
			$thread['prefix']     = $bboptions['stickyprefix'];
			$thread['folder_img'] = array('icons' => 'sticky.gif',
											'title' => $forums->lang['threadstick'],);
		}
		if ($thread['sticky'] == 2) {
			$thread['prefix']     = $bboptions['gstickyprefix'];
			$thread['folder_img'] = array('icons' => 'sticky.gif',
											'title' => $forums->lang['threadgstick'],);
		}
		if ($view_as_post == 1) {
			if ( $bboptions['postsearchlength'] ) {
				$thread['pagetext'] = preg_replace('/<img (.*) \/>/iU', '', $thread['pagetext']);
				$thread['pagetext'] = strip_tags($thread['pagetext'], '(<br />|<br>)');
				$thread['pagetext'] = $forums->func->fetch_trimmed_title( $thread['pagetext'], $bboptions['postsearchlength'] );
				$thread['pagetext'] = str_replace( "\n", "<br />", $thread['pagetext'] );
			}
			$thread['username'] = '<strong>'.$forums->func->fetch_user_link($thread['username'], $thread['userid']).'</strong>';
			if ($thread['keywords']) {
				$keywords = str_replace( "+", ",", rawurldecode($thread['keywords']) );
				if ( preg_match("/,(and|or),/i", $keywords) ) {
					while ( preg_match("/,(and|or),/i", $keywords, $match) ) {
						$word_array = explode( ",".$match[1].",", $keywords );
						if (is_array($word_array)) {
							foreach ($word_array AS $keywords) {
								$thread['pagetext'] = preg_replace( "/(.*)(".preg_quote($keywords, "/").")(.*)/i", "\\1<span class='highlight'>\\2</span>\\3", $thread['pagetext'] );
							}
						}
					}
				} else {
					$thread['pagetext'] = preg_replace( "/(.*)(".preg_quote($keywords, "/").")(.*)/i", "\\1<span class='highlight'>\\2</span>\\3", $thread['pagetext'] );
				}
			}
		} else {
			$forums->func->check_cache('st');
			$thread['st'] = $forums->cache['st'][$thread['stopic']]['name'];
		}
		$thread['forum_full_name'] = $forums->forum->foruminfo[ $thread['forumid'] ]['name'];
		if ( strlen($thread['forum_full_name']) > 50 ) {
			$thread['forum_name'] = $forums->func->fetch_trimmed_title( $thread['forum_full_name'], 25 );
		} else {
			$thread['forum_name'] = $thread['forum_full_name'];
		}
		return $thread;
	}

	function parse_data( $thread )
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$last_time = $this->threadread[$thread['tid']] > $_INPUT['lastvisit'] ? $this->threadread[$thread['tid']] : $_INPUT['lastvisit'];
		$maxposts = $bboptions['maxposts'] ? $bboptions['maxposts'] : '10';
		$thread['lastposter'] = $thread['lastposterid'] ? $forums->func->fetch_user_link( $thread['lastposter'], $thread['lastposterid']) : "-".$thread['lastposter']."-";
		$thread['postusername'] = $thread['postuserid'] ? $forums->func->fetch_user_link( $thread['postusername'], $thread['postuserid']) : $thread['postusername']."*";
		if ($thread['pollstate']) {
			$thread['prefix']  = $bboptions['pollprefix'].' ';
		}
		$thread['folder_img'] = $forums->func->folder_icon( $thread, $last_time );
		$forums->func->check_cache('icon');
		$thread['thread_icon'] = $thread['iconid']  ? 1 : 0;
		$thread['thread_cache_icon'] = $forums->cache['icon'][$thread['iconid']]['image'];
		$thread['showpages']  = $forums->func->build_threadpages(
												array( 'id'  => $thread['tid'],
														'totalpost'  => $thread['post'],
														'perpage'    => $maxposts,
													  )
										   );
		$thread['post']  = $forums->func->fetch_number_format( $thread['post'] );
		$thread['views']	 = $forums->func->fetch_number_format( $thread['views'] );
		if ($last_time  && ($thread['lastpost'] > $last_time)) {
			$thread['gotonewpost']  = 1;
		} else {
			$thread['gotonewpost']  = 0;
		}
		$thread['lastpost']  = $forums->func->get_date( $thread['lastpost'], 1 );
		if ($thread['open'] == 2) {
			$t_array = explode("&", $thread['moved']);
			$thread['tid']       = $t_array[0];
			$thread['forumid']  = $t_array[1];
			$thread['views']     = '--';
			$thread['post']     = '--';
			$thread['prefix']    = $bboptions['movedprefix']." ";
			$thread['gotonewpost'] = "";
		}
		return $thread;
	}

    function filter_keywords($words="", $name=0)
    {
    	global $forums;
    	$words = trim($forums->func->strtolower(str_replace("%", "\\%", $words)));
    	$words = preg_replace( "/\s+(and|or|&|\|)$/" , "" , $words );
    	$words = str_replace( "_", "\\_", $words );
    	if ($name == 0) {
    		$words = preg_replace( "/[\[\]\{\}\(\)\,\\\\\"']|&quot;/", "", $words );
    	}
    	$words = preg_replace( "/^(?:img|quote|code|html|javascript|a href|color|span|div)$/", "", $words );
    	return " ".preg_quote($words)." ";
    }

    function convert_highlight($words="")
    {
    	return preg_replace(array("/\s+(and|or|&|\|)(\s+|$)/i", "/\s/"), array("+\\1+", "+"), trim(rawurldecode($words)));
    }

    function get_forums()
    {
    	global $forums, $DB, $_INPUT;
    	$forumids = array();
    	if ( is_array( $_INPUT['forumlist'] )  ) {
    		if ( in_array( '0', $_INPUT['forumlist'] ) ) {
    			foreach( $forums->forum->foruminfo AS $id => $data ) {
    				$forumids[] = $data['id'];
    			}
    		} else {
				foreach( $_INPUT['forumlist'] AS $l ) {
					if ( $forums->forum->foruminfo[ $l ] ) {
						$forumids[] = intval($l);
					}
				}
				if ( count( $forumids  ) ) {
					foreach( $forumids AS $f ) {
						$children = $forums->forum->forums_get_children( $f );
						if ( is_array($children) AND count($children) ) {
							$forumids  = array_merge( $forumids , $children );
						}
					}
				} else {
					return;
				}
    		}
		} else {
			if ( $_INPUT['forumlist'] == '' ) {
				foreach( $forums->forum->foruminfo AS $id => $data ) {
    				$forumids[] = $data['id'];
    			}
			} else {
				if ( $_INPUT['forumlist'] != "" ) {
					$l = intval($_INPUT['forumlist']);
					if ( $forums->forum->foruminfo[ $l ] ) {
						$forumids[] = intval($l);
					}
					if ( $_INPUT['includesubforum'] == 1 ) {
						$children = $forums->forum->forums_get_children( $f );
						if ( is_array($children) AND count($children) ) {
							$forumids  = array_merge( $forumids , $children );
						}
					}
				}
			}
		}
		$final = array();
		foreach( $forumids  AS $f ) {
			if ( $this->check_permissions($forums->forum->foruminfo[ $f ] ) == TRUE ) {
				$final[] = $f;
			}
		}
    	return implode( "," , $final );
    }

    function check_permissions($forum)
    {
    	global $forums;
    	$can_read = false;
    	if ($forums->func->fetch_permissions( $forum['canread'], 'canread') == true) {
    		$can_read = true;
    	}
        if ($forum['password'] != "" AND $can_read == true) {
			if ($forums->forum->check_password($forum['id']) != true) {
				$can_read = false;
			}
		}
		return $can_read;
	}
}

$output = new search();
$output->show();

?>