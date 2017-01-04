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
define('THIS_SCRIPT', 'newpoll');
require_once('./global.php');

class newpoll
{
	var $posthash = '';
	var $post = array();
	var $thread = array();
	var $pollcount = 0;

	function show()
	{
		global $forums, $_INPUT, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		$forums->forum->forums_init();
		require_once(ROOT_PATH.'includes/xfunctions_hide.php');
        $this->hidefunc = new hidefunc();
		$this->posthash = $_INPUT['posthash'] ? $_INPUT['posthash'] : md5(microtime());
		$bboptions['maxpolloptions'] = $bboptions['maxpolloptions'] ? $bboptions['maxpolloptions'] : 10;
		require_once(ROOT_PATH."includes/functions_credit.php");
		$this->credit = new functions_credit();
		switch ($_INPUT['do'])
		{
			case 'add':
				$this->addpoll();
				break;
			default:
				require ROOT_PATH."includes/functions_post.php";
				$this->lib = new functions_post();
				$this->lib->dopost($this);
				break;
		}
	}

	function showform()
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		if (! $this->lib->forum['allowpoll'] OR ! $bbuserinfo['canpostpoll']) {
			$forums->func->standard_error("cannotpostpoll");
		}
		if ( $forums->func->fetch_permissions($this->lib->forum['canstart'], 'canstart') != TRUE ) {
			$forums->func->standard_error("cannotpostpoll");
		}
		$forums->func->check_cache('usergroup');
		$usergrp = $forums->cache['usergroup'];
		$forums->func->check_cache('credit');
		$hidecredit = $forums->cache['credit']['list'];
        $hidetypes = $this->hidefunc->generate_hidetype_list();
		$title = isset($_INPUT['title']) ? $_INPUT['title'] : "";
		$description  = isset($_INPUT['description'])  ? $_INPUT['description']  : "";
		$this->cookie_mxeditor = $forums->func->get_cookie('mxeditor');
		if ( $this->cookie_mxeditor ) {
			$bbuserinfo['usewysiwyg'] = ($this->cookie_mxeditor == 'wysiwyg') ? 1 : 0;
		} elseif ($bboptions['mxemode']) {
			$bbuserinfo['usewysiwyg'] = 1;
		} else {
			$bbuserinfo['usewysiwyg'] = 0;
		}
		if ($_POST['post']) {
			$_POST['post'] = $forums->func->stripslashes_uni($_POST['post']);
			$content = $forums->func->htmlspecialchars_uni($_POST['post']);
		}
		$show['title'] = TRUE;
		if ($bboptions['enablepolltags']) {
			$extra = $forums->lang['enablepolltags'];
		}
		$poll = $forums->func->htmlspecialchars_uni($forums->func->stripslashes_uni($_POST['polloptions']));
		if ($this->lib->obj['errors']) {
			$show['errors'] = TRUE;
			$errors = $this->lib->obj['errors'];
		}
		if ($this->lib->moderator['caneditthreads'] OR $bbuserinfo['supermod']) {
			$show['colorpicker'] = TRUE;
		}
		if ($this->lib->obj['preview']) {
			$show['preview'] = TRUE;
			$preview = $this->lib->parser->convert_text( $this->post['pagetext'] );
		}
		$show['poll'] = TRUE;
		$form_start = $this->lib->fetch_post_form( array( 1 => array( 'do', 'update' ),
														  							  2 => array( 'f', $this->lib->forum['id']),
														  							  3 => array( 't', $this->thread['tid']),
																					  4 => array( 'posthash', $this->posthash ),
											     			 )      );
		$forums->lang['polldesc'] = sprintf( $forums->lang['polldesc'], $this->lib->forum['name'] );
		$postdesc = $forums->lang['polldesc'];
		$modoptions = $this->lib->modoptions();
		if ($this->lib->canupload) {
			$show['upload'] = TRUE;
			$upload = $this->lib->fetch_upload_form($this->posthash,'new');
		}
		$upload['maxnum'] = intval($bbuserinfo['attachnum']);
		if ($this->lib->forum['threadprefix']) {
			$prefix = explode("||", $this->lib->forum['threadprefix']);
			$threadprefix = "<select onchange='document.mxbform.title.focus(); document.mxbform.title.value = this.options[this.selectedIndex].value + document.mxbform.title.value;'>\r\n<option selected value=''>".$forums->lang['type']."</option>\r\n";
			foreach ($prefix AS $value) {
				$threadprefix .= "<option value='".$value."'>".$value."</option>\r\n";
			}
			$threadprefix .= "</select>\r\n";
		}
		$forums->lang['optionsdesc'] = sprintf( $forums->lang['optionsdesc'], $bboptions['maxpolloptions'] );

