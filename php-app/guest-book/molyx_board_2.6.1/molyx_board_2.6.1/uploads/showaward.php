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
define('THIS_SCRIPT', 'showaward');

require_once('./global.php');

class showaward {

	var $more_info = FALSE;
	var $reduce_money = 0;
	var $reduce_reputation = 0;

    function show()
    {
    	global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'award' );
		$forums->func->check_cache('award');
		$r = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."setting WHERE varname = 'award_deduct' LIMIT 0, 1");
		if ($r['value'] OR $r['defaultvalue']) {
			$reduce = $r['value'] ? $r['value'] : $r['defaultvalue'];
			$this->more_info = TRUE;
			$reduce = explode("|", $reduce);
			$this->reduce_money = intval($reduce[0]);
			$this->reduce_reputation = intval($reduce[1]);
		}
    	switch($_INPUT['do'])
    	{
			case 'request':
    			$this->request();
    			break;
    		default:
    			$this->show_award();
    			break;
    	}
 	}

	function request()
	{
        global $forums, $DB, $bbuserinfo, $bboptions, $_INPUT;
		$id = intval($_INPUT['id']);
        if ( !$id OR !is_array($forums->cache['award'][$id]) ) {
            $errmsg = $forums->lang['cannotfindaward'];
        }
		if (!$bbuserinfo['id']) {
			$errmsg = $forums->lang['must_loggin'];
		} else {
			$award = explode(",", $bbuserinfo['award_data']);
			if (in_array($id, $award)) {
				$errmsg = $forums->lang['has_awards'];
			}
			$DB->query( "SELECT * FROM ".TABLE_PREFIX."award_request WHERE aid=$id AND uid=".$bbuserinfo['id']."" );
			if ($DB->num_rows()) {
				$errmsg = $forums->lang['has_request_awards'];
			}
			if ($this->more_info) {
				if (($this->reduce_money AND $bbuserinfo['cash'] < $this->reduce_money) OR ($this->reduce_reputation AND $bbuserinfo['reputation'] < $this->reduce_reputation) ) {
					$errmsg = $forums->lang['no_cash_rep']."<br /><br />";
					$errmsg .= sprintf($forums->lang['require_rep'], $this->reduce_reputation, $bbuserinfo['reputation'] )."<br />";
					$errmsg .= sprintf($forums->lang['require_money'], $this->reduce_money, $bbuserinfo['cash'] );
				} else {
					$extra = "<br />".sprintf($forums->lang['award_will_reduce'], $this->reduce_money, $this->reduce_reputation );
				}
			}
			if ($_INPUT['update'] AND !$errmsg) {
				$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."award_request (aid, uid, post, dateline) VALUES (".$id.", ".$bbuserinfo['id'].", '".addslashes($_INPUT['post'])."', ".TIMENOW.")");
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET cash=cash-".$this->reduce_money.", reputation=reputation-".$this->reduce_reputation." WHERE id=".$bbuserinfo['id']."");
				$errmsg = $forums->lang['has_request_awards'];
			}
		}
		$pagetitle = $forums->lang['awardlist']." - ".$bboptions['bbtitle'];
        include $forums->func->load_template('award_request');
        exit;
	}
 	
 	function show_award()
 	{
		global $forums, $DB, $bbuserinfo, $_INPUT, $bboptions;
		$showtop = FALSE;
		$pp = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		if ($_INPUT['id']) {

			$_INPUT['id'] = intval($_INPUT['id']);

			$all = $DB->query_first("SELECT COUNT(id) as count FROM ".TABLE_PREFIX."user WHERE award_data != '' AND award_data LIKE '%,".$_INPUT['id'].",%'");

			$link = $forums->func->build_pagelinks( array( 'totalpages'  => $all['count'],
											'perpage'    => 30,
											'curpage'  => $pp,
											'pagelink'    => "showaward.php?{$forums->sessionurl}id=".$_INPUT['id'],
										  )
								   );

			$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE award_data != '' AND award_data LIKE '%,".$_INPUT['id'].",%' ORDER BY length(award_data) DESC, lastactivity DESC LIMIT {$pp}, 30");

			$showtitle = sprintf($forums->lang['award_list'], $forums->cache['award'][$_INPUT['id']]['name']);

		} else if ($_INPUT['showall'] == 1) {

			$all = $DB->query_first("SELECT COUNT(id) as count FROM ".TABLE_PREFIX."user WHERE award_data != ''");

			$link = $forums->func->build_pagelinks( array( 'totalpages'  => $all['count'],
											'perpage'    => 30,
											'curpage'  => $pp,
											'pagelink'    => "showaward.php?{$forums->sessionurl}showall=1",
										  )
								   );

			$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE award_data != '' ORDER BY length(award_data) DESC, lastactivity DESC LIMIT {$pp}, 30");

			$showtitle = $forums->lang['all_award_list'];
		} else {
			$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE award_data != '' ORDER BY length(award_data) DESC, lastactivity DESC LIMIT 0, 10");
			$showtop = TRUE;
			$showtitle = $forums->lang['toptenlist'];
			$showtitle .= "[ <a href='showaward.php?{$forums->sessionurl}showall=1'>" . $forums->lang['view_all_users'] . "</a> ]";
		}
		if ($DB->num_rows()) {
			while ($user = $DB->fetch_array()) {
				$user =  $forums->func->fetch_user($user);
				$userlist[] = $user;
			}
		} else {
			$no_awards = TRUE;
		}
		
		$pagetitle = $forums->lang['_award']." - ".$bboptions['bbtitle'];
		$nav = array( "<a href='showaward.php?{$forums->sessionurl}'>" . $forums->lang['_award'] . "</a>" );
        include $forums->func->load_template('showaward');
        exit;
 	}	 
}

$output = new showaward();
$output->show();

?>