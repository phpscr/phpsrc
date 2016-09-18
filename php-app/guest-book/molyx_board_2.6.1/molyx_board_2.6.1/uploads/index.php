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
define('THIS_SCRIPT', 'index');
require ('./global.php');

class index {
    function show()
    {
        global $forums, $DB, $bbuserinfo, $bboptions, $_INPUT;
		$forums->lang = $forums->func->load_lang($forums->lang, 'index');
		$forums->forum->forums_init();
        $sep_char = ',';
        if (!$bbuserinfo['id']) {
        	$bbuserinfo['lastvisit'] = TIMENOW;
        } else {
			$forums->func->check_cache('banksettings');
			$varusedintemplate = array("bankcurrency" => $forums->cache['banksettings']['bankcurrency']);
		}
		if ($forums->forum->foruminfo[$bboptions['newsforumid']]['lastthreadid'] AND $bboptions['shownewslink']) {
			$newstitle = $forums->forum->foruminfo[$bboptions['newsforumid']]['lastthread'];
			$newsid = $forums->forum->foruminfo[ $bboptions['newsforumid'] ]['lastthreadid'];
			$show['news'] = TRUE;
		}
        $lastvisit = $forums->func->get_date( $bbuserinfo['lastvisit'], 2 );
		$forums->func->check_cache('credit');
		if ($forums->cache['credit']['list']) {
			$expand_credit = "<br />";
			foreach ($forums->cache['credit']['list'] AS $tag_name => $name) {
				$credit[] = $name . ": <strong>" . $bbuserinfo[$tag_name] . "</strong>";
			}
			$expand_credit .= implode( " / ", $credit);
		}
		$forums->func->check_cache('stats');
		if ($bboptions['showstatus']) {
			$show['stats'] = TRUE;
			$totalthreads = $forums->func->fetch_number_format($forums->cache['stats']['totalthreads']);
			$totalposts = $forums->func->fetch_number_format($forums->cache['stats']['totalposts']);
			$todaypost = $forums->func->fetch_number_format($forums->cache['stats']['todaypost']);
			$numbermembers = $forums->func->fetch_number_format($forums->cache['stats']['numbermembers']);
		}
		$newuserid = $forums->cache['stats']['newuserid'];
		$newusername = $forums->cache['stats']['newusername'];
		if ($bboptions['showloggedin']) {
			$show['stats'] = TRUE;
			$online = array(
				'username' => '',
				'guests' => 0 ,
				'users' => 0 ,
				'invisible' => 0 ,
			);
			$maxonline = $forums->func->fetch_number_format($forums->cache['stats']['maxonline']);
			$maxonlinedate = $forums->func->get_date( $forums->cache['stats']['maxonlinedate'], 2 );
			$cutoff = $bboptions['cookietimeout'] != "" ? $bboptions['cookietimeout'] : '15';
			$oltime = TIMENOW - $cutoff * 60;
			$rows = array(0 => array(
				'invisible' => $bbuserinfo['invisible'],
				'lastactivity' => TIMENOW,
				'userid' => $bbuserinfo['id'],
				'username' => $bbuserinfo['name'],
				'usergroupid' => $bbuserinfo['usergroupid'])
			);
			if ( isset($_INPUT['online']) ) {
				switch ( $_INPUT['online'] ) {
					case 'hide':
						$forums->func->set_cookie('online', '-1');
						$hideonline = 1;
						break;
					case 'show':
						$forums->func->set_cookie('online', '0', -1 );
						$hideonline = 0;
						break;
				}
			} else {
				$hideonline = $forums->func->get_cookie('online');
			}
			$online = $DB->query_first("SELECT COUNT(sessionhash) AS count FROM ".TABLE_PREFIX."session WHERE lastactivity > $oltime");
			$totalonline = $online['count'];
			if ( !$hideonline ) {
				if ($totalonline > $bboptions['maxonlineusers'] && !isset($_INPUT['online'])) {
					$hideonline = true;
				} else {
					$rows = ($rows[0]['userid'] > 0) ? $rows : array();
					if (!$bboptions['showguest']) {
						$sql = ' userid > 0 AND ';
					}
					$forums->func->check_cache('usergroup');
					$cached = array();
					$online['users'] = $online['guests'] = $online['invisible'] = $count = $guest_mobile = 0;
					$DB->query("SELECT sessionhash, userid, username, invisible, lastactivity, usergroupid, mobile FROM ".TABLE_PREFIX."session WHERE $sql lastactivity > $oltime ORDER BY lastactivity DESC");
					while ($r = $DB->fetch_array()) {
						$rows[] = $r;
					}
					foreach ($rows as $result) {
						$result['lastactivity'] = $forums->func->get_time( $result['lastactivity'] );
						if ($result['userid'] == 0 ) {
							if ($result['mobile']) {
								$guest_mobile++;
							}
							$online['guests']++;
						} else {
							if ( empty( $cached[ $result['userid'] ] ) ) {
								$cached[ $result['userid'] ] = 1;
								$result['opentag'] = $forums->cache['usergroup'][ $result['usergroupid'] ]['opentag'];
								$result['closetag'] = $forums->cache['usergroup'][ $result['usergroupid'] ]['closetag'];
								$result['usericon'] = $forums->cache['usergroup'][ $result['usergroupid'] ]['onlineicon'] ? $forums->cache['usergroup'][ $result['usergroupid'] ]['onlineicon'] : 0;
								$result['mobile'] = $result['mobile'] ? 1 : 0;
								if ($result['invisible']) {
									if ( $bbuserinfo['usergroupid'] == 4) {
										$result['show_icon'] = 1;
										$online['username'][] = $result;
									}
									$online['invisible']++;
								} else {
									$online['username'][] = $result;
								}
								$online['users']++;
							}
						}
					}
					if ($bboptions['showguest']) {
						$temp['userid'] = 0;
						$temp['guesticon'] = $forums->cache['usergroup'][2]['onlineicon'] ? $forums->cache['usergroup'][2]['onlineicon'] : '';
						for($i=0; $i<$online['guests']; $i++) {
							$temp['mobile'] = ($guest_mobile AND $i < $guest_mobile) ? 1 : 0;
							$temp['username'] = $forums->lang['_guset'];
							$online['username'][] = $temp;
						}
					}
					if (!$online['guests']) {
						$online['guests'] = $totalonline - $online['users'];
					}
					if ($online['users'] > 500) {
						$forums->func->set_cookie('online', '0', -1 );
					}
					$forums->lang['onlineusers'] = sprintf( $forums->lang['onlineusers'], $online['guests'], $online['users'], $online['invisible'], $maxonline, $maxonlinedate );
				}
			}
			$forums->lang['onlineclosed'] = sprintf( $forums->lang['onlineclosed'], $totalonline, $maxonline, $maxonlinedate );
		}
		if ($totalonline > $forums->cache['stats']['maxonline']) {
			$forums->cache['stats']['maxonline'] = $totalonline;
			$forums->cache['stats']['maxonlinedate']  = TIMENOW;
			$forums->func->update_cache(array('name' => 'stats'));
		}
		if($bboptions['openolrank']){
			$forums->func->check_cache('olranks');
			$bbuserinfo['onlinerankimg'] = $forums->func->fetch_online_time(array('id' => $bbuserinfo['id'], 'onlinetime' => $bbuserinfo['onlinetime']));
		}
		if ($bboptions['showbirthday']) {
			$show['stats'] = TRUE;
			$birthusers = "";
			$bcount = 0;
			$users = array();
			$forums->func->check_cache('birthdays');
			if ( is_array( $forums->cache['birthdays'] ) ) {
				foreach( $forums->cache['birthdays'] AS $id => $u ) {
					$users[] = $u;
				}
			}
			foreach ( $users AS $id => $user ) {
				$birthusers .= "<a href='profile.php?{$forums->sessionurl}u={$user['id']}'>{$user['name']}</a>";
				$year = explode( '-', $user['birthday'] );
				if ($year[0] != '0000') {
					$today = $forums->func->get_time(TIMENOW, 'Y');
					$age = $today - $year[0];
					$birthusers .= "(<strong>$age</strong>)";
				}
				$birthusers .= $sep_char."\n";
				$bcount++;
			}
			$show['birthday'] = TRUE;
            if ($bcount < 1 AND $bboptions['autohidebirthday']) {
                 $show['birthday'] = FALSE ;
            }
			$birthusers = preg_replace( "/".$sep_char."$/", "", trim($birthusers) );
			$forums->lang['todaybirthdays'] = sprintf( $forums->lang['todaybirthdays'], $bcount );
		}
		$forums->func->check_cache('league');
		if (is_array($forums->cache['league'])) {
			$show['league'] = TRUE;
			$league = $forums->cache['league'];
		}
		$rsslink = true;
		$pagetitle = $bboptions['bbtitle'];
		include $forums->func->load_template('index');
		exit;
	}
}

$output = new index();
$output->show();

?>