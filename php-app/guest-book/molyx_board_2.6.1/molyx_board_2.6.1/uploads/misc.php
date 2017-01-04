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
define('THIS_SCRIPT', 'misc');
require_once('./global.php');

class misc {

    function show()
	{
        global $_INPUT, $forums;
		$forums->lang = $forums->func->load_lang($forums->lang, 'misc');
		require_once(ROOT_PATH."includes/xfunctions_hide.php");
        $this->hidefunc = new hidefunc();
        require_once(ROOT_PATH."includes/xfunctions_bank.php");
        $this->bankfunc = new bankfunc();
    	switch($_INPUT['do'])
    	{
			case 'show_voters':
    			$this->show_voters();
    			break;
			case 'forumread':
    			$this->forumread();
    			break;
    		case 'allforumread':
    			$this->allforumread();
    			break;
    		case 'icon':
    			$this->show_icon();
    			break;
    		case 'bbcode':
    			$this->show_bbcode();
    			break;
			case 'privacy':
    			$this->privacy();
    			break;
			case 'rss':
    			$this->rss();
    			break;
			case 'whobought':
                $this->whobought();
                break;
            case 'buyhidden':
                $this->buyhidden();
                break;
    		default:
    			$this->show_icon();
    			break;
    	}
 	}

	function show_voters()
	{
        global $forums, $DB, $bbuserinfo, $bboptions, $_INPUT;
		$pollid = intval($_INPUT['pollid']);
        if ( !$pollid ) {
            $errmsg = $forums->lang['cannotfindpost'];
        }
        $data = $DB->query_first("SELECT pollid, voters FROM ".TABLE_PREFIX."poll WHERE pollid = ".$pollid." LIMIT 0, 1");
        if ( !$data['pollid'] ) {
            $errmsg = $forums->lang['cannotfindpost'];
        } else {
			if (!$data['voters']) {
				$errmsg = $forums->lang['no_voters'];
			} else {
				$voters = explode(",", $data['voters']);
				foreach ($voters AS $userid) {
					if (!$userid) continue;
					$userid = intval($userid);
					$all_voters[] = $userid;
				}
				$DB->query("SELECT id, name FROM ".TABLE_PREFIX."user WHERE id IN (".implode(", ", $all_voters).")");
				if ($DB->num_rows()) {
					$all_polls = 0;
					while ($user = $DB->fetch_array()) {
						$all_polls++;
						$pollvoters[] = $user;
					}
					$forums->lang['allvoters'] = sprintf( $forums->lang['allvoters'], $all_polls );
				} else {
					$errmsg = $forums->lang['no_voters'];
				}
			}
		}
        $pagetitle = $forums->lang['whovotes']." - ".$bboptions['bbtitle'];
        include $forums->func->load_template('misc_whovoters');
        exit;
	}

	function whobought()
    {
        global $forums, $DB, $bbuserinfo, $bboptions, $_INPUT;
		$pid = intval($_INPUT['pid']);
        if ( !$pid ) {
            $errmsg = $forums->lang['cannotfindpost'];
        }
        $data = $DB->query_first("SELECT hidepost FROM ".TABLE_PREFIX."post WHERE pid = ".$pid." LIMIT 0, 1");
        if ( !$data['hidepost'] ) {
            $errmsg = $forums->lang['norecords'];
        } else {
			$hideinfo = unserialize($data['hidepost']);
			$buyers = $hideinfo['buyers'];
			$totalbuyers = count($buyers);
			$forums->lang['allbuyers'] = sprintf( $forums->lang['allbuyers'], $totalbuyers );
		}
        $pagetitle = $forums->lang['whobuypost']." - ".$bboptions['bbtitle'];
        include $forums->func->load_template('misc_whobought');
        exit;
    }

