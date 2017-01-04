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
define('THIS_SCRIPT', 'showthread');

require_once('./global.php');

class showthread {

	var $posthash = '';
	var $thread = array();
	var $forum = array();
	var $page = 0;
	var $maxposts = 10;
	var $moderator = array();
	var $cached_users = array();
	var $postcount = 0;
	var $already_replied = 0;
    var $canview_hideattach = 0;
    var $canview_hidecontent = 0;

    function show()
    {
        global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'showthread' );
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		$forums->forum->forums_init();
		$forums->func->check_cache('banksettings');
		require_once(ROOT_PATH.'includes/xfunctions_hide.php');
		$this->hidefunc = new hidefunc();
        $this->posthash = $forums->func->md5_check();
		$_INPUT['t'] = intval($_INPUT['t']);
		if ($_INPUT['t'] < 1) {
			$forums->func->standard_error("errorthreadlink");
		}
		$this->thread = $DB->query_first("SELECT t.* FROM ".TABLE_PREFIX."thread t WHERE t.tid='".$_INPUT['t']."'");
		$this->forum = $forums->forum->single_forum($this->thread['forumid']);
        if (!$this->forum['id'] OR !$this->thread['tid']) {
        	$forums->func->standard_error("erroraddress");
        }
		$forums->func->load_forum_style($this->forum['style']);
        if (!$this->can_moderate($this->forum['id'])) {
			if ($this->thread['visible'] != 1) {
				$forums->func->standard_error("errorthreadlink");
			}
        }
        require_once(ROOT_PATH."includes/functions_codeparse.php");
		require_once(ROOT_PATH."includes/functions_showthread.php");
		$this->lib = new functions_showthread();
        $this->parser = new functions_codeparse();
        $forums->func->check_cache('ranks');
		if($bboptions['openolrank']) {
			$forums->func->check_cache('olranks');
		}
        if ($this->thread['open'] == 2) {
        	$f_stuff = explode('&', $this->thread['moved']);
			$forums->func->standard_redirect(ROOT_PATH."showthread.php?{$forums->sessionurl}t={$f_stuff[0]}");
        }
        $forums->forum->check_permissions( $this->forum['id'], 1, 'thread', $this->thread['postuserid'] );
        if ($threadread = $forums->func->get_cookie('threadread')) {
        	$threadread = unserialize($threadread);
        	if (!is_array($threadread)) {
        		$threadread = array();
        	}
        }
		$this->forum['jump'] = $forums->func->construct_forum_jump();
		$this->pp = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
        if ( ! $bbuserinfo['canviewothers'] AND $this->thread['postuserid'] != $bbuserinfo['id'] ) {
        	$forums->func->standard_error("cannotviewthread");
        }
        if ($bbuserinfo['id']) {
			$threadread[$this->thread['tid']] = TIMENOW;
			$forums->func->set_cookie('threadread', serialize($threadread), -1 );
        }
		$this->maxposts = $bboptions['maxposts'] ? $bboptions['maxposts'] : '10';
		if ($bbuserinfo['id'] AND !$bbuserinfo['supermod']) {
			$this->moderator = $bbuserinfo['_moderator'][ $this->forum['id'] ];
		}
		$this->thread['replybutton'] = $this->reply_button();
		if ($_INPUT['highlight']) {
			$highlight = '&amp;highlight='.$_INPUT['highlight'];
		}
		if ($this->can_moderate($this->forum['id'])) {
			$this->thread['post'] += intval( $this->thread['modposts'] );
		}
		if ($_INPUT['extra']) {
			$extra = '&amp;extra='.urlencode(str_replace("&amp;", "&", $_INPUT['extra']));
		}
		$this->thread['pagenav'] = $forums->func->build_pagelinks(array(
			'totalpages'  => ($this->thread['post']+1),
			'perpage'    => $this->maxposts,
			'curpage'  => $_INPUT['pp'],
			'pagelink'    => "showthread.php?{$forums->sessionurl}t=".$this->thread['tid'].$highlight.$extra,)
		);
		if (($this->thread['post'] + 1) > $this->maxposts) {
			$this->thread['gotonew'] = "<a href='redirect.php?{$forums->sessionurl}f=".$this->forum['id']."&amp;t=".$this->thread['tid']."&amp;goto=newpost'>".$forums->lang['gotonewpost']."</a>";
		}
		if ($this->thread['description']) {
			$this->thread['description'] = ' - '.$this->thread['description'];
		}
		if ($bboptions['threadviewsdelay']) {
			if(@$fp = fopen(ROOT_PATH . 'cache/cache/threadviews.txt', 'a')) {
				fwrite($fp, $_INPUT['t'] . "\n");
				fclose($fp);
			}
		} else {
			$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET views=views+1 WHERE tid='".$this->thread['tid']."'");
		}

