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
require ('./global.php');

class award {

	function show()
	{
		global $forums, $_INPUT, $DB, $bbuserinfo;
		$forums->lang = $forums->func->load_lang($forums->lang, 'admin_award' );
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditusers']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$forums->admin->nav[] = array( 'award.php', $forums->lang['manageaward'] );
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$this->cache = new adminfunctions_cache();

		$forums->cache['award'] = array();
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."award ORDER BY id" );
		while ( $r = $DB->fetch_array() ) {
			$forums->cache['award'][$r['id']] = $r;
		}
		$forums->func->check_cache('credit');

		switch($_INPUT['do']) {			
			case 'add':
				$this->award_form('add');
				break;
			case 'doadd':
				$this->save('add');
				break;
			case 'edit':
				$this->award_form('edit');
				break;
			case 'doedit':
				$this->save('edit');
				break;
			case 'delete':
				$this->dodelete();
				break;
			case 'showlist':
				$this->showlist();
				break;
			case 'authorize':
				$this->authorize(1);
				break;
			case 'refuse':
				$this->authorize(-1);
				break;
			case 'finduser':
				$this->finduser();
				break;
			case 'delete_user':
				$this->delete_user_award();
				break;
			default:
				$this->showaward();
				break;
		}
	}

	function delete_user_award()
	{
		global $forums, $DB, $_INPUT;

		if ( ! $_INPUT['id'] ) {
			$forums->main_msg = $forums->lang['noids'];
			$this->showaward();
		}

		if ( ! $forums->cache['award'][$_INPUT['id']]['id'] ) {
			$forums->main_msg = $forums->lang['noids'];
			$this->showaward();
		}

		if (!is_array($_INPUT['user'])) {
			$forums->main_msg = $forums->lang['no_delete_user'];
			$this->finduser();
		}
		foreach ($_INPUT['user'] AS $userid) {
			$userid = intval($userid);
			$userids[] = $userid;
		}
		$DB->query( "SELECT id, award_data FROM ".TABLE_PREFIX."user WHERE id IN (".implode(",", $userids).")" );
		while ($user = $DB->fetch_array()) {
			$new_award = array();
			$award = explode(",", $user['award_data']);
			foreach ($award AS $id) {
				if (!$id OR $id == $_INPUT['id']) continue;
				$new_award[] = $id;
			}
			$userids[] = $user['id'];
			$user_award = count($new_award) ? ",".implode( "," , $new_award )."," : "";
			$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET award_data='".$user_award."' WHERE id=".$user['id']."" );			
		}
		require_once(ROOT_PATH."includes/functions_credit.php");
		$this->credit = new functions_credit();
		$this->credit->update_credit('award', $userids, "-");

		$forums->admin->redirect("award.php?do=finduser&amp;id={$_INPUT['id']}", $forums->lang['manageaward'], $forums->lang['manageaward'] );
	}

	function finduser()
	{
		global $forums, $DB, $_INPUT;
		$_INPUT['id'] = intval($_INPUT['id']);
		if ( ! $_INPUT['id'] ) {
			$forums->main_msg = $forums->lang['noids'];
			$this->showaward();
		}

		if ( ! $forums->cache['award'][$_INPUT['id']]['id'] ) {
			$forums->main_msg = $forums->lang['noids'];
			$this->showaward();
		}

		$pp = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;

		$count = $DB->query_first( "SELECT COUNT(*) as count FROM ".TABLE_PREFIX."user WHERE award_data LIKE '%,".$_INPUT['id'].",%'" );

		$pages = $forums->func->build_pagelinks( array( 'totalpages'  => $count['count'],
											   'perpage'    => 50,
											   'curpage'  => $pp,
											   'pagelink'    => "award.php?{$forums->sessionurl}do=finduser",
											 )
									  );

		$pagetitle  = $forums->lang['manageaward'];
		$forums->admin->print_cp_header($pagetitle);
		$forums->admin->print_form_header( array( 1 => array( 'do', 'delete_user' ), 2 => array( 'id', $_INPUT['id'] ) ) , "mutliact" );
		$forums->admin->columns[] = array( $forums->lang['userid'], "5%" );
		$forums->admin->columns[] = array( $forums->lang['username'], "20%" );
		$forums->admin->columns[] = array( $forums->lang['has_awards'], "45%" );
		$forums->admin->columns[] = array( $forums->lang['gender'], "10%" );
		$forums->admin->columns[] = array( "<input name='allbox' type='checkbox' value='".$forums->lang['selectall']."' onClick='CheckAll(document.mutliact);'>", "5%" );
		$forums->admin->print_table_start( $forums->lang['manageaward'] );

		$DB->query( "SELECT id, name, award_data, gender FROM ".TABLE_PREFIX."user WHERE award_data LIKE '%,".$_INPUT['id'].",%'" );
		if ($DB->num_rows()) {
			while ($user = $DB->fetch_array()) {
				$award = explode(",", $user['award_data']);
				$user_award = "";
				foreach ($award AS $id) {
					if (!$id) continue;
					$user_award .= "<img src='".((substr($forums->cache['award'][$id]['img'], 0, 7) != 'http://' AND substr($forums->cache['award'][$id]['img'], 0, 1) != '/') ? '../' : '').$forums->cache['award'][$id]['img']."' alt='".$forums->cache['award'][$id]['name']."' /> \r\n";
				}
				$forums->admin->print_cells_row( array (
																"<center>{$user['id']}</center>",
																"<center><strong><a href='user.php?{$forums->sessionurl}do=doform&amp;u={$user['id']}' target='_blank'>{$user['name']}</a></strong></center>",
																$user_award,
																"<center>".$user['id'] ? $forums->lang['male'] : $forums->lang['female']."</center>",
																"<center><input name='user[".$user['id']."]' value='{$user['id']}' type='checkbox'></center>",
													 )       );
			}
		} else {
			$forums->admin->print_cells_single_row( $forums->lang['no_award_users'], "center" );
		}

		$forums->admin->print_form_submit($forums->lang['delete_user_award'] ." - ". $forums->cache['award'][$_INPUT['id']]['name']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}
	
	function dodelete()
	{
		global $forums, $DB, $_INPUT;
		if ( ! $_INPUT['id'] ) {
			$forums->main_msg = $forums->lang['noids'];
			$this->showaward();
		}
		$DB->query_unbuffered( "DELETE FROM ".TABLE_PREFIX."award WHERE id=".$_INPUT['id']."" );
		$this->cache->award_recache();
		$this->showaward();
	}	
	
	function save($type='add')
	{
		global $forums, $DB, $_INPUT;
		if ( $type == 'edit' ) {
			if ( ! $_INPUT['id'] ) {
				$forums->main_msg = $forums->lang['noids'];
				$this->award_form($type);
			}
		}
		if ( ! $_INPUT['name'] OR ! $_INPUT['img'] OR ! $_INPUT['explanation'] ) {
			$forums->main_msg = $forums->lang['inputallforms'];
			$this->award_form($type);
		}
		$array = array('name'				=> $_INPUT['name'],
					   'explanation'		=> $forums->func->convert_andstr( $forums->func->stripslashes_uni( $_POST['explanation'] ) ),
                       'img'				=> $_INPUT['img'],
                       'used'				=> $_INPUT['used'],
                       'gender'				=> $_INPUT['gender'],
                       'date'				=> $_INPUT['date'],
                       'onlinetime'				=> $_INPUT['onlinetime'],
                       'posts'				=> $_INPUT['posts'],
                       'reputation'				=> $_INPUT['reputation'],
                       'strategy'				=> $_INPUT['strategy']
			);
		if ( $type == 'add' ) {
			$forums->func->fetch_query_sql( $array, 'award' );
			$forums->main_msg = $forums->lang['awardadded'];
		} else {
			$forums->func->fetch_query_sql( $array, 'award', 'id='.$_INPUT['id'] );
			$forums->main_msg = $forums->lang['awardedited'];
		}
		$this->cache->award_recache();
		$this->showaward();
	}
	
	function award_form($type='add')
	{
		global $forums, $DB, $_INPUT;
		if ( $type == 'edit' ) {
			if ( ! $_INPUT['id'] ) {
				$forums->main_msg = $forums->lang['noids'];
				$this->showaward();
			}
			$award = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."award WHERE id='".$_INPUT['id']."'" );
			$button = $forums->lang['editaward'];
			$code   = 'doedit';
			$pagetitle  = $forums->lang['editaward'].": ".$award['name'];
			$forums->admin->nav[] = array( '' ,$forums->lang['editaward'] );
		} else {
			$award = array();
			$code   = 'doadd';
			$pagetitle  = $forums->lang['editaward'];
			$button = $forums->lang['editaward'];
			$forums->admin->nav[] = array( '' ,$forums->lang['editaward'] );
		}
		$forums->admin->print_cp_header($pagetitle);
		$forums->admin->print_form_header( array( 1 => array( 'do' , $code ), 2 => array( 'id', $_INPUT['id'] ) ) );
		$forums->admin->columns[] = array( "&nbsp;", "40%" );
		$forums->admin->columns[] = array( "&nbsp;", "60%" );
		$forums->admin->print_table_start( $pagetitle );
		$forums->admin->print_cells_row( array( $forums->lang['awardname'], $forums->admin->print_input_row( 'name', $_INPUT['name'] ? $_INPUT['name'] : $award['name'] ) ) );
		$forums->admin->print_cells_row( array( $forums->lang['awardtags'], $forums->admin->print_textarea_row( 'explanation', $_INPUT['explanation'] ? $_INPUT['explanation'] : $award['explanation'] ) ) );
		$forums->admin->print_cells_row( array( $forums->lang['awardbutton'], $forums->admin->print_input_row( 'img', $_INPUT['img'] ? $_INPUT['img'] : $award['img'] ) ) );
		$forums->admin->print_table_footer();

		$forums->admin->columns[] = array( "&nbsp;", "40%" );
		$forums->admin->columns[] = array( "&nbsp;", "60%" );
		$forums->admin->print_table_start( $forums->lang['award_promotion'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['use_award_promotion']."</strong>",
												                 $forums->admin->print_yes_no_row("used", $award['used'] )
									                    )      );

		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['award_gender']."</strong>",
												                 $forums->admin->print_input_select_row( "gender", array( 0 => array( '0', $forums->lang['any_gender'] ), 1 => array( '1' , $forums->lang['male'] ), 2 => array( '2' , $forums->lang['female'] ) ), $award['gender'] )
									                    )      );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['onlinetime']."</strong>",$forums->admin->print_input_row( "onlinetime", $award['onlinetime'] ? $award['onlinetime'] : 500 ) ) );

		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['joindates']."</strong>",$forums->admin->print_input_row( "date", $award['date'] ? $award['date'] : 30 ) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['posts']."</strong>",$forums->admin->print_input_row( "posts", $award['posts'] ? $award['posts'] : 100 ) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['reputation']."</strong>",$forums->admin->print_input_row( "reputation", $award['reputation'] ? $award['reputation'] : 50 ) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['award_promotion_type']."</strong><div class='description'>".$forums->lang['promotionstrategydesc']."</div>",$forums->admin->print_input_select_row( 'strategy', 
					array( 0 => array( '17', $forums->lang['posts'] ),
							1 => array( '18', $forums->lang['joindates'] ),
							2 => array( '19', $forums->lang['reputation'] ),
							3 => array( '1', $forums->lang['complex'].': '.$forums->lang['posts'].$forums->lang['and'].$forums->lang['joindate'].$forums->lang['and'].$forums->lang['reputation'] ),
							4 => array( '2', $forums->lang['complex'].': '.$forums->lang['posts'].$forums->lang['or'].$forums->lang['joindate'].$forums->lang['or'].$forums->lang['reputation'] ),
							5 => array( '3', $forums->lang['complex'].': ('.$forums->lang['posts'].$forums->lang['and'].$forums->lang['joindate'].')'.$forums->lang['or'].$forums->lang['reputation'] ),
							6 => array( '4', $forums->lang['complex'].': ('.$forums->lang['posts'].$forums->lang['or'].$forums->lang['joindate'].')'.$forums->lang['and'].$forums->lang['reputation'] ),
							7 => array( '5', $forums->lang['complex'].': '.$forums->lang['posts'].$forums->lang['and'].'('.$forums->lang['joindate'].$forums->lang['or'].$forums->lang['reputation'].')' ),
							8 => array( '6', $forums->lang['complex'].': '.$forums->lang['posts'].$forums->lang['or'].'('.$forums->lang['joindate'].$forums->lang['and'].$forums->lang['reputation'].')' ),
							9 => array( '7', $forums->lang['complex'].': ('.$forums->lang['posts'].$forums->lang['or'].$forums->lang['reputation'].')'.$forums->lang['and'].$forums->lang['joindate'] ),
							10 => array( '8', $forums->lang['complex'].': ('.$forums->lang['posts'].$forums->lang['and'].$forums->lang['reputation'].')'.$forums->lang['or'].$forums->lang['joindate'] ),
						), $award['strategy']
												  				 								 ) ) );

		$forums->admin->print_form_submit($button);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();

		$forums->admin->print_cp_footer();	
	}

	function showlist()
	{
		global $forums, $DB, $_INPUT;

		$pp = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;

		$pagetitle  = $forums->lang['manageaward'];
		$forums->admin->print_cp_header($pagetitle);
		$forums->admin->columns[] = array( $forums->lang['request_award'], "10%" );
		$forums->admin->columns[] = array( $forums->lang['applicant'], "20%" );
		$forums->admin->columns[] = array( $forums->lang['reason'], "55%" );
		$forums->admin->columns[] = array( $forums->lang['action'], "15%" );
		$forums->admin->print_table_start( $forums->lang['manageaward'] );

		$count = $DB->query_first( "SELECT COUNT(*) as count FROM ".TABLE_PREFIX."award_request" );

		$pages = $forums->func->build_pagelinks( array( 'totalpages'  => $count['count'],
											   'perpage'    => 50,
											   'curpage'  => $pp,
											   'pagelink'    => "award.php?{$forums->sessionurl}do=showlist",
											 )
									  );

		$DB->query( "SELECT ar.*, u.name 
							FROM ".TABLE_PREFIX."award_request ar
							LEFT JOIN ".TABLE_PREFIX."user u ON (u.id=ar.uid)
							ORDER BY dateline LIMIT ".$pp.", 50
						" );
		while ( $row = $DB->fetch_array() ) {

			$image = $forums->cache['award'][$row['aid']]['img'] ? "<img src='".((substr($forums->cache['award'][$row['aid']]['img'], 0, 7) != 'http://' AND substr($forums->cache['award'][$row['aid']]['img'], 0, 1) != '/') ? '../' : '').$forums->cache['award'][$row['aid']]['img']."' alt='".$forums->cache['award'][$row['aid']]['name']."' align='middle' />" : '&nbsp;';

			$forums->admin->print_cells_row( array( "<div align='center'>".$image."</div>",
																	 "<b><a href='./../profile.php?u=".$row['uid']."' target='_blank'>".$row['name']."</a></b><br />".$forums->cache['award'][$row['aid']]['name']."",
																	 "<b>".stripslashes($row['post'])."</b>",
																	 "<div align='center'>".
																	  $forums->admin->print_button($forums->lang['authorize'], "award.php?".$forums->sessionurl."do=authorize&amp;aid={$row['aid']}&amp;uid={$row['uid']}").'&nbsp;'.
																	  $forums->admin->print_button($forums->lang['refuse'], "award.php?".$forums->sessionurl."do=refuse&amp;aid={$row['aid']}&amp;uid={$row['uid']}")
																	 ."</div>",
														   )      );
		}
		$forums->admin->print_cells_single_row( $pages, 'right' );
		$forums->admin->print_table_footer();
		$forums->admin->print_cp_footer();	
	}

	function authorize($type=1)
	{
		global $forums, $DB, $_INPUT;
		$_INPUT['aid'] = intval($_INPUT['aid']);
		$_INPUT['uid'] = intval($_INPUT['uid']);
		if(!$_INPUT['aid'] OR !$_INPUT['uid']) {
			$forums->admin->print_cp_error($forums->lang['cannotfindaward']);
		}
		$user = $DB->query_first("SELECT id, award_data,name FROM ".TABLE_PREFIX."user WHERE id=".$_INPUT['uid']."");
		if ($type == 1) {
			$img = "";			
			$award = explode(",", $user['award_data']);
			if (is_array($award)) {
				foreach ($award AS $id) {
					if (!$id) continue;
					if ($_INPUT['aid'] == $id) {
						$has_update = TRUE;
					}
					$img .= "[img]".$forums->cache['award'][$id]['img']."[/img]";
					$list[] = $id;
				}
			}
			if (!$has_update) {
				$img .= "[img]".$forums->cache['award'][$_INPUT['aid']]['img']."[/img]";
				$list[] = $_INPUT['aid'];
			}
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET award_data = ',".implode(",", $list).",' WHERE id=".$_INPUT['uid']."");
		}
		if ($type == -1) {
			$_INPUT['title'] = $forums->lang['award_refuse'];
			$_POST['post'] = sprintf($forums->lang['award_refuse_msg'], $forums->cache['award'][$_INPUT['aid']]['name']);
		} else {
			$_INPUT['title'] = $forums->lang['award_authorize'];
			$_POST['post'] = sprintf($forums->lang['award_authorize_msg'], $forums->cache['award'][$_INPUT['aid']]['name'], $img);
		}
		$_INPUT['username'] = $user['name'];
		require_once( ROOT_PATH.'includes/functions_private.php' );
		$pm = new functions_private();
		$_INPUT['noredirect'] = 1;		
		$pm->cookie_mxeditor = "wysiwyg";
		$bboptions['pmallowbbcode'] = 1;
		$bboptions['pmallowhtml'] = 1;
		$pm->sendpm();
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."award_request WHERE aid=".$_INPUT['aid']." AND uid=".$_INPUT['uid']."");

		require_once(ROOT_PATH."includes/functions_credit.php");
		$this->credit = new functions_credit();
		$this->credit->update_credit('award', $_INPUT['uid']);

		$forums->admin->redirect("award.php?do=showlist", $forums->lang['manageaward'], $forums->lang['manageaward'] );
	}
	
	function showaward()
	{
		global $forums, $DB;
		$pagetitle  = $forums->lang['manageaward'];
		$forums->admin->print_cp_header($pagetitle);
		$forums->admin->print_form_header( array( 1 => array( 'do', 'add' ) ) );
		$forums->admin->columns[] = array( $forums->lang['awardname'], "20%" );
		$forums->admin->columns[] = array( $forums->lang['awardtags'], "45%" );
		$forums->admin->columns[] = array( $forums->lang['awardbutton'], "5%" );
		$forums->admin->columns[] = array( $forums->lang['option'], "30%" );
		$forums->admin->print_table_start( $forums->lang['manageaward'] );
		$DB->query( "SELECT * FROM ".TABLE_PREFIX."award ORDER BY name" );
		while ( $row = $DB->fetch_array() ) {
			$image = $row['img'] ? "<img src='".((substr($row['img'], 0, 7) != 'http://' AND substr($row['img'], 0, 1) != '/') ? '../' : '').$row['img']."' alt='".$row['name']."' align='middle' />" : '&nbsp;';
			$forums->admin->print_cells_row( array( "<b><a href='award.php?{$forums->sessionurl}do=finduser&amp;id={$row['id']}' title='".$forums->lang['find_award_users']."'>".$row['name']."</a></b>",
																	 "<b>".$row['explanation']."</b>",
																	 "<div align='center'>".$image."</div>",
																	 "<div align='center'>".
																	  $forums->admin->print_button($forums->lang['edit'], "award.php?".$forums->sessionurl."do=edit&amp;id={$row['id']}").'&nbsp;'.
																	  $forums->admin->print_button($forums->lang['delete'], "award.php?".$forums->sessionurl."do=delete&amp;id={$row['id']}")
																	 ."</div>",
														   )      );
		}
		$forums->admin->print_form_submit($forums->lang['addaward']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();	
	}	
}

$output = new award();
$output->show();
?>