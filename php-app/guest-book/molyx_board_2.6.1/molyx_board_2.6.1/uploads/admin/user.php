<?php
// **************************************************************************#
// MolyX2
// ------------------------------------------------------
// copyright (c) 2004-2006 HOGE Software.
// official forum : http://molyx.com
// license : MolyX License, http://molyx.com/license
// MolyX2 is free software. You can redistribute this file and/or modify
// it under the terms of MolyX License. If you do not accept the Terms
// and Conditions stated in MolyX License, please do not redistribute
// this file.Please visit http://molyx.com/license periodically to review
// the Terms and Conditions, or contact HOGE Software.
// **************************************************************************#
require ('./global.php');
class user {
	function show()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if (!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditusers']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$user_id = intval($_INPUT['u']);
		if ($user_id > 0 && $bbuserinfo['id'] != $user_id && in_array($user_id, $admin)) {
			$forums->admin->print_cp_error($forums->lang['cannoteditadmin']);
		}
		$forums->admin->nav[] = array('user.php', $forums->lang['manageuser']);
		switch ($_INPUT['do']) {
			case 'doform':
				$this->useredit('edit');
				break;
			case 'doedit':
				$this->douseredit('edit');
				break;
			case 'unsuspend':
				$this->userunsuspend();
				break;
			case 'newuser':
				$this->useredit('add');
				break;
			case 'adduser':
				$this->douseredit('add');
				break;
			case 'doprune':
				$this->douserprune();
				break;
			case 'rankform':
				$this->rankform();
				break;
			case 'rankedit':
				$this->rankedit('edit');
				break;
			case 'addrank':
				$this->rankedit('add');
				break;
			case 'doaddrank':
				$this->doaddrank();
				break;
			case 'dorankedit':
				$this->dorankedit();
				break;
			case 'rank_delete':
				$this->dodeleterank();
				break;
			case 'olrankform':
				$this->olrankform();
				break;
			case 'olrankedit':
				$this->olrankedit('edit');
				break;
			case 'addolrank':
				$this->olrankedit('add');
				break;
			case 'doaddolrank':
				$this->doaddolrank();
				break;
			case 'doolrankedit':
				$this->doolrankedit();
				break;
			case 'olrank_delete':
				$this->dodeleteolrank();
				break;
			case 'mod':
				$this->viewmod();
				break;
			case 'domod':
				$this->domod();
				break;
			case 'changename':
				$this->changename();
				break;
			case 'dochangename':
				$this->dochangename();
				break;
			case 'banuser':
				$this->banuser();
				break;
			case 'dobanuser':
				$this->dobanuser();
				break;
			case 'changepassword':
				$this->changepassword();
				break;
			case 'dochangepassword':
				$this->dochangepassword();
				break;
			case 'search':
				$this->search_form();
				break;
			case 'searchresults':
				$this->searchresults();
				break;
			case 'deleteuser':
				$this->deleteuser();
				break;
			case 'adminperms':
				$this->adminperms();
				break;
			case 'doadminperms':
				$this->doadminperms();
				break;
			case 'ban':
				$this->banlist();
				break;
			case 'changeavatar':
				$this->changeavatar();
				break;
			case 'dochangeavatar':
				$this->dochangeavatar();
				break;
			case 'adminlist':
				$this->adminlist();
				break;
			case 'joinuser':
				$this->joinuser();
				break;
			case 'dojoin':
				$this->dojoin();
				break;
			default:
				$this->search_form();
				break;
		}
	}

	function dochangepassword()
	{
		global $forums, $DB, $_INPUT;
		if (! $_INPUT['password']) {
			$forums->main_msg = $forums->lang['requireuserpassword'];
			$this->changepassword();
		}
		$newsalt = $forums->func->generate_user_salt(5);
		$password = md5(trim($_INPUT['password']));
		$user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id=" . $_INPUT['u'] . "");
		$save_array = array();
		if ($_INPUT['newsalt']) {
			$save_array['salt'] = $newsalt;
			$save_array['password'] = md5($password . $newsalt);
		} else {
			$save_array['password'] = md5($password . $user['salt']);
		}
		$save_array['password'] = addslashes($save_array['password']);
		$forums->func->fetch_query_sql($save_array, 'user', 'id=' . $_INPUT['u']);
		$forums->admin->save_log($forums->lang['passwordchanged'] . " ( " . $forums->lang['userid'] . ": {$_INPUT['id']})");
		$forums->admin->redirect("user.php", $forums->lang['manageuser'], $forums->lang['passwordchanged']);
	}

