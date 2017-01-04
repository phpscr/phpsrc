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
class session {

    var $user = array();
    var $sessionid = 0;
    var $badsessionid = 0;

    function loadsession()
    {
        global $DB, $_INPUT, $forums, $bboptions, $_USEROPTIONS;
        if ($bboptions['loadlimit'] > 0) {
        	if (substr(PHP_OS, 0, 3) != 'WIN' AND file_exists('/proc/loadavg') AND $data = @file_get_contents('/proc/loadavg')) {
        		$load_avg = explode(' ', $data);
        		$forums->server_load = trim($load_avg[0]);
        		if ($forums->server_load > $bboptions['loadlimit']) {
        			$forums->func->standard_error("loadlimit");
				}
        	} else if ($serverstats = @exec('uptime')) {
				preg_match("/(?:averages)?\: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $serverstats, $load);
				$forums->server_load = $load[1];
				if ($forums->server_load > $bboptions['loadlimit']) {
    				$forums->func->standard_error("loadlimit");
				}
			}
        }
		$forums->func->check_cache('banfilter');
		if (is_array($forums->cache['banfilter']) AND $forums->cache['banfilter']) {
			foreach ($forums->cache['banfilter'] as $banip) {
				$banip = str_replace('\*', '.*', preg_quote($banip, '/'));
				if ( preg_match("/^$banip$/", ALT_IP)) {
					$forums->func->standard_error('banip');
				}
			}
		}
        $this->user = array(
        	'id' => 0,
        	'name' => '',
        	'usergroupid' => 2,
        	'timezoneoffset' => $bboptions['timezoneoffset']
        );
		if (THIS_SCRIPT === 'cron') {
        	$this->user = $forums->func->set_up_guest();
			$forums->func->check_cache('usergroup_2', 'usergroup');
			$forums->perm_id_array = array();
        	$_INPUT['lastactivity'] = TIMENOW;
			$_INPUT['lastvisit'] = TIMENOW;
			return $this->user;
        }
		if (preg_match('/('.$bboptions['spiderid'].')/i', $_SERVER['HTTP_USER_AGENT'], $match)) {
			$this->user = $forums->func->set_up_guest();
			$forums->func->check_cache("usergroup_{$bboptions['spider_roup']}", 'usergroup');
			$this->user = array_merge( $this->user, $forums->cache["usergroup_{$bboptions['spider_roup']}"] );
			$this->build_group_permissions();
			$forums->sessiontype = 'cookie';
			$bot_agent = trim($match[0]);
			$DB->shutdown_query("DELETE FROM ".TABLE_PREFIX."session WHERE useragent = '".$DB->escape_string(USER_AGENT)."' AND host='".$DB->escape_string(SESSION_HOST)."'");
			$this->sessionid = md5(uniqid(microtime()));
			$DB->shutdown_query("INSERT IGNORE INTO ".TABLE_PREFIX."session
				(sessionhash, username, userid, usergroupid, inforum, inthread, inblog, invisible, lastactivity, location, host, useragent, badlocation, mobile) VALUES
				('".$this->sessionid."', '".$bot_agent."', 0, ".$bboptions['spider_roup'].', '.intval($_INPUT['f']).', '.intval($_INPUT['t']).', '.intval($_INPUT['bid']).", '0', '".TIMENOW."', '".$DB->escape_string(WOLPATH)."', '".$DB->escape_string(SESSION_HOST)."', '".$DB->escape_string(USER_AGENT)."', 0, 0)");
			return $this->user;
		}
        $cookie = array();
        $cookie['sessionid'] = $forums->func->get_cookie('sessionid');
        $cookie['userid'] = $forums->func->get_cookie('userid');
        $cookie['password'] = $forums->func->get_cookie('password');
        if ($cookie['sessionid']) {
        	$this->get_session($cookie['sessionid']);
        	$forums->sessiontype = 'cookie';
        } elseif ($_INPUT['s']) {
        	$this->get_session($_INPUT['s']);
        	$forums->sessiontype = 'url';
        } else {
        	$this->sessionid = 0;
        }
		if ($this->sessionid) {
			if ($this->userid != 0 AND !empty($this->userid)) {
				$this->load_user($this->userid);
				if (!$this->user['id'] OR $this->user['id'] == 0) {
					$this->unload_user();
					$this->update_guest_session();
				} else {
					$this->update_user_session();
				}
			} else {
				$this->update_guest_session();
			}
		} else {
			if ($cookie['userid'] != '' AND $cookie['password'] != '') {
				$this->load_user($cookie['userid']);
				if (!$this->user['id'] OR $this->user['id'] == 0) {
					$this->unload_user();
					$this->create_session();
				} else {
					if ($this->user['password'] == $cookie['password']) {
						$this->create_session($this->user['id']);
					} else {
						$this->unload_user();
						$this->create_session();
					}
				}
			} else {
				$this->create_session();
			}
		}
        if (!$this->user['id']) {
        	$this->user = $forums->func->set_up_guest();
        	$this->user['lastactivity'] = TIMENOW;
			$this->user['lastvisit'] = TIMENOW;
		}
		$forums->func->check_cache("usergroup_{$this->user['usergroupid']}", 'usergroup');
        $this->user = array_merge($this->user, $forums->cache["usergroup_{$this->user['usergroupid']}"]);
		$this->build_group_permissions();
		if ($this->user['usergroupid'] != 2) {
			if ($this->user['supermod'] == 1) {
				$this->user['is_mod'] = 1;
			} else {
				if (file_exists(ROOT_PATH.'cache/cache/moderator_user_'.$this->user['id'].'.php')) {
					include(ROOT_PATH.'cache/cache/moderator_user_'.$this->user['id'].'.php');
					foreach((array) $forums->cache['moderator_user_'.$this->user['id']] as $i => $r) {
						if ($this->user['caneditusers']) {
							$this->user['caneditusers'] = 1;
						}
						$this->user['_moderator'][$r['forumid']] = $r;
						$this->user['is_mod'] = 1;
					}
				}

				if (file_exists(ROOT_PATH.'cache/cache/moderator_group_'.$this->user['usergroupid'].'.php')) {
					include(ROOT_PATH.'cache/cache/moderator_group_'.$this->user['usergroupid'].'.php');
					foreach((array) $forums->cache['moderator_group_'.$this->user['usergroupid']] as $i => $r) {
						if ($this->user['caneditusers']) {
							$this->user['caneditusers'] = 1;
						}
						$this->user['_moderator'][$r['forumid']] = $r;
						$this->user['is_mod'] = 1;
					}
				}
			}
		}
        if ($this->user['id']) {
        	if (!$_INPUT['lastactivity']) {
				if ($this->user['lastactivity']) {
					$_INPUT['lastactivity'] = $this->user['lastactivity'];
				} else {
					$_INPUT['lastactivity'] = TIMENOW;
				}
        	}
        	if ( ! $_INPUT['lastvisit'] ) {
				if ($this->user['lastvisit']) {
					$_INPUT['lastvisit'] = $this->user['lastvisit'];
				} else {
					$_INPUT['lastvisit'] = TIMENOW;
				}
        	}
			if (!$this->user['lastvisit']) {
				$DB->shutdown_query("UPDATE LOW_PRIORITY ".TABLE_PREFIX."user SET lastvisit='".TIMENOW."', lastactivity='".TIMENOW."' WHERE id = ".$this->user['id']);
			} else if ((TIMENOW - $_INPUT['lastactivity']) > 300) {
				$this->user['options'] = $forums->func->convert_array_to_bits(array_merge($this->user , array('invisible' => $this->user['invisible'], 'loggedin' => 1)), $_USEROPTIONS);
				$useronline = TIMENOW - $_INPUT['lastactivity'];
				$bboptions['cookietimeout'] = $bboptions['cookietimeout'] ? $bboptions['cookietimeout'] * 60 : 3600;
				if($useronline > $bboptions['cookietimeout']) {
					$useronline = $bboptions['cookietimeout'];
				}
				$DB->shutdown_query("UPDATE LOW_PRIORITY ".TABLE_PREFIX."user SET options=".$this->user['options'].", lastactivity=".TIMENOW.", onlinetime=onlinetime+".$useronline."  WHERE id=".$this->user['id']."");
			}
			if ($this->user['liftban']) {
				$ban_arr = $forums->func->banned_detect(  $this->user['liftban'] );
				if ($ban_arr['timespan'] == -1) {
					$bbuserinfo = $this->user;
					$forums->func->standard_error("banuserever", 1);
				} else if ( TIMENOW >= $ban_arr['date_end'] ) {
					$DB->shutdown_query("UPDATE LOW_PRIORITY ".TABLE_PREFIX."user SET liftban='', usergroupid={$ban_arr['groupid']} WHERE id=".$this->user['id']."");
				} else {
					$bbuserinfo = $this->user;
					$forums->func->standard_error("banusertemp", 1, $forums->func->get_date($ban_arr['date_end'],2));
				}
			}
		}
        $forums->func->set_cookie('sessionid', $this->sessionid, 0);
        return $this->user;
    }

	function build_group_permissions() {
    	global $forums;
		$this->user['membergroupid'] = $this->user['usergroupid'];
    	if ( $this->user['membergroupids'] ) {
			$groups_id = explode( ',', $this->user['membergroupids'] );
			$exclude = array( 'usergroupid', 'grouptitle', 'groupicon', 'onlineicon', 'opentag', 'closetag' );
			$less_is_more = array( 'canappendedit', 'searchflood' );
			if ( count( $groups_id ) ) {
				foreach( $groups_id as $pid ) {
					$forums->func->check_cache("usergroup_{$pid}", 'usergroup');
					if ( $forums->cache["usergroup_{$pid}"]['usergroupid'] ) {
						$this->user['membergroupid'] .= ','.$pid;
						foreach( $forums->cache["usergroup_{$pid}"] AS $k => $v ) {
							if ( ! in_array( $k, $exclude ) ) {
								if ( in_array( $k, $less_is_more ) ) {
									if ( $v < $this->user[ $k ] ) {
										$this->user[ $k ] = $v;
									}
								} else {
									if ( $v > $this->user[ $k ] ) {
										$this->user[ $k ] = $v;
									}
								}
							}
						}
					}
				}
			}
			$rmp = array();
			$tmp = explode( ',', preg_replace( array("/,{2,}/", "/^,/", "/,$/"), array(",", "", ""), $this->user['membergroupid'] ) );
			if ( count( $tmp ) ) {
				foreach( $tmp AS $t ) {
					$rmp[ $t ] = $t;
				}
			}
			if ( count( $rmp ) ) {
				$this->user['membergroupid'] = implode( ',', $rmp );
			}
		}
        $forums->perm_id_array = explode( ",", $this->user['membergroupid'] );
    }

	function load_user($userid=0)
	{
    	global $DB, $forums, $_USEROPTIONS;
    	$userid = intval($userid);
     	if ($userid != 0) {
			$this->user = $DB->query_first("SELECT ue.*, u.*, ue.id AS expandid
				FROM ".TABLE_PREFIX."user u
					LEFT JOIN ".TABLE_PREFIX."userexpand ue
						ON u.id = ue.id
				WHERE u.id = $userid");
            if ($this->user['id']) {
				$this->user['options'] = intval($this->user['options']);
				foreach($_USEROPTIONS as $optionname => $optionval) {
					$this->user[$optionname] = $this->user['options'] & $optionval ? 1 : 0;
				}
				$this->check_userexpand();
            } else {
				$this->unload_user();
            }
		}
		unset($userid);
	}

	function check_userexpand()
	{
    	global $DB;
		if (!$this->user['expandid']) {
			$DB->query_unbuffered('INSERT INTO '.TABLE_PREFIX.'userexpand (id) VALUES ('.$this->user['id'].')');
		}
	}

	function unload_user()
	{
		global $forums;
		$forums->func->set_cookie('sessionid', 0, -1);
		$forums->func->set_cookie('userid', 0, -1);
		$forums->func->set_cookie('password', '', -1);
		$this->user['id'] = 0;
		$this->user['name'] = '';
	}

    function update_user_session() {
        global $DB, $_INPUT;
        if (!$this->sessionid) {
        	return $this->create_session($this->user['id']);
        }
        if (empty($this->user['id'])) {
        	$this->unload_user();
        	$this->create_session();
        	return;
        }
		$inforum = $_INPUT['t'] ? '' : ', inforum='.intval($_INPUT['f']);
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."session SET username='".$this->user['name']."', userid=".intval($this->user['id']).", usergroupid=".$this->user['usergroupid']."".$inforum.", inthread=".intval($_INPUT['t']).", invisible=".$this->user['invisible'].", lastactivity=".TIMENOW.", location='".$DB->escape_string(WOLPATH)."', badlocation=0, mobile=0 WHERE sessionhash='".$this->sessionid."'");
    }

	function update_guest_session() {
        global $DB, $_INPUT;
        if (!$this->sessionid) {
        	$this->create_session();
        	return;
        }
		$inforum = $_INPUT['t'] ? '' : ', inforum='.intval($_INPUT['f']);
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."session SET username='', userid=0, usergroupid=2".$inforum.", inthread=".intval($_INPUT['t']).", invisible=0, lastactivity='".TIMENOW."', location='".$DB->escape_string(WOLPATH)."', badlocation=0, mobile=0 WHERE sessionhash='".$this->sessionid."'");
    }

    function get_session($sessionid="") {
        global $DB, $bboptions;
        $result = array();
        $query = "";
        $sessionid = preg_replace("/([^a-zA-Z0-9])/", "", $sessionid);
		if ( $sessionid ) {
			if ($bboptions['matchbrowser']) {
				$query = " AND (useragent='".$DB->escape_string(USER_AGENT)."')";
			}
			if(!$result = $DB->query_first("SELECT sessionhash, userid, lastactivity, location FROM ".TABLE_PREFIX."session WHERE (sessionhash='".$sessionid."' AND host='".$DB->escape_string(SESSION_HOST)."')".$query) ) {
				$this->badsessionid = $sessionid;
				$this->sessionid = 0;
				$this->userid = 0;
			} else {
				$this->sessionid = $result['sessionhash'];
				$this->userid   = $result['userid'];
			}
			unset($result);
			return;
		}
    }

	function create_session($userid = 0) {
        global $DB, $_INPUT, $forums, $bboptions, $_USEROPTIONS;
		$cookietimeout = $bboptions['cookietimeout'] ? (time() - $bboptions['cookietimeout'] * 60) : (time() - 3600);
        if ($userid) {
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."session WHERE userid='".$this->user['id']."'");
			if ( TIMENOW - $this->user['lastactivity'] > 3600 ) {
				$forums->func->set_cookie('threadread', '');
				$this->user['options'] = $forums->func->convert_array_to_bits(array_merge($this->user , array('invisible' => $this->user['invisible'], 'loggedin' => 1)), $_USEROPTIONS);
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET options=".$this->user['options'].", lastvisit = lastactivity, lastactivity = '".TIMENOW."' WHERE id='".$this->user['id']."'");
				$_INPUT['lastvisit'] = $this->user['lastactivity'];
				$_INPUT['lastactivity'] = TIMENOW;
			}
		} else {
			$extra = ($this->badsessionid != 0) ? " OR sessionhash='".$this->badsessionid."'" : "";
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."session WHERE useragent='".$DB->escape_string(USER_AGENT)."' AND host='".$DB->escape_string(SESSION_HOST)."'".$extra);
			$this->user['name'] = '';
			$this->user['id'] = 0;
			$this->user['usergroupid'] = 2;
			$this->user['invisible'] = 0;
		}
		$DB->shutdown_query("DELETE FROM ".TABLE_PREFIX."session WHERE lastactivity < ".$cookietimeout);
		$this->sessionid  = md5(uniqid(microtime()));
		$DB->query_unbuffered("INSERT IGNORE INTO ".TABLE_PREFIX."session
			(sessionhash, username, userid, usergroupid, inforum, inthread, inblog, invisible, lastactivity, location, host, useragent, badlocation, mobile) VALUES
			('".$this->sessionid."', '".$this->user['name']."', ".intval($this->user['id']).", ".$this->user['usergroupid'].", ".intval($_INPUT['f']).", ".intval($_INPUT['t']).", ".intval($_INPUT['bid']).", '".$this->user['invisible']."', '".TIMENOW."', '".$DB->escape_string(WOLPATH)."', '".$DB->escape_string(SESSION_HOST)."', '".$DB->escape_string(USER_AGENT)."', 0, 0)");
    }
}
?>