		$credit_list = $this->credit->show_credit('newthread');

		$smiles = $this->lib->construct_smiles();
		$smile_count = $smiles['count'];
		$all_smiles = $smiles['all'];
		$smiles = $smiles['smiles'];
		$icons  = $this->lib->construct_icons();
		$checked = $this->lib->construct_checkboxes();
		$pagetitle = $forums->lang['newpoll']." - ".$bboptions['bbtitle'];
		$nav = $this->lib->nav;
		$extrabuttons = $this->lib->code->construct_extrabuttons();
		if ($bboptions['isajax']) {
			$mxajax_request_type = "POST";
			$jsinclude['ajax'] = 1;
			$attachbutton = 'button'; $ajaxfunc = 'onclick="sendattach();"'; $previewfunc = 'onclick="previewpost('.$this->lib->forum['id'].');"';
		} else {
			$attachbutton = 'submit'; $ajaxfunc = "onclick='override=1;'"; $previewfunc = 'onclick="preview();"';
		}
		$iconname = 'post.gif';
		$antispam = $this->lib->code->showantispam();
		$jsinclude['mxe'] = true;
		include $forums->func->load_template('newpost_wysiwyg');
		exit;
	}

	function process()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		if ( ! $this->lib->forum['allowpoll'] ) {
			$forums->func->standard_error("cannotpostpoll");
		}
		if ( $forums->func->fetch_permissions($this->lib->forum['canstart'], 'canstart') != TRUE ) {
			$forums->func->standard_error("cannotpostpoll");
		}
		$this->post = $this->lib->compile_post();
		$hidepostinfo = $this->hidefunc->check_hide_condition();
        if ( !$hidepostinfo ) {
            $this->post['hidepost'] = '';
        } else if ( is_string($hidepostinfo) && strlen($hidepostinfo) > 0 ) {
            $this->lib->obj['errors'] = $hidepostinfo;
        } else {
            $hidepostinfo = serialize($hidepostinfo);
            $this->post['hidepost'] = $hidepostinfo;
        }
		if ( ($this->lib->obj['errors'] != "") OR ($this->lib->obj['preview'] != "") ) {
			return $this->showform();
		}
		$_INPUT['title'] = trim($_INPUT['title']);
		if ($forums->func->strlen($_INPUT['title']) < 2 OR !$_INPUT['title']  ) {
			$this->lib->obj['errors'] = $forums->lang['musttitle'];
		}
		if ( strlen(preg_replace("/&#([0-9]+);/", "-", $forums->func->stripslashes_uni($_INPUT['title']) ) ) > 80 ) {
			$this->lib->obj['errors'] = $forums->lang['titletoolong'];
		}
		$polloptions  = array();
		$count = 0;
		$polls = explode( "<br />", $_INPUT['polloptions'] );
		foreach ($polls AS $options) {
			if ( trim($options) == "" ) continue;
			$polloptions[] = array( $count , $this->lib->parser->censoredwords($options), 0 );
			$count++;
		}
		if ($count > $bboptions['maxpolloptions']) {
			$this->lib->obj['errors'] = $forums->lang['polltoomore'];
		}
		if ($count < 2) {
			$this->lib->obj['errors'] = $forums->lang['polltooless'];
		}
        if ($bboptions['useantispam']) {
			$antispam = $this->lib->validate_antispam();
			if (!$antispam) {
				$this->lib->obj['errors'] = $forums->lang['badimagehash'];
			}
		}
		if ( ($this->lib->obj['errors'] != "") OR ($this->lib->obj['preview'] != "") ) {
			return $this->showform();
		}

		$_INPUT['title'] = $this->lib->parser->censoredwords( $_INPUT['title'] );
		$_INPUT['description']  = $this->lib->parser->censoredwords( $_INPUT['description']  );
		if ( $bboptions['disablenoreplypoll'] != 1 ) {
			$pollstate = $_INPUT['allow_disc'] == 0 ? 1 : 2;
		} else {
			$pollstate = 1;
		}
		$multipoll = $_INPUT['allowmultipoll'] ? 1 : 0;
		$sticky = 0;
		$open  = 1;
		if ( isset($_INPUT['modoptions']) ) {
			switch ($_INPUT['modoptions']) {
				case 'gstick':
					$sticky = 2;
					$this->lib->moderate_log($forums->lang['gstickthread'].' - ', $_INPUT['title']);
					break;
				case 'stick':
					$sticky = 1;
					$this->lib->moderate_log($forums->lang['stickthread'].' - ', $_INPUT['title']);
					break;
				case 'close':
					if ($bbuserinfo['supermod'] OR $this->lib->moderator['canopenclose']) {
						$open = 0;
						$this->lib->moderate_log($forums->lang['closethread'].' - ', $_INPUT['title']);
					}
					break;
				case 'gstickclose':
					if ($bbuserinfo['supermod']) {
						$sticky = 2;
						$open = 0;
						$this->lib->moderate_log($forums->lang['gstickclose'].' - ', $_INPUT['title']);
					}
					break;
				case 'stickclose':
					if ($bbuserinfo['supermod'] OR ( $this->lib->moderator['canstickthread'] AND $this->lib->moderator['canopenclose'] ) ) {
						$sticky = 1;
						$open = 0;
						$this->lib->moderate_log($forums->lang['stickclose'].' - ', $_INPUT['title']);
					}
					break;
			}
		}
		if($bbuserinfo['cananonymous'] AND $_INPUT['anonymous']) {
			$useanonymous = ",postuserid=0,postusername='anonymous*'";
			$bbuserinfo['id'] = 0;
			$bbuserinfo['name'] = 'anonymous*';
		}
		$this->thread = array(
							  'title'				=> $_INPUT['title'],
							  'description'		=> $_INPUT['description'] ,
							  'open'				=> $open,
							  'post'				=> 0,
							  'postuserid'		=> $bbuserinfo['id'],
							  'postusername'	=> $bbuserinfo['id'] ?  $bbuserinfo['name'] : $_INPUT['username'],
							  'dateline'			=> TIMENOW,
							  'lastposterid'		=> $bbuserinfo['id'],
							  'lastposter'		=> $bbuserinfo['id'] ?  $bbuserinfo['name'] : $_INPUT['username'],
							  'lastpost'			=> TIMENOW,
							  'iconid'				=> $_INPUT['iconid'],
							  'pollstate'			=> $pollstate,
							  'lastvote'			=> 0,
							  'views'				=> 0,
							  'forumid'			=> $this->lib->forum['id'],
							  'visible'				=> $this->lib->obj['moderate'] ? 0 : 1,
							  'sticky'				=> $sticky,
							 );
		$forums->func->fetch_query_sql( $this->thread, 'thread'  );
		$this->post['threadid'] = $DB->insert_id();
		$this->thread['tid'] = $this->post['threadid'];
		$this->post['posthash']  = $this->posthash;
		$this->post['newthread'] = 1;
		if ( $this->lib->obj['moderate'] == 3 ) {
			$this->post['moderate'] = 0;
		}
		$forums->func->fetch_query_sql( $this->post, 'post' );
		$this->post['pid'] = $DB->insert_id();
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET firstpostid=".$this->post['pid'].",lastpostid=".$this->post['pid']."$useanonymous WHERE tid='".$this->thread['tid']."'" );
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."poll
									(tid, forumid, dateline, options, votes, question, multipoll)
								VALUES
									('".$this->thread['tid']."', '".$this->lib->forum['id']."', '".TIMENOW."', '".addslashes(serialize($polloptions))."', 0, '".addslashes($this->lib->parser->censoredwords($_INPUT['question']))."', ".$multipoll.")"
							);
		$this->lib->stats_recount($this->thread['tid'], $this->thread['title'], 'new');
		$this->lib->attachment_complete(array($this->posthash), $this->thread['tid'], $this->post['pid']);
		$this->lib->posts_recount();
		$forums->func->check_cache('banksettings');
		$banksettings = $forums->cache['banksettings'];
		if ( $banksettings['banknewthread'] OR $this->lib->forum['paypoints']) {
			require_once(ROOT_PATH.'includes/xfunctions_bank.php');
			$this->bankfunc = new bankfunc();
			$paypoints = explode("|", $this->lib->forum['paypoints']);
			$points = $paypoints[0] ? intval($paypoints[0]) : $banksettings['banknewthread'];
			$this->bankfunc->add_money($bbuserinfo['id'], $points);
		}
		if ($sticky == 2) {
			require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
			$lib = new adminfunctions_cache();
			$lib->globalstick_recache();
		}
		$this->credit->update_credit('newthread', $bbuserinfo['id']);
		if ( $this->lib->obj['moderate'] == 1 OR $this->lib->obj['moderate'] == 2 ) {
			$forums->lang['haspost'] = sprintf( $forums->lang['haspost'], $forums->lang['poll'] );
			$forums->func->redirect_screen( $forums->lang['haspost'], "forumdisplay.php?{$forums->sessionurl}&f=".$this->lib->forum['id']."" );
		}
		if ($_INPUT['redirect']) {
			$forums->func->standard_redirect("forumdisplay.php?{$forums->sessionurl}f={$this->lib->forum['id']}");
		} else {
			$forums->func->standard_redirect("showthread.php?{$forums->sessionurl}t={$this->thread['tid']}");
		}
	}

	function addpoll()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		if (! $bbuserinfo['canvote']) {
			$forums->func->standard_error("cannotvotepoll");
		}
		if (!$_INPUT['nullvote']) {
			if (! isset($_INPUT['poll_vote']) ) {
				$forums->func->standard_error("notselectpoll");
			}
		}
       	$_INPUT['t'] = intval($_INPUT['t']);
		if (! $_INPUT['t'] ) {
			$forums->func->standard_error("erroraddress");
		}
   		$this->thread = $DB->query_first("SELECT f.allowpollup, t.*, p.pollid,p.options,p.votes,p.voters
				FROM ".TABLE_PREFIX."poll p, ".TABLE_PREFIX."thread t, ".TABLE_PREFIX."forum f
				WHERE t.tid='{$_INPUT['t']}' AND p.tid=t.tid AND t.forumid=f.id");
   		if (! $this->thread['tid'] OR ! $this->thread['pollstate'] ) {
   			$forums->func->standard_error("erroraddress");
   		}
   		if ($this->thread['open'] != 1) {
   			$forums->func->standard_error("cannotvote");
   		}
		if(preg_match("#\,".$bbuserinfo['id']."\,#","\,".$this->thread['voters'])) {
			$forums->func->standard_error("alreadyvote");
		}
		$polloptions = unserialize($this->thread['options']);
		if (!$polloptions) {
			$polloptions = unserialize(stripslashes($this->thread['options']));
		}
       	reset($polloptions);
       	$newpolloptions = array();
       	foreach ($polloptions AS $entry) {
       		$id = $entry[0];
       		$choice = $entry[1];
       		$votes  = $entry[2];
			if( is_array($_INPUT['poll_vote'])) {
				if ( in_array ($id, $_INPUT['poll_vote']) ) {
					$votes++;
				}
			}
       		$newpolloptions[] = array( $id, $choice, $votes);
       	}
       	$this->thread['options'] = addslashes(serialize($newpolloptions));
		$this->thread['voters'] = $this->thread['voters'].$bbuserinfo['id'].',';
		$pollcount = 1 ;
        if (is_array($_INPUT['poll_vote'])) {
           $pollcount = count($_INPUT['poll_vote']);
        }
       	$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."poll SET votes=votes+$pollcount,options='{$this->thread['options']}',voters='{$this->thread['voters']}' WHERE pollid='{$this->thread['pollid']}'" );
       	if ($this->thread['allowpollup']) {
       		$this->thread['lastvote'] = TIMENOW;
       		$this->thread['lastpost'] = TIMENOW;
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."thread SET lastvote={$this->thread['lastvote']}, lastpost={$this->thread['lastpost']} WHERE tid={$this->thread['tid']}" );
       	} else {
       		$this->thread['lastvote'] = TIMENOW;
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."thread SET lastvote={$this->thread['lastvote']} WHERE tid={$this->thread['tid']}" );
       	}		$forums->func->standard_redirect("showthread.php?{$forums->sessionurl}f={$this->thread['forumid']}&amp;t={$this->thread['tid']}&amp;pp={$_INPUT['pp']}");
	}
}

$output = new newpoll();
$output->show();

?>