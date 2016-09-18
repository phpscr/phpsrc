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
define('THIS_SCRIPT', 'newthread');
require_once('./global.php');

class newthread
{
	var $posthash = '';
	var $post = array();
	var $thread = array();

	function show()
	{
		global $forums, $DB, $_INPUT;
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		$forums->forum->forums_init();
		require_once(ROOT_PATH.'includes/xfunctions_hide.php');
        $this->hidefunc = new hidefunc();
		$this->posthash = $_INPUT['posthash'] ? $_INPUT['posthash'] : md5(microtime());
		require_once(ROOT_PATH."includes/functions_credit.php");
		$this->credit = new functions_credit();
		require ROOT_PATH."includes/functions_post.php";
        $this->lib = new functions_post();
		$this->lib->dopost($this);
	}

	function showform()
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$this->check_permission();
		$forums->func->check_cache('usergroup');
		$forums->func->check_cache('credit');
		$usergrp = $forums->cache['usergroup'];
		$hidecredit = $forums->cache['credit']['list'];
        $hidetypes = $this->hidefunc->generate_hidetype_list(1);
		$title = isset($_INPUT['title']) ? trim($_INPUT['title']) : "";
		$description  = (isset($_INPUT['description']) && trim($_INPUT['description']))  ? $forums->func->fetch_trimmed_title(trim($_INPUT['description']), 80) : '';
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
		$form_start = $this->lib->fetch_post_form( array( 1 => array( 'do', 'update'),
																					  2 => array( 'posthash', $this->posthash ),
																		 ) );
		$forums->lang['threaddesc'] = sprintf( $forums->lang['threaddesc'], $this->lib->forum['name'] );
		$postdesc = $forums->lang['threaddesc'];
		$modoptions = $this->lib->modoptions();
		if ($this->lib->canupload) {
			$show['upload'] = TRUE;
			$upload = $this->lib->fetch_upload_form($this->posthash,'new');
		}
		$upload['maxnum'] = intval($bbuserinfo['attachnum']);
		if ($this->lib->forum['threadprefix']) {
			$prefix = explode("||", $this->lib->forum['threadprefix']);
			$threadprefix = '<select onchange="document.mxbform.title.focus(); document.mxbform.title.value = this.options[this.selectedIndex].value + document.mxbform.title.value;"><option selected value="">'.$forums->lang['type'].'</option>';
			foreach ($prefix AS $value) {
				$threadprefix .= '<option value="'.$value.'">'.$value.'</option>';
			}
			$threadprefix .= '</select>';
		}
		if($this->lib->forum['specialtopic']) {
			$forums->func->check_cache('st');
            $st = explode(",", $this->lib->forum['specialtopic']);
            $specialtopic = '<select name="specialtopic" class="bginput"><option selected value="">'.$forums->lang['selectspecialtopic'].'</option>';
            foreach ($st AS $id) {
                $specialtopic .= "<option value='".$forums->cache['st'][$id]['id']."'>".$forums->cache['st'][$id]['name']."</option>";
            }
            $specialtopic .= '</select>';
        }
		$credit_list = $this->credit->show_credit('newthread');
		$smiles = $this->lib->construct_smiles();
		$smile_count = $smiles['count'];
		$all_smiles = $smiles['all'];
		$smiles = $smiles['smiles'];
		$icons  = $this->lib->construct_icons();
		$checked = $this->lib->construct_checkboxes();
		$pagetitle = $forums->lang['newthread']." - ".$bboptions['bbtitle'];
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
		$forum = $this->lib->forum;
		$jsinclude['mxe'] = true;
		include $forums->func->load_template('newpost_wysiwyg');
		exit;
	}

	function process()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$this->check_permission();
		$this->credit->check_credit_limit('newthread');
		$this->post = $this->lib->compile_post();
		if ( $this->lib->forum['forcespecial'] AND isset($_INPUT['specialtopic']) AND $_INPUT['specialtopic'] == '' ) {
			$this->lib->obj['errors'] = $forums->lang['forcespecial'];
		}
		$_INPUT['title'] = trim($_INPUT['title']);
		if ( ($forums->func->strlen($_INPUT['title']) < 2) OR (!$_INPUT['title'])  ) {
			$this->lib->obj['errors'] = $forums->lang['musttitle'];
		}
		if ( strlen($forums->func->stripslashes_uni($_INPUT['title'])) > 250 ) {
			$this->lib->obj['errors'] = $forums->lang['titletoolong'];
		}
		$hidepostinfo = $this->hidefunc->check_hide_condition();
        if ( !$hidepostinfo ) {
            $this->post['hidepost'] = '';
        } else if ( is_string($hidepostinfo) && strlen($hidepostinfo) > 0 ) {
            $this->lib->obj['errors'] = $hidepostinfo;
        } else {
            $hidepostinfo = serialize($hidepostinfo);
            $this->post['hidepost'] = $hidepostinfo;
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
		$_INPUT['description'] = $forums->func->fetch_trimmed_title(trim($_INPUT['description']), 80);
		$_INPUT['description'] = $this->lib->parser->censoredwords( $_INPUT['description']  );
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
		$_INPUT['title'] = $this->lib->compile_title();
		if($bbuserinfo['cananonymous'] AND $_INPUT['anonymous']) {
			$useanonymous = ",postuserid=0,postusername='anonymous*'";
			$bbuserinfo['id'] = 0;
			$_INPUT['username'] = "anonymous*";
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
							  'iconid'				=> intval($_INPUT['iconid']),
							  'pollstate'			=> 0,
							  'lastvote'			=> 0,
							  'views'				=> 0,
							  'forumid'			=> $this->lib->forum['id'],
							  'visible'				=> ( $this->lib->obj['moderate'] == 1 || $this->lib->obj['moderate'] == 2 ) ? 0 : 1,
							  'sticky'				=> $sticky,
							  'stopic'				=> intval($_INPUT['specialtopic']),
							 );
		$forums->func->fetch_query_sql( $this->thread, 'thread' );
		$this->post['threadid']  = $DB->insert_id();
		$this->thread['tid'] = $this->post['threadid'];
		$this->post['posthash']  = $this->posthash;
		$this->post['newthread'] = 1;
		$this->post['moderate'] = 0;
		$forums->func->fetch_query_sql( $this->post, 'post' );
		$this->post['pid'] = $DB->insert_id();
		if($_INPUT['useblog'] AND $bbuserinfo['canblog']) {
			$this->blog = array(
								  'title'				=> $_INPUT['title'],
								  'userid'				=> $bbuserinfo['id'],
								  'private'			=> 2,
								  'dateline'			=> TIMENOW,
								  'category'			=> $_INPUT['blogcategory'],
								  'posthash'			=> $this->posthash,
								  'postid'				=> $this->post['pid'],
								 );
			$forums->func->fetch_query_sql( $this->blog, 'blog' );
			$this->blog['bid'] = $DB->insert_id();
			require_once (ROOT_PATH."includes/xfunctions_blog.php");
			$this->blogfunc = new functions_blog();
			$this->blogfunc->update_blogcache($bbuserinfo['id']);
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."userblog SET blogs=blogs+1 WHERE id=".$bbuserinfo['id']);
		}
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET firstpostid=".$this->post['pid'].",lastpostid=".$this->post['pid']."$useanonymous WHERE tid='".$this->thread['tid']."'" );
		$this->lib->stats_recount($this->thread['tid'], $this->thread['title'], 'new');
		$no_attachment = $this->lib->attachment_complete(array($this->posthash), $this->thread['tid'], $this->post['pid'], "", $this->blog['bid']);
		if($no_attachment AND $this->blog['bid']) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."blog SET attach=".intval($no_attachment)." WHERE bid='".$this->blog['bid']."'" );
		}
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
			$forums->lang['haspost'] = sprintf( $forums->lang['haspost'], $forums->lang['thread'] );
			$forums->func->redirect_screen( $forums->lang['haspost'], "forumdisplay.php?{$forums->sessionurl}&f=".$this->lib->forum['id']."" );
		}
		if ($_INPUT['redirect']) {
			$forums->func->standard_redirect("forumdisplay.php?{$forums->sessionurl}f=".$this->lib->forum['id']."");
		} else {
			$forums->func->standard_redirect("showthread.php?{$forums->sessionurl}t={$this->thread['tid']}");
		}
	}

	function check_permission()
	{
		global $forums, $bbuserinfo;
		$usercanpostnew = $forums->func->fetch_permissions($this->lib->forum['canstart'], 'canstart');
		if (!($bbuserinfo['canpostnew'] && $usercanpostnew)) {
			$forums->func->standard_error("cannotnewthread");
		}
	}
}

$output = new newthread();
$output->show();

?>