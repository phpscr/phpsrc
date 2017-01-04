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
define('THIS_SCRIPT', 'moderate');

require_once('./global.php');

class moderate
{
    var $moderator = array();
    var $forum = array();
    var $thread = array();
    var $tids = array();
    var $pids = array();
    var $uploadfolder  = "";
	var $recycleforum = 0;
	var $userecycle = 0;
	var $st = array();

    function show()
    {
        global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'moderate' );
		$forums->forum->forums_init();
        require_once(ROOT_PATH.'includes/xfunctions_bank.php');
		$this->bankfunc = new bankfunc();
        if (!in_array($_INPUT['do'], array('edituser', 'announcement', 'doannouncement', 'updateannouncement', 'deleteannouncement', 'findmember', 'dofindmember', 'money', 'domoney', 'massdomoney')) AND !empty($_INPUT['do'])) {
			if ($_INPUT['tid']) {
				foreach ($_INPUT['tid'] AS $key) {
					$key = intval($key);
					if ( empty($key) ) continue;
					$this->tids[] = $key;
				}
				if ( count ($this->tids) == 1) {
					$this->thread = $DB->query_first( "SELECT t.* FROM ".TABLE_PREFIX."thread t WHERE t.tid = ".implode("", $this->tids) );
				}
			}
			if ($_INPUT['t']) {
				$_INPUT['t'] = intval($_INPUT['t']);
				if ( ! $_INPUT['t'] ) {
					$forums->func->standard_error("erroraddress");
				} else {
					if ( ! $this->thread = $DB->query_first( "SELECT t.* FROM ".TABLE_PREFIX."thread t WHERE t.tid=".$_INPUT['t'] ) ) {
						$forums->func->standard_error("erroraddress");
					}
					$this->tids[] = $this->thread['tid'];
				}
			}
			if ( $_INPUT['pid'] ) {
				if ( is_array($_INPUT['pid']) ) {
					foreach ($_INPUT['pid'] AS $key) {
						$key = intval($key);
						if ( empty($key) ) continue;
						$this->pids[] = $key;
					}
				} else {
					$this->pids[]  = intval($_INPUT['pid']);
				}
			}
			$_INPUT['f'] = intval($_INPUT['f']);
			if ( ! $_INPUT['f'] && !$bbuserinfo['supermod']) {
				$forums->func->standard_error("erroraddress");
			}
			$_INPUT['pp'] = intval($_INPUT['pp']);
			$this->forum = $forums->forum->single_forum($_INPUT['f']);
			if ( $bbuserinfo['_moderator'][ $_INPUT['f'] ] ) {
				$this->moderator = $bbuserinfo['_moderator'][ $_INPUT['f'] ];
			}
        }
        require( ROOT_PATH.'includes/functions_moderate.php');
        $this->modfunc = new modfunctions();
        $this->modfunc->init($this->forum);
        $this->uploadfolder = $bboptions['uploadfolder'];
		$this->posthash = $forums->func->md5_check();
        if ($bboptions['enablerecyclebin'] AND $bboptions['recycleforumid']) {
        	if ($forums->cache['forum'][$bboptions['recycleforumid']]['allowposting']) {
        		if ( $bbuserinfo['cancontrolpanel'] ) {
        			$this->recycleforum = $bboptions['recycleforadmin'] ? $bboptions['recycleforumid'] : 0;
        		} else if ( $bbuserinfo['supermod'] ) {
        			$this->recycleforum = $bboptions['recycleforsuper'] ? $bboptions['recycleforumid'] : 0;
        		} else if ( $bbuserinfo['is_mod'] ) {
        			$this->recycleforum = $bboptions['recycleformod'] ? $bboptions['recycleforumid'] : 0;
        		} else {
        			$this->recycleforum = $bboptions['recycleforumid'];
        		}
        	}
        }
        switch ($_INPUT['do'])
        {
        	case 'edituser':
        		$this->edituser();
        		break;
        	case 'editpoll':
        		$this->editpoll();
        		break;
			case 'deletepoll':
        		$this->deletepoll();
        		break;
			case 'close':
        		$this->openclose('close');
        		break;
			case 'open':
        		$this->openclose('open');
        		break;
			case 'stick':
        		$this->stickunstick('stick');
        		break;
			case 'gstick':
        		$this->gstick();
        		break;
        	case 'unstick':
        		$this->stickunstick('unstick');
        		break;
			case 'approve':
        		$this->approveunapprove('approve');
        		break;
        	case 'unapprove':
        		$this->approveunapprove('unapprove');
        		break;
			case 'quintessence':
        		$this->quintessence('quintessence');
        		break;
        	case 'unquintessence':
        		$this->quintessence('unquintessence');
        		break;
			case 'move':
        		$this->movethread();
        		break;
			case 'domove':
        		$this->domove();
        		break;
			case 'merge':
				$this->mergethread();
				break;
			case 'domerge':
				$this->domerge();
				break;
			case 'delete':
        		$this->deletethread();
        		break;
			case 'editthread':
        		$this->editthread();
        		break;
        	case 'unsubscribe':
        		$this->unsubscribeall();
        		break;
			case 'movepost':
				$this->movepost();
        		break;
			case 'deletepost':
				$this->deletepost();
        		break;
			case 'splitthread':
				$this->splitthread();
        		break;
			case 'approvepost':
				$this->approvepost(1);
        		break;
			case 'unapprovepost':
				$this->approvepost(0);
        		break;
			case 'recount':
        		$this->recount($this->forum['id'], 1);
        		break;
			case 'announcement':
    			$this->announcement();
    			break;
			case 'doannouncement':
    			$this->announcementform();
    			break;
			case 'updateannouncement':
    			$this->updateannouncement();
    			break;
			case 'deleteannouncement':
    			$this->deleteannouncement();
    			break;
			case 'cleanmoveurl':
    			$this->cleanmoveurl();
    			break;
			case 'findmember':
				$this->findmember();
				break;
			case 'dofindmember':
				$this->dofindmember();
				break;
			case 'money':
				$this->money();
				break;
			case 'domoney':
				$this->domoney();
				break;
			case 'massdomoney':
				$this->massdomoney();
				break;
			case 'resetrep':
				$this->changerep(1);
				break;
			case 'repdec':
				$this->changerep();
				break;
			case 'cleanlog':
				$this->cleanlog();
				break;
			case 'postdomoney':
				$this->postdomoney();
				break;
			case 'specialtopic':
				$this->specialtopic();
				break;
			case 'dospecialtopic':
				$this->dospecialtopic();
				break;
			case 'unspecialtopic':
				$this->unspecialtopic();
				break;
        	default:
        		$this->announcement();
        		break;
        }
	}

	function moderate_log($action = 'Unknown')
	{
		global $_INPUT;
		$this->modfunc->add_moderate_log( $_INPUT['f'], $this->thread['tid'], $_INPUT['p'], $this->thread['title'], $action );
	}

	function thread_log($tids, $action = 'Unknown')
	{
		global $DB, $forums, $bbuserinfo;
		if(is_array($tids)) {
			$uptid = "tid IN (".implode(",", $tids).")";
		} elseif (count($tids)==1) {
			$uptid = "tid=".intval($tids);
		} else if (!$this->thread['tid']) {
			return;
		}
		$timenow = $forums->func->get_date(TIMENOW , 2);
		$threadlog = sprintf( $forums->lang['threadlog'], $bbuserinfo['name'], $timenow, $action );
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET logtext = '".addslashes($threadlog)."' WHERE {$uptid}");
	}

	function cleanlog()
	{
		global $DB, $forums, $bbuserinfo;
		if (!$bbuserinfo['supermod']) {
			$forums->func->standard_error("noperms");
		}
		if (!$this->thread['tid']) {
			$forums->func->standard_error("erroraddress");
		}
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET logtext = '' WHERE tid='".$this->thread['tid']."'");
		$forums->func->redirect_screen( $forums->lang['threadlogcleaned'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid']."&amp;pp=".$_INPUT['pp'] );
	}

	function recount($fid='', $redirect=0)
	{
		global $DB, $forums;
		$forumid = $fid ? $fid : $this->forum['id'];
		$this->modfunc->forum_recount( $forumid );
		$this->modfunc->stats_recount();
		if ($redirect) {
			$forums->func->standard_redirect( "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		}
	}

	function edituser()
	{
		global $DB, $forums, $_INPUT, $bbuserinfo, $bboptions;
		$userid = intval($_INPUT['u']);
		$posthash = $this->posthash;
		$passed = ($bbuserinfo['supermod'] OR $bbuserinfo['caneditusers']) ? TRUE : FALSE;
		if (empty($userid) OR !$passed) {
			$forums->func->standard_error("noperms");
		}
		require ( ROOT_PATH."includes/functions_codeparse.php");
        $parser = new functions_codeparse();
		if (!$user = $DB->query_first("SELECT u.*, s.lastactivity, s.inforum, s.inthread FROM ".TABLE_PREFIX."user u
					LEFT JOIN ".TABLE_PREFIX."session s ON (s.userid=u.id)
				WHERE u.id='".$userid."'"))
		{
			$forums->func->standard_error("cannotfindmember");
		}
		$ban = $forums->func->banned_detect( $user['liftban'] );
		if ( ! $_INPUT['update'] ) {
			if (!$bbuserinfo['cancontrolpanel']) {
				if ($user['cancontrolpanel']) {
					$forums->func->standard_error("userisadmin");
				}
			}
			if ($ban['timespan'] == -1) {
				$pchecked = ' checked="checked"';
			}
			$units = array( 0 => array( 'h', $forums->lang['hours'] ), 1 => array( 'd', $forums->lang['days'] ) );
			$bantype = "<select name='units'>\n";
			foreach ($units AS $v) {
				$selected = ( $v[0] == $user['liftban'] ) ? " selected='selected'" : "";
				$bantype .= "<option value='".$v[0]."'".$selected.">".$v[1]."</option>\n";
			}
			$bantype .= "</select>\n\n";
			if (preg_match ("#<!--sig_img--><div>(.+?)</div><!--sig_img1-->#", $user['signature'], $match) ) {
				$user['signature'] = preg_replace("#<!--sig_img-->(.+?)<!--sig_img1-->#", "", $user['signature']);
			}
			$sigimg = preg_replace('#<img[^>]+src=(\'|")./data/signature/(\S+?)(\\1).*>#siU', '\2', $match[1]);
			$issigimg = $sigimg ? 1 : 0;

			$user['signature']  = $parser->unconvert($user['signature'], $bboptions['signatureallowbbcode'], $bboptions['signatureallowhtml']);
			$pagetitle = $forums->lang['edituser']." - ".$bboptions['bbtitle'];
			$nav = array("<a href='profile.php?{$forums->sessionurl}u={$userid}'>".$forums->lang['userinfo']."</a>", $forums->lang['edituser']);
			include $forums->func->load_template('mod_edituser');
			exit;
		} else {
			if ( $user['usergroupid'] != 2 ) {
				if ( $user['supermod'] == 1 OR $user['usergroupid'] == 4 ) {
					$user['is_mod'] = 1;
				} else if ( @is_file( ROOT_PATH.'cache/cache/moderator_user_'.$user['id'].'.php' ) ) {
					$user['is_mod'] = 1;
				} else if ( @is_file( ROOT_PATH.'cache/cache/moderator_group_'.$user['usergroupid'].'.php' ) ) {
					$user['is_mod'] = 1;
				}
			}
			$post = $forums->func->htmlspecialchars_uni($_POST['signature']);
			$_INPUT['signature'] = $parser->convert(  array( 'text' => $forums->func->stripslashes_uni($post),
																	  'allowsmilies'   => 1,
																	  'allowcode'      => $bboptions['signatureallowbbcode'],
																	  'allowhtml'      => $bboptions['signatureallowhtml'],
															)       );
			if (preg_match("#<!--sig_img-->(.+?)<!--sig_img1-->#is", $user['signature'], $match)) {
				if ($_INPUT['sigimg']) {
					foreach( array( 'swf', 'jpg', 'jpeg', 'gif', 'png' ) as $extension ) {
						if ( @file_exists(ROOT_PATH . "data/signature/sig-".$user['id'].".".$extension ) ) {
							@unlink(ROOT_PATH . "data/signature/sig-".$user['id'].".".$extension );
						}
					}
				} else {
					$_INPUT['signature'] .= $match[0];
				}
			}
			if ($parser->error != "") {
				$forums->func->standard_error($parser->error);
			}
			$userinfo = array(  'website'	=> $_INPUT['website'],
							   'qq'			=> $_INPUT['qq'],
							   'skype'			=> $_INPUT['skype'],
							   'icq'			=> $_INPUT['icq'],
							   'aim'			=> $_INPUT['aim'],
							   'yahoo'		=> $_INPUT['yahoo'],
							   'msn'			=> $_INPUT['msn'],
							   'uc'			=> $_INPUT['uc'],
							   'popo'			=> $_INPUT['popo'],
							   'location'		=> $_INPUT['location'],
							   'signature'	=> $_INPUT['signature'],
							   'usergroupid' => $usergroupid,
							);
			if ( (!$_INPUT['timespan'] AND !$_INPUT['permanent']) OR $_INPUT['unban'] ) {
				$userinfo['liftban'] = "";
				$userinfo['usergroupid'] = $ban['groupid'] ? intval($ban['groupid']) : $user['usergroupid'];
			} else if ($_INPUT['permanent']) {
				$user['usergroupid'] = $ban['groupid'] ? intval($ban['groupid']) : $user['usergroupid'];
				$userinfo['liftban'] = $forums->func->banned_detect( array( 'timespan' => -1, 'unit' => $_INPUT['units'], 'groupid' => $user['usergroupid'], 'banuser' => $bbuserinfo['name']  ) );
				$userinfo['usergroupid'] = 5;
			} else {
				$userinfo['liftban'] = $forums->func->banned_detect( array( 'timespan' => intval($_INPUT['timespan']), 'unit' => $_INPUT['units'], 'groupid' => $user['usergroupid'], 'banuser' => $bbuserinfo['name']  ) );
				$userinfo['usergroupid'] = 5;
			}
			if ($user['is_mod'] AND $userinfo['liftban']) {
				$forums->func->standard_error("cannotbanuser");
			}
			if ($_INPUT['avatar']) {
				$userinfo['avatarlocation'] = "";
				$userinfo['avatarsize'] = "";
				foreach( array( 'swf', 'jpg', 'jpeg', 'gif', 'png' ) AS $ext ) {
					if ( @file_exists( $bboptions['uploadfolder']."/avatar/avatar-".$user['id'].".".$ext ) ) {
						@unlink( $bboptions['uploadfolder']."/avatar/avatar-".$user['id'].".".$ext );
					}
				}
			}
			$forums->func->fetch_query_sql( $userinfo, 'user', 'id='.$userid );
			$forums->func->standard_redirect("moderate.php?{$forums->sessionurl}do=edituser&amp;posthash={$posthash}&amp;u={$userid}");
		}
	}

	function editpoll()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$bboptions['maxpolloptions'] = $bboptions['maxpolloptions'] ? $bboptions['maxpolloptions'] : 10;
		$fid = $this->forum['id'];
		$tid = $this->thread['tid'];
		$t_title = $this->thread['title'];
		$posthash = $this->posthash;
		$passed = ($bbuserinfo['supermod'] OR $bbuserinfo['_moderator'][$fid]['caneditposts']) ? TRUE : FALSE;
		if (empty($tid) OR !$passed) {
			$forums->func->standard_error("noperms");
		}
        $poll_data = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."poll WHERE tid=".$tid."" );
        if (! $poll_data['pollid']) {
        	$forums->func->standard_error("cannotfindpolls");
        }
		$question = $poll_data['question'];
		$polloptions = unserialize($poll_data['options']);
		reset($polloptions);
		if ( ! $_INPUT['update'] ) {
			if ( count($polloptions) < $bboptions['maxpolloptions'] ) {
				for ( $i = count($polloptions) ; $i < $bboptions['maxpolloptions'] ; $i++ ) {
					$newpoll[] = $i;
				}
			}
			$poll_data['multipoll'] ? ($allowmulti = "selected='selected'") : ($disallowmulti = "selected='selected'");
			$pagetitle = $forums->lang['editpoll'].": ".$this->thread['title']." - ".$bboptions['bbtitle'];
			$nav = array ( "<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>",
								 "<a href='showthread.php?{$forums->sessionurl}t={$this->thread['tid']}'>{$this->thread['title']}</a>"
							   );
			include $forums->func->load_template('mod_editpoll');
			exit;
		} else {
			$newpolloptions = array();
			$ids            = array();
			$rearranged     = array();
			foreach ($_INPUT['poll'] AS $key => $value) {
				if ($value) {
					$ids[] = $key;
				}
			}
			foreach ($polloptions AS $entry) {
				$rearranged[ $entry[0] ] = array( $entry[0], $entry[1], $entry[2]);
			}
			$votetotal = 0;
			foreach( $ids AS $nid ) {
				if ( strlen($rearranged[ $nid ][1]) > 0 ) {
					$newpolloptions[] = array( $rearranged[ $nid ][0], $_INPUT['poll'][$nid], intval($_INPUT['votes'][$nid]) );
					$votetotal += intval($_INPUT['votes'][$nid]);
				} else {
					if ( strlen($_INPUT['poll'][$nid]) > 0 ) {
						$newpolloptions[] = array( $nid, $_INPUT['poll'][$nid], intval($_INPUT['votes'][$nid]) );
						$votetotal += intval($_INPUT['votes'][$nid]);
					}
				}
			}
			$poll_data['options'] = addslashes(serialize($newpolloptions));
			$multipoll = $_INPUT['multipoll'] ? 1 : 0;
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."poll SET votes=".$votetotal.", options='".addslashes(serialize($newpolloptions))."', question='".addslashes($_INPUT['question'])."', multipoll=".intval($multipoll)." WHERE tid=".$this->thread['tid']."" );
			$pollstate = $_INPUT['pollonly'] == 1 ? 2 : 1;
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."thread SET pollstate=".$pollstate." WHERE tid=".$this->thread['tid']."" );
			$forums->lang['editthreadpoll'] = sprintf( $forums->lang['editthreadpoll'], $this->thread['title'] );
			$this->moderate_log($forums->lang['editthreadpoll']);
			$forums->func->redirect_screen( $forums->lang['pollhasedited'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid']."&amp;pp=".$_INPUT['pp'] );
		}
	}

	function deletepoll()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
	    $fid = $this->forum['id'];
	    $passed = ($bbuserinfo['supermod'] OR $bbuserinfo['_moderator'][$fid]['candeletethreads']) ? TRUE : FALSE;
		if (empty($this->thread['tid']) OR !$passed) {
			$forums->func->standard_error("noperms");
		}
		$DB->query( "SELECT pollid FROM ".TABLE_PREFIX."poll WHERE tid=".$this->thread['tid'] );
        if (!$DB->num_rows() ) {
        	$forums->func->standard_error("cannotfinddelpolls");
        }
		$DB->shutdown_query( "DELETE FROM ".TABLE_PREFIX."poll WHERE tid=".$this->thread['tid']."" );
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."thread SET pollstate=0, lastvote='', votetotal='' WHERE tid=".$this->thread['tid']."" );
		$forums->lang['deletepoll'] = sprintf( $forums->lang['deletepoll'], $this->thread['title'] );
		$this->moderate_log($forums->lang['deletepoll']);
		$forums->func->redirect_screen( $forums->lang['pollhasdeleted'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid']."&amp;pp=".$_INPUT['pp'] );
	}

	function openclose($type='open')
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$passed = ($bbuserinfo['supermod'] OR ( $this->thread['postuserid'] == $bbuserinfo['id'] AND $bbuserinfo['canopenclose']) OR $this->moderator['canopenclose']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		if ($type == 'close') {
			$action = $forums->lang['closethread'];
			$operation = 0;
		} else if ($type == 'open') {
			$action = $forums->lang['openthread'];
			$operation = 1;
		}
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."thread SET open='".$operation."' WHERE tid IN(".implode(",",$this->tids).")" );
		if (count ($this->tids) > 1) {
			$this->moderate_log($action." - ".$forums->lang['threadid'].": ".implode(",",$this->tids));
		} else {
			$this->moderate_log($action." - ".$this->thread['title']);
		}
		if ($this->forum['id']) {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		} else {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php?do=show&searchid=".$_INPUT['searchid']."&searchin=".$_INPUT['searchin']."&showposts=".$_INPUT['showposts']."&highlight=".urlencode(trim($_INPUT['highlight'])));
		}
	}

	function stickunstick($type='stick')
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['canstickthread']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		if ($type == 'stick') {
			$action = $forums->lang['stickthread'];
			$sticky = 1;
		} else if ($type == 'unstick') {
			$action = $forums->lang['unstickthread'];
			$sticky = 0;
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET sticky='".$sticky."' WHERE tid IN(".implode(",",$this->tids).")" );
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$lib = new adminfunctions_cache();
		$lib->globalstick_recache();
		if (count ($this->tids) > 1) {
			$this->moderate_log($action." - ".$forums->lang['threadid'].": ".implode(",",$this->tids));
			$this->thread_log($this->tids, $action);
		} else {
			$this->moderate_log($action." - ".$this->thread['title']);
			$this->thread_log($this->thread['tid'], $action);
		}
		if ($this->forum['id']) {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		} else {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php?do=show&searchid=".$_INPUT['searchid']."&searchin=".$_INPUT['searchin']."&showposts=".$_INPUT['showposts']."&highlight=".urlencode(trim($_INPUT['highlight'])));
		}
	}

	function gstick()
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$passed = $bbuserinfo['supermod'] ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET sticky=2 WHERE tid IN(".implode(",",$this->tids).")" );
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$lib = new adminfunctions_cache();
		$lib->globalstick_recache();
		$action = $forums->lang['gstickthread'];
		if (count ($this->tids) > 1) {
			$this->moderate_log($action." - ".$forums->lang['threadid'].": ".implode(",",$this->tids));
			$this->thread_log($this->tids, $action);
		} else {
			$this->moderate_log($action." - ".$this->thread['title']);
			$this->thread_log($this->thread['tid'], $action);
		}
		if ($this->forum['id']) {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		} else {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php?do=show&searchid=".$_INPUT['searchid']."&searchin=".$_INPUT['searchin']."&showposts=".$_INPUT['showposts']."&highlight=".urlencode(trim($_INPUT['highlight'])));
		}
	}

	function approveunapprove($type='approve')
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['canmoderateposts']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
			return;
		}
		if ($type == 'approve') {
			$action = $forums->lang['approvethread'];
			$operation = 1;
		} else if ($type == 'unapprove') {
			$action = $forums->lang['unapprovethread'];
			$operation = 0;
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET visible='".$operation."' WHERE tid IN(".implode(",",$this->tids).")" );
		if (count ($this->tids) > 1) {
			$this->moderate_log($action." - ".$forums->lang['threadid'].": ".implode(",",$this->tids));
			$this->thread_log($this->tids, $action);
		} else {
			$this->moderate_log($action." - ".$this->thread['title']);
			$this->thread_log($this->thread['tid'], $action);
		}
		$this->recount( $this->forum['id'] );
		if ($this->forum['id']) {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		} else {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php?do=show&searchid=".$_INPUT['searchid']."&searchin=".$_INPUT['searchin']."&showposts=".$_INPUT['showposts']."&highlight=".urlencode(trim($_INPUT['highlight'])));
		}
	}

	function quintessence($type='quintessence')
	{
		global $forums, $DB, $bbuserinfo, $_INPUT, $bboptions;
		if (count($this->tids)==0 OR !$bbuserinfo['is_mod']) {
			$forums->func->standard_error("erroroperation");
			return;
		}
		if ($type == 'quintessence') {
			$action = $forums->lang['quintessencethread'];
			$operation = 1;
			$userquintessence = 'quintessence=quintessence+1';
		} else if ($type == 'unquintessence') {
			$action = $forums->lang['unquintessencethread'];
			$operation = 0;
			$userquintessence = 'quintessence=quintessence-1';
		}
		$forums->func->check_cache('banksettings');
		$banksettings = $forums->cache['banksettings'];
		$users = $DB->query("SELECT tid,title,postuserid,postusername,quintessence FROM ".TABLE_PREFIX."thread WHERE tid IN(".implode(",",$this->tids).")");
		if ($DB->num_rows($users)) {
			while ( $user = $DB->fetch_array($users) ) {
				if ($type == 'quintessence') {
					if ( $banksettings['bankquint'] AND !$user['quintessence'] ) {
						$this->bankfunc->desc = $forums->lang['makequintessence'];
						$this->bankfunc->add_money($user['postuserid'], $banksettings['bankquint']);
					}
					$_INPUT['title'] = $forums->lang['threadquintessenced'];
					$forums->lang['quintessenceinfo'] = sprintf( $forums->lang['quintessenceinfo'], $user['tid'], $user['title'], $bboptions['bbtitle'] );
					$_POST['post'] = $forums->lang['quintessenceinfo'];
					$_INPUT['username'] = $user['postusername'];
					require_once( ROOT_PATH.'includes/functions_private.php' );
					$pm = new functions_private();
					$_INPUT['noredirect'] = 1;
					$bboptions['usewysiwyg'] = 1;
					$bboptions['pmallowhtml'] = 1;
					$pm->sendpm();
				} else if($type == 'unquintessence') {
					if ( $banksettings['bankquint'] AND $user['quintessence'] ) {
						$this->bankfunc->desc = $forums->lang['makeunquintessence'];
						$this->bankfunc->add_money($user['postuserid'], intval("-".$banksettings['bankquint']));
					}
					$_INPUT['title'] = $forums->lang['threadunquintessenced'];
					$forums->lang['unquintessenceinfo'] = sprintf( $forums->lang['unquintessenceinfo'], $user['tid'], $user['title'], $bboptions['bbtitle'] );
					$_POST['post'] = $forums->lang['unquintessenceinfo'];
					$_INPUT['username'] = $user['postusername'];
					require_once( ROOT_PATH.'includes/functions_private.php' );
					$pm = new functions_private();
					$_INPUT['noredirect'] = 1;
					$bboptions['usewysiwyg'] = 1;
					$bboptions['pmallowhtml'] = 1;
					$pm->sendpm();
				}
				$allusers[] = $user['postuserid'];
			}
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET $userquintessence WHERE id IN (".implode("," , $allusers).")" );

			require_once(ROOT_PATH."includes/functions_credit.php");
			$this->credit = new functions_credit();
			if ($operation == 1) {
				$this->credit->update_credit('quintessence', $allusers);
			} else {
				$this->credit->update_credit('quintessence', $allusers, "-");
			}
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET quintessence='".$operation."' WHERE tid IN(".implode(",",$this->tids).")" );
		if (count ($this->tids) > 1) {
			$this->moderate_log($action." - ".$forums->lang['threadid'].": ".implode(",",$this->tids));
			$this->thread_log($this->tids, $action);
		} else {
			$this->moderate_log($action." - ".$this->thread['title']);
			$this->thread_log($this->thread['tid'], $action);
		}
		if ($this->forum['id']) {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		} else {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php?do=show&searchid=".$_INPUT['searchid']."&searchin=".$_INPUT['searchin']."&showposts=".$_INPUT['showposts']."&highlight=".urlencode(trim($_INPUT['highlight'])));
		}
	}

	function cleanmoveurl()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['canremoveposts']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed ) {
			$forums->func->standard_error("erroroperation");
		}
		foreach ($this->tids AS $tid) {
			if( $cleanid = $DB->query_first( "SELECT tid FROM ".TABLE_PREFIX."thread WHERE moved LIKE '".$tid."&%'" ) ) {
				$thread[] = $cleanid['tid'];
			}
		}
		if (is_array($thread)) {
			$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."thread WHERE tid IN (".implode(",", $thread).")" );
			$this->recount( $this->forum['id'] );
		}
		if ($this->forum['id']) {
			$forums->func->redirect_screen( $forums->lang['hascleaned'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		} else {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php?do=show&searchid=".$_INPUT['searchid']."&searchin=".$_INPUT['searchin']."&showposts=".$_INPUT['showposts']."&highlight=".urlencode(trim($_INPUT['highlight'])));
		}
	}

	function movethread()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		$fid = $this->forum['id'];
		$fname = $this->forum['name'];
		$posthash = $this->posthash;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['canremoveposts']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR $passed != 1) {
			$forums->func->standard_error("erroroperation");
		}
		$thread = array();
		$desc_forum = $forums->func->construct_forum_jump(0,0);
		if (count ($this->tids) > 1) {
			$DB->query( "SELECT title, tid FROM ".TABLE_PREFIX."thread WHERE tid IN(".implode(",", $this->tids).")" );
			while( $row = $DB->fetch_array() ) {
				$thread[] = $row;
			}
			$pagetitle = $forums->lang['batchmove']." - ".$bboptions['bbtitle'];
			$nav = array ( "<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>" );
		} else {
			$tid = $this->thread['tid'];
			$t_title = $this->thread['title'];
			$thread[] = $this->thread;
			$pagetitle = $forums->lang['movethread'].": ".$t_title." - ".$bboptions['bbtitle'];
			$nav = array ( "<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>",
								 "<a href='showthread.php?{$forums->sessionurl}t={$this->thread['tid']}'>{$this->thread['title']}</a>"
							   );
		}
		$forums->lang['movethreadto'] = sprintf( $forums->lang['movethreadto'], $this->forum['name'], $forum['name'] );
		$forums->lang['moveallthreadto'] = sprintf( $forums->lang['moveallthreadto'], $fname );
		include $forums->func->load_template('mod_move');
		exit;
	}

	function domove()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['canremoveposts']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed ) {
			$forums->func->standard_error("erroroperation");
		}
		$dest_id = intval($_INPUT['move_id']);
		$source_id = $this->forum['id'];
		$_INPUT['leave'] = $_INPUT['leave'] == '1' ? 1 : 0;
		if ($source_id == "") {
			$forums->func->standard_error("cannotfindsource");
		}
		if ($dest_id == "" OR $dest_id == -1) {
			$forums->func->standard_error("cannotfindtarget");
		}
		if ($source_id == $dest_id) {
			$forums->func->standard_error("notsamesource");
		}
		if (!$forum = $DB->query_first( "SELECT id, allowposting, name FROM ".TABLE_PREFIX."forum WHERE id=".$dest_id."" ) ) {
			$forums->func->standard_error("cannotfindtarget");
		}
		if ( $forum['allowposting'] != 1 ) {
			$forums->func->standard_error("cannotmove");
		}
		$this->modfunc->thread_move( $this->tids, $source_id, $dest_id, $_INPUT['leave'] );
		$this->recount($source_id);
		$this->recount($dest_id);
		$forums->lang['movethreadto'] = sprintf( $forums->lang['movethreadto'], $this->forum['name'], $forum['name'] );
		$this->moderate_log($forums->lang['movethreadto']);
		$this->thread_log($this->tids, $forums->lang['movethreadto']);
		if ($this->forum['id']) {
			$forums->func->redirect_screen( $forums->lang['hasmoved'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
		} else {
			$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php?do=show&searchid=".$_INPUT['searchid']."&searchin=".$_INPUT['searchin']."&showposts=".$_INPUT['showposts']."&highlight=".urlencode(trim($_INPUT['highlight'])));
		}
	}

	function specialtopic()
	{
		global $forums, $DB, $bbuserinfo, $bboptions, $_INPUT;
		$fid = $this->forum['id'];
		$posthash = $this->posthash;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['cansetst']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR $passed != 1) {
			$forums->func->standard_error("erroroperation");
		}
		if($this->forum['specialtopic']) {
			$forums->func->check_cache('st');
            $this->st = explode(",", $this->forum['specialtopic']);
            $specialtopic = '';
            foreach ($this->st as $id) {
                $specialtopic .= '<option value="'.$forums->cache['st'][$id]['id'].'">'.$forums->cache['st'][$id]['name'].'</option>';
            }
        }
		$thread = array();
		if (count($this->tids) > 1) {
			$DB->query("SELECT title, tid FROM ".TABLE_PREFIX."thread WHERE tid IN(".implode(",", $this->tids).")");
			while( $row = $DB->fetch_array() ) {
				$thread[] = $row;
			}
			$pagetitle = $forums->lang['batchsetspecialtopic']." - ".$bboptions['bbtitle'];
		} else {
			$tid = $this->thread['tid'];
			$t_title = $this->thread['title'];
			$thread[] = $this->thread;
			$pagetitle = $forums->lang['setspecialtopic'].": ".$t_title." - ".$bboptions['bbtitle'];
		}
		$nav = array ("<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>");
		$t_title .= '<input type="hidden" name="t" value="' . $_INPUT['t'] . '" />';
		include $forums->func->load_template('mod_specialtopic');
		exit;
	}

	function dospecialtopic()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['cansetst']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed ) {
			$forums->func->standard_error("erroroperation");
		}
		$st_id = intval($_INPUT['st_id']);
		if ($st_id == '' OR $st_id == -1) {
			$forums->func->standard_error("cannotfindst");
		}
		$this->st = explode(',', $this->forum['specialtopic']);
		if (!in_array($st_id, $this->st)) {
			$forums->func->standard_error('cannotsetst', $this->forum['name']);
		}
		$this->modfunc->thread_st($this->tids, $st_id);
		$forums->func->check_cache('st');
		$forums->lang['settospecialtopic'] = sprintf($forums->lang['settospecialtopic'], $forums->cache['st'][$st_id]['name']);
		$this->moderate_log($forums->lang['settospecialtopic']);
		$this->thread_log($this->tids, $forums->lang['settospecialtopic']);
		$forums->lang['hassettost'] = sprintf($forums->lang['hassettost'], $forums->cache['st'][$st_id]['name']);
		$forums->func->recache_cache('globalstick');
		$url = ($_INPUT['t']) ? "showthread.php?{$forums->sessionurl}t=".$_INPUT['t'] : "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'];
		$forums->func->redirect_screen( $forums->lang['hassettost'], $url);
	}

	function unspecialtopic()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$fid = $this->forum['id'];
		$posthash = $this->posthash;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['cansetst']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR $passed != 1) {
			$forums->func->standard_error("erroroperation");
		}
		$this->modfunc->thread_st($this->tids, 0);
		if (count ($this->tids) > 1) {
			$this->moderate_log($forums->lang['unsetspecialtopic']." - ".$forums->lang['threadid'].": ".implode(",",$this->tids));
			$this->thread_log($this->tids, $forums->lang['unsetspecialtopic']);
		} else {
			$this->moderate_log($forums->lang['unsetspecialtopic']." - ".$this->thread['title']);
			$this->thread_log($this->thread['tid'], $forums->lang['unsetspecialtopic']);
		}
		$forums->func->recache_cache('globalstick');
		$url = ($_INPUT['t']) ? "showthread.php?{$forums->sessionurl}t=".$_INPUT['t'] : "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'];
		$forums->func->redirect_screen($forums->lang['unsetspecialtopic'].$forums->lang['actioned'], $url);
		exit;
	}

	function mergethread()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		$fid = $this->forum['id'];
		$fname = $this->forum['name'];
		$posthash = $this->posthash;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['candeletethreads'] OR $this->moderator['canmergethreads']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		$thread = array();
		if (count ($this->tids) > 1) {
			$DB->query("SELECT * FROM ".TABLE_PREFIX."thread WHERE tid IN (".implode( ",",$this->tids ).") ORDER BY dateline ASC");
			while($row = $DB->fetch_array()) {
				$thread[] = $row;
			}
			$nav = array ( "<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>" );
		} else {
			$show['single'] = TRUE;
			$tid = $this->thread['tid'];
			$t_title = $this->thread['title'];
			$t_desc = $this->thread['description'];
			$thread[] = $this->thread;
			$nav = array ( "<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>",
								 "<a href='showthread.php?{$forums->sessionurl}t={$this->thread['tid']}'>{$this->thread['title']}</a>"
							   );
		}
		$pagetitle = $forums->lang['mergethread']." - ".$bboptions['bbtitle'];
		include $forums->func->load_template('mod_merge');
		exit;
	}

	function domerge()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['candeletethreads'] OR $this->moderator['canmergethreads']) ? TRUE : FALSE;
		$count = count($this->tids);
		if ($count==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		if (($count < 2 AND empty($_INPUT['threadurl'])) OR ($count == 1 AND $_INPUT['threadurl'] == '')) {
			$forums->func->standard_error("selectmerge");
		}
		if ($count < 2) {
			preg_match( "/(\?|&amp;)t=(\d+)($|&amp;)/", $_INPUT['threadurl'], $match );
			$old_id = intval(trim($match[2]));
			if ($old_id == "") {
				$forums->func->standard_error("cannotfindmerge");
			}

			if ( ! $old_thread = $DB->query_first( "SELECT tid, title, forumid, lastpost, lastposterid, lastposter, post, views FROM ".TABLE_PREFIX."thread WHERE tid='".intval($old_id)."'" ) ) {
				$forums->func->standard_error("cannotfindmerge");
			}
			$this->thread = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."thread WHERE tid IN (".implode(',',$this->tids).")");
			if ($old_id == $this->thread['tid']) {
				$forums->func->standard_error("mergenotsame");
			}
			$pass = FALSE;
			if ( $this->thread['forumid'] == $old_thread['forumid'] ) {
				$pass = TRUE;
			} else {
				if ( $bbuserinfo['supermod']) {
					$pass = TRUE;
				} else {
					$DB->query( "SELECT moderatorid FROM ".TABLE_PREFIX."moderator WHERE forumid=".$old_thread['forumid']." AND (userid='".$bbuserinfo['id']."' OR (isgroup=1 AND usergroupid='".$bbuserinfo['usergroupid']."'))" );
					if ( $DB->num_rows() ) {
						$pass = TRUE;
					}
				}
			}
			if ( $pass == FALSE ) {
				$forums->func->standard_error("cannotmerge");
			}
			$new_id = $this->thread['tid'];
			$new_title = $_INPUT['title'] ? $_INPUT['title'] : $this->thread['title'];
			$new_desc = $_INPUT['description'] ? $_INPUT['description'] : $this->thread['description'];
			$merge_ids[] = $old_thread['tid'];
			if ($this->thread['forumid'] != $old_thread['forumid']) {
				$this->recount( $old_thread['forumid'] );
			}
			$forums->lang['mergethreadto'] = sprintf( $forums->lang['mergethreadto'], $old_thread['title'], $new_title );
			$this->moderate_log($forums->lang['mergethreadto']);
		} else {
			$thread = array();
			$DB->query( "SELECT tid, title, description FROM ".TABLE_PREFIX."thread WHERE tid IN (".implode( ",",$this->tids ).") ORDER BY dateline ASC" );
			while ( $r = $DB->fetch_array() ) {
				$thread[] = $r;
			}
			unset($r);
			if ( count($thread) < 2 ) {
				$forums->func->standard_error("selectmerge");
			}
			$first_thread = array_shift( $thread );
			$new_id = $first_thread['tid'];
			$new_title = $_INPUT['title'] ? $_INPUT['title'] : $first_thread['title'];
			$new_desc = $_INPUT['description'] ? $_INPUT['description'] : $first_thread['description'];
			$merge_ids = array();
			foreach($thread as $t) {
				$merge_ids[] = $t['tid'];
			}
			$this->moderate_log($forums->lang['mergethread']." - ".$forums->lang['threadid'].": ".implode(",", $this->tids));
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET threadid='".$new_id."' WHERE threadid IN (".implode(',',$merge_ids).")" );
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET title='".addslashes($new_title)."', description='".addslashes($new_desc)."' WHERE tid=".$new_id."" );
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."poll WHERE tid IN (".implode(",",$merge_ids).")" );
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."subscribethread WHERE threadid IN (".implode(",",$merge_ids).")" );
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."thread WHERE tid IN (".implode(",",$merge_ids).")" );
		$this->modfunc->rebuild_thread($new_id);
		$this->recount($this->forum['id']);
		$forums->func->redirect_screen( $forums->lang['hasmerged'], "showthread.php?{$forums->sessionurl}t=".$new_id );
	}

	function deletethread()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['candeletethreads'] OR ($this->thread['postuserid'] == $bbuserinfo['id'] AND $bbuserinfo['candeletethread']) ) ? TRUE : FALSE;
		if ($_INPUT['threadid']) {
			$this->tids = array();
			$tids = explode(",", $_INPUT['threadid']);
			foreach ($tids AS $key) {
				$key = intval($key);
				if (!empty($key)) {
					$this->tids[] = $key;
				}
			}
		}
		if (count( $this->tids )==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		$fid = $this->forum['id'];
		if (!$_INPUT['update']) {
			$threadids = implode(",",$this->tids);
			$pagetitle = $forums->lang['deletethread']." - ".$bboptions['bbtitle'];
			$nav = array ( "<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>" );
			include $forums->func->load_template('mod_deletethread');
			exit;
		} else {
			foreach ($this->tids AS $link_thread) {
				$links = $DB->query( "SELECT tid, forumid FROM ".TABLE_PREFIX."thread WHERE open=2 AND moved LIKE '".$link_thread."&%'" );
				if ( $linked_thread = $DB->fetch_array($links) ) {
					$del_tids[] = $linked_thread['tid'];
					if (!$d_ceche[$linked_thread['forumid']]) {
						$d_ceche[$linked_thread['forumid']] = $linked_thread['forumid'];
					}
				}
			}
			if (is_array($del_tids)) {
				$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."thread WHERE tid IN (".implode(", ", $del_tids).")" );
				foreach ($d_ceche AS $forumid) {
					$this->recount($d_ceche);
				}
			}
			if($_INPUT['deletepmusers'] AND $this->recycleforum != $this->forum['id']) {
				$threads = $DB->query( "SELECT t.postuserid, t.tid, t.title, u.name FROM ".TABLE_PREFIX."thread t LEFT JOIN ".TABLE_PREFIX."user u ON (u.id=t.postuserid) WHERE tid IN(".implode(",",$this->tids).")" );
				while ($thread = $DB->fetch_array($threads)) {
					$delthread[$thread['postuserid']][] = $thread;
				}
				if (is_array($delthread)) {
					foreach ($delthread AS $userid => $delthreadinfo) {
						$deltitle = '';
						foreach($delthreadinfo AS $delinfo ) {
							$deltitle .= "<li>".$delinfo['title']."</li>\n";
						}
						$_INPUT['title'] = $forums->lang['yourthreaddeleted'];
						$forums->lang['yourthreaddeletedinfos'] = sprintf( $forums->lang['yourthreaddeletedinfo'], $deltitle, $_INPUT['deletereason'] );
						$_POST['post'] = $forums->lang['yourthreaddeletedinfos'];
						$_INPUT['username'] = $delinfo['name'];
						require_once( ROOT_PATH.'includes/functions_private.php' );
						$pm = new functions_private();
						$_INPUT['noredirect'] = 1;
						$bboptions['pmallowhtml'] = 1;
						$bboptions['usewysiwyg'] = 1;
						$pm->sendpm();
					}
				}
			}
			require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
			$lib = new adminfunctions_cache();
			if ( $this->recycleforum AND $this->recycleforum != $this->forum['id'] ) {
				$this->modfunc->thread_move($this->tids, $this->forum['id'], $this->recycleforum);
				$this->recount($this->forum['id']);
				$this->recount($this->recycleforum);
				$this->moderate_log($forums->lang['movetorecycle']);
				$this->thread_log($forums->lang['movetorecycle']);
				$lib->globalstick_recache();
				if ($this->forum['id']) {
					$forums->func->redirect_screen( $forums->lang['hastorecycle'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
				} else {
					$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php");
				}
			} else {
				$deletecashs = $_INPUT['deletecashs'] ? 1 : 0;
				$this->modfunc->thread_delete($this->tids, 0, $deletecashs);
				$this->recount( $this->forum['id'] );
				$this->moderate_log($forums->lang['deletethread']);
				$lib->globalstick_recache();
				if ($this->forum['id']) {
					$forums->func->redirect_screen( $forums->lang['hasdeleted'], "forumdisplay.php?{$forums->sessionurl}f=".$this->forum['id'] );
				} else {
					$forums->func->redirect_screen( $action.$forums->lang['actioned'], "search.php");
				}
			}
		}
	}

	function editthread()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$tid = $this->thread['tid'];
		$t_title = $this->thread['title'];
		$t_desc = $this->thread['description'];
		$fid = $this->forum['id'];
		$posthash = $this->posthash;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['caneditthreads']) ? TRUE : FALSE;
		if (count($this->tids)==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		if (!$_INPUT['update']) {
			$t_title = $forums->func->unhtmlspecialchars($t_title);
			if (preg_match( '#<strong>(.*)</strong>#siU', $t_title )) {
				$_INPUT['titlebold'] = 'checked="checked"';
				$t_title = preg_replace('#<strong>(.*)</strong>#siU', '\1', $t_title);
			}
			if (preg_match( '#<font[^>]+color=(\'|")(.*)(\\1)>(.*)</font>#esiU', $t_title )) {
				$_INPUT['titlecolor'] = preg_replace('#<font[^>]+color=(\'|")(.*)(\\1)>(.*)</font>#siU', '\\2', $t_title);
				$t_title = preg_replace('#<font[^>]+color=(\'|")(.*)(\\1)>(.*)</font>#siU', '\\4', $t_title);
			}
			$pagetitle = $forums->lang['editthread'].": ".$this->thread['title']." - ".$bboptions['bbtitle'];
			$nav = array ( "<a href='forumdisplay.php?{$forums->sessionurl}f={$this->forum['id']}'>{$this->forum['name']}</a>", "<a href='showthread.php?{$forums->sessionurl}t={$this->thread['tid']}'>{$this->thread['title']}</a>" );
			include $forums->func->load_template('mod_editthread');
			exit;
		} else {
			if ( trim($_INPUT['title']) == "") {
				$forums->func->standard_error("titlenotblank");
			}
			$title = preg_replace( "/'/", "/\\'/", $_INPUT['title'] );
			if ($_INPUT['titlecolor']) {
				$title = '<font color="'.$_INPUT['titlecolor'].'">'.$title.'</font>';
			}
			if ($_INPUT['titlebold']) {
				$title = '<strong>'.$title.'</strong>';
			}
			$description  = preg_replace( "/'/", "/\\'/", $_INPUT['description']  );
			$forums->func->fetch_query_sql( array( 'title' => $title, 'description' => $description ), 'thread', 'tid='.$this->thread['tid'] );
			if ($this->thread['tid'] == $this->forum['lastthreadid']) {
				$forums->func->fetch_query_sql(array('lastthread' => strip_tags($title)), 'forum', 'id='.$this->forum['id'] );
			}
			$forums->lang['changetitle'] = sprintf( $forums->lang['changetitle'], $this->thread['title'], $title );
			$this->moderate_log($forums->lang['changetitle']." ( ".$forums->lang['threadid'].": {$this->thread['tid']} )");
			$forums->func->redirect_screen( $forums->lang['hasedited'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid'] );
		}
	}

	function unsubscribeall()
	{
		global $forums, $DB, $bbuserinfo;
		if (!$bbuserinfo['supermod'] OR empty($this->thread['tid'])) {
			$forums->func->standard_error("noperms");
		}
		$DB->shutdown_query( "DELETE FROM ".TABLE_PREFIX."subscribethread WHERE threadid=".$this->thread['tid']."" );
		$forums->func->redirect_screen( $forums->lang['hasunsubscribe'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid'] );
	}

	function movepost()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$posthash = $this->posthash;
		$forumid = $this->forum['id'];
		$threadid = $this->thread['tid'];
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['canremoveposts']) ? TRUE : FALSE;
		if (count($this->pids)==0 OR !$this->thread['tid'] OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		require_once ( ROOT_PATH."includes/functions_codeparse.php");
        $this->parser = new functions_codeparse();
		if ( !$_INPUT['update'] ) {
			$DB->query( "SELECT pagetext, pid, dateline, userid, username FROM ".TABLE_PREFIX."post WHERE pid IN (".implode(",", $this->pids).") ORDER BY dateline");
			$post_count = 0;
			while ( $row = $DB->fetch_array() ) {
				if ( strlen($row['pagetext']) > 800 ) {
					$row['pagetext'] = $this->parser->unconvert($row['pagetext']);
					$row['pagetext'] = substr(strip_tags($row['pagetext']), 0, 800) . '...';
				}
				$row['date']   = $forums->func->get_date( $row['dateline'], 2 );
				$row['post_css'] = $post_count % 2 ? 'row1' : 'row2';
				$post_count++;
				$post[] = $row;
			}
			$pagetitle = $forums->lang['movepost']." - ".$bboptions['bbtitle'];
			$nav = array( "<a href='showthread.php?{$forums->sessionurl}t={$this->thread['tid']}'>{$this->thread['title']}</a>", $forums->lang['movepost'] );
			include $forums->func->load_template('mod_movepost');
			exit;
		} else {
			$affected_ids = count($this->pids);
			if ($affected_ids < 1) {
				$forums->func->standard_error("notselectmove");
			}
			if ( ! intval($_INPUT['threadurl']) ) {
				preg_match( "/(\?|&amp;)t=(\d+)($|&amp;)/", $_INPUT['threadurl'], $match );
				$old_id = intval(trim($match[2]));
			} else {
				$old_id = intval($_INPUT['threadurl']);
			}
			if ($old_id == "") {
				$forums->func->standard_error("erroraddress");
			}
			$move_to_thread = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."thread WHERE tid=".$old_id."" );
			if ( ! $move_to_thread['tid'] OR ! $forums->forum->foruminfo[ $move_to_thread['forumid'] ]['id'] ) {
				$forums->func->standard_error("erroraddress");
			}
			$count = $DB->query_first( "SELECT count(pid) as count FROM ".TABLE_PREFIX."post WHERE threadid=".$this->thread['tid']."" );
			if ( $affected_ids >= $count['count'] ) {
				$forums->func->standard_error("erroraddress");
			}
			$pids = implode( ",", $this->pids );
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET threadid=".$move_to_thread['tid'].", newthread=0 WHERE pid IN(".$pids.")" );
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET newthread=0 WHERE threadid=".$this->thread['tid']."" );
			$this->modfunc->rebuild_thread($move_to_thread['tid']);
			$this->modfunc->rebuild_thread($this->thread['tid']);
			$this->recount($this->thread['forumid']);
			if ($this->thread['forumid'] != $move_to_thread['forumid']) {
				$this->recount($move_to_thread['forumid']);
			}
			$forums->lang['movepostto'] = sprintf( $forums->lang['movepostto'], $this->thread['title'], $move_to_thread['title'] );
			$this->moderate_log($forums->lang['movepostto']);
			$forums->func->redirect_screen( $forums->lang['posthasmoved'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid'] );
		}
	}

	function deletepost()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$posthash = $this->posthash;
		$forumid = $this->forum['id'];
		$threadid = $this->thread['tid'];
		if (count($this->pids)==0) {
			$forums->func->standard_error("erroroperation");
		}
		if ($_INPUT['deltype'] == 'search' AND $bbuserinfo['supermod']) {
			$_INPUT['pid_t'] = (array) $_INPUT['pid_t'];
			$_INPUT['pid'] = (array) $_INPUT['pid'];
			$data_tid = array_intersect($_INPUT['pid_t'],$_INPUT['pid']);
			$data_pid = in_array($_INPUT['pid_t'],$_INPUT['pid']);
			if (is_array($data_tid) AND count($data_tid)>0) {
				foreach ($data_tid as $tmp_tid) {
					$del_tid[] = $_INPUT['tid'][$tmp_tid];
				}
				$data_fid = array_unique((array) $_INPUT['fid']);
				require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
				$lib = new adminfunctions_cache();
				if ($this->recycleforum && $forumid != $this->recycleforum) {
					$this->modfunc->thread_move($del_tid, '0', $this->recycleforum);
					foreach ($data_fid as $cont_fid) {
						$this->recount($cont_fid);
					}
					$this->recount($this->recycleforum);
					$this->moderate_log($forums->lang['movetorecycle']);
					$this->thread_log($forums->lang['movetorecycle']);
				} else {
					$deletecashs = $_INPUT['deletecashs'] ? 1 : 0;
					$this->modfunc->thread_delete($del_tid, 0, $deletecashs);
					foreach ($data_fid as $cont_fid) {
						$this->recount($cont_fid);
					}
					$this->moderate_log($forums->lang['deletethread']);
				}
				$lib->globalstick_recache();
			}
			if (is_array($_INPUT['pid']) AND count($_INPUT['pid'])>0) {
				foreach ($_INPUT['pid'] AS $tmp_pid) {
					if (in_array($tmp_pid,$_INPUT['pid_t'])) {
						$this->pids[] = $tmp_pid;
					}
				}
				$pids = implode(',', $this->pids);
				if ($this->recycleforum) {
					foreach ($this->pids as $tmp_tid) {
						$del_tid[] = $_INPUT['tid'][$tmp_tid];
					}
					$_INPUT['update'] = 1;
					$_INPUT['fid'] = $this->recycleforum;
					$_INPUT['title'] = $forums->lang['searchdeleted'];
					$_INPUT['description'] = $forums->lang['threadid'].": ".implode( ",", $del_tid );
					$newthread = array('title' => $_INPUT['title'],
						'description' => $_INPUT['description'] ,
						'open' => 1,
						'post' => 0,
						'postuserid' => 0,
						'postusername' => 0,
						'dateline' => TIMENOW,
						'lastposterid' => 0,
						'lastposter' => 0,
						'lastpost' => TIMENOW,
						'iconid' => 0,
						'pollstate' => 0,
						'lastvote' => 0,
						'views' => 0,
						'forumid' => $_INPUT['fid'],
						'visible' => 1,
						'sticky' => 0,
					);
					$forums->func->fetch_query_sql($newthread, 'thread');
					$threadid = $DB->insert_id();
					$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET threadid=".$threadid.", newthread=0 WHERE pid IN($pids)" );
					$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET moderate=0 WHERE threadid=$threadid" );
					$this->modfunc->rebuild_thread($threadid);
					foreach ($del_tid as $cont_tid) {
						$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET newthread=0 WHERE threadid={$cont_tid}" );
						$this->modfunc->rebuild_thread($cont_tid);
					}
					$data_fid = array_unique((array) $_INPUT['fid']);
					foreach ($data_fid as $cont_fid) {
						$this->recount($cont_fid);
					}
				} else {
					$this->modfunc->post_delete( $this->pids );
					$data_fid = array_unique((array) $_INPUT['fid']);
					foreach ($data_fid as $cont_fid) {
						$this->recount($cont_fid);
					}
				}
			}
			$userid = intval($_INPUT['userid']);
			$forums->func->redirect_screen( $forums->lang['posthasdeleted'], "search.php?do=finduser&u=$userid");
		}
		$post = $DB->query_first( "SELECT pid, userid, dateline, newthread FROM ".TABLE_PREFIX."post WHERE pid IN (".implode(",", $this->pids).") AND threadid=".$threadid."" );
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['candeleteposts'] OR ($bbuserinfo['candeletepost'] AND $bbuserinfo['id'] == $post['userid'] )) ? TRUE : FALSE;
		if (!$threadid OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}

		if ( ! $post['pid']  ) {
			$forums->func->standard_error("erroraddress");
		}
		foreach( $this->pids AS $p ) {
			if ( $this->thread['firstpostid'] == $p ) {
				$pagetitle = $forums->lang['deletethread'].": ".strip_tags($this->thread['title'])." - ".$bboptions['bbtitle'];
				$nav = array( "<a href='showthread.php?{$forums->sessionurl}t={$this->thread['tid']}'>{$this->thread['title']}</a>", $forums->lang['deletethread'] );
				include $forums->func->load_template('mod_delpost_one');
				exit;
			}
		}
		if ( $this->recycleforum AND $this->recycleforum != $this->forum['id'] ) {
			$_INPUT['update'] = 1;
			$_INPUT['fid'] = $this->recycleforum;
			$forums->lang['fromdeleted'] = sprintf( $forums->lang['fromdeleted'], $this->thread['title'] );
			$_INPUT['title'] = $forums->lang['fromdeleted'];
			$_INPUT['description'] = $forums->lang['threadid'].": ".$this->thread['tid'];
			$this->userecycle = 1;
			$this->splitthread();
			$this->userecycle = 0;
		} else {
			$this->modfunc->post_delete( $this->pids );
			$this->recount( $this->thread['forumid'] );
			$forums->func->redirect_screen( $forums->lang['posthasdeleted'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid'] );
		}
	}

	function splitthread()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$posthash = $this->posthash;
		$forumid = $this->forum['id'];
		$threadid = $this->thread['tid'];
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['cansplitthreads'] OR $this->userecycle) ? TRUE : FALSE;
		if (count($this->pids)==0 OR !$threadid OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		require_once(ROOT_PATH."includes/functions_codeparse.php");
        $this->parser = new functions_codeparse();
		if ( ! $_INPUT['update'] ) {
			$forum_jump = $forums->func->construct_forum_jump(0,1);
			$t_title = $this->thread['title'];
			$DB->query( "SELECT pagetext, pid, dateline, userid, username FROM ".TABLE_PREFIX."post WHERE pid IN (".implode(",", $this->pids).") ORDER BY dateline");
			$post_count = 0;
			while ( $row = $DB->fetch_array() ) {
				if ( strlen($row['pagetext']) > 800 ) {
					$row['pagetext'] = $this->parser->unconvert($row['pagetext']);
					$row['pagetext'] = substr($row['pagetext'], 0, 500) . '...';
				}
				$row['date'] = $forums->func->get_date( $row['dateline'], 2 );
				$row['post_css'] = $post_count % 2 ? 'row1' : 'row2';
				$post_count++;
				$post[] = $row;
			}
			$pagetitle = $forums->lang['splitthread'].": ".$this->thread['title']." - ".$bboptions['bbtitle'];
			$nav = array( "<a href='showthread.php?{$forums->sessionurl}t={$this->thread['tid']}'>{$this->thread['title']}</a>", $forums->lang['splitthread'] );
			include $forums->func->load_template('mod_splitthread');
			exit;
		} else {
			if ($_INPUT['title'] == "") {
				$forums->func->standard_error("plzinputallform");
			}
			$affected_ids = count($this->pids);
			if ($affected_ids < 1) {
				$forums->func->standard_error("notselectsplit");
			}
			$count = $DB->query_first( "SELECT count(pid) as cnt FROM ".TABLE_PREFIX."post WHERE threadid=".$this->thread['tid']."" );
			if ( $affected_ids >= $count['cnt'] ) {
				$forums->func->standard_error("notselectsplit");
			}
			$pids = implode( ",", $this->pids );
			$_INPUT['fid'] = intval($_INPUT['fid']);
			if ($_INPUT['fid'] != $this->forum['id']) {
				$forum = $forums->forum->single_forum( $_INPUT['fid'] );
				if ( ! $forum['id'] ) {
					$forums->func->standard_error("selectsplit");
				}
				if ($forum['allowposting'] != 1) {
					$forums->func->standard_error("cannotsplit");
				}
			}
			$newthread = array('title'					=> $_INPUT['title'],
										 'description'		=> $_INPUT['description'] ,
										 'open'				=> 1,
										 'post'				=> 0,
										 'postuserid'       => 0,
										 'postusername'	=> 0,
										 'dateline'			=> TIMENOW,
										 'lastposterid'		=> 0,
										 'lastposter'			=> 0,
										 'lastpost'			=> TIMENOW,
										 'iconid'				=> 0,
										 'pollstate'			=> 0,
										 'lastvote'			=> 0,
										 'views'				=> 0,
										 'forumid'			=> $_INPUT['fid'],
										 'visible'				=> 1,
										 'sticky'				=> 0,
									);
			$forums->func->fetch_query_sql($newthread, 'thread');
			$threadid = $DB->insert_id();
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET threadid=".$threadid.", newthread=0 WHERE pid IN($pids)" );
			if ( $this->userecycle ) {
				$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET moderate=0 WHERE threadid=$threadid" );
			}
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET newthread=0 WHERE threadid={$this->thread['tid']}" );
			$this->modfunc->rebuild_thread($threadid);
			$this->modfunc->rebuild_thread($this->thread['tid']);
			$this->recount($this->thread['forumid']);
			if ($this->thread['forumid'] != $_INPUT['fid']) {
				$this->recount($_INPUT['fid']);
			}
			if ( $this->userecycle ) {
				$forums->lang['movethreadtorecycle'] = sprintf( $forums->lang['movethreadtorecycle'], $this->thread['title'] );
				$this->moderate_log($forums->lang['movethreadtorecycle']);
				$forums->func->redirect_screen( $forums->lang['posthasdeleted'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid'] );
			} else {
				$this->moderate_log($forums->lang['splitthread']." '".$this->thread['title']."'");
				$forums->func->redirect_screen( $forums->lang['hassplited'], "showthread.php?{$forums->sessionurl}t=".$threadid );
			}
		}
	}

	function approvepost($type=1)
	{
		global $forums, $DB, $bbuserinfo;
		$passed = ($bbuserinfo['supermod'] OR $this->moderator['canmoderateposts']) ? TRUE : FALSE;
		if (count($this->pids)==0 OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		$at = 1;
		$ap   = 0;
		if ( $type != 1 ) {
			$at = 0;
			$ap = 1;
		}
		if ( in_array($this->thread['firstpostid'], $this->pids) ) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET visible=".$at." WHERE tid=".$this->thread['tid']."" );
			$tmp = $this->pids;
			$this->pids = array();
			foreach( $tmp AS $t ) {
				if ( $t != $this->thread['firstpostid'] ) {
					$this->pids[] = $t;
				}
			}
		}
		if ( count($this->pids) ) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET moderate=".$ap." WHERE pid IN (".implode(",", $this->pids).")" );
		}
		$this->modfunc->rebuild_thread( $this->thread['tid'] );
		$this->recount( $this->thread['forumid'] );
		$forums->func->redirect_screen( $forums->lang['hasapproved'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid'] );
	}

	function announcement()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		if (! $bbuserinfo['is_mod']) {
			$forums->func->standard_error("erroroperation");
		}
		$DB->query("SELECT a.*, u.name FROM ".TABLE_PREFIX."announcement a LEFT JOIN ".TABLE_PREFIX."user u on (a.userid=u.id) ORDER BY a.active, a.enddate DESC");
		$content = "";
		while ( $r = $DB->fetch_array() ) {
			$r['startdate'] = $r['startdate'] ? $forums->func->get_time($r['startdate'], 'Y-m-d') : '-';
			$r['enddate'] = $r['enddate'] ? $forums->func->get_time($r['enddate'], 'Y-m-d') : '-';
			if ( $r['forumid'] == -1 ) {
				$r['forumlist'] = $forums->lang['allforum'];
			} else {
				$tmp_forums = explode(",",$r['forumid']);
				if ( is_array( $tmp_forums ) AND count($tmp_forums) ) {
					$tmp2 = array();
					foreach( $tmp_forums AS $id ) {
						$tmp2[] = "<a href='forumdisplay.php?{$forums->sessionurl}f=".$id."' target='_blank'>".$forums->forum->foruminfo[ $id ]['name']."</a>";
					}
					$r['forumlist'] = implode( "<br />", $tmp2 );
				}
			}
			if (!$r['active']) {
				$r['inactive'] = "<span class='description'>".$forums->lang['noactive']."</span>";
			}
			$r['action'] = '';
			if ($bbuserinfo['id'] == $r['userid'] OR $bbuserinfo['supermod']) {
				$r['action'] = "[<a href='moderate.php?{$forums->sessionurl}do=doannouncement&amp;id=".$r['id']."'>".$forums->lang['edit']."</a>] [<a href='#' onclick='delete_post(\"moderate.php?{$forums->sessionurl}do=deleteannouncement&amp;id=".$r['id']."\"); return false;'>".$forums->lang['delete']."</a>]";
			}
			$announce[] = $r;
		}
		$pagetitle = $forums->lang['announcement']." - ".$bboptions['bbtitle'];
 		$nav = array( $forums->lang['announcement'] );
		include $forums->func->load_template('mod_announcement');
		exit;
	}

	function announcementform($errors='')
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		if (! $bbuserinfo['is_mod']) {
			$forums->func->standard_error("erroroperation");
		}
		$_INPUT['id'] = intval($_INPUT['id']);
		$forum_html = '';
		if ( $_INPUT['id'] ) {
			$button = $forums->lang['editannouncement'];
			if (!$announce = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."announcement WHERE id=".$_INPUT['id']."" )) {
				$forums->func->standard_error("cannotfindannounce");
			}
			if ($bbuserinfo['id'] != $announce['userid'] && !$bbuserinfo['supermod']) {
				$forums->func->standard_error("noperms");
			}
			$pagetitle = $forums->lang['editannouncement']." - ".$bboptions['bbtitle'];
			$nav = array( "<a href='moderate.php?{$forums->sessionurl}do=announcement'>".$forums->lang['announcement']."</a>", $forums->lang['editannouncement'] );
		} else {
			$button   = $forums->lang['addannouncement'];
			$announce = array( 'active' => 1 );
			$pagetitle = $forums->lang['addannouncement']." - ".$bboptions['bbtitle'];
			$nav = array( "<a href='moderate.php?{$forums->sessionurl}do=announcement'>".$forums->lang['announcement']."</a>", $forums->lang['addannouncement'] );
		}
		require ( ROOT_PATH."includes/functions_codeparse.php");
		$parser = new functions_codeparse();
		$announce['title'] = $announce['title'] ? $parser->unconvert($announce['title']) : $_POST['title'];
		$announce['pagetext'] = $announce['pagetext'] ? $parser->unconvert($announce['pagetext'], 1, 1, 0) : $_POST['post'];
		$announce['pagetext'] = preg_replace("#<br.*>#siU", "\n", $announce['pagetext']);
		$announce['forumids'] = $announce['forumid'] ? explode( ",", $announce['forumid'] ) : $_POST['announceforum'];
		$announce['startdate'] = $announce['startdate'] ? $forums->func->get_time($announce['startdate'], 'Y-m-d') : ($_POST['startdate'] ? $_POST['startdate'] : $forums->func->get_time(time(), 'Y-m-d'));
		$announce['enddate']   = $announce['enddate'] ? $forums->func->get_time($announce['enddate'], 'Y-m-d') : ($_POST['enddate'] ? $_POST['enddate'] : $forums->func->get_time(time()+2592000, 'Y-m-d'));
		if ($bbuserinfo['supermod']) {
			$forum_html .= "<option value='-1'>".$forums->lang['allforum']."</option><optgroup label='-----------------------'>" . $forums->forum->forum_jump();
		}
		if (is_array($bbuserinfo['_moderator'])) {
			foreach ($bbuserinfo['_moderator'] AS $id => $value) {
				$forum_html .= "<option value='".$id."'>".$forums->forum->foruminfo[ $id ]['name']."</option>";
			}
		}
		if ( is_array( $announce['forumids'] ) AND count( $announce['forumids'] ) ) {
			foreach( $announce['forumids'] AS $f ) {
				$forum_html = preg_replace( '#<option[^>]+value=(\'|")('.$f.')(\\1)>#siU', "<option value='\\2' selected='selected'>", $forum_html );
			}
		}
		$forum_html .= "</optgroup>";
		$announce['active_checked'] = $announce['active'] ? 'checked="checked"' : '';
		$announce['allowhtml'] = $announce['allowhtml'] ? 'checked="checked"' : '';
		$announce['pagetext'] = $forums->func->my_br2nl( $announce['pagetext'] );
		include $forums->func->load_template('mod_newannouncement');
		exit;
	}

	function updateannouncement()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		if (! $bbuserinfo['is_mod']) {
			$forums->func->standard_error("erroroperation");
		}
		if ( ! $_INPUT['title'] OR ! $_INPUT['post'] ) {
			return $this->announcementform( $forums->lang['requireannouncement'] );
		}
		$forumids = '';
		if ( is_array( $_INPUT['announceforum'] ) AND count( $_INPUT['announceforum'] ) ) {
			if ( in_array( '-1', $_INPUT['announceforum'] ) AND $bbuserinfo['supermod'] ) {
				$forumids = '-1';
			} else {
				if ($bbuserinfo['supermod']) {
					$forumids = implode( ",", $_INPUT['announceforum'] );
				} else {
					foreach ($bbuserinfo['_moderator'] AS $id => $value) {
						if (in_array($id, $_INPUT['announceforum'])) {
							$ids[] = $id;
						}
					}
					$forumids = implode( ",", $ids );
				}
			}
		}
		if (empty($forumids)) {
			return $this->announcementform( $forums->lang['selectforum'] );
		}
		$startdate = 0;
		$enddate   = 0;
		if ( strstr( $_INPUT['startdate'], '-' ) ) {
			$start_array = explode( '-', $_INPUT['startdate'] );
			if ( $start_array[0] AND $start_array[1] AND $start_array[2] ) {
				if ( ! checkdate( $start_array[1], $start_array[2], $start_array[0] ) ) {
					return $this->announcementform( $forums->lang['errorstartdate'] );
				}
			}
			$startdate = $forums->func->mk_time( 0, 0, 1, $start_array[1], $start_array[2], $start_array[0] );
		}
		if ( strstr( $_INPUT['enddate'], '-' ) ) {
			$end_array = explode( '-', $_INPUT['enddate']  );
			if ( $end_array[0] AND $end_array[1] AND $end_array[2] ) {
				if ( ! checkdate( $end_array[1], $end_array[2], $end_array[0] ) ) {
					return $this->announcementform( $forums->lang['errorenddate'] );
				}
			}
			$enddate = $forums->func->mk_time( 23, 59, 59, $end_array[1], $end_array[2], $end_array[0] );
		}
		require ( ROOT_PATH."includes/functions_codeparse.php");
        $parser = new functions_codeparse();
		$save_array = array( 'title'				=>  $parser->convert( array( 'text' => $forums->func->htmlspecialchars_uni($forums->func->stripslashes_uni($_POST['title'])),
																								'allowsmilies' => 0,
																								'allowcode' => 1,
																								'allowhtml' => 1
																							  ) ),
									 'pagetext'		=> $parser->convert( array( 'text' => $forums->func->htmlspecialchars_uni($forums->func->stripslashes_uni($_POST['post'])),
																								'allowsmilies' => $_INPUT['allowsmile'],
																								'allowcode' => $_INPUT['allowbbcode'],
																								'allowhtml' => $_INPUT['allowhtml']
																							  )
																					   ),
									 'active'			=> $_INPUT['active'],
									 'forumid'		=> $forumids,
									 'allowhtml'		=> intval($_INPUT['allowhtml']),
									 'startdate'		=> $startdate,
									 'enddate'		=> $enddate
								   );
		if ( !$_INPUT['id'] ) {
			$save_array['userid'] = $bbuserinfo['id'];
			$forums->func->fetch_query_sql( $save_array, 'announcement' );
		} else {
			$forums->func->fetch_query_sql( $save_array, 'announcement', 'id='.intval($_INPUT['id']) );
		}
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$announcement = new adminfunctions_cache();
		$announcement->announcement_recache();
		return $this->announcement();
	}

	function deleteannouncement()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		if (! $bbuserinfo['is_mod']) {
			$forums->func->standard_error("erroroperation");
		}
		$id = intval( $_INPUT['id'] );
		if (!$announce = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."announcement WHERE id=".$id."" )) {
			$forums->func->standard_error("cannotfindannounce");
		}
		if ($bbuserinfo['id'] != $announce['userid'] && !$bbuserinfo['supermod']) {
			$forums->func->standard_error("cannotdelannounce");
		}
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."announcement WHERE id=".$id."" );
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$announcement = new adminfunctions_cache();
		$announcement->announcement_recache();
		return $this->announcement();
	}

	function findmember()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		if (!$bbuserinfo['supermod'] && !$bbuserinfo['caneditusers']) {
			$forums->func->standard_error("erroroperation");
		}
		$pagetitle = $forums->lang['findmember']." - ".$bboptions['bbtitle'];
 		$nav = array( $forums->lang['findmember'] );
		include $forums->func->load_template('mod_findmember');
		exit;
	}

	function dofindmember()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$username = trim($_INPUT['username']);
		$first = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$passed = ($bbuserinfo['supermod'] OR $bbuserinfo['caneditusers']) ? TRUE : FALSE;
		if (empty($username) OR !$passed) {
			$forums->func->standard_error("erroroperation");
		}
		$count = $DB->query_first( "SELECT COUNT(*) AS counts FROM ".TABLE_PREFIX."user WHERE LOWER(name) LIKE concat('".$forums->func->strtolower($username)."','%') OR name LIKE concat('".$username."','%')" );
		if(!$count['counts']) {
			$forums->func->standard_error("cannotfindmember");
		}
		$pages = $forums->func->build_pagelinks( array( 'totalpages'  => $count['counts'],
												   'perpage'    => 20,
												   'curpage'  => $first,
												   'pagelink'    => "moderate.php?{$forums->sessionurl}do=dofindmember&amp;username=$username",
												 ) );
		$forums->func->check_cache('usergroup');
		$users = $DB->query( "SELECT name, id, host, posts, joindate, usergroupid FROM ".TABLE_PREFIX."user WHERE name LIKE '$username%' ORDER BY joindate DESC LIMIT $first, 20");
		while( $user = $DB->fetch_array($users) ) {
			$user['joindate']    = $forums->func->get_date( $user['joindate'], 3 );
			$user['grouptitle'] = $forums->cache['usergroup'][$user['usergroupid']]['opentag'].$forums->cache['usergroup'][$user['usergroupid']]['grouptitle'].$forums->cache['usergroup'][$user['usergroupid']]['closetag'];
			if ( $bbuserinfo['usergroupid'] != 4 AND $user['usergroupid'] == 4 ) {
				$user['host'] = '--';
			}
			$userlist[] = $user;
		}
		$pagetitle = $forums->lang['findmember']." - ".$bboptions['bbtitle'];
 		$nav = array( $forums->lang['findmember'] );
		include $forums->func->load_template('mod_showmember');
		exit;
	}

	function money()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		if (! $bbuserinfo['supermod']) {
			$forums->func->standard_error("erroroperation");
		}
		$forums->func->check_cache('usergroup');
		$usergroups = $forums->cache['usergroup'];
		$pagetitle = $forums->lang['changemoney']." - ".$bboptions['bbtitle'];
 		$nav = array( $forums->lang['changemoney'] );
		include $forums->func->load_template('mod_money');
		exit;
	}

	function domoney()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		if (! $bbuserinfo['supermod']) {
			$forums->func->standard_error("noperms");
		}
		$username = trim($_INPUT['username']);
		$username = addslashes(str_replace( '|', '&#124;', $username));
		$amount = trim($_INPUT['amount']);
		$this->bankfunc->desc = trim($_INPUT['desc']);
		if ( empty($username) || empty($amount) || empty($this->bankfunc->desc) )
			$forums->func->standard_error("plzinputallform");
		if ( !intval($amount) )
			$forums->func->standard_error("noamountmoney");
		if ( $username == $bbuserinfo['name'] )
			$forums->func->standard_error("cannottome");
		$userinfo = $DB->query_first("SELECT u.id, u.name, u.cash, u.bank, u.mkaccount
					      FROM ".TABLE_PREFIX."user u
					      WHERE LOWER(u.name)='".$forums->func->strtolower($username)."' OR u.name='".$username."'");
		if ( !$userinfo['id'] ) {
			$forums->func->standard_error("namenotexist");
		}
		if ( !$this->bankfunc->add_money($userinfo, $amount) ) {
			$forums->func->standard_error("handleerror");
		} else {
			$forums->func->check_cache('banksettings');
			$banksettings = $forums->cache['banksettings'];
			$msgamount = $amount;
			if ( $amount < 0 ) {
				$change = $forums->lang['reduce'];
				$msgamount = $msgamount * -1;
			} else {
				$change = $forums->lang['add'];
			}
			$forums->lang['moneyhaschange'] = sprintf( $forums->lang['moneyhaschange'], $userinfo['name'], $change, $msgamount, $banksettings['bankcurrency'] );
			$resultmsg = $forums->lang['moneyhaschange'];
		}
		$forums->lang['changelog'] = sprintf( $forums->lang['changelog'], $bbuserinfo['name'], $username, $change, $msgamount, $banksettings['bankcurrency'] );
		$this->moderate_log($forums->lang['changelog']);
		$forums->func->check_cache('usergroup');
		$usergroups = $forums->cache['usergroup'];
		$pagetitle = $forums->lang['changemoney']." - ".$bboptions['bbtitle'];
 		$nav = array( $forums->lang['changemoney'] );
		include $forums->func->load_template('mod_money');
		exit;
	}

	function massdomoney()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		if (! $bbuserinfo['supermod']) {
			$forums->func->standard_error("noperms");
		}
		$pagetitle = $forums->lang['changemoney']." - ".$bboptions['bbtitle'];
 		$nav = array( $forums->lang['changemoney'] );
		$_INPUT['usergroupid'] = intval($_INPUT['usergroupid']);
		$_INPUT['posts'] = intval($_INPUT['posts']);
		$_INPUT['quint'] = intval($_INPUT['quint']);
		$_INPUT['money'] = intval($_INPUT['money']);
		$_INPUT['regtime'] = trim($_INPUT['regtime']);
		if ( $bbuserinfo['usergroupid'] != 4 ) {
			$forums->func->standard_error("erroroperation");
		}
		if ( !intval($_INPUT['amount']) ) {
			$forums->func->standard_error("noamountmoney");
		}
		$usergroups = $forums->cache['usergroup'];
		$factors = FALSE;
		$sql_where = "usergroupid != 2 AND id !=".$bbuserinfo['id'];
		if ( $_INPUT['usergroupid'] != -1 ) {
			$factors = TRUE;
			$sql_where = $sql_where." AND usergroupid = ".$_INPUT['usergroupid'];
		}
		if ( $_INPUT['posts'] ) {
			$factors = TRUE;
			$sql_where = $sql_where." AND posts ";
			switch ( $_INPUT['poststype'] ) {
				case 2  :
					$sql_where .= "=";
					break;
				case -1 :
					$sql_where .= "<";
					break;
				default :
					$sql_where .= ">";
					break;
			}
			$sql_where = $sql_where." ".$_INPUT['posts'];
		}
		if ( $_INPUT['quint'] ) {
			$factors = TRUE;
			$sql_where = $sql_where." AND quintessence ";
			switch ( $_INPUT['quinttype'] ) {
				case 2  : $sql_where .= "="; break;
				case -1 : $sql_where .= "<"; break;
				default : $sql_where .= ">"; break;
			}
			$sql_where = $sql_where." ".$_INPUT['quint'];
		}
		if ( $_INPUT['money'] ) {
			$factors = TRUE;
			$sql_where = $sql_where." AND ";
			switch ( $_INPUT['moneyswitch'] ) {
				case 1  : $sql_where .= "cash "; break;
				case -1 : $sql_where .= "bank "; break;
				default : $sql_where .= "(cash+bank) "; break;
			}
			switch ( $_INPUT['moneytype'] ) {
				case 2  : $sql_where .= "="; break;
				case -1 : $sql_where .= "<"; break;
				default : $sql_where .= ">"; break;
			}
			$sql_where = $sql_where." ".$_INPUT['money'];
		}
		if ( $_INPUT['regtime'] ) {
			$timewhere = " AND joindate ";
			if ( $_INPUT['regtype'] == -1 )
				$timewhere .= "> ";
			else
				$timewhere .= "< ";
			if ( !preg_match("/(\d+)-(\d+)-(\d+)/i", $_INPUT['regtime']) ) {
				$forums->func->standard_error("errortimes");
			} else {
				$timearr = explode("-", $_INPUT['regtime']);
				@$datetimestamp = $forums->func->mk_time(23, 59, 59, $timearr[1], $timearr[2], $timearr[0]);
				if ( !$datetimestamp || strlen($datetimestamp) != 10 )
					$forums->func->standard_error("errortimes");
				else
					$timewhere .= $datetimestamp;
			}
			if ( $timewhere && strlen($timewhere) > 10 ) {
				$sql_where .= $timewhere;
				$factors = TRUE;
			}
		}
		if ( !$factors ) {
			$forums->func->standard_error("cannotfindedituser");
		}
		$idstodo = $DB->query("SELECT id FROM ".TABLE_PREFIX."user WHERE ".$sql_where);
		if ($DB->num_rows($idstodo) ) {
			$whereids = array();
			while ( $ids = $DB->fetch_array($idstodo) ) {
				$whereids[] = $ids['id'];
			}
			$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET cash=cash+".$_INPUT['amount']." WHERE id IN (".implode(",", $whereids).")" );
			$resultnum = count($whereids);
		} else {
			$forums->func->standard_error("cannotfindedituser");
		}
		$forums->func->check_cache('banksettings');
		$banksettings = $forums->cache['banksettings'];
		if ( $_INPUT['amount'] > 0 ) {
			$msgchange = $forums->lang['add'];
			$msgamount = $_INPUT['amount'];
		} else {
			$msgchange = $forums->lang['reduce'];
			$msgamount = -1*$_INPUT['amount'];
		}
		$forums->lang['batchaddmoney'] = sprintf( $forums->lang['batchaddmoney'], $resultnum, $msgchange, $msgamount, $banksettings['bankcurrency'] );
		$resultmassmsg = $forums->lang['batchaddmoney'];

		$forums->lang['masslog'] = sprintf( $forums->lang['masslog'], $bbuserinfo['name'], $resultnum, $msgchange, $msgamount, $banksettings['bankcurrency'] );
		$this->moderate_log($forums->lang['masslog']);
		include $forums->func->load_template('mod_money');
		exit;
	}

	function changerep($reset=0)
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$_INPUT['pid'] = intval($_INPUT['pid']);
		$tarinfo = $DB->query_first("SELECT p.reppost,p.threadid,u.id, u.name, u.usergroupid
					     FROM ".TABLE_PREFIX."post p
					     LEFT JOIN ".TABLE_PREFIX."user u ON (p.userid = u.id)
					     LEFT JOIN ".TABLE_PREFIX."usergroup g ON (g.usergroupid = u.usergroupid)
						WHERE p.pid = ".$_INPUT['pid']);
		if ( !$tarinfo['id'] ) {
			$forums->func->standard_error("cannotfindedituser");
		}
		if ( !preg_match("/,".$tarinfo['usergroupid'].",/i", ",".$bbuserinfo['canmodrep'].",") ) {
			$forums->func->standard_error("cannotrep");
		}
		if ($tarinfo['id'] == $bbuserinfo['id'] ) {
			$forums->func->standard_error("cannotrepself");
		}
		if ($reset) {
			$rr = unserialize($tarinfo['reppost']);
			$rep = '-'.intval($rr['number']);
			$change = $forums->lang['_reset'];
			$reputation = '';
		} else {
			if ($tarinfo['reppost'] ) {
				$forums->func->standard_error("posthasrep");
			}
			$rep = intval($_INPUT['rep']);
			if (!$rep ) {
				$forums->func->standard_error("requirerep");
			}
			$change = $rep > 0 ? $forums->lang['add'] : $forums->lang['reduce'];
			$reputation = addslashes(serialize(array('user' => $bbuserinfo['name'], 'number' => $rep)));
		}
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET reputation = reputation+".$rep." WHERE id = ".$tarinfo['id']." LIMIT 1");
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."post SET reppost = '".$reputation."' WHERE pid = ".$_INPUT['pid']."");
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET allrep = allrep+".$rep." WHERE tid = ".$tarinfo['threadid']."");
		$forums->lang['modrep'] = sprintf( $forums->lang['modrep'], $bbuserinfo['name'], $tarinfo['name'], $change );
		$this->moderate_log($forums->lang['modrep']);
		$forums->lang['hasreputation'] = sprintf( $forums->lang['hasreputation'], $change );
		$forums->func->redirect_screen( $forums->lang['hasreputation'], "showthread.php?{$forums->sessionurl}t=".$this->thread['tid']."&amp;pp=".$_INPUT['pp'] );
	}

	function postdomoney()
	{
		global $forums, $DB, $bbuserinfo, $_INPUT, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'showthread' );
		if ($_INPUT['uid'] == $bbuserinfo['id']) {
			$forums->func->standard_error('modrep_sameuserid_error');
		}
		if ($bbuserinfo['supermod'] OR $bbuserinfo['_moderator'][$_INPUT['f']]['forumid'] == $_INPUT['f']){
			if ( abs($_INPUT['cash']) > $bboptions['modrepmax'] ) {
				$forums->func->standard_error('modrep_limited_error');
			}
			$cashpost = $DB->query_first("SELECT cashpost FROM ".TABLE_PREFIX."post WHERE pid = '".intval($_INPUT['pid'])."'");
			$oldrecord = unserialize($cashpost['cashpost']);
			$forums->func->check_cache('banksettings');
			if ($_INPUT['cash']) {
				if ($cashpost['cashpost']) {
					$forums->func->standard_error('alreadycashed',0,$oldrecord['user']);
				}
				$cashpostrecord['user'] = $bbuserinfo['name'];
				$cashpostrecord['cashnum'] = $_INPUT['cash'];
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."post SET cashpost = '".serialize($cashpostrecord)."' WHERE pid = ".intval($_INPUT['pid'])."");
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET allcash = allcash+".intval($_INPUT['cash'])." WHERE tid = ".$_INPUT['t']."");
			} else {
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."post SET cashpost = '' WHERE pid = ".intval($_INPUT['pid'])."");
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET cash = cash - ".intval($oldrecord['cashnum'])." WHERE id = ".$_INPUT['uid']."");
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET allcash = allcash-".intval($oldrecord['cashnum'])." WHERE tid = ".$_INPUT['t']."");
				$forums->lang['cashcleanupsuc'] = sprintf($forums->lang['cashcleanupsuc'],$forums->cache['banksettings']['bankcurrency']);
				$forums->func->redirect_screen($forums->lang['cashcleanupsuc'],"showthread.php?{$forums->sessionurl}t=".$_INPUT['t']);
			}
			$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET cash = cash + ".intval($_INPUT['cash'])." WHERE id = ".$_INPUT['uid']."");
			$illuminate = "<a href='showthread.php?t=".$_INPUT['t']."'>".$forums->lang['banklog_thread']."</a>";
			$DB->shutdown_query("INSERT INTO ".TABLE_PREFIX."banklog (fromuserid, touserid, action, dateline) VALUES (".$bbuserinfo['id'].", ".$_INPUT['uid'].", '".addslashes($illuminate)."', ".TIMENOW.")");
			$forums->lang['send_cash_success'] = sprintf($forums->lang['send_cash_success'],$forums->cache['banksettings']['bankcurrency'],$_INPUT['cash']);
			$forums->func->redirect_screen($forums->lang['send_cash_success'],"showthread.php?{$forums->sessionurl}t=".$_INPUT['t']);
		}
	}
}

$output = new moderate();
$output->show();

?>
