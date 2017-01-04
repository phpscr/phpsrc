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
if(!defined('IN_MXB')) exit('Access denied.Sorry, you can not access this file directly.');
class cron_award_promotion
{
	var $class;
	var $cron = "";

	function docron()
	{
		global $DB, $forums;
		$forums->lang = $forums->func->load_lang($forums->lang, 'cron' );

		$promotions = $DB->query("SELECT id, gender, date, onlinetime, posts, reputation, strategy FROM ".TABLE_PREFIX."award WHERE used = 1" );

		if ($DB->num_rows($promotions)) {
			while ($promotion = $DB->fetch_array($promotions)) {
				$promotion['onlinetime'] = $promotion['onlinetime'] ? $promotion['onlinetime'] * 3600 : 0;
				$p_list[] = $promotion;	
			}
			$users = $DB->query("SELECT u.joindate, u.id, u.posts, u.onlinetime, u.award_data, u.reputation, u.name, u.gender FROM ".TABLE_PREFIX."user u WHERE u.lastactivity >= ".(time() - 3600*24)."" );
			
			while ($user = $DB->fetch_array($users) ) {
				foreach ($p_list AS $promotion) {
					$dojoin= FALSE;
					$reg_time = intval( time() - $promotion['date'] * 86400 );
					if ( preg_match("/,".$promotion['id'].",/i", ",".$user['award_data'].",") ) {
						continue;
					}
					if ($promotion['gender'] AND $promotion['gender'] != $user['gender']) {
						continue;
					}
					if ($user['onlinetime'] < $promotion['onlinetime'] AND $promotion['onlinetime']) {
						continue;
					}
					switch ($promotion['strategy']) {
						case '17':
							if ($user['posts'] > $promotion['posts']) {
								$dojoin= TRUE;
							}
							break;
						case '18':
							if ($user['joindate'] < $reg_time) {
								$dojoin= TRUE;
							}
							break;
						case '19':
							if ($user['reputation'] > $promotion['reputation']) {
								$dojoin= TRUE;
							}
							break;
						case '1':
							if ($user['posts'] > $promotion['posts'] AND $user['reputation'] > $promotion['reputation'] AND $user['joindate'] < $reg_time) {
								$dojoin= TRUE;
							}
							break;
						case '2':
							if ($user['posts'] > $promotion['posts'] OR $user['reputation'] > $promotion['reputation'] OR $user['joindate'] < $reg_time) {
								$dojoin= TRUE;
							}
							break;
						case '3':
							if (($user['posts'] > $promotion['posts'] AND $user['joindate'] < $reg_time) OR $user['reputation'] > $promotion['reputation']) {
								$dojoin= TRUE;
							}
							break;
						case '4':
							if (($user['posts'] > $promotion['posts'] OR $user['joindate'] < $reg_time) AND $user['reputation'] > $promotion['reputation']) {
								$dojoin= TRUE;
							}
							break;
						case '5':
							if ($user['posts'] > $promotion['posts'] AND ($user['reputation'] > $promotion['reputation'] OR $user['joindate'] < $reg_time )) {
								$dojoin= TRUE;
							}
							break;
						case '6':
							if ($user['posts'] > $promotion['posts'] OR ($user['reputation'] > $promotion['reputation'] AND $user['joindate'] < $reg_time )) {
								$dojoin= TRUE;
							}
							break;
						case '7':
							if (($user['posts'] > $promotion['posts'] OR $user['reputation'] > $promotion['reputation']) AND $user['joindate'] < $reg_time ) {
								$dojoin= TRUE;
							}
							break;
						case '8':
							if (($user['posts'] > $promotion['posts'] AND $user['reputation'] > $promotion['reputation']) OR $user['joindate'] < $reg_time ) {
								$dojoin= TRUE;
							}
							break;
					}
					if ($dojoin) {
						$new_aid[] = $promotion['id'];
					}
				}
				if (is_array($new_aid)) {
					if ($user['award_data']) {
						$update_data = $user['award_data'] . implode(",", $new_aid).",";
					} else {
						$update_data = "," . implode(",", $new_aid).",";
					}
					$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET award_data='".$update_data."' WHERE id=".$user['id']."");
				}
				unset ($new_aid);
			}
		}
		$this->class->cronlog( $this->cron, $forums->lang['awardpromotion'] );		
	}

	function register_class($class)
	{
		$this->class = $class;
	}

	function pass_cron( $this_cron )
	{
		$this->cron = $this_cron;
	}
}

?>