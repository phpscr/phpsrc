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
class adminfunctions_forum
{
	var $type     = "";
	var $show_all = 0;
	var $styles    = array();
	var $need_desc = array();

	function fetch_forum_parentlist($forumid)
	{
		global $DB;
		if ($forumid == -1) {
			return '-1';
		}
		$foruminfo = $DB->query_first("SELECT parentid FROM " . TABLE_PREFIX . "forum WHERE id = ".$forumid."");
		$forumarray = $forumid;
		if ($foruminfo['parentid'] != 0) {
			$forumarray .= ',' . $this->fetch_forum_parent_list($foruminfo['parentid']);
		}
		if (substr($forumarray, -2) != -1) {
			$forumarray .= '-1';
		}
		return $forumarray;
	}

	function fetch_forum_parent_list($forumid)
	{
		global $DB, $forums;
		static $forumarraycache;
		$forums->func->check_cache('forum');
		if (isset($forums->cache['forum']["$forumid"]['parentlist'])) {
			return $forums->cache['forum']["$forumid"]['parentlist'];
		} else {
			if (isset($forumarraycache["$forumid"])) {
				return $forumarraycache["$forumid"];
			} else if (isset($forums->cache['forum']["$forumid"]['parentlist'])) {
				return $forums->cache['forum']["$forumid"]['parentlist'];
			} else {
				$foruminfo = $DB->query_first("SELECT parentlist FROM " . TABLE_PREFIX . "forum WHERE id = $forumid");
				$forumarraycache["$forumid"] = $foruminfo['parentlist'];
				return $foruminfo['parentlist'];
			}
		}
	}

