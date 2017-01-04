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
define('THIS_SCRIPT', 'announcement');
require_once('./global.php');

class announce {

	var $ids = array();

    function show()
    {
        global $_INPUT, $DB, $bbuserinfo, $bboptions, $forums;
		$forums->lang = $forums->func->load_lang($forums->lang, 'showthread' );
		$forums->forum->forums_init();
		require ROOT_PATH."includes/functions_showthread.php";
		$show = new functions_showthread();
        $_INPUT['id'] = intval($_INPUT['id']);
		$start = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$where = '';
		$pages = '';
        if ( $_INPUT['id'] ) {
			$where = " AND a.id=".$_INPUT['id'];
        } else {
			$acount = $DB->query_first("SELECT COUNT(*) AS count FROM ".TABLE_PREFIX."announcement a WHERE active = 1 AND (startdate=0 OR startdate < ".TIMENOW.") AND (enddate=0 OR enddate > ".TIMENOW.")".$where."");
			$pages = $forums->func->build_pagelinks(array(
				'totalpages' => $acount['count'],
				'perpage' => 5,
				'curpage' => $start,
				'pagelink' => "announcement.php?".$forums->sessionurl,)
			);
		}
		$DB->query( "SELECT a.pagetext, a.forumid, a.userid, a.allowhtml, a.views, a.startdate, a.enddate, a.id AS announceid, a.title AS announcetitle, u.* FROM ".TABLE_PREFIX."announcement a LEFT JOIN ".TABLE_PREFIX."user u on (a.userid=u.id)  WHERE active = 1 AND (startdate=0 OR startdate < ".TIMENOW.") AND (enddate=0 OR enddate > ".TIMENOW.")".$where." ORDER BY enddate DESC LIMIT ".$start.", 5" );
		if ($DB->num_rows()) {
			while ($announce = $DB->fetch_array() ) {
				$announce['title'] = strip_tags($announce['title']);
				$pass = FALSE;
				if ( $announce['forumid'] == -1 ) {
					$pass = TRUE;
				} else {
					$tmp = explode( ",", $announce['forumid'] );
					if ( ! is_array( $tmp ) and ! ( count( $tmp ) ) ) {
						$pass = FALSE;
					} else {
						foreach( $tmp as $id ) {
							if ( $forums->forum->foruminfo[ $id ]['id'] ) {
								$pass = TRUE;
								break;
							}
						}
					}
				}
				$announce['pagetext'] = preg_replace( "/<!--emule1-->(.+?)<!--emule2-->/ie", "\$show->paste_emule('\\1')", $announce['pagetext'] );
				if ( $pass != 1 ) {
					$forums->func->standard_error("cannotviewannounce");
				}
				$announce['user'] = $forums->func->fetch_user( $announce );
				if ( $announce['startdate'] and $announce['enddate'] ) {
					$announce['dateline'] = " ( ".$forums->lang['from']." ".$forums->func->get_time($announce['startdate'], 'Y-m-d')." ".$forums->lang['to']." ".gmdate( 'Y-m-d', $announce['enddate'] )." ) ";
				} else if ( $announce['startdate'] and ! $announce['enddate'] ) {
					$announce['dateline'] = " ( ".$forums->lang['from']." ".$forums->func->get_time( $announce['startdate'], 'Y-m-d')." ) ";
				} else {
					$announce['dateline'] = '';
				}
				$this->ids[] = $announce['announceid'];
				$announcement[] = $announce;
			}
			if (count($this->ids)) {
				$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."announcement SET views=views+1 WHERE id  IN (".implode(", ", $this->ids).")" );
			}
		} else {
			$forums->func->standard_error("cannotviewannounce");
		}
		$pagetitle = $forums->lang['announcement']." - ".$bboptions['bbtitle'];
		$nav = array( $forums->lang['announcement']." [<a href='announcement.php?".$forums->sessionurl."'>".$forums->lang['allannounce']."</a>]" );
		include $forums->func->load_template('announcement');
		exit;
    }
}

$output = new announce();
$output->show();

?>