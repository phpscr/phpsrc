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
define('THIS_SCRIPT', 'usercp');
require ('./global.php');

class usercp {

	var $posthash = '';
	var $pmselect = '';
	var $threadread = '';
	var $read_array = array();

    function show()
    {
    	global $forums, $DB, $_INPUT, $bbuserinfo;
        $this->posthash = $forums->func->md5_check();
		$forums->forum->forums_init();
		if ( ! $bbuserinfo['id'] ) {
			$forums->func->standard_error("notlogin");
		}
		$forums->lang = $forums->func->load_lang($forums->lang, 'usercp' );
    	if ( $bbuserinfo['pmquota']) {
    		$bbuserinfo['folder_links'] = "";
			$bbuserinfo['pmfolders'] = unserialize($bbuserinfo['pmfolders']);
			if (count($bbuserinfo['pmfolders']) < 2) {
				$bbuserinfo['pmfolders'] = array(-1 => array( 'pmcount' => 0, 'foldername' => $forums->lang['_outbox'] ), 0 => array( 'pmcount' => 0, 'foldername' => $forums->lang['_inbox'] ) );
			}
			foreach( $bbuserinfo['pmfolders'] AS $id => $data ) {
				$this->pmselect .= "<option value='".$id."'>".$data['foldername']."</option>\n";
			}
        }
    	switch($_INPUT['do']) {
    		case 'editprofile':
    			$this->edit_profile();
    			break;
    		case 'setting':
    			$this->forum_setting();
    			break;
    		case 'dosetting':
    			$this->do_forum_setting();
    			break;
    		case 'doprofile':
    			$this->do_profile();
    			break;
    		case 'editsignature':
    			$this->edit_signature();
    			break;
    		case 'dosignature':
    			$this->do_signature();
    			break;
    		case 'editavatar':
    			$this->edit_avatar();
    			break;
    		case 'doavatar':
    			$this->do_avatar();
    			break;
    		case 'subscribethread':
    			$this->subscribe_thread();
    			break;
    		case 'dounsubscribe':
    			$this->do_unsubscribe();
    			break;
    		case 'userchange':
    			$this->userchange();
    			break;
    		case 'dochange':
    			$this->do_change();
    			break;
    		case 'subscribeforum':
    			$this->subscribe_forum();
    			break;
    		case 'getgallery':
    			$this->avatar_gallery();
    			break;
    		case 'setinternalavatar':
    			$this->set_internal_avatar();
    			break;
    		case 'attach':
    			$this->attachment();
    			break;
    		default:
    			$this->usercp_main();
    			break;
    	}
 	}

