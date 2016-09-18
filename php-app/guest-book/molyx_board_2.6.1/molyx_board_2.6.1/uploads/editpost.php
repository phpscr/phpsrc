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
define('THIS_SCRIPT', 'editpost');
require_once('./global.php');

class editpost {
	var $getpost = '';
	var $thread = array();
	var $post = array();
	var $posthash = '';
	var $edittitle = '';
	var $moderator = array();

	function show()
	{
		global $forums, $DB, $_INPUT;
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		$forums->forum->forums_init();
		require_once(ROOT_PATH.'includes/xfunctions_hide.php');
        $this->hidefunc = new hidefunc();
		$this->getpost = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."post WHERE pid=".intval($_INPUT['p'])."" );
		if (! $this->getpost) {
			$forums->func->standard_error("cannoteditpost");
		}
		$this->thread = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."thread WHERE tid='".intval($this->getpost['threadid'])."'" );
		if ( ! $this->getpost['posthash'] ) {
			$this->posthash = md5(microtime());
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."post SET posthash='".$this->posthash."' WHERE pid='".$this->getpost['pid']."'" );
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."attachment SET posthash='".$this->posthash."' WHERE postid='".$this->getpost['pid']."'" );
		} else {
			$this->posthash = $this->getpost['posthash'];
		}
		require_once(ROOT_PATH."includes/functions_post.php");
        $this->lib = new functions_post();
		$this->lib->dopost($this);
	}

	function showform()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$this->fetch_permission();
		$forums->func->check_cache('usergroup');
		$usergrp = $forums->cache['usergroup'];
		$forums->func->check_cache('credit');
		$hidecredit = $forums->cache['credit']['list'];
        $hidetypes = $this->hidefunc->generate_hidetype_list();
        if ( $this->getpost['hidepost'] ) {
            $hideinfo = unserialize($this->getpost['hidepost']);
        } else {
            $hideinfo = array();
        }
		$this->cookie_mxeditor = $forums->func->get_cookie('mxeditor');
		if ( $this->cookie_mxeditor ) {
			$bbuserinfo['usewysiwyg'] = ($this->cookie_mxeditor == 'wysiwyg') ? 1 : 0;
		} elseif ($bboptions['mxemode']) {
			$bbuserinfo['usewysiwyg'] = 1;
		} else {
			$bbuserinfo['usewysiwyg'] = 0;
		}
		$this->getpost['pagetext'] = preg_replace("#<!--editpost-->(.+?)<!--editpost1-->#","",$this->getpost['pagetext']);
		if (!isset($_POST['post'])) {
			$_POST['post'] = $this->lib->parser->unconvert($this->getpost['pagetext'], $this->lib->forum['allowbbcode'], $this->lib->forum['allowhtml'], $bbuserinfo['usewysiwyg']);
		}
		if ($_POST['post']) {
			$content = $_POST['post'];
		}
		if (!$bbuserinfo['usewysiwyg']) {
			$content = preg_replace("#<br.*>#siU", "\n", $content);
		}
		if (isset($content)) {
			$content = $forums->func->init_post($content);
			$content = preg_replace("#&lt;!--editpost--&gt;(.+?)&lt;!--editpost1--&gt;#","",$content);
		}
		if($this->edittitle) {
			$show['title'] = TRUE;
			$title = isset($_INPUT['title']) ? $_INPUT['title'] : $this->thread['title'];
			$description  = isset($_INPUT['description'])  ? $forums->func->fetch_trimmed_title(trim($_INPUT['description']), 80)  : $this->thread['description'];
			$title = $forums->func->unhtmlspecialchars($title);
			if (preg_match( '#<strong>(.*)</strong>#siU', $title )) {
				$title = preg_replace('#<strong>(.*)</strong>#siU', '\\1', $title);
				$_INPUT['titlebold'] = 'checked="checked"';
			}
			if (preg_match( '#<font[^>]+color=(\'|")(.*)(\\1)>(.*)</font>#esiU', $title )) {
				$_INPUT['titlecolor'] = preg_replace('#<font[^>]+color=(\'|")(.*)(\\1)>(.*)</font>#siU', '\\2', $title);
			}
			$title = strip_tags($title);
			if ($this->lib->forum['threadprefix']) {
				$prefix = explode("||", $this->lib->forum['threadprefix']);
				$threadprefix = "<select onchange='document.mxbform.title.focus(); document.mxbform.title.value = this.options[this.selectedIndex].value + document.mxbform.title.value;'><option selected value=''>".$forums->lang['type']."</option>";
				if (is_array($prefix)) {
					foreach ($prefix AS $value) {
						$threadprefix .= "<option value='".$value."'>".$value."</option>";
					}
				}
				$threadprefix .= "</select>";
			}
			if($this->lib->forum['specialtopic']) {
				$forums->func->check_cache('st');
                $st = explode(",", $this->lib->forum['specialtopic']);
                $specialtopic = "<select name='specialtopic' class='bginput'><option selected value=''>".$forums->lang['selectspecialtopic']."</option>";
				if (is_array($st)) {
					foreach ($st AS $id) {
						$c = ($this->thread['stopic'] == $id) ? " selected='selected'" : '';
						$specialtopic .= "<option value='".$forums->cache['st'][$id]['id']."'{$c}>".$forums->cache['st'][$id]['name']."</option>";
					}
				}
                $specialtopic .= "</select>";
            }
			if ($this->moderator['caneditthreads'] OR $bbuserinfo['supermod']) {
				$show['colorpicker'] = TRUE;
			}
		}
		if ($this->lib->obj['errors']) {
			$show['errors'] = TRUE;
			$errors = $this->lib->obj['errors'];
		}
		if ($this->lib->obj['preview']) {
			$show['preview'] = TRUE;
			$preview = $this->lib->parser->convert_text($this->post['pagetext']);
			$content = preg_replace("#<!--editpost-->(.+?)<!--editpost1-->#","",$content);
		}
		$form_start = $this->lib->fetch_post_form( array( 1 => array( 'do', 'update' ),
			2 => array('t', $this->thread['tid']),
			3 => array('p', $_INPUT['p']),
			4 => array('pp', $_INPUT['pp']),
			5 => array('posthash', $this->posthash))
		);
		$postdesc = $forums->lang['editpost'];
		if ($this->lib->canupload) {
			$show['upload'] = TRUE;
			$upload = $this->lib->fetch_upload_form($this->posthash,'edit',$this->getpost['pid']);
		}
		$upload['maxnum'] = intval($bbuserinfo['attachnum']);
		if ($bbuserinfo['canappendedit']) {
			$show['appendedit'] = TRUE;
		}
		$smiles = $this->lib->construct_smiles();
		$smile_count = $smiles['count'];
		$all_smiles = $smiles['all'];
		$smiles = $smiles['smiles'];
		$_INPUT['iconid'] = isset($_INPUT['iconid']) ? $_INPUT['iconid'] : $this->getpost['iconid'];
		$forums->func->check_cache('icon');
		if (!$this->getpost['iconid'])
		{
			$this->getpost['iconid'] = 2;
		}
		$iconname = $forums->cache['icon'][$this->getpost['iconid']]['image'];
		if (!$iconname) {
			$iconname = 'post.gif';
		}
		$icons  = $this->lib->construct_icons();
		$checked = $this->lib->construct_checkboxes();
		$pagetitle = $forums->lang['editpost']." - ".$bboptions['bbtitle'];
		$nav = $this->lib->nav;
		$extrabuttons = $this->lib->code->construct_extrabuttons();
		if ($bboptions['isajax']) {
			$mxajax_request_type = "POST";
			$jsinclude['ajax'] = 1;
			$attachbutton = 'button'; $ajaxfunc = 'onclick="sendattach();"'; $previewfunc = 'onclick="previewpost('.$this->lib->forum['id'].');"';
		} else {
			$attachbutton = 'submit'; $ajaxfunc = "onclick='override=1;'"; $previewfunc = 'onclick="preview();"';
		}
		if ($bbuserinfo['usewysiwyg']) {
			$content = preg_replace("#\[code\](.+?)\[/code\]#ies"  , "str_replace('&lt;br /&gt;', '<br />', '[code]\\1[/code]')", $content);
			$content = str_replace(array('&lt;', '&gt;'), array('&amp;lt;', '&amp;gt;'), $content);
		}
		$forum = $this->lib->forum;
		$jsinclude['mxe'] = true;
		include $forums->func->load_template('newpost_wysiwyg');
		exit;
	}

	function process()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$this->fetch_permission();
		$this->post = $this->lib->compile_post();
		if ($this->edittitle) {
			$_INPUT['title'] = trim($_INPUT['title']);
			if ( ($forums->func->strlen($_INPUT['title']) < 2) OR !$_INPUT['title']  ) {
				$this->lib->obj['errors'] = $forums->lang['musttitle'];
			}
			if ( strlen($forums->func->stripslashes_uni($_INPUT['title'])) > 250 ) {
				$this->lib->obj['errors'] = $forums->lang['titletoolong'];
			}
		}
		$hidepostinfo = $this->hidefunc->check_hide_condition();
        if ( is_string($hidepostinfo) && strlen($hidepostinfo) > 0 ) {
            $this->lib->obj['errors'] = $hidepostinfo;
        } else if ( !$this->getpost['hidepost'] && is_array($hidepostinfo) ) {
            $hidepostinfo = serialize($hidepostinfo);
            $this->post['hidepost'] = $hidepostinfo;
        } else if ( $this->getpost['hidepost'] && is_array($hidepostinfo) ) {
            $oldhideinfo = unserialize($this->getpost['hidepost']);
            if ( ($oldhideinfo['type'] == 1 || $oldhideinfo['type'] == 2) && ($hidepostinfo['type'] == 1 || $hidepostinfo['type'] == 2) ) {
                $newhideinfo = $hidepostinfo;
                $newhideinfo['buyers'] = $oldhideinfo['buyers'];
            } else {
                $newhideinfo = $hidepostinfo;
            }
            $newhideinfo = serialize($newhideinfo);
            $this->post['hidepost'] = $newhideinfo;
        } else {
            $this->post['hidepost'] = '';
        }
		if ( ($this->lib->obj['errors'] != "") OR ($this->lib->obj['preview'] != "") ) {
			return $this->showform();
		}
		$time = $forums->func->get_date( TIMENOW, 2 );
		$this->post['host']  = $this->getpost['host'];
		$this->post['threadid'] = $this->getpost['threadid'];
		$this->post['userid'] = $this->getpost['userid'];
		$this->post['pid'] = $this->getpost['pid'];
		$this->post['dateline']   = $this->getpost['dateline'];
		$this->post['username'] = $this->getpost['username'];
		$this->post['moderate'] = $this->getpost['moderate'];
		if ($this->getpost['newthread'] == 1) {
			if ($this->post['iconid'] != $this->getpost['iconid'] AND $this->post['iconid'] != '') {
				$uptitle[] = "iconid=".intval($this->post['iconid'])."";
			}
		}
		if ( $this->edittitle ) {
			if ( $this->lib->forum['forcespecial'] AND isset($_INPUT['specialtopic']) AND $_INPUT['specialtopic'] == '' ) {
				$_INPUT['specialtopic'] = $this->thread['stopic'];
			}
			$_INPUT['title'] = $this->lib->parser->censoredwords( $_INPUT['title'] );
			$_INPUT['description'] = (trim($_INPUT['description'])) ? $forums->func->fetch_trimmed_title(trim($_INPUT['description']), 80) : '';
			$_INPUT['description']  = $this->lib->parser->censoredwords( $_INPUT['description']  );
			$_INPUT['title'] = $this->lib->compile_title();
			if ( ($_INPUT['title'] != $this->thread['title']) OR ($_INPUT['description'] != $this->thread['description'])  OR ($_INPUT['specialtopic'] != $this->thread['stopic'])  ) {
				if ($_INPUT['title'] != $this->thread['title']) {
					$uptitle[] = "title='".addslashes($_INPUT['title'])."'";
				}
				if ($_INPUT['description'] != $this->thread['description']) {
					$uptitle[] = "description='".addslashes($_INPUT['description'])."'";
				}
				if ($_INPUT['specialtopic'] != $this->thread['stopic']) {
					$uptitle[] = "stopic='".intval($_INPUT['specialtopic'])."'";
				}

				if ($this->thread['tid'] == $this->lib->forum['lastthreadid'] && $this->lib->forum['id'] > 0) {
					$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."forum SET lastthread='".addslashes(strip_tags($_INPUT['title']))."' WHERE id='".$this->lib->forum['id']."'" );
					$forums->func->check_cache('forum_stats', 'forum');
					$forums->cache['forum_stats'][$this->lib->forum['id']]['lastthread'] = strip_tags($_INPUT['title']);
					$forums->func->update_cache(array('name' => 'forum_stats'));
				}
				if ( ($this->moderator['caneditthreads'] == 1) OR ( $bbuserinfo['supermod'] == 1 ) ) {
					$this->thread['title'] = addslashes($this->thread['title']);
					$_INPUT['title'] = addslashes($_INPUT['title']);
					$DB->shutdown_query( "INSERT INTO ".TABLE_PREFIX."moderatorlog
												(forumid, threadid, postid, userid, username, host, referer, dateline, title, action)
											VALUES
												(".$this->lib->forum['id'].", ".$this->thread['tid'].", ".$this->post['pid'].", ".$bbuserinfo['id'].", '".$DB->escape_string($bbuserinfo['name'])."', '".$DB->escape_string(SESSION_HOST)."', '".$DB->escape_string(REFERRER)."', ".TIMENOW.", '".$this->thread['title']."', '{$forums->lang['changetitle']} \"".$this->thread['title']."\" {$forums->lang['changetitleto']} \"".$_INPUT['title']."\"')"
										);
				}
			}
		}
		if (is_array($uptitle)) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET ".implode(", ", $uptitle)." WHERE tid=".$this->thread['tid']."" );
			if ($this->thread['sticky'] == 2) {
				require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
				$lib = new adminfunctions_cache();
				$lib->globalstick_recache();
			}
		}
		$forums->func->fetch_query_sql( $this->post, 'post', "pid=".$this->post['pid'] );
		$this->lib->attachment_complete(array($this->posthash), $this->thread['tid'], $this->post['pid']);
		if ($_INPUT['redirect']) {
			$forums->func->standard_redirect("forumdisplay.php?{$forums->sessionurl}f={$this->lib->forum['id']}");
		} else {
			$forums->func->standard_redirect("showthread.php?{$forums->sessionurl}t={$this->thread['tid']}&amp;pp={$_INPUT['pp']}#pid{$this->post['pid']}");
		}
	}

	function fetch_permission()
	{
		global $forums, $bbuserinfo;
		$canedit = FALSE;
		if ( ($bbuserinfo['id']) AND ($bbuserinfo['supermod'] != 1) ) {
			$this->moderator = $bbuserinfo['_moderator'][ $this->lib->forum['id'] ];
		}
		if ( $bbuserinfo['supermod'] OR $this->moderator['caneditposts'] ) {
			$canedit = TRUE;
		} elseif ( ($this->getpost['userid'] == $bbuserinfo['id']) AND ($bbuserinfo['caneditpost']) ) {
			if ($bbuserinfo['edittimecut'] > 0) {
				if ( $this->getpost['dateline'] > ( TIMENOW - ( intval($bbuserinfo['edittimecut']) * 60 ) ) ) {
					$canedit = TRUE;
				}
			} else {
				$canedit = TRUE;
			}
		}
		if ($canedit != TRUE) {
			$forums->func->standard_error("exceededitpost");
		}
		if (($this->thread['open'] != 1) AND (!$bbuserinfo['supermod'])) {
			if ($bbuserinfo['canpostclosed'] != 1) {
				$forums->func->standard_error("threadeditclosed");
			}
		}
		$this->edittitle = FALSE;
		if ( $this->getpost['newthread'] == 1 ) {
			if ($bbuserinfo['supermod'] == 1) {
				$this->edittitle = TRUE;
			} else if ($this->moderator['caneditthreads'] == 1) {
				$this->edittitle = TRUE;
			} else if ($bbuserinfo['caneditthread'] == 1 ) {
				$this->edittitle = TRUE;
			}
		}
		return;
	}
}

$output = new editpost();
$output->show();

?>
