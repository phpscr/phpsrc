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
define('THIS_SCRIPT', 'profile');

require_once('./global.php');

class profile {

	function show()
	{
    	global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions, $_USEROPTIONS;
		$forums->lang = $forums->func->load_lang($forums->lang, 'profile' );
    	$id = intval($_INPUT['u']);
		if ($bbuserinfo['canviewmember'] != 1 AND $id != $bbuserinfo['id']) {
 			$forums->func->standard_error("cannotviewthispage");
    	}
    	require ROOT_PATH."includes/functions_codeparse.php";
        $this->parser = new functions_codeparse();
        $this->parser->check_caches();
		$info = array();
    	if ( empty($id) ) {
    		$forums->func->standard_error("cannotfindedituser");
    	}
    	if ( ! $user = $DB->query_first("SELECT up.*, u.*, s.inforum, s.inthread, e.loanamount
					 FROM ".TABLE_PREFIX."user u
					 LEFT JOIN ".TABLE_PREFIX."session s ON (s.userid=u.id)
					 LEFT JOIN ".TABLE_PREFIX."userextra e ON (e.id=u.id)
					 LEFT JOIN ".TABLE_PREFIX."userexpand up ON (up.id=u.id)
					 WHERE u.id=".$id."" ) )
    	{
    		$forums->func->standard_error("cannotfindedituser");
    	}
    	$forumids = array('0');
    	foreach( $forums->forum->foruminfo AS $i => $r ) {
    		if ( $forums->func->fetch_permissions($r['canread'], 'canread') == TRUE ) {
    			$forumids[] = $r['id'];
    		}
    	}
    	$percent = 0;
    	$totalposts = $DB->query_first("SELECT COUNT(*) as totalposts FROM ".TABLE_PREFIX."post WHERE userid='".$user['id']."'");
		$forums->func->check_cache('stats');
    	$allposts = $forums->cache['stats']['totalposts'];
    	if ($user['posts'] AND $allposts) {
    		$info['perdaypost'] = round( $user['posts'] / (((TIMENOW - $user['joindate']) / 86400)), 1);
    		$info['totalpercent'] = sprintf( '%.2f', ( $user['posts'] / $allposts * 100 ) );
    	}
    	if ($info['perdaypost'] > $user['posts']) {
    		$info['perdaypost'] = $user['posts'];
    	}
    	$ranklevel = 0;
		$forums->func->check_cache('ranks');
		foreach($forums->cache['ranks'] AS $k => $v) {
			if ($user['posts'] >= $v['post']) {
				if (!$user['title']) {
					$user['title'] = $forums->cache['ranks'][ $k ]['title'];
				}
				$ranklevel = $v['ranklevel'];
				break;
			}
		}
		if($bboptions['openolrank']) {
			$forums->func->check_cache('olranks');
			$info['onlinerankimg'] = $forums->func->fetch_online_time(array('id' => $user['id'], 'onlinetime' => $user['onlinetime']));
		}
		$forums->func->check_cache("usergroup_{$user['usergroupid']}", 'usergroup');
		if ($forums->cache["usergroup_{$user['usergroupid']}"]['groupicon']) {
			$user['rank'] = 1;
			$user['rank_ext'] = $forums->cache["usergroup_{$user['usergroupid']}"]['groupicon'];
		} else {
			if ($ranklevel) {
				if ( preg_match( "/^\d+$/", $ranklevel ) ) {
					for ($i = 1; $i <= $ranklevel; ++$i) {
						$user['rank'] = 2;
						$user['rank_ext'][] = 1;
					}
				} else {
					$user['rank'] = 3;
					$user['rank_ext'] = $ranklevel;
				}
			}
		}
		$info['award'] = $user['award_data'] ? $forums->func->fetch_award($user['award_data']) : 0;
    	$info['posts'] = $user['posts'] ? $user['posts'] : 0;
    	$info['name'] = $user['name'];
    	$info['usertitle'] = $user['title'] ? $user['title'] : $forums->lang['noinfo'];
		$info['gender'] = $user['gender'];
		$info['rank'] = $user['rank'];
		$info['rank_ext'] = $user['rank_ext'];
    	$info['userid'] = $user['id'];
    	$info['grouptitle'] = $forums->cache["usergroup_{$user['usergroupid']}"]['grouptitle'];
    	$info['allposts'] = $allposts;
    	$info['joindate'] = $forums->func->get_date( $user['joindate'], 3 );
    	$info['lastactivity'] = $forums->func->get_date( $user['lastactivity'], 1 );
    	$info['qq'] = $user['qq'] ? $user['qq'] : $forums->lang['noinfo'];
    	$info['uc'] = $user['uc'] ? $user['uc'] : $forums->lang['noinfo'];
    	$info['popo'] = $user['popo'] ? $user['popo'] : $forums->lang['noinfo'];
    	$info['skype'] = $user['skype'] ? $user['skype'] : $forums->lang['noinfo'];
    	$info['aim'] = $user['aim']   ? $user['aim'] : $forums->lang['noinfo'];
    	$info['icq'] = $user['icq'] ? $user['icq'] : $forums->lang['noinfo'];
    	$info['yahoo'] = $user['yahoo'] ? $user['yahoo'] : $forums->lang['noinfo'];
    	$info['location'] = $user['location'] ? $user['location'] : $forums->lang['noinfo'];
    	$info['msn'] = $user['msn'] ? $user['msn'] : $forums->lang['noinfo'];
		$timelimit = TIMENOW - $bboptions['cookietimeout'] * 60;
		$info['status'] = 0;
		$info['extra'] = "( ".$forums->lang['offline']." )";
		$forums->lang['totalpercent'] = sprintf( $forums->lang['totalpercent'], $info['perdaypost'], $info['totalpercent'] );
		$forums->lang['userpercent'] = sprintf( $forums->lang['userpercent'], $info['activeposts'], $info['percent'] );
		$user['options'] = intval($user['options']);
		foreach($_USEROPTIONS AS $optionname => $optionval) {
			$user["$optionname"] = $user['options'] & $optionval ? 1 : 0;
		}
		if ( ( $user['lastvisit'] > $timelimit OR $user['lastactivity'] > $timelimit ) AND $user['invisible'] != 1 AND $user['loggedin'] == 1 ) {
			$info['status'] = 1;
			$where = "";
			if ( $user['inthread'] ) {
				$thread = $DB->query_first( "SELECT tid, title, forumid FROM ".TABLE_PREFIX."thread WHERE tid='".$user['inthread']."'" );
				if ( $thread['tid'] ) {
					$forums->func->check_cache('forum');
					if ( $forums->func->fetch_permissions($forums->cache['forum'][$thread['forumid']]['canread'], 'canread') == TRUE ) {
						$where = "( ".$forums->lang['readthread'].": <a href='showthread.php?{$forums->sessionurl}t=".$thread['tid']."'>".$thread['title']."</a> )";
					}
				}
			} else if ( $user['inforum'] ) {
				$forums->func->check_cache('forum');
				if ( $forums->func->fetch_permissions($forums->cache['forum'][$user['inforum']]['canread'], 'canread') == TRUE ) {
					$where = "( ".$forums->lang['viewforum'].": <a href='forumdisplay.php?{$forums->sessionurl}f=".$user['inforum']."'>".$forums->cache['forum'][$user['inforum']]['name']."</a> )";
				}
			}
			$info['extra'] = $where;
		}
    	$info['avatar'] = $forums->func->get_avatar( $user['avatarlocation'] , 1, $user['avatarsize'], $user['avatartype'] );
    	$info['signature']   = $user['signature'];
    	$this->parser->show_html = intval($bboptions['signatureallowhtml']);
		$info['signature'] = $this->parser->convert_text($info['signature']);
		$info['website'] = ( $user['website'] AND preg_match( "/^http:\/\/\S+$/", $user['website'] ) ) ? "<a href='".$user['website']."' target='_blank'>".$user['website']."</a>" : $forums->lang['noinfo'];
    	if ($user['birthday']) {
			$birthday = explode( '-', $user['birthday'] );
			$info['birthday'] =($birthday[0] == '0000') ? ($birthday[1]."-".$birthday[2]) : ($birthday[0]."-".$birthday[1]."-".$birthday[2]);
    	} else {
    		$info['birthday'] = $forums->lang['noinfo'];
    	}

		$info['pm'] = ( $user['usepm'] ) ? "<a href='private.php?{$forums->sessionurl}do=newpm&amp;u=".$user['id']."'><strong>".$forums->lang['sendpm']."</strong></a>" : $forums->lang['nousepm'];

		$info['email'] = ( ! $user['hideemail'] ) ? "<a href='sendmessage.php?{$forums->sessionurl}do=mailmember&amp;u=".$user['id']."'>".$forums->lang['sendmail']."</a>" : $forums->lang['nouseemail'];

    	$info['post'] = $forums->func->fetch_number_format($info['post']);

		$forums->func->check_cache('banksettings');
		$this->bankcurrency = $forums->cache['banksettings']['bankcurrency'];
		$info['cash'] = $forums->func->fetch_number_format($user['cash']);
		$info['cash'] .= $this->bankcurrency;
		if ( !$user['mkaccount'] ) {
			$info['bank'] = $forums->lang['noaccount'];
		} else if ( $user['loanamount'] ) {
			$info['bank'] = $forums->lang['hasloan'];
		} else {
			$info['bank'] = $forums->lang['normal'];
		}
		$info['reputation'] = $user['reputation'];

    	$posthash = $forums->func->md5_check();
    	if ($user['id'] == $bbuserinfo['id']) {
			$show['options'] = TRUE;
    	}
 		$pagetitle = $forums->lang['profile']." - ".$bboptions['bbtitle'];
 		$nav = array( $forums->lang['profile'] );
		include $forums->func->load_template('profile');
		exit;
 	}
}

$output = new profile();
$output->show();

?>