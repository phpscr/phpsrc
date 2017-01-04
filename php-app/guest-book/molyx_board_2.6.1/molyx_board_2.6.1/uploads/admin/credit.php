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

class credit {

	function show()
	{
		global $forums, $_INPUT, $DB, $bbuserinfo;
		$forums->lang = $forums->func->load_lang($forums->lang, 'admin_credit' );
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditusers']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}

		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$this->cache = new adminfunctions_cache();

		switch($_INPUT['do']) {			
			case 'add':
				$this->credit_form('add');
				break;
			case 'delete':
				$this->delete_credit();
				break;
			case 'edit':
				$this->credit_form('edit');
				break;
			case 'doedit':
				$this->doedit();
				break;
			default:
				$this->creditlist();
				break;
		}
	}

	function creditlist()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle  = $forums->lang['managecredit'];

		$detail = $forums->lang['managecreditdesc'];
		$forums->admin->nav[] = array( 'credit.php' , $forums->lang['creditlist'] );

		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do' , "add" ) ) );

		$forums->admin->columns[] = array( "ID", "3%" );
		$forums->admin->columns[] = array( $forums->lang['creditname'], "14%" );
		$forums->admin->columns[] = array( $forums->lang['newthread'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['newreply'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['addquin'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['addaward'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['downattach'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['sendpm'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['search'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['creditlimit'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['display'], "7%" );
		$forums->admin->columns[] = array( $forums->lang['action'], "13%" );

		$forums->admin->print_table_start( $pagetitle );

		$DB->query("SELECT * FROM ".TABLE_PREFIX."credit");
		if ($DB->num_rows()) {
			while ($credit = $DB->fetch_array()) {
				foreach($credit AS $key => $value) {
					if (in_array( $key, array( "creditid", "name") )) {
						continue;
					}
					if ($key == "used") {
						$value = $value == 1 ? "<img src='{$forums->imageurl}/check.gif' border='0' alt='X' />" : "&nbsp;";
					} else	if ($value > 0) {
						$value = "<strong>+".$value."</strong>";
					} elseif ($value == 0) {
						$value = "&nbsp;";
					} else {
						$value;
					}
					$credit[$key] = $value;
				}
				$forums->admin->print_cells_row( array( 
										"<center>".$credit['creditid']."</center>",
										"<center>".$credit['name']."</center>",
										"<center>".$credit['newthread']."</center>",
										"<center>".$credit['newreply']."</center>",
										"<center>".$credit['quintessence']."</center>",
										"<center>".$credit['award']."</center>",
										"<center>".$credit['downattach']."</center>",
										"<center>".$credit['sendpm']."</center>",
										"<center>".$credit['search']."</center>",
										"<center>".$credit['c_limit']."</center>",
										"<center>".$credit['used']."</center>",
										"<center><a href='credit.php?{$forums->sessionurl}do=edit&amp;id={$credit['creditid']}'>{$forums->lang['edit']}</a> | <a href='credit.php?{$forums->sessionurl}do=delete&amp;id={$credit['creditid']}'>{$forums->lang['delete']}</a></center>",
									                    )      );
			}
		} else {
			$forums->admin->print_cells_single_row( "<strong>{$forums->lang['no_any_credits']}</strong>", 'center' );
		}

		$forums->admin->print_form_submit($forums->lang['add_new_credit']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();

		$forums->admin->print_cp_footer();	
	}

	function credit_form($type = 'add')
	{
		global $forums, $DB, $_INPUT;

		if ($type=="edit") {
			$_INPUT['id'] = intval($_INPUT['id']);
			if (!$_INPUT['id']) {
				$forums->admin->print_cp_error($forums->lang['noids']);
			}
			$credit = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."credit WHERE creditid = ".$_INPUT['id']."");
			if (!$credit['creditid']) {
				$forums->admin->print_cp_error($forums->lang['noids']);
			}
			$pagetitle  = $forums->lang['edit_credit'];
			$button  = $forums->lang['edit_credit'];
		} else {
			$pagetitle  = $forums->lang['add_credit'];
			$button  = $forums->lang['add_credit'];
		}
		
		$detail = $forums->lang['creditlistdesc'];
		$forums->admin->nav[] = array( 'credit.php' , $forums->lang['creditlist'] );

		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do' , 'doedit' ), 2 => array( 'id', $credit['creditid'] ) ) );
		$forums->admin->columns[] = array( "&nbsp;", "40%" );
		$forums->admin->columns[] = array( "&nbsp;", "60%" );
		$forums->admin->print_table_start( $pagetitle );



		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_name']}</strong>", $forums->admin->print_input_row( 'name', $_INPUT['name'] ? $_INPUT['name'] : $credit['name'] ) ) );

		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_tag_name']}</strong><div class='description'>{$forums->lang['credit_tag_name_desc']}</div>", $credit['tag_name'] ? $credit['tag_name'] : $forums->admin->print_input_row( 'tag_name', $_INPUT['tag_name'] ? $_INPUT['tag_name'] : "" ) ) );

		$newthread = isset($_INPUT['newthread']) ? $_INPUT['newthread'] : $credit['newthread'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_newthread']}</strong>", $forums->admin->print_input_row( 'newthread', $newthread ) ) );

		$newreply = isset($_INPUT['newreply']) ? $_INPUT['newreply'] : $credit['newreply'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_newreply']}</strong>", $forums->admin->print_input_row( 'newreply', $newreply ) ) );

		$quintessence = isset($_INPUT['quintessence']) ? $_INPUT['quintessence'] : $credit['quintessence'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_quintessence']}</strong>", $forums->admin->print_input_row( 'quintessence', $quintessence ) ) );

		$award = isset($_INPUT['award']) ? $_INPUT['award'] : $credit['award'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_award']}</strong>", $forums->admin->print_input_row( 'award', $award ) ) );

		$downattach = isset($_INPUT['downattach']) ? $_INPUT['downattach'] : $credit['downattach'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_downattach']}</strong>", $forums->admin->print_input_row( 'downattach', $downattach ) ) );

		$sendpm = isset($_INPUT['sendpm']) ? $_INPUT['sendpm'] : $credit['sendpm'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_sendpm']}</strong>", $forums->admin->print_input_row( 'sendpm', $sendpm ) ) );

		$search = isset($_INPUT['search']) ? $_INPUT['search'] : $credit['search'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_search']}</strong>", $forums->admin->print_input_row( 'search', $search ) ) );

		$c_limit = isset($_INPUT['c_limit']) ? $_INPUT['c_limit'] : $credit['c_limit'];
		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_limit']}</strong>", $forums->admin->print_input_row( 'c_limit', $c_limit ) ) );

		$forums->admin->print_cells_row( array( "<strong>{$forums->lang['credit_show']}</strong>", $forums->admin->print_yes_no_row( 'used', $_INPUT['used'] ? $_INPUT['used'] : $credit['used'] ) ) );

		$forums->admin->print_form_submit($button);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();

		$forums->admin->print_table_start( $forums->lang['credit_explain'] );

		$explain[] = "<ul><li>{$forums->lang['credit_explain_1']}</li></ul>";

		$explain[] = "<ul><li>{$forums->lang['credit_explain_2']}</li></ul>";

		$explain[] = "<ul><li>{$forums->lang['credit_explain_3']}</li></ul>";

		$explain[] = "<ul><li>{$forums->lang['credit_explain_4']}</li></ul>";

		$show_explain = "\r\n";
		$show_explain .= implode("\r\n", $explain);
		$show_explain .= "\r\n";

		$forums->admin->print_cells_single_row(  $show_explain, "left" );

		$forums->admin->print_table_footer();

		$forums->admin->print_cp_footer();	
	}

	function doedit()
	{
		global $forums, $DB, $_INPUT;

		$_INPUT['id'] = intval($_INPUT['id']);
		$_INPUT['name'] = trim($_INPUT['name']);
		$_INPUT['tag_name'] = strtolower(trim($_INPUT['tag_name']));
		$_INPUT['newthread'] = intval($_INPUT['newthread']);
		$_INPUT['newreply'] = intval($_INPUT['newreply']);
		$_INPUT['quintessence'] = intval($_INPUT['quintessence']);
		$_INPUT['award'] = intval($_INPUT['award']);
		$_INPUT['addattach'] = intval($_INPUT['addattach']);
		$_INPUT['downattach'] = intval($_INPUT['downattach']);
		$_INPUT['sendpm'] = intval($_INPUT['sendpm']);
		$_INPUT['search'] = intval($_INPUT['search']);
		$_INPUT['c_limit'] = intval($_INPUT['c_limit']);
		$_INPUT['used'] = intval($_INPUT['used']);

		if (!$_INPUT['name']) {
			$forums->admin->print_cp_error( $forums->lang['require_credit_name'] );
		}

		if ($_INPUT['id']) {
			$credit = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."credit WHERE creditid = ".$_INPUT['id']."");
			if (!$credit['creditid']) {
				$forums->admin->print_cp_error($forums->lang['noids']);
			}
		} else {
			if (!preg_match( "#^(\w+)$#i", $_INPUT['tag_name'] )) {
				$forums->admin->print_cp_error( $forums->lang['only_letter_num'] );
			}
			$keys = $DB->query("DESCRIBE ".TABLE_PREFIX."user");
			while ( $r = $DB->fetch_array($keys) ) {
				$key[$r['Field']] = $r['Field'];
			}
			$keys = $DB->query("DESCRIBE ".TABLE_PREFIX."userexpand");
			while ( $r = $DB->fetch_array($keys) ) {
				$key[$r['Field']] = $r['Field'];
			}
			if ($key[$_INPUT['tag_name']]) {
				$forums->admin->print_cp_error( $forums->lang['key_already_used'] );
			}
		}

		$c = array(
							'name' => $_INPUT['name'],
							'newthread' => $_INPUT['newthread'],
							'newreply' => $_INPUT['newreply'],
							'award' => $_INPUT['award'],
							'quintessence' => $_INPUT['quintessence'],
							'downattach' => $_INPUT['downattach'],
							'sendpm' => $_INPUT['sendpm'],
							'search' => $_INPUT['search'],
							'c_limit' => $_INPUT['c_limit'],
							'used' => $_INPUT['used'],
						);

		if ($credit['creditid']) {
			$forums->func->fetch_query_sql( $c, "credit", "creditid=".$credit['creditid']);
			$this->cache->credit_recache();
			$forums->admin->redirect("credit.php", $forums->lang['credit_edited'], $forums->lang['credit_edited'] );
		} else {
			$c['tag_name'] = $_INPUT['tag_name'];
			$forums->func->fetch_query_sql( $c, "credit");
			$DB->query_unbuffered("ALTER TABLE ".TABLE_PREFIX."userexpand ADD ".$_INPUT['tag_name']." smallint(5) NOT NULL default '0'");
			$this->cache->credit_recache();
			$forums->admin->redirect("credit.php", $forums->lang['credit_added'], $forums->lang['credit_added'] );
		}		
	}

	function delete_credit()
	{
		global $forums, $DB, $_INPUT;

		$forums->admin->nav[] = array( 'credit.php' , $forums->lang['creditlist'] );

		$_INPUT['id'] = intval($_INPUT['id']);
		if (!$_INPUT['id']) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		$credit = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."credit WHERE creditid = ".$_INPUT['id']."");
		if (!$credit['creditid']) {
			$forums->admin->print_cp_error($forums->lang['noids']);
		}
		if ($_INPUT['update']) {
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."credit WHERE creditid = '".$credit['creditid']."'");
			$DB->query_unbuffered("ALTER TABLE ".TABLE_PREFIX."userexpand DROP ".$credit['tag_name']."");

			$this->cache->credit_recache();
			$forums->admin->redirect("credit.php", $forums->lang['credit_deleted'], $forums->lang['credit_deleted'] );
		} else {
			$pagetitle  = $forums->lang['credit_confirm_deleted'];
			$detail = $forums->lang['credit_confirm_deleted'];

			$forums->admin->print_cp_header($pagetitle, $detail);
			$forums->admin->print_form_header( array( 1 => array( 'do' , 'delete' ), 2 => array( 'id', $credit['creditid'] ), 3 => array( 'update', 1 ) ) );
			$forums->admin->print_table_start( $pagetitle );

			$forums->admin->print_cells_single_row(  $forums->lang['confirm_deleted_desc'], "center" );

			$forums->admin->print_form_submit($forums->lang['confirm_deleted']);
			$forums->admin->print_table_footer();
			$forums->admin->print_form_end();
			$forums->admin->print_cp_footer();	
			
		}
	}
}

$output = new credit();
$output->show();
?>