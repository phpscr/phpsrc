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
define('THIS_SCRIPT', 'subscribe');
require_once('./global.php');

class subscribe {

    function show()
    {
        global $forums, $DB, $_INPUT, $bbuserinfo;
		$forums->lang = $forums->func->load_lang($forums->lang, 'subscribe' );
		$threadid = intval($_INPUT['t']);
        $forumid = intval($_INPUT['f']);
        $type = trim($_INPUT['type']);
		$this->thread = $forums->forum->single_forum( $forumid );
        if ($type != 'forum') {			
        	$row = $DB->query_first( "SELECT tid, forumid FROM ".TABLE_PREFIX."thread WHERE tid='".$threadid."'" );
        	$this->thread = array_merge($row, $this->thread);
        }
        if ( ! $this->thread['id'] ) {
        	$forums->func->standard_error("nosubscribe");
        }
        if ($type != 'forum' AND ! $this->thread['tid']) {
			$forums->func->standard_error("nosubscribe");
        }
        if (! $bbuserinfo['id'] ) {
        	$forums->func->standard_error("notlogin");
        }
        if ( $forums->func->fetch_permissions( $this->thread['canread'], 'canread' ) != TRUE ) {
			$forums->func->standard_error("cannotviewboard");
		}
		if ($this->thread['password'] != "") {
			if ( $this->thread['password'] != $forums->func->get_cookie('forum_'.$this->thread['fid']) ) {
				$forums->func->standard_error("cannotviewboard");
			}
		}
		if ($type == 'forum') {
			$DB->query( "SELECT subscribeforumid FROM ".TABLE_PREFIX."subscribeforum WHERE forumid='".$this->thread['id']."' AND userid='".$bbuserinfo['id']."'" );
		} else {
			$DB->query( "SELECT subscribethreadid FROM ".TABLE_PREFIX."subscribethread WHERE threadid='".$this->thread['tid']."' AND userid='".$bbuserinfo['id']."'" );
		}
		if ( $DB->num_rows() ) {
			$forums->func->standard_error("alreadysubscribe");
		}
		if ($type == 'forum') {
			$DB->shutdown_query("INSERT INTO ".TABLE_PREFIX."subscribeforum (userid, forumid, dateline) VALUES ('".$bbuserinfo['id']."', '".$forumid."', '".TIMENOW."')");
			$forums->func->redirect_screen( $forums->lang['subscribeforum'], "forumdisplay.php?{$forums->sessionurl}f=".$forumid."" );
		} else {
			$DB->shutdown_query("INSERT INTO ".TABLE_PREFIX."subscribethread (userid, threadid, dateline) VALUES ('".$bbuserinfo['id']."', '".$this->thread['tid']."', '".TIMENOW."')");
			$forums->func->redirect_screen( $forums->lang['subscribethread'], "showthread.php?{$forums->sessionurl}f=".$this->thread['id']."&amp;t=".$this->thread['tid']."&amp;pp=".$_INPUT['pp']."" );
		}
	}
}

$output = new subscribe();
$output->show();

?>