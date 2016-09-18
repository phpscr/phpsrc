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

class rebuild {

	function show()
	{
		global $forums, $_INPUT, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditothers']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$forums->admin->nav[] = array( 'rebuild.php' ,$forums->lang['rebulidcounter'] );
		switch($_INPUT['do']) {
			case 'docount':
				$this->docount();
				break;
			case 'doresyncforums':
				$this->recount_forums();
				break;
			case 'doresyncthread':
				$this->recount_thread();
				break;
			case 'dopost':
				$this->rebuild_post();
				break;
			case 'dopostnames':
				$this->rebuild_post_names();
				break;
			case 'dopostcounts':
				$this->recount_post();
				break;
			case 'dothumbnails':
				$this->rebuild_thumbnails();
				break;
			case 'doattachdata':
				$this->rebuild_attachdata();
			default:
				$this->rebuild_start();
				break;
		}
	}

	function rebuild_attachdata()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		require_once( ROOT_PATH.'includes/functions_upload.php' );
		$upload = new functions_upload();
		$done   = 0;
		$start  = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$end    =  $_INPUT['percycle'] ? intval( $_INPUT['percycle'] ) : 100;
		$end   += $start;
		$output = array();
		$tmp = $DB->query_first( "SELECT attachmentid FROM ".TABLE_PREFIX."attachment WHERE attachmentid > ".$end."" );
		$max = intval( $tmp['attachmentid'] );
		$ra = $DB->query( "SELECT * FROM ".TABLE_PREFIX."attachment WHERE attachmentid >= ".$start." AND attachmentid < ".$end." ORDER BY attachmentid ASC" );
		while( $r = $DB->fetch_array($ra) ) {
			$update = array();
			$update['extension'] = strtolower( str_replace( ".", "", substr( $r['filename'], strrpos( $r['filename'], '.' ) ) ) );
			if ( $r['location'] ) {
				if ( file_exists( $bboptions['uploadfolder'].'/'.$r['attachpath'].'/'.$r['location'] ) ) {
					$update['filesize'] = @filesize( $bboptions['uploadfolder'].'/'.$r['attachpath'].'/'.$r['location'] );
				}
			}
			if ( count( $update ) ) {
				$forums->func->fetch_query_sql( $update, 'attachment', 'attachmentid='.$r['attachmentid'] );
			}
			$done++;
		}
		if ( ! $done AND ! $max ) {
			$text = "<strong>".$forums->lang['rebulidfinish']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php";
			$time = 2;
		} else {
			$forums->lang['rebulidattachs'] = sprintf( $forums->lang['rebulidattachs'], $end );
			$text = "<strong>".$forums->lang['rebulidattachs']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php?do=".$_INPUT['do'].'&amp;percycle='.$_INPUT['percycle'].'&amp;pp='.$end;
			$time = 3;
		}
		$forums->admin->redirect( $url, $forums->lang['rebulidattachmant'], $text, 0, $time );
	}

	function rebuild_thumbnails()
	{
		global $forums, $DB, $_INPUT, $bboptions;
		$done = 0;
		$start  = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$end = $_INPUT['percycle'] ? intval($_INPUT['percycle']) : 100;
		$end   += $start;
		$output = array();
		$tmp = $DB->query_first( "SELECT attachmentid FROM ".TABLE_PREFIX."attachment WHERE attachmentid > ".$end."" );
		$max = intval( $tmp['attachmentid'] );
		require_once( ROOT_PATH.'includes/functions_image.php' );
		$image = new functions_image();
		$rt = $DB->query( "SELECT * FROM ".TABLE_PREFIX."attachment WHERE attachmentid >= ".$start." AND attachmentid < ".$end." ORDER BY attachmentid ASC" );
		while( $r = $DB->fetch_array($rt) ) {
			if ( $r['image'] ) {
				if ( $r['thumblocation'] ) {
					if ( file_exists( $bboptions['uploadfolder'].'/'.$r['attachpath'].'/'.$r['thumblocation'] ) AND $r['thumblocation'] != $r['location'] ) {
						if ( ! @unlink( $bboptions['uploadfolder'].'/'.$r['attachpath'].'/'.$r['thumblocation'] ) ) {
							$output[] = $forums->lang['cannotmove'].": ".$r['thumblocation'];
							continue;
						}
					}
				}
				if ( !$bboptions['viewattachedthumbs'] ) {
					$attach_data['thumbwidth']    = '';
					$attach_data['thumbheight']   = '';
					$attach_data['thumblocation'] = '';
				} else {
					$attach_data = array();
					$thumb_data = array();
					$subpath = SAFE_MODE ? "" : implode('/', preg_split('//', $r['userid'],  -1, PREG_SPLIT_NO_EMPTY));
					$image->filepath = $bboptions['uploadfolder']. '/' . $subpath;
					$image->filename   = $r['location'];
					$image->thumbswidth  = $bboptions['thumbswidth'];
					$image->thumbsheight = $bboptions['thumbsheight'];
					$image->thumb_filename = '';
					$thumb_data = $image->generate_thumbnail();
					$attach_data['thumbwidth']    = $thumb_data['thumbwidth'];
					$attach_data['thumbheight']   = $thumb_data['thumbheight'];
					$attach_data['thumblocation'] = $thumb_data['thumblocation'];
				}
				if ( count( $attach_data ) ) {
					$forums->func->fetch_query_sql( $attach_data, 'attachment', 'attachmentid='.$r['attachmentid'] );
					$output[] = $forums->lang['rebulid'].": ".$r['location'];
				}
			}
			$done++;
		}
		if ( ! $done AND ! $max ) {
			$text = "<strong>".$forums->lang['rebulidfinish']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php";
			$time = 2;
		} else {
			$forums->lang['rebulidthumbs'] = sprintf( $forums->lang['rebulidthumbs'], $end );
			$text = "<strong>".$forums->lang['rebulidthumbs']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php?do=".$_INPUT['do'].'&amp;percycle='.$_INPUT['percycle'].'&amp;pp='.$end;
			$time = 3;
		}
		$forums->admin->redirect( $url, $forums->lang['rebulidthumb'], $text, 0, $time );
	}

	function recount_post()
	{
		global $forums, $DB, $_INPUT;
		$done   = 0;
		$start  = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$end    = $_INPUT['percycle'] ? intval( $_INPUT['percycle'] ) : 100;
		$end   += $start;
		$output = array();
		$tmp = $DB->query_first( "SELECT id FROM ".TABLE_PREFIX."user WHERE id > ".$end."" );
		$max = intval( $tmp['id'] );
		$usercount = $DB->query( "SELECT id, name FROM ".TABLE_PREFIX."user WHERE id >= ".$start." AND id < ".$end." ORDER BY id ASC" );
		while( $r = $DB->fetch_array($usercount) ) {
			$count = $DB->query_first( "SELECT count(*) as count FROM ".TABLE_PREFIX."post WHERE userid=".$r['id']." AND moderate != 1" );
			$new_post_count = intval( $count['count'] );
			$forums->func->fetch_query_sql( array( 'posts' => $new_post_count ), 'user', 'id='.$r['id'] );
			$done++;
		}
		if ( ! $done AND ! $max ) {
			$text = "<strong>".$forums->lang['rebulidfinish']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php";
			$time = 2;
		} else {
			$forums->lang['rebulidposts'] = sprintf( $forums->lang['rebulidposts'], $end );
			$text = "<strong>".$forums->lang['rebulidposts']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php?do=".$_INPUT['do'].'&amp;percycle='.$_INPUT['percycle'].'&amp;pp='.$end;
			$time = 3;
		}
		$forums->admin->redirect( $url, $forums->lang['rebulidpost'], $text, 0, $time );
	}

	function rebuild_post_names()
	{
		global $forums, $DB, $_INPUT;
		$done   = 0;
		$start  = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$end    = $_INPUT['percycle'] ? intval( $_INPUT['percycle'] ) : 100;
		$end   += $start;
		$output = array();
		$tmp = $DB->query_first( "SELECT id FROM ".TABLE_PREFIX."user WHERE id > ".$end."" );
		$max = intval( $tmp['id'] );
		$user = $DB->query( "SELECT id, name FROM ".TABLE_PREFIX."user WHERE id >= ".$start." AND id < ".$end." ORDER BY id ASC" );
		while( $r = $DB->fetch_array( $user ) ) {
			$forums->func->fetch_query_sql( array( 'contactname' => $r['name'] ), 'pmuserlist', "contactid="    .$r['id'] );
			$forums->func->fetch_query_sql( array( 'username' => $r['name'] ), 'moderatorlog', "userid="     .$r['id'] );
			$forums->func->fetch_query_sql( array( 'username' => $r['name'] ), 'moderator', "userid="     .$r['id'] );
			$forums->func->fetch_query_sql( array( 'username' => $r['name'] ), 'post', "userid="     .$r['id'] );
			$forums->func->fetch_query_sql( array( 'postusername' => $r['name'] ), 'thread', "postuserid="    .$r['id'] );
			$done++;
		}
		if ( ! $done AND ! $max ) {
			$text = "<strong>".$forums->lang['rebulidfinish']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php";
			$time = 2;
		} else {
			$forums->lang['rebulidnames'] = sprintf( $forums->lang['rebulidnames'], $end );
			$text = "<strong>".$forums->lang['rebulidnames']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php?do=".$_INPUT['do'].'&amp;percycle='.$_INPUT['percycle'].'&amp;pp='.$end;
			$time = 3;
		}
		$forums->admin->redirect( $url, $forums->lang['rebulidusername'], $text, 0, $time );
	}

	function rebuild_post()
	{
		global $forums, $DB, $_INPUT;
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		require_once( ROOT_PATH.'includes/functions_codeparse.php' );
		$parser = new functions_codeparse();
		$done   = 0;
		$start  = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$end    = $_INPUT['percycle'] ? intval( $_INPUT['percycle'] ) : 100;
		$end   += $start;
		$output = array();
		$tmp = $DB->query_first( "SELECT pid FROM ".TABLE_PREFIX."post WHERE pid > ".$end." LIMIT 0, 1" );
		$max = intval( $tmp['pid'] );
		$forums->func->check_cache('forum');
		$DB->query( "SELECT p.*, t.forumid FROM ".TABLE_PREFIX."post p, ".TABLE_PREFIX."thread t WHERE pid >= ".$start." AND pid < ".$end." AND p.threadid=t.tid ORDER BY pid ASC" );
		while( $r = $DB->fetch_array() ) {
			$parser->quote_open   = 0;
			$parser->quote_closed = 0;
			$parser->quote_error  = 0;
			$parser->error        = '';
			$rawpost = $parser->unconvert( $r['post'] );
			$newpost = $parser->convert( array( 'text'      => $rawpost,
			'allowsmilies'   => $r['allowsmile'],
			'allowcode'      => $forums->cache['forum'][$r['forumid']]['allowbbcode'],
			)       );
			if ( $newpost ) {
				$forums->func->fetch_query_sql( array( 'post' => $newpost ), 'post', 'pid='.$r['pid'] );
			}
			$done++;
		}
		if ( ! $done AND ! $max ) {
			$text = "<strong>".$forums->lang['rebulidfinish']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php";
			$time = 2;
		} else {
			$forums->lang['rebulidcontents'] = sprintf( $forums->lang['rebulidcontents'], $end );
			$text = "<strong>".$forums->lang['rebulidcontents']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php?do=".$_INPUT['do'].'&amp;percycle='.$_INPUT['percycle'].'&amp;pp='.$end;
			$time = 3;
		}
		$forums->admin->redirect( $url, $forums->lang['rebulidcontent'], $text, 0, $time );
	}

	function recount_thread()
	{
		global $forums, $DB, $_INPUT;
		require_once( ROOT_PATH.'includes/functions_moderate.php' );
		$modfunc = new modfunctions();
		$done   = 0;
		$start  = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$end    = $_INPUT['percycle'] ? intval( $_INPUT['percycle'] ) : 100;
		$end   += $start;
		$output = array();
		$tmp = $DB->query_first( "SELECT count(*) as count FROM ".TABLE_PREFIX."thread WHERE tid > ".$end."" );
		$max = intval( $tmp['count'] );
		$thread = $DB->query( "SELECT * FROM ".TABLE_PREFIX."thread WHERE tid >= ".$start." AND tid < ".$end." ORDER BY tid ASC" );
		while( $r = $DB->fetch_array( $thread ) ) {
			$modfunc->rebuild_thread($r['tid'], 0);
			if ( $_INPUT['percycle'] <= 200 ) {
				$output[] = $forums->lang['rebulidthreadtitle']." - ".$r['title'];
			}
			$done++;
		}
		if ( ! $done AND ! $max ) {
			$text = "<strong>".$forums->lang['rebulidfinish']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php";
			$time = 2;
		} else {
			$forums->lang['rebulidthreads'] = sprintf( $forums->lang['rebulidthreads'], $end );
			$text = "<strong>".$forums->lang['rebulidthreads']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php?do=".$_INPUT['do'].'&amp;percycle='.$_INPUT['percycle'].'&amp;pp='.$end;
			$time = 3;
		}
		$forums->admin->redirect( $url, $forums->lang['rebulidthread'], $text, 0, $time );
	}

	function recount_forums()
	{
		global $forums, $DB, $_INPUT;
		require_once( ROOT_PATH.'includes/functions_moderate.php' );
		$modfunc = new modfunctions();
		$done = 0;
		$start = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$end = $_INPUT['percycle'] ? intval( $_INPUT['percycle'] ) : 100;
		$end += $start;
		$output = array();
		$tmp = $DB->query_first("SELECT count(*) as count FROM ".TABLE_PREFIX."forum WHERE id > ".$end);
		$max = intval($tmp['count']);
		$forumid = $DB->query("SELECT * FROM ".TABLE_PREFIX."forum WHERE id >= ".$start." AND id < ".$end." ORDER BY id ASC");
		while($r = $DB->fetch_array($forumid)) {
			$modfunc->forum_recount($r['id']);
			$output[] = $forums->lang['rebulidforumtitle']." - ".$r['name'];
			$done++;
		}
		if (!$done AND !$max) {
			$text = "<strong>".$forums->lang['rebulidfinish']."</strong><br />".implode( "<br />", $output );
			$url  = "rebuild.php";
			$time = 2;
		} else {
			$forums->lang['rebulidforums'] = sprintf( $forums->lang['rebulidforums'], $end );
			$text = "<strong>".$forums->lang['rebulidforums']."</strong><br />".implode( "<br />", $output );
			$url = "rebuild.php?do=".$_INPUT['do'].'&amp;percycle='.$_INPUT['percycle'].'&amp;pp='.$end;
			$time = 3;
		}
		$forums->admin->redirect($url, $forums->lang['rebulidforum'], $text, 0, $time );
	}

	function docount()
	{
		global $forums, $DB, $_INPUT;
		if ( (! $_INPUT['post']) AND (! $_INPUT['users'] ) AND (! $_INPUT['lastreg'] ) ) {
			$forums->admin->print_cp_error($forums->lang['norequirecounter']);
		}
		$forums->func->check_cache('stats');
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );
		$this->cache = new adminfunctions_cache();
		$this->cache->stats_recache(0);
		$forums->main_msg = $forums->lang['statscounterdone'];
		$forums->admin->redirect("rebuild.php", $forums->lang['rebulidstats'], $forums->lang['statscounterdone'] );
	}

	function rebuild_start()
	{
		global $forums, $DB;
		$pagetitle  = $forums->lang['rebulidcounter'];
		$detail = $forums->lang['rebulidcounterdesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'docount' ) ),'docountform' );
		$forums->admin->columns[] = array( $forums->lang['stats'], "70%" );
		$forums->admin->columns[] = array( $forums->lang['option'], "30%" );
		$forums->admin->print_table_start( $forums->lang['rebulidstats'] );
		$forums->admin->print_cells_row( array( $forums->lang['rebulidposttotals'],
		$forums->admin->print_input_select_row( 'post', array( 0 => array( 1, $forums->lang['yes'] ), 1 => array( 0, $forums->lang['no'] ) ) )
		)      );
		$forums->admin->print_cells_row( array( $forums->lang['rebulidusertotals'],
		$forums->admin->print_input_select_row( 'users', array( 0 => array( 1, $forums->lang['yes'] ), 1 => array( 0, $forums->lang['no'] ) ) )
		)      );
		$forums->admin->print_cells_row( array( $forums->lang['rebulidnewregister'],
		$forums->admin->print_input_select_row( 'lastreg', array( 0 => array( 1, $forums->lang['yes'] ), 1 => array( 0, $forums->lang['no'] ) ) )
		)      );
		$forums->admin->print_cells_row( array( $forums->lang['resetmaxonline'],
		$forums->admin->print_input_select_row( 'online', array( 0 => array( 0, $forums->lang['no'] ), 1 => array( 1, $forums->lang['yes'] ) ) )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidstats']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'doresyncforums' ) ),'resyncforums' );
		$forums->admin->columns[] = array( "&nbsp;", "60%" );
		$forums->admin->columns[] = array( "&nbsp;", "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidforum'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['rebulidforum']."</strong><div class='description'>".$forums->lang['rebulidforumdesc']."</div>",
		$forums->lang['percycle']."&nbsp;".$forums->admin->print_input_row( 'percycle', '50', '', '', 5 )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidforum']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'doresyncthread' ) ),'resyncthread' );
		$forums->admin->columns[] = array( "&nbsp;"    , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"    , "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidthread'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['rebulidthread']."</strong><div class='description'>".$forums->lang['rebulidthreaddesc']."</div>",
		$forums->lang['percycle']."&nbsp;".$forums->admin->print_input_row( 'percycle', '500', '', '', 5 )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidthread']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'dopost' ) ),'postform' );
		$forums->admin->columns[] = array( "&nbsp;"    , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"    , "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidcontent'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['rebulidcontent']."</strong><div class='description'>".$forums->lang['rebulidcontentdesc']."</div>",
		$forums->lang['percycle']."&nbsp;".$forums->admin->print_input_row( 'percycle', '500', '', '', 5 )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidcontent']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'dopostnames' ) ),'postnames' );
		$forums->admin->columns[] = array( "&nbsp;"    , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"    , "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidusername'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['rebulidusername']."</strong><div class='description'>".$forums->lang['rebulidusernamedesc']."</div>",
		$forums->lang['percycle']."&nbsp;".$forums->admin->print_input_row( 'percycle', '500', '', '', 5 )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidusername']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'dopostcounts' ) ),'postcounts' );
		$forums->admin->columns[] = array( "&nbsp;"    , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"    , "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidpost'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['rebulidpost']."</strong><div class='description'>".$forums->lang['rebulidpostdesc']."</div>",
		$forums->lang['percycle']."&nbsp;".$forums->admin->print_input_row( 'percycle', '500', '', '', 5 )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidpost']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'dothumbnails' ) ),'thumbnails' );
		$forums->admin->columns[] = array( "&nbsp;"    , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"    , "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidthumb'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['rebulidthumb']."</strong><div class='description'>".$forums->lang['rebulidthumbdesc']."</div>",
		$forums->lang['percycle']."&nbsp;".$forums->admin->print_input_row( 'percycle', '20', '', '', 5 )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidthumb']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header( array( 1 => array( 'do'  , 'doattachdata' ) ),'attachdata' );
		$forums->admin->columns[] = array( "&nbsp;"    , "60%" );
		$forums->admin->columns[] = array( "&nbsp;"    , "40%" );
		$forums->admin->print_table_start( $forums->lang['rebulidattachmant'] );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['rebulidattachmant']."</strong><div class='description'>".$forums->lang['rebulidattachdesc']."</div>",
		$forums->lang['percycle']."&nbsp;".$forums->admin->print_input_row( 'percycle', '50', '', '', 5 )
		)      );
		$forums->admin->print_form_submit($forums->lang['rebulidattachmant']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}
}

$output = new rebuild();
$output->show();

?>