	function changepassword()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$pagetitle = $forums->lang['changepassword'];
		$detail = $forums->lang['changepassworddesc'];
		$forums->lang['changepasswordmail'] = sprintf($forums->lang['changepasswordmail'], $bboptions['bbtitle'], $bboptions['bburl'], $bboptions['forumindex']);
		$contents = $forums->lang['changepasswordmail'];
		$forums->admin->nav[] = array('', $forums->lang['changepassword']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$page_array = array(1 => array('do' , 'dochangepassword'),
			2 => array('u' , $_INPUT['u']),
			);
		$forums->admin->print_form_header($page_array);
		if (! $user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id=" . $_INPUT['u'] . "")) {
			$forums->admin->print_cp_error($forums->lang['cannotfounduser']);
		}
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['changepassword'] . ": " . $user['name']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['inputnewpassword'] . "</strong>", $forums->admin->print_input_row('password')));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['createnewsalt'] . "</strong><div class='description'>" . $forums->lang['createnewsaltdesc'] . "</div>", $forums->admin->print_yes_no_row("newsalt", 1)));
		$forums->admin->print_form_submit($forums->lang['changepassword']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function banuser()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$pagetitle = $forums->lang['banuser'];
		$detail = $forums->lang['banuserdesc'];
		$forums->lang['banusermail'] = sprintf($forums->lang['banusermail'], $bboptions['bbtitle'], $bboptions['bburl'], $bboptions['forumindex']);
		$contents = $forums->lang['banusermail'];
		$forums->admin->nav[] = array('', $forums->lang['banuser']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		if ($_INPUT['u'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if (! $user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id='" . intval($_INPUT['u']) . "'")) {
			$forums->admin->print_cp_error($forums->lang['nouseraccounts']);
		}
		$page_array = array(1 => array('do' , 'dobanuser'), 2 => array('u' , $_INPUT['u'])) ;
		$forums->admin->print_form_header($page_array);
		$ban = $forums->func->banned_detect($user['liftban']);
		$units = array(0 => array('h', $forums->lang['hours']), 1 => array('d', $forums->lang['days']));
		if ($ban['date_end'] == -1) {
			$pchecked = 1;
		}
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['banuser'], $forums->lang['banuserwarning']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['tempbanuser'] . " {$user['name']}</strong>" ,
				$forums->admin->print_input_row('timespan', $ban['timespan'], "text", "", '5') . '&nbsp;' . $forums->admin->print_input_select_row('units', $units, $ban['units']) . '&nbsp;' . $forums->admin->print_checkbox_row('permanent', $pchecked) . '&nbsp;' . $forums->lang['banalways'] ,
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['bansendmail'] . "</strong><br />" . $forums->lang['bansendmaildesc'] ,
				$forums->admin->print_yes_no_row("send_email", 0)
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['banmailcontent'] . "</strong><br />" . $forums->lang['banmailcontentdesc'] ,
				$forums->admin->print_textarea_row("email_contents", $contents)
				), "", 'top');
		$forums->admin->print_form_submit($forums->lang['banuser']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function dobanuser()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$_INPUT['u'] = intval($_INPUT['u']);
		if ($_INPUT['u'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if (! $user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id='" . intval($_INPUT['u']) . "'")) {
			$forums->admin->print_cp_error($forums->lang['nouseraccounts']);
		}
		$_INPUT['timespan'] = intval($_INPUT['timespan']);
		if ($_INPUT['permanent']) {
			$ban['liftban'] = $forums->func->banned_detect(array('timespan' => -1, 'unit' => $_INPUT['units'], 'groupid' => $user['usergroupid'], 'banuser' => $bbuserinfo['name']));
			$ban['usergroupid'] = 5;
		} else if (!$_INPUT['timespan']) {
			$ban['liftban'] = "";
		} else {
			$ban['liftban'] = $forums->func->banned_detect(array('banuser' => $bbuserinfo['name'], 'timespan' => intval($_INPUT['timespan']), 'unit' => $_INPUT['units'], 'groupid' => $user['usergroupid']));
			$ban['usergroupid'] = 5;
		}
		$show_ban = $forums->func->banned_detect($ban['liftban']);
		$forums->func->fetch_query_sql($ban, 'user', 'id=' . $_INPUT['u']);
		if ($_INPUT['send_email'] == 1) {
			require_once(ROOT_PATH . "includes/functions_email.php");
			$this->email = new functions_email();
			$contents = trim($forums->func->convert_andstr($forums->func->stripslashes_uni($_POST['email_contents'])));
			$contents = str_replace("{username}", $user['name'], $contents);
			$contents = str_replace("{date_end}" , $forums->func->get_date($show_ban['date_end'], 2) , $contents);
			$this->email->build_message($contents);
			$this->email->subject = $forums->lang['banuserinfo'];
			$this->email->to = $bbuserinfo['email'];
			$this->email->send_mail();
		}
		$forums->admin->save_log($forums->lang['tempbanuser'] . " (" . $forums->lang['username'] . ": {$user['name']})");
		$forums->admin->redirect("user.php", $forums->lang['manageuser'], $forums->lang['tempbanneduser'] . " - {$user['name']}");
	}

	function userunsuspend()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$userid = intval($_INPUT['u']);
		if (!$userid) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if (! $user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id='" . $userid . "'")) {
			$forums->admin->print_cp_error($forums->lang['nouseraccounts']);
		}
		$ban = $forums->func->banned_detect($user['liftban']);
		$updategroup = $ban['groupid'] ? intval($ban['groupid']) : 3;
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "user SET liftban = '', usergroupid='" . $updategroup . "' WHERE id=" . $userid . "");
		$forums->admin->save_log($forums->lang['userunsuspend'] . " (" . $forums->lang['username'] . ": {$user['name']})");
		$msg = "{$user['name']}" . $forums->lang['unsuspended'];
		$forums->admin->redirect("user.php?do=ban", $forums->lang['manageuser'], $msg);
	}

	function banlist()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle = $forums->lang['banuserlist'];
		$detail = $forums->lang['banuserlistdesc'];
		$forums->admin->nav[] = array('', $forums->lang['managebanuser']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$banusers = $DB->query("SELECT id,name,liftban FROM " . TABLE_PREFIX . "user WHERE liftban != ''");
		while ($banuser = $DB->fetch_array($banusers)) {
			$ban = $forums->func->banned_detect($banuser['liftban']);
			$ban['id'] = $banuser['id'];
			$ban['name'] = $banuser['name'];
			$ban['leftban'] = sprintf("%01.2f", ($ban['date_end'] - TIMENOW) / 3600);
			$ban['date_start'] = $forums->func->get_date($ban['date_start'], 2);
			$ban['date_end'] = $forums->func->get_date($ban['date_end'], 2);
			if ($ban['timespan'] == -1) {
				$perban[$banuser['id']] = $ban;
			} else {
				$tempban[$banuser['id']] = $ban;
			}
		}
		$forums->admin->columns[] = array($forums->lang['username'], "20%");
		$forums->admin->columns[] = array($forums->lang['dobanuser'], "20%");
		$forums->admin->columns[] = array($forums->lang['banusertime'], "15%");
		$forums->admin->columns[] = array($forums->lang['unsuspendtime'], "15%");
		$forums->admin->columns[] = array($forums->lang['leftbantime'], "15%");
		$forums->admin->columns[] = array($forums->lang['unsuspend'], "5%");
		$forums->admin->print_table_start($forums->lang['tempbanuserlist']);
		if (is_array($tempban)) {
			foreach($tempban AS $id => $data) {
				$forums->admin->print_cells_row(array("<strong>" . $data['name'] . "</strong>", $data['banuser'], $data['date_start'], $data['date_end'], $data['leftban'] . $forums->lang['hours'], '<a href=user.php?' . $forums->sessionurl . 'do=unsuspend&amp;u=' . $data['id'] . '>' . $forums->lang['unsuspend'] . '</a>'));
			}
		} else {
			$forums->admin->print_cells_single_row($forums->lang['notempbanuser'], 'center');
		}
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array($forums->lang['username'], "20%");
		$forums->admin->columns[] = array($forums->lang['dobanuser'], "20%");
		$forums->admin->columns[] = array($forums->lang['banusertime'], "15%");
		$forums->admin->columns[] = array($forums->lang['unsuspendtime'], "15%");
		$forums->admin->columns[] = array($forums->lang['unsuspend'], "5%");
		$forums->admin->print_table_start($forums->lang['banalwaysuserlist']);
		if (is_array($perban)) {
			foreach($perban AS $id => $data) {
				$forums->admin->print_cells_row(array("<strong>" . $data['name'] . "</strong>", $data['banuser'], $data['date_start'], $forums->lang['banalways'], '<a href=user.php?' . $forums->sessionurl . 'do=unsuspend&amp;u=' . $data['id'] . '>' . $forums->lang['unsuspend'] . '</a>'));
			}
		} else {
			$forums->admin->print_cells_single_row($forums->lang['nobanalwaysuser'], 'center');
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_cp_footer();
	}

	function dochangename()
	{
		global $forums, $DB, $_INPUT;
		$_INPUT['newname'] = str_replace('|', '&#124;', $_INPUT['newname']);
		if ($_INPUT['u'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ($_INPUT['newname'] == "") {
			$this->changename($forums->lang['requirenewname']);
			exit();
		}
		if (! $user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id='" . intval($_INPUT['u']) . "'")) {
			$forums->admin->print_cp_error($forums->lang['nouseraccounts']);
		}
		$userid = $_INPUT['u'];
		if ($_INPUT['newname'] == $user['name']) {
			$this->changename($forums->lang['newnamesameold']);
			exit();
		}
		$newname = trim($_INPUT['newname']);
		$DB->query("SELECT u.*, g.*
				FROM " . TABLE_PREFIX . "user u, " . TABLE_PREFIX . "usergroup g
				WHERE (LOWER(u.name)='" . $forums->func->strtolower($newname) . "' OR u.name='" . $newname . "') AND u.usergroupid=g.usergroupid");
		if ($DB->num_rows()) {
			$forums->lang['newnameexist'] = sprintf($forums->lang['newnameexist'], $newname);
			$this->changename($forums->lang['newnameexist']);
			exit();
		}
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "user SET name='" . $newname . "' WHERE id=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "pmuserlist SET contactname='" . $newname . "' WHERE contactid=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "forum SET lastposter='" . $newname . "' WHERE lastposterid=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "moderatorlog SET username='" . $newname . "' WHERE userid=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "moderator SET username='" . $newname . "' WHERE userid=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "post SET username='" . $newname . "' WHERE userid=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "session SET username='" . $newname . "' WHERE userid=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "thread SET postusername='" . $newname . "' WHERE postuserid=" . $userid . "");
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "thread SET lastposter='" . $newname . "' WHERE lastposterid=" . $userid . "");
		require_once(ROOT_PATH . 'includes/adminfunctions_cache.php');
		$moderator = new adminfunctions_cache();
		$moderator->moderator_recache();
		if ($_INPUT['send_email'] == 1) {
			require_once(ROOT_PATH . "includes/functions_email.php");
			$this->email = new functions_email();
			$msg = trim($forums->func->convert_andstr($forums->func->stripslashes_uni($_POST['email_contents'])));
			$msg = str_replace("{old_name}", $user['name'], $msg);
			$msg = str_replace("{newname}", $newname , $msg);
			$this->email->build_message($msg);
			$this->email->subject = $forums->lang['namechangemail'];
			$this->email->to = $user['email'];
			$this->email->send_mail();
		}
		$forums->lang['namechangedlog'] = sprintf($forums->lang['namechangedlog'], $user['name'], $newname);
		$forums->admin->save_log($forums->lang['namechangedlog']);
		$forums->admin->redirect("user.php", $forums->lang['manageuser'], $forums->lang['namechanged']);
	}

	function changename($message = "")
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$pagetitle = $forums->lang['changeusername'];
		$detail = $forums->lang['changeusernamedesc'];
		$forums->admin->nav[] = array('', $forums->lang['changeusername']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		if ($_INPUT['u'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if (! $user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id='" . intval($_INPUT['u']) . "'")) {
			$forums->admin->print_cp_error($forums->lang['nouseraccounts']);
		}
		$forums->lang['changeusernamemail'] = sprintf($forums->lang['changeusernamemail'], $bboptions['bbtitle'], $bboptions['bburl'], $bboptions['forumindex']);
		$contents = $forums->lang['changeusernamemail'];
		$page_array = array(1 => array('do' , 'dochangename'), 2 => array('u' , $_INPUT['u']));
		$forums->admin->print_form_header($page_array);
		$forums->admin->columns[] = array("&nbsp;", "40%");
		$forums->admin->columns[] = array("&nbsp;", "60%");
		$forums->admin->print_table_start($forums->lang['changeusername']);
		if ($message != "") {
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['errorinfo'] . ":</strong>", "<strong><span style='color:yellow'>$message</span></strong>"));
		}
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['currentusername'] . "</strong>", $user['name']));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['newusername'] . "</strong>", $forums->admin->print_input_row("newname", $_INPUT['newname'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['newnamesendmail'] . "</strong><br />" . $forums->lang['newnamesendmaildesc'], $forums->admin->print_yes_no_row("send_email", 1)));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['newnamemailcontent'] . "</strong><br />" . $forums->lang['newnamemailcontentdesc'], $forums->admin->print_textarea_row("email_contents", $contents)));
		$forums->admin->print_form_submit($forums->lang['changeusername']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function domod()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$ids = array();
		if (is_array($_INPUT['userid'])) {
			foreach ($_INPUT['userid'] AS $value) {
				if ($value) {
					$ids[] = $value;
				}
			}
		}
		if (count($ids) < 1) {
			$forums->admin->print_cp_error($forums->lang['noselectmoduser']);
		}
		if ($_INPUT['type'] == 'approve') {
			require_once(ROOT_PATH . "includes/functions_email.php");
			$email = new functions_email();
			$message = $email->fetch_accept_account();
			$email->build_message($message);
			$a = $DB->query("SELECT u.id, u.email, u.usergroupid AS oldgroupid, ua.* FROM " . TABLE_PREFIX . "useractivation ua
				 LEFT JOIN " . TABLE_PREFIX . "user u ON (ua.userid=u.id)
				WHERE u.id IN(" . implode(",", $ids) . ")");
			while ($row = $DB->fetch_array($a)) {
				if ($row['oldgroupid'] != 1) {
					continue;
				}
				if ($row['oldgroupid'] == "") {
					$row['usergroupid'] = 3;
				}
				$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "user SET usergroupid=" . $row['usergroupid'] . " WHERE id=" . $row['id'] . "");
				$forums->lang['userapproved'] = sprintf($forums->lang['userapproved'], $bboptions['bbtitle']);
				$email->subject = $forums->lang['userapproved'];
				$email->to = $row['email'];
				$email->send_mail();
			}
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "useractivation WHERE userid IN(" . implode(",", $ids) . ")");
			$forums->admin->save_log($forums->lang['approveaccounts']);
			$forums->lang['accountsapproved'] = sprintf($forums->lang['accountsapproved'], count($ids));
			$forums->func->check_cache('stats');
			include_once(ROOT_PATH.'includes/adminfunctions_cache.php');
			$cache = new adminfunctions_cache();
			$_INPUT['lastreg'] = $_INPUT['users'] = 1;
			$cache->stats_recache(0);
			$forums->admin->redirect("user.php?do=mod", $forums->lang['managenewaccounts'], $forums->lang['accountsapproved']);
		} else {
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "user WHERE id IN(" . implode(",", $ids) . ")");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "pm WHERE fromuserid IN(" . implode(",", $ids) . ")");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "pmtext WHERE fromuserid IN(" . implode(",", $ids) . ")");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "pmuserlist WHERE userid IN(" . implode(",", $ids) . ") OR contactid IN(" . implode(",", $ids) . ")");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "useractivation WHERE userid IN(" . implode(",", $ids) . ")");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "post SET userid=0 WHERE userid  IN(" . implode(",", $ids) . ")");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "thread SET postuserid=0 WHERE postuserid IN(" . implode(",", $ids) . ")");
			$forums->lang['accountsdeleted'] = sprintf($forums->lang['accountsdeleted'], count($ids));
			$forums->admin->save_log($forums->lang['deleteaccounts']);
			$forums->admin->redirect("user.php?do=mod", $forums->lang['managenewaccounts'], $forums->lang['accountsdeleted']);
		}
	}

	function viewmod()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle = $forums->lang['userrequest'];
		$detail = $forums->lang['userrequestdesc'];
		$forums->admin->nav[] = array('', $forums->lang['manageactivation']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$row = $DB->query_first("SELECT COUNT(useractivationid) as count FROM " . TABLE_PREFIX . "useractivation WHERE type != 1");
		$totalpages = $row['count'] < 1 ? 0 : $row['count'];
		$page = intval($_INPUT['pp']);
		$ord = $_INPUT['ord'] == 'asc' ? 'asc' : 'desc';
		$new_ord = $ord == 'asc' ? 'desc' : 'asc';
		switch ($_INPUT['sort']) {
			case 'name':
				$col = 'u.name';
				break;
			case 'email':
				$col = 'u.email';
				break;
			case 'sent':
				$col = 'ua.dateline';
				break;
			case 'post':
				$col = 'u.posts';
				break;
			case 'reg':
				$col = 'u.joindate';
				break;
			default:
				$col = 'ua.dateline';
				break;
		}
		$forums->admin->print_form_header(array(1 => array('do', 'domod')));
		$forums->admin->columns[] = array("<a href='user.php?{$forums->sessionurl}do=mod&amp;pp=$page&amp;sort=name&amp;ord=$new_ord'>" . $forums->lang['username'] . "</a>", "20%");
		$forums->admin->columns[] = array($forums->lang['activationtype'], "10%");
		$forums->admin->columns[] = array("<a href='user.php?{$forums->sessionurl}do=mod&amp;pp=$page&amp;sort=email&amp;ord=$new_ord'>" . $forums->lang['email'] . "</a>", "15%");
		$forums->admin->columns[] = array("<a href='user.php?{$forums->sessionurl}do=mod&amp;pp=$page&amp;sort=sent&amp;ord=$new_ord'>" . $forums->lang['emailsendtime'] . "</a>", "15%");
		$forums->admin->columns[] = array("<a href='user.php?{$forums->sessionurl}do=mod&amp;pp=$page&amp;sort=post&amp;ord=$new_ord'>" . $forums->lang['posts'] . "</a>", "10%");
		$forums->admin->columns[] = array("<a href='user.php?{$forums->sessionurl}do=mod&amp;pp=$page&amp;sort=reg&amp;ord=$new_ord'>" . $forums->lang['joindate'] . "</a>", "15%");
		$forums->admin->columns[] = array($forums->lang['joindates'], "15%");
		$forums->admin->columns[] = array("&nbsp;", "");
		$forums->admin->print_table_start($forums->lang['activationuser']);
		$links = $forums->func->build_pagelinks(array('totalpages' => $totalpages,
				'perpage' => 75,
				'curpage' => $page,
				'pagelink' => "user.php?{$forums->sessionurl}do=mod",
				));
		$forums->lang['wautactivationusers'] = sprintf($forums->lang['wautactivationusers'], $totalpages);
		$forums->admin->print_cells_single_row("<strong>" . $forums->lang['wautactivationusers'] . "</strong>", "center");
		if ($totalpages > 0) {
			$DB->query("SELECT u.name, u.id, u.email, u.posts, u.joindate, ua.*
				  FROM " . TABLE_PREFIX . "useractivation ua
				LEFT JOIN " . TABLE_PREFIX . "user u ON (ua.userid=u.id)
				WHERE ua.type <> 1
				ORDER BY " . $col . " " . $ord . " LIMIT " . $page . ",75");
			while ($r = $DB->fetch_array()) {
				$where = ($r['type'] == 2 ? $forums->lang['regnewaccounts'] : ($r['type'] == 3 ? $forums->lang['changeusermail'] : $forums->lang['none']));
				$hours = floor((time() - $r['dateline']) / 3600);
				$days = intval($hours / 24);
				$rhours = intval($hours - ($days * 24));
				if ($r['name'] == "") {
					$r['name'] = $forums->lang['deleteuser'];
				}
				$forums->admin->print_cells_row(array("<strong>" . $r['name'] . "</strong>" ,
						"<center>$where</center>",
						$r['email'],
						"<center>" . $forums->func->get_date($r['dateline'], 1) . "</center>",
						"<center>{$r['posts']}</center>",
						"<center>" . $forums->func->get_date($r['joindate'], 3) . "</center>",
						"<center><strong>$days {$forums->lang['days']}, $rhours {$forums->lang['hours']}</center>",
						"<center><input type='checkbox' name='userid[]' value='{$r['userid']}' /></center>"
						));
			}
			$forums->admin->print_cells_single_row("$links", "left");
			$forums->admin->print_cells_single_row("<select name='type' class='dropdown'><option value='approve'>" . $forums->lang['moderateattounts'] . "</option><option value='delete'>" . $forums->lang['deleteattounts'] . "</option></select>", "center");
		}
		$forums->admin->print_form_submit($forums->lang['ok']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function ranks_recache()
	{
		global $forums, $DB;
		require_once(ROOT_PATH . 'includes/adminfunctions_cache.php');
		$cache = new adminfunctions_cache();
		$cache->ranks_recache();
	}

	function rankform()
	{
		global $forums, $DB, $bboptions;
		$pagetitle = $forums->lang['manageranks'];
		$detail = $forums->lang['manageranksdesc'];
		$forums->admin->nav[] = array('', $forums->lang['manageranks']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->columns[] = array($forums->lang['rankname'], "30%");
		$forums->admin->columns[] = array($forums->lang['miniposts'], "10%");
		$forums->admin->columns[] = array($forums->lang['rankshow'], "20%");
		$forums->admin->columns[] = array($forums->lang['option'], "20%");
		$user = $DB->query_first("SELECT imagefolder FROM " . TABLE_PREFIX . "style WHERE usedefault=1");
		$user['imagefolder'] .= '/' . $bboptions['language'];
		$forums->admin->print_table_start($forums->lang['manageranks']);
		$DB->query("SELECT * FROM " . TABLE_PREFIX . "usertitle ORDER BY post");
		while ($r = $DB->fetch_array()) {
			$img = "";
			if (preg_match("/^\d+$/", $r['ranklevel'])) {
				for ($i = 1; $i <= $r['ranklevel']; $i++) {
					$img .= "<img src='../images/" . $user['imagefolder'] . "/pip.gif' border='0' alt='' />";
				}
			} else {
				$img = "<img src='../images/{$user['imagefolder']}/team/{$r['ranklevel']}' border='0' alt='' />";
			}
			$forums->admin->print_cells_row(array("<strong>" . $r['title'] . "</strong>" ,
					$r['post'],
					$img,
					"<div align='center'><a href='user.php?{$forums->sessionurl}do=rankedit&amp;id={$r['id']}'>" . $forums->lang['edit'] . "</a> <a href='user.php?{$forums->sessionurl}do=rank_delete&amp;id={$r['id']}'>" . $forums->lang['delete'] . "</a></div>",
					));
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_form_header(array(1 => array('do' , 'doaddrank'),));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['adduserrank']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['ranktitle'] . "</strong>", $forums->admin->print_input_row("title")));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['rankminiposts'] . "</strong>", $forums->admin->print_input_row("post")));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['rankimages'] . "</strong><br />" . $forums->lang['rankimagesdesc'], $forums->admin->print_input_row("ranklevel")));
		$forums->admin->print_form_submit($forums->lang['addnewrank']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function doaddrank()
	{
		global $forums, $DB, $_INPUT;
		foreach(array('post', 'title', 'ranklevel') AS $field) {
			if ($_INPUT[ $field ] == "") {
				$forums->admin->print_cp_error($forums->lang['inputallforms']);
			}
		}
		$forums->func->fetch_query_sql(array('post' => trim($_INPUT['post']), 'title' => trim($_INPUT['title']), 'ranklevel' => trim($_INPUT['ranklevel'])), 'usertitle');
		$this->ranks_recache();
		$forums->admin->redirect("user.php?do=rankform", $forums->lang['manageranks'], $forums->lang['userrankadded']);
	}

	function dodeleterank()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "usertitle WHERE id='" . $_INPUT['id'] . "'");
		$this->ranks_recache();
		$forums->admin->save_log($forums->lang['userrankdeleted']);
		$forums->admin->redirect("user.php?do=rankform", $forums->lang['manageranks'], $forums->lang['userrankdeleted']);
	}

	function olranks_recache()
	{
		global $forums, $DB;
		require_once(ROOT_PATH . 'includes/adminfunctions_cache.php');
		$cache = new adminfunctions_cache();
		$cache->olranks_recache();
	}

	function olrankform()
	{
		global $forums, $DB, $bboptions;
		$pagetitle = $forums->lang['manageolranks'];
		$detail = $forums->lang['manageolranksdesc'];
		$forums->admin->nav[] = array('', $forums->lang['manageolranks']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->columns[] = array($forums->lang['olranknum'], "30%");
		$forums->admin->columns[] = array($forums->lang['olrankpic'], "30%");
		$forums->admin->columns[] = array($forums->lang['olrankshows'], "10%");
		$forums->admin->columns[] = array($forums->lang['option'], "30%");
		$user = $DB->query_first("SELECT imagefolder FROM " . TABLE_PREFIX . "style WHERE usedefault=1");
		$user['imagefolder'] .= '/' . $bboptions['language'];
		$forums->admin->print_table_start($forums->lang['manageolranks']);
		$DB->query("SELECT * FROM " . TABLE_PREFIX . "userolrank ORDER BY onlineranklevel");
		while ($r = $DB->fetch_array()) {
			$img = "<img src='../images/{$user['imagefolder']}/team/{$r['onlinerankimg']}' border='0' alt='' />";
			$forums->admin->print_cells_row(array("<b>" . $r['onlineranklevel'] . "</b>" ,
					$img,
					$r['maxnum'],
					"<div align='center'><a href='user.php?{$forums->sessionurl}do=olrankedit&amp;id={$r['onlinerankid']}'>" . $forums->lang['edit'] . "</a> <a href='user.php?{$forums->sessionurl}do=olrank_delete&amp;id={$r['onlinerankid']}'>" . $forums->lang['delete'] . "</a></div>",
					));
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_form_header(array(1 => array('do' , 'doaddolrank'),));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['addolrank']);
		$forums->admin->print_cells_row(array("<b>" . $forums->lang['olranklevel'] . "</b>", $forums->admin->print_input_row("onlineranklevel")));
		$forums->admin->print_cells_row(array("<b>" . $forums->lang['olrankmax'] . "</b><br />" . $forums->lang['olrankmaxdesc'], $forums->admin->print_input_row("maxnum")));
		$forums->admin->print_cells_row(array("<b>" . $forums->lang['olrankimages'] . "</b><br />" . $forums->lang['olrankimagesdesc'], $forums->admin->print_input_row("onlinerankimg")));
		$forums->admin->print_form_submit($forums->lang['addnewolrank']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function doaddolrank()
	{
		global $forums, $DB, $_INPUT;
		foreach(array('onlineranklevel', 'maxnum', 'onlinerankimg') AS $field) {
			if ($_INPUT[ $field ] == "") {
				$forums->admin->print_cp_error($forums->lang['inputallforms']);
			}
		}
		$forums->func->fetch_query_sql(array('onlineranklevel' => trim($_INPUT['onlineranklevel']), 'maxnum' => trim($_INPUT['maxnum']), 'onlinerankimg' => trim($_INPUT['onlinerankimg'])), 'userolrank');
		$this->olranks_recache();
		$forums->admin->redirect("user.php?do=olrankform", $forums->lang['manageolranks'], $forums->lang['olrankadded']);
	}

	function dodeleteolrank()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "userolrank WHERE onlinerankid='" . $_INPUT['id'] . "'");
		$this->olranks_recache();
		$forums->admin->save_log($forums->lang['olrankdeleted']);
		$forums->admin->redirect("user.php?do=olrankform", $forums->lang['manageolranks'], $forums->lang['olrankdeleted']);
	}

	function olrankedit($mode = 'edit')
	{
		global $forums, $DB, $_INPUT;
		$pagetitle = $forums->lang['manageolranks'];
		$detail = $forums->lang['manageolranksdesc'];
		$forums->admin->nav[] = array('', $forums->lang['manageolranks']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		if ($mode == 'edit') {
			$form_code = 'doolrankedit';
			if ($_INPUT['id'] == "") {
				$forums->admin->print_cp_error($forums->lang['noids']);
			}
			$olrank = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "userolrank WHERE onlinerankid='" . $_INPUT['id'] . "'");
			$button = $forums->lang['doolrankedit'];
		} else {
			$form_code = 'doaddolrank';
			$rank = array('onlineranklevel' => "", 'maxnum' => "", 'onlinerankimg' => "");
			$button = $forums->lang['addnewolrank'];
		}
		$forums->admin->print_form_header(array(1 => array('do', $form_code), 2 => array('id' , $olrank['onlinerankid'])));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['manageolranks']);
		$forums->admin->print_cells_row(array("<b>" . $forums->lang['olranklevel'] . "</b>", $forums->admin->print_input_row("onlineranklevel", $olrank['onlineranklevel'])));
		$forums->admin->print_cells_row(array("<b>" . $forums->lang['olrankmax'] . "</b><br />" . $forums->lang['olrankmaxdesc'], $forums->admin->print_input_row("maxnum", $olrank['maxnum'])));
		$forums->admin->print_cells_row(array("<b>" . $forums->lang['olrankimages'] . "</b><br />" . $forums->lang['olrankimagesdesc'], $forums->admin->print_input_row("onlinerankimg", $olrank['onlinerankimg'])));
		$forums->admin->print_form_end($button);
		$forums->admin->print_table_footer();
		$forums->admin->print_cp_footer();
	}

	function doolrankedit()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		foreach(array('onlineranklevel', 'maxnum', 'onlinerankimg') AS $field) {
			if ($_INPUT[ $field ] == "") {
				$forums->admin->print_cp_error($forums->lang['inputallforms']);
			}
		}
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "userolrank SET onlineranklevel=" . trim($_INPUT['onlineranklevel']) . ", maxnum='" . trim($_INPUT['maxnum']) . "', onlinerankimg='" . trim($_INPUT['onlinerankimg']) . "' WHERE onlinerankid=" . $_INPUT['id'] . "");
		$this->olranks_recache();
		$forums->admin->save_log($forums->lang['olrankedited']);
		$forums->admin->redirect("user.php?do=olrankform", $forums->lang['manageolranks'], $forums->lang['olrankedited']);
	}

	function dorankedit()
	{
		global $forums, $DB, $_INPUT;
		if ($_INPUT['id'] == "") {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		foreach(array('post', 'title', 'ranklevel') AS $field) {
			if ($_INPUT[ $field ] == "") {
				$forums->admin->print_cp_error($forums->lang['inputallforms']);
			}
		}
		$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "usertitle SET post=" . trim($_INPUT['post']) . ", title='" . trim($_INPUT['title']) . "', ranklevel='" . trim($_INPUT['ranklevel']) . "' WHERE id=" . $_INPUT['id'] . "");
		$this->ranks_recache();
		$forums->admin->save_log($forums->lang['userrankedited']);
		$forums->admin->redirect("user.php?do=rankform", $forums->lang['manageranks'], $forums->lang['userrankedited']);
	}

	function rankedit($mode = 'edit')
	{
		global $forums, $DB, $_INPUT;
		$pagetitle = $forums->lang['manageranks'];
		$detail = $forums->lang['manageranksdesc'];
		$forums->admin->nav[] = array('', $forums->lang['manageranks']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		if ($mode == 'edit') {
			$form_code = 'dorankedit';
			if ($_INPUT['id'] == "") {
				$forums->admin->print_cp_error($forums->lang['noids']);
			}
			$rank = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "usertitle WHERE id='" . $_INPUT['id'] . "'");
			$button = $forums->lang['dorankedit'];
		} else {
			$form_code = 'doaddrank';
			$rank = array('post' => "", 'title' => "", 'ranklevel' => "");
			$button = $forums->lang['addnewrank'];
		}
		$forums->admin->print_form_header(array(1 => array('do', $form_code), 2 => array('id' , $rank['id'])));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['manageranks']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['ranktitle'] . "</strong>", $forums->admin->print_input_row("title", $rank['title'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['rankminiposts'] . "</strong>", $forums->admin->print_input_row("post", $rank['post'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['rankimages'] . "</strong><br />" . $forums->lang['rankimagesdesc'], $forums->admin->print_input_row("ranklevel", $rank['ranklevel'])));
		$forums->admin->print_form_submit($button);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function user_prune_confirm($ids = array(), $query)
	{
		global $forums, $DB;
		if (count($ids) < 101) {
			foreach($ids AS $i => $n) {
				$user_arr[] = $forums->func->fetch_user_link($n[1], $n[0]);
			}
		}
		$pagetitle = $forums->lang['deleteuser'];
		$detail = $forums->lang['confirmdeleteuser'];
		$forums->admin->nav[] = array('', $forums->lang['deleteuser']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header(array(1 => array('do' , 'doprune'), 2 => array('query' , str_replace("'", '&#39;', $query))));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['confirmdeleteuser']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['bedeleteusers'] . "</strong>", count($ids)));
		if (count($user_arr) > 0) {
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['bedeleteuser'] . "</strong>", implode('<br />', $user_arr)));
		}
		$forums->admin->print_form_submit($forums->lang['confirmdelete']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function douserprune()
	{
		global $forums, $DB;
		$query = trim(rawurldecode($forums->func->convert_andstr($forums->func->stripslashes_uni($_POST['query']))));
		$query = str_replace(array("&lt;", "&gt;", '&#39;'), array("<", ">", "'"), $query);
		if ($query == "") {
			$forums->admin->print_cp_error($forums->lang['deleteusererror']);
		}
		$ids = array();
		$DB->query($query);
		if ($DB->num_rows()) {
			while ($i = $DB->fetch_array()) {
				$ids[] = $i['id'];
			}
		} else {
			$forums->admin->print_cp_error($forums->lang['nomatchdeleteuser']);
		}
		$this->dodeleteuser($ids);
		$forums->admin->redirect("user.php", $forums->lang['manageuser'], $forums->lang['useraccountdeleted']);
	}

	function dodeleteuser($ids)
	{
		global $forums, $DB, $bboptions, $_INPUT;
		if (is_array($ids)) {
			$userids = ' IN (' . implode(",", $ids) . ')';
		} else {
			$userids = ' = ' . $ids;
		}
		if ($_INPUT['deletepost']) {
			require_once(ROOT_PATH . "includes/functions_moderate.php");
			$mod = new modfunctions();
			$users = $DB->query("SELECT id,avatarlocation FROM " . TABLE_PREFIX . "user WHERE id" . $userids . "");
			while ($user = $DB->fetch_array($users)) {
				@unlink($bboptions['uploadfolder'] . "/avatar/" . $user['avatarlocation']);
				$forums->admin->rm_dir($bboptions['uploadfolder'] . '/' . implode('/', preg_split('//', intval($user['id']), -1, PREG_SPLIT_NO_EMPTY)));
			}
			$threads = $DB->query("SELECT tid FROM " . TABLE_PREFIX . "thread WHERE postuserid" . $userids . "");
			while ($thread = $DB->fetch_array($threads)) {
				$threadids[] = $thread['tid'];
			}
			$posts = $DB->query("SELECT pid FROM " . TABLE_PREFIX . "post WHERE userid" . $userids . "");
			while ($post = $DB->fetch_array($posts)) {
				$postids[] = $post['pid'];
			}
			$mod->thread_delete($threadids, 0);
			$mod->post_delete($postids);
		} else {
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "post SET userid=0 WHERE userid" . $userids . "");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "thread SET postuserid=0 WHERE postuserid" . $userids . "");
		}
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "user WHERE id" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "userextra WHERE id" . $userids . "");
		// $DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."userblog WHERE id".$userids."" );
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "pm WHERE fromuserid" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "pmtext WHERE fromuserid" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "pmuserlist WHERE userid" . $userids . " OR contactid" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "subscribethread WHERE userid" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "subscribeforum WHERE userid" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "useractivation WHERE userid" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "useractivation WHERE userid" . $userids . "");
		$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "session WHERE userid" . $userids . "");
		// $DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."blog WHERE userid".$userids."" );
		// $DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."blogcontents WHERE userid".$userids."" );
		$forums->func->check_cache('stats');
		$user = $DB->query_first("SELECT count(*) as counts FROM " . TABLE_PREFIX . "user WHERE usergroupid <> 2");
		$forums->cache['stats']['numbermembers'] = intval($user['counts']);
		$user = $DB->query_first("SELECT id, name FROM " . TABLE_PREFIX . "user WHERE usergroupid <> 2 ORDER BY id DESC LIMIT 0, 1");
		$forums->cache['stats']['newusername'] = $user['name'];
		$forums->cache['stats']['newuserid'] = $user['id'];
		$forums->func->update_cache(array('name' => 'stats'));
	}

	function deleteuser()
	{
		global $DB, $forums, $bbuserinfo, $_INPUT;
		if (! $_INPUT['u']) {
			$forums->main_msg = $forums->lang['noids'];
			$this->search_form();
		}
		if (strstr($_INPUT['u'], ',')) {
			$ids = explode(',', $_INPUT['u']);
		} else {
			$ids = array($_INPUT['u']);
		}
		$DB->query("SELECT id, name FROM " . TABLE_PREFIX . "user WHERE id IN (" . implode(",", $ids) . ")");
		$names = array();
		while ($r = $DB->fetch_array()) {
			if ($r['id'] == $bbuserinfo['id']) {
				$forums->admin->print_cp_error($forums->lang['cannotdeletemine']);
			}
			$names[] = $r['name'];
		}
		if (! count($names)) {
			$forums->main_msg = $forums->lang['cannotfounduser'];
			$this->search_form();
		}
		if ($_INPUT['update']) {
			$this->dodeleteuser($ids);
			$forums->admin->save_log($forums->lang['deleteduser'] . " ( " . implode(",", $names) . " )");
			$forums->admin->redirect("user.php", $forums->lang['manageuser'], $forums->lang['userdeleted']);
		} else {
			$pagetitle = $forums->lang['deleteuser'] . " - " . implode(",", $names);
			$detail = $forums->lang['confirmdeleteuser'];
			$forums->admin->nav[] = array('', $forums->lang['deleteuser']);
			$forums->admin->print_cp_header($pagetitle, $detail);
			$forums->admin->columns[] = array("&nbsp;" , "60%");
			$forums->admin->columns[] = array("&nbsp;" , "40%");
			$forums->admin->print_form_header(array(1 => array('do', 'deleteuser'), 2 => array('u', $_INPUT['u']), 3 => array('update', 1)));
			$forums->admin->print_table_start($forums->lang['deleteuser'] . " - " . implode(",", $names));
			$forums->lang['areyousuredeleteuser'] = sprintf($forums->lang['areyousuredeleteuser'], implode(",", $names));
			$forums->admin->print_cells_single_row($forums->lang['areyousuredeleteuser'], "center");
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['onetimedeletepost'] . "</strong><div class='description'>" . $forums->lang['onetimedeletepostdesc'] . "</div>", $forums->admin->print_yes_no_row("deletepost", 1)));
			$forums->admin->print_form_submit($forums->lang['confirmdelete']);
			$forums->admin->print_table_footer();
			$forums->admin->print_form_end();
			$forums->admin->print_cp_footer();
		}
	}

	function search_form()
	{
		global $forums, $DB, $_INPUT;
		if (($_INPUT['gotcount'] > 1 AND $_INPUT['fromdel']) OR ($_INPUT['gotcount'] AND ! $_INPUT['fromdel'])) {
			$_INPUT['searchtype'] = 'normal';
			$this->searchresults();
		}
		$pagetitle = $forums->lang['edituser'];
		$detail = $forums->lang['finduserinfo'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$user_group = array(0 => array('', $forums->lang['anyusergroup']));
		$DB->query("SELECT usergroupid, grouptitle FROM " . TABLE_PREFIX . "usergroup ORDER BY grouptitle");
		while ($r = $DB->fetch_array()) {
			$user_group[] = array($r['usergroupid'] , $r['grouptitle']);
		}
		$forums->admin->print_form_header(array(1 => array('do' , 'searchresults'),));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['finduser']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['username'] . "</strong><div class='description'>" . $forums->lang['usernamedesc'] . "</div>",
				$forums->admin->print_input_select_row('namewhere', array(0 => array('begin', $forums->lang['namebegin']),
						1 => array('is', $forums->lang['exactmatch']),
						2 => array('contains', $forums->lang['nameinclude']),
						3 => array('end', $forums->lang['nameend'])
						), $_INPUT['namewhere']
					)
				 . '&nbsp;' . $forums->admin->print_input_row("name", $_INPUT['name'])
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['usergroup'] . "</strong>" ,
				$forums->admin->print_input_select_row("usergroupid", $user_group, $_INPUT['usergroupid'])
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['membergroup'] . "</strong>" ,
				$forums->admin->print_input_select_row("membergroupid", $user_group, $_INPUT['membergroupid'])
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['searchtype'] . "</strong>",
				$forums->admin->print_input_select_row("searchtype", array(0 => array('normal', $forums->lang['searchuseredit']), 1 => array('prune' , $forums->lang['batchdeleteuser'])), $_INPUT['searchtype'])
				));
		$forums->admin->print_cells_single_row($forums->lang['optionalsearchparts'], "left", "pformstrip");
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['emailinclude'] . "</strong>", $forums->admin->print_input_row("email", $_INPUT['email'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['tempbanuser'] . "</strong>",
				$forums->admin->print_input_select_row("suspended", array(0 => array('0', $forums->lang['any']), 1 => array('yes', $forums->lang['yes']), 2 => array('no', $forums->lang['no'])), $_INPUT['suspended'])
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['ipaddressinclude'] . "</strong>", $forums->admin->print_input_row("host", $_INPUT['host'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['qqinclude'] . "</strong>", $forums->admin->print_input_row("qq", $_INPUT['qq'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['ucinclude'] . "</strong>", $forums->admin->print_input_row("uc", $_INPUT['uc'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['popoinclude'] . "</strong>", $forums->admin->print_input_row("popo", $_INPUT['popo'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['skypeinclude'] . "</strong>", $forums->admin->print_input_row("skype", $_INPUT['skype'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['msninclude'] . "</strong>", $forums->admin->print_input_row("msn", $_INPUT['msn'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['aiminclude'] . "</strong>", $forums->admin->print_input_row("aim", $_INPUT['aim'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['icqinclude'] . "</strong>", $forums->admin->print_input_row("icq", $_INPUT['icq'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['yahooinclude'] . "</strong>", $forums->admin->print_input_row("yahoo", $_INPUT['yahoo'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['signatureinclude'] . "</strong>", $forums->admin->print_input_row("signature", $_INPUT['signature'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['postslessthan'] . "</strong>", $forums->admin->print_input_row("posts", $_INPUT['posts'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['regdateinter'] . " (YYYY-MM-DD)</strong><div class='description'>" . $forums->lang['regdateinterdesc'] . "</div>",
				$forums->lang['from'] . ' ' . $forums->admin->print_input_row("registered_first", $_INPUT['registered_first'], '', '', 10) . ' ' . $forums->lang['to'] . ' ' . $forums->admin->print_input_row("registered_last", $_INPUT['registered_last'], '', '', 10)
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['lastpostinter'] . " (YYYY-MM-DD)</strong><div class='description'>" . $forums->lang['lastpostinterdesc'] . "</div>" ,
				$forums->lang['from'] . ' ' . $forums->admin->print_input_row("lastpost_first", $_INPUT['lastpost_first'], '', '', 10) . ' ' . $forums->lang['to'] . ' ' . $forums->admin->print_input_row("lastpost_last", $_INPUT['lastpost_last'], '', '', 10)
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['lastactivityinter'] . " (YYYY-MM-DD)</strong><div class='description'>" . $forums->lang['lastactivityinterdesc'] . "</div>" ,
				$forums->lang['from'] . ' ' . $forums->admin->print_input_row("lastactivity_first", $_INPUT['lastactivity_first'], '', '', 10) . ' ' . $forums->lang['to'] . ' ' . $forums->admin->print_input_row("lastactivity_last", $_INPUT['lastactivity_last'], '', '', 10)
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['onceoperateusers'] . "</strong><div class='description'>" . $forums->lang['operateusersdesc'] . "</div>", $forums->admin->print_input_row("operateuser", $_INPUT['operateuser'] ? intval($_INPUT['operateuser']) : 50)));
		$forums->admin->print_form_submit($forums->lang['finduser']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function searchresults()
	{
		global $forums, $DB, $_INPUT;
		$page_query = "";
		$un_all = "";
		$query = array();
		$date_keys = array('registered_first', 'registered_last', 'lastpost_first', 'lastpost_last', 'lastactivity_first', 'lastactivity_last');
		foreach(array('name', 'email', 'host', 'qq', 'uc', 'popo', 'skype', 'aim', 'icq', 'yahoo', 'msn', 'signature', 'posts', 'suspended', 'registered_first', 'registered_last', 'lastpost_first', 'lastpost_last', 'lastactivity_first', 'lastactivity_last', 'usergroupid', 'membergroupid') as $bit) {
			$_INPUT[$bit] = rawurldecode(trim($_INPUT[$bit]));
			$page_query .= '&amp;' . $bit . '=' . urlencode($_INPUT[ $bit ]);
			if ($_INPUT[ $bit ]) {
				if (in_array($bit, $date_keys)) {
					list($year, $month, $day) = explode('-', $_INPUT[ $bit ]);
					if (! checkdate($month, $day, $year)) {
						$forums->lang['inputdateerror'] = sprintf($forums->lang['inputdateerror'], $year, $month, $day);
						$forums->main_msg = $forums->lang['inputdateerror'];
						$this->search_form();
					}
					$time_int = $forums->func->mk_time(0, 0 , 0, $month, $day, $year);
					$tmp_bit = str_replace('_first' , '', $bit);
					$tmp_bit = str_replace('_last' , '', $tmp_bit);
					$tmp_bit = str_replace('registered', 'joindate', $tmp_bit);
					if (strstr($bit, '_first')) {
						$query[] = $tmp_bit . ' > ' . $time_int;
					} else {
						$query[] = $tmp_bit . ' < ' . $time_int;
					}
				} else if ($bit == 'usergroupid') {
					if ($_INPUT['usergroupid'] != '') {
						$query[] = "usergroupid=" . $_INPUT['usergroupid'];
					}
				} else if ($bit == 'membergroupid') {
					if ($_INPUT['membergroupid'] != '') {
						$query[] = "(membergroupids LIKE ('" . $_INPUT['membergroupid'] . ",%') OR membergroupids LIKE ('%," . $_INPUT['membergroupid'] . "') OR membergroupids LIKE ('%," . $_INPUT['membergroupid'] . ",%') OR membergroupids =" . $_INPUT['membergroupid'] . ")";
					}
				} else if ($bit == 'posts') {
					$query[] = "posts <=" . $_INPUT[$bit];
				} else if ($bit == 'suspended') {
					if ($_INPUT[$bit] == 'yes') {
						$query[] = "liftban <> ''";
					} else if ($_INPUT[$bit] == 'no') {
						$query[] = "liftban = ''";
					}
				} else if ($bit == 'name') {
					$start_bit = '%';
					$end_bit = '%';
					if ($_INPUT['namewhere'] == 'begin') {
						$start_bit = '';
					} else if ($_INPUT['namewhere'] == 'end') {
						$end_bit = '';
					} else if ($_INPUT['namewhere'] == 'is') {
						$end_bit = '';
						$start_bit = '';
					}
					$name = "LOWER(name) LIKE concat('" . $start_bit . "','" . $forums->func->strtolower($_INPUT[$bit]) . "','" . $end_bit . "')";
					$query[] = $name;
				} else {
					$query[] = $bit . " LIKE '%" . $_INPUT[$bit] . "%'";
				}
			}
		}
		if ($_INPUT['searchtype'] != 'normal') {
			$query[] = "usergroupid != 4";
		}
		if (count($query)) {
			$where = ' WHERE ' . implode(" AND ", $query);
		}
		$first = intval($_INPUT['pp']);
		$end = intval($_INPUT['pp'] + $_INPUT['operateuser']);
		if ($_INPUT['operateuser']) {
			$limit = " LIMIT {$first},{$end}";
		}
		$query = "SELECT *, id as userid FROM " . TABLE_PREFIX . "user" . $where . " ORDER BY name$limit";
		$pquery = "SELECT *, id as userid FROM " . TABLE_PREFIX . "user" . $where . "";
		$count = $DB->query_first("SELECT COUNT(*) as count FROM " . TABLE_PREFIX . "user" . $where . "");
		if ($count['count'] < 1) {
			$forums->main_msg = $forums->lang['nomatchresult'];
			$this->search_form();
		}
		if ($_INPUT['searchtype'] != 'normal') {
			$ids = array();
			$DB->query($pquery);
			while ($r = $DB->fetch_array()) {
				$ids[ $r['id'] ] = array($r['id'], $r['name']);
			}
			$this->user_prune_confirm($ids, $query);
			exit();
		}
		$page_query .= '&amp;searchtype=normal&amp;namewhere=' . $_INPUT['namewhere'] . '&amp;gotcount=' . $count['count'] . '&amp;operateuser=' . $_INPUT['operateuser'];
		$pagetitle = $forums->lang['usersearchresult'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$pages = $forums->func->build_pagelinks(array('totalpages' => $count['count'],
				'perpage' => $_INPUT['operateuser'],
				'curpage' => $first,
				'pagelink' => "user.php?{$forums->sessionurl}do={$_INPUT['do']}" . $page_query,
				)
			);
		echo "<script type='text/javascript'>\n";
		echo "function js_user_jump(userinfo)\n";
		echo "{\n";
		echo "value = eval('document.cpform.u' + userinfo + '.options[document.cpform.u' + userinfo + '.selectedIndex].value');\n";
		echo "window.location = 'user.php?{$forums->js_sessionurl}&do=' + value + '&u=' + userinfo;\n";
		echo "}\n";
		echo "</script>\n";
		$forums->admin->columns[] = array($forums->lang['userid'], "5%");
		$forums->admin->columns[] = array($forums->lang['username'], "10%");
		$forums->admin->columns[] = array($forums->lang['joinipaddress'], "15%");
		$forums->admin->columns[] = array($forums->lang['usergroup'], "10%");
		$forums->admin->columns[] = array($forums->lang['email'], "10%");
		$forums->admin->columns[] = array($forums->lang['joindate'], "15%");
		$forums->admin->columns[] = array($forums->lang['posts'], "5%");
		$forums->admin->columns[] = array($forums->lang['action'], "25%");
		$forums->admin->print_form_header();
		$forums->lang['totalrecords'] = sprintf($forums->lang['totalrecords'], $count['count']);
		$forums->admin->print_table_start($forums->lang['totalrecords']);
		$per_row = 3;
		$td_width = 100 / $per_row;
		$count = 0;
		$forums->func->check_cache('usergroup');
		$user = $DB->query($query);
		while ($r = $DB->fetch_array($user)) {
			$count++;
			if ($r['liftban'] == "") {
				$ban = array ('action' => 'banuser', 'text' => $forums->lang['tempbanuser']);
			} else {
				$ban = array ('action' => 'unsuspend', 'text' => $forums->lang['userunsuspend']);
			}
			$joindate = $forums->func->get_date($r['joindate'], 3);

			$forums->admin->print_cells_row(array($r['id'],
					"<a href='user.php?{$forums->sessionurl}do=doform&amp;u=" . $r['id'] . "'><strong>" . $r['name'] . "</strong></a>",
					$r['host'],
					$forums->cache['usergroup'][$r['usergroupid']]['grouptitle'],
					"<a href='mailto:" . $r['email'] . "'>" . $r['email'] . "</a>",
					$joindate,
					$r['posts'],
					$forums->admin->print_input_select_row('u' . $r['id'],
						array(0 => array('doform', $forums->lang['edituserprofile']),
							1 => array('changename', $forums->lang['changeusername']),
							2 => array('changepassword' , $forums->lang['changepassword']),
							3 => array('deleteuser&amp;fromdel=1' , $forums->lang['deleteuser']),
							4 => array($ban['action'], $ban['text']),
							($r['usergroupid'] == 4 ? array('adminperms' , $forums->lang['setadminperms']) : '')
							), '', "onchange='js_user_jump(" . $r['id'] . ");'") . "<input type='button' class='button' value='" . $forums->lang['ok'] . "' onclick='js_user_jump(" . $r['id'] . ");' />",
					));
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		echo "<div align='right'>{$pages}</div>\n";
		$forums->admin->print_cp_footer();
	}

	function useredit($type = 'edit')
	{
		global $forums, $DB, $_INPUT, $_USEROPTIONS, $bbuserinfo, $bboptions;
		require_once(ROOT_PATH . "includes/functions_codeparse.php");
		$parser = new functions_codeparse();
		$user_group = array();
		$show_fixed = false;
		if ($type == 'edit') {
			if ($_INPUT['u'] == "") {
				$forums->admin->print_cp_error($forums->lang['noids']);
			}
			if (!$user = $DB->query_first("SELECT up.*, u.* FROM " . TABLE_PREFIX . "user u LEFT JOIN " . TABLE_PREFIX . "userexpand up USING(id) WHERE u.id='" . $_INPUT['u'] . "'")) {
				$forums->admin->print_cp_error($forums->lang['cannotfounduser']);
			}
			$options = $forums->func->convert_bits_to_array($user['options'], $_USEROPTIONS);
			$user = array_merge($user, $options);
			$pagetitle = $forums->lang['edituser'] . ": " . $user['name'] . " (ID: " . $user['id'] . ")";
			$detail = $forums->lang['edituserdesc'];
			$page_array = array(1 => array('do', 'doedit'), 2 => array('u', $user['id']), 3 => array('curemail', $user['email']));
		} else {
			$user['usergroupid'] = 3;
			$pagetitle = $forums->lang['addnewuser'];
			$detail = $forums->lang['addnewuserdesc'];
			$page_array = array(1 => array('do', 'adduser'));
		}
		$units = array(0 => array('h', $forums->lang['hours']), 1 => array('d', $forums->lang['days']));
		$member_group[] = array('-1' , $forums->lang['nomembergroupids']);
		$DB->query("SELECT usergroupid, grouptitle FROM " . TABLE_PREFIX . "usergroup ORDER BY grouptitle");
		while ($r = $DB->fetch_array()) {
			$member_group[] = $user_group[] = array($r['usergroupid'] , $r['grouptitle']);
		}
		if ($bbuserinfo['usergroupid'] != 4) {
			if ($user['usergroupid'] == 4) {
				$show_fixed = true;
			}
		}
		$forums->admin->nav[] = array('', $forums->lang['edituser']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header($page_array);
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['userbasicsetting']);
		if ($type == 'edit') {
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['registeredips'] . "</strong>" , $user['host'] ? $user['host'] : '??????'));
		} else {
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['username'] . "</strong>", $forums->admin->print_input_row("name", $_INPUT['name'])));
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['password'] . "</strong>", $forums->admin->print_input_row("password", $_INPUT['password'])));
		}
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['emailaddress'] . "</strong>", $forums->admin->print_input_row("email", $_INPUT['email'] ? $_INPUT['email'] : $user['email'])));
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['usergroupoptions']);
		if ($show_fixed != true) {
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['mastergroup'] . "</strong><br /><div class='description'>" . $forums->lang['mastergroupdesc'] . "</div>" ,
					$forums->admin->print_input_select_row("usergroupid", $user_group, $user['usergroupid'])
					));
			$arr = explode(",", $user['membergroupids']);
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['membersgroup'] . "</strong><div class='description'>" . $forums->lang['membersgroupdesc'] . "</div>" ,
					$forums->admin->print_multiple_select_row("membergroupids[]", $member_group, $arr, 5)
					));
		} else {
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['mastergroup'] . "</strong>" ,
					$forums->admin->print_hidden_row(array(1 => array('usergroupid' , $user['usergroupid']))) . "<strong>" . $forums->lang['admingroup'] . "</strong> (" . $forums->lang['cannotchanged'] . ")",
					));
		}
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array("&nbsp;", "40%");
		$forums->admin->columns[] = array("&nbsp;", "60%");
		$forums->admin->print_table_start($forums->lang['userpostperms']);
		$mod_checked = "";
		$mod_arr = array();
		if ($user['moderate'] == 1) {
			$mod_checked = 'checked';
		} elseif ($user['moderate'] > 0) {
			$mod_arr = $forums->func->banned_detect($user['moderate']);
			$hours = ceil(($mod_arr['date_end'] - time()) / 3600);
			if ($hours > 24 AND (($hours / 24) == ceil($hours / 24))) {
				$mod_arr['units'] = 'd';
				$mod_arr['timespan'] = $hours / 24;
			} else {
				$mod_arr['units'] = 'h';
				$mod_arr['timespan'] = $hours;
			}
			$mod_extra = "<br /><span style='color:yellow'>" . $forums->lang['timespanchanged'] . "</span>";
		}
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['usermoderatepost'] . "</strong><br />" . $forums->lang['usermoderatepostdesc'] ,
				"<input type='checkbox' name='mod_indef' value='1' $mod_checked /> " . $forums->lang['usermoderatepost'] . "
																 <br /><strong>" . $forums->lang['or'] . "</strong> " . $forums->admin->print_input_row('mod_timespan', $mod_arr['timespan'], "text", "", '5') . '&nbsp;' . $forums->admin->print_input_select_row('mod_units', $units, $mod_arr['units']) . $forums->lang['modposttime'] . $mod_extra
				));
		$checked = "";
		$post_arr = array();
		if ($user['forbidpost'] == 1) {
			$checked = 'checked="checked"';
		} else if ($user['forbidpost'] > 0) {
			$post_arr = $forums->func->banned_detect($user['forbidpost']);
			$hours = ceil(($post_arr['date_end'] - time()) / 3600);
			if ($hours > 24 AND (($hours / 24) == ceil($hours / 24))) {
				$post_arr['units'] = 'd';
				$post_arr['timespan'] = $hours / 24;
			} else {
				$post_arr['units'] = 'h';
				$post_arr['timespan'] = $hours;
			}
			$post_extra = "<br /><span style='color:yellow'>" . $forums->lang['timespanchanged'] . "</span>";
		}
		$forums->lang['forbiduserpost'] = sprintf($forums->lang['forbiduserpost'], $user['name']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['forbiduserpost'] . "</strong>" ,
				"<input type='checkbox' name='post_indef' value='1' $checked /> " . $forums->lang['forbidpost'] . "
																 <br /><strong>" . $forums->lang['or'] . "</strong> " . $forums->admin->print_input_row('post_timespan', $post_arr['timespan'], "text", "", '5') . '&nbsp;' . $forums->admin->print_input_select_row('post_units', $units, $post_arr['units']) . $forums->lang['forbidposttime'] . $post_extra
				));
		$forums->admin->print_table_footer();
		$forums->func->cache_styles();
		foreach($forums->func->stylecache AS $style) {
			$styles[] = array($style[styleid], $forums->func->construct_depth_mark($style['depth'], '--') . $style[title]);
		}
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['userforumsetting']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['usestyles'] . "</strong>" ,
				$forums->admin->print_input_select_row("style",
					$styles,
					$user['style'] != "" ? $user['style'] : $def_style
					)
				));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['usedstonoff'] . "</strong>", $forums->admin->print_yes_no_row("options[dstonoff]", $user['dstonoff'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['showsignatures'] . "</strong>", $forums->admin->print_yes_no_row("options[showsignatures]", $user['showsignatures'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['showavatars'] . "</strong>", $forums->admin->print_yes_no_row("options[showavatars]", $user['showavatars'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['pmpop'] . "</strong>", $forums->admin->print_yes_no_row("options[pmpop]", $user['pmpop'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['hideemail'] . "</strong>", $forums->admin->print_yes_no_row("options[hideemail]", $user['hideemail'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['emailonpm'] . "</strong>", $forums->admin->print_yes_no_row("options[emailonpm]", $user['emailonpm'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['adminemail'] . "</strong>", $forums->admin->print_yes_no_row("options[adminemail]", $user['adminemail'])));
		$forums->admin->print_table_footer();
		$forums->admin->print_table_start($forums->lang['useravatar']);
		$avatar_info = $forums->func->get_avatar($user['avatarlocation'], 1, $user['avatarsize'], $user['avatartype']);
		$avatar = $avatar_info ? $avatar_info['src'] : $forums->lang['noavatars'];
		if ($avatar_info['type'] == 'img') {
			$avatar = "<img class='avatar' src='{$avatar_info['src']}' width='{$avatar_info['width']}' height='{$avatar_info['height']}' border='0' alt='' />";
		} elseif ($avatar_info['type'] == 'object') {
			$avatar = "<object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' width='{$avatar_info['width']}' height='{$avatar_info['height']}'><param name='movie' value='{$bboptions['uploadurl']}/avatar/{$avatar_info['value']}'><param name='play' value='true'><param name='loop' value='true'><param name='quality' value='high'><embed src='{$bboptions['uploadurl']}/avatar/{$avatar_info['value']}' width='{$avatar_info['width']}' height='{$avatar_info['height']}' play='true' loop='true' quality='high'></embed></object>";
		} elseif ($avatar_info['type'] == 'sysimg') {
			$avatar = "<img class='avatar' src='{$avatar_info['src']}' border='0' alt='' />";
		}
		$forums->admin->print_cells_single_row($avatar . "<div><input type='submit' name='changeavatar' value='" . $forums->lang['changeavatar'] . "' id='button' /></div>", 'center');
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['othercontactinfo']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['qq'] . "</strong>", $forums->admin->print_input_row("qq", $user['qq'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['uc'] . "</strong>", $forums->admin->print_input_row("uc", $user['uc'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['popo'] . "</strong>", $forums->admin->print_input_row("popo", $user['popo'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['skype'] . "</strong>", $forums->admin->print_input_row("skype", $user['skype'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['aim'] . "</strong>", $forums->admin->print_input_row("aim", $user['aim'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['icq'] . "</strong>", $forums->admin->print_input_row("icq", $user['icq'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['yahoo'] . "</strong>", $forums->admin->print_input_row("yahoo", $user['yahoo'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['msn'] . "</strong>", $forums->admin->print_input_row("msn", $user['msn'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['website'] . "</strong>", $forums->admin->print_input_row("website", $user['website'])));
		$forums->admin->print_table_footer();

		$forums->admin->columns[] = array("" , "5%");
		$forums->admin->columns[] = array("" , "20%");
		$forums->admin->columns[] = array("" , "20%");
		$forums->admin->columns[] = array("" , "55%");
		$forums->admin->print_table_start($forums->lang['userawardsetting']);
		$awarddata = $user['award_data'];
		$useraward = explode(",", $awarddata);
		$DB->query("SELECT * FROM " . TABLE_PREFIX . "award ORDER BY id");
		while ($row = $DB->fetch_array()) {
			$id = $row['id'];
			$checked = "";
			if (in_array($id, $useraward)) {
				$checked = 'checked="checked"';
			}
			$image = $row['img'] ? "<img src='" . ((substr($row['img'], 0, 7) != 'http://' AND substr($row['img'], 0, 1) != '/') ? '../' : '') . $row['img'] . "' alt='" . $row['name'] . "' align='middle' />" : '&nbsp;';
			$forums->admin->print_cells_row(array("<div align='center'><input type='checkbox' name='award[$id]' value='1' $checked /></div>",
					"<div align='center'>" . $image . "</div>",
					"<b>" . $row['name'] . "</b>",
					"<b>" . $row['explanation'] . "</b>",
					));
		}
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['dateprofile']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['joindate'] . "</strong>", $forums->admin->print_input_row("joindate", $user['joindate'] ? $forums->func->get_date($user['joindate'], 3) : '')));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['lastvisittime'] . "</strong>", $forums->admin->print_input_row("lastvisit", $user['lastvisit'] ? $forums->func->get_date($user['lastvisit'], 2) : '')));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['lastactivitytime'] . "</strong>", $forums->admin->print_input_row("lastactivity", $user['lastactivity'] ? $forums->func->get_date($user['lastactivity'], 2) : '')));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['lastposttime'] . "</strong>", $forums->admin->print_input_row("lastpost", $user['lastpost'] ? $forums->func->get_date($user['lastpost'], 2) : '')));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['userbirthday'] . "</strong>", $forums->admin->print_input_row("birthday", $user['birthday'])));
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['otherprofile']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['customtitle'] . "</strong>", $forums->admin->print_input_row("customtitle", $user['customtitle'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['posts'] . "</strong>", $forums->admin->print_input_row("posts", $user['posts'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['quintessence'] . "</strong>", $forums->admin->print_input_row("quintessence", $user['quintessence'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['location'] . "</strong>", $forums->admin->print_input_row("location", $user['location'])));

		if (preg_match ("#<!--sig_img--><div>(.+?)</div><!--sig_img1-->#", $user['signature'], $match)) {
			$user['signature'] = preg_replace("#<!--sig_img-->(.+?)<!--sig_img1-->#", "", $user['signature']);
		}
		$sigimg = preg_replace('#<img[^>]+src=(\'|")./data/signature/(\S+?)(\\1).*>#siU', '\2', $match[1]);
		$sigimg = $sigimg ? "<img src='../data/signature/{$sigimg}' border='0' onclick='javascript:window.open(this.src);' alt='' style='CURSOR: pointer' onload='javascript:if(this.width>screen.width-300)this.style.width=screen.width-300;'><br />" : "";
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['signature'] . "</strong>", $forums->admin->print_textarea_row("signature", $parser->unconvert($user['signature']))));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['deletesigimg'] . "</strong>", $sigimg . $forums->admin->print_yes_no_row("deletesigimg", 0)));

		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['bankprofile']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['reputation'] . "</strong>", $forums->admin->print_input_row("reputation", $user['reputation'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['usercash'] . "</strong>", $forums->admin->print_input_row("cash", $user['cash'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['banksaved'] . "</strong>", $forums->admin->print_input_row("bank", $user['bank'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['mkaccount'] . "</strong>", $forums->admin->print_input_row("mkaccount", $user['mkaccount'] ? $forums->func->get_date($user['mkaccount'], 2) : '')));

		$forums->func->check_cache('credit');
		if ($forums->cache['credit']['list']) {
			$forums->admin->print_table_footer();

			$forums->admin->columns[] = array("&nbsp;" , "40%");
			$forums->admin->columns[] = array("&nbsp;" , "60%");
			$forums->admin->print_table_start($forums->lang['credit_mod']);

			foreach ($forums->cache['credit']['list'] AS $tag_name => $name) {
				$forums->admin->print_cells_row(array("<strong>" . $name . "</strong>", $forums->admin->print_input_row("credit_expand[" . $tag_name . "]", $user[$tag_name])));
			}
		}
		$forums->admin->print_form_submit($forums->lang['douseredit']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function douseredit($type = 'edit')
	{
		global $forums, $DB, $_INPUT, $_USEROPTIONS, $bboptions;
		$newuserbit = array();
		$userid = '';
		if ($type == 'edit') {
			$_INPUT['u'] = intval($_INPUT['u']);
			$user = $DB->query_first("SELECT up.*, up.id AS expandid, u.* FROM " . TABLE_PREFIX . "user u LEFT JOIN " . TABLE_PREFIX . "user up USING(id) WHERE u.id='" . $_INPUT['u'] . "'");
			if (!$user['expandid']) {
				$DB->query("INSERT INTO " . TABLE_PREFIX . "userexpand (id) VALUES (" . $user['id'] . ")");
			}
			$olduserinfo = $forums->func->fetch_user($user);
			$user['options'] = $forums->func->convert_array_to_bits(array_merge($olduserinfo, $_INPUT['options']), $_USEROPTIONS);
			if ($_INPUT['email'] != $_INPUT['curemail']) {
				if ($DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE email='" . $_INPUT['email'] . "' AND id <> " . $_INPUT['u'] . "")) {
					$forums->main_msg = $forums->lang['otheruserusemail'];
					$this->useredit('edit');
				}
			}
			if (is_array($_INPUT['credit_expand'])) {
				foreach ($_INPUT['credit_expand'] AS $tag_name => $value) {
					$expand_up[] = "$tag_name = " . intval($value) ;
				}
				$DB->shutdown_query("UPDATE " . TABLE_PREFIX . "userexpand SET " . implode(", ", $expand_up) . " WHERE id=" . $user['id'] . "");
			}
			$userid = 'id=' . $_INPUT['u'];
			$mod_log = $forums->lang['edituser'] . " - '{$user['name']}'";
			$redirect = $forums->lang['useredited'];
		} else {
			foreach(array($forums->lang['username'] => 'name', $forums->lang['password'] => 'password', $forums->lang['email'] => 'email', $forums->lang['usergroup'] => 'usergroupid') AS $key => $field) {
				if ($_INPUT[ $field ] == "") {
					$forums->lang['mustuserfield'] = sprintf($forums->lang['mustuserfield'], $key);
					$forums->main_msg = $forums->lang['mustuserfield'];
					$this->useredit('add');
				}
			}
			$in_username = trim($_INPUT['name']);
			if ($DB->query_first("SELECT u.*, g.* FROM " . TABLE_PREFIX . "user u, " . TABLE_PREFIX . "usergroup g WHERE (LOWER(u.name)='" . $forums->func->strtolower($in_username) . "' OR u.name='" . $in_username . "') AND u.usergroupid=g.usergroupid")) {
				$forums->lang['usernameexist'] = sprintf($forums->lang['usernameexist'], $in_username);
				$forums->main_msg = $forums->lang['usernameexist'];
				$this->useredit('add');
			}
			$in_password = md5(trim($_INPUT['password']));
			$in_email = trim($forums->func->strtolower($_INPUT['email']));
			if ($email_check = $DB->query_first("SELECT id FROM " . TABLE_PREFIX . "user WHERE email='" . $in_email . "'")) {
				$forums->main_msg = $forums->lang['otheruserusemail'];
				$this->useredit('add');
			}
			$salt = $forums->func->generate_user_salt(5);
			$saltpassword = md5($in_password . $salt);
			$user['options'] = $forums->func->convert_array_to_bits($_INPUT['options'], $_USEROPTIONS);
			$newuserbit = array ('name' => $in_username,
				'salt' => $salt,
				'password' => $saltpassword,
				'joindate' => TIMENOW,
				'timezoneoffset' => $bboptions['timezoneoffset'],
				'pmtotal' => 0,
				'pmunread' => 0,
				);
			$mod_log = $forums->lang['adduser'] . " - '{$in_username}'";
			$redirect = $forums->lang['useradded'];
		}
		$forbidpost = 0;
		$mod_queue = 0;
		if ($_INPUT['mod_indef'] == 1) {
			$mod_queue = 1;
		} elseif ($_INPUT['mod_timespan'] > 0) {
			$mod_queue = $forums->func->banned_detect(array('timespan' => intval($_INPUT['mod_timespan']), 'unit' => $_INPUT['mod_units']));
		}
		if ($_INPUT['post_indef'] == 1) {
			$forbidpost = 1;
		} elseif ($_INPUT['post_timespan'] > 0) {
			$forbidpost = $forums->func->banned_detect(array('timespan' => intval($_INPUT['post_timespan']), 'unit' => $_INPUT['post_units']));
		}
		if ($_INPUT['membergroupids'] == '' OR in_array(-1, $_INPUT['membergroupids'])) {
			$membergroupids = '';
		} else {
			$membergroupids = implode(",", $_INPUT['membergroupids']);
		}
		$_INPUT['joindate'] = $_INPUT['joindate'] ? strtotime($_INPUT['joindate']) : $_INPUT['joindate'];
		$_INPUT['lastvisit'] = $_INPUT['lastvisit'] ? strtotime($_INPUT['lastvisit']) : $_INPUT['lastvisit'];
		$_INPUT['lastactivity'] = $_INPUT['lastactivity'] ? strtotime($_INPUT['lastactivity']) : $_INPUT['lastactivity'];
		$_INPUT['lastpost'] = $_INPUT['lastpost'] ? strtotime($_INPUT['lastpost']) : $_INPUT['lastpost'];
		require_once(ROOT_PATH . "includes/functions_codeparse.php");
		$parser = new functions_codeparse();
		$signature = $parser->convert(array('text' => $forums->func->stripslashes_uni($forums->func->htmlspecialchars_uni($_POST['signature'])),
				'allowsmilies' => 1,
				'allowcode' => $bboptions['signatureallowbbcode'],
				'allowhtml' => $bboptions['signatureallowhtml'],
				));

		if (preg_match("#<!--sig_img-->(.+?)<!--sig_img1-->#is", $user['signature'], $match)) {
			if ($_INPUT['deletesigimg']) {
				foreach(array('swf', 'jpg', 'jpeg', 'gif', 'png') as $extension) {
					if (@file_exists(ROOT_PATH . "data/signature/sig-" . $user['id'] . "." . $extension)) {
						@unlink(ROOT_PATH . "data/signature/sig-" . $user['id'] . "." . $extension);
					}
				}
			} else {
				$signature .= $match[0];
			}
		}

		if ($_INPUT['award']) {
			foreach ($_INPUT['award'] AS $key => $value) {
				$key = intval($key);
				$has_award[] = $key;
			}
		}
		$userbit = array ('forbidpost' => $forbidpost,
			'usergroupid' => $_INPUT['usergroupid'],
			'customtitle' => $_INPUT['customtitle'],
			'style' => $_INPUT['style'] ? $_INPUT['style'] : 0,
			'options' => $user['options'],
			'email' => $_INPUT['email'],
			'posts' => $_INPUT['posts'] ? $_INPUT['posts'] : 0,
			'quintessence' => $_INPUT['quintessence'] ? $_INPUT['quintessence'] : 0,
			'moderate' => $mod_queue,
			'membergroupids' => $membergroupids,
			'qq' => $_INPUT['qq'] ? $_INPUT['qq'] : 0,
			'uc' => $_INPUT['uc'] ? $_INPUT['uc'] : 0,
			'skype' => $_INPUT['skype'],
			'popo' => $_INPUT['popo'],
			'aim' => $_INPUT['aim'],
			'icq' => $_INPUT['icq'] ? $_INPUT['icq'] : 0,
			'yahoo' => $_INPUT['yahoo'],
			'msn' => $_INPUT['msn'],
			'website' => $_INPUT['website'],
			'location' => $_INPUT['location'],
			'signature' => $signature,
			'birthday' => $_INPUT['birthday'] ? $_INPUT['birthday'] : '',
			'reputation' => $_INPUT['reputation'] ? $_INPUT['reputation'] : 0,
			'cash' => $_INPUT['cash'] ? $_INPUT['cash'] : 0,
			'lastpost' => $_INPUT['lastpost'] ? $_INPUT['lastpost'] : TIMENOW,
			'lastactivity' => $_INPUT['lastactivity'] ? $_INPUT['lastactivity'] : TIMENOW,
			'lastvisit' => $_INPUT['lastvisit'] ? $_INPUT['lastvisit'] : TIMENOW,
			'joindate' => $_INPUT['joindate'] ? $_INPUT['joindate'] : TIMENOW,
			'award_data' => is_array($has_award) ? "," . implode(",", $has_award) . "," : "",
			);
		if ($_INPUT['mkaccount']) {
			$mkaccount = explode(' ', $_INPUT['mkaccount']);
			list($year, $month, $day) = explode('-', $mkaccount[0]);
			list($hour, $minute, $second) = explode(':', $mkaccount[1]);
			$year = intval($year);
			$month = intval($month);
			$day = intval($day);
			$hour = intval($hour);
			$minute = intval($minute);
			$second = intval($second);
			$mkaccounttime = $forums->func->mk_time($hour, $minute , $second, $month, $day, $year);
		}
		if ($_INPUT['bank']) {
			$userbit['bank'] = intval($_INPUT['bank']);
			$userbit['mkaccount'] = $mkaccounttime ? $mkaccounttime : TIMENOW;
		}
		$userbit = array_merge($newuserbit, $userbit);
		$forums->func->fetch_query_sql($userbit, 'user', $userid);
		$page_query = "";
		foreach(array('name', 'suspended', 'registered_first', 'registered_last', 'lastpost_first', 'lastpost_last', 'lastactivity_first', 'lastactivity_last', 'usergroupid', 'namewhere', 'gotcount', 'fromdel') AS $bit) {
			$page_query .= '&amp;' . $bit . '=' . trim($_INPUT[ $bit ]);
		}
		if ($type == 'add') {
			$user['id'] = $DB->insert_id();

			if (is_array($_INPUT['credit_expand'])) {
				foreach ($_INPUT['credit_expand'] AS $tag_name => $value) {
					$expand_key .= ", $tag_name";
					$expand_up .= ", " . intval($value) ;
				}
				$DB->shutdown_query("INSERT INTO " . TABLE_PREFIX . "userexpand (id{$expand_key}) VALUES (" . $user['id'] . "{$expand_up})");
			}

			$forums->func->check_cache('stats');
			$forums->cache['stats']['newusername'] = $in_username;
			$forums->cache['stats']['newuserid'] = $user['id'];
			$forums->cache['stats']['numbermembers']++;
			$forums->func->update_cache(array('name' => 'stats'));
		}
		$forums->admin->save_log($mod_log);
		if ($_INPUT['changeavatar']) {
			$forums->admin->redirect("user.php?do=changeavatar&amp;u={$user['id']}", $forums->lang['userupdated'], $forums->lang['redirectavatargallery']);
		} else if ($_INPUT['usergroupid'] == 4 OR preg_match("/,4,/", "," . $membergroupids . ",")) {
			$forums->admin->redirect("user.php?do=adminperms&amp;u={$user['id']}", $forums->lang['userupdated'], $forums->lang['redirectadminperms']);
		} else if ($_INPUT['usergroupid'] == 7) {
			$forums->admin->redirect("moderate.php?userid={$user['id']}", $forums->lang['userupdated'], $forums->lang['redirectmodperms']);
		} else {
			$forums->admin->redirect("user.php", $forums->lang['manageuser'], $redirect);
		}
	}

	function changeavatar()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$userid = intval($_INPUT['u']);
		if (!$userid) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$user = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id=$userid");
		$forums->lang['changeuseravatar'] = sprintf($forums->lang['changeuseravatar'], $user['name'], $user['id']);
		$pagetitle = $forums->lang['changeuseravatar'];
		$detail = $forums->lang['changeuseravatardesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header(array(1 => array('do' , 'dochangeavatar'), 2 => array('u', $userid)), '', "enctype='multipart/form-data'");
		list($user['avatar_width'] , $user['avatar_height']) = explode ("x", $user['avatarsize']);
		list($bboptions['av_width'], $bboptions['av_height']) = explode ("x", $bboptions['avatardimension']);
		list($w, $h) = explode ("x", $bboptions['avatardimensiondefault']);
		$avatar_info = $forums->func->get_avatar($user['avatarlocation'], 1, $user['avatarsize'], $user['avatartype']);
		$avatar = $avatar_info ? $avatar_info['src'] : $forums->lang['noavatars'];
		if ($avatar_info['type'] == 'img') {
			$avatar = "<img class='avatar' src='{$avatar_info['src']}' width='{$avatar_info['width']}' height='{$avatar_info['height']}' border='0' alt='' />";
		} elseif ($avatar_info['type'] == 'object') {
			$avatar = "<object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' width='{$avatar_info['width']}' height='{$avatar_info['height']}'><param name='movie' value='{$bboptions['uploadurl']}/avatar/{$avatar_info['value']}'><param name='play' value='true'><param name='loop' value='true'><param name='quality' value='high'><embed src='{$bboptions['uploadurl']}/avatar/{$avatar_info['value']}' width='{$avatar['width']}' height='{$avatar_info['height']}' play='true' loop='true' quality='high'></embed></object>";
		} elseif ($avatar_info['type'] == 'sysimg') {
			$avatar = "<img class='avatar' src='{$avatar_info['src']}' border='0' alt='' />";
		}
		$allowed_files = implode (' .', explode("|", $bboptions['avatarextension']));
		if ($bboptions['allowflash'] != 1) {
			$allowed_files = str_replace(".swf", "", $allowed_files);
		}
		$avatar_gallery = array();
		$i = 0;
		$dh = opendir(ROOT_PATH . 'images/avatars');
		while ($file = readdir($dh)) {
			if (is_dir(ROOT_PATH . 'images/avatars/' . $file)) {
				if ($file != "." && $file != "..") {
					$i++;
					if ($i == 1) $def_gallary = $file;
					$categories[] = array($file, str_replace("_", " ", $file));
				}
			}
		}
		closedir($dh);
		if (is_array($categories)) {
			reset($categories);
			$forums->admin->columns[] = array("&nbsp;" , "40%");
			$forums->admin->columns[] = array("&nbsp;" , "60%");
			$forums->admin->print_table_start($forums->lang['avatargallerylist']);
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['canusegallerylist'] . "</strong>", $forums->admin->print_input_select_row("gallerylist", $categories, $_INPUT['gallerylist'], "onsubmit='document.gallerylist.submit()'") . "&nbsp;<input type='submit' value='" . $forums->lang['ok'] . "' class='button' />"));
			$forums->admin->print_table_footer();
			$forums->admin->print_form_end();
			$currentavatar = $_INPUT['gallerylist'] ? trim($_INPUT['gallerylist']) : $def_gallary;
			$forums->admin->print_form_header(array(1 => array('do' , 'dochangeavatar'), 2 => array('u', $userid), 3 => array('MAX_FILE_SIZE', '9000000'), 4 => array('current_folder', $currentavatar)));
			$forums->admin->print_table_start($forums->lang['currentavatargallery']);
			echo "<table cellpadding='4' cellspacing='0' border='0' width='100%'>\n";
			$dh = opendir(ROOT_PATH . 'images/avatars/' . $currentavatar);
			while ($file = readdir($dh)) {
				if (! preg_match("/^..?$|^index|^\.ds_store|^\.htaccess/i", $file)) {
					if (is_file(ROOT_PATH . "images/avatars/" . $currentavatar . "/" . $file)) {
						if (preg_match("/\.(gif|jpg|jpeg|png|swf)$/i", $file)) {
							$galleryimages[] = $file;
						}
					}
				}
			}
			if (is_array($galleryimages) AND count($galleryimages)) {
				natcasesort($galleryimages);
				reset($galleryimages);
			}
			closedir($dh);
			$colspan = $bboptions['avatarcolspannumbers'] == "" ? 5 : $bboptions['avatarcolspannumbers'];
			$gal_found = count($galleryimages);
			$posthash = $this->posthash;
			$current_folder = urlencode($selectedavatar);
			$c = 0;
			$avatar_list = '';
			if (is_array($galleryimages) AND count($galleryimages)) {
				foreach($galleryimages AS $img) {
					$c++;
					if ($c == 1) {
						$avatar_list .= "<tr>";
					}
					$image = str_replace("_", " ", preg_replace("/^(.*)\.\w+$/", "\\1", $img));
					echo "<td align='center' class='tdrow1' width='20%'><img src='../images/avatars/" . $currentavatar . "/" . $img . "' border='0' alt='' /><br /><input type='radio' class='radiobutton' name='avatarid' value='" . urlencode($img) . "' id='" . urlencode($img) . "' />&nbsp;<strong><label for='" . urlencode($img) . "'>" . $image . "</label></strong></td>";
					if ($c == $colspan) {
						echo "</tr>";
						$c = 0;
					}
				}
			}
			if ($c != $colspan) {
				for ($i = $c ; $i < $colspan ; ++$i) {
					echo "<td class='tdrow1' width='20%'>&nbsp;</td>";
				}
				echo "</tr>";
			}
			echo "</table><br/>\n";
		}
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['customavatar']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['currentusedavatar'] . "</strong>", $avatar));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['avatarparameter'] . "</strong>", "<input type='radio' name='avatarid' value='-2' />" . $forums->lang['usecustomavatar'] . " <input type='radio' name='avatarid' value='-1' />" . $forums->lang['deleteavatar']));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['uploadurlavatar'] . "</strong>", $forums->admin->print_input_row("avatarurl", 'http://')));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['uploadlocalavatar'] . "</strong>", $forums->admin->print_input_row("upload_avatar", '', 'file')));

		if ($bboptions['disableavatarsize']) {
			$forums->lang['avatarsizelimit'] = sprintf($forums->lang['avatarsizelimit'], $bboptions['av_width'], $bboptions['av_height'], $bboptions['avatamaxsize'], $allowed_files);
			$forums->admin->print_cells_single_row($forums->lang['avatarsizelimit'], 'center');
		} else {
			$forums->lang['avatarsizechange'] = sprintf($forums->lang['avatarsizechange'], $bboptions['av_width'], $bboptions['av_height'], $allowed_files);
			$forums->admin->print_cells_single_row($forums->lang['avatarsizechange'], 'center');
		}
		$forums->admin->print_form_submit($forums->lang['ok']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function clean_avatars($id)
	{
		global $bboptions;
		foreach(array('swf', 'jpg', 'jpeg', 'gif', 'png') as $extension) {
			if (@file_exists($bboptions['uploadfolder'] . "/avatar-" . $id . "." . $extension)) {
				@unlink($bboptions['uploadfolder'] . "/avatar-" . $id . "." . $extension);
			}
		}
	}

	function dochangeavatar()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$userid = intval($_INPUT['u']);
		if (!$userid) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if (!$_INPUT['avatarid']) {
			$forums->admin->print_cp_error($forums->lang['requireavatartype']);
		}
		if ($_INPUT['avatarid'] == -1) {
			$this->clean_avatars($userid);
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "user SET avatarlocation='', avatarsize='', avatartype='' WHERE id=" . $userid . "");
		} else if ($_INPUT['avatarid'] != -2) {
			$real_choice = 'noavatar';
			$real_dims = '';
			$real_dir = "";
			$save_dir = "";
			$current_folder = preg_replace("/[^\s\w_-]/", "", rawurldecode($_INPUT['current_folder']));
			$selected_avatar = preg_replace("/[^\s\w\._\-\[\]\(\)]/" , "", rawurldecode($_INPUT['avatarid']));
			if ($current_folder != "") {
				$real_dir = "/" . $current_folder;
				$save_dir = $current_folder . "/";
			}
			$avatar_gallery = array();
			$dh = opendir(ROOT_PATH . 'images/avatars' . $real_dir);
			while ($file = readdir($dh)) {
				if (!preg_match("/^..?$|^index/i", $file)) {
					$avatar_gallery[] = $file;
				}
			}
			closedir($dh);
			if (!in_array($selected_avatar, $avatar_gallery)) {
				$forums->admin->print_cp_error($forums->lang['notfoundavatar']);
			}
			$final_string = $save_dir . $selected_avatar;
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "user SET avatarlocation='" . addslashes($final_string) . "', avatartype=3 WHERE id='" . $userid . "'");
		} else {
			list($p_width, $p_height) = explode("x", $bboptions['avatardimension']);
			if (preg_match("/^http:\/\/$/i", $_INPUT['avatarurl'])) {
				$_INPUT['avatarurl'] = "";
			}
			if (empty($_INPUT['avatarurl'])) {
				if ($_FILES['upload_avatar']['name'] != "" AND ($_FILES['upload_avatar']['name'] != "none")) {
					$path = $bboptions['uploadfolder'] . '/avatar';
					if (! is_dir($path)) {
						if (! @ mkdir($path, 0777)) {
							$forums->admin->print_cp_error($forums->lang['avatarcannotwrite']);
						}
						@chmod($path, 0777);
					}
					$this->clean_avatars($userid);
					$real_name = 'avatar-' . $userid;
					$real_type = 2;
					require_once(ROOT_PATH . 'includes/functions_upload.php');
					$upload = new functions_upload();
					$upload->filename = 'avatar-' . $userid;
					$upload->filepath = $path;
					$upload->maxfilesize = ($bboptions['avatamaxsize'] * 1024) * 8;
					$upload->upload_form = 'upload_avatar';
					$upload->allow_extension = array('swf', 'jpg', 'jpeg', 'gif', 'png');
					$upload->upload_process();
					if ($upload->error_no) {
						switch ($upload->error_no) {
							case 1:
								$forums->admin->print_cp_error($forums->lang['avataruploaderror1']);
							case 2:
								$forums->admin->print_cp_error($forums->lang['avataruploaderror2']);
							case 3:
								$forums->admin->print_cp_error($forums->lang['avataruploaderror3']);
							case 4:
								$forums->admin->print_cp_error($forums->lang['avataruploaderror4']);
						}
					}
					$real_name = $upload->parsed_file_name;
					if (! $bboptions['disableavatarsize'] AND $upload->file_extension != '.swf') {
						require_once(ROOT_PATH . 'includes/functions_image.php');
						$image = new functions_image();
						$image->filepath = $path;
						$image->filename = $real_name;
						$image->thumb_filename = 'thumb_' . $userid;
						$image->thumbswidth = $p_width;
						$image->thumbsheight = $p_height;
						$return = $image->generate_thumbnail();
						$im['img_width'] = $return['thumbwidth'];
						$im['img_height'] = $return['thumbheight'];
						if (strstr($return['thumblocation'], 'thumb_')) {
							@unlink($path . "/" . $real_name);
							$real_name = 'avatar-' . $userid . '.' . $image->file_extension;
							@rename($path . "/" . $return['thumblocation'], $path . "/" . $real_name);
							@chmod($path . "/" . $real_name, 0777);
						}
					} else {
						if (! $img_size = @GetImageSize($path . '/' . $real_name)) {
							$img_size[0] = $p_width;
							$img_size[1] = $p_height;
						}
						$w = $img_size[0] ? intval($img_size[0]) : $p_width;
						$h = $img_size[1] ? intval($img_size[1]) : $p_height;
						$im['img_width'] = $w > $p_width ? $p_width : $w;
						$im['img_height'] = $h > $p_height ? $p_height : $h;
					}
					if (@filesize($path . "/" . $real_name) > ($bboptions['avatamaxsize'] * 1024)) {
						@unlink($path . "/" . $real_name);
						$forums->admin->print_cp_error($forums->lang['avataruponlimit']);
					}
					$real_choice = $real_name;
					$real_dims = $im['img_width'] . 'x' . $im['img_height'];
				} else {
					$forums->admin->print_cp_error($forums->lang['selectuploadavatar']);
				}
			} else {
				$_INPUT['avatarurl'] = trim($_INPUT['avatarurl']);
				$ext = explode (",", $bboptions['avatarextension']);
				$checked = 0;
				$av_ext = preg_replace("/^.*\.(\S+)$/", "\\1", $_INPUT['avatarurl']);
				foreach ($ext AS $v) {
					if (strtolower($v) == strtolower($av_ext)) {
						$checked = 1;
					}
				}
				if ($checked != 1) {
					$forums->admin->print_cp_error($forums->lang['avataruploaderror2']);
				}
				if (! $img_size = @GetImageSize($_INPUT['avatarurl'])) {
					$img_size[0] = $p_width;
					$img_size[1] = $p_height;
				}
				$im = array();
				if (! $bboptions['disableavatarsize']) {
					require_once(ROOT_PATH . 'includes/functions_image.php');
					$image = new functions_image();

					$im = $image->scale_image(array('max_width' => $p_width,
							'max_height' => $p_height,
							'cur_width' => $img_size[0],
							'cur_height' => $img_size[1]
							));
				} else {
					$w = $img_size[0] ? intval($img_size[0]) : $p_width;
					$h = $img_size[1] ? intval($img_size[1]) : $p_height;
					$im['img_width'] = $w > $p_width ? $p_width : $w;
					$im['img_height'] = $h > $p_height ? $p_height : $h;
				}
				$this->clean_avatars($userid);
				$real_choice = $_INPUT['avatarurl'];
				$real_dims = $im['img_width'] . 'x' . $im['img_height'];
				$real_type = 1;
			}
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "user SET avatarlocation='" . $real_choice . "', avatarsize='" . $real_dims . "', avatartype=" . $real_type . " WHERE id='" . $userid . "'");
		}
		$forums->admin->redirect("user.php?do=changeavatar&amp;u=$userid", $forums->lang['manageuser'], $forums->lang['useravatarupdated']);
	}

	function adminperms()
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if (!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditadmins']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$userid = intval($_INPUT['u']);
		if (!$userid) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if (!$user = $DB->query_first("SELECT id,name, usergroupid, membergroupids FROM " . TABLE_PREFIX . "user WHERE id=" . $userid . "")) {
			if ($user['usergroupid'] != 4 AND !preg_match("/,4,/i", "," . $user['membergroupids'] . ",") AND !preg_match("/," . $user['id'] . ",/i", "," . SUPERADMIN . ",")) {
				$forums->admin->print_cp_error($forums->lang['noids']);
			}
		}
		$adminperms = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "administrator WHERE aid=" . $user['id'] . "");

		$pagetitle = $forums->lang['manageadminperms'];
		$detail = $forums->lang['manageadminpermsdesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header(array(1 => array('do' , 'doadminperms'), 2 => array('u', $userid)));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->lang['adminuserperms'] = sprintf($forums->lang['adminuserperms'], $user['name']);
		$forums->admin->print_table_start($forums->lang['adminuserperms']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditsettings'] . "</strong>", $forums->admin->print_yes_no_row("caneditsettings", $adminperms['caneditsettings'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditforums'] . "</strong>", $forums->admin->print_yes_no_row("caneditforums", $adminperms['caneditforums'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditusers'] . "</strong>", $forums->admin->print_yes_no_row("caneditusers", $adminperms['caneditusers'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditgroups'] . "</strong>", $forums->admin->print_yes_no_row("caneditusergroups", $adminperms['caneditusergroups'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincanmassprune'] . "</strong>", $forums->admin->print_yes_no_row("canmassprunethreads", $adminperms['canmassprunethreads'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincanmassmove'] . "</strong>", $forums->admin->print_yes_no_row("canmassmovethreads", $adminperms['canmassmovethreads'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditattachments'] . "</strong>", $forums->admin->print_yes_no_row("caneditattachments", $adminperms['caneditattachments'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditbbcodes'] . "</strong>", $forums->admin->print_yes_no_row("caneditbbcodes", $adminperms['caneditbbcodes'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditimages'] . "</strong>", $forums->admin->print_yes_no_row("caneditimages", $adminperms['caneditimages'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditbans'] . "</strong>", $forums->admin->print_yes_no_row("caneditbans", $adminperms['caneditbans'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditcaches'] . "</strong>", $forums->admin->print_yes_no_row("caneditcaches", $adminperms['caneditcaches'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditstyles'] . "</strong>", $forums->admin->print_yes_no_row("caneditstyles", $adminperms['caneditstyles'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditcrons'] . "</strong>", $forums->admin->print_yes_no_row("caneditcrons", $adminperms['caneditcrons'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditmysql'] . "</strong>", $forums->admin->print_yes_no_row("caneditmysql", $adminperms['caneditmysql'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditothers'] . "</strong>", $forums->admin->print_yes_no_row("caneditothers", $adminperms['caneditothers'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditleagues'] . "</strong>", $forums->admin->print_yes_no_row("caneditleagues", $adminperms['caneditleagues'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincanviewmodlogs'] . "</strong>", $forums->admin->print_yes_no_row("canviewmodlogs", $adminperms['canviewmodlogs'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincanviewadminlogs'] . "</strong>", $forums->admin->print_yes_no_row("canviewadminlogs", $adminperms['canviewadminlogs'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditads'] . "</strong>", $forums->admin->print_yes_no_row("caneditads", $adminperms['caneditads'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditinvite'] . "</strong>", $forums->admin->print_yes_no_row("caneditinvite", $adminperms['caneditinvite'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditbank'] . "</strong>", $forums->admin->print_yes_no_row("caneditbank", $adminperms['caneditbank'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditjs'] . "</strong>", $forums->admin->print_yes_no_row("caneditjs", $adminperms['caneditjs'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincanpms'] . "</strong>", $forums->admin->print_yes_no_row("cansendpms", $adminperms['cansendpms'])));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['admincaneditadmins'] . "</strong>", $forums->admin->print_yes_no_row("caneditadmins", $adminperms['caneditadmins'])));

		$forums->admin->print_form_submit($forums->lang['ok']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function doadminperms()
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if (!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditadmins']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$adminperms = $DB->query_first("SELECT aid FROM " . TABLE_PREFIX . "administrator WHERE aid=" . intval($_INPUT['u']) . "");
		$admin = array('aid' => intval($_INPUT['u']),
			'caneditsettings' => $_INPUT['caneditsettings'],
			'caneditforums' => $_INPUT['caneditforums'],
			'caneditusers' => $_INPUT['caneditusers'],
			'caneditusergroups' => $_INPUT['caneditusergroups'],
			'canmassprunethreads' => $_INPUT['canmassprunethreads'],
			'canmassmovethreads' => $_INPUT['canmassmovethreads'],
			'caneditattachments' => $_INPUT['caneditattachments'],
			'caneditbbcodes' => $_INPUT['caneditbbcodes'],
			'caneditimages' => $_INPUT['caneditimages'],
			'caneditbans' => $_INPUT['caneditbans'],
			'caneditstyles' => $_INPUT['caneditstyles'],
			'caneditcaches' => $_INPUT['caneditcaches'],
			'caneditcrons' => $_INPUT['caneditcrons'],
			'caneditmysql' => $_INPUT['caneditmysql'],
			'caneditothers' => $_INPUT['caneditothers'],
			'caneditleagues' => $_INPUT['caneditleagues'],
			'caneditadmins' => $_INPUT['caneditadmins'],
			'canviewadminlogs' => $_INPUT['canviewadminlogs'],
			'canviewmodlogs' => $_INPUT['canviewmodlogs'],
			'caneditads' => $_INPUT['caneditads'],
			'caneditinvite' => $_INPUT['caneditinvite'],
			'caneditbank' => $_INPUT['caneditbank'],
			'cansendpms' => $_INPUT['cansendpms'],
			'caneditjs' => $_INPUT['caneditjs'],
			);
		if ($adminperms['aid']) {
			$forums->func->fetch_query_sql($admin, 'administrator', 'aid=' . $adminperms['aid']);
		} else {
			$forums->func->fetch_query_sql($admin, 'administrator');
		}
		$forums->admin->redirect("user.php?do=adminlist", $forums->lang['manageuser'], $forums->lang['adminpermsupdated']);
	}

	function adminlist()
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if (!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditadmins']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$image = "<img src='{$forums->imageurl}/check.gif' border='0' alt='X' />";
		$pagetitle = $forums->lang['adminpermslist'];
		$detail = $forums->lang['adminpermslistdesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->columns[] = array($forums->lang['username'], "");
		$forums->admin->columns[] = array($forums->lang['superadmin'], "");
		$forums->admin->columns[] = array($forums->lang['generalsettings'], "");
		$forums->admin->columns[] = array($forums->lang['boardsetting'], "");
		$forums->admin->columns[] = array($forums->lang['user'], "");
		$forums->admin->columns[] = array($forums->lang['usergroup'], "");
		$forums->admin->columns[] = array($forums->lang['massprune'], "");
		$forums->admin->columns[] = array($forums->lang['massmove'], "");
		$forums->admin->columns[] = array($forums->lang['attachment'] , "");
		$forums->admin->columns[] = array($forums->lang['bbcode'], "");
		$forums->admin->columns[] = array($forums->lang['imagemanage'], "");
		$forums->admin->columns[] = array($forums->lang['banmanage'], "");
		$forums->admin->columns[] = array($forums->lang['cache'], "");
		$forums->admin->columns[] = array($forums->lang['style'], "");
		$forums->admin->columns[] = array($forums->lang['cron'], "");
		$forums->admin->columns[] = array($forums->lang['sql'], "");
		$forums->admin->columns[] = array($forums->lang['system'], "");
		$forums->admin->columns[] = array($forums->lang['modlog'], "");
		$forums->admin->columns[] = array($forums->lang['adminlog'], "");
		$forums->admin->columns[] = array($forums->lang['ads'], "");
		$forums->admin->columns[] = array($forums->lang['itinvite'], "");
		$forums->admin->columns[] = array($forums->lang['bank'], "");
		$forums->admin->columns[] = array($forums->lang['js'], "");
		$forums->admin->columns[] = array($forums->lang['pms'], "");
		$forums->admin->columns[] = array($forums->lang['adminperms'], "");
		$forums->admin->columns[] = array($forums->lang['action'], "");
		$forums->admin->print_table_start($forums->lang['adminpermslist']);
		$sadmins = $DB->query("SELECT id,name FROM " . TABLE_PREFIX . "user WHERE id IN (" . implode(',', $admin) . ")");
		if ($DB->num_rows($sadmins)) {
			while ($supadmin = $DB->fetch_array($sadmins)) {
				$cache[$supadmin['id']] = $supadmin['id'];
				$forums->admin->print_cells_row(array($supadmin['name'], $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, $image, '&nbsp;'));
			}
		}
		$nadmins = $DB->query("SELECT a.*,u.name, u.usergroupid, u.membergroupids FROM " . TABLE_PREFIX . "administrator a LEFT JOIN " . TABLE_PREFIX . "user u ON (a.aid=u.id)");
		if ($DB->num_rows($nadmins)) {
			while ($nadmin = $DB->fetch_array($nadmins)) {
				if ($nadmin['usergroupid'] != 4 AND !preg_match("/,4,/i", "," . $nadmin['membergroupids'] . ",") AND !preg_match("/," . $nadmin['id'] . ",/i", "," . SUPERADMIN . ",")) {
					$del_ids[] = $nadmin['aid'];
					continue;
				}
				if ($nadmin['aid'] == $cache[$nadmin['aid']]) continue;

				$caneditsettings = $nadmin['caneditsettings'] ? $image : '&nbsp;';
				$caneditforums = $nadmin['caneditforums'] ? $image : '&nbsp;';
				$caneditusers = $nadmin['caneditusers'] ? $image : '&nbsp;';
				$caneditusergroups = $nadmin['caneditusergroups'] ? $image : '&nbsp;';
				$canmassprunethreads = $nadmin['canmassprunethreads'] ? $image : '&nbsp;';
				$canmassmovethreads = $nadmin['canmassmovethreads'] ? $image : '&nbsp;';
				$caneditattachments = $nadmin['caneditattachments'] ? $image : '&nbsp;';
				$caneditbbcodes = $nadmin['caneditbbcodes'] ? $image : '&nbsp;';
				$caneditimages = $nadmin['caneditimages'] ? $image : '&nbsp;';
				$caneditbans = $nadmin['caneditbans'] ? $image : '&nbsp;';
				$caneditstyles = $nadmin['caneditstyles'] ? $image : '&nbsp;';
				$caneditcaches = $nadmin['caneditcaches'] ? $image : '&nbsp;';
				$caneditcrons = $nadmin['caneditcrons'] ? $image : '&nbsp;';
				$caneditmysql = $nadmin['caneditmysql'] ? $image : '&nbsp;';
				$caneditothers = $nadmin['caneditothers'] ? $image : '&nbsp;';
				$caneditleagues = $nadmin['caneditleagues'] ? $image : '&nbsp;';
				$caneditadmins = $nadmin['caneditadmins'] ? $image : '&nbsp;';
				$canviewadminlogs = $nadmin['canviewadminlogs'] ? $image : '&nbsp;';
				$canviewmodlogs = $nadmin['canviewmodlogs'] ? $image : '&nbsp;';
				$caneditads = $nadmin['caneditads'] ? $image : '&nbsp;';
				$caneditinvite = $nadmin['caneditinvite'] ? $image : '&nbsp;';
				$caneditbank = $nadmin['caneditbank'] ? $image : '&nbsp;';
				$caneditjs = $nadmin['caneditjs'] ? $image : '&nbsp;';
				$cansendpms = $nadmin['cansendpms'] ? $image : '&nbsp;';

				$forums->admin->print_cells_row(array($nadmin['name'], '&nbsp;', $caneditsettings, $caneditforums, $caneditusers, $caneditusergroups, $canmassprunethreads, $canmassmovethreads, $caneditattachments, $caneditbbcodes, $caneditimages, $caneditbans, $caneditcaches, $caneditstyles, $caneditcrons, $caneditmysql, $caneditothers, $canviewmodlogs, $canviewadminlogs, $caneditads, $caneditinvite, $caneditbank, $caneditjs, $cansendpms, $caneditadmins, '<a href="user.php?' . $forums->sessionurl . 'do=adminperms&amp;u=' . $nadmin['aid'] . '">' . $forums->lang['edit'] . '</a>'));
			}
			if (is_array($del_ids)) {
				$DB->shutdown_query("DELETE FROM " . TABLE_PREFIX . "administrator WHERE aid IN (" . implode(",", $del_ids) . ")");
			}
		}

		$forums->admin->print_table_footer();
		$forums->admin->print_cp_footer();
	}

	function joinuser()
	{
		global $forums, $DB, $_INPUT;

		$pagetitle = $forums->lang['joinuser'];
		$detail = $forums->lang['joinuserdesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);

		$dojoin_checked = $_INPUT['dojoinid'] ? "checked='checked'" : '';
		$tojoin_checked = $_INPUT['tojoinid'] ? "checked='checked'" : '';

		$forums->admin->print_form_header(array(1 => array('do' , 'dojoin'),));
		$forums->admin->columns[] = array("&nbsp;" , "40%");
		$forums->admin->columns[] = array("&nbsp;" , "60%");
		$forums->admin->print_table_start($forums->lang['joinuser']);
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['dojoinuser'] . "</strong>",
				$forums->admin->print_input_row("joinuser", $_INPUT['joinuser']) . "<input type='checkbox' name='dojoinid' value='1' $dojoin_checked /> " . $forums->lang['useuserid']));
		$forums->admin->print_cells_row(array("<strong>" . $forums->lang['tojoinuser'] . "</strong><div class='description'>" . $forums->lang['tojoinuserdesc'] . "</div>" ,
				$forums->admin->print_input_row("tojoinuser", $_INPUT['tojoinuser']) . "<input type='checkbox' name='tojoinid' value='1' $tojoin_checked /> " . $forums->lang['useuserid']
				));
		$forums->admin->print_form_submit($forums->lang['joinuser']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function dojoin()
	{
		global $forums, $DB, $_INPUT;
		$forums->func->check_cache('usergroup');
		if ($_INPUT['update']) {
			if ($_INPUT['joinuser'] == $_INPUT['tojoinuser']) {
				$forums->admin->print_cp_error($forums->lang['joinusererror']);
			}
			$checkjoinuser = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id='" . intval($_INPUT['joinuser']) . "'");
			if (!$checkjoinuser['id']) {
				$forums->admin->print_cp_error($forums->lang['cannotfinduser']);
			}
			$checktojoin = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE id='" . intval($_INPUT['tojoinuser']) . "'");
			if (!$checktojoin['id']) {
				$forums->admin->print_cp_error($forums->lang['cannotfinduser']);
			}
			if ($checktojoin['id'] == $bbuserinfo['id']) {
				$forums->admin->print_cp_error($forums->lang['cannotjoinself']);
			}
			$admin = explode(',', SUPERADMIN);
			if (in_array($checktojoin['id'], $admin)) {
				$forums->admin->print_cp_error($forums->lang['cannotjoinadmin']);
			}
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "adminlog SET userid={$checkjoinuser['id']} WHERE userid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "announcement SET userid={$checkjoinuser['id']} WHERE userid = {$checktojoin['id']}");
			// $DB->query_unbuffered("UPDATE ".TABLE_PREFIX."blog SET userid={$checkjoinuser['id']} WHERE userid = {$checktojoin['id']}");
			// $DB->query_unbuffered("UPDATE ".TABLE_PREFIX."blogcontents SET userid={$checkjoinuser['id']} WHERE userid = {$checktojoin['id']}");
			// $DB->query_unbuffered("UPDATE ".TABLE_PREFIX."blogsearch SET userid={$checkjoinuser['id']} WHERE userid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "moderator SET userid={$checkjoinuser['id']} WHERE userid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "moderatorlog SET userid={$checkjoinuser['id']} WHERE userid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "pm SET fromuserid={$checkjoinuser['id']} WHERE fromuserid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "pm SET touserid={$checkjoinuser['id']} WHERE touserid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "pmtext SET fromuserid={$checkjoinuser['id']} WHERE fromuserid = {$checktojoin['id']}");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "pmtext WHERE fromuserid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "post SET userid={$checkjoinuser['id']}, username='{$checkjoinuser['name']}' WHERE userid = {$checktojoin['id']}");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "session WHERE userid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "thread SET postuserid={$checkjoinuser['id']}, postusername='{$checkjoinuser['name']}' WHERE postuserid = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "thread SET lastposterid={$checkjoinuser['id']}, lastposter='{$checkjoinuser['name']}' WHERE lastposterid = {$checktojoin['id']}");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "useractivation WHERE userid = {$checktojoin['id']}");
			// $DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."userblog WHERE id = {$checktojoin['id']}");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "userextra WHERE id = {$checktojoin['id']}");
			$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "user SET posts={$checkjoinuser['posts']}+{$checktojoin['posts']}, pmunread={$checkjoinuser['pmunread']}+{$checktojoin['pmunread']}, pmtotal={$checkjoinuser['pmtotal']}+{$checktojoin['pmtotal']}, quintessence={$checkjoinuser['quintessence']}+{$checktojoin['quintessence']}, cash={$checkjoinuser['cash']}+{$checktojoin['cash']}, bank={$checkjoinuser['bank']}+{$checktojoin['bank']}, reputation={$checkjoinuser['reputation']}+{$checktojoin['reputation']} WHERE id = {$checkjoinuser['id']}");
			$DB->query_unbuffered("DELETE FROM " . TABLE_PREFIX . "user WHERE id = {$checktojoin['id']}");

			$forums->admin->redirect("user.php?do=joinuser", $forums->lang['manageuser'], $forums->lang['userhasjoined']);
		} else {
			$pagetitle = $forums->lang['joinuser'];
			$detail = $forums->lang['joinuserdesc'];
			$forums->admin->print_cp_header($pagetitle, $detail);
			if ($_INPUT['dojoinid']) {
				$joinuser = intval($_INPUT['joinuser']);
				$where1 = "id='" . $joinuser . "'";
			} else {
				$joinuser = preg_replace("/\s{2,}/", " ", trim(str_replace('|', '&#124;' , $_INPUT['joinuser'])));
				$where1 = "LOWER(name)='" . $forums->func->strtolower($joinuser) . "' OR name='" . $joinuser . "'";
			}
			if ($_INPUT['tojoinid']) {
				$tojoinuser = intval($_INPUT['tojoinuser']);
				$where2 = "id='" . $tojoinuser . "'";
			} else {
				$tojoinuser = preg_replace("/\s{2,}/", " ", trim(str_replace('|', '&#124;' , $_INPUT['tojoinuser'])));
				$where2 = "LOWER(name)='" . $forums->func->strtolower($tojoinuser) . "' OR name='" . $tojoinuser . "'";
			}

			$checkjoinuser = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE $where1");
			if (!$checkjoinuser['id']) {
				$forums->admin->print_cp_error($forums->lang['cannotfinduser']);
			}

			$checktojoin = $DB->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE $where2");
			if (!$checktojoin['id']) {
				$forums->admin->print_cp_error($forums->lang['cannotfinduser']);
			}

			if ($checkjoinuser['id'] == $checktojoin['id']) {
				$forums->admin->print_cp_error($forums->lang['joinusererror']);
			}

			$forums->admin->print_form_header(array(1 => array('do' , 'dojoin'), 2 => array('update' , 1), 3 => array('joinuser' , $checkjoinuser['id']), 4 => array('tojoinuser' , $checktojoin['id']),));
			$forums->admin->columns[] = array("&nbsp;" , "20%");
			$forums->admin->columns[] = array($forums->lang['dojoinuser'] , "40%");
			$forums->admin->columns[] = array($forums->lang['tojoinuser'] , "40%");

			$forums->admin->print_table_start($forums->lang['confirmjoin']);
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['username'] . "</strong>", $checkjoinuser['name'], $checktojoin['name']));
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['usergroup'] . "</strong>", $forums->cache['usergroup'][$checkjoinuser['usergroupid']]['grouptitle'], $forums->cache['usergroup'][$checktojoin['usergroupid']]['grouptitle']));
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['posts'] . "</strong>", $checkjoinuser['posts'], $checktojoin['posts']));
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['joindate'] . "</strong>", $forums->func->get_date($checkjoinuser['joindate'], 3), $forums->func->get_date($checktojoin['joindate'], 3)));
			$forums->admin->print_cells_row(array("<strong>" . $forums->lang['reputation'] . "</strong>", $checkjoinuser['reputation'], $checktojoin['reputation']));
			$forums->admin->print_form_submit($forums->lang['joinuser']);
			$forums->admin->print_table_footer();
			$forums->admin->print_form_end();
			$forums->admin->print_cp_footer();
		}
	}
}

$output = new user();
$output->show();

?>