	function build_forum_parentlists($forumid = -1)
	{
		global $DB;
		$forumlist = $DB->query("
			SELECT id, (CHAR_LENGTH(parentlist) - CHAR_LENGTH(REPLACE(parentlist, ',', ''))) AS parents
			FROM " . TABLE_PREFIX . "forum
			WHERE FIND_IN_SET('$forumid', parentlist)
			ORDER BY parents ASC
		");
		while($forum = $DB->fetch_array($forumlist)) {
			$parentlist = $this->fetch_forum_parentlist($forum['id']);
			$DB->query_unbuffered( "UPDATE " . TABLE_PREFIX . "forum SET parentlist = '" . addslashes($parentlist) . "' WHERE id = $forum[id]" );
		}
	}

	function build_forum_child_lists($forumid = -1)
	{
		global $DB;
		$forumlist = $DB->query("SELECT id FROM " . TABLE_PREFIX . "forum WHERE FIND_IN_SET('$forumid', childlist)");
		while ($forum = $DB->fetch_array($forumlist)) {
			$childlist = $this->construct_child_list($forum['id']);
			$DB->query_unbuffered( "UPDATE " . TABLE_PREFIX . "forum SET childlist = '$childlist' WHERE id = $forum[id]");
		}
	}

	function construct_child_list($forumid)
	{
		global $DB;
		if ($forumid == -1) {
			return '-1';
		}
		$childlist = $forumid;
		$children = $DB->query( "SELECT id FROM " . TABLE_PREFIX . "forum WHERE parentlist LIKE '%,$forumid,%'");
		while ($child = $DB->fetch_array($children)) {
			$childlist .= ',' . $child['id'];
		}
		$childlist .= ',-1';
		return $childlist;
	}
	
	function forums_list_forums($forumlist = array())
	{
		global $forums, $DB, $_INPUT, $bboptions;
		if (!is_array($forumlist) || count($forumlist) == 0) {
			$forumlist = $forums->func->cache_forums();
		}
		if (is_array($forumlist)) {
			foreach($forumlist AS $key => $forum) {
				if ( $this->type == "manage") {
					$reorder = "<select class='dropdown' name='f_{$forum['id']}'>";
					for( $i = 1 ; $i <= $forum['count'] ; $i++ ) {
						$sel = "";
						if ( $forum['order'] == $i ) {
							$sel = ' selected="selected" ';
						}
						$reorder .= "\n<option value='$i'{$sel}>$i</option>";
					}
					$reorder .= "</select>\n";
					if ( $forum['id'] == $bboptions['recycleforumid'] ) {
						$trash_can = "&nbsp;<a href='settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=14'><img src='{$forums->imageurl}/recyclebin.gif' border='0' title='".$forums->lang['isrecyclebin']."' align='absmiddle' /></a>";
					}
					$cell = '';
					$cell .= $reorder . "<strong><a href='forums.php?".$forums->sessionurl."do=edit&amp;f=".$forum['id']."' title='".$forums->lang['forumids'].": ".$forum['id']."'>$forum[name]</a></strong>".$trash_can;
					$cell .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='forums.php?".$forums->sessionurl."do=edit&amp;f=".$forum['id']."'>".$forums->lang['edit']."</a>&nbsp;|";
					$cell .= "&nbsp;<a href='forums.php?".$forums->sessionurl."do=delete&amp;f=".$forum['id']."'>".$forums->lang['delete']."</a>&nbsp;|";
					$cell .= "&nbsp;<a href='forums.php?".$forums->sessionurl."do=empty&amp;f=".$forum['id']."'>".$forums->lang['empty']."</a>&nbsp;|";
					$cell .= "&nbsp;<a href='forums.php?".$forums->sessionurl."do=editpermissions&amp;f=".$forum['id']."'>".$forums->lang['premission']."</a>&nbsp;|";
					$cell .= "&nbsp;<a href='forums.php?".$forums->sessionurl."do=new&amp;p=".$forum['id']."'>".$forums->lang['addsubforum']."</a>&nbsp;|";
					$cell .= "&nbsp;<a href='moderate.php?".$forums->sessionurl."f=".$forum['id']."&amp;type=single'>".$forums->lang['addmoderator']."</a>&nbsp;|";
					$cell .= "&nbsp;<a href='forums.php?".$forums->sessionurl."do=recount&amp;f=".$forum['id']."'>".$forums->lang['forumrecount']."</a>";
					$forums->admin->print_cells_single_row($cell,'left','tdrow1',"style='padding-left:".($forum['depth']*25)."px;'");
					$trash_can = '';
				} else if ( $this->type == 'moderator' ) {
					$mod_string = "";
					foreach( $this->moderator AS $phpid => $data ) {
						if ($data['forumid'] == $forum['id']) {
							if ($data['isgroup'] == 1) {
								$mod_string .= "<tr>
												 <td width='100%'>".$forums->lang['usergroup'].": <strong>{$data['usergroupname']}</strong></td>
												 <td width='0%' nowrap='nowrap'><a href='moderate.php?{$forums->sessionurl}do=remove&amp;id={$data['moderatorid']}'>".$forums->lang['delete']."</a>&nbsp;<a href='moderate.php?{$forums->sessionurl}do=edit&amp;u={$data['moderatorid']}'>".$forums->lang['edit']."</a>&nbsp;</td>
												</tr>";
							} else {
								$lastactive = TIMENOW - $data['lastactivity'];
								if ($lastactive < 86400) {
									$class = 'modtoday';
								} elseif ($lastactive > 86400 AND ($lastactive < 86400*2)) {
									$class = 'modyesterday';
								} elseif (($lastactive > 86400*2) AND ($lastactive < 86400*7)) {
									$class = 'modlastweekdays';
								} elseif (($lastactive > 86400*7) AND ($lastactive < 86400*30)) {
									$class = 'modsinceweekdays';
								} elseif (($lastactive > 86400*30)) {
									$class = 'modsincethirtydays';
								}
								$data['lastactivity'] = $forums->func->get_date($data['lastactivity'], 2);
								$mod_string .= "<tr>
												 <td width='100%'><b title='".$forums->lang['lastactivity'].": {$data['lastactivity']}'>{$data['username']}</strong> (".$forums->lang['lastactivity'].": <font class='$class'>{$data['lastactivity']}</font>)</td>
												 <td width='0%' nowrap='nowrap'><a href='moderate.php?{$forums->sessionurl}do=remove&id={$data['moderatorid']}'>".$forums->lang['delete']."</a>&nbsp;<a href='moderate.php?{$forums->sessionurl}do=edit&amp;u={$data['moderatorid']}'>".$forums->lang['edit']."</a>&nbsp;</td>
												</tr>";
							}
						}
					}
					if ($mod_string != "") {
						$these_mods = "<table cellpadding='3' cellspacing='0' width='100%' align='center'>".$mod_string."</table>";
					} else {
						$these_mods = "<center><i>".$forums->lang['none']."</i></center>";
					}
					$cell = '';
					$cell .= "<strong>" . $forums->func->construct_depth_mark($forum['depth'],"--") ." <a href='forums.php?".$forums->sessionurl."do=edit&amp;f=".$forum['id']."' title='".$forums->lang['forumids'].": ".$forum['id']."'>$forum[name]</a></strong>";
					$forums->admin->print_cells_row( array( "<center><input type='checkbox' name='add[]' value='{$forum['id']}' /></center>", $cell, $these_mods ) );
				}
			}
		}
	}
}

?>