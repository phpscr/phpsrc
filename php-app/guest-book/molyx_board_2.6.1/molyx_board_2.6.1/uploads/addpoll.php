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
define('THIS_SCRIPT', 'addpoll');
require_once('./global.php');

class addpoll {

	var $posthash = '';
	var $thread = array();

	function show()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		$forums->forum->forums_init();
		$this->posthash = $_INPUT['posthash'] ? $_INPUT['posthash'] : md5(microtime());
		if (! $bbuserinfo['canpostpoll'])	{
			$forums->func->standard_error('cannotpostpoll');
		}
		$_INPUT['t'] = intval($_INPUT['t']);
		if ( ! $_INPUT['t'] ) {
			$forums->func->standard_error("threadnotexist");
		} else {
			if ( !$this->thread = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."thread WHERE tid='".$_INPUT['t']."'") ) {
				$forums->func->standard_error("threadnotexist");
			}
		}
		if ( $bbuserinfo['id'] ) {
			if ( $bbuserinfo['supermod'] ) {
				$pass = TRUE;
			} else if ( $this->thread['postuserid'] == $bbuserinfo['id'] ) {
				if ( ($bboptions['addpolltimeout'] > 0) AND ( $this->thread['dateline'] + ($bboptions['addpolltimeout'] * 3600) > TIMENOW ) ) {
					$pass = TRUE;
				}
			}
		}
		if ( !$pass ) {
			$forums->func->standard_error("cannotaddpoll");
		}
		$bboptions['maxpolloptions'] = $bboptions['maxpolloptions'] ? $bboptions['maxpolloptions'] : 10;
		require ROOT_PATH."includes/functions_post.php";
        $this->lib = new functions_post();
		$this->lib->dopost($this);
	}

	function showform()
	{
		global $forums, $_INPUT, $bboptions, $bbuserinfo;
		if ( $forums->func->fetch_permissions($this->lib->forum['canstart'], 'canstart') != TRUE ) {
			$forums->func->standard_error("cannotaddpoll");
		}
		if ($this->lib->obj['errors']) {
			$show['errors'] = TRUE;
			$errors = $this->lib->obj['errors'];
		}
		if ($bboptions['enablepolltags']) {
			$extra = $forums->lang['enablepolltags'];
		}
		$forums->lang['selectoptionsdesc'] = sprintf( $forums->lang['selectoptionsdesc'], $bboptions['maxpolloptions'] );
		$form_start = $this->lib->fetch_post_form( array( 1 => array( 'do', 'update' ),
														  			  2 => array( 'f', $this->lib->forum['id']),
														  			  3 => array( 't', $this->thread['tid']),
											     			 )      );
		$postdesc = $forums->lang['addpoll'].": ".$this->thread['title'];
		$pagetitle = $forums->lang['addpoll']." - ".$bboptions['bbtitle'];
		$nav = $this->lib->nav;
		include $forums->func->load_template('addpoll');
		exit;
	}

	function process()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		if ( ! $this->lib->forum['allowpoll'] ) {
			$forums->func->standard_error("cannotaddpoll");
		}
		if ( $forums->func->fetch_permissions($this->lib->forum['canstart'], 'canstart') != TRUE ) {
			$forums->func->standard_error("cannotaddpoll");
		}
		$polloptions  = array();
		$pcount = 0;
		$polls = explode( "<br />", $_INPUT['polloptions'] );
		if ( $bboptions['disablenoreplypoll'] != 1 ) {
			$pollstate = ($_INPUT['allow_disc'] == 0) ? 1 : 2;
		} else {
			$pollstate = 1;
		}
		$multipoll = $_INPUT['allowmultipoll'] ? 1 : 0;
		foreach ($polls AS $options) {
			if ( trim($options) == "" ) continue;
			$polloptions[] = array( $pcount , $this->lib->parser->censoredwords($options), 0 );
			$pcount++;
		}
		if ($pcount > $bboptions['maxpolloptions']) {
			$this->lib->obj['errors'] = $forums->lang['polltoomore'];
		}
		if ($pcount < 2) {
			$this->lib->obj['errors'] = $forums->lang['polltooless'];
		}
		if ( $this->lib->obj['errors'] != "" ) {
			return $this->showform();
		}
		$poll = array ('tid'			=> $this->thread['tid'],
							'forumid'		=> $this->lib->forum['id'],
							'dateline'	=> TIMENOW,
							'options'		=> serialize($polloptions),
							'votes'		=> 0,
							'question'	=> $this->lib->parser->censoredwords($_INPUT['question']),
							'multipoll'	=> $multipoll,
						);
		$forums->func->fetch_query_sql($poll, 'poll');
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."thread SET pollstate={$pollstate} WHERE tid='".$this->thread['tid']."'" );
		$forums->func->standard_redirect("showthread.php?{$forums->sessionurl}t=".$this->thread['tid']."");
	}
}

$output = new addpoll();
$output->show();
?>