	function usercp_main()
 	{
 		global $forums, $DB, $bbuserinfo, $bboptions;
		$info['email'] = $bbuserinfo['email'];
		$info['joindate'] = $forums->func->get_date( $bbuserinfo['joindate'], 3 );
		$info['posts'] = $bbuserinfo['posts'];
		$info['daily_average'] = $forums->lang['dailyaverage'];
		if ($bbuserinfo['posts'] > 0 ) {
			$diff = TIMENOW - $bbuserinfo['joindate'];
			$days = ($diff / 3600) / 24;
			$days = $days < 1 ? 1 : $days;
			$info['daily_average']  = sprintf('%.2f', ($bbuserinfo['posts'] / $days) );
		}
		$safe = $DB->query_first("SELECT answer, question FROM ".TABLE_PREFIX."userextra WHERE id = {$bbuserinfo['id']}");
		if (!$safe['answer']) {
			$forums->lang['nosafedesc'] = preg_replace( "#(.*)(^|\.php)#", '\\1\\2?'.$forums->sessionurl, str_replace( "?", '', $forums->lang['nosafedesc']));
			$show_safe = TRUE;
		}
		$pms = $DB->query("SELECT p.*,u.name as fromusername, u.id as from_id
						 FROM ".TABLE_PREFIX."pm p
						 LEFT JOIN ".TABLE_PREFIX."user u ON ( p.fromuserid=u.id )
						WHERE p.userid='".$bbuserinfo['id']."' AND p.folderid=0 AND p.touserid='".$bbuserinfo['id']."'  AND p.pmread=0
						ORDER BY dateline DESC LIMIT 0, 5");
		if ( $DB->num_rows($pms) ) {
			$show['message'] = TRUE;
 			while( $row = $DB->fetch_array($pms) ) {
				if ( $row['attach'] ) {
					$row['attach_img'] = 1;
				}
 				$row['date'] = $forums->func->get_date( $row['dateline'] , 2 );
				$unread_pmlist[] = $row;
			}
		}
		$threadread = array();
		$final_array = array();
		$this->threadread = $forums->func->get_cookie( 'threadread' );
		$this->read_array = unserialize( $this->threadread );
		if ( is_array( $this->read_array ) AND count( $this->read_array ) ) {
			arsort($this->read_array);
			$thread_array = array_slice( array_keys( $this->read_array ), 0, 5 );
			if ( count( $thread_array ) ) {
				$show['thread'] = TRUE;
				$DB->query("SELECT * FROM ".TABLE_PREFIX."thread WHERE tid IN (".implode(",",$thread_array).") LIMIT 0, 5");
				$thread = array();
				while ( $row = $DB->fetch_array() ) {
					if ( $forums->forum->foruminfo[ $row['forumid'] ] ) {
						$row = $this->parse_data( $row );
						$thread[] = $row;
					}
				}
			}
		}
		$forums->func->check_cache('attachmenttype');
		$DB->query("SELECT * FROM ".TABLE_PREFIX."attachment WHERE userid='".$bbuserinfo['id']."' ORDER BY dateline DESC LIMIT 0, 5");
		if ( $DB->num_rows() ) {
			$show['attachment'] = TRUE;
			while ( $row = $DB->fetch_array() ) {
				$row['method'] = $row['pmid'] ? 'msg' : 'post';
				$row['image'] = $forums->cache['attachmenttype'][ $row['extension'] ]['attachimg'];
				$attachment[] = $row;
			}
		}
 		$pagetitle = $forums->lang['usercp']." - ".$bboptions['bbtitle'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>" );
		include $forums->func->load_template('usercp_main');
		exit;
 	}

	function subscribe_thread()
 	{
 		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
 		$datecut   = intval($_INPUT['datecut']) != "" ? intval($_INPUT['datecut']) : 1000;
 		$date_query = $datecut != 1000 ? " AND s.dateline > '".(TIMENOW - ( $datecut * 86400 ) )."' " : "";
		$DB->query("SELECT s.subscribethreadid, s.userid, s.threadid, s.dateline as track_started, t.*
									FROM ".TABLE_PREFIX."subscribethread s
										LEFT JOIN ".TABLE_PREFIX."thread t ON (t.tid=s.threadid)
									WHERE s.userid='".$bbuserinfo['id']."' ".$date_query." ORDER BY s.subscribethreadid DESC
								");
 		if ( $DB->num_rows() ) {
			$show['subscribe'] = TRUE;
 			$last_forumid = -1;
			$this->threadread = $forums->func->get_cookie( 'threadread' );
			$this->read_array = unserialize( $this->threadread );
			while( $thread = $DB->fetch_array() ) {
				$thread = $this->parse_data($thread);
				$thread['description'] = empty($thread['description']) ? '' : $thread['description'].' | ';
				$subscribe[] = $thread;
			}
		}
		$datelist = "";
		foreach( array( 1,7,30,60,90,365 ) AS $day ) {
			$selected = $day == $datecut ? " selected='selected'" : '';
			$datelist .= "<option value='$day'$selected>".$day." {$forums->lang['_days']}</option>\n";
		}
 		if ($bboptions['removesubscibe'] > 0) {
			$forums->lang['autounsubscibe'] = sprintf( $forums->lang['autounsubscibe'], $bboptions['removesubscibe'] );
			$auto_explain = $forums->lang['autounsubscibe'];
 		}
		$pagetitle = $forums->lang['subscibethread']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['subscibethread'] );
		include $forums->func->load_template('usercp_subscribe_thread');
		exit;
 	}

	function subscribe_forum()
 	{
 		global $forums, $DB, $bbuserinfo, $bboptions;
		$DB->query("SELECT s.*, f.*
				FROM ".TABLE_PREFIX."subscribeforum s
				 LEFT JOIN ".TABLE_PREFIX."forum f ON (s.forumid=f.id)
				WHERE s.userid='".$bbuserinfo['id']."'");
 		if ( $DB->num_rows() ) {
			$show['subscribe'] = TRUE;
 			while( $forum = $DB->fetch_array() ) {
 				$forum['foldericon'] = $forums->forum->forums_new_post($forum);
 				$forum['lastpost'] = $forums->func->get_date($forum['lastpost'], 2);
 				$forum['lastthread'] = str_replace( "&#33;" , "!", $forum['lastthread'] );
				$forum['lastthread'] = str_replace( "&quot;", "\"", $forum['lastthread'] );
				if (strlen($forum['lastthread']) > 30) {
					$forum['lastthread'] = $forums->func->fetch_trimmed_title(strip_tags($forum['lastthread']), 14);
				}
				if ($forum['lastthread'] == "") {
					$forum['lastthread'] = "----";
				} else if ($forum['password'] != "") {
					$forum['lastthread'] = $forums->lang['hiddenforum'];
				} else {
					$forum['lastthread'] = "<a href='redirect.php?{$forums->sessionurl}t=".$forum['lastthreadid']."&amp;goto=lastpost'>".$forum['lastthread']."</a>";
				}
				if ( isset($forum['lastposter'])) {
					$forum['lastposter'] = $forum['lastposterid'] ? "<a href='profile.php?{$forums->sessionurl}u={$forum['lastposterid']}'>{$forum['lastposter']}</a>" : $forum['lastposter'];
				} else {
					$forum['lastposter'] = "----";
				}
				$subscribe[] = $forum;
			}
		}
		$pagetitle = $forums->lang['subscibeforum']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['subscibeforum'] );
		include $forums->func->load_template('usercp_subscribe_forum');
		exit;
 	}

	function edit_profile()
 	{
 		global $forums, $DB, $bbuserinfo, $bboptions;
		if (!$bbuserinfo['caneditprofile']) {
			$forums->func->standard_error("noperms");
		}
		$safe = $DB->query_first("SELECT answer, question FROM ".TABLE_PREFIX."userextra WHERE id = {$bbuserinfo['id']}");
		if (is_array($safe)) {
			$bbuserinfo = @array_merge($safe, $bbuserinfo);
		}
		$date = getdate();
		$year = "<option value='0'>--</option>\n";
		$mon  = "<option value='0'>--</option>\n";
		$day  = "<option value='0'>--</option>\n";
		$birthday = explode('-', $bbuserinfo['birthday']);
		$i = $date['year'] - 1;
		$j = $date['year'] - 50;
		for ( $i ; $j < $i ; $i-- ) {
			$selected = ($i == $birthday[0]) ? " selected='selected'" : "";
			$year .= "<option value='$i'{$selected}>$i</option>\n";
		}
		for ( $i = 1 ; $i < 13 ; $i++ ) {
			$selected = ($i == $birthday[1]) ? " selected='selected'" : "";
			$mon .= "<option value='$i'{$selected}>$i ".$forums->lang['month']."</option>\n";
		}
		for ( $i = 1 ; $i < 32 ; $i++ ) {
			$selected = ($i == $birthday[2]) ? " selected='selected'" : "";
			$day .= "<option value='$i'{$selected}>$i</option>\n";
		}
 		$posthash = $this->posthash;
		if ( $bbuserinfo['cancustomtitle'] AND ($bbuserinfo['posts'] > $bboptions['titlechangeposts']) ) {
			$show['usertitle'] = TRUE;
			$forums->func->check_cache("usergroup_{$bbuserinfo['usergroupid']}", 'usergroup');
			$usertitle = $bbuserinfo['customtitle'] ? $bbuserinfo['customtitle'] : $forums->cache["usergroup_{$bbuserinfo['usergroupid']}"]['grouptitle'];
		}
		if ($bbuserinfo['gender'] == 1) $male_check = 'checked="checked"';
		elseif ($bbuserinfo['gender'] == 2) $female_check = 'checked="checked"';
		else $default_check = 'checked="checked"';
		$pagetitle = $forums->lang['editprofile']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['editprofile'] );
		include $forums->func->load_template('usercp_profile');
		exit;
	}

	function edit_signature()
 	{
 		global $forums, $DB, $bbuserinfo, $bboptions;
		if (!$bbuserinfo['cansignature']) {
			$forums->func->standard_error("noperms");
		}
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		require (ROOT_PATH."includes/functions_post.php");
        $this->lib = new functions_post();
		$max_length = $bboptions['signaturemaxlength'] ? $bboptions['signaturemaxlength'] : 0;
		if ( $bboptions['signatureallowhtml'] == 1 ) {
			$this->lib->parser->show_html = 1;
		}
		$posthash = $this->posthash;
		$smiles = $this->lib->construct_smiles();
		$smile_count = $smiles['count'];
		$all_smiles = $smiles['all'];
		$smiles = $smiles['smiles'];
 		$signature1 = $this->lib->parser->convert_text($bbuserinfo['signature']);
		$signature = $signature1;
		$this->cookie_mxeditor = $forums->func->get_cookie('mxeditor');
		if ( $this->cookie_mxeditor ) {
			$bbuserinfo['usewysiwyg'] = ($this->cookie_mxeditor == 'wysiwyg') ? 1 : 0;
		} elseif ($bboptions['mxemode']) {
			$bbuserinfo['usewysiwyg'] = 1;
		} else {
			$bbuserinfo['usewysiwyg'] = 0;
		}
		$signature1 = preg_replace("#<!--sig_img-->(.+?)<!--sig_img1-->#", "", $bbuserinfo['signature']);
		$signature1 = $this->lib->parser->unconvert( $signature1, $bboptions['signatureallowbbcode'], $bboptions['signatureallowhtml'], $bbuserinfo['usewysiwyg'] );
		if (!$bbuserinfo['usewysiwyg']) {
			$signature1 = preg_replace("#<br.*>#siU", "\n", $signature1);
		}
 		$pagetitle = $forums->lang['editsignature']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['editsignature'] );
	    $mxajax_request_type = "POST";
		$jsinclude['ajax'] = 1;
		$jsinclude['mxe'] = true;
		include $forums->func->load_template('usercp_signature');
		exit;
 	}