		if ($this->thread['pollstate']) {
			$show['poll'] = TRUE;
			$poll_footer = "";
			$poll_data = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."poll WHERE tid='".$this->thread['tid']."'" );
			if (! $poll_data['pollid']) {
				return;
			}
			if (!$poll_data['question']) {
				$poll_data['question'] = $this->thread['title'];
			}
			if (!$bbuserinfo['id']) {
				$show['results'] = TRUE;
				$poll_footer = $forums->lang['guestcannotpoll'];
			} else {
				$delete_link = "";
				$edit_link = "";
				$canedit = FALSE;
				$candelete = FALSE;
				if ($this->moderator['caneditposts']) {
					$canedit = TRUE;
				}
				if ($this->moderator['candeleteposts']) {
					$candelete = TRUE;
				}
				if ($bbuserinfo['supermod']) {
					$canedit   = TRUE;
					$candelete = TRUE;
				}
				if ($canedit) {
					$edit_poll   = "[ <a href=\"moderate.php?{$forums->sessionurl}do=editpoll&amp;f=".$this->forum['id']."&amp;t=".$this->thread['tid']."&amp;posthash=".$this->posthash."\">".$forums->lang['edit']."</a> ]";
				}
				if ($candelete) {
					$delete_poll = "[ <a href=\"moderate.php?{$forums->sessionurl}do=deletepoll&amp;f=".$this->forum['id']."&amp;t=".$this->thread['tid']."&amp;posthash=".$this->posthash."\">".$forums->lang['delete']."</a> ]";
				}
				$poll_data['voters'] = ','.$poll_data['voters'];
				if(preg_match("#\,".$bbuserinfo['id']."\,#",$poll_data['voters'])) {
					$show['results'] = TRUE;
					$poll_footer = $forums->lang['youvoted'];
				}
				if ( $this->thread['open'] == 0 ) {
					$show['results'] = TRUE;
					$poll_footer = '&nbsp;';
				}
				if ( $bboptions['allowviewresults'] ) {
					if ( $_INPUT['mode'] == 'showpoll' ) {
						$show['results'] = TRUE;
						$poll_footer = "";
					}
				}
			}
			$polloptions = unserialize(stripslashes($poll_data['options']));
			reset($polloptions);
			if ($show['results']) {
				$votetotal = 0;
				foreach ($polloptions AS $entry) {
					$entry['id'] = intval($entry[0]);
					$entry['choice'] = $entry[1];
					$entry['votes'] = intval($entry[2]);
					$votetotal += $entry['votes'];
					if ( strlen($entry['choice']) < 1 ) continue;
					if ($bboptions['enablepolltags']) {
						$entry['choice'] = $this->parser->parse_poll_tags($entry['choice']);
					}
					$entry['percent'] = $entry['votes'] == 0 ? 0 : $entry['votes'] / $poll_data['votes'] * 100;
					$entry['percent'] = sprintf( '%.2f' , $entry['percent'] );
					$entry['width']   = $entry['percent'] > 0 ? (int) $entry['percent'] * 2 : 0;
					$voters[$entry['id']] = $entry;
				}
				$votetotal = $votetotal == $poll_data['votes'] ? $votetotal : $poll_data['votes'];
			} else {
				$question = $poll_data['question'];
				foreach ($polloptions AS $entry) {
					$entry['id'] = intval($entry[0]);
					$entry['choice'] = $entry[1];
					$entry['votes'] = intval($entry[2]);
					if ( strlen($entry['choice']) < 1 ) continue;
					if ($bboptions['enablepolltags']) {
						$entry['choice'] = $this->parser->parse_poll_tags($entry['choice']);
					}
					$voters[$entry['id']] = $entry;
				}
			}
			if (empty($poll_footer)) {
				if ($bboptions['allowviewresults']) {
					if ($_INPUT['mode'] == 'showpoll') {
						$showresult = "<input type='button' class='button' name='viewresult' value='".$forums->lang['showoptions']."'  title='".$forums->lang['returnandshow']."' onclick='show_votes()' />";
					} else {
						$showresult = "<input type='button' value='".$forums->lang['showresults']."' title='".$forums->lang['viewresults']."' class='button' onclick='get_votes()' />";
						$votepoll = "<input type='submit' name='submit' value=' ".$forums->lang['poll']." ' class='button' title='".$forums->lang['addpoll']."' />";
					}
				} else {
					$votepoll = "<input type='submit' name='submit' value=' ".$forums->lang['poll']." ' class='button' title='".$forums->lang['addpoll']."' />";
					$showresult = "<input type='submit' name='nullvote' class='button' value='".$forums->lang['addemptypoll']."' title='".$forums->lang['viewresultsnotpoll']."' />";
				}
			} else {
				$votepoll = $poll_footer;
			}
		} else {
			if (!$this->thread['pollstate'] AND $this->thread['open'] != 0 AND $bbuserinfo['id'] AND $bbuserinfo['canpostpoll'] AND $this->forum['allowpoll'] AND ( ($this->thread['postuserid'] == $bbuserinfo['id']) AND ($bboptions['addpolltimeout'] > 0) AND ( $this->thread['dateline'] + ($bboptions['addpolltimeout'] * 3600) > TIMENOW ) ) OR ( $bbuserinfo['supermod'] ) )
			{
				$this->thread['newpoll'] = 1;
			}
		}
		if ($bboptions['cookietimeout'] == "") {
			$bboptions['cookietimeout'] = 15;
		}
		if ( $this->forum['moderatepost']) {
			$moderate = ' AND p.moderate=0';
			if ( $this->can_moderate($this->thread['forumid']) ) {
				$moderate = '';
				if ( $_INPUT['modfilter'] == 'invisiblepost' ) {
					$moderate = ' AND p.moderate=1';
				}
			}
		} else {
			$moderate = '';
		}
		$thread = $this->thread;
		$forum = $this->forum;
		$posthash = $this->posthash;
		$showpost = array();
		$post = $DB->query( "SELECT up.*, p.*, u.id,u.name,u.usergroupid,u.gender,u.qq,u.uc,u.popo,u.email,u.joindate,u.quintessence, u.posts, u.lastvisit, u.lastactivity,u.options,u.customtitle,u.options, u.signature, u.location, u.award_data, u.avatarlocation, u.avatartype, u.avatarsize, u.cash, u.bank, u.mkaccount, u.reputation, u.onlinetime, g.canblog, e.loanamount
			FROM ".TABLE_PREFIX."post p
				LEFT JOIN ".TABLE_PREFIX."user u ON (p.userid=u.id)
				LEFT JOIN ".TABLE_PREFIX."usergroup g ON (g.usergroupid=u.usergroupid)
				LEFT JOIN ".TABLE_PREFIX."userextra e ON (p.userid=e.id)
				LEFT JOIN ".TABLE_PREFIX."userexpand up ON (up.id=p.userid)
			WHERE threadid='".$this->thread['tid']."'".$moderate." ORDER BY pid LIMIT ".$this->pp.", ".$this->maxposts."" );

		$allpostrows = $attachment_inpost = array();
        while ( $row = $DB->fetch_array( $post ) ) {
            if ( $row['userid'] == $bbuserinfo['id'] ) {
                $this->already_replied = TRUE;
            }
			$pids[] = $row['pid'];
            $allpostrows[] = $row;
        }
		if ( $this->thread['attach'] ) {
			$attachment = $this->lib->parse_attachment( $pids );
			$attachment_inpost = $attachment['attachments_inpost'];
			$attachment = $attachment['attachments'];
		}
		$forums->func->check_cache('usergroup');
		$forums->func->check_cache('icon');
        foreach ( $allpostrows AS $row ) {
            $this->canview_hidecontent = FALSE;
           	$row['attachment_inpost'] = ($attachment_inpost[$row['pid']]) ? $attachment_inpost[$row['pid']] : '';
            $return = $this->parse_row( $row );
			$return['poster']['onlinerankimg'] = is_array($return['poster']['onlinerankimg']) ? $return['poster']['onlinerankimg'] : array();
            $return['poster']['cash'] = $return['poster']['cash'].$forums->cache['banksettings']['bankcurrency'];
            $return['row']['stype'] = ($bbuserinfo['id'] != $return['row']['userid'])?1:0;
			if (($bbuserinfo['id'] != $return['poster']['userid'] AND !$bbuserinfo['supermod']) OR !$bboptions['isajax']) {
			   $return['poster']['canajaxeditsig'] = false;
			} else {
			   $return['poster']['canajaxeditsig'] = true;
			}
            if ( $row['hidepost'] && $row['hidepost'] != NULL && $attachment[$row['pid']] && $return['row']['canview_hideattach'] ) {
                if ( $return['row']['onlyattach'] ) {
					$forums->lang['buyers'] = sprintf( $forums->lang['buyers'], $return['row']['buyernum'] );
                    $return['attachment'] = '<div class="hidetop">'.$forums->lang['hideattachment'].': '.sprintf( $forums->lang['requiremoney'], $return['row']['hidecond'], $forums->cache['banksettings']['bankcurrency']).' '.$forums->lang['buyers'].' [ <a href="#pid'.$row['pid'].'" onclick="javascript:window.open(\'misc.php?'.$forums->sessionurl.'do=whobought&amp;pid='.$row['pid'].'\',\'WhoBought\',\'width=200,height=300,resizable=yes,scrollbars=yes\');">'.$forums->lang['whobought'].'</a> ]</div><div class="hidemain">'.$attachment[$row['pid']].'</div>';
                } else if ( $return['row']['attachextra'] ) {
                    $return['attachment'] = '<div class="hidetop">'.$forums->lang['hideattachment'].':</div><div class="hidemain">'.$attachment[$row['pid']].'</div>';
                } else {
                    $return['attachment'] = $attachment[$row['pid']];
                }
            } else if ( $row['hidepost'] && $row['hidepost'] != NULL && $attachment[$row['pid']] && !$return['row']['canview_hideattach'] ) {
                if ( $return['row']['onlyattach'] ) {
					$forums->lang['buypost'] = sprintf( $forums->lang['buypost'], $return['row']['hidecond'], $forums->cache['banksettings']['bankcurrency'], $return['row']['buyernum'] );
                    $return['attachment'] = '<div class="hidetop">'.$forums->lang['buypost'].' [ <a href="#pid'.$row[pid].'" onclick="javascript:window.open(\'misc.php?'.$forums->sessionurl.'do=whobought&amp;pid='.$row[pid].'\',\'WhoBought\',\'width=200,height=300,resizable=yes,scrollbars=yes\');">'.$forums->lang['whobought'].'</a> | <a href="misc.php?'.$forums->sessionurl.'do=buyhidden&amp;tid='.$row[threadid].'&amp;pid='.$row[pid].'">'.$forums->lang['wantbuy'].'</a> ]</div><div class="hidemain">'.$forums->lang['hideattachment'].'</div>';
                } else if ( $return['row']['attachextra'] ) {
                    $return['attachment'] = '<div class="hidetop">'.$forums->lang['attachmenthidden'].':</div><div class="hidemain">'.$forums->lang['hideattachment'].'</div>';
                } else {
                    $return['attachment'] = $attachment[$row['pid']];
                }
            } else {
                $return['attachment'] = $attachment[$row['pid']];
            }
            $showpost[] = $return;
        }
		$mod = $this->moderation_panel();
		$jsinclude['quickmxe'] = true;
		if (  ( $forums->func->fetch_permissions( $this->forum['canreply'], 'canreply') == TRUE ) AND ( $this->thread['open'] != 0 ) ) {
			$jsinclude['quickreply'] = true;
			$show['quickreply'] = true;

		}
		$showvoters = $voters;
		if ($show['quickreply']) {
			require_once( ROOT_PATH."includes/functions_showcode.php" );
			$this->code = new functions_showcode();
			$antispam = $this->code->showantispam();
			if ( $bbuserinfo['redirecttype'] ) {
				$redirect = ' checked="checked"';
			}
			if ( $bbuserinfo['usewysiwyg'] ) {
				$showwysiwyg = "&wysiwyg=1";
				$loadjs = "";
			}
			$patterns = $forums->lang['hiddencontent'].": ";
			$replacements = "";
			$limitmax = $this->thread['post'];
			$limitmin = $limitmax % 10;
			$limit = $limitmax - $limitmin;
			if($_INPUT['pp'] == $limit AND $bboptions['isajax']) {
				$jsinclude['ajax'] = 1;
				$mxajax_request_type = "POST";
				$queryajax = 1;
				$subtype="button";
				$action="onkeydown=\"if(event.ctrlKey && event.keyCode==13){queryly();}\"";
			} else {
				$queryajax = 0;
				$subtype="submit";
				$action="onKeyDown='javascript: ctlent();'";
			}
		}
		$title_no_tags = strip_tags($this->thread['title']);
		$pagetitle = $title_no_tags." - ".$bboptions['bbtitle'];
		$nav = array_merge( $forums->forum->forums_nav( $this->forum['id'], $_INPUT['extra'] ), array( "<a href='showthread.php?{$forums->sessionurl}t=".$this->thread['tid']."'>".$this->thread['title']."</a>" ) );
		$thisfid = $this->forum['id'];
		if (($bbuserinfo['_moderator'][$thisfid]['caneditposts'] OR $bbuserinfo['supermod']))
		{
			$ismod_or_supermod = true;
			if ($bboptions['isajax'])
			{
				$jsinclude['ajax'] = 1;
				$mxajax_request_type = "POST";
				$canajaxeditpost = true;
				if (!$this->thread['open'] AND !$bbuserinfo['supermod'])
				{
					$canajaxeditpost = false;
				}
			}
		}
		$varusedintemplate = array("bankcurrency" => $forums->cache['banksettings']['bankcurrency'],
							       "threaddateline" => $forums->func->get_date( $thread['dateline'], 2 ),
							       "allowbbcode" => $this->forum['allowbbcode'],
							       "allowhtml" => $this->forum['allowhtml'],
		                      );
		include $forums->func->load_template('showthread');
		exit;
	}

	function parse_row( $row = array() )
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions, $_USEROPTIONS;
		$poster = array();
		if ($row['userid']) {
			if ( isset($this->cached_users[ $row['userid'] ]) ) {
				$poster = $this->cached_users[ $row['userid'] ];
				$row['name_css'] = 'normalname';
			} else {
				$row['name_css'] = 'normalname';
				$poster = $forums->func->fetch_user( $row );
				$this->cached_users[ $row['userid'] ] = $poster;
			}
		} else {
			$poster = $forums->func->set_up_guest( $row['username'] );
			$row['name_css'] = 'unreg';
		}
		if ($row['anonymous']) {
			if ($bbuserinfo['usergroupid'] == 4) {
				$poster['name'] = $poster['name']." (".$forums->lang['anonymouspost'].")";
			} else {
				$poster = array();
				$poster['name'] = $forums->lang['anonymous'].'-'.rand(100000,999999);
				$poster['id'] = 0;
				$poster['grouptitle'] = $forums->lang['byanonymous'];
				$poster['posts'] = $forums->lang['unknown'];
			}
		}
		if ($row['reppost']) {
			$reputation = unserialize($row['reppost']);
			$row['repuser'] = $forums->lang['repuser'].": ".$reputation['user'];
			$row['showreputation'] = $forums->lang['showreputation'].": ".$reputation['number'];
		}
		if ($row['cashpost']) {
			$cashpost = unserialize($row['cashpost']);
			$forums->lang['cashpostuser'] = ($cashpost['cashnum'] >= 0) ? $forums->lang['addcashpostuser'] : $forums->lang['reducecashpostuser'];
			$row['cashuser'] = sprintf($forums->lang['cashpostuser'],$forums->cache['banksettings']['bankcurrency']).": ".$cashpost['user'];
			$row['showcashpost'] = sprintf($forums->lang['showcashpost'],$forums->cache['banksettings']['bankcurrency']).": ".$cashpost['cashnum'];
		}
		if ( $row['moderate'] OR ($this->thread['firstpostid'] == $row['pid'] AND $this->thread['visible'] != 1) ) {
			$row['post_css'] = $this->postcount % 2 ? 'row1shaded' : 'row2shaded';
			$row['altrow']   = 'row1shaded';
		} else {
			$row['post_css'] = $this->postcount % 2 ? 'row1' : 'row2';
			$row['altrow']   = 'row1';
		}
		if ($_INPUT['highlight']) {
			$keywords = str_replace( "+", ",", $_INPUT['highlight'] );
			if ( preg_match("/,(and|or),/i", $keywords) ) {
				while ( preg_match("/,(and|or),/i", $keywords, $match) ) {
					$word_array = explode( ",".$match[1].",", $keywords );
					if (is_array($word_array)) {
						foreach ($word_array AS $keywords) {
							$row['pagetext'] = preg_replace( "/(.*)(".preg_quote($keywords, '/').")(.*)/is", "\\1<span class='highlight'>\\2</span>\\3", $row['pagetext'] );
						}
					}
				}
			} else {
				$row['pagetext'] = preg_replace( "/(.*)(".preg_quote($keywords, "/").")(.*)/i", "\\1<span class='highlight'>\\2</span>\\3", $row['pagetext'] );
			}
		}
		if ($forums->func->is_browser('ie')) {
			$row['pagetext'] = preg_replace( "/<div class='codetop'>(.+?)<\/div><div class='codemain'>(.+?)<\/div>/ie", "\$this->lib->paste_code('\\1', '\\2')", $row['pagetext'] );
		}
		$row['pagetext'] = preg_replace( "/<!--emule1-->(.+?)<!--emule2-->/ie", "\$this->lib->paste_emule('\\1')", $row['pagetext'] );
		if ($row['attachment_inpost']) {
			$row['pagetext'] = preg_replace("/<!--attachid::(.+?)-->.+?<!--attachid-->/ie", "\$this->lib->paste_attachment('\\1', \$row['attachment_inpost']['\\1'])", $row['pagetext']);
		}
		if ( $row['userid'] ) {
			$timelimit = TIMENOW - $bboptions['cookietimeout'] * 60;
			$poster['status'] = 0;
			$this->user['options'] = intval($this->user['options']);
			foreach($_USEROPTIONS AS $optionname => $optionval) {
				$row["$optionname"] = $row['options'] & $optionval ? 1 : 0;
			}
			if ( ( $row['lastvisit'] > $timelimit OR $row['lastactivity'] > $timelimit ) AND $row['invisible'] != 1 AND $row['loggedin'] == 1 ) {
				$poster['status'] = 1;
			}
		} else {
			$poster['status'] = '';
		}
		$row['delete_button'] = $this->delete_button($row['pid'], $poster);
		$row['edit_button']   = $this->edit_button($row['pid'], $poster, $row['dateline']);
		$row['dateline']     = $forums->func->get_date( $row['dateline'], 2 );
		$row['post_icon'] = $row['iconid'] ? 1 : 0;
		$row['post_icon_hash'] = $forums->cache['icon'][$row['iconid']]['image'];
		$row['host'] = $this->view_ip($row, $poster);
		$row['report_link'] = (($bboptions['disablereport'] != 1) AND ( $bbuserinfo['id'] )) ? 1 : 0;
		$row['signature'] = "";
		if ($poster['signature'] AND $bbuserinfo['showsignatures']) {
			if ($row['showsignature']) {
				if (!$this->sigcache[$row['userid']] || !$bboptions['onlyonesignatures']) {
					$this->sigcache[$row['userid']] = 1;
					$this->parser->show_html = intval($bboptions['signatureallowhtml']);
					$this->parser->nnl2br = 1;
					$row["sig"] = $this->parser->convert_text($poster['signature']);
					$row['signature'] = 1;
				} else {
					$row['signature'] = 0;
				}
			}
		}
		if ($poster['id']) {
			$poster['name'] = "<a href='profile.php?{$forums->sessionurl}u=".$poster['id']."'>".$poster['name']."</a>";
		}
		$this->parser->show_html  = ( $this->forum['allowhtml'] AND $forums->cache['usergroup'][ $poster['usergroupid'] ]['canposthtml'] ) ? 1 : 0;
		$row['pagetext'] = $this->parser->convert_text( $row['pagetext'] );
		if ( $row['hidepost'] && $row['hidepost'] != NULL ) {
            $row = $this->hidefunc->parse_hide_code($row, $this->forum['id']);
        }
		$this->postcount++;
		$row['postcount'] = intval($_INPUT['pp']) + $this->postcount;
		return array( 'row' => $row, 'poster' => $poster );
	}

	function delete_button($postid, $poster)
	{
		global $forums, $bbuserinfo, $bboptions, $_INPUT;
		if (!$bbuserinfo['id']) return "";
		if ( $bboptions['isajax']) {
			$button = 'isajax';
		} else {
			$button = 'noajax';
		}
		if ($bbuserinfo['supermod'] OR $this->moderator['candeleteposts'] OR ($poster['id'] == $bbuserinfo['id'] AND ($bbuserinfo['candeletepost']))) {
			return $button;
		}
		return "";
	}

	function edit_button($postid, $poster, $dateline)
	{
		global $forums, $bbuserinfo, $_INPUT;
		if (!$bbuserinfo['id']) return "";
		$button = 1;
		if ($bbuserinfo['supermod'] OR $this->moderator['caneditposts']) {
			return $button;
		}
		if ($poster['id'] == $bbuserinfo['id'] AND ($bbuserinfo['caneditpost'])) {
			if ($bbuserinfo['edittimecut'] > 0) {
				if ( $dateline > ( TIMENOW - ( intval($bbuserinfo['edittimecut']) * 60 ) ) ) {
					return $button;
				} else {
					return "";
				}
			} else {
				return $button;
			}
		}
		return "";
	}

	function reply_button()
	{
		global $forums, $bbuserinfo;
		if ($this->thread['open'] == 0) {
			if ($bbuserinfo['canpostclosed']) {
				$return['isurl'] = 1;
				$return['button'] = 'closed';
				return $return;
			} else {
				$return['isurl'] = 0;
				$return['button'] = 'closed';
				return $return;
			}
		}
		if ($this->thread['open'] == 2) {
			$return['isurl'] = 0;
			$return['button'] = 't_moved';
			return $return;
		}
		if ($this->thread['pollstate'] == 2) {
			$return['isurl'] = 0;
			$return['button'] = 'closed';
			return $return;
		}
		$return['isurl'] = 1;
		$return['button'] = 'newreply';
		return $return;
	}

	function view_ip($row, $poster)
	{
		global $forums, $bbuserinfo, $bboptions;
		if (!$bbuserinfo['supermod'] && !$this->moderator['canviewips']) {
			return "";
		} else {
			$row['host'] = $poster['usergroupid'] == 4 ? '' : "IP: ".$row['host']." &#0124;";
			return $row['host'];
		}
	}

	function moderation_panel()
	{
		global $bbuserinfo, $forums;
		if (!$bbuserinfo['id']) {
			return '';
		}
		$modlink = '';
		$canmoderate = false;
		if ($bbuserinfo['id'] == $this->thread['postuserid'] OR $bbuserinfo['supermod'] OR $this->moderator['moderatorid'] != '') {
			$canmoderate = true;
		}
		if (!$canmoderate) {
			return '';
		}
		$mod_action = array(
			'closethread' => 'close',
			'openthread' => 'open',
			'canremoveposts' => 'move',
			'candeletethreads' => 'delete',
			'caneditthreads' => 'editthread',
			'gstickthread' => 'gstick',
			'stickthread' => 'stick',
			'unstickthread' => 'unstick',
			'canmergethreads' => 'merge',
			'cansplitthreads' => 'splitthread',
		);
		$actions = array(
			'quintessence' => $forums->lang['modquintess'],
			'unquintessence' => $forums->lang['modunquintess'],
			'canremoveposts' => $forums->lang['modmove'],
			'closethread' => $forums->lang['modclose'],
			'openthread' => $forums->lang['modopen'],
			'candeletethreads' => $forums->lang['moddelete'],
			'caneditthreads' => $forums->lang['modedittitle'],
			'stickthread' => $forums->lang['modstick'],
			'gstickthread' => $forums->lang['modgstick'],
			'unstickthread' => $forums->lang['modunstick'],
			'canmergethreads' => $forums->lang['modmerge'],
			'cansplitthreads' => $forums->lang['modsplit'],
			'approve' => $forums->lang['modapprove'],
			'unapprove' => $forums->lang['modunapprove'],
			'specialtopic' => $forums->lang['modspecialtopic'],
			'unspecialtopic' => $forums->lang['modunspecialtopic'],
			'cleanlog' => $forums->lang['cleanlog'],
		);
		foreach($actions as $key => $value) {
			$this_action = ($mod_action[$key]) ? $mod_action[$key] : $key;
			if ($key == 'specialtopic') {
				if ($this->forum['specialtopic']) {
					$modlink .= $this->append_link($key, $value, $this_action);
				}
			} else if ($key == 'unspecialtopic') {
				if (!$this->forum['forcespecial']) {
					$modlink .= $this->append_link($key, $value, $this_action);
				}
			} else if ($bbuserinfo['supermod']) {
				$modlink .= $this->append_link($key, $value, $this_action);
			} else if ($this->moderator['moderatorid']) {
				if ($key == 'quintessence' OR $key == 'unquintessence') {
					$modlink .= $this->append_link($key, $value, $this_action);
				} else if ($key == 'closethread' OR $key == 'openthread') {
					if ($this->moderator['canopenclose']) {
						$modlink .= $this->append_link($key, $value, $this_action);
					}
				} else if ($key == 'stickthread' OR $key == 'unstickthread') {
					if ($this->moderator['canstickthread']) {
						$modlink .= $this->append_link($key, $value, $this_action);
					}
				} else if ($key == 'approve' OR $key == 'unapprove') {
					if ($this->moderator['canmanagethreads']) {
						$modlink .= $this->append_link($key, $value, $this_action);
					}
				} else {
					if ($this->moderator[strtolower($key)]) {
						$modlink .= $this->append_link($key, $value, $this_action);
					}
				}
			} else if ($key == 'openthread' OR $key == 'closethread') {
				if ($bbuserinfo['canopenclose']) {
					$modlink .= $this->append_link($key, $value, $this_action);
				}
			} else if ($key == 'candeletethreads') {
				if ($bbuserinfo['candeletethreads']) {
					$modlink .= $this->append_link($key, $value, $this_action);
				}
			}
		}
		if ($modlink != '') {
			return $modlink;
		}
	}

	function append_link($key = '', $value = '', $mod_action = '')
	{
		if  ($key == '' ||
			($this->thread['quintessence'] == 1 AND $key == 'quintessence') ||
			($this->thread['quintessence'] == 0 AND $key == 'unquintessence') ||
			($this->thread['open'] == 1 AND $key == 'openthread') ||
			($this->thread['open'] == 0 AND $key == 'closethread') ||
			($this->thread['open'] == 2 AND ($key == 'closethread' OR $key == 'canremoveposts')) ||
			($this->thread['sticky'] == 2 AND $key == 'gstickthread') ||
			($this->thread['sticky'] == 1 AND $key == 'stickthread') ||
			($this->thread['sticky'] == 0 AND $key == 'unstickthread') ||
			($this->thread['visible'] == 1 AND $key == 'approve') ||
			($this->thread['visible'] == 0 AND $key == 'unapprove')) {
			return '';
		}

		return "<option value='".$mod_action."'>".$value."</option>\n";
	}

	function can_moderate($fid = 0)
	{
		global $bbuserinfo;
		$return = false;
		if ($bbuserinfo['supermod'] OR ($fid AND $bbuserinfo['is_mod'] AND $bbuserinfo['_moderator'][$fid]['canmoderateposts']))
		{
			$return = true;
		}
		return $return;
	}
}

$output = new showthread();
$output->show();

?>