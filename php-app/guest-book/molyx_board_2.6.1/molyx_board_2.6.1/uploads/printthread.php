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
define('THIS_SCRIPT', 'printthread');

require_once('./global.php');

class printthread {

    function show()
    {
        global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'printthread' );
		$forums->forum->forums_init();
        require ROOT_PATH."includes/functions_codeparse.php";
        $parser = new functions_codeparse();
        $_INPUT['t'] = intval($_INPUT['t']);
        $_INPUT['f'] = intval($_INPUT['f']);
        if ( ! $_INPUT['t'] OR ! $_INPUT['f'] ) {
            $forums->func->standard_error("erroraddress");
        }
		$thread = $DB->query_first("SELECT tid, title, forumid, postusername, sticky FROM ".TABLE_PREFIX."thread WHERE tid='".$_INPUT['t']."'");
		$forum = $forums->forum->single_forum( $thread['forumid'] );
        if ( ! $forum['id'] OR ! $thread['tid'] ) {
        	$forums->func->standard_error("erroraddress");
        }
        if ( !$bbuserinfo['canviewothers'] ) {
        	$forums->func->standard_error("cannotviewthread");
        }
        $forums->forum->check_permissions( $forum['id'], 1 );
		$maxposts = 300;
		if ( $this->forum['moderatepost']) {
			$moderate = ' AND moderate=0';
			if ( $this->can_moderate($this->thread['forumid']) ) {
				$moderate = '';
				if ( $_INPUT['modfilter'] == 'invisiblepost' ) {
					$moderate = ' AND moderate=1';
				}
			}
		} else {
			$moderate = '';
		}
		$posts = $DB->query( "SELECT * FROM ".TABLE_PREFIX."post WHERE threadid='".$thread['tid']."'{$moderate} ORDER BY pid LIMIT 0, ".$maxposts);
		$the_post = array();
		$user_ids = array();
		$user_array   = array();
		$cached_users = array();
		while ( $post = $DB->fetch_array($posts) ) {
			$thispost[] = $post;
			if ($post['userid'] != 0 AND !in_array( $post['userid'], $user_ids)) {
				$user_ids[] = $post['userid'];
			}
		}
		if (count($user_ids)) {
			$users = $DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE id IN (".implode(", ", $user_ids).")");
			while ( $user = $DB->fetch_array( $users ) ) {
				if ($user['id'] AND $user['name']) {
					if (isset($user_array[ $user['id'] ])) {
						continue;
					} else {
						$user_array[ $user['id'] ] = $user;
					}
				}
			}
		}
		$forums->func->check_cache('usergroup');
		foreach ($thispost AS $row) {		
			$poster = array();
			if ($row['userid'] != 0) {
				if ( isset($cached_users[ $row['userid'] ]) ) {
					$poster = $cached_users[ $row['userid'] ];
					$row['name_css'] = 'normalname';
				} else {
					if ($user_array[ $row['userid'] ]) {
						$row['name_css'] = 'normalname';
						$poster = $user_array[ $row['userid'] ];
						$cached_users[ $row['userid'] ] = $poster;
					} else {
						$poster = $forums->func->set_up_guest( $row['userid'] );
						$row['name_css'] = 'unreg';
					}
				}
			} else {
				$poster = $forums->func->set_up_guest( $row['name'] );
				$row['name_css'] = 'unreg';
			}
			$row['name'] = $poster['name'];
			$row['post_css'] = $td_col_count % 2 ? 'row1' : 'row2';
			++$td_col_count;
			$row['dateline']   = $forums->func->get_date( $row['dateline'], 2 );
			if ($row['hidepost']) {
                $row['pagetext'] = $forums->lang['_posthidden'];
            } else {
			    $row['pagetext'] = preg_replace( array( "#<!--Flash (.+?)-->.+?<!--End Flash-->#e", "#<a href=[\"'](http|news|https|ftp|ed2k|rtsp|mms)://(\S+?)['\"].+?".">(.+?)</a>#"), array( "[FLASH]" , "\\1://\\2"),  $row['pagetext'] );
                $parser->show_html  = ( $forum['allowhtml'] AND $forums->cache['usergroup'][ $poster['usergroupid'] ]['canposthtml'] ) ? 1 : 0;
                $row['pagetext'] = $parser->convert_text( $row['pagetext'] );
            }
			$data[] = $row;
		}
		include $forums->func->load_template('printthread');
		exit;
	}
}

$output = new printthread();
$output->show();

?>