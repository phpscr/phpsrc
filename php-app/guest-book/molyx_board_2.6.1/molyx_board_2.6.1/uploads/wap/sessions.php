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
					$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
					$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
					$contents = convert($forums->lang['loadlimit']);
					include $forums->func->load_template('wap_info');
					exit;
				}
			} else {
				if ( $serverstats = @exec("uptime") ) {
					preg_match( "/(?:averages)?\: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $serverstats, $load );
					$forums->server_load = $load[1];
					if ($forums->server_load > $bboptions['loadlimit']) {
						$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
						$forums->lang['wapinfo'] = convert($forums->lang['wapinfo']);
						$contents = convert($forums->lang['loadlimit']);
						include $forums->func->load_template('wap_info');
						exit;
					}
				}
			}
		}
		$this->user = array( 'id' => 0, 'name' => "", 'usergroupid' => 2 );

		$cookie = array();
		//$cookie['sessionid']   = $forums->func->get_cookie('sessionid');
		$cookie['userid'] = $_GET['bbuid'] ? $_GET['bbuid'] : $forums->func->get_cookie('userid');
		$cookie['password'] = $_GET['bbpwd'] ? $_GET['bbpwd'] : $forums->func->get_cookie('password');
		$forums->mobile = FALSE;
		if ( $_INPUT['s'] ) {
			$this->get_session($_INPUT['s']);
			$forums->sessiontype = 'url';
		} elseif ( $cookie['sessionid'] ) {
			$this->get_session($cookie['sessionid']);
			$forums->sessiontype = 'cookie';
		} else {
			$this->sessionid = 0;
		}
		if ( $this->sessionid) {
			if ( $this->userid != 0 AND !empty($this->userid) ) {
				$this->load_user($this->userid);
				if ( !$this->user['id'] OR $this->user['id'] == 0 ) {
					$this->unload_user();
					$this->update_guest_session();
				} else {
					$this->update_user_session();
				}
			} else {
				$this->update_guest_session();
			}
		} else {
			if ($cookie['userid'] != "" AND $cookie['password'] != "") {
				$this->load_user($cookie['userid']);
				if ( (! $this->user['id']) OR ($this->user['id'] == 0) ) {
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
		if (! $this->user['id']) {
			$this->user = $forums->func->set_up_guest();
			$this->user['lastactivity'] = TIMENOW;
			$this->user['lastvisit']    = TIMENOW;
		}
		$forums->func->check_cache("usergroup_{$this->user['usergroupid']}", 'usergroup');
		$this->user = array_merge( $this->user, $forums->cache["usergroup_{$this->user['usergroupid']}"] );
		$this->build_group_permissions();
		if ( $this->user['usergroupid'] != 2 ) {
			if ( $this->user['supermod'] == 1 ) {
				$this->user['is_mod'] = 1;
			} else {
				if ( @is_file( ROOT_PATH.'cache/cache/moderator_user_'.$this->user['id'].'.php' ) ) {
					require (ROOT_PATH.'cache/cache/moderator_user_'.$this->user['id'].'.php');
					foreach( $forums->cache["moderator_user_".$this->user['id']] AS $i => $r ) {
						if ($this->user['caneditusers']) $this->user['caneditusers'] = 1;
						$this->user['_moderator'][ $r['forumid'] ] = $r;
						$this->user['is_mod'] = 1;
					}
				}
				if ( @is_file( ROOT_PATH.'cache/cache/moderator_group_'.$this->user['usergroupid'].'.php' ) ) {
					require (ROOT_PATH."cache/cache/moderator_group_".$this->user['usergroupid'].".php");
					foreach( $forums->cache["moderator_group_".$this->user['usergroupid']] AS $i => $r ) {
						if ($this->user['caneditusers']) $this->user['caneditusers'] = 1;
						$this->user['_moderator'][ $r['forumid'] ] = $r;
						$this->user['is_mod'] = 1;
					}
				}
			}
		}
		if ($this->user['id']) {
			if ( ! $_INPUT['lastactivity'] ) {
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
			if ( ! $this->user['lastvisit'] ) {
				$DB->query_unbuffered("UPDATE LOW_PRIORITY ".TABLE_PREFIX."user SET lastvisit='".TIMENOW."', lastactivity='".TIMENOW."' WHERE id='".$this->user['id']."'");

			} else if ( (TIMENOW - $_INPUT['lastactivity']) > 300 ) {
				$this->user['options'] = $forums->func->convert_array_to_bits(array_merge($this->user , array('invisible' => $this->user['invisible'], 'loggedin' => 1)), $_USEROPTIONS);
				$DB->shutdown_query("UPDATE LOW_PRIORITY ".TABLE_PREFIX."user SET options=".$this->user['options'].", lastactivity=".TIMENOW." WHERE id=".$this->user['id']."");
			}
			if ( $this->user['liftban'] ) {
				$ban_arr = $forums->func->banned_detect(  $this->user['liftban'] );
				if ($ban_arr['timespan'] == -1) {
					$bbuserinfo = $this->user;
					$forums->func->standard_error("banuserever", 1);
				} else if ( TIMENOW >= $ban_arr['date_end'] ) {
					$DB->query_unbuffered("UPDATE LOW_PRIORITY ".TABLE_PREFIX."user SET liftban='', usergroupid={$ban_arr['groupid']} WHERE id=".$this->user['id']."");
				} else {
					$bbuserinfo = $this->user;
					$forums->func->standard_error("banusertemp", 1, $forums->func->get_date($ban_arr['date_end'],2));
				}
			}
		}
		$forums->func->set_cookie("sessionid", $this->sessionid, 31536000);
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
				foreach( $groups_id AS $pid ) {
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

	function load_user($userid=0) {
		global $DB, $forums, $_USEROPTIONS;
		$userid = intval($userid);
		if ($userid != 0) {
			if ( $this->user = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."user WHERE id='".$userid."'") ) {
				$this->user['options'] = intval($this->user['options']);
				foreach($_USEROPTIONS AS $optionname => $optionval) {
					$this->user["$optionname"] = $this->user['options'] & $optionval ? 1 : 0;
				}
			}
			if ( ($this->user['id'] == 0) OR (empty($this->user['id'])) ) {
				$this->unload_user();
			}
		}
		unset($userid);
	}

	function unload_user() {
		global $forums;
		$forums->func->set_cookie( "sessionid" , "0", -1  );
		$forums->func->set_cookie( "userid" , "0", -1  );
		$forums->func->set_cookie( "password" , "0", -1  );
		$this->user['id'] = 0;
		$this->user['name'] = "";
	}

	function update_user_session() {
		global $DB, $_INPUT;
		if ( ! $this->sessionid ) {
			return $this->create_session($this->user['id']);
		}
		if (empty($this->user['id'])) {
			$this->unload_user();
			$this->create_session();
			return;
		}
		$inforum = $_INPUT['t'] ? '' : ', inforum='.intval($_INPUT['f']);
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."session SET username='".$this->user['name']."', userid=".intval($this->user['id']).", usergroupid=".$this->user['usergroupid']."".$inforum.", inthread=".intval($_INPUT['t']).", invisible=".$this->user['invisible'].", lastactivity=".TIMENOW.", location='".$DB->escape_string(WOLPATH)."', badlocation=0, mobile=1 WHERE sessionhash='".$this->sessionid."'");
	}

	function update_guest_session() {
		global $DB, $_INPUT;
		if ( ! $this->sessionid ) {
			$this->create_session();
			return;
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."session SET username='', userid=0, usergroupid=2, inforum=".intval($_INPUT['f']).", inthread=".intval($_INPUT['t']).", invisible=0, lastactivity='".TIMENOW."', location='".$DB->escape_string(WOLPATH)."', badlocation=0, mobile=1 WHERE sessionhash='".$this->sessionid."'");
	}

	function get_session($sessionid="") {
		global $DB, $bboptions;
		$result = array();
		$query = "";
		$sessionid = preg_replace("/([^a-zA-Z0-9])/", "", $sessionid);
		if ( $sessionid ) {
			if(!$result = $DB->query_first("SELECT sessionhash, userid, lastactivity, location FROM ".TABLE_PREFIX."session WHERE sessionhash='".$sessionid."' AND host='".$DB->escape_string(SESSION_HOST)."'") ) {
				$this->badsessionid   = $sessionid;
				$this->sessionid = 0;
				$this->userid   = 0;
			} else {
				$this->sessionid = $result['sessionhash'];
				$this->userid   = $result['userid'];
			}
			unset($result);
			return;
		}
	}

	function create_session($userid=0) {
		global $DB, $_INPUT, $forums, $bboptions, $_USEROPTIONS;
		$cookietimeout = $bboptions['cookietimeout'] ? (time() - $bboptions['cookietimeout'] * 60) : (time() - 3600);
		if ($userid) {
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."session WHERE userid='".$this->user['id']."'");
			if ( TIMENOW - $this->user['lastactivity'] > 3600 ) {
				$forums->func->set_cookie('threadread', '');
				$this->user['options'] = $forums->func->convert_array_to_bits(array_merge($this->user , array('invisible' => $this->user['invisible'], 'loggedin' => 1)), $_USEROPTIONS);
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET options=".$this->user['options'].", lastvisit=lastactivity, lastactivity='".TIMENOW."' WHERE id='".$this->user['id']."'");
				$_INPUT['lastvisit'] = $this->user['lastactivity'];
				$_INPUT['lastactivity'] = TIMENOW;
			}
		} else {
			$extra = (empty($this->badsessionid)) ? "" : " OR sessionhash='".$this->badsessionid."'";
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."session WHERE host='".$DB->escape_string(SESSION_HOST)."'".$extra);
			$this->user['name'] = '';
			$this->user['id'] = 0;
			$this->user['usergroupid'] = 2;
			$this->user['invisible'] = 0;
		}
		$DB->shutdown_query( "DELETE FROM ".TABLE_PREFIX."session WHERE lastactivity < ".$cookietimeout."" );
		$this->sessionid  = substr(md5( uniqid(microtime()) ), 0, 16);
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."session
							(sessionhash, username, userid, usergroupid, inforum, inthread, inblog, invisible, lastactivity, location, host, useragent, badlocation, mobile)
						VALUES
							('".$this->sessionid."', '".$this->user['name']."', ".intval($this->user['id']).", ".$this->user['usergroupid'].", ".intval($_INPUT['f']).", ".intval($_INPUT['t']).", ".intval($_INPUT['bid']).", '".$this->user['invisible']."', '".TIMENOW."', '".$DB->escape_string(WOLPATH)."', '".$DB->escape_string(SESSION_HOST)."', '".$DB->escape_string(USER_AGENT)."', 0, 1)
							");
	}
}

?>