    function buyhidden()
    {
        global $forums, $DB, $bbuserinfo, $_INPUT;
		$pid = intval($_INPUT['pid']);
		$tid = intval($_INPUT['tid']);
        if ( !$pid OR !$tid ) {
            $forums->func->standard_error("cannotfindpost");
        }
        $data = $DB->query_first("SELECT hidepost, userid FROM ".TABLE_PREFIX."post WHERE pid = ".$pid." LIMIT 1");
        if ( !$data['hidepost'] ) {
            $forums->func->standard_error("nohidepost");
        }
        if ( !$data['userid'] ) {
            $forums->func->standard_error("noauthor");
        }
        $hideinfo = unserialize($data['hidepost']);
        if ( $hideinfo['type'] != 1 AND $hideinfo['type'] != 2 AND $hideinfo['type'] != 999 ) {
            $forums->func->standard_error("cannotbuy");
        }
        if ( in_array($bbuserinfo['name'], $hideinfo['buyers']) ) {
            $forums->func->standard_error("haspurchase");
        }
        if ( $bbuserinfo['cash'] < $hideinfo['cond'] ) {
            $forums->func->standard_error("noenoughmoney");
        }
		if ($hideinfo['type'] == 999 ) {
			$forums->func->check_cache('credit');
			if (!$forums->cache['credit']['list'][$hideinfo['credit_type']]) {
				$forums->func->standard_error("errorcredit");
			}
			if ($bbuserinfo[$hideinfo['credit_type']] < $hideinfo['cond']) {
				$forums->func->standard_error("noenoughcredit", 0, $forums->cache['credit']['list'][$hideinfo['credit_type']]);
			}
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."userexpand SET {$hideinfo['credit_type']} = {$hideinfo['credit_type']} - {$hideinfo['cond']} WHERE id = ".$bbuserinfo['id']."" );
		} else {
			$bbuserinfo = $this->bankfunc->patch_bankinfo();
			$tarinfo = $DB->query_first("SELECT u.id, u.name, u.cash, u.bank, u.mkaccount
						   FROM ".TABLE_PREFIX."user u
						   WHERE u.id = ".$data['userid']);
			if ( !$tarinfo || !is_array($tarinfo) || !$tarinfo['id'] ) {
				$forums->func->standard_error("nouserid");
			}

			$this->bankfunc->trdesc = $forums->lang['buypost']." [<a href='redirect.php?goto=findpost&p=$pid'>{$forums->lang['view']}</a>]";
			$this->bankfunc->fromCorB = 0;
			$this->bankfunc->tarCorB = 0;
			$this->bankfunc->meextra = 0;
			$this->bankfunc->tarextra = 0;

			if ( !$this->bankfunc->user_transfer_money($tarinfo, $hideinfo['cond']) ) {
				$forums->func->standard_error("errormoney");
			}
		}
        $hideinfo['buyers'][] = $bbuserinfo['name'];
        $hideinfo = addslashes(serialize($hideinfo));
        $DB->shutdown_query("UPDATE ".TABLE_PREFIX."post SET hidepost = '".$hideinfo."' WHERE pid = ".$pid." LIMIT 1");
        $forums->func->standard_redirect('showthread.php?'.$forums->sessionurl.'t='.$tid);
        exit;
    }

	function rss()
 	{
 		global $forums, $DB, $bboptions, $bbuserinfo, $_INPUT;
		$forums->forum->forums_init();
		$showforum = $forums->forum->forum_jump(1, 1);
		if ($_INPUT['update']) {
			$extra = array();
			$forumlist = $this->get_forums();
			if ($forumlist) $extra[] = "fid=".$forumlist;
			if ($_INPUT['version'] != 'rss') $extra[] = "version=".$_INPUT['version'];
			if (intval($_INPUT['limit'])) $extra[] = "limit=".intval($_INPUT['limit']);
			$message = intval($_INPUT['limit']) > 100 ? $forums->lang['rsslimit'] : $bboptions['bburl']."/rss.php?".implode("&amp;", $extra);
		} else {
			if ( ! $_INPUT['f'] ) {
				$selected = " selected='selected'";
			}
		}
		$pagetitle = $forums->lang['rss']." - ".$bboptions['bbtitle'];
		$nav[] = $forums->lang['rss'];
		include $forums->func->load_template('misc_rss');
		exit;
 	}

    function get_forums()
    {
    	global $forums, $_INPUT;
		$forums->forum->forums_init();
    	$forumids = array();
		if ( $_INPUT['forumlist'] != '' ) {
			foreach( $forums->forum->foruminfo AS $id => $data ) {
				if (in_array($data['id'], $_INPUT['forumlist'])) {
    				$forumids[] = $data['id'];
				}
    		}
		}
    	return implode( "," , $forumids );
    }

	function privacy()
 	{
 		global $forums, $DB, $bboptions, $bbuserinfo, $_INPUT;
		if ( ! $bboptions['showprivacy'] ) {
			$forums->func->standard_redirect();
		}
		if ( $bboptions['privacyurl'] ) {
			$forums->func->standard_redirect($bboptions['privacyurl']);
		} else {
			$privacy = $DB->query_first("SELECT value FROM ".TABLE_PREFIX."setting WHERE varname='privacytext'");
			$privacytext = str_replace("\n", '<br />', $privacy['value']);
			$pagetitle = $bboptions['privacytitle']." - ".$bboptions['bbtitle'];
			$nav[] = $bboptions['privacytitle'];
			include $forums->func->load_template('misc_privacy');
			exit;
		}
 	}