	function edit_avatar()
 	{
 		global $forums, $DB, $bbuserinfo, $bboptions;
		$posthash = $this->posthash;
 		list( $bbuserinfo['avatar_width'] , $bbuserinfo['avatar_height']  ) = explode ("x", $bbuserinfo['avatarsize']);
 		list( $bboptions['av_width'], $bboptions['av_height']        ) = explode ("x", $bboptions['avatardimension']);
 		list( $w, $h ) = explode ( "x", $bboptions['avatardimensiondefault'] );
 		$my_avatar = $forums->func->get_avatar( $bbuserinfo['avatarlocation'], 1, $bbuserinfo['avatarsize'], $bbuserinfo['avatartype'] );
 		$my_avatar = $my_avatar ? $my_avatar : $forums->lang['noavatar'];
		$allowed_files = implode (' .', explode( "|", $bboptions['avatarextension'] ) );
 		if ( !$bboptions['allowflash'] ) {
			$allowed_files = str_replace( ".swf", "", $allowed_files );
		}
 		$avatar_gallery = array();
 		$dh = opendir( ROOT_PATH.'images/avatars' );
 		while ( $file = readdir( $dh ) ) {
			if ( is_dir( ROOT_PATH.'images/avatars/'.$file ) ) {
				if ( $file != "." && $file != ".." ) {
					$categories[] = array( $file, str_replace( "_", " ", $file ) );
				}
			}
 		}
 		closedir( $dh );
		if( is_array($categories) ) {
			$show['gallerylist'] = TRUE;
			reset( $categories );
			$gallerylist = "<select name='av_cat'>\n";
			foreach( $categories AS $cat ) {
				$gallerylist .= "<option value='".$cat[0]."'>".$cat[1]."</option>\n";
			}
			$gallerylist .= "</select>\n";
		}
 		$formextra   = "";
 		$hidden_field = "";
 		if ($bbuserinfo['canuseavatar'] == 1) {
 			$formextra    = " enctype='multipart/form-data'";
			$hidden_field = "<input type='hidden' name='MAX_FILE_SIZE' value='9000000' />";
		}
 		if ($bboptions['avatarurl']) {
			$show['avatarurl'] = TRUE;
			$avatarurl = "http://";
 		}
		if ($bbuserinfo['canuseavatar'] == 1) {
			$show['avatar_upload'] = TRUE;
 		}
		if ($bboptions['disableavatarsize']) {
			$forums->lang['disableavatarsize'] = sprintf( $forums->lang['disableavatarsize'], $bboptions['av_width'], $bboptions['av_height'], $bboptions['avatamaxsize'] );
			$upload_size = $forums->lang['disableavatarsize'];
		} else {
			$forums->lang['changeavatar'] = sprintf( $forums->lang['changeavatar'], $bboptions['av_width'], $bboptions['av_height'] );
			$upload_size = $forums->lang['changeavatar'];
		}
 		$pagetitle = $forums->lang['editavatar']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['changeavatar'] );
		include $forums->func->load_template('usercp_avatar');
		exit;
 	}

	function attachment()
 	{
 		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		if ( $bbuserinfo['attachlimit'] == -1 ) {
 			$forums->func->standard_error("noperms");
 		}
 		$info = array();
 		$start = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
 		$perpage  = 15;
 		$sortby = "";
 		switch ($_INPUT['sort']) {
 			case 'date':
 				$sortby = 'a.dateline ASC';
 				$info['date_order'] = 'rdate';
 				$info['size_order'] = 'size';
 				break;
 			case 'rdate':
 				$sortby = 'a.dateline DESC';
 				$info['date_order'] = 'date';
 				$info['size_order'] = 'size';
 				break;
 			case 'size':
 				$sortby = 'a.filesize DESC';
 				$info['date_order'] = 'date';
 				$info['size_order'] = 'rsize';
 				break;
 			case 'rsize':
 				$sortby = 'a.filesize ASC';
 				$info['date_order'] = 'date';
 				$info['size_order'] = 'size';
 				break;
 			default:
 				$sortby = 'a.dateline DESC';
 				$info['date_order'] = 'date';
 				$info['size_order'] = 'size';
 				break;
 		}
		if (is_array($_INPUT['attachid'])) {
			foreach ($_INPUT['attachid'] AS $k) {
				$k = intval($k);
				if ( empty($k) ) continue;
				$ids[] = $k;
			}
		}
 		$affected_ids = count($ids);
 		if ( $affected_ids > 0 ) {
    		$attachments = $DB->query("SELECT a.*, p.threadid, p.pid
										 FROM ".TABLE_PREFIX."attachment a
										  LEFT JOIN ".TABLE_PREFIX."post p ON ( a.postid=p.pid )
										 WHERE a.attachmentid IN (".implode(",",$ids).")
										 AND a.userid='".$bbuserinfo['id']."'");
			if ( $attachment = $DB->fetch_array($attachments) ) {
				if ( $attachment['location'] ) {
					@unlink( $bboptions['uploadfolder']."/".$attachment['attachpath']."/".$attachment['location'] );
				}
				if ( $attachment['thumblocation'] ) {
					@unlink( $bboptions['uploadfolder']."/".$attachment['attachpath']."/".$attachment['thumblocation'] );
				}
				if ( $attachment['threadid'] ) {
					$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."thread SET attach=attach-1 WHERE tid='".$attachment['threadid']."'" );
				}
			}
			$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."attachment WHERE attachmentid IN (".implode(",",$ids).") AND userid='".$bbuserinfo['id']."'" );
 		}
 		$maxspace = intval($bbuserinfo['attachlimit']);
 		$stats = $DB->query_first( "SELECT count(*) as count, sum(filesize) as sum FROM ".TABLE_PREFIX."attachment WHERE userid='".$bbuserinfo['id']."'" );
		$info['used_space'] = $forums->func->fetch_number_format(intval($stats['sum']), true);
 		if ( $maxspace > 0 ) {
			$show['limit'] = TRUE;
			$info['totalpercent'] = $stats['sum'] ? sprintf( "%.0f", ( ( $stats['sum'] / ($maxspace * 1024) ) * 100) ) : 0;
			$info['img_width'] = $info['totalpercent'] > 0 ? intval($info['totalpercent']) * 2.4 : 1;
			$info['total_space'] = $forums->func->fetch_number_format($maxspace * 1024, true);
			if ($info['img_width'] > 250) {
				$info['img_width'] = 250;
			}
 		}
 		$pages = $forums->func->build_pagelinks( array( 'totalpages'  => $stats['count'],
											   'perpage'    => $perpage,
											   'curpage'  => $start,
											   'pagelink'    => "usercp.php?{$forums->sessionurl}do=attach&amp;sort={$_INPUT['sort']}",
									  )      );
		$forums->func->check_cache('attachmenttype');
    	$posts = $DB->query("SELECT a.*, t.*, p.pid
										 FROM ".TABLE_PREFIX."attachment a
										  LEFT JOIN ".TABLE_PREFIX."post p ON ( a.postid=p.pid )
										  LEFT JOIN ".TABLE_PREFIX."thread t ON ( t.tid=p.threadid )
										 WHERE a.userid='".$bbuserinfo['id']."'
										 ORDER BY ".$sortby."
										 LIMIT ".$start.", ".$perpage."");
		while ( $row = $DB->fetch_array( $posts ) ) {
			if ( $forums->func->fetch_permissions($forums->forum->foruminfo[ $row['forumid'] ]['canread'], 'canread') != TRUE ) {
				$row['title'] = $forums->lang['cannotview'];
			}
			if ( $row['postid'] ) {
				$row['type'] = 'post';
			} elseif ( $row['blogid'] ) {
				$row['type'] = 'blog';
				$row['title'] = "BLOG";
			} elseif ( $row['pmid'] ) {
				$row['type'] = 'msg';
				$row['title'] = $forums->lang['pm'];
			} else {
				$row['type'] = 'noattribute';
				$row['title'] = $forums->lang['noattribute'];
			}
			$row['image']       = $forums->cache['attachmenttype'][ $row['extension'] ]['attachimg'];
			$row['shortname']  = $forums->func->fetch_trimmed_title( $row['filename'], 15 );
			$row['dateline'] = $forums->func->get_date( $row['dateline'], 1 );
			$row['filesize']   = $forums->func->fetch_number_format( $row['filesize'], true );
			$attach[] = $row;
		}
		$forums->lang['usedspace'] = sprintf( $forums->lang['usedspace'], $info['used_space'] );
		$forums->lang['leftspace'] = sprintf( $forums->lang['leftspace'], $info['total_space'] );
		$forums->lang['totalattachs'] = sprintf( $forums->lang['totalattachs'], $stats['count'], $info['totalpercent'] );
    	$pagetitle = $forums->lang['attachmanage']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['attachmanage'] );
		include $forums->func->load_template('usercp_attachment');
		exit;
 	}

	function forum_setting()
 	{
 		global $forums, $DB, $bbuserinfo, $bboptions;
		$posthash = $this->posthash;
		require_once(ROOT_PATH."includes/functions_user.php");
		$this->fu = new functions_user();
		$forums->func->check_cache('style');
		if ( $bboptions['allowselectstyles'] AND count($forums->cache['style']) > 1 ) {
			$show['style'] = TRUE;
			$select_style = "<select name='style'>\n";
			foreach ($forums->cache['style'] AS $id => $style) {
				$selected = ($id == $bbuserinfo['style']) ? " selected='selected'" : "";
				$select_style .= "<option value='$id'{$selected}>";
				$parentlist = explode(',', $style['parentlist']);
				$style_prefix = "";
				for ($i = 1, $n = count($parentlist); $i < $n; $i++) {
					$style_prefix .= "--";
				}
				$select_style .= $style_prefix . $style['title'];
				$select_style .="</option>\n";
			}
			$select_style .= "</select>\n";
		}
 		$offset = ( $bbuserinfo['timezoneoffset'] != "" ) ? $bbuserinfo['timezoneoffset'] : 8;
 		$time_select = "<select name='u_timezone'>\n";
		foreach ( $this->fu->fetch_timezone() AS $off => $words ) {
			$selected = ($off == $offset) ? " selected='selected'" : "";
			$time_select .= "<option value='$off'{$selected}>$words</option>\n";
 		}
 		$time_select .= "</select>\n";
 		if ($bboptions['perpagepost'] == "") {
			$bboptions['perpagepost'] = '5,10,15,20,25,30,35,40';
		}
		if ($bboptions['perpagethread'] == "") {
			$bboptions['perpagethread'] = '5,10,15,20,25,30,35,40';
		}
 		list($thread_page, $post_page) = explode( "&", $bbuserinfo['viewprefs'] );
 		if ($post_page == "") {
 			$post_page = -1;
 		}
 		if ($thread_page == "") {
 			$thread_page = -1;
 		}
 		$pp_a = array();
 		$tp_a = array();
 		$post_select  = "";
 		$thread_select = "";
 		$pp_a[] = array( '-1', $forums->lang['usedefault']);
 		$tp_a[] = array( '-1', $forums->lang['usedefault']);
 		foreach( explode( ',', $bboptions['perpagepost'] ) AS $n ) {
 			$n = intval(trim($n));
 			$pp_a[] = array( $n, $n );
 		}
 		foreach( explode( ',', $bboptions['perpagethread'] ) AS $n ) {
 			$n = intval(trim($n));
 			$tp_a[] = array( $n, $n );
 		}
 		foreach( $pp_a AS $id => $data ) {
			$selected = ($data[0] == $post_page) ? " selected='selected'" : "";
 			$post_select .= "<option value='{$data[0]}'{$selected}>{$data[1]}</option>\n";
 		}
 		foreach( $tp_a AS $id => $data ) {
			$selected = ($data[0] == $thread_page) ? " selected='selected'" : "";
 			$thread_select .= "<option value='{$data[0]}'{$selected}>{$data[1]}</option>\n";
 		}
		$userinfo = array();
 		foreach ( array('dstonoff', 'showavatars', 'showsignatures', 'pmpop', 'hideemail', 'adminemail', 'usepm', 'emailonpm', 'usewysiwyg','redirecttype') AS $k ) {
 			if (!empty($bbuserinfo[ $k ]) OR $bbuserinfo[ $k ] != 0) {
 				$userinfo[$k] = " checked='checked'";
 			}
 		}
 		$pagetitle = $forums->lang['forumsetting']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['forumsetting'] );
		include $forums->func->load_template('usercp_forum_setting');
		exit;
 	}

	function userchange()
 	{
 		global $forums, $bboptions, $bbuserinfo;
 		$pagetitle = $forums->lang['changeinfo']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['changeinfo'] );
		include $forums->func->load_template('usercp_change_password');
		exit;
 	}

	function do_unsubscribe()
 	{
 		global $forums, $DB, $_INPUT, $bbuserinfo;
 		$ids = array();
		if (is_array($_INPUT['sid'])) {
			foreach ($_INPUT['sid'] AS $id) {
				$id = intval($id);
				if(!$id) continue;
				$ids[] = $id;
			}
		}
		if ( count($ids) > 0 ) {
			if ( $_INPUT['type'] == 'thread') {
				$DB->shutdown_query( "DELETE FROM ".TABLE_PREFIX."subscribethread WHERE userid='".$bbuserinfo['id']."' AND subscribethreadid IN (".implode( ",", $ids ).")" );
				$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=subscribethread");
			}
			if ($_INPUT['type'] == 'forum') {
				$DB->shutdown_query( "DELETE FROM ".TABLE_PREFIX."subscribeforum WHERE userid='".$bbuserinfo['id']."' AND forumid IN (".implode( ",", $ids ).")" );
				$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=subscribeforum");
			}
 		} else {
 			$forums->func->standard_error("erroridinfo");
 		}
		$forums->func->standard_redirect("usercp.php?".$forums->sessionurl);
 	}

	function do_profile()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
        if ($_INPUT['posthash'] != $this->posthash ) {
			$forums->func->standard_error("badposthash");
		}
		$banfilter = array();
		$day = intval($_INPUT['day']);
		$month = intval($_INPUT['month']);
		$year = intval($_INPUT['year']);
		$userinfo['website'] = trim($_INPUT['website']);
		$userinfo['qq'] = intval($_INPUT['qq']);
		$userinfo['uc'] = intval($_INPUT['uc']);
		$userinfo['popo'] = trim($_INPUT['popo']);
		$userinfo['skype'] = trim($_INPUT['skype']);
		$userinfo['icq'] = intval($_INPUT['icq']);
		$userinfo['gender'] = intval($_INPUT['gender']);
		$userinfo['yahoo'] = trim($_INPUT['yahoo']);
		$userinfo['aim'] = trim($_INPUT['aim']);
		$userinfo['msn'] = trim($_INPUT['msn']);
		$userinfo['location'] = trim($_INPUT['location']);
		if (($bboptions['locationmaxlength'] && $forums->func->strlen($userinfo['location']) > $bboptions['locationmaxlength']) || strlen($userinfo['location']) > 250) {
			$forums->func->standard_error("errorlocation");
		}
		if (strlen($userinfo['website']) > 150) {
			$forums->func->standard_error("errorwebsite");
		}
		if ( ($userinfo['qq']) && (!preg_match( "/^(?:\d+)$/", $userinfo['qq'] ) ) ) {
			$forums->func->standard_error("errorqq");
		}
		if ( ($userinfo['uc']) && (!preg_match( "/^(?:\d+)$/", $userinfo['uc'] ) ) ) {
			$forums->func->standard_error("erroruc");
		}
		if ( ($userinfo['icq']) && (!preg_match( "/^(?:\d+)$/", $userinfo['icq'] ) ) ) {
			$forums->func->standard_error("erroricq");
		}
		require (ROOT_PATH."includes/functions_codeparse.php");
        $this->parser = new functions_codeparse();
		if (($day == 0) AND ($month == 0)) {
			$birthday = '';
		} else {
			if ($month < 10 AND $month > 0) {
				$month = '0' . $month;
			}
			if ($day < 10 AND $day > 0) {
				$day = '0' . $day;
			}
			if (($year > 1901) AND ($year < $forums->func->get_time(TIMENOW, 'Y'))) {
				if (checkdate($month, $day, $year)) {
					$userinfo['birthday'] = "$year-$month-$day";
				} else {
					$forums->func->standard_error("errorbirthday");
				}
			} else if ($year >= date('Y')) {
				$forums->func->standard_error("errorbirthday");
			} else {
				if (checkdate($month, $day, 1996)) {
					$userinfo['birthday'] = "0000-$month-$day";
				} else {
					$forums->func->standard_error("errorbirthday");
				}
			}
		}
		if ( ! preg_match( "#^http://#", $userinfo['website'] ) ) {
			$userinfo['website'] = 'http://'.$userinfo['website'];
		}
		if ( (isset($_INPUT['customtitle'])) AND $bbuserinfo['cancustomtitle'] AND ( $bbuserinfo['posts'] >= $bboptions['titlechangeposts']) ) {
			$userinfo['customtitle'] = trim($_INPUT['customtitle']);
			$userinfo['customtitle'] = $this->parser->censoredwords( $userinfo['customtitle'] );
			$check = str_replace(array('　',' '), array("",""), $userinfo['customtitle']);
			$DB->query("SELECT * FROM ".TABLE_PREFIX."banfilter WHERE type='title'");
			while( $r = $DB->fetch_array() ) {
				if ( preg_match( "/{$r['content']}/", $check ) ) {
					$forums->func->standard_error("errorcustomtitle");
				}
			}
			$title = ", customtitle = '".addslashes($userinfo['customtitle'])."'";
		}
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET birthday= '".$userinfo['birthday']."', gender= ".$userinfo['gender'].", website = '".addslashes($userinfo['website'])."', qq = '".$userinfo['qq']."', uc = '".$userinfo['uc']."', skype = '".$userinfo['skype']."', popo = '".$userinfo['popo']."', icq = '".$userinfo['icq']."', aim = '".addslashes($userinfo['aim'])."', yahoo = '".addslashes($userinfo['yahoo'])."', msn = '".addslashes($userinfo['msn'])."', location = '".addslashes($this->parser->censoredwords($userinfo['location']))."'$title WHERE id='".$bbuserinfo['id']."'" );

		$safes = $DB->query("SELECT answer, question FROM ".TABLE_PREFIX."userextra WHERE id = {$bbuserinfo['id']}");
		if ($DB->num_rows()) {
			$has_update = TRUE;
			$safe = $DB->fetch_array($safes);
			$bbuserinfo = array_merge($safe, $bbuserinfo);
		}

		$userinfo['question'] = trim($_INPUT['question']);
		$userinfo['answer'] = trim($_INPUT['answer']);
		if ($bbuserinfo['question'] AND $bbuserinfo['answer']) {
			$userinfo['newquestion'] = trim($_INPUT['newquestion']);
			$userinfo['newanswer'] = trim($_INPUT['newanswer']);

			if ($userinfo['answer'] OR $userinfo['newquestion'] OR $userinfo['newanswer']) {
				if ($userinfo['newquestion'] AND $userinfo['newanswer'] AND !$userinfo['answer']) {
					$forums->func->standard_error("originanswer");
				}
				if ($userinfo['answer'] != $bbuserinfo['answer']) {
					$forums->func->standard_error("originanswererror");
				}
				if ((!$userinfo['newquestion'] AND $userinfo['newanswer']) OR ($userinfo['newquestion'] AND !$userinfo['newanswer']) OR (!$userinfo['newquestion'] AND !$userinfo['newanswer'])) {
					$forums->func->standard_error("requireanswerquestion");
				}
				if ( $userinfo['newquestion'] == $userinfo['newanswer'] ) {
					$forums->func->standard_error("cannotsame");
				}
				$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."userextra SET question= '".addslashes($userinfo['newquestion'])."', answer= '".addslashes($userinfo['newanswer'])."' WHERE id='".$bbuserinfo['id']."'" );
			}
		} else {
			if ((!$userinfo['question'] AND $userinfo['answer']) OR ($userinfo['question'] AND !$userinfo['answer'])) {
				$forums->func->standard_error("requireanswerquestion");
			}
			if ($userinfo['question'] AND $userinfo['answer']) {
				if ( $userinfo['question'] == $userinfo['answer'] ) {
					$forums->func->standard_error("cannotsame");
				}
				if ($has_update) {
					$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."userextra SET question= '".addslashes($userinfo['question'])."', answer= '".addslashes($userinfo['answer'])."' WHERE id='".$bbuserinfo['id']."'" );
				} else {
					$DB->shutdown_query( "INSERT INTO ".TABLE_PREFIX."userextra (id, question, answer) VALUES (".$bbuserinfo['id'].", '".addslashes($userinfo['question'])."', '".addslashes($userinfo['answer'])."')" );
				}
			}
		}

		$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=editprofile");
	}

	function do_signature()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		if (!$bbuserinfo['cansignature']) {
			$forums->func->standard_error("noperms");
		}
		require (ROOT_PATH."includes/functions_codeparse.php");
        $this->parser = new functions_codeparse();
		if (($bboptions['signaturemaxlength'] && $forums->func->strlen(strip_tags($_POST['post'])) > $bboptions['signaturemaxlength']) || strlen($_POST['post']) > 16777215) {
			$forums->func->standard_error("errorsignature");
		}
		if ( $_INPUT['posthash'] != $this->posthash ) {
			$forums->func->standard_error("badposthash");
		}
		$this->cookie_mxeditor = $forums->func->get_cookie('mxeditor');
		if ( $this->cookie_mxeditor ) {
			$bbuserinfo['usewysiwyg'] = ($this->cookie_mxeditor == 'wysiwyg') ? 1 : 0;
		} elseif ($bboptions['mxemode']) {
			$bbuserinfo['usewysiwyg'] = 1;
		} else {
			$bbuserinfo['usewysiwyg'] = 0;
		}
		$post = $bbuserinfo['usewysiwyg'] ? $_POST['post'] : $forums->func->htmlspecialchars_uni($_POST['post']);
		$post = $this->parser->censoredwords($post);
		$signature = $this->parser->convert(array(
			'text' => $forums->func->stripslashes_uni($post),
			'allowsmilies' => 1,
			'allowcode' => intval($bboptions['signatureallowbbcode']),
			'allowhtml' => intval($bboptions['signatureallowhtml']),
			'usewysiwyg' => $bbuserinfo['usewysiwyg'])
		);
		if ($this->parser->error != "") {
			$forums->func->standard_error($this->parser->error);
		}
		if ($_INPUT['delete']) {
			$this->clean_signature($bbuserinfo['id']);
			$has_cleaned = TRUE;
		}

		if ($bboptions['allowuploadsigimg'] AND $bbuserinfo['cansigimg']) {
			if ($_FILES['sig_img']['name'] != "" AND ($_FILES['sig_img']['name'] != "none") ) {
				list($p_width, $p_height) = explode( "x", $bboptions['sigimgdimension'] );
				$path = ROOT_PATH."data/signature";
				if ( ! is_dir( $path ) ) {
					if ( ! @ mkdir( $path, 0777 ) ) {
						$forums->func->standard_error("cannotwrite");
					}
					@chmod( $path, 0777 );
				}
				if (!$has_cleaned) {
					$this->clean_signature($bbuserinfo['id']);
				}
				$real_name = 'sig-'.$bbuserinfo['id'];
				$real_type = 2;
				require_once( ROOT_PATH.'includes/functions_upload.php' );
				$upload = new functions_upload();
				$upload->filepath = $path;
				$upload->allow_extension  = array( 'jpg', 'jpeg', 'gif', 'png' );
				if ($bbuserinfo['canuseflash']) {
					$upload->allow_extension[]  = 'swf';
				}
				$fileinfo['name'] = $_FILES['sig_img']['name'];
				$fileinfo['size'] = $_FILES['sig_img']['size'];
				$fileinfo['type'] = $_FILES['sig_img']['type'];
				$fileinfo['tmp_name'] = $_FILES['sig_img']['tmp_name'];
				$fileinfo['filename'] = 'sig-'.$bbuserinfo['id'];
				$fileinfo['num'] = 0;
				$upload->upload_file($fileinfo);
				if ( $upload->error_no ) {
					switch( $upload->error_no ) {
						case 1:
							$forums->func->standard_error("errorupload1");
						case 2:
							$forums->func->standard_error("errorupload2");
						case 3:
							$forums->func->standard_error("errorupload3");
						case 4:
							$forums->func->standard_error("errorupload4");
					}
				}
				$real_name = $upload->parsed_file_name[0];
				if ( $p_width AND $p_height AND $upload->file_extension != '.swf' ) {
					require_once( ROOT_PATH.'includes/functions_image.php' );
					$image = new functions_image();
					$image->filepath = $path;
					$image->filename = $real_name;
					$image->thumb_filename  = 'thumb_'.$bbuserinfo['id'];
					$image->thumbswidth  = $p_width;
					$image->thumbsheight = $p_height;
					$return = $image->generate_thumbnail();
					$im['img_width']  = $return['thumbwidth'];
					$im['img_height'] = $return['thumbheight'];
					if ( strstr( $return['thumblocation'], 'thumb_' ) ) {
						@unlink( $path."/".$real_name );
						$real_name = 'sig-'.$bbuserinfo['id'].'.'.$image->file_extension;
						@rename( $path."/".$return['thumblocation'], $path."/".$real_name );
						@chmod(  $path."/".$real_name, 0777 );
					}
				} else {
					if ( ! $img_size = @GetImageSize( $path.'/'.$real_name ) ) {
						$img_size[0] = $p_width;
						$img_size[1] = $p_height;
					}
					$w = $img_size[0] ? intval($img_size[0]) : $p_width;
					$h = $img_size[1] ? intval($img_size[1]) : $p_height;
					$im['img_width']  = $w > $p_width  ? $p_width  : $w;
					$im['img_height'] = $h > $p_height ? $p_height : $h;
				}
				$real_choice = $real_name;
				if (!preg_match("#<!--sig_img-->(.+?)<!--sig_img1-->#", $bbuserinfo['signature'])) {
					$signature .= "<!--sig_img--><div><img src='./data/signature/".$real_choice."' width='".$im['img_width']."' height='".$im['img_height']."' /></div><!--sig_img1-->";
				}
			}
			if ( preg_match("#<!--sig_img-->(.+?)<!--sig_img1-->#", $bbuserinfo['signature'], $match) AND !$_INPUT['delete'] ) {
				$signature .= $match[0];
			}
		}

		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET signature = '".addslashes($signature)."' WHERE id=".$bbuserinfo['id']."" );
		$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=editsignature");
	}

	function avatar_gallery()
 	{
 		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
 		$avatar_gallery    = array();
 		$selectedavatar = preg_replace( "/[^\w\s_\-]/", "", $_INPUT['av_cat'] );
 		$thiscategories = FALSE;
 		$currentcategories = "";
 		$dh = opendir( ROOT_PATH.'images/avatars' );
 		while ( $file = readdir( $dh ) ) {
			if ( is_dir( ROOT_PATH.'images/avatars'."/".$file ) ) {
				if ( $file != "." && $file != ".." ) {
					if ( $file == $selectedavatar ) {
						$thiscategories = TRUE;
						$currentcategories = str_replace( "_", " ", $file );
					}
					$categories[] = array( $file, str_replace( "_", " ", $file ) );
				}
			}
 		}
 		closedir( $dh );
 		reset( $categories );
 		if ($selectedavatar) {
 			if ( $thiscategories != TRUE ) {
 				$forums->func->standard_error("erroravatarcategories");
 			}
 			$currentavatar = "/".$selectedavatar;
 		}
 		$dh = opendir( ROOT_PATH.'images/avatars'.$currentavatar);
 		while ( $file = readdir( $dh ) ) {
 			if ( ! preg_match( "/^..?$|^index|^\.ds_store|^\.htaccess/i", $file ) ) {
 				if ( is_file( ROOT_PATH."images/avatars".$currentavatar."/".$file) ) {
 					if ( preg_match( "/\.(gif|jpg|jpeg|png|swf)$/i", $file ) ) {
 						$galleryimages[] = array (
							'file'=>$file,
							'encode'=>urlencode($file),
							'name'=> str_replace( '_', ' ', preg_replace( '/^(.*)\.\w+$/', '\\1', $file ) ),
							);
 					}
 				}
 			}
 		}
 		if ( is_array($galleryimages) AND count($galleryimages) ) {
 			natcasesort($galleryimages);
 			reset($galleryimages);
 		}
 		closedir( $dh );
 		$colspan = $bboptions['avatarcolspannumbers'] == "" ? 5 : $bboptions['avatarcolspannumbers'];
 		$gal_found = count($galleryimages);
		$posthash = $this->posthash;
		$current_folder = urlencode($selectedavatar);
 		$c = 0;
		$avatar_list = '';
 		if ( is_array($galleryimages) AND count($galleryimages) ) {
			$avatar_list_show = 1;
 		}
 		$pagetitle = $forums->lang['avatargallery']." - ".$forums->lang['usercp'];
 		$nav = array( "<a href='usercp.php?{$forums->sessionurl}'>".$forums->lang['usercp']."</a>", $forums->lang['avatargallery'] );
		include $forums->func->load_template('usercp_avatar_category');
		exit;
 	}

	function set_internal_avatar()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
        if ($_INPUT['posthash'] != $this->posthash ) {
			$forums->func->standard_error("badposthash");
		}
		$real_choice = 'noavatar';
		$real_dims   = '';
		$real_dir    = "";
		$save_dir    = "";
		$current_folder  = preg_replace( "/[^\s\w_-]/", "", rawurldecode($_INPUT['current_folder']) );
		$selected_avatar = preg_replace( "/[^\s\w\._\-\[\]\(\)]/"  , "", rawurldecode($_INPUT['avatar']) );
		if ($current_folder != "") {
			$real_dir = "/".$current_folder;
			$save_dir = $current_folder."/";
		}
		$avatar_gallery = array();
		$dh = opendir( ROOT_PATH.'images/avatars'.$real_dir );
		while ( $file = readdir( $dh ) ) {
			if ( !preg_match( "/^..?$|^index/i", $file ) ) {
				$avatar_gallery[] = $file;
			}
		}
		closedir( $dh );
		if (!in_array( $selected_avatar, $avatar_gallery ) ) {
			$forums->func->standard_error("erroravatar");
		}
		$final_string = $save_dir.$selected_avatar;
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET avatarlocation='".addslashes($final_string)."', avatartype=3 WHERE id='".$bbuserinfo['id']."'" );
		$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=editavatar");
	}

	function do_avatar()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->func->check_cache('attachmenttype');
		$real_type = "";
        if ($_INPUT['posthash'] != $this->posthash ) {
			$forums->func->standard_error("badposthash");
		}
		if ( $_INPUT['remove'] ) {
			$this->clean_avatars($bbuserinfo['id']);
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET avatarlocation='', avatarsize='', avatartype=0 WHERE id=".$bbuserinfo['id']."" );
			$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=editavatar");
		}
		list($p_width, $p_height) = explode( "x", $bboptions['avatardimension'] );
		if ( preg_match( "/^http:\/\/$/i", $_INPUT['avatarurl'] ) ) {
			$_INPUT['avatarurl'] = "";
		}
		if ( empty($_INPUT['avatarurl']) ) {
			if ($_FILES['upload_avatar']['name'] != "" AND ($_FILES['upload_avatar']['name'] != "none") ) {
				if ( ($bbuserinfo['canuseavatar'] != 1) OR ($bboptions['avatamaxsize'] < 1) ) {
					$forums->func->standard_error("cannotupavatar");
				}
				$path = $bboptions['uploadfolder'].'/avatar';
				if ( ! is_dir( $path ) ) {
					if ( ! @ mkdir( $path, 0777 ) ) {
						$forums->func->standard_error("cannotwrite");
					}
					@chmod( $path, 0777 );
				}
				$this->clean_avatars($bbuserinfo['id']);
				$real_name = 'avatar-'.$bbuserinfo['id'];
				$real_type = 2;
				require_once( ROOT_PATH.'includes/functions_upload.php' );
				$upload = new functions_upload();
				$upload->filepath      = $path;
				$upload->maxfilesize     = ($bboptions['avatamaxsize'] * 1024) * 8;
				$forums->func->check_cache('attachmenttype');
				if ( is_array( $forums->cache['attachmenttype'] ) AND count( $forums->cache['attachmenttype'] ) ) {
					foreach( $forums->cache['attachmenttype'] AS $idx => $data ) {
						if ( $data['useavatar'] ) {
							$upload->allow_extension[] = $data['extension'];
						}
					}
				}
				$fileinfo['name'] = $_FILES['upload_avatar']['name'];
				$fileinfo['size'] = $_FILES['upload_avatar']['size'];
				$fileinfo['type'] = $_FILES['upload_avatar']['type'];
				$fileinfo['tmp_name'] = $_FILES['upload_avatar']['tmp_name'];
				$fileinfo['filename'] = $real_name;
				$fileinfo['num'] = 0;
				$upload->upload_file($fileinfo);
				if ( $upload->error_no ) {
					switch( $upload->error_no ) {
						case 1:
							$forums->func->standard_error("errorupload1");
						case 2:
							$forums->func->standard_error("errorupload2");
						case 3:
							$forums->func->standard_error("errorupload3");
						case 4:
							$forums->func->standard_error("errorupload4");
					}
				}
				if ( ( $upload->file_extension == 'swf' ) AND (!$bbuserinfo['canuseflash']) ) {
					$forums->func->standard_error( 'erroruploadswf');
				}
				$real_name = $upload->parsed_file_name[0];
				if ( ! $bboptions['disableavatarsize'] AND $upload->file_extension != '.swf' ) {
					require_once( ROOT_PATH.'includes/functions_image.php' );
					$image = new functions_image();
					$image->filepath = $path;
					$image->filename = $real_name;
					$image->thumb_filename  = 'thumb_'.$bbuserinfo['id'];
					$image->thumbswidth  = $p_width;
					$image->thumbsheight = $p_height;
					$return = $image->generate_thumbnail();
					$im['img_width']  = $return['thumbwidth'];
					$im['img_height'] = $return['thumbheight'];
					if ( strstr( $return['thumblocation'], 'thumb_' ) ) {
						@unlink( $path."/".$real_name );
						$real_name = 'avatar-'.$bbuserinfo['id'].'.'.$image->file_extension;
						@rename( $path."/".$return['thumblocation'], $path."/".$real_name );
						@chmod(  $path."/".$real_name, 0777 );
					}
				} else {
					if ( ! $img_size = @GetImageSize( $path.'/'.$real_name ) ) {
						$img_size[0] = $p_width;
						$img_size[1] = $p_height;
					}
					$w = $img_size[0] ? intval($img_size[0]) : $p_width;
					$h = $img_size[1] ? intval($img_size[1]) : $p_height;
					$im['img_width']  = $w > $p_width  ? $p_width  : $w;
					$im['img_height'] = $h > $p_height ? $p_height : $h;
				}
				if ( @filesize( $path."/".$real_name ) > ($bboptions['avatamaxsize']*1024)) {
					@unlink( $path."/".$real_name );
					$forums->func->standard_error("uploadpasslimit");
				}
				$real_choice = $real_name;
				$real_dims   = $im['img_width'].'x'.$im['img_height'];
			} else {
				$forums->func->standard_error("selectupavatar");
			}
		} else {
			$_INPUT['avatarurl'] = trim($_INPUT['avatarurl']);
			if ( empty($bboptions['allowdynimg']) ) {
				if ( preg_match( "/[?&;]/", $_INPUT['avatarurl'] ) ) {
					$forums->func->standard_error("errorurl");
				}
			}
			$ext = explode ( ",", $bboptions['avatarextension'] );
			$checked = 0;
			$av_ext = preg_replace( "/^.*\.(\S+)$/", "\\1", $_INPUT['avatarurl'] );
			foreach ($ext AS $v ) {
				if (strtolower($v) == strtolower($av_ext)) {
					if ( ( $v == 'swf' ) AND (!$bboptions['allowflash']) ) {
						$forums->func->standard_error("erroruploadswf");
					}
					$checked = 1;
				}
			}
			if ($checked != 1) {
				$forums->func->standard_error("errorextension");
			}
			if ( ! $img_size = @GetImageSize( $_INPUT['avatarurl'] ) ) {
				$img_size[0] = $p_width;
				$img_size[1] = $p_height;
			}
			$im = array();
			if ( ! $bboptions['disableavatarsize'] ) {
				require_once( ROOT_PATH.'includes/functions_image.php' );
				$image = new functions_image();
				$im = $image->scale_image( array(
												'max_width'  => $p_width,
												'max_height' => $p_height,
												'cur_width'  => $img_size[0],
												'cur_height' => $img_size[1]
									   )      );
			} else {
				$w = $img_size[0] ? intval($img_size[0]) : $p_width;
				$h = $img_size[1] ? intval($img_size[1]) : $p_height;
				$im['img_width']  = $w > $p_width  ? $p_width  : $w;
				$im['img_height'] = $h > $p_height ? $p_height : $h;
			}
			$this->clean_avatars($bbuserinfo['id']);
			$real_choice = $_INPUT['avatarurl'];
			$real_dims   = $im['img_width'].'x'.$im['img_height'];
			$real_type   = 1;
		}
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET avatarlocation='".$real_choice."', avatarsize='".$real_dims."', avatartype='".$real_type."' WHERE id='".$bbuserinfo['id']."'" );
		$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=editavatar");
	}

	function do_forum_setting()
	{
		global $forums, $DB, $_INPUT, $_USEROPTIONS, $bbuserinfo, $bboptions;
		if ($_INPUT['posthash'] != $this->posthash ) {
			$forums->func->standard_error("badposthash");
		}
		$upstyle = '';
		$timezone = intval($_INPUT['u_timezone']);
		if ( $bboptions['allowselectstyles'] ) {
			$forums->func->check_cache('style');
			$styleid = intval($_INPUT['style']);
			$style = $forums->cache['style'][$styleid]['userselect'] ? $styleid : $bbuserinfo['style'];
			$upstyle = "style='".$style."', ";
		}
		$bbuserinfo['options'] = $forums->func->convert_array_to_bits($_INPUT['options'], $_USEROPTIONS);
		if ($bboptions['perpagepost'] == "") {
			$bboptions['perpagepost'] = '5,10,15,20,25,30,35,40';
		}
		if ($bboptions['perpagethread'] == "") {
			$bboptions['perpagethread'] = '5,10,15,20,25,30,35,40';
		}
		$bboptions['perpagepost']  .= ",-1,";
		$bboptions['perpagethread'] .= ",-1,";
		if (! preg_match( "/(^|,)".$_INPUT['postpage'].",/", $bboptions['perpagepost'] ) ) {
			$_INPUT['postpage'] = '-1';
		}
		if (! preg_match( "/(^|,)".$_INPUT['threadpage'].",/", $bboptions['perpagethread'] ) ) {
			$_INPUT['threadpage'] = '-1';
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET {$upstyle}timezoneoffset=".$_INPUT['u_timezone'].", options=".$bbuserinfo['options'].", viewprefs='".$_INPUT['threadpage']."&".$_INPUT['postpage']."' WHERE id=".$bbuserinfo['id']."");
		$forums->func->standard_redirect("usercp.php?{$forums->sessionurl}do=setting");
	}

	function do_change()
 	{
 		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$currentpassword = trim($_INPUT['currentpassword']);
 		$newpassword = trim($_INPUT['newpassword']);
 		$newpasswordconfirm = trim($_INPUT['newpasswordconfirm']);
		$email = $forums->func->strtolower( trim($_INPUT['email']) );
 		$emailconfirm = $forums->func->strtolower( trim($_INPUT['emailconfirm']) );
 		if ( $_INPUT['currentpassword'] == "" OR ( empty($newpassword) AND empty($email) ) ) {
 			$forums->func->standard_error("plzinputallform");
 		}
		$salt = $bbuserinfo['salt'];
		if ( $bbuserinfo['password'] != md5(md5($currentpassword) . $salt) ) {
			$forums->func->standard_error("errorcurrentpassword");
		}
		if (($email OR $emailconfirm ) AND $email != $emailconfirm) {
			$forums->func->standard_error("erroremailconfirm");
		}
 		if ( ( $newpasswordconfirm OR $newpasswordconfirm ) AND $newpassword != $newpasswordconfirm) {
 			$forums->func->standard_error("errorpassword");
 		}
		if ( $newpassword ) {
			$newpassword = md5(md5($newpassword) . $salt);
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET password='".$newpassword."' WHERE id=".$bbuserinfo['id']."" );
		}
		if ( $email ) {
			if ($bbuserinfo['usergroupid'] == 1) {
				$forums->func->standard_error("notactivation");
			}
			if (strlen($email) < 6) {
				$forums->func->standard_error('erroremail');
			}
			$email = $forums->func->clean_email($email);
			if ( $email == "" ) {
				$forums->func->standard_error("erroremail");
			}
			if ( $DB->query_first( "SELECT id FROM ".TABLE_PREFIX."user WHERE email='".$email."'" ) ) {
				$forums->func->standard_error("mailalreadyexist");
			}
			$DB->query("SELECT * FROM ".TABLE_PREFIX."banfilter WHERE type='email'");
			while( $r = $DB->fetch_array() ) {
				$banemail = $r['content'];
				$banemail = preg_replace( "/\*/", '.*' , $banemail );
				if ( preg_match( "/$banemail/", $email ) ) {
					$forums->func->standard_error("bademail");
				}
			}
			require_once(ROOT_PATH."includes/functions_email.php");
			$this->email = new functions_email();
			if ($bboptions['moderatememberstype'] AND !$bbuserinfo['cancontrolpanel'] ) {
				$activationkey = md5( $forums->func->make_password() . TIMENOW );
				$DB->query_unbuffered( "INSERT INTO ".TABLE_PREFIX."useractivation
											(useractivationid, userid, usergroupid, tempgroup, dateline, type, host)
										VALUES
											('".$activationkey."', '".$bbuserinfo['id']."', '".$bbuserinfo['usergroupid']."', 3, '".TIMENOW."', 3, '".$DB->escape_string(SESSION_HOST)."')"
									);
				$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET usergroupid=1, email='".$email."' WHERE id='".$bbuserinfo['id']."'" );
				if ( $forums->sessionid ) {
					$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."session SET username='', userid=0, usergroupid=1 WHERE userid=".$bbuserinfo['id']." AND id='".$forums->sessionid."'" );
				}
				$forums->func->set_cookie( 'password'  , '-1', 0 );
				$forums->func->set_cookie( 'userid'  , '-1', 0 );
				$forums->func->set_cookie( 'sessionid' , '-1', 0 );
				$message = $this->email->fetch_email_changeemail( array(
													'link'     => $bboptions['bburl']."/register.php?do=validate&type=newemail&u=".urlencode($bbuserinfo['id'])."&a=".urlencode($activationkey),
													'name'		=> $bbuserinfo['name'],
													'linkpage'	=> $bboptions['bburl']."/register.php?do=changeemail",
													'id'				=> $bbuserinfo['id'],
													'code'		=> $activationkey,
												  )
											);
				$this->email->build_message( $message );
				$forums->lang['emailchangetitle'] = sprintf( $forums->lang['emailchangetitle'], $bboptions['bbtitle'] );
				$this->email->subject = $forums->lang['emailchangetitle'];
				$this->email->to      = $email;
				$this->email->send_mail();
				$forums->func->redirect_screen( $forums->lang['redirectinfo'], "register.php?{$forums->sessionurl}do=changeemail" );
			} else {
				$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET email='".$email."' WHERE id=".$bbuserinfo['id']."" );
			}
		}
 		$forums->func->standard_redirect("usercp.php?".$forums->sessionurl);
 	}

	function parse_data( $thread )
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$last_time = $this->threadread[$thread['tid']] > $_INPUT['lastvisit'] ? $this->read_array[$thread['tid']] : $_INPUT['lastvisit'];
		$maxposts = $bboptions['maxposts'] ? $bboptions['maxposts'] : '10';
		if ( $thread['attach'] ) {
			$thread['attach_img'] = 1;
			$thread['alt_attach'] = $forums->lang['attachs'];
		}
		$thread['lastposter'] = $thread['lastposterid'] ? $forums->func->fetch_user_link( $thread['lastposter'], $thread['lastposterid']) : "-".$thread['lastposter']."-";
		$thread['postusername']     = $thread['postuserid'] ? $forums->func->fetch_user_link( $thread['postusername'], $thread['postuserid']) : "-".$thread['postusername']."-";
		if ($thread['pollstate']) {
			$thread['prefix']  = $bboptions['pollprefix'].' ';
		}
		$thread['foldericon'] = $forums->func->folder_icon( $thread, $last_time );
		$thread['dateline'] = $forums->func->get_date( $thread['dateline'], 2 );
		if ( $bbuserinfo['is_mod'] ) {
			$thread['post'] += intval($thread['modposts']);
		}
		$thread['showpages']  = $forums->func->build_threadpages(
												array( 'id'  => $thread['tid'],
														'totalpost'  => $thread['post'],
														'perpage'    => $maxposts,
													  )
										   );
		$thread['post']  = $forums->func->fetch_number_format( $thread['post'] );
		$thread['views']	 = $forums->func->fetch_number_format( $thread['views'] );
		$thread['lastpost']  = $forums->func->get_date( $thread['lastpost'], 1 );
		if ($thread['open'] == 2) {
			$t_array = explode("&", $thread['moved']);
			$thread['tid']       = $t_array[0];
			$thread['forumid']  = $t_array[1];
			$thread['title']     = $thread['title'];
			$thread['views']     = '--';
			$thread['post']     = '--';
			$thread['prefix']    = $bboptions['movedprefix']." ";
			$thread['gotonewpost'] = "";
		}
		return $thread;
	}

	function clean_avatars($id)
	{
		global $bboptions;
		foreach( array( 'swf', 'jpg', 'jpeg', 'gif', 'png' ) as $extension ) {
			if ( @file_exists( $bboptions['uploadfolder']."/avatar/avatar-".$id.".".$extension ) ) {
				@unlink( $bboptions['uploadfolder']."/avatar/avatar-".$id.".".$extension );
			}
		}
	}

	function clean_signature($id)
	{
		foreach( array( 'swf', 'jpg', 'jpeg', 'gif', 'png' ) as $extension ) {
			if ( @file_exists( ROOT_PATH."data/signature/sig-".$id.".".$extension ) ) {
				@unlink( ROOT_PATH."data/signature/sig-".$id.".".$extension );
			}
		}
	}
}

$output = new usercp();
$output->show();

?>