 	function show_icon()
 	{
 		global $forums, $DB, $bboptions, $bbuserinfo, $_INPUT;
		$forums->func->check_cache('smile');
		$emoticons = $forums->cache['smile'];
		$pagetitle = $forums->lang['smiles']." - ".$bboptions['bbtitle'];
		include $forums->func->load_template('misc_icons');
		exit;
 	}

 	function show_bbcode()
 	{
 		global $forums, $DB, $bboptions, $bbuserinfo;
 		require_once(ROOT_PATH.'includes/functions_codeparse.php');
 		$this->parser = new functions_codeparse();
 		$bbcode = array(
						'[B]' => '[b]'.$forums->lang['boldsample'].'[/b]',
						'[I]'=> '[i]'.$forums->lang['italicsample'].'[/i]',
						'[U]' => '[u]'.$forums->lang['underlinesample'].'[/u]',
						'[S]' => '[s]'.$forums->lang['strikethroughsample'].'[/s]',
						'[EMAIL]' => '[email]user@domain.com[/email]',
						'[EMAIL=xxx]'  => '[email=user@domain.com]'.$forums->lang['emailsample'].'[/email]',
						'[URL]' => '[url]http://www.domain.com[/url]',
						'[URL=xxx]' => '[url=http://www.domain.com]'.$forums->lang['websitesample'].'[/url]',
						'[SIZE]' => '[size=7]'.$forums->lang['sizesample'].'[/size]',
						'[FONT]' => '[font=simli]'.$forums->lang['fontsample'].'[/font]',
						'[COLOR]' => '[color=red]'.$forums->lang['colorsample'].'[/color]',
						'[IMG]' => '[img]http://www.google.com/images/logo.gif[/img]',
						'[LIST]' => '[list][*]'.$forums->lang['listsample'].' [*]'.$forums->lang['listsample'].'[/list]',
						'[LIST=1]' => '[list=1][*]'.$forums->lang['listsample'].' [*]'.$forums->lang['listsample'].'[/list]',
						'[LIST=a]' => '[list=a][*]'.$forums->lang['listsample'].' [*]'.$forums->lang['listsample'].'[/list]',
						'[LIST=i]' => '[list=i][*]'.$forums->lang['listsample'].' [*]'.$forums->lang['listsample'].'[/list]',
						'[QUOTE]' => '[quote]'.$forums->lang['quotesample'].'[/quote]',
						'[CODE]' => '[code]&lt;a href=&quot;test/page.html&quot;&gt;A Test Page&lt;/a&gt;[/code]',
					  );
		foreach( $bbcode AS $k => $v ) {
			$code['title'] = $k;
			$code['before'] = $v;
			$code['change'] = $this->parser->convert( array( 'text' => $v, 'allowcode' => 1 ) );
			$ori[] = $code;
		}
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."bbcode");
		while ( $row = $DB->fetch_array() ) {
			$code['title'] = '['.$row['bbcodetag'].']';
			$code['desc'] = $row['description'];
			$code['before']  = $row['bbcodeexample'];
			$code['change'] = $this->parser->parse_bbcode($row['bbcodereplacement']);
			$new[] = $code;
		}
		$codelist = array_merge($ori, $new);
		$pagetitle = $forums->lang['bbcode']." - ".$bboptions['bbtitle'];
		include $forums->func->load_template('misc_bbcode');
		exit;
 	}

	function allforumread()
 	{
 		global $forums, $DB, $bbuserinfo, $bboptions;
 		if(!$bbuserinfo['id']) {
			$forums->func->standard_error("notlogin");
		}
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET lastvisit=".TIMENOW.", lastactivity=".TIMENOW." WHERE id=".$bbuserinfo['id']."");
		$forums->func->standard_redirect();
	}

    function forumread()
    {
        global $forums, $_INPUT, $bboptions;
        $fid = intval($_INPUT['f']);
        if ( !$fid ) {
        	$forums->func->standard_error("cannotfindforum");
        }
		$forum = $forums->forum->single_forum( $fid );
        if ( !$forum['id'] ) {
        	$forums->func->standard_error("cannotfindforum");
        }
        $children = $forums->forum->forums_get_children( $forum['id'] );
        $forums->forum_read[ $forum['id'] ] = TIMENOW;
		$forums->func->forumread(1);
        if ( count($children) ) {
        	$forums->func->standard_redirect("forumdisplay.php?{$forums->sessionurl}f=".$forum['parentid']);
        } else {
        	$forums->func->standard_redirect();
        }
    }
}

$output = new misc();
$output